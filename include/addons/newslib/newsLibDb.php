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

class newsLibDb extends BaseDb
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
	 * 新着情報項目の追加
	 *
	 * @param string  $contentType	コンテンツタイプ
	 * @param string  $contentId	コンテンツID
	 * @param string  $message		メッセージ
	 * @param string  $url			リンク先URL
	 * @param timestamp $regDt		登録日時
	 * @param int     $newSerial	新規シリアル番号
	 * @return bool					true = 成功、false = 失敗
	 */
	function addNewsItem($contentType, $contentId, $message, $url, $regDt, &$newSerial)
	{
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $this->gEnv->getCurrentUserId();	// 現在のユーザ
			
		// トランザクション開始
		$this->startTransaction();
			
		// 新着情報IDを決定する
		$queryStr = 'SELECT MAX(nw_id) AS mid FROM news';
		$ret = $this->selectRecord($queryStr, array(), $row);
		if ($ret){
			$newsId = $row['mid'] + 1;
		} else {
			$newsId = 1;
		}

		// データを追加
		$params = array();
		$queryStr  = 'INSERT INTO news ';
		$queryStr .=   '(nw_id, ';				$params[] = $newsId;
		$queryStr .=   'nw_content_type, ';		$params[] = $contentType;
		$queryStr .=   'nw_content_id, ';		$params[] = $contentId;
		$queryStr .=   'nw_message, ';			$params[] = $message;
		$queryStr .=   'nw_url, ';				$params[] = $url;
		$queryStr .=   'nw_regist_dt, ';		$params[] = $regDt;
		$queryStr .=   'nw_create_user_id, ';	$params[] = $userId;
		$queryStr .=   'nw_create_dt)';			$params[] = $now;
		$queryStr .= 'VALUES ';
		$queryStr .=   '(?, ?, ?, ?, ?, ?, ?, ?)';
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
}
?>
