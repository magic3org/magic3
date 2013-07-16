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
 * @version    SVN: $Id: reserve_mainDb.php 567 2008-05-01 02:55:52Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getDbPath() . '/baseDb.php');

class reserve_mainDb extends BaseDb
{
	/**
	 * 予約定義値を取得
	 *
	 * @param int $id		定義ID
	 * @param string $key	キーとなる項目値
	 * @return string		値
	 */
	function getReserveConfig($id, $key)
	{
		$retValue = '';
		$queryStr = 'SELECT rc_value FROM reserve_config ';
		$queryStr .=  'WHERE rc_id  = ? ';
		$queryStr .=    'AND rc_key = ? ';
		$ret = $this->selectRecord($queryStr, array($id, $key), $row);
		if ($ret) $retValue = $row['rc_value'];
		return $retValue;
	}
	/**
	 * 予約定義値を更新
	 *
	 * @param int $id		定義ID
	 * @param string $key	キーとなる項目値
	 * @param string $value	設定値
	 * @return bool			true=更新成功、false=更新失敗
	 */
	function updateReserveConfig($id, $key, $value)
	{
		$queryStr = 'UPDATE reserve_config SET rc_value = ? ';
		$queryStr .=  'WHERE rc_id  = ? ';
		$queryStr .=    'AND rc_key = ? ';
		$params = array($value, $id, $key);
		$ret = $this->execStatement($queryStr, $params);
		return $ret;
	}
	/**
	 * リソース総数取得
	 *
	 * @param int		$type		リソースタイプ
	 * @param int		$configId	定義ID
	 * @return int					総数
	 */
	function getAllResourceListCount($type, $configId)
	{
		$queryStr = 'SELECT * FROM reserve_resource ';
		$queryStr .=  'WHERE rr_deleted = false ';// 削除されていない
		$queryStr .=    'AND rr_type = ? ';
		$queryStr .=    'AND rr_config_id = ? ';
		return $this->selectRecordCount($queryStr, array($type, $configId));
	}
	/**
	 * リソースリスト取得
	 *
	 * @param int		$type		リソースタイプ
	 * @param int		$configId	定義ID
	 * @param int		$limit		取得する項目数
	 * @param int		$page		取得するページ(1～)
	 * @param function $callback	コールバック関数
	 * @return						なし
	 */
	function getAllResourceList($type, $configId, $limit, $page, $callback)
	{
		$offset = $limit * ($page -1);
		if ($offset < 0) $offset = 0;
		
		$queryStr = 'SELECT * FROM reserve_resource ';
		$queryStr .=  'WHERE rr_deleted = false ';// 削除されていない
		$queryStr .=    'AND rr_type = ? ';
		$queryStr .=    'AND rr_config_id = ? ';
		$queryStr .=  'ORDER BY rr_sort_order limit ' . $limit . ' offset ' . $offset;
		$this->selectLoop($queryStr, array($type, $configId), $callback);
	}
	/**
	 * リソースリスト取得
	 *
	 * @param int		$type		リソースタイプ
	 * @param int		$configId	定義ID
	 * @param function $callback	コールバック関数
	 * @return						なし
	 */
	function getAllResource($type, $configId, $callback)
	{
		$queryStr = 'SELECT * FROM reserve_resource ';
		$queryStr .=  'WHERE rr_deleted = false ';// 削除されていない
		$queryStr .=    'AND rr_type = ? ';
		$queryStr .=    'AND rr_config_id = ? ';
		$queryStr .=  'ORDER BY rr_sort_order';
		$this->selectLoop($queryStr, array($type, $configId), $callback);
	}
	/**
	 * リソース最大表示順を取得
	 *
	 * @param int		$type		リソースタイプ
	 * @param int		$configId	定義ID
	 * @return int					最大インデックス
	 */
	function getMaxResourceIndex($type, $configId)
	{
		$index = 0;
		$queryStr = 'SELECT * FROM reserve_resource ';
		$queryStr .=  'WHERE rr_deleted = false ';// 削除されていない
		$queryStr .=    'AND rr_type = ? ';
		$queryStr .=    'AND rr_config_id = ? ';
		$queryStr .=  'ORDER BY rr_sort_order desc';
		$ret = $this->selectRecord($queryStr, array($type, $configId), $row);
		if ($ret) $index = $row['rr_sort_order'];
		return $index;
	}
	/**
	 * リソース情報を更新、または追加
	 *
	 * @param int $resourceId		リソースID(0のとき追加)
	 * @param int		$type		リソースタイプ
	 * @param int		$configId	定義ID
	 * @param string	$name		名前
	 * @param string	$desc		説明
	 * @param bool $visible			表示状態
	 * @param int $sortOrder		表示順
	 * @param int $newResourceId	新規リソースID
	 * @return						true=成功、false=失敗
	 */
	function updateResource($resourceId, $type, $configId, $name, $desc, $visible, $sortOrder, &$newResourceId)
	{
		global $gEnvManager;

		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $gEnvManager->getCurrentUserId();	// 現在のユーザ
		
		// トランザクション開始
		$this->startTransaction();

		if (empty($resourceId)){
			// インデックス番号を取得
			$newResourceId = 1;
			$queryStr = 'SELECT max(rr_id) as m FROM reserve_resource ';
			$ret = $this->selectRecord($queryStr, array(), $row);
			if ($ret) $newResourceId = $row['m'] + 1;
		
			$queryStr = 'INSERT INTO reserve_resource (';
			$queryStr .=  'rr_id, ';
			$queryStr .=  'rr_type, ';
			$queryStr .=  'rr_config_id, ';
			$queryStr .=  'rr_name, ';
			$queryStr .=  'rr_description, ';
			$queryStr .=  'rr_visible, ';
			$queryStr .=  'rr_sort_order, ';
			$queryStr .=  'rr_update_user_id, ';
			$queryStr .=  'rr_update_dt ';
			$queryStr .=  ') VALUES (';
			$queryStr .=  '?, ?, ?, ?, ?, ?, ?, ?, ?';
			$queryStr .=  ')';
			$this->execStatement($queryStr, array($newResourceId, $type, $configId, $name, $desc, $visible, $sortOrder, $userId, $now));
		} else {
			$queryStr  = 'UPDATE reserve_resource ';
			$queryStr .=   'SET rr_type = ?, ';
			$queryStr .=     'rr_config_id = ?, ';			
			$queryStr .=     'rr_name = ?, ';
			$queryStr .=     'rr_description = ?, ';
			$queryStr .=     'rr_visible = ?, ';
			$queryStr .=     'rr_sort_order = ?, ';
			$queryStr .=     'rr_update_user_id = ?, ';
			$queryStr .=     'rr_update_dt = ? ';
			$queryStr .=   'WHERE rr_id = ? ';
			$this->execStatement($queryStr, array($type, $configId, $name, $desc, $visible, $sortOrder, $userId, $now, $resourceId));
		}
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * リソース取得
	 *
	 * @param int		$id			リソースID
	 * @param int		$row		取得するページ(1～)
	 * @return						true=成功、false=失敗
	 */
	function getResourceById($id, &$row)
	{
		$queryStr = 'SELECT * FROM reserve_resource ';
		$queryStr .=  'WHERE rr_id = ? ';
		$ret = $this->selectRecord($queryStr, array($id), $row);
		return $ret;
	}
	/**
	 * リソース情報をリソースIDで削除
	 *
	 * @param array   $id		リソースID
	 * @return					true=成功、false=失敗
	 */
	function deleteResourceById($id)
	{
		global $gEnvManager;
		
		// 引数のエラーチェック
		if (!is_array($id)) return false;
		if (count($id) <= 0) return true;
		
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $gEnvManager->getCurrentUserId();	// 現在のユーザ
		
		// トランザクション開始
		$this->startTransaction();
		
		// 指定のシリアルNoのレコードが削除状態でないかチェック
		for ($i = 0; $i < count($id); $i++){
			$queryStr  = 'SELECT * FROM reserve_resource ';
			$queryStr .=   'WHERE rr_deleted = false ';		// 未削除
			$queryStr .=     'AND rr_id = ? ';
			$ret = $this->isRecordExists($queryStr, array($id[$i]));
			// 存在しない場合は、既に削除されたとして終了
			if (!$ret){
				$this->endTransaction();
				return false;
			}
		}
		
		// レコードを削除
		$queryStr  = 'UPDATE reserve_resource ';
		$queryStr .=   'SET rr_deleted = true, ';	// 削除
		$queryStr .=     'rr_update_user_id = ?, ';
		$queryStr .=     'rr_update_dt = ? ';
		$queryStr .=   'WHERE rr_id in (' . implode($id, ',') . ') ';
		$this->execStatement($queryStr, array($userId, $now));
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * 予約カレンダーの通常データをすべて削除
	 *
	 * @param int $id				定義ID
	 * @return						true=成功、false=失敗
	 */
	function deleteAllCalendar($id)
	{
		// 全商品を削除
		$queryStr  = 'DELETE FROM reserve_calendar ';
		$queryStr .=   'WHERE ra_config_id = ? ';
		$queryStr .=   'AND ra_usual = true ';
		$ret = $this->execStatement($queryStr, array($id));
		return $ret;
	}
	/**
	 * 予約カレンダー情報を追加
	 *
	 * @param int $id				定義ID
	 * @param bool $usual			通常日あるいは特定日
	 * @param int $specifyType		指定方法
	 * @param int $attr				日にち属性
	 * @param date $date			日付
	 * @param int $startTime		開始時間
	 * @param int $endTime			終了時間
	 * @param bool $available		利用可能あるいは利用不可
	 * @return						true=成功、false=失敗
	 */
	function addCalendar($id, $usual, $specifyType, $attr, $date, $startTime, $endTime, $available)
	{
		$params = array();
		$replace = array();
		$queryStr = 'INSERT INTO reserve_calendar (';
		$queryStr .=  'ra_config_id, ';						$params[] = $id;	$replace[] = '?';
		$queryStr .=  'ra_usual, ';						$params[] = $usual;	$replace[] = '?';
		$queryStr .=  'ra_specify_type, ';				$params[] = $specifyType;	$replace[] = '?';
		$queryStr .=  'ra_day_attribute, ';				$params[] = $attr;	$replace[] = '?';
		if (!empty($date)){
			$queryStr .=  'ra_date, ';					$params[] = $date;	$replace[] = '?';
		}
		$queryStr .=  'ra_start_time, ';				$params[] = $startTime;	$replace[] = '?';
		$queryStr .=  'ra_end_time, ';					$params[] = $endTime;	$replace[] = '?';
		$queryStr .=  'ra_available ';					$params[] = $available;	$replace[] = '?';
		$queryStr .=  ') VALUES (';
		$queryStr .=  implode(",", $replace);
		$queryStr .=  ')';
		$ret = $this->execStatement($queryStr, $params);
		return $ret;
	}
	/**
	 * 予約カレンダーの曜日設定値を取得
	 *
	 * @param int $id		定義ID
	 * @param array	$rows			取得データ
	 * @return						true=成功、false=失敗
	 */
	function getCalendarByWeek($id, &$rows)
	{
		$queryStr = 'SELECT * FROM reserve_calendar ';
		$queryStr .=  'WHERE ra_config_id = ? AND ra_usual = true ';
		$queryStr .=    'AND (ra_specify_type = 0 OR ra_specify_type = 1)';		// デフォルトあるいは曜日指定
		$queryStr .=  'ORDER BY ra_specify_type, ra_start_time ';
		return $this->selectRecords($queryStr, array($id), $rows);
	}
	/**
	 * ユーザリスト取得
	 *
	 * @param string	$type				機能タイプ
	 * @param int		$limit				取得する項目数
	 * @param int		$page				取得するページ(1～)
	 * @param function $callback	コールバック関数
	 * @return						なし
	 */
	function getAllUserList($type, $limit, $page, $callback)
	{
		$offset = $limit * ($page -1);
		if ($offset < 0) $offset = 0;
		
		$queryStr = 'SELECT * FROM _login_user LEFT JOIN _login_user_info on lu_id = li_id ';
		$queryStr .=  'WHERE lu_deleted = false AND li_deleted = false ';// 削除されていない
		$queryStr .=  'AND lu_assign LIKE \'%' . $type . ',%\' ';
		$queryStr .=  'ORDER BY lu_account limit ' . $limit . ' offset ' . $offset;
		$this->selectLoop($queryStr, array(), $callback);
	}
	/**
	 * ユーザ総数取得
	 *
	 * @param string	$type				機能タイプ
	 * @return int							総数
	 */
	function getAllUserListCount($type)
	{
		$queryStr = 'SELECT * FROM _login_user LEFT JOIN _login_user_info on lu_id = li_id ';
		$queryStr .=  'WHERE lu_deleted = false AND li_deleted = false ';// 削除されていない
		$queryStr .=  'AND lu_assign LIKE \'%' . $type . ',%\' ';
		return $this->selectRecordCount($queryStr, array());
	}
	/**
	 * ユーザ情報をシリアル番号で取得
	 *
	 * @param string	$serial				シリアル番号
	 * @param array     $row				レコード
	 * @return bool							取得 = true, 取得なし= false
	 */
	function getUserBySerial($serial, &$row)
	{
		$queryStr  = 'SELECT * FROM _login_user LEFT JOIN _login_user_info on lu_id = li_id ';
		$queryStr .=   'WHERE lu_serial = ? AND li_deleted = false ';
		$ret = $this->selectRecord($queryStr, array($serial), $row);
		return $ret;
	}
	/**
	 * 新規ユーザの追加
	 *
	 * @param string  $name			名前
	 * @param string  $account		アカウント
	 * @param string  $password		パスワード
	 * @param string  $widgetId		ウィジェットID
	 * @param int     $newId		新規に作成したログインユーザID
	 * @param int     $newSerial	新規レコードのシリアル番号
	 * @return						true=成功、false=失敗
	 */
	function addUser($name, $account, $password, $widgetId, &$newId, &$newSerial)
	{
		global $gEnvManager;
			
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $gEnvManager->getCurrentUserId();	// 現在のユーザ
		
		// 新規IDを作成
		$newId = 1;
		$queryStr = 'select max(lu_id) as ms from _login_user ';
		$ret = $this->selectRecord($queryStr, array(), $row);
		if ($ret) $newId = $row['ms'] + 1;
		
		// ユーザ種別を設定
		$userType = UserInfo::USER_TYPE_NORMAL;		// 一般ユーザ
		$subject = 'rv,';		// 予約機能(rv)にアクセス可能
		
		// 新規レコードを追加
		$queryStr  = 'INSERT INTO _login_user (';
		$queryStr .=   'lu_id, ';
		$queryStr .=   'lu_history_index, ';
		$queryStr .=   'lu_name, ';
		$queryStr .=   'lu_account, ';
		$queryStr .=   'lu_password, ';
		$queryStr .=   'lu_user_type, ';
		$queryStr .=   'lu_assign, ';
		$queryStr .=   'lu_enable_login, ';
		$queryStr .=   'lu_widget_id, ';
		$queryStr .=   'lu_create_user_id, ';
		$queryStr .=   'lu_create_dt ';
		$queryStr .= ') VALUES (';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?) ';
		$ret = $this->execStatement($queryStr, array($newId, 0, $name, $account, $password, $userType, $subject, 1, $widgetId, $userId, $now));
		
		// 新規のシリアル番号取得
		$queryStr = 'select max(lu_serial) as ns from _login_user ';
		$ret = $this->selectRecord($queryStr, array(), $row);
		if ($ret) $newSerial = $row['ns'];
		return $ret;
	}
	/**
	 * ユーザの更新
	 *
	 * @param int $serial			シリアル番号
	 * @param string  $name			ユーザ名
	 * @param string  $account		アカウント
	 * @param string  $password		パスワード(空のときは更新しない)
	 * @param int $userId			ユーザID
	 * @param int     $newSerial	新規シリアル番号
	 * @return						true=成功、false=失敗
	 */
	function updateUser($serial, $name, $account, $password, &$newSerial)
	{
		global $gEnvManager;
			
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $gEnvManager->getCurrentUserId();	// 現在のユーザ
		
		// 指定のシリアルNoのレコードが削除状態でないかチェック
		$historyIndex = 0;		// 履歴番号
		$queryStr  = 'select * from _login_user ';
		$queryStr .=   'where lu_serial = ? ';
		$ret = $this->selectRecord($queryStr, array($serial), $row);
		if ($ret){		// 既に登録レコードがあるとき
			if ($row['lu_deleted']){		// レコードが削除されていれば終了
				return false;
			}
			$historyIndex = $row['lu_history_index'] + 1;
		} else {		// 存在しない場合は終了
			return false;
		}
		
		// パスワードが設定されているときは更新
		$pwd = $row['lu_password'];
		if (!empty($password)) $pwd = $password;
		
		// 古いレコードを削除
		$queryStr  = 'UPDATE _login_user ';
		$queryStr .=   'SET lu_deleted = true, ';	// 削除
		$queryStr .=     'lu_update_user_id = ?, ';
		$queryStr .=     'lu_update_dt = ? ';
		$queryStr .=   'WHERE lu_serial = ?';
		$this->execStatement($queryStr, array($userId, $now, $serial));

		// 新規レコード追加
		$queryStr  = 'INSERT INTO _login_user (';
		$queryStr .=   'lu_id, ';
		$queryStr .=   'lu_history_index, ';
		$queryStr .=   'lu_name, ';
		$queryStr .=   'lu_account, ';
		$queryStr .=   'lu_password, ';
		$queryStr .=   'lu_user_type, ';
		$queryStr .=   'lu_assign, ';
		$queryStr .=   'lu_widget_id, ';
		$queryStr .=   'lu_enable_login, ';
		$queryStr .=   'lu_create_user_id, ';
		$queryStr .=   'lu_create_dt ';
		$queryStr .= ') VALUES (';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?) ';
		$this->execStatement($queryStr, array($row['lu_id'], $historyIndex, $name, $account, $pwd,
							$row['lu_user_type'], $row['lu_assign'], $row['lu_widget_id'], $row['lu_enable_login'], $userId, $now));

		// 新規のシリアル番号取得
		$queryStr = 'select max(lu_serial) as ns from _login_user ';
		$ret = $this->selectRecord($queryStr, array(), $row);
		if ($ret) $newSerial = $row['ns'];
		return $ret;
	}
	/**
	 * ユーザNoが存在するかチェック
	 *
	 * @param string $no	ユーザNo
	 * @return				true=存在する、false=存在しない
	 */
	function isExistsUserNo($no)
	{
		$queryStr = 'SELECT * from _login_user_info ';
		$queryStr .=  'WHERE li_no = ? ';
		$queryStr .=    'AND li_deleted = false';
		return $this->isRecordExists($queryStr, array($no));
	}
	/**
	 * 都道府県を取得
	 *
	 * @param string	$coutryId			国ID
	 * @param string	$lang				言語
	 * @param function	$callback			コールバック関数
	 * @return 			なし
	 */
	function getAllState($coutryId, $lang, $callback)
	{
		$queryStr = 'SELECT * FROM geo_zone ';
		$queryStr .=  'WHERE gz_country_id = ? AND gz_type = 1 AND gz_language_id = ? ';
		$queryStr .=  'ORDER BY gz_index ';
		$this->selectLoop($queryStr, array($coutryId, $lang), $callback, null);
	}
	/**
	 * ユーザリスト取得(メニュー選択用)
	 *
	 * @param string	$type				機能タイプ
	 * @param int		$limit				取得する項目数
	 * @param int		$page				取得するページ(1～)
	 * @param function $callback	コールバック関数
	 * @return						なし
	 */
	function getAllUserListForMenu($type, $callback)
	{
		$queryStr = 'SELECT * FROM _login_user LEFT JOIN _login_user_info on lu_id = li_id ';
		$queryStr .=  'WHERE lu_deleted = false AND li_deleted = false ';// 削除されていない
		$queryStr .=  'AND lu_assign LIKE \'%' . $type . ',%\' ';
		$queryStr .=  'ORDER BY li_family_name_kana, li_first_name_kana, li_id';
		$this->selectLoop($queryStr, array(), $callback);
	}
	/**
	 * 予約状況を取得
	 *
	 * @param int $id				リソースID
	 * @param int $userId			ユーザID
	 * @param timestamp $date		データを取得する先頭日付
	 * @param int $status			取得するステータス(0=すべて、1=予約、2=キャンセル)
	 * @param array	$rows			取得データ
	 * @return						true=成功、false=失敗
	 */
	function getReserveStatus($id, $userId, $date, $status, &$rows)
	{
		$params = array();
		$queryStr = 'SELECT * FROM reserve_status ';
		$queryStr .=  'WHERE rs_deleted = false ';		// データが削除されていない
		$queryStr .=    'AND rs_resource_id = ? '; $params[] = $id;		// リソースID
		$queryStr .=    'AND rs_user_id = ? '; $params[] = $userId;		// ユーザID
		if (!empty($date)){
			$queryStr .=    'AND ? <= rs_start_dt ';
			$params[] = $date;
		}
		if (!empty($status)){
			$queryStr .=    'AND rs_status = ? ';
			$params[] = $status;		// ステータス
		}
		$queryStr .=  'ORDER BY rs_start_dt ';
		return $this->selectRecords($queryStr, $params, $rows);
	}
	/**
	 * 予約状況を日付範囲で取得
	 *
	 * @param int $id				リソースID
	 * @param timestamp $startDate		データを取得する先頭日付
	 * @param timestamp $endDate		データを取得する終了日付(結果にこの日付のデータは含まれない)
	 * @param int $status			取得するステータス(0=すべて、1=予約、2=キャンセル)
	 * @param array	$rows			取得データ
	 * @return						true=成功、false=失敗
	 */
	function getReserveStatusByDate($id, $startDate, $endDate, $status, &$rows)
	{
		$params = array();
		$queryStr = 'SELECT * FROM reserve_status LEFT JOIN _login_user_info on rs_user_id = li_id ';
		$queryStr .=  'WHERE rs_deleted = false AND li_deleted = false ';		// データが削除されていない
		$queryStr .=    'AND rs_resource_id = ? '; $params[] = $id;		// リソースID
		if (!empty($startDate)){
			$queryStr .=    'AND ? <= rs_start_dt ';
			$params[] = $startDate;
		}
		if (!empty($endDate)){
			$queryStr .=    'AND rs_start_dt < ? ';
			$params[] = $endDate;
		}
		if (!empty($status)){
			$queryStr .=    'AND rs_status = ? ';
			$params[] = $status;		// ステータス
		}
		$queryStr .=  'ORDER BY rs_start_dt, rs_serial ';
		return $this->selectRecords($queryStr, $params, $rows);
	}
	/**
	 * 指定時間の予約数を取得
	 *
	 * @param int $id				リソースID
	 * @param int $userId			ユーザID(0のときはすべてのユーザ)
	 * @param timestamp $date		データを取得する日付時間
	 * @param int $status			取得するステータス(0=すべて、1=予約、2=キャンセル)
	 * @param array	$rows			取得データ
	 * @return						true=成功、false=失敗
	 */
	function getReserveStatusCountByDateTime($id, $userId, $date, $status)
	{
		$params = array();
		$queryStr = 'SELECT * FROM reserve_status ';
		$queryStr .=  'WHERE rs_deleted = false ';		// データが削除されていない
		$queryStr .=    'AND rs_resource_id = ? '; $params[] = $id;		// リソースID
		if (!empty($userId)){
			$queryStr .=    'AND rs_user_id = ? ';
			$params[] = $userId;
		}
		if (!empty($date)){
			$queryStr .=    'AND rs_start_dt = ? ';
			$params[] = $date;
		}
		if (!empty($status)){
			$queryStr .=    'AND rs_status = ? ';
			$params[] = $status;		// ステータス
		}
		return $this->selectRecordCount($queryStr, $params);
	}
	/**
	 * 新規予約の追加
	 *
	 * @param int $id				リソースID
	 * @param int $userId			ユーザID
	 * @param timestamp $date		日付
	 * @param int $status			ステータス(1=予約、2=キャンセル)
	 * @param string $note			備考
	 * @param int     $newSerial	新規レコードのシリアル番号
	 * @return						true=成功、false=失敗
	 */
	function addReserveStatus($id, $userId, $date, $status, $note, &$newSerial)
	{
		global $gEnvManager;
			
		$now = date("Y/m/d H:i:s");	// 現在日時
		$updateUserId = $gEnvManager->getCurrentUserId();	// データ更新ユーザ
		
		// トランザクション開始
		$this->startTransaction();
		
		// 新規レコードを追加
		$queryStr  = 'INSERT INTO reserve_status (';
		$queryStr .=   'rs_resource_id, ';
		$queryStr .=   'rs_user_id, ';
		$queryStr .=   'rs_status, ';
		$queryStr .=   'rs_start_dt, ';
		$queryStr .=   'rs_note, ';
		$queryStr .=   'rs_create_user_id, ';
		$queryStr .=   'rs_create_dt ';
		$queryStr .= ') VALUES (';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?) ';
		$ret = $this->execStatement($queryStr, array($id, $userId, $status, $date, $note, $updateUserId, $now));
		
		// 新規のシリアル番号取得
		$queryStr = 'select max(rs_serial) as ns from reserve_status ';
		$ret = $this->selectRecord($queryStr, array(), $row);
		if ($ret) $newSerial = $row['ns'];
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * 予約の更新
	 *
	 * @param int $serial			シリアル番号
	 * @param int $id				リソースID(確認用)
	 * @param int $userId			ユーザID(確認用)
	 * @param int $status			ステータス(1=予約、2=キャンセル)
	 * @param string $note			備考
	 * @return						true=成功、false=失敗
	 */
	function updateReserveStatus($serial, $id, $userId, $status, $note)
	{
		global $gEnvManager;
			
		$now = date("Y/m/d H:i:s");	// 現在日時
		$updateUserId = $gEnvManager->getCurrentUserId();	// データ更新ユーザ
		
		// トランザクション開始
		$this->startTransaction();
		
		$queryStr  = 'SELECT * FROM reserve_status ';
		$queryStr .=   'WHERE rs_serial = ? ';
		$queryStr .=   'AND rs_resource_id = ? ';
		$queryStr .=   'AND rs_user_id = ? ';
		$ret = $this->selectRecord($queryStr, array($serial, $id, $userId), $row);
		if ($ret){		// 既に登録レコードがあるとき
			if ($row['rs_deleted']){		// レコードが削除されていれば終了
				// トランザクション確定
				$ret = $this->endTransaction();
				return false;
			}
		} else {		// 存在しない場合は終了
			// トランザクション確定
			$ret = $this->endTransaction();
			return false;
		}
		
		// データを更新
		$queryStr  = 'UPDATE reserve_status ';
		$queryStr .=   'SET rs_status = ?, ';
		$queryStr .=     'rs_note = ?, ';
		$queryStr .=     'rs_update_user_id = ?, ';
		$queryStr .=     'rs_update_dt = ? ';
		$queryStr .=   'WHERE rs_serial = ?';
		$this->execStatement($queryStr, array($status, $note, $updateUserId, $now, $serial));
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * 予約状況をシリアル番号で取得
	 *
	 * @param string	$serial				シリアル番号
	 * @param array     $row				レコード
	 * @return bool							取得 = true, 取得なし= false
	 */
	function getReserveStatusBySerial($serial, &$row)
	{
		$queryStr = 'SELECT * FROM reserve_status LEFT JOIN _login_user_info on rs_user_id = li_id ';
		$queryStr .=  'WHERE rs_deleted = false ';		// データが削除されていない
		$queryStr .=   'AND rs_serial = ? ';
		$ret = $this->selectRecord($queryStr, array($serial), $row);
		return $ret;
	}
}
?>
