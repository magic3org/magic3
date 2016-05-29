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
 * @copyright  Copyright 2006-2016 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getDbPath() . '/baseDb.php');

class news_mainDb extends BaseDb
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
	
	 * @param string  $name			コンテンツタイトル
	 * @param string  $message		メッセージ
	 * @param string  $url			リンク先URL
	 * @param int     $mark			表示マーク
	 * @param bool    $visible		表示するかどうか
	 * @param timestamp $regDt		登録日時
	 * @param int     $newSerial	新規シリアル番号
	 * @return bool					true = 成功、false = 失敗
	 */
	function updateNewsItem($serial, $name, $message, $url, $mark, $visible, $regDt, &$newSerial)
	{
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $this->gEnv->getCurrentUserId();	// 現在のユーザ
			
		// トランザクション開始
		$this->startTransaction();
		
		// 引継ぎパラメータ初期化
		$otherParams = array();
		$otherQueryStr = '';
		$otherValueStr = '';
			
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
				$newsId = $row['nw_id'];
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
			
			$keepFields = array();	// 値を引き継ぐフィールド名
			$keepFields[] = 'nw_type';
			$keepFields[] = 'nw_server_id';
			$keepFields[] = 'nw_content_type';
			$keepFields[] = 'nw_content_id';
			$keepFields[] = 'nw_content_dt';
			$keepFields[] = 'nw_site_name';
			$keepFields[] = 'nw_site_url';
		
			// 値を引き継ぐフィールドをセット
			for ($i = 0; $i < count($keepFields); $i++){
				$fieldName = $keepFields[$i];
				$otherParams[] = $row[$fieldName];
				$otherQueryStr .= ', ' . $fieldName;
				$otherValueStr .= ', ?';
			}
		}
	
		// データを追加
		$params = array();
		$queryStr  = 'INSERT INTO news ';
		$queryStr .=   '(nw_id, ';				$params[] = $newsId;
		$queryStr .=   'nw_history_index, ';	$params[] = $historyIndex;
		$queryStr .=   'nw_name, ';				$params[] = $name;
		$queryStr .=   'nw_message, ';			$params[] = $message;
		$queryStr .=   'nw_url, ';				$params[] = $url;
		$queryStr .=   'nw_mark, ';				$params[] = $mark;
		$queryStr .=   'nw_visible, ';			$params[] = intval($visible);
		$queryStr .=   'nw_regist_dt, ';		$params[] = $regDt;
		$queryStr .=   'nw_create_user_id, ';	$params[] = $userId;
		$queryStr .=   'nw_create_dt';			$params[] = $now;
		$queryStr .=   $otherQueryStr . ') ';
		$queryStr .= 'VALUES ';
		$queryStr .=   '(?, ?, ?, ?, ?, ?, ?, ?, ?, ?';
		$queryStr .=   $otherValueStr . ') ';
		$this->execStatement($queryStr, array_merge($params, $otherParams));
		
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
	/**
	 * コンテンツ項目をコンテンツIDで取得
	 *
	 * @param string  $contentType		コンテンツタイプ
	 * @param string	$langId			言語ID
	 * @param string	$contentId		コンテンツID
	 * @param array     $row			レコード
	 * @return bool						取得 = true, 取得なし= false
	 */
	function getContentById($contentType, $langId, $contentId, &$row)
	{
		$queryStr  = 'SELECT * FROM content LEFT JOIN _login_user ON cn_create_user_id = lu_id AND lu_deleted = false ';
		$queryStr .=   'WHERE cn_deleted = false ';	// 削除されていない
		$queryStr .=    'AND cn_type = ? ';
		$queryStr .=   'AND cn_id = ? ';
		$queryStr .=   'AND cn_language_id = ? ';
		$ret = $this->selectRecord($queryStr, array($contentType, $contentId, $langId), $row);
		return $ret;
	}
	/**
	 * 商品を商品ID、言語IDで取得
	 *
	 * @param int		$id					商品ID
	 * @param string	$langId				言語ID
	 * @param array     $row				レコード
	 * @return bool							取得 = true, 取得なし= false
	 */
	function getProductById($id, $langId, &$row)
	{
		$queryStr  = 'SELECT * FROM product LEFT JOIN product_record ON pt_id = pe_product_id AND pt_language_id = pe_language_id ';
		$queryStr .=   'WHERE pt_deleted = false ';	// 削除されていない
		$queryStr .=    'AND pt_visible = true ';		// 表示可能な商品
		$queryStr .=    'AND pt_id = ? ';
		$queryStr .=    'AND pt_language_id = ? ';
		$ret = $this->selectRecord($queryStr, array($id, $langId), $row);
		return $ret;
	}
	/**
	 * ブログ記事をエントリーIDで取得
	 *
	 * @param string	$id					エントリーID
	 * @param string	$langId				言語ID
	 * @param array     $row				レコード
	 * @return bool							取得 = true, 取得なし= false
	 */
	function getEntryById($id, $langId, &$row)
	{
		$queryStr  = 'SELECT * FROM blog_entry ';
		$queryStr .=   'WHERE be_deleted = false ';	// 削除されていない
		$queryStr .=   'AND be_id = ? ';
		$queryStr .=   'AND be_language_id = ? ';
		$queryStr .=   'AND be_history_index >= 0 ';		// 正規(Regular)記事を対象
		$ret = $this->selectRecord($queryStr, array($id, $langId), $row);
		return $ret;
	}
	/**
	 * ルーム情報を識別IDで取得
	 *
	 * @param string	$id					識別ID
	 * @param string	$langId				言語
	 * @param array     $row				レコード
	 * @return bool							取得 = true, 取得なし= false
	 */
	function getRoomById($id, $langId, &$row)
	{
		$queryStr  = 'SELECT * FROM user_content_room ';
		$queryStr .=   'WHERE ur_deleted = false ';
		$queryStr .=   'AND ur_id = ? ';
		$ret = $this->selectRecord($queryStr, array($id), $row);
		return $ret;
	}
	/**
	 * イベント情報を取得
	 *
	 * @param int		$id					イベントID
	 * @param string	$langId				言語
	 * @param array     $row				レコード
	 * @return bool							取得 = true, 取得なし= false
	 */
	function getEventById($id, $langId, &$row)
	{
		$queryStr  = 'SELECT * FROM event_entry ';
		$queryStr .=   'WHERE ee_deleted = false ';	// 削除されていない
		$queryStr .=   'AND ee_id = ? ';
		$queryStr .=   'AND ee_language_id = ? ';
		$ret = $this->selectRecord($queryStr, array($id, $langId), $row);
		return $ret;
	}
	/**
	 * フォト情報を取得
	 *
	 * @param int		$id					公開画像ID
	 * @param string	$langId				言語
	 * @param array     $row				レコード
	 * @return bool							取得 = true, 取得なし= false
	 */
	function getPhotoById($id, $langId, &$row)
	{
		$queryStr  = 'SELECT * FROM photo LEFT JOIN _login_user ON ht_owner_id = lu_id AND lu_deleted = false ';
		$queryStr .=   'WHERE ht_deleted = false ';
		$queryStr .=     'AND ht_public_id = ? ';
		$queryStr .=     'AND ht_language_id = ? ';
		$ret = $this->selectRecord($queryStr, array($id, $langId), $row);
		return $ret;
	}
}
?>
