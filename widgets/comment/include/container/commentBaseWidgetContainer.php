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
 * @copyright  Copyright 2006-2013 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: commentBaseWidgetContainer.php 6056 2013-05-30 13:45:24Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetContainerPath() . '/commentCommonDef.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/commentDb.php');

class commentBaseWidgetContainer extends BaseWidgetContainer
{
	protected static $_mainDb;			// DB接続オブジェクト
	protected static $_configArray;		// ブログ定義値
	protected static $_paramObj;		// ウィジェットパラメータオブジェクト
	protected static $_canEditEntry;	// 記事が編集可能かどうか
	protected $_langId;			// 現在の言語
	protected $_userId;			// 現在のユーザ
	const DEFAULT_COMMENT_LENGTH	= 300;				// デフォルトのコメント最大文字数
	const DEFAULT_CATEGORY_COUNT	= 2;				// デフォルトのカテゴリ数
	
	// 画面
	const TASK_TOP			= 'top';			// トップ画面
	const TASK_ENTRY		= 'entry';			// 記事編集画面
	const TASK_ENTRY_DETAIL = 'entry_detail';			// 記事編集画面詳細
	const TASK_COMMENT		= 'comment';		// ブログ記事コメント管理
	const TASK_COMMENT_DETAIL = 'comment_detail';		// ブログ記事コメント管理(詳細)
	const DEFAULT_TASK		= 'top';
			
	// アドオンオブジェクト用
	const BLOG_OBJ_ID = 'bloglib';		// ブログオブジェクトID
	
	// カレンダー用スクリプト
	const CALENDAR_SCRIPT_FILE = '/jscalendar-1.0/calendar.js';		// カレンダースクリプトファイル
	const CALENDAR_LANG_FILE = '/jscalendar-1.0/lang/calendar-ja.js';	// カレンダー言語ファイル
	const CALENDAR_SETUP_FILE = '/jscalendar-1.0/calendar-setup.js';	// カレンダーセットアップファイル
	const CALENDAR_CSS_FILE = '/jscalendar-1.0/calendar-win2k-1.css';		// カレンダー用CSSファイル
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();

		// DBオブジェクト作成
		if (!isset(self::$_mainDb)) self::$_mainDb = new commentDb();
				
		$this->_langId = $this->gEnv->getCurrentLanguage();			// 現在の言語
		$this->_userId = $this->gEnv->getCurrentUserId();		// 現在のユーザ
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
		$cmd = $request->trimValueOf(M3_REQUEST_PARAM_OPERATION_COMMAND);		// 実行コマンドを取得
		if ($cmd != M3_REQUEST_CMD_DO_WIDGET) return;		// 単体実行以外のときは終了
		
		$openBy = $request->trimValueOf(M3_REQUEST_PARAM_OPEN_BY);		// ウィンドウオープンタイプ
		//if (!empty($openBy)) $this->addOptionUrlParam(M3_REQUEST_PARAM_OPEN_BY, $openBy);
		if ($openBy == 'simple') return;			// シンプルウィンドウのときはメニューを表示しない
		
		// 表示画面を決定
		$task = $request->trimValueOf(M3_REQUEST_PARAM_OPERATION_TASK);
		if (empty($task)) $task = self::DEFAULT_TASK;
		$blogId = $request->trimValueOf(M3_REQUEST_PARAM_BLOG_ID);		// 所属ブログ
		
		// パンくずリストを作成
		switch ($task){
			case 'entry':		// ブログ記事
			case 'entry_detail':	// ブログ記事詳細
				$linkList = ' &gt;&gt; 記事';// パンくずリスト
				break;
			case 'comment':		// ブログ記事コメント
			case 'comment_detail':	// ブログ記事コメント
				$linkList = ' &gt;&gt; コメント';// パンくずリスト
				break;
/*			case 'user':		// ユーザ管理
			case 'user_detail':		// ユーザ管理(詳細)
				$linkList = ' &gt;&gt; ユーザ管理 &gt;&gt; ユーザ一覧';// パンくずリスト
				break;
			case 'config':		// ブログ設定
				$linkList = ' &gt;&gt; 基本設定 &gt;&gt; ブログ設定';// パンくずリスト
				break;
			case 'category':		// カテゴリ設定
			case 'category_detail':		// カテゴリ設定
				$linkList = ' &gt;&gt; 基本設定 &gt;&gt; カテゴリ';// パンくずリスト
				break;
			case 'blogid':		// マルチブログ設定
			case 'blogid_detail':		// マルチブログ設定
				$linkList = ' &gt;&gt; 基本設定 &gt;&gt; マルチブログ';// パンくずリスト
				break;*/
		}
		// ベースURL作成
		$urlparam  = M3_REQUEST_PARAM_OPERATION_COMMAND . '=' . M3_REQUEST_CMD_DO_WIDGET . '&';
		$urlparam .= M3_REQUEST_PARAM_WIDGET_ID . '=' . $this->gEnv->getCurrentWidgetId() .'&';
		$urlparam .= 'openby=other&' . M3_REQUEST_PARAM_BLOG_ID . '=' . $blogId;
		$baseUrl = $this->gEnv->getDefaultUrl() . '?' . $urlparam;
				
		// ####### 上段メニューの作成 #######
/*		$menuText = '<div id="configmenu-upper">' . M3_NL;
		$menuText .= '<ul>' . M3_NL;
		
		$current = '';
		//$baseUrl = $this->getAdminUrlWithOptionParam();
		
		// ブログ記事管理
		$current = '';
		$link = $baseUrl . '&task=entry';
		if ($task == 'entry' ||
			$task == 'entry_detail' ||
			$task == 'comment' ||		// ブログ記事コメント管理
			$task == 'comment_detail'){
			$current = 'id="current"';
		}
		$menuText .= '<li ' . $current . '><a href="'. $this->getUrl($link) .'"><span>ブログ記事</span></a></li>' . M3_NL;
		
		// 基本設定
		$current = '';
		$link = $baseUrl . '&task=category';
		if ($task == 'category' ||		// カテゴリ設定
			$task == 'category_detail' ||		// カテゴリ設定
			$task == 'blogid' ||		// マルチブログ
			$task == 'blogid_detail' ||		// マルチブログ詳細
			$task == 'config'){		// ブログ設定
			$current = 'id="current"';
		}
		$menuText .= '<li ' . $current . '><a href="'. $this->getUrl($link) .'"><span>基本設定</span></a></li>' . M3_NL;
		
		// 上段メニュー終了
		$menuText .= '</ul>' . M3_NL;
		$menuText .= '</div>' . M3_NL;*/
		
		// ####### 下段メニューの作成 #######		
		$menuText .= '<div id="configmenu-lower">' . M3_NL;
		$menuText .= '<ul>' . M3_NL;

		if ($task == 'entry' ||		// ブログ記事管理
			$task == 'entry_detail' ||
			$task == 'comment' ||		// ブログ記事コメント管理
			$task == 'comment_detail'){
			
			// ブログ記事一覧
			$current = '';
			//$link = $baseUrl . '&task=entry';
			$link = $baseUrl . '&task=entry_detail';		// 詳細をデフォルトにする
			if ($task == 'entry' || $task == 'entry_detail') $current = 'id="current"';
			$menuText .= '<li ' . $current . '><a href="'. $this->getUrl($link) .'"><span>記事</span></a></li>' . M3_NL;
			
			// ブログ記事コメント一覧
			$current = '';
			$link = $baseUrl . '&task=comment';
			if ($task == 'comment' || $task == 'comment_detail') $current = 'id="current"';
			$menuText .= '<li ' . $current . '><a href="'. $this->getUrl($link) .'"><span>コメント</span></a></li>' . M3_NL;
/*		} else if ($task == 'category' ||		// カテゴリ設定
			$task == 'category_detail' ||		// カテゴリ設定
			$task == 'blogid' ||		// マルチブログ
			$task == 'blogid_detail' ||		// マルチブログ詳細
			$task == 'config'){		// ブログ設定
			
			// カテゴリ設定
			$current = '';
			$link = $baseUrl . '&task=category';
			if ($task == 'category' || $task == 'category_detail') $current = 'id="current"';
			$menuText .= '<li ' . $current . '><a href="'. $this->getUrl($link) .'"><span>カテゴリ</span></a></li>' . M3_NL;
			
			// マルチブログ
			$current = '';
			$link = $baseUrl . '&task=blogid';
			if ($task == 'blogid' || $task == 'blogid_detail') $current = 'id="current"';
			$menuText .= '<li ' . $current . '><a href="'. $this->getUrl($link) .'"><span>マルチブログ</span></a></li>' . M3_NL;
			
			// その他設定
			$current = '';
			$link = $baseUrl . '&task=config';
			if ($task == 'config') $current = 'id="current"';
			$menuText .= '<li ' . $current . '><a href="'. $this->getUrl($link) .'"><span>ブログ設定</span></a></li>' . M3_NL;
	*/
		}
		
		// 下段メニュー終了
		$menuText .= '</ul>' . M3_NL;
		$menuText .= '</div>' . M3_NL;

		// 作成データの埋め込み
		$linkList = '<div id="configmenu-top"><label>' . 'ブログ' . $linkList . '</div>';
		$outputText .= '<table width="90%"><tr><td>' . $linkList . $menuText . '</td></tr></table>' . M3_NL;
		$this->tmpl->addVar("_widget", "menu_items", $outputText);
	}
	/**
	 * ブログ定義値をDBから取得
	 *
	 * @return bool			true=取得成功、false=取得失敗
	 */
/*	function _loadConfig()
	{
		self::$_configArray = array();

		// ブログ定義を読み込み
		//$ret = $this->_db->getAllConfig($rows);
		$ret = self::$_localDb->getAllConfig($rows);
		if ($ret){
			// 取得データを連想配列にする
			$configCount = count($rows);
			for ($i = 0; $i < $configCount; $i++){
				$key = $rows[$i]['bg_id'];
				$value = $rows[$i]['bg_value'];
				self::$_configArray[$key] = $value;
			}
		}
		return $ret;
	}*/
}
?>
