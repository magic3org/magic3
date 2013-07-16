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
 * @version    SVN: $Id: admin_flashWidgetContainer.php 2266 2009-08-28 08:25:59Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseAdminWidgetContainer.php');

class admin_flashWidgetContainer extends BaseAdminWidgetContainer
{
	private $sysDb;	// DB接続オブジェクト
	private $serialNo;		// 選択中の項目のシリアル番号
	private $serialArray = array();			// 表示中のシリアル番号
	private $langId;
	private $configId;		// 定義ID
	private $paramObj;		// パラメータ保存用オブジェクト
	const DEFAULT_NAME_HEAD = '名称未設定';			// デフォルトの設定名
	const DEFAULT_WIDTH = 130;		// デフォルトの幅
	const DEFAULT_HEIGHT = 130;		// デフォルトの高さ
	
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

		$name	= $request->trimValueOf('item_name');			// ヘッダタイトル
		$width	= $request->trimValueOf('item_width');		// ヘッダの幅
		$height	= $request->trimValueOf('item_height');		// ヘッダの高さ
		$url = $request->trimValueOf('item_flash_url');		// FlashファイルのURL
		if (strncmp($url, '/', 1) == 0){		// 相対パス表記のとき
			$url = M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END . $this->gEnv->getRelativePathToSystemRootUrl($this->gEnv->getDocumentRootUrl() . $url);
		}
		$showTitle = ($request->trimValueOf('item_showtitle') == 'on') ? 1 : 0;		// タイトルを表示するかどうか			
		$this->configId = $request->trimValueOf('item_id');		// 定義ID
		if (empty($this->configId)) $this->configId = $defConfigId;		// 呼び出しウィンドウから引き継いだ定義ID
				
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
			$this->checkInput($url, 'ファイル');
			$this->checkNumeric($width, '幅');
			$this->checkNumeric($height, '高さ');
			
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
				$newObj->width	= $width;		// 幅
				$newObj->height	= $height;		// 高さ
				$newObj->url = $url;		// FlashファイルのURL
				$newObj->showTitle = $showTitle;					// タイトルを表示するかどうか
				
				$newParam = new stdClass;
				$newParam->id = -1;		// 新規追加
				$newParam->object = $newObj;
			
				// ウィジェットパラメータオブジェクト更新
				$ret = $this->updateWidgetParamObjectWithId($newParam);
				if ($ret){
					$this->paramObj[] = $newParam;		// 新規定義を追加
					$newConfigId = $newParam->id;		// 新規に取得したIDを設定
				}
				
				// 画面定義更新
				if ($ret && !empty($defSerial)){		// 画面作成から呼ばれている場合のみ更新
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
			$this->checkInput($url, 'ファイル');
			$this->checkNumeric($width, '幅');
			$this->checkNumeric($height, '高さ');
			
			if ($this->getMsgCount() == 0){			// エラーのないとき
				// 該当項目を更新
				$ret = false;
				for ($i = 0; $i < count($this->paramObj); $i++){
					$id			= $this->paramObj[$i]->id;// 定義ID
					$targetObj	= $this->paramObj[$i]->object;
					if ($id == $this->configId){
						// ウィジェットオブジェクト更新
						$targetObj->width	= $width;		// ヘッダの幅
						$targetObj->height	= $height;		// ヘッダの高さ
						$targetObj->url	= $url;		// FlashファイルのURL
						$targetObj->showTitle = $showTitle;					// タイトルを表示するかどうか
						
						// ウィジェットパラメータオブジェクトを更新
						$ret = $this->updateWidgetParamObjectWithId($this->paramObj[$i]);
						break;
					}
				}
				
				if (empty($defConfigId) && !empty($defSerial)){		// 画面定義の定義IDが設定されていないときは設定。画面作成から呼ばれている場合のみ更新
					// 画面定義更新
					if ($ret) $ret = $this->sysDb->updateWidgetConfigId($this->gEnv->getCurrentWidgetId(), $defSerial, $this->configId, $targetObj->name);
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
			$width	= self::DEFAULT_WIDTH;		// 幅
			$height	= self::DEFAULT_HEIGHT;		// 高さ
			$url = '';		// FlashファイルのURL
			$showTitle = 1;					// タイトルを表示するかどうか
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
					$name	= $targetObj->name;// 名前
					$width	= $targetObj->width;		// 幅
					$height	= $targetObj->height;		// 高さ
					$url	= $targetObj->url;		// FlashファイルのURL			
					$showTitle = $targetObj->showTitle;					// タイトルを表示するかどうか
				}
				$this->serialNo = $this->configId;
				
				// 新規作成でないときは、メニューを変更不可にする(画面作成から呼ばれている場合のみ)
				if (!empty($defConfigId) && !empty($defSerial)) $this->tmpl->addVar("_widget", "id_disabled", 'disabled');
			}
		}
		// プレビューURL
		if (!empty($url)) $previewUrl = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $this->gEnv->getRootUrl(), $url);
		
		// 画面にデータを埋め込む
		$this->tmpl->addVar("item_name_visible", "name", $name);		// 名前
		if (!empty($this->configId)) $this->tmpl->addVar("_widget", "id", $this->configId);		// 定義ID
		$this->tmpl->addVar("_widget", "width",	$width);
		$this->tmpl->addVar("_widget", "height",	$height);
		$this->tmpl->addVar("_widget", "flash_url",	$this->getUrl($url));
		$this->tmpl->addVar("_widget", "preview_url",	$this->getUrl($previewUrl));
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
			//$this->tmpl->setAttribute('del_button', 'visibility', 'visible');// 「削除」ボタン
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
			$pathArray = explode('/', $targetObj->url);
			if (count($pathArray) > 0) $filename = $pathArray[count($pathArray) -1];// ファイル名
			
			// プレビュー用URL
			$url = $targetObj->url;
			if (!empty($url)) $url = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $this->gEnv->getRootUrl(), $url);
			
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
				'filename' => $filename,		// ファイル名
				'url' => $this->getUrl($url),					// URL
				'width' => $targetObj->width,					// Flashファイル幅
				'height' => $targetObj->height,					// Flashファイル高さ
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
