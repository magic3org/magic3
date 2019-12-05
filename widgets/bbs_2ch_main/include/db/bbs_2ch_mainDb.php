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
 * @copyright  Copyright 2006-2011 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: bbs_2ch_mainDb.php 4026 2011-03-10 07:40:49Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getDbPath() . '/baseDb.php');

class bbs_2ch_mainDb extends BaseDb
{
	/**
	 * BBS定義値をすべて取得
	 *
	 * @param array  $rows			レコード
	 * @param string $boardId		掲示板ID
	 * @return bool					1行以上取得 = true, 取得なし= false
	 */
	function getAllConfig(&$rows, $boardId = '')
	{
		$queryStr  = 'SELECT * FROM bbs_2ch_config ';
		$queryStr .=  'WHERE tg_board_id = ? ';
		$queryStr .=   'ORDER BY tg_index';
		$retValue = $this->selectRecords($queryStr, array($boardId), $rows);
		return $retValue;
	}
	/**
	 * BBS定義値を更新
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
		
		$queryStr  = 'SELECT tg_value FROM bbs_2ch_config ';
		$queryStr .=   'WHERE tg_board_id = ? ';
		$queryStr .=     'AND tg_id = ? ';
		$ret = $this->selectRecord($queryStr, array($boardId, $key), $row);
		if ($ret){
			$queryStr  = 'UPDATE bbs_2ch_config ';
			$queryStr .=   'SET tg_value = ? ';
			$queryStr .=   'WHERE tg_board_id = ? ';
			$queryStr .=     'AND tg_id = ? ';
			$ret = $this->execStatement($queryStr, array($value, $boardId, $key));			
		} else {
			$queryStr  = 'INSERT INTO bbs_2ch_config (';
			$queryStr .=   'tg_board_id, ';
			$queryStr .=   'tg_id, ';
			$queryStr .=   'tg_value ';
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
	 * スレッドの新規追加
	 *
	 * @param string  $boardId		掲示板ID
	 * @param string  $threadId		スレッドID
	 * @param string  $subject		スレッド件名
	 * @param string  $userName		投稿者名
	 * @param string  $email		Eメールアドレス
	 * @param string  $message		投稿メッセージ
	 * @return bool					true = 成功、false = 失敗
	 */
	function addNewThread($boardId, $threadId, $subject, $userName, $email, $message)
	{
		$now = date("Y/m/d H:i:s");	// 現在日時
		$user = $this->gEnv->getCurrentUserId();	// 現在のユーザ
		$accessLog = $this->gEnv->getCurrentAccessLogSerial();
				
		// トランザクション開始
		$this->startTransaction();
		
		// データを追加
		$queryStr  = 'INSERT INTO bbs_2ch_thread ';
		$queryStr .=   '(';
		$queryStr .=   'th_board_id, ';
		$queryStr .=   'th_id, ';
		$queryStr .=   'th_subject, ';
		$queryStr .=   'th_message_count, ';
		$queryStr .=   'th_dt, ';
		$queryStr .=   'th_log_serial, ';
		$queryStr .=   'th_create_user_id, ';
		$queryStr .=   'th_create_dt) ';
		$queryStr .=  'VALUES ';
		$queryStr .=  '(?, ?, ?, ?, ?, ?, ?, ?)';
		$this->execStatement($queryStr, array($boardId, $threadId, $subject, 1, $now, $accessLog, $user, $now));
		
		$queryStr  = 'INSERT INTO bbs_2ch_thread_message ';
		$queryStr .=   '(';
		$queryStr .=   'te_board_id, ';
		$queryStr .=   'te_thread_id, ';
		$queryStr .=   'te_index, ';
		$queryStr .=   'te_user_name, ';
		$queryStr .=   'te_email, ';
		$queryStr .=   'te_message, ';
		$queryStr .=   'te_regist_dt, ';
		$queryStr .=   'te_log_serial, ';
		$queryStr .=   'te_update_user_id, ';
		$queryStr .=   'te_update_dt) ';
		$queryStr .=  'VALUES ';
		$queryStr .=  '(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
		$this->execStatement($queryStr, array($boardId, $threadId, 1, $userName, $email, $message, $now, $accessLog, $user, $now));
			
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * スレッド件名を取得
	 *
	 * @param function	$callback		コールバック関数
	 * @param string    $boardId		掲示板ID
	 * @param int       $limit			取得数(-1=すべて取得)
	 * @return 			なし
	 */
	function getThread($callback, $boardId, $limit)
	{
		$queryStr = 'SELECT * FROM bbs_2ch_thread ';
		$queryStr .=  'WHERE th_board_id = ? ';
		$queryStr .=    'AND th_deleted = false ';		// 削除されていない
		if ($limit == -1){
			$queryStr .=  'ORDER BY th_dt DESC';
		} else {
			$queryStr .=  'ORDER BY th_dt DESC limit ' . intval($limit);
		}
		$this->selectLoop($queryStr, array($boardId), $callback, null);
	}
	/**
	 * スレッド件名を検索キーワードで取得
	 *
	 * @param function	$callback		コールバック関数
	 * @param string    $boardId		掲示板ID
	 * @param int       $limit			取得数(-1=すべて取得)
	 * @param string    $keyword		検索キーワード
	 * @return 			なし
	 */
	function getThreadByKeyword($callback, $boardId, $limit, $keyword)
	{
		$queryStr  = 'SELECT th_id,th_subject,th_message_count FROM bbs_2ch_thread_message LEFT JOIN bbs_2ch_thread ON te_board_id = th_board_id AND te_thread_id = th_id AND th_deleted = false ';
		$queryStr .=   'WHERE te_board_id = ? ';
		$queryStr .=     'AND te_deleted = false ';		// 削除されていない
		
		// 「'"\」文字をエスケープ
		$keyword = addslashes($keyword);
		$queryStr .=     'AND (te_user_name LIKE \'%' . $keyword . '%\' ';
		$queryStr .=     'OR te_email LIKE \'%' . $keyword . '%\' ';
		$queryStr .=     'OR te_message LIKE \'%' . $keyword . '%\') ';
			
		$queryStr .=   'GROUP BY th_id ';
		if ($limit == -1){
			$queryStr .=  'ORDER BY th_dt DESC';
		} else {
			$queryStr .=  'ORDER BY th_dt DESC limit ' . intval($limit);
		}
		$this->selectLoop($queryStr, array($boardId), $callback, null);
	}
	/**
	 * スレッドメッセージを取得
	 *
	 * @param function	$callback		コールバック関数
	 * @param string    $boardId		掲示板ID
	 * @param array     $threadId		スレッドID
	 * @param array     $minIndexArray	メッセージインデックス番号の最小値
	 * @return 			なし
	 */
	function getThreadMessage($callback, $boardId, $threadId, $minIndexArray)
	{
		// スレッドの指定がないときは終了
		if (empty($threadId)) return;
		
		// CASE文作成
		$threadCount = count($threadId);
		$caseStr = 'CASE te_thread_id ';
		$thread = '';
		for ($i = 0; $i < $threadCount; $i++){
			$threadIdStr = addslashes($threadId[$i]);
			$caseStr .= 'WHEN \'' . $threadIdStr . '\' THEN ' . $i . ' ';
			$thread .= '\'' . $threadIdStr . '\',';
		}
		$caseStr .= 'END AS no';
		$thread = rtrim($thread, ',');
		
		// スレッドのメッセージ取得条件作成
		$condStr = 'AND (';
		for ($i = 0; $i < $threadCount; $i++){
			if ($i > 0) $condStr .= 'OR ';
			$threadIdStr = addslashes($threadId[$i]);
			$condStr .= '(te_thread_id = \'' . $threadIdStr . '\' ';
			$condStr .= 'AND (te_index = 1 OR te_index >= ' . intval($minIndexArray[$i]) . ')) ';
		}
		$condStr .= ') ';
		
		$queryStr  = 'SELECT *, ' . $caseStr . ' FROM bbs_2ch_thread_message ';
		$queryStr .=   'WHERE te_board_id = ? ';
		$queryStr .= $condStr;
		$queryStr .=   'ORDER BY no, te_index';
		$this->selectLoop($queryStr, array($boardId), $callback, null);
	}
	/**
	 * スレッドメッセージを範囲で取得
	 *
	 * @param function	$callback		コールバック関数
	 * @param string    $boardId		掲示板ID
	 * @param array     $threadId		スレッドID
	 * @param int		$limit			取得する項目数(0のときすべて)
	 * @param int		$offset			取得開始位置(0～)
	 * @return 			なし
	 */
	function getThreadMessageByRange($callback, $boardId, $threadId, $limit, $offset)
	{
		$queryStr  = 'SELECT * FROM bbs_2ch_thread_message ';
		$queryStr .=   'WHERE te_board_id = ? ';
		$queryStr .=     'AND te_thread_id = ? ';
		if ($limit <= 0){
			$queryStr .=  'ORDER BY te_index';
		} else {
			$queryStr .=  'ORDER BY te_index limit ' . intval($limit) . ' offset ' . intval($offset);
		}
		$this->selectLoop($queryStr, array($boardId, $threadId), $callback);
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
		$queryStr  = 'SELECT * FROM bbs_2ch_thread ';
		$queryStr .=   'WHERE th_board_id = ? ';
		$queryStr .=     'AND th_id = ? ';
		$queryStr .=     'AND th_deleted = false ';		// 削除されていない
		$ret = $this->selectRecord($queryStr, array($boardId, $threadId), $row);
		return $ret;
	}
	/**
	 * 投稿文の追加
	 *
	 * @param string  $boardId		掲示板ID
	 * @param string  $threadId		スレッドID
	 * @param string  $userName		投稿者名
	 * @param string  $email		Eメールアドレス
	 * @param string  $message		投稿メッセージ
	 * @param bool    $updateDt		日付を更新するかどうか
	 * @param int     $newSerial	新規シリアル番号
	 * @return bool					true = 成功、false = 失敗
	 */
	function addMessage($boardId, $threadId, $userName, $email, $message, $updateDt, &$newSerial)
	{
		$now = date("Y/m/d H:i:s");	// 現在日時
		$user = $this->gEnv->getCurrentUserId();	// 現在のユーザ
		$accessLog = $this->gEnv->getCurrentAccessLogSerial();
				
		// トランザクション開始
		$this->startTransaction();
		
		// スレッドがあるかどうかチェック
		$queryStr  = 'SELECT * FROM bbs_2ch_thread ';
		$queryStr .=   'WHERE th_board_id = ? ';
		$queryStr .=     'AND th_id = ? ';
		$queryStr .=     'AND th_deleted = false ';		// 削除されていない
		$ret = $this->selectRecord($queryStr, array($boardId, $threadId), $row);
		if ($ret){
			$params = array();
			$messageCount = $row['th_message_count'] + 1;		// メッセージ数
			$queryStr  = 'UPDATE bbs_2ch_thread ';
			$queryStr .=   'SET th_message_count = ?, '; $params[] = $messageCount;
			if ($updateDt){
				$queryStr .=     'th_dt = ?, '; $params[] = $now;		// 日付を更新
			}
			$queryStr .=     'th_update_user_id = ?, '; $params[] = $user;
			$queryStr .=     'th_update_dt = ? '; $params[] = $now;
			$queryStr .=   'WHERE th_serial = ?'; $params[] = $row['th_serial'];
			$ret = $this->execStatement($queryStr, $params);			
		} else {		// 存在しない場合は終了
			$this->endTransaction();
			return false;
		}
		// 投稿文を追加
		$queryStr  = 'INSERT INTO bbs_2ch_thread_message ';
		$queryStr .=   '(';
		$queryStr .=   'te_board_id, ';
		$queryStr .=   'te_thread_id, ';
		$queryStr .=   'te_index, ';
		$queryStr .=   'te_user_name, ';
		$queryStr .=   'te_email, ';
		$queryStr .=   'te_message, ';
		$queryStr .=   'te_regist_dt, ';
		$queryStr .=   'te_log_serial, ';
		$queryStr .=   'te_update_user_id, ';
		$queryStr .=   'te_update_dt) ';
		$queryStr .=  'VALUES ';
		$queryStr .=  '(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
		$this->execStatement($queryStr, array($boardId, $threadId, $messageCount, $userName, $email, $message, $now, $accessLog, $user, $now));
			
		// 新規のシリアル番号取得
		$queryStr = 'SELECT MAX(te_serial) AS mx FROM bbs_2ch_thread_message ';
		$ret = $this->selectRecord($queryStr, array(), $row);
		if ($ret) $newSerial = $row['mx'];
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * 投稿メッセージを取得(管理用)
	 *
	 * @param string    $boardId		掲示板ID
	 * @param int       $limit			取得数
	 * @param int		$page			取得するページ(1～)
	 * @param function	$callback		コールバック関数
	 * @return 			なし
	 */
	function getMessage($boardId, $limit, $page, $callback)
	{
		if ($limit < 0) $limit = 0;
		
		$offset = $limit * ($page -1);
		if ($offset < 0) $offset = 0;
		
		$queryStr = 'SELECT * FROM bbs_2ch_thread_message LEFT JOIN bbs_2ch_thread ON te_board_id = th_board_id AND te_thread_id = th_id AND th_deleted = false ';
		$queryStr .=  'WHERE te_board_id = ? ';
		$queryStr .=    'AND te_deleted = false ';		// 削除されていない
		$queryStr .=  'ORDER BY th_dt DESC, te_index ';
		$queryStr .=  'LIMIT ' . $limit . ' OFFSET ' . $offset;
		$this->selectLoop($queryStr, array($boardId), $callback);
	}
	/**
	 * 投稿メッセージ数を取得(管理用)
	 *
	 * @param string    $boardId		掲示板ID
	 * @return int						項目数
	 */
	function getMessageCount($boardId)
	{
		$queryStr = 'SELECT * FROM bbs_2ch_thread_message LEFT JOIN bbs_2ch_thread ON te_board_id = th_board_id AND te_thread_id = th_id AND th_deleted = false ';
		$queryStr .=  'WHERE te_board_id = ? ';
		$queryStr .=    'AND te_deleted = false ';		// 削除されていない
		return $this->selectRecordCount($queryStr, array($boardId));
	}
	/**
	 * メッセージをシリアル番号で取得(管理用)
	 *
	 * @param string	$serial				シリアル番号
	 * @param array     $row				レコード
	 * @return bool							取得 = true, 取得なし= false
	 */
	function getMessageBySerial($serial, &$row)
	{
		$queryStr = 'SELECT * FROM bbs_2ch_thread_message LEFT JOIN bbs_2ch_thread ON te_board_id = th_board_id AND te_thread_id = th_id AND th_deleted = false ';
		$queryStr .=   'WHERE te_serial = ? ';
		$ret = $this->selectRecord($queryStr, array($serial), $row);
		return $ret;
	}
	/**
	 * メッセージ項目の削除
	 *
	 * @param array $serial			シリアルNo
	 * @return						true=成功、false=失敗
	 */
	function delMessage($serial)
	{
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $this->gEnv->getCurrentUserId();	// 現在のユーザ
		
		if (!is_array($serial) || count($serial) <= 0) return true;
		
		// トランザクション開始
		$this->startTransaction();
		
		// 指定のシリアルNoのレコードが削除状態でないかチェック
		$delThread = array();			// 削除するスレッドのID
		for ($i = 0; $i < count($serial); $i++){
			$queryStr  = 'SELECT * FROM bbs_2ch_thread_message ';
			$queryStr .=   'WHERE te_deleted = false ';		// 未削除
			$queryStr .=     'AND te_serial = ? ';
			$ret = $this->selectRecord($queryStr, array($serial[$i]), $row);
			
			// 存在しない場合は、既に削除されたとして終了
			if ($ret){
				$boardId = $row['te_board_id'];
				$index = $row['te_index'];
				if ($index == 1) $delThread[] = $row['te_thread_id'];
			} else {
				$this->endTransaction();
				return false;
			}
		}
		
		// レコードを削除
		$queryStr  = 'UPDATE bbs_2ch_thread_message ';
		$queryStr .=   'SET te_deleted = true, ';	// 削除
		$queryStr .=     'te_update_user_id = ?, ';
		$queryStr .=     'te_update_dt = ? ';
		$queryStr .=   'WHERE te_serial in (' . implode(',', $serial) . ') ';
		$this->execStatement($queryStr, array($userId, $now));
		
		// スレッド削除
		if (count($delThread) > 0){
			$delId = '';
			for ($i = 0; $i < count($delThread); $i++){
				$delId .= '\'' . addslashes($delThread[$i]) . '\',';
			}
			$delId = rtrim($delId, ',');
			$queryStr  = 'UPDATE bbs_2ch_thread ';
			$queryStr .=   'SET th_deleted = true, ';	// 削除
			$queryStr .=     'th_update_user_id = ?, ';
			$queryStr .=     'th_update_dt = ? ';
			$queryStr .=   'WHERE th_board_id = ? ';
			$queryStr .=     'AND th_id in (' . $delId . ') ';
			$queryStr .=     'AND th_deleted = false ';
			$this->execStatement($queryStr, array($userId, $now, $boardId));
			
			// 削除するスレッドに属するメッセージはすべて削除
			$queryStr  = 'UPDATE bbs_2ch_thread_message ';
			$queryStr .=   'SET te_deleted = true, ';	// 削除
			$queryStr .=     'te_update_user_id = ?, ';
			$queryStr .=     'te_update_dt = ? ';
			$queryStr .=   'WHERE te_board_id = ? ';
			$queryStr .=     'AND te_thread_id in (' . $delId . ') ';
			$queryStr .=     'AND te_deleted = false ';
			$this->execStatement($queryStr, array($userId, $now, $boardId));
		}

		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * メッセージ項目の更新
	 *
	 * @param int     $serial		シリアルNo
	 * @param string  $userName		投稿者名
	 * @param string  $email		Eメールアドレス
	 * @param string  $message		投稿メッセージ
	 * @return bool					true = 成功、false = 失敗
	 */
	function updateMessage($serial, $userName, $email, $message)
	{
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $this->gEnv->getCurrentUserId();	// 現在のユーザ
				
		// トランザクション開始
		$this->startTransaction();
		
		// メッセージを更新
		$queryStr  = 'UPDATE bbs_2ch_thread_message ';
		$queryStr .=   'SET te_user_name = ?, ';
		$queryStr .=     'te_email = ?, ';
		$queryStr .=     'te_message = ?, ';
		$queryStr .=     'te_update_user_id = ?, ';
		$queryStr .=     'te_update_dt = ? ';
		$queryStr .=   'WHERE te_serial = ?';
		$this->execStatement($queryStr, array($userName, $email, $message, $userId, $now, $serial));
			
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
}
?>
