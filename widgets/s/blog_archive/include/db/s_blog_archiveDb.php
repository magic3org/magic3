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
 * @version    SVN: $Id: s_blog_archiveDb.php 4752 2012-03-14 04:42:10Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getDbPath() . '/baseDb.php');

class s_blog_archiveDb extends BaseDb
{
	/**
	 * すべてのブログ記事を取得
	 *
	 * @param timestamp $now				現在日時(現在日時より未来の投稿日時の記事は取得しない)
	 * @param string	$langId				言語
	 * @param array		$rows				取得データ
	 * @return 			なし
	 */
	function getAllEntry($now, $langId, &$rows)
	{
		$initDt = $this->gEnv->getInitValueOfTimestamp();
		
		$queryStr = 'SELECT be_regist_dt FROM blog_entry ';
		$queryStr .=  'WHERE be_deleted = false ';		// 削除されていない
		$queryStr .=    'AND be_status = ? ';
		$queryStr .=    'AND be_language_id = ? ';
		$queryStr .=    'AND be_regist_dt <= ? ';	// 投稿日時が現在日時よりも過去のものを取得
		
		// 公開範囲を指定
		$queryStr .=    'AND (be_active_start_dt = ? OR (be_active_start_dt != ? AND be_active_start_dt <= ?)) ';
		$queryStr .=    'AND (be_active_end_dt = ? OR (be_active_end_dt != ? AND be_active_end_dt > ?)) ';
		
		$queryStr .=  'ORDER BY be_regist_dt desc';
		$retValue = $this->selectRecords($queryStr, array(2, $langId, $now, $initDt, $initDt, $now, $initDt, $initDt, $now), $rows);// 「公開」(2)データを取得
		return $retValue;
	}
}
?>
