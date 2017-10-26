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
 * @copyright  Copyright 2006-2017 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() . '/admin_ec_mainBaseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() .	'/ec_mainPhotoProductDb.php');

class admin_ec_mainPhotoproductWidgetContainer extends admin_ec_mainBaseWidgetContainer
{
	private $db;	// DB接続オブジェクト
	private $serialNo;			// シリアル番号
	private $serialArray = array();		// 表示されている項目のシリアル番号
	private $langId;			// 言語
	private $unitTypeId;		// 選択単位
	private $currency;			// 通貨
	private $defaultCurrencyId;	// デフォルトの通貨ID
	private $taxType;			// 税種別
	private $ecObj;			// 共通ECオブジェクト
	const MAX_HIER_LEVEL = 20;		// カテゴリー階層最大値
	const REGULAR_PRICE = 'regular';		// 通常価格
	const PRODUCT_IMAGE_MEDIUM = 'standard-product';		// 中サイズ商品画像ID
	const PRODUCT_IMAGE_SMALL = 'small-product';		// 小サイズ商品画像ID
	const PRODUCT_IMAGE_LARGE = 'large-product';		// 大サイズ商品画像ID
	const DEFAULT_TAX_TYPE = 'sales';			// デフォルト税種別
	const PRICE_OBJ_ID = "eclib";		// 価格計算オブジェクトID
	const DEFAULT_LIST_COUNT = 20;			// 最大リスト表示数
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->db = new ec_mainPhotoProductDb();
		
		$this->defaultCurrencyId = $this->ecObj->getDefaultCurrency();	// デフォルトの通貨ID
		
		// EC用共通オブジェクト取得
		$this->ecObj = $this->gInstance->getObject(self::PRICE_OBJ_ID);
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
		if ($task == 'photoproduct_detail'){		// 詳細画面
			return 'admin_photoproduct_detail.tmpl.html';
		} else {
			return 'admin_photoproduct.tmpl.html';
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
		$task = $request->trimValueOf('task');
		if ($task == 'photoproduct_detail'){	// 詳細画面
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
		$pageNo = $request->trimValueOf('page');				// ページ番号
		if (empty($pageNo)) $pageNo = 1;
		// DBの保存設定値を取得
		$maxListCount = self::DEFAULT_LIST_COUNT;				// 表示項目数
		
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
				for ($i = 0; $i < count($delItems); $i++){
					$ret = $this->db->getProductBySerial($delItems[$i], $row, $row2, $row3);
					if ($ret){
						$newInfoObj = new stdClass;
						$newInfoObj->id = $row['hp_id'];		// 商品ID
						$newInfoObj->name = $row['hp_name'];	// 商品名
						$delProductInfo[] = $newInfoObj;
					}
				}
				// 商品を削除
				$ret = $this->db->delProduct($delItems);
				if ($ret){		// データ削除成功のとき
					$this->setGuidanceMsg('データを削除しました');
					
					// 運用ログを残す
					for ($i = 0; $i < count($delProductInfo); $i++){
						$infoObj = $delProductInfo[$i];
						$this->gOpeLog->writeUserInfo(__METHOD__, 'フォト商品を削除しました。ID: ' . $infoObj->id, 2100, '商品名=' . $infoObj->name);
					}
				} else {
					$this->setAppErrorMsg('データ削除に失敗しました');
				}
			}
		} else if ($act == 'search'){		// 検索のとき
			$pageNo = 1;		// ページ番号初期化
		}
				
		// 総数を取得
		$parsedKeywords = array();
		$totalCount = $this->db->searchProductCount($parsedKeywords, $defaultLangId);
		$pageCount = (int)(($totalCount -1) / $maxListCount) + 1;		// 総ページ数

		// 表示するページ番号の修正
		if ($pageNo < 1) $pageNo = 1;
		if ($pageNo > $pageCount) $pageNo = $pageCount;

		// 商品リストを表示
		$this->db->searchProduct($parsedKeywords, $defaultLangId, $maxListCount, ($pageNo -1) * $maxListCount, array($this, 'productListLoop'));
		if (count($this->serialArray) <= 0) $this->tmpl->setAttribute('itemlist', 'visibility', 'hidden');// 項目がないときは、一覧を表示しない
		
		// ページング用リンク作成
		$pageLink = '';
		if ($pageCount > 1){	// ページが2ページ以上のときリンクを作成
			for ($i = 1; $i <= $pageCount; $i++){
				$linkUrl = $this->gEnv->getDefaultAdminUrl() . '?' . M3_REQUEST_PARAM_OPERATION_COMMAND . '=' . M3_REQUEST_CMD_CONFIG_WIDGET . 
				'&' . M3_REQUEST_PARAM_WIDGET_ID . '=' . $this->gEnv->getCurrentWidgetId() . '&task=photoproduct&page=' . $i;
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
			
		// その他
		$this->tmpl->addVar("_widget", "admin_url", $this->getUrl($this->gEnv->getDefaultAdminUrl()));
		$this->tmpl->addVar('_widget', 'widget_id', $this->gEnv->getCurrentWidgetId());
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
		
		$act = $request->trimValueOf('act');
		$this->serialNo = $request->trimValueOf('serial');			// 選択項目のシリアル番号
//		if (empty($this->serialNo)) $this->serialNo = 0;
//		$this->productId = $request->trimValueOf('productid');	// 商品ID
//		if (empty($this->productId)) $this->productId = 0;
		$this->currency = $request->trimValueOf('item_currency');		// 通貨
		if (empty($this->currency)) $this->currency	= $this->ecObj->getDefaultCurrency();		// 通貨
		
		// 編集中の項目
		$name	= $request->trimValueOf('item_name');		// 商品名
		$code	= $request->trimValueOf('item_code');		// 商品コード
		$index	= $request->trimValueOf('item_index');		// 表示順
		$visible = ($request->trimValueOf('item_visible') == 'on') ? 1 : 0;			// 表示するかどうか
		$this->unitTypeId = $request->trimValueOf('item_unit_type');		// 選択単位
		$unitQuantity = $request->trimValueOf('item_unit_quantity');		// 数量
		$delivType = $request->trimValueOf('item_deliv_type');		// 配送タイプ
		$delivPrice = $request->trimValueOf('item_deliv_price');		// 配送単価
		if (empty($delivPrice)) $delivPrice = 0;
		$delivWeight = $request->trimValueOf('item_deliv_weight');		// 配送基準重量
		if (empty($delivWeight)) $delivWeight = 0;
		$description = $request->valueOf('item_description');			// 説明
		$description_short = $request->trimValueOf('item_desc_short');		// 簡易説明
//		$keyword = $request->trimValueOf('item_keyword');					// 検索キーワード
		$metaKeyword = $request->trimValueOf('item_meta_keyword');	// METAタグ用キーワード
		$url = $request->trimValueOf('item_url');							// 詳細情報URL
		$this->taxType = $request->trimValueOf('item_tax_type');					// 税種別
		$adminNote = $request->trimValueOf('item_admin_note');		// 管理者用備考
		$price = $request->trimValueOf('item_price');		// 価格
		
		// 画像のパスをマクロ表記パスに直す
		$imageUrl_s = $request->trimValueOf('imageurl_s');		// 小画像
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
		}
		$this->langId = $request->trimValueOf('item_lang');				// 現在メニューで選択中の言語
		if (empty($this->langId)) $this->langId = $defaultLang;			// 言語が選択されていないときは、デフォルト言語を設定
	
		$reloadData = false;		// データ再取得するかどうか
		if ($act == 'select'){		// 項目選択の場合
			$reloadData = true;		// データ再取得
		} else if ($act == 'add'){		// 項目追加の場合
			// 入力チェック
			$this->checkInput($name, '商品名');
			$this->checkInput($code, '商品コード');
			$this->checkNumeric($index, '表示順');
			$this->checkNumericF($unitQuantity, '数量');
			$this->checkNumericF($price, '商品価格');
			if (empty($this->unitTypeId)) $this->setUserErrorMsg('販売単位が選択されていません');

			// エラーなしの場合は、データを登録
			if ($this->getMsgCount() == 0){
				// ##### 格納値を作成 #####
				// 商品情報その他
				$otherParams = array();
				$otherParams['hp_name'] = $name;			// 商品名
				$otherParams['hp_code'] = $code;			// 商品コード
				$otherParams['hp_sort_order'] = $index;			// 表示順
				$otherParams['hp_visible'] = $visible;		// 表示するかどうか
				$otherParams['hp_product_type'] = 1;				// 商品種別(1=単品商品、2=セット商品、3=オプション商品)
				$otherParams['hp_unit_type_id'] = $this->unitTypeId;	// 選択単位
				$otherParams['hp_unit_quantity'] = $unitQuantity;		// 数量
				$otherParams['hp_description'] = $description;		// 説明
				$otherParams['hp_description_short'] = $description_short;	// 簡易説明
//				$otherParams['hp_search_keyword'] = $keyword;		// 検索キーワード
				$otherParams['hp_meta_keywords'] = $metaKeyword;	// METAタグ用キーワード
				$otherParams['hp_site_url'] = $url;			// 詳細情報URL
				$otherParams['hp_tax_type_id'] = $this->taxType;	// 税種別
				$otherParams['hp_admin_note'] = $adminNote;		// 管理者用備考
				$otherParams['hp_deliv_type'] = $delivType;		// 配送タイプ
				$otherParams['hp_deliv_fee'] = $delivPrice;		// 配送単価
				$otherParams['hp_weight'] = $delivWeight;		// 配送基準重量
				
				// 価格情報の作成
				$priceArray = array();
				$startDt = $this->gEnv->getInitValueOfTimestamp();
				$endDt = $this->gEnv->getInitValueOfTimestamp();
				$priceArray[] = array(self::REGULAR_PRICE, $this->currency, $price, $startDt, $endDt);		// 単品商品で追加
				
				// 画像情報の作成
				$imageArray = array();
				if (!empty($imageUrl_s)) $imageArray[] = array(self::PRODUCT_IMAGE_SMALL, '', $imageUrl_s);		// 商品画像小追加
				if (!empty($imageUrl_m)) $imageArray[] = array(self::PRODUCT_IMAGE_MEDIUM, '', $imageUrl_m);		// 商品画像中追加
				if (!empty($imageUrl_l)) $imageArray[] = array(self::PRODUCT_IMAGE_LARGE, '', $imageUrl_l);		// 商品画像大追加
								
				// 商品ステータス情報の作成
				$ret = $this->db->updateProduct(0/*データ新規追加*/, 0/*商品ID新規作成*/, $this->langId, $otherParams, 
												$priceArray, $imageArray, $newSerial);
				if ($ret){
					$this->setGuidanceMsg('データを追加しました');
					
					// シリアル番号更新
					$this->serialNo = $newSerial;
					$reloadData = true;		// データ再取得
					
					// 運用ログを残す
					$ret = $this->db->getProductBySerial($this->serialNo, $row, $row2, $row3);
					if ($ret) $this->gOpeLog->writeUserInfo(__METHOD__, 'フォト商品を追加しました。ID: ' . $row['hp_id'], 2100, '商品名=' . $row['hp_name']);
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
			$this->checkNumericF($price, '商品価格');
			if (empty($this->unitTypeId)) $this->setUserErrorMsg('販売単位が選択されていません');
			
			// エラーなしの場合は、データを登録
			if ($this->getMsgCount() == 0){
				// ##### 格納値を作成 #####
				// 商品情報その他
				$otherParams = array();
				$otherParams['hp_name'] = $name;			// 商品名
				$otherParams['hp_code'] = $code;			// 商品コード
				$otherParams['hp_sort_order'] = $index;			// 表示順
				$otherParams['hp_visible'] = $visible;		// 表示するかどうか
				$otherParams['hp_product_type'] = 1;				// 商品種別(1=単品商品、2=セット商品、3=オプション商品)
				$otherParams['hp_unit_type_id'] = $this->unitTypeId;	// 選択単位
				$otherParams['hp_unit_quantity'] = $unitQuantity;		// 数量
				$otherParams['hp_description'] = $description;		// 説明
				$otherParams['hp_description_short'] = $description_short;	// 簡易説明
//				$otherParams['hp_search_keyword'] = $keyword;		// 検索キーワード
				$otherParams['hp_meta_keywords'] = $metaKeyword;	// METAタグ用キーワード
				$otherParams['hp_site_url'] = $url;			// 詳細情報URL
				$otherParams['hp_tax_type_id'] = $this->taxType;	// 税種別
				$otherParams['hp_admin_note'] = $adminNote;		// 管理者用備考
				$otherParams['hp_deliv_type'] = $delivType;		// 配送タイプ
				$otherParams['hp_deliv_fee'] = $delivPrice;		// 配送単価
				$otherParams['hp_weight'] = $delivWeight;		// 配送基準重量
		
				// 価格情報の作成
				$priceArray = array();
				$startDt = $this->gEnv->getInitValueOfTimestamp();
				$endDt = $this->gEnv->getInitValueOfTimestamp();
				$priceArray[] = array(self::REGULAR_PRICE, $this->currency, $price, $startDt, $endDt);		// 単品商品で追加
				
				// 画像情報の作成
				$imageArray = array();
				if (!empty($imageUrl_s)) $imageArray[] = array(self::PRODUCT_IMAGE_SMALL, '', $imageUrl_s);		// 商品画像小追加
				if (!empty($imageUrl_m)) $imageArray[] = array(self::PRODUCT_IMAGE_MEDIUM, '', $imageUrl_m);		// 商品画像中追加
				if (!empty($imageUrl_l)) $imageArray[] = array(self::PRODUCT_IMAGE_LARGE, '', $imageUrl_l);		// 商品画像大追加
				
				// 商品情報を取得
				$ret = $this->db->getProductBySerial($this->serialNo, $row, $row2, $row3);
				if ($ret) $productId = $row['hp_id'];	// 商品ID

				if ($ret) $ret = $this->db->updateProduct($this->serialNo, $productId, $this->langId, $otherParams, 
												$priceArray, $imageArray, $newSerial);
				if ($ret){
					$this->setGuidanceMsg('データを更新しました');
					
					$this->serialNo = $newSerial;
					$reloadData = true;		// データ再取得
					
					// 運用ログを残す
					$ret = $this->db->getProductBySerial($this->serialNo, $row, $row2, $row3);
					if ($ret) $this->gOpeLog->writeUserInfo(__METHOD__, 'フォト商品を更新しました。ID: ' . $row['hp_id'], 2100, '商品名=' . $row['hp_name']);
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
				$ret = $this->db->getProductBySerial($this->serialNo, $row, $row2, $row3);

				if ($ret) $ret = $this->db->delProduct(array($this->serialNo));
				if ($ret){		// データ削除成功のとき
					$this->setGuidanceMsg('データを削除しました');
					
					// 運用ログを残す
					$this->gOpeLog->writeUserInfo(__METHOD__, 'フォト商品を削除しました。ID: ' . $row['hp_id'], 2100, '商品名=' . $row['hp_name']);
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
				$ret = $this->db->delProductById($this->serialNo);
				if ($ret){		// データ削除成功のとき
					$this->setGuidanceMsg('データを削除しました');
				} else {
					$this->setAppErrorMsg('データ削除に失敗しました');
				}
			}
		} else {	// 初期表示
			$reloadData = true;			// データを再取得
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
				$price = '';	// 価格
				$this->currency	= $this->ecObj->getDefaultCurrency();		// 通貨
				$this->unitTypeId = '';	// 単位
				$unitQuantity = 1;		// 数量
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
			} else {
				// データ再取得
				$ret = $this->db->getProductBySerial($this->serialNo, $row, $row2, $row3);
				if ($ret){
					// 取得値を設定
					$productId = $row['hp_id'];	// 商品ID
					$this->langId = $row['hp_language_id'];
					$name = $row['hp_name'];		// 名前
					$code = $row['hp_code'];		// 商品コード
					$index = $row['hp_sort_order'];	// 表示順
					$visible = $row['hp_visible'];	// 表示状態
					$this->unitTypeId = $row['hp_unit_type_id'];	// 単位
					$unitQuantity = $row['hp_unit_quantity'];		// 数量
					$delivType = $row['hp_deliv_type'];		// 配送タイプ
					$delivPrice = $row['hp_deliv_fee'];		// 配送単価
					$delivWeight = $row['hp_weight'];		// 配送基準重量
					$description = $row['hp_description'];			// 説明
					$description_short = $row['hp_description_short'];		// 簡易説明
//					$keyword = $row['hp_search_keyword'];					// 検索キーワード
					$metaKeyword = $row['hp_meta_keywords'];	// METAタグ用キーワード
					$url = $row['hp_site_url'];							// 詳細情報URL
					$this->taxType = $row['hp_tax_type_id'];					// 税種別
					$adminNote = $row['hp_admin_note'];		// 管理者用備考
					$updateUser = $this->convertToDispString($row['lu_name']);	// 更新者
					$updateDt = $this->convertToDispDateTime($row['hp_create_dt']);	// 更新日時
				
					// 価格を取得
					$priceArray = $this->getPrice($row2, self::REGULAR_PRICE, $this->defaultCurrencyId);
					$price = $priceArray['pp_price'];	// 価格
					$this->currency = $priceArray['pp_currency_id'];	// 通貨
				
					// 画像を取得
					$imageArray = $this->getImage($row3, self::PRODUCT_IMAGE_SMALL);// 商品画像小
					$imageUrl_s = $imageArray['im_url'];	// URL
					$imageArray = $this->getImage($row3, self::PRODUCT_IMAGE_MEDIUM);// 商品画像中
					$imageUrl_m = $imageArray['im_url'];	// URL
					$imageArray = $this->getImage($row3, self::PRODUCT_IMAGE_LARGE);// 商品画像大
					$imageUrl_l = $imageArray['im_url'];	// URL
				}
			}
		}
		
		// 各種価格を求める
		$price = $this->ecObj->getCurrencyPrice($price);	// 端数調整
		$this->ecObj->setCurrencyType($this->currency, $this->langId);		// 通貨設定
		$this->ecObj->setTaxType($this->taxType);		// 税種別設定
		$totalPrice = $this->ecObj->getPriceWithTax($price, $dispPrice);	// 税込み価格取得
		$delivPrice = $this->ecObj->getCurrencyPrice($delivPrice);	// 端数調整
		
		$this->tmpl->addVar("_widget", "name", $name);		// 名前
		$this->tmpl->addVar("_widget", "code", $code);		// 商品コード
		$this->tmpl->addVar("_widget", "index", $index);		// 表示順
		$this->tmpl->addVar("_widget", "unit_quantity", $unitQuantity);		// 数量
		$this->tmpl->addVar("_widget", "deliv_type", $delivType);		// 配送タイプ
		$this->tmpl->addVar("_widget", "deliv_price", $delivPrice);		// 配送単価
		$this->tmpl->addVar("_widget", "deliv_weight", $delivWeight);		// 配送基準重量
		$this->tmpl->addVar("_widget", "description", $description);		// 説明
		$this->tmpl->addVar("_widget", "desc_short", $description_short);		// 簡易説明		
//		$this->tmpl->addVar("_widget", "keyword", $keyword);		// 検索キーワード
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
		if (!empty($updateUser)) $this->tmpl->addVar("_widget", "update_user", $updateUser);	// 更新者
		if (!empty($updateDt)) $this->tmpl->addVar("_widget", "update_dt", $updateDt);	// 更新日時

		// 単位タイプ選択メニュー作成
		$this->db->getUnitType($defaultLang, array($this, 'unitTypeLoop'));

		// 通貨タイプ選択メニュー作成
/*		if ($this->gEnv->getMultiLanguage()){	// 多言語対応のとき
			$this->db->getCurrency($defaultLang, array($this, 'currencyLoop'));
		}*/
		// 課税タイプ選択メニューの作成
		$this->db->getTaxType(array($this, 'taxTypeLoop'));

		// 商品画像プレビューの作成
		// 画像小
		$destImg = '';
		if (empty($imageUrl_s)){
			$destImg = '<img id="preview_img_small" style="display:none;" ';
			$destImg .= 'width="' . $defaultImageSWidth . '" ';
			$destImg .= 'height="' . $defaultImageSHeight . '" ';
			$destImg .= '/>';
		} else {
			// URLマクロ変換
			$imgUrl = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $this->gEnv->getRootUrl(), $imageUrl_s);
			$destImg = '<img id="preview_img_small" src="' . $this->getUrl($imgUrl) . '" ';
			$destImg .= 'width="' . $defaultImageSWidth . '"';
			$destImg .= ' height="' . $defaultImageSHeight . '"';
			$destImg .= ' />';
		}
		$this->tmpl->addVar("_widget", "image_s", $destImg);
		// 画像中
		$destImg = '';
		if (empty($imageUrl_m)){
			$destImg = '<img id="preview_img_medium" style="display:none;" ';
			$destImg .= 'width="' . $defaultImageMWidth . '" ';
			$destImg .= 'height="' . $defaultImageMHeight . '" ';
			$destImg .= '/>';
		} else {
			// URLマクロ変換
			$imgUrl = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $this->gEnv->getRootUrl(), $imageUrl_m);
			$destImg = '<img id="preview_img_medium" src="' . $this->getUrl($imgUrl) . '" ';
			$destImg .= 'width="' . $defaultImageMWidth . '"';
			$destImg .= ' height="' . $defaultImageMHeight . '"';
			$destImg .= ' />';
		}
		$this->tmpl->addVar("_widget", "image_m", $destImg);
		// 画像大
		$destImg = '';
		if (empty($imageUrl_l)){
			$destImg = '<img id="preview_img_large" style="display:none;" ';
			$destImg .= 'width="' . $defaultImageLWidth . '" ';
			$destImg .= 'height="' . $defaultImageLHeight . '" ';
			$destImg .= '/>';
		} else {
			// URLマクロ変換
			$imgUrl = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $this->gEnv->getRootUrl(), $imageUrl_l);
			$destImg = '<img id="preview_img_large" src="' . $this->getUrl($imgUrl) . '" ';
			$destImg .= 'width="' . $defaultImageLWidth . '"';
			$destImg .= ' height="' . $defaultImageLHeight . '"';
			$destImg .= ' />';
		}
		$this->tmpl->addVar("_widget", "image_l", $destImg);
		$this->tmpl->addVar("_widget", "image_s_width", $defaultImageSWidth);		// 商品画像小の幅
		$this->tmpl->addVar("_widget", "image_s_height", $defaultImageSHeight);		// 商品画像小の高さ
		$this->tmpl->addVar("_widget", "image_m_width", $defaultImageMWidth);		// 商品画像中の幅
		$this->tmpl->addVar("_widget", "image_m_height", $defaultImageMHeight);		// 商品画像中の高さ
		$this->tmpl->addVar("_widget", "image_l_width", $defaultImageLWidth);		// 商品画像大の幅
		$this->tmpl->addVar("_widget", "image_l_height", $defaultImageLHeight);		// 商品画像大の高さ
				
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
		$this->tmpl->addVar("_widget", "image_url_s", $imageUrl_s);		// 画像小
		$this->tmpl->addVar("_widget", "image_url_m", $imageUrl_m);		// 画像中
		$this->tmpl->addVar("_widget", "image_url_l", $imageUrl_l);		// 画像大
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
		$ret = $this->db->getProductBySerial($fetchedRow['hp_serial'], $row, $row2, $row3);
		if ($ret){
			$langId = $row['hp_language_id'];
			
			// 価格を取得
			$priceArray = $this->getPrice($row2, self::REGULAR_PRICE, $this->defaultCurrencyId);
			$price = $priceArray['pp_price'];	// 価格
			$currency = $priceArray['pp_currency_id'];	// 通貨
			$taxType = $row['hp_tax_type_id'];					// 税種別			

			// 価格作成
			$this->ecObj->setCurrencyType($currency, $langId);		// 通貨設定
			$this->ecObj->setTaxType($taxType);		// 税種別設定
			$totalPrice = $this->ecObj->getPriceWithTax($price, $dispPrice);	// 税込み価格取得
			if (empty($taxType)) $dispPrice = '(未)';		// 税種別未選択のとき
			
			$visible = '';
			if ($row['hp_visible']){	// 項目の表示
				$visible = 'checked';
			}
		}
		
		$row = array(
			'index' => $index,													// 項目番号
			'no' => $index + 1,													// 行番号
			'serial' => $this->convertToDispString($fetchedRow['hp_serial']),	// シリアル番号
			'id' => $row['hp_id'],			// ID
			'name' => $this->convertToDispString($row['hp_name']),		// 名前
			'code' => $this->convertToDispString($row['hp_code']),		// 商品コード
			'price' => $dispPrice,													// 価格(税込)
			'view_index' => $this->convertToDispString($row['hp_sort_order']),		// 表示順
			'update_user' => $this->convertToDispString($row['lu_name']),	// 更新者
	//		'update_dt' => $this->convertToDispDateTime($row['hp_create_dt']),	// 更新日時
			'update_dt' => $this->convertToDispDateTime($fetchedRow['hp_create_dt'], 0, 10/*時分表示*/),// 更新日時
			'visible' => $visible											// メニュー項目表示制御
		);
		$this->tmpl->addVars('itemlist', $row);
		$this->tmpl->parseTemplate('itemlist', 'a');
		
		// 表示中の項目のシリアル番号を保存
		$this->serialArray[] = $fetchedRow['hp_serial'];
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
	 * @param string    $currencyId			通貨ID
	 * @return array						取得した価格行
	 */
	function getPrice($srcRows, $priceType, $currencyId)
	{
		for ($i = 0; $i < count($srcRows); $i++){
			if ($srcRows[$i]['pp_currency_id'] == $currencyId && $srcRows[$i]['pp_price_type_id'] == $priceType){
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
}
?>
