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
 * @version    SVN: $Id: update_content_boxDb.php 2447 2009-10-22 05:00:59Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getDbPath() . '/baseDb.php');

class update_content_boxDb extends BaseDb
{
	/**
	 * 最近の更新順にコンテンツリストを取得
	 *
	 * @param string   $langId		言語
	 * @param string   $typeId		コンテンツタイプ
	 * @param bool	   $all			すべてのデータを取得するか、ユーザ制限のないデータを取得するかを指定
	 * @param string   $now			現在日時
	 * @param function $callback	コールバック関数
	 * @return						なし
	 */
	function getContentList($langId, $typeId, $all, $now, $callback)
	{
		$initDt = $this->gEnv->getInitValueOfTimestamp();
		$params = array();
		
		$queryStr  = 'SELECT * FROM content ';
		$queryStr .=   'WHERE cn_deleted = false ';
		$queryStr .=     'AND cn_visible = true ';
		$queryStr .=     'AND cn_type = ? ';$params[] = $typeId;
		$queryStr .=     'AND cn_language_id = ? ';$params[] = $langId;
		if (!$all) $queryStr .=    'AND cn_user_limited = false ';		// ユーザ制限のないデータ
		
		// 公開期間を指定
		$queryStr .=    'AND (cn_active_start_dt = ? OR (cn_active_start_dt != ? AND cn_active_start_dt <= ?)) ';
		$queryStr .=    'AND (cn_active_end_dt = ? OR (cn_active_end_dt != ? AND cn_active_end_dt > ?)) ';
		$params[] = $initDt;
		$params[] = $initDt;
		$params[] = $now;
		$params[] = $initDt;
		$params[] = $initDt;
		$params[] = $now;
		
		$queryStr .=   'ORDER BY cn_create_dt DESC';
		$this->selectLoop($queryStr, $params, $callback);
	}
}
?>
