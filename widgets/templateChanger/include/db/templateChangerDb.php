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
 * @copyright  Copyright 2006-2011 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: templateChangerDb.php 4456 2011-11-15 22:29:21Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getDbPath() . '/baseDb.php');

class templateChangerDb extends BaseDb
{
	/**
	 * テンプレートリスト(携帯用以外)取得
	 *
	 * @param function $callback	コールバック関数
	 * @return						なし
	 */
	function getAllTemplateList($callback)
	{
		// tm_device_typeは後で追加したため、tm_mobileを残しておく
		$queryStr  = 'SELECT * FROM _templates ';
		$queryStr .=   'WHERE tm_deleted = false ';// 削除されていない
		$queryStr .=     'AND tm_mobile = false ';		// 携帯用ではない
		$queryStr .=     'AND tm_device_type = 0 ';		// PC用テンプレート
		$queryStr .=     'AND tm_available = true ';		// 利用可能
		$queryStr .=   'ORDER BY tm_id';
		$this->selectLoop($queryStr, array(), $callback);
	}
}
?>
