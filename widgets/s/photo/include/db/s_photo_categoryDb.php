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
 * @copyright  Copyright 2006-2012 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: s_photo_categoryDb.php 4711 2012-02-22 13:20:46Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getDbPath() . '/baseDb.php');

class s_photo_categoryDb extends BaseDb
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
	 * 画像カテゴリー一覧を取得
	 *
	 * @param string	$lang				言語
	 * @param function	$callback			コールバック関数
	 * @param bool      $onlyVisible		表示可能なカテゴリーのみ取得
	 * @return 			なし
	 */
	function getAllCategory($lang, $callback, $onlyVisible=false)
	{
		$queryStr = 'SELECT * FROM photo_category LEFT JOIN _login_user ON hc_create_user_id = lu_id AND lu_deleted = false ';
		$queryStr .=  'WHERE hc_language_id = ? ';
		$queryStr .=    'AND hc_deleted = false ';		// 削除されていない
		if ($onlyVisible) $queryStr .=    'AND hc_visible = true ';		// 表示可能項目のみ
		$queryStr .=  'ORDER BY hc_sort_order';
		$this->selectLoop($queryStr, array($lang), $callback);
	}
	/**
	 * 画像親カテゴリー一覧を取得
	 *
	 * @param string	$lang		言語
	 * @param array     $row		レコード
	 * @return bool					true=取得、false=取得せず
	 */
	function getAllPCategory($lang, &$rows)
	{
		$queryStr = 'SELECT DISTINCT pc1.hc_parent_id AS parent, pc2.hc_name AS name FROM photo_category AS pc1 RIGHT JOIN photo_category AS pc2 ON pc1.hc_parent_id = pc2.hc_id AND pc1.hc_language_id = pc2.hc_language_id AND pc2.hc_deleted = false ';
		$queryStr .=  'WHERE pc1.hc_deleted = false ';		// 削除されていない
		$queryStr .=    'AND pc1.hc_parent_id > 0 ';		// 親カテゴリーあり
		$queryStr .=    'AND pc1.hc_language_id = ? ';
		$queryStr .=  'ORDER BY pc2.hc_sort_order';
		$retValue = $this->selectRecords($queryStr, array($lang), $rows);
		return $retValue;
	}
	/**
	 * メニュー作成用のカテゴリ一覧を取得
	 *
	 * @param string  $langId			言語ID
	 * @param array   $categoryArray	取得するカテゴリID
	 * @param array  $rows				取得レコード
	 * @return							true=取得、false=取得せず
	 */
	function getAllCategoryForMenu($langId, $categoryArray, &$rows)
	{
		if (count($categoryArray) <= 0) return false;
		
		// CASE文作成
		$categoryId = '';
		$caseStr = 'CASE hc_parent_id ';
		for ($i = 0; $i < count($categoryArray); $i++){
			//$id = '\'' . addslashes($categoryArray[$i]) . '\'';
			$id = addslashes($categoryArray[$i]);
			$caseStr .= 'WHEN ' . $id . ' THEN ' . $i . ' ';
			$categoryId .= $id . ',';
		}
		$caseStr .= 'END AS no ';
		$categoryId = rtrim($categoryId, ',');

		$queryStr  = 'SELECT *, ' . $caseStr . ' FROM photo_category ';
		$queryStr .=   'WHERE hc_deleted = false ';		// 削除されていない
		$queryStr .=     'AND hc_visible = true ';		// 表示
		$queryStr .=     'AND hc_language_id = ? ';
		$queryStr .=     'AND hc_parent_id in (' . $categoryId . ') ';
		$queryStr .=   'ORDER BY no, hc_sort_order';
		$retValue = $this->selectRecords($queryStr, array($langId), $rows);
		return $retValue;
	}
	/**
	 * 画像カテゴリーの対応言語を取得
	 *
	 * @param int		$id			画像カテゴリーID
	 * @param array     $row		レコード
	 * @return bool					true=取得、false=取得せず
	 */
	function getLangByCategoryId($id, &$rows)
	{
		$queryStr = 'SELECT ln_id, ln_name, ln_name_en FROM photo_category LEFT JOIN _language ON hc_language_id = ln_id ';
		$queryStr .=  'WHERE hc_deleted = false ';	// 削除されていない
		$queryStr .=    'AND hc_id = ? ';
		$queryStr .=  'ORDER BY hc_id, ln_priority';
		$retValue = $this->selectRecords($queryStr, array($id), $rows);
		return $retValue;
	}
	/**
	 * 画像カテゴリーの新規追加
	 *
	 * @param int	  $id	カテゴリーID
	 * @param string  $lang			言語ID
	 * @param string  $name			名前
	 * @param string  $password		パスワード
	 * @param int     $pcategory	親カテゴリーID
	 * @param int     $index		表示順
	 * @param bool    $visible		表示、非表示
	 * @param int     $userId		更新者ユーザID
	 * @param int     $newSerial	新規シリアル番号
	 * @return bool					true = 成功、false = 失敗
	 */
	function addCategory($id, $lang, $name, $password, $pcategory, $index, $visible, $userId, &$newSerial)
	{	
		// トランザクション開始
		$this->startTransaction();
		
		if ($id == 0){		// IDが0のときは、カテゴリーIDを新規取得
			// コンテンツIDを決定する
			$queryStr = 'select max(hc_id) as mid from photo_category ';
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
		$queryStr = 'SELECT * FROM photo_category ';
		$queryStr .=  'WHERE hc_id = ? ';
		$queryStr .=    'AND hc_language_id = ? ';
		$queryStr .=  'ORDER BY hc_history_index DESC ';
		$ret = $this->selectRecord($queryStr, array($cId, $lang), $row);
		if ($ret){
			if (!$row['hc_deleted']){		// レコード存在していれば終了
				$this->endTransaction();
				return false;
			}
			$historyIndex = $row['hc_history_index'] + 1;
		}
		
		// データを追加
		$queryStr = 'INSERT INTO photo_category ';
		$queryStr .=  '(hc_id, hc_language_id, hc_history_index, hc_name, hc_password, hc_parent_id, hc_sort_order, hc_visible, hc_create_user_id, hc_create_dt) ';
		$queryStr .=  'VALUES ';
		$queryStr .=  '(?, ?, ?, ?, ?, ?, ?, ?, ?, now())';
		$this->execStatement($queryStr, array($cId, $lang, $historyIndex, $name, $password, $pcategory, $index, $visible, $userId));
		
		// 新規のシリアル番号取得
		$queryStr = 'select max(hc_serial) as ns from photo_category ';
		$ret = $this->selectRecord($queryStr, array(), $row);
		if ($ret) $newSerial = $row['ns'];
			
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * 画像カテゴリーの更新
	 *
	 * @param int     $serial		シリアル番号
	 * @param string  $name			名前
	 * @param string  $password		パスワード
	 * @param int     $pcategory	親カテゴリーID
	 * @param int     $index		表示順
	 * @param bool    $visible		表示、非表示
	 * @param int     $userId		更新者ユーザID
	 * @param int     $newSerial	新規シリアル番号
	 * @return bool					true = 成功、false = 失敗
	 */
	function updateCategory($serial, $name, $password, $pcategory, $index, $visible, $userId, &$newSerial)
	{	
		$now = date("Y/m/d H:i:s");	// 現在日時
				
		// トランザクション開始
		$this->startTransaction();
		
		// 指定のシリアルNoのレコードが削除状態でないかチェック
		$changePCategory = false;		// 親カテゴリを変更かどうか
		$historyIndex = 0;		// 履歴番号
		$queryStr  = 'select * from photo_category ';
		$queryStr .=   'where hc_serial = ? ';
		$ret = $this->selectRecord($queryStr, array($serial), $row);
		if ($ret){		// 既に登録レコードがあるとき
			if ($row['hc_deleted']){		// レコードが削除されていれば終了
				$this->endTransaction();
				return false;
			}
			$historyIndex = $row['hc_history_index'] + 1;
			if ($pcategory != $row['hc_parent_id']) $changePCategory = true;
		} else {		// 存在しない場合は終了
			$this->endTransaction();
			return false;
		}
		// 親カテゴリーが変更のときは、同じカテゴリーIDの親カテゴリーも変更
		if ($changePCategory){
			$queryStr = 'SELECT hc_serial FROM photo_category ';
			$queryStr .=  'WHERE hc_deleted = false ';	// 削除されていない
			$queryStr .=    'AND hc_id = ? ';
			$ret = $this->selectRecords($queryStr, array($row['hc_id']), $rows);
			if ($ret){
				for ($i = 0; $i < count($rows); $i++){
					if ($rows[$i]['hc_serial'] != $serial){
						if (!$this->updatePCategory($rows[$i]['hc_serial'], $pcategory, $userId, $now)){
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
		$queryStr  = 'UPDATE photo_category ';
		$queryStr .=   'SET hc_deleted = true, ';	// 削除
		$queryStr .=     'hc_update_user_id = ?, ';
		$queryStr .=     'hc_update_dt = ? ';
		$queryStr .=   'WHERE hc_serial = ?';
		$this->execStatement($queryStr, array($userId, $now, $serial));
		
		// 新規レコード追加
		$queryStr = 'INSERT INTO photo_category ';
		$queryStr .=  '(hc_id, hc_language_id, hc_history_index, hc_name, hc_password, hc_parent_id, hc_sort_order, hc_visible, hc_create_user_id, hc_create_dt) ';
		$queryStr .=  'VALUES ';
		$queryStr .=  '(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
		$this->execStatement($queryStr, array($row['hc_id'], $row['hc_language_id'], $historyIndex, $name, $password, $pcategory, $index, $visible, $userId, $now));

		// 新規のシリアル番号取得
		$queryStr = 'select max(hc_serial) as ns from photo_category ';
		$ret = $this->selectRecord($queryStr, array(), $row);
		if ($ret) $newSerial = $row['ns'];
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * 画像カテゴリーをシリアル番号で取得
	 *
	 * @param int		$serial				シリアル番号
	 * @param array     $row				レコード
	 * @return bool							取得 = true, 取得なし= false
	 */
	function getCategoryBySerial($serial, &$row)
	{
		$queryStr  = 'select * from photo_category LEFT JOIN _login_user ON hc_create_user_id = lu_id AND lu_deleted = false ';
		$queryStr .=   'WHERE hc_serial = ? ';
		$ret = $this->selectRecord($queryStr, array($serial), $row);
		return $ret;
	}	
	/**
	 * 画像カテゴリーをカテゴリーIDで取得
	 *
	 * @param int		$id					カテゴリーID
	 * @param string	$langId				言語ID
	 * @param array     $row				レコード
	 * @return bool							取得 = true, 取得なし= false
	 */
	function getCategoryByCategoryId($id, $langId, &$row)
	{
		$queryStr  = 'SELECT * FROM photo_category LEFT JOIN _login_user ON hc_create_user_id = lu_id AND lu_deleted = false ';
		$queryStr .=   'WHERE hc_deleted = false ';	// 削除されていない
		$queryStr .=   'AND hc_id = ? ';
		$queryStr .=   'AND hc_language_id = ? ';
		$ret = $this->selectRecord($queryStr, array($id, $langId), $row);
		return $ret;
	}
	/**
	 * 親画像カテゴリーの更新
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
		$queryStr  = 'select * from photo_category ';
		$queryStr .=   'where hc_serial = ? ';
		$ret = $this->selectRecord($queryStr, array($serial), $row);
		if ($ret){		// 既に登録レコードがあるとき
			if ($row['hc_deleted']){		// レコードが削除されていれば終了
				return false;
			}
			$historyIndex = $row['hc_history_index'] + 1;
		} else {		// 存在しない場合は終了
			return false;
		}
		
		// 古いレコードを削除
		$queryStr  = 'UPDATE photo_category ';
		$queryStr .=   'SET hc_deleted = true, ';	// 削除
		$queryStr .=     'hc_update_user_id = ?, ';
		$queryStr .=     'hc_update_dt = ? ';
		$queryStr .=   'WHERE hc_serial = ?';
		$ret = $this->execStatement($queryStr, array($userId, $now, $serial));
		if (!$ret) return false;
		
		// 新規レコード追加
		$queryStr = 'INSERT INTO photo_category ';
		$queryStr .=  '(hc_id, hc_language_id, hc_history_index, hc_name, hc_parent_id, hc_sort_order, hc_create_user_id, hc_create_dt) ';
		$queryStr .=  'VALUES ';
		$queryStr .=  '(?, ?, ?, ?, ?, ?, ?, ?)';
		$ret = $this->execStatement($queryStr, array($row['hc_id'], $row['hc_language_id'], $historyIndex, $row['hc_name'], $pcategory, $row['hc_sort_order'], $userId, $now));
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
		$queryStr  = 'select * from photo_category ';
		$queryStr .=   'where hc_deleted = false ';		// 未削除
		$queryStr .=     'and hc_serial = ? ';
		$ret = $this->isRecordExists($queryStr, array($serialNo));
		// 存在しない場合は、既に削除されたとして終了
		if (!$ret){
			$this->endTransaction();
			return false;
		}
		
		// レコードを削除
		$queryStr  = 'UPDATE photo_category ';
		$queryStr .=   'SET hc_deleted = true, ';	// 削除
		$queryStr .=     'hc_update_user_id = ?, ';
		$queryStr .=     'hc_update_dt = now() ';
		$queryStr .=   'WHERE hc_serial = ?';
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
			$queryStr  = 'SELECT * FROM photo_category ';
			$queryStr .=   'WHERE hc_deleted = false ';		// 未削除
			$queryStr .=     'AND hc_serial = ? ';
			$ret = $this->isRecordExists($queryStr, array($serial[$i]));
			// 存在しない場合は、既に削除されたとして終了
			if (!$ret){
				$this->endTransaction();
				return false;
			}
		}
		
		// レコードを削除
		$queryStr  = 'UPDATE photo_category ';
		$queryStr .=   'SET hc_deleted = true, ';	// 削除
		$queryStr .=     'hc_update_user_id = ?, ';
		$queryStr .=     'hc_update_dt = ? ';
		$queryStr .=   'WHERE hc_serial in (' . implode($serial, ',') . ') ';
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
		$queryStr  = 'select * from photo_category ';
		$queryStr .=   'where hc_deleted = false ';		// 未削除
		$queryStr .=     'and hc_serial = ? ';
		$ret = $this->selectRecord($queryStr, array($serial), $row);
		if ($ret){		// 既に登録レコードがあるとき
			if ($row['hc_deleted']){		// レコードが削除されていれば終了
				$this->endTransaction();
				return false;
			}
		} else {		// 存在しない場合は終了
			$this->endTransaction();
			return false;
		}
		$contId = $row['hc_id'];
		
		// レコードを削除
		$queryStr  = 'UPDATE photo_category ';
		$queryStr .=   'SET hc_deleted = true, ';	// 削除
		$queryStr .=     'hc_update_user_id = ?, ';
		$queryStr .=     'hc_update_dt = now() ';
		$queryStr .=   'WHERE hc_id = ?';
		$this->execStatement($queryStr, array($userId, $contId));
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * 指定したカテゴリーが親であるカテゴリーを取得
	 *
	 * @param int		$id			親画像カテゴリーID(0はトップレベル)
	 * @param string	$langId				言語ID
	 * @return array				カテゴリーIDの配列
	 */
	function getChildCategory($id, $lang)
	{
		$retArray = array();
		$queryStr = 'SELECT hc_id FROM photo_category ';
		$queryStr .=  'WHERE hc_deleted = false ';	// 削除されていない
		$queryStr .=    'AND hc_parent_id = ? ';
		$queryStr .=    'AND hc_language_id = ? ';
		$queryStr .=  'ORDER BY hc_sort_order';
		$ret = $this->selectRecords($queryStr, array($id, $lang), $rows);
		if ($ret){
			for ($i = 0; $i < count($rows); $i++){
				$retArray[] = $rows[$i]['hc_id'];
			}
		}
		return $retArray;
	}
	/**
	 * 指定したカテゴリーが親であるカテゴリーを取得
	 *
	 * @param int		$id			親画像カテゴリーID(0はトップレベル)
	 * @param string	$langId				言語ID
	 * @param array		$rows		取得した行データ
	 * @return int					取得した行数
	 */
	function getChildCategoryWithRows($id, $lang, &$rows)
	{
		$retCount = 0;
		$queryStr = 'SELECT hc_id,hc_name FROM photo_category ';
		$queryStr .=  'WHERE hc_deleted = false ';	// 削除されていない
		$queryStr .=    'AND hc_parent_id = ? ';
		$queryStr .=    'AND hc_language_id = ? ';
		$queryStr .=  'ORDER BY hc_sort_order';
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
		$caseStr = 'CASE hc_id ';
		for ($i = 0; $i < count($idArray); $i++){
			$caseStr .= 'WHEN ' . $idArray[$i] . ' THEN ' . $i . ' ';
		}
		$caseStr .= 'END AS no';

		$queryStr = 'SELECT *, ' . $caseStr . ' FROM photo_category ';
		$queryStr .=  'WHERE hc_deleted = false ';		// 削除されていない
		$queryStr .=    'AND hc_id in (' . $catId . ') ';
		$queryStr .=    'AND hc_language_id = ? ';
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
		$queryStr = 'SELECT max(hc_sort_order) as mi FROM photo_category ';
		$queryStr .=  'WHERE hc_deleted = false ';
		$queryStr .=  'AND hc_language_id = ? ';
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
