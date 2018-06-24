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
 * @copyright  Copyright 2006-2018 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getDbPath() . '/baseDb.php');

class default_menuDb extends BaseDb
{
	/**
	 * メニューIDのリストを取得
	 *
	 * @param int $deviceType		端末タイプ(0=PC、1=携帯、2=スマートフォン)
	 * @param function $callback	コールバック関数
	 * @return						なし
	 */
	function getMenuIdList($deviceType, $callback)
	{
		$queryStr  = 'SELECT * FROM _menu_id ';
		$queryStr .=   'WHERE mn_widget_id = \'\' ';		// ウィジェット制限されていないメニュー
		$queryStr .=     'AND mn_device_type = ? ';
		$queryStr .=   'ORDER BY mn_sort_order';
		$this->selectLoop($queryStr, array($deviceType), $callback);
	}
	/**
	 * メニュー項目を取得
	 *
	 * @param string $menuId		メニュー識別ID
	 * @param string $parentId		親項目ID
	 * @param string $langId		言語ID
	 * @param array  $rows			取得レコード
	 * @return						true=取得、false=取得せず
	 */
	function getChildMenuItems($menuId, $parentId, $langId, &$rows)
	{
		$now = date("Y/m/d H:i:s");	// 現在日時
		$initDt = $this->gEnv->getInitValueOfTimestamp();
		$params = array();
		
		//$queryStr  = 'SELECT md_id, md_name, md_type, md_link_type, md_link_url, md_visible, md_content_type, cn_user_limited FROM _menu_def ';
		$queryStr  = 'SELECT * FROM _menu_def ';
		if ($this->getDbType() == M3_DB_TYPE_PGSQL){		// PostgreSQLの場合
			$queryStr .=   'LEFT JOIN content ON md_content_type = ? AND md_content_id = cn_id::text AND cn_type = ? AND cn_language_id = ? AND cn_deleted = false ';
		} else {		// MySQLの場合
			$queryStr .=   'LEFT JOIN content ON md_content_type = ? AND md_content_id = cn_id AND cn_type = ? AND cn_language_id = ? AND cn_deleted = false ';
		}
		$params[] = M3_VIEW_TYPE_CONTENT;		// 汎用コンテンツ
		$params[] = '';				// PC用コンテンツ
		$params[] = $langId;
		
		// 共通の取得条件
		$queryStr .=   'WHERE md_menu_id = ? ';
		$queryStr .=     'AND md_parent_id = ? ';
		$params[] = $menuId;
		$params[] = $parentId;
		
		// 汎用コンテンツの表示条件
		$queryStr .=    'AND (md_content_type != ? OR (md_content_type = ? ';
		$queryStr .=    'AND cn_visible = true ';
		$queryStr .=    'AND (cn_active_start_dt = ? OR (cn_active_start_dt != ? AND cn_active_start_dt <= ?)) ';
		$queryStr .=    'AND (cn_active_end_dt = ? OR (cn_active_end_dt != ? AND cn_active_end_dt > ?)))) ';
		$params[] = M3_VIEW_TYPE_CONTENT;		// 汎用コンテンツ
		$params[] = M3_VIEW_TYPE_CONTENT;		// 汎用コンテンツ
		$params[] = $initDt;
		$params[] = $initDt;
		$params[] = $now;
		$params[] = $initDt;
		$params[] = $initDt;
		$params[] = $now;
		
		$queryStr .=   'ORDER BY md_index';
		$retValue = $this->selectRecords($queryStr, $params, $rows);
		return $retValue;
	}
	/**
	 * メニュー情報の取得
	 *
	 * @param string  $id			メニューID
	 * @return						true=正常、false=異常
	 */
	function getMenu($id, &$row)
	{
		$queryStr  = 'SELECT * FROM _menu_id ';
		$queryStr .=   'WHERE mn_id = ? ';
		$ret = $this->selectRecord($queryStr, array($id), $row);
		return $ret;
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
}
?>
