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
 * @copyright  Copyright 2006-2011 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: ec_product_display2Db.php 4495 2011-12-05 12:14:03Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getDbPath() . '/baseDb.php');

class ec_product_display2Db extends BaseDb
{
	/**
	 * 商品を商品ID、言語IDで取得
	 *
	 * @param int		$id					商品ID
	 * @param string	$langId				言語ID
	 * @param array     $priceRows			商品価格
	 * @param array     $imageRows			商品画像
	 * @param array     $statusRows			商品ステータス
	 * @return bool							取得 = true, 取得なし= false
	 */
	function getProductInfo($id, $langId, &$priceRows, &$imageRows, &$statusRows)
	{
		$queryStr  = 'SELECT * FROM product_price ';
		$queryStr .=   'WHERE pp_deleted = false ';// 削除されていない
		$queryStr .=     'AND pp_product_id = ? ';
		$this->selectRecords($queryStr, array($id), $priceRows);
		
		$queryStr  = 'SELECT * FROM product_image ';
		$queryStr .=   'WHERE im_deleted = false ';// 削除されていない
		$queryStr .=     'AND im_type = 2 ';		// 商品画像
		$queryStr .=     'AND im_id = ? ';
		$queryStr .=     'AND im_language_id = ? ';
		$this->selectRecords($queryStr, array($id, $langId), $imageRows);
		
		// 商品ステータス
		$queryStr  = 'SELECT * FROM product_status ';
		$queryStr .=   'WHERE ps_deleted = false ';// 削除されていない
		$queryStr .=     'AND ps_id = ? ';
		$queryStr .=     'AND ps_language_id = ? ';
		$this->selectRecords($queryStr, array($id, $langId), $statusRows);
		return true;
	}
	/**
	 * 商品を取得
	 *
	 * @param string	$lang				言語
	 * @param array		$productArray		商品ID
	 * @param function	$callback			コールバック関数
	 * @return 			なし
	 */
	function getProduct($lang, $productArray, $callback)
	{
		$productIdStr = implode(',', $productArray);
		
		// CASE文作成
		$caseStr = 'CASE pt_id ';
		for ($i = 0; $i < count($productArray); $i++){
			$caseStr .= 'WHEN ' . $productArray[$i] . ' THEN ' . $i . ' ';
		}
		$caseStr .= 'END AS no';

		$queryStr = 'SELECT *, ' . $caseStr . ' FROM product ';
		$queryStr .=  'WHERE pt_deleted = false ';		// 削除されていない
		$queryStr .=    'AND pt_visible = true ';		// 表示可能
		$queryStr .=    'AND pt_id in (' . $productIdStr . ') ';
		$queryStr .=    'AND pt_language_id = ? ';
		$queryStr .=  'ORDER BY no';
		$this->selectLoop($queryStr, array($lang), $callback);
	}
	/**
	 * 通貨情報を取得
	 *
	 * @param string	$type				単位タイプ
	 * @param string	$lang				言語
	 * @param array     $row				レコード
	 * @return bool							取得 = true, 取得なし= false
	 */
	function getCurrency($type, $lang, &$row)
	{
		$queryStr = 'SELECT * FROM currency ';
		$queryStr .=  'WHERE cu_id = ? AND cu_language_id = ? ';
		$ret = $this->selectRecord($queryStr, array($type, $lang), $row);
		return $ret;
	}
	/**
	 * 画像情報を取得
	 *
	 * @param string	$type			画像タイプ
	 * @param array     $row				レコード
	 * @return bool							取得 = true, 取得なし= false
	 */
	function getProductImageInfo($type, &$row)
	{
		$queryStr  = 'SELECT * FROM image_size ';
		$queryStr .=   'WHERE is_id = ? ';
		$ret = $this->selectRecord($queryStr, array($type), $row);
		return $ret;
	}
}
?>
