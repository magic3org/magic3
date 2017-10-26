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
require_once($gEnvManager->getContainerPath() . '/baseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() .	'/ec_dispDb.php');
require_once($gEnvManager->getCommonPath() . '/valueCheck.php');

class ec_dispProductWidgetContainer extends BaseWidgetContainer
{
	private $db;			// DB接続オブジェクト
	private $langId;		// 現在の言語ID
	private $currencyId;	// 現在の通貨ID
	private $viewStyle;		// 表示スタイル
	private $categoryId;		// カテゴリーID
	private $productId;			// 製品ID
	private $defaultImageSWidth;		// 画像小幅
	private $defaultImageSHeight;		// 画像小高さ
	private $defaultImageMWidth;		// 画像中幅
	private $defaultImageMHeight;		// 画像中高さ
	private $productExists;				// 商品が存在するかどうか
	private $subcategoryExists;				// サブカテゴリーが存在するかどうか
	private $currentPageUrl;			// 現在のページURL
	private $ecObj;					// EC共通ライブラリオブジェクト
	private $showStock;				// 在庫表示するかどうか
	private $contentNoStock;		// 在庫なし時コンテンツ
	private $headTitle;		// METAタグタイトル
	private $headDesc;		// METAタグ要約
	private $headKeyword;	// METAタグキーワード
	private $categoryTitleSeparator;	// カテゴリータイトル作成用セパレータ
	const PRICE_OBJ_ID = "eclib";		// 価格計算オブジェクトID
	const REGULAR_PRICE = 'regular';		// 通常価格
	const PRODUCT_IMAGE_MEDIUM = 'standard-product';		// 中サイズ商品画像ID
	const PRODUCT_IMAGE_SMALL = 'small-product';		// 小サイズ商品画像ID
	const PRODUCT_IMAGE_LARGE = 'large-product';		// 大サイズ商品画像ID
	const PRODUCT_STATUS_NEW = 'new';		// 商品ステータス新規
	const PRODUCT_STATUS_SUGGEST = 'suggest';		// 商品ステータスおすすめ
	const DEFAULT_TAX_TYPE = 'sales';			// デフォルト税種別
	const VIEW_STYLE_SMALL = 'small';			// 表示スタイル(小)
	const VIEW_STYLE_MEDIUM = 'medium';			// 表示スタイル(中)
	const VIEW_STYLE_LARGE = 'large';			// 表示スタイル(大)
	const PRODUCT_CLASS_DEFAULT = '';			// 商品クラス(一般商品)
	const PRODUCT_TYPE_DEFAULT = '';		// 商品タイプデフォルト
	const DEFAULT_PRODUCT_COUNT = 10;				// デファオルとの表示項目数
	const DEFAULT_TARGET_WIDGET = 'ec_main';		// 呼び出しウィジェットID
	const DEFAULT_STOCK_VIEW_FORMAT = '0:なし;3:残り僅か($1);:あり($1)';
	const DEFAULT_PRODUCT_IMAGE_TYPE = 'c.jpg';			// 商品画像ファイルのタイプ
	const PRODUCT_IMAGE_DIR = '/widgets/product/image/';				// 商品画像格納ディレクトリ
	const MAX_CATEGORY_LEVEL = 5;			// カテゴリー階層最大数
	const DEFAULT_LIST_TITLE = '商品一覧';		// デフォルト一覧タイトル
	const DEFAULT_SEARCH_LIST_TITLE = '検索結果';		// デフォルト検索結果商品一覧タイトル
	const DEFAULT_CATEGORY_LIST_TITLE = '「$1」の商品一覧';		// カテゴリー表示時タイトル
	const DEFAULT_CATEGORY_TITLE_SEPARATOR = '-';		// デフォルトのカテゴリータイトル作成用セパレータ
	const LINK_PAGE_COUNT		= 5;			// リンクページ数
	//const DEFAULT_TITLE_SEPARATOR = '-';		// デフォルトのタイトル作成用セパレータ
	// Eコマース設定DB値
	const CF_E_HIERARCHICAL_CATEGORY	= 'hierarchical_category';	// 階層化商品カテゴリー
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->db = new ec_dispDb();
		
		// 価格計算用オブジェクト取得
		$this->ecObj = $this->gInstance->getObject(self::PRICE_OBJ_ID);
		
		$this->currencyId = $this->ecObj->getDefaultCurrency();		// 現在の通貨ID
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
		$act = $request->trimValueOf('act');
		$this->productId = $request->trimValueOf(M3_REQUEST_PARAM_PRODUCT_ID);	// 製品ID
		if (empty($this->productId)) $this->productId = $request->trimValueOf(M3_REQUEST_PARAM_PRODUCT_SHORT);		// 略式コンテンツID
		
		if ($act == 'inputbasketbylist'){		// 商品リストからの選択の場合
			return 'main.tmpl.html';
		} else {
			if ($act == 'search' || empty($this->productId)){		// 商品一覧のとき
				return 'main.tmpl.html';
			} else {			// 商品詳細表示
				return 'main_detail.tmpl.html';
			}
		}
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
		// 現在日時を取得
		$this->currentDay = date("Y/m/d");		// 日
		$this->currentHour = (int)date("H");		// 時間
		$this->langId	= $this->gEnv->getCurrentLanguage();		// 表示言語を取得
		$this->currentPageUrl = $this->gEnv->createCurrentPageUrl();// 現在のページURL
		
		// 保存値取得
		$categoryListTitle = self::DEFAULT_CATEGORY_LIST_TITLE;	// カテゴリー表示時タイトル
		$this->categoryTitleSeparator = self::DEFAULT_CATEGORY_TITLE_SEPARATOR;	// カテゴリータイトル作成用セパレータ
		$productCount = self::DEFAULT_PRODUCT_COUNT;						// 商品表示数
		$targetWidget = self::DEFAULT_TARGET_WIDGET;		// カート表示ウィジェット
		$this->showStock		= 0;				// 在庫表示するかどうか
		$stockViewFormat	= self::DEFAULT_STOCK_VIEW_FORMAT;	// 在庫表示フォーマット
		$this->contentNoStock = '';								// 在庫なし時コンテンツ
		$paramObj = $this->getWidgetParamObj();
		if (!empty($paramObj)){
			if (!empty($paramObj->categoryListTitle)) $categoryListTitle = $paramObj->categoryListTitle;	// カテゴリー表示時タイトル
			if (!empty($paramObj->categoryTitleSeparator)) $this->categoryTitleSeparator = $paramObj->categoryTitleSeparator;	// カテゴリータイトル作成用セパレータ
			$productCount = $paramObj->productCount;		// 商品表示数
			$targetWidget = $paramObj->targetWidget;		// カート表示ウィジェット
			$this->showStock		= $paramObj->showStock;				// 在庫表示するかどうか
			$stockViewFormat	= $paramObj->stockViewFormat;	// 在庫表示フォーマット
			$this->contentNoStock		= $paramObj->contentNoStock;	// 在庫なし時コンテンツ
		}

		// 表示モードを取得
		$this->viewStyle = $request->trimValueOf(M3_REQUEST_PARAM_VIEW_STYLE);		// 表示モード
		if (empty($this->viewStyle)) $this->viewStyle = self::VIEW_STYLE_MEDIUM;
		
		// 画像情報を取得
		$this->defaultImageSWidth = 0;
		$this->defaultImageSHeight = 0;
		$ret = $this->db->getProductImageInfo(self::PRODUCT_IMAGE_SMALL, $row);
		if ($ret){
			$this->defaultImageSWidth = $row['is_width'];
			$this->defaultImageSHeight = $row['is_height'];
		}
		$this->defaultImageMWidth = 0;
		$this->defaultImageMHeight = 0;
		$ret = $this->db->getProductImageInfo(self::PRODUCT_IMAGE_MEDIUM, $row);
		if ($ret){
			$this->defaultImageMWidth = $row['is_width'];
			$this->defaultImageMHeight = $row['is_height'];
		}
		$this->defaultImageLWidth = 0;
		$this->defaultImageLHeight = 0;
		$ret = $this->db->getProductImageInfo(self::PRODUCT_IMAGE_LARGE, $row);
		if ($ret){
			$this->defaultImageLWidth = $row['is_width'];
			$this->defaultImageLHeight = $row['is_height'];
		}
				
		// パラメータを取得
		// カテゴリーを取得
		$categoryId = $request->trimValueOf('category');	// カテゴリーID
		$this->categoryId = $categoryId;
		$categoryArray = explode(',', $categoryId);
		if (!ValueCheck::isNumeric($categoryArray)){
			$categoryId = '';		// すべて数値であるかチェック
			$categoryArray = array();
		}
		if (!empty($categoryArray) && $this->ecObj->getConfig(self::CF_E_HIERARCHICAL_CATEGORY)){			// サブカテゴリーを取得する場合
			$categoryArray = array_unique($this->getSubCategory($categoryArray));
			$categoryId = implode(',', $categoryArray);
		}
		
		$pageNo = $request->trimIntValueOf(M3_REQUEST_PARAM_PAGE_NO, '1');				// ページ番号
		$quantity = $request->trimValueOf('item_quantity');		// 数量
		if (empty($quantity)) $quantity = 1;
		
		// DBの保存設定値を取得
/*		$maxListCount = self::DEFAULT_LIST_COUNT;
		$count = intval($this->db->getConfig(self::DISP_PRODUCT_COUNT));						// 商品一覧の商品表示数
		if ($count > 0) $maxListCount = $count;*/
		
		$act = $request->trimValueOf('act');
		
		if ($act == 'search'){			// 検索
			$keyword = $request->trimValueOf('keyword');
		} else if ($act == 'inputbasket' || $act == 'inputbasketbylist'){			// カートに入れる処理の場合
			// クッキー読み込み、カートIDを取得
			$cartId = $request->getCookieValue(M3_COOKIE_CART_ID);
			if (empty($cartId)){	// カートIDが設定されていないとき
				// カートIDを生成
				$cartId = $this->ecObj->createCartId();
			}
			$request->setCookieValue(M3_COOKIE_CART_ID, $cartId);

			// ####### カートに商品を追加 ########
			// 商品名と価格を取得
			if (!empty($this->productId)){
				// カートの情報を取得
				$cartSerial = 0;		// カートシリアル番号
				$cartItemSerial = 0;	// カート商品シリアル番号
				$userId = $this->gEnv->getCurrentUserId();
				$ret = $this->ecObj->db->getCartHead($cartId, $this->langId, $row);
				if ($ret){
					$cartSerial = $row['sh_serial'];
					if ($userId == 0) $userId = $row['sh_id'];
				}
				
				// 商品情報を取得
				$isValidItem = true;		// 現在のカートのデータが有効かどうか
				$ret = $this->db->getProductByProductId($this->productId, $this->langId, $row, $row2, $row3, $row4, $row5);
				if ($ret){
					// 価格を取得
					$priceArray = $this->getPrice($row2, self::REGULAR_PRICE);
					$price = $priceArray['pp_price'];	// 価格
					$currency = $priceArray['pp_currency_id'];	// 通貨
					$taxType = $row['pt_tax_type_id'];					// 税種別			

					// 価格作成
					$this->ecObj->setCurrencyType($currency, $this->langId);		// 通貨設定
					$this->ecObj->setTaxType($taxType);		// 税種別設定
					$totalPrice = $this->ecObj->getPriceWithTax($price, $dispPrice);	// 税込み価格取得
				} else {		// 商品情報が取得できないときは、カートの商品をキャンセル
					$isValidItem = false;
				}
				// カートにある商品を取得
				if ($isValidItem && $cartSerial > 0){
					$ret = $this->ecObj->db->getCartItem($cartSerial, self::PRODUCT_CLASS_DEFAULT, $this->productId, self::PRODUCT_TYPE_DEFAULT, $cartItemRow);
					if ($ret){		// 取得できたときは価格をチェック
						if ($cartItemRow['si_available']){		// データが有効のとき
							// 価格が変更されているときはカートの商品を無効にする
							$cart_item_currency = $cartItemRow['si_currency_id'];
							$cart_item_quantity = $cartItemRow['si_quantity'];
							$cart_item_price = $cartItemRow['si_subtotal'];
							$cartItemSerial = $cartItemRow['si_serial'];
							
							if ($cart_item_currency != $currency) $isValidItem = false;		// 通貨が変更のときはレコードを無効化
							if ($totalPrice * $cart_item_quantity != $cart_item_price) $isValidItem = false;		// 価格が変更のときはレコードを無効化
						} else {
							$isValidItem = false;
						}
					}
				}
				// カート商品情報を更新
				if ($isValidItem){
					// カートに商品を追加
					$ret = $this->ecObj->db->addCartItem($cartSerial, $cartItemSerial, $cartId, self::PRODUCT_CLASS_DEFAULT, $this->productId, self::PRODUCT_TYPE_DEFAULT, $this->langId, 
														$currency, $totalPrice, $quantity);
				} else {
					// カート商品の無効化
					$ret = $ecLibObj->db->voidCartItem($cartSerial, $cartItemSerial);
				}
			}
			// 商品一覧からの選択の場合は製品IDをリセット
			if ($act == 'inputbasketbylist') $this->productId = 0;
			
			// カート画面を表示
			$cartPage = $this->createCmdUrlToWidget($targetWidget, 'task=cart');
			$this->gPage->redirect($cartPage);
			return;
		}
		// ####### 商品情報の表示 #######
		if (empty($this->productId)){		// 商品一覧表示のとき
			if ($this->showStock){				// 在庫表示する場合
				$this->tmpl->setAttribute('show_stock', 'visibility', 'visible');
				$this->contentNoStock = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $this->gEnv->getRootUrl(), $this->contentNoStock);// マクロ変換
			}
				
			// 検索からの一覧または、カテゴリー指定の一覧
			if ($act == 'search'){
				// キーワード分割
				$parsedKeywords = $this->gInstance->getTextConvManager()->parseSearchKeyword($keyword);
				
				// 検索キーワードを記録
				//$this->gInstance->getAnalyzeManager()->logSearchWord($this->gEnv->getCurrentWidgetId(), $keyword);
				for ($i = 0; $i < count($parsedKeywords); $i++){
					$this->gInstance->getAnalyzeManager()->logSearchWord($this->gEnv->getCurrentWidgetId(), $parsedKeywords[$i]);
				}
				$totalCount = $this->db->getProductCountByKeyword($parsedKeywords, $this->langId, 1);
				$this->calcPageLink($pageNo, $totalCount, $productCount);		// ページ番号修正

/*				$pageCount = (int)(($totalCount -1) / $productCount) + 1;		// 総ページ数
				
				// 表示するページ番号の修正
				if ($pageNo < 1) $pageNo = 1;
				if ($pageNo > $pageCount) $pageNo = $pageCount;*/
				
				// 商品リストを表示
				//$this->db->getProductByKeyword($keyword, $this->langId, $productCount, ($pageNo -1) * $productCount, array($this, 'productListLoop'));
				$this->db->getProductByKeyword($parsedKeywords, $this->langId, $productCount, ($pageNo -1) * $productCount, array($this, 'productListLoop'));

				// ページング用リンク作成
				$pageLink = $this->createPageLink($pageNo, self::LINK_PAGE_COUNT, $this->currentPageUrl . '&task=product&act=search&keyword=' . urlencode($keyword));
/*				$pageLink = '';
				if ($pageCount > 1){	// ページが2ページ以上のときリンクを作成
					for ($i = 1; $i <= $pageCount; $i++){
						$linkUrl = $this->currentPageUrl . '&task=product&act=search&keyword=' . urlencode($keyword) . '&page=' . $i;
						if ($i == $pageNo){
							$link = '&nbsp;' . $i;
						} else {
							$link = '&nbsp;<a href="' . $this->getUrl($linkUrl, true) . '" >' . $i . '</a>';
						}
						$pageLink .= $link;
					}
				}*/
				$this->tmpl->addVar("show_productlist", "page_link", $pageLink);
				
				// 商品があるかチェック
				$title = self::DEFAULT_SEARCH_LIST_TITLE;		// デフォルトの検索結果タイトル
				$this->headTitle = $title;		// METAタグタイトル
				if ($this->productExists){
					$this->tmpl->setAttribute('show_productlist', 'visibility', 'visible');
					$this->tmpl->addVar("show_productlist", "title", $this->convertToDispString($title));// タイトルを設定
					if (empty($keyword)) $keyword = 'なし';
					$this->tmpl->addVar("show_productlist", "detail", 'キーワード=' . $keyword . '&nbsp;&nbsp;' . $totalCount . '件');// 詳細
				} else {
					$this->tmpl->setAttribute('no_item_message', 'visibility', 'visible');
					$this->tmpl->addVar("no_item_message", "title", $this->convertToDispString($title));// タイトルを設定
					if (empty($keyword)) $keyword = 'なし';
					$this->tmpl->addVar("no_item_message", "detail", 'キーワード=' . $keyword . '&nbsp;&nbsp;' . $totalCount . '件');// 詳細
				}
			} else {
				// タイトルの取得
				$titleArray = array();
				$menuItems = $this->gEnv->getSelectedMenuItems();
				for ($i = 0; $i < count($menuItems); $i++){
					$titleArray[] = $menuItems[$i]->title;
				}
				//$title = $menuItem['title'];
				$title = implode($this->categoryTitleSeparator, $titleArray);
				if (empty($title)){
					$title = self::DEFAULT_LIST_TITLE;		// デフォルトのタイトル
				} else {
					$title = str_replace('$1', $title, $categoryListTitle);
				}
				
				// 指定カテゴリーの商品を取得
				if (!empty($categoryId)){
					// 総数を取得
					$totalCount = $this->db->getProductCountByCategoryId($categoryId, $this->langId);
					$this->calcPageLink($pageNo, $totalCount, $productCount);		// ページ番号修正
					//$pageCount = (int)(($totalCount -1) / $productCount) + 1;		// 総ページ数
			
		/*			// 表示するページ番号の修正
					if ($pageNo < 1) $pageNo = 1;
					if ($pageNo > $pageCount) $pageNo = $pageCount;*/

					// 商品リストを表示
					$this->db->getProductByCategoryId($categoryId, $this->langId, $productCount, ($pageNo -1) * $productCount, array($this, 'productListLoop'));

					// ページング用リンク作成
					$pageLink = $this->createPageLink($pageNo, self::LINK_PAGE_COUNT, $this->currentPageUrl . '&category=' . $this->categoryId);
/*					// ##### カテゴリーはアクセス時のパラメータを使用 #####
					$pageLink = '';
					if ($pageCount > 1){	// ページが2ページ以上のときリンクを作成
						for ($i = 1; $i <= $pageCount; $i++){
							$linkUrl = $this->currentPageUrl . '&category=' . $this->categoryId . '&page=' . $i;
							if ($i == $pageNo){
								$link = '&nbsp;' . $i;
							} else {
								$link = '&nbsp;<a href="' . $this->getUrl($linkUrl, true) . '" >' . $i . '</a>';
							}
							$pageLink .= $link;
						}
					}*/
					$this->tmpl->addVar("show_productlist", "page_link", $pageLink);
				}
		
				// サブカテゴリーと商品リストの表示制御
				// サブカテゴリーがあるかチェック
				if ($this->subcategoryExists) $this->tmpl->setAttribute('show_subcategory', 'visibility', 'visible');
				// 商品があるかチェック
				if ($this->productExists) $this->tmpl->setAttribute('show_productlist', 'visibility', 'visible');
				// 登録項目がないとき
				if (!empty($categoryId) && !$this->subcategoryExists && !$this->productExists){
					$this->tmpl->setAttribute('no_item_message', 'visibility', 'visible');
					$this->tmpl->addVar("no_item_message", "detail", '項目がありません');// 詳細
				}
				
				// タイトルを設定
				$this->headTitle = $title;		// METAタグタイトル
				$this->tmpl->addVar("show_productlist", "title", $this->convertToDispString($title));
			}
		} else {		// 商品詳細表示のとき
			// 表示モードは画像大
			$this->viewStyle = self::VIEW_STYLE_LARGE;
			
			$ret = $this->db->getProductByProductId($this->productId, $this->langId, $row, $row2, $row3, $row4, $row5);
			if ($ret){
				// 取得値を設定
				//$this->serialNo = $row['pt_serial'];		// シリアル番号
				$this->productId = $row['pt_id'];	// 商品ID
				//$this->langId = $row['pt_language_id'];

				$unitTypeId = $row['pt_unit_type_id'];	// 単位
				// 単位情報を取得
				$unitType = '';
				if ($this->db->getUnitTypeRecord($unitTypeId, $this->langId, $unitRow)){
					$unitType = $unitRow['ut_symbol'];
				}
				$unitQuantity = $row['pt_unit_quantity'];		// 数量
				$description = $row['pt_description'];			// 説明
				$description_short = $row['pt_description_short'];		// 簡易説明
				//$keyword = $row['pt_search_keyword'];					// 検索キーワード
				$this->headTitle = $row['pt_name'];		// METAタグタイトル
				$this->headDesc = $row['pt_description_short'];		// METAタグ要約
				$this->headKeyword = $row['pt_meta_keywords'];			// METAタグキーワード
				$url = $row['pt_site_url'];							// 詳細情報URL
				$this->taxType = $row['pt_tax_type_id'];					// 税種別
				//$adminNote = $row['pt_admin_note'];		// 管理者用備考
			
				// 価格を取得
				$priceArray = $this->getPrice($row2, self::REGULAR_PRICE);
				$price = $priceArray['pp_price'];	// 価格
				$currency = $priceArray['pp_currency_id'];	// 通貨
				$taxType = $row['pt_tax_type_id'];					// 税種別
				$lang = $row['pt_language_id'];					// 言語				
				$prePrice = $this->convertToDispString($priceArray['cu_symbol']);
				$postPrice = $this->convertToDispString($priceArray['cu_post_symbol']);
			
				// 表示額作成
				$this->ecObj->setCurrencyType($currency, $lang);		// 通貨設定
				$this->ecObj->setTaxType($taxType);		// 税種別設定
				$totalPrice = $this->ecObj->getPriceWithTax($price, $dispPrice);	// 税込み価格取得
			
				// 画像を取得
				$imageArray = $this->getImage($row3, self::PRODUCT_IMAGE_SMALL);// 商品画像小
				$imageUrl_s = $imageArray['im_url'];	// URL
				$imageArray = $this->getImage($row3, self::PRODUCT_IMAGE_MEDIUM);// 商品画像中
				$imageUrl_m = $imageArray['im_url'];	// URL
				$imageArray = $this->getImage($row3, self::PRODUCT_IMAGE_LARGE);// 商品画像大
				$imageUrl_l = $imageArray['im_url'];	// URL
							
				// 画像を配列に保存
				$images = array();// 画像URL保存用
				$images[] = $imageUrl_s;
				$images[] = $imageUrl_m;
				$images[] = $imageUrl_l;
			
				// 商品ステータスを取得
				$statusArray = $this->getStatus($row4, self::PRODUCT_STATUS_NEW);// 新規
				$new = $statusArray['ps_value'];
				$statusArray = $this->getStatus($row4, self::PRODUCT_STATUS_SUGGEST);// おすすめ
				$suggest = $statusArray['ps_value'];
			
				// 画像中
/*				$destImg_m = '';
				if ($this->viewStyle == self::VIEW_STYLE_MEDIUM){
					$imageUrl_m = $this->getProperImage($images, 1);
					if (empty($imageUrl_m)){
						$destImg_m = '<img id="preview_img_medium" style="display:none;" ';
						$destImg_m .= 'width="' . $this->defaultImageMWidth . '" ';
						$destImg_m .= 'height="' . $this->defaultImageMHeight . '" ';
						$destImg_m .= '/>';
					} else {
						// URLマクロ変換
						$imgUrl = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $this->gEnv->getRootUrl(), $imageUrl_m);
						$destImg_m = '<img id="preview_img_medium" src="' . $this->getUrl($imgUrl) . '" ';
						$destImg_m .= 'width="' . $this->defaultImageMWidth . '"';
						$destImg_m .= ' height="' . $this->defaultImageMHeight . '"';
						$destImg_m .= ' />';
					}
				}*/
				// 画像大
				$destImg_l = '';
				if ($this->viewStyle == self::VIEW_STYLE_LARGE){
					$imageUrl = $this->getProperImage($images, 2);
					$imageUrl_l = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $this->gEnv->getRootUrl(), $imageUrl);
					$imagePath_l = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $this->gEnv->getSystemRootPath(), $imageUrl);
					if (!file_exists($imagePath_l)){
						$imageUrl_l = $this->getProductImageUrl('0_' . $this->defaultImageLWidth . 'x' . $this->defaultImageLHeight . self::DEFAULT_PRODUCT_IMAGE_TYPE);
					}
					$destImg_l = '<img class="product_image_large" src="' . $this->getUrl($imageUrl_l) . '" ';
					$destImg_l .= 'width="' . $this->defaultImageLWidth . '"';
					$destImg_l .= ' height="' . $this->defaultImageLHeight . '"';
					$destImg_l .= ' />';
				}
				$this->tmpl->addVar("_widget", "id", $row['pt_id']);		// 商品ID
				$this->tmpl->addVar("_widget", "name", $row['pt_name']);		// 名前
				$this->tmpl->addVar("_widget", "code", $row['pt_code']);		// 商品コード
				$this->tmpl->addVar("_widget", "description", $row['pt_description']);		// 説明
				$this->tmpl->addVar("_widget", "desc_short", $row['pt_description_short']);		// 簡易説明		
				$this->tmpl->addVar("show_cart", "unit_type", $unitType);		// 単位タイプ
				$this->tmpl->addVar("show_cart", "quantity", '1');		// 数量デフォルト値
				
				//$this->tmpl->addVar("_widget", "url", $url);				// 詳細情報URL
				//$this->tmpl->addVar("_widget", "admin_note", $adminNote);			// 管理者用備考
				$this->tmpl->addVar("_widget", "disp_total_price", $prePrice . $dispPrice . $postPrice);		// 税込み価格
//				$this->tmpl->addVar("_widget", "image_m", $destImg_m);		// 画像
				$this->tmpl->addVar("_widget", "image_l", $destImg_l);		// 画像
				$this->tmpl->addVar("_widget", "productid_key", M3_REQUEST_PARAM_PRODUCT_ID);		// 商品IDキー
				
				// 在庫表示
				if ($this->showStock){				// 在庫表示する場合
					$stockCount = intval($row['pe_stock_count']);		// 表示在庫数
					$this->tmpl->setAttribute('show_stock', 'visibility', 'visible');
					if (empty($stockViewFormat)) $stockViewFormat = self::DEFAULT_STOCK_VIEW_FORMAT;		// フォーマットが空の場合はデフォルトフォーマットで表示

					// 在庫表示用メッセージを取得
					$stockMsgArray = parseUserCustomParam($stockViewFormat);	// メッセージを配列化

					$stockMsg = '--該当なし--';
					$foreValue = -1;
					for ($i = 0; $i < count($stockMsgArray); $i++){
						if ($stockMsgArray[$i]->key == '' || ($foreValue < $stockCount && $stockCount <= $stockMsgArray[$i]->key)) break;
						$foreValue = $stockMsgArray[$i]->key;
					}
					if ($i < count($stockMsgArray)){
						$stockMsg = str_replace('$1', $stockCount, $stockMsgArray[$i]->value);
					}
					$this->tmpl->addVar("show_stock", "stock", '在庫： ' . $stockMsg);	// 在庫数表示
					
					// 在庫なし時のメッセージ表示
					if ($stockCount <= 0){		// 在庫ない場合
						$this->tmpl->setAttribute('show_cart', 'visibility', 'hidden');
						$this->tmpl->setAttribute('show_no_stock', 'visibility', 'visible');
						
						// マクロ変換
						$this->contentNoStock = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $this->gEnv->getRootUrl(), $this->contentNoStock);
						$this->tmpl->addVar("show_no_stock", 'content', $this->contentNoStock);
					}
				}
			} else {		// 商品情報が見つからないとき
				// テンプレートファイルを強制入れ替え
				$this->replaceTemplateFile('message.tmpl.html');
				$this->SetMsg(self::MSG_APP_ERR, "商品が見つかりません");
			}
		}
	}
	/**
	 * ヘッダ部メタタグの設定
	 *
	 * HTMLのheadタグ内に出力する。
	 * _assign()よりも後に実行される。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ
	 * @return array 						設定データ。連想配列で「title」「description」「keywords」を設定。
	 */
	function _setHeadMeta($request, &$param)
	{
		$headData = array(	'title' => $this->headTitle,
							'description' => $this->headDesc,
							'keywords' => $this->headKeyword);
		return $headData;
	}
	/**
	 * 取得したデータをテンプレートに設定する
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function productListLoop($index, $fetchedRow, $param)
	{
		$images = array();// 画像URL保存用
		
		// 商品の詳細情報を取得
		$ret = $this->db->getProductBySerial($fetchedRow['pt_serial'], $row, $row2, $row3, $row4);
		if ($ret){	
			// 価格を取得
			$priceArray = $this->getPrice($row2, self::REGULAR_PRICE);
			$price = $priceArray['pp_price'];	// 価格
			$currency = $priceArray['pp_currency_id'];	// 通貨
			$taxType = $row['pt_tax_type_id'];					// 税種別
			$lang = $row['pt_language_id'];					// 言語
			$prePrice = $this->convertToDispString($priceArray['cu_symbol']);
			$postPrice = $this->convertToDispString($priceArray['cu_post_symbol']);

			// 表示額作成
			$this->ecObj->setCurrencyType($currency, $lang);		// 通貨設定
			$this->ecObj->setTaxType($taxType);		// 税種別設定
			$totalPrice = $this->ecObj->getPriceWithTax($price, $dispPrice);	// 税込み価格取得
			
			// 画像を取得
			$imageArray = $this->getImage($row3, self::PRODUCT_IMAGE_SMALL);// 商品画像小
			$imageUrl_s = $imageArray['im_url'];	// URL
			$imageArray = $this->getImage($row3, self::PRODUCT_IMAGE_MEDIUM);// 商品画像中
			$imageUrl_m = $imageArray['im_url'];	// URL
			$imageArray = $this->getImage($row3, self::PRODUCT_IMAGE_LARGE);// 商品画像大
			$imageUrl_l = $imageArray['im_url'];	// URL
			
			// 画像を配列に保存
			$images[] = $imageUrl_s;
			$images[] = $imageUrl_m;
			$images[] = $imageUrl_l;
						
			// 商品ステータスを取得
			$statusArray = $this->getStatus($row4, self::PRODUCT_STATUS_NEW);// 新規
			$new = $statusArray['ps_value'];
			$statusArray = $this->getStatus($row4, self::PRODUCT_STATUS_SUGGEST);// おすすめ
			$suggest = $statusArray['ps_value'];
		}
		// 画像中
		$destImg_m = '';
		if ($this->viewStyle == self::VIEW_STYLE_MEDIUM){
			$imageUrl = $this->getProperImage($images, 1);
			$imageUrl_m = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $this->gEnv->getRootUrl(), $imageUrl);
			$imagePath_m = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $this->gEnv->getSystemRootPath(), $imageUrl);
			if (!file_exists($imagePath_m)){
				$imageUrl_m = $this->getProductImageUrl('0_' . $this->defaultImageMWidth . 'x' . $this->defaultImageMHeight . self::DEFAULT_PRODUCT_IMAGE_TYPE);
			}
			$destImg_m = '<img class="product_image_medium" src="' . $this->getUrl($imageUrl_m) . '" ';
			$destImg_m .= 'width="' . $this->defaultImageMWidth . '"';
			$destImg_m .= ' height="' . $this->defaultImageMHeight . '"';
			$destImg_m .= ' />';
		}
		// 項目選択のラジオボタンの状態
		$id = $this->convertToDispString($row['pt_id']);
		$selected = '';
		if ($id == $this->productId){
			$selected = 'checked';
		}
		
		$visible = '';
		if ($row['pt_visible']){	// 項目の表示
			$visible = 'checked';
		}
		
		// 商品詳細リンクを作成
		$name = '詳細';		// 名前
		//$linkUrl = $this->gEnv->getDefaultUrl() . '?' . M3_REQUEST_PARAM_PRODUCT_ID . '=' . $row['pt_id'];
		$linkUrl = $this->currentPageUrl;
		if (!empty($this->categoryId)) $linkUrl .= '&category=' . $this->categoryId;
		$linkUrl .= '&' . M3_REQUEST_PARAM_PRODUCT_ID . '=' . $row['pt_id'];
		$link = '<a href="' . $this->getUrl($linkUrl, true) . '" >' . $name . '</a>';
		
		// 画像にリンクを付ける
//		if (!empty($destImg_s)) $destImg_s = '<a href="' . $this->getUrl($linkUrl, true) . '" >' . $destImg_s . '</a>';
		if (!empty($destImg_m)) $destImg_m = '<a href="' . $this->getUrl($linkUrl, true) . '" >' . $destImg_m . '</a>';
		
		// 在庫表示
		if ($this->showStock){				// 在庫表示する場合
			$stockCount = intval($row['pe_stock_count']);		// 表示在庫数
			if (empty($stockViewFormat)) $stockViewFormat = self::DEFAULT_STOCK_VIEW_FORMAT;		// フォーマットが空の場合はデフォルトフォーマットで表示

			// 在庫表示用メッセージを取得
			$stockMsgArray = parseUserCustomParam($stockViewFormat);	// メッセージを配列化

			$stockMsg = '--該当なし--';
			$foreValue = -1;
			for ($i = 0; $i < count($stockMsgArray); $i++){
				if ($stockMsgArray[$i]->key == '' || ($foreValue < $stockCount && $stockCount <= $stockMsgArray[$i]->key)) break;
				$foreValue = $stockMsgArray[$i]->key;
			}
			if ($i < count($stockMsgArray)){
				$stockMsg = str_replace('$1', $stockCount, $stockMsgArray[$i]->value);
			}
			
			$this->tmpl->clearTemplate('show_stock');
			$stockRow = array(
				'stock' => '在庫： ' . $stockMsg			// 在庫数表示
			);
			$this->tmpl->addVars('show_stock', $stockRow);
			$this->tmpl->parseTemplate('show_stock', 'a');
			
			// 在庫なし時のメッセージ表示
			if ($stockCount <= 0){		// 在庫ない場合	
				$this->tmpl->addVar('cart_option', 'carttype', 'content');		// コンテンツ表示
				$this->tmpl->addVar("cart_option", 'content', $this->contentNoStock);
			}
		}
		$this->tmpl->addVar("cart_option", 'id', $id);
		
		$itemRow = array(
			'no' => $index + 1,													// 行番号
			'serial' => $this->convertToDispString($row['pt_serial']),	// シリアル番号
			'id' => $id,			// ID
//			'image_s' => $destImg_s,		// 画像
			'image_m' => $destImg_m,		// 画像
			'image_l_url' => $this->getUrl($imageUrl_l),		// ツールチップ用画像URL
			'name' => $this->convertToDispString($row['pt_name']),		// 名前
			'code' => $this->convertToDispString($row['pt_code']),		// 商品コード
			'disp_total_price' => $prePrice . $dispPrice . $postPrice,				// 税込み価格
			'description_short' => $row['pt_description_short'],				// 簡易説明
			'product_link' => $link												// 商品詳細リンク
		);
		$this->tmpl->addVars('productlist', $itemRow);
		$this->tmpl->parseTemplate('productlist', 'a');
		
		// 表示中のコンテンツIDを保存
		$this->dispContIdArray[] = $row['pt_id'];
		
		$this->productExists = true;				// 商品が存在するかどうか
		return true;
	}
	/**
	 * 価格取得
	 *
	 * @param array  	$srcRows			価格リスト
	 * @param string	$priceType			価格のタイプ
	 * @return array						取得した価格行
	 */
	function getPrice($srcRows, $priceType)
	{
		for ($i = 0; $i < count($srcRows); $i++){
			if ($srcRows[$i]['pp_currency_id'] == $this->currencyId && $srcRows[$i]['pp_price_type_id'] == $priceType){
				return $srcRows[$i];
			}
		}
		return array();
	}
	/**
	 * 画像取得
	 *
	 * @param array  	$srcRows			画像リスト
	 * @param string	$imageType			画像タイプ
	 * @return array						取得した行
	 */
	function getImage($srcRows, $sizeType)
	{
		for ($i = 0; $i < count($srcRows); $i++){
			if ($srcRows[$i]['im_size_id'] == $sizeType){
				return $srcRows[$i];
			}
		}
		return array();
	}
	/**
	 * 商品ステータス取得
	 *
	 * @param array  	$srcRows			取得行
	 * @param string	$type			商品ステータスタイプ
	 * @return array						取得した行
	 */
	function getStatus($srcRows, $type)
	{
		for ($i = 0; $i < count($srcRows); $i++){
			if ($srcRows[$i]['ps_type'] == $type){
				return $srcRows[$i];
			}
		}
		return array();
	}
	/**
	 * 最適な画像を取得
	 *
	 * @param array  	$images			画像へのパス(優先順)
	 * @param int		$index			目的の画像のインデックス番号
	 * @return string					パス
	 */
	function getProperImage($images, $index)
	{
		// 指定画像が存在する場合はそのまま返す
		if (!empty($images[$index])) return $images[$index];
		
		for ($i = $index + 1; $i < count($images); $i++){
			if (!empty($images[$i])) return $images[$i];
		}
		for ($i = 0; $i < $index; $i++){
			if (!empty($images[$i])) return $images[$i];
		}
		return '';
	}
	/**
	 * 商品画像URL取得
	 *
	 * @param string $filename		ファイル名
	 * @return string				URL
	 */
	function getProductImageUrl($filename)
	{
		return $this->gEnv->getResourceUrl() . self::PRODUCT_IMAGE_DIR . $filename;
	}
	/**
	 * サブカテゴリーを取得
	 *
	 * @param array $category		カテゴリー
	 * @return array				すべてのカテゴリーID
	 */
	function getSubCategory($category, $level = 0)
	{
		if (empty($category)) return array();
		
		$destCategory = $category;
		
		for ($i = 0; $i < count($category); $i++){
			$addCategory = $this->_getChildCategory($category[$i]);
			$destCategory = array_merge($destCategory, $addCategory);
		}
		return $destCategory;
	}
	/**
	 * サブカテゴリーを取得
	 *
	 * @param int $categoryId		カテゴリーId
	 * @return array				すべてのカテゴリーID
	 */
	function _getChildCategory($categoryId, $level = 0)
	{
		$destCategory = array();
		
		// メニューの階層を制限
		if ($level >= self::MAX_CATEGORY_LEVEL) return $destCategory;
		$level++;
		
		// 子カテゴリーを取得
		$ret = $this->db->getChildCategory($categoryId, $this->langId, $rows);
		if ($ret){
			for ($i = 0; $i < count($rows); $i++){
				$childCategory = $rows[$i]['pc_id'];
				if (!in_array($childCategory, $destCategory)){
					$addCategoryArray = $this->_getChildCategory($childCategory, $level);
					for ($j = 0; $j < count($addCategoryArray); $j++){
						$addCategory = $addCategoryArray[$j];
						if (!in_array($addCategory, $destCategory)) $destCategory[] = $addCategory;
					}
					$destCategory[] = $childCategory;
				}
			}
		}
		return $destCategory;
	}
}
?>
