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
 * @copyright  Copyright 2006-2010 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: admin_default_menuWidgetContainer.php 3906 2010-12-22 12:10:54Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseAdminWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/default_menuDb.php');

class admin_default_menuWidgetContainer extends BaseAdminWidgetContainer
{
	private $db;	// DB接続オブジェクト
	private $sysDb;	// DB接続オブジェクト
	private $serialNo;		// 選択中の項目のシリアル番号
	private $serialArray = array();			// 表示中のシリアル番号
	private $langId;
	private $configId;		// 定義ID
	private $paramObj;		// パラメータ保存用オブジェクト
	private $menuId;		// メニューID
	const DEFAULT_NAME_HEAD = '名称未設定';			// デフォルトの設定名
	const DEFAULT_MENU_ID = 'main_menu';			// デフォルトメニューID
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->db = new default_menuDb();
		$this->sysDb = $this->gInstance->getSytemDbObject();
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
	 * @param								なし
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
		$userId		= $this->gEnv->getCurrentUserId();
		$this->langId	= $this->gEnv->getCurrentLanguage();		// 表示言語を取得
		$act = $request->trimValueOf('act');
		$this->serialNo = $request->trimValueOf('serial');		// 選択項目のシリアル番号
		
		// ページ定義IDとページ定義のレコードシリアル番号
		$defConfigId = $this->getPageDefConfigId($request);				// 定義ID取得
		$defSerial = $this->getPageDefSerial($request);		// ページ定義のレコードシリアル番号
		$value = $request->trimValueOf('defconfig');		// ページ定義の定義ID
		if (!empty($value)) $defConfigId = $value;
		$value = $request->trimValueOf('defserial');		// ページ定義のレコードシリアル番号
		if (!empty($value)) $defSerial = $value;

		$this->configId = $request->trimValueOf('item_id');		// 定義ID
		if (empty($this->configId)) $this->configId = $defConfigId;		// 呼び出しウィンドウから引き継いだ定義ID
		$name	= $request->trimValueOf('item_name');			// ヘッダタイトル
		$isHierMenu = ($request->trimValueOf('is_hier') == 'on') ? 1 : 0;		// 階層化メニューを使用するかどうか
		$limitUser = ($request->trimValueOf('item_limituser') == 'on') ? 1 : 0;		// ユーザを制限するかどうか
		$useVerticalMenu = ($request->trimValueOf('item_vertical_menu') == 'on') ? 1 : 0;		// 縦型メニューデザインを使用するかどうか
		$this->menuId = $request->trimValueOf('menuid');
		if ($this->menuId == '') $this->menuId = self::DEFAULT_MENU_ID;

		// パラメータオブジェクトを取得
		$this->paramObj = $this->getWidgetParamObjectWithId();
		
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
				$newObj->isHierMenu = $isHierMenu;		// 階層化メニューを使用するかどうか
				$newObj->limitUser = $limitUser;					// ユーザを制限するかどうか
				$newObj->useVerticalMenu = $useVerticalMenu;		// 縦型メニューデザインを使用するかどうか
				
				$newParam = new stdClass;
				$newParam->id = -1;		// 新規追加
				$newParam->object = $newObj;
			
				// ウィジェットパラメータオブジェクト更新
				$ret = $this->updateWidgetParamObjectWithId($newParam);
				if ($ret) $this->paramObj[] = $newParam;		// 新規定義を追加
				
				// 画面定義更新
				if ($ret && !empty($defSerial)){		// 画面作成から呼ばれている場合のみ更新
					$newConfigId = $newParam->id;
					$ret = $this->sysDb->updateWidgetConfigId($this->gEnv->getCurrentWidgetId(), $defSerial, $newConfigId, $name, $this->menuId);
				}
				
				if ($ret){
					$this->setGuidanceMsg('データを追加しました');
					
					$defConfigId = $newConfigId;		// 定義定義IDを更新
					$this->configId = $newConfigId;		// 定義定義IDを更新
					$replaceNew = true;			// データ再取得
				} else {
					$this->setAppErrorMsg('データ追加に失敗しました');
				}
				$this->gPage->updateParentWindow($defSerial);// 親ウィンドウを更新
			}
		} else if ($act == 'update'){		// 設定更新のとき
			// 入力値のエラーチェック
			
			if ($this->getMsgCount() == 0){			// エラーのないとき
				// 該当項目を更新
				$ret = false;
				for ($i = 0; $i < count($this->paramObj); $i++){
					$id			= $this->paramObj[$i]->id;// 定義ID
					$targetObj	= $this->paramObj[$i]->object;
					if ($id == $this->configId){
						// ウィジェットオブジェクト更新。更新値のみ再設定。
						if (!empty($defConfigId) && !empty($defSerial)){		// 設定再選択不可の場合
							// 取得値で更新
							$this->menuId = $targetObj->menuId;		// メニューID
						} else {			// 新規で既存設定の更新
							$targetObj->menuId	= $this->menuId;		// メニューID
							$targetObj->isHierMenu = $isHierMenu;		// 階層化メニューを使用するかどうか
						}
						$targetObj->limitUser = $limitUser;					// ユーザを制限するかどうか
						$targetObj->useVerticalMenu = $useVerticalMenu;		// 縦型メニューデザインを使用するかどうか
						
						// ウィジェットパラメータオブジェクトを更新
						$ret = $this->updateWidgetParamObjectWithId($this->paramObj[$i]);
						break;
					}
				}
				// 画面定義更新
				if (!empty($defSerial)){		// 画面作成から呼ばれている場合のみ更新
					if ($ret) $ret = $this->sysDb->updateWidgetConfigId($this->gEnv->getCurrentWidgetId(), $defSerial, $this->configId, $targetObj->name, $this->menuId);
				}

				if ($ret){
					$this->setMsg(self::MSG_GUIDANCE, 'データを更新しました');
					
					if (empty($defConfigId)){		// 画面定義の定義IDが設定されていないときは設定
						$defConfigId = $this->configId;		// 定義定義IDを更新
					}
					$replaceNew = true;			// データ再取得
				} else {
					$this->setMsg(self::MSG_APP_ERR, 'データ更新に失敗しました');
				}
				$this->gPage->updateParentWindow($defSerial);// 親ウィンドウを更新
			}
		} else if ($act == 'select'){	// 定義IDを変更
			$replaceNew = true;			// データ再取得
		}
		
		// 表示用データを取得
		if (empty($this->configId)){		// 新規登録の場合
			$this->tmpl->setAttribute('item_name_visible', 'visibility', 'visible');// 名前入力フィールド表示
			$name = $this->createDefaultName();			// デフォルト登録項目名
			$$isHierMenu = 0;		// 階層化メニューを使用するかどうか
			$limitUser = 0;					// ユーザを制限するかどうか
			$useVerticalMenu = 0;		// 縦型メニューデザインを使用するかどうか
			$this->serialNo = 0;
		} else {
			// 定義からウィジェットパラメータを検索して、定義データを取得
			for ($i = 0; $i < count($this->paramObj); $i++){
				$id			= $this->paramObj[$i]->id;// 定義ID
				$targetObj	= $this->paramObj[$i]->object;
				if ($id == $this->configId) break;
			}
			if ($i < count($this->paramObj)){		// 該当項目があるとき
				// ウィジェットオブジェクトから値を取得
				if ($replaceNew){		// データ再取得のとき
					$this->menuId	= $targetObj->menuId;		// メニューID
					$name			= $targetObj->name;// 名前
					$isHierMenu		= $targetObj->isHierMenu;		// 階層化メニューを使用するかどうか
					$limitUser		= $targetObj->limitUser;					// ユーザを制限するかどうか
					$useVerticalMenu = $targetObj->useVerticalMenu;		// 縦型メニューデザインを使用するかどうか
				}
				$this->serialNo = $this->configId;
				
				// 新規作成でないときは、メニューを変更不可にする(画面作成から呼ばれている場合のみ)
				if (!empty($defConfigId) && !empty($defSerial)){
					$this->tmpl->addVar("_widget", "id_disabled", 'disabled');
					$this->tmpl->addVar("_widget", "is_hier_disabled", 'disabled');	// ユーザを制限するかどうかを使用不可
				}
			}
		}
		// 設定項目選択メニュー作成
		$this->createItemMenu();
		
		// メニューID選択メニュー作成
		$this->db->getMenuIdList(array($this, 'menuIdListLoop'));
		
		// 画面にデータを埋め込む
		$this->tmpl->addVar("item_name_visible", "name", $name);		// 名前
		if (!empty($this->configId)) $this->tmpl->addVar("_widget", "id", $this->configId);		// 定義ID
		
		$checked = '';
		if ($isHierMenu) $checked = 'checked';
		$this->tmpl->addVar("_widget", "is_hier", $checked);	// 階層化メニューを使用するかどうか
		$checked = '';
		if ($limitUser) $checked = 'checked';
		$this->tmpl->addVar("_widget", "limit_user", $checked);	// ユーザを制限するかどうか
		$checked = '';
		if ($useVerticalMenu) $checked = 'checked';
		$this->tmpl->addVar("_widget", "vertical_menu", $checked);		// 縦型メニューデザインを使用するかどうか
		
		$this->tmpl->addVar("_widget", "serial", $this->serialNo);// 選択中のシリアル番号、IDを設定
		
		// 画面定義用の情報を戻す
		$this->tmpl->addVar("_widget", "def_serial", $defSerial);	// ページ定義のレコードシリアル番号
		$this->tmpl->addVar("_widget", "def_config", $defConfigId);	// ページ定義の定義ID
		
		// ボタンの表示制御
		if (empty($this->serialNo)){		// 新規追加項目を選択しているとき
			$this->tmpl->setAttribute('add_button', 'visibility', 'visible');// 「新規追加」ボタン
		} else {
			$this->tmpl->setAttribute('update_button', 'visibility', 'visible');// 「更新」ボタン
		}
		// タブの選択状態を設定
		// 一度設定を保存している場合は、メニュー定義を前面にする(初期起動時のみ)
		if (empty($act) && !empty($this->configId)) $this->tmpl->setAttribute('select_menu_def', 'visibility', 'visible');
	}
	/**
	 * 選択用メニューを作成
	 *
	 * @return なし						
	 */
	function createItemMenu()
	{
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
	 * デフォルトの名前を取得
	 *
	 * @return string	デフォルト名						
	 */
	function createDefaultName()
	{
		$name = self::DEFAULT_NAME_HEAD;
		for ($j = 1; $j < 100; $j++){
			$name = self::DEFAULT_NAME_HEAD . $j;
			// 設定名の重複チェック
			for ($i = 0; $i < count($this->paramObj); $i++){
				$targetObj = $this->paramObj[$i]->object;
				if ($name == $targetObj->name){		// 定義名
					break;
				}
			}
			// 重複なしのときは終了
			if ($i == count($this->paramObj)) break;
		}
		return $name;
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
		$userId		= $this->gEnv->getCurrentUserId();
		$langId	= $this->gEnv->getCurrentLanguage();		// 表示言語を取得
		$act = $request->trimValueOf('act');
		
		// ページ定義IDとページ定義のレコードシリアル番号
		$defConfigId = $this->getPageDefConfigId($request);				// 定義ID取得
		$defSerial = $this->getPageDefSerial($request);		// ページ定義のレコードシリアル番号
		$value = $request->trimValueOf('defconfig');		// ページ定義の定義ID
		if (!empty($value)) $defConfigId = $value;
		$value = $request->trimValueOf('defserial');		// ページ定義のレコードシリアル番号
		if (!empty($value)) $defSerial = $value;

		// 詳細画面からの引継ぎデータ
		$menuId = $request->trimValueOf('menuid');
		$isHierMenu = ($request->trimValueOf('is_hier') == 'on') ? 1 : 0;		// 階層化メニューを使用するかどうか
		
		// パラメータオブジェクトを取得
		$this->paramObj = $this->getWidgetParamObjectWithId();
		
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
				for ($i = 0; $i < count($this->paramObj); $i++){
					$id			= $this->paramObj[$i]->id;// 定義ID
					if (in_array($id, $delItems)){		// 削除対象のとき
						$newParam = new stdClass;
						$newParam->id = $id;
						$newParam->object = null;		// 削除処理
						$ret = $this->updateWidgetParamObjectWithId($newParam);
						if (!$ret) break;
					}
				}
				
				// ウィジェットパラメータオブジェクト更新
				if ($ret){		// データ削除成功のとき
					$this->setGuidanceMsg('データを削除しました');
				} else {
					$this->setAppErrorMsg('データ削除に失敗しました');
				}
				// パラメータオブジェクトを取得
				$this->paramObj = $this->getWidgetParamObjectWithId();
			}
		}
		// 定義一覧作成
		$this->createItemList();
		
		// メニュー定義画面のURLを作成
		$taskValue = 'menudef';
		if (empty($isHierMenu)) $taskValue = 'smenudef';
		$menuDefUrl = $this->gEnv->getDefaultAdminUrl() . '?' . 'task=' . $taskValue . '&openby=tabs&menuid=' . $menuId;
		$this->tmpl->addVar("_widget", "url", $this->getUrl($menuDefUrl));
		$this->tmpl->addVar("_widget", "menu_id", $menuId);
		if ($isHierMenu) $checked = 'on';
		$this->tmpl->addVar("_widget", "is_hier", $checked);	// 階層化メニューを使用するかどうか
					
		$this->tmpl->addVar("_widget", "serial_list", implode($this->serialArray, ','));// 表示項目のシリアル番号を設定
		
		// 画面定義用の情報を戻す
		$this->tmpl->addVar("_widget", "def_serial", $defSerial);	// ページ定義のレコードシリアル番号
		$this->tmpl->addVar("_widget", "def_config", $defConfigId);	// ページ定義の定義ID
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
				$defCount = $this->sysDb->getPageDefCount($this->gEnv->getCurrentWidgetId(), $id);
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
}
?>
