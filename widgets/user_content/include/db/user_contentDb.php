<?php
/**
 * DBクラス
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    ユーザ作成コンテンツ
 * @author     株式会社 毎日メディアサービス
 * @copyright  Copyright 2010 株式会社 毎日メディアサービス.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: user_contentDb.php 3059 2010-04-23 06:23:11Z fishbone $
 * @link       http://www.m-media.co.jp
 */
require_once($gEnvManager->getDbPath() . '/baseDb.php');

class user_contentDb extends BaseDb
{
	/**
	 * タブ定義をすべて取得(一般用)
	 *
	 * @param string	$lang				言語
	 * @param int       $groupId			グループID
	 * @param function	$callback			コールバック関数
	 * @return 			なし
	 */
	function getAllVisibleTabs($lang, $groupId, $callback)
	{
		$queryStr = 'SELECT * FROM user_content_tab ';
		$queryStr .=  'WHERE ub_deleted = false ';		// 削除されていない
		$queryStr .=    'AND ub_visible = true ';		// 表示中
		$queryStr .=    'AND ub_language_id = ? ';
		$queryStr .=    'AND ub_group_id = ? ';
		$queryStr .=  'ORDER BY ub_index';
		$this->selectLoop($queryStr, array($lang, $groupId), $callback);
	}
	/**
	 * タブ定義一覧を取得(管理用)
	 *
	 * @param string	$lang				言語
	 * @param function	$callback			コールバック関数
	 * @return 			なし
	 */
	function getAllTabs($lang, $callback)
	{
		$queryStr = 'SELECT * FROM user_content_tab LEFT JOIN _login_user ON ub_create_user_id = lu_id AND lu_deleted = false ';
		$queryStr .=  'WHERE ub_deleted = false ';		// 削除されていない
		$queryStr .=    'AND ub_language_id = ? ';
		$queryStr .=  'ORDER BY ub_group_id, ub_index';
		$this->selectLoop($queryStr, array($lang), $callback);
	}
	/**
	 * タブ項目をシリアル番号で取得
	 *
	 * @param string	$serial				シリアル番号
	 * @param array     $row				レコード
	 * @return bool							取得 = true, 取得なし= false
	 */
	function getTabBySerial($serial, &$row)
	{
		$queryStr  = 'SELECT * FROM user_content_tab LEFT JOIN _login_user ON ub_create_user_id = lu_id AND lu_deleted = false ';
		$queryStr .=   'WHERE ub_serial = ? ';
		$ret = $this->selectRecord($queryStr, array($serial), $row);
		return $ret;
	}
	/**
	 * タブ項目を識別IDで取得
	 *
	 * @param string	$lang				言語
	 * @param string	$id					識別ID
	 * @param array     $row				レコード
	 * @return bool							取得 = true, 取得なし= false
	 */
	function getTabById($lang, $id, &$row)
	{
		$queryStr = 'SELECT * FROM user_content_tab ';
		$queryStr .=  'WHERE ub_deleted = false ';
		$queryStr .=  'AND ub_id = ? ';
		$queryStr .=  'AND ub_language_id = ? ';
		$ret = $this->selectRecord($queryStr, array($id, $lang), $row);
		return $ret;
	}
	/**
	 * 最大表示順を取得
	 *
	 * @return int					最大表示順
	 */
	function getMaxTabIndex()
	{
		$queryStr = 'SELECT max(ub_index) as mindex FROM user_content_tab ';
		$queryStr .=  'WHERE ub_deleted = false ';
		$ret = $this->selectRecord($queryStr, array(), $row);
		if ($ret){
			$index = $row['mindex'];
		} else {
			$index = 0;
		}
		return $index;
	}
	/**
	 * タブ情報を更新
	 *
	 * @param string $serial	シリアル番号(0のときは新規登録)
	 * @param string $lang		言語ID
	 * @param string $id		タブ識別キー
	 * @param string $name		名前
	 * @param string $html		テンプレートHTML
	 * @param int $index		表示順
	 * @param bool $visible		表示制御
	 * @param string $useItemId	使用しているコンテンツ項目ID
	 * @param int $groupId		グループID
	 * @param int $newSerial	新規シリアル番号
	 * @return					true = 正常、false=異常
	 */
	function updateTab($serial, $lang, $id, $name, $html, $index, $visible, $useItemId, $groupId, &$newSerial)
	{
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $this->gEnv->getCurrentUserId();	// 現在のユーザ
		$limited = false;		// ユーザ制限
		
		// トランザクション開始
		$this->startTransaction();
		
		// 前レコードの削除状態チェック
		$historyIndex = 0;
		$desc = '';
		if (empty($serial)){		// 新規登録のとき
			$queryStr = 'SELECT * FROM user_content_tab ';
			$queryStr .=  'WHERE ub_id = ? ';
			$queryStr .=     'AND ub_language_id = ? ';
			$queryStr .=  'ORDER BY ub_history_index DESC ';
			$ret = $this->selectRecord($queryStr, array($id, $lang), $row);
			if ($ret){
				if (!$row['ub_deleted']){		// レコード存在していれば終了
					$this->endTransaction();
					return false;
				}
				$historyIndex = $row['ub_history_index'] + 1;
				$desc = $row['ub_description'];
				$limited = $row['ub_user_limited'];
			}
		} else {		// 更新のとき
			// 指定のシリアルNoのレコードが削除状態でないかチェック
			$queryStr  = 'SELECT * FROM user_content_tab ';
			$queryStr .=   'WHERE ub_serial = ? ';
			$ret = $this->selectRecord($queryStr, array($serial), $row);
			if ($ret){		// 既に登録レコードがあるとき
				if ($row['ub_deleted']){		// レコードが削除されていれば終了
					$this->endTransaction();
					return false;
				}
				$historyIndex = $row['ub_history_index'] + 1;
				$desc = $row['ub_description'];
				$limited = $row['ub_user_limited'];
				
				// 識別IDと言語の変更は不可
				$id = $row['ub_id'];
				$lang = $row['ub_language_id'];
			} else {		// 存在しない場合は終了
				$this->endTransaction();
				return false;
			}
			
			// 古いレコードを削除
			$queryStr  = 'UPDATE user_content_tab ';
			$queryStr .=   'SET ub_deleted = true, ';	// 削除
			$queryStr .=     'ub_update_user_id = ?, ';
			$queryStr .=     'ub_update_dt = ? ';
			$queryStr .=   'WHERE ub_serial = ?';
			$ret = $this->execStatement($queryStr, array($userId, $now, $serial));
			if (!$ret){
				$this->endTransaction();
				return false;
			}
		}
		
		// データを追加
		$queryStr = 'INSERT INTO user_content_tab ';
		$queryStr .=  '(ub_id, ub_language_id, ub_history_index, ub_name, ub_description, ub_template_html, ub_use_item_id, ub_index, ub_visible, ub_user_limited, ub_group_id, ub_create_user_id, ub_create_dt) ';
		$queryStr .=  'VALUES ';
		$queryStr .=  '(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
		$this->execStatement($queryStr, array($id, $lang, $historyIndex, $name, $desc, $html, $useItemId, $index, intval($visible), intval($limited), intval($groupId), $userId, $now));
		
		// 新規のシリアル番号取得
		$queryStr = 'SELECT MAX(ub_serial) AS ns FROM user_content_tab ';
		$ret = $this->selectRecord($queryStr, array(), $row);
		if ($ret) $newSerial = $row['ns'];
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * タブ情報の削除
	 *
	 * @param array $serial			シリアルNo
	 * @return						true=成功、false=失敗
	 */
	function delTab($serial)
	{
		$now = date("Y/m/d H:i:s");	// 現在日時
		$user = $this->gEnv->getCurrentUserId();	// 現在のユーザ
		
		if (!is_array($serial) || count($serial) <= 0) return true;
		
		// トランザクション開始
		$this->startTransaction();
		
		// 指定のシリアルNoのレコードが削除状態でないかチェック
		for ($i = 0; $i < count($serial); $i++){
			$queryStr  = 'SELECT * FROM user_content_tab ';
			$queryStr .=   'WHERE ub_deleted = false ';		// 未削除
			$queryStr .=     'AND ub_serial = ? ';
			$ret = $this->isRecordExists($queryStr, array($serial[$i]));
			// 存在しない場合は、既に削除されたとして終了
			if (!$ret){
				$this->endTransaction();
				return false;
			}
		}
		
		// レコードを削除
		$queryStr  = 'UPDATE user_content_tab ';
		$queryStr .=   'SET ub_deleted = true, ';	// 削除
		$queryStr .=     'ub_update_user_id = ?, ';
		$queryStr .=     'ub_update_dt = ? ';
		$queryStr .=   'WHERE ub_serial in (' . implode($serial, ',') . ') ';
		$this->execStatement($queryStr, array($user, $now));
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * コンテンツ項目定義一覧を取得(管理用)
	 *
	 * @param function	$callback			コールバック関数
	 * @return 			なし
	 */
	function getAllItems($callback)
	{
		$queryStr = 'SELECT * FROM user_content_item LEFT JOIN _login_user ON ui_create_user_id = lu_id AND lu_deleted = false ';
		$queryStr .=  'WHERE ui_deleted = false ';		// 削除されていない
		$queryStr .=  'ORDER BY ui_index, ui_id';
		$this->selectLoop($queryStr, array(), $callback);
	}
	/**
	 * コンテンツ項目定義一覧を取得
	 *
	 * @param array  $rows			取得レコード
	 * @return						true=取得、false=取得せず
	 */
	function getAllContentItems(&$rows)
	{
		$queryStr = 'SELECT * FROM user_content_item ';
		$queryStr .=  'WHERE ui_deleted = false ';		// 削除されていない
		$queryStr .=  'ORDER BY ui_index, ui_id';
		$retValue = $this->selectRecords($queryStr, array(), $rows);
		return $retValue;
	}
	/**
	 * コンテンツ項目を識別IDで取得
	 *
	 * @param string	$id					識別ID
	 * @param array     $row				レコード
	 * @return bool							取得 = true, 取得なし= false
	 */
	function getItemById($id, &$row)
	{
		$queryStr = 'SELECT * FROM user_content_item ';
		$queryStr .=  'WHERE ui_deleted = false ';
		$queryStr .=  'AND ui_id = ? ';
		$ret = $this->selectRecord($queryStr, array($id), $row);
		return $ret;
	}
	/**
	 * コンテンツ項目をシリアル番号で取得
	 *
	 * @param string	$serial				シリアル番号
	 * @param array     $row				レコード
	 * @return bool							取得 = true, 取得なし= false
	 */
	function getItemBySerial($serial, &$row)
	{
		$queryStr  = 'SELECT * FROM user_content_item LEFT JOIN _login_user ON ui_create_user_id = lu_id AND lu_deleted = false ';
		$queryStr .=   'WHERE ui_serial = ? ';
		$ret = $this->selectRecord($queryStr, array($serial), $row);
		return $ret;
	}
	/**
	 * コンテンツ項目情報を更新
	 *
	 * @param string $serial	シリアル番号(0のときは新規登録)
	 * @param string $id		コンテンツ項目識別キー
	 * @param string $name		名前
	 * @param string $desc		説明
	 * @param int $type		コンテンツ項目タイプ
	 * @param string $key		外部参照キー
	 * @param int $newSerial	新規シリアル番号
	 * @return					true = 正常、false=異常
	 */
	function updateItem($serial, $id, $name, $desc, $type, $key, &$newSerial)
	{
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $this->gEnv->getCurrentUserId();	// 現在のユーザ
		
		// トランザクション開始
		$this->startTransaction();
		
		// 前レコードの削除状態チェック
		$historyIndex = 0;
		if (empty($serial)){		// 新規登録のとき
			$queryStr = 'SELECT * FROM user_content_item ';
			$queryStr .=  'WHERE ui_id = ? ';
			$queryStr .=  'ORDER BY ui_history_index DESC ';
			$ret = $this->selectRecord($queryStr, array($id), $row);
			if ($ret){
				if (!$row['ui_deleted']){		// レコード存在していれば終了
					$this->endTransaction();
					return false;
				}
				$historyIndex = $row['ui_history_index'] + 1;
			}
		} else {		// 更新のとき
			// 指定のシリアルNoのレコードが削除状態でないかチェック
			$queryStr  = 'SELECT * FROM user_content_item ';
			$queryStr .=   'WHERE ui_serial = ? ';
			$ret = $this->selectRecord($queryStr, array($serial), $row);
			if ($ret){		// 既に登録レコードがあるとき
				if ($row['ui_deleted']){		// レコードが削除されていれば終了
					$this->endTransaction();
					return false;
				}
				$historyIndex = $row['ui_history_index'] + 1;
				
				// 識別IDと言語の変更は不可
				$id = $row['ui_id'];
			} else {		// 存在しない場合は終了
				$this->endTransaction();
				return false;
			}
			
			// 古いレコードを削除
			$queryStr  = 'UPDATE user_content_item ';
			$queryStr .=   'SET ui_deleted = true, ';	// 削除
			$queryStr .=     'ui_update_user_id = ?, ';
			$queryStr .=     'ui_update_dt = ? ';
			$queryStr .=   'WHERE ui_serial = ?';
			$ret = $this->execStatement($queryStr, array($userId, $now, $serial));
			if (!$ret){
				$this->endTransaction();
				return false;
			}
		}
		
		// データを追加
		$queryStr = 'INSERT INTO user_content_item ';
		$queryStr .=  '(ui_id, ui_history_index, ui_name, ui_description, ui_type, ui_key, ui_create_user_id, ui_create_dt) ';
		$queryStr .=  'VALUES ';
		$queryStr .=  '(?, ?, ?, ?, ?, ?, ?, ?)';
		$this->execStatement($queryStr, array($id, $historyIndex, $name, $desc, $type, $key, $userId, $now));
		
		// 新規のシリアル番号取得
		$queryStr = 'SELECT MAX(ui_serial) AS ns FROM user_content_item ';
		$ret = $this->selectRecord($queryStr, array(), $row);
		if ($ret) $newSerial = $row['ns'];
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * コンテンツ項目情報の削除
	 *
	 * @param array $serial			シリアルNo
	 * @return						true=成功、false=失敗
	 */
	function delItem($serial)
	{
		$now = date("Y/m/d H:i:s");	// 現在日時
		$user = $this->gEnv->getCurrentUserId();	// 現在のユーザ
		
		if (!is_array($serial) || count($serial) <= 0) return true;
		
		// トランザクション開始
		$this->startTransaction();
		
		// 指定のシリアルNoのレコードが削除状態でないかチェック
		for ($i = 0; $i < count($serial); $i++){
			$queryStr  = 'SELECT * FROM user_content_item ';
			$queryStr .=   'WHERE ui_deleted = false ';		// 未削除
			$queryStr .=     'AND ui_serial = ? ';
			$ret = $this->isRecordExists($queryStr, array($serial[$i]));
			// 存在しない場合は、既に削除されたとして終了
			if (!$ret){
				$this->endTransaction();
				return false;
			}
		}
		
		// レコードを削除
		$queryStr  = 'UPDATE user_content_item ';
		$queryStr .=   'SET ui_deleted = true, ';	// 削除
		$queryStr .=     'ui_update_user_id = ?, ';
		$queryStr .=     'ui_update_dt = ? ';
		$queryStr .=   'WHERE ui_serial in (' . implode($serial, ',') . ') ';
		$this->execStatement($queryStr, array($user, $now));
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * ルーム一覧を取得(管理用)
	 *
	 * @param function	$callback			コールバック関数
	 * @return 			なし
	 */
	function getAllRooms($callback)
	{
		$queryStr = 'SELECT * FROM user_content_room LEFT JOIN _login_user ON ur_create_user_id = lu_id AND lu_deleted = false ';
		$queryStr .=  'WHERE ur_deleted = false ';		// 削除されていない
		$queryStr .=  'ORDER BY ur_id';
		$this->selectLoop($queryStr, array(), $callback);
	}
	/**
	 * ルーム情報を識別IDで取得(管理用)
	 *
	 * @param string	$id					識別ID
	 * @param array     $row				レコード
	 * @return bool							取得 = true, 取得なし= false
	 */
	function getRoomById($id, &$row)
	{
		$queryStr = 'SELECT * FROM user_content_room ';
		$queryStr .=  'WHERE ur_deleted = false ';
		$queryStr .=  'AND ur_id = ? ';
		$ret = $this->selectRecord($queryStr, array($id), $row);
		return $ret;
	}
	/**
	 * 公開可能なルームかどうか
	 *
	 * @param string	$id			識別ID
	 * @return bool					true=存在する、false=存在しない
	 */
	/*function isActiveRoom($id)
	{
		$queryStr = 'SELECT * FROM user_content_room ';
		$queryStr .=  'WHERE ur_deleted = false ';
		$queryStr .=  'AND ur_visible = true ';
		$queryStr .=  'AND ur_id = ? ';
		return $this->isRecordExists($queryStr, array($id));
	}*/
	/**
	 * ルーム情報をシリアル番号で取得
	 *
	 * @param string	$serial				シリアル番号
	 * @param array     $row				レコード
	 * @return bool							取得 = true, 取得なし= false
	 */
	function getRoomBySerial($serial, &$row)
	{
		$queryStr  = 'SELECT * FROM user_content_room LEFT JOIN _login_user ON ur_create_user_id = lu_id AND lu_deleted = false ';
		$queryStr .=   'WHERE ur_serial = ? ';
		$ret = $this->selectRecord($queryStr, array($serial), $row);
		return $ret;
	}
	/**
	 * ルーム情報を更新
	 *
	 * @param string $serial	シリアル番号(0のときは新規登録)
	 * @param string $id		コンテンツ項目識別キー
	 * @param string $name		名前
	 * @param int $groupId		所属グループID
	 * @param bool $visible		表示制御
	 * @param int $newSerial	新規シリアル番号
	 * @return					true = 正常、false=異常
	 */
	function updateRoom($serial, $id, $name, $groupId, $visible, &$newSerial)
	{
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $this->gEnv->getCurrentUserId();	// 現在のユーザ
		$contentUpdateDt = $this->gEnv->getInitValueOfTimestamp();
		
		// トランザクション開始
		$this->startTransaction();
		
		// 前レコードの削除状態チェック
		$historyIndex = 0;
		$desc = '';
		if (empty($serial)){		// 新規登録のとき
			$queryStr = 'SELECT * FROM user_content_room ';
			$queryStr .=  'WHERE ur_id = ? ';
			$queryStr .=  'ORDER BY ur_history_index DESC ';
			$ret = $this->selectRecord($queryStr, array($id), $row);
			if ($ret){
				if (!$row['ur_deleted']){		// レコード存在していれば終了
					$this->endTransaction();
					return false;
				}
				$historyIndex = $row['ur_history_index'] + 1;
			}
		} else {		// 更新のとき
			// 指定のシリアルNoのレコードが削除状態でないかチェック
			$queryStr  = 'SELECT * FROM user_content_room ';
			$queryStr .=   'WHERE ur_serial = ? ';
			$ret = $this->selectRecord($queryStr, array($serial), $row);
			if ($ret){		// 既に登録レコードがあるとき
				if ($row['ur_deleted']){		// レコードが削除されていれば終了
					$this->endTransaction();
					return false;
				}
				$historyIndex = $row['ur_history_index'] + 1;
				
				// 識別IDとコンテンツ更新日時の変更は不可
				$id = $row['ur_id'];
				$contentUpdateDt = $row['ur_content_update_dt'];
			} else {		// 存在しない場合は終了
				$this->endTransaction();
				return false;
			}
			
			// 古いレコードを削除
			$queryStr  = 'UPDATE user_content_room ';
			$queryStr .=   'SET ur_deleted = true, ';	// 削除
			$queryStr .=     'ur_update_user_id = ?, ';
			$queryStr .=     'ur_update_dt = ? ';
			$queryStr .=   'WHERE ur_serial = ?';
			$ret = $this->execStatement($queryStr, array($userId, $now, $serial));
			if (!$ret){
				$this->endTransaction();
				return false;
			}
		}
		
		// データを追加
		$queryStr = 'INSERT INTO user_content_room ';
		$queryStr .=  '(ur_id, ur_history_index, ur_name, ur_group_id, ur_visible, ur_content_update_dt, ur_create_user_id, ur_create_dt) ';
		$queryStr .=  'VALUES ';
		$queryStr .=  '(?, ?, ?, ?, ?, ?, ?, ?)';
		$this->execStatement($queryStr, array($id, $historyIndex, $name, intval($groupId), intval($visible), $contentUpdateDt, $userId, $now));
		
		// 新規のシリアル番号取得
		$queryStr = 'SELECT MAX(ur_serial) AS ns FROM user_content_room ';
		$ret = $this->selectRecord($queryStr, array(), $row);
		if ($ret) $newSerial = $row['ns'];
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * ルーム情報のコンテンツ更新日時を更新
	 *
	 * @param string $roomId		ルームID
	 * @param timestamp	$dt			日時
	 * @return						true = 正常、false=異常
	 */
	function updateRoomContentUpdateDt($roomId, $dt)
	{
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $this->gEnv->getCurrentUserId();	// 現在のユーザ
		
		// トランザクション開始
		$this->startTransaction();
		
		$queryStr = 'SELECT * FROM user_content_room ';
		$queryStr .=  'WHERE ur_deleted = false ';
		$queryStr .=  'AND ur_id = ? ';
		$ret = $this->selectRecord($queryStr, array($roomId), $row);
		if (!$ret){
			$this->endTransaction();
			return false;
		}
		
		// コンテンツ更新日時を更新
		$queryStr  = 'UPDATE user_content_room ';
		$queryStr .=   'SET ur_content_update_dt = ?, ';
		$queryStr .=     'ur_update_user_id = ?, ';
		$queryStr .=     'ur_update_dt = ? ';
		$queryStr .=   'WHERE ur_serial = ?';
		$ret = $this->execStatement($queryStr, array($dt, $userId, $now, $row['ur_serial']));
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * ルーム情報の削除
	 *
	 * @param array $serial			シリアルNo
	 * @return						true=成功、false=失敗
	 */
	function delRoom($serial)
	{
		$now = date("Y/m/d H:i:s");	// 現在日時
		$user = $this->gEnv->getCurrentUserId();	// 現在のユーザ
		
		if (!is_array($serial) || count($serial) <= 0) return true;
		
		// トランザクション開始
		$this->startTransaction();
		
		// 指定のシリアルNoのレコードが削除状態でないかチェック
		for ($i = 0; $i < count($serial); $i++){
			$queryStr  = 'SELECT * FROM user_content_room ';
			$queryStr .=   'WHERE ur_deleted = false ';		// 未削除
			$queryStr .=     'AND ur_serial = ? ';
			$ret = $this->isRecordExists($queryStr, array($serial[$i]));
			// 存在しない場合は、既に削除されたとして終了
			if (!$ret){
				$this->endTransaction();
				return false;
			}
		}
		
		// レコードを削除
		$queryStr  = 'UPDATE user_content_room ';
		$queryStr .=   'SET ur_deleted = true, ';	// 削除
		$queryStr .=     'ur_update_user_id = ?, ';
		$queryStr .=     'ur_update_dt = ? ';
		$queryStr .=   'WHERE ur_serial in (' . implode($serial, ',') . ') ';
		$this->execStatement($queryStr, array($user, $now));
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * コンテンツの更新
	 *
	 * @param string  $id			コンテンツID
	 * @param string  $roomId		ルームID
	 * @param string  $langId		言語ID
	 * @param string  $html			設定データ
	 * @param float   $number		数値データ
	 * @param bool    $visible		表示状態
	 * @param timestamp	$startDt	期間(開始日)
	 * @param timestamp	$endDt		期間(終了日)
	 * @param bool    $limited		ユーザ制限するかどうか
	 * @return bool					true = 成功、false = 失敗
	 */
	function updateContent($id, $roomId, $langId, $html, $number, $visible, $startDt, $endDt, $limited)
	{
		$now = date("Y/m/d H:i:s");	// 現在日時
		$user = $this->gEnv->getCurrentUserId();	// 現在のユーザ
		$contentType = '';
				
		// トランザクション開始
		$this->startTransaction();
		
		// 前レコードの削除状態チェック
		$historyIndex = 0;
		$queryStr = 'SELECT * FROM user_content ';
		$queryStr .=  'WHERE uc_id = ? ';
		$queryStr .=    'AND uc_room_id = ? ';
		$queryStr .=    'AND uc_language_id = ? ';
		$queryStr .=  'ORDER BY uc_history_index DESC ';
		$ret = $this->selectRecord($queryStr, array($id, $roomId, $langId), $row);
		if ($ret){
			$historyIndex = $row['uc_history_index'] + 1;
			
			if (!$row['uc_deleted']){		// レコード存在していれば削除
				// 古いレコードを削除
				$queryStr  = 'UPDATE user_content ';
				$queryStr .=   'SET uc_deleted = true, ';	// 削除
				$queryStr .=     'uc_update_user_id = ?, ';
				$queryStr .=     'uc_update_dt = ? ';
				$queryStr .=   'WHERE uc_serial = ?';
				$this->execStatement($queryStr, array($user, $now, $row['uc_serial']));
			}
		}

		// 新規レコード追加
		$queryStr  = 'INSERT INTO user_content ';
		$queryStr .=   '(uc_id, ';
		$queryStr .=   'uc_room_id, ';
		$queryStr .=   'uc_language_id, ';
		$queryStr .=   'uc_history_index, ';
		$queryStr .=   'uc_data, ';
		$queryStr .=   'uc_data_search_num, ';
		$queryStr .=   'uc_visible, ';
		$queryStr .=   'uc_active_start_dt, ';
		$queryStr .=   'uc_active_end_dt, ';
		$queryStr .=   'uc_user_limited, ';
		$queryStr .=   'uc_create_user_id, ';
		$queryStr .=   'uc_create_dt) ';
		$queryStr .=   'VALUES ';
		$queryStr .=   '(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
		$this->execStatement($queryStr, array($id, $roomId, $langId, $historyIndex, $html, $number, 
							intval($visible), $startDt, $endDt, intval($limited), $user, $now));
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * コンテンツをコンテンツIDで取得
	 *
	 * @param string  $id			コンテンツID
	 * @param string  $roomId		ルームID
	 * @param string  $langId		言語ID
	 * @param array   $row			レコード
	 * @return bool					取得 = true, 取得なし= false
	 */
	function getContent($id, $roomId, $langId, &$row)
	{
		$queryStr  = 'SELECT * FROM user_content LEFT JOIN _login_user ON uc_create_user_id = lu_id AND lu_deleted = false ';
		$queryStr .=   'WHERE uc_deleted = false ';	// 削除されていない
		$queryStr .=     'AND uc_id = ? ';
		$queryStr .=     'AND uc_room_id = ? ';
		$queryStr .=     'AND uc_language_id = ? ';
		$ret = $this->selectRecord($queryStr, array($id, $roomId, $langId), $row);
		return $ret;
	}
	/**
	 * コンテンツ一覧を取得(管理用)
	 *
	 * @param string  $roomId		ルームID
	 * @param string  $langId		言語ID
	 * @param function	$callback			コールバック関数
	 * @return 			なし
	 */
	function getAllContents($roomId, $langId, $callback)
	{
		if (empty($roomId) || empty($langId)) return;

		$queryStr  = 'SELECT * FROM (user_content RIGHT JOIN user_content_item ON uc_id = ui_id AND ui_deleted = false AND uc_deleted = false AND uc_room_id = ? AND uc_language_id = ?) ';
		$queryStr .=   'LEFT JOIN _login_user ON uc_create_user_id = lu_id AND lu_deleted = false ';
		$queryStr .=   'WHERE ui_deleted = false ';
		$queryStr .=     'AND (uc_deleted IS NULL ';
		$queryStr .=     'OR (uc_deleted = false ';	// 削除されていない
		$queryStr .=     'AND uc_room_id = ? ';
		$queryStr .=     'AND uc_language_id = ?)) ';
		$queryStr .=  'ORDER BY ui_index, ui_id';
		$this->selectLoop($queryStr, array($roomId, $langId, $roomId, $langId), $callback);
	}
	/**
	 * ルームIDですべてのコンテンツを取得
	 *
	 * @param string  $roomId		ルームID
	 * @param string  $langId		言語ID
	 * @param array   $rows			取得レコード
	 * @return bool					取得 = true, 取得なし= false
	 */
	function getAllContentsByRoomId($roomId, $langId, &$rows)
	{
		if (empty($roomId) || empty($langId)) return false;
		
		$queryStr  = 'SELECT * FROM user_content LEFT JOIN user_content_item ON uc_id = ui_id AND ui_deleted = false ';
		$queryStr .=   'WHERE uc_deleted = false ';	// 削除されていない
		$queryStr .=     'AND uc_room_id = ? ';
		$queryStr .=     'AND uc_language_id = ? ';
		$retValue = $this->selectRecords($queryStr, array($roomId, $langId), $rows);
		return $retValue;
	}
	/**
	 * カテゴリ情報をシリアル番号で取得
	 *
	 * @param string	$serial				シリアル番号
	 * @param array     $row				レコード
	 * @return bool							取得 = true, 取得なし= false
	 */
	function getCategoryBySerial($serial, &$row)
	{
		$queryStr  = 'SELECT * FROM user_content_category LEFT JOIN _login_user ON ua_create_user_id = lu_id AND lu_deleted = false ';
		$queryStr .=   'WHERE ua_serial = ? ';
		$ret = $this->selectRecord($queryStr, array($serial), $row);
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
		$queryStr  = 'SELECT * FROM user_content_category LEFT JOIN _login_user ON ua_create_user_id = lu_id AND lu_deleted = false ';
		$queryStr .=   'WHERE ua_deleted = false ';		// 削除されていない
		$queryStr .=     'AND ua_item_id = ? ';
		$queryStr .=     'AND ua_language_id = ? ';
		$queryStr .=   'ORDER BY ua_index';
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
		$queryStr  = 'SELECT ua_id FROM user_content_category ';
		$queryStr .=   'WHERE ua_deleted = false ';		// 削除されていない
		$queryStr .=     'AND ua_item_id = ? ';
		$queryStr .=     'AND ua_language_id = ? ';
		$queryStr .=   'ORDER BY ua_index';
		$retValue = $this->selectRecords($queryStr, array(''/*カテゴリ情報のみ*/, $langId), $rows);
		if (!$retValue) return;
		
		// CASE文作成
		$categoryId = '';
		$caseStr = 'CASE ua_id ';
		for ($i = 0; $i < count($rows); $i++){
			$id = '\'' . addslashes($rows[$i]['ua_id']) . '\'';
			$caseStr .= 'WHEN ' . $id . ' THEN ' . $i . ' ';
			$categoryId .= $id . ',';
		}
		$caseStr .= 'END AS no,';
		$categoryId = rtrim($categoryId, ',');
		// タイトルを最後にする
		$caseStr .=   'CASE ua_item_id ';
		$caseStr .=     'WHEN \'\' THEN 1 ';
		$caseStr .=     'ELSE 0 ';
		$caseStr .=   'END AS type ';
		
		$queryStr  = 'SELECT *, ' . $caseStr . ' FROM user_content_category ';
		$queryStr .=   'WHERE ua_deleted = false ';		// 削除されていない
		$queryStr .=     'AND ua_language_id = ? ';
		$queryStr .=     'AND ua_id in (' . $categoryId . ') ';
		$queryStr .=   'ORDER BY no, type, ua_index';
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
		
		$queryStr  = 'SELECT * FROM user_content_category ';
		$queryStr .=   'WHERE ua_deleted = false ';		// 削除されていない
		$queryStr .=     'AND ua_id = ? ';
		$queryStr .=     'AND ua_item_id = ? ';
		$queryStr .=     'AND ua_language_id = ? ';
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
		
		$queryStr  = 'SELECT * FROM user_content_category ';
		$queryStr .=   'WHERE ua_deleted = false ';		// 削除されていない
		$queryStr .=     'AND ua_id = ? ';
		$queryStr .=     'AND ua_item_id != ? ';
		$queryStr .=     'AND ua_language_id = ? ';
		$queryStr .=   'ORDER BY ua_index';
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
			$queryStr  = 'SELECT * FROM user_content_category ';
			$queryStr .=   'WHERE ua_id = ? ';
			$queryStr .=     'AND ua_item_id = ? ';
			$queryStr .=     'AND ua_language_id = ? ';
			$queryStr .=   'ORDER BY ua_history_index DESC ';
			$ret = $this->selectRecord($queryStr, array($id, ''/*カテゴリタイトル*/, $langId), $row);
			if ($ret){
				if (!$row['ua_deleted']){		// レコード存在していれば終了
					$this->endTransaction();
					return false;
				}
				$historyIndex = $row['ua_history_index'] + 1;
			}
		} else {		// 更新のとき
			// 指定のシリアルNoのレコードが削除状態でないかチェック
			$queryStr  = 'SELECT * FROM user_content_category ';
			$queryStr .=   'WHERE ua_serial = ? ';
			$ret = $this->selectRecord($queryStr, array($serial), $row);
			if ($ret){		// 既に登録レコードがあるとき
				if ($row['ua_deleted']){		// レコードが削除されていれば終了
					$this->endTransaction();
					return false;
				}
				$historyIndex = $row['ua_history_index'] + 1;
				
				// 識別IDと言語の変更は不可
				$id = $row['ua_id'];
				$langId = $row['ua_language_id'];
			} else {		// 存在しない場合は終了
				$this->endTransaction();
				return false;
			}
			
			// 古いレコードを削除
			$queryStr  = 'UPDATE user_content_category ';
			$queryStr .=   'SET ua_deleted = true, ';	// 削除
			$queryStr .=     'ua_update_user_id = ?, ';
			$queryStr .=     'ua_update_dt = ? ';
			$queryStr .=   'WHERE ua_deleted = false ';		// 削除されていない
			$queryStr .=     'AND ua_id = ? ';
			$queryStr .=     'AND ua_language_id = ? ';
			$ret = $this->execStatement($queryStr, array($userId, $now, $id, $langId));
			if (!$ret){
				$this->endTransaction();
				return false;
			}
		}
		
		// カテゴリ種別を追加
		$queryStr = 'INSERT INTO user_content_category ';
		$queryStr .=  '(ua_id, ua_item_id, ua_language_id, ua_history_index, ua_name, ua_index, ua_create_user_id, ua_create_dt) ';
		$queryStr .=  'VALUES ';
		$queryStr .=  '(?, ?, ?, ?, ?, ?, ?, ?)';
		$this->execStatement($queryStr, array($id, ''/*カテゴリ種別*/, $langId, $historyIndex, $name, intval($index), $userId, $now));
		
		// カテゴリ項目を追加
		for ($i = 0; $i < count($itemArray); $i++){
			$line = $itemArray[$i];
			
			$queryStr = 'INSERT INTO user_content_category ';
			$queryStr .=  '(ua_id, ua_item_id, ua_language_id, ua_history_index, ua_name, ua_index, ua_create_user_id, ua_create_dt) ';
			$queryStr .=  'VALUES ';
			$queryStr .=  '(?, ?, ?, ?, ?, ?, ?, ?)';
			$this->execStatement($queryStr, array($id, $line['ua_item_id'], $langId, $historyIndex, $line['ua_name'], intval($line['ua_index']), $userId, $now));
		}
		
		// 新規のシリアル番号取得
		$queryStr = 'SELECT MAX(ua_serial) AS ns FROM user_content_category ';
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
			$queryStr  = 'SELECT * FROM user_content_category ';
			$queryStr .=   'WHERE ua_deleted = false ';		// 未削除
			$queryStr .=     'AND ua_serial = ? ';
			$ret = $this->selectRecord($queryStr, array($serial[$i]), $row);
			// 存在しない場合は、既に削除されたとして終了
			if (!$ret){
				$this->endTransaction();
				return false;
			}
			$line = array();
			$line['ua_id'] = $row['ua_id'];
			$line['ua_language_id'] = $row['ua_language_id'];
			$delLines[] = $line;
		}
		
		// レコードを削除
		for ($i = 0; $i < count($delLines); $i++){
			$queryStr  = 'UPDATE user_content_category ';
			$queryStr .=   'SET ua_deleted = true, ';	// 削除
			$queryStr .=     'ua_update_user_id = ?, ';
			$queryStr .=     'ua_update_dt = ? ';
			$queryStr .=   'WHERE ua_deleted = false ';		// 削除されていない
			$queryStr .=     'AND ua_id = ? ';
			$queryStr .=     'AND ua_language_id = ? ';
			$this->execStatement($queryStr, array($user, $now, $delLines[$i]['ua_id'], $delLines[$i]['ua_language_id']));
		}
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * ルームに対応するカテゴリの更新
	 *
	 * @param string $roomId			ルームID
	 * @param array $categoryValues		カテゴリ選択値
	 * @return					true = 正常、false=異常
	 */
	function updateRoomCategory($roomId, $categoryValues)
	{
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $this->gEnv->getCurrentUserId();	// 現在のユーザ
		
		// トランザクション開始
		$this->startTransaction();
		
		// 古いレコードを削除
		$queryStr = 'DELETE FROM user_content_room_category ';
		$queryStr .=  'WHERE um_room_id = ? ';
		$ret = $this->execStatement($queryStr, array($roomId));
		if (!$ret){
			$this->endTransaction();
			return false;
		}

		// データを追加
		$keys = array_keys($categoryValues);
		for ($i = 0; $i < count($keys); $i++){
			$queryStr = 'INSERT INTO user_content_room_category ';
			$queryStr .=  '(um_room_id, um_category_id, um_category_item_id) ';
			$queryStr .=  'VALUES ';
			$queryStr .=  '(?, ?, ?)';
			$this->execStatement($queryStr, array($roomId, $keys[$i], $categoryValues[$keys[$i]]));
		}
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * ルームに対応したカテゴリ情報を取得
	 *
	 * @param string  $roomId		ルームID
	 * @param array   $rows			取得レコード
	 * @return bool					取得 = true, 取得なし= false
	 */
	function getRoomCategory($roomId, &$rows)
	{
		if (empty($roomId)) return false;
		
		$queryStr  = 'SELECT * FROM user_content_room_category ';
		$queryStr .=   'WHERE um_room_id = ? ';
		$retValue = $this->selectRecords($queryStr, array($roomId), $rows);
		return $retValue;
	}
	/**
	 * ルームに対応するカテゴリの削除
	 *
	 * @param string $roomId			ルームID
	 * @return					true = 正常、false=異常
	 */
	function delRoomCategory($roomId)
	{
		// トランザクション開始
		$this->startTransaction();
		
		// 古いレコードを削除
		$queryStr = 'DELETE FROM user_content_room_category ';
		$queryStr .=  'WHERE um_room_id = ? ';
		$ret = $this->execStatement($queryStr, array($roomId));
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
}
?>
