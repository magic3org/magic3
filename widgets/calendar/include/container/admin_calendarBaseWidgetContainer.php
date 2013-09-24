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
	const TASK_DAYTYPE			= 'daytype';					// 日付タイプ一覧
	const TASK_DAYTYPE_DETAIL	= 'daytype_detail';				// 日付タイプ詳細
	const TASK_CONFIG			= 'config';				// 基本設定
	const TASK_CONFIG_LIST		= 'config_list';		// 設定一覧
	const DEFAULT_TASK			= 'config';

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
			case self::TASK_DAYTYPE:					// 日付タイプ一覧
			case self::TASK_DAYTYPE_DETAIL:				// 日付タイプ詳細
				$linkList = ' &gt;&gt; 日付タイプ';
				break;
		}

		// ####### 上段メニューの作成 #######
		$menuText = '<div id="configmenu-upper">' . M3_NL;
		$menuText .= '<ul>' . M3_NL;
	
		$current = '';
		$baseUrl = $this->getAdminUrlWithOptionParam(true);// 画面定義ID付き
	
		// 日付タイプ
		$current = '';
		$link = $this->getUrl($baseUrl . '&task=' . self::TASK_DAYTYPE);
		if ($task == self::TASK_DAYTYPE ||			// 日付タイプ一覧
			$task == self::TASK_DAYTYPE_DETAIL){	// 日付タイプ詳細
			$current = 'id="current"';
		}
		$menuText .= '<li ' . $current . '><a href="'. $this->convertUrlToHtmlEntity($link) .'"><span>日付タイプ</span></a></li>' . M3_NL;
	
	
		// 上段メニュー終了
		$menuText .= '</ul>' . M3_NL;
		$menuText .= '</div>' . M3_NL;
	
		// 作成データの埋め込み
		$linkList = '<div id="configmenu-top"><label>' . '汎用カレンダー' . $linkList . '</label></div>';
		$outputText .= '<table width="90%"><tr><td>' . $linkList . $menuText . '</td></tr></table>' . M3_NL;
		$this->tmpl->addVar("_widget", "menu_items", $outputText);
	}
}
?>
