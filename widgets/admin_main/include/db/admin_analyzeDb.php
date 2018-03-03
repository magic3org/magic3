<?php
/**
 * アクセス解析用DBクラス
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2018 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getDbPath() . '/baseDb.php');

class admin_analyzeDb extends BaseDb
{
	const MAX_URL_LENGTH = 180;					// URLの長さ最大値
	
	/**
	 * 最も古いアクセスログを取得
	 *
	 * @param array  	$row		取得レコード
	 * @param bool					true=成功、false=失敗
	 */
	function getOldAccessLog(&$row)
	{
/*		$queryStr  = 'SELECT * FROM _access_log ';
		$queryStr .=   'ORDER BY al_serial';
		$ret = $this->selectRecord($queryStr, array(), $row);
		return $ret;*/
		$serial = 0;
		$queryStr  = 'SELECT min(al_serial) as m FROM _access_log ';
		$ret = $this->selectRecord($queryStr, array(), $row);
		if ($ret) $serial = $row['m'];
		
		$queryStr  = 'SELECT * FROM _access_log ';
		$queryStr .=   'WHERE al_serial = ?';
		$ret = $this->selectRecord($queryStr, array($serial), $row);
		return $ret;
	}
	/**
	 * 最も古いサイト解析ページビューを取得
	 *
	 * @param array  	$row		取得レコード
	 * @param bool					true=成功、false=失敗
	 */
	function getOldPageView(&$row)
	{
		$serial = 0;
		$queryStr  = 'SELECT min(ap_serial) as m FROM _analyze_page_view ';
		$ret = $this->selectRecord($queryStr, array(), $row);
		if ($ret) $serial = $row['m'];
		
		$queryStr  = 'SELECT * FROM _analyze_page_view ';
		$queryStr .=   'WHERE ap_serial = ?';
		$ret = $this->selectRecord($queryStr, array($serial), $row);
		return $ret;
	}
	/**
	 * アクセスログを削除
	 *
	 * @param timestamp	$startDt	最初の未集計日
	 * @return						true=成功、false=失敗
	 */
	function delAccessLog($startDt)
	{
		$queryStr = 'DELETE FROM _access_log WHERE al_dt < ?';
		$params = array($startDt);
		$ret = $this->execStatement($queryStr, $params);
		return $ret;
	}
	/**
	 * サイト解析状況を更新
	 *
	 * @param string $key		キーとなる項目値
	 * @param string $value		値
	 * @return					true = 正常、false=異常
	 */
	function updateStatus($key, $value)
	{
		$now = date("Y/m/d H:i:s");	// 現在日時
		
		// トランザクションスタート
		$startTran = false;			// この関数でトランザクションを開始したかどうか
		if (!$this->isInTransaction()){
			$this->startTransaction();
			$startTran = true;
		}
		
		$queryStr  = 'SELECT as_value FROM _analyze_status ';
		$queryStr .=   'WHERE as_id = ? ';
		$ret = $this->selectRecord($queryStr, array($key), $row);
		if ($ret){
			$queryStr  = 'UPDATE _analyze_status ';
			$queryStr .=   'SET as_value = ?, ';
			$queryStr .=     'as_update_dt = ? ';
			$queryStr .=   'WHERE as_id = ? ';
			$ret = $this->execStatement($queryStr, array($value, $now, $key));			
		} else {
			$queryStr  = 'INSERT INTO _analyze_status (';
			$queryStr .=   'as_id, ';
			$queryStr .=   'as_value, ';
			$queryStr .=   'as_update_dt ';
			$queryStr .= ') VALUES (';
			$queryStr .=   '?, ?, ?';
			$queryStr .= ')';
			$ret = $this->execStatement($queryStr, array($key, $value, $now));	
		}
		// トランザクション確定
		if ($startTran) $ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * サイト解析状況を取得
	 *
	 * @param string $key		キーとなる項目値
	 * @return string $value	値
	 */
	function getStatus($key)
	{
		$retValue = '';
		$queryStr  = 'SELECT as_value FROM _analyze_status ';
		$queryStr .=   'WHERE as_id  = ?';
		$ret = $this->selectRecord($queryStr, array($key), $row);
		if ($ret) $retValue = $row['as_value'];
		return $retValue;
	}
	/**
	 * ページビューデータを日付範囲で取得
	 *
	 * @param string	$path		アクセスポイント(NULL=すべてのデータ)
	 * @param date		$startDate	集計期間、開始日(NULL=先頭からすべて)
	 * @param date		$endDate	集計期間、終了日
	 * @param function $callback	コールバック関数
	 * @return						なし
	 */
	function getPageViewByDate($path, $startDate, $endDate, $callback)
	{
		$params = array();
		$queryStr  = 'SELECT SUM(ap_count) AS total, ap_date FROM _analyze_page_view ';
		$queryStr .=   'WHERE ap_type = ? '; $params[] = 0;
		if (is_null($startDate)){
			$queryStr .=     'AND ap_date <= ? ';
		} else {
			$queryStr .=     'AND (? <= ap_date AND ap_date <= ?) ';
			$params[] = $startDate;
		}
		$params[] = $endDate;
		if (!is_null($path)){
			$queryStr .=  'AND ap_path = ? ';
			$params[] = $path;
		}
		$queryStr .=  'GROUP BY ap_date ';
		$queryStr .=  'ORDER BY ap_date';
		$this->selectLoop($queryStr, $params, $callback);
	}
	/**
	 * 日次カウントデータを日付範囲で取得
	 *
	 * @param int       $type		データのタイプ(0=訪問数、1=訪問者数)
	 * @param string	$path		アクセスポイント(NULL=すべてのデータ)
	 * @param date		$startDate	集計期間、開始日(NULL=先頭からすべて)
	 * @param date		$endDate	集計期間、終了日
	 * @param function $callback	コールバック関数
	 * @return						なし
	 */
	function getDailyCountByDate($type, $path, $startDate, $endDate, $callback)
	{
		$params = array();
		$queryStr  = 'SELECT SUM(aa_count) AS total, aa_date FROM _analyze_daily_count ';
		$queryStr .=   'WHERE aa_type = ? '; $params[] = $type;
		$queryStr .=     'AND aa_url = ? '; $params[] = '';			/*アクセスポイントごとの集計値*/
		if (is_null($startDate)){
			$queryStr .=     'AND aa_date <= ? ';
		} else {
			$queryStr .=     'AND (? <= aa_date AND aa_date <= ?) ';
			$params[] = $startDate;
		}
		$params[] = $endDate;
		if (is_null($path)){		// すべてのアクセスポイントの場合
			$queryStr .=  'AND aa_path = ? ';
			$params[] = '';
		} else {
			$queryStr .=  'AND aa_path = ? ';
			$params[] = $path;
		}
		$queryStr .=  'GROUP BY aa_date ';
		$queryStr .=  'ORDER BY aa_date';
		$this->selectLoop($queryStr, $params, $callback);
	}
	/**
	 * 省略文字列を作成
	 *
	 * @param string  $str		変換元文字列
	 * @param int     $len		文字列長
	 * @return string			作成した文字列
	 */
	function makeTruncStr($str, $len)
	{
		if (strlen($str) > $len) $addStr = '...';
		$destStr = substr($str, 0, $len) . $addStr;
		return $destStr;
	}
	/**
	 * 日付範囲内のURL単位ページビュー数を取得
	 *
	 * @param string	$path		アクセスポイント(NULL=すべてのデータ)
	 * @param date		$startDate	集計期間、開始日(NULL=先頭からすべて)
	 * @param date		$endDate	集計期間、終了日
	 * @param int		$limit		取得する項目数
	 * @param int		$page		取得するページ(1～)
	 * @param function $callback	コールバック関数
	 * @return						なし
	 */
	function getUrlListByPageView($path, $startDate, $endDate, $limit, $page, $callback)
	{
		$offset = $limit * ($page -1);
		if ($offset < 0) $offset = 0;
		
		$params = array();
		$queryStr  = 'SELECT SUM(ap_count) AS total, ap_url AS url FROM _analyze_page_view ';
		$queryStr .=   'WHERE ap_type = ? '; $params[] = 0;
		if (is_null($startDate)){
			$queryStr .=     'AND ap_date <= ? ';
		} else {
			$queryStr .=     'AND (? <= ap_date AND ap_date <= ?) ';
			$params[] = $startDate;
		}
		$params[] = $endDate;
		if (!is_null($path)){
			$queryStr .=  'AND ap_path = ? ';
			$params[] = $path;
		}
		$queryStr .=  'GROUP BY url ';
		$queryStr .=  'ORDER BY total DESC limit ' . $limit . ' offset ' . $offset;
		$this->selectLoop($queryStr, $params, $callback);
	}
	/**
	 * 日付範囲内のURL単位ページビュー数用のURL数を取得
	 *
	 * @param string	$path		アクセスポイント(NULL=すべてのデータ)
	 * @param date		$startDate	集計期間、開始日(NULL=先頭からすべて)
	 * @param date		$endDate	集計期間、終了日
	 * @return int						総数
	 */
	function getUrlListCountByPageView($path, $startDate, $endDate)
	{
		$params = array();
		$queryStr  = 'SELECT SUM(ap_count) AS total, ap_url AS url FROM _analyze_page_view ';
		$queryStr .=   'WHERE ap_type = ? '; $params[] = 0;
		if (is_null($startDate)){
			$queryStr .=     'AND ap_date <= ? ';
		} else {
			$queryStr .=     'AND (? <= ap_date AND ap_date <= ?) ';
			$params[] = $startDate;
		}
		$params[] = $endDate;
		if (!is_null($path)){
			$queryStr .=  'AND ap_path = ? ';
			$params[] = $path;
		}
		$queryStr .=  'GROUP BY url ';
		return $this->selectRecordCount($queryStr, $params);
	}
	/**
	 * 日付範囲内のURL単位訪問数、訪問者数を取得
	 *
	 * @param int       $type		データのタイプ(0=訪問数、1=訪問者数)
	 * @param string	$path		アクセスポイント(NULL=すべてのデータ)
	 * @param date		$startDate	集計期間、開始日(NULL=先頭からすべて)
	 * @param date		$endDate	集計期間、終了日
	 * @param int		$limit		取得する項目数
	 * @param int		$page		取得するページ(1～)
	 * @param function $callback	コールバック関数
	 * @return						なし
	 */
	function getUrlListByDailyCount($type, $path, $startDate, $endDate, $limit, $page, $callback)
	{
		$offset = $limit * ($page -1);
		if ($offset < 0) $offset = 0;
		
		$params = array();
		$queryStr  = 'SELECT SUM(aa_count) AS total, aa_url AS url FROM _analyze_daily_count ';
		$queryStr .=   'WHERE aa_type = ? '; $params[] = $type;
		$queryStr .=     'AND aa_url != ? '; $params[] = '';			/*アクセスポイントごとの集計値以外*/
		if (is_null($startDate)){
			$queryStr .=     'AND aa_date <= ? ';
		} else {
			$queryStr .=     'AND (? <= aa_date AND aa_date <= ?) ';
			$params[] = $startDate;
		}
		$params[] = $endDate;
		if (is_null($path)){		// すべてのアクセスポイントの場合
			$queryStr .=  'AND aa_path = ? ';
			$params[] = '';
		} else {
			$queryStr .=  'AND aa_path = ? ';
			$params[] = $path;
		}
		$queryStr .=  'GROUP BY url ';
		$queryStr .=  'ORDER BY total DESC limit ' . $limit . ' offset ' . $offset;
		$this->selectLoop($queryStr, $params, $callback);
	}
	/**
	 * 日付範囲内のURL単位訪問数、訪問者数用のURL数を取得
	 *
	 * @param int       $type		データのタイプ(0=訪問数、1=訪問者数)
	 * @param string	$path		アクセスポイント(NULL=すべてのデータ)
	 * @param date		$startDate	集計期間、開始日(NULL=先頭からすべて)
	 * @param date		$endDate	集計期間、終了日
	 * @param int		$limit		取得する項目数
	 * @param int		$page		取得するページ(1～)
	 * @param function $callback	コールバック関数
	 * @return						なし
	 */
	function getUrlListCountByDailyCount($type, $path, $startDate, $endDate)
	{
		$params = array();
		$queryStr  = 'SELECT SUM(aa_count) AS total, aa_url AS url FROM _analyze_daily_count ';
		$queryStr .=   'WHERE aa_type = ? '; $params[] = $type;
		$queryStr .=     'AND aa_url != ? '; $params[] = '';			/*アクセスポイントごとの集計値以外*/
		if (is_null($startDate)){
			$queryStr .=     'AND aa_date <= ? ';
		} else {
			$queryStr .=     'AND (? <= aa_date AND aa_date <= ?) ';
			$params[] = $startDate;
		}
		$params[] = $endDate;
		if (is_null($path)){		// すべてのアクセスポイントの場合
			$queryStr .=  'AND aa_path = ? ';
			$params[] = '';
		} else {
			$queryStr .=  'AND aa_path = ? ';
			$params[] = $path;
		}
		$queryStr .=  'GROUP BY url ';
		return $this->selectRecordCount($queryStr, $params);
	}
}
?>
