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
 * @version    SVN: $Id: epsilonDb.php 5437 2012-12-07 13:14:59Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getDbPath() . '/baseDb.php');

class epsilonDb extends BaseDb
{
	/**
	 * 受注情報を取得
	 *
	 * @param int $id				受注ID
	 * @param array $row			取得レコード
	 * @return						true=成功、false=失敗
	 */
/*	function getOrder($id, &$row)
	{
		$queryStr = 'SELECT * FROM order_header WHERE or_id = ? AND or_deleted = false';
		$ret = $this->selectRecord($queryStr, array($id), $row);
		return $ret;
	}*/
	/**
	 * 先頭の受注詳細を取得
	 *
	 * @param string	$orderId			受注ID
	 * @param string	$lang				言語ID
	 * @param array $row					取得レコード
	 * @return								true=成功、false=失敗
	 */
/*	function getFirstOrderDetail($orderId, $lang, &$row)
	{
		$queryStr  = 'SELECT * FROM order_detail LEFT JOIN order_header ON od_order_id = or_id AND or_deleted = false ';
		$queryStr .=   'LEFT JOIN currency ON or_currency_id = cu_id AND cu_language_id = ? ';
		$queryStr .=   'LEFT JOIN product_type ON od_product_class = py_product_class AND od_product_type_id = py_id AND or_language_id = py_language_id AND py_deleted = false ';
		$queryStr .=   'LEFT JOIN photo ON od_product_id = ht_id AND cu_language_id = ht_language_id AND ht_deleted = false ';		// フォトギャラリー画像用
		$queryStr .=   'LEFT JOIN product ON od_product_id = pt_id AND cu_language_id = pt_language_id AND pt_deleted = false ';	// 一般商品用
		$queryStr .=   'WHERE od_order_id = ? AND od_deleted = false ';
		$queryStr .=   'ORDER BY od_index ';
		$ret = $this->selectRecord($queryStr, array($lang, $orderId), $row);
		return $ret;
	}*/
	/**
	 * 注文情報取得
	 *
	 * @param  int    $userId	ユーザID
	 * @param  string $orderNo	注文番号
	 * @param  array  $row		取得レコード
	 * @return bool				true=正常取得、false=取得できず
	 */
	function getOrderRecord($userId, $orderNo, &$row)
	{
		$queryStr  = 'SELECT * FROM order_header ';
		$queryStr .=   'WHERE or_deleted = false ';
		$queryStr .=     'AND or_user_id = ? ';
		$queryStr .=     'AND or_order_no = ? ';
		$ret = $this->selectRecord($queryStr, array($userId, $orderNo), $row);
		return $ret;
	}
}
?>
