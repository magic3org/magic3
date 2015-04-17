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

class event_headlineDb extends BaseDb
{
	/**
	 * エントリー項目を取得(表示用)
	 *
	 * @param int 		$itemCount			取得項目数(0のときは該当すべて)
	 * @param string	$langId				言語
	 * @param int       $sortOrder			ソート順(0=昇順、1=降順)
	 * @param bool      $useBaseDay			基準日を使用するかどうか
	 * @param int       $dayCount			基準日までの日数
	 * @param function	$callback			コールバック関数
	 * @return 			なし
	 */
	function getEntryItems($itemCount, $langId, $sortOrder, $useBaseDay, $dayCount, $callback)
	{
		$now = date("Y/m/d H:i:s");	// 現在日時
		$initDt = $this->gEnv->getInitValueOfTimestamp();
		$params = array();
		
		// イベント記事を取得
		$queryStr = 'SELECT * FROM event_entry ';
		$queryStr .=  'WHERE ee_deleted = false ';		// 削除されていない
		$queryStr .=    'AND ee_status = ? ';		$params[] = 2;	// 「公開」(2)データを表示
		$queryStr .=    'AND ee_language_id = ? ';	$params[] = $langId;

		if ($useBaseDay){		// 基準日を使用する場合
			if (empty($sortOrder)){			// 昇順の場合
				// 基準日以降
				$compareDate = date("Y/m/d", strtotime("$dayCount day"));
				$queryStr .=    'AND ? <= ee_start_dt ';		// 基準日を含む
			} else {
				// 基準日の翌日よりも前
				$dayCount++;
				$compareDate = date("Y/m/d", strtotime("$dayCount day"));
				$queryStr .=    'AND ? > ee_start_dt ';
			}
			$params[] = $compareDate;
		}

		$queryStr .=  'ORDER BY ee_start_dt ';
		if (!empty($sortOrder)) $queryStr .=  'DESC';
		$queryStr .=  ', ee_id LIMIT ' . $itemCount;		// 投稿順
		$this->selectLoop($queryStr, $params, $callback);
	}
}
?>
