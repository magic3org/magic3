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
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseAdminWidgetContainer.php');
require_once($gEnvManager->getWidgetContainerPath('calendar') . '/default_calendarCommonDef.php');
require_once($gEnvManager->getWidgetDbPath('calendar') . '/calendarDb.php');

class admin_calendarBaseWidgetContainer extends BaseAdminWidgetContainer
{
	protected static $_mainDb;			// DB接続オブジェクト
	
	// 画面
	const TASK_DATE				= 'date';				// 日付管理
	const TASK_DATE_DETAIL		= 'date_detail';		// 日付管理詳細
	const TASK_DATETYPE			= 'datetype';					// 日付タイプ一覧
	const TASK_DATETYPE_DETAIL	= 'datetype_detail';				// 日付タイプ詳細
	const TASK_CONFIG			= 'config';				// 基本設定
	const TASK_CONFIG_LIST		= 'config_list';		// 設定一覧
	const TASK_EVENT			= 'event';				// 簡易イベント管理
	const TASK_EVENT_DETAIL		= 'event_detail';		// 簡易イベント管理詳細
	const DEFAULT_TASK			= 'config';

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
		
		// 代替処理用のウィジェットIDを設定
		$this->setDefaultWidgetId(default_calendarCommonDef::CALENDAR_WIDGET_ID);
		
		// DBオブジェクト作成
		if (!isset(self::$_mainDb)) self::$_mainDb = new calendarDb();
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
		// ウィンドウオープンタイプ取得
		$openBy = $request->trimValueOf(M3_REQUEST_PARAM_OPEN_BY);		// ウィンドウオープンタイプ
		if (!empty($openBy)) $this->addOptionUrlParam(M3_REQUEST_PARAM_OPEN_BY, $openBy);
		if ($openBy == 'simple' || $openBy == 'tabs') return;			// シンプルウィンドウまたはタブ表示のときはメニューを表示しない
	
		// 表示画面を決定
		$task = $request->trimValueOf(M3_REQUEST_PARAM_OPERATION_TASK);
		if (empty($task)) $task = self::DEFAULT_TASK;		// デフォルト画面を設定

		// パンくずリストを作成
		switch ($task){
			case self::TASK_CONFIG:				// カレンダー設定
			case self::TASK_CONFIG_LIST:		// 設定一覧
				$linkList = ' &gt;&gt; カレンダー管理 &gt;&gt; カレンダー設定';
				break;
			case self::TASK_EVENT:				// 簡易イベント管理
			case self::TASK_EVENT_DETAIL:		// 簡易イベント管理詳細
				$linkList = ' &gt;&gt; カレンダー管理 &gt;&gt; 簡易イベント';
				break;
			case self::TASK_DATE:				// 日付管理
			case self::TASK_DATE_DETAIL:		// 日付管理詳細
				$linkList = ' &gt;&gt; 基本設定 &gt;&gt; 日付定義';
				break;
			case self::TASK_DATETYPE:					// 日付タイプ一覧
			case self::TASK_DATETYPE_DETAIL:				// 日付タイプ詳細
				$linkList = ' &gt;&gt; 基本設定 &gt;&gt; 日付タイプ';
				break;
		}

		// ####### 上段メニューの作成 #######
		$menuText = '<div id="configmenu-upper">' . M3_NL;
		$menuText .= '<ul>' . M3_NL;
	
		$current = '';
		$baseUrl = $this->getAdminUrlWithOptionParam(true);// 画面定義ID付き
	
		// カレンダー管理
		$current = '';
		$link = $this->getUrl($baseUrl . '&task=' . self::TASK_CONFIG);
		if ($task == self::TASK_CONFIG ||		// カレンダー定義
			$task == self::TASK_CONFIG_LIST ||	// カレンダー定義一覧
			$task == self::TASK_EVENT ||				// 簡易イベント管理
			$task == self::TASK_EVENT_DETAIL){		// 簡易イベント管理詳細
			$current = 'id="current"';
		}
		$menuText .= '<li ' . $current . '><a href="'. $this->convertUrlToHtmlEntity($link) .'"><span>カレンダー管理</span></a></li>' . M3_NL;
		
		// 基本設定
		$current = '';
		$link = $this->getUrl($baseUrl . '&task=' . self::TASK_DATE);
		if ($task == self::TASK_DATE ||				// 日付管理
			$task == self::TASK_DATE_DETAIL ||		// 日付管理詳細
			$task == self::TASK_DATETYPE ||			// 日付タイプ一覧
			$task == self::TASK_DATETYPE_DETAIL){	// 日付タイプ詳細
			$current = 'id="current"';
		}
		$menuText .= '<li ' . $current . '><a href="'. $this->convertUrlToHtmlEntity($link) .'"><span>基本設定</span></a></li>' . M3_NL;
	
		// 上段メニュー終了
		$menuText .= '</ul>' . M3_NL;
		$menuText .= '</div>' . M3_NL;
	
		// ####### 下段メニューの作成 #######		
		$menuText .= '<div id="configmenu-lower">' . M3_NL;
		$menuText .= '<ul>' . M3_NL;

		if ($task == self::TASK_CONFIG ||		// カレンダー定義
			$task == self::TASK_CONFIG_LIST ||	// カレンダー定義一覧
			$task == self::TASK_EVENT ||				// 簡易イベント管理
			$task == self::TASK_EVENT_DETAIL){		// 簡易イベント管理詳細
			
			// カレンダー定義
			$current = '';
			$link = $this->getUrl($baseUrl . '&task=' . self::TASK_CONFIG);
			if ($task == self::TASK_CONFIG || $task == self::TASK_CONFIG_LIST) $current = 'id="current"';
			$menuText .= '<li ' . $current . '><a href="'. $this->getUrl($link) .'"><span>カレンダー設定</span></a></li>' . M3_NL;
			
			// 簡易イベント
			$current = '';
			$link = $this->getUrl($baseUrl . '&task=' . self::TASK_EVENT);
			if ($task == self::TASK_EVENT || $task == self::TASK_EVENT_DETAIL) $current = 'id="current"';
			$menuText .= '<li ' . $current . '><a href="'. $this->getUrl($link) .'"><span>簡易イベント</span></a></li>' . M3_NL;
		} else if ($task == self::TASK_DATE ||				// 日付管理
			$task == self::TASK_DATE_DETAIL ||		// 日付管理詳細
			$task == self::TASK_DATETYPE ||			// 日付タイプ一覧
			$task == self::TASK_DATETYPE_DETAIL){	// 日付タイプ詳細
			
			// 日付管理
			$current = '';
			$link = $this->getUrl($baseUrl . '&task=' . self::TASK_DATE);
			if ($task == self::TASK_DATE || $task == self::TASK_DATE_DETAIL) $current = 'id="current"';
			$menuText .= '<li ' . $current . '><a href="'. $this->getUrl($link) .'"><span>日付定義</span></a></li>' . M3_NL;
			
/*			// イベント管理
			$current = '';
			$link = $this->getUrl($baseUrl . '&task=' . self::TASK_DATE);
			if ($task == self::TASK_DATE || $task == self::TASK_DATE_DETAIL) $current = 'id="current"';
			$menuText .= '<li ' . $current . '><a href="'. $this->getUrl($link) .'"><span>イベント管理</span></a></li>' . M3_NL;*/
			
			// 日付タイプ
			$current = '';
			$link = $this->getUrl($baseUrl . '&task=' . self::TASK_DATETYPE);
			if ($task == self::TASK_DATETYPE || $task == self::TASK_DATETYPE_DETAIL) $current = 'id="current"';
			$menuText .= '<li ' . $current . '><a href="'. $this->getUrl($link) .'"><span>日付タイプ</span></a></li>' . M3_NL;
		}
		
		// 下段メニュー終了
		$menuText .= '</ul>' . M3_NL;
		$menuText .= '</div>' . M3_NL;
		
		// 作成データの埋め込み
		$linkList = '<div id="configmenu-top"><label>' . '汎用カレンダー' . $linkList . '</label></div>';
		$outputText .= '<table width="90%"><tr><td>' . $linkList . $menuText . '</td></tr></table>' . M3_NL;
		$this->tmpl->addVar("_widget", "menu_items", $outputText);
	}
}
?>
