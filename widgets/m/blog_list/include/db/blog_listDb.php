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
 * @copyright  Copyright 2006-2010 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: blog_listDb.php 3740 2010-10-27 01:22:39Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getDbPath() . '/baseDb.php');

class blog_listDb extends BaseDb
{
	/**
	 * ブログ一覧を取得
	 *
	 * @param function	$callback			コールバック関数
	 * @return 			なし
	 */
	function getAllBlog($callback)
	{
		$queryStr  = 'SELECT * FROM blog_id ';
		$queryStr .=   'WHERE bl_deleted = false ';		// 削除されていない
		$queryStr .=     'AND bl_visible = true ';
		$queryStr .=   'ORDER BY bl_index, bl_id';
		$this->selectLoop($queryStr, array(), $callback);
	}
}
?>
