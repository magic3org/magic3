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
 * @copyright  Copyright 2006-2015 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getDbPath() . '/baseDb.php');

class evententry_attachmentDb extends BaseDb
{
	/**
	 * イベント予約定義値を取得をすべて取得
	 *
	 * @param array  $rows			レコード
	 * @return bool					1行以上取得 = true, 取得なし= false
	 */
	function getAllConfig(&$rows)
	{
		$queryStr  = 'SELECT * FROM evententry_config ';
		$queryStr .=   'ORDER BY ef_index';
		$retValue = $this->selectRecords($queryStr, array(), $rows);
		return $retValue;
	}
	/**
	 * イベント項目をイベントIDで取得
	 *
	 * @param string  $langId		言語ID
	 * @param string  $eventId		イベントID
	 * @param string  $entryType	受付タイプ
	 * @param array   $row			レコード
	 * @return bool					true = 成功、false = 失敗
	 */
	function getEntry($langId, $eventId, $entryType, &$row)
	{
		$params = array();
		$queryStr  = 'SELECT * FROM evententry ';
		$queryStr .=   'LEFT JOIN event_entry ON et_contents_id = ee_id AND ee_deleted = false ';
		$queryStr .=     'AND ee_language_id = ? '; $params[] = $langId;
		$queryStr .=   'WHERE et_deleted = false ';	// 削除されていない
		$queryStr .=     'AND et_contents_id = ? '; $params[] = $eventId;
		$queryStr .=     'AND et_type = ? '; $params[] = $entryType;
		$ret = $this->selectRecord($queryStr, $params, $row);
		return $ret;
	}
	/**
	 * ユーザの予約数を取得
	 *
	 * @param string  $id			イベント予約ID
	 * @return bool					true=存在する、false=存在しない
	 */
	function getEntryUserCount($id)
	{
		$queryStr  = 'SELECT * FROM evententry_request ';
		$queryStr .=   'WHERE er_deleted = false ';			// 削除されていない
		$queryStr .=     'AND er_status = 1 ';				// 「参加」のみ
		$queryStr .=     'AND er_evententry_id = ? ';
		return $this->selectRecordCount($queryStr, array($id));
	}
	/**
	 * ユーザの予約状況の確認
	 *
	 * @param string  $id			イベント予約ID
	 * @param int     $entryUserId	登録ユーザID
	 * @return bool					true=存在する、false=存在しない
	 */
	function isExistsEntryUser($id, $userId)
	{
		$queryStr  = 'SELECT * FROM evententry_request ';
		$queryStr .=   'WHERE er_deleted = false ';			// 削除されていない
		$queryStr .=     'AND er_evententry_id = ? ';
		$queryStr .=     'AND er_user_id = ? ';
		return $this->isRecordExists($queryStr, array($id, $userId));
	}
}
?>
