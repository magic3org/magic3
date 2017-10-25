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

class ec_mainCartWidgetContainer extends ec_mainBaseWidgetContainer
{
	private $request;
	
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
		return 'cart.tmpl.html';
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
		$this->request = $request;
		$index = $request->trimValueOf('index');	// 処理対象項目インデックス
		$backUrl = $request->trimValueOf('backurl');	// 戻り先URL
		$defaultCurrency = ec_mainCommonDef::DEFAULT_CURRENCY;		// デフォルト通貨
				
		// クッキー読み込み、カートIDを取得
		$cartId = $request->getCookieValue(M3_COOKIE_CART_ID);
		
		$act = $request->trimValueOf('act');
		if ($act == 'delproduct' || $act == 'delproductall'){		// カート項目の削除のとき
			if (!empty($cartId)){	// カートIDが設定されているとき
				// カート情報を取得
				$ret = self::$_ecObj->db->getCartHead($cartId, $this->_langId, $row);
				if ($ret){
					$cartSerial = $row['sh_serial'];

					if ($act == 'delproduct'){		// 商品削除の場合
						// 項目位置を取得
						list($productClass, $itemIndex) = explode(',', $request->trimValueOf('iteminfo_' . $index));
						
						// カート内の商品を取得
						$ret = self::$_ecObj->db->getCartItemByIndex($cartId, $this->_langId, $productClass, $itemIndex, $cartItemRow);
						if ($ret){
							$checkVal = $request->trimValueOf('checkvalue_' . $index);
							
							switch ($productClass){
								case ec_mainCommonDef::PRODUCT_CLASS_PHOTO:		// フォトギャラリー画像のとき
									$checkId = $cartItemRow['ht_public_id'];
									break;
								case ec_mainCommonDef::PRODUCT_CLASS_DEFAULT:	// 一般商品のとき
									$checkId = $cartItemRow['pt_id'];
									break;
							}
							$cartItemSerial = $cartItemRow['si_serial'];		// 削除カート項目
							
							// 項目を削除
							if ($checkVal == $checkId) self::$_ecObj->db->deleteCartItem($cartSerial, $cartItemSerial);
						}
					} else {	// すべての商品を削除のとき
						// 項目をすべて削除
						self::$_ecObj->db->deleteCartItem($cartSerial);
					}
				}
			}
		} else if ($act == 'updatecart'){		// カートの更新のとき
			// カート内容を表示
			if (!empty($cartId)){	// カートIDが設定されているとき
				// カートの商品を取得
				$this->_getCartItems('updateCartLoop');
				
				// カート情報を取得
				$ret = self::$_ecObj->db->getCartHead($cartId, $this->_langId, $row);
				if ($ret){
					$cartSerial = $row['sh_serial'];
					
					// カートヘッダの更新日時を更新
					self::$_ecObj->db->updateCartDt($cartSerial);
				}
			}
		} else {
			// カート画面へ来たときは受注書を一旦削除
			$this->_delOrderSheet();
		}
					
		// カート内容を表示
		if (!empty($cartId)){	// カートIDが設定されているとき
			$this->_total = 0;					// 合計価格
			$this->_canUpdateCart = false;			// カートが更新可能かどうか
			$discount = 0;						// 割引額
			$discountDesc = '';					// 割引説明

			// 通貨情報を取得
			$ret = self::$_ecObj->db->getCurrency($defaultCurrency, $this->_langId, $row);
			if ($ret){
				$prePrice = $this->convertToDispString($row['cu_symbol']);
				$postPrice = $this->convertToDispString($row['cu_post_symbol']);
			}
			// 画像情報を取得
			$this->_productImageWidth = 0;		// 商品画像幅
			$this->_productImageHeight = 0;		// 商品画像高さ
			$ret = self::$_mainDb->getProductImageInfo(ec_mainCommonDef::PRODUCT_IMAGE_SMALL, $row);
			if ($ret){
				$this->_productImageWidth = $row['is_width'];
				$this->_productImageHeight = $row['is_height'];
			}
	
			// カートの商品を取得
			$this->_getCartItems('_defaultCartLoop');
			
			// 値引額、割り増し額を求める
			$ret = $this->getExtraPrice($extraPrice, $extraTitle);
			if ($ret){
				for ($i = 0; $i < count($extraPrice); $i++){
					$discountPrice = $extraPrice[$i];
					if ($discountPrice < 0){
						$discount -= $discountPrice;
						$discountDesc .= $extraTitle[$i] . ',';
					}
				}
				$discountDesc = '(' . trim($discountDesc, ',') . ')';
			}
			if ($discount > 0){			// 値引額がある場合
				$this->_total -= $discount;
				
				self::$_ecObj->setCurrencyType($defaultCurrency, $this->_langId);		// 通貨設定
				self::$_ecObj->getPriceWithoutTax($discount, $dispDiscount);
				$this->tmpl->setAttribute('show_discount', 'visibility', 'visible');
				$this->tmpl->addVar("show_discount", "discount", $prePrice . '-' . $dispDiscount . $postPrice);
				$this->tmpl->addVar("show_discount", "discount_desc", $discountDesc);
			}
			
			// 合計価格
			self::$_ecObj->setCurrencyType($defaultCurrency, $this->_langId);		// 通貨設定
			self::$_ecObj->getPriceWithoutTax($this->_total, $dispTotal);
			$this->tmpl->addVar("show_cart", "total", $prePrice . $dispTotal . $postPrice);
		}
		
		// カート項目がないとき
		if ($this->_productExists){
			$this->tmpl->setAttribute('show_cart', 'visibility', 'visible');
		} else {
			$this->tmpl->setAttribute('no_item_message', 'visibility', 'visible');
		}
		// 注文受付停止中は購入ボタンを不可にする(システム管理者以外)
		if (!$this->gEnv->isSystemAdmin() && !$this->_getConfig(ec_mainCommonDef::CF_E_ACCEPT_ORDER)){
			$this->tmpl->addVar("show_cart", "order_msg", 'ただ今、一時的に注文処理を停止しています');
			$this->tmpl->addVar("show_cart", "order_disabled", 'disabled');
		}
		// エラーの場合は購入ボタンの使用不可
		if ($this->getMsgCount() > 0){
			$this->tmpl->addVar("show_cart", "order_msg", 'カートの内容を確認してください');
			$this->tmpl->addVar("show_cart", "order_disabled", 'disabled');
		}
		
		// 遷移先を設定
		if (empty($backUrl)) $backUrl = $this->gRequest->trimServerValueOf('HTTP_REFERER');
		$this->tmpl->addVar("_widget", "back_url", $backUrl);			// 戻り先URL
		$this->tmpl->addVar("show_cart", "order_url", $this->getUrl($this->gEnv->createCurrentPageUrl() . '&task=order', true));		// 購入用URL
	}
	/**
	 * 取得したデータをテンプレートに設定する
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function updateCartLoop($index, $fetchedRow, $request)
	{
		static $itemIndex = 0;
		
		$priceAvailable = true;	// 価格が有効であるかどうか
		$cartItemSerial = $fetchedRow['si_serial'];		// カート項目シリアル番号
		$productClass = $fetchedRow['si_product_class'];		// 商品クラス
		$productType = $fetchedRow['si_product_type_id'];		// 商品タイプ
		$productId = $fetchedRow['si_product_id'];				// 商品ID
		$prePrice = $this->convertToDispString($fetchedRow['cu_symbol']);		// 価格表示用
		$postPrice = $this->convertToDispString($fetchedRow['cu_post_symbol']);	// 価格表示用
		
		// 入力された数量を取得
		$quantity = $this->request->trimValueOf('quantity_' . $itemIndex);	// 数量
		$checkVal = $this->request->trimValueOf('checkvalue_' . $itemIndex);	// チェック値
		$itemIndex++;
		
		switch ($productClass){
			case ec_mainCommonDef::PRODUCT_CLASS_PHOTO:		// フォトギャラリー画像のとき
				$checkId = $fetchedRow['ht_public_id'];
				if ($checkVal != $checkId) return true;// 入力チェックエラーの場合は終了
				
				// 数量の修正
				if ($fetchedRow['py_single_select'] && $quantity > 1) $quantity = 1;// 単一選択商品のときは数量を1に限定
				
				// 商品の状態
				if (!$fetchedRow['ht_visible']) $priceAvailable = false;		// 商品が表示不可のときは価格を無効とする
				
				// 画像価格情報を取得
				$ret = self::$_mainDb->getPhotoInfoWithPrice($productId, $productClass, $productType, ec_mainCommonDef::REGULAR_PRICE, $this->_langId, $row);
				
				break;
			case ec_mainCommonDef::PRODUCT_CLASS_DEFAULT:	// 一般商品のとき
				$checkId = $fetchedRow['pt_id'];
				if ($checkVal != $checkId) return true;// 入力チェックエラーの場合は終了
				
				// 商品の状態
				if (!$fetchedRow['pt_visible']) $priceAvailable = false;		// 商品が表示不可のときは価格を無効とする
				
				// 商品価格情報を取得
				$ret = self::$_mainDb->getProductByProductId($productId, $this->_langId, $row, $imageRows);
				break;
		}
		
		if ($ret){
			// 価格を取得
			$price = $row['pp_price'];	// 価格
			$currency = $row['pp_currency_id'];	// 通貨
			$taxType = ec_mainCommonDef::TAX_TYPE;					// 税種別

			// 価格作成
			self::$_ecObj->setCurrencyType($currency, $this->_langId);		// 通貨設定
			self::$_ecObj->setTaxType($taxType);		// 税種別設定
			$unitPrice = self::$_ecObj->getPriceWithTax($price, $dispUnitPrice);	// 税込み価格取得
			$dispUnitPrice = $prePrice . $dispUnitPrice . $postPrice;
		} else {
			$priceAvailable = false;
		}
		
		// 小計を再計算
		$subtotal = $unitPrice * $quantity;

		// カートの情報を更新
		self::$_ecObj->db->updateCartItem($cartItemSerial, $currency, $quantity, $subtotal, $priceAvailable);
		return true;
	}
}
?>
