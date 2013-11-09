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
	 * 時間割一覧を取得
	 *
	 * @param int		$dateTypeId		日付タイプ
	 * @param array  	$rows			取得レコード
	 * @return							true=取得、false=取得せず
	 */
	function getTimePeriodRecords($dateTypeId, &$rows)
	{
		$queryStr  = 'SELECT * FROM time_period ';
		$queryStr .=   'WHERE to_date_type_id = ? ';
		$queryStr .=   'ORDER BY to_index';
		$ret = $this->selectRecords($queryStr, array($dateTypeId), $rows);
		return $ret;
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
	/**
	 * カレンダー定義一覧を取得
	 *
	 * @param function	$callback			コールバック関数
	 * @return 			なし
	 */
	function getCalendarDefList($callback)
	{
		$queryStr  = 'SELECT * FROM calendar_def LEFT JOIN _login_user ON cd_update_user_id = lu_id AND lu_deleted = false ';
		$queryStr .=   'WHERE cd_deleted = false ';		// 削除されていない
		$queryStr .=   'ORDER BY cd_id';
		$this->selectLoop($queryStr, array(), $callback);
	}
	/**
	 * カレンダー定義の追加更新
	 *
	 * @param string $id				定義ID。0のときは新規追加
	 * @param string $name				カレンダー定義名
	 * @param int    $repeatType		繰り返しタイプ
	 * @param int    $dateCount			所要日数
	 * @param string $openDateStyle		開業日スタイル
	 * @param string $closedDateStyle	休業日スタイル
	 * @param int    $newId				新規ID
	 * @return							true = 正常、false=異常
	 */
	function updateCalendarDef($id, $name, $repeatType, $dateCount, $openDateStyle, $closedDateStyle, &$newId)
	{
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $this->gEnv->getCurrentUserId();	// 現在のユーザ
		$historyIndex = 0;
		
		// トランザクション開始
		$this->startTransaction();
		
		if (empty($id)){		// 新規追加の場合
			// 新規IDを作成
			$queryStr  = 'SELECT MAX(cd_id) AS mi FROM calendar_def ';
			$ret = $this->selectRecord($queryStr, array(), $row);
			if ($ret){
				$newId = $row['mi'] + 1;
			} else {
				$newId = 1;
			}
			$id = $newId;
		} else {
			// 旧レコード取得
			$queryStr  = 'SELECT * FROM calendar_def ';
			$queryStr .=   'WHERE cd_id = ? ';
			$queryStr .=   'ORDER BY cd_history_index DESC ';
			$ret = $this->selectRecord($queryStr, array($id), $row);
			if ($ret){
				$historyIndex = $row['cd_history_index'] + 1;
		
				// レコードが削除されていない場合は削除
				if (!$row['cd_deleted']){
					// 古いレコードを削除
					$queryStr  = 'UPDATE calendar_def ';
					$queryStr .=   'SET cd_deleted = true, ';	// 削除
					$queryStr .=     'cd_update_user_id = ?, ';
					$queryStr .=     'cd_update_dt = ? ';
					$queryStr .= 'WHERE cd_serial = ?';
					$this->execStatement($queryStr, array($userId, $now, $row['cd_serial']));
					if (!$ret){
						// トランザクション終了
						$this->endTransaction();
				
						return false;
					}
				}
			}
		}

		// レコードを追加
		$queryStr  = 'INSERT INTO calendar_def ';
		$queryStr .=   '(cd_id, cd_history_index, cd_name, cd_repeat_type, cd_date_count, cd_open_date_style, cd_closed_date_style, cd_create_user_id, cd_create_dt) ';
		$queryStr .= 'VALUES ';
		$queryStr .=   '(?, ?, ?, ?, ?, ?, ?, ?, ?)';
		$this->execStatement($queryStr, array($id, $historyIndex, $name, intval($repeatType), $dateCount, $openDateStyle, $closedDateStyle, $userId, $now));
			
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * カレンダー定義の削除
	 *
	 * @param array  $idArray		ID
	 * @return bool					true=成功、false=失敗
	 */
	function deleteCalendarDef($idArray)
	{
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $this->gEnv->getCurrentUserId();	// 現在のユーザ
		
		if (empty($idArray)) return false;
		
		// トランザクション開始
		$this->startTransaction();
		
		$queryStr  = 'UPDATE calendar_def ';
		$queryStr .=   'SET cd_deleted = true, ';	// 削除
		$queryStr .=     'cd_update_user_id = ?, ';
		$queryStr .=     'cd_update_dt = ? ';
		$queryStr .=   'WHERE cd_id in (' . implode($idArray, ',') . ') ';
		$queryStr .=     'AND cd_deleted = false ';	// 削除されていない
		$this->execStatement($queryStr, array($userId, $now));
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * カレンダー定義を取得
	 *
	 * @param string	$id				定義ID
	 * @param array     $row			レコード
	 * @return bool						取得 = true, 取得なし= false
	 */
	function getCalendarDef($id, &$row)
	{
		$queryStr  = 'SELECT * FROM calendar_def LEFT JOIN _login_user ON cd_create_user_id = lu_id AND lu_deleted = false ';
		$queryStr .=   'WHERE cd_deleted = false ';
		$queryStr .=     'AND cd_id = ? ';
		$ret = $this->selectRecord($queryStr, array(intval($id)), $row);
		return $ret;
	}
	/**
	 * 日付一覧を取得
	 *
	 * @param int       $defId				カレンダー定義ID
	 * @param int       $type				0=基本データ,1=個別データ
	 * @param function	$callback			コールバック関数
	 * @param timestamp	$startDt			期間(開始日)
	 * @param timestamp	$endDt				期間(終了日)
	 * @return 			なし
	 */
	function getDateList($defId, $type, $callback, $startDt = null, $endDt = null)
	{
		$params = array();
		$queryStr  = 'SELECT * FROM calendar_date ';
		$queryStr .=   'WHERE ce_def_id = ? '; $params[] = intval($defId);
		$queryStr .=     'AND ce_type = ? '; $params[] = intval($type);
		
		// 日付範囲
		if ($type == 1){		// 個別指定の場合
			if (!empty($startDt)){
				$queryStr .=    'AND ? <= ce_date ';
				$params[] = $startDt;
			}
			if (!empty($endDt)){
				$queryStr .=    'AND ce_date < ? ';
				$params[] = $endDt;
			}
		}
		
		$queryStr .=   'ORDER BY ce_index, ce_date';
		$this->selectLoop($queryStr, $params, $callback);
	}
	/**
	 * 日付の追加更新
	 *
	 * @param string $id				カレンダー定義ID
	 * @param int    $dataType			データタイプ(0=インデックス番号,1=日付)
	 * @param array $dateInfoArray		日付情報の配列
	 * @param array $timeInfoArray		時間割データ(データタイプ=1のとき)
	 * @return bool						true = 正常、false=異常
	 */
	function updateDate($id, $dataType, $dateInfoArray, $timeInfoArray = null)
	{
		// パラメータエラーチェック
		if (intval($id) <= 0) return false;
		
		// トランザクション開始
		$this->startTransaction();
		
		if ($dataType == 0){		// インデックス番号指定の場合
			// 旧データ削除
			$queryStr  = 'DELETE FROM calendar_date ';
			$queryStr .=   'WHERE ce_def_id = ? ';
			$queryStr .=     'AND ce_type = ? ';
			$this->execStatement($queryStr, array($id, $dataType));
		
			// レコードを追加
			$dateCount = count($dateInfoArray);
			for ($i = 0; $i < $dateCount; $i++){
				$defObj = $dateInfoArray[$i];
				$dateName	= $defObj->dateName;			// 名前
				$dateType	= $defObj->dateType;		// 日付タイプ
			
				$queryStr  = 'INSERT INTO calendar_date ';
				$queryStr .=   '(ce_def_id, ce_type, ce_index, ce_name, ce_date_type_id) ';
				$queryStr .= 'VALUES ';
				$queryStr .=   '(?, ?, ?, ?, ?)';
				$this->execStatement($queryStr, array($id, $dataType, $i, $dateName, $dateType));			
			}
		} else if ($dataType == 10){		// オプションデータあり、インデックス番号指定の場合
			// 旧データ削除
			$queryStr  = 'DELETE FROM calendar_date ';
			$queryStr .=   'WHERE ce_def_id = ? ';
			$queryStr .=     'AND ce_type = ? ';
			$this->execStatement($queryStr, array($id, $dataType));
		
			// レコードを追加
			$dateCount = count($dateInfoArray);
			for ($i = 0; $i < $dateCount; $i++){
				$defObj = $dateInfoArray[$i];
				$dateName	= $defObj->dateName;			// 名前
				$dateType	= $defObj->dateType;		// 日付タイプ
				$dateParam 	= serialize(array('no' => $defObj->dateNo, 'week' => $defObj->dateWeek));
			
				$queryStr  = 'INSERT INTO calendar_date ';
				$queryStr .=   '(ce_def_id, ce_type, ce_index, ce_name, ce_date_type_id, ce_param) ';
				$queryStr .= 'VALUES ';
				$queryStr .=   '(?, ?, ?, ?, ?, ?)';
				$this->execStatement($queryStr, array($id, $dataType, $i, $dateName, $dateType, $dateParam));			
			}
		} else if ($dataType == 1){			// 日付指定の場合
			// 旧レコード取得
			$queryStr  = 'SELECT * FROM calendar_date ';
			$queryStr .=   'WHERE ce_def_id = ? ';
			$queryStr .=     'AND ce_type = ? ';
			$queryStr .=     'AND ce_date_type_id = ? ';
			$ret = $this->selectRecords($queryStr, array($id, $dataType, -1/*個別定義*/), $rows);
			if ($ret){
				$dateTypeArray = array();
				for ($i = 0; $i < count($rows); $i++){
					$dateTypeId = intval($rows[$i]['ce_serial']) * (-1);		// カレンダー日付のシリアル番号を負にする
					$dateTypeArray[] = $dateTypeId;
				}
			
				// 時間枠データ削除
				$queryStr  = 'DELETE FROM time_period ';
				$queryStr .=   'WHERE to_date_type_id in (' . implode($dateTypeArray, ',') . ') ';
				$this->execStatement($queryStr, array());
			}
		
			// 旧データ削除
			$queryStr  = 'DELETE FROM calendar_date ';
			$queryStr .=   'WHERE ce_def_id = ? ';
			$queryStr .=     'AND ce_type = ? ';
			$this->execStatement($queryStr, array($id, $dataType));
			
			// レコードを追加
			$timeInfoIndex = 0;
			$dateCount = count($dateInfoArray);
			for ($i = 0; $i < $dateCount; $i++){
				$defObj = $dateInfoArray[$i];
				$date		= $defObj->date;				// 日付
				$dateName	= $defObj->dateName;			// 名前
				$dateType	= $defObj->dateType;		// 日付タイプ
			
				$queryStr  = 'INSERT INTO calendar_date ';
				$queryStr .=   '(ce_def_id, ce_type, ce_date, ce_name, ce_date_type_id) ';
				$queryStr .= 'VALUES ';
				$queryStr .=   '(?, ?, ?, ?, ?)';
				$this->execStatement($queryStr, array($id, $dataType, $date, $dateName, $dateType));
				
				if ($dateType == -1){		// 個別定義の場合
					// シリアル番号取得
					$queryStr  = 'SELECT max(ce_serial) AS ms FROM calendar_date ';
					$ret = $this->selectRecord($queryStr, array(), $row);
					$dateTypeId = intval($row['ms']) * (-1);
				
					// 時間割データを追加
					$timePeriodArray = $timeInfoArray[$timeInfoIndex++];
					$timeCount = count($timePeriodArray);
					for ($j = 0; $j < $timeCount; $j++){
						$timePeriod = $timePeriodArray[$j];
						$title		= $timePeriod['name'];		// 時間枠タイトル
						$startTime	= $timePeriod['time'];		// 開始時間
						$minute		= $timePeriod['minute'];		// 時間枠(分)
			
						$queryStr  = 'INSERT INTO time_period ';
						$queryStr .=   '(to_date_type_id, to_index, to_name, to_start_time, to_minute) ';
						$queryStr .= 'VALUES ';
						$queryStr .=   '(?, ?, ?, ?, ?)';
						$this->execStatement($queryStr, array($dateTypeId, $j, $title, $startTime, $minute));			
					}
				}
			}
		}
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * 簡易イベント一覧を取得(表示用)
	 *
	 * @param int		$limit				取得する項目数
	 * @param int		$page				取得するページ(1～)
	 * @param timestamp	$startDt			期間(開始日)
	 * @param timestamp	$endDt				期間(終了日)
	 * @param string	$langId				言語
	 * @param function	$callback			コールバック関数
	 * @return 			なし
	 */
	function getEvent($limit, $page, $startDt, $endDt, $langId, $callback)
	{
		$offset = $limit * ($page -1);
		if ($offset < 0) $offset = 0;
		$params = array();
		
		$queryStr  = 'SELECT * FROM calendar_event ';
		$queryStr .=   'WHERE cv_deleted = false ';		// 削除されていない
		$queryStr .=   'AND cv_visible = true ';		// 表示可
		
		// 期間が重なっている場合は取得
		$queryStr .=    'AND ((cv_start_dt <= ? AND ? < cv_end_dt) ';
		$params[] = $startDt;
		$params[] = $startDt;
		$queryStr .=    'OR (? <= cv_start_dt AND cv_start_dt < ?)) ';
		$params[] = $startDt;
		$params[] = $endDt;
		
		$queryStr .=  'ORDER BY cv_start_dt, cv_id LIMIT ' . $limit . ' OFFSET ' . $offset;
		$this->selectLoop($queryStr, $params, $callback);
	}
	/**
	 * 簡易イベント一覧を取得(管理用)
	 *
	 * @param int		$limit				取得する項目数
	 * @param int		$page				取得するページ(1～)
	 * @param timestamp	$startDt			期間(開始日)
	 * @param timestamp	$endDt				期間(終了日)
	 * @param array		$category			カテゴリーID
	 * @param array		$keywords			検索キーワード
	 * @param function	$callback			コールバック関数
	 * @return 			なし
	 */
	function searchEvent($limit, $page, $startDt, $endDt, $category, $keywords, $callback)
	{
		$offset = $limit * ($page -1);
		if ($offset < 0) $offset = 0;
		
		$params = array();
		if (count($category) == 0){		// カテゴリー指定なしのとき
			$queryStr = 'SELECT * FROM calendar_event ';
			$queryStr .=  'WHERE cv_deleted = false ';		// 削除されていない
		}
		
		// フィールドを検索
		if (!empty($keywords)){
			for ($i = 0; $i < count($keywords); $i++){
				$keyword = addslashes($keywords[$i]);// 「'"\」文字をエスケープ
				$queryStr .=    'AND (cv_name LIKE \'%' . $keyword . '%\' ';
				$queryStr .=    'OR cv_html LIKE \'%' . $keyword . '%\') ';
			}
		}
		
		// 日付範囲
		if (!empty($startDt)){
			$queryStr .=    'AND ? <= cv_start_dt ';
			$params[] = $startDt;
		}
		if (!empty($endDt)){
			$queryStr .=    'AND cv_start_dt < ? ';
			$params[] = $endDt;
		}
		
		if (count($category) == 0){
			$queryStr .=  'ORDER BY cv_start_dt desc, cv_id limit ' . $limit . ' offset ' . $offset;
			$this->selectLoop($queryStr, $params, $callback);
		}
	}
	/**
	 * 簡易イベント数を取得(管理用)
	 *
	 * @param timestamp	$startDt			期間(開始日)
	 * @param timestamp	$endDt				期間(終了日)
	 * @param array		$category			カテゴリーID
	 * @param array		$keywords			検索キーワード
	 * @return int							項目数
	 */
	function getEventCount($startDt, $endDt, $category, $keywords)
	{
		$params = array();
		if (count($category) == 0){		// カテゴリー指定なしのとき
			$queryStr = 'SELECT * FROM calendar_event ';
			$queryStr .=  'WHERE cv_deleted = false ';		// 削除されていない
		}
		
		// フィールドを検索
		if (!empty($keywords)){
			for ($i = 0; $i < count($keywords); $i++){
				$keyword = addslashes($keywords[$i]);// 「'"\」文字をエスケープ
				$queryStr .=    'AND (cv_name LIKE \'%' . $keyword . '%\' ';
				$queryStr .=    'OR cv_html LIKE \'%' . $keyword . '%\') ';
			}
		}
		
		// 日付範囲
		if (!empty($startDt)){
			$queryStr .=    'AND ? <= cv_start_dt ';
			$params[] = $startDt;
		}
		if (!empty($endDt)){
			$queryStr .=    'AND cv_start_dt < ? ';
			$params[] = $endDt;
		}
		
		if (count($category) == 0){
			return $this->selectRecordCount($queryStr, $params);
		}
	}
	/**
	 * 簡易イベントをシリアル番号で取得
	 *
	 * @param string	$serial				シリアル番号
	 * @param array     $row				レコード
	 * @param array     $categoryRow		簡易イベントカテゴリー
	 * @return bool							取得 = true, 取得なし= false
	 */
	function getEventBySerial($serial, &$row, &$categoryRow)
	{
		$queryStr  = 'SELECT * FROM calendar_event LEFT JOIN _login_user ON cv_create_user_id = lu_id AND lu_deleted = false ';
		$queryStr .=   'WHERE cv_serial = ? ';
		$ret = $this->selectRecord($queryStr, array($serial), $row);
		
		// 簡易イベントカテゴリー
		return $ret;
	}
	/**
	 * 簡易イベントの削除
	 *
	 * @param array   $serial		シリアルNo
	 * @return						true=成功、false=失敗
	 */
	function delEvent($serial)
	{
		// 引数のエラーチェック
		if (!is_array($serial)) return false;
		if (count($serial) <= 0) return true;
		
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $this->gEnv->getCurrentUserId();	// 現在のユーザ
		
		// トランザクション開始
		$this->startTransaction();
		
		for ($i = 0; $i < count($serial); $i++){
			$queryStr  = 'SELECT * FROM calendar_event ';
			$queryStr .=   'WHERE cv_deleted = false ';		// 未削除
			$queryStr .=     'AND cv_serial = ? ';
			$ret = $this->selectRecord($queryStr, array($serial[$i]), $row);
			if ($ret){		// 既に登録レコードがあるとき			
				// レコードを削除
				$queryStr  = 'UPDATE calendar_event ';
				$queryStr .=   'SET cv_deleted = true, ';	// 削除
				$queryStr .=     'cv_update_user_id = ?, ';
				$queryStr .=     'cv_update_dt = ? ';
				$queryStr .=   'WHERE cv_serial = ?';
				$this->execStatement($queryStr, array($userId, $now, $serial[$i]));
			} else {// 指定のシリアルNoのレコードが削除状態のときはエラー
				$this->endTransaction();
				return false;
			}
		}
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * 簡易イベントの新規追加
	 *
	 * @param string  $id			エントリーID
	 * @param string  $langId		言語ID
	 * @param string  $name			イベント名
	 * @param string  $html			HTML
	 * @param bool    $visible		表示可否
	 * @param bool    $isAllDay		終日イベントかどうか
	 * @param timestamp	$startDt	期間(開始日)
	 * @param timestamp	$endDt		期間(終了日)
	 * @param array   $category		カテゴリーID
	 * @param int     $newSerial	新規シリアル番号
	 * @return bool					true = 成功、false = 失敗
	 */
	function addEvent($id, $langId, $name, $html, $visible, $isAllDay, $startDt, $endDt, $category, &$newSerial)
	{
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $this->gEnv->getCurrentUserId();	// 現在のユーザ
			
		// トランザクション開始
		$this->startTransaction();
		
		if (empty($id)){		// エントリーIDが0のときは、エントリーIDを新規取得
			// エントリーIDを決定する
			$queryStr = 'SELECT MAX(cv_id) AS mid FROM calendar_event ';
			$ret = $this->selectRecord($queryStr, array(), $row);
			if ($ret){
				$entryId = $row['mid'] + 1;
			} else {
				$entryId = 1;
			}
		} else {
			$entryId = $id;
		}
		
		// 前レコードの削除状態チェック
		$historyIndex = 0;
		$queryStr  = 'SELECT * FROM calendar_event ';
		$queryStr .=   'WHERE cv_id = ? ';
		$queryStr .=   'ORDER BY cv_history_index DESC ';
		$ret = $this->selectRecord($queryStr, array($entryId), $row);
		if ($ret){
			if (!$row['cv_deleted']){		// レコード存在していれば終了
				$this->endTransaction();
				return false;
			}
			$historyIndex = $row['cv_history_index'] + 1;
		}
		
		// データを追加
		$queryStr  = 'INSERT INTO calendar_event ';
		$queryStr .=   '(cv_id, ';
		$queryStr .=   'cv_history_index, ';
		$queryStr .=   'cv_name, ';
		$queryStr .=   'cv_html, ';
		$queryStr .=   'cv_visible, ';
		$queryStr .=   'cv_is_all_day, ';
		$queryStr .=   'cv_start_dt, ';
		$queryStr .=   'cv_end_dt, ';
		$queryStr .=   'cv_create_user_id, ';
		$queryStr .=   'cv_create_dt) ';
		$queryStr .= 'VALUES ';
		$queryStr .=   '(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
		$this->execStatement($queryStr, array($entryId, $historyIndex, $name, $html, intval($visible), intval($isAllDay), $startDt, $endDt, $userId, $now));
		
		// 新規のシリアル番号取得
		$newSerial = 0;
		$queryStr = 'SELECT MAX(cv_serial) AS ns FROM calendar_event ';
		$ret = $this->selectRecord($queryStr, array(), $row);
		if ($ret) $newSerial = $row['ns'];
			
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * エントリー項目の更新
	 *
	 * @param int     $serial		シリアル番号
	 * @param string  $name			イベント名
	 * @param string  $html			HTML
	 * @param bool    $visible		表示可否
	 * @param bool    $isAllDay		終日イベントかどうか
	 * @param timestamp	$startDt	期間(開始日)
	 * @param timestamp	$endDt		期間(終了日)
	 * @param array   $category		カテゴリーID
	 * @param int     $newSerial	新規シリアル番号
	 * @return bool					true = 成功、false = 失敗
	 */
	function updateEvent($serial, $name, $html, $visible, $isAllDay, $startDt, $endDt, $category, &$newSerial)
	{
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $this->gEnv->getCurrentUserId();	// 現在のユーザ
						
		// トランザクション開始
		$this->startTransaction();
		
		// 指定のシリアルNoのレコードが削除状態でないかチェック
		$historyIndex = 0;		// 履歴番号
		$queryStr  = 'SELECT * FROM calendar_event ';
		$queryStr .=   'WHERE cv_serial = ? ';
		$ret = $this->selectRecord($queryStr, array($serial), $row);
		if ($ret){		// 既に登録レコードがあるとき
			if ($row['cv_deleted']){		// レコードが削除されていれば終了
				$this->endTransaction();
				return false;
			}
			$historyIndex = $row['cv_history_index'] + 1;
		} else {		// 存在しない場合は終了
			$this->endTransaction();
			return false;
		}
		// 古いレコードを削除
		$queryStr  = 'UPDATE calendar_event ';
		$queryStr .=   'SET cv_deleted = true, ';	// 削除
		$queryStr .=     'cv_update_user_id = ?, ';
		$queryStr .=     'cv_update_dt = ? ';
		$queryStr .=   'WHERE cv_serial = ?';
		$this->execStatement($queryStr, array($userId, $now, $serial));
		
		// 旧データを取得
		$entryId = $row['cv_id'];
		
		// データを追加
		$queryStr  = 'INSERT INTO calendar_event ';
		$queryStr .=   '(cv_id, ';
		$queryStr .=   'cv_history_index, ';
		$queryStr .=   'cv_name, ';
		$queryStr .=   'cv_html, ';
		$queryStr .=   'cv_visible, ';
		$queryStr .=   'cv_is_all_day, ';
		$queryStr .=   'cv_start_dt, ';
		$queryStr .=   'cv_end_dt, ';
		$queryStr .=   'cv_create_user_id, ';
		$queryStr .=   'cv_create_dt) ';
		$queryStr .= 'VALUES ';
		$queryStr .=   '(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
		$this->execStatement($queryStr, array($entryId, $historyIndex, $name, $html, intval($visible), intval($isAllDay), $startDt, $endDt, $userId, $now));

		// 新規のシリアル番号取得
		$newSerial = 0;
		$queryStr = 'SELECT MAX(cv_serial) AS ns FROM calendar_event ';
		$ret = $this->selectRecord($queryStr, array(), $row);
		if ($ret) $newSerial = $row['ns'];
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
}
?>
