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
 * @version    SVN: $Id: admin_loginuserDb.php 5229 2012-09-19 12:18:58Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getDbPath() . '/baseDb.php');

class admin_loginuserDb extends BaseDb
{
	/**
	 * ユーザ情報取得
	 *
	 * @param int  $userId			ユーザID
	 * @param array $row			取得データ
	 * @return						true=正常、false=異常
	 */
	function getLoginUserInfo($userId, &$row)
	{
		$queryStr  = 'SELECT * FROM _login_user LEFT JOIN _login_user_info ON lu_id = li_id AND li_deleted = false ';
		$queryStr .=   'LEFT JOIN _login_log ON lu_id = ll_user_id ';
		$queryStr .=   'WHERE lu_deleted = false ';// 削除されていない
		$queryStr .=     'AND lu_id = ?';
		$ret = $this->selectRecord($queryStr, array($userId), $row);
		return $ret;
	}
}
?>
