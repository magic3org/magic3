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
 * @copyright  Copyright 2006-2017 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getDbPath() . '/baseDb.php');

class ec_mainProductDb extends BaseDb
{
	/**
	 * すべての言語を取得
	 *
	 * @param function	$callback			コールバック関数
	 * @return			true=取得、false=取得せず
	 */
	function getAllLang($callback)
	{
		$queryStr = 'SELECT * FROM _language ORDER BY ln_priority';
		$this->selectLoop($queryStr, array(), $callback, null);
	}
	/**
	 * 単位一覧を取得
	 *
	 * @param string	$lang				言語
	 * @param function	$callback			コールバック関数
	 * @return 			なし
	 */
	function getUnitType($lang, $callback)
	{
		$queryStr = 'SELECT * FROM unit_type ';
		$queryStr .=  'WHERE ut_language_id = ? ';
		$queryStr .=  'ORDER BY ut_index';
		$this->selectLoop($queryStr, array($lang), $callback, null);
	}
	/**
	 * 通貨一覧を取得
	 *
	 * @param string	$lang				言語
	 * @param function	$callback			コールバック関数
	 * @return 			なし
	 */
	function getCurrency($lang, $callback)
	{
		$queryStr = 'SELECT * FROM currency ';
		$queryStr .=  'WHERE cu_language_id = ? ';
		$queryStr .=  'ORDER BY cu_index';
		$this->selectLoop($queryStr, array($lang), $callback, null);
	}
	/**
	 * 税一覧を取得
	 *
	 * @param function	$callback			コールバック関数
	 * @return 			なし
	 */
	function getTaxType($callback)
	{
		$queryStr = 'SELECT * FROM tax_type ';
		$queryStr .=  'ORDER BY tt_index';
		$this->selectLoop($queryStr, array(), $callback, null);
	}
	/**
	 * 単位名から単位IDを取得
	 *
	 * @param string	$lang				言語
	 * @param string	$name				名前
	 * @return string						単位ID
	 */
	function getUnitTypeIdByName($lang, $name)
	{
		$queryStr = 'SELECT * FROM unit_type ';
		$queryStr .=  'WHERE ut_language_id = ? AND ut_name = ?';
		$ret = $this->selectRecord($queryStr, array($lang, $name), $row);
		if ($ret){
			return $row['ut_id'];
		} else {
			return '';
		}
	}
	/**
	 * 商品の対応言語を取得
	 *
	 * @param int		$id			商品カテゴリーID
	 * @return bool					true=取得、false=取得せず
	 */
	function getLangByProductId($id, &$rows)
	{
		$queryStr = 'SELECT ln_id, ln_name, ln_name_en FROM product LEFT JOIN _language ON pt_language_id = ln_id ';
		$queryStr .=  'WHERE pt_deleted = false ';	// 削除されていない
		$queryStr .=    'AND pt_id = ? ';
		$queryStr .=  'ORDER BY pt_id, ln_priority';
		$retValue = $this->selectRecords($queryStr, array($id), $rows);
		return $retValue;
	}
	/**
	 * 商品一覧を取得
	 *
	 * @param string	$id					商品カテゴリーID
	 * @param string	$lang				言語
	 * @param int		$productType		商品種別(1=単品商品、2=セット商品、3=オプション商品)
	 * @param function	$callback			コールバック関数
	 * @return 			なし
	 */
/*	function getProductByCategoryId($id, $lang, $productType, $callback)
	{
		$queryStr = 'SELECT * FROM product LEFT JOIN _login_user ON pt_create_user_id = lu_id AND lu_deleted = false ';
		$queryStr .=  'WHERE pt_deleted = false ';// 削除されていない
		$queryStr .=    'AND pt_product_type = ? ';		
		$queryStr .=    'AND pt_category_id = ? ';		
		$queryStr .=    'AND pt_language_id = ? ';
		$queryStr .=  'ORDER BY pt_sort_order';
		$this->selectLoop($queryStr, array($productType, $id, $lang), $callback, null);
	}*/
	/**
	 * すべての商品一覧を取得
	 *
	 * @param string	$lang				言語
	 * @param function	$callback			コールバック関数
	 * @return 			なし
	 */
	function getAllProductByLang($lang, $callback)
	{
		$queryStr = 'SELECT * FROM product LEFT JOIN _login_user ON pt_create_user_id = lu_id AND lu_deleted = false ';
		$queryStr .=  'WHERE pt_deleted = false ';// 削除されていない
		$queryStr .=    'AND pt_language_id = ? ';
		$queryStr .=  'ORDER BY pt_sort_order';
		$this->selectLoop($queryStr, array($lang), $callback, null);
	}
	/**
	 * すべての商品一覧を取得
	 *
	 * @param function	$callback			コールバック関数
	 * @return 			なし
	 */
	function getAllProduct($callback)
	{
		$queryStr = 'SELECT * FROM (product LEFT JOIN unit_type ON pt_unit_type_id = ut_id) ';
		$queryStr .=  'LEFT JOIN tax_type ON pt_tax_type_id = tt_id ';
		$queryStr .=  'WHERE pt_deleted = false ';// 削除されていない
		$queryStr .=  'ORDER BY pt_id';
		$this->selectLoop($queryStr, array(), $callback, null);
	}
	/**
	 * 商品をシリアル番号で取得
	 *
	 * @param int		$serial				シリアル番号
	 * @param array     $row				レコード
	 * @param array     $row2				商品価格
	 * @param array     $row3				商品画像
	 * @param array     $row4				商品ステータス
	 * @param array     $row5				商品カテゴリー
	 * @return bool							取得 = true, 取得なし= false
	 */
	function getProductBySerial($serial, &$row, &$row2, &$row3, &$row4, &$row5)
	{
		$queryStr  = 'SELECT * from product LEFT JOIN product_record ON pt_id = pe_product_id AND pt_language_id = pe_language_id ';
		$queryStr .=   'LEFT JOIN _login_user ON pt_create_user_id = lu_id AND lu_deleted = false ';
		$queryStr .=   'WHERE pt_serial = ? ';
		$ret = $this->selectRecord($queryStr, array($serial), $row);
		if ($ret){
			$queryStr  = 'SELECT * FROM product_price ';
			$queryStr .=   'WHERE pp_deleted = false ';// 削除されていない
			$queryStr .=     'AND pp_product_id = ? ';
			$queryStr .=     'AND pp_language_id = ? ';
			$this->selectRecords($queryStr, array($row['pt_id'], $row['pt_language_id']), $row2);
			
			$queryStr  = 'SELECT * FROM product_image ';
			$queryStr .=   'WHERE im_deleted = false ';// 削除されていない
			$queryStr .=     'AND im_type = 2 ';		// 商品画像
			$queryStr .=     'AND im_id = ? ';
			$queryStr .=     'AND im_language_id = ? ';
			$this->selectRecords($queryStr, array($row['pt_id'], $row['pt_language_id']), $row3);
			
			// 商品ステータス
			$queryStr  = 'SELECT * FROM product_status ';
			$queryStr .=   'WHERE ps_deleted = false ';// 削除されていない
			$queryStr .=     'AND ps_id = ? ';
			$queryStr .=     'AND ps_language_id = ? ';
			$this->selectRecords($queryStr, array($row['pt_id'], $row['pt_language_id']), $row4);
			
			// 商品カテゴリー
			$queryStr  = 'SELECT * FROM product_with_category ';
			$queryStr .=   'WHERE pw_product_serial = ? ';
			$queryStr .=  'ORDER BY pw_index ';
			$this->selectRecords($queryStr, array($row['pt_serial']), $row5);
		}
		return $ret;
	}
	/**
	 * 商品を商品ID、言語IDで取得
	 *
	 * @param int		$id					商品ID
	 * @param string	$langId				言語ID
	 * @param array     $row				レコード
	 * @param array     $row2				商品価格
	 * @param array     $row3				商品画像
	 * @param array     $row4				商品ステータス
	 * @param array     $row5				商品カテゴリー
	 * @return bool							取得 = true, 取得なし= false
	 */
	function getProductByProductId($id, $langId, &$row, &$row2, &$row3, &$row4, &$row5)
	{
		//$queryStr  = 'SELECT * FROM product LEFT JOIN _login_user ON pt_create_user_id = lu_id AND lu_deleted = false ';
		$queryStr  = 'SELECT * from product LEFT JOIN product_record ON pt_id = pe_product_id AND pt_language_id = pe_language_id ';
		$queryStr .=   'LEFT JOIN _login_user ON pt_create_user_id = lu_id AND lu_deleted = false ';
		$queryStr .=   'WHERE pt_deleted = false ';	// 削除されていない
		$queryStr .=   'AND pt_id = ? ';
		$queryStr .=   'AND pt_language_id = ? ';
		$ret = $this->selectRecord($queryStr, array($id, $langId), $row);
		if ($ret){
			$queryStr  = 'SELECT * FROM product_price ';
			$queryStr .=   'WHERE pp_deleted = false ';// 削除されていない
			$queryStr .=     'AND pp_product_id = ? ';
			$queryStr .=     'AND pp_language_id = ? ';
			$this->selectRecords($queryStr, array($row['pt_id'], $row['pt_language_id']), $row2);
			
			$queryStr  = 'SELECT * FROM product_image ';
			$queryStr .=   'WHERE im_deleted = false ';// 削除されていない
			$queryStr .=     'AND im_type = 2 ';		// 商品画像
			$queryStr .=     'AND im_id = ? ';
			$queryStr .=     'AND im_language_id = ? ';
			$this->selectRecords($queryStr, array($row['pt_id'], $row['pt_language_id']), $row3);
			
			// 商品ステータス
			$queryStr  = 'SELECT * FROM product_status ';
			$queryStr .=   'WHERE ps_deleted = false ';// 削除されていない
			$queryStr .=     'AND ps_id = ? ';
			$queryStr .=     'AND ps_language_id = ? ';
			$this->selectRecords($queryStr, array($row['pt_id'], $row['pt_language_id']), $row4);
			
			// 商品カテゴリー
			$queryStr  = 'SELECT * FROM product_with_category ';
			$queryStr .=   'WHERE pw_product_serial = ? ';
			$queryStr .=  'ORDER BY pw_index ';
			$this->selectRecords($queryStr, array($row['pt_serial']), $row5);
		}
		return $ret;
	}
	/**
	 * 商品を商品コード、言語IDで取得
	 *
	 * @param string	$code				商品コード
	 * @param string	$langId				言語ID
	 * @param array     $row				レコード
	 * @param array     $row2				商品価格
	 * @param array     $row3				商品画像
	 * @param array     $row4				商品ステータス
	 * @param array     $row5				商品カテゴリー
	 * @return bool							取得 = true, 取得なし= false
	 */
	function getProductByProductCode($code, $langId, &$row, &$row2, &$row3, &$row4, &$row5)
	{
		//$queryStr  = 'SELECT * FROM product LEFT JOIN _login_user ON pt_create_user_id = lu_id AND lu_deleted = false ';
		$queryStr  = 'SELECT * from product LEFT JOIN product_record ON pt_id = pe_product_id AND pt_language_id = pe_language_id ';
		$queryStr .=   'LEFT JOIN _login_user ON pt_create_user_id = lu_id AND lu_deleted = false ';
		$queryStr .=   'WHERE pt_deleted = false ';	// 削除されていない
		$queryStr .=   'AND pt_code = ? ';
		$queryStr .=   'AND pt_language_id = ? ';
		$ret = $this->selectRecord($queryStr, array($code, $langId), $row);
		if ($ret){
			$queryStr  = 'SELECT * FROM product_price ';
			$queryStr .=   'WHERE pp_deleted = false ';// 削除されていない
			$queryStr .=     'AND pp_product_id = ? ';
			$queryStr .=     'AND pp_language_id = ? ';
			$this->selectRecords($queryStr, array($row['pt_id'], $row['pt_language_id']), $row2);
			
			$queryStr  = 'SELECT * FROM product_image ';
			$queryStr .=   'WHERE im_deleted = false ';// 削除されていない
			$queryStr .=     'AND im_type = 2 ';		// 商品画像
			$queryStr .=     'AND im_id = ? ';
			$queryStr .=     'AND im_language_id = ? ';
			$this->selectRecords($queryStr, array($row['pt_id'], $row['pt_language_id']), $row3);
			
			// 商品ステータス
			$queryStr  = 'SELECT * FROM product_status ';
			$queryStr .=   'WHERE ps_deleted = false ';// 削除されていない
			$queryStr .=     'AND ps_id = ? ';
			$queryStr .=     'AND ps_language_id = ? ';
			$this->selectRecords($queryStr, array($row['pt_id'], $row['pt_language_id']), $row4);
			
			// 商品カテゴリー
			$queryStr  = 'SELECT * FROM product_with_category ';
			$queryStr .=   'WHERE pw_product_serial = ? ';
			$queryStr .=  'ORDER BY pw_index ';
			$this->selectRecords($queryStr, array($row['pt_serial']), $row5);
		}
		return $ret;
	}
	/**
	 * 商品の新規追加、更新
	 *
	 * @param int     $serial		シリアル番号(0=新規追加、0以外=更新)
	 * @param int	  $id			商品ID(0=商品ID新規作成)
	 * @param string  $lang			言語ID
	 * @param array   $otherParams	商品情報その他の値
	 * @param int     $stockCount	表示在庫数
	 * @param array   $prices		価格情報の配列
	 * @param array   $images		画像の配列
	 * @param array   $statuses		商品スタータスの配列
	 * @param array   $categories	商品カテゴリーの配列
	 * @param int     $newSerial	新規シリアル番号
	 * @return bool					true = 成功、false = 失敗
	 */
	function updateProduct($serial, $id, $lang, $otherParams, $stockCount, $prices, $images, $statuses, $categories, &$newSerial)
	{
		$userId = $this->gEnv->getCurrentUserId();	// 現在のユーザ
		$now = date("Y/m/d H:i:s");	// 現在日時
		$newSerial = 0;
		$startTran = false;			// この関数でトランザクションを開始したかどうか
		$updateFields = array();	// 更新するフィールド名
		$updateFields[] = 'pt_name';			// 商品名
		$updateFields[] = 'pt_code';			// 商品コード
		$updateFields[] = 'pt_sort_order';			// 表示順
		$updateFields[] = 'pt_visible';				// 表示するかどうか
		$updateFields[] = 'pt_product_type';		// 商品種別(1=単品商品、2=セット商品、3=オプション商品)
		$updateFields[] = 'pt_unit_type_id';		// 選択単位
		$updateFields[] = 'pt_unit_quantity';		// 数量
		$updateFields[] = 'pt_description';			// 説明
		$updateFields[] = 'pt_description_short';	// 簡易説明
		$updateFields[] = 'pt_meta_title';			// METAタグ、タイトル
		$updateFields[] = 'pt_meta_description';	// METAタグ、ページ要約
		$updateFields[] = 'pt_meta_keywords';		// METAタグ、検索用キーワード
		$updateFields[] = 'pt_search_keyword';		// 検索キーワード
		$updateFields[] = 'pt_site_url';			// 詳細情報URL
		$updateFields[] = 'pt_tax_type_id';			// 税種別
		$updateFields[] = 'pt_admin_note';		// 管理者用備考
		$updateFields[] = 'pt_deliv_type';		// 配送タイプ
		$updateFields[] = 'pt_deliv_fee';		// 配送単価
		$updateFields[] = 'pt_weight';			// 配送基準重量
						
		// トランザクション開始
		if (!$this->isInTransaction()){
			$this->startTransaction();
			$startTran = true;
		}
		
		if (empty($serial)){		// 新規追加のとき
			//if ($id == 0){		// IDが0のときは、商品IDを新規取得
			if (intval($id) <= 0){		// 商品IDが0以下のときは、商品IDを新規取得
				// IDを決定する
				$queryStr = 'SELECT MAX(pt_id) AS mid FROM product ';
				$ret = $this->selectRecord($queryStr, array(), $row);
				if ($ret){
					$pId = $row['mid'] + 1;
				} else {
					$pId = 1;
				}
				
				// 新規追加のときは商品IDが変更されていないかチェック
				if (intval($id) * (-1) != $pId){
					if ($startTran) $this->endTransaction();
					return false;
				}
			} else {
				$pId = $id;
			}
			// 前レコードの削除状態チェック
			$historyIndex = 0;
			$queryStr = 'SELECT * FROM product ';
			$queryStr .=  'WHERE pt_id = ? ';
			$queryStr .=    'AND pt_language_id = ? ';
			$queryStr .=  'ORDER BY pt_history_index DESC ';
			$queryStr .=    'LIMIT 1';
			$ret = $this->selectRecord($queryStr, array($pId, $lang), $row);
			if ($ret){
				if (!$row['pt_deleted']){		// レコード存在していれば終了
					if ($startTran) $this->endTransaction();
					return false;
				}
				$historyIndex = $row['pt_history_index'] + 1;
			}
		} else {		// 更新のとき
			// 指定のシリアルNoのレコードが削除状態でないかチェック
			$historyIndex = 0;		// 履歴番号
			$queryStr  = 'SELECT * FROM product ';
			$queryStr .=   'WHERE pt_serial = ? ';
			$ret = $this->selectRecord($queryStr, array($serial), $row);
			if ($ret){		// 既に登録レコードがあるとき
				if ($row['pt_deleted']){		// レコードが削除されていれば終了
					if ($startTran) $this->endTransaction();
					return false;
				}
				$historyIndex = $row['pt_history_index'] + 1;
			} else {		// 存在しない場合は終了
				if ($startTran) $this->endTransaction();
				return false;
			}
			$pId = $row['pt_id'];
			$lang = $row['pt_language_id'];
		
			// 古いレコードを削除
			$queryStr  = 'UPDATE product ';
			$queryStr .=   'SET pt_deleted = true, ';	// 削除
			$queryStr .=     'pt_update_user_id = ?, ';
			$queryStr .=     'pt_update_dt = ? ';
			$queryStr .=   'WHERE pt_serial = ?';
			$this->execStatement($queryStr, array($userId, $now, $serial));
		}
		// ##### データを追加 #####
		// キーを取得
		$keys = array_keys($otherParams);
		
		// クエリー作成
		$queryStr = 'INSERT INTO product (pt_id, pt_language_id, pt_history_index, ';
		$valueStr = '(?, ?, ?, ';
		$values = array($pId, $lang, $historyIndex);
		for ($i = 0; $i < count($keys); $i++){
			$queryStr .= $keys[$i] . ', ';
			$valueStr .= '?, ';
			$values[] = $otherParams[$keys[$i]];
		}

		// 更新の場合、値が設定されていない場合は、旧レコード値を設定
		if (!empty($serial)){
			for ($i = 0; $i < count($updateFields); $i++){
				$fieldName = $updateFields[$i];
				if (!in_array($fieldName, $keys)){		// フィールドがないとき
					$queryStr .= $fieldName . ', ';
					$valueStr .= '?, ';
					$values[] = $row[$fieldName];
				}
			}
		}
		
		// レコードを追加
		$queryStr .= 'pt_create_user_id, pt_create_dt) ';
		$valueStr .= '?, ?)';
		$values = array_merge($values, array($userId, $now));
		$queryStr .=  'VALUES ';
		$queryStr .=  $valueStr;
		$this->execStatement($queryStr, $values);

		// 新規のシリアル番号取得
		$queryStr = 'SELECT MAX(pt_serial) AS ns FROM product ';
		$ret = $this->selectRecord($queryStr, array(), $row);
		if ($ret) $newSerial = $row['ns'];
		
		// 表示在庫数更新
		$queryStr = 'SELECT * FROM product_record ';
		$queryStr .=  'WHERE pe_product_id = ? ';
		$queryStr .=    'AND pe_language_id = ? ';
		$ret = $this->selectRecord($queryStr, array($pId, $lang), $stockRow);
		if ($ret){	// データが存在するとき
			// データを更新
			$queryStr  = 'UPDATE product_record ';
			$queryStr .=   'SET pe_stock_count = ?, ';
			$queryStr .=     'pe_update_user_id = ?, ';
			$queryStr .=     'pe_update_dt = ? ';
			$queryStr .=   'WHERE pe_serial = ?';
			$this->execStatement($queryStr, array($stockCount, $userId, $now, $stockRow['pe_serial']));
		} else {
			$queryStr = 'INSERT INTO product_record ';
			$queryStr .=  '(pe_product_id, pe_language_id, pe_stock_count, pe_update_user_id, pe_update_dt) ';
			$queryStr .=  'VALUES ';
			$queryStr .=  '(?, ?, ?, ?, ?)';
			$this->execStatement($queryStr, array($pId, $lang, $stockCount, $userId, $now));
		}
		
		// 価格の更新
		for ($i = 0; $i < count($prices); $i++){
			$price = $prices[$i];
			$ret = $this->updatePrice($pId, $lang, $price[0], $price[1], $price[2], $price[3], $price[4], $userId, $now);
			if (!$ret){
				if ($startTran) $this->endTransaction();
				return false;
			}
		}
		// 画像の更新
		for ($i = 0; $i < count($images); $i++){
			$image = $images[$i];
			$ret = $this->updateImage(2/* 商品画像 */, $pId, $lang, $image[0], $image[1], $image[2], $userId, $now);
			if (!$ret){
				if ($startTran) $this->endTransaction();
				return false;
			}
		}
		// 商品ステータスの更新
		// ***** updateProductStatus()は、$statusesの値が存在していることが前提なので注意 *****
		for ($i = 0; $i < count($statuses); $i++){
			$status = $statuses[$i];
			$ret = $this->updateProductStatus($pId, $lang, $status[0], $status[1], $userId, $now);
			if (!$ret){
				if ($startTran) $this->endTransaction();
				return false;
			}
		}
		// 商品カテゴリーの更新
		for ($i = 0; $i < count($categories); $i++){
			$ret = $this->updateProductCategory($newSerial, $i, $categories[$i]);
			if (!$ret){
				if ($startTran) $this->endTransaction();
				return false;
			}
		}
		
		// トランザクション確定
		if ($startTran) $ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * 商品の削除
	 *
	 * @param array   $serial		シリアルNo
	 * @return						true=成功、false=失敗
	 */
	function delProduct($serial)
	{
		// 引数のエラーチェック
		if (!is_array($serial)) return false;
		if (count($serial) <= 0) return true;
		
		$userId = $this->gEnv->getCurrentUserId();	// 現在のユーザ
		$now = date("Y/m/d H:i:s");	// 現在日時
				
		// トランザクション開始
		$this->startTransaction();

		// 指定のシリアルNoのレコードが削除状態でないかチェック
		for ($i = 0; $i < count($serial); $i++){
			$queryStr  = 'SELECT * FROM product ';
			$queryStr .=   'WHERE pt_deleted = false ';		// 未削除
			$queryStr .=     'AND pt_serial = ? ';
			$ret = $this->isRecordExists($queryStr, array($serial[$i]));
			// 存在しない場合は、既に削除されたとして終了
			if (!$ret){
				$this->endTransaction();
				return false;
			}
		}
		for ($i = 0; $i < count($serial); $i++){
			// 商品IDを取得
			$queryStr  = 'SELECT * FROM product ';
			$queryStr .=   'WHERE pt_deleted = false ';		// 未削除
			$queryStr .=     'AND pt_serial = ? ';
			$ret = $this->selectRecord($queryStr, array($serial[$i]), $row);
			if (!$ret) break;		// 存在しない場合は終了
			$id = $row['pt_id'];
			$langId = $row['pt_language_id'];
		
			// レコードを削除
			$queryStr  = 'UPDATE product ';
			$queryStr .=   'SET pt_deleted = true, ';	// 削除
			$queryStr .=     'pt_update_user_id = ?, ';
			$queryStr .=     'pt_update_dt = ? ';
			$queryStr .=   'WHERE pt_serial = ?';
			$this->execStatement($queryStr, array($userId, $now, $serial[$i]));
		
			// 価格レコードを削除
			$queryStr  = 'UPDATE product_price ';
			$queryStr .=   'SET pp_deleted = true, ';	// 削除
			$queryStr .=     'pp_update_user_id = ?, ';
			$queryStr .=     'pp_update_dt = ? ';
			$queryStr .=   'WHERE pp_deleted = false ';
			$queryStr .=     'AND pp_product_id = ? ';
			$queryStr .=     'AND pp_language_id = ? ';
			$this->execStatement($queryStr, array($userId, $now, $id, $langId));

			// 画像レコードを削除
			$queryStr  = 'UPDATE product_image ';
			$queryStr .=   'SET im_deleted = true, ';	// 削除
			$queryStr .=     'im_update_user_id = ?, ';
			$queryStr .=     'im_update_dt = ? ';
			$queryStr .=   'WHERE im_deleted = false ';
			$queryStr .=     'AND im_type = 2 ';		// 対象は商品
			$queryStr .=     'AND im_id = ? ';
			$queryStr .=     'AND im_language_id = ? ';
			$this->execStatement($queryStr, array($userId, $now, $id, $langId));
		
			// 商品ステータスを削除
			$queryStr  = 'UPDATE product_status ';
			$queryStr .=   'SET ps_deleted = true, ';	// 削除
			$queryStr .=     'ps_update_user_id = ?, ';
			$queryStr .=     'ps_update_dt = ? ';
			$queryStr .=   'WHERE ps_deleted = false ';
			$queryStr .=     'AND ps_id = ? ';
			$queryStr .=     'AND ps_language_id = ? ';
			$this->execStatement($queryStr, array($userId, $now, $id, $langId));
		}
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * 商品IDで削除
	 *
	 * @param int $serial			シリアルNo
	 * @return						true=成功、false=失敗
	 */
	function delProductById($serial)
	{
		$userId = $this->gEnv->getCurrentUserId();	// 現在のユーザ
		$now = date("Y/m/d H:i:s");	// 現在日時
				
		// トランザクション開始
		$this->startTransaction();
		
		// 商品IDを取得
		$queryStr  = 'SELECT * FROM product ';
		$queryStr .=   'WHERE pt_deleted = false ';		// 未削除
		$queryStr .=     'AND pt_serial = ? ';
		$ret = $this->selectRecord($queryStr, array($serial), $row);
		if (!$ret){		// 存在しない場合は終了
			$this->endTransaction();
			return false;
		}
		$id = $row['pt_id'];
		
		// レコードを削除
		$queryStr  = 'UPDATE product ';
		$queryStr .=   'SET pt_deleted = true, ';	// 削除
		$queryStr .=     'pt_update_user_id = ?, ';
		$queryStr .=     'pt_update_dt = now() ';
		$queryStr .=   'WHERE pt_id = ?';
		$this->execStatement($queryStr, array($userId, $id));
		
		// 価格レコードを削除
		$queryStr  = 'UPDATE product_price ';
		$queryStr .=   'SET pp_deleted = true, ';	// 削除
		$queryStr .=     'pp_update_user_id = ?, ';
		$queryStr .=     'pp_update_dt = ? ';
		$queryStr .=   'WHERE pp_deleted = false ';
		$queryStr .=     'AND pp_product_id = ? ';
		$this->execStatement($queryStr, array($userId, $now, $row['pt_id']));
		
		// 画像レコードを削除
		$queryStr  = 'UPDATE product_image ';
		$queryStr .=   'SET im_deleted = true, ';	// 削除
		$queryStr .=     'im_update_user_id = ?, ';
		$queryStr .=     'im_update_dt = ? ';
		$queryStr .=   'WHERE im_deleted = false ';
		$queryStr .=     'AND im_id = ? ';
		$this->execStatement($queryStr, array($userId, $now, $row['pt_id']));
		
		// 商品スタータスレコードを削除
		$queryStr  = 'UPDATE product_status ';
		$queryStr .=   'SET ps_deleted = true, ';	// 削除
		$queryStr .=     'ps_update_user_id = ?, ';
		$queryStr .=     'ps_update_dt = ? ';
		$queryStr .=   'WHERE ps_deleted = false ';
		$queryStr .=     'AND ps_id = ? ';
		$this->execStatement($queryStr, array($userId, $now, $row['pt_id']));
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * 次の商品IDを取得
	 *
	 * @return int							商品ID
	 */
	function getNextProductId()
	{
		// IDを決定する
		$queryStr = 'SELECT MAX(pt_id) AS mid FROM product ';
		$ret = $this->selectRecord($queryStr, array(), $row);
		if ($ret){
			$pId = $row['mid'] + 1;
		} else {
			$pId = 1;
		}
		return $pId;
	}
	/**
	 * 商品カテゴリー一覧を取得
	 *
	 * @param string	$lang				言語
	 * @param array		$rows				取得データ
	 * @return bool							true=取得、false=取得せず
	 */
	function getAllCategory($lang, &$rows)
	{
		$queryStr = 'SELECT * FROM product_category LEFT JOIN _login_user ON pc_create_user_id = lu_id AND lu_deleted = false ';
		$queryStr .=  'WHERE pc_language_id = ? ';
		$queryStr .=    'AND pc_deleted = false ';		// 削除されていない
		$queryStr .=  'ORDER BY pc_id';
		$retValue = $this->selectRecords($queryStr, array($lang), $rows);
		return $retValue;
	}
	/**
	 * 商品カテゴリー一覧を取得
	 *
	 * @param string	$lang				言語
	 * @param array		$rows				取得データ
	 * @param function	$callback			コールバック関数
	 * @return なし
	 */
	function getAllCategoryByLoop($lang, $callback)
	{
		$queryStr = 'SELECT * FROM product_category LEFT JOIN _login_user ON pc_create_user_id = lu_id AND lu_deleted = false ';
		$queryStr .=  'WHERE pc_language_id = ? ';
		$queryStr .=    'AND pc_deleted = false ';		// 削除されていない
		$queryStr .=  'ORDER BY pc_id';
		$this->selectLoop($queryStr, array($lang), $callback, null);
	}
	/**
	 * 商品カテゴリーをカテゴリーIDで取得
	 *
	 * @param int		$id					カテゴリーID
	 * @param string	$langId				言語ID
	 * @param array     $row				レコード
	 * @return bool							取得 = true, 取得なし= false
	 */
	function getCategoryByCategoryId($id, $langId, &$row)
	{
		$queryStr  = 'SELECT * FROM product_category LEFT JOIN _login_user ON pc_create_user_id = lu_id AND lu_deleted = false ';
		$queryStr .=   'WHERE pc_deleted = false ';	// 削除されていない
		$queryStr .=   'AND pc_id = ? ';
		$queryStr .=   'AND pc_language_id = ? ';
		$ret = $this->selectRecord($queryStr, array($id, $langId), $row);
		return $ret;
	}
	/**
	 * 指定したカテゴリーが親であるカテゴリーを取得
	 *
	 * @param int		$id			親商品カテゴリーID(0はトップレベル)
	 * @param string	$langId				言語ID
	 * @param array		$rows		取得した行データ
	 * @return int					取得した行数
	 */
	function getChildCategoryWithRows($id, $lang, &$rows)
	{
		$retCount = 0;
		$queryStr = 'SELECT pc_id,pc_name FROM product_category ';
		$queryStr .=  'WHERE pc_deleted = false ';	// 削除されていない
		$queryStr .=    'AND pc_parent_id = ? ';
		$queryStr .=    'AND pc_language_id = ? ';
		$queryStr .=  'ORDER BY pc_sort_order';
		$ret = $this->selectRecords($queryStr, array($id, $lang), $rows);
		if ($ret){
			$retCount = count($rows);
		}
		return $retCount;
	}
	/**
	 * 商品価格の更新
	 *
	 * @param int        $productId		商品ID(商品タイプに応じて参照するテーブルが異なる)
	 * @param string     $langId		言語ID
	 * @param string     $priceType		価格の種別ID(price_typeテーブル)
	 * @param string     $currency		通貨種別
	 * @param float      $price			単価(税抜)。nullの場合はレコードを削除。
	 * @param timestamp  $startDt		使用開始
	 * @param timestamp  $endDt			使用終了
	 * @param int        $userId		更新者ユーザID
	 * @param timestamp  $now			現時日時
	 * @return bool		 true = 成功、false = 失敗
	 */
	function updatePrice($productId, $langId, $priceType, $currency, $price, $startDt, $endDt, $userId, $now)
	{
		// 指定のレコードの履歴インデックス取得
		$historyIndex = 0;		// 履歴番号
		$queryStr  = 'SELECT * FROM product_price ';
		$queryStr .=   'WHERE pp_product_id = ? ';
		$queryStr .=     'AND pp_language_id = ? ';
		$queryStr .=     'AND pp_price_type_id = ? ';
		$queryStr .=  'ORDER BY pp_history_index desc ';
		$queryStr .=    'LIMIT 1';
		$ret = $this->selectRecord($queryStr, array($productId, $langId, $priceType), $row);
		if ($ret){
			$historyIndex = $row['pp_history_index'] + 1;
		
			// レコードが削除されていない場合は削除
			if (!$row['pp_deleted']){
				// 古いレコードを削除
				$queryStr  = 'UPDATE product_price ';
				$queryStr .=   'SET pp_deleted = true, ';	// 削除
				$queryStr .=     'pp_update_user_id = ?, ';
				$queryStr .=     'pp_update_dt = ? ';
				$queryStr .=   'WHERE pp_serial = ?';
				$ret = $this->execStatement($queryStr, array($userId, $now, $row['pp_serial']));
				if (!$ret) return false;
			}
		}
		
		// 価格がnullの場合はレコードを追加しない
		if (is_null($price)) return true;
		
		// 新規レコード追加
		$queryStr = 'INSERT INTO product_price ';
		$queryStr .=  '(';
		$queryStr .=  'pp_product_id, ';
		$queryStr .=  'pp_language_id, ';
		$queryStr .=  'pp_price_type_id, ';
		$queryStr .=  'pp_history_index, ';
		$queryStr .=  'pp_currency_id, ';
		$queryStr .=  'pp_price, ';
		$queryStr .=  'pp_active_start_dt, ';
		$queryStr .=  'pp_active_end_dt, ';
		$queryStr .=  'pp_create_user_id, ';
		$queryStr .=  'pp_create_dt) ';
		$queryStr .=  'VALUES ';
		$queryStr .=  '(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
		$ret =$this->execStatement($queryStr, array($productId, $langId, $priceType, $historyIndex, $currency, $price, $startDt, $endDt, $userId, $now));
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
	/**
	 * 画像の更新
	 *
	 * @param int        $imageType		画像タイプ(1=商品カテゴリ、2=商品)
	 * @param int        $productId		商品ID(商品タイプに応じて参照するテーブルが異なる)
	 * @param string     $langId		言語ID
	 * @param string     $sizeId		画像サイズID
	 * @param string     $name			画像名
	 * @param string     $url			画像URL
	 * @param int        $userId		更新者ユーザID
	 * @param timestamp  $now			現時日時
	 * @return bool		 true = 成功、false = 失敗
	 */
	function updateImage($imageType, $productId, $langId, $sizeId, $name, $url, $userId, $now)
	{
		// 指定のレコードの履歴インデックス取得
		$historyIndex = 0;		// 履歴番号
		$queryStr  = 'SELECT * FROM product_image ';
		$queryStr .=   'WHERE im_type = ? ';
		$queryStr .=     'AND im_id = ? ';
		$queryStr .=     'AND im_language_id = ? ';
		$queryStr .=     'AND im_size_id = ? ';
		$queryStr .=  'ORDER BY im_history_index desc ';
		$queryStr .=    'LIMIT 1';
		$ret = $this->selectRecord($queryStr, array($imageType, $productId, $langId, $sizeId), $row);
		if ($ret){
			$historyIndex = $row['im_history_index'] + 1;
		
			// レコードが削除されていない場合は削除
			if (!$row['im_deleted']){
				// 古いレコードを削除
				$queryStr  = 'UPDATE product_image ';
				$queryStr .=   'SET im_deleted = true, ';	// 削除
				$queryStr .=     'im_update_user_id = ?, ';
				$queryStr .=     'im_update_dt = ? ';
				$queryStr .=   'WHERE im_serial = ?';
				$ret = $this->execStatement($queryStr, array($userId, $now, $row['im_serial']));
				if (!$ret) return false;
			}
		}
		
		// 新規レコード追加
		$queryStr = 'INSERT INTO product_image ';
		$queryStr .=  '(';
		$queryStr .=  'im_type, ';
		$queryStr .=  'im_id, ';
		$queryStr .=  'im_language_id, ';
		$queryStr .=  'im_size_id, ';
		$queryStr .=  'im_history_index, ';
		$queryStr .=  'im_name, ';
		$queryStr .=  'im_url, ';
		$queryStr .=  'im_create_user_id, ';
		$queryStr .=  'im_create_dt) ';
		$queryStr .=  'VALUES ';
		$queryStr .=  '(?, ?, ?, ?, ?, ?, ?, ?, ?)';
		$ret =$this->execStatement($queryStr, array($imageType, $productId, $langId, $sizeId, $historyIndex, $name, $url, $userId, $now));
		return $ret;
	}
	/**
	 * 商品ステータスの更新
	 *
	 * @param int        $productId		商品ID
	 * @param string     $langId		言語ID
	 * @param string     $type			ステータスタイプ
	 * @param string     $value			値
	 * @param int        $userId		更新者ユーザID
	 * @param timestamp  $now			現時日時
	 * @return bool		 true = 成功、false = 失敗
	 */
	function updateProductStatus($productId, $langId, $type, $value, $userId, $now)
	{
		// 指定のレコードの履歴インデックス取得
		$historyIndex = 0;		// 履歴番号
		$queryStr  = 'SELECT * FROM product_status ';
		$queryStr .=   'WHERE ps_id = ? ';
		$queryStr .=     'AND ps_language_id = ? ';
		$queryStr .=     'AND ps_type = ? ';
		$queryStr .=  'ORDER BY ps_history_index desc ';
		$queryStr .=    'LIMIT 1';
		$ret = $this->selectRecord($queryStr, array($productId, $langId, $type), $row);
		if ($ret){
			$historyIndex = $row['ps_history_index'] + 1;
		
			// レコードが削除されていない場合は削除
			if (!$row['ps_deleted']){
				// 古いレコードを削除
				$queryStr  = 'UPDATE product_status ';
				$queryStr .=   'SET ps_deleted = true, ';	// 削除
				$queryStr .=     'ps_update_user_id = ?, ';
				$queryStr .=     'ps_update_dt = ? ';
				$queryStr .=   'WHERE ps_serial = ?';
				$ret = $this->execStatement($queryStr, array($userId, $now, $row['ps_serial']));
				if (!$ret) return false;
			}
		}
		
		// 新規レコード追加
		$queryStr = 'INSERT INTO product_status ';
		$queryStr .=  '(';
		$queryStr .=  'ps_id, ';
		$queryStr .=  'ps_language_id, ';
		$queryStr .=  'ps_type, ';
		$queryStr .=  'ps_history_index, ';
		$queryStr .=  'ps_value, ';
		$queryStr .=  'ps_create_user_id, ';
		$queryStr .=  'ps_create_dt) ';
		$queryStr .=  'VALUES ';
		$queryStr .=  '(?, ?, ?, ?, ?, ?, ?)';
		$ret =$this->execStatement($queryStr, array($productId, $langId, $type, $historyIndex, $value, $userId, $now));
		return $ret;
	}
	/**
	 * 商品カテゴリーの更新
	 *
	 * @param int        $productSerial		商品シリアル番号
	 * @param int        $index			インデックス番号
	 * @param int        $categoryId	カテゴリーID
	 * @return bool		 true = 成功、false = 失敗
	 */
	function updateProductCategory($productSerial, $index, $categoryId)
	{
		// 新規レコード追加
		$queryStr = 'INSERT INTO product_with_category ';
		$queryStr .=  '(';
		$queryStr .=  'pw_product_serial, ';
		$queryStr .=  'pw_index, ';
		$queryStr .=  'pw_category_id) ';
		$queryStr .=  'VALUES ';
		$queryStr .=  '(?, ?, ?)';
		$ret =$this->execStatement($queryStr, array($productSerial, $index, $categoryId));
		return $ret;
	}
	/**
	 * 商品コードが存在するかチェック
	 *
	 * @param string $code	商品コード
	 * @return				true=存在する、false=存在しない
	 */
	function isExistsProductCode($code)
	{
		$queryStr  = 'SELECT * FROM product ';
		$queryStr .=   'WHERE pt_deleted = false ';		// 未削除
		$queryStr .=     'AND pt_code = ? ';
		return $this->isRecordExists($queryStr, array($code));
	}
	/**
	 * 商品を検索(管理用)
	 *
	 * @param array		$keywords			検索キーワード
	 * @param string,array $categoryId		商品カテゴリー
	 * @param string	$lang				言語
	 * @param int		$limit				取得する項目数
	 * @param int		$offset				取得する先頭位置(0～)
	 * @param string	$sortKey			ソートキー(index=表示順,stock=在庫数,id=商品ID,date=更新日時,name=商品名,code=商品コード,price=価格,visible=公開状態)
	 * @param int		$sortDirection		取得順(0=降順,1=昇順)
	 * @param function	$callback			コールバック関数
	 * @return 			なし
	 */
	function searchProduct($keywords, $categoryId, $lang, $limit, $offset, $sortKey, $sortDirection, $callback)
	{
		$params = array();
		if (empty($categoryId)){
			$queryStr = 'SELECT distinct(pt_serial) FROM product ';
		} else {
			$queryStr = 'SELECT distinct(pt_serial) FROM product RIGHT JOIN product_with_category ON pt_serial = pw_product_serial ';
		}
		$queryStr .=  'WHERE pt_language_id = ? '; $params[] = $lang;
		$queryStr .=    'AND pt_deleted = false ';		// 削除されていない
//		$queryStr .=    'AND pt_visible = true ';		// 表示可能な商品
		
		// 商品カテゴリー
		if (!empty($categoryId)){
			if (!is_array($categoryId)) $categoryId = array($categoryId);
			$queryStr .=    'AND pw_category_id in (' . implode(",", $categoryId) . ') ';
		}

		// 商品名、商品コード、説明を検索
		if (!empty($keywords)){
			for ($i = 0; $i < count($keywords); $i++){
				$keyword = addslashes($keywords[$i]);// 「'"\」文字をエスケープ
				$queryStr .=    'AND (pt_name LIKE \'%' . $keyword . '%\' ';
				$queryStr .=    'OR pt_code LIKE \'%' . $keyword . '%\' ';
				$queryStr .=    'OR pt_description LIKE \'%' . $keyword . '%\' ';
				$queryStr .=    'OR pt_description_short LIKE \'%' . $keyword . '%\') ';
			}
		}
		
		// シリアル番号を取得
		$serialArray = array();
		$ret = $this->selectRecords($queryStr, $params, $serialRows);
		if ($ret){
			for ($i = 0; $i < count($serialRows); $i++){
				$serialArray[] = $serialRows[$i]['pt_serial'];
			}
		}
		$serialStr = implode(',', $serialArray);
		if (empty($serialStr)) $serialStr = '0';		// ダミーを設定
		$queryStr  = 'SELECT * FROM product LEFT JOIN product_record ON pt_id = pe_product_id AND pt_language_id = pe_language_id ';
		$queryStr .=   'WHERE pt_serial in (' . $serialStr . ') ';

		// ソート順
		switch ($sortKey){
			case 'index':	// 表示順
			default:
				$orderKey = 'pt_sort_order ';
				break;
			case 'stock':		// 在庫数
				$orderKey = 'pe_stock_count ';
				break;
			case 'id':		// 商品ID
				$orderKey = 'pt_id ';
				break;
			case 'date':		// 更新日時
				$orderKey = 'pt_create_dt ';
				break;
			case 'name':		// 商品名
				$orderKey = 'pt_name ';
				break;
			case 'code':		// 商品コード
				$orderKey = 'pt_code ';
				break;
//			case 'price':		// 価格
//				$orderKey = 'pt_code ';
//				break;
			case 'visible':		// 公開状態
				$orderKey = 'pt_visible ';
				break;
		}
		$ord = '';
		if (empty($sortDirection)) $ord = 'DESC ';
		$defaultOrder = '';
		if ($sortKey != 'visible') $defaultOrder = ', pt_visible DESC ';
		$queryStr .=  'ORDER BY ' . $orderKey . $ord . $defaultOrder . 'LIMIT ' . $limit . ' OFFSET ' . $offset;	// 画像アップロード日時順
//		$queryStr .=   'ORDER BY pt_visible DESC, pe_stock_count, pt_create_dt DESC limit ' . $limit . ' offset ' . $offset;		// 公開、在庫、最新順
		$this->selectLoop($queryStr, array(), $callback);
	}
	/**
	 * 商品検索数を取得(管理用)
	 *
	 * @param array		$keywords			検索キーワード
	 * @param string,array $categoryId		商品カテゴリー
	 * @param string	$lang				言語
	 * @return int							商品総数
	 */
	function searchProductCount($keywords, $categoryId, $lang)
	{
		$params = array();
		if (empty($categoryId)){
			$queryStr = 'SELECT distinct(pt_serial) FROM product ';
		} else {
			$queryStr = 'SELECT distinct(pt_serial) FROM product RIGHT JOIN product_with_category ON pt_serial = pw_product_serial ';
		}
		$queryStr .=  'WHERE pt_language_id = ? '; $params[] = $lang;
		$queryStr .=    'AND pt_deleted = false ';		// 削除されていない
//		$queryStr .=    'AND pt_visible = true ';		// 表示可能な商品

		// 商品カテゴリー
		if (!empty($categoryId)){
			if (!is_array($categoryId)) $categoryId = array($categoryId);
			$queryStr .=    'AND pw_category_id in (' . implode(",", $categoryId) . ') ';
		}
		
		// 商品名、商品コード、説明を検索
		if (!empty($keywords)){
			for ($i = 0; $i < count($keywords); $i++){
				$keyword = addslashes($keywords[$i]);// 「'"\」文字をエスケープ
				$queryStr .=    'AND (pt_name LIKE \'%' . $keyword . '%\' ';
				$queryStr .=    'OR pt_code LIKE \'%' . $keyword . '%\' ';
				$queryStr .=    'OR pt_description LIKE \'%' . $keyword . '%\' ';
				$queryStr .=    'OR pt_description_short LIKE \'%' . $keyword . '%\') ';
			}
		}
		return $this->selectRecordCount($queryStr, $params);
	}
	/**
	 * 商品記録を更新
	 *
	 * @param int	  $id			商品ID
	 * @param string  $lang			言語ID
	 * @param array	$updateParam	更新パラメータ
	 * @return bool					true = 成功、false = 失敗
	 */
	function updateProductRecord($id, $lang, $updateParam)
	{
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $this->gEnv->getCurrentUserId();	// 現在のユーザ
		
		// パラメータエラーチェック
		$keys = array_keys($updateParam);
		if (in_array('pe_serial', $keys)) return false;
				
		// 既存データ取得
		$queryStr = 'SELECT * FROM product_record ';
		$queryStr .=  'WHERE pe_product_id = ? ';
		$queryStr .=    'AND pe_language_id = ? ';
		$ret = $this->selectRecord($queryStr, array($id, $lang), $stockRow);
		if ($ret){	// データが存在するとき
			// ##### データを更新 #####
			// レコード更新
			$queryStr = 'UPDATE product_record ';
			$queryStr .=  'SET ';
			$values = array();
			for ($i = 0; $i < count($keys); $i++){
				$queryStr .= $keys[$i] . ' = ?, ';
				$values[] = $updateParam[$keys[$i]];
			}
			$queryStr .= 'pe_update_user_id = ?, '; $values[] = $userId;
			$queryStr .= 'pe_update_dt = ? '; $values[] = $now;
			$queryStr .=  'WHERE pe_serial = ? ';
			$values[] = $stockRow['pe_serial'];
			$ret =$this->execStatement($queryStr, $values);
		} else {
			// ##### データを新規追加 #####
			// 新規レコード追加
			$queryStr = 'INSERT INTO product_record ';
			$queryStr .=  '(';
		
			$valueStr = '(';
			$values = array();
			for ($i = 0; $i < count($keys); $i++){
				$queryStr .= $keys[$i] . ', ';
				$valueStr .= '?, ';
				$values[] = $updateParam[$keys[$i]];
			}
			$queryStr .= 'pe_product_id, pe_language_id, pe_update_user_id, pe_update_dt) ';
			$valueStr .= '?, ?, ?, ?) ';
			$values[] = $id;
			$values[] = $lang;
			$values[] = $userId;
			$values[] = $now;
			
			$queryStr .=  'VALUES ';
			$queryStr .=  $valueStr;
			$ret =$this->execStatement($queryStr, $values);
		}
		return $ret;
	}
	/**
	 * 最大表示順を取得
	 *
	 * @param string	$lang		言語
	 * @return int					最大表示順
	 */
	function getMaxIndex($lang)
	{
		$queryStr = 'SELECT MAX(pt_sort_order) as mi FROM product ';
		$queryStr .=  'WHERE pt_deleted = false ';
		$queryStr .=  'AND pt_language_id = ? ';
		$ret = $this->selectRecord($queryStr, array($lang), $row);
		if ($ret){
			$index = $row['mi'];
		} else {
			$index = 0;
		}
		return $index;
	}
	/**
	 * サムネールファイル名の更新
	 *
	 * @param string $id			製品ID
	 * @param string $langId		言語ID
	 * @param string $thumbFilename	サムネールファイル名
	 * @param string $thumbSrc		サムネール作成元画像ファイル(resourceディレクトリからの相対パス)
	 * @return bool					true = 成功、false = 失敗
	 */
	function updateThumbFilename($id, $langId, $thumbFilename, $thumbSrc)
	{
		$serial = $this->getProductSerialNoById($id, $langId);
		if (empty($serial)) return false;
		
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $this->gEnv->getCurrentUserId();	// 現在のユーザ
						
		// トランザクション開始
		$this->startTransaction();
		
		// 指定のシリアルNoのレコードが削除状態でないかチェック
		$queryStr  = 'SELECT * FROM product ';
		$queryStr .=   'WHERE pt_serial = ? ';
		$ret = $this->selectRecord($queryStr, array(intval($serial)), $row);
		if ($ret){		// 既に登録レコードがあるとき
			if ($row['pt_deleted']){		// レコードが削除されていれば終了
				$this->endTransaction();
				return false;
			}
		} else {		// 存在しない場合は終了
			$this->endTransaction();
			return false;
		}
		// 日付を更新
		$queryStr  = 'UPDATE product ';
		$queryStr .=   'SET pt_thumb_filename = ?, ';	// サムネールファイル名
		$queryStr .=     'pt_thumb_src = ?, ';			// サムネール作成元画像ファイル
		$queryStr .=     'pt_update_user_id = ?, ';
		$queryStr .=     'pt_update_dt = ? ';
		$queryStr .=   'WHERE pt_serial = ?';
		$this->execStatement($queryStr, array($thumbFilename, $thumbSrc, $userId, $now, intval($serial)));
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * 製品情報のシリアル番号を製品IDで取得
	 *
	 * @param string	$id					製品ID
	 * @param string	$langId				言語ID
	 * @return int							シリアル番号、取得できないときは0を返す
	 */
	function getProductSerialNoById($id, $langId)
	{
		$serial = 0;
		$queryStr  = 'SELECT * FROM product ';
		$queryStr .=   'WHERE pt_deleted = false ';	// 削除されていない
		$queryStr .=   'AND pt_history_index >= 0 ';		// 正規(Regular)記事を対象
		$queryStr .=   'AND pt_id = ? ';
		$queryStr .=   'AND pt_language_id = ? ';
		$ret = $this->selectRecord($queryStr, array($id, $langId), $row);
		if ($ret) $serial = $row['pt_serial'];
		return $serial;
	}
}
?>
