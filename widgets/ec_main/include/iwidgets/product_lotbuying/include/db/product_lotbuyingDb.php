<?php
/**
 * DBクラス
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2012 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: product_lotbuyingDb.php 5437 2012-12-07 13:14:59Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getDbPath() . '/baseDb.php');

class product_lotbuyingDb extends BaseDb
{
	/**
	 * 商品クラスを取得
	 *
	 * @param string	$lang				言語
	 * @param function	$callback			コールバック関数
	 * @return 			なし
	 */
	function getAllProductClass($lang, $callback)
	{
		$queryStr  = 'SELECT * FROM product_class ';
		$queryStr .=   'WHERE pu_deleted = false ';
		$queryStr .=     'AND pu_language_id = ? ';
		$queryStr .=   'ORDER BY pu_index';
		$this->selectLoop($queryStr, array($lang), $callback);
	}
	/**
	 * 商品タイプを取得
	 *
	 * @param string	$productClass		商品クラス
	 * @param string	$lang				言語
	 * @param function	$callback			コールバック関数
	 * @return 			なし
	 */
	function getAllProductType($productClass, $lang, $callback)
	{
		$queryStr  = 'SELECT * FROM product_type ';
		$queryStr .=   'WHERE py_deleted = false ';
		$queryStr .=     'AND py_product_class = ? ';
		$queryStr .=     'AND py_language_id = ? ';
		$queryStr .=   'ORDER BY py_index';
		$this->selectLoop($queryStr, array($productClass, $lang), $callback);
	}
	/**
	 * 画像情報を取得
	 *
	 * @param int $id				画像ID
	 * @param string $productClass	商品クラス
	 * @param string $productType	製品ID
	 * @param string $priceType		価格タイプ
	 * @param string $lang			言語ID
	 * @param string $currency		通貨ID
	 * @param array $row			取得データ
	 * @return						true=正常、false=異常
	 */
	function getPhotoInfoWithPrice($id, $productClass, $productType, $priceType, $lang, $currency, &$row)
	{
		$queryStr  = 'SELECT * FROM photo RIGHT JOIN product_price ON (ht_id = pp_product_id OR pp_product_id = 0) AND pp_deleted = false ';
		$queryStr .=   'WHERE ht_deleted = false ';
		$queryStr .=     'AND ht_language_id = ? ';
		$queryStr .=     'AND ht_id = ? ';
		$queryStr .=     'AND pp_product_class = ? ';
		$queryStr .=     'AND pp_product_type_id = ? ';
		$queryStr .=     'AND pp_price_type_id = ? ';
		$queryStr .=     'AND pp_currency_id = ? ';
		$queryStr .=   'ORDER BY pp_product_id DESC';		// 商品価格マスターの商品ID
		$ret = $this->selectRecord($queryStr, array($lang, $id, $productClass, $productType, $priceType, $currency), $row);
		return $ret;
	}
	/**
	 * 商品を商品ID、言語IDで取得
	 *
	 * @param int		$id					商品ID
	 * @param string	$langId				言語ID
	 * @param string    $currencyId			通貨ID
	 * @param array     $row				レコード
	 * @param array     $imageRows			商品画像
	 * @return bool							取得 = true, 取得なし= false
	 */
	function getProductByProductId($id, $langId, $currencyId, &$row, &$imageRows)
	{
		$queryStr  = 'SELECT * FROM product RIGHT JOIN product_price ON (pt_id = pp_product_id OR pp_product_id = 0) AND pp_product_class = \'\' AND pp_deleted = false ';// 商品の個別価格とデフォルト価格を取得
		$queryStr .=   'WHERE pt_deleted = false ';	// 削除されていない
		$queryStr .=   'AND pt_id = ? ';
		$queryStr .=   'AND pt_language_id = ? ';
		$queryStr .=   'AND pp_currency_id = ? ';
		$queryStr .=   'ORDER BY pp_product_id DESC';		// デフォルト価格よりも個別価格を優先
		$ret = $this->selectRecord($queryStr, array($id, $langId, $currencyId), $row);
		if ($ret){
			$queryStr  = 'SELECT * FROM product_image ';
			$queryStr .=   'WHERE im_deleted = false ';// 削除されていない
			$queryStr .=     'AND im_type = 2 ';		// 商品画像
			$queryStr .=     'AND im_id = ? ';
			$queryStr .=     'AND im_language_id = ? ';
			$this->selectRecords($queryStr, array($id, $langId), $imageRows);
		}
		return $ret;
	}
}
?>
