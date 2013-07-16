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
 * @version    SVN: $Id: chacha_mainBaseWidgetContainer.php 3352 2010-07-08 02:59:39Z fishbone $
 * @link       http://www.m-media.co.jp
 */
require_once($gEnvManager->getContainerPath() . '/baseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/chacha_mainDb.php');

class chacha_mainBaseWidgetContainer extends BaseWidgetContainer
{
	protected $_db;			// DB接続オブジェクト
	protected $_configArray;		// BBS定義値
	protected $_headCss;	// CSS定義
	protected $_boardId;	// 掲示板ID
	protected $_currentPageUrl;	// 現在のページのURL
	protected $_autolink;		// リンクを自動作成
	const CREATE_CODE_RETRY_COUNT = 10;				// コード生成のリトライ数
	const DEFAULT_BBS_ID_HEAD = 'board';		// デフォルトの掲示板ID
	const DEFAULT_LAST_MESSAGE_COUNT = 50;	// メッセージ取得用デフォルトの最新メッセージ数
	const DEFAULT_MESSAGE_COUNT_PER_PAGE = 100;	// メッセージ取得用デフォルトの1ページあたりメッセージ数
	const AVATAR_DIR = '/widgets/chacha/avatar';			// アバター格納ディレクトリ
	const AVATAR32_DIR = '/widgets/chacha/avatar32';			// アバター(小)格納ディレクトリ
	const DEFAULT_AVATAR_ICON_FILE = '/images/default_avatar.gif';		// デフォルトのアバターアイコン
	const DEFAULT_AVATAR_FILE_EXT = 'gif';			// アバターファイルのデフォルト拡張子
	const DEFAULT_SMALL_AVATAR_FILE_EXT = 'gif';			// アバター(小)ファイルのデフォルト拡張子
	const AVATAR_TAG_ID = 'chacha_avatar_image';			// アバタータグID
	const AVATAR_SIZE = 64;			// アバター画像のサイズ
	const SMALL_AVATAR_SIZE = 32;			// アバター画像(小)のサイズ
	const TOP_MESSAGE_LENGTH = 100;		// トップページのメッセージ長
	// DB定義値
	const CF_BBS_TITLE = 'title';			// 掲示板タイトル
	const CF_TITLE_COLOR = 'title_color';	// タイトルカラー
	const CF_TOP_LINK = 'top_link';		// トップ画像のリンク先
	const CF_TOP_IMAGE = 'top_image';		// トップ画像
	const CF_BBS_GUIDE = 'bbs_guide';		// 掲示板規則
	const CF_BG_IMAGE = 'bg_image';		// 背景画像
	
	const CF_BG_COLOR = 'bg_color';		// 背景色
	const CF_INNER_BG_COLOR = 'inner_bg_color';		// 内枠のデフォルト背景色
	const CF_PROFILE_COLOR = 'profile_color';		// プロフィール背景色
	
	const CF_MENU_COLOR = 'menu_color';		// メニュー背景色
	const CF_MAKE_THREAD_COLOR = 'makethread_color';		// スレッド作成部背景色
	const CF_THREAD_COLOR = 'thread_color';		// スレッド表示部背景色
	const CF_TEXT_COLOR = 'text_color';		// 文字色
		
	const CF_SUBJECT_LENGTH = 'subject_length';		// 件名最大長
	const CF_NAME_LENGTH = 'name_length';		// 投稿者名最大長
	const CF_EMAIL_LENGTH = 'email_length';		// emailアドレス最大長
	const CF_MESSAGE_LENGTH = 'message_length';	// 最大メッセージ長
	const CF_ERR_MESSAGE_COLOR = 'err_message_color';		// エラーメッセージ文字色
	const CF_LINE_LENGTH = 'line_length';		// 投稿文行長
	const CF_LINE_COUNT = 'line_count';			// 投稿文行数
	const CF_RES_ANCHOR_LINK_COUNT = 'res_anchor_link_count';		// レスアンカーリンク数
	const CF_THREAD_COUNT = 'thread_count';		// トップ画面に表示するスレッド最大数
	const CF_RES_COUNT = 'res_count';		// トップ画面に表示するレス最大数
	const CF_THREAD_RES = 'thread_res';		// 1スレッドに投稿できるレス数の上限
	const CF_MENU_THREAD_COUNT = 'menu_thread_count';	// メニューに表示するスレッド最大数
	const CF_SHOW_EMAIL = 'show_email';		// Eメールアドレスを表示
	const CF_AUTOLINK = 'autolink';			// 自動的にリンクを作成
	const CF_NONAME_NAME = 'noname_name';				// 名前未設定時の表示名
	const CF_MESSAGE_COUNT_TOP = 'message_count_top';	// トップページのメッセージ表示項目数
	const CF_MESSAGE_COUNT_MYPAGE = 'message_count_mypage';	// マイページのメッセージ表示項目数
	const CF_TOP_CONTENTS = 'top_contents';		// トップコンテンツ
	// 画面
	const TASK_TOP = 'top';			// トップ画面
	const TASK_SUBJECT = 'subject';			// スレッド件名
	const TASK_THREAD = 'thread';			// スレッド処理
	const TASK_NEW_THREAD = 'newthread';		// スレッド新規作成
	const TASK_READ = 'read';		// スレッド表示
	const TASK_PROFILE = 'profile';			// プロフィール表示
	const TASK_MYPAGE = 'mypage';			// マイページ表示
	// URL用パラメータ
	const URL_PARAM_MEMBER_ID = 'memberid';		// 会員ID
	const URL_PARAM_MESSAGE_ID = 'messageid';		// メッセージID
	
	// CSS
	const CSS_BLOG_STYLE = 'width:100%;text-align:left;padding:10px 0 10px 0;';		// ベースのスタイル
	const CSS_BLOG_TOP_STYLE = 'width:95%;text-align:left;padding:0 0 5px 0;';						// トップコンテンツ枠のスタイル
	const CSS_BLOG_INNER_STYLE = 'width:95%;text-align:left;padding:5px 0 5px 0;';					// 内枠のスタイル
	const CSS_LINK_STYLE_TOP = 'margin:0 10px;text-align:right;';	// 上のリンク部のスタイル
	const CSS_LINK_STYLE_BOTTOM = 'margin:0 10px;';					// 下のリンク部のスタイル
	const CSS_LINK_STYLE_INNER_BOTTOM = 'margin:0 10px;text-align:right;';	// 内枠の下のリンク部のスタイル
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// サブウィジェット起動のときだけ初期処理実行
		if ($this->gEnv->getIsSubWidget()){
			// DBオブジェクト作成
			$this->_db = new chacha_mainDb();
		
			// 定義ID取得
			// 定義IDから掲示板IDを作成
			$configId = $this->gEnv->getCurrentWidgetConfigId();
			$this->_boardId = self::DEFAULT_BBS_ID_HEAD . intval($configId);
		
			// BBS定義を読み込む
			$this->_loadConfig($this->_boardId);
		
			$this->_currentPageUrl = $this->gEnv->createCurrentPageUrl();	// 現在のページのURL
			$this->_autolink = $this->_configArray[self::CF_AUTOLINK];		// リンクを自動作成
		}
	}
	/**
	 * テンプレートにデータ埋め込む
	 *
	 * _setTemplate()で指定したテンプレートファイルにデータを埋め込む。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。_setTemplate()と共有。
	 * @return								なし
	 */
	function _postAssign($request, &$param)
	{
		// Ajax処理のときは終了
		if ($param == 'ajax') return;
		
		// トップ画像
/*		$titleColor = $this->_configArray[self::CF_TITLE_COLOR];	// タイトルカラー
		$topImage = $this->_configArray[self::CF_TOP_IMAGE];
		if (!empty($topImage)){
			$topLink = $this->convertUrlToHtmlEntity($this->_configArray[self::CF_TOP_LINK]);		// トップ画像のリンク先
			$topImage = $this->gEnv->getCurrentWidgetImagesUrl() . '/' . $topImage;
			$topImage = '<img src="' . $topImage . '" border="0" />';
			if (!empty($topLink)) $topImage = '<a href="' . $topLink . '">' . $topImage . '</a>';
		}
		$this->tmpl->addVar("_widget", "top_image", $topImage);		// トップ画像
		
		// 掲示板タイトル、説明
		$title = $this->_configArray[self::CF_BBS_TITLE];			// 掲示板タイトル
		$this->tmpl->addVar("_widget", "bbs_title", $this->convertToDispString($title));
		$this->tmpl->addVar("_widget", "guide", $this->_configArray[self::CF_BBS_GUIDE]);*/
		
		// 共通のスタイル
		$bbsStyle = self::CSS_BLOG_STYLE;
		$bgImage = $this->gEnv->getCurrentWidgetImagesUrl() . '/' . $this->_configArray[self::CF_BG_IMAGE];
		$bgColor = $this->_configArray[self::CF_BG_COLOR];
		$textColor = $this->_configArray[self::CF_TEXT_COLOR];		// 文字色
//		$bbsStyle .= 'background-image:url(' . $bgImage . ');';
		$bbsStyle .= 'background-color:' . $bgColor . ';';
//		$bbsStyle .= 'color:' . $textColor . ';';
		$this->tmpl->addVar("_widget", "blog_style", $bbsStyle);
		$this->tmpl->addVar("top_link_area", "link_style_top", self::CSS_LINK_STYLE_TOP);// 上下のリンク部のスタイル
		$this->tmpl->addVar("_widget", "link_style_bottom", self::CSS_LINK_STYLE_BOTTOM);// 下のリンク部のスタイル
		$this->tmpl->addVar("_widget", "link_style_inner_bottom", self::CSS_LINK_STYLE_INNER_BOTTOM);// 内枠下のリンク部のスタイル
		
		$menuColor = $this->_configArray[self::CF_MENU_COLOR];		// メニュー背景色
		$menuStyle .= 'background-color:' . $menuColor . ';';
		$this->tmpl->addVar("_widget", "menu_style", $menuStyle);
		
		// メッセージカラーを設定
		if ($this->getMsgCount(1) > 0 || $this->getMsgCount(2) > 0){		// エラーメッセージが出力されているとき
			$errMessageColor = $this->_configArray[self::CF_ERR_MESSAGE_COLOR];		// エラーメッセージ色
			if (!empty($errMessageColor)){
				$errMessageStyle .= 'color:' . $errMessageColor . ';';
				$attr = 'style="' . $errMessageStyle . '"';
				$this->setMessageAttr($attr);
			}
		}
		
		// 追加CSSを作成
		$cssId = $this->gEnv->getCurrentWidgetId() . '_0';
		$this->tmpl->addVar("_widget", "css_id",	$cssId);	// CSS用ID
		
		// 遷移先を設定
		$this->tmpl->addVar("_widget", "home_url", $this->convertUrlToHtmlEntity($this->getUrl($this->gEnv->createCurrentPageUrl(), true)));
//		$this->tmpl->addVar("_widget", "subject_url",	$this->convertUrlToHtmlEntity($this->getUrl($this->gEnv->createCurrentPageUrl() . '&task=' . self::TASK_SUBJECT, true)));	// スレッド件名
//		$this->tmpl->addVar("_widget", "newthread_url",	$this->convertUrlToHtmlEntity($this->getUrl($this->gEnv->createCurrentPageUrl() . '&task=' . self::TASK_NEW_THREAD, true)));	// 新規スレッド作成
	}
	/**
	 * CSSデータをHTMLヘッダ部に設定
	 *
	 * CSSデータをHTMLのheadタグ内に追加出力する。
	 * _assign()よりも後に実行される。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。
	 * @return string 						CSS文字列。出力しない場合は空文字列を設定。
	 */
	function _addCssToHead($request, &$param)
	{
		return $this->_headCss;
	}
	/**
	 * ブログ定義値をDBから取得
	 *
	 * @param string $boardId	掲示板ID
	 * @return bool				true=取得成功、false=取得失敗
	 */
	function _loadConfig($boardId)
	{
		$this->_configArray = array();

		// BBS定義を読み込み
		$ret = $this->_db->getAllConfig($rows, $boardId);
		if ($ret){
			// 取得データを連想配列にする
			$configCount = count($rows);
			for ($i = 0; $i < $configCount; $i++){
				$key = $rows[$i]['mc_id'];
				$value = $rows[$i]['mc_value'];
				$this->_configArray[$key] = $value;
			}
		}
		return $ret;
	}
	/**
	 * 投稿文入力データのエラーチェック
	 *
	 * @param string $boardId		掲示板ID
	 * @param string $threadId		スレッドID
	 * @param string $name			ユーザ名
	 * @param string $email			Eメールアドレス
	 * @param string $message		投稿文
	 * @return bool 				true=正常終了、false=異常終了
	 */
	function checkMessageInput($boardId, $threadId, $name, $email, $message)
	{
		// 入力チェック
		$this->checkInput($threadId, 'スレッドID');
		$this->checkInput($message, 'メッセージ');
		
		if ($this->getMsgCount() == 0){
			// スレッドIDが正しいかチェック
			$ret = $this->_db->getThreadInfo($boardId, $threadId, $row);
			if ($ret){
				// レス可能かチェック
				$resCount = $this->_configArray[self::CF_THREAD_RES];
				if ($row['th_message_count'] > $resCount){
					$this->setUserErrorMsg('このスレッドは' . $resCount . 'を超えました。<br /> もう書けないので、新しいスレッドを立ててくださいです。。。');
				}
			} else {
				$this->setUserErrorMsg('ＥＲＲＯＲ：スレッドＩＤが不正です！');
			}
			
			// 本文
			if (strlen($message) > $this->_configArray[self::CF_MESSAGE_LENGTH]){
				$this->setUserErrorMsg('ＥＲＲＯＲ：本文が長すぎます！');
			}

			// 名前
			if (strlen($name) > $this->_configArray[self::CF_NAME_LENGTH]) $this->setUserErrorMsg('ＥＲＲＯＲ：名前が長すぎます！');
			
			// メールアドレス
			if (strlen($email) > $this->_configArray[self::CF_EMAIL_LENGTH]) $this->setUserErrorMsg('ＥＲＲＯＲ：メールアドレスが長すぎます！');
		}
		if ($this->getMsgCount() == 0){
			return true;
		} else {
			return false;
		}
	}
	/**
	 * アバターファイルのURLを取得
	 *
	 * @param string $memberId		会員ID
	 * @return string				URL
	 */
	function getAvatarUrl($memberId)
	{
		$avatarImagePath = $this->gEnv->getResourcePath() . self::AVATAR_DIR . '/' . $memberId . '.' . self::DEFAULT_AVATAR_FILE_EXT;
		if (file_exists($avatarImagePath)){		// アバターファイルが見つからないときはデフォルトを使用
			$avatarImageUrl = $this->gEnv->getResourceUrl() . self::AVATAR_DIR . '/' . $memberId . '.' . self::DEFAULT_AVATAR_FILE_EXT;
		} else {
			$avatarImageUrl = $this->gEnv->getCurrentWidgetRootUrl() . self::DEFAULT_AVATAR_ICON_FILE;
		}
		return $avatarImageUrl;
	}
	/**
	 * メッセージを表示用に変換
	 *
	 * @param string $message		変換元メッセージ
	 * @param string $threadId		スレッドID
	 * @return string				変換後メッセージ
	 */
	function convDispMessage($message, $threadId)
	{
		// リンク変換
		if (!empty($this->_autolink)){		// 自動リンク作成のとき
			$message = preg_replace("/(https?):\/\/([\w;\/\?:\@&=\+\$,\-\.!~\*'\(\)%#]+)/", "<a href=\"$1://$2\" target=\"_blank\">$1://$2</a>", $message);
		
			// メッセージへのリンク
		//	$baseUrl = $this->convertUrlToHtmlEntity($this->getUrl($this->_currentPageUrl . '&' . M3_REQUEST_PARAM_BBS_THREAD_ID . '=' . $threadId, true));
			$messageUrl = $baseUrl . $this->convertUrlToHtmlEntity('&' . M3_REQUEST_PARAM_ITEM_NO . '=');
			$messageListUrl = $baseUrl . $this->convertUrlToHtmlEntity('&' . M3_REQUEST_PARAM_LIST_NO . '=');
			$message = preg_replace("/&gt;&gt;([0-9]+)(?![-\d])/", '<a href="' . $messageUrl . '$1" target="_blank">&gt;&gt;$1</a>', $message);
			$message = preg_replace("/&gt;&gt;([0-9]+)\-([0-9]+)/", '<a href="' . $messageListUrl . '$1-$2" target="_blank">&gt;&gt;$1-$2</a>', $message);
		}
		return $message;
	}
	/**
	 * ランダム文字列を作成
	 *
	 * @param string $baseChars		使用する文字
	 * @param int $length			作成する文字列の長さ
	 * @return string				ランダム文字列。作成できなかった場合は空文字列。
	 */
	function _createRandString($baseChars, $length)
	{
    	// 文字列の初期化
    	$destStr = '';

		if (!(is_numeric($length) && $length > 0)) return $destStr;
    
		for ($i = 0; $i < $length; $i++){
			$pos = rand(0, strlen($baseChars) -1);
			$destStr .= $baseChars[$pos];
		}
		return $destStr;
	}
	/**
	 * テキストデータを表示用のテキストに変換
	 *
	 * 変換内容　・改行コードをスペース「&nbsp;」に変換
	 *
	 * @param string $src			変換するデータ
	 * @return string				変換後データ
	 */
	function _convertToPreviewTextWithSpace($src)
	{
		return preg_replace("/(\015\012)|(\015)|(\012)/", "&nbsp;&nbsp;", $src);
	}
}
?>
