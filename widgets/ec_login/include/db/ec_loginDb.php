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
 * @version    SVN: $Id: ec_loginDb.php 5425 2012-12-05 01:57:48Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getDbPath() . '/baseDb.php');

class ec_loginDb extends BaseDb
{
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
