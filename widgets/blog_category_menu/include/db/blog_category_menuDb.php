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
 * @version    SVN: $Id: blog_category_menuDb.php 689 2008-06-04 07:32:52Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getDbPath() . '/baseDb.php');

class blog_category_menuDb extends BaseDb
{
	/**
	 * ブログカテゴリー一覧を取得
	 *
	 * @param function	$callback			コールバック関数
	 * @param string	$lang				言語
	 * @return 			なし
	 */
	function getAllCategory($callback, $lang)
	{
		$queryStr = 'SELECT * FROM blog_category ';
		$queryStr .=  'WHERE bc_language_id = ? ';
		$queryStr .=    'AND bc_deleted = false ';		// 削除されていない
		$queryStr .=    'AND bc_visible = true ';		// 表示状態
		$queryStr .=  'ORDER BY bc_sort_order';
		$this->selectLoop($queryStr, array($lang), $callback, null);
	}
}
?>
