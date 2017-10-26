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
require_once($gEnvManager->getCurrentWidgetContainerPath() . '/ec_mainCommonDef.php');
require_once($gEnvManager->getContainerPath() . '/baseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/ec_mainDb.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/ec_mainOrderDb.php');

class ec_mainBaseWidgetContainer extends BaseWidgetContainer
{
	protected static $_mainDb;			// DB接続オブジェクト
	protected static $_orderDb;			// DB接続オブジェクト
	protected static $_ecObj;			// 価格計算用オブジェクト
	protected static $_configArray;		// Eコマース定義値
	protected static $_task;			// 現在のタスク
	protected $_langId;			// 現在の言語
	protected $_userId;			// 現在のユーザ
	protected $_now;			// 現在日時
	protected static $_orderProcessAllTasks;	// すべての注文処理プロセスタスク
	protected static $_orderProcessTasks;	// 注文処理プロセス
	protected static $_productClass;		// カート内の商品の商品クラス
	
	// カート一覧処理用
	protected $_total;			// 合計価格
	protected $_productExists;			// カートに商品があるかどうか
	protected $_createEmailData;	// メール送信用の受注データを作成するかどうか
	protected $_addToOrder;			// 受注明細を登録するかどうか
	protected $_emailData;			// メール送信用の受注データ
	protected $_orderText;			// メール送信用の受注内容
	protected $_useOrderDetail;		// カート内容でなく、受注内容を取得
	protected $_canUpdateCart;			// カートが更新可能かどうか
	protected $_productTotal;		// 商品合計額
	protected $_productCount;		// 商品総数
	protected $_contentIdArray;		// 注文詳細のコンテンツID取得用
	protected $_checkCart;			// カート内容のエラーチェックかどうか
	protected $_updateContentAccess;		// コンテンツアクセス権の設定かどうか
	protected $_selectProductClass;		// 商品クラス
	protected $_selectProductType;		// 商品タイプ
	protected $_isExistsDefaultProduct;		// 一般商品が含まれているかどうか
	protected $_isExistsPhotoProduct;		// フォト関連商品が含まれているかどうか
	protected $_productImageWidth;		// 商品画像幅
	protected $_productImageHeight;		// 商品画像高さ
	
	const CSS_FILE = '/style.css';		// CSSファイルのパス
	const PRICE_OBJ_ID = "eclib";		// 価格計算オブジェクトID
	const SHORT_TITLE_LENGTH = 20;		// カート用タイトル名長さ
	const DEFAULT_PRODUCT_IMAGE_TYPE = 'c.jpg';			// 商品画像ファイルのタイプ
	const PRODUCT_IMAGE_DIR = '/widgets/product/image/';				// 商品画像格納ディレクトリ
	
	// 画面
	const DEFAULT_TOP_TASK = 'login';		// デフォルトのトップページ
	const DEFAULT_MEMBER_TASK = 'membermenu';		// デフォルトの会員ページ
	const DEFAULT_ORDER_BACK_TASK = 'cart';			// 購入処理からの戻りタスク
	const DEFAULT_ORDER_DELIVERY_TASK = 'delivery';				// 配送先入力
	const DEFAULT_ORDER_DELIVMETHOD_TASK = 'delivmethod';		// 配送方法選択
	const ERROR_TASK		= 'error';		// エラー画面
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();

		// DBオブジェクト作成
		if (!isset(self::$_mainDb)) self::$_mainDb = new ec_mainDb();
		if (!isset(self::$_orderDb)) self::$_orderDb = new ec_mainOrderDb();

		// 価格計算用オブジェクト取得
		if (!isset(self::$_ecObj)) self::$_ecObj = $this->gInstance->getObject(self::PRICE_OBJ_ID);
		
		// Eコマース定義を読み込む
		if (!isset(self::$_configArray)) self::$_configArray = ec_mainCommonDef::loadConfig(self::$_mainDb);
		
		$this->_langId = $this->gEnv->getCurrentLanguage();
		$this->_userId = $this->gEnv->getCurrentUserId();
		$this->_now = date("Y/m/d H:i:s");			// 現在日時
		
		// 商品クラス表示順
		$productClassOrder = array(	ec_mainCommonDef::PRODUCT_CLASS_PHOTO,		// フォトギャラリー画像
									ec_mainCommonDef::PRODUCT_CLASS_DEFAULT);	// 一般商品
		
		// 注文処理プロセス
		//if (!isset(self::$_orderProcessTasks)){
		if (!isset(self::$_orderProcessAllTasks)){
			$cartId = $this->gRequest->getCookieValue(M3_COOKIE_CART_ID);			// カートID
			$productClassInCart = self::$_ecObj->db->getProductClassInCart($cartId, $this->_langId);

			// 商品クラス表示を修正
			self::$_productClass = array();
			for ($i = 0; $i < count($productClassOrder); $i++){
				if (in_array($productClassOrder[$i], $productClassInCart)) self::$_productClass[] = $productClassOrder[$i];
			}

			// 全注文処理タスク
			self::$_orderProcessAllTasks = array(	'delivery',		// 配送先入力
													'delivmethod',	// 配送方法選択
													'payment',		// 支払い
													'confirm',		// 確認
													'complete');		// 手続き完了
													
			// 配送が必要な商品が入っているかチェック
			$this->_getCartItems('_calcCartLoop');
			if ($this->isErr){		// エラー発生のとき
				// カート内の商品が削除されていてエラーが発生している場合でも、注文プロセスへのアクセス制御のためをタスクを設定しておく必要がある
				self::$_orderProcessTasks = self::$_orderProcessAllTasks;
			} else {
				if ($this->_isExistsDefaultProduct || $this->_isExistsPhotoProduct){
					/*self::$_orderProcessTasks = array(	'delivery',		// 配送先入力
													'delivmethod',	// 配送方法選択
													'payment',		// 支払い
													'confirm',		// 確認
													'complete');		// 手続き完了
													*/
					self::$_orderProcessTasks = self::$_orderProcessAllTasks;
				} else {
					self::$_orderProcessTasks = array(	'payment',		// 支払い
													'confirm',		// 確認
													'complete');		// 手続き完了
				}
			}
		/*
			if (in_array(ec_mainCommonDef::PRODUCT_CLASS_DEFAULT, self::$_productClass)){		// 一般商品を含むとき
				self::$_orderProcessTasks = array(	'delivery',		// 配送先入力
												'delivmethod',	// 配送方法選択
												'payment',		// 支払い
												'confirm',		// 確認
												'complete');		// 手続き完了
			} else {
				self::$_orderProcessTasks = array(	'payment',		// 支払い
												'confirm',		// 確認
												'complete');		// 手続き完了
			}*/
		}
	}
	/**
	 * テンプレートにデータ埋め込む
	 *
	 * _setTemplate()で指定したテンプレートファイルにデータを埋め込む。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。_setTemplate()と共有。
	 * @return								なし
	 */
	function _postAssign($request, &$param)
	{
	}
	/**
	 * CSSファイルをHTMLヘッダ部に設定
	 *
	 * CSSファイルをHTMLのheadタグ内に追加出力する。
	 * _assign()よりも後に実行される。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。
	 * @return string 						CSS文字列。出力しない場合は空文字列を設定。
	 */
	function _addCssFileToHead($request, &$param)
	{
		return $this->getUrl($this->gEnv->getCurrentWidgetCssUrl() . self::CSS_FILE);
	}
	/**
	 * 定義値を取得
	 *
	 * @param string $key		定義キー
	 * @param string $default	デフォルト値
	 * @return string			値
	 */
	function _getConfig($key, $default = '')
	{
		$value = self::$_configArray[$key];
		if (!isset($value)) $value = $default;
		return $value;
	}
	/**
	 * 注文処理の次のタスクを取得
	 *
	 * @param int $direction	1=次のタスク、-1=前のタスク
	 * @param string $task		現在のタスク
	 * @return string			次のタスク。タスクがないときは空文字列
	 */
	function _getOrderNextTask($direction = 1, $task = '')
	{
		if (empty($task)) $task = self::$_task;			// 現在のタスク
		
		switch ($direction){
			case 1:		// 次のタスクの場合
				$ret = array_search($task, self::$_orderProcessTasks);
				if ($ret !== false){
					if ($ret < count(self::$_orderProcessTasks) -1){
						return self::$_orderProcessTasks[$ret +1];
					} else {
						return '';
					}
				} else {
					return '';
				}
				break;
			case -1:
				$ret = array_search($task, self::$_orderProcessTasks);
				if ($ret !== false){
					if (0 < $ret && $ret < count(self::$_orderProcessTasks)){
						return self::$_orderProcessTasks[$ret -1];
					} else {
						// 注文処理からの戻りタスクの場合
						return self::DEFAULT_ORDER_BACK_TASK;
						//return '';
					}
				} else {
					return '';
				}
				break;
		}
		return '';
	}
	/**
	 * クライアントIDを取得
	 *
	 * @return string			クライアントID
	 */
	function _getClientId()
	{
		$cid = '';
		if (empty($this->_userId)) $cid = $this->gAccess->getClientId();			// 非会員の購入の場合はブラウザのクライアントIDを取得
		return $cid;
	}
	/**
	 * 注文書を取得
	 *
	 * @param array $row		注文レコード
	 * @return bool				true=取得成功、false=取得失敗
	 */
	function _getOrderSheet(&$row)
	{
		if (empty($this->_userId)){			// 非会員の購入の場合
			$cid = $this->gAccess->getClientId();			// ブラウザのクライアントIDを取得
			$ret = self::$_orderDb->getOrderSheetByClientId($cid, $row);		// クライアントIDで注文情報を取得
			if (!$ret) self::$_orderDb->delOrderSheetByClientId($cid);		// エラーのときはエラーデータを削除
		} else {
			$ret = self::$_orderDb->getOrderSheet($this->_userId, $this->_langId, $row);
		}
		return $ret;
	}
	/**
	 * 注文書を削除
	 *
	 * @return bool				true=取得成功、false=取得失敗
	 */
	function _delOrderSheet()
	{
		if (empty($this->_userId)){			// 非会員の購入の場合
			$ret = self::$_orderDb->delOrderSheetByClientId($this->gAccess->getClientId());
		} else {
			$ret = self::$_orderDb->delOrderSheet($this->_userId, $this->_langId);
		}
		return $ret;
	}
	/**
	 * 注文情報を初期化
	 *
	 * @return bool				true=正常終了、false=異常終了
	 */
	function _initOrderSheet()
	{
		global $gRequestManager;
		
		$cartId = $gRequestManager->getCookieValue(M3_COOKIE_CART_ID);			// カートID
		
		if ($this->gEnv->isSystemAdmin()){		// システム管理者の場合(テスト用)
			$cid = '';			// ブラウザのクライアントIDは使用しない
			$custm_id = 0;
			
			// 顧客は購入者の情報
			$custm_name = 'システム管理者(テスト用)';
			$custm_name_kana = '';
			$custm_person = '';
			$custm_person_kana = '';
			$custm_zipcode = '';
			$custm_state_id = 0;
			$custm_address = '';
			$custm_address2 = '';
			$custm_phone = '';
			$custm_fax = '';
			$custm_email = '';
			$custm_country_id = '';
		} else if (empty($this->_userId)){			// 非会員の購入の場合
			$cid = $this->gAccess->getClientId();			// ブラウザのクライアントIDを取得
//			$ret = self::$_orderDb->getOrderSheetByClientId($cid, $row);
//			if ($ret){
				// 顧客は購入者の情報
				$custm_id = 0;
				$custm_name = '';
				$custm_name_kana = '';
				$custm_person = '';
				$custm_person_kana = '';
				$custm_zipcode = '';
				$custm_state_id = 0;
				$custm_address = '';
				$custm_address2 = '';
				$custm_phone = '';
				$custm_fax = '';
				$custm_email = '';
				$custm_country_id = '';
/*			} else {
				self::$_orderDb->delOrderSheetByClientId($cid);		// エラーのときはエラーデータを削除
				return false;
			}*/
		} else {		// 会員の購入の場合
			// 購入者の情報を取得
			$ret = self::$_orderDb->getMemberInfo($this->_userId, $memberInfo, $personInfo, $companyInfo, $addressRow);
			if ($ret){
				// 現在のログインユーザを購入者とする。購入者のID(会員ID)を取得
				$custm_id = 0;
				$ret = self::$_orderDb->getMember($this->_userId, $memberRow);
				if ($ret) $custm_id = $memberRow['sm_id'] * (-1);		// 会員IDに「-」を付けて格納

				// 顧客は購入者の情報
				$custm_name = $personInfo['pi_family_name'] . $personInfo['pi_first_name'];
				$custm_name_kana = $personInfo['pi_family_name_kana'] . $personInfo['pi_first_name_kana'];
				$custm_person = '';
				$custm_person_kana = '';
				$custm_zipcode = $addressRow['ad_zipcode'];
				$custm_state_id = $addressRow['ad_state_id'];
				$custm_address = $addressRow['ad_address1'];
				$custm_address2 = $addressRow['ad_address2'];
				$custm_phone = $addressRow['ad_phone'];
				$custm_fax = $addressRow['ad_fax'];
				$custm_email = $personInfo['pi_email'];
				$custm_country_id = $addressRow['ad_country_id'];
			
				$cid = '';			// ブラウザのクライアントIDは使用しない
			} else {
				$this->setAppErrorMsg('ログイン中のユーザは会員登録されていません。会員登録が必要です。');
				return false;
			}
		}
		
		// 請求先を購入者の情報で初期化
		$bill_id = $custm_id;
		$bill_name = $custm_name;
		$bill_name_kana = $custm_name_kana;
		$bill_person = $custm_person;
		$bill_person_kana = $custm_person_kana;
		$bill_zipcode = $custm_zipcode;
		$bill_state_id = $custm_state_id;
		$bill_address = $custm_address;
		$bill_address2 = $custm_address2;
		$bill_phone = $custm_phone;
		$bill_fax = $custm_fax;
		$bill_email = $custm_email;
		$bill_country_id = $custm_country_id;

		// 配送先を初期化
		$deliv_id = 0;
		$deliv_name = '';
		$deliv_name_kana = '';
		$deliv_person = '';
		$deliv_person_kana = '';
		$deliv_zipcode = '';
		$deliv_state_id = 0;
		$deliv_address = '';
		$deliv_address2 = '';
		$deliv_phone = '';
		$deliv_fax = '';
		$deliv_email = '';
		$deliv_country_id = '';
	
		// 配送方法、支払方法を初期化
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
		$currency_id = ec_mainCommonDef::DEFAULT_CURRENCY;		// デフォルト通貨
		$subtotal = 0;		// 商品合計
		$deliv_fee = 0;		// 配送料
		$charge = 0;		// 手数料
		$discount = 0;		// 値引き額
		$total = 0;		// 総支払額
		
		// カート内の商品合計を取得
		$ret = $this->getTotalPrice($price, $count);
		if ($ret){
			$subtotal = $price;
		} else {
			$this->setAppErrorMsg('カートの内容を確認してください。');
//			return false;
		}
		
		// 注文書初期化
		$ret = self::$_orderDb->updateOrderSheet($this->_userId, $this->_langId, $cid,
				$custm_id, $custm_name, $custm_name_kana, $custm_person, $custm_person_kana, $custm_zipcode, $custm_state_id, $custm_address, $custm_address2, $custm_phone, $custm_fax, $custm_email, $custm_country_id, 
				$deliv_id, $deliv_name, $deliv_name_kana, $deliv_person, $deliv_person_kana, $deliv_zipcode, $deliv_state_id, $deliv_address, $deliv_address2, $deliv_phone, $deliv_fax, $deliv_email, $deliv_country_id,
				$bill_id,  $bill_name,  $bill_name_kana,  $bill_person,  $bill_person_kana,  $bill_zipcode,  $bill_state_id,  $bill_address, $bill_address2,  $bill_phone,  $bill_fax, $bill_email, $bill_country_id,
				$deliv_method_id, $pay_method_id, $card_type, $card_owner, $card_number, $card_expires, $demand_dt, $demand_time, $appoint_dt, $currency_id, $subtotal, $discount, $deliv_fee, $charge, $total);
		return $ret;
	}
	/**
	 * 商品の総額を取得
	 *
	 * @param float  $price		総額
	 * @param int    $count		総数
	 * @return bool				true=正常、false=異常
	 */
	public function getTotalPrice(&$price, &$count)
	{
		$this->_productTotal = 0;		// 商品合計額
		$this->_productCount = 0;		// 商品総数
		$this->isErr = false;
		
		$this->_getCartItems('_calcCartLoop');
		if ($this->isErr){		// エラー発生のとき
			return false;
		} else {
			$price = $this->_productTotal;
			$count = $this->_productCount;
			return true;
		}
	}
	/**
	 * 商品の値引き額、追加料を取得
	 *
	 * @param array  $priceArray		値引き額、割り増し額
	 * @param array  $titleArray		値引き額、割り増し額のタイトル
	 * @return bool						true=正常、false=異常
	 */
	public function getExtraPrice(&$priceArray, &$titleArray)
	{
		$cartId = $this->gRequest->getCookieValue(M3_COOKIE_CART_ID);			// カートID
		$priceArray = array();
		$titleArray = array();
		$descArray = array();		// 説明
		
		$ret = self::$_orderDb->getAllIWidgetMethod(ec_mainCommonDef::IWIDGET_METHOD_CALC_ORDER, $this->_langId, $rows);
		for ($i = 0; $i < count($rows); $i++){
			$iWidgetId	= $rows[$i]['id_iwidget_id'];	// インナーウィジェットID
			if (!empty($iWidgetId)){
				// パラメータをインナーウィジェットに設定し、計算結果を取得
				$optionParam = new stdClass;
				$optionParam->id = $rows[$i]['id_id'];
				$optionParam->init = true;		// 初期データ取得
				$optionParam->userId = $this->_userId;					// ログインユーザID
				$optionParam->languageId = $this->_langId;		// 言語ID
				$optionParam->cartId = $cartId;					// 商品のカート
				if ($this->calcIWidgetParam($iWidgetId, $rows[$i]['id_id'], $rows[$i]['id_param'], $optionParam, $resultObj)){
					if (isset($resultObj->price)){
						$priceArray[] = $resultObj->price;		// 価格
						$titleArray[] = $rows[$i]['id_name'];	// タイトル
						$descArray[] = $rows[$i]['id_desc_short'];		// 簡易説明
					}
				}
			}
		}
		if (count($priceArray) > 0){
			return true;
		} else {
			return false;
		}
	}
	/**
	 * カート内の商品を取得
	 *
	 * @param string $loopMethod		ループ処理メソッド名
	 * @return 							なし
	 */
	public function _getCartItems($loopMethod)
	{
		$cartId = $this->gRequest->getCookieValue(M3_COOKIE_CART_ID);			// カートID
		
		for ($i = 0; $i < count(self::$_productClass); $i++){
			self::$_ecObj->db->getCartItems($cartId, $this->_langId, self::$_productClass[$i], array($this, $loopMethod));
			if ($this->isErr) break;		// エラー発生時は終了
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
			case ec_mainCommonDef::PRODUCT_CLASS_PHOTO:		// フォトギャラリー画像のとき
				$photoId = $fetchedRow['ht_public_id'];		// 公開画像ID
				$title = $fetchedRow['ht_name'];		// サムネール画像タイトル
				//$productTypeName = $fetchedRow['py_name'];		// 商品タイプ名
				//$productTypeCode = $fetchedRow['py_code'];		// 商品タイプコード
				if ($productType == ec_mainCommonDef::PRODUCT_TYPE_DOWNLOAD){		// ダウンロード商品の場合
					$productTypeName = $fetchedRow['py_name'];		// 商品タイプ名
					$productTypeCode = $fetchedRow['py_code'];		// 商品タイプコード
				} else {							// フォト関連商品の場合
					// 商品内容
					$this->_isExistsPhotoProduct = true;		// フォト関連商品が含まれているかどうか
				
					$productTypeName = $fetchedRow['hp_name'];		// 商品タイプ名
					$productTypeCode = $fetchedRow['hp_code'];		// 商品タイプコード
				}

				// 表示用の商品名、商品コード作成
				$productName = sprintf(ec_mainCommonDef::PRODUCT_NAME_FORMAT, $productTypeName, $title);		// 商品名
				$productCode = sprintf(ec_mainCommonDef::PRODUCT_CODE_FORMAT, $photoId, $productTypeCode);		// 商品コード
				
				// 商品の状態
				if (!$fetchedRow['ht_visible']) $priceAvailable = false;		// 商品が表示不可のときは価格を無効とする
				
				// 画像価格情報を取得
				$ret = self::$_mainDb->getPhotoInfoWithPrice($productId, $productClass, $productType, ec_mainCommonDef::REGULAR_PRICE, $this->_langId, $fetchedRow['cu_id'], $row);
				break;
			case ec_mainCommonDef::PRODUCT_CLASS_DEFAULT:	// 一般商品のとき
				// 商品内容
				$this->_isExistsDefaultProduct = true;		// 一般商品が含まれているかどうか
				
				// 表示用の商品名、商品コード作成
				$productName = $fetchedRow['pt_name'];		// 商品名
				$productCode = $fetchedRow['pt_code'];		// 商品コード
				
				// 商品の状態
				if (!$fetchedRow['pt_visible']) $priceAvailable = false;		// 商品が表示不可のときは価格を無効とする
				
				// 商品価格情報を取得
				$ret = self::$_mainDb->getProductByProductId($productId, $this->_langId, $fetchedRow['cu_id'], $row, $imageRows);
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
			
			// 受注明細を登録
			$ret = true;
			if ($this->_addToOrder){
				$tax = $subtotal - $price * $quantity;
				$ret = self::$_orderDb->addOrderDetail($this->_orderId, $itemIndex, $productClass, $productId, $productType,
													$productName, $productCode, $price, $quantity, $tax, $subtotal, $this->_userId, $this->_now);
													
				// ##### 在庫数を更新 #####
				if ($productClass == ec_mainCommonDef::PRODUCT_CLASS_DEFAULT){	// 一般商品のとき
					if ($this->_getConfig(ec_mainCommonDef::CF_E_AUTO_STOCK)){
						$newStockCount = intval($row['pe_stock_count']) - $quantity;
						if ($newStockCount < 0) $newStockCount = 0;
						$updateParam = array('pe_stock_count' => $newStockCount);
						self::$_orderDb->updateProductRecord($productId, $this->_langId, $updateParam);
					}
				}
			}
			if ($ret){
				$itemIndex++;
				return true;
			}
		}
		$this->isErr = true;			// エラーステータス
		return false;		// 処理中断
	}
	/**
	 * 取得したデータをテンプレートに設定する
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function _defaultCartLoop($index, $fetchedRow, $param)
	{
		static $itemIndex = 0;
		
		$priceAvailable = true;	// 価格が有効であるかどうか
		if ($this->_useOrderDetail){		// カート内容でなく、受注内容を取得の場合
			$productClass = $fetchedRow['od_product_class'];		// 商品クラス
			$productType = $fetchedRow['od_product_type_id'];		// 商品タイプ
			$productId = $fetchedRow['od_product_id'];				// 商品ID
		} else {	// カート内容の場合
			$productClass = $fetchedRow['si_product_class'];		// 商品クラス
			$productType = $fetchedRow['si_product_type_id'];		// 商品タイプ
			$productId = $fetchedRow['si_product_id'];				// 商品ID
		}
		$prePrice = $this->convertToDispString($fetchedRow['cu_symbol']);		// 価格表示用
		$postPrice = $this->convertToDispString($fetchedRow['cu_post_symbol']);	// 価格表示用

		switch ($productClass){
			case ec_mainCommonDef::PRODUCT_CLASS_PHOTO:		// フォトギャラリー画像のとき
				$photoId = $fetchedRow['ht_public_id'];		// 公開画像ID
				$title = $fetchedRow['ht_name'];		// サムネール画像タイトル
				$checkValue = $photoId;					// 項目チェック値
				if ($productType == ec_mainCommonDef::PRODUCT_TYPE_DOWNLOAD){		// ダウンロード商品の場合
					$productTypeName = $fetchedRow['py_name'];		// 商品タイプ名
					$productTypeCode = $fetchedRow['py_code'];		// 商品タイプコード
				} else {							// フォト関連商品の場合
					$productTypeName = $fetchedRow['hp_name'];		// 商品タイプ名
					$productTypeCode = $fetchedRow['hp_code'];		// 商品タイプコード
				}

				// 表示用の商品名、商品コード作成
				if ($this->_useOrderDetail){		// カート内容でなく、受注内容を取得の場合
					$productName = $fetchedRow['od_product_name'];		// 商品名
					$productCode = $fetchedRow['od_product_code'];		// 商品コード
				} else {
					$productName = sprintf(ec_mainCommonDef::PRODUCT_NAME_FORMAT, $productTypeName, $title);		// 商品名
					$productCode = sprintf(ec_mainCommonDef::PRODUCT_CODE_FORMAT, $photoId, $productTypeCode);		// 商品コード
				}
				
				// 商品の状態
				if (!$fetchedRow['ht_visible']) $priceAvailable = false;		// 商品が表示不可のときは価格を無効とする
				
				// 画像価格情報を取得
				$ret = self::$_mainDb->getPhotoInfoWithPrice($productId, $productClass, $productType, ec_mainCommonDef::REGULAR_PRICE, $this->_langId, $fetchedRow['cu_id'], $row);
				
				// 画像詳細へのリンク
				$url = $this->gEnv->getDefaultUrl() . '?' . M3_REQUEST_PARAM_PHOTO_ID . '=' . $photoId;
		
				// 画像URL
				$imageUrl = $this->gEnv->getResourceUrl() . ec_mainCommonDef::THUMBNAIL_DIR . '/' . $photoId . '_' . ec_mainCommonDef::DEFAULT_THUMBNAIL_SIZE . '.' . ec_mainCommonDef::DEFAULT_IMAGE_EXT;
				$imageWidth = ec_mainCommonDef::CART_ICON_SIZE;
				$imageHeight = ec_mainCommonDef::CART_ICON_SIZE;
				break;
			case ec_mainCommonDef::PRODUCT_CLASS_DEFAULT:	// 一般商品のとき
				$title = $fetchedRow['pt_name'];		// サムネール画像タイトル
				$checkValue = $productId;					// 項目チェック値
				
				// 表示用の商品名、商品コード作成
				if ($this->_useOrderDetail){		// カート内容でなく、受注内容を取得の場合
					$productName = $fetchedRow['od_product_name'];		// 商品名
					$productCode = $fetchedRow['od_product_code'];		// 商品コード
				} else {
					$productName = $fetchedRow['pt_name'];		// 商品名
					$productCode = $fetchedRow['pt_code'];		// 商品コード
				}
				
				// 商品の状態
				if (!$fetchedRow['pt_visible']) $priceAvailable = false;		// 商品が表示不可のときは価格を無効とする
				
				// 商品価格情報を取得
				$ret = self::$_mainDb->getProductByProductId($productId, $this->_langId, $fetchedRow['cu_id'], $row, $imageRows);
				
				// 商品詳細へのリンク
				$url = $this->gEnv->getDefaultUrl() . '?' . M3_REQUEST_PARAM_PRODUCT_ID . '=' . $productId;

				// 画像URL
				$imageArray = $this->_getImage($imageRows, ec_mainCommonDef::PRODUCT_IMAGE_SMALL);// 商品画像小
				$imageUrl = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $this->gEnv->getRootUrl(), $imageArray['im_url']);
				$imagePath = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $this->gEnv->getSystemRootPath(), $imageArray['im_url']);
				if (!file_exists($imagePath)){
					$imageUrl = $this->_getProductImageUrl('0_' . $this->_productImageWidth . 'x' . $this->_productImageHeight . self::DEFAULT_PRODUCT_IMAGE_TYPE);
				}
				$imageWidth = $this->_productImageWidth;
				$imageHeight = $this->_productImageHeight;
				
				// ##### 在庫自動処理 #####
				if (!$this->_createEmailData/*Eメール出力でない場合*/ && $this->_getConfig(ec_mainCommonDef::CF_E_AUTO_STOCK)){
					if (!$this->_useOrderDetail){		// カート内容を取得の場合
						// カートの購入数と在庫数を比較し、在庫数が少ない場合はメッセージ出力
						$stockCount = intval($row['pe_stock_count']);
						if ($fetchedRow['si_quantity'] > $stockCount){
							$msg = '「'. $productName . '」の在庫が不足しています。在庫数(' . $stockCount . ')';
							$this->setAppErrorMsg($msg);
						}
					}
				}
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
		
		// ##### カートの内容のチェック #####
		// 価格が変更のときは、価格を無効にする
		if ($this->_useOrderDetail){		// カート内容でなく、受注内容を取得
			$quantity = $fetchedRow['od_quantity'];
			$subtotal = $fetchedRow['od_total'];
		} else {
			$quantity = $fetchedRow['si_quantity'];
			$subtotal = $fetchedRow['si_subtotal'];
		}
		$oldCurrency = $fetchedRow['cu_id'];
		if ($unitPrice * $quantity != $subtotal) $priceAvailable = false;
		if ($oldCurrency != $currency) $priceAvailable = false;

		// 価格の有効判断
		if (!$fetchedRow['si_available']) $priceAvailable = false;
		
		// 小計価格作成
		self::$_ecObj->setCurrencyType($oldCurrency, $this->_langId);		// 通貨設定
		self::$_ecObj->getPriceWithoutTax($subtotal, $dispPrice);				// 税込み価格取得

		// 小計価格表示文字列
		$priceStatus = '';
		if (!$priceAvailable) $priceStatus = '<span style="color:#ff0000;">(無効)</span>';
		$dispPrice = $prePrice . $dispPrice . $postPrice;
		
		// 合計価格は、有効な価格のみ合計に加える
		if ($priceAvailable) $this->_total += $subtotal;					// 合計価格
		
		// 商品詳細へのリンク
		$urlLink = $this->convertUrlToHtmlEntity($this->getUrl($url, true));
		$nameLink = '<a href="' . $urlLink . '">' . $this->convertToDispString($productName) . '</a>';
		
		// サムネール
		$photoImage = '<a href="' . $urlLink . '"><img src="' . $this->getUrl($imageUrl) . '" width="' . $imageWidth . '" height="' . $imageHeight . 
								'" title="' . $this->convertToDispString($title) . '" alt="' . $this->convertToDispString($title) . '" style="border:none;" /></a>';
		// 数量変更可否
		$quantityDisabled = '';
		if ($fetchedRow['py_single_select']){
			$quantityDisabled = 'readonly';
		} else {
			$this->_canUpdateCart = true;			// カートが更新可能かどうか
		}
		
		//if (empty($this->_updateContentAccess)){		// コンテンツアクセス権の設定でないとき
		if (empty($this->_checkCart) && empty($this->_updateContentAccess)){		// カート内容のエラーチェックでない、コンテンツアクセス権の設定でないとき
			$row = array(
				'index'	=> $itemIndex,
				'product_class' => $productClass,		// 商品クラス
				'class_index'	=> $index,
				'name' => $nameLink,
				'code' => $this->convertToDispString($productCode),		// 商品コード
				'check_value' => $checkValue,		// チェック値
				'image' => $photoImage,			// サムネール
				'unit_price' => $dispUnitPrice,			// 税込み単価
				'price' => $dispPrice,					// 小計
				'price_status' => $priceStatus,			// 小計の状態
				'quantity' => $quantity,
				'quantity_disabled' => $quantityDisabled
			);
			$this->tmpl->addVars('cartlist', $row);
			$this->tmpl->parseTemplate('cartlist', 'a');
		
			// ##### メール送信用の受注データを作成 #####
			if ($this->_createEmailData){	// メール送信用の受注データを作成するかどうか
				$delim = $this->gEnv->getDefaultCsvDelimCode();		// CSV区切りコードを取得
				$mailData = $productCode . $delim . $productName . $delim . self::$_ecObj->getCurrencyPrice($unitPrice) . $delim . $quantity . M3_NL;
				$this->_emailData .= $mailData;			// メール送信用の受注データ
				$this->_orderText .= ($itemIndex + 1) . ' ' . $productName . ' ' . $productCode . ' ' . 
									//$prePrice . self::$_ecObj->getCurrencyPrice($unitPrice) . $postPrice . ' 数量' . $quantity . M3_NL;			// 受注内容
									$prePrice . self::$_ecObj->convertByCurrencyFormat($currency, $this->_langId, $unitPrice) . $postPrice . ' 数量' . $quantity . M3_NL;			// 受注内容
			}
		
			$itemIndex++;
			$this->_productExists = true;			// カートに商品があるかどうか
		} else {		// コンテンツアクセス権の設定の場合(コンテンツIDを取得)
			// 指定のコンテンツIDを取得
			if ($productClass == $this->_selectProductClass && $productType == $this->_selectProductType){
				$this->_contentIdArray[] = $productId;		// コンテンツID
			}
		}
		return true;
	}
	/**
	 * 画像取得
	 *
	 * @param array  	$srcRows			画像リスト
	 * @param string	$imageType			画像タイプ
	 * @return array						取得した行
	 */
	function _getImage($srcRows, $sizeType)
	{
		for ($i = 0; $i < count($srcRows); $i++){
			if ($srcRows[$i]['im_size_id'] == $sizeType){
				return $srcRows[$i];
			}
		}
		return array();
	}
	/**
	 * 商品画像URL取得
	 *
	 * @param string $filename		ファイル名
	 * @return string				URL
	 */
	function _getProductImageUrl($filename)
	{
		return $this->gEnv->getResourceUrl() . self::PRODUCT_IMAGE_DIR . $filename;
	}
}
?>
