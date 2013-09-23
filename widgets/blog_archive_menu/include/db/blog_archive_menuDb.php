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

class blog_archive_menuDb extends BaseDb
{
	const USER_ID_SEPARATOR = ',';		// ユーザIDセパレータ
	
	/**
	 * すべてのブログ記事を取得
	 *
	 * @param string	$langId				言語
	 * @param int		$sortOrder			ソート順(0=降順、1=昇順)
	 * @param array		$rows				取得データ
	 * @return 			なし
	 */
	function getAllEntry($langId, $sortOrder, &$rows)
	{
		$params = array();
		$initDt = $this->gEnv->getInitValueOfTimestamp();
		$now = date("Y/m/d H:i:s");	// 現在日時
		if (!$this->gEnv->isSystemManageUser()) $userId = $this->gEnv->getCurrentUserId();		// システム管理ユーザの場合
		
		$queryStr = 'SELECT be_regist_dt FROM blog_entry LEFT JOIN blog_id ON be_blog_id = bl_id AND bl_deleted = false ';
		$queryStr .=  'WHERE be_deleted = false ';		// 削除されていない
		$queryStr .=    'AND be_status = ? ';		$params[] = 2;	// 「公開」(2)データを表示
		$queryStr .=    'AND be_language_id = ? ';	$params[] = $langId;
		$queryStr .=    'AND be_regist_dt <= ? ';	$params[] = $now;		// 投稿日時が現在日時よりも過去のものを取得

		// ユーザ参照制限
		if (isset($userId)){
			$queryStr .=     'AND (be_blog_id = \'\' ';
			$queryStr .=     'OR (be_blog_id != \'\' ';
			$queryStr .=     'AND ((bl_owner_id = ? AND bl_owner_id != 0) ';	$params[] = $userId;
			$queryStr .=     'OR bl_user_limited = false ';
			$queryStr .=     'OR (bl_user_limited = true AND bl_limited_user_id = \'\' AND 0 != ' . $userId . ') ';
			$queryStr .=     'OR (bl_user_limited = true AND bl_limited_user_id != \'\' AND bl_limited_user_id LIKE \'%' . self::USER_ID_SEPARATOR . $userId . self::USER_ID_SEPARATOR . '%\')))) ';
		}
			
		// 公開期間を指定
		$queryStr .=    'AND (be_active_start_dt = ? OR (be_active_start_dt != ? AND be_active_start_dt <= ?)) ';
		$queryStr .=    'AND (be_active_end_dt = ? OR (be_active_end_dt != ? AND be_active_end_dt > ?)) ';
		$params[] = $initDt;
		$params[] = $initDt;
		$params[] = $now;
		$params[] = $initDt;
		$params[] = $initDt;
		$params[] = $now;
		
		$queryStr .=  'ORDER BY be_regist_dt ';
		if (empty($sortOrder)) $queryStr .=  'desc';
		$retValue = $this->selectRecords($queryStr, $params, $rows);
		return $retValue;		
	}
}
?>
