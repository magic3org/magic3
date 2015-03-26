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
require_once($gEnvManager->getWidgetContainerPath('news_main') . '/newsCommonDef.php');
require_once($gEnvManager->getWidgetDbPath('news_main') . '/news_mainDb.php');

class admin_news_mainBaseWidgetContainer extends BaseAdminWidgetContainer
{
	protected static $_mainDb;			// DB接続オブジェクト
	protected static $_configArray;		// 新着情報定義値
	protected static $_task;			// 現在の画面
	protected $_baseUrl;			// 管理画面のベースURL
	protected $_contentType;		// コンテンツタイプ
	const DEFAULT_MESSAGE_LENGTH	= 300;				// デフォルトのコメント最大文字数
	const DEFAULT_CATEGORY_COUNT	= 2;				// デフォルトのカテゴリ数
	
	// カレンダー用スクリプト
	const CALENDAR_SCRIPT_FILE = '/jscalendar-1.0/calendar.js';		// カレンダースクリプトファイル
	const CALENDAR_LANG_FILE = '/jscalendar-1.0/lang/calendar-ja.js';	// カレンダー言語ファイル
	const CALENDAR_SETUP_FILE = '/jscalendar-1.0/calendar-setup.js';	// カレンダーセットアップファイル
	const CALENDAR_CSS_FILE = '/jscalendar-1.0/calendar-win2k-1.css';		// カレンダー用CSSファイル
	
	// 画面
	const TASK_CONFIG			= 'config';				// 基本設定
	const TASK_NEWS				= 'news';				// 新着情報一覧
	const TASK_NEWS_DETAIL 		= 'news_detail';		// 新着情報詳細
	const DEFAULT_TASK			= 'config';
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		if (!isset(self::$_mainDb)) self::$_mainDb = new news_mainDb();
		
		// DB定義を読み込む
		if (!isset(self::$_configArray)) self::$_configArray = newsCommonDef::loadConfig(self::$_mainDb);
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
			case self::TASK_NEWS:		// 新着情報一覧
				$titles[] = '新着情報管理';
				$titles[] = '新着一覧';
				break;
			case self::TASK_NEWS_DETAIL:		// 新着情報詳細
				$titles[] = '新着情報管理';
				$titles[] = '新着一覧';
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
						'name'		=> '新着情報管理',
						'task'		=> self::TASK_NEWS,
						'url'		=> '',
						'tagid'		=> '',
						'active'	=> (
											$task == self::TASK_NEWS ||			// 新着情報一覧
											$task == self::TASK_NEWS_DETAIL		// 新着情報詳細
										),
						'submenu'	=> array(
							(Object)array(
								'name'		=> '新着一覧',
								'task'		=> self::TASK_NEWS,
								'url'		=> '',
								'tagid'		=> '',
								'active'	=> (
													$task == self::TASK_NEWS ||			// 新着情報一覧
													$task == self::TASK_NEWS_DETAIL		// 新着情報詳細
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
/*
		// パンくずリストを作成
		switch ($task){
			case self::TASK_NEWS:			// 新着情報一覧
			case self::TASK_NEWS_DETAIL:	// 新着情報詳細
				$linkList = ' &gt;&gt; 新着一覧';// パンくずリスト
				break;
			case self::TASK_CONFIG:		// 基本設定
				$linkList = ' &gt;&gt; 基本設定';// パンくずリスト
				break;
		}

		// ####### 上段メニューの作成 #######
		$menuText = '<div id="configmenu-upper">' . M3_NL;
		$menuText .= '<ul>' . M3_NL;
		
		$current = '';
//		$baseUrl = $this->getAdminUrlWithOptionParam();
		
		// 新着情報一覧
		$current = '';
		$link = $this->_baseUrl . '&task=' . self::TASK_NEWS;
		if ($task == self::TASK_NEWS || $task == self::TASK_NEWS_DETAIL) $current = 'id="current"';
		$menuText .= '<li ' . $current . '><a href="'. $this->getUrl($link) .'"><span>新着一覧</span></a></li>' . M3_NL;
		
		// 基本設定
		$current = '';
		$link = $this->_baseUrl . '&task=' . self::TASK_CONFIG;
		if ($task == self::TASK_CONFIG) $current = 'id="current"';
		$menuText .= '<li ' . $current . '><a href="'. $this->getUrl($link) .'"><span>基本設定</span></a></li>' . M3_NL;
		
		// 上段メニュー終了
		$menuText .= '</ul>' . M3_NL;
		$menuText .= '</div>' . M3_NL;
		
		// 作成データの埋め込み
		$linkList = '<div id="configmenu-top"><label>' . '新着情報' . $linkList . '</label></div>';
		$outputText .= '<table width="90%"><tr><td>' . $linkList . $menuText . '</td></tr></table>' . M3_NL;
		$this->tmpl->addVar("_widget", "menu_items", $outputText);
		*/
	}
}
?>
