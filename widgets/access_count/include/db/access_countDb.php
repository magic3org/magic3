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
 * @copyright  Copyright 2006-2007 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: access_countDb.php 2 2007-11-03 04:59:01Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getDbPath() . '/baseDb.php');

class access_countDb extends BaseDb
{	
	/**
	 * 同じセッションIDのレコードがあるかどうかチェック
	 *
	 * @param string	$ssid			セッションID
	 * @return bool						true=あり、false=なし
	 */
	function isSessionExists($ssid)
	{
		// トランザクション開始
		$this->startTransaction();
		
		$queryStr = 'select * from ac_access ';
		$queryStr .=  'where ac_ssid = ?';
		$ret = $this->isRecordExists($queryStr, array($ssid));
		if ($ret){		// 存在している場合は時間を更新
			$queryStr = 'UPDATE ac_access ';
			$queryStr .=  'SET ac_time = ? ';
			$queryStr .=  'WHERE ac_ssid = ?';
			$this->execStatement($queryStr, array(time(), $ssid));
		} else {
			$queryStr = 'INSERT INTO ac_access ';
			$queryStr .=  '(ac_ssid, ac_time) ';
			$queryStr .=  'VALUES ';
			$queryStr .=  '(?, ?)';
			$this->execStatement($queryStr, array($ssid, time()));
		}
		
		// トランザクション確定
		$this->endTransaction();
		return $ret;
	}
	/**
	 * カウンタを更新
	 *
	 * @param string	$date			年月日(yyyy-mm-dd)
	 * @return なし
	 */
	function incrementCount($date)
	{
		// トランザクション開始
		$this->startTransaction();
		
		$queryStr = 'select * from ac_count ';
		$queryStr .=  'where co_date = ?';
		$ret = $this->selectRecord($queryStr, array($date), $row);
		if ($ret){		// 存在している場合はカウンタを更新
			$count = $row['co_count'] + 1;
			$queryStr = 'UPDATE ac_count ';
			$queryStr .=  'SET co_count = ? ';
			$queryStr .=  'WHERE co_date = ?';
			$this->execStatement($queryStr, array($count, $date));
		} else {
			$queryStr = 'INSERT INTO ac_count ';
			$queryStr .=  '(co_date, co_count) ';
			$queryStr .=  'VALUES ';
			$queryStr .=  '(?, ?)';
			$this->execStatement($queryStr, array($date, 1));
		}
		
		// トランザクション確定
		$this->endTransaction();
		return $ret;
	}
	
	/**
	 * 現在のカウントを取得
	 *
	 * @param string	$date			年月日(yyyy-mm-dd)
	 * @return なし
	 */
	function getCount($date)
	{
		$count = 0;
		$queryStr = 'select sum(co_count) as count from ac_count ';
		$queryStr .=  'where co_date <= ?';
		$ret = $this->selectRecord($queryStr, array($date), $row);
		if ($ret) $count = $row['count'];
		return $count;
	}
}
?>
