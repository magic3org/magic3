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
 * @copyright  Copyright 2006-2015 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseAdminWidgetContainer.php');
require_once($gEnvManager->getWidgetContainerPath('evententry_main') . '/evententryCommonDef.php');
require_once($gEnvManager->getWidgetDbPath('evententry_main') . '/evententry_mainDb.php');

class admin_evententry_mainBaseWidgetContainer extends BaseAdminWidgetContainer
{
	protected static $_mainDb;			// DB接続オブジェクト
	protected static $_configArray;		// イベント参加定義値
	protected static $_task;			// 現在の画面
	protected $_baseUrl;			// 管理画面のベースURL
	protected $_contentType;		// コンテンツタイプ
	
	// カレンダー用スクリプト
	const CALENDAR_SCRIPT_FILE = '/jscalendar-1.0/calendar.js';		// カレンダースクリプトファイル
	const CALENDAR_LANG_FILE = '/jscalendar-1.0/lang/calendar-ja.js';	// カレンダー言語ファイル
	const CALENDAR_SETUP_FILE = '/jscalendar-1.0/calendar-setup.js';	// カレンダーセットアップファイル
	const CALENDAR_CSS_FILE = '/jscalendar-1.0/calendar-win2k-1.css';		// カレンダー用CSSファイル
	
	// 画面
	const TASK_CONFIG			= 'config';				// 基本設定
	const TASK_EVENT			= 'event';				// イベント一覧
	const TASK_EVENT_DETAIL 	= 'event_detail';		// イベント詳細
	const TASK_REQUEST			= 'request';			// 参加要求一覧
	const TASK_REQUEST_DETAIL	= 'request_detail';		// 参加要求詳細
	const DEFAULT_TASK			= 'event';
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		if (!isset(self::$_mainDb)) self::$_mainDb = new evententry_mainDb();
		
		// DB定義を読み込む
		if (!isset(self::$_configArray)) self::$_configArray = evententryCommonDef::loadConfig(self::$_mainDb);
	}
	/**
	 * テンプレートに前処理
	 *
	 * _setTemplate()で指定したテンプレートファイルにデータを埋め込む。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。_setTemplate()と共有。
	 * @return								なし
	 */
	function _preAssign($request, &$param)
	{
		$this->addOptionUrlParam(M3_REQUEST_PARAM_OPEN_BY, $this->_openBy);		// ウィンドウオープンタイプ
		
		// 管理画面ペースURL取得
		$this->_baseUrl = $this->getAdminUrlWithOptionParam(true);		// ページ定義パラメータ付加
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
		if ($this->_openBy == 'simple') return;			// シンプルウィンドウのときはメニューを表示しない
		
		// 表示画面を決定
		$task = self::$_task;			// 現在の画面を取得
		
		// パンくずリストの定義データ作成
		$titles = array();
		switch ($task){
			case self::TASK_EVENT:		// イベント一覧
				$titles[] = 'イベント参加管理';
				$titles[] = 'イベント一覧';
				break;
			case self::TASK_EVENT_DETAIL:		// イベント詳細
				$titles[] = 'イベント参加管理';
				$titles[] = 'イベント一覧';
				$titles[] = '詳細';
				break;
			case self::TASK_CONFIG:		// 基本設定
				$titles[] = '基本';
				$titles[] = '基本設定';
				break;
		}
		
		// メニューバーの定義データ作成
		$menu =	array(
					(Object)array(
						'name'		=> 'イベント参加管理',
						'task'		=> self::TASK_EVENT,
						'url'		=> '',
						'tagid'		=> '',
						'active'	=> (
											$task == self::TASK_EVENT ||			// イベント一覧
											$task == self::TASK_EVENT_DETAIL		// イベント詳細
										),
						'submenu'	=> array(
							(Object)array(
								'name'		=> 'イベント一覧',
								'task'		=> self::TASK_EVENT,
								'url'		=> '',
								'tagid'		=> '',
								'active'	=> (
													$task == self::TASK_EVENT ||			// イベント一覧
													$task == self::TASK_EVENT_DETAIL		// イベント詳細
												)
							)
						)
					),
					(Object)array(
						'name'		=> '基本',
						'task'		=> self::TASK_CONFIG,
						'url'		=> '',
						'tagid'		=> '',
						'active'	=> (
											$task == self::TASK_CONFIG
										),
						'submenu'	=> array(
							(Object)array(
								'name'		=> '基本設定',
								'task'		=> self::TASK_CONFIG,
								'url'		=> '',
								'tagid'		=> '',
								'active'	=> (
													$task == self::TASK_CONFIG					// 基本設定
												)
							)
						)
					)
				);

		// サブメニューバーを作成
		$this->setConfigMenubarDef($titles, $menu);
	}
}
?>
