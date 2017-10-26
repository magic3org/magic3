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
 * @version    SVN: $Id: ec_dispDb.php 5398 2012-11-22 02:29:37Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getDbPath() . '/baseDb.php');

class ec_dispDb extends BaseDb
{
	/**
	 * 商品一覧を取得
	 *
	 * @param string	$categoryId			商品カテゴリーID
	 * @param string	$lang				言語
	 * @param int		$limit				取得する項目数
	 * @param int		$offset				取得する先頭位置(0～)
	 * @param function	$callback			コールバック関数
	 * @return 			なし
	 */
	function getProductByCategoryId($categoryId, $lang, $limit, $offset, $callback)
	{
		$queryStr = 'SELECT distinct(pt_serial) ';
		$queryStr .=  'FROM product RIGHT JOIN product_with_category ON pt_serial = pw_product_serial ';
		$queryStr .=  'WHERE pt_deleted = false ';// 削除されていない
		$queryStr .=    'AND pt_visible = true ';		// 表示可能な商品	
		$queryStr .=    'AND pt_language_id = ? ';
		if (!empty($categoryId)) $queryStr .=    'AND pw_category_id in (' . $categoryId . ') ';
		
		$serialArray = array();
		$ret = $this->selectRecords($queryStr, array($lang), $serialRows);
		if ($ret){
			for ($i = 0; $i < count($serialRows); $i++){
				$serialArray[] = $serialRows[$i]['pt_serial'];
			}
		} else {		// データがない場合は終了
			return;
		}
		$serialStr = implode(',', $serialArray);
		if (empty($serialStr)) $serialStr = '0';	// 0レコードのときはダミー値を設定
		
		$queryStr  = 'SELECT * FROM product ';
		$queryStr .=   'WHERE pt_serial in (' . $serialStr . ') ';
		$queryStr .=   'ORDER BY pt_sort_order limit ' . $limit . ' offset ' . $offset;
		$this->selectLoop($queryStr, array(), $callback, null);
	}
	/**
	 * 商品総数を取得
	 *
	 * @param string	$categoryId			商品カテゴリーID
	 * @param string	$lang				言語
	 * @return int							商品総数
	 */
	function getProductCountByCategoryId($categoryId, $lang)
	{
		$queryStr = 'SELECT distinct(pt_serial) ';
		$queryStr .=  'FROM product RIGHT JOIN product_with_category ON pt_serial = pw_product_serial ';
		$queryStr .=  'WHERE pt_deleted = false ';// 削除されていない
		$queryStr .=    'AND pt_visible = true ';		// 表示可能な商品
		$queryStr .=    'AND pt_language_id = ? ';
		if (!empty($categoryId)) $queryStr .=    'AND pw_category_id in (' . $categoryId . ') ';
		return $this->selectRecordCount($queryStr, array($lang));
	}
	/**
	 * 商品一覧を取得
	 *
	 * @param array		$keywords			検索キーワード
	 * @param string	$lang				言語
	 * @param int		$limit				取得する項目数
	 * @param int		$offset				取得する先頭位置(0～)
	 * @param function	$callback			コールバック関数
	 * @return 			なし
	 */
	function getProductByKeyword($keywords, $lang, $limit, $offset, $callback)
	{
		$params = array();
		$queryStr = 'SELECT * FROM product ';
		$queryStr .=  'WHERE pt_language_id = ? '; $params[] = $lang;
		$queryStr .=    'AND pt_deleted = false ';		// 削除されていない
		$queryStr .=    'AND pt_visible = true ';		// 表示可能な商品
		
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
		$queryStr .=  'ORDER BY pt_sort_order limit ' . $limit . ' offset ' . $offset;
		$this->selectLoop($queryStr, $params, $callback, null);
	}
	/**
	 * 商品総数を取得
	 *
	 * @param array		$keywords			検索キーワード
	 * @param string	$lang				言語
	 * @return int							商品総数
	 */
	function getProductCountByKeyword($keywords, $lang)
	{
		$params = array();
		$queryStr = 'SELECT * FROM product ';
		$queryStr .=  'WHERE pt_language_id = ? '; $params[] = $lang;
		$queryStr .=    'AND pt_deleted = false ';		// 削除されていない
		$queryStr .=    'AND pt_visible = true ';		// 表示可能な商品

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
	 * 商品をシリアル番号で取得
	 *
	 * @param int		$serial				シリアル番号
	 * @param array     $row				レコード
	 * @param array     $row2				商品価格
	 * @param array     $row3				商品画像
	 * @param array     $row4				商品ステータス
	 * @return bool							取得 = true, 取得なし= false
	 */
	function getProductBySerial($serial, &$row, &$row2, &$row3, &$row4)
	{
		//$queryStr  = 'select * from product LEFT JOIN _login_user ON pt_create_user_id = lu_id AND lu_deleted = false ';
		$queryStr  = 'SELECT * FROM product LEFT JOIN product_record ON pt_id = pe_product_id AND pt_language_id = pe_language_id ';
		$queryStr .=   'WHERE pt_serial = ? ';
		$ret = $this->selectRecord($queryStr, array($serial), $row);
		if ($ret){
			$queryStr  = 'SELECT * FROM product_price ';
			$queryStr .=   'LEFT JOIN currency ON pp_currency_id = cu_id ';
			$queryStr .=   'WHERE pp_deleted = false ';// 削除されていない
			$queryStr .=     'AND pp_product_id = ? ';
			$this->selectRecords($queryStr, array($row['pt_id']), $row2);
			
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
		$queryStr  = 'SELECT * FROM product LEFT JOIN product_record ON pt_id = pe_product_id AND pt_language_id = pe_language_id ';
		$queryStr .=   'WHERE pt_deleted = false ';	// 削除されていない
		$queryStr .=    'AND pt_visible = true ';		// 表示可能な商品
		$queryStr .=    'AND pt_id = ? ';
		$queryStr .=    'AND pt_language_id = ? ';
		$ret = $this->selectRecord($queryStr, array($id, $langId), $row);
		if ($ret){
			$queryStr  = 'SELECT * FROM product_price ';
			$queryStr .=   'LEFT JOIN currency ON pp_currency_id = cu_id ';
			$queryStr .=   'WHERE pp_deleted = false ';// 削除されていない
			$queryStr .=     'AND pp_product_id = ? ';
			$this->selectRecords($queryStr, array($row['pt_id']), $row2);
			
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
	 * 商品カテゴリーをカテゴリーIDで取得
	 *
	 * @param int		$id					カテゴリーID
	 * @param string	$langId				言語ID
	 * @param array     $row				レコード
	 * @return bool							取得 = true, 取得なし= false
	 */
/*	function getCategoryByCategoryId($id, $langId, &$row)
	{
		$queryStr  = 'SELECT * FROM product_category ';
		$queryStr .=   'WHERE pc_deleted = false ';	// 削除されていない
		$queryStr .=   'AND pc_id = ? ';
		$queryStr .=   'AND pc_language_id = ? ';
		$ret = $this->selectRecord($queryStr, array($id, $langId), $row);
		return $ret;
	}*/
	/**
	 * サブ商品カテゴリーを取得
	 *
	 * @param int		$id			カテゴリーID
	 * @param string	$langId		言語ID
	 * @param array  $rows			取得レコード
	 * @return bool					true=取得、false=取得せず
	 */
	function getChildCategory($id, $langId, &$rows)
	{
		$queryStr  = 'SELECT * FROM product_category ';
		$queryStr .=   'WHERE pc_deleted = false ';	// 削除されていない
		$queryStr .=     'AND pc_parent_id = ? ';
		$queryStr .=     'AND pc_language_id = ? ';
		$queryStr .=   'ORDER BY pc_sort_order';
		$retValue = $this->selectRecords($queryStr, array($id, $langId), $rows);
		return $retValue;
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
	 * 単位情報を取得
	 *
	 * @param string	$type				単位タイプ
	 * @param string	$lang				言語
	 * @param array     $row				レコード
	 * @return bool							取得 = true, 取得なし= false
	 */
	function getUnitTypeRecord($type, $lang, &$row)
	{
		$queryStr = 'SELECT * FROM unit_type ';
		$queryStr .=  'WHERE ut_id = ? AND ut_language_id = ? ';
		$ret = $this->selectRecord($queryStr, array($type, $lang), $row);
		return $ret;
	}
}
?>
