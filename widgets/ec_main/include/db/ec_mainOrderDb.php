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
 * @version    SVN: $Id: ec_mainOrderDb.php 5475 2012-12-18 14:29:52Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getDbPath() . '/baseDb.php');

class ec_mainOrderDb extends BaseDb
{
	const NO_ID = 'order_no';		// 注文番号取得用
	
	/**
	 * 正会員情報を取得
	 *
	 * @param int $userId			ユーザID兼データ更新ユーザ
	 * @param array $memberInfo		会員情報
	 * @param array $personInfo		個人情報
	 * @param array $companyInfo	法人情報
	 * @param array $address		住所
	 * @return						true=成功、false=失敗
	 */
	function getMemberInfo($userId, &$memberInfo, &$personInfo, &$companyInfo, &$address)
	{
		$queryStr = 'SELECT * FROM shop_member WHERE sm_login_user_id = ? AND sm_deleted = false';
		$ret = $this->selectRecord($queryStr, array($userId), $memberInfo);
		if ($ret){
			if ($memberInfo['sm_type'] == 1){		// 個人の場合
				$queryStr = 'SELECT * FROM person_info WHERE pi_id = ? AND pi_language_id = ? AND pi_deleted = false';
				$ret = $this->selectRecord($queryStr, array($memberInfo['sm_person_info_id'], $memberInfo['sm_language_id']), $personInfo);
				
				// 住所を取得
				if ($ret){
					$queryStr = 'SELECT * FROM address WHERE ad_id = ? AND ad_language_id = ? AND ad_deleted = false';
					$ret = $this->selectRecord($queryStr, array($personInfo['pi_address_id'], $personInfo['pi_language_id']), $address);
				}
			} else if ($memberInfo['sm_type'] == 2){		// 法人の場合
				$queryStr = 'SELECT * FROM company_info WHERE ci_id = ? AND ci_language_id = ? AND ci_deleted = false';
				$ret = $this->selectRecord($queryStr, array($memberInfo['sm_company_info_id'], $memberInfo['sm_language_id']), $companyInfo);
				
				// 住所を取得
				if ($ret){
					$queryStr = 'SELECT * FROM address WHERE ad_id = ? AND ad_language_id = ? AND ad_deleted = false';
					$ret = $this->selectRecord($queryStr, array($companyInfo['ci_address_id'], $companyInfo['ci_language_id']), $address);
				}
			}
			return true;
		} else {
			return false;
		}
	}
	/**
	 * 発注シートを更新
	 *
	 * @param ing     $userId				ユーザID
	 * @param string  $lang					言語
	 * @param string  $clientId				クライアントID
	 * @param int    $custm_id				得意先(顧客)ID
	 * @param string $custm_name			得意先(顧客)名
	 * @param string $custm_name_kana		得意先(顧客)名(カナ)
	 * @param string $custm_person			得意先(顧客)担当者名
	 * @param string $custm_person_kana		得意先(顧客)担当者名(カナ)
	 * @param string $custm_zipcode			郵便番号(7桁)
	 * @param int    $custm_state_id		都道府県、州(geo_zoneテーブル)
	 * @param string $custm_address			市区町村
	 * @param string $custm_address2		建物名
	 * @param string $custm_phone			電話番号
	 * @param string $custm_fax				FAX
	 * @param string $custm_email			Eメール
	 * @param string $custm_country_id		国ID
	 * @param int    $deliv_id				出荷先ID
	 * @param string $deliv_name			出荷先名
	 * @param string $deliv_name_kana		出荷先名(カナ)
	 * @param string $deliv_person			出荷先担当者名
	 * @param string $deliv_person_kana		出荷先担当者名(カナ)
	 * @param string $deliv_zipcode			郵便番号(7桁)
	 * @param int    $deliv_state_id		都道府県、州(geo_zoneテーブル)
	 * @param string $deliv_address			市区町村
	 * @param string $deliv_address2		建物名
	 * @param string $deliv_phone			電話番号
	 * @param string $deliv_fax				FAX
	 * @param string $deliv_email			Eメール
	 * @param string $deliv_country_id		国ID
	 * @param int    $bill_id				請求先ID
	 * @param string $bill_name				請求先名
	 * @param string $bill_name_kana		請求先名(カナ)
	 * @param string $bill_person			請求先担当者名
	 * @param string $bill_person_kana		請求先担当者名(カナ)
	 * @param string $bill_zipcode			郵便番号(7桁)
	 * @param int    $bill_state_id			都道府県、州(geo_zoneテーブル)
	 * @param string $bill_address			市区町村
	 * @param string $bill_address2			建物名
	 * @param string $bill_phone			電話番号
	 * @param string $bill_fax				FAX
	 * @param string $bill_email			Eメール
	 * @param string $bill_country_id		国ID
	 * @param string $deliv_method_id		配送方法
	 * @param string $pay_method_id			支払い方法
	 * @param string $card_type				クレジットカードタイプ
	 * @param string $card_owner			クレジットカード所有者
	 * @param string $card_number			クレジットカード番号
	 * @param string $card_expires			クレジットカード期限
	 * @param timestamp $demand_dt			希望納期
	 * @param string $demand_time			希望納期(時間帯)
	 * @param timestamp $appoint_dt			予定納期
	 * @param string $currency_id			通貨ID
	 * @param float $subtotal				商品総額
	 * @param float $discount				値引き額
	 * @param float $deliv_fee				配送料
	 * @param float $charge					手数料
	 * @param float $total					支払い総額
	 * @param string $discount_desc			値引き説明
	 * @return								true=成功、false=失敗
	 */
	function updateOrderSheet($userId, $lang, $clientId,
				$custm_id, $custm_name, $custm_name_kana, $custm_person, $custm_person_kana, $custm_zipcode, $custm_state_id, $custm_address, $custm_address2, $custm_phone, $custm_fax, $custm_email, $custm_country_id, 
				$deliv_id, $deliv_name, $deliv_name_kana, $deliv_person, $deliv_person_kana, $deliv_zipcode, $deliv_state_id, $deliv_address, $deliv_address2, $deliv_phone, $deliv_fax, $deliv_email, $deliv_country_id,
				$bill_id,  $bill_name,  $bill_name_kana,  $bill_person,  $bill_person_kana,  $bill_zipcode,  $bill_state_id,  $bill_address, $bill_address2, $bill_phone,  $bill_fax, $bill_email, $bill_country_id,
				$deliv_method_id, $pay_method_id, $card_type, $card_owner, $card_number, $card_expires, $demand_dt, $demand_time, $appoint_dt, $currency_id, $subtotal, $discount, $deliv_fee, $charge, $total,
				$discount_desc = '')
	{
		$now = date("Y/m/d H:i:s");	// 現在日時
		$updateUserId = $this->gEnv->getCurrentUserId();	// 現在のユーザ
		
		// トランザクション開始
		$this->startTransaction();
		
		$keyUserId = 0;
		$keyLang = '';
		if (empty($userId)){
			if (empty($clientId)){
				// トランザクション確定
				$ret = $this->endTransaction();
				return false;
			} else {// クライアントIDからデータを取得するとき
				// クライアントIDが登録済みかどうか確認
				$queryStr = 'SELECT * FROM order_sheet WHERE oe_client_id = ?';
				$ret = $this->selectRecords($queryStr, array($clientId), $rows);
				if ($ret){
					if (count($rows) > 1){		// 2行以上取得できるときはエラー
						// トランザクション確定
						$ret = $this->endTransaction();
						return false;
					}
					$keyUserId = intval($rows[0]['oe_user_id']);
					$keyLang = $rows[0]['oe_language_id'];
					if ($keyUserId >= 0){		// クライアントIDで取得の場合はユーザIDがマイナス値のみ許可
						// トランザクション確定
						$ret = $this->endTransaction();
						return false;
					}
				} else {		// レコードがない場合はユーザIDを作成
					$keyUserId = -1;
					$keyLang = $lang;
					$queryStr = 'SELECT MIN(oe_user_id) AS mid FROM order_sheet WHERE oe_language_id = ?';
					$ret = $this->selectRecord($queryStr, array($lang), $row);
					if ($ret) $keyUserId = intval($row['mid']) -1;
					if ($keyUserId >= 0) $keyUserId = -1;
				}
			}
		} else {
			if ($userId > 0){
				$keyUserId = $userId;
				$keyLang = $lang;
			} else {
				// トランザクション確定
				$ret = $this->endTransaction();
				return false;
			}
		}

		// 登録済みかどうか確認
		$queryStr = 'SELECT * FROM order_sheet WHERE oe_user_id = ? AND oe_language_id = ?';
		$ret = $this->selectRecord($queryStr, array($keyUserId, $keyLang), $row);
		if ($ret){
			$queryStr  = 'UPDATE order_sheet ';
			$queryStr .=   'SET ';
			$queryStr .=     'oe_user_id = ?, ';
			$queryStr .=     'oe_language_id = ?, ';
			$queryStr .=     'oe_custm_id = ?, ';
			$queryStr .=     'oe_custm_name = ?, ';
			$queryStr .=     'oe_custm_name_kana = ?, ';
			$queryStr .=     'oe_custm_person = ?, ';
			$queryStr .=     'oe_custm_person_kana = ?, ';
			$queryStr .=     'oe_custm_zipcode = ?, ';
			$queryStr .=     'oe_custm_state_id = ?, ';
			$queryStr .=     'oe_custm_address1 = ?, ';
			$queryStr .=     'oe_custm_address2 = ?, ';
			$queryStr .=     'oe_custm_phone = ?, ';
			$queryStr .=     'oe_custm_fax = ?, ';
			$queryStr .=     'oe_custm_email = ?, ';
			$queryStr .=     'oe_custm_country_id = ?, ';
			$queryStr .=     'oe_deliv_id = ?, ';
			$queryStr .=     'oe_deliv_name = ?, ';
			$queryStr .=     'oe_deliv_name_kana = ?, ';
			$queryStr .=     'oe_deliv_person = ?, ';
			$queryStr .=     'oe_deliv_person_kana = ?, ';
			$queryStr .=     'oe_deliv_zipcode = ?, ';
			$queryStr .=     'oe_deliv_state_id = ?, ';
			$queryStr .=     'oe_deliv_address1 = ?, ';
			$queryStr .=     'oe_deliv_address2 = ?, ';
			$queryStr .=     'oe_deliv_phone = ?, ';
			$queryStr .=     'oe_deliv_fax = ?, ';
			$queryStr .=     'oe_deliv_email = ?, ';
			$queryStr .=     'oe_deliv_country_id = ?, ';
			$queryStr .=     'oe_bill_id = ?, ';
			$queryStr .=     'oe_bill_name = ?, ';
			$queryStr .=     'oe_bill_name_kana = ?, ';
			$queryStr .=     'oe_bill_person = ?, ';
			$queryStr .=     'oe_bill_person_kana = ?, ';
			$queryStr .=     'oe_bill_zipcode = ?, ';
			$queryStr .=     'oe_bill_state_id = ?, ';
			$queryStr .=     'oe_bill_address1 = ?, ';
			$queryStr .=     'oe_bill_address2 = ?, ';
			$queryStr .=     'oe_bill_phone = ?, ';
			$queryStr .=     'oe_bill_fax = ?, ';
			$queryStr .=     'oe_bill_email = ?, ';
			$queryStr .=     'oe_bill_country_id = ?, ';
			$queryStr .=     'oe_deliv_method_id = ?, ';
			$queryStr .=     'oe_pay_method_id = ?, ';
			$queryStr .=     'oe_card_type = ?, ';
			$queryStr .=     'oe_card_owner = ?, ';
			$queryStr .=     'oe_card_number = ?, ';
			$queryStr .=     'oe_card_expires = ?, ';
			$queryStr .=     'oe_demand_dt = ?, ';
			$queryStr .=     'oe_demand_time = ?, ';
			$queryStr .=     'oe_appoint_dt = ?, ';
			$queryStr .=     'oe_discount_desc = ?, ';			// 値引き説明
			$queryStr .=     'oe_currency_id = ?, ';			
			$queryStr .=     'oe_subtotal = ?, ';			
			$queryStr .=     'oe_discount = ?, ';			
			$queryStr .=     'oe_deliv_fee = ?, ';			
			$queryStr .=     'oe_charge = ?, ';			
			$queryStr .=     'oe_total = ?, ';
			$queryStr .=     'oe_client_id = ?, ';			// クライアントID
			$queryStr .=     'oe_session = ?, ';			// セッションID
			$queryStr .=     'oe_update_user_id = ?, ';
			$queryStr .=     'oe_update_dt = ? ';
			$queryStr .=   'WHERE oe_serial = ? ';
			$this->execStatement($queryStr, array($keyUserId, $keyLang, 
				$custm_id, $custm_name, $custm_name_kana, $custm_person, $custm_person_kana, $custm_zipcode, $custm_state_id, $custm_address, $custm_address2, $custm_phone, $custm_fax, $custm_email, $custm_country_id, 
				$deliv_id, $deliv_name, $deliv_name_kana, $deliv_person, $deliv_person_kana, $deliv_zipcode, $deliv_state_id, $deliv_address, $deliv_address2, $deliv_phone, $deliv_fax, $deliv_email, $deliv_country_id,
				$bill_id,  $bill_name,  $bill_name_kana,  $bill_person,  $bill_person_kana,  $bill_zipcode,  $bill_state_id,  $bill_address, $bill_address2,  $bill_phone,  $bill_fax, $bill_email, $bill_country_id,
				$deliv_method_id, $pay_method_id, $card_type, $card_owner, $card_number, $card_expires, $demand_dt, $demand_time, $appoint_dt, $discount_desc, $currency_id, $subtotal, $discount, $deliv_fee, $charge, $total,
				$clientId, session_id(), $updateUserId, $now, $row['oe_serial']));
		} else {
			// 新規レコードを追加
			$queryStr  = 'INSERT INTO order_sheet (';
			$queryStr .=     'oe_user_id, ';
			$queryStr .=     'oe_language_id, ';
			$queryStr .=     'oe_custm_id, ';
			$queryStr .=     'oe_custm_name, ';
			$queryStr .=     'oe_custm_name_kana, ';
			$queryStr .=     'oe_custm_person, ';
			$queryStr .=     'oe_custm_person_kana, ';
			$queryStr .=     'oe_custm_zipcode, ';
			$queryStr .=     'oe_custm_state_id, ';
			$queryStr .=     'oe_custm_address1, ';
			$queryStr .=     'oe_custm_address2, ';
			$queryStr .=     'oe_custm_phone, ';
			$queryStr .=     'oe_custm_fax, ';
			$queryStr .=     'oe_custm_email, ';
			$queryStr .=     'oe_custm_country_id, ';
			$queryStr .=     'oe_deliv_id, ';
			$queryStr .=     'oe_deliv_name, ';
			$queryStr .=     'oe_deliv_name_kana, ';
			$queryStr .=     'oe_deliv_person, ';
			$queryStr .=     'oe_deliv_person_kana, ';
			$queryStr .=     'oe_deliv_zipcode, ';
			$queryStr .=     'oe_deliv_state_id, ';
			$queryStr .=     'oe_deliv_address1, ';
			$queryStr .=     'oe_deliv_address2, ';
			$queryStr .=     'oe_deliv_phone, ';
			$queryStr .=     'oe_deliv_fax, ';
			$queryStr .=     'oe_deliv_email, ';
			$queryStr .=     'oe_deliv_country_id, ';
			$queryStr .=     'oe_bill_id, ';
			$queryStr .=     'oe_bill_name, ';
			$queryStr .=     'oe_bill_name_kana, ';
			$queryStr .=     'oe_bill_person, ';
			$queryStr .=     'oe_bill_person_kana, ';
			$queryStr .=     'oe_bill_zipcode, ';
			$queryStr .=     'oe_bill_state_id, ';
			$queryStr .=     'oe_bill_address1, ';
			$queryStr .=     'oe_bill_address2, ';
			$queryStr .=     'oe_bill_phone, ';
			$queryStr .=     'oe_bill_fax, ';
			$queryStr .=     'oe_bill_email, ';
			$queryStr .=     'oe_bill_country_id, ';
			$queryStr .=     'oe_deliv_method_id, ';
			$queryStr .=     'oe_pay_method_id, ';
			$queryStr .=     'oe_card_type, ';
			$queryStr .=     'oe_card_owner, ';
			$queryStr .=     'oe_card_number, ';
			$queryStr .=     'oe_card_expires, ';
			$queryStr .=     'oe_demand_dt, ';
			$queryStr .=     'oe_demand_time, ';			
			$queryStr .=     'oe_appoint_dt, ';
			$queryStr .=     'oe_discount_desc, ';			// 値引き説明
			$queryStr .=     'oe_currency_id, ';			
			$queryStr .=     'oe_subtotal, ';			
			$queryStr .=     'oe_discount, ';			
			$queryStr .=     'oe_deliv_fee, ';			
			$queryStr .=     'oe_charge, ';			
			$queryStr .=     'oe_total, ';
			$queryStr .=     'oe_client_id, ';			// クライアントID
			$queryStr .=     'oe_session, ';			// セッションID
			$queryStr .=     'oe_update_user_id, ';
			$queryStr .=     'oe_update_dt ';
			$queryStr .= ') VALUES (';
			$queryStr .=   '?, ';
			$queryStr .=   '?, ';
			$queryStr .=   '?, ';
			$queryStr .=   '?, ';
			$queryStr .=   '?, ';
			$queryStr .=   '?, ';
			$queryStr .=   '?, ';
			$queryStr .=   '?, ';
			$queryStr .=   '?, ';
			$queryStr .=   '?, ';
			$queryStr .=   '?, ';
			$queryStr .=   '?, ';
			$queryStr .=   '?, ';
			$queryStr .=   '?, ';
			$queryStr .=   '?, ';
			$queryStr .=   '?, ';
			$queryStr .=   '?, ';
			$queryStr .=   '?, ';
			$queryStr .=   '?, ';
			$queryStr .=   '?, ';
			$queryStr .=   '?, ';
			$queryStr .=   '?, ';
			$queryStr .=   '?, ';
			$queryStr .=   '?, ';
			$queryStr .=   '?, ';
			$queryStr .=   '?, ';
			$queryStr .=   '?, ';
			$queryStr .=   '?, ';
			$queryStr .=   '?, ';
			$queryStr .=   '?, ';
			$queryStr .=   '?, ';
			$queryStr .=   '?, ';
			$queryStr .=   '?, ';
			$queryStr .=   '?, ';
			$queryStr .=   '?, ';
			$queryStr .=   '?, ';
			$queryStr .=   '?, ';
			$queryStr .=   '?, ';
			$queryStr .=   '?, ';
			$queryStr .=   '?, ';
			$queryStr .=   '?, ';
			$queryStr .=   '?, ';
			$queryStr .=   '?, ';
			$queryStr .=   '?, ';
			$queryStr .=   '?, ';
			$queryStr .=   '?, ';
			$queryStr .=   '?, ';
			$queryStr .=   '?, ';
			$queryStr .=   '?, ';
			$queryStr .=   '?, ';
			$queryStr .=   '?, ';
			$queryStr .=   '?, ';
			$queryStr .=   '?, ';
			$queryStr .=   '?, ';
			$queryStr .=   '?, ';
			$queryStr .=   '?, ';
			$queryStr .=   '?, ';
			$queryStr .=   '?, ';
			$queryStr .=   '?, ';
			$queryStr .=   '?, ';
			$queryStr .=   '?) ';
			$ret = $this->execStatement($queryStr, array($keyUserId, $keyLang, 
				$custm_id, $custm_name, $custm_name_kana, $custm_person, $custm_person_kana, $custm_zipcode, $custm_state_id, $custm_address, $custm_address2, $custm_phone, $custm_fax, $custm_email, $custm_country_id, 
				$deliv_id, $deliv_name, $deliv_name_kana, $deliv_person, $deliv_person_kana, $deliv_zipcode, $deliv_state_id, $deliv_address, $deliv_address2, $deliv_phone, $deliv_fax, $deliv_email, $deliv_country_id,
				$bill_id,  $bill_name,  $bill_name_kana,  $bill_person,  $bill_person_kana,  $bill_zipcode,  $bill_state_id,  $bill_address, $bill_address2, $bill_phone,  $bill_fax, $bill_email, $bill_country_id,
				$deliv_method_id, $pay_method_id, $card_type, $card_owner, $card_number, $card_expires, $demand_dt, $demand_time, $appoint_dt, $discount_desc, $currency_id, $subtotal, $discount, $deliv_fee, $charge, $total,
				$clientId, session_id(), $updateUserId, $now));
		}
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * 受注情報を更新
	 *
	 * @param int     $serial				シリアル番号
	 * @param ing     $userId				購入者ユーザID
	 * @param string  $lang					言語
	 * @param string  $order_no				受注番号(任意)
	 * @param int    $custm_id				得意先(顧客)ID
	 * @param string $custm_name			得意先(顧客)名
	 * @param string $custm_name_kana		得意先(顧客)名(カナ)
	 * @param string $custm_person			得意先(顧客)担当者名
	 * @param string $custm_person_kana		得意先(顧客)担当者名(カナ)
	 * @param string $custm_zipcode			郵便番号(7桁)
	 * @param int    $custm_state_id		都道府県、州(geo_zoneテーブル)
	 * @param string $custm_address			市区町村
	 * @param string $custm_address2		建物名
	 * @param string $custm_phone			電話番号
	 * @param string $custm_fax				FAX
	 * @param string $custm_email			Eメール
	 * @param string $custm_country_id		国ID
	 * @param int    $deliv_id				出荷先ID
	 * @param string $deliv_name			出荷先名
	 * @param string $deliv_name_kana		出荷先名(カナ)
	 * @param string $deliv_person			出荷先担当者名
	 * @param string $deliv_person_kana		出荷先担当者名(カナ)
	 * @param string $deliv_zipcode			郵便番号(7桁)
	 * @param int    $deliv_state_id		都道府県、州(geo_zoneテーブル)
	 * @param string $deliv_address			市区町村
	 * @param string $deliv_address2		建物名
	 * @param string $deliv_phone			電話番号
	 * @param string $deliv_fax				FAX
	 * @param string $deliv_email			Eメール
	 * @param string $deliv_country_id		国ID
	 * @param int    $bill_id				請求先ID
	 * @param string $bill_name				請求先名
	 * @param string $bill_name_kana		請求先名(カナ)
	 * @param string $bill_person			請求先担当者名
	 * @param string $bill_person_kana		請求先担当者名(カナ)
	 * @param string $bill_zipcode			郵便番号(7桁)
	 * @param int    $bill_state_id			都道府県、州(geo_zoneテーブル)
	 * @param string $bill_address			市区町村
	 * @param string $bill_address2			建物名
	 * @param string $bill_phone			電話番号
	 * @param string $bill_fax				FAX
	 * @param string $bill_email			Eメール
	 * @param string $bill_country_id		国ID
	 * @param string $deliv_method_id		配送方法
	 * @param string $pay_method_id			支払い方法
	 * @param string $card_type				クレジットカードタイプ
	 * @param string $card_owner			クレジットカード所有者
	 * @param string $card_number			クレジットカード番号
	 * @param string $card_expires			クレジットカード期限
	 * @param timestamp $demand_dt			希望納期
	 * @param string $demand_time			希望納期(時間帯)
	 * @param timestamp $appoint_dt			予定納期
	 * @param string $currency_id			通貨ID
	 * @param float $subtotal				商品総額
	 * @param float $discount				値引き額
	 * @param float $deliv_fee				配送料
	 * @param float $charge					手数料
	 * @param float $total					支払い総額
	 * @param int   $order_status			受注状況
	 * @param timestamp $estimate_dt		見積日時
	 * @param timestamp $regist_dt			受注受付日時
	 * @param timestamp $order_dt			受注開始日時
	 * @param timestamp $deliv_dt			配送日時
	 * @param timestamp $close_dt			取引終了日時
	 * @param int     $updateUserId			更新者ID
	 * @param timestamp $now				更新日時
	 * @param int       $newId				新規ID
	 * @param int       $newSerial			新規シリアル番号
	 * @param string $discount_desc			値引き説明
	 * @param timestamp $pay_dt				支払い日時
	 * @return								true=成功、false=失敗
	 */
	function updateOrder($serial, $userId, $lang, $order_no,
				$custm_id, $custm_name, $custm_name_kana, $custm_person, $custm_person_kana, $custm_zipcode, $custm_state_id, $custm_address, $custm_address2, $custm_phone, $custm_fax, $custm_email, $custm_country_id, 
				$deliv_id, $deliv_name, $deliv_name_kana, $deliv_person, $deliv_person_kana, $deliv_zipcode, $deliv_state_id, $deliv_address, $deliv_address2, $deliv_phone, $deliv_fax, $deliv_email, $deliv_country_id,
				$bill_id,  $bill_name,  $bill_name_kana,  $bill_person,  $bill_person_kana,  $bill_zipcode,  $bill_state_id,  $bill_address, $bill_address2,  $bill_phone,  $bill_fax, $bill_email, $bill_country_id,
				$deliv_method_id, $pay_method_id, $card_type, $card_owner, $card_number, $card_expires, $demand_dt, $demand_time, $appoint_dt, $currency_id, $subtotal, $discount, $deliv_fee, $charge, $total,
				$order_status, $estimate_dt, $regist_dt, $order_dt, $deliv_dt, $close_dt, 
				$updateUserId, $now, &$newId, &$newSerial, $discount_desc = '', $pay_dt = '')
	{
		if (empty($pay_dt)) $pay_dt = $this->gEnv->getInitValueOfTimestamp();		// 日時初期化値
		
		$historyIndex = 0;		// 履歴番号
		if ($serial == 0){			// 新規登録のとき
			// 新規IDを作成
			$id = 1;
			$queryStr = 'select max(or_id) as ms from order_header ';
			$ret = $this->selectRecord($queryStr, array(), $row);
			if ($ret) $id = $row['ms'] + 1;
			$newId = $id;
		} else {
			$queryStr  = 'SELECT * FROM order_header ';
			$queryStr .=   'WHERE or_serial = ? ';
			$ret = $this->selectRecord($queryStr, array($serial), $row);
			if ($ret){		// 既に登録レコードがあるとき
				if ($row['or_deleted']){		// レコードが削除されていれば終了
					return false;
				}
			} else {		// 存在しない場合は終了
				return false;
			}
			$historyIndex = $row['or_history_index'] + 1;
			$id = $row['or_id'];
			
			// 古いレコードを削除
			$queryStr  = 'UPDATE order_header ';
			$queryStr .=   'SET or_deleted = true, ';	// 削除
			$queryStr .=     'or_update_user_id = ?, ';
			$queryStr .=     'or_update_dt = ? ';
			$queryStr .=   'WHERE or_serial = ?';
			$this->execStatement($queryStr, array($updateUserId, $now, $serial));
		}

		// 新規レコードを追加
		$queryStr  = 'INSERT INTO order_header (';
		$queryStr .=     'or_id, ';
		$queryStr .=     'or_history_index, ';
		$queryStr .=     'or_user_id, ';
		$queryStr .=     'or_language_id, ';
		$queryStr .=     'or_order_no, ';
		$queryStr .=     'or_custm_id, ';
		$queryStr .=     'or_custm_name, ';
		$queryStr .=     'or_custm_name_kana, ';
		$queryStr .=     'or_custm_person, ';
		$queryStr .=     'or_custm_person_kana, ';
		$queryStr .=     'or_custm_zipcode, ';
		$queryStr .=     'or_custm_state_id, ';
		$queryStr .=     'or_custm_address1, ';
		$queryStr .=     'or_custm_address2, ';
		$queryStr .=     'or_custm_phone, ';
		$queryStr .=     'or_custm_fax, ';
		$queryStr .=     'or_custm_email, ';
		$queryStr .=     'or_custm_country_id, ';
		$queryStr .=     'or_deliv_id, ';
		$queryStr .=     'or_deliv_name, ';
		$queryStr .=     'or_deliv_name_kana, ';
		$queryStr .=     'or_deliv_person, ';
		$queryStr .=     'or_deliv_person_kana, ';
		$queryStr .=     'or_deliv_zipcode, ';
		$queryStr .=     'or_deliv_state_id, ';
		$queryStr .=     'or_deliv_address1, ';
		$queryStr .=     'or_deliv_address2, ';
		$queryStr .=     'or_deliv_phone, ';
		$queryStr .=     'or_deliv_fax, ';
		$queryStr .=     'or_deliv_email, ';
		$queryStr .=     'or_deliv_country_id, ';
		$queryStr .=     'or_bill_id, ';
		$queryStr .=     'or_bill_name, ';
		$queryStr .=     'or_bill_name_kana, ';
		$queryStr .=     'or_bill_person, ';
		$queryStr .=     'or_bill_person_kana, ';
		$queryStr .=     'or_bill_zipcode, ';
		$queryStr .=     'or_bill_state_id, ';
		$queryStr .=     'or_bill_address1, ';
		$queryStr .=     'or_bill_address2, ';
		$queryStr .=     'or_bill_phone, ';
		$queryStr .=     'or_bill_fax, ';
		$queryStr .=     'or_bill_email, ';
		$queryStr .=     'or_bill_country_id, ';
		$queryStr .=     'or_deliv_method_id, ';
		$queryStr .=     'or_pay_method_id, ';
		$queryStr .=     'or_card_type, ';
		$queryStr .=     'or_card_owner, ';
		$queryStr .=     'or_card_number, ';
		$queryStr .=     'or_card_expires, ';
		$queryStr .=     'or_demand_dt, ';			
		$queryStr .=     'or_demand_time, ';
		$queryStr .=     'or_appoint_dt, ';
		$queryStr .=     'or_discount_desc, ';			// 値引き説明
		$queryStr .=     'or_currency_id, ';			
		$queryStr .=     'or_subtotal, ';			
		$queryStr .=     'or_discount, ';			
		$queryStr .=     'or_deliv_fee, ';			
		$queryStr .=     'or_charge, ';			
		$queryStr .=     'or_total, ';
		$queryStr .=     'or_order_status, ';
		$queryStr .=     'or_estimate_dt, ';
		$queryStr .=     'or_regist_dt, ';
		$queryStr .=     'or_order_dt, ';
		$queryStr .=     'or_deliv_dt, ';
		$queryStr .=     'or_close_dt, ';
		$queryStr .=     'or_pay_dt, ';				// 支払い日時
		$queryStr .=     'or_create_user_id, ';
		$queryStr .=     'or_create_dt ';
		$queryStr .= ') VALUES (';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?) ';
		$ret = $this->execStatement($queryStr, array($id, $historyIndex, $userId, $lang, $order_no,
			$custm_id, $custm_name, $custm_name_kana, $custm_person, $custm_person_kana, $custm_zipcode, $custm_state_id, $custm_address, $custm_address2, $custm_phone, $custm_fax, $custm_email, $custm_country_id, 
			$deliv_id, $deliv_name, $deliv_name_kana, $deliv_person, $deliv_person_kana, $deliv_zipcode, $deliv_state_id, $deliv_address, $deliv_address2, $deliv_phone, $deliv_fax, $deliv_email, $deliv_country_id,
			$bill_id,  $bill_name,  $bill_name_kana,  $bill_person,  $bill_person_kana,  $bill_zipcode,  $bill_state_id,  $bill_address, $bill_address2,  $bill_phone,  $bill_fax, $bill_email, $bill_country_id,
			$deliv_method_id, $pay_method_id, $card_type, $card_owner, $card_number, $card_expires, $demand_dt, $demand_time, $appoint_dt, $discount_desc, $currency_id, $subtotal, $discount, $deliv_fee, $charge, $total,
			$order_status, $estimate_dt, $regist_dt, $order_dt, $deliv_dt, $close_dt, $pay_dt,
			$updateUserId, $now));
			
		// 新規のシリアル番号取得
		$queryStr = 'select max(or_serial) as ns from order_header ';
		$ret = $this->selectRecord($queryStr, array(), $row);
		if ($ret) $newSerial = $row['ns'];
		return $ret;
	}
	/**
	 * 受注詳細の追加
	 *
	 * @param int  $orderId				受注ID
	 * @param int  $index				詳細インデックス(0～)
	 * @param string    $productClass	商品クラス
	 * @param int     $productId		商品ID
	 * @param string    $productType	商品タイプ
	 * @param string  $productName		商品名
	 * @param string  $productCode		商品コード
	 * @param float   $unitPrice		税抜き商品単価
	 * @param int     $quantity			数量
	 * @param float   $tax				税
	 * @param float   $total			税込み価格
	 * @param int     $userId			更新者ID
	 * @param string  $now				現在日時
	 * @return							true=成功、false=失敗
	 */
	function addOrderDetail($orderId, $index, $productClass, $productId, $productType, $productName, $productCode, $unitPrice, $quantity, $tax, $total, $userId, $now)
	{
		// 新規レコードを追加
		$queryStr  = 'INSERT INTO order_detail (';
		$queryStr .=   'od_order_id, ';
		$queryStr .=   'od_index, ';
		$queryStr .=   'od_product_class, ';
		$queryStr .=   'od_product_id, ';
		$queryStr .=   'od_product_type_id, ';
		$queryStr .=   'od_product_name, ';
		$queryStr .=   'od_product_code, ';
		$queryStr .=   'od_unit_price, ';
		$queryStr .=   'od_quantity, ';
		$queryStr .=   'od_tax, ';
		$queryStr .=   'od_total, ';
		$queryStr .=   'od_create_user_id, ';
		$queryStr .=   'od_create_dt ';
		$queryStr .= ') VALUES (';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?) ';
		$ret = $this->execStatement($queryStr, array($orderId, $index, $productClass, $productId, $productType, $productName, $productCode, $unitPrice, $quantity, $tax, $total, $userId, $now));
		return $ret;
	}
	/**
	 * 受注詳細一覧を取得
	 *
	 * @param string	$orderId			受注ID
	 * @param string	$lang				言語ID
	 * @param function	$callback			コールバック関数
	 * @return 			なし
	 */
	function getOrderDetailList($orderId, $lang, $callback)
	{
		$queryStr  = 'SELECT * FROM order_detail LEFT JOIN order_header ON od_order_id = or_id AND or_deleted = false ';
		$queryStr .=   'LEFT JOIN currency ON or_currency_id = cu_id AND cu_language_id = ? ';
		$queryStr .=   'LEFT JOIN product_type ON od_product_class = py_product_class AND od_product_type_id = py_id AND or_language_id = py_language_id AND py_deleted = false ';
		$queryStr .=   'LEFT JOIN photo ON od_product_id = ht_id AND cu_language_id = ht_language_id AND ht_deleted = false ';		// フォトギャラリー画像用
		$queryStr .=   'LEFT JOIN product ON od_product_id = pt_id AND cu_language_id = pt_language_id AND pt_deleted = false ';	// 一般商品用
		$queryStr .=   'WHERE od_order_id = ? AND od_deleted = false ';
		$queryStr .=   'ORDER BY od_index ';
		$this->selectLoop($queryStr, array($lang, $orderId), $callback, null);
	}
	/**
	 * 発注書を取得
	 *
	 * @param int $userId			ユーザID兼データ更新ユーザ
	 * @param string  $lang					言語
	 * @param array $row			取得レコード
	 * @return						true=成功、false=失敗
	 */
	function getOrderSheet($userId, $lang, &$row)
	{
		$queryStr = 'SELECT * FROM order_sheet WHERE oe_user_id = ? AND oe_language_id = ?';
		$ret = $this->selectRecord($queryStr, array($userId, $lang), $row);
		return $ret;
	}
	/**
	 * クライアントIDから発注書を取得
	 *
	 * @param string  $clientId		クライアントID
	 * @param array $row			取得レコード
	 * @return						true=成功、false=失敗
	 */
	function getOrderSheetByClientId($clientId, &$row)
	{
		// 引数エラーチェック
		if (empty($clientId)) return false;
		
		$queryStr = 'SELECT * FROM order_sheet WHERE oe_client_id = ?';
		$ret = $this->selectRecords($queryStr, array($clientId), $rows);
		if ($ret){
			if (count($rows) > 1){		// 2行以上取得できるときはエラー
				return false;
			}
			if (intval($rows[0]['oe_user_id']) >= 0){		// クライアントIDで取得の場合はユーザIDがマイナス値のみ許可
				return false;
			}
			$row = $rows[0];
		}
		return $ret;
	}
	/**
	 * 発注書を削除
	 *
	 * @param int $userId			ユーザID兼データ更新ユーザ
	 * @param string  $lang			言語
	 * @return						true=成功、false=失敗
	 */
	function delOrderSheet($userId, $lang)
	{
		$startTran = false;			// この関数でトランザクションを開始したかどうか
		
		// トランザクション開始
		if (!$this->isInTransaction()){
			$this->startTransaction();
			$startTran = true;
		}
		
		$queryStr  = 'DELETE FROM order_sheet ';
		$queryStr .=   'WHERE oe_user_id = ? AND oe_language_id = ?';
		$ret = $this->execStatement($queryStr, array($userId, $lang));
		
		// トランザクション確定
		if ($startTran) $ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * クライアントIDで発注書を削除
	 *
	 * @param string  $clientId		クライアントID
	 * @return						true=成功、false=失敗
	 */
	function delOrderSheetByClientId($clientId)
	{
		$startTran = false;			// この関数でトランザクションを開始したかどうか
		
		// トランザクション開始
		if (!$this->isInTransaction()){
			$this->startTransaction();
			$startTran = true;
		}
		
		$queryStr  = 'DELETE FROM order_sheet ';
		$queryStr .=   'WHERE oe_client_id = ?';
		$ret = $this->execStatement($queryStr, array($clientId));
		
		// トランザクション確定
		if ($startTran) $ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * 受注情報を取得
	 *
	 * @param int $id				受注ID
	 * @param array $row			取得レコード
	 * @return						true=成功、false=失敗
	 */
	function getOrder($id, &$row)
	{
		$queryStr = 'SELECT * FROM order_header WHERE or_id = ? AND or_deleted = false';
		$ret = $this->selectRecord($queryStr, array($id), $row);
		return $ret;
	}
	/**
	 * 受注情報をシリアル番号で取得
	 *
	 * @param int $serial			シリアル番号
	 * @param array $row			取得レコード
	 * @return						true=成功、false=失敗
	 */
	function getOrderBySerial($serial, &$row)
	{
		$queryStr = 'SELECT * FROM order_header LEFT JOIN _login_user ON or_create_user_id = lu_id AND lu_deleted = false ';
		$queryStr .=  'WHERE or_serial = ?';
		$ret = $this->selectRecord($queryStr, array($serial), $row);
		return $ret;
	}
	/**
	 * 受注情報を注文番号で取得
	 *
	 * @param string $no			注文番号
	 * @param array $row			取得レコード
	 * @return						true=成功、false=失敗
	 */
	function getOrderByOrderNo($no, &$row)
	{
		$queryStr  = 'SELECT * FROM order_header LEFT JOIN _login_user ON or_create_user_id = lu_id AND lu_deleted = false ';
		$queryStr .=   'WHERE or_deleted = false ';		// 未削除
		$queryStr .=     'AND or_order_no = ? ';
		$ret = $this->selectRecord($queryStr, array($no), $row);
		return $ret;
	}
	/**
	 * 最新の受注情報を取得
	 *
	 * @param array $row			取得レコード
	 * @return						true=成功、false=失敗
	 */
/*	function getLatestOrder(&$row)
	{
		// 最大シリアル番号取得
		$queryStr = 'SELECT max(or_serial) as ms FROM order_header ';
		$ret = $this->selectRecord($queryStr, array(), $maxRow);
		if ($ret){
			$queryStr = 'SELECT * FROM order_header WHERE or_serial = ?';
			$ret = $this->selectRecord($queryStr, array($maxRow['ms']), $row);
		}
		return $ret;
	}*/
	/**
	 * 注文番号を取得
	 *
	 * @return string				注文番号。存在しない場合は空文字列。
	 */
	function getOrderNo()
	{
		$orderNo = '';
		$queryStr = 'SELECT * FROM _used_no WHERE un_id = ?';
		$ret = $this->selectRecord($queryStr, array(self::NO_ID), $row);
		if ($ret) $orderNo = $row['un_value'];
		return $orderNo;
	}
	/**
	 * 注文番号を更新
	 *
	 * @param string $no	注文番号
	 * @return				true=成功、false=失敗
	 */
	function updateOrderNo($no)
	{
		// データの確認
		$queryStr = 'SELECT * FROM _used_no ';
		$queryStr .=  'WHERE un_id  = ?';
		$ret = $this->isRecordExists($queryStr, array(self::NO_ID));
		if ($ret){
			$queryStr = "UPDATE _used_no SET un_value = ? WHERE un_id = ?";
			return $this->execStatement($queryStr, array($no, self::NO_ID));
		} else {
			$queryStr = "INSERT INTO _used_no (un_id, un_value) VALUES (?, ?)";
			return $this->execStatement($queryStr, array(self::NO_ID, $no));
		}
	}
	/**
	 * 受注情報削除
	 *
	 * @param int $serialNo			シリアルNo
	 * @param int $userId			ユーザID(データ更新者)
	 * @return						true=成功、false=失敗
	 */
	function delOrder($serialNo, $userId)
	{
		$now = date("Y/m/d H:i:s");	// 現在日時
				
		// トランザクション開始
		$this->startTransaction();
		
		// 指定のシリアルNoのレコードが削除状態でないかチェック
		$queryStr  = 'select * from order_header ';
		$queryStr .=   'where or_deleted = false ';		// 未削除
		$queryStr .=     'and or_serial = ? ';
		$ret = $this->selectRecord($queryStr, array($serialNo), $row);
		if (!$ret){		// 存在しない場合は終了
			$this->endTransaction();
			return false;
		}
		
		// レコードを削除
		$queryStr  = 'UPDATE order_header ';
		$queryStr .=   'SET or_deleted = true, ';	// 削除
		$queryStr .=     'or_update_user_id = ?, ';
		$queryStr .=     'or_update_dt = ? ';
		$queryStr .=   'WHERE or_serial = ?';
		$this->execStatement($queryStr, array($userId, $now, $serialNo));
		
		// 詳細情報を削除
		$queryStr  = 'UPDATE order_detail ';
		$queryStr .=   'SET od_deleted = true, ';	// 削除
		$queryStr .=     'od_update_user_id = ?, ';
		$queryStr .=     'od_update_dt = ? ';
		$queryStr .=   'WHERE od_order_id = ? AND od_deleted = false';
		$this->execStatement($queryStr, array($userId, $now, $row['or_id']));		
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	
	/**
	 * 都道府県を取得
	 *
	 * @param string	$coutryId			国ID
	 * @param string	$lang				言語
	 * @param function	$callback			コールバック関数
	 * @return 			なし
	 */
	function getAllState($coutryId, $lang, $callback)
	{
		$queryStr = 'SELECT * FROM geo_zone ';
		$queryStr .=  'WHERE gz_country_id = ? AND gz_type = 1 AND gz_language_id = ? ';
		$queryStr .=  'ORDER BY gz_index ';
		$this->selectLoop($queryStr, array($coutryId, $lang), $callback, null);
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
	/**
	 * 配送方法総数を取得
	 *
	 * @param string	$lang		言語
	 * @param int		$setId		セットID
	 * @return int					総数
	 */
	function getAllDelivMethodCount($lang, $setId = 0)
	{
//		$setId = 0;		// デフォルトの定義セット
		$queryStr  = 'SELECT * FROM delivery_method_def ';
		$queryStr .=   'WHERE do_deleted = false ';
		$queryStr .=     'AND do_visible = true ';			// 表示状態
		$queryStr .=     'AND do_language_id = ? ';
		$queryStr .=     'AND do_set_id = ? ';
		return $this->selectRecordCount($queryStr, array($lang, $setId));
	}
	/**
	 * 配送方法を取得
	 *
	 * @param string	$lang				言語
	 * @param int		$setId		セットID
	 * @param function	$callback			コールバック関数
	 * @return 			なし
	 */
	function getAllDelivMethod($lang, $setId, $callback)
	{
//		$setId = 0;		// デフォルトの定義セット
		$queryStr = 'SELECT * FROM delivery_method_def ';
		$queryStr .=  'WHERE do_deleted = false ';
		$queryStr .=  'AND do_visible = true ';			// 表示状態
		$queryStr .=  'AND do_language_id = ? ';
		$queryStr .=  'AND do_set_id = ? ';
		$queryStr .=  'ORDER BY do_index ';
		$this->selectLoop($queryStr, array($lang, $setId), $callback, null);
	}
	/**
	 * 配送方法を取得
	 *
	 * @param string	$id			配送方法ID
	 * @param string	$lang		言語
	 * @param int		$setId		セットID
	 * @param array $row			取得レコード
	 * @return						true=成功、false=失敗
	 */
	function getDelivMethod($id, $lang, $setId, &$row)
	{
		$queryStr = 'SELECT * FROM delivery_method_def ';
		$queryStr .=  'WHERE do_deleted = false ';
		$queryStr .=  'AND do_id = ? ';			// 表示状態
		$queryStr .=  'AND do_language_id = ? ';
		$queryStr .=  'AND do_set_id = ? ';
		$ret = $this->selectRecord($queryStr, array($id, $lang, $setId), $row);
		return $ret;
	}
	/**
	 * 支払い方法総数を取得
	 *
	 * @param string	$lang		言語
	 * @return int					総数
	 */
	function getAllPaymentMethodCount($lang)
	{
		$setId = 0;		// デフォルトの定義セット
		$queryStr  = 'SELECT * FROM pay_method_def ';
		$queryStr .=   'WHERE po_deleted = false ';
		$queryStr .=    'AND po_visible = true ';			// 表示状態
		$queryStr .=    'AND po_language_id = ? ';
		$queryStr .=    'AND po_set_id = ? ';
		return $this->selectRecordCount($queryStr, array($lang, $setId));
	}
	/**
	 * 支払い方法を取得
	 *
	 * @param string	$lang				言語
	 * @param function	$callback			コールバック関数
	 * @return 			なし
	 */
	function getAllPaymentMethod($lang, $callback)
	{
		$setId = 0;		// デフォルトの定義セット
		$queryStr = 'SELECT * FROM pay_method_def ';
		$queryStr .=  'WHERE po_deleted = false ';
		$queryStr .=  'AND po_visible = true ';			// 表示状態
		$queryStr .=  'AND po_language_id = ? ';
		$queryStr .=  'AND po_set_id = ? ';
		$queryStr .=  'ORDER BY po_index ';
		$this->selectLoop($queryStr, array($lang, $setId), $callback, null);
	}
	/**
	 * 支払い方法を取得
	 *
	 * @param string	$id			支払い方法ID
	 * @param string	$lang		言語
	 * @param int		$setId		セットID
	 * @param array $row			取得レコード
	 * @return						true=成功、false=失敗
	 */
	function getPaymentMethod($id, $lang, $setId, &$row)
	{
		$queryStr = 'SELECT * FROM pay_method_def ';
		$queryStr .=  'WHERE po_deleted = false ';
		$queryStr .=  'AND po_id = ? ';			// 表示状態
		$queryStr .=  'AND po_language_id = ? ';
		$queryStr .=  'AND po_set_id = ? ';
		$ret = $this->selectRecord($queryStr, array($id, $lang, $setId), $row);
		return $ret;
	}
	/**
	 * インナーウィジェットのメソッド定義を取得
	 *
	 * @param string    $type			メソッド種別
	 * @param string	$lang			言語
	 * @param array     $rows			取得レコード
	 * @return bool						true=取得、false=取得せず
	 */
	function getAllIWidgetMethod($type, $lang, &$rows)
	{
		$setId = 0;		// デフォルトの定義セット
		$queryStr  = 'SELECT * FROM _iwidget_method ';
		$queryStr .=   'WHERE id_deleted = false ';
		$queryStr .=     'AND id_visible = true ';			// 表示状態
		$queryStr .=     'AND id_type = ? ';
		$queryStr .=     'AND id_language_id = ? ';
		$queryStr .=     'AND id_set_id = ? ';
		$queryStr .=   'ORDER BY id_index ';
		$retValue = $this->selectRecords($queryStr, array($type, $lang, $setId), $rows);
		return $retValue;
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
	 * 受注状況一覧を取得
	 *
	 * @param int		$statusMin			取得する受注状況のステータス範囲(開始)、指定なしの場合は0
	 * @param int		$statusMax			取得する受注状況のステータス範囲(終了)、指定なしの場合は0
	 * @param int		$limit				取得する項目数
	 * @param int		$offset				取得する先頭位置(0～)
	 * @param function	$callback			コールバック関数
	 * @return 			なし
	 */
	function searchOrderHeader($statusMin, $statusMax, $limit, $offset, $callback)
	{
		$param = array();
		$queryStr = 'SELECT * FROM order_header LEFT JOIN _login_user ON or_user_id = lu_id AND lu_deleted = false ';
		$queryStr .=  'WHERE or_deleted = false ';// 削除されていない
		if ($statusMin != 0){		// 開始が設定されているとき
			$queryStr .=    'AND or_order_status >= ? ';
			$param[] = $statusMin;
		}
		if ($statusMax != 0){		// 終了が設定されているとき
			$queryStr .=    'AND or_order_status <= ? ';
			$param[] = $statusMax;
		}
		$queryStr .=  'ORDER BY or_id desc limit ' . $limit . ' offset ' . $offset;
		$this->selectLoop($queryStr, $param, $callback, null);
	}
	/**
	 * 受注状況一覧数を取得
	 *
	 * @param int		$statusMin			取得する受注状況のステータス範囲(開始)、指定なしの場合は0
	 * @param int		$statusMax			取得する受注状況のステータス範囲(終了)、指定なしの場合は0
	 * @return int							総数
	 */
	function searchOrderHeaderCount($statusMin, $statusMax)
	{
		$param = array();
		$queryStr = 'SELECT * FROM order_header LEFT JOIN _login_user ON or_user_id = lu_id AND lu_deleted = false ';
		$queryStr .=  'WHERE or_deleted = false ';// 削除されていない
		if ($statusMin != 0){		// 開始が設定されているとき
			$queryStr .=    'AND or_order_status >= ? ';
			$param[] = $statusMin;
		}
		if ($statusMax != 0){		// 終了が設定されているとき
			$queryStr .=    'AND or_order_status <= ? ';
			$param[] = $statusMax;
		}
		return $this->selectRecordCount($queryStr, $param);
	}
	/**
	 * ユーザを指定して、受注状況一覧を取得
	 *
	 * @param int		$userId				ユーザID
	 * @param function	$callback			コールバック関数
	 * @return 			なし
	 */
	function getOrderHeaderByUser($userId, $callback)
	{
		$initDt = $this->gEnv->getInitValueOfTimestamp();		// 日時初期化値
		
		$queryStr  = 'SELECT * FROM order_header LEFT JOIN _login_user ON or_user_id = lu_id AND lu_deleted = false ';
		$queryStr .=   'WHERE or_deleted = false ';// 削除されていない
		$queryStr .=     'AND or_user_id = ? ';
		$queryStr .=     'AND or_pay_dt != ? ';		// 支払い済み
		$queryStr .=   'ORDER BY or_id desc';
		$this->selectLoop($queryStr, array($userId, $initDt), $callback);
	}
	/**
	 * 指定受注項目が指定ユーザの受注であるか判断
	 *
	 * @param int		$orderId			受注ID
	 * @param int		$userId				ユーザID
	 * @return 			なし
	 */
	function isOrderByUser($orderId, $userId)
	{
		$queryStr = 'SELECT * FROM order_header ';
		$queryStr .=  'WHERE or_deleted = false ';// 削除されていない
		$queryStr .=    'AND or_id = ? ';
		$queryStr .=    'AND or_user_id = ? ';
		return $this->isRecordExists($queryStr, array($orderId, $userId));
	}
	/**
	 * 受注ステータス名を取得
	 *
	 * @param int		$status				受注ステータス
	 * @param string	$lang				言語
	 * @param array     $row				レコード
	 * @return bool							取得 = true, 取得なし= false
	 */
	function getOrderStatusName($status, $lang, &$row)
	{
		$queryStr = 'SELECT * FROM order_status ';
		$queryStr .=  'WHERE os_id = ? AND os_language_id = ? ';
		$ret = $this->selectRecord($queryStr, array($status, $lang), $row);
		return $ret;
	}
	/**
	 * 受注ステータスを取得
	 *
	 * @param string	$coutryId			国ID
	 * @param string	$lang				言語
	 * @param function	$callback			コールバック関数
	 * @return 			なし
	 */
	function getAllOrderStatus($lang, $callback)
	{
		$queryStr = 'SELECT * FROM order_status ';
		$queryStr .=  'WHERE os_language_id = ? ';
		$queryStr .=  'ORDER BY os_id ';
		$this->selectLoop($queryStr, array($lang), $callback, null);
	}
	/**
	 * ユーザ情報をユーザIDで取得
	 *
	 * @param int		$id					ユーザID
	 * @param array     $row				レコード
	 * @return bool							取得 = true, 取得なし= false
	 */
	function getLoginUserById($id, &$row)
	{
		$queryStr  = 'select * from _login_user ';
		$queryStr .=   'WHERE lu_id = ? AND lu_deleted = false';
		$ret = $this->selectRecord($queryStr, array($id), $row);
		return $ret;
	}
	/**
	 * 商品記録を更新
	 *
	 * @param int	  $id			商品ID
	 * @param string  $lang			言語ID
	 * @param array	$updateParam	更新パラメータ
	 * @return bool					true = 成功、false = 失敗
	 */
	function updateProductRecord($id, $lang, $updateParam)
	{
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $this->gEnv->getCurrentUserId();	// 現在のユーザ
		
		// パラメータエラーチェック
		$keys = array_keys($updateParam);
		if (in_array('pe_serial', $keys)) return false;
				
		// 既存データ取得
		$queryStr = 'SELECT * FROM product_record ';
		$queryStr .=  'WHERE pe_product_id = ? ';
		$queryStr .=    'AND pe_language_id = ? ';
		$ret = $this->selectRecord($queryStr, array($id, $lang), $stockRow);
		if ($ret){	// データが存在するとき
			// ##### データを更新 #####
			// レコード更新
			$queryStr = 'UPDATE product_record ';
			$queryStr .=  'SET ';
			$values = array();
			for ($i = 0; $i < count($keys); $i++){
				$queryStr .= $keys[$i] . ' = ?, ';
				$values[] = $updateParam[$keys[$i]];
			}
			$queryStr .= 'pe_update_user_id = ?, '; $values[] = $userId;
			$queryStr .= 'pe_update_dt = ? '; $values[] = $now;
			$queryStr .=  'WHERE pe_serial = ? ';
			$values[] = $stockRow['pe_serial'];
			$ret =$this->execStatement($queryStr, $values);
		} else {
			// ##### データを新規追加 #####
			// 新規レコード追加
			$queryStr = 'INSERT INTO product_record ';
			$queryStr .=  '(';
		
			$valueStr = '(';
			$values = array();
			for ($i = 0; $i < count($keys); $i++){
				$queryStr .= $keys[$i] . ', ';
				$valueStr .= '?, ';
				$values[] = $updateParam[$keys[$i]];
			}
			$queryStr .= 'pe_product_id, pe_language_id, pe_update_user_id, pe_update_dt) ';
			$valueStr .= '?, ?, ?, ?) ';
			$values[] = $id;
			$values[] = $lang;
			$values[] = $userId;
			$values[] = $now;
			
			$queryStr .=  'VALUES ';
			$queryStr .=  $valueStr;
			$ret =$this->execStatement($queryStr, $values);
		}
		return $ret;
	}
}
?>
