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
 * @copyright  Copyright 2006-2016 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getDbPath() . '/baseDb.php');

class linkInfoDb extends BaseDb
{
	/**
	 * ウィジェットが配置されているページサブIDのリストを取得
	 *
	 * @param string $pageId		ページID
	 * @param function $callback	コールバック関数
	 * @param int    $setId			定義セットID
	 * @return						なし
	 */
	function getPageSubIdListWithWidget($pageId, $callback, $setId = 0)
	{
		$queryStr  = 'SELECT DISTINCT pg_id, pg_name, pn_content_type FROM _page_def LEFT JOIN _page_id ON pd_sub_id = pg_id AND pg_type = 1 ';// ページサブID
		$queryStr .= 'LEFT JOIN _page_info ON pd_id = pn_id AND pd_sub_id = pn_sub_id AND pn_deleted = false AND pn_language_id = \'\' ';
		$queryStr .=   'WHERE pd_id = ? ';
		$queryStr .=     'AND pd_sub_id != \'\' ';	// 共通でないウィジェットが配置されている
		$queryStr .=     'AND pd_set_id = ? ';
		$queryStr .=     'AND pg_visible = true ';	// 外部公開可能なページ
		$queryStr .=     'AND pg_active = true ';	// 外部公開可能なページ
		$queryStr .=   'ORDER BY pg_priority';
		$this->selectLoop($queryStr, array($pageId, $setId), $callback);
	}
	/**
	 * 画面配置している主要コンテンツ編集ウィジェットを取得
	 *
	 * @param string $langId			言語ID
	 * @param array $pageIdArray		ページID
	 * @param array $contentTypeArray    コンテンツタイプ
	 * @param array  $rows				取得レコード
	 * @param int    $setId				定義セットID
	 * @return							true=取得、false=取得せず
	 */
	function getEditWidgetOnPage($langId, $pageIdArray, $contentTypeArray, &$rows, $setId = 0)
	{
		// CASE文作成
		$caseStr = 'CASE pd_id ';
		$pageStr = '';
		for ($i = 0; $i < count($pageIdArray); $i++){
			$caseStr .= 'WHEN \'' . $pageIdArray[$i] . '\' THEN ' . $i . ' ';
			$pageStr .= '\'' . $pageIdArray[$i] . '\', ';
		}
		$caseStr .= 'END AS pageno, ';
		$pageStr = rtrim($pageStr, ', ');
		
		$caseStr .= 'CASE wd_type ';
		$contentStr = '';
		for ($i = 0; $i < count($contentTypeArray); $i++){
			$caseStr .= 'WHEN \'' . $contentTypeArray[$i] . '\' THEN ' . $i . ' ';
			$contentStr .= '\'' . $contentTypeArray[$i] . '\', ';
		}
		$caseStr .= 'ELSE 100 ';		// デフォルトでないメインコンテンツ編集ウィジェットは後にする
		$caseStr .= 'END AS contentno';
		$contentStr = rtrim($contentStr, ', ');
		
		$queryStr  = 'SELECT DISTINCT pd_id, wd_id, wd_name, wd_type, wd_content_info, wd_content_name, ls_value, ' . $caseStr . ' FROM _page_def ';
		$queryStr .=   'LEFT JOIN _widgets ON pd_widget_id = wd_id AND wd_deleted = false ';
		$queryStr .=   'LEFT JOIN _page_id ON pd_sub_id = pg_id AND pg_type = 1 ';// ページサブID
		$queryStr .=   'LEFT JOIN _language_string ON wd_type = ls_id AND ls_type = 2 AND ls_language_id = ? ';	// コンテンツ種別名
		$queryStr .= 'WHERE pd_set_id = ? ';
		$queryStr .=   'AND pd_id in (' . $pageStr . ') ';
		//$queryStr .=   'AND pd_visible = true ';			// ウィジェットは表示中に限定しない
		$queryStr .=   'AND wd_deleted = false ';			// ウィジェットは削除されていない
		$queryStr .=   'AND wd_active = true ';				// 一般ユーザが実行可能かどうか
		$queryStr .=   'AND (pd_sub_id = \'\' OR pg_active = true) ';		// グローバル属性ウィジェットか公開中のページ上のウィジェット
//		$queryStr .=   'AND wd_edit_content = true ';			// ##### メインウィジェットに限定しない #####
		$queryStr .=   'AND wd_type in (' . $contentStr . ') ';	// ##### パラメータのコンテンツタイプに限定 #####
//		$queryStr .=   'AND wd_type != \'\' ';
//		$queryStr .=   'AND wd_use_instance_def = false ';		// インスタンス定義を使用しないウィジェットをメインコンテンツ編集ウィジェットとする
		$queryStr .= 'ORDER BY pageno, contentno';
		$retValue = $this->selectRecords($queryStr, array($langId, $setId), $rows);
		return $retValue;
	}
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
		$queryStr .=     'AND be_history_index >= 0 ';		// 正規(Regular)記事を対象
		
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
		$queryStr .=   'AND be_history_index >= 0 ';		// 正規(Regular)記事を対象
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
	 * フォト情報一覧を取得
	 *
	 * @param string	$langId				言語
	 * @param int		$limit				取得する項目数
	 * @param int		$page				取得するページ(1～)
	 * @param function	$callback			コールバック関数
	 * @return 			なし
	 */
	function getPhotoList($langId, $limit, $page, $callback)
	{
		$offset = $limit * ($page -1);
		if ($offset < 0) $offset = 0;
		
		$params = array();
		$queryStr  = 'SELECT ht_public_id AS id, ht_name AS name FROM photo LEFT JOIN _login_user ON ht_create_user_id = lu_id AND lu_deleted = false ';
		$queryStr .=   'WHERE ht_deleted = false ';// 削除されていない
		$queryStr .=     'AND ht_language_id = ? '; $params[] = $langId;
		$queryStr .=   'ORDER BY ht_create_dt DESC ';
		$queryStr .=   'LIMIT ' . $limit . ' OFFSET ' . $offset;
		$this->selectLoop($queryStr, $params, $callback);
	}
	/**
	 * フォト情報数を取得
	 *
	 * @param string	$langId				言語
	 * @return int							項目数
	 */
	function getPhotoCount($langId)
	{
		$params = array();
		$queryStr  = 'SELECT ht_public_id AS id, ht_name AS name FROM photo LEFT JOIN _login_user ON ht_create_user_id = lu_id AND lu_deleted = false ';
		$queryStr .=   'WHERE ht_deleted = false ';// 削除されていない
		$queryStr .=     'AND ht_language_id = ? '; $params[] = $langId;
		return $this->selectRecordCount($queryStr, $params);
	}
	/**
	 * フォト情報を取得
	 *
	 * @param int		$id					イベントID
	 * @param string	$langId				言語
	 * @param array     $row				レコード
	 * @return bool							取得 = true, 取得なし= false
	 */
	function getPhoto($id, $langId, &$row)
	{
		$queryStr  = 'SELECT * FROM photo ';
		$queryStr .=   'WHERE ht_deleted = false ';
		$queryStr .=     'AND ht_public_id = ? ';
		$queryStr .=     'AND ht_language_id = ? ';
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
	/**
	 * コンテンツ編集用のメインウィジェットを取得
	 *
	 * @param string $contentType	コンテンツタイプ
	 * @param int $deviceType		デバイスタイプ
	 * @param array  $rows			レコード
	 * @return bool					1行以上取得 = true, 取得なし= false
	 */
	function getContentEditWidget($contentType, $deviceType, &$rows)
	{
		$queryStr  = 'SELECT * FROM _widgets ';
		$queryStr .=   'WHERE wd_deleted = false ';	// 削除されていない
		$queryStr .=     'AND wd_edit_content = true ';				// コンテンツ編集可能
		$queryStr .=     'AND wd_content_widget_id = \'\' ';		// メインタイプ
		$queryStr .=     'AND wd_content_type = ? ';		// コンテンツタイプ
		$queryStr .=     'AND wd_device_type = ? ';		// デバイスタイプ
		$queryStr .=   'ORDER BY wd_priority';
		$retValue = $this->selectRecords($queryStr, array($contentType, $deviceType), $rows);
		return $retValue;
	}
}
?>
