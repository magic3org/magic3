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
require_once($gEnvManager->getCommonPath() . '/valueCheck.php');

class admin_ec_product_display2WidgetContainer extends BaseAdminWidgetContainer
{
	private $sysDb;	// DB接続オブジェクト
	private $serialNo;		// 選択中の項目のシリアル番号
	private $serialArray = array();			// 表示中のシリアル番号
	private $configId;		// 定義ID
	private $paramObj;		// パラメータ保存用オブジェクト
	private $imgMenu;		// 画像選択メニュー
	const DEFAULT_NAME_HEAD = '名称未設定';			// デフォルトの設定名
	const DEFAULT_ROW_COUNT = 2;			// 表示する行の数
	const DEFAULT_COLUMN_COUNT = 3;			// 表示する列の数
	const PRODUCT_IMAGE_SMALL = 'small-product';		// 小サイズ商品画像ID
	const PRODUCT_IMAGE_MEDIUM = 'standard-product';		// 中サイズ商品画像ID
	const PRODUCT_IMAGE_LARGE = 'large-product';		// 大サイズ商品画像ID
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->sysDb = $this->gInstance->getSytemDbObject();
		
		// 画像選択メニュー
		$this->imgMenu = array(	array(	'name' => '小',		'value' => self::PRODUCT_IMAGE_SMALL),
								array(	'name' => '中',		'value' => self::PRODUCT_IMAGE_MEDIUM),
								array(	'name' => '大',		'value' => self::PRODUCT_IMAGE_LARGE));
	}
	/**
	 * ウィジェット初期化
	 *
	 * 共通パラメータの初期化や、以下のパターンでウィジェット出力方法の変更を行う。
	 * ・組み込みの_setTemplate(),_assign()を使用
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @return 								なし
	 */
	function _init($request)
	{
		$task = $request->trimValueOf('task');
		if ($task == 'list'){		// 一覧画面
			// 通常のテンプレート処理を組み込みのテンプレート処理に変更。_setTemplate()、_assign()はキャンセル。
			$this->replaceAssignTemplate(self::ASSIGN_TEMPLATE_BASIC_CONFIG_LIST);		// 設定一覧(基本)
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
		// メニューバー、パンくずリスト作成(簡易版)
		$this->createBasicConfigMenubar($request);
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
		
		$act = $request->trimValueOf('act');
		$this->serialNo = $request->trimValueOf('serial');		// 選択項目のシリアル番号

		// 入力値を取得
		$name	= $request->trimValueOf('item_name');			// タイトル
		$detailLabel	= $request->trimValueOf('item_detail_label');// 詳細へのリンク
		$rowCount		= $request->trimValueOf('item_row');			// 表示する行の数
		$columnCount	= $request->trimValueOf('item_column');			// 表示する列の数
		$this->imgSize	= $request->trimValueOf('item_img_size');				// 選択中の画像サイズ
		$nameVisible	= ($request->trimValueOf('item_name_visible') == 'on') ? 1 : 0;			// 商品名表示
		$codeVisible	= ($request->trimValueOf('item_code_visible') == 'on') ? 1 : 0;			// 商品コード表示
		$priceVisible	= ($request->trimValueOf('item_price_visible') == 'on') ? 1 : 0;			// 商品価格表示
		$descVisible	= ($request->trimValueOf('item_desc_visible') == 'on') ? 1 : 0;			// 商品説明表示
		$imgVisible		= ($request->trimValueOf('item_img_visible') == 'on') ? 1 : 0;			// 商品画像表示
		$detailVisible	= ($request->trimValueOf('item_detail_visible') == 'on') ? 1 : 0;			// 詳細ボタン表示
		$productItems	= $request->trimValueOf('item_product_items');			// 表示する商品
			
		$this->configId = $request->trimValueOf('item_id');		// 定義ID
		if (empty($this->configId)) $this->configId = $defConfigId;		// 呼び出しウィンドウから引き継いだ定義ID
		
		$replaceNew = false;		// データを再取得するかどうか
		if (empty($act)){// 初期起動時
			// デフォルト値設定
			$this->configId = $defConfigId;		// 呼び出しウィンドウから引き継いだ定義ID
			$replaceNew = true;			// データ再取得
		} else if ($act == 'add'){// 新規追加
			// 入力チェック
			$this->checkInput($name, '名前');
			$this->checkNumeric($rowCount, '行数');
			$this->checkNumeric($columnCount, '列数');
			
			// 設定名の重複チェック
			for ($i = 0; $i < count($this->paramObj); $i++){
				$targetObj = $this->paramObj[$i]->object;
				if ($name == $targetObj->name){		// 定義名
					$this->setUserErrorMsg('名前が重複しています');
					break;
				}
			}

			if (!empty($productItems)){
				$productArray = explode(',', $productItems);
				if (!ValueCheck::isNumeric($productArray)){		// すべて数値であるかチェック
					$this->setUserErrorMsg('商品IDに数値以外は使用できません');
				}
			}
			
			// エラーなしの場合は、データを登録
			if ($this->getMsgCount() == 0){
				// 追加オブジェクト作成
				$newObj = new stdClass;
				$newObj->name	= $name;// 表示名
				$newObj->detailLabel	= $detailLabel;// 詳細へのリンク
				$newObj->rowCount	= $rowCount;			// 表示する行の数
				$newObj->columnCount	= $columnCount;			// 表示する列の数
				$newObj->imgSize	= $this->imgSize;				// 選択中の画像サイズ
				$newObj->nameVisible	= $nameVisible;			// 商品名表示
				$newObj->codeVisible	= $codeVisible;			// 商品コード表示
				$newObj->priceVisible	= $priceVisible;			// 商品価格表示
				$newObj->descVisible	= $descVisible;			// 商品説明表示
				$newObj->imgVisible	= $imgVisible;			// 商品画像表示
				$newObj->detailVisible	= $detailVisible;			// 詳細ボタン表示
				$newObj->productItems	= $productItems;			// 表示する商品
				
				$ret = $this->addPageDefParam($defSerial, $defConfigId, $this->paramObj, $newObj);
				if ($ret){
					$this->setGuidanceMsg('データを追加しました');
					
					$this->configId = $defConfigId;		// 定義定義IDを更新
					$replaceNew = true;			// データ再取得
				} else {
					$this->setAppErrorMsg('データ追加に失敗しました');
				}
			}
		} else if ($act == 'update'){		// 設定更新のとき
			// 入力値のエラーチェック
			$this->checkNumeric($rowCount, '行数');
			$this->checkNumeric($columnCount, '列数');
			
			if (!empty($productItems)){
				$productArray = explode(',', $productItems);
				if (!ValueCheck::isNumeric($productArray)){		// すべて数値であるかチェック
					$this->setUserErrorMsg('商品IDに数値以外は使用できません');
				}
			}
			
			if ($this->getMsgCount() == 0){			// エラーのないとき
				// 現在の設定値を取得
				$ret = $this->getPageDefParam($defSerial, $defConfigId, $this->paramObj, $this->configId, $targetObj);
				if ($ret){
					// ウィジェットオブジェクト更新
					$targetObj->detailLabel	= $detailLabel;// 詳細へのリンク
					$targetObj->rowCount	= $rowCount;			// 表示する行の数
					$targetObj->columnCount	= $columnCount;			// 表示する列の数
					$targetObj->imgSize	= $this->imgSize;				// 選択中の画像サイズ
					$targetObj->nameVisible	= $nameVisible;			// 商品名表示
					$targetObj->codeVisible	= $codeVisible;			// 商品コード表示
					$targetObj->priceVisible	= $priceVisible;			// 商品価格表示
					$targetObj->descVisible	= $descVisible;			// 商品説明表示
					$targetObj->imgVisible	= $imgVisible;			// 商品画像表示
					$targetObj->detailVisible	= $detailVisible;			// 詳細ボタン表示
					$targetObj->productItems	= $productItems;			// 表示する商品
				}
				
				// 設定値を更新
				if ($ret) $ret = $this->updatePageDefParam($defSerial, $defConfigId, $this->paramObj, $this->configId, $targetObj);
				if ($ret){
					$this->setMsg(self::MSG_GUIDANCE, 'データを更新しました');
					
					$replaceNew = true;			// データ再取得
				} else {
					$this->setMsg(self::MSG_APP_ERR, 'データ更新に失敗しました');
				}
			}
		} else if ($act == 'select'){	// 定義IDを変更
			$replaceNew = true;			// データ再取得
		}
		
		// 設定項目選択メニュー作成
		$this->createItemMenu();
		
		// 表示用データを取得
		if (empty($this->configId)){		// 新規登録の場合
			$this->tmpl->setAttribute('item_name_visible', 'visibility', 'visible');// 名前入力フィールド表示
			if ($replaceNew){		// データ再取得時
				//$name = $this->createDefaultName();			// デフォルト登録項目名
				$name = $this->createConfigDefaultName();			// デフォルト登録項目名
				$detailLabel = 'もっと詳しく...';	// 詳細へのリンク
				$rowCount = self::DEFAULT_ROW_COUNT;			// 表示する行の数
				$columnCount = self::DEFAULT_COLUMN_COUNT;			// 表示する列の数
				$this->imgSize = self::PRODUCT_IMAGE_MEDIUM;				// 選択中の画像サイズ
				$nameVisible = 1;			// 商品名表示
				$codeVisible = 0;			// 商品コード表示
				$priceVisible = 1;			// 商品価格表示
				$descVisible = 0;			// 商品説明表示
				$imgVisible = 1;			// 商品画像表示
				$detailVisible = 0;			// 詳細ボタン表示
				$productItems = '';			// 表示する商品
			}
			$this->serialNo = 0;
		} else {
			if ($replaceNew){
				$ret = $this->getPageDefParam($defSerial, $defConfigId, $this->paramObj, $this->configId, $targetObj);
				if ($ret){
					$name = $targetObj->name;						// 名前
					$detailLabel = $targetObj->detailLabel;			// 詳細へのリンク
					$rowCount = $targetObj->rowCount;				// 表示する行の数
					$columnCount = $targetObj->columnCount;			// 表示する列の数
					$this->imgSize = $targetObj->imgSize;			// 選択中の画像サイズ
					$nameVisible = $targetObj->nameVisible;			// 商品名表示
					$codeVisible = $targetObj->codeVisible;			// 商品コード表示
					$priceVisible = $targetObj->priceVisible;		// 商品価格表示
					$descVisible = $targetObj->descVisible;			// 商品説明表示
					$imgVisible = $targetObj->imgVisible;			// 商品画像表示
					$detailVisible = $targetObj->detailVisible;		// 詳細ボタン表示
					$productItems = $targetObj->productItems;			// 表示する商品
				}
			}
			$this->serialNo = $this->configId;
				
			// 新規作成でないときは、メニューを変更不可にする(画面作成から呼ばれている場合のみ)
			if (!empty($defConfigId) && !empty($defSerial)) $this->tmpl->addVar("_widget", "id_disabled", 'disabled');
		}
		// 画像選択メニュー作成
		$this->createImgMenu();
		
		// 画面にデータを埋め込む
		$this->tmpl->addVar("item_name_visible", "name", $this->convertToDispString($name));		// 名前
		$this->tmpl->addVar("_widget", "detail_label",	$this->convertToDispString($detailLabel));// 詳細へのリンク
		$this->tmpl->addVar("_widget", "row",	$this->convertToDispString($rowCount));// 表示する行の数
		$this->tmpl->addVar("_widget", "column",	$this->convertToDispString($columnCount));// 表示する列の数
		if ($nameVisible) $this->tmpl->addVar('_widget', 'name_visible',	'checked');	// 商品名表示
		if ($codeVisible) $this->tmpl->addVar('_widget', 'code_visible',	'checked');			// 商品コード表示
		if ($priceVisible) $this->tmpl->addVar('_widget', 'price_visible',	'checked');			// 商品価格表示
		if ($descVisible) $this->tmpl->addVar('_widget', 'desc_visible',	'checked');			// 商品説明表示
		if ($imgVisible) $this->tmpl->addVar('_widget', 'img_visible',	'checked');			// 商品画像表示
		if ($detailVisible) $this->tmpl->addVar('_widget', 'detail_visible',	'checked');			// 詳細ボタン表示
		$this->tmpl->addVar("_widget", "product_items",	$this->convertToDispString($productItems));// 表示する商品
				
		$this->tmpl->addVar("_widget", "serial", $this->serialNo);// 選択中のシリアル番号、IDを設定
		
		// ボタンの表示制御
		if (empty($this->serialNo)){		// 新規追加項目を選択しているとき
			$this->tmpl->setAttribute('add_button', 'visibility', 'visible');// 「新規追加」ボタン
			$this->tmpl->addVar("_widget", "id", '');// 定義ID
		} else {
			$this->tmpl->setAttribute('update_button', 'visibility', 'visible');// 「更新」ボタン
			$this->tmpl->addVar("_widget", "id", $this->convertToDispString($this->serialNo));// 定義ID
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
	 * デフォルトの名前を取得
	 *
	 * @return string	デフォルト名						
	 */
/*	function createDefaultName()
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
	}*/
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
			$id = $this->paramObj[$i]->id;// 定義ID
			$targetObj = $this->paramObj[$i]->object;
			$name = $targetObj->name;// 定義名
			
			$defCount = 0;
			if (!empty($id)){
				$defCount = $this->sysDb->getPageDefCount($this->gEnv->getCurrentWidgetId(), $id);
			}
			$operationDisagled = '';
			if ($defCount > 0) $operationDisagled = 'disabled';
			$row = array(
				'index' => $i,
				'id' => $this->convertToDispString($id),	// 設定ID
				'ope_disabled' => $operationDisagled,			// 選択可能かどうか
				'name' => $this->convertToDispString($name),		// 名前
				'def_count' => $defCount							// 使用数
			);
			$this->tmpl->addVars('itemlist', $row);
			$this->tmpl->parseTemplate('itemlist', 'a');
			
			// シリアル番号を保存
			$this->serialArray[] = $id;
		}
	}
	/**
	 * メニュー表示速度選択メニュー作成
	 *
	 * @return なし
	 */
	function createImgMenu()
	{
		for ($i = 0; $i < count($this->imgMenu); $i++){
			$value = $this->imgMenu[$i]['value'];
			$name = $this->imgMenu[$i]['name'];
			
			$selected = '';
			if ($value == $this->imgSize) $selected = 'selected';
			
			$row = array(
				'value'    => $value,			// 値
				'name'     => $name,			// 名前
				'selected' => $selected														// 選択中かどうか
			);
			$this->tmpl->addVars('img_size_list', $row);
			$this->tmpl->parseTemplate('img_size_list', 'a');
		}
	}
}
?>
