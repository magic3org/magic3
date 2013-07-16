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
 * @version    SVN: $Id: productrateDb.php 5436 2012-12-07 09:55:12Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getDbPath() . '/baseDb.php');

class productrateDb extends BaseDb
{
	/**
	 * カート内容を取得を取得
	 *
	 * @param string	$cartId				カートID
	 * @param string	$lang				言語
	 * @param function	$callback			コールバック関数
	 * @return 			なし
	 */
	function getCartItemList($cartId, $lang, $callback)
	{
		$queryStr = 'SELECT * FROM shop_cart ';
		$queryStr .=  'WHERE sh_id = ? AND sh_language_id = ? ';
		$ret = $this->selectRecord($queryStr, array($cartId, $lang), $row);
		if ($ret){
			$queryStr = 'SELECT * FROM shop_cart_item LEFT JOIN currency ON si_currency_id = cu_id AND cu_language_id = ? ';
			$queryStr .=  'WHERE si_head_serial = ? ';
			$queryStr .=  'ORDER BY cu_index,si_serial';
			$this->selectLoop($queryStr, array($lang, $row['sh_serial']), $callback, null);
		}
	}
	/**
	 * 商品を商品ID、言語IDで取得
	 *
	 * @param int		$id					商品ID
	 * @param string	$langId				言語ID
	 * @param array     $row				レコード
	 * @return bool							取得 = true, 取得なし= false
	 */
	function getProductByProductId($id, $langId, &$row)
	{
		$queryStr  = 'SELECT * from (product LEFT JOIN product_record ON pt_id = pe_product_id AND pt_language_id = pe_language_id) ';
		$queryStr .=   'LEFT JOIN _login_user ON pt_create_user_id = lu_id AND lu_deleted = false ';
		$queryStr .=   'WHERE pt_deleted = false ';	// 削除されていない
		$queryStr .=   'AND pt_id = ? ';
		$queryStr .=   'AND pt_language_id = ? ';
		$ret = $this->selectRecord($queryStr, array($id, $langId), $row);
		return $ret;
	}
}
?>
