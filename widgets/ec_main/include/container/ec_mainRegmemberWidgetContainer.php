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
 * @version    SVN: $Id: ec_mainRegmemberWidgetContainer.php 5572 2013-01-23 08:43:39Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/ec_mainBaseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/ec_mainMemberDb.php');

class ec_mainRegmemberWidgetContainer extends ec_mainBaseWidgetContainer
{
	private $db;	// DB接続オブジェクト
	private $state;		// 都道府県
	private $genderArray;	// 性別選択メニュー用
	private $gender;	// 性別
	private $year;		// 生年月日(年)
	private $month;	// 生年月日(月)
	private $day;		// 生年月日(日)
	const REGIST_MEMBER_FORM = 'regist_member';		// パスワード送信用フォーム
	const PERSON_INFO_OPT_SPORTS = 'sports';		// 個人情報オプション(現在やっているスポーツ)
	const SHOW_ADDRESS = false;						// 住所入力領域を表示するかどうか
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DB接続オブジェクト作成
		$this->db = new ec_mainMemberDb();
		
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
		return 'regmember.tmpl.html';
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
		$email = $request->trimValueOf('item_email');	// Email
		$email2 = $request->trimValueOf('item_email2');	// Email確認用

		// 住所登録が必要なとき追加分
		$zipcode = $request->trimValueOf('item_zipcode');	// 郵便番号
		$this->state = $request->trimValueOf('item_state');	// 都道府県
		if (empty($this->state)) $this->state = $request->trimValueOf('state');	// 都道府県
		$address = $request->trimValueOf('item_address');	// 住所
		$address2 = $request->trimValueOf('item_address2');	// 住所2
		$phone = $request->trimValueOf('item_phone');	// 電話番号
		$fax = $request->trimValueOf('item_fax');	// FAX

		// フォトギャラリー追加分
		if (photo_shopCommonDef::MEMBER_INFO_OPTION){
			$this->gender = $request->trimValueOf('item_gender');		// 性別
			$this->year = $request->trimValueOf('item_year');		// 生年月日(年)
			$this->month = $request->trimValueOf('item_month');	// 生年月日(月)
			$this->day = $request->trimValueOf('item_day');		// 生年月日(日)
			$sports = $request->trimValueOf('item_sports');			// 現在やってるスポーツ
		}
		
		$act = $request->trimValueOf('act');
		if ($act == 'check'){			// 会員情報エラーチェック	
			$this->checkInput($familyname, '名前(姓)');		
			$this->checkInput($firstname, '名前(名)');
			$this->checkInput($familynameKana, '名前カナ(姓)');
			$this->checkInput($firstnameKana, '名前カナ(名)');
			$this->checkMailAddress($email, 'Eメール');
			$this->checkMailAddress($email2, 'Eメール(確認)');
			
			// 住所登録が必要なとき追加分
			if (self::SHOW_ADDRESS){			// 住所登録が必要なとき
				$this->checkSingleByte($zipcode, '郵便番号');
				$this->checkNumeric($this->state, '都道府県');
				$this->checkInput($address, '住所1');
				$this->checkSingleByte($phone, '電話番号');
				if (!empty($fax)) $this->checkSingleByte($fax, 'FAX');// 空のときはチェックしない
			}

			if ($this->getMsgCount() == 0){			// メールアドレスのチェック
				if ($email != $email2){
					$this->setAppErrorMsg('Eメールアドレスに誤りがあります');
				} else if ($this->_db->isExistsAccount($email)){// メールアドレスがログインIDとして既に登録されているかチェック
					$this->setAppErrorMsg('このEメールアドレスは既に登録されています');
				}
			}
			
			// フォトギャラリー追加分
			if (photo_shopCommonDef::MEMBER_INFO_OPTION){
				$this->checkInput($this->gender, '性別');
				if (empty($this->year) || empty($this->month) || empty($this->day)) $this->setUserErrorMsg('生年月日が入力されていません');
				$this->checkInput($sports, '現在やっているスポーツ');
			}
			
			// エラーなしの場合は、確認画面へ
			if ($this->getMsgCount() == 0){
				// 入力値を変更不可にする
				$this->tmpl->addVar("_widget", "firstname_disabled", 'readonly');
				$this->tmpl->addVar("_widget", "familyname_disabled", 'readonly');
				$this->tmpl->addVar("_widget", "firstname_kana_disabled", 'readonly');
				$this->tmpl->addVar("_widget", "familyname_kana_disabled", 'readonly');
				$this->tmpl->addVar("_widget", "email_disabled", 'readonly');
				$this->tmpl->addVar("_widget", "email2_disabled", 'readonly');
				if (self::SHOW_ADDRESS){			// 住所登録が必要なとき
					$this->tmpl->addVar("_widget", "zipcode_disabled", 'readonly');
					$this->tmpl->addVar("_widget", "address_disabled", 'readonly');
					$this->tmpl->addVar("_widget", "address2_disabled", 'readonly');
					$this->tmpl->addVar("_widget", "phone_disabled", 'readonly');
					$this->tmpl->addVar("_widget", "fax_disabled", 'readonly');
					$this->tmpl->addVar("_widget", "state_disabled", 'disabled');
					$this->tmpl->addVar("_widget", "state", $this->state);		// 都道府県の値は非表示パラメータに持つ
				}
				// フォトギャラリー追加分
				if (photo_shopCommonDef::MEMBER_INFO_OPTION){
					$this->tmpl->addVar("show_member_info_option", "gender_disabled", 'disabled');		// 性別
					$this->tmpl->addVar("show_member_info_option", "year_disabled", 'disabled');		// 生年月日(年)
					$this->tmpl->addVar("show_member_info_option", "month_disabled", 'disabled');		// 生年月日(月)
					$this->tmpl->addVar("show_member_info_option", "day_disabled", 'disabled');			// 生年月日(日)
					$this->tmpl->addVar("show_member_info_option", "sports_disabled", 'readonly');		// 現在やっているスポーツ
					$this->tmpl->addVar("show_member_info_option", "gender_hidden", '<input type="hidden" name="item_gender" value="' . $this->gender . '" />');		// 性別
					$this->tmpl->addVar("show_member_info_option", "year_hidden", '<input type="hidden" name="item_year" value="' . $this->year . '" />');		// 生年月日(年)
					$this->tmpl->addVar("show_member_info_option", "month_hidden", '<input type="hidden" name="item_month" value="' . $this->month . '" />');		// 生年月日(月)
					$this->tmpl->addVar("show_member_info_option", "day_hidden", '<input type="hidden" name="item_day" value="' . $this->day . '" />');		// 生年月日(日)
				}
				
				// 確認ボタンを表示
				$this->tmpl->addVar("_widget", "message", 'この内容で登録しますか？');		// 確認メッセージ
				$this->tmpl->setAttribute('show_confirm', 'visibility', 'visible');
			} else {
				// 入力完了ボタンを表示
				$this->tmpl->setAttribute('show_input', 'visibility', 'visible');
			}
		} else if ($act == 'regist'){			// 会員情報登録
			// 非表示パラメータから値を取得
			$this->state = $request->trimValueOf('state');	// 都道府県
			
			// パスワード生成
			$password = $this->makePassword();

			// トランザクションスタート
			$this->db->startTransaction();
			
			// アカウントの重複チェック
			$ret = !$this->_db->isExistsAccount($email);

			// ログインユーザを作成
			if ($ret){
				$userId = 0;
				$ret = $this->db->addUser(0/* 仮会員 */, $familyname . $firstname, $email, $password, $this->gEnv->getCurrentWidgetId(), $userId, $now, $loginUserId);		// 新規ログインユーザIDを取得
			}

			// 住所登録
			if ($ret) $ret = $this->db->updateAddress(0, $this->_langId, '', $zipcode, $this->state, $address, $address2, $phone, $fax, $countryId, $userId, $now, $addressId);

			// 個人情報登録
			$personOptions = array();
			if (photo_shopCommonDef::MEMBER_INFO_OPTION){
				$personOptions[self::PERSON_INFO_OPT_SPORTS] = $sports;
				$birthday = $this->convertToProperDate($this->year . '/' . $this->month . '/' . $this->day);
			} else {
				$birthday = $this->gEnv->getInitValueOfDate();
				$this->gender = 0;
			}
			if ($ret) $ret = $this->db->updatePersonInfo(0, $this->_langId, $firstname, $familyname, $firstnameKana, $familynameKana, $this->gender, $birthday, $email, '', $addressId, $userId, $now, $personalInfoId, $personOptions);

			// 仮会員情報を登録
			if ($ret) $ret = $this->db->addTmpMember($this->_langId, 1, 0, $personalInfoId, $loginUserId, $loginUserId, $now);
			
			// トランザクション終了
			$ret = $this->db->endTransaction();
			if ($ret){
				// 入力値を変更不可にする
				$this->tmpl->addVar("_widget", "firstname_disabled", 'readonly');
				$this->tmpl->addVar("_widget", "familyname_disabled", 'readonly');
				$this->tmpl->addVar("_widget", "firstname_kana_disabled", 'readonly');
				$this->tmpl->addVar("_widget", "familyname_kana_disabled", 'readonly');
				$this->tmpl->addVar("_widget", "email_disabled", 'readonly');
				$this->tmpl->addVar("_widget", "email2_disabled", 'readonly');
				if (self::SHOW_ADDRESS){			// 住所登録が必要なとき
					$this->tmpl->addVar("_widget", "zipcode_disabled", 'readonly');
					$this->tmpl->addVar("_widget", "address_disabled", 'readonly');
					$this->tmpl->addVar("_widget", "address2_disabled", 'readonly');
					$this->tmpl->addVar("_widget", "phone_disabled", 'readonly');
					$this->tmpl->addVar("_widget", "fax_disabled", 'readonly');
					$this->tmpl->addVar("_widget", "state_disabled", 'disabled');
				}
				// フォトギャラリー追加分
				if (photo_shopCommonDef::MEMBER_INFO_OPTION){
					$this->tmpl->addVar("show_member_info_option", "gender_disabled", 'disabled');		// 性別
					$this->tmpl->addVar("show_member_info_option", "year_disabled", 'disabled');		// 生年月日(年)
					$this->tmpl->addVar("show_member_info_option", "month_disabled", 'disabled');		// 生年月日(月)
					$this->tmpl->addVar("show_member_info_option", "day_disabled", 'disabled');			// 生年月日(日)
					$this->tmpl->addVar("show_member_info_option", "sports_disabled", 'readonly');		// 現在やっているスポーツ
				}
				
				// ####### 会員登録完了のメールを送信する #######
				if ($this->_getConfig(photo_shopCommonDef::CF_USE_EMAIL)){// メール送信許可のときはメールを送信
					$fromAddress = $this->_getConfig(photo_shopCommonDef::CF_AUTO_EMAIL_SENDER);	// 自動送信送信元
					if (empty($fromAddress)) $fromAddress = $this->gEnv->getSiteEmail();// 送信元が取得できないときは、システムのデフォルトメールアドレスを使用
					$toAddress = $email;			// eメール(ログインアカウント)
					//$url = $this->gEnv->createCurrentPageUrl() . sprintf(photo_shopCommonDef::EMAIL_LOGIN_URL, urlencode($email), urlencode($password));		// ログイン用URL
					$url = photo_shopCommonDef::createLoginUrl($toAddress, $password);		// ログイン用URL
					$mailParam = array();
					$mailParam['PASSWORD'] = $password;
					$mailParam['URL']		= $this->getUrl($url, true);		// ログイン用URL
					$mailParam['SIGNATURE']	= self::$_mainDb->getCommerceConfig(photo_shopCommonDef::CF_E_SHOP_SIGNATURE);	// ショップメール署名
					$ret = $this->gInstance->getMailManager()->sendFormMail(1, $this->gEnv->getCurrentWidgetId(), $toAddress, $fromAddress, '', '', self::REGIST_MEMBER_FORM, $mailParam);// 自動送信
					$this->tmpl->addVar("_widget", "message", '登録完了しました。指定のメールアドレス宛てにパスワードが送信されます。<br />再度ログインしてください。');
				} else {
					$this->tmpl->addVar("_widget", "message", '登録完了しました。再度ログインしてください。パスワード:' . $password);
				}
					
				// ログイン画面への遷移ボタンを表示
				$this->tmpl->setAttribute('show_complete', 'visibility', 'visible');
			} else {
				$this->tmpl->addVar("_widget", "message", '登録に失敗しました。再度実行してください。');		// 確認メッセージ
				
				// 入力完了ボタンを表示
				$this->tmpl->setAttribute('show_input', 'visibility', 'visible');
			}
		} else {		// 初期状態のとき
			// 入力完了ボタンを表示
			$this->tmpl->setAttribute('show_input', 'visibility', 'visible');
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
		if (self::SHOW_ADDRESS) $this->tmpl->setAttribute('input_address', 'visibility', 'visible');
		
		// 入力値を戻す
		$this->tmpl->addVar("_widget", "firstname", $firstname);
		$this->tmpl->addVar("_widget", "familyname", $familyname);
		$this->tmpl->addVar("_widget", "firstname_kana", $firstnameKana);
		$this->tmpl->addVar("_widget", "familyname_kana", $familynameKana);
		$this->tmpl->addVar("_widget", "email", $email);
		$this->tmpl->addVar("_widget", "email2", $email2);
		if (self::SHOW_ADDRESS){			// 住所登録が必要なとき
			$this->tmpl->addVar("_widget", "zipcode", $zipcode);
			$this->tmpl->addVar("_widget", "address", $address);
			$this->tmpl->addVar("_widget", "address2", $address2);
			$this->tmpl->addVar("_widget", "phone", $phone);
			$this->tmpl->addVar("_widget", "fax", $fax);
			
			// 都道府県を設定
			$this->db->getAllState('JPN', $this->_langId, array($this, 'stateLoop'));
		}
		// フォトギャラリー追加分
		if (photo_shopCommonDef::MEMBER_INFO_OPTION){
			$this->tmpl->addVar("show_member_info_option", "sports", $sports);		// 現在やっているスポーツ
		}
				
		// 遷移先を設定
		$this->tmpl->addVar("_widget", "login_url", $this->getUrl($this->gEnv->createCurrentPageUrl() . '&task=login', true));		// ログイン用URL
		$this->tmpl->addVar("_widget", "regist_url", $this->getUrl($this->gEnv->createCurrentPageUrl() . '&task=regmember', true));		// 会員登録用URL
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
