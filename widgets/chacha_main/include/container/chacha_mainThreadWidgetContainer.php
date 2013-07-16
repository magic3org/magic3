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
 * @version    SVN: $Id: chacha_mainThreadWidgetContainer.php 3362 2010-07-10 03:08:21Z fishbone $
 * @link       http://www.m-media.co.jp
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/chacha_mainBaseWidgetContainer.php');

class chacha_mainThreadWidgetContainer extends chacha_mainBaseWidgetContainer
{
	private $messageCount;			// メッセージ数
	private $isExistsMessage;	// メッセージが存在するかどうか
	private $isExistsNextPage;	// 次のページがあるかどうか
	
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
		return 'thread.tmpl.html';
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
		$postTicket = $request->trimValueOf('ticket');		// POST確認用
		$pageNo = $request->trimIntValueOf(M3_REQUEST_PARAM_PAGE_NO, '1');				// ページ番号
		if (empty($pageNo)) $pageNo = 1;
		$messageId = $request->trimValueOf(self::URL_PARAM_MESSAGE_ID);		// メッセージID
		$message = $request->trimValueOf('message');		// 投稿メッセージ
		
		// クライアントIDを取得
		$canPost = false;			// ブログ返信可能かどうか
		$clientId = '';
		if ($this->gEnv->canUseCookie()){		// クッキー使用可能なとき
			$clientId = $this->gAccess->getClientId();
		}
		
		// 会員IDを取得
		$clientMemberId = '';			// 現在の端末の会員ID
		$ret = $this->_db->getMemberInfoByDeviceId($clientId, $row);
		if ($ret) $clientMemberId = $row['mb_id'];
		if (!empty($clientMemberId)) $canPost = true;			// ブログ返信可能かどうか
		
		$reloadData = false;		// データの再読み込み
		if ($act == 'add'){		// 新規追加のとき
			if (!empty($postTicket) && $postTicket == $request->getSessionValue(M3_SESSION_POST_TICKET)){		// 正常なPOST値のとき
				// 投稿権限のチェック
				if (!$canPost) $this->setUserErrorMsg('投稿権限がありません');
				
				// 入力項目のエラーチェック
				if ($this->checkInput($message, 'メッセージ')){
					// 文字数のチェック
					$messageLength = $this->_configArray[self::CF_MESSAGE_LENGTH];			// 最大メッセージ長
					if (getLetterCount($message) > $messageLength) $this->setUserErrorMsg('メッセージは' . $messageLength . '文字まで入力可能です');
				}
				if (empty($clientMemberId)) $this->setUserErrorMsg('会員IDが不正です');
			
				// エラーなしの場合は、データを更新
				if ($this->getMsgCount() == 0){
					$ret = $this->_db->addNewReply($this->_boardId, $messageId, $clientMemberId, $message);
					if ($ret){		// データ追加成功のとき
						$this->setGuidanceMsg('投稿完了しました');
						
						$message = '';			// メッセージクリア
					} else {
						$this->setAppErrorMsg('投稿に失敗しました');
					}
				}
			}
			$request->unsetSessionValue(M3_SESSION_POST_TICKET);		// セッション値をクリア
		} else {
			$reloadData = true;		// データの再読み込み
		}
		
		// スレッド情報取得
		$ret = $this->_db->getThreadInfo($this->_boardId, $messageId, $row);
		if ($ret){
			// メッセージ用のデータを取得
			$memberId = $row['mb_id'];		// 会員ID
			$memberName = $row['mb_name'];	// 会員名
			$messageId = $row['mm_thread_id'];
			$messageCount = $row['mt_message_count'] -1;		// メッセージ数
			$mypageLink = $this->convertUrlToHtmlEntity($this->getUrl($this->_currentPageUrl . '&' . self::URL_PARAM_MEMBER_ID . '=' . $memberId, true));
			$messageLink = $this->convertUrlToHtmlEntity($this->getUrl($this->_currentPageUrl . '&' . self::URL_PARAM_MESSAGE_ID . '=' . $messageId, true));
		
			// 名前作成
			$nameTag = '<a href="' . $mypageLink . '">' . $this->convertToDispString($memberName) . '</a>';
		
			// 日付作成
			$weekDay = array('日', '月', '火', '水', '木', '金', '土');
			$timestamp = strtotime($row['mm_regist_dt']);
			$weekNo = intval(date('w', $timestamp));
			$date = date('Y/m/d(' . $weekDay[$weekNo] . ') H:i:s', $timestamp);
		
			// 投稿文
			$topMessage = $this->convertToDispString($row['mm_message']);// 文字エスケープ処理
			$topMessage = $this->_convertToPreviewTextWithSpace($topMessage);			// 改行をスペースに変換
			$topMessage = $this->convDispMessage($topMessage, $row['mm_thread_id']);// メッセージ内のリンクを作成
		
			// メッセージID
			$messageIdTag = '<a href="' . $messageLink  . '">#' . $messageId . '</a>';
		
			// アバター画像を設定
			$avatarImageUrl = $this->getAvatarUrl($memberId);
			$avatarTag = '<img src="' . $this->getUrl($avatarImageUrl) . '" width="' . self::AVATAR_SIZE . '" height="' . self::AVATAR_SIZE .'" />';
			$avatarTag = '<a href="' . $mypageLink . '">' . $avatarTag . '</a>';
			
			$this->tmpl->addVar("thread_area", "id", $messageIdTag);	// メッセージID
			$this->tmpl->addVar("thread_area", "thread_message_count", $messageCount);	// メッセージ数
			$this->tmpl->addVar("thread_area", "name", $nameTag);		// 会員名
			$this->tmpl->addVar("thread_area", "date", $date);			// 投稿日付
			$this->tmpl->addVar("thread_area", "thread_message", $topMessage);		// 投稿文
			$this->tmpl->addVar("thread_area", "avatar", $avatarTag);		// アバター画像

			// 個別のスタイル設定
			$innerStyle = self::CSS_BLOG_INNER_STYLE;
			//$innerColor = $this->_configArray[self::CF_PROFILE_COLOR];		// プロフィール背景色
			if (empty($innerColor)) $innerColor = $this->_configArray[self::CF_INNER_BG_COLOR];		// デフォルトの内枠背景色
			if (!empty($innerColor)) $innerStyle .= 'background-color:' . $innerColor . ';';
			$this->tmpl->addVar("_widget", "inner_style", $innerStyle);
		
			// 登録用リンクを作成。会員として認識できないときはプロフィール画面。
			if (empty($clientMemberId)){
				$this->tmpl->setAttribute('top_link_area', 'visibility', 'visible');
				$rigistLink = $this->convertUrlToHtmlEntity($this->getUrl($this->_currentPageUrl . '&task=' . self::TASK_PROFILE, true));
				$registName = 'ユーザ登録';
				$this->tmpl->addVar("top_link_area", "regist_url", $rigistLink);
				$this->tmpl->addVar("top_link_area", "regist_name", $registName);
/*			} else {
				$rigistLink = $this->convertUrlToHtmlEntity($this->getUrl($this->_currentPageUrl . '&task=' . self::TASK_MYPAGE . '&' . self::URL_PARAM_MEMBER_ID . '=' . $clientMemberId, true));
				$registName = '投稿';*/
			}

			// 表示メッセージ取得
			// 1つ多く呼び出し、次のページがあるか確認
			$this->messageCount = $this->_configArray[self::CF_MESSAGE_COUNT_MYPAGE];		// 最大項目数
			$this->_db->getThreadReply(array($this, 'itemsLoop'), $this->_boardId, $messageId, $this->messageCount, $pageNo, true);
		
			// スレッドが存在しないときは一覧を非表示にする
			if (!$this->isExistsMessage){
				// 投稿なしのメッセージを表示
				$this->tmpl->setAttribute('no_message_area', 'visibility', 'visible');
				$this->tmpl->setAttribute('message_list', 'visibility', 'hidden');
			}
		
			// ページ遷移用リンク
			$pageLink = '';
			if ($pageNo > 1){			// 前のページがあるとき
				$foreLink = $this->convertUrlToHtmlEntity($this->getUrl($this->_currentPageUrl . '&' . self::URL_PARAM_MESSAGE_ID . '=' . $messageId . '&page=' . ($pageNo -1), true));
				$pageLink .= '<a href="' . $foreLink . '"><b>前へ</b></a>';
			}
			if ($this->isExistsNextPage){	// 次のページがあるとき
				if (!empty($pageLink)) $pageLink .= '&nbsp;&nbsp;';
				$nextLink = $this->convertUrlToHtmlEntity($this->getUrl($this->_currentPageUrl . '&' . self::URL_PARAM_MESSAGE_ID . '=' . $messageId . '&page=' . ($pageNo +1), true));
				$pageLink .= '<a href="' . $nextLink . '"><b>次へ</b></a>';
			}
			$this->tmpl->addVar("_widget", "page_link", $pageLink);
			
			if ($canPost){			// ブログ返信可能なとき
				$this->tmpl->setAttribute('add_area', 'visibility', 'visible');// 投稿エリアを表示
				$this->tmpl->addVar("add_area", "member_id", $memberId);			// 会員ID
				$this->tmpl->addVar("add_area", "message", $message);
				
				// ハッシュキー作成
				$postTicket = md5(time() . $this->gAccess->getAccessLogSerialNo());
				$request->setSessionValue(M3_SESSION_POST_TICKET, $postTicket);		// セッションに保存
				$this->tmpl->addVar("add_area", "ticket", $postTicket);				// 画面に書き出し
			}
		} else {
			$this->setUserErrorMsg('メッセージIDが不正です');
			
			// スレッド表示部を非表示にする
			$this->tmpl->setAttribute('thread_area', 'visibility', 'hidden');
		}
	
		// 表示設定
		$this->threadStyle = self::CSS_BLOG_INNER_STYLE;
		$threadColor = $this->_configArray[self::CF_THREAD_COLOR];		// スレッド表示部背景色
		if (empty($threadColor)) $threadColor = $this->_configArray[self::CF_INNER_BG_COLOR];		// デフォルトの内枠背景色
		if (!empty($threadColor)) $this->threadStyle .= 'background-color:' . $threadColor . ';';
		$this->tmpl->addVar("_widget", "thread_style", $this->threadStyle);
	}
	/**
	 * 取得したメッセージ項目をテンプレートに設定する
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
		
		// メッセージ用のデータを取得
		$memberId = $fetchedRow['mb_id'];		// 会員ID
		$memberName = $fetchedRow['mb_name'];	// 会員名
		$messageId = $fetchedRow['mm_thread_id'];
		$messageId .= '-' . $fetchedRow['mm_index'];
		$mypageLink = $this->convertUrlToHtmlEntity($this->getUrl($this->_currentPageUrl . '&' . self::URL_PARAM_MEMBER_ID . '=' . $memberId, true));
		$messageLink = $this->convertUrlToHtmlEntity($this->getUrl($this->_currentPageUrl . '&' . self::URL_PARAM_MESSAGE_ID . '=' . $messageId, true));
		
		// 名前作成
		$nameTag = '<a href="' . $mypageLink . '">' . $this->convertToDispString($memberName) . '</a>';
		
		// 日付作成
		$weekDay = array('日', '月', '火', '水', '木', '金', '土');
		$timestamp = strtotime($fetchedRow['mm_regist_dt']);
		$weekNo = intval(date('w', $timestamp));
		$date = date('Y/m/d(' . $weekDay[$weekNo] . ') H:i:s', $timestamp);
		
		// 投稿文
		$message = $this->convertToDispString($fetchedRow['mm_message']);// 文字エスケープ処理
		$message = $this->_convertToPreviewTextWithSpace($message);			// 改行をスペースに変換
		$message = $this->convDispMessage($message, $fetchedRow['mm_thread_id']);// メッセージ内のリンクを作成
		
		// メッセージID
		$messageIdTag = '<a href="' . $messageLink  . '">#' . $messageId . '</a>';
		
		// 削除メッセージのとき
		/*if ($fetchedRow['mm_deleted']){
			$message = '参照できません。';
		}*/
		// アバター画像を設定
		$avatarImageUrl = $this->getAvatarUrl($memberId);
		$avatarTag = '<img src="' . $this->getUrl($avatarImageUrl) . '" width="' . self::AVATAR_SIZE . '" height="' . self::AVATAR_SIZE .'" />';
		$avatarTag = '<a href="' . $mypageLink . '">' . $avatarTag . '</a>';
			
		$row = array(
			'id'		=> $messageIdTag,		// メッセージID
			'name'		=> $nameTag,			// 会員名
			'date'		=> $date,			// 投稿日付
			'message'	=> $message,		// 投稿文
			'avatar'	=> $avatarTag	// アバター画像
		);
		$this->tmpl->addVars('message_list', $row);
		$this->tmpl->parseTemplate('message_list', 'a');
		
		// メッセージが存在するかどうか
		$this->isExistsMessage = true;
		return true;
	}
}
?>
