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
require_once($gEnvManager->getContainerPath() . '/baseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetContainerPath() . '/blog_mainCommonDef.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/blog_mainDb.php');

class blog_mainBaseWidgetContainer extends BaseWidgetContainer
{
	protected static $_mainDb;			// DB接続オブジェクト
	protected static $_configArray;		// ブログ定義値
	protected static $_paramObj;		// ウィジェットパラメータオブジェクト
	protected static $_canEditEntry;	// 記事が編集可能かどうか
	protected static $_task;			// デフォルトのタスク
	protected $_isMultiLang;			// 多言語対応画面かどうか
	protected $_blogId;		// ブログID
	protected $_baseUrl;		// ベースURL
//	const DEFAULT_COMMENT_LENGTH	= 300;				// デフォルトのコメント最大文字数
//	const DEFAULT_CATEGORY_COUNT	= 2;				// デフォルトのカテゴリ数
	
	// 画面
	const TASK_TOP				= 'top';			// トップ画面
	const TASK_ENTRY			= 'entry';			// 記事編集画面
	const TASK_ENTRY_DETAIL 	= 'entry_detail';			// 記事編集画面詳細
	const TASK_IMAGE			= 'image';				// ブログ記事画像
	const TASK_HISTORY			= 'history';			// ブログ記事履歴
	const TASK_COMMENT			= 'comment';		// ブログ記事コメント管理
	const TASK_COMMENT_DETAIL 	= 'comment_detail';		// ブログ記事コメント管理(詳細)
	const TASK_LINKINFO			= 'linkinfo';		// CKEditorプラグインのリンク情報取得用
	const DEFAULT_TASK			= 'top';
	const DEFAULT_CONFIG_TASK	= 'entry';
			
	// アドオンオブジェクト用
	const BLOG_OBJ_ID = 'bloglib';		// ブログオブジェクトID
//	const LINKINFO_OBJ_ID = 'linkinfo';	// リンク情報オブジェクトID
	
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
		if (!isset(self::$_mainDb)) self::$_mainDb = new blog_mainDb();
		
		// ブログ定義を読み込む
		if (!isset(self::$_configArray)) self::$_configArray = blog_mainCommonDef::loadConfig(self::$_mainDb);
	}
	/**
	 * ウィジェット初期化
	 *
	 * 共通パラメータの初期化や、以下のパターンでウィジェット出力方法の変更を行う。
	 * ・組み込みの_setTemplate(),_assign()を使用
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @return 								なし
	 */
	function _preInit($request)
	{
		// URLパラメータ取得
		$this->_blogId = $request->trimValueOf(M3_REQUEST_PARAM_BLOG_ID);		// 所属ブログ
		$this->addOptionUrlParam(M3_REQUEST_PARAM_OPEN_BY, $this->_openBy);		// ウィンドウオープンタイプ
		$this->addOptionUrlParam(M3_REQUEST_PARAM_BLOG_ID, $this->_blogId);
		
		// 共通パラメータ初期化
		$this->_isMultiLang = $this->gEnv->isMultiLanguageSite();			// 多言語対応画面かどうか
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
		// ##### 投稿管理画面のトップメニューを作成 #####
		$cmd = $request->trimValueOf(M3_REQUEST_PARAM_OPERATION_COMMAND);		// 実行コマンドを取得
		if ($cmd != M3_REQUEST_CMD_DO_WIDGET) return;		// 単体実行以外のときは終了
		
		$openBy = $request->trimValueOf(M3_REQUEST_PARAM_OPEN_BY);		// ウィンドウオープンタイプ
		if ($openBy == 'simple') return;			// シンプルウィンドウのときはメニューを表示しない
		
		// 表示画面を決定
		$task = $request->trimValueOf(M3_REQUEST_PARAM_OPERATION_TASK);
		if (empty($task)) $task = self::$_task;
		
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
			case self::TASK_COMMENT:			// ブログ記事コメント(一覧)
				$titles[] = 'ブログ記事管理';
				$titles[] = 'コメント一覧';
				break;
			case self::TASK_COMMENT_DETAIL:		// ブログ記事コメント(詳細)
				$titles[] = 'ブログ記事管理';
				$titles[] = 'コメント一覧';
				$titles[] = '詳細';
				break;
/*			case self::TASK_CATEGORY:			// 記事カテゴリー(一覧)
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
				break;*/
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
													$task == self::TASK_HISTORY				// ブログ記事履歴
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
					)
				);
		
		// サブメニューバーを作成
		$this->setConfigMenubarDef($titles, $menu);
	}
}
?>
