<?php
/**
 * DBクラス
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2008 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: admin_tableDb.php 384 2008-03-14 05:09:32Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getDbPath() . '/baseDb.php');

class admin_tableDb extends BaseDb
{	
	/**
	 * テーブル定義情報にフィールド追加
	 *
	 * @param string $tableName		テーブル名
	 * @param string $fieldName		フィールド名(空文字列=テーブル名保持用)
	 * @param string $dispName		項目表示名
	 * @param string $dataType		データ型
	 * @param string $defaultValue	デフォルト値
	 * @return						true = 正常、false=異常
	 */
	function addTableField($tableName, $fieldName, $dispName, $dataType, $defaultValue)
	{
		global $gEnvManager;
		
		// 引数エラーチェック
		if (empty($tableName)) return false;
		
		// トランザクション開始
		$this->startTransaction();
			
		// 追加用のインデックスNoを作成
		$index = 0;
		$queryStr  = 'SELECT * FROM _table_def ';
		$queryStr .=   'WHERE td_table_id = ? ';
		$ret = $this->isRecordExists($queryStr, array($tableName));
		if ($ret){
			$queryStr  = 'SELECT max(td_index) as ms FROM _table_def ';
			$queryStr .=   'WHERE td_table_id = ? ';
			$ret = $this->selectRecord($queryStr, array($tableName), $row);
			if ($ret) $index = $row['ms'] + 1;
		}

		// 新規レコード追加
		$queryStr = 'INSERT INTO _table_def ';
		$queryStr .=  '(';
		$queryStr .=  'td_table_id, ';
		$queryStr .=  'td_id, ';
		$queryStr .=  'td_index, ';
		$queryStr .=  'td_type, ';
		$queryStr .=  'td_name, ';
		$queryStr .=  'td_default_value) ';
		$queryStr .=  'VALUES ';
		$queryStr .=  '(?, ?, ?, ?, ?, ?)';
		$ret =$this->execStatement($queryStr, array($tableName, $fieldName, $index, $dataType, $dispName, $defaultValue));
			
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * フィールド定義の削除
	 *
	 * @param int $serial			シリアル番号
	 * @return bool					true=成功、false=失敗
	 */
	function deleteTableField($serial)
	{
		// テーブルID取得
		$queryStr  = 'select * from _table_def ';
		$queryStr .=   'where td_serial = ? ';
		$ret = $this->selectRecord($queryStr, array($serial), $row);
		if (!$ret) return false;
		$tableId = $row['td_table_id'];
		
		// トランザクション開始
		$this->startTransaction();
		
		// データ削除
		$queryStr = 'DELETE FROM _table_def ';
		$queryStr .=  'WHERE td_serial = ? ';
		$this->execStatement($queryStr, array($serial));
		
		// インデックス番号を更新
		$queryStr  = 'SELECT * FROM _table_def ';
		$queryStr .=   'WHERE td_table_id = ? ';
		$queryStr .=   'ORDER BY td_index';
		$retValue = $this->selectRecords($queryStr, array($tableId), $rows);
		
		for ($i = 0; $i < count($rows); $i++){
			$queryStr  = 'UPDATE _table_def ';
			$queryStr .=   'SET td_index = ? ';	// インデックス
			$queryStr .=   'WHERE td_serial = ?';
			$ret = $this->execStatement($queryStr, array($i, $rows[$i]['td_serial']));
			if (!$ret){
				$this->endTransaction();
				return false;
			}
		}
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * フィールド定義の一括更新
	 *
	 * @param array	  $updateValues		更新データ
	 * @return bool						true=成功、false=失敗
	 */
	function updateTableField($updateValues)
	{	
		// トランザクション開始
		$this->startTransaction();

		for ($j = 0; $j < count($updateValues); $j++){
			// 1レコード取得
			$line = $updateValues[$j];
			
			// キーを取得
			$keys = array_keys($line);
				
			// レコード更新
			$queryStr = 'UPDATE _table_def ';
			$queryStr .=  'SET ';
			$values = array();
			for ($i = 0; $i < count($keys); $i++){
				if ($keys[$i] != 'td_serial'){
					$queryStr .= $keys[$i] . ' = ?, ';
					$values[] = $line[$keys[$i]];
				}
			}
			$queryStr = substr($queryStr, 0, strlen($queryStr) -2);// 最後の「, 」を削除
			$queryStr .= ' ';
			$queryStr .=  'WHERE td_serial = ? ';
			$values[] = $line['td_serial'];
			$ret =$this->execStatement($queryStr, $values);
			if (!$ret){
				$this->endTransaction();
				return false;
			}
		}
			
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * テーブル定義情報の一覧を取得
	 *
	 * @param string	$tableName			テーブル名
	 * @param function	$callback			コールバック関数
	 * @return 			なし
	 */
	function getTableDef($tableName, $callback)
	{
		$queryStr = 'SELECT * FROM _table_def ';
		$queryStr .=  'WHERE td_table_id = ? ';
		$queryStr .=  'AND td_id != \'\' ';		// 空フィールド以外
		$queryStr .=  'ORDER BY td_index';
		$this->selectLoop($queryStr, array($tableName), $callback, null);
	}
	/**
	 * テーブル定義IDの一覧を取得
	 *
	 * @param function	$callback			コールバック関数
	 * @return 			なし
	 */
	function getAllTableIdList($callback)
	{
		$queryStr = 'SELECT DISTINCT td_table_id FROM _table_def ';
		$queryStr .=  'ORDER BY td_serial desc';
		$this->selectLoop($queryStr, array(), $callback, null);
	}
	/**
	 * フィールド名の存在チェック
	 *
	 * @param string  $tableName	テーブル名
	 * @param string  $fieldName	フィールド名
	 * @return bool					true=存在する、false=存在しない
	 */
	function isExistsField($tableName, $fieldName)
	{
		$queryStr = 'SELECT * FROM _table_def ';
		$queryStr .=  'WHERE td_table_id = ? ';
		$queryStr .=  'AND td_id = ? ';
		return $this->isRecordExists($queryStr, array($tableName, $fieldName));
	}
	/**
	 * データ取得
	 *
	 * @param string  $tableName	テーブル名
	 * @param int		$limit				取得する項目数
	 * @param int		$page				取得するページ(1～)
	 * @param function	$callback	コールバック関数
	 * @return						true=正常、false=異常
	 */
	function getTableData($tableName, $limit, $page, $callback)
	{
		$offset = $limit * ($page -1);
		if ($offset < 0) $offset = 0;
		
		$queryStr  = 'SELECT * FROM ' . $tableName . ' ';
		$queryStr .=   'ORDER BY _serial ';
		$queryStr .=   'limit ' . $limit . ' offset ' . $offset;
		$this->selectLoop($queryStr, array(), $callback, null);
	}
	/**
	 * データ総数取得
	 *
	 * @param string  $tableName	テーブル名
	 * @return int					総数
	 */
	function getTableDataListCount($tableName)
	{
		$queryStr  = 'SELECT * FROM ' . $tableName . ' ';
		return $this->selectRecordCount($queryStr, array());
	}
	/**
	 * シリアル番号でデータ取得
	 *
	 * @param string  $tableName	テーブル名
	 * @param int $serialNo			シリアル番号
	 * @param array $row			取得データ
	 * @return						true=正常、false=異常
	 */
	function getTableDataBySerial($tableName, $serialNo, &$row)
	{
		$queryStr  = 'SELECT * FROM ' . $tableName . ' ';
		$queryStr .=  'WHERE _serial = ? ';
		$ret = $this->selectRecord($queryStr, array($serialNo), $row);
		return $ret;
	}
	/**
	 * データ新規追加
	 *
	 * @param string  $tableName	テーブル名
	 * @param array	  $addValues	追加データ
	 * @param int     $newSerial	追加成功時のシリアル番号
	 * @return						true=正常、false=異常
	 */
	function addTableData($tableName, $addValues, &$newSerial)
	{
		// トランザクション開始
		$this->startTransaction();

		$keys = array_keys($addValues);
		
		// 新規レコード追加
		$queryStr = 'INSERT INTO ' . $tableName;
		$queryStr .=  '(';
		
		$valueStr = '(';
		$values = array();
		for ($i = 0; $i < count($keys); $i++){
			if ($i < count($keys) -1){
				$queryStr .= $keys[$i] . ', ';
				$valueStr .= '?, ';
				$values[] = $addValues[$keys[$i]];
			} else {
				$queryStr .= $keys[$i] . ') ';
				$valueStr .= '?) ';
				$values[] = $addValues[$keys[$i]];
			}
		}
		$queryStr .=  'VALUES ';
		$queryStr .=  $valueStr;
		$ret =$this->execStatement($queryStr, $values);
		if (!$ret){
			$this->endTransaction();
			return false;
		}
		
		$queryStr  = 'SELECT max(_serial) as ms FROM ' . $tableName;
		$ret = $this->selectRecord($queryStr, array(), $row);
		if ($ret) $newSerial = $row['ms'];
			
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * データ更新
	 *
	 * @param string  $tableName		テーブル名
	 * @param int     $serialNo			シリアル番号
	 * @param array	  $updateValues		更新データ
	 * @return							true=正常、false=異常
	 */
	function updateTableData($tableName, $serialNo, $updateValues)
	{
		// トランザクション開始
		$this->startTransaction();

		// キーを取得
		$keys = array_keys($updateValues);
				
		// レコード更新
		$queryStr = 'UPDATE ' . $tableName . ' ';
		$queryStr .=  'SET ';
		$values = array();
		for ($i = 0; $i < count($keys); $i++){
			if ($i < count($keys) -1){
				$queryStr .= $keys[$i] . ' = ?, ';
				$values[] = $updateValues[$keys[$i]];
			} else {
				$queryStr .= $keys[$i] . ' = ? ';
				$values[] = $updateValues[$keys[$i]];
			}
		}
		$queryStr .=  'WHERE _serial = ? ';
		$values[] = $serialNo;
		
		$ret =$this->execStatement($queryStr, $values);
		if (!$ret){
			$this->endTransaction();
			return false;
		}
			
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * データ削除
	 *
	 * @param string  $tableName	テーブル名
	 * @param array $serial			シリアルNo
	 * @return						true=成功、false=失敗
	 */
	function deleteTableDataBySerial($tableName, $serial)
	{
		global $gEnvManager;
			
		// 引数のエラーチェック
		if (!is_array($serial)) return false;
		if (count($serial) <= 0) return true;
		
		// トランザクション開始
		$this->startTransaction();
		
		// 指定のシリアルNoのレコードが削除状態でないかチェック
		for ($i = 0; $i < count($serial); $i++){
			$queryStr  = 'SELECT * FROM ' . $tableName . ' ';
			$queryStr .=   'WHERE _serial = ? ';
			$ret = $this->isRecordExists($queryStr, array($serial[$i]));
			// 存在しない場合は、既に削除されたとして終了
			if (!$ret){
				$this->endTransaction();
				return false;
			}
		}
		
		// データ削除
		$queryStr = 'DELETE FROM ' . $tableName . ' ';
		$queryStr .=  'WHERE _serial in (' . implode(',', $serial) . ') ';
		$this->execStatement($queryStr, array());
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
}
?>
