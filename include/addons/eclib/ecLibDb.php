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
 * @version    SVN: $Id: ecLibDb.php 5450 2012-12-09 03:29:49Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getDbPath() . '/baseDb.php');

class ecLibDb extends BaseDb
{
	/**
	 * Eコマース定義値を取得をすべて取得
	 *
	 * @param array  $rows			レコード
	 * @return bool					1行以上取得 = true, 取得なし= false
	 */
	function getAllConfig(&$rows)
	{
		$queryStr  = 'SELECT * FROM commerce_config ';
		$queryStr .=   'ORDER BY cg_index';
		$retValue = $this->selectRecords($queryStr, array(), $rows);
		return $retValue;
	}
	/**
	 * 通貨情報を取得
	 *
	 * @param string	$id					通貨種別ID
	 * @param string	$lang				言語ID
	 * @param array     $row				レコード
	 * @return bool							取得 = true, 取得なし= false
	 */
	function getCurrency($id, $lang, &$row)
	{
		$queryStr  = 'SELECT * FROM currency ';
		$queryStr .=   'WHERE cu_id = ? ';
		$queryStr .=   'AND cu_language_id = ? ';
		$ret = $this->selectRecord($queryStr, array($id, $lang), $row);
		return $ret;
	}
	/**
	 * 税情報を取得
	 *
	 * @param string	$id					税種別ID
	 * @param string	$lang				言語ID
	 * @param array     $row				レコード
	 * @return bool							取得 = true, 取得なし= false
	 */
	function getTaxType($id, $lang, &$row)
	{
		$queryStr  = 'SELECT * FROM tax_type ';
		$queryStr .=   'WHERE tt_id = ? ';
		$queryStr .=   'AND tt_language_id = ? ';
		$ret = $this->selectRecord($queryStr, array($id, $lang), $row);
		return $ret;
	}
	
	/**
	 * 税率を取得
	 *
	 * @param string	$rateTypeId			税率種別ID
	 * @param timestamp $dt					基準日
	 * @return float						0以外=税率、0=取得値なし
	 */
	function getTaxRate($rateTypeId, $dt = null)
	{
		$queryStr  = 'SELECT * FROM tax_rate ';
		$queryStr .=   'WHERE tr_id = ? ';
		$queryStr .=   'ORDER BY tr_priority';
		$ret = $this->selectRecord($queryStr, array($rateTypeId), $row);
		if ($ret){
			return $row['tr_rate'];
		} else {
			return 0;
		}
	}
	/**
	 * カートIDの最大シリアルNoを取得
	 *
	 * @return int		最大シリアルNo
	 */
	function getMaxSerialOfBasket()
	{
		$retValue = 0;
		$queryStr = 'select max(sh_serial) as m from shop_cart';
		$ret = $this->selectRecord($queryStr, array(), $row);
		if ($ret) $retValue = $row['m'];
		return $retValue;
	}
	/**
	 * デフォルト通貨を取得
	 *
	 * @param string	デフォルト通貨
	 */
	function getDefaultCurrency()
	{
		$retValue = '';
		$queryStr = 'select cg_value from commerce_config ';
		$queryStr .=  'where cg_id = ?';
		$ret = $this->selectRecord($queryStr, array('default_currency'), $row);
		if ($ret) $retValue = $row['cg_value'];
		return $retValue;
	}
	/**
	 * 自動生成会員番号の最大数
	 *
	 * @param string	$head			ヘッダ部の文字列
	 * @param int		最大数(見つからないときは0)
	 */
	function getMaxMemberNo($head)
	{
		$retNo = 0;
		$queryStr = 'SELECT sm_member_no FROM shop_member ';
		$queryStr .=  'WHERE sm_member_no LIKE \'' . $head . '%\' ';
		$queryStr .=  'ORDER BY sm_member_no DESC ';
		$ret = $this->selectRecord($queryStr, array(), $row);
		if ($ret){
			$retValue = $row['sm_member_no'];
			$retValue = str_replace($head, '', $retValue);// ヘッダ部削除
			if (is_numeric($retValue)) $retNo = intval($retValue);
		}
		return $retNo;
	}
	/**
	 * 仮会員を正会員に変更
	 *
	 * @param int $userId			ユーザID兼データ更新ユーザ
	 * @param string $memNo			会員番号
	 * @return						true=成功、false=失敗
	 */
	function makeTmpMemberToProperMember($userId, $memNo)
	{
//		global $gInstanceManager;
		
		$now = date("Y/m/d H:i:s");	// 現在日時
				
		// トランザクション開始
		$this->startTransaction();
		
		$queryStr = 'SELECT * FROM shop_tmp_member WHERE sb_login_user_id = ? AND sb_deleted = false';
		$ret = $this->selectRecord($queryStr, array($userId), $row);
		if (!$ret){
			// トランザクション確定
			$this->endTransaction();
			return false;
		}
		
		// 新規IDを作成
		$newId = 1;
		$queryStr = 'select max(sm_id) as ms from shop_member ';
		$ret = $this->selectRecord($queryStr, array(), $maxRow);
		if ($ret) $newId = $maxRow['ms'] + 1;
		
		$type = 1;		// 会員種別(個人)

		// 会員番号の自動生成
		/*
		$memNo = '';
		if ($generateMemNo){
			//$memNo = $gInstanceManager->getObject(self::EC_LIB_ID)->generateMemberNo();
			$memNo = $this->generateMemberNo();
		}*/
		// 新規レコードを追加
		$queryStr  = 'INSERT INTO shop_member (';
		$queryStr .=   'sm_id, ';
		$queryStr .=   'sm_history_index, ';
		$queryStr .=   'sm_language_id, ';
		$queryStr .=   'sm_type, ';
		$queryStr .=   'sm_member_no, ';
		$queryStr .=   'sm_person_info_id, ';
		$queryStr .=   'sm_login_user_id, ';
		$queryStr .=   'sm_create_user_id, ';
		$queryStr .=   'sm_create_dt ';
		$queryStr .= ') VALUES (';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?) ';
		$ret = $this->execStatement($queryStr, array($newId, 0, $row['sb_language_id'], $type, $memNo, $row['sb_person_info_id'], $row['sb_login_user_id'], $userId, $now));
		
		// 仮会員の情報を削除
		$queryStr  = 'UPDATE shop_tmp_member ';
		$queryStr .=   'SET sb_deleted = true, ';	// 削除
		$queryStr .=     'sb_update_user_id = ?, ';
		$queryStr .=     'sb_update_dt = ? ';
		$queryStr .=   'WHERE sb_serial = ?';
		$ret = $this->execStatement($queryStr, array($userId, $now, $row['sb_serial']));
								
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;		
	}
	/**
	 * カートヘッダ情報を取得
	 *
	 * @param string	$cartId				カートID
	 * @param string	$lang				言語
	 * @param array     $row				レコード
	 * @return bool							取得 = true, 取得なし= false
	 */
	function getCartHead($cartId, $lang, &$row)
	{
		$queryStr = 'SELECT * FROM shop_cart ';
		$queryStr .=  'WHERE sh_id = ? AND sh_language_id = ? ';
		$ret = $this->selectRecord($queryStr, array($cartId, $lang), $row);
		return $ret;
	}
	/**
	 * カート内容を取得を取得
	 *
	 * @param string	$cartId				カートID
	 * @param string	$lang				言語
	 * @param string    $productClass		商品クラス
	 * @param function	$callback			コールバック関数
	 * @return 			なし
	 */
	function getCartItems($cartId, $lang, $productClass, $callback)
	{
		$queryStr  = 'SELECT * FROM shop_cart_item LEFT JOIN currency ON si_currency_id = cu_id AND cu_language_id = ? ';
		$queryStr .=   'LEFT JOIN shop_cart ON si_head_serial = sh_serial ';
		
		// 商品情報の付加
		if (empty($productClass)){			// 一般商品
			$queryStr .=   'LEFT JOIN product ON si_product_id = pt_id AND sh_language_id = pt_language_id AND pt_deleted = false ';
		} else if ($productClass == 'photo'){		// フォトギャラリー商品の場合
			$queryStr .=   'LEFT JOIN photo ON si_product_id = ht_id AND sh_language_id = ht_language_id AND ht_deleted = false ';
			$queryStr .=   'LEFT JOIN product_type ON si_product_class = py_product_class AND si_product_type_id = py_id AND sh_language_id = py_language_id AND py_deleted = false ';
			if ($this->getDbType() == M3_DB_TYPE_PGSQL){		// PostgreSQLの場合
				$queryStr .=   'LEFT JOIN photo_product ON si_product_type_id = hp_id::text AND sh_language_id = hp_language_id AND hp_deleted = false ';		// ショップ関連商品
			} else {
				$queryStr .=   'LEFT JOIN photo_product ON si_product_type_id = hp_id AND sh_language_id = hp_language_id AND hp_deleted = false ';		// ショップ関連商品
			}
		}
		$queryStr .=   'WHERE si_product_class = ? ';
		$queryStr .=     'AND sh_id = ? ';
		$queryStr .=     'AND sh_language_id = ? ';
		$queryStr .=   'ORDER BY cu_index,si_serial';
		$this->selectLoop($queryStr, array($lang, $productClass, $cartId, $lang), $callback);
	}
	/**
	 * カート内容を商品クラスを取得
	 *
	 * @param string	$cartId		カートID
	 * @param string	$lang		言語
	 * @return 			array		商品クラス
	 */
	function getProductClassInCart($cartId, $lang)
	{
		$productClass = array();
		$queryStr  = 'SELECT DISTINCT si_product_class FROM shop_cart_item LEFT JOIN shop_cart ON si_head_serial = sh_serial ';
		$queryStr .=   'WHERE sh_id = ? ';
		$queryStr .=     'AND sh_language_id = ? ';
		$queryStr .=   'ORDER BY si_product_class';
		$ret = $this->selectRecords($queryStr, array($cartId, $lang), $rows);
		if ($ret){
			for ($i = 0; $i < count($rows); $i++){
				$productClass[] = $rows[$i]['si_product_class'];
			}
		}
		return $productClass;
	}
	/**
	 * カート商品情報を取得
	 *
	 * @param int		$serial				カートシリアル番号
	 * @param string    $productClass		商品クラス
	 * @param int   	$productId			商品ID
	 * @param string    $productType		商品タイプ
	 * @param array     $row				レコード
	 * @return bool							取得 = true, 取得なし= false
	 */
	function getCartItem($serial, $productClass, $productId, $productType, &$row)
	{
		$queryStr  = 'SELECT * FROM shop_cart_item ';
		$queryStr .=   'WHERE si_head_serial = ? ';
		$queryStr .=   'AND si_product_class = ? ';
		$queryStr .=   'AND si_product_id = ? ';
		$queryStr .=   'AND si_product_type_id = ? ';
		$ret = $this->selectRecord($queryStr, array($serial, $productClass, $productId, $productType), $row);
		return $ret;
	}
	/**
	 * カート商品情報をインデックス番号で取得
	 *
	 * @param string	$cartId				カートID
	 * @param string	$lang				言語
	 * @param string    $productClass		商品クラス
	 * @param int   	$index				項目インデックス番号
	 * @param array     $row				レコード
	 * @return bool							取得 = true, 取得なし= false
	 */
	function getCartItemByIndex($cartId, $lang, $productClass, $index, &$row)
	{
		$queryStr  = 'SELECT * FROM shop_cart_item LEFT JOIN currency ON si_currency_id = cu_id AND cu_language_id = ? ';
		$queryStr .=   'LEFT JOIN shop_cart ON si_head_serial = sh_serial ';
		
		// 商品情報の付加
		if (empty($productClass)){			// 一般商品
			$queryStr .=   'LEFT JOIN product ON si_product_id = pt_id AND sh_language_id = pt_language_id AND pt_deleted = false ';
		} else if ($productClass == 'photo'){		// フォトギャラリー商品の場合
			$queryStr .=   'LEFT JOIN photo ON si_product_id = ht_id AND sh_language_id = ht_language_id AND ht_deleted = false ';
			$queryStr .=   'LEFT JOIN product_type ON si_product_class = py_product_class AND si_product_type_id = py_id AND sh_language_id = py_language_id AND py_deleted = false ';
		}
		$queryStr .=   'WHERE si_product_class = ? ';
		$queryStr .=     'AND sh_id = ? ';
		$queryStr .=     'AND sh_language_id = ? ';
		$queryStr .=   'ORDER BY cu_index,si_serial limit 1 offset ' . $index;
		$ret = $this->selectRecord($queryStr, array($lang, $productClass, $cartId, $lang), $row);
		return $ret;
	}
	/**
	 * カートに商品を追加
	 *
	 * @param int		$serial			カートシリアル番号
	 * @param int		$itemSerial		カート商品シリアル番号
	 * @param string	$cartId			カートID
	 * @param string    $productClass	商品クラス
	 * @param string	$productId		商品ID
	 * @param string    $productType	商品タイプ
	 * @param string	$lang			言語
	 * @param string	$currency		通貨
	 * @param float	    $priceWithTax	税込み価格(単位個数あたり)
	 * @param int		$quantity		数量
	 * @return bool						取得 = true, 取得なし= false
	 */
	function addCartItem($serial, $itemSerial, $cartId, $productClass, $productId, $productType, $lang, $currency, $priceWithTax, $quantity)
	{
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $this->gEnv->getCurrentUserId();	// 現在のユーザ
		
		// トランザクション開始
		$this->startTransaction();
		
		if (empty($serial)){		// カートがまだ存在しないとき
			// 新規作成
			$queryStr = "INSERT INTO shop_cart (sh_id, sh_language_id, sh_user_id, sh_dt) VALUES (?, ?, ?, ?)";
			$params = array($cartId, $lang, $userId, $now);
			$this->execStatement($queryStr, $params);
			
			$queryStr = 'SELECT MAX(sh_serial) AS m FROM shop_cart';
			$ret = $this->selectRecord($queryStr, array(), $row);
			if ($ret){
				$maxSerial = $row['m'];
			} else {
				$this->endTransaction();
				return false;
			}
			
			// 商品の登録
			$price = $priceWithTax * $quantity;
			$queryStr = "INSERT INTO shop_cart_item (si_head_serial, si_product_class, si_product_id, si_product_type_id, si_currency_id, si_quantity, si_subtotal) VALUES (?, ?, ?, ?, ?, ?, ?)";
			$params = array($maxSerial, $productClass, $productId, $productType, $currency, $quantity, $price);
			$this->execStatement($queryStr, $params);			
		} else if (empty($itemSerial)){		// カート商品が存在しないとき
			// カートヘッダの日付を更新
			$queryStr  = 'UPDATE shop_cart ';
			$queryStr .=   'SET sh_dt = ? ';
			$queryStr .=   'WHERE sh_serial = ? ';
			$this->execStatement($queryStr, array($now, $serial));
		
			// 商品の登録
			$price = $priceWithTax * $quantity;
			$queryStr = "INSERT INTO shop_cart_item (si_head_serial, si_product_class, si_product_id, si_product_type_id, si_currency_id, si_quantity, si_subtotal) VALUES (?, ?, ?, ?, ?, ?, ?)";
			$params = array($serial, $productClass, $productId, $productType, $currency, $quantity, $price);
			$this->execStatement($queryStr, $params);			
		} else {		// カートに商品が存在するときは数量、小計を更新
			// カートヘッダの日付を更新
			$queryStr  = 'UPDATE shop_cart ';
			$queryStr .=   'SET sh_dt = ? ';
			$queryStr .=   'WHERE sh_serial = ? ';
			$this->execStatement($queryStr, array($now, $serial));
					
			$queryStr  = 'SELECT * FROM shop_cart_item ';
			$queryStr .=  'WHERE si_serial = ?';
			$ret = $this->selectRecord($queryStr, array($itemSerial), $row);
			if (!$ret){
				$this->endTransaction();
				return false;
			}
		
			// カート商品を更新
			$newQuantity = $quantity + $row['si_quantity'];
			$newPrice = $priceWithTax * $quantity + $row['si_subtotal'];
			$queryStr  = 'UPDATE shop_cart_item ';
			$queryStr .=   'SET si_quantity = ?, ';
			$queryStr .=   'si_subtotal = ? ';
			$queryStr .=   'WHERE si_serial = ? ';
			$this->execStatement($queryStr, array($newQuantity, $newPrice, $itemSerial));
		}
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * カート商品情報を無効化
	 *
	 * @param int		$serial				カートシリアル番号
	 * @param int		$itemSerial		カート商品シリアル番号
	 * @return bool							取得 = true, 取得なし= false
	 */
	function voidCartItem($serial, $itemSerial)
	{
		if (empty($serial)) return true;		// カートがまだ存在しないとき
			
		$now = date("Y/m/d H:i:s");	// 現在日時
				
		// トランザクション開始
		$this->startTransaction();
		
		// カートヘッダの日付を更新
		$queryStr  = 'UPDATE shop_cart ';
		$queryStr .=   'SET sh_dt = ? ';
		$queryStr .=   'WHERE sh_serial = ? ';
		$this->execStatement($queryStr, array($now, $serial));
		
		// カート商品を無効化
		$queryStr  = 'UPDATE shop_cart_item ';
		$queryStr .=   'SET si_available = false ';
		$queryStr .=   'WHERE si_serial = ? ';
		$this->execStatement($queryStr, array($itemSerial));
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * カート内の商品を削除
	 *
	 * @param int		$serial				カートシリアル番号
	 * @param int		$itemSerial			カート商品シリアル番号。0のときすべて削除。
	 * @return bool							取得 = true, 取得なし= false
	 */
	function deleteCartItem($serial, $itemSerial = 0)
	{
		if ($serial == 0) return true;		// カートがまだ存在しないとき
			
		$now = date("Y/m/d H:i:s");	// 現在日時
				
		// トランザクション開始
		$this->startTransaction();
		
		// カートヘッダの日付を更新
		$queryStr  = 'UPDATE shop_cart ';
		$queryStr .=   'SET sh_dt = ? ';
		$queryStr .=   'WHERE sh_serial = ? ';
		$this->execStatement($queryStr, array($now, $serial));
		
		if (empty($itemSerial)){			// カート内の商品をすべて削除
			// 全商品を削除
			$queryStr  = 'DELETE FROM shop_cart_item ';
			$queryStr .=   'WHERE si_head_serial = ? ';
			$this->execStatement($queryStr, array($serial));
		} else {
			// カート商品を削除
			$queryStr  = 'DELETE FROM shop_cart_item ';
			$queryStr .=   'WHERE si_serial = ? ';
			$this->execStatement($queryStr, array($itemSerial));
		}
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * カート商品を更新
	 *
	 * @param int		$serial				カート商品シリアル番号
	 * @param string	$currency			通貨
	 * @param int		$quantity			数量
	 * @param float	    $subtotal			小計
	 * @param bool		$available			利用可能かどうか
	 * @return bool							取得 = true, 取得なし= false
	 */
	function updateCartItem($serial, $currency, $quantity, $subtotal, $available)
	{
		// トランザクション開始
		$this->startTransaction();
	
		// カート商品を更新
		$queryStr  = 'UPDATE shop_cart_item ';
		$queryStr .=   'SET si_currency_id = ?, ';
		$queryStr .=   'si_quantity = ?, ';
		$queryStr .=   'si_subtotal = ?, ';
		$queryStr .=   'si_available = ? ';
		$queryStr .=   'WHERE si_serial = ? ';
		$this->execStatement($queryStr, array($currency, $quantity, $subtotal, intval($available), $serial));
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * カートヘッダ日付を更新
	 *
	 * @param int		$serial				カートシリアル番号
	 * @return bool							成功 = true, 失敗 = false
	 */
	function updateCartDt($serial)
	{
		if ($serial == 0) return true;		// カートがまだ存在しないとき
			
		$now = date("Y/m/d H:i:s");	// 現在日時
		
		// トランザクション開始
		$this->startTransaction();
		
		// カートヘッダの日付を更新
		$queryStr  = 'UPDATE shop_cart ';
		$queryStr .=   'SET sh_dt = ? ';
		$queryStr .=   'WHERE sh_serial = ? ';
		$this->execStatement($queryStr, array($now, $serial));
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return true;
	}
	/**
	 * 注文書の状態を更新
	 *
	 * @param int		$userId		ユーザID
	 * @param int		$status		カート状態
	 * @param string	$lang		言語
	 * @return bool					成功 = true, 失敗 = false
	 */
	function updateOrderSheetStatus($userId, $status, $lang = null)
	{
		global $gEnvManager;
		
		$serial = 0;
		if (is_null($lang)) $lang = $gEnvManager->getDefaultLanguage();
		
		// トランザクション開始
		$this->startTransaction();
		
		$queryStr  = 'SELECT * FROM order_sheet ';
		$queryStr .=   'WHERE oe_user_id = ? AND oe_language_id = ? ';
		$ret = $this->selectRecord($queryStr, array($userId, $lang), $row);
		if ($ret) $serial = $row['oe_serial'];
		
		// カートヘッダの日付を更新
		$queryStr  = 'UPDATE order_sheet ';
		$queryStr .=   'SET oe_status = ? ';
		$queryStr .=   'WHERE oe_serial = ? ';
		$this->execStatement($queryStr, array($status, $serial));
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return true;
	}
	/**
	 * 注文書の状態を取得
	 *
	 * @param int		$userId		ユーザID
	 * @param string	$lang		言語
	 * @return int					カート状態
	 */
	function getOrderSheetStatus($userId, $lang = null)
	{

		global $gEnvManager;

		$status = 0;
		$serial = 0;
		if (is_null($lang)) $lang = $gEnvManager->getDefaultLanguage();
		
		$queryStr  = 'SELECT * FROM order_sheet ';
		$queryStr .=   'WHERE oe_user_id = ? AND oe_language_id = ? ';
		$ret = $this->selectRecord($queryStr, array($userId, $lang), $row);
		if ($ret) $serial = $row['oe_serial'];
		
		$queryStr  = 'SELECT oe_status FROM order_sheet ';
		$queryStr .=   'WHERE oe_serial = ?';
		$ret = $this->selectRecord($queryStr, array($serial), $row);
		if ($ret) $status = $row['oe_status'];
		return $status;
	}
}
?>
