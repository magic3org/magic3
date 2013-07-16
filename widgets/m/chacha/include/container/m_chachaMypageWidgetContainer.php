<?php
/**
 * index.php用コンテナクラス
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    マイクロブログ
 * @author     株式会社 毎日メディアサービス
 * @copyright  Copyright 2010 株式会社 毎日メディアサービス.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: m_chachaMypageWidgetContainer.php 3363 2010-07-10 05:12:31Z fishbone $
 * @link       http://www.m-media.co.jp
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/m_chachaBaseWidgetContainer.php');

class m_chachaMypageWidgetContainer extends m_chachaBaseWidgetContainer
{
	private $messageCount;		// 表示メッセージ数
	private $isExistsMessage;	// メッセージが存在するかどうか
	private $isExistsNextPage;	// 次のページがあるかどうか
	const THREAD_ID_LENGTH = 5;	// スレッドIDの長さ
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
	}
	/**
	 * テンプレートファイルを設定
	 *
	 * _assign()でデータを埋め込むテンプレートファイルのファイル名を返す。
	 * 読み込むディレクトリは、「自ウィジェットディレクトリ/include/template」に固定。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。そのまま_assign()に渡る
	 * @return string 						テンプレートファイル名。テンプレートライブラリを使用しない場合は空文字列「''」を返す。
	 */
	function _setTemplate($request, &$param)
	{
		return 'mypage.tmpl.html';
	}
	/**
	 * テンプレートにデータ埋め込む
	 *
	 * _setTemplate()で指定したテンプレートファイルにデータを埋め込む。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。_setTemplate()と共有。
	 * @param								なし
	 */
	function _assign($request, &$param)
	{
		$act = $request->trimValueOf('act');
		$pageNo = $request->trimIntValueOf(M3_REQUEST_PARAM_PAGE_NO, '1');				// ページ番号
		if (empty($pageNo)) $pageNo = 1;
		$postTicket = $request->trimValueOf('ticket');		// POST確認用
		$memberId = $request->trimValueOf(self::URL_PARAM_MEMBER_ID);	// 会員ID
		$message = $request->mobileTrimValueOf('message');		// 投稿メッセージ
		
		// 現在アクセス中の端末IDを取得
		$canPost = false;			// ブログ投稿可能かどうか
		$clientId = $this->_mobileId;
		
		// 自分のブログページのときは投稿可能
		$clientMemberId = '';			// 現在の端末の会員ID
		$ret = $this->_db->getMemberInfoByDeviceId($clientId, $row);
		if ($ret) $clientMemberId = $row['mb_id'];
		if (empty($memberId)){
			if (!empty($clientMemberId)){		// 登録メンバーのとき
				$memberId = $clientMemberId;
				$canPost = true;			// ブログ投稿可能かどうか
			}
		} else if ($memberId == $clientMemberId){
			$canPost = true;			// ブログ投稿可能かどうか
		}

		if ($act == 'add'){		// 投稿追加のとき
			if (!empty($postTicket) && $postTicket == $request->getSessionValue(M3_SESSION_POST_TICKET)){		// 正常なPOST値のとき
				// 投稿権限のチェック
				if (!$canPost) $this->setUserErrorMsg('投稿権限がありません');
						
				// 入力項目のエラーチェック
				//$this->checkMessageInput($this->_boardId, $threadId, $name, $email, $message);
				if ($this->checkInput($message, 'ﾒｯｾｰｼﾞ')){
					// 文字数のチェック
					$messageLength = $this->_configArray[self::CF_MESSAGE_LENGTH];			// 最大メッセージ長
					if (getLetterCount($message) > $messageLength) $this->setUserErrorMsg('ﾒｯｾｰｼﾞは' . $messageLength . '文字まで入力可能です');
				}
				if (empty($memberId)) $this->setUserErrorMsg('会員IDが不正です');
			
				// エラーなしの場合は、データを更新
				if ($this->getMsgCount() == 0){
					// スレッドID作成
					$threadId = $this->createThreadId();
					if (empty($threadId)){
						$this->setAppErrorMsg('ｽﾚｯﾄﾞIDが作成できません');
					} else {
						// 新規投稿文の追加
						$ret = $this->_db->addNewThread($this->_boardId, $threadId, $memberId, ''/*件名*/, $message);

						if ($ret){		// データ追加成功のとき
							$this->setGuidanceMsg('投稿完了しました');
							
							$message = '';			// メッセージクリア
						} else {
							$this->setAppErrorMsg('投稿に失敗しました');
						}
					}
				}
			}
			$request->unsetSessionValue(M3_SESSION_POST_TICKET);		// セッション値をクリア
		}

		// 会員情報を取得
		$ret = $this->_db->getMemberInfoById($memberId, $row);
		if ($ret){
			// アバターを設定
			$avatarImageUrl = $this->getAvatarUrl($memberId);// アバター画像URL
			$imageTag = '<img src="' . $this->getUrl($avatarImageUrl) . '" width="' . self::AVATAR_SIZE . '" height="' . self::AVATAR_SIZE .'" />';
			$this->tmpl->addVar("thread_area", "avatar_image", $imageTag);		// 画像

			// 投稿文を作成
			$this->messageCount = $this->_configArray[self::CF_MESSAGE_COUNT_MYPAGE];		// 最大項目数
			$this->_db->getThreadByMemberId($this->_boardId, $memberId, $this->messageCount, $pageNo, array($this, 'itemsLoop'), true);
			
			// 画面にデータを埋め込む
			$this->tmpl->addVar("thread_area", "name", $this->convertToDispString($row['mb_name']));				// ユーザ名

			if ($canPost){			// ブログ投稿可能なとき
				$this->tmpl->setAttribute('add_area', 'visibility', 'visible');// 投稿エリアを表示
				$this->tmpl->addVar("add_area", "member_id", $memberId);			// 会員ID
				$this->tmpl->addVar('add_area', 'act', 'add');		// 新規登録
				$this->tmpl->addVar("add_area", "current_url", $this->gEnv->getCurrentRequestUri());
				
				// ハッシュキー作成
				$postTicket = md5(time() . $this->gAccess->getAccessLogSerialNo());
				$request->setSessionValue(M3_SESSION_POST_TICKET, $postTicket);		// セッションに保存
				$this->tmpl->addVar("add_area", "ticket", $postTicket);				// 画面に書き出し
			}
			
			// スレッドが存在しないときはタグを非表示にする
			if (!$this->isExistsMessage){
				// 投稿なしのメッセージを表示。自分自身のマイページのときは表示しない。
				if (!$canPost) $this->tmpl->setAttribute('no_message_area', 'visibility', 'visible');
				
				$this->tmpl->setAttribute('message_list', 'visibility', 'hidden');
			}
			// リンク作成
			//$registLink = $this->convertUrlToHtmlEntity($this->getUrl($this->_currentPageUrl . '&task=' . self::TASK_PROFILE . '&' . self::URL_PARAM_MEMBER_ID . '=' . $memberId, true));
			$registLink = $this->convertUrlToHtmlEntity($this->getUrl($this->gEnv->createCurrentPageUrlForMobile('task=' . self::TASK_PROFILE . '&' . self::URL_PARAM_MEMBER_ID . '=' . $memberId)));
			$registName = 'ﾌﾟﾛﾌｨｰﾙ';
			$this->tmpl->addVar("_widget", "regist_url", $registLink);
			$this->tmpl->addVar("_widget", "regist_name", $registName);
			
			// ページ遷移用リンク
			$pageLink = '';
			if ($pageNo > 1){			// 前のページがあるとき
				$foreLink = $this->convertUrlToHtmlEntity($this->getUrl($this->_currentPageUrl . '&' . self::URL_PARAM_MEMBER_ID . '=' . $memberId . '&page=' . ($pageNo -1), true));
				$pageLink .= '<a href="' . $foreLink . '" accesskey="1">前へ[1]</a>';
			}
			if ($this->isExistsNextPage){	// 次のページがあるとき
				if (!empty($pageLink)) $pageLink .= '&nbsp;&nbsp;';
				$nextLink = $this->convertUrlToHtmlEntity($this->getUrl($this->_currentPageUrl . '&' . self::URL_PARAM_MEMBER_ID . '=' . $memberId . '&page=' . ($pageNo +1), true));
				$pageLink .= '<a href="' . $nextLink . '" accesskey="2">次へ[2]</a>';
			}
			$this->tmpl->addVar("_widget", "page_link", $pageLink);
		} else {
			$this->setUserErrorMsg('会員IDが不正です');
			
			// スレッド表示部を非表示にする
			$this->tmpl->setAttribute('thread_area', 'visibility', 'hidden');
		}
		
		// 画面にデータを埋め込む
		$this->tmpl->addVar("add_area", "message", $message);
	}
	/**
	 * 取得したコンテンツ項目をテンプレートに設定する
	 *
	 * @param int		$index			行番号
	 * @param array		$fetchedRow		取得行
	 * @param object	$param			任意使用パラメータ
	 * @return bool						trueを返すとループ続行。falseを返すとその時点で終了。
	 */
	function itemsLoop($index, $fetchedRow)
	{
		// 最大表示数を超えたときは終了
		if ($index >= $this->messageCount){
			$this->isExistsNextPage = true;				// 次のページあり
			return false;
		}
		
		// 日付作成
		$weekDay = array('日', '月', '火', '水', '木', '金', '土');
		$timestamp = strtotime($fetchedRow['mm_regist_dt']);
		$weekNo = intval(date('w', $timestamp));
		$date = date('m/d(' . $weekDay[$weekNo] . ') H:i:s', $timestamp);
		
		// 投稿文
		$message = $this->convertToDispString($fetchedRow['mm_message']);// 文字エスケープ処理
		$message = $this->convertToPreviewText($message);			// 改行をBRタグに変換
		if ($fetchedRow['mm_index'] == 1) $message .= '<br />';		// トップのメッセージは改行を追加
		
		// メッセージ変換
		$message = $this->convDispMessage($message, $fetchedRow['mm_thread_id']);
		
		// メッセージID
		$messageId = $fetchedRow['mm_thread_id'];
		$messageCount = $fetchedRow['mt_message_count'] -1;		// メッセージ数
		if ($messageCount <= 0) $messageCount = '';				// 返信がないときはメッセージ数を表示しない
		$messageLink = $this->convertUrlToHtmlEntity($this->getUrl($this->_currentPageUrl . '&' . self::URL_PARAM_MESSAGE_ID . '=' . $messageId));
		$messageIdTag = '<a href="' . $messageLink  . '">#' . $messageId . '</a>';

		// 削除メッセージのとき
		/*if ($fetchedRow['mm_deleted']){
			$message = '参照できません。';
		}*/
		
		$row = array(
			'id'		=> $messageIdTag,		// メッセージID
			'message_count'	=> $messageCount,	// メッセージ数
			'date'		=> $date,			// 投稿日付
			'message'		=> $message,		// 投稿文
			'spacer'	=> $this->_spacer	// スペーサ
		);
		$this->tmpl->addVars('message_list', $row);
		$this->tmpl->parseTemplate('message_list', 'a');
		
		// メッセージが存在するかどうか
		$this->isExistsMessage = true;
		return true;
	}
	/**
	 * スレッドIDを作成
	 *
	 * @return string				スレッドID
	 */
	function createThreadId()
	{
		$threadId = '';
		
		for ($i = 0; $i < self::CREATE_CODE_RETRY_COUNT; $i++){
			// 「0,I,L,O,i,l,o」除くランダム文字列を作成
			$threadId = $this->_createRandString('123456789ABCDEFGHJKMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz', self::THREAD_ID_LENGTH);
		
			// すでに登録済みかどうかチェック
			$ret = $this->_db->isExistsThreadId($threadId);
			if (!$ret) break;
		}
		return $threadId;
	}
}
?>
