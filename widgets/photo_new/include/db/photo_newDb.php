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
 * @version    SVN: $Id: photo_newDb.php 4649 2012-02-03 01:39:03Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getDbPath() . '/baseDb.php');

class photo_newDb extends BaseDb
{
	/**
	 * 最新画像を取得(表示用)
	 *
	 * @param int 		$itemCount			取得項目数(0のときは該当すべて)
	 * @param string	$lang				言語
	 * @param function	$callback			コールバック関数
	 * @return 			なし
	 */
	function getPhotoItems($itemCount, $lang, $callback)
	{
		$queryStr  = 'SELECT * FROM photo LEFT JOIN _login_user ON ht_owner_id = lu_id AND lu_deleted = false ';
		$queryStr .=   'WHERE ht_deleted = false ';		// 未削除
		$queryStr .=     'AND ht_visible = true ';		// 公開中
		$queryStr .=   'ORDER BY ht_regist_dt DESC LIMIT ' . $itemCount;		// 画像アップロード日時順
		$this->selectLoop($queryStr, array(), $callback);
	}
	/**
	 * フォトギャラリー定義値を取得
	 *
	 * @param string $key		キーとなる項目値
	 * @return string $value	値
	 */
	function getConfig($key)
	{
		$retValue = '';
		$queryStr = 'SELECT hg_value FROM photo_config ';
		$queryStr .=  'WHERE hg_id  = ?';
		$ret = $this->selectRecord($queryStr, array($key), $row);
		if ($ret) $retValue = $row['hg_value'];
		return $retValue;
	}
}
?>
