<?php
/**
 * index.php用コンテナクラス
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2012 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: ec_mainMemberinfoWidgetContainer.php 5440 2012-12-08 09:37:39Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/ec_mainBaseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/ec_mainMemberDb.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/ec_mainOrderDb.php');

class ec_mainMemberinfoWidgetContainer extends ec_mainBaseWidgetContainer
{
	private $db;	// DB接続オブジェクト
	private $orderDb;	// DB接続オブジェクト
	private $state;		// 都道府県
	private $genderArray;	// 性別選択メニュー用
	private $gender;	// 性別
	private $year;		// 生年月日(年)
	private $month;	// 生年月日(月)
	private $day;		// 生年月日(日)
	private $ecMailObj;	// メール連携オブジェクト
	const MAIL_OBJ_ID = 'ecmail';
	const PERSON_INFO_OPT_SPORTS = 'sports';		// 個人情報オプション(現在やっているスポーツ)
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DB接続オブジェクト作成
		$this->db = new ec_mainMemberDb();
		$this->orderDb = new ec_mainOrderDb();
		
		// メール連携オブジェクト取得
		$this->ecMailObj = $this->gInstance->getObject(self::MAIL_OBJ_ID);
		
		// 性別選択メニュー項目
		$this->genderArray = array(	array(	'name' => '男',	'value' => '1'),
									array(	'name' => '女',	'value' => '2'));
	}
	/**
	 * テンプレートファイルを設定
	 *
	 * _assign()でデータを埋め込むテンプレートファイルのファイル名を返す。
	 * 読み込むディレクトリは、「自ウィジェットディレクトリ/include/template」に固定。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。そのまま_assign()に渡る
	 * @return string 						テンプレートファイル名。テンプレートライブラリを使用しない場合は空文字列「''」を返す。
	 */
	function _setTemplate($request, &$param)
	{	
		return 'memberinfo.tmpl.html';
	}
	/**
	 * テンプレートにデータ埋め込む
	 *
	 * _setTemplate()で指定したテンプレートファイルにデータを埋め込む。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。_setTemplate()と共有。
	 * @param								なし
	 */
	function _assign($request, &$param)
	{
		$now = date("Y/m/d H:i:s");	// 現在日時
		$countryId = photo_shopCommonDef::DEFAULT_COUNTRY_ID;			// デフォルト国ID
				
		$firstname = $request->trimValueOf('item_firstname');			// 名前(名)
		$familyname = $request->trimValueOf('item_familyname');			// 名前(姓)
		$firstnameKana = $request->trimValueOf('item_firstname_kana');		// 名前カナ(名)
		$familynameKana = $request->trimValueOf('item_familyname_kana');	// 名前カナ(姓)
		$zipcode = $request->trimValueOf('item_zipcode');	// 郵便番号
		$this->state = $request->trimValueOf('item_state');	// 都道府県
		$address = $request->trimValueOf('item_address');	// 住所1
		$address2 = $request->trimValueOf('item_address2');	// 住所2
		$phone = $request->trimValueOf('item_phone');	// 電話番号
		$fax = $request->trimValueOf('item_fax');	// FAX
		$email = $request->trimValueOf('item_email');	// Email
		$email2 = $request->trimValueOf('item_email2');	// Email確認用
		$oldemail = $request->trimValueOf('oldemail');	// 旧Email
		$act = $request->trimValueOf('act');
		
		// フォトギャラリー追加分
		if (photo_shopCommonDef::MEMBER_INFO_OPTION){
			$this->gender = $request->trimValueOf('item_gender');		// 性別
			$this->year = $request->trimValueOf('item_year');		// 生年月日(年)
			$this->month = $request->trimValueOf('item_month');	// 生年月日(月)
			$this->day = $request->trimValueOf('item_day');		// 生年月日(日)
			$sports = $request->trimValueOf('item_sports');			// 現在やってるスポーツ
		}
		
		$replaceNew = false;		// データを再取得するかどうか
		if ($act == 'update'){			// 会員情報更新
			$this->checkInput($familyname, '名前(姓)');
			$this->checkInput($firstname, '名前(名)');
			$this->checkInput($familynameKana, '名前カナ(姓)');
			$this->checkInput($firstnameKana, '名前カナ(名)');
			
			// 住所登録が必要なとき追加分
/*			if ($this->_getConfig(photo_shopCommonDef::CF_USE_MEMBER_ADDRESS)){			// 住所登録が必要なとき
				$this->checkSingleByte($zipcode, '郵便番号');
				$this->checkNumeric($this->state, '都道府県');
				$this->checkInput($address, '住所');	
				$this->checkSingleByte($phone, '電話番号');
				if (!empty($fax)) $this->checkSingleByte($fax, 'FAX');// 空のときはチェックしない
			}*/
			
			$this->checkMailAddress($email, 'Eメール');
			$this->checkMailAddress($email2, 'Eメール(確認)');
			$isEmailChange = false;		// Eメールアドレスの変更の必要あるかどうか
			if ($this->getMsgCount() == 0){			// メールアドレスのチェック
				if ($email != $email2){
					$this->setAppErrorMsg('Eメールアドレスに誤りがあります');
				} else if ($email != $oldemail){		// Eメールに変更があるとき
					$isEmailChange = true;// Eメールアドレスの変更の必要あり
					if ($this->_db->isExistsAccount($email)){// 新しいメールアドレスがログインIDとして既に登録されているかチェック
						$this->setAppErrorMsg('このEメールアドレスは既に登録されています');
					}
				}
			}
			
			// フォトギャラリー追加分
			if (photo_shopCommonDef::MEMBER_INFO_OPTION){
				$this->checkInput($this->gender, '性別');
				if (empty($this->year) || empty($this->month) || empty($this->day)) $this->setUserErrorMsg('生年月日が入力されていません');
				$this->checkInput($sports, '現在やっているスポーツ');
			}
			
			// エラーなしの場合は、更新
			if ($this->getMsgCount() == 0){
				// トランザクションスタート
				$this->db->startTransaction();

				// 登録済み会員情報を取得
				$memberSerialNo = 0;
				$ret = $this->orderDb->getMember($this->_userId, $memberRow);
				if ($ret){
					if ($memberRow['sm_type'] == 1){		// 個人メンバーのとき
						$memberSerialNo = $memberRow['sm_serial'];
					}
				}
				if ($ret) $ret = $this->db->getMemberBySerial(0/*正会員*/, $memberSerialNo, $row);
				if ($ret){
					$personId = $row['sm_person_info_id'];
					$loginUserId = $row['sm_login_user_id'];
					$memberNo = $row['sm_member_no'];//	会員番号は変更しない
					$ret = $this->db->getPersonInfo($personId, $row, $personOptions);
					if ($ret){
						$addressId = $row['pi_address_id'];
						//$email = $row['pi_email'];
						
						// 変更しない値を取得
//						$gender		= $row['pi_gender'];
//						$birthday	= $row['pi_birthday'];
						$mobile		= $row['pi_mobile'];
					}
				}
				// 住所登録
				if ($ret) $ret = $this->db->updateAddress($addressId, $this->_langId, '', $zipcode, $this->state, $address, $address2, $phone, $fax, $countryId, $this->_userId, $now, $addressId);

				// 個人情報登録
				$personOptions = array();
				if (photo_shopCommonDef::MEMBER_INFO_OPTION){
					$personOptions[self::PERSON_INFO_OPT_SPORTS] = $sports;
					$birthday = $this->convertToProperDate($this->year . '/' . $this->month . '/' . $this->day);
				} else {
					$birthday = $this->gEnv->getInitValueOfDate();
					$this->gender = 0;
				}
				if ($ret) $ret = $this->db->updatePersonInfo($personId, $this->_langId, $firstname, $familyname, $firstnameKana, $familynameKana, $this->gender, $birthday, $email, $mobile, $addressId, $this->_userId, $now, $personalInfoId, $personOptions);

				// 会員情報更新
				if ($ret) $ret = $this->db->updateMember($memberSerialNo, $this->_langId, 1/* 個人 */, 0/* 法人情報ID */, $personalInfoId, $memberNo, $loginUserId, $this->_userId, $now, $newSerial);
			
				// ### ログインユーザ名を更新 ###
				if ($ret){
					$fieldArray = array();
					$fieldArray['lu_name'] = $familyname . $firstname;		// 名前を更新
					if ($isEmailChange) $fieldArray['lu_account'] = $email;	// Eメールアドレスの変更の必要があるときは変更
					$ret = $this->_db->updateLoginUserByField($this->_userId/*ログイン中のユーザ*/, $fieldArray, $newSerial);
				}
													
				// トランザクション終了
				$ret = $this->db->endTransaction();
				if ($ret){
					$this->setGuidanceMsg('データを更新しました');
					
					// ######## 会員登録のメールをイントラネット側に送信 ########
					//$this->ecMailObj->sendMemberInfoToBackoffice(1/*更新*/, $this->_userId);
					
					$replaceNew = true;			// 会員情報を再取得
				} else {
					$this->setAppErrorMsg('データ更新に失敗しました');
				}
			}
		} else {		// 初期状態のとき
			$replaceNew = true;			// 会員情報を再取得
		}
		if ($replaceNew){		// 会員情報を取得のとき
			// 会員情報を取得
			$ret = $this->orderDb->getMember($this->_userId, $memberRow);
			if ($ret){
				if ($memberRow['sm_type'] == 1){		// 個人メンバーのとき
					// 個人情報取得
					$ret = $this->db->getPersonInfo($memberRow['sm_person_info_id'], $personRow, $personOptions);
					if ($ret){
						$ret = $this->orderDb->getAddress($personRow['pi_address_id'], $addressRow);
						if ($ret){
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
							$this->state = $addressRow['ad_state_id'];
							
							// フォトギャラリー追加分
							if (photo_shopCommonDef::MEMBER_INFO_OPTION){
								$this->gender		= $personRow['pi_gender'];
								$this->timestampToYearMonthDay($personRow['pi_birthday'], $this->year, $this->month, $this->day);
								$sports				= $personOptions[self::PERSON_INFO_OPT_SPORTS];
							}
						}
					}
				}
			} else {
				$this->setAppErrorMsg('このユーザはお買い物会員ではありません');
			}
		}
		// ##### フォトギャラリー追加分 #####
		if (photo_shopCommonDef::MEMBER_INFO_OPTION){
			$this->tmpl->setAttribute('show_member_info_option', 'visibility', 'visible');
			
			// 性別選択メニュー作成
			$this->createGenderMenu();
		
			// 生年月日メニュー作成
			$this->createBirthMenu();
		}
		
		// 住所入力エリア表示制御
		if ($this->_getConfig(photo_shopCommonDef::CF_USE_MEMBER_ADDRESS)) $this->tmpl->setAttribute('input_address', 'visibility', 'visible');
		
		// 入力値を戻す
		$this->tmpl->addVar("_widget", "firstname", $firstname);
		$this->tmpl->addVar("_widget", "familyname", $familyname);
		$this->tmpl->addVar("_widget", "firstname_kana", $firstnameKana);
		$this->tmpl->addVar("_widget", "familyname_kana", $familynameKana);
		$this->tmpl->addVar("_widget", "email", $email);
		$this->tmpl->addVar("_widget", "email2", $email2);
		$this->tmpl->addVar("_widget", "old_email", $oldemail);
		if ($this->_getConfig(photo_shopCommonDef::CF_USE_MEMBER_ADDRESS)){			// 住所登録が必要なとき
			$this->tmpl->addVar("input_address", "zipcode", $zipcode);
			$this->tmpl->addVar("input_address", "address", $address);
			$this->tmpl->addVar("input_address", "address2", $address2);
			$this->tmpl->addVar("input_address", "phone", $phone);
			$this->tmpl->addVar("input_address", "fax", $fax);
			
			// 都道府県を設定
			$this->db->getAllState('JPN', $this->_langId, array($this, 'stateLoop'));
		}
		// フォトギャラリー追加分
		if (photo_shopCommonDef::MEMBER_INFO_OPTION){
			$this->tmpl->addVar("show_member_info_option", "sports", $sports);		// 現在やっているスポーツ
		}
		
		// 遷移先を設定
		$this->tmpl->addVar("_widget", "regist_url", $this->getUrl($this->gEnv->createCurrentPageUrl() . '&task=memberinfo', true));		// 会員登録用URL
		$this->tmpl->addVar("_widget", "cancel_url", $this->getUrl($this->gEnv->createCurrentPageUrl(), true));		// キャンセル用URL
	}
	/**
	 * 取得した都道府県をテンプレートに設定する
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function stateLoop($index, $fetchedRow, $param)
	{
		$selected = '';
		if ($fetchedRow['gz_id'] == $this->state){		// 選択中の都道府県
			$selected = 'selected';
		}

		$row = array(
			'value'    => $this->convertToDispString($fetchedRow['gz_id']),			// ID
			'name'     => $this->convertToDispString($fetchedRow['gz_name']),			// 表示名
			'selected' => $selected														// 選択中かどうか
		);
		$this->tmpl->addVars('state_list', $row);
		$this->tmpl->parseTemplate('state_list', 'a');
		return true;
	}
	/**
	 * 性別選択メニュー作成
	 *
	 * @return なし
	 */
	function createGenderMenu()
	{
		for ($i = 0; $i < count($this->genderArray); $i++){
			$value = $this->genderArray[$i]['value'];
			$name = $this->genderArray[$i]['name'];
			
			$selected = '';
			if ($value == $this->gender) $selected = 'selected';
			
			$row = array(
				'value'    => $value,			// ページID
				'name'     => $name,			// ページ名
				'selected' => $selected														// 選択中かどうか
			);
			$this->tmpl->addVars('gender_list', $row);
			$this->tmpl->parseTemplate('gender_list', 'a');
		}
	}
	/**
	 * 生年月日メニュー作成
	 *
	 * @return なし
	 */
	function createBirthMenu()
	{
		$nowYear = date("Y");	// 現在年
		$startYear = $nowYear - 100;
		for ($i = $startYear; $i < $nowYear; $i++){
			$value = $name = $i;
			
			$selected = '';
			if ($value == $this->year) $selected = 'selected';
			
			$row = array(
				'value'    => $value,			// 値
				'name'     => $name,			// 表示タイトル
				'selected' => $selected														// 選択中かどうか
			);
			$this->tmpl->addVars('year_list', $row);
			$this->tmpl->parseTemplate('year_list', 'a');
		}
		
		for ($i = 1; $i <= 12; $i++){
			$value = $name = $i;
			
			$selected = '';
			if ($value == $this->month) $selected = 'selected';
			
			$row = array(
				'value'    => $value,			// 値
				'name'     => $name,			// 表示タイトル
				'selected' => $selected														// 選択中かどうか
			);
			$this->tmpl->addVars('month_list', $row);
			$this->tmpl->parseTemplate('month_list', 'a');
		}
		
		for ($i = 1; $i <= 31; $i++){
			$value = $name = $i;
			
			$selected = '';
			if ($value == $this->day) $selected = 'selected';
			
			$row = array(
				'value'    => $value,			// 値
				'name'     => $name,			// 表示タイトル
				'selected' => $selected														// 選択中かどうか
			);
			$this->tmpl->addVars('day_list', $row);
			$this->tmpl->parseTemplate('day_list', 'a');
		}
	}
}
?>
