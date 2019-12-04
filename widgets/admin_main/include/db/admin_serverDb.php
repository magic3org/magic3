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
 * @copyright  Copyright 2006-2009 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: admin_serverDb.php 2734 2009-12-25 05:51:56Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getDbPath() . '/baseDb.php');

class admin_serverDb extends BaseDb
{
	/**
	 * テナントサーバリスト取得
	 *
	 * @param int		$limit				取得する項目数
	 * @param int		$page				取得するページ(1～)
	 * @param function $callback	コールバック関数
	 * @return						なし
	 */
	function getAllServerList($limit, $page, $callback)
	{
		$offset = $limit * ($page -1);
		if ($offset < 0) $offset = 0;
		
		$queryStr  = 'SELECT * FROM _tenant_server ';
		$queryStr .=   'WHERE ts_deleted = false ';// 削除されていない
		$queryStr .=   'ORDER BY ts_name limit ' . $limit . ' offset ' . $offset;
		$this->selectLoop($queryStr, array(), $callback);
	}
	/**
	 * テナントサーバ総数取得
	 *
	 * @return int					総数
	 */
	function getAllSeverListCount()
	{
		$queryStr = 'SELECT * FROM _tenant_server ';
		$queryStr .=  'WHERE ts_deleted = false ';// 削除されていない
		return $this->selectRecordCount($queryStr, array());
	}
	/**
	 * テナントサーバ情報をシリアル番号で取得
	 *
	 * @param string	$serial				シリアル番号
	 * @param array     $row				レコード
	 * @return bool							取得 = true, 取得なし= false
	 */
	function getServerBySerial($serial, &$row)
	{
		$queryStr  = 'SELECT * FROM _tenant_server ';
		$queryStr .=   'WHERE ts_serial = ? ';
		$ret = $this->selectRecord($queryStr, array($serial), $row);
		return $ret;
	}
	/**
	 * テナントサーバ情報をサーバIDで取得
	 *
	 * @param string	$id					サーバID
	 * @param array     $row				レコード
	 * @return bool							取得 = true, 取得なし= false
	 */
	function getServerById($id, &$row)
	{
		$queryStr  = 'SELECT * FROM _tenant_server ';
		$queryStr .=   'WHERE ts_deleted = false ';// 削除されていない
		$queryStr .=     'AND ts_id = ? ';
		$ret = $this->selectRecord($queryStr, array($id), $row);
		return $ret;
	}
	/**
	 * テナントサーバ情報の更新
	 *
	 * @param int $serial			シリアル番号(0のときは新規追加)
	 * @param string $name			ユーザ名
	 * @param string $serverId		サーバID
	 * @param string $ip			サーバIPアドレス
	 * @param string $url			サーバURL
	 * @param string $dbDsn			DB接続情報
	 * @param string $dbAccount		DB接続アカウント
	 * @param string $dbPassword	DB接続パスワード
	 * @param string $canAccess		アクセス可能かどうか
	 * @param int    $newSerial	新規シリアル番号
	 * @return						true=成功、false=失敗
	 */
	function updateServer($serial, $name, $serverId, $ip, $url, $dbDsn, $dbAccount, $dbPassword, $canAccess, &$newSerial)
	{
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $this->gEnv->getCurrentUserId();	// 現在のユーザ
		$historyIndex = 0;		// 履歴番号
					
		// トランザクション開始
		$this->startTransaction();
		
		if (empty($serial)){		// 新規追加のとき
			// 新規IDを作成
			$id = 1;
			$queryStr = 'SELECT MAX(ts_id) AS ms FROM _tenant_server ';
			$ret = $this->selectRecord($queryStr, array(), $row);
			if ($ret) $id = $row['ms'] + 1;
		} else {
			// 指定のシリアルNoのレコードが削除状態でないかチェック
			$queryStr  = 'SELECT * FROM _tenant_server ';
			$queryStr .=   'WHERE ts_serial = ? ';
			$ret = $this->selectRecord($queryStr, array($serial), $row);
			if ($ret){		// 既に登録レコードがあるとき
				if ($row['ts_deleted']){		// レコードが削除されていれば終了
					$this->endTransaction();
					return false;
				}
				$historyIndex = $row['ts_history_index'] + 1;
				$id = $row['ts_id'];
			} else {		// 存在しない場合は終了
				$this->endTransaction();
				return false;
			}
		
			// 古いレコードを削除
			$queryStr  = 'UPDATE _tenant_server ';
			$queryStr .=   'SET ts_deleted = true, ';	// 削除
			$queryStr .=     'ts_update_user_id = ?, ';
			$queryStr .=     'ts_update_dt = ? ';
			$queryStr .=   'WHERE ts_serial = ?';
			$this->execStatement($queryStr, array($userId, $now, $serial));
		}

		// 新規レコード追加
		$queryStr  = 'INSERT INTO _tenant_server (';
		$queryStr .=   'ts_id, ';
		$queryStr .=   'ts_history_index, ';
		$queryStr .=   'ts_server_id, ';
		$queryStr .=   'ts_name, ';
		$queryStr .=   'ts_url, ';
		$queryStr .=   'ts_ip, ';
		$queryStr .=   'ts_auth_account, ';
		$queryStr .=   'ts_auth_password, ';
		$queryStr .=   'ts_db_connect_dsn, ';
		$queryStr .=   'ts_db_account, ';
		$queryStr .=   'ts_db_password, ';
		$queryStr .=   'ts_enable_access, ';
		$queryStr .=   'ts_create_user_id, ';
		$queryStr .=   'ts_create_dt ';
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
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?) ';
		$this->execStatement($queryStr, array($id, $historyIndex, $serverId, $name, $url, $ip, '', '', $dbDsn, $dbAccount, $dbPassword, $canAccess, $userId, $now));

		// 新規のシリアル番号取得
		$queryStr = 'SELECT MAX(ts_serial) AS ns FROM _tenant_server ';
		$ret = $this->selectRecord($queryStr, array(), $row);
		if ($ret) $newSerial = $row['ns'];
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * サーバの削除
	 *
	 * @param array $serial			シリアルNo
	 * @return						true=成功、false=失敗
	 */
	function delServerBySerial($serial)
	{
		// 引数のエラーチェック
		if (!is_array($serial)) return false;
		if (count($serial) <= 0) return true;
		
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $this->gEnv->getCurrentUserId();	// 現在のユーザ
		
		// トランザクション開始
		$this->startTransaction();
		
		// 指定のシリアルNoのレコードが削除状態でないかチェック
		for ($i = 0; $i < count($serial); $i++){
			$queryStr  = 'SELECT * FROM _tenant_server ';
			$queryStr .=   'WHERE ts_deleted = false ';		// 未削除
			$queryStr .=     'AND ts_serial = ? ';
			$ret = $this->isRecordExists($queryStr, array($serial[$i]));
			// 存在しない場合は、既に削除されたとして終了
			if (!$ret){
				$this->endTransaction();
				return false;
			}
		}
		
		// レコードを削除
		$queryStr  = 'UPDATE _tenant_server ';
		$queryStr .=   'SET ts_deleted = true, ';	// 削除
		$queryStr .=     'ts_update_user_id = ?, ';
		$queryStr .=     'ts_update_dt = ? ';
		$queryStr .=   'WHERE ts_serial in (' . implode(',', $serial) . ') ';
		$this->execStatement($queryStr, array($userId, $now));
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * サーバ名の存在チェック
	 *
	 * @param string  $name			ユーザ名
	 * @param int $serial			チェック対象から除くシリアル番号(0のときはすべてがチェック対象)
	 * @return bool					true=存在する、false=存在しない
	 */
	function isExistsServerName($name, $serial = 0)
	{
		$params = array();
		$queryStr  = 'SELECT * FROM _tenant_server ';
		$queryStr .=   'WHERE ts_deleted = false ';		// 未削除
		$queryStr .=     'AND ts_name = ? '; $params[] = $name;
		if (!empty($serial)){
			$queryStr .=     'AND ts_serial != ? ';
			$params[] = $serial;
		}
		return $this->isRecordExists($queryStr, $params);
	}
	/**
	 * サーバIDの存在チェック
	 *
	 * @param string  $id			サーバID
	 * @param int $serial			チェック対象から除くシリアル番号(0のときはすべてがチェック対象)
	 * @return bool					true=存在する、false=存在しない
	 */
	function isExistsServerId($id, $serial = 0)
	{
		$params = array();
		$queryStr  = 'SELECT * FROM _tenant_server ';
		$queryStr .=   'WHERE ts_deleted = false ';		// 未削除
		$queryStr .=     'AND ts_server_id = ? '; $params[] = $id;
		if (!empty($serial)){
			$queryStr .=     'AND ts_serial != ? ';
			$params[] = $serial;
		}
		return $this->isRecordExists($queryStr, $params);
	}
}
?>
