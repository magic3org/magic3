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
 * @copyright  Copyright 2006-2015 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getDbPath() . '/baseDb.php');

class event_mainCategoryDb extends BaseDb
{
	/**
	 * すべての言語を取得
	 *
	 * @param function	$callback			コールバック関数
	 * @return			true=取得、false=取得せず
	 */
	function getAllLang($callback)
	{
		$queryStr = 'SELECT * FROM _language ORDER BY ln_priority';
		$this->selectLoop($queryStr, array(), $callback, null);
	}
	/**
	 * イベントカテゴリー一覧を取得
	 *
	 * @param function	$callback			コールバック関数
	 * @param string	$lang				言語
	 * @return 			なし
	 */
	function getAllCategory($callback, $lang)
	{
		$queryStr = 'SELECT * FROM event_category LEFT JOIN _login_user ON ec_create_user_id = lu_id AND lu_deleted = false ';
		$queryStr .=  'WHERE ec_language_id = ? ';
		$queryStr .=    'AND ec_deleted = false ';		// 削除されていない
		$queryStr .=  'ORDER BY ec_sort_order, ec_id';	// カテゴリー並び順
		$this->selectLoop($queryStr, array($lang), $callback);
	}
	/**
	 * イベントカテゴリーの対応言語を取得
	 *
	 * @param int		$id			商品カテゴリーID
	 * @return bool					true=取得、false=取得せず
	 */
	function getLangByCategoryId($id, &$rows)
	{
		$queryStr = 'SELECT ln_id, ln_name, ln_name_en, ln_priority, ec_id FROM event_category LEFT JOIN _language ON ec_language_id = ln_id ';
		$queryStr .=  'WHERE ec_deleted = false ';	// 削除されていない
		$queryStr .=    'AND ec_id = ? ';
		$queryStr .=  'ORDER BY ec_id, ln_priority';
		$retValue = $this->selectRecords($queryStr, array($id), $rows);
		return $retValue;
	}
	/**
	 * イベントカテゴリーの新規追加
	 *
	 * @param int	  $id	カテゴリーID
	 * @param string  $lang			言語ID
	 * @param string  $name			名前
	 * @param int     $pcategory	親カテゴリーID
	 * @param int     $index		表示順
	 * @param bool    $visible		表示、非表示
	 * @param int     $userId		更新者ユーザID
	 * @param int     $newSerial	新規シリアル番号
	 * @return bool					true = 成功、false = 失敗
	 */
	function addCategory($id, $lang, $name, $pcategory, $index, $visible, $userId, &$newSerial)
	{	
		// トランザクション開始
		$this->startTransaction();
		
		if ($id == 0){		// IDが0のときは、カテゴリーIDを新規取得
			// コンテンツIDを決定する
			$queryStr = 'SELECT MAX(ec_id) AS mid FROM event_category ';
			$ret = $this->selectRecord($queryStr, array(), $row);
			if ($ret){
				$cId = $row['mid'] + 1;
			} else {
				$cId = 1;
			}
		} else {
			$cId = $id;
		}
		
		// 前レコードの削除状態チェック
		$historyIndex = 0;
		$queryStr = 'SELECT * FROM event_category ';
		$queryStr .=  'WHERE ec_id = ? ';
		$queryStr .=    'AND ec_language_id = ? ';
		$queryStr .=  'ORDER BY ec_history_index DESC ';
		$ret = $this->selectRecord($queryStr, array($cId, $lang), $row);
		if ($ret){
			if (!$row['ec_deleted']){		// レコード存在していれば終了
				$this->endTransaction();
				return false;
			}
			$historyIndex = $row['ec_history_index'] + 1;
		}
		
		// データを追加
		$queryStr = 'INSERT INTO event_category ';
		$queryStr .=  '(ec_id, ec_language_id, ec_history_index, ec_name, ec_parent_id, ec_sort_order, ec_visible, ec_create_user_id, ec_create_dt) ';
		$queryStr .=  'VALUES ';
		$queryStr .=  '(?, ?, ?, ?, ?, ?, ?, ?, now())';
		$this->execStatement($queryStr, array($cId, $lang, $historyIndex, $name, $pcategory, $index, $visible, $userId));
		
		// 新規のシリアル番号取得
		$queryStr = 'SELECT MAX(ec_serial) AS ns FROM event_category ';
		$ret = $this->selectRecord($queryStr, array(), $row);
		if ($ret) $newSerial = $row['ns'];
			
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * イベントカテゴリーをシリアル番号で取得
	 *
	 * @param int		$serial				シリアル番号
	 * @param array     $row				レコード
	 * @return bool							取得 = true, 取得なし= false
	 */
	function getCategoryBySerial($serial, &$row)
	{
		$queryStr  = 'SELECT * FROM event_category LEFT JOIN _login_user ON ec_create_user_id = lu_id AND lu_deleted = false ';
		$queryStr .=   'WHERE ec_serial = ? ';
		$ret = $this->selectRecord($queryStr, array($serial), $row);
		return $ret;
	}	
	/**
	 * イベントカテゴリーをカテゴリーIDで取得
	 *
	 * @param int		$id					カテゴリーID
	 * @param string	$langId				言語ID
	 * @param array     $row				レコード
	 * @return bool							取得 = true, 取得なし= false
	 */
	function getCategoryByCategoryId($id, $langId, &$row)
	{
		$queryStr  = 'SELECT * FROM event_category LEFT JOIN _login_user ON ec_create_user_id = lu_id AND lu_deleted = false ';
		$queryStr .=   'WHERE ec_deleted = false ';	// 削除されていない
		$queryStr .=   'AND ec_id = ? ';
		$queryStr .=   'AND ec_language_id = ? ';
		$ret = $this->selectRecord($queryStr, array($id, $langId), $row);
		return $ret;
	}
	/**
	 * イベントカテゴリーの更新
	 *
	 * @param int     $serial		シリアル番号
	 * @param string  $name			名前
	 * @param int     $pcategory	親カテゴリーID
	 * @param int     $index		表示順
	 * @param bool    $visible		表示、非表示
	 * @param int     $userId		更新者ユーザID
	 * @param int     $newSerial	新規シリアル番号
	 * @return bool					true = 成功、false = 失敗
	 */
	function updateCategory($serial, $name, $pcategory, $index, $visible, $userId, &$newSerial)
	{	
		$now = date("Y/m/d H:i:s");	// 現在日時
				
		// トランザクション開始
		$this->startTransaction();
		
		// 指定のシリアルNoのレコードが削除状態でないかチェック
		$changePCategory = false;		// 親カテゴリを変更かどうか
		$historyIndex = 0;		// 履歴番号
		$queryStr  = 'SELECT * FROM event_category ';
		$queryStr .=   'where ec_serial = ? ';
		$ret = $this->selectRecord($queryStr, array($serial), $row);
		if ($ret){		// 既に登録レコードがあるとき
			if ($row['ec_deleted']){		// レコードが削除されていれば終了
				$this->endTransaction();
				return false;
			}
			$historyIndex = $row['ec_history_index'] + 1;
			if ($pcategory != $row['ec_parent_id']) $changePCategory = true;
		} else {		// 存在しない場合は終了
			$this->endTransaction();
			return false;
		}
		// 親カテゴリーが変更のときは、同じカテゴリーIDの親カテゴリーも変更
		if ($changePCategory){
			$queryStr = 'SELECT ec_serial FROM event_category ';
			$queryStr .=  'WHERE ec_deleted = false ';	// 削除されていない
			$queryStr .=    'AND ec_id = ? ';
			$ret = $this->selectRecords($queryStr, array($row['ec_id']), $rows);
			if ($ret){
				for ($i = 0; $i < count($rows); $i++){
					if ($rows[$i]['ec_serial'] != $serial){
						if (!$this->updatePCategory($rows[$i]['ec_serial'], $pcategory, $userId, $now)){
							$this->endTransaction();
							return false;		
						}
					}
				}
			} else {
				$this->endTransaction();
				return false;			
			}
		}
		
		// 古いレコードを削除
		$queryStr  = 'UPDATE event_category ';
		$queryStr .=   'SET ec_deleted = true, ';	// 削除
		$queryStr .=     'ec_update_user_id = ?, ';
		$queryStr .=     'ec_update_dt = ? ';
		$queryStr .=   'WHERE ec_serial = ?';
		$this->execStatement($queryStr, array($userId, $now, $serial));
		
		// 新規レコード追加
		$queryStr = 'INSERT INTO event_category ';
		$queryStr .=  '(ec_id, ec_language_id, ec_history_index, ec_name, ec_parent_id, ec_sort_order, ec_visible, ec_create_user_id, ec_create_dt) ';
		$queryStr .=  'VALUES ';
		$queryStr .=  '(?, ?, ?, ?, ?, ?, ?, ?, ?)';
		$this->execStatement($queryStr, array($row['ec_id'], $row['ec_language_id'], $historyIndex, $name, $pcategory, $index, $visible, $userId, $now));

		// 新規のシリアル番号取得
		$queryStr = 'SELECT MAX(ec_serial) AS ns FROM event_category ';
		$ret = $this->selectRecord($queryStr, array(), $row);
		if ($ret) $newSerial = $row['ns'];
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * 親イベントカテゴリーの更新
	 *
	 * @param int     $serial		シリアル番号
	 * @param int     $pcategory	親カテゴリーID
	 * @param int     $userId		更新者ユーザID
	 * @param string  $now			現在日時
	 * @return bool					true = 成功、false = 失敗
	 */
	function updatePCategory($serial, $pcategory, $userId, $now)
	{
		// 指定のシリアルNoのレコードが削除状態でないかチェック
		$historyIndex = 0;		// 履歴番号
		$queryStr  = 'SELECT * FROM event_category ';
		$queryStr .=   'where ec_serial = ? ';
		$ret = $this->selectRecord($queryStr, array($serial), $row);
		if ($ret){		// 既に登録レコードがあるとき
			if ($row['ec_deleted']){		// レコードが削除されていれば終了
				return false;
			}
			$historyIndex = $row['ec_history_index'] + 1;
		} else {		// 存在しない場合は終了
			return false;
		}
		
		// 古いレコードを削除
		$queryStr  = 'UPDATE event_category ';
		$queryStr .=   'SET ec_deleted = true, ';	// 削除
		$queryStr .=     'ec_update_user_id = ?, ';
		$queryStr .=     'ec_update_dt = ? ';
		$queryStr .=   'WHERE ec_serial = ?';
		$ret = $this->execStatement($queryStr, array($userId, $now, $serial));
		if (!$ret) return false;
		
		// 新規レコード追加
		$queryStr = 'INSERT INTO event_category ';
		$queryStr .=  '(ec_id, ec_language_id, ec_history_index, ec_name, ec_parent_id, ec_sort_order, ec_create_user_id, ec_create_dt) ';
		$queryStr .=  'VALUES ';
		$queryStr .=  '(?, ?, ?, ?, ?, ?, ?, ?)';
		$ret = $this->execStatement($queryStr, array($row['ec_id'], $row['ec_language_id'], $historyIndex, $row['ec_name'], $pcategory, $row['ec_sort_order'], $userId, $now));
		if ($ret){
			return true;
		} else {
			return false;
		}
	}
	/**
	 * カテゴリーの削除
	 *
	 * @param int $serialNo			シリアルNo
	 * @param int $userId			ユーザID(データ更新者)
	 * @return						true=成功、false=失敗
	 */
	function delCategory($serialNo, $userId)
	{
		// トランザクション開始
		$this->startTransaction();
		
		// 指定のシリアルNoのレコードが削除状態でないかチェック
		$queryStr  = 'SELECT * FROM event_category ';
		$queryStr .=   'WHERE ec_deleted = false ';		// 未削除
		$queryStr .=     'AND ec_serial = ? ';
		$ret = $this->isRecordExists($queryStr, array($serialNo));
		// 存在しない場合は、既に削除されたとして終了
		if (!$ret){
			$this->endTransaction();
			return false;
		}
		
		// レコードを削除
		$queryStr  = 'UPDATE event_category ';
		$queryStr .=   'SET ec_deleted = true, ';	// 削除
		$queryStr .=     'ec_update_user_id = ?, ';
		$queryStr .=     'ec_update_dt = now() ';
		$queryStr .=   'WHERE ec_serial = ?';
		$this->execStatement($queryStr, array($userId, $serialNo));
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * カテゴリーをシリアル番号で削除
	 *
	 * @param array   $serial		シリアルNo
	 * @return						true=成功、false=失敗
	 */
	function delCategoryBySerial($serial)
	{
		global $gEnvManager;
		
		// 引数のエラーチェック
		if (!is_array($serial)) return false;
		if (count($serial) <= 0) return true;
		
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $gEnvManager->getCurrentUserId();	// 現在のユーザ
		
		// トランザクション開始
		$this->startTransaction();
		
		// 指定のシリアルNoのレコードが削除状態でないかチェック
		for ($i = 0; $i < count($serial); $i++){
			$queryStr  = 'SELECT * FROM event_category ';
			$queryStr .=   'WHERE ec_deleted = false ';		// 未削除
			$queryStr .=     'AND ec_serial = ? ';
			$ret = $this->isRecordExists($queryStr, array($serial[$i]));
			// 存在しない場合は、既に削除されたとして終了
			if (!$ret){
				$this->endTransaction();
				return false;
			}
		}
		
		// レコードを削除
		$queryStr  = 'UPDATE event_category ';
		$queryStr .=   'SET ec_deleted = true, ';	// 削除
		$queryStr .=     'ec_update_user_id = ?, ';
		$queryStr .=     'ec_update_dt = ? ';
		$queryStr .=   'WHERE ec_serial in (' . implode($serial, ',') . ') ';
		$this->execStatement($queryStr, array($userId, $now));
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * カテゴリーIDで削除
	 *
	 * @param int $serial			シリアルNo
	 * @param int $userId			ユーザID(データ更新者)
	 * @return						true=成功、false=失敗
	 */
	function delCategoryById($serial, $userId)
	{
		// トランザクション開始
		$this->startTransaction();
		
		// コンテンツIDを取得
		$queryStr  = 'SELECT * FROM event_category ';
		$queryStr .=   'WHERE ec_deleted = false ';		// 未削除
		$queryStr .=     'AND ec_serial = ? ';
		$ret = $this->selectRecord($queryStr, array($serial), $row);
		if ($ret){		// 既に登録レコードがあるとき
			if ($row['ec_deleted']){		// レコードが削除されていれば終了
				$this->endTransaction();
				return false;
			}
		} else {		// 存在しない場合は終了
			$this->endTransaction();
			return false;
		}
		$contId = $row['ec_id'];
		
		// レコードを削除
		$queryStr  = 'UPDATE event_category ';
		$queryStr .=   'SET ec_deleted = true, ';	// 削除
		$queryStr .=     'ec_update_user_id = ?, ';
		$queryStr .=     'ec_update_dt = now() ';
		$queryStr .=   'WHERE ec_id = ?';
		$this->execStatement($queryStr, array($userId, $contId));
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * 指定したカテゴリーが親であるカテゴリーを取得
	 *
	 * @param int		$id			親商品カテゴリーID(0はトップレベル)
	 * @param string	$langId				言語ID
	 * @return array				カテゴリーIDの配列
	 */
	function getChildCategory($id, $lang)
	{
		$retArray = array();
		$queryStr = 'SELECT ec_id, ec_sort_order FROM event_category ';
		$queryStr .=  'WHERE ec_deleted = false ';	// 削除されていない
		$queryStr .=    'AND ec_parent_id = ? ';
		$queryStr .=    'AND ec_language_id = ? ';
		$queryStr .=  'ORDER BY ec_sort_order, ec_id';		// カテゴリー並び順
		$ret = $this->selectRecords($queryStr, array($id, $lang), $rows);
		if ($ret){
			for ($i = 0; $i < count($rows); $i++){
				$retArray[] = $rows[$i]['ec_id'];
			}
		}
		return $retArray;
	}
	/**
	 * 指定したカテゴリーが親であるカテゴリーを取得
	 *
	 * @param int		$id			親商品カテゴリーID(0はトップレベル)
	 * @param string	$langId				言語ID
	 * @param array		$rows		取得した行データ
	 * @return int					取得した行数
	 */
	function getChildCategoryWithRows($id, $lang, &$rows)
	{
		$retCount = 0;
		$queryStr = 'SELECT ec_id, ec_name, ec_sort_order FROM event_category ';
		$queryStr .=  'WHERE ec_deleted = false ';	// 削除されていない
		$queryStr .=    'AND ec_parent_id = ? ';
		$queryStr .=    'AND ec_language_id = ? ';
		$queryStr .=  'ORDER BY ec_sort_order, ec_id';// カテゴリー並び順
		$ret = $this->selectRecords($queryStr, array($id, $lang), $rows);
		if ($ret){
			$retCount = count($rows);
		}
		return $retCount;
	}
	/**
	 * カテゴリーを取得
	 *
	 * @param function	$callback			コールバック関数
	 * @param array		$idArray			カテゴリーID
	 * @param string	$lang				言語
	 * @return 			なし
	 */
	function getCategoryByIdArray($callback, $idArray, $lang)
	{
		$catId = implode(',', $idArray);
		
		// CASE文作成
		$caseStr = 'CASE ec_id ';
		for ($i = 0; $i < count($idArray); $i++){
			$caseStr .= 'WHEN ' . $idArray[$i] . ' THEN ' . $i . ' ';
		}
		$caseStr .= 'END AS no';

		$queryStr = 'SELECT *, ' . $caseStr . ' FROM event_category ';
		$queryStr .=  'WHERE ec_deleted = false ';		// 削除されていない
		$queryStr .=    'AND ec_id in (' . $catId . ') ';
		$queryStr .=    'AND ec_language_id = ? ';
		$queryStr .=  'ORDER BY no';
		$this->selectLoop($queryStr, array($lang), $callback, null);
	}
	/**
	 * 最大表示順を取得
	 *
	 * @param string	$lang		言語
	 * @return int					最大表示順
	 */
	function getMaxIndex($lang)
	{
		$queryStr = 'SELECT MAX(ec_sort_order) AS mi FROM event_category ';
		$queryStr .=  'WHERE ec_deleted = false ';
		$queryStr .=  'AND ec_language_id = ? ';
		$ret = $this->selectRecord($queryStr, array($lang), $row);
		if ($ret){
			$index = $row['mi'];
		} else {
			$index = 0;
		}
		return $index;
	}
}
?>
