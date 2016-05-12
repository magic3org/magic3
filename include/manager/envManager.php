<?php
/**
 * 環境取得用マネージャー
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
require_once(M3_SYSTEM_INCLUDE_PATH . '/common/version.php');	// システムバージョンクラス
require_once(M3_SYSTEM_INCLUDE_PATH . '/common/userInfo.php');		// ユーザ情報クラス
require_once(M3_SYSTEM_INCLUDE_PATH . '/common/core.php');

class EnvManager extends Core
{
	public  $sysVersion;		// システムバージョンオブジェクト
	private $currentTemplateId;	// 現在のテンプレートID(ディレクトリ名)
	private $currentSubTemplateId;	// 現在のサブテンプレートID
	private $currentTemplateType;	// 現在のテンプレートのタイプ
	private $currentTemplateGenerator;		// テンプレート作成アプリケーション
	private $currentTemplateVersion;		// テンプレートバージョン
	private $currentTemplateCleanType;	// 現在のテンプレートのクリーンタイプ
	private $currentTemplateUseBootstrap;	// 現在のテンプレートでBootstrapライブラリを使用するかどうか
	private $currentRenderType;		// 現在のビュー作成タイプ
	private $currentWidgetObj;		// 現在実行中のウィジェットオブジェクト
	private $currentWidgetId;	// 現在作成中のウィジェットId
	private $currentWidgetConfigId;	// 現在作成中のウィジェットの定義ID
	private $currentIWidgetConfigId;	// 現在作成中のインナーウィジェットの定義ID
	private $currentIWidgetId;	// 現在作成中のインナーウィジェットId
	private $currentPageId;		// 現在のページId
	private $currentPageSubId;	// 現在のページサブId
	private $currentPageDefSerial;	// 現在処理を行っているページ定義のレコードのシリアル番号
	private $currentPageDefRec;		// 現在処理中のウィジェットのページ定義レコード
	private $defaultPageSubId;	// デフォルトのページサブId
	private $currentPageDeviceType;		// 現在のページの端末タイプ
	private $currentWidgetPrefix;	// 現在作成中のウィジェットのプレフィックス文字列
	private $currentWidgetTitle;	// 現在作成中のウィジェットのタイトル文字列
	private $currentWidgetStyle;	// 現在作成中のウィジェットのスタイル文字列
	private $currentWidgetJoomlaParam;	// 現在作成中のウィジェットのJoomla用パラメータ
	private $currentWidgetParams = array();		// 現在作成中のウィジェットのその他パラメータ
	private $isCurrentWidgetShared;	// 現在作成中のウィジェットが共通ウィジェットかどうか
	private $currentDomainRootUrl;	// マルチドメイン運用時の現在のルートURL
	private $defaultLanguage;	// デフォルト言語(システムで固定)
	private $currentLanguage;	// 現在の言語(ユーザによって可変)
	private $defaultLocale;		// デフォルトのロケール
	private $currentLocale;		// 現在のロケール(ユーザによって可変)
	private $multiLanguage;		// 多言語対応かどうか
	private $adminDefaultTheme;	// 管理画面のデフォルトテーマ
	private $accessPath;		// アクセスポイントパス
	private $accessDir;			// アクセスポイントディレクトリ(空文字列=PC用、s=スマートフォン用、m=携帯用)
 	private $db;				// DBオブジェクト
	private $canUseDbSession;	// DBセッションが使用できるかどうか
	private $canUseDb;			// DBが使用可能状態にあるかどうか
	private $canUseCookie;		// クッキーが使用可能かどうか
	private $mobileUseSession;	// 携帯でセッション管理を使用するかどうか
	private $canChangeLang;		// 言語変更可能かどうか
	private $useSsl;			// SSL機能を使用するかどうか
	private $useSslAdmin;		// 管理画面にSSL機能を使用するかどうか
	private $sslUrl;			// SSL用URL
	private $siteName;			// サイト名称
	private $siteOwner;			// サイト所有者
	private $siteCopyRight;		// サイトコピーライト
	private $siteEmail;			// サイトEメール
	private $widgetLog;			// ウィジェット実行ログを残すかどうか
	private $multiDomain;		// マルチドメイン運用かどうか
	private $isPcSite;			// PC用URLアクセスかどうか
	private $isMobileSite;		// 携帯用URLアクセスかどうか
	private $isSmartphoneSite;	// スマートフォン用URLへのアクセスかどうか
	private $isSubWidget;		// サブウィジェットの起動かどうか
	private $isServerConnector;	// サーバ接続かどうか
	private $mobileEncoding;	// 携帯用の入力、出力エンコーディング
	private $workDir;			// 作業用ディレクトリ
	private $userAgent = array();	// アクセス端末の情報
	private $menuAttr = array();		// メニューの表示属性
	private $joomlaDocument;		// Joomla!ドキュメント
	private $joomlaMenuContent;		// Joomla!v1.5用メニューコンテンツ
	private $joomlaMenuData;		// Joomla!v2.5用メニュー階層データ
	private $joomlaPageNavData;		// Joomla!v2.5用ページ前後遷移データ
	private $joomlaPaginationData;	// Joomla!v2.5用ページ番号遷移データ
	private $joomlaViewData;		// Joomla!ビュー作成用データ
	private $remoteContent = array();			// リモート表示コンテンツ
	private $defaultLacaleArray;	// デフォルトのロケール取得用
	private $selectedMenuItems = array();				// 現在選択中のメニュー項目
	const DEFAULT_LOCALE = 'ja_JP';			// デフォルトロケール
	const DEFAULT_CSV_DELIM_CODE = 'csv_delim_code';		// デフォルトのCSV区切り文字コード
	const DEFAULT_CSV_NL_CODE = 'csv_nl_code';		// デフォルトのCSV改行コード
	const DEFAULT_CSV_FILE_SUFFIX = 'csv_file_suffix';		// デフォルトのCSVファイル拡張子
	const MULTI_LANGUAGE = 'multi_language';		// 多言語対応かどうか
	const MOBILE_ENCODING = 'mobile_encoding';		// 携帯用入出力エンコーディング
	const MOBILE_CHARSET = 'mobile_charset';		// 携帯用HTML上のエンコーディング表記
	const DEFAULT_THEME_CSS_FILE = 'jquery-ui.custom.css';		// テーマファイル
	const CONFIG_ID_WORK_DIR = 'work_dir';			// 作業用ディレクトリ
	const DEFAULT_PAGE_ID = 'index';					// デフォルトのページID
	const DEFAULT_REGIST_PAGE_ID = 'regist';		// デフォルトの登録機能用ページID
	const DEFAULT_MOBILE_PAGE_ID = 'm_index';					// 携帯用デフォルトのページID
	const DEFAULT_SMARTPHONE_PAGE_ID = 's_index';				// スマートフォン用デフォルトのページID
	const DEFAULT_ADMIN_PAGE_ID = 'admin_index';		// デフォルトの管理機能用ページID
	const USER_AGENT_TYPE_PC = 'pc';					// アクセス端末の種類(PC)
	const USER_AGENT_TYPE_MOBILE = 'mobile';			// アクセス端末の種類(携帯)
	const CF_CSV_DOWNLOAD_ENCODING = 'csv_download_encoding';			// CSVダウンロードエンコーディング
	const CF_CSV_UPLOAD_ENCODING = 'csv_upload_encoding';			// CSVアップロードエンコーディング
	const CF_MOBILE_USE_SESSION = 'mobile_use_session';		// 携帯でセッション管理を行うかどうか
	const CF_USE_SSL = 'use_ssl';		// SSL機能を使用するかどうか
	const CF_USE_SSL_ADMIN = 'use_ssl_admin';		// 管理画面にSSL機能を使用するかどうか
	const CF_SSL_URL = 'ssl_root_url';				// SSL用のルートURL
	const CF_DEFAULT_LANG = 'default_lang';			// デフォルト言語
	const CF_MULTI_DOMAIN = 'multi_domain';			// マルチドメイン運用かどうか
	const CF_SITE_SMARTPHONE_URL = 'site_smartphone_url';		// スマートフォン用サイトURL
	const CF_SITE_MOBILE_URL = 'site_mobile_url';		// 携帯用サイトURL
	const CF_REALTIME_SERVER_PORT = 'realtime_server_port';		// リアルタイムサーバ用ポート番号
	const DEFAULT_SITE_NAME = 'サイト名未設定';		// 管理画面用のデフォルトサイト名
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// パラメータエラーチェック
		if (strEndsWith(M3_SYSTEM_ROOT_URL, '/')){
			$errMsg = '設定の不正: M3_SYSTEM_ROOT_URLの値の最後の「/」を削除してください。ファイル=include/siteDef.php';
 			$this->gLog->error(__METHOD__, $errMsg);
		}
			
		// データ初期化
		$this->accessPath = '';		// アクセスポイントパス
		$this->accessDir = '';		// アクセスポイントディレクトリ
		$this->defaultLacaleArray = array('ja'		=> 'ja_JP',
											'en'	=> 'en_US');	// デフォルトのロケール取得用
		$this->currentWidgetId = '';	// 現在作成中のウィジェットId

		// デフォルトの作業用ディレクトリ取得
		if (function_exists('sys_get_temp_dir')){		// PHP 5.2.1以上
			$this->workDir = sys_get_temp_dir();
		} else {
      		$this->workDir = getenv('TMP');
			if (empty($this->workDir)) $this->workDir = getenv('TEMP');
			if (empty($this->workDir)) $this->workDir = getenv('TMPDIR');
			if (empty($this->workDir)){
				$temp = tempnam(__FILE__, '');
				if (file_exists($temp)){
					unlink($temp);
					$this->workDir = dirname($temp);
				}
			}
		}
		$this->workDir = rtrim($this->workDir, DIRECTORY_SEPARATOR);		// 最後の「/」「\」を除く
		if (!file_exists($this->workDir)) $this->workDir = M3_SYSTEM_WORK_DIR_PATH;// 作業用ディレクトリデフォルト値
		
		// システムバージョンオブジェクト作成
		$this->sysVersion = new m3Version();
		
		// システムDBオブジェクト取得
		$this->db = $this->gInstance->getSytemDbObject();
		
		// ######## DBの接続チェック ########
		if (defined('M3_STATE_IN_INSTALL')){		// システムがインストールモードで起動のとき
			$this->canUseDb = false;			// DBは使用できない
		} else {
			// システム名称、バージョンを取得
			$status = $this->db->getDisplayErrMessage();	// 出力状態を取得
			$this->db->displayErrMessage(false);		// 画面へのエラー出力を抑止
			//$value = $this->db->getSystemConfig(M3_TB_FIELD_SYSTEM_NAME);
			$ret = $this->gSystem->_loadSystemConfig();
			$this->db->displayErrMessage($status);		// 抑止解除
			// 値が取得できたときは、セッションDBテーブルも作成されているとする
			/*if ($value == ''){
				$this->canUseDbSession = false;
				$this->canUseDb = false;			// DBは使用できない
			} else {
				$this->canUseDbSession = true;
				$this->canUseDb = true;			// DBは使用可能
				
				// システム関係のパラメータを取得
				$this->loadSystemParams();
			}*/
			if ($ret){
				$this->canUseDbSession = true;
				$this->canUseDb = true;			// DBは使用可能
				
				// システム関係のパラメータを取得
				$this->loadSystemParams(false);		// DBから再取得しない
			} else {
				$this->canUseDbSession = false;
				$this->canUseDb = false;			// DBは使用できない
			}
		}
		// 日本語処理関係
		if (extension_loaded('mbstring')){	// mbstring使用可能
			if (version_compare(PHP_VERSION, '5.6.0') < 0){
				ini_set('mbstring.http_input',                  'pass');
				ini_set('mbstring.http_output',                 'pass');
			}
			ini_set('mbstring.encoding_translation',        'Off');		// ここでは設定を変更できない？
			ini_set('mbstring.substitute_character',		'none');	// 無効な文字の代替出力
			ini_set('mbstring.func_overload',               '0');
			
			if (function_exists('mb_language')) mb_language("Japanese");
			if (function_exists('mb_internal_encoding')) mb_internal_encoding("UTF-8");
		}
		// 現在のルートURL初期化。外部アクセスのときはこのURLを使用。
		$this->currentDomainRootUrl = M3_SYSTEM_ROOT_URL;
	}
	/**
	 * システム関係のパラメータを再取得
	 *
	 * @param bool $reloadFromDb		DBから再取得するかどうか
	 * @return 							なし
	 */
	public function loadSystemParams($reloadFromDb = true)
	{
		if ($reloadFromDb) $this->gSystem->_loadSystemConfig();
		
		// デフォルト値取得
		$this->defaultLanguage = $this->gSystem->getSystemConfig(self::CF_DEFAULT_LANG);// デフォルト言語
		$this->defaultLocale = $this->defaultLacaleArray[$this->defaultLanguage];		// デフォルトのロケール
		if (empty($this->defaultLocale)) $this->defaultLocale = self::DEFAULT_LOCALE;
		$this->currentLanguage = $this->defaultLanguage;
		$this->currentLocale = $this->defaultLocale;			// 現在のロケール
		$this->canChangeLang = $this->gSystem->canChangeLang();// 言語変更可能かどうか
		$this->multiLanguage = $this->gSystem->getSystemConfig(self::MULTI_LANGUAGE);		// 多言語対応かどうか
		$this->multiDomain	= $this->gSystem->getSystemConfig(self::CF_MULTI_DOMAIN);		// マルチドメイン運用かどうか
		
		$this->adminDefaultTheme = $this->gSystem->adminDefaultTheme();	// 管理画面のデフォルトテーマ
		$this->useSsl = $this->gSystem->getSystemConfig(self::CF_USE_SSL);		// SSL機能を使用するかどうか
		$this->useSslAdmin = $this->gSystem->getSystemConfig(self::CF_USE_SSL_ADMIN);		// 管理画面にSSL機能を使用するかどうか
		$this->sslUrl = $this->gSystem->getSystemConfig(self::CF_SSL_URL);			// SSL用URL
		$this->mobileEncoding = $this->gSystem->getSystemConfig(self::MOBILE_ENCODING);	// 携帯用の入力、出力エンコーディング
		$this->mobileCharset = $this->gSystem->getSystemConfig(self::MOBILE_CHARSET);		// 携帯用HTML上のエンコーディング表記
		$this->mobileUseSession = $this->gSystem->getSystemConfig(self::CF_MOBILE_USE_SESSION);	// 携帯でセッション管理を使用するかどうか
		$value = $this->gSystem->getSystemConfig(self::CONFIG_ID_WORK_DIR);// 作業用ディレクトリ
		if (!empty($value)) $this->workDir = $value;
	}
	/**
	 * マルチドメイン用設定初期化
	 *
	 * @return 							なし
	 */
	public function initMultiDomain()
	{
		// マルチドメイン運用の場合はルートURLを設定
		if ($this->multiDomain){
			$url = '';
			if ($this->isSmartphoneSite){
				$url = $this->gSystem->getSystemConfig(self::CF_SITE_SMARTPHONE_URL);	// スマートフォン用サイトURL
			} else if ($this->isMobileSite){
				$url = $this->gSystem->getSystemConfig(self::CF_SITE_MOBILE_URL);		// 携帯用サイトURL
			}
			if (!empty($url)) $this->currentDomainRootUrl = $url;
		}
	}
	/**
	 * デバッグ出力を行うかどうか
	 */
	public function getSystemDebugOut()
	{
		return M3_SYSTEM_DEBUG_OUT;
	}

	// ##################### システム全体のパス環境 #####################
	/**
	 * システムルートディレクトリへのパスを取得
	 *
	 * @return string			パス
	 */
	public function getSystemRootPath()
	{
		return M3_SYSTEM_ROOT_PATH;
	}
	/**
	 * 管理用ディレクトリへのパスを取得
	 *
	 * @return string			パス
	 */
	public function getAdminPath()
	{
		return M3_SYSTEM_ROOT_PATH . DIRECTORY_SEPARATOR . M3_DIR_NAME_ADMIN;
	}
	/**
	 * includeディレクトリへのパスを取得
	 *
	 * @return string			パス
	 */
	public function getIncludePath()
	{
		return M3_SYSTEM_INCLUDE_PATH;
	}
	/**
	 * インナーウィジェット用ディレクトリへのパスを取得
	 */
/*	public function getIWidgetsPath()
	{
		return M3_SYSTEM_INCLUDE_PATH . DIRECTORY_SEPARATOR . 'iwidgets';
	}*/
	/**
	 * addonsディレクトリへのパスを取得
	 *
	 * @return string			パス
	 */
	public function getAddonsPath()
	{
		return M3_SYSTEM_INCLUDE_PATH . DIRECTORY_SEPARATOR . 'addons';
	}
	/**
	 * cronjobsディレクトリへのパスを取得
	 *
	 * @return string			パス
	 */
	public function getCronjobsPath()
	{
		return M3_SYSTEM_INCLUDE_PATH . DIRECTORY_SEPARATOR . 'cronjobs';
	}
	/**
	 * コンテナクラス用ディレクトリへのパスを取得
	 *
	 * @return string			パス
	 */
	public function getContainerPath()
	{
		return M3_SYSTEM_INCLUDE_PATH . DIRECTORY_SEPARATOR . 'container';
	}
	/**
	 * DBクラス用ディレクトリへのパスを取得
	 *
	 * @return string			パス
	 */
	public function getDbPath()
	{
		return M3_SYSTEM_INCLUDE_PATH . DIRECTORY_SEPARATOR . 'db';
	}
	/**
	 * ライブラリ用ディレクトリへのパスを取得
	 *
	 * @return string			パス
	 */
	public function getLibPath()
	{
		return M3_SYSTEM_INCLUDE_PATH . DIRECTORY_SEPARATOR . 'lib';
	}
	/**
	 * SQL格納用ディレクトリへのパスを取得
	 *
	 * @return string			パス
	 */
	public function getSqlPath()
	{
		return M3_SYSTEM_INCLUDE_PATH . DIRECTORY_SEPARATOR . 'sql';
	}
	/**
	 * テーブル作成用SQL格納用ディレクトリへのパスを取得
	 *
	 * @return string			パス
	 */
	public function getTablesPath()
	{
		return M3_SYSTEM_INCLUDE_PATH . DIRECTORY_SEPARATOR . 'tables';
	}
	/**
	 * Coreディレクトリへのパスを取得
	 *
	 * @return string			パス
	 */
	public function getCorePath()
	{
		return M3_SYSTEM_INCLUDE_PATH . DIRECTORY_SEPARATOR . 'core';
	}
	/**
	 * Commonディレクトリへのパスを取得
	 *
	 * @return string			パス
	 */
	public function getCommonPath()
	{
		return M3_SYSTEM_INCLUDE_PATH . DIRECTORY_SEPARATOR . 'common';
	}
	/**
	 * dataディレクトリへのパスを取得
	 *
	 * @return string			パス
	 */
	public function getDataPath()
	{
		return M3_SYSTEM_INCLUDE_PATH . DIRECTORY_SEPARATOR . 'data';
	}
	/**
	 * Joomla用ルートディレクトリへのパスを取得
	 *
	 * @return string			パス
	 */
	public function getJoomlaRootPath()
	{
		return M3_SYSTEM_INCLUDE_PATH . DIRECTORY_SEPARATOR . 'mos';
	}
	/**
	 * スクリプトファイルディレクトリへのパスを取得
	 *
	 * @return string			パス
	 */
	public function getScriptsPath()
	{
		return M3_SYSTEM_ROOT_PATH . DIRECTORY_SEPARATOR . 'scripts';
	}
	/**
	 * テンプレート用ディレクトリへのパスを取得
	 *
	 * @return string			パス
	 */
	public function getTemplatesPath()
	{
		return M3_SYSTEM_ROOT_PATH . DIRECTORY_SEPARATOR . 'templates';
	}
	/**
	 * リソース用ディレクトリへのパスを取得
	 *
	 * @return string			パス
	 */
	public function getResourcePath()
	{
		return M3_SYSTEM_ROOT_PATH . DIRECTORY_SEPARATOR . 'resource';
	}
	/**
	 * ユーザの種別に対応したリソース用ディレクトリへのパスを取得(外部アプリケーション用)
	 *
	 * @return string		パス
	 */
	public function getResourcePathForUser()
	{
		$path = $this->getResourcePath();
		
		// ユーザのリソース制限が必要な場合は、ユーザごとのディレクトリを設定
		if ($this->isResourceLimitedUser()){
			$path .= '/' . M3_DIR_NAME_HOME . '/' . $this->getCurrentUserAccount();
		}
		return $path;
	}
	/**
	 * 非公開リソースディレクトリへのパスを取得
	 *
	 * @return string			パス
	 */
	public function getPrivateResourcePath()
	{
		return M3_SYSTEM_INCLUDE_PATH . DIRECTORY_SEPARATOR . 'etc';
	}
	/**
	 * widgetsディレクトリへのパスを取得
	 *
	 * 例) /var/www/html/magic3/widgets
	 *
	 * @return string			パス
	 */
	public function getWidgetsPath()
	{
		return M3_SYSTEM_ROOT_PATH . DIRECTORY_SEPARATOR . 'widgets';
	}
	/**
	 * ウィジェットのdbディレクトリへのパスを取得
	 *
	 * 例) /var/www/html/magic3/widgets/xxxxx/include/db
	 *
	 * @param string $widgetId			ウィジェットID
	 * @return string					パス
	 */
	public function getWidgetDbPath($widgetId)
	{
		return $this->getWidgetsPath() . DIRECTORY_SEPARATOR . $widgetId . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'db';
	}
	/**
	 * ウィジェットのcontainerディレクトリへのパスを取得
	 *
	 * 例) /var/www/html/magic3/widgets/xxxxx/include/container
	 *
	 * @param string $widgetId			ウィジェットID
	 * @return string					パス
	 */
	public function getWidgetContainerPath($widgetId)
	{
		return $this->getWidgetsPath() . DIRECTORY_SEPARATOR . $widgetId . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'container';
	}
	/**
	 * ウィジェットのincludeディレクトリへのパスを取得
	 *
	 * 例) /var/www/html/magic3/widgets/xxxxx/include
	 *
	 * @param string $widgetId			ウィジェットID
	 * @return string					パス
	 */
	public function getWidgetIncludePath($widgetId)
	{
		return $this->getWidgetsPath() . DIRECTORY_SEPARATOR . $widgetId . DIRECTORY_SEPARATOR . 'include';
	}
	/**
	 * システムのルートURLを取得
	 */
	public function getRootUrl()
	{
		//return M3_SYSTEM_ROOT_URL;
		return $this->currentDomainRootUrl;
	}
	/**
	 * SSL用のルートURLを取得
	 */
	public function getSslRootUrl()
	{
		// 設定値が空のときはシステムルートURLから生成
		if (empty($this->sslUrl)){
			$url = str_replace('http://', 'https://', M3_SYSTEM_ROOT_URL);
		} else {
			$url = $this->sslUrl;
		}
		return $url;
	}
	/**
	 * 現在のページのシステムのルートURLを取得
	 */
	public function getRootUrlByCurrentPage()
	{
		//$url = M3_SYSTEM_ROOT_URL;
		$url = $this->currentDomainRootUrl;
		if ($this->isAdminDirAccess()){		// 管理画面へのアクセスのとき
			// 管理画面のSSL状態を参照
			//if ($this->useSslAdmin) $url = str_replace('http://', 'https://', $url);
			if ($this->useSslAdmin) $url = $this->getSslRootUrl();
		} else {
			$url = $this->getRootUrlByPage($this->getCurrentPageId(), $this->getCurrentPageSubId());
		}
		return $url;
	}
	/**
	 * 指定のページのシステムのルートURLを取得
	 *
	 * @param string $pageId		ページID
	 * @param string $pageSubId		ページサブID
	 * @return string				URL
	 */
	public function getRootUrlByPage($pageId, $pageSubId)
	{
		global $gPageManager;
		
		//$url = M3_SYSTEM_ROOT_URL;
		$url = $this->currentDomainRootUrl;
		$isSslPage = $gPageManager->isSslPage($pageId, $pageSubId);
		if ($isSslPage) $url = $this->getSslRootUrl();
		return $url;
	}
	/**
	 * リアルタイムサーバ用のURLを取得
	 *
	 * @return string				URL
	 */
	public function getRealtimeServerUrl()
	{
		static $serverUrl;
		
		if (!isset($serverUrl)){
			// リアルタイムサーバ用ポート番号を取得
			$portNo = $this->gSystem->getSystemConfig(self::CF_REALTIME_SERVER_PORT);
			$rootUrl = $this->getRootUrlByCurrentPage();
			
			$parsedUrl = parse_url($rootUrl);
			$url = $parsedUrl['scheme'] . '://' . $parsedUrl['host'];
			if (!empty($portNo)) $url .= ':' . $portNo;
			$serverUrl = $url;
		}
		return $serverUrl;
	}
	/**
	 * 現在のページにSSLが必要かどうかを取得
	 */
	public function isSslByCurrentPage()
	{
		global $gPageManager;
		
		$isSslPage = false;
		if ($this->isAdminDirAccess()){		// 管理画面へのアクセスのとき
			// 管理画面のSSL状態を参照
			if ($this->useSslAdmin) $isSslPage = true;
		} else {
			$isSslPage = $gPageManager->isSslPage($this->getCurrentPageId(), $this->getCurrentPageSubId());
		}
		return $isSslPage;
	}
	/**
	 * widgetsディレクトリへのURLを取得
	 *
	 * 例) http://www.magic3.org/magic3/widgets
	 */
	public function getWidgetsUrl()
	{
		//return M3_SYSTEM_ROOT_URL . '/widgets';
		return $this->currentDomainRootUrl . '/widgets';
	}
	/**
	 * widgetsディレクトリへのSSL用URLを取得
	 *
	 * 例) https://www.magic3.org/magic3/widgets
	 */
	public function getSslWidgetsUrl()
	{
		return $this->getSslRootUrl() . '/widgets';
	}
	/**
	 * リソース用ディレクトリへのURLを取得
	 */
	public function getResourceUrl()
	{
		//return M3_SYSTEM_ROOT_URL . '/resource';
		return $this->currentDomainRootUrl . '/resource';
	}
	/**
	 * ユーザの種別に対応したリソース用ディレクトリへのURLを取得(外部アプリケーション用)
	 *
	 * @return string		URL
	 */
	public function getResourceUrlForUser()
	{
		$url = $this->getResourceUrl();
		
		// ユーザのリソース制限が必要な場合は、ユーザごとのディレクトリを設定
		if ($this->isResourceLimitedUser()){
			$url .= '/' . M3_DIR_NAME_HOME . '/' . $this->getCurrentUserAccount();
		}
		return $url;
	}
	/**
	 * リソース用ディレクトリへのSSL用URLを取得
	 */
	public function getSslResourceUrl()
	{
		return $this->getSslRootUrl() . '/resource';
	}
	/**
	 * 画像用ディレクトリへのパスを取得
	 */
	public function getImagesUrl()
	{
		//return M3_SYSTEM_ROOT_URL . '/images';
		return $this->currentDomainRootUrl . '/images';
	}
	/**
	 * 絵文字画像用ディレクトリへのパスを取得
	 */
	public function getEmojiImagesUrl()
	{
		//return M3_SYSTEM_ROOT_URL . '/images/system/emoji';
		return $this->currentDomainRootUrl . '/images/system/emoji';
	}
	/**
	 * scriptsディレクトリ(共通スクリプトディレクトリ)へのURLを取得
	 *
	 * 例) http://www.magic3.org/magic3/scripts
	 */
	public function getScriptsUrl()
	{
		//return M3_SYSTEM_ROOT_URL . '/scripts';
		return $this->currentDomainRootUrl . '/scripts';
	}
	/**
	 * scriptsディレクトリ(共通スクリプトディレクトリ)へのSSL用URLを取得
	 *
	 * 例) http://www.magic3.org/magic3/scripts
	 */
	public function getSslScriptsUrl()
	{
		return $this->getSslRootUrl() . '/scripts';
	}
	/**
	 * templatesディレクトリ(テンプレートディレクトリ)へのURLを取得
	 *
	 * 例) http://www.magic3.org/magic3/templates
	 */
	public function getTemplatesUrl()
	{
		//return M3_SYSTEM_ROOT_URL . '/templates';
		return $this->currentDomainRootUrl . '/templates';
	}
	/**
	 * templatesディレクトリ(テンプレートディレクトリ)へのSSL用URLを取得
	 *
	 * 例) http://www.magic3.org/magic3/templates
	 */
	public function getSslTemplatesUrl()
	{
		return $this->getSslRootUrl() . '/templates';
	}
	/**
	 * themesディレクトリ(jQueryUIテーマディレクトリ)へのURLを取得
	 *
	 * 例) http://www.magic3.org/magic3/ui/themes
	 */
	public function getThemesUrl()
	{
		//return M3_SYSTEM_ROOT_URL . '/ui/themes';
		return $this->currentDomainRootUrl . '/ui/themes';
	}
	/**
	 * 管理用ディレクトリへのURLパスを取得
	 *
	 * @param bool $removeAdminDir		「admin」ディレクトリ名を削除するかどうか
	 * @return string					管理用ディレクトリへのURLパス
	 */
	public function getAdminUrl($removeAdminDir = false)
	{
		static $url;
		
		if (!isset($url)){
			$url = M3_SYSTEM_ROOT_URL;
			if ($this->useSslAdmin) $url = $this->getSslRootUrl();
		}
		$destUrl = $url;
		if (!$removeAdminDir) $destUrl .= '/' . M3_DIR_NAME_ADMIN;
		return $destUrl;
	}
	/**
	 * システムのデフォルトindexのURLを取得
	 *
	 * @return string					デフォルトURL
	 */
	public function getDefaultUrl()
	{
		//return M3_SYSTEM_ROOT_URL . '/' . M3_FILENAME_INDEX;
		return $this->currentDomainRootUrl . '/' . M3_FILENAME_INDEX;
	}
	/**
	 * システムのPC用デフォルトindexのURLを取得
	 *
	 * @return string					デフォルトURL
	 */
	public function getDefaultPcUrl()
	{
		return M3_SYSTEM_ROOT_URL . '/' . M3_FILENAME_INDEX;
	}
	/**
	 * システムの携帯用デフォルトindexのURLを取得
	 *
	 * @param bool $withMobileParam		携帯用のパラメータを付加するかどうか
	 * @param bool $withFilename		ファイル名を付加するかどうか
	 * @return string					携帯用デフォルトURL
	 */
	public function getDefaultMobileUrl($withMobileParam = false, $withFilename = true)
	{
		static $mobileUrl;
		
		if ($this->multiDomain){			// マルチドメイン運用の場合
			if (!isset($mobileUrl)) $mobileUrl = $this->gSystem->getSystemConfig(self::CF_SITE_MOBILE_URL);		// 携帯用サイトURL

			if (empty($mobileUrl)){
				$url = M3_SYSTEM_ROOT_URL . '/' . M3_DIR_NAME_MOBILE;
			} else {
				$url = $mobileUrl;
			}
		} else {
			$url = M3_SYSTEM_ROOT_URL . '/' . M3_DIR_NAME_MOBILE;
		}
		if ($withFilename) $url .= '/' . M3_FILENAME_INDEX;
		if ($withMobileParam){		// 携帯用のパラメータを付加するとき
			$url = createUrl($url, $this->_getMobileUrlParam());
		}
		return $url;
	}
	/**
	 * システムのスマートフォン用デフォルトindexのURLを取得
	 *
	 * @param bool $withFilename		ファイル名を付加するかどうか
	 * @return string					スマートフォン用デフォルトURL
	 */
	public function getDefaultSmartphoneUrl($withFilename = true)
	{
		static $smartphoneUrl;
		
		if ($this->multiDomain){			// マルチドメイン運用の場合
			if (!isset($smartphoneUrl)) $smartphoneUrl = $url = $this->gSystem->getSystemConfig(self::CF_SITE_SMARTPHONE_URL);	// スマートフォン用サイトURL
			
			if (empty($smartphoneUrl)){
				$url = M3_SYSTEM_ROOT_URL . '/' . M3_DIR_NAME_SMARTPHONE;
			} else {
				$url = $smartphoneUrl;
			}
		} else {
			$url = M3_SYSTEM_ROOT_URL . '/' . M3_DIR_NAME_SMARTPHONE;
		}
		if ($withFilename) $url .= '/' . M3_FILENAME_INDEX;
		return $url;
	}
	/**
	 * システムのデフォルトの管理用indexのURLを取得
	 */
	public function getDefaultAdminUrl()
	{
		return $this->getAdminUrl() . '/' . M3_FILENAME_INDEX;
	}
	/**
	 * 管理画面用jQueryUIテーマのCSSのURLを取得
	 *
	 * @return string		CSSのURL
	 */
	public function getAdminDefaultThemeUrl()
	{
		$themeFile = $this->getThemesUrl() . '/'. $this->adminDefaultTheme . '/'. self::DEFAULT_THEME_CSS_FILE;	// 管理画面用jQueryUIテーマ
		return $themeFile;
	}
	/**
	 * 作業用ディレクトリへのパスを取得
	 *
	 * @return string		作業ディレクトリ
	 */
	public function getWorkDirPath()
	{
		return $this->workDir;
	}
	/**
	 * セッション単位の一時ディレクトリを取得
	 *
	 * @param bool  $createDir	ディレクトリが存在しない場合、作成するかどうか
	 * @return string		一時ディレクトリ
	 */
	function getTempDirBySession($createDir = false)
	{
		$dir = $this->workDir . DIRECTORY_SEPARATOR . session_id();
		if (!file_exists($dir) && $createDir) mkdir($dir, M3_SYSTEM_DIR_PERMISSION, true/*再帰的*/);
		return $dir;
	}
	/**
	 * 一時ディレクトリを取得
	 *
	 * 一時ディレクトリを作成してパスを取得
	 *
	 * @return string		ディレクトリパス(失敗のときは空を返す)
	 */
	public function getTempDir()
	{
		$path = $this->workDir . '/' . M3_SYSTEM_WORK_DIRNAME_HEAD . uniqid();
			
		if (!file_exists($path)){// ディレクトリがないとき
			if (!mkdir($path, M3_SYSTEM_DIR_PERMISSION)){
				$path = $this->workDir . '/' . M3_SYSTEM_WORK_DIRNAME_HEAD . uniqid(rand());
				if (!mkdir($path, M3_SYSTEM_DIR_PERMISSION)) return '';
			}
		}
		return $path;
	}
	// ##################### パス処理 #####################
	/**
	 * サーバのURLを取得
	 *
	 * 例) http://www.magic3.org, http://www.magic3.org:8080
	 */
	public function getServerUrl()
	{
		// クライアントからの情報を元にURLを取得
		if (isset($_SERVER['HTTPS'])){		// SSL通信の場合
			$url = 'https://' . $_SERVER['HTTP_HOST'];
		} else {
			$url = 'http://' . $_SERVER['HTTP_HOST'];
		}
		return $url;
	}
	/**
	 * 現在実行中のスクリプトファイルのURLを取得
	 *
	 * 例) http://www.magic3.org/magic3/index.php
	 */
	public function getCurrentScriptUrl()
	{
		//return $_SERVER["SCRIPT_URI"];		// SCRIPT_URIはサーバによってはundefinedになる
		return $this->getServerUrl() . $_SERVER["PHP_SELF"];
	}
	/**
	 * クライアントから要求されたURI(パラメータ付き)を取得
	 *
	 * 例) http://www.magic3.org/magic3/index.php?aaa=bbb
	 */
	public function getCurrentRequestUri()
	{
		return $this->getServerUrl() . $_SERVER["REQUEST_URI"];
	}
	/**
	 * 現在実行中のスクリプトファイルのパスを取得
	 *
	 * 例) /var/www/html/magic3/index.php
	 */
	public function getCurrentScriptPath()
	{
		return realpath($_SERVER["SCRIPT_FILENAME"]);
	}	
	/**
	 * ドキュメントルートを取得
	 *
	 * 例) /var/www/html
	 */
	public function getDocumentRoot()
	{
		// バーチャルサーバの場合にも対応
		//return $_SERVER["DOCUMENT_ROOT"];
		$name = $_SERVER["SCRIPT_NAME"];
		$filename = $_SERVER["SCRIPT_FILENAME"];
		$dir = substr($filename, 0, strlen($filename) - strlen($name));
		return $dir;
	}
	/**
	 * ドキュメントルートURLを取得
	 *
	 * 例) http://www.magic3.org, http://www.magic3.org:8080
	 */
	public function getDocumentRootUrl()
	{
		$rootUrl = parse_url($this->getRootUrl());
		$url = 'http://' . $rootUrl['host'];
		if (!empty($rootUrl['port'])) $url .= ':' . $rootUrl['port'];
		return $url;
	}
	/**
	 * システムルートURLを求める
	 *
	 * @return string		システムのルートURL。算出できなかったときは空文字列を返す。
	 */
	public function calcSystemRootUrl()
	{	
		// 相対パスを得る
		$base = explode(DIRECTORY_SEPARATOR, $this->getSystemRootPath());
		$target = explode(DIRECTORY_SEPARATOR, $this->getCurrentScriptPath());
		
		for ($i = 0; $i < count($base); $i++)
		{
			if ($base[$i] != $target[$i]) break;
		}
		$relativePath = '';
		for ($j = $i; $j < count($target); $j++)
		{
			$relativePath .= '/' . $target[$j];
		}
		// システムルートディレクトリ取得
		$sytemRootUrl = '';
		$pos = strrpos($this->getCurrentScriptUrl(), $relativePath);
		if (!($pos === false)){
			$sytemRootUrl = substr($this->getCurrentScriptUrl(), 0, $pos);
		}
		return $sytemRootUrl;
	}
	/**
	 * 相対パスを求める
	 *
	 * @param string $basePath		基点となるディレクトリの絶対パス
	 * @param string $targetPath	対象となるディレクトリの絶対パス
	 * @return string				相対パス
	 */
	public function calcRelativePath($basePath, $targetPath)
	{
		// 相対パスを得る
		$base = explode('/', $basePath);
		$target = explode('/', $targetPath);
		
		for ($i = 0; $i < count($base); $i++)
		{
			if ($base[$i] != $target[$i]) break;
		}
		$relativePath = '';
		for ($j = $i; $j < count($target); $j++)
		{
			$relativePath .= '/' . $target[$j];
		}
		return $relativePath;
	}
	/**
	 * パーマネントリンク用の現在のページURLを取得
	 *
	 * @param bool $hasSubPage		サブページIDを必ず付加するかどうか。必ず付加しない場合はデフォルトページのとき省略
	 * @return string				パーマネントリンクURL
	 */
	/*public function getCurrentPermanentPageUrl($hasSubPage=false)
	{
		$url = $this->getDefaultUrl();
		if ($hasSubPage || $this->currentPageSubId != $this->defaultPageSubId) $url .= '?sub=' . $this->currentPageSubId;
		return $url;
	}*/
	/**
	 * ドキュメントルートからのリソース用ディレクトリへの相対パスを取得(外部アプリケーション用)
	 *
	 * @return string		相対パス
	 */
	public function getRelativeResourcePathToDocumentRoot()
	{
		// 相対パスを得る
		//if (isset($_SERVER['HTTPS'])){		// SSL通信の場合
		//	$res = parse_url($this->getSslResourceUrl());
		//} else {
			$res = parse_url($this->getResourceUrl());
		//}
		return $res['path'];
	}
	/**
	 * ユーザの種別に対応した、ドキュメントルートからのリソース用ディレクトリへの相対パスを取得(外部アプリケーション用)
	 *
	 * @return string		相対パス
	 */
	public function getRelativeResourcePathToDocumentRootForUser()
	{
		// 相対パスを取得
		$res = parse_url($this->getResourceUrl());
		$path = $res['path'];
		
		// ユーザのリソース制限が必要な場合は、ユーザごとのディレクトリを設定
		//if ($this->isCurrentUserLogined() && !$this->isSystemManageUser()){
		if ($this->isResourceLimitedUser()){
			$path .= '/' . M3_DIR_NAME_HOME . '/' . $this->getCurrentUserAccount();
		}
		return $path;
	}
	/**
	 * アプリケーションルートから指定ディレクトリへの相対パスを取得
	 *
	 * @param string $url	指定URL
	 * @return string		相対パス
	 */
	public function getRelativePathToSystemRootUrl($url)
	{
		// システムのルートURL以下か、SSL用のルートURL以下か判断
		$rootUrl = $this->getRootUrl();
		$relativePath = str_replace($this->getSslRootUrl(), '', $url);
		if (empty($relativePath) || strStartsWith($relativePath, '/') || strStartsWith($relativePath, '?') || strStartsWith($relativePath, '#')){
			$rootUrl = $this->getSslRootUrl();
		}
			
		// URLから相対パスを得る
		$root = parse_url($rootUrl);
		$target = parse_url($url);
		return $this->calcRelativePath($root['path'], $target['path']);
	}
	/**
	 * アプリケーションルートから指定ディレクトリへの相対パスを取得
	 *
	 * @param string $path	指定パス
	 * @return string		相対パス
	 */
	public function getRelativePathToSystemRootPath($path)
	{
		return $this->calcRelativePath($this->getSystemRootPath(), $path);
	}
	/**
	 * URLから絶対パスを取得
	 *
	 * @param string $url	指定URL
	 * @return string		絶対パス
	 */
	public function getAbsolutePath($url)
	{
		return $this->getSystemRootPath() . $this->getRelativePathToSystemRootUrl($url);
	}
	/**
	 * フルパスからURLを取得
	 *
	 * @param string $path	指定パス
	 * @return string		URL
	 */
	public function getUrlToPath($path)
	{
		return $this->getRootUrl() . $this->calcRelativePath($this->getSystemRootPath(), $path);
	}
	/**
	 * マクロ変換したパスを取得
	 *
	 * @param string $path	変換元パス(絶対パス、相対パス)
	 * @return string		変換したパス
	 */
	public function getMacroPath($path)
	{
		$destPath = $path;
		if (strncmp($destPath, '/', 1) == 0){		// 相対パス表記のとき
			$destPath = M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END . $this->getRelativePathToSystemRootUrl($this->getDocumentRootUrl() . $destPath);
		} else if (strncmp($destPath, 'http', strlen('http')) == 0 || strncmp($destPath, 'https', strlen('https')) == 0){				// 絶対パス表記のとき
			$destPath = str_replace('https://', 'http://', $destPath);		// 一旦httpに統一
			$rootUrl = str_replace('https://', 'http://', $this->getRootUrl());		// 一旦httpに統一
			$destPath = str_replace($rootUrl, M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $destPath);// マクロ変換
		}
		return $destPath;
	}
	/**
	 * SSL用のURLに変換
	 *
	 * @param string $url	指定URL
	 */
	public function getSslUrl($url)
	{
		// 「?」以降のパラメータはそのまま維持する
		list($tmp, $query) = explode('?', $url);
		
		// URLから相対パスを得て、SSL用URLに連結
		$destUrl = $this->getSslRootUrl() . $this->getRelativePathToSystemRootUrl($url);
		if (!empty($query)) $destUrl .= '?' . $query;
		return $destUrl;
	}
	/**
	 * URLを解析
	 *
	 * @param string $url			指定URL
	 * @param string $pageId		ページIDが返る
	 * @param string $pageSubId		ページサブIDが返る
	 * @param array $params			ページID,ページサブID以外のパラメータが返る
	 * @return bool					true=解析成功、false=解析失敗(Magic3以外のURL)
	 */
/*	public function parseUrl($url, &$pageId, &$pageSubId, &$params)
	{
		// 引数エラーチェック
		if (empty($url)) return false;
		
		$params = array();
		if ($this->isSystemUrlAccess($url)){
			// URLを解析
			$queryArray = array();
			$parsedUrl = parse_url($url);
			if (!empty($parsedUrl['query'])) parse_str($parsedUrl['query'], $queryArray);		// クエリーの解析
			
			// ルートからの相対パスを取得
			$relativePath = $this->getRelativePathToSystemRootUrl($url);
			
			// ページIDを取得
			$path = trim($relativePath, '/');
			$pathArray = explode('/', $path);
			$basename = '';
			for ($i = 0; $i < count($pathArray); $i++){
				if ($i == 0){
					$basename .= $pathArray[$i];
				} else {
					$basename .= ('_' . $pathArray[$i]);
				}
			}
			$basename = basename($basename, '.php');
			if (empty($basename)) $basename = $this->getDefaultPageId();
			$pageId = $basename;
			
			// ページサブID取得
			$pageSubId = $this->_getPageSubIdFromUrlQuery($pageId, $parsedUrl['query']);

			// その他のパラメータを取得
			$keys = array_keys($queryArray);
			$keyCount = count($keys);
			for ($i = 0; $i < $keyCount; $i++){
				$key = $keys[$i];
				$value = $queryArray[$key];
				if ($key != M3_REQUEST_PARAM_PAGE_SUB_ID){		// ページIDは追加しない
					$params[$key] = $value;
				}
			}
			return true;
		} else {// システムディレクトリ以外のときはエラー
			return false;
		}
	}*/
	/**
	 * ページIDとURLのクエリー文字列からサブページIDを取得
	 *
	 * @param string $pageId	ページID
	 * @param string $query		クエリー文字列
	 * @return string			サブページID
	 */
/*	public function _getPageSubIdFromUrlQuery($pageId, $query)
	{
		$queryArray = array();
		if (!empty($query)) parse_str($query, $queryArray);		// クエリーの解析
			
		// ページサブID取得
		$pageSubId = $queryArray[M3_REQUEST_PARAM_PAGE_SUB_ID];
		if (empty($pageSubId)){
			// ページサブIDがないときは、パラメータからページ属性を判断する
			// キーが設定されていれば値は空文字列でも属性を持っているとする
			if (isset($queryArray[M3_REQUEST_PARAM_CONTENT_ID]) || isset($queryArray[M3_REQUEST_PARAM_CONTENT_ID_SHORT])){		// コンテンツIDのとき
				$pageSubId = $this->db->getSubPageIdWithContent(M3_VIEW_TYPE_CONTENT, $pageId);// ページサブIDを取得
			} else if (isset($queryArray[M3_REQUEST_PARAM_PRODUCT_ID]) || isset($queryArray[M3_REQUEST_PARAM_PRODUCT_ID_SHORT])){	// 製品IDのとき
				$pageSubId = $this->db->getSubPageIdWithContent(M3_VIEW_TYPE_PRODUCT, $pageId);// ページサブIDを取得
			} else if (isset($queryArray[M3_REQUEST_PARAM_BBS_ID]) || isset($queryArray[M3_REQUEST_PARAM_BBS_ID_SHORT])){	// 掲示板投稿記事のとき
				$pageSubId = $this->db->getSubPageIdWithContent(M3_VIEW_TYPE_BBS, $pageId);// ページサブIDを取得
			} else if (isset($queryArray[M3_REQUEST_PARAM_BLOG_ENTRY_ID]) || isset($queryArray[M3_REQUEST_PARAM_BLOG_ENTRY_ID_SHORT])){	// ブログ記事のとき
				$pageSubId = $this->db->getSubPageIdWithContent(M3_VIEW_TYPE_BLOG, $pageId);// ページサブIDを取得
			}
		}
		if (empty($pageSubId)) $pageSubId = $this->db->getDefaultPageSubId($pageId);	// 最終的に見つからないときはデフォルト値を取得
		return $pageSubId;
	}*/
	/**
	 * Magic3システムへのアクセスかどうか(SSL用のURL含む)
	 *
	 * @param string $url		指定URL(空のときは現在のスクリプト)
	 * @return bool				Magic3システムディレクトリ以下のアクセスのときはtrue。それ以外の場合はfalse。
	 */
	public function isSystemUrlAccess($url = '')
	{
		if (empty($url)) $url = $_SERVER["HTTP_REFERER"];
		
		$url = str_replace('https://', 'http://', $url);		// 一旦httpに統一
		$systemUrl = str_replace('https://', 'http://', $this->getRootUrl());		// 一旦httpに統一
		$systemSslUrl = str_replace('https://', 'http://', $this->getSslRootUrl());		// 一旦httpに統一
			
		// パスを解析
		$relativePath = str_replace($systemUrl, '', $url);		// ルートURLからの相対パスを取得
		if (empty($relativePath)){			// Magic3のルートURLの場合
			return true;
		} else if (strStartsWith($relativePath, '/') || strStartsWith($relativePath, '?') || strStartsWith($relativePath, '#')){		// ルートURL配下のとき
			return true;
		} else {		// ルートURL以外のURLのとき(SSL用のURL以下かどうかチェック)
			$relativePath = str_replace($systemSslUrl, '', $url);		// ルートURLからの相対パスを取得
			if (empty($relativePath)){			// Magic3のルートURLの場合
				return true;
			} else if (strStartsWith($relativePath, '/') || strStartsWith($relativePath, '?') || strStartsWith($relativePath, '#')){		// ルートURL配下のとき
				return true;
			} else {		// ルートURL以外のURLのとき(SSL用のURL以下かどうかチェック)
				return false;
			}
		}
	}
	/**
	 * 管理者用URLへのアクセスかどうか
	 *
	 * @param string $url		判断対象のURL(空のときは現在のスクリプト)
	 * @return bool				管理者用ディレクトリへのアクセスのときは、true。それ以外の場合はfalse。
	 */
	public function isAdminUrlAccess($url = '')
	{
		if (empty($url)) $url = $_SERVER["HTTP_REFERER"];
		
		$url = str_replace('https://', 'http://', $url);		// 一旦httpに統一
		$adminUrl = str_replace('https://', 'http://', $this->getAdminUrl());		// 一旦httpに統一
			
		// パスを解析
		$relativePath = str_replace($adminUrl, '', $url);		// ルートURLからの相対パスを取得
		if (empty($relativePath)){			// Magic3のルートURLの場合
			return true;
		} else if (strStartsWith($relativePath, '/') || strStartsWith($relativePath, '?') || strStartsWith($relativePath, '#')){		// ルートURL配下のとき
			return true;
		} else {		// ルートURL以外のURLのとき
			return false;
		}
	}
	/**
	 * 管理者用URLへのアクセスかどうか
	 *
	 * @return bool		管理者用ディレクトリへのアクセスのときはtrue。それ以外の場合はfalse。
	 */
	public function isAdminDirAccess()
	{
		static $isAdminDirAccess;
		
		if (!isset($isAdminDirAccess)){
			if (dirname($this->getCurrentScriptPath()) == $this->getAdminPath()){
				$isAdminDirAccess = true;
			} else {
				$isAdminDirAccess = false;
			}
		}
		return $isAdminDirAccess;
	}
	// ##################### カレントのテンプレート関係 #####################
	/**
	 * 現在のテンプレートを設定
	 *
	 * @param string $name				テンプレートID
	 * @param string $subTemplateId		サブテンプレートID
	 * @return							なし
	 */
	public function setCurrentTemplateId($name, $subTemplateId = '')
	{
		global $gPageManager;
		
		$this->currentTemplateId = $name;

		// テンプレートの属性を取得
		$this->currentSubTemplateId = '';	// 現在のサブテンプレートID
		$this->currentTemplateType = 0;
		$this->currentTemplateGenerator = '';		// テンプレート作成アプリケーション
		$this->currentTemplateVersion = '';		// テンプレートバージョン
		$this->currentTemplateCleanType = 0;
		$this->currentTemplateUseBootstrap = false;	// 現在のテンプレートでBootstrapライブラリを使用するかどうか
		if ($this->canUseDb){		// DB使用可能なとき
			if ($this->db->getTemplate($name, $row)){
				$this->currentTemplateType = $row['tm_type'];		// テンプレートタイプ
				$this->currentTemplateGenerator = $row['tm_generator'];		// テンプレート作成アプリケーション
				$this->currentTemplateVersion = $row['tm_version'];		// テンプレートバージョン
				$this->currentTemplateCleanType = $row['tm_clean_type'];	// 現在のテンプレートのクリーンタイプ
				$this->currentTemplateUseBootstrap = $row['tm_use_bootstrap'];	// 現在のテンプレートでBootstrapライブラリを使用するかどうか
				
				// テンプレートが設定された段階でBootstrapの使用があればページマネージャーに反映する。ウィジェット側で使用状況を参照してビューを作成することがあるため。
				if ($this->currentTemplateUseBootstrap) $gPageManager->useBootstrap();
				
				if (!empty($subTemplateId)) $this->currentSubTemplateId = $subTemplateId;	// 現在のサブテンプレートID
			}
		}
	}
	/**
	 * 現在のテンプレートを取得
	 *
	 * @return string		テンプレートID
	 */
	public function getCurrentTemplateId()
	{
		return $this->currentTemplateId;
	}
	/**
	 * 現在のサブテンプレートを取得
	 *
	 * @return string		サブテンプレートID
	 */
	public function getCurrentSubTemplateId()
	{
		return $this->currentSubTemplateId;
	}
	/**
	 * 現在のテンプレートタイプ
	 *
	 * @return int		0=デフォルトテンプレート(Joomla!v1.0),1=Joomla!v1.5,2=Joomla!v2.5,10=Bootstrap v3.0
	 */
	public function getCurrentTemplateType()
	{
		return $this->currentTemplateType;
	}
	/**
	 * テンプレート作成アプリケーション
	 *
	 * @return string		テンプレート作成アプリケーション(artisteer,themler)
	 */
	public function getCurrentTemplateGenerator()
	{
		return $this->currentTemplateGenerator;
	}
	/**
	 * テンプレートバージョン
	 *
	 * @return string		バージョン文字列
	 */
	public function getCurrentTemplateVersion()
	{
		return $this->currentTemplateVersion;
	}
	/**
	 * 現在のテンプレートのクリーンタイプ
	 *
	 * @return int		クリーンタイプ
	 */
	public function getCurrentTemplateCleanType()
	{
		return $this->currentTemplateCleanType;
	}
	/**
	 * 現在のテンプレートでBootstrapライブラリを使用するかどうか
	 *
	 * @return bool		true=使用、false=未使用
	 */
	public function getCurrentTemplateUseBootstrap()
	{
		return $this->currentTemplateUseBootstrap;
	}
	/**
	 * 現在のビュー作成タイプを設定
	 *
	 * @param string $renderType	ビュー作成タイプ
	 * @return						なし
	 */
	public function setCurrentRenderType($renderType)
	{
		$this->currentRenderType = $renderType;
	}
	/**
	 * 現在のビュー作成タイプを取得
	 *
	 * @return string			ビュー作成タイプ
	 */
	public function getCurrentRenderType()
	{
		return $this->currentRenderType;
	}
	/**
	 * 現在のテンプレートへのパスを取得
	 *
	 * 例) /var/www/html/magic3/templates/menu
	 */
	public function getCurrentTemplatePath()
	{
		return M3_SYSTEM_ROOT_PATH . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . $this->currentTemplateId;
	}
	/**
	 * 現在のテンプレートへのURLを取得
	 *
	 * 例) http://www.magic3.org/magic3/templates/menu
	 */
	public function getCurrentTemplateUrl()
	{
		//return M3_SYSTEM_ROOT_URL . '/templates/' . $this->currentTemplateId;
		return $this->currentDomainRootUrl . '/templates/' . $this->currentTemplateId;
	}
	
	// ##################### カレントのウィジェット関係 #####################
	/**
	 * 現在処理中のウィジェットのルートディレクトリへのパスを取得
	 *
	 * 例) http://www.magic3.org/magic3/widgets/xxxxx
	 */
	public function getCurrentWidgetRootUrl()
	{
		//return M3_SYSTEM_ROOT_URL . '/widgets/' . $this->currentWidgetId;
		return $this->currentDomainRootUrl . '/widgets/' . $this->currentWidgetId;
	}
	/**
	 * 現在処理中のウィジェットのSSLルートディレクトリへのパスを取得
	 *
	 * 例) https://www.magic3.org/magic3/widgets/xxxxx
	 */
	public function getCurrentWidgetSslRootUrl()
	{
		return $this->getSslRootUrl() . '/widgets/' . $this->currentWidgetId;
	}
	/**
	 * 現在処理中のウィジェットのルートディレクトリへのパスを取得
	 */
	public function getCurrentWidgetRootPath()
	{
		return M3_SYSTEM_ROOT_PATH . DIRECTORY_SEPARATOR . 'widgets' . DIRECTORY_SEPARATOR . $this->currentWidgetId;
	}
	/**
	 * 現在処理中のウィジェットのincludeディレクトリへのパスを取得
	 */
	public function getCurrentWidgetIncludePath()
	{
		return M3_SYSTEM_ROOT_PATH . '/widgets/' . $this->currentWidgetId . '/include';
	}
	/**
	 * 現在処理中のウィジェットのdbディレクトリへのパスを取得
	 *
	 * 例) /var/www/html/magic3/widgets/xxxxx/include/db
	 */
	public function getCurrentWidgetDbPath()
	{
		return $this->getWidgetsPath() . DIRECTORY_SEPARATOR . $this->currentWidgetId . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'db';
	}
	/**
	 * 現在処理中のウィジェットのcontainerディレクトリへのパスを取得
	 *
	 * 例) /var/www/html/magic3/widgets/xxxxx/include/container
	 */
	public function getCurrentWidgetContainerPath()
	{
		return $this->getWidgetsPath() . DIRECTORY_SEPARATOR . $this->currentWidgetId . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'container';
	}
	/**
	 * 現在処理中のウィジェットのlibディレクトリへのパスを取得
	 *
	 * 例) /var/www/html/magic3/widgets/xxxxx/include/lib
	 */
	public function getCurrentWidgetLibPath()
	{
		return $this->getWidgetsPath() . DIRECTORY_SEPARATOR . $this->currentWidgetId . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'lib';
	}
	/**
	 * 現在処理中のウィジェットのtemplateディレクトリへのパスを取得
	 *
	 * 例) /var/www/html/magic3/widgets/xxxxx/include/template
	 */
	public function getCurrentWidgetTemplatePath()
	{
		return $this->getWidgetsPath() . DIRECTORY_SEPARATOR . $this->currentWidgetId . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'template';
	}
	/**
	 * 現在処理中のウィジェットのlocaleディレクトリへのパスを取得
	 *
	 * 例) /var/www/html/magic3/widgets/xxxxx/include/locale
	 */
	public function getCurrentWidgetLocalePath()
	{
		return $this->getWidgetsPath() . DIRECTORY_SEPARATOR . $this->currentWidgetId . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'locale';
	}
	/**
	 * 現在処理中のウィジェットのsqlディレクトリへのパスを取得
	 *
	 * 例) /var/www/html/magic3/widgets/xxxxx/include/sql
	 */
	public function getCurrentWidgetSqlPath()
	{
		return $this->getWidgetsPath() . DIRECTORY_SEPARATOR . $this->currentWidgetId . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'sql';
	}
	/**
	 * 現在処理中のウィジェットのscriptsディレクトリへURLを取得
	 */
	public function getCurrentWidgetScriptsUrl()
	{
		//return M3_SYSTEM_ROOT_URL . '/widgets/' . $this->currentWidgetId . '/scripts';
		return $this->currentDomainRootUrl . '/widgets/' . $this->currentWidgetId . '/scripts';
	}
	/**
	 * 現在処理中のウィジェットのimagesディレクトリへURLを取得
	 */
	public function getCurrentWidgetImagesUrl()
	{
		//return M3_SYSTEM_ROOT_URL . '/widgets/' . $this->currentWidgetId . '/images';
		return $this->currentDomainRootUrl . '/widgets/' . $this->currentWidgetId . '/images';
	}
	/**
	 * 現在処理中のウィジェットのscriptsディレクトリへURLを取得
	 */
	public function getCurrentWidgetCssUrl()
	{
		//return M3_SYSTEM_ROOT_URL . '/widgets/' . $this->currentWidgetId . '/css';
		return $this->currentDomainRootUrl . '/widgets/' . $this->currentWidgetId . '/css';
	}
	/**
	 * 現在処理中のインナーウィジェットのルートディレクトリへのパスを取得
	 *
	 * 例) /var/www/html/magic3/widgets/xxxxx/include/iwidgets/yyyyy
	 */
	public function getCurrentIWidgetRootPath()
	{
		// ウィジェットIDとインナーウィジェットIDを取り出す
		list($widgetId, $iWidgetId) = explode(M3_WIDGET_ID_SEPARATOR, $this->currentIWidgetId);

		return $this->getWidgetsPath() . '/' . $widgetId . '/include/iwidgets/' . $iWidgetId;
	}
	/**
	 * 現在処理中のインナーウィジェットのdbディレクトリへのパスを取得
	 *
	 * 例) /var/www/html/magic3/widgets/xxxxx/include/iwidgets/yyyyy/include/db
	 */
	public function getCurrentIWidgetDbPath()
	{
		// ウィジェットIDとインナーウィジェットIDを取り出す
		list($widgetId, $iWidgetId) = explode(M3_WIDGET_ID_SEPARATOR, $this->currentIWidgetId);

		return $this->getWidgetsPath() . '/' . $widgetId . '/include/iwidgets/' . $iWidgetId . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'db';
	}
	/**
	 * 現在処理中のインナーウィジェットのdbディレクトリへのパスを取得
	 *
	 * 例) /var/www/html/magic3/widgets/xxxxx/include/iwidgets/yyyyy/include/container
	 */
	public function getCurrentIWidgetContainerPath()
	{
		// ウィジェットIDとインナーウィジェットIDを取り出す
		list($widgetId, $iWidgetId) = explode(M3_WIDGET_ID_SEPARATOR, $this->currentIWidgetId);

		return $this->getWidgetsPath() . '/' . $widgetId . '/include/iwidgets/' . $iWidgetId . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'container';
	}
	/**
	 * 現在実行中のウィジェットオブジェクトを設定
	 *
	 * @param object $obj		ウィジェットオブジェクト
	 * @return					なし
	 */
	public function setCurrentWidgetObj($obj)
	{
		$this->currentWidgetObj = $obj;
	}
	/**
	 * 現在実行中のウィジェットオブジェクトを取得
	 *
	 * @return object		ウィジェットオブジェクト
	 */
	public function getCurrentWidgetObj()
	{
		return $this->currentWidgetObj;
	}
	/**
	 * 現在処理中のウィジェット
	 */
	public function setCurrentWidgetId($id = '')
	{
		$this->currentWidgetId = $id;
	}
	/**
	 * 現在処理中のウィジェット
	 */
	public function getCurrentWidgetId()
	{
		return $this->currentWidgetId;
	}
	/**
	 * 現在処理中のインナーウィジェット
	 */
	public function setCurrentIWidgetId($id = '')
	{
		$this->currentIWidgetId = $id;
	}
	/**
	 * 現在処理中のインナーウィジェット
	 */
	public function getCurrentIWidgetId()
	{
		return $this->currentIWidgetId;
	}
	/**
	 * 現在作成中のウィジェットの定義ID
	 *
	 * @param int,string $id		定義ID(定義なしの場合は空文字列。それ以外の場合はint型の値。)
	 */
	public function setCurrentWidgetConfigId($id)
	{
		$this->currentWidgetConfigId = $id;
	}
	/**
	 * 現在作成中のウィジェットの定義ID
	 *
	 * @return int,string			定義ID(定義なしの場合は空文字列。それ以外の場合はint型の値。)
	 */
	public function getCurrentWidgetConfigId()
	{
		return $this->currentWidgetConfigId;
	}
	/**
	 * 現在作成中のインナーウィジェットの定義ID
	 *
	 * @param in,string $id		定義ID(定義なしの場合は空文字列。それ以外の場合はint型の値。)
	 */
	public function setCurrentIWidgetConfigId($id)
	{
		$this->currentIWidgetConfigId = $id;
	}
	/**
	 * 現在作成中のインナーウィジェットの定義ID
	 *
	 * @return int,string			定義ID(定義なしの場合は空文字列。それ以外の場合はint型の値。)
	 */
	public function getCurrentIWidgetConfigId()
	{
		return $this->currentIWidgetConfigId;
	}
	/**
	 * 現在処理中のウィジェットのプレフィックス文字列
	 */
	public function setCurrentWidgetPrefix($val)
	{
		$this->currentWidgetPrefix = $val;
	}
	/**
	 * 現在処理中のウィジェットのプレフィックス文字列
	 */
	public function getCurrentWidgetPrefix()
	{
		return $this->currentWidgetPrefix;
	}
	/**
	 * 現在処理中のウィジェットのタイトル文字列
	 *
	 * @param string $val	タイトル文字列
	 * @return 				なし
	 */
	public function setCurrentWidgetTitle($val)
	{
		$this->currentWidgetTitle = $val;
	}
	/**
	 * 現在処理中のウィジェットのタイトル文字列
	 *
	 * @return string	タイトル文字列
	 */
	public function getCurrentWidgetTitle()
	{
		return $this->currentWidgetTitle;
	}
	/**
	 * 現在処理中のウィジェットのパラメータ設定
	 *
	 * @param string $key	キー
	 * @param string $val	値
	 * @return 				なし
	 */
	public function setCurrentWidgetParams($key, $val)
	{
		$this->currentWidgetParams[$key] = $val;
	}
	/**
	 * 現在処理中のウィジェットのパラメータから値取得
	 *
	 * @param string $key	キー
	 * @return string		値
	 */
	public function getCurrentWidgetParams($key)
	{
		return $this->currentWidgetParams[$key];
	}
	/**
	 * 現在処理中のウィジェットのスタイル文字列
	 *
	 * @param bool $val	スタイル文字列
	 * @return 			なし
	 */
	public function setCurrentWidgetStyle($val)
	{
		$this->currentWidgetStyle = $val;
	}
	/**
	 * 現在処理中のウィジェットのスタイル文字列
	 *
	 * @return string	スタイル文字列
	 */
	public function getCurrentWidgetStyle()
	{
		return $this->currentWidgetStyle;
	}
	/**
	 * 現在作成中のウィジェットのJoomla用パラメータを設定
	 *
	 * @param array	$val	Joomla用パラメータ
	 * @return		なし
	 */
	public function setCurrentWidgetJoomlaParam($val)
	{
		$this->currentWidgetJoomlaParam = $val;
	}
	/**
	 * 現在作成中のウィジェットのJoomla用パラメータを取得
	 *
	 * @return array	Joomla用パラメータ
	 */
	public function getCurrentWidgetJoomlaParam()
	{
		return $this->currentWidgetJoomlaParam;
	}
	/**
	 * 現在作成中のウィジェットが共通ウィジェットかどうかを設定
	 *
	 * @param bool		$val	現在のウィジェットの共通ウィジェット状態
	 */
	public function setIsCurrentWidgetShared($val)
	{
		$this->isCurrentWidgetShared = $val;
	}
	/**
	 * 現在作成中のウィジェットが共通ウィジェットかどうか
	 */
	public function isCurrentWidgetShared()
	{
		return $this->isCurrentWidgetShared;
	}
	/**
	 * 現在処理を行っているページ定義のレコードのシリアル番号
	 *
	 * @param int $serial		シリアル番号
	 */
	public function setCurrentPageDefSerial($serial)
	{
		$this->currentPageDefSerial = $serial;
	}
	/**
	 * 現在処理を行っているページ定義のレコードのシリアル番号
	 *
	 * @return int			シリアル番号
	 */
	public function getCurrentPageDefSerial()
	{
		return $this->currentPageDefSerial;
	}
	/**
	 * 現在処理を行っているページ定義レコードを設定
	 *
	 * @param array $rec			ページ定義レコード
	 * @return						なし
	 */
	public function setCurrentPageDefRec($rec = null)
	{
		$this->currentPageDefRec = $rec;
	}
	/**
	 * 現在処理を行っているページ定義レコードを取得
	 *
	 * @return array			ページ定義レコード
	 */
	public function getCurrentPageDefRec()
	{
		return $this->currentPageDefRec;
	}
	// ##################### ユーザ情報 #####################
	/**
	 * 現在アクセス中のユーザ情報取得
	 *
	 * @return UserInfo		ユーザ情報。設定されていない場合はnullを返す。
	 */
	public function getCurrentUserInfo()
	{
		global $gInstanceManager;

		return $gInstanceManager->getUserInfo();
	}
	/**
	 * 現在アクセス中のユーザID取得
	 *
	 * @return int		ユーザID,ユーザが確定できないときは0
	 */
	public function getCurrentUserId()
	{
		global $gInstanceManager;

		$userInfo = $gInstanceManager->getUserInfo();
		if (is_null($userInfo)){		// ログインしていない場合
			return 0;
		} else {
			return $userInfo->userId;
		}
	}
	/**
	 * 現在アクセス中のユーザのアカウント取得
	 *
	 * @return string		ユーザアカウント,ユーザが確定できないときは空文字列
	 */
	public function getCurrentUserAccount()
	{
		global $gInstanceManager;

		$userInfo = $gInstanceManager->getUserInfo();
		if (is_null($userInfo)){		// ログインしていない場合
			return '';
		} else {
			return $userInfo->account;
		}
	}
	/**
	 * 現在アクセス中のユーザの名前を取得
	 *
	 * @return string		ユーザ名,ユーザが確定できないときは空文字列
	 */
	public function getCurrentUserName()
	{
		global $gInstanceManager;

		$userInfo = $gInstanceManager->getUserInfo();
		if (is_null($userInfo)){		// ログインしていない場合
			return '';
		} else {
			return $userInfo->name;
		}
	}
	/**
	 * 現在アクセス中のユーザのタイプを取得
	 *
	 * @return int		ユーザ名,ユーザが確定できないときは0
	 */
	public function getCurrentUserType()
	{
		global $gInstanceManager;

		$userInfo = $gInstanceManager->getUserInfo();
		if (is_null($userInfo)){		// ログインしていない場合
			return 0;
		} else {
			return $userInfo->userType;
		}
	}
	/**
	 * 現在アクセス中のユーザのEメールを取得
	 *
	 * @return string		Eメールが確定できないときは空文字列
	 */
	public function getCurrentUserEmail()
	{
		global $gInstanceManager;

		$userInfo = $gInstanceManager->getUserInfo();
		if (is_null($userInfo)){		// ログインしていない場合
			return '';
		} else {
			return $userInfo->email;
		}
	}
	/**
	 * 現在のユーザがアクセス可能なウィジェットを取得
	 *
	 * @return array		ウィジェット
	 */
/*	public function getAccessableWidget()
	{
		global $gInstanceManager;

		$userInfo = $gInstanceManager->getUserInfo();
		if (is_null($userInfo)){		// ログインしていない場合
			return array();
		} else {
			return $userInfo->adminWidget;
		}
	}*/
	/**
	 * 現在のユーザが指定のウィジェットの管理画面が使用可能かを取得
	 *
	 * @param string $widgetId	ウィジェットID
	 * @return bool				true=使用可能、false=使用不可
	 */
	public function canUseWidgetAdmin($widgetId)
	{
		global $gInstanceManager;

		$canUseAdmin = false;
		$userInfo = $gInstanceManager->getUserInfo();
		if (!is_null($userInfo)){		// ログイン中の場合
			if ($userInfo->userType == UserInfo::USER_TYPE_SYS_ADMIN){	// システム管理者の場合
				$canUseAdmin = true;
			} else if ($userInfo->userType == UserInfo::USER_TYPE_MANAGER){	// システム運用者の場合
				$accessWidget = $userInfo->adminWidget;
				if (empty($accessWidget)){
					$canUseAdmin = true;
				} else {
					if (in_array($widgetId, $accessWidget)) $canUseAdmin = true;
				}
			}
		}
		return $canUseAdmin;
	}
	/**
	 * 現在のユーザがユーザタイプオプションを持っているかを取得
	 *
	 * @param string $option	ユーザタイプオプション
	 * @return bool				true=オプションあり、false=オプションなし
	 */
	public function hasUserTypeOption($option)
	{
		global $gInstanceManager;

		$hasOption = false;
		$userInfo = $gInstanceManager->getUserInfo();
		if (!is_null($userInfo)){		// ログイン中の場合
			if ($userInfo->userType == UserInfo::USER_TYPE_MANAGER){	// システム運用者の場合
				$pos = strpos($userInfo->userTypeOption, $option);
				if ($pos !== false) $hasOption = true;
			}
		}
		return $hasOption;
	}
	/**
	 * 管理者用一時キーを取得
	 *
	 * @return string		管理者キー、管理者でないときは空文字列
	 */
	public function getAdminKey()
	{
		global $gInstanceManager;

		$userInfo = $gInstanceManager->getUserInfo();
		if (is_null($userInfo)){		// ログインしていない場合
			return '';
		} else {
			return $userInfo->_adminKey;
		}
	}
	/**
	 * 現在アクセス中のユーザに管理者権限があるかどうかを返す
	 *
	 * @return bool		true=ログイン中かつ管理者権限あり、false=未ログインまたはログイン中であるが管理者権限なし
	 */
	public function isSystemAdmin()
	{
		$isAdmin = false;
		$userInfo = $this->getCurrentUserInfo();
		if (is_null($userInfo)){		// ログインしていない場合
		} else {
			if ($userInfo->isSystemAdmin()){	// システム管理者の場合
				$isAdmin = true;
			}
		}
		return $isAdmin;
	}
	/**
	 * 現在アクセス中のユーザにシステム運用権限があるかどうかを返す
	 *
	 * @return bool		true=システム運用可、false=システム運用不可
	 */
	public function isSystemManageUser()
	{
		$canManage = false;
		$userInfo = $this->getCurrentUserInfo();
		if (is_null($userInfo)){		// ログインしていない場合
		} else {
			if ($userInfo->userType >= UserInfo::USER_TYPE_MANAGER){	// システム運用者以上の場合
				$canManage = true;
			}
		}
		return $canManage;
	}
	/**
	 * 現在アクセス中のユーザにコンテンツ編集権限があるかどうかを返す
	 *
	 * @return bool		true=コンテンツ編集可、false=コンテンツ編集不可
	 */
	public function isContentEditableUser()
	{
		$canManage = false;
		$userInfo = $this->getCurrentUserInfo();
		if (is_null($userInfo)){		// ログインしていない場合
		} else {
			if ($userInfo->userType >= UserInfo::USER_TYPE_AUTHOR){	// 投稿ユーザ以上の場合
				$canManage = true;
			}
		}
		return $canManage;
	}
	/**
	 * 現在アクセス中のユーザがリソース制限必要なユーザかどうかを返す
	 *
	 * @return bool		true=制限が必要、false=制限なし
	 */
	public function isResourceLimitedUser()
	{
		global $gInstanceManager;

		$resourceLimited = true;
		$userInfo = $gInstanceManager->getUserInfo();
		if (!is_null($userInfo)){		// ログイン中の場合
			if ($userInfo->userType == UserInfo::USER_TYPE_SYS_ADMIN){	// システム管理者の場合
				$resourceLimited = false;
			} else if ($userInfo->userType == UserInfo::USER_TYPE_MANAGER){	// システム運用者の場合
				$accessWidget = $userInfo->adminWidget;
				if (empty($accessWidget)) $resourceLimited = false;
			}
		}
		return $resourceLimited;
	}
	/**
	 * 現在アクセス中のユーザがログインしているか確認
	 *
	 * @return bool		true=ログイン中、false=未ログイン
	 */
	public function isCurrentUserLogined()
	{
		$userInfo = $this->getCurrentUserInfo();
		if (is_null($userInfo)){		// ログインしていない場合
			return false;
		} else {
			return true;
		}
	}
	/**
	 * 指定のユーザに管理者権限があるかどうかを返す
	 *
	 * @param int $userId	ユーザID
	 * @return bool			true=管理者権限あり、false=管理者権限なし
	 */
	public function isSystemAdminUser($userId)
	{
		return $this->db->isSystemAdmin($userId);
	}
	// ##################### アクセスログ #####################
	/**
	 * 現在のアクセスログのシリアル番号を返す
	 *
	 * @return int			アクセスログシリアル番号
	 */
	public function getCurrentAccessLogSerial()
	{
		global $gAccessManager;
		return $gAccessManager->getAccessLogSerialNo();
	}
	/**
	 * アクセスポイントパスを設定
	 *
	 * @param string $path		アクセスポイントパス
	 * @return なし
	 */
	public function setAccessPath($path)
	{
		$this->accessPath = $path;		// アクセスポイントパス
		$pathArray = explode('/', $path);
		if (count($pathArray) >= 2){
			$this->accessDir = $pathArray[0];			// アクセスポイントディレクトリ
		} else {
			$this->accessDir = '';
		}
	}
	/**
	 * アクセスポイントパスを取得
	 *
	 * @return string			アクセスポイントパス
	 */
	public function getAccessPath()
	{
		return $this->accessPath;
	}
	/**
	 * アクセスポイントディレクトリを取得
	 *
	 * @return string			アクセスポイントディレクトリ
	 */
	public function getAccessDir()
	{
		return $this->accessDir;
	}
	// ##################### ページ制御 #####################
	/**
	 * デフォルトのページID取得
	 *
	 * @return string			デフォルトのページID
	 */
	public function getDefaultPageId()
	{
		return self::DEFAULT_PAGE_ID;
	}
	/**
	 * デフォルトの登録機能用ページID
	 *
	 * @return string			デフォルトのページID
	 */
	public function getDefaultRegistPageId()
	{
		return self::DEFAULT_REGIST_PAGE_ID;
	}
	/**
	 * 携帯用デフォルトのページID取得
	 *
	 * @return string			携帯用デフォルトのページID
	 */
	public function getDefaultMobilePageId()
	{
		return self::DEFAULT_MOBILE_PAGE_ID;
	}
	/**
	 * スマートフォン用デフォルトのページID取得
	 *
	 * @return string			スマートフォン用デフォルトのページID
	 */
	public function getDefaultSmartphonePageId()
	{
		return self::DEFAULT_SMARTPHONE_PAGE_ID;
	}
	/**
	 * デフォルトの管理機能用ページID取得
	 *
	 * @return string			デフォルトの管理機能用ページID
	 */
	public function getDefaultAdminPageId()
	{
		return self::DEFAULT_ADMIN_PAGE_ID;
	}
	/**
	 * 一般画面のデフォルトのページID取得
	 *
	 * @return array			ページID(0=PC,1=携帯,2=スマートフォン)
	 */
	public function getAllDefaultPageId()
	{
		return array(self::DEFAULT_PAGE_ID, self::DEFAULT_MOBILE_PAGE_ID, self::DEFAULT_SMARTPHONE_PAGE_ID);
	}
	/**
	 * 一般画面のデフォルトのアクセスポイント取得
	 *
	 * @return array			アクセスポイント(0=PC,1=携帯,2=スマートフォン)
	 */
	public function getAllDefaultAccessPoint()
	{
		return array('', self::M3_DIR_NAME_MOBILE, self::M3_DIR_NAME_SMARTPHONE);
	}
	/**
	 * 現在のページID
	 */
	public function setCurrentPageId($id)
	{
		// 現在のページIDが変更のときは、デフォルトのページサブIDを更新
		if ($this->canUseDb && $this->currentPageId != $id){
			$deviceType = 0;		// 端末タイプ取得
			$this->defaultPageSubId = $this->db->getDefaultPageSubId($id, $deviceType);
			$this->currentPageDeviceType = $deviceType;		// 現在のページの端末タイプ
		}
		$this->currentPageId = $id;
	}
	/**
	 * 現在のページID
	 */
	public function getCurrentPageId()
	{
		return $this->currentPageId;
	}
	/**
	 * 現在のページサブID
	 */
	public function setCurrentPageSubId($id)
	{
		$this->currentPageSubId = $id;
	}
	/**
	 * 現在のページサブID
	 */
	public function getCurrentPageSubId()
	{
		return $this->currentPageSubId;
	}
	/**
	 * 現在のページのデフォルトのページサブID
	 *
	 * @return string 		デフォルトのページサブID
	 */
	public function getDefaultPageSubId()
	{
		return $this->defaultPageSubId;
	}
	/**
	 * 管理画面のデフォルトのページサブID
	 *
	 * @return string 		デフォルトのページサブID
	 */
	public function getAdminDefaultPageSubId()
	{
		return $this->getDefaultPageSubIdByPageId(self::DEFAULT_ADMIN_PAGE_ID);
	}
	/**
	 * 指定ページのデフォルトのページサブID
	 *
	 * @param string $pageId	ページID
	 * @return string 			デフォルトのページサブID
	 */
	public function getDefaultPageSubIdByPageId($pageId)
	{
		return $this->db->getDefaultPageSubId($pageId);
	}
	/**
	 * 現在実行中のウィジェット用のページサブID取得
	 *
	 * @return string		ページサブID。共通属性ありの場合、現在のページサブIDがデフォルトページサブIDと同じときは空。
	 */
	public function getCurrentWidgetPageSubId()
	{
		if ($this->isCurrentWidgetShared) return '';		// 共通属性ありの場合
		
		if ($this->currentPageSubId == $this->defaultPageSubId) return '';
		
		return $this->currentPageSubId;
	}
	/**
	 * コンテンツ種別からデフォルトのページサブID取得
	 *
	 * @param string $pageId		ページID
	 * @param string $contentType	コンテンツ種別	 * @return string 				ページサブID
	 */
	public function getPageSubIdByContentType($pageId, $contentType)
	{
		$pageSubId = $this->db->getSubPageIdWithContent($contentType, $pageId);// ページサブIDを取得
		return $pageSubId;
	}
	/**
	 * 現在のページの端末タイプを取得
	 *
	 * @return string		端末タイプ(0=PC、1=携帯、2=スマートフォン)
	 */
	public function getCurrentPageDeviceType()
	{
		return $this->currentPageDeviceType;		// 現在のページの端末タイプ
	}
	/**
	 * 現在のページID、サブページIDのURLを作成
	 *
	 * @param bool          $withPageSubId		ページサブIDを付加するかどうか
	 * @return string		作成したURL
	 */
	public function createCurrentPageUrl($withPageSubId = true)
	{
		$url = $this->createPageUrl();
		if ($withPageSubId) $url .= '?sub=' . $this->getCurrentPageSubId();
		return $url;
	}
	/**
	 * 指定ページIDのURLを作成
	 *
	 * @param string $pageId	ページID。空のときは現在のページIDから作成
	 * @param string $isSslPage	SSLが必要なページかどうか
	 * @return string			作成したURL
	 */
	public function createPageUrl($pageId='', $isSslPage = false)
	{
		if (empty($pageId)) $pageId = $this->getCurrentPageId();
		if (empty($pageId)) $pageId = $this->getDefaultPageId();// 空のときはデフォルトページIDを設定
		
		// ページIDからパスを求める
		$path = '';
		$pathArray = explode('_', $pageId);
		if ($this->multiDomain){			// マルチドメイン運用の場合
			$path = '/' . $pathArray[count($pathArray) - 1];
		} else {
			for ($i = 0; $i < count($pathArray); $i++){
				$path .= '/' . $pathArray[$i];
			}
		}
		if ($isSslPage){			// SSLページのとき
			$url = $this->getSslRootUrl() . $path . '.php';
		} else {
			$url = $this->getRootUrl() . $path . '.php';
		}
		return $url;
	}
	/**
	 * 現在のページID、サブページIDのURLを作成(セッションID付き)
	 *
	 * @return string		作成したURL
	 */
	/*public function createCurrentPageUrlWithSessionId()
	{
		return $this->getCurrentScriptUrl() . '?sub=' . $this->getCurrentPageSubId() . '&' . session_name() . '=' . session_id();
	}*/
	/**
	 * 携帯用の現在のページID、サブページIDのURLを作成
	 *
	 * 携帯用URLには以下の情報を付加する
	 * ・セッションID
	 * ・ドコモ端末の場合はiモードID受信用のパラメータを付加
	 *
	 * @param string,array	$addParam		追加パラメータ
	 * @param bool          $withSessionId	セッションIDを付加するかどうか
	 * @return string		作成したURL
	 */
	public function createCurrentPageUrlForMobile($addParam = '', $withSessionId = true)
	{
		// 携帯用パラメータ取得
		$param = $this->_getMobileUrlParam($withSessionId);
		
		// ページサブID付加
		$param['sub'] = $this->getCurrentPageSubId();

		// 追加パラメータがある場合
		if (!empty($addParam)){
			if (is_array($addParam)){		// 配列の場合
				$newParam = $addParam;
			} else {		// 文字列の場合
				$newParam = array();
				$addParamArray = explode('&', trim($addParam, "?&"));
				for ($i = 0; $i < count($addParamArray); $i++){
					list($key, $value) = explode('=', $addParamArray[$i]);
					$key = trim($key);
					$value = trim($value);
					$newParam[$key] = $value;
				}
			}
			$param = array_merge($param, $newParam);
		}
		//$url = $this->_createUrl($this->getCurrentScriptUrl(), $param);
		$url = createUrl($this->createPageUrl(), $param);
		return $url;
	}
	/**
	 * デフォルト言語取得
	 */
	public function getDefaultLanguage()
	{
		return $this->defaultLanguage;
	}
	/**
	 * デフォルトの言語名をカレントの言語で表したもの
	 */
	public function getDefaultLanguageNameByCurrentLanguage()
	{
		return $this->db->getLanguageNameByDispLanguageId($this->defaultLanguage, $this->currentLanguage);
	}
	/**
	 * カレント言語取得
	 */
	public function getCurrentLanguage()
	{
		return $this->currentLanguage;
	}
	/**
	 * カレント言語設定
	 */
	public function setCurrentLanguage($value)
	{
		$this->currentLanguage = $value;
		
		// ロケールも変更
		$locale = $this->defaultLacaleArray[$value];
		if (!empty($locale)) $this->currentLocale = $locale;
	}
	/**
	 * デフォルトロケール取得
	 */
	public function getDefaultLocale()
	{
		return $this->defaultLocale;
	}
	/**
	 * カレントロケール取得
	 */
	public function getCurrentLocale()
	{
		return $this->currentLocale;
	}
	/**
	 * カレントロケール設定
	 */
	public function setCurrentLocale($value)
	{
		$this->currentLocale = $value;
	}
	/**
	 * カレント言語の変更可否を取得
	 */
	public function getCanChangeLang()
	{
		return $this->canChangeLang;
	}
	/**
	 * 多言語対応かどうかを取得(廃止予定 => isMultiLanguageSite())
	 */
	public function getMultiLanguage()
	{
		return $this->multiLanguage;
	}
	/**
	 * 多言語対応サイトかどうかを取得
	 */
	public function isMultiLanguageSite()
	{
		return $this->multiLanguage;
	}
	/**
	 * マルチドメイン運用かどうかを取得
	 *
	 * @param bool		true=マルチドメイン運用、false=シングルドメイン運用
	 */
	public function isMultiDomain()
	{
		return $this->multiDomain;		// マルチドメイン運用かどうか
	}
	/**
	 * SSL機能を使用するかどうかを取得
	 */
	public function getUseSsl()
	{
		return $this->useSsl;
	}
	/**
	 * 管理画面にSSL機能を使用するかどうかを取得
	 */
	public function getUseSslAdmin()
	{
		return $this->useSslAdmin;
	}
	/**
	 * 携帯用の入出力エンコーディングを設定
	 *
	 * @param string		エンコーディング
	 */
	public function setMobileEncoding($value)
	{
		$this->mobileEncoding = $value;
	}
	/**
	 * 携帯用の入出力エンコーディングを取得
	 *
	 * @return string		エンコーディング
	 */
	public function getMobileEncoding()
	{
		return $this->mobileEncoding;
	}
	/**
	 * 携帯用HTML上のエンコーディング表記を設定
	 *
	 * @param string		エンコーディング
	 */
	public function setMobileCharset($value)
	{
		$this->mobileCharset = $value;
	}
	/**
	 * 携帯用HTML上のエンコーディング表記を取得
	 *
	 * @return string		エンコーディング
	 */
	public function getMobileCharset()
	{
		return $this->mobileCharset;
	}
	/**
	 * DBセッションが使用できるかどうか
	 */
	public function canUseDbSession()
	{
		return $this->canUseDbSession;
	}
	/**
	 * DBが使用可能かどうか
	 */
	public function canUseDb()
	{
		return $this->canUseDb;
	}
	/**
	 * クッキーが使用可能かどうかを設定
	 *
	 * @param bool			true=使用可、false=使用不可
	 * @return				なし
	 */
	public function setCanUseCookie($value)
	{
		$this->canUseCookie = $value;
	}
	/**
	 * クッキーが使用可能かどうか
	 *
	 * @return bool			true=使用可、false=使用不可
	 */
	public function canUseCookie()
	{
		return $this->canUseCookie;
	}
	/**
	 * Timestamp型データの初期値を取得
	 *
	 * @param string Timestmp型初期データ文字列
	 */
	public function getInitValueOfTimestamp()
	{
		if ($this->db->getDbType() == M3_DB_TYPE_MYSQL){		// MySQLの場合
			return M3_TIMESTAMP_INIT_VALUE_MYSQL;
		} else if ($this->db->getDbType() == M3_DB_TYPE_PGSQL){
			return M3_TIMESTAMP_INIT_VALUE_PGSQL;
		} else {
			return '';
		}
	}
	/**
	 * Date型データの初期値を取得
	 *
	 * @param string Date型初期データ文字列
	 */
	public function getInitValueOfDate()
	{
		if ($this->db->getDbType() == M3_DB_TYPE_MYSQL){		// MySQLの場合
			return M3_DATE_INIT_VALUE_MYSQL;
		} else if ($this->db->getDbType() == M3_DB_TYPE_PGSQL){
			return M3_DATE_INIT_VALUE_PGSQL;
		} else {
			return '';
		}
	}
	/**
	 * サイトの名称を取得
	 *
	 * @param bool $reload	データを再取得するかどうか
	 * @return string		サイト名称
	 */
	public function getSiteName($reload = false)
	{
		// DBが使用不可のときはデフォルト名を返す
		if (!$this->canUseDb) return self::DEFAULT_SITE_NAME;
		
		if ($reload || empty($this->siteName)){			// サイト名称
			$this->siteName = $this->gSystem->getSiteDef(M3_TB_FIELD_SITE_NAME);
			if (empty($this->siteName)) $this->siteName = self::DEFAULT_SITE_NAME;
		}
		return $this->siteName;
	}
	/**
	 * サイトの所有者を取得
	 *
	 * @param bool $reload	データを再取得するかどうか
	 * @return string		サイト所有者
	 */
	public function getSiteOwner($reload = false)
	{
		// DBが使用不可のときは空文字列を返す
		if (!$this->canUseDb) return '';
		
		if ($reload || empty($this->siteOwner)){			// サイト所有者
			$this->siteOwner = $this->gSystem->getSiteDef(M3_TB_FIELD_SITE_OWNER);
		}
		return $this->siteOwner;
	}
	/**
	 * サイトコピーライトを取得
	 *
	 * @param bool $reload	データを再取得するかどうか
	 * @return string		サイトコピーライト
	 */
	public function getSiteCopyRight($reload = false)
	{
		// DBが使用不可のときは空文字列を返す
		if (!$this->canUseDb) return '';
		
		if ($reload || empty($this->siteCopyRight)){			// サイトコピーライト
			$this->siteCopyRight = $this->gSystem->getSiteDef(M3_TB_FIELD_SITE_COPYRIGHT);
		}
		return $this->siteCopyRight;
	}
	/**
	 * サイトEメールを取得
	 *
	 * @param bool $reload	データを再取得するかどうか
	 * @return string		サイトEメール
	 */
	public function getSiteEmail($reload = false)
	{
		// DBが使用不可のときは空文字列を返す
		if (!$this->canUseDb) return '';
		
		if ($reload || empty($this->siteEmail)){			// サイトEメール
			$this->siteEmail = $this->gSystem->getSiteDef(M3_TB_FIELD_SITE_EMAIL);
		}
		return $this->siteEmail;
	}
	/**
	 * 現在選択中のメニュー項目を設定
	 *
	 * @param array $menuItemData	メニュー項目データ(title=タイトル、url=リンク)の配列
	 * @return 						なし
	 */
	public function setSelectedMenuItems($menuItemData)
	{
		$this->selectedMenuItems = $menuItemData;				// 現在選択中のメニュー項目
	}
	/**
	 * 現在選択中のメニュー項目を取得
	 *
	 * @return array				メニュー項目データ(title=タイトル、url=リンク)
	 */
	public function getSelectedMenuItems()
	{
		return $this->selectedMenuItems;				// 現在選択中のメニュー項目
	}
	/**
	 * デフォルトCSV区切り文字コードを取得
	 *
	 * @return string		区切り文字
	 */
	public function getDefaultCsvDelimCode()
	{
		static $code;
		
		if (!isset($code)){		// 設定されていないとき
			$retValue = $this->gSystem->getSystemConfig(self::DEFAULT_CSV_DELIM_CODE);
			if (empty($retValue)){
				$code = ',';
			} else {
				$code = $retValue;
			}
		}
		return $code;
	}
	/**
	 * デフォルトCSV改行コードを取得
	 *
	 * @return string		改行文字
	 */
	public function getDefaultCsvNLCode()
	{
		static $code;
		
		if (!isset($code)){		// 設定されていないとき
			$retValue = $this->gSystem->getSystemConfig(self::DEFAULT_CSV_NL_CODE);
			if (empty($retValue)){
				$code = '\r\n';
			} else {
				$code = $retValue;
			}
		}
		return $code;
	}
	/**
	 * デフォルトCSVファイル拡張子を取得
	 *
	 * @return string		改行文字
	 */
	public function getDefaultCsvFileSuffix()
	{
		return $this->gSystem->getSystemConfig(self::DEFAULT_CSV_FILE_SUFFIX);
	}
	/**
	 * CSVファイルのダウンロードエンコーディングを取得
	 *
	 * @return string 		エンコーディング
	 */
	public function getCsvDownloadEncoding()
	{
		static $encoding;
		
		if (!isset($encoding)){		// 設定されていないとき
			$retValue = $this->gSystem->getSystemConfig(self::CF_CSV_DOWNLOAD_ENCODING);
			if (empty($retValue)){
				$encoding = 'SJIS-win';
			} else {
				$encoding = $retValue;
			}
		}
		return $encoding;
	}
	/**
	 * CSVファイルのアップロードロードエンコーディングを取得
	 *
	 * @return string 		エンコーディング
	 */
	public function getCsvUploadEncoding()
	{
		static $encoding;
		
		if (!isset($encoding)){		// 設定されていないとき
			$retValue = $this->gSystem->getSystemConfig(self::CF_CSV_UPLOAD_ENCODING);
			if (empty($retValue)){
				$encoding = 'SJIS-win';
			} else {
				$encoding = $retValue;
			}
		}
		return $encoding;
	}
	/**
	 * ウィジェット実行ログを残すかどうか
	 *
	 * @return bool		true=ログ出力、false=ログ出力しない
	 */
	public function getWidgetLog()
	{
		return $this->widgetLog;
	}
	/**
	 * PC用URLへのアクセスかどうかを設定(管理画面はPC用URLとしない)
	 *
	 * @param bool $status			true=PC用アクセス、false=PC用管理画面のアクセス
	 * @return 			なし
	 */
	public function setIsPcSite($status)
	{
		$this->isPcSite = $status;
	}
	/**
	 * PC用URLへのアクセスかどうか(管理画面はPC用URLとしない)
	 *
	 * @return bool		true=PC用アクセス、false=PC用管理画面のアクセス
	 */
	public function getIsPcSite()
	{
		return $this->isPcSite;
	}
	/**
	 * 携帯用URLへのアクセスかどうかを設定
	 *
	 * @param bool $status			true=携帯アクセス、false=通常アクセス
	 * @return 			なし
	 */
	public function setIsMobileSite($status)
	{
		$this->isMobileSite = $status;
		
		if ($this->isMobile() && $status){
			// ##### 携帯用の設定 #####
			// セッションをURLに保存
			ini_set('session.use_cookies', 0);	// クッキーは使用しない
		}
	}
	/**
	 * 携帯用URLへのアクセスかどうか
	 *
	 * @return bool		true=携帯アクセス、false=通常アクセス
	 */
	public function getIsMobileSite()
	{
		return $this->isMobileSite;
	}
	/**
	 * スマートフォン用URLへのアクセスかどうかを設定
	 *
	 * @param bool $status			true=スマートフォン用URLへのアクセス、false=スマートフォン用URL以外へのアクセス
	 * @return 			なし
	 */
	public function setIsSmartphoneSite($status)
	{
		$this->isSmartphoneSite = $status;
	}
	/**
	 * スマートフォン用URLへのアクセスかどうか
	 *
	 * @return bool		true=スマートフォン用URLへのアクセス、false=スマートフォン用URL以外へのアクセス
	 */
	public function getIsSmartphoneSite()
	{
		return $this->isSmartphoneSite;
	}
	/**
	 * サブウィジェットの起動かどうかを設定
	 *
	 * @param bool $status			true=サブウィジェットでの起動、false=通常のウィジェット起動
	 * @return 			なし
	 */
	public function setIsSubWidget($status)
	{
		$this->isSubWidget = $status;
	}
	/**
	 * サブウィジェットの起動かどうか
	 *
	 * @return bool		true=サブウィジェットでの起動、false=通常のウィジェット起動
	 */
	public function getIsSubWidget()
	{
		return $this->isSubWidget;
	}
	/**
	 * サーバ接続かどうかを設定
	 *
	 * @param bool $status			true=サーバ接続、false=サーバ接続でない
	 * @return 			なし
	 */
	public function setIsServerConnector($status)
	{
		$this->isServerConnector = $status;
	}
	/**
	 * サーバ接続かどうか
	 *
	 * @return bool		true=サーバ接続、false=サーバ接続でない
	 */
	public function isServerConnector()
	{
		return $this->isServerConnector;
	}
	/**
	 * 携帯端末IDを取得
	 *
	 * @return string		携帯端末ID
	 */
	public function getMobileId()
	{
		global $gInstanceManager;
		global $gRequestManager;
		
		$agent = $gInstanceManager->getMobileAgent();
		if ($agent->isDoCoMo()){	// ドコモ端末のとき
			$mobileId = $gRequestManager->trimServerValueOf('HTTP_X_DCMGUID');
			if (!empty($mobileId)) $mobileId = 'DC-' . $mobileId;		// キャリアコードを付加
		} else if ($agent->isEZweb()){	// au端末のとき
			$mobileId = $gRequestManager->trimServerValueOf('HTTP_X_UP_SUBNO');
			// ドメイン名を消去
			$pos = strpos($mobileId, '.ezweb.ne.jp');
			if ($pos !== false) $mobileId = substr($mobileId, 0, $pos);
			if (!empty($mobileId)) $mobileId = 'AU-' . $mobileId;		// キャリアコードを付加
		} else if ($agent->isSoftBank()){	// ソフトバンク端末のとき
			$mobileId = $gRequestManager->trimServerValueOf('HTTP_X_JPHONE_UID');
			if (!empty($mobileId)) $mobileId = 'SB-' . $mobileId;		// キャリアコードを付加
		} else {		// その他の端末のとき(PC用)
			$mobileId = '';
		}
		return $mobileId;
	}
	/**
	 * 携帯端末でのアクセスかどうか
	 *
	 * @return bool		true=携帯端末アクセス、false=携帯端末以外からのアクセス
	 */
	public function isMobile()
	{
		global $gInstanceManager;
		static $isMobile;
		
		if (!isset($isMobile)){
			$isMobile = false;
			$agent = $gInstanceManager->getMobileAgent();
			if (method_exists($agent, 'isNonMobile')){
				if (!$agent->isNonMobile()){			// 携帯端末でのアクセスの場合
					$isMobile = true;
				}
			}
		}
		return $isMobile;
	}
	/**
	 * スマートフォン端末でのアクセスかどうか
	 *
	 * @return bool		true=スマートフォン端末アクセス、false=スマートフォン端末以外からのアクセス
	 */
	public function isSmartphone()
	{
		global $gRequestManager;
		static $isSmartphone;
		
		if (!isset($isSmartphone)){
			$isSmartphone = false;
			$agent = $gRequestManager->trimServerValueOf('HTTP_USER_AGENT');
			if (preg_match('/android/i', $agent)){
				$isSmartphone = true;
			} else if (preg_match('/ipod/i', $agent) || preg_match('/iphone/i', $agent)){
				$isSmartphone = true;
			}
		}
		return $isSmartphone;
	}
	/**
	 * 携帯用のURLパラメータを取得
	 *
	 * @param bool   $withSessionId	セッションIDを付加するかどうか
	 * @return array				URLパラメータ
	 */
	function _getMobileUrlParam($withSessionId = true)
	{
		global $gInstanceManager;
		
		$param = array();
		$agent = $gInstanceManager->getMobileAgent();
		if (method_exists($agent, 'isNonMobile')){
			if (!$agent->isNonMobile()){			// 携帯端末でのアクセスの場合
				// ログインしている場合はセッションIDを付加(セッション管理機能が使用可能なときのみ)
				//if (!empty($this->mobileUseSession) && $this->isCurrentUserLogined()) $param[session_name()] = session_id();
				// ログイン状況に関わらずセッションIDを付加(セッション管理機能が使用可能なときのみ)
				if ($withSessionId && !empty($this->mobileUseSession)) $param[session_name()] = session_id();
					
				// ドコモ端末の場合はiモードIDを送信させる
				if ($agent->isDoCoMo()) $param['guid'] = 'ON';
			}
		}
		return $param;
	}
	/**
	 * メニューの表示属性を設定
	 *
	 * @param array $attr		メニュー表示属性
	 * @return 					なし
	 */
	public function setMenuAttr($attr)
	{
		$this->menuAttr = $attr;
	}
	/**
	 * メニューの表示属性を取得
	 *
	 * @return array		メニュー表示属性
	 */
	public function getMenuAttr()
	{
		return $this->menuAttr;
	}
	/**
	 * Joomla!ドキュメントを設定
	 *
	 * @param object $doc			Joomla!ドキュメントオブジェクト
	 * @return 						なし
	 */
	public function setJoomlaDocument($text)
	{
		$this->joomlaDocument = $text;
	}
	/**
	 * Joomla!ドキュメントを取得
	 *
	 * @return object		Joomla!ドキュメントオブジェクト
	 */
	public function getJoomlaDocument()
	{
		return $this->joomlaDocument;
	}
	/**
	 * Joomla!v1.5用メニューコンテンツを設定
	 *
	 * @param string $text			メニューコンテンツ文字列
	 * @return 			なし
	 */
	public function setJoomlaMenuContent($text)
	{
		$this->joomlaMenuContent = $text;
	}
	/**
	 * Joomla!v1.5用メニューコンテンツを取得
	 *
	 * @return string		メニューコンテンツ文字列
	 */
	public function getJoomlaMenuContent()
	{
		return $this->joomlaMenuContent;
	}
	/**
	 * Joomla!v2.5用メニュー階層データを設定
	 *
	 * @param array $menuData		メニュー階層データ
	 * @return 						なし
	 */
	public function setJoomlaMenuData($menuData)
	{
		$this->joomlaMenuData = $menuData;
	}
	/**
	 * Joomla!v2.5用メニュー階層データを取得
	 *
	 * @return array		メニューデータ
	 */
	public function getJoomlaMenuData()
	{
		return $this->joomlaMenuData;
	}
	/**
	 * Joomla!v2.5用ページ前後遷移データを設定
	 *
	 * @param array $navData		ページ遷移データ
	 * @return 						なし
	 */
	public function setJoomlaPageNavData($navData)
	{
		$this->joomlaPageNavData = $navData;
	}
	/**
	 * Joomla!v2.5用ページ前後遷移データを取得
	 *
	 * @return array		ページ遷移データ
	 */
	public function getJoomlaPageNavData()
	{
		return $this->joomlaPageNavData;
	}
	/**
	 * Joomla!v2.5用ページ番号遷移データを設定
	 *
	 * @param array $data		ページ遷移データ
	 * @return 					なし
	 */
	public function setJoomlaPaginationData($data)
	{
		$this->joomlaPaginationData = $data;
	}
	/**
	 * Joomla!v2.5用ページ番号遷移データを取得
	 *
	 * @return array		ページ遷移データ
	 */
	public function getJoomlaPaginationData()
	{
		return $this->joomlaPaginationData;
	}
	/**
	 * Joomla!用ビュー作成用データを設定
	 *
	 * @param array $viewData		ビュー作成データ
	 * @return 						なし
	 */
	public function setJoomlaViewData($viewData)
	{
		$this->joomlaViewData = $viewData;
	}
	/**
	 * Joomla!用ビュー作成用データを取得
	 *
	 * @return array		メニューデータ
	 */
	public function getJoomlaViewData()
	{
		return $this->joomlaViewData;
	}
	/**
	 * リモート表示コンテンツを設定
	 *
	 * @param string $position		配置ポジション名
	 * @param string $data			コンテンツデータ
	 * @return 						なし
	 */
	public function setRemoteContent($position, $data)
	{
		$this->remoteContent[$position] = $data;
	}
	/**
	 * リモート表示コンテンツを取得
	 *
	 * @param string $position		配置ポジション名
	 * @return string				コンテンツデータ
	 */
	public function getRemoteContent($position)
	{
		return $this->remoteContent[$position];
	}
}
?>
