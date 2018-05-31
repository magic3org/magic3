<?php
/**
 * DBクラス
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    パンくずリスト
 * @author     株式会社 毎日メディアサービス
 * @copyright  Copyright 2010-2018 株式会社 毎日メディアサービス.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: breadcrumbDb.php 3521 2010-08-23 04:08:41Z fishbone $
 * @link       http://www.m-media.co.jp
 */
require_once($gEnvManager->getDbPath() . '/baseDb.php');

class breadcrumbDb extends BaseDb
{
	/**
	 * 有効なメニューIDを取得
	 *
	 * @param bool   $useHiddenMenu	非表示中のメニューの定義を使用するかどうか
	 * @param string $pageId		ページID
	 * @param string $subpage    	ページサブID
	 * @param array  $rows			レコード
	 * @param int    $setId			定義セットID
	 * @return bool					1行以上取得 = true, 取得なし= false
	 */
	function getMenuId($useHiddenMenu, $pageId, $pageSubId, &$rows, $setId = 0)
	{
		global $gEnvManager;
		
		$params = array();
		$initDt = $gEnvManager->getInitValueOfTimestamp();		// 日時初期化値
		$now = date("Y/m/d H:i:s");	// 現在日時
		
		$queryStr  = 'SELECT DISTINCT pd_menu_id, pd_sub_id, mn_sort_order, pd_except_sub_id, ';
		$queryStr .=   'CASE pd_sub_id ';
		$queryStr .=     'WHEN \'\' THEN -1 ';
		$queryStr .=     'ELSE pg_priority ';
		$queryStr .=   'END AS idx ';
		$queryStr .= 'FROM (_page_def LEFT JOIN _widgets ON pd_widget_id = wd_id AND wd_deleted = false) ';
		$queryStr .=   'LEFT JOIN _page_id ON pd_sub_id = pg_id AND pg_type = 1 ';// ページサブID
		$queryStr .=   'LEFT JOIN _menu_id ON pd_menu_id = mn_id ';// メニューID
		$queryStr .= 'WHERE pd_id = ? '; $params[] = $pageId;
		$queryStr .=   'AND (pd_sub_id = ? or pd_sub_id = \'\') '; $params[] = $pageSubId;	// 空の場合は共通項目
		$queryStr .=   'AND pd_set_id = ? '; $params[] = $setId;
		$queryStr .=   'AND pd_menu_id != \'\' ';				// メニューIDが設定されている
		if (empty($useHiddenMenu)) $queryStr .=   'AND pd_visible = true ';			// ウィジェットは表示中
		$queryStr .=   'AND wd_deleted = false ';			// ウィジェットは削除されていない

		// ##### ウィジェットの表示状態をチェック
		// システム運用者以外の場合は、ユーザのログイン状況をチェック
		if (!$gEnvManager->isSystemManageUser()){
			if ($gEnvManager->isCurrentUserLogined()){		// ログイン中のとき
				$queryStr .=     'AND (pd_view_control_type = 0 OR pd_view_control_type = 1) ';		// ログイン時のみ表示
			} else {
				$queryStr .=     'AND (pd_view_control_type = 0 OR pd_view_control_type = 2) ';		// 非ログイン時のみ表示
			}
		}
		
		// 公開期間を指定
		$queryStr .=    'AND (pd_active_start_dt = ? OR (pd_active_start_dt != ? AND pd_active_start_dt <= ?)) ';
		$queryStr .=    'AND (pd_active_end_dt = ? OR (pd_active_end_dt != ? AND pd_active_end_dt > ?)) ';
		$params[] = $initDt;
		$params[] = $initDt;
		$params[] = $now;
		$params[] = $initDt;
		$params[] = $initDt;
		$params[] = $now;
			
		$queryStr .= 'ORDER BY idx, mn_sort_order';
		$retValue = $this->selectRecords($queryStr, $params, $rows);
		return $retValue;
	}
	/**
	 * メニュー項目を取得
	 *
	 * @param array $menuIdArray	メニュー識別ID
	 * @param array  $rows			取得レコード
	 * @return						true=取得、false=取得せず
	 */
	function getMenuItems($menuIdArray, &$rows)
	{
		$queryStr = 'SELECT *, ';
		$menuId = '';
		for ($i = 0; $i < count($menuIdArray); $i++){
			$menuId .= '\'' . addslashes($menuIdArray[$i]) . '\',';
		}
		$menuId = trim($menuId, ',');
		
		// CASE文作成
		$caseStr = 'CASE md_menu_id ';
		for ($i = 0; $i < count($menuIdArray); $i++){
			$caseStr .= 'WHEN \'' . $menuIdArray[$i] . '\' THEN ' . $i . ' ';
		}
		$caseStr .= 'END AS no ';
			 
		$queryStr .= $caseStr;
		$queryStr .=  'FROM _menu_def ';
		$queryStr .=  'WHERE md_menu_id in (' . $menuId . ') ';
		$queryStr .=  'ORDER BY no, md_index';
		$retValue = $this->selectRecords($queryStr, array(), $rows);
		return $retValue;
	}
	/**
	 * ページ情報取得
	 *
	 * @param string $pageSubId		ページサブID
	 * @param array $row			取得データ
	 * @return bool					true=成功、false=失敗
	 */
	function getPageRecord($pageSubId, &$row)
	{
		$queryStr = 'SELECT * FROM _page_id ';
		$queryStr .=  'WHERE pg_type = 1 ';
		$queryStr .=  'AND pg_id = ?';
		return $this->selectRecord($queryStr, array($pageSubId), $row);
	}
}
?>
