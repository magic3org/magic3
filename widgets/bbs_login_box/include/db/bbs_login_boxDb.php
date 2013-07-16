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
 * @version    SVN: $Id: bbs_login_boxDb.php 2 2007-11-03 04:59:01Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getDbPath() . '/baseDb.php');

class bbs_login_boxDb extends BaseDb
{
	/**
	 * BBS仮会員を正会員に変更
	 *
	 * @param int $userId			ユーザID兼データ更新ユーザ
	 * @return						true=成功、false=失敗
	 */
	function makeTmpMemberToProperMember($userId)
	{
		$now = date("Y/m/d H:i:s");	// 現在日時
				
		// トランザクション開始
		$this->startTransaction();

		$queryStr = 'SELECT * FROM bbs_member WHERE sv_login_user_id = ? AND sv_deleted = false';
		$ret = $this->selectRecord($queryStr, array($userId), $row);
		if (!$ret){
			// トランザクション確定
			$this->endTransaction();
			return false;
		}
		
		// 新規レコードを追加
		$queryStr  = 'INSERT INTO bbs_member (';
		$queryStr .=   'sv_id, ';
		$queryStr .=   'sv_history_index, ';
		$queryStr .=   'sv_language_id, ';
		$queryStr .=   'sv_type, ';
		$queryStr .=   'sv_login_user_id, ';
		$queryStr .=   'sv_name, ';
		$queryStr .=   'sv_group, ';
		$queryStr .=   'sv_url, ';
		$queryStr .=   'sv_signature, ';
		$queryStr .=   'sv_regist_dt, ';
		$queryStr .=   'sv_recv_mailnews, ';
		$queryStr .=   'sv_create_user_id, ';
		$queryStr .=   'sv_create_dt ';
		$queryStr .= ') VALUES (';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?) ';
		$ret = $this->execStatement($queryStr, array($row['sv_id'], $row['sv_history_index'] + 1, $row['sv_language_id'], 1/*正会員*/, $row['sv_login_user_id'],
										$row['sv_name'], $row['sv_group'], $row['sv_url'], $row['sv_signature'], $row['sv_regist_dt'], intval($row['sv_recv_mailnews']), $userId, $now));
		
		// 古い情報を削除
		$queryStr  = 'UPDATE bbs_member ';
		$queryStr .=   'SET sv_deleted = true, ';	// 削除
		$queryStr .=     'sv_update_user_id = ?, ';
		$queryStr .=     'sv_update_dt = ? ';
		$queryStr .=   'WHERE sv_serial = ?';
		$ret = $this->execStatement($queryStr, array($userId, $now, $row['sv_serial']));
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;		
	}
}
?>
