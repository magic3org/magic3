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
 * @version    SVN: $Id: contentLibDb.php 5895 2013-04-01 23:57:46Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getDbPath() . '/baseDb.php');

class contentLibDb extends BaseDb
{
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
	 * 汎用コンテンツ定義値を取得をすべて取得
	 *
	 * @param string $contentType	コンテンツタイプ
	 * @param array  $rows			レコード
	 * @return bool					1行以上取得 = true, 取得なし= false
	 */
	function getAllConfig($contentType, &$rows)
	{
		$queryStr  = 'SELECT * FROM content_config ';
		$queryStr .=   'WHERE ng_type = ? ';
		$queryStr .=   'ORDER BY ng_index';
		$retValue = $this->selectRecords($queryStr, array($contentType), $rows);
		return $retValue;
	}
	/**
	 * 公開中のエントリー項目を取得。アクセス制限も行う。
	 *
	 * @param int		$limit				取得する項目数
	 * @param int		$page				取得するページ(1～)
	 * @param int,array	$contentId			コンテンツID(0のときは期間で取得)
	 * @param timestamp $now				現在日時(現在日時より未来の投稿日時の記事は取得しない)
	 * @param timestamp	$startDt			期間(開始日)
	 * @param timestamp	$endDt				期間(終了日)
	 * @param array		$keywords			検索キーワード
	 * @param string	$langId				言語
	 * @param int		$order				取得順(0=昇順,1=降順)
	 * @param int       $userId				参照制限用ユーザID
	 * @param function	$callback			コールバック関数
	 * @param string	$contentType		コンテンツタイプ(空文字列=汎用コンテンツ)
	 * @return 			なし
	 */
	function getPublicContentItems($limit, $page, $contentId, $now, $startDt, $endDt, $keywords, $langId, $order, $userId, $callback, $contentType = '')
	{
		$initDt = $this->gEnv->getInitValueOfTimestamp();
		$offset = $limit * ($page -1);
		if ($offset < 0) $offset = 0;
		
		$params = array();
		$queryStr = 'SELECT * FROM content ';
		$queryStr .=  'WHERE cn_visible = true ';
		$queryStr .=    'AND cn_deleted = false ';		// 削除されていない
		$queryStr .=    'AND cn_search_target = true ';		// 検索対象
		$queryStr .=    'AND cn_type = ? ';$params[] = $contentType;
		$queryStr .=    'AND cn_language_id = ? ';$params[] = $langId;

		// ##### IDで取得コンテンツを指定 #####
		if (!empty($contentId)){
			if (is_array($contentId)){		// 配列で複数指定の場合
				$queryStr .=    'AND cn_id in (' . implode(",", $contentId) . ') ';
			} else {
				$queryStr .=     'AND cn_id = ? ';		$params[] = $contentId;
			}
		}
		
		// ##### 任意設定の検索条件 #####
		// タイトル、本文、ユーザ定義フィールドを検索
		if (!empty($keywords)){
			for ($i = 0; $i < count($keywords); $i++){
				$keyword = addslashes($keywords[$i]);// 「'"\」文字をエスケープ
				$queryStr .=    'AND (cn_name LIKE \'%' . $keyword . '%\' ';
				$queryStr .=    'OR cn_html LIKE \'%' . $keyword . '%\' ';
				$queryStr .=    'OR cn_description LIKE \'%' . $keyword . '%\' ';
				$queryStr .=    'OR cn_option_fields LIKE \'%' . $keyword . '%\') ';	// ユーザ定義フィールド
			}
		}
		
		// 期間で指定
		if (!empty($startDt)){
			$queryStr .=    'AND ? <= cn_create_dt ';
			$params[] = $startDt;
		}
		if (!empty($endDt)){
			$queryStr .=    'AND cn_create_dt < ? ';
			$params[] = $endDt;
		}
		
		// ##### ユーザによる参照制限 #####
		// ゲストユーザはユーザ制限のない記事のみ参照可能
		if (empty($userId)){
			$queryStr .= 'AND cn_user_limited = false ';		// ユーザ制限のないデータ
		}
		
		// ##### コンテンツの参照制限 #####
		// 公開期間を指定
		$queryStr .=    'AND (cn_active_start_dt = ? OR (cn_active_start_dt != ? AND cn_active_start_dt <= ?)) ';
		$queryStr .=    'AND (cn_active_end_dt = ? OR (cn_active_end_dt != ? AND cn_active_end_dt > ?)) ';
		$params[] = $initDt;
		$params[] = $initDt;
		$params[] = $now;
		$params[] = $initDt;
		$params[] = $initDt;
		$params[] = $now;
		
		if (empty($contentId)){
			$ord = '';
			if (!empty($order)) $ord = 'DESC ';
			$queryStr .=  'ORDER BY cn_create_dt ' . $ord . 'LIMIT ' . $limit . ' offset ' . $offset;// 作成順
		}
//		$queryStr .=  'ORDER BY cn_create_dt desc limit ' . $limit . ' offset ' . $offset;
		$this->selectLoop($queryStr, $params, $callback);
	}
}
?>
