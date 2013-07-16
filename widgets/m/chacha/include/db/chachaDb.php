<?php
/**
 * DBクラス
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    マイクロブログ
 * @author     株式会社 毎日メディアサービス
 * @copyright  Copyright 2010 株式会社 毎日メディアサービス.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: chachaDb.php 3363 2010-07-10 05:12:31Z fishbone $
 * @link       http://www.m-media.co.jp
 */
require_once($gEnvManager->getDbPath() . '/baseDb.php');

class chachaDb extends BaseDb
{
	/**
	 * マイクロブログ定義値をすべて取得
	 *
	 * 掲示板IDが空のデフォルト値は常に読み込む
	 *
	 * @param array  $rows			レコード
	 * @param string $boardId		掲示板ID
	 * @return bool					1行以上取得 = true, 取得なし= false
	 */
	function getAllConfig(&$rows, $boardId = '')
	{
		$queryStr  = 'SELECT * FROM mblog_config ';
		$queryStr .=  'WHERE mc_board_id = \'\' ';
		$queryStr .=   'OR mc_board_id = ? ';
		$queryStr .=   'ORDER BY mc_board_id, mc_index';
		$retValue = $this->selectRecords($queryStr, array($boardId), $rows);
		return $retValue;
	}
	/**
	 * マイクロブログ定義値を更新
	 *
	 * @param string $key		キーとなる項目値
	 * @param string $value		値
	 * @param string $boardId		掲示板ID
	 * @return					true = 正常、false=異常
	 */
	function updateConfig($key, $value, $boardId = '')
	{
		// トランザクションスタート
		$this->startTransaction();
		
		$queryStr  = 'SELECT mc_value FROM mblog_config ';
		$queryStr .=   'WHERE mc_board_id = ? ';
		$queryStr .=     'AND mc_id = ? ';
		$ret = $this->selectRecord($queryStr, array($boardId, $key), $row);
		if ($ret){
			$queryStr  = 'UPDATE mblog_config ';
			$queryStr .=   'SET mc_value = ? ';
			$queryStr .=   'WHERE mc_board_id = ? ';
			$queryStr .=     'AND mc_id = ? ';
			$ret = $this->execStatement($queryStr, array($value, $boardId, $key));			
		} else {
			$queryStr  = 'INSERT INTO mblog_config (';
			$queryStr .=   'mc_board_id, ';
			$queryStr .=   'mc_id, ';
			$queryStr .=   'mc_value ';
			$queryStr .= ') VALUES (';
			$queryStr .=   '?, ?, ?';
			$queryStr .= ')';
			$ret = $this->execStatement($queryStr, array($boardId, $key, $value));	
		}
		// トランザクション終了
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * 端末IDから会員情報の取得
	 *
	 * @param string $deviceId		端末ID
	 * @param array  $row			取得データ
	 * @return						true=正常、false=異常
	 */
	function getMemberInfoByDeviceId($deviceId, &$row)
	{
		// 引数エラーチェック
		if (empty($deviceId)) return false;
		
		$queryStr  = 'SELECT * FROM mblog_member ';
		$queryStr .=   'WHERE mb_device_id = ? ';
		$queryStr .=     'AND mb_deleted = false ';		// 削除されていない
		$ret = $this->selectRecord($queryStr, array($deviceId), $row);
		return $ret;
	}
	/**
	 * 会員IDから会員情報の取得
	 *
	 * @param string $id			会員ID
	 * @param array  $row			取得データ
	 * @return						true=正常、false=異常
	 */
	function getMemberInfoById($id, &$row)
	{
		// 引数エラーチェック
		if (empty($id)) return false;
		
		$queryStr  = 'SELECT * FROM mblog_member ';
		$queryStr .=   'WHERE mb_id = ? ';
		$queryStr .=     'AND mb_deleted = false ';		// 削除されていない
		$ret = $this->selectRecord($queryStr, array($id), $row);
		return $ret;
	}
	/**
	 * 会員IDが存在するかチェック
	 *
	 * @param string $id	会員ID
	 * @return				true=存在する、false=存在しない
	 */
	function isExistsMemberId($id)
	{
		$queryStr = 'SELECT * FROM mblog_member ';
		$queryStr .=  'WHERE mb_id = ? ';
		return $this->isRecordExists($queryStr, array($id));
	}
	/**
	 * 名前が存在するかチェック
	 *
	 * @param string $name		名前
	 * @return					true=存在する、false=存在しない
	 */
	function isExistsMemberName($name)
	{
		$queryStr = 'SELECT * FROM mblog_member ';
		$queryStr .=  'WHERE mb_name = ? ';
		$queryStr .=    'AND mb_deleted = false';
		return $this->isRecordExists($queryStr, array($name));
	}
	/**
	 * Eメールが存在するかチェック
	 *
	 * @param string $email		Eメール
	 * @return					true=存在する、false=存在しない
	 */
	function isExistsMemberEmail($email)
	{
		$queryStr = 'SELECT * FROM mblog_member ';
		$queryStr .=  'WHERE mb_email = ? ';
		$queryStr .=    'AND mb_deleted = false';
		return $this->isRecordExists($queryStr, array($email));
	}
	/**
	 * 会員情報の新規追加
	 *
	 * @param string  $deviceId		端末ID
	 * @param string  $memberId		会員ID
	 * @param int     $userId		ユーザID
	 * @param string  $name			投稿者名
	 * @param string  $email		Eメールアドレス
	 * @param string  $url			URL
	 * @param string  $avatar		アバターファイル名
	 * @param bool    $showEmail	Eメールアドレスを公開するかどうか
	 * @return bool					true = 成功、false = 失敗
	 */
	function addMember($deviceId, $memberId, $userId, $name, $email, $url, $avatar, $showEmail)
	{
		$now = date("Y/m/d H:i:s");	// 現在日時
//		$userId = $this->gEnv->getCurrentUserId();	// 現在のユーザ
				
		// トランザクション開始
		$this->startTransaction();
		
		// 会員IDの存在をチェック
		if ($this->isExistsMemberId($memberId)){		// 会員IDが登録されているときは異常終了
			$this->endTransaction();
			return false;
		}
		
		// データを追加
		$queryStr  = 'INSERT INTO mblog_member ';
		$queryStr .=   '(';
		$queryStr .=   'mb_id, ';
		$queryStr .=   'mb_device_id, ';
		$queryStr .=   'mb_user_id, ';
		$queryStr .=   'mb_name, ';
		$queryStr .=   'mb_email, ';
		$queryStr .=   'mb_url, ';
		$queryStr .=   'mb_avatar, ';
		$queryStr .=   'mb_show_email, ';
		$queryStr .=   'mb_regist_dt, ';
		$queryStr .=   'mb_last_access_dt, ';
		$queryStr .=   'mb_create_user_id, ';
		$queryStr .=   'mb_create_dt) ';
		$queryStr .=  'VALUES ';
		$queryStr .=  '(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
		$this->execStatement($queryStr, array($memberId, $deviceId, $userId, $name, $email, $url, $avatar, intval($showEmail), $now, $now, $userId, $now));
			
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * 会員情報を更新
	 *
	 * @param string  $deviceId		端末ID
	 * @param string  $name			投稿者名
	 * @param string  $email		Eメールアドレス
	 * @param string  $url			URL
	 * @param string  $avatar		アバターファイル名
	 * @param bool    $showEmail	Eメールアドレスを公開するかどうか
	 * @param int $newSerial	新規シリアル番号
	 * @return					true = 正常、false=異常
	 */
	function updateMember($deviceId, $name, $email, $url, $avatar, $showEmail, &$newSerial)
	{
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $this->gEnv->getCurrentUserId();	// 現在のユーザ
		
		// 引数エラーチェック
		if (empty($deviceId)) return false;
		
		// トランザクション開始
		$this->startTransaction();

		// 指定の端末IDのレコードが削除状態でないかチェック
		$queryStr  = 'SELECT * FROM mblog_member ';
		$queryStr .=   'WHERE mb_device_id = ? ';
		$queryStr .=     'AND mb_deleted = false ';		// 削除されていない
		$ret = $this->selectRecord($queryStr, array($deviceId), $row);
		if (!$ret){		// 登録レコードが存在しない場合は終了
			$this->endTransaction();
			return false;
		}
		$historyIndex = $row['mb_history_index'] + 1;
		
		// 古いレコードを削除
		$queryStr  = 'UPDATE mblog_member ';
		$queryStr .=   'SET mb_deleted = true, ';	// 削除
		$queryStr .=     'mb_update_user_id = ?, ';
		$queryStr .=     'mb_update_dt = ? ';
		$queryStr .=   'WHERE mb_serial = ?';
		$ret = $this->execStatement($queryStr, array($userId, $now, $row['mb_serial']));
		if (!$ret){
			$this->endTransaction();
			return false;
		}
		
		// データを追加
		// 識別ID、会員No、登録日時の変更は不可
		$queryStr  = 'INSERT INTO mblog_member ';
		$queryStr .=   '(';
		$queryStr .=   'mb_id, ';
		$queryStr .=   'mb_history_index, ';
		$queryStr .=   'mb_device_id, ';
		$queryStr .=   'mb_user_id, ';
		$queryStr .=   'mb_name, ';
		$queryStr .=   'mb_email, ';
		$queryStr .=   'mb_url, ';
		$queryStr .=   'mb_avatar, ';
		$queryStr .=   'mb_show_email, ';
		$queryStr .=   'mb_regist_dt, ';
		$queryStr .=   'mb_last_access_dt, ';
		$queryStr .=   'mb_create_user_id, ';
		$queryStr .=   'mb_create_dt) ';
		$queryStr .=  'VALUES ';
		$queryStr .=  '(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
		$this->execStatement($queryStr, array($row['mb_id'], $historyIndex, $deviceId, $row['mb_user_id'], $name, $email, $url, $avatar, intval($showEmail),
												$row['mb_regist_dt'], $row['mb_last_access_dt'], $userId, $now));
		
		// 新規のシリアル番号取得
		$queryStr = 'SELECT MAX(mb_serial) AS ns FROM mblog_member ';
		$ret = $this->selectRecord($queryStr, array(), $row);
		if ($ret) $newSerial = $row['ns'];
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * スレッドIDが存在するかチェック
	 *
	 * @param string $id	スレッドID
	 * @return				true=存在する、false=存在しない
	 */
	function isExistsThreadId($id)
	{
		$queryStr = 'SELECT * FROM mblog_thread ';
		$queryStr .=  'WHERE mt_id = ? ';
		return $this->isRecordExists($queryStr, array($id));
	}
	/**
	 * スレッドの新規追加
	 *
	 * @param string  $boardId		掲示板ID
	 * @param array   $threadId		スレッドID
	 * @param string  $memberId		会員ID
	 * @param string  $subject		スレッド件名
	 * @param string  $message		投稿メッセージ
	 * @return bool					true = 成功、false = 失敗
	 */
	function addNewThread($boardId, $threadId, $memberId, $subject, $message)
	{
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $this->gEnv->getCurrentUserId();	// 現在のユーザ
		$accessLog = $this->gEnv->getCurrentAccessLogSerial();

		// 引数エラーチェック
		if (empty($threadId) || empty($memberId)) return false;
		
		// トランザクション開始
		$this->startTransaction();
		
		// スレッドIDの存在をチェック
		if ($this->isExistsThreadId($threadId)){		// スレッドIDが登録されているときは異常終了
			$this->endTransaction();
			return false;
		}
		// スレッド番号作成
		$queryStr = 'SELECT MAX(mt_no) AS mn FROM mblog_thread ';
		$ret = $this->selectRecord($queryStr, array(), $row);
		if ($ret){
			$threadNo = $row['mn'] + 1;
		} else {
			$threadNo = 1;
		}
		$queryStr = 'SELECT MAX(mt_update_no) AS mn FROM mblog_thread ';
		$ret = $this->selectRecord($queryStr, array(), $row);
		if ($ret){
			$threadUpdateNo = $row['mn'] + 1;
		} else {
			$threadUpdateNo = 1;
		}
		
		// データを追加
		$queryStr  = 'INSERT INTO mblog_thread ';
		$queryStr .=   '(';
		$queryStr .=   'mt_board_id, ';
		$queryStr .=   'mt_id, ';
		$queryStr .=   'mt_no, ';
		$queryStr .=   'mt_update_no, ';
		$queryStr .=   'mt_subject, ';
		$queryStr .=   'mt_message_count, ';
		$queryStr .=   'mt_dt, ';
		$queryStr .=   'mt_log_serial, ';
		$queryStr .=   'mt_create_user_id, ';
		$queryStr .=   'mt_create_dt) ';
		$queryStr .=  'VALUES ';
		$queryStr .=  '(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
		$this->execStatement($queryStr, array($boardId, $threadId, $threadNo, $threadUpdateNo, $subject, 1, $now, $accessLog, $userId, $now));
		
		$queryStr  = 'INSERT INTO mblog_thread_message ';
		$queryStr .=   '(';
		$queryStr .=   'mm_board_id, ';
		$queryStr .=   'mm_thread_id, ';
		$queryStr .=   'mm_index, ';
		$queryStr .=   'mm_message, ';
		$queryStr .=   'mm_regist_member_id, ';
		$queryStr .=   'mm_regist_dt, ';
		$queryStr .=   'mm_log_serial, ';
		$queryStr .=   'mm_create_user_id, ';
		$queryStr .=   'mm_create_dt) ';
		$queryStr .=  'VALUES ';
		$queryStr .=  '(?, ?, ?, ?, ?, ?, ?, ?, ?)';
		$this->execStatement($queryStr, array($boardId, $threadId, 1, $message, $memberId, $now, $accessLog, $userId, $now));
			
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * 返信メッセージの追加
	 *
	 * @param string  $boardId		掲示板ID
	 * @param array   $threadId		スレッドID
	 * @param string  $memberId		会員ID
	 * @param string  $message		投稿メッセージ
	 * @return bool					true = 成功、false = 失敗
	 */
	function addNewReply($boardId, $threadId, $memberId, $message)
	{
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $this->gEnv->getCurrentUserId();	// 現在のユーザ
		$accessLog = $this->gEnv->getCurrentAccessLogSerial();

		// 引数エラーチェック
		if (empty($threadId) || empty($memberId)) return false;
		
		// トランザクション開始
		$this->startTransaction();
		
		// スレッド情報取得
		$queryStr  = 'SELECT * FROM mblog_thread ';
		$queryStr .=   'WHERE mt_board_id = ? ';
		$queryStr .=     'AND mt_id = ? ';
		$queryStr .=     'AND mt_deleted = false ';		// 削除されていない
		$ret = $this->selectRecord($queryStr, array($boardId, $threadId), $row);
		if (!$ret){		// スレッドが登録されていないときは異常終了
			$this->endTransaction();
			return false;
		}
		$serial = $row['mt_serial'];
		$historyIndex = $row['mt_history_index'] + 1;
		$subject = $row['mt_subject'];
		$threadNo = $row['mt_no'];
		$messageCount = $row['mt_message_count'] + 1;

		// スレッド更新番号作成
		$queryStr = 'SELECT MAX(mt_update_no) AS mn FROM mblog_thread ';
		$ret = $this->selectRecord($queryStr, array(), $row);
		if ($ret){
			$threadUpdateNo = $row['mn'] + 1;
		} else {
			$threadUpdateNo = 1;
		}
		
		// 古いレコードを削除
		$queryStr  = 'UPDATE mblog_thread ';
		$queryStr .=   'SET mt_deleted = true, ';	// 削除
		$queryStr .=     'mt_update_user_id = ?, ';
		$queryStr .=     'mt_update_dt = ? ';
		$queryStr .=   'WHERE mt_serial = ?';
		$this->execStatement($queryStr, array($userId, $now, $serial));
		
		// データを追加
		$queryStr  = 'INSERT INTO mblog_thread ';
		$queryStr .=   '(';
		$queryStr .=   'mt_board_id, ';
		$queryStr .=   'mt_id, ';
		$queryStr .=   'mt_history_index, ';
		$queryStr .=   'mt_no, ';
		$queryStr .=   'mt_update_no, ';
		$queryStr .=   'mt_subject, ';
		$queryStr .=   'mt_message_count, ';
		$queryStr .=   'mt_dt, ';
		$queryStr .=   'mt_log_serial, ';
		$queryStr .=   'mt_create_user_id, ';
		$queryStr .=   'mt_create_dt) ';
		$queryStr .=  'VALUES ';
		$queryStr .=  '(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
		$this->execStatement($queryStr, array($boardId, $threadId, $historyIndex, $threadNo, $threadUpdateNo, $subject, $messageCount, $now, $accessLog, $userId, $now));
		
		$queryStr  = 'INSERT INTO mblog_thread_message ';
		$queryStr .=   '(';
		$queryStr .=   'mm_board_id, ';
		$queryStr .=   'mm_thread_id, ';
		$queryStr .=   'mm_index, ';
		$queryStr .=   'mm_message, ';
		$queryStr .=   'mm_regist_member_id, ';
		$queryStr .=   'mm_regist_dt, ';
		$queryStr .=   'mm_log_serial, ';
		$queryStr .=   'mm_create_user_id, ';
		$queryStr .=   'mm_create_dt) ';
		$queryStr .=  'VALUES ';
		$queryStr .=  '(?, ?, ?, ?, ?, ?, ?, ?, ?)';
		$this->execStatement($queryStr, array($boardId, $threadId, $messageCount, $message, $memberId, $now, $accessLog, $userId, $now));
			
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * 会員IDでスレッドを取得
	 *
	 * @param string    $boardId		掲示板ID
	 * @param string    $memberId		会員ID
	 * @param int		$limit			取得する項目数
	 * @param int		$page			取得するページ(1～)
	 * @param function	$callback		コールバック関数
	 * @param bool      $plusRecord		次のページ判断用に1レコード多く読み込むかどうか
	 * @return 			なし
	 */
	function getThreadByMemberId($boardId, $memberId, $limit, $page, $callback, $plusRecord = false)
	{
		$offset = $limit * ($page -1);
		if ($offset < 0) $offset = 0;
		
		// 次ページがあるか判断するために1レコード追加
		if ($plusRecord) $limit++;
		
		$queryStr = 'SELECT * FROM mblog_thread LEFT JOIN mblog_thread_message ON mt_board_id = mm_board_id AND mt_id = mm_thread_id AND mm_deleted = false AND mm_index = 1 ';
		$queryStr .=  'WHERE mt_board_id = ? ';
		$queryStr .=    'AND mt_deleted = false ';		// 削除されていない
		$queryStr .=    'AND mm_regist_member_id = ? ';
		$queryStr .=  'ORDER BY mt_dt DESC ';			// 投稿順に取得
		$queryStr .=  'LIMIT ' . $limit . ' OFFSET ' . $offset;
		$this->selectLoop($queryStr, array($boardId, $memberId), $callback);
	}
	/**
	 * 最新のスレッドメッセージを取得
	 *
	 * @param string    $boardId		掲示板ID
	 * @param int       $limit			取得数
	 * @param int		$page			取得するページ(1～)
	 * @param function	$callback		コールバック関数
	 * @param bool      $plusRecord		次のページ判断用に1レコード多く読み込むかどうか
	 * @return 			なし
	 */
	function getThread($boardId, $limit, $page, $callback, $plusRecord = false)
	{
		if ($limit < 0) $limit = 0;
		
		$offset = $limit * ($page -1);
		if ($offset < 0) $offset = 0;
		
		// 次ページがあるか判断するために1レコード追加
		if ($plusRecord) $limit++;
		
		$queryStr = 'SELECT * FROM mblog_thread LEFT JOIN mblog_thread_message ON mt_board_id = mm_board_id AND mt_id = mm_thread_id AND mm_deleted = false AND mm_index = 1 ';
		$queryStr .=  'LEFT JOIN mblog_member ON mm_regist_member_id = mb_id AND mb_deleted = false ';
		$queryStr .=  'WHERE mt_board_id = ? ';
		$queryStr .=    'AND mt_deleted = false ';		// 削除されていない
		//$queryStr .=  'ORDER BY mt_dt DESC ';
		$queryStr .=  'ORDER BY mt_update_no DESC ';
		$queryStr .=  'LIMIT ' . $limit . ' OFFSET ' . $offset;
		$this->selectLoop($queryStr, array($boardId), $callback);
	}
	/**
	 * スレッド情報の取得
	 *
	 * @param string $boardId		掲示板ID
	 * @param string $threadId		スレッドID
	 * @param array  $row			取得データ
	 * @return						true=正常、false=異常
	 */
	function getThreadInfo($boardId, $threadId, &$row)
	{
		$queryStr  = 'SELECT * FROM mblog_thread LEFT JOIN mblog_thread_message ON mt_board_id = mm_board_id AND mt_id = mm_thread_id AND mm_deleted = false AND mm_index = 1 ';
		$queryStr .=   'LEFT JOIN mblog_member ON mm_regist_member_id = mb_id AND mb_deleted = false ';
		$queryStr .=   'WHERE mt_board_id = ? ';
		$queryStr .=     'AND mt_id = ? ';
		$queryStr .=     'AND mt_deleted = false ';		// 削除されていない
		$ret = $this->selectRecord($queryStr, array($boardId, $threadId), $row);
		return $ret;
	}
	/**
	 * 返信メッセージを取得
	 *
	 * @param function	$callback		コールバック関数
	 * @param string    $boardId		掲示板ID
	 * @param array     $threadId		スレッドID
	 * @param int       $limit			取得数(0のときすべて)
	 * @param int		$page			取得するページ(1～)
	 * @param bool      $plusRecord		次のページ判断用に1レコード多く読み込むかどうか
	 * @return 			なし
	 */
	function getThreadReply($callback, $boardId, $threadId, $limit, $page, $plusRecord = false)
	{
		if ($limit < 0) $limit = 0;
		
		$offset = $limit * ($page -1);
		if ($offset < 0) $offset = 0;
		
		// 次ページがあるか判断するために1レコード追加
		if ($plusRecord) $limit++;
		
		$queryStr  = 'SELECT * FROM mblog_thread_message LEFT JOIN mblog_member ON mm_regist_member_id = mb_id AND mb_deleted = false ';
		$queryStr .=   'WHERE mm_board_id = ? ';
		$queryStr .=     'AND mm_thread_id = ? ';
		$queryStr .=     'AND mm_index > 1 ';		// 返信のみ
		if ($limit <= 0){
			$queryStr .=  'ORDER BY mm_index';
		} else {
			$queryStr .=  'ORDER BY mm_index LIMIT ' . intval($limit) . ' OFFSET ' . intval($offset);
		}
		$this->selectLoop($queryStr, array($boardId, $threadId), $callback);
	}
}
?>
