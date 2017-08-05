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
 * @copyright  Copyright 2006-2017 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getDbPath() . '/baseDb.php');

class blog_category_menuDb extends BaseDb
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
		
		$queryStr = 'SELECT bc_id, bc_name, COUNT(*) AS entrycount FROM blog_category RIGHT JOIN blog_entry_with_category ON bc_id = bw_category_id LEFT JOIN blog_entry ON bw_entry_serial = be_serial ';
		$queryStr .=  'WHERE bc_language_id = ? ';		$params[] = $lang;
		$queryStr .=    'AND bc_deleted = false ';		// 削除されていない
		$queryStr .=    'AND bc_visible = true ';		// 表示状態
		
		// ブログ記事の表示条件
		$queryStr .=     'AND be_deleted = false ';		// 削除されていない
		$queryStr .=     'AND be_status = ? ';		$params[] = 2;	// 「公開」(2)データを表示
		$queryStr .=     'AND be_regist_dt <= ? ';	$params[] = $now;		// 投稿日時が現在日時よりも過去のものを取得
	
		// 公開期間を指定
		$queryStr .=    'AND (be_active_start_dt = ? OR (be_active_start_dt != ? AND be_active_start_dt <= ?)) ';
		$queryStr .=    'AND (be_active_end_dt = ? OR (be_active_end_dt != ? AND be_active_end_dt > ?)) ';
		$params[] = $initDt;
		$params[] = $initDt;
		$params[] = $now;
		$params[] = $initDt;
		$params[] = $initDt;
		$params[] = $now;
		
		$queryStr .=  'GROUP BY bc_id ';
		$queryStr .=  'ORDER BY bc_sort_order';
		$this->selectLoop($queryStr, $params, $callback);
	}
}
?>
