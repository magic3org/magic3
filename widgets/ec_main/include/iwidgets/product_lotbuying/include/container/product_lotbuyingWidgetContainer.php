<?php
/**
 * コンテナクラス
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2012 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: product_lotbuyingWidgetContainer.php 5437 2012-12-07 13:14:59Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseIWidgetContainer.php');
require_once($gEnvManager->getCurrentIWidgetDbPath() . '/product_lotbuyingDb.php');

class product_lotbuyingWidgetContainer extends BaseIWidgetContainer
{
	private $db;	// DB接続オブジェクト
	private $ecObj;			// 価格計算用オブジェクト
	private $langId;			// 言語ID
	private $productClass;		// 商品クラス
	private $productTotal;					// 合計価格
	private $productCount;					// 商品総数
	private $productArray;					// 集計対象
	private $calcTotal;					// 割引額計算用データ
	const PRICE_OBJ_ID = "eclib";		// 価格計算オブジェクトID
	const PRODUCT_CLASS_DEFAULT	= '';		// 商品クラス
	const PRODUCT_CLASS_PHOTO	= 'photo';		// 商品クラス
	const REGULAR_PRICE 		= 'regular';		// 通常価格
	const PRODUCT_TYPE_DOWNLOAD = 'download';		// 商品タイプ
	const TAX_TYPE				= 'sales';						// 課税タイプ(外税)
	const PRODUCT_NAME_FORMAT	= '%s(%s)';		// 商品名表示フォーマット
	const PRODUCT_CODE_FORMAT	= '%s-%s';		// 商品コード表示フォーマット
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト取得
		$this->db = new product_lotbuyingDb();
		
		// 価格計算用オブジェクト取得
		$this->ecObj = $this->gInstance->getObject(self::PRICE_OBJ_ID);
	}
	/**
	 * テンプレートファイルを設定
	 *
	 * _assign()でデータを埋め込むテンプレートファイルのファイル名を返す。
	 * 読み込むディレクトリは、「自ウィジェットディレクトリ/include/template」に固定。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param string         $act			実行処理
	 * @param object         $configObj		定義情報オブジェクト
	 * @param object         $optionObj		可変パラメータオブジェクト
	 * @return string 						テンプレートファイル名。テンプレートライブラリを使用しない場合は空文字列「''」を返す。
	 */
	function _setTemplate($request, $act, $configObj, $optionObj)
	{
		return 'index.tmpl.html';
	}
	/**
	 * テンプレートにデータ埋め込む
	 *
	 * _setTemplate()で指定したテンプレートファイルにデータを埋め込む。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param string         $act			実行処理
	 * @param object         $configObj		定義情報オブジェクト
	 * @param object         $optionObj		可変パラメータオブジェクト
	 * @param								なし
	 */
	function _assign($request, $act, $configObj, $optionObj)
	{
		// 基本情報を取得
		$id		= $optionObj->id;		// ユニークなID
		$init	= $optionObj->init;		// データ初期化を行うかどうか
		
		// 入力値取得
		if ($act == 'calc'){		// 計算のとき
			// 定義値取得
			$this->productClass	= $configObj->productClass;		// 商品クラス
			$productId			= $configObj->productId;			// 商品ID
			$rateTable			= $configObj->rateTable;		// 割引率表
			
			$this->productArray = explode(',', $productId);		// 集計対象
			$rateArray = parseUserCustomParam($rateTable);	// 割引率表を配列化
			
			// 可変データ取得
			$this->langId	= $optionObj->languageId;		// 言語ID
			$cartId			= $optionObj->cartId;			// 商品のカート

			// カート内容を取得
			$this->isErr = false;			// エラーステータス
			$this->_productTotal = 0;					// 合計価格
			$this->_productCount = 0;					// 商品総数
			$this->calcTotal = array();					// 割引額計算用データ
			$this->ecObj->db->getCartItems($cartId, $this->langId, $this->productClass, array($this, _calcCartLoop));

			// 割引額を求める
			$discountTotal = 0;
			$keys = array_keys($this->calcTotal);
			for ($i = 0; $i < count($keys); $i++){
				$productObj = $this->calcTotal[$keys[$i]];
				$total = $productObj->total;
				$quantity = $productObj->quantity;
				$price = 0;
				$foreValue = 0;
				for ($j = 0; $j < count($rateArray); $j++){
					if ($foreValue <= $quantity && $quantity < $rateArray[$j]->key) break;
					$foreValue = $rateArray[$j]->key;
				}
				if ($j > 0) $price = $total * $rateArray[$j -1]->value * 0.01;
				$discountTotal -= intval($price);// 割引額合計更新
			}
			
			// 計算結果オブジェクトに設定
			$resultObj->price = $discountTotal;	// 割引額合計。割引額は負の値
			$this->setResultObj($resultObj);
		} else if ($act == 'content'){		// 画面表示のとき
		}
	}
	/**
	 * 取得したデータをテンプレートに設定する
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function _calcCartLoop($index, $fetchedRow, $param)
	{
		static $itemIndex = 0;

		$priceAvailable = true;	// 価格が有効であるかどうか
		$productClass = $fetchedRow['si_product_class'];		// 商品クラス
		$productType = $fetchedRow['si_product_type_id'];		// 商品タイプ
		$productId = $fetchedRow['si_product_id'];				// 商品ID
		$prePrice = $this->convertToDispString($fetchedRow['cu_symbol']);		// 価格表示用
		$postPrice = $this->convertToDispString($fetchedRow['cu_post_symbol']);	// 価格表示用
		
		switch ($productClass){
			case self::PRODUCT_CLASS_PHOTO:		// フォトギャラリー画像のとき
				$photoId = $fetchedRow['ht_public_id'];		// 公開画像ID
				$title = $fetchedRow['ht_name'];		// サムネール画像タイトル
				$productTypeName = $fetchedRow['py_name'];		// 商品タイプ名
				$productTypeCode = $fetchedRow['py_code'];		// 商品タイプコード

				// 表示用の商品名、商品コード作成
				$productName = sprintf(ec_mainCommonDef::PRODUCT_NAME_FORMAT, $productTypeName, $title);		// 商品名
				$productCode = sprintf(ec_mainCommonDef::PRODUCT_CODE_FORMAT, $photoId, $productTypeCode);		// 商品コード
				
				// 商品の状態
				if (!$fetchedRow['ht_visible']) $priceAvailable = false;		// 商品が表示不可のときは価格を無効とする
				
				// 画像価格情報を取得
				$ret = $this->db->getPhotoInfoWithPrice($productId, $productClass, $productType, self::REGULAR_PRICE, $this->langId, $fetchedRow['cu_id'], $row);
				break;
			case self::PRODUCT_CLASS_DEFAULT:	// 一般商品のとき
				// 表示用の商品名、商品コード作成
				$productName = $fetchedRow['pt_name'];		// 商品名
				$productCode = $fetchedRow['pt_code'];		// 商品コード
				
				// 商品の状態
				if (!$fetchedRow['pt_visible']) $priceAvailable = false;		// 商品が表示不可のときは価格を無効とする
				
				// 商品価格情報を取得
				$ret = $this->db->getProductByProductId($productId, $this->langId, $fetchedRow['cu_id'], $row, $imageRows);
				break;
		}
		
		if ($ret){
			// 価格を取得
			$price = $row['pp_price'];	// 価格
			$currency = $row['pp_currency_id'];	// 通貨
			$taxType = self::TAX_TYPE;					// 税種別

			// 価格作成
			$this->ecObj->setCurrencyType($currency, $this->langId);		// 通貨設定
			$this->ecObj->setTaxType($taxType);		// 税種別設定
			$unitPrice = $this->ecObj->getPriceWithTax($price, $dispUnitPrice);	// 税込み価格取得
			$dispUnitPrice = $prePrice . $dispUnitPrice . $postPrice;
		} else {
			$priceAvailable = false;
		}
		
		// ##### カートの内容のチェック #####
		// 価格が変更のときは、価格を無効にする
		$quantity = $fetchedRow['si_quantity'];
		$subtotal = $fetchedRow['si_subtotal'];
		$oldCurrency = $fetchedRow['si_currency_id'];
		if ($unitPrice * $quantity != $subtotal) $priceAvailable = false;
		if ($oldCurrency != $currency) $priceAvailable = false;

		// 価格の有効判断
		if (!$fetchedRow['si_available']) $priceAvailable = false;

		if ($priceAvailable){		// 価格無効があった場合はエラーを返す
			$this->_productTotal += $subtotal;					// 合計価格
			$this->_productCount += $quantity;					// 商品総数
			$itemIndex++;
			
			// 商品ごとの集計
			switch ($productClass){
				case self::PRODUCT_CLASS_PHOTO:		// フォト商品のとき
					if ($productType != self::PRODUCT_TYPE_DOWNLOAD){		// ダウンロード商品以外を集計
						if (in_array($productType, $this->productArray)) $this->addProduct($productType, $subtotal, $quantity);
					}
					break;
				case self::PRODUCT_CLASS_DEFAULT:	// 一般商品のとき
					if (in_array($productId, $this->productArray)) $this->addProduct($productId, $subtotal, $quantity);
					break;
			}
			return true;
		} else {
			$this->isErr = true;			// エラーステータス
			return false;		// 処理中断
		}
	}
	
	/**
	 * 割引額計算用に商品を追加
	 *
	 * @param int $productId	商品ID
	 * @param float $total		商品価格合計
	 * @param int $quantity		商品総数
	 * @return 					なし
	 */
	function addProduct($productId, $total, $quantity)
	{
		if (array_key_exists($productId, $this->calcTotal)){
			$productObj = $this->calcTotal[$productId];
			$productObj->total += $total;
			$productObj->quantity += $quantity;
		} else {
			$productObj = new stdClass;
			$productObj->total = $total;
			$productObj->quantity = $quantity;
			$this->calcTotal[$productId] = $productObj;
		}
	}
}
?>
