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
 * @version    SVN: $Id: ec_product_display2WidgetContainer.php 5482 2012-12-22 10:13:49Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath()	. '/ec_product_display2Db.php');
require_once($gEnvManager->getCommonPath() . '/valueCheck.php');

class ec_product_display2WidgetContainer extends BaseWidgetContainer
{
	private $db;			// DB接続オブジェクト
	private $ecObj;					// EC共通ライブラリオブジェクト
	private $langId;		// 現在の言語
	private $maxCount;		// 最大表示項目数
	private $viewItemCount;		// 表示項目数
	private $imageWidth;	// 画像幅
	private $imageHeight;	// 画像高さ
	private $detailLabel;			// 詳細へのリンク
	private $rowCount;				// 表示する行の数
	private $columnCount;			// 表示する列の数
	private $imgSize;			// 選択中の画像サイズ
	private $nameVisible;			// 商品名表示
	private $codeVisible;			// 商品コード表示
	private $priceVisible;		// 商品価格表示
	private $descVisible;			// 商品説明表示
	private $imgVisible;			// 商品画像表示
	private $detailVisible;		// 詳細ボタン表示
	private $imageSizeArray;		// 画像サイズ
	private	$prePrice;			// 価格表示用
	private $postPrice;			// 価格表示用
	const DEFAULT_CONFIG_ID = 0;
	const DEFAULT_TITLE = '新着おすすめ';			// デフォルトのウィジェットタイトル
	const REGULAR_PRICE = 'regular';		// 通常価格
	const PRICE_OBJ_ID = "eclib";		// 価格計算オブジェクトID
	const PRODUCT_IMAGE_SMALL = 'small-product';		// 小サイズ商品画像ID
	const PRODUCT_IMAGE_MEDIUM = 'standard-product';		// 中サイズ商品画像ID
	const PRODUCT_IMAGE_LARGE = 'large-product';		// 大サイズ商品画像ID
	const DEFAULT_PRODUCT_IMAGE_TYPE = 'c.jpg';			// 商品画像ファイルのタイプ
	const PRODUCT_IMAGE_DIR = '/widgets/product/image/';				// 商品画像格納ディレクトリ
	const CSS_FILE = '/style.css';		// CSSファイルのパス
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->db = new ec_product_display2Db();
		
		// 価格計算用オブジェクト取得
		$this->ecObj = $this->gInstance->getObject(self::PRICE_OBJ_ID);
		
		$this->imageSizeArray = array(self::PRODUCT_IMAGE_SMALL, self::PRODUCT_IMAGE_MEDIUM, self::PRODUCT_IMAGE_LARGE);		// 画像サイズ
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
		$this->langId = $this->gEnv->getCurrentLanguage();
		$this->viewItemCount = 0;		// 表示項目数
		
		// 定義ID取得
		$configId = $this->gEnv->getCurrentWidgetConfigId();
		if (empty($configId)) $configId = self::DEFAULT_CONFIG_ID;
		
		// パラメータオブジェクトを取得
		$targetObj = $this->getWidgetParamObjByConfigId($configId);
		if (!empty($targetObj)){		// 定義データが取得できたとき
			$name = $targetObj->name;// 定義名
			$this->detailLabel = $targetObj->detailLabel;			// 詳細へのリンク
			$this->rowCount = $targetObj->rowCount;				// 表示する行の数
			$this->columnCount = $targetObj->columnCount;			// 表示する列の数
			$this->imgSize = $targetObj->imgSize;			// 選択中の画像サイズ
			$this->nameVisible = $targetObj->nameVisible;			// 商品名表示
			$this->codeVisible = $targetObj->codeVisible;			// 商品コード表示
			$this->priceVisible = $targetObj->priceVisible;		// 商品価格表示
			$this->descVisible = $targetObj->descVisible;			// 商品説明表示
			$this->imgVisible = $targetObj->imgVisible;			// 商品画像表示
			$this->detailVisible = $targetObj->detailVisible;		// 詳細ボタン表示
			$productItems = $targetObj->productItems;			// 表示する商品
			
			if (empty($productItems)){
				$this->cancelParse();		// 出力しない
			} else {
				// 最大表示可能数
				$this->maxCount = $this->rowCount * $this->columnCount;
			
				// 通貨情報を取得
				$ret = $this->db->getCurrency($this->ecObj->getDefaultCurrency(), $this->langId, $currencyRow);
				if ($ret){
					$this->prePrice = $currencyRow['cu_symbol'];
					$this->postPrice = $currencyRow['cu_post_symbol'];
				}
		
				// 画像情報を取得
				$this->imageWidth = 0;
				$this->imageHeight = 0;
				$ret = $this->db->getProductImageInfo($this->imgSize, $row);
				if ($ret){
					$this->imageWidth = $row['is_width'];
					$this->imageHeight = $row['is_height'];
				}

				// 商品一覧を作成
				$productArray = explode(',', $productItems);
				if (ValueCheck::isNumeric($productArray)){		// すべて数値であるかチェック
					$this->outputHtml = '';
					$this->db->getProduct($this->langId, $productArray, array($this, 'itemListLoop'));
				}

				// 商品情報を埋め込む
				$this->tmpl->addVar("_widget", "new_items", $this->outputHtml);
			}
		} else {
			$this->cancelParse();		// 出力しない
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
		return $this->getUrl($this->gEnv->getCurrentWidgetCssUrl() . self::CSS_FILE);
	}
	/**
	 * 取得したデータをテンプレートに設定する
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function itemListLoop($index, $fetchedRow, $param)
	{
		$productId = $fetchedRow['pt_id'];
		$name = $fetchedRow['pt_name'];
		
		// 商品情報を取得
		$ret = $this->db->getProductInfo($productId, $this->langId, $priceRows, $imageRows, $statusRows);

		// 商品詳細へのリンク
		$url = $this->gEnv->getDefaultUrl() . '?' . M3_REQUEST_PARAM_PRODUCT_ID . '=' . $productId;
		
		// 価格を取得
		$priceArray = $this->getPrice($priceRows, self::REGULAR_PRICE);
		$price = $priceArray['pp_price'];	// 価格
		$currency = $priceArray['pp_currency_id'];	// 通貨
		$taxType = $fetchedRow['pt_tax_type_id'];					// 税種別
		$lang = $fetchedRow['pt_language_id'];					// 言語				

		// 表示額作成
		$this->ecObj->setCurrencyType($currency, $lang);		// 通貨設定
		$this->ecObj->setTaxType($taxType, $lang);		// 税種別設定
		$totalPrice = $this->ecObj->getPriceWithTax($price, $dispPrice);	// 税込み価格取得
	
		// 画像を配列に保存
		$images = array();// 画像URL保存用
		for ($i = 0; $i < count($this->imageSizeArray); $i++){
			$size = $this->imageSizeArray[$i];
			$imageArray = $this->getImage($imageRows, $size);
			if (empty($imageArray)){
				$images[] = array('size' => $size, 'url' => '');		// 画像が設定されていない場合は空文字列
			} else {
				$images[] = array('size' => $size, 'url' => $imageArray['im_url']);
			}
		}
		
		// ######## タグを作成 ########
		$tableColumnCount = 1;		// 商品表示テーブルのカラムの数
		
		// 画像タグ作成
		$imageTag = '';
		$imageUrl = $this->getProperImage($images, $this->imgSize);
		$imgUrl = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $this->gEnv->getRootUrl(), $imageUrl);
		$imgPath = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $this->gEnv->getSystemRootPath(), $imageUrl);
		if (!file_exists($imgPath)){
			$imgUrl = $this->getProductImageUrl('0_' . $this->imageWidth . 'x' . $this->imageHeight . self::DEFAULT_PRODUCT_IMAGE_TYPE);
		}
		$imageTag = '<img class="product_image" src="' . $this->getUrl($imgUrl) . '" ';
		$imageTag .= 'width="' . $this->imageWidth . '"';
		$imageTag .= ' height="' . $this->imageHeight . '"';
		$imageTag .= ' />';
		
		// 出力制御
		if ($this->imgVisible){				// 画像の出力
			if (!empty($imageTag)) $imgSpan = '<div class="product_image"><a href="' . $this->getUrl($url, true) . '">' . $imageTag . '</a></div>';
		}
		if ($this->nameVisible){			// 商品名表示
			$nameLink = '<div class="product_name"><a href="' . $this->getUrl($url, true) . '">' . $this->convertToDispString($name) . '</a></div>';
		}
		if ($this->codeVisible){			// 商品コード表示
			$productCode = '<div class="product_code">' . $this->convertToDispString($fetchedRow['pt_code']) . '</div>';
		}
		if ($this->priceVisible){			// 商品価格表示
			$priceStr = '<div class="product_price">' . $this->convertToDispString($this->prePrice . $dispPrice . $this->postPrice) . '</div>';
		}
		if ($this->descVisible){			// 商品説明表示
			$tableColumnCount = 2;
			$destStr = '<div class="product_description">' . $this->convertToDispString($fetchedRow['pt_description_short']) . '</div>';
		}
		if ($this->detailVisible){			// 詳細ボタン表示
			$detailButton = '<div class="product_link"><a href="' . $this->getUrl($url, true) . '">' . $this->detailLabel . '</a></div>';
		}
	
		if ($tableColumnCount < 2){
			$itemStr = '<div class="product_outer">' . $imgSpan . $nameLink . $productCode . $priceStr . $detailButton . '</div>';
		} else {
			$itemStr = '<table><tr><td style="border:none;">';
			$itemStr .= $imgSpan . $nameLink . $productCode . $priceStr;
			$itemStr .= '</td><td style="border:none;">';
			$itemStr .= $destStr . $detailButton;
			$itemStr .= '</td></tr></table>';
		}
	
		$colNo = $this->viewItemCount % $this->columnCount;
		if ($colNo == 0){// 左端のとき
			$this->outputHtml .= '<tr><td style="border:none;">';
			$this->outputHtml .= $itemStr;
			$this->outputHtml .= '</td>';
		} else if ($colNo == $this->columnCount -1){// 右端のとき
			$this->outputHtml .= '<td style="border:none;">';
			$this->outputHtml .= $itemStr;
			$this->outputHtml .= '</td></tr>';
		} else {
			$this->outputHtml .= '<td style="border:none;">';
			$this->outputHtml .= $itemStr;
			$this->outputHtml .= '</td>';
		}
		
		$this->viewItemCount++;		// 表示項目数
		if ($this->viewItemCount < $this->maxCount){
			return true;
		} else {
			return false;// 最大表示項目数まで表示
		}
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
	 * 最適な画像を取得
	 *
	 * @param array  	$images			画像へのパス(優先順)
	 * @param string	$size			目的の画像のサイズ
	 * @return string					URL
	 */
	function getProperImage($images, $size)
	{
		for ($i = 0; $i < count($images); $i++){
			$imageInfo = $images[$i];
			if ($imageInfo['size'] == $size) break;
		}
		if ($i == count($images)) return '';
		
		// 指定画像が存在する場合はそのまま返す
		if (!empty($imageInfo['url'])) return $imageInfo['url'];
		
		$index = $i;
		for ($i = $index + 1; $i < count($images); $i++){
			$imageInfo = $images[$i];
			if (!empty($imageInfo['url'])) return $imageInfo['url'];
		}
		for ($i = 0; $i < $index; $i++){
			$imageInfo = $images[$i];
			if (!empty($imageInfo['url'])) return $imageInfo['url'];
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
}
?>
