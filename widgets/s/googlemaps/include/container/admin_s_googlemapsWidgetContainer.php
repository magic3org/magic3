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
 * @version    SVN: $Id: admin_s_googlemapsWidgetContainer.php 4767 2012-03-19 08:59:10Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseAdminWidgetContainer.php');

class admin_s_googlemapsWidgetContainer extends BaseAdminWidgetContainer
{
	private $serialNo;		// 選択中の項目のシリアル番号
	private $serialArray = array();			// 表示中のシリアル番号
	private $langId;
	private $configId;		// 定義ID
	private $paramObj;		// パラメータ保存用オブジェクト
	const DEFAULT_NAME_HEAD = '名称未設定';			// デフォルトの設定名
	const DEFAULT_WIDTH = 320;		// デフォルトの幅
	const DEFAULT_HEIGHT = 480;		// デフォルトの高さ
	const CODING_URL = 'http://www.geocoding.jp/';			// 緯度経度取得用URL
	const DEFAULT_POS_LAT = '35.594757';				// デフォルト緯度
	const DEFAULT_POS_LNG = '139.620739';			// デフォルト経度
	const DEFAULT_ZOOM = 13;			// デフォルトのズームレベル
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
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
	 * @return								なし
	 */
	function createDetail($request)
	{
		// ページ定義IDとページ定義のレコードシリアル番号を取得
		$this->startPageDefParam($defSerial, $defConfigId, $this->paramObj);
		
		$userId		= $this->gEnv->getCurrentUserId();
		$this->langId	= $this->gEnv->getCurrentLanguage();		// 表示言語を取得
		$act = $request->trimValueOf('act');
		$this->serialNo = $request->trimValueOf('serial');		// 選択項目のシリアル番号

		// 入力値を取得
		$name	= $request->trimValueOf('item_name');			// ヘッダタイトル
		$width	= $request->trimValueOf('item_width');		// ヘッダの幅
		$height	= $request->trimValueOf('item_height');		// ヘッダの高さ
		$lat	= $request->trimValueOf('item_lat');		// 緯度
		$lng	= $request->trimValueOf('item_lng');		// 経度
		$markerLat	= $request->trimValueOf('item_marker_lat');		// マーカー緯度
		$markerLng	= $request->trimValueOf('item_marker_lng');		// マーカー経度
		$infoLat	= $request->trimValueOf('item_info_lat');		// 吹き出し緯度
		$infoLng	= $request->trimValueOf('item_info_lng');		// 吹き出し経度
		$zoom	= $request->trimValueOf('item_zoom');		// ズームレベル
		$infoContent	= $request->valueOf('item_info_content');		// 吹き出し内容
		$infoContent = str_replace(array("\r", "\n", "\t"), '', $infoContent);		// 改行、タブ削除
		$showMarker = ($request->trimValueOf('item_show_marker') == 'on') ? 1 : 0;		// マーカーを表示するかどうか
		$showPosControl = ($request->trimValueOf('item_pos_control') == 'on') ? 1 : 0;		// 位置コントローラを表示するかどうか
		$showTypeControl = ($request->trimValueOf('item_type_control') == 'on') ? 1 : 0;		// 地図タイプコントローラを表示するかどうか
		$showInfo = ($request->trimValueOf('item_show_info') == 'on') ? 1 : 0;		// 吹き出しを表示するかどうか
		
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
			$this->checkNumeric($width, '幅');
			$this->checkNumeric($height, '高さ');
			$this->checkNumber($lat, '緯度');
			$this->checkNumber($lng, '経度');
			$this->checkNumber($zoom, 'ズームレベル');
			if ($showMarker){			// マーカーを表示するかどうか
				$this->checkNumber($markerLat, 'マーカー緯度');
				$this->checkNumber($markerLng, 'マーカー経度');
			} else {
				$this->checkNumber($markerLat, 'マーカー緯度', true);
				$this->checkNumber($markerLng, 'マーカー経度', true);
			}
			if ($showInfo){		// 吹き出しを表示するかどうか
				$this->checkNumber($infoLat, '吹き出し緯度');
				$this->checkNumber($infoLng, '吹き出し経度');
			} else {
				$this->checkNumber($infoLat, '吹き出し緯度', true);
				$this->checkNumber($infoLng, '吹き出し経度', true);
			}
			
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
				$newObj->name	= $name;	// 表示名
				$newObj->width	= $width;		// 幅
				$newObj->height	= $height;		// 高さ
				$newObj->lat	= $lat;		// 緯度
				$newObj->lng	= $lng;		// 経度
				$newObj->markerLat	= $markerLat;		// マーカー緯度
				$newObj->markerLng	= $markerLng;		// マーカー経度
				$newObj->infoLat	= $infoLat;		// 吹き出し緯度
				$newObj->infoLng	= $infoLng;		// 吹き出し経度
				
				$newObj->zoom	= $zoom;		// ズームレベル
				$newObj->infoContent	= $infoContent;		// 吹き出し内容
				$newObj->showMarker = $showMarker;		// マーカーを表示するかどうか
				$newObj->showPosControl = $showPosControl;		// 位置コントローラを表示するかどうか
				$newObj->showTypeControl = $showTypeControl;		// 地図タイプコントローラを表示するかどうか
				$newObj->showInfo = $showInfo;			// 吹き出しを表示するかどうか
				
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
			$this->checkNumeric($width, '幅');
			$this->checkNumeric($height, '高さ');
			$this->checkNumber($lat, '緯度');
			$this->checkNumber($lng, '経度');
			$this->checkNumber($zoom, 'ズームレベル');
			if ($showMarker){			// マーカーを表示するかどうか
				$this->checkNumber($markerLat, 'マーカー緯度');
				$this->checkNumber($markerLng, 'マーカー経度');
			} else {
				$this->checkNumber($markerLat, 'マーカー緯度', true);
				$this->checkNumber($markerLng, 'マーカー経度', true);
			}
			if ($showInfo){		// 吹き出しを表示するかどうか
				$this->checkNumber($infoLat, '吹き出し緯度');
				$this->checkNumber($infoLng, '吹き出し経度');
			} else {
				$this->checkNumber($infoLat, '吹き出し緯度', true);
				$this->checkNumber($infoLng, '吹き出し経度', true);
			}
			
			if ($this->getMsgCount() == 0){			// エラーのないとき
				// 現在の設定値を取得
				$ret = $this->getPageDefParam($defSerial, $defConfigId, $this->paramObj, $this->configId, $targetObj);
				if ($ret){
					// ウィジェットオブジェクト更新
					$targetObj->width	= $width;		// ヘッダの幅
					$targetObj->height	= $height;		// ヘッダの高さ
					$targetObj->lat		= $lat;		// 緯度
					$targetObj->lng		= $lng;		// 経度
					$targetObj->markerLat		= $markerLat;		// マーカー緯度
					$targetObj->markerLng		= $markerLng;		// マーカー経度
					$targetObj->infoLat	= $infoLat;		// 吹き出し緯度
					$targetObj->infoLng	= $infoLng;		// 吹き出し経度
					$targetObj->zoom	= $zoom;		// ズームレベル
					$targetObj->infoContent	= $infoContent;		// 吹き出し内容
					$targetObj->showMarker = $showMarker;		// マーカーを表示するかどうか
					$targetObj->showPosControl = $showPosControl;		// 位置コントローラを表示するかどうか
					$targetObj->showTypeControl = $showTypeControl;		// 地図タイプコントローラを表示するかどうか
					$targetObj->showInfo = $showInfo;			// 吹き出しを表示するかどうか
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
				$name = $this->createDefaultName();			// デフォルト登録項目名
				$width	= self::DEFAULT_WIDTH;		// 幅
				$height	= self::DEFAULT_HEIGHT;		// 高さ
				$lat	= self::DEFAULT_POS_LAT;		// 緯度
				$lng	= self::DEFAULT_POS_LNG;		// 経度
				$markerLat	= self::DEFAULT_POS_LAT;		// マーカー緯度
				$markerLng	= self::DEFAULT_POS_LNG;		// マーカー経度
				$infoLat	= self::DEFAULT_POS_LAT;		// 吹き出し緯度
				$infoLng	= self::DEFAULT_POS_LNG;		// 吹き出し経度
				$zoom	= self::DEFAULT_ZOOM;		// ズームレベル
				$infoContent = '';		// 吹き出し内容
				$showMarker = 0;		// マーカーを表示するかどうか
				$showPosControl = 1;		// 位置コントローラを表示するかどうか
				$showTypeControl = 1;		// 地図タイプコントローラを表示するかどうか
				$showInfo = 0;			// 吹き出しを表示するかどうか
			}
			$this->serialNo = 0;
		} else {
			if ($replaceNew){
				$ret = $this->getPageDefParam($defSerial, $defConfigId, $this->paramObj, $this->configId, $targetObj);
				if ($ret){
					$name = $targetObj->name;// 名前
					$width	= $targetObj->width;		// 幅
					$height	= $targetObj->height;		// 高さ
					$lat	= $targetObj->lat;		// 緯度
					$lng	= $targetObj->lng;		// 経度
					$markerLat	= $targetObj->markerLat;		// マーカー緯度
					$markerLng	= $targetObj->markerLng;		// マーカー経度
					$infoLat	= $targetObj->infoLat;		// 吹き出し緯度
					$infoLng	= $targetObj->infoLng;		// 吹き出し経度
					$zoom	= $targetObj->zoom;		// ズームレベル
					$infoContent	= $targetObj->infoContent;		// 吹き出し内容
					$showMarker = $targetObj->showMarker;		// マーカーを表示するかどうか
					$showPosControl = $targetObj->showPosControl;		// 位置コントローラを表示するかどうか
					$showTypeControl = $targetObj->showTypeControl;		// 地図タイプコントローラを表示するかどうか
					$showInfo		= $targetObj->showInfo;			// 吹き出しを表示するかどうか
				}
			}
			$this->serialNo = $this->configId;
				
			// 新規作成でないときは、メニューを変更不可にする(画面作成から呼ばれている場合のみ)
			if (!empty($defConfigId) && !empty($defSerial)) $this->tmpl->addVar("_widget", "id_disabled", 'disabled');
		}
		
		// プレビュー表示
		if (is_numeric($lat) && is_numeric($lng) && is_numeric($zoom)) $this->tmpl->setAttribute('show_script', 'visibility', 'visible');// 緯度経度が入力されている場合
		
		// 画面にデータを埋め込む
		$this->tmpl->addVar("item_name_visible", "name", $name);		// 名前
		$this->tmpl->addVar("_widget", "width",	$width);
		$this->tmpl->addVar("_widget", "height",	$height);
		$this->tmpl->addVar("_widget", "lat",	$lat);		// 緯度
		$this->tmpl->addVar("_widget", "lng",	$lng);		// 経度
		$this->tmpl->addVar("_widget", "zoom",	$zoom);		// ズームレベル
		$this->tmpl->addVar("_widget", "info_content",	$this->convertToDispString($infoContent));		// 吹き出し内容
		$this->tmpl->addVar("_widget", "marker_lat",	$markerLat);		// マーカー緯度
		$this->tmpl->addVar("_widget", "marker_lng",	$markerLng);		// マーカー経度
		$this->tmpl->addVar("_widget", "info_lat",	$infoLat);		// 吹き出し緯度
		$this->tmpl->addVar("_widget", "info_lng",	$infoLng);		// 吹き出し経度
		$this->tmpl->addVar("show_script", "lat",	$lat);		// 緯度
		$this->tmpl->addVar("show_script", "lng",	$lng);		// 経度
		$this->tmpl->addVar("show_script", "zoom",	$zoom);		// ズームレベル
		$this->tmpl->addVar("show_marker", "marker_lat",	$markerLat);		// マーカー緯度
		$this->tmpl->addVar("show_marker", "marker_lng",	$markerLng);		// マーカー経度
		$this->tmpl->addVar("show_info", "info_lat",	$infoLat);		// 吹き出し緯度
		$this->tmpl->addVar("show_info", "info_lng",	$infoLng);		// 吹き出し経度
		$this->tmpl->addVar("show_info", "info_content",	addslashes($infoContent));		// 吹き出し内容
		
		$checked = '';
		if ($showMarker){			// マーカーを表示するかどうか
			$checked = 'checked';
			
			// 緯度経度が入力されている場合はスクリプトを表示
			if (is_numeric($markerLat) && is_numeric($markerLng)) $this->tmpl->setAttribute('show_marker', 'visibility', 'visible');// マーカーを表示
		}
		$this->tmpl->addVar("_widget", "show_marker_checked", $checked);	// マーカーを表示するかどうか
		$checked = '';
		if ($showPosControl){		// 位置コントローラを表示するかどうか
			$checked = 'checked';
			$this->tmpl->setAttribute('show_pos_control', 'visibility', 'hidden');// 位置コントローラを表示
		}
		$this->tmpl->addVar("_widget", "pos_checked", $checked);	// 位置コントローラを表示するかどうか
		$checked = '';
		if ($showTypeControl){		// 地図タイプコントローラを表示するかどうか
			$checked = 'checked';
			$this->tmpl->setAttribute('show_type_control', 'visibility', 'hidden');// 地図タイプコントローラを表示
		}
		$this->tmpl->addVar("_widget", "type_checked", $checked);	// 地図タイプコントローラを表示するかどうか
		$checked = '';
		if ($showInfo){		// 吹き出しを表示するかどうか
			$checked = 'checked';
			
			// 緯度経度が入力されている場合はスクリプトを表示
			if (is_numeric($infoLat) && is_numeric($infoLng)) $this->tmpl->setAttribute('show_info', 'visibility', 'visible');// 吹き出しを表示
		}
		$this->tmpl->addVar("_widget", "show_info_checked", $checked);	// 吹き出しを表示するかどうか
		$this->tmpl->addVar("_widget", "coding_url",	self::CODING_URL);		// 緯度経度取得用URL
				
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
		for ($i = 0; $i < count($this->paramObj); $i++){
			$id = $this->paramObj[$i]->id;// 定義ID
			$targetObj = $this->paramObj[$i]->object;
			$name = $targetObj->name;// 定義名
			$selected = '';

			if (empty($id)) continue;// 定義ID=0は一覧表示しない
			
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
	 * @return								なし
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
		
		$this->tmpl->addVar("_widget", "serial_list", implode($this->serialArray, ','));// 表示項目のシリアル番号を設定
		
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
		$index = 0;
		for ($i = 0; $i < count($this->paramObj); $i++){
			$id = $this->paramObj[$i]->id;// 定義ID
			$targetObj = $this->paramObj[$i]->object;
			$name = $targetObj->name;// 定義名
			
			if (empty($id)) continue;// 定義ID=0は一覧表示しない
			
			$defCount = 0;
			if (!empty($id)){
				$defCount = $this->_db->getPageDefCount($this->gEnv->getCurrentWidgetId(), $id);
			}
			$operationDisagled = '';
			if ($defCount > 0) $operationDisagled = 'disabled';
			
			// 値が設定されていないときはデフォルト値を設定
			$markerLat = $targetObj->markerLat;
			if ($markerLat == '')	$markerLat	= 0;		// マーカー緯度
			$markerLng = $targetObj->markerLng;
			if ($markerLng == '')	$markerLng	= 0;		// マーカー経度
			$infoLat = $targetObj->infoLat;
			if ($infoLat == '')		$infoLat	= 0;		// 吹き出し緯度
			$infoLng = $targetObj->infoLng;
			if ($infoLng == '')		$infoLng	= 0;		// 吹き出し経度

			$row = array(
				'index' => $index,
				'ope_disabled' => $operationDisagled,			// 選択可能かどうか
				'name' => $this->convertToDispString($name),		// 名前
				'width' => $targetObj->width,					// 動画幅
				'height' => $targetObj->height,					// 動画高さ
				'lat' => $targetObj->lat,		// 緯度
				'lng' => $targetObj->lng,		// 経度
				'marker_lat' => $markerLat,		// マーカー緯度
				'marker_lng' => $markerLng,		// マーカー経度
				'info_lat' => $infoLat,		// 吹き出し緯度
				'info_lng' => $infoLng,		// 吹き出し経度
				'zoom' => $targetObj->zoom,		// ズームレベル
				'info_content' => $this->convertToDispString($targetObj->infoContent),		// 吹き出し内容
				'marker' => $targetObj->showMarker,		// マーカーを表示するかどうか
				'info' => $targetObj->showInfo,			// 吹き出しを表示するかどうか
				'pos_control' => $targetObj->showPosControl,		// 位置コントローラを表示するかどうか
				'type_control' => $targetObj->showTypeControl,		// 地図タイプコントローラを表示するかどうか
				'def_count' => $defCount							// 使用数
			);
			$this->tmpl->addVars('itemlist', $row);
			$this->tmpl->parseTemplate('itemlist', 'a');
			
			// シリアル番号を保存
			$this->serialArray[] = $id;
			$index++;		// 項目番号更新
		}
	}
	/**
	 * JavascriptファイルをHTMLヘッダ部に設定
	 *
	 * JavascriptファイルをHTMLのheadタグ内に追加出力する。
	 * _assign()よりも後に実行される。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。
	 * @return string 						Javascriptファイル。出力しない場合は空文字列を設定。
	 */
	function _addScriptFileToHead($request, &$param)
	{
		$scriptUrl = $this->getUrl('http://maps.google.com/maps/api/js?sensor=false');
		return $scriptUrl;
	}
}
?>
