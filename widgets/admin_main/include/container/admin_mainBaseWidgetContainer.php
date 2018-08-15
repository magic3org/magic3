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
 * @copyright  Copyright 2006-2018 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseAdminWidgetContainer.php');

class admin_mainBaseWidgetContainer extends BaseAdminWidgetContainer
{
	private $headTitle;		// METAタグタイトル
	private $headDesc;		// METAタグ要約
	private $headKeyword;	// METAタグキーワード
	
	// カレンダー用スクリプト
	const CALENDAR_SCRIPT_FILE = '/jscalendar-1.0/calendar.js';		// カレンダースクリプトファイル
	const CALENDAR_LANG_FILE = '/jscalendar-1.0/lang/calendar-ja.js';	// カレンダー言語ファイル
	const CALENDAR_SETUP_FILE = '/jscalendar-1.0/calendar-setup.js';	// カレンダーセットアップファイル
	const CALENDAR_CSS_FILE = '/jscalendar-1.0/calendar-win2k-1.css';		// カレンダー用CSSファイル
	
	// 画面
	// 基本情報
	const TASK_CONFIGSITE		= 'configsite';			// サイト情報
	const TASK_PAGEHEAD			= 'pagehead';			// ページヘッダ情報
	const TASK_PAGEHEAD_DETAIL	= 'pagehead_detail';	// ページヘッダ情報詳細
	const TASK_PORTAL			= 'portal';				// Magic3ポータル
	// システム情報
	const TASK_CONFIGSYS		= 'configsys';	// システム基本設定
	const TASK_CONFIGLANG		= 'configlang';	// 言語設定
	const TASK_CONFIGMESSAGE	= 'configmessage';	// メッセージ設定
	const TASK_CONFIGIMAGE		= 'configimage';		// 画像設定
	const TASK_CONFIGMAIL		= 'configmail';			// メールサーバ
	const TASK_SERVER_ENV		= 'serverenv';	// サーバ環境
	// ユーザ管理
	const TASK_USERLIST			= 'userlist';		// ユーザ一覧
	const TASK_USERLIST_DETAIL	= 'userlist_detail';	// ユーザ詳細
	const TASK_LOGINHISTORY		= 'loginhistory';		// ログイン履歴
	const TASK_USERGROUP		= 'usergroup';		// ユーザグループ
	const TASK_USERGROUP_DETAIL	= 'usergroup_detail';		// ユーザグループ詳細
	const TASK_TASKACCESS		= 'taskaccess';						// 管理画面アクセス制御
	// テンプレート管理
	const TASK_TEMPLIST			= 'templist';				// テンプレート一覧
	const TASK_TEMPIMAGE		= 'tempimage';				// テンプレート画像編集
	const TASK_TEMPIMAGE_DETAIL	= 'tempimage_detail';		// テンプレート画像編集(詳細)
	const TASK_TEMPGENERATECSS			= 'tempgeneratecss';			// テンプレートCSS生成
	const TASK_TEMPGENERATECSS_DETAIL	= 'tempgeneratecss_detail';		// テンプレートCSS生成(詳細)
	// 運用状況
	const TASK_OPELOG				= 'opelog';			// 運用ログ一覧
	const TASK_OPELOG_DETAIL 		= 'opelog_detail';		// 運用ログ詳細
	const TASK_ACCESSLOG			= 'accesslog';				// アクセスログ一覧
	const TASK_ACCESSLOG_DETAIL		= 'accesslog_detail';		// アクセスログ詳細
	const TASK_SEARCHWORDLOG		= 'searchwordlog';				// 検索語ログ一覧
	const TASK_SEARCHWORDLOG_DETAIL	= 'searchwordlog_detail';		// 検索語ログ詳細
	const TASK_CALC					= 'analyzecalc';		// 集計
	const TASK_GRAPH				= 'analyzegraph';		// グラフ表示
	const TASK_AWSTATS				= 'awstats';		// Awstats表示
	// メンテナンス
	const TASK_FILEBROWSE 		= 'filebrowse';		// ファイルブラウザ
	const TASK_PAGEINFO			= 'pageinfo';	// ページ情報
	const TASK_PAGEINFO_DETAIL	= 'pageinfo_detail';	// ページ情報
	const TASK_ACCESSPOINT			= 'accesspoint';		// アクセスポイント
	const TASK_ACCESSPOINT_DETAIL	= 'accesspoint_detail';		// アクセスポイント
	const TASK_PAGEID			= 'pageid';		// ページID
	const TASK_PAGEID_DETAIL	= 'pageid_detail';		// ページID
	const TASK_MENUID			= 'menuid';		// メニューID
	const TASK_MENUID_DETAIL	= 'menuid_detail';		// メニューID
	const TASK_INITSYSTEM		= 'initsystem';		// DBメンテナンス
	const TASK_INSTALLDATA		= 'installdata';	// データインストール
	const TASK_DBBACKUP			= 'dbbackup';		// DBバックアップ
	const TASK_INITWIZARD		= 'initwizard';		// 管理画面カスタムウィザード
	const TASK_EDITMENU			= 'editmenu';		// 管理メニュー編集
	const TASK_DB_ACCESSLOG		= 'dbaccesslog';			// DB管理アクセスログ
	// サーバ管理
	const TASK_SERVERINFO		= 'serverinfo';			// サーバ情報
	const TASK_SITELIST			= 'sitelist';			// サイト一覧
	const TASK_SITELIST_DETAIL	= 'sitelist_detail';	// サイト詳細
	const TASK_SERVERTOOL		= 'servertool';			// サーバ管理ツール
	// 画面なし(直接実行)
	const TASK_SITEOPEN			= 'siteopen';			// アクセスポイントの公開,非公開
	// その他
	const TASK_TOP					= 'top';					// ダッシュボード(メッセージのみ)
	const TASK_LANDINGPAGE			= 'landingpage';			// ランディングページ管理
	const TASK_LANDINGPAGE_DETAIL	= 'landingpage_detail';		// ランディングページ管理(詳細)
	const TASK_MENUDEF				= 'menudef';				// 多階層メニュー定義
	const TASK_MENUDEF_DETAIL		= 'menudef_detail';			// 多階層メニュー定義
	const TASK_SMENUDEF				= 'smenudef';				// 単階層メニュー定義
	const TASK_SMENUDEF_DETAIL		= 'smenudef_detail';		// 単階層メニュー定義詳細
	const TASK_TEST					= 'test';						// テスト画面
	
	// DBアクセス用
	const CF_USE_LANDING_PAGE = 'use_landing_page';		// ランディングページ機能を使用するかどうか
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		global $gRequestManager;
			
		// 親クラスを呼び出す
		parent::__construct();
		
		// ページタイトルを設定
		$task = $gRequestManager->trimValueOf(M3_REQUEST_PARAM_OPERATION_TASK);
		switch ($task){
			case 'adjustwidget':	// ウィジェット位置調整
				$this->headTitle = 'ウィジェット共通設定';
				break;
		}
	}
	/**
	 * ヘッダ部メタタグの設定
	 *
	 * HTMLのheadタグ内に出力する。
	 * _assign()よりも後に実行される。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ
	 * @return array 						設定データ。連想配列で「title」「description」「keywords」を設定。
	 */
	function _setHeadMeta($request, &$param)
	{
		$headData = array(	'title' => $this->headTitle,
							'description' => $this->headDesc,
							'keywords' => $this->headKeyword);
		return $headData;
	}
}
?>
