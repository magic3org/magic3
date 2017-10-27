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
require_once($gEnvManager->getCurrentWidgetDbPath() .	'/product_randomDb.php');

class ec_product_randomWidgetContainer extends BaseWidgetContainer
{
	private $db;			// DB接続オブジェクト
	private $langId;		// 言語
	private $currencyId;	// 現在の通貨ID
	private $currentPageUrl;	// 現在のページ
	private $viewStyle;		// 表示モード
	private $viewCountArray;	// 表示回数更新用
	const TARGET_WIDGET = 'ec_disp';		// 呼び出しウィジェットID
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
	const DEFAULT_ITEM_COUNT = 8;				// デフォルトの表示項目数
	const DEFAULT_TITLE = '商品ランダム表示';	// デフォルトのタイトル
	const CSS_FILE = '/skin.css';		// CSSファイルのパス
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->db = new product_randomDb();
		
		// EC用共通オブジェクト取得
		$this->ecObj = $this->gInstance->getObject(self::PRICE_OBJ_ID);
		//$this->ecObj->initByDefault();		// デフォルト値で初期化
		
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
		$this->viewStyle = self::VIEW_STYLE_MEDIUM;		// 画像中サイズ
		$viewCount = self::DEFAULT_ITEM_COUNT;		// 表示商品数
		$condition = 0;	// 条件(すべて表示)
		$statusArray = array();	// 商品ステータス

		// ウィジェットパラメータ取得
		$paramObj = $this->getWidgetParamObj();
		if (!empty($paramObj)){
			$viewCount	= $paramObj->viewCount;	// 表示項目数
			$condition	= $paramObj->condition;	// 条件
			$statusArray = $paramObj->statusArray;	// 商品ステータス
		}
		
		// 商品リストを取得
		$this->db->getProductSerial($this->langId, $viewCount, 0, $statusArray, $rows);
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
	function productListLoop($index, $fetchedRow, $param = null)
	{
		$images = array();// 画像URL保存用
		
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
		// 画像小
		$destImg_s = '';
		if ($this->viewStyle == self::VIEW_STYLE_SMALL){
			$imageUrl_s = $this->getProperImage($images, 0);
			if (empty($imageUrl_s)){
				$destImg_s = '<img id="preview_img_small" style="display:none;" ';
				$destImg_s .= 'width="' . $this->defaultImageSWidth . '" ';
				$destImg_s .= 'height="' . $this->defaultImageSHeight . '" ';
				$destImg_s .= '/>';
			} else {
				// URLマクロ変換
				$imgUrl = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $this->gEnv->getRootUrl(), $imageUrl_s);
				$destImg_s = '<img id="preview_img_small" src="' . $this->getUrl($imgUrl) . '" ';
				$destImg_s .= 'width="' . $this->defaultImageSWidth . '"';
				$destImg_s .= ' height="' . $this->defaultImageSHeight . '"';
				$destImg_s .= ' />';
			}
		}
		// 画像中
		$destImg_m = '';
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
		}
		// 画像大
		$destImg_l = '';
		if ($this->viewStyle == self::VIEW_STYLE_LARGE){
			$imageUrl_l = $this->getProperImage($images, 2);
			if (empty($imageUrl_l)){		// 画像が空のとき
				$destImg_l = '<img id="preview_img_large" style="display:none;" ';
				$destImg_l .= 'width="' . $this->defaultImageLWidth . '" ';
				$destImg_l .= 'height="' . $this->defaultImageLHeight . '" ';
				$destImg_l .= '/>';
			} else {
				// URLマクロ変換
				$imgUrl = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $this->gEnv->getRootUrl(), $imageUrl_l);
				$imageUrl_l = $imgUrl;
				$destImg_l = '<img id="preview_img_large" src="' . $this->getUrl($imgUrl) . '" ';
				$destImg_l .= 'width="' . $this->defaultImageLWidth . '"';
				$destImg_l .= ' height="' . $this->defaultImageLHeight . '"';
				$destImg_l .= ' />';
			}
		}
		
		// 商品詳細リンクを作成
		$name = '詳細';		// 名前
		$linkUrl = $this->gEnv->getDefaultUrl() . '?' . M3_REQUEST_PARAM_PRODUCT_ID . '=' . $id;
		$link = '<a href="' . $this->getUrl($linkUrl, true) . '" >' . $name . '</a>';
		
		// 画像にリンクを付ける
		if (!empty($destImg_s)) $destImg_s = '<a href="' . $this->getUrl($linkUrl, true) . '" >' . $destImg_s . '</a>';
		if (!empty($destImg_m)) $destImg_m = '<a href="' . $this->getUrl($linkUrl, true) . '" >' . $destImg_m . '</a>';
		$row = array(
			'id' => $id,			// ID
			'image' => $this->getUrl($imgUrl),		// 画像
			'image_s' => $destImg_s,		// 画像
			'image_m' => $destImg_m,		// 画像
			'image_l_url' => $this->getUrl($imageUrl_l),		// 画像URL
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
}
?>
