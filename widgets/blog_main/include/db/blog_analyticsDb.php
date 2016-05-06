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
	 * 全コンテンツの日単位の参照数を日付範囲で取得
	 *
	 * @param string    $typeId			コンテンツタイプ
	 * @param date		$startDate		集計期間、開始日(NULL=先頭からすべて)
	 * @param date		$endDate		集計期間、終了日(NULL=最後まですべて)
	 * @param function $callback		コールバック関数
	 * @return							なし
	 */
	function getAllContentViewCountByDate($typeId, $startDate, $endDate, $callback)
	{
		$params = array();
		$queryStr  = 'SELECT vc_date AS day, SUM(vc_count) AS total FROM _view_count ';
		$queryStr .=   'WHERE vc_type_id = ? ';	$params[] = $typeId;		// データタイプ

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
		$queryStr .=  'GROUP BY day ';
		$queryStr .=  'ORDER BY day';

		$this->selectLoop($queryStr, $params, $callback);
	}
	/**
	 * コンテンツの日単位の参照数を日付範囲で取得
	 *
	 * @param string    $typeId			コンテンツタイプ
	 * @param string    $contentId		コンテンツID
	 * @param date		$startDate		集計期間、開始日(NULL=先頭からすべて)
	 * @param date		$endDate		集計期間、終了日(NULL=最後まですべて)
	 * @param array		$rows			取得した行データ
	 * @return bool						true=取得、false=取得せず
	 */
	function getContentViewCountByDate($typeId, $contentId, $startDate, $endDate, &$rows)
	{
		$params = array();
		$queryStr  = 'SELECT vc_date AS day, SUM(vc_count) AS total FROM _view_count ';
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
		$queryStr .=  'GROUP BY day ';
		$queryStr .=  'ORDER BY day';

		$retValue = $this->selectRecords($queryStr, $params, $rows);
		return $retValue;
	}
	/**
	 * 全コンテンツの月単位の参照数を日付範囲で取得
	 *
	 * @param string    $typeId			コンテンツタイプ
	 * @param date		$startDate		集計期間、開始日(NULL=先頭からすべて)
	 * @param date		$endDate		集計期間、終了日(NULL=最後まですべて)
	 * @param function $callback		コールバック関数
	 * @return							なし
	 */
	function getAllContentViewCountByMonth($typeId, $startDate, $endDate, $callback)
	{
		$params = array();
		$queryStr  = 'SELECT EXTRACT(year FROM vc_date) AS year, EXTRACT(month FROM vc_date) AS month, SUM(vc_count) AS total FROM _view_count ';
		$queryStr .=   'WHERE vc_type_id = ? ';	$params[] = $typeId;		// データタイプ

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
		$queryStr .=  'GROUP BY year, month ';
		$queryStr .=  'ORDER BY year, month';

		$this->selectLoop($queryStr, $params, $callback);
	}
	/**
	 * コンテンツの月単位の参照数を日付範囲で取得
	 *
	 * @param string    $typeId			コンテンツタイプ
	 * @param string    $contentId		コンテンツID
	 * @param date		$startDate		集計期間、開始日(NULL=先頭からすべて)
	 * @param date		$endDate		集計期間、終了日(NULL=最後まですべて)
	 * @param array		$rows			取得した行データ
	 * @return bool						true=取得、false=取得せず
	 */
	function getContentViewCountByMonth($typeId, $contentId, $startDate, $endDate, &$rows)
	{
		$params = array();
		$queryStr  = 'SELECT EXTRACT(year FROM vc_date) AS year, EXTRACT(month FROM vc_date) AS month, SUM(vc_count) AS total FROM _view_count ';
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
		$queryStr .=  'GROUP BY year, month ';
		$queryStr .=  'ORDER BY year, month';

		$retValue = $this->selectRecords($queryStr, $params, $rows);
		return $retValue;
	}
	/**
	 * 全コンテンツの時間単位の参照数を日付範囲で取得
	 *
	 * @param string    $typeId			コンテンツタイプ
	 * @param date		$startDate		集計期間、開始日(NULL=先頭からすべて)
	 * @param date		$endDate		集計期間、終了日(NULL=最後まですべて)
	 * @param function $callback		コールバック関数
	 * @return							なし
	 */
	function getAllContentViewCountByHour($typeId, $startDate, $endDate, $callback)
	{
		$params = array();
		$queryStr  = 'SELECT vc_hour AS hour, SUM(vc_count) AS total FROM _view_count ';
		$queryStr .=   'WHERE vc_type_id = ? ';	$params[] = $typeId;		// データタイプ

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
		$queryStr .=  'GROUP BY hour ';
		$queryStr .=  'ORDER BY hour';

		$this->selectLoop($queryStr, $params, $callback);
	}
	/**
	 * コンテンツの時間単位の参照数を日付範囲で取得
	 *
	 * @param string    $typeId			コンテンツタイプ
	 * @param string    $contentId		コンテンツID
	 * @param date		$startDate		集計期間、開始日(NULL=先頭からすべて)
	 * @param date		$endDate		集計期間、終了日(NULL=最後まですべて)
	 * @param array		$rows			取得した行データ
	 * @return bool						true=取得、false=取得せず
	 */
	function getContentViewCountByHour($typeId, $contentId, $startDate, $endDate, &$rows)
	{
		$params = array();
		$queryStr  = 'SELECT vc_hour AS hour, SUM(vc_count) AS total FROM _view_count ';
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
		$queryStr .=  'GROUP BY hour ';
		$queryStr .=  'ORDER BY hour';

		$retValue = $this->selectRecords($queryStr, $params, $rows);
		return $retValue;
	}
	/**
	 * 全コンテンツの曜日単位の参照数を日付範囲で取得
	 *
	 * @param string    $typeId			コンテンツタイプ
	 * @param date		$startDate		集計期間、開始日(NULL=先頭からすべて)
	 * @param date		$endDate		集計期間、終了日(NULL=最後まですべて)
	 * @param function $callback		コールバック関数
	 * @return							なし
	 */
	function getAllContentViewCountByWeek($typeId, $startDate, $endDate, $callback)
	{
		$params = array();
		if ($this->getDbType() == M3_DB_TYPE_MYSQL){		// MySQLの場合
			$queryStr  = 'SELECT WEEKDAY(vc_date) AS week, SUM(vc_count) AS total FROM _view_count ';
		} else if ($this->getDbType() == M3_DB_TYPE_PGSQL){// PostgreSQLの場合
			$queryStr  = 'SELECT EXTRACT(dow FROM vc_date) AS week, SUM(vc_count) AS total FROM _view_count ';
		}
		$queryStr .=   'WHERE vc_type_id = ? ';	$params[] = $typeId;		// データタイプ

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
		$queryStr .=  'GROUP BY week ';
		$queryStr .=  'ORDER BY week';

		$this->selectLoop($queryStr, $params, $callback);
	}
	/**
	 * コンテンツの曜日単位の参照数を日付範囲で取得
	 *
	 * @param string    $typeId			コンテンツタイプ
	 * @param string    $contentId		コンテンツID
	 * @param date		$startDate		集計期間、開始日(NULL=先頭からすべて)
	 * @param date		$endDate		集計期間、終了日(NULL=最後まですべて)
	 * @param array		$rows			取得した行データ
	 * @return bool						true=取得、false=取得せず
	 */
	function getContentViewCountByWeek($typeId, $contentId, $startDate, $endDate, &$rows)
	{
		$params = array();
		if ($this->getDbType() == M3_DB_TYPE_MYSQL){		// MySQLの場合
			$queryStr  = 'SELECT WEEKDAY(vc_date) AS week, SUM(vc_count) AS total FROM _view_count ';
		} else if ($this->getDbType() == M3_DB_TYPE_PGSQL){// PostgreSQLの場合
			$queryStr  = 'SELECT EXTRACT(dow FROM vc_date) AS week, SUM(vc_count) AS total FROM _view_count ';
		}
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
		$queryStr .=  'GROUP BY week ';
		$queryStr .=  'ORDER BY week';

		$retValue = $this->selectRecords($queryStr, $params, $rows);
		return $retValue;
	}
	/**
	 * 指定期間の参照が多い順にコンテンツを取得
	 *
	 * @param string    $typeId				コンテンツタイプ
	 * @param date		$startDate			集計期間、開始日(NULL=先頭からすべて)
	 * @param date		$endDate			集計期間、終了日(NULL=最後まですべて)
	 * @param int		$limit				取得する項目数
	 * @param int		$page				取得するページ(1～)
	 * @param string    $langId				言語ID
	 * @param function  $callback			コールバック関数
	 * @return								なし
	 */
	function getTopContentByDateRange($typeId, $startDate, $endDate, $limit, $page, $langId, $callback)
	{
		$offset = $limit * ($page -1);
		if ($offset < 0) $offset = 0;
	 
		$params = array();
		$queryStr  = 'SELECT vc_content_id, SUM(vc_count) AS total, be_id, be_name FROM _view_count ';
		if ($this->getDbType() == M3_DB_TYPE_MYSQL){		// MySQLの場合
			$queryStr .=   'LEFT JOIN blog_entry ON CAST(vc_content_id AS UNSIGNED) = be_id AND be_deleted = false AND be_language_id = ? '; $params[] = $langId;
		} else if ($this->getDbType() == M3_DB_TYPE_PGSQL){// PostgreSQLの場合
			$queryStr .=   'LEFT JOIN blog_entry ON CAST(vc_content_id AS INT) = be_id AND be_deleted = false AND be_language_id = ? '; $params[] = $langId;
		}
		
		$queryStr .=   'WHERE vc_type_id = ? ';	$params[] = $typeId;		// データタイプ
		$queryStr .=     'AND vc_content_id != \'\' ';		// 空のコンテンツID除く

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
		$queryStr .=  'GROUP BY vc_content_id, be_id, be_name ';
		if ($this->getDbType() == M3_DB_TYPE_MYSQL){		// MySQLの場合
			$queryStr .=  'ORDER BY total DESC, CAST(vc_content_id AS UNSIGNED) DESC LIMIT ' . $limit . ' offset ' . $offset;
		} else if ($this->getDbType() == M3_DB_TYPE_PGSQL){// PostgreSQLの場合
			$queryStr .=  'ORDER BY total DESC, CAST(vc_content_id AS INT) DESC LIMIT ' . $limit . ' offset ' . $offset;
		}
		
		$this->selectLoop($queryStr, $params, $callback);
	}
}
?>
