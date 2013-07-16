<?php
/**
 * index.php用コンテナクラス
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2012 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: admin_ec_mainCalcorderWidgetContainer.php 5440 2012-12-08 09:37:39Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() . '/admin_ec_mainBaseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() .	'/ec_mainIWidgetDb.php');

class admin_ec_mainCalcorderWidgetContainer extends admin_ec_mainBaseWidgetContainer
{
	private $db;	// DB接続オブジェクト
	private $langId;		// 表示言語
	private $iWidgetId;	// インナーウィジェットID
	private $serialNo;		// 選択中の項目のシリアル番号
	private $serialArray = array();		// 表示されている項目シリアル番号
	const METHOD_TYPE = 'CALCORDER';		// インナーウィジェットタイプ
	const IWIDGET_TYPE = 'CALCORDER';		// インナーウィジェットタイプ(注文計算)
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->db = new ec_mainIWidgetDb();
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
		if ($task == 'calcorder_detail'){		// 詳細画面
			return 'admin_calcorder_detail.tmpl.html';
		} else {			// 一覧画面
			return 'admin_calcorder.tmpl.html';
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
		if ($task == 'calcorder_detail'){	// 詳細画面
			return $this->createDetail($request);
		} else {			// 一覧画面
			return $this->createList($request);
		}
	}
	/**
	 * 一覧画面作成
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param								なし
	 */
	function createList($request)
	{
		$this->langId	= $this->gEnv->getCurrentLanguage();		// 表示言語を取得
		$act = $request->trimValueOf('act');
		
		if ($act == 'delete'){		// 項目削除の場合
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
				$ret = $this->db->deleteMethodBySerial($delItems);
				if ($ret){		// データ削除成功のとき
					$this->setGuidanceMsg('データを削除しました');
				} else {
					$this->setAppErrorMsg('データ削除に失敗しました');
				}
			}
		}
		// 一覧を表示
		$this->db->getAllMethod(self::METHOD_TYPE, $this->langId, 0/*デフォルトのセットID*/, array($this, 'methodLoop'));
		
		$this->tmpl->addVar("_widget", "serial_list", implode($this->serialArray, ','));// 表示項目のシリアル番号を設定
	}
	/**
	 * 詳細画面作成
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param								なし
	 */
	function createDetail($request)
	{
		$this->langId	= $this->gEnv->getCurrentLanguage();		// 表示言語を取得
		$act = $request->trimValueOf('act');
		$this->serialNo = $request->trimValueOf('serial');		// 選択項目のシリアル番号
		if (empty($this->serialNo)) $this->serialNo = 0;
				
		$this->id	= $request->trimValueOf('id');	// ID
		$name	= $request->trimValueOf('item_name');	// 名前
		$descShort	= $request->trimValueOf('item_desc_short');	// 簡易説明
		$desc	= $request->valueOf('item_desc');	// 説明
		$index	= $request->trimValueOf('item_index');	// 表示順
		$visible	= ($request->trimValueOf('item_visible') == 'on') ? 1 : 0;	// 表示状態
		$this->iWidgetId	= $request->trimValueOf('item_iwidget');	// インナーウィジェットID
		$param = '';			// インナーウィジェット用パラメータ
		
		$replaceNew = false;		// データを再取得するかどうか
		if ($act == 'add'){		// 新規追加のとき
			// 入力チェック
			$this->checkInput($name, '表示名');
			$this->checkNumeric($index, '表示順');

			// インナーウィジェット更新
			if ($this->getMsgCount() == 0){
				if (!empty($this->iWidgetId)){
					// インナーウィジェットでのパラメータの更新に成功した場合
					$this->updateIWidgetParam($this->iWidgetId, $this->id, $param, $optionParam, true);
					
					// インナーウィジェット内で発生したエラーを取得
					$this->getGlobalMsg();
				}
			}
			
			// エラーなしの場合は、データを更新
			if ($this->getMsgCount() == 0){
				$addid = 0;		// 新規追加
				$ret = $this->db->updateMethod($addid, self::METHOD_TYPE, $this->langId, 0/*デフォルトのセットID*/, $name, $descShort, $desc, $index, $visible, $this->iWidgetId, $param);
				if ($ret){		// データ追加成功のとき
					$this->setMsg(self::MSG_GUIDANCE, 'データを追加しました');
					
					$this->id = $addid;		// 新規IDに更新
					$replaceNew = true;			// データを再取得
				} else {
					$this->setMsg(self::MSG_APP_ERR, 'データ追加に失敗しました');
				}
			}
		} else if ($act == 'update'){		// 行更新のとき
			// 入力チェック
			$this->checkInput($name, '表示名');
			$this->checkNumeric($index, '表示順');
			
			// インナーウィジェット更新
			if ($this->getMsgCount() == 0){
				if (!empty($this->iWidgetId)){
					// インナーウィジェットでのパラメータの更新に成功した場合
					$this->updateIWidgetParam($this->iWidgetId, $this->id, $param, $optionParam, true);
					
					// インナーウィジェット内で発生したエラーを取得
					$this->getGlobalMsg();
				}
			}
			
			// エラーなしの場合は、データを更新
			if ($this->getMsgCount() == 0){
				$ret = $this->db->updateMethod($this->id, self::METHOD_TYPE, $this->langId, 0/*デフォルトのセットID*/, $name, $descShort, $desc, $index, $visible, $this->iWidgetId, $param);
				if ($ret){		// 更新成功のとき
					$this->setMsg(self::MSG_GUIDANCE, 'データを更新しました');
					$replaceNew = true;			// データを再取得
				} else {
					$this->setMsg(self::MSG_APP_ERR, 'データ更新に失敗しました');
				}
			}
		} else if ($act == 'delete'){		// 削除のとき
			$ret = $this->db->deleteMethodBySerial(array($this->serialNo));
			if ($ret){		// データ更新成功のとき
				$this->setMsg(self::MSG_GUIDANCE, '項目を削除しました');
			} else {
				$this->setMsg(self::MSG_APP_ERR, '項目削除に失敗しました');
			}
		} else if ($act == 'selectcalc'){		// 計算方法選択のとき
			if (!empty($this->iWidgetId)){
				// 初期パラメータをインナーウィジェットに設定
				$optionParam = new stdClass;
				$optionParam->init = true;		// 初期データ取得
				$this->setIWidgetParam($this->iWidgetId, $this->id, $param, $optionParam, true);
				//$this->setIWidgetParam($this->iWidgetId, $this->id, $row['id_param'], $optionParam, true);
			}
		} else if ($act == 'selectmenu'){		// メニュー選択のとき
			// 何もしない
		} else {		// 初期状態
			// シリアル番号からIDを取得
			$this->id = $this->db->getMethodIdBySerial($this->serialNo);			// 選択中の配送項目ID
			if (empty($this->id)){	// 空のときは新規とする
				$this->serialNo = 0;
				
				$name	= '';	// 名前
				$descShort	= '';	// 簡易説明
				$desc	= '';	// 説明
				$index	= $this->db->getMaxMethodIndex(self::METHOD_TYPE, $this->langId, 0/*デフォルトのセットID*/) + 1;	// 表示順
				$visible	= 1;	// 表示状態
				$this->iWidgetId	= '';	// インナーウィジェットID
			} else {
				$replaceNew = true;			// データを再取得
			}
		}
		// データを再取得のとき
		if ($replaceNew){
			$ret = $this->db->getMethod(self::METHOD_TYPE, $this->id, $this->langId, 0/*デフォルトのセットID*/, $row);
			if ($ret){
				$name		= $row['id_name'];	// 名前
				$descShort		= $row['id_desc_short'];	// 簡易説明
				$desc		= $row['id_desc'];	// 説明
				$index		= $row['id_index'];	// 表示順
				$visible	= $row['id_visible'];	// 表示状態
				$this->iWidgetId	= $row['id_iwidget_id'];	// インナーウィジェットID
				if (!empty($this->iWidgetId)){
					// パラメータをインナーウィジェットに設定
					$optionParam = new stdClass;
					$optionParam->init = true;		// 初期データ取得
					$this->setIWidgetParam($this->iWidgetId, $this->id, $row['id_param'], $optionParam, true);
				}
			}
		}
		
		if (empty($this->serialNo)){		// シリアル番号が空のときは新規とする
			$this->tmpl->addVar("_widget", "id_label", '新規');			// 選択項目のIDラベル
			$this->tmpl->addVar("_widget", "new_selected", 'checked');// ユーザIDが0のときは新規追加をチェック状態にする
			
//			$this->tmpl->setAttribute('add_id_field', 'visibility', 'visible');// 新規ID入力フィールド表示
			$this->tmpl->setAttribute('add_button', 'visibility', 'visible');// 新規登録ボタン表示
		} else {
			$this->tmpl->addVar("_widget", "id_label", $this->id);			// 選択項目のIDラベル
			$this->tmpl->setAttribute('update_button', 'visibility', 'visible');// 更新ボタン表示
		}
		$this->tmpl->addVar("_widget", "serial", $this->serialNo);
		$this->tmpl->addVar("_widget", "id", $this->id);			// ID
		$this->tmpl->addVar("_widget", "name", $name);		// 名前
		$this->tmpl->addVar("_widget", "index", $index);	// 表示順
		$this->tmpl->addVar("_widget", "desc_short", $descShort);	// 簡易説明
		$this->tmpl->addVar("_widget", "desc", $desc);	// 説明
		$visibleStr = '';
		if ($visible){	// 項目の表示
			$visibleStr = 'checked';
		}
		$this->tmpl->addVar("_widget", "visible", $visibleStr);		// 表示状態
		
		// インナーウィジェット選択メニューを作成
		$this->_db->getAllIWidgetListByType($this->gEnv->getCurrentWidgetId(), self::IWIDGET_TYPE, array($this, 'iWidgetLoop'));
		
		// 選択中のインナーウィジェットの管理画面を取得
		if (!empty($this->iWidgetId)){
			$innerContent = $this->getIWidgetContent($this->iWidgetId, $this->id, true);	// 管理者画面を取得
			$this->tmpl->addVar("_widget", "iwidget", $innerContent);
		}
	}
	/**
	 * 取得した配送方法定義をテンプレートに設定する
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function methodLoop($index, $fetchedRow, $param)
	{
		$checked = '';
		if ($fetchedRow['id_id'] == $this->id){
			$checked = 'checked';
		}
		$visible = '';
		if ($fetchedRow['id_visible']){	// 項目の表示
			$visible = 'checked';
		}
		$row = array(
			'serial' => $fetchedRow['id_serial'],								// シリアル番号
			'index' => $index,													// 項目番号
			'id'     => $this->convertToDispString($fetchedRow['id_id']),			// ID
			'name'     => $this->convertToDispString($fetchedRow['id_name']),			// 表示名
			'view_index'     => $this->convertToDispString($fetchedRow['id_index']),			// 表示順
			'visible' => $visible,											// メニュー項目表示制御
			'checked' => $checked														// 選択中かどうか
		);
		$this->tmpl->addVars('calcorder_list', $row);
		$this->tmpl->parseTemplate('calcorder_list', 'a');
		
		// 表示中項目のシリアル番号を保存
		$this->serialArray[] = $fetchedRow['id_serial'];
		return true;
	}
	/**
	 * 取得した配送方法インナーウィジェットをテンプレートに設定する
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function iWidgetLoop($index, $fetchedRow, $param)
	{
		$id = $fetchedRow['iw_widget_id'] . ',' . $fetchedRow['iw_id'];
		$selected = '';
		if ($id == $this->iWidgetId){		// 選択中のインナーウィジェット
			$selected = 'selected';
		}

		$row = array(
			'value'    => $id,			// ID
			'name'     => $this->convertToDispString($fetchedRow['iw_name']),			// 表示名
			'selected' => $selected														// 選択中かどうか
		);
		$this->tmpl->addVars('iwidget_list', $row);
		$this->tmpl->parseTemplate('iwidget_list', 'a');
		return true;
	}
}
?>
