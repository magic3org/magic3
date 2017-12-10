<?php
/**
 * Eコマース価格処理クラス
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
require_once(dirname(__FILE__) . '/ecLibDb.php');

class ecLib
{
	private $currencyType;		// 通貨種別
	private $taxType;			// 税種別
	private $taxRate;			// 税率
	private $decimalPlace;		// 小数点位置
	private $configArray;		// Eコマース定義
	public $db;	// DB接続オブジェクト
	
	// 会員番号自動生成
	const MEMBER_NO_HEAD = 'WEB';		// 自動生成する会員番号のヘッダ部
	const MEMBER_NO_LENGTH = 5;			// 自動生成する会員番号の数値桁数
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// DBオブジェクト作成
		$this->db = new ecLibDb();
		
		// オブジェクトを初期化
		$this->_initByDefault();
		
		// Eコマース定義を読み込む
		$this->configArray = $this->_loadConfig();
	}
	/**
	 * オブジェクトをデフォルト通貨、言語で初期化
	 */
	public function _initByDefault()
	{
		global $gEnvManager;
		
		$type = $this->db->getDefaultCurrency();
		$lang = $gEnvManager->getDefaultLanguage();
		$this->setCurrencyType($type, $lang);
	}
	/**
	 * 通貨種別設定
	 *
	 * @param string	$type		通貨種別
	 * @param string	$lang		言語
	 */
	public function setCurrencyType($type, $lang)
	{
		if ($type != $this->currencyType){
			// 価格表示方法(小数桁数)を取得
			$ret = $this->db->getCurrency($type, $lang, $row);
			if ($ret){
				$this->decimalPlace = $row['cu_decimal_place'];
				$this->currencyType = $type;
			}
		}
	}
	/**
	 * 税種別設定
	 *
	 * @param string	$type		税種別
	 */
	public function setTaxType($type)
	{
		if (empty($type)){			// 税タイプ未選択のとき
			$this->taxRate = 0;
			$this->taxType = '';
		} else if ($type != $this->taxType){
			// 税種別情報を取得
			$ret = $this->db->getTaxType($type, $row);
			if ($ret){
				// 税率を取得
				// 税率が取得できないときは0とする
				$rate = $this->db->getTaxRate($row['tt_tax_rate_id']);
				$this->taxRate = $rate;
				$this->taxType = $type;
			}
		}
	}
	/**
	 * 税率を取得
	 *
	 * @param string	$type		税種別
	 * @return float				税率
	 */
	public function getTaxRate($type)
	{
		$rate = 0;
		// 税種別情報を取得
		$ret = $this->db->getTaxType($type, $row);
		if ($ret){
			// 税率を取得
			$rate = $this->db->getTaxRate($row['tt_tax_rate_id']);
		}
		return $rate;
	}
	/**
	 * 税込み価格を取得
	 *
	 * 税抜き価格から、現在の税種別の設定で税額を求める
	 * 現在の通貨の設定で価格の文字列を作成する
	 *
	 * @param float	 $srcPrice		税抜き価格
	 * @param string $dispPrice		税込み価格文字列表現
	 * @return float				税込み価格(数値)
	 */
	public function getPriceWithTax($srcPrice, &$dispPrice)
	{
		$tax = $srcPrice * $this->taxRate * 0.01;
		$total = $srcPrice + $tax;
		
		// 価格文字列作成
		$dispPrice = number_format($total, $this->decimalPlace);
		return $total;
	}
	/**
	 * 税抜き価格を取得
	 *
	 * 税抜き価格から、現在の税種別の設定で税額を求める
	 * 現在の通貨の設定で価格の文字列を作成する
	 *
	 * @param float	 $srcPrice		税抜き価格
	 * @param string $dispPrice		税抜き価格文字列表現
	 * @return float				税抜き価格(数値)
	 */
	public function getPriceWithoutTax($srcPrice, &$dispPrice)
	{	
		// 価格文字列作成
		$dispPrice = number_format($srcPrice, $this->decimalPlace);
		return $srcPrice;
	}
	/**
	 * クッキーに保存するカートIDを生成
	 *
	 * @return string				カートID
	 */
	public function createCartId()
	{
		// 最大シリアルNoを取得
		$max = $this->db->getMaxSerialOfBasket();
		$cartId = md5(time() . ($max + 1));
		return $cartId;
	}
	/**
	 * 数値を価格表示用の数値に変換
	 *
	 * @param string	$currencyType		通貨種別
	 * @param string	$lang				言語
	 * @param float	 	$price				変換する価格
	 */
	public function convertByCurrencyFormat($currencyType, $lang, $price)
	{
		$dispPrice = '';
		
		// 価格表示方法(小数桁数)を取得
		$ret = $this->db->getCurrency($currencyType, $lang, $row);
		if ($ret){
			$decimalPlace = $row['cu_decimal_place'];
			$dispPrice = number_format($price, $decimalPlace);
		}
		return $dispPrice;
	}
	/**
	 * デフォルトの通貨を取得
	 *
	 * @return string				デフォルトの通貨ID
	 */
	public function getDefaultCurrency()
	{
		return $this->db->getDefaultCurrency();
	}
	/**
	 * 会員Noを生成
	 *
	 * @return string		生成した会員NO
	 */
	public function generateMemberNo()
	{
		// 最大Noを取得
		$max = $this->db->getMaxMemberNo(self::MEMBER_NO_HEAD);
		$max++;
		$no = self::MEMBER_NO_HEAD . sprintf("%0" . self::MEMBER_NO_LENGTH . "d", $max);
		return $no;
	}
	/**
	 * 仮会員を正会員に変更
	 *
	 * @param int $userId			ユーザID兼データ更新ユーザ
	 * @param bool $generateMemNo	会員NOを自動生成するかどうか
	 * @return						true=成功、false=失敗
	 */
	function makeTmpMemberToProperMember($userId, $generateMemNo=true)
	{
		$no = '';
		if ($generateMemNo) $no = $this->generateMemberNo();
		return $this->db->makeTmpMemberToProperMember($userId, $no);
	}
	/**
	 * 規定の端数処理を行う
	 *
	 * @param float	$price			処理を行う価格
	 * @param float					処理結果
	 */
	public function getCurrencyPrice($price)
	{
		return floor($price);
	}
	/**
	 * 定義値を取得
	 *
	 * @param string $key		定義キー
	 * @param string $default	デフォルト値
	 * @return string			値
	 */
	function getConfig($key, $default = '')
	{
		$value = $this->configArray[$key];
		if (!isset($value)) $value = $default;
		return $value;
	}
	/**
	 * Eコマース定義値をDBから取得
	 *
	 * @return array		取得データ
	 */
	function _loadConfig()
	{
		$retVal = array();

		// 定義を読み込み
		$ret = $this->db->getAllConfig($rows);
		if ($ret){
			// 取得データを連想配列にする
			$configCount = count($rows);
			for ($i = 0; $i < $configCount; $i++){
				$key = $rows[$i]['cg_id'];
				$value = $rows[$i]['cg_value'];
				$retVal[$key] = $value;
			}
		}
		return $retVal;
	}
	/**
	 * 実行可能な配送方法を取得
	 *
	 * @param string	$lang				言語
	 * @param function	$callback			コールバック関数
	 * @return 			なし
	 */
	function getActiveDelivMethod($lang, $callback)
	{
		$this->db->getActiveDelivMethod($lang, 0/*セットID*/, $callback);
	}
	/**
	 * 商品情報を取得
	 *
	 * @param int		$id					商品ID
	 * @param string	$langId				言語
	 * @param array     $rowProduct			商品レコード
	 * @param array     $rowPrice			商品価格
	 * @param array     $rowImage			商品画像
	 * @param array     $rowStatus			商品ステータス
	 * @param array     $rowCategory		商品カテゴリー
	 * @return bool							取得 = true, 取得なし= false
	 */
	function getProductInfo($id, $langId, &$rowProduct, &$rowPrice, &$rowImage, &$rowStatus, &$rowCategory)
	{
		$ret = $this->db->getProductInfo($id, $langId, $rowProduct, $rowPrice, $rowImage, $rowStatus, $rowCategory);
		return $ret;
	}
	/**
	 * コンテンツ一覧の表示設定を取得
	 *
	 * @return array				表示項目数,ソート順(0=昇順,1=降順),サムネール表示可否の配列
	 */
	function getPublicContentViewConfig()
	{
	//	$itemCount = $this->getConfig(self::CF_ENTRY_VIEW_COUNT);
		$itemCount = 10;
		$showThumb = 1;
		return array($itemCount, 1, $showThumb);
	}
	/**
	 * 公開中の製品数を取得。アクセス制限も行う。
	 *
	 * @param timestamp $now				現在日時
	 * @param timestamp	$startDt			期間(開始日)
	 * @param timestamp	$endDt				期間(終了日)
	 * @param string,array	$keywords		検索キーワード
	 * @param string	$langId				言語
	 * @param int		$categoryId			カテゴリーID(nullのとき指定なし)
	 * @return int							項目数
	 */
	function getPublicContentCount($now, $startDt, $endDt, $keywords, $langId, $categoryId = null)
	{
		global $gEnvManager;
		
		$userId = $gEnvManager->getCurrentUserId();
		$itemCount = $this->db->getPublicProductItemsCount($now, $startDt, $endDt, $keywords, $langId, $userId, $categoryId);
		return $itemCount;
	}
	/**
	 * 公開中の製品を取得。アクセス制限も行う。
	 *
	 * @param int		$limit				取得する項目数
	 * @param int		$page				取得するページ(1～)
	 * @param int,array	$productId			製品ID(0のときは期間で取得)
	 * @param timestamp $now				現在日時
	 * @param timestamp	$startDt			期間(開始日)
	 * @param timestamp	$endDt				期間(終了日)
	 * @param string,array	$keywords		検索キーワード
	 * @param string	$langId				言語
	 * @param int		$order				取得順(0=昇順,1=降順)
	 * @param function	$callback			コールバック関数
	 * @param int		$categoryId			カテゴリーID(nullのとき指定なし)
	 * @return 			なし
	 */
	function getPublicContentList($limit, $page, $productId, $now, $startDt, $endDt, $keywords, $langId, $order, $callback, $categoryId = null)
	{
		global $gEnvManager;
		
		$userId = $gEnvManager->getCurrentUserId();
		$this->db->getPublicProductItems($limit, $page, $productId, $now, $startDt, $endDt, $keywords, $langId, $order, $userId, $callback, $categoryId);
	}
	/**
	 * 公開中の製品の前後の製品の項目を取得
	 *
	 * @param int       $type				前後製品のタイプ(0=前方,1=後方)
	 * @param int       $contentId			基準となるコンテンツのID
	 * @param timestamp $now				現在日時
	 * @param timestamp	$startDt			期間(開始日)
	 * @param timestamp	$endDt				期間(終了日)
	 * @param string,array	$keywords		検索キーワード
	 * @param string	$langId				言語
	 * @param int		$order				取得順(0=昇順,1=降順)
	 * @param int		$categoryId			カテゴリーID(nullのとき指定なし)
	 * @return array 						製品のレコード。取得なしの場合はfalseを返す。
	 */
/*	function getPublicPrevNextEntry($type, $contentId, $now, $startDt, $endDt, $keywords, $langId, $order, $categoryId = null)
	{
		global $gEnvManager;
		
		$userId = $gEnvManager->getCurrentUserId();
		$row = $this->db->getPublicPrevNextEntry($type, $contentId, $now, $startDt, $endDt, $keywords, $langId, $order, $userId, $categoryId);
		return $row;
	}*/
	/**
	 * コンテンツのサムネール画像パスを取得
	 *
	 * @param int $id			コンテンツID
	 * @param string	$langId				言語
	 * @return string			画像パス(resourceディレクトリからの相対パス)
	 */
	function getPublicContentThumb($id, $langId)
	{
		global $gEnvManager;
		
		$url = '';
		
		$ret = $this->db->getProductImage($id, $langId, $rows);
		if ($ret){
			// 標準(standard)の画像を取得
			$url = $rows[0]['im_url'];
			
			// resourceディレクトリからの相対パスに変換
			if (!strStartsWith($url, '/')) $url = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END . '/' . M3_DIR_NAME_RESOURCE, '', $url);
		}
		return $url;
	}
}
?>
