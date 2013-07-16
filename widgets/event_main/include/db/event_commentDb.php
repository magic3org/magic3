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
 * @version    SVN: $Id: event_commentDb.php 3982 2011-02-07 03:00:55Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getDbPath() . '/baseDb.php');

class event_commentDb extends BaseDb
{
	/**
	 * コメント項目一覧を取得(管理用)
	 *
	 * @param int		$limit				取得する項目数
	 * @param int		$page				取得するページ(1～)
	 * @param timestamp	$startDt			期間(開始日)
	 * @param timestamp	$endDt				期間(終了日)
	 * @param string	$keyword			検索キーワード
	 * @param string	$langId				言語
	 * @param function	$callback			コールバック関数
	 * @return 			なし
	 */
	function searchCommentItems($limit, $page, $startDt, $endDt, $keyword, $langId, $callback)
	{
		$offset = $limit * ($page -1);
		if ($offset < 0) $offset = 0;
		
		$params = array();
		$queryStr = 'SELECT * FROM event_comment LEFT JOIN _login_user ON eo_user_id = lu_id AND lu_deleted = false ';
		$queryStr .=   'LEFT JOIN event_entry ON eo_entry_id = ee_id AND ee_deleted = false ';
		$queryStr .=  'WHERE eo_language_id = ? '; $params[] = $langId;
		$queryStr .=    'AND eo_deleted = false ';		// 削除されていない

		// コメント内容を検索
		if (!empty($keyword)){
			// 「'"\」文字をエスケープ
			$keyword = addslashes($keyword);
			
			$queryStr .=    'AND (eo_name LIKE \'%' . $keyword . '%\' ';
			$queryStr .=    'OR eo_html LIKE \'%' . $keyword . '%\' ';
			$queryStr .=    'OR eo_url LIKE \'%' . $keyword . '%\' ';
			$queryStr .=    'OR eo_user_name LIKE \'%' . $keyword . '%\' ';
			$queryStr .=    'OR eo_email LIKE \'%' . $keyword . '%\') ';
		}
		
		// 日付範囲
		if (!empty($startDt)){
			$queryStr .=    'AND ? <= eo_regist_dt ';
			$params[] = $startDt;
		}
		if (!empty($endDt)){
			$queryStr .=    'AND eo_regist_dt < ? ';
			$params[] = $endDt;
		}
		$queryStr .=  'ORDER BY eo_regist_dt desc limit ' . $limit . ' offset ' . $offset;
		$this->selectLoop($queryStr, $params, $callback, null);
	}
	/**
	 * コメント項目数を取得(管理用)
	 *
	 * @param timestamp	$startDt			期間(開始日)
	 * @param timestamp	$endDt				期間(終了日)
	 * @param string	$keyword			検索キーワード
	 * @param string	$langId				言語
	 * @return 			なし
	 */
	function getCommentItemCount($startDt, $endDt, $keyword, $langId)
	{
		$params = array();
		$queryStr = 'SELECT * FROM event_comment LEFT JOIN _login_user ON eo_user_id = lu_id AND lu_deleted = false ';
		$queryStr .=   'LEFT JOIN event_entry ON eo_entry_id = ee_id AND ee_deleted = false ';
		$queryStr .=  'WHERE eo_language_id = ? '; $params[] = $langId;
		$queryStr .=    'AND eo_deleted = false ';		// 削除されていない

		// コメント内容を検索
		if (!empty($keyword)){
			// 「'"\」文字をエスケープ
			$keyword = addslashes($keyword);
			
			$queryStr .=    'AND (eo_name LIKE \'%' . $keyword . '%\' ';
			$queryStr .=    'OR eo_html LIKE \'%' . $keyword . '%\' ';
			$queryStr .=    'OR eo_url LIKE \'%' . $keyword . '%\' ';
			$queryStr .=    'OR eo_user_name LIKE \'%' . $keyword . '%\' ';
			$queryStr .=    'OR eo_email LIKE \'%' . $keyword . '%\') ';
		}
		
		// 日付範囲
		if (!empty($startDt)){
			$queryStr .=    'AND ? <= eo_regist_dt ';
			$params[] = $startDt;
		}
		if (!empty($endDt)){
			$queryStr .=    'AND eo_regist_dt < ? ';
			$params[] = $endDt;
		}
		return $this->selectRecordCount($queryStr, $params);
	}
	/**
	 * コメント項目の新規追加
	 *
	 * @param string  $id			エントリーID
	 * @param string  $langId		言語ID
	 * @param string  $title		題名
	 * @param string  $html			HTML
	 * @param string  $url			URL
	 * @param string  $name			ユーザ名(任意)
	 * @param string  $email		Eメール
	 * @param int     $regUserId	投稿者ユーザID
	 * @param timestamp $regDt		投稿日時
	 * @param int     $newSerial	新規シリアル番号
	 * @return bool					true = 成功、false = 失敗
	 */
	function addCommentItem($id, $langId, $title, $html, $url, $name, $email, $regUserId, $regDt, &$newSerial)
	{
		global $gEnvManager;
		
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $gEnvManager->getCurrentUserId();	// 現在のユーザ
			
		// トランザクション開始
		$this->startTransaction();
		
		// データを追加
		$queryStr = 'INSERT INTO event_comment ';
		$queryStr .=  '(eo_entry_id, eo_language_id, eo_user_id, eo_regist_dt, eo_name, eo_html, eo_url, eo_user_name, eo_email, eo_update_user_id, eo_update_dt) ';
		$queryStr .=  'VALUES ';
		$queryStr .=  '(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
		$this->execStatement($queryStr, array($id, $langId, $regUserId, $regDt, $title, $html, $url, $name, $email, $userId, $now));
		
		// 新規のシリアル番号取得
		$newSerial = 0;
		$queryStr = 'select max(eo_serial) as ns from event_comment ';
		$ret = $this->selectRecord($queryStr, array(), $row);
		if ($ret) $newSerial = $row['ns'];
			
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * コメント項目の更新
	 *
	 * @param int     $serial		シリアル番号
	 * @param string  $title		題名
	 * @param string  $html			HTML
	 * @param string  $url			URL
	 * @param string  $name			ユーザ名(任意)
	 * @param string  $email		Eメール
	 * @return bool					true = 成功、false = 失敗
	 */
	function updateCommentItem($serial, $title, $html, $url, $name, $email)
	{
		global $gEnvManager;
		
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $gEnvManager->getCurrentUserId();	// 現在のユーザ
						
		// トランザクション開始
		$this->startTransaction();
		
		// 指定のシリアルNoのレコードが削除状態でないかチェック
		$queryStr  = 'select * from event_comment ';
		$queryStr .=   'where eo_serial = ? ';
		$ret = $this->selectRecord($queryStr, array($serial), $row);
		if ($ret){		// 既に登録レコードがあるとき
			if ($row['eo_deleted']){		// レコードが削除されていれば終了
				$this->endTransaction();
				return false;
			}
		} else {		// 存在しない場合は終了
			$this->endTransaction();
			return false;
		}
		
		// データを更新
		$queryStr  = 'UPDATE event_comment ';
		$queryStr .=   'SET ';
		$queryStr .=     'eo_name = ?, ';
		$queryStr .=     'eo_html = ?, ';
		$queryStr .=     'eo_url = ?, ';
		$queryStr .=     'eo_user_name = ?, ';
		$queryStr .=     'eo_email = ?, ';
		$queryStr .=     'eo_update_user_id = ?, ';
		$queryStr .=     'eo_update_dt = ? ';
		$queryStr .=   'WHERE eo_serial = ?';
		$this->execStatement($queryStr, array($title, $html, $url, $name, $email, $userId, $now, $serial));
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * コメント項目をシリアル番号で取得
	 *
	 * @param string	$serial				シリアル番号
	 * @param array     $row				レコード
	 * @return bool							取得 = true, 取得なし= false
	 */
	function getCommentBySerial($serial, &$row)
	{
		$queryStr = 'SELECT *,reg.lu_name as update_user_name FROM event_comment LEFT JOIN _login_user ON eo_user_id = lu_id AND lu_deleted = false ';
		$queryStr .=   'LEFT JOIN _login_user as reg ON eo_update_user_id = reg.lu_id AND reg.lu_deleted = false ';
		$queryStr .=   'LEFT JOIN event_entry ON eo_entry_id = ee_id AND ee_deleted = false ';
		$queryStr .=   'WHERE eo_serial = ? ';
		$ret = $this->selectRecord($queryStr, array($serial), $row);
		return $ret;
	}
	/**
	 * コメント項目の削除
	 *
	 * @param array   $serial		シリアルNo
	 * @return						true=成功、false=失敗
	 */
	function delCommentItem($serial)
	{
		global $gEnvManager;
		
		// 引数のエラーチェック
		if (!is_array($serial)) return false;
		if (count($serial) <= 0) return true;
		
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $gEnvManager->getCurrentUserId();	// 現在のユーザ
		
		// トランザクション開始
		$this->startTransaction();
		
		for ($i = 0; $i < count($serial); $i++){
			$queryStr  = 'SELECT * FROM event_comment ';
			$queryStr .=   'WHERE eo_deleted = false ';		// 未削除
			$queryStr .=     'AND eo_serial = ? ';
			$ret = $this->selectRecord($queryStr, array($serial[$i]), $row);
			if ($ret){		// 既に登録レコードがあるとき			
				// レコードを削除
				$queryStr  = 'UPDATE event_comment ';
				$queryStr .=   'SET eo_deleted = true, ';	// 削除
				$queryStr .=     'eo_update_user_id = ?, ';
				$queryStr .=     'eo_update_dt = ? ';
				$queryStr .=   'WHERE eo_serial = ?';
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
	 * コメントを記事IDで取得
	 *
	 * @param string	$entryId			イベント記事ID
	 * @param string	$langId				言語ID
	 * @param array     $rows				レコード
	 * @return bool							取得 = true, 取得なし= false
	 */
	function getCommentByEntryId($entryId, $langId, &$rows)
	{
		$queryStr  = 'SELECT * FROM event_comment ';
		$queryStr .=   'WHERE eo_deleted = false ';	// 削除されていない
		$queryStr .=    'AND eo_entry_id = ? ';
		$queryStr .=   'AND eo_language_id = ? ';
		$queryStr .=   'ORDER BY eo_regist_dt';
		$ret = $this->selectRecords($queryStr, array($entryId, $langId), $rows);
		return $ret;
	}
	/**
	 * コメント数を記事IDで取得
	 *
	 * @param string	$entryId			イベント記事ID
	 * @param string	$langId				言語ID
	 * @return int							コメント数
	 */
	function getCommentCountByEntryId($entryId, $langId)
	{
		$queryStr  = 'SELECT * FROM event_comment ';
		$queryStr .=   'WHERE eo_deleted = false ';	// 削除されていない
		$queryStr .=    'AND eo_entry_id = ? ';
		$queryStr .=   'AND eo_language_id = ? ';
		return $this->selectRecordCount($queryStr, array($entryId, $langId));
	}
}
?>
