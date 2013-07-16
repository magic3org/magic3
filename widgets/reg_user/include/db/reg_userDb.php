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
 * @copyright  Copyright 2006-2009 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: reg_userDb.php 2023 2009-06-30 11:57:01Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getDbPath() . '/baseDb.php');

class reg_userDb extends BaseDb
{
	/**
	 * 新規ユーザの追加
	 *
	 * @param string  $name			名前
	 * @param string  $account		アカウント
	 * @param string  $password		パスワード
	 * @param string  $widgetId		ウィジェットID
	 * @param int     $newId		新規に作成したログインユーザID
	 * @return						true=成功、false=失敗
	 */
	function addUser($name, $account, $password, $widgetId, &$newId)
	{
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $this->gEnv->getCurrentUserId();	// 現在のユーザ
		
		// トランザクション開始
		$this->startTransaction();
		
		// 新規IDを作成
		$newId = 1;
		$queryStr = 'select max(lu_id) as ms from _login_user ';
		$ret = $this->selectRecord($queryStr, array(), $row);
		if ($ret) $newId = $row['ms'] + 1;
		
		// ユーザ種別を設定
		$userType = UserInfo::USER_TYPE_NOT_AUTHENTICATED;		// 未承認ユーザ
		
		// 新規レコードを追加
		$queryStr  = 'INSERT INTO _login_user (';
		$queryStr .=   'lu_id, ';
		$queryStr .=   'lu_history_index, ';
		$queryStr .=   'lu_name, ';
		$queryStr .=   'lu_account, ';
		$queryStr .=   'lu_password, ';
		$queryStr .=   'lu_user_type, ';
		$queryStr .=   'lu_assign, ';
		$queryStr .=   'lu_enable_login, ';
		$queryStr .=   'lu_widget_id, ';
		$queryStr .=   'lu_create_user_id, ';
		$queryStr .=   'lu_create_dt ';
		$queryStr .= ') VALUES (';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   'md5(?), ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?) ';
		$ret = $this->execStatement($queryStr, array($newId, 0, $name, $account, $password, $userType, '', 1, $widgetId, $userId, $now));

		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
}
?>
