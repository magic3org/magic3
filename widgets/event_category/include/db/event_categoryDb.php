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

class event_categoryDb extends BaseDb
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
		$queryStr = 'SELECT distinct(ee_serial) FROM event_entry RIGHT JOIN event_entry_with_category ON ee_serial = ew_entry_serial ';
		$queryStr .=  'WHERE ee_deleted = false ';		// 削除されていない
		$queryStr .=    'AND ee_status = ? ';		$params[] = 2;	// 「公開」(2)データを表示
		$queryStr .=    'AND ee_language_id = ? ';	$params[] = $langId;
		$queryStr .=    'AND ew_category_id = ? '; $params[] = $categoryId;// 記事カテゴリー
		
		// シリアル番号の記事を取得
		$serialArray = array();
		$ret = $this->selectRecords($queryStr, $params, $serialRows);
		if ($ret){
			for ($i = 0; $i < count($serialRows); $i++){
				$serialArray[] = $serialRows[$i]['ee_serial'];
			}
		}
		$serialStr = implode(',', $serialArray);
		if (empty($serialStr)) $serialStr = '0';	// 0レコードのときはダミー値を設定
	
		$queryStr = 'SELECT * FROM event_entry ';
		$queryStr .=  'WHERE ee_serial in (' . $serialStr . ') ';
		$queryStr .=  'ORDER BY ee_start_dt DESC LIMIT ' . $itemCount;		// 投稿順
		$this->selectLoop($queryStr, array(), $callback);
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
		$queryStr  = 'SELECT * FROM event_category ';
		$queryStr .=   'WHERE ec_deleted = false ';	// 削除されていない
		$queryStr .=   'AND ec_id = ? ';
		$queryStr .=   'AND ec_language_id = ? ';
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
		$queryStr = 'SELECT * FROM event_category LEFT JOIN _login_user ON ec_create_user_id = lu_id AND lu_deleted = false ';
		$queryStr .=  'WHERE ec_language_id = ? ';
		$queryStr .=    'AND ec_deleted = false ';		// 削除されていない
		$queryStr .=  'ORDER BY ec_sort_order';
		$this->selectLoop($queryStr, array($lang), $callback);
	}
}
?>
