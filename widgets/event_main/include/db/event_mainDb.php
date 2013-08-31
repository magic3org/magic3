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
	 * @param array		$category			カテゴリーID(キー=カテゴリ種別、値=カテゴリ値)
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
			$queryStr = 'SELECT distinct(ee_serial) FROM event_entry RIGHT JOIN event_entry_with_category ON ee_id = ew_entry_id ';
			$queryStr .=  'WHERE ee_language_id = ? '; $params[] = $langId;
			$queryStr .=    'AND ee_deleted = false ';		// 削除されていない
			
			// 記事カテゴリー
			//$queryStr .=    'AND bw_category_id in (' . implode(",", $category) . ') ';
			$queryStr .=    'AND (';
			$keys = array_keys($category);
			for ($i = 0; $i < count($keys); $i++){
				if ($i == 0){
					$queryStr .=    '(ew_category_id = ? '; $params[] = $keys[$i];
					$queryStr .=    'AND ew_category_item_id = ?)'; $params[] = $category[$keys[$i]];
				} else {
					$queryStr .=    'OR (ew_category_id = ? '; $params[] = $keys[$i];
					$queryStr .=    'AND ew_category_item_id = ?)'; $params[] = $category[$keys[$i]];
				}
			}
			$queryStr .=    ')';
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
			$queryStr .=  'ORDER BY ee_start_dt desc limit ' . $limit . ' offset ' . $offset;
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
			$queryStr .=  'ORDER BY ee_start_dt desc limit ' . $limit . ' offset ' . $offset;
			$this->selectLoop($queryStr, array(), $callback, null);
		}
	}
	/**
	 * エントリー項目数を取得(管理用)
	 *
	 * @param timestamp	$startDt			期間(開始日)
	 * @param timestamp	$endDt				期間(終了日)
	 * @param array		$category			カテゴリーID(キー=カテゴリ種別、値=カテゴリ値)
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
			$queryStr = 'SELECT distinct(ee_serial) FROM event_entry RIGHT JOIN event_entry_with_category ON ee_id = ew_entry_id ';
			$queryStr .=  'WHERE ee_language_id = ? '; $params[] = $langId;
			$queryStr .=    'AND ee_deleted = false ';		// 削除されていない
			
			// 記事カテゴリー
			//$queryStr .=    'AND bw_category_id in (' . implode(",", $category) . ') ';
			$queryStr .=    'AND (';
			$keys = array_keys($category);
			for ($i = 0; $i < count($keys); $i++){
				if ($i == 0){
					$queryStr .=    '(ew_category_id = ? '; $params[] = $keys[$i];
					$queryStr .=    'AND ew_category_item_id = ?)'; $params[] = $category[$keys[$i]];
				} else {
					$queryStr .=    'OR (ew_category_id = ? '; $params[] = $keys[$i];
					$queryStr .=    'AND ew_category_item_id = ?)'; $params[] = $category[$keys[$i]];
				}
			}
			$queryStr .=    ')';
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
	 * エントリー項目を検索(表示用)
	 *
	 * @param int		$limit				取得する項目数
	 * @param int		$page				取得するページ(1～)
	 * @param timestamp $now				現在日時(現在日時より未来の投稿日時の記事は取得しない)
	 * @param string	$keyword			検索キーワード
	 * @param string	$langId				言語
	 * @param function	$callback			コールバック関数
	 * @return 			なし
	 */
	function searchEntryItemsByKeyword($limit, $page, $now, $keyword, $langId, $callback)
	{
		$offset = $limit * ($page -1);
		if ($offset < 0) $offset = 0;
		$params = array();
		
		$queryStr = 'SELECT * FROM event_entry ';
		$queryStr .=   'WHERE ee_language_id = ? ';	$params[] = $langId;
		$queryStr .=     'AND ee_deleted = false ';		// 削除されていない

		// タイトルと記事を検索
		if (!empty($keyword)){
			// 「'"\」文字をエスケープ
			$keyword = addslashes($keyword);
			
			$queryStr .=    'AND (ee_name LIKE \'%' . $keyword . '%\' ';
			$queryStr .=    'OR ee_html LIKE \'%' . $keyword . '%\' ';
			$queryStr .=    'OR ee_html_ext LIKE \'%' . $keyword . '%\' ';
			$queryStr .=    'OR ee_summary LIKE \'%' . $keyword . '%\' ';
			$queryStr .=    'OR ee_place LIKE \'%' . $keyword . '%\' ';
			$queryStr .=    'OR ee_contact LIKE \'%' . $keyword . '%\') ';
		}
		
		$queryStr .=  'ORDER BY ee_start_dt desc limit ' . $limit . ' offset ' . $offset;
		$this->selectLoop($queryStr, $params, $callback, null);
	}
	/**
	 * 検索条件のエントリー項目数を取得(表示用)
	 *
	 * @param timestamp $now				現在日時(現在日時より未来の投稿日時の記事は取得しない)
	 * @param string	$keyword			検索キーワード
	 * @param string	$langId				言語
	 * @return int							項目数
	 */
	function searchEntryItemsCountByKeyword($now, $keyword, $langId)
	{
		$params = array();
		
		$queryStr = 'SELECT * FROM event_entry ';
		$queryStr .=   'WHERE ee_language_id = ? ';	$params[] = $langId;
		$queryStr .=     'AND ee_deleted = false ';		// 削除されていない

		// タイトルと記事を検索
		if (!empty($keyword)){
			// 「'"\」文字をエスケープ
			$keyword = addslashes($keyword);
			
			$queryStr .=    'AND (ee_name LIKE \'%' . $keyword . '%\' ';
			$queryStr .=    'OR ee_html LIKE \'%' . $keyword . '%\' ';
			$queryStr .=    'OR ee_html_ext LIKE \'%' . $keyword . '%\' ';
			$queryStr .=    'OR ee_summary LIKE \'%' . $keyword . '%\' ';
			$queryStr .=    'OR ee_place LIKE \'%' . $keyword . '%\' ';
			$queryStr .=    'OR ee_contact LIKE \'%' . $keyword . '%\') ';
		}
		return $this->selectRecordCount($queryStr, $params);
	}
	/**
	 * エントリー項目の新規追加
	 *
	 * @param string  $id			エントリーID
	 * @param string  $langId		言語ID
	 * @param string  $name			イベント名
	 * @param string  $html			HTML
	 * @param string  $html2		HTML(続き)
	 * @param string  $summary		要約
	 * @param string  $place		場所
	 * @param string  $contact		連絡先
	 * @param string  $url			URL
	 * @param string  $note			管理者備考
	 * @param int     $status		エントリー状態(0=未設定、1=編集中、2=公開、3=非公開)
	 * @param array   $category		カテゴリーID
	 * @param timestamp	$startDt	期間(開始日)
	 * @param timestamp	$endDt		期間(終了日)
	 * @param bool    $showComment	コメントを表示するかどうか
	 * @param bool $receiveComment	コメントを受け付けるかどうか
	 * @param bool $userLimited		参照ユーザを制限するかどうか
	 * @param int     $newSerial	新規シリアル番号
	 * @return bool					true = 成功、false = 失敗
	 */
	function addEntryItem($id, $langId, $name, $html, $html2, $summary, $place, $contact, $url, $note, $status, $category, $startDt, $endDt, $showComment, $receiveComment, $userLimited, &$newSerial)
	{
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $this->gEnv->getCurrentUserId();	// 現在のユーザ
			
		// トランザクション開始
		$this->startTransaction();
		
		if ($id == 0){		// エントリーIDが0のときは、エントリーIDを新規取得
			// エントリーIDを決定する
			$queryStr = 'SELECT MAX(ee_id) AS mid FROM event_entry ';
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
		$queryStr .=   'ee_show_comment, ';
		$queryStr .=   'ee_receive_comment, ';
		$queryStr .=   'ee_user_limited, ';
		$queryStr .=   'ee_start_dt, ';
		$queryStr .=   'ee_end_dt, ';
		$queryStr .=   'ee_create_user_id, ';
		$queryStr .=   'ee_create_dt) ';
		$queryStr .= 'VALUES ';
		$queryStr .=   '(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
		$this->execStatement($queryStr, array($entryId, $langId, $historyIndex, $name, $html, $html2, $summary, $place, $contact, $url, $note, $status, 
												intval($showComment), intval($receiveComment), intval($userLimited), $startDt, $endDt, $userId, $now));
		
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
	 * @param string  $summary		要約
	 * @param string  $place		場所
	 * @param string  $contact		連絡先
	 * @param string  $url			URL
	 * @param string  $note			管理者備考
	 * @param int     $status		エントリー状態(0=未設定、1=編集中、2=公開、3=非公開)
	 * @param array   $category		カテゴリーID
	 * @param timestamp	$startDt	期間(開始日)
	 * @param timestamp	$endDt		期間(終了日)
	 * @param bool    $showComment	コメントを表示するかどうか
	 * @param bool $receiveComment	コメントを受け付けるかどうか
	 * @param bool $userLimited		参照ユーザを制限するかどうか
	 * @param int     $newSerial	新規シリアル番号
	 * @return bool					true = 成功、false = 失敗
	 */
	function updateEntryItem($serial, $name, $html, $html2, $summary, $place, $contact, $url, $note, $status, $category, $startDt, $endDt, $showComment, $receiveComment, $userLimited, &$newSerial)
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
		if (empty($regDt)){
			$rDt = $row['ee_regist_dt'];
		} else {
			$rDt = $regDt;
		}
		$entryId = $row['ee_id'];
		$langId = $row['ee_language_id'];
		
		// 新規レコード追加		
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
		$queryStr .=   'ee_show_comment, ';
		$queryStr .=   'ee_receive_comment, ';
		$queryStr .=   'ee_user_limited, ';
		$queryStr .=   'ee_start_dt, ';
		$queryStr .=   'ee_end_dt, ';
		$queryStr .=   'ee_create_user_id, ';
		$queryStr .=   'ee_create_dt) ';
		$queryStr .= 'VALUES ';
		$queryStr .=   '(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
		$this->execStatement($queryStr, array($entryId, $langId, $historyIndex, $name, $html, $html2, $summary, $place, $contact, $url, $note, $status, 
												intval($showComment), intval($receiveComment), intval($userLimited), $startDt, $endDt, $userId, $now));

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
		$queryStr = 'INSERT INTO blog_entry_with_category ';
		$queryStr .=  '(';
		$queryStr .=  'bw_entry_serial, ';
		$queryStr .=  'bw_index, ';
		$queryStr .=  'bw_category_id) ';
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
			$queryStr  = 'SELECT * FROM event_entry_with_category ';
			$queryStr .=   'WHERE ew_entry_id = ? ';
			$queryStr .=  'ORDER BY ew_serial ';
			$this->selectRecords($queryStr, array($row['ee_id']), $rows);
			$categoryRow = array();
			for ($i = 0; $i < count($rows); $i++){
				$categoryRow['ew_category_id'] = $rows[$i]['ew_category_item_id'];
			}
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
	 * @param int		$id					エントリーID
	 * @param string	$langId				言語
	 * @param array     $row				レコード
	 * @return bool							取得 = true, 取得なし= false
	 */
	function getEntryItem($id, $langId, &$row)
	{
		$queryStr  = 'SELECT * FROM event_entry ';
		$queryStr .=   'WHERE ee_deleted = false ';	// 削除されていない
		$queryStr .=   'AND ee_id = ? ';
		$queryStr .=   'AND ee_language_id = ? ';
		$ret = $this->selectRecord($queryStr, array($id, $langId), $row);
		return $ret;
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
	 * @return 			なし
	 */
	function getEntryItems($limit, $page, $now, $entryId, $startDt, $endDt, $langId, $order, $callback)
	{
		$offset = $limit * ($page -1);
		if ($offset < 0) $offset = 0;
		$params = array();
		
		// エントリーIDの指定がない場合は、期間で取得
		if (empty($entryId)){
			$queryStr  = 'SELECT * FROM event_entry ';
			$queryStr .=   'WHERE ee_deleted = false ';		// 削除されていない
			$queryStr .=     'AND ee_status = ? ';		$params[] = 2;	// 「公開」(2)データを表示
			$queryStr .=     'AND ee_language_id = ? ';	$params[] = $langId;
		
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
		
			$ord = '';
			if (!empty($order)) $ord = 'DESC ';
			$queryStr .=  'ORDER BY ee_start_dt ' . $ord . 'LIMIT ' . $limit . ' offset ' . $offset;// 投稿順
			$this->selectLoop($queryStr, $params, $callback, null);
		} else {
			$queryStr  = 'SELECT * FROM event_entry ';
			$queryStr .=   'WHERE ee_deleted = false ';		// 削除されていない
			$queryStr .=     'AND ee_status = ? ';	$params[] = 2;	// 「公開」(2)データを表示
			$queryStr .=     'AND ee_id = ? ';		$params[] = $entryId;
			$queryStr .=     'AND ee_language_id = ? ';	$params[] = $langId;
			
			$this->selectLoop($queryStr, $params, $callback, null);		// 「公開」(2)データを表示
		}
	}
	
	/**
	 * エントリー項目数を取得(表示用)
	 *
	 * @param timestamp $now				現在日時(期間を指定しない場合は現在日より未来のイベントを取得)
	 * @param timestamp	$startDt			期間(開始日)
	 * @param timestamp	$endDt				期間(終了日)
	 * @param string	$langId				言語
	 * @return int							項目数
	 */
	function getEntryItemsCount($now, $startDt, $endDt, $langId)
	{
		$params = array();
		
		$queryStr = 'SELECT * FROM event_entry ';
		$queryStr .=  'WHERE ee_deleted = false ';		// 削除されていない
		$queryStr .=    'AND ee_status = ? ';		$params[] = 2;	// 「公開」(2)データを表示
		$queryStr .=    'AND ee_language_id = ? ';	$params[] = $langId;
		
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
		return $this->selectRecordCount($queryStr, $params);
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
	function getEntryItemsByCategory($limit, $page, $now, $categoryId, $langId, $order, $callback)
	{
		$offset = $limit * ($page -1);
		if ($offset < 0) $offset = 0;
		$initDt = $this->gEnv->getInitValueOfTimestamp();		// 日時初期化値
		$params = array();
		
		$queryStr = 'SELECT distinct(be_serial) FROM blog_entry RIGHT JOIN blog_entry_with_category ON be_serial = bw_entry_serial ';
		$queryStr .=  'WHERE be_language_id = ? '; $params[] = $langId;
		$queryStr .=    'AND be_deleted = false ';		// 削除されていない
		$queryStr .=    'AND be_status = ? '; $params[] = 2;	// 「公開」(2)データ
		$queryStr .=    'AND bw_category_id = ? ';	$params[] = $categoryId;// 記事カテゴリー
		$queryStr .=    'AND be_regist_dt <= ? ';	$params[] = $now;			// 投稿日時が現在日時よりも過去のものを取得
		
		// 公開期間を指定
		$queryStr .=    'AND (be_active_start_dt = ? OR (be_active_start_dt != ? AND be_active_start_dt <= ?)) ';
		$queryStr .=    'AND (be_active_end_dt = ? OR (be_active_end_dt != ? AND be_active_end_dt > ?)) ';
		$params[] = $initDt;
		$params[] = $initDt;
		$params[] = $now;
		$params[] = $initDt;
		$params[] = $initDt;
		$params[] = $now;
		
		// シリアル番号を取得
		$serialArray = array();
		$ret = $this->selectRecords($queryStr, $params, $serialRows);
		if ($ret){
			for ($i = 0; $i < count($serialRows); $i++){
				$serialArray[] = $serialRows[$i]['be_serial'];
			}
		}
		$serialStr = implode(',', $serialArray);
		if (empty($serialStr)) $serialStr = '0';	// 0レコードのときはダミー値を設定
	
		//$queryStr = 'SELECT * FROM blog_entry ';
		$queryStr  = 'SELECT * FROM blog_entry LEFT JOIN blog_id ON be_blog_id = bl_id AND bl_deleted = false ';
		$queryStr .=   'WHERE be_serial in (' . $serialStr . ') ';
		$ord = '';
		if (!empty($order)) $ord = 'DESC ';
		$queryStr .=  'ORDER BY be_regist_dt ' . $ord . 'LIMIT ' . $limit . ' offset ' . $offset;// 投稿順
		$this->selectLoop($queryStr, array(), $callback, null);
	}
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
		$initDt = $this->gEnv->getInitValueOfTimestamp();		// 日時初期化値
		$params = array();
		
		$queryStr = 'SELECT distinct(be_serial) FROM blog_entry RIGHT JOIN blog_entry_with_category ON be_serial = bw_entry_serial ';
		$queryStr .=  'WHERE be_language_id = ? '; $params[] = $langId;
		$queryStr .=    'AND be_deleted = false ';		// 削除されていない
		$queryStr .=    'AND be_status = ? '; $params[] = 2;	// 「公開」(2)データ
		$queryStr .=    'AND bw_category_id = ? ';	$params[] = $categoryId;// 記事カテゴリー
		$queryStr .=    'AND be_regist_dt <= ? ';	$params[] = $now;			// 投稿日時が現在日時よりも過去のものを取得
		
		// 公開期間を指定
		$queryStr .=    'AND (be_active_start_dt = ? OR (be_active_start_dt != ? AND be_active_start_dt <= ?)) ';
		$queryStr .=    'AND (be_active_end_dt = ? OR (be_active_end_dt != ? AND be_active_end_dt > ?)) ';
		$params[] = $initDt;
		$params[] = $initDt;
		$params[] = $now;
		$params[] = $initDt;
		$params[] = $initDt;
		$params[] = $now;
		
		// シリアル番号を取得
		$serialArray = array();
		$ret = $this->selectRecords($queryStr, $params, $serialRows);
		if ($ret){
			for ($i = 0; $i < count($serialRows); $i++){
				$serialArray[] = $serialRows[$i]['be_serial'];
			}
		}
		$serialStr = implode(',', $serialArray);
		if (empty($serialStr)) $serialStr = '0';	// 0レコードのときはダミー値を設定
	
		$queryStr = 'SELECT * FROM blog_entry ';
		$queryStr .=  'WHERE be_serial in (' . $serialStr . ') ';
		return $this->selectRecordCount($queryStr, array());
	}
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
		
		$queryStr = 'SELECT ee_id,ee_name,ee_summary,ee_place,ee_contact,ee_url,ee_start_dt,ee_end_dt FROM event_entry ';
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
		
		$queryStr .=  'ORDER BY ee_start_dt,ee_id';
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
		$params = array();
		
		$queryStr  = 'SELECT MIN(ee_start_dt) AS mindt, MAX(ee_end_dt) AS maxdt FROM event_entry ';
		$queryStr .=   'WHERE ee_deleted = false ';		// 削除されていない
		$queryStr .=     'AND ee_status = ? ';		$params[] = 2;	// 「公開」(2)データを表示
		$queryStr .=     'AND ee_language_id = ? ';	$params[] = $langId;
		$retValue = $this->selectRecord($queryStr, $params, $rows);
		if ($retValue){
			$startDt = $rows['mindt'];
			$endDt = $rows['maxdt'];
			$endDt = $startDt > $endDt ? $startDt : $endDt;
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
		$queryStr = 'SELECT * FROM blog_category LEFT JOIN _login_user ON bc_create_user_id = lu_id AND lu_deleted = false ';
		$queryStr .=  'WHERE bc_language_id = ? ';
		$queryStr .=    'AND bc_deleted = false ';		// 削除されていない
		$queryStr .=  'ORDER BY bc_id';
		$retValue = $this->selectRecords($queryStr, array($langId), $rows);
		return $retValue;
	}
}
?>
