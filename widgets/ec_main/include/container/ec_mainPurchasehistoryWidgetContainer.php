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
 * @copyright  Copyright 2006-2017 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/ec_mainBaseWidgetContainer.php');

class ec_mainPurchasehistoryWidgetContainer extends ec_mainBaseWidgetContainer
{
	private $orderId;				// 現在選択中の受注ID
	private $lastOrderId;			// 最新の受注ID
	private $isExistsOrder;			// 購入履歴があるかどうか
		
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
		return 'purchasehistory.tmpl.html';
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
		$this->currency = ec_mainCommonDef::DEFAULT_CURRENCY;		// デフォルト通貨
		
		// 画像情報を取得
		$this->_productImageWidth = 0;		// 商品画像幅
		$this->_productImageHeight = 0;		// 商品画像高さ
		$ret = self::$_mainDb->getProductImageInfo(ec_mainCommonDef::PRODUCT_IMAGE_SMALL, $row);
		if ($ret){
			$this->_productImageWidth = $row['is_width'];
			$this->_productImageHeight = $row['is_height'];
		}
		
		$this->orderId = $request->trimValueOf('item_order');		// 受注ID
		$act = $request->trimValueOf('act');
		
		// 注文履歴をすべて取得
		self::$_orderDb->getOrderHeaderByUser($this->_userId, array($this, 'orderListLoop'));

		$showOrderDetail = false;		// 受注情報を表示フラグクリア
		if ($act == 'selorder'){		// 注文項目を選択
			// データにアクセス可能かチェック
			if (self::$_orderDb->isOrderByUser($this->orderId, $this->_userId)){
				$showOrderDetail = true;		// 受注情報を表示
			} else {
				$this->setAppErrorMsg('アクセスできません');
			}
		} else {	// 初期処理
			if ($this->isExistsOrder){			// 購入履歴があるかどうか
				$this->orderId = $this->lastOrderId;
				$showOrderDetail = true;		// 受注情報を表示
			}
		}
		
		if ($showOrderDetail){		// 受注情報にアクセス可能なとき
			// 受注情報を取得
			$ret = self::$_orderDb->getOrder($this->orderId, $row);
			if ($ret){
				$this->currency	= $row['or_currency_id'];	// 通貨
				$subtotal = $row['or_subtotal'];		// 商品総額
				$discount = $row['or_discount'];		// 値引き額
				$delivFee = $row['or_deliv_fee'];		// 配送料
				$charge = $row['or_charge'];		// 手数料
				$total = $row['or_total'];			// 総額
				$orderDt	= $row['or_regist_dt'];	// 注文日時
				$discountDesc = $row['or_discount_desc'];	// 値引き説明

				// 通貨情報を取得
				$ret = self::$_ecObj->db->getCurrency($this->currency, $this->_langId, $row);
				if ($ret){
					$prePrice = $this->convertToDispString($row['cu_symbol']);
					$postPrice = $this->convertToDispString($row['cu_post_symbol']);
				}
		
				$subtotalStr = self::$_ecObj->convertByCurrencyFormat($this->currency, $this->_langId, $subtotal);
				$this->tmpl->addVar("order_history", "subtotal", $prePrice . $subtotalStr . $postPrice);		// 商品総額
				$delivFeeStr = self::$_ecObj->convertByCurrencyFormat($this->currency, $this->_langId, $delivFee);
				$this->tmpl->addVar("deliv_visible", "delivery_fee", $prePrice . $delivFeeStr . $postPrice);		// 送料
				$chargeStr = self::$_ecObj->convertByCurrencyFormat($this->currency, $this->_langId, $charge);
				$this->tmpl->addVar("charge_visible", "charge", $prePrice . $chargeStr . $postPrice);		// 手数料
				$totalStr = self::$_ecObj->convertByCurrencyFormat($this->currency, $this->_langId, $total);
				$this->tmpl->addVar("order_history", "total", $prePrice . $totalStr . $postPrice);		// 総額
				
				// 値引き額表示
				if ($discount > 0){
					$this->tmpl->setAttribute('show_discount', 'visibility', 'visible');
					$priceStr = '-' . self::$_ecObj->convertByCurrencyFormat($this->currency, $this->_langId, $discount);
					$this->tmpl->addVar("show_discount", "discount", $prePrice . $priceStr . $postPrice);
					if (!empty($discountDesc)) $this->tmpl->addVar("show_discount", "discount_desc", $this->convertToDispString('(' . $discountDesc . ')'));
				}
				// 項目の表示制御
				if ($delivFee == 0) $this->tmpl->setAttribute('deliv_visible', 'visibility', 'hidden');
				if ($charge == 0) $this->tmpl->setAttribute('charge_visible', 'visibility', 'hidden');
				
				// 受注商品を取得
				$this->_useOrderDetail = true;		// カート内容でなく、受注内容を取得
				self::$_orderDb->getOrderDetailList($this->orderId, $this->_langId, array($this, '_defaultCartLoop'));
			}
		}
		if ($this->isExistsOrder){			// 購入履歴があるかどうか
			$this->tmpl->setAttribute('order_history', 'visibility', 'visible');
		} else {
			$this->tmpl->addVar("_widget", "message", '購入履歴がありません');
		}
		
		$this->tmpl->addVar("_widget", "history_url", $this->getUrl($this->gEnv->createCurrentPageUrl() . '&task=purchasehistory', true));		// 購入履歴URL
	}
	/**
	 * 取得したデータをテンプレートに設定する
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function orderListLoop($index, $fetchedRow, $param)
	{
		$selected = '';
		if ($fetchedRow['or_id'] == $this->orderId){		// 選択中の受注ID
			$selected = 'selected';
		}
		$row = array(
			'value'    => $this->convertToDispString($fetchedRow['or_id']),			// ID
			'name'     => $this->convertToDispDateTime($fetchedRow['or_regist_dt'], 0, 10),		// 注文日時
			'selected' => $selected														// 選択中かどうか
		);
		$this->tmpl->addVars('orderlist', $row);
		$this->tmpl->parseTemplate('orderlist', 'a');
		
		$this->isExistsOrder = true;			// 購入履歴があるかどうか
		if ($index == 0) $this->lastOrderId = $fetchedRow['or_id'];			// 最近の受注ID
		return true;
	}
}
?>
