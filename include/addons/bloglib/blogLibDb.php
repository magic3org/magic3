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
 * @copyright  Copyright 2006-2016 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getDbPath() . '/baseDb.php');

class blogLibDb extends BaseDb
{
	/**
	 * エントリー項目を取得
	 *
	 * @param int		$id					エントリーID
	 * @param string	$langId				言語
	 * @param array     $row				レコード
	 * @return bool							取得 = true, 取得なし= false
	 */
	function getEntryItem($id, $langId, &$row)
	{
		$queryStr  = 'SELECT * FROM blog_entry LEFT JOIN blog_id ON be_blog_id = bl_id AND bl_deleted = false ';
		$queryStr .=   'WHERE be_deleted = false ';	// 削除されていない
		$queryStr .=   'AND be_id = ? ';
		$queryStr .=   'AND be_language_id = ? ';
		$queryStr .=   'AND be_history_index >= 0 ';		// 正規(Regular)記事を対象
		$ret = $this->selectRecord($queryStr, array($id, $langId), $row);
		return $ret;
	}
	/**
	 * ブログ情報を識別IDで取得(管理用)
	 *
	 * @param string	$id					識別ID
	 * @param array     $row				レコード
	 * @return bool							取得 = true, 取得なし= false
	 */
	function getBlogInfoById($id, &$row)
	{
		$queryStr = 'SELECT * FROM blog_id ';
		$queryStr .=  'WHERE bl_deleted = false ';
		$queryStr .=  'AND bl_id = ? ';
		$ret = $this->selectRecord($queryStr, array($id), $row);
		return $ret;
	}
	/**
	 * ブログ定義値を取得をすべて取得
	 *
	 * @param array  $rows			レコード
	 * @return bool					1行以上取得 = true, 取得なし= false
	 */
	function getAllConfig(&$rows)
	{
		$queryStr  = 'SELECT * FROM blog_config ';
		$queryStr .=   'ORDER BY bg_index';
		$retValue = $this->selectRecords($queryStr, array(), $rows);
		return $retValue;
	}
}
?>
