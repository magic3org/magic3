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
 * @version    SVN: $Id: admin_ec_menuMenudefWidgetContainer.php 5562 2013-01-18 03:49:58Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/admin_ec_menuBaseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/ec_menuDb.php');

class admin_ec_menuMenudefWidgetContainer extends admin_ec_menuBaseWidgetContainer
{
	private $db;	// DB接続オブジェクト
	private $userId;
	private $langId;		// 表示言語を取得
	private $serialNo;		// 選択中の項目のシリアル番号(メニュー項目IDを使用)
	private $serialArray = array();			// 表示中のシリアル番号
	private $isExistsMenuItem;		// メニュー項目が存在するかどうか
	private $menuId;		// 現在選択中のメニューID
	private $menuItemType;	// メニュー項目タイプ
	private $menuDeviceType;	// メニューの端末タイプ
	private $itemTypeArray;		// メニュー項目の種類
	private $isMultiLang;			// 多言語対応画面かどうか
	private $availableLangRows;	// 利用可能な言語
	private $availableLangArray;	// 利用可能な言語
	private $categoryListData;		// 商品カテゴリー
	private $categoryArray;			// カテゴリー設定値
	private $catagorySelectCount;	// カテゴリー選択可能数
	private $parentId;				// メニュー親項目
	private $parentIdArray = array();			// メニュー項目パス
	private $parentNameArray = array();			// メニュー項目名
	const SEARCH_WIDGET = 'ec_disp';			// 商品表示用ウィジェット
	const DEFAULT_MENU_ID = 'ec_menu';			// デフォルトメニューID
	const MAX_MENU_TREE_LEVEL = 5;			// メニュー階層最大数
	const FILE_ICON_FILE = '/images/system/file.png';			// ファイルアイコン
	const FOLDER_ICON_FILE = '/images/system/folder.png';		// フォルダアイコン
	const ICON_SIZE = 16;		// アイコンのサイズ
	const WIDGET_TYPE_MENU = 'menu';		// メニュー型のウィジェット(キャッシュクリア用)
	const HEAD_CONTENT_TYPE = 'ec_menu';			// ヘッダ用コンテンツ
//	const CONTENT_TYPE_PC = '';			// 汎用コンテンツのコンテンツタイプ(PC用)
//	const CONTENT_TYPE_MOBILE = 'mobile';			// 汎用コンテンツのコンテンツタイプ(携帯用)
//	const CONTENT_TYPE_SMARTPHONE = 'smartphone';			// 汎用コンテンツのコンテンツタイプ(スマートフォン用)
	const LANG_ICON_PATH = '/images/system/flag/';		// 言語アイコンパス
	const ITEM_NAME_HEAD = 'item_name_';				// 多言語対応名前ヘッダ
	const PARAM_CONTENT_ID = 'pcontent';	// コンテンツIDキー
	const PARAM_CATEGORY_ID = 'category';	// カテゴリーIDキー
	const DEFAULT_CATEGORY_COUNT = 2;	// デフォルトの商品カテゴリー選択可能数
	const TREE_ITEM_HEAD = 'treeitem_';		// ツリー項目IDヘッダ
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->db = new ec_menuDb();

		// メニュー項目タイプ
		$this->itemTypeArray = array(	array(	'name' => $this->_('Folder'),		'value' => '1'),	// フォルダ
										array(	'name' => $this->_('Separator'),	'value' => '3'));	// セパレータ

		$this->userId	= $this->gEnv->getCurrentUserId();
		$this->langId	= $this->gEnv->getCurrentLanguage();		// 表示言語を取得
		
		// ウィジェットパラメータ取得
		$paramObj = $this->getWidgetParamObj();
		if (!empty($paramObj)){
			$this->catagorySelectCount		= $paramObj->categoryCount;		// 商品カテゴリー選択可能数
		}
		if ($this->catagorySelectCount <= 0) $this->catagorySelectCount = self::DEFAULT_CATEGORY_COUNT;
		
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
		if ($task == 'menudef_detail'){		// 詳細画面
			return 'admin_menudef_detail.tmpl.html';
		} else {			// 一覧画面
			return 'admin_menudef.tmpl.html';
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
		
		if ($task == 'menudef_detail'){		// 詳細画面
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
			$localeText['label_menu_item_list'] = $this->_('Menu Definition');					// メニュー定義
			$localeText['label_path'] = $this->_('Path:');					// 階層パス
			$localeText['label_new'] = $this->_('New');					// 新規
			$localeText['label_edit'] = $this->_('Edit');				// 編集
			$localeText['label_delete'] = $this->_('Delete');			// 削除
			$localeText['label_check'] = $this->_('Select');			// 選択
			$localeText['label_name'] = $this->_('Name');			// 名前
			$localeText['label_lang'] = $this->_('Language');			// 言語
			$localeText['label_url'] = $this->_('URL');			// リンク先URL
			$localeText['label_link_type'] = $this->_('Link Type');			// 表示方法
			$localeText['label_visible'] = $this->_('Visible');			// 公開
			$localeText['label_operation'] = $this->_('Operation');		// 操作
			$localeText['label_menu_layout'] = $this->_('Menu Layout');		// メニューレイアウト
			$localeText['msg_change_item_order'] = $this->_('Menu items can be sorted by mouse drag and drop.');		// マウスドラッグで項目の表示順を変更できます
			$localeText['label_menu_top'] = $this->_('Menu Top');					// メニュートップ
			$localeText['label_open_all'] = $this->_('Open All');					// すべて開く
			$localeText['label_close_all'] = $this->_('Close All');				// すべて閉じる
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
		$fixedMode = $request->trimValueOf('fixed_mode');		// メニュー定義IDが固定かどうか
		if ($fixedMode == ''){		// 値が設定されていないときは設定
			if ($request->trimValueOf('menuid') == ''){
				$fixedMode = '0';
			} else {
				$fixedMode = '1';		// 固定
			}
		}
//		$this->menuId = $request->trimValueOf('menuid');		// 現在選択中のメニュータイプ
//		if ($this->menuId == '') $this->menuId = self::DEFAULT_MENU_ID;		// デフォルト商品メニューID
		$this->menuId = self::DEFAULT_MENU_ID;		// デフォルト商品メニューID
		
		// メニューの端末タイプを取得
		$this->menuDeviceType = 0;	// メニューの端末タイプ
		$ret = $this->db->getMenuId($this->menuId, $row);
		if ($ret) $this->menuDeviceType = $row['mn_device_type'];
		
		$act = $request->trimValueOf('act');
		$this->parentId = $request->trimValueOf('parentid');		// 親メニューID取得
		if ($this->parentId == '') $this->parentId = '0';
		
		if ($act == 'delete'){		// メニュー項目の削除
			$listedItem = explode(',', $request->trimValueOf('seriallist'));
			$delItems = array();
			for ($i = 0; $i < count($listedItem); $i++){
				// 項目がチェックされているかを取得
				$itemName = 'item' . $i . '_selected';
				$itemValue = ($request->trimValueOf($itemName) == 'on') ? 1 : 0;
				
				if ($itemValue){		// チェック項目
					$delItems[] = $listedItem[$i];
					
					// 子カテゴリーのある項目は削除不可
					$ret = $this->db->getChildMenuItems($this->menuId, $listedItem[$i], $rows);
					if ($ret){
						$this->setAppErrorMsg($this->_('You are not allowed to delete menu item with child item.'));		// 子項目を持つメニュー項目は削除できません。
						break;
					}
				}
			}

			// エラーなしの場合は、データを削除
			if ($this->getMsgCount() == 0){
				if (count($delItems) > 0){
					$ret = $this->delMenuItems($delItems);
					if ($ret){		// データ削除成功のとき
						$this->setGuidanceMsg($this->_('Menu item deleted.'));		// データを削除しました
					} else {
						$this->setAppErrorMsg($this->_('Failed in deleting menu item.'));		// データ削除に失敗しました
					}
				}
				$this->gCache->clearCacheByWidgetType(self::WIDGET_TYPE_MENU);		// キャッシュをクリア
				$this->gPage->updateParentWindow();// 親ウィンドウを更新
			}
		} else if ($act == 'selecttree'){		// メニューツリーから選択
			$this->parentId = str_replace(self::TREE_ITEM_HEAD, '', $request->trimValueOf('treedest'));
		} else if ($act == 'updatetree'){		// メニューツリーからの更新
			$treeId = str_replace(self::TREE_ITEM_HEAD, '', $request->trimValueOf('treesrc'));
			$treeParentId = str_replace(self::TREE_ITEM_HEAD, '', $request->trimValueOf('treedest'));
			$treePos = $request->trimValueOf('treepos');
			$this->db->reorderMenuItem($this->menuId, $treeParentId, $treeId, $treePos);
			
			$this->gCache->clearCacheByWidgetType(self::WIDGET_TYPE_MENU);		// キャッシュをクリア
			$this->gPage->updateParentWindow();// 親ウィンドウを更新
		}
		
		// メニュー階層パスを取得
		if (!empty($this->parentId)){
			// 現在のメニュー項目からパスを作成
			$this->parentIdArray = $this->getPath($this->menuId, $this->parentId);
			
			// メニュー項目を選択
			$this->tmpl->setAttribute('select_tree_area', 'visibility', 'visible');
			$this->tmpl->addVar("select_tree_area", "select_id", self::TREE_ITEM_HEAD . $this->parentId);		// 選択中のメニュー項目
		}
		
		// 一覧の表示タイプを設定
		if ($this->isMultiLang){		// 多言語対応の場合
			$this->tmpl->setAttribute('show_multilang', 'visibility', 'visible');
		} else {
			$this->tmpl->setAttribute('show_singlelang', 'visibility', 'visible');
		}
		
		// メニューID選択メニュー作成
		$this->db->getMenuIdList($this->gEnv->getCurrentWidgetId(), array($this, 'menuIdListLoop'));
		
		// メニューツリー作成
		$treeMenu = $this->createTreeMenu($this->menuId, 0);
		
		// メニュー項目が存在しないときはメニューのプレビューを表示しない
		if (!$this->isExistsMenuItem) $this->tmpl->setAttribute('itemlist', 'visibility', 'hidden');		// メニュー項目一覧
		if ($fixedMode == '1') $this->tmpl->addVar("_widget", "sel_menu_id_disabled", 'disabled');			// メニューIDの選択可否
		
		// メニュー階層パスを作成
		if (empty($this->parentIdArray)){
			$path = '[ルート]';
		} else {
			$path = '<a href="#" onclick="selectTree(0);">[ルート]</a>';
		}
		for ($i = 0; $i < count($this->parentIdArray); $i++){
			$path .= '&nbsp;/&nbsp;';
			$path .= '<a href="#" onclick="selectTree(' . $this->parentIdArray[$i] . ');">' . $this->convertToDispString($this->parentNameArray[$i]) . '</a>';
		}
		
		$this->tmpl->addVar("_widget", "tree", $treeMenu);		// メニューツリー
		$this->tmpl->addVar("_widget", "parent_id", $this->parentId);		// メニュー項目親ID
		$this->tmpl->addVar("_widget", "serial_list", implode($this->serialArray, ','));// 表示項目のシリアル番号を設定
		$this->tmpl->addVar('_widget', 'admin_url', $this->getUrl($this->gEnv->getDefaultAdminUrl()));// 管理者URL
		$this->tmpl->addVar("_widget", "fixed_mode", $fixedMode);		// メニュー定義IDが固定かどうか
		$this->tmpl->addVar("_widget", "path", $path);		// メニュー階層パス
		
		// ディレクトリを設定
		$this->tmpl->addVar("_widget", "script_url", $this->getUrl($this->gEnv->getScriptsUrl()));
	}
	/**
	 * 詳細画面作成
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param								なし
	 */
	function createDetail($request)
	{
		$fixedMode = $request->trimValueOf('fixed_mode');		// メニュー定義IDが固定かどうか
		$act = $request->trimValueOf('act');
		$this->serialNo = $request->trimValueOf('serial');		// 選択項目のシリアル番号
		$value = $request->trimValueOf('item_sel_menuid');
		if ($value != '') $this->serialNo = $value;			// メニュー項目が選択されている場合はシリアル番号を更新
		$this->menuId = $request->trimValueOf('menuid');		// 現在選択中のメニューID
		$parentId = $request->trimValueOf('parentid');		// 親メニューID取得
		if ($parentId == '') $parentId = '0';
		
		$name = $request->trimValueOf('item_name');
		$title = $request->valueOf('item_title');		// タイトル(HTML可)
		$this->menuItemType = $request->trimValueOf('item_type');
		if ($this->menuItemType == '') $this->menuItemType = '0';		// デフォルトの項目タイプは通常リンク
		$linkType = $request->trimValueOf('item_link_type');
		$visible = ($request->trimValueOf('item_visible') == 'on') ? 1 : 0;		// チェックボックス
//		$url = $request->trimValueOf('item_url');		// 決定したURL
//		$url = str_replace($this->gEnv->getRootUrl(), M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $url);// マクロ変換
		$html = $request->valueOf('item_html');		// ヘッダコンテンツ
		
		// カテゴリーを取得
		$this->categoryArray = array();
		for ($i = 0; $i < $this->catagorySelectCount; $i++){
			$itemName = 'item_category' . $i;
			$itemValue = $request->trimValueOf($itemName);
			if (!empty($itemValue)){		// 0以外の値を取得
				$this->categoryArray[] = $itemValue;
			}
		}
		
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
		
		$reloadData = false;		// データの再読み込み
		if ($act == 'add'){// 新規追加
			// 入力チェック
			$this->checkInput($name, $this->_('Name'));		// 名前
			$this->checkInput($this->menuId, $this->_('Menu ID'));		// メニューID

			// エラーなしの場合は、データを登録
			if ($this->getMsgCount() == 0){
				// ##### 格納用の名前を作成 #####
				if ($this->isMultiLang){		// 多言語対応の場合
					$nameLangStr = $this->serializeLangArray($nameLangArray);
				} else {
					$nameLangStr = $name;
				}
				
				// トランザクションスタート
				$this->db->startTransaction();
				
				$ret = $this->db->updateContentItem(self::HEAD_CONTENT_TYPE, 0/*新規*/, $this->langId, $name, $html, 1/*表示*/, $newContentId, $newSerial);
				if ($ret){
					// パラメータ作成
					$param = self::PARAM_CATEGORY_ID . '=' . implode(',', $this->categoryArray) . '&' . self::PARAM_CONTENT_ID . '=' . $newContentId;
			
					$ret = $this->db->addMenuItem($this->menuId, $parentId, $nameLangStr, $title, ''/*説明*/, 0/*項目順は自動設定*/, $this->menuItemType, $linkType, $param, $visible, $newId);
				}
				
				// トランザクション終了
				$ret = $this->db->endTransaction();
				if ($ret){
					$this->setGuidanceMsg($this->_('Menu item added.'));	// データを追加しました
					$this->serialNo = $newId;		// メニュー項目IDを更新
					$reloadData = true;		// データの再読み込み
				} else {
					$this->setAppErrorMsg($this->_('Failed in adding menu item.'));	// データ追加に失敗しました
				}
				$this->gCache->clearCacheByWidgetType(self::WIDGET_TYPE_MENU);		// キャッシュをクリア
				$this->gPage->updateParentWindow();// 親ウィンドウを更新
			}
		} else if ($act == 'update'){		// 項目更新の場合
			// 入力チェック
			$this->checkInput($name, $this->_('Name'));		// 名前

			// エラーなしの場合は、データを更新
			if ($this->getMsgCount() == 0){
				// ##### 格納用の名前を作成 #####
				if ($this->isMultiLang){		// 多言語対応の場合
					$nameLangStr = $this->serializeLangArray($nameLangArray);
				} else {
					$nameLangStr = $name;
				}
				
				// トランザクションスタート
				$this->db->startTransaction();
				
				$ret = $this->db->getMenuItem($this->serialNo, $row);
				if ($ret){
					// ヘッダコンテンツIDを取得
					$param = $row['md_param'];
					$parsedParam = parseUrlParam($param);
					$contentId = $parsedParam[self::PARAM_CONTENT_ID];
					if ($contentId == '') $contentId = 0;		// コンテンツID初期化
					
					$ret = $this->db->updateContentItem(self::HEAD_CONTENT_TYPE, $contentId, $this->langId, $name, $html, 1/*表示*/, $newContentId, $newSerial);
				}
										
				// コンテンツを更新
				if ($ret){
					// パラメータ作成
					$param = self::PARAM_CATEGORY_ID . '=' . implode(',', $this->categoryArray) . '&' . self::PARAM_CONTENT_ID . '=' . $newContentId;
					
					// メニューを更新
					$ret = $this->db->updateMenuItem($this->serialNo, $nameLangStr, $title, ''/*説明*/, $this->menuItemType, $linkType, $param, $visible);
				}
				
				// トランザクション終了
				$ret = $this->db->endTransaction();
				if ($ret){
					$this->setGuidanceMsg($this->_('Menu item updated.'));		// データを更新しました
					
					$reloadData = true;		// データの再読み込み
				} else {
					$this->setAppErrorMsg($this->_('Failed in updating menu item.'));		// データ更新に失敗しました
				}
				$this->gCache->clearCacheByWidgetType(self::WIDGET_TYPE_MENU);		// キャッシュをクリア
				$this->gPage->updateParentWindow();// 親ウィンドウを更新
			}
		} else if ($act == 'delete'){		// 項目削除の場合
			//$ret = $this->db->delMenuItems($this->serialNo);
			$ret = $this->delMenuItems(array($this->serialNo));
			if ($ret){		// データ削除成功のとき
				$this->setGuidanceMsg($this->_('Menu item deleted.'));		// データを削除しました
			} else {
				$this->setAppErrorMsg($this->_('Failed in deleting menu item.'));		// データ削除に失敗しました
			}
			$this->gCache->clearCacheByWidgetType(self::WIDGET_TYPE_MENU);		// キャッシュをクリア
			$this->gPage->updateParentWindow();// 親ウィンドウを更新
		} else if ($act == 'select'){	// ページサブIDを変更
		} else {
			$reloadData = true;		// データの再読み込み
		}
		
		if ($reloadData){		// データの再読み込み
			if (empty($this->serialNo)){		// 新規項目追加のとき
				$name = '';		// 名前
				$linkType = 0;	// リンクタイプ
				$visible = 1;
			} else {
				$ret = $this->db->getMenuItem($this->serialNo, $row);
				if ($ret){
					// 取得値を設定
					$this->serialNo = $row['md_id'];			// ID
					$name = $this->getDefaultLangString($row['md_name']);		// 名前
					$title = $row['md_title'];		// タイトル(HTML可)
					$this->menuItemType = $row['md_type'];		// 項目タイプ
					$linkType = $row['md_link_type'];	// リンクタイプ
					$visible = $row['md_visible'];
					$param = $row['md_param'];
					
					// カテゴリーIDとヘッダコンテンツIDを取得
					$parsedParam = parseUrlParam($param);
					$contentId = $parsedParam[self::PARAM_CONTENT_ID];
					if (empty($parsedParam[self::PARAM_CATEGORY_ID])){
						$this->categoryArray = array();
					} else {
						$this->categoryArray = explode(',', $parsedParam[self::PARAM_CATEGORY_ID]);
					}
			
					// コンテンツを取得
					$ret = $this->db->getContentByContentId(self::HEAD_CONTENT_TYPE, $contentId, $this->langId, $row);
					if ($ret){
						$html = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $this->getUrl($this->gEnv->getRootUrl()), $row['cn_html']);// アプリケーションルートを変換
					}
					
					// ##### 多言語対応用データ作成 #####
					if ($this->isMultiLang){		// 多言語対応の場合
						$nameLangArray = $this->unserializeLangArray($row['md_name']);
					}
				}
			}
		}
		// リンク先を実URLに変換
		if (count($this->categoryArray) > 0){
			$url = $this->gPage->getDefaultPageUrlByWidget(self::SEARCH_WIDGET, $param);
		} else {
			$url = '';
		}
		
		// メニュー項目タイプメニュー
		$this->createItemTypeMenu();
		
		// メニューID選択メニュー作成
		$this->db->getAllMenuItems($this->menuId, array($this, 'menuIdLoop'));
		
		// カテゴリーメニューを作成
		$this->db->getAllCategory($this->langId, $this->categoryListData);
		$this->createCategoryMenu();
		
		// 多言語用入力エリア作成
		if ($this->isMultiLang){		// 多言語対応の場合
			// デフォルト言語設定
			$this->tmpl->addVar("_widget", "lang", $this->createLangImage($this->gEnv->getDefaultLanguage()));
			
			// その他の入力欄作成
			$this->tmpl->setAttribute('input_lang', 'visibility', 'visible');
			$this->createInputLangText($nameLangArray);
		}
		
		// ### 入力値を再設定 ###
		$this->tmpl->addVar("_widget", "name", $this->convertToDispString($name));		// 名前
		$this->tmpl->addVar("_widget", "title", $this->convertToDispString($title));		// タイトル(HTML可)
		$this->tmpl->addVar("_widget", "url", $url);		// リンク先URL
		$this->tmpl->addVar("_widget", "disp_url", $this->convertToDispString($url));		// リンク先URL
		$this->tmpl->addVar("_widget", "html", $html);		// ヘッダコンテンツ
		
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
		
		// 選択中のシリアル番号、IDを設定
		$this->tmpl->addVar("_widget", "serial", $this->serialNo);
		$this->tmpl->addVar("_widget", "menu_id", $this->menuId);		// メニューID
		$this->tmpl->addVar("_widget", "parent_id", $parentId);		// メニュー項目親ID
		$this->tmpl->addVar("_widget", "fixed_mode", $fixedMode);		// メニュー定義IDが固定かどうか
		
		// ボタンの表示制御
		if (empty($this->serialNo)){		// 新規追加項目を選択しているとき
			$this->tmpl->setAttribute('add_button', 'visibility', 'visible');// 「新規追加」ボタン
		} else {
			$this->tmpl->setAttribute('del_button', 'visibility', 'visible');// 「削除」ボタン
			
			// 子項目を持つときは削除不可
			$ret = $this->db->getChildMenuItems($this->menuId, $this->serialNo, $rows);
			if ($ret) $this->tmpl->addVar('del_button', 'del_button_disabled', 'disabled');
		}
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
	 * メニューIDをテンプレートに設定する
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
	 * メニューツリー作成
	 *
	 * @param string	$menuId		メニューID
	 * @param int		$parantId	親メニュー項目ID
	 * @param int		$level		階層数
	 * @return string		ツリーメニュータグ
	 */
	function createTreeMenu($menuId, $parantId, $level = 0)
	{
		static $index = 0;		// インデックス番号
		
		// メニューの階層を制限
		if ($level >= self::MAX_MENU_TREE_LEVEL) return '';
		
		$treeHtml = '';
		if ($this->db->getChildMenuItems($menuId, $parantId, $rows)){
			$itemCount = count($rows);
			for ($i = 0; $i < $itemCount; $i++){
				$row = $rows[$i];
				$id = $row['md_id'];
				$name = $this->getDefaultLangString($row['md_name']);// デフォルト言語で名前を取得
			
				// 対応言語を取得
				$lang = '';
				if ($this->isMultiLang){		// 多言語対応の場合
					$lang = $this->createLangImageList($row['md_name']);
				}
			
				// ##### ツリーメニュー作成 #####
				if ($row['md_type'] == 0){	// リンク項目のとき
					$treeHtml .= '<li id="' . self::TREE_ITEM_HEAD . $id . '"><a href="#">' . $this->convertToDispString($name) . '</a></li>' . M3_NL;
				} else if ($row['md_type'] == 1){			// フォルダのとき
					// サブメニュー作成
					if (in_array($id, $this->parentIdArray)){		// メニュー階層パス上にある場合はフォルダを開く
						$itemClass = 'jstree-open';
						
						$this->parentNameArray[] = $name;
					} else {
						$itemClass = 'jstree-closed';
					}
					$treeHtml .= '<li id="' . self::TREE_ITEM_HEAD . $id . '" rel="folder" class="' . $itemClass . '"><a href="#">' . $this->convertToDispString($name) . '</a>' . M3_NL;
					$treeHtml .= '<ul>' . M3_NL;
					$treeHtml .= $this->createTreeMenu($menuId, $id, $level + 1);
					$treeHtml .= '</ul>' . M3_NL;
					$treeHtml .= '</li>' . M3_NL;
				} else if ($row['md_type'] == 2){			// テキストのとき
					$treeHtml .= '<li id="' . self::TREE_ITEM_HEAD . $id . '"><a href="#">' . $this->convertToDispString($name) . '</a></li>' . M3_NL;
				} else if ($row['md_type'] == 3){			// セパレータのとき
					$treeHtml .= '<li id="' . self::TREE_ITEM_HEAD . $id . '"><a href="#">' . '-----' . '</a></li>' . M3_NL;
				}
				
				// 選択中のメニュー項目の内容のみ表示
				if ($parantId != $this->parentId) continue;
				
				// メニュー項目タイプアイコン
				$iconUrl = '';
				switch ($row['md_type']){
					case 0:		// リンク
						$iconTitle = $this->_('Link');		// リンク
						$iconUrl = $this->gEnv->getRootUrl() . self::FILE_ICON_FILE;
						break;
					case 1:		// フォルダ
						$iconTitle = $this->_('Folder');		// フォルダ
						$iconUrl = $this->gEnv->getRootUrl() . self::FOLDER_ICON_FILE;
						break;
					case 2:	// テキスト
						$iconTitle = $this->_('Text');		// テキスト
						$iconUrl = $this->gEnv->getRootUrl() . self::FILE_ICON_FILE;
						break;
					case 3:	// セパレータ
						$iconTitle = $this->_('Separator');		// セパレータ
						$iconUrl = $this->gEnv->getRootUrl() . self::FILE_ICON_FILE;
						break;
					default:
						break;
				}
				$iconTag = '<img src="' . $this->getUrl($iconUrl) . '" width="' . self::ICON_SIZE . '" height="' . self::ICON_SIZE . '" border="0" alt="' . $iconTitle . '" title="' . $iconTitle . '" />';
		
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
				// 更新用シリアル番号
				$serial = $id;
		
				// リンクURLを作成
				$parsedParam = parseUrlParam($row['md_param']);
				if (empty($parsedParam[self::PARAM_CATEGORY_ID])){
					$linkUrlStr = '';
				} else {
					$url = $this->gPage->getDefaultPageUrlByWidget(self::SEARCH_WIDGET, $row['md_param']);
					$linkUrlStr = str_replace($this->gEnv->getRootUrl(), '...', $url);		// 表示テキスト
					$linkUrlStr = '<a href="#" onclick="previewInOtherWindow(\'' . $this->getUrl($url, true) . '\');">' . $this->convertToDispString($linkUrlStr) . '</a>';
				}
				
				$itemRow = array(
					'index' => $index,													// 行番号
					'serial' => $this->convertToDispString($serial),			// シリアル番号
					'id' => $this->convertToDispString($id),			// ID
					'name' => $this->convertToDispString($name),		// 名前
					'lang'		=> $lang,			// 言語
					'item_type' => $iconTag,											// メニュー項目タイプアイコン
					'link_type' => $linkString,											// リンクタイプ
					'link_str' => $linkUrlStr,											// リンクURL
					'visible' => $visible,											// メニュー項目表示制御
					'label_edit_content' => $this->_('Edit Content')				// コンテンツを編集
				);

				if ($this->isMultiLang){		// 多言語対応のとき
					$this->tmpl->addVars('itemlist2', $itemRow);
					$this->tmpl->parseTemplate('itemlist2', 'a');
				} else {
					$this->tmpl->addVars('itemlist', $itemRow);
					$this->tmpl->parseTemplate('itemlist', 'a');
				}
			
				$index++;		// インデックス番号更新
				$this->isExistsMenuItem = true;		// メニュー項目が存在するかどうか
		
				// シリアル番号を保存
				$this->serialArray[] = $serial;
			}
		}
		return $treeHtml;
	}
	/**
	 * メニュー階層パス取得
	 *
	 * @param string $menuId		メニューID
	 * @param int    $parantId		親メニュー項目ID
	 * @param int    $level			階層数
	 * @return array				メニュー項目IDの配列
	 */
	function getPath($menuId, $parantId, $level = 0)
	{
		static $pathArray = array();
		
		// メニューの階層を制限
		if ($level >= self::MAX_MENU_TREE_LEVEL) return $pathArray;
		
		if (empty($parantId)) return $pathArray;		// メニューIDが0のときは終了
		
		// メニューパス追加
		$pathArray[] = $parantId;
		
		// メニュー項目情報を取得
		$ret = $this->db->getMenuItem($parantId, $row);
		if ($ret){
			// 親メニュー項目のパスを取得
			$this->getPath($menuId, $row['md_parent_id'], $level + 1);
			
			if ($level == 0) $pathArray = array_reverse($pathArray);		// パスを反転
			return $pathArray;
		} else {
			return $pathArray;
		}
	}
	/**
	 * メニュー項目削除
	 *
	 * @param array $idArray	削除するメニュー項目ID
	 * @param bool				true=成功、false=失敗
	 */
	function delMenuItems($idArray)
	{
		// トランザクションスタート
		$this->db->startTransaction();
		
		for ($i = 0; $i < count($idArray); $i++){
			$ret = $this->db->getMenuItem($idArray[$i], $row);
			if ($ret){
				// ヘッダコンテンツIDを取得
				$param = $row['md_param'];
				$parsedParam = parseUrlParam($param);
				$contentId = $parsedParam[self::PARAM_CONTENT_ID];
				if ($contentId == '') $contentId = 0;		// コンテンツID初期化
			}
			if ($ret) $ret = $this->db->delMenuItem($idArray[$i]);
			if ($ret){
				if (!empty($contentId)) $ret = $this->db->delContentItem(self::HEAD_CONTENT_TYPE, $contentId, $this->langId);		// コンテンツを削除
			}
			if (!$ret){			// エラーの場合は終了
				$this->db->endTransaction();
				return false;
			}
		}
		// トランザクション終了
		$ret = $this->db->endTransaction();
		return $ret;
	}
	/**
	 * 商品カテゴリーメニューを作成
	 *
	 * @return なし						
	 */
	function createCategoryMenu()
	{
		for ($j = 0; $j < $this->catagorySelectCount; $j++){
			// selectメニューの作成
			$this->tmpl->clearTemplate('category_list');
			for ($i = 0; $i < count($this->categoryListData); $i++){
				$categoryId = $this->categoryListData[$i]['pc_id'];
				$selected = '';
				if ($j < count($this->categoryArray) && $this->categoryArray[$j] == $categoryId){
					$selected = 'selected';
				}
				$menurow = array(
					'value'		=> $categoryId,			// カテゴリーID
					'name'		=> $this->categoryListData[$i]['pc_name'],			// カテゴリー名
					'selected'	=> $selected														// 選択中かどうか
				);
				$this->tmpl->addVars('category_list', $menurow);
				$this->tmpl->parseTemplate('category_list', 'a');
			}
			$itemRow = array(		
					'index'		=> $j			// 項目番号											
			);
			$this->tmpl->addVars('category', $itemRow);
			$this->tmpl->parseTemplate('category', 'a');
		}
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
