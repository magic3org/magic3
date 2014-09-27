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
 * @copyright  Copyright 2006-2014 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getDbPath() . '/baseDb.php');

class default_login_boxDb extends BaseDb
{
	/**
	 * 利用可能なブログのブログIDを取得
	 *
	 * @param function	$callback			コールバック関数
	 * @return 								なし
	 */
	function getAvailableBlogId($callback)
	{
		$params = array();
		$queryStr  = 'SELECT * FROM blog_id ';
		$queryStr .=   'WHERE bl_deleted = false ';		// 削除されていない
		if (!$this->gEnv->isSystemManageUser()){		// コンテンツ編集可能ユーザの場合
			$queryStr .=     'AND bl_owner_id = ? '; $params[] = $this->gEnv->getCurrentUserId();	// 現在のユーザ
		}
		$queryStr .=  'ORDER BY bl_index, bl_id';
		$this->selectLoop($queryStr, $params, $callback);
	}
}
?>
