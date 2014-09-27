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
	const USER_ID_SEPARATOR = ',';		// ユーザIDセパレータ
	
	/**
	 * ブログ一覧を取得
	 *
	 * @param function	$callback			コールバック関数
	 * @return 			なし
	 */
	function getAllBlog($callback)
	{
		$params = array();
		$now = date("Y/m/d H:i:s");	// 現在日時
		if (!$this->gEnv->isSystemManageUser()) $userId = $this->gEnv->getCurrentUserId();		// システム管理ユーザの場合
		
		$queryStr  = 'SELECT * FROM blog_id ';
		$queryStr .=   'WHERE bl_deleted = false ';		// 削除されていない
		$queryStr .=     'AND bl_visible = true ';
		
		// ユーザ参照制限
		if (isset($userId)){
			$queryStr .=     'AND ((bl_owner_id = ? AND bl_owner_id != 0) ';	$params[] = $userId;
			$queryStr .=     'OR bl_user_limited = false ';
			$queryStr .=     'OR (bl_user_limited = true AND bl_limited_user_id = \'\' AND 0 != ' . $userId . ') ';
			$queryStr .=     'OR (bl_user_limited = true AND bl_limited_user_id != \'\' AND bl_limited_user_id LIKE \'%' . self::USER_ID_SEPARATOR . $userId . self::USER_ID_SEPARATOR . '%\')) ';
		}
		
		$queryStr .=   'ORDER BY bl_index, bl_id';
		$this->selectLoop($queryStr, $params, $callback);
	}
}
?>
