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
 * @version    SVN: $Id: blog_commentDb.php 5240 2012-09-23 09:35:26Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getDbPath() . '/baseDb.php');

class blog_commentDb extends BaseDb
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
	 * @param string	$blogId				ブログID(未指定の場合はnull)
	 * @return 			なし
	 */
	function searchCommentItems($limit, $page, $startDt, $endDt, $keyword, $langId, $callback, $blogId = null)
	{
		$offset = $limit * ($page -1);
		if ($offset < 0) $offset = 0;
		
		$params = array();
		$queryStr = 'SELECT * FROM blog_comment LEFT JOIN _login_user ON bo_user_id = lu_id AND lu_deleted = false ';
		$queryStr .=   'LEFT JOIN blog_entry ON bo_entry_id = be_id AND be_deleted = false ';
		$queryStr .=  'WHERE bo_language_id = ? '; $params[] = $langId;
		$queryStr .=    'AND bo_deleted = false ';		// 削除されていない

		// コメント内容を検索
		if (!empty($keyword)){
			// 「'"\」文字をエスケープ
			$keyword = addslashes($keyword);
			
			$queryStr .=    'AND (bo_name LIKE \'%' . $keyword . '%\' ';
			$queryStr .=    'OR bo_html LIKE \'%' . $keyword . '%\' ';
			$queryStr .=    'OR bo_url LIKE \'%' . $keyword . '%\' ';
			$queryStr .=    'OR bo_user_name LIKE \'%' . $keyword . '%\' ';
			$queryStr .=    'OR bo_email LIKE \'%' . $keyword . '%\') ';
		}
		
		// 日付範囲
		if (!empty($startDt)){
			$queryStr .=    'AND ? <= bo_regist_dt ';
			$params[] = $startDt;
		}
		if (!empty($endDt)){
			$queryStr .=    'AND bo_regist_dt < ? ';
			$params[] = $endDt;
		}
		// ブログID
		if (isset($blogId)){
			$queryStr .=    'AND be_blog_id = ? ';
			$params[] = $blogId;
		}
		$queryStr .=  'ORDER BY bo_regist_dt desc limit ' . $limit . ' offset ' . $offset;
		$this->selectLoop($queryStr, $params, $callback, null);
	}
	/**
	 * コメント項目数を取得(管理用)
	 *
	 * @param timestamp	$startDt			期間(開始日)
	 * @param timestamp	$endDt				期間(終了日)
	 * @param string	$keyword			検索キーワード
	 * @param string	$langId				言語
	 * @param string	$blogId				ブログID(未指定の場合はnull)
	 * @return 			なし
	 */
	function getCommentItemCount($startDt, $endDt, $keyword, $langId, $blogId = null)
	{
		$params = array();
		$queryStr = 'SELECT * FROM blog_comment LEFT JOIN _login_user ON bo_user_id = lu_id AND lu_deleted = false ';
		$queryStr .=   'LEFT JOIN blog_entry ON bo_entry_id = be_id AND be_deleted = false ';
		$queryStr .=  'WHERE bo_language_id = ? '; $params[] = $langId;
		$queryStr .=    'AND bo_deleted = false ';		// 削除されていない

		// コメント内容を検索
		if (!empty($keyword)){
			// 「'"\」文字をエスケープ
			$keyword = addslashes($keyword);
			
			$queryStr .=    'AND (bo_name LIKE \'%' . $keyword . '%\' ';
			$queryStr .=    'OR bo_html LIKE \'%' . $keyword . '%\' ';
			$queryStr .=    'OR bo_url LIKE \'%' . $keyword . '%\' ';
			$queryStr .=    'OR bo_user_name LIKE \'%' . $keyword . '%\' ';
			$queryStr .=    'OR bo_email LIKE \'%' . $keyword . '%\') ';
		}
		
		// 日付範囲
		if (!empty($startDt)){
			$queryStr .=    'AND ? <= bo_regist_dt ';
			$params[] = $startDt;
		}
		if (!empty($endDt)){
			$queryStr .=    'AND bo_regist_dt < ? ';
			$params[] = $endDt;
		}
		// ブログID
		if (isset($blogId)){
			$queryStr .=    'AND be_blog_id = ? ';
			$params[] = $blogId;
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
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $this->gEnv->getCurrentUserId();	// 現在のユーザ
			
		// トランザクション開始
		$this->startTransaction();
		
		// コメントNoを決定する
		$queryStr  = 'SELECT MAX(bo_no) AS mid FROM blog_comment ';
		$queryStr .=   'WHERE bo_entry_id = ? ';
		$ret = $this->selectRecord($queryStr, array($id), $row);
		if ($ret){
			$commentNo = $row['mid'] + 1;
		} else {
			$commentNo = 1;
		}
			
		// データを追加
		$queryStr = 'INSERT INTO blog_comment ';
		$queryStr .=  '(bo_entry_id, bo_language_id, bo_no, bo_user_id, bo_regist_dt, bo_name, bo_html, bo_url, bo_user_name, bo_email, bo_update_user_id, bo_update_dt) ';
		$queryStr .=  'VALUES ';
		$queryStr .=  '(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
		$this->execStatement($queryStr, array($id, $langId, $commentNo, $regUserId, $regDt, $title, $html, $url, $name, $email, $userId, $now));
		
		// 新規のシリアル番号取得
		$newSerial = 0;
		$queryStr = 'select max(bo_serial) as ns from blog_comment ';
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
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $this->gEnv->getCurrentUserId();	// 現在のユーザ
						
		// トランザクション開始
		$this->startTransaction();
		
		// 指定のシリアルNoのレコードが削除状態でないかチェック
		$queryStr  = 'select * from blog_comment ';
		$queryStr .=   'where bo_serial = ? ';
		$ret = $this->selectRecord($queryStr, array($serial), $row);
		if ($ret){		// 既に登録レコードがあるとき
			if ($row['bo_deleted']){		// レコードが削除されていれば終了
				$this->endTransaction();
				return false;
			}
		} else {		// 存在しない場合は終了
			$this->endTransaction();
			return false;
		}
		
		// データを更新
		$queryStr  = 'UPDATE blog_comment ';
		$queryStr .=   'SET ';
		$queryStr .=     'bo_no = ?, ';
		$queryStr .=     'bo_name = ?, ';
		$queryStr .=     'bo_html = ?, ';
		$queryStr .=     'bo_url = ?, ';
		$queryStr .=     'bo_user_name = ?, ';
		$queryStr .=     'bo_email = ?, ';
		$queryStr .=     'bo_update_user_id = ?, ';
		$queryStr .=     'bo_update_dt = ? ';
		$queryStr .=   'WHERE bo_serial = ?';
		$this->execStatement($queryStr, array($row['bo_no'], $title, $html, $url, $name, $email, $userId, $now, $serial));
		
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
		$queryStr = 'SELECT *,reg.lu_name as update_user_name FROM blog_comment LEFT JOIN _login_user ON bo_user_id = lu_id AND lu_deleted = false ';
		$queryStr .=   'LEFT JOIN _login_user as reg ON bo_update_user_id = reg.lu_id AND reg.lu_deleted = false ';
		$queryStr .=   'LEFT JOIN blog_entry ON bo_entry_id = be_id AND be_deleted = false ';
		$queryStr .=   'WHERE bo_serial = ? ';
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
		// 引数のエラーチェック
		if (!is_array($serial)) return false;
		if (count($serial) <= 0) return true;
		
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $this->gEnv->getCurrentUserId();	// 現在のユーザ
		
		// トランザクション開始
		$this->startTransaction();
		
		for ($i = 0; $i < count($serial); $i++){
			$queryStr  = 'SELECT * FROM blog_comment ';
			$queryStr .=   'WHERE bo_deleted = false ';		// 未削除
			$queryStr .=     'AND bo_serial = ? ';
			$ret = $this->selectRecord($queryStr, array($serial[$i]), $row);
			if ($ret){		// 既に登録レコードがあるとき			
				// レコードを削除
				$queryStr  = 'UPDATE blog_comment ';
				$queryStr .=   'SET bo_deleted = true, ';	// 削除
				$queryStr .=     'bo_update_user_id = ?, ';
				$queryStr .=     'bo_update_dt = ? ';
				$queryStr .=   'WHERE bo_serial = ?';
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
	 * @param string	$entryId			ブログ記事ID
	 * @param string	$langId				言語ID
	 * @param array     $rows				レコード
	 * @return bool							取得 = true, 取得なし= false
	 */
	function getCommentByEntryId($entryId, $langId, &$rows)
	{
		$queryStr  = 'SELECT * FROM blog_comment LEFT JOIN _login_user ON bo_user_id = lu_id AND lu_deleted = false ';
		$queryStr .=   'WHERE bo_deleted = false ';	// 削除されていない
		$queryStr .=    'AND bo_entry_id = ? ';
		$queryStr .=   'AND bo_language_id = ? ';
		$queryStr .=   'ORDER BY bo_regist_dt';
		$ret = $this->selectRecords($queryStr, array($entryId, $langId), $rows);
		return $ret;
	}
	/**
	 * コメント数を記事IDで取得
	 *
	 * @param string	$entryId			ブログ記事ID
	 * @param string	$langId				言語ID
	 * @return int							コメント数
	 */
	function getCommentCountByEntryId($entryId, $langId)
	{
		$queryStr  = 'SELECT * FROM blog_comment ';
		$queryStr .=   'WHERE bo_deleted = false ';	// 削除されていない
		$queryStr .=    'AND bo_entry_id = ? ';
		$queryStr .=   'AND bo_language_id = ? ';
		return $this->selectRecordCount($queryStr, array($entryId, $langId));
	}
}
?>
