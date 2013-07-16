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
 * @copyright  Copyright 2006-2012 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: admin_mainSmenudefWidgetContainer.php 4979 2012-06-19 05:53:00Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/admin_mainBaseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/admin_mainDb.php');

class admin_mainSmenudefWidgetContainer extends admin_mainBaseWidgetContainer
{
	private $db;	// DB接続オブジェクト
	private $serialNo;		// 選択中の項目のシリアル番号
	private $serialArray = array();			// 表示中のシリアル番号
	private $menuId;		// 現在選択中のメニューID
	private $menuItemType;	// メニュー項目タイプ
	private $menuDeviceType;	// メニューの端末タイプ
	private $currentPageSubId;	// 現在のページサブID
	private $contentId;			// 現在のコンテンツID
	private $isExistsMenuItem;		// メニュー項目が存在するかどうか
	private $isExistsPreviewMenuItem;		// プレビューメニュー項目が存在するかどうか
	private $itemTypeArray;		// メニュー項目の種類
	private $menuHtml;	// コンテンツメニュー
	private $isMultiLang;			// 多言語対応画面かどうか
	private $availableLangRows;	// 利用可能な言語
	private $availableLangArray;	// 利用可能な言語
	const MAIN_MENU_ID = 'main_menu';			// メインメニューID
	const WIDGET_TYPE_MENU = 'menu';		// メニュー型のウィジェット(キャッシュクリア用)
	const CONTENT_TYPE_PC = '';			// 汎用コンテンツのコンテンツタイプ(PC用)
	const CONTENT_TYPE_MOBILE = 'mobile';			// 汎用コンテンツのコンテンツタイプ(携帯用)
	const CONTENT_TYPE_SMARTPHONE = 'smartphone';			// 汎用コンテンツのコンテンツタイプ(スマートフォン用)
	const CONTENT_WIDGET_ID_PC = 'default_content';			// コンテンツ編集ウィジェット(PC用)
	const CONTENT_WIDGET_ID_MOBILE = 'm/content';			// コンテンツ編集ウィジェット(携帯用)
	const CONTENT_WIDGET_ID_SMARTPHONE = 's/content';			// コンテンツ編集ウィジェット(スマートフォン用)
	const LANG_ICON_PATH = '/images/system/flag/';		// 言語アイコンパス
	const ITEM_NAME_HEAD = 'item_name_';				// 多言語対応名前ヘッダ
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->db = new admin_mainDb();
		
		// メニュー項目タイプ
		$this->itemTypeArray = array(	array(	'name' => $this->_('Link'),			'value' => '0'),	// リンク
										array(	'name' => $this->_('Folder'),		'value' => '1'),	// フォルダ
										array(	'name' => $this->_('Text'),			'value' => '2'),	// テキスト
										array(	'name' => $this->_('Separator'),	'value' => '3'));	// セパレータ
		
		// ##### 多言語対応用データ作成 #####
		$this->isMultiLang = $this->gEnv->isMultiLanguageSite();			// 多言語対応画面かどうか
		$this->availableLangRows = array();
		$this->availableLangArray = array();
		$ret = $this->db->getAvailableLang($rows);				// 利用可能な言語を取得
		if ($ret){
			for ($i = 0; $i < count($rows); $i++){
				$langId = $rows[$i]['ln_id'];
				$this->availableLangArray[$langId] = $rows[$i];
			}
			$this->availableLangRows = $rows;
		}
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
		$task = $request->trimValueOf('task');
		
		if ($task == 'smenudef_detail'){		// 詳細画面
			return 'smenudef_detail.tmpl.html';
		} else {			// 一覧画面
			return 'smenudef.tmpl.html';
		}
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
		return 'menudef';
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
		$localeText = array();
		$task = $request->trimValueOf('task');
		
		if ($task == 'smenudef_detail'){	// 詳細画面
			$this->createDetail($request);
			
			// テキストをローカライズ
			$localeText['msg_add'] = $this->_('Add menu item?');		// 項目を新規追加しますか?
			$localeText['msg_update'] = $this->_('Update menu item?');		// 項目を更新しますか?
			$localeText['msg_delete'] = $this->_('Delete menu item?');		// 項目を削除しますか?
			$localeText['label_menu_item_detail'] = $this->_('Menu Item Detail');		// メニュー項目詳細
			$localeText['label_go_back'] = $this->_('Go back');		// 戻る
			$localeText['label_name'] = $this->_('Name');		// 名前
			$localeText['label_lang'] = $this->_('Language');			// 言語
			$localeText['label_new'] = $this->_('New');		// 新規
			$localeText['label_item_type'] = $this->_('Item Type');		// 項目タイプ
			$localeText['label_link_type'] = $this->_('Link Type');		// 表示方法
			$localeText['label_link_self'] = $this->_('Open page in the same window');		// 同ウィンドウで表示
			$localeText['label_link_other'] = $this->_('Open page in the other window');		// 別ウィンドウで表示
			$localeText['label_select_link'] = $this->_('Select Link Type');		// リンク先を選択
			$localeText['label_unselected'] = $this->_('Unselected');	// 未選択
			$localeText['label_link_top'] = $this->_('Top');	// トップ
			$localeText['label_input'] = $this->_('Input URL');	// URL任意設定
			$localeText['label_page_id'] = $this->_('Page ID:');	// ページID：
			$localeText['label_content'] = $this->_('General Contents:');	// 汎用コンテンツ：
			$localeText['label_url'] = $this->_('URL');	// リンク先URL
			$localeText['label_item_visible'] = $this->_('Item Control');	// 表示制御
			$localeText['label_visible'] = $this->_('Visible');	// 公開
			$localeText['msg_link_to_content'] = $this->_('Cotrol visible status linked to contents.');	// リンク先のコンテンツに連動
			$localeText['label_desc'] = $this->_('Description');	// 説明
			$localeText['label_delete'] = $this->_('Delete');	// 削除
			$localeText['label_update'] = $this->_('Update');	// 更新
			$localeText['label_add'] = $this->_('Add');	// 新規追加
		} else {			// 一覧画面
			$this->createList($request);
			
			// テキストをローカライズ
			$localeText['msg_select_item'] = $this->_('Select menu item to edit.');		// 編集する項目を選択してください
			$localeText['msg_select_del_item'] = $this->_('Select menu item to delete.');		// 削除する項目を選択してください
			$localeText['msg_delete_item'] = $this->_('Delete selected item?');// 選択項目を削除しますか?
			$localeText['label_menu_item_list'] = $this->_('Menu Item List');					// メニュー項目一覧
			$localeText['label_new'] = $this->_('New');					// 新規
			$localeText['label_edit'] = $this->_('Edit');				// 編集
			$localeText['label_delete'] = $this->_('Delete');			// 削除
			$localeText['label_check'] = $this->_('Select');			// 選択
			$localeText['label_name'] = $this->_('Name');			// 名前
			$localeText['label_lang'] = $this->_('Language');			// 言語
			$localeText['label_url'] = $this->_('Url');			// リンク先URL
			$localeText['label_link_type'] = $this->_('Link Type');			// 表示方法
			$localeText['label_visible'] = $this->_('Visible');			// 公開
			$localeText['label_operation'] = $this->_('Operation');		// 操作
			$localeText['label_menu_layout'] = $this->_('Menu Layout');		// メニューレイアウト
			$localeText['msg_change_item_order'] = $this->_('Menu items can be sorted by mouse drag and drop.');		// マウスドラッグで項目の表示順を変更できます
		}
		$this->setLocaleText($localeText);
	}
	/**
	 * 一覧画面作成
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param								なし
	 */
	function createList($request)
	{
		$userId		= $this->gEnv->getCurrentUserId();
		$langId	= $this->gEnv->getCurrentLanguage();		// 表示言語を取得
		
		$fixedMode = $request->trimValueOf('fixed_mode');		// メニュー定義IDが固定かどうか
		if ($fixedMode == ''){		// 値が設定されていないときは設定
			if ($request->trimValueOf('menuid') == ''){
				$fixedMode = '0';
			} else {
				$fixedMode = '1';		// 固定
			}
		}
		$this->menuId = $request->trimValueOf('menuid');		// 現在選択中のメニュータイプ
		if ($this->menuId == '') $this->menuId = self::MAIN_MENU_ID;		// デフォルトは通常のメニュー

		// メニューの端末タイプを取得
		$this->menuDeviceType = 0;	// メニューの端末タイプ
		$ret = $this->db->getMenuId($this->menuId, $row);
		if ($ret) $this->menuDeviceType = $row['mn_device_type'];
		
		// コンテンツタイプを取得
		switch ($this->menuDeviceType){
			case 0:			// PC用
			default:
				$contType = self::CONTENT_TYPE_PC;			// 汎用コンテンツのコンテンツタイプ
				break;
			case 1:			// 携帯用
				$contType = self::CONTENT_TYPE_MOBILE;			// 汎用コンテンツのコンテンツタイプ
				break;
			case 2:			// スマートフォン用
				$contType = self::CONTENT_TYPE_SMARTPHONE;			// 汎用コンテンツのコンテンツタイプ
				break;
		}
		
		$act = $request->trimValueOf('act');
		if ($act == 'updatemenu'){		// メニュー項目順の更新のとき
			$menuitems = $request->trimValueOf('menuitems');
			if (!empty($menuitems)){
				$menuItemNoArray = explode(',', $menuitems);
			
				// メニューの並び順を変更
				$this->db->orderMenuItems($this->menuId, 0/*1階層目*/, true/*表示項目のみ*/, $menuItemNoArray);
			}
			$this->gCache->clearCacheByWidgetType(self::WIDGET_TYPE_MENU);		// キャッシュをクリア
			$this->gPage->updateParentWindow();// 親ウィンドウを更新
		} else if ($act == 'delete'){		// メニュー項目の削除
			$listedItem = explode(',', $request->trimValueOf('seriallist'));
			$delItems = array();
			for ($i = 0; $i < count($listedItem); $i++){
				// 項目がチェックされているかを取得
				$itemName = 'item' . $i . '_selected';
				$itemValue = ($request->trimValueOf($itemName) == 'on') ? 1 : 0;
				
				if ($itemValue){		// チェック項目
					$delItems[] = $listedItem[$i];
				}
			}
			if (count($delItems) > 0){
				$ret = $this->db->delMenuItems(implode($delItems, ','));
				if ($ret){		// データ削除成功のとき
					//$this->setGuidanceMsg('データを削除しました');
					$this->setGuidanceMsg($this->_('Menu item deleted.'));		// データを削除しました
				} else {
					//$this->setAppErrorMsg('データ削除に失敗しました');
					$this->setAppErrorMsg($this->_('Failed in deleting menu item.'));		// データ削除に失敗しました
				}
			}
			$this->gCache->clearCacheByWidgetType(self::WIDGET_TYPE_MENU);		// キャッシュをクリア
			$this->gPage->updateParentWindow();// 親ウィンドウを更新
		}
		// 一覧の表示タイプを設定
		if ($this->isMultiLang){		// 多言語対応の場合
			$this->tmpl->setAttribute('show_multilang', 'visibility', 'visible');
		} else {
			$this->tmpl->setAttribute('show_singlelang', 'visibility', 'visible');
		}
		
		// メニューID選択メニュー作成
		$this->db->getMenuIdList(array($this, 'menuIdListLoop'));
		
		// メニュー項目一覧を作成
		$this->createMenuList($this->menuId);
		
		// メニュー項目が存在しないときはメニューのプレビューを表示しない
		if (!$this->isExistsMenuItem) $this->tmpl->setAttribute('itemlist', 'visibility', 'hidden');		// メニュー項目一覧
		if (!$this->isExistsPreviewMenuItem) $this->tmpl->setAttribute('menuitemlist', 'visibility', 'hidden');// メニュープレビュー
		if ($fixedMode == '1') $this->tmpl->addVar("_widget", "sel_menu_id_disabled", 'disabled');			// メニューIDの選択可否
		
		// コンテンツ編集用ウィジェット
		switch ($contType){
			case self::CONTENT_TYPE_PC:			// PC用
			default:
				$contentEditWidget = self::CONTENT_WIDGET_ID_PC;
				break;
			case self::CONTENT_TYPE_MOBILE:			// 携帯用
				$contentEditWidget = self::CONTENT_WIDGET_ID_MOBILE;
				break;
			case self::CONTENT_TYPE_SMARTPHONE:			// スマートフォン用
				$contentEditWidget = self::CONTENT_WIDGET_ID_SMARTPHONE;
				break;
		}
	
		$this->tmpl->addVar("_widget", "serial_list", implode($this->serialArray, ','));// 表示項目のシリアル番号を設定
		$this->tmpl->addVar('_widget', 'content_widget_id', $contentEditWidget);// コンテンツ表示ウィジェット
		$this->tmpl->addVar('_widget', 'admin_url', $this->getUrl($this->gEnv->getDefaultAdminUrl()));// 管理者URL
		$this->tmpl->addVar("_widget", "fixed_mode", $fixedMode);		// メニュー定義IDが固定かどうか
	}
	/**
	 * 詳細画面作成
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param								なし
	 */
	function createDetail($request)
	{
		$userId		= $this->gEnv->getCurrentUserId();
		$langId	= $this->gEnv->getCurrentLanguage();		// 表示言語を取得
		
		$fixedMode = $request->trimValueOf('fixed_mode');		// メニュー定義IDが固定かどうか
		$act = $request->trimValueOf('act');
		$this->serialNo = $request->trimValueOf('serial');		// 選択項目のシリアル番号
		$value = $request->trimValueOf('item_sel_menuid');
		if ($value != '') $this->serialNo = $value;			// メニュー項目が選択されている場合はシリアル番号を更新
		$this->menuId = $request->trimValueOf('menuid');		// 現在選択中のメニューID
		
		$name = $request->trimValueOf('item_name');
		$desc = $request->trimValueOf('item_desc');
		$this->menuItemType = $request->trimValueOf('item_type');
		if ($this->menuItemType == '') $this->menuItemType = '0';		// デフォルトの項目タイプは通常リンク
		$linkType = $request->trimValueOf('item_link_type');
		$visible = ($request->trimValueOf('item_visible') == 'on') ? 1 : 0;		// メニュー項目表示制御チェックボックス
		$linkContent = ($request->trimValueOf('item_link_content') == 'on') ? 1 : 0;		// コンテンツにリンクしてメニュー項目を表示制御するかどうか
		$this->currentPageSubId = $request->trimValueOf('item_sub_id');			// ページサブID
		$this->contentId = $request->trimValueOf('item_content_id');			// コンテンツID
		$url = $request->trimValueOf('item_url');		// 入力URL		
		$url = str_replace($this->gEnv->getRootUrl(), M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $url);// マクロ変換
		
		// 多言語入力を取得
		if ($this->isMultiLang){		// 多言語対応の場合
			$nameLangArray = array();		// 多言語対応文字列
			for ($i = 0; $i < count($this->availableLangRows); $i++){
				$inputLangId = $this->availableLangRows[$i]['ln_id'];

				$itemName = self::ITEM_NAME_HEAD . $inputLangId;
				$itemValue = $request->trimValueOf($itemName);
				if (!empty($itemValue)) $nameLangArray[$inputLangId] = $itemValue;
			}
			// デフォルト言語を追加
			if (!empty($name)) $nameLangArray[$this->gEnv->getDefaultLanguage()] = $name;
		}
		
		$this->menuDeviceType = 0;	// メニューの端末タイプ
		$ret = $this->db->getMenuId($this->menuId, $row);
		if ($ret) $this->menuDeviceType = $row['mn_device_type'];
		
		// コンテンツタイプを取得
		switch ($this->menuDeviceType){
			case 0:			// PC用
			default:
				$contType = self::CONTENT_TYPE_PC;			// 汎用コンテンツのコンテンツタイプ
				break;
			case 1:			// 携帯用
				$contType = self::CONTENT_TYPE_MOBILE;			// 汎用コンテンツのコンテンツタイプ
				break;
			case 2:			// スマートフォン用
				$contType = self::CONTENT_TYPE_SMARTPHONE;			// 汎用コンテンツのコンテンツタイプ
				break;
		}

		$reloadData = false;		// データの再読み込み
		if ($act == 'add'){// 新規追加
			// 入力チェック
			$this->checkInput($name, $this->_('Name'));		// 名前
			$this->checkInput($this->menuId, $this->_('Menu ID'));	// メニューID
			//$this->checkInput($url, 'リンク先');

			// エラーなしの場合は、データを登録
			if ($this->getMsgCount() == 0){
				// リンク先でメニューの表示制御を行う場合は、リンク先コンテンツタイプ、IDを取得
				$linkContentType = '';		// コンテンツタイプ
				$linkContentId = '';		// コンテンツID
				if (!empty($linkContent)){
					list($tmp, $queryStr) = explode('?', $url, 2);
					if (!empty($queryStr)){
						parse_str($queryStr, $params);
						if (isset($params[M3_REQUEST_PARAM_CONTENT_ID]) || isset($params[M3_REQUEST_PARAM_CONTENT_ID_SHORT])){
							$linkContentType = M3_VIEW_TYPE_CONTENT;		// 汎用コンテンツ
							$linkContentId = isset($params[M3_REQUEST_PARAM_CONTENT_ID]) ? $params[M3_REQUEST_PARAM_CONTENT_ID] : $params[M3_REQUEST_PARAM_CONTENT_ID_SHORT];
						}
					}
				}
				
				// ##### 格納用の名前を作成 #####
				if ($this->isMultiLang){		// 多言語対応の場合
					$nameLangStr = $this->serializeLangArray($nameLangArray);
				} else {
					$nameLangStr = $name;
				}
				$ret = $this->db->addMenuItem($this->menuId, 0, $nameLangStr, $desc, 0/*項目順は自動設定*/, $this->menuItemType, $linkType, $url, $visible, $newId, $linkContentType, $linkContentId);
				if ($ret){
					//$this->setGuidanceMsg('データを追加しました');
					$this->setGuidanceMsg($this->_('Menu item added.'));	// データを追加しました
					
					$this->serialNo = $newId;		// メニュー項目IDを更新
					$reloadData = true;		// データの再読み込み
				} else {
					//$this->setAppErrorMsg('データ追加に失敗しました');
					$this->setAppErrorMsg($this->_('Failed in adding menu item.'));	// データ追加に失敗しました
				}
				$this->gCache->clearCacheByWidgetType(self::WIDGET_TYPE_MENU);		// キャッシュをクリア
				$this->gPage->updateParentWindow();// 親ウィンドウを更新
			}
		} else if ($act == 'update'){		// 項目更新の場合
			// 入力チェック
			$this->checkInput($name, $this->_('Name'));		// 名前
			//$this->checkInput($url, 'リンク先');

			// エラーなしの場合は、データを更新
			if ($this->getMsgCount() == 0){
				// リンク先でメニューの表示制御を行う場合は、リンク先コンテンツタイプ、IDを取得
				$linkContentType = '';		// コンテンツタイプ
				$linkContentId = '';		// コンテンツID
				if (!empty($linkContent)){
					list($tmp, $queryStr) = explode('?', $url, 2);
					if (!empty($queryStr)){
						parse_str($queryStr, $params);
						if (isset($params[M3_REQUEST_PARAM_CONTENT_ID]) || isset($params[M3_REQUEST_PARAM_CONTENT_ID_SHORT])){
							$linkContentType = M3_VIEW_TYPE_CONTENT;		// 汎用コンテンツ
							$linkContentId = isset($params[M3_REQUEST_PARAM_CONTENT_ID]) ? $params[M3_REQUEST_PARAM_CONTENT_ID] : $params[M3_REQUEST_PARAM_CONTENT_ID_SHORT];
						}
					}
				}
				
				// ##### 格納用の名前を作成 #####
				if ($this->isMultiLang){		// 多言語対応の場合
					$nameLangStr = $this->serializeLangArray($nameLangArray);
				} else {
					$nameLangStr = $name;
				}
				$ret = $this->db->updateMenuItem($this->serialNo, $nameLangStr, $desc, $this->menuItemType, $linkType, $url, $visible, $linkContentType, $linkContentId);
				if ($ret){
					//$this->setGuidanceMsg('データを更新しました');
					$this->setGuidanceMsg($this->_('Menu item updated.'));		// データを更新しました
					
					$reloadData = true;		// データの再読み込み
				} else {
					//$this->setAppErrorMsg('データ更新に失敗しました');
					$this->setAppErrorMsg($this->_('Failed in updating menu item.'));		// データ更新に失敗しました
				}
				$this->gCache->clearCacheByWidgetType(self::WIDGET_TYPE_MENU);		// キャッシュをクリア
				$this->gPage->updateParentWindow();// 親ウィンドウを更新
			}
		} else if ($act == 'delete'){		// 項目削除の場合
			$ret = $this->db->delMenuItems($this->serialNo);
			if ($ret){		// データ削除成功のとき
				//$this->setGuidanceMsg('データを削除しました');
				$this->setGuidanceMsg($this->_('Menu item deleted.'));		// データを削除しました
			} else {
				//$this->setAppErrorMsg('データ削除に失敗しました');
				$this->setAppErrorMsg($this->_('Failed in deleting menu item.'));		// データ削除に失敗しました
			}
			$this->gCache->clearCacheByWidgetType(self::WIDGET_TYPE_MENU);		// キャッシュをクリア
			$this->gPage->updateParentWindow();// 親ウィンドウを更新
		} else if ($act == 'select'){	// ページサブIDを変更
			if ($this->currentPageSubId == '_root'){
				$url = M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END . '/';
			} else if ($this->currentPageSubId == '_other'){		// 任意設定以外のとき
				$url = '';
			} else {
				$url = $this->gEnv->getDefaultUrl() . '?' . M3_REQUEST_PARAM_PAGE_SUB_ID . '=' . $this->currentPageSubId;
				if (!empty($this->contentId)) $url .= '&' . M3_REQUEST_PARAM_CONTENT_ID . '=' . $this->contentId;
			}
		} else if ($act == 'getmenu'){		// コンテンツ選択メニュー取得
			// コンテンツIDを取得
			$contentSerial = $request->trimValueOf('content_serial');
			$ret = $this->db->getContentBySerial($contentSerial, $row);
			if ($ret) $this->contentId = $row['cn_id'];
			
			// コンテンツ選択メニューを作成
			$this->menuHtml  = '<select name="item_content_id" onchange="selectPage();">';
	        $this->menuHtml .= '<option value="">-- 未選択 --</option>';
			$this->db->getAllContents($langId, $contType, array($this, 'contentListLoop'));
			$this->menuHtml .= '</select>';
			$this->gInstance->getAjaxManager()->addData('menu_html', $this->menuHtml);
		} else {
			$reloadData = true;		// データの再読み込み
		}
		if ($reloadData){		// データの再読み込み
			if (empty($this->serialNo)){		// 新規項目追加のとき
				$name = '';		// 名前
				$linkType = 0;	// リンクタイプ
				$visible = 1;
				$linkContent = 0;		// コンテンツにリンクしてメニュー項目を表示制御するかどうか
				$url = '';	// リンク先
			} else {
				$ret = $this->db->getMenuItem($this->serialNo, $row);
				if ($ret){
					// 取得値を設定
					$this->serialNo = $row['md_id'];			// ID
					$name = $this->getDefaultLangString($row['md_name']);		// 名前
					$desc = $row['md_description'];		// 説明
					$this->menuItemType = $row['md_type'];		// 項目タイプ
					$linkType = $row['md_link_type'];	// リンクタイプ
					$visible = $row['md_visible'];
					$url = $row['md_link_url'];	// リンク先
					
					// リンク先を解析
					if ($url == M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END . '/'){		// トップのとき
						$this->currentPageSubId = '_root';
					} else {
						// システム以下へのリンクかチェック
						$testUrl = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $this->gEnv->getRootUrl(), $url);		// マクロ展開
						$ret = $this->gEnv->parseUrl($testUrl, $pageId, $pageSubId, $paramArray);
						if (!$ret) $this->currentPageSubId = '_other';		// 他サイトへのリンクのとき
					}
					// コンテンツにリンクしてメニュー項目を表示制御するかどうか
					$linkContent = 0;
					if (!empty($row['md_content_type'])) $linkContent = 1;			// リンクコンテンツが設定されている場合はメニューの表示制御を行う
					
					// ##### 多言語対応用データ作成 #####
					if ($this->isMultiLang){		// 多言語対応の場合
						$nameLangArray = $this->unserializeLangArray($row['md_name']);
					}
				}
			}
		}
		
		// リンク先を実URLに変換
		$url = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $this->gEnv->getRootUrl(), $url);		// マクロ展開

		$contentType = '';		// コンテンツタイプ(ページ属性)
		if ($this->currentPageSubId == '_root'){
			$this->tmpl->setAttribute('input_no_url', 'visibility', 'visible');// URL非表示データ
			$this->tmpl->addVar('_widget', 'root_selected', 'selected');		// ページサブID選択
		} else if ($this->currentPageSubId == '_other'){
			$this->tmpl->setAttribute('input_url', 'visibility', 'visible');// URL入力エリア表示
			$this->tmpl->addVar('_widget', 'other_selected', 'selected');		// ページサブID選択
		} else {		// ルート、任意設定以外のとき
			$this->tmpl->setAttribute('input_no_url', 'visibility', 'visible');// URL非表示データ

			// URLを解析
			$ret = $this->gEnv->parseUrl($url, $pageId, $pageSubId, $paramArray);
			if ($ret){
				$this->currentPageSubId = $pageSubId;

				// ページ情報を取得
				$ret = $this->db->getPageInfo($this->gEnv->getDefaultPageId(), $this->currentPageSubId, $row);
				if ($ret){
					$contentType = $row['pn_content_type'];
				}

				// メニューの端末タイプごとのアクセスポイントを取得
				switch ($this->menuDeviceType){
					case 0:			// PC用
					default:
						$url = $this->gEnv->getDefaultUrl();
						break;
					case 1:			// 携帯用
						$url = $this->gEnv->getDefaultMobileUrl();
						break;
					case 2:			// スマートフォン用
						$url = $this->gEnv->getDefaultSmartphoneUrl();
						break;
				}
				
				// 表示データタイプごとの処理
				switch ($contentType){
					case M3_VIEW_TYPE_CONTENT:				// 汎用コンテンツ
						$this->contentId = $paramArray[M3_REQUEST_PARAM_CONTENT_ID];
						$url .= '?' . M3_REQUEST_PARAM_CONTENT_ID . '=' . $this->contentId;
						break;
					case M3_VIEW_TYPE_PRODUCT:				// 製品
					case M3_VIEW_TYPE_BBS:					// BBS
					case M3_VIEW_TYPE_BLOG:				// ブログ
					case M3_VIEW_TYPE_WIKI:				// wiki
					case M3_VIEW_TYPE_USER:				// ユーザ作成コンテンツ
					default:
						$url .= '?' . M3_REQUEST_PARAM_PAGE_SUB_ID . '=' . $this->currentPageSubId;
						break;
				}
			}
		}
		
		// 表示データタイプごとの表示処理
		switch ($contentType){
			case M3_VIEW_TYPE_CONTENT:				// 汎用コンテンツ
				$this->tmpl->setAttribute('sel_content', 'visibility', 'visible');// コンテンツ選択メニュー表示
				$this->db->getAllContents($langId, $contType, array($this, 'contentListLoop'));
				break;
			case M3_VIEW_TYPE_PRODUCT:				// 製品
				break;
			case M3_VIEW_TYPE_BBS:					// BBS
				break;
			case M3_VIEW_TYPE_BLOG:				// ブログ
				break;
			case M3_VIEW_TYPE_WIKI:				// wiki
				break;
			case M3_VIEW_TYPE_USER:				// ユーザ作成コンテンツ
				break;
			default:
				break;
		}
		
		// リンク先設定用メニュー
		//$this->db->getPageIdList(array($this, 'pageSubIdLoop'), 1);// ウィジェットサブIDメニュー作成
		switch ($this->menuDeviceType){
			case 0:			// PC用
			default:
				$defaultPageId = $this->gEnv->getDefaultPageId();
				break;
			case 1:			// 携帯用
				$defaultPageId = $this->gEnv->getDefaultMobilePageId();
				break;
			case 2:			// スマートフォン用
				$defaultPageId = $this->gEnv->getDefaultSmartphonePageId();
				break;
		}
		$this->db->getPageSubIdList($defaultPageId, ''/*言語なし*/, array($this, 'pageSubIdLoop'));

		// メニュー項目タイプメニュー
		$this->createItemTypeMenu();
		
		// メニューID選択メニュー作成
		$this->db->getAllMenuItems($this->menuId, array($this, 'menuIdLoop'));
		
		// コンテンツ編集用ウィジェット
		switch ($contType){
			case self::CONTENT_TYPE_PC:			// PC用
			default:
				$contentEditWidget = self::CONTENT_WIDGET_ID_PC;
				break;
			case self::CONTENT_TYPE_MOBILE:			// 携帯用
				$contentEditWidget = self::CONTENT_WIDGET_ID_MOBILE;
				break;
			case self::CONTENT_TYPE_SMARTPHONE:			// スマートフォン用
				$contentEditWidget = self::CONTENT_WIDGET_ID_SMARTPHONE;
				break;
		}
		
		// 多言語用入力エリア作成
		if ($this->isMultiLang){		// 多言語対応の場合
			// デフォルト言語設定
			$this->tmpl->addVar("_widget", "lang", $this->createLangImage($this->gEnv->getDefaultLanguage()));
			
			// その他の入力欄作成
			$this->tmpl->setAttribute('input_lang', 'visibility', 'visible');
			$this->createInputLangText($nameLangArray);
		}

		// ### 入力値を再設定 ###
		$this->tmpl->addVar("_widget", "sel_item_name", $name);		// 名前
		$this->tmpl->addVar("_widget", "desc", $desc);		// 説明
		$this->tmpl->addVar("_widget", "sel_url", $url);		// 表示するURL
		$this->tmpl->addVar("input_url", "sel_url", $url);		// 表示するURL
		$this->tmpl->addVar("input_no_url", "sel_url", $url);		// 表示するURL
		$attrStr = '';
		if (!empty($contentType)) $attrStr = $this->_('Page Attribute:') . ' ' . $contentType;		// ページ属性：
		$this->tmpl->addVar("_widget", "attr", $attrStr);		// ページ属性
		$this->tmpl->addVar('_widget', 'content_widget_id', $contentEditWidget);// コンテンツ表示ウィジェット
		// リンクタイプ
		switch ($linkType){
			case 0:			// 同ウィンドウで開くリンク
				$this->tmpl->addVar("_widget", "link_type_0", 'selected');
				break;
			case 1:			// 別ウィンドウで開くリンク
				$this->tmpl->addVar("_widget", "link_type_1", 'selected');
				break;
		}
		// 項目表示、項目利用可否チェックボックス
		$visibleStr = '';
		if ($visible) $visibleStr = 'checked';
		$this->tmpl->addVar("_widget", "sel_item_visible", $visibleStr);
		$checked = '';
		if (!empty($linkContent)) $checked = 'checked';
		$this->tmpl->addVar("_widget", "link_content", $checked);
		
		// 選択中のシリアル番号、IDを設定
		$this->tmpl->addVar("_widget", "serial", $this->serialNo);
		$this->tmpl->addVar("_widget", "menu_id", $this->menuId);		// メニューID
		$this->tmpl->addVar("_widget", "fixed_mode", $fixedMode);		// メニュー定義IDが固定かどうか
		
		// ボタンの表示制御
		if (empty($this->serialNo)){		// 新規追加項目を選択しているとき
			$this->tmpl->setAttribute('add_button', 'visibility', 'visible');// 「新規追加」ボタン
		} else {
			$this->tmpl->setAttribute('del_button', 'visibility', 'visible');// 「削除」ボタン
		}
	}
	/**
	 * メニュー一覧作成
	 *
	 * @param string	$menuId		メニューID
	 * @return string		ツリーメニュータグ
	 */
	function createMenuList($menuId)
	{
		if (!$this->db->getChildMenuItems($menuId, 0, $rows)) return;
		
		$itemCount = count($rows);
		for ($i = 0; $i < $itemCount; $i++){
			$row = $rows[$i];
			
			// デフォルト言語で名前を取得
			$name = $this->getDefaultLangString($row['md_name']);
			
			// 対応言語を取得
			$lang = '';
			if ($this->isMultiLang){		// 多言語対応の場合
				$lang = $this->createLangImageList($row['md_name']);
			}
			// メニュー項目表示状態
			$visible = '';
			if ($row['md_visible']){
				$visible = 'checked';
			}
			// リンクタイプ
			$linkString = '';
			switch ($row['md_link_type']){
				case 0:			// 同ウィンドウで開くリンク
					$linkString = $this->_('Self');		// 同
					break;
				case 1:			// 別ウィンドウで開くリンク
					$linkString = $this->_('Other');	// 別
					break;
			}
			// 項目選択のラジオボタンの状態
			$serial = $this->convertToDispString($row['md_id']);
			$selected = '';
			if ($serial == $this->serialNo){
				$selected = 'checked';
			}
	
			// リンクURLからコンテンツIDを取得
			$linkUrl = $row['md_link_url'];
			// システム配下のパスであるかチェック
			$contentId = '';
			$pos = strpos($linkUrl, M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END);
			if ($pos === false){		// 見つからない場合
			} else {
				// コンテンツID取得
				list($path, $param) = explode("?", $linkUrl);
				$params = explode("&", $param);
				$count = count($params);
				for ($j = 0; $j < $count; $j++){
					list($key, $value) = explode('=', $params[$j]);
					if ($key == M3_REQUEST_PARAM_CONTENT_ID){
						$contentId = $value;
						break;
					}
				}
			}
			// リンク先を実URLに変換
			$linkUrlStr = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, '...', $linkUrl);		// 表示テキスト
			$linkUrl = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $this->gEnv->getRootUrl(), $linkUrl);		// マクロ展開
			$linkUrlStr = '<a href="#" onclick="showUrl(\'' . $linkUrl . '\');">' . $this->convertToDispString($linkUrlStr) . '</a>';

			// コンテンツの編集ボタンの有効状態
			$enableContentLink = '';
			if (empty($contentId)) $enableContentLink = 'disabled';
	
			$itemRow = array(
				'index' => $i,													// 行番号
				'serial' => $serial,			// シリアル番号
				'id' => $this->convertToDispString($row['md_id']),			// ID
				'name' => $this->convertToDispString($name),		// 名前
				'lang'		=> $lang,			// 言語
				'item_type' => $iconTag,											// メニュー項目タイプアイコン
				'link_type' => $linkString,											// リンクタイプ
				'link_str' => $linkUrlStr,											// リンクURL
				'content_id' => $contentId,											// コンテンツID
				'enable_content' => $enableContentLink,											// コンテンツの編集ボタンの有効状態
				'visible' => $visible,											// メニュー項目表示制御
				'selected' => $selected,												// 項目選択用ラジオボタン
				'label_edit_content' => $this->_('Edit Content')				// コンテンツを編集
			);
//			$this->tmpl->addVars('itemlist', $itemRow);
//			$this->tmpl->parseTemplate('itemlist', 'a');
			if ($this->isMultiLang){		// 多言語対応のとき
				$this->tmpl->addVars('itemlist2', $itemRow);
				$this->tmpl->parseTemplate('itemlist2', 'a');
			} else {
				$this->tmpl->addVars('itemlist', $itemRow);
				$this->tmpl->parseTemplate('itemlist', 'a');
			}
		
			// メニューのプレビュー
			if ($row['md_visible']){		// 表示項目のみ追加
				$this->tmpl->addVars('menuitemlist', $itemRow);
				$this->tmpl->parseTemplate('menuitemlist', 'a');
				$this->isExistsPreviewMenuItem = true;		// メニュー項目が存在するかどうか
			}
			$this->isExistsMenuItem = true;		// メニュー項目が存在するかどうか
	
			// シリアル番号を保存
			$this->serialArray[] = $serial;
		}
	}
	/**
	 * ページID、取得したデータをテンプレートに設定する
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function pageIdLoop($index, $fetchedRow, $param)
	{
		$selected = '';
		$row = array(
			'value'    => $this->convertToDispString($fetchedRow['pg_id']),			// ページID
			'name'     => $this->convertToDispString($fetchedRow['pg_name']),			// ページ名
			'selected' => $selected														// 選択中かどうか
		);
		$this->tmpl->addVars('main_id_list', $row);
		$this->tmpl->parseTemplate('main_id_list', 'a');
		return true;
	}
	/**
	 * ページサブID、取得したデータをテンプレートに設定する
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function pageSubIdLoop($index, $fetchedRow, $param)
	{
		$selected = '';
		if ($fetchedRow['pg_id'] == $this->currentPageSubId) $selected = 'selected';	// 現在のページサブID
		
		$contentType = $fetchedRow['pn_content_type'];
		$name = $fetchedRow['pg_id'];
		if (!empty($contentType)) $name .= '[' . $contentType . ']';
		$name .= ' - ' . $fetchedRow['pg_name'];

		$row = array(
			'value'    => $this->convertToDispString($fetchedRow['pg_id']),			// ページID
			'name'     => $this->convertToDispString($name),			// ページ名
			'selected' => $selected														// 選択中かどうか
		);
		$this->tmpl->addVars('sub_id_list', $row);
		$this->tmpl->parseTemplate('sub_id_list', 'a');
		return true;
	}
	/**
	 * 取得したデータをテンプレートに設定する
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function contentListLoop($index, $fetchedRow, $param)
	{
		$id = $fetchedRow['cn_id'];
		$selected = '';
		if ($id == $this->contentId) $selected = 'selected';	// 現在のコンテンツID
		$row = array(
			'value'    => $this->convertToDispString($id),								// コンテンツID
			'name'     => $this->convertToDispString($fetchedRow['cn_name']),			// コンテンツ名
			'selected' => $selected														// 選択中かどうか
		);
		$this->tmpl->addVars('content_list', $row);
		$this->tmpl->parseTemplate('content_list', 'a');
		
		// コンテンツ選択メニューHTML
		$this->menuHtml .= '<option value="' . $id . '" ' . $selected . '>' . $this->convertToDispString($fetchedRow['cn_name']) . '</option>';
		return true;
	}
	/**
	 * 取得したデータをテンプレートに設定する
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function menuIdListLoop($index, $fetchedRow, $param)
	{
		$value = $fetchedRow['mn_id'];
		$name = $value . ' - ' . $fetchedRow['mn_name'];

		$selected = '';
		if ($value == $this->menuId) $selected = 'selected';
		
		$row = array(
			'value'    => $this->convertToDispString($value),			// ページID
			'name'     => $this->convertToDispString($name),			// ページ名
			'selected' => $selected														// 選択中かどうか
		);
		$this->tmpl->addVars('menu_id_list', $row);
		$this->tmpl->parseTemplate('menu_id_list', 'a');
		return true;
	}
	/**
	 * メニュー項目タイプ選択メニュー作成
	 *
	 * @return なし
	 */
	function createItemTypeMenu()
	{
		for ($i = 0; $i < count($this->itemTypeArray); $i++){
			$value = $this->itemTypeArray[$i]['value'];
			$name = $this->itemTypeArray[$i]['name'];
			
			$selected = '';
			if ($value == $this->menuItemType) $selected = 'selected';
			
			$row = array(
				'value'    => $value,			// ページID
				'name'     => $name,			// ページ名
				'selected' => $selected														// 選択中かどうか
			);
			$this->tmpl->addVars('item_type_list', $row);
			$this->tmpl->parseTemplate('item_type_list', 'a');
		}
	}
	/**
	 * メニュー項目をテンプレートに設定する
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function menuIdLoop($index, $fetchedRow, $param)
	{
		$value = $this->convertToDispString($fetchedRow['md_id']);
		$name = $this->getDefaultLangString($fetchedRow['md_name']);
		
		$selected = '';
		if ($value == $this->serialNo) $selected = 'selected';
			
		$row = array(
			'value'    => $value,			// メニュー項目ID
			'name'     => $this->convertToDispString($name),			// メニュー項目名
			'selected' => $selected														// 選択中かどうか
		);
		$this->tmpl->addVars('menuid_list', $row);
		$this->tmpl->parseTemplate('menuid_list', 'a');
		return true;
	}
	/**
	 * 言語アイコンを作成
	 *
	 * @param string $src		多言語対応文字列
	 * @return string			言語アイコンタグ
	 */
	function createLangImageList($src)
	{
		$imageTag = '';
		$langArray = $this->unserializeLangArray($src);
		$keys = array_keys($langArray);
		
		for ($i = 0; $i < count($this->availableLangRows); $i++){
			$langId = $this->availableLangRows[$i]['ln_id'];
			if (in_array($langId, $keys)) $imageTag .= $this->createLangImage($langId);
		}
		return $imageTag;
	}
	/**
	 * 言語アイコンを作成
	 *
	 * @param string $id		言語ID
	 * @return string			言語アイコンタグ
	 */
	function createLangImage($id)
	{
		$langInfo = $this->availableLangArray[$id];
		if (!isset($langInfo)) return '';
		
		if ($this->gEnv->getCurrentLanguage() == 'ja'){	// 日本語の場合
			$langName = $langInfo['ln_name'];
		} else {
			$langName = $langInfo['ln_name_en'];
		}
		// 言語アイコン
		$iconTitle = $langName;
		$iconUrl = $this->gEnv->getRootUrl() . self::LANG_ICON_PATH . $langInfo['ln_image_filename'];		// 画像ファイル
		$iconTag = '<img src="' . $this->getUrl($iconUrl) . '" border="0" alt="' . $iconTitle . '" title="' . $iconTitle . '" />';
		$imageTag .= $iconTag;
		return $imageTag;
	}
	/**
	 * 多言語文字列入力エリア作成
	 *
	 * @param array $nameArray	多言語対応文字列
	 * @return なし
	 */
	function createInputLangText($nameArray)
	{
		for ($i = 0; $i < count($this->availableLangRows); $i++){
			$langId = $this->availableLangRows[$i]['ln_id'];
			if ($langId == $this->gEnv->getDefaultLanguage()) continue;		// デフォルト言語は除く
			$langImage = $this->createLangImage($langId);
			
			$value = $nameArray[$langId];
			$row = array(
				'id'    => $langId,			// 言語ID
				'value'	=> $this->convertToDispString($value),			// 入力値
				'lang'	=> $langImage		// 言語画像
			);
			$this->tmpl->addVars('input_lang', $row);
			$this->tmpl->parseTemplate('input_lang', 'a');
		}
	}
}
?>
