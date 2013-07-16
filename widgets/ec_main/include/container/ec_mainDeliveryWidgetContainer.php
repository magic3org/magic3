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
 * @version    SVN: $Id: ec_mainDeliveryWidgetContainer.php 5572 2013-01-23 08:43:39Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/ec_mainBaseWidgetContainer.php');

class ec_mainDeliveryWidgetContainer extends ec_mainBaseWidgetContainer
{
	private $db;	// DB接続オブジェクト
	private $zipcode;	// 郵便番号
	private $state;	// 都道府県
	private $addressId;	// コピー用に選択した住所ID
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
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
		return 'delivery.tmpl.html';
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
		// 初期データ取得
		$name = $request->trimValueOf('item_name');			// 名前
		$nameKana = $request->trimValueOf('item_name_kana');			// 名前カナ
		$this->zipcode = $request->trimValueOf('item_zipcode');	// 郵便番号
		$this->state = $request->trimValueOf('item_state');	// 都道府県
		$address = $request->trimValueOf('item_address');	// 住所
		$address2 = $request->trimValueOf('item_address2');	// 住所
		$phone = $request->trimValueOf('item_phone');	// 電話番号
//		$fax = $request->trimValueOf('item_fax');	// FAX
		$this->addressId = $request->trimValueOf('item_addressid');	// コピー用に選択した住所ID
//		$demandDt = $request->trimValueOf('item_demand_dt');		// 配達希望日
		
		// ##### 非会員の購入の場合はEメールを入力 #####
		if (empty($this->_userId)){			// 非会員の購入の場合
			$email = $request->trimValueOf('item_email');	// Email
			$email2 = $request->trimValueOf('item_email2');	// Email確認用
		}

		$act = $request->trimValueOf('act');
		if ($act == 'regist'){			// 配送先仮登録
			$this->checkInput($name, '名前');		
			$this->checkInput($nameKana, '名前カナ');
			$this->checkSingleByte($this->zipcode, '郵便番号');
			$this->checkNumeric($this->state, '都道府県');
			$this->checkInput($address, '住所');	
			$this->checkSingleByte($phone, '電話番号');
			
			// ##### 非会員の購入の場合はEメールを入力 #####
			if (empty($this->_userId)){
				$this->checkMailAddress($email, 'Eメール');
				$this->checkMailAddress($email2, 'Eメール(確認)');
				
				if ($this->getMsgCount() == 0){			// メールアドレスのチェック
					if ($email != $email2) $this->setAppErrorMsg('Eメールアドレスに誤りがあります');
				}
			}
			
			// エラーなしの場合
			if ($this->getMsgCount() == 0){
				// 注文書を取得
				$ret = $this->_getOrderSheet($row);
				if ($ret){
					// 非会員の購入の場合は顧客名を設定
					if (empty($this->_userId)){			// 非会員の購入の場合
						$custm_name = $name;
						$custm_name_kana = $nameKana;
						$custm_email = $email;
						
						// 請求先を初期化
						$bill_name = $name;
						$bill_name_kana = $nameKana;
						$bill_person = '';
						$bill_person_kana = '';
						$bill_zipcode = $this->zipcode;
						$bill_state_id = $this->state;
						$bill_address = $address;
						$bill_address2 = $address2;
						$bill_phone = $phone;
						$bill_fax = '';
						$bill_email = $email;
						$bill_country_id = '';
					} else {
						$custm_name = $row['oe_custm_name'];
						$custm_name_kana = $row['oe_custm_name_kana'];
						$custm_email = $row['oe_custm_email'];
						
						// 請求先を維持
						$bill_name = $row['oe_bill_name'];
						$bill_name_kana = $row['oe_bill_name_kana'];
						$bill_person = $row['oe_bill_person'];
						$bill_person_kana = $row['oe_bill_person_kana'];
						$bill_zipcode = $row['oe_bill_zipcode'];
						$bill_state_id = $row['oe_bill_state_id'];
						$bill_address = $row['oe_bill_address1'];
						$bill_address2 = $row['oe_bill_address2'];
						$bill_phone = $row['oe_bill_phone'];
						$bill_fax = $row['oe_bill_fax'];
						$bill_email = $row['oe_bill_email'];
						$bill_country_id = $row['oe_bill_country_id'];
					}
					
					// 配送先が更新されている場合は以降の画面で設定するデータの更新の必要があるので一旦データをすべて初期化
					// 配送先は入力値
					$deliv_id = 0;
					$deliv_name = $name;
					$deliv_name_kana = $nameKana;
					$deliv_person = '';
					$deliv_person_kana = '';
					$deliv_zipcode = $this->zipcode;
					$deliv_state_id = $this->state;
					$deliv_address = $address;
					$deliv_address2 = $address2;
					$deliv_phone = $phone;
					$deliv_fax = '';
					$deliv_email = '';
					$deliv_country_id = '';
				
/*					// 配送方法、支払方法を初期化
					$deliv_method_id = '';			// 配送方法
					$pay_method_id = '';			// 支払方法
					$card_type = '';
					$card_owner = '';
					$card_number = '';
					$card_expires = '';
					$demand_dt = $this->gEnv->getInitValueOfTimestamp();		// 希望日
					$demand_time = '';	// 希望時間帯
					$appoint_dt = $this->gEnv->getInitValueOfTimestamp();		// 予定納期
					
					// 金額を初期化
					$currency_id = '';
					$subtotal = 0;		// 商品合計
					$deliv_fee = 0;		// 配送料
					$charge = 0;		// 手数料
					$discount = 0;		// 値引き額
					$total = $subtotal - $discount + $deliv_fee + $charge;		// 総支払額を求める
					*/

					$ret = self::$_orderDb->updateOrderSheet($this->_userId, $this->_langId, $this->_getClientId(),
						$row['oe_custm_id'], $custm_name, $custm_name_kana, $row['oe_custm_person'], $row['oe_custm_person_kana'],
						$row['oe_custm_zipcode'], $row['oe_custm_state_id'], $row['oe_custm_address1'], $row['oe_custm_address2'], $row['oe_custm_phone'], $row['oe_custm_fax'], $custm_email, $row['oe_custm_country_id'], 
						$deliv_id, $deliv_name, $deliv_name_kana, $deliv_person, $deliv_person_kana, $deliv_zipcode, $deliv_state_id, $deliv_address, $deliv_address2, $deliv_phone, $deliv_fax, $deliv_email, $deliv_country_id,
						$row['oe_bill_id'], $bill_name, $bill_name_kana, $bill_person, $bill_person_kana, 
						$bill_zipcode, $bill_state_id, $bill_address, $bill_address2, $bill_phone, $bill_fax, $bill_email, $bill_country_id,
						$row['oe_deliv_method_id'], $row['oe_pay_method_id'], $row['oe_card_type'], $row['oe_card_owner'], $row['oe_card_number'], $row['oe_card_expires'],
						$row['oe_demand_dt'], $row['oe_demand_time'], $row['oe_appoint_dt'], $row['oe_currency_id'], $row['oe_subtotal'], $row['oe_discount'], $row['oe_deliv_fee'], $row['oe_charge'], $row['oe_total']);
					// この画面の登録完了をもって、購入用データは作成済みとする
					// エラーがなければ次の画面へ
					if ($ret){
						$deliveryPage = $this->gEnv->createCurrentPageUrl() . '&task=' . $this->_getOrderNextTask();
						$this->gPage->redirect($deliveryPage);
						return;
					}
					$this->setAppErrorMsg('データ更新に失敗しました');
				}
			}
		} else if ($act == 'copydata'){			// データコピーのとき
			if (!empty($this->_userId)){			// 会員の購入の場合
				// 会員情報を取得
				$ret = self::$_orderDb->getMember($this->_userId, $memberRow);
				if ($ret){
					if ($memberRow['sm_type'] == 1){		// 個人メンバーのとき
						// 個人情報取得
						$ret = self::$_orderDb->getPersonInfo($memberRow['sm_person_info_id'], $personRow);
						if ($ret){
							$ret = self::$_orderDb->getAddress($personRow['pi_address_id'], $addressRow);
							if ($ret){
								$name = $personRow['pi_family_name'] . $personRow['pi_first_name'];
								$nameKana = $personRow['pi_family_name_kana'] . $personRow['pi_first_name_kana'];
								$this->zipcode = $addressRow['ad_zipcode'];
								$address = $addressRow['ad_address1'];
								$address2 = $addressRow['ad_address2'];
								$phone = $addressRow['ad_phone'];
								$this->state = $addressRow['ad_state_id'];
							}
						}
					}
				}
			}
		} else {		// 初期表示
			// 注文書を取得
			$ret = $this->_getOrderSheet($row);	// 注文情報を取得
			if ($ret){			// データが既に登録済みのとき
				$name = $row['oe_deliv_name'];
				$nameKana = $row['oe_deliv_name_kana'];
				$this->zipcode = $row['oe_deliv_zipcode'];
				$address = $row['oe_deliv_address1'];
				$address2 = $row['oe_deliv_address2'];
				$phone = $row['oe_deliv_phone'];
				$this->state = $row['oe_deliv_state_id'];
//				$demandDt = $row['oe_demand_time'];			// 配達希望日

				// ##### 非会員の購入の場合はEメールを入力 #####
				if (empty($this->_userId)){
					$email = $row['oe_custm_email'];	// Email
					$email2 = $row['oe_custm_email'];	// Email確認用
				}
			}
		}
		// コピー用メニュー作成
		$isCopyMenuActive = $this->createCopyDataMenu($this->_userId);
		
		// 都道府県を設定
		self::$_orderDb->getAllState('JPN', $this->_langId, array($this, 'stateLoop'));
		
		// 入力値を戻す
		$this->tmpl->addVar("_widget", "name", $name);
		$this->tmpl->addVar("_widget", "name_kana", $nameKana);
		$this->tmpl->addVar("_widget", "zipcode", $this->zipcode);
		$this->tmpl->addVar("_widget", "address", $address);
		$this->tmpl->addVar("_widget", "address2", $address2);
		$this->tmpl->addVar("_widget", "phone", $phone);
//		$this->tmpl->addVar("_widget", "demand_dt", $demandDt);
		if (!$isCopyMenuActive){
			//$this->tmpl->addVar("_widget", "copymenu_disabled", "disabled");
			$this->tmpl->setAttribute('copy_data', 'visibility', 'hidden');		// 住所コピーメニュー隠す
		}
		
		// ##### 非会員の購入の場合はEメールを入力 #####
		if (empty($this->_userId)){
			$this->tmpl->setAttribute('input_email', 'visibility', 'visible');
			$this->tmpl->addVar("input_email", "email", $email);
			$this->tmpl->addVar("input_email", "email2", $email2);
		}
		
		$this->tmpl->addVar("_widget", "goback_url", $this->getUrl($this->gEnv->createCurrentPageUrl() . '&task=' . $this->_getOrderNextTask(-1) . '&act=goback', true));		// 1つ前の画面
	}
	/**
	 * データコピー用のメニューを作成
	 *
	 * @param int $userId		ユーザID
	 * @return bool				true=メニュー項目あり、false=メニュー項目なし
	 */
	function createCopyDataMenu($userId)
	{
		$menu = array(array(	'name' => '住所をコピー',		'value' => ''));
		if (!empty($userId)){		// ログインユーザの場合
			$addressArray = $this->getAddressByUserId($userId);
			for ($i = 0; $i < count($addressArray); $i++){
				$menu[] = array('name' => $addressArray[$i]['ad_title'],	'value' => $addressArray[$i]['ad_id']);
			}
		}
		
		for ($i = 0; $i < count($menu); $i++){
			$value = $menu[$i]['value'];
			$name = $menu[$i]['name'];
			
			$selected = '';
			if ($value == $this->addressId) $selected = 'selected';
			
			$row = array(
				'value'    => $value,			// 値
				'name'     => $name,			// 名前
				'selected' => $selected														// 選択中かどうか
			);
			$this->tmpl->addVars('copy_data_list', $row);
			$this->tmpl->parseTemplate('copy_data_list', 'a');
		}
		if (count($menu) > 1){
			return true;
		} else {
			return false;
		}
	}
	/**
	 * ユーザIDから住所を取得
	 *
	 * @param int $userId		ユーザID
	 * @return array			住所の配列
	 */
	function getAddressByUserId($userId)
	{
		$address = array();
		
		// 会員情報を取得
		$ret = self::$_orderDb->getMember($userId, $memberRow);
		if ($ret){
			if ($memberRow['sm_type'] == 1){		// 個人メンバーのとき
				// 個人情報取得
				$ret = self::$_orderDb->getPersonInfo($memberRow['sm_person_info_id'], $personRow);
				if ($ret){
					$ret = self::$_orderDb->getAddress($personRow['pi_address_id'], $addressRow);
					if ($ret){
						if (empty($addressRow['ad_title'])) $addressRow['ad_title'] = '会員の住所';
						$address[] = $addressRow;
					}
				}
			}
		}
		return $address;
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
}
?>
