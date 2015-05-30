<?php
/**
 * index.php用共通定義クラス
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2015 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
 
class member_mainCommonDef
{
	static $_deviceType = 0;				// デバイスタイプ(PC)
	static $_viewContentType = 'member';		// コンテンツタイプ
	
	// メールレンプレート
	const MAIL_TMPL_REGIST_USER_AUTO	= 'regist_user_auto';						// メールテンプレート(会員自動登録)
	const MAIL_TMPL_REGIST_USER_AUTH	= 'regist_user_auth';						// メールテンプレート(会員承認登録)
	const MAIL_TMPL_REGIST_USER_AUTH_ADMIN	= 'regist_user_auth_a';					// メールテンプレート(会員承認登録(管理者用))
	const MAIL_TMPL_REGIST_USER_AUTO_COMPLETED	= 'regist_user_auto_completed';		// メールテンプレート(会員自動登録完了)
	const MAIL_TMPL_REGIST_USER_AUTH_COMPLETED	= 'regist_user_auth_completed';		// メールテンプレート(会員承認登録完了)

	// DBフィールド名

	/**
	 * 新着情報定義値をDBから取得
	 *
	 * @param object $db	DBオブジェクト
	 * @return array		取得データ
	 */
/*	static function loadConfig($db)
	{
		$retVal = array();

		// 汎用コンテンツ定義を読み込み
		$ret = $db->getAllConfig($rows);
		if ($ret){
			// 取得データを連想配列にする
			$configCount = count($rows);
			for ($i = 0; $i < $configCount; $i++){
				$key = $rows[$i]['nc_id'];
				$value = $rows[$i]['nc_value'];
				$retVal[$key] = $value;
			}
		}
		return $retVal;
	}*/
}
?>
