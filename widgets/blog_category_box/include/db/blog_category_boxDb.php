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
 * @copyright  Copyright 2006-2010 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: blog_category_boxDb.php 3820 2010-11-14 11:11:58Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getDbPath() . '/baseDb.php');

class blog_category_boxDb extends BaseDb
{
	/**
	 * エントリー項目を取得(表示用)
	 *
	 * @param int 		$itemCount			取得項目数(0のときは該当すべて)
	 * @param string	$langId				言語
	 * @param string	$categoryId			カテゴリID
	 * @param string	$now				現在日時
	 * @param function	$callback			コールバック関数
	 * @return 			なし
	 */
	function getEntryItems($itemCount, $langId, $categoryId, $now, $callback)
	{
		$initDt = $this->gEnv->getInitValueOfTimestamp();
		$params = array();
		
		// カテゴリに属する記事のシリアル番号を取得
		$queryStr = 'SELECT distinct(be_serial) FROM blog_entry RIGHT JOIN blog_entry_with_category ON be_serial = bw_entry_serial ';
		$queryStr .=  'WHERE be_deleted = false ';		// 削除されていない
		$queryStr .=    'AND be_status = ? ';		$params[] = 2;	// 「公開」(2)データを表示
		$queryStr .=    'AND be_language_id = ? ';	$params[] = $langId;
		$queryStr .=    'AND bw_category_id = ? '; $params[] = $categoryId;// 記事カテゴリー

		// 公開期間を指定
		$queryStr .=    'AND (be_active_start_dt = ? OR (be_active_start_dt != ? AND be_active_start_dt <= ?)) ';
		$queryStr .=    'AND (be_active_end_dt = ? OR (be_active_end_dt != ? AND be_active_end_dt > ?)) ';
		$params[] = $initDt;
		$params[] = $initDt;
		$params[] = $now;
		$params[] = $initDt;
		$params[] = $initDt;
		$params[] = $now;
		
		// シリアル番号の記事を取得
		$serialArray = array();
		$ret = $this->selectRecords($queryStr, $params, $serialRows);
		if ($ret){
			for ($i = 0; $i < count($serialRows); $i++){
				$serialArray[] = $serialRows[$i]['be_serial'];
			}
		}
		$serialStr = implode(',', $serialArray);
		if (empty($serialStr)) $serialStr = '0';	// 0レコードのときはダミー値を設定
	
		$queryStr = 'SELECT * FROM blog_entry LEFT JOIN _login_user ON be_regist_user_id = lu_id AND lu_deleted = false ';
		$queryStr .=  'WHERE be_serial in (' . $serialStr . ') ';
		$queryStr .=  'ORDER BY be_regist_dt DESC LIMIT ' . $itemCount;		// 投稿順
		$this->selectLoop($queryStr, array(), $callback, null);
	}
	/**
	 * ブログカテゴリーをカテゴリーIDで取得
	 *
	 * @param int		$id					カテゴリーID
	 * @param string	$langId				言語ID
	 * @param array     $row				レコード
	 * @return bool							取得 = true, 取得なし= false
	 */
	function getCategoryByCategoryId($id, $langId, &$row)
	{
		$queryStr  = 'SELECT * FROM blog_category ';
		$queryStr .=   'WHERE bc_deleted = false ';	// 削除されていない
		$queryStr .=   'AND bc_id = ? ';
		$queryStr .=   'AND bc_language_id = ? ';
		$ret = $this->selectRecord($queryStr, array($id, $langId), $row);
		return $ret;
	}
	/**
	 * ブログカテゴリー一覧を取得
	 *
	 * @param function	$callback			コールバック関数
	 * @param string	$lang				言語
	 * @return 			なし
	 */
	function getAllCategory($callback, $lang)
	{
		$queryStr = 'SELECT * FROM blog_category LEFT JOIN _login_user ON bc_create_user_id = lu_id AND lu_deleted = false ';
		$queryStr .=  'WHERE bc_language_id = ? ';
		$queryStr .=    'AND bc_deleted = false ';		// 削除されていない
		$queryStr .=  'ORDER BY bc_id';
		$this->selectLoop($queryStr, array($lang), $callback, null);
	}
}
?>
