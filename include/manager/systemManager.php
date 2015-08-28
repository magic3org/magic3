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
 * @copyright  Copyright 2006-2015 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once(M3_SYSTEM_INCLUDE_PATH . '/common/core.php');		// Magic3コアクラス

class SystemManager extends Core
{
	private $_systemConfigArray = array();			// システム定義値
	private $_siteDefArray = array();			// サイト定義値
	private $defaultAdminTemplateId;				// 管理画面用テンプレートID
	private $defaultTemplateId;						// PC用テンプレートID
	private $defaultSubTemplateId;						// PC用サブテンプレートID
	private $defaultMobileTemplateId;				// 携帯用テンプレートID
	private $defaultSmartphoneTemplateId;				// スマートフォン用テンプレートID
	private $adminDefaultTheme;			// 管理画面用jQueryUIテーマ
	private $permitInitSystem;						// システム初期化可能かどうか
	private $siteInPublic;				// Webサイトの公開状況
	private $sitePcInPublic;			// PC用サイトの公開状況
	private $siteMobileInPublic;		// 携帯用サイトの公開状況
	private $siteSmartphoneInPublic;	// スマートフォン用サイトの公開状況
	private $mobileAutoRedirect;		// 携帯の自動遷移
	private $smartphoneAutoRedirect;	// スマートフォンの自動遷移
	private $usePageCache;		// 表示キャッシュ機能を使用するかどうか
	private $pageCacheLifetime;	// 画面キャッシュの更新時間(分)
	private $useTemplateIdInSession;	// テンプレートIDをセッションに保存するかどうか
	private $acceptLanguage;			// アクセス可能言語
	private $systemLanguages;			// システムで利用可能な言語
	private $hierarchicalPage;			// 階層化ページを使用するかどうか
	const CF_DEFAULT_LANG = 'default_lang';			// デフォルト言語
	const CF_PERMIT_INIT_SYSTEM = 'permit_init_system';					// システム初期化可能かどうか
	const CF_PERMIT_CHANGE_LANG = 'permit_change_lang';					// 言語変更可能かどうか
	const CF_USE_TEMPLATE_ID_IN_SESSION = 'use_template_id_in_session';			// セッションのテンプレートIDを使用するかどうか
	const CF_REGENERATE_SESSION_ID = 'regenerate_session_id';			// セッションIDを毎回更新するかどうか
	const CF_SITE_IN_PUBLIC = 'site_in_public';	// Webサイトの公開状況
	const CF_SITE_PC_IN_PUBLIC = 'site_pc_in_public';				// PC用サイトの公開状況
	const CF_SITE_MOBILE_IN_PUBLIC = 'site_mobile_in_public';		// 携帯用サイトの公開状況
	const CF_SITE_SMARTPHONE_IN_PUBLIC = 'site_smartphone_in_public';		// スマートフォン用サイトの公開状況
	const CF_USE_PAGE_CACHE = 'use_page_cache';		// 画面キャッシュ機能を使用するかどうか
	const CF_PAGE_CACHE_LIFETIME = 'page_cache_lifetime';		// 画面キャッシュの更新時間(分)
	const CF_DEFAULT_TEMPLATE			= 'default_template';			// システム定義値取得用キー(PC用デフォルトテンプレート)
	const CF_DEFAULT_SUB_TEMPLATE		= 'default_sub_template';			// システム定義値取得用キー(PC用デフォルトサブテンプレート)
	const CF_DEFAULT_TEMPLATE_MOBILE	= 'mobile_default_template';	// システム定義値取得用キー(携帯用デフォルトテンプレート)
	const CF_DEFAULT_TEMPLATE_SMARTPHONE	= 'smartphone_default_template';	// システム定義値取得用キー(スマートフォン用デフォルトテンプレート)
	const CF_ADMIN_DEFAULT_THEME = 'admin_default_theme';		// 管理画面用jQueryUIテーマ
	const CF_DEFAULT_THEME = 'default_theme';		// 一般画面用jQueryUIテーマ
	const CF_ACCEPT_LANGUAGE	= 'accept_language';	// アクセス可能言語
	const CF_ADMIN_DEFAULT_TEMPLATE = 'admin_default_template';		// 管理用デフォルトテンプレート
	const CF_MOBILE_AUTO_REDIRECT = 'mobile_auto_redirect';		// 携帯の自動遷移
	const CF_SMARTPHONE_AUTO_REDIRECT = 'smartphone_auto_redirect';		// スマートフォンの自動遷移
	const CF_HIERARCHICAL_PAGE = 'hierarchical_page';		// 階層化ページを使用するかどうか
	const DEFAULT_PAGE_CACHE_LIFETIME = 1440;		// デフォルトの画面キャッシュの更新時間(分)。1日ごと。
	const CF_UPLOAD_IMAGE_AUTORESIZE = 'upload_image_autoresize';		// 画像リサイズ機能を使用するかどうか
	const CF_UPLOAD_IMAGE_AUTORESIZE_MAX_WIDTH = 'upload_image_autoresize_max_width';		// 画像リサイズ機能最大画像幅
	const CF_UPLOAD_IMAGE_AUTORESIZE_MAX_HEIGHT = 'upload_image_autoresize_max_height';		// 画像リサイズ機能最大画像高さ

	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
											
		// システムDBオブジェクト取得
		$this->db = $this->gInstance->getSytemDbObject();
		
		// 初期値を設定
		$this->defaultAdminTemplateId = '';				// 管理画面用テンプレートID
		$this->defaultTemplateId = '';						// PC用テンプレートID
		$this->defaultSubTemplateId = '';						// PC用サブテンプレートID
		$this->defaultMobileTemplateId = '';				// 携帯用テンプレートID
		$this->defaultSmartphoneTemplateId = '';			// スマートフォン用テンプレートID
		$this->adminDefaultTheme = '';			// 管理画面用jQueryUIテーマ
		$this->permitInitSystem		= false;			// システム初期化可能かどうか
		$this->siteInPublic = '1';				// Webサイトの公開状況
		$this->sitePcInPublic = '1';			// PC用サイトの公開状況
		$this->siteMobileInPublic = '1';		// 携帯用サイトの公開状況
		$this->siteSmartphoneInPublic = '1';	// スマートフォン用サイトの公開状況
		$this->mobileAutoRedirect = '0';		// 携帯の自動遷移
		$this->smartphoneAutoRedirect = '0';	// スマートフォンの自動遷移
		$this->usePageCache = '0';		// 表示キャッシュ機能を使用するかどうか
		$this->pageCacheLifetime = self::DEFAULT_PAGE_CACHE_LIFETIME;	// 画面キャッシュの更新時間(分)
		$this->useTemplateIdInSession = true;	// テンプレートIDをセッションに残すかどうか
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
		$ret = $this->db->updateSystemConfig($key, $value);
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
		$ret = $this->db->getAllSystemConfig($rows);
		if ($ret){
			// 取得データを連想配列にする
			$configCount = count($rows);
			for ($i = 0; $i < $configCount; $i++){
				$key = $rows[$i]['sc_id'];
				$value = $rows[$i]['sc_value'];
				$this->_systemConfigArray[$key] = $value;
			}
			// メンバー変数に再設定
			$this->defaultAdminTemplateId	= $this->getSystemConfig(self::CF_ADMIN_DEFAULT_TEMPLATE);				// 管理画面用テンプレートID
			$this->defaultTemplateId		= $this->getSystemConfig(self::CF_DEFAULT_TEMPLATE);	// PC用テンプレートID
			$this->defaultSubTemplateId		= $this->getSystemConfig(self::CF_DEFAULT_SUB_TEMPLATE);						// PC用サブテンプレートID
			$this->defaultMobileTemplateId	= $this->getSystemConfig(self::CF_DEFAULT_TEMPLATE_MOBILE);// 携帯用テンプレートID
			$this->defaultSmartphoneTemplateId = $this->getSystemConfig(self::CF_DEFAULT_TEMPLATE_SMARTPHONE);		// スマートフォン用テンプレートID
			$this->adminDefaultTheme = $this->getSystemConfig(self::CF_ADMIN_DEFAULT_THEME);			// 管理画面用jQueryUIテーマ
			if ($this->getSystemConfig(self::CF_PERMIT_INIT_SYSTEM) == '1'){// システム初期化可能かどうか
				$this->permitInitSystem = true;
			} else {
				$this->permitInitSystem = false;
			}
			if ($this->db->getSystemConfig(self::CF_USE_TEMPLATE_ID_IN_SESSION) == '1'){// テンプレートIDをセッションに保存するかどうか
				$this->useTemplateIdInSession = true;
			} else {
				$this->useTemplateIdInSession = false;
			}
			$this->siteInPublic		= $this->getSystemConfig(self::CF_SITE_IN_PUBLIC);			// Webサイトの公開状況
			if ($this->siteInPublic == '') $this->siteInPublic = '1';		// デフォルトは公開
			$this->sitePcInPublic		= $this->getSystemConfig(self::CF_SITE_PC_IN_PUBLIC);			// PC用サイトの公開状況
			if ($this->sitePcInPublic == '') $this->sitePcInPublic = '1';		// デフォルトは公開
			$this->siteMobileInPublic	= $this->getSystemConfig(self::CF_SITE_MOBILE_IN_PUBLIC);	// 携帯用サイトの公開状況
			if ($this->siteMobileInPublic == '') $this->siteMobileInPublic = '1';		// デフォルトは公開
			$this->siteSmartphoneInPublic	= $this->getSystemConfig(self::CF_SITE_SMARTPHONE_IN_PUBLIC);	// スマートフォン用サイトの公開状況
			if ($this->siteSmartphoneInPublic == '') $this->siteSmartphoneInPublic = '1';		// デフォルトは公開
			$this->mobileAutoRedirect	= $this->getSystemConfig(self::CF_MOBILE_AUTO_REDIRECT);	// 携帯の自動遷移
			if ($this->mobileAutoRedirect == '') $this->mobileAutoRedirect = '0';
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
		$ret = $this->db->getAllSiteDefValue($lang, $rows);
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
			$value = $this->db->getSystemConfig(self::CF_PERMIT_INIT_SYSTEM);			// システム初期化可能かどうか
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
		$this->db->updateSystemConfig(self::CF_PERMIT_INIT_SYSTEM, 1);
		
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
		$this->db->updateSystemConfig(self::CF_PERMIT_INIT_SYSTEM, 0);
		
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
	 * テンプレートIDをセッションに保存するかどうかを取得
	 *
	 * @param bool $reload	再取得するかどうか
	 * @return bool			true=変更可能、false=変更不可
	 */
	function useTemplateIdInSession($reload = false)
	{
		if ($reload){
			$retValue = $this->db->getSystemConfig(self::CF_USE_TEMPLATE_ID_IN_SESSION);
			if ($retValue == '1'){
				$this->useTemplateIdInSession = true;
			} else {
				$this->useTemplateIdInSession = false;
			}
		}
		return $this->useTemplateIdInSession;
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
			$this->defaultAdminTemplateId		= $this->db->getSystemConfig(self::CF_ADMIN_DEFAULT_TEMPLATE);
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
			$this->defaultTemplateId		= $this->db->getSystemConfig(self::CF_DEFAULT_TEMPLATE);
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
			$this->defaultSubTemplateId		= $this->db->getSystemConfig(self::CF_DEFAULT_SUB_TEMPLATE);
		}
		return $this->defaultSubTemplateId;
	}
	/**
	 * 携帯用デフォルトのテンプレートIDを取得
	 *
	 * @param bool $reload	再取得するかどうか
	 * @return string	デフォルトのテンプレートID
	 */
	function defaultMobileTemplateId($reload = false)
	{
		if ($reload){
			$this->defaultMobileTemplateId		= $this->db->getSystemConfig(self::CF_DEFAULT_TEMPLATE_MOBILE);
		}
		return $this->defaultMobileTemplateId;
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
			$this->defaultSmartphoneTemplateId		= $this->db->getSystemConfig(self::CF_DEFAULT_TEMPLATE_SMARTPHONE);
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
		$ret = $this->db->updateSystemConfig(self::CF_DEFAULT_TEMPLATE, $templateId);
		$ret = $this->db->updateSystemConfig(self::CF_DEFAULT_SUB_TEMPLATE, $subTemplateId);
		
		// データ再取得
		$this->defaultTemplateId(true);
		$this->defaultSubTemplateId(true);
		return $ret;
	}
	/**
	 * 携帯用デフォルトテンプレートの変更
	 *
	 * @param string  $templateId	デフォルトテンプレートID
	 * @return						true=成功、false=失敗
	 */
	function changeDefaultMobileTemplate($templateId)
	{
		$ret = $this->db->updateSystemConfig(self::CF_DEFAULT_TEMPLATE_MOBILE, $templateId);
		
		// データ再取得
		$this->defaultMobileTemplateId(true);
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
		$ret = $this->db->updateSystemConfig(self::CF_DEFAULT_TEMPLATE_SMARTPHONE, $templateId);
		
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
			$this->adminDefaultTheme		= $this->db->getSystemConfig(self::CF_ADMIN_DEFAULT_THEME);
		}
		return $this->adminDefaultTheme;
	}
	/**
	 * 一般画面用jQueryUIテーマを取得
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
		$ret = $this->db->getAllSiteDefValue($langId, $rows);
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
			$value = $this->db->getSystemConfig(self::CF_ACCEPT_LANGUAGE);			// アクセス可能言語
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
			
			$ret = $this->db->getPageIdRecords(0/*ページID*/, $rows);
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
			$this->siteInPublic		= $this->db->getSystemConfig(self::CF_SITE_IN_PUBLIC);			// Webサイトの公開状況
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
			$this->sitePcInPublic		= $this->db->getSystemConfig(self::CF_SITE_PC_IN_PUBLIC);			// PC用サイトの公開状況
			if ($this->sitePcInPublic == '') $this->sitePcInPublic = '1';		// デフォルトは公開
		}
		return $this->sitePcInPublic;			// PC用サイトの公開状況
	}
	/**
	 * 携帯用サイトを公開するかどうか
	 *
	 * @param bool $reload	再取得するかどうか
	 * @return bool			true=公開、false=非公開
	 */
	public function siteMobileInPublic($reload = false)
	{
		if ($reload){
			$this->siteMobileInPublic	= $this->db->getSystemConfig(self::CF_SITE_MOBILE_IN_PUBLIC);	// 携帯用サイトの公開状況
			if ($this->siteMobileInPublic == '') $this->siteMobileInPublic = '1';		// デフォルトは公開
		}
		return $this->siteMobileInPublic;			// 携帯用サイトの公開状況
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
			$this->siteSmartphoneInPublic	= $this->db->getSystemConfig(self::CF_SITE_SMARTPHONE_IN_PUBLIC);	// スマートフォン用サイトの公開状況
			if ($this->siteSmartphoneInPublic == '') $this->siteSmartphoneInPublic = '1';		// デフォルトは公開
		}
		return $this->siteSmartphoneInPublic;			// スマートフォン用サイトの公開状況
	}
	/**
	 * 携帯の自動遷移を行うかどうか
	 *
	 * @param bool $reload	再取得するかどうか
	 * @return bool			true=公開、false=非公開
	 */
	public function mobileAutoRedirect($reload = false)
	{
		if ($reload){
			$this->mobileAutoRedirect	= $this->db->getSystemConfig(self::CF_MOBILE_AUTO_REDIRECT);	// 携帯の自動遷移
			if ($this->mobileAutoRedirect == '') $this->mobileAutoRedirect = '0';
		}
		return $this->mobileAutoRedirect;
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
			$this->smartphoneAutoRedirect	= $this->db->getSystemConfig(self::CF_SMARTPHONE_AUTO_REDIRECT);	// スマートフォンの自動遷移
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
			$this->usePageCache	= $this->db->getSystemConfig(self::CF_USE_PAGE_CACHE);	// 表示キャッシュ機能を使用するかどうか
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
			$this->pageCacheLifetime = intval($this->db->getSystemConfig(self::CF_PAGE_CACHE_LIFETIME));	// 画面キャッシュの更新時間(分)
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
}
?>
