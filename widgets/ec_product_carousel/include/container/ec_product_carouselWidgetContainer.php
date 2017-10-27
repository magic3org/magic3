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
require_once($gEnvManager->getCurrentWidgetDbPath() .	'/ec_product_carouselDb.php');
require_once($gEnvManager->getCommonPath() . '/valueCheck.php');

class ec_product_carouselWidgetContainer extends BaseWidgetContainer
{
	private $db;			// DB接続オブジェクト
	private $langId;		// 言語
	private $currencyId;	// 現在の通貨ID
	private $currentPageUrl;	// 現在のページ
	private $viewCountArray;	// 表示回数更新用
	private $imageWidth;		// 商品画像幅
	private $imageHeight;		// 商品画像高さ
	private $categoryId;	// カテゴリーID
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
	const DEFAULT_ITEM_COUNT = 10;				// デフォルトの表示項目数
	const DEFAULT_TITLE = '商品カルーセル表示';	// デフォルトのタイトル
	const DEFAULT_CSS_FILE = '/default.css';	// CSSファイル
	const DEFAULT_PRODUCT_IMAGE_TYPE = 'c.jpg';			// 商品画像ファイルのタイプ
	const PRODUCT_IMAGE_DIR = '/widgets/product/image/';				// 商品画像格納ディレクトリ
	const MAX_CATEGORY_LEVEL = 5;			// カテゴリー階層最大数
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
		$this->db = new ec_product_carouselDb();
		
		// EC用共通オブジェクト取得
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
		return 'index.tmpl.html';
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
		// 環境取得
		$this->langId	= $this->gEnv->getCurrentLanguage();		// 表示言語を取得
		
		// 現在のページURL
		$this->currentPageUrl = $this->gEnv->createCurrentPageUrl();
		
		// 表示設定を取得
		$viewCount = self::DEFAULT_ITEM_COUNT;		// 表示商品数

		// ウィジェットパラメータ取得
		$paramObj = $this->getWidgetParamObj();
		if (!empty($paramObj)){
			$viewCount	= $paramObj->viewCount;	// 表示項目数
		}
		
		// 画像情報を取得
		$this->imageWidth = 0;		// 商品画像幅
		$this->imageHeight = 0;		// 商品画像高さ
		$ret = $this->db->getProductImageInfo(self::PRODUCT_IMAGE_MEDIUM, $row);
		if ($ret){
			$this->imageWidth = $row['is_width'];
			$this->imageHeight = $row['is_height'];
		}
		
		// パラメータ取得
		$categoryId = $request->trimValueOf('category');	// カテゴリーID
		$this->categoryId = $categoryId;
		$categoryArray = explode(',', $categoryId);
		if (!ValueCheck::isNumeric($categoryArray)){
			$categoryId = '';		// すべて数値であるかチェック
			$categoryArray = array();
		}
		if (empty($categoryId)){			// カテゴリーが設定されていないときは商品詳細からカテゴリーを取得
			$productId = $request->trimValueOf(M3_REQUEST_PARAM_PRODUCT_ID);		// 商品ID取得
			
			// 商品カテゴリー取得
			$ret = $this->db->getProductWithCategory($productId, $this->langId, $row);
			if ($ret){
				$categoryId = $row['pw_category_id'];
				$categoryArray = explode(',', $categoryId);
			}
		}
		if (!empty($categoryArray) && $this->ecObj->getConfig(self::CF_E_HIERARCHICAL_CATEGORY)){			// サブカテゴリーを取得する場合
			$categoryArray = array_unique($this->getSubCategory($categoryArray));
			$categoryId = implode(',', $categoryArray);
		}
		
		// 商品リストを取得
		$this->db->getProductSerial($this->langId, $viewCount, 0, $categoryArray, $rows);
		shuffle($rows);			// 配列をシャッフル
		$productCount = count($rows);
		for ($i = 0; $i < $productCount; $i++){
			$this->productListLoop($i, $rows[$i]);
		}

		// 表示回数を更新
		$viewCountSize = count($this->viewCountArray);
		if ($viewCountSize > 0){
			// トランザクションスタート
			$this->db->startTransaction();
				
			for ($i = 0; $i < $viewCountSize; $i++){
				$id = $this->viewCountArray[$i][0];
				$viewCount = $this->viewCountArray[$i][1] + 1;
				
				$updateParam = array();
				$updateParam['pe_promote_count'] = $viewCount;
				$this->db->updateProductRecord($id, $this->langId, $updateParam);
			}
			// トランザクション終了
			$ret = $this->db->endTransaction();
		}
	}
	/**
	 * ウィジェットのタイトルを設定
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。そのまま_assign()に渡る
	 * @return string 						ウィジェットのタイトル名
	 */
	function _setTitle($request, &$param)
	{
		return self::DEFAULT_TITLE;
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
		$cssFilePath = $this->getUrl($this->gEnv->getCurrentWidgetCssUrl() . self::DEFAULT_CSS_FILE);		// CSSファイル
		return array($cssFilePath);
	}
	/**
	 * 取得したデータをテンプレートに設定する
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function productListLoop($index, $fetchedRow, $param = null)
	{
		// 商品の詳細情報を取得
		$ret = $this->db->getProductBySerial($fetchedRow['pt_serial'], $row, $row2, $row3, $row4, $row5);
		if ($ret){
			// 商品ID
			$id = $row['pt_id'];
			if (empty($id)) $id = 0;
		
			// 参照数
			$viewCount = $row['pe_promote_count'];
			if (empty($viewCount)) $viewCount = 0;
		
			// 価格を取得
			$priceArray = $this->getPrice($row2, self::REGULAR_PRICE);
			$price = $priceArray['pp_price'];	// 価格
			$currency = $priceArray['pp_currency_id'];	// 通貨
			$taxType = $row['pt_tax_type_id'];					// 税種別
			$lang = $row['pt_language_id'];					// 言語

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
			
			// 画像中
			$imageUrl = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $this->gEnv->getRootUrl(), $imageUrl_m);
			$imagePath = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $this->gEnv->getSystemRootPath(), $imageUrl_m);
			if (!file_exists($imagePath)){
				$imageUrl = $this->getProductImageUrl('0_' . $this->imageWidth . 'x' . $this->imageHeight . self::DEFAULT_PRODUCT_IMAGE_TYPE);
			}
		}
		
		// 商品詳細リンクを作成
		$name = '詳細';		// 名前
//		if (empty($this->categoryId)){			// カテゴリーなしのとき
			$linkUrl = $this->gEnv->getDefaultUrl() . '?' . M3_REQUEST_PARAM_PRODUCT_ID . '=' . $id;
/*		} else {
			$linkUrl = $this->currentPageUrl;
			$linkUrl .= '&category=' . $this->categoryId;
			$linkUrl .= '&' . M3_REQUEST_PARAM_PRODUCT_ID . '=' . $id;
		}*/
		$link = '<a href="' . $this->getUrl($linkUrl, true) . '" >' . $name . '</a>';
		
		$row = array(
			'id' => $id,			// ID
			'image' => $this->getUrl($imageUrl),		// 画像
			'image_width' => $this->imageWidth,			// 画像幅
			'image_height' => $this->imageHeight,			// 画像高さ
			'name' => $this->convertToDispString($row['pt_name']),		// 名前
			'code' => $this->convertToDispString($row['pt_code']),		// 商品コード
			'disp_total_price' => $dispPrice,				// 税込み価格
			'description_short' => $row['pt_description'],				// 簡易説明
			'product_url' => $this->getUrl($linkUrl, true),										// 商品詳細リンクURL
			'product_link' => $link											// 商品詳細リンク
		);
		$this->tmpl->addVars('product_list', $row);
		$this->tmpl->parseTemplate('product_list', 'a');
		
		// 表示回数の更新用
		$this->viewCountArray[] = array($id, $viewCount);
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
