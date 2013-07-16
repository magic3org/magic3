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
 * @version    SVN: $Id: ec_cartWidgetContainer.php 5970 2013-04-30 00:45:47Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath()		. '/baseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() .	'/ec_cart_boxDb.php');

class ec_cartWidgetContainer extends BaseWidgetContainer
{
	private $db;			// DBオブジェクト
	private $langId;		// 現在の言語
	private $ecObj;					// EC共通ライブラリオブジェクト
	private $titleLength;		// タイトル文字数
//	private $productClass;		// 商品クラス
	protected $productTotal;		// 商品合計額
	protected $productCount;		// 商品総数
	protected $productImageWidth;		// 商品画像幅
	protected $productImageHeight;		// 商品画像高さ
	const PRICE_OBJ_ID = "eclib";		// 価格計算オブジェクトID
	const DEFAULT_CART_WIDGET = 'photo_shop';		// カート内容表示用呼び出しウィジェットID
	const DEFAULT_TITLE = 'カート';			// デフォルトのウィジェットタイトル名
	const PRODUCT_CLASS_PHOTO = 'photo';		// 商品クラス
	const PRODUCT_CLASS_DEFAULT = '';		// 商品クラス
	const PRODUCT_TYPE_DOWNLOAD = 'download';		// ダウンロード商品タイプ
	const DEFAULT_TITLE_LENGTH = 10;		// タイトル名長さ
	const ICON_SIZE = 64;
	const CSS_FILE = '/style.css';		// CSSファイルのパス
	const THUMBNAIL_DIR = '/widgets/photo/image';		// 画像格納ディレクトリ
	const DEFAULT_IMAGE_EXT = 'jpg';			// 画像ファイルのデフォルト拡張子
	const DEFAULT_THUMBNAIL_SIZE = 128;		// サムネール画像サイズ
	const PRODUCT_IMAGE_SMALL = 'small-product';		// 小サイズ商品画像ID
	const PRODUCT_NAME_FORMAT	= '%s(%s)';		// 商品名表示フォーマット
	const DEFAULT_PRODUCT_IMAGE_TYPE = 'c.jpg';			// 商品画像ファイルのタイプ
	const PRODUCT_IMAGE_DIR = '/widgets/product/image/';				// 商品画像格納ディレクトリ
	const CART_WIDGET_TYPE = 'product';			// カート表示ウィジェットのウィジェットタイプ
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		$this->db = new ec_cart_boxDb;			// DBオブジェクト
		
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
	 * @param object         $param			任意使用パラメータ。そのまま_assign()に渡る
	 * @return string 						テンプレートファイル名。テンプレートライブラリを使用しない場合は空文字列「''」を返す。
	 */
	function _setTemplate($request, &$param)
	{	
		return 'main.tmpl.html';
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
		$this->langId	= $this->gEnv->getCurrentLanguage();		// 表示言語を取得
		$this->productTotal = 0;		// 商品合計額
		$this->productCount = 0;		// 商品総数
		
		// 設定値を取得
		$this->titleLength = self::DEFAULT_TITLE_LENGTH;	// タイトル文字数
		$paramObj = $this->getWidgetParamObj();
		if (!empty($paramObj)){
			$this->titleLength	= $paramObj->titleLength;
		}
		// 画像情報を取得
		$this->productImageWidth = 0;		// 商品画像幅
		$this->productImageHeight = 0;		// 商品画像高さ
		$ret = $this->db->getProductImageInfo(self::PRODUCT_IMAGE_SMALL, $row);
		if ($ret){
			$this->productImageWidth = $row['is_width'];
			$this->productImageHeight = $row['is_height'];
		}
		
		// クッキー読み込み、カートIDを取得
		$cartId = $request->getCookieValue(M3_COOKIE_CART_ID);
		if (!empty($cartId)){	// カートIDが設定されているとき
			// カートの商品を取得
//			$this->productClass = self::PRODUCT_CLASS_PHOTO;		// 商品クラス
			$this->ecObj->db->getCartItems($cartId, $this->langId, self::PRODUCT_CLASS_PHOTO, array($this, 'cartLoop'));	// フォトギャラリー画像
//			$this->productClass = self::PRODUCT_CLASS_DEFAULT;		// 商品クラス
			$this->ecObj->db->getCartItems($cartId, $this->langId, self::PRODUCT_CLASS_DEFAULT, array($this, 'cartLoop'));	// 一般商品
		}
		if ($this->productCount <= 0) $this->tmpl->setAttribute('show_item', 'visibility', 'hidden');	// 商品一覧を非表示
		
		// カートリンク先
		$cartWidget = $this->gPage->getActiveMainWidgetIdByWidgetType(self::CART_WIDGET_TYPE);		// カート表示用ウィジェット取得
		if (empty($cartWidget)) $cartWidget = self::DEFAULT_CART_WIDGET;
		$url = $this->createCmdUrlToWidget($cartWidget, 'task=cart');
		$this->tmpl->addVar("_widget", "cart_detail_url", $this->getUrl($url, true));
		
		$this->tmpl->addVar("_widget", "item_count", $this->productCount);			// 商品総数
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
	 * 取得したデータをテンプレートに設定する
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function cartLoop($index, $fetchedRow, $param)
	{
		$productClass	= $fetchedRow['si_product_class'];		// 商品クラス
		$productType	= $fetchedRow['si_product_type_id'];		// 商品タイプ
		$productId		= $fetchedRow['si_product_id'];				// 商品ID
		$prePrice = $this->convertToDispString($fetchedRow['cu_symbol']);
		$postPrice = $this->convertToDispString($fetchedRow['cu_post_symbol']);

		// 価格作成
		$this->ecObj->setCurrencyType($fetchedRow['si_currency_id'], $this->langId);		// 通貨設定
		$this->ecObj->getPriceWithoutTax($fetchedRow['si_subtotal'], $dispPrice);					// 税込み価格取得
					
		// 価格の有効判断
		$available = '';
		if (!$fetchedRow['si_available']) $available = '(無効)';
		
		//switch ($this->productClass){
		switch ($productClass){
			case self::PRODUCT_CLASS_PHOTO;		// フォトギャラリー画像
				$photoId = $fetchedRow['ht_public_id'];		// 公開画像ID
				$title = $fetchedRow['ht_name'];		// サムネール画像タイトル
				if ($productType == self::PRODUCT_TYPE_DOWNLOAD){		// ダウンロード商品の場合
					$productTypeName = $fetchedRow['py_name'];		// 商品タイプ名
				} else {							// フォト関連商品の場合
					$productTypeName = $fetchedRow['hp_name'];		// 商品タイプ名
				}

				// 商品名
				$productName = sprintf(self::PRODUCT_NAME_FORMAT, $productTypeName, $title);		// 商品名
				$shortName = makeTruncStr($productName, $this->titleLength);// タイトル
				
				// 商品詳細へのリンク
				$url = $this->gEnv->getDefaultUrl() . '?' . M3_REQUEST_PARAM_PHOTO_ID . '=' . $photoId;
				$urlLink = $this->convertUrlToHtmlEntity($this->getUrl($url, true));
		
				// 画像URL
				$imageUrl = $this->gEnv->getResourceUrl() . self::THUMBNAIL_DIR . '/' . $photoId . '_' . self::DEFAULT_THUMBNAIL_SIZE . '.' . self::DEFAULT_IMAGE_EXT;
				$imageWidth = self::ICON_SIZE;
				$imageHeight = self::ICON_SIZE;
				break;
			case self::PRODUCT_CLASS_DEFAULT;		// 一般商品
				// 商品名
				$title = $fetchedRow['pt_name'];		// 名前
				$shortName = makeTruncStr($fetchedRow['pt_name'], $this->titleLength);// タイトル

				// 商品詳細へのリンク
				$url = $this->gEnv->getDefaultUrl() . '?' . M3_REQUEST_PARAM_PRODUCT_ID . '=' . $fetchedRow['si_product_id'];
				$urlLink = $this->convertUrlToHtmlEntity($this->getUrl($url, true));
				
				// 画像URL
				$ret = $this->db->getImageInfoByProductId($fetchedRow['si_product_id'], $this->langId, $row);
				if ($ret){
					$imageArray = $this->getImage($row, self::PRODUCT_IMAGE_SMALL);// 商品画像小
					$imageUrl = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $this->gEnv->getRootUrl(), $imageArray['im_url']);
					$imagePath = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $this->gEnv->getSystemRootPath(), $imageArray['im_url']);
					if (!file_exists($imagePath)){
						$imageUrl = $this->getProductImageUrl('0_' . $this->productImageWidth . 'x' . $this->productImageHeight . self::DEFAULT_PRODUCT_IMAGE_TYPE);
					}
				}
				$imageWidth = $this->productImageWidth;
				$imageHeight = $this->productImageHeight;
				break;
		}
		// 商品合計価格、総数
		$quantity = $fetchedRow['si_quantity'];
		$subtotal = $fetchedRow['si_subtotal'];
		$this->productTotal += $subtotal;					// 合計価格
		$this->productCount += $quantity;					// 商品総数
			
		// タグを作成
		$nameLink = '<a href="' . $urlLink . '">' . $this->convertToDispString($shortName) . '</a>';
		$itemStr = '<div class="photo_cart_image"><a href="' . $urlLink . '"><img src="' . $this->getUrl($imageUrl) . '" width="' . $imageWidth . '" height="' . $imageHeight . 
								'" title="' . $this->convertToDispString($title) . '" alt="' . $this->convertToDispString($title) . '" /></a></div>';
		$itemStr .= '<div class="photo_cart_info"><div>' . $nameLink . '</div><div>数量' . $fetchedRow['si_quantity'] . '&nbsp;' . $prePrice . $dispPrice . $postPrice . $available . '</div></div>';
		$itemStr .= '<div style="clear:both;"></div>';
		
		$row = array(
			'cart_item' => $itemStr
		);
		$this->tmpl->addVars('itemlist', $row);
		$this->tmpl->parseTemplate('itemlist', 'a');
		return true;
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
