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
 * @copyright  Copyright 2006-2011 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: event_categoryDb.php 5484 2012-12-24 23:17:17Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getDbPath() . '/baseDb.php');

class event_categoryDb extends BaseDb
{
	/**
	 * カテゴリ情報をシリアル番号で取得
	 *
	 * @param string	$serial				シリアル番号
	 * @param array     $row				レコード
	 * @return bool							取得 = true, 取得なし= false
	 */
	function getCategoryBySerial($serial, &$row)
	{
		$queryStr  = 'SELECT * FROM event_category LEFT JOIN _login_user ON ec_create_user_id = lu_id AND lu_deleted = false ';
		$queryStr .=   'WHERE ec_serial = ? ';
		$ret = $this->selectRecord($queryStr, array(intval($serial)), $row);
		return $ret;
	}
	/**
	 * カテゴリ一覧を取得(管理用)
	 *
	 * @param string  $langId		言語ID
	 * @param function	$callback	コールバック関数
	 * @return 			なし
	 */
	function getAllCategory($langId, $callback)
	{
		$queryStr  = 'SELECT * FROM event_category LEFT JOIN _login_user ON ec_create_user_id = lu_id AND lu_deleted = false ';
		$queryStr .=   'WHERE ec_deleted = false ';		// 削除されていない
		$queryStr .=     'AND ec_item_id = ? ';
		$queryStr .=     'AND ec_language_id = ? ';
		$queryStr .=   'ORDER BY ec_index';
		$this->selectLoop($queryStr, array(''/*カテゴリのみ取得*/, $langId), $callback);
	}
	/**
	 * メニュー作成用のカテゴリ一覧を取得(管理用)
	 *
	 * @param string  $langId		言語ID
	 * @param function	$callback	コールバック関数
	 * @return 			なし
	 */
	function getAllCategoryForMenu($langId, $callback)
	{
		// カテゴリ情報を取得
		$queryStr  = 'SELECT ec_id FROM event_category ';
		$queryStr .=   'WHERE ec_deleted = false ';		// 削除されていない
		$queryStr .=     'AND ec_item_id = ? ';
		$queryStr .=     'AND ec_language_id = ? ';
		$queryStr .=   'ORDER BY ec_index';
		$retValue = $this->selectRecords($queryStr, array(''/*カテゴリ情報のみ*/, $langId), $rows);
		if (!$retValue) return;
		
		// CASE文作成
		$categoryId = '';
		$caseStr = 'CASE ec_id ';
		for ($i = 0; $i < count($rows); $i++){
			$id = '\'' . addslashes($rows[$i]['ec_id']) . '\'';
			$caseStr .= 'WHEN ' . $id . ' THEN ' . $i . ' ';
			$categoryId .= $id . ',';
		}
		$caseStr .= 'END AS no,';
		$categoryId = rtrim($categoryId, ',');
		// タイトルを最後にする
		$caseStr .=   'CASE ec_item_id ';
		$caseStr .=     'WHEN \'\' THEN 1 ';
		$caseStr .=     'ELSE 0 ';
		$caseStr .=   'END AS type ';
		
		$queryStr  = 'SELECT *, ' . $caseStr . ' FROM event_category ';
		$queryStr .=   'WHERE ec_deleted = false ';		// 削除されていない
		$queryStr .=     'AND ec_language_id = ? ';
		$queryStr .=     'AND ec_id in (' . $categoryId . ') ';
		$queryStr .=   'ORDER BY no, type, ec_index';
		$this->selectLoop($queryStr, array($langId), $callback);
	}
	/**
	 * カテゴリIDでカテゴリ情報を取得
	 *
	 * @param string  $categoryId	カテゴリID
	 * @param string  $langId		言語ID
	 * @param array   $row			取得レコード
	 * @return bool					取得 = true, 取得なし= false
	 */
	function getCategoryById($categoryId, $langId, &$row)
	{
		if (empty($categoryId) || empty($langId)) return false;
		
		$queryStr  = 'SELECT * FROM event_category ';
		$queryStr .=   'WHERE ec_deleted = false ';		// 削除されていない
		$queryStr .=     'AND ec_id = ? ';
		$queryStr .=     'AND ec_item_id = ? ';
		$queryStr .=     'AND ec_language_id = ? ';
		$retValue = $this->selectRecord($queryStr, array($categoryId, ''/*カテゴリ情報*/, $langId), $row);
		return $retValue;
	}
	/**
	 * カテゴリIDですべてのカテゴリ項目を取得
	 *
	 * @param string  $categoryId	カテゴリID
	 * @param string  $langId		言語ID
	 * @param array   $rows			取得レコード
	 * @return bool					取得 = true, 取得なし= false
	 */
	function getAllCategoryItemsById($categoryId, $langId, &$rows)
	{
		if (empty($categoryId) || empty($langId)) return false;
		
		$queryStr  = 'SELECT * FROM event_category ';
		$queryStr .=   'WHERE ec_deleted = false ';		// 削除されていない
		$queryStr .=     'AND ec_id = ? ';
		$queryStr .=     'AND ec_item_id != ? ';
		$queryStr .=     'AND ec_language_id = ? ';
		$queryStr .=   'ORDER BY ec_index';
		$retValue = $this->selectRecords($queryStr, array($categoryId, ''/*カテゴリ以外*/, $langId), $rows);
		return $retValue;
	}
	/**
	 * カテゴリ情報を更新
	 *
	 * @param string $serial	シリアル番号(0のときは新規登録)
	 * @param string $id		カテゴリID
	 * @param string  $langId	言語ID
	 * @param string $name		名前
	 * @param int $index		表示順
	 * @param array $itemArray	カテゴリ項目
	 * @param int $newSerial	新規シリアル番号
	 * @return					true = 正常、false=異常
	 */
	function updateCategory($serial, $id, $langId, $name, $index, $itemArray, &$newSerial)
	{
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $this->gEnv->getCurrentUserId();	// 現在のユーザ
		
		// トランザクション開始
		$this->startTransaction();
		
		// 前レコードの削除状態チェック
		$historyIndex = 0;
		$desc = '';
		if (empty($serial)){		// 新規登録のとき
			$queryStr  = 'SELECT * FROM event_category ';
			$queryStr .=   'WHERE ec_id = ? ';
			$queryStr .=     'AND ec_item_id = ? ';
			$queryStr .=     'AND ec_language_id = ? ';
			$queryStr .=   'ORDER BY ec_history_index DESC ';
			$ret = $this->selectRecord($queryStr, array($id, ''/*カテゴリタイトル*/, $langId), $row);
			if ($ret){
				if (!$row['ec_deleted']){		// レコード存在していれば終了
					$this->endTransaction();
					return false;
				}
				$historyIndex = $row['ec_history_index'] + 1;
			}
		} else {		// 更新のとき
			// 指定のシリアルNoのレコードが削除状態でないかチェック
			$queryStr  = 'SELECT * FROM event_category ';
			$queryStr .=   'WHERE ec_serial = ? ';
			$ret = $this->selectRecord($queryStr, array($serial), $row);
			if ($ret){		// 既に登録レコードがあるとき
				if ($row['ec_deleted']){		// レコードが削除されていれば終了
					$this->endTransaction();
					return false;
				}
				$historyIndex = $row['ec_history_index'] + 1;
				
				// 識別IDと言語の変更は不可
				$id = $row['ec_id'];
				$langId = $row['ec_language_id'];
			} else {		// 存在しない場合は終了
				$this->endTransaction();
				return false;
			}
			
			// 古いレコードを削除
			$queryStr  = 'UPDATE event_category ';
			$queryStr .=   'SET ec_deleted = true, ';	// 削除
			$queryStr .=     'ec_update_user_id = ?, ';
			$queryStr .=     'ec_update_dt = ? ';
			$queryStr .=   'WHERE ec_deleted = false ';		// 削除されていない
			$queryStr .=     'AND ec_id = ? ';
			$queryStr .=     'AND ec_language_id = ? ';
			$ret = $this->execStatement($queryStr, array($userId, $now, $id, $langId));
			if (!$ret){
				$this->endTransaction();
				return false;
			}
		}
		
		// カテゴリ種別を追加
		$queryStr = 'INSERT INTO event_category ';
		$queryStr .=  '(ec_id, ec_item_id, ec_language_id, ec_history_index, ec_name, ec_index, ec_create_user_id, ec_create_dt) ';
		$queryStr .=  'VALUES ';
		$queryStr .=  '(?, ?, ?, ?, ?, ?, ?, ?)';
		$this->execStatement($queryStr, array($id, ''/*カテゴリ種別*/, $langId, $historyIndex, $name, intval($index), $userId, $now));
		
		// カテゴリ項目を追加
		for ($i = 0; $i < count($itemArray); $i++){
			$line = $itemArray[$i];
			
			$queryStr = 'INSERT INTO event_category ';
			$queryStr .=  '(ec_id, ec_item_id, ec_language_id, ec_history_index, ec_name, ec_index, ec_create_user_id, ec_create_dt) ';
			$queryStr .=  'VALUES ';
			$queryStr .=  '(?, ?, ?, ?, ?, ?, ?, ?)';
			$this->execStatement($queryStr, array($id, $line['ec_item_id'], $langId, $historyIndex, $line['ec_name'], intval($line['ec_index']), $userId, $now));
		}

		// 新規のシリアル番号取得
		$queryStr = 'SELECT MAX(ec_serial) AS ns FROM event_category ';
		$ret = $this->selectRecord($queryStr, array(), $row);
		if ($ret) $newSerial = $row['ns'];
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * カテゴリ情報の削除
	 *
	 * @param array $serial			シリアルNo
	 * @return						true=成功、false=失敗
	 */
	function delCategory($serial)
	{
		$now = date("Y/m/d H:i:s");	// 現在日時
		$user = $this->gEnv->getCurrentUserId();	// 現在のユーザ
		
		if (!is_array($serial) || count($serial) <= 0) return true;
		
		// トランザクション開始
		$this->startTransaction();
		
		// 指定のシリアルNoのレコードが削除状態でないかチェック
		$delLines = array();		// 削除対象
		for ($i = 0; $i < count($serial); $i++){
			$queryStr  = 'SELECT * FROM event_category ';
			$queryStr .=   'WHERE ec_deleted = false ';		// 未削除
			$queryStr .=     'AND ec_serial = ? ';
			$ret = $this->selectRecord($queryStr, array($serial[$i]), $row);
			// 存在しない場合は、既に削除されたとして終了
			if (!$ret){
				$this->endTransaction();
				return false;
			}
			$line = array();
			$line['ec_id'] = $row['ec_id'];
			$line['ec_language_id'] = $row['ec_language_id'];
			$delLines[] = $line;
		}
		
		// レコードを削除
		for ($i = 0; $i < count($delLines); $i++){
			$queryStr  = 'UPDATE event_category ';
			$queryStr .=   'SET ec_deleted = true, ';	// 削除
			$queryStr .=     'ec_update_user_id = ?, ';
			$queryStr .=     'ec_update_dt = ? ';
			$queryStr .=   'WHERE ec_deleted = false ';		// 削除されていない
			$queryStr .=     'AND ec_id = ? ';
			$queryStr .=     'AND ec_language_id = ? ';
			$this->execStatement($queryStr, array($user, $now, $delLines[$i]['ec_id'], $delLines[$i]['ec_language_id']));
		}
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * イベントに対応するカテゴリの更新
	 *
	 * @param string $eventId			イベントID
	 * @param array $categoryValues		カテゴリ選択値
	 * @return					true = 正常、false=異常
	 */
	function updateEventCategory($eventId, $categoryValues)
	{
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $this->gEnv->getCurrentUserId();	// 現在のユーザ
		
		// トランザクション開始
		$this->startTransaction();
		
		// 古いレコードを削除
		$queryStr = 'DELETE FROM event_entry_with_category ';
		$queryStr .=  'WHERE ew_entry_id = ? ';
		$ret = $this->execStatement($queryStr, array($eventId));
		if (!$ret){
			$this->endTransaction();
			return false;
		}

		// データを追加
		$keys = array_keys($categoryValues);
		for ($i = 0; $i < count($keys); $i++){
			$queryStr = 'INSERT INTO event_entry_with_category ';
			$queryStr .=  '(ew_entry_id, ew_category_id, ew_category_item_id) ';
			$queryStr .=  'VALUES ';
			$queryStr .=  '(?, ?, ?)';
			$this->execStatement($queryStr, array($eventId, $keys[$i], $categoryValues[$keys[$i]]));
		}
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * イベントに対応したカテゴリ情報を取得
	 *
	 * @param string  $eventId		イベントID
	 * @param array   $rows			取得レコード
	 * @return bool					取得 = true, 取得なし= false
	 */
	function getEventCategory($eventId, &$rows)
	{
		if (empty($eventId)) return false;
		
		$queryStr  = 'SELECT * FROM event_entry_with_category ';
		$queryStr .=   'WHERE ew_entry_id = ? ';
		$retValue = $this->selectRecords($queryStr, array($eventId), $rows);
		return $retValue;
	}
	/**
	 * イベントに対応するカテゴリの削除
	 *
	 * @param string $eventId	イベントID
	 * @return					true = 正常、false=異常
	 */
	function delEventCategory($eventId)
	{
		// トランザクション開始
		$this->startTransaction();
		
		// 古いレコードを削除
		$queryStr = 'DELETE FROM event_entry_with_category ';
		$queryStr .=  'WHERE ew_entry_id = ? ';
		$ret = $this->execStatement($queryStr, array($eventId));
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
}
?>
