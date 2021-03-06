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
require_once($gEnvManager->getWidgetContainerPath('default_content') . '/default_contentCommonDef.php');
require_once($gEnvManager->getWidgetDbPath('default_content') . '/default_contentDb.php');

class admin_default_contentBaseWidgetContainer extends BaseAdminWidgetContainer
{
	protected static $_mainDb;			// DB接続オブジェクト
	protected static $_configArray;		// 汎用コンテンツ定義値
	protected $_openBy;				// ウィンドウオープンタイプ
	protected $_baseUrl;			// 管理画面のベースURL
	
	const DEFAULT_TOP_PAGE = 'content';		// デフォルトのトップページ
	const BREADCRUMB_TITLE = '汎用コンテンツ';				// パンくずリストトップタイトル
	// 画面
	const TASK_CONTENT = 'content';							// コンテンツ管理
	const TASK_CONTENT_DETAIL = 'content_detail';			// コンテンツ管理詳細
	const TASK_OTHER = 'other';								// その他設定
	const TASK_HISTORY = 'history';							// コンテンツ履歴
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// 代替処理用のウィジェットIDを設定
		$this->setDefaultWidgetId(default_contentCommonDef::CONTENT_WIDGET_ID);
		
		// DBオブジェクト作成
		if (!isset(self::$_mainDb)) self::$_mainDb = new default_contentDb();
		
		// 汎用コンテンツ定義を読み込む
		if (!isset(self::$_configArray)) self::$_configArray = default_contentCommonDef::loadConfig(self::$_mainDb);
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
		$this->_openBy = $request->trimValueOf(M3_REQUEST_PARAM_OPEN_BY);		// ウィンドウオープンタイプ
		if (!empty($this->_openBy)) $this->addOptionUrlParam(M3_REQUEST_PARAM_OPEN_BY, $this->_openBy);
		
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
		// ウィンドウオープンタイプ取得
		//if ($this->_openBy == 'simple' || $this->_openBy == 'tabs' || $this->_openBy == 'dialog') return;	// シンプルウィンドウまたはタブ、ダイアログ表示のときはメニューを表示しない
		if ($this->_openBy == 'tabs' || $this->_openBy == 'dialog'){
			return;	// タブ、ダイアログ表示のときはメニューを表示しない
		} else if ($this->_openBy == 'simple'){		// シンプルウィンドウの場合はウィジェット名のみ表示
			$navbarDef = new stdClass;
			$navbarDef->title = $this->gEnv->getCurrentWidgetTitle();		// ウィジェット名
			$this->gPage->setAdminSubNavbarDef($navbarDef);
			return;
		}
		
		// 表示画面を決定
		$task = $request->trimValueOf(M3_REQUEST_PARAM_OPERATION_TASK);
		if (empty($task)) $task = self::DEFAULT_TOP_PAGE;
		
		// パンくずリストの定義データ作成
		$titles = array();
		switch ($task){
			case self::TASK_CONTENT:		// コンテンツ管理
				$titles[] = 'コンテンツ管理';
				$titles[] = 'コンテンツ一覧';
				break;
			case self::TASK_CONTENT_DETAIL:		// コンテンツ管理(詳細)
				$titles[] = 'コンテンツ管理';
				$titles[] = 'コンテンツ一覧';
				$titles[] = '詳細';
				break;
			case self::TASK_HISTORY:				// コンテンツ履歴
				$titles[] = 'コンテンツ管理';
				$titles[] = 'コンテンツ一覧';
				$titles[] = '詳細';
				$titles[] = '履歴';
				break;
			case self::TASK_OTHER:		// その他設定
				$titles[] = '基本';
				$titles[] = '基本設定';
				break;
		}
		
		// メニューバーの定義データ作成
		$menu =	array(
					(Object)array(
						'name'		=> 'コンテンツ管理',
						'task'		=> self::TASK_CONTENT,
						'url'		=> '',
						'tagid'		=> '',
						'active'	=> (
											$task == self::TASK_CONTENT ||				// コンテンツ管理(一覧)
											$task == self::TASK_CONTENT_DETAIL ||		// コンテンツ管理(詳細)
											$task == self::TASK_HISTORY					// コンテンツ管理履歴
										),
						'submenu'	=> array(
							(Object)array(
								'name'		=> 'コンテンツ一覧',
								'task'		=> self::TASK_CONTENT,
								'url'		=> '',
								'tagid'		=> '',
								'active'	=> (
													$task == self::TASK_CONTENT ||				// コンテンツ管理(一覧)
													$task == self::TASK_CONTENT_DETAIL ||		// コンテンツ管理(詳細)
													$task == self::TASK_HISTORY					// コンテンツ管理履歴
												)
							)
						)
					),
					(Object)array(
						'name'		=> '基本',
						'task'		=> self::TASK_OTHER,
						'url'		=> '',
						'tagid'		=> '',
						'active'	=> (
											$task == self::TASK_OTHER
										),
						'submenu'	=> array(
							(Object)array(
								'name'		=> '基本設定',
								'task'		=> self::TASK_OTHER,
								'url'		=> '',
								'tagid'		=> '',
								'active'	=> (
													$task == self::TASK_OTHER					// 基本設定
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
			case self::TASK_CONTENT:		// コンテンツ管理
			case self::TASK_CONTENT_DETAIL:		// コンテンツ管理(詳細)
				$linkList = ' &gt;&gt; コンテンツ管理';// パンくずリスト
				break;
			case self::TASK_OTHER:		// その他設定
				$linkList = ' &gt;&gt; 基本設定';// パンくずリスト
				break;
		}
		// ####### 上段メニューの作成 #######
		$menuText = '<div id="configmenu-upper">' . M3_NL;
		$menuText .= '<ul>' . M3_NL;
		$baseUrl = $this->getAdminUrlWithOptionParam(true);// 画面定義ID付き
		
		// コンテンツ管理
		$current = '';
		$link = $baseUrl . '&task=content';
		if ($task == self::TASK_CONTENT ||
			$task == self::TASK_CONTENT_DETAIL){
			$current = 'id="current"';
		}
		$menuText .= '<li ' . $current . '><a href="'. $this->getUrl($link, true) .'"><span>コンテンツ管理</span></a></li>' . M3_NL;
		
		// その他設定
		$current = '';
		$link = $baseUrl . '&task=other';
		if ($task == self::TASK_OTHER){		
			$current = 'id="current"';
		}
		$menuText .= '<li ' . $current . '><a href="'. $this->getUrl($link, true) .'"><span>基本設定</span></a></li>' . M3_NL;
		
		// 上段メニュー終了
		$menuText .= '</ul>' . M3_NL;
		$menuText .= '</div>' . M3_NL;
		
		// 作成データの埋め込み
		$topName = self::WIDGET_TITLE_NAME . '(' . default_contentCommonDef::$_deviceTypeName . ')';
		$linkList = '<div id="configmenu-top"><label>' . $topName . $linkList . '</label></div>';
		$outputText .= '<table width="90%"><tr><td>' . $linkList . $menuText . '</td></tr></table>' . M3_NL;
		$this->tmpl->addVar("_widget", "menu_items", $outputText);
	*/
	}
}
?>
