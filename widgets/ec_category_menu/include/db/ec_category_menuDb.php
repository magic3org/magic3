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
 * @version    SVN: $Id: ec_category_menuDb.php 2 2007-11-03 04:59:01Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getDbPath() . '/baseDb.php');

class ec_category_menuDb extends BaseDb
{
	/**
	 * 指定したカテゴリーが親であるカテゴリーを取得(画面表示用)
	 *
	 * @param int		$id			親商品カテゴリーID(0はトップレベル)
	 * @param string	$langId				言語ID
	 * @param array		$rows		取得した行データ
	 * @return int					取得した行数
	 */
	function getChildCategoryWithRows($id, $lang, &$rows)
	{
		$retCount = 0;
		$queryStr = 'SELECT pc_id,pc_name FROM product_category ';
		$queryStr .=  'WHERE pc_deleted = false ';	// 削除されていない
		$queryStr .=    'AND pc_visible = true ';	// 表示するカテゴリー
		$queryStr .=    'AND pc_parent_id = ? ';
		$queryStr .=    'AND pc_language_id = ? ';
		$queryStr .=  'ORDER BY pc_sort_order';
		$ret = $this->selectRecords($queryStr, array($id, $lang), $rows);
		if ($ret){
			$retCount = count($rows);
		}
		return $retCount;
	}
}
?>
