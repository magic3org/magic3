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
 * @version    SVN: $Id: chacha_mainTopWidgetContainer.php 3346 2010-07-07 01:30:02Z fishbone $
 * @link       http://www.m-media.co.jp
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/chacha_mainBaseWidgetContainer.php');

class chacha_mainTopWidgetContainer extends chacha_mainBaseWidgetContainer
{
	private $messageCount;			// メッセージ取得数
	private $bufIndex;		// バッファ番号
	private $maxThreadNo;			// スレッド番号最大値
	private $ajaxSendData;			// Ajax送信データ
	
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
		$act = $request->trimValueOf('act');
		if ($act == 'getmessage'){	// Ajaxインターフェイスでの対応
			$param = 'ajax';		// Ajax処理
			return '';		// テンプレートは使用しない
		} else {
			return 'top.tmpl.html';
		}
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
		if ($param == 'ajax'){		// Ajax処理のとき
			// 現在のアクセスURLを再設定
			$this->_currentPageUrl = $this->gPage->getDefaultPageUrlByWidget($this->gEnv->getCurrentWidgetId());
			
			return $this->createAjax($request);
		} else {
			return $this->createHtml($request);
		}
	}
	/**
	 * HTML画面作成
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param								なし
	 */
	function createHtml($request)
	{
		$act = $request->trimValueOf('act');
		
		// クライアントIDを取得
		$clientId = '';
		if ($this->gEnv->canUseCookie()){		// クッキー使用可能なとき
			$clientId = $this->gAccess->getClientId();
		}
		
		// 会員IDを取得
		$clientMemberId = '';			// 現在の端末の会員ID
		$ret = $this->_db->getMemberInfoByDeviceId($clientId, $row);
		if ($ret) $clientMemberId = $row['mb_id'];
		
		// 表示メッセージ取得
		$this->maxThreadNo = 0;			// スレッド番号最大値
		$this->messageCount = 0;			// メッセージ取得数
		$visibleMessageCount = $this->_configArray[self::CF_MESSAGE_COUNT_TOP];			// 一度に表示されるメッセージの数
		$messageBufSize = $visibleMessageCount * 2;		// メッセージバッファサイズは表示メッセージ数の2倍
		$this->bufIndex = $messageBufSize;		// バッファ番号設定
		$this->_db->getThread($this->_boardId, $messageBufSize, 1/*ページ番号*/, array($this, 'itemsLoop'));
	
		// 最大メッセージ数に足りないときは、ダミーデータを追加
		$avatarImageUrl = $this->gEnv->getCurrentWidgetRootUrl() . self::DEFAULT_AVATAR_ICON_FILE;
		$avatarTag = '<img src="' . $this->getUrl($avatarImageUrl) . '" width="' . self::AVATAR_SIZE . '" height="' . self::AVATAR_SIZE .'" />';
		$avatarTag = '<a href="#">' . $avatarTag . '</a>';
		for ($i = $this->messageCount; $i < $messageBufSize; $i++){
			$message = '投稿メッセージがありません';
			$row = array(
				'buf_index'	=> $this->bufIndex,		// バッファ番号
				'no'		=> 0,					// 項目番号
				'id'		=> '',		// メッセージID
				'message_count'	=> '',		// メッセージ数
				'name'		=> '',			// 会員名
				'date'		=> '',			// 投稿日付
				'message'		=> $message,		// 投稿文
				'avatar'	=> $avatarTag	// アバター画像
			);
			$this->tmpl->addVars('message_list', $row);
			$this->tmpl->parseTemplate('message_list', 'a');
			$this->bufIndex--;		// バッファ番号更新
		}
		// メッセージ表示用の設定
		$this->tmpl->addVar("_widget", "message_count", $messageBufSize);		// ブラウザのメッセージバッファサイズ
		$this->tmpl->addVar("_widget", "visible_message_count", $visibleMessageCount);		// 表示メッセージ数
		$this->tmpl->addVar("_widget", "first_message_no", $messageBufSize - $visibleMessageCount + 1);		// 先頭に表示されるメッセージの番号
		$this->tmpl->addVar("_widget", "widget_id", $this->gEnv->getCurrentWidgetId());
		$this->tmpl->addVar("_widget", "max_no", $this->maxThreadNo);			// スレッド番号最大値を設定
	
		// 表示設定
		$innerStyle = self::CSS_BLOG_INNER_STYLE;
		//$innerColor = $this->_configArray[self::CF_PROFILE_COLOR];		// プロフィール背景色
		if (empty($innerColor)) $innerColor = $this->_configArray[self::CF_INNER_BG_COLOR];		// デフォルトの内枠背景色
		if (!empty($innerColor)) $innerStyle .= 'background-color:' . $innerColor . ';';
		$this->tmpl->addVar("_widget", "inner_style", $innerStyle);
		$this->tmpl->addVar("_widget", "top_style", self::CSS_BLOG_TOP_STYLE);		// トップコンテンツのスタイル
		
		// トップコンテンツ
		$this->tmpl->addVar("_widget", "top_contents", $this->_configArray[self::CF_TOP_CONTENTS]);
		
		// 登録用リンクを作成。会員として認識できないときはプロフィール画面へ、認識できる場合はマイページ画面へ遷移。
		$this->tmpl->setAttribute('top_link_area', 'visibility', 'visible');
		if (empty($clientMemberId)){
			$rigistLink = $this->convertUrlToHtmlEntity($this->getUrl($this->_currentPageUrl . '&task=' . self::TASK_PROFILE, true));
			$registName = 'ユーザ登録';
		} else {
			$rigistLink = $this->convertUrlToHtmlEntity($this->getUrl($this->_currentPageUrl . '&task=' . self::TASK_MYPAGE . '&' . self::URL_PARAM_MEMBER_ID . '=' . $clientMemberId, true));
			$registName = '投稿';
		}
		$this->tmpl->addVar("top_link_area", "regist_url", $rigistLink);
		$this->tmpl->addVar("top_link_area", "regist_name", $registName);
		
		// その他のURL
		$readLink = $this->convertUrlToHtmlEntity($this->getUrl($this->_currentPageUrl . '&task=' . self::TASK_READ, true));
		$this->tmpl->addVar("_widget", "read_more_url", $readLink);		// 「もっと読む」リンク
	}
	/**
	 * Ajaxデータ作成
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param								なし
	 */
	function createAjax($request)
	{
		$act = $request->trimValueOf('act');
		
		if ($act == 'getmessage'){			// Ajaxでのメッセージの取得のとき
			$no = $request->trimValueOf('no');		// 開始番号
			if ($no == '') $no = 1;
			$count = $request->trimValueOf('count');		// 取得数
			
			// 送信データ作成
			$this->ajaxSendData = array();
			$this->_db->getThreadByNo($this->_boardId, $no, $count, array($this, 'ajaxMessageLoop'));
			
			// 送信データをセット
			$this->gInstance->getAjaxManager()->addData('messages', $this->ajaxSendData);
		} else {

		}
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
		// スレッド番号最大値取得
		if ($index == 0) $this->maxThreadNo = $fetchedRow['mt_update_no'];
		
		// メッセージ用のデータを取得
		$no = $fetchedRow['mt_update_no'];		// スレッド更新番号
		$memberId = $fetchedRow['mb_id'];		// 会員ID
		$memberName = $fetchedRow['mb_name'];	// 会員名
		$messageId = $fetchedRow['mm_thread_id'];
		$messageCount = $fetchedRow['mt_message_count'] -1;		// メッセージ数
		if ($messageCount <= 0) $messageCount = '';				// 返信がないときはメッセージ数を表示しない
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
		$message = $fetchedRow['mm_message'];
		$message = makeTruncStr($message, self::TOP_MESSAGE_LENGTH);// メッセージ長調整
		$message = $this->convertToDispString($message);// 文字エスケープ処理
		$message = $this->_convertToPreviewTextWithSpace($message);			// 改行をスペースに変換
		$message = $this->convDispMessage($message, $fetchedRow['mm_thread_id']);// メッセージ内のリンクを作成
		
		// メッセージID
		$messageIdTag = '<a href="' . $messageLink  . '">#' . $messageId . '</a>';
		
		// アバター画像を設定
		$avatarImageUrl = $this->getAvatarUrl($memberId);
		$avatarTag = '<img src="' . $this->getUrl($avatarImageUrl) . '" width="' . self::AVATAR_SIZE . '" height="' . self::AVATAR_SIZE .'" />';
		$avatarTag = '<a href="' . $mypageLink . '">' . $avatarTag . '</a>';
			
		$row = array(
			'buf_index'	=> $this->bufIndex,		// バッファ番号
			'no'		=> $no,					// 項目番号
			'id'		=> $messageIdTag,		// メッセージID
			'message_count'	=> $messageCount,		// メッセージ数
			'name'		=> $nameTag,			// 会員名
			'date'		=> $date,			// 投稿日付
			'message'	=> $message,		// 投稿文
			'avatar'	=> $avatarTag	// アバター画像
		);
		$this->tmpl->addVars('message_list', $row);
		$this->tmpl->parseTemplate('message_list', 'a');
		
		// メッセージが存在するかどうか
		$this->isExistsMessage = true;
		$this->messageCount++;			// メッセージ取得数
		$this->bufIndex--;		// バッファ番号更新
		return true;
	}
	/**
	 * 取得したメッセージ項目をajax返信用オブジェクトに格納する
	 *
	 * @param int		$index			行番号
	 * @param array		$fetchedRow		取得行
	 * @param object	$param			任意使用パラメータ
	 * @return bool						trueを返すとループ続行。falseを返すとその時点で終了。
	 */
	function ajaxMessageLoop($index, $fetchedRow)
	{
		// メッセージ用のデータを取得
		$no = $fetchedRow['mt_update_no'];		// スレッド更新番号
		$memberId = $fetchedRow['mb_id'];		// 会員ID
		$memberName = $fetchedRow['mb_name'];	// 会員名
		$messageId = $fetchedRow['mm_thread_id'];
		$messageCount = $fetchedRow['mt_message_count'] -1;		// メッセージ数
		if ($messageCount <= 0) $messageCount = '';				// 返信がないときはメッセージ数を表示しない
		$mypageLink = $this->getUrl($this->_currentPageUrl . '&' . self::URL_PARAM_MEMBER_ID . '=' . $memberId, true);
		$messageLink = $this->getUrl($this->_currentPageUrl . '&' . self::URL_PARAM_MESSAGE_ID . '=' . $messageId, true);
		
		// アバター画像URL
		$avatarImageUrl = $this->getAvatarUrl($memberId);
		
		// 名前作成
		$nameTag = '<a href="' . $mypageLink . '">' . $memberName . '</a>';
		
		// 投稿文
		$message = $fetchedRow['mm_message'];
		$message = makeTruncStr($message, self::TOP_MESSAGE_LENGTH);		// メッセージ長調整
		$message = $this->_convertToPreviewTextWithSpace($message);			// 改行をスペースに変換
		$message = $this->convDispMessage($message, $fetchedRow['mm_thread_id']);// メッセージ内のリンクを作成
		
		// メッセージID
		$messageIdTag = '<a href="' . $messageLink  . '">#' . $messageId . '</a>';

		// 日付作成
		$weekDay = array('日', '月', '火', '水', '木', '金', '土');
		$timestamp = strtotime($fetchedRow['mm_regist_dt']);
		$weekNo = intval(date('w', $timestamp));
		$date = date('Y/m/d(' . $weekDay[$weekNo] . ') H:i:s', $timestamp);
		
		$line = array();
		$line['no'] = $no;			// スレッド番号
		$line['avatar'] = $avatarImageUrl;
		$line['membername'] = $nameTag;	// 会員名
		$line['message'] = $message;		// 投稿メッセージ
		$line['messageid'] = $messageIdTag;		// メッセージID
		$line['messagecount'] = $messageCount;		// メッセージ数
		$line['date'] = $date;		// 投稿日付
		$this->ajaxSendData[] = $line;
		return true;
	}
}
?>
