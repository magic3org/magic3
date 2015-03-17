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

class event_mainDb extends BaseDb
{
	/**
	 * イベント定義値を取得をすべて取得
	 *
	 * @param array  $rows			レコード
	 * @return bool					1行以上取得 = true, 取得なし= false
	 */
	function getAllConfig(&$rows)
	{
		$queryStr  = 'SELECT * FROM event_config ';
		$queryStr .=   'ORDER BY eg_index';
		$retValue = $this->selectRecords($queryStr, array(), $rows);
		return $retValue;
	}
	/**
	 * イベント定義値を取得
	 *
	 * @param string $key		キーとなる項目値
	 * @return string $value	値
	 */
	function getConfig($key)
	{
		$retValue = '';
		$queryStr = 'SELECT eg_value FROM event_config ';
		$queryStr .=  'WHERE eg_id  = ?';
		$ret = $this->selectRecord($queryStr, array($key), $row);
		if ($ret) $retValue = $row['eg_value'];
		return $retValue;
	}
	/**
	 * イベント定義値を更新
	 *
	 * @param string $key		キーとなる項目値
	 * @param string $value		値
	 * @return					true = 正常、false=異常
	 */
	function updateConfig($key, $value)
	{
		// データの確認
		$queryStr = 'SELECT eg_value FROM event_config ';
		$queryStr .=  'WHERE eg_id  = ?';
		$ret = $this->isRecordExists($queryStr, array($key));
		if ($ret){
			$queryStr = "UPDATE event_config SET eg_value = ? WHERE eg_id = ?";
			return $this->execStatement($queryStr, array($value, $key));
		} else {
			$queryStr = "INSERT INTO event_config (eg_id, eg_value) VALUES (?, ?)";
			return $this->execStatement($queryStr, array($key, $value));
		}
	}
	/**
	 * エントリー項目一覧を取得(管理用)
	 *
	 * @param int		$limit				取得する項目数
	 * @param int		$page				取得するページ(1～)
	 * @param timestamp	$startDt			期間(開始日)
	 * @param timestamp	$endDt				期間(終了日)
	 * @param array		$category			カテゴリーID(キー=カテゴリー種別、値=カテゴリー値)
	 * @param string	$keyword			検索キーワード
	 * @param string	$langId				言語
	 * @param function	$callback			コールバック関数
	 * @return 			なし
	 */
	function searchEntryItems($limit, $page, $startDt, $endDt, $category, $keyword, $langId, $callback)
	{
		$offset = $limit * ($page -1);
		if ($offset < 0) $offset = 0;
		
		$params = array();
		if (count($category) == 0){		// カテゴリー指定なしのとき
			$queryStr = 'SELECT * FROM event_entry ';
			$queryStr .=  'WHERE ee_language_id = ? '; $params[] = $langId;
			$queryStr .=    'AND ee_deleted = false ';		// 削除されていない
		} else {
			$queryStr = 'SELECT distinct(ee_serial) FROM event_entry RIGHT JOIN event_entry_with_category ON ee_serial = ew_entry_serial ';
			$queryStr .=  'WHERE ee_language_id = ? '; $params[] = $langId;
			$queryStr .=    'AND ee_deleted = false ';		// 削除されていない
			
			// 記事カテゴリー
			$queryStr .=    'AND ew_category_id in (' . implode(",", $category) . ') ';
		}
		// 名前、予定、結果、概要、管理者用備考、場所、連絡先を検索
		if (!empty($keyword)){
			// 「'"\」文字をエスケープ
			$keyword = addslashes($keyword);
			
			$queryStr .=    'AND (ee_name LIKE \'%' . $keyword . '%\' ';
			$queryStr .=    'OR ee_html LIKE \'%' . $keyword . '%\' ';
			$queryStr .=    'OR ee_html_ext LIKE \'%' . $keyword . '%\' ';
			$queryStr .=    'OR ee_summary LIKE \'%' . $keyword . '%\' ';
			$queryStr .=    'OR ee_admin_note LIKE \'%' . $keyword . '%\' ';
			$queryStr .=    'OR ee_place LIKE \'%' . $keyword . '%\' ';
			$queryStr .=    'OR ee_contact LIKE \'%' . $keyword . '%\') ';
		}
		
		// 日付範囲
		if (!empty($startDt)){
			$queryStr .=    'AND ? <= ee_start_dt ';
			$params[] = $startDt;
		}
		if (!empty($endDt)){
			$queryStr .=    'AND ee_start_dt < ? ';
			$params[] = $endDt;
		}
		
		if (count($category) == 0){
			$queryStr .=  'ORDER BY ee_start_dt desc, ee_id limit ' . $limit . ' offset ' . $offset;
			$this->selectLoop($queryStr, $params, $callback, null);
		} else {
			// シリアル番号を取得
			$serialArray = array();
			$ret = $this->selectRecords($queryStr, $params, $serialRows);
			if ($ret){
				for ($i = 0; $i < count($serialRows); $i++){
					$serialArray[] = $serialRows[$i]['ee_serial'];
				}
			}
			$serialStr = implode(',', $serialArray);
			if (empty($serialStr)) $serialStr = '0';	// 0レコードのときはダミー値を設定
		
			$queryStr = 'SELECT * FROM event_entry ';
			$queryStr .=  'WHERE ee_serial in (' . $serialStr . ') ';
			$queryStr .=  'ORDER BY ee_start_dt desc, ee_id limit ' . $limit . ' offset ' . $offset;
			$this->selectLoop($queryStr, array(), $callback, null);
		}
	}
	/**
	 * エントリー項目数を取得(管理用)
	 *
	 * @param timestamp	$startDt			期間(開始日)
	 * @param timestamp	$endDt				期間(終了日)
	 * @param array		$category			カテゴリーID(キー=カテゴリー種別、値=カテゴリー値)
	 * @param string	$keyword			検索キーワード
	 * @param string	$langId				言語
	 * @return int							項目数
	 */
	function getEntryItemCount($startDt, $endDt, $category, $keyword, $langId)
	{
		$params = array();
		if (count($category) == 0){		// カテゴリー指定なしのとき
			$queryStr = 'SELECT * FROM event_entry ';
			$queryStr .=  'WHERE ee_language_id = ? '; $params[] = $langId;
			$queryStr .=    'AND ee_deleted = false ';		// 削除されていない
		} else {
			$queryStr = 'SELECT distinct(ee_serial) FROM event_entry RIGHT JOIN event_entry_with_category ON ee_serial = ew_entry_serial ';
			$queryStr .=  'WHERE ee_language_id = ? '; $params[] = $langId;
			$queryStr .=    'AND ee_deleted = false ';		// 削除されていない
			
			// 記事カテゴリー
			$queryStr .=    'AND ew_category_id in (' . implode(",", $category) . ') ';
		}
		// 名前、予定、結果、概要、管理者用備考、場所、連絡先を検索
		if (!empty($keyword)){
			// 「'"\」文字をエスケープ
			$keyword = addslashes($keyword);
			
			$queryStr .=    'AND (ee_name LIKE \'%' . $keyword . '%\' ';
			$queryStr .=    'OR ee_html LIKE \'%' . $keyword . '%\' ';
			$queryStr .=    'OR ee_html_ext LIKE \'%' . $keyword . '%\' ';
			$queryStr .=    'OR ee_summary LIKE \'%' . $keyword . '%\' ';
			$queryStr .=    'OR ee_admin_note LIKE \'%' . $keyword . '%\' ';
			$queryStr .=    'OR ee_place LIKE \'%' . $keyword . '%\' ';
			$queryStr .=    'OR ee_contact LIKE \'%' . $keyword . '%\') ';
		}
		
		// 日付範囲
		if (!empty($startDt)){
			$queryStr .=    'AND ? <= ee_start_dt ';
			$params[] = $startDt;
		}
		if (!empty($endDt)){
			$queryStr .=    'AND ee_start_dt < ? ';
			$params[] = $endDt;
		}
		
		if (count($category) == 0){
			return $this->selectRecordCount($queryStr, $params);
		} else {
			// シリアル番号を取得
			$serialArray = array();
			$ret = $this->selectRecords($queryStr, $params, $serialRows);
			if ($ret){
				for ($i = 0; $i < count($serialRows); $i++){
					$serialArray[] = $serialRows[$i]['ee_serial'];
				}
			}
			$serialStr = implode(',', $serialArray);
			if (empty($serialStr)) $serialStr = '0';	// 0レコードのときはダミー値を設定
		
			$queryStr = 'SELECT * FROM event_entry ';
			$queryStr .=  'WHERE ee_serial in (' . $serialStr . ') ';
			return $this->selectRecordCount($queryStr, array());
		}
	}
	/**
	 * 次のエントリーIDを取得
	 *
	 * @return int							エントリーID
	 */
	function getNextEntryId()
	{
		// エントリーIDを決定する
		$queryStr = 'SELECT MAX(ee_id) AS mid FROM event_entry ';
		$ret = $this->selectRecord($queryStr, array(), $row);
		if ($ret){
			$entryId = $row['mid'] + 1;
		} else {
			$entryId = 1;
		}
		return $entryId;
	}
	/**
	 * エントリー項目の新規追加
	 *
	 * @param string  $id			エントリーID
	 * @param string  $langId		言語ID
	 * @param string  $name			イベント名
	 * @param string  $html			HTML
	 * @param string  $html2		HTML(続き)
	 * @param int     $status		エントリー状態(0=未設定、1=編集中、2=公開、3=非公開)
	 * @param array   $category		カテゴリーID
	 * @param array   $otherParams	その他のフィールド値
	 * @param int     $newSerial	新規シリアル番号
	 * @return bool					true = 成功、false = 失敗
	 */
	function addEntryItem($id, $langId, $name, $html, $html2, $status, $category, $otherParams, &$newSerial)
	{
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $this->gEnv->getCurrentUserId();	// 現在のユーザ
			
		// トランザクション開始
		$this->startTransaction();
		
		if (intval($id) <= 0){		// エントリーIDが0以下のときは、エントリーIDを新規取得
			// エントリーIDを決定する
			$queryStr = 'SELECT MAX(ee_id) AS mid FROM event_entry ';
			$ret = $this->selectRecord($queryStr, array(), $row);
			if ($ret){
				$entryId = $row['mid'] + 1;
			} else {
				$entryId = 1;
			}
			
			// 新規記事追加のときは記事IDが変更されていないかチェック
			if (intval($id) * (-1) != $entryId){
				$this->endTransaction();
				return false;
			}
		} else {
			$entryId = $id;
		}
		
		// 前レコードの削除状態チェック
		$historyIndex = 0;
		$queryStr = 'SELECT * FROM event_entry ';
		$queryStr .=  'WHERE ee_id = ? ';
		$queryStr .=    'AND ee_language_id = ? ';
		$queryStr .=  'ORDER BY ee_history_index DESC ';
		$ret = $this->selectRecord($queryStr, array($entryId, $langId), $row);
		if ($ret){
			if (!$row['ee_deleted']){		// レコード存在していれば終了
				$this->endTransaction();
				return false;
			}
			$historyIndex = $row['ee_history_index'] + 1;
		}
		
		// データを追加
		$params = array($entryId, $langId, $historyIndex, $name, $html, $html2, $status, $userId, $now);
												
		$queryStr  = 'INSERT INTO event_entry ';
		$queryStr .=   '(ee_id, ';
		$queryStr .=   'ee_language_id, ';
		$queryStr .=   'ee_history_index, ';
		$queryStr .=   'ee_name, ';
		$queryStr .=   'ee_html, ';
		$queryStr .=   'ee_html_ext, ';
		$queryStr .=   'ee_status, ';
		$queryStr .=   'ee_create_user_id, ';
		$queryStr .=   'ee_create_dt ';
		
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
		$queryStr .=  ') VALUES ';
		$queryStr .=   '(?, ?, ?, ?, ?, ?, ?, ?, ?' . $otherValueStr . ')';
		$this->execStatement($queryStr, $params);
		
		// 新規のシリアル番号取得
		$newSerial = 0;
		$queryStr = 'SELECT MAX(ee_serial) AS ns FROM event_entry ';
		$ret = $this->selectRecord($queryStr, array(), $row);
		if ($ret) $newSerial = $row['ns'];
		
		// 記事カテゴリーの更新
		for ($i = 0; $i < count($category); $i++){
			$ret = $this->updateEntryCategory($newSerial, $i, $category[$i]);
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
	 * エントリー項目の更新
	 *
	 * @param int     $serial		シリアル番号
	 * @param string  $name			イベント名
	 * @param string  $html			HTML
	 * @param string  $html2		HTML(続き)
	 * @param int     $status		エントリー状態(0=未設定、1=編集中、2=公開、3=非公開)
	 * @param array   $category		カテゴリーID
	 * @param array   $otherParams	その他のフィールド値
	 * @param int     $newSerial	新規シリアル番号
	 * @return bool					true = 成功、false = 失敗
	 */
	function updateEntryItem($serial, $name, $html, $html2, $status, $category, $otherParams, &$newSerial)
	{
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $this->gEnv->getCurrentUserId();	// 現在のユーザ
						
		// トランザクション開始
		$this->startTransaction();
		
		// 指定のシリアルNoのレコードが削除状態でないかチェック
		$historyIndex = 0;		// 履歴番号
		$queryStr  = 'SELECT * FROM event_entry ';
		$queryStr .=   'WHERE ee_serial = ? ';
		$ret = $this->selectRecord($queryStr, array($serial), $row);
		if ($ret){		// 既に登録レコードがあるとき
			if ($row['ee_deleted']){		// レコードが削除されていれば終了
				$this->endTransaction();
				return false;
			}
			$historyIndex = $row['ee_history_index'] + 1;
		} else {		// 存在しない場合は終了
			$this->endTransaction();
			return false;
		}
		// 古いレコードを削除
		$queryStr  = 'UPDATE event_entry ';
		$queryStr .=   'SET ee_deleted = true, ';	// 削除
		$queryStr .=     'ee_update_user_id = ?, ';
		$queryStr .=     'ee_update_dt = ? ';
		$queryStr .=   'WHERE ee_serial = ?';
		$this->execStatement($queryStr, array($userId, $now, $serial));
		
		// データを追加
		if (empty($regUserId)){
			$rUserId = $row['ee_regist_user_id'];
		} else {
			$rUserId = $regUserId;
		}
		$entryId = $row['ee_id'];
		$langId = $row['ee_language_id'];
		
/*		// 新規レコード追加		
		$queryStr  = 'INSERT INTO event_entry ';
		$queryStr .=   '(ee_id, ';
		$queryStr .=   'ee_language_id, ';
		$queryStr .=   'ee_history_index, ';
		$queryStr .=   'ee_name, ';
		$queryStr .=   'ee_html, ';
		$queryStr .=   'ee_html_ext, ';
		$queryStr .=   'ee_summary, ';
		$queryStr .=   'ee_place, ';
		$queryStr .=   'ee_contact, ';
		$queryStr .=   'ee_url, ';
		$queryStr .=   'ee_admin_note, ';
		$queryStr .=   'ee_status, ';
		$queryStr .=   'ee_is_all_day, ';
		$queryStr .=   'ee_show_comment, ';
		$queryStr .=   'ee_receive_comment, ';
		$queryStr .=   'ee_user_limited, ';
		$queryStr .=   'ee_start_dt, ';
		$queryStr .=   'ee_end_dt, ';
		$queryStr .=   'ee_create_user_id, ';
		$queryStr .=   'ee_create_dt) ';
		$queryStr .= 'VALUES ';
		$queryStr .=   '(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
		$this->execStatement($queryStr, array($entryId, $langId, $historyIndex, $name, $html, $html2, $summary, $place, $contact, $url, $note, $status, 
												intval($isAllDay), intval($showComment), intval($receiveComment), intval($userLimited), $startDt, $endDt, $userId, $now));*/
		// データを追加
		$params = array($entryId, $langId, $historyIndex, $name, $html, $html2, $status, $userId, $now);
												
		$queryStr  = 'INSERT INTO event_entry ';
		$queryStr .=   '(ee_id, ';
		$queryStr .=   'ee_language_id, ';
		$queryStr .=   'ee_history_index, ';
		$queryStr .=   'ee_name, ';
		$queryStr .=   'ee_html, ';
		$queryStr .=   'ee_html_ext, ';
		$queryStr .=   'ee_status, ';
		$queryStr .=   'ee_create_user_id, ';
		$queryStr .=   'ee_create_dt ';
		
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
		$queryStr .=  ') VALUES ';
		$queryStr .=   '(?, ?, ?, ?, ?, ?, ?, ?, ?' . $otherValueStr . ')';
		$this->execStatement($queryStr, $params);

		// 新規のシリアル番号取得
		$newSerial = 0;
		$queryStr = 'SELECT MAX(ee_serial) AS ns FROM event_entry ';
		$ret = $this->selectRecord($queryStr, array(), $row);
		if ($ret) $newSerial = $row['ns'];
		
		// 記事カテゴリーの更新
		for ($i = 0; $i < count($category); $i++){
			$ret = $this->updateEntryCategory($newSerial, $i, $category[$i]);
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
	 * サムネールファイル名の更新
	 *
	 * @param string $id			エントリーID
	 * @param string $langId		言語ID
	 * @param string $thumbFilename	サムネールファイル名
	 * @return bool					true = 成功、false = 失敗
	 */
	function updateThumbFilename($id, $langId, $thumbFilename)
	{
		$serial = $this->getEntrySerialNoByContentId($id, $langId);
		if (empty($serial)) return false;
		
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $this->gEnv->getCurrentUserId();	// 現在のユーザ
						
		// トランザクション開始
		$this->startTransaction();
		
		// 指定のシリアルNoのレコードが削除状態でないかチェック
		$queryStr  = 'SELECT * FROM event_entry ';
		$queryStr .=   'WHERE ee_serial = ? ';
		$ret = $this->selectRecord($queryStr, array(intval($serial)), $row);
		if ($ret){		// 既に登録レコードがあるとき
			if ($row['ee_deleted']){		// レコードが削除されていれば終了
				$this->endTransaction();
				return false;
			}
		} else {		// 存在しない場合は終了
			$this->endTransaction();
			return false;
		}
		// 日付を更新
		$queryStr  = 'UPDATE event_entry ';
		$queryStr .=   'SET ee_thumb_filename = ?, ';	// サムネールファイル名
		$queryStr .=     'ee_update_user_id = ?, ';
		$queryStr .=     'ee_update_dt = ? ';
		$queryStr .=   'WHERE ee_serial = ?';
		$this->execStatement($queryStr, array($thumbFilename, $userId, $now, intval($serial)));
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * 記事カテゴリーの更新
	 *
	 * @param int        $serial		記事シリアル番号
	 * @param int        $index			インデックス番号
	 * @param int        $categoryId	カテゴリーID
	 * @return bool		 true = 成功、false = 失敗
	 */
	function updateEntryCategory($serial, $index, $categoryId)
	{
		// 新規レコード追加
		$queryStr = 'INSERT INTO event_entry_with_category ';
		$queryStr .=  '(';
		$queryStr .=  'ew_entry_serial, ';
		$queryStr .=  'ew_index, ';
		$queryStr .=  'ew_category_id) ';
		$queryStr .=  'VALUES ';
		$queryStr .=  '(?, ?, ?)';
		$ret =$this->execStatement($queryStr, array($serial, $index, $categoryId));
		return $ret;
	}
	/**
	 * エントリー項目をシリアル番号で取得
	 *
	 * @param string	$serial				シリアル番号
	 * @param array     $row				レコード
	 * @param array     $categoryRow		記事カテゴリー
	 * @return bool							取得 = true, 取得なし= false
	 */
	function getEntryBySerial($serial, &$row, &$categoryRow)
	{
		$queryStr  = 'SELECT * FROM event_entry LEFT JOIN _login_user ON ee_create_user_id = lu_id AND lu_deleted = false ';
		$queryStr .=   'WHERE ee_serial = ? ';
		$ret = $this->selectRecord($queryStr, array($serial), $row);
		
		if ($ret){
			// イベントカテゴリー
			$queryStr  = 'SELECT * FROM event_entry_with_category LEFT JOIN event_category ON ew_category_id = ec_id AND ec_deleted = false ';
			$queryStr .=   'WHERE ew_entry_serial = ? ';
		//	$queryStr .=  'ORDER BY ew_index ';
			$queryStr .=  'ORDER BY ec_sort_order, ec_id ';		// カテゴリー並び順
			$this->selectRecords($queryStr, array(intval($serial)), $categoryRow);
		}
		return $ret;
	}
	/**
	 * エントリー項目のシリアル番号をエントリーIDで取得
	 *
	 * @param string	$id					エントリーID
	 * @param string	$langId				言語ID
	 * @return int							シリアル番号、取得できないときは0を返す
	 */
	function getEntrySerialNoByContentId($id, $langId)
	{
		$serial = 0;
		$queryStr  = 'SELECT * FROM event_entry ';
		$queryStr .=   'WHERE ee_deleted = false ';	// 削除されていない
		$queryStr .=   'AND ee_id = ? ';
		$queryStr .=   'AND ee_language_id = ? ';
		$ret = $this->selectRecord($queryStr, array($id, $langId), $row);
		if ($ret) $serial = $row['ee_serial'];
		return $serial;
	}
	/**
	 * エントリー項目の削除
	 *
	 * @param array   $serial		シリアルNo
	 * @return						true=成功、false=失敗
	 */
	function delEntryItem($serial)
	{
		// 引数のエラーチェック
		if (!is_array($serial)) return false;
		if (count($serial) <= 0) return true;
		
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $this->gEnv->getCurrentUserId();	// 現在のユーザ
		
		// トランザクション開始
		$this->startTransaction();
		
		for ($i = 0; $i < count($serial); $i++){
			$queryStr  = 'SELECT * FROM event_entry ';
			$queryStr .=   'WHERE ee_deleted = false ';		// 未削除
			$queryStr .=     'AND ee_serial = ? ';
			$ret = $this->selectRecord($queryStr, array($serial[$i]), $row);
			if ($ret){		// 既に登録レコードがあるとき			
				// レコードを削除
				$queryStr  = 'UPDATE event_entry ';
				$queryStr .=   'SET ee_deleted = true, ';	// 削除
				$queryStr .=     'ee_update_user_id = ?, ';
				$queryStr .=     'ee_update_dt = ? ';
				$queryStr .=   'WHERE ee_serial = ?';
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
	 * エントリーIDでエントリー項目を削除
	 *
	 * @param array   $serial		シリアルNo
	 * @return						true=成功、false=失敗
	 */
	function delEntryItemById($serial)
	{
		// 引数のエラーチェック
		if (!is_array($serial)) return false;
		if (count($serial) <= 0) return true;
		
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $this->gEnv->getCurrentUserId();	// 現在のユーザ
		
		// トランザクション開始
		$this->startTransaction();
		
		// コンテンツIDを取得
		$queryStr  = 'SELECT * FROM event_entry ';
		$queryStr .=   'WHERE ee_deleted = false ';		// 未削除
		$queryStr .=     'AND ee_serial = ? ';
		$ret = $this->selectRecord($queryStr, array($serial), $row);
		if ($ret){		// 既に登録レコードがあるとき
			if ($row['ee_deleted']){		// レコードが削除されていれば終了
				$this->endTransaction();
				return false;
			}
		} else {		// 存在しない場合は終了
			$this->endTransaction();
			return false;
		}
		$entryId = $row['ee_id'];
		
		// レコードを削除
		$queryStr  = 'UPDATE event_entry ';
		$queryStr .=   'SET ee_deleted = true, ';	// 削除
		$queryStr .=     'ee_update_user_id = ?, ';
		$queryStr .=     'ee_update_dt = now() ';
		$queryStr .=   'WHERE ee_id = ?';
		$this->execStatement($queryStr, array($userId, $entryId));
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * エントリー項目を取得
	 *
	 * @param int,array	$id					エントリーID
	 * @param string	$langId				言語
	 * @param array     $row				レコード
	 * @return bool							取得 = true, 取得なし= false
	 */
	function getEntryItem($id, $langId, &$row)
	{
		if (is_array($id)){
			$params = array();
			$contentId = implode(',', $id);
		
			// CASE文作成
			$caseStr = 'CASE ee_id ';
			for ($i = 0; $i < count($id); $i++){
				$caseStr .= 'WHEN ' . $id[$i] . ' THEN ' . $i . ' ';
			}
			$caseStr .= 'END AS no';

			$queryStr = 'SELECT *, ' . $caseStr . ' FROM event_entry ';
			$queryStr .=  'WHERE ee_deleted = false ';		// 削除されていない
			$queryStr .=    'AND ee_id in (' . $contentId . ') ';
			$queryStr .=    'AND ee_language_id = ? '; $params[] = $langId;
			$queryStr .=  'ORDER BY no';
			$ret = $this->selectRecords($queryStr, $params, $row);
		} else {
			$queryStr  = 'SELECT * FROM event_entry ';
			$queryStr .=   'WHERE ee_deleted = false ';	// 削除されていない
			$queryStr .=   'AND ee_id = ? ';
			$queryStr .=   'AND ee_language_id = ? ';
			$ret = $this->selectRecord($queryStr, array($id, $langId), $row);
		}
		return $ret;
	}
	/**
	 * 前後のエントリー項目を取得
	 *
	 * @param int       $entryId			記事ID
	 * @param timestamp $startDate			開始日時
	 * @param array     $prevRow			前のレコード
	 * @param array     $nextRow			次のレコード
	 * @return bool							取得 = true, 取得なし= false
	 */
	function getPrevNextEntryByDate($entryId, $startDate, &$prevRow, &$nextRow)
	{
		if ($startDate == $this->gEnv->getInitValueOfTimestamp()){
			return false;
		} else {
			$retStatus = false;
			$params = array();
			$queryStr  = 'SELECT * FROM event_entry ';
			$queryStr .=   'WHERE ee_deleted = false ';	// 削除されていない
			$queryStr .=     'AND (ee_start_dt < ? '; $params[] = $startDate;
			$queryStr .=     'OR (ee_start_dt = ? '; $params[] = $startDate;
			$queryStr .=     'AND ee_id < ?)) '; $params[] = $entryId;
			$queryStr .=   'ORDER BY ee_start_dt, ee_id DESC LIMIT 1';// 開始日時順
			$ret = $this->selectRecord($queryStr, $params, $row);
			if ($ret){
				$prevRow = $row;
				$retStatus = true;
			}
			
			$params = array();
			$queryStr  = 'SELECT * FROM event_entry ';
			$queryStr .=   'WHERE ee_deleted = false ';	// 削除されていない
			$queryStr .=     'AND (? < ee_start_dt '; $params[] = $startDate;
			$queryStr .=     'OR (ee_start_dt = ? '; $params[] = $startDate;
			$queryStr .=     'AND ee_id > ?)) '; $params[] = $entryId;
			$queryStr .=   'ORDER BY ee_start_dt, ee_id LIMIT 1';// 開始日時順
			$ret = $this->selectRecord($queryStr, $params, $row);
			if ($ret){
				$nextRow = $row;
				$retStatus = true;
			}
		}
		return $retStatus;
	}
	/**
	 * エントリー項目を取得(表示用)
	 *
	 * @param int		$limit				取得する項目数
	 * @param int		$page				取得するページ(1～)
	 * @param timestamp $now				現在日時(現在日時より未来の投稿日時の記事は取得しない)
	 * @param int		$entryId			エントリーID(0のときは期間で取得)
	 * @param timestamp	$startDt			期間(開始日)
	 * @param timestamp	$endDt				期間(終了日)
	 * @param array		$keywords			検索キーワード
	 * @param string	$langId				言語
	 * @param int		$order				取得順(0=昇順,1=降順)
	 * @param function	$callback			コールバック関数
	 * @param bool		$preview			プレビューモードかどうか
	 * @return 			なし
	 */
	function getEntryItems($limit, $page, $now, $entryId, $startDt, $endDt, $keywords, $langId, $order, $callback, $preview = false)
	{
		$offset = $limit * ($page -1);
		if ($offset < 0) $offset = 0;
		$params = array();
		
		$queryStr  = 'SELECT * FROM event_entry ';
		$queryStr .=   'WHERE ee_deleted = false ';		// 削除されていない
		$queryStr .=     'AND ee_language_id = ? ';	$params[] = $langId;
		if (!empty($entryId)){
			$queryStr .=     'AND ee_id = ? ';		$params[] = $entryId;
		}
		
		// タイトルと記事、ユーザ定義フィールドを検索
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
			}
		}
	
		// 検索条件
		if (empty($startDt) && empty($endDt)){
			$nowDate = date("Y/m/d", strtotime($now));
			$queryStr .=    'AND ? <= ee_start_dt ';
			$params[] = $nowDate;
		} else {
			if (!empty($startDt)){
				$queryStr .=    'AND ? <= ee_start_dt ';
				$params[] = $startDt;
			}
			if (!empty($endDt)){
				$queryStr .=    'AND ee_start_dt < ? ';
				$params[] = $endDt;
			}
		}
		
		if (!$preview){		// プレビューモードでないときは取得制限
			$queryStr .=     'AND ee_status = ? ';		$params[] = 2;	// 「公開」(2)データを表示
		}

		if (empty($entryId)){
			$ord = '';
			if (!empty($order)) $ord = 'DESC ';
			//$queryStr .=  'ORDER BY ee_start_dt ' . $ord . 'LIMIT ' . $limit . ' offset ' . $offset;// 投稿順
			$queryStr .=  'ORDER BY ee_start_dt ' . $ord . ', ee_id LIMIT ' . $limit . ' offset ' . $offset;// 投稿順
		}
		$this->selectLoop($queryStr, $params, $callback);
	}
	/**
	 * エントリー項目を取得(表示用)
	 *
	 * @param int		$limit				取得する項目数
	 * @param int		$page				取得するページ(1～)
	 * @param timestamp $now				現在日時(期間を指定しない場合は現在日より未来のイベントを取得)
	 * @param int		$entryId			エントリーID(0のときは期間で取得)
	 * @param timestamp	$startDt			期間(開始日)
	 * @param timestamp	$endDt				期間(終了日)
	 * @param string	$langId				言語
	 * @param int		$order				取得順(0=昇順,1=降順)
	 * @param function	$callback			コールバック関数
	 * @param bool		$preview			プレビューモードかどうか
	 * @return 			なし
	 */
/*	function getEntryItems($limit, $page, $now, $entryId, $startDt, $endDt, $langId, $order, $callback, $preview = false)
	{
		$offset = $limit * ($page -1);
		if ($offset < 0) $offset = 0;
		$params = array();
		
		// エントリーIDの指定がない場合は、期間で取得
		if (empty($entryId)){
			$queryStr  = 'SELECT * FROM event_entry ';
			$queryStr .=   'WHERE ee_deleted = false ';		// 削除されていない
			$queryStr .=     'AND ee_language_id = ? ';	$params[] = $langId;
		
			if (!$preview){		// プレビューモードでないときは取得制限
				$queryStr .=     'AND ee_status = ? ';		$params[] = 2;	// 「公開」(2)データを表示
				
				// 検索条件
				if (empty($startDt) && empty($endDt)){
					$nowDate = date("Y/m/d", strtotime($now));
					$queryStr .=    'AND ? <= ee_start_dt ';
					$params[] = $nowDate;
				} else {
					if (!empty($startDt)){
						$queryStr .=    'AND ? <= ee_start_dt ';
						$params[] = $startDt;
					}
					if (!empty($endDt)){
						$queryStr .=    'AND ee_start_dt < ? ';
						$params[] = $endDt;
					}
				}
			}
			
			$ord = '';
			if (!empty($order)) $ord = 'DESC ';
			$queryStr .=  'ORDER BY ee_start_dt ' . $ord . ', ee_id LIMIT ' . $limit . ' offset ' . $offset;// 投稿順
			$this->selectLoop($queryStr, $params, $callback, null);
		} else {
			$queryStr  = 'SELECT * FROM event_entry ';
			$queryStr .=   'WHERE ee_deleted = false ';		// 削除されていない
			$queryStr .=     'AND ee_id = ? ';		$params[] = $entryId;
			$queryStr .=     'AND ee_language_id = ? ';	$params[] = $langId;
			
			if (!$preview){		// プレビューモードでないときは取得制限
				$queryStr .=     'AND ee_status = ? ';	$params[] = 2;	// 「公開」(2)データを表示
			}
			$this->selectLoop($queryStr, $params, $callback, null);		// 「公開」(2)データを表示
		}
	}*/
	/**
	 * エントリー項目数を取得(表示用)
	 *
	 * @param timestamp $now				現在日時(現在日時より未来の投稿日時の記事は取得しない)
	 * @param timestamp	$startDt			期間(開始日)
	 * @param timestamp	$endDt				期間(終了日)
	 * @param array		$keywords			検索キーワード
	 * @param string	$langId				言語
	 * @param bool		$preview			プレビューモードかどうか
	 * @return int							項目数
	 */
	function getEntryItemsCount($now, $startDt, $endDt, $keywords, $langId, $preview = false)
	{
		$params = array();
		
		$queryStr  = 'SELECT * FROM event_entry ';
		$queryStr .=   'WHERE ee_deleted = false ';		// 削除されていない
		$queryStr .=     'AND ee_language_id = ? ';	$params[] = $langId;
		if (!empty($entryId)){
			$queryStr .=     'AND ee_id = ? ';		$params[] = $entryId;
		}
		
		// タイトルと記事、ユーザ定義フィールドを検索
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
			}
		}
	
		// 検索条件
		if (empty($startDt) && empty($endDt)){
			$nowDate = date("Y/m/d", strtotime($now));
			$queryStr .=    'AND ? <= ee_start_dt ';
			$params[] = $nowDate;
		} else {
			if (!empty($startDt)){
				$queryStr .=    'AND ? <= ee_start_dt ';
				$params[] = $startDt;
			}
			if (!empty($endDt)){
				$queryStr .=    'AND ee_start_dt < ? ';
				$params[] = $endDt;
			}
		}
		
		if (!$preview){		// プレビューモードでないときは取得制限
			$queryStr .=     'AND ee_status = ? ';		$params[] = 2;	// 「公開」(2)データを表示
		}
		return $this->selectRecordCount($queryStr, $params);
	}
	/**
	 * エントリー項目数を取得(表示用)
	 *
	 * @param timestamp $now				現在日時(期間を指定しない場合は現在日より未来のイベントを取得)
	 * @param timestamp	$startDt			期間(開始日)
	 * @param timestamp	$endDt				期間(終了日)
	 * @param string	$langId				言語
	 * @param bool		$preview			プレビューモードかどうか
	 * @return int							項目数
	 */
/*	function getEntryItemsCount($now, $startDt, $endDt, $langId, $preview = false)
	{
		$params = array();
		
		$queryStr = 'SELECT * FROM event_entry ';
		$queryStr .=  'WHERE ee_deleted = false ';		// 削除されていない
		$queryStr .=    'AND ee_language_id = ? ';	$params[] = $langId;
		
		if (!$preview){		// プレビューモードでないときは取得制限
			$queryStr .=    'AND ee_status = ? ';		$params[] = 2;	// 「公開」(2)データを表示
					
			// 検索条件
			if (empty($startDt) && empty($endDt)){
				$nowDate = date("Y/m/d", strtotime($now));
				$queryStr .=    'AND ? <= ee_start_dt ';
				$params[] = $nowDate;
			} else {
				if (!empty($startDt)){
					$queryStr .=    'AND ? <= ee_start_dt ';
					$params[] = $startDt;
				}
				if (!empty($endDt)){
					$queryStr .=    'AND ee_start_dt < ? ';
					$params[] = $endDt;
				}
			}
		}
		return $this->selectRecordCount($queryStr, $params);
	}*/
	/**
	 * エントリー項目をカテゴリー指定で取得(表示用)
	 *
	 * @param int		$limit				取得する項目数
	 * @param int		$page				取得するページ(1～)
	 * @param timestamp $now				現在日時(現在日時より未来の投稿日時の記事は取得しない)
	 * @param int		$categoryId			カテゴリーID
	 * @param string	$langId				言語
	 * @param int		$order				取得順(0=昇順,1=降順)
	 * @param function	$callback			コールバック関数
	 * @return 			なし
	 */
	function getEntryItemsByCategory($limit, $page, $now, $categoryId, $langId, $order, $callback)
	{
		$offset = $limit * ($page -1);
		if ($offset < 0) $offset = 0;
		$params = array();
		
		$queryStr  = 'SELECT distinct(ee_serial) FROM event_entry RIGHT JOIN event_entry_with_category ON ee_serial = ew_entry_serial ';
		$queryStr .=  'WHERE ee_language_id = ? '; $params[] = $langId;
		$queryStr .=    'AND ee_deleted = false ';		// 削除されていない
		$queryStr .=    'AND ee_status = ? '; $params[] = 2;	// 「公開」(2)データ
		$queryStr .=    'AND ew_category_id = ? ';	$params[] = $categoryId;// 記事カテゴリー
		
		// シリアル番号を取得
		$serialArray = array();
		$ret = $this->selectRecords($queryStr, $params, $serialRows);
		if ($ret){
			for ($i = 0; $i < count($serialRows); $i++){
				$serialArray[] = $serialRows[$i]['ee_serial'];
			}
		}
		$serialStr = implode(',', $serialArray);
		if (empty($serialStr)) $serialStr = '0';	// 0レコードのときはダミー値を設定
	
		$queryStr  = 'SELECT * FROM event_entry ';
		$queryStr .=   'WHERE ee_serial in (' . $serialStr . ') ';
		$ord = '';
		if (!empty($order)) $ord = 'DESC ';
		$queryStr .=  'ORDER BY ee_start_dt ' . $ord . ', ee_id LIMIT ' . $limit . ' offset ' . $offset;// 投稿順
		$this->selectLoop($queryStr, array(), $callback, null);
	}
	/**
	 * エントリー項目をカテゴリー指定で取得(表示用)
	 *
	 * @param int		$limit				取得する項目数
	 * @param int		$page				取得するページ(1～)
	 * @param timestamp $now				現在日時(現在日時より未来の投稿日時の記事は取得しない)
	 * @param int		$categoryId			カテゴリーID
	 * @param string	$langId				言語
	 * @param int		$order				取得順(0=昇順,1=降順)
	 * @param function	$callback			コールバック関数
	 * @return 			なし
	 */
/*	function getEntryItemsByCategory($limit, $page, $now, $categoryId, $langId, $order, $callback)
	{
		$offset = $limit * ($page -1);
		if ($offset < 0) $offset = 0;
		$initDt = $this->gEnv->getInitValueOfTimestamp();		// 日時初期化値
		$params = array();
		
		$queryStr = 'SELECT distinct(ee_serial) FROM event_entry RIGHT JOIN event_entry_with_category ON ee_serial = ew_entry_serial ';
		$queryStr .=  'WHERE ee_language_id = ? '; $params[] = $langId;
		$queryStr .=    'AND ee_deleted = false ';		// 削除されていない
		$queryStr .=    'AND ee_status = ? '; $params[] = 2;	// 「公開」(2)データ
		$queryStr .=    'AND ew_category_id = ? ';	$params[] = $categoryId;// 記事カテゴリー
		
		// シリアル番号を取得
		$serialArray = array();
		$ret = $this->selectRecords($queryStr, $params, $serialRows);
		if ($ret){
			for ($i = 0; $i < count($serialRows); $i++){
				$serialArray[] = $serialRows[$i]['ee_serial'];
			}
		}
		$serialStr = implode(',', $serialArray);
		if (empty($serialStr)) $serialStr = '0';	// 0レコードのときはダミー値を設定
	
		$queryStr = 'SELECT * FROM event_entry ';
//		$queryStr  = 'SELECT * FROM event_entry LEFT JOIN blog_id ON ee_blog_id = bl_id AND bl_deleted = false ';
		$queryStr .=   'WHERE ee_serial in (' . $serialStr . ') ';
		$ord = '';
		if (!empty($order)) $ord = 'DESC ';
		$queryStr .=  'ORDER BY ee_start_dt ' . $ord . ', ee_id LIMIT ' . $limit . ' offset ' . $offset;// 投稿順
		$this->selectLoop($queryStr, array(), $callback);
	}*/
	/**
	 * エントリー項目数をカテゴリー指定で取得(表示用)
	 *
	 * @param timestamp $now				現在日時(現在日時より未来の投稿日時の記事は取得しない)
	 * @param int		$categoryId			カテゴリーID
	 * @param string	$langId				言語
	 * @return int							エントリー項目数
	 */
	function getEntryItemsCountByCategory($now, $categoryId, $langId)
	{
		$params = array();
		
		$queryStr  = 'SELECT distinct(ee_serial) FROM event_entry RIGHT JOIN event_entry_with_category ON ee_serial = ew_entry_serial ';
		$queryStr .=  'WHERE ee_language_id = ? '; $params[] = $langId;
		$queryStr .=    'AND ee_deleted = false ';		// 削除されていない
		$queryStr .=    'AND ee_status = ? '; $params[] = 2;	// 「公開」(2)データ
		$queryStr .=    'AND ew_category_id = ? ';	$params[] = $categoryId;// 記事カテゴリー
		
		// シリアル番号を取得
		$serialArray = array();
		$ret = $this->selectRecords($queryStr, $params, $serialRows);
		if ($ret){
			for ($i = 0; $i < count($serialRows); $i++){
				$serialArray[] = $serialRows[$i]['ee_serial'];
			}
		}
		$serialStr = implode(',', $serialArray);
		if (empty($serialStr)) $serialStr = '0';	// 0レコードのときはダミー値を設定
	
		$queryStr  = 'SELECT * FROM event_entry ';
		$queryStr .=   'WHERE ee_serial in (' . $serialStr . ') ';
		return $this->selectRecordCount($queryStr, array());
	}
	/**
	 * エントリー項目数をカテゴリー指定で取得(表示用)
	 *
	 * @param timestamp $now				現在日時(現在日時より未来の投稿日時の記事は取得しない)
	 * @param int		$categoryId			カテゴリーID
	 * @param string	$langId				言語
	 * @return int							エントリー項目数
	 */
/*	function getEntryItemsCountByCategory($now, $categoryId, $langId)
	{
		$initDt = $this->gEnv->getInitValueOfTimestamp();		// 日時初期化値
		$params = array();
		
		$queryStr = 'SELECT distinct(ee_serial) FROM event_entry RIGHT JOIN event_entry_with_category ON ee_serial = ew_entry_serial ';
		$queryStr .=  'WHERE ee_language_id = ? '; $params[] = $langId;
		$queryStr .=    'AND ee_deleted = false ';		// 削除されていない
		$queryStr .=    'AND ee_status = ? '; $params[] = 2;	// 「公開」(2)データ
		$queryStr .=    'AND ew_category_id = ? ';	$params[] = $categoryId;// 記事カテゴリー
		
		// シリアル番号を取得
		$serialArray = array();
		$ret = $this->selectRecords($queryStr, $params, $serialRows);
		if ($ret){
			for ($i = 0; $i < count($serialRows); $i++){
				$serialArray[] = $serialRows[$i]['ee_serial'];
			}
		}
		$serialStr = implode(',', $serialArray);
		if (empty($serialStr)) $serialStr = '0';	// 0レコードのときはダミー値を設定
	
		$queryStr = 'SELECT * FROM event_entry ';
		$queryStr .=  'WHERE ee_serial in (' . $serialStr . ') ';
		return $this->selectRecordCount($queryStr, array());
	}*/
	/**
	 * イベント記事を取得(表示用)
	 *
	 * @param timestamp $now				現在日時
	 * @param timestamp	$startDt			期間(開始日)
	 * @param timestamp	$endDt				期間(終了日)
	 * @param string	$lang				言語
	 * @param array		$rows				取得データ
	 * @return bool							true=取得、false=取得せず
	 */
	function getEntryItemsForCelendar($now, $startDt, $endDt, $lang, &$rows)
	{
		$initDt = $this->gEnv->getInitValueOfTimestamp();
		$params = array();
		
		$queryStr = 'SELECT ee_id,ee_name,ee_summary,ee_place,ee_contact,ee_url,ee_start_dt,ee_end_dt,ee_is_all_day FROM event_entry ';
		$queryStr .=  'WHERE ee_deleted = false ';		// 削除されていない
		$queryStr .=    'AND ee_status = ? ';		$params[] = 2;	// 「公開」(2)データを表示
		$queryStr .=    'AND ee_language_id = ? ';	$params[] = $lang;
		
		if (!empty($startDt)){
			$queryStr .=    'AND ? <= ee_start_dt ';
			$params[] = $startDt;
		}
		if (!empty($endDt)){
			$queryStr .=    'AND ee_start_dt < ? ';
			$params[] = $endDt;
		}
		
		$queryStr .=  'ORDER BY ee_start_dt, ee_id';
		$retValue = $this->selectRecords($queryStr, $params, $rows);
		return $retValue;
	}
	/**
	 * イベント記事のデータのある期間を取得(表示用)
	 *
	 * @param string	$lang				言語
	 * @param timestamp	$startDt			期間(開始日)
	 * @param timestamp	$endDt				期間(終了日)
	 * @return bool							true=取得、false=取得せず
	 */
	function getTermWithEntryItems($langId, &$startDt, &$endDt)
	{
		$initDt = $this->gEnv->getInitValueOfTimestamp();
		$params = array();
		
		$queryStr  = 'SELECT MIN(ee_start_dt) AS minsdt, MAX(ee_start_dt) AS maxsdt, MAX(ee_end_dt) AS maxdt FROM event_entry ';
		$queryStr .=   'WHERE ee_deleted = false ';		// 削除されていない
		$queryStr .=     'AND ee_status = ? ';		$params[] = 2;	// 「公開」(2)データを表示
		$queryStr .=     'AND ee_language_id = ? ';	$params[] = $langId;
		$retValue = $this->selectRecord($queryStr, $params, $rows);
		if ($retValue){
			$startDt = $rows['minsdt'];				// 開始日最小
			$endDt = $rows['maxsdt'];				// 開始日最大
			if (!$rows['maxdt'] == $initDt && $rows['maxdt'] > $endDt) $endDt = $rows['maxdt'];
		}
		return $retValue;
	}
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
	 * 記事カテゴリー一覧を取得
	 *
	 * @param string	$langId				言語
	 * @param array		$rows				取得データ
	 * @return bool							true=取得、false=取得せず
	 */
	function getAllCategory($langId, &$rows)
	{
		$queryStr = 'SELECT * FROM event_category LEFT JOIN _login_user ON ec_create_user_id = lu_id AND lu_deleted = false ';
		$queryStr .=  'WHERE ec_language_id = ? ';
		$queryStr .=    'AND ec_deleted = false ';		// 削除されていない
		$queryStr .=  'ORDER BY ec_sort_order, ec_id';	// カテゴリー並び順
		$retValue = $this->selectRecords($queryStr, array($langId), $rows);
		return $retValue;
	}
}
?>
