<?php
/**
 * 画面制御マネージャー
 *
 * 画面の作成、遷移を処理する
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2023 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @link       http://magic3.org
 */
require_once(M3_SYSTEM_INCLUDE_PATH . '/common/core.php');
require_once(M3_SYSTEM_INCLUDE_PATH . '/common/scriptLibInfo.php');

class PageManager extends _Core
{
	private $popupMsg;				// ポップアップダイアログメッセージ
	private $showPositionMode;			// ポジション表示モード
	private $showWidget;			// ウィジェットの単体表示
	private $systemHandleMode;			// システム制御遷移モード(1=サイト非公開時)
	private $isPageEditable;		// フロント画面ページ編集可能モード
	private $isTransparentMode;		// 画面透過モード
	private $isPersonalMode;		// 機能アクセスパーソナルモード
	private $isEditMode;			// フロント画面編集モード
	private $isLayout;				// 画面レイアウト中かどうか
	private $isPageTopUrl;			// ページトップ(サブページ内のトップ位置)のURLかどうか
	private $isContentDetailPage;	// コンテンツ詳細画面のページかどうか
	private $tmpData;				// データ作成用
	//private $db;					// DBオブジェクト
	private $defaultScriptFiles;	// デフォルトで読み込むスクリプトファイル
	private $defaultCssFiles;		// デフォルトで読み込むCSSファイル
	private $defaultAdminScriptFiles;	// デフォルトで読み込むスクリプトファイル(管理用)
	private $defaultAdminCssFiles;		// デフォルトで読み込むCSSファイル(管理用)
	private $defaultAdminDirScriptFiles;	// デフォルトで読み込むスクリプトファイル(管理ディレクトリ用)
	private $defaultAdminDirCssFiles;	// デフォルトで読み込むCSSファイル(管理ディレクトリ用)
	private $headScriptFiles = array();		// ウィジェットからの追加で読み込むスクリプトファイル
	private $headCssFiles = array();		// ウィジェットからの追加で読み込むCSSファイル
	private $headRssFiles = array();		// HTMLヘッダに出力するRSS配信情報
	private $currentWidgetPosition;			// 現在のウィジェットのポジション
	private $currentWidgetIndex;			// 現在のウィジェットのポジション番号
	private $pageDefPosition;		// 現在取得しているページ定義のポジション
	private $pageDefRows;			// ページ定義レコード
	private $nonSharedWidgetCount = -1;	// 非共通ウィジェットの数(-1=ページ作成でないとき)
	private $replaceHeadDone;			// ヘッダマクロ変換処理が完了したかどうか
	private $useHelp = false;		// 標準ヘルプ機能を使用するかどうか
	private $hasScriptCache = false;	// JavaScriptファイルをブラウザにキャッシュさせるかどうか
	private $isAccessPointWithAdminMenu;				// 管理メニューを使用するアクセスポイントかどうか
	private $lateLaunchWidgetList;	// 遅延実行ウィジェットのリスト
	private $latelaunchWidgetParam;		// 遅延実行ウィジェットのパラメータ
	private $defPositions = array();	// テンプレート上のポジション(画面定義データすべて)
	private $viewPositions = array();	// テンプレート上のポジション(ウィジェット表示ありのみ)
	private $viewPosId = array();		// テンプレート上のポジションのタグID
	private $updateParentWindow;		// 親ウィンドウを再描画するかどうか
	private $updateDefSerial;			// 更新する項目のページ定義シリアル番号
	private $headDescription;				// HTMLヘッダ「description」に出力する文字列
	private $headKeywords;				// HTMLヘッダ「keywords」に出力する文字列
	private $headOthers;				// HTMLヘッダに出力するタグ文字列
	private $adminHeadOthers = '';				// HTMLヘッダに出力するタグ文字列(管理画面用)
	private $lastHeadTitle;				// 最後にヘッダ部titleにセットした文字列
	private $lastHeadDescription;		// 最後にヘッダ部descriptionにセットした文字列
	private $lastHeadKeywords;			// 最後にヘッダ部keywordsにセットした文字列
	private $headFirstTag;				// HTMLヘッダに最初に出力するタグ文字列
	private $headCss = array();			// HTMLヘッダにCSS出力する文字列
	private $headScript = array();		// HTMLヘッダにJavascript出力する文字列
	private $headString = array();		// HTMLヘッダに出力する任意文字列
	private $exportCss = array();		// 外部出力でCSS出力する文字列
	private $lastHeadCss;				// 最後に設定したHTMLヘッダにCSS出力する文字列
	private $lastHeadScript;			// 最後に設定したHTMLヘッダにJavascript出力する文字列
	private $lastHeadString;			// 最後に設定したHTMLヘッダに出力する任意文字列
	private $headSubTitle = array();				// ヘッダタグサブタイトル
	private $headSubTitleUrl = array();				// ヘッダサブタイトルのリンク先
	private $headCanonicalUrl;				// カノニカル属性URL
	private $initScript = '';				// ウィンドウ初期化時に実行されるスクリプト
	private $outputByHtml = true;				// HTMLフォーマットで出力するかどうか
	private $outputByStandardHead;		// HTML文書の標準のヘッダタグを出力するかどうか
	private $outputHead;				// HTMLヘッダ出力を行ったかどうか
	private $outputTheme;				// jQueryUIテーマ出力を行ったかどうか
	private $outputAjaxResponseBody;	// AJAX用のレスポンスボディデータかどうか
	private $isAbort;					// ページ作成処理を中断するかどうか
	private $isWidgetAbort;				// 各ウィジェット処理を中断するかどうか
	private $isRedirect;				// リダイレクトするかどうか
	private $isSystemPage;				// システム制御ページを表示するかどうか
	private $libFiles;					// javascript追加用ライブラリ
	private $pageDefRev = 234;				// 画面定義のリビジョン番号
	private $pageInfo;					// すべてのページ情報
	private $currentPageInfo;			// 現在のページのページ情報
	private $configWidgetInfo;			// ウィジェット設定画面のウィジェットの情報
	private $contentType = '';				// ページのコンテンツタイプ
	private $mainContentTypeInfo;				// フロント画面で使用する主要コンテンツタイプ
	private $subContentTypeInfo;				// フロント画面で使用する補助コンテンツタイプ
	private $mainFeatureTypeInfo;				// フロント画面で使用する主要機能タイプ
	private $adminFeatureTypeInfo;						// 管理画面専用で使用する主要機能タイプ
	private $rssVersion;					// RSSバージョン
	private $rssChannel;				// RSSチャンネルデータ
	private $selectedJQueryFilename;		// 使用対象のjQueryファイル
	private $selectedJQueryUiFilename;		// 使用対象のjQuery UIファイル
	private $urlParamOrder;					// URLパラメータの並び順
	private $wysiwygEditor;				// 管理画面用WYSIWYGエディター
	private $optionTemplateId;			// 追加設定するテンプレートID
	private $optionSubTemplateId;			// 追加設定するサブテンプレートID
	private $isContentGooglemaps;		// コンテンツにGoogleマップが含むかどうか
	private $useGooglemaps;				// Googleマップを使用するかどうか
	private $useBootstrap;				// Bootstrapを使用するかどうか
	private $isHtml5;					// HTML5で出力するかどうか
	private $ckeditorCssFiles = array();	// CKEditor用のCSSファイル
	private $ckeditorTemplateType;			// CKEditor用のテンプレートタイプ
	private $adminSubNavbarDef = array();		// 管理画面用のサブメニューバーの定義
	private $adminBreadcrumbDef = array();			// 管理画面用パンくずリスト定義
	private $adminBreadcrumbDefHelp = array();				// 管理画面用パンくずリストヘルプ定義
	const CONFIG_KEY_HEAD_TITLE_FORMAT = 'head_title_format';		// ヘッダ作成用フォーマット
//	const ADMIN_WIDGET_ID = 'admin_main';		// 管理用ウィジェットのウィジェットID
	//const CONTENT_TYPE_WIKI = 'wiki';		// ページのコンテンツタイプ(Wiki)
	const WIDGET_ID_TAG_START = '{{WIDGETID:';		// 遅延実行用タグ
	const WIDGET_ID_TAG_END = '}}';		// 遅延実行用タグ
	const WIDGET_ID_TITLE_TAG_START = '{{WIDGETID_TITLE:';		// 遅延実行用タグ(タイトル埋め込み用)
	const WIDGET_ID_TITLE_TAG_END = '}}';						// 遅延実行用タグ(タイトル埋め込み用)
	const WIDGET_ID_SEPARATOR = ',';
	const HEAD_TAGS				= '{{HEAD_TAGS}}';				// HTMLヘッダ出力用タグ
	const MENUBAR_TAGS			= '{{MENUBAR_TAGS}}';			// メニューバー出力用タグ
	const MENUBAR_SCRIPT_TAGS	= '{{MENUBAR_SCRIPT_TAGS}}';				// メニューバー出力用スクリプトタグ
	const WIDGET_ICON_IMG_SIZE = 32;			// ウィジェットアイコンサイズ
	const WIDGET_INVISIBLE_CLASS = 'm3invisible';			// 非表示ウィジェットクラス
	const WIDGET_LOGIN_STATUS_CLASS = 'm3loginstatus';		// ログイン状態連動クラス
	const WIDGET_OUTER_CLASS = 'm3_widget_outer';			// ウィジェット外枠クラス
	const WIDGET_OUTER_CLASS_HEAD_POSITION = 'm3_pos_';			// ウィジェットの外枠クラス用ヘッダ(ポジション表示用)
	const WIDGET_OUTER_CLASS_WIDGET_TAG = 'm3_';				// ウィジェットの外枠クラス用ヘッダ(ポジション表示用)
//	const WIDGET_INNER_CLASS = 'm3_widget_inner';			// ウィジェットの内側クラス
	const POSITION_TAG_HEAD = 'm3pos_';			// ポジションの識別用タグIDヘッダ
	const WIDGET_TAG_HEAD = 'm3widget_';			// ウィジェットの識別用タグIDヘッダ
//	const WIDGET_TAG_HEAD_SHORT = 'm3_';			// ウィジェットの識別用タグIDヘッダ
	const WIDGET_TYPE_TAG_HEAD = 'm3widgettype_';			// ウィジェット種別の識別用タグIDヘッダ
	const WIDTET_CLASS_NAME = 'm3widget';			// ウィジェットオブジェクトのタグクラス名
	const WIDTET_CLASS_TYPE_0 = 'm3widget_type0';			// ウィジェットオブジェクトのタグクラス(グローバル属性でない)
	const WIDTET_CLASS_TYPE_1 = 'm3widget_type1';			// ウィジェットオブジェクトのタグクラス(グローバル属性)
	const POSITION_CLASS_NAME = 'm3position';		// ポジションオブジェクトのタグクラス名
	const JOOMLA10_DEFAULT_WIDGET_MENU_PARAM = 'class="moduletable"';	// Joomla!1.0用デフォルトメニューパラメータ値
	const ADMIN_TEMPLATE = '_admin';		// PC管理用テンプレートID
	const SCRIPT_LIB_SEPARATOR = ';';			// JavaScriptライブラリ読み込み設定のライブラリの区切り
	const PAGE_ID_SEPARATOR = ',';				// ページIDとページサブID連結用
	const DEFAULT_ADMIN_FAVICON_FILE = '/images/system/favicon.ico';			// デフォルトの管理画面用faviconファイル
	const DEFAULT_FAVICON_FILE = '/favicon.ico';			// デフォルトのfaviconファイル
//	const DEFAULT_SITE_NAME = 'サイト名未設定';		// 管理画面用のデフォルトサイト名
	const DEFAULT_ADMIN_TITLE = '管理画面';			// デフォルトの管理画面名
	const WIDGET_TITLE_START = '[';					// ウィジェットのタイトルの括弧
	const WIDGET_TITLE_END = ']';					// ウィジェットのタイトルの括弧
	const DEFAULT_RSS_VERSION = '1.0';				// デフォルトのRSSのバージョン
	const CF_ACCESS_IN_INTRANET = 'access_in_intranet';		// イントラネット運用かどうか
	const CF_USE_LATEST_SCRIPT_LIB = 'dev_use_latest_script_lib';		// 最新のJavaScriptライブラリを使用するかどうか
	const CF_GOOGLE_MAPS_KEY = 'google_maps_key';				// Googleマップ利用キー
	const CF_JQUERY_VERSION = 'jquery_version';			// jQueryバージョン
	const CF_EXTERNAL_JQUERY = 'external_jquery';			// システム外部のjQueryを使用するかどうか
	const CF_WYSIWYG_EDITOR = 'wysiwyg_editor';		// 管理画面用WYSIWYGエディター
	const CF_ADMIN_JQUERY_VERSION = 'admin_jquery_version';			// 管理画面用jQueryバージョン
	const SD_HEAD_OTHERS	= 'head_others';		// ヘッダその他タグ
	const DEFAULT_THEME_DIR = '/ui/themes/';				// jQueryUIテーマ格納ディレクトリ
	const THEME_CSS_FILE = 'jquery-ui.custom.css';		// テーマファイル
//	const CONFIG_ICON_FILE = '/images/system/config.png';			// ウィジェット定義画面アイコン
//	const ADJUST_ICON_FILE = '/images/system/adjust_widget.png';	// 位置調整アイコン
//	const SHARED_ICON_FILE = '/images/system/shared.png';	// グローバル属性
//	const DELETE_ICON_FILE = '/images/system/delete.png';	// ウィジェット削除
//	const CONFIG_ICON32_FILE = '/images/system/config32.png';			// ウィジェット定義画面アイコン(ツールチップ用)
//	const ADJUST_ICON32_FILE = '/images/system/adjust_widget32.png';	// 位置調整アイコン(ツールチップ用)
	const CLOSE_BOX_ICON32_FILE = '/images/system/close_box.png';		// ウィンドウ閉じるアイコン(ツールチップ用)
	const NOTICE_ICON_FILE = '/images/system/notice16.png';		// ウィジェット配置注意アイコン
	const ALERT_ICON_FILE = '/images/system/alert16.png';		// ウィジェット配置警告アイコン
	const PREV_ICON_FILE = '/images/system/prev48.png';		// ウィンドウ「前へ」アイコン
	const NEXT_ICON_FILE = '/images/system/next48.png';		// ウィンドウ「次へ」アイコン
	const DEFAULT_READMORE_TITLE = 'もっと読む';			// もっと読むボタンのデフォルトタイトル
	const POS_HEAD_NAV_MENU = '<i class="glyphicon glyphicon-th" rel="m3help" title="ナビゲーションメニュー" data-placement="auto"></i> ';		// 特殊ポジションブロック(ナビゲーションメニュー型)
	const POS_HEAD_MAIN = '<i class="glyphicon glyphicon-stop" rel="m3help" title="メイン型" data-placement="auto"></i> ';		// 特殊ポジションブロック(メイン型)
	const WIDGET_MARK_MAIN = '<i class="glyphicon glyphicon-stop" rel="m3help" title="メイン型" data-placement="auto"></i> ';		// ウィジェットの機能マーク(メイン型ウィジェット)
	const WIDGET_MARK_NAVMENU = '<i class="glyphicon glyphicon-th" rel="m3help" title="ナビゲーションメニュー" data-placement="auto"></i> ';		// ウィジェットの機能マーク(ナビゲーションメニュー)
	const WIDGET_FUNCTION_MARK_BOOTSTRAP = ' <span class="label label-warning" rel="m3help" title="Bootstrap型テンプレート対応" data-placement="auto">B</span>';			// ウィジェット機能マーク(Boostrap型テンプレート
	const WIDGET_FUNCTION_MARK_WORDPRESS = ' <span class="label label-warning" rel="m3help" title="WordPress型テンプレート対応" data-placement="auto">W</span>';			// ウィジェット機能マーク(WordPress型テンプレート
	const WIDGET_STYLE_NAVMENU = '_navmenu';		// ウィジェットの表示スタイル(ナビゲーションメニュー)
	const WIDGET_STYLE_WORDPRESS = '_wordpress';						// ウィジェットの表示スタイル(WordPressテンプレート用出力)
	const WIDGET_STYLE_WORDPRESS_NOTITLE = '_wordpress_notitle';		// ウィジェットの表示スタイル(WordPressテンプレート(タイトルなし)用出力)
	
	// アドオンオブジェクト用
	const CONTENT_OBJ_ID	= 'contentlib';	// 汎用コンテンツオブジェクトID
	const BLOG_OBJ_ID		= 'bloglib';		// ブログオブジェクトID
	const LINKINFO_OBJ_ID	= 'linkinfo';	// リンク情報オブジェクトID
	
	// インナーウィジェット用
	const IWIDTET_CMD_CONTENT = 'content';		// コンテンツ取得
	const IWIDTET_CMD_INIT = 'init';			// 初期化
	const IWIDTET_CMD_UPDATE = 'update';		// 更新
	const IWIDTET_CMD_CALC = 'calc';			// 計算
	
	// Magic3用スクリプト
	const M3_ADMIN_SCRIPT_FILENAME			= 'm3admin2.1.1.js';				// 管理機能用スクリプト(CKEditor4.0.1対応)
	const M3_ADMIN_WIDGET_SCRIPT_FILENAME	= 'm3admin_widget2.1.2.js';	// 管理機能(ウィジェット操作)用スクリプト
	const M3_ADMIN_WIDGET_CSS_FILE			= '/m3/widget1.0.css';			// 管理機能(ウィジェット操作)用CSSファイル
	const M3_STD_SCRIPT_FILENAME			= 'm3std1.5.2.js';			// 一般、管理機能共通スクリプト
	const M3_OPTION_SCRIPT_FILENAME			= 'm3opt2.0.1.js';			// AJAXを含んだオプションライブラリファイル(jQuery必須)
	const M3_ADMIN_CSS_FILE					= 'm3/admin1.2.css';			// 管理機能用のCSS
	const M3_EDIT_CSS_FILE					= 'm3/edit.css';			// フロント画面編集用のCSS
	const M3_DEFAULT_CSS_FILE				= 'm3/default.css';			// フロント画面共通のデフォルトCSS
	const M3_CKEDITOR_CSS_FILE				= 'm3/ckeditor.css';			// CKEditorの編集エリア用CSS
	const M3_ADMIN_ICONFONT_CSS_FILE		= 'fontawesome-free-5.15.3/css/all.min.css';			// 管理機能用アイコンフォントのCSS
	
	// 読み込み制御
	const USE_BOOTSTRAP_ADMIN	= false;			// 管理画面でBootstrapを使用するかどうか(デフォルト値)
	const BOOTSTRAP_BUTTON_CLASS = 'btn btn-default';
	
	// UI
	const FILEBROWSER_WIDTH_RATIO = '0.8';			// ファイルブラウザ幅比率
	const FILEBROWSER_HEIGHT_RATIO = '0.8';			// ファイルブラウザ高さ比率
	
	// 日次処理,月次処理
	const CF_DAILY_JOB = 'daily_job';			// 日次処理を実行するかどうか
	const CF_DAILY_JOB_HOUR = 'daily_job_hour';	// 日次処理実行時間(0-23)
	const CF_DAILY_JOB_DT = 'daily_job_dt';		// 日次処理完了日時
//	const CF_MONTHLY_JOB = 'monthly_job';			// 月次処理を実行するかどうか
//	const CF_MONTHLY_JOB_HOUR = 'monthly_job_hour';	// 月次処理実行時間(0-23)
//	const CF_MONTHLY_JOB_DT = 'monthly_job_dt';		// 月次処理完了日時
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		global $gInstanceManager;
		global $gEnvManager;
		global $gSystemManager;
		global $gRequestManager;
				
		// 親クラスを呼び出す
		parent::__construct();
		
		$cmd = $gRequestManager->trimValueOf(M3_REQUEST_PARAM_OPERATION_COMMAND);
		$widgetId = $gRequestManager->trimValueOf(M3_REQUEST_PARAM_WIDGET_ID);
		
		// システムDBオブジェクト取得
		//$this->systemDb = $gInstanceManager->getSytemDbObject();
		
		// 運用方法
		$value = $gSystemManager->getSystemConfig(self::CF_ACCESS_IN_INTRANET);		// イントラネット運用かどうか
		if (empty($value)){		// インターネット運用
			$this->useGooglemaps = true;				// Googleマップを使用するかどうか
		}
		$this->useHelp = true;		// ヘルプ機能
		
		// デフォルトのWYSIWYGエディターを取得
		$this->wysiwygEditor = $gSystemManager->getSystemConfig(self::CF_WYSIWYG_EDITOR);				// 管理画面用WYSIWYGエディター
		
		// ##### Javascriptライブラリのバージョン設定 #####
		// jQueryバージョン設定
		// アクセスする画面に応じてjQueryのバージョンを設定
		if ($gEnvManager->isAdminDirAccess()){		// 管理画面へのアクセスのとき
			$version = $gSystemManager->getSystemConfig(self::CF_ADMIN_JQUERY_VERSION);// 管理画面用jQueryバージョン
			
			// 管理画面にBOOTSTRAPを使用するかどうか(初期値)
			$this->useBootstrap = self::USE_BOOTSTRAP_ADMIN;
			
			// CKEditorのバージョン設定
			// 小画面デバイス最適化の場合は最新バージョンを使用
			if ($gEnvManager->isSmallDeviceAdmin()) ScriptLibInfo::setCkeditorVer(1/*最新*/);
		} else {
			$version = $gSystemManager->getSystemConfig(self::CF_JQUERY_VERSION);// jQueryバージョン
		}
		ScriptLibInfo::setJQueryVer($version);
		
		// WYSISIGエディターのタイプを設定。ScriptLibInfo::getLib()を実行する前に設定。
		ScriptLibInfo::setWysiwygEditorType($this->wysiwygEditor);
		
		// Javascriptライブラリ
		$this->libFiles = ScriptLibInfo::getLib();			// ライブラリ取得
		
		// jQueryファイル名取得
		$this->selectedJQueryFilename = ScriptLibInfo::getJQueryFilename(0);			// 使用対象のjQueryファイル
		$this->selectedJQueryUiFilename = ScriptLibInfo::getJQueryFilename(1);		// 使用対象のjQuery UIファイル

		// ##### フロント画面用のデフォルトのJavascript、CSSを取得 #####
		$this->defaultScriptFiles	=	array(
											$this->selectedJQueryFilename,			// jQuery
											self::M3_STD_SCRIPT_FILENAME
										);
		$this->defaultCssFiles		=	array(
											self::M3_DEFAULT_CSS_FILE				// フロント画面共通のデフォルトCSS
										);
		
		// ##### 管理機能用のデフォルトのJavascript、CSSを取得 #####
		if (defined('M3_STATE_IN_INSTALL')){		// インストーラの場合のスクリプト
			$this->defaultAdminScriptFiles	=	array(
													$this->selectedJQueryFilename,			// jQuery
												//	self::M3_STD_SCRIPT_FILENAME,
													self::M3_ADMIN_SCRIPT_FILENAME
												);
			$this->defaultAdminCssFiles		= 	array(
													self::M3_ADMIN_ICONFONT_CSS_FILE			// 管理機能用アイコンフォントのCSS
												);
		} else {
			$this->defaultAdminScriptFiles	=	array(
													$this->selectedJQueryFilename,			// jQuery
													$this->selectedJQueryUiFilename,				// jQuery UI Core
													ScriptLibInfo::JQUERY_CONTEXTMENU_FILENAME,		// jQuery Contextmenu Lib
													self::M3_STD_SCRIPT_FILENAME,
													self::M3_ADMIN_SCRIPT_FILENAME,
													self::M3_OPTION_SCRIPT_FILENAME
												);
			$this->defaultAdminCssFiles		=	array(
													self::M3_ADMIN_CSS_FILE,			// 管理機能用のCSS
													self::M3_ADMIN_ICONFONT_CSS_FILE	// 管理機能用アイコンフォントのCSS
												);
			
			// Javascriptライブラリ
			$this->addAdminScript('', ScriptLibInfo::LIB_JQUERY_M3_SLIDEPANEL);	// 管理パネル用
			$this->addAdminScript('', ScriptLibInfo::LIB_JQUERY_EASING);		// 管理パネル用
			$this->addAdminScript('', ScriptLibInfo::LIB_JQUERY_NUMERIC);		// 入力制限プラグイン
//			$this->addAdminScript('', ScriptLibInfo::LIB_JQUERY_RESPONSIVETABLE);// 管理画面作成用
			$this->addAdminScript('', ScriptLibInfo::LIB_IDLETIMEOUT);	// 無操作監視用
		}
		
		// 管理権限なしで管理ディレクトリアクセスで読み込むスクリプトファイル
		$this->defaultAdminDirScriptFiles = array($this->selectedJQueryFilename);			// jQuery
		
		// 遅延ウィジェットリスト									
		$this->lateLaunchWidgetList = array();
		$this->latelaunchWidgetParam = array();
		
		// DB接続可能なときは設定値を取得
		if ($gEnvManager->canUseDb()){
			$value = $this->gSystem->getSystemConfig('script_cache_in_browser');	// ブラウザにJavaScriptファイルのキャッシュを保持するかどうか
			if ($value != '') $this->hasScriptCache = $value;
		}
		
		$this->rssVersion = self::DEFAULT_RSS_VERSION;					// RSSバージョン
		
		// フロント画面で使用する主要コンテンツタイプ
		$this->mainContentTypeInfo	 = array(
												array(	'name' => '汎用コンテンツ',				'value' => M3_VIEW_TYPE_CONTENT),
												array(	'name' => 'ブログ',						'value' => M3_VIEW_TYPE_BLOG),
												array(	'name' => '製品',						'value' => M3_VIEW_TYPE_PRODUCT),
												array(	'name' => 'BBS',						'value' => M3_VIEW_TYPE_BBS),
												array(	'name' => 'Wiki',						'value' => M3_VIEW_TYPE_WIKI),
												array(	'name' => 'イベント情報',				'value' => M3_VIEW_TYPE_EVENT),
												array(	'name' => 'フォトギャラリー',			'value' => M3_VIEW_TYPE_PHOTO),
												array(	'name' => '会員情報',					'value' => M3_VIEW_TYPE_MEMBER)
											);
		// フロント画面で使用する補助コンテンツタイプ(ページ属性に対応しない)
		$this->subContentTypeInfo	 = array(	array(	'name' => '新着情報',					'value' => M3_VIEW_TYPE_NEWS),
												array(	'name' => 'コメント',					'value' => M3_VIEW_TYPE_COMMENT),
												array(	'name' => 'イベント予約',				'value' => M3_VIEW_TYPE_EVENTENTRY),
												array(	'name' => 'バナー',						'value' => M3_VIEW_TYPE_BANNER)
											);
		// フロント画面で使用する主要機能タイプ(「ダッシュボード」は含まない)
		$this->mainFeatureTypeInfo	 = array(	array(	'name' => '検索',						'value' => M3_VIEW_TYPE_SEARCH),
												array(	'name' => 'Eコマース',					'value' => M3_VIEW_TYPE_COMMERCE),
												array(	'name' => 'カレンダー',					'value' => M3_VIEW_TYPE_CALENDAR)
											);
		// 管理画面専用で使用する機能タイプ
		$this->adminFeatureTypeInfo	 = array(	array(	'name' => 'ダッシュボード',				'value' => M3_VIEW_TYPE_DASHBOARD)
											);
											
		// URLパラメータ並び順
		$this->urlParamOrder = array(
			// コンテンツID
			M3_REQUEST_PARAM_CONTENT_ID				=> -20,		// 汎用コンテンツID
			M3_REQUEST_PARAM_CONTENT_ID_SHORT		=> -19,		// 汎用コンテンツID(略式)
			M3_REQUEST_PARAM_PRODUCT_ID				=> -18,		// 製品ID
			M3_REQUEST_PARAM_PRODUCT_ID_SHORT		=> -17,		// 製品ID(略式)
			M3_REQUEST_PARAM_BLOG_ID				=> -16,		// ブログID
			M3_REQUEST_PARAM_BLOG_ID_SHORT			=> -15,		// ブログID(略式)
			M3_REQUEST_PARAM_BLOG_ENTRY_ID			=> -14,		// ブログ記事ID
			M3_REQUEST_PARAM_BLOG_ENTRY_ID_SHORT	=> -13,		// ブログ記事ID(略式)
			M3_REQUEST_PARAM_BBS_ID					=> -12,		// 掲示板投稿記事ID
			M3_REQUEST_PARAM_BBS_ID_SHORT			=> -11,		// 掲示板投稿記事ID(略式)
			M3_REQUEST_PARAM_BBS_THREAD_ID			=> -10,		// 掲示板投稿スレッドID
			M3_REQUEST_PARAM_BBS_THREAD_ID_SHORT	=> -9,		// 掲示板投稿スレッドID(略式)
			M3_REQUEST_PARAM_EVENT_ID				=> -4,		// イベントID
			M3_REQUEST_PARAM_EVENT_ID_SHORT			=> -3,		// イベントID(略式)
			M3_REQUEST_PARAM_PHOTO_ID				=> -2,		// 画像ID
			M3_REQUEST_PARAM_PHOTO_ID_SHORT			=> -1,		// 画像ID(略式)
			// URLパラメータ
			M3_REQUEST_PARAM_PAGE_SUB_ID			=> 1,		// ページサブID
			M3_REQUEST_PARAM_PAGE_CONTENT_ID		=> 2,		// ページコンテンツID
			M3_REQUEST_PARAM_WIDGET_ID 				=> 3,		// ウィジェットID
			M3_REQUEST_PARAM_TEMPLATE_ID			=> 4,		// テンプレートID
			M3_REQUEST_PARAM_URL					=> 5,		// リンク先等のURL
			M3_REQUEST_PARAM_STAMP					=> 6,		// 公開発行ID
			M3_REQUEST_PARAM_OPTION					=> 7,		// 通信オプション
			M3_REQUEST_PARAM_OPERATION_COMMAND		=> 8,		// 実行処理
			M3_REQUEST_PARAM_OPERATION_WIKI_COMMAND	=> 9,		// Wikiコマンド実行
			M3_REQUEST_PARAM_OPERATION_TASK			=> 10,		// ウィジェット間共通処理
			M3_REQUEST_PARAM_OPERATION_ACT			=> 11,		// クライアントからの実行処理
			M3_REQUEST_PARAM_OPERATION_LANG			=> 12,		// 言語指定表示
			M3_REQUEST_PARAM_SERIAL_NO				=> 13,		// シリアル番号
			M3_REQUEST_PARAM_PAGE_NO      			=> 14,		// ページ番号
			M3_REQUEST_PARAM_LIST_NO				=> 15,		// 一覧番号
			M3_REQUEST_PARAM_ITEM_NO	      		=> 16,		// 項目番号
			M3_REQUEST_PARAM_OPERATION_TODO			=> 17,		// 指定ウィジェットに実行させる処理
			M3_REQUEST_PARAM_FROM					=> 18,		// メッセージの送信元ウィジェットID
			M3_REQUEST_PARAM_VIEW_STYLE				=> 19,		// 表示スタイル
			M3_REQUEST_PARAM_FORWARD				=> 20,		// 画面遷移用パラメータ
			M3_REQUEST_PARAM_OPEN_BY				=> 22,		// ウィンドウの開き方
			M3_REQUEST_PARAM_SHOW_HEADER			=> 23,		// ヘッダ部表示制御
			M3_REQUEST_PARAM_SHOW_FOOTER			=> 24,		// フッタ部表示制御
			M3_REQUEST_PARAM_SHOW_MENU				=> 25,		// メニュー部表示制御
			M3_REQUEST_PARAM_KEYWORD				=> 26,		// 検索キーワード
			M3_REQUEST_PARAM_HISTORY				=> 27,		// 履歴インデックスNo
			M3_REQUEST_PARAM_DEF_PAGE_ID			=> 28,		// ページID(画面編集用)
			M3_REQUEST_PARAM_DEF_PAGE_SUB_ID		=> 29,		// ページサブID(画面編集用)
			M3_REQUEST_PARAM_PAGE_DEF_SERIAL		=> 30,		// ページ定義のレコードシリアル番号(設定画面起動時)
			M3_REQUEST_PARAM_PAGE_DEF_CONFIG_ID		=> 31,		// ページ定義のウィジェット定義ID(設定画面起動時)
			M3_REQUEST_PARAM_BACK_URL				=> 32,		// 戻り先URL
			M3_REQUEST_PARAM_BACKUP_URL				=> 33,		// URL退避用(画面編集時)
			M3_REQUEST_PARAM_SERVER					=> 34,		// サーバ指定
			M3_REQUEST_PARAM_CATEGORY_ID			=> 35,		// カテゴリID(共通)
			M3_REQUEST_PARAM_WIDTH					=> 36,		// 幅
			M3_REQUEST_PARAM_HEIGHT					=> 37);		// 高さ
	}
	/**
	 * メインコンテンツタイプを取得
	 *
	 * @return array	コンテンツタイプ
	 */
	public function _getAllContentType()
	{
		global $M3_ALL_CONTENT_TYPE;
		
		return $M3_ALL_CONTENT_TYPE;
	}
	/**
	 * 機能タイプを取得
	 *
	 * @return array	機能タイプ
	 */
	public function _getAllFeatureType()
	{
		global $M3_ALL_FEATURE_TYPE;
		
/*		$featureType = array(	M3_VIEW_TYPE_DASHBOARD,			// ダッシュボード
								M3_VIEW_TYPE_SEARCH,			// 検索結果
								M3_VIEW_TYPE_COMMERCE);			// Eコマース
		return $featureType;*/
		return $M3_ALL_FEATURE_TYPE;
	}
	/**
	 * タイムアウトを停止
	 *
	 * @return なし
	 */
	function setNoTimeout()
	{
		// スクリプトの処理の中断を禁止する
		ignore_user_abort(true);
		set_time_limit(0);
	}
	/**
	 * ポップアップメッセージを設定
	 *
	 * @param string $msg   メッセージ
	 */
	function setPopupMsg($msg)
	{
		$this->popupMsg = $msg;
	}
	/**
	 * HTMLヘッダ「description」に出力する文字列を設定
	 *
	 * @param string $str   出力文字列
	 */
	function setHeadDescription($str)
	{
		if (is_string($str) && !empty($str)){
			$this->headDescription = $str;
			$this->lastHeadDescription = $str;	// 最後に設定した値を退避
		}
	}
	/**
	 * HTMLヘッダ「description」に出力する文字列を取得
	 *
	 * @return string $str   出力文字列
	 */
	function getHeadDescription()
	{
		return $this->headDescription;
	}
	/**
	 * HTMLヘッダ「keywords」に出力する文字列を設定
	 *
	 * @param string $str   出力文字列
	 */
	function setHeadKeywords($str)
	{
		if (is_string($str) && !empty($str)){
			$this->headKeywords = $str;
			$this->lastHeadKeywords = $str;		// 最後に設定した値を退避
		}
	}
	/**
	 * HTMLヘッダ「keywords」に出力する文字列を取得
	 *
	 * @return string $str   出力文字列
	 */
	function getHeadKeywords()
	{
		return $this->headKeywords;
	}
	/**
	 * HTMLヘッダに出力するタグ文字列を設定
	 *
	 * @param string $str   出力文字列
	 * @return なし
	 */
	function setHeadOthers($str)
	{
		if (is_string($str) && !empty($str)){
			$this->headOthers = $str;
		}
	}
	/**
	 * HTMLヘッダに出力するタグ文字列を追加
	 *
	 * @param string $str   出力文字列
	 * @return なし
	 */
	function addHeadOthers($str)
	{
		if (is_string($str) && !empty($str)){
			// 追加されていない場合のみ追加
			$pos = strpos($this->headOthers, $str);
			if ($pos === false) $this->headOthers .= $str;
		}
	}
	/**
	 * HTMLヘッダに出力するタグ文字列を追加(管理画面用)
	 *
	 * @param string $str   出力文字列
	 * @return なし
	 */
	function addAdminHeadOthers($str)
	{
		if (is_string($str) && !empty($str)){
			// 追加されていない場合のみ追加
			$pos = strpos($this->adminHeadOthers, $str);
			if ($pos === false) $this->adminHeadOthers .= $str;
		}
	}
	/**
	 * HTMLヘッダ「title」のサブタイトル出力する文字列を設定
	 *
	 * @param string $str   	出力文字列
	 * @param string $url   	リンク先
	 * @param bool $isDefault	デフォルト値かどうか
	 * @return					なし
	 */
	function setHeadSubTitle($str = '', $url = '', $isDefault = false)
	{
		// ##### ページのサブタイトルはデフォルトでページのMETAタイトルを使用し、メインウィジェット側で上書きする #####
		
		static $isUpdated = false;			// デフォルト値が更新されているかどうか
		
		if (!is_string($str) || empty($str)) return;
		
		// デフォルト値の場合はデータを初期化
		if ($isDefault){
			$this->headSubTitle = array();
			$this->headSubTitleUrl = array();
			$this->lastHeadTitle = '';
			$isUpdated = false;
		} else {			// デフォルトでない場合はデータを追加
			if (!$isUpdated){			// デフォルト値が更新されていない場合は以前のデータを削除
				$this->headSubTitle = array();
				$this->headSubTitleUrl = array();
				$this->lastHeadTitle = '';
				$isUpdated = true;			// デフォルト値は更新された
			}
		}
		// タイトル追加
		$this->headSubTitle[] = $str;
		$this->headSubTitleUrl[] = $url;
		$this->lastHeadTitle = $str;	// 最後に設定した値を退避
	}
	/**
	 * HTMLヘッダ「title」のサブタイトル出力する文字列を取得
	 *
	 * @return $array   サブタイトルに設定されている文字列を連想配列(title=タイトル、url=リンク先URL)の配列で返す
	 */
	function getHeadSubTitle()
	{
		$titleArray = array();
		$titleCount = count($this->headSubTitle);
		for ($i = 0; $i < $titleCount; $i++){
			$line = array('title' => $this->headSubTitle[$i], 'url' => $this->headSubTitleUrl[$i]);
			$titleArray[] = $line;
		}
		return $titleArray;
	}
	/**
	 * HTMLヘッダに出力するCSSの文字列を設定
	 *
	 * @param string $css	追加するCSS内容
	 * @return 				なし
	 */
	function addHeadCss($css)
	{
		$destCss = trim($css);
		if (!empty($destCss)){
			//if (!in_array($css, $this->headCss)) $this->headCss[] = $css;
			if (!in_array($css, $this->exportCss)) $this->exportCss[] = $css;		// CSS動的外部出力
			
			$this->lastHeadCss = $css;			// 最後に設定したHTMLヘッダにCSS出力する文字列
		}
	}
	/**
	 * 外部出力で出力するCSSの文字列を設定(ウィジェット共通設定からの設定値)
	 *
	 * @param string $css	追加するCSS内容
	 * @return 				なし
	 */
	function addExportCss($css)
	{
		$destCss = trim($css);
		if (!empty($destCss)){
			if (!in_array($css, $this->exportCss)) $this->exportCss[] = $css;
		}
	}
	/**
	 * HTMLヘッダに出力するJavascriptの文字列を設定
	 *
	 * @param string $script	追加するJavascript内容
	 * @return 					なし
	 */
	function addHeadScript($script)
	{
		$destScript = trim($script);
		if (!empty($destScript)){
			if (!in_array($script, $this->headScript)) $this->headScript[] = $script;
			
			$this->lastHeadScript = $script;			// 最後に設定したHTMLヘッダにJavascript出力する文字列
		}
	}
	/**
	 * HTMLヘッダに出力する任意文字列を設定
	 *
	 * @param string $str	追加する任意文字列
	 * @return 				なし
	 */
	function addHeadString($str)
	{
		$destScript = trim($str);
		if (!empty($destScript)){
			if (!in_array($str, $this->headString)) $this->headString[] = $str;
			
			$this->lastHeadString = $str;			// 最後に設定したHTMLヘッダに出力する任意文字列
		}
	}
	/**
	 * HTMLヘッダに出力するCSSファイルを追加
	 *
	 * @param string,array $cssFile	CSSファイルURLパス
	 * @return 				なし
	 */
	function addHeadCssFile($cssFile)
	{
		if (is_array($cssFile)){	// 配列の場合
			for ($i = 0; $i < count($cssFile); $i++){
				$destCss = trim($cssFile[$i]);
				if (!empty($destCss) && !in_array($destCss, $this->headCssFiles)) $this->headCssFiles[] = $destCss;
			}
		} else {		// 文字列の場合
			$destCss = trim($cssFile);
			if (!empty($destCss) && !in_array($destCss, $this->headCssFiles)) $this->headCssFiles[] = $destCss;
		}
	}
	/**
	 * HTMLヘッダに出力するJavascriptライブラリを追加
	 *
	 * @param string,array $scriptLib	JavascriptライブラリID
	 * @return 					なし
	 */
	function addHeadAdminScriptLib($scriptLib)
	{
		if (is_array($scriptLib)){	// 配列の場合
			for ($i = 0; $i < count($scriptLib); $i++){
				$destScript = trim($scriptLib[$i]);
				if (!empty($destScript)) $this->addAdminScript('', $destScript);// スクリプト追加
			}
		} else {		// 文字列の場合
			$destScript = trim($scriptLib);
			if (!empty($destScript)) $this->addAdminScript('', $destScript);// スクリプト追加
		}
	}
	/**
	 * HTMLヘッダに出力するJavascriptライブラリを追加
	 *
	 * @param string,array $scriptLib	JavascriptライブラリID
	 * @return 					なし
	 */
	function addHeadScriptLib($scriptLib)
	{
		if (is_array($scriptLib)){	// 配列の場合
			for ($i = 0; $i < count($scriptLib); $i++){
				$destScript = trim($scriptLib[$i]);
				if (!empty($destScript)) $this->addScript('', $destScript);// スクリプト追加
			}
		} else {		// 文字列の場合
			$destScript = trim($scriptLib);
			if (!empty($destScript)) $this->addScript('', $destScript);// スクリプト追加
		}
	}
	/**
	 * HTMLヘッダに出力するJavascriptファイルを追加
	 *
	 * @param string,array $scriptFile	JavascriptファイルURLパス
	 * @return 					なし
	 */
	function addHeadScriptFile($scriptFile)
	{
		if (is_array($scriptFile)){	// 配列の場合
			for ($i = 0; $i < count($scriptFile); $i++){
				$destScript = trim($scriptFile[$i]);
				if (!empty($destScript) && !in_array($destScript, $this->headScriptFiles)) $this->headScriptFiles[] = $destScript;
			}
		} else {		// 文字列の場合
			$destScript = trim($scriptFile);
			if (!empty($destScript) && !in_array($destScript, $this->headScriptFiles)) $this->headScriptFiles[] = $destScript;
		}
	}
	/**
	 * HTMLヘッダに出力するRSSファイルを追加
	 *
	 * @param array	$rssFile	タイトル(title)、配信用URL(href)の連想配列。複数の場合は連想配列の配列。
	 * @return 					なし
	 */
	function addHeadRssFile($rssFile)
	{
		// パラメータエラーチェック
		if (!is_array($rssFile)) return;

		if (array_keys($rssFile) === range(0, count($rssFile) - 1)){	// 複数追加の場合
			$line = $rssFile[0];	// 最初の1行のみ取得
			for ($i = 0; $i < count($line); $i++){
				// すでに追加済みかどうかチェック
				$url = trim($line[$i]['href']);
				if (!empty($url)){
					for ($j = 0; $j < count($this->headRssFiles); $j++){
						$storedUrl = $this->headRssFiles[$j]['href'];
						if ($url == $storedUrl) break;
					}
					if ($j == count($this->headRssFiles)){		// 見つからないときは追加
						$this->headRssFiles[] = array(	'href' => $url,		// リンク先URL
														'title' => $line[$i]['title']);		// タイトル
					}
				}
			}
		} else {		// 単数追加のとき
			// すでに追加済みかどうかチェック
			$url = trim($rssFile['href']);
			if (!empty($url)){
				for ($i = 0; $i < count($this->headRssFiles); $i++){
					$storedUrl = $this->headRssFiles[$i]['href'];
					if ($url == $storedUrl) break;
				}
				if ($i == count($this->headRssFiles)){		// 見つからないときは追加
					$this->headRssFiles[] = array(	'href' => $url,		// リンク先URL
													'title' => $rssFile['title']);		// タイトル
				}
			}
		}
	}
	/**
	 * HTMLヘッダに最初に出力するタグ文字列を設定
	 *
	 * @param string $str	追加するタグ文字列
	 * @return 				なし
	 */
	function setHeadFirstTag($str)
	{
		if (!empty($str)) $this->headFirstTag = $str;
	}
	/**
	 * ヘッダ部に出力するカノニカル属性URLを設定
	 *
	 * @param string $url		URL
	 * @return 					なし
	 */
	function setCanonicalUrl($url)
	{
		if (!empty($url)) $this->headCanonicalUrl = $url;				// カノニカル属性URL
	}
	/**
	 * RSSチャンネル部に出力するデータを設定
	 *
	 * @param array $rssData	RSSチャンネル部データ
	 * @return 					なし
	 */
	function setRssChannel($rssData)
	{
		if (!empty($rssData)) $this->rssChannel = $rssData;				// RSSチャンネルデータ
	}
	/**
	 * 表示ポジションを表示するかどうか
	 *
	 * @param int $mode   0=ポジション表示しない(通常画面)、1=ポジション表示、2=ウィジェット込みポジション表示
	 */
	function showPosition($mode)
	{
		$this->showPositionMode = $mode;
	}
	/**
	 * 画面レイアウト中かどうか
	 *
	 * @return bool		true=レイアウト中、false=レイアウト中でない
	 */
	function isLayout()
	{
		return $this->isLayout;				// 画面レイアウト中かどうか
	}
	/**
	 * ウィジェットの単体表示を設定
	 */
	function showWidget()
	{
		$this->showWidget = true;
	}
	/**
	 * ウィジェットの単体表示を取得
	 *
	 * @param bool		true=単体表示、false=単体表示でない
	 */
	function getShowWidget()
	{
		return $this->showWidget;
	}
	/**
	 * システム制御遷移モードを設定
	 *
	 * @param int $mode   0=設定なし、1=ログイン画面、10=サイト非公開、11=アクセス不可、12=存在しないページ
	 */
	function setSystemHandleMode($mode)
	{
		$this->systemHandleMode = $mode;
	}
	/**
	 * システム制御遷移モード取得
	 */
	function getSystemHandleMode()
	{
		return $this->systemHandleMode;
	}
	/**
	 * フロント画面編集モードを設定
	 *
	 * @return 				なし
	 */
	function setEditMode()
	{
		$this->isEditMode = true;
	}
	/**
	 * フロント画面編集モードを取得
	 *
	 * @return bool		true=編集モードオン、false=編集モードオフ
	 */
	function isEditMode()
	{
		return $this->isEditMode;
	}
	/**
	 * 機能アクセスパーソナルモードを設定
	 *
	 * @return 				なし
	 */
	function setPersonalMode()
	{
		$this->isPersonalMode = true;
	}
	/**
	 * 機能アクセスパーソナルモードを取得
	 *
	 * @return bool		true=パーソナルモードオン、false=パーソナルモードオフ
	 */
	function isPersonalMode()
	{
		return $this->isPersonalMode;
	}
	/**
	 * 出力フォーマットがHTMLであるかを設定
	 *
	 * @param bool $isHtml	HTMLフォーマットかどうか
	 * @return 				なし
	 */
	function setOutputByHtml($isHtml)
	{
		$this->outputByHtml = $isHtml;
	}
	
	/**
	 * AJAX用のレスポンスボディデータかどうかを設定
	 *
	 * @param bool $isResponseBody	レスポンスボディデータかどうか
	 * @return 						なし
	 */	
	function setOutputAjaxResponseBody($isResponseBody)
	{
		$this->outputAjaxResponseBody = $isResponseBody;
	}
	/**
	 * ヘルプ機能の使用可否を設定
	 *
	 * @param bool $status	ヘルプ機能を使用するかどうか
	 * @return 				なし
	 */
	function setUseHelp($status)
	{
		$this->useHelp = $status;
	}
	/**
	 * ヘルプ機能の使用可否を取得
	 *
	 * @return bool ヘルプ機能を使用するかどうか
	 */
	function getUseHelp()
	{
		return $this->useHelp;
	}
	/**
	 * 表示するコンテンツにGoogleマップが含まれているかを設定
	 *
	 * @param bool $status	Googleマップを含むかどうか
	 * @return 				なし
	 */
	function setIsContentGooglemaps($status)
	{
		$this->isContentGooglemaps = $status;		// コンテンツにGoogleマップが含むかどうか
	}
	/**
	 * 表示するコンテンツにGoogleマップが含まれているかを取得
	 *
	 * @return bool ヘルプ機能を使用するかどうか
	 */
	function isContentGooglemaps()
	{
		return $this->isContentGooglemaps;
	}
	/**
	 * JavaScriptのブラウザキャッシュの使用可否を設定
	 *
	 * @param bool $status	ブラウザキャッシュを使用するかどうか
	 * @return 				なし
	 */
	function setHasScriptCache($status)
	{
		$this->hasScriptCache = $status;
	}
	/**
	 * Bootstrapを使用に設定
	 *
	 * @return 				なし
	 */
	function useBootstrap()
	{
		$this->useBootstrap = true;				// Bootstrapを使用するかどうか
	}
	/**
	 * Bootstrapを強制的に未使用に設定
	 *
	 * @return 				なし
	 */
	function cancelBootstrap()
	{
		$this->useBootstrap = false;				// Bootstrapを使用するかどうか
	}
	/**
	 * Bootstrap使用状況を取得
	 *
	 * @return bool			true=使用、false=使用しない
	 */
	function getUseBootstrap()
	{
		return $this->useBootstrap;				// Bootstrapを使用するかどうか
	}
	/**
	 * HTML5を使用に設定
	 *
	 * @return 				なし
	 */
	function setHtml5()
	{
		$this->isHtml5 = true;					// HTML5で出力するかどうか
	}
	/**
	 * ページ作成処理を中断するかどうかを取得
	 *
	 * @return bool		true=中断、false=継続
	 */
	function isPageAbort()
	{
		return $this->isAbort;
	}
	/**
	 * 現在のウィジェットのポジションを取得
	 *
	 * @param string $pos		ポジション
	 * @param int    $index		インデックス番号
	 * @return			なし
	 */
	function getCurrentWidgetPosition(&$pos, &$index)
	{
		$pos = $this->currentWidgetPosition;			// 現在のウィジェットのポジション
		$index = $this->currentWidgetIndex;			// 現在のウィジェットのポジション番号
	}
	/**
	 * 親ウィンドウを再描画
	 *
	 * @param int $defSerial	ページ定義シリアル番号
	 * @return 					なし
	 */
	function updateParentWindow($defSerial = 0)
	{
		$this->updateParentWindow = true;
		$this->updateDefSerial = $defSerial;			// 更新する項目のページ定義シリアル番号
	}
	/**
	 * CSSファイルの追加
	 *
	 * @param string $path	追加するファイルのパス(「ルート/scripts」ディレクトリからの相対パスで指定する)
	 * @return 				なし
	 */
	function addCssFile($path)
	{
		$destPath = trim($path, '/');
		if (!in_array($destPath, $this->defaultCssFiles)) $this->defaultCssFiles[] = $destPath;
	}
	/**
	 * CSSファイルの追加
	 *
	 * @param string $path	追加するファイルのパス(「ルート/scripts」ディレクトリからの相対パスまたは絶対パス(scriptディレクトリ以外の場合)で指定する)
	 * @return 				なし
	 */
	function addAdminCssFile($path)
	{
		$destPath = trim($path, '/');
		if (!in_array($destPath, $this->defaultAdminCssFiles)) $this->defaultAdminCssFiles[] = $destPath;
	}
	/**
	 * 編集エリア用のCSSファイルの追加
	 *
	 * @param string $path	追加するファイルのパス(「ルート/scripts」ディレクトリからの相対パスまたは絶対パス(scriptディレクトリ以外の場合)で指定する)
	 * @return 				なし
	 */
	function addCkeditorCssFile($path)
	{
		$destPath = trim($path, '/');
		if (!in_array($destPath, $this->ckeditorCssFiles)) $this->ckeditorCssFiles[] = $destPath;
	}
	/**
	 * JavaScriptファイルの追加
	 *
	 * @param string $path	追加するファイルのパス(「ルート/scripts」ディレクトリからの相対パスで指定する)
	 * @return 				なし
	 */
	function addScriptFile($path)
	{
		$destPath = trim($path, '/');
		if (!empty($destPath) && !in_array($destPath, $this->defaultScriptFiles)) $this->defaultScriptFiles[] = $destPath;
	}
	/**
	 * JavaScriptファイルの追加
	 *
	 * @param string $path	追加するファイルのパス(「ルート/scripts」ディレクトリからの相対パスで指定する)
	 * @return 				なし
	 */
	function addAdminScriptFile($path)
	{
		$destPath = trim($path, '/');
		if (!in_array($destPath, $this->defaultAdminScriptFiles)) $this->defaultAdminScriptFiles[] = $destPath;
	}
	/**
	 * 追加設定するテンプレートIDを返す
	 *
	 * @return array						テンプレートID,サブテンプレートIDの配列
	 */
	function getOptionTemplateId()
	{
		global $gEnvManager;
		
		// ページ情報取得
		$pageId = $gEnvManager->getCurrentPageId();

		switch ($this->contentType){
			case M3_VIEW_TYPE_CONTENT:		// ページのコンテンツタイプ				
				// コンテンツ単位のテンプレート設定
				$contentLibObj = $this->gInstance->getObject(self::CONTENT_OBJ_ID);
				if (isset($contentLibObj)) list($this->optionTemplateId, $this->optionSubTemplateId) = $contentLibObj->getOptionTemplate();
				break;
			case M3_VIEW_TYPE_BLOG:		// ページがブログタイプのとき
				if ($pageId == $this->gEnv->getDefaultPageId()){		// PCサイトのとき
					// ブログライブラリオブジェクトからテンプレートを取得
					$blogLibObj = $this->gInstance->getObject(self::BLOG_OBJ_ID);
					if (isset($blogLibObj)) list($this->optionTemplateId, $this->optionSubTemplateId) = $blogLibObj->getOptionTemplate();
				}
				break;
		}
		return array($this->optionTemplateId, $this->optionSubTemplateId);
	}
	/**
	 * 使用した非共通ウィジェットの数を取得
	 *
	 * @return int				ウィジェット数
	 */
	function getNonSharedWidgetCount()
	{
		return $this->nonSharedWidgetCount;
	}
	/**
	 * ページのコンテンツタイプを取得
	 *
	 * @return string			コンテンツタイプ
	 */
	function getContentType()
	{
		return $this->contentType;
	}
	/**
	 * フロント画面で使用する主要コンテンツタイプの情報取得
	 *
	 * @return array			コンテンツタイプの情報の連想配列
	 */
	function getMainContentTypeInfo()
	{
		return $this->mainContentTypeInfo;				// 主要コンテンツタイプ
	}
	/**
	 * フロント画面で使用する補助コンテンツタイプの情報取得
	 *
	 * @return array			コンテンツタイプの情報の連想配列
	 */
	function getSubContentTypeInfo()
	{
		return $this->subContentTypeInfo;				// 補助コンテンツタイプ
	}
	/**
	 * フロント画面で使用する主要コンテンツタイプを取得
	 *
	 * @return array			コンテンツタイプの配列
	 */
	function getMainContentTypes()
	{
		// 「value」値のみ取得
		//return array_map(create_function('$a', 'return $a["value"];'), $this->mainContentTypeInfo);
		$types = array_map(function($a){ return $a["value"]; }, $this->mainContentTypeInfo);
		return $types;
	}
	/**
	 * フロント画面で使用するサブコンテンツタイプを取得
	 *
	 * @return array			コンテンツタイプの配列
	 */
	function getSubContentTypes()
	{
		// 「value」値のみ取得
//		return array_map(create_function('$a', 'return $a["value"];'), $this->subContentTypeInfo);
		$types = array_map(function($a){ return $a["value"]; }, $this->subContentTypeInfo);
		return $types;
	}
	/**
	 * フロント画面で使用する主要機能タイプ情報を取得
	 *
	 * @return array			機能タイプの情報の連想配列
	 */
	function getMainFeatureTypeInfo()
	{
		return $this->mainFeatureTypeInfo;				// 主要機能タイプ
	}
	/**
	 * フロント画面で使用する主要機能タイプを取得
	 *
	 * @return array			機能タイプの配列
	 */
	function getMainFeatureTypes()
	{
		// 「value」値のみ取得
//		return array_map(create_function('$a', 'return $a["value"];'), $this->mainFeatureTypeInfo);
		$types = array_map(function($a){ return $a["value"]; }, $this->mainFeatureTypeInfo);
		return $types;
	}
	
	/**
	 * すべてのページ属性情報を取得
	 *
	 * @return array			ページ属性情報の連想配列
	 */
	function getAllPageAttributeTypeInfo()
	{
		return array_merge($this->mainContentTypeInfo, $this->subContentTypeInfo, $this->mainFeatureTypeInfo, $this->adminFeatureTypeInfo);
	}
	/**
	 * 管理画面用のサブメニューバーの定義を設定
	 *
	 * @param array $def	メニューバー定義
	 * @return 				なし
	 */
	function setAdminSubNavbarDef($def)
	{
		$this->adminSubNavbarDef = $def;
	}
	/**
	 * 管理画面用のサブメニューバーの定義を取得
	 *
	 * @return array メニューバーの定義
	 */
	function getAdminSubNavbarDef()
	{
		return $this->adminSubNavbarDef;
	}
	/**
	 * 管理画面用パンくずリスト定義を設定
	 *
	 * @param array $def	パンくずリスト定義
	 * @param array $help	ヘルプ(title,bodyの連想配列)
	 * @return 				なし
	 */
	function setAdminBreadcrumbDef($def, $help = array())
	{
		$this->adminBreadcrumbDef = $def;
		if (!empty($help)) $this->adminBreadcrumbDefHelp = $help;				// 管理画面用パンくずリストヘルプ定義
	}
	/**
	 * 管理画面用パンくずリスト定義を取得
	 *
	 * @param array $help	ヘルプ(title,bodyの連想配列)
	 * @return array		パンくずリスト定義
	 */
	function getAdminBreadcrumbDef(&$help)
	{
		// パラメータ数を取得
		$argNum = func_num_args();
		
		// ヘルプ取得用パラメータが存在する場合は定義を設定
		if ($argNum > 0) $help = $this->adminBreadcrumbDefHelp;

		return $this->adminBreadcrumbDef;
	}
	/**
	 * ページ作成開始
	 *
	 * HTTPヘッダを設定する。セッションを取得する。サブページIDを設定する。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @return 								なし
	 */
	function startPage($request)
	{
		global $gEnvManager;
		global $gRequestManager;
		global $gInstanceManager;
		global $gAccessManager;
		global $gSystemManager;
		global $gDispManager;
		global $gOpeLogManager;
		
		// 実行コマンドを取得
		$cmd = $request->trimValueOf(M3_REQUEST_PARAM_OPERATION_COMMAND);
		$task = $request->trimValueOf(M3_REQUEST_PARAM_OPERATION_TASK);
		$openBy = $request->trimValueOf(M3_REQUEST_PARAM_OPEN_BY);		// ウィンドウオープンタイプ
		$pageId = $gEnvManager->getCurrentPageId();

		// ###### クライアントIDの読み込み、再設定 ######
		// この後、クライアントIDがアクセスログに記録される
		$clientId = $request->getCookieValue(M3_COOKIE_CLIENT_ID);
		if (empty($clientId)){	// クライアントIDが設定されていないとき(初回アクセス)
			// クライアントIDを生成
			$clientId = $this->gAccess->createClientId();
		} else {
			$this->gAccess->setClientId($clientId);	// クライアントIDを設定
		
			// クッキーの使用可否を設定
			$this->gEnv->setCanUseCookie(true);
		}
		$request->setCookieValue(M3_COOKIE_CLIENT_ID, $clientId, M3_COOKIE_EXPIRE_CLIENT_ID);
			
		// インストール時の管理画面用ライブラリを追加(フレームコンテナでの設定を反映)
		if (defined('M3_STATE_IN_INSTALL')){
			// Bootstrapライブラリ追加
			if ($this->useBootstrap){
				$this->addAdminScript('', ScriptLibInfo::LIB_BOOTSTRAP);		// 管理画面でBootstrapを使用するかどうか
				$this->addAdminScript('', ScriptLibInfo::LIB_BOOTSTRAP_ADMIN);	// Bootstrap管理画面オプション
			}
		} else {
			// 管理者キーがあればGETまたはPOST値のセッションIDを使用する
//			if ($gEnvManager->isAdminDirAccess()){
//				if ($gAccessManager->isValidAdminKey()) session_id($gRequestManager->trimValueOf(session_name()));
//			}
		}

		// 最終HTML(ページ全体で使用するHTML)の出力
		if ($cmd == M3_REQUEST_CMD_CSS){		// CSS生成のとき
			$gRequestManager->stopSessionUpdate();			// セッションの更新を停止
		}
		
		// セッション変数を取得可能にする
		session_start();
		
		// ##### インストール時はここで終了 #####
		if (defined('M3_STATE_IN_INSTALL')) return;		// インストール時は実行しない
		
		// ### 非同期処理を実行(サーバ接続コネクターの起動を除く) ###
		if (!$gEnvManager->isServerConnector()) $this->launchJobs();
		
		// セッションを再生成する(セキュリティ対策)
		if ($gSystemManager->regenerateSessionId()){
			$gAccessManager->setOldSessionId(session_id());		// 古いセッションIDを保存
			session_regenerate_id(true);
		}
		
		// セッションからユーザ情報を取得
		$userInfo = $gRequestManager->getSessionValueWithSerialize(M3_SESSION_USER_INFO);
		if (!$gAccessManager->checkSessionSecurity($userInfo)) return;			// セキュリティ問題ありの場合はここで終了
		// ##### ここで終了した場合はページサブIDが未設定のためページ不正のエラーが発生しアクセスエラー画面が表示される #####
		
		// ##### POST時のリファラーチェック #####
		// WooCommerceのAJAX処理が通らないのでチェックしない?
/*		if ($gRequestManager->isPostMethod() && !$this->_checkReferer()){
			$errMsg = '不正なPOSTデータ(リファラー異常)を検出しました。アクセス元IP: ' . $gAccessManager->getClientIp();
			$msgDetail = 'アクセスをブロックしました。account=' . $userInfo->account . ', userid=' . $userInfo->userId;
			$gOpeLogManager->writeUserError(__METHOD__, $errMsg, 2210, $msgDetail);
			return;
		}*/
		
		// その他セッション情報取得
		$gRequestManager->_doSessionOpenEventCallback();

		// ユーザ情報をロード
		$gInstanceManager->setUserInfo($userInfo);

		// ##### 自動ログイン #####
		// ログイン中でない場合は自動ログインでユーザ情報を取得
		$gAccessManager->startAutoLogin();
		
		// デバッグモードの表示
		if (M3_SYSTEM_DEBUG) echo 'Debug mode<br />';
		
		// ##### ユーザアクセスモードの設定 #####
		if (!empty($userInfo) && $userInfo->isPersonal()) $this->setPersonalMode();		// パーソナルモードをオン
/*		if (($userInfo->userType == UserInfo::USER_TYPE_MANAGER && $userInfo->userOptType == UserInfo::USER_OPT_TYPE_PAGE_MANAGER)||			// ページ運用者の場合
			$userInfo->userType < UserInfo::USER_TYPE_MANAGER){			// システム運用者よりも権限が低い場合
			
			// パーソナルモードをオン
			$this->setPersonalMode();
		}*/

		// ##### ページIDの修正 #####
		// ポジション表示の場合はページID、ページサブIDを再設定。再設定はシステム管理者のみ可能。
		if (($cmd == M3_REQUEST_CMD_SHOW_POSITION ||					// 管理画面(ウィジェットなしポジション表示)のとき
			$cmd == M3_REQUEST_CMD_SHOW_POSITION_WITH_WIDGET) &&		// 管理画面(ウィジェット付きポジション表示)のとき
//			$gEnvManager->isSystemAdmin()){
			$gEnvManager->isSystemManageUser()){	// システム運用可能の場合(2018/8/5変更)

			$pageId = $request->trimValueOf(M3_REQUEST_PARAM_DEF_PAGE_ID);
			if (empty($pageId)) $pageId = $this->gEnv->getDefaultPageId();		// 値がないときはデフォルトのページIDを設定
			$this->gEnv->setCurrentPageId($pageId);
			$subId = $request->trimValueOf(M3_REQUEST_PARAM_DEF_PAGE_SUB_ID);
			if (!empty($subId)) $this->gEnv->setCurrentPageSubId($subId);
		}
		
		// ##### サブページIDの設定 #####
		if ($gEnvManager->isAdminDirAccess() &&		// 管理画面へのアクセスのとき
			($cmd == M3_REQUEST_CMD_LOGIN || $cmd == M3_REQUEST_CMD_LOGOUT)){				// ログイン、ログアウト場合は管理画面のページサブIDを指定
			$subId = $gEnvManager->getAdminDefaultPageSubId();		// 管理画面用のデフォルトページサブID
		} else {
			if (empty($subId)) $subId = $request->trimValueOf(M3_REQUEST_PARAM_PAGE_SUB_ID);// ページサブIDを取得
			
			if (empty($subId)){			// サブページIDが設定されていないとき
				// URLパラメータからコンテンツ形式を取得し、ページを選択
				$params = $gRequestManager->getQueryArray();
				$paramCount = count($params);

				// コマンド以外のパラメータ数が1つだけのときはパラメータからページ属性を判断する
				// 値が空でもキーがあれば属性を持つとする
				if ($paramCount == 0){
					$this->isPageTopUrl = true;			// ページトップ(サブページ内のトップ位置)のURLかどうか
					
					//if ($gEnvManager->isAdminDirAccess() && $gEnvManager->isSystemAdmin() && empty($task)){
					if ($gEnvManager->isAdminDirAccess() && $gEnvManager->isSystemManageUser() && empty($task)){
						// ダッシュボード機能は、パラメータなし、管理者ディレクトリ、システム運用者の条件で使用可能(2018/8/5変更)
						// POST値にタスクがある場合はダッシュボードとしない
						$subId = $this->systemDb->getSubPageIdWithContent(M3_VIEW_TYPE_DASHBOARD, $gEnvManager->getCurrentPageId());// ページサブIDを取得
					}
				} else if ($paramCount > 0 && !$gEnvManager->isAdminDirAccess()){		// パラメータ付きの場合(2013/3/23)
					// ##### ページ属性から画面を選択(管理画面は対応しない) ###
					// 最初のURLパラメータでコンテンツを判断
					// プレビュー(cmd=preview)等のパターンで複数パラメータがある
					reset($params);
					$firstKey = key($params);
					$firstValue = $params[$firstKey];

					if (!empty($firstValue)){		// 「key=value」の形式であること
						switch ($firstKey){
							case M3_REQUEST_PARAM_CONTENT_ID:		// コンテンツIDのとき
							case M3_REQUEST_PARAM_CONTENT_ID_SHORT:
								// ### 値をチェックし不正文字がある場合はエラー画面へ遷移 ###
								$checkStatus = $this->_checkFirstValueRedirect($firstValue);
								if (!$checkStatus) return;			// エラーの場合は終了
							
								// ローカルメニューのURLからページを特定。ページが特定できないときはページ属性で取得。
								$url = $gEnvManager->getMacroPath($gEnvManager->getCurrentRequestUri());
								$ret = $this->systemDb->getSubPageIdByMenuItemUrl($url, $gEnvManager->getCurrentPageId(), M3_VIEW_TYPE_CONTENT, $rows);
								if ($ret){
									$rowCount = count($rows);
									for ($i = 0; $i < $rowCount; $i++){
										// コンテンツを表示するウィジェットがあるときはページサブIDを確定
										//$widgetId = $this->systemDb->getWidgetIdByType($gEnvManager->getCurrentPageId(), $rows[$i]['pd_sub_id'], M3_VIEW_TYPE_CONTENT);
										$widgetId = $this->systemDb->getWidgetIdByContentType($gEnvManager->getCurrentPageId(), $rows[$i]['pd_sub_id'], M3_VIEW_TYPE_CONTENT);// コンテンツタイプでの取得に変更(2012/6/20)
										if (!empty($widgetId)){
											$subId = $rows[$i]['pd_sub_id'];
											break;
										}
									}
								}
								if (empty($subId)) $subId = $this->systemDb->getSubPageIdWithContent(M3_VIEW_TYPE_CONTENT, $gEnvManager->getCurrentPageId());// ページサブIDを取得
								$this->contentType = M3_VIEW_TYPE_CONTENT;		// ページのコンテンツタイプ
								
								// コンテンツ詳細ページかどうかを設定
								if ($firstKey == M3_REQUEST_PARAM_CONTENT_ID || $firstKey == M3_REQUEST_PARAM_CONTENT_ID_SHORT) $this->isContentDetailPage = true;
								break;
							case M3_REQUEST_PARAM_PRODUCT_ID:	// 製品IDのとき
							case M3_REQUEST_PARAM_PRODUCT_ID_SHORT:
								// ### 値をチェックし不正文字がある場合はエラー画面へ遷移 ###
								$checkStatus = $this->_checkFirstValueRedirect($firstValue);
								if (!$checkStatus) return;			// エラーの場合は終了
								
								$subId = $this->systemDb->getSubPageIdWithContent(M3_VIEW_TYPE_PRODUCT, $gEnvManager->getCurrentPageId());// ページサブIDを取得
								$this->contentType = M3_VIEW_TYPE_PRODUCT;		// ページのコンテンツタイプ
								
								// コンテンツ詳細ページかどうかを設定
								$this->isContentDetailPage = true;
								break;
							case M3_REQUEST_PARAM_BBS_ID:		// 掲示板投稿記事のとき
							case M3_REQUEST_PARAM_BBS_ID_SHORT:
							case M3_REQUEST_PARAM_BBS_THREAD_ID:
							case M3_REQUEST_PARAM_BBS_THREAD_ID_SHORT:
								// ### 値をチェックし不正文字がある場合はエラー画面へ遷移 ###
								$checkStatus = $this->_checkFirstValueRedirect($firstValue);
								if (!$checkStatus) return;			// エラーの場合は終了
								
								$subId = $this->systemDb->getSubPageIdWithContent(M3_VIEW_TYPE_BBS, $gEnvManager->getCurrentPageId());// ページサブIDを取得
								$this->contentType = M3_VIEW_TYPE_BBS;		// ページのコンテンツタイプ
								
								// コンテンツ詳細ページかどうかを設定
								if ($firstKey == M3_REQUEST_PARAM_BBS_THREAD_ID || $firstKey == M3_REQUEST_PARAM_BBS_THREAD_ID_SHORT) $this->isContentDetailPage = true;
								break;
							case M3_REQUEST_PARAM_EVENT_ID:		// イベント記事のとき
							case M3_REQUEST_PARAM_EVENT_ID_SHORT:
								// ### 値をチェックし不正文字がある場合はエラー画面へ遷移 ###
								$checkStatus = $this->_checkFirstValueRedirect($firstValue);
								if (!$checkStatus) return;			// エラーの場合は終了
								
								$subId = $this->systemDb->getSubPageIdWithContent(M3_VIEW_TYPE_EVENT, $gEnvManager->getCurrentPageId());// ページサブIDを取得
								$this->contentType = M3_VIEW_TYPE_EVENT;		// ページのコンテンツタイプ
								
								// コンテンツ詳細ページかどうかを設定
								$this->isContentDetailPage = true;
								break;
							case M3_REQUEST_PARAM_PHOTO_ID:		// フォトギャラリー写真のとき
							case M3_REQUEST_PARAM_PHOTO_ID_SHORT:
								// ### 値をチェックし不正文字がある場合はエラー画面へ遷移 ###
								$checkStatus = $this->_checkFirstValueRedirect($firstValue);
								if (!$checkStatus) return;			// エラーの場合は終了
								
								$subId = $this->systemDb->getSubPageIdWithContent(M3_VIEW_TYPE_PHOTO, $gEnvManager->getCurrentPageId());// ページサブIDを取得
								$this->contentType = M3_VIEW_TYPE_PHOTO;		// ページのコンテンツタイプ
								
								// コンテンツ詳細ページかどうかを設定
								$this->isContentDetailPage = true;
								break;
							case M3_REQUEST_PARAM_BLOG_ID:		// ブログIDのとき
							case M3_REQUEST_PARAM_BLOG_ID_SHORT:
							case M3_REQUEST_PARAM_BLOG_ENTRY_ID:
							case M3_REQUEST_PARAM_BLOG_ENTRY_ID_SHORT:
								// ### 値をチェックし不正文字がある場合はエラー画面へ遷移 ###
								$checkStatus = $this->_checkFirstValueRedirect($firstValue);
								if (!$checkStatus) return;			// エラーの場合は終了
		
								$subId = $this->systemDb->getSubPageIdWithContent(M3_VIEW_TYPE_BLOG, $gEnvManager->getCurrentPageId());// ページサブIDを取得
								$this->contentType = M3_VIEW_TYPE_BLOG;		// ページのコンテンツタイプ
								
								// コンテンツ詳細ページかどうかを設定
								if ($firstKey == M3_REQUEST_PARAM_BLOG_ENTRY_ID || $firstKey == M3_REQUEST_PARAM_BLOG_ENTRY_ID_SHORT) $this->isContentDetailPage = true;
								break;
							default:		// オプションのURLコンテンツパラメータからサブページIDを取得
								$ret = $this->systemDb->getSubPageIdByUrlContentParam($gEnvManager->getCurrentPageId(), $firstKey, $row);
								if ($ret) $subId = $row['pd_sub_id'];
								break;
						}
					}
				}

				// wiki用パラメータの取得
				if (empty($subId)){
					// 「http://www.example.com?ページ名」「wcmd」の場合はwikiコンテンツページを選択
					$wikiCmd = $request->trimValueOf(M3_REQUEST_PARAM_OPERATION_WIKI_COMMAND);
					$pageName = $gRequestManager->getWikiPageFromQuery();		// 「=」なしのパラメータはwikiパラメータとする
			
					if (!empty($wikiCmd) || !empty($pageName)){			// Wikiコンテンツページを指定のとき
						// ページサブIDを取得
						$subId = $this->systemDb->getSubPageIdWithContent(M3_VIEW_TYPE_WIKI, $gEnvManager->getCurrentPageId());
						$this->contentType = M3_VIEW_TYPE_WIKI;		// ページのコンテンツタイプ
						
						// コンテンツ詳細ページかどうかを設定
						$this->isContentDetailPage = true;
					}
				}
				// その他のGET,POSTパラメータからページサブID取得
				if (empty($subId)){
					// 検索用パラメータなどでリダイレクト先のURLが取得できた場合は遷移
					$subId = $this->getPageSubIdByParam($request, $redirectUrl);
					if (!empty($subId) && !empty($redirectUrl)) $this->redirect($redirectUrl);
				}
				
				// ページサブIDが取得できない場合はデフォルトを使用
				if (empty($subId)) $subId = $gEnvManager->getDefaultPageSubId();
			} else {		// ページサブIDが設定されているとき
				// URLパラメータからコンテンツ形式を取得し、ページを選択
				$params = $gRequestManager->getQueryArray();
				$paramCount = count($params);
				if ($paramCount == 1) $this->isPageTopUrl = true;			// ページトップ(サブページ内のトップ位置)のURLかどうか
			}
		}
		$gEnvManager->setCurrentPageSubId($subId);// サブページIDを設定
		
		// SSL通信機能がオンの場合は、アクセスされたURLのSSLをチェックし不正の場合は正しいURLにリダイレクト
		// 設定に間違いがある場合、管理画面にアクセスできなくなるので、フロント画面のみ制御
//		if ($gEnvManager->getUseSsl() || $gEnvManager->getUseSslAdmin()){
		if ($gEnvManager->getUseSsl()){			// フロント画面のSSL設定のみチェック(2016/7/30)
			if (!$gEnvManager->isAdminDirAccess()){		// 管理画面以外へのアクセスのとき
				// ルートURLがSSLの場合はSSLに固定
				if ($gEnvManager->isRootUrlSsl()){		// ルートURLがSSLの場合はフロント画面のすべてがSSL付き
					$isSsl = true;
				} else {
					$isSsl = $gEnvManager->isSslByCurrentPage();
				}
				$currentUrl = $gEnvManager->getCurrentRequestUri();
				if ($isSsl){
					$correctUrl = str_replace('http://', 'https://', $currentUrl);
				} else {
					$correctUrl = str_replace('https://', 'http://', $currentUrl);
				}
				if ($currentUrl != $correctUrl) $this->redirect($correctUrl);
			}
		}
		// マルチドメイン用設定初期化
		$gEnvManager->initMultiDomain();
		
		// 画面設定取得
		$gDispManager->load();
		
		// Magic3管理用のスクリプトを追加
		if ($gEnvManager->isAdminDirAccess()){		// 管理画面へのアクセスのとき
			if ($gEnvManager->isSystemManageUser()){		// システム運用権限がある場合のみ有効
//					$this->isEditMode = true;			// フロント画面編集モード
				$this->isPageEditable = true;		// フロント画面ページ編集可能モードに設定(コンテキストメニュー表示)
					
				// 管理画面用ライブラリを追加
				if ($cmd == M3_REQUEST_CMD_CONFIG_WIDGET){	// ウィジェット詳細設定画面のとき
				//if ($cmd == M3_REQUEST_CMD_CONFIG_WIDGET || $cmd == M3_REQUEST_CMD_CONFIG_TEMPLATE){						// ウィジェット設定画面またはテンプレート設定画面のとき
					$this->addAdminScript('', ScriptLibInfo::getWysiwygEditorLibId());	// WYSIWYGエディターを追加
					
					// Googleマップライブラリの読み込み
					if ($this->useGooglemaps && $this->wysiwygEditor == ScriptLibInfo::LIB_CKEDITOR){			// CKEditorの場合はGoogleマップライブラリを読み込む
						$this->defaultAdminScriptFiles[] = ScriptLibInfo::getScript(ScriptLibInfo::LIB_GOOGLEMAPS);
					}
				} else if ($cmd == M3_REQUEST_CMD_SHOW_POSITION_WITH_WIDGET){		// 管理画面(ウィジェット付きポジション表示)のとき
					$this->isLayout = true;		// 画面レイアウト中かどうか
					$this->addAdminScript('', ScriptLibInfo::LIB_JQUERY_IDTABS);			// 管理パネル用スクリプト追加(ポジション表示追加分)
					$this->addAdminScript('', ScriptLibInfo::LIB_JQUERY_M3_DROPDOWN);		// 管理パネル用スクリプト追加(ドロップダウンメニュー)
					//$this->useBootstrap = true;		// Bootstrapを使用
					//$this->addAdminScript('', ScriptLibInfo::LIB_JQUERY_JQEASYPANEL);		// パネルメニュー(フロント画面と管理画面の切り替え等)用
				}
				$this->addAdminScript('', ScriptLibInfo::LIB_JQUERY_HOVERINTENT);// HELP用スクリプト追加
				$this->addAdminScript('', ScriptLibInfo::LIB_JQUERY_CLUETIP);// HELP用スクリプト追加
				
				// スクリプトが必要なウィジェットをすべて取得
				$this->systemDb->getWidgetsIdWithLib($gEnvManager->getCurrentPageId(), $gEnvManager->getCurrentPageSubId(), $rows);
				for ($i = 0; $i < count($rows); $i++){
					$this->addAdminScript($task, trim($rows[$i]['wd_add_script_lib']));
				}
				if ($cmd == M3_REQUEST_CMD_CONFIG_WIDGET){	// ウィジェット詳細設定画面のとき
					// ウィジェット情報取得
					$widgetId = $request->trimValueOf(M3_REQUEST_PARAM_WIDGET_ID);
					$ret = $this->systemDb->getWidgetInfo($widgetId, $this->configWidgetInfo);
					if ($ret) $this->addAdminScript($task, trim($this->configWidgetInfo['wd_add_script_lib_a']));		// 管理機能用スクリプト
				}
			}
		} else {		// フロント画面へのアクセスのとき
			// フロント画面用スクリプトファイル追加
			$this->addScriptFile($this->selectedJQueryFilename);// 常にjQueryを使用
	
			if ($cmd != M3_REQUEST_CMD_DO_WIDGET &&							// ウィジェット単体実行でない
				$cmd != M3_REQUEST_CMD_RSS){								// RSS配信でない
				if ($gEnvManager->isSystemManageUser()){
					$this->isEditMode = true;			// フロント画面編集モード
					$this->isPageEditable = true;		// フロント画面ページ編集可能モードに設定
				
					// システム運用権限がある場合は管理用スクリプトを追加
					// フロント画面と管理画面の切り替え用のスライドメニューバーには管理用スクリプト,CSSが必要
					$this->addScriptFile($this->selectedJQueryFilename);		// JQueryスクリプト追加
					$this->addScriptFile(ScriptLibInfo::JQUERY_CONTEXTMENU_FILENAME);		// jQuery Contextmenu Lib
					$this->addScriptFile(self::M3_ADMIN_SCRIPT_FILENAME);		// 管理スクリプトライブラリ追加
					$this->addScriptFile(self::M3_OPTION_SCRIPT_FILENAME);	// Magic3のオプションライブラリ追加
					//$this->addScript('', ScriptLibInfo::LIB_JQUERY_JQEASYPANEL);		// パネルメニュー(フロント画面と管理画面の切り替え等)用
					$this->addScript('', ScriptLibInfo::LIB_JQUERY_M3_SLIDEPANEL);	// 管理パネル用スクリプト追加
					$this->addScript('', ScriptLibInfo::LIB_JQUERY_COOKIE);			// 管理パネル用スクリプト追加
					$this->addScript('', ScriptLibInfo::LIB_JQUERY_EASING);			// 管理パネル用スクリプト追加
					$this->addScript('', ScriptLibInfo::LIB_JQUERY_HOVERINTENT);// HELP用スクリプト追加
					$this->addScript('', ScriptLibInfo::LIB_JQUERY_CLUETIP);// HELP用スクリプト追加
					$this->addScript('', ScriptLibInfo::LIB_IDLETIMEOUT);	// 無操作監視用
				
					$this->addCssFile(self::M3_ADMIN_CSS_FILE);				// 管理機能用CSS
					$this->addCssFile(self::M3_ADMIN_ICONFONT_CSS_FILE);	// 管理機能用アイコンフォントのCSS
				} else if ($gEnvManager->isContentEditableUser()){		// コンテンツ編集可能ユーザの場合
					$this->isEditMode = true;			// フロント画面編集モード
				}
			} else if ($cmd == M3_REQUEST_CMD_DO_WIDGET && !empty($openBy)){						// ウィジェット単体実行でウィンドウを持つ場合の追加スクリプト
				if ($gEnvManager->isContentEditableUser()){		// コンテンツ編集可能ユーザの場合
					$this->isEditMode = true;			// フロント画面編集モード
				
//						$this->addScript('', ScriptLibInfo::LIB_JQUERY_RESPONSIVETABLE);// 管理画面作成用
					$this->addScript('', ScriptLibInfo::getWysiwygEditorLibId());	// WYSIWYGエディターを追加
					$this->addScriptFile(self::M3_ADMIN_SCRIPT_FILENAME);		// 管理スクリプトライブラリ追加
					$this->addScriptFile(self::M3_OPTION_SCRIPT_FILENAME);	// Magic3のオプションライブラリ追加
					$this->addScript('', ScriptLibInfo::LIB_JQUERY_HOVERINTENT);// HELP用スクリプト追加
					$this->addScript('', ScriptLibInfo::LIB_JQUERY_CLUETIP);// HELP用スクリプト追加
					$this->addScript('', ScriptLibInfo::LIB_IDLETIMEOUT);	// 無操作監視用
					
					// Googleマップライブラリの読み込み
					if ($this->useGooglemaps && $this->wysiwygEditor == ScriptLibInfo::LIB_CKEDITOR){			// CKEditorの場合はGoogleマップライブラリを読み込む
						$this->addScriptFile(ScriptLibInfo::getScript(ScriptLibInfo::LIB_GOOGLEMAPS));
					}
				}
			}
		}

		// デフォルトのページ情報を取得
		$row = $this->getPageInfo($gEnvManager->getCurrentPageId(), $gEnvManager->getCurrentPageSubId());
		if (!empty($row)){
			// ショートURLで取得できない場合は、ページコンテンツタイプを取得
			if (empty($this->contentType)) $this->contentType = $row['pn_content_type'];
		
			// 現在のページ情報を設定
			$this->currentPageInfo = $row;			// 現在のページのページ情報
		}
			
		if ($cmd == M3_REQUEST_CMD_SHOW_POSITION_WITH_WIDGET){		// 管理画面(ウィジェット付きポジション表示)のとき
			// テンプレートの情報を取得
			$ret = $this->systemDb->getPageDefOnPage($gEnvManager->getCurrentPageId(), $gEnvManager->getCurrentPageSubId(), $rows);
			if ($ret){
				for ($i = 0; $i < count($rows); $i++){
					$position = $rows[$i]['pd_position_id'];
					if (!in_array($position, $this->defPositions)) $this->defPositions[] = $position;	// 画面定義データのポジション(すべて)
				}
			}
		}
		
		// 画面透過モードを設定
		if ($openBy == 'tabs') $this->isTransparentMode = true;		// 画面透過モード
	}
	/**
	 * URLの最初のパラメータが不正文字の場合はエラー画面へリダイレクト
	 *
	 * @param string $value			URLの最初のパラメータ値
	 * @return bool					true=正常、false=異常
	 */
	function _checkFirstValueRedirect($value)
	{
		global $gEnvManager;
		global $gOpeLogManager;
		
		// 半角英小文字大文字、数字、アンダーバー、ハイフン、カンマのみ使用可能。
		if (preg_match("/[^0-9a-zA-Z-_,]/", $value)){
			$errMessage = 'URLパラメータの不正。';
			$gOpeLogManager->writeUserAccess(__METHOD__, '不正なアクセスを検出しました。' . $errMessage, 2200, 'アクセスをブロックしました。URL: ' . $gEnvManager->getCurrentRequestUri());
			
			// システム制御ページへ遷移
			$this->isSystemPage = true;
			return false;
		} else {
			return true;
		}
	}
	/**
	 * 言語に依存する情報を取り込む
	 *
	 * @return 							なし
	 */
	function loadLang()
	{
		global $gEnvManager;
		global $gSystemManager;
		
		$lang = $gEnvManager->getCurrentLanguage();
		
		// デフォルト言語とカレント言語が異なる場合のみ実行
		if ($lang != $gEnvManager->getDefaultLanguage()){
			// 指定言語のサイト定義取得
			$gSystemManager->roadSiteDefByLang($lang);
		}
		
		// 現在の言語でヘッダ初期化
		$this->headDescription	= $gSystemManager->getSiteDef(M3_TB_FIELD_SITE_DESCRIPTION);	// HTMLヘッダ「description」に出力する文字列
		$this->headKeywords		= $gSystemManager->getSiteDef(M3_TB_FIELD_SITE_KEYWORDS);		// HTMLヘッダ「keywords」に出力する文字列
		$this->headOthers		= $gSystemManager->getSiteDef(self::SD_HEAD_OTHERS);			// HTMLヘッダに出力するタグ文字列

		// デフォルトのページ情報でヘッダを更新
		if (!empty($this->currentPageInfo)){
			$title		= $this->currentPageInfo['pn_meta_title'];		// 画面タイトル
			$desc		= $this->currentPageInfo['pn_meta_description'];		// ページ要約
			$keyword	= $this->currentPageInfo['pn_meta_keywords'];		// ページキーワード
			$headOthers	= $this->currentPageInfo['pn_head_others'];		// ヘッダその他タグ
			
			if (!empty($title)) $this->setHeadSubTitle($title, '', true);		// デフォルト値として設定
			if (!empty($desc)) $this->setHeadDescription($desc);
			if (!empty($keyword)) $this->setHeadKeywords($keyword);
			if (!empty($headOthers)) $this->setHeadOthers($headOthers);
		}

		// 現在の言語のページ情報でヘッダを更新
		$ret = $this->systemDb->getPageInfo($gEnvManager->getCurrentPageId(), $gEnvManager->getCurrentPageSubId(), $lang, $row);
		if ($ret){
			$title		= $row['pn_meta_title'];		// 画面タイトル
			$desc		= $row['pn_meta_description'];		// ページ要約
			$keyword	= $row['pn_meta_keywords'];		// ページキーワード
			$headOthers = $row['pn_head_others'];		// ヘッダその他タグ
			
			if (!empty($title)) $this->setHeadSubTitle($title, '', true);		// デフォルト値として設定
			if (!empty($desc)) $this->setHeadDescription($desc);
			if (!empty($keyword)) $this->setHeadKeywords($keyword);
			if (!empty($headOthers)) $this->setHeadOthers($headOthers);
			
			// 現在のページ情報を設定
//			$this->currentPageInfo = $row;			// 現在のページのページ情報
		}
	}
	/**
	 * スクリプト追加情報から、管理機能用のJavascriptファイル、CSSを追加する
	 *
	 * @param string $task				指定タスク
	 * @param string $scriptInfo		スクリプト追加情報
	 * @return 							なし
	 */
	function addAdminScript($task, $scriptInfo)
	{
		$itemArray = explode(self::SCRIPT_LIB_SEPARATOR, strtolower(trim($scriptInfo)));// 小文字に変換したものを解析
		for ($i = 0; $i < count($itemArray); $i++){
			$pos = strpos($itemArray[$i], '=');
			if ($pos === false){// 見つからないときは、タスクが指定されないとき
				$libs = trim($itemArray[$i]);
			} else {		// タスク指定のとき
				list($libTask, $libs) = explode('=', trim($itemArray[$i]));
				$libTask = trim($libTask);
				$libs = trim($libs);
				
				if (strEndsWith($libTask, '_')){		// 「task_subtask」形式のタスクのとき
					if (!strStartsWith($task, $libTask)) $libs = '';			// タスクIDの先頭部が異なるときは追加しない
				} else {
					if (empty($libTask) || $libTask != $task) $libs = '';			// タスクが異なるときは追加しない
				}
			}
			if (!empty($libs)){		// // スクリプト、CSSの追加を行うとき
				$libsArray = explode(',', $libs);
				for ($j = 0; $j < count($libsArray); $j++){
					$lib = strtolower(trim($libsArray[$j]));// 小文字に変換
					
					// ライブラリセットを展開
					$setLibArray = ScriptLibInfo::getLibSet($lib);
					$setLibCount = count($setLibArray);
					if ($setLibCount > 0){			// ライブラリセットの場合
						for ($k = 0; $k < $setLibCount; $k++){
							$this->_addAdminScript($setLibArray[$k]);
						}
					} else {
						$this->_addAdminScript($lib);
					}
				}
			}
		}
	}
	/**
	 * ライブラリIDに対応するJavascriptファイル、CSSを追加する
	 *
	 * @param string $lib				ライブラリID
	 * @return 							なし
	 */
	function _addAdminScript($lib)
	{
		// ライブラリが存在しないときは終了
		if (!isset($this->libFiles[$lib])) return;
		
		// 依存ライブラリを取得
//		if (strcmp($lib, ScriptLibInfo::LIB_ELFINDER) == 0 || strcmp($lib, ScriptLibInfo::LIB_JQUERY_TIMEPICKER) == 0){		// elFinder、timepickerを使用する場合
		// jQuery UIライブラリを追加
		$dependentLib = ScriptLibInfo::getDependentLib($lib);
		if (isset($dependentLib)){
			for ($i = 0; $i < count($dependentLib); $i++){
				$addLib = $dependentLib[$i];

				// ライブラリのファイルを追加
				if (isset($this->libFiles[$addLib]['script'])){
					$scriptFiles = $this->libFiles[$addLib]['script'];
					for ($m = 0; $m < count($scriptFiles); $m++){
						$this->addAdminScriptFile($scriptFiles[$m]);		// 通常機能用のスクリプト追加
					}
				}
				// ライブラリの言語ファイルを追加
				if (isset($this->libFiles[$addLib]['script_lang'])){
					$scriptFiles = ScriptLibInfo::getLangScript($addLib);
					for ($m = 0; $m < count($scriptFiles); $m++){
						$this->addAdminScriptFile($scriptFiles[$m]);		// 通常機能用のスクリプト追加
					}
				}
				// ライブラリのCSSファイルを追加
				if (isset($this->libFiles[$addLib]['css'])){
					$cssFiles = $this->libFiles[$addLib]['css'];
					for ($m = 0; $m < count($cssFiles); $m++){
						$this->addAdminCssFile($cssFiles[$m]);		// 通常機能用のCSS追加
					}
				}
			}
			// jQueryUIテーマを追加
			if (!$this->outputTheme){				// jQueryUIテーマ出力を行ったかどうか
				$this->addAdminCssFile($this->getAdminDefaultThemeUrl());		// CSS追加(絶対パス)
				$this->outputTheme = true;
			}
		}

		// Javascript追加
		if (isset($this->libFiles[$lib]['script'])){
			$scriptFiles = $this->libFiles[$lib]['script'];

			// ##### ライブラリ情報を作成する場合 #####
			if (empty($scriptFiles)){
				$generatedLib = ScriptLibInfo::generateLib($lib);			// ライブラリ取得
				if (!empty($generatedLib)) $scriptFiles = $generatedLib['script'];
			}
			
			for ($i = 0; $i < count($scriptFiles); $i++){
				$this->addAdminScriptFile($scriptFiles[$i]);		// 管理機能用のスクリプト追加
			}
		}
		// ライブラリの言語ファイルを追加
		if (isset($this->libFiles[$lib]['script_lang'])){
			$scriptFiles = ScriptLibInfo::getLangScript($lib);
			for ($i = 0; $i < count($scriptFiles); $i++){
				$this->addAdminScriptFile($scriptFiles[$i]);		// 管理機能用のスクリプト追加
			}
		}
		// CSS追加
		if (isset($this->libFiles[$lib]['css'])){
			$cssFiles = $this->libFiles[$lib]['css'];
			for ($i = 0; $i < count($cssFiles); $i++){
				$this->addAdminCssFile($cssFiles[$i]);		// 管理機能用のCSS追加
			}
		}
		// その他
		if (strncmp($lib, 'jquery-ui.', 10) == 0){		// jQuery UIのwidgetsまたはeffectsのとき。jQuery UI Coreはデフォルトで読み込まれている。
			// jQueryUIテーマを追加
			if (!$this->outputTheme){				// jQueryUIテーマ出力を行ったかどうか
				$this->addAdminCssFile($this->getAdminDefaultThemeUrl());		// CSS追加(絶対パス)
				$this->outputTheme = true;
			}
		}
	}
	/**
	 * スクリプト追加情報から、通常機能用のJavascriptファイル、CSSを追加する
	 *
	 * @param string $task				指定タスク
	 * @param string $scriptInfo		スクリプト追加情報
	 * @return 							なし
	 */
	function addScript($task, $scriptInfo)
	{
		$itemArray = explode(self::SCRIPT_LIB_SEPARATOR, strtolower(trim($scriptInfo)));// 小文字に変換したものを解析
		for ($i = 0; $i < count($itemArray); $i++){
			$pos = strpos($itemArray[$i], '=');
			if ($pos === false){// 見つからないときは、タスクが指定されないとき
				$libs = trim($itemArray[$i]);
			} else {		// タスク指定のとき
				list($libTask, $libs) = explode('=', trim($itemArray[$i]));
				$libTask = trim($libTask);
				$libs = trim($libs);
				if (empty($libTask) || $libTask != $task) $libs = '';			// タスクが異なるときは追加しない
			}
			if (!empty($libs)){		// // スクリプト、CSSの追加を行うとき
				$libsArray = explode(',', $libs);
				for ($j = 0; $j < count($libsArray); $j++){
					$lib = strtolower(trim($libsArray[$j]));// 小文字に変換
					
					// jQueryライブラリ等、デフォルトでは追加されないライブラリを追加
					$setLibArray = ScriptLibInfo::getLibSet($lib);// ライブラリセットを展開
					$setLibCount = count($setLibArray);
					if ($setLibCount > 0){			// ライブラリセットの場合
						for ($k = 0; $k < $setLibCount; $k++){
							$this->_addScript($setLibArray[$k]);
						}
					} else {
						$this->_addScript($lib);
					}
				}
			}
		}
	}
	/**
	 * ライブラリIDに対応するJavascriptファイル、CSSを追加する
	 *
	 * @param string $lib				ライブラリID
	 * @return 							なし
	 */
	function _addScript($lib)
	{
		// ライブラリが存在しないときは終了
		if (!isset($this->libFiles[$lib])) return;

		// ライブラリの依存ライブラリファイルを追加
		if (strcmp($lib, 'jquery') == 0){// jQuery本体のとき
			$this->addScriptFile($this->selectedJQueryFilename);		// JQueryスクリプト追加
		} else if (strncmp($lib, 'jquery.', 7) == 0){		// jQueryプラグインのとき
			$this->addScriptFile($this->selectedJQueryFilename);		// JQueryスクリプト追加

			// 依存ライブラリ追加
			$dependentLib = ScriptLibInfo::getDependentLib($lib);
			if (isset($dependentLib)){
				for ($i = 0; $i < count($dependentLib); $i++){
					$addLib = $dependentLib[$i];
			
					// ライブラリのファイルを追加
					if (isset($this->libFiles[$addLib]['script'])){
						$scriptFiles = $this->libFiles[$addLib]['script'];
						for ($m = 0; $m < count($scriptFiles); $m++){
							$this->addScriptFile($scriptFiles[$m]);		// 通常機能用のスクリプト追加
						}
					}
					// ライブラリの言語ファイルを追加
					if (isset($this->libFiles[$addLib]['script_lang'])){
						$scriptFiles = ScriptLibInfo::getLangScript($addLib);
						for ($m = 0; $m < count($scriptFiles); $m++){
							$this->addScriptFile($scriptFiles[$m]);		// 通常機能用のスクリプト追加
						}
					}
					// ライブラリのCSSファイルを追加
					if (isset($this->libFiles[$addLib]['css'])){
						$cssFiles = $this->libFiles[$addLib]['css'];
						for ($m = 0; $m < count($cssFiles); $m++){
							$this->addCssFile($cssFiles[$m]);		// 通常機能用のCSS追加
						}
					}
				}
			}
		} else if (strcmp($lib, ScriptLibInfo::LIB_JQUERY_UI) == 0){	// jQuery UI
			$this->addScriptFile($this->selectedJQueryFilename);		// JQueryスクリプト追加
		//} else if (strcmp($lib, ScriptLibInfo::LIB_JQUERY_UI_PLUS) == 0){	// jQuery UI plus
		//	$this->addScriptFile($this->selectedJQueryFilename);		// JQueryスクリプト追加
		//	$this->addScriptFile($this->selectedJQueryUiFilename);		// jQuery Coreスクリプト追加
		} else if (strncmp($lib, 'jquery-ui.', 10) == 0 ||		// jQuery UIのwidgetsまたはeffectsのとき
			strcmp($lib, ScriptLibInfo::LIB_ELFINDER) == 0 || strcmp($lib, ScriptLibInfo::LIB_JQUERY_TIMEPICKER) == 0){		// elFinder、timepickerを使用する場合

			// 依存ライブラリ追加
			if (strncmp($lib, 'jquery-ui.', 10) == 0){
				$jQueryUiInfo = ScriptLibInfo::getJQueryUiInfo();// ライブラリ情報取得
				$dependentLib = $jQueryUiInfo[$lib];		// 依存ライブラリ取得
			} else {
				$dependentLib = ScriptLibInfo::getDependentLib($lib);
			}
			for ($i = 0; $i < count($dependentLib); $i++){
				$addLib = $dependentLib[$i];
				
				// ライブラリのファイルを追加
				if (isset($this->libFiles[$addLib]['script'])){
					$scriptFiles = $this->libFiles[$addLib]['script'];
					for ($m = 0; $m < count($scriptFiles); $m++){
						$this->addScriptFile($scriptFiles[$m]);		// 通常機能用のスクリプト追加
					}
				}
				// ライブラリの言語ファイルを追加
				if (isset($this->libFiles[$addLib]['script_lang'])){
					$scriptFiles = ScriptLibInfo::getLangScript($addLib);;
					for ($m = 0; $m < count($scriptFiles); $m++){
						$this->addScriptFile($scriptFiles[$m]);		// 通常機能用のスクリプト追加
					}
				}
				// ライブラリのCSSファイルを追加
				if (isset($this->libFiles[$addLib]['css'])){
					$cssFiles = $this->libFiles[$addLib]['css'];
					for ($m = 0; $m < count($cssFiles); $m++){
						$this->addCssFile($cssFiles[$m]);		// 通常機能用のCSS追加
					}
				}
			}
			// jQueryUIテーマを追加
			if (!$this->outputTheme){				// jQueryUIテーマ出力を行ったかどうか
				$this->addCssFile($this->getDefaultThemeUrl());		// 通常機能用のCSS追加
				$this->outputTheme = true;
			}
		}
		
		// ライブラリ自体のファイルを追加
		if (isset($this->libFiles[$lib]['script'])){
			$scriptFiles = $this->libFiles[$lib]['script'];
			
			// ##### ライブラリ情報を作成する場合 #####
			if (empty($scriptFiles)){
				$generatedLib = ScriptLibInfo::generateLib($lib);			// ライブラリ取得
				if (!empty($generatedLib)) $scriptFiles = $generatedLib['script'];
			}
			
			for ($i = 0; $i < count($scriptFiles); $i++){
				$this->addScriptFile($scriptFiles[$i]);		// 通常機能用のスクリプト追加
			}
		}
		// ライブラリの言語ファイルを追加
		if (isset($this->libFiles[$lib]['script_lang'])){
			$scriptFiles = ScriptLibInfo::getLangScript($lib);
			for ($i = 0; $i < count($scriptFiles); $i++){
				$this->addScriptFile($scriptFiles[$i]);		// 通常機能用のスクリプト追加
			}
		}
		// ライブラリのCSSファイルを追加
		if (isset($this->libFiles[$lib]['css'])){
			$cssFiles = $this->libFiles[$lib]['css'];
			for ($i = 0; $i < count($cssFiles); $i++){
				$this->addCssFile($cssFiles[$i]);		// 通常機能用のCSS追加
			}
		}
	}
	/**
	 * 非ログイン時の管理機能用のJavascriptファイル、CSSを追加する
	 *
	 * @param array, string 	$lib		追加ライブラリID
	 * @return 					なし
	 */
	function addPermittedAdminScript($lib)
	{
		if (is_array($lib)){
			for ($j = 0; $j < count($lib); $j++){
				$libId = $lib[$j];
			
				// Javascript追加
				if (isset($this->libFiles[$libId]['script'])){
					$scriptFiles = $this->libFiles[$libId]['script'];
					
					// ##### ライブラリ情報を作成する場合 #####
					if (empty($scriptFiles)){
						$generatedLib = ScriptLibInfo::generateLib($libId);			// ライブラリ取得
						if (!empty($generatedLib)) $scriptFiles = $generatedLib['script'];
					}
			
					for ($i = 0; $i < count($scriptFiles); $i++){
						$this->defaultAdminDirScriptFiles[] = $scriptFiles[$i];		// デフォルトで読み込むスクリプトファイル(管理ディレクトリ用)
					}
				}
				// ライブラリの言語ファイルを追加
				if (isset($this->libFiles[$libId]['script_lang'])){
					$scriptFiles = ScriptLibInfo::getLangScript($libId);
					for ($i = 0; $i < count($scriptFiles); $i++){
						$this->defaultAdminDirScriptFiles[] = $scriptFiles[$i];		// デフォルトで読み込むスクリプトファイル(管理ディレクトリ用)
					}
				}
				// CSS追加
				if (isset($this->libFiles[$libId]['css'])){
					$cssFiles = $this->libFiles[$libId]['css'];
					for ($i = 0; $i < count($cssFiles); $i++){
						$this->defaultAdminDirCssFiles[] = $cssFiles[$i];		// デフォルトで読み込むCSSファイル(管理ディレクトリ用)
					}
				}
			}
		} else {
			$libId = $lib;
			
			// Javascript追加
			if (isset($this->libFiles[$libId]['script'])){
				$scriptFiles = $this->libFiles[$libId]['script'];
				
				// ##### ライブラリ情報を作成する場合 #####
				if (empty($scriptFiles)){
					$generatedLib = ScriptLibInfo::generateLib($libId);			// ライブラリ取得
					if (!empty($generatedLib)) $scriptFiles = $generatedLib['script'];
				}
			
				for ($i = 0; $i < count($scriptFiles); $i++){
					$this->defaultAdminDirScriptFiles[] = $scriptFiles[$i];		// デフォルトで読み込むスクリプトファイル(管理ディレクトリ用)
				}
			}
			// ライブラリの言語ファイルを追加
			if (isset($this->libFiles[$libId]['script_lang'])){
				$scriptFiles = ScriptLibInfo::getLangScript($libId);
				for ($i = 0; $i < count($scriptFiles); $i++){
					$this->defaultAdminDirScriptFiles[] = $scriptFiles[$i];		// デフォルトで読み込むスクリプトファイル(管理ディレクトリ用)
				}
			}
			// CSS追加
			if (isset($this->libFiles[$libId]['css'])){
				$cssFiles = $this->libFiles[$libId]['css'];
				for ($i = 0; $i < count($cssFiles); $i++){
					$this->defaultAdminDirCssFiles[] = $cssFiles[$i];		// デフォルトで読み込むCSSファイル(管理ディレクトリ用)
				}
			}
		}
	}
	/**
	 * ページ作成終了
	 *
	 * ・最終HTML出力
	 * ・セッション情報の保存
	 * ・ウィジェットで生成されたHTTPヘッダを設定する
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param bool $getOutput				出力を取得するかどうか
	 * @return string        				最終出力HTML
	 */
	function endPage($request, $getOutput = false)
	{
		global $gRequestManager;
		global $gInstanceManager;
		global $gEnvManager;
		global $gDispManager;
		global $gAccessManager;
		
		// ページ作成処理を中断するかどうか
		if ($this->isAbort) return '';
		
		$contents = '';
		
		// 実行コマンドを取得
		$cmd = $request->trimValueOf(M3_REQUEST_PARAM_OPERATION_COMMAND);
		
		// 最終HTML(ページ全体で使用するHTML)の出力
		if ($cmd == M3_REQUEST_CMD_CSS){		// CSS生成のとき
			// 外部出力形式でCSS出力
			if (count($this->exportCss) > 0){
				for ($i = 0; $i < count($this->exportCss); $i++){
					$contents .= $this->exportCss[$i] . M3_NL;
				}
			}
			
			// ヘッダ出力
			header('Content-type: text/css');
			
			// 画面情報、ユーザ情報の保存は行わない
			return $contents;
		} else if ($cmd != M3_REQUEST_CMD_DO_WIDGET){		// ウィジェット単体オペレーションのときは出力しない
/*			if ($getOutput){
				$contents = $this->getLastContents($request);
			} else {
				echo $this->getLastContents($request);
			}*/
		}
		
		// セッションへユーザ情報を保存
		$userInfo = $gInstanceManager->getUserInfo();
		$gRequestManager->setSessionValueWithSerialize(M3_SESSION_USER_INFO, $userInfo);
		
		// その他セッション情報保存
		$gRequestManager->_doSessionCloseEventCallback();
		
		// 画面設定保存
		$gDispManager->save();
			
		// キャッシュリミッタは、各リクエスト毎に(アウトプットバッファー が無効な場合は、
		// session_start()がコールされる 前に) session_cache_limiter()をコールする必要がある。
	 	// キャッシュを残す設定
//	 	session_cache_limiter('private');
//	 	session_cache_expire(5);
		if ($this->isRedirect) return '';			// リダイレクトの場合は終了

		// ########## HTTPヘッダ出力処理 ########
		if (headers_sent($filename, $linenum)){		// HTTPヘッダが既に送信されているとき
			echo "$filename の $linenum 行目でヘッダがすでに送信されています。";
		} else {
			// キャッシュを無効にする場合
			header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');// 過去の日付
			header('Cache-Control: no-store, no-cache, must-revalidate');// HTTP/1.1
			header('Cache-Control: post-check=0, pre-check=0', false);
			header('Pragma: no-cache');
	
			// 更新日時
			header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
	
			// 文字コード設定
			header('Content-Type: text/html; charset=UTF-8');			// 2018/4/19 Firefoxで文字化けが起きる場合があるので追加
			
			// Ajax用JSON型データをHTTPヘッダに格納
			$gInstanceManager->getAjaxManager()->header();

			// システム制御画面が設定されている場合はステータスコードを変更
			if (!$this->isRedirect){			// リダイレクトがセットされていない場合
				switch ($this->systemHandleMode){
					case 10:			// サイト非公開(システムメンテナンス)
						//header('HTTP/1.1 503 Service Temporarily Unavailable');
						//header('Status: 503 Service Temporarily Unavailable');
						
						// レスポンスヘッダ設定
						$this->setResponse(503);
						break;
					case 11:			// アクセス禁止のとき
						//header('HTTP/1.1 403 Forbidden');
						//header('Status: 403 Forbidden');
						
						// レスポンスヘッダ設定
						$this->setResponse(403);
						break;
					case 12:			// 存在しないページのとき
						//header("HTTP/1.1 404 Not Found");
						//header("Status: 404 Not Found");
						
						// レスポンスヘッダ設定
						$this->setResponse(404);
						break;
				}
			}
		}
		// ##### 自動ログイン #####
		$gAccessManager->endAutoLogin();
		
		return $contents;
	}
	/**
	 * ページ作成処理中断
	 *
	 * 注意)exitSystem()でシステムを終了させる必要あり
	 *
	 * @return 							なし
	 */
	function abortPage()
	{
		global $gInstanceManager;
		global $gRequestManager;
		
		// HTTPヘッダを削除(PHP 5.3以上で有効)
		if (version_compare(PHP_VERSION, '5.3.0') >= 0) header_remove();

		// exit()等でabortPage()が最後の処理になってしまう可能性があるのでなるべく必要な処理を行う
		//if (ob_get_level() > 0) ob_end_clean();// バッファ内容が残っているときは破棄
		while (ob_get_level()) ob_end_clean();	// バッファ削除方法変更(2009/12/2)
		
		// セッションへユーザ情報を保存
		$userInfo = $gInstanceManager->getUserInfo();
		$gRequestManager->setSessionValueWithSerialize(M3_SESSION_USER_INFO, $userInfo);
		
		// その他セッション情報保存
		$gRequestManager->_doSessionCloseEventCallback();
		
		$this->isAbort = true;					// ページ作成処理を中断するかどうか
	}
	/**
	 * ウィジェット処理中断
	 *
	 * @return 							なし
	 */
	function abortWidget()
	{
		$this->isWidgetAbort = true;					// 各ウィジェット処理を中断するかどうか
	}
	/**
	 * 強制終了を実行
	 *
	 * @return 		なし
	 */
	function exitSystem()
	{
		global $gEnvManager;
		global $gAccessManager;
		
		// DBが使用可能であれば、アクセスログのユーザを登録
		if ($gEnvManager->canUseDb()) $gAccessManager->accessLogUser();
		
		exit();		// システム終了
	}
	/**
	 * Joomla!v1.5タグを読み込んでウィジェット実行
	 *
	 * @param string	$srcBuf			バッファデータ
	 * @param int		$templateVer	テンプレートバージョン(99=レイアウト用テンプレート、0=デフォルト(Joomla!v1.0)、-1=携帯用、1=Joomla!v1.5、2=Joomla!v2.5)
	 * @return string					変換後文字列
	 */
	function launchWidgetByJoomlaTag($srcBuf, $templateVer)
	{
		$replace = array();
		$matches = array();
		$destBuf = $srcBuf;
		
		if (preg_match_all('#<jdoc:include\ type="([^"]+)" (.*)\s*\/>#iU', $srcBuf, $matches)){
			$count = count($matches[1]);
			for ($i = 0; $i < $count; $i++)
			{
				$contents = '';
				$type  = $matches[1][$i];
				$attr = array();
				if (strcasecmp($type, 'head') == 0){		// ヘッダ埋め込みタグの場合
					ob_clean();
					$this->getHeader();
					$contents = ob_get_contents();
				} else if (strcasecmp($type, 'modules') == 0 || strcasecmp($type, 'module') == 0 ||		// 一般ウィジェット用配置ブロックの場合
							strcasecmp($type, 'navmenu') == 0){											// ナビゲーションメニューウィジェット用配置ブロックの場合
					$name = '';			// ポジション名
					$posType = '';		// ポジションのタイプ
					$style = '';		// 表示スタイル
					$params = explode(' ', $matches[2][$i]);
					$paramArray = array();
					for ($j = 0; $j < count($params); $j++){
						list($key, $value) = explode('=', $params[$j]);
						$key = trim($key);
						$value = trim($value, "\"'");
						if (!empty($key)) $paramArray[$key] = $value;
					}
					$value = $paramArray['name'];
					if (isset($value)){
						$name = $value;
						$attr['name'] = $value;
					}
					$value = $paramArray['type'];		// 配置ブロックのタイプを属性に格納。テンプレート内での参照用。
					if (isset($value)){
						$posType = $value;
						$attr['type'] = $value;
					}
					$value = $paramArray['id'];
					if (isset($value)) $attr['id'] = $value;
					
					// スタイルが設定されている場合はオプションスタイルを取得
					$value = $paramArray['style'];
					if (isset($value)){
						$style = $value;
						$attr['style'] = $value;
						
						$optionStyle = $paramArray[$value];		// オプションのスタイル
						if (isset($optionStyle)) $attr[$value] = $optionStyle;
					}
/*					for ($j = 0; $j < count($params); $j++){
						list($key, $value) = explode('=', $params[$j]);
						if (strcasecmp($key, 'name') == 0){
							$name = strtolower(trim($value, "\"'"));
							$attr['name'] = $name;
						} else if (strcasecmp($key, 'style') == 0){
							// スタイルは大文字小文字の区別あり
							$style = trim($value, "\"'");
						} else if (strcasecmp($key, 'artstyle') == 0){		// テンプレート側指定の表示スタイル(Artisteer用)
							$attr['artstyle'] = trim($value, "\"'");
						} else if (strcasecmp($key, 'bootstyle') == 0){		// テンプレート側指定の表示スタイル(Bootstrap用)
							$attr['bootstyle'] = trim($value, "\"'");
						} else if (strcasecmp($key, 'drstyle') == 0){		// テンプレート側指定の表示スタイル(Themer用)
							$attr['drstyle'] = trim($value, "\"'");
						}
					}*/

					if (!empty($name)){		// ポジション名が取得できたとき
						// Joomla!では、テンプレートの「jdoc:include」タグの属性styleが空のときは「none」で処理される
						// Joomla!デフォルトで設定可能なのは「none,table,horz,xhtml,rounded,outline」
						// 配置ブロックがナビゲーション型に固定されていない場合はテンプレートの種類によってナビゲーションかどうかを判断
						if (strcasecmp($type, 'navmenu') == 0){											// メニューウィジェット用配置ブロックの場合
							$style = self::WIDGET_STYLE_NAVMENU;		// デフォルトはナビゲーション型
							$attr['type'] = $type;			// テンプレート内での参照用
						} else {
							switch ($templateVer){
							case 0:			// デフォルトテンプレート(Joomla!v1.0)
							case 1:			// Joomla!v1.5
							case 2:			// Joomla!v2.5
							case 10:		// Bootstrap v3.0
							default:
								if (strcasecmp($name, 'user3') == 0 ||		// ナビゲーションメニュー位置の場合
									strcasecmp($name, 'position-1') == 0 ||		// Joomla!v2.5テンプレート対応
									strcasecmp($posType, 'hmenu') == 0){		// Joomla!v3テンプレート対応
									$style = self::WIDGET_STYLE_NAVMENU;		// デフォルトはナビゲーション型
								}
								break;
							case 99:		// レイアウト用テンプレート
								global $gRequestManager;
								static $templateType;		// レイアウト用テンプレートタイプを一度だけ取得
								static $templateGenerator;
								
								if (!isset($templateType)){
									$templateType = 99;
									
									// レイアウト用テンプレートの場合は対象とするテンプレートの情報を取得して判断する
									$pageId		= $gRequestManager->trimValueOf(M3_REQUEST_PARAM_DEF_PAGE_ID);
									$pageSubId	= $gRequestManager->trimValueOf(M3_REQUEST_PARAM_DEF_PAGE_SUB_ID);
								
									// ページ用個別に設定されたテンプレートがある場合は取得
									$curTemplate = '';
									$line = $this->getPageInfo($pageId, $pageSubId);
									if (!empty($line)) $curTemplate = $line['pn_template_id'];
				
									// 取得できなければデフォルトを取得
									if (empty($curTemplate)){
										if ($pageId == $this->gEnv->getDefaultPageId()){		// 通常サイトのとき
											$curTemplate = $this->gSystem->defaultTemplateId();
										} else if ($pageId == $this->gEnv->getDefaultSmartphonePageId()){		// スマートフォン用サイトのとき
											$curTemplate = $this->gSystem->defaultSmartphoneTemplateId();		// スマートフォン用デフォルトテンプレート
										}
									}
									
									// テンプレートのタイプを取得
									$ret = $this->systemDb->getTemplate($curTemplate, $row);
									if ($ret){
										$templateType = $row['tm_type'];
										$templateGenerator = $row['tm_generator'];
									}
								}
								
								if ($templateType != 99){
									// Themlerテンプレート場合は配置ポジション名が「hmenu」をナビゲーション型とする
									if ($templateGenerator == M3_TEMPLATE_GENERATOR_THEMLER){
										if (strcasecmp($name, 'hmenu') == 0){		// Joomla!v3テンプレート対応
											$style = self::WIDGET_STYLE_NAVMENU;		// デフォルトはナビゲーション型
										}
									} else {
										if (strcasecmp($name, 'user3') == 0 ||		// ナビゲーションメニュー位置の場合
											strcasecmp($name, 'position-1') == 0){		// Joomla!v2.5テンプレート対応
											$style = self::WIDGET_STYLE_NAVMENU;		// デフォルトはナビゲーション型
										}
									}
								}
								break;
							}
						}
						if (empty($style)) $style = 'none';
						
						// ウィジェットの出力を取得
						$contents = $this->getContents($name, $style, $templateVer, $attr);
					}
				} else if (strcasecmp($type, 'component') == 0){	// メインポジションタグの場合
					// スタイルを取得
					$style = '';		// 表示スタイル
					$params = explode(' ', $matches[2][$i]);
					for ($j = 0; $j < count($params); $j++){
						list($key, $value) = explode('=', $params[$j]);
						if (strcasecmp($key, 'style') == 0){
							// スタイルは大文字小文字の区別あり
							$style = trim($value, "\"'");
							break;
						}
					}
					if ($style != 'none') $style = 'xhtml';
					$contents = $this->getContents('main', $style, $templateVer, $attr);
				} else if (strcasecmp($type, 'message') == 0){	// メッセージタグの場合
				}
				$replace[$i] = $contents;
			}
			ob_clean();
			$destBuf = str_replace($matches[0], $replace, $srcBuf);
		}
		return $destBuf;
	}
	/**
	 * 遅延ウィジェット実行
	 *
	 * 遅延実行インデックスのついているウィジェットをインデックス順に実行し、出力バッファデータ内のタグの位置に出力を埋め込む
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param string         $srcBuf		バッファデータ
	 * @return string						変換後文字列
	 */
	function lateLaunchWidget($request, $srcBuf)
	{
		static $render;		// HTML生成オブジェクト
		global $gEnvManager;
		global $gErrorManager;
		global $gDesignManager;
		
		// ページ作成中断またはウィジェット処理中断のときは終了
		if ($this->isAbort || $this->isWidgetAbort) return '';
		
		// ウィジェットヘッダ(Joomla!1.0用)を出力のタイプを取得
		$widgetHeaderType = $this->getTemplateWidgetHeaderType();
		
		// テンプレートタイプ取得
		$templateVer = $gEnvManager->getCurrentTemplateType();
		
		// 遅延実行ウィジェットをインデックス順にソート
		asort($this->lateLaunchWidgetList, SORT_NUMERIC);
		
		// タグを置換
		$destBuf = $srcBuf;
		foreach ($this->lateLaunchWidgetList as $widgetId => $value){
			// 実行パラメータ取得
			$count = count($this->latelaunchWidgetParam);
			for ($i = 0; $i < $count; $i++){
				list($wId, $maxNo, $confId, $preId, $serial, $style, $cssStyle, $title, $shared, $exportCss, $position, $index, $pageDefRec) = $this->latelaunchWidgetParam[$i];
				if ($wId == $widgetId){
					// パラメータ初期化
					$this->lastHeadCss = '';			// 最後に設定したHTMLヘッダにCSS出力する文字列
					$this->lastHeadScript = '';			// 最後に設定したHTMLヘッダにJavascript出力する文字列
					$this->lastHeadString = '';			// 最後に設定したHTMLヘッダに出力する任意文字列
					
					// 現在のウィジェットのポジション
					$this->currentWidgetPosition = $position;			// 現在のウィジェットのポジション
					$this->currentWidgetIndex = $index;			// 現在のウィジェットのポジション番号
	
					// バッファ作成
					ob_start();

					// ウィジェット実行ファイル取得
					$widgetIndexFile = $gEnvManager->getWidgetsPath() . '/' . $widgetId . '/index.php';

					// その他パラメータ取得
					$configId = $confId;		// ウィジェット定義ID
					if ($configId == 0) $configId = '';
					$prefix = $preId;		// サフィックス文字列
			
					// Joomla!1.0テンプレートのときはウィジェットタイトルを出力
					$joomlaTitleVisble = false;
					if ($widgetHeaderType > 0 && empty($style)){			// Joomla!1.0テンプレートのとき
						if (!empty($title)){
							if ($widgetHeaderType == 1){		// PC用ウィジェットヘッダ出力
								echo '<table ' . self::JOOMLA10_DEFAULT_WIDGET_MENU_PARAM . '>' . M3_NL;
								echo '<tr><th>' . $title . '</th></tr>' . M3_NL;
								echo '<tr><td>' . M3_NL;
								$joomlaTitleVisble = true;
							} else if ($widgetHeaderType == 2){			// 携帯用ウィジェットヘッダ出力
								echo '<div>' . $title . '</div>' . M3_NL;
								$joomlaTitleVisble = true;
							}
						}
					}
					// ウィジェットの外枠タグを設定
					//echo '<div class="' . self::WIDGET_OUTER_CLASS_WIDGET_TAG . $widgetId . '">' . M3_NL;
					// ウィジェット親のCSS定義があるときは、タグを追加
					if (!empty($cssStyle)) echo '<div style="' . $cssStyle . '">' . M3_NL;
					
					// ウィジェットの前出力
					echo $gDesignManager->getAdditionalWidgetOutput(true);
				
					// 作業中のウィジェットIDを設定
					$gEnvManager->setCurrentWidgetId($widgetId);

					// ウィジェット定義IDを設定
					$gEnvManager->setCurrentWidgetConfigId($configId);
					
					// ページ定義のシリアル番号を設定
					$gEnvManager->setCurrentPageDefSerial($serial);
		
					// ページ定義レコードを設定
					$gEnvManager->setCurrentPageDefRec($pageDefRec);
				
					// パラメータを設定
					$gEnvManager->setCurrentWidgetPrefix($prefix);		// プレフィックス文字列
		
					// ウィジェットのタイトルを設定
					$gEnvManager->setCurrentWidgetTitle('');
					
					// ウィジェットのスタイルを設定
					$gEnvManager->setCurrentWidgetStyle($style);
				
					// ウィジェットのグローバル属性状況を設定
					$gEnvManager->setIsCurrentWidgetShared($shared);
				
					// 実行ログを残す
				$this->systemDb->writeWidgetLog($widgetId, 0/*ページ実行*/);
					
					// ウィジェットを実行
					// ウィジェットの呼び出しは、複数回存在する可能性があるのでrequire_once()で呼び出さない
					$msg = 'widget-start(' . $widgetId . ')';
					$gErrorManager->writeDebug(__METHOD__, $msg);		// 時間計測用
					require($widgetIndexFile);
					$msg = 'widget-end(' . $widgetId . ')';
					$gErrorManager->writeDebug(__METHOD__, $msg);		// 時間計測用

					// 作業中のウィジェットIDを解除
					$gEnvManager->setCurrentWidgetId('');
		
					// ウィジェット定義IDを解除
					$gEnvManager->setCurrentWidgetConfigId('');
					
					// ページ定義のシリアル番号を解除
					$gEnvManager->setCurrentPageDefSerial(0);
		
					// ページ定義レコードを解除
					$gEnvManager->setCurrentPageDefRec();
					
					// パラメータを解除
					$gEnvManager->setCurrentWidgetPrefix('');				// プレフィックス文字列
					
					// ウィジェットのスタイルを解除
					$gEnvManager->setCurrentWidgetStyle('');
					
					// ウィジェットのグローバル属性状況を解除
					$gEnvManager->setIsCurrentWidgetShared(false);
					
					// ウィジェットのタイトルを取得
					$newTitle = $gEnvManager->getCurrentWidgetTitle();

					// ウィジェットの後出力
					echo $gDesignManager->getAdditionalWidgetOutput(false);
				
					// ウィジェット親のCSS定義があるときは、タグを追加
					if (!empty($cssStyle)) echo '</div>' . M3_NL;
					// ウィジェットの外枠タグを設定
					//echo '</div>' . M3_NL;
					
					// Joomla!1.0テンプレートのときはタイトルを出力
					if ($joomlaTitleVisble && $widgetHeaderType == 1){		// PC用ウィジェットヘッダ出力
						echo '</td></tr>' . M3_NL;
						echo '</table>' . M3_NL;
					}
					
					// 現在のバッファ内容を取得し、バッファを破棄
					$srcContents = ob_get_contents();
					ob_end_clean();
					
					// ### テンプレート側のウィジェット生成処理使える場合はウィジェットの出力と入れ替える ###
					// ### 先のrequire($widgetIndexFile)で必要なデータは作成されている                    ###
					if ($templateVer >= 1 && $templateVer < 100){		// Joomla!v1.5,v2.5テンプレートのとき
						$widgetType = $pageDefRec['wd_type'];		// ウィジェットタイプ
						if ($widgetType == 'breadcrumb'){		// パンくずリスト
							// 描画オブジェクト作成
							if (!isset($render)) $render = new JRender();

							$templateContents = $render->getBreadcrumbContents('', '', '', array(), array(), $pageDefRec, $templateVer);			// テンプレートバージョンのみ必要
							if (!empty($templateContents)) $srcContents = $templateContents;
						}
					}

					// ウィジェットの出力を取得
					$tag = self::WIDGET_ID_TAG_START . $widgetId . self::WIDGET_ID_SEPARATOR . $maxNo . self::WIDGET_ID_TAG_END;
					$destBuf = str_replace($tag, $srcContents, $destBuf);
					
					// タイトルの出力
					if (!empty($newTitle)) $title = $newTitle;
					$tag = self::WIDGET_ID_TITLE_TAG_START . $widgetId . self::WIDGET_ID_SEPARATOR . $maxNo . self::WIDGET_ID_TITLE_TAG_END;
					$destBuf = str_replace($tag, $title, $destBuf);
					
					// ##### 外部出力用のCSSがある場合は追加 #####
					if (!empty($exportCss)){
						// ウィジェットのタグIDを変換
						$widgetTag = self::WIDGET_TAG_HEAD . $position . '_' . $index;				// ウィジェット識別用ユニークタグ
						$exportCss = str_replace(M3_TAG_START . M3_TAG_MACRO_WIDGET_CSS_ID . M3_TAG_END, $widgetTag, $exportCss);
						$this->addExportCss($exportCss);
					}
				}
			}
		}
		
		// ##### HTMLヘッダ出力処理 #####
		$destBuf = $this->replaceHead($destBuf);
		
		return $destBuf;
	}
	/**
	 * ジョブ実行
	 *
	 * @param string $jobType		ジョブタイプ
	 * @return						なし
	 */
	function launchJob($jobType)
	{
		global $gEnvManager;
		
		// ジョブタイプから実行するウィジェットIDを取得
		$widgetId = $this->getWidgetIdByJobType($jobType);
		if (empty($widgetId)) return;

		// 作業中のウィジェットIDを設定
		$gEnvManager->setCurrentWidgetId($widgetId);
			
		// 指定のウィジェットを実行
		$widgetIndexFile = $gEnvManager->getWidgetsPath() . '/' . $widgetId . '/admin/job.php';
			
		if (file_exists($widgetIndexFile)){
//			// 実行のログを残す
//			$this->_db->writeWidgetLog($widgetId, 1/*単体実行*/, $cmd);

			require_once($widgetIndexFile);
		} else {
			echo 'file not found: ' . $widgetIndexFile;
		}
				
		// 作業中のウィジェットIDを解除
		$gEnvManager->setCurrentWidgetId();
	}
	/**
	 * 非同期処理を実行
	 *
	 * @return						なし
	 */
	function launchJobs()
	{
		// 日次処理を実行するか確認(自動処理フラグで確認?)
		if (!$this->gSystem->getSystemConfig(self::CF_DAILY_JOB)) return;
		
		// ジョブ実行可能な時間帯を確認
		$currentHour = intval(date('G'));
		
		// ### 日次処理を実行時間帯の場合 ###
		$jobHour = intval($this->gSystem->getSystemConfig(self::CF_DAILY_JOB_HOUR));
		if ($currentHour == $jobHour){
		
			// ジョブの実行を確認
			$jobDt = $this->gSystem->getSystemConfig(self::CF_DAILY_JOB_DT);
			//if (!empty($jobDt) && strtotime(date('Y/m/d', strtotime($jobDt))) >= strtotime(date('Y/m/d'))) return;	// 日付のみで比較
			if (empty($jobDt) || strtotime(date('Y/m/d', strtotime($jobDt))) < strtotime(date('Y/m/d'))){	// 日付のみで比較
		
				// ### ２重起動防止のためジョブの実行を先に登録する ###
				$now = date("Y/m/d H:i:s");	// 現在日時
				$this->systemDb->updateSystemConfig(self::CF_DAILY_JOB_DT, $now);
		
				// マルチドメイン対応のためにホスト名を取得
				$parsedUrl = parse_url($this->gEnv->getRootUrl());
				$host = $parsedUrl['host'];
				//$url = $parsedUrl['scheme'] . '://' . $parsedUrl['host'];
		
				// ジョブを実行
				//$fp = fsockopen('ssl://127.0.0.1', 443, $errNo, $errStr, 30);	// SSLの場合?
				$fp = fsockopen('127.0.0.1', 80, $errNo, $errStr, 30);
				if (!$fp) {
					// 実行失敗の場合はログを残す
					$this->gOpeLog->writeError(__METHOD__, '日次処理実行に失敗しました。(要因: ' . $errStr . '(' . $errNo . '))', 1100);
				} else {
					$jobUrl = $this->gEnv->getServerConnectorUrl(true/*相対URL*/) . '?task=dailyjob';
					$out = "GET $jobUrl HTTP/1.1\r\n";
					$out .= "Host: $host\r\n";
				    $out .= "Connection: Close\r\n\r\n";
				    fwrite($fp, $out);
				    fclose($fp);
				}
			
				// ### 日次処理、月次処理は同時には実行しない ###
				return;
			}
		}
		
		// ### 月次処理の実行時間帯の場合 ###
		/*
		$jobHour = intval($this->gSystem->getSystemConfig(self::CF_MONTHLY_JOB_HOUR));
		if ($currentHour == $jobHour){
		
			// ジョブの実行を確認
			$jobDt = $this->gSystem->getSystemConfig(self::CF_MONTHLY_JOB_DT);
			if (empty($jobDt) || strtotime(date('Y/m', strtotime($jobDt))) < strtotime(date('Y/m'))){	// 年月のみで比較
		
				// ### ２重起動防止のためジョブの実行を先に登録する ###
				$now = date("Y/m/d H:i:s");	// 現在日時
				$this->systemDb->updateSystemConfig(self::CF_MONTHLY_JOB_DT, $now);
		
				// マルチドメイン対応のためにホスト名を取得
				$parsedUrl = parse_url($this->gEnv->getRootUrl());
				$host = $parsedUrl['host'];
		
				// ジョブを実行
				$fp = fsockopen('127.0.0.1', 80, $errNo, $errStr, 30);
				if (!$fp) {
					// 実行失敗の場合はログを残す
					$this->gOpeLog->writeError(__METHOD__, '月次処理実行に失敗しました。(要因: ' . $errStr . '(' . $errNo . '))', 1100);
				} else {
					$jobUrl = $this->gEnv->getServerConnectorUrl(true) . '?task=monthlyjob';
					$out = "GET $jobUrl HTTP/1.1\r\n";
					$out .= "Host: $host\r\n";
				    $out .= "Connection: Close\r\n\r\n";
				    fwrite($fp, $out);
				    fclose($fp);
				}
			}
		}*/
	}
	/**
	 * ヘッダ部マクロ変換処理
	 *
	 * @param string         $srcBuf		変換元
	 * @return string						変換後文字列
	 */
	function replaceHead($srcBuf)
	{
		$destBuf = $srcBuf;

		// ##### ヘッダ部分の置換 #####
		if ($this->outputHead){				// HTMLヘッダ出力を行っているとき
			$replaceStr = '';
			
			// HTMLヘッダに最初に出力するタグ文字列がある場合は追加
			if (!empty($this->headFirstTag)) $replaceStr .= $this->headFirstTag;
			
			// METAタグcharset等のHTML文書の標準のヘッダタグを出力
			if ($this->outputByStandardHead) $replaceStr .= $this->getStandardHead();
		
			// タグ変換用文字列の取得
			$replaceStr .= $this->getHeaderOutput();
			
			// HTMLヘッダのデータ埋め込み
			$destBuf = str_replace(self::HEAD_TAGS, $replaceStr, $destBuf);
		}
		$this->replaceHeadDone = true;			// ヘッダマクロ変換処理が完了したかどうか
		return $destBuf;
	}
	/**
	 * ヘッダ部マクロ変換を有効にする
	 *
	 * @return					なし
	 */
	function enableReplaceHead()
	{
		$this->outputHead = true;
	}
	/**
	 * ウィジェット検索モードの場合のページサブIDの設定
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 */
	function redirectToUpdatedPageSubId($request)
	{
		global $gEnvManager;
		
		// 現在設定されているページIDを取得
		$pageId		= $gEnvManager->getCurrentPageId();
		$pageSubId	= $gEnvManager->getCurrentPageSubId();
		
		// 送信元のウィジェットIDを取得
		$fromWidgetId = $request->trimValueOf(M3_REQUEST_PARAM_FROM);
		
		// 対象のウィジェットIDを取得
		$targetWidgetId = $request->trimValueOf(M3_REQUEST_PARAM_WIDGET_ID);
		
		// 対象のウィジェットのページサブIDを取得
		$ret = $this->systemDb->getSubPageId($targetWidgetId, $pageId, $rows);
		if ($ret){// データが存在する
			if (empty($rows[0]['pd_sub_id'])){		// 共通ウィジェットのときは、送信元にあわせる
				$ret = $this->systemDb->getSubPageId($fromWidgetId, $pageId, $rows2);
				if ($ret){// データが存在する
					if (empty($rows2[0]['pd_sub_id'])){		// 送信元が共通ウィジェットのときは、既に設定されているページサブIDを使用
					} else {
						$gEnvManager->setCurrentPageSubId($rows2[0]['pd_sub_id']);
					}
				}
			} else {
				// 送信元があるか順にチェック
				for ($i = 0; $i < count($rows); $i++){
					$ret = $this->systemDb->isExistsWidgetOnPage($pageId, $rows[$i]['pd_sub_id'], $fromWidgetId);
					if ($ret){	
						break;
					}
				}
				if ($i == count($rows)){		// 送信元が見つからない場合は1番目のページサブIDを使用
					$gEnvManager->setCurrentPageSubId($rows[0]['pd_sub_id']);
				} else {
					$gEnvManager->setCurrentPageSubId($rows[$i]['pd_sub_id']);// 存在するときは見つかったページサブIDで更新
				}
			}
		} else {		// 対象のウィジェットが見つからない場合は、互換ウィジェットを探す
			$widgetId = $this->systemDb->getCompatibleWidgetId($targetWidgetId);
			if (!empty($widgetId)){
				$targetWidgetId = $widgetId;
				
				// 対象のウィジェットのページサブIDを取得
				$ret = $this->systemDb->getSubPageId($targetWidgetId, $pageId, $rows);
				if ($ret){// データが存在する
					if (empty($rows[0]['pd_sub_id'])){		// 共通ウィジェットのときは、送信元にあわせる
						$ret = $this->systemDb->getSubPageId($fromWidgetId, $pageId, $rows2);
						if ($ret){// データが存在する
							if (empty($rows2[0]['pd_sub_id'])){		// 送信元が共通ウィジェットのときは、既に設定されているページサブIDを使用
							} else {
								$gEnvManager->setCurrentPageSubId($rows2[0]['pd_sub_id']);
							}
						}
					} else {
						// 送信元があるか順にチェック
						for ($i = 0; $i < count($rows); $i++){
							$ret = $this->systemDb->isExistsWidgetOnPage($pageId, $rows[$i]['pd_sub_id'], $fromWidgetId);
							if ($ret){	
								break;
							}
						}
						if ($i == count($rows)){		// 送信元が見つからない場合は1番目のページサブIDを使用
							$gEnvManager->setCurrentPageSubId($rows[0]['pd_sub_id']);
						} else {
							$gEnvManager->setCurrentPageSubId($rows[$i]['pd_sub_id']);// 存在するときは見つかったページサブIDで更新
						}
					}
				}
			}
		}
		// ページサブIDが見つからないときは、既に設定されている値を使用
		// 既に設定されている値は、URL「sub」パラメータで指定されている値か
		// 設定されていない場合はデフォルトのサブページID
		// ********** 指定ページへリダイレクト ***********
		// 実行パラメータ取得
		$todo = $request->trimValueOf(M3_REQUEST_PARAM_OPERATION_TODO);
		$todo = str_replace(M3_TODO_SEPARATOR, '&', $todo);		// セパレータを変換
//		$redirectUrl = '?' . M3_REQUEST_PARAM_PAGE_SUB_ID . '=' . $gEnvManager->getCurrentPageSubId();
		$redirectUrl = $gEnvManager->createPageUrl() . '?' . M3_REQUEST_PARAM_PAGE_SUB_ID . '=' . $gEnvManager->getCurrentPageSubId();
		if (!empty($todo)) $redirectUrl .= '&' . $todo;
		$this->redirect($redirectUrl);
	}
	/**
	 * 最終HTML出力処理
	 *
	 * テンプレートの出力が完了した後、HTMLとして出力する最後の出力を行う
	 * 追加するHTMLは主にウィンドウ制御用のスクリプト
	 *
	 * @return string        				最終HTML
	 */
	function getLastContents()
	{
		global $gEnvManager;
		global $gRequestManager;
		
		$contents = '';
		$initScript = '';		// 初期化用スクリプト
		$pageId		= $gEnvManager->getCurrentPageId();
		$pageSubId	= $gEnvManager->getCurrentPageSubId();
		$cmd = $gRequestManager->trimValueOf(M3_REQUEST_PARAM_OPERATION_COMMAND);
		
		if (!$gEnvManager->isSystemManageUser()) return '';		// システム運用権限がない場合は終了
		
		// 処理を行わない場合は終了
		if ($cmd == M3_REQUEST_CMD_RSS ||						// RSS配信のときは終了
			$cmd == M3_REQUEST_CMD_DO_WIDGET) return '';		// ウィジェット単体オペレーションのときは出力しない
		
		if ($gEnvManager->isAdminDirAccess()){		// 管理画面へのアクセスのとき
			// ウィジェットレイアウト用カーソル
			if ($cmd == M3_REQUEST_CMD_SHOW_POSITION_WITH_WIDGET){		// ウィジェット付きポジション表示
/*				// テンプレート上のポジション名
				if (count($this->viewPosId) > 0){
					$posArrayStr = '[';
					for ($i = 0; $i < count($this->viewPosId); $i++){
						$posArrayStr .= '\'#' . $this->viewPosId[$i] . '\'';
						if ($i < count($this->viewPosId) - 1) $posArrayStr .= ',';
					}
					$posArrayStr .= ']';
					$contents .= 'var M3_POSITIONS=' . $posArrayStr . ';' . M3_NL;
				}
				// 画面定義のリビジョン番号
				$contents .= 'var M3_REVISION=' . $this->pageDefRev . ';' . M3_NL;*/
		
				// 配置済みのウィジェットを更新またはウィジェット一覧を再取得する関数を追加
				// serialが-1の場合ウィジェット一覧を更新。1以上の場合は配置済みのウィジェットのページ定義シリアル番号を指定。
				$contents .= 'function m3UpdateByConfig(serial){' . M3_NL;
				$contents .= M3_INDENT_SPACE . 'window.m3.m3UpdateByConfig(serial);' . M3_NL;
				$contents .= '}' . M3_NL;
			} else if ($cmd == M3_REQUEST_CMD_CONFIG_WIDGET){			// ウィジェット設定画面
			} else {		// ダッシュボード画面、メイン管理画面
				// 画面更新用関数追加
				$contents .= 'function m3UpdateByConfig(serial){' . M3_NL;
				$contents .= M3_INDENT_SPACE . 'var href = window.location.href.split("#");' . M3_NL;
				$contents .= M3_INDENT_SPACE . 'window.location.href = href[0];' . M3_NL;
				$contents .= '}' . M3_NL;
			}
			
			// ウィジェット単体実行以外のときの処理
			if (!$this->showWidget){
				if ($this->updateParentWindow){			// 管理画面からの親画面の更新
					$initScript .= M3_INDENT_SPACE . 'm3UpdateParentWindowByConfig(' . $this->updateDefSerial . ');' . M3_NL;// 更新する項目のページ定義シリアル番号
				}
			}
		} else {		// 通常画面のとき
			// 画面更新用関数追加
			$contents .= 'function m3UpdateByConfig(serial){' . M3_NL;
			$contents .= M3_INDENT_SPACE . 'var href = window.location.href.split("#");' . M3_NL;
			$contents .= M3_INDENT_SPACE . 'window.location.href = href[0];' . M3_NL;
			$contents .= '}' . M3_NL;
		}

		$destContents = '';
		if (!empty($contents) || !empty($initScript)){
//			$destContents .= '<script type="text/javascript">' . M3_NL;
//			$destContents .= '//<![CDATA[' . M3_NL;
			$destContents .= $contents;
			if (!empty($initScript)){		// 初期化用スクリプト
				$destContents .= '$(function(){' . M3_NL;
				$destContents .= $initScript;
				$destContents .= '});' . M3_NL;
			}
//			$destContents .= '//]]>' . M3_NL;
//			$destContents .= '</script>' . M3_NL;
		}
		return $destContents;
	}
	/**
	 * オプションHTML出力処理
	 *
	 * テンプレートの出力が完了した後、HTMLとして出力する最後の出力を行う
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @return string        				最終HTML
	 */
	function getOptionContents($request)
	{
		global $gEnvManager;
		
		$contents = '';
		
		// ページ作成中断のときは終了
		if ($this->isAbort) return '';
		
		// AJAX用のレスポンスボディデータのときは終了
		if ($this->outputAjaxResponseBody) return '';
		
		// ウィジェット処理中断のとき
		// AJAXを送信する場合は空文字列では送信できないので、ダミーデータを返す
		if ($this->isWidgetAbort) $contents .= 'NO DATA' . M3_NL;
		
		// Magic3出力コメント
		if ($this->outputByHtml){		// HTML出力のとき
			$contents .= '<!-- created by ' . M3_SYSTEM_NAME . ' v' . M3_SYSTEM_VERSION . ' - http://www.magic3.org -->' . M3_NL;
			$contents .= '<!-- convert time: ' . sprintf('%01.03f', microtime(true) - M3_MTIME) . ' -->' . M3_NL;
		}
		return $contents;
	}
	/**
	 * Widget単体起動用のHTMLのヘッダ部(headタグ)出力
	 *
	 * startWidget(),endWidget()は、以下のコマンドを処理する
	 *  ・M3_REQUEST_CMD_SHOW_WIDGET(ウィジェットの単体表示)
	 *  ・M3_REQUEST_CMD_CONFIG_WIDGET(ウィジェット設定画面)
	 *  ・M3_REQUEST_CMD_CONFIG_TEMPLATE(テンプレート設定画面)
	 *  ・M3_REQUEST_CMD_DO_WIDGET(ウィジェット単体実行)
	 * Widgetの出力方法は、以下のパターンがある
	 *  ・HTMLヘッダ付加 - Widget単体で画面出力するためにHTMLヘッダを付加するパターン
	 *  ・HTMLヘッダなし - Wiget単体のタグ出力のみのパターン
	 *
	 * @param string $cmd		起動コマンド
	 */
	function startWidget($cmd)
	{
		global $gEnvManager;
		global $gRequestManager;

		// ウィジェット単体表示のときのみ出力
		if (!$this->showWidget) return;

		// パラメータ取得
		$openBy = $gRequestManager->trimValueOf(M3_REQUEST_PARAM_OPEN_BY);		// ウィンドウオープンタイプ
		$task = $gRequestManager->trimValueOf(M3_REQUEST_PARAM_OPERATION_TASK);

		$tempVer = $gEnvManager->getCurrentTemplateType();		// テンプレートタイプを取得(0=デフォルト(Joomla!v1.0),1=Joomla!v1.5,2=Joomla!v2.5)
		if (intval($tempVer) >= 2) $this->isHtml5 = true;		// HTML5で出力するかどうか				
		
		// DOCTYPEの設定
		if ($this->isHtml5){
			echo '<!DOCTYPE html>' . M3_NL;
			echo '<html dir="ltr" lang="' . $gEnvManager->getCurrentLanguage() . '">' . M3_NL;
			echo '<head>' . M3_NL;
		} else {
			echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">' . M3_NL;
			echo '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="' . $gEnvManager->getCurrentLanguage() . '" lang="' . $gEnvManager->getCurrentLanguage() . '">' . M3_NL;
			echo '<head>' . M3_NL;
		}

		// HTMLのヘッダ部(headタグ内)出力
		$this->getHeader();

		// ##### 作成対象がウィジェットの場合とテンプレートの場合に分ける #####
		if ($cmd == M3_REQUEST_CMD_CONFIG_TEMPLATE){			// テンプレート設定画面の場合
			$templateId = $gRequestManager->trimValueOf(M3_REQUEST_PARAM_TEMPLATE_ID);// テンプレートID
		} else {
			// 現在のウィジェットを取得
			$widgetId = $gEnvManager->getCurrentWidgetId();		// カレントのウィジェットID
		}
		
		// URLを作成
		if ($gEnvManager->getUseSslAdmin()){
			$rootUrl = $gEnvManager->getSslRootUrl();
			$templatesUrl = $gEnvManager->getSslTemplatesUrl();	// テンプレート読み込み用パス
			$widgetsUrl = $gEnvManager->getSslWidgetsUrl();		// ウィジェット格納パス
		} else {
			$rootUrl = $gEnvManager->getRootUrl();
			$templatesUrl = $gEnvManager->getTemplatesUrl();	// テンプレート読み込み用パス
			$widgetsUrl = $gEnvManager->getWidgetsUrl();		// ウィジェット格納パス
		}

		// ##### テンプレートのCSSの読み込み #####
		// テンプレートは管理用テンプレートに固定されている
		//if ($cmd == M3_REQUEST_CMD_CONFIG_WIDGET ||		// ウィジェット設定のとき
		if ($cmd == M3_REQUEST_CMD_CONFIG_WIDGET || $cmd == M3_REQUEST_CMD_CONFIG_TEMPLATE ||						// ウィジェット設定画面またはテンプレート設定画面のとき
			($cmd == M3_REQUEST_CMD_DO_WIDGET && !empty($openBy) && $gEnvManager->isContentEditableUser())){	// ウィジェット単体実行でウィンドウを持つ場合の追加スクリプト
			$curTemplateUrl = $templatesUrl . '/' . $gEnvManager->getCurrentTemplateId();
//			if ($this->isHtml5){
				echo '<link rel="stylesheet" href="' . $curTemplateUrl . '/css/default.css" media="screen">' . M3_NL;
//				echo '<link rel="stylesheet" href="' . $curTemplateUrl . '/css/widget.css" media="screen">' . M3_NL;		// ウィジェット設定画面用CSS(未使用?)
//			} else {
//				echo '<link href="' . $curTemplateUrl . '/css/default.css" rel="stylesheet" type="text/css" />' . M3_NL;
//			}
		}
		// ウィジェット情報取得
		$ret = $this->systemDb->getWidgetInfo($widgetId, $row);

		// ##### 共通ライブラリ読み込み設定 #####
		if ($cmd == M3_REQUEST_CMD_DO_WIDGET){		// ウィジェット単体実行のとき
			$scritLib = trim($row['wd_add_script_lib']);
			if (!empty($scritLib)) $this->addScript($task, $scritLib);
		}
				
		// CSS読み込みが指定されていて、ディレクトリがあるときはディレクトリ内読み込み
		if ($row['wd_read_css']){
			$searchPath = $gEnvManager->getWidgetsPath() . '/' . $widgetId . '/' . M3_DIR_NAME_CSS;
			if (is_dir($searchPath)){
				$dir = dir($searchPath);
				while (($file = $dir->read()) !== false){
					$filePath = $searchPath . '/' . $file;
					if ($file != '.' && $file != '..' && is_file($filePath)
						&& strncmp($file, '_', 1) != 0){		// 「_」で始まる名前のファイルは読み込まない
						
						// CSSへのURLを作成
						$cssURL = $widgetsUrl . '/' . $widgetId . '/' . M3_DIR_NAME_CSS . '/' . $file;
						echo '<link rel="stylesheet" type="text/css" href="' . $cssURL . '" />' . M3_NL;
					}
				}
				$dir->close();
			}
		}
		
		// スクリプト読み込みが指定されていて、ディレクトリがあるときはディレクトリ内読み込み
		if ($row['wd_read_scripts']){
			$searchPath = $gEnvManager->getWidgetsPath() . '/' . $widgetId . '/' . M3_DIR_NAME_SCRIPTS;
			if (is_dir($searchPath)){
				$dir = dir($searchPath);
				while (($file = $dir->read()) !== false){
					$filePath = $searchPath . '/' . $file;
					if ($file != '.' && $file != '..' && is_file($filePath)
						&& strncmp($file, '_', 1) != 0){		// 「_」で始まる名前のファイルは読み込まない
						
						// スクリプトへのURLを作成
						$scriptURL = $widgetsUrl . '/' . $widgetId . '/' . M3_DIR_NAME_SCRIPTS . '/' . $file;
						
						// スクリプトをキャッシュ保存しない場合は、パラメータを付加
						if (!$this->hasScriptCache) $scriptURL .= $this->getCacheParam();
						echo '<script type="text/javascript" src="' . convertUrlToHtmlEntity($scriptURL) . '"></script>' . M3_NL;
					}
				}
				$dir->close();
			}
		}
		// ##### スクリプト用出力用タグを埋め込む #####
		// ウィジェット設定画面用メニューバーの作成
		if ($cmd == M3_REQUEST_CMD_CONFIG_WIDGET || $cmd == M3_REQUEST_CMD_CONFIG_TEMPLATE ||						// ウィジェット設定画面またはテンプレート設定画面のとき
			($cmd == M3_REQUEST_CMD_DO_WIDGET && $this->isEditMode)){	// ウィジェット設定画面またはフロント画面編集モードのとき
			echo self::MENUBAR_SCRIPT_TAGS;			// メニューバー出力用タグ
		}
		
		// ##### 作成対象がウィジェットの場合とテンプレートの場合に分ける #####
		// ウィジェットのタイトルを設定
		if ($cmd == M3_REQUEST_CMD_CONFIG_TEMPLATE){			// テンプレート設定画面の場合
			$title = self::WIDGET_TITLE_START . 'テンプレート' . self::WIDGET_TITLE_END . ' ' . $templateId;
			$gEnvManager->setCurrentWidgetTitle($title);
			echo '<title>' . htmlspecialchars($title) . '</title>' . M3_NL;
		} else {
			$title = $row['wd_name'];
			if (empty($title)) $title = $row['wd_id'];
			$gEnvManager->setCurrentWidgetTitle($title);
			echo '<title>' . self::WIDGET_TITLE_START . htmlspecialchars($title) . self::WIDGET_TITLE_END . '</title>' . M3_NL;
		}
		echo '</head>' . M3_NL;
		
		// タブでウィンドウを開く場合は背景を透過モードにする
		if ($this->isTransparentMode){
			echo '<body style="background-color:transparent;">' . M3_NL;
		} else {
			echo '<body>' . M3_NL;
		}
		// ウィジェット設定画面用メニューバーの作成
		if ($cmd == M3_REQUEST_CMD_CONFIG_WIDGET || $cmd == M3_REQUEST_CMD_CONFIG_TEMPLATE ||						// ウィジェット設定画面またはテンプレート設定画面のとき
			($cmd == M3_REQUEST_CMD_DO_WIDGET && $this->isEditMode)){	// ウィジェット設定画面またはフロント画面編集モードのとき
			// ウィジェット情報を設定
			$desc = $row['wd_description'];		// 説明
			$gEnvManager->setCurrentWidgetParams('desc', $desc);
			
			echo self::MENUBAR_TAGS;			// メニューバー出力用タグ
		}
		// Bootstrap用のタグ出力
		if ($this->useBootstrap) echo '<div class="container">' . M3_NL;
		
		// 別ウィンドウで表示のときは、「閉じる」ボタンを表示
		if ($cmd == M3_REQUEST_CMD_SHOW_WIDGET ||		// ウィジェットの単体表示のとき
			$cmd == M3_REQUEST_CMD_CONFIG_WIDGET || $cmd == M3_REQUEST_CMD_CONFIG_TEMPLATE ||						// ウィジェット設定画面またはテンプレート設定画面のとき
			($cmd == M3_REQUEST_CMD_DO_WIDGET && $this->isEditMode)){		// ウィジェット単体実行でフロント画面編集モードのとき

//			if ($this->isEditMode){// フロント画面編集モードのとき
				if (!empty($openBy)){
					// サーバ指定されている場合はサーバ情報を取得
					$serverName = '';
					$server = $gRequestManager->trimValueOf(M3_REQUEST_PARAM_SERVER);
					if (!empty($server)){
						// 設定データを取得
						$ret = $this->systemDb->getServerById($server, $row);
						if ($ret){
							$serverName = 'サーバ名：' . $row['ts_name'];// サーバ名
							echo '<div align="left" style="float:left;padding-left:30px;"><label>' . convertToHtmlEntity($serverName) . '</label></div>';
						}
					}
					// タブ、インナーフレーム、ダイアログ表示以外のときは「閉じる」ボタンを表示
					if ($openBy != 'tabs' && $openBy != 'iframe' && $openBy != 'dialog'){		// 以外
						if ($openBy == 'logout'){
							$titleStr = 'ログアウト';
							//echo '<div class="m3configclose"><a href="#" onclick="location.href=\'?cmd=logout\';" data-placement="left" data-container="body" title="' . $titleStr . '" rel="m3help"><img src="' . $rootUrl . self::CLOSE_ICON_FILE . '" alt="' . $titleStr . '" /></a></div>' . M3_NL;
							echo '<div class="m3configclose"><a href="#" onclick="location.href=\'?cmd=logout\';" data-placement="left" data-container="body" title="' . $titleStr . '" rel="m3help"><i class="fas fa-window-close fa-2x text-danger" style="background: radial-gradient(white 50%, transparent 50%);"></i></a></div>' . M3_NL;
						} else {
							$titleStr = '閉じる';
							//echo '<div class="m3configclose"><a href="#" onclick="window.close();" data-placement="left" data-container="body" title="' . $titleStr . '" rel="m3help"><img src="' . $rootUrl . self::CLOSE_ICON_FILE . '" alt="' . $titleStr . '" /></a></div>' . M3_NL;
							echo '<div class="m3configclose"><a href="#" onclick="window.close();" data-placement="left" data-container="body" title="' . $titleStr . '" rel="m3help"><i class="fas fa-window-close fa-2x text-danger" style="background: radial-gradient(white 50%, transparent 50%);"></i></a></div>' . M3_NL;
						}
					}
				}
				// 「前へ」「次へ」ボタン
				$titleStr = '前へ';
				echo '<div class="m3configprev" style="display:none;"><a id="m3configprev" href="#"><img src="' . $rootUrl . self::PREV_ICON_FILE . 
							'" alt="' . $titleStr . '" title="' . $titleStr . '" rel="m3help" /></a></div>' . M3_NL;
				$titleStr = '次へ';
				echo '<div class="m3confignext" style="display:none;"><a id="m3confignext" href="#"><img src="' . $rootUrl . self::NEXT_ICON_FILE . 
							'" alt="' . $titleStr . '" title="' . $titleStr . '" rel="m3help" /></a></div>' . M3_NL;
//			}
		}
		echo '<!-- Widget Start -->' . M3_NL;
	}
	/**
	 * Widget単体起動の終了処理
	 *
	 * startWidget(),endWidget()は、以下のコマンドを処理する
	 *  ・M3_REQUEST_CMD_SHOW_WIDGET(ウィジェットの単体表示)
	 *  ・M3_REQUEST_CMD_CONFIG_WIDGET(ウィジェット設定画面)
	 *  ・M3_REQUEST_CMD_CONFIG_TEMPLATE(テンプレート設定画面)
	 *  ・M3_REQUEST_CMD_DO_WIDGET(ウィジェット単体実行)
	 *
	 * @param string $cmd			起動コマンド
	 * @param string $srcContent	HTML出力ソース
	 */
	function endWidget($cmd, $srcContent)
	{
		global $gDesignManager;
		
		// ウィジェット単体表示のときのみ出力
		if (!$this->showWidget) return;
		
		// メニューバー出力
		// パンくずリストを表示
		$breadcrumbHtml = '';
		if (!empty($this->adminSubNavbarDef)) $breadcrumbHtml .= $gDesignManager->createSubMenubar($this->adminSubNavbarDef);
		if (!empty($this->adminBreadcrumbDef)) $breadcrumbHtml .= $gDesignManager->createAdminBreadcrumb($this->adminBreadcrumbDef);
		$destContent = str_replace(self::MENUBAR_TAGS, $breadcrumbHtml, $srcContent);

		// ヘッドタグ出力
		$replaceStr .= '<script type="text/javascript">' . M3_NL;
		$replaceStr .= '//<![CDATA[' . M3_NL;
		
		// ##### 追加関数 #####
		// ウィジェット設定画面用
		if ($cmd == M3_REQUEST_CMD_CONFIG_WIDGET){		// ウィジェットの設定管理
			// 画面更新用関数追加
			$replaceStr .= 'function m3UpdateByConfig(serial){' . M3_NL;
			$replaceStr .= M3_INDENT_SPACE . 'var href = window.location.href.split("#");' . M3_NL;
			$replaceStr .= M3_INDENT_SPACE . 'window.location.href = href[0];' . M3_NL;
			$replaceStr .= M3_INDENT_SPACE . 'm3UpdateParentWindow();' . M3_NL;		// 親ウィンドウ更新
			$replaceStr .= '}' . M3_NL;
		
			// IEエラーメッセージ出力抑止
			$replaceStr .= 'function hideIEErrors(){' . M3_NL;
			$replaceStr .= M3_INDENT_SPACE . 'return true;' . M3_NL;
			$replaceStr .= '}' . M3_NL;
			$replaceStr .= 'window.onerror = hideIEErrors;' . M3_NL;
		}
		
		// ##### 初期処理 #####
		$replaceStr .= '$(function(){' . M3_NL;
		// トップ位置修正
		if ($cmd == M3_REQUEST_CMD_CONFIG_WIDGET || $cmd == M3_REQUEST_CMD_CONFIG_TEMPLATE ||						// ウィジェット設定画面またはテンプレート設定画面のとき
			($cmd == M3_REQUEST_CMD_DO_WIDGET && $this->isEditMode)){		// フロント画面編集モードのとき
			if (!empty($this->adminSubNavbarDef) || !empty($this->adminBreadcrumbDef)){
				$menubarHeight = $gDesignManager->getSubMenubarHeight();
				$replaceStr .= str_repeat(M3_INDENT_SPACE, 1) . '$("nav.secondlevel").css("margin-top", "0");' . M3_NL;
				$replaceStr .= str_repeat(M3_INDENT_SPACE, 1) . '$("body").css("padding-top", "' . $menubarHeight . 'px");' . M3_NL;
			}
		}
		// ##### ウィジェットからの指定による処理 #####
		if ($this->updateParentWindow){			// 親ウィンドウ再描画のとき
			if ($cmd == M3_REQUEST_CMD_CONFIG_WIDGET){		// ウィジェット詳細設定画面のとき
				$replaceStr .= str_repeat(M3_INDENT_SPACE, 1) . 'm3UpdateParentWindowByConfig(' . $this->updateDefSerial . ');' . M3_NL;// 更新する項目のページ定義シリアル番号
			} else if ($cmd == M3_REQUEST_CMD_DO_WIDGET){			// ウィジェット単体実行のとき
				$replaceStr .= str_repeat(M3_INDENT_SPACE, 1) . 'm3UpdateParentWindow();' . M3_NL;
			}
		}
		$replaceStr .= '});' . M3_NL;
		$replaceStr .= '//]]>' . M3_NL;
		$replaceStr .= '</script>' . M3_NL;
		$destContent = str_replace(self::MENUBAR_SCRIPT_TAGS, $replaceStr, $destContent);
		echo $destContent;// 変換したコンテンツを出力
		
		echo '<!-- Widget End -->' . M3_NL;
		
		// Bootstrap用のタグ出力
		if ($this->useBootstrap) echo '</div>' . M3_NL;
		
		echo '</body>' . M3_NL;
		echo '</html>' . M3_NL;
	}
	/**
	 * Widget単体RSS出力用のHTMLのヘッダ部(headタグ)出力
	 *
	 * Widgetの出力方法は、以下のパターンがある
	 *  ・HTMLヘッダ付加 - Widget単体で画面出力するためにHTMLヘッダを付加するパターン
	 *  ・HTMLヘッダなし - Wiget単体のタグ出力のみのパターン
	 *
	 * @param string $cmd		起動コマンド
	 */
	function startWidgetRss($cmd)
	{
	}
	/**
	 * Widget単体RSS出力用のタグを閉じる
	 *
	 * @param string $cmd			起動コマンド
	 * @param string $rssContent	RSS配信内容
	 */
	function endWidgetRss($cmd, $rssContent)
	{
		global $gEnvManager;
		
		// ページ作成中断のときは終了
		if ($this->isAbort) return;
		
		echo '<?xml version="1.0" encoding="' . M3_HTML_CHARSET . '" ?>' . M3_NL;
		
		// RSSチャンネルデータ取得
		$lang	= $gEnvManager->getCurrentLanguage();
		$date	= getW3CDate();		// RSS1.0用日付
		$copyright = $gEnvManager->getSiteCopyRight();		// 著作権
		$title	= $this->rssChannel['title'];				// タイトル
		$link	= $this->rssChannel['link'];				// RSS取得用URL
		$desc	= $this->rssChannel['description'];			// 説明
		$seq	= $this->rssChannel['seq'];					// 項目の並び順(URL)
		
		switch ($this->rssVersion){					// RSSバージョン
			case '1.0':
			default:
				echo '<rdf:RDF xmlns="http://purl.org/rss/1.0/" ';
				echo 'xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" ';
				echo 'xmlns:dc="http://purl.org/dc/elements/1.1/" ';
				echo 'xml:lang="' . $lang . '">' . M3_NL;
				echo '<channel rdf:about="' . convertUrlToHtmlEntity($link) . '">' . M3_NL;
  				echo '<title>' . convertToHtmlEntity($title) . '</title>' . M3_NL;
				echo '<link>' . convertUrlToHtmlEntity($link) . '</link>' . M3_NL;		// 「convertUrlToHtmlEntity」が必要
				echo '<description>' . convertToHtmlEntity($desc) . '</description>' . M3_NL;
				echo '<dc:language>' . $lang . '</dc:language>' . M3_NL;
				if (!empty($copyright)) echo '<dc:rights>' . convertToHtmlEntity($copyright) . '</dc:rights>' . M3_NL;
				echo '<dc:date>' . $date . '</dc:date>' . M3_NL;
				echo '<items>' . M3_NL;
				if (count($seq) > 0){
					echo str_repeat(M3_INDENT_SPACE, 1) . '<rdf:Seq>' . M3_NL;
					for ($i = 0; $i < count($seq); $i++){
						echo str_repeat(M3_INDENT_SPACE, 2) . '<rdf:li rdf:resource="' . convertUrlToHtmlEntity($seq[$i]) . '" />' . M3_NL;
					}
					echo str_repeat(M3_INDENT_SPACE, 1) . '</rdf:Seq>' . M3_NL;
				}
				echo '</items>' . M3_NL;
				echo '</channel>' . M3_NL;
				echo $rssContent;
				echo '</rdf:RDF>' . M3_NL;
				break;
			case '2.0':
				break;
		}
		
		// HTTPレスポンスヘッダ設定
		header("Content-type: text/xml; charset=utf-8");
	}
	/**
	 * 直サーバ接続時のXML出力用のHTMLのヘッダ部(headタグ)出力
	 *
	 * @param string $cmd		起動コマンド
	 * @return					なし
	 */
	function startWidgetXml($cmd)
	{
		// HTTPレスポンスヘッダ
		//header("Content-type: text/xml; charset=utf-8");
	}
	/**
	 * 直サーバ接続時のXML出力終了
	 *
	 * @param string $cmd		起動コマンド
	 * @return					なし
	 */
	function endWidgetXml()
	{
	}
	/**
	 * デフォルトのXML宣言取得
	 *
	 * @return string	XML宣言
	 */
	function getDefaultXmlDeclaration()
	{
		return '<?xml version="1.0" encoding="' . M3_HTML_CHARSET . '" ?>';
	}
	/**
	 * HTMLのヘッダ部(headタグ内)出力
	 *
	 * システムに共通な定義をHTMLのheadタグ内に出力する
	 * mosFunc.phpからも実行されるので、このメソッドは引数なしに固定。
	 * この関数は、以下の「形式1」または「形式2」でheadタグ内に記述する
	 *
	 * 形式1:            <!-- m3:HTMLHeader -->
	 * 形式2(old style): <?php mosShowHead(); ?>
	 */
	function getHeader()
	{
		// ヘッダタグ変換の設定
		$this->outputHead = true;				// HTMLヘッダ出力を行ったかどうか
		$this->outputByStandardHead = true;		// HTML文書の標準のヘッダタグを出力するか
		
		// ##### Magic3システムで必要なヘッダ出力用のタグを埋め込む #####
		echo self::HEAD_TAGS;			// HTMLヘッダの埋め込みデータ
	}
	/**
	 * HTML文書の標準のヘッダタグを出力
	 *
	 * @return string			標準ヘッダ文字列
	 */
	function getStandardHead()
	{
		global $gEnvManager;
		global $gRequestManager;
		
		$headStr = '';			// ヘッダ文字列;
		
		// 実行コマンドを取得
		$cmd = $gRequestManager->trimValueOf(M3_REQUEST_PARAM_OPERATION_COMMAND);
		
		if ($gEnvManager->getIsSmartphoneSite()){		// スマートフォン用サイトのときはHTML5で設定
			$this->isHtml5 = true;
		} else {
			$tempVer = $gEnvManager->getCurrentTemplateType();		// テンプレートタイプを取得(0=デフォルト(Joomla!v1.0),1=Joomla!v1.5,2=Joomla!v2.5)
			if (intval($tempVer) >= 2) $this->isHtml5 = true;
		}
		
		// キャラクターセット
		if ($this->isHtml5){
			$headStr .= '<meta charset="' . M3_HTML_CHARSET . '">' . M3_NL;
		} else {
			$headStr .= '<meta http-equiv="content-script-type" content="text/javascript" />' . M3_NL;
			$headStr .= '<meta http-equiv="content-style-type" content="text/css" />' . M3_NL;
			$headStr .= '<meta http-equiv="content-type" content="application/xhtml+xml; charset=' . M3_HTML_CHARSET .'" />' . M3_NL;
		}
		if ($gEnvManager->isAdminDirAccess()){		// 管理画面へのアクセスのとき
			// Bootstrapで必要なMETAタグを追加
			$headStr .= '<meta name="viewport" content="width=device-width, initial-scale=1">' . M3_NL;
		}
		
		// 基準ディレクトリの指定
		if (defined('M3_STATE_IN_INSTALL')){		// インストール時
			// インストール時は相対パスを使用するのでベースURLを設定する
			$baseUrl = dirname($gEnvManager->getCurrentScriptUrl());
			$headStr .= '<base href="' . $baseUrl . '/" />' . M3_NL;
		} else {
			if ($cmd == M3_REQUEST_CMD_SHOW_POSITION ||				// 表示位置を表示するとき
				$cmd == M3_REQUEST_CMD_SHOW_POSITION_WITH_WIDGET){	// 表示位置を表示するとき(ウィジェット付き)
		
				if ($gEnvManager->getUseSslAdmin()){
					$rootUrl = $gEnvManager->getSslRootUrl();
				} else {
					$rootUrl = $gEnvManager->getRootUrl();
				}
				$headStr .= '<base href="' . $rootUrl . '/" />' . M3_NL;
			}
		}
		return $headStr;
	}
	/**
	 * HTMLヘッダ出力文字列の取得
	 *
	 * @return string		HTMLヘッダ出力文字列
	 */
	function getHeaderOutput()
	{
		global $gEnvManager;
		global $gRequestManager;
		global $gInstanceManager;
		global $gSystemManager;

		$replaceStr = '';		// 変換文字列
		
		// 実行コマンドを取得
		$cmd = $gRequestManager->trimValueOf(M3_REQUEST_PARAM_OPERATION_COMMAND);
		$task = $gRequestManager->trimValueOf(M3_REQUEST_PARAM_OPERATION_TASK);
		$widgetId = $gRequestManager->trimValueOf(M3_REQUEST_PARAM_WIDGET_ID);
		$openBy = $gRequestManager->trimValueOf(M3_REQUEST_PARAM_OPEN_BY);		// ウィンドウオープンタイプ
		
		// ********************************************************
		//               ヘッダ文字列作成の前処理
		// ********************************************************
		// ##### テンプレートの設定、フレームの設定から必要なライブラリを取得 #####
		// Bootstrapライブラリ
//		if (!$this->useBootstrap) $this->useBootstrap = $gEnvManager->getCurrentTemplateUseBootstrap();
		if ($this->useBootstrap){
			if ($gEnvManager->isAdminDirAccess()){		// 管理画面へのアクセスのとき
				if ($gEnvManager->isSystemManageUser()){		// システム運用権限がある場合のみ有効(ログイン中の場合)
					$this->addAdminScript('', ScriptLibInfo::LIB_BOOTSTRAP);		// 管理画面でBootstrapを使用するかどうか
					if ($cmd != M3_REQUEST_CMD_SHOW_POSITION_WITH_WIDGET){		// 管理画面(ウィジェット付きポジション表示)以外のとき
						$this->addAdminScript('', ScriptLibInfo::LIB_BOOTSTRAP_ADMIN);	// Bootstrap管理画面オプション
					} else {
						// テンプレートタイプが管理画面のBootstrapバージョン(v3.0)と異なる場合はBootstrapを使用しない場合の管理機能用スクリプトを読み込む
						$templateType = $gEnvManager->getCurrentTemplateType();
						if ($templateType != M3_TEMPLATE_BOOTSTRAP_30) $this->addAdminScript('', ScriptLibInfo::LIB_NOBOOTSTRAP);		// Bootstrapを使用しない場合の管理機能用スクリプト
					}
				} else {		// ログインしていない場合(ログイン画面、インストール画面等)
					$this->addPermittedAdminScript(ScriptLibInfo::LIB_BOOTSTRAP);
					$this->addPermittedAdminScript(ScriptLibInfo::LIB_BOOTSTRAP_ADMIN);// Bootstrap管理画面オプション
					
					// アイコンフォントを追加
					$this->defaultAdminDirCssFiles[] = self::M3_ADMIN_ICONFONT_CSS_FILE;// 管理機能用アイコンフォントのCSS
				}
			} else {		// フロント画面へのアクセスの場合
				// ### Bootstrapのjsとcssはテンプレート側で読み込む ###
				// ### フロントからプレビューコマンドでログイン画面を表示させるパターンがあるので注意 ###
//				if ($cmd == M3_REQUEST_CMD_LOGIN || $cmd == M3_REQUEST_CMD_LOGOUT || $cmd == M3_REQUEST_CMD_PREVIEW ||				// ログイン、ログアウト場合
				if ($cmd == M3_REQUEST_CMD_LOGIN || $cmd == M3_REQUEST_CMD_LOGOUT || ($cmd == M3_REQUEST_CMD_PREVIEW && !$gEnvManager->isCurrentUserLogined()) ||				// ログイン、ログアウト場合
					($cmd == M3_REQUEST_CMD_DO_WIDGET && !empty($openBy) && $gEnvManager->isContentEditableUser())){		// ウィジェット単体実行でウィンドウを持つ場合の追加スクリプト
					
					$this->addScript('', ScriptLibInfo::LIB_BOOTSTRAP_ADMIN);		// Bootstrap管理画面オプション
				} else {
					if ($gEnvManager->isSystemManageUser() ||		// システム運用権限がある場合
						$gEnvManager->isContentEditableUser()){		// 投稿ユーザの場合
						// テンプレートタイプが管理画面のBootstrapバージョン(v3.0)と異なる場合はBootstrapを使用しない場合の管理機能用スクリプトを読み込む
						$templateType = $gEnvManager->getCurrentTemplateType();
						if ($templateType != M3_TEMPLATE_BOOTSTRAP_30) $this->addScript('', ScriptLibInfo::LIB_NOBOOTSTRAP);		// Bootstrapを使用しない場合の管理機能用スクリプト
					}
				}
			}
		} else {
			if ($gEnvManager->isSystemManageUser()){		// システム運用権限がある場合のみ有効(ログイン中の場合)
				if ($gEnvManager->isAdminDirAccess()){		// 管理画面へのアクセスのとき
					$this->addAdminScript('', ScriptLibInfo::LIB_NOBOOTSTRAP);		// Bootstrapを使用しない場合の管理機能用スクリプト
				} else {
					$this->addScript('', ScriptLibInfo::LIB_NOBOOTSTRAP);		// Bootstrapを使用しない場合の管理機能用スクリプト
				}
			} else if ($gEnvManager->isContentEditableUser()){		// 投稿ユーザの場合
				$this->addScript('', ScriptLibInfo::LIB_NOBOOTSTRAP);		// Bootstrapを使用しない場合の管理機能用スクリプト
			}
		}
		
		// ********************************************************
		//               ヘッダ文字列作成処理
		// ********************************************************
		// ##### インストール時のヘッダ出力 #####
		if (defined('M3_STATE_IN_INSTALL')){
			// タイトルの作成
			$title = '';
			if (count($this->headSubTitle) > 0) $title = htmlspecialchars(trim($this->headSubTitle[0]));
			
			// ********** メタタグの設定 **********
			$replaceStr .= '<title>' . $title . '</title>' . M3_NL;
			
			// ##### インストーラ用のファイルの読み込み #####
			$scriptsUrl = '../scripts';
			
			// 管理機能用共通ライブラリのCSSの読み込み
			$count = count($this->defaultAdminCssFiles);
			for ($i = 0; $i < $count; $i++){
				// CSSへのURLを作成
				$cssFilename = $this->defaultAdminCssFiles[$i];
				if (strncasecmp($cssFilename, 'http://', 7) == 0 || strncasecmp($cssFilename, 'https://', 8) == 0){
					$cssURL = $cssFilename;
				} else {
					$cssURL = $scriptsUrl . '/' . $cssFilename;
				}
				$replaceStr .=  '<link rel="stylesheet" type="text/css" href="' . $cssURL . '" />' . M3_NL;
			}
			
			// 管理画面用の共通スクリプトを読み込む
			$count = count($this->defaultAdminScriptFiles);
			for ($i = 0; $i < $count; $i++){
				$scriptFilename = $this->defaultAdminScriptFiles[$i];

				// スクリプトのURLを修正
				if (strncasecmp($scriptFilename, 'http://', 7) == 0 || strncasecmp($scriptFilename, 'https://', 8) == 0){
					$scriptURL = $scriptFilename;
					
					// SSLをページの状態に合わせる
					if ($isSslPage){
						$scriptURL = str_replace('http://', 'https://', $scriptURL);
					} else {
						$scriptURL = str_replace('https://', 'http://', $scriptURL);
					}
				} else {
					$scriptURL = $scriptsUrl . '/' . $scriptFilename;
				}
			
				// スクリプトをキャッシュ保存しない場合は、パラメータを付加
				if (!$this->hasScriptCache) $scriptURL .= $this->getCacheParam();
				$replaceStr .=  '<script type="text/javascript" src="' . convertUrlToHtmlEntity($scriptURL) . '"></script>' . M3_NL;
			}
			
			// ##### ページへJavascriptの埋め込む #####
			// JavaScriptグローバル変数の設定
			$rootUrl = $gEnvManager->getRootUrl();
			if (empty($rootUrl)) $rootUrl = $gEnvManager->calcSystemRootUrl();
			$replaceStr .= '<script type="text/javascript">' . M3_NL;
			$replaceStr .= '//<![CDATA[' . M3_NL;
			$replaceStr .= '// Magic3 Global values' . M3_NL;
			if (!empty($rootUrl)) $replaceStr .= 'var M3_ROOT_URL = "' . $rootUrl . '";' . M3_NL;		// システムルートURL
			$replaceStr .= '//]]>' . M3_NL;
			$replaceStr .= '</script>' . M3_NL;
			return $replaceStr;
		}
		
		// テンプレートの情報を取得
		$cleanType = $gEnvManager->getCurrentTemplateCleanType();		// テンプレートクリーンタイプ
/*		if ($this->systemDb->getTemplate($gEnvManager->getCurrentTemplateId(), $templateRow)){
			$cleanType = $templateRow['tm_clean_type'];
		}*/
		
		// URLの作成
		$isSslPage = false;
		if ($gEnvManager->isAdminDirAccess()){		// 管理画面へのアクセスのとき
			// 管理画面のSSL状態を参照
			if ($gEnvManager->getUseSslAdmin()) $isSslPage = true;		// 管理画面でSSLを使用するとき
		} else {
			$isSslPage = $this->isSslPage($gEnvManager->getCurrentPageId(), $gEnvManager->getCurrentPageSubId());
		}
		if ($isSslPage){
			$rootUrl = $gEnvManager->getSslRootUrl();
			$scriptsUrl = $gEnvManager->getSslScriptsUrl();		// スクリプト読み込み用パス
			$widgetsUrl = $gEnvManager->getSslWidgetsUrl();		// ウィジェット格納パス
			$templatesUrl = $gEnvManager->getSslTemplatesUrl();	// テンプレート読み込み用パス
		} else {
			$rootUrl = $gEnvManager->getRootUrl();
			$scriptsUrl = $gEnvManager->getScriptsUrl();		// スクリプト読み込み用パス
			$widgetsUrl = $gEnvManager->getWidgetsUrl();		// ウィジェット格納パス
			$templatesUrl = $gEnvManager->getTemplatesUrl();	// テンプレート読み込み用パス
		}

		// タイトルの設定
		if (!$this->showWidget){// 単体実行以外のとき
			if ($gEnvManager->isAdminDirAccess()){		// 管理画面へのアクセスの場合
				// 管理画面のタイトル
				// メイン画面は「サイト名 - 管理画面」、サブ画面は個別画面名
				$siteName = $gEnvManager->getSiteName();
//				if (empty($siteName)) $siteName = self::DEFAULT_SITE_NAME;
				$title = $siteName . ' - ' . self::DEFAULT_ADMIN_TITLE;
				$titleCount = count($this->headSubTitle);
				if ($titleCount > 0) $title = $this->headSubTitle[$titleCount -1];		// サブタイトルが設定されている場合は変更
				$replaceStr .= '<title>' . htmlspecialchars($title) . '</title>' . M3_NL;
				
				// その他HTMLヘッダに出力するタグ文字列
				if (!empty($this->adminHeadOthers)){
					// マクロを変換
					$this->adminHeadOthers = $gInstanceManager->getTextConvManager()->convContentMacro($this->adminHeadOthers, false/*改行コードをbrタグに変換しない*/, array(), true/*変換後の値はHTMLエスケープ処理する*/);
					$replaceStr .= $this->adminHeadOthers . M3_NL;
				}
			} else {			// 管理画面以外の画面へのアクセスの場合
				// 画面タイトル
				$titleItemCount = 0;		// タイトル項目数
				$defaultTitle = trim($this->gSystem->getSiteDef(M3_TB_FIELD_SITE_TITLE));
				if (!empty($defaultTitle)) $titleItemCount++;
				if (!empty($this->headSubTitle)){		// サブタイトルが設定されているとき
					$titleItemCount += count($this->headSubTitle);
				}
				// タイトルフォーマットを取得
				$title = '';
				if ($titleItemCount > 0){
					$format = $this->gSystem->getSystemConfig(self::CONFIG_KEY_HEAD_TITLE_FORMAT);
					if (empty($format)){
						$title = htmlspecialchars($defaultTitle);
					} else {
						$formats = explode(';', $format);
						$titleItemCount = ($titleItemCount > count($formats)) ? count($formats) : $titleItemCount;
						$title = $formats[$titleItemCount -1];
						$number = 1;
						if (!empty($defaultTitle)){
							$title = str_replace('$1', htmlspecialchars($defaultTitle), $title);
							$number++;
						}
						for ($i = 0; $i < count($this->headSubTitle); $i++){
							$key = '$' . $number;
							$value = htmlspecialchars(trim($this->headSubTitle[$i]));
							$title = str_replace($key, $value, $title);
							$number++;
						}
					}
				}
				if (!empty($title)) $replaceStr .= '<title>' . $title . '</title>' . M3_NL;

				// サイトの説明
				if (!empty($this->headDescription)) $replaceStr .= '<meta name="description" content="' . htmlspecialchars($this->headDescription) . '" />' . M3_NL;
		
				// 検索エンジン用キーワード
				if (!empty($this->headKeywords)) $replaceStr .= '<meta name="keywords" content="' . htmlspecialchars($this->headKeywords) . '" />' . M3_NL;
				
				// その他HTMLヘッダに出力するタグ文字列
				if (!empty($this->headOthers)){
					// マクロを変換
					$this->headOthers = $gInstanceManager->getTextConvManager()->convContentMacro($this->headOthers, false/*改行コードをbrタグに変換しない*/, array(), true/*変換後の値はHTMLエスケープ処理する*/);
					$replaceStr .= $this->headOthers . M3_NL;
				}
			}
		}
		
		// ##### PC用URLと携帯用URLのアクセス別に処理 #####
		// ##### テンプレート情報に応じた処理 #####
		// テンプレートクリーンが必要な場合はJQueryを追加
		if ($cleanType != 0) $this->addScriptFile($this->selectedJQueryFilename);		// JQueryスクリプト追加
		
		// 検索ロボットへの指示
		$robots = htmlspecialchars(trim($this->gSystem->getSiteDef(M3_TB_FIELD_SITE_ROBOTS)));
		if (!empty($robots)){
			$replaceStr .= '<meta name="robots" content="' . $robots . '" />' . M3_NL;
		}
	
		// サイト構築エンジン
		$replaceStr .= '<meta name="generator" content="' . M3_SYSTEM_NAME . ' ver.' . M3_SYSTEM_VERSION . ' - ' . M3_SYSTEM_DESCRIPTION . '" />' . M3_NL;		

		// カノニカル属性、Faviconの読み込み
		$templateId = $gEnvManager->getCurrentTemplateId();
		if ($gEnvManager->isAdminDirAccess()){		// 管理画面へのアクセスのとき
			// テンプレートのFaviconがない場合はシステムのデフォルトのFaviconを使用
			$faviconPath = $gEnvManager->getTemplatesPath() . '/' . $templateId . self::DEFAULT_FAVICON_FILE;
			if (file_exists($faviconPath)){		// ファイルが存在しているとき
				$faviconFile = $templatesUrl . '/' . $templateId . self::DEFAULT_FAVICON_FILE;
				$replaceStr .= '<link rel="shortcut icon" href="' . $faviconFile .'" />' . M3_NL;
			} else {
				// 管理画面のアイコンを設定
				$faviconPath = $gEnvManager->getSystemRootPath() . self::DEFAULT_ADMIN_FAVICON_FILE;
				if (file_exists($faviconPath)){		// ファイルが存在しているとき
					$faviconFile = $rootUrl . self::DEFAULT_ADMIN_FAVICON_FILE;
					$replaceStr .= '<link rel="shortcut icon" href="' . $faviconFile .'" />' . M3_NL;
				}
			}
		} else {
			// カノニカル属性
			if (!empty($this->headCanonicalUrl)) $replaceStr .= '<link rel="canonical" href="' . convertUrlToHtmlEntity($this->headCanonicalUrl) .'" />' . M3_NL;				// カノニカル属性URL
			
			// Favicon
			$faviconPath = $gEnvManager->getTemplatesPath() . '/' . $templateId . self::DEFAULT_FAVICON_FILE;
			if (file_exists($faviconPath)){		// ファイルが存在しているとき
				$faviconFile = $templatesUrl . '/' . $templateId . self::DEFAULT_FAVICON_FILE;
				$replaceStr .= '<link rel="shortcut icon" href="' . $faviconFile .'" />' . M3_NL;
			}
		}
		
		// ##### Ajaxライブラリの読み込み #####
		if (!$gEnvManager->isAdminDirAccess()){		// 通常画面へのアクセスのとき
			if ($this->systemDb->isExistsWidgetWithAjax($gEnvManager->getCurrentPageId(), $gEnvManager->getCurrentPageSubId())){// Ajaxライブラリを使用しているウィジェットがあるときは追加
				$this->addScriptFile($this->selectedJQueryFilename);		// デフォルトAjaxライブラリ追加
				$this->addScriptFile(self::M3_OPTION_SCRIPT_FILENAME);	// Magic3のオプションライブラリ追加
			}
		}
			
		// ##### 共通ライブラリの読み込み #####
		if (!$this->showWidget){// 単体実行以外のとき
			$this->systemDb->getWidgetsIdWithLib($gEnvManager->getCurrentPageId(), $gEnvManager->getCurrentPageSubId(), $rows);
			for ($i = 0; $i < count($rows); $i++){
				$this->addScript($task, trim($rows[$i]['wd_add_script_lib']));
			}
		}

		// ##### 共通CSS読み込み #####
		if (($gEnvManager->isAdminDirAccess() && $gEnvManager->isSystemManageUser()) || $this->isEditMode){			// フロント画面編集モード
			$cssURL = $scriptsUrl . '/' . self::M3_EDIT_CSS_FILE;
			$replaceStr .=  '<link rel="stylesheet" type="text/css" href="' . $cssURL . '" />' . M3_NL;
		}
		if ($gEnvManager->isAdminDirAccess()){		// 管理画面へのアクセスのとき
			if ($gEnvManager->isSystemManageUser()){		// システム運用権限がある場合のみ有効
				// 管理機能用共通ライブラリのCSSの読み込み
				$count = count($this->defaultAdminCssFiles);
				for ($i = 0; $i < $count; $i++){
					// CSSへのURLを作成
					//$cssURL = $scriptsUrl . '/' . $this->defaultAdminCssFiles[$i];
					$cssFilename = $this->defaultAdminCssFiles[$i];
					if (strncasecmp($cssFilename, 'http://', 7) == 0 || strncasecmp($cssFilename, 'https://', 8) == 0){
						$cssURL = $cssFilename;
					} else {
						$cssURL = $scriptsUrl . '/' . $cssFilename;
					}
					$replaceStr .=  '<link rel="stylesheet" type="text/css" href="' . $cssURL . '" />' . M3_NL;
				}
			} else {
				// 管理権限なしで管理ディレクトリアクセスで必要なCSSファイルを読み込む
				$count = count($this->defaultAdminDirCssFiles);
				for ($i = 0; $i < $count; $i++){
					// CSSへのURLを作成
					$cssURL = $scriptsUrl . '/' . $this->defaultAdminDirCssFiles[$i];
					$replaceStr .=  '<link rel="stylesheet" type="text/css" href="' . $cssURL . '" />' . M3_NL;
				}
			}
		} else {
			// 共通ライブラリのCSSの読み込み
			$count = count($this->defaultCssFiles);
			for ($i = 0; $i < $count; $i++){
				// CSSへのURLを作成
				$cssFilename = $this->defaultCssFiles[$i];
				if (strncasecmp($cssFilename, 'http://', 7) == 0 || strncasecmp($cssFilename, 'https://', 8) == 0){
					$cssURL = $cssFilename;
				} else {
					$cssURL = $scriptsUrl . '/' . $cssFilename;
				}
				$replaceStr .=  '<link rel="stylesheet" type="text/css" href="' . $cssURL . '" />' . M3_NL;
			}
		}
		
		// ##### 表示モードによるCSS読み込み #####
		// ウィジェット付きポジション画面は管理画面のアクセスではない
		if ($cmd == M3_REQUEST_CMD_SHOW_POSITION_WITH_WIDGET){		// ウィジェット付きポジション表示
			// ウィジェット操作用CSS
			$cssURL = $scriptsUrl . self::M3_ADMIN_WIDGET_CSS_FILE;
			$replaceStr .=  '<link rel="stylesheet" type="text/css" href="' . $cssURL . '" />' . M3_NL;
		}
		
		// ##### ウィジェットごとのCSS読み込み #####
		// CSSがあるウィジェットを取得
		$this->systemDb->getWidgetsIdWithCss($gEnvManager->getCurrentPageId(), $gEnvManager->getCurrentPageSubId(), $rows);
		for ($i = 0; $i < count($rows); $i++){
			$searchPath = $gEnvManager->getWidgetsPath() . '/' . $rows[$i]['wd_id'] . '/' . M3_DIR_NAME_CSS;
			// ディレクトリがあるときはディレクトリ内読み込み
			if (is_dir($searchPath)){
				$dir = dir($searchPath);
				while (($file = $dir->read()) !== false){
					$filePath = $searchPath . '/' . $file;
					if ($file != '.' && $file != '..' && is_file($filePath)
						&& strncmp($file, '_', 1) != 0){		// 「_」で始まる名前のファイルは読み込まない
				
						// CSSへのURLを作成
						$cssURL = $widgetsUrl . '/' . $rows[$i]['wd_id'] . '/' . M3_DIR_NAME_CSS . '/' . $file;
						$replaceStr .=  '<link rel="stylesheet" type="text/css" href="' . $cssURL . '" />' . M3_NL;
					}
				}
				$dir->close();
			}
		}
		// ##### 外部出力用CSS読み込み #####
		// ウィジェットからの追加のCSS読み込み
		$count = count($this->headCssFiles);
		for ($i = 0; $i < $count; $i++){
			$cssUrl = $this->headCssFiles[$i];
			if ($isSslPage) $cssUrl = str_replace('http://', 'https://', $cssUrl);			// SSL化が必要なときは変換
			$replaceStr .=  '<link rel="stylesheet" type="text/css" href="' . convertUrlToHtmlEntity($cssUrl) . '" />' . M3_NL;
		}
		
		// 外部出力用CSSデータがある場合はURLを追加
		if (!empty($this->exportCss)){
			$cssUrl = $this->createCssCmdUrl($isSslPage, date('YmdHis'));
			if ($isSslPage) $cssUrl = str_replace('http://', 'https://', $cssUrl);			// SSL化が必要なときは変換
			$replaceStr .=  '<link rel="stylesheet" type="text/css" href="' . convertUrlToHtmlEntity($cssUrl) . '" />' . M3_NL;
		}
		
		// ##### RSS配信情報の読み込み #####
		$count = count($this->headRssFiles);
		for ($i = 0; $i < $count; $i++){
			$rssUrl = $this->headRssFiles[$i]['href'];// リンク先URL
			$rssTitle = $this->headRssFiles[$i]['title'];// タイトル
			$replaceStr .=  '<link rel="alternate" type="application/rss+xml" title="' . $rssTitle . '" href="' . convertUrlToHtmlEntity($rssUrl) . '" />' . M3_NL;
		}
								
		// ##### 共通Javascriptの読み込み #####
		if ($gEnvManager->isAdminDirAccess()){		// 管理画面へのアクセスのとき
			if ($gEnvManager->isSystemManageUser()){		// システム運用権限がある場合のみ有効
				// 管理画面用の共通スクリプトを読み込む
				$count = count($this->defaultAdminScriptFiles);
				for ($i = 0; $i < $count; $i++){
					$scriptFilename = $this->defaultAdminScriptFiles[$i];

					// スクリプトのURLを修正
					if (strncasecmp($scriptFilename, 'http://', 7) == 0 || strncasecmp($scriptFilename, 'https://', 8) == 0){
						$scriptURL = $scriptFilename;
						
						// SSLをページの状態に合わせる
						if ($isSslPage){
							$scriptURL = str_replace('http://', 'https://', $scriptURL);
						} else {
							$scriptURL = str_replace('https://', 'http://', $scriptURL);
						}
					} else {
						$scriptURL = $scriptsUrl . '/' . $scriptFilename;
					}
				
					// スクリプトをキャッシュ保存しない場合は、パラメータを付加
					//$scriptURL = $scriptsUrl . '/' . $scriptFilename;
					if (!$this->hasScriptCache) $scriptURL .= $this->getCacheParam();
					$replaceStr .=  '<script type="text/javascript" src="' . convertUrlToHtmlEntity($scriptURL) . '"></script>' . M3_NL;
				}
				if ($cmd == M3_REQUEST_CMD_SHOW_POSITION_WITH_WIDGET){		// ウィジェット付きポジション表示のときは、ウィジェット操作ライブラリを読み込む
					// wigetのドラッグドロップ用
					$scriptURL = $scriptsUrl . '/' . self::M3_ADMIN_WIDGET_SCRIPT_FILENAME;
					if (!$this->hasScriptCache) $scriptURL .= $this->getCacheParam();// スクリプトをキャッシュ保存しない場合は、パラメータを付加
					$replaceStr .=  '<script type="text/javascript" src="' . convertUrlToHtmlEntity($scriptURL) . '"></script>' . M3_NL;
				}
			} else {		// システム運用権限がない場合
				// 管理権限なしで管理ディレクトリアクセスで必要なスクリプトを読み込む
				$count = count($this->defaultAdminDirScriptFiles);
				for ($i = 0; $i < $count; $i++){
					$scriptFilename = $this->defaultAdminDirScriptFiles[$i];

					// スクリプトをキャッシュ保存しない場合は、パラメータを付加
					$scriptURL = $scriptsUrl . '/' . $scriptFilename;
					if (!$this->hasScriptCache) $scriptURL .= $this->getCacheParam();
					$replaceStr .=  '<script type="text/javascript" src="' . convertUrlToHtmlEntity($scriptURL) . '"></script>' . M3_NL;
				}
			}
		} else {			// 通常画面
			// Googleマップライブラリの読み込み
			if ($this->useGooglemaps && $this->isContentGooglemaps) $this->addScriptFile(ScriptLibInfo::getScript(ScriptLibInfo::LIB_GOOGLEMAPS));	// コンテンツにGoogleマップが含むかどうか

			$useExternalJquery = $gSystemManager->getSystemConfig(self::CF_EXTERNAL_JQUERY);		// システム外部のjQueryを使用するかどうか
			
			$count = count($this->defaultScriptFiles);
			for ($i = 0; $i < $count; $i++){
				$defaultScriptFile = $this->defaultScriptFiles[$i];

				// 外部のjQueryを使用する場合はSCRIPTタグを出力しない
				if ($defaultScriptFile == $this->selectedJQueryFilename && $useExternalJquery) continue;
				
				// スクリプトのURLを修正
				if (strncasecmp($defaultScriptFile, 'http://', 7) == 0 || strncasecmp($defaultScriptFile, 'https://', 8) == 0){
					$scriptURL = $defaultScriptFile;
					
					// SSLをページの状態に合わせる
					if ($isSslPage){
						$scriptURL = str_replace('http://', 'https://', $scriptURL);
					} else {
						$scriptURL = str_replace('https://', 'http://', $scriptURL);
					}
				} else {
					$scriptURL = $scriptsUrl . '/' . $defaultScriptFile;
				}

				// スクリプトをキャッシュ保存しない場合は、パラメータを付加
				if (!$this->hasScriptCache) $scriptURL .= $this->getCacheParam();
				$replaceStr .=  '<script type="text/javascript" src="' . convertUrlToHtmlEntity($scriptURL) . '"></script>' . M3_NL;
			}
			
			// ### フロントからプレビューコマンドでログイン画面を表示させるパターンがあるので注意 ###
//				if ($cmd == M3_REQUEST_CMD_LOGIN || $cmd == M3_REQUEST_CMD_LOGOUT || $cmd == M3_REQUEST_CMD_PREVIEW){				// ログイン、ログアウト場合
			if ($cmd == M3_REQUEST_CMD_LOGIN || $cmd == M3_REQUEST_CMD_LOGOUT || ($cmd == M3_REQUEST_CMD_PREVIEW && !$gEnvManager->isCurrentUserLogined())){				// ログイン、ログアウト場合
				// 管理権限なしで管理ディレクトリアクセスで必要なスクリプトを読み込む
				$count = count($this->defaultAdminDirScriptFiles);
				for ($i = 0; $i < $count; $i++){
					$scriptFilename = $this->defaultAdminDirScriptFiles[$i];
					if (!in_array($scriptFilename, $this->defaultScriptFiles)){		// 既に追加されていない場合のみ追加
						// スクリプトをキャッシュ保存しない場合は、パラメータを付加
						$scriptURL = $scriptsUrl . '/' . $scriptFilename;
						if (!$this->hasScriptCache) $scriptURL .= $this->getCacheParam();
						$replaceStr .=  '<script type="text/javascript" src="' . convertUrlToHtmlEntity($scriptURL) . '"></script>' . M3_NL;
					}
				}
			}
			
			// ***** [WordPressテンプレート] jQueryの読み込み後、ヘッダ部に出力するテンプレート用のJavascriptがあれば読み込む *****
			$headData = $this->gEnv->getWpHeadScriptsData();
			if (!empty($headData)) $replaceStr .= $headData;
		}
		// ##### ウィジェットごとのJavaScript読み込み #####
		// スクリプトがあるウィジェットを取得
		$this->systemDb->getWidgetsIdWithScript($gEnvManager->getCurrentPageId(), $gEnvManager->getCurrentPageSubId(), $rows);
		for ($i = 0; $i < count($rows); $i++){
			$searchPath = $gEnvManager->getWidgetsPath() . '/' . $rows[$i]['wd_id'] . '/' . M3_DIR_NAME_SCRIPTS;
		
			// ディレクトリがあるときはディレクトリ内読み込み
			if (is_dir($searchPath)){
				$dir = dir($searchPath);
				while (($file = $dir->read()) !== false){
					$filePath = $searchPath . '/' . $file;
					if ($file != '.' && $file != '..' && is_file($filePath)
						&& strncmp($file, '_', 1) != 0){		// 「_」で始まる名前のファイルは読み込まない
					
						// スクリプトへのURLを作成
						$scriptURL = $widgetsUrl . '/' . $rows[$i]['wd_id'] . '/' . M3_DIR_NAME_SCRIPTS . '/' . $file;
						// スクリプトをキャッシュ保存しない場合は、パラメータを付加
						if (!$this->hasScriptCache) $scriptURL .= $this->getCacheParam();
						$replaceStr .=  '<script type="text/javascript" src="' . convertUrlToHtmlEntity($scriptURL) . '"></script>' . M3_NL;
					}
				}
				$dir->close();
			}
		}
		// ウィジェットからの追加のCSS読み込み
		$count = count($this->headScriptFiles);
		for ($i = 0; $i < $count; $i++){
			$scriptUrl = $this->headScriptFiles[$i];
			if ($isSslPage) $scriptUrl = str_replace('http://', 'https://', $scriptUrl);			// SSL化が必要なときは変換
	
			// スクリプトをキャッシュ保存しない場合は、パラメータを付加
			if (!$this->hasScriptCache) $scriptUrl .= $this->getCacheParam();
			$replaceStr .=  '<script type="text/javascript" src="' . convertUrlToHtmlEntity($scriptUrl) . '"></script>' . M3_NL;
		}
		
		// ##### ページへJavascriptの埋め込む #####
		// JavaScriptグローバル変数の設定
		//$replaceStr .= '<script type="text/javascript">' . M3_NL;
		//$replaceStr .= '<!--' . M3_NL;
		$replaceStr .= '<script type="text/javascript">' . M3_NL;
		$replaceStr .= '//<![CDATA[' . M3_NL;
		$replaceStr .= '// Magic3 Global values' . M3_NL;
		$replaceStr .= 'var M3_ROOT_URL = "' . $rootUrl . '";' . M3_NL;		// システムルートURL

		if ($gEnvManager->isAdminDirAccess() && $gEnvManager->isSystemManageUser()){		// 管理画面へのアクセス、システム運用権限があり
			$pageId = $gRequestManager->trimValueOf(M3_REQUEST_PARAM_DEF_PAGE_ID);		// ページID
			$pageSubId = $gRequestManager->trimValueOf(M3_REQUEST_PARAM_DEF_PAGE_SUB_ID);// ページサブID
				
			// 管理画面のオープン設定
			$replaceStr .= 'var M3_DEFAULT_ADMIN_URL="' . $gEnvManager->getDefaultAdminUrl() . '";' . M3_NL;		// 管理機能URL
			
			// ページID、ページサブID
			$replaceStr .= 'var M3_PAGE_ID = "' . $gEnvManager->getCurrentPageId() . '";' . M3_NL;
			$replaceStr .= 'var M3_PAGE_SUB_ID = "' . $gEnvManager->getCurrentPageSubId() . '";' . M3_NL;
			// WYSIWYGエディター
			$replaceStr .= 'var M3_WYSIWYG_EDITOR = "' . $this->wysiwygEditor . '";' . M3_NL;
			
			// ファイルブラウザ
			$replaceStr .= 'var M3_FILEBROWSER_VER = "' . $this->libFiles[ScriptLibInfo::LIB_ELFINDER]['version'] . '";' . M3_NL;		// バージョン
			$replaceStr .= 'var M3_FILEBROWSER_WIDTH_RATIO = ' . self::FILEBROWSER_WIDTH_RATIO . ';' . M3_NL;			// ファイルブラウザ幅比率
			$replaceStr .= 'var M3_FILEBROWSER_HEIGHT_RATIO = ' . self::FILEBROWSER_HEIGHT_RATIO . ';' . M3_NL;		// ファイルブラウザ高さ比率

			// Googleマップライブラリの読み込み
			if ($this->useGooglemaps){
				$replaceStr .= 'var M3_USE_GOOGLEMAPS = true;' . M3_NL;
				
				// CKEditor拡張プラグイン用の定義
				$googleMapsKey = $this->gSystem->getSystemConfig(self::CF_GOOGLE_MAPS_KEY);		// Googleマップ利用キー
				if (!empty($googleMapsKey)) $replaceStr .= 'var M3_GOOGLEMAPS_KEY="' . $googleMapsKey . '";' . M3_NL;		// システムルートURL
			} else {
				$replaceStr .= 'var M3_USE_GOOGLEMAPS = false;' . M3_NL;
			}

			// 起動がパーソナルモードかどうか
			if ($this->isPersonalMode){
				$replaceStr .= 'var M3_PERSONAL_MODE = true;' . M3_NL;
			} else {
				$replaceStr .= 'var M3_PERSONAL_MODE = false;' . M3_NL;
			}
			// 管理画面の小画面デバイス最適化を行うかどうか
			if ($gEnvManager->isSmallDeviceAdmin()){
				$replaceStr .= 'var M3_SMALL_DEVICE_OPTIMIZE = true;' . M3_NL;
			} else {
				$replaceStr .= 'var M3_SMALL_DEVICE_OPTIMIZE = false;' . M3_NL;
			}
			
			// ウィジェット詳細設定画面専用のJavaScriptグローバル変数
			if ($cmd == M3_REQUEST_CMD_CONFIG_WIDGET){
			//if ($cmd == M3_REQUEST_CMD_CONFIG_WIDGET || $cmd == M3_REQUEST_CMD_CONFIG_TEMPLATE){						// ウィジェット設定画面またはテンプレート設定画面のとき
				// ##### CKEditor用の設定 #####
				$replaceStr .= 'var M3_CONFIG_WIDGET_DEVICE_TYPE = ' . $this->configWidgetInfo['wd_device_type'] . ';' . M3_NL;			// ウィジェット設定画面のウィジェットの端末タイプ
				
				// CKEditor用のCSSファイル
				if (!empty($this->ckeditorCssFiles)){
					// 編集エリア用のCSSファイルを追加
					$this->ckeditorCssFiles[] = $scriptsUrl . '/' . self::M3_CKEDITOR_CSS_FILE;
					//array_unshift($this->ckeditorCssFiles, $scriptsUrl . '/' . self::M3_CKEDITOR_CSS_FILE);
					
					//$fileList = implode(', ', array_map(create_function('$a','return "\'" . $a . "\'";'), $this->ckeditorCssFiles));
					$fileList = implode(', ', array_map(function($a){ return "'" . $a . "'"; }, $this->ckeditorCssFiles));
					$replaceStr .= 'var M3_CONFIG_WIDGET_CKEDITOR_CSS_FILES = [ ' . $fileList . ' ];' . M3_NL;
				}
				// CKEditor用(レイアウト)のCSSファイル
				$cssFiles = array();
				$cssFiles[] = $scriptsUrl . '/' . ScriptLibInfo::BOOTSTRAP_ADMIN_CSS;		// BootstrapのCSSを追加
				$cssFiles[] = $scriptsUrl . '/' . self::M3_CKEDITOR_CSS_FILE;
//					$fileList = implode(', ', array_map(create_function('$a','return "\'" . $a . "\'";'), $cssFiles));
				$fileList = implode(', ', array_map(function($a){ return "'" . $a . "'"; }, $cssFiles));
				$replaceStr .= 'var M3_CONFIG_WIDGET_CKEDITOR_LAYOUT_CSS_FILES = [ ' . $fileList . ' ];' . M3_NL;
				
				// CKEditor用のテンプレートタイプ
				if (isset($this->ckeditorTemplateType)){
					$replaceStr .= 'var M3_CONFIG_WIDGET_CKEDITOR_TEMPLATE_TYPE = ' . $this->ckeditorTemplateType . ';' . M3_NL;
				}
			} else if ($cmd == M3_REQUEST_CMD_SHOW_POSITION){		// ポジション表示
				$replaceStr .= 'var M3_POSITION_DATA = "' . implode(',', $this->defPositions) . '";' . M3_NL;			// 管理画面画面の端末タイプ(主にテスト用に使用)
				$replaceStr .= 'var M3_CONFIG_WIDGET_DEVICE_TYPE = 0;' . M3_NL;			// 管理画面画面の端末タイプ(主にテスト用に使用)
			} else if ($cmd == M3_REQUEST_CMD_SHOW_POSITION_WITH_WIDGET){		// ウィジェット付きポジション表示
				// その他のポジションデータを取得
				$positionData = $this->getRestPositionData();
				
				// テンプレート上のポジション名
				if (count($this->viewPosId) > 0){
					$posArrayStr = '[';
					for ($i = 0; $i < count($this->viewPosId); $i++){
						$posArrayStr .= '\'#' . $this->viewPosId[$i] . '\'';
						if ($i < count($this->viewPosId) - 1) $posArrayStr .= ',';
					}
					$posArrayStr .= ']';
					$replaceStr .= 'var M3_POSITIONS = ' . $posArrayStr . ';' . M3_NL;
				}
				// 画面定義のリビジョン番号
				$replaceStr .= 'var M3_REVISION = ' . $this->pageDefRev . ';' . M3_NL;
				
				// その他のポジションデータ
				$replaceStr .= 'var M3_REST_POSITION_DATA = \'' . $positionData . '\';' . M3_NL;
			} else if (!empty($pageId)){
				$accessPoint = $this->gEnv->getAllDefaultPageId();
				for ($i = 0; $i < count($accessPoint); $i++){
					if ($pageId == $accessPoint[$i]){
						$replaceStr .= 'var M3_CONFIG_WIDGET_DEVICE_TYPE = ' . $i . ';' . M3_NL;			// ウィジェット設定画面のウィジェットの端末タイプ
						break;
					}
				}
			} else {		// メインの管理画面の場合
				$replaceStr .= 'var M3_CONFIG_WIDGET_DEVICE_TYPE = 0;' . M3_NL;			// 管理画面画面の端末タイプ(主にテスト用に使用)
			}
					
			if ($cmd == M3_REQUEST_CMD_SHOW_POSITION_WITH_WIDGET){		// ウィジェット付きポジション表示
				if (!empty($task)){		// 戻りタスクが設定されているときのみ最大化可能
					$replaceStr .= 'function gobackPagedef(){' . M3_NL;
					$replaceStr .= '    window.location.href = "' . $gEnvManager->getDefaultAdminUrl() . '?pageid=' . $pageId . '&pagesubid=' . $pageSubId . '&task=' . $task . '";' . M3_NL;
					$replaceStr .= '}' . M3_NL;
					$replaceStr .= '$(function(){' . M3_NL;
					$replaceStr .= '    $(document).keyup(function(e){' . M3_NL;
					$replaceStr .= '        if (e.which == 36) gobackPagedef();' . M3_NL;
					$replaceStr .= '    });' . M3_NL;
					$replaceStr .= '});' . M3_NL;
				}
			} else {
				// ##### 管理用テンプレートを使用している場合の処理 #####
				// Bootstrap用のスクリプト処理
				if ($this->useBootstrap){
					$replaceStr .= '$(function(){' . M3_NL;
					$replaceStr .= '    $(\'.button\').addClass(\'' . self::BOOTSTRAP_BUTTON_CLASS . '\');' . M3_NL;
					$replaceStr .= '});' . M3_NL;
				}
			}
		} else if ($this->isPageEditable){		// フロント画面ページ編集可能モードのとき
			$replaceStr .= 'var M3_DEFAULT_ADMIN_URL="' . $gEnvManager->getDefaultAdminUrl() . '";' . M3_NL;		// 管理機能URL
			
			// ページID、ページサブID
			$replaceStr .= 'var M3_PAGE_ID = "' . $gEnvManager->getCurrentPageId() . '";' . M3_NL;
			$replaceStr .= 'var M3_PAGE_SUB_ID = "' . $gEnvManager->getCurrentPageSubId() . '";' . M3_NL;
			// WYSIWYGエディター
			$replaceStr .= 'var M3_WYSIWYG_EDITOR = "' . $this->wysiwygEditor . '";' . M3_NL;
			
			// ファイルブラウザ
			$replaceStr .= 'var M3_FILEBROWSER_VER = "' . $this->libFiles[ScriptLibInfo::LIB_ELFINDER]['version'] . '";' . M3_NL;	// バージョン
			$replaceStr .= 'var M3_FILEBROWSER_WIDTH_RATIO = ' . self::FILEBROWSER_WIDTH_RATIO . ';' . M3_NL;			// ファイルブラウザ幅比率
			$replaceStr .= 'var M3_FILEBROWSER_HEIGHT_RATIO = ' . self::FILEBROWSER_HEIGHT_RATIO . ';' . M3_NL;		// ファイルブラウザ高さ比率
			
			// Googleマップライブラリの読み込み
			if ($this->useGooglemaps){
				$replaceStr .= 'var M3_USE_GOOGLEMAPS = true;' . M3_NL;
				
				// CKEditor拡張プラグイン用の定義
				$googleMapsKey = $this->gSystem->getSystemConfig(self::CF_GOOGLE_MAPS_KEY);		// Googleマップ利用キー
				if (!empty($googleMapsKey)) $replaceStr .= 'var M3_GOOGLEMAPS_KEY="' . $googleMapsKey . '";' . M3_NL;		// システムルートURL
			} else {
				$replaceStr .= 'var M3_USE_GOOGLEMAPS = false;' . M3_NL;
			}

			// 起動がパーソナルモードかどうか
			if ($this->isPersonalMode){
				$replaceStr .= 'var M3_PERSONAL_MODE = true;' . M3_NL;
			} else {
				$replaceStr .= 'var M3_PERSONAL_MODE = false;' . M3_NL;
			}
			
			// テンプレートタイプ
			$templateType = $gEnvManager->getCurrentTemplateType();
			if (isset($templateType)) $replaceStr .= 'var M3_TEMPLATE_TYPE = ' . $templateType . ';' . M3_NL;
		} else if ($this->isEditMode){			// フロント画面編集モード(コンテンツ編集可能ユーザ)
			if ($cmd == M3_REQUEST_CMD_DO_WIDGET && !empty($openBy)){						// ウィジェット単体実行でウィンドウを持つ場合の追加スクリプト
				// WYSIWYGエディター
				$replaceStr .= 'var M3_WYSIWYG_EDITOR = "' . $this->wysiwygEditor . '";' . M3_NL;
			
				// ファイルブラウザ
				$replaceStr .= 'var M3_FILEBROWSER_VER = "' . $this->libFiles[ScriptLibInfo::LIB_ELFINDER]['version'] . '";' . M3_NL;	// バージョン
				$replaceStr .= 'var M3_FILEBROWSER_WIDTH_RATIO = ' . self::FILEBROWSER_WIDTH_RATIO . ';' . M3_NL;			// ファイルブラウザ幅比率
				$replaceStr .= 'var M3_FILEBROWSER_HEIGHT_RATIO = ' . self::FILEBROWSER_HEIGHT_RATIO . ';' . M3_NL;		// ファイルブラウザ高さ比率
			
				// Googleマップライブラリの読み込み
				if ($this->useGooglemaps){
					$replaceStr .= 'var M3_USE_GOOGLEMAPS = true;' . M3_NL;
					
					// CKEditor拡張プラグイン用の定義
					$googleMapsKey = $this->gSystem->getSystemConfig(self::CF_GOOGLE_MAPS_KEY);		// Googleマップ利用キー
					if (!empty($googleMapsKey)) $replaceStr .= 'var M3_GOOGLEMAPS_KEY="' . $googleMapsKey . '";' . M3_NL;		// システムルートURL
				} else {
					$replaceStr .= 'var M3_USE_GOOGLEMAPS = false;' . M3_NL;
				}
			
				// 起動がパーソナルモードかどうか(常にパーソナルモード)
				$replaceStr .= 'var M3_PERSONAL_MODE = true;' . M3_NL;
			
				// 管理画面の小画面デバイス最適化を行うかどうか
				if ($gEnvManager->isSmallDeviceAdmin()){
					$replaceStr .= 'var M3_SMALL_DEVICE_OPTIMIZE = true;' . M3_NL;
				} else {
					$replaceStr .= 'var M3_SMALL_DEVICE_OPTIMIZE = false;' . M3_NL;
				}
			
				// ##### CKEditor用の設定 #####
				// ウィジェット情報取得
				$ret = $this->systemDb->getWidgetInfo($widgetId, $this->configWidgetInfo);
				$replaceStr .= 'var M3_CONFIG_WIDGET_DEVICE_TYPE = ' . $this->configWidgetInfo['wd_device_type'] . ';' . M3_NL;			// ウィジェット設定画面のウィジェットの端末タイプ
			
				// CKEditor用のCSSファイル
				if (!empty($this->ckeditorCssFiles)){
					// 編集エリア用のCSSファイルを追加
					$this->ckeditorCssFiles[] = $scriptsUrl . '/' . self::M3_CKEDITOR_CSS_FILE;
					//array_unshift($this->ckeditorCssFiles, $scriptsUrl . '/' . self::M3_CKEDITOR_CSS_FILE);
				
//						$fileList = implode(', ', array_map(create_function('$a','return "\'" . $a . "\'";'), $this->ckeditorCssFiles));
					$fileList = implode(', ', array_map(function($a){ return "'" . $a . "'"; }, $this->ckeditorCssFiles));
					$replaceStr .= 'var M3_CONFIG_WIDGET_CKEDITOR_CSS_FILES = [ ' . $fileList . ' ];' . M3_NL;
				}
				// CKEditor用(レイアウト)のCSSファイル
				$cssFiles = array();
				$cssFiles[] = $scriptsUrl . '/' . ScriptLibInfo::BOOTSTRAP_ADMIN_CSS;		// BootstrapのCSSを追加
				$cssFiles[] = $scriptsUrl . '/' . self::M3_CKEDITOR_CSS_FILE;
	//			$fileList = implode(', ', array_map(create_function('$a','return "\'" . $a . "\'";'), $cssFiles));
				$fileList = implode(', ', array_map(function($a){ return "'" . $a . "'"; }, $cssFiles));
				$replaceStr .= 'var M3_CONFIG_WIDGET_CKEDITOR_LAYOUT_CSS_FILES = [ ' . $fileList . ' ];' . M3_NL;
				
				// CKEditor用のテンプレートタイプ
				if (isset($this->ckeditorTemplateType)){
					$replaceStr .= 'var M3_CONFIG_WIDGET_CKEDITOR_TEMPLATE_TYPE = ' . $this->ckeditorTemplateType . ';' . M3_NL;
				}
				
				// Bootstrap用のスクリプト処理
				if ($this->useBootstrap){
					$replaceStr .= '$(function(){' . M3_NL;
					$replaceStr .= '    $(\'.button\').addClass(\'' . self::BOOTSTRAP_BUTTON_CLASS . '\');' . M3_NL;
					$replaceStr .= '});' . M3_NL;
				}
			} else {
				// プレビュー画面用にテンプレートタイプを出力
				$templateType = $gEnvManager->getCurrentTemplateType();
				if (isset($templateType)) $replaceStr .= 'var M3_TEMPLATE_TYPE = ' . $templateType . ';' . M3_NL;
				
				// ##### ヘルプシステムの組み込み #####
				if ($this->useHelp){			// ヘルプ表示のとき
					$replaceStr .= '$(function(){' . M3_NL;
					$replaceStr .= '    if (jQuery().tooltip) $(\'[rel=m3help]\').tooltip({ placement: \'top\'});' . M3_NL;
					$replaceStr .= '});' . M3_NL;
				}
			}
		} else {			// 権限なしの場合
			if (!$gEnvManager->isAdminDirAccess()){			// フロント画面のとき
				// テンプレートタイプを追加(ファイルローダ等で使用)
				$templateType = $gEnvManager->getCurrentTemplateType();
				if (isset($templateType)) $replaceStr .= 'var M3_TEMPLATE_TYPE = ' . $templateType . ';' . M3_NL;
			}
		}
		
		// ##### パネルメニュー(フロント画面と管理画面の切り替え等)の表示 #####
		// PC用、携帯用、スマートフォン用画面とウィジェット付きポジションの管理画面時に表示
		if (($gEnvManager->isAdminDirAccess() && $cmd == M3_REQUEST_CMD_SHOW_POSITION_WITH_WIDGET) ||		// 管理画面(ウィジェット付きポジション表示)のとき
			(!$gEnvManager->isAdminDirAccess() &&							// 一般用画面のとき
				$cmd != M3_REQUEST_CMD_DO_WIDGET &&							// ウィジェット単体実行でない
				$cmd != M3_REQUEST_CMD_RSS)){								// RSS配信でない
			//if ($gEnvManager->isSystemAdmin()){				// 管理者権限がある場合のみ有効
			if ($gEnvManager->isSystemManageUser()){		// システム運用権限ありの場合
				// トップメニュー項目作成
				$menubarTag = '';	// 管理用メニューバー
				$adminTag = '';		// 管理画面ボタン
				$editTag = '';		// 編集ボタン
				$logoutTag = '';		// ログアウトボタン
				
				if ($gEnvManager->isAdminDirAccess()){		// 管理画面(ウィジェット付きポジション表示)の場合
					// 編集ボタン
/*						$titleStr = '編集終了';
					$linkUrl = $gRequestManager->trimValueOf(M3_REQUEST_PARAM_BACKUP_URL);		// 退避していたURLを取得
					if (empty($linkUrl)) $linkUrl = $gEnvManager->getDefaultUrl();
					$editTag = '<li><a href="' . convertUrlToHtmlEntity($linkUrl) . '">';
					$editTag .= '<img src="' . $rootUrl . self::EDIT_END_ICON_FILE . '" alt="' . $titleStr . '" title="' . $titleStr . '" /></a></li>';
					*/
					$titleStr = '編集終了';
					$linkUrl = $gRequestManager->trimValueOf(M3_REQUEST_PARAM_BACKUP_URL);		// 退避していたURLを取得
					if (empty($linkUrl)) $linkUrl = $gEnvManager->getDefaultUrl();
					//$editTag = '<div class="m3editend"><a href="' . convertUrlToHtmlEntity($linkUrl) . '" rel="m3help" data-placement="bottom" data-container="body" title="' . $titleStr . '">';
					//$editTag .= '<img src="' . $rootUrl . self::EDIT_END_ICON_FILE . '" alt="' . $titleStr . '" /></a></div>';
					$editTag = '<div class="m3editend m3topright"><a href="' . convertUrlToHtmlEntity($linkUrl) . '" rel="m3help" data-placement="left" data-container="body" title="' . $titleStr . '">';
					//$editTag .= '<i class="glyphicon glyphicon-ok-sign"></i></a></div>';
					$editTag .= '<i class="fas fa-stop-circle text-danger fa-2x" style="background: radial-gradient(white 50%, transparent 50%);"></i></a></div>';
					$menubarTag .= $editTag;
					
			//		$this->initScript .= str_repeat(M3_INDENT_SPACE, 1) . 'if (window.parent && window.parent.frames.length == 0){' . M3_NL;// インラインフレームでないときパネルメニューを表示
					$this->initScript .= str_repeat(M3_INDENT_SPACE, 1) . 'if (window == window.parent){' . M3_NL;// インラインフレームでないときパネルメニューを表示(修正 2016/9/1)
					$this->initScript .= str_repeat(M3_INDENT_SPACE, 2) . '$("body").prepend(\'' . $menubarTag . '\');' . M3_NL;		// appendでうまく表示できないのでprependで表示
					$this->initScript .= str_repeat(M3_INDENT_SPACE, 1) . '}' . M3_NL;
				} else if ($this->isAccessPointWithAdminMenu){		// 通常画面は、管理メニューを使用するアクセスポイントの場合のみ表示
					// 管理画面ボタン
					//$titleStr = '管理画面へ遷移';
					$titleStr = 'ダッシュボードへ';
					$linkUrl = $gEnvManager->getDefaultAdminUrl();
					$adminTag = '<li><a href="' . convertUrlToHtmlEntity($linkUrl) . '" rel="m3help" data-placement="bottom" data-container="body" title="' . $titleStr . '">';
					$adminTag .= '<i class="dashboard fas fa-tachometer-alt text-warning"></i></a></li>';	// ダッシュボードアイコン
				
					// 編集ボタン
					$titleStr = 'ページ編集';
					$linkUrl  = $gEnvManager->getDefaultAdminUrl() . '?' . M3_REQUEST_PARAM_OPERATION_COMMAND . '=' .M3_REQUEST_CMD_SHOW_POSITION_WITH_WIDGET;
					$linkUrl .= '&' . M3_REQUEST_PARAM_DEF_PAGE_ID . '=' . $gEnvManager->getCurrentPageId();
					$linkUrl .= '&' . M3_REQUEST_PARAM_DEF_PAGE_SUB_ID . '=' . $gEnvManager->getCurrentPageSubId();
					$linkUrl .= '&' . M3_REQUEST_PARAM_BACKUP_URL . '=' . urlencode($gEnvManager->getCurrentRequestUri());			// URL退避用
					$editTag = '<li><a href="' . convertUrlToHtmlEntity($linkUrl) . '" rel="m3help" data-placement="bottom" data-container="body" title="' . $titleStr . '">';
					//$editTag .= '<img src="' . $rootUrl . self::EDIT_PAGE_ICON_FILE . '" alt="' . $titleStr . '" /></a></li>';
					$editTag .= '<i class="editpage fas fa-clone"></i></a></li>';
					
					// ログアウトボタン
					$titleStr = 'ログアウト';
//						if ($gEnvManager->isAdminDirAccess()){		// 管理画面の場合
//							$linkUrl = $gRequestManager->trimValueOf(M3_REQUEST_PARAM_BACKUP_URL);		// 退避していたURLを取得
//							if (empty($linkUrl)) $linkUrl = $gEnvManager->getDefaultUrl();
//						} else {
						$linkUrl = $gEnvManager->getCurrentRequestUri();
//						}
					$linkUrl = createUrl($linkUrl, M3_REQUEST_PARAM_OPERATION_COMMAND . '=' . M3_REQUEST_CMD_LOGOUT);
					$logoutTag = '<li><a href="' . convertUrlToHtmlEntity($linkUrl) . '" rel="m3help" data-placement="bottom" data-container="body" title="' . $titleStr . '">';
					//$logoutTag .= '<img src="' . $rootUrl . self::LOGOUT_ICON_FILE . '" alt="' . $titleStr . '" /></a></li>';
					$logoutTag .= '<i class="logout fas fa-power-off text-danger"></i></a></li>';
				
					// ウィジェット操作表示制御ボタン
					$titleStr = 'ウィジェット操作';
					$widgetToolTag .= '<li><div class="m3widgettoolbutton m3-nav m3-navbar-nav">';
					//$widgetToolTag .= '<button type="button" class="m3-navbar-btn btn btn-sm" data-color="success" rel="m3help" data-placement="bottom" data-container="body" title="' . $titleStr . '"><span class="title">' . $titleStr . '</span></button>';
					$widgetToolTag .= '<button type="button" class="m3-navbar-btn btn btn-sm" data-color="success"><span class="title">' . $titleStr . '</span></button>';
					$widgetToolTag .= '<input type="checkbox" class="hidden" />';
					$widgetToolTag .= '</div></li>';
					
					$menubarTag .= '<div id="m3slidepanel" class="m3panel_top m3-navbar-default" style="top:-60px; visibility: visible;">';
					$menubarTag .= '<div class="m3panelopener m3topleft"><a href="#" rel="m3help" data-placement="bottom" data-container="body" title="メニューバーを表示"><!--<i class="glyphicon glyphicon-align-justify"></i>--><i class="fas fa-align-justify fa-2x"></i></a></div>';
					$menubarTag .= '<div>';
					$menubarTag .= '<ul class="m3-nav m3-navbar-nav">';
					if ($gEnvManager->isSystemAdmin()){				// 管理画面、編集モードは、管理者権限がある場合のみ有効
						$menubarTag .= $adminTag;
						$menubarTag .= $editTag;
					}
					$menubarTag .= $widgetToolTag;
					$menubarTag .= '</ul>';
					$menubarTag .= '<ul class="m3-nav m3-navbar-nav pos-right">';	// 右寄せボタン
					$menubarTag .= $logoutTag;
					$menubarTag .= '</ul>';
					$menubarTag .= '</div>';
					$menubarTag .= '</div>';

					$this->initScript .= str_repeat(M3_INDENT_SPACE, 1) . 'if (window == window.parent){' . M3_NL;// インラインフレームでないときパネルメニューを表示(修正 2016/9/1)
					$this->initScript .= str_repeat(M3_INDENT_SPACE, 2) . '$("body").append(\'' . $menubarTag . '\');' . M3_NL;
					$this->initScript .= str_repeat(M3_INDENT_SPACE, 2) . '$(".m3panel_top").m3slidepanel({ "position": "top", "type": "push" });' . M3_NL;
					$this->initScript .= str_repeat(M3_INDENT_SPACE, 2) . '$("body").css("position", "relative");' . M3_NL;
					$this->initScript .= str_repeat(M3_INDENT_SPACE, 2) . 'm3SetHelp($(\'#m3slidepanel\'));' . M3_NL;
					$this->initScript .= str_repeat(M3_INDENT_SPACE, 2) . 'm3SetupWidgetTool(\'m3widgettoolbutton\');' . M3_NL;
					$this->initScript .= str_repeat(M3_INDENT_SPACE, 1) . '}' . M3_NL;
				}
			}
		}
		// ##### フロント画面からのウィジェット操作用ツールバー #####
		if (!$gEnvManager->isAdminDirAccess() && 
			$cmd != M3_REQUEST_CMD_DO_WIDGET &&							// ウィジェット単体実行でない
			$cmd != M3_REQUEST_CMD_RSS){								// RSS配信でない
			if ($gEnvManager->isSystemManageUser()){		// 一般用画面で管理者権限がある場合のみ有効
				// 管理用ツールバー
				$this->initScript .= M3_INDENT_SPACE . '$(\'div.m3_widget\').each(function(){' . M3_NL;
				$this->initScript .= str_repeat(M3_INDENT_SPACE, 2) . 'var attrs = m3_splitAttr($(this).attr(\'m3\'));' . M3_NL;
				$this->initScript .= str_repeat(M3_INDENT_SPACE, 2) . 'var widgetId = attrs[\'widgetid\'];' . M3_NL;
				$this->initScript .= str_repeat(M3_INDENT_SPACE, 2) . 'var serialNo = attrs[\'serial\'];' . M3_NL;
				$this->initScript .= str_repeat(M3_INDENT_SPACE, 2) . 'var configId = attrs[\'configid\'];' . M3_NL;
				$this->initScript .= str_repeat(M3_INDENT_SPACE, 2) . 'var useconfig = attrs[\'useconfig\'];' . M3_NL;
				
				$this->initScript .= str_repeat(M3_INDENT_SPACE, 2) . 'var html = \'\';' . M3_NL;
				$this->initScript .= str_repeat(M3_INDENT_SPACE, 2) . 'if (useconfig == 1){' . M3_NL;
//				$this->initScript .= str_repeat(M3_INDENT_SPACE, 3) . 
//									'html += \'<a href="javascript:void(0);" onclick="m3ShowConfigWindow(\\\'\' + widgetId + \'\\\', \' + configId + \', \' + serialNo + \');return false;" rel="m3help" data-container="body" title="ウィジェットの設定">' .
//									'<img src="' . $rootUrl . self::CONFIG_ICON32_FILE . '" alt="ウィジェットの設定" width="32" height="32" /></a>\';' . M3_NL;
				$this->initScript .= str_repeat(M3_INDENT_SPACE, 3) . 
									'html += \'<a href="javascript:void(0);" onclick="m3ShowConfigWindow(\\\'\' + widgetId + \'\\\', \' + configId + \', \' + serialNo + \');return false;" rel="m3help" title="ウィジェットの設定">' .
									'<i class="fas fa-cog fa-2x" style="color: black; margin:3px;"></i></a>\';' . M3_NL;
				$this->initScript .= str_repeat(M3_INDENT_SPACE, 2) . '}' . M3_NL;
				if ($gEnvManager->isSystemAdmin()){		// 位置調整は管理者権限がある場合のみ有効(管理ウィジェットの機能のため)
					//$this->initScript .= str_repeat(M3_INDENT_SPACE, 2) . 
					//					'html += \'<a href="javascript:void(0);" onclick="m3ShowAdjustWindow(\' + configId + \', \' + serialNo + \', M3_PAGE_ID, M3_PAGE_SUB_ID);return false;" rel="m3help" data-container="body" title="タイトル・スタイル調整">' .
					//					'<img src="' . $rootUrl . self::ADJUST_ICON32_FILE . '" alt="タイトル・スタイル調整" width="32" height="32" /></a>\';' . M3_NL;
					$this->initScript .= str_repeat(M3_INDENT_SPACE, 2) . 
										'html += \'<a href="javascript:void(0);" onclick="m3ShowAdjustWindow(\' + configId + \', \' + serialNo + \', M3_PAGE_ID, M3_PAGE_SUB_ID);return false;" rel="m3help" data-container="body" title="タイトル・スタイル調整">' .
										'<i class="fas fa-window-maximize fa-2x" style="color: black; margin:3px;"></i></a>\';' . M3_NL;
				}
				$this->initScript .= str_repeat(M3_INDENT_SPACE, 2) . 'if (html != \'\'){' . M3_NL;
				//$this->initScript .= str_repeat(M3_INDENT_SPACE, 3) . 
				//						'html = \'<div class="m3tooltip" style="display:none;">\' + html + \'<a class="m3closebox" href="javascript:void(0);" rel="m3help" data-container="body" title="閉じる">' . 
				//						'<img src="' . $rootUrl . self::CLOSE_BOX_ICON32_FILE . '" alt="閉じる" width="11" height="11" /></a></div>\';' . M3_NL;
				$this->initScript .= str_repeat(M3_INDENT_SPACE, 3) . 
										'html = \'<div class="m3tooltip" style="display:none;">\' + html + \'<a class="m3closebox" href="javascript:void(0);" rel="m3help" data-container="body" title="閉じる">' . 
										'<i class="fas fa-window-close" style="color:black; font-size:15px; vertical-align:20px;"></i></a></div>\';' . M3_NL;
										
				$this->initScript .= str_repeat(M3_INDENT_SPACE, 3) . '$(this).append(html);' . M3_NL;
				$this->initScript .= str_repeat(M3_INDENT_SPACE, 2) . '}' . M3_NL;
				// クリックイベントの設定
				$this->initScript .= str_repeat(M3_INDENT_SPACE, 2) . '$(this).click(function(){' . M3_NL;
				$this->initScript .= str_repeat(M3_INDENT_SPACE, 3) . 'var tooltipObj = $(this).children(\'.m3tooltip\');' . M3_NL;
				$this->initScript .= str_repeat(M3_INDENT_SPACE, 3) . 'if (tooltipObj.css(\'display\') == \'none\'){' . M3_NL;
				$this->initScript .= str_repeat(M3_INDENT_SPACE, 4) . 'if (_m3ShowWidgetTool){' . M3_NL;
				$this->initScript .= str_repeat(M3_INDENT_SPACE, 5) . 'tooltipObj.show();' . M3_NL;
				$this->initScript .= str_repeat(M3_INDENT_SPACE, 4) . '}' . M3_NL;
				$this->initScript .= str_repeat(M3_INDENT_SPACE, 3) . '} else {' . M3_NL;
				$this->initScript .= str_repeat(M3_INDENT_SPACE, 4) . 'tooltipObj.hide();' . M3_NL;
				$this->initScript .= str_repeat(M3_INDENT_SPACE, 3) . '}' . M3_NL;
				$this->initScript .= str_repeat(M3_INDENT_SPACE, 2) . '});' . M3_NL;
				// ウィジェットボーダーハイライト、ツールチップ表示
				$this->initScript .= str_repeat(M3_INDENT_SPACE, 2) . '$(this).hover(function(){' . M3_NL;
				$this->initScript .= str_repeat(M3_INDENT_SPACE, 3) . 'if (_m3ShowWidgetTool){' . M3_NL;
				$this->initScript .= str_repeat(M3_INDENT_SPACE, 4) . '$(this).addClass(\'m3_widget_highlight\');' . M3_NL;
				$this->initScript .= str_repeat(M3_INDENT_SPACE, 4) . 'var tooltipObj = $(this).children(\'.m3tooltip\');' . M3_NL;
				$this->initScript .= str_repeat(M3_INDENT_SPACE, 4) . 'var pos = $(this).position();' . M3_NL;
				$this->initScript .= str_repeat(M3_INDENT_SPACE, 4) . 'var width = $(this).outerWidth();' . M3_NL;
				$this->initScript .= str_repeat(M3_INDENT_SPACE, 4) . 'var tooltipWidth = tooltipObj.outerWidth(true);' . M3_NL;
				$this->initScript .= str_repeat(M3_INDENT_SPACE, 4) . 'var x = pos.left + width - tooltipWidth;' . M3_NL;
				$this->initScript .= str_repeat(M3_INDENT_SPACE, 4) . 'var y = pos.top;' . M3_NL;
				$this->initScript .= str_repeat(M3_INDENT_SPACE, 4) . 'tooltipObj.css({position: "absolute",top: y + "px", left: x + "px"}).show();' . M3_NL;
				$this->initScript .= str_repeat(M3_INDENT_SPACE, 3) . '}' . M3_NL;
				$this->initScript .= str_repeat(M3_INDENT_SPACE, 2) . '}, function(){' . M3_NL;
				$this->initScript .= str_repeat(M3_INDENT_SPACE, 3) . '$(this).removeClass(\'m3_widget_highlight\');' . M3_NL;
				$this->initScript .= str_repeat(M3_INDENT_SPACE, 3) . 'var tooltipObj = $(this).children(\'.m3tooltip\');' . M3_NL;
				$this->initScript .= str_repeat(M3_INDENT_SPACE, 3) . 'tooltipObj.hide();' . M3_NL;
				$this->initScript .= str_repeat(M3_INDENT_SPACE, 2) . '});' . M3_NL;
				// 閉じるボタン処理
				$this->initScript .= str_repeat(M3_INDENT_SPACE, 2) . '$(this).find(\'.m3closebox\').click(function(event){' . M3_NL;
				$this->initScript .= str_repeat(M3_INDENT_SPACE, 3) . '$(this).children(\'.m3tooltip\').hide();' . M3_NL;
				$this->initScript .= str_repeat(M3_INDENT_SPACE, 2) . '});' . M3_NL;
				// コンテンツ編集ボタンの位置修正
				$this->initScript .= str_repeat(M3_INDENT_SPACE, 2) . 'var pos = $(this).position();' . M3_NL;
				$this->initScript .= str_repeat(M3_INDENT_SPACE, 2) . 'var offset = $(this).offset();' . M3_NL;
				$this->initScript .= str_repeat(M3_INDENT_SPACE, 2) . 'var width = $(this).outerWidth();' . M3_NL;
				$this->initScript .= str_repeat(M3_INDENT_SPACE, 2) . '$(this).find(\'.m3edittool\').each(function(){' . M3_NL;
				$this->initScript .= str_repeat(M3_INDENT_SPACE, 3) . '$(this).offset({left: offset.left});' . M3_NL;
				$this->initScript .= str_repeat(M3_INDENT_SPACE, 3) . '$(this).width(width);' . M3_NL;
				$this->initScript .= str_repeat(M3_INDENT_SPACE, 2) . '});' . M3_NL;
				$this->initScript .= M3_INDENT_SPACE . '});' . M3_NL;
				
				// コンテキストメニューを作成
				$this->initScript .= M3_INDENT_SPACE . 'var widgetWindow = \'<div class="m3_contextmenu" id="m3_widgetmenu" style="visibility:hidden;">\';' . M3_NL;
				$this->initScript .= M3_INDENT_SPACE . 'widgetWindow += \'<ul>\';' . M3_NL;
	//			$this->initScript .= M3_INDENT_SPACE . 'widgetWindow += \'<li id="m3_wconfig"><img src="\' + M3_ROOT_URL + \'/images/system/config.png" />&nbsp;<span>ウィジェットの設定</span></li>\';' . M3_NL;
				$this->initScript .= M3_INDENT_SPACE . 'widgetWindow += \'<li id="m3_wconfig"><i class="fas fa-cog"></i>&nbsp;<span>ウィジェットの設定</span></li>\';' . M3_NL;
				if ($gEnvManager->isSystemAdmin()){		// 位置調整は管理者権限がある場合のみ有効(管理ウィジェットの機能のため)
					//$this->initScript .= M3_INDENT_SPACE . 'widgetWindow += \'<li id="m3_wadjust"><img src="\' + M3_ROOT_URL + \'/images/system/adjust_widget.png" />&nbsp;<span>タイトル・スタイル調整</span></li>\';' . M3_NL;
					$this->initScript .= M3_INDENT_SPACE . 'widgetWindow += \'<li id="m3_wadjust"><i class="fas fa-window-maximize"></i>&nbsp;<span>タイトル・スタイル調整</span></li>\';' . M3_NL;
				}
				$this->initScript .= M3_INDENT_SPACE . 'widgetWindow += \'</ul>\';' . M3_NL;
				$this->initScript .= M3_INDENT_SPACE . 'widgetWindow += \'</div>\';' . M3_NL;
				$this->initScript .= M3_INDENT_SPACE . '$("body").append(widgetWindow);' . M3_NL;
				$this->initScript .= M3_INDENT_SPACE . '$(\'div.m3_widget\').contextMenu(\'m3_widgetmenu\', {' . M3_NL;
				$this->initScript .= str_repeat(M3_INDENT_SPACE, 2) . 'menuStyle: {' . M3_NL;
				$this->initScript .= str_repeat(M3_INDENT_SPACE, 3) . 'backgroundColor: \'#FFFFFF\',' . M3_NL;
				//$this->initScript .= str_repeat(M3_INDENT_SPACE, 3) . 'width: "150px",' . M3_NL;
				$this->initScript .= str_repeat(M3_INDENT_SPACE, 3) . 'textAlign: \'left\',' . M3_NL;
				//$this->initScript .= str_repeat(M3_INDENT_SPACE, 3) . 'font: \'c, sans-serif\'' . M3_NL;
				$this->initScript .= str_repeat(M3_INDENT_SPACE, 3) . 'font: \'15px/1.5 "Lucida Grande","Hiragino Kaku Gothic ProN",Meiryo,sans-serif\'' . M3_NL;
				$this->initScript .= str_repeat(M3_INDENT_SPACE, 2) . '},' . M3_NL;
				$this->initScript .= str_repeat(M3_INDENT_SPACE, 2) . 'itemStyle: {' . M3_NL;
				$this->initScript .= str_repeat(M3_INDENT_SPACE, 3) . 'padding: \'3px 3px\'' . M3_NL;
				$this->initScript .= str_repeat(M3_INDENT_SPACE, 2) . '},' . M3_NL;
				$this->initScript .= str_repeat(M3_INDENT_SPACE, 2) . 'bindings: {' . M3_NL;
				$this->initScript .= str_repeat(M3_INDENT_SPACE, 3) . '\'m3_wconfig\': function(t){' . M3_NL;
				$this->initScript .= str_repeat(M3_INDENT_SPACE, 4) . 'var attrs = m3_splitAttr($(\'#\' + t.id).attr(\'m3\'));' . M3_NL;
				$this->initScript .= str_repeat(M3_INDENT_SPACE, 4) . 'if (attrs[\'useconfig\'] == \'0\'){' . M3_NL;
				$this->initScript .= str_repeat(M3_INDENT_SPACE, 5) . 'alert("このウィジェットには設定画面がありません");' . M3_NL;
				$this->initScript .= str_repeat(M3_INDENT_SPACE, 5) . 'return;' . M3_NL;
				$this->initScript .= str_repeat(M3_INDENT_SPACE, 4) . '}' . M3_NL;
				$this->initScript .= str_repeat(M3_INDENT_SPACE, 4) . 'm3ShowConfigWindow(attrs[\'widgetid\'], attrs[\'configid\'], attrs[\'serial\']);' . M3_NL;
				$this->initScript .= str_repeat(M3_INDENT_SPACE, 3) . '},' . M3_NL;
				$this->initScript .= str_repeat(M3_INDENT_SPACE, 3) . '\'m3_wadjust\': function(t){' . M3_NL;
				$this->initScript .= str_repeat(M3_INDENT_SPACE, 4) . 'var attrs = m3_splitAttr($(\'#\' + t.id).attr(\'m3\'));' . M3_NL;
				$this->initScript .= str_repeat(M3_INDENT_SPACE, 4) . 'm3ShowAdjustWindow(attrs[\'configid\'], attrs[\'serial\'], M3_PAGE_ID, M3_PAGE_SUB_ID);' . M3_NL;
				$this->initScript .= str_repeat(M3_INDENT_SPACE, 3) . '}' . M3_NL;
				$this->initScript .= str_repeat(M3_INDENT_SPACE, 2) . '},' . M3_NL;
				$this->initScript .= str_repeat(M3_INDENT_SPACE, 2) . 'onContextMenu: function(e){' . M3_NL;
				$this->initScript .= str_repeat(M3_INDENT_SPACE, 3) . 'if (_m3ShowWidgetTool){' . M3_NL;
				$this->initScript .= str_repeat(M3_INDENT_SPACE, 4) . 'return true;' . M3_NL;
				$this->initScript .= str_repeat(M3_INDENT_SPACE, 3) . '} else {' . M3_NL;
				$this->initScript .= str_repeat(M3_INDENT_SPACE, 4) . 'return false;' . M3_NL;
				$this->initScript .= str_repeat(M3_INDENT_SPACE, 3) . '}' . M3_NL;
				$this->initScript .= str_repeat(M3_INDENT_SPACE, 2) . '},' . M3_NL;
				$this->initScript .= str_repeat(M3_INDENT_SPACE, 2) . 'onShowMenu: function(e, menu){' . M3_NL;
				$this->initScript .= str_repeat(M3_INDENT_SPACE, 3) . '// メニュー項目の変更' . M3_NL;
				$this->initScript .= str_repeat(M3_INDENT_SPACE, 3) . 'var attrs = m3_splitAttr($(e.target).parents(\'dl\').attr(\'m3\'));' . M3_NL;
				$this->initScript .= str_repeat(M3_INDENT_SPACE, 3) . 'if (attrs[\'useconfig\'] == \'0\'){' . M3_NL;
				$this->initScript .= str_repeat(M3_INDENT_SPACE, 4) . '$(\'#m3_wconfig\', menu).remove();' . M3_NL;
				$this->initScript .= str_repeat(M3_INDENT_SPACE, 3) . '}' . M3_NL;
				$this->initScript .= str_repeat(M3_INDENT_SPACE, 3) . 'return menu;' . M3_NL;
				$this->initScript .= str_repeat(M3_INDENT_SPACE, 2) . '}' . M3_NL;
				$this->initScript .= M3_INDENT_SPACE . '});' . M3_NL;
				
				// コンテンツの「非公開」表示
				$this->initScript .= M3_INDENT_SPACE . '$(\'div.m3content_private\').each(function(){' . M3_NL;
//					$this->initScript .= str_repeat(M3_INDENT_SPACE, 2) . 'var pos = $(this).position();' . M3_NL;
//					$this->initScript .= str_repeat(M3_INDENT_SPACE, 2) . 'var top = pos.top;' . M3_NL;
//					$this->initScript .= str_repeat(M3_INDENT_SPACE, 2) . 'var left = pos.left;' . M3_NL;
//					$this->initScript .= str_repeat(M3_INDENT_SPACE, 2) . 'var width = $(this).css(\'width\');' . M3_NL;
//					$this->initScript .= str_repeat(M3_INDENT_SPACE, 2) . 'var height = $(this).css(\'height\');' . M3_NL;
				$this->initScript .= str_repeat(M3_INDENT_SPACE, 2) . 'var html = \'<div class="m3content_overlay"><div><h2>非公開</h2></div></div>\';' . M3_NL;
				$this->initScript .= str_repeat(M3_INDENT_SPACE, 2) . '$(this).prepend(html);' . M3_NL;
			//	$this->initScript .= str_repeat(M3_INDENT_SPACE, 2) . 'var overlayObj = $(this).children(\'.m3content_overlay div\');' . M3_NL;
			//	$this->initScript .= str_repeat(M3_INDENT_SPACE, 2) . 'overlayObj.css({ position:"absolute", top:top + "px", left:left + "px" }).show();' . M3_NL;
				$this->initScript .= M3_INDENT_SPACE . '});' . M3_NL;
			}
		}
		// ##### フロント画面のデフォルトのJavaスクリプト #####
//			if (!$gEnvManager->isAdminDirAccess()){
//				$this->initScript .= str_repeat(M3_INDENT_SPACE, 1) . 'if (jQuery().tooltip) $(\'[rel=tooltip]\').tooltip();' . M3_NL;		// 標準ツールチップ作成
//			}
		// ポップアップメッセージがある場合は表示
		if (!empty($this->popupMsg)){
			$replaceStr .=  'alert("' . $this->popupMsg . '");' . M3_NL;
		}
		// テンプレートに応じた処理
		if ($cleanType == 1){
			// HTMLのクリーン処理が必要なときはコードを埋め込む
			$this->initScript .= '    $(\'.PostHeaderIcons\').remove();' . M3_NL;// 不要なアイコン表示タグの削除
			$this->initScript .= '    $(\'.PostMetadataHeader\').each(function(i){' . M3_NL;
			$this->initScript .= '        if ($(this).text().trim() == \'\') $(this).remove();' . M3_NL;
			$this->initScript .= '    });' . M3_NL;
		}
		
		// 管理画面用スクリプト追加
		$replaceStr .= $this->getLastContents();
		
		// 初期処理用スクリプト埋め込み
		if (!empty($this->initScript)){
			$replaceStr .= '$(function(){' . M3_NL;
			$replaceStr .= $this->initScript;
			$replaceStr .= '});' . M3_NL;
		}
		
		//$replaceStr .= '// -->' . M3_NL;
		//$replaceStr .= '</script>' . M3_NL;
		$replaceStr .= '//]]>' . M3_NL;
		$replaceStr .= '</script>' . M3_NL;

		// HEADタグに埋め込むCSS,JavaScript,任意文字列
		if (count($this->headCss) > 0){
			// CSSの場合は全体をstyleタグで囲む
			$replaceStr .= '<style type="text/css">' . M3_NL;
			$replaceStr .= '<!--' . M3_NL;
			for ($i = 0; $i < count($this->headCss); $i++){
				$replaceStr .= $this->headCss[$i];
			}
			$replaceStr .= M3_NL . '//-->' . M3_NL;
			$replaceStr .= '</style>' . M3_NL;
		}
		if (count($this->headScript) > 0){
			// JavaScriptの場合は全体をscriptタグで囲む
			//$replaceStr .= '<script type="text/javascript">' . M3_NL;
			//$replaceStr .= '<!--' . M3_NL;
			$replaceStr .= '<script type="text/javascript">' . M3_NL;
			$replaceStr .= '//<![CDATA[' . M3_NL;
			for ($i = 0; $i < count($this->headScript); $i++){
				$replaceStr .= $this->headScript[$i];
			}
			//$replaceStr .= M3_NL . '//-->' . M3_NL;
			//$replaceStr .= '</script>' . M3_NL;
			$replaceStr .= M3_NL;
			$replaceStr .= '//]]>' . M3_NL;
			$replaceStr .= '</script>' . M3_NL;
		}
		if (count($this->headString) > 0){
			// 任意文字列の場合はそのまま追加
			for ($i = 0; $i < count($this->headString); $i++){
				$replaceStr .= $this->headString[$i];
			}
		}
		return $replaceStr;
	}
	/**
	 * 各部品のHTML出力
	 *
	 * @param string $position			HTMLテンプレート上の書き出し位置
	 * @param string $style				ウィジェットの表示スタイル(空の場合=Joomla!v1.0テンプレート用、空以外=Joomla!v1.5テンプレート用)
	 * @param int    $templateVer		テンプレートバージョン(0=デフォルト(Joomla!v1.0)、-1=携帯用、1=Joomla!v1.5、2=Joomla!v2.5)
	 * @param array  $attr				その他属性
	 * @return string					出力コンテンツ
	 */
	function getContents($position, $style = '', $templateVer = 0, $attr = array())
	{
		static $render;		// HTML生成オブジェクト
		global $gRequestManager;
		global $gEnvManager;
		
		// ファイル名、ページ名を取得
		$filename	= $gEnvManager->getCurrentPageId();
		$subId		= $gEnvManager->getCurrentPageSubId();
		if (empty($subId)) $subId = $gEnvManager->getDefaultPageSubId();

		// ポジション名表示モードに応じて出力を作成
		$contents = '';		// 出力コンテンツ
		switch ($this->showPositionMode){
			case 0:		// 通常画面
				// ページ定義を取得。同じポジションが続く場合は最初の一度だけ定義を取得
				if (empty($this->pageDefPosition) || $position != $this->pageDefPosition){		// ポジションが異なる場合
					$ret = $this->systemDb->getPageDef($filename, $subId, $position, $rows, 0/*定義セットIdデフォルト*/, true/*表示ウィジェットのみ*/);
					if ($ret){	// 1行以上データが取得できたとき
						$this->pageDefRows = $rows;			// ページ定義レコード
						$this->pageDefPosition = $position;
					} else {
						$this->pageDefRows = array();
						$this->pageDefPosition = '';
					}
				}
				// ウィジェットを実行
				$count = count($this->pageDefRows);
				
				if ($templateVer == 0){			// ########## Joomla!v1.0テンプレートの場合 ##########
					ob_start();// バッファ作成

					// ウィジェットヘッダ(Joomla!1.0用)を出力するテンプレートかどうかチェック
					$widgetHeaderType = $this->getTemplateWidgetHeaderType();
					for ($i = 0; $i < $count; $i++){
						$pageDefParam = $this->pageDefRows[$i];			// 画面定義パラメータ
						$widgetId = $this->pageDefRows[$i]['wd_id'];
						
						// ### 遅延実行ウィジェットはキャッシュしない ###
						// キャッシュデータがあるときはキャッシュデータを使用
						$cacheData = $this->gCache->getWidgetCache($request, $this->pageDefRows[$i], $metaTitle, $metaDesc, $metaKeyword);

						if (empty($cacheData)){		// キャッシュデータがないとき
							ob_clean();
							$ret = $this->pageDefLoop($position, $i, $this->pageDefRows[$i], $style, $launchWidgetTag, $titleTag, $widgetHeaderType);
							if (!$ret) break;
							$widgetContent = ob_get_contents();
							
							// ウィジェット共通のコンテンツ処理
							$widgetContent = $this->_addOptionContent($widgetContent, $pageDefParam);

							// ウィジェットの内枠(コンテンツ外枠)を設定
						//	$widgetContent = '<div class="' . self::WIDGET_INNER_CLASS . '">' . $widgetContent . '</div>';
										
							// キャッシュデータを設定
							$this->gCache->setWidgetCache($gRequestManager, $this->pageDefRows[$i], $widgetContent,
															$this->lastHeadTitle, $this->lastHeadDescription, $this->lastHeadKeywords);
															
							// ウィジェットの外枠タグを設定
							$widgetClassSuffix = $this->pageDefRows[$i]['pd_suffix'];		// 追加CSSクラスサフィックス
							$widgetOuterClass = self::WIDGET_OUTER_CLASS . ' ' . self::WIDGET_OUTER_CLASS_WIDGET_TAG . str_replace('/', '_', $widgetId);// ウィジェットの外枠のクラス
							if (!empty($widgetClassSuffix)) $widgetOuterClass .= ' ' . $widgetOuterClass . '_' . $widgetClassSuffix;	// 追加CSSクラス
							$widgetOuterClass .= ' ' . self::WIDGET_OUTER_CLASS_HEAD_POSITION . $position;		// ポジションブロッククラス
							$widgetContent = '<div class="' . $widgetOuterClass . '">' . $widgetContent . '</div>';
							if ($this->isPageEditable){		// フロント画面ページ編集可能モードのとき
								// ウィジェット表示タイプによる表示制御
								$widgetStatusClass = '';// ウィジェットの状態表示クラス
								$widgetStatusLabelOption = '';		// ウィジェット状態表示ラベル
								$controlType = $this->pageDefRows[$i]['pd_view_control_type'];
								$pageState = $this->pageDefRows[$i]['pd_view_page_state'];
								if (!empty($controlType)){
									switch ($controlType){
										case 1:			// ログイン時のみ表示
											$widgetStatusClass .= ' ' . self::WIDGET_LOGIN_STATUS_CLASS;
											$widgetStatusLabelOption .= ' data-bg-text="ログイン時のみ表示"';
											break;
										case 2:			// 非ログイン時のみ表示
											$widgetStatusClass .= ' ' . self::WIDGET_LOGIN_STATUS_CLASS;
											$widgetStatusLabelOption .= ' data-bg-text="非ログイン時のみ表示"';
											break;
										default:
											break;
									}
								}
								if (!empty($pageState)){
									switch ($pageState){
										case 1:			// トップ時のみ表示
											if (!$this->isPageTopUrl){
												$widgetStatusClass .= ' ' . self::WIDGET_INVISIBLE_CLASS;		// ページトップ(サブページ内のトップ位置)でなければ非表示
												$widgetStatusLabelOption .= ' data-bg-text="非表示中"';
											}
											break;
										default:
											break;
									}
								}
		
								$configId = $this->pageDefRows[$i]['pd_config_id'];		// 定義ID
								$serial = $this->pageDefRows[$i]['pd_serial'];		// シリアル番号
								$hasAdmin = '0';		// 管理画面があるかどうか
								if ($this->pageDefRows[$i]['wd_has_admin']) $hasAdmin = '1';
								$shared = '0';		// 共通属性があるかどうか
								if (empty($this->pageDefRows[$i]['pd_sub_id'])) $shared = '1';	// 共通ウィジェットのとき
								$m3Option = 'm3="widgetid:' . $widgetId . '; serial:' . $serial . '; configid:' . $configId . '; useconfig:' . $hasAdmin . '; shared:' . $shared . '"';
								$widgetTag = self::WIDGET_TAG_HEAD . $position . '_' . $i;				// ウィジェット識別用ユニークタグ
								$widgetContent = '<div id="' . $widgetTag . '" class="m3_widget' . $widgetStatusClass . '" rel="#m3editwidget" ' . $m3Option . $widgetStatusLabelOption . '>' . $widgetContent . '</div>';
							} else {
								$widgetTag = self::WIDGET_TAG_HEAD . $position . '_' . $i;				// ウィジェット識別用ユニークタグ
								$widgetContent = '<div id="' . $widgetTag . '">' . $widgetContent . '</div>';
							}
						} else {		// キャッシュデータがあるとき
							$widgetContent = $cacheData;
							
							// HTMLのメタタグを設定
							if (!empty($metaTitle)) $this->setHeadSubTitle($metaTitle);
							if (!empty($metaDesc)) $this->setHeadDescription($metaDesc);
							if (!empty($metaKeyword)) $this->setHeadKeywords($metaKeyword);
						}
						$contents .= $widgetContent;
						
						// ##### 外部出力用のCSSがある場合は追加 #####
						$exportCss = $this->pageDefRows[$i]['pd_css'];
						if (!empty($exportCss)){
							// ウィジェットのタグIDを変換
							$widgetTag = self::WIDGET_TAG_HEAD . $position . '_' . $i;				// ウィジェット識別用ユニークタグ
							$exportCss = str_replace(M3_TAG_START . M3_TAG_MACRO_WIDGET_CSS_ID . M3_TAG_END, $widgetTag, $exportCss);
							$this->addExportCss($exportCss);
						}
					}
					ob_end_clean();		// バッファ破棄
					
					if ($i < $count) return '';// 処理中断のときは終了
				} else if ($templateVer == 100){			// ########## WordPressテンプレートの場合 ##########
					ob_start();// バッファ作成

					// ウィジェットヘッダ(Joomla!1.0用)を出力するテンプレートかどうかチェック
					//$widgetHeaderType = $this->getTemplateWidgetHeaderType();
					for ($i = 0; $i < $count; $i++){
						$pageDefParam = $this->pageDefRows[$i];			// 画面定義パラメータ
						$widgetId = $this->pageDefRows[$i]['wd_id'];
						
						// ### 遅延実行ウィジェットはキャッシュしない ###
						// キャッシュデータがあるときはキャッシュデータを使用
						$cacheData = $this->gCache->getWidgetCache($request, $this->pageDefRows[$i], $metaTitle, $metaDesc, $metaKeyword);

						if (empty($cacheData)){		// キャッシュデータがないとき
							// WordPressウィジェット用のパラメータ設定
							$gEnvManager->setWpWidgetClass('');				// ウィジェットクラス名初期化
							
							ob_clean();
					//		$ret = $this->pageDefLoop($position, $i, $this->pageDefRows[$i], $style, $launchWidgetTag, $titleTag, $widgetHeaderType);
							$ret = $this->pageDefLoop($position, $i, $this->pageDefRows[$i], $style, $launchWidgetTag, $titleTag, 0/*タイトル出力なし*/);
							if (!$ret) break;
							$widgetContent = ob_get_contents();

							// WordPressウィジェット用のパラメータ設定
							$widgetClassName = $gEnvManager->getWpWidgetClass();				// ウィジェットクラス名
							
							$trimContent = trim($widgetContent);
							if (!empty($trimContent)){		// 出力が空でない場合
							
								// オブジェクト作成
								if (!isset($render)) $render = new WPRender();		// WordPress用HTML生成オブジェクト
							
								// デフォルトのウィジェットタイトル取得
								$defaultTitle = $gEnvManager->getCurrentWidgetTitle();
					
								// Joomla用のパラメータを取得
								//$joomlaParam = $gEnvManager->getCurrentWidgetJoomlaParam();

								// 遅延ウィジェットのときはタイトルタグを埋め込む
								if (!empty($titleTag)) $defaultTitle = $titleTag;
					
								// タイトルが空でタイトル表示を行う場合は、デフォルトタイトルを使用
								$title = $this->pageDefRows[$i]['pd_title'];
								if ($this->pageDefRows[$i]['pd_title_visible']){		// タイトル表示のとき
									if (empty($title)) $title = $defaultTitle;
								} else {
									$title = '';			// タイトルは非表示
								}
							
								// 強制的にタイトルを非表示にする場合はタイトルを修正
								if (!empty($title) && $this->pageDefRows[$i]['wd_hide_title']){
									if ($this->isPageEditable){		// フロント画面ページ編集可能モードのとき
										$title = '[' . $title . ']';
									} else {
										$title = '';
									}
								}
								
								// WordPress用パラメータ作成
								$params = array();				// ウィジェットごとの属性
								if (!empty($widgetClassName))  $params['moduleclass'] = $widgetClassName;	// ウィジェットクラス名
										
								// ウィジェット生成
								$widgetTag = self::WIDGET_TAG_HEAD . $position . '_' . ($i + 1);				// ウィジェット識別用ユニークタグ
								
								if (strcmp($position, 'main') == 0){// メイン部のとき
									$widgetContent = $render->getComponentContents($style, $widgetContent, $title, $attr, $params, $pageDefParam, $templateVer, $widgetTag);
								} else {
									$widgetContent = $render->getModuleContents($style, $widgetContent, $title, $attr, $params, $pageDefParam, $templateVer, $widgetTag);
								}
							
								// ウィジェット共通のコンテンツ処理
							//	$widgetContent = $this->_addOptionContent($widgetContent, $pageDefParam);

								// ウィジェットの内枠(コンテンツ外枠)を設定
							//	$widgetContent = '<div class="' . self::WIDGET_INNER_CLASS . '">' . $widgetContent . '</div>';
							}
							
							// キャッシュデータを設定
							$this->gCache->setWidgetCache($gRequestManager, $this->pageDefRows[$i], $widgetContent,
															$this->lastHeadTitle, $this->lastHeadDescription, $this->lastHeadKeywords);
															
							// ウィジェットの外枠タグを設定
					//		$widgetClassSuffix = $this->pageDefRows[$i]['pd_suffix'];		// 追加CSSクラスサフィックス
					//		$widgetOuterClass = self::WIDGET_OUTER_CLASS . ' ' . self::WIDGET_OUTER_CLASS_WIDGET_TAG . str_replace('/', '_', $widgetId);// ウィジェットの外枠のクラス
					//		if (!empty($widgetClassSuffix)) $widgetOuterClass .= ' ' . $widgetOuterClass . '_' . $widgetClassSuffix;	// 追加CSSクラス
					//		$widgetOuterClass .= ' ' . self::WIDGET_OUTER_CLASS_HEAD_POSITION . $position;		// ポジションブロッククラス
					//		$widgetContent = '<div class="' . $widgetOuterClass . '">' . $widgetContent . '</div>';
					
							if ($this->isPageEditable){		// フロント画面ページ編集可能モードのとき
								// ウィジェット表示タイプによる表示制御
								$widgetStatusClass = '';// ウィジェットの状態表示クラス
								$widgetStatusLabelOption = '';		// ウィジェット状態表示ラベル
								$controlType = $this->pageDefRows[$i]['pd_view_control_type'];
								$pageState = $this->pageDefRows[$i]['pd_view_page_state'];
								if (!empty($controlType)){
									switch ($controlType){
										case 1:			// ログイン時のみ表示
											$widgetStatusClass .= ' ' . self::WIDGET_LOGIN_STATUS_CLASS;
											$widgetStatusLabelOption .= ' data-bg-text="ログイン時のみ表示"';
											break;
										case 2:			// 非ログイン時のみ表示
											$widgetStatusClass .= ' ' . self::WIDGET_LOGIN_STATUS_CLASS;
											$widgetStatusLabelOption .= ' data-bg-text="非ログイン時のみ表示"';
											break;
										default:
											break;
									}
								}
								if (!empty($pageState)){
									switch ($pageState){
										case 1:			// トップ時のみ表示
											if (!$this->isPageTopUrl){
												$widgetStatusClass .= ' ' . self::WIDGET_INVISIBLE_CLASS;		// ページトップ(サブページ内のトップ位置)でなければ非表示
												$widgetStatusLabelOption .= ' data-bg-text="非表示中"';
											}
											break;
										default:
											break;
									}
								}
								
								$configId = $this->pageDefRows[$i]['pd_config_id'];		// 定義ID
								$serial = $this->pageDefRows[$i]['pd_serial'];		// シリアル番号
								$hasAdmin = '0';		// 管理画面があるかどうか
								if ($this->pageDefRows[$i]['wd_has_admin']) $hasAdmin = '1';
								$shared = '0';		// 共通属性があるかどうか
								if (empty($this->pageDefRows[$i]['pd_sub_id'])) $shared = '1';	// 共通ウィジェットのとき
								$m3Option = 'm3="widgetid:' . $widgetId . '; serial:' . $serial . '; configid:' . $configId . '; useconfig:' . $hasAdmin . '; shared:' . $shared . '"';
								$widgetTag = self::WIDGET_TAG_HEAD . $position . '_' . $i;				// ウィジェット識別用ユニークタグ
								$widgetContent = '<div id="' . $widgetTag . '" class="m3_widget' . $widgetStatusClass . '" rel="#m3editwidget" ' . $m3Option . $widgetStatusLabelOption . '>' . $widgetContent . '</div>';
							} else {
						//		$widgetTag = self::WIDGET_TAG_HEAD . $position . '_' . $i;				// ウィジェット識別用ユニークタグ
						//		$widgetContent = '<div id="' . $widgetTag . '">' . $widgetContent . '</div>';
							}
						} else {		// キャッシュデータがあるとき
							$widgetContent = $cacheData;
							
							// HTMLのメタタグを設定
							if (!empty($metaTitle)) $this->setHeadSubTitle($metaTitle);
							if (!empty($metaDesc)) $this->setHeadDescription($metaDesc);
							if (!empty($metaKeyword)) $this->setHeadKeywords($metaKeyword);
						}
						$contents .= $widgetContent;
						
						// ##### 外部出力用のCSSがある場合は追加 #####
						$exportCss = $this->pageDefRows[$i]['pd_css'];
						if (!empty($exportCss)){
							// ウィジェットのタグIDを変換
							$widgetTag = self::WIDGET_TAG_HEAD . $position . '_' . $i;				// ウィジェット識別用ユニークタグ
							$exportCss = str_replace(M3_TAG_START . M3_TAG_MACRO_WIDGET_CSS_ID . M3_TAG_END, $widgetTag, $exportCss);
							$this->addExportCss($exportCss);
						}
					}
					ob_end_clean();		// バッファ破棄
					
					if ($i < $count) return '';// 処理中断のときは終了
				} else {			// ########## Joomla!v1.5テンプレートの場合 ##########
					// テンプレート側で指定されたメニューの表示属性を設定
					$gEnvManager->setMenuAttr($attr);
					
					// ポジションタイプ
					$posType = '';
					if (!empty($attr['type'])) $posType = $attr['type'];
					
					for ($i = 0; $i < $count; $i++){
						$pageDefParam = $this->pageDefRows[$i];			// 画面定義パラメータ
						$widgetId = $this->pageDefRows[$i]['wd_id'];
														
						// ### 遅延実行ウィジェットはキャッシュしない ###
						// キャッシュデータがあるときはキャッシュデータを使用
						$cacheData = $this->gCache->getWidgetCache($request, $this->pageDefRows[$i], $metaTitle, $metaDesc, $metaKeyword);
							
						if (empty($cacheData)){		// キャッシュデータがないとき
							// ウィジェットのタイトルを初期化
							$gEnvManager->setCurrentWidgetTitle('');
						
							// Joomla用のパラメータを初期化
							$gEnvManager->setCurrentWidgetJoomlaParam(array());
							
							// ウィジェットの出力を取得
							ob_clean();
							$ret = $this->pageDefLoop($position, $i, $this->pageDefRows[$i], $style, $launchWidgetTag, $titleTag, 0/*タイトル出力なし*/);
							$widgetContent = ob_get_contents();

							$trimContent = trim($widgetContent);
							if (!empty($trimContent)){		// 出力が空でない場合
								$isRendered = false;		// Joomla!の描画処理を行ったかどうか
								if (!empty($this->pageDefRows[$i]['pd_use_render'])){			// Joomla!の描画処理を使用する場合
									// Joomla!ウィジェットの出力に埋め込む
									if (strcasecmp($style, 'none') != 0){
										// オブジェクト作成
										if (!isset($render)) $render = new JRender();
								
										// デフォルトのウィジェットタイトル取得
										$defaultTitle = $gEnvManager->getCurrentWidgetTitle();
								
										// Joomla用のパラメータを取得
										$joomlaParam = $gEnvManager->getCurrentWidgetJoomlaParam();

										// 遅延ウィジェットのときはタイトルタグを埋め込む
										if (!empty($titleTag)) $defaultTitle = $titleTag;
								
										// タイトルが空でタイトル表示を行う場合は、デフォルトタイトルを使用
										$title = $this->pageDefRows[$i]['pd_title'];
										if ($this->pageDefRows[$i]['pd_title_visible']){		// タイトル表示のとき
											if (empty($title)) $title = $defaultTitle;
										} else {
											$title = '';			// タイトルは非表示
										}
									
										// 強制的にタイトルを非表示にする場合はタイトルを修正
										if (!empty($title) && $this->pageDefRows[$i]['wd_hide_title']){
											if ($this->isPageEditable){		// フロント画面ページ編集可能モードのとき
												$title = '[' . $title . ']';
											} else {
												$title = '';
											}
										}
										
										// Joomla用パラメータ作成
										$params = array();				// ウィジェットごとの属性
										$widgetType = $this->pageDefRows[$i]['wd_type'];		// ウィジェットタイプ
										
										// オプションのJoomlaクラス(縦型メニュー(art-vmenu)等)
										$joomlaClass = $this->pageDefRows[$i]['wd_joomla_class'];		// 「wd_joomla_class」は使っていない?
										if (!empty($joomlaClass)) $params['moduleclass_sfx'] = $joomlaClass;
										if (isset($joomlaParam['moduleclass_sfx'])) $params['moduleclass_sfx'] = $joomlaParam['moduleclass_sfx'];// ウィジェットでjoomla用パラメータの設定があるとき
								
										if ($style == self::WIDGET_STYLE_NAVMENU){		// ポジションブロックがナビゲーションメニュー型の場合はメニューウィジェットのみ実行
											$moduleContent = '';
											
											// ウィジェットタイプがメニューのときはナビゲーションメニューを生成
											if ($widgetType == 'menu') $moduleContent = $render->getNavMenuContents($style, $widgetContent, $title, $attr, $params, $pageDefParam, $templateVer);
								
											// ナビゲーションバータイプで作成できないときはデフォルトの出力を取得
											if (empty($moduleContent)) $moduleContent = $render->getModuleContents('xhtml', $widgetContent, $title, $attr, $params, $pageDefParam, $templateVer);
											$widgetContent = $moduleContent;
										} else {
											// ウィジェットタイプに応じた処理を実行
											if ($widgetType == 'menu'){		// メニュー型
												// デフォルトまたは縦型メニューでメニュー表示
												$widgetContent = $render->getMenuContents($style, $widgetContent, $title, $attr, $params, $pageDefParam, $templateVer);
											} else if ($widgetType == 'breadcrumb'){		// パンくずリスト
												$moduleContent = '';
												
												if (empty($launchWidgetTag)){			// 遅延実行の場合はここでパンくずリストを作成しないで遅延実行時に行う
													$moduleContent = $render->getBreadcrumbContents($style, $widgetContent, $title, $attr, $params, $pageDefParam, $templateVer);
												} else {
													$moduleContent = $launchWidgetTag;		// 遅延実行ウィジェットのタグのみ取得
												}
												
												// パンくずリストで作成できないときはデフォルトの出力を取得
												if (empty($moduleContent)) $moduleContent = $render->getModuleContents('xhtml', $widgetContent, $title, $attr, $params, $pageDefParam, $templateVer);
												$widgetContent = $moduleContent;
											} else {
												if (strcmp($position, 'main') == 0){// メイン部のとき
													$widgetContent = $render->getComponentContents($style, $widgetContent, $title, $attr, $params, $pageDefParam, $templateVer);
												} else {		// その他の位置のとき
													$widgetContent = $render->getModuleContents($style, $widgetContent, $title, $attr, $params, $pageDefParam, $templateVer);
												}
											}
										}
										$isRendered = true;		// Joomla!の描画処理を行ったかどうか
									}
								}
								if (!$isRendered){		// Joomla!の描画処理を行っていない場合
									// ウィジェット共通のコンテンツ処理
									$widgetContent = $this->_addOptionContent($widgetContent, $pageDefParam);
								}
							}
							if (!$ret) return '';		// 処理中断のときは終了
							
							// キャッシュデータを設定
							$this->gCache->setWidgetCache($gRequestManager, $this->pageDefRows[$i], $widgetContent,
															$this->lastHeadTitle, $this->lastHeadDescription, $this->lastHeadKeywords);
															
							// ウィジェットの外枠タグを設定
							$widgetClassSuffix = $this->pageDefRows[$i]['pd_suffix'];		// 追加CSSクラスサフィックス
							$widgetOuterClass = self::WIDGET_OUTER_CLASS . ' ' . self::WIDGET_OUTER_CLASS_WIDGET_TAG . str_replace('/', '_', $widgetId);// ウィジェットの外枠のクラス
							if (!empty($widgetClassSuffix)) $widgetOuterClass .= ' ' . $widgetOuterClass . '_' . $widgetClassSuffix;	// 追加CSSクラス
							$widgetOuterClass .= ' ' . self::WIDGET_OUTER_CLASS_HEAD_POSITION . $position;	// ポジションブロッククラス
							$widgetContent = '<div class="' . $widgetOuterClass . '">' . $widgetContent . '</div>';
							if ($this->isPageEditable){		// フロント画面ページ編集可能モードのとき
								// ウィジェット表示タイプによる表示制御
								$widgetStatusClass = '';// ウィジェットの状態表示クラス
								$widgetStatusLabelOption = '';		// ウィジェット状態表示ラベル
								$controlType = $this->pageDefRows[$i]['pd_view_control_type'];
								$pageState = $this->pageDefRows[$i]['pd_view_page_state'];
								if (!empty($controlType)){
									switch ($controlType){
										case 1:			// ログイン時のみ表示
											$widgetStatusClass .= ' ' . self::WIDGET_LOGIN_STATUS_CLASS;
											$widgetStatusLabelOption .= ' data-bg-text="ログイン時のみ表示"';
											break;
										case 2:			// 非ログイン時のみ表示
											$widgetStatusClass .= ' ' . self::WIDGET_LOGIN_STATUS_CLASS;
											$widgetStatusLabelOption .= ' data-bg-text="非ログイン時のみ表示"';
											break;
										default:
											break;
									}
								}
								if (!empty($pageState)){
									switch ($pageState){
										case 1:			// トップ時のみ表示
											if (!$this->isPageTopUrl){
												$widgetStatusClass .= ' ' . self::WIDGET_INVISIBLE_CLASS;		// ページトップ(サブページ内のトップ位置)でなければ非表示
												$widgetStatusLabelOption .= ' data-bg-text="非表示中"';
											}
											break;
										default:
											break;
									}
								}
			
								//$editInfo = 'widgetid:' . $this->pageDefRows[$i]['wd_id'];
								$configId = $this->pageDefRows[$i]['pd_config_id'];		// 定義ID
								$serial = $this->pageDefRows[$i]['pd_serial'];		// シリアル番号
								$hasAdmin = '0';		// 管理画面があるかどうか
								if ($this->pageDefRows[$i]['wd_has_admin']) $hasAdmin = '1';
								$shared = '0';		// 共通属性があるかどうか
								if (empty($this->pageDefRows[$i]['pd_sub_id'])) $shared = '1';	// 共通ウィジェットのとき
								$m3Option = 'm3="widgetid:' . $widgetId . '; serial:' . $serial . '; configid:' . $configId . '; useconfig:' . $hasAdmin . '; shared:' . $shared . '"';
								$widgetTag = self::WIDGET_TAG_HEAD . $position . '_' . $i;				// ウィジェット識別用ユニークタグ
								$widgetContent = '<div id="' . $widgetTag . '" class="m3_widget' . $widgetStatusClass . '" rel="#m3editwidget" ' . $m3Option . $widgetStatusLabelOption . '>' . $widgetContent . '</div>';
							} else {
								$widgetTag = self::WIDGET_TAG_HEAD . $position . '_' . $i;				// ウィジェット識別用ユニークタグ
								$widgetContent = '<div id="' . $widgetTag . '">' . $widgetContent . '</div>';
							}
						} else {		// キャッシュデータがあるとき
							$widgetContent = $cacheData;
							
							// HTMLのメタタグを設定
							if (!empty($metaTitle)) $this->setHeadSubTitle($metaTitle);
							if (!empty($metaDesc)) $this->setHeadDescription($metaDesc);
							if (!empty($metaKeyword)) $this->setHeadKeywords($metaKeyword);
						}
						$contents .= $widgetContent;
						
						// ##### 外部出力用のCSSがある場合は追加 #####
						$exportCss = $this->pageDefRows[$i]['pd_css'];
						if (!empty($exportCss)){
							// ウィジェットのタグIDを変換
							$widgetTag = self::WIDGET_TAG_HEAD . $position . '_' . $i;				// ウィジェット識別用ユニークタグ
							$exportCss = str_replace(M3_TAG_START . M3_TAG_MACRO_WIDGET_CSS_ID . M3_TAG_END, $widgetTag, $exportCss);
							$this->addExportCss($exportCss);
						}
					}
				}
			
				if ($position == 'main'){		// メイン部のときは、メッセージを出力
					/*if (strlen($this->popupMsg) > 0){
						echo "\n<script language=\"javascript\">alert('" . addslashes($this->popupMsg) . "');</script>";
					}*/
				} else if ($position == 'debug'){		// デバッグ文出力
				} else {

				}
				break;
			case 1:		// ポジション表示
				$contents .= '<div style="background-color:#eee;margin:2px;padding:10px;border:3px solid #f00;color:#700;">ポジション名: ';
				$contents .= '<b>' . $position . '</b>';
				$contents .= '</div>';
				
				// ポジションを保存
				if (!in_array($position, $this->defPositions)) $this->defPositions[] = $position;	// テンプレート上のポジション
				break;
			case 2:		// ポジション表示(ウィジェット付き)
				$rev = '555';			// データのリビジョン番号
				// ポジションのHTMLタグIDを作成
				$num = 0;
				$posId = '';
				for ($i = 0; $i < 100; $i++){
					$posId = $position . '_' . $num;
					$viewPosId = self::POSITION_TAG_HEAD . $posId;
					if (!in_array($viewPosId, $this->viewPosId)) break;
					$num++;
				}
				$this->viewPosId[] = $viewPosId;// IDを保存
				
				// ページ定義を取得。同じポジションが続く場合は最初の一度だけ定義を取得
				if (empty($this->pageDefPosition) || $position != $this->pageDefPosition){		// ポジションが異なる場合
					$ret = $this->systemDb->getPageDef($filename, $subId, $position, $rows);
					if ($ret){	// 1行以上データが取得できたとき
						$this->pageDefRows = $rows;			// ページ定義レコード
						$this->pageDefPosition = $position;
					} else {
						$this->pageDefRows = array();
						$this->pageDefPosition = '';
					}
				}
				$posHead = '';
				// ナビゲーション型のポジションの場合はアイコン付加
				//if (strcasecmp($position, 'user3') == 0 || strcasecmp($position, 'position-1') == 0) $posHead = self::POS_HEAD_NAV_MENU;		// 特殊ポジションブロックのアイコン付加
				if ($style == self::WIDGET_STYLE_NAVMENU){
					$posHead = self::POS_HEAD_NAV_MENU;		// 特殊ポジションブロックのアイコン付加(ナビゲーションメニュー型)
				} else if (strcasecmp($position, 'main') == 0){
					$posHead = self::POS_HEAD_MAIN;		// 特殊ポジションブロックのアイコン付加(メイン型)
				}
				$contents .= '<div id="' . $viewPosId . '" class="m3_widgetpos_box" m3="pos:' . $position . ';rev:' . $rev . ';">' . M3_NL;		// リビジョン番号を付加
				$contents .= '<h2 class="m3_widgetpos_box_title">' . $posHead . $position . '</h2>' . M3_NL;
				
				// ウィジェットイメージを表示
				$widgetTagHead = self::WIDGET_TAG_HEAD . $posId;
				//$contents .= $this->getWidgetList($gEnvManager->getCurrentPageId(), $gEnvManager->getCurrentPageSubId(), $widgetTagHead, $this->pageDefRows);
				$contents .= $this->getWidgetList($filename, $subId, $widgetTagHead, $this->pageDefRows);

				$contents .= '</div>' . M3_NL;
				break;
			default:
				$contents .= '<div style="background-color:#eee;margin:2px;padding:10px;border:1px solid #f00;color:#700;">param error</div>';
				break;
		}
		// ポジションを保存
		$this->viewPositions[] = $position;

		return $contents;
	}
	/**
	 * 各部品のHTML出力(WordPressテンプレート専用)
	 *
	 * @param string $position			HTMLテンプレート上の書き出し位置
	 * @param bool $showTitle			タイトルを表示するかどうか
	 * @return string					出力コンテンツ
	 */
	function getWPContents($position, $showTitle = true)
	{
		if ($showTitle){
			return $this->getContents($position, self::WIDGET_STYLE_WORDPRESS/*WordPressテンプレート用出力*/, 100/*WordPressテンプレート*/);
		} else {
			return $this->getContents($position, self::WIDGET_STYLE_WORDPRESS_NOTITLE/*WordPressテンプレート(タイトルなし)用出力*/, 100/*WordPressテンプレート*/);
		}
	}
	/**
	 * その他のポジションブロックデータを取得
	 *
	 * @param string 		ポジション作成用タグ
	 */
	function getRestPositionData()
	{
		global $gEnvManager;
		
		$restPositionData = '';
		$rev = '888';
		$pageId = $gEnvManager->getCurrentPageId();
		$subId = $gEnvManager->getCurrentPageSubId();
		
		$restPositions = array_values(array_diff($this->defPositions/*全ポジション*/, $this->viewPositions/*表示済みポジション*/));
		$positionCount = count($restPositions);
		for ($i = 0; $i < $positionCount; $i++){
			$posHead = '';		// アイコン付加用
			$position = $restPositions[$i];
			$posId = $position . '_0';
			$viewPosId = self::POSITION_TAG_HEAD . $posId;

			// 画面情報を取得
			$ret = $this->systemDb->getPageDef($pageId, $subId, $position, $rows);
			if ($ret){
				//$pageDefRows = $rows;			// ページ定義レコード
						
				$restPositionData .= '<div id="' . $viewPosId . '" class="m3_widgetpos_box" m3="pos:' . $position . ';rev:' . $rev . ';">';		// リビジョン番号を付加
				$restPositionData .= '<h2 class="m3_widgetpos_box_title">' . $posHead . $position . '</h2>';
			
				// ウィジェットイメージを表示
				$widgetTagHead = self::WIDGET_TAG_HEAD . $posId;
				$contents = $this->getWidgetList($pageId, $subId, $widgetTagHead, $rows);
				$contents = str_replace(M3_NL, '', $contents);
				$contents = str_replace('\'', '\\\'', $contents);
				$restPositionData .= $contents;
				$restPositionData .= '</div>';
				
				$this->viewPosId[] = $viewPosId;// IDを保存
			}
		}
		return $restPositionData;
	}
	/**
	 * ウィジェット共通のコンテンツ追加処理
	 *
	 * @param string $src				ウィジェット出力
	 * @param array $pageDefParam		画面定義レコード
	 * @return string					コンテンツを付加したウィジェット出力
	 */
	function _addOptionContent($src, $pageDefParam)
	{
		// 前後コンテンツ追加
		$dest = $pageDefParam['pd_top_content'] . $src . $pageDefParam['pd_bottom_content'];
		
		// 「もっと読む」ボタンを追加
		if ($pageDefParam['pd_show_readmore']){
			$title = $pageDefParam['pd_readmore_title'];
			if (empty($title)) $title = self::DEFAULT_READMORE_TITLE;
			$dest .= '<div><a href="' . convertUrlToHtmlEntity($pageDefParam['pd_readmore_url']) . '">' . convertToHtmlEntity($title) . '</a></div>';
		}
		return $dest;
	}
	/**
	 * BODYタグに付加するスタイルを取得(管理画面用)
	 *
	 * @param string 		CSS文字列
	 */
	function getBodyStyle()
	{
		// 画面透過モードのときスタイルを追加
		$transCss = '';
		if ($this->isTransparentMode) $transCss = ' style="background-color:transparent;"';
		return $transCss;
	}
	/**
	 * 各ポジションのウィジェット数を取得
	 *
	 * @param string $position		HTMLテンプレート上の書き出し位置
	 * @return int   				ウィジェットの数
	 */
	function getWidgetsCount($position)
	{
		global $gRequestManager;
		global $gEnvManager;
		static $widgetCountArray = array();

		// 管理画面へのアクセスでログインされていないときは0を返す
		if ($gEnvManager->isAdminDirAccess() && !$gEnvManager->isSystemManageUser()){		// 管理画面へのアクセスのときでシステム運用権限がない場合
			return 0;
		}
				
		// 実行コマンドを取得
		$cmd = $gRequestManager->trimValueOf(M3_REQUEST_PARAM_OPERATION_COMMAND);
		if ($cmd == M3_REQUEST_CMD_SHOW_POSITION ||					// 管理画面(ウィジェットなしポジション表示)のとき
			$cmd == M3_REQUEST_CMD_SHOW_POSITION_WITH_WIDGET){		// 管理画面(ウィジェット付きポジション表示)のとき
			return 1;		// ウィジェットが設定されていないポジション名を表示するために固定で値を返す
		}
		
		// 単一ポジション以外の設定のときは固定で返す(or等)
		$pos = strpos($position, ' ');
		if ($pos !== false) return 1;
		
		// 既にウィジェット数が取得されている場合は保存値を返す
		//$widgetCount = $widgetCountArray[$position];
		//if (isset($widgetCount)) return $widgetCount;
		if (isset($widgetCountArray[$position])) return $widgetCountArray[$position];
		
		// ファイル名、ページ名を取得
		$filename	= $gEnvManager->getCurrentPageId();
		$subId		= $gEnvManager->getCurrentPageSubId();
		if (empty($subId)) $subId = $gEnvManager->getDefaultPageSubId();

		// 取得しようとするページ定義のポジションが既に取得しているポジションと異なるときはデータを取得
		if (empty($this->pageDefPosition) || $position != $this->pageDefPosition){		// 現在取得しているページ定義のポジション
			$ret = $this->systemDb->getPageDef($filename, $subId, $position, $rows, 0/*定義セットIdデフォルト*/, true/*表示ウィジェットのみ*/);
			if ($ret){	// 1行以上データが取得できたとき
				$this->pageDefRows = $rows;			// ページ定義レコード
				$this->pageDefPosition = $position;
			} else {
				$this->pageDefRows = array();
				$this->pageDefPosition = '';
			}
		}
		// ### ウィジェット数を取得 ###
		// テンプレートのカラム制御を行うためにウィジェット表示条件でウィジェット数を取得
		$widgetCount = 0;
		$rowCount = count($this->pageDefRows);
		for ($i = 0; $i < $rowCount; $i++){
	//		$condition = trim($this->pageDefRows[$i]['wd_visible_condition']);
			$condition = trim($this->pageDefRows[$i]['pd_visible_condition']);
			if (empty($condition)){			// ウィジェット表示条件が設定されていない場合はウィジェットを表示
				$widgetCount++;
			} else {			// ウィジェット表示条件が設定されている場合はパラメータをチェック
				$conds = explode(';', $condition);
				for ($j = 0; $j < count($conds); $j++){
					$pos = strpos($conds[$j], '=');
					if ($pos !== false){		// URLクエリーパラメータ条件がマッチした場合はウィジェットを表示
						list($key, $value) = explode('=', trim($conds[$j]));
						$key = trim($key);
						$value = trim($value);
						if (!empty($key) && !empty($value)){
							$requestValue = $gRequestManager->trimValueOf($key);
							if ($value == $requestValue) $widgetCount++;
						}
					}
				}
			}
		}
//		$widgetCount = count($this->pageDefRows);
		$widgetCountArray[$position] = $widgetCount;
		return $widgetCount;
	}
	/**
	 * ウィジェットのページ定義シリアル番号からウィジェットCSS IDを取得
	 *
	 * @param int $defSerial		ページ定義シリアル番号
	 * @param string $pageId		ページID
	 * @param string $subpage    	ページサブID
	 * @param string $position		表示位置ID
	 * @return string				CSSエレメントID
	 */
	function getWidgetCssId($defSerial, $pageId, $pageSubId, $position)
	{
		$elementId = '';
		$ret = $this->systemDb->getPageDef($pageId, $pageSubId, $position, $rows, 0/*定義セットIdデフォルト*/, true/*表示ウィジェットのみ*/);
		if ($ret){
			$rowCount = count($rows);
			for ($i = 0; $i < $rowCount; $i++){
				$row = $rows[$i];
				if ($row['pd_serial'] == $defSerial){
					$elementId = self::WIDGET_TAG_HEAD . $position . '_' . $i;				// ウィジェット識別用ユニークタグ
					break;
				}
			}
		}
		return $elementId;
	}
	/**
	 * ウィジェット情報取得
	 *
	 * 画面作成機能でウィジェット情報を取得するためのAjaxインターフェイス
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 */
	function getWidgetInfoByAjax($request)
	{
		global $gEnvManager;
		global $gDesignManager;
		
		// アクセスするページIDからPC用、携帯用、スマートフォン用かを判断
		$widgetDeviceType = 0;		// 端末タイプをPC用に初期化
		$pageId		= $request->trimValueOf(M3_REQUEST_PARAM_DEF_PAGE_ID);
		$pageSubId	= $request->trimValueOf(M3_REQUEST_PARAM_DEF_PAGE_SUB_ID);
		$mobilePageIdPrefix = M3_DIR_NAME_MOBILE . '_';
		$smartphonePageIdPrefix = M3_DIR_NAME_SMARTPHONE . '_';
		if (strncmp($pageId, $mobilePageIdPrefix, strlen($mobilePageIdPrefix)) == 0){
			$widgetDeviceType = 1;		// 携帯用
		} else if (strncmp($pageId, $smartphonePageIdPrefix, strlen($smartphonePageIdPrefix)) == 0){
			$widgetDeviceType = 2;		// スマートフォン用
		}

		$task = $request->trimValueOf(M3_REQUEST_PARAM_OPERATION_TASK);
		if ($task == 'list'){
			// ウィジェット一覧を取得
			$ret = $this->systemDb->getAvailableWidgetList($widgetDeviceType, $rows);
			if ($ret){
				$currentCategoryId = '_none';		// 現在のウィジェットカテゴリー初期化
				for ($i = 0; $i < count($rows); $i++){
					$widgetId = $rows[$i]['wd_id'];
					$desc = $rows[$i]['wd_description'];
					$widgetTag = self::WIDGET_TYPE_TAG_HEAD . $widgetId;
			//		$categoryId = $rows[$i]['wd_category_id'];
					$categoryId = $rows[$i]['pid'];		// 親カテゴリーID
					
					// カテゴリーの開始タグを追加
					if ($categoryId != $currentCategoryId){
						if ($i > 0){
							echo '</dd>' . M3_NL;
							echo '</dl>' . M3_NL;
						}
						echo '<dl class="m3accordion">' . M3_NL;
				//		echo '<dt>' . $rows[$i]['wt_name'] . '</dt>' . M3_NL;
						echo '<dt>' . $rows[$i]['pname'] . '</dt>' . M3_NL;			// 親カテゴリー名
						echo '<dd>' . M3_NL;
						
						// 現在のカテゴリー更新
						$currentCategoryId = $categoryId;
					}
					
					$imageTag = $gDesignManager->createWidgetIconTag($widgetId, self::WIDGET_ICON_IMG_SIZE);
					/*$image = $gDesignManager->getWidgetIconUrl($widgetId, self::WIDGET_ICON_IMG_SIZE);
					if (!empty($image) && $gEnvManager->getUseSslAdmin()){
						//$image = str_replace('http://', 'https://', $image);		// SSL通信の場合はSSL用に変換
						$image = $gEnvManager->getSslUrl($image);
					}
					$imageTag = '<img class="' . self::WIDTET_CLASS_NAME . '" src="' . $image . '" ';
					$imageTag .= 'width="' . self::WIDGET_ICON_IMG_SIZE . '"';
					$imageTag .= ' height="' . self::WIDGET_ICON_IMG_SIZE . '"';
					$imageTag .= ' />';*/
					
					// ウィジェット機能マーク
					$widgetMark = '';
					if ($rows[$i]['wd_edit_content'] && !empty($rows[$i]['wd_type'])) $widgetMark = self::WIDGET_MARK_MAIN;					// メイン型ウィジェット
					if ($rows[$i]['wd_type'] == 'menu' && $rows[$i]['wd_type_option'] == 'nav') $widgetMark = self::WIDGET_MARK_NAVMENU;		// ナビゲーションメニュー

					// ウィジェット機能一覧
					$functionMark = '';
					if (!empty($rows[$i]['wd_template_type'])){		// 対応テンプレートタイプ
						$templateTypeArray = explode(',', $rows[$i]['wd_template_type']);
						if (in_array('bootstrap', $templateTypeArray)) $functionMark .= self::WIDGET_FUNCTION_MARK_BOOTSTRAP;		// Bootstrap型テンプレート対応
						if (in_array('wordpress', $templateTypeArray)) $functionMark .= self::WIDGET_FUNCTION_MARK_WORDPRESS;		// WordPress型テンプレート対応
					}
					if (!empty($functionMark)) $functionMark = '<hr />' . $functionMark;
			
					echo '<dl class="m3_widgetlist_item" id="' . $widgetTag . '">' . M3_NL;
					echo '<dt>' . $widgetMark . $rows[$i]['wd_name'] . '</dt>' . M3_NL;			// ウィジェット名
					echo '<dd><table style="width:100%;"><tr style="vertical-align:top;"><td style="width:35px;">' . $imageTag . '</td><td>' . $desc . '</td></tr></table>';
					echo $functionMark . '</dd>' . M3_NL;
					echo '</dl>' . M3_NL;
					
					// カテゴリーの終了タグを追加
					if ($i == count($rows) - 1){
						echo '</dd>' . M3_NL;
						echo '</dl>' . M3_NL;
					}
				}
			}
		} else if ($task == 'wget' || $task == 'wdelete' || $task == 'wtoggle' || $task == 'wadd' || $task == 'wmove'){	// ウィジェット再取得、ウィジェット削除,ウィジェット共通属性変更、ウィジェット追加、ウィジェット移動のとき
			$rev	= $request->trimValueOf('rev');			// リビジョン
			$serial = $request->trimValueOf('serial');
			$position = $request->trimValueOf('pos');
			$widgetsStr = $request->trimValueOf('widgets');
			if (empty($widgetsStr)){
				$widgets = array();
			} else {
				$widgets = explode(',', $widgetsStr);
			}
			$shared = $request->trimValueOf('shared');
			$updatepos = explode(',', $request->trimValueOf('updatepos'));
			$index = $request->trimValueOf('index');
			
			// 処理ごとのパラメータ
			if ($task == 'wmove'){
				$positions = explode(',', $position);
				if (count($positions) >= 2){
					$position = $positions[0];
					$position2 = $positions[1];
				} else {
					$position = $positions[0];
					$position2 = '';
				}
			}
			// ##### エラーチェック #####
			$isErr = false;
			// リビジョンのエラーチェック
			$rev = '111';			// データのリビジョン番号

			// 変更前データ取得
			$ret = $this->systemDb->getPageDef($pageId, $pageSubId, $position, $rows);	// 0レコードでも正常とする
			
			// 変更前のウィジェットのシリアル番号をチェック
			if (count($widgets) == count($rows)){
				if (!($task == 'wmove' && empty($position2))){			// 同一ブロック内の移動の場合はチェックなし
					for ($i = 0; $i < count($rows); $i++){
						if ($widgets[$i] != $rows[$i]['pd_serial']){// シリアル番号
							$isErr = true;
							break;
						}
					}
				}
			} else {
				$isErr = true;
			}

			// データの更新
			if (!$isErr){		// エラーなしのとき
				if ($task == 'wdelete'){
					$ret = $this->systemDb->deleteWidget($serial);
				} else if ($task == 'wtoggle'){
					$newShared = 0;
					if (empty($shared)) $newShared = 1;
					$ret = $this->systemDb->toggleSharedWidget($pageId, $pageSubId, $serial, $newShared);
				} else if ($task == 'wadd'){	// ウィジェットの追加
					$widget = $request->trimValueOf('widget');
					
					// エラーチェック
					if (empty($widget)) $isErr = true;
					
					// ウィジェットを追加
					if (!$isErr) $this->systemDb->addWidget($pageId, $pageSubId, $position, $widget, $index);
				} else if ($task == 'wmove'){
					// ウィジェットを移動
					if (!$isErr) $this->systemDb->moveWidget($pageId, $pageSubId, $position, $serial, $index);
				}
			}
			// 再表示データ取得
			$ret = $this->systemDb->getPageDef($pageId, $pageSubId, $position, $rows);// 0レコードでも正常とする
			
			// 移動のときは、移動元と移動先の再表示データを取得
			if ($task == 'wmove' && !empty($position2)){
				$ret = $this->systemDb->getPageDef($pageId, $pageSubId, $position2, $rows2);// 0レコードでも正常とする
			}

			// ##### 該当するページのテンプレートタイプを取得 #####
			$templateType = 99;
			$templateGenerator = '';
		
			// ページ用個別に設定されたテンプレートがある場合は取得
			$curTemplate = '';
			$line = $this->getPageInfo($pageId, $pageSubId);
			if (!empty($line)) $curTemplate = $line['pn_template_id'];

			// 取得できなければデフォルトを取得
			if (empty($curTemplate)){
				if ($pageId == $this->gEnv->getDefaultPageId()){		// 通常サイトのとき
					$curTemplate = $this->gSystem->defaultTemplateId();
				} else if ($pageId == $this->gEnv->getDefaultSmartphonePageId()){		// スマートフォン用サイトのとき
					$curTemplate = $this->gSystem->defaultSmartphoneTemplateId();		// スマートフォン用デフォルトテンプレート
				}
			}
			
			// テンプレートのタイプを取得
			$ret = $this->systemDb->getTemplate($curTemplate, $row);
			if ($ret){
				$templateType = $row['tm_type'];
				$templateGenerator = $row['tm_generator'];
			}

			// 更新データを作成
			// 更新対象のポジションブロック
			echo '<div>' . M3_NL;
			for ($i = 0; $i < count($updatepos); $i++){
				// ウィジェットIDヘッダ作成
				$widgetTagHead = str_replace(self::POSITION_TAG_HEAD, self::WIDGET_TAG_HEAD, $updatepos[$i]);
					
				// ポジション名取得
				$posName = str_replace(self::POSITION_TAG_HEAD, '', substr($updatepos[$i], 0, strlen($updatepos[$i]) - strlen(strrchr($updatepos[$i], "_"))));
				if ($task == 'wmove' && $posName == $position2){
					// Themlerテンプレート場合は配置ポジション名が「hmenu」をナビゲーション型とする
					$style = '';
					if ($templateGenerator == M3_TEMPLATE_GENERATOR_THEMLER){
						if (strcasecmp($position2, 'hmenu') == 0){		// Joomla!v3テンプレート対応
							$style = self::WIDGET_STYLE_NAVMENU;		// デフォルトはナビゲーション型
						}
					} else {
						if (strcasecmp($position2, 'user3') == 0 ||		// ナビゲーションメニュー位置の場合
							strcasecmp($position2, 'position-1') == 0){		// Joomla!v2.5テンプレート対応
							$style = self::WIDGET_STYLE_NAVMENU;		// デフォルトはナビゲーション型
						}
					}
									
					// ウィジェット一覧外枠
					$posHead = '';
					if ($style == self::WIDGET_STYLE_NAVMENU){
						$posHead = self::POS_HEAD_NAV_MENU;		// 特殊ポジションブロックのアイコン付加(ナビゲーションメニュー型)
					} else if (strcasecmp($position2, 'main') == 0){
						$posHead = self::POS_HEAD_MAIN;		// 特殊ポジションブロックのアイコン付加(メイン型)
					}
					//if (strcasecmp($position2, 'user3') == 0 || strcasecmp($position2, 'position-1') == 0) $posHead = self::POS_HEAD_NAV_MENU;		// 特殊ポジションブロックのアイコン付加
					echo '<div id="' . $updatepos[$i] . '" class="m3_widgetpos_box" m3="pos:' . $position2 . ';rev:' . $rev . ';">' . M3_NL;		// リビジョン番号を付加
					echo '<h2 class="m3_widgetpos_box_title">' . $posHead . $position2 . '</h2>' . M3_NL;
				
					// ウィジェット一覧出力
					echo $this->getWidgetList($pageId, $pageSubId, $widgetTagHead, $rows2);
				} else {
					// Themlerテンプレート場合は配置ポジション名が「hmenu」をナビゲーション型とする
					$style = '';
					if ($templateGenerator == M3_TEMPLATE_GENERATOR_THEMLER){
						if (strcasecmp($position, 'hmenu') == 0){		// Joomla!v3テンプレート対応
							$style = self::WIDGET_STYLE_NAVMENU;		// デフォルトはナビゲーション型
						}
					} else {
						if (strcasecmp($position, 'user3') == 0 ||		// ナビゲーションメニュー位置の場合
							strcasecmp($position, 'position-1') == 0){		// Joomla!v2.5テンプレート対応
							$style = self::WIDGET_STYLE_NAVMENU;		// デフォルトはナビゲーション型
						}
					}
					
					// ウィジェット一覧外枠
					$posHead = '';
					if ($style == self::WIDGET_STYLE_NAVMENU){
						$posHead = self::POS_HEAD_NAV_MENU;		// 特殊ポジションブロックのアイコン付加(ナビゲーションメニュー型)
					} else if (strcasecmp($position, 'main') == 0){
						$posHead = self::POS_HEAD_MAIN;		// 特殊ポジションブロックのアイコン付加(メイン型)
					}
					//if (strcasecmp($position, 'user3') == 0 || strcasecmp($position, 'position-1') == 0) $posHead = self::POS_HEAD_NAV_MENU;		// 特殊ポジションブロックのアイコン付加
					echo '<div id="' . $updatepos[$i] . '" class="m3_widgetpos_box" m3="pos:' . $position . ';rev:' . $rev . ';">' . M3_NL;		// リビジョン番号を付加
					echo '<h2 class="m3_widgetpos_box_title">' . $posHead . $position . '</h2>' . M3_NL;
				
					// ウィジェット一覧出力
					echo $this->getWidgetList($pageId, $pageSubId, $widgetTagHead, $rows);
				}
				// ウィジェット一覧外枠
				echo '</div>' . M3_NL;
			}
			echo '</div>' . M3_NL;
		}
	}
	/**
	 * 画面作成用ウィジェット一覧データ出力
	 *
	 * @param string $pageId			ページID
	 * @param string $pageSubId			ページサブID
	 * @param string $widgetTagHead		HTMLウィジェットID用のヘッダ
	 * @param array $rows				ウィジェット一覧データ
	 * @return string					ウィジェット一覧出力
	 */
	function getWidgetList($pageId, $pageSubId, $widgetTagHead, $rows)
	{
		global $gEnvManager;
		global $gDesignManager;
		global $gSystemManager;
		
		if ($gEnvManager->getUseSslAdmin()){
			$rootUrl = $gEnvManager->getSslRootUrl();
		} else {
			$rootUrl = $gEnvManager->getRootUrl();
		}

		// ページのコンテンツタイプを取得
		$line = $this->getPageInfo($pageId, $pageSubId);
		if (!empty($line)) $pageContentType = $line['pn_content_type'];
			
		$contents = '';		// ウィジェット一覧出力コンテンツ
		for ($i = 0; $i < count($rows); $i++){
			$widgetId = $rows[$i]['wd_id'];
			$desc = $rows[$i]['wd_description'];
			$widgetIndex = $rows[$i]['pd_index'];		// 表示順
			$configId = $rows[$i]['pd_config_id'];		// 定義ID
			$serial = $rows[$i]['pd_serial'];		// シリアル番号
			$isShared = empty($rows[$i]['pd_sub_id']);			// グローバル属性が設定されているかどうか
			$widgetTag = $widgetTagHead . '_' . $i;				// ウィジェット識別用ユニークタグ
			
			$imageTag = $gDesignManager->createWidgetIconTag($widgetId, self::WIDGET_ICON_IMG_SIZE);
			/*$image = $gDesignManager->getWidgetIconUrl($widgetId, self::WIDGET_ICON_IMG_SIZE);
			if (!empty($image) && $gEnvManager->getUseSslAdmin()){
				//$image = str_replace('http://', 'https://', $image);		// SSL通信の場合はSSL用に変換
				$image = $gEnvManager->getSslUrl($image);
			}
			$imageTag = '<img class="' . self::WIDTET_CLASS_NAME . '" src="' . $image . '" ';
			$imageTag .= 'width="' . self::WIDGET_ICON_IMG_SIZE . '"';
			$imageTag .= ' height="' . self::WIDGET_ICON_IMG_SIZE . '"';
			$imageTag .= ' />';*/
			
			// 定義名
			if (empty($rows[$i]['pd_config_name'])){
				if ($rows[$i]['wd_use_instance_def'] && $configId == 0){		// インスタンス定義が必要で未定義のとき
					$configName = '<span style="color:red;">[未設定]</span>';
				} else {
					$configName = '';
				}
			} else {
				$configName = '[' . $rows[$i]['pd_config_name'] . ']';
			}
			$hasAdmin = '0';		// 管理画面があるかどうか
			$configImg = '';	// 設定アイコンの表示
			
			// ウィジェットの配置位置に問題があるかどうかを表示
			// メインコンテンツとページ属性が一致するかどうかチェック
			$widgetContentType = $rows[$i]['wd_content_type'];
			//if (!empty($widgetContentType) && $widgetContentType != $pageContentType && 
			if (!empty($widgetContentType) && $widgetContentType != $pageContentType && empty($rows[$i]['wd_content_widget_id']) &&			// 編集用のウィジェットが別にある場合は除く(2015/3/24))
						(in_array($widgetContentType, $this->_getAllContentType()) || in_array($widgetContentType, $this->_getAllFeatureType()))){
				//$title = 'ウィジェット配置注意';
				$title = 'ページ属性と不一致';
				$configImg .= '<span rel="m3help" data-container="body" title="' . $title . '"><img src="' . $rootUrl . self::NOTICE_ICON_FILE . '" alt="' . $title . '" /></span>&nbsp;';
			}
			if ($rows[$i]['wd_edit_content'] && !empty($rows[$i]['wd_type']) && $isShared){					// メイン型ウィジェットがグローバル属性を持つとき
				$title = 'メイン型ウィジェットはグローバル属性を設定できません';
				$configImg .= '<span rel="m3help" data-container="body" title="' . $title . '"><img src="' . $rootUrl . self::ALERT_ICON_FILE . '" alt="' . $title . '" /></span>&nbsp;';
			}
			if ($rows[$i]['wd_has_admin']){
				$hasAdmin = '1';
				$title = 'ウィジェットの設定';
				$configImg .= '<a href="javascript:void(0);" onclick="m3ShowConfigWindow(\'' . $widgetId . '\', \'' . $configId . '\', \'' . $serial . '\');return false;" rel="m3help" data-container="body" title="' . $title . '">' .
								'<i class="fas fa-cog" style="color:black;"></i></a>&nbsp;';
								//'<img src="' . $rootUrl . self::CONFIG_ICON_FILE . '" alt="' . $title . '"/></a>&nbsp;';
			}
			// 表示順
			$configImg .= '<span rel="m3help" data-container="body" title="表示順">' . $widgetIndex . '</span>';
			
			$shared = '0';		// 共通属性があるかどうか
			$sharedClassName = '';
			//if (empty($rows[$i]['pd_sub_id'])){
			if ($isShared){					// グローバル属性ウィジェットのとき
				$shared = '1';
				$sharedClassName = 'm3_widget_shared';			// 共通ウィジェットのクラス
			}
			$m3Option = 'm3="widgetid:' . $widgetId . '; serial:' . $serial . '; configid:' . $configId . '; useconfig:' . $hasAdmin . '; shared:' . $shared . '"';
			
			// ウィジェット機能マーク
			$widgetMark = '';
			if ($rows[$i]['wd_edit_content'] && !empty($rows[$i]['wd_type'])) $widgetMark = self::WIDGET_MARK_MAIN;					// メイン型ウィジェット
			if ($rows[$i]['wd_type'] == 'menu' && $rows[$i]['wd_type_option'] == 'nav') $widgetMark = self::WIDGET_MARK_NAVMENU;		// ナビゲーションメニュー
			
			// 操作メニュー
			$dropdownMenuId = $widgetTag . '_dropdown';
			$operationMenu = '<div class="m3widgetdropdown">';
//			$operationMenu .= '<a class="m3widgetdropdownbutton" data-dropdown="#' . $dropdownMenuId . '" href="#"><i class="glyphicon glyphicon-list-alt"></i> <b class="caret"></b></a>';
			$operationMenu .= '<a class="m3widgetdropdownbutton" data-dropdown="#' . $dropdownMenuId . '" href="#" data-horizontal-offset="4"><b class="caret"></b></a>';
			$operationMenu .= '<div id="' . $dropdownMenuId . '" class="m3dropdown m3dropdown-tip m3dropdown-relative m3dropdown-anchor-right">';
			$operationMenu .= '<ul class="m3dropdown-menu">';
			//if ($hasAdmin) $operationMenu .= '<li class="m3_wconfig"><a href="#"><img src="' . $rootUrl . self::CONFIG_ICON_FILE . '" /> <span>ウィジェットの設定</span></a></li>';
			if ($hasAdmin) $operationMenu .= '<li class="m3_wconfig"><a href="#"><i class="fas fa-cog" style="color:black;"></i> <span>ウィジェットの設定</span></a></li>';
			//$operationMenu .= '<li class="m3_wadjust"><a href="#"><img src="' . $rootUrl . self::ADJUST_ICON_FILE . '" /> <span>タイトル・スタイル調整</span></a></li>';
			$operationMenu .= '<li class="m3_wadjust"><a href="#"><i class="fas fa-window-maximize" style="color:black;"></i> <span>タイトル・スタイル調整</span></a></li>';
			if ($shared){
				//$operationMenu .= '<li class="m3_wshared"><a href="#"><img src="' . $rootUrl . self::SHARED_ICON_FILE . '" /> <span>グローバル属性を解除</span></a></li>';
				$operationMenu .= '<li class="m3_wshared"><a href="#"><i class="fas fa-newspaper" style="color:black;"></i> <span>グローバル属性を解除</span></a></li>';
			} else {
				//$operationMenu .= '<li class="m3_wshared"><a href="#"><img src="' . $rootUrl . self::SHARED_ICON_FILE . '" /> <span>グローバル属性を設定</span></a></li>';
				$operationMenu .= '<li class="m3_wshared"><a href="#"><i class="fas fa-newspaper" style="color:black;"></i> <span>グローバル属性を設定</span></a></li>';
			}
			//$operationMenu .= '<li class="m3_wdelete"><a href="#"><img src="' . $rootUrl . self::DELETE_ICON_FILE . '" /> <span>このウィジェットを削除</span></a></li>';
			$operationMenu .= '<li class="m3_wdelete"><a href="#"><i class="fas fa-times-circle text-danger"></i> <span>このウィジェットを削除</span></a></li>';
			$operationMenu .= '</ul>';
			$operationMenu .= '</div>';
			$operationMenu .= '</div>';
			
			$contents .= '<dl class="m3_widget m3_widget_sortable" id="' . $widgetTag . '" ' . $m3Option . ' >' . M3_NL;
			$contents .= '<dt class="m3_widget_with_check_box ' . $sharedClassName . '"><div class="m3widgettitle">' . $widgetMark . $rows[$i]['wd_name'] . '</div>' . $operationMenu . '</dt>' . M3_NL;
			$contents .= '<dd><table style="width:100%;"><tr style="vertical-align:top;"><td style="width:35px;border:none;">' . $imageTag . '</td><td style="border:none;">' . $desc . '</td></tr></table><hr />' . M3_NL;
			$contents .= '<table style="width:100%;"><tr><td style="border:none;">' . $configName . '</td><td style="text-align:right;border:none;">' . $configImg . '</td></tr></table></dd>' . M3_NL;
			$contents .= '</dl>' . M3_NL;
		}
		return $contents;
	}
	/**
	 * ウィジェット出力を処理
	 *
	 * @param string $position			HTMLテンプレート上の書き出し位置
	 * @param int $index				行番号
	 * @param array $fetchedRow			fetch取得した行
	 * @param string $style				ウィジェットの表示スタイル(空の場合=Joomla!v1.0テンプレート用、空以外=Joomla!v1.5テンプレート用)
	 * @param string $launchWidgetTag	遅延ウィジェット埋め込み用タグ(遅延ウィジェットの場合のみ作成)
	 * @param string $titleTag    		タイトル埋め込み用タグ(遅延ウィジェットの場合のみ作成)
	 * @param int $widgetHeaderType		ウィジェットタイトルを出力方法(0=出力なし、1=Joomla!v1.0PC用出力、2=Joomla!v1.0携帯用出力)
	 * @return bool						処理を継続するかどうか(true=続行、false=中断)
	 */
	function pageDefLoop($position, $index, $fetchedRow, $style, &$launchWidgetTag, &$titleTag, $widgetHeaderType = 0)
	{
		global $gEnvManager;
		global $gErrorManager;
		global $gDesignManager;

		// ページ作成中断またはウィジェット処理中断のときは終了
		if ($this->isAbort || $this->isWidgetAbort) return false;

		// ウィジェット実行停止中のときは空で返す
		// 管理者、運営者どのレベルで制限をかける?
		if (!$fetchedRow['wd_active']) return true;
		
		// グローバル属性ありの場合は現在のページが例外ページにないかチェック
		if (empty($fetchedRow['pd_sub_id'])){
			$exceptPageStr = $fetchedRow['pd_except_sub_id'];
			if (!empty($exceptPageStr)){
				$exceptPageArray = explode(',', $exceptPageStr);
				if (in_array($gEnvManager->getCurrentPageSubId(), $exceptPageArray)) return true;		// 例外ページの場合は出力しない
			}
		}
		
		// ウィジェット表示タイプによる表示制御。システム管理者以上の場合は常時表示。
		if (!$gEnvManager->isSystemAdmin()){
			if (!empty($fetchedRow['pd_view_control_type'])){
				switch ($fetchedRow['pd_view_control_type']){
					case 1:			// ログイン時のみ表示
						if (!$gEnvManager->isCurrentUserLogined()) return true;		// ログインしていなければ終了
						break;
					case 2:			// 非ログイン時のみ表示
						if ($gEnvManager->isCurrentUserLogined()) return true;		// ログインしていれば終了
						break;
					default:
						break;
				}
			}
			if (!empty($fetchedRow['pd_view_page_state'])){
				switch ($fetchedRow['pd_view_page_state']){
					case 1:			// トップ時のみ表示
						if (!$this->isPageTopUrl) return true;		// ページトップ(サブページ内のトップ位置)でなければ終了
						break;
					default:
						break;
				}
			}
		}
		
		// パラメータ初期化
		$launchWidgetTag = '';		// 遅延ウィジェット埋め込み用タグ
		$titleTag = '';
		
		// ウィジェットID取得
		$widgetId = $fetchedRow['wd_id'];		
		$widgetIndexFile = $gEnvManager->getWidgetsPath() . '/' . $widgetId . '/index.php';

		// その他パラメータ取得
		$configId = $fetchedRow['pd_config_id'];		// ウィジェット定義ID
		if ($configId == 0) $configId = '';
		$prefix = $fetchedRow['wd_suffix'];		// サフィックス文字列
		$title = $fetchedRow['wd_name'];		// デフォルトのウィジェットタイトル
		if (empty($title)) $title = $fetchedRow['wd_id'];
		$widgetHeadTitle = $fetchedRow['pd_title'];		// ウィジェットのヘッドタイトル
		if (empty($widgetHeadTitle)) $widgetHeadTitle = $title;			// ウィジェットのヘッドタイトルが空の場合はウィジェットタイトルを取得
		$serial = $fetchedRow['pd_serial'];		// シリアル番号
		$cssStyle	= $fetchedRow['pd_style'];		// CSS
		$lateLaunchIndex = $fetchedRow['wd_launch_index'];		// 遅延実行インデックス
		$shared = false;
		if (empty($fetchedRow['pd_sub_id'])) $shared = true;// 共通属性あり
		if ($this->nonSharedWidgetCount == -1) $this->nonSharedWidgetCount = 0;		// 初期化されていない場合は初期化
		if (!$shared) $this->nonSharedWidgetCount++;	// 非共通ウィジェットの数を更新
		
		// ウィジェットが遅延実行に指定されている場合は、ウィジェットのタグのみ埋め込む
		if ($lateLaunchIndex > 0){		// 遅延実行のとき
			// 遅延実行ウィジェットリストに追加
			if (!isset($this->lateLaunchWidgetList[$widgetId])){
				$this->lateLaunchWidgetList[$widgetId] = (int)$lateLaunchIndex;
			}
			// 実行パラメータ保存
			$maxNo = 0;		// 最大シリアル番号
			$count = count($this->latelaunchWidgetParam);
			for ($i = 0; $i < $count; $i++){
				list($wId, $maxNo, $tmp1, $tmp2, $tmp3, $tmp4, $tmp5, $tmp6, $tmp7, $tmp8, $tmp9, $tmp10, $tmp11) = $this->latelaunchWidgetParam[$i];
				if ($wId == $widgetId) $maxNo = $maxNo + 1;
			}
			// Joomla!1.0テンプレートの場合はタイトルを修正
			if ($widgetHeaderType > 0 && empty($style)){			// Joomla!1.0テンプレートのとき
				if (!empty($fetchedRow['pd_title'])) $title = $fetchedRow['pd_title'];
			}
//			$this->latelaunchWidgetParam[] = array($widgetId, $maxNo, $configId, $prefix, $serial, $style, $cssStyle, $title, $shared, $exportCss, $position, $index, $fetchedRow);
			$this->latelaunchWidgetParam[] = array($widgetId, $maxNo, $configId, $prefix, $serial, $style, $cssStyle, $title, $shared, ''/* exportCss */, $position, $index, $fetchedRow);
			
			// 遅延実行用タグを埋め込む
			$launchWidgetTag = self::WIDGET_ID_TAG_START . $widgetId . self::WIDGET_ID_SEPARATOR . $maxNo . self::WIDGET_ID_TAG_END;		// 遅延ウィジェット埋め込み用タグ
			echo $launchWidgetTag;
			
			// タイトル用タグ作成
			$titleTag = self::WIDGET_ID_TITLE_TAG_START . $widgetId . self::WIDGET_ID_SEPARATOR . $maxNo . self::WIDGET_ID_TITLE_TAG_END;
		} else {
			// ウィジェットが存在する場合は実行
			if (!file_exists($widgetIndexFile)) {
				if ($gEnvManager->isSystemManageUser()) echo '<span class="error">widget not found error: ' . $widgetId . '</span>';		// システム運用者の場合はエラーメッセージ表示
			} else {
				// パラメータ初期化
				$this->lastHeadCss = '';			// 最後に設定したHTMLヘッダにCSS出力する文字列
				$this->lastHeadScript = '';			// 最後に設定したHTMLヘッダにJavascript出力する文字列
				$this->lastHeadString = '';			// 最後に設定したHTMLヘッダに出力する任意文字列
				$this->lastHeadTitle = '';			// ヘッダ部titleにセットした文字列
				$this->lastHeadDescription = '';	// ヘッダ部descriptionにセットした文字列
				$this->lastHeadKeywords = '';		// ヘッダ部keywordsにセットした文字列
			
				// 現在のウィジェットのポジション
				$this->currentWidgetPosition = $position;			// 現在のウィジェットのポジション
				$this->currentWidgetIndex = $index;			// 現在のウィジェットのポジション番号
					
				// Joomla!1.0テンプレートのときはウィジェットタイトルを出力
				$joomlaTitleVisble = false;
				if ($widgetHeaderType > 0 && empty($style)){			// Joomla!1.0テンプレートのとき
					if ($fetchedRow['pd_title_visible']){
						if ($widgetHeaderType == 1){		// PC用出力のとき
							$joomlaTitle = $fetchedRow['pd_title'];
							if (empty($joomlaTitle)) $joomlaTitle = $title;
							echo '<table ' . self::JOOMLA10_DEFAULT_WIDGET_MENU_PARAM . '>' . M3_NL;
							echo '<tr><th>' . $joomlaTitle . '</th></tr>' . M3_NL;
							echo '<tr><td>' . M3_NL;
							$joomlaTitleVisble = true;
						} else if ($widgetHeaderType == 2){		// 携帯用出力のとき
							$joomlaTitle = $fetchedRow['pd_title'];
							if (empty($joomlaTitle)) $joomlaTitle = $title;
							echo '<div>' . $joomlaTitle . '</div>' . M3_NL;
							$joomlaTitleVisble = true;
						}
					}
				}
				// ウィジェットの外枠タグを設定
				//echo '<div class="' . self::WIDGET_OUTER_CLASS_WIDGET_TAG . $widgetId . '">' . M3_NL;
				// ウィジェット親のCSS定義があるときは、タグを追加
				if (!empty($cssStyle)) echo '<div style="' . $cssStyle . '">' . M3_NL;
					
				// ウィジェットの前出力
				echo $gDesignManager->getAdditionalWidgetOutput(true);
				
				// 作業中のウィジェットIDを設定
				$gEnvManager->setCurrentWidgetId($widgetId);
	
				// ウィジェット定義IDを設定
				$gEnvManager->setCurrentWidgetConfigId($configId);
			
				// ページ定義のシリアル番号を設定
				$gEnvManager->setCurrentPageDefSerial($fetchedRow['pd_serial']);
				
				// ページ定義レコードを設定
				$gEnvManager->setCurrentPageDefRec($fetchedRow);
				
				// パラメータを設定
				$gEnvManager->setCurrentWidgetPrefix($prefix);		// プレフィックス文字列
			
				// ウィジェットのヘッドタイトルを設定
				$gEnvManager->setCurrentWidgetHeadTitle($widgetHeadTitle);

				// ウィジェットのタイトルを設定
				$gEnvManager->setCurrentWidgetTitle($title);
				
				// ウィジェットのスタイルを設定
				$gEnvManager->setCurrentWidgetStyle($style);
				
				// ウィジェットのグローバル属性状況を設定
				$gEnvManager->setIsCurrentWidgetShared($shared);
					
				// 実行ログを残す
			$this->systemDb->writeWidgetLog($widgetId, 0/*ページ実行*/);
				
				// ウィジェットを実行
				//require_once($widgetIndexFile);
				// ウィジェットの呼び出しは、複数回存在する可能性があるのでrequire_once()で呼び出さない
				$msg = 'widget-start(' . $widgetId . ')';
				$gErrorManager->writeDebug(__METHOD__, $msg);		// 時間計測用
				require($widgetIndexFile);
				$msg = 'widget-end(' . $widgetId . ')';
				$gErrorManager->writeDebug(__METHOD__, $msg);		// 時間計測用

				// 作業中のウィジェットIDを解除
				$gEnvManager->setCurrentWidgetId('');
			
				// ウィジェット定義IDを解除
				$gEnvManager->setCurrentWidgetConfigId('');
				
				// ページ定義のシリアル番号を解除
				$gEnvManager->setCurrentPageDefSerial(0);
			
				// ページ定義レコードを解除
				$gEnvManager->setCurrentPageDefRec();
				
				// パラメータを解除
				$gEnvManager->setCurrentWidgetPrefix('');				// プレフィックス文字列

				// ウィジェットのスタイルを解除
				$gEnvManager->setCurrentWidgetStyle('');
				
				// ウィジェットのグローバル属性状況を解除
				$gEnvManager->setIsCurrentWidgetShared(false);
				
				// ウィジェットの後出力
				echo $gDesignManager->getAdditionalWidgetOutput(false);
//				echo "<!-- ".time()." -->";

				// ウィジェット親のCSS定義があるときは、タグを追加
				if (!empty($cssStyle)) echo '</div>' . M3_NL;
				// ウィジェットの外枠タグを設定
				//echo '</div>' . M3_NL;
				
				// Joomla!1.0テンプレートのときはタイトルを出力
				if ($joomlaTitleVisble && $widgetHeaderType == 1){		// PC用出力のとき
					echo '</td></tr>' . M3_NL;
					echo '</table>' . M3_NL;
				}
			}
		}
		return true;		// 処理継続
	}
	/**
	 * 定義IDを指定してウィジェットの出力を取得
	 *
	 * @param string $widgetId		ウィジェットID
	 * @param int $configId			ウィジェット定義ID
	 * @return string				ウィジェットの出力
	 */
	function getWidgetOutput($widgetId, $configId = 0)
	{
		global $gEnvManager;
		global $gErrorManager;

		// ウィジェットのアクセス権をチェック
		if (!$this->systemDb->canAccessWidget($widgetId)) return '';

		// ウィジェット情報取得
		if (!$this->systemDb->getWidgetInfo($widgetId, $row)) return '';

		// 端末タイプをチェック
		if (intval($row['wd_device_type']) != intval($gEnvManager->getCurrentPageDeviceType())) return '';
		
		// 必要なJavascriptライブラリを追加
		$scritLib = trim($row['wd_add_script_lib']);
		if (!empty($scritLib)) $this->addScript('', $scritLib);
		
		// バッファ作成
		ob_start();

		// ウィジェット実行ファイル取得
		$widgetIndexFile = $gEnvManager->getWidgetsPath() . '/' . $widgetId . '/index.php';
					
		// 作業中のウィジェットIDを設定
		$saveWidgetId = $gEnvManager->getCurrentWidgetId();
		$gEnvManager->setCurrentWidgetId($widgetId);

		// ウィジェット定義IDを設定
		$saveConfigId = $gEnvManager->getCurrentWidgetConfigId();
		$gEnvManager->setCurrentWidgetConfigId($configId);
	
		// ページ定義のシリアル番号を設定
		//$gEnvManager->setCurrentPageDefSerial(0);		// 未使用?
			
		// パラメータを設定
		//$gEnvManager->setCurrentWidgetPrefix($prefix);		// プレフィックス文字列
	
		// ウィジェットのタイトルを設定
		//$gEnvManager->setCurrentWidgetTitle($title);
		
		// ウィジェットのグローバル属性状況を設定
		//$gEnvManager->setIsCurrentWidgetShared($shared);
			
		// 実行ログを残す
	$this->systemDb->writeWidgetLog($widgetId, 0/*ページ実行*/);
		
		// ウィジェットを実行
		//require_once($widgetIndexFile);
		// ウィジェットの呼び出しは、複数回存在する可能性があるのでrequire_once()で呼び出さない
		$msg = 'widget-start(' . $widgetId . ')';
		$gErrorManager->writeDebug(__METHOD__, $msg);		// 時間計測用
		if (file_exists($widgetIndexFile)){
			require($widgetIndexFile);
		} else {
			if ($gEnvManager->isSystemManageUser()) echo '<span class="error">widget not found error: ' . $widgetId . '</span>';		// システム運用者の場合はエラーメッセージ表示
		}
		$msg = 'widget-end(' . $widgetId . ')';
		$gErrorManager->writeDebug(__METHOD__, $msg);		// 時間計測用

		// 作業中のウィジェットIDを戻す
		$gEnvManager->setCurrentWidgetId($saveWidgetId);
	
		// ウィジェット定義IDを戻す
		$gEnvManager->setCurrentWidgetConfigId($saveConfigId);
		
		// ページ定義のシリアル番号を解除
		//$gEnvManager->setCurrentPageDefSerial(0);		// 未使用?
	
		// パラメータを解除
		//$gEnvManager->setCurrentWidgetPrefix('');				// プレフィックス文字列

		// ウィジェットのグローバル属性状況を解除
		//$gEnvManager->setIsCurrentWidgetShared(false);

		// 現在のバッファ内容を取得し、バッファを破棄
		$output = ob_get_contents();
		ob_end_clean();
					
		return $output;
	}
	/**
	 * インナーウィジェットの操作
	 *
	 * @param int $cmdNo				実行コマンド(0=初期化、1=更新、2=計算、10=コンテンツ取得)
	 * @param string $id				ウィジェットID+インナーウィジェットID
	 * @param string $configId			インナーウィジェット定義ID
	 * @param object $paramObj			インナーウィジェットに渡すパラメータオブジェクト
	 * @param object $optionParamObj	インナーウィジェットに渡すパラメータオブジェクト(オプション)
	 * @param object $resultObj			インナーウィジェットから返る結果オブジェクト
	 * @param string $content			出力内容
	 * @param bool $isAdmin				管理者機能(adminディレクトリ以下)かどうか
	 * @return bool						true=成功、false=失敗
	 */
	function commandIWidget($cmdNo, $id, $configId, &$paramObj, &$optionParamObj, &$resultObj, &$content, $isAdmin = false)
	{
		global $gEnvManager;
		
		// ウィジェットIDとインナーウィジェットIDを取り出す
		list($widgetId, $iWidgetId) = explode(M3_WIDGET_ID_SEPARATOR, $id);

		// インナーウィジェットに渡すパラメータを設定
		switch ($cmdNo){
			case 0:
				$cmd = self::IWIDTET_CMD_INIT;			// 初期化
				$this->gInstance->getCmdParamManager()->setParam($id . M3_WIDGET_ID_SEPARATOR . $configId, $cmd, $paramObj, $optionParamObj);
				break;
			case 1:
				$cmd = self::IWIDTET_CMD_UPDATE;		// 更新
				$this->gInstance->getCmdParamManager()->setParam($id . M3_WIDGET_ID_SEPARATOR . $configId, $cmd);		// 設定されているパラメータの更新は行わない
				break;
			case 2:
				$cmd = self::IWIDTET_CMD_CALC;			// 計算
				$this->gInstance->getCmdParamManager()->setParam($id . M3_WIDGET_ID_SEPARATOR . $configId, $cmd, $paramObj, $optionParamObj);
				break;
			case 10:
				$cmd = self::IWIDTET_CMD_CONTENT;			// コンテンツ取得
				$this->gInstance->getCmdParamManager()->setParam($id . M3_WIDGET_ID_SEPARATOR . $configId, $cmd);		// 設定されているパラメータの更新は行わない
				break;
			default:
				$cmd = '';
				$this->gInstance->getCmdParamManager()->setParam($id . M3_WIDGET_ID_SEPARATOR . $configId, $cmd, $paramObj, $optionParamObj);
				break;
		}

		// インナーウィジェットのエラー出力は呼び出しウィジェット側で受けるため、出力を抑止する
		ob_start();// バッファ作成

		// ウィジェット実行ファイル取得
		if (empty($widgetId)){		// ウィジェットIDが指定されていないときは共通ディレクトリ
		//$gEnvManager->getIWidgetsPath() . '/' . $iWidgetId . '/' . $containerClass . '.php';
		} else {
			if ($isAdmin){
				$widgetIndexFile = $gEnvManager->getWidgetsPath() . '/' . $widgetId . '/include/iwidgets/' . $iWidgetId . '/' . M3_DIR_NAME_ADMIN . '/index.php';
			} else {
				$widgetIndexFile = $gEnvManager->getWidgetsPath() . '/' . $widgetId . '/include/iwidgets/' . $iWidgetId . '/index.php';
			}
		}

		// 作業中のインナーウィジェットIDを設定
		$gEnvManager->setCurrentIWidgetId($id);		// インナーウィジェットを設定

		// インナーウィジェット定義IDを設定
		$gEnvManager->setCurrentIWidgetConfigId($configId);
		
		// ウィジェットを実行
		// ウィジェットの呼び出しは、複数回存在する可能性があるのでrequire_once()で呼び出さない
		if (file_exists($widgetIndexFile)){
			require($widgetIndexFile);
		} else {
			echo 'file not found error: ' . $widgetIndexFile;
		}
		
		// 作業中のウィジェットIDを解除
		$gEnvManager->setCurrentIWidgetId('');
	
		// インナーウィジェット定義IDを解除
		$gEnvManager->setCurrentIWidgetConfigId('');
		
		// 現在のバッファ内容を取得し、バッファを破棄
		$content = ob_get_contents();
		ob_end_clean();
		
		// 値を再取得
		if ($this->gInstance->getCmdParamManager()->getParam($id . M3_WIDGET_ID_SEPARATOR . $configId, $cmd, $obj, $option)){
			$paramObj = $obj;
			$optionParamObj = $option;
		}

		// 結果を取得
		if ($this->gInstance->getCmdParamManager()->getResult($id . M3_WIDGET_ID_SEPARATOR . $configId, $resObj)) $resultObj = $resObj;
		return true;
	}
	/**
	 * コールバックメソッドを実行し、出力を返す
	 *
	 * @param array $callback		実行するコールバック
	 * @return string				出力結果
	 */
	function getOutputByCallbackMethod($callback)
	{
		ob_start();// バッファ作成
		
		// コールバック関数を実行
		if (is_callable($callback)) call_user_func($callback);
		
		// 現在のバッファ内容を取得し、バッファを破棄
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}
	/**
	 * ページ作成処理中断を行った場合のレスポンスヘッダ設定
	 *
	 * @param int $responseCode		レスポンスコード
	 * @return 						なし
	 */
	function setResponse($responseCode)
	{
		switch ($responseCode){
			case 400:			// リクエストにエラーがあるとき
				header('HTTP/1.1 400 Bad request');
				header('Status: 400 Bad request');
				break;
			case 403:			// アクセス禁止のとき
				header('HTTP/1.1 403 Forbidden');
				header('Status: 403 Forbidden');
				break;
			case 404:			// 存在しないページのとき
				header("HTTP/1.1 404 Not Found");
				header("Status: 404 Not Found");
				break;
			case 500:			// サーバ内部エラー
				header('HTTP/1.1 500 Internal Server Error');
				header('Status: 500 Internal Server Error');
				break;
			case 503:			// サイト非公開(システムメンテナンス)
				header('HTTP/1.1 503 Service Temporarily Unavailable');
				header('Status: 503 Service Temporarily Unavailable');
				break;
		}
	}
	/**
	 * ファイルダウンロード
	 *
	 * @param string $filePath			ダウンロードするファイルのファイルパス
	 * @param string $downloadFilename	ダウンロード時のファイル名
	 * @param bool   $deleteFile		実行後ファイルを削除するかどうか(成功失敗に関係なく)
	 * @return bool						true=成功、false=失敗
	 */
	function downloadFile($filePath, $downloadFilename, $deleteFile=false)
	{
		// エラーチェック
		if (!file_exists($filePath)) return false;
		
		// ファイルサイズ取得
		$fileSize = filesize($filePath);
		
		// バッファ内容が残っているときは破棄
		if (ob_get_level() > 0) ob_end_clean();
		
		header('Cache-Control: public');		// IE6+SSHでダウンロードエラーが出る問題を回避
		header('Pragma: public');				// IE6+SSHでダウンロードエラーが出る問題を回避
	    header('Content-Disposition: attachment; filename=' . $downloadFilename);
		header('Content-Length: ' . $fileSize);
	    header('Content-Type: application/octet-stream; name=' . $downloadFilename);
	    $fp = fopen($filePath, "r");
	    echo fread($fp, $fileSize);
	    fclose($fp);
		
		ob_end_flush();
		
		// ファイル削除
		if ($deleteFile) unlink($filePath);
		return true;
	}
	/**
	 * システム制御ページを表示するかどうかを返す
	 *
	 * @return bool		true=システム制御ページを表示、false=システム制御ページを表示しない
	 */
	function isSystemPage()
	{
		return $this->isSystemPage;
	}
	/**
	 * 指定URLへリダイレクト
	 *
	 * @param string $url			遷移先URL。未指定の場合は現在のスクリプト。URLでないときは、現在のスクリプトに付加。
	 * @param bool $noMessage		画面を遷移させたとき、ドコモ携帯端末でダイアログ(サイトが移動しました(301))が出ないようにするオプション
	 * @param int  $responseCode	レスポンスコード
	 * @param bool $autoSsl			リダイレクト先のページの状態に合わせてSSLを制御するかどうか
	 * @return 						なし
	 */
	function redirect($url = '', $noMessage = false, $responseCode = 0, $autoSsl = true)
	{
		global $gEnvManager;

		// すでにリダイレクトが設定されている場合は終了
		if ($this->isRedirect) return;				// リダイレクトするかどうか
		
		$toUrl = $url;
		if (empty($toUrl)){
			$toUrl = $gEnvManager->getCurrentScriptUrl();
		} else if (strncmp($toUrl, 'http://', 7) != 0 && strncmp($toUrl, 'https://', 8) != 0){	// URL以外のときはクエリー文字列と判断する
			$toUrl = $gEnvManager->getCurrentScriptUrl() . $toUrl;
		}

		// リダイレクト先のページの状態に合わせてSSLを制御する場合は、SSLの状態を必ず修正してリダイレクトする
		if ($autoSsl){
			if ($gEnvManager->isRootUrlSsl()){	// サイト全体がSSLの場合は常にSSL画面に遷移
				$isSslPage = true;
			} else {
				$isSslPage = false;
				if ($gEnvManager->isAdminUrlAccess($toUrl)){		// 管理画面へのアクセスのとき
					// 管理画面のSSL状態を参照
					if ($gEnvManager->getUseSslAdmin()) $isSslPage = true;		// 管理画面でSSLを使用するとき
				} else {		// フロント画面へのアクセスのとき
					// ファイル名を取得
					$paramArray = array();
					//list($filename, $query) = explode('?', basename($toUrl));
					list($url, $query) = explode('?', $toUrl);
					$baseUrl = dirname($url);
					$filename = basename($url);
					if (empty($filename)) $filename = M3_FILENAME_INDEX;
					if (!empty($query)) parse_str($query, $paramArray);		// クエリーの解析
		
					// ページIDを取得
					$pageId = basename($filename, '.php');
					$pageSubId = $paramArray[M3_REQUEST_PARAM_PAGE_SUB_ID];
			
					// ページのSSL設定状況を取得
					$isSslPage = $this->isSslPage($pageId, $pageSubId);
			
					// 階層化ページの場合はURLを修正
					if ($this->gSystem->hierarchicalPage() && $filename == M3_FILENAME_INDEX){
						$toUrl = $baseUrl . '/' . $pageSubId . '/';
						unset($paramArray[M3_REQUEST_PARAM_PAGE_SUB_ID]);
					
						$paramStr = $this->_createUrlParamStr($paramArray);
						if (!empty($paramStr)) $toUrl .= '?' . $paramStr;
					}
					
					// SSLありのページへ遷移する場合はSSL用のURLを修正
					if ($isSslPage) $toUrl = $gEnvManager->getSslUrl($toUrl);		// SSL用URLに変換
				}
			}
			if ($isSslPage){
				$toUrl = str_replace('http://', 'https://', $toUrl);
			} else {
				$toUrl = str_replace('https://', 'http://', $toUrl);
			}
		}

		// バッファ内容が残っているときは破棄
		if (ob_get_level() > 0) ob_end_clean();

		if (empty($responseCode)){
			if ($noMessage){
				header('HTTP/1.1 302 Found(Moved Temporary)');		// ダイアログメッセージを抑止(ドコモ携帯端末用)
			} else {
//				header('HTTP/1.1 301 Moved Permanently');
				header('HTTP/1.1 302 Moved Temporary');				// ドメインのURLをHTTPからHTTPに変更するとChromeで問題が発生したので301から302リダイレクトに変更(2018/4/30)
			}
		} else {		// レスポンスコードが指定されている場合
			switch ($responseCode){
				case 301:
					// ##### 301リダイレクトはChromeではブラウザにキャッシュされてしまうので注意 #####
					header('HTTP/1.1 301 Moved Permanently');
					break;
				case 302:
					header('HTTP/1.1 302 Found(Moved Temporary)');		// ダイアログメッセージを抑止(ドコモ携帯端末用)
					break;
				case 303:
					header('HTTP/1.1 303 See Other');
					break;
				case 500:			// サーバ内部エラー
					header('HTTP/1.1 500 Internal Server Error');
					header('Status: 500 Internal Server Error');
					break;
				case 503:			// サイト非公開(システムメンテナンス)
					header('HTTP/1.1 503 Service Temporarily Unavailable');
					header('Status: 503 Service Temporarily Unavailable');
					break;
				default:
					header('HTTP/1.1 ' . (string)$responseCode);
					break;
			}
		}
		header('Location: ' . $toUrl);
		
		$this->isRedirect = true;				// リダイレクトするかどうか
	}
	/**
	 * リダイレクト処理かどうかを返す
	 *
	 * @return bool		true=リダイレクト、false=リダイレクトでない
	 */
	function isRedirect()
	{
		return $this->isRedirect;
	}
	/**
	 * 指定URLのディレクトリへリダイレクト
	 *
	 * @return 						なし
	 */
	function redirectToDirectory()
	{
		global $gEnvManager;

		// ファイル名を削除
		$dirPath = dirname($gEnvManager->getCurrentScriptUrl()) . '/';
		//$this->redirect($dirPath);
		$this->redirect($dirPath, false, 303, false/*SSLは自動制御しない*/);			// Firefoxでredirect先がキャッシュに残る問題を回避(2012/7/23)
	}
	/**
	 * インストール時のリダイレクト処理
	 *
	 * @param string $url			遷移先URL
	 * @return 						なし
	 */
	function redirectInInstall($url)
	{
		$this->redirect($url, false, 303, false/*SSLは自動制御しない*/);			// Firefoxでredirect先がキャッシュに残る問題を回避(2012/8/1)
	}
	/**
	 * インストール用URLへリダイレクト
	 *
	 * @return 						なし
	 */
	function redirectToInstall()
	{
		global $gEnvManager;
		
		$sytemRootUrl = $gEnvManager->calcSystemRootUrl();
		//$this->redirect($sytemRootUrl . '/admin/install.php');
		$this->redirect($sytemRootUrl . '/admin/install.php', false, 303, false/*SSLは自動制御しない*/);			// Firefoxでredirect先がキャッシュに残る問題を回避(2012/8/1)
	}
	/**
	 * ログイン、ログアウト処理を行った後、リダイレクト
	 *
	 * @param RequestManager	$request		HTTPリクエスト処理クラス
	 * @param bool				$success		ログインの成否
	 * @param string			$redirectUrl	ログイン成功の場合のリダイレクト先
	 * @return int								0=何も処理しなかった場合、1=ログイン処理、2=ログアウト処理、3=パスワード送信
	 */
	function standardLoginLogoutRedirect($request, &$success, $redirectUrl = '')
	{
		global $gEnvManager;
		global $gAccessManager;
		global $gRequestManager;
		
		// ログイン、ログアウト処理、および、コンテナの起動
		$cmd = $request->trimValueOf(M3_REQUEST_PARAM_OPERATION_COMMAND);
		if ($cmd == M3_REQUEST_CMD_LOGIN){			// ログインの場合
			$act = $request->trimValueOf(M3_REQUEST_PARAM_OPERATION_ACT);
			if ($act == 'sendpwd'){		// パスワード送信の場合
				$success = $gAccessManager->sendPassword($request);
				return 3;
			} else {
				// ユーザ認証
				$ret = $gAccessManager->userLogin($request);
				if ($ret){
					if (empty($redirectUrl)){
						// システム運用可能ユーザの場合でリダイレクト先が設定されていない場合はデフォルトの管理画面URLへ遷移
						$userInfo = $gEnvManager->getCurrentUserInfo();// ユーザ情報取得
						if ($userInfo->userType >= UserInfo::USER_TYPE_MANAGER){
							$ret = $this->systemDb->getLoginUserRecordById($userInfo->userId, $userInfoRow);
							if ($ret && !empty($userInfoRow['lu_default_admin_url'])){
								$redirectUrl = $gEnvManager->getDefaultAdminUrl() . $userInfoRow['lu_default_admin_url'];
							}
						}
					}
				
					// リダイレクト処理
					$this->redirect($redirectUrl);
					$success = true;		// ログイン成功
				} else {	// ログイン失敗のとき
					$success = false;		// ログイン失敗
				}
				return 1;
			}
		} else if ($cmd == M3_REQUEST_CMD_LOGOUT){		// ログアウト
			// ログアウト処理
			$gAccessManager->userLogout(true);
			
			// リダイレクト処理
			$this->redirect($redirectUrl);
			return 2;
		} else {
			return 0;
		}
	}
	/**
	 * エラー画面を表示
	 *
	 * @param int $errStatus		HTTPエラーステータスコード
	 * @return 						なし
	 */
	function showError($errStatus)
	{
		// レスポンスヘッダ設定
		$this->setResponse($errStatus);
		
		// メッセージ設定
		$msg = 'NOT DEFINED ERROR';
		switch ($errStatus){
			case 403:			// アクセス禁止のとき
				$msg = 'Forbidden';
				break;
			case 404:			// 存在しないページのとき
				$msg = 'Not Found';
				break;
			case 500:			// サーバ内部エラー
				$msg = 'Internal Server Error';
				break;
			case 503:			// サイト非公開(システムメンテナンス)
				$msg = 'Service Temporarily Unavailable';
				break;
		}
		
		echo '<!doctype html>';
		echo '<html>';
		echo '<head>';
		echo '<title>' . $msg . '</title>';
		echo '</head>';
		echo '<body>';
		echo '<h2>' . $msg . '</h2>';
		echo '</body>';
		echo '</html>';
	}
	/**
	 * ウィジェットがセットされているページサブIDを取得
	 *
	 * @param string $pageId		ページID
	 * @param string $widgetId		対象ウィジェットID
	 * @param int $configId			ウィジェット定義ID
	 * @return string				ページサブID
	 */
	function getPageSubIdByWidget($pageId, $widgetId, $configId = 0)
	{
		$pageSubId = '';
		
		// 対象のウィジェットのページサブIDを取得
		$ret = $this->systemDb->getSubPageId($widgetId, $pageId, $rows);
		if ($ret){// データが存在する
			for ($i = 0; $i < count($rows); $i++){
				if (!empty($rows[$i]['pd_sub_id']) && $rows[$i]['pd_config_id'] == $configId){
					$pageSubId = $rows[$i]['pd_sub_id'];
					break;
				}
			}
		}
		return $pageSubId;
	}
	/**
	 * その他のGET,POSTパラメータからページサブID取得
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param string $redirectUrl			リダイレクト用URL
	 * @return string						ページサブID
	 */
	function getPageSubIdByParam($request, &$redirectUrl)
	{
		global $gEnvManager;
		
		$subId = '';
		$redirectUrl = '';
		$task = $request->trimValueOf('task');
		$option = $request->trimValueOf('option');
		if ($task == 'search' && $option == 'com_search'){		// joomla!の検索結果表示の場合
			$this->contentType = M3_VIEW_TYPE_SEARCH;		// ページのコンテンツタイプ
				
			$subId = $this->systemDb->getSubPageIdWithContent(M3_VIEW_TYPE_SEARCH, $gEnvManager->getCurrentPageId());// ページサブIDを取得
			if (!empty($subId)){
				// リダイレクト用URLを作成
				$keyword = $request->trimValueOf('searchword');
				$redirectUrl = $gEnvManager->createPageUrl();
				$redirectUrl .= '?' . M3_REQUEST_PARAM_PAGE_SUB_ID . '=' . $subId;
				$redirectUrl .= '&' . M3_REQUEST_PARAM_OPERATION_TASK . '=search&' . M3_REQUEST_PARAM_KEYWORD . '=' . urlencode($keyword);
			}
		}
		return $subId;
	}
	/**
	 * コンテンツタイプとページIDからページサブID取得
	 *
	 * @param string $contentType	コンテンツタイプ
	 * @param string $pageId		ページID
	 * @return string				ページサブID
	 */
	function getPageSubIdByContentType($contentType, $pageId)
	{
		$subId = $this->systemDb->getSubPageIdWithContent($contentType, $pageId);// ページサブIDを取得
		return $subId;
	}
	/**
	 * 指定のウィジェットがページ上にあるかを取得
	 *
	 * @param string $pageId		ページID
	 * @param string $widgetId		対象ウィジェットID
	 * @param bool $activePageOnly	公開中のページのウィジェットに制限するかどうか。falseの場合はすべてのページのウィジェットを対象とする。
	 * @return bool					true=ページ上にあり、false=ページ上になし
	 */
	function isWidgetOnPage($pageId, $widgetId, $activePageOnly=true)
	{
		$ret = $this->systemDb->isWidgetOnPage($pageId, $widgetId, $activePageOnly, 0/*定義セットID*/);
		return $ret;
	}
	/**
	 * URLで示されるページ上のウィジェットをウィジェット種別で取得
	 *
	 * @param string $url			URL
	 * @param string $widgetType	対象ウィジェットタイプ
	 * @return string				ウィジェットID
	 */
	function getWidgetIdByWidgetType($url, $widgetType)
	{
		$ret = $this->getPageIdFromUrl($url, $pageId, $pageSubId, $param);
		
		// ウィジェットIDを取得
		if ($ret) $widgetId = $this->systemDb->getWidgetIdByType($pageId, $pageSubId, $widgetType);
		return $widgetId;
	}
	/**
	 * ページコンテンツタイプ、コンテンツタイプからウィジェットIDを取得
	 *
	 * @param string $pageId		ページID
	 * @param string $contentType    コンテンツタイプ
	 * @return string				ウィジェットID
	 */
	function getWidgetIdWithPageInfoByContentType($pageId, $contentType)
	{
		$widgetId = $this->systemDb->getWidgetIdWithPageInfoByContentType($pageId, $contentType);
		return $widgetId;
	}
	/**
	 * ジョブタイプからウィジェットIDを取得
	 *
	 * @param string $jobType		ジョブタイプ
	 * @return string				ウィジェットID
	 */
	function getWidgetIdByJobType($jobType)
	{
		global $gEnvManager;
		global $gErrorManager;

		// ジョブ実行用ウィジェット取得
		$widgetId = '';
		$linkInfoObj = $this->gInstance->getObject(self::LINKINFO_OBJ_ID);
		if (isset($linkInfoObj)) $widgetId = $linkInfoObj->getJobWidget($jobType);
		
		// ウィジェットのアクセス権をチェック
		if (!$this->systemDb->canAccessWidget($widgetId)) return '';

		// ウィジェット情報取得
		if (!$this->systemDb->getWidgetInfo($widgetId, $row)) return '';

		// 端末タイプをチェック
		if (intval($row['wd_device_type']) != intval($gEnvManager->getCurrentPageDeviceType())) return '';
		
		return $widgetId;
	}
	/**
	 * 現在アクティブなメイン型ウィジェットをウィジェット種別で取得
	 *
	 * @param string $widgetType	対象ウィジェットタイプ
	 * @return string				ウィジェットID
	 */
	function getActiveMainWidgetIdByWidgetType($widgetType)
	{
		// ウィジェットIDを取得
		$widgetId = $this->systemDb->getActiveMainWidgetIdByType($widgetType);
		return $widgetId;
	}
	/**
	 * URLを解析して、ページID,サブページIDを取得
	 *
	 * @param string $url			URL
	 * @param string $pageId		ページID
	 * @param string $pageSubId		ページサブID
	 * @param array  $urlParam		URLパラメータ
	 * @return bool					true=成功、false=失敗
	 */
	function getPageIdFromUrl($url, &$pageId, &$pageSubId, &$urlParam)
	{
		global $gEnvManager;
			
		// ページID、ページサブIDを求める
		list($page, $other) = explode('?', str_replace($gEnvManager->getRootUrl(), '', $url));
		parse_str($other, $urlParam);
		
		$pageId = str_replace('/', '_', trim($page, '/'));
		$pageId = basename($pageId, '.php');

		// ページサブIDを取得
		$pageSubId = $param[M3_REQUEST_PARAM_PAGE_SUB_ID];
		if (empty($pageSubId)) $pageSubId = $gEnvManager->getDefaultPageSubId();
		return true;
	}
	/**
	 * ウィジェットが組み込まれているページのURLを生成
	 *
	 * @param string $widgetId	送信元ウィジェットID
	 * @param string $param		実行パラメータ
	 * @param string $pageId	ウィジェットが存在するページID
	 * @return string			生成したURL
	 */
	function getDefaultPageUrlByWidget($widgetId, $param = '', $pageId = '')
	{
		global $gEnvManager;

		if (empty($pageId)) $pageId = $gEnvManager->getDefaultPageId();
		
		// このウィジェットがマップされているページサブIDを取得
		$sub = '';
		$subPageId = $this->getPageSubIdByWidget($pageId, $widgetId);
		if (empty($subPageId)){
			// 見つからないときは互換ウィジェットを探す
			$targetWidgetId = $this->systemDb->getCompatibleWidgetId($widgetId);
			if (!empty($targetWidgetId)){
				$subPageId = $this->getPageSubIdByWidget($pageId, $targetWidgetId);
			}
		}
		if (!empty($subPageId)) $sub = M3_REQUEST_PARAM_PAGE_SUB_ID . '=' . $subPageId;
		
		//$url = $gEnvManager->getDefaultUrl();
		$url = $gEnvManager->createPageUrl($pageId);
		if (!empty($sub)) $url .= '?' . $sub;
		if (!empty($param)) $url .= '&' . $param;
		return $url;
	}
	/**
	 * 現在のアクセスポイントの指定のページ属性のページURLを生成
	 *
	 * @param string $contentType	コンテンツタイプ
	 * @param string $param			追加パラメータ
	 * @param bool $omitPageId		ページID省略形を使用するかどうか
	 * @return string				生成したURL
	 */
	function createContentPageUrl($contentType, $param = '', $omitPageId = true)
	{
		global $gEnvManager;
		static $urlArray = array();

		// 複数回呼ばれるのでコンテンツタイプごとにページまでのURLは保存しておく
		//$baseUrl = $urlArray[$contentType];
		//if (!isset($baseUrl)){
		if (isset($urlArray[$contentType])) {
			$baseUrl = $urlArray[$contentType];
		} else {
			// コンテンツタイプからページサブIDを取得
			$pageSubId = $this->getPageSubIdByContentType($contentType, $gEnvManager->getCurrentPageId());		// DBから取得

			if (empty($pageSubId)){	// ページサブIDが取得できない場合はデフォルトのページサブIDを取得
				$pageSubId = $gEnvManager->getDefaultPageSubId();
			} else if ($pageSubId == $gEnvManager->getDefaultPageSubId()){		// デフォルトページの場合
				if ($omitPageId) $pageSubId = '';	// ページID省略形を使用する場合はページサブIDを省略
			}

			// 現在のアクセスポイントのURLを取得
			$baseUrl = $gEnvManager->createPageUrl();

			// ページサブIDを付加
			if (!empty($pageSubId)) $baseUrl .= '?' . M3_REQUEST_PARAM_PAGE_SUB_ID . '=' . $pageSubId;

			// URLを保存
			$urlArray[$contentType] = $baseUrl;
		}

		// 追加パラメータ
		$url = $baseUrl;
		if (!empty($param)){
			if (strEndsWith($url, '.php') || strEndsWith($url, '/')){
				$url .= '?';
			} else {
				$url .= '&';
			}
			$url .= $param;
		}
		return $url;
	}
	/**
	 * ウィジェット指定呼び出し用のURLを生成
	 *
	 * @param string $toWidgetId	送信先ウィジェットID
	 * @param string $fromWidgetId	送信元ウィジェットID
	 * @param string $todo			実行パラメータ
	 * @param string $pageId		ページID(空のときは現在のURL)
	 * @return string				生成したURL
	 */
	function createWidgetCmdUrl($toWidgetId, $fromWidgetId, $todo, $pageId = '')
	{
		global $gEnvManager;
		
		$url = $gEnvManager->createPageUrl($pageId);
		
		$url .= '?' . M3_REQUEST_PARAM_OPERATION_COMMAND . '=' . M3_REQUEST_CMD_FIND_WIDGET;
		$url .= '&' . M3_REQUEST_PARAM_WIDGET_ID . '=' . $toWidgetId;
		if (!empty($fromWidgetId)) $url .= '&' . M3_REQUEST_PARAM_FROM . '=' . $fromWidgetId;		// 送信元
		if (!empty($todo)) $url .= '&' . M3_REQUEST_PARAM_OPERATION_TODO . '=' . urlencode($todo);
		return $url;
	}
	/**
	 * RSS配信用のURLを生成
	 *
	 * @param string $widgetId		実行ウィジェットID
	 * @param string $optionParam	追加パラメータ
	 * @param string $pageId		ページID(空のときは現在のURL)
	 * @param string $pageSubId		ページサブID
	 * @return string				生成したURL
	 */
	function createRssCmdUrl($widgetId, $optionParam = '', $pageId = '', $pageSubId = '')
	{
		global $gEnvManager;
		
		// 現在のページURLを取得
		$url = $gEnvManager->createPageUrl() . '?';
		
		// ページサブIDを取得
		if (empty($pageSubId)) $pageSubId = $this->gEnv->getCurrentWidgetPageSubId();	// グローバル属性ありのときは空
		if (!empty($pageSubId)) $url .= M3_REQUEST_PARAM_PAGE_SUB_ID . '=' . $pageSubId . '&';
		
		$url .= M3_REQUEST_PARAM_OPERATION_COMMAND . '=' . M3_REQUEST_CMD_RSS;
		$url .= '&' . M3_REQUEST_PARAM_WIDGET_ID . '=' . $widgetId;
		if (!empty($optionParam)) $url .= '&' . $optionParam;
		return $url;
	}
	/**
	 * CSS生成用のURLを生成
	 *
	 * @param bool   $isSslPage		SSLが必要なページかどうか
	 * @param string $optionParam	追加パラメータ
	 * @return string				生成したURL
	 */
	function createCssCmdUrl($isSslPage, $optionParam = '')
	{
		global $gEnvManager;
		global $gRequestManager;
		
		// 現在のページURLを取得
	//	$url = $gEnvManager->createPageUrl() . '?';
		$url = $gEnvManager->createPageUrl(''/*現在のページ*/, $isSslPage) . '?';
		
		// ページサブIDを取得
		$pageSubId = $this->gEnv->getCurrentPageSubId();
		$url .= M3_REQUEST_PARAM_PAGE_SUB_ID . '=' . $pageSubId . '&';
		
		// コンテンツ詳細画面の場合はコンテンツパラメータを追加
		if ($this->isContentDetailPage){
			switch ($this->contentType){
				case M3_VIEW_TYPE_CONTENT:		// 汎用コンテンツ
					$contentsId = $gRequestManager->trimValueOf(M3_REQUEST_PARAM_CONTENT_ID);
					if (empty($contentsId)) $contentsId = $gRequestManager->trimValueOf(M3_REQUEST_PARAM_CONTENT_ID_SHORT);
					if (!empty($contentsId)) $url .= M3_REQUEST_PARAM_CONTENT_ID . '=' . $contentsId . '&';
					break;
				case M3_VIEW_TYPE_PRODUCT:	// 製品
					$contentsId = $gRequestManager->trimValueOf(M3_REQUEST_PARAM_PRODUCT_ID);
					if (empty($contentsId)) $contentsId = $gRequestManager->trimValueOf(M3_REQUEST_PARAM_PRODUCT_ID_SHORT);
					if (!empty($contentsId)) $url .= M3_REQUEST_PARAM_PRODUCT_ID . '=' . $contentsId . '&';
					break;
				case M3_VIEW_TYPE_BBS:	// BBS
					$contentsId = $gRequestManager->trimValueOf(M3_REQUEST_PARAM_BBS_THREAD_ID);
					if (empty($contentsId)) $contentsId = $gRequestManager->trimValueOf(M3_REQUEST_PARAM_BBS_THREAD_ID_SHORT);
					if (!empty($contentsId)) $url .= M3_REQUEST_PARAM_BBS_THREAD_ID . '=' . $contentsId . '&';
					break;
				case M3_VIEW_TYPE_BLOG:	// ブログ
					$contentsId = $gRequestManager->trimValueOf(M3_REQUEST_PARAM_BLOG_ENTRY_ID);
					if (empty($contentsId)) $contentsId = $gRequestManager->trimValueOf(M3_REQUEST_PARAM_BLOG_ENTRY_ID_SHORT);
					if (!empty($contentsId)) $url .= M3_REQUEST_PARAM_BLOG_ENTRY_ID . '=' . $contentsId . '&';
					break;
				case M3_VIEW_TYPE_WIKI:	// Wiki
					$contentsId = $gRequestManager->getWikiPageFromQuery();		// 「=」なしのパラメータはwikiパラメータとする
					if (!empty($contentsId)) $url .= $contentsId . '&';
					break;
				case M3_VIEW_TYPE_EVENT:	// イベント
					$contentsId = $gRequestManager->trimValueOf(M3_REQUEST_PARAM_EVENT_ID);
					if (empty($contentsId)) $contentsId = $gRequestManager->trimValueOf(M3_REQUEST_PARAM_EVENT_ID_SHORT);
					if (!empty($contentsId)) $url .= M3_REQUEST_PARAM_EVENT_ID . '=' . $contentsId . '&';
					break;
				case M3_VIEW_TYPE_PHOTO:	// フォトギャラリー
					$contentsId = $gRequestManager->trimValueOf(M3_REQUEST_PARAM_PHOTO_ID);
					if (empty($contentsId)) $contentsId = $gRequestManager->trimValueOf(M3_REQUEST_PARAM_PHOTO_ID_SHORT);
					if (!empty($contentsId)) $url .= M3_REQUEST_PARAM_PHOTO_ID . '=' . $contentsId . '&';
					break;
			}
		}
		
		$url .= M3_REQUEST_PARAM_OPERATION_COMMAND . '=' . M3_REQUEST_CMD_CSS;
		if (!empty($optionParam)) $url .= '&' . $optionParam;
		return $url;
	}
	/**
	 * ウィジェット実行用のURLを生成
	 *
	 * @param string $widgetId	実行ウィジェットID
	 * @param string $optionParam	追加パラメータ
	 * @param bool $byMacro		マクロ変換で返すかどうか
	 * @param string $pageId		ページID(空のときは現在のURL)
	 * @param string $pageSubId		ページサブID
	 * @return string				生成したURL
	 */
	function createDirectWidgetCmdUrl($widgetId, $optionParam = '', $byMacro = false, $pageId = '', $pageSubId = '')
	{
		global $gEnvManager;
		
		// 現在のページURLを取得
		if ($byMacro){
			$url = M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END . '/' . M3_FILENAME_INDEX . '?';
		} else {
			$url = $gEnvManager->createPageUrl() . '?';
		}
		
		// ページサブIDを取得
		if (empty($pageSubId)) $pageSubId = $this->gEnv->getCurrentWidgetPageSubId();	// グローバル属性ありのときは空
		if (!empty($pageSubId)) $url .= M3_REQUEST_PARAM_PAGE_SUB_ID . '=' . $pageSubId . '&';
		
		$url .= M3_REQUEST_PARAM_OPERATION_COMMAND . '=' . M3_REQUEST_CMD_DO_WIDGET;
		$url .= '&' . M3_REQUEST_PARAM_WIDGET_ID . '=' . $widgetId;
		if (!empty($optionParam)) $url .= '&' . $optionParam;
		return $url;
	}
	/**
	 * URLパラメータの並び順を取得
	 *
	 * @return array					パラメータ文字列の配列
	 */
	function getUrlParamOrder()
	{
		return $this->urlParamOrder;					// URLパラメータの並び順
	}
	/**
	 * キャッシュ制御用の付加パラメータを取得
	 *
	 * @return string				パラメータ
	 */	
	function getCacheParam()
	{
		$addParam = '?' . date('YmdHis');
		return $addParam;
	}
	/**
	 * 現在のURLにアクセス可能かチェック
	 *
	 * @param bool $isActivePage	ページが有効かどうか
	 * @param int  $errCode			エラーコード
	 * @return bool					true=可能、false=不可
	 */	
	function canAccessUrl(&$isActivePage, &$errCode)
	{
		global $gEnvManager;

		$errCode = 0;			// エラーコード初期化
		
		// 現在設定されているページIDを取得
		$pageId		= $gEnvManager->getCurrentPageId();
		$pageSubId	= $gEnvManager->getCurrentPageSubId();
		$ret = $this->systemDb->canAccessPage($pageId, $pageSubId, $pageVisible, $pageSubVisible, $isAdminMenu);
		if ($ret){		// アクセス可能なときは、ユーザ制限もチェックする
			$isActivePage = true;
			
			// ページが表示可能かチェック
			if (!$pageVisible || !$pageSubVisible) return false;

			if (!$gEnvManager->isCurrentUserLogined() && $this->isUserLimitedPage($pageId, $pageSubId)){			// ユーザがログインされていない状態でユーザ制限されていればアクセス不可
				return false;
			} else {
				$this->isAccessPointWithAdminMenu = $isAdminMenu;				// 管理メニューを使用するアクセスポイントかどうか
				return true;
			}
		} else {			// 無効ページのとき
			$isActivePage = false;
			
			// ページIDの文字のチェック。ページIDは半角英小文字、数字、アンダーバー、ハイフンのみ使用可能。
			if (preg_match("/[^0-9a-z-_]/", $pageSubId)) $errCode = 1;			// 不正文字検出
		}
		return false;
	}
	/**
	 * ウィジェットヘッダ(Joomla!1.0用)を出力タイプを取得
	 *
	 * @return int				0=出力しない、1=Joomla!1.0テンプレートPC用、2=Joomla!1.0テンプレート携帯用
	 */	
	function getTemplateWidgetHeaderType()
	{
		global $gEnvManager;
		
		$templateId = $gEnvManager->getCurrentTemplateId();
		if ($templateId == self::ADMIN_TEMPLATE){		// PC管理用テンプレート
			return 0;
		} else if(strncmp($templateId, 'm/', 2) == 0){		// 携帯用テンプレート
			return 2;		// 携帯テンプレート用出力
		} else {
			return 1;		// PCテンプレート用出力
		}
	}
	/**
	 * SSLが必要なページかどうかを判断
	 *
	 * @param string $pageId	ページID
	 * @param string $pageSubId	ページサブID
	 * @return bool				true=必要、false=不要
	 */	
	function isSslPage($pageId, $pageSubId)
	{
		global $gEnvManager;
		
		// パラメータ修正
		if (empty($pageId)) $pageId = $gEnvManager->getDefaultPageId();
		
		// 管理用ページの場合
		if ($pageId == $gEnvManager->getDefaultAdminPageId()){
			if ($gEnvManager->getUseSslAdmin()){		// 管理用ページにSSLを使用するかどうか
				return true;
			} else {
				return false;
			}
		}
		
		// ##### フロント画面の場合 #####
		if ($gEnvManager->isRootUrlSsl()) return true;		// ルートURLがSSLの場合はフロント画面のすべてがSSL付き
		
		// 一般用ページにSSLを使用しない場合は不要を返す
		if (!$gEnvManager->getUseSsl()) return false;
					
		// 一般用ページでSSLを使用する場合は、データベースの設定をチェックする
		$line = $this->getPageInfo($pageId, $pageSubId);
		if (!empty($line)){
			if ($line['pn_use_ssl']){
				return true;
			} else {
				return false;
			}
		}
		return false;
	}
	/**
	 * ユーザ制限が必要なページかどうかを判断
	 *
	 * @param string $pageId	ページID
	 * @param string $pageSubId	ページサブID
	 * @return bool				true=必要、false=不要
	 */	
	function isUserLimitedPage($pageId, $pageSubId)
	{
		global $gEnvManager;
		
		// パラメータ修正
		if (empty($pageId)) $pageId = $gEnvManager->getDefaultPageId();
		
		$line = $this->getPageInfo($pageId, $pageSubId);
		if (!empty($line)){
			if ($line['pn_user_limited']){
				return true;
			} else {
				return false;
			}
		}
		return false;
	}
	/**
	 * ページ情報を取得
	 *
	 * @param string $pageId	ページID
	 * @param string $pageSubId	ページサブID
	 * @return array			ページ情報レコード
	 */
	function getPageInfo($pageId, $pageSubId)
	{
		// ページ情報が空のときはデータをロード
		if (empty($this->pageInfo)){
			$records = $this->systemDb->getPageInfoRecords();
			$count = count($records);
			$this->pageInfo = array();
			for ($i = 0; $i < $count; $i++){
				$pId = $records[$i]['pn_id'] . self::PAGE_ID_SEPARATOR . $records[$i]['pn_sub_id'];
				$this->pageInfo[$pId] = $records[$i];
			}
		}
		//return $this->pageInfo[$pageId . self::PAGE_ID_SEPARATOR . $pageSubId];
		if (isset($this->pageInfo[$pageId . self::PAGE_ID_SEPARATOR . $pageSubId])){
			return $this->pageInfo[$pageId . self::PAGE_ID_SEPARATOR . $pageSubId];
		} else {
			return NULL;
		}
	}
	/**
	 * 現在のページのページ情報を取得
	 *
	 * @return array			ページ情報レコード
	 */	
	function getCurrentPageInfo()
	{
		return $this->currentPageInfo;
	}
	/**
	 * 現在のページ情報からテンプレートIDを取得
	 *
	 * @param string $subTemplateId		テンプレートIDが取得できるときはサブページIDが返る
	 * @return string					テンプレートID
	 */	
	function getTemplateIdFromCurrentPageInfo(&$subTemplateId)
	{
		$templateId = '';
		if (!empty($this->currentPageInfo)){
			$templateId = $this->currentPageInfo['pn_template_id'];
			$subTemplateId = $this->currentPageInfo['pn_sub_template_id'];			// サブテンプレートID
		}
		return $templateId;
	}
	/**
	 * JavascriptライブラリのIDを取得
	 *
	 * @param int $type			種別(0=すべて、1=jQuery関係すべて、2=jQueryプラグインのみ)
	 * @return array			JavascriptライブラリのID
	 */	
	function getScriptLibId($type = 0)
	{
		// Javascriptライブラリのすべてのキーを取得
		$keys = array_keys($this->libFiles);
		
		$destKeys = array();
		for ($i = 0; $i < count($keys); $i++){
			switch ($type){
				case 1:		// jQuery関係すべて
					if (strStartsWith($keys[$i], 'jquery')) $destKeys[] = $keys[$i];
					break;
				case 2:		// jQueryプラグインのみ
					if (strStartsWith($keys[$i], 'jquery.')) $destKeys[] = $keys[$i];
					break;
			}
		}
		return $destKeys;
	}
	/**
	 * Javascriptライブラリの情報を取得
	 *
	 * @param string $id		ライブラリID
	 * @return array			ライブラリの情報
	 */	
	function getScriptLibInfo($id)
	{
		$libInfo = $this->libFiles[$id];
		if (isset($libInfo)){
			return $libInfo;
		} else {
			return array();
		}
	}
	/**
	 * 管理画面用jQueryUIテーマのURLを取得
	 *
	 * @return string			テーマURL
	 */	
	function getAdminDefaultThemeUrl()
	{
//		$themeFile = $this->gEnv->getRootUrl() . self::DEFAULT_THEME_DIR . $this->gSystem->adminDefaultTheme() . '/'. self::THEME_CSS_FILE;
		$themeFile = $this->gEnv->getAdminUrl(true/*「admin」削除*/) . self::DEFAULT_THEME_DIR . $this->gSystem->adminDefaultTheme() . '/'. self::THEME_CSS_FILE;
		return $themeFile;
	}
	/**
	 * フロント画面用jQueryUIテーマのURLを取得
	 *
	 * @return string			テーマURL
	 */	
	function getDefaultThemeUrl()
	{
		static $themeFile;
		
		if (!isset($themeFile)){
			$themeFile = '';
			$theme = $this->gSystem->defaultTheme();
			if (!empty($theme)){
				$path = $this->gEnv->getSystemRootPath() . self::DEFAULT_THEME_DIR . $theme . '/'. self::THEME_CSS_FILE;
				if (file_exists($path)) $themeFile = $this->gEnv->getRootUrl() . self::DEFAULT_THEME_DIR . $theme . '/'. self::THEME_CSS_FILE;
			}
		}
		return $themeFile;
	}
	/**
	 * URLパラメータ文字列作成
	 *
	 * @param array $paramArray			URL作成用のパス
	 * @return string					作成したURLパラメータ
	 */
	function _createUrlParamStr($paramArray)
	{
		$destParam = '';
		if (count($paramArray) == 0) return $destParam;

		$sortParam = array();
		$keys = array_keys($paramArray);
		$keyCount = count($keys);
		for ($i = 0; $i < $keyCount; $i++){
			$key = $keys[$i];
			$value = $paramArray[$key];
			$orderNo = $this->urlParamOrder[$key];
			if (!isset($orderNo)) $orderNo = 100;
			$sortParam[] = array('key' => $key, 'value' => $value, 'no' => $orderNo);
		}
//		usort($sortParam, create_function('$a,$b', 'return $a["no"] - $b["no"];'));
		usort($sortParam, function($a, $b)
		{
			return $a['no'] - $b['no'];
		});
		
		// 文字列を作成
		$sortCount = count($sortParam);
		for ($i = 0; $i < $sortCount; $i++){
			if ($i > 0) $destParam .= '&';
			$destParam .= rawurlencode($sortParam[$i]['key']) . '=' . rawurlencode($sortParam[$i]['value']);
		}
		return $destParam;
	}
	/**
	 * DBからCSSファイル(テンプレート独自のCSSまたは外部のCSS)を取得
	 *
	 * @param string $templateId	テンプレートID
	 * @return bool					true=取得成功、false=取得失敗
	 */
	function loadCssFiles($templateId = '')
	{
		if (empty($templateId)) $templateId = $this->gSystem->defaultTemplateId();		// PC画面デフォルトのテンプレートを取得

		$ret = $this->systemDb->getTemplate($templateId, $row);
		if (!$ret) return false;
		
		// 編集エディタ用パラメータが初期化されていない場合は初期化
		if (empty($row['tm_editor_param'])){
			// テンプレートを解析し、使用しているCSSファイルを取得
			$editorParamObj = $this->parseTemplate($templateId);
			
			// テンプレートの情報を更新
			$updateParam = array();
			$updateParam['tm_editor_param'] = serialize($editorParamObj);
			$ret = $this->systemDb->updateTemplate($templateId, $updateParam);
		} else {
			$editorParamObj = unserialize($row['tm_editor_param']);
		}
		
		if (isset($editorParamObj)){
			$this->ckeditorCssFiles = array();
			$cssFiles = $editorParamObj->cssFiles;
			for ($i = 0; $i < count($cssFiles); $i++){
				$cssFile = $cssFiles[$i];
				
				if (strStartsWith($cssFile, '//')){		// URLが「//」で始まる外部ファイルの場合はそのまま追加
					$this->ckeditorCssFiles[] = $cssFile;
				} else if (strStartsWith($cssFile, '/')){		// URLが「/」で始まる相対パスの場合はURLに変換
					$this->ckeditorCssFiles[] = $this->gEnv->getAdminUrl(true/*adminディレクトリ削除*/) . $cssFile;
				} else {
					$this->ckeditorCssFiles[] = $cssFile;
				}
			}
			return true;
		} else {
			return false;
		}
	}
	/**
	 * URLで指定した画面のCSSファイル(テンプレート独自のCSSまたは外部のCSS)を取得
	 *
	 * @param string $url	取得画面のURL
	 * @return				なし
	 */
	function loadCssFilesByUrl($url)
	{
		// ##### cURLを使用する場合はセッションのブロックを解除する #####
		session_write_close();
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_USERAGENT, M3_SYSTEM_NAME . '/' . M3_SYSTEM_VERSION);		// ユーザエージェント
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);		// 画面に出力しない
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
		curl_setopt($ch, CURLOPT_TIMEOUT, 3);
		curl_setopt($ch, CURLOPT_COOKIE, session_name() . '=' . session_id());		// セッションを維持
		$content = curl_exec($ch);
		curl_close($ch);

		// HEADタグを取り出す
		$urlArray = array();
		$headContent = '';
		$pattern = '/<head\b[^>]*?>(.*?)<\/head\b[^>]*?>/si';
		if (preg_match($pattern, $content, $matches)) $headContent = $matches[0];

		// CSSファイル取り出し
		$pattern = '/<link[^<]*?href\s*=\s*[\'"]+(.+?)[\'"]+[^>]*?>/si';
		if (preg_match_all($pattern, $headContent, $matches, PREG_SET_ORDER)){
			foreach ($matches as $match){
				if (strEndsWith($match[1], '.css')){		// ファイル名の拡張子が「css」の場合
					$urlArray[] = $match[1];
				} else {
					// typeが「text/css」を取得
					$attrPattern = '/type\s*=\s*[\'"]+(.+?)[\'"]/si';
					if (preg_match($attrPattern, $match[0], $attrMatch)){
						if (strcasecmp($attrMatch[1], 'text/css') == 0){
							$urlArray[] = $match[1];
							continue;
						}
					}
					// relが「stylesheet」を取得
					$attrPattern = '/rel\s*=\s*[\'"]+(.+?)[\'"]/si';
					if (preg_match($attrPattern, $match[0], $attrMatch)){
						if (strcasecmp($attrMatch[1], 'stylesheet') == 0){
							$urlArray[] = $match[1];
							continue;
						}
					}
				}
			}
		}

		// ifで制御されているCSSファイルを除く
		$delUrlArray = array();
		$pattern = '/<!--\[if\b.*?\]>[\s\S]*<link[^<]*?href\s*=\s*[\'"]+(.+?)[\'"]+[^>]*?>[\s\S]*<!\[endif\]-->/si';
		if (preg_match_all($pattern, $headContent, $matches, PREG_SET_ORDER)){
			foreach ($matches as $match) $delUrlArray[] = $match[1];
		}

		// ##### テンプレート独自のCSSまたは外部のCSSを取得する #####
		$this->ckeditorCssFiles = array();
		$cssFiles = array_merge(array_diff($urlArray, $delUrlArray));	// CKEditor用のCSSファイル
		for ($i = 0; $i < count($cssFiles); $i++){
			$cssFileUrl = $cssFiles[$i];
			$baseUrl = '';
			if (strncasecmp($cssFileUrl, 'http://', strlen('http://')) == 0){
				$baseUrl = $this->gEnv->getRootUrl();
			} else if (strncasecmp($cssFileUrl, 'https://', strlen('https://')) == 0){
				$baseUrl = $this->gEnv->getSslRootUrl();		// SSLの場合
			}
			// パスを解析
			$relativePath = str_replace($baseUrl, '', $cssFileUrl);		// ルートURLからの相対パスを取得
			
			if (strStartsWith($relativePath, '//')){		// URLが「//」で始まる外部ファイルの場合
				$this->ckeditorCssFiles[] = $cssFileUrl;
			} else if (strStartsWith($relativePath, '/' . M3_DIR_NAME_TEMPLATES . '/')){		// テンプレートディレクトリの場合
				$this->ckeditorCssFiles[] = $cssFileUrl;
			} else if (strStartsWith($relativePath, '/')){		// テンプレートディレクトリ以外のディレクトリの場合
			} else {			// 外部URLの場合
				$this->ckeditorCssFiles[] = $cssFileUrl;
			}
		}

		// テンプレートタイプを取得
		$pattern = '/var\s*M3_TEMPLATE_TYPE\s*=\s*(\d+?)\s*;/si';
		if (preg_match($pattern, $headContent, $matches)) $this->ckeditorTemplateType = $matches[1];			// CKEditor用のテンプレートタイプ
	}
	/**
	 * CKEditor用のテンプレートタイプを取得
	 *
	 * @return int			テンプレートタイプ
	 */	
	function getCkeditorTemplateType()
	{
		return $this->ckeditorTemplateType;
	}
	/**
	 * POST時のリファラーチェック
	 *
	 * @return bool		true=正常、false=不正
	 */
/*	function _checkReferer()
	{
		global $gEnvManager;
		global $gRequestManager;
		
		// リファラーをチェック
		$referer	= $gRequestManager->trimServerValueOf('HTTP_REFERER');
		$uri		= $gEnvManager->getCurrentRequestUri();
		if (empty($referer) || $referer != $uri){
			return false;
		} else {
			return true;
		}
	}*/
	/**
	 * テンプレート画面を解析してCSSファイルやポジションの情報を取得
	 *
	 * @param string $templateId	テンプレートID
	 * @return						テンプレート情報オブジェクト
	 */
	function parseTemplate($templateId)
	{
		// ##### cURLを使用する場合はセッションのブロックを解除する #####
		session_write_close();

		// テンプレートのURL
		$url = $this->gEnv->getDefaultAdminUrl() . '?' . M3_REQUEST_PARAM_OPERATION_COMMAND . '=' . M3_REQUEST_CMD_SHOW_POSITION . '&template=' . $templateId;
		
		// テンプレート情報オブジェクト
		$tmplateInfoObj = new stdClass;
		$tmplateInfoObj->cssFiles	= array();		// テンプレートで使用されているCSSファイル
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_USERAGENT, M3_SYSTEM_NAME . '/' . M3_SYSTEM_VERSION);		// ユーザエージェント
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);		// 画面に出力しない
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
		curl_setopt($ch, CURLOPT_TIMEOUT, 3);
		curl_setopt($ch, CURLOPT_COOKIE, session_name() . '=' . session_id());		// セッションを維持
		$content = curl_exec($ch);
		curl_close($ch);

		// HEADタグを取り出す
		$urlArray = array();
		$headContent = '';
		$pattern = '/<head\b[^>]*?>(.*?)<\/head\b[^>]*?>/si';
		if (preg_match($pattern, $content, $matches)) $headContent = $matches[0];

		// CSSファイル取り出し
		$pattern = '/<link[^<]*?href\s*=\s*[\'"]+(.+?)[\'"]+[^>]*?>/si';
		if (preg_match_all($pattern, $headContent, $matches, PREG_SET_ORDER)){
			foreach ($matches as $match){
				if (strEndsWith($match[1], '.css')){		// ファイル名の拡張子が「css」の場合
					$urlArray[] = $match[1];
				} else {
					// typeが「text/css」を取得
					$attrPattern = '/type\s*=\s*[\'"]+(.+?)[\'"]/si';
					if (preg_match($attrPattern, $match[0], $attrMatch)){
						if (strcasecmp($attrMatch[1], 'text/css') == 0){
							$urlArray[] = $match[1];
							continue;
						}
					}
					// relが「stylesheet」を取得
					$attrPattern = '/rel\s*=\s*[\'"]+(.+?)[\'"]/si';
					if (preg_match($attrPattern, $match[0], $attrMatch)){
						if (strcasecmp($attrMatch[1], 'stylesheet') == 0){
							$urlArray[] = $match[1];
							continue;
						}
					}
				}
			}
		}

		// ifで制御されているCSSファイルを除く
		$delUrlArray = array();
		$pattern = '/<!--\[if\b.*?\]>[\s\S]*<link[^<]*?href\s*=\s*[\'"]+(.+?)[\'"]+[^>]*?>[\s\S]*<!\[endif\]-->/si';
		if (preg_match_all($pattern, $headContent, $matches, PREG_SET_ORDER)){
			foreach ($matches as $match) $delUrlArray[] = $match[1];
		}

		// テンプレート独自のCSSまたは外部のCSSを取得する
		$cssFiles = array_merge(array_diff($urlArray, $delUrlArray));	// CKEditor用のCSSファイル
		for ($i = 0; $i < count($cssFiles); $i++){
			$cssFileUrl = $cssFiles[$i];
			$baseUrl = '';
			if (strncasecmp($cssFileUrl, 'http://', strlen('http://')) == 0){
				$baseUrl = $this->gEnv->getRootUrl();
			} else if (strncasecmp($cssFileUrl, 'https://', strlen('https://')) == 0){
				$baseUrl = $this->gEnv->getSslRootUrl();		// SSLの場合
			}
			// パスを解析
			$relativePath = str_replace($baseUrl, '', $cssFileUrl);		// ルートURLからの相対パスを取得
			
			if (strStartsWith($relativePath, '//')){		// URLが「//」で始まる外部ファイルの場合
				$tmplateInfoObj->cssFiles[] = $cssFileUrl;
			} else if (strStartsWith($relativePath, '/' . M3_DIR_NAME_TEMPLATES . '/')){		// テンプレートディレクトリの場合
				$tmplateInfoObj->cssFiles[] = $cssFileUrl;
			} else if (strStartsWith($relativePath, '/')){		// テンプレートディレクトリ以外のディレクトリの場合
			} else {			// 外部URLの場合
				$tmplateInfoObj->cssFiles[] = $cssFileUrl;
			}
		}
		
		// パスを相対パスに変換
		for ($i = 0; $i < count($tmplateInfoObj->cssFiles); $i++){
			$cssFileUrl = $tmplateInfoObj->cssFiles[$i];
			
			$baseUrl = '';
			if (strncasecmp($cssFileUrl, 'http://', strlen('http://')) == 0){
				$baseUrl = $this->gEnv->getRootUrl();
			} else if (strncasecmp($cssFileUrl, 'https://', strlen('https://')) == 0){
				$baseUrl = $this->gEnv->getSslRootUrl();		// SSLの場合
			}
			// パスを解析
			$relativePath = str_replace($baseUrl, '', $cssFileUrl);		// ルートURLからの相対パスを取得
			
			// テンプレート内のCSSの場合は相対パスに変換
			if (strStartsWith($relativePath, '/' . M3_DIR_NAME_TEMPLATES . '/')){// テンプレートディレクトリの場合
				$tmplateInfoObj->cssFiles[$i] = $relativePath;
			} else {
				$tmplateInfoObj->cssFiles[$i] = $cssFileUrl;
			}
		}
		
		// テンプレートのポジションを取得
		$tmplateInfoObj->positions = array();
		$pattern = '/var\s*M3_POSITION_DATA\s*=\s*[\'"]+(.+?)[\'"]/si';
		if (preg_match($pattern, $headContent, $matches) && !empty($matches[1])){
			$tmplateInfoObj->positions = explode(',', $matches[1]);
		}
		return $tmplateInfoObj;
	}
	/**
	 * デフォルトのテンプレートからレイアウト用テンプレートで使用していないポジションを取得
	 *
	 * @return array				ポジション名の配列
	 */
	function getLayoutTemplatePosition()
	{
		$removePositions = array('header', 'extra1', 'extra2', 'brand', 'hmenu', 'user3', 'banner1', 'top1', 'top2', 'top3',
								'left', 'banner2', 'breadcrumb', 'user1', 'user2', 'banner3', 'main-top', 'main', 'main-bottom', 'banner4', 'user4', 'user5', 'banner5', 
								'right','bottom1', 'bottom2', 'bottom3', 'banner6', 'syndicate', 'copyright', 'footer');		// レイアウト用テンプレートに存在するので削除するポジション
		
		// URLパラメータからデフォルトのテンプレートを取得
		$pageId = $this->gRequest->trimValueOf(M3_REQUEST_PARAM_DEF_PAGE_ID);

		$templateId = '';
		if ($pageId == $this->gEnv->getDefaultPageId()){		// 通常サイトのとき
			$templateId = $this->gSystem->defaultTemplateId();
		} else if ($pageId == $this->gEnv->getDefaultSmartphonePageId()){		// スマートフォン用サイトのとき
			$templateId = $this->gSystem->defaultSmartphoneTemplateId();		// スマートフォン用デフォルトテンプレート
		}

		// テンプレート情報取得
		$ret = $this->systemDb->getTemplate($templateId, $row);
		if (!$ret) return array();
		if (empty($row['tm_editor_param'])) return array();
	
		// レイアウト用テンプレートに存在しないポジション名のみ返す
		$editorParamObj = unserialize($row['tm_editor_param']);
		if (isset($editorParamObj)){
			if (isset($editorParamObj->positions)){
				$srcArray = $editorParamObj->positions;
				$destArray = array();
				for ($i = 0; $i < count($srcArray); $i++){
					$position = $srcArray[$i];
					if (!in_array($position, $removePositions)) $destArray[] = $position;
				}
				return $destArray;
			}
		}
		return array();
	}
	// ********************************************************
	//          テンプレート用API
	// ********************************************************
	/**
	 * FontAwesomeのCSSファイルのURL取得
	 *
	 * @param string $baseUrl	テンプレートで取得されたベースURL
	 * @return					CSSファイルのURL
	 */
	function getFontAwesomeUrl($baseUrl){
		return $baseUrl . '/' . M3_DIR_NAME_SCRIPTS . '/' . self::M3_ADMIN_ICONFONT_CSS_FILE;
	}
}
?>
