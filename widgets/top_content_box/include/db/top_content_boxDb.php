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
 * @copyright  Copyright 2006-2008 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: top_content_boxDb.php 2446 2009-10-22 05:00:48Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getDbPath() . '/baseDb.php');

class top_content_boxDb extends BaseDb
{
	/**
	 * 閲覧数が多い順にコンテンツリストを取得
	 *
	 * @param string   $typeId		コンテンツタイプ
	 * @param bool	   $all			すべてのデータを取得するか、ユーザ制限のないデータを取得するかを指定
	 * @param string	$now				現在日時
	 * @param function $callback	コールバック関数
	 * @return						なし
	 */
	function getContentList($typeId, $all, $now, $callback)
	{
		$initDt = $this->gEnv->getInitValueOfTimestamp();
		$params = array();
		
		$queryStr  = 'SELECT vc_content_serial,SUM(vc_count) AS total,cn_id,cn_name,cn_deleted,cn_visible FROM _view_count LEFT JOIN content ON vc_content_serial = cn_serial ';
		$queryStr .=   'WHERE vc_type_id = ? ';	$params[] = $typeId;
		$queryStr .=     'AND cn_deleted = false ';
		$queryStr .=     'AND cn_visible = true ';
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
		
		$queryStr .=   'GROUP BY vc_content_serial ';
		$queryStr .=   'ORDER BY total DESC';
		$this->selectLoop($queryStr, $params, $callback);
	}
}
?>
