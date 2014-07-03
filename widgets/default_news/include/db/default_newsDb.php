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
 * @copyright  Copyright 2006-2014 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getDbPath() . '/baseDb.php');

class default_newsDb extends BaseDb
{
	/**
	 * 新着情報定義値を取得をすべて取得
	 *
	 * @param array  $rows			レコード
	 * @return bool					1行以上取得 = true, 取得なし= false
	 */
	function getAllConfig(&$rows)
	{
		$queryStr  = 'SELECT * FROM news_config ';
		$queryStr .=   'ORDER BY nc_index';
		$retValue = $this->selectRecords($queryStr, array(), $rows);
		return $retValue;
	}
	/**
	 * 新着情報定義値を取得
	 *
	 * @param string $key		キーとなる項目値
	 * @return string $value	値
	 */
	function getConfig($key)
	{
		$retValue = '';
		$queryStr = 'SELECT nc_value FROM news_config ';
		$queryStr .=  'WHERE nc_id  = ?';
		$ret = $this->selectRecord($queryStr, array($key), $row);
		if ($ret) $retValue = $row['nc_value'];
		return $retValue;
	}
	/**
	 * 新着情報定義値を更新
	 *
	 * @param string $key		キーとなる項目値
	 * @param string $value		値
	 * @return					true = 正常、false=異常
	 */
	function updateConfig($key, $value)
	{
		// データの確認
		$queryStr = 'SELECT nc_value FROM news_config ';
		$queryStr .=  'WHERE nc_id  = ?';
		$ret = $this->isRecordExists($queryStr, array($key));
		if ($ret){
			$queryStr = "UPDATE news_config SET nc_value = ? WHERE nc_id = ?";
			return $this->execStatement($queryStr, array($value, $key));
		} else {
			$queryStr = "INSERT INTO news_config (nc_id, nc_value) VALUES (?, ?)";
			return $this->execStatement($queryStr, array($key, $value));
		}
	}
	/**
	 * 新着情報数を取得(管理用)
	 *
	 * @param string	$contentType		コンテンツタイプ
	 * @param string	$keywords			検索キーワード
	 * @return int							項目数
	 */
	function getNewsListCount($contentType, $keywords)
	{
		$params = array();
		$queryStr = 'SELECT * FROM news ';
		$queryStr .=  'WHERE nw_deleted = false ';		// 削除されていない
		if (!empty($contentType)) $queryStr .=    'AND nw_type = ? ';$params[] = $contentType;

		// タイトル、本文、ユーザ定義フィールドを検索
		if (!empty($keywords)){
			for ($i = 0; $i < count($keywords); $i++){
				$keyword = addslashes($keywords[$i]);// 「'"\」文字をエスケープ
				$queryStr .=    'AND (nw_message LIKE \'%' . $keyword . '%\' ';		// メッセージ
				$queryStr .=    'OR nw_summary LIKE \'%' . $keyword . '%\') ';		// 概要
			}
		}
		return $this->selectRecordCount($queryStr, $params);
	}
	/**
	 * 新着情報項目を検索(管理用)
	 *
	 * @param string	$contentType		コンテンツタイプ
	 * @param int		$limit				取得する項目数
	 * @param int		$page				取得するページ(1～)
	 * @param array		$keywords			検索キーワード
	 * @param function	$callback			コールバック関数
	 * @return 			なし
	 */
	function getNewsList($contentType, $limit, $page, $keywords, $callback)
	{
		$offset = $limit * ($page -1);
		if ($offset < 0) $offset = 0;
		
		$params = array();
		$queryStr = 'SELECT * FROM news ';
		$queryStr .=  'WHERE nw_deleted = false ';		// 削除されていない
		if (!empty($contentType)) $queryStr .=    'AND nw_type = ? ';$params[] = $contentType;

		// タイトル、本文、ユーザ定義フィールドを検索
		if (!empty($keywords)){
			for ($i = 0; $i < count($keywords); $i++){
				$keyword = addslashes($keywords[$i]);// 「'"\」文字をエスケープ
				$queryStr .=    'AND (nw_message LIKE \'%' . $keyword . '%\' ';		// メッセージ
				$queryStr .=    'OR nw_summary LIKE \'%' . $keyword . '%\') ';		// 概要
			}
		}
		
		$queryStr .=  'ORDER BY nw_regist_dt desc limit ' . $limit . ' offset ' . $offset;
		$this->selectLoop($queryStr, $params, $callback);
	}
	/**
	 * 新着情報項目の追加、更新
	 *
	 * @param int     $serial		シリアル番号(0のときは新規追加)
	 * @param string  $contentType	コンテンツタイプ
	 * @param string  $contentId	コンテンツID
	 * @param string  $message		メッセージ
	 * @param string  $url			リンク先URL
	 * @param int     $newSerial	新規シリアル番号
	 * @return bool					true = 成功、false = 失敗
	 */
	function updateNewsItem($serial, $message, $url, &$newSerial)
	{
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $this->gEnv->getCurrentUserId();	// 現在のユーザ
			
		// トランザクション開始
		$this->startTransaction();
		
		if (empty($serial)){		// シリアル番号が0のときはIDを新規取得
			// 新着情報IDを決定する
			$queryStr = 'SELECT MAX(nw_id) AS mid FROM news';
			$ret = $this->selectRecord($queryStr, array(), $row);
			if ($ret){
				$newsId = $row['mid'] + 1;
			} else {
				$newsId = 1;
			}
			$historyIndex = 0;		// 履歴番号
		} else {
			// 指定のシリアルNoのレコードが削除状態でないかチェック
			$queryStr  = 'SELECT * FROM news ';
			$queryStr .=   'WHERE nw_serial = ? ';
			$ret = $this->selectRecord($queryStr, array($serial), $row);
			if ($ret){		// 既に登録レコードがあるとき
				if ($row['nw_deleted']){		// レコードが削除されていれば終了
					$this->endTransaction();
					return false;
				}
				$historyIndex = $row['nw_history_index'] + 1;
			} else {		// 存在しない場合は終了
				$this->endTransaction();
				return false;
			}
			// 古いレコードを削除
			$queryStr  = 'UPDATE news ';
			$queryStr .=   'SET nw_deleted = true, ';	// 削除
			$queryStr .=     'nw_update_user_id = ?, ';
			$queryStr .=     'nw_update_dt = ? ';
			$queryStr .=   'WHERE nw_serial = ?';
			$this->execStatement($queryStr, array($userId, $now, $serial));
		}
		
		// データを追加
		$params = array();
		$queryStr  = 'INSERT INTO news ';
		$queryStr .=   '(nw_id, ';				$params[] = $newsId;
		$queryStr .=   'nw_history_index, ';	$params[] = $historyIndex;
		$queryStr .=   'nw_message, ';			$params[] = $message;
		$queryStr .=   'nw_create_user_id, ';	$params[] = $userId;
		$queryStr .=   'nw_create_dt) ';		$params[] = $now;
		$queryStr .= 'VALUES ';
		$queryStr .=   '(?, ?, ?, ?, ?)';
		$this->execStatement($queryStr, $params);
		
		// 新規のシリアル番号取得
		$newSerial = 0;
		$queryStr = 'SELECT MAX(nw_serial) AS ns FROM news ';
		$ret = $this->selectRecord($queryStr, array(), $row);
		if ($ret) $newSerial = $row['ns'];
			
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * 新着情報項目の削除
	 *
	 * @param array $serial			シリアルNo
	 * @return						true=成功、false=失敗
	 */
	function delNewsItem($serial)
	{
		$now = date("Y/m/d H:i:s");	// 現在日時
		$user = $this->gEnv->getCurrentUserId();	// 現在のユーザ
		
		if (!is_array($serial) || count($serial) <= 0) return true;
		
		// トランザクション開始
		$this->startTransaction();
		
		// 指定のシリアルNoのレコードが削除状態でないかチェック
		for ($i = 0; $i < count($serial); $i++){
			$queryStr  = 'SELECT * FROM news ';
			$queryStr .=   'WHERE nw_deleted = false ';		// 未削除
			$queryStr .=     'AND nw_serial = ? ';
			$ret = $this->isRecordExists($queryStr, array($serial[$i]));
			// 存在しない場合は、既に削除されたとして終了
			if (!$ret){
				$this->endTransaction();
				return false;
			}
		}
		
		// レコードを削除
		$queryStr  = 'UPDATE news ';
		$queryStr .=   'SET nw_deleted = true, ';	// 削除
		$queryStr .=     'nw_update_user_id = ?, ';
		$queryStr .=     'nw_update_dt = ? ';
		$queryStr .=   'WHERE nw_serial in (' . implode($serial, ',') . ') ';
		$this->execStatement($queryStr, array($user, $now));
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * 新着情報項目をシリアル番号で取得
	 *
	 * @param string	$serial				シリアル番号
	 * @param array     $row				レコード
	 * @return bool							取得 = true, 取得なし= false
	 */
	function getNewsItem($serial, &$row)
	{
		$queryStr  = 'SELECT * FROM news LEFT JOIN _login_user ON nw_create_user_id = lu_id AND lu_deleted = false ';
		$queryStr .=   'WHERE nw_serial = ? ';
		$ret = $this->selectRecord($queryStr, array($serial), $row);
		return $ret;
	}
}
?>
