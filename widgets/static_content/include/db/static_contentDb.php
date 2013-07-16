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
 * @copyright  Copyright 2006-2012 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: static_contentDb.php 4690 2012-02-16 15:37:54Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getDbPath() . '/baseDb.php');

class static_contentDb extends BaseDb
{
	/**
	 * コンテンツ項目をコンテンツIDで取得
	 *
	 * @param string	$contentType		コンテンツタイプ
	 * @param string	$contentId			コンテンツID
	 * @param string	$langId				言語ID
	 * @param string	$now				現在日時
	 * @param array     $row				レコード
	 * @return bool							取得 = true, 取得なし= false
	 */
	function getContentByContentId($contentType, $contentId, $langId, $now, &$row)
	{
		$params = array();
		$initDt = $this->gEnv->getInitValueOfTimestamp();
		
		$queryStr  = 'SELECT * FROM content ';
		$queryStr .=   'WHERE cn_deleted = false ';	// 削除されていない
		$queryStr .=     'AND cn_type = ? ';
		$queryStr .=     'AND cn_id = ? ';
		$queryStr .=     'AND cn_language_id = ? ';
		$params[] = $contentType;
		$params[] = $contentId;
		$params[] = $langId;
			
		// 公開期間を指定
		$queryStr .=    'AND (cn_active_start_dt = ? OR (cn_active_start_dt != ? AND cn_active_start_dt <= ?)) ';
		$queryStr .=    'AND (cn_active_end_dt = ? OR (cn_active_end_dt != ? AND cn_active_end_dt > ?))';
		$params[] = $initDt;
		$params[] = $initDt;
		$params[] = $now;
		$params[] = $initDt;
		$params[] = $initDt;
		$params[] = $now;
		$ret = $this->selectRecord($queryStr, $params, $row);
		return $ret;
	}
	/**
	 * コンテンツ項目をシリアル番号で取得
	 *
	 * @param string	$serial				シリアル番号
	 * @param array     $row				レコード
	 * @return bool							取得 = true, 取得なし= false
	 */
	function getContentBySerial($serial, &$row)
	{
		$queryStr  = 'SELECT * FROM content LEFT JOIN _login_user ON cn_create_user_id = lu_id AND lu_deleted = false ';
		$queryStr .=   'WHERE cn_serial = ? ';
		$ret = $this->selectRecord($queryStr, array($serial), $row);
		return $ret;
	}
	/**
	 * コンテンツ項目一覧を取得
	 *
	 * @param function	$callback			コールバック関数
	 * @param string	$lang				言語
	 * @return 			なし
	 */
	function getAllContentItems($callback, $lang)
	{
		$contentType = '';
		$queryStr = 'SELECT * FROM content ';
		$queryStr .=  'WHERE cn_type = ? ';
		$queryStr .=    'AND cn_language_id = ? ';
		$queryStr .=    'AND cn_deleted = false ';		// 削除されていない
		$queryStr .=  'ORDER BY cn_id';
		$this->selectLoop($queryStr, array($contentType, $lang), $callback, null);
	}
}
?>
