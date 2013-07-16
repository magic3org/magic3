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
 * @version    SVN: $Id: wiki_mainDb.php 4953 2012-06-09 10:09:55Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getDbPath() . '/baseDb.php');

class wiki_mainDb extends BaseDb
{
	/**
	 * ページが存在するかチェック
	 *
	 * @param string $name	ページ名
	 * @param string $type	ページタイプ
	 * @return				true=存在する、false=存在しない
	 */
	function isExistsPage($name, $type='')
	{
		$queryStr = 'SELECT * from wiki_content ';
		$queryStr .=  'WHERE wc_type = ? ';
		$queryStr .=    'AND wc_id = ? ';
		$queryStr .=    'AND wc_deleted = false';
		return $this->isRecordExists($queryStr, array($type, $name));
	}
	/**
	 * Wikiページデータの取得
	 *
	 * @param string  $name			ウィキページ名
	 * @param array   $row			レコード
	 * @param string  $type			ページタイプ
	 * @return bool					取得 = true, 取得なし= false
	 */
	function getPage($name, &$row, $type='')
	{
		$queryStr  = 'SELECT * FROM wiki_content ';
		$queryStr .=   'WHERE wc_deleted = false ';	// 削除されていない
		$queryStr .=    'AND wc_type = ? ';
		$queryStr .=   'AND wc_id = ? ';
		$ret = $this->selectRecord($queryStr, array($type, $name), $row);
		return $ret;
	}
	/**
	 * Wikiページデータを履歴番号で取得
	 *
	 * @param string  $name			ウィキページ名
	 * @param int $history			履歴番号
	 * @param array   $row			レコード
	 * @param string  $type			ページタイプ
	 * @return bool					取得 = true, 取得なし= false
	 */
	function getPageWithHistory($name, $history, &$row, $type='')
	{
		$queryStr  = 'SELECT * FROM wiki_content ';
		$queryStr .=   'WHERE wc_type = ? ';
		$queryStr .=   'AND wc_id = ? ';
		$queryStr .=   'AND wc_history_index = ? ';
		$ret = $this->selectRecord($queryStr, array($type, $name, intval($history)), $row);
		return $ret;
	}
	
	/**
	 * 古いWikiページ(削除フラグがオンのデータ)の表示状態の制御
	 *
	 * @param string  $name			ウィキページ名
	 * @param bool   $visible		true=表示、false=非表示
	 * @param string  $type			ページタイプ
	 * @return bool					true=成功、false=失敗
	 */
	function setOldPageVisible($name, $visible, $type='')
	{
		global $gEnvManager;
		
		// トランザクションスタート
		$this->startTransaction();
		
		// 表示状態を更新
		$queryStr  = 'UPDATE wiki_content ';
		$queryStr .=   'SET wc_visible = ? ';	
		$queryStr .=  'WHERE wc_type = ? ';
		$queryStr .=    'AND wc_id = ? ';
		$queryStr .=    'AND wc_deleted = true';// 削除済みデータ
		$this->execStatement($queryStr, array(intval($visible), $type, $name));
		
		// トランザクション終了
		$ret = $this->endTransaction();
		return $ret;
	}
	
	/**
	 * 履歴情報を取得
	 *
	 * @param string  $name			ウィキページ名
	 * @param int $history			履歴番号(-1のときはすべてのレコードを取得)
	 * @param array   $row			レコード
	 * @param string  $type			ページタイプ
	 * @return bool					取得 = true, 取得なし= false
	 */
	function getPageInfo($name, $history, &$rows, $type='')
	{
		$param = array($name, $type);
		$queryStr .= 'SELECT wc_serial, ';
		$queryStr .=   'wc_id, ';
		$queryStr .=   'wc_history_index, ';
		$queryStr .=   'wc_content_dt, ';
		$queryStr .=   'wc_visible, ';
		$queryStr .=   'wc_update_user_id, ';
		$queryStr .=   'wc_update_dt, ';
		$queryStr .=   'wc_deleted ';
		$queryStr .= 'FROM wiki_content ';
		$queryStr .= 'WHERE wc_id = ? ';	
		$queryStr .=   'AND wc_type = ? ';
		if ($history != -1){
			$queryStr .=   'AND wc_history_index = ? ';
			$param[] = intval($history);
		}
		$queryStr .= 'ORDER BY wc_history_index';
		$ret = $this->selectRecords($queryStr, $param, $rows);
		return $ret;
	}
	/**
	 * 取得可能なWikiページ名の取得
	 *
	 * @param string  $type			ページタイプ
	 * @return array				ページ名
	 */
	function getAvailablePages($type='')
	{
		$retValue = array();
		$queryStr  = 'SELECT wc_id FROM wiki_content ';
		$queryStr .=   'WHERE wc_deleted = false ';	// 削除されていない
		$queryStr .=    'AND wc_type = ? ';
		$queryStr .=    'ORDER BY wc_id';
		$ret = $this->selectRecords($queryStr, array($type), $rows);
		if ($ret){
			for ($i = 0; $i < count($rows); $i++){
				$retValue[] = $rows[$i]['wc_id'];
			}
		}
		return $retValue;
	}
	/**
	 * すべてのWikiページ名の取得
	 *
	 * @param string  $type			ページタイプ
	 * @return array				ページ名
	 */
	function getAllPages($type='')
	{
		$retValue = array();
		$queryStr  = 'SELECT DISTINCT wc_id FROM wiki_content ';
		$queryStr .=   'WHERE wc_type = ? ';
		$queryStr .=    'ORDER BY wc_id';
		$ret = $this->selectRecords($queryStr, array($type), $rows);
		if ($ret){
			for ($i = 0; $i < count($rows); $i++){
				$retValue[] = $rows[$i]['wc_id'];
			}
		}
		return $retValue;
	}
	/**
	 * Wikiページの更新
	 *
	 * @param string  $name			ウィキページ名
	 * @param string  $data			データ
	 * @param string  $type			ページタイプ
	 * @param bool $keepTime		更新日時を維持するかどうか
	 * @return bool					true = 成功、false = 失敗
	 */
	function updatePage($name, $data, $type='', $keepTime=false)
	{
		global $gEnvManager;
		
		$historyIndex = 0;		// 履歴番号
		$now = date("Y/m/d H:i:s");	// 現在日時
		$contentNow = $now;			// コンテンツ更新日時
		$locked = 1;		// コンテンツロック状態
		$userId = $gEnvManager->getCurrentUserId();	// 現在のユーザ
		
		// トランザクションスタート
		$this->startTransaction();
				
		// 前レコードの削除状態チェック
		$queryStr = 'SELECT * FROM wiki_content ';
		$queryStr .=  'WHERE wc_type = ? ';
		$queryStr .=    'AND wc_id = ? ';
		$queryStr .=  'ORDER BY wc_history_index DESC ';
		$ret = $this->selectRecord($queryStr, array($type, $name), $row);
		if ($ret){
			if ($row['wc_deleted']){// レコードが削除されているときは、新規追加とする
			} else {		// レコードが削除されていなければ削除
				// 古いレコードを削除
				$queryStr  = 'UPDATE wiki_content ';
				$queryStr .=   'SET wc_deleted = true, ';	// 削除
				$queryStr .=     'wc_update_user_id = ?, ';
				$queryStr .=     'wc_update_dt = ? ';
				$queryStr .=   'WHERE wc_serial = ?';
				$this->execStatement($queryStr, array($userId, $now, $row['wc_serial']));
				
				$locked = $row['wc_locked'];		// ロック状態を引き継ぐ
				// 更新日時を維持のときは更新日時取得
				if ($keepTime) $contentNow = $row['wc_content_dt'];
			}
			// 履歴インデックス番号作成
			$historyIndex = $row['wc_history_index'] + 1;
		}

		// 新規レコード追加
		$queryStr = 'INSERT INTO wiki_content ';
		$queryStr .=  '(wc_type, wc_id, wc_history_index, wc_data, wc_content_dt, wc_locked, wc_create_user_id, wc_create_dt) ';
		$queryStr .=  'VALUES ';
		$queryStr .=  '(?, ?, ?, ?, ?, ?, ?, ?)';
		$this->execStatement($queryStr, array($type, $name, $historyIndex, $data, $contentNow, intval($locked), $userId, $now));
		
		// トランザクション終了
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * Wikiページを削除
	 *
	 * @param string  $name			ウィキページ名
	 * @param string  $type			ページタイプ
	 * @return bool					true=成功、false=失敗
	 */
	function deletePage($name, $type='')
	{
		global $gEnvManager;
		
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $gEnvManager->getCurrentUserId();	// 現在のユーザ
		
		// トランザクションスタート
		$this->startTransaction();
				
		// レコードを取得
		$queryStr = 'SELECT * from wiki_content ';
		$queryStr .=  'WHERE wc_type = ? ';
		$queryStr .=    'AND wc_id = ? ';
		$queryStr .=    'AND wc_deleted = false';
		$ret = $this->selectRecord($queryStr, array($type, $name), $row);
		if (!$ret){
			// トランザクション終了
			$ret = $this->endTransaction();
			return false;
		}
		
		$queryStr  = 'UPDATE wiki_content ';
		$queryStr .=   'SET wc_deleted = true, ';	// 削除
		$queryStr .=     'wc_update_user_id = ?, ';
		$queryStr .=     'wc_update_dt = ? ';
		$queryStr .=   'WHERE wc_serial = ?';
		$this->execStatement($queryStr, array($userId, $now, $row['wc_serial']));
		
		// トランザクション終了
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * ページを変更
	 *
	 * @param string $oldName		旧ページ名
	 * @param string $newName		新ページ名
	 * @param string  $type			ページタイプ
	 * @return bool					true=成功、false=失敗
	 */
	public function renamePage($oldName, $newName, $type='')
	{
		global $gEnvManager;
		
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $gEnvManager->getCurrentUserId();	// 現在のユーザ
		
		// トランザクションスタート
		$this->startTransaction();
		
		// ##### 旧ページを取得 #####
		$queryStr = 'SELECT * from wiki_content ';
		$queryStr .=  'WHERE wc_type = ? ';
		$queryStr .=    'AND wc_id = ? ';
		$queryStr .=    'AND wc_deleted = false';
		$ret = $this->selectRecord($queryStr, array($type, $oldName), $oldRow);
		if (!$ret){
			// トランザクション終了
			$ret = $this->endTransaction();
			return false;
		}
		
		// ##### 新ページを作成 #####
		$historyIndex = 0;		// 履歴番号
		
		// 前レコードの削除状態チェック
		$queryStr = 'SELECT * FROM wiki_content ';
		$queryStr .=  'WHERE wc_type = ? ';
		$queryStr .=    'AND wc_id = ? ';
		$queryStr .=  'ORDER BY wc_history_index DESC ';
		$ret = $this->selectRecord($queryStr, array($type, $newName), $row);
		if ($ret){
			if ($row['wc_deleted']){// レコードが削除されているときは、新規追加とする
			} else {		// レコードが削除されていなければ削除
				// 古いレコードを削除
				$queryStr  = 'UPDATE wiki_content ';
				$queryStr .=   'SET wc_deleted = true, ';	// 削除
				$queryStr .=     'wc_update_user_id = ?, ';
				$queryStr .=     'wc_update_dt = ? ';
				$queryStr .=   'WHERE wc_serial = ?';
				$this->execStatement($queryStr, array($userId, $now, $row['wc_serial']));
			}
			// 履歴インデックス番号作成
			$historyIndex = $row['wc_history_index'] + 1;
		}

		// 新規レコード追加
		$queryStr = 'INSERT INTO wiki_content ';
		$queryStr .=  '(wc_type, wc_id, wc_history_index, wc_data, wc_content_dt, wc_locked, wc_fore_serial, wc_create_user_id, wc_create_dt) ';
		$queryStr .=  'VALUES ';
		$queryStr .=  '(?, ?, ?, ?, ?, ?, ?, ?, ?)';
		$this->execStatement($queryStr, array($type, $newName, $historyIndex, $oldRow['wc_data'], 
			$oldRow['wc_content_dt'], intval($oldRow['wc_locked']), $oldRow['wc_serial']/*引き継ぎシリアル番号*/, $userId, $now));
		
		// シリアル番号取得
		$newSerial = 0;
		$queryStr = 'SELECT MAX(wc_serial) AS ns FROM wiki_content ';
		$ret = $this->selectRecord($queryStr, array(), $row);
		if ($ret) $newSerial = $row['ns'];
		
		// 旧ページを削除
		$queryStr  = 'UPDATE wiki_content ';
		$queryStr .=   'SET wc_deleted = true, ';	// 削除
		$queryStr .=     'wc_update_user_id = ?, ';
		$queryStr .=     'wc_update_dt = ?, ';
		$queryStr .=     'wc_next_serial = ? ';		// 引き継ぎシリアル番号
		$queryStr .=   'WHERE wc_serial = ?';
		$this->execStatement($queryStr, array($userId, $now, $newSerial, $oldRow['wc_serial']));
		
		// トランザクション終了
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * Wikiページのロックの制御
	 *
	 * @param string  $name			ウィキページ名
	 * @param bool   $lock			true=ロック、false=ロック解除
	 * @param string  $type			ページタイプ
	 * @return bool					true=成功、false=失敗
	 */
	function lockPage($name, $lock, $type='')
	{
		global $gEnvManager;
		
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $gEnvManager->getCurrentUserId();	// 現在のユーザ
		
		// トランザクションスタート
		$this->startTransaction();
				
		// レコードを取得
		$queryStr = 'SELECT * from wiki_content ';
		$queryStr .=  'WHERE wc_type = ? ';
		$queryStr .=    'AND wc_id = ? ';
		$queryStr .=    'AND wc_deleted = false';
		$ret = $this->selectRecord($queryStr, array($type, $name), $row);
		if (!$ret){
			// トランザクション終了
			$ret = $this->endTransaction();
			return false;
		}
		
		// ロック状態を変更
		$lockValue = 0;		// ロック状態
		if ($lock) $lockValue = 1;
		
		$queryStr  = 'UPDATE wiki_content ';
		$queryStr .=   'SET wc_locked = ?, ';	// ロック状態
		$queryStr .=     'wc_check_out_user_id = ?, ';
		$queryStr .=     'wc_check_out_dt = ? ';
		$queryStr .=   'WHERE wc_serial = ?';
		$this->execStatement($queryStr, array(intval($lockValue), $userId, $now, $row['wc_serial']));
		
		// トランザクション終了
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * Wikiページの更新日付を更新
	 *
	 * @param string  $name			ウィキページ名
	 * @param timestamp $time		更新日時(nullのときは、現在日時を設定)
	 * @param string  $type			ページタイプ
	 * @return bool					true=成功、false=失敗
	 */
	function updatePageTime($name, $time, $type='')
	{
		global $gEnvManager;
		
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $gEnvManager->getCurrentUserId();	// 現在のユーザ
		
		// トランザクションスタート
		$this->startTransaction();
				
		// レコードを取得
		$queryStr = 'SELECT * from wiki_content ';
		$queryStr .=  'WHERE wc_type = ? ';
		$queryStr .=    'AND wc_id = ? ';
		$queryStr .=    'AND wc_deleted = false';
		$ret = $this->selectRecord($queryStr, array($type, $name), $row);
		if (!$ret){
			// トランザクション終了
			$ret = $this->endTransaction();
			return false;
		}
		
		// コンテンツ更新日時
		$contentTime = $time;
		if ($contentTime == null) $contentTime = $now;

		$queryStr  = 'UPDATE wiki_content ';
		$queryStr .=   'SET wc_content_dt = ? ';	// コンテンツ更新日時
		$queryStr .=   'WHERE wc_serial = ?';
		$this->execStatement($queryStr, array($contentTime, $row['wc_serial']));
		
		// トランザクション終了
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * Wikiページその他データの更新
	 *
	 * @param string  $name			ウィキページ名
	 * @param string  $data			データ
	 * @param string  $type			ページタイプ
	 * @return bool					true = 成功、false = 失敗
	 */
	function updatePageOther($name, $data, $type)
	{
		global $gEnvManager;
		
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $gEnvManager->getCurrentUserId();	// 現在のユーザ
		
		// トランザクションスタート
		$this->startTransaction();

		$queryStr = 'SELECT * FROM wiki_content ';
		$queryStr .=  'WHERE wc_type = ? ';
		$queryStr .=    'AND wc_id = ? ';
		$queryStr .=    'AND wc_history_index = 0';
		$ret = $this->selectRecord($queryStr, array($type, $name), $row);
		if ($ret){
			$queryStr  = 'UPDATE wiki_content ';
			$queryStr .=   'SET wc_data = ?, ';
			$queryStr .=     'wc_update_user_id = ?, ';
			$queryStr .=     'wc_update_dt = ? ';
			$queryStr .=   'WHERE wc_serial = ?';
			$this->execStatement($queryStr, array($data, $userId, $now, $row['wc_serial']));			
		} else {
			$queryStr  = 'INSERT INTO wiki_content (';
			$queryStr .=   'wc_type, ';
			$queryStr .=   'wc_id, ';
			$queryStr .=   'wc_data, ';
			$queryStr .=   'wc_create_user_id, ';
			$queryStr .=   'wc_create_dt ';
			$queryStr .= ') VALUES (';
			$queryStr .=   '?, ?, ?, ?, ?';
			$queryStr .= ')';
			$this->execStatement($queryStr, array($type, $name, $data, $userId, $now));	
		}
		// トランザクション終了
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * Wikiページその他データを取得
	 *
	 * @param string  $name			ウィキページ名
	 * @param string  $type			ページタイプ
	 * @return string				取得値
	 */
	function getPageOther($name, $type)
	{
		$queryStr = 'SELECT * FROM wiki_content ';
		$queryStr .=  'WHERE wc_type = ? ';
		$queryStr .=    'AND wc_id = ? ';
		$queryStr .=    'AND wc_history_index = 0';
		$ret = $this->selectRecord($queryStr, array($type, $name), $row);
		if ($ret){
			return $row['wc_data'];
		} else {
			return '';
		}
	}
	/**
	 * Wikiページその他データが存在するかチェック
	 *
	 * @param string  $name			ウィキページ名
	 * @param string  $type			ページタイプ
	 * @return				true=存在する、false=存在しない
	 */
	function isExistsPageOther($name, $type)
	{
		$queryStr = 'SELECT * FROM wiki_content ';
		$queryStr .=  'WHERE wc_type = ? ';
		$queryStr .=    'AND wc_id = ? ';
		$queryStr .=    'AND wc_history_index = 0';
		return $this->isRecordExists($queryStr, array($type, $name));
	}
	/**
	 * Wikiページその他データを削除
	 *
	 * @param string $name			ページ名(空文字列のときは同じタイプのデータをすべて削除)
	 * @param string  $type			ページタイプ
	 * @return bool					true = 成功、false = 失敗
	 */
	function clearPageOther($name, $type)
	{
		// トランザクションスタート
		$this->startTransaction();
		
		$param = array();
		$queryStr  = 'DELETE FROM wiki_content ';
		$queryStr .=  'WHERE wc_type = ? '; $param[] = $type;
		if (!empty($name)){
			$queryStr .=  'AND wc_id = ? '; $param[] = $name;
		}
		$this->execStatement($queryStr, $param);
		
		// トランザクション終了
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * 設定値が存在するかチェック
	 *
	 * @param string $id	定義ID
	 * @return				true=存在する、false=存在しない
	 */
	function isExistsConfig($id)
	{
		$queryStr = 'SELECT * from wiki_config ';
		$queryStr .=  'WHERE wg_id = ? ';
		return $this->isRecordExists($queryStr, array($id));
	}
	/**
	 * 設定値を定義IDで取得
	 *
	 * @param string $id		定義ID
	 * @return string			定義値
	 */
	function getConfig($id)
	{
		$queryStr  = 'SELECT * FROM wiki_config ';
		$queryStr .=   'WHERE wg_id = ? ';
		$ret = $this->selectRecord($queryStr, array($id), $row);
		if ($ret){
			return $row['wg_value'];
		} else {
			return '';
		}
	}
	/**
	 * 設定値の更新
	 *
	 * @param string $id			定義ID
	 * @param string  $data			データ
	 * @param string  $name			名前
	 * @param string  $desc			説明
	 * @return bool					true = 成功、false = 失敗
	 */
	function updateConfig($id, $data, $name='', $desc='')
	{
		// トランザクション開始
		$this->startTransaction();

		$queryStr  = 'SELECT * FROM wiki_config ';
		$queryStr .=   'WHERE wg_id = ? ';	
		$ret = $this->selectRecord($queryStr, array($id), $row);
		if ($ret){
			$queryStr  = 'UPDATE wiki_config ';
			$queryStr .=   'SET wg_value = ?, ';
			$queryStr .=     'wg_name = ?, ';
			$queryStr .=     'wg_description = ? ';
			$queryStr .=   'WHERE wg_id = ?';
			$this->execStatement($queryStr, array($data, $row['wg_name'], $row['wg_description'], $id));			
		} else {
			$queryStr = 'INSERT INTO wiki_config (';
			$queryStr .=  'wg_id, ';
			$queryStr .=  'wg_value, ';
			$queryStr .=  'wg_name, ';
			$queryStr .=  'wg_description ';
			$queryStr .=  ') VALUES (';
			$queryStr .=  '?, ?, ?, ?';
			$queryStr .=  ')';
			$this->execStatement($queryStr, array($id, $data, $name, $desc));	
		}
				
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
}
?>
