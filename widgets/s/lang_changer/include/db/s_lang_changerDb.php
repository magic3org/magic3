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
 * @version    SVN: $Id: s_lang_changerDb.php 4914 2012-05-22 00:50:29Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getDbPath() . '/baseDb.php');

class s_lang_changerDb extends BaseDb
{
	/**
	 * 指定言語を取得
	 *
	 * @param array		$langArray	取得言語のID
	 * @param function	$callback	コールバック関数
	 * @return			true=取得、false=取得せず
	 */
	function getLangs($langArray, $callback)
	{
		$id = '';
		for ($i = 0; $i < count($langArray); $i++){
			$id .= '\'' . addslashes($langArray[$i]) . '\',';
		}
		$id = rtrim($id, ',');
		$queryStr  = 'SELECT * FROM _language ';
		$queryStr .=   'WHERE ln_id in (' . $id . ') ';
		$queryStr .= 'ORDER BY ln_priority';
		$this->selectLoop($queryStr, array(), $callback, null);
	}
}
?>
