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

class blog_analyticsDb extends BaseDb
{
	/**
	 * コンテンツの参照数を日付範囲で取得
	 *
	 * @param string    $typeId				コンテンツタイプ
	 * @param string    $contentId			コンテンツID(空の場合はすべての集計)
	 * @param date		$startDate			集計期間、開始日(NULL=先頭からすべて)
	 * @param date		$endDate			集計期間、終了日(NULL=最後まですべて)
	 * @param function $callback			コールバック関数
	 * @return								なし
	 */
	function getContentViewCountByDate($typeId, $contentId, $startDate, $endDate, $callback)
	{
		$params = array();
		$queryStr  = 'SELECT vc_date, SUM(vc_count) AS total FROM _view_count ';
		$queryStr .=   'WHERE vc_type_id = ? ';	$params[] = $typeId;		// データタイプ
		
		// 対象コンテンツ
		if (!empty($contentId)){
			$queryStr .=   'AND vc_content_id = ? ';
			$params[] = $contentId;
		}

		// 日付範囲
		if (!empty($startDate)){
			$queryStr .=    'AND ? <= vc_date ';
			$params[] = $startDate;
		}
		if (!empty($endDate)){
			$queryStr .=    'AND vc_date <= ? ';
			$params[] = $endDate;
		}

		// 日付でソート
		$queryStr .=  'GROUP BY vc_date ';
		$queryStr .=  'ORDER BY vc_date';

		$this->selectLoop($queryStr, $params, $callback);
	}
}
?>
