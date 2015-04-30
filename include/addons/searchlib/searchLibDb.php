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
 * @copyright  Copyright 2006-2015 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getDbPath() . '/baseDb.php');

class searchLibDb extends BaseDb
{
	/**
	 * イベント項目数を取得(管理用)
	 *
	 * @param string	$langId				言語
	 * @param timestamp	$startDt			期間(開始日)
	 * @param timestamp	$endDt				期間(終了日)
	 * @param array		$category			カテゴリーID
	 * @param array     $keywords			検索キーワード
	 * @return int							項目数
	 */
	function getEventCount($langId, $startDt, $endDt, $category, $keywords)
	{
		return $this->_findEvent(0/*検索数取得*/, $langId, $startDt, $endDt, $category, $keywords);
	}
	/**
	 * イベント項目一覧を取得(管理用)
	 *
	 * @param string	$langId				言語
	 * @param timestamp	$startDt			期間(開始日)
	 * @param timestamp	$endDt				期間(終了日)
	 * @param array		$category			カテゴリーID
	 * @param array     $keywords			検索キーワード
	 * @param int       $order				検索ソート順(0=降順、1=昇順)
	 * @param int		$limit				取得する項目数
	 * @param int		$page				取得するページ(1～)
	 * @param function	$callback			コールバック関数
	 * @return 			なし
	 */
	function getEvent($langId, $startDt, $endDt, $category, $keywords, $order, $limit, $page, $callback)
	{
		$this->_findEvent(1/*検索データ取得*/, $langId, $startDt, $endDt, $category, $keywords, $order, $limit, $page, $callback);
	}
	/**
	 * イベント項目一覧を取得(管理用)
	 *
	 * @param int       $operation			操作(0=検索数取得、1=検索データ取得)
	 * @param string	$langId				言語
	 * @param timestamp	$startDt			期間(開始日)
	 * @param timestamp	$endDt				期間(終了日)
	 * @param array		$category			カテゴリーID
	 * @param array     $keywords			検索キーワード
	 * @param int       $order				検索ソート順(0=降順、1=昇順)[データ取得時のみ]
	 * @param int		$limit				取得する項目数[データ取得時のみ]
	 * @param int		$page				取得するページ(1～)[データ取得時のみ]
	 * @param function	$callback			コールバック関数[データ取得時のみ]
	 * @return 			int					項目数(検索データループ処理の場合は0を返す)
	 */
	function _findEvent($operation, $langId, $startDt, $endDt, $category, $keywords, $order = 0, $limit = 0, $page = 0, $callback = null)
	{
		$params = array();
		if (count($category) == 0){		// カテゴリー指定なしのとき
			$queryStr = 'SELECT * FROM event_entry ';
			$queryStr .=  'WHERE ee_language_id = ? '; $params[] = $langId;
			$queryStr .=    'AND ee_deleted = false ';		// 削除されていない
		} else {
			$queryStr = 'SELECT distinct(ee_serial) FROM event_entry RIGHT JOIN event_entry_with_category ON ee_serial = ew_entry_serial ';
			$queryStr .=  'WHERE ee_language_id = ? '; $params[] = $langId;
			$queryStr .=    'AND ee_deleted = false ';		// 削除されていない
			
			// 記事カテゴリー
			$queryStr .=    'AND ew_category_id in (' . implode(",", $category) . ') ';
		}
		// 検索キーワード条件
		// 名前、予定、結果、概要、管理者用備考、場所、連絡先を検索
		if (!empty($keywords)){
			if (!is_array($keywords)) $keywords = array($keywords);
			for ($i = 0; $i < count($keywords); $i++){
				$keyword = addslashes($keywords[$i]);// 「'"\」文字をエスケープ
			
				$queryStr .=    'AND (ee_name LIKE \'%' . $keyword . '%\' ';
				$queryStr .=    'OR ee_html LIKE \'%' . $keyword . '%\' ';
				$queryStr .=    'OR ee_html_ext LIKE \'%' . $keyword . '%\' ';
				$queryStr .=    'OR ee_summary LIKE \'%' . $keyword . '%\' ';
				$queryStr .=    'OR ee_admin_note LIKE \'%' . $keyword . '%\' ';
				$queryStr .=    'OR ee_place LIKE \'%' . $keyword . '%\' ';
				$queryStr .=    'OR ee_contact LIKE \'%' . $keyword . '%\') ';
			}
		}
		
		// 日付範囲
		if (!empty($startDt)){
			$queryStr .=    'AND ? <= ee_start_dt ';
			$params[] = $startDt;
		}
		if (!empty($endDt)){
			$queryStr .=    'AND ee_start_dt < ? ';
			$params[] = $endDt;
		}
		
		if (count($category) == 0){
			$queryStr .=  'ORDER BY ee_start_dt desc, ee_id limit ' . $limit . ' offset ' . $offset;
			$this->selectLoop($queryStr, $params, $callback, null);
		} else {
			// シリアル番号を取得
			$serialArray = array();
			$ret = $this->selectRecords($queryStr, $params, $serialRows);
			if ($ret){
				for ($i = 0; $i < count($serialRows); $i++){
					$serialArray[] = $serialRows[$i]['ee_serial'];
				}
			}
			$serialStr = implode(',', $serialArray);
			if (empty($serialStr)) $serialStr = '0';	// 0レコードのときはダミー値を設定
		
			if (empty($operation)){			// 検索数取得の場合
				$queryStr = 'SELECT * FROM event_entry ';
				$queryStr .=  'WHERE ee_serial in (' . $serialStr . ') ';
				return $this->selectRecordCount($queryStr, array());
			} else {
				$offset = $limit * ($page -1);
				if ($offset < 0) $offset = 0;
				
				$queryStr = 'SELECT * FROM event_entry ';
				$queryStr .=  'WHERE ee_serial in (' . $serialStr . ') ';
				$queryStr .=  'ORDER BY ee_start_dt desc, ee_id limit ' . $limit . ' offset ' . $offset;
				$this->selectLoop($queryStr, array(), $callback);
				return 0;
			}
		}
	}
}
?>
