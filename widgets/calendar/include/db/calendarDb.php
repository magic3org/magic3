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

class calendarDb extends BaseDb
{
	/**
	 * イベント記事を取得(表示用)
	 *
	 * @param int		$limit				取得する項目数
	 * @param int		$page				取得するページ(1～)
	 * @param timestamp	$startDt			期間(開始日)
	 * @param timestamp	$endDt				期間(終了日)
	 * @param string	$langId				言語
	 * @param function	$callback			コールバック関数
	 * @return 			なし
	 */
	function getEventItems($limit, $page, $startDt, $endDt, $langId, $callback)
	{
		$offset = $limit * ($page -1);
		if ($offset < 0) $offset = 0;
		$params = array();
		
		$queryStr  = 'SELECT * FROM event_entry ';
		$queryStr .=   'WHERE ee_deleted = false ';		// 削除されていない
		$queryStr .=     'AND ee_status = ? ';		$params[] = 2;	// 「公開」(2)データを表示
		$queryStr .=     'AND ee_language_id = ? ';	$params[] = $langId;
	
		// 期間が重なっている場合は取得
		$queryStr .=    'AND ((ee_start_dt <= ? AND ? < ee_end_dt) ';
		$params[] = $startDt;
		$params[] = $startDt;
		$queryStr .=    'OR (? <= ee_start_dt AND ee_start_dt < ?)) ';
		$params[] = $startDt;
		$params[] = $endDt;

		$queryStr .=  'ORDER BY ee_start_dt LIMIT ' . $limit . ' OFFSET ' . $offset;// 日付順
		$this->selectLoop($queryStr, $params, $callback);
	}
}
?>
