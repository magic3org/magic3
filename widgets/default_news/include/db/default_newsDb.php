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
	 * コメントの新規追加
	 *
	 * @param int $addType			追加タイプ(0=フラット、1=ツリー)
	 * @param string $contentType	コンテンツタイプ
	 * @param string $langId		言語
	 * @param string  $contentsId	共通コメントID
	 * @param int  $deviceType		デバイスタイプ(0=PC、1=携帯、2=スマートフォン)
	 * @param int  $parentSerial	親コメントシリアル番号
	 * @param string  $title		題名
	 * @param string  $message		コメントメッセージ
	 * @param string  $url			URL
	 * @param string  $author		ユーザ名
	 * @param string  $email		Eメール
	 * @param int $status			状態(0=未設定、1=非公開、2=公開)
	 * @param int     $newSerial	新規シリアル番号
	 * @return bool					true = 成功、false = 失敗
	 */
	function addComment($addType, $contentType, $langId, $contentsId, $deviceType, $parentSerial, $title, $message, $url, $author, $email, $status, &$newSerial)
	{
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $this->gEnv->getCurrentUserId();	// 現在のユーザ
		$nestLevel = 0;
		if (!empty($userId)){		// ログイン中の場合
			$author = '';
		}
		
		// トランザクション開始
		$this->startTransaction();
		
		// コメントNoを決定する
		$params = array();
		$queryStr  = 'SELECT MAX(cm_no) AS mid FROM comment ';
		$queryStr .=   'WHERE cm_content_type = ? '; $params[] = $contentType;
		$queryStr .=     'AND cm_contents_id = ? '; $params[] = $contentsId;
		$ret = $this->selectRecord($queryStr, $params, $row);
		if ($ret){
			$commentNo = $row['mid'] + 1;
		} else {
			$commentNo = 1;
		}
		
		// 親コメントがある場合は情報を取得
		if (!empty($parentSerial)){
			$queryStr  = 'SELECT * FROM comment ';
			$queryStr .=   'WHERE cm_serial = ? ';
			$ret = $this->selectRecord($queryStr, array(intval($parentSerial)), $row);
			if ($ret){
				$nestLevel = $row['cm_nest_level'] + 1;
			}
		}
		// 表示順を作成
		if (empty($addType)){		// 最後に追加
			$sortOrder = $commentNo;
		} else {		// レスポンス先のコメントの最後に追加
		}
		// コメントを追加
		$queryStr  = 'INSERT INTO comment ';
		$queryStr .=   '(cm_content_type, cm_contents_id, cm_device_type, cm_language_id, cm_parent_serial, cm_no, cm_sort_order, cm_nest_level, cm_title, cm_message, cm_url, cm_author, cm_email, cm_status, cm_create_user_id, cm_create_dt) ';
		$queryStr .=   'VALUES ';
		$queryStr .=   '(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
		$this->execStatement($queryStr, array($contentType, $contentsId, $deviceType, $langId, $parentSerial, $commentNo, $sortOrder, $nestLevel, $title, $message, $url, $author, $email, $status, $userId, $now));
		
		// 新規のシリアル番号取得
		$newSerial = 0;
		$queryStr = 'SELECT MAX(cm_serial) AS ns FROM comment ';
		$ret = $this->selectRecord($queryStr, array(), $row);
		if ($ret) $newSerial = $row['ns'];
			
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
}
?>
