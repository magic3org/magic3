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
 * @copyright  Copyright 2006-2011 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: admin_mainPortalWidgetContainer.php 5111 2012-08-16 00:53:12Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/admin_mainConfigbasicBaseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/admin_mainDb.php');

class admin_mainPortalWidgetContainer extends admin_mainConfigbasicBaseWidgetContainer
{
	private $db;	// DB接続オブジェクト
	private $siteCategory;		// サイト所属カテゴリー
	private $siteState;			// サイト都道府県メニュー
	private $siteCategoryMenu = array();		// サイト所属カテゴリーメニュー
	private $siteStateMenu = array();			// サイト都道府県メニュー
	const CF_SERVER_ID = 'server_id';		// サーバID
	const CF_PORTAL_SERVER_VERSION = 'portal_server_version';		// ポータルサーババージョン
	const CF_PORTAL_SERVER_URL = 'portal_server_url';				// ポータルサーバURL
	const CF_SITE_REGISTERED_IN_PORTAL = 'site_registered_in_portal';				// サイトのポータルへの登録状況
	const CF_PORTAL_Q_SITE_CATEGORY_MENU = 'portal_q_site_category_menu';			// サイトの所属カテゴリーメニュー
	const CF_PORTAL_Q_SITE_STATE_MENU = 'portal_q_site_state_menu';		// 都道府県メニュー
	const CF_PORTAL_A_SITE_DESCRIPTION = 'portal_a_site_description';			// サイト説明
	const CF_PORTAL_A_SITE_CATEGORY = 'portal_a_site_category';			// サイトの所属カテゴリー
	const CF_PORTAL_A_SITE_STATE = 'portal_a_site_state';				// サイト都道府県
	const MAX_DESC_LENGTH = 140;		// サイト説明の最大文字数
	const CF_SITE_LOGO_FILENAME = 'site_logo_filename';		// サイトロゴファイル
	
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
		return 'portal.tmpl.html';
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
		return 'portal';
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
		$serverId = $this->db->getSystemConfig(self::CF_SERVER_ID);	// サーバID
		$portalVersion = intval($this->db->getSystemConfig(self::CF_PORTAL_SERVER_VERSION));// ポータルサーババージョン
		$portalUrl = $this->db->getSystemConfig(self::CF_PORTAL_SERVER_URL);// ポータルサーバURL
		$siteName = $this->gEnv->getSiteName();		// サイト名
		$siteUrl = $this->gEnv->getRootUrl();		// サイトURL
		$siteDesc = $request->trimValueOf('item_site_desc');		// サイト説明
		$this->siteCategory = $request->trimValueOf('item_site_category');
		$this->siteState = $request->trimValueOf('item_site_state');
		
		$saveEnabled = false;		// 「保存」ボタンの状態
		$act = $request->trimValueOf('act');
		if ($act == 'update'){		// 設定更新のとき
			// 入力チェック
			$this->checkLength($siteDesc, $this->_('Description'), self::MAX_DESC_LENGTH);		// サイト説明
			$this->checkInput($this->siteCategory, $this->_('Category'), $this->_('%s not selected.'));		// サイトカテゴリー
			$this->checkInput($this->siteState, $this->_('State'), $this->_('%s not selected.'));		// サイト都道府県
				
			if ($this->getMsgCount() == 0){
				$updateResult = true;	// 更新状況
				if ($updateResult){
					if (!$this->db->updateSystemConfig(self::CF_PORTAL_A_SITE_DESCRIPTION, $siteDesc)) $updateResult = false;// サイト説明
				}
				if ($updateResult){
					if (!$this->db->updateSystemConfig(self::CF_PORTAL_A_SITE_CATEGORY, $this->siteCategory)) $updateResult = false;// 所属カテゴリー
				}
				if ($updateResult){
					if (!$this->db->updateSystemConfig(self::CF_PORTAL_A_SITE_STATE, $this->siteState)) $updateResult = false;// サイト都道府県
				}

				if ($updateResult){
					$this->setMsg(self::MSG_GUIDANCE, $this->_('Data updated.'));		// データを更新しました
				} else {
					$this->setMsg(self::MSG_APP_ERR, $this->_('Failed in updating data.'));			// データ更新に失敗しました
					$saveEnabled = true;		// 「保存」ボタンの状態
				}
			} else {
				$saveEnabled = true;		// 「保存」ボタンの状態
			}
		} else if ($act == 'newinfo'){		// ポータルサーバから最新情報取得のとき
			$sendSiteName = $siteName;
			if (empty($sendSiteName)) $sendSiteName = $this->_('Untitled Site');
			$params = array('act'		=> 'newinfo',		// ポータル最新情報取得
							'site_name'	=> $sendSiteName,		// サイト名
							'site_url'	=> $siteUrl);		// サイトURL
			$ret = $this->gInstance->getConnectManager()->sendToPortal($params, $xmlObj);
			if ($ret){
				// サイトの登録状況を更新
				$siteRegistered = '0';		// サイトの登録状況
				if (!empty($xmlObj->site_status) && $xmlObj->site_status != 'unregistered') $siteRegistered = '1';
				$this->db->updateSystemConfig(self::CF_SITE_REGISTERED_IN_PORTAL, $siteRegistered);
				
				// ポータルからのリソースを更新
				$currentPortalVersion = intval($xmlObj->version);
				if ($portalVersion < $currentPortalVersion){		// 現在のポータルサーバのバージョンよりも古い場合は最新情報を更新
					$updateResult = true;	// 更新状況
					if ($updateResult){
						if (!$this->db->updateSystemConfig(self::CF_PORTAL_Q_SITE_CATEGORY_MENU, $xmlObj->site_attribute->category_menu)) $updateResult = false;// 所属カテゴリーメニュー
					}
					if ($updateResult){
						if (!$this->db->updateSystemConfig(self::CF_PORTAL_Q_SITE_STATE_MENU, $xmlObj->site_attribute->state_menu)) $updateResult = false;// 都道府県メニュー
					}
					// バージョンを更新
					if ($updateResult){
						if ($this->db->updateSystemConfig(self::CF_PORTAL_SERVER_VERSION, $currentPortalVersion)){
							$portalVersion = $currentPortalVersion;
						} else {
							$updateResult = false;
						}
					}
					if ($updateResult){
						$this->setMsg(self::MSG_GUIDANCE, $this->_('Get new information from portal server.'));		// ポータルサーバから最新情報を取得しました。
					} else {
						$this->setMsg(self::MSG_APP_ERR, $this->_('Failed in getting new information from portal server.'));			// ポータルサーバから最新情報取得に失敗しました
					}
				}
			} else {
				$this->setMsg(self::MSG_APP_ERR, $this->_('Failed in getting new information from portal server.'));			// ポータルサーバから最新情報が取得できませんでした
			}
		} else if ($act == 'registsite' || $act == 'updatesite'){		// ポータルへサイト情報登録、更新のとき
			// 入力チェック
			$this->checkInput($siteName, $this->_('Site Name'));		// サイト名
			
			// URLが有効かどうかチェック
			$ret = checkGlobalUrl($siteUrl);
			if (!$ret) $this->setMsg(self::MSG_USER_ERR, $this->_('Local server not to register to portal server.'));			// ローカルのサーバは登録できません
			
			if ($this->getMsgCount() == 0){
				$sendSiteName = $siteName;
				if (empty($sendSiteName)) $sendSiteName = $this->_('Untitled Site');
				$params = array('act'		=> $act,		// サイト登録
								'site_name'	=> $sendSiteName,		// サイト名
								'site_url'	=> $siteUrl,		// サイトURL
								'site_description'	=> $siteDesc,		// サイト説明
								'site_category'	=> $this->siteCategory,	// サイトカテゴリー
								'site_state'	=> $this->siteState);		// サイト都道府県
		
				$ret = $this->gInstance->getConnectManager()->sendToPortal($params, $xmlObj);
				if ($ret){
					// サイトの登録状況を更新
					$siteRegistered = '0';		// サイトの登録状況
					if (!empty($xmlObj->site_status) && $xmlObj->site_status != 'unregistered') $siteRegistered = '1';
					$this->db->updateSystemConfig(self::CF_SITE_REGISTERED_IN_PORTAL, $siteRegistered);
				
					$this->setMsg(self::MSG_GUIDANCE, $this->_('Send site information to potarl server.'));		// ポータルサーバへサイト情報を送信しました。
				} else {
					$this->setMsg(self::MSG_APP_ERR, $this->_('Failed in sending site information to potarl server.'));			// ポータルサーバへサイト情報送信に失敗しました
				}
			}
		} else {		// 初期表示の場合
			$siteDesc = $this->db->getSystemConfig(self::CF_PORTAL_A_SITE_DESCRIPTION);			// サイト説明
			$this->siteCategory = $this->db->getSystemConfig(self::CF_PORTAL_A_SITE_CATEGORY);			// サイトの所属カテゴリー
			$this->siteState = $this->db->getSystemConfig(self::CF_PORTAL_A_SITE_STATE);				// サイト都道府県
		}
		
		// 表示用データ作成
//		if (empty($siteName)) $siteName = $this->_('Untitled Site');// サイト名
		if ($portalVersion > 0){		// 取得したポータル最新情報があるとき
			// 入力領域表示
			$this->tmpl->setAttribute('site_info', 'visibility', 'visible');
		
			// メニューデータ作成
			// サイト所属カテゴリーメニュー
			$menuDef = $this->db->getSystemConfig(self::CF_PORTAL_Q_SITE_CATEGORY_MENU);
			$this->siteCategoryMenu = explode(';', $menuDef);
			$this->createSiteCategoryMenu();
			
			// サイト都道府県メニュー
			$menuDef = $this->db->getSystemConfig(self::CF_PORTAL_Q_SITE_STATE_MENU);
			$this->siteStateMenu = explode(';', $menuDef);
			$this->createSiteStateMenu();
		} else {
			$this->tmpl->addVar("_widget", "category_desc_items", '');		// サイト所属カテゴリーメニュー初期化
		}
		
		// サイトの登録状況
		$value = $this->db->getSystemConfig(self::CF_SITE_REGISTERED_IN_PORTAL);
		if (empty($value)){
			$siteRegAct = 'registsite';
		} else {
			$siteRegAct = 'updatesite';
		}
		
		// サイトロゴ
		$siteLogoUrl = $this->gEnv->getResourceUrl() . '/etc/site/thumb/' . $this->db->getSystemConfig(self::CF_SITE_LOGO_FILENAME);		// サイトロゴファイル名
		$siteLogoImage = '<img src="' . $this->convertUrlToHtmlEntity($this->getUrl($siteLogoUrl)) . '" />';
		$this->tmpl->addVar("_widget", "logo_image", $siteLogoImage);
		
		// 画面にデータを埋め込む
		$this->tmpl->addVar("_widget", "server_id", $this->convertToDispString($serverId));		// サーバID
		$this->tmpl->addVar("_widget", "site_name", $this->convertToDispString($siteName));		// サイト名
		$this->tmpl->addVar("_widget", "site_url", $this->convertToDispString($siteUrl));		// サイトURL
		$this->tmpl->addVar("_widget", "portal_url", $this->convertToDispString($portalUrl));		// ポータルサーバURL
		$this->tmpl->addVar("site_info", "site_desc", $this->convertToDispString($siteDesc));		// サイト説明
		if (!$saveEnabled) $this->tmpl->addVar("site_info", "save_disabled", "disabled");		// 「保存」ボタンの状態
		if ($saveEnabled || empty($siteDesc) || empty($this->siteCategory) || empty($this->siteState)) $this->tmpl->addVar("site_info", "send_disabled", "disabled");		// 「ポータルへ送信」ボタンの状態
		$this->tmpl->addVar("_widget", "site_act", $this->convertToDispString($siteRegAct));		// サイト登録
		
		// テキストをローカライズ
		$localeText = array();
		$localeText['msg_update'] = $this->_('Save config?');		// 設定を保存しますか?
		$localeText['msg_send_to_portal'] = $this->_('Send site information to portal?');		// サイト情報をポータルへ送信しますか?
		$localeText['label_portal_info'] = $this->_('Portal Information');// ポータル情報
		$localeText['label_get_new_info'] = $this->_('Get new information from portal');// ポータル情報
		$localeText['label_url'] = $this->_('URL');
		$localeText['label_site_info'] = $this->_('Site Information');			// サイト情報
		$localeText['label_site_name'] = $this->_('Site Name');// サイト名
		$localeText['label_site_desc'] = $this->_('Description');// サイト説明
		$localeText['label_site_attr'] = $this->_('Attribute');// サイト属性
		$localeText['label_site_category'] = $this->_('Category:');// サイト所属カテゴリー
		$localeText['label_site_state'] = $this->_('State:');// サイト都道府県
		$localeText['label_server_id'] = $this->_('Server Id');// サーバID
		$localeText['label_unselected'] = $this->_('Unselected');// 未選択
//		$localeText['label_send_to_portal'] = $this->_('Send to Portal');// ポータルへ送信
		if ($siteRegAct == 'registsite'){
			$localeText['label_send_to_portal'] = $this->_('Register Site Information to Portal');// ポータルへサイト情報を登録
		} else {
			$localeText['label_send_to_portal'] = $this->_('Update Site Information in Portal');// ポータルのサイト情報を更新
		}
		$localeText['label_save'] = $this->_('Save');// 保存
		$this->setLocaleText($localeText);
	}
	/**
	 * 所属カテゴリーメニューを作成
	 *
	 * @return 					なし
	 */
	function createSiteCategoryMenu()
	{
		$categoryDescItems = '';
		
		for ($i = 0; $i < count($this->siteCategoryMenu); $i++){
			list($value, $other) = explode('=', $this->siteCategoryMenu[$i]);
			list($name, $desc) = explode('|', $other);
			$selected = '';
			if ($value == $this->siteCategory){
				$selected = 'selected';
			}
		
			$row = array(
				'value'    => $this->convertToDispString($value),			// 値
				'name'     => $this->convertToDispString($name),			// 表示名
				'selected' => $selected														// 選択中かどうか
			);
			$this->tmpl->addVars('category_list', $row);
			$this->tmpl->parseTemplate('category_list', 'a');
			
			// カテゴリー説明
			$categoryDescItems .= '"' . $value . '"' . ': "' . $desc . '", ';
		}
		rtrim(',', $categoryDescItems);
		$this->tmpl->addVar("_widget", "category_desc_items", $categoryDescItems);
	}
	/**
	 * 都道府県メニューを作成
	 *
	 * @return 					なし
	 */
	function createSiteStateMenu()
	{
		for ($i = 0; $i < count($this->siteStateMenu); $i++){
			list($value, $name) = explode('=', $this->siteStateMenu[$i]);
			$selected = '';
			if ($value == $this->siteState){
				$selected = 'selected';
			}
		
			$row = array(
				'value'    => $this->convertToDispString($value),			// 値
				'name'     => $this->convertToDispString($name),			// 表示名
				'selected' => $selected														// 選択中かどうか
			);
			$this->tmpl->addVars('state_list', $row);
			$this->tmpl->parseTemplate('state_list', 'a');
		}
	}
}
?>
