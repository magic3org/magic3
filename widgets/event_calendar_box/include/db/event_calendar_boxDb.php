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
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getDbPath() . '/baseDb.php');

class event_calendar_boxDb extends BaseDb
{
	/**
	 * イベント記事を取得(表示用)
	 *
	 * @param timestamp $now				現在日時(現在日時より未来の投稿日時の記事は取得しない)
	 * @param timestamp	$startDt			期間(開始日)
	 * @param timestamp	$endDt				期間(終了日)
	 * @param string	$lang				言語
	 * @param function	$callback			コールバック関数
	 * @return 			なし
	 */
	function getEntryItems($now, $startDt, $endDt, $lang, $callback)
	{
		$initDt = $this->gEnv->getInitValueOfTimestamp();
		$params = array();
		
		$queryStr = 'SELECT * FROM event_entry ';
		$queryStr .=  'WHERE ee_deleted = false ';		// 削除されていない
		$queryStr .=    'AND ee_status = ? ';		$params[] = 2;	// 「公開」(2)データを表示
		$queryStr .=    'AND ee_language_id = ? ';	$params[] = $lang;
		
		if (!empty($startDt)){
			$queryStr .=    'AND ? <= ee_start_dt ';
			$params[] = $startDt;
		}
		if (!empty($endDt)){
			$queryStr .=    'AND ee_start_dt < ? ';
			$params[] = $endDt;
		}
		
		$queryStr .=  'ORDER BY ee_start_dt';
		$this->selectLoop($queryStr, $params, $callback, null);
	}
	/**
	 * イベント記事のデータのある期間を取得(表示用)
	 *
	 * @param string	$lang				言語
	 * @param timestamp	$startDt			期間(開始日)
	 * @param timestamp	$endDt				期間(終了日)
	 * @return bool							true=取得、false=取得せず
	 */
	function getTermWithEntryItems($langId, &$startDt, &$endDt)
	{
		$params = array();
		
		$queryStr  = 'SELECT MIN(ee_start_dt) AS mindt, MAX(ee_end_dt) AS maxdt FROM event_entry ';
		$queryStr .=   'WHERE ee_deleted = false ';		// 削除されていない
		$queryStr .=     'AND ee_status = ? ';		$params[] = 2;	// 「公開」(2)データを表示
		$queryStr .=     'AND ee_language_id = ? ';	$params[] = $langId;
		$retValue = $this->selectRecord($queryStr, $params, $rows);
		if ($retValue){
			$startDt = $rows['mindt'];
			$endDt = $rows['maxdt'];
			$endDt = $startDt > $endDt ? $startDt : $endDt;
		}
		return $retValue;
	}
}
?>
