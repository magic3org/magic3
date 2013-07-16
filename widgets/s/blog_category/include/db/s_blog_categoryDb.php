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
 * @version    SVN: $Id: s_blog_categoryDb.php 4749 2012-03-13 03:12:29Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getDbPath() . '/baseDb.php');

class s_blog_categoryDb extends BaseDb
{
	/**
	 * ブログカテゴリー一覧を取得
	 *
	 * @param function	$callback			コールバック関数
	 * @param string	$lang				言語
	 * @return 			なし
	 */
	function getAllCategory($callback, $lang)
	{
		$now = date("Y/m/d H:i:s");	// 現在日時
		$initDt = $this->gEnv->getInitValueOfTimestamp();		// 日時初期化値
		$params = array();
		
		$queryStr  = 'SELECT COUNT(*) AS total, bw_category_id, bc_id, bc_name FROM blog_entry_with_category ';
		$queryStr .=   'LEFT JOIN blog_entry ON bw_entry_serial = be_serial AND be_deleted = false ';
		$queryStr .=   'LEFT JOIN blog_category ON bw_category_id = bc_id AND bc_language_id = ? AND bc_deleted = false ';	$params[] = $lang;
		// 記事の取得条件
		$queryStr .= 'WHERE be_serial IS NOT NULL ';
		$queryStr .=   'AND be_status = 2 ';		// 公開中
		$queryStr .=   'AND be_regist_dt <= ? ';	$params[] = $now;		// 投稿日時が現在日時よりも過去のものを取得
		$queryStr .=   'AND (be_active_start_dt = ? OR (be_active_start_dt != ? AND be_active_start_dt <= ?)) ';
		$queryStr .=   'AND (be_active_end_dt = ? OR (be_active_end_dt != ? AND be_active_end_dt > ?)) ';
		$params[] = $initDt;
		$params[] = $initDt;
		$params[] = $now;
		$params[] = $initDt;
		$params[] = $initDt;
		$params[] = $now;
		// カテゴリの取得条件
		$queryStr .=   'AND bc_id IS NOT NULL ';
		$queryStr .=   'AND bc_visible = true ';		// 表示状態
		$queryStr .=   'GROUP BY bw_category_id, bc_id, bc_name ';
		$queryStr .=   'ORDER BY bc_sort_order';		
		$this->selectLoop($queryStr, $params, $callback);
	}
}
?>
