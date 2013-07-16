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
 * @version    SVN: $Id: staterateDb.php 5436 2012-12-07 09:55:12Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getDbPath() . '/baseDb.php');

class staterateDb extends BaseDb
{
	/**
	 * 都道府県を取得
	 *
	 * @param string	$coutryId			国ID
	 * @param string	$lang				言語
	 * @return bool							true=取得、false=取得せず
	 */
	function getAllState($coutryId, $lang, &$rows)
	{
		$queryStr = 'SELECT * FROM geo_zone ';
		$queryStr .=  'WHERE gz_country_id = ? AND gz_type = 1 AND gz_language_id = ? ';
		$queryStr .=  'ORDER BY gz_index ';
		$retValue = $this->selectRecords($queryStr, array($coutryId, $lang), $rows);
		return $retValue;
	}
}
?>
