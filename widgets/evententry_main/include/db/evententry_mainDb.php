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

class evententry_mainDb extends BaseDb
{
	/**
	 * イベント予約定義値を取得をすべて取得
	 *
	 * @param array  $rows			レコード
	 * @return bool					1行以上取得 = true, 取得なし= false
	 */
	function getAllConfig(&$rows)
	{
		$queryStr  = 'SELECT * FROM evententry_config ';
		$queryStr .=   'ORDER BY ef_index';
		$retValue = $this->selectRecords($queryStr, array(), $rows);
		return $retValue;
	}
	/**
	 * イベント予約定義値を取得
	 *
	 * @param string $key		キーとなる項目値
	 * @return string $value	値
	 */
	function getConfig($key)
	{
		$retValue = '';
		$queryStr = 'SELECT ef_value FROM evententry_config ';
		$queryStr .=  'WHERE ef_id  = ?';
		$ret = $this->selectRecord($queryStr, array($key), $row);
		if ($ret) $retValue = $row['ef_value'];
		return $retValue;
	}
	/**
	 * イベント予約定義値を更新
	 *
	 * @param string $key		キーとなる項目値
	 * @param string $value		値
	 * @return					true = 正常、false=異常
	 */
	function updateConfig($key, $value)
	{
		// データの確認
		$queryStr = 'SELECT ef_value FROM evententry_config ';
		$queryStr .=  'WHERE ef_id  = ?';
		$ret = $this->isRecordExists($queryStr, array($key));
		if ($ret){
			$queryStr = "UPDATE evententry_config SET ef_value = ? WHERE ef_id = ?";
			return $this->execStatement($queryStr, array($value, $key));
		} else {
			$queryStr = "INSERT INTO evententry_config (ef_id, ef_value) VALUES (?, ?)";
			return $this->execStatement($queryStr, array($key, $value));
		}
	}
	/**
	 * イベント項目数を取得(管理用)
	 *
	 * @param string $langId			言語ID
	 * @param array	 $keywords			検索キーワード
	 * @return int							項目数
	 */
	function getEventEntryListCount($langId, $keywords)
	{
		$params = array();
		$queryStr  = 'SELECT * FROM evententry LEFT JOIN event_entry ON et_event_id = ee_id AND ee_deleted = false ';
		$queryStr .=   'AND ee_language_id = ? '; $params[] = $langId;
		$queryStr .=  'WHERE et_deleted = false ';				// 削除されていない

		// 検索キーワード条件
		if (!empty($keywords)){
			for ($i = 0; $i < count($keywords); $i++){
				$keyword = addslashes($keywords[$i]);// 「'"\」文字をエスケープ
				
				$queryStr .=    'AND (ee_name LIKE \'%' . $keyword . '%\' ';
				$queryStr .=    'OR ee_html LIKE \'%' . $keyword . '%\' ';
				$queryStr .=    'OR ee_html_ext LIKE \'%' . $keyword . '%\' ';
				$queryStr .=    'OR ee_summary LIKE \'%' . $keyword . '%\' ';
				$queryStr .=    'OR ee_place LIKE \'%' . $keyword . '%\' ';
				$queryStr .=    'OR ee_contact LIKE \'%' . $keyword . '%\' ';
				$queryStr .=    'OR ee_url LIKE \'%' . $keyword . '%\' ';
				$queryStr .=    'OR ee_option_fields LIKE \'%' . $keyword . '%\') ';	// ユーザ定義フィールド
				$queryStr .=    'AND (et_code LIKE \'%' . $keyword . '%\' ';		// イベント予約受付コード
				$queryStr .=    'OR et_html LIKE \'%' . $keyword . '%\') ';			// 説明
			}
		}
		return $this->selectRecordCount($queryStr, $params);
	}
	/**
	 * イベント項目を検索(管理用)
	 *
	 * @param string    $langId				言語ID
	 * @param int		$limit				取得する項目数
	 * @param int		$page				取得するページ(1～)
	 * @param array		$keywords			検索キーワード
	 * @param function	$callback			コールバック関数
	 * @return 			なし
	 */
	function getEventEntryList($langId, $limit, $page, $keywords, $callback)
	{
		$offset = $limit * ($page -1);
		if ($offset < 0) $offset = 0;
		
		$params = array();
		$orderStr = '';
		$queryStr  = 'SELECT * FROM evententry LEFT JOIN event_entry ON et_event_id = ee_id AND ee_deleted = false ';
		$queryStr .=   'AND ee_language_id = ? '; $params[] = $langId;
		
		// 並び順
		$orderStr = 'ee_start_dt DESC, ee_id ';
		$queryStr .=  'WHERE et_deleted = false ';				// 削除されていない

		// 検索キーワード条件
		if (!empty($keywords)){
			for ($i = 0; $i < count($keywords); $i++){
				$keyword = addslashes($keywords[$i]);// 「'"\」文字をエスケープ
				
				$queryStr .=    'AND (ee_name LIKE \'%' . $keyword . '%\' ';
				$queryStr .=    'OR ee_html LIKE \'%' . $keyword . '%\' ';
				$queryStr .=    'OR ee_html_ext LIKE \'%' . $keyword . '%\' ';
				$queryStr .=    'OR ee_summary LIKE \'%' . $keyword . '%\' ';
				$queryStr .=    'OR ee_place LIKE \'%' . $keyword . '%\' ';
				$queryStr .=    'OR ee_contact LIKE \'%' . $keyword . '%\' ';
				$queryStr .=    'OR ee_url LIKE \'%' . $keyword . '%\' ';
				$queryStr .=    'OR ee_option_fields LIKE \'%' . $keyword . '%\') ';	// ユーザ定義フィールド
				$queryStr .=    'AND (et_code LIKE \'%' . $keyword . '%\' ';		// イベント予約受付コード
				$queryStr .=    'OR et_html LIKE \'%' . $keyword . '%\') ';			// 説明
			}
		}
		$queryStr .=  'ORDER BY ' . $orderStr . 'LIMIT ' . $limit . ' OFFSET ' . $offset;			// コンテンツID、受付タイプ
		$this->selectLoop($queryStr, $params, $callback);
	}
	/**
	 * イベント項目の新規追加
	 *
	 * @param string  $eventId		イベントID
	 * @param string  $entryType	受付タイプ
	 * @param array   $otherParams	その他のフィールド値
	 * @param int     $newSerial	新規シリアル番号
	 * @return bool					true = 成功、false = 失敗
	 */
	function addEventEntry($eventId, $entryType, $otherParams, &$newSerial)
	{
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $this->gEnv->getCurrentUserId();	// 現在のユーザ
			
		// トランザクション開始
		$this->startTransaction();
			
		// イベント予約IDを決定する
		$queryStr = 'SELECT MAX(et_id) AS mid FROM evententry';
		$ret = $this->selectRecord($queryStr, array(), $row);
		if ($ret){
			$id = $row['mid'] + 1;
		} else {
			$id = 1;
		}
		$historyIndex = 0;		// 履歴番号
	
		// 固定フィールドを作成
		$params = array();
		$queryStr  = 'INSERT INTO evententry ';
		$queryStr .=   '(';
		$queryStr .=   'et_id, ';				$params[] = $id;
		$queryStr .=   'et_event_id, ';			$params[] = $eventId;
		$queryStr .=   'et_type, ';				$params[] = $entryType;
		$queryStr .=   'et_history_index, ';	$params[] = $historyIndex;
		$queryStr .=   'et_create_user_id, ';	$params[] = $userId;
		$queryStr .=   'et_create_dt';			$params[] = $now;

		// その他のフィールド値を追加
		$otherValueStr = '';
		if (!empty($otherParams)){
			$keys = array_keys($otherParams);// キーを取得
			for ($i = 0; $i < count($keys); $i++){
				$fieldName = $keys[$i];
				$fieldValue = $otherParams[$fieldName];
				if (!isset($fieldValue)) continue;
				$params[] = $fieldValue;
				$queryStr .= ', ' . $fieldName;
				$otherValueStr .= ', ?';
			}
		}
		$queryStr .= ') VALUES ';
		$queryStr .=   '(?, ?, ?, ?, ?, ?, ?';
		$queryStr .=   $otherValueStr . ') ';
		$this->execStatement($queryStr, $params);

		// 新規のシリアル番号取得
		$newSerial = 0;
		$queryStr = 'SELECT MAX(et_serial) AS ns FROM evententry ';
		$ret = $this->selectRecord($queryStr, array(), $row);
		if ($ret) $newSerial = $row['ns'];
			
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * イベント項目の更新
	 *
	 * @param int     $serial		シリアル番号
	 * @param array   $otherParams	その他のフィールド値
	 * @param int     $newSerial	新規シリアル番号
	 * @return bool					true = 成功、false = 失敗
	 */
	function updateEventEntry($serial, $otherParams, &$newSerial)
	{
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $this->gEnv->getCurrentUserId();	// 現在のユーザ
			
		// トランザクション開始
		$this->startTransaction();
			
		// 指定のシリアルNoのレコードが削除状態でないかチェック
		$queryStr  = 'SELECT * FROM evententry ';
		$queryStr .=   'WHERE et_serial = ? ';
		$ret = $this->selectRecord($queryStr, array($serial), $row);
		if ($ret){		// 既に登録レコードがあるとき
			if ($row['et_deleted']){		// レコードが削除されていれば終了
				$this->endTransaction();
				return false;
			}
			$id = $row['et_id'];
			$historyIndex = $row['et_history_index'] + 1;
		} else {		// 存在しない場合は終了
			$this->endTransaction();
			return false;
		}
		// 古いレコードを削除
		$queryStr  = 'UPDATE evententry ';
		$queryStr .=   'SET et_deleted = true, ';	// 削除
		$queryStr .=     'et_update_user_id = ?, ';
		$queryStr .=     'et_update_dt = ? ';
		$queryStr .=   'WHERE et_serial = ?';
		$this->execStatement($queryStr, array($userId, $now, $serial));
		
		// 固定フィールドを作成
		$params = array();
		$queryStr  = 'INSERT INTO evententry ';
		$queryStr .=   '(';
		$queryStr .=   'et_id, ';				$params[] = $row['et_id'];
		$queryStr .=   'et_event_id, ';			$params[] = $row['et_event_id'];
		$queryStr .=   'et_type, ';				$params[] = $row['et_type'];
		$queryStr .=   'et_history_index, ';	$params[] = $historyIndex;
		$queryStr .=   'et_create_user_id, ';	$params[] = $userId;
		$queryStr .=   'et_create_dt';			$params[] = $now;

		// その他のフィールド値を追加
		$otherValueStr = '';
		if (!empty($otherParams)){
			$keys = array_keys($otherParams);// キーを取得
			for ($i = 0; $i < count($keys); $i++){
				$fieldName = $keys[$i];
				$fieldValue = $otherParams[$fieldName];
				if (!isset($fieldValue)) continue;
				$params[] = $fieldValue;
				$queryStr .= ', ' . $fieldName;
				$otherValueStr .= ', ?';
			}
		}
		$queryStr .= ') VALUES ';
		$queryStr .=   '(?, ?, ?, ?, ?, ?, ?';
		$queryStr .=   $otherValueStr . ') ';
		$this->execStatement($queryStr, $params);
		
		// 新規のシリアル番号取得
		$newSerial = 0;
		$queryStr = 'SELECT MAX(et_serial) AS ns FROM evententry ';
		$ret = $this->selectRecord($queryStr, array(), $row);
		if ($ret) $newSerial = $row['ns'];
			
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * イベント項目をシリアル番号で取得
	 *
	 * @param string    $langId		言語ID
	 * @param string	$serial		シリアル番号
	 * @param array     $row		レコード
	 * @return bool					取得 = true, 取得なし= false
	 */
	function getEventEntryBySerial($langId, $serial, &$row)
	{
		$params = array();
		$queryStr  = 'SELECT * FROM evententry ';
		$queryStr .=   'LEFT JOIN event_entry ON et_event_id = ee_id AND ee_deleted = false ';
		$queryStr .=     'AND ee_language_id = ? '; $params[] = $langId;
		$queryStr .=   'WHERE et_serial = ? '; $params[] = intval($serial);
		$ret = $this->selectRecord($queryStr, $params, $row);
		return $ret;
	}
	/**
	 * イベント項目を共通コンテンツIDで取得
	 *
	 * @param string  $langId		言語ID
	 * @param string  $eventId		イベントID
	 * @param string  $entryType	受付タイプ
	 * @param array     $row				レコード
	 * @return bool					true = 成功、false = 失敗
	 */
	function getEventEntryByContentsId($langId, $eventId, $entryType, &$row)
	{
		$params = array();
		$queryStr  = 'SELECT * FROM evententry ';
		$queryStr .=   'LEFT JOIN event_entry ON et_event_id = ee_id AND ee_deleted = false ';
		$queryStr .=     'AND ee_language_id = ? '; $params[] = $langId;
		$queryStr .=   'WHERE et_deleted = false ';	// 削除されていない
		$queryStr .=     'AND et_event_id = ? '; $params[] = $eventId;
		$queryStr .=     'AND et_type = ? '; $params[] = $entryType;
		$ret = $this->selectRecord($queryStr, $params, $row);
		return $ret;
	}
	/**
	 * イベント項目をイベントIDで取得
	 *
	 * @param string  $langId		言語ID
	 * @param string  $eventId		イベントID
	 * @param string  $entryType	受付タイプ
	 * @param array   $row			レコード
	 * @return bool					true = 成功、false = 失敗
	 */
	function getEventEntryByEventId($langId, $eventId, $entryType, &$row)
	{
		$params = array();
		$queryStr  = 'SELECT * FROM evententry ';
		$queryStr .=   'LEFT JOIN event_entry ON et_event_id = ee_id AND ee_deleted = false ';
		$queryStr .=     'AND ee_language_id = ? '; $params[] = $langId;
		$queryStr .=   'WHERE et_deleted = false ';	// 削除されていない
		$queryStr .=     'AND et_event_id = ? '; $params[] = $eventId;
		$queryStr .=     'AND et_type = ? '; $params[] = $entryType;
		$ret = $this->selectRecord($queryStr, $params, $row);
		return $ret;
	}
	/**
	 * イベント項目の削除
	 *
	 * @param array $serial			シリアルNo
	 * @return						true=成功、false=失敗
	 */
	function delEventEntry($serial)
	{
		$now = date("Y/m/d H:i:s");	// 現在日時
		$user = $this->gEnv->getCurrentUserId();	// 現在のユーザ
		
		if (!is_array($serial) || count($serial) <= 0) return true;
		
		// トランザクション開始
		$this->startTransaction();
		
		// 指定のシリアルNoのレコードが削除状態でないかチェック
		for ($i = 0; $i < count($serial); $i++){
			$queryStr  = 'SELECT * FROM evententry ';
			$queryStr .=   'WHERE et_deleted = false ';		// 未削除
			$queryStr .=     'AND et_serial = ? ';
			$ret = $this->isRecordExists($queryStr, array($serial[$i]));
			// 存在しない場合は、既に削除されたとして終了
			if (!$ret){
				$this->endTransaction();
				return false;
			}
		}
		
		// レコードを削除
		$queryStr  = 'UPDATE evententry ';
		$queryStr .=   'SET et_deleted = true, ';	// 削除
		$queryStr .=     'et_update_user_id = ?, ';
		$queryStr .=     'et_update_dt = ? ';
		$queryStr .=   'WHERE et_serial in (' . implode($serial, ',') . ') ';
		$this->execStatement($queryStr, array($user, $now));
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * イベント予約の新規追加
	 *
	 * @param string  $id			イベント予約ID
	 * @param int     $entryUserId	登録ユーザID
	 * @param string  $codeFormat	受付コードフォーマット(sprintfタイプの文字列)
	 * @param int     $newSerial	新規シリアル番号
	 * @return bool					true = 成功、false = 失敗
	 */
	function addEventEntryRequest($id, $entryUserId, $codeFormat, &$newSerial)
	{
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $this->gEnv->getCurrentUserId();	// 現在のユーザ
		$status = 1;			// 参加
		
		// トランザクション開始
		$this->startTransaction();
		
		// 既に登録済みの場合は終了
		$queryStr  = 'SELECT * FROM evententry_request ';
		$queryStr .=   'WHERE er_deleted = false ';			// 削除されていない
		$queryStr .=     'AND er_evententry_id = ? ';
		$queryStr .=     'AND er_user_id = ? ';
		$ret = $this->selectRecord($queryStr, array($id, $entryUserId), $row);
		if ($ret){		// 既に登録レコードがあるとき
			$this->endTransaction();
			return false;
		}
		
		// エントリーIDを決定する
		$queryStr = 'SELECT MAX(er_index) AS mid FROM evententry_request ';
		$queryStr .=  'WHERE er_evententry_id = ? ';
		$ret = $this->selectRecord($queryStr, array($id), $row);
		if ($ret){
			$index = $row['mid'] + 1;
		} else {
			$index = 1;
		}
		// 予約コード
		$code = sprintf($codeFormat, $index);
		
		// データを追加
		$queryStr  = 'INSERT INTO evententry_request ';
		$queryStr .=   '(';
		$queryStr .=   'er_evententry_id, ';
		$queryStr .=   'er_index, ';
		$queryStr .=   'er_code, ';
		$queryStr .=   'er_user_id, ';
		$queryStr .=   'er_status, ';
		
		$queryStr .=   'er_create_user_id, ';
		$queryStr .=   'er_create_dt';
		$queryStr .=   ') ';
		$queryStr .= 'VALUES ';
		$queryStr .=   '(?, ?, ?, ?, ?, ?, ?)';
		$this->execStatement($queryStr, array($id, $index, $code, $entryUserId, $status, $userId, $now));
		
		// 新規のシリアル番号取得
		$newSerial = 0;
		$queryStr = 'SELECT MAX(er_serial) AS ns FROM evententry_request ';
		$ret = $this->selectRecord($queryStr, array(), $row);
		if ($ret) $newSerial = $row['ns'];
			
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * ユーザの予約状況の確認
	 *
	 * @param string  $id			イベント予約ID
	 * @param int     $entryUserId	登録ユーザID
	 * @return bool					true=存在する、false=存在しない
	 */
	function isExistsEntryUser($id, $userId)
	{
		$queryStr  = 'SELECT * FROM evententry_request ';
		$queryStr .=   'WHERE er_deleted = false ';			// 削除されていない
		$queryStr .=     'AND er_evententry_id = ? ';
		$queryStr .=     'AND er_user_id = ? ';
		return $this->isRecordExists($queryStr, array($id, $userId));
	}
	/**
	 * ユーザの予約数を取得
	 *
	 * @param string  $id			イベント予約ID
	 * @return bool					true=存在する、false=存在しない
	 */
	function getEntryUserCount($id)
	{
		$queryStr  = 'SELECT * FROM evententry_request ';
		$queryStr .=   'WHERE er_deleted = false ';			// 削除されていない
		$queryStr .=     'AND er_status = 1 ';				// 「参加」のみ
		$queryStr .=     'AND er_evententry_id = ? ';
		return $this->selectRecordCount($queryStr, array($id));
	}
}
?>
