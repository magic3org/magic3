<?php
/**
 * index.php用コンテナクラス
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2011 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: bbs_2ch_mainBaseWidgetContainer.php 4019 2011-03-06 11:37:15Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/bbs_2ch_mainDb.php');

class bbs_2ch_mainBaseWidgetContainer extends BaseWidgetContainer
{
	protected $_db;			// DB接続オブジェクト
	protected $_configArray;		// BBS定義値
	protected $_headCss;	// CSS定義
	protected $_boardId;	// 掲示板ID
	protected $_currentPageUrl;	// 現在のページのURL
	protected $_autolink;		// リンクを自動作成
	const MACRO_RES_MAX_NO = 'RES_MAX_NO';		// レス上限数マクロ
	const DEFAULT_BBS_ID = 'board1';		// デフォルトの掲示板ID
	const DEFAULT_LAST_MESSAGE_COUNT = 50;	// メッセージ取得用デフォルトの最新メッセージ数
	const DEFAULT_MESSAGE_COUNT_PER_PAGE = 100;	// メッセージ取得用デフォルトの1ページあたりメッセージ数
	const DEFAULT_BOTTOM_MESSAGE = '<center><b>どのような形の削除依頼であれ公開させていただきます。</b></center>';		// デフォルトのトップ画面下部メッセージ
	const DEFAULT_THREAD_END_MESSAGE = "このスレッドは[#RES_MAX_NO#]を超えました。\r\nもう書けないので、新しいスレッドを立ててくださいです。。。";		// デフォルトのレス上限メッセージ
	const DEFAULT_EMAIL_NOT_UPDATE = 'sage';		// スレッドの日付更新を行わないEmailアドレス
	const DEFAULT_ADMIN_NAME = 'サイト運営者';				// サイト運営者名
	const CF_BBS_TITLE = 'title';			// 掲示板タイトル
	const CF_TITLE_COLOR = 'title_color';	// タイトルカラー
	const CF_TOP_LINK = 'top_link';		// トップ画像のリンク先
	const CF_TOP_IMAGE = 'top_image';		// トップ画像
	const CF_BBS_GUIDE = 'bbs_guide';		// 掲示板規則
	const CF_BOTTOM_MESSAGE = 'bottom_message';		// トップ画面下部メッセージ
	const CF_THREAD_END_MESSAGE = 'thread_end_message';		// レス上限メッセージ
	const CF_BG_IMAGE = 'bg_image';		// 背景画像
	const CF_BG_COLOR = 'bg_color';		// 背景色
	const CF_TEXT_COLOR = 'text_color';		// 文字色
	const CF_MENU_COLOR = 'menu_color';		// メニュー背景色
	const CF_MAKE_THREAD_COLOR = 'makethread_color';		// スレッド作成部背景色
	const CF_THREAD_COLOR = 'thread_color';		// スレッド表示部背景色
	const CF_LINK_COLOR = 'link_color';		// リンク色
	const CF_ALINK_COLOR = 'alink_color';		// リンク色
	const CF_VLINK_COLOR = 'vlink_color';		// リンク色
	const CF_NAME_COLOR = 'name_color';			// 投稿者名文字色
	const CF_FILE_UPLOAD = 'file_upload';		// ファイルアップロード許可
	const CF_SUBJECT_LENGTH = 'subject_length';		// 件名最大長
	const CF_NAME_LENGTH = 'name_length';		// 投稿者名最大長
	const CF_EMAIL_LENGTH = 'email_length';		// emailアドレス最大長
	const CF_MESSAGE_LENGTH = 'message_length';	// 最大メッセージ長
	const CF_ERR_MESSAGE_COLOR = 'err_message_color';		// エラーメッセージ文字色
	const CF_SUBJECT_COLOR = 'subject_color';		// 件名文字色
	const CF_LINE_LENGTH = 'line_length';		// 投稿文行長
	const CF_LINE_COUNT = 'line_count';			// 投稿文行数
	const CF_RES_ANCHOR_LINK_COUNT = 'res_anchor_link_count';		// レスアンカーリンク数
	const CF_THREAD_COUNT = 'thread_count';		// トップ画面に表示するスレッド最大数
	const CF_RES_COUNT = 'res_count';		// トップ画面に表示するレス最大数
	const CF_THREAD_RES = 'thread_res';		// 1スレッドに投稿できるレス番号の上限
	const CF_MENU_THREAD_COUNT = 'menu_thread_count';	// メニューに表示するスレッド最大数
	const CF_SHOW_EMAIL = 'show_email';		// Eメールアドレスを表示
	const CF_AUTOLINK = 'autolink';			// 自動的にリンクを作成
	const CF_NONAME_NAME = 'noname_name';				// 名前未設定時の表示名
	const CF_ADMIN_NAME = 'admin_name';				// サイト運営者名
	// 画面
	const TASK_TOP = 'top';			// トップ画面
	const TASK_SUBJECT = 'subject';			// スレッド件名
	const TASK_THREAD = 'thread';			// スレッド処理
	const TASK_NEW_THREAD = 'newthread';		// スレッド新規作成
	const TASK_READ_THREAD = 'read';		// スレッド表示
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->_db = new bbs_2ch_mainDb();
		
		// BBS定義を読み込む
		$this->_loadConfig();
		
		$this->_boardId = self::DEFAULT_BBS_ID;
		$this->_currentPageUrl = $this->gEnv->createCurrentPageUrl();	// 現在のページのURL
		$this->_autolink = $this->_configArray[self::CF_AUTOLINK];		// リンクを自動作成
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
		// トップ画像
		$titleColor = $this->_configArray[self::CF_TITLE_COLOR];	// タイトルカラー
		$topImage = $this->_configArray[self::CF_TOP_IMAGE];
		if (!empty($topImage)){
			$topLink = $this->convertUrlToHtmlEntity($this->_configArray[self::CF_TOP_LINK]);		// トップ画像のリンク先
			
			// パスの修正
			$pos = strpos($topImage, '/');
			if ($pos === false){		// ファイル名だけのとき
				$topImage = $this->gEnv->getCurrentWidgetImagesUrl() . '/' . $topImage;
			} else {
				$topImage = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $this->gEnv->getRootUrl(), $topImage);
			}
			$topImage = '<img src="' . $topImage . '" border="0" />';
			if (!empty($topLink)) $topImage = '<a href="' . $topLink . '">' . $topImage . '</a>';
		}
		$this->tmpl->addVar("_widget", "top_image", $topImage);		// トップ画像
		
		// 掲示板タイトル、説明
		$title = $this->_configArray[self::CF_BBS_TITLE];			// 掲示板タイトル
		$this->tmpl->addVar("_widget", "bbs_title", $this->convertToDispString($title));
		$this->tmpl->addVar("_widget", "guide", $this->_configArray[self::CF_BBS_GUIDE]);
		
		// 共通のスタイル
		$bbsStyle = '';
		$bgImage = $this->_configArray[self::CF_BG_IMAGE];
		$pos = strpos($bgImage, '/');
		if ($pos === false){		// ファイル名だけのとき
			$bgImage = $this->gEnv->getCurrentWidgetImagesUrl() . '/' . $bgImage;
		} else {
			$bgImage = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $this->gEnv->getRootUrl(), $bgImage);
		}
		$bgColor = $this->_configArray[self::CF_BG_COLOR];
		$textColor = $this->_configArray[self::CF_TEXT_COLOR];		// 文字色
		$bbsStyle .= 'background-image:url(' . $bgImage . ');';
		$bbsStyle .= 'background-color:' . $bgColor . ';';
		$bbsStyle .= 'color:' . $textColor . ';';
		$this->tmpl->addVar("_widget", "bbs_style", $bbsStyle);
		
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
		$this->_headCss .= '#' . $cssId . ' a {text-decoration:underline;}' . M3_NL;
		$this->_headCss .= '#' . $cssId . ' a:link {color:' . $this->_configArray[self::CF_LINK_COLOR] . ';}' . M3_NL;
		$this->_headCss .= '#' . $cssId . ' a:active {color:' . $this->_configArray[self::CF_ALINK_COLOR] . ';}' . M3_NL;
		//$this->_headCss .= '#' . $cssId . ' a:hover {color:' . $this->_configArray[self::CF_VLINK_COLOR] . ';}' . M3_NL;
		$this->_headCss .= '#' . $cssId . ' a:visited {color:' . $this->_configArray[self::CF_VLINK_COLOR] . ';}' . M3_NL;
		$this->tmpl->addVar("_widget", "css_id",	$cssId);	// CSS用ID
		
		// 遷移先を設定
		$this->tmpl->addVar("_widget", "bbs_url", $this->convertUrlToHtmlEntity($this->getUrl($this->gEnv->createCurrentPageUrl(), true)));
		$this->tmpl->addVar("_widget", "subject_url",	$this->convertUrlToHtmlEntity($this->getUrl($this->gEnv->createCurrentPageUrl() . '&task=' . self::TASK_SUBJECT, true)));	// スレッド件名
		$this->tmpl->addVar("_widget", "newthread_url",	$this->convertUrlToHtmlEntity($this->getUrl($this->gEnv->createCurrentPageUrl() . '&task=' . self::TASK_NEW_THREAD, true)));	// 新規スレッド作成
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
	 * BBS定義値をDBから取得
	 *
	 * @return bool			true=取得成功、false=取得失敗
	 */
	function _loadConfig()
	{
		$this->_configArray = array();

		// BBS定義を読み込み
		$ret = $this->_db->getAllConfig($rows);
		if ($ret){
			// 取得データを連想配列にする
			$configCount = count($rows);
			for ($i = 0; $i < $configCount; $i++){
				$key = $rows[$i]['tg_id'];
				$value = $rows[$i]['tg_value'];
				$this->_configArray[$key] = $value;
			}
		}
		return $ret;
	}
	/**
	 * 投稿文入力データのエラーチェック
	 *
	 * @param string $boardId		掲示板ID
	 * @param string $threadId		スレッドID(-1のときチェックなし)
	 * @param string $name			ユーザ名
	 * @param string $email			Eメールアドレス
	 * @param string $message		投稿文
	 * @return bool 				true=正常終了、false=異常終了
	 */
	function checkMessageInput($boardId, $threadId, $name, $email, $message)
	{
		// 入力チェック
		if ($threadId != -1) $this->checkInput($threadId, 'スレッドID');
		$this->checkInput($message, '本文', 'ＥＲＲＯＲ：本文がありません！');
		
		if ($this->getMsgCount() == 0){
			// スレッドIDが正しいかチェック
			if ($threadId != -1){
				$ret = $this->_db->getThreadInfo($boardId, $threadId, $row);
				if ($ret){
					// レス可能かチェック
					$resCount = $this->_configArray[self::CF_THREAD_RES];
					if ($row['th_message_count'] >= $resCount){
						$this->setUserErrorMsg('このスレッドは' . $resCount . 'を超えました。<br /> もう書けないので、新しいスレッドを立ててくださいです。。。');
					}
			
				// 書き込み可能かチェック
		/*#.datが存在してないか書けないならばいばい
	if (!is_writable($DATAFILE)) DispError("ＥＲＲＯＲ！","ＥＲＲＯＲ：このスレッドには書けません！");*/
				} else {
					$this->setUserErrorMsg('ＥＲＲＯＲ：スレッドＩＤが不正です！');
				}
			}
			
			// 本文の文字数を取得
			if (function_exists('mb_strlen')){
				$length = mb_strlen($message);
			} else {
				$length = strlen($message);
			}
			if ($length > $this->_configArray[self::CF_MESSAGE_LENGTH]){
				$this->setUserErrorMsg('ＥＲＲＯＲ：本文が長すぎます！(最大文字数' . $this->_configArray[self::CF_MESSAGE_LENGTH] . ')');
			} else {
				// 行数のチェック
				$lines = preg_split("/(\015\012)|(\015)|(\012)/", $message);
				if (count($lines) > $this->_configArray[self::CF_LINE_COUNT]) $this->setUserErrorMsg('ＥＲＲＯＲ：改行が多すぎます！');
				
				// 1行の長さのチェック
				$maxLineLength = $this->_configArray[self::CF_LINE_LENGTH];
				foreach ($lines as $line) {
					// 文字数を取得
					if (function_exists('mb_strlen')){
						$length = mb_strlen($line);
					} else {
						$length = strlen($line);
					}
					if ($length > $maxLineLength){
						$this->setUserErrorMsg('ＥＲＲＯＲ：長すぎる行があります！(最大文字数' . $maxLineLength . ')');
						break;
					}
				}
				
				// レスアンカーリンク数チェック
				if (preg_match_all("/>>[0-9]/", $message, $matches) > $this->_configArray[self::CF_RES_ANCHOR_LINK_COUNT]) $this->setUserErrorMsg('レスアンカーリンクが多すぎます！');
			}

			// 名前
			if (function_exists('mb_strlen')){
				$length = mb_strlen($name);
			} else {
				$length = strlen($name);
			}
			if ($length > $this->_configArray[self::CF_NAME_LENGTH]) $this->setUserErrorMsg('ＥＲＲＯＲ：名前が長すぎます！(最大文字数' . $this->_configArray[self::CF_NAME_LENGTH] . ')');
			
			// メールアドレス
			if (function_exists('mb_strlen')){
				$length = mb_strlen($email);
			} else {
				$length = strlen($email);
			}
			if ($length > $this->_configArray[self::CF_EMAIL_LENGTH]) $this->setUserErrorMsg('ＥＲＲＯＲ：メールアドレスが長すぎます！(最大文字数' . $this->_configArray[self::CF_EMAIL_LENGTH] . ')');
		}
		if ($this->getMsgCount() == 0){
			return true;
		} else {
			return false;
		}
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
			$baseUrl = $this->convertUrlToHtmlEntity($this->getUrl($this->_currentPageUrl . '&' . M3_REQUEST_PARAM_BBS_THREAD_ID . '=' . $threadId, true));
			$messageUrl = $baseUrl . $this->convertUrlToHtmlEntity('&' . M3_REQUEST_PARAM_ITEM_NO . '=');
			$messageListUrl = $baseUrl . $this->convertUrlToHtmlEntity('&' . M3_REQUEST_PARAM_LIST_NO . '=');
			$message = preg_replace("/&gt;&gt;([0-9]+)(?![-\d])/", '<a href="' . $messageUrl . '$1" target="_blank">&gt;&gt;$1</a>', $message);
			$message = preg_replace("/&gt;&gt;([0-9]+)\-([0-9]+)/", '<a href="' . $messageListUrl . '$1-$2" target="_blank">&gt;&gt;$1-$2</a>', $message);
		}
		return $message;
	}
}
?>
