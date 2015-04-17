<?php
/**
 * グローバル定義ファイル
 *
 * 機能：ユーザが編集不可のグローバル定義。共通クラスの取り込み用。
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2015 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    Release 2.12.x SVN: $Id$
 * @link       http://www.magic3.org
 */
if (defined("M3_SYSTEM_DEFINE_INCLUDED")) return;
define("M3_SYSTEM_DEFINE_INCLUDED", true);

// ######### PHPバージョンチェック #########
if (version_compare(PHP_VERSION, '5.2.0') < 0) die("PHP version error: ver=" . PHP_VERSION . ". Magic3 can move above version 5.2.0.");
// ######### システム起動状況 #########
define('M3_SYSTEM',			true);
define('M3_SYSTEM_DEMO',	false);		// システム起動モード、デモモードフラグ
define('M3_SYSTEM_DEBUG',	false);		// システム起動モード、デバッグモードフラグ
define('M3_SESSION_DB',		true);		// セッションの保存先にDBを使用するかどうか

// ######### 出力の制御 #########
ini_set('display_errors', '1');		// コメントをはずすと画面にエラー出力する(以下はエラーレベルの設定)
//if (phpversion() < '5.4.0'){
if (version_compare(PHP_VERSION, '5.4.0') < 0){
error_reporting(E_ALL ^ E_NOTICE);			// E_NOTICE 以外の全てのエラーを表示する(PHP5.3以下初期設定値)
} else if (version_compare(PHP_VERSION, '5.6.0') < 0){
error_reporting(E_ALL & ~E_STRICT & ~E_NOTICE);	// E_STRICT,E_NOTICE 以外の全てのエラーを表示する(PHP5.4以上初期設定値)
} else {
error_reporting(E_ALL ^ E_NOTICE ^ E_DEPRECATED);
}
//error_reporting(E_ALL);
//error_reporting(E_ALL | E_STRICT);// 構文ミスエラーを表示
define('M3_SYSTEM_DEBUG_OUT', true);			// デバッグ文出力を行うかどうか
define('M3_SYSTEM_REALTIME_ANALYTICS', false);	// 即時アクセス解析を行うかどうか

// ######### 別名定義 #########
define('M3_NL',	"\n");				// 改行
define('M3_TB',	"\t");				// タブ
define('M3_INDENT_SPACE', '    ');	// 字下げスペース
define('M3_ENCODING',	'UTF-8');	// 内部エンコーディング
define('M3_TITLE_BRACKET_START',	'「');					// タイトルを括弧表記する場合の開始
define('M3_TITLE_BRACKET_END',		'」');					// タイトルを括弧表記する場合の終了

// ########## システム関係 ##########
// システム基本情報
define('M3_SYSTEM_NAME',			'Magic3');		// システム名称
define('M3_SYSTEM_VERSION',			'2.12.1');		// システムのバージョン
define('M3_SYSTEM_RELEASE_DATE',	'2014/4/10');	// システムのリリース日付
define('M3_SYSTEM_ID',				'M3');			// コンポーネント識別用ID
define('M3_SYSTEM_DESCRIPTION',		'Magic3 is open source cms and web communication platform.');		// システムの説明
define('M3_SYSTEM_MIN_MEMORY',		'16M');			// 最小メモリ使用量
define('M3_SYSTEM_DIR_PERMISSION',	0755);				// ディレクトリアクセス権
define('M3_SYSTEM_FILE_PERMISSION',	0644);				// ファイルアクセス権

// ディレクトリ定義
define('M3_SYSTEM_INCLUDE_PATH',	dirname(__FILE__));						// global.phpファイルのあるディレクトリ(基準ディレクトリ)
define('M3_SYSTEM_ROOT_PATH',		dirname(M3_SYSTEM_INCLUDE_PATH));		// システムのルートディレクトリ
define('M3_SYSTEM_LIB_PATH',		M3_SYSTEM_INCLUDE_PATH . '/lib');		// ライブラリパス
define('M3_SYSTEM_CONF_PATH',		M3_SYSTEM_INCLUDE_PATH . '/conf');		// 設定ファイル用パス
define('M3_SYSTEM_CORE_PATH',		M3_SYSTEM_INCLUDE_PATH . '/core');		// システムディレクトリパス
define('M3_SYSTEM_COMMON_PATH',		M3_SYSTEM_INCLUDE_PATH . '/common');	// システム共通ディレクトリパス
// ファイル、ディレクトリ名
define('M3_SYSTEM_ROOT_DIR_NAME',	basename(M3_SYSTEM_ROOT_PATH));			// ルートディレクトリ名
define('M3_SYSTEM_ADMIN_DIR_NAME',	'admin');								// システム管理ディレクトリ名
define('M3_DIR_NAME_INCLUDE',		'include');								// プログラム格納(非公開)ディレクトリ名
define('M3_DIR_NAME_TEMPLATES',		'templates');							// テンプレート格納ディレクトリ名
define('M3_DIR_NAME_WIDGETS',		'widgets');								// ウィジェット格納ディレクトリ名
define('M3_DIR_NAME_SCRIPTS',		'scripts');								// スクリプト格納ディレクトリ名
define('M3_DIR_NAME_RESOURCE',		'resource');							// ユーザリソース格納ディレクトリ名
define('M3_DIR_NAME_CSS',			'css');									// CSS格納ディレクトリ名
define('M3_DIR_NAME_LOCALE',		'locale');								// ロケール情報格納ディレクトリ名
define('M3_DIR_NAME_ADMIN',			'admin');								// 管理用ディレクトリ名
define('M3_DIR_NAME_MOBILE',		'm');									// 携帯用ディレクトリ名
define('M3_DIR_NAME_SMARTPHONE',	's');									// スマートフォン用ディレクトリ名
define('M3_DIR_NAME_HOME',			'home');								// 一般ユーザ用ディレクトリ格納ディレクトリ
define('M3_FILENAME_INDEX',			'index.php');							// デフォルトのアクセスポイントファイル名
define('M3_FILENAME_SERVER_CONNECTOR',		'connector.php');				// デフォルトのサーバ接続用アクセスポイントファイル名
// Magic3専用タグ
define('M3_TAG_START',				'[#');									// Magic3専用タグの開始
define('M3_TAG_END',				'#]');									// Magic3専用タグの終了
define('M3_TAG_MACRO_ROOT_URL',		'M3_ROOT_URL');							// アプリケーションのルートURLを示すマクロ
define('M3_TAG_MACRO_IMG_URL',		'M3_IMG_URL');							// 画像URLを示すマクロ
define('M3_TAG_MACRO_LINK_URL',		'M3_LINK_URL');							// リンク先URLを示すマクロ
define('M3_TAG_MACRO_WIDGET_URL',	'M3_WIDGET_URL');						// 個別のウィジェットのルートURLを示すマクロ
define('M3_TAG_MACRO_WIDGET_CSS_ID',	'M3_WIDGET_CSS_ID');				// 個別のウィジェットのCSS用IDを示すマクロ
define('M3_TAG_MACRO_EMOJI_CODE',	'M3_EMOJI');							// 絵文字コード
define('M3_TAG_MACRO_SEPARATOR',	'M3_$');								// テキストデータセパレータ
define('M3_TAG_MACRO_SITE_KEY',		'SITE_');								// サイト定義置換キー
define('M3_TAG_MACRO_CUSTOM_KEY',	'CUSTOM_KEY_');							// 汎用置換キー
define('M3_TAG_MACRO_ITEM_KEY',		'ITEM_KEY_');							// 汎用置換キー
define('M3_TAG_MACRO_CONTENT_KEY',	'CT_');									// コンテンツマクロ置換キー
define('M3_TAG_MACRO_COMMENT_KEY',	'CM_');									// コメントマクロ置換キー
define('M3_TAG_MACRO_USER_KEY',		'USER_');								// ユーザ定義置換キー
define('M3_TAG_MACRO_ITEM',			'ITEM');								// 項目置換キー
define('M3_TAG_MACRO_TITLE',		'TITLE');								// タイトル置換キー
define('M3_TAG_MACRO_BODY',			'BODY');								// 本文置換キー
define('M3_TAG_MACRO_RESULT',		'RESULT');								// 結果置換キー
define('M3_TAG_MACRO_DATE',			'DATE');								// 日付置換キー
define('M3_TAG_MACRO_MESSAGE',		'MESSAGE');								// メッセージ置換キー
define('M3_TAG_MACRO_MARK',			'MARK');								// マーク置換キー
define('M3_TAG_MACRO_IMAGE',		'IMAGE');								// サムネール画像置換キー
define('M3_TAG_MACRO_AVATAR',		'AVATAR');								// アバター置換キー
define('M3_TAG_MACRO_URL',			'URL');									// URL置換キー
define('M3_TAG_MACRO_FILES',		'FILES');								// 添付ファイル置換キー
define('M3_TAG_MACRO_PAGES',		'PAGES');								// ページリンク置換キー
define('M3_TAG_MACRO_LINKS',		'LINKS');								// 関連リンク置換キー
define('M3_TAG_MACRO_CATEGORY',		'CATEGORY');							// カテゴリーリンク置換キー
define('M3_TAG_MACRO_BLOG_LINK',	'BLOG_LINK');							// ブログリンク置換キー
define('M3_TAG_MACRO_COMMENT_LINK',	'COMMENT_LINK');						// コメントリンク置換キー
define('M3_TAG_MACRO_DETAIL_LINK',	'DETAIL_LINK');							// 詳細画面へのリンク置換キー
define('M3_TAG_MACRO_PERMALINK',	'PERMALINK');							// パーマリンク置換キー
define('M3_TAG_MACRO_RATE',			'RATE');								// 評価機能置換キー
define('M3_TAG_MACRO_WIDGET',		'WIDGET');								// ウィジェット埋め込みキー
define('M3_TAG_MACRO_SITE_NAME',		'SITE_NAME');						// サイト情報置換キー(サイト名)
define('M3_TAG_MACRO_SITE_URL',			'SITE_URL');						// サイト情報置換キー(URL)
define('M3_TAG_MACRO_SITE_DESCRIPTION',	'SITE_DESCRIPTION');				// サイト情報置換キー(簡易説明)
define('M3_TAG_MACRO_SITE_IMAGE',		'SITE_IMAGE');						// サイト情報置換キー(画像)
define('M3_TAG_MACRO_CONTENT_BREAK',		'CT_BREAK');					// コンテンツ置換キー(コンテンツ区切り)
define('M3_TAG_MACRO_CONTENT_ID',			'CT_ID');						// コンテンツ置換キー(コンテンツID)
define('M3_TAG_MACRO_CONTENT_BLOG_ID',		'CT_BLOG_ID');					// コンテンツ置換キー(ブログID)
define('M3_TAG_MACRO_CONTENT_NOW',			'CT_NOW');						// コンテンツ置換キー(表示時日時)
define('M3_TAG_MACRO_CONTENT_DATE',			'CT_DATE');						// コンテンツ置換キー(登録日)
define('M3_TAG_MACRO_CONTENT_TIME',			'CT_TIME');						// コンテンツ置換キー(登録時)
define('M3_TAG_MACRO_CONTENT_CREATE_DT',	'CT_CREATE_DT');				// コンテンツ置換キー(作成日時)
define('M3_TAG_MACRO_CONTENT_UPDATE_DT',	'CT_UPDATE_DT');				// コンテンツ置換キー(更新日時)
define('M3_TAG_MACRO_CONTENT_REGIST_DT',	'CT_REGIST_DT');				// コンテンツ置換キー(登録日時)
define('M3_TAG_MACRO_CONTENT_START_DT',		'CT_START_DT');					// コンテンツ置換キー(公開開始日時)
define('M3_TAG_MACRO_CONTENT_END_DT',		'CT_END_DT');					// コンテンツ置換キー(公開終了日時)
define('M3_TAG_MACRO_CONTENT_START_TIME',	'CT_START_TIME');				// コンテンツ置換キー(公開開始時間)
define('M3_TAG_MACRO_CONTENT_END_TIME',		'CT_END_TIME');					// コンテンツ置換キー(公開終了時間)
define('M3_TAG_MACRO_CONTENT_AUTHOR',		'CT_AUTHOR');					// コンテンツ置換キー(著者)
define('M3_TAG_MACRO_CONTENT_TITLE',		'CT_TITLE');					// コンテンツ置換キー(タイトル)
define('M3_TAG_MACRO_CONTENT_BLOG_TITLE',	'CT_BLOG_TITLE');				// コンテンツ置換キー(ブログタイトル)
define('M3_TAG_MACRO_CONTENT_SUMMARY',		'CT_SUMMARY');					// コンテンツ置換キー(概要)
define('M3_TAG_MACRO_CONTENT_DESCRIPTION',	'CT_DESCRIPTION');				// コンテンツ置換キー(説明)
define('M3_TAG_MACRO_CONTENT_URL',			'CT_URL');						// コンテンツ置換キー(URL)
define('M3_TAG_MACRO_CONTENT_INFO_URL',		'CT_INFO_URL');					// コンテンツ置換キー(その他の情報URL)
define('M3_TAG_MACRO_CONTENT_IMAGE',		'CT_IMAGE');					// コンテンツ置換キー(画像)
define('M3_TAG_MACRO_CONTENT_LOCATION',		'CT_LOCATION');					// コンテンツ置換キー(場所)
define('M3_TAG_MACRO_CONTENT_CONTACT',		'CT_CONTACT');					// コンテンツ置換キー(連絡先)
define('M3_TAG_MACRO_CONTENT_CAMERA',		'CT_CAMERA');					// コンテンツ置換キー(カメラ)
define('M3_TAG_MACRO_CONTENT_CATEGORY',		'CT_CATEGORY');					// コンテンツ置換キー(カテゴリー)
define('M3_TAG_MACRO_CONTENT_KEYWORD',		'CT_KEYWORD');					// コンテンツ置換キー(検索キーワード)
define('M3_TAG_MACRO_COMMENT_DATE',			'CM_DATE');						// コメント置換キー(登録日)
define('M3_TAG_MACRO_COMMENT_TIME',			'CM_TIME');						// コメント置換キー(登録時)
define('M3_TAG_MACRO_COMMENT_AUTHOR',		'CM_AUTHOR');					// コメント置換キー(著者)

// イベントフックタイプ
define('M3_EVENT_HOOK_TYPE_OPELOG',			'opelog');						// 運用ログ
// イベントフック用パラメータ
define('M3_EVENT_HOOK_PARAM_CONTENT_TYPE',	'content_type');				// コンテンツタイプ
define('M3_EVENT_HOOK_PARAM_CONTENT_ID',	'content_id');					// コンテンツID
define('M3_EVENT_HOOK_PARAM_UPDATE_DT',		'update_dt');					// 更新日時

// データ作成用
define('M3_WIDGET_ID_SEPARATOR',	',');									// ウィジェットIDと付加情報を連結
define('M3_LANG_SEPARATOR',			'|');									// 言語IDと内容を連結

// テンプレートタイプ
define('M3_TEMPLATE_JOOMLA_10',			0);		// Joomla!v1.0テンプレート
define('M3_TEMPLATE_JOOMLA_15',			1);		// Joomla!v1.5テンプレート
define('M3_TEMPLATE_JOOMLA_25',			2);		// Joomla!v2.5テンプレート
define('M3_TEMPLATE_BOOTSTRAP_30',		10);		// Bootstrap v3.0テンプレート
define('M3_TEMPLATE_JQUERY_MOBILE_10',	20);		// jQuery Mobile v1.0テンプレート

// 描画出力タイプ
define('M3_RENDER_JOOMLA_OLD',		'joomla_old');		// Joomla! 1.0テンプレート
define('M3_RENDER_JOOMLA_NEW',		'joomla_new');		// Joomla! 1.5以上のテンプレート
define('M3_RENDER_BOOTSTRAP',		'bootstrap');		// Bootstrap 3.0テンプレート
define('M3_RENDER_JQUERY_MOBILE',	'jquery mobile');	// jQuery Mobileテンプレート
	
// ファイル拡張子
define('M3_TEMPLATE_FILE_EXTENSION', '.tmpl.html');		// テンプレートファイルの拡張子(suffix)

// システムに関係するHTMLタグ名
define('M3_SYSTEM_TAG_CHANGE_TEMPLATE',	'_sel_template');		// テンプレート選択用メニュー

// クッキー保存値
define('M3_COOKIE_CLIENT_ID',			'CID');				// クライアントID
define('M3_COOKIE_EXPIRE_CLIENT_ID',	360);				// クライアントIDの保存期間(日)
define('M3_COOKIE_LANG',				'LANG');			// 言語
define('M3_COOKIE_CART_ID',				'CARTID');			// EコマースカートID
define('M3_COOKIE_DISP_ID',				'DISPID');			// クライアント画面設定ID
define('M3_COOKIE_AUTO_LOGIN',			'LOGIN');			// 自動ログインID

// 表示データフォーマット
define('M3_VIEW_FORMAT_DATETIME',		'Y/m/d H:i:s');		// 日時
define('M3_VIEW_FORMAT_DATE',			'Y/m/d');			// 日付
define('M3_VIEW_FORMAT_TIME',			'H:i:s');			// 時間

// 表示データタイプ
// 機能タイプ
define('M3_VIEW_TYPE_DASHBOARD',	'dboard');				// ダッシュボード
define('M3_VIEW_TYPE_SEARCH',		'search');				// 検索結果
define('M3_VIEW_TYPE_COMMERCE',		'commerce');			// Eコマース
define('M3_VIEW_TYPE_CALENDAR',		'calendar');			// カレンダー
// コンテンツタイプ
define('M3_VIEW_TYPE_NEWS',			'news');				// 新着情報
define('M3_VIEW_TYPE_MEMBER',		'member');				// 会員情報
define('M3_VIEW_TYPE_CONTENT',		'content');				// 汎用コンテンツ
define('M3_VIEW_TYPE_PRODUCT',		'product');				// 商品情報
define('M3_VIEW_TYPE_BBS',			'bbs');					// BBS
define('M3_VIEW_TYPE_BLOG',			'blog');				// ブログ
define('M3_VIEW_TYPE_WIKI',			'wiki');				// Wiki
define('M3_VIEW_TYPE_USER',			'user');				// ユーザ作成コンテンツ
define('M3_VIEW_TYPE_EVENT',		'event');				// イベント情報
define('M3_VIEW_TYPE_PHOTO',		'photo');				// フォトギャラリー
// すべての機能タイプ
$M3_ALL_FEATURE_TYPE	= array(	M3_VIEW_TYPE_DASHBOARD,			// ダッシュボード
									M3_VIEW_TYPE_SEARCH,			// 検索結果
									M3_VIEW_TYPE_COMMERCE,			// Eコマース
									M3_VIEW_TYPE_CALENDAR);			// カレンダー
// すべてのコンテンツタイプ
$M3_ALL_CONTENT_TYPE	= array(	M3_VIEW_TYPE_NEWS,				// 新着情報
									M3_VIEW_TYPE_MEMBER,			// 会員情報
									M3_VIEW_TYPE_CONTENT,			// 汎用コンテンツ
									M3_VIEW_TYPE_PRODUCT,			// 商品情報
									M3_VIEW_TYPE_BBS,				// BBS
									M3_VIEW_TYPE_BLOG,				// ブログ
									M3_VIEW_TYPE_WIKI,				// Wiki
									M3_VIEW_TYPE_USER,				// ユーザ作成コンテンツ
									M3_VIEW_TYPE_EVENT,				// イベント情報
									M3_VIEW_TYPE_PHOTO);			// フォトギャラリー

// コンテンツ取得用キー
define('M3_CONTENT_KEY_MAINTENANCE',	'MAINTENANCE');				// メンテナンス画面用
define('M3_CONTENT_KEY_ACCESS_DENY',	'ACCESS_DENY');				// アクセス不可画面用
define('M3_CONTENT_KEY_PAGE_NOT_FOUND',	'PAGE_NOT_FOUND');				// ページが見つからない画面用

// ウィジェットタイプ
define('M3_WIDGET_TYPE_MENU',	'menu');				// メニュー
define('M3_WIDGET_TYPE_USER',	'user');				// ユーザ作成コンテンツ

// 作業用
define('M3_SYSTEM_WORK_DIR_PATH', 				'/tmp');			// 作業用ディレクトリ
define('M3_SYSTEM_WORK_UPLOAD_FILENAME_HEAD',	'm3_upload_');		// アップロードファイル一時退避用ファイル名ヘッダ
define('M3_SYSTEM_WORK_DOWNLOAD_FILENAME_HEAD',	'm3_download_');	// ダウンロードファイル一時退避用ファイル名ヘッダ
define('M3_SYSTEM_WORK_BACKUP_FILENAME_HEAD',	'm3_backup_');		// バックアップファイル一時退避用ファイル名ヘッダ
define('M3_SYSTEM_WORK_DIRNAME_HEAD',			'm3_tmp_');			// 一時ディレクトリ名ヘッダ

// ######### DB定義 ###########
define('M3_DB_PGSQL_PREFIX',	'pgsql_');				// DB区別用プレフィクス
define('M3_DB_MYSQL_PREFIX',	'mysql_');				// DB区別用プレフィクス
define('M3_DB_TYPE_PGSQL',		'pgsql');				// DB種別(PostgreSQL)
define('M3_DB_TYPE_MYSQL',		'mysql');				// DB種別(MySQL)
define('M3_TIMESTAMP_INIT_VALUE_MYSQL',		'0000-00-00 00:00:00');	// Timestamp型データの初期化値
define('M3_TIMESTAMP_INIT_VALUE_PGSQL',		'0001-01-01 00:00:00');	// Timestamp型データの初期化値
define('M3_DATE_INIT_VALUE_MYSQL',			'0000-00-00');			// Date型データの初期化値
define('M3_DATE_INIT_VALUE_PGSQL',			'0001-01-01');			// Date型データの初期化値

// DBフィールド定義
define('M3_TB_FIELD_SYSTEM_NAME',		'system_name');				// システム名称
define('M3_TB_FIELD_SYSTEM_VERSION',	'system_version');			// システムバージョン
define('M3_TB_FIELD_DB_VERSION',		'db_version');				// DBバージョン
define('M3_TB_FIELD_DB_UPDATE_DT',		'db_update_dt');				// DB更新日時
// HTMLヘッダに出力する項目
define('M3_TB_FIELD_SITE_TITLE',		'head_title');				// サイトのタイトル
define('M3_TB_FIELD_SITE_DESCRIPTION',	'head_description');		// サイトの説明
define('M3_TB_FIELD_SITE_KEYWORDS',		'head_keywords');			// 検索エンジン用キーワード
define('M3_TB_FIELD_SITE_ROBOTS',		'head_robots');				// 検索ロボットへの指示
// サイト定義
define('M3_TB_FIELD_SITE_NAME',			'site_name');				// サイト名称
define('M3_TB_FIELD_SITE_SLOGAN',		'site_slogan');				// サイトスローガン
define('M3_TB_FIELD_SITE_OWNER',		'site_owner');				// サイト所有者
define('M3_TB_FIELD_SITE_COPYRIGHT', 	'site_copyright');			// サイトコピーライト
define('M3_TB_FIELD_SITE_EMAIL',		'site_email');				// サイトEメール

// ######### HTTPリクエストパラメータ ###########
// キー
define('M3_REQUEST_PARAM_PAGE_SUB_ID',      		'sub');				// ページサブID
define('M3_REQUEST_PARAM_PAGE_CONTENT_ID',      	'id');				// ページコンテンツID
define('M3_REQUEST_PARAM_WIDGET_ID',				'widget');			// ウィジェットID
define('M3_REQUEST_PARAM_TEMPLATE_ID',				'template');		// テンプレートID
define('M3_REQUEST_PARAM_URL',						'url');				// リンク先等のURL
define('M3_REQUEST_PARAM_STAMP',					'stamp');			// 公開発行ID
define('M3_REQUEST_PARAM_OPTION',					'opt');				// 通信オプション
define('M3_REQUEST_PARAM_OPERATION_COMMAND',		'cmd');				// 実行処理
define('M3_REQUEST_PARAM_OPERATION_WIKI_COMMAND',	'wcmd');			// Wikiコマンド実行
define('M3_REQUEST_PARAM_OPERATION_TASK',			'task');			// 画面指定用タスク
define('M3_REQUEST_PARAM_OPERATION_ANCHOR',			'anchor');			// 画面指定用アンカー
define('M3_REQUEST_PARAM_OPERATION_ACT',			'act');				// クライアントからの実行処理
define('M3_REQUEST_PARAM_OPERATION_LANG',			'lang');			// 言語指定表示
define('M3_REQUEST_PARAM_SERIAL_NO',      			'serial');			// シリアル番号
define('M3_REQUEST_PARAM_PAGE_NO',      			'page');			// ページ番号
define('M3_REQUEST_PARAM_LIST_NO',      			'list');			// 一覧番号
define('M3_REQUEST_PARAM_ITEM_NO',      			'no');				// 項目番号
define('M3_REQUEST_PARAM_OPERATION_TODO',      		'todo');			// 指定ウィジェットに実行させる処理
define('M3_REQUEST_PARAM_FROM',						'from');			// メッセージの送信元ウィジェットID。遷移元画面。
define('M3_REQUEST_PARAM_VIEW_STYLE',				'style');			// 表示スタイル
define('M3_REQUEST_PARAM_FORWARD',					'forward');			// 画面遷移用パラメータ
define('M3_REQUEST_PARAM_ADMIN_KEY',				'adminkey');		// 管理者一時キー
define('M3_REQUEST_PARAM_OPEN_BY',					'openby');			// ウィンドウの開き方
define('M3_REQUEST_PARAM_SHOW_HEADER',				'head');			// ヘッダ部表示制御
define('M3_REQUEST_PARAM_SHOW_FOOTER',				'foot');			// フッタ部表示制御
define('M3_REQUEST_PARAM_SHOW_MENU',				'menu');			// メニュー部表示制御
define('M3_REQUEST_PARAM_KEYWORD',					'keyword');			// 検索キーワード
define('M3_REQUEST_PARAM_HISTORY',					'history');			// 履歴インデックスNo
define('M3_REQUEST_PARAM_DEF_PAGE_ID',      		'_page');			// ページID(画面編集用)
define('M3_REQUEST_PARAM_DEF_PAGE_SUB_ID',      	'_sub');			// ページサブID(画面編集用)
define('M3_REQUEST_PARAM_PAGE_DEF_SERIAL',			'_defserial');		// ページ定義のレコードシリアル番号(設定画面起動時)
define('M3_REQUEST_PARAM_PAGE_DEF_CONFIG_ID',		'_defconfig');		// ページ定義のウィジェット定義ID(設定画面起動時)
define('M3_REQUEST_PARAM_FORM_ID',					'_formid');			// フォーム識別用
define('M3_REQUEST_PARAM_BACK_URL',					'_backurl');		// 戻り先URL
define('M3_REQUEST_PARAM_BACKUP_URL',				'_backupurl');		// URL退避用(画面編集時)
define('M3_REQUEST_PARAM_SERVER',					'_server');			// サーバ指定
define('M3_REQUEST_PARAM_FILE_ID',      			'fileid');			// ファイルID
define('M3_REQUEST_PARAM_CATEGORY_ID',      		'categoryid');		// カテゴリID(共通)
define('M3_REQUEST_PARAM_WIDTH',      				'width');			// 幅
define('M3_REQUEST_PARAM_HEIGHT',      				'height');			// 高さ
define('M3_REQUEST_PARAM_COMMENT_ID',      			'commentid');		// コメント識別用ID
define('M3_REQUEST_PARAM_CONTENT_ID',      			'contentid');		// 汎用コンテンツID
define('M3_REQUEST_PARAM_CONTENT_ID_SHORT',      	'cid');				// 汎用コンテンツID(略式)
define('M3_REQUEST_PARAM_PRODUCT_ID',      			'productid');		// 製品ID
define('M3_REQUEST_PARAM_PRODUCT_ID_SHORT',      	'pid');				// 製品ID(略式)
define('M3_REQUEST_PARAM_BLOG_ID',      			'blogid');			// ブログID
define('M3_REQUEST_PARAM_BLOG_ID_SHORT',      		'bid');				// ブログID(略式)
define('M3_REQUEST_PARAM_BLOG_ENTRY_ID',      		'entryid');			// ブログ記事ID
define('M3_REQUEST_PARAM_BLOG_ENTRY_ID_SHORT',      'eid');				// ブログ記事ID(略式)
define('M3_REQUEST_PARAM_BBS_ID',      				'bbsid');			// 掲示板投稿記事ID
define('M3_REQUEST_PARAM_BBS_ID_SHORT',      		'sid');				// 掲示板投稿記事ID(略式)
define('M3_REQUEST_PARAM_BBS_THREAD_ID',      		'threadid');		// 掲示板投稿スレッドID
define('M3_REQUEST_PARAM_BBS_THREAD_ID_SHORT',      'tid');				// 掲示板投稿スレッドID(略式)
define('M3_REQUEST_PARAM_USER_ID',      			'userid');			// ユーザ作成コンテンツID
define('M3_REQUEST_PARAM_USER_ID_SHORT',      		'uid');				// ユーザ作成コンテンツID(略式)
define('M3_REQUEST_PARAM_ROOM_ID',      			'roomid');			// ユーザ作成コンテンツ区画ID
define('M3_REQUEST_PARAM_ROOM_ID_SHORT',      		'rid');				// ユーザ作成コンテンツ区画ID(略式)
define('M3_REQUEST_PARAM_EVENT_ID',      			'eventid');			// イベントID
define('M3_REQUEST_PARAM_EVENT_ID_SHORT',      		'vid');				// イベントID(略式)
define('M3_REQUEST_PARAM_PHOTO_ID',      			'photoid');			// 画像ID
define('M3_REQUEST_PARAM_PHOTO_ID_SHORT',      		'hid');				// 画像ID(略式)

// ######### HTTPレスポンスパラメータ ###########
define('M3_RESPONSE_PARAM_STATUS',      			'status');			// 処理実行結果

// 値
// 実行オペレーション
define('M3_REQUEST_CMD_INIT_DB',      				'initdb');				// DB初期作成
define('M3_REQUEST_CMD_SHOW_POSITION',      		'showposition');		// ウィジェット組み込み位置を表示
define('M3_REQUEST_CMD_SHOW_NO_POSITION',      		'shownoposition');		// ウィジェット組み込み位置なしで表示
define('M3_REQUEST_CMD_SHOW_POSITION_WITH_WIDGET',  'showpositionwithwidget');		// ウィジェット組み込み位置を表示(ウィジェット込み)
define('M3_REQUEST_CMD_GET_WIDGET_INFO',  			'getwidgetinfo');		// ウィジェット各種情報取得(AJAX用)
define('M3_REQUEST_CMD_SHOW_PHPINFO',      			'showphpinfo');			// phpinfo()を表示
define('M3_REQUEST_CMD_FIND_WIDGET',				'findwidget');			// ウィジェットを検索し、前面表示
define('M3_REQUEST_CMD_SHOW_WIDGET',      			'showwidget');			// ウィジェット単体表示
define('M3_REQUEST_CMD_DO_WIDGET',      			'dowidget');			// ウィジェット単体オペレーション
define('M3_REQUEST_CMD_CONFIG_WIDGET',      		'configwidget');		// ウィジェットの設定
define('M3_REQUEST_CMD_PREVIEW',      				'preview');				// サイトのプレビューを表示
define('M3_REQUEST_CMD_LOGIN',      				'login');				// ログイン
define('M3_REQUEST_CMD_LOGOUT',      				'logout');				// ログアウト
define('M3_REQUEST_CMD_CHANGE_TEMPLATE',			'changetemplate');		// テンプレートの変更
define('M3_REQUEST_CMD_RSS',						'rss');					// RSS配信
define('M3_REQUEST_CMD_CSS',						'css');					// CSS生成
// 共通
define('M3_REQUEST_VALUE_ON',						'on');					// on値
define('M3_REQUEST_VALUE_OFF',						'off');					// off値
// セパレータ
define('M3_TODO_SEPARATOR',							';');					// TODO用セパレータ

// ######### セッション保存パラメータ ###########
define('M3_SESSION_CURRENT_TEMPLATE',		'_current_template');			// 現在表示中のテンプレート
define('M3_SESSION_USER_INFO',      		'_user_info');					// 現在ログイン中のユーザ情報
define('M3_SESSION_POST_TICKET',      		'_ticket');						// POSTデータ確認用
define('M3_SESSION_WIDGET',      			'_widget_');					// 各ウィジェット用(+ウィジェットID)

// ######### システムの定義 ###########
define('M3_HTML_CHARSET',	'utf-8');				// キャラクターセット
define('M3_TIMEZONE',		'Asia/Tokyo');			// タイムゾーン

// ######### サイト定義の読み込み ###########
require_once(dirname(__FILE__) . '/siteDef.php');

// ######### 時刻設定 ###########
define('M3_MTIME', microtime(true));					// 現在のマイクロ秒タイムスタンプ

// ######### PHPの設定 ###########
if (version_compare(PHP_VERSION, '5.3.0') < 0){
	set_magic_quotes_runtime(0);
}
date_default_timezone_set(M3_TIMEZONE);	// デフォルトタイムゾーンの設定

// ######### グローバル変数の作成 ###########
$HELP = array();			// ヘルプ文字列
$TABLE_FIELDS = array();	// テーブルフィールド名

// 共通関数
require_once(dirname(__FILE__) . '/common/m3Func.php');

// ######### グローバルオブジェクト(各マネージャー)の作成 ###########
require_once(dirname(__FILE__) . '/manager/logManager.php');
require_once(dirname(__FILE__) . '/manager/instanceManager.php');
require_once(dirname(__FILE__) . '/manager/systemManager.php');
require_once(dirname(__FILE__) . '/manager/envManager.php');
require_once(dirname(__FILE__) . '/manager/opeLogManager.php');
require_once(dirname(__FILE__) . '/manager/cacheManager.php');
require_once(dirname(__FILE__) . '/manager/launchManager.php');
require_once(dirname(__FILE__) . '/manager/accessManager.php');
require_once(dirname(__FILE__) . '/manager/configManager.php');
require_once(dirname(__FILE__) . '/manager/errorManager.php');
require_once(dirname(__FILE__) . '/manager/pageManager.php');
require_once(dirname(__FILE__) . '/manager/requestManager.php');
require_once(dirname(__FILE__) . '/manager/designManager.php');
require_once(dirname(__FILE__) . '/manager/dispManager.php');

// マネージャーの作成、初期化
$gLogManager 		= new LogManager();		// これよりロギング可能
$gInstanceManager 	= new InstanceManager();
$gSystemManager 	= new SystemManager();
$gEnvManager 		= new EnvManager();		// システムの動作環境管理
$gOpeLogManager 	= new OpeLogManager();
$gCacheManager		= new CacheManager();
$gLaunchManager 	= new LaunchManager();
$gAccessManager 	= new AccessManager();
$gConfigManager 	= new ConfigManager();
$gErrorManager 		= new ErrorManager();
$gRequestManager 	= new RequestManager();
$gPageManager 		= new PageManager();
$gDesignManager 	= new DesignManager();
$gDispManager		= new DispManager();

// ######### ライブラリ用設定 ###########
define('CALENDAR_FIRST_DAY_OF_WEEK', 0);	// カレンダーは日曜が先頭
define('CALENDAR_ROOT', $gEnvManager->getLibPath() . '/Calendar-0.5.5/');		// カレンダーライブラリ
?>
