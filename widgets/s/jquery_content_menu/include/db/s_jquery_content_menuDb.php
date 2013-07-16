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
 * @copyright  Copyright 2006-2011 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: s_jquery_content_menuDb.php 4561 2012-01-04 01:06:52Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getDbPath() . '/baseDb.php');

class s_jquery_content_menuDb extends BaseDb
{
	/**
	 * コンテンツ項目を取得
	 *
	 * @param string	$contentType		コンテンツタイプ
	 * @param function	$callback			コールバック関数
	 * @param array		$contentIdArray		コンテンツID
	 * @param string	$lang				言語
	 * @param bool		$all				すべてのデータを取得するか、ユーザ制限のないデータを取得するかを指定
	 * @param string	$now				現在日時
	 * @return 			なし
	 */
	function getContentItems($contentType, $callback, $contentIdArray, $lang, $all, $now)
	{
		$params = array();
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
