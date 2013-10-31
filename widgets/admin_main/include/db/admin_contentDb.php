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
 * @copyright  Copyright 2006-2013 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: admin_contentDb.php 5945 2013-04-19 00:31:14Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getDbPath() . '/baseDb.php');

class admin_contentDb extends BaseDb
{
	/**
	 * 汎用コンテンツ一覧を取得
	 *
	 * @param string	$contentType		コンテンツタイプ
	 * @param string	$langId				言語
	 * @param int		$limit				取得する項目数
	 * @param int		$page				取得するページ(1～)
	 * @param int		$sortOrder			検索ソート順(0=降順、1=昇順)
	 * @param function	$callback			コールバック関数
	 * @return 			なし
	 */
	function getContentList($contentType, $langId, $limit, $page, $sortOrder, $callback)
	{
		$offset = $limit * ($page -1);
		if ($offset < 0) $offset = 0;
		
		$params = array();
		$queryStr  = 'SELECT cn_id AS id, cn_name AS name FROM content ';
		$queryStr .=   'WHERE cn_deleted = false ';		// 削除されていない
		$queryStr .=     'AND cn_type = ? '; $params[] = $contentType;
		$queryStr .=     'AND cn_language_id = ? '; $params[] = $langId;

		$queryStr .=  'ORDER BY cn_id ';
		if (empty($sortOrder)){		// 降順のとき
			$queryStr .=  'desc ';
		}
		$queryStr .=  'limit ' . $limit . ' offset ' . $offset;
		$this->selectLoop($queryStr, $params, $callback);
	}
	/**
	 * コンテンツ項目をコンテンツIDで取得
	 *
	 * @param string  $contentType		コンテンツタイプ
	 * @param string	$contentId		コンテンツID
	 * @param string	$langId			言語ID
	 * @param array     $row			レコード
	 * @return bool						取得 = true, 取得なし= false
	 */
	function getContent($contentType, $contentId, $langId, &$row)
	{
		$queryStr  = 'SELECT * FROM content LEFT JOIN _login_user ON cn_create_user_id = lu_id AND lu_deleted = false ';
		$queryStr .=   'WHERE cn_deleted = false ';	// 削除されていない
		$queryStr .=    'AND cn_type = ? ';
		$queryStr .=   'AND cn_id = ? ';
		$queryStr .=   'AND cn_language_id = ? ';
		$ret = $this->selectRecord($queryStr, array($contentType, $contentId, $langId), $row);
		return $ret;
	}
	/**
	 * ブログ記事一覧を取得
	 *
	 * @param string	$langId				言語
	 * @param int		$limit				取得する項目数
	 * @param int		$page				取得するページ(1～)
	 * @param function	$callback			コールバック関数
	 * @return 			なし
	 */
	function getEntryList($langId, $limit, $page, $callback)
	{
		$offset = $limit * ($page -1);
		if ($offset < 0) $offset = 0;
		
		$params = array();
		$queryStr  = 'SELECT be_id AS id, be_name AS name FROM blog_entry ';
		$queryStr .=   'WHERE be_language_id = ? '; $params[] = $langId;
		$queryStr .=     'AND be_deleted = false ';		// 削除されていない
		
		$queryStr .=  'ORDER BY be_id desc limit ' . $limit . ' offset ' . $offset;
		$this->selectLoop($queryStr, $params, $callback);
	}
	/**
	 * ブログ記事を取得
	 *
	 * @param int,array		$id				エントリーID
	 * @param string	$langId				言語
	 * @param array     $row				レコード
	 * @return bool							取得 = true, 取得なし= false
	 */
	function getEntry($id, $langId, &$row)
	{
		$queryStr  = 'SELECT * FROM blog_entry LEFT JOIN blog_id ON be_blog_id = bl_id AND bl_deleted = false ';
		$queryStr .=   'WHERE be_deleted = false ';	// 削除されていない
		$queryStr .=   'AND be_id = ? ';
		$queryStr .=   'AND be_language_id = ? ';
		$ret = $this->selectRecord($queryStr, array($id, $langId), $row);
		return $ret;
	}
	/**
	 * 商品情報一覧を取得
	 *
	 * @param string	$langId				言語
	 * @param int		$limit				取得する項目数
	 * @param int		$page				取得するページ(1～)
	 * @param function	$callback			コールバック関数
	 * @return 			なし
	 */
	function getProductList($langId, $limit, $page, $callback)
	{
		$offset = $limit * ($page -1);
		if ($offset < 0) $offset = 0;
		
		$params = array();
		$queryStr  = 'SELECT pt_id AS id, pt_name AS name FROM product LEFT JOIN _login_user ON pt_create_user_id = lu_id AND lu_deleted = false ';
		$queryStr .=   'WHERE pt_deleted = false ';		// 削除されていない
		$queryStr .=     'AND pt_language_id = ? ';$params[] = $langId;
		$queryStr .=   'ORDER BY pt_id DESC ';
		$queryStr .=   'LIMIT ' . $limit . ' OFFSET ' . $offset;
		$this->selectLoop($queryStr, $params, $callback);
	}
	/**
	 * 商品情報数を取得
	 *
	 * @param string	$langId				言語
	 * @return int							項目数
	 */
	function getProductCount($langId)
	{
		$params = array();
		$queryStr  = 'SELECT * FROM product LEFT JOIN _login_user ON pt_create_user_id = lu_id AND lu_deleted = false ';
		$queryStr .=   'WHERE pt_deleted = false ';		// 削除されていない
		$queryStr .=     'AND pt_language_id = ? ';$params[] = $langId;
		return $this->selectRecordCount($queryStr, $params);
	}
	/**
	 * 商品を商品ID、言語IDで取得
	 *
	 * @param int		$id					商品ID
	 * @param string	$langId				言語ID
	 * @param array     $row				レコード
	 * @return bool							取得 = true, 取得なし= false
	 */
	function getProduct($id, $langId, &$row)
	{
		$queryStr  = 'SELECT * FROM product LEFT JOIN product_record ON pt_id = pe_product_id AND pt_language_id = pe_language_id ';
		$queryStr .=   'WHERE pt_deleted = false ';	// 削除されていない
		$queryStr .=    'AND pt_visible = true ';		// 表示可能な商品
		$queryStr .=    'AND pt_id = ? ';
		$queryStr .=    'AND pt_language_id = ? ';
		$ret = $this->selectRecord($queryStr, array($id, $langId), $row);
		return $ret;
	}
	/**
	 * イベント情報一覧を取得
	 *
	 * @param string	$langId				言語
	 * @param int		$limit				取得する項目数
	 * @param int		$page				取得するページ(1～)
	 * @param function	$callback			コールバック関数
	 * @return 			なし
	 */
	function getEventList($langId, $limit, $page, $callback)
	{
		$offset = $limit * ($page -1);
		if ($offset < 0) $offset = 0;
		
		$params = array();
		$queryStr  = 'SELECT ee_id AS id, ee_name AS name FROM event_entry LEFT JOIN _login_user ON ee_create_user_id = lu_id AND lu_deleted = false ';
		$queryStr .=   'WHERE ee_deleted = false ';		// 削除されていない
		$queryStr .=     'AND ee_language_id = ? ';$params[] = $langId;
		$queryStr .=   'ORDER BY ee_create_dt DESC ';
		$queryStr .=   'LIMIT ' . $limit . ' OFFSET ' . $offset;
		$this->selectLoop($queryStr, $params, $callback);
	}
	/**
	 * イベント情報数を取得
	 *
	 * @param string	$langId				言語
	 * @return int							項目数
	 */
	function getEventCount($langId)
	{
		$params = array();
		$queryStr  = 'SELECT * FROM event_entry LEFT JOIN _login_user ON ee_create_user_id = lu_id AND lu_deleted = false ';
		$queryStr .=   'WHERE ee_deleted = false ';		// 削除されていない
		$queryStr .=     'AND ee_language_id = ? ';$params[] = $langId;
		return $this->selectRecordCount($queryStr, $params);
	}
	/**
	 * イベント情報を取得
	 *
	 * @param int		$id					イベントID
	 * @param string	$langId				言語
	 * @param array     $row				レコード
	 * @return bool							取得 = true, 取得なし= false
	 */
	function getEvent($id, $langId, &$row)
	{
		$queryStr  = 'SELECT * FROM event_entry ';
		$queryStr .=   'WHERE ee_deleted = false ';	// 削除されていない
		$queryStr .=   'AND ee_id = ? ';
		$queryStr .=   'AND ee_language_id = ? ';
		$ret = $this->selectRecord($queryStr, array($id, $langId), $row);
		return $ret;
	}
	/**
	 * Wiki情報一覧を取得
	 *
	 * @param string	$langId				言語
	 * @param int		$limit				取得する項目数
	 * @param int		$page				取得するページ(1～)
	 * @param function	$callback			コールバック関数
	 * @return 			なし
	 */
	function getWikiList($langId, $limit, $page, $callback)
	{
		$offset = $limit * ($page -1);
		if ($offset < 0) $offset = 0;
		
		$type = '';		// Wikiコンテンツタイプ
		$params = array();
		$queryStr  = 'SELECT wc_id AS id, wc_id AS name FROM wiki_content LEFT JOIN _login_user ON wc_create_user_id = lu_id AND lu_deleted = false ';
		$queryStr .=   'WHERE wc_deleted = false ';		// 削除されていない
		$queryStr .=     'AND wc_type = ? ';$params[] = $type;
		$queryStr .=   'ORDER BY wc_create_dt DESC ';
		$queryStr .=   'LIMIT ' . $limit . ' OFFSET ' . $offset;
		$this->selectLoop($queryStr, $params, $callback);
	}
	/**
	 * Wiki情報数を取得
	 *
	 * @param string	$langId				言語
	 * @return int							項目数
	 */
	function getWikiCount($langId)
	{
		$type = '';		// Wikiコンテンツタイプ
		$params = array();
		$queryStr  = 'SELECT * FROM wiki_content LEFT JOIN _login_user ON wc_create_user_id = lu_id AND lu_deleted = false ';
		$queryStr .=   'WHERE wc_deleted = false ';		// 削除されていない
		$queryStr .=     'AND wc_type = ? ';$params[] = $type;
		return $this->selectRecordCount($queryStr, $params);
	}
}
?>
