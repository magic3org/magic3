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
 * @copyright  Copyright 2006-2021 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseAdminWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/default_menuDb.php');

class admin_default_menuWidgetContainer extends BaseAdminWidgetContainer
{
	private $db;	// DB接続オブジェクト
	private $serialNo;		// 選択中の項目のシリアル番号
	private $serialArray = array();			// 表示中のシリアル番号
	private $langId;
	private $configId;		// 定義ID
	private $paramObj;		// パラメータ保存用オブジェクト
	private $menuId;		// メニューID
	const DEFAULT_NAME_HEAD = '名称未設定';			// デフォルトの設定名
	const DEFAULT_MENU_ID = 'main_menu';			// デフォルトメニューID
	const DEFAULT_PLACEHOLDER = 'キーワード検索';			// デフォルトの検索フィールドプレースホルダー文字列
//	const SEL_MENU_ID = 'admin_menu';		// メニュー変換対象メニューバーID
//	const TREE_MENU_TASK	= 'menudef';	// メニュー管理画面(多階層)
//	const SINGLE_MENU_TASK	= 'smenudef';	// メニュー管理画面(単階層)
	// 画面
	const TASK_LIST = 'list';			// 設定一覧
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->db = new default_menuDb();
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
		if ($task == 'list'){		// 一覧画面
			return 'admin_list.tmpl.html';
		} else {			// 一覧画面
			return 'admin.tmpl.html';
		}
	}
	/**
	 * テンプレートにデータ埋め込む
	 *
	 * _setTemplate()で指定したテンプレートファイルにデータを埋め込む。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。_setTemplate()と共有。
	 * @return								なし
	 */
	function _postAssign($request, &$param)
	{
		// 表示画面を決定
		$task = $request->trimValueOf(M3_REQUEST_PARAM_OPERATION_TASK);

		// パンくずリストの作成
		// ダミーで作成。タイトルはJavascript側で設定。
		$titles = array();
		$titles[] = '設定なし';
		$this->gPage->setAdminBreadcrumbDef($titles);
		
		// メニューバーの作成
		$navbarDef = new stdClass;
		$navbarDef->title = $this->gEnv->getCurrentWidgetTitle();		// ウィジェット名
		$navbarDef->baseurl = $this->getAdminUrlWithOptionParam();
		$navbarDef->help	= $this->_createWidgetInfoHelp();		// ウィジェットの説明用ヘルプ// ヘルプ文字列
		$navbarDef->menu =	array(
								(Object)array(
									'name'		=> 'メニュー定義',	// メニュー定義
									'task'		=> '',
									'url'		=> '',
									'tagid'		=> 'menubar_other',
									'active'	=> false,
									'submenu'	=> array()
								),
								(Object)array(
									'name'		=> '基本',		// 基本
									'task'		=> '',
									'url'		=> '',
									'tagid'		=> 'menubar_basic',
									'active'	=> (
//														$task == '' ||						// 基本設定
														$task == self::TASK_LIST			// 設定一覧
													),
									'submenu'	=> array()
								)
							);
		$this->gPage->setAdminSubNavbarDef($navbarDef);
	}
	/**
	 * テンプレートにデータ埋め込む
	 *
	 * _setTemplate()で指定したテンプレートファイルにデータを埋め込む。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。_setTemplate()と共有。
	 * @return								なし
	 */
	function _assign($request, &$param)
	{
		$task = $request->trimValueOf('task');
		if ($task == 'list'){		// 一覧画面
			$this->createList($request);
		} else {			// 詳細設定画面
			$this->createDetail($request);
		}
	}
	/**
	 * 詳細画面作成
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param								なし
	 */
	function createDetail($request)
	{
		// ページ定義IDとページ定義のレコードシリアル番号を取得
		$this->startPageDefParam($defSerial, $defConfigId, $this->paramObj);
		
		$userId		= $this->gEnv->getCurrentUserId();
		$this->langId	= $this->gEnv->getCurrentLanguage();		// 表示言語を取得
		$anchor = $request->trimValueOf('anchor');
		$act = $request->trimValueOf('act');
		$this->serialNo = $request->trimValueOf('serial');		// 選択項目のシリアル番号

		$this->configId = $request->trimValueOf('item_id');		// 定義ID
		if (empty($this->configId)) $this->configId = $defConfigId;		// 呼び出しウィンドウから引き継いだ定義ID
		$this->menuId = $request->trimValueOf('menuid');
		if (empty($this->menuId)) $this->menuId = self::DEFAULT_MENU_ID;
		$name	= $request->trimValueOf('item_name');			// ヘッダタイトル
		$isHierMenu			= $request->trimCheckedValueOf('is_hier');		// 階層化メニューを使用するかどうか
		$limitUser			= $request->trimCheckedValueOf('item_limituser');		// ユーザを制限するかどうか
		$useVerticalMenu	= $request->trimCheckedValueOf('item_vertical_menu');		// 縦型メニューデザインを使用するかどうか
		$showSitename		= $request->trimCheckedValueOf('item_show_sitename');		// サイト名を表示するかどうか
		$showSearch			= $request->trimCheckedValueOf('item_show_search');			// 検索フィールドを表示するかどうか
		$showLogin			= $request->trimCheckedValueOf('item_show_login');			// ログインを表示するかどうか
		$showRegist			= $request->trimCheckedValueOf('item_show_regist');			// 会員登録を表示するかどうか
		$anotherColor		= $request->trimCheckedValueOf('item_another_color');		// 色を変更するかどうか
		$placeholder		= $request->trimValueOf('item_placeholder');				// 検索フィールドプレースホルダー文字列
		
		$replaceNew = false;		// データを再取得するかどうか
		if (empty($act)){// 初期起動時
			// デフォルト値設定
			$this->configId = $defConfigId;		// 呼び出しウィンドウから引き継いだ定義ID
			$replaceNew = true;			// データ再取得
		} else if ($act == 'add'){// 新規追加
			// 入力チェック
			$this->checkInput($name, '名前');
			
			// エラーなしの場合は、データを登録
			if ($this->getMsgCount() == 0){
				// 追加オブジェクト作成
				$newObj = new stdClass;
				$newObj->menuId	= $this->menuId;		// メニューID
				$newObj->name	= $name;// 表示名
//				$newObj->isHierMenu = $isHierMenu;		// 階層化メニューを使用するかどうか
				$newObj->limitUser = $limitUser;					// ユーザを制限するかどうか
				$newObj->useVerticalMenu	= $useVerticalMenu;		// 縦型メニューデザインを使用するかどうか
				$newObj->showSitename		= $showSitename;		// サイト名を表示するかどうか
				$newObj->showSearch			= $showSearch;			// 検索フィールドを表示するかどうか
				$newObj->showLogin			= $showLogin;			// ログインを表示するかどうか
				$newObj->showRegist			= $showRegist;			// 会員登録を表示するかどうか
				$newObj->anotherColor		= $anotherColor;		// 色を変更するかどうか
				$newObj->placeholder		= $placeholder;			// 検索フィールドプレースホルダー文字列
		
				$ret = $this->addPageDefParam($defSerial, $defConfigId, $this->paramObj, $newObj, $this->menuId);
				if ($ret){
					$this->setGuidanceMsg('データを追加しました');
					
					$this->configId = $defConfigId;		// 定義定義IDを更新
					$replaceNew = true;			// データ再取得
					
					// メニュー管理画面を変更
					$ret = $this->gSystem->changeSiteMenuHier($isHierMenu);
/*					$ret = $this->getMenuInfo($dummy, $itemId, $row);// メニュー情報を取得
					if ($ret){
						// メニュー管理画面を変更
						if ($isHierMenu){		// 多階層の場合
							$ret = $this->db->updateNavItemMenuType($itemId, self::TREE_MENU_TASK);
						} else {
							$ret = $this->db->updateNavItemMenuType($itemId, self::SINGLE_MENU_TASK);
						}
					}*/
				} else {
					$this->setAppErrorMsg('データ追加に失敗しました');
				}
			}
		} else if ($act == 'update'){		// 設定更新のとき
			// 入力値のエラーチェック
			
			if ($this->getMsgCount() == 0){			// エラーのないとき
				// 現在の設定値を取得
				$ret = $this->getPageDefParam($defSerial, $defConfigId, $this->paramObj, $this->configId, $targetObj);
				if ($ret){
					// ウィジェットオブジェクト更新。更新値のみ再設定。
					if (!empty($defConfigId) && !empty($defSerial)){		// 設定再選択不可の場合
						// 取得値で更新
						$this->menuId = $targetObj->menuId;		// メニューID
					} else {			// 新規で既存設定の更新
						$targetObj->menuId	= $this->menuId;		// メニューID
					}
//					$targetObj->isHierMenu = $isHierMenu;		// 階層化メニューを使用するかどうか
					$targetObj->limitUser = $limitUser;					// ユーザを制限するかどうか
					$targetObj->useVerticalMenu 	= $useVerticalMenu;		// 縦型メニューデザインを使用するかどうか
					$targetObj->showSitename		= $showSitename;		// サイト名を表示するかどうか
					$targetObj->showSearch			= $showSearch;			// 検索フィールドを表示するかどうか
					$targetObj->showLogin			= $showLogin;			// ログインを表示するかどうか
					$targetObj->showRegist			= $showRegist;			// 会員登録を表示するかどうか
					$targetObj->anotherColor		= $anotherColor;		// 色を変更するかどうか
					$targetObj->placeholder			= $placeholder;			// 検索フィールドプレースホルダー文字列
				}

				// 設定値を更新
				if ($ret) $ret = $this->updatePageDefParam($defSerial, $defConfigId, $this->paramObj, $this->configId, $targetObj, $this->menuId);
				if ($ret){
					$this->setMsg(self::MSG_GUIDANCE, 'データを更新しました');
					
					$replaceNew = true;			// データ再取得
					
					// メニュー管理画面を変更
					$ret = $this->gSystem->changeSiteMenuHier($isHierMenu);
/*					$ret = $this->getMenuInfo($dummy, $itemId, $row);// メニュー情報を取得
					if ($ret){
						// メニュー管理画面を変更
						if ($isHierMenu){		// 多階層の場合
							$ret = $this->db->updateNavItemMenuType($itemId, self::TREE_MENU_TASK);
						} else {
							$ret = $this->db->updateNavItemMenuType($itemId, self::SINGLE_MENU_TASK);
						}
					}*/
				} else {
					$this->setMsg(self::MSG_APP_ERR, 'データ更新に失敗しました');
				}
			}
		} else if ($act == 'select'){	// 定義IDを変更
			$replaceNew = true;			// データ再取得
		}
		
		// 表示用データを取得
		if (empty($this->configId)){		// 新規登録の場合
			$this->tmpl->setAttribute('item_name_visible', 'visibility', 'visible');// 名前入力フィールド表示
			
			if ($replaceNew){		// データ再取得時
				$this->menuId = self::DEFAULT_MENU_ID;
				//$name = $this->createDefaultName();			// デフォルト登録項目名
				$name = $this->createConfigDefaultName();			// デフォルト登録項目名
//				$isHierMenu = 0;		// 階層化メニューを使用するかどうか
				$limitUser = 0;					// ユーザを制限するかどうか
				$useVerticalMenu = 0;		// 縦型メニューデザインを使用するかどうか
				$showSitename		= 1;		// サイト名を表示するかどうか
				$showSearch			= 0;			// 検索フィールドを表示するかどうか
				$showLogin			= 0;			// ログインを表示するかどうか
				$showRegist			= 0;			// 会員登録を表示するかどうか
				$anotherColor		= 0;		// 色を変更するかどうか
				$placeholder		= self::DEFAULT_PLACEHOLDER;			// 検索フィールドプレースホルダー文字列
			}
			$this->serialNo = 0;
		} else {
			if ($replaceNew){
				$ret = $this->getPageDefParam($defSerial, $defConfigId, $this->paramObj, $this->configId, $targetObj);
				if ($ret){
					$this->menuId	= $targetObj->menuId;		// メニューID
					$name			= $targetObj->name;// 名前
//					$isHierMenu		= $targetObj->isHierMenu;		// 階層化メニューを使用するかどうか
					$limitUser		= $targetObj->limitUser;					// ユーザを制限するかどうか
					$useVerticalMenu 	= $targetObj->useVerticalMenu;		// 縦型メニューデザインを使用するかどうか
//					$showSitename		= $targetObj->showSitename;		// サイト名を表示するかどうか
//					$showSearch			= $targetObj->showSearch;			// 検索フィールドを表示するかどうか					
					$showSitename	= isset($targetObj->showSitename) ? $targetObj->showSitename : 1;		// サイト名を表示するかどうか
					$showSearch		= isset($targetObj->showSearch) ? $targetObj->showSearch : 0;			// 検索フィールドを表示するかどうか
					$showLogin		= isset($targetObj->showLogin) ? $targetObj->showLogin : 0;			// ログインを表示するかどうか
					$showRegist		= isset($targetObj->showRegist) ? $targetObj->showRegist : 0;			// 会員登録を表示するかどうか
					$anotherColor	= isset($targetObj->anotherColor) ? $targetObj->anotherColor : 0;		// 色を変更するかどうか
					$placeholder	= $targetObj->placeholder;			// 検索フィールドプレースホルダー文字列
				}
			}
			$this->serialNo = $this->configId;
				
			// 新規作成でないときは、メニューを変更不可にする(画面作成から呼ばれている場合のみ)
			if (!empty($defConfigId) && !empty($defSerial)){
				$this->tmpl->addVar("_widget", "id_disabled", 'disabled');
				$this->tmpl->addVar("_widget", "menu_id_disabled", 'readonly');		// 値は送信する必要あり
			}
		}
		
		// 階層化メニューを使用するかどうか取得
		//$this->getMenuInfo($isHierMenu, $itemId, $row);
		$isHierMenu = $this->gSystem->isSiteMenuHier();
		
		// 設定項目選択メニュー作成
		$this->createItemMenu();
		
		// メニューID選択メニュー作成
		$this->db->getMenuIdList(0/*PC用*/, array($this, 'menuIdListLoop'));
		
		// 一度設定を保存している場合は、メニュー定義を前面にする(初期起動時のみ)
		$activeIndex = 0;
		if (empty($act) && !empty($this->configId)) $activeIndex = 1;
		// 一覧画面からの戻り画面が指定されてる場合は優先する
		if ($anchor == 'widget_config') $activeIndex = 0;
		
		if (empty($activeIndex)){		// タブの選択
			$this->tmpl->addVar("_widget", "active_tab", 'widget_config');
		} else {
			$this->tmpl->addVar("_widget", "active_tab", 'menu_define');
		}
		
		// 画面にデータを埋め込む
		$this->tmpl->addVar("item_name_visible", "name", $name);		// 名前
		if (!empty($this->configId)) $this->tmpl->addVar("_widget", "id", $this->configId);		// 定義ID
		
		$this->tmpl->addVar("_widget", "is_hier", $this->convertToCheckedString($isHierMenu));	// 階層化メニューを使用するかどうか
		$this->tmpl->addVar("_widget", "limit_user", $this->convertToCheckedString($limitUser));	// ユーザを制限するかどうか
		$this->tmpl->addVar("_widget", "vertical_menu", $this->convertToCheckedString($useVerticalMenu));		// 縦型メニューデザインを使用するかどうか
		$this->tmpl->addVar("_widget", "show_sitename_checked", $this->convertToCheckedString($showSitename));		// サイト名を表示するかどうか
		$this->tmpl->addVar("_widget", "show_search_checked", $this->convertToCheckedString($showSearch));			// 検索フィールドを表示するかどうか
		$this->tmpl->addVar("_widget", "show_login_checked", $this->convertToCheckedString($showLogin));			// ログインを表示するかどうか
		$this->tmpl->addVar("_widget", "show_regist_checked", $this->convertToCheckedString($showRegist));			// 会員登録を表示するかどうか
		$this->tmpl->addVar("_widget", "another_color_checked", $this->convertToCheckedString($anotherColor));		// 色を変更するかどうか
		$this->tmpl->addVar("_widget", "placeholder", $this->convertToDispString($placeholder));			// 検索フィールドプレースホルダー文字列
					
		$this->tmpl->addVar("_widget", "serial", $this->serialNo);// 選択中のシリアル番号、IDを設定
		
		// ボタンの表示制御
		if (empty($this->serialNo)){		// 新規追加項目を選択しているとき
			$this->tmpl->setAttribute('add_button', 'visibility', 'visible');// 「新規追加」ボタン
		} else {
			$this->tmpl->setAttribute('update_button', 'visibility', 'visible');// 「更新」ボタン
		}
		
		// ページ定義IDとページ定義のレコードシリアル番号を更新
		$this->endPageDefParam($defSerial, $defConfigId, $this->paramObj);
	}
	/**
	 * 選択用メニューを作成
	 *
	 * @return なし						
	 */
	function createItemMenu()
	{
		if (!is_array($this->paramObj)) return;
		
		for ($i = 0; $i < count($this->paramObj); $i++){
			$id = $this->paramObj[$i]->id;// 定義ID
			$targetObj = $this->paramObj[$i]->object;
			$name = $targetObj->name;// 定義名
			$selected = '';
			if ($this->configId == $id) $selected = 'selected';

			$row = array(
				'name' => $name,		// 名前
				'value' => $id,		// 定義ID
				'selected' => $selected	// 選択中の項目かどうか
			);
			$this->tmpl->addVars('title_list', $row);
			$this->tmpl->parseTemplate('title_list', 'a');
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
		$name = $fetchedRow['mn_name'] . '(' . $value . ')';
			
		$selected = '';
		if ($value == $this->menuId) $selected = 'selected';
		
		$row = array(
			'value'    => $value,			// ページID
			'name'     => $name,			// ページ名
			'selected' => $selected														// 選択中かどうか
		);
		$this->tmpl->addVars('menu_id_list', $row);
		$this->tmpl->parseTemplate('menu_id_list', 'a');
		return true;
	}
	/**
	 * 一覧画面作成
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param								なし
	 */
	function createList($request)
	{
		// ページ定義IDとページ定義のレコードシリアル番号を取得
		$this->startPageDefParam($defSerial, $defConfigId, $this->paramObj);
		
		$userId		= $this->gEnv->getCurrentUserId();
		$langId	= $this->gEnv->getCurrentLanguage();		// 表示言語を取得
		$act = $request->trimValueOf('act');

		// 詳細画面からの引継ぎデータ
		$menuId = $request->trimValueOf('menuid');
//		$isHierMenu = ($request->trimValueOf('is_hier') == 'on') ? 1 : 0;		// 階層化メニューを使用するかどうか

		// 階層化メニューを使用するかどうか取得
		//$this->getMenuInfo($isHierMenu, $itemId, $row);
		$isHierMenu = $this->gSystem->isSiteMenuHier();
		
		if ($act == 'delete'){		// メニュー項目の削除
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
				$ret = $this->delPageDefParam($defSerial, $defConfigId, $this->paramObj, $delItems);
				if ($ret){		// データ削除成功のとき
					$this->setGuidanceMsg('データを削除しました');
				} else {
					$this->setAppErrorMsg('データ削除に失敗しました');
				}
			}
		}
		// 定義一覧作成
		$this->createItemList();
		if (count($this->serialArray) <= 0) $this->tmpl->setAttribute('itemlist', 'visibility', 'hidden');// 一覧非表示
		
		// メニュー定義画面のURLを作成
		$taskValue = 'menudef';
		if (empty($isHierMenu)) $taskValue = 'smenudef';
		$menuDefUrl = $this->gEnv->getDefaultAdminUrl() . '?' . 'task=' . $taskValue . '&openby=tabs&menuid=' . $menuId;
		$this->tmpl->addVar("_widget", "url", $this->getUrl($menuDefUrl));
		$this->tmpl->addVar("_widget", "menu_id", $menuId);
		if ($isHierMenu) $checked = 'on';
		$this->tmpl->addVar("_widget", "is_hier", $checked);	// 階層化メニューを使用するかどうか
					
		$this->tmpl->addVar("_widget", "serial_list", implode(',', $this->serialArray));// 表示項目のシリアル番号を設定
		
		// ページ定義IDとページ定義のレコードシリアル番号を更新
		$this->endPageDefParam($defSerial, $defConfigId, $this->paramObj);
	}
	/**
	 * 定義一覧作成
	 *
	 * @return なし						
	 */
	function createItemList()
	{
		for ($i = 0; $i < count($this->paramObj); $i++){
			$id			= $this->paramObj[$i]->id;// 定義ID
			$targetObj	= $this->paramObj[$i]->object;
			$name = $targetObj->name;// 定義名
			
			// メニュー定義名を取得
			$menuName = '';
			if ($this->db->getMenu($targetObj->menuId, $row)){
				$menuName = $row['mn_name'] . '(' . $row['mn_id'] . ')';
			}
			
			$defCount = 0;
			if (!empty($id)){
				$defCount = $this->_db->getPageDefCount($this->gEnv->getCurrentWidgetId(), $id);
			}
			$operationDisagled = '';
			if ($defCount > 0) $operationDisagled = 'disabled';
			$row = array(
				'index' => $i,
				'id' => $id,
				'ope_disabled' => $operationDisagled,			// 選択可能かどうか
				'name' => $this->convertToDispString($name),		// 名前
				'menu_name' => $this->convertToDispString($menuName),		// メニュー定義名
				'def_count' => $defCount							// 使用数
			);
			$this->tmpl->addVars('itemlist', $row);
			$this->tmpl->parseTemplate('itemlist', 'a');
			
			// シリアル番号を保存
			$this->serialArray[] = $id;
		}
	}
	/**
	 * メニュー管理画面の情報を取得
	 *
	 * @param bool  $isHier		階層化メニューかどうか
	 * @param int   $itemId		メニュー項目ID
	 * @param array  $row		取得レコード
	 * @return bool				取得できたかどうか
	 */
/*	function getMenuInfo(&$isHier, &$itemId, &$row)
	{
		$isHier = false;	// 多階層メニューかどうか
		$ret = $this->db->getNavItemsByTask(self::SEL_MENU_ID, self::TREE_MENU_TASK, $row);
		if ($ret){
			$isHier = true;
		} else {
			$ret = $this->db->getNavItemsByTask(self::SEL_MENU_ID, self::SINGLE_MENU_TASK, $row);
		}
		if ($ret) $itemId = $row['ni_id'];
		return $ret;
	}*/
}
?>
