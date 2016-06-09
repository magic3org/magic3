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
 * @copyright  Copyright 2006-2016 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseAdminWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetContainerPath() . '/blog_mainCommonDef.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/blog_mainDb.php');

class admin_blog_mainBaseWidgetContainer extends BaseAdminWidgetContainer
{
	protected static $_mainDb;			// DB接続オブジェクト
	protected static $_configArray;		// ブログ定義値
//	const DEFAULT_COMMENT_LENGTH	= 300;				// デフォルトのコメント最大文字数
//	const DEFAULT_CATEGORY_COUNT	= 2;				// デフォルトのカテゴリ数
	
	// カレンダー用スクリプト
	const CALENDAR_SCRIPT_FILE = '/jscalendar-1.0/calendar.js';		// カレンダースクリプトファイル
	const CALENDAR_LANG_FILE = '/jscalendar-1.0/lang/calendar-ja.js';	// カレンダー言語ファイル
	const CALENDAR_SETUP_FILE = '/jscalendar-1.0/calendar-setup.js';	// カレンダーセットアップファイル
	const CALENDAR_CSS_FILE = '/jscalendar-1.0/calendar-win2k-1.css';		// カレンダー用CSSファイル
	// 画面
	const TASK_ENTRY			= 'entry';				// ブログ記事(一覧)
	const TASK_ENTRY_DETAIL		= 'entry_detail';		// ブログ記事(詳細)
	const TASK_IMAGE			= 'image';				// ブログ記事画像
	const TASK_HISTORY			= 'history';			// ブログ記事履歴
	const TASK_SCHEDULE			= 'schedule';			// ブログ記事予約(一覧)
	const TASK_SCHEDULE_DETAIL	= 'schedule_detail';	// ブログ記事予約(詳細)
	const TASK_COMMENT			= 'comment';			// ブログ記事コメント(一覧)
	const TASK_COMMENT_DETAIL	= 'comment_detail';		// ブログ記事コメント(詳細)
	const TASK_ANALYTICS		= 'analytics';			// アクセス解析
	const TASK_CATEGORY			= 'category';			// 記事カテゴリー(一覧)
	const TASK_CATEGORY_DETAIL	= 'category_detail';	// 記事カテゴリー(詳細)
	const TASK_BLOGID			= 'blogid';				// マルチブログ設定(一覧)
	const TASK_BLOGID_DETAIL	= 'blogid_detail';		// マルチブログ設定(詳細)
	const TASK_CONFIG			= 'config';				// 基本設定
	const DEFAULT_TASK			= 'entry';				// デフォルトのタスク(ブログ記事(一覧))
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		if (!isset(self::$_mainDb)) self::$_mainDb = new blog_mainDb();
		
		// データ初期処理(1回だけ実行)
		if (!isset(self::$_configArray)){
			// ブログ定義を読み込む
			self::$_configArray = blog_mainCommonDef::loadConfig(self::$_mainDb);
			
			// プレビューデータを一旦削除
			self::$_mainDb->delAllEntryPreview();
		}
		
		// ブログオブジェクト生成
		$blogLibObj = $this->gInstance->getObject(blog_mainCommonDef::BLOG_OBJ_ID);
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
			case self::TASK_ENTRY:				// ブログ記事(一覧)
				$titles[] = 'ブログ記事管理';
				$titles[] = '記事一覧';
				break;
			case self::TASK_ENTRY_DETAIL:		// ブログ記事(詳細)
				$titles[] = 'ブログ記事管理';
				$titles[] = '記事一覧';
				$titles[] = '詳細';
				break;
			case self::TASK_IMAGE:			// ブログ記事画像
				$titles[] = 'ブログ記事管理';
				$titles[] = '記事一覧';
				$titles[] = '詳細';
				$titles[] = '画像';
				break;
			case self::TASK_HISTORY:			// ブログ記事履歴
				$titles[] = 'ブログ記事管理';
				$titles[] = '記事一覧';
				$titles[] = '詳細';
				$titles[] = '履歴';
				break;
			case self::TASK_SCHEDULE:			// ブログ記事予約(一覧)
				$titles[] = 'ブログ記事管理';
				$titles[] = '記事一覧';
				$titles[] = '記事詳細';
				$titles[] = '予約一覧';
				break;
			case self::TASK_SCHEDULE_DETAIL:			// ブログ記事予約(詳細)
				$titles[] = 'ブログ記事管理';
				$titles[] = '記事一覧';
				$titles[] = '記事詳細';
				$titles[] = '予約一覧';
				$titles[] = '詳細';
				break;
			case self::TASK_COMMENT:			// ブログ記事コメント(一覧)
				$titles[] = 'ブログ記事管理';
				$titles[] = 'コメント一覧';
				break;
			case self::TASK_COMMENT_DETAIL:		// ブログ記事コメント(詳細)
				$titles[] = 'ブログ記事管理';
				$titles[] = 'コメント一覧';
				$titles[] = '詳細';
				break;
			case self::TASK_ANALYTICS:		// アクセス解析
				$titles[] = 'アクセス解析';
				$titles[] = '期間上位記事';
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
			case self::TASK_BLOGID:			// マルチブログ設定(一覧)
				$titles[] = '基本';
				$titles[] = 'マルチブログ';
				break;
			case self::TASK_BLOGID_DETAIL:		// マルチブログ設定(詳細)
				$titles[] = '基本';
				$titles[] = 'マルチブログ';
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
						'name'		=> 'ブログ記事管理',
						'task'		=> '',
						'url'		=> '',
						'tagid'		=> '',
						'active'	=> (
											$task == self::TASK_ENTRY ||				// ブログ記事(一覧)
											$task == self::TASK_ENTRY_DETAIL ||		// ブログ記事(詳細)
											$task == self::TASK_IMAGE ||			// ブログ記事画像
											$task == self::TASK_HISTORY ||			// ブログ記事履歴
											$task == self::TASK_SCHEDULE ||			// ブログ記事予約(一覧)
											$task == self::TASK_SCHEDULE_DETAIL ||		// ブログ記事予約(詳細)
											$task == self::TASK_COMMENT ||			// ブログ記事コメント(一覧)
											$task == self::TASK_COMMENT_DETAIL		// ブログ記事コメント(詳細)
										),
						'submenu'	=> array(
							(Object)array(
								'name'		=> '記事一覧',
								'task'		=> self::TASK_ENTRY,
								'url'		=> '',
								'tagid'		=> '',
								'active'	=> (
													$task == self::TASK_ENTRY ||			// ブログ記事(一覧)
													$task == self::TASK_ENTRY_DETAIL ||		// ブログ記事(詳細)
													$task == self::TASK_IMAGE ||			// ブログ記事画像
													$task == self::TASK_HISTORY	||			// ブログ記事履歴
													$task == self::TASK_SCHEDULE ||			// ブログ記事予約(一覧)
													$task == self::TASK_SCHEDULE_DETAIL		// ブログ記事予約(詳細)
												)
							),
							(Object)array(
								'name'		=> 'コメント一覧',
								'task'		=> self::TASK_COMMENT,
								'url'		=> '',
								'tagid'		=> '',
								'active'	=> (
													$task == self::TASK_COMMENT ||			// ブログ記事コメント(一覧)
													$task == self::TASK_COMMENT_DETAIL		// ブログ記事コメント(詳細)
												)
							)
						)
					),
					(Object)array(
						'name'		=> 'アクセス解析',
						'task'		=> self::TASK_ANALYTICS,
						'url'		=> '',
						'tagid'		=> '',
						'active'	=> (
											$task == self::TASK_ANALYTICS					// アクセス解析
										),
						'submenu'	=> array(
							(Object)array(
								'name'		=> '期間上位記事',
								'task'		=> self::TASK_ANALYTICS,
								'url'		=> '',
								'tagid'		=> '',
								'active'	=> (
													$task == self::TASK_ANALYTICS					// アクセス解析
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
											$task == self::TASK_BLOGID ||			// マルチブログ設定(一覧)
											$task == self::TASK_BLOGID_DETAIL ||		// マルチブログ設定(詳細)
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
								'name'		=> 'マルチブログ',
								'task'		=> self::TASK_BLOGID,
								'url'		=> '',
								'tagid'		=> '',
								'active'	=> (
													$task == self::TASK_BLOGID ||			// マルチブログ設定(一覧)
													$task == self::TASK_BLOGID_DETAIL		// マルチブログ設定(詳細)
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
			case 'entry':		// ブログ記事
			case 'entry_detail':	// ブログ記事詳細
			case self::TASK_HISTORY:							// ブログ記事履歴
				$linkList = ' &gt;&gt; ブログ記事 &gt;&gt; 記事一覧';// パンくずリスト
				break;
			case 'comment':		// ブログ記事コメント
			case 'comment_detail':	// ブログ記事コメント
				$linkList = ' &gt;&gt; ブログ記事 &gt;&gt; コメント一覧';// パンくずリスト
				break;
			case 'user':		// ユーザ管理
			case 'user_detail':		// ユーザ管理(詳細)
				$linkList = ' &gt;&gt; ユーザ管理 &gt;&gt; ユーザ一覧';// パンくずリスト
				break;
			case self::TASK_CONFIG:		// ブログ設定
				$linkList = ' &gt;&gt; 基本設定 &gt;&gt; ブログ設定';// パンくずリスト
				break;
			case 'category':		// カテゴリ設定
			case 'category_detail':		// カテゴリ設定
				$linkList = ' &gt;&gt; 基本設定 &gt;&gt; カテゴリ';// パンくずリスト
				break;
			case 'blogid':		// マルチブログ設定
			case 'blogid_detail':		// マルチブログ設定
				$linkList = ' &gt;&gt; 基本設定 &gt;&gt; マルチブログ';// パンくずリスト
				break;
		}

		// ####### 上段メニューの作成 #######
		$menuText = '<div id="configmenu-upper">' . M3_NL;
		$menuText .= '<ul>' . M3_NL;
		
		$current = '';
		$baseUrl = $this->getAdminUrlWithOptionParam();
		
		// ブログ記事管理
		$current = '';
		$link = $baseUrl . '&task=entry';
		if ($task == 'entry' ||
			$task == 'entry_detail' ||
			$task == self::TASK_HISTORY ||	// ブログ記事履歴
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
			$task == self::TASK_CONFIG){		// ブログ設定
			$current = 'id="current"';
		}
		$menuText .= '<li ' . $current . '><a href="'. $this->getUrl($link) .'"><span>基本設定</span></a></li>' . M3_NL;
		
		// 上段メニュー終了
		$menuText .= '</ul>' . M3_NL;
		$menuText .= '</div>' . M3_NL;
		
		// ####### 下段メニューの作成 #######		
		$menuText .= '<div id="configmenu-lower">' . M3_NL;
		$menuText .= '<ul>' . M3_NL;

		if ($task == 'entry' ||		// ブログ記事管理
			$task == 'entry_detail' ||
			$task == self::TASK_HISTORY ||	// ブログ記事履歴
			$task == 'comment' ||		// ブログ記事コメント管理
			$task == 'comment_detail'){
			
			// ブログ記事一覧
			$current = '';
			$link = $baseUrl . '&task=entry';
			if ($task == 'entry' || $task == 'entry_detail' || $task == self::TASK_HISTORY) $current = 'id="current"';
			$menuText .= '<li ' . $current . '><a href="'. $this->getUrl($link) .'"><span>記事一覧</span></a></li>' . M3_NL;
			
			// ブログ記事コメント一覧
			$current = '';
			$link = $baseUrl . '&task=comment';
			if ($task == 'comment' || $task == 'comment_detail') $current = 'id="current"';
			$menuText .= '<li ' . $current . '><a href="'. $this->getUrl($link) .'"><span>コメント一覧</span></a></li>' . M3_NL;
		} else if ($task == 'category' ||		// カテゴリ設定
			$task == 'category_detail' ||		// カテゴリ設定
			$task == 'blogid' ||		// マルチブログ
			$task == 'blogid_detail' ||		// マルチブログ詳細
			$task == self::TASK_CONFIG){		// ブログ設定
			
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
			if ($task == self::TASK_CONFIG) $current = 'id="current"';
			$menuText .= '<li ' . $current . '><a href="'. $this->getUrl($link) .'"><span>ブログ設定</span></a></li>' . M3_NL;
		}
		
		// 下段メニュー終了
		$menuText .= '</ul>' . M3_NL;
		$menuText .= '</div>' . M3_NL;

		// 作成データの埋め込み
		$linkList = '<div id="configmenu-top"><label>' . 'ブログ' . $linkList . '</label></div>';
		$outputText .= '<table width="90%"><tr><td>' . $linkList . $menuText . '</td></tr></table>' . M3_NL;
		$this->tmpl->addVar("_widget", "menu_items", $outputText);
		*/
	}
}
?>
