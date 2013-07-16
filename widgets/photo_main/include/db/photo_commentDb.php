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
 * @version    SVN: $Id: photo_commentDb.php 5612 2013-02-07 14:32:27Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getDbPath() . '/baseDb.php');

class photo_commentDb extends BaseDb
{
	/**
	 * 画像評価項目一覧を取得(管理用)
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
		$queryStr  = 'SELECT * FROM photo_rate LEFT JOIN _login_user ON hr_user_id = lu_id AND lu_deleted = false ';
		$queryStr .=   'LEFT JOIN photo ON hr_photo_id = ht_id AND hr_language_id = ht_language_id AND ht_deleted = false ';
		$queryStr .= 'WHERE hr_language_id = ? '; $params[] = $langId;
		$queryStr .=   'AND hr_deleted = false ';		// 削除されていない

		// コメント内容を検索
		if (!empty($keyword)){
			// 「'"\」文字をエスケープ
			$keyword = addslashes($keyword);
			
			$queryStr .=    'AND (hr_message LIKE \'%' . $keyword . '%\') ';
		}
		
		// 日付範囲
		if (!empty($startDt)){
			$queryStr .=    'AND ? <= hr_regist_dt ';
			$params[] = $startDt;
		}
		if (!empty($endDt)){
			$queryStr .=    'AND hr_regist_dt < ? ';
			$params[] = $endDt;
		}
		$queryStr .=  'ORDER BY hr_regist_dt desc limit ' . $limit . ' offset ' . $offset;
		$this->selectLoop($queryStr, $params, $callback, null);
	}
	/**
	 * 画像評価数を取得(管理用)
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
		$queryStr  = 'SELECT * FROM photo_rate LEFT JOIN _login_user ON hr_user_id = lu_id AND lu_deleted = false ';
		$queryStr .=   'LEFT JOIN photo ON hr_photo_id = ht_id AND hr_language_id = ht_language_id AND ht_deleted = false ';
		$queryStr .=  'WHERE hr_language_id = ? '; $params[] = $langId;
		$queryStr .=    'AND hr_deleted = false ';		// 削除されていない

		// コメント内容を検索
		if (!empty($keyword)){
			// 「'"\」文字をエスケープ
			$keyword = addslashes($keyword);
			
			$queryStr .=    'AND (hr_message LIKE \'%' . $keyword . '%\') ';
		}
		
		// 日付範囲
		if (!empty($startDt)){
			$queryStr .=    'AND ? <= hr_regist_dt ';
			$params[] = $startDt;
		}
		if (!empty($endDt)){
			$queryStr .=    'AND hr_regist_dt < ? ';
			$params[] = $endDt;
		}
		return $this->selectRecordCount($queryStr, $params);
	}
	/**
	 * 画像評価の新規追加
	 *
	 * @param int     $publicId		公開画像ID
	 * @param string  $langId		言語ID
	 * @param string  $clientId		クライアントID
	 * @param int     $rateValue	評価値
	 * @param string  $message		メッセージ
	 * @param float   $rateAverage	評価集計値
	 * @param int     $newSerial	新規シリアル番号
	 * @return bool					true = 成功、false = 失敗
	 */
	function addCommentItem($publicId, $langId, $clientId, $rateValue, $message, &$rateAverage, &$newSerial)
	{
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $this->gEnv->getCurrentUserId();	// 現在のユーザ
			
		// トランザクション開始
		$this->startTransaction();
		
		// 既に投稿済みのときは終了
		$ret = $this->isExistsComment($publicId, $clientId, $langId);
		if ($ret){
				$this->endTransaction();
				return false;
		}

		// 画像ID取得
		$queryStr  = 'SELECT * FROM photo ';
		$queryStr .=   'WHERE ht_deleted = false ';
		$queryStr .=     'AND ht_public_id = ? ';
		$queryStr .=     'AND ht_language_id = ? ';
		$ret = $this->selectRecord($queryStr, array($publicId, $langId), $row);
		if (!$ret){
				$this->endTransaction();
				return false;
		}
		$photoId = $row['ht_id'];
		$serial = $row['ht_serial'];

		// データを追加
		$queryStr  = 'INSERT INTO photo_rate ';
		$queryStr .=  '(hr_photo_id, hr_language_id, hr_client_id, hr_user_id, hr_regist_dt, hr_rate_value, hr_message, hr_update_user_id, hr_update_dt) ';
		$queryStr .=  'VALUES ';
		$queryStr .=  '(?, ?, ?, ?, ?, ?, ?, ?, ?)';
		$this->execStatement($queryStr, array($photoId, $langId, $clientId, $userId, $now, $rateValue, $message, $userId, $now));
		
		// 集計値を更新
		$queryStr  = 'SELECT AVG(hr_rate_value) AS av FROM photo_rate ';
		$queryStr .=   'WHERE hr_deleted = false ';		// 削除されていない
		$queryStr .=     'AND hr_photo_id = ? ';
		$queryStr .=     'AND hr_language_id = ? ';
		$ret = $this->selectRecord($queryStr, array($photoId, $langId), $row);
		if (!$ret){
				$this->endTransaction();
				return false;
		}
		$rateAverage = $row['av'];
		
		// データを更新
		$queryStr  = 'UPDATE photo ';
		$queryStr .=   'SET ';
		$queryStr .=     'ht_rate_average = ?, ';
		$queryStr .=     'ht_update_user_id = ?, ';
		$queryStr .=     'ht_update_dt = ? ';
		$queryStr .=   'WHERE ht_serial = ?';
		$this->execStatement($queryStr, array($rateAverage, $userId, $now, $serial));
		
		// 新規のシリアル番号取得
		$newSerial = 0;
		$queryStr = 'SELECT MAX(hr_serial) AS ns FROM photo_rate ';
		$ret = $this->selectRecord($queryStr, array(), $row);
		if ($ret) $newSerial = $row['ns'];
			
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * 画像評価の更新
	 *
	 * @param int     $serial		シリアル番号
	 * @param string  $comment		コメント
	 * @return bool					true = 成功、false = 失敗
	 */
	function updateCommentItem($serial, $comment)
	{
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $this->gEnv->getCurrentUserId();	// 現在のユーザ
						
		// トランザクション開始
		$this->startTransaction();
		
		// 指定のシリアルNoのレコードが削除状態でないかチェック
		$queryStr  = 'SELECT * FROM photo_rate ';
		$queryStr .=   'WHERE hr_serial = ? ';
		$ret = $this->selectRecord($queryStr, array($serial), $row);
		if ($ret){		// 既に登録レコードがあるとき
			if ($row['hr_deleted']){		// レコードが削除されていれば終了
				$this->endTransaction();
				return false;
			}
		} else {		// 存在しない場合は終了
			$this->endTransaction();
			return false;
		}
		
		// データを更新
		$queryStr  = 'UPDATE photo_rate ';
		$queryStr .=   'SET ';
		$queryStr .=     'hr_message = ?, ';
		$queryStr .=     'hr_update_user_id = ?, ';
		$queryStr .=     'hr_update_dt = ? ';
		$queryStr .=   'WHERE hr_serial = ?';
		$this->execStatement($queryStr, array($comment, $userId, $now, $serial));
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * 画像評価をシリアル番号で取得
	 *
	 * @param string	$serial				シリアル番号
	 * @param array     $row				レコード
	 * @return bool							取得 = true, 取得なし= false
	 */
	function getCommentBySerial($serial, &$row)
	{
		$queryStr  = 'SELECT *,reg.lu_name as regist_user_name,up.lu_name as update_user_name FROM photo_rate LEFT JOIN _login_user AS reg ON hr_user_id = reg.lu_id AND reg.lu_deleted = false ';
		$queryStr .=   'LEFT JOIN _login_user as up ON hr_update_user_id = up.lu_id AND up.lu_deleted = false ';
		$queryStr .=   'LEFT JOIN photo ON hr_photo_id = ht_id AND hr_language_id = ht_language_id AND ht_deleted = false ';
		$queryStr .=   'WHERE hr_serial = ? ';
		$ret = $this->selectRecord($queryStr, array($serial), $row);
		return $ret;
	}
	/**
	 * 画像評価の削除
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
			$queryStr  = 'SELECT * FROM photo_rate ';
			$queryStr .=   'WHERE hr_deleted = false ';		// 未削除
			$queryStr .=     'AND hr_serial = ? ';
			$ret = $this->selectRecord($queryStr, array($serial[$i]), $row);
			if ($ret){		// 既に登録レコードがあるとき			
				// レコードを削除
				$queryStr  = 'UPDATE photo_rate ';
				$queryStr .=   'SET hr_deleted = true, ';	// 削除
				$queryStr .=     'hr_update_user_id = ?, ';
				$queryStr .=     'hr_update_dt = ? ';
				$queryStr .=   'WHERE hr_serial = ?';
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
	 * コメントを画像IDで取得
	 *
	 * @param int		$limit				取得する項目数
	 * @param int		$page				取得するページ(1～)
	 * @param string	$publicId			公開画像ID
	 * @param string	$langId				言語ID
	 * @param function	$callback			コールバック関数
	 * @return 			なし
	 */
	function getCommentByPublicPhotoId($limit, $page, $publicId, $langId, $callback)
	{
		$offset = $limit * ($page -1);
		if ($offset < 0) $offset = 0;
		
		$queryStr  = 'SELECT * FROM photo_rate ';
		$queryStr .=   'LEFT JOIN photo ON hr_photo_id = ht_id AND hr_language_id = ht_language_id AND ht_deleted = false ';
		$queryStr .=   'WHERE hr_deleted = false ';	// 削除されていない
		$queryStr .=     'AND hr_language_id = ? ';
		$queryStr .=     'AND ht_public_id = ? ';
		$queryStr .=   'ORDER BY hr_regist_dt desc limit ' . $limit . ' offset ' . $offset;
		$this->selectLoop($queryStr, array($langId, $publicId), $callback);
	}
	/**
	 * コメント数を記事IDで取得
	 *
	 * @param string	$publicId			公開画像ID
	 * @param string	$langId				言語ID
	 * @return int							コメント数
	 */
	function getCommentCountByPublicPhotoId($publicId, $langId)
	{
		$queryStr  = 'SELECT * FROM photo_rate ';
		$queryStr .=   'LEFT JOIN photo ON hr_photo_id = ht_id AND hr_language_id = ht_language_id AND ht_deleted = false ';
		$queryStr .=   'WHERE hr_deleted = false ';	// 削除されていない
		$queryStr .=     'AND hr_language_id = ? ';
		$queryStr .=     'AND ht_public_id = ? ';
		return $this->selectRecordCount($queryStr, array($langId, $publicId));
	}
	/**
	 * コメントが存在するかチェック
	 *
	 * @param string $publicId	公開画像ID
	 * @param string $clientId	クライアントID
	 * @param string $langId	言語
	 * @return					true=存在する、false=存在しない
	 */
	function isExistsComment($publicId, $clientId, $langId)
	{
		$queryStr  = 'SELECT * FROM photo_rate LEFT JOIN _login_user ON hr_user_id = lu_id AND lu_deleted = false ';
		$queryStr .=   'LEFT JOIN photo ON hr_photo_id = ht_id AND hr_language_id = ht_language_id AND ht_deleted = false ';
		$queryStr .=   'WHERE hr_deleted = false ';	// 削除されていない
		$queryStr .=     'AND ht_public_id = ? ';
		$queryStr .=     'AND hr_client_id = ? ';
		$queryStr .=     'AND hr_language_id = ? ';
		return $this->isRecordExists($queryStr, array($publicId, $clientId, $langId));
	}
}
?>
