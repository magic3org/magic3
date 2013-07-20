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
 * @version    SVN: $Id: chacha_mainMypageWidgetContainer.php 3367 2010-07-11 11:45:28Z fishbone $
 * @link       http://www.m-media.co.jp
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/chacha_mainBaseWidgetContainer.php');

class chacha_mainMypageWidgetContainer extends chacha_mainBaseWidgetContainer
{
	private $threadStyle;	// スレッド部表示スタイル
	private $isExistsMessage;	// メッセージが存在するかどうか
	private $isExistsNextPage;	// 次のページがあるかどうか
	private $messageCount;		// メッセージ総数
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
		$postTicket = $request->trimValueOf('ticket');		// POST確認用
		$memberId = $request->trimValueOf(self::URL_PARAM_MEMBER_ID);	// 会員ID
		$message = $request->trimValueOf('message');		// 投稿メッセージ
		
		// 現在アクセス中の端末IDを取得
		$canPost = false;			// ブログ投稿可能かどうか
		$clientId = '';
		if ($this->gEnv->canUseCookie()){		// クッキー使用可能なとき
			$clientId = $this->gAccess->getClientId();
		}
		
		// 自分のブログページのときは投稿可能
		$clientMemberId = '';			// 現在の端末の会員ID
		$ret = $this->_db->getMemberInfoByDeviceId($clientId, $row);
		if ($ret) $clientMemberId = $row['mb_id'];
		if (!empty($memberId) && $memberId == $clientMemberId) $canPost = true;			// ブログ投稿可能かどうか
		
		if ($act == 'add'){		// 投稿追加のとき
			if (!empty($postTicket) && $postTicket == $request->getSessionValue(M3_SESSION_POST_TICKET)){		// 正常なPOST値のとき
				// 投稿権限のチェック
				if (!$canPost) $this->setUserErrorMsg('投稿権限がありません');
						
				// 入力項目のエラーチェック
				//$this->checkMessageInput($this->_boardId, $threadId, $name, $email, $message);
				if ($this->checkInput($message, 'メッセージ')){
					// 文字数のチェック
					$messageLength = $this->_configArray[self::CF_MESSAGE_LENGTH];			// 最大メッセージ長
					if (getLetterCount($message) > $messageLength) $this->setUserErrorMsg('メッセージは' . $messageLength . '文字まで入力可能です');
				}
				if (empty($memberId)) $this->setUserErrorMsg('会員IDが不正です');
			
				// エラーなしの場合は、データを更新
				if ($this->getMsgCount() == 0){
					// スレッドID作成
					$threadId = $this->createThreadId();
					if (empty($threadId)){
						$this->setAppErrorMsg('スレッドIDが作成できません');
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
			// 会員情報を設定
			$avatarImageUrl = $this->getAvatarUrl($memberId);// アバター画像URL
			$imageTag = '<img src="' . $this->getUrl($avatarImageUrl) . '" width="' . self::AVATAR_SIZE . '" height="' . self::AVATAR_SIZE .'" />';
			$this->tmpl->addVar("thread_area", "image", $imageTag);		// 画像
			
			// 投稿文を作成
			$this->messageCount = $this->_configArray[self::CF_MESSAGE_COUNT_MYPAGE];		// 最大項目数
			$this->_db->getThreadByMemberId($this->_boardId, $memberId, $this->messageCount, $pageNo, array($this, 'itemsLoop'), true);
			
			// 画面にデータを埋め込む
			$this->tmpl->addVar("thread_area", "name", $this->convertToDispString($row['mb_name']));				// ユーザ名

			if ($canPost){			// ブログ投稿可能なとき
				$this->tmpl->setAttribute('add_area', 'visibility', 'visible');// 投稿エリアを表示
				$this->tmpl->addVar("add_area", "member_id", $memberId);			// 会員ID
				
				// ハッシュキー作成
				$postTicket = md5(time() . $this->gAccess->getAccessLogSerialNo());
				$request->setSessionValue(M3_SESSION_POST_TICKET, $postTicket);		// セッションに保存
				$this->tmpl->addVar("add_area", "ticket", $postTicket);				// 画面に書き出し
			}
			
			// スレッドが存在しないときはタグを非表示にする
			if (!$this->isExistsMessage){
				// 投稿なしのメッセージを表示
				$this->tmpl->setAttribute('no_message_area', 'visibility', 'visible');
				
				$this->tmpl->setAttribute('message_list', 'visibility', 'hidden');
			}
			// ページ遷移用リンク
			$pageLink = '';
			if ($pageNo > 1){			// 前のページがあるとき
				$foreLink = $this->convertUrlToHtmlEntity($this->getUrl($this->_currentPageUrl . '&' . self::URL_PARAM_MEMBER_ID . '=' . $memberId . '&page=' . ($pageNo -1), true));
				$pageLink .= '<a href="' . $foreLink . '"><b>前へ</b></a>';
			}
			if ($this->isExistsNextPage){	// 次のページがあるとき
				if (!empty($pageLink)) $pageLink .= '&nbsp;&nbsp;';
				$nextLink = $this->convertUrlToHtmlEntity($this->getUrl($this->_currentPageUrl . '&' . self::URL_PARAM_MEMBER_ID . '=' . $memberId . '&page=' . ($pageNo +1), true));
				$pageLink .= '<a href="' . $nextLink . '"><b>次へ</b></a>';
			}
			$this->tmpl->addVar("_widget", "page_link", $pageLink);
			
			// リンク作成
			$profileLink = $this->convertUrlToHtmlEntity($this->getUrl($this->_currentPageUrl . '&task=' . self::TASK_PROFILE . '&' . self::URL_PARAM_MEMBER_ID . '=' . $memberId, true));
			$this->tmpl->setAttribute('top_link_area', 'visibility', 'visible');
			$this->tmpl->addVar("top_link_area", "profile_url", $profileLink);			// プロフィールURL
		} else {
			$this->setUserErrorMsg('会員IDが不正です');
			
			// スレッド表示部を非表示にする
			$this->tmpl->setAttribute('thread_area', 'visibility', 'hidden');
		}
		// 表示設定
		$this->threadStyle = self::CSS_BLOG_INNER_STYLE;
		$threadColor = $this->_configArray[self::CF_THREAD_COLOR];		// スレッド表示部背景色
		if (empty($threadColor)) $threadColor = $this->_configArray[self::CF_INNER_BG_COLOR];		// デフォルトの内枠背景色
		if (!empty($threadColor)) $this->threadStyle .= 'background-color:' . $threadColor . ';';
		$this->tmpl->addVar("_widget", "thread_style", $this->threadStyle);
		
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
		$date = date('Y/m/d(' . $weekDay[$weekNo] . ') H:i:s', $timestamp);
		
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
		$messageLink = $this->convertUrlToHtmlEntity($this->getUrl($this->_currentPageUrl . '&' . self::URL_PARAM_MESSAGE_ID . '=' . $messageId, true));
		$messageIdTag = '<a href="' . $messageLink  . '">#' . $messageId . '</a>';
		// 削除メッセージのとき
		/*if ($fetchedRow['mm_deleted']){
			$message = '参照できません。';
		}*/
		
		$row = array(
			'id'		=> $messageIdTag,		// メッセージID
			'message_count'	=> $messageCount,	// メッセージ数
			'date'		=> $date,			// 投稿日付
			'message'		=> $message		// 投稿文
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
