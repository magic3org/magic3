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
 * @copyright  Copyright 2006-2012 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: contactus_mainDb.php 4949 2012-06-09 08:52:24Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getDbPath() . '/baseDb.php');

class contactus_mainDb extends BaseDb
{
	/**
	 * 都道府県を取得
	 *
	 * @param string	$coutryId			国ID
	 * @param string	$lang				言語
	 * @param function	$callback			コールバック関数
	 * @return 			なし
	 */
	function getAllState($coutryId, $lang, $callback)
	{
		$queryStr = 'SELECT * FROM geo_zone ';
		$queryStr .=  'WHERE gz_country_id = ? AND gz_type = 1 AND gz_language_id = ? ';
		$queryStr .=  'ORDER BY gz_index ';
		$this->selectLoop($queryStr, array($coutryId, $lang), $callback, null);
	}
	/**
	 * 都道府県名を取得
	 *
	 * @param string	$coutryId			国ID
	 * @param string	$lang				言語
	 * @param string $stateId    都道府県ID
	 * @return string		値
	 */
	function getStateName($coutryId, $lang, $stateId)
	{
		$retValue = '';
		$queryStr = 'SELECT * FROM geo_zone ';
		$queryStr .=  'WHERE gz_country_id = ? AND gz_type = 1 AND gz_language_id = ? AND gz_id = ?';
		$ret = $this->selectRecord($queryStr, array($coutryId, $lang, intval($stateId)), $row);
		if ($ret) $retValue = $row['gz_name'];
		return $retValue;
	}
}
?>
