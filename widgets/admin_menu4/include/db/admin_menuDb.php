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
 * @copyright  Copyright 2006-2014 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getDbPath() . '/baseDb.php');

class admin_menuDb extends BaseDb
{
	/**
	 * ナビゲーションバー項目を取得
	 *
	 * @param string $navId			ナビゲーションバー識別ID
	 * @param string $parentId		親項目ID
	 * @param array  $rows			取得レコード
	 * @return						true=取得、false=取得せず
	 */
	function getNavItems($navId, $parentId, &$rows)
	{
		$queryStr  = 'SELECT * FROM _nav_item ';
		$queryStr .=   'WHERE ni_nav_id = ? ';
		$queryStr .=     'AND ni_parent_id = ? ';
		$queryStr .=     'AND ni_visible = true ';
		$queryStr .=   'ORDER BY ni_index';
		
		$retValue = $this->selectRecords($queryStr, array($navId, $parentId), $rows);
		return $retValue;
	}
	/**
	 * ナビゲーションバー項目を取得(タスク指定)
	 *
	 * @param string $navId			ナビゲーションバー識別ID
	 * @param string $taskId		タスクID
	 * @param array  $row			取得レコード
	 * @return						true=取得、false=取得せず
	 */
	function getNavItemsByTask($navId, $taskId, &$row)
	{
		$queryStr  = 'SELECT * FROM _nav_item ';
		$queryStr .=   'WHERE ni_nav_id = ? ';
		$queryStr .=     'AND ni_task_id = ? ';
		$queryStr .=     'AND ni_visible = true ';
		$retValue = $this->selectRecord($queryStr, array($navId, $taskId), $row);
		return $retValue;
	}
	/**
	 * メニュー項目のタスクを更新
	 *
	 * @param string $itemId	メニュー項目ID
	 * @param bool $taskId		タスク
	 * @return					true = 正常、false=異常
	 */
	function updateNavItemMenuType($itemId, $taskId)
	{
		$sql = 'UPDATE _nav_item SET ni_task_id = ? WHERE ni_id = ?';
		$params = array($taskId, $itemId);
		$retValue =$this->execStatement($sql, $params);
		return $retValue;
	}
	/**
	 * 画面配置している主要コンテンツ編集ウィジェットを取得
	 *
	 * @param array $pageIdArray		ページID
	 * @param array $contentTypeArray    コンテンツタイプ
	 * @param array  $rows				取得レコード
	 * @param int    $setId				定義セットID
	 * @return							true=取得、false=取得せず
	 */
	function getEditWidgetOnPage($pageIdArray, $contentTypeArray, &$rows, $setId = 0)
	{
		// CASE文作成
		$caseStr = 'CASE pd_id ';
		$pageStr = '';
		for ($i = 0; $i < count($pageIdArray); $i++){
			$caseStr .= 'WHEN \'' . $pageIdArray[$i] . '\' THEN ' . $i . ' ';
			$pageStr .= '\'' . $pageIdArray[$i] . '\', ';
		}
		$caseStr .= 'END AS pageno, ';
		$pageStr = rtrim($pageStr, ', ');
		
		$caseStr .= 'CASE wd_type ';
//		$contentStr = '';
		for ($i = 0; $i < count($contentTypeArray); $i++){
			$caseStr .= 'WHEN \'' . $contentTypeArray[$i] . '\' THEN ' . $i . ' ';
//			$contentStr .= '\'' . $contentTypeArray[$i] . '\', ';
		}
		$caseStr .= 'ELSE 100 ';		// デフォルトでないメインコンテンツ編集ウィジェットは後にする
		$caseStr .= 'END AS contentno';
//		$contentStr = rtrim($contentStr, ', ');
		
		$queryStr  = 'SELECT DISTINCT pd_id, wd_id, wd_name, wd_type, wd_content_name, ' . $caseStr . ' FROM _page_def ';
		$queryStr .=   'LEFT JOIN _widgets ON pd_widget_id = wd_id AND wd_deleted = false ';
		$queryStr .=   'LEFT JOIN _page_id ON pd_sub_id = pg_id AND pg_type = 1 ';// ページサブID
		$queryStr .= 'WHERE pd_set_id = ? ';
		$queryStr .=   'AND pd_id in (' . $pageStr . ') ';
		//$queryStr .=   'AND pd_visible = true ';			// ウィジェットは表示中に限定しない
		$queryStr .=   'AND wd_deleted = false ';			// ウィジェットは削除されていない
		$queryStr .=   'AND wd_active = true ';				// 一般ユーザが実行可能かどうか
		$queryStr .=   'AND (pd_sub_id = \'\' OR pg_active = true) ';		// グローバル属性ウィジェットか公開中のページ上のウィジェット
		$queryStr .=   'AND wd_edit_content = true ';
//		$queryStr .=   'AND wd_type in (' . $contentStr . ') ';
		$queryStr .=   'AND wd_type != \'\' ';
//		$queryStr .=   'AND wd_use_instance_def = false ';		// インスタンス定義を使用しないウィジェットをメインコンテンツ編集ウィジェットとする
		$queryStr .= 'ORDER BY pageno, contentno';
		$retValue = $this->selectRecords($queryStr, array($setId), $rows);
		return $retValue;
	}
	/**
	 * 画面配置しているサブコンテンツ編集ウィジェットを取得
	 *
	 * @param array $pageIdArray		ページID
	 * @param array $contentTypeArray   コンテンツタイプ
	 * @param array  $rows				取得レコード
	 * @param int    $setId				定義セットID
	 * @return							true=取得、false=取得せず
	 */
	function getEditSubWidgetOnPage($pageIdArray, $contentTypeArray, &$rows, $setId = 0)
	{
		// CASE文作成
		$caseStr = 'CASE pd_id ';
		$pageStr = '';
		for ($i = 0; $i < count($pageIdArray); $i++){
			$caseStr .= 'WHEN \'' . $pageIdArray[$i] . '\' THEN ' . $i . ' ';
			$pageStr .= '\'' . $pageIdArray[$i] . '\', ';
		}
		$caseStr .= 'END AS pageno,';
		$pageStr = rtrim($pageStr, ', ');
		
	//	$caseStr .= 'CASE wd_type ';
		$caseStr .= 'CASE wd_content_type ';
//		$contentStr = '';
		for ($i = 0; $i < count($contentTypeArray); $i++){
			$caseStr .= 'WHEN \'' . $contentTypeArray[$i] . '\' THEN ' . $i . ' ';
//			$contentStr .= '\'' . $contentTypeArray[$i] . '\', ';
		}
		$caseStr .= 'ELSE 100 ';		// デフォルトでないメインコンテンツ編集ウィジェットは後にする
		$caseStr .= 'END AS contentno';
//		$contentStr = rtrim($contentStr, ', ');
		
//		$queryStr  = 'SELECT DISTINCT pd_id, wd_id, wd_name, wd_content_type, wd_sort_order, ' . $caseStr . ' FROM _page_def ';
		$queryStr  = 'SELECT DISTINCT pd_id, wd_id, wd_name, wd_content_type, wd_sort_order, wd_content_widget_id, ' . $caseStr . ' FROM _page_def ';
		$queryStr .=   'LEFT JOIN _widgets ON pd_widget_id = wd_id AND wd_deleted = false ';
		$queryStr .=   'LEFT JOIN _page_id ON pd_sub_id = pg_id AND pg_type = 1 ';// ページサブID
		$queryStr .= 'WHERE pd_set_id = ? ';
		$queryStr .=   'AND pd_id in (' . $pageStr . ') ';
		//$queryStr .=   'AND pd_visible = true ';			// ウィジェットは表示中に限定しない
		$queryStr .=   'AND wd_deleted = false ';			// ウィジェットは削除されていない
		$queryStr .=   'AND wd_active = true ';				// 一般ユーザが実行可能かどうか
		$queryStr .=   'AND (pd_sub_id = \'\' OR pg_active = true) ';		// グローバル属性ウィジェットか公開中のページ上のウィジェット
		$queryStr .=   'AND wd_edit_content = true ';
		$queryStr .=   'AND wd_type = \'\' ';
//		$queryStr .=   'AND wd_use_instance_def = true ';		// インスタンス定義が必要であるウィジェットをサブコンテンツ編集ウィジェットとする
	//	$queryStr .= 'ORDER BY pageno, wd_sort_order';
		$queryStr .= 'ORDER BY pageno, contentno';
		$retValue = $this->selectRecords($queryStr, array($setId), $rows);
		return $retValue;
	}
}
?>
