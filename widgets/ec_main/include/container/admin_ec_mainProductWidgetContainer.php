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
 * @copyright  Copyright 2006-2013 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() . '/admin_ec_mainBaseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() .	'/ec_mainProductDb.php');
require_once($gEnvManager->getLibPath()			. '/qqFileUploader/fileuploader.php');

class admin_ec_mainProductWidgetContainer extends admin_ec_mainBaseWidgetContainer
{
	private $db;	// DB接続オブジェクト
	private $serialNo;			// シリアル番号
	private $serialArray = array();		// 表示されている項目のシリアル番号
	private $sessionParamObj;		// セッション保存データ
	private $langId;			// 言語
	private $unitTypeId;		// 選択単位
	private $currency;			// 通貨
	private $taxType;			// 税種別
	private $ecObj;			// 共通ECオブジェクト
	private $categoryListData;		// 商品カテゴリー
	private $categoryArray;			// カテゴリー設定値
	private $sortKeyType;			// ソートキータイプ
	private $sortKey;		// ソートキー
	private $sortDirection;		// ソート方向
	private $imageTypes;			// 画像タイプ
	private $catagorySelectCount;	// カテゴリー選択可能数
	const MAX_HIER_LEVEL = 20;		// カテゴリー階層最大値
	const STANDARD_PRICE = 'selling';		// 通常価格
	const PRODUCT_IMAGE_MEDIUM = 'standard-product';		// 中サイズ商品画像ID
	const PRODUCT_IMAGE_SMALL = 'small-product';		// 小サイズ商品画像ID
	const PRODUCT_IMAGE_LARGE = 'large-product';		// 大サイズ商品画像ID
	const PRODUCT_STATUS_NEW = 'new';		// 商品ステータス新規
	const PRODUCT_STATUS_SUGGEST = 'suggest';		// 商品ステータスおすすめ
	const DEFAULT_TAX_TYPE = 'sales';			// デフォルト税種別
	const PRICE_OBJ_ID = "eclib";		// 価格計算オブジェクトID
	const DEFAULT_CATEGORY_COUNT = 5;				// 商品カテゴリーの選択可能数
	const DEFAULT_LIST_COUNT = 20;			// 最大リスト表示数
	const DEFAULT_UNIT_TYPE_ID = 'ko';		// 販売単位(個)
	const UPLOAD_ICON_FILE = '/images/system/upload_box32.png';		// アップロードボックスアイコン
	const SORT_UP_ICON_FILE = '/images/system/arrow_up10.png';		// ソート降順アイコン
	const SORT_DOWN_ICON_FILE = '/images/system/arrow_down10.png';		// ソート昇順アイコン
	const SEARCH_ICON_FILE = '/images/system/search16.png';		// 検索用アイコン
	const SORT_ICON_SIZE = 10;		// ソートアイコンサイズ
	const CURRENT_TASK = 'product_detail';
	const PRODUCT_IMAGE_DIR = '/widgets/product/image/';				// 商品画像格納ディレクトリ
	const LOG_MSG_ADD_CONTENT = '商品情報を追加しました。商品名: %s';
	const LOG_MSG_UPDATE_CONTENT = '商品情報を更新しました。商品名: %s';
	const LOG_MSG_DEL_CONTENT = '商品情報を削除しました。商品名: %s';
	const DEFAULT_PRODUCT_LIST_KEY = 'id';		// 商品一覧のデフォルトキー
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->db = new ec_mainProductDb();
		
		// EC用共通オブジェクト取得
		$this->ecObj = $this->gInstance->getObject(self::PRICE_OBJ_ID);
		
		$this->sortKeyType = array('index'/*表示順*/, 'stock'/*在庫数*/, 'id'/*商品ID*/, 'date'/*更新日時*/, 'name'/*商品名*/, 'code'/*商品コード*/, 'price'/*商品価格*/, 'visible'/*公開状態*/);
		$this->imageTypes = array('s', 'm', 'l');			// 画像タイプ
		$this->catagorySelectCount = self::$_mainDb->getCommerceConfig(photo_shopCommonDef::CF_E_CATEGORY_SELECT_COUNT);	// カテゴリー選択可能数
		if ($this->catagorySelectCount <= 0) $this->catagorySelectCount = self::DEFAULT_CATEGORY_COUNT;
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
		$task = $request->trimValueOf('task');
		if ($task == 'product_detail'){		// 詳細画面
			return 'admin_product_detail.tmpl.html';
		} else {
			return 'admin_product.tmpl.html';
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
		// ##### セッションパラメータ取得 #####
		$this->sessionParamObj = $this->getWidgetSessionObj();		// セッション保存パラメータ
		if (empty($this->sessionParamObj)){			// 空の場合は作成
			$this->sessionParamObj = new stdClass;		
//			$this->sessionParamObj->uploadFile = array();		// アップロードしたファイル
			$this->sessionParamObj->imageFile = array();		// 画像ファイル
		}
		
		$task = $request->trimValueOf('task');
		if ($task == 'product_detail'){	// 詳細画面
			return $this->createDetail($request);
		} else {			// 一覧画面
			return $this->createList($request);
		}
	}
	/**
	 * 一覧画面作成
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param								なし
	 */
	function createList($request)
	{
		// ユーザ情報、表示言語
		$userId		= $this->gEnv->getCurrentUserId();
		$defaultLangId	= $this->gEnv->getDefaultLanguage();
		
		// ##### 検索条件 #####
		$pageNo = $request->trimIntValueOf(M3_REQUEST_PARAM_PAGE_NO, '1');				// ページ番号
		// DBの保存設定値を取得
		$maxListCount = self::DEFAULT_LIST_COUNT;				// 表示項目数
		$this->search_categoryId = $request->trimValueOf('category');		// 検索カテゴリー
		$keyword = $request->trimValueOf('keyword');			// 検索キーワード
		$sort = $request->trimValueOf('sort');		// ソート順
		
		$act = $request->trimValueOf('act');
		if ($act == 'delete'){		// 項目削除の場合
			$listedItem = explode(',', $request->trimValueOf('seriallist'));
			$delItems = array();
			for ($i = 0; $i < count($listedItem); $i++){
				// 項目がチェックされているかを取得
				$itemName = 'item' . $i . '_selected';
				$itemValue = ($request->trimValueOf($itemName) == 'on') ? 1 : 0;
				
				if ($itemValue){		// チェック項目
					$delItems[] = $listedItem[$i];
				}
			}
			if (count($delItems) > 0){
				// 削除する商品の情報を取得
				$delProductInfo = array();
				$imageUrlArray = array();
				for ($i = 0; $i < count($delItems); $i++){
					$ret = $this->db->getProductBySerial($delItems[$i], $row, $row2, $row3, $row4, $row5);
					if ($ret){
						$newInfoObj = new stdClass;
						$newInfoObj->id = $row['pt_id'];		// 商品ID
						$newInfoObj->name = $row['pt_name'];	// 商品名
						$delProductInfo[] = $newInfoObj;
						
						// 削除する商品画像
						$imageArray = $this->getImage($row3, self::PRODUCT_IMAGE_SMALL);// 商品画像小
						if (!empty($imageArray['im_url'])) $imageUrlArray[] = $imageArray['im_url'];	// URL
						$imageArray = $this->getImage($row3, self::PRODUCT_IMAGE_MEDIUM);// 商品画像中
						if (!empty($imageArray['im_url'])) $imageUrlArray[] = $imageArray['im_url'];	// URL
						$imageArray = $this->getImage($row3, self::PRODUCT_IMAGE_LARGE);// 商品画像大
						if (!empty($imageArray['im_url'])) $imageUrlArray[] = $imageArray['im_url'];	// URL
					}
				}
				// 商品を削除
				$ret = $this->db->delProduct($delItems);
				if ($ret){		// データ削除成功のとき
					// 商品画像を削除
					for ($i = 0; $i < count($imageUrlArray); $i++){
						$imageFile = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $this->gEnv->getSystemRootPath(), $imageUrlArray[$i]);
						if (strStartsWith($imageFile, $this->gEnv->getResourcePath() . self::PRODUCT_IMAGE_DIR)){// 商品画像ディレクトリの場合のみ削除
							if (file_exists($imageFile)) unlink($imageFile);		
						}
					}
					
					$this->setGuidanceMsg('データを削除しました');
					
					// 運用ログを残す
					for ($i = 0; $i < count($delProductInfo); $i++){
						$infoObj = $delProductInfo[$i];
						//$this->gOpeLog->writeUserInfo(__METHOD__, '商品を削除しました。ID: ' . $infoObj->id, 2100, '商品名=' . $infoObj->name);
						$this->gOpeLog->writeUserInfo(__METHOD__, sprintf(self::LOG_MSG_DEL_CONTENT, $infoObj->name), 2100, 'ID=' . $infoObj->id);
					}
				} else {
					$this->setAppErrorMsg('データ削除に失敗しました');
				}
			}
		} else if ($act == 'search'){		// 検索のとき
			$pageNo = 1;		// ページ番号初期化
		}
		// アップロードファイル初期化
		$this->resetUploadImage();
			
		// カテゴリー一覧を作成
		$this->db->getAllCategoryByLoop($defaultLangId, array($this, 'categoryLoop'));
						
		// ###### 検索条件を作成 ######
		// キーワード分割
		$parsedKeywords = $this->gInstance->getTextConvManager()->parseSearchKeyword($keyword);
				
		// 総数を取得
		$totalCount = $this->db->searchProductCount($parsedKeywords, $this->search_categoryId, $defaultLangId);
		$pageCount = (int)(($totalCount -1) / $maxListCount) + 1;		// 総ページ数

		// 表示するページ番号の修正
		if ($pageNo < 1) $pageNo = 1;
		if ($pageNo > $pageCount) $pageNo = $pageCount;

		// ソート順
		list($this->sortKey, $this->sortDirection) = explode('-', $sort);
		if (!in_array($this->sortKey, $this->sortKeyType) || !in_array($this->sortDirection, array('0', '1'))){
			$this->sortKey = self::DEFAULT_PRODUCT_LIST_KEY;
			$this->sortDirection = '1';	// 昇順
		}
		
		// 商品リストを表示
		$this->db->searchProduct($parsedKeywords, $this->search_categoryId, $defaultLangId, $maxListCount, ($pageNo -1) * $maxListCount,
									$this->sortKey, $this->sortDirection, array($this, 'productListLoop'));

		// ページング用リンク作成
		$pageLink = '';
		if ($pageCount > 1){	// ページが2ページ以上のときリンクを作成
			$sort = '';		// ソート値
			if (!empty($this->sortKey)) $sort = '&sort=' . $this->sortKey . '-' . $this->sortDirection;
			
			for ($i = 1; $i <= $pageCount; $i++){
				//$linkUrl = $this->gEnv->getDefaultAdminUrl() . '?' . M3_REQUEST_PARAM_OPERATION_COMMAND . '=' . M3_REQUEST_CMD_CONFIG_WIDGET . 
				//				'&' . M3_REQUEST_PARAM_WIDGET_ID . '=' . $this->gEnv->getCurrentWidgetId() . 
				$linkUrl = $this->_baseUrl .
							'&task=product&keyword=' . urlencode($keyword) . '&category=' . $this->search_categoryId . '&page=' . $i . $sort;
				if ($i == $pageNo){
					$link = '&nbsp;' . $i;
				} else {
					$link = '&nbsp;<a href="' . $this->getUrl($linkUrl, true) . '" >' . $i . '</a>';
				}
				$pageLink .= $link;
			}
		}
		// 検出順を作成
		$startNo = ($pageNo -1) * $maxListCount +1;
		$endNo = $pageNo * $maxListCount > $totalCount ? $totalCount : $pageNo * $maxListCount;
		$this->tmpl->addVar("show_productlist", "page_link", $pageLink);
		$this->tmpl->addVar("_widget", "page_link", $pageLink);
		$this->tmpl->addVar("_widget", "total_count", $totalCount);
		$this->tmpl->addVar("search_range", "start_no", $startNo);
		$this->tmpl->addVar("search_range", "end_no", $endNo);
		if ($totalCount > 0) $this->tmpl->setAttribute('search_range', 'visibility', 'visible');// 検出範囲を表示
		
		// ソート用データ設定
/*		if (empty($this->sortKey)){		// ソートが設定されていない場合
			$this->tmpl->addVar('_widget', 'sort_id', 'id-1');
			$this->tmpl->addVar('_widget', 'sort_name', 'name-1');
			$this->tmpl->addVar('_widget', 'sort_code', 'code-1');
			$this->tmpl->addVar('_widget', 'sort_stock', 'stock-1');
			$this->tmpl->addVar('_widget', 'sort_index', 'index-1');
			$this->tmpl->addVar('_widget', 'sort_visible', 'visible-1');
			$this->tmpl->addVar('_widget', 'sort_date', 'date-1');
		} else {		// ソートが設定されている場合*/
			if (empty($this->sortDirection)){
				$iconUrl = $this->getUrl($this->gEnv->getRootUrl() . self::SORT_UP_ICON_FILE);	// ソート降順アイコン
				$iconTitle = '降順';
			} else {
				$iconUrl = $this->getUrl($this->gEnv->getRootUrl() . self::SORT_DOWN_ICON_FILE);	// ソート昇順アイコン
				$iconTitle = '昇順';
			}
			$sortImage = '<img src="' . $iconUrl . '" width="' . self::SORT_ICON_SIZE . '" height="' . self::SORT_ICON_SIZE . '" title="' . $iconTitle . '" alt="' . $iconTitle . '" />';
			
			switch ($this->sortKey){
				case 'id':		// 商品ID
					$this->tmpl->addVar('_widget', 'direct_icon_id', $sortImage);
					break;
				case 'name':		// 商品名
					$this->tmpl->addVar('_widget', 'direct_icon_name', $sortImage);
					break;
				case 'code':		// 商品コード
					$this->tmpl->addVar('_widget', 'direct_icon_code', $sortImage);
					break;
				case 'price':		// 商品価格
					$this->tmpl->addVar('_widget', 'direct_icon_price', $sortImage);
					break;
				case 'stock':		// 在庫数
					$this->tmpl->addVar('_widget', 'direct_icon_stock', $sortImage);
					break;
				case 'index':		// 表示順
					$this->tmpl->addVar('_widget', 'direct_icon_index', $sortImage);
					break;
				case 'visible':		// 公開状態
					$this->tmpl->addVar('_widget', 'direct_icon_visible', $sortImage);
					break;
				case 'date':		// 更新日時
					$this->tmpl->addVar('_widget', 'direct_icon_date', $sortImage);
					break;
			}
			if ($this->sortKey == 'id' && !empty($this->sortDirection)){
				$this->tmpl->addVar('_widget', 'sort_id', 'id-0');
			} else {
				$this->tmpl->addVar('_widget', 'sort_id', 'id-1');
			}
			if ($this->sortKey == 'name' && !empty($this->sortDirection)){
				$this->tmpl->addVar('_widget', 'sort_name', 'name-0');
			} else {
				$this->tmpl->addVar('_widget', 'sort_name', 'name-1');
			}
			if ($this->sortKey == 'code' && !empty($this->sortDirection)){
				$this->tmpl->addVar('_widget', 'sort_code', 'code-0');
			} else {
				$this->tmpl->addVar('_widget', 'sort_code', 'code-1');
			}
			if ($this->sortKey == 'stock' && !empty($this->sortDirection)){
				$this->tmpl->addVar('_widget', 'sort_stock', 'stock-0');
			} else {
				$this->tmpl->addVar('_widget', 'sort_stock', 'stock-1');
			}
			if ($this->sortKey == 'index' && !empty($this->sortDirection)){
				$this->tmpl->addVar('_widget', 'sort_index', 'index-0');
			} else {
				$this->tmpl->addVar('_widget', 'sort_index', 'index-1');
			}
			if ($this->sortKey == 'visible' && !empty($this->sortDirection)){
				$this->tmpl->addVar('_widget', 'sort_visible', 'visible-0');
			} else {
				$this->tmpl->addVar('_widget', 'sort_visible', 'visible-1');
			}
			if ($this->sortKey == 'date' && !empty($this->sortDirection)){
				$this->tmpl->addVar('_widget', 'sort_date', 'date-0');
			} else {
				$this->tmpl->addVar('_widget', 'sort_date', 'date-1');
			}
			$this->tmpl->addVar('_widget', 'sort', $this->sortKey . '-' . $this->sortDirection);
//		}
		// ボタン作成
		$searchImg = $this->getUrl($this->gEnv->getRootUrl() . self::SEARCH_ICON_FILE);
		$searchStr = '検索';
		$this->tmpl->addVar("_widget", "search_img", $searchImg);
		$this->tmpl->addVar("_widget", "search_str", $searchStr);
		
		// パラメータ再設定
		$this->tmpl->addVar("_widget", "search_word", $this->convertToDispString($keyword));
			
		// その他
		$this->tmpl->addVar("_widget", "serial_list", implode($this->serialArray, ','));// 表示項目のシリアル番号を設定
	}
	/**
	 * 詳細画面作成
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param								なし
	 */
	function createDetail($request)
	{
		// ユーザ情報、表示言語
		$userId		= $this->gEnv->getCurrentUserId();
		$defaultLang	= $this->gEnv->getDefaultLanguage();
		$defaultLangName = $this->gEnv->getDefaultLanguageNameByCurrentLanguage();// デフォルト言語の現在の表示名を取得
		
		// 画像情報を取得
		$defaultImageSWidth = 0;
		$defaultImageSHeight = 0;
		$ret = $this->db->getProductImageInfo(self::PRODUCT_IMAGE_SMALL, $row);
		if ($ret){
			$defaultImageSWidth = $row['is_width'];
			$defaultImageSHeight = $row['is_height'];
		}
		$defaultImageMWidth = 0;
		$defaultImageMHeight = 0;
		$ret = $this->db->getProductImageInfo(self::PRODUCT_IMAGE_MEDIUM, $row);
		if ($ret){
			$defaultImageMWidth = $row['is_width'];
			$defaultImageMHeight = $row['is_height'];
		}
		$defaultImageLWidth = 0;
		$defaultImageLHeight = 0;
		$ret = $this->db->getProductImageInfo(self::PRODUCT_IMAGE_LARGE, $row);
		if ($ret){
			$defaultImageLWidth = $row['is_width'];
			$defaultImageLHeight = $row['is_height'];
		}
		// 作成画像フォーマット
		$imageFormat_s = $defaultImageSWidth . 'x' . $defaultImageSHeight . photo_shopCommonDef::DEFAULT_PRODUCT_IMAGE_TYPE;
		$imageFormat_m = $defaultImageMWidth . 'x' . $defaultImageMHeight . photo_shopCommonDef::DEFAULT_PRODUCT_IMAGE_TYPE;
		$imageFormat_l = $defaultImageLWidth . 'x' . $defaultImageLHeight . photo_shopCommonDef::DEFAULT_PRODUCT_IMAGE_TYPE;
		$imageFormat = $imageFormat_s . ';' . $imageFormat_m . ';' . $imageFormat_l;
		
		// 商品一覧へ戻す値
		$sort = $request->trimValueOf('sort');		// ソート順
		
		$act = $request->trimValueOf('act');
		$this->serialNo = $request->trimValueOf('serial');			// 選択項目のシリアル番号
//		if (empty($this->serialNo)) $this->serialNo = 0;
//		$this->productId = $request->trimValueOf('productid');	// 商品ID
//		if (empty($this->productId)) $this->productId = 0;
		$this->currency = $request->trimValueOf('item_currency');		// 通貨
		if (empty($this->currency)) $this->currency	= $this->ecObj->getDefaultCurrency();		// 通貨
		$imageType = $request->trimValueOf('imagetype');

		// 編集中の項目
		$name	= $request->trimValueOf('item_name');		// 商品名
		$code	= $request->trimValueOf('item_code');		// 商品コード
		$index	= $request->trimValueOf('item_index');		// 表示順
		$visible = ($request->trimValueOf('item_visible') == 'on') ? 1 : 0;			// 表示するかどうか
		$this->unitTypeId = $request->trimValueOf('item_unit_type');		// 選択単位
		$unitQuantity = $request->trimValueOf('item_unit_quantity');		// 数量
		$stockCount	= $request->trimValueOf('item_stock_count');		// 表示在庫数
		if (empty($stockCount)) $stockCount = 0;
		$delivType = $request->trimValueOf('item_deliv_type');		// 配送タイプ
		$delivPrice = $request->trimValueOf('item_deliv_price');		// 配送単価
		if (empty($delivPrice)) $delivPrice = 0;
		$delivWeight = $request->trimValueOf('item_deliv_weight');		// 配送基準重量
		if (empty($delivWeight)) $delivWeight = 0;
		$description = $request->valueOf('item_description');			// 説明
		$description_short = $request->trimValueOf('item_desc_short');		// 簡易説明
		//$keyword = $request->trimValueOf('item_keyword');					// 検索キーワード
		$metaKeyword = $request->trimValueOf('item_meta_keyword');	// METAタグ用キーワード
		$url = $request->trimValueOf('item_url');							// 詳細情報URL
		$this->taxType = $request->trimValueOf('item_tax_type');					// 税種別
		$adminNote = $request->trimValueOf('item_admin_note');		// 管理者用備考
		$price = $request->trimValueOf('item_price');		// 価格
		
		// 画像のパスをマクロ表記パスに直す
/*		$imageUrl_s = $request->trimValueOf('imageurl_s');		// 小画像
		if (!empty($imageUrl_s)){
			if (strncmp($imageUrl_s, '/', 1) == 0){		// 相対パス表記のとき
				$imageUrl_s = M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END . $this->gEnv->getRelativePathToSystemRootUrl($this->gEnv->getDocumentRootUrl() . $imageUrl_s);
			}
		}
		$imageUrl_m = $request->trimValueOf('imageurl_m');		// 中画像
		if (!empty($imageUrl_m)){
			if (strncmp($imageUrl_m, '/', 1) == 0){		// 相対パス表記のとき
				$imageUrl_m = M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END . $this->gEnv->getRelativePathToSystemRootUrl($this->gEnv->getDocumentRootUrl() . $imageUrl_m);
			}
		}
		$imageUrl_l = $request->trimValueOf('imageurl_l');		// 大画像
		if (!empty($imageUrl_l)){
			if (strncmp($imageUrl_l, '/', 1) == 0){		// 相対パス表記のとき
				$imageUrl_l = M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END . $this->gEnv->getRelativePathToSystemRootUrl($this->gEnv->getDocumentRootUrl() . $imageUrl_l);
			}
		}*/
		$new = ($request->trimValueOf('item_new') == 'on') ? 1 : 0;		// 新規
		$suggest = ($request->trimValueOf('item_suggest') == 'on') ? 1 : 0;		// おすすめ
		$this->langId = $request->trimValueOf('item_lang');				// 現在メニューで選択中の言語
		if (empty($this->langId)) $this->langId = $defaultLang;			// 言語が選択されていないときは、デフォルト言語を設定
		
		// カテゴリーを取得
		$this->categoryArray = array();
		for ($i = 0; $i < $this->catagorySelectCount; $i++){
			$itemName = 'item_category' . $i;
			$itemValue = $request->trimValueOf($itemName);
			if (!empty($itemValue)){		// 0以外の値を取得
				$this->categoryArray[] = $itemValue;
			}
		}
	
		$reloadData = false;		// データ再取得するかどうか
		if ($act == 'select'){		// 項目選択の場合
			$reloadData = true;		// データ再取得
		} else if ($act == 'add'){		// 項目追加の場合
			// 入力チェック
			$this->checkInput($name, '商品名');
			$this->checkInput($code, '商品コード');
			$this->checkNumeric($index, '表示順');
			$this->checkNumericF($unitQuantity, '数量');
			$this->checkNumeric($stockCount, '表示在庫数');
			$this->checkNumericF($price, '商品価格');
			if (empty($this->unitTypeId)) $this->setUserErrorMsg('販売単位が選択されていません');

			// エラーなしの場合は、データを登録
			if ($this->getMsgCount() == 0){
				// ##### 格納値を作成 #####
				// 商品情報その他
				$otherParams = array();
				$otherParams['pt_name'] = $name;			// 商品名
				$otherParams['pt_code'] = $code;			// 商品コード
				$otherParams['pt_sort_order'] = $index;			// 表示順
				$otherParams['pt_visible'] = $visible;		// 表示するかどうか
				$otherParams['pt_product_type'] = 1;				// 商品種別(1=単品商品、2=セット商品、3=オプション商品)
				$otherParams['pt_unit_type_id'] = $this->unitTypeId;	// 選択単位
				$otherParams['pt_unit_quantity'] = $unitQuantity;		// 数量
				$otherParams['pt_description'] = $description;		// 説明
				$otherParams['pt_description_short'] = $description_short;	// 簡易説明
				//$otherParams['pt_search_keyword'] = $keyword;		// 検索キーワード
				$otherParams['pt_meta_keywords'] = $metaKeyword;	// METAタグ用キーワード
				$otherParams['pt_site_url'] = $url;			// 詳細情報URL
				$otherParams['pt_tax_type_id'] = $this->taxType;	// 税種別
				$otherParams['pt_admin_note'] = $adminNote;		// 管理者用備考
				$otherParams['pt_deliv_type'] = $delivType;		// 配送タイプ
				$otherParams['pt_deliv_fee'] = $delivPrice;		// 配送単価
				$otherParams['pt_weight'] = $delivWeight;		// 配送基準重量
				
				// 価格情報の作成
				$priceArray = array();
				$startDt = $this->gEnv->getInitValueOfTimestamp();
				$endDt = $this->gEnv->getInitValueOfTimestamp();
				$priceArray[] = array(self::STANDARD_PRICE, $this->currency, $price, $startDt, $endDt);		// 単品商品で追加
				
				// 画像情報の作成
				$productId = $this->db->getNextProductId();		// 次の商品ID取得
				$imageKeys = array_keys($this->sessionParamObj->imageFile);
				for ($i = 0; $i < count($imageKeys); $i++){
					switch ($imageKeys[$i]){
						case 's':
							$imageFilename = $productId . '_' . $imageFormat_s;
							$imageUrl_s = str_replace($this->gEnv->getRootUrl(), M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $this->getProductImageUrl($imageFilename));
							$imagePath_s = $this->getProductImagePath($imageFilename);
							break;
						case 'm':
							$imageFilename = $productId . '_' . $imageFormat_m;
							$imageUrl_m = str_replace($this->gEnv->getRootUrl(), M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $this->getProductImageUrl($imageFilename));
							$imagePath_m = $this->getProductImagePath($imageFilename);
							break;
						case 'l':
							$imageFilename = $productId . '_' . $imageFormat_l;
							$imageUrl_l = str_replace($this->gEnv->getRootUrl(), M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $this->getProductImageUrl($imageFilename));
							$imagePath_l = $this->getProductImagePath($imageFilename);
							break;
					}
				}
				// 配列に格納
				$imageArray = array();
				if (!empty($imageUrl_s)) $imageArray[] = array(self::PRODUCT_IMAGE_SMALL, '', $imageUrl_s);
				if (!empty($imageUrl_m)) $imageArray[] = array(self::PRODUCT_IMAGE_MEDIUM, '', $imageUrl_m);
				if (!empty($imageUrl_l)) $imageArray[] = array(self::PRODUCT_IMAGE_LARGE, '', $imageUrl_l);
								
				// 商品ステータス情報の作成
				$statusArray = array();
				$statusArray[] = array(self::PRODUCT_STATUS_NEW, $new);		// 新着
				$statusArray[] = array(self::PRODUCT_STATUS_SUGGEST, $suggest);		// おすすめ
				
				$ret = $this->db->updateProduct(0/*データ新規追加*/, $productId * (-1)/*次のコンテンツIDのチェック*/, $this->langId, $otherParams, 
												$stockCount, $priceArray, $imageArray, $statusArray, $this->categoryArray, $newSerial);
				if ($ret){
					$this->setGuidanceMsg('データを追加しました');
					
					// 画像ファイルの移動
					$imageDir = $this->getProductImagePath();
					if (!file_exists($imageDir)) mkdir($imageDir, M3_SYSTEM_DIR_PERMISSION, true/*再帰的*/);		// ディレクトリ作成
					for ($i = 0; $i < count($imageKeys); $i++){
						$imageFile = $this->sessionParamObj->imageFile[$imageKeys[$i]];
						switch ($imageKeys[$i]){
							case 's':
								renameFile($imageFile, $imagePath_s);
								break;
							case 'm':
								renameFile($imageFile, $imagePath_m);
								break;
							case 'l':
								renameFile($imageFile, $imagePath_l);
								break;
						}
					}
					// セッションの画像情報をクリア
					$this->resetUploadImage();
				
					// シリアル番号更新
					$this->serialNo = $newSerial;
					$reloadData = true;		// データ再取得
					
					// 運用ログを残す
					$ret = $this->db->getProductBySerial($this->serialNo, $row, $row2, $row3, $row4, $row5);
					//if ($ret) $this->gOpeLog->writeUserInfo(__METHOD__, '商品を追加しました。ID: ' . $row['pt_id'], 2100, '商品名=' . $row['pt_name']);
					if ($ret) $this->gOpeLog->writeUserInfo(__METHOD__, sprintf(self::LOG_MSG_ADD_CONTENT, $row['pt_name']), 2100, 'ID=' . $row['pt_id']);
				} else {
					$this->setAppErrorMsg('データ追加に失敗しました');
				}
			}
		} else if ($act == 'update'){		// 項目更新の場合
			// 入力チェック
			$this->checkInput($name, '商品名');
			$this->checkInput($code, '商品コード');
			$this->checkNumeric($index, '表示順');
			$this->checkNumericF($unitQuantity, '数量');
			$this->checkNumeric($stockCount, '表示在庫数');
			$this->checkNumericF($price, '商品価格');
			if (empty($this->unitTypeId)) $this->setUserErrorMsg('販売単位が選択されていません');
			
			// エラーなしの場合は、データを登録
			if ($this->getMsgCount() == 0){
				// ##### 格納値を作成 #####
				// 商品情報その他
				$otherParams = array();
				$otherParams['pt_name'] = $name;			// 商品名
				$otherParams['pt_code'] = $code;			// 商品コード
				$otherParams['pt_sort_order'] = $index;			// 表示順
				$otherParams['pt_visible'] = $visible;		// 表示するかどうか
				$otherParams['pt_product_type'] = 1;				// 商品種別(1=単品商品、2=セット商品、3=オプション商品)
				$otherParams['pt_unit_type_id'] = $this->unitTypeId;	// 選択単位
				$otherParams['pt_unit_quantity'] = $unitQuantity;		// 数量
				$otherParams['pt_description'] = $description;		// 説明
				$otherParams['pt_description_short'] = $description_short;	// 簡易説明
				//$otherParams['pt_search_keyword'] = $keyword;		// 検索キーワード
				$otherParams['pt_meta_keywords'] = $metaKeyword;	// METAタグ用キーワード
				$otherParams['pt_site_url'] = $url;			// 詳細情報URL
				$otherParams['pt_tax_type_id'] = $this->taxType;	// 税種別
				$otherParams['pt_admin_note'] = $adminNote;		// 管理者用備考
				$otherParams['pt_deliv_type'] = $delivType;		// 配送タイプ
				$otherParams['pt_deliv_fee'] = $delivPrice;		// 配送単価
				$otherParams['pt_weight'] = $delivWeight;		// 配送基準重量
		
				// 価格情報の作成
				$priceArray = array();
				$startDt = $this->gEnv->getInitValueOfTimestamp();
				$endDt = $this->gEnv->getInitValueOfTimestamp();
				$priceArray[] = array(self::STANDARD_PRICE, $this->currency, $price, $startDt, $endDt);		// 単品商品で追加
				
				// 画像情報の作成
				// 商品情報取得
				$oldImageUrlArray = array();		// 旧画像
				$ret = $this->db->getProductBySerial($this->serialNo, $row, $row2, $row3, $row4, $row5);
				if ($ret){
					$productId = $row['pt_id'];	// 商品ID
					
					// 画像を取得
					$imageArray = $this->getImage($row3, self::PRODUCT_IMAGE_SMALL);// 商品画像小
					$imageUrl_s = $imageArray['im_url'];	// URL
					$imageArray = $this->getImage($row3, self::PRODUCT_IMAGE_MEDIUM);// 商品画像中
					$imageUrl_m = $imageArray['im_url'];	// URL
					$imageArray = $this->getImage($row3, self::PRODUCT_IMAGE_LARGE);// 商品画像大
					$imageUrl_l = $imageArray['im_url'];	// URL
				}
				$imageKeys = array_keys($this->sessionParamObj->imageFile);
				for ($i = 0; $i < count($imageKeys); $i++){
					switch ($imageKeys[$i]){
						case 's':
							if (!empty($imageUrl_s)) $oldImageUrlArray[] = $imageUrl_s;	// URL
							
							$imageFilename = $productId . '_' . $imageFormat_s;
							$imageUrl_s = str_replace($this->gEnv->getRootUrl(), M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $this->getProductImageUrl($imageFilename));
							$imagePath_s = $this->getProductImagePath($imageFilename);
							break;
						case 'm':
							if (!empty($imageUrl_m)) $oldImageUrlArray[] = $imageUrl_m;	// URL
							
							$imageFilename = $productId . '_' . $imageFormat_m;
							$imageUrl_m = str_replace($this->gEnv->getRootUrl(), M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $this->getProductImageUrl($imageFilename));
							$imagePath_m = $this->getProductImagePath($imageFilename);
							break;
						case 'l':
							if (!empty($imageUrl_l)) $oldImageUrlArray[] = $imageUrl_l;	// URL
							
							$imageFilename = $productId . '_' . $imageFormat_l;
							$imageUrl_l = str_replace($this->gEnv->getRootUrl(), M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $this->getProductImageUrl($imageFilename));
							$imagePath_l = $this->getProductImagePath($imageFilename);
							break;
					}
				}
				// 配列に格納
				$imageArray = array();
				if (!empty($imageUrl_s)) $imageArray[] = array(self::PRODUCT_IMAGE_SMALL, '', $imageUrl_s);
				if (!empty($imageUrl_m)) $imageArray[] = array(self::PRODUCT_IMAGE_MEDIUM, '', $imageUrl_m);
				if (!empty($imageUrl_l)) $imageArray[] = array(self::PRODUCT_IMAGE_LARGE, '', $imageUrl_l);
				
				// 商品ステータス情報の作成
				$statusArray = array();
				$statusArray[] = array(self::PRODUCT_STATUS_NEW, $new);		// 新着
				$statusArray[] = array(self::PRODUCT_STATUS_SUGGEST, $suggest);		// おすすめ
				
				// 商品情報を取得
				//$ret = $this->db->getProductBySerial($this->serialNo, $row, $row2, $row3, $row4, $row5);
				//if ($ret) $productId = $row['pt_id'];	// 商品ID
				if ($ret) $ret = $this->db->updateProduct($this->serialNo, $productId, $this->langId, $otherParams, 
												$stockCount, $priceArray, $imageArray, $statusArray, $this->categoryArray, $newSerial);
				if ($ret){
					$this->setGuidanceMsg('データを更新しました');
					
					// 旧画像を削除
					for ($i = 0; $i < count($oldImageUrlArray); $i++){
						$oldImageFile = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $this->gEnv->getSystemRootPath(), $oldImageUrlArray[$i]);
						if (strStartsWith($oldImageFile, $this->gEnv->getResourcePath() . self::PRODUCT_IMAGE_DIR)){	// 商品画像ディレクトリの場合のみ削除
							if (file_exists($oldImageFile)) unlink($oldImageFile);
						}
					}
					
					// 画像ファイルの移動
					$imageDir = $this->getProductImagePath();
					if (!file_exists($imageDir)) mkdir($imageDir, M3_SYSTEM_DIR_PERMISSION, true/*再帰的*/);		// ディレクトリ作成
					for ($i = 0; $i < count($imageKeys); $i++){
						$imageFile = $this->sessionParamObj->imageFile[$imageKeys[$i]];
						switch ($imageKeys[$i]){
							case 's':
								renameFile($imageFile, $imagePath_s);
								break;
							case 'm':
								renameFile($imageFile, $imagePath_m);
								break;
							case 'l':
								renameFile($imageFile, $imagePath_l);
								break;
						}
					}
					// セッションの画像情報をクリア
					$this->resetUploadImage();
					
					$this->serialNo = $newSerial;
					$reloadData = true;		// データ再取得
					
					// 運用ログを残す
					$ret = $this->db->getProductBySerial($this->serialNo, $row, $row2, $row3, $row4, $row5);
					//if ($ret) $this->gOpeLog->writeUserInfo(__METHOD__, '商品を更新しました。ID: ' . $row['pt_id'], 2100, '商品名=' . $row['pt_name']);
					if ($ret) $this->gOpeLog->writeUserInfo(__METHOD__, sprintf(self::LOG_MSG_UPDATE_CONTENT, $row['pt_name']), 2100, 'ID=' . $row['pt_id']);
				} else {
					$this->setAppErrorMsg('データ更新に失敗しました');
				}
			}
		} else if ($act == 'delete'){		// 項目削除の場合
			if (empty($this->serialNo)){
				$this->setUserErrorMsg('削除項目が選択されていません');
			}
			// エラーなしの場合は、データを削除
			if ($this->getMsgCount() == 0){
				// 商品情報を取得
				$ret = $this->db->getProductBySerial($this->serialNo, $row, $row2, $row3, $row4, $row5);

				if ($ret) $ret = $this->db->delProduct(array($this->serialNo));
				if ($ret){		// データ削除成功のとき
					// 商品画像を削除
					$imageUrlArray = array();
					$imageArray = $this->getImage($row3, self::PRODUCT_IMAGE_SMALL);// 商品画像小
					if (!empty($imageArray['im_url'])) $imageUrlArray[] = $imageArray['im_url'];	// URL
					$imageArray = $this->getImage($row3, self::PRODUCT_IMAGE_MEDIUM);// 商品画像中
					if (!empty($imageArray['im_url'])) $imageUrlArray[] = $imageArray['im_url'];	// URL
					$imageArray = $this->getImage($row3, self::PRODUCT_IMAGE_LARGE);// 商品画像大
					if (!empty($imageArray['im_url'])) $imageUrlArray[] = $imageArray['im_url'];	// URL
					for ($i = 0; $i < count($imageUrlArray); $i++){
						$imageFile = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $this->gEnv->getSystemRootPath(), $imageUrlArray[$i]);
						if (strStartsWith($imageFile, $this->gEnv->getResourcePath() . self::PRODUCT_IMAGE_DIR)){	// 商品画像ディレクトリの場合のみ削除
							if (file_exists($imageFile)) unlink($imageFile);		
						}
					}
					
					$this->setGuidanceMsg('データを削除しました');
					
					// 運用ログを残す
					//$this->gOpeLog->writeUserInfo(__METHOD__, '商品を削除しました。ID: ' . $row['pt_id'], 2100, '商品名=' . $row['pt_name']);
					$this->gOpeLog->writeUserInfo(__METHOD__, sprintf(self::LOG_MSG_DEL_CONTENT, $row['pt_name']), 2100, 'ID=' . $row['pt_id']);
				} else {
					$this->setAppErrorMsg('データ削除に失敗しました');
				}
			}
		} else if ($act == 'deleteid'){		// ID項目削除の場合
			if (empty($this->serialNo)){
				$this->setUserErrorMsg('削除項目が選択されていません');
			}
			// エラーなしの場合は、データを削除
			if ($this->getMsgCount() == 0){
				$ret = $this->db->getProductBySerial($this->serialNo, $row, $row2, $row3, $row4, $row5);
				
				if ($ret) $ret = $this->db->delProductById($this->serialNo);
				if ($ret){		// データ削除成功のとき
					// 商品画像を削除
					$imageUrlArray = array();
					$imageArray = $this->getImage($row3, self::PRODUCT_IMAGE_SMALL);// 商品画像小
					if (!empty($imageArray['im_url'])) $imageUrlArray[] = $imageArray['im_url'];	// URL
					$imageArray = $this->getImage($row3, self::PRODUCT_IMAGE_MEDIUM);// 商品画像中
					if (!empty($imageArray['im_url'])) $imageUrlArray[] = $imageArray['im_url'];	// URL
					$imageArray = $this->getImage($row3, self::PRODUCT_IMAGE_LARGE);// 商品画像大
					if (!empty($imageArray['im_url'])) $imageUrlArray[] = $imageArray['im_url'];	// URL
					for ($i = 0; $i < count($imageUrlArray); $i++){
						$imageFile = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $this->gEnv->getSystemRootPath(), $imageUrlArray[$i]);
						if (strStartsWith($imageFile, $this->gEnv->getResourcePath() . self::PRODUCT_IMAGE_DIR)){		// 商品画像ディレクトリの場合のみ削除
							if (file_exists($imageFile)) unlink($imageFile);		
						}
					}
				
					$this->setGuidanceMsg('データを削除しました');
				} else {
					$this->setAppErrorMsg('データ削除に失敗しました');
				}
			}
		} else if ($act == 'uploadfile'){		// 画像ファイルアップロード
			$uploader = new qqFileUploader(array());
			$tmpDir = $this->gEnv->getTempDirBySession();		// セッション単位の作業ディレクトリを取得
			$resultObj = $uploader->handleUpload($tmpDir);

			if ($resultObj['success']){
				$fileInfo = $resultObj['file'];
				
				// 画像タイプから作成する画像フォーマットを取得
				$index = array_search($imageType, $this->imageTypes);
				if ($index !== FALSE){		// 単一画像の更新のとき
					$imageFormatArray = explode(';', $imageFormat);
					$imageFormat = $imageFormatArray[$index];
				}

				$ret = $this->gInstance->getImageManager()->createImageByFormat($fileInfo['path'], $imageFormat, dirname($fileInfo['path']), $fileInfo['fileid'], $destFilename);
				if ($ret){
					// 新規画像を登録
					$images = array();			// 作成した画像のURL
					if ($index !== FALSE){		// 単一画像の更新のとき
						// アップロードファイルクリア
						$this->resetUploadImage($imageType);
						
						//$this->sessionParamObj->uploadFile[$imageType] = $fileInfo['path'];		// アップロードしたファイル
						$this->sessionParamObj->imageFile[$imageType] = dirname($fileInfo['path']) . DIRECTORY_SEPARATOR . $destFilename[0];		// 変換したファイル
						$images[] = $this->getImageUrl($imageType);
					} else {		// 全画像を更新のとき
						// アップロードファイルクリア
						$this->resetUploadImage();
			
						for ($i = 0; $i < count($this->imageTypes); $i++){
							$imageType = $this->imageTypes[$i];
							//$this->sessionParamObj->uploadFile[$imageType] = $fileInfo['path'];		// アップロードしたファイル
							$this->sessionParamObj->imageFile[$imageType] = dirname($fileInfo['path']) . DIRECTORY_SEPARATOR . $destFilename[$i];		// 変換したファイル
							$images[] = $this->getImageUrl($imageType);
						}
					}
					// アップロードしたファイルを削除
					unlink($fileInfo['path']);
						
					$this->setWidgetSessionObj($this->sessionParamObj);		// セッションを更新
					$resultObj['images'] = $images;
				} else {			// 画像作成失敗のとき
					unlink($fileInfo['path']);
					$resultObj = array('error' => 'Could not create file information.');
				}
			}
			// ##### 添付ファイルアップロード結果を返す #####
			// ページ作成処理中断
			$this->gPage->abortPage();
			
			// 添付ファイルの登録データを返す
			if (function_exists('json_encode')){
				$destStr = json_encode($resultObj);
			} else {
				$destStr = $this->gInstance->getAjaxManager()->createJsonString($resultObj);
			}
			//$destStr = htmlspecialchars($destStr, ENT_NOQUOTES);		// 「&」が「&amp;」に変換されるので使用しない
			//header('Content-type: application/json; charset=utf-8');
			header('Content-Type: text/html; charset=UTF-8');		// JSONタイプを指定するとIE8で動作しないのでHTMLタイプを指定
			echo $destStr;
			
			// システム強制終了
			$this->gPage->exitSystem();
		} else if ($act == 'getimage'){		// 画像取得
			if (empty($this->sessionParamObj->imageFile[$imageType])) return;
			
			// ページ作成処理中断
			$this->gPage->abortPage();
		
			$ret = $this->gPage->downloadFile($this->sessionParamObj->imageFile[$imageType], basename($this->sessionParamObj->imageFile[$imageType]));
		
			// システム強制終了
			$this->gPage->exitSystem();
		} else {	// 初期表示
			$reloadData = true;			// データを再取得

			// アップロードファイル初期化
			$this->resetUploadImage();
		}
		if ($reloadData){		// データ再取得のとき
			if (empty($this->serialNo)){
				// 入力値初期化
				$productId = 0;
				$this->langId = $defaultLang;
				$name = '';		// 名前
				$code = '';		// 商品コード
				//$index = '';	// 表示順
				$index = $this->db->getMaxIndex($this->langId) + 1;	// 表示順
				$visible = 1;	// 表示状態
				$new = 0;// 新規
				$suggest = 0;// おすすめ
				$price = '';	// 価格
				$this->currency	= $this->ecObj->getDefaultCurrency();		// 通貨
				$this->unitTypeId = self::DEFAULT_UNIT_TYPE_ID;	// 単位
				$unitQuantity = 1;		// 数量
				$stockCount	= 0;		// 表示在庫数
				$delivType = '';		// 配送タイプ
				$delivPrice = 0;		// 配送単価
				$delivWeight = 0;		// 配送基準重量
				$description = '';			// 説明
				$description_short = '';		// 簡易説明
//				$keyword = '';					// 検索キーワード
				$metaKeyword = '';	// METAタグ用キーワード
				$url = '';							// 詳細情報URL
				$this->taxType	= self::DEFAULT_TAX_TYPE;		// 税種別
				$adminNote = '';		// 管理者用備考
				$updateUser = '';	// 更新者
				$updateDt = '';	// 更新日時	
				$imageUrl_s = '';		// 商品画像小
				$imageUrl_m = '';		// 商品画像中
				$imageUrl_l = '';		// 商品画像大
				$this->categoryArray = array();		// 商品カテゴリー			
			} else {
				// データ再取得
				$ret = $this->db->getProductBySerial($this->serialNo, $row, $row2, $row3, $row4, $row5);
				if ($ret){
					// 取得値を設定
					$productId = $row['pt_id'];	// 商品ID
					$this->langId = $row['pt_language_id'];
					$name = $row['pt_name'];		// 名前
					$code = $row['pt_code'];		// 商品コード
					$index = $row['pt_sort_order'];	// 表示順
					$visible = $row['pt_visible'];	// 表示状態
					$this->unitTypeId = $row['pt_unit_type_id'];	// 単位
					$unitQuantity = $row['pt_unit_quantity'];		// 数量
					$stockCount	= $row['pe_stock_count'];		// 表示在庫数
					if (empty($stockCount)) $stockCount = 0;
					$delivType = $row['pt_deliv_type'];		// 配送タイプ
					$delivPrice = $row['pt_deliv_fee'];		// 配送単価
					$delivWeight = $row['pt_weight'];		// 配送基準重量
					$description = $row['pt_description'];			// 説明
					$description_short = $row['pt_description_short'];		// 簡易説明
					//$keyword = $row['pt_search_keyword'];					// 検索キーワード
					$metaKeyword = $row['pt_meta_keywords'];	// METAタグ用キーワード
					$url = $row['pt_site_url'];							// 詳細情報URL
					$this->taxType = $row['pt_tax_type_id'];					// 税種別
					$adminNote = $row['pt_admin_note'];		// 管理者用備考
					$updateUser = $this->convertToDispString($row['lu_name']);	// 更新者
					$updateDt = $this->convertToDispDateTime($row['pt_create_dt']);	// 更新日時
				
					// 価格を取得
					$priceArray = $this->getPrice($row2, self::STANDARD_PRICE);
					$price = $priceArray['pp_price'];	// 価格
					$this->currency = $priceArray['pp_currency_id'];	// 通貨
				
					// 画像を取得
					$imageArray = $this->getImage($row3, self::PRODUCT_IMAGE_SMALL);// 商品画像小
					$imageUrl_s = $imageArray['im_url'];	// URL
					$imageArray = $this->getImage($row3, self::PRODUCT_IMAGE_MEDIUM);// 商品画像中
					$imageUrl_m = $imageArray['im_url'];	// URL
					$imageArray = $this->getImage($row3, self::PRODUCT_IMAGE_LARGE);// 商品画像大
					$imageUrl_l = $imageArray['im_url'];	// URL
									
					// 商品ステータスを取得
					$statusArray = $this->getStatus($row4, self::PRODUCT_STATUS_NEW);// 新規
					$new = $statusArray['ps_value'];
					$statusArray = $this->getStatus($row4, self::PRODUCT_STATUS_SUGGEST);// おすすめ
					$suggest = $statusArray['ps_value'];
				
					// 商品カテゴリー取得
					$this->categoryArray = $this->getCategory($row5);
				}
			}
		} else {	// データ再取得しないとき
			// ##### 画像の再設定 #####
			// データ再取得
			$ret = $this->db->getProductBySerial($this->serialNo, $row, $row2, $row3, $row4, $row5);
			if ($ret){
				$imageArray = $this->getImage($row3, self::PRODUCT_IMAGE_SMALL);// 商品画像小
				$imageUrl_s = $imageArray['im_url'];	// URL
				$imageArray = $this->getImage($row3, self::PRODUCT_IMAGE_MEDIUM);// 商品画像中
				$imageUrl_m = $imageArray['im_url'];	// URL
				$imageArray = $this->getImage($row3, self::PRODUCT_IMAGE_LARGE);// 商品画像大
				$imageUrl_l = $imageArray['im_url'];	// URL
			}
			// セッションに残っている画像があれば上書き
			$imageKeys = array_keys($this->sessionParamObj->imageFile);
			for ($i = 0; $i < count($imageKeys); $i++){
				switch ($imageKeys[$i]){
					case 's':
						$imageUrl_s = $this->getImageUrl($imageKeys[$i]);
						break;
					case 'm':
						$imageUrl_m = $this->getImageUrl($imageKeys[$i]);
						break;
					case 'l':
						$imageUrl_l = $this->getImageUrl($imageKeys[$i]);
						break;
				}
			}
		}
		
		// カテゴリーメニューを作成
		$this->db->getAllCategory($defaultLang, $this->categoryListData);
		$this->createCategoryMenu();
		
		// 各種価格を求める
		$price = $this->ecObj->getCurrencyPrice($price);	// 端数調整
		$this->ecObj->setCurrencyType($this->currency, $this->langId);		// 通貨設定
		$this->ecObj->setTaxType($this->taxType, $this->langId);		// 税種別設定
		$totalPrice = $this->ecObj->getPriceWithTax($price, $dispPrice);	// 税込み価格取得
		$delivPrice = $this->ecObj->getCurrencyPrice($delivPrice);	// 端数調整
		
		// 商品一覧へ戻す値
		$this->tmpl->addVar("_widget", "sort", $sort);		// ソート順
		
		$this->tmpl->addVar("_widget", "name", $name);		// 名前
		$this->tmpl->addVar("_widget", "code", $code);		// 商品コード
		$this->tmpl->addVar("_widget", "index", $index);		// 表示順
		$this->tmpl->addVar("_widget", "unit_quantity", $unitQuantity);		// 数量
		$this->tmpl->addVar("_widget", "stock_count", $stockCount);		// 表示在庫数
		$this->tmpl->addVar("_widget", "deliv_type", $delivType);		// 配送タイプ
		$this->tmpl->addVar("_widget", "deliv_price", $delivPrice);		// 配送単価
		$this->tmpl->addVar("_widget", "deliv_weight", $delivWeight);		// 配送基準重量
		$this->tmpl->addVar("_widget", "description", $description);		// 説明
		$this->tmpl->addVar("_widget", "desc_short", $description_short);		// 簡易説明		
		//$this->tmpl->addVar("_widget", "keyword", $keyword);		// 検索キーワード
		$this->tmpl->addVar("_widget", "meta_keyword", $metaKeyword);	// METAタグ用キーワード
		$this->tmpl->addVar("_widget", "url", $url);				// 詳細情報URL
		$this->tmpl->addVar("_widget", "admin_note", $adminNote);			// 管理者用備考
		$this->tmpl->addVar("_widget", "price", $price);		// 価格
		$this->tmpl->addVar("_widget", "price_with_tax", $dispPrice);		// 税込価格
		
		$visibleStr = '';
		if ($visible){	// 項目の表示
			$visibleStr = 'checked';
		}
		$this->tmpl->addVar("_widget", "visible", $visibleStr);		// 表示状態
		$visibleStr = '';
		if ($new){	// 新規
			$visibleStr = 'checked';
		}
		$this->tmpl->addVar("_widget", "new", $visibleStr);
		$visibleStr = '';
		if ($suggest){	// おすすめ
			$visibleStr = 'checked';
		}
		$this->tmpl->addVar("_widget", "suggest", $visibleStr);
		if (!empty($updateUser)) $this->tmpl->addVar("_widget", "update_user", $updateUser);	// 更新者
		if (!empty($updateDt)) $this->tmpl->addVar("_widget", "update_dt", $updateDt);	// 更新日時

		// 単位タイプ選択メニュー作成
		$this->db->getUnitType($defaultLang, array($this, 'unitTypeLoop'));

		// 通貨タイプ選択メニュー作成
		if ($this->gEnv->getMultiLanguage()){	// 多言語対応のとき
			$this->db->getCurrency($defaultLang, array($this, 'currencyLoop'));
		}
		// 課税タイプ選択メニューの作成
		$this->db->getTaxType($defaultLang, array($this, 'taxTypeLoop'));

		// 商品画像プレビューの作成
		// 画像小
		$imgUrl = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $this->gEnv->getRootUrl(), $imageUrl_s) . '?' . date('YmdHis');
		$this->tmpl->addVar("_widget", "image_s", $this->getUrl($imgUrl));
		// 画像中
		$imgUrl = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $this->gEnv->getRootUrl(), $imageUrl_m) . '?' . date('YmdHis');
		$this->tmpl->addVar("_widget", "image_m", $this->getUrl($imgUrl));
		// 画像大
		$imgUrl = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $this->gEnv->getRootUrl(), $imageUrl_l) . '?' . date('YmdHis');
		$this->tmpl->addVar("_widget", "image_l", $this->getUrl($imgUrl));
		
		$this->tmpl->addVar("_widget", "image_s_width", $defaultImageSWidth);		// 商品画像小の幅
		$this->tmpl->addVar("_widget", "image_s_height", $defaultImageSHeight);		// 商品画像小の高さ
		$this->tmpl->addVar("_widget", "image_m_width", $defaultImageMWidth);		// 商品画像中の幅
		$this->tmpl->addVar("_widget", "image_m_height", $defaultImageMHeight);		// 商品画像中の高さ
		$this->tmpl->addVar("_widget", "image_l_width", $defaultImageLWidth);		// 商品画像大の幅
		$this->tmpl->addVar("_widget", "image_l_height", $defaultImageLHeight);		// 商品画像大の高さ

		// アップロード実行用URL
		$this->tmpl->addVar("_widget", "upload_url_s", $this->getUrl($this->getUploadImageUrl('s')));
		$this->tmpl->addVar("_widget", "upload_url_m", $this->getUrl($this->getUploadImageUrl('m')));
		$this->tmpl->addVar("_widget", "upload_url_l", $this->getUrl($this->getUploadImageUrl('l')));
		$this->tmpl->addVar("_widget", "upload_url_all", $this->getUrl($this->getUploadImageUrl('all')));
		
		// ウィンドウ閉じるアイコンを設定
		$iconUrl = $this->gEnv->getRootUrl() . self::UPLOAD_ICON_FILE;
		$this->tmpl->addVar("_widget", "upload_image", $this->getUrl($iconUrl));
				
		// ボタンの設定
		if (empty($this->serialNo)){		// 新規追加項目を選択しているとき
			$this->tmpl->addVar("_widget", "id", '新規');
			
			$this->tmpl->setAttribute('add_button', 'visibility', 'visible');// 「新規追加」ボタン
		} else {
			$this->tmpl->addVar("_widget", "id", $productId);
			
			// データ更新、削除ボタン表示
			if ($this->langId == $defaultLang){		// デフォルト言語のときは「ID削除」ボタン
				$this->tmpl->setAttribute('delete_id_button', 'visibility', 'visible');// ID削除ボタン
			} else {
				$this->tmpl->setAttribute('delete_button', 'visibility', 'visible');// 削除ボタン
			}
			$this->tmpl->setAttribute('update_button', 'visibility', 'visible');
		}
		
		// 非表示項目の設定
		$this->tmpl->addVar("_widget", "serial", $this->serialNo);
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
		// データ再取得
		$ret = $this->db->getProductBySerial($fetchedRow['pt_serial'], $row, $row2, $row3, $row4, $row5);
		if ($ret){
			$langId = $row['pt_language_id'];
			
			// 価格を取得
			$priceArray = $this->getPrice($row2, self::STANDARD_PRICE);
			$price = $priceArray['pp_price'];	// 価格
			$currency = $priceArray['pp_currency_id'];	// 通貨
			$taxType = $row['pt_tax_type_id'];					// 税種別			

			// 価格作成
			$this->ecObj->setCurrencyType($currency, $langId);		// 通貨設定
			$this->ecObj->setTaxType($taxType, $langId);		// 税種別設定
			$totalPrice = $this->ecObj->getPriceWithTax($price, $dispPrice);	// 税込み価格取得
			if (empty($taxType)) $dispPrice = '(未)';		// 税種別未選択のとき
			
			$visible = '';
			if ($row['pt_visible']){	// 項目の表示
				$visible = 'checked';
			}
		}
		$stockCount = $fetchedRow['pe_stock_count'];// 表示在庫数
		if (empty($stockCount)) $stockCount = 0;
		
		$row = array(
			'index' => $index,													// 項目番号
			'no' => $index + 1,													// 行番号
			'serial' => $this->convertToDispString($fetchedRow['pt_serial']),	// シリアル番号
			'id' => $row['pt_id'],			// ID
			'name' => $this->convertToDispString($row['pt_name']),		// 名前
			'code' => $this->convertToDispString($row['pt_code']),		// 商品コード
			'price' => $dispPrice,													// 価格(税込)
			'stock_count' => $stockCount,		// 表示在庫数
			'view_index' => $this->convertToDispString($row['pt_sort_order']),		// 表示順
			'update_user' => $this->convertToDispString($row['lu_name']),	// 更新者
	//		'update_dt' => $this->convertToDispDateTime($row['pt_create_dt']),	// 更新日時
			'update_dt' => $this->convertToDispDateTime($fetchedRow['pt_create_dt'], 0, 10/*時分表示*/),// 更新日時
			'visible' => $visible											// メニュー項目表示制御
		);
		$this->tmpl->addVars('itemlist', $row);
		$this->tmpl->parseTemplate('itemlist', 'a');
		
		// 表示中の項目のシリアル番号を保存
		$this->serialArray[] = $fetchedRow['pt_serial'];
		return true;
	}
	/**
	 * 取得した通貨種別をテンプレートに設定する
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function currencyLoop($index, $fetchedRow, $param)
	{
		$selected = '';
		if ($fetchedRow['cu_id'] == $this->currency){
			$selected = 'selected';
		}
		$name = $this->convertToDispString($fetchedRow['cu_name']);

		$row = array(
			'value'    => $this->convertToDispString($fetchedRow['cu_id']),			// 言語ID
			'name'     => $name,			// 言語名
			'selected' => $selected														// 選択中かどうか
		);
		$this->tmpl->addVars('currency_list', $row);
		$this->tmpl->parseTemplate('currency_list', 'a');
		return true;
	}
	/**
	 * 取得した単位をテンプレートに設定する
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function unitTypeLoop($index, $fetchedRow, $param)
	{
		$selected = '';
		if ($fetchedRow['ut_id'] == $this->unitTypeId){
			$selected = 'selected';
		}

		$row = array(
			'value'    => $this->convertToDispString($fetchedRow['ut_id']),				// 単位ID
			'name'     => $this->convertToDispString($fetchedRow['ut_name']),			// 単位名
		//	'name'     => $fetchedRow['ut_name'],			// 単位名
			'selected' => $selected														// 選択中かどうか
		);
		$this->tmpl->addVars('unit_type_list', $row);
		$this->tmpl->parseTemplate('unit_type_list', 'a');
		return true;
	}
	/**
	 * 取得した課税種別をテンプレートに設定する
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function taxTypeLoop($index, $fetchedRow, $param)
	{
		$selected = '';
		if ($fetchedRow['tt_id'] == $this->taxType){
			$selected = 'selected';
		}

		$row = array(
			'value'    => $this->convertToDispString($fetchedRow['tt_id']),				// 単位ID
			'name'     => $this->convertToDispString($fetchedRow['tt_name']),			// 単位名
			'selected' => $selected														// 選択中かどうか
		);
		$this->tmpl->addVars('tax_type_list', $row);
		$this->tmpl->parseTemplate('tax_type_list', 'a');
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
			if ($srcRows[$i]['pp_price_type_id'] == $priceType){
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
	 * 商品カテゴリー取得
	 *
	 * @param array  	$srcRows			取得行
	 * @return array						取得した行
	 */
	function getCategory($srcRows)
	{
		$destArray = array();
		$itemCount = 0;
		for ($i = 0; $i < count($srcRows); $i++){
			if (!empty($srcRows[$i]['pw_category_id'])){
				$destArray[] = $srcRows[$i]['pw_category_id'];
				$itemCount++;
				if ($itemCount >= $this->catagorySelectCount) break;
			}
		}
		return $destArray;
	}
	/**
	 * 商品カテゴリーメニューを作成
	 *
	 * @return なし						
	 */
	function createCategoryMenu()
	{
		for ($j = 0; $j < $this->catagorySelectCount; $j++){
			// selectメニューの作成
			$this->tmpl->clearTemplate('category_list');
			for ($i = 0; $i < count($this->categoryListData); $i++){
				$categoryId = $this->categoryListData[$i]['pc_id'];
				$selected = '';
				if ($j < count($this->categoryArray) && $this->categoryArray[$j] == $categoryId){
					$selected = 'selected';
				}
				$menurow = array(
					'value'		=> $categoryId,			// カテゴリーID
					'name'		=> $this->categoryListData[$i]['pc_name'],			// カテゴリー名
					'selected'	=> $selected														// 選択中かどうか
				);
				$this->tmpl->addVars('category_list', $menurow);
				$this->tmpl->parseTemplate('category_list', 'a');
			}
			$itemRow = array(		
					'index'		=> $j			// 項目番号											
			);
			$this->tmpl->addVars('category', $itemRow);
			$this->tmpl->parseTemplate('category', 'a');
		}
	}
	/**
	 * 取得したカテゴリーをテンプレートに設定する
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function categoryLoop($index, $fetchedRow, $param)
	{
		$selected = '';
		if ($fetchedRow['pc_id'] == $this->search_categoryId){
			$selected = 'selected';
		}
		$name = $this->convertToDispString($fetchedRow['pc_name']);

		$row = array(
			'value'    => $this->convertToDispString($fetchedRow['pc_id']),			// カテゴリーID
			'name'     => $name,			// カテゴリー名
			'selected' => $selected														// 選択中かどうか
		);
		$this->tmpl->addVars('category_list', $row);
		$this->tmpl->parseTemplate('category_list', 'a');
		return true;
	}
	/**
	 * アップロード画像初期化
	 *
	 * @param string $type			画像タイプ(空の場合はすべての画像)
	 * @return なし
	 */
	function resetUploadImage($type = '')
	{
		if (empty($type)){
/*			$keys = array_keys($this->sessionParamObj->uploadFile);
			for ($i = 0; $i < count($keys); $i++){
				$uploadFile = $this->sessionParamObj->uploadFile[$keys[$i]];
				if (file_exists($uploadFile)) unlink($uploadFile);
			}*/
			$keys = array_keys($this->sessionParamObj->imageFile);
			for ($i = 0; $i < count($keys); $i++){
				$imageFile = $this->sessionParamObj->imageFile[$keys[$i]];
				if (file_exists($imageFile)) unlink($imageFile);
			}
//			$this->sessionParamObj->uploadFile = array();		// アップロードしたファイル
			$this->sessionParamObj->imageFile = array();		// 画像ファイル
		} else {
//			$uploadFile = $this->sessionParamObj->uploadFile[$type];
//			if (file_exists($uploadFile)) unlink($uploadFile);
			$imageFile = $this->sessionParamObj->imageFile[$type];
			if (file_exists($imageFile)) unlink($imageFile);
			
//			unset($this->sessionParamObj->uploadFile[$type]);
			unset($this->sessionParamObj->imageFile[$type]);
		}
		$this->setWidgetSessionObj($this->sessionParamObj);
	}
	/**
	 * 画像アップロード用URL取得
	 *
	 * @param string $type			画像タイプ
	 * @return string				URL
	 */
	function getUploadImageUrl($type)
	{
		$urlparam  = M3_REQUEST_PARAM_OPERATION_COMMAND . '=' . M3_REQUEST_CMD_CONFIG_WIDGET;	// ウィジェット設定画面
		$urlparam .= '&' . M3_REQUEST_PARAM_WIDGET_ID . '=' . $this->gEnv->getCurrentWidgetId();	// ウィジェットID
		$urlparam .= '&' . M3_REQUEST_PARAM_OPERATION_TASK . '=' . self::CURRENT_TASK;
		$urlparam .= '&' . M3_REQUEST_PARAM_OPERATION_ACT . '=' . 'uploadfile';
		$urlparam .= '&imagetype=' . $type;
		$uploadUrl = $this->gEnv->getDefaultAdminUrl() . '?' . $urlparam;
		return $uploadUrl;
	}
	/**
	 * アップロード画像URL取得
	 *
	 * @param string $type			画像タイプ
	 * @return string				URL
	 */
	function getImageUrl($type)
	{
		// 画像URL
		$urlparam  = M3_REQUEST_PARAM_OPERATION_COMMAND . '=' . M3_REQUEST_CMD_CONFIG_WIDGET;	// ウィジェット設定画面
		$urlparam .= '&' . M3_REQUEST_PARAM_WIDGET_ID . '=' . $this->gEnv->getCurrentWidgetId();	// ウィジェットID
		$urlparam .= '&' . M3_REQUEST_PARAM_OPERATION_TASK . '=' . self::CURRENT_TASK;
		$urlparam .= '&' . M3_REQUEST_PARAM_OPERATION_ACT . '=' . 'getimage';
		$urlparam .= '&imagetype=' . $type . '&' . date('YmdHis');
		$imageUrl = $this->gEnv->getDefaultUrl() . '?' . $urlparam;
		return $imageUrl;
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
	 * 商品画像パス取得
	 *
	 * @param string $filename		ファイル名
	 * @return string				パス
	 */
	function getProductImagePath($filename = '')
	{
		return $this->gEnv->getResourcePath() . self::PRODUCT_IMAGE_DIR . $filename;
	}
}
?>
