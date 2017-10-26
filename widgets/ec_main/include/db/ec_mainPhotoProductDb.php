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
 * @copyright  Copyright 2006-2015 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getDbPath() . '/baseDb.php');

class ec_mainPhotoProductDb extends BaseDb
{
	const PRODUCT_CLASS_PHOTO	= 'photo';		// 商品クラス

	/**
	 * すべての言語を取得
	 *
	 * @param function	$callback			コールバック関数
	 * @return			true=取得、false=取得せず
	 */
	function getAllLang($callback)
	{
		$queryStr = 'SELECT * FROM _language ORDER BY ln_priority';
		$this->selectLoop($queryStr, array(), $callback);
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
		$this->selectLoop($queryStr, array($lang), $callback);
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
		$this->selectLoop($queryStr, array($lang), $callback);
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
		$this->selectLoop($queryStr, array(), $callback);
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
		$queryStr = 'SELECT ln_id, ln_name, ln_name_en FROM photo_product LEFT JOIN _language ON hp_language_id = ln_id ';
		$queryStr .=  'WHERE hp_deleted = false ';	// 削除されていない
		$queryStr .=    'AND hp_id = ? ';
		$queryStr .=  'ORDER BY hp_id, ln_priority';
		$retValue = $this->selectRecords($queryStr, array($id), $rows);
		return $retValue;
	}
	/**
	 * すべての商品一覧を取得
	 *
	 * @param string	$lang				言語
	 * @param function	$callback			コールバック関数
	 * @return 			なし
	 */
	function getAllProductByLang($lang, $callback)
	{
		$queryStr = 'SELECT * FROM photo_product LEFT JOIN _login_user ON hp_create_user_id = lu_id AND lu_deleted = false ';
		$queryStr .=  'WHERE hp_deleted = false ';// 削除されていない
		$queryStr .=    'AND hp_language_id = ? ';
		$queryStr .=  'ORDER BY hp_sort_order';
		$this->selectLoop($queryStr, array($lang), $callback);
	}
	/**
	 * すべての商品一覧を取得
	 *
	 * @param function	$callback			コールバック関数
	 * @return 			なし
	 */
	function getAllProduct($callback)
	{
		$queryStr = 'SELECT * FROM photo_product LEFT JOIN unit_type ON hp_unit_type_id = ut_id ';
		$queryStr .=  'LEFT JOIN tax_type ON hp_tax_type_id = tt_id ';
		$queryStr .=  'WHERE hp_deleted = false ';// 削除されていない
		$queryStr .=  'ORDER BY hp_id';
		$this->selectLoop($queryStr, array(), $callback);
	}
	/**
	 * 商品をシリアル番号で取得
	 *
	 * @param int		$serial				シリアル番号
	 * @param array     $row				レコード
	 * @param array     $row2				商品価格
	 * @param array     $row3				商品画像
	 * @return bool							取得 = true, 取得なし= false
	 */
	function getProductBySerial($serial, &$row, &$row2, &$row3)
	{
		$queryStr  = 'SELECT * FROM photo_product ';
		$queryStr .=   'LEFT JOIN _login_user ON hp_create_user_id = lu_id AND lu_deleted = false ';
		$queryStr .=   'WHERE hp_serial = ? ';
		$ret = $this->selectRecord($queryStr, array($serial), $row);
		if ($ret){
			$queryStr  = 'SELECT * FROM product_price ';
			$queryStr .=   'WHERE pp_deleted = false ';// 削除されていない
			$queryStr .=     'AND pp_product_class = ? ';		// 商品クラス
			$queryStr .=     'AND pp_product_id = ? ';			// 商品ID(画像ID)
			$queryStr .=     'AND pp_product_type_id = ? ';		// 商品タイプ
			$this->selectRecords($queryStr, array(self::PRODUCT_CLASS_PHOTO/*フォト画像クラス*/, 0/*全画像対象*/, $row['hp_id']), $row2);
			
			$queryStr  = 'SELECT * FROM product_image ';
			$queryStr .=   'WHERE im_deleted = false ';// 削除されていない
			$queryStr .=     'AND im_product_class = ? ';		// 商品クラス
			$queryStr .=     'AND im_type = 0 ';		// デフォルト画像タイプ
			$queryStr .=     'AND im_id = ? ';
			$queryStr .=     'AND im_language_id = ? ';
			$this->selectRecords($queryStr, array(self::PRODUCT_CLASS_PHOTO/*フォト画像クラス*/, $row['hp_id'], $row['hp_language_id']), $row3);
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
	 * @param array   $prices		価格情報の配列
	 * @param array   $images		画像の配列
	 * @param int     $newSerial	新規シリアル番号
	 * @return bool					true = 成功、false = 失敗
	 */
	function updateProduct($serial, $id, $lang, $otherParams, $prices, $images, &$newSerial)
	{
		$userId = $this->gEnv->getCurrentUserId();	// 現在のユーザ
		$now = date("Y/m/d H:i:s");	// 現在日時
		$newSerial = 0;
		$startTran = false;			// この関数でトランザクションを開始したかどうか
		$updateFields = array();	// 更新するフィールド名
		$updateFields[] = 'hp_name';			// 商品名
		$updateFields[] = 'hp_code';			// 商品コード
		$updateFields[] = 'hp_sort_order';			// 表示順
		$updateFields[] = 'hp_visible';				// 表示するかどうか
		$updateFields[] = 'hp_product_type';		// 商品種別(1=単品商品、2=セット商品、3=オプション商品)
		$updateFields[] = 'hp_unit_type_id';		// 選択単位
		$updateFields[] = 'hp_unit_quantity';		// 数量
		$updateFields[] = 'hp_description';			// 説明
		$updateFields[] = 'hp_description_short';	// 簡易説明
		$updateFields[] = 'hp_search_keyword';		// 検索キーワード
		$updateFields[] = 'hp_site_url';			// 詳細情報URL
		$updateFields[] = 'hp_tax_type_id';			// 税種別
		$updateFields[] = 'hp_admin_note';		// 管理者用備考
		$updateFields[] = 'hp_deliv_type';		// 配送タイプ
		$updateFields[] = 'hp_deliv_fee';		// 配送単価
		$updateFields[] = 'hp_weight';			// 配送基準重量
						
		// トランザクション開始
		if (!$this->isInTransaction()){
			$this->startTransaction();
			$startTran = true;
		}
		
		if (empty($serial)){		// 新規追加のとき
			if ($id == 0){		// IDが0のときは、商品IDを新規取得
				// IDを決定する
				$queryStr = 'SELECT MAX(hp_id) AS mid FROM photo_product ';
				$ret = $this->selectRecord($queryStr, array(), $row);
				if ($ret){
					$pId = $row['mid'] + 1;
				} else {
					$pId = 1;
				}
			} else {
				$pId = $id;
			}
			// 前レコードの削除状態チェック
			$historyIndex = 0;
			$queryStr = 'SELECT * FROM photo_product ';
			$queryStr .=  'WHERE hp_id = ? ';
			$queryStr .=    'AND hp_language_id = ? ';
			$queryStr .=  'ORDER BY hp_history_index DESC ';
			$ret = $this->selectRecord($queryStr, array($pId, $lang), $row);
			if ($ret){
				if (!$row['hp_deleted']){		// レコード存在していれば終了
					if ($startTran) $this->endTransaction();
					return false;
				}
				$historyIndex = $row['hp_history_index'] + 1;
			}
		} else {		// 更新のとき
			// 指定のシリアルNoのレコードが削除状態でないかチェック
			$historyIndex = 0;		// 履歴番号
			$queryStr  = 'SELECT * FROM photo_product ';
			$queryStr .=   'WHERE hp_serial = ? ';
			$ret = $this->selectRecord($queryStr, array($serial), $row);
			if ($ret){		// 既に登録レコードがあるとき
				if ($row['hp_deleted']){		// レコードが削除されていれば終了
					if ($startTran) $this->endTransaction();
					return false;
				}
				$historyIndex = $row['hp_history_index'] + 1;
			} else {		// 存在しない場合は終了
				if ($startTran) $this->endTransaction();
				return false;
			}
			$pId = $row['hp_id'];
			$lang = $row['hp_language_id'];
		
			// 古いレコードを削除
			$queryStr  = 'UPDATE photo_product ';
			$queryStr .=   'SET hp_deleted = true, ';	// 削除
			$queryStr .=     'hp_update_user_id = ?, ';
			$queryStr .=     'hp_update_dt = ? ';
			$queryStr .=   'WHERE hp_serial = ?';
			$this->execStatement($queryStr, array($userId, $now, $serial));
		}
		// ##### データを追加 #####
		// キーを取得
		$keys = array_keys($otherParams);
		
		// クエリー作成
		$queryStr = 'INSERT INTO photo_product (hp_id, hp_language_id, hp_history_index, ';
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
		$queryStr .= 'hp_create_user_id, hp_create_dt) ';
		$valueStr .= '?, ?)';
		$values = array_merge($values, array($userId, $now));
		$queryStr .=  'VALUES ';
		$queryStr .=  $valueStr;
		$this->execStatement($queryStr, $values);

		// 新規のシリアル番号取得
		$queryStr = 'SELECT MAX(hp_serial) AS ns FROM photo_product ';
		$ret = $this->selectRecord($queryStr, array(), $row);
		if ($ret) $newSerial = $row['ns'];
		
		// 価格の更新
		for ($i = 0; $i < count($prices); $i++){
			$price = $prices[$i];
			$ret = $this->updatePrice($pId, $price[0], $price[1], $price[2], $price[3], $price[4], $userId, $now);
			if (!$ret){
				if ($startTran) $this->endTransaction();
				return false;
			}
		}
		// 画像の更新
		for ($i = 0; $i < count($images); $i++){
			$image = $images[$i];
			$ret = $this->updateImage($pId, $lang, $image[0], $image[1], $image[2], $userId, $now);
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
			$queryStr  = 'SELECT * FROM photo_product ';
			$queryStr .=   'WHERE hp_deleted = false ';		// 未削除
			$queryStr .=     'AND hp_serial = ? ';
			$ret = $this->isRecordExists($queryStr, array($serial[$i]));
			// 存在しない場合は、既に削除されたとして終了
			if (!$ret){
				$this->endTransaction();
				return false;
			}
		}
		
		for ($i = 0; $i < count($serial); $i++){
			// 商品IDを取得
			$queryStr  = 'SELECT * FROM photo_product ';
			$queryStr .=   'WHERE hp_deleted = false ';		// 未削除
			$queryStr .=     'AND hp_serial = ? ';
			$ret = $this->selectRecord($queryStr, array($serial[$i]), $row);
			if (!$ret) break;		// 存在しない場合は終了
			$id = $row['hp_id'];
			$langId = $row['hp_language_id'];
		
			// レコードを削除
			$queryStr  = 'UPDATE photo_product ';
			$queryStr .=   'SET hp_deleted = true, ';	// 削除
			$queryStr .=     'hp_update_user_id = ?, ';
			$queryStr .=     'hp_update_dt = ? ';
			$queryStr .=   'WHERE hp_serial = ?';
			$this->execStatement($queryStr, array($userId, $now, $serial[$i]));
		
			// 価格レコードを削除
			$queryStr  = 'UPDATE product_price ';
			$queryStr .=   'SET pp_deleted = true, ';	// 削除
			$queryStr .=     'pp_update_user_id = ?, ';
			$queryStr .=     'pp_update_dt = ? ';
			$queryStr .=   'WHERE pp_deleted = false ';
			$queryStr .=     'AND pp_product_class = ? ';		// 商品クラス
			$queryStr .=     'AND pp_product_id = ? ';
			$queryStr .=     'AND pp_product_type_id = ? ';		// 商品タイプ
			$this->execStatement($queryStr, array($userId, $now, self::PRODUCT_CLASS_PHOTO/*フォト画像クラス*/, 0/*全画像対象*/, $id));
			
			// 画像レコードを削除
			$queryStr  = 'UPDATE product_image ';
			$queryStr .=   'SET im_deleted = true, ';	// 削除
			$queryStr .=     'im_update_user_id = ?, ';
			$queryStr .=     'im_update_dt = ? ';
			$queryStr .=   'WHERE im_deleted = false ';
			$queryStr .=     'AND im_product_class = ? ';		// 商品クラス
			$queryStr .=     'AND im_type = 0 ';		// デフォルト画像タイプ
			$queryStr .=     'AND im_id = ? ';
			$queryStr .=     'AND im_language_id = ? ';
			$this->execStatement($queryStr, array($userId, $now, self::PRODUCT_CLASS_PHOTO/*フォト画像クラス*/, $id, $langId));
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
		$queryStr  = 'SELECT * FROM photo_product ';
		$queryStr .=   'WHERE hp_deleted = false ';		// 未削除
		$queryStr .=     'AND hp_serial = ? ';
		$ret = $this->selectRecord($queryStr, array($serial), $row);
		if (!$ret){		// 存在しない場合は終了
			$this->endTransaction();
			return false;
		}
		$id = $row['hp_id'];
		
		// レコードを削除
		$queryStr  = 'UPDATE photo_product ';
		$queryStr .=   'SET hp_deleted = true, ';	// 削除
		$queryStr .=     'hp_update_user_id = ?, ';
		$queryStr .=     'hp_update_dt = ? ';
		$queryStr .=   'WHERE hp_id = ?';
		$this->execStatement($queryStr, array($userId, $now, $id));
		
		// 価格レコードを削除
		$queryStr  = 'UPDATE product_price ';
		$queryStr .=   'SET pp_deleted = true, ';	// 削除
		$queryStr .=     'pp_update_user_id = ?, ';
		$queryStr .=     'pp_update_dt = ? ';
		$queryStr .=   'WHERE pp_deleted = false ';
		$queryStr .=     'AND pp_product_class = ? ';		// 商品クラス
		$queryStr .=     'AND pp_product_type_id = ? ';		// 商品タイプ
		$this->execStatement($queryStr, array($userId, $now, self::PRODUCT_CLASS_PHOTO/*フォト画像クラス*/, $row['hp_id']));
		
		// 画像レコードを削除
		$queryStr  = 'UPDATE product_image ';
		$queryStr .=   'SET im_deleted = true, ';	// 削除
		$queryStr .=     'im_update_user_id = ?, ';
		$queryStr .=     'im_update_dt = ? ';
		$queryStr .=   'WHERE im_deleted = false ';
		$queryStr .=     'AND im_product_class = ? ';		// 商品クラス
		$queryStr .=     'AND im_id = ? ';
		$this->execStatement($queryStr, array($userId, $now, self::PRODUCT_CLASS_PHOTO/*フォト画像クラス*/, $row['hp_id']));
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * 商品価格の更新
	 *
	 * @param int        $productId		商品ID(商品タイプに応じて参照するテーブルが異なる)
	 * @param string     $priceType		価格の種別ID(price_typeテーブル)
	 * @param string     $currency		通貨種別
	 * @param float      $price			単価(税抜)
	 * @param timestamp  $startDt		使用開始
	 * @param timestamp  $endDt			使用終了
	 * @param int        $userId		更新者ユーザID
	 * @param timestamp  $now			現時日時
	 * @return bool		 true = 成功、false = 失敗
	 */
	function updatePrice($productId, $priceType, $currency, $price, $startDt, $endDt, $userId, $now)
	{
		// 指定のレコードの履歴インデックス取得
		$historyIndex = 0;		// 履歴番号
		$queryStr  = 'SELECT * FROM product_price ';
		$queryStr .=   'WHERE pp_product_class = ? ';		// 商品クラス
		$queryStr .=     'AND pp_product_id = ? ';
		$queryStr .=     'AND pp_product_type_id = ? ';		// 商品タイプ
		$queryStr .=     'AND pp_price_type_id = ? ';
		$queryStr .=     'AND pp_currency_id = ? ';
		$queryStr .=  'ORDER BY pp_history_index DESC ';
		$ret = $this->selectRecord($queryStr, array(self::PRODUCT_CLASS_PHOTO/*フォト画像クラス*/, 0/*全画像対象*/, $productId, $priceType, $currency), $row);
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
		
		// 新規レコード追加
		$queryStr = 'INSERT INTO product_price ';
		$queryStr .=  '(';
		$queryStr .=  'pp_product_class, ';
		$queryStr .=  'pp_product_id, ';
		$queryStr .=  'pp_product_type_id, ';// 商品タイプ
		$queryStr .=  'pp_price_type_id, ';
		$queryStr .=  'pp_history_index, ';
		$queryStr .=  'pp_currency_id, ';
		$queryStr .=  'pp_price, ';
		$queryStr .=  'pp_active_start_dt, ';
		$queryStr .=  'pp_active_end_dt, ';
		$queryStr .=  'pp_create_user_id, ';
		$queryStr .=  'pp_create_dt) ';
		$queryStr .=  'VALUES ';
		$queryStr .=  '(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
		$ret =$this->execStatement($queryStr, array(self::PRODUCT_CLASS_PHOTO/*フォト画像クラス*/, 0/*全画像対象*/, $productId, 
													$priceType, $historyIndex, $currency, $price, $startDt, $endDt, $userId, $now));
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
	 * @param int        $productId		商品ID(商品タイプに応じて参照するテーブルが異なる)
	 * @param string     $langId		言語ID
	 * @param string     $sizeId		画像サイズID
	 * @param string     $name			画像名
	 * @param string     $url			画像URL
	 * @param int        $userId		更新者ユーザID
	 * @param timestamp  $now			現時日時
	 * @return bool		 true = 成功、false = 失敗
	 */
	function updateImage($productId, $langId, $sizeId, $name, $url, $userId, $now)
	{
		// 指定のレコードの履歴インデックス取得
		$historyIndex = 0;		// 履歴番号
		$queryStr  = 'SELECT * FROM product_image ';
		$queryStr .=   'WHERE im_product_class = ? ';		// 商品クラス
		$queryStr .=     'AND im_type = 0 ';				// デフォルト画像タイプ
		$queryStr .=     'AND im_id = ? ';
		$queryStr .=     'AND im_language_id = ? ';
		$queryStr .=     'AND im_size_id = ? ';
		$queryStr .=  'ORDER BY im_history_index DESC ';
		$ret = $this->selectRecord($queryStr, array(self::PRODUCT_CLASS_PHOTO/*フォト画像クラス*/, $productId, $langId, $sizeId), $row);
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
		$queryStr .=  'im_product_class, ';		// 商品クラス
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
		$queryStr .=  '(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
		$ret =$this->execStatement($queryStr, array(self::PRODUCT_CLASS_PHOTO/*フォト画像クラス*/, 0/*デフォルト画像タイプ*/, $productId, $langId, $sizeId, $historyIndex, 
														$name, $url, $userId, $now));
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
		$queryStr  = 'SELECT * FROM photo_product ';
		$queryStr .=   'WHERE hp_deleted = false ';		// 未削除
		$queryStr .=     'AND hp_code = ? ';
		return $this->isRecordExists($queryStr, array($code));
	}
	/**
	 * 商品を検索(管理用)
	 *
	 * @param array		$keywords			検索キーワード
	 * @param string	$lang				言語
	 * @param int		$limit				取得する項目数
	 * @param int		$offset				取得する先頭位置(0～)
	 * @param function	$callback			コールバック関数
	 * @return 			なし
	 */
	function searchProduct($keywords, $lang, $limit, $offset, $callback)
	{
		$params = array();
		$queryStr = 'SELECT DISTINCT(hp_serial) FROM photo_product ';
		$queryStr .=  'WHERE hp_language_id = ? '; $params[] = $lang;
		$queryStr .=    'AND hp_deleted = false ';		// 削除されていない
//		$queryStr .=    'AND hp_visible = true ';		// 表示可能な商品

		// 商品名、商品コード、説明を検索
		if (!empty($keywords)){
			for ($i = 0; $i < count($keywords); $i++){
				$keyword = addslashes($keywords[$i]);// 「'"\」文字をエスケープ
				$queryStr .=    'AND (hp_name LIKE \'%' . $keyword . '%\' ';
				$queryStr .=    'OR hp_code LIKE \'%' . $keyword . '%\' ';
				$queryStr .=    'OR hp_description LIKE \'%' . $keyword . '%\' ';
				$queryStr .=    'OR hp_description_short LIKE \'%' . $keyword . '%\') ';
			}
		}
		
		// シリアル番号を取得
		$serialArray = array();
		$ret = $this->selectRecords($queryStr, $params, $serialRows);
		if ($ret){
			for ($i = 0; $i < count($serialRows); $i++){
				$serialArray[] = $serialRows[$i]['hp_serial'];
			}
		}
		$serialStr = implode(',', $serialArray);
		if (empty($serialStr)) $serialStr = '0';		// ダミーを設定

		$queryStr  = 'SELECT * FROM photo_product ';
		$queryStr .=   'WHERE hp_serial in (' . $serialStr . ') ';
		$queryStr .=   'ORDER BY hp_sort_order LIMIT ' . $limit . ' OFFSET ' . $offset;
		$this->selectLoop($queryStr, array(), $callback);
	}
	/**
	 * 商品検索数を取得(管理用)
	 *
	 * @param array		$keywords			検索キーワード
	 * @param string	$lang				言語
	 * @return int							商品総数
	 */
	function searchProductCount($keywords, $lang)
	{
		$params = array();
		$queryStr = 'SELECT DISTINCT(hp_serial) FROM photo_product ';
		$queryStr .=  'WHERE hp_language_id = ? '; $params[] = $lang;
		$queryStr .=    'AND hp_deleted = false ';		// 削除されていない
//		$queryStr .=    'AND hp_visible = true ';		// 表示可能な商品
		
		// 商品名、商品コード、説明を検索
		if (!empty($keywords)){
			for ($i = 0; $i < count($keywords); $i++){
				$keyword = addslashes($keywords[$i]);// 「'"\」文字をエスケープ
				$queryStr .=    'AND (hp_name LIKE \'%' . $keyword . '%\' ';
				$queryStr .=    'OR hp_code LIKE \'%' . $keyword . '%\' ';
				$queryStr .=    'OR hp_description LIKE \'%' . $keyword . '%\' ';
				$queryStr .=    'OR hp_description_short LIKE \'%' . $keyword . '%\') ';
			}
		}
		return $this->selectRecordCount($queryStr, $params);
	}
	/**
	 * 最大表示順を取得
	 *
	 * @param string	$lang		言語
	 * @return int					最大表示順
	 */
	function getMaxIndex($lang)
	{
		$queryStr = 'SELECT MAX(hp_sort_order) AS mi FROM photo_product ';
		$queryStr .=  'WHERE hp_deleted = false ';
		$queryStr .=  'AND hp_language_id = ? ';
		$ret = $this->selectRecord($queryStr, array($lang), $row);
		if ($ret){
			$index = $row['mi'];
		} else {
			$index = 0;
		}
		return $index;
	}
}
?>
