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
 * @copyright  Copyright 2006-2008 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: product_randomDb.php 681 2008-06-04 02:35:50Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getDbPath() . '/baseDb.php');

class product_randomDb extends BaseDb
{
	/**
	 * 商品一覧を取得(シリアル番号のみ)
	 *
	 * @param string	$lang				言語
	 * @param int		$limit				取得する項目数
	 * @param int		$offset				取得する先頭位置(0～)
	 * @param array		$status				商品ステータス
	 * @param array     $rows				取得レコード
	 * @return bool							取得 = true, 取得なし= false
	 */
	function getProductSerial($lang, $limit, $offset, $status, &$rows)
	{
		$params = array();
		if (count($status) <= 0){		// 商品ステータスの絞込みがないとき
			$queryStr = 'SELECT pt_serial, ';
			$queryStr .=    'CASE WHEN pe_promote_count IS NULL THEN 0 ELSE pe_promote_count ';
			$queryStr .=    'END AS promote_count ';		// 表示回数
			$queryStr .=  'FROM product LEFT JOIN product_record ON pt_id = pe_product_id AND pt_language_id = pe_language_id ';
			$queryStr .=  'WHERE pt_language_id = ? '; $params[] = $lang;
			$queryStr .=    'AND pt_deleted = false ';		// 削除されていない
			$queryStr .=    'AND pt_visible = true ';		// 表示可能な商品
			$queryStr .=  'ORDER BY promote_count limit ' . $limit . ' offset ' . $offset;		// 表示回数が少ないものを優先的に取得
			$ret = $this->selectRecords($queryStr, $params, $rows);
		} else {
			// 該当するステータスの商品を取得
			$queryStr = 'SELECT distinct(pt_serial) FROM product RIGHT JOIN product_status ON pt_id = ps_id AND pt_language_id = ps_language_id AND ps_deleted = false AND pt_deleted = false ';
			$queryStr .=  'WHERE pt_language_id = ? '; $params[] = $lang;
			$queryStr .=    'AND pt_deleted = false ';		// 削除されていない
			$queryStr .=    'AND ps_value <> \'0\' ';// ステータスの値が設定されている
			$statusStr = '';
			for ($i = 0; $i < count($status); $i++){
				$statusStr .= '\'' . $status[$i] . '\',';
			}
			$statusStr = trim($statusStr, ',');
			$queryStr .=    'AND ps_type in (' . $statusStr . ') ';			// 商品ステータス
			$ret = $this->selectRecords($queryStr, $params, $rows);
			$serialArray = array();
			if ($ret){
				for ($i = 0; $i < count($rows); $i++){
					$serialArray[] = $rows[$i]['pt_serial'];
				}
			}
			$serialStr = implode(',', $serialArray);
			if (empty($serialStr)) $serialStr = '0';		// 0レコードのときはダミー値を設定
			
			$params = array();
			$queryStr = 'SELECT pt_serial, ';
			$queryStr .=    'CASE WHEN pe_promote_count IS NULL THEN 0 ELSE pe_promote_count ';
			$queryStr .=    'END AS promote_count ';		// 表示回数
			$queryStr .=  'FROM product LEFT JOIN product_record ON pt_id = pe_product_id AND pt_language_id = pe_language_id ';
			$queryStr .=  'WHERE pt_language_id = ? '; $params[] = $lang;
			$queryStr .=    'AND pt_deleted = false ';		// 削除されていない
			$queryStr .=    'AND pt_visible = true ';		// 表示可能な商品
			$queryStr .=    'AND pt_serial in (' . $serialStr . ') ';			// 商品シリアル番号
			$queryStr .=  'ORDER BY promote_count limit ' . $limit . ' offset ' . $offset;		// 表示回数が少ないものを優先的に取得
			$ret = $this->selectRecords($queryStr, $params, $rows);
		}
		return $ret;
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
		$queryStr = 'SELECT * ';
		$queryStr .=  'FROM product LEFT JOIN product_record ON pt_id = pe_product_id AND pt_language_id = pe_language_id ';
		$queryStr .=  'WHERE pt_serial = ? ';
		$ret = $this->selectRecord($queryStr, array($serial), $row);
		if ($ret){
			$queryStr  = 'SELECT * FROM product_price ';
			$queryStr .=   'WHERE pp_deleted = false ';// 削除されていない
			$queryStr .=     'AND pp_product_class = ? ';		// 商品クラス
			$queryStr .=     'AND pp_product_id = ? ';
			$this->selectRecords($queryStr, array(''/*デフォルト商品クラス*/, $row['pt_id']), $row2);
			
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
	 * 商品記録を更新
	 *
	 * @param int	  $id			商品ID
	 * @param string  $lang			言語ID
	 * @param array	$updateParam	更新パラメータ
	 * @return bool					true = 成功、false = 失敗
	 */
	function updateProductRecord($id, $lang, $updateParam)
	{
		global $gEnvManager;
		
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $gEnvManager->getCurrentUserId();	// 現在のユーザ
		
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
	 * 商品ステータス一覧を取得
	 *
	 * @param string	$lang				言語
	 * @param array     $rows				取得レコード
	 * @return bool							取得 = true, 取得なし= false
	 */
	function getAllProductStatus($lang, &$rows)
	{
		$queryStr = 'SELECT * FROM product_status_type ';
		$queryStr .=  'WHERE pa_language_id = ? ';
		$queryStr .=  'ORDER BY pa_priority';
		$ret = $this->selectRecords($queryStr, array($lang), $rows);
		return $ret;
	}
}
?>
