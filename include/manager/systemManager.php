<?php
/**
 * システム制御マネージャー
 *
 *  主にシステムの動作に関わる制御の仲介を行う
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2023 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once(M3_SYSTEM_INCLUDE_PATH . '/common/core.php');		// Magic3コアクラス

class SystemManager extends _Core
{
	private $_systemConfigArray = array();			// システム定義値
	private $_siteDefArray = array();			// サイト定義値
	private $defaultAdminTemplateId;				// 管理画面用テンプレートID
	private $defaultTemplateId;						// PC用テンプレートID
	private $defaultSubTemplateId;						// PC用サブテンプレートID
	private $defaultSmartphoneTemplateId;				// スマートフォン用テンプレートID
	private $adminDefaultTheme;			// 管理画面用jQueryUIテーマ
	private $permitInitSystem;						// システム初期化可能かどうか
	private $siteInPublic;				// Webサイトの公開状況
	private $sitePcInPublic;			// PC用サイトの公開状況
	private $siteSmartphoneInPublic;	// スマートフォン用サイトの公開状況
	private $smartphoneAutoRedirect;	// スマートフォンの自動遷移
	private $usePageCache;		// 表示キャッシュ機能を使用するかどうか
	private $pageCacheLifetime;	// 画面キャッシュの更新時間(分)
	private $acceptLanguage;			// アクセス可能言語
	private $systemLanguages;			// システムで利用可能な言語
	private $hierarchicalPage;			// 階層化ページを使用するかどうか
	
	const SEL_MENU_ID = 'admin_menu';		// メニュー変換対象メニューバーID
	const TREE_MENU_TASK	= 'menudef';	// メニュー管理画面(多階層)
	const SINGLE_MENU_TASK	= 'smenudef';	// メニュー管理画面(単階層)
	// DB定義
	const CF_SERVER_URL = 'server_url';		// サーバURL
	const CF_SERVER_DIR = 'server_dir';		// サーバディレクトリ
	const CF_DEFAULT_LANG = 'default_lang';			// デフォルト言語
	const CF_PERMIT_INIT_SYSTEM = 'permit_init_system';					// システム初期化可能かどうか
	const CF_PERMIT_CHANGE_LANG = 'permit_change_lang';					// 言語変更可能かどうか
	const CF_REGENERATE_SESSION_ID = 'regenerate_session_id';			// セッションIDを毎回更新するかどうか
	const CF_SITE_IN_PUBLIC = 'site_in_public';	// Webサイトの公開状況
	const CF_SITE_PC_IN_PUBLIC = 'site_pc_in_public';				// PC用サイトの公開状況
	const CF_SITE_SMARTPHONE_IN_PUBLIC = 'site_smartphone_in_public';		// スマートフォン用サイトの公開状況
	const CF_USE_PAGE_CACHE = 'use_page_cache';		// 画面キャッシュ機能を使用するかどうか
	const CF_PAGE_CACHE_LIFETIME = 'page_cache_lifetime';		// 画面キャッシュの更新時間(分)
	const CF_DEFAULT_TEMPLATE			= 'default_template';			// システム定義値取得用キー(PC用デフォルトテンプレート)
	const CF_DEFAULT_SUB_TEMPLATE		= 'default_sub_template';			// システム定義値取得用キー(PC用デフォルトサブテンプレート)
	const CF_DEFAULT_TEMPLATE_SMARTPHONE	= 'smartphone_default_template';	// システム定義値取得用キー(スマートフォン用デフォルトテンプレート)
	const CF_ADMIN_DEFAULT_THEME = 'admin_default_theme';		// 管理画面用jQueryUIテーマ
	const CF_DEFAULT_THEME = 'default_theme';		// フロント画面用jQueryUIテーマ
	const CF_ACCEPT_LANGUAGE	= 'accept_language';	// アクセス可能言語
	const CF_ADMIN_DEFAULT_TEMPLATE = 'admin_default_template';		// 管理用デフォルトテンプレート
	const CF_SMARTPHONE_AUTO_REDIRECT = 'smartphone_auto_redirect';		// スマートフォンの自動遷移
	const CF_HIERARCHICAL_PAGE = 'hierarchical_page';		// 階層化ページを使用するかどうか
	const DEFAULT_PAGE_CACHE_LIFETIME = 1440;		// デフォルトの画面キャッシュの更新時間(分)。1日ごと。
	const CF_UPLOAD_IMAGE_AUTORESIZE = 'upload_image_autoresize';		// 画像リサイズ機能を使用するかどうか
	const CF_UPLOAD_IMAGE_AUTORESIZE_MAX_WIDTH = 'upload_image_autoresize_max_width';		// 画像リサイズ機能最大画像幅
	const CF_UPLOAD_IMAGE_AUTORESIZE_MAX_HEIGHT = 'upload_image_autoresize_max_height';		// 画像リサイズ機能最大画像高さ
	const CF_SITE_MENU_HIER = 'site_menu_hier';		// サイトのメニューを階層化するかどうか
	const CF_SYSTEM_MANAGER_ENABLE_TASK	= 'system_manager_enable_task';	// システム運用者が実行可能な管理画面タスク
	const CF_JQUERY_VERSION = 'jquery_version';			// jQueryバージョン
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
											
		// システムDBオブジェクト取得
		//$this->systemDb = $this->gInstance->getSytemDbObject();
		
		// 初期値を設定
		$this->defaultAdminTemplateId = '';				// 管理画面用テンプレートID
		$this->defaultTemplateId = '';						// PC用テンプレートID
		$this->defaultSubTemplateId = '';						// PC用サブテンプレートID
		$this->defaultSmartphoneTemplateId = '';			// スマートフォン用テンプレートID
		$this->adminDefaultTheme = '';			// 管理画面用jQueryUIテーマ
		$this->permitInitSystem		= false;			// システム初期化可能かどうか
		$this->siteInPublic = '1';				// Webサイトの公開状況
		$this->sitePcInPublic = '1';			// PC用サイトの公開状況
		$this->siteSmartphoneInPublic = '1';	// スマートフォン用サイトの公開状況
		$this->smartphoneAutoRedirect = '0';	// スマートフォンの自動遷移
		$this->usePageCache = '0';		// 表示キャッシュ機能を使用するかどうか
		$this->pageCacheLifetime = self::DEFAULT_PAGE_CACHE_LIFETIME;	// 画面キャッシュの更新時間(分)
		$this->acceptLanguage = array();			// アクセス可能言語
		$this->systemLanguages = array('ja', 'en');			// システムで利用可能な言語
		$this->hierarchicalPage = false;			// 階層化ページを使用するかどうか
	}
	/**
	 * システム定義値を取得
	 *
	 * @param string $key    キーとなる項目値
	 * @return string		値
	 */
	function getSystemConfig($key)
	{
		$value = $this->_systemConfigArray[$key];
		if (isset($value)){
			return $value;
		} else {
			return '';
		}
	}
	/**
	 * システム定義値を更新
	 *
	 * @param string $key		キーとなる項目値
	 * @param string $value		値
	 * @return					true = 正常、false=異常
	 */
	function updateSystemConfig($key, $value)
	{
		$ret = $this->systemDb->updateSystemConfig($key, $value);
		if ($ret) $this->_systemConfigArray[$key] = $value;
		return $ret;
	}
	/**
	 * サイト定義値を取得
	 *
	 * @param string $key    キーとなる項目値
	 * @return string		値
	 */
	function getSiteDef($key)
	{
		$value = $this->_siteDefArray[$key];
		if (isset($value)){
			return $value;
		} else {
			return '';
		}
	}
	/**
	 * システム定義値、サイト定義値をDBから取得
	 *
	 * @return bool			true=取得成功、false=取得失敗
	 */
	function _loadSystemConfig()
	{
		$this->_systemConfigArray = array();
		$this->_siteDefArray = array();

		// システム定義を読み込み
		$ret = $this->systemDb->getAllSystemConfig($rows);
		if ($ret){
			// 取得データを連想配列にする
			$configCount = count($rows);
			for ($i = 0; $i < $configCount; $i++){
				$key = $rows[$i]['sc_id'];
				$value = $rows[$i]['sc_value'];
				$this->_systemConfigArray[$key] = $value;
			}
			
			// システムURL,ディレクトリのチェック。変更されている場合はログを出力してDBを更新。
			if (!defined('M3_STATE_IN_INSTALL')){		// インストールモード以外の場合
				$serverUrl = $this->getSystemConfig(self::CF_SERVER_URL);
				$serverDir = $this->getSystemConfig(self::CF_SERVER_DIR);
				if ($serverUrl != M3_SYSTEM_ROOT_URL || $serverDir != M3_SYSTEM_ROOT_PATH){
					
					$ret = $this->systemDb->updateSystemConfig(self::CF_SERVER_URL, M3_SYSTEM_ROOT_URL);
					if ($ret) $this->_systemConfigArray[self::CF_SERVER_URL] = M3_SYSTEM_ROOT_URL;
					$ret = $this->systemDb->updateSystemConfig(self::CF_SERVER_DIR, M3_SYSTEM_ROOT_PATH);
					if ($ret) $this->_systemConfigArray[self::CF_SERVER_DIR] = M3_SYSTEM_ROOT_PATH;
					
					$errMsg = 'DBの自動修正: システム設定マスター(_system_config)のサーバURL(server_url)またはサーバディレクトリ(server_dir)が変更されているので自動修正しました。URL: ' . $serverUrl . '=>' . M3_SYSTEM_ROOT_URL . ', ディレクトリ: ' . $serverDir . '=>' . M3_SYSTEM_ROOT_PATH;
 					$this->gLog->error(__METHOD__, $errMsg);
				}
			}
			
			// メンバー変数に再設定
			$this->defaultAdminTemplateId	= $this->getSystemConfig(self::CF_ADMIN_DEFAULT_TEMPLATE);				// 管理画面用テンプレートID
			$this->defaultTemplateId		= $this->getSystemConfig(self::CF_DEFAULT_TEMPLATE);	// PC用テンプレートID
			$this->defaultSubTemplateId		= $this->getSystemConfig(self::CF_DEFAULT_SUB_TEMPLATE);						// PC用サブテンプレートID
			$this->defaultSmartphoneTemplateId = $this->getSystemConfig(self::CF_DEFAULT_TEMPLATE_SMARTPHONE);		// スマートフォン用テンプレートID
			$this->adminDefaultTheme = $this->getSystemConfig(self::CF_ADMIN_DEFAULT_THEME);			// 管理画面用jQueryUIテーマ
			if ($this->getSystemConfig(self::CF_PERMIT_INIT_SYSTEM) == '1'){// システム初期化可能かどうか
				$this->permitInitSystem = true;
			} else {
				$this->permitInitSystem = false;
			}
			$this->siteInPublic		= $this->getSystemConfig(self::CF_SITE_IN_PUBLIC);			// Webサイトの公開状況
			if ($this->siteInPublic == '') $this->siteInPublic = '1';		// デフォルトは公開
			$this->sitePcInPublic		= $this->getSystemConfig(self::CF_SITE_PC_IN_PUBLIC);			// PC用サイトの公開状況
			if ($this->sitePcInPublic == '') $this->sitePcInPublic = '1';		// デフォルトは公開
			$this->siteSmartphoneInPublic	= $this->getSystemConfig(self::CF_SITE_SMARTPHONE_IN_PUBLIC);	// スマートフォン用サイトの公開状況
			if ($this->siteSmartphoneInPublic == '') $this->siteSmartphoneInPublic = '1';		// デフォルトは公開
			$this->smartphoneAutoRedirect	= $this->getSystemConfig(self::CF_SMARTPHONE_AUTO_REDIRECT);	// スマートフォンの自動遷移
			if ($this->smartphoneAutoRedirect == '') $this->smartphoneAutoRedirect = '0';
			$this->usePageCache	= $this->getSystemConfig(self::CF_USE_PAGE_CACHE);	// 表示キャッシュ機能を使用するかどうか
			if ($this->usePageCache == '') $this->usePageCache = '0';		// デフォルトはキャッシュなし
			$this->pageCacheLifetime = intval($this->getSystemConfig(self::CF_PAGE_CACHE_LIFETIME));	// 画面キャッシュの更新時間(分)
			if ($this->pageCacheLifetime < 0) $this->pageCacheLifetime = self::DEFAULT_PAGE_CACHE_LIFETIME;
			if ($this->getSystemConfig(self::CF_HIERARCHICAL_PAGE) == '1') $this->hierarchicalPage = true;			// 階層化ページを使用するかどうか
			
			$value = $this->getSystemConfig(self::CF_ACCEPT_LANGUAGE);			// アクセス可能言語
			if (!empty($value)) $this->acceptLanguage = explode(',', $value);
		} else {
			$this->gLog->error(__METHOD__, 'DBエラー発生: システム定義(_system_config)が読み込めません。');
			return false;
		}
		
		// 言語取得
		$lang = $this->getSystemConfig(self::CF_DEFAULT_LANG);// デフォルト言語

		// サイト定義をデフォルト言語で読み込む。データが空でもエラーとしない
		$ret = $this->systemDb->getAllSiteDefValue($lang, $rows);
		if ($ret){
			// 取得データを連想配列にする
			$defCount = count($rows);
			for ($i = 0; $i < $defCount; $i++){
				$key = $rows[$i]['sd_id'];
				$value = $rows[$i]['sd_value'];
				$this->_siteDefArray[$key] = $value;
			}
		}
		return true;
	}
	/**
	 * システム初期化可能どうかを返す
	 *
	 * @param bool $reload	再取得するかどうか
	 * @return bool		true=システム初期化モード、false=システム初期化しない
	 */
	public function canInitSystem($reload = false)
	{
		if ($reload){
			$value = $this->systemDb->getSystemConfig(self::CF_PERMIT_INIT_SYSTEM);			// システム初期化可能かどうか
			if ($value == '1'){
				$this->permitInitSystem = true;
			} else {
				$this->permitInitSystem = false;
			}
		}
		return $this->permitInitSystem;			// システム初期化可能かどうか
	}
	/**
	 * システム初期化可能にする
	 *
	 * @return 		なし
	 */
	public function enableInitSystem()
	{
		// データを更新
		$this->systemDb->updateSystemConfig(self::CF_PERMIT_INIT_SYSTEM, 1);
		
		// データを再取得
		$this->canInitSystem(true);
	}
	/**
	 * システム初期化を不可にする
	 *
	 * @return 		なし
	 */
	public function disableInitSystem()
	{
		// データを更新
		$this->systemDb->updateSystemConfig(self::CF_PERMIT_INIT_SYSTEM, 0);
		
		// データを再取得
		$this->canInitSystem(true);
	}
	/**
	 * セッションを毎回更新するかどうかを返す
	 *
	 * @return bool		true=毎回更新、false=更新しない
	 */
	public function regenerateSessionId()
	{
		$retValue = $this->getSystemConfig(self::CF_REGENERATE_SESSION_ID);
		if ($retValue == '1'){
			return true;
		} else {
			return false;
		}
	}
	/**
	 * 表示言語変更可能かどうかを取得
	 *
	 * @return bool			true=変更可能、false=変更不可
	 */
	function canChangeLang()
	{
		$retValue = $this->getSystemConfig(self::CF_PERMIT_CHANGE_LANG);
		if ($retValue == '1'){
			return true;
		} else {
			return false;
		}
	}
	/**
	 * 管理画面用のデフォルトのテンプレートIDを取得
	 *
	 * @param bool $reload	再取得するかどうか
	 * @return string	デフォルトのテンプレートID
	 */
	function defaultAdminTemplateId($reload = false)
	{
		if ($reload){
			$this->defaultAdminTemplateId		= $this->systemDb->getSystemConfig(self::CF_ADMIN_DEFAULT_TEMPLATE);
		}
		return $this->defaultAdminTemplateId;
	}
	/**
	 * デフォルトのテンプレートIDを取得
	 *
	 * @param bool $reload	再取得するかどうか
	 * @return string	デフォルトのテンプレートID
	 */
	function defaultTemplateId($reload = false)
	{
		if ($reload){
			$this->defaultTemplateId		= $this->systemDb->getSystemConfig(self::CF_DEFAULT_TEMPLATE);
		}
		return $this->defaultTemplateId;
	}
	/**
	 * デフォルトのサブテンプレートIDを取得
	 *
	 * @param bool $reload	再取得するかどうか
	 * @return string	デフォルトのテンプレートID
	 */
	function defaultSubTemplateId($reload = false)
	{
		if ($reload){
			$this->defaultSubTemplateId		= $this->systemDb->getSystemConfig(self::CF_DEFAULT_SUB_TEMPLATE);
		}
		return $this->defaultSubTemplateId;
	}
	/**
	 * スマートフォン用デフォルトのテンプレートIDを取得
	 *
	 * @param bool $reload	再取得するかどうか
	 * @return string	デフォルトのテンプレートID
	 */
	function defaultSmartphoneTemplateId($reload = false)
	{
		if ($reload){
			$this->defaultSmartphoneTemplateId		= $this->systemDb->getSystemConfig(self::CF_DEFAULT_TEMPLATE_SMARTPHONE);
		}
		return $this->defaultSmartphoneTemplateId;
	}
	/**
	 * デフォルトテンプレート、サブテンプレートの変更
	 *
	 * @param string  $templateId		デフォルトテンプレートID
	 * @param string  $subTemplateId	デフォルトのサブテンプレートID
	 * @return							true=成功、false=失敗
	 */
	function changeDefaultTemplate($templateId, $subTemplateId = '')
	{
		$ret = $this->systemDb->updateSystemConfig(self::CF_DEFAULT_TEMPLATE, $templateId);
		$ret = $this->systemDb->updateSystemConfig(self::CF_DEFAULT_SUB_TEMPLATE, $subTemplateId);
		
		// データ再取得
		$this->defaultTemplateId(true);
		$this->defaultSubTemplateId(true);
		return $ret;
	}
	/**
	 * スマートフォン用デフォルトテンプレートの変更
	 *
	 * @param string  $templateId	デフォルトテンプレートID
	 * @return						true=成功、false=失敗
	 */
	function changeDefaultSmartphoneTemplate($templateId)
	{
		$ret = $this->systemDb->updateSystemConfig(self::CF_DEFAULT_TEMPLATE_SMARTPHONE, $templateId);
		
		// データ再取得
		$this->defaultSmartphoneTemplateId(true);
		return $ret;
	}
	/**
	 * 管理画面用jQueryUIテーマを取得
	 *
	 * @param bool $reload	再取得するかどうか
	 * @return string	デフォルトのjQueryUIテーマ
	 */
	function adminDefaultTheme($reload = false)
	{
		if ($reload){
			$this->adminDefaultTheme		= $this->systemDb->getSystemConfig(self::CF_ADMIN_DEFAULT_THEME);
		}
		return $this->adminDefaultTheme;
	}
	/**
	 * フロント画面用jQueryUIテーマを取得
	 *
	 * @return string	jQueryUIテーマ
	 */
	function defaultTheme()
	{
		static $theme;

		if (!isset($theme)) $theme = $this->getSystemConfig(self::CF_DEFAULT_THEME);
		return $theme;
	}
	/**
	 * デフォルト言語以外のサイト定義を読み込む
	 *
	 * @param string $langId		言語ID
	 * @return bool					true=成功(指定言語あり)、false=失敗(指定言語なし)
	 */
	function roadSiteDefByLang($langId)
	{
		if ($langId == $this->getSystemConfig(self::CF_DEFAULT_LANG)) return false;
		
		// サイト定義を読み込む
		$ret = $this->systemDb->getAllSiteDefValue($langId, $rows);
		if ($ret){
			// 取得データを連想配列にする
			$defCount = count($rows);
			for ($i = 0; $i < $defCount; $i++){
				$key = $rows[$i]['sd_id'];
				$value = $rows[$i]['sd_value'];
				
				// 値が存在した場合のみ上書き
				if (!empty($value)){
					$this->_siteDefArray[$key] = $value;
				}
			}
		}
		return $ret;
	}
	/**
	 * アクセス可能言語を取得
	 *
	 * @param bool $reload	再取得するかどうか
	 * @return array	アクセス可能言語
	 */
	function getAcceptLanguage($reload = false)
	{
		if ($reload){
			$value = $this->systemDb->getSystemConfig(self::CF_ACCEPT_LANGUAGE);			// アクセス可能言語
			if (empty($value)){
				$this->acceptLanguage = array();
			} else {
				$this->acceptLanguage = explode(',', $value);
			}
		}
		return $this->acceptLanguage;
	}
	/**
	 * システムで利用可能な言語を取得
	 *
	 * @return array	言語
	 */
	function getSystemLanguages()
	{
		return $this->systemLanguages;
	}
	/**
	 * アップロード可能なファイルの最大サイズを返す
	 *
	 * @param bool $byInteger	数値のバイトサイズを返すかどうか
	 * @return string			バイトサイズ
	 */
	public function getMaxFileSizeForUpload($byInteger = false)
	{	
		$limit = convBytes(ini_get('upload_max_filesize')) > convBytes(ini_get('post_max_size')) ? ini_get('post_max_size') : ini_get('upload_max_filesize');
		$limit = convBytes($limit) > convBytes(ini_get('memory_limit')) ? ini_get('memory_limit') : $limit;
		
		if ($byInteger){		// ファイルサイズを数値で返す場合
			return convBytes($limit);
		} else {
			return $limit;
		}
	}
	/**
	 * アクセスポイントが有効かどうかを返す
	 *
	 * @param int $type		デバイスタイプ(0=PC,1=携帯,2=スマートフォン)
	 * @return bool			true=有効、false=無効
	 */
	public function getSiteActiveStatus($type)
	{
		static $siteActiveStatus;			// アクセスポイントが有効かどうか
	
		if (!isset($siteActiveStatus)){
			$siteActiveStatus = array();
			
			$ret = $this->systemDb->getPageIdRecords(0/*ページID*/, $rows);
			if ($ret){
				for ($i = 0; $i < 3; $i++){
					// ページID作成
					switch ($i){
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
					for ($j = 0; $j < count($rows); $j++){
						if ($rows[$j]['pg_id'] == $pageId){
							$siteActiveStatus[$i] = $rows[$j]['pg_active'];
							break;
						}
					}
				}
			}
		}
		return $siteActiveStatus[$type];
	}
	/**
	 * Webサイトを公開するかどうか
	 *
	 * @param bool $reload	再取得するかどうか
	 * @return bool			true=公開、false=非公開
	 */
	public function siteInPublic($reload = false)
	{
		if ($reload){
			$this->siteInPublic		= $this->systemDb->getSystemConfig(self::CF_SITE_IN_PUBLIC);			// Webサイトの公開状況
			if ($this->siteInPublic == '') $this->siteInPublic = '1';		// デフォルトは公開
		}
		return $this->siteInPublic;			// Webサイトの公開状況
	}
	/**
	 * PC用サイトを公開するかどうか
	 *
	 * @param bool $reload	再取得するかどうか
	 * @return bool			true=公開、false=非公開
	 */
	public function sitePcInPublic($reload = false)
	{
		if ($reload){
			$this->sitePcInPublic		= $this->systemDb->getSystemConfig(self::CF_SITE_PC_IN_PUBLIC);			// PC用サイトの公開状況
			if ($this->sitePcInPublic == '') $this->sitePcInPublic = '1';		// デフォルトは公開
		}
		return $this->sitePcInPublic;			// PC用サイトの公開状況
	}
	/**
	 * スマートフォン用サイトを公開するかどうか
	 *
	 * @param bool $reload	再取得するかどうか
	 * @return bool			true=公開、false=非公開
	 */
	public function siteSmartphoneInPublic($reload = false)
	{
		if ($reload){
			$this->siteSmartphoneInPublic	= $this->systemDb->getSystemConfig(self::CF_SITE_SMARTPHONE_IN_PUBLIC);	// スマートフォン用サイトの公開状況
			if ($this->siteSmartphoneInPublic == '') $this->siteSmartphoneInPublic = '1';		// デフォルトは公開
		}
		return $this->siteSmartphoneInPublic;			// スマートフォン用サイトの公開状況
	}
	/**
	 * スマートフォンの自動遷移を行うかどうか
	 *
	 * @param bool $reload	再取得するかどうか
	 * @return bool			true=公開、false=非公開
	 */
	public function smartphoneAutoRedirect($reload = false)
	{
		if ($reload){
			$this->smartphoneAutoRedirect	= $this->systemDb->getSystemConfig(self::CF_SMARTPHONE_AUTO_REDIRECT);	// スマートフォンの自動遷移
			if ($this->smartphoneAutoRedirect == '') $this->smartphoneAutoRedirect = '0';
		}
		return $this->smartphoneAutoRedirect;
	}
	/**
	 * 表示キャッシュ機能を使用するかどうか
	 *
	 * @param bool $reload	再取得するかどうか
	 * @return bool			true=キャッシュ、false=キャッシュなし
	 */
	public function usePageCache($reload = false)
	{
		if ($reload){
			$this->usePageCache	= $this->systemDb->getSystemConfig(self::CF_USE_PAGE_CACHE);	// 表示キャッシュ機能を使用するかどうか
			if ($this->usePageCache == '') $this->usePageCache = '0';		// デフォルトはキャッシュなし
		}
		return $this->usePageCache;
	}
	/**
	 * 画面キャッシュの更新時間
	 *
	 * @param bool $reload	再取得するかどうか
	 * @return int			キャッシュの更新時間(分)
	 */
	public function pageCacheLifetime($reload = false)
	{
		if ($reload){
			$this->pageCacheLifetime = intval($this->systemDb->getSystemConfig(self::CF_PAGE_CACHE_LIFETIME));	// 画面キャッシュの更新時間(分)
			if ($this->pageCacheLifetime < 0) $this->pageCacheLifetime = self::DEFAULT_PAGE_CACHE_LIFETIME;
		}
		return $this->pageCacheLifetime;
	}
	/**
	 * 階層化ページを使用するかどうか
	 *
	 * @param bool $reload	再取得するかどうか
	 * @return int			true=階層化ページ使用、false=階層化ページ不使用
	 */
	public function hierarchicalPage($reload = false)
	{
		if ($reload){
			$value = $this->getSystemConfig(self::CF_HIERARCHICAL_PAGE);
			if (empty($value)){
				$this->hierarchicalPage = false;			// 階層化ページを使用するかどうか
			} else {
				$this->hierarchicalPage = true;			// 階層化ページを使用するかどうか
			}
		}
		return $this->hierarchicalPage;
	}
	/**
	 * サイトのメニューを階層化するかどうかを取得
	 *
	 * @return bool			true=階層化、false=階層化なし
	 */
	public function isSiteMenuHier()
	{
		$value = $this->getSystemConfig(self::CF_SITE_MENU_HIER);
		return (bool)$value;
	}
	/**
	 * サイトのメニューを階層化するかどうかを変更
	 *
	 * ・使用するメニュー管理画面を変更
	 *
	 * @param bool $isHier	true=階層化、false=階層化なし
	 * @return bool			true=正常終了、false=異常終了
	 */
	public function changeSiteMenuHier($isHier)
	{
		// メニュー階層化の設定を更新
		if ($isHier){
			$this->systemDb->updateSystemConfig(self::CF_SITE_MENU_HIER, '1');
		} else {
			$this->systemDb->updateSystemConfig(self::CF_SITE_MENU_HIER, '0');
		}
		
		// メニュー情報を更新
		$ret = $this->_getMenuInfo($dummy, $itemId, $row);
		if ($ret){
			// メニュー管理画面を変更
			if ($isHier){		// 多階層の場合
				$ret = $this->systemDb->updateNavItemMenuType($itemId, self::TREE_MENU_TASK);
			} else {
				$ret = $this->systemDb->updateNavItemMenuType($itemId, self::SINGLE_MENU_TASK);
			}
		}
		return $ret;
	}
	/**
	 * メニュー管理画面の情報を取得
	 *
	 * @param bool  $isHier		階層化メニューかどうか
	 * @param int   $itemId		メニュー項目ID
	 * @param array  $row		取得レコード
	 * @return bool				取得できたかどうか
	 */
	function _getMenuInfo(&$isHier, &$itemId, &$row)
	{
		$isHier = false;	// 多階層メニューかどうか
		$ret = $this->systemDb->getNavItemsByTask(self::SEL_MENU_ID, self::TREE_MENU_TASK, $row);
		if ($ret){
			$isHier = true;
		} else {
			$ret = $this->systemDb->getNavItemsByTask(self::SEL_MENU_ID, self::SINGLE_MENU_TASK, $row);
		}
		if ($ret) $itemId = $row['ni_id'];
		return $ret;
	}
	/**
	 * システム運用者が実行可能な管理画面タスクを取得
	 *
	 * @return array	実行可能タスク
	 */
	function getSystemManagerEnableTask()
	{
		static $enableTaskArray;
		
		if (!isset($enableTaskArray)){
			$enableTaskArray = array();
			$enableTask = $this->getSystemConfig(self::CF_SYSTEM_MANAGER_ENABLE_TASK);
			if (!empty($enableTask)) $enableTaskArray = explode(',', $enableTask);
		}
		return $enableTaskArray;
	}
	/**
	 * jQueryバージョンを回復修正
	 *
	 * @param string $version	使用可能なjQueryバージョン
	 * @return bool				true=正常終了、false=異常終了
	 */
	function recoverJQueryVersion($version)
	{
		$ret = $this->systemDb->updateSystemConfig(self::CF_JQUERY_VERSION, $version);
		if ($ret){
			$this->_systemConfigArray[self::CF_JQUERY_VERSION] = $version;
		
			$errMsg = 'DBの自動修正: システム設定マスター(_system_config)のjQueryのバージョン(jquery_version)を自動修正しました。バージョン: ' . $version;
			$this->gLog->error(__METHOD__, $errMsg);
		}
		return $ret;
	}
}
?>
