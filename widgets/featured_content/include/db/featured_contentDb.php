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
 * @version    SVN: $Id: featured_contentDb.php 5529 2013-01-08 13:40:47Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getDbPath() . '/baseDb.php');

class featured_contentDb extends BaseDb
{
	/**
	 * コンテンツ項目をコンテンツIDで取得(管理用)
	 *
	 * @param string  $contentType		コンテンツタイプ
	 * @param string	$contentId		コンテンツID
	 * @param string	$langId			言語ID
	 * @param array     $row			レコード
	 * @return bool						取得 = true, 取得なし= false
	 */
	function getContentByContentId($contentType, $contentId, $langId, &$row)
	{
		$queryStr  = 'SELECT * FROM content LEFT JOIN _login_user ON cn_create_user_id = lu_id AND lu_deleted = false ';
		$queryStr .=   'WHERE cn_deleted = false ';	// 削除されていない
		$queryStr .=    'AND cn_type = ? ';
		$queryStr .=   'AND cn_id = ? ';
		$queryStr .=   'AND cn_language_id = ? ';
		$ret = $this->selectRecord($queryStr, array($contentType, $contentId, $langId), $row);
		return $ret;
	}
	/**
	 * コンテンツ項目を取得(一般用)
	 *
	 * @param string	$contentType		コンテンツタイプ
	 * @param function	$callback			コールバック関数
	 * @param array		$contentIdArray		コンテンツID
	 * @param string	$lang				言語
	 * @param bool		$all				すべてのデータを取得するか、ユーザ制限のないデータを取得するかを指定
	 * @return 			なし
	 */
	function getContentItems($contentType, $callback, $contentIdArray, $lang, $all)
	{
		$params = array();
		$now = date("Y/m/d H:i:s");	// 現在日時
		$initDt = $this->gEnv->getInitValueOfTimestamp();
		
		$contentId = implode(',', $contentIdArray);
		
		// CASE文作成
		$caseStr = 'CASE cn_id ';
		for ($i = 0; $i < count($contentIdArray); $i++){
			$caseStr .= 'WHEN ' . $contentIdArray[$i] . ' THEN ' . $i . ' ';
		}
		$caseStr .= 'END AS no';

		$queryStr = 'SELECT *, ' . $caseStr . ' FROM content ';
		$queryStr .=  'WHERE cn_deleted = false ';		// 削除されていない
		$queryStr .=    'AND cn_type = ? ';
		$queryStr .=    'AND cn_id in (' . $contentId . ') ';
		$queryStr .=    'AND cn_language_id = ? ';
		$params[] = $contentType;
		$params[] = $lang;
		
		// 取得制限
		$queryStr .=    'AND cn_visible = true ';
		if (!$all) $queryStr .=    'AND cn_user_limited = false ';		// ユーザ制限のないデータ
	
		// 公開期間を指定
		$queryStr .=    'AND (cn_active_start_dt = ? OR (cn_active_start_dt != ? AND cn_active_start_dt <= ?)) ';
		$queryStr .=    'AND (cn_active_end_dt = ? OR (cn_active_end_dt != ? AND cn_active_end_dt > ?))';
		$params[] = $initDt;
		$params[] = $initDt;
		$params[] = $now;
		$params[] = $initDt;
		$params[] = $initDt;
		$params[] = $now;

		$queryStr .=  'ORDER BY no';
		$this->selectLoop($queryStr, $params, $callback);
	}
}
?>
