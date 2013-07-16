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
 * @copyright  Copyright 2006-2008 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: admin_mainEdittableWidgetContainer.php 383 2008-03-13 05:12:24Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() . '/admin_mainTableBaseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/admin_tableDb.php');

class admin_mainEdittableWidgetContainer extends admin_mainTableBaseWidgetContainer
{
	private $db;	// DB接続オブジェクト
	private $serialNo;	// シリアルNo
	private $serialArray = array();		// 表示されているコンテンツシリアル番号
	private $tableId;		// 現在選択中のテーブル
	private $defaultTableId;	// デフォルトのテーブル
	private $tableFields = array();	// テーブルフィールド名
	const DEFAULT_LIST_COUNT = 30;			// 最大リスト表示数
	const VIEW_STR_LENGTH = 30;			// 表示文字数
	const ADD_STR = '...';				// 文字列を省略する場合の記号
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DB接続オブジェクト作成
		$this->db = new admin_tableDb();
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
		if ($task == 'edittable_detail'){		// 詳細画面
			return 'edittable_detail.tmpl.html';
		} else {
			return 'edittable.tmpl.html';
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
		if ($task == 'edittable_detail'){	// 詳細画面
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
		global $gEnvManager;
		global $TABLE_FIELDS;
		
		$act = $request->trimValueOf('act');
		$tableName = $request->trimValueOf('item_tableid');		// テーブル名
		$this->tableId = $request->trimValueOf('tableid');		// テーブルID
		
		// テーブルが指定されていないときは最初のテーブルを選択状態にする
		$this->db->getAllTableIdList(array($this, 'defaultTableIdLoop'));
		if (empty($this->tableId)) $this->tableId = $this->defaultTableId;	
		
		// テーブルのフィールド名を取得
		if (!empty($this->tableId)){
			// フィールド名の定義ファイルを読み込む
			$fieldScriptPath = $gEnvManager->getTablesPath() . '/' . $this->tableId . '/index.php';
			if (file_exists($fieldScriptPath)){
				require_once($fieldScriptPath);
				$this->tableFields = $TABLE_FIELDS;
			}
		}
		$reloadData = false;		// データの再読み込み
		if ($act == 'update'){		// 行更新のとき
			// 入力チェック
		} else if ($act == 'delete'){		// 削除のとき
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
				$ret = $this->db->deleteTableDataBySerial($this->tableId, $delItems);
				if ($ret){		// データ削除成功のとき
					$this->setMsg(self::MSG_GUIDANCE, 'データを削除しました');
				} else {
					$this->setMsg(self::MSG_APP_ERR, 'データ削除に失敗しました');
				}
			}
		} else if ($act == 'seltable'){		// テーブル名選択のとき
		}
		// テーブル名一覧取得
		$this->db->getAllTableIdList(array($this, 'tableIdLoop'));
		
		// テーブル存在チェック
		$tableExists = $this->db->isTableExists($this->tableId);
		if ($tableExists){
			// ###### ページリンク作成 #####
			$maxListCount = self::DEFAULT_LIST_COUNT;				// 表示項目数
			$pageNo = $request->trimValueOf('page');				// ページ番号
			if (empty($pageNo)) $pageNo = 1;
		
			// 総数を取得
			$totalCount = $this->db->getTableDataListCount($this->tableId);

			// 表示するページ番号の修正
			$pageCount = (int)(($totalCount -1) / $maxListCount) + 1;		// 総ページ数
			if ($pageNo < 1) $pageNo = 1;
			if ($pageNo > $pageCount) $pageNo = $pageCount;
			$this->firstNo = ($pageNo -1) * $maxListCount + 1;		// 先頭番号
	
			// ページング用リンク作成
			$pageLink = '';
			if ($pageCount > 1){	// ページが2ページ以上のときリンクを作成
				for ($i = 1; $i <= $pageCount; $i++){
					if ($i == $pageNo){
						$link = '&nbsp;' . $i;
					} else {
						$link = '&nbsp;<a href="#" onclick="selpage(\'' . $i . '\');return false;">' . $i . '</a>';
					}
					$pageLink .= $link;
				}
			}
			$startNo = ($pageNo -1) * $maxListCount +1;
			$endNo = $pageNo * $maxListCount > $totalCount ? $totalCount : $pageNo * $maxListCount;
			$this->tmpl->addVar("_widget", "page_link", $pageLink);
			$this->tmpl->addVar("_widget", "total_count", $totalCount);
			$this->tmpl->addVar("_widget", "page", $pageNo);	// ページ番号
			$this->tmpl->addVar("list_range", "start_no", $startNo);
			$this->tmpl->addVar("list_range", "end_no", $endNo);
			if ($totalCount > 0) $this->tmpl->setAttribute('list_range', 'visibility', 'visible');// 検出範囲を表示
		
			// テーブルヘッダ作成
			for ($i = 0; $i < count($this->tableFields); $i++){
				$name = $this->tableFields[$i]['name'];
				$fieldName = $this->tableFields[$i]['id'];
				if (empty($name)) $name = '(名称未設定' . ($i + 1) . ')';
				$row = array('name' => $name,
								'field_name' => $fieldName);
				$this->tmpl->addVars('headlist', $row);
				$this->tmpl->parseTemplate('headlist', 'a');
			}
			// テーブルデータ部作成
			$this->db->getTableData($this->tableId, $maxListCount, $pageNo, array($this, 'tableBodyLoop'));
			if (count($this->serialArray) > 0) $this->tmpl->setAttribute('showbody', 'visibility', 'visible');// データがあるとき表示
			// テーブル定義一覧表示
			if (empty($this->tableId)){// テーブル新規追加のとき
			} else {
				//$this->tmpl->setAttribute('table_def', 'visibility', 'visible');
		//		$this->db->getTableDef($this->tableId, array($this, 'fieldListLoop'));
			}
		} else {
			$this->setMsg(self::MSG_GUIDANCE, 'テーブルが存在しません');
		}
		
		// 値を戻す
		$this->tmpl->addVar("_widget", "table_id", $this->tableId);
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
		global $gEnvManager;
		global $TABLE_FIELDS;
		
		$act = $request->trimValueOf('act');
		$this->serialNo = $request->trimValueOf('serial');		// 選択項目のシリアル番号
		$this->tableId = $request->trimValueOf('tableid');		// テーブルID

		// テーブル存在チェック
		$tableExists = $this->db->isTableExists($this->tableId);
		if (!$tableExists){
			$this->setMsg(self::MSG_GUIDANCE, 'テーブルが存在しません');
			return;
		}
		
		// テーブルのフィールド名を取得
		// フィールド名の定義ファイルを読み込む
		$fieldScriptPath = $gEnvManager->getTablesPath() . '/' . $this->tableId . '/index.php';
		if (file_exists($fieldScriptPath)){
			require_once($fieldScriptPath);
			$this->tableFields = $TABLE_FIELDS;
		}
		
		if ($act == 'add'){		// データ新規追加のとき
			// 入力チェック
			
			// エラーなしの場合は、データを登録
			if ($this->getMsgCount() == 0){
				$addValues = array();		// 追加データ
				for ($i = 0; $i < count($this->tableFields); $i++){
					$fieldName = $this->tableFields[$i]['id'];
					$dataType = $this->tableFields[$i]['type'];
					$itemName = 'item_' . $fieldName;
					
					// 設定データのエラーチェック、エラーの場合は設定しない
					$dataError = false;		// エラーなしにリセット
					$itemValue = $request->trimValueOf($itemName);
					if (strncasecmp($dataType, 'int', strlen('int')) == 0){// int型のとき
						if (is_numeric($itemValue)){		// 数値変換可能なとき
							$itemValue = intval($itemValue);		// 整数に変換
						} else {
							$dataError = true;		// エラーあり
						}
					}
					// データエラーでないとき格納
					if (!$dataError) $addValues[$fieldName] = $itemValue;
				}
				// データ追加
				$ret = $this->db->addTableData($this->tableId, $addValues, $newSerial);
				if ($ret){		// データ追加成功のとき
					$this->setMsg(self::MSG_GUIDANCE, 'データを追加しました');
					$this->serialNo = $newSerial;
					$reloadData = true;		// データの再読み込み
				} else {
					$this->setMsg(self::MSG_APP_ERR, 'データ追加に失敗しました');
				}
			}
		} else if ($act == 'update'){		// 行更新のとき
			// 入力チェック
			// エラーなしの場合は、データを登録
			if ($this->getMsgCount() == 0){
				$updateValues = array();		// 追加データ
				for ($i = 0; $i < count($this->tableFields); $i++){
					$fieldName = $this->tableFields[$i]['id'];
					$dataType = $this->tableFields[$i]['type'];
					$itemName = 'item_' . $fieldName;
					
					// 設定データのエラーチェック、エラーの場合は設定しない
					$dataError = false;		// エラーなしにリセット
					$itemValue = $request->trimValueOf($itemName);
					if (strncasecmp($dataType, 'int', strlen('int')) == 0){// int型のとき
						if (is_numeric($itemValue)){		// 数値変換可能なとき
							$itemValue = intval($itemValue);		// 整数に変換
						} else {
							$dataError = true;		// エラーあり
						}
					}
					// データエラーでないとき格納
					if (!$dataError) $updateValues[$fieldName] = $itemValue;
				}
				// データ更新
				$ret = $this->db->updateTableData($this->tableId, $this->serialNo, $updateValues);
				if ($ret){		// データ追加成功のとき
					$this->setMsg(self::MSG_GUIDANCE, 'データを更新しました');
					$reloadData = true;		// データの再読み込み
				} else {
					$this->setMsg(self::MSG_APP_ERR, 'データ更新に失敗しました');
				}
			}
		} else if ($act == 'delete'){		// 削除のとき
			// 削除前のデータを取得
			$ret = $this->db->getTableDataBySerial($this->tableId, $this->serialNo, $dataRow);
			
			// データを削除
			if ($ret) $ret = $this->db->deleteTableDataBySerial($this->tableId, array($this->serialNo));
			if ($ret){		// データ削除成功のとき
				$this->setMsg(self::MSG_GUIDANCE, 'データを削除しました');
				
				// ボタンを使用不可に設定
				$this->tmpl->addVar("update_button", "del_button_enabled", "disabled");
				$this->tmpl->addVar("update_button", "update_button_enabled", "disabled");
			} else {
				$this->setMsg(self::MSG_APP_ERR, 'データ削除に失敗しました');
			}
		} else {
			$reloadData = true;
		}
		if ($reloadData){		// データ再取得のとき
			// データを取得
			$ret = $this->db->getTableDataBySerial($this->tableId, $this->serialNo, $dataRow);
			if ($ret){
			}
		}
		// 詳細部分を作成
		for ($i = 0; $i < count($this->tableFields); $i++){
			// 行カラーの設定
			$lineColor = '';
			if ($i % 2 != 0){
				$lineColor = 'class="even"';		// 偶数行
			}
	
			// テーブルヘッダ作成
			$name = $this->tableFields[$i]['name'];
			$fieldName = $this->tableFields[$i]['id'];
			$dataType = $this->tableFields[$i]['type'];
			if (empty($name)) $name = '(名称未設定' . ($i + 1) . ')';
			
			// レコードデータ
			if (empty($this->serialNo)){		// 空のときは新規とする
				$value = '';
			} else {
				$value = $dataRow[$fieldName];
			}
			$row = array('name' => $name,
							'field_name' => $fieldName,
							'data_type' => $dataType,
							'value' => $value,					// レコードデータ
							'line_color' => $lineColor);		// 行のカラー
			$this->tmpl->addVars('bodylist', $row);
			$this->tmpl->parseTemplate('bodylist', 'a');
		}
		
		if (empty($this->serialNo)){		// 空のときは新規とする
			$this->tmpl->setAttribute('add_button', 'visibility', 'visible');// 新規登録ボタン表示
		} else {
			$this->tmpl->setAttribute('update_button', 'visibility', 'visible');// 更新ボタン表示
		}
		// 値を戻す
		$this->tmpl->addVar("_widget", "table_id", $this->tableId);
		$this->tmpl->addVar("_widget", "serial", $this->serialNo);		// シリアル番号
	}
	/**
	 * 取得したデータをテンプレートに設定する
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function tableBodyLoop($index, $fetchedRow, $param)
	{
		// 行カラーの設定
		$lineColor = '';
		if ($index % 2 != 0){
			$lineColor = 'class="even"';		// 偶数行
		}
		
		// 行を作成
		$this->tmpl->clearTemplate('tableline');
		for ($i = 0; $i < count($this->tableFields); $i++){
			$id = $this->tableFields[$i]['id'];		// フィールド名
			
			// 表示データ
			$addValue = '';
			$srcValue = $fetchedRow[$id];
			if (function_exists('mb_substr')){
				if (mb_strlen($srcValue) > self::VIEW_STR_LENGTH) $addValue = self::ADD_STR;
				$value = mb_substr($srcValue, 0, self::VIEW_STR_LENGTH) . $addValue;
			} else {
				if (strlen($srcValue) > self::VIEW_STR_LENGTH) $addValue = self::ADD_STR;
				$value = substr($srcValue, 0, self::VIEW_STR_LENGTH) . $addValue;
			}
			$tableLine = array(
				'index'		=> $index,			// 行番号
				'col_no'	=> $i,				// カラム番号
				'value'	=> $this->convertToDispString($value)		// データ
			);
			$this->tmpl->addVars('tableline', $tableLine);
			$this->tmpl->parseTemplate('tableline', 'a');
		}
			
		$row = array(
			'index'			=> $index,
			'line_color'	=> $lineColor											// 行のカラー
		);
		$this->tmpl->addVars('bodylist', $row);
		$this->tmpl->parseTemplate('bodylist', 'a');
		
		// 表示中データのシリアル番号を保存
		$this->serialArray[] = $fetchedRow['_serial'];
		return true;
	}
	/**
	 * テーブル名一覧を作成
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function tableIdLoop($index, $fetchedRow, $param)
	{
		// デフォルトのテーブル
		if ($index == 0) $this->defaultTableId = $fetchedRow['td_table_id'];
		
		$selected = '';
		if ($fetchedRow['td_table_id'] == $this->tableId){
			$selected = 'selected';
		}

		$row = array(
			'value'    => $this->convertToDispString($fetchedRow['td_table_id']),
			'name'     => $this->convertToDispString($fetchedRow['td_table_id']),
			'selected' => $selected														// 選択中かどうか
		);
		$this->tmpl->addVars('table_id_list', $row);
		$this->tmpl->parseTemplate('table_id_list', 'a');
		return true;
	}
	/**
	 * テーブル名一覧を作成
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function defaultTableIdLoop($index, $fetchedRow, $param)
	{
		// デフォルトのテーブル
		$this->defaultTableId = $fetchedRow['td_table_id'];
		return false;
	}
}
?>
