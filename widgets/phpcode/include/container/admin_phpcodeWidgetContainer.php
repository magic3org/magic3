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
 * @copyright  Copyright 2006-2009 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: admin_phpcodeWidgetContainer.php 2591 2009-11-23 05:56:11Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseAdminWidgetContainer.php');

class admin_phpcodeWidgetContainer extends BaseAdminWidgetContainer
{
	private $sysDb;	// DB接続オブジェクト
	private $serialNo;		// 選択中の項目のシリアル番号
	private $serialArray = array();			// 表示中のシリアル番号
	private $configId;		// 定義ID
	private $paramObj;		// パラメータ保存用オブジェクト
	const DEFAULT_NAME_HEAD = '名称未設定';			// デフォルトの設定名
	const DEFAULT_CODE = 'echo \'<div style="color:black; background-color:LimeGreen; padding: 5px; font-size:x-large;">Hello, Magic3!</div>\';';		// デフォルトのPHPプログラム
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
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
		$userId		= $this->gEnv->getCurrentUserId();
		$act = $request->trimValueOf('act');
		$this->serialNo = $request->trimValueOf('serial');		// 選択項目のシリアル番号
		
		// ページ定義IDとページ定義のレコードシリアル番号
		$defConfigId = $this->getPageDefConfigId($request);				// 定義ID取得
		$defSerial = $this->getPageDefSerial($request);		// ページ定義のレコードシリアル番号
		$value = $request->trimValueOf('defconfig');		// ページ定義の定義ID
		if (!empty($value)) $defConfigId = $value;
		$value = $request->trimValueOf('defserial');		// ページ定義のレコードシリアル番号
		if (!empty($value)) $defSerial = $value;

		$name	= $request->trimValueOf('item_name');			// ヘッダタイトル
		$code	= $request->valueOf('item_code');		// PHPプログラム
		$showTitle = ($request->trimValueOf('item_showtitle') == 'on') ? 1 : 0;		// タイトルを表示するかどうか			
		$this->configId = $request->trimValueOf('item_id');		// 定義ID
		if (empty($this->configId)) $this->configId = $defConfigId;		// 呼び出しウィンドウから引き継いだ定義ID

		// パラメータオブジェクトを取得
		$this->paramObj = $this->getWidgetParamObj();
		
		$replaceNew = false;		// データを再取得するかどうか
		if (empty($act)){// 初期起動時
			// デフォルト値設定
			$this->configId = $defConfigId;		// 呼び出しウィンドウから引き継いだ定義ID
			$replaceNew = true;			// データ再取得
		} else if ($act == 'add'){// 新規追加
			// 入力チェック
			$this->checkInput($name, '名前');
			
			// 設定名の重複チェック
			for ($i = 0; $i < count($this->paramObj); $i++){
				$targetObj = $this->paramObj[$i];
				if ($name == $targetObj->name){		// 定義名
					$this->setUserErrorMsg('名前が重複しています');
					break;
				}
			}
			
			// エラーなしの場合は、データを登録
			if ($this->getMsgCount() == 0){
				// 最大の定義ID取得
				$newConfigId = 1;
				if (count($this->paramObj) > 0){
					$targetObj = $this->paramObj[count($this->paramObj) -1];
					$newConfigId = $targetObj->id + 1;
				}
				// 追加オブジェクト作成
				$newObj = new stdClass;
				$newObj->id		= $newConfigId;// 定義ID
				$newObj->name	= $name;// 表示名
				$newObj->code = $code;		// PHPプログラム
				$newObj->showTitle = $showTitle;					// タイトルを表示するかどうか
				$this->paramObj[] = $newObj;
			
				// ウィジェットパラメータオブジェクト更新
				$ret = $this->updateWidgetParamObj($this->paramObj);
				
				// 画面定義更新
				if ($ret && !empty($defSerial)){
					$ret = $this->sysDb->updateWidgetConfigId($this->gEnv->getCurrentWidgetId(), $defSerial, $newConfigId, $name);
				}
				if ($ret){
					$this->setGuidanceMsg('データを追加しました');
					
					if (!empty($defSerial)) $defConfigId = $newConfigId;		// 定義定義IDを更新
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
				for ($i = 0; $i < count($this->paramObj); $i++){
					$targetObj = $this->paramObj[$i];
					$id = $targetObj->id;// 定義ID
					$name = $targetObj->name;// 定義名
					if ($id == $this->configId){
						// ウィジェットオブジェクト更新
						$targetObj->code	= $code;		// PHPプログラム
						$targetObj->showTitle = $showTitle;					// タイトルを表示するかどうか
						break;
					}
				}
				// ウィジェットパラメータオブジェクトを更新
				$ret = $this->updateWidgetParamObj($this->paramObj);
				
				if (empty($defConfigId) && !empty($defSerial)){		// 画面定義の定義IDが設定されていないときは設定
					// 画面定義更新
					if ($ret) $ret = $this->sysDb->updateWidgetConfigId($this->gEnv->getCurrentWidgetId(), $defSerial, $this->configId, $name);
				}
				if ($ret){
					$this->setMsg(self::MSG_GUIDANCE, 'データを更新しました');
					
					if (empty($defConfigId) && !empty($defSerial)){		// 画面定義の定義IDが設定されていないときは設定
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
		
		// 設定項目選択メニュー作成
		$this->createItemMenu();
		
		// 表示用データを取得
		if (empty($this->configId)){		// 新規登録の場合
			$this->tmpl->setAttribute('item_name_visible', 'visibility', 'visible');// 名前入力フィールド表示
			$name = $this->createDefaultName();			// デフォルト登録項目名
			$code = self::DEFAULT_CODE;		// PHPプログラム
			$showTitle = 1;					// タイトルを表示するかどうか
			$this->serialNo = 0;
		} else {
			// 定義からウィジェットパラメータを検索して、定義データを取得
			for ($i = 0; $i < count($this->paramObj); $i++){
				$targetObj = $this->paramObj[$i];
				$id = $targetObj->id;// 定義ID
				if ($id == $this->configId) break;
			}
			if ($i < count($this->paramObj)){		// 該当項目があるとき
				// ウィジェットオブジェクトから値を取得
				if ($replaceNew){		// データ再取得のとき
					$name = $targetObj->name;// 名前
					$code = $targetObj->code;		// PHPプログラム				
					$showTitle = $targetObj->showTitle;					// タイトルを表示するかどうか
				}
				$this->serialNo = $this->configId;
				
				// 新規作成でないときは、メニューを変更不可にする
				if (!empty($defConfigId) && !empty($defSerial)) $this->tmpl->addVar("_widget", "id_disabled", 'disabled');
			}
		}
		
		// 画面にデータを埋め込む
		$this->tmpl->addVar("item_name_visible", "name", $name);		// 名前
		$this->tmpl->addVar("_widget", "code",	$code);
		$checked = '';
		if ($showTitle) $checked = 'checked';
		$this->tmpl->addVar("_widget", "show_title", $checked);	// タイトルを表示するかどうか
				
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
	}
	/**
	 * 選択用メニューを作成
	 *
	 * @return なし						
	 */
	function createItemMenu()
	{
		for ($i = 0; $i < count($this->paramObj); $i++){
			$targetObj = $this->paramObj[$i];
			$id = $targetObj->id;// 定義ID
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
				$targetObj = $this->paramObj[$i];
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
	 * 一覧画面作成
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param								なし
	 */
	function createList($request)
	{
		$userId		= $this->gEnv->getCurrentUserId();
		$act = $request->trimValueOf('act');
		
		// ページ定義IDとページ定義のレコードシリアル番号
		$defConfigId = $this->getPageDefConfigId($request);				// 定義ID取得
		$defSerial = $this->getPageDefSerial($request);		// ページ定義のレコードシリアル番号
		$value = $request->trimValueOf('defconfig');		// ページ定義の定義ID
		if (!empty($value)) $defConfigId = $value;
		$value = $request->trimValueOf('defserial');		// ページ定義のレコードシリアル番号
		if (!empty($value)) $defSerial = $value;

		// パラメータオブジェクトを取得
		$this->paramObj = $this->getWidgetParamObj();
		
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
				// 更新オブジェクト作成
				$newParamObj = array();

				for ($i = 0; $i < count($this->paramObj); $i++){
					$targetObj = $this->paramObj[$i];
					$id = $targetObj->id;// 定義ID
					if (!in_array($id, $delItems)){		// 削除対象でないときは追加
						$newParamObj[] = $targetObj;
					}
				}
				
				// ウィジェットパラメータオブジェクト更新
				$ret = $this->updateWidgetParamObj($newParamObj);
				if ($ret){		// データ削除成功のとき
					$this->setGuidanceMsg('データを削除しました');
					$this->paramObj = $newParamObj;
				} else {
					$this->setAppErrorMsg('データ削除に失敗しました');
				}
			}
		}
		// 定義一覧作成
		$this->createItemList();
		
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
			$targetObj = $this->paramObj[$i];
			$id = $targetObj->id;// 定義ID
			$name = $targetObj->name;// 定義名
			
			$defCount = 0;
			if (!empty($id)){
				$defCount = $this->sysDb->getPageDefCount($this->gEnv->getCurrentWidgetId(), $id);
			}
			$operationDisagled = '';
			if ($defCount > 0) $operationDisagled = 'disabled';
			$row = array(
				'index' => $i,
				'ope_disabled' => $operationDisagled,			// 選択可能かどうか
				'name' => $this->convertToDispString($name),		// 名前
				'movie_id' => $this->convertToDispString($targetObj->code),	// PHPプログラム
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
