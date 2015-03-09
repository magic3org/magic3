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
require_once($gEnvManager->getCurrentWidgetContainerPath() . '/event_mainCommonDef.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/event_mainDb.php');

class admin_event_mainBaseWidgetContainer extends BaseAdminWidgetContainer
{
	protected static $_mainDb;			// DB接続オブジェクト
	protected static $_configArray;		// イベント定義値

	// カレンダー用スクリプト
	const CALENDAR_SCRIPT_FILE = '/jscalendar-1.0/calendar.js';		// カレンダースクリプトファイル
	const CALENDAR_LANG_FILE = '/jscalendar-1.0/lang/calendar-ja.js';	// カレンダー言語ファイル
	const CALENDAR_SETUP_FILE = '/jscalendar-1.0/calendar-setup.js';	// カレンダーセットアップファイル
	const CALENDAR_CSS_FILE = '/jscalendar-1.0/calendar-win2k-1.css';		// カレンダー用CSSファイル
	
	// 画面
	const TASK_ENTRY			= 'entry';				// イベント記事(一覧)
	const TASK_ENTRY_DETAIL		= 'entry_detail';		// イベント記事(詳細)
	const TASK_HISTORY			= 'history';			// (未使用)イベント記事履歴
	const TASK_CATEGORY			= 'category';			// 記事カテゴリー(一覧)
	const TASK_CATEGORY_DETAIL	= 'category_detail';	// 記事カテゴリー(詳細)
	const TASK_CONFIG			= 'config';				// 基本設定
	const DEFAULT_TASK			= 'entry';				// デフォルトのタスク(イベント記事(一覧))
	
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
			if (!isset(self::$_mainDb)) self::$_mainDb = new event_mainDb();
		
			// イベント定義を読み込む
			if (!isset(self::$_configArray)) self::$_configArray = event_mainCommonDef::loadConfig(self::$_mainDb);
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
		$openBy = $request->trimValueOf(M3_REQUEST_PARAM_OPEN_BY);		// ウィンドウオープンタイプ
		if (!empty($openBy)) $this->addOptionUrlParam(M3_REQUEST_PARAM_OPEN_BY, $openBy);
		if ($openBy == 'simple') return;			// シンプルウィンドウのときはメニューを表示しない
		
		// 表示画面を決定
		$task = $request->trimValueOf(M3_REQUEST_PARAM_OPERATION_TASK);
		if (empty($task)) $task = self::DEFAULT_TASK;
		
		// パンくずリストの定義データ作成
		$titles = array();
		switch ($task){
			case self::TASK_ENTRY:				// イベント記事(一覧)
				$titles[] = 'イベント記事管理';
				$titles[] = '記事一覧';
				break;
			case self::TASK_ENTRY_DETAIL:		// イベント記事(詳細)
				$titles[] = 'イベント記事管理';
				$titles[] = '記事一覧';
				$titles[] = '詳細';
				break;
			case self::TASK_HISTORY:			// イベント記事履歴
				$titles[] = 'イベント記事管理';
				$titles[] = '記事一覧';
				$titles[] = '詳細';
				$titles[] = '履歴';
				break;
			case self::TASK_CATEGORY:			// 記事カテゴリー(一覧)
				$titles[] = '基本';
				$titles[] = '記事カテゴリー';
				break;
			case self::TASK_CATEGORY_DETAIL:	// 記事カテゴリー(詳細)
				$titles[] = '基本';
				$titles[] = '記事カテゴリー';
				$titles[] = '詳細';
				break;
			case self::TASK_CONFIG:				// 基本設定
				$titles[] = '基本';
				$titles[] = '基本設定';
				break;
		}
		
		// メニューバーの定義データ作成
		$menu =	array(
					(Object)array(
						'name'		=> 'イベント記事管理',
						'task'		=> '',
						'url'		=> '',
						'tagid'		=> '',
						'active'	=> (
											$task == self::TASK_ENTRY ||				// イベント記事(一覧)
											$task == self::TASK_ENTRY_DETAIL ||		// イベント記事(詳細)
											$task == self::TASK_HISTORY 			// イベント記事履歴
										),
						'submenu'	=> array(
							(Object)array(
								'name'		=> '記事一覧',
								'task'		=> self::TASK_ENTRY,
								'url'		=> '',
								'tagid'		=> '',
								'active'	=> (
													$task == self::TASK_ENTRY ||			// イベント記事(一覧)
													$task == self::TASK_ENTRY_DETAIL ||		// イベント記事(詳細)
													$task == self::TASK_HISTORY				// イベント記事履歴
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
											$task == self::TASK_CATEGORY ||			// 記事カテゴリー(一覧)
											$task == self::TASK_CATEGORY_DETAIL ||	// 記事カテゴリー(詳細)
											$task == self::TASK_CONFIG					// 基本設定
										),
						'submenu'	=> array(
							(Object)array(
								'name'		=> '記事カテゴリー',
								'task'		=> self::TASK_CATEGORY,
								'url'		=> '',
								'tagid'		=> '',
								'active'	=> (
													$task == self::TASK_CATEGORY ||			// 記事カテゴリー(一覧)
													$task == self::TASK_CATEGORY_DETAIL		// 記事カテゴリー(詳細)
												)
							),
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
/*
		// パンくずリストを作成
		switch ($task){
			case 'entry':		// イベント記事
			case 'entry_detail':	// イベント記事詳細
				$linkList = ' &gt;&gt; イベント記事 &gt;&gt; 記事一覧';// パンくずリスト
				break;
			case 'comment':		// イベント記事コメント
			case 'comment_detail':	// イベント記事コメント
				$linkList = ' &gt;&gt; イベント記事 &gt;&gt; コメント一覧';// パンくずリスト
				break;
			case 'user':		// ユーザ管理
			case 'user_detail':		// ユーザ管理(詳細)
				$linkList = ' &gt;&gt; ユーザ管理 &gt;&gt; ユーザ一覧';// パンくずリスト
				break;
			case 'config':		// イベント設定
				$linkList = ' &gt;&gt; 基本設定 &gt;&gt; イベント設定';// パンくずリスト
				break;
			case 'category':		// カテゴリー設定
			case 'category_detail':		// カテゴリー設定
				$linkList = ' &gt;&gt; 基本設定 &gt;&gt; カテゴリー';// パンくずリスト
				break;
		}

		// ####### 上段メニューの作成 #######
		$menuText = '<div id="configmenu-upper">' . M3_NL;
		$menuText .= '<ul>' . M3_NL;
		
		$current = '';
		$baseUrl = $this->getAdminUrlWithOptionParam();
		
		// イベント記事管理
		$current = '';
		$link = $baseUrl . '&task=entry';
		if ($task == 'entry' ||
			$task == 'entry_detail' ||
			$task == 'comment' ||		// イベント記事コメント管理
			$task == 'comment_detail'){
			$current = 'id="current"';
		}
		$menuText .= '<li ' . $current . '><a href="'. $this->getUrl($link) .'"><span>イベント記事</span></a></li>' . M3_NL;
		
		// 基本設定
		$current = '';
		$link = $baseUrl . '&task=category';
		if ($task == 'category' ||		// カテゴリー設定
			$task == 'category_detail' ||		// カテゴリー設定
			$task == 'config'){		// イベント設定
			$current = 'id="current"';
		}
		$menuText .= '<li ' . $current . '><a href="'. $this->getUrl($link) .'"><span>基本設定</span></a></li>' . M3_NL;
		
		// 上段メニュー終了
		$menuText .= '</ul>' . M3_NL;
		$menuText .= '</div>' . M3_NL;
		
		// ####### 下段メニューの作成 #######		
		$menuText .= '<div id="configmenu-lower">' . M3_NL;
		$menuText .= '<ul>' . M3_NL;

		if ($task == 'entry' ||		// イベント記事管理
			$task == 'entry_detail' ||
			$task == 'comment' ||		// イベント記事コメント管理
			$task == 'comment_detail'){
			
			// イベント記事一覧
			$current = '';
			$link = $baseUrl . '&task=entry';
			if ($task == 'entry' || $task == 'entry_detail') $current = 'id="current"';
			$menuText .= '<li ' . $current . '><a href="'. $this->getUrl($link) .'"><span>記事一覧</span></a></li>' . M3_NL;
			
			// イベント記事コメント一覧
			$current = '';
			$link = $baseUrl . '&task=comment';
			if ($task == 'comment' || $task == 'comment_detail') $current = 'id="current"';
			$menuText .= '<li ' . $current . '><a href="'. $this->getUrl($link) .'"><span>コメント一覧</span></a></li>' . M3_NL;
		} else if ($task == 'category' ||		// カテゴリー設定
			$task == 'category_detail' ||		// カテゴリー設定
			$task == 'config'){		// イベント設定
			
			// カテゴリー設定
			$current = '';
			$link = $baseUrl . '&task=category';
			if ($task == 'category' || $task == 'category_detail') $current = 'id="current"';
			$menuText .= '<li ' . $current . '><a href="'. $this->getUrl($link) .'"><span>カテゴリー</span></a></li>' . M3_NL;
			
			// その他設定
			$current = '';
			$link = $baseUrl . '&task=config';
			if ($task == 'config') $current = 'id="current"';
			$menuText .= '<li ' . $current . '><a href="'. $this->getUrl($link) .'"><span>イベント設定</span></a></li>' . M3_NL;
		}
		
		// 下段メニュー終了
		$menuText .= '</ul>' . M3_NL;
		$menuText .= '</div>' . M3_NL;

		// 作成データの埋め込み
		$linkList = '<div id="configmenu-top"><label>' . 'イベント' . $linkList . '</label></div>';
		$outputText .= '<table width="90%"><tr><td>' . $linkList . $menuText . '</td></tr></table>' . M3_NL;
		$this->tmpl->addVar("_widget", "menu_items", $outputText);
		*/
	}
}
?>
