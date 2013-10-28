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
	 * @param int		$searchKey			検索キー(0=コンテンツID、1=更新日時)
	 * @param int		$searchOrder		検索ソート順(0=降順、1=昇順)
	 * @param function	$callback			コールバック関数
	 * @return 			なし
	 */
	function getContentList($contentType, $langId, $limit, $page, $searchKey, $searchOrder, $callback)
	{
		$offset = $limit * ($page -1);
		if ($offset < 0) $offset = 0;
		
		$params = array();
		$queryStr  = 'SELECT cn_id AS id, cn_name AS name FROM content ';
		$queryStr .=   'WHERE cn_deleted = false ';		// 削除されていない
		$queryStr .=     'AND cn_type = ? '; $params[] = $contentType;
		$queryStr .=     'AND cn_language_id = ? '; $params[] = $langId;

		if (empty($searchKey)){			// ソートキー
			$queryStr .=  'ORDER BY cn_id ';
		} else {			// 更新日時
			$queryStr .=  'ORDER BY cn_create_dt ';
		}
		if (empty($searchOrder)){		// 降順のとき
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
	function getContentByContentId($contentType, $contentId, $langId, &$row)
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
}
?>
