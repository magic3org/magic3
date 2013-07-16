<?php
/**
 * Eコマースメール連携クラス
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2007 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: ecMail.php 2359 2009-09-26 09:43:46Z fishbone $
 * @link       http://www.magic3.org
 */
require_once(dirname(__FILE__) . '/ecMailDb.php');

class ecMail
{
	private $db;	// DB接続オブジェクト
	// メール連携用定義
	const USE_EMAIL_TO_BACKOFFICE	= 'use_email_to_backoffice';		// EMAIL連携機能が使用可能かどうか
	const MAIL_TO_MEMBER_REGIST		= 'mail_to_member_regist';		// メール送信先(会員登録)
	const MAIL_FROM_MEMBER_REGIST	= 'mail_from_member_regist';	// メール送信元(会員登録)
	const REGIST_MEMBER_TO_BACKOFFICE_FORM = 'regist_member_to_backoffice';		// 会員情報送信用フォーム
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// DBオブジェクト作成
		$this->db = new ecMailDb();
	}
	/**
	 * 指定したユーザの会員情報を更新情報として、イントラネット側に送信
	 *
	 * @param int $mailType			メールタイプ(0=会員情報登録、1=更新、2=削除)
	 * @param int	$userId		ログインユーザID
	 * @return bool				true=成功、false=失敗
	 */
	public function sendMemberInfoToBackoffice($mailType, $userId)
	{
		global $gEnvManager;
		
		$langId	= $gEnvManager->getCurrentLanguage();		// 表示言語を取得
		
		// 会員情報を取得
		$ret = $this->db->getMember($userId, $memberRow);
		if ($ret){
			if ($memberRow['sm_type'] == 1){		// 個人メンバーのとき
				// 個人情報取得
				$ret = $this->db->getPersonInfo($memberRow['sm_person_info_id'], $personRow);
				if ($ret){
					$ret = $this->db->getAddress($personRow['pi_address_id'], $addressRow);
					if ($ret){
						$memberNo = $memberRow['sm_member_no'];		// 会員No
						$firstname = $personRow['pi_first_name'];
						$familyname = $personRow['pi_family_name'];
						$firstnameKana = $personRow['pi_first_name_kana'];
						$familynameKana = $personRow['pi_family_name_kana'];
						$name = $personRow['pi_family_name'] . $personRow['pi_first_name'];
						$nameKana = $personRow['pi_family_name_kana'] . $personRow['pi_first_name_kana'];
	
						$email	= $personRow['pi_email'];
						$email2 = $personRow['pi_email'];
						$oldemail = $personRow['pi_email'];
						$zipcode = $addressRow['ad_zipcode'];
						$address = $addressRow['ad_address1'];
						$address2 = $addressRow['ad_address2'];
						$phone	= $addressRow['ad_phone'];
						$fax	= $addressRow['ad_fax'];				// FAX	
						$state = $addressRow['ad_state_id'];
						
						$stateName = $this->db->getStateName('JPN', $langId, $state);			// 都道府県
						$address1 = $stateName . $address;
						$this->sendMailToBackoffice($mailType, $memberNo, $email, $familyname . $firstname, $familynameKana . $firstnameKana, $zipcode, $address1, $address2, $phone);
					}
				}
			}
		}
		return $ret;
	}
	/**
	 * イントラネット側に会員登録、更新情報をメール送信する
	 *
	 * @param int $mailType			メールタイプ(0=会員情報登録、1=更新、2=削除)
	 * @param string $memberNo		会員No
	 * @param string $email			eメール(ログインアカウント)
	 * @param string $name			会員名
	 * @param string $nameKana		会員名カナ
	 * @param string $zipcode		郵便番号
	 * @param string $address1		住所1
	 * @param string $address2		住所2
	 * @param string $phone			電話番号
	 * @return bool					true=成功、false=失敗
	 */
	function sendMailToBackoffice($mailType, $memberNo, $email, $name, $nameKana, $zipcode, $address1, $address2, $phone)
	{	
		global $gEnvManager;
		global $gInstanceManager;
		
		// メール連携許可のときはメールを送信
		if ($this->db->getConfig(self::USE_EMAIL_TO_BACKOFFICE) == '1'){
			$fromAddress = $this->db->getConfig(self::MAIL_FROM_MEMBER_REGIST);	// 連携メール送信元
			$toAddress = $this->db->getConfig(self::MAIL_TO_MEMBER_REGIST);		// 連携メール送信先
			
			// 件名の設定
			$operation = '不明';
			if ($mailType == 0){	// 新規登録
				$operation = '顧客情報新規';
			} else if ($mailType == 1){	// 更新
				$operation = '顧客情報更新';
			} else if ($mailType == 2){	// 削除
				$operation = '顧客情報削除';
			}
			$now = date("Y/m/d");	// 現在日
			$subject = $memberNo . ':' . $name . ':' . $now . ':' . $operation;
			$mailParam = array();
			//$mailParam['PASSWORD'] = $password;
			$mailParam['MEMBER_NO'] = $memberNo;
			$mailParam['EMAIL'] = $email;
			$mailParam['NAME'] = $name;
			$mailParam['NAME_KANA'] = $nameKana;
			$mailParam['ZIPCODE'] = $zipcode;
			$mailParam['ADDRESS1'] = $address1;
			$mailParam['ADDRESS2'] = $address2;
			$mailParam['PHONE'] = $phone;
			$ret = $gInstanceManager->getMailManager()->sendFormMail(1/*自動送信*/, $gEnvManager->getCurrentWidgetId(), $toAddress, $fromAddress, ''/*返信先*/,
												$subject/*件名*/, self::REGIST_MEMBER_TO_BACKOFFICE_FORM, $mailParam);// 自動送信
		}
	}
}
?>
