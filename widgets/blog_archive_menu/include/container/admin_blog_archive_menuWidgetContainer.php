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
 * @copyright  Copyright 2006-2014 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseAdminWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath()	. '/blog_archive_menuDb.php');

class admin_blog_archive_menuWidgetContainer extends BaseAdminWidgetContainer
{
	private $db;	// DB接続オブジェクト
	private $langId;
	private $archiveType;		// アーカイブタイプ
	private $sortOrder;		// ソート順
	private $archiveTypeArray;		// アーカイブタイプ
	private $sortOrderArray;		// ソート順
	const DEFAULT_ITEM_COUNT = 10;		// デフォルトの表示項目数
	const DEFAULT_NAME_HEAD = '名称未設定';			// デフォルトの設定名
	// 画面
	const TASK_LIST = 'list';			// 設定一覧
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DB接続オブジェクト作成
		$this->db = new blog_archive_menuDb();
		
		$this->archiveTypeArray = array(	array(	'name' => '月別',	'value' => '0'),
											array(	'name' => '年別',	'value' => '1'));		// アーカイブタイプ
		$this->sortOrderArray = array(	array(	'name' => '降順',	'value' => '0'),
										array(	'name' => '昇順',	'value' => '1'));		// ソート順
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
		if ($task == self::TASK_LIST){		// 一覧画面
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
/*		// 表示画面を決定
		$task = $request->trimValueOf(M3_REQUEST_PARAM_OPERATION_TASK);
		
		// パンくずリストの作成
		$titles = array();
		switch ($task){
			case self::TASK_LIST:			// 設定一覧
				$titles[] = '基本';
				$titles[] = '設定一覧';
				break;
			default:
				$titles[] = '基本';
				break;
		}
		$this->gPage->setAdminBreadcrumbDef($titles);
		
		// メニューバーの作成
		$navbarDef = new stdClass;
		$navbarDef->title = $this->gEnv->getCurrentWidgetTitle();		// ウィジェット名
		$navbarDef->baseurl = $this->getAdminUrlWithOptionParam();
		$navbarDef->help	= '';// ヘルプ文字列
		$navbarDef->menu =	array(
								(Object)array(
									'name'		=> '基本',		// 基本
									'task'		=> '',
									'url'		=> '',
									'tagid'		=> '',
									'active'	=> true,
									'submenu'	=> array()
								)
							);
		$this->gPage->setAdminSubNavbarDef($navbarDef);*/
		
		// メニューバー、パンくずリスト作成(簡易版)
		$this->createBasicConfigMenubar($request);
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
		if ($task == self::TASK_LIST){		// 一覧画面
			return $this->createList($request);
		} else {			// 詳細設定画面
			return $this->createDetail($request);
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
		
		$act = $request->trimValueOf('act');
		$this->serialNo = $request->trimValueOf('serial');		// 選択項目のシリアル番号
		$this->configId = $request->trimValueOf('item_id');		// 定義ID
		if (empty($this->configId)) $this->configId = $defConfigId;		// 呼び出しウィンドウから引き継いだ定義ID
		
		// 入力値を取得
		$name	= $request->trimValueOf('item_name');			// 定義名
		$itemCount	= $request->valueOf('item_count');			// 表示項目数
		$this->archiveType	= $request->valueOf('item_archive_type');		// アーカイブタイプ
		$this->sortOrder	= $request->valueOf('item_sort_order');		// ソート順
	
		$replaceNew = false;		// データを再取得するかどうか
		if ($act == 'add'){// 新規追加
			// 入力チェック
			$this->checkInput($name, '名前');
			$this->checkNumeric($itemCount, '表示項目数');
			
			// 設定名の重複チェック
			for ($i = 0; $i < count($this->paramObj); $i++){
				$targetObj = $this->paramObj[$i]->object;
				if ($name == $targetObj->name){		// 定義名
					$this->setUserErrorMsg('名前が重複しています');
					break;
				}
			}
			
			// エラーなしの場合は、データを登録
			if ($this->getMsgCount() == 0){
				// 追加オブジェクト作成
				$newObj = new stdClass;
				$newObj->name	= $name;// 表示名
				$newObj->itemCount	= $itemCount;
				$newObj->archiveType	= $this->archiveType;		// アーカイブタイプ
				$newObj->sortOrder	= $this->sortOrder;		// ソート順
				
				$ret = $this->addPageDefParam($defSerial, $defConfigId, $this->paramObj, $newObj);
				if ($ret){
					$this->setGuidanceMsg('データを追加しました');
					
					$this->configId = $defConfigId;		// 定義定義IDを更新
					$replaceNew = true;			// データ再取得
				} else {
					$this->setAppErrorMsg('データ追加に失敗しました');
				}
				$this->gPage->updateParentWindow($defSerial);// 親ウィンドウを更新
			}
		} else if ($act == 'update'){		// 設定更新のとき
			// 入力値のエラーチェック
			$this->checkNumeric($itemCount, '表示項目数');
			
			if ($this->getMsgCount() == 0){			// エラーのないとき
				// 現在の設定値を取得
				$ret = $this->getPageDefParam($defSerial, $defConfigId, $this->paramObj, $this->configId, $targetObj);
				if ($ret){
					// ウィジェットオブジェクト更新
					$targetObj->itemCount	= $itemCount;
					$targetObj->archiveType	= $this->archiveType;		// アーカイブタイプ
					$targetObj->sortOrder	= $this->sortOrder;		// ソート順
				}
				
				// 設定値を更新
				if ($ret) $ret = $this->updatePageDefParam($defSerial, $defConfigId, $this->paramObj, $this->configId, $targetObj);
				if ($ret){
					$this->setMsg(self::MSG_GUIDANCE, 'データを更新しました');
					$replaceNew = true;			// データ再取得
				} else {
					$this->setMsg(self::MSG_APP_ERR, 'データ更新に失敗しました');
				}
				$this->gPage->updateParentWindow($defSerial);// 親ウィンドウを更新
			}
		} else if ($act == 'select'){	// 定義IDを変更
			$replaceNew = true;			// データ再取得
		} else {	// 初期起動時、または上記以外の場合
			// デフォルト値設定
			$this->configId = $defConfigId;		// 呼び出しウィンドウから引き継いだ定義ID
			$replaceNew = true;			// データ再取得
		}
		// 表示用データを取得
		if (empty($this->configId)){		// 新規登録の場合
			$this->tmpl->setAttribute('item_name_visible', 'visibility', 'visible');// 名前入力フィールド表示
			if ($replaceNew){		// データ再取得時
				$name = $this->createDefaultName();			// デフォルト登録項目名
				$itemCount = self::DEFAULT_ITEM_COUNT;	// 表示項目数
				$this->archiveType	= '0';		// アーカイブタイプ
				$this->sortOrder	= '0';		// ソート順
			}
			$this->serialNo = 0;
		} else {
			if ($replaceNew){// データ再取得時
				$ret = $this->getPageDefParam($defSerial, $defConfigId, $this->paramObj, $this->configId, $targetObj);
				if ($ret){
					$name		= $targetObj->name;	// 名前
					$itemCount	= $targetObj->itemCount;
					$this->archiveType	= $targetObj->archiveType;		// アーカイブタイプ
					$this->sortOrder	= $targetObj->sortOrder;		// ソート順
				}
			}
			$this->serialNo = $this->configId;
				
			// 新規作成でないときは、メニューを変更不可にする(画面作成から呼ばれている場合のみ)
			if (!empty($defConfigId) && !empty($defSerial)) $this->tmpl->addVar("_widget", "id_disabled", 'disabled');
		}

		// 設定項目選択メニュー作成
		$this->createItemMenu();
		
		// メニュー作成
		$this->createArchiveTypeMenu();	// アーカイブタイプメニュー
		$this->createSortOrderMenu();	// ソート順メニュー
		
		// 画面にデータを埋め込む
		if (!empty($this->configId)) $this->tmpl->addVar("_widget", "id", $this->configId);		// 定義ID
		$this->tmpl->addVar("item_name_visible", "name",	$name);
		$this->tmpl->addVar("_widget", "serial", $this->serialNo);// 選択中のシリアル番号、IDを設定
		
		$this->tmpl->addVar("_widget", "item_count",	$itemCount);
		
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
		
		if (!empty($this->serialArray)) $this->tmpl->addVar("_widget", "serial_list", implode(',', $this->serialArray));// 表示項目のシリアル番号を設定
		
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
		
			// 使用数
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
				'def_count' => $defCount							// 使用数
			);
			$this->tmpl->addVars('itemlist', $row);
			$this->tmpl->parseTemplate('itemlist', 'a');
			
			// シリアル番号を保存
			$this->serialArray[] = $id;
		}
		// 一覧部の表示制御
		$this->setListTemplateVisibility('itemlist');
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
	 * アーカイブタイプ選択メニュー作成
	 *
	 * @return なし
	 */
	function createArchiveTypeMenu()
	{
		for ($i = 0; $i < count($this->archiveTypeArray); $i++){
			$value = $this->archiveTypeArray[$i]['value'];
			$name = $this->archiveTypeArray[$i]['name'];
			
			$row = array(
				'value'    => $value,			// タイプ値
				'name'     => $this->convertToDispString($name),			// タイプ名
				'selected' => $this->convertToSelectedString($value, $this->archiveType)			// 選択中かどうか
			);
			$this->tmpl->addVars('archive_type', $row);
			$this->tmpl->parseTemplate('archive_type', 'a');
		}
	}
	/**
	 * ソート順選択メニュー作成
	 *
	 * @return なし
	 */
	function createSortOrderMenu()
	{
		for ($i = 0; $i < count($this->sortOrderArray); $i++){
			$value = $this->sortOrderArray[$i]['value'];
			$name = $this->sortOrderArray[$i]['name'];
			
			$row = array(
				'value'    => $value,			// タイプ値
				'name'     => $this->convertToDispString($name),			// タイプ名
				'selected' => $this->convertToSelectedString($value, $this->sortOrder)			// 選択中かどうか
			);
			$this->tmpl->addVars('sort_order', $row);
			$this->tmpl->parseTemplate('sort_order', 'a');
		}
	}
}
?>
