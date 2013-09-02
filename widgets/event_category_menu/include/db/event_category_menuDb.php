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
 * @copyright  Copyright 2006-2013 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getDbPath() . '/baseDb.php');

class event_category_menuDb extends BaseDb
{
	/**
	 * イベントカテゴリー一覧を取得
	 *
	 * @param function	$callback			コールバック関数
	 * @param string	$lang				言語
	 * @return 			なし
	 */
	function getAllCategory($callback, $lang)
	{
		$queryStr  = 'SELECT * FROM event_category ';
		$queryStr .=   'WHERE ec_language_id = ? ';
		$queryStr .=     'AND ec_deleted = false ';		// 削除されていない
		$queryStr .=     'AND ec_visible = true ';		// 表示状態
		$queryStr .=   'ORDER BY ec_sort_order';
		$this->selectLoop($queryStr, array($lang), $callback);
	}
}
?>
