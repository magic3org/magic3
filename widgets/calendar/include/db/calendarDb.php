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
 * @copyright  Copyright 2006-2013 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getDbPath() . '/baseDb.php');

class calendarDb extends BaseDb
{
	/**
	 * イベント記事を取得(表示用)
	 *
	 * @param int		$limit				取得する項目数
	 * @param int		$page				取得するページ(1～)
	 * @param timestamp	$startDt			期間(開始日)
	 * @param timestamp	$endDt				期間(終了日)
	 * @param string	$langId				言語
	 * @param function	$callback			コールバック関数
	 * @return 			なし
	 */
	function getEventItems($limit, $page, $startDt, $endDt, $langId, $callback)
	{
		$offset = $limit * ($page -1);
		if ($offset < 0) $offset = 0;
		$params = array();
		
		$queryStr  = 'SELECT * FROM event_entry ';
		$queryStr .=   'WHERE ee_deleted = false ';		// 削除されていない
		$queryStr .=     'AND ee_status = ? ';		$params[] = 2;	// 「公開」(2)データを表示
		$queryStr .=     'AND ee_language_id = ? ';	$params[] = $langId;
	
		// 期間が重なっている場合は取得
		$queryStr .=    'AND ((ee_start_dt <= ? AND ? < ee_end_dt) ';
		$params[] = $startDt;
		$params[] = $startDt;
		$queryStr .=    'OR (? <= ee_start_dt AND ee_start_dt < ?)) ';
		$params[] = $startDt;
		$params[] = $endDt;

		$queryStr .=  'ORDER BY ee_start_dt, ee_id LIMIT ' . $limit . ' OFFSET ' . $offset;// 日付順
		$this->selectLoop($queryStr, $params, $callback);
	}
	/**
	 * 日付タイプ一覧を取得
	 *
	 * @param function	$callback			コールバック関数
	 * @return 			なし
	 */
	function getDateTypeList($callback)
	{
		$queryStr  = 'SELECT * FROM date_type LEFT JOIN time_period ON dt_id = to_date_type_id AND to_index = 0 ';
		$queryStr .=   'WHERE dt_deleted = false ';		// 削除されていない
		$queryStr .=   'ORDER BY dt_sort_order';
		$this->selectLoop($queryStr, array(), $callback);
	}
	/**
	 * 日付タイプの追加更新
	 *
	 * @param string $id		日付タイプID。0のときは新規追加
	 * @param string $name		日付タイプ名
	 * @param int    $sortOrder	ソート順
	 * @param int    $newId		新規ID
	 * @return					true = 正常、false=異常
	 */
	function updateDataType($id, $name, $sortOrder, &$newId)
	{
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $this->gEnv->getCurrentUserId();	// 現在のユーザ
		
		// トランザクション開始
		$this->startTransaction();
		
		if (empty($id)){		// 新規追加の場合
			// 新規IDを作成
			$queryStr  = 'SELECT max(dt_id) AS mi FROM date_type ';
			$ret = $this->selectRecord($queryStr, array(), $row);
			if ($ret){
				$newId = $row['mi'] + 1;
			} else {
				$newId = 1;
			}
			
			// レコードを追加
			$queryStr  = 'INSERT INTO date_type ';
			$queryStr .=   '(dt_id, dt_name, dt_sort_order, dt_update_user_id, dt_update_dt) ';
			$queryStr .= 'VALUES ';
			$queryStr .=   '(?, ?, ?, ?, ?)';
			$this->execStatement($queryStr, array($newId, $name, intval($sortOrder), $userId, $now));
		} else {
			$queryStr  = 'SELECT * FROM date_type ';
			$queryStr .=   'WHERE dt_deleted = false ';
			$queryStr .=    'AND dt_id = ? ';
			$ret = $this->isRecordExists($queryStr, array($id));
			if ($ret){
				$queryStr  = 'UPDATE date_type ';
				$queryStr .=   'SET dt_name = ?, ';
				$queryStr .=     'dt_sort_order = ?, ';
				$queryStr .=     'dt_update_user_id = ?, ';
				$queryStr .=     'dt_update_dt = ? ';
				$queryStr .= 'WHERE dt_id = ?';
				$this->execStatement($queryStr, array($name, intval($sortOrder), $userId, $now, $id));
			} else {
				// トランザクション終了
				$this->endTransaction();
				
				return false;
			}
		}
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * 日付タイプの削除
	 *
	 * @param array  $idArray		ID
	 * @return bool					true=成功、false=失敗
	 */
	function deleteDateType($idArray)
	{
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $this->gEnv->getCurrentUserId();	// 現在のユーザ
		
		if (empty($idArray)) return false;
		
		// トランザクション開始
		$this->startTransaction();
		
		$queryStr  = 'UPDATE date_type ';
		$queryStr .=   'SET dt_deleted = true, ';	// 削除
		$queryStr .=     'dt_update_user_id = ?, ';
		$queryStr .=     'dt_update_dt = ? ';
		$queryStr .=   'WHERE dt_id in (' . implode($idArray, ',') . ') ';
		$this->execStatement($queryStr, array($userId, $now));
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * 日付タイプを取得
	 *
	 * @param string	$id				日付タイプID
	 * @param array     $row			レコード
	 * @return bool						取得 = true, 取得なし= false
	 */
	function getDateType($id, &$row)
	{
		$queryStr  = 'SELECT * FROM date_type LEFT JOIN _login_user ON dt_update_user_id = lu_id AND lu_deleted = false ';
		$queryStr .=   'WHERE dt_deleted = false ';
		$queryStr .=     'AND dt_id = ? ';
		$ret = $this->selectRecord($queryStr, array(intval($id)), $row);
		return $ret;
	}
	/**
	 * 日付タイプの最大表示順を取得
	 *
	 * @return int					最大表示順
	 */
	function getDateTypeMaxSortOrder()
	{
		$queryStr  = 'SELECT max(dt_sort_order) AS mo FROM date_type ';
		$queryStr .=   'WHERE dt_deleted = false ';
		$ret = $this->selectRecord($queryStr, array(), $row);
		if ($ret){
			$order = $row['mo'] + 1;
		} else {
			$order = 1;
		}
		return $order;
	}
	/**
	 * 時間割一覧を取得
	 *
	 * @param int		$dateTypeId		日付タイプ
	 * @param function	$callback		コールバック関数
	 * @return 			なし
	 */
	function getTimePeriodList($dateTypeId, $callback)
	{
		$queryStr  = 'SELECT * FROM time_period ';
		$queryStr .=   'WHERE to_date_type_id = ? ';
		$queryStr .=   'ORDER BY to_index';
		$this->selectLoop($queryStr, array($dateTypeId), $callback);
	}
	/**
	 * 時間割の追加更新
	 *
	 * @param string $id				日付タイプID
	 * @param array $timePeriodArray	時間割定義
	 * @return bool						true = 正常、false=異常
	 */
	function updateTimePeriod($id, $timePeriodArray)
	{
		// パラメータエラーチェック
		if (intval($id) <= 0) return false;
		
		// トランザクション開始
		$this->startTransaction();
		
		// 旧データ削除
		$queryStr  = 'DELETE FROM time_period ';
		$queryStr .=   'WHERE to_date_type_id = ? ';
		$this->execStatement($queryStr, array($id));
		
		// レコードを追加
		$timeCount = count($timePeriodArray);
		for ($i = 0; $i < $timeCount; $i++){
			$defObj = $timePeriodArray[$i];
			$title		= $defObj->title;			// 時間枠タイトル
			$startTime	= $defObj->startTime;		// 開始時間
			$minute		= $defObj->minute;			// 時間枠(分)
			
			$queryStr  = 'INSERT INTO time_period ';
			$queryStr .=   '(to_date_type_id, to_index, to_name, to_start_time, to_minute) ';
			$queryStr .= 'VALUES ';
			$queryStr .=   '(?, ?, ?, ?, ?)';
			$this->execStatement($queryStr, array($id, $i, $title, $startTime, $minute));			
		}
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
}
?>
