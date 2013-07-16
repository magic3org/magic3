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
 * @version    SVN: $Id: ec_mainMemberDb.php 5434 2012-12-06 12:32:21Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getDbPath() . '/baseDb.php');

class ec_mainMemberDb extends BaseDb
{
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
	 * 都道府県名から都道府県IDを取得
	 *
	 * @param string	$coutryId			国ID
	 * @param string	$lang				言語
	 * @param string	$name				名前
	 * @return string						地域ID
	 */
	function getStateIdByName($coutryId, $lang, $name)
	{
		$queryStr = 'SELECT * FROM geo_zone ';
		$queryStr .=  'WHERE gz_country_id = ? AND gz_type = 1 AND gz_language_id = ? AND gz_name = ?';
		$ret = $this->selectRecord($queryStr, array($coutryId, $lang, $name), $row);
		if ($ret){
			return $row['gz_id'];
		} else {
			return '0';
		}
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
	 * 会員総数を取得
	 *
	 * @param int	$type					0=正会員、1=仮会員
	 * @return int							会員総数
	 */
	function getMemberCount($type)
	{
		if ($type == 0){
			$queryStr = 'SELECT * FROM shop_member ';
			$queryStr .=  'WHERE sm_deleted = false ';// 削除されていない
			return $this->selectRecordCount($queryStr, array());
		} else {
			$queryStr = 'SELECT * FROM shop_tmp_member ';
			$queryStr .=  'WHERE sb_deleted = false ';// 削除されていない
			return $this->selectRecordCount($queryStr, array());
		}
	}
	/**
	 * 会員一覧を取得
	 *
	 * @param int	$type					0=正会員、1=仮会員
	 * @param int		$limit				取得する項目数(-1のときすべて)
	 * @param int		$offset				取得する先頭位置(0～)
	 * @param function	$callback			コールバック関数
	 * @return 			なし
	 */
	function getMemberList($type, $limit, $offset, $callback)
	{
		if ($type == 0){		// 正会員
			$queryStr = 'SELECT * FROM shop_member LEFT JOIN person_info ON sm_person_info_id = pi_id AND pi_deleted = false LEFT JOIN address ON pi_address_id = ad_id AND ad_deleted = false LEFT JOIN geo_zone ON ad_state_id = gz_id LEFT JOIN _login_user ON sm_create_user_id = lu_id AND lu_deleted = false ';
			$queryStr .=  'WHERE sm_deleted = false ';// 削除されていない
			if ($limit <= -1){		// すべて取得のとき
				$queryStr .=  'ORDER BY sm_id';
			} else {
				$queryStr .=  'ORDER BY sm_id limit ' . $limit . ' offset ' . $offset;
			}
			$this->selectLoop($queryStr, array(), $callback, null);
		} else {
			$queryStr = 'SELECT * FROM shop_tmp_member LEFT JOIN person_info ON sb_person_info_id = pi_id AND pi_deleted = false LEFT JOIN address ON pi_address_id = ad_id AND ad_deleted = false LEFT JOIN geo_zone ON ad_state_id = gz_id LEFT JOIN _login_user ON sb_create_user_id = lu_id AND lu_deleted = false ';
			$queryStr .=  'WHERE sb_deleted = false ';// 削除されていない
			$queryStr .=  'ORDER BY sb_serial limit ' . $limit . ' offset ' . $offset;
			$this->selectLoop($queryStr, array(), $callback, null);
		}
	}
	/**
	 * 会員情報をシリアル番号で取得
	 *
	 * @param int	$type					0=正会員、1=仮会員
	 * @param int		$serial				シリアル番号
	 * @param array     $row				レコード
	 * @return bool							取得 = true, 取得なし= false
	 */
	function getMemberBySerial($type, $serial, &$row)
	{
		if ($type == 0){		// 正会員のとき
			$queryStr  = 'SELECT * FROM shop_member LEFT JOIN person_info ON sm_person_info_id = pi_id AND pi_deleted = false LEFT JOIN address ON pi_address_id = ad_id AND ad_deleted = false LEFT JOIN geo_zone ON ad_state_id = gz_id LEFT JOIN _login_user ON sm_create_user_id = lu_id AND lu_deleted = false ';
			$queryStr .=   'WHERE sm_serial = ? ';
			$ret = $this->selectRecord($queryStr, array($serial), $row);
			return $ret;
		} else {
			$queryStr  = 'SELECT * FROM shop_tmp_member LEFT JOIN person_info ON sb_person_info_id = pi_id AND pi_deleted = false LEFT JOIN address ON pi_address_id = ad_id AND ad_deleted = false LEFT JOIN geo_zone ON ad_state_id = gz_id LEFT JOIN _login_user ON sb_create_user_id = lu_id AND lu_deleted = false ';
			$queryStr .=   'WHERE sb_serial = ? ';
			$ret = $this->selectRecord($queryStr, array($serial), $row);
			return $ret;
		}
	}
	/**
	 * 会員の削除
	 *
	 * @param int	$type			0=正会員、1=仮会員
	 * @param int $serialNo			シリアルNo
	 * @return						true=成功、false=失敗
	 */
	function delMemberBySerial($type, $serialNo)
	{
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $this->gEnv->getCurrentUserId();	// 現在のユーザ
				
		// トランザクション開始
		$this->startTransaction();
		
		if ($type == 0){		// 正会員のとき
			// 仮会員のときは、ログインユーザも削除
			// 指定のシリアルNoのレコードが削除状態でないかチェック
			$queryStr  = 'select * from shop_member ';
			$queryStr .=   'where sm_deleted = false ';		// 未削除
			$queryStr .=     'and sm_serial = ? ';
			$ret = $this->selectRecord($queryStr, array($serialNo), $row);
			// 存在しない場合は、既に削除されたとして終了
			if (!$ret){
				$this->endTransaction();
				return false;
			}
		
			// 個人情報を削除
			$this->delPersonInfo($row['sm_person_info_id'], $now, $userId);
			
			// レコードを削除
			$queryStr  = 'UPDATE shop_member ';
			$queryStr .=   'SET sm_deleted = true, ';	// 削除
			$queryStr .=     'sm_update_user_id = ?, ';
			$queryStr .=     'sm_update_dt = ? ';
			$queryStr .=   'WHERE sm_serial = ?';
			$this->execStatement($queryStr, array($userId, $now, $serialNo));
		} else {				// 仮会員のとき
			// 仮会員のときは、ログインユーザも削除
			// 指定のシリアルNoのレコードが削除状態でないかチェック
			$queryStr  = 'select * from shop_tmp_member ';
			$queryStr .=   'where sb_deleted = false ';		// 未削除
			$queryStr .=     'and sb_serial = ? ';
			$ret = $this->selectRecord($queryStr, array($serialNo), $row);
			// 存在しない場合は、既に削除されたとして終了
			if (!$ret){
				$this->endTransaction();
				return false;
			}
			
			// 個人情報を削除
			$this->delPersonInfo($row['sb_person_info_id'], $now, $userId);
			
			// レコードを削除
			$queryStr  = 'UPDATE shop_tmp_member ';
			$queryStr .=   'SET sb_deleted = true, ';	// 削除
			$queryStr .=     'sb_update_user_id = ?, ';
			$queryStr .=     'sb_update_dt = ? ';
			$queryStr .=   'WHERE sb_serial = ?';
			$this->execStatement($queryStr, array($userId, $now, $serialNo));
			
			// ログインユーザレコードを削除
			$queryStr  = 'select * from _login_user ';
			$queryStr .=   'where lu_deleted = false ';		// 未削除
			$queryStr .=     'and lu_id = ? ';
			$ret = $this->selectRecord($queryStr, array($row['sb_login_user_id']), $loginRow);
			if ($ret){
				$queryStr  = 'UPDATE _login_user ';
				$queryStr .=   'SET lu_deleted = true, ';	// 削除
				$queryStr .=     'lu_update_user_id = ?, ';
				$queryStr .=     'lu_update_dt = ? ';
				$queryStr .=   'WHERE lu_serial = ?';
				$this->execStatement($queryStr, array($userId, $now, $loginRow['lu_serial']));
			}
		}
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * 個人情報の削除
	 *
	 * @param int	$id				個人情報ID
	 * @param timestamp $now		現在日時
	 * @param int	$userId			更新者
	 * @return						true=成功、false=失敗
	 */
	function delPersonInfo($id, $now, $userId)
	{
		$queryStr  = 'select * from person_info ';
		$queryStr .=   'where pi_deleted = false ';		// 未削除
		$queryStr .=     'and pi_id = ? ';
		$ret = $this->selectRecord($queryStr, array($id), $row);
		if (!$ret) return true;

		// 登録住所を削除
		$queryStr  = 'UPDATE address ';
		$queryStr .=   'SET ad_deleted = true, ';	// 削除
		$queryStr .=     'ad_update_user_id = ?, ';
		$queryStr .=     'ad_update_dt = ? ';
		$queryStr .=   'WHERE ad_id = ? AND ad_deleted = false';
		$this->execStatement($queryStr, array($userId, $now, $row['pi_address_id']));
		
		// 個人情報を削除
		$queryStr  = 'UPDATE person_info ';
		$queryStr .=   'SET pi_deleted = true, ';	// 削除
		$queryStr .=     'pi_update_user_id = ?, ';
		$queryStr .=     'pi_update_dt = ? ';
		$queryStr .=   'WHERE pi_serial = ?';
		$this->execStatement($queryStr, array($userId, $now, $row['pi_serial']));
		return true;
	}
	/**
	 * 新規ユーザの追加
	 *
	 * @param int  $type			追加するユーザのタイプ(0=仮会員、1=正会員)
	 * @param string  $name			名前
	 * @param string  $account		アカウント
	 * @param string  $password		パスワード
	 * @param string  $widgetId		ウィジェットID
	 * @param int     $userId		更新者ID
	 * @param string  $now			現在日時
	 * @param int     $newId		新規に作成したログインユーザID
	 * @return						true=成功、false=失敗
	 */
	function addUser($type, $name, $account, $password, $widgetId, $userId, $now, &$newId)
	{
		// 新規IDを作成
		$newId = 1;
		$queryStr = 'select max(lu_id) as ms from _login_user ';
		$ret = $this->selectRecord($queryStr, array(), $row);
		if ($ret) $newId = $row['ms'] + 1;
		
		// ユーザ種別を設定
		if ($type == 0){
			$userType = UserInfo::USER_TYPE_TMP;		// 一時ユーザ
		} else {
			$userType = UserInfo::USER_TYPE_NORMAL;		// 一般ユーザ
		}
		$subject = ',photo,';		// フォトギャラリーコマース
		
		// 新規レコードを追加
		$queryStr  = 'INSERT INTO _login_user (';
		$queryStr .=   'lu_id, ';
		$queryStr .=   'lu_history_index, ';
		$queryStr .=   'lu_name, ';
		$queryStr .=   'lu_account, ';
		$queryStr .=   'lu_password, ';
		$queryStr .=   'lu_user_type, ';
		$queryStr .=   'lu_assign, ';
		$queryStr .=   'lu_enable_login, ';
		$queryStr .=   'lu_widget_id, ';
		$queryStr .=   'lu_create_user_id, ';
		$queryStr .=   'lu_create_dt ';
		$queryStr .= ') VALUES (';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   'md5(?), ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?) ';
		$ret = $this->execStatement($queryStr, array($newId, 0, $name, $account, $password, $userType, $subject, 1, $widgetId, $userId, $now));
		return $ret;
	}
	/**
	 * アドレスの新規追加、更新
	 *
	 * @param int     $id			アドレスID(0のときは新規追加)
	 * @param string  $lang			言語
	 * @param string  $title		タイトル
	 * @param string  $zipcode		郵便番号
	 * @param int     $stateId		都道府県
	 * @param string  $address1		住所前半
	 * @param string  $address2		住所後半(未使用)
	 * @param string  $phone		電話番号
	 * @param string  $fax			FAX
	 * @param string  $countryId	国ID
	 * @param int     $userId		更新者ID
	 * @param string  $now			現在日時
	 * @param int     $newId		新規に作成したアドレスのID
	 * @return						true=成功、false=失敗
	 */
	function updateAddress($id, $langId, $title, $zipcode, $stateId, $address1, $address2, $phone, $fax, $countryId, $userId, $now, &$newId)
	{
		if ($id == 0){
			// 新規IDを作成
			$newId = 1;
			$queryStr = 'select max(ad_id) as ms from address ';
			$ret = $this->selectRecord($queryStr, array(), $row);
			if ($ret) $newId = $row['ms'] + 1;
			
			$historyIndex = 0;
		} else {
			// 指定のレコードの履歴インデックス取得
			$queryStr  = 'SELECT * FROM address ';
			$queryStr .=   'WHERE ad_id = ? ';
			$queryStr .=     'AND ad_language_id = ? ';
			$queryStr .=  'ORDER BY ad_history_index desc ';
			$ret = $this->selectRecord($queryStr, array($id, $langId), $row);
			if (!$ret) return false;
			
			if ($row['ad_deleted']) return false;		// 削除されていれば終了
			$historyIndex = $row['ad_history_index'] + 1;
			
			// レコードを削除
			$queryStr  = 'UPDATE address ';
			$queryStr .=   'SET ad_deleted = true, ';
			$queryStr .=   'ad_update_user_id = ?, ';
			$queryStr .=   'ad_update_dt = ? ';			
			$queryStr .=   'WHERE ad_serial = ? ';
			$this->execStatement($queryStr, array($userId, $now, $row['ad_serial']));
			
			$newId = $id;
		}
		// 新規レコードを追加
		$queryStr  = 'INSERT INTO address (';
		$queryStr .=   'ad_id, ';
		$queryStr .=   'ad_language_id, ';
		$queryStr .=   'ad_history_index, ';
		$queryStr .=   'ad_title, ';
		$queryStr .=   'ad_zipcode, ';
		$queryStr .=   'ad_state_id, ';
		$queryStr .=   'ad_address1, ';
		$queryStr .=   'ad_address2, ';
		$queryStr .=   'ad_phone, ';
		$queryStr .=   'ad_fax, ';
		$queryStr .=   'ad_country_id, ';
		$queryStr .=   'ad_create_user_id, ';
		$queryStr .=   'ad_create_dt ';
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
		$ret = $this->execStatement($queryStr, array($newId, $langId, $historyIndex, $title, $zipcode, $stateId, $address1, $address2, $phone, $fax, $countryId, $userId, $now));
		return $ret;
	}
	/**
	 * 個人情報の新規追加、更新
	 *
	 * @param int     $id			個人情報ID(0のときは新規追加)
	 * @param string  $lang			言語
	 * @param string  $firstname	名前(名)
	 * @param string  $familyname	名前(姓)
	 * @param string  $firstname_kana	名前カナ(名)
	 * @param string  $familyname_kana	名前カナ(姓)
	 * @param int     $gender		性別(0=男、1=女)
	 * @param date    $birthday		生年月日
	 * @param string  $email		Eメール
	 * @param string  $mobile		携帯電話
	 * @param int     $addressId	住所ID
	 * @param int     $userId		更新者ID
	 * @param string  $now			現在日時
	 * @param int     $newId		新規に作成した個人情報のID
	 * @param array   $optionValues	オプション値
	 * @return						true=成功、false=失敗
	 */
	function updatePersonInfo($id, $langId, $firstname, $familyname, $firstname_kana, $familyname_kana, $gender, $birthday, $email, $mobile, $addressId, $userId, $now, &$newId, $optionValues)
	{
		if ($id == 0){
			// 新規IDを作成
			$newId = 1;
			$queryStr = 'select max(pi_id) as ms from person_info ';
			$ret = $this->selectRecord($queryStr, array(), $row);
			if ($ret) $newId = $row['ms'] + 1;
			
			$historyIndex = 0;
		} else {
			// 指定のレコードの履歴インデックス取得
			$queryStr  = 'SELECT * FROM person_info ';
			$queryStr .=   'WHERE pi_id = ? ';
			$queryStr .=     'AND pi_language_id = ? ';
			$queryStr .=  'ORDER BY pi_history_index desc ';
			$ret = $this->selectRecord($queryStr, array($id, $langId), $row);
			if (!$ret) return false;
			
			if ($row['pi_deleted']) return false;		// 削除されていれば終了
			$historyIndex = $row['pi_history_index'] + 1;
			
			// レコードを削除
			$queryStr  = 'UPDATE person_info ';
			$queryStr .=   'SET pi_deleted = true, ';
			$queryStr .=   'pi_update_user_id = ?, ';
			$queryStr .=   'pi_update_dt = ? ';			
			$queryStr .=   'WHERE pi_serial = ? ';
			$this->execStatement($queryStr, array($userId, $now, $row['pi_serial']));
			
			$newId = $id;
		}
		// 新規レコードを追加
		$queryStr  = 'INSERT INTO person_info (';
		$queryStr .=   'pi_id, ';
		$queryStr .=   'pi_language_id, ';
		$queryStr .=   'pi_history_index, ';
		$queryStr .=   'pi_first_name, ';
		$queryStr .=   'pi_family_name, ';
		$queryStr .=   'pi_first_name_kana, ';
		$queryStr .=   'pi_family_name_kana, ';
		$queryStr .=   'pi_gender, ';
		$queryStr .=   'pi_birthday, ';
		$queryStr .=   'pi_email, ';
		$queryStr .=   'pi_mobile, ';
		$queryStr .=   'pi_address_id, ';
		$queryStr .=   'pi_create_user_id, ';
		$queryStr .=   'pi_create_dt ';
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
		$queryStr .=   '?) ';
		$ret = $this->execStatement($queryStr, array($newId, $langId, $historyIndex, $firstname, $familyname, $firstname_kana, $familyname_kana, $gender, $birthday, $email, $mobile, $addressId, $userId, $now));
		
		// 個人情報オプション値を格納
		if (!empty($optionValues)){
			// 個人情報のシリアル番号を取得
			$queryStr = 'SELECT MAX(pi_serial) AS ns FROM person_info ';
			$ret = $this->selectRecord($queryStr, array(), $row);
			if ($ret) $newSerial = $row['ns'];
			
			$keys = array_keys($optionValues);// キーを取得
			for ($i = 0; $i < count($keys); $i++){
				// クエリー作成
				$queryStr  = 'INSERT INTO person_info_opt_value (';
				$queryStr .=   'pl_person_serial, ';
				$queryStr .=   'pl_field_id, ';
				$queryStr .=   'pl_value) VALUES (?, ?, ?)';
				$ret = $this->execStatement($queryStr, array($newSerial, $keys[$i], $optionValues[$keys[$i]]));
				if (!$ret) break;
			}
		}
		return $ret;
	}
	/**
	 * 新規仮会員の追加
	 *
	 * @param string  $lang				言語
	 * @param int     $type				会員タイプ(0=未設定、1=個人、2=法人)
	 * @param int     $companyInfoId	法人情報ID
	 * @param int     $personInfoId		個人情報ID
	 * @param int     $loginUserId		ログインユーザID
	 * @param int     $userId		更新者ID
	 * @param string  $now			現在日時
	 * @return						true=成功、false=失敗
	 */
	function addTmpMember($lang, $type, $companyInfoId, $personInfoId, $loginUserId, $userId, $now)
	{
		// 新規レコードを追加
		$queryStr  = 'INSERT INTO shop_tmp_member (';
		$queryStr .=   'sb_language_id, ';
		$queryStr .=   'sb_type, ';
		$queryStr .=   'sb_company_info_id, ';
		$queryStr .=   'sb_person_info_id, ';
		$queryStr .=   'sb_login_user_id, ';
		$queryStr .=   'sb_create_user_id, ';
		$queryStr .=   'sb_create_dt ';
		$queryStr .= ') VALUES (';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?) ';
		$ret = $this->execStatement($queryStr, array($lang, $type, $companyInfoId, $personInfoId, $loginUserId, $userId, $now));
		return $ret;
	}
	/**
	 * 会員の更新、新規追加
	 *
	 * @param int     $serial			更新する会員情報のシリアル番号(0のときは新規追加)
	 * @param string  $lang				言語
	 * @param int     $type				会員タイプ(0=未設定、1=個人、2=法人)
	 * @param int     $companyInfoId	法人情報ID
	 * @param int     $personInfoId		個人情報ID
	 * @param string  $memberNo				会員No
	 * @param int     $loginUserId		ログインユーザID
	 * @param int     $userId		更新者ID
	 * @param string  $now			現在日時
	 * @param int     $newSerial	新規シリアル番号
	 * @return						true=成功、false=失敗
	 */
	function updateMember($serial, $lang, $type, $companyInfoId, $personInfoId, $memberNo, $loginUserId, $userId, $now, &$newSerial)
	{
		if ($serial == 0){
			// 新規IDを作成
			$newId = 1;
			$queryStr = 'select max(sm_id) as ms from shop_member ';
			$ret = $this->selectRecord($queryStr, array(), $maxRow);
			if ($ret) $newId = $maxRow['ms'] + 1;
		
			$historyIndex = 0;
		} else {
			// 前レコードの削除状態チェック
			$queryStr = 'SELECT * FROM shop_member ';
			$queryStr .=  'WHERE sm_serial = ? ';
			$ret = $this->selectRecord($queryStr, array($serial), $row);
			if (!$ret) return;
			
			if ($row['sm_deleted']) return false;		// 削除されていれば終了
			$historyIndex = $row['sm_history_index'] + 1;
			$newId = $row['sm_id'];
			
			// レコードを削除
			$queryStr  = 'UPDATE shop_member ';
			$queryStr .=   'SET sm_deleted = true, ';
			$queryStr .=   'sm_update_user_id = ?, ';
			$queryStr .=   'sm_update_dt = ? ';			
			$queryStr .=   'WHERE sm_serial = ? ';
			$this->execStatement($queryStr, array($userId, $now, $serial));
		}
		// 新規レコードを追加
		$queryStr  = 'INSERT INTO shop_member (';
		$queryStr .=   'sm_id, ';
		$queryStr .=   'sm_history_index, ';
		$queryStr .=   'sm_language_id, ';
		$queryStr .=   'sm_type, ';
		$queryStr .=   'sm_company_info_id, ';
		$queryStr .=   'sm_person_info_id, ';
		$queryStr .=   'sm_member_no, ';
		$queryStr .=   'sm_login_user_id, ';
		$queryStr .=   'sm_create_user_id, ';
		$queryStr .=   'sm_create_dt ';
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
		$queryStr .=   '?) ';
		$ret = $this->execStatement($queryStr, array($newId, $historyIndex, $lang, $type, $companyInfoId, $personInfoId, $memberNo, $loginUserId, $userId, $now));

		// 新規のシリアル番号取得
		$queryStr = 'select max(sm_serial) as ns from shop_member ';
		$ret = $this->selectRecord($queryStr, array(), $row);
		if ($ret) $newSerial = $row['ns'];
		return true;
	}
	/**
	 * 個人情報を取得
	 *
	 * @param int $id				個人情報ID
	 * @param array $row			取得レコード
	 * @param array $optValues		個人情報オプション値
	 * @return						true=成功、false=失敗
	 */
	function getPersonInfo($id, &$row, &$optValues)
	{
		$queryStr = 'SELECT * FROM person_info WHERE pi_id = ? AND pi_deleted = false';
		$retValue = $this->selectRecord($queryStr, array($id), $row);
		
		// 個人情報オプション値取得
		$optValues = array();
		$queryStr = 'SELECT * FROM person_info_opt_value WHERE pl_person_serial = ?';
		$ret = $this->selectRecords($queryStr, array($row['pi_serial']), $rows);
		for ($i = 0; $i < count($rows); $i++){
			$optValues[$rows[$i]['pl_field_id']] = $rows[$i]['pl_value'];
		}
		return $retValue;
	}
	/**
	 * 会員Noが存在するかチェック
	 *
	 * @param string $no	会員No
	 * @return				true=存在する、false=存在しない
	 */
	function isExistsMemberNo($no)
	{
		$queryStr = 'SELECT * from shop_member ';
		$queryStr .=  'WHERE sm_member_no = ? ';
		$queryStr .=    'AND sm_deleted = false';
		return $this->isRecordExists($queryStr, array($no));
	}
	/**
	 * 会員Noで会員情報を取得
	 *
	 * @param string $no	会員No
	 * @param array $row			取得レコード
	 * @return						true=成功、false=失敗
	 */
	function getMemberByMemberNo($no, &$row)
	{
		$queryStr = 'SELECT * from shop_member ';
		$queryStr .=  'WHERE sm_member_no = ? ';
		$queryStr .=    'AND sm_deleted = false';
		return $this->selectRecord($queryStr, array($no), $row);
	}
	/**
	 * 会員IDで会員情報のシリアル番号を取得
	 *
	 * @param string $id	会員ID
	 * @return int			シリアル番号(見つからないときは0)
	 */
	function getMemberSerialById($id)
	{
		$serial = 0;
		$queryStr = 'SELECT * from shop_member ';
		$queryStr .=  'WHERE sm_id = ? ';
		$queryStr .=    'AND sm_deleted = false';
		$ret = $this->selectRecord($queryStr, array($id), $row);
		if ($ret) $serial = $row['sm_serial'];
		return $serial;
	}
}
?>
