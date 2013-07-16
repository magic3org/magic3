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
 * @version    SVN: $Id: ec_mainConfirmWidgetContainer.php 5572 2013-01-23 08:43:39Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/ec_mainBaseWidgetContainer.php');

class ec_mainConfirmWidgetContainer extends ec_mainBaseWidgetContainer
{
	private $cartId;			// カートID
	private $deliveryMethod;		// 配送方法
	private $payMethod;			// 支払い方法
	private $orderId;			// 受注ID
	private $now;				// 現在日時
	private $currency;			// 通貨
	private $redirectUrl;		// リダイレクト先URL
	private $paymentNote;				// 支払補足情報
	const ORDER_ADMIN_WIDGET = 'photo_shop';		// 受注管理ウィジェット
	
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
		return 'confirm.tmpl.html';
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
		$this->now = date("Y/m/d H:i:s");	// 現在日時
		$this->currency = photo_shopCommonDef::DEFAULT_CURRENCY;		// デフォルト通貨
		
		// クッキー読み込み、カートIDを取得
		$this->cartId = $request->getCookieValue(M3_COOKIE_CART_ID);
		if (empty($this->cartId)){			// カートIDがない場合は終了
			$errorPage = $this->gEnv->createCurrentPageUrl() . '&task=' . self::ERROR_TASK;
			$this->gPage->redirect($errorPage);
		}
		
		// 通貨情報を取得
		$ret = self::$_ecObj->db->getCurrency($this->currency, $this->_langId, $row);
		if ($ret){
			$prePrice = $this->convertToDispString($row['cu_symbol']);
			$postPrice = $this->convertToDispString($row['cu_post_symbol']);
		}
		
		// 画像情報を取得
		$this->_productImageWidth = 0;		// 商品画像幅
		$this->_productImageHeight = 0;		// 商品画像高さ
		$ret = self::$_mainDb->getProductImageInfo(photo_shopCommonDef::PRODUCT_IMAGE_SMALL, $row);
		if ($ret){
			$this->_productImageWidth = $row['is_width'];
			$this->_productImageHeight = $row['is_height'];
		}
			
		// 支払いのオンライン接続があるかどうかを取得
		$isOnlinePayment = $this->getIsOnlinePayment($payMethodRow);

		$reloadData = false;		// データ再取得
		$orderCompleted = false;	// 注文処理完了かどうか
		$act = $request->trimValueOf('act');
		if ($act == 'order'){			// 購入完了
			// 注文書を取得
			$ret = $this->_getOrderSheet($row);
			if ($ret){
				// ##### 注文書データエラーチェック #####
				$this->checkInput($row['oe_pay_method_id'], 'お支払い方法', 'お支払い方法が選択されていません');		// 支払方法は必須入力

				// カートの商品の在庫数をチェック
				$this->_checkCart = true;			// カート内容のエラーチェックかどうか
				$this->_getCartItems('_defaultCartLoop');
				$this->_checkCart = false;			// カート内容のエラーチェックかどうか
				
				// エラーなしの場合は受注登録処理
				if ($this->getMsgCount() == 0){
					// 受注No作成
					$orderNo = $this->createOrderNo();
					if (empty($orderNo)) $ret = false;
					
					// ##### オンライン決済サービス接続の場合は、注文登録処理を行う #####
					if ($ret && $isOnlinePayment){
						$ret = $this->connectService($payMethodRow, $row, $orderNo);
						if ($ret){
							// カートの状態をオンライン決済サービス中に設定する
							self::$_ecObj->db->updateOrderSheetStatus($this->_userId, 1/*オンライン処理中*/);
						}
					}
					
					// オンライン決済が正常に終了した場合、または、オンライン決済が必要ない場合は、受注登録を行う。
					if ($ret){
						// トランザクションスタート
						self::$_orderDb->startTransaction();

						// 受注ヘッダ作成
						$orderStatus = photo_shopCommonDef::ORDER_STATUS_REGIST;
						$estimateDt = $this->gEnv->getInitValueOfTimestamp();
						$registDt = $this->now;
						$orderDt = $this->gEnv->getInitValueOfTimestamp();
						$delivDt = $this->gEnv->getInitValueOfTimestamp();
						$closeDt = $this->gEnv->getInitValueOfTimestamp();
						$ret = self::$_orderDb->updateOrder(0/*新規追加*/, $this->_userId, $this->_langId, $orderNo,
							$row['oe_custm_id'], $row['oe_custm_name'], $row['oe_custm_name_kana'], $row['oe_custm_person'], $row['oe_custm_person_kana'],
							$row['oe_custm_zipcode'], $row['oe_custm_state_id'], $row['oe_custm_address1'], $row['oe_custm_address2'], $row['oe_custm_phone'], $row['oe_custm_fax'], $row['oe_custm_email'], $row['oe_custm_country_id'], 
							$row['oe_deliv_id'], $row['oe_deliv_name'], $row['oe_deliv_name_kana'], $row['oe_deliv_person'], $row['oe_deliv_person_kana'],
							$row['oe_deliv_zipcode'], $row['oe_deliv_state_id'], $row['oe_deliv_address1'], $row['oe_deliv_address2'], $row['oe_deliv_phone'], $row['oe_deliv_fax'], $row['oe_deliv_email'], $row['oe_deliv_country_id'],
							$row['oe_bill_id'], $row['oe_bill_name'], $row['oe_bill_name_kana'], $row['oe_bill_person'], $row['oe_bill_person_kana'], 
							$row['oe_bill_zipcode'], $row['oe_bill_state_id'], $row['oe_bill_address1'], $row['oe_bill_address2'], $row['oe_bill_phone'], $row['oe_bill_fax'], $row['oe_bill_email'], $row['oe_bill_country_id'],
							$row['oe_deliv_method_id'], $row['oe_pay_method_id'], $row['oe_card_type'], $row['oe_card_owner'], $row['oe_card_number'], $row['oe_card_expires'],
							$row['oe_demand_dt'], $row['oe_demand_time'], $row['oe_appoint_dt'], $row['oe_currency_id'], $row['oe_subtotal'], $row['oe_discount'], $row['oe_deliv_fee'], $row['oe_charge'], $row['oe_total'],
							$orderStatus, $estimateDt, $registDt, $orderDt, $delivDt, $closeDt,
							$this->_userId, $this->now, $this->orderId, $newSerial, $row['oe_discount_desc']);
				
						// 受注詳細作成
						// カートの商品を受注明細にコピー
						$this->_addToOrder = true;			// 受注明細を登録
						$this->_orderId = $this->orderId;
						$this->_getCartItems('_calcCartLoop');

						// トランザクション終了
						$ret = self::$_orderDb->endTransaction();
					}
				} else {
					$ret = false;
				}
			}

			// 注文データを新規登録した場合は、受注ヘッダから画面表示用のデータを取得
			if ($ret){
				// 配送方法名、決済方法名
				$delivMethodName = '';
				$delivMethodDesc = '';
				$payMethodName = '';
				$payMethodDesc = '';
				
				// 格納データ取得
				$ret = self::$_orderDb->getOrder($this->orderId, $row);
				if ($ret){
					$orderNo = $row['or_order_no'];		// 注文番号
					$name = $row['or_deliv_name'];
					$nameKana = $row['or_deliv_name_kana'];
					$zipcode = $row['or_deliv_zipcode'];
					$address = $row['or_deliv_address1'];
					$address2 = $row['or_deliv_address2'];
					$phone = $row['or_deliv_phone'];
					$this->state = $row['or_deliv_state_id'];
					$this->deliveryMethod = $row['or_deliv_method_id'];		// 配送方法
					$this->payMethod = $row['or_pay_method_id'];		// 支払い方法
					$email		= $row['or_custm_email'];		// 購入者のEメールアドレス
					
					// 送料、手数料(メール送信用)
					$discountValue = self::$_ecObj->getCurrencyPrice($row['or_discount']);
					$deliveryFeeValue = self::$_ecObj->getCurrencyPrice($row['or_deliv_fee']);
					$chargeValue = self::$_ecObj->getCurrencyPrice($row['or_charge']);

					// 価格
					$discount = $row['or_discount'];		// 値引額
					$deliveryFee = $row['or_deliv_fee'];
					$charge = $row['or_charge'];		// 手数料
					$total = $row['or_total'];
					
					$discountDesc = $row['or_discount_desc'];		// 値引き説明
					$demand_dt = $row['or_demand_dt'];	// 希望日
					$demand_time = $row['or_demand_time'];	// 希望時間帯
					if ($demand_dt != $this->gEnv->getInitValueOfDate() || !empty($demand_time)){
						$note = '配達希望日：' . $this->convertToDispDate($demand_dt) . '&nbsp;' . $this->convertToDispString($demand_time);
					}
					
					// 配送方法名
					if (self::$_orderDb->getDelivMethod($this->deliveryMethod, $this->_langId, 0/*デフォルトのセットID*/, $row)){
						$delivMethodName = $row['do_name'];
						$delivMethodDesc = $row['do_description'];
					}
					// 支払い方法名
					if (self::$_orderDb->getPaymentMethod($this->payMethod, $this->_langId, 0/*デフォルトのセットID*/, $row)){
						$payMethodName = $row['po_name'];
						$payMethodDesc = $row['po_description'];
					}
				}
				//$this->tmpl->addVar("_widget", "message_bottom", '以上の内容でご注文承りました。');		// メッセージ
				$msg = '以下の内容でご注文承りました。';
					
				// 管理者向けメール送信用の会員情報を取得
				$memberNo	= '---';						// 会員No
				$memberName = '[テスト用]';					// 会員名
				if (empty($this->_userId)){			// 非会員のとき
					$memberNo	= '0';							// 会員No
					//$memberName = '[非会員]';					// 会員名
					$memberName = $name;					// 会員名
				} else {
					$ret = self::$_orderDb->getMember($this->_userId, $memberRow);
					if ($ret){
						if ($memberRow['sm_type'] == 1){		// 個人メンバーのとき
							// 個人情報取得
							$ret = self::$_orderDb->getPersonInfo($memberRow['sm_person_info_id'], $personRow);
							if ($ret){
								$ret = self::$_orderDb->getAddress($personRow['pi_address_id'], $addressRow);
								if ($ret){
									$memberNo	= $memberRow['sm_member_no'];		// 会員No
									$memberName = $personRow['pi_family_name'] . $personRow['pi_first_name'];		// 会員名
									$email		= $personRow['pi_email'];
								}
							}
						}
					}
				}
				// カートの商品を取得し、表示が終わった後、カートの内容をすべて削除
				$this->_emailData = '';			// メール送信用の受注明細データ
				$this->_orderText = '';			// 受注内容

				$this->_createEmailData = true;	// メール送信用の受注データを作成する
				$this->_getCartItems('_defaultCartLoop');
				$this->_createEmailData = false;	// メール送信用の受注データを作成終了
				
				// 受注データに値引料、送料、手数料を追加
				$delim = $this->gEnv->getDefaultCsvDelimCode();		// CSV区切りコードを取得
				$this->_emailData .= 'discount' . $delim . $discountValue . M3_NL;		// 値引額
				$this->_emailData .= 'delivery' . $delim . $deliveryFeeValue . M3_NL;
				$this->_emailData .= 'charge' . $delim . $chargeValue . M3_NL;
				$this->_orderText .= '-----' . M3_NL;
				if ($discount > 0) $this->_orderText .= '値引額 ' . $prePrice . self::$_ecObj->convertByCurrencyFormat($this->currency, $this->_langId, $discount) . $postPrice . M3_NL;// 値引額
				if (!empty($this->deliveryMethod)){			// 配送方法が選択されている場合
					if ($deliveryFee > 0){
						$this->_orderText .= '配送料 ' . $prePrice . self::$_ecObj->convertByCurrencyFormat($this->currency, $this->_langId, $deliveryFee) . $postPrice . M3_NL;	// 配送料
					} else {
						$this->_orderText .= '配送料 無料' . M3_NL;	// 配送料
					}
				}
				if ($charge > 0) $this->_orderText .= '手数料 ' . $prePrice . self::$_ecObj->convertByCurrencyFormat($this->currency, $this->_langId, $charge) . $postPrice . M3_NL;		// 手数料
				$this->_orderText .= '合計 ' . $prePrice . self::$_ecObj->convertByCurrencyFormat($this->currency, $this->_langId, $total) . $postPrice . M3_NL;		// 合計
				
				// ######## 受注情報をメール送信 ########
				$stateName = self::$_orderDb->getStateName(photo_shopCommonDef::DEFAULT_COUNTRY_ID, $this->_langId, $this->state);			// 都道府県
				$adminUrl = $this->gEnv->getDefaultAdminUrl() . '?task=configwidget_' . self::ORDER_ADMIN_WIDGET;		// 管理画面URL
				$adminUrl .= '&' . M3_REQUEST_PARAM_OPERATION_TODO . '=' . urlencode(M3_REQUEST_PARAM_OPERATION_TASK . '=order_detail&orderno=' . $orderNo);
				$this->sendOrderMail(0/*新規登録*/, $orderNo, $memberNo, $email, $memberName/*会員名*/,
						$name, $nameKana, $zipcode, $stateName, $address/*配送先住所1*/, $address2/*配送先住所2*/, $phone, $this->convertToDateString($demand_dt), $demand_time, 
						$delivMethodName, $payMethodName, '', $this->_emailData/*受注明細データ*/, $this->_orderText/*受注内容*/, $adminUrl);
						
				// 注文書の状態を取得
				$orderSheetStatus = self::$_ecObj->db->getOrderSheetStatus($this->_userId);
				if (empty($orderSheetStatus)){		// オンライン接続がない場合は、カートの内容、注文書を削除
					$orderCompleted = true;	// 注文処理完了
				}
				
				// オンライン決済サービスのリダイレクト先が指定されている場合はリダイレクト
				if (!empty($this->redirectUrl)) $this->gPage->redirect($this->redirectUrl);
			} else {
				$this->setAppErrorMsg('登録処理に失敗しました。');
				
				// カートの商品を取得
				$this->_getCartItems('_defaultCartLoop');
			}
		} else if ($act == 'complete'){		// 決済完了
			// 注文情報を取得し、セッションが同じ場合のみ注文状態を更新する
			$ret = $this->_getOrderSheet($row);
			if ($ret && $row['oe_session'] == session_id()){					
				// 注文書の状態を取得
				$orderSheetStatus = self::$_ecObj->db->getOrderSheetStatus($this->_userId);
				if ($orderSheetStatus == 1){		// オンライン接続中の場合のみ実行
					// 決済結果を取得
					$ret = $this->completeService($payMethodRow);
			
					// 決済完了処理
					if ($ret){
						// 注文状態を支払い完了にする
						$this->updateOrderStatus($this->orderId, photo_shopCommonDef::ORDER_STATUS_PAYMENT_COMPLETED);
				
						// 購入完了処理
						//photo_shopCommonDef::purchaseProduct(self::$_orderDb, $this->orderId);
						$this->setDownloadContentAccess($this->_userId/*画像購入ユーザ*/, $this->orderId);
					}
				}
			}
			
			$orderCompleted = true;	// 注文処理完了
			$reloadData = true;		// データ再取得
		} else if ($act == 'cancel'){		// 注文キャンセル
			// 注文IDを取得
			$ret = $this->cancelService($payMethodRow);

			// 注文をキャンセル
			if ($ret) $this->updateOrderStatus($this->orderId, photo_shopCommonDef::ORDER_STATUS_CANCEL);
			
			// カートの状態のオンライン決済中を終了
			self::$_ecObj->db->updateOrderSheetStatus($this->_userId, 0);
			
			$reloadData = true;		// データ再取得
		} else {		// 初期処理
			$reloadData = true;		// データ再取得
		}
		
		if ($reloadData){		// データ再取得のとき
			// 注文書を取得
			$ret = $this->_getOrderSheet($row);
			if ($ret){
				$name = $row['oe_deliv_name'];
				$nameKana = $row['oe_deliv_name_kana'];
				$zipcode = $row['oe_deliv_zipcode'];
				$address = $row['oe_deliv_address1'];
				$address2 = $row['oe_deliv_address2'];
				$phone = $row['oe_deliv_phone'];
				$this->state = $row['oe_deliv_state_id'];
				$this->deliveryMethod = $row['oe_deliv_method_id'];	// 配送方法
				$this->payMethod = $row['oe_pay_method_id'];		// 支払い方法
				$email = $row['oe_custm_email'];	// 購入者のEメールアドレス
				
				/*$demand_dt = $this->convertToDispDate($row['oe_demand_dt']);	// 希望日
				$demand_time = $row['oe_demand_time'];	// 希望時間帯
				if ($row['oe_demand_dt'] != $this->gEnv->getInitValueOfTimestamp()) $note = '配達希望日：' . $demand_dt . ' ' . $demand_time;*/
				$demand_dt = $row['oe_demand_dt'];	// 希望日
				$demand_time = $row['oe_demand_time'];	// 希望時間帯
				if ($demand_dt != $this->gEnv->getInitValueOfDate() || !empty($demand_time)){
					$note = '配達希望日：' . $this->convertToDispDate($demand_dt) . '&nbsp;' . $this->convertToDispString($demand_time);
				}
				$discountDesc = $row['oe_discount_desc'];		// 値引き説明
				
				// 配送料、合計の作成
				$discount = $row['oe_discount'];		// 値引額
				$deliveryFee = $row['oe_deliv_fee'];
				$charge = $row['oe_charge'];
				$total = $row['oe_total'];
			
				// カートの商品を取得
				$this->_getCartItems('_defaultCartLoop');
				
				if ($orderCompleted){		// 決済完了のとき
					$msg = 'ご購入ありがとうございました。';
					//$this->tmpl->addVar("_widget", "message_top", $msg);		// メッセージ
				} else {
					// 在庫数の確認
					if ($this->getMsgCount() == 0){		// 注文分の在庫がある場合
						$msg = 'ご注文を確定してください。';
						if ($isOnlinePayment) $msg .= '注文確定後、お支払い画面へ遷移します。';
						//$this->tmpl->addVar("_widget", "message_top", $msg);		// メッセージ
					
						// カートに商品がある場合は、確認ボタンを表示
						if ($this->_productExists) $this->tmpl->setAttribute('show_confirm', 'visibility', 'visible');
					}
					$this->tmpl->setAttribute('show_back', 'visibility', 'visible');// 戻るボタン表示
				}
			} else {		// 注文書が取得できない場合はエラー画面へ遷移
				$errPage = $this->gEnv->createCurrentPageUrl() . '&task=' . self::ERROR_TASK;
				$this->gPage->redirect($errPage);
			}
		}
		// 配送先、配送方法の表示制御
		if (in_array(self::DEFAULT_ORDER_DELIVERY_TASK, self::$_orderProcessTasks)){
			$this->tmpl->setAttribute('show_delivery', 'visibility', 'visible');// 配送先入力
			
			// 入力値を戻す
			$this->tmpl->addVar("show_delivery", "name", $name);
			$this->tmpl->addVar("show_delivery", "name_kana", $nameKana);
			$this->tmpl->addVar("show_delivery", "zipcode", $zipcode);
			$this->tmpl->addVar("show_delivery", "address", $address);
			$this->tmpl->addVar("show_delivery", "address2", $address2);
			$this->tmpl->addVar("show_delivery", "phone", $phone);
			
			// 都道府県を設定
			self::$_orderDb->getAllState('JPN', $this->_langId, array($this, 'stateLoop'));
			
			// ##### 非会員の購入の場合はEメールを表示 #####
			if (empty($this->_userId)){
				$this->tmpl->setAttribute('show_email', 'visibility', 'visible');
				$this->tmpl->addVar("show_email", "email", $email);
			}
		}
		if (in_array(self::DEFAULT_ORDER_DELIVMETHOD_TASK, self::$_orderProcessTasks)){
			$this->tmpl->setAttribute('show_delivmethod', 'visibility', 'visible');	// 配送方法選択
			
			// 配送方法を設定
			$delivmethodName = '';
			$delivmethodDesc = '';
			if (self::$_orderDb->getDelivMethod($this->deliveryMethod, $this->_langId, 0/*デフォルトのセットID*/, $row)){
				$delivmethodName = $row['do_name'];
				$delivmethodDesc = $row['do_description'];
			}
			
			// 入力値を戻す
			$this->tmpl->addVar("show_delivmethod", "delivmethod_name", $delivmethodName);
//			$this->tmpl->addVar("show_delivmethod", "delivmethod_desc", $delivmethodDesc);
			$this->tmpl->addVar("show_delivmethod", "delivmethod_note", $note);
			
			// 配送料
			if (!empty($this->deliveryMethod)){		// 配送方法が選択されている場合
				if ($deliveryFee > 0){
					$dispDeliveryFee = $prePrice . self::$_ecObj->convertByCurrencyFormat($this->currency, $this->_langId, $deliveryFee) . $postPrice;
				} else {
					$dispDeliveryFee = '無料';
				}
				$this->tmpl->setAttribute('show_delivery_fee', 'visibility', 'visible');
				$this->tmpl->addVar("show_delivery_fee", "delivery_fee", $dispDeliveryFee);		// 配送料
			}
			
			// 手数料。0のときは非表示。
			if ($charge > 0){
				$dispCharge = $prePrice . self::$_ecObj->convertByCurrencyFormat($this->currency, $this->_langId, $charge) . $postPrice;
				$this->tmpl->setAttribute('show_charge', 'visibility', 'visible');
				$this->tmpl->addVar("show_charge", "charge", $dispCharge);					// 手数料
			}
		}
		// 支払方法
		$paymethodName = '';
		$paymethodDesc = '';
		if (self::$_orderDb->getPaymentMethod($this->payMethod, $this->_langId, 0/*デフォルトのセットID*/, $row)){
			$paymethodName = $row['po_name'];
			$paymethodDesc = $row['po_description'];
		}
		$this->tmpl->addVar("_widget", "paymethod_name", $paymethodName);
//		$this->tmpl->addVar("_widget", "paymethod_desc", $paymethodDesc);

		// ##### 価格表示 #####
		if ($discount > 0){			// 値引額がある場合
			self::$_ecObj->setCurrencyType($this->currency, $this->_langId);		// 通貨設定
			self::$_ecObj->getPriceWithoutTax($discount, $dispDiscount);
			$this->tmpl->setAttribute('show_discount', 'visibility', 'visible');
			$this->tmpl->addVar("show_discount", "discount", $prePrice . '-' . $dispDiscount . $postPrice);
			if (!empty($discountDesc)) $this->tmpl->addVar("show_discount", "discount_desc", '(' . $discountDesc . ')');
		}
		
		// 合計価格
		self::$_ecObj->setCurrencyType($this->currency, $this->_langId);		// 通貨設定
		self::$_ecObj->getPriceWithoutTax($total, $dispTotal);
		$this->tmpl->addVar("_widget", "total", $prePrice . $dispTotal . $postPrice);

		// その他
		$this->tmpl->addVar("_widget", "message_top", $msg);		// メッセージ
		$this->tmpl->addVar("_widget", "goback_url", $this->getUrl($this->gEnv->createCurrentPageUrl() . '&task=' . $this->_getOrderNextTask(-1) . '&act=goback', true));		// 1つ前の画面
		
		// ##### データ削除処理 #####
		// 注文処理完了のときはカート内容、注文書を削除
		if ($orderCompleted){
			// カート内容削除
			$ret = self::$_ecObj->db->getCartHead($this->cartId, $this->_langId, $row);
			if ($ret) self::$_ecObj->db->deleteCartItem($row['sh_serial'], 0);
			
			// 受注シート削除
			$this->_delOrderSheet();
		}
	}
	/**
	 * オンライン決済サービスの接続があるかどうか
	 *
	 * @param array $methodRow		支払方法情報
	 * @return bool					true=接続あり、false=接続なし
	 */
	function getIsOnlinePayment(&$methodRow)
	{
		// 注文書を取得
		$ret = $this->_getOrderSheet($row);
		if ($ret){
			$payMethod = $row['oe_pay_method_id'];		// 支払い方法
			
			$ret = self::$_orderDb->getPaymentMethod($payMethod, $this->_langId, 0/*デフォルトのセットID*/, $methodRow);
			if ($ret){
				// インナーウィジェットの情報を取得
				list($widgetId, $iwidgetId) = explode(',', $methodRow['po_iwidget_id']);

				$ret = $this->_db->getIWidgetInfo($widgetId, $iwidgetId, $row);
				if ($ret){
					if ($row['iw_online']) return true;
				}
			}
		}
		return false;
	}
	/**
	 * オンライン決済サービスを実行
	 *
	 * @param array $payMethodRow	支払方法情報
	 * @param array $orderSheetRow	注文書データ
	 * @param string $orderNo		注文番号
	 * @return bool					true=成功、false=失敗
	 */
	function connectService($payMethodRow, $orderSheetRow, $orderNo)
	{
		$ret = false;
		$iWidgetId	= $payMethodRow['po_iwidget_id'];	// インナーウィジェットID
		if (!empty($iWidgetId)){
			// パラメータをインナーウィジェットに設定し、計算結果を取得
			$optionParam = new stdClass;
			$optionParam->id = $payMethodRow['po_id'];
			$optionParam->init = true;		// 初期データ取得
			$optionParam->userId = $this->_userId;					// ログインユーザID
			$optionParam->languageId = $this->_langId;		// 言語ID
			$optionParam->langId = $this->_langId;		// 言語ID(エイリアス)
			$optionParam->cartId = $this->cartId;					// 商品のカート
//			$optionParam->orderId = $this->orderId;			// 注文ID
			$optionParam->orderNo = $orderNo;				// 注文番号
			$optionParam->orderSheetRow = $orderSheetRow;				// 注文書データ

			if ($this->calcIWidgetParam($iWidgetId, $payMethodRow['po_id'], $payMethodRow['po_param'], $optionParam, $resultObj)){
				if (isset($resultObj->retcode) && $resultObj->retcode == 1){
					$this->redirectUrl = $resultObj->redirectUrl;		// リダイレクト先URL
					$ret = true;		// 成功
				}
			}
		}
		return $ret;
	}
	/**
	 * オンライン決済サービスをキャンセル
	 *
	 * @param array $payMethodRow	支払方法情報
	 * @return bool					true=成功、false=失敗
	 */
	function cancelService($payMethodRow)
	{
		$ret = false;
		$iWidgetId	= $payMethodRow['po_iwidget_id'];	// インナーウィジェットID
		if (!empty($iWidgetId)){
			// パラメータをインナーウィジェットに設定し、計算結果を取得
			$optionParam = new stdClass;
			$optionParam->id = $payMethodRow['po_id'];
			$optionParam->init = true;		// 初期データ取得
			$optionParam->operation = 'cancel_order';			// 注文キャンセル処理
			$optionParam->userId = $this->_userId;			// ログインユーザID
			$optionParam->languageId = $this->_langId;		// 言語ID
			$optionParam->langId = $this->_langId;		// 言語ID(エイリアス)
			$optionParam->cartId = $this->cartId;					// 商品のカート

			if ($this->calcIWidgetParam($iWidgetId, $payMethodRow['po_id'], $payMethodRow['po_param'], $optionParam, $resultObj)){
				if (isset($resultObj->retcode) && $resultObj->retcode == 1){
					$this->orderId = $resultObj->orderId;		// キャンセルする注文情報の注文ID
					$ret = true;		// 成功
				}
			}
		}
		return $ret;
	}
	/**
	 * オンライン決済サービス完了
	 *
	 * @param array $payMethodRow	支払方法情報
	 * @return bool					true=成功、false=失敗
	 */
	function completeService($payMethodRow)
	{
		$ret = false;
		$iWidgetId	= $payMethodRow['po_iwidget_id'];	// インナーウィジェットID
		if (!empty($iWidgetId)){
			// パラメータをインナーウィジェットに設定し、計算結果を取得
			$optionParam = new stdClass;
			$optionParam->id = $payMethodRow['po_id'];
			$optionParam->init = true;		// 初期データ取得
			$optionParam->operation = 'complete_order';			// 決済完了
			$optionParam->userId = $this->_userId;			// ログインユーザID
			$optionParam->languageId = $this->_langId;		// 言語ID
			$optionParam->langId = $this->_langId;		// 言語ID(エイリアス)
			$optionParam->cartId = $this->cartId;					// 商品のカート

			if ($this->calcIWidgetParam($iWidgetId, $payMethodRow['po_id'], $payMethodRow['po_param'], $optionParam, $resultObj)){
				if (isset($resultObj->retcode) && $resultObj->retcode == 1){
					$this->orderId = $resultObj->orderId;		// 注文情報の注文ID
					$this->paymentNote = $resultObj->note;				// 補足情報
					$ret = true;		// 成功
				}
			}
		}
		return $ret;
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
	 * 注文番号を作成(フォーマット: yyyymmdd-0000)
	 *
	 * @return string				注文番号。作成失敗のときは空文字列を返す
	 */
	function createOrderNo()
	{
		// トランザクションスタート
		self::$_orderDb->startTransaction();
						
		$newNo = '';
		$no = self::$_orderDb->getOrderNo();
		if (!empty($no)){
			list($date, $foreNo) = explode('-', $no);
			$today = date("Ymd");
			if ($date == $today){
				$newNo = $date . '-' . sprintf("%04d", (intval($foreNo) + 1));
			}
		}
		// 新規番号の場合
		if (empty($newNo)) $newNo = date("Ymd-" . '0001');	// 現在日
		
		// 注文番号を登録
		$ret = self::$_orderDb->updateOrderNo($newNo);
		
		// トランザクション終了
		$ret = self::$_orderDb->endTransaction();
		if ($ret){
			return $newNo;
		} else {
			return '';
		}
	}
	/**
	 * 注文状態を更新
	 *
	 * @param int $orderId			注文ID
	 * @param int $status			注文状態
	 * * @return bool				true=成功、false=失敗
	 */
	function updateOrderStatus($orderId, $status)
	{
		// データ取得
		$ret = self::$_orderDb->getOrder($orderId, $row);
		if ($ret){
			// トランザクションスタート
			self::$_orderDb->startTransaction();
		
			// 決済完了のときは支払い日時を設定
			$payDt = $row['or_pay_dt'];
			if ($status == photo_shopCommonDef::ORDER_STATUS_PAYMENT_COMPLETED) $payDt = $this->now;
			
			// 受注ヘッダ作成
			$ret = self::$_orderDb->updateOrder($row['or_serial'], $row['or_user_id'], $row['or_language_id'], $row['or_order_no'],
				$row['or_custm_id'], $row['or_custm_name'], $row['or_custm_name_kana'], $row['or_custm_person'], $row['or_custm_person_kana'],
				$row['or_custm_zipcode'], $row['or_custm_state_id'], $row['or_custm_address1'], $row['or_custm_address2'], $row['or_custm_phone'], $row['or_custm_fax'], $row['or_custm_email'], $row['or_custm_country_id'],
				$row['or_deliv_id'], $row['or_deliv_name'], $row['or_deliv_name_kana'], $row['or_deliv_person'], $row['or_deliv_person_kana'],
				$row['or_deliv_zipcode'], $row['or_deliv_state_id'], $row['or_deliv_address1'], $row['or_deliv_address2'], $row['or_deliv_phone'], $row['or_deliv_fax'], $row['or_deliv_email'], $row['or_deliv_country_id'],
				$row['or_bill_id'], $row['or_bill_name'], $row['or_bill_name_kana'], $row['or_bill_person'], $row['or_bill_person_kana'], 
				$row['or_bill_zipcode'], $row['or_bill_state_id'], $row['or_bill_address1'], $row['or_bill_address2'], $row['or_bill_phone'], $row['or_bill_fax'], $row['or_bill_email'], $row['or_bill_country_id'],
				$row['or_deliv_method_id'], $row['or_pay_method_id'], $row['or_card_type'], $row['or_card_owner'], $row['or_card_number'], $row['or_card_expires'],
				$row['or_demand_dt'], $row['or_demand_time'], $row['or_appoint_dt'], $row['or_currency_id'], $row['or_subtotal'], $row['or_discount'], $row['or_deliv_fee'], $row['or_charge'], $row['or_total'],
				$status, $row['or_estimate_dt'], $row['or_regist_dt'], $row['or_order_dt'], $row['or_deliv_dt'], $row['or_close_dt'],
				$this->_userId, $this->now, $newOrderId, $newSerial, $row['or_discount_desc'], $payDt);
				
			// トランザクション終了
			$ret = self::$_orderDb->endTransaction();
		}
		return $ret;
	}
	/**
	 * ダウンロードコンテンツのアクセス権を設定
	 *
	 * @param int  	$userId				ユーザID
	 * @param int	$orderId			注文ID
	 * @return bool						true=成功、false=失敗
	 */
	function setDownloadContentAccess($userId, $orderId)
	{
		// ダウンロードコンテンツのIDを取得
		$this->_useOrderDetail = true;		// カート内容でなく、受注内容を取得
		$this->_updateContentAccess = true;		// コンテンツアクセス権の設定かどうか
		$this->_selectProductClass	= photo_shopCommonDef::PRODUCT_CLASS_PHOTO;		// 商品クラス(フォトギャラリー画像)
		$this->_selectProductType	= photo_shopCommonDef::PRODUCT_TYPE_DOWNLOAD;		// 商品タイプ(ダウンロード画像)
		$this->_contentIdArray = array();		// コンテンツID
		self::$_orderDb->getOrderDetailList($orderId, $this->gEnv->getCurrentLanguage(), array($this, '_defaultCartLoop'));
		$this->_useOrderDetail = false;		// カート内容でなく、受注内容を取得
		$this->_updateContentAccess = false;		// コンテンツアクセス権の設定かどうか
				
		// コンテンツのアクセス権を更新
		$ret = self::$_mainDb->updateContentAccess($userId, M3_VIEW_TYPE_PHOTO, $this->_contentIdArray);
		return $ret;
	}
	/**
	 * 受注登録、更新情報をメール送信する
	 *
	 * @param int $mailType			メールタイプ(0=受注情報登録、1=更新、2=削除)
	 * @param string $orderNo		注文No
	 * @param string $memberNo		会員No
	 * @param string $email			eメール(ログインアカウント)
	 * @param string $memberName	会員名
	 * @param string $delivName		届け先名
	 * @param string $nameKana		会員名カナ
	 * @param string $zipcode		配送先郵便番号
	 * @param string $state			都道府県
	 * @param string $address1		配送先住所1
	 * @param string $address2		配送先住所2
	 * @param string $phone			配送先電話番号
	 * @param string $demandDt		配達希望日
	 * @param string $demandTime	配達希望時間帯
	 * @param string $delivMethod	配送方法
	 * @param string $payMethod		決済方法
	 * @param string $note			備考
	 * @param string $orderDetail	受注明細データ
	 * @param string $orderText	注文内容
	 * @param string $adminUrl	管理画面URL
	 * @return bool					true=成功、false=失敗
	 */
	function sendOrderMail($mailType, $orderNo, $memberNo, $email, $memberName, $delivName, $nameKana, $zipcode, $state, $address1, $address2, $phone, 
									$demandDt, $demandTime, $delivMethod, $payMethod, $note, $orderDetail, $orderText, $adminUrl)
	{	
		$fromAddress = $this->_getConfig(photo_shopCommonDef::CF_AUTO_EMAIL_SENDER);	// 自動送信送信元
		if (empty($fromAddress)) $fromAddress = $this->gEnv->getSiteEmail();// 送信元が取得できないときは、システムのデフォルトメールアドレスを使用
		$toAddress = $this->_getConfig(photo_shopCommonDef::CF_EMAIL_TO_ORDER_PRODUCT);	// 商品受注時送信先メールアドレス
		if (empty($toAddress)) $toAddress = $this->gEnv->getSiteEmail();// 送信先が取得できないときは、システムのデフォルトメールアドレスを使用
		
		// 件名の設定
		$operation = '不明';
		if ($mailType == 0){	// 新規登録
			$operation = '受注情報新規';
		} else if ($mailType == 1){	// 更新
			$operation = '受注情報更新';
		} else if ($mailType == 2){	// 削除
			$operation = '受注情報削除';
		}
		$now = date("Y/m/d:H.i.s");	// 現在日時
		$subject = $memberNo . ':' . $memberName . ':' . $now . ':' . $operation;
		$mailParam = array();
		$mailParam['ORDER_NO'] = $orderNo;		// 受注No
		$mailParam['DATE'] = date("Y/m/d");		// 受付日付
		$mailParam['MEMBER_NO'] = $memberNo;
		$mailParam['EMAIL'] = $email;
		$mailParam['NAME'] = $memberName;
		$mailParam['DELIV_NAME'] = $delivName;		// 届け先名
		$mailParam['NAME_KANA'] = $nameKana;
		$mailParam['ZIPCODE'] = $zipcode;
		$mailParam['STATE'] = $state;			// 都道府県
		$mailParam['ADDRESS1'] = $address1;
		$mailParam['ADDRESS2'] = $address2;
		$mailParam['PHONE'] = $phone;
		$mailParam['DEMAND_DATE'] = $demandDt;		// 配達希望日
		$mailParam['DEMAND_TIME'] = $demandTime;	// 配達希望時間帯
		$mailParam['BODY'] = "■明細開始\n" . $orderDetail . "■明細終了\n";		// 受注明細
		$mailParam['DELIV_METHOD'] = $delivMethod;	// 配送方法
		$mailParam['PAY_METHOD'] = $payMethod;	// 決済方法
		$mailParam['NOTE'] = $note;	// 備考
		$delivText = $zipcode . M3_NL . $state . M3_NL . $address1 . M3_NL;	// お届け先
		if (empty($address2)){
			$delivText .= 'TEL ' . $phone  . M3_NL . $delivName . ' 様';
		} else {
			$delivText .= $address2  . M3_NL . 'TEL ' . $phone  . M3_NL . $delivName . ' 様';
		}
		$mailParam['DELIV_TEXT'] = $delivText;
		$mailParam['ORDER_TEXT'] = $orderText;	// 注文内容
		$mailParam['ADMIN_URL'] = $adminUrl . ' ';	// 管理画面URL
		$ret = $this->gInstance->getMailManager()->sendFormMail(1/*自動送信*/, $this->gEnv->getCurrentWidgetId(), $toAddress, $fromAddress, ''/*返信先*/,
											$subject/*件名*/, photo_shopCommonDef::MAIL_FORM_ORDER_PRODUCT_TO_SHOP_MANAGER, $mailParam);// 自動送信
											
		// ##### 購入者にメールを送信 #####
		if (empty($email)) return $ret;			// 送信先が設定されていないときは送信しない
		
		$fromAddress = $this->_getConfig(photo_shopCommonDef::CF_AUTO_EMAIL_SENDER);	// 自動送信送信元
		if (empty($fromAddress)) $fromAddress = $this->gEnv->getSiteEmail();// 送信元が取得できないときは、システムのデフォルトメールアドレスを使用
		$toAddress = $email;

		$mailParam = array();
		$mailParam['NAME'] = $memberName;
		$mailParam['SHOP_NAME']		= self::$_mainDb->getCommerceConfig(photo_shopCommonDef::CF_E_SHOP_NAME);		// ショップ名
		$mailParam['DELIV_TEXT'] = $delivText;
		$mailParam['ORDER_TEXT']	= $orderText;	// 注文内容
		$mailParam['SIGNATURE']	= self::$_mainDb->getCommerceConfig(photo_shopCommonDef::CF_E_SHOP_SIGNATURE);	// ショップメール署名
		$ret = $this->gInstance->getMailManager()->sendFormMail(1/*自動送信*/, $this->gEnv->getCurrentWidgetId(), $toAddress, $fromAddress, ''/*返信先*/,
											''/*件名*/, photo_shopCommonDef::MAIL_FORM_ORDER_PRODUCT_TO_CUSTOMER, $mailParam);// 自動送信
	}
}
?>
