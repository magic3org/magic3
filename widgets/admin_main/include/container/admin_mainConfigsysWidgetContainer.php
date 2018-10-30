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
 * @copyright  Copyright 2006-2018 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/admin_mainConfigsystemBaseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/admin_mainDb.php');
require_once(M3_SYSTEM_INCLUDE_PATH . '/common/scriptLibInfo.php');

class admin_mainConfigsysWidgetContainer extends admin_mainConfigsystemBaseWidgetContainer
{
	private $db;	// DB接続オブジェクト
	private $systemTemplate;// システム画面用テンプレート
	private $jqueryVersion;			// jQueryバージョン
	const DEFAULT_SYSTEM_TEMPLATE_ID = '_system';				// デフォルトのシステム画面用テンプレート
	const DEFAULT_JQUERY_VERSION = '1.9';				// デフォルトのjQueryバージョン
	
	// DB定義値
	const CF_USE_SSL = 'use_ssl';		// SSL機能を使用するかどうか
	const CF_USE_SSL_ADMIN = 'use_ssl_admin';		// 管理画面にSSL機能を使用するかどうか
	const CF_SITE_IN_PUBLIC = 'site_in_public';			// サイト公開状況
	const CF_SITE_PC_IN_PUBLIC = 'site_pc_in_public';				// PC用サイトの公開状況
	const CF_SITE_SMARTPHONE_IN_PUBLIC = 'site_smartphone_in_public';		// スマートフォン用サイトの公開状況
	const CF_SMARTPHONE_AUTO_REDIRECT = 'smartphone_auto_redirect';			// スマートフォンの自動遷移
	const CF_SITE_SMARTPHONE_URL = 'site_smartphone_url';		// スマートフォン用サイトURL
	const CF_SITE_OPERATION_MODE = 'site_operation_mode';			// サイト運用モード
	const CF_ACCESS_IN_INTRANET = 'access_in_intranet';		// イントラネット運用
	const CF_MULTI_DOMAIN = 'multi_domain';		// マルチドメイン運用
	const CF_USE_LANDING_PAGE = 'use_landing_page';		// ランディングページ機能を使用するかどうか
	const CF_SITE_ACCESS_EXCEPTION_IP = 'site_access_exception_ip';		// アクセス制御、例外とするIP
	const CF_USE_PAGE_CACHE = 'use_page_cache';		// 画面キャッシュ機能を使用するかどうか
	const CF_SSL_URL = 'ssl_root_url';				// SSL用のルートURL
	const CF_CONNECT_SERVER_URL = 'default_connect_server_url';			// ポータル接続先URL
	const CF_SYSTEM_TEMPLATE = 'msg_template';			// メッセージ用テンプレート取得キー
	const CF_HIERARCHICAL_PAGE = 'hierarchical_page';		// 階層化ページを使用するかどうか
	const CF_MULTI_LANGUAGE = 'multi_language';			// 多言語対応
	const CF_JQUERY_VERSION = 'jquery_version';			// jQueryバージョン
	const CF_EXTERNAL_JQUERY = 'external_jquery';			// システム外部のjQueryを使用するかどうか
	const CF_MULTI_DEVICE_ADMIN = 'multi_device_admin';			// マルチデバイス最適化管理画面
	const CF_PERMIT_DETAIL_CONFIG	= 'permit_detail_config';				// 詳細設定が可能かどうか
	const CF_DEFAULT_LANG		= 'default_lang';					// デフォルト言語
	const CF_WORK_DIR = 'work_dir';			// 作業ディレクトリ
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DB接続オブジェクト作成
		$this->db = new admin_mainDb();
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
	 * ヘルプデータを設定
	 *
	 * ヘルプの設定を行う場合はヘルプIDを返す。
	 * ヘルプデータの読み込むディレクトリは「自ウィジェットディレクトリ/include/help」に固定。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。そのまま_assign()に渡る
	 * @return string 						ヘルプID。ヘルプデータはファイル名「help_[ヘルプID].php」で作成。ヘルプを使用しない場合は空文字列「''」を返す。
	 */
	function _setHelp($request, &$param)
	{	
		return 'configsys';
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
		$usePageCache = ($request->trimValueOf('item_use_page_cache') == 'on') ? 1 : 0;		// 表示キャッシュ機能を使用するかどうか
		$canDetailConfig = ($request->trimValueOf('item_can_detail_config') == 'on') ? 1 : 0;		// 詳細システム設定が可能かどうか
		$multiDeviceAdmin = ($request->trimValueOf('item_multi_device_admin') == 'on') ? 1 : 0;// マルチデバイス最適化管理画面
		$smartphoneAutoRedirect = ($request->trimValueOf('item_smartphone_auto_redirect') == 'on') ? 1 : 0;		// スマートフォンの自動遷移
		$sitePcInPublic = ($request->trimValueOf('item_site_pc_in_public') == 'on') ? 1 : 0;			// PC用サイトの公開状況
		$siteSmartphoneInPublic = ($request->trimValueOf('item_site_smartphone_in_public') == 'on') ? 1 : 0;	// スマートフォン用サイトの公開状況
		$accessInIntranet	= $request->trimCheckedValueOf('item_access_in_intranet');		// イントラネット運用
		$multiDomain = ($request->trimValueOf('item_multi_domain') == 'on') ? 1 : 0;// マルチドメイン運用
		$useLandingPage		= $request->trimCheckedValueOf('item_use_landing_page');	// ランディングページ機能を使用するかどうか
		$isActiveSitePc = ($request->trimValueOf('item_is_active_site_pc') == 'on') ? 1 : 0;	// PC用サイト有効
		$isActiveSiteSmartphone = ($request->trimValueOf('item_is_active_site_smartphone') == 'on') ? 1 : 0;	// スマートフォン用サイト有効
		$multiLanguage = ($request->trimValueOf('item_multi_language') == 'on') ? 1 : 0;			// 多言語対応
		$lang = $request->trimValueOf('item_lang');
		$workDir = $request->trimValueOf('item_work_dir');
		$this->systemTemplate = $request->trimValueOf('item_systemplate');	// システム画面用テンプレート
		$this->jqueryVersion = $request->trimValueOf('item_jquery_version');		// jQueryバージョン
		$externalJquery = $request->trimCheckedValueOf('item_external_jquery');		// システム外部のjQueryを使用するかどうか
		$uploadImageAutoresize = $request->trimCheckedValueOf('item_upload_image_autoresize');		// アップロード画像の自動リサイズを行うかどうか
		$uploadImageAutoresizeMaxWidth = $request->trimValueOf('item_upload_image_autoresize_max_width');		// アップロード画像の自動リサイズ、画像最大幅
		$uploadImageAutoresizeMaxHeight = $request->trimValueOf('item_upload_image_autoresize_max_height');		// アップロード画像の自動リサイズ、画像最大高さ
		$isHier = $request->trimValueOf('menu_type');
		
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
			if (!$isErr){
				if (!$this->db->updateSystemConfig(self::CF_PERMIT_DETAIL_CONFIG, $canDetailConfig)) $isErr = true;
			}
			if (!$isErr){
				if (!$this->db->updateSystemConfig(self::CF_MULTI_DEVICE_ADMIN, $multiDeviceAdmin)) $isErr = true;// マルチデバイス最適化管理画面
			}
			if (!$isErr){
				if (!$this->db->updateSystemConfig(self::CF_SMARTPHONE_AUTO_REDIRECT, $smartphoneAutoRedirect)) $isErr = true;
			}
			if (!$isErr){
				if (!$this->db->updateSystemConfig(self::CF_SITE_PC_IN_PUBLIC, $sitePcInPublic)) $isErr = true;
			}
			if (!$isErr){
				if (!$this->db->updateSystemConfig(self::CF_SITE_SMARTPHONE_IN_PUBLIC, $siteSmartphoneInPublic)) $isErr = true;
			}
			if (!$isErr){
				if (!$this->db->updateSystemConfig(self::CF_ACCESS_IN_INTRANET, $accessInIntranet)) $isErr = true;// イントラネット運用
			}
			if (!$isErr){
				if (!$this->db->updateSystemConfig(self::CF_MULTI_DOMAIN, $multiDomain)) $isErr = true;// マルチドメイン運用
			}
			if (!$isErr){
				if (!$this->db->updateSystemConfig(self::CF_USE_LANDING_PAGE, $useLandingPage)) $isErr = true;// ランディングページ機能を使用するかどうか
			}
			if (!$isErr){
				if (!$this->updateActiveAccessPoint(0/*PC*/, $isActiveSitePc)) $isErr = true;// PC用サイト有効
			}
			if (!$isErr){
				if (!$this->updateActiveAccessPoint(2/*スマートフォン*/, $isActiveSiteSmartphone)) $isErr = true;// スマートフォン用サイト有効
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
				$workDir = rtrim($workDir, '/');// 最後の「/」を除く
				if (!$this->db->updateSystemConfig(self::CF_WORK_DIR, $workDir)) $isErr = true;
			}
			if (!$isErr){
				if (!$this->db->updateSystemConfig(self::CF_SYSTEM_TEMPLATE, $this->systemTemplate)) $isErr = true;// システム画面用テンプレート
			}
			if (!$isErr){
				if (!$this->db->updateSystemConfig(self::CF_JQUERY_VERSION, $this->jqueryVersion)) $isErr = true;// jQueryバージョン
			}
			if (!$isErr){
				if (!$this->db->updateSystemConfig(self::CF_EXTERNAL_JQUERY, $externalJquery)) $isErr = true;// システム外部のjQueryを使用するかどうか
			}
			if (!$isErr){
				if (!$this->db->updateSystemConfig(SystemManager::CF_UPLOAD_IMAGE_AUTORESIZE, $uploadImageAutoresize)) $isErr = true;		// アップロード画像の自動リサイズを行うかどうか
			}
			if (!$isErr){
				if (!$this->db->updateSystemConfig(SystemManager::CF_UPLOAD_IMAGE_AUTORESIZE_MAX_WIDTH, $uploadImageAutoresizeMaxWidth)) $isErr = true;		// アップロード画像の自動リサイズ、画像最大幅
			}
			if (!$isErr){
				if (!$this->db->updateSystemConfig(SystemManager::CF_UPLOAD_IMAGE_AUTORESIZE_MAX_HEIGHT, $uploadImageAutoresizeMaxHeight)) $isErr = true;		// アップロード画像の自動リサイズ、画像最大高さ
			}
			if (!$isErr){
				if (!$this->gSystem->changeSiteMenuHier($isHier)) $isErr = true;// メニュー管理画面を変更
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
			$canDetailConfig	= $this->db->getSystemConfig(self::CF_PERMIT_DETAIL_CONFIG);
			$multiDeviceAdmin	= $this->db->getSystemConfig(self::CF_MULTI_DEVICE_ADMIN);		// マルチデバイス最適化管理画面
			$smartphoneAutoRedirect	= $this->gSystem->smartphoneAutoRedirect(true/*再取得*/);		// スマートフォンの自動遷移
			$workDir = $this->db->getSystemConfig(self::CF_WORK_DIR);
			$sitePcInPublic = $this->gSystem->sitePcInPublic(true/*再取得*/);			// PC用サイトの公開状況
			$siteSmartphoneInPublic = $this->gSystem->siteSmartphoneInPublic(true/*再取得*/);	// スマートフォン用サイトの公開状況
			$accessInIntranet	= $this->db->getSystemConfig(self::CF_ACCESS_IN_INTRANET);		// イントラネット運用
			$multiDomain		= $this->db->getSystemConfig(self::CF_MULTI_DOMAIN);			// マルチドメイン運用
			$useLandingPage		= $this->db->getSystemConfig(self::CF_USE_LANDING_PAGE);// ランディングページ機能を使用するかどうか
			$isActiveSitePc			= $this->isActiveAccessPoint(0/*PC*/);					// PC用サイト有効かどうか
			$isActiveSiteSmartphone	= $this->isActiveAccessPoint(2/*スマートフォン*/);		// スマートフォン用サイト有効かどうか
			$siteSmartphoneUrl = $this->db->getSystemConfig(self::CF_SITE_SMARTPHONE_URL);		// スマートフォン用サイトURL
			$multiLanguage = $this->gSystem->getSystemConfig(self::CF_MULTI_LANGUAGE);		// 多言語対応かどうか
			$this->systemTemplate		= $this->db->getSystemConfig(self::CF_SYSTEM_TEMPLATE);// システム画面用テンプレート
			$this->jqueryVersion = $this->db->getSystemConfig(self::CF_JQUERY_VERSION);		// jQueryバージョン
			if (empty($this->jqueryVersion)) $this->jqueryVersion = self::DEFAULT_JQUERY_VERSION;
			$externalJquery = $this->db->getSystemConfig(self::CF_EXTERNAL_JQUERY);// システム外部のjQueryを使用するかどうか
			$uploadImageAutoresize = $this->db->getSystemConfig(SystemManager::CF_UPLOAD_IMAGE_AUTORESIZE);		// アップロード画像の自動リサイズを行うかどうか
			$uploadImageAutoresizeMaxWidth = $this->db->getSystemConfig(SystemManager::CF_UPLOAD_IMAGE_AUTORESIZE_MAX_WIDTH);	// アップロード画像の自動リサイズ、画像最大幅
			$uploadImageAutoresizeMaxHeight = $this->db->getSystemConfig(SystemManager::CF_UPLOAD_IMAGE_AUTORESIZE_MAX_HEIGHT);	// アップロード画像の自動リサイズ、画像最大高さ
			$isHier = $this->gSystem->isSiteMenuHier();		// メニューを階層化するかどうかを取得
			
			// ### サイト定義ファイル(siteDef.php)にオプション定義を追加 ###
			$adminUrl = $this->gEnv->getAdminUrl();			// 管理機能URL
			$params = array();
			$params['M3_SYSTEM_ADMIN_URL'] = $adminUrl;
			$ret = $this->gConfig->updateOptionParam($params, $msg);
			if (!$ret) var_dump($msg);
			
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
		} else if ($act == 'site_operation_mode_on'){			// サイト運用モード変更
			// システム制御マネージャーの値を更新
			$this->gSystem->updateSystemConfig(self::CF_SITE_OPERATION_MODE, 1);
		} else if ($act == 'site_operation_mode_off'){			// サイト運用モード変更
			// システム制御マネージャーの値を更新
			$this->gSystem->updateSystemConfig(self::CF_SITE_OPERATION_MODE, 0);
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
			$canDetailConfig	= $this->db->getSystemConfig(self::CF_PERMIT_DETAIL_CONFIG);
			$multiDeviceAdmin	= $this->db->getSystemConfig(self::CF_MULTI_DEVICE_ADMIN);		// マルチデバイス最適化管理画面
			$smartphoneAutoRedirect	= $this->gSystem->smartphoneAutoRedirect(true/*再取得*/);		// スマートフォンの自動遷移
			$workDir				= $this->db->getSystemConfig(self::CF_WORK_DIR);
			$sitePcInPublic = $this->gSystem->sitePcInPublic(true/*再取得*/);			// PC用サイトの公開状況
			$siteSmartphoneInPublic = $this->gSystem->siteSmartphoneInPublic(true/*再取得*/);	// スマートフォン用サイトの公開状況
			$accessInIntranet	= $this->db->getSystemConfig(self::CF_ACCESS_IN_INTRANET);		// イントラネット運用
			$multiDomain		= $this->db->getSystemConfig(self::CF_MULTI_DOMAIN);// マルチドメイン運用
			$useLandingPage		= $this->db->getSystemConfig(self::CF_USE_LANDING_PAGE);// ランディングページ機能を使用するかどうか
			$isActiveSitePc			= $this->isActiveAccessPoint(0/*PC*/);					// PC用サイト有効かどうか
			$isActiveSiteSmartphone	= $this->isActiveAccessPoint(2/*スマートフォン*/);		// スマートフォン用サイト有効かどうか
			$siteSmartphoneUrl = $this->db->getSystemConfig(self::CF_SITE_SMARTPHONE_URL);		// スマートフォン用サイトURL
			$multiLanguage = $this->gSystem->getSystemConfig(self::CF_MULTI_LANGUAGE);		// 多言語対応かどうか
			$this->systemTemplate		= $this->db->getSystemConfig(self::CF_SYSTEM_TEMPLATE);// システム画面用テンプレート
			$this->jqueryVersion = $this->db->getSystemConfig(self::CF_JQUERY_VERSION);		// jQueryバージョン
			if (empty($this->jqueryVersion)) $this->jqueryVersion = self::DEFAULT_JQUERY_VERSION;
			$externalJquery = $this->db->getSystemConfig(self::CF_EXTERNAL_JQUERY);// システム外部のjQueryを使用するかどうか
			$uploadImageAutoresize = $this->db->getSystemConfig(SystemManager::CF_UPLOAD_IMAGE_AUTORESIZE);		// アップロード画像の自動リサイズを行うかどうか
			$uploadImageAutoresizeMaxWidth = $this->db->getSystemConfig(SystemManager::CF_UPLOAD_IMAGE_AUTORESIZE_MAX_WIDTH);	// アップロード画像の自動リサイズ、画像最大幅
			$uploadImageAutoresizeMaxHeight = $this->db->getSystemConfig(SystemManager::CF_UPLOAD_IMAGE_AUTORESIZE_MAX_HEIGHT);	// アップロード画像の自動リサイズ、画像最大高さ
			$isHier = $this->gSystem->isSiteMenuHier();		// メニューを階層化するかどうかを取得
		}
		// 言語選択メニューを作成
		$this->db->getLangs($this->gSystem->getSystemLanguages(), array($this, 'langLoop'));
		
		// システム画面用テンプレート作成
		$this->db->getAllTemplateList(0/*PC用*/, array($this, 'sysTemplateLoop'), false/*利用不可も表示*/);
		
		// jQueryバージョン選択メニュー作成
		$this->createJqueryVerMenu(ScriptLibInfo::getJQueryVersionInfo());
		
		// サイトURL
		$this->tmpl->addVar("_widget", "site_url", $this->gEnv->getRootUrl());
		$this->tmpl->addVar("show_site_pc_open", "pc_access_url", $this->gEnv->getDefaultUrl());
		$this->tmpl->addVar("show_site_smartphone_open", "smartphone_access_url", $this->gEnv->getDefaultSmartphoneUrl());
		$this->tmpl->addVar("_widget", "admin_access_url", $this->gEnv->getDefaultAdminUrl());
		
		// サイト運用状況を設定
		$checked = '';
		if ($this->gSystem->siteInPublic(true/*再取得*/)) $checked = 'checked';		// 運用中のとき
		$this->tmpl->addVar("_widget", "site_status_checked", $checked);
		$this->tmpl->addVar("_widget", "except_ip", $this->db->getSystemConfig(self::CF_SITE_ACCESS_EXCEPTION_IP));
		$checked = '';
		if ($this->db->getSystemConfig(self::CF_SITE_OPERATION_MODE) == '1') $checked = 'checked';		// サイト運用モードのとき
		$this->tmpl->addVar("_widget", "site_operation_mode_checked", $checked);
		
		// 項目の表示制御
		$isActiveSite = $this->gSystem->getSiteActiveStatus(0);		// PC用サイト
		if ($isActiveSite){
			$this->tmpl->setAttribute('show_site_pc_open', 'visibility', 'visible');
		} else {
			$this->tmpl->setAttribute('show_site_pc_close', 'visibility', 'visible');
		}
		$isActiveSite = $this->gSystem->getSiteActiveStatus(2);		// スマートフォン用サイト
		if ($isActiveSite){
			$this->tmpl->setAttribute('show_site_smartphone_open', 'visibility', 'visible');
		} else {
			$this->tmpl->setAttribute('show_site_smartphone_close', 'visibility', 'visible');
		}
		
		// SSL証明書期限を取得
		if (!empty($sslUrl)){
			$expireDt = $this->_getSslExpireDt($sslUrl, $sslDomain);
			if (empty($expireDt)){
				$expireDtTag = '未取得';
			} else {
				if (time() <= $expireDt){
					$expireDt = date("Y/m/d H:i:s", $expireDt);
					$expireDtTag = '<span class="available">' . $this->convertToDispDateTime($expireDt) . '</span>';
				} else {
					$expireDt = date("Y/m/d H:i:s", $expireDt);
					$expireDtTag = '<span class="stopped">' . $this->convertToDispDateTime($expireDt) . '</span>';
				}
			}
		}
		
		// 画面に書き戻す
		$checked = '';
		if ($sitePcInPublic) $checked = 'checked';
		$this->tmpl->addVar("show_site_pc_open", "site_pc_in_public", $checked);// PC用サイトの公開状況
		$checked = '';
		if ($siteSmartphoneInPublic) $checked = 'checked';
		$this->tmpl->addVar("show_site_smartphone_open", "site_smartphone_in_public", $checked);// スマートフォン用サイトの公開状況
		$this->tmpl->addVar("_widget", "access_in_intranet", $this->convertToCheckedString($accessInIntranet));// イントラネット運用
		$checked = '';
		if ($multiDomain) $checked = 'checked';
		$this->tmpl->addVar("_widget", "multi_domain", $checked);// マルチドメイン運用
		$this->tmpl->addVar("_widget", "use_landing_page_checked", $this->convertToCheckedString($useLandingPage));// ランディングページ機能を使用するかどうか
		$checked = '';
		if ($isActiveSitePc) $checked = 'checked';
		$this->tmpl->addVar("_widget", "is_active_site_pc", $checked);// PC用サイト有効
		$checked = '';
		if ($isActiveSiteSmartphone) $checked = 'checked';
		$this->tmpl->addVar("_widget", "is_active_site_smartphone", $checked);// スマートフォン用サイト有効
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
		if ($multiLanguage) $checked = 'checked';
		$this->tmpl->addVar("_widget", "multi_language", $checked);	// 多言語対応かどうか
		$this->tmpl->addVar("_widget", "external_jquery", $this->convertToCheckedString($externalJquery));// システム外部のjQueryを使用するかどうか
		if ($isHier){		// 階層化メニューのとき
			$this->tmpl->addVar("_widget", "menu_type_tree", 'checked');		// 多階層メニュー
		} else {
			$this->tmpl->addVar("_widget", "menu_type_single", 'checked');		// 単階層メニュー
		}
		
		// URL
		$this->tmpl->addVar("_widget", "root_url", $this->gEnv->getRootUrl());
		$this->tmpl->addVar("_widget", "connect_server_url", $connectServerUrl);// ポータル接続先URL
		$this->tmpl->addVar("_widget", "site_smartphone_url", $siteSmartphoneUrl);		// スマートフォン用サイトURL
		
		// 共有SSL用のURL
		if (!empty($sslUrl)){
			$this->tmpl->setAttribute('show_ssl_url', 'visibility', 'hidden');
			$this->tmpl->setAttribute('show_ssl_url_expiredt', 'visibility', 'visible');		// SSLの期限を表示
			
			$this->tmpl->addVar("show_ssl_url_expiredt", "ssl_url", $sslUrl);// SSLのURL
			$this->tmpl->addVar('show_ssl_url_expiredt', 'ssl_expire_dt',	$expireDtTag);		// SSL証明書期限
		}
				
		$checked = '';
		if ($canDetailConfig) $checked = 'checked';
		$this->tmpl->addVar("_widget", "can_detail_config", $checked);
		$this->tmpl->addVar("_widget", "multi_device_admin", $this->convertToCheckedString($multiDeviceAdmin));// マルチデバイス最適化管理画面
		$checked = '';
		if (!empty($smartphoneAutoRedirect)) $checked = 'checked';
		$this->tmpl->addVar("_widget", "smartphone_auto_redirect", $checked);// スマートフォンの自動遷移

		$this->tmpl->addVar("_widget", "upload_image_autoresize", $this->convertToCheckedString($uploadImageAutoresize));			// アップロード画像の自動リサイズを行うかどうか
		$this->tmpl->addVar("_widget", "upload_image_autoresize_max_width", $uploadImageAutoresizeMaxWidth);		// アップロード画像の自動リサイズ、画像最大幅
		$this->tmpl->addVar("_widget", "upload_image_autoresize_max_height", $uploadImageAutoresizeMaxHeight);		// アップロード画像の自動リサイズ、画像最大高さ
			
		// 一時ディレクトリ
		$this->tmpl->addVar("_widget", "work_dir", $workDir);
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
		
		// 非公開リソースディレクトリ
		$path = $this->gEnv->getPrivateResourcePath();
		$this->tmpl->addVar("_widget", "private_resource_dir", $path);
		if (is_writable($path)){
			if (checkWritableDir($path)){
				$data = '<b><font color="green">書き込み可能</font></b>';
			} else {
				$data = '<b><font color="red">Safe Modeにより書き込み不可</font></b>';
			}
		} else {
			$data = '<b><font color="red">書き込み不可</font></b>';
		}
		$this->tmpl->addVar("_widget", "private_resource_dir_access", $data);
		
		// ディレクトリサイズ
		$size = convFromBytes(calcDirSize($this->gEnv->getResourcePath()));
		$this->tmpl->addVar("_widget", "resource_dir_size", $size);
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
	 * システム画面用テンプレート一覧を作成
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function sysTemplateLoop($index, $fetchedRow, $param)
	{
		$templateId = $fetchedRow['tm_id'];
		

		$name = $fetchedRow['tm_name'];
		if ($templateId == self::DEFAULT_SYSTEM_TEMPLATE_ID){
			$name = '[デフォルト]';
		} else if (strStartsWith($templateId, '_')){			// 「_」で始まるテンプレートは表示しない
			return true;
		}
		
		// 選択状態
		$selected = '';
		if ($templateId == $this->systemTemplate) $selected = 'selected';
		
		$row = array(
			'value'    => $this->convertToDispString($templateId),			// テンプレートID
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
	 * アクセスポイントの有効状態を更新
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
			$ret = $this->db->updatePageId(0/*アクセスポイント*/, $pageId, $row['pg_name'], $row['pg_description'], $row['pg_priority'], $status, $row['pg_visible']);
		}
		return $ret;
	}
	/**
	 * SSLの期限を取得
	 *
	 * @param string $url		SSL証明書を取得するURL
	 * @param string $sslDomain	SSLの対象となるドメイン名
	 * @return int				UNIXタイムスタンプ。取得できない場合は0。
	 */
	function _getSslExpireDt($url, &$sslDomain)
	{
		$arr = parse_url($url);
		if ($arr == false)  return '';
		
		$hostname = $arr['host'];

		$stream_context = stream_context_create(array(
			'ssl' => array('capture_peer_cert' => true)
		));
		$fp = @stream_socket_client(
			'ssl://' . $hostname . ':443',
			$errno,
			$errstr,
			30,
			STREAM_CLIENT_CONNECT,
			$stream_context
		);
		if (!$fp) return 0;		// 取得不可の場合は終了
		
		$cont = stream_context_get_params($fp);
		$parsed = openssl_x509_parse($cont['options']['ssl']['peer_certificate']);
//		$expireDt = date("Y/m/d H:i:s", $parsed['validTo_time_t']);
		$expireDt = $parsed['validTo_time_t'];
		$sslDomain = $parsed['subject']['CN'];		// ドメイン名
		
		// ファイルポインタ閉じる
		fclose($fp);
		
		// ドメイン名のチェック
		if (strStartsWith($sslDomain, '*.')){		// ワイルドカードSSLの場合
			if (!strEndsWith($hostname, substr($sslDomain, 1))) $expireDt = 0;
		} else {
			if (!strEndsWith($hostname, '.' . $sslDomain)) $expireDt = 0;
		}
		return $expireDt;
	}
}
?>
