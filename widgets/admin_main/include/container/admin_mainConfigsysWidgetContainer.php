<?php
/**
 * コンテナクラス
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2013 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: admin_mainConfigsysWidgetContainer.php 5966 2013-04-27 09:11:24Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/admin_mainConfigsystemBaseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/admin_mainDb.php');
require_once(M3_SYSTEM_INCLUDE_PATH . '/common/scriptLibInfo.php');

class admin_mainConfigsysWidgetContainer extends admin_mainConfigsystemBaseWidgetContainer
{
	private $db;	// DB接続オブジェクト
	private $adminTheme;		// 管理画面用jQueryUIテーマ
	private $defaultTheme;		// 一般画面用jQueryUIテーマ
	private $systemTemplate;// システム画面用テンプレート
	private $jqueryVersion;			// jQueryバージョン
	private $wysiwygMenuData;		// WYSIWYGエディター選択メニューデータ
	private $wysiwygEditor;			// 管理画面用WYSIWYGエディター
	const MENU_ITEM_DEVELOP = 'develop';				// 「開発」メニューの識別ID
	const DEFAULT_THEME_DIR = '/ui/themes';				// jQueryUIテーマ格納ディレクトリ
	const DEFAULT_SYSTEM_TEMPLATE_ID = '_system';				// デフォルトのシステム画面用テンプレート
	const DEFAULT_JQUERY_VERSION = '1.8';				// デフォルトのjQueryバージョン
	
	// DB定義値
	const CF_USE_SSL = 'use_ssl';		// SSL機能を使用するかどうか
	const CF_USE_SSL_ADMIN = 'use_ssl_admin';		// 管理画面にSSL機能を使用するかどうか
	const CF_SITE_IN_PUBLIC = 'site_in_public';			// サイト公開状況
	const CF_SITE_PC_IN_PUBLIC = 'site_pc_in_public';				// PC用サイトの公開状況
	const CF_SITE_MOBILE_IN_PUBLIC = 'site_mobile_in_public';		// 携帯用サイトの公開状況
	const CF_SITE_SMARTPHONE_IN_PUBLIC = 'site_smartphone_in_public';		// スマートフォン用サイトの公開状況
	const CF_MOBILE_AUTO_REDIRECT = 'mobile_auto_redirect';					// 携帯の自動遷移
	const CF_SMARTPHONE_AUTO_REDIRECT = 'smartphone_auto_redirect';			// スマートフォンの自動遷移
	const CF_SITE_SMARTPHONE_URL = 'site_smartphone_url';		// スマートフォン用サイトURL
	const CF_SITE_MOBILE_URL = 'site_mobile_url';		// 携帯用サイトURL
	const CF_MULTI_DOMAIN = 'multi_domain';		// マルチドメイン運用
//	const CF_USE_SITE_PC			= 'use_site_pc';			// PC用サイト使用
//	const CF_USE_SITE_SMARTPHONE	= 'use_site_smartphone';	// スマートフォン用サイト使用
//	const CF_USE_SITE_MOBILE		= 'use_site_mobile';		// 携帯用サイト使用
	const CF_SITE_ACCESS_EXCEPTION_IP = 'site_access_exception_ip';		// アクセス制御、例外とするIP
	const CF_DISTRIBUTION_NAME = 'distribution_name';		// ディストリビューション名
	const CF_DISTRIBUTION_VERSION = 'distribution_version';		// ディストリビューションバージョン
	const CF_MOBILE_USE_SESSION = 'mobile_use_session';		// 携帯でセッション管理を行うかどうか
	const CF_USE_PAGE_CACHE = 'use_page_cache';		// 画面キャッシュ機能を使用するかどうか
	const CF_USE_TEMPLATE_ID_IN_SESSION = 'use_template_id_in_session';			// セッションにテンプレートIDを保存
	const CF_SSL_URL = 'ssl_root_url';				// SSL用のルートURL
	const CF_CONNECT_SERVER_URL = 'default_connect_server_url';			// ポータル接続先URL
	const CF_CONFIG_WINDOW_OPEN_TYPE = 'config_window_open_type';		// ウィジェット設定画面のウィンドウ表示タイプ(0=別ウィンドウ、1=タブ)
	const CF_SYSTEM_TEMPLATE = 'msg_template';			// メッセージ用テンプレート取得キー
	const CF_ADMIN_DEFAULT_THEME = 'admin_default_theme';		// 管理画面用jQueryUIテーマ
	const CF_DEFAULT_THEME = 'default_theme';		// 一般画面用jQueryUIテーマ
	const CF_HIERARCHICAL_PAGE = 'hierarchical_page';		// 階層化ページを使用するかどうか
	const CF_MULTI_LANGUAGE = 'multi_language';			// 多言語対応
	const CF_JQUERY_VERSION = 'jquery_version';			// jQueryバージョン
	const CF_USE_JQUERY = 'use_jquery';			// jQueryを常に使用するかどうか
	const CF_WYSIWYG_EDITOR = 'wysiwyg_editor';		// 管理画面用WYSIWYGエディター
	const CF_PERMIT_DETAIL_CONFIG	= 'permit_detail_config';				// 詳細設定が可能かどうか
	const CF_SERVER_ID = 'server_id';		// サーバID
	const CF_DEFAULT_LANG		= 'default_lang';					// デフォルト言語
	const CF_INSTALL_DT = 'install_dt';		// システムインストール日時
	const CF_WORK_DIR = 'work_dir';			// 作業ディレクトリ
	// 未使用
	const CF_REGENERATE_SESSION_ID = 'regenerate_session_id';				// セッションIDを毎回更新するかどうか
	const CF_SCRIPT_CACHE_IN_BROWSER = 'script_cache_in_browser';				// ブラウザにスクリプトのキャッシュを保持するかどうか
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DB接続オブジェクト作成
		$this->db = new admin_mainDb();
		
		$this->wysiwygMenuData = array('fckeditor' => 'FCKEditor 2.6.6', 'ckeditor' => 'CKEditor 4.1.0');		// WYSIWYGエディター選択メニューデータ
	}
	/**
	 * テンプレートファイルを設定
	 *
	 * _assign()でデータを埋め込むテンプレートファイルのファイル名を返す。
	 * 読み込むディレクトリは、「自ウィジェットディレクトリ/include/template」に固定。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。そのまま_assign()に渡る
	 * @return string 						テンプレートファイル名。テンプレートライブラリを使用しない場合は空文字列「''」を返す。
	 */
	function _setTemplate($request, &$param)
	{	
		return 'configsystem.tmpl.html';
	}
	/**
	 * テンプレートにデータ埋め込む
	 *
	 * _setTemplate()で指定したテンプレートファイルにデータを埋め込む。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。_setTemplate()と共有。
	 * @param								なし
	 */
	function _assign($request, &$param)
	{
		$this->defaultLang		= $this->gEnv->getDefaultLanguage();
		
		$act = $request->trimValueOf('act');
		$useSsl = ($request->trimValueOf('item_use_ssl') == 'on') ? 1 : 0;		// SSL機能を使用するかどうか
		$useSslAdmin = ($request->trimValueOf('item_use_ssl_admin') == 'on') ? 1 : 0;		// 管理画面にSSL機能を使用するかどうか
		$useHierPage = ($request->trimValueOf('item_use_hier_page') == 'on') ? 1 : 0;		// 階層化ページを使用するかどうか
		$sslUrl = $request->trimValueOf('item_ssl_url');			// SSL用のURL
		$connectServerUrl = $request->trimValueOf('item_connect_server_url');			// ポータル接続先URL
		$siteSmartphoneUrl = $request->trimValueOf('item_site_smartphone_url');		// スマートフォン用サイトURL
		$siteMobileUrl = $request->trimValueOf('item_site_mobile_url');		// 携帯用サイトURL
		$usePageCache = ($request->trimValueOf('item_use_page_cache') == 'on') ? 1 : 0;		// 表示キャッシュ機能を使用するかどうか
		$canChangeTemplate = ($request->trimValueOf('item_can_change_template') == 'on') ? 1 : 0;		// ユーザによるテンプレート変更を許可するかどうか
		$canDetailConfig = ($request->trimValueOf('item_can_detail_config') == 'on') ? 1 : 0;		// 詳細システム設定が可能かどうか
//		$regenerateSession = ($request->trimValueOf('item_regenerate_sesison') == 'on') ? 1 : 0;		// セッションIDを更新するかどうか
//		$scriptCacheInBrowser = ($request->trimValueOf('item_script_cache_in_browser') == 'on') ? 1 : 0;		// ブラウザにスクリプトのキャッシュを保持するかどうか
		$mobileAutoRedirect = ($request->trimValueOf('item_mobile_auto_redirect') == 'on') ? 1 : 0;		// 携帯の自動遷移
		$smartphoneAutoRedirect = ($request->trimValueOf('item_smartphone_auto_redirect') == 'on') ? 1 : 0;		// スマートフォンの自動遷移
		$mobileUseSession = ($request->trimValueOf('item_mobile_use_session') == 'on') ? 1 : 0;		// 携帯でセッション管理するかどうか
		$sitePcInPublic = ($request->trimValueOf('item_site_pc_in_public') == 'on') ? 1 : 0;			// PC用サイトの公開状況
		$siteMobileInPublic = ($request->trimValueOf('item_site_mobile_in_public') == 'on') ? 1 : 0;	// 携帯用サイトの公開状況
		$siteSmartphoneInPublic = ($request->trimValueOf('item_site_smartphone_in_public') == 'on') ? 1 : 0;	// スマートフォン用サイトの公開状況
		$multiDomain = ($request->trimValueOf('item_multi_domain') == 'on') ? 1 : 0;// マルチドメイン運用
		$isActiveSitePc = ($request->trimValueOf('item_is_active_site_pc') == 'on') ? 1 : 0;	// PC用サイト有効
		$isActiveSiteSmartphone = ($request->trimValueOf('item_is_active_site_smartphone') == 'on') ? 1 : 0;	// スマートフォン用サイト有効
		$isActiveSiteMobile = ($request->trimValueOf('item_is_active_site_mobile') == 'on') ? 1 : 0;	// 携帯用サイト有効
		$configWindowOpenByTab = ($request->trimValueOf('item_config_window_open_by_tab') == 'on') ? 1 : 0;			// ウィジェット設定画面をタブで開くかどうか
		$multiLanguage = ($request->trimValueOf('item_multi_language') == 'on') ? 1 : 0;			// 多言語対応
		$lang = $request->trimValueOf('item_lang');
		$workDir = $request->trimValueOf('item_work_dir');
		$this->systemTemplate = $request->trimValueOf('item_systemplate');	// システム画面用テンプレート
		$this->adminTheme = $request->trimValueOf('item_admin_theme');		// 管理画面用jQueryUIテーマ
		$this->defaultTheme = $request->trimValueOf('item_default_theme');		// 一般画面用jQueryUIテーマ
		$this->jqueryVersion = $request->trimValueOf('item_jquery_version');		// jQueryバージョン
		$useJquery = ($request->trimValueOf('item_use_jquery') == 'on') ? 1 : 0;			// 常にjQueryを使用するかどうか
		$this->wysiwygEditor = $request->trimValueOf('item_wysiwyg_editor');			// 管理画面用WYSIWYGエディター
		
		if ($act == 'update'){		// 設定更新のとき
			$isErr = false;
			if (!$isErr && !empty($lang)){
				if (!$this->db->updateSystemConfig(self::CF_DEFAULT_LANG, $lang)) $isErr = true;
			}
			if (!$isErr){
				if (!$this->db->updateSystemConfig(self::CF_USE_SSL, $useSsl)) $isErr = true;			// SSL機能を使用するかどうか
			}
			if (!$isErr) if (!$this->db->updateSystemConfig(self::CF_USE_SSL_ADMIN, $useSslAdmin)) $isErr = true;			// SSL機能を使用するかどうか
			if (!$isErr) if (!$this->db->updateSystemConfig(self::CF_HIERARCHICAL_PAGE, $useHierPage)) $isErr = true;			// 階層化ページを使用するかどうか
			if (!$isErr) if (!$this->db->updateSystemConfig(self::CF_USE_PAGE_CACHE, $usePageCache)) $isErr = true;			// 表示キャッシュ機能を使用するかどうか
			if (!$isErr) if (!$this->db->updateSystemConfig(self::CF_USE_TEMPLATE_ID_IN_SESSION, $canChangeTemplate)) $isErr = true;// ユーザによるテンプレート変更を許可するかどうか
			if (!$isErr){
				if (!$this->db->updateSystemConfig(self::CF_PERMIT_DETAIL_CONFIG, $canDetailConfig)) $isErr = true;
			}
			/*	
			if (!$isErr){
				if (!$this->db->updateSystemConfig(self::CF_REGENERATE_SESSION_ID, $regenerateSession)) $isErr = true;
			}
			if (!$isErr){
				if (!$this->db->updateSystemConfig(self::CF_SCRIPT_CACHE_IN_BROWSER, $scriptCacheInBrowser)) $isErr = true;
			}
			*/
			if (!$isErr){
				if (!$this->db->updateSystemConfig(self::CF_MOBILE_AUTO_REDIRECT, $mobileAutoRedirect)) $isErr = true;
			}
			if (!$isErr){
				if (!$this->db->updateSystemConfig(self::CF_SMARTPHONE_AUTO_REDIRECT, $smartphoneAutoRedirect)) $isErr = true;
			}
			if (!$isErr){
				if (!$this->db->updateSystemConfig(self::CF_MOBILE_USE_SESSION, $mobileUseSession)) $isErr = true;// 携帯でセッション管理するかどうか
			}
			if (!$isErr){
				if (!$this->db->updateSystemConfig(self::CF_SITE_PC_IN_PUBLIC, $sitePcInPublic)) $isErr = true;
			}
			if (!$isErr){
				if (!$this->db->updateSystemConfig(self::CF_SITE_MOBILE_IN_PUBLIC, $siteMobileInPublic)) $isErr = true;
			}
			if (!$isErr){
				if (!$this->db->updateSystemConfig(self::CF_SITE_SMARTPHONE_IN_PUBLIC, $siteSmartphoneInPublic)) $isErr = true;
			}
			if (!$isErr){
				if (!$this->db->updateSystemConfig(self::CF_MULTI_DOMAIN, $multiDomain)) $isErr = true;// マルチドメイン運用
			}
			if (!$isErr){
				//if (!$this->db->updateSystemConfig(self::CF_USE_SITE_PC, $isActiveSitePc)) $isErr = true;// PC用サイト使用
				if (!$this->updateActiveAccessPoint(0/*PC*/, $isActiveSitePc)) $isErr = true;// PC用サイト有効
			}
			if (!$isErr){
				//if (!$this->db->updateSystemConfig(self::CF_USE_SITE_SMARTPHONE, $isActiveSiteSmartphone)) $isErr = true;// スマートフォン用サイト使用
				if (!$this->updateActiveAccessPoint(2/*スマートフォン*/, $isActiveSiteSmartphone)) $isErr = true;// スマートフォン用サイト有効
			}
			if (!$isErr){
				//if (!$this->db->updateSystemConfig(self::CF_USE_SITE_MOBILE, $isActiveSiteMobile)) $isErr = true;// 携帯用サイト使用
				if (!$this->updateActiveAccessPoint(1/*携帯*/, $isActiveSiteMobile)) $isErr = true;// 携帯用サイト有効
			}
			if (!$isErr){
				if (!$this->db->updateSystemConfig(self::CF_CONFIG_WINDOW_OPEN_TYPE, $configWindowOpenByTab)) $isErr = true;			// ウィジェット設定画面をタブで開くかどうか
			}
			if (!$isErr){
				if (!$this->db->updateSystemConfig(self::CF_MULTI_LANGUAGE, $multiLanguage)) $isErr = true;			// 多言語対応
			}
			if (!$isErr){
				$sslUrl = rtrim($sslUrl, '/');// 最後の「/」を除く
				$sslUrl = str_replace('http://', 'https://', $sslUrl);// httpの場合はhttpsに変更
				if (!$this->db->updateSystemConfig(self::CF_SSL_URL, $sslUrl)) $isErr = true;
			}
			if (!$isErr){
				$connectServerUrl = rtrim($connectServerUrl, '/');// 最後の「/」を除く
				if (!$this->db->updateSystemConfig(self::CF_CONNECT_SERVER_URL, $connectServerUrl)) $isErr = true;// ポータル接続先URL
			}
			if (!$isErr){
				$siteSmartphoneUrl = rtrim($siteSmartphoneUrl, '/');// 最後の「/」を除く
				if (!$this->db->updateSystemConfig(self::CF_SITE_SMARTPHONE_URL, $siteSmartphoneUrl)) $isErr = true;// スマートフォン用サイトURL
			}
			if (!$isErr){
				$siteMobileUrl = rtrim($siteMobileUrl, '/');// 最後の「/」を除く
				if (!$this->db->updateSystemConfig(self::CF_SITE_MOBILE_URL, $siteMobileUrl)) $isErr = true;// 携帯用サイトURL
			}
			if (!$isErr){
				$workDir = rtrim($workDir, '/');// 最後の「/」を除く
				if (!$this->db->updateSystemConfig(self::CF_WORK_DIR, $workDir)) $isErr = true;
			}
			if (!$isErr){
				if (!$this->db->updateSystemConfig(self::CF_SYSTEM_TEMPLATE, $this->systemTemplate)) $isErr = true;// システム画面用テンプレート
			}			
			if (!$isErr){
				if (!$this->db->updateSystemConfig(self::CF_ADMIN_DEFAULT_THEME, $this->adminTheme)) $isErr = true;// 管理画面用jQueryUIテーマ
			}
			if (!$isErr){
				if (!$this->db->updateSystemConfig(self::CF_DEFAULT_THEME, $this->defaultTheme)) $isErr = true;// 一般画面用jQueryUIテーマ
			}
			if (!$isErr){
				if (!$this->db->updateSystemConfig(self::CF_JQUERY_VERSION, $this->jqueryVersion)) $isErr = true;// jQueryバージョン
			}
			if (!$isErr){
				if (!$this->db->updateSystemConfig(self::CF_USE_JQUERY, $useJquery)) $isErr = true;// 常にjQueryを使用するかどうか
			}
			if (!$isErr){
				if (!$this->db->updateSystemConfig(self::CF_WYSIWYG_EDITOR, $this->wysiwygEditor)) $isErr = true;// 管理画面用WYSIWYGエディター
			}		
			
			if ($isErr){
				$this->setMsg(self::MSG_APP_ERR, 'データ更新に失敗しました');
			} else {
				$this->setMsg(self::MSG_GUIDANCE, 'データを更新しました');
			}
			// システムパラメータを更新
			$this->gEnv->loadSystemParams();
			
			// 値を再取得
			$this->defaultLang		= $this->gEnv->getDefaultLanguage();
			$useSsl				= $this->db->getSystemConfig(self::CF_USE_SSL);			// SSL機能を使用するかどうか
			$useSslAdmin		= $this->db->getSystemConfig(self::CF_USE_SSL_ADMIN);			// SSL機能を使用するかどうか
			$useHierPage		= $this->db->getSystemConfig(self::CF_HIERARCHICAL_PAGE);			// 階層化ページを使用するかどうか
			$sslUrl				= $this->db->getSystemConfig(self::CF_SSL_URL);			// SSLのURL
			$connectServerUrl	= $this->db->getSystemConfig(self::CF_CONNECT_SERVER_URL);// ポータル接続先URL
			$usePageCache 		= $this->db->getSystemConfig(self::CF_USE_PAGE_CACHE);			// 表示キャッシュ機能を使用するかどうか
			$canChangeTemplate	= $this->db->getSystemConfig(self::CF_USE_TEMPLATE_ID_IN_SESSION);// ユーザによるテンプレート変更を許可するかどうか
			$canDetailConfig	= $this->db->getSystemConfig(self::CF_PERMIT_DETAIL_CONFIG);
//			$regenerateSession	= $this->db->getSystemConfig(self::CF_REGENERATE_SESSION_ID);
//			$scriptCacheInBrowser = $this->db->getSystemConfig(self::CF_SCRIPT_CACHE_IN_BROWSER);
//			$mobileAutoRedirect	= $this->db->getSystemConfig(self::CF_MOBILE_AUTO_REDIRECT);
			$mobileAutoRedirect	= $this->gSystem->mobileAutoRedirect(true/*再取得*/);				// 携帯の自動遷移
			$smartphoneAutoRedirect	= $this->gSystem->smartphoneAutoRedirect(true/*再取得*/);		// スマートフォンの自動遷移
			$mobileUseSession = $this->db->getSystemConfig(self::CF_MOBILE_USE_SESSION);// 携帯でセッション管理するかどうか
			$workDir = $this->db->getSystemConfig(self::CF_WORK_DIR);
			$sitePcInPublic = $this->gSystem->sitePcInPublic(true/*再取得*/);			// PC用サイトの公開状況
			$siteMobileInPublic = $this->gSystem->siteMobileInPublic(true/*再取得*/);	// 携帯用サイトの公開状況
			$siteSmartphoneInPublic = $this->gSystem->siteSmartphoneInPublic(true/*再取得*/);	// スマートフォン用サイトの公開状況
			$multiDomain		= $this->db->getSystemConfig(self::CF_MULTI_DOMAIN);// マルチドメイン運用
			
/*			$isActiveSitePc			= $this->db->getSystemConfig(self::CF_USE_SITE_PC);	// PC用サイト使用
			$isActiveSiteSmartphone	= $this->db->getSystemConfig(self::CF_USE_SITE_SMARTPHONE);	// スマートフォン用サイト使用
			$isActiveSiteMobile		= $this->db->getSystemConfig(self::CF_USE_SITE_MOBILE);	// // 携帯用サイト使用
			*/
			$isActiveSitePc			= $this->isActiveAccessPoint(0/*PC*/);					// PC用サイト有効かどうか
			$isActiveSiteSmartphone	= $this->isActiveAccessPoint(2/*スマートフォン*/);		// スマートフォン用サイト有効かどうか
			$isActiveSiteMobile		= $this->isActiveAccessPoint(1/*スマートフォン*/);		// 携帯用サイト有効かどうか
			$siteSmartphoneUrl = $this->db->getSystemConfig(self::CF_SITE_SMARTPHONE_URL);		// スマートフォン用サイトURL
			$siteMobileUrl = $this->db->getSystemConfig(self::CF_SITE_MOBILE_URL);		// 携帯用サイトURL
			$configWindowOpenByTab = $this->db->getSystemConfig(self::CF_CONFIG_WINDOW_OPEN_TYPE);			// ウィジェット設定画面をタブで開くかどうか
			$multiLanguage = $this->gSystem->getSystemConfig(self::CF_MULTI_LANGUAGE);		// 多言語対応かどうか
			$this->systemTemplate		= $this->db->getSystemConfig(self::CF_SYSTEM_TEMPLATE);// システム画面用テンプレート
			$this->adminTheme = $this->db->getSystemConfig(self::CF_ADMIN_DEFAULT_THEME);		// 管理画面用jQueryUIテーマ
			$this->defaultTheme = $this->db->getSystemConfig(self::CF_DEFAULT_THEME);		// 一般画面用jQueryUIテーマ
			$this->jqueryVersion = $this->db->getSystemConfig(self::CF_JQUERY_VERSION);		// jQueryバージョン
			if (empty($this->jqueryVersion)) $this->jqueryVersion = self::DEFAULT_JQUERY_VERSION;
			$useJquery = $this->db->getSystemConfig(self::CF_USE_JQUERY);// 常にjQueryを使用するかどうか
			$this->wysiwygEditor = $this->db->getSystemConfig(self::CF_WYSIWYG_EDITOR);			// 管理画面用WYSIWYGエディター
			
			// メニュー項目の制御
			//$this->db->updateMenuVisible(self::MENU_ITEM_DEVELOP, $canDetailConfig);			// 「開発」メニュー
		} else if ($act == 'updateip'){		// IPアドレスを更新のとき
			$exceptIp = $request->trimValueOf('except_ip');

			// 入力チェック
			$this->checkIp($exceptIp, 'IP', true);
			
			// エラーなしの場合は、データを更新
			if ($this->getMsgCount() == 0){
				$isErr = false;
				if (!$this->db->updateSystemConfig(self::CF_SITE_ACCESS_EXCEPTION_IP, $exceptIp)) $isErr = true;
				if ($isErr){
					$this->setMsg(self::MSG_APP_ERR, 'データ更新に失敗しました');
				} else {
					$this->setMsg(self::MSG_GUIDANCE, 'データを更新しました');
				}
			}
		} else if ($act == 'siteopen'){		// サイト運用開始のとき
			$this->db->updateSystemConfig(self::CF_SITE_IN_PUBLIC, 1);
		} else if ($act == 'siteclose'){		// サイト運用停止のとき
			$this->db->updateSystemConfig(self::CF_SITE_IN_PUBLIC, 0);
		} else if ($act == 'clearcache'){		// キャッシュクリアのとき
			$ret = $this->gCache->clearAllCache();
			if ($ret){
				$this->setMsg(self::MSG_GUIDANCE, 'キャッシュをクリアしました');
			} else {
				$this->setMsg(self::MSG_APP_ERR, 'キャッシュをクリアに失敗しました');
			}
		} else {		// 初期表示の場合
			$useSsl				= $this->db->getSystemConfig(self::CF_USE_SSL);			// SSL機能を使用するかどうか
			$useSslAdmin		= $this->db->getSystemConfig(self::CF_USE_SSL_ADMIN);			// SSL機能を使用するかどうか
			$useHierPage		= $this->db->getSystemConfig(self::CF_HIERARCHICAL_PAGE);		// 階層化ページを使用するかどうか
			$sslUrl				= $this->db->getSystemConfig(self::CF_SSL_URL);			// SSLのURL
			$connectServerUrl	= $this->db->getSystemConfig(self::CF_CONNECT_SERVER_URL);// ポータル接続先URL
			$usePageCache 		= $this->db->getSystemConfig(self::CF_USE_PAGE_CACHE);			// 表示キャッシュ機能を使用するかどうか
			$canChangeTemplate	= $this->db->getSystemConfig(self::CF_USE_TEMPLATE_ID_IN_SESSION);// ユーザによるテンプレート変更を許可するかどうか
			$canDetailConfig	= $this->db->getSystemConfig(self::CF_PERMIT_DETAIL_CONFIG);
//			$regenerateSession	= $this->db->getSystemConfig(self::CF_REGENERATE_SESSION_ID);
//			$scriptCacheInBrowser = $this->db->getSystemConfig(self::CF_SCRIPT_CACHE_IN_BROWSER);
//			$mobileAutoRedirect	= $this->db->getSystemConfig(self::CF_MOBILE_AUTO_REDIRECT);
			$mobileAutoRedirect	= $this->gSystem->mobileAutoRedirect(true/*再取得*/);				// 携帯の自動遷移
			$smartphoneAutoRedirect	= $this->gSystem->smartphoneAutoRedirect(true/*再取得*/);		// スマートフォンの自動遷移
			$mobileUseSession = $this->db->getSystemConfig(self::CF_MOBILE_USE_SESSION);// 携帯でセッション管理するかどうか
			$workDir				= $this->db->getSystemConfig(self::CF_WORK_DIR);
			$sitePcInPublic = $this->gSystem->sitePcInPublic(true/*再取得*/);			// PC用サイトの公開状況
			$siteMobileInPublic = $this->gSystem->siteMobileInPublic(true/*再取得*/);	// 携帯用サイトの公開状況
			$siteSmartphoneInPublic = $this->gSystem->siteSmartphoneInPublic(true/*再取得*/);	// スマートフォン用サイトの公開状況
			$multiDomain		= $this->db->getSystemConfig(self::CF_MULTI_DOMAIN);// マルチドメイン運用
//			$isActiveSitePc			= $this->db->getSystemConfig(self::CF_USE_SITE_PC);	// PC用サイト使用
//			$isActiveSiteSmartphone	= $this->db->getSystemConfig(self::CF_USE_SITE_SMARTPHONE);	// スマートフォン用サイト使用
//			$isActiveSiteMobile		= $this->db->getSystemConfig(self::CF_USE_SITE_MOBILE);	// // 携帯用サイト使用
			$isActiveSitePc			= $this->isActiveAccessPoint(0/*PC*/);					// PC用サイト有効かどうか
			$isActiveSiteSmartphone	= $this->isActiveAccessPoint(2/*スマートフォン*/);		// スマートフォン用サイト有効かどうか
			$isActiveSiteMobile		= $this->isActiveAccessPoint(1/*スマートフォン*/);		// 携帯用サイト有効かどうか
			$siteSmartphoneUrl = $this->db->getSystemConfig(self::CF_SITE_SMARTPHONE_URL);		// スマートフォン用サイトURL
			$siteMobileUrl = $this->db->getSystemConfig(self::CF_SITE_MOBILE_URL);		// 携帯用サイトURL
			$configWindowOpenByTab = $this->db->getSystemConfig(self::CF_CONFIG_WINDOW_OPEN_TYPE);			// ウィジェット設定画面をタブで開くかどうか
			$multiLanguage = $this->gSystem->getSystemConfig(self::CF_MULTI_LANGUAGE);		// 多言語対応かどうか
			$this->systemTemplate		= $this->db->getSystemConfig(self::CF_SYSTEM_TEMPLATE);// システム画面用テンプレート
			$this->adminTheme = $this->db->getSystemConfig(self::CF_ADMIN_DEFAULT_THEME);		// 管理画面用jQueryUIテーマ
			$this->defaultTheme = $this->db->getSystemConfig(self::CF_DEFAULT_THEME);		// 一般画面用jQueryUIテーマ
			$this->jqueryVersion = $this->db->getSystemConfig(self::CF_JQUERY_VERSION);		// jQueryバージョン
			if (empty($this->jqueryVersion)) $this->jqueryVersion = self::DEFAULT_JQUERY_VERSION;
			$useJquery = $this->db->getSystemConfig(self::CF_USE_JQUERY);// 常にjQueryを使用するかどうか
			$this->wysiwygEditor = $this->db->getSystemConfig(self::CF_WYSIWYG_EDITOR);			// 管理画面用WYSIWYGエディター
		}
		// 言語選択メニューを作成
		$this->db->getLangs($this->gSystem->getSystemLanguages(), array($this, 'langLoop'));
		
		// システム画面用テンプレート作成
		$this->db->getAllTemplateList(0/*PC用*/, array($this, 'sysTemplateLoop'), false/*利用不可も表示*/);
		
		// jQueryUIテーマ選択メニュー作成
		$this->createAdminThemeMenu($this->gEnv->getSystemRootPath() . self::DEFAULT_THEME_DIR);
		$this->createDefaultThemeMenu($this->gEnv->getSystemRootPath() . self::DEFAULT_THEME_DIR);
		
		// jQueryバージョン選択メニュー作成
		$this->createJqueryVerMenu(ScriptLibInfo::getJQueryVersionInfo());
		
		// WYSIWYGエディター選択メニュー作成
		$this->createWysiwygMenu();
		
		// サイトURL
		$this->tmpl->addVar("_widget", "site_url", $this->gEnv->getRootUrl());
		$this->tmpl->addVar("show_site_pc_open", "pc_access_url", $this->gEnv->getDefaultUrl());
		$this->tmpl->addVar("show_site_mobile_open", "mobile_access_url", $this->gEnv->getDefaultMobileUrl());
		$this->tmpl->addVar("show_site_smartphone_open", "smartphone_access_url", $this->gEnv->getDefaultSmartphoneUrl());
		$this->tmpl->addVar("_widget", "admin_access_url", $this->gEnv->getDefaultAdminUrl());
		// 携帯画面エンコード
		$this->tmpl->addVar("show_site_mobile_open", "mobile_encode", $this->gEnv->getMobileEncoding());
		
		// サイト運用状況を設定
		//if ($this->db->getSystemConfig(self::CF_SITE_IN_PUBLIC)){		// 運用中のとき
		if ($this->gSystem->siteInPublic(true/*再取得*/)){		// 運用中のとき
			$this->tmpl->addVar("_widget", "site_open", '<b><font color="green">公開中</font></b>');
			$this->tmpl->addVar("_widget", "site_open_status", '0');
			$this->tmpl->addVar("_widget", "site_open_label", '公開停止');
		} else {
			$this->tmpl->addVar("_widget", "site_open", '非公開');
			$this->tmpl->addVar("_widget", "site_open_status", '1');
			$this->tmpl->addVar("_widget", "site_open_label", '公開開始');
		}
		$this->tmpl->addVar("_widget", "except_ip", $this->db->getSystemConfig(self::CF_SITE_ACCESS_EXCEPTION_IP));
		
		// ##### システム状態をチェック #####
		$systemMessage = '';
		// インストーラの存在
		$installFile = $this->gInstance->getFileManager()->getInstallerPath();
		if (file_exists($installFile)){
			$systemMessage .= 'インストーラファイルが存在しています。削除してください。ファイル=' . $installFile . '<br />';
		}
		if (!empty($systemMessage)){
			$this->tmpl->setAttribute('system_check', 'visibility', 'visible');
			$systemMessage = '<b><font color="red">' . $systemMessage . '</font></b>';
			$this->tmpl->addVar("system_check", "message", $systemMessage);
		}
		
		// 項目の表示制御
		$isActiveSite = $this->gSystem->getSiteActiveStatus(0);		// PC用サイト
		if ($isActiveSite){
			$this->tmpl->setAttribute('show_site_pc_open', 'visibility', 'visible');
		} else {
			$this->tmpl->setAttribute('show_site_pc_close', 'visibility', 'visible');
		}
		$isActiveSite = $this->gSystem->getSiteActiveStatus(1);		// 携帯用サイト
		if ($isActiveSite){
			$this->tmpl->setAttribute('show_site_mobile_open', 'visibility', 'visible');
		} else {
			$this->tmpl->setAttribute('show_site_mobile_close', 'visibility', 'visible');
		}
		$isActiveSite = $this->gSystem->getSiteActiveStatus(2);		// スマートフォン用サイト
		if ($isActiveSite){
			$this->tmpl->setAttribute('show_site_smartphone_open', 'visibility', 'visible');
		} else {
			$this->tmpl->setAttribute('show_site_smartphone_close', 'visibility', 'visible');
		}
		
		// 画面に書き戻す
		$checked = '';
		if ($sitePcInPublic) $checked = 'checked';
		$this->tmpl->addVar("show_site_pc_open", "site_pc_in_public", $checked);// PC用サイトの公開状況
		$checked = '';
		if ($siteMobileInPublic) $checked = 'checked';
		$this->tmpl->addVar("show_site_mobile_open", "site_mobile_in_public", $checked);// 携帯用サイトの公開状況
		$checked = '';
		if ($siteSmartphoneInPublic) $checked = 'checked';
		$this->tmpl->addVar("show_site_smartphone_open", "site_smartphone_in_public", $checked);// スマートフォン用サイトの公開状況
		$checked = '';
		if ($multiDomain) $checked = 'checked';
		$this->tmpl->addVar("_widget", "multi_domain", $checked);// マルチドメイン運用
		$checked = '';
		if ($isActiveSitePc) $checked = 'checked';
		$this->tmpl->addVar("_widget", "is_active_site_pc", $checked);// PC用サイト有効
		$checked = '';
		if ($isActiveSiteSmartphone) $checked = 'checked';
		$this->tmpl->addVar("_widget", "is_active_site_smartphone", $checked);// スマートフォン用サイト有効
		$checked = '';
		if ($isActiveSiteMobile) $checked = 'checked';
		$this->tmpl->addVar("_widget", "is_active_site_mobile", $checked);// 携帯用サイト有効
		$checked = '';
		if ($useSsl) $checked = 'checked';
		$this->tmpl->addVar("_widget", "use_ssl", $checked);// SSL機能を使用するかどうか
		$checked = '';
		if ($useSslAdmin) $checked = 'checked';
		$this->tmpl->addVar("_widget", "use_ssl_admin", $checked);// 管理画面にSSL機能を使用するかどうか
		$checked = '';
		if ($useHierPage) $checked = 'checked';
		$this->tmpl->addVar("_widget", "use_hier_page", $checked);// 階層化ページを使用するかどうか
		$checked = '';
		if ($usePageCache) $checked = 'checked';
		$this->tmpl->addVar("_widget", "use_page_cache", $checked);	// 表示キャッシュ機能を使用するかどうか
		$checked = '';
		if ($canChangeTemplate) $checked = 'checked';
		$this->tmpl->addVar("_widget", "can_change_template", $checked);	// ユーザによるテンプレート変更を許可するかどうか
		$checked = '';
		if ($multiLanguage) $checked = 'checked';
		$this->tmpl->addVar("_widget", "multi_language", $checked);	// 多言語対応かどうか
		if ($useJquery) $checked = 'checked';
		$this->tmpl->addVar("_widget", "use_jquery", $checked);		// 常にjQueryを使用するかどうか

		$this->tmpl->addVar("_widget", "root_url", $this->gEnv->getRootUrl());
		$this->tmpl->addVar("_widget", "ssl_url", $sslUrl);// SSLのURL
		$this->tmpl->addVar("_widget", "connect_server_url", $connectServerUrl);// ポータル接続先URL
		$this->tmpl->addVar("_widget", "site_smartphone_url", $siteSmartphoneUrl);		// スマートフォン用サイトURL
		$this->tmpl->addVar("_widget", "site_mobile_url", $siteMobileUrl);		// 携帯用サイトURL
			
		$checked = '';
		if ($canDetailConfig) $checked = 'checked';
		$this->tmpl->addVar("_widget", "can_detail_config", $checked);
		/*
		$checked = '';
		if ($regenerateSession) $checked = 'checked';
		$this->tmpl->addVar("_widget", "regenerate_session", $checked);
		
		$checked = '';
		if ($scriptCacheInBrowser) $checked = 'checked';
		$this->tmpl->addVar("_widget", "script_cache_in_browser", $checked);
*/
		$checked = '';
		if (!empty($mobileAutoRedirect)) $checked = 'checked';
		$this->tmpl->addVar("_widget", "mobile_auto_redirect", $checked);// 携帯の自動遷移
		$checked = '';
		if (!empty($smartphoneAutoRedirect)) $checked = 'checked';
		$this->tmpl->addVar("_widget", "smartphone_auto_redirect", $checked);// スマートフォンの自動遷移
		$checked = '';
		if (!empty($mobileUseSession)) $checked = 'checked';
		$this->tmpl->addVar("_widget", "mobile_use_session", $checked);
		$checked = '';
		if (!empty($configWindowOpenByTab)) $checked = 'checked'; 			// ウィジェット設定画面をタブで開くかどうか
		$this->tmpl->addVar("_widget", "config_window_open_by_tab", $checked);
		
		$this->tmpl->addVar("_widget", "upload_filesize_limit", $this->gSystem->getMaxFileSizeForUpload());
		$this->tmpl->addVar("_widget", "memory_limit", ini_get('memory_limit'));
		$this->tmpl->addVar("_widget", "post_max_size", ini_get('post_max_size'));
		$this->tmpl->addVar("_widget", "upload_max_filesize", ini_get('upload_max_filesize'));
		// ファイルのアップロード許可
		if (ini_get('file_uploads')){
			$data = '<b><font color="green">On</font></b>';
		} else {
			$data = '<b><font color="red">Off</font></b>';
		}
		$this->tmpl->addVar("_widget", "file_uploads", $data);
		
		// バージョン
		$this->tmpl->addVar("_widget", "distribution_name", $this->db->getSystemConfig(self::CF_DISTRIBUTION_NAME));		// ディストリビューション名
		$value = $this->db->getSystemConfig(self::CF_DISTRIBUTION_VERSION);
		if (empty($value)) $value = M3_SYSTEM_VERSION;
		$this->tmpl->addVar("_widget", "distribution_version", $value);		// ディストリビューションバージョン
		$this->tmpl->addVar("_widget", "magic3_version", M3_SYSTEM_VERSION);
		$this->tmpl->addVar("_widget", "php_version", phpversion());
		if ($this->db->getDbType() == M3_DB_TYPE_MYSQL){		// MySQLの場合
			$dbType = 'MySQL';
		} else if ($this->db->getDbType() == M3_DB_TYPE_PGSQL){// PostgreSQLの場合
			$dbType = 'PostgreSQL';
		} else {
			$dbType = 'DB未設定';
		}
		$this->tmpl->addVar("_widget", "db_type", $dbType);			// 使用しているDB種
		$this->tmpl->addVar("_widget", "db_version", $this->db->getDbVersion());
		$this->tmpl->addVar("_widget", "os_version", php_uname('s') . ' ' . php_uname('r') . ' ' . php_uname('m'));		// OSバージョン
		
		// DB接続
		$this->gConfig->getDbConnectDsnByList($dbType, $hostname, $dbname);
		$this->tmpl->addVar("_widget", "db_type", $dbType);			// DB種
		$this->tmpl->addVar("_widget", "db_host_name", $hostname);			// DBホスト名
		$this->tmpl->addVar("_widget", "db_name", $dbname);			// DB名
		$dbuser = $this->gConfig->getDbConnectUser();		// 接続ユーザ
		$this->tmpl->addVar("_widget", "db_user_name", $dbuser);			// 接続ユーザ名
				
		// mbstring
		if (extension_loaded('mbstring')){
			$data = '<b><font color="green">On</font></b>';
		} else {
			$data = '<b><font color="red">Off</font></b>';
		}
		$this->tmpl->addVar("_widget","current_mbstring", $data);
		// zlib
		if (extension_loaded('zlib')){
			$data = '<b><font color="green">On</font></b>';
		} else {
			$data = '<b><font color="red">Off</font></b>';
		}
		$this->tmpl->addVar("_widget","current_zlib", $data);
		// gd
		if (extension_loaded('gd')){
			$data = '<b><font color="green">On</font></b>';
		} else {
			$data = '<b><font color="red">Off</font></b>';
		}
		$this->tmpl->addVar("_widget","current_gd", $data);
		// dom
		if (extension_loaded('dom')){
			$data = '<b><font color="green">On</font></b>';
		} else {
			$data = '<b><font color="red">Off</font></b>';
		}
		$this->tmpl->addVar("_widget","current_dom", $data);
		// xml
		if (extension_loaded('xml')){
			$data = '<b><font color="green">On</font></b>';
		} else {
			$data = '<b><font color="red">Off</font></b>';
		}
		$this->tmpl->addVar("_widget","current_xml", $data);
		// gettext
		if (extension_loaded('gettext')){
			$data = '<b><font color="green">On</font></b>';
		} else {
			$data = '<b><font color="red">Off</font></b>';
		}
		$this->tmpl->addVar("_widget","current_gettext", $data);
		// curl
		if (extension_loaded('curl')){
			$data = '<b><font color="green">On</font></b>';
		} else {
			$data = '<b><font color="red">Off</font></b>';
		}
		$this->tmpl->addVar("_widget","current_curl", $data);
		
		// サーバ環境
		$hostname = exec('hostname');
		$this->tmpl->addVar("_widget", "host_name", $hostname);
		$dnsResolv = '解決できません';
		if ($hostname != 'localhost.localdomain'){
			$hosts = gethostbynamel($hostname);
			if ($hosts !== false){
				if (count($hosts) > 0) $dnsResolv = $hosts[0];
			}
		}
		$this->tmpl->addVar("_widget", "dns_resolv", $dnsResolv);
		$this->tmpl->addVar("_widget", "server_id", $this->db->getSystemConfig(self::CF_SERVER_ID));
		$this->tmpl->addVar("_widget", "install_dt", $this->db->getSystemConfig(self::CF_INSTALL_DT));		// インストール日時
		$this->tmpl->addVar("_widget", "work_dir", $workDir);		// 一時ディレクトリ
		if (is_writable($workDir)){
			if (checkWritableDir($workDir)){
				$data = '<b><font color="green">書き込み可能</font></b>';
			} else {
				$data = '<b><font color="red">Safe Modeにより書き込み不可</font></b>';
			}
		} else {
			$data = '<b><font color="red">書き込み不可</font></b>';
		}
		$this->tmpl->addVar("_widget","work_dir_access", $data);		// 一時ディレクトリの書き込み権限
		$this->tmpl->addVar("_widget", "system_db_version", $this->db->getSystemConfig(M3_TB_FIELD_DB_VERSION));		// Magic3システムDBバージョン
		$this->tmpl->addVar("_widget", "system_db_update_dt", $this->db->getSystemConfig(M3_TB_FIELD_DB_UPDATE_DT));	// Magic3システムDB更新日時

		// リソース格納ディレクトリパス
		$path = $this->gEnv->getResourcePath();
		$this->tmpl->addVar("_widget", "resource_dir", $path);
		if (is_writable($path)){
			if (checkWritableDir($path)){
				$data = '<b><font color="green">書き込み可能</font></b>';
			} else {
				$data = '<b><font color="red">Safe Modeにより書き込み不可</font></b>';
			}
		} else {
			$data = '<b><font color="red">書き込み不可</font></b>';
		}
		$this->tmpl->addVar("_widget", "resource_dir_access", $data);
/*		// 画像ディレクトリ
		$path = $this->gEnv->getResourcePath() . '/image';
		$this->tmpl->addVar("_widget", "resource_dir_image", $path);
		if (is_writable($path)){
			if (checkWritableDir($path)){
				$data = '<b><font color="green">書き込み可能</font></b>';
			} else {
				$data = '<b><font color="red">Safe Modeにより書き込み不可</font></b>';
			}
		} else {
			$data = '<b><font color="red">書き込み不可</font></b>';
		}
		$this->tmpl->addVar("_widget", "resource_dir_image_access", $data);
		// FLASHディレクトリ
		$path = $this->gEnv->getResourcePath() . '/flash';
		$this->tmpl->addVar("_widget", "resource_dir_flash", $path);
		if (is_writable($path)){
			if (checkWritableDir($path)){
				$data = '<b><font color="green">書き込み可能</font></b>';
			} else {
				$data = '<b><font color="red">Safe Modeにより書き込み不可</font></b>';
			}
		} else {
			$data = '<b><font color="red">書き込み不可</font></b>';
		}
		$this->tmpl->addVar("_widget", "resource_dir_flash_access", $data);
		// メディアディレクトリ
		$path = $this->gEnv->getResourcePath() . '/media';
		$this->tmpl->addVar("_widget", "resource_dir_media", $path);
		if (is_writable($path)){
			if (checkWritableDir($path)){
				$data = '<b><font color="green">書き込み可能</font></b>';
			} else {
				$data = '<b><font color="red">Safe Modeにより書き込み不可</font></b>';
			}
		} else {
			$data = '<b><font color="red">書き込み不可</font></b>';
		}
		$this->tmpl->addVar("_widget", "resource_dir_media_access", $data);
		// ファイルディレクトリ
		$path = $this->gEnv->getResourcePath() . '/file';
		$this->tmpl->addVar("_widget", "resource_dir_file", $path);
		if (is_writable($path)){
			if (checkWritableDir($path)){
				$data = '<b><font color="green">書き込み可能</font></b>';
			} else {
				$data = '<b><font color="red">Safe Modeにより書き込み不可</font></b>';
			}
		} else {
			$data = '<b><font color="red">書き込み不可</font></b>';
		}
		$this->tmpl->addVar("_widget", "resource_dir_file_access", $data);*/
		// テンプレートディレクトリ
		$path = $this->gEnv->getTemplatesPath();
		$this->tmpl->addVar("_widget", "templates_dir", $path);
		if (is_writable($path)){
			if (checkWritableDir($path)){
				$data = '<b><font color="green">書き込み可能</font></b>';
			} else {
				$data = '<b><font color="red">Safe Modeにより書き込み不可</font></b>';
			}
		} else {
			$data = '<b><font color="red">書き込み不可</font></b>';
		}
		$this->tmpl->addVar("_widget", "templates_dir_access", $data);
		// ウィジェットディレクトリ
		$path = $this->gEnv->getWidgetsPath();
		$this->tmpl->addVar("_widget", "widgets_dir", $path);
		if (is_writable($path)){
			if (checkWritableDir($path)){
				$data = '<b><font color="green">書き込み可能</font></b>';
			} else {
				$data = '<b><font color="red">Safe Modeにより書き込み不可</font></b>';
			}
		} else {
			$data = '<b><font color="red">書き込み不可</font></b>';
		}
		$this->tmpl->addVar("_widget", "widgets_dir_access", $data);
		
		// ディレクトリサイズ
		$size = convFromBytes(calcDirSize($this->gEnv->getResourcePath()));
		$this->tmpl->addVar("_widget", "resource_dir_size", $size);
		
		// phpinfo出力へのURL
		//$phpinfoUrl = '?task=phpinfo&menu=off';			// メニューは非表示にする
		$phpinfoUrl = $this->gEnv->getDefaultAdminUrl() . '?' . M3_REQUEST_PARAM_OPERATION_COMMAND . '=' . M3_REQUEST_CMD_SHOW_PHPINFO;			// phpinfo画面
		$this->tmpl->addVar("_widget", "phpinfo_url", $phpinfoUrl);
	}
	/**
	 * 取得した言語をテンプレートに設定する
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function langLoop($index, $fetchedRow, $param)
	{
		$selected = '';
		if ($fetchedRow['ln_id'] == $this->defaultLang){
			$selected = 'selected';
		}
		//if ($this->gEnv->getCurrentLanguage() == 'ja'){		// 日本語表示の場合
			$name = $this->convertToDispString($fetchedRow['ln_name']) . ' - ';
		//} else {
			$name .= $this->convertToDispString($fetchedRow['ln_name_en']);
		//}

		$row = array(
			'value'    => $this->convertToDispString($fetchedRow['ln_id']),			// 言語ID
			'name'     => $name,			// 言語名
			'selected' => $selected														// 選択中かどうか
		);
		$this->tmpl->addVars('lang_list', $row);
		$this->tmpl->parseTemplate('lang_list', 'a');
		return true;
	}
	/**
	 * jQueryUIテーマの選択メニューを作成
	 *
	 * @param string $dir		テーマのディレクトリ
	 * @return 					なし
	 */
	function createAdminThemeMenu($themeDir)
	{
		if (is_dir($themeDir)){
			$dir = dir($themeDir);
			while (($file = $dir->read()) !== false){
				$filePath = $themeDir . '/' . $file;
				// ディレクトリかどうかチェック
				if (strncmp($file, '.', 1) != 0 && $file != '..' && is_dir($filePath) &&
					strncmp($file, '_', 1) != 0){	// 「_」で始まる名前のディレクトリは読み込まない

					$selected = '';
					if ($file == $this->adminTheme) $selected = 'selected';
					
					$row = array(
						'value'    => $this->convertToDispString($file),			// テーマID
						'name'     => $this->convertToDispString($file),
						'selected' => $selected			// 選択中かどうか
					);
					$this->tmpl->addVars('admin_theme_list', $row);
					$this->tmpl->parseTemplate('admin_theme_list', 'a');
				}
			}
			$dir->close();
		}
	}
	/**
	 * jQueryUIテーマの選択メニューを作成
	 *
	 * @param string $dir		テーマのディレクトリ
	 * @return 					なし
	 */
	function createDefaultThemeMenu($themeDir)
	{
		$selected = '';
		if (empty($this->defaultTheme)) $selected = 'selected';
		
		$row = array(
			'value'    => '',			// テーマID
			'name'     => '-- 指定なし --',
			'selected' => $selected			// 選択中かどうか
		);
		$this->tmpl->addVars('theme_list', $row);
		$this->tmpl->parseTemplate('theme_list', 'a');
					
		if (is_dir($themeDir)){
			$dir = dir($themeDir);
			while (($file = $dir->read()) !== false){
				$filePath = $themeDir . '/' . $file;
				// ディレクトリかどうかチェック
				if (strncmp($file, '.', 1) != 0 && $file != '..' && is_dir($filePath) &&
					strncmp($file, '_', 1) != 0){	// 「_」で始まる名前のディレクトリは読み込まない

					$selected = '';
					if ($file == $this->defaultTheme) $selected = 'selected';
					
					$row = array(
						'value'    => $this->convertToDispString($file),			// テーマID
						'name'     => $this->convertToDispString($file),
						'selected' => $selected			// 選択中かどうか
					);
					$this->tmpl->addVars('theme_list', $row);
					$this->tmpl->parseTemplate('theme_list', 'a');
				}
			}
			$dir->close();
		}
	}
	/**
	 * システム画面用テンプレート一覧を作成
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function sysTemplateLoop($index, $fetchedRow, $param)
	{
		$selected = '';
		if ($fetchedRow['tm_id'] == $this->systemTemplate){
			$selected = 'selected';
		}
		$name = $fetchedRow['tm_name'];
		if ($fetchedRow['tm_id'] == self::DEFAULT_SYSTEM_TEMPLATE_ID) $name = '[デフォルト]';
		
		$row = array(
			'value'    => $this->convertToDispString($fetchedRow['tm_id']),			// テンプレートID
			'name'     => $this->convertToDispString($name),			// テンプレート名名
			'selected' => $selected														// 選択中かどうか
		);
		$this->tmpl->addVars('systemplate_list', $row);
		$this->tmpl->parseTemplate('systemplate_list', 'a');
		return true;
	}
	/**
	 * jQueryのバージョン選択メニューを作成
	 *
	 * @param array		$versionInfo	jQueryバージョン情報
	 * @return 							なし
	 */
	function createJqueryVerMenu($versionInfo)
	{
		foreach ($versionInfo as $key => $value){
			$value = $key;
			$name = $key;
			
			$selected = '';
			if ($value == $this->jqueryVersion) $selected = 'selected';
			
			$row = array(
				'value'    => $this->convertToDispString($value),
				'name'     => $this->convertToDispString($name),
				'selected' => $selected			// 選択中かどうか
			);
			$this->tmpl->addVars('jquery_version_list', $row);
			$this->tmpl->parseTemplate('jquery_version_list', 'a');
		}
	}
	/**
	 * WYSIWYGエディター選択メニュー作成
	 *
	 * @return 							なし
	 */
	function createWysiwygMenu()
	{
		foreach ($this->wysiwygMenuData as $key => $value){
			$name = $value;
			
			$selected = '';
			if ($key == $this->wysiwygEditor) $selected = 'selected';
			
			$row = array(
				'value'    => $this->convertToDispString($key),
				'name'     => $this->convertToDispString($name),
				'selected' => $selected			// 選択中かどうか
			);
			$this->tmpl->addVars('wysiwyg_editor_list', $row);
			$this->tmpl->parseTemplate('wysiwyg_editor_list', 'a');
		}
	}
	/**
	 * アクセスポイントが有効かどうか
	 *
	 * @param int   $deviceType デバイスタイプ(0=PC,1=携帯,2=スマートフォン)
	 * @return bool 			true=有効、false=無効
	 */
	function isActiveAccessPoint($deviceType)
	{
		// ページID作成
		switch ($deviceType){
			case 0:		// PC
				$pageId = 'index';
				break;
			case 1:		// 携帯
				$pageId = M3_DIR_NAME_MOBILE . '_index';
				break;
			case 2:		// スマートフォン
				$pageId = M3_DIR_NAME_SMARTPHONE . '_index';
				break;
		}
		
		$isActive = false;
		$ret = $this->db->getPageIdRecord(0/*アクセスポイント*/, $pageId, $row);
		if ($ret){
			$isActive = $row['pg_active'];
		}
		return $isActive;
	}
	/**
	 * アクセスポイントが有効状態を更新
	 *
	 * @param int   $deviceType デバイスタイプ(0=PC,1=携帯,2=スマートフォン)
	 * @param bool  $status		有効状態
	 * @return bool 			true=成功、false=失敗
	 */
	function updateActiveAccessPoint($deviceType, $status)
	{
		// ページID作成
		switch ($deviceType){
			case 0:		// PC
				$pageId = 'index';
				break;
			case 1:		// 携帯
				$pageId = M3_DIR_NAME_MOBILE . '_index';
				break;
			case 2:		// スマートフォン
				$pageId = M3_DIR_NAME_SMARTPHONE . '_index';
				break;
		}
		
		$ret = $this->db->getPageIdRecord(0/*アクセスポイント*/, $pageId, $row);
		if ($ret){
			$ret = $this->db->updatePageId(0/*アクセスポイント*/, $pageId, $row['pg_name'], $row['pg_description'], $row['pg_priority'], $status);
		}
		return $ret;
	}
}
?>
