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
 * @copyright  Copyright 2006-2007 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: ecMailDb.php 89 2007-12-03 09:16:09Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getDbPath() . '/baseDb.php');

class ecMailDb extends BaseDb
{
	/**
	 * Eコマース定義値を取得
	 *
	 * @param string $key		キーとなる項目値
	 * @return string $value	値
	 */
	function getConfig($key)
	{
		$retValue = '';
		$queryStr = 'SELECT cg_value FROM commerce_config ';
		$queryStr .=  'WHERE cg_id  = ?';
		$ret = $this->selectRecord($queryStr, array($key), $row);
		if ($ret) $retValue = $row['cg_value'];
		return $retValue;
	}
	/**
	 * Eコマース定義値を更新
	 *
	 * @param string $key		キーとなる項目値
	 * @param string $value		値
	 * @return					true = 正常、false=異常
	 */
	function updateConfig($key, $value)
	{
		// データの確認
		$queryStr = 'SELECT cg_value FROM commerce_config ';
		$queryStr .=  'WHERE cg_id  = ?';
		$ret = $this->isRecordExists($queryStr, array($key));
		if ($ret){
			$queryStr = "UPDATE commerce_config SET cg_value = ? WHERE cg_id = ?";
			return $this->execStatement($queryStr, array($value, $key));
		} else {
			$queryStr = "INSERT INTO commerce_config (cg_id, cg_value) VALUES (?, ?)";
			return $this->execStatement($queryStr, array($key, $value));
		}
	}
	/**
	 * 会員情報を取得
	 *
	 * @param int $userId			ユーザID兼データ更新ユーザ
	 * @param array $row			取得レコード
	 * @return						true=成功、false=失敗
	 */
	function getMember($userId, &$row)
	{
		$queryStr = 'SELECT * FROM shop_member WHERE sm_login_user_id = ? AND sm_deleted = false';
		$ret = $this->selectRecord($queryStr, array($userId), $row);
		return $ret;
	}
	/**
	 * 個人情報を取得
	 *
	 * @param int $id				個人情報ID
	 * @param array $row			取得レコード
	 * @return						true=成功、false=失敗
	 */
	function getPersonInfo($id, &$row)
	{
		$queryStr = 'SELECT * FROM person_info WHERE pi_id = ? AND pi_deleted = false';
		$ret = $this->selectRecord($queryStr, array($id), $row);
		return $ret;
	}
	/**
	 * 住所を取得
	 *
	 * @param int $id				個人情報ID
	 * @param array $row			取得レコード
	 * @return						true=成功、false=失敗
	 */
	function getAddress($id, &$row)
	{
		$queryStr = 'SELECT * FROM address WHERE ad_id = ? AND ad_deleted = false';
		$ret = $this->selectRecord($queryStr, array($id), $row);
		return $ret;
	}
	/**
	 * IDから都道府県名を取得
	 *
	 * @param string	$coutryId			国ID
	 * @param string	$lang				言語
	 * @param string	$id					地域ID
	 * @return string						名前
	 */
	function getStateName($coutryId, $lang, $id)
	{
		$queryStr = 'SELECT * FROM geo_zone ';
		$queryStr .=  'WHERE gz_country_id = ? AND gz_type = 1 AND gz_language_id = ? AND gz_id = ?';
		$ret = $this->selectRecord($queryStr, array($coutryId, $lang, $id), $row);
		if ($ret){
			return $row['gz_name'];
		} else {
			return '';
		}
	}
}
?>
