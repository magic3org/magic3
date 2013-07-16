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
 * @version    SVN: $Id: ec_mainPaymentWidgetContainer.php 5440 2012-12-08 09:37:39Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/ec_mainBaseWidgetContainer.php');

class ec_mainPaymentWidgetContainer extends ec_mainBaseWidgetContainer
{
	private $payMethod;			// 支払い方法
	private $payMethodCount;	// 支払い方法総数
	
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
		return 'payment.tmpl.html';
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
		$this->payMethod = $request->trimValueOf('item_payment_method');	// 支払い方法
		
		$act = $request->trimValueOf('act');
		if ($act == 'regist'){			// 確認画面へ
			$this->checkInput($this->payMethod, 'お支払い方法', 'お支払い方法が選択されていません');
			
			// エラーなしの場合
			if ($this->getMsgCount() == 0){
				// 注文書を取得
				$ret = $this->_getOrderSheet($row);
				if ($ret){
					// 手数料を求める
					$subtotal = $row['oe_subtotal'];		// 商品合計
					$charge = 0;		// 手数料
					if (self::$_orderDb->getPaymentMethod($this->payMethod, $this->_langId, 0/*デフォルトのセットID*/, $payMethodRow)){
						$iWidgetId	= $payMethodRow['po_iwidget_id'];	// インナーウィジェットID
						if (!empty($iWidgetId)){
							// パラメータをインナーウィジェットに設定し、計算結果を取得
							$optionParam = new stdClass;
							$optionParam->id = $payMethodRow['po_id'];
							$optionParam->init = true;		// 初期データ取得
							$optionParam->userId = $this->_userId;					// ログインユーザID
							$optionParam->languageId = $this->_langId;		// 言語ID
							$optionParam->cartId = $this->cartId;					// 商品のカート
							$optionParam->productTotal = $subtotal;				// 商品総額
							$optionParam->productCount = $this->productCount;	// 商品総数
							$optionParam->zipcode = $this->zipcode;		// 配送先の郵便番号
							$optionParam->stateId = $this->stateId;		// 配送先の都道府県
							if ($this->calcIWidgetParam($iWidgetId, $payMethodRow['po_id'], $payMethodRow['po_param'], $optionParam, $resultObj)){
								if (isset($resultObj->price)) $charge = $resultObj->price;		// 手数料
							}
						}
					}

					// 値引額、割り増し額を求める
					$discount = 0;
					$discountDesc = '';			// 値引き説明
					$ret = $this->getExtraPrice($extraPrice, $extraTitle);
					if ($ret){
						for ($i = 0; $i < count($extraPrice); $i++){
							$discountPrice = $extraPrice[$i];
							if ($discountPrice < 0){
								$discount -= $discountPrice;
								$discountDesc .= $extraTitle[$i] . ',';
							}
						}
						$discountDesc = trim($discountDesc, ',');
					}
					
					// 総額再集計
					//$subtotal = $row['oe_subtotal'];		// 商品合計
					$deliv_fee = $row['oe_deliv_fee'];		// 配送料
					$total = $subtotal - $discount + $deliv_fee + $charge;		// 総支払額を求める
										
					$ret = self::$_orderDb->updateOrderSheet($this->_userId, $this->_langId, $this->_getClientId(),
						$row['oe_custm_id'], $row['oe_custm_name'], $row['oe_custm_name_kana'], $row['oe_custm_person'], $row['oe_custm_person_kana'],
						$row['oe_custm_zipcode'], $row['oe_custm_state_id'], $row['oe_custm_address1'], $row['oe_custm_address2'], $row['oe_custm_phone'], $row['oe_custm_fax'], $row['oe_custm_email'], $row['oe_custm_country_id'], 
						$row['oe_deliv_id'], $row['oe_deliv_name'], $row['oe_deliv_name_kana'], $row['oe_deliv_person'], $row['oe_deliv_person_kana'],
						$row['oe_deliv_zipcode'], $row['oe_deliv_state_id'], $row['oe_deliv_address1'], $row['oe_deliv_address2'], $row['oe_deliv_phone'], $row['oe_deliv_fax'], $row['oe_deliv_email'], $row['oe_deliv_country_id'],
						$row['oe_bill_id'], $row['oe_bill_name'], $row['oe_bill_name_kana'], $row['oe_bill_person'], $row['oe_bill_person_kana'], 
						$row['oe_bill_zipcode'], $row['oe_bill_state_id'], $row['oe_bill_address1'], $row['oe_bill_address2'], $row['oe_bill_phone'], $row['oe_bill_fax'], $row['oe_bill_email'], $row['oe_bill_country_id'],
						$row['oe_deliv_method_id'], $this->payMethod, $row['oe_card_type'], $row['oe_card_owner'], $row['oe_card_number'], $row['oe_card_expires'],
						$row['oe_demand_dt'], $row['oe_demand_time'], $row['oe_appoint_dt'], $row['oe_currency_id'], $row['oe_subtotal'], $discount/*値引額*/, $row['oe_deliv_fee'], $charge, $total/*総額*/, $discountDesc/*値引き説明*/);
					if ($ret){
						// 確認画面へ遷移
						$deliveryPage = $this->gEnv->createCurrentPageUrl() . '&task=' . $this->_getOrderNextTask();
						$this->gPage->redirect($deliveryPage);
						return;
					}
				}
				$this->setAppErrorMsg('データ更新に失敗しました');
			}
		} else {		// 初期表示
			// 注文書を取得
			$ret = $this->_getOrderSheet($row);	// 注文情報を取得
			if ($ret){
				$this->payMethod = $row['oe_pay_method_id'];		// 支払い方法
			}
		}
		// 支払い方法メニューを作成
		$this->payMethodCount = self::$_orderDb->getAllPaymentMethodCount($this->_langId);		// 支払い方法数
		self::$_orderDb->getAllPaymentMethod($this->_langId, array($this, 'paymentMethodLoop'));
		
		// 選択メッセージを表示
		if ($this->payMethodCount > 1) $this->tmpl->setAttribute('select_message', 'visibility', 'visible');
		
		// 遷移先を設定
		$this->tmpl->addVar("_widget", "goback_url", $this->getUrl($this->gEnv->createCurrentPageUrl() . '&task=' . $this->_getOrderNextTask(-1) . '&act=goback', true));		// 1つ前の画面
	}
	/**
	 * 取得した支払い方法をテンプレートに設定する
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function paymentMethodLoop($index, $fetchedRow, $param)
	{
		$checked = '';
		if ($fetchedRow['po_id'] == $this->payMethod) $checked = 'checked';		// 選択中の支払い方法
		
		// 入力コントロールタイプ
		$inputType = 'radio';
		if ($this->payMethodCount <= 1) $inputType = 'hidden';			// 支払い方法が選択できないとき
		
		$row = array(
			'type'		=> $inputType,			// 入力コントロールタイプ
			'value'		=> $this->convertToDispString($fetchedRow['po_id']),			// ID
			'name'		=> $this->convertToDispString($fetchedRow['po_name']),		// 表示名
			'desc'		=> $fetchedRow['po_description'],							// 説明
			'checked'	=> $checked														// 選択中かどうか
		);
		$this->tmpl->addVars('payment_method_list', $row);
		$this->tmpl->parseTemplate('payment_method_list', 'a');
		return true;
	}
}
?>
