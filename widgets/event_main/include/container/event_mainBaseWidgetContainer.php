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
require_once($gEnvManager->getContainerPath() . '/baseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetContainerPath() . '/event_mainCommonDef.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/event_mainDb.php');

class event_mainBaseWidgetContainer extends BaseWidgetContainer
{
//	protected static $_localDb;			// DB接続オブジェクト
	protected static $_mainDb;			// DB接続オブジェクト
	protected static $_configArray;		// イベント定義値
	protected static $_paramObj;		// ウィジェットパラメータオブジェクト
	protected static $_canEditEntry;	// 記事が編集可能かどうか
	protected $_currentPageUrl;	// 現在のページのURL

	const DATE_RANGE_DELIMITER		= '～';				// 日時範囲用デリミター
	
	// 画面
	const TASK_TOP			= 'top';			// トップ画面
	const TASK_CALENDAR		= 'calendar';		// カレンダー画面
	const DEFAULT_TASK		= 'top';
	
	// カレンダー用スクリプト
	const CALENDAR_SCRIPT_FILE = '/jscalendar-1.0/calendar.js';		// カレンダースクリプトファイル
	const CALENDAR_LANG_FILE = '/jscalendar-1.0/lang/calendar-ja.js';	// カレンダー言語ファイル
	const CALENDAR_SETUP_FILE = '/jscalendar-1.0/calendar-setup.js';	// カレンダーセットアップファイル
	const CALENDAR_CSS_FILE = '/jscalendar-1.0/calendar-win2k-1.css';		// カレンダー用CSSファイル
	
	// リンク部CSS
	const CSS_LINK_STYLE_TOP = 'margin:0 10px;text-align:right;';	// 上のリンク部のスタイル
		
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();

		// 初期値設定
		$this->_currentPageUrl = $this->gEnv->createCurrentPageUrl();	// 現在のページのURL
		
		// サブウィジェット起動のときだけ初期処理実行
		if ($this->gEnv->getIsSubWidget()){
			// DBオブジェクト作成
			if (!isset(self::$_mainDb)) self::$_mainDb = new event_mainDb();
		
			// イベント定義を読み込む
			if (!isset(self::$_configArray)) self::$_configArray = event_mainCommonDef::loadConfig(self::$_mainDb);
		}
		/*
		// DBオブジェクト作成
		if (!isset(self::$_localDb)) self::$_localDb = new event_mainDb();
			
		// イベント定義を読み込む
		if (!isset(self::$_configArray)) self::$_configArray = event_mainCommonDef::loadConfig(self::$_mainDb);
		*/
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
		$this->tmpl->addVar("top_link_area", "link_style_top", self::CSS_LINK_STYLE_TOP);// 上下のリンク部のスタイル
	}
}
?>
