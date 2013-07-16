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
 * @copyright  Copyright 2006-2008 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: accordion_menuDb.php 1383 2008-12-23 06:35:00Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getDbPath() . '/baseDb.php');

class accordion_menuDb extends BaseDb
{
	/**
	 * メニューIDのリストを取得
	 *
	 * @param function $callback	コールバック関数
	 * @return						なし
	 */
	function getMenuIdList($callback)
	{
		$queryStr = 'SELECT * FROM _menu_id ';
		$queryStr .=  'ORDER BY mn_sort_order';
		$this->selectLoop($queryStr, array(), $callback);
	}
	/**
	 * メニュー項目を取得
	 *
	 * @param string $menuId		メニュー識別ID
	 * @param string $parentId		親項目ID
	 * @param array  $rows			取得レコード
	 * @return						true=取得、false=取得せず
	 */
	function getChildMenuItems($menuId, $parentId, &$rows)
	{
		$queryStr  = 'SELECT * FROM _menu_def ';
		$queryStr .=   'WHERE md_menu_id = ? ';
		$queryStr .=     'AND md_parent_id = ? ';
		$queryStr .=   'ORDER BY md_index';
		$retValue = $this->selectRecords($queryStr, array($menuId, $parentId), $rows);
		return $retValue;
	}
	/**
	 * メニュー情報の取得
	 *
	 * @param string  $id			メニューID
	 * @return						true=正常、false=異常
	 */
	function getMenu($id, &$row)
	{
		$queryStr  = 'SELECT * FROM _menu_id ';
		$queryStr .=   'WHERE mn_id = ? ';
		$ret = $this->selectRecord($queryStr, array($id), $row);
		return $ret;
	}
}
?>
