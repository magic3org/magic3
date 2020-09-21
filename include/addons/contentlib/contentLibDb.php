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
 * @copyright  Copyright 2006-2020 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
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
	 * コンテンツ項目をシリアル番号で取得
	 *
	 * @param string	$serial				シリアル番号
	 * @param array     $row				レコード
	 * @return bool							取得 = true, 取得なし= false
	 */
	function getContentBySerial($serial, &$row)
	{
		$queryStr  = 'SELECT * FROM content LEFT JOIN _login_user ON cn_create_user_id = lu_id AND lu_deleted = false ';
		$queryStr .=   'WHERE cn_serial = ? ';
		$ret = $this->selectRecord($queryStr, array($serial), $row);
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
	 * @param string,array	$keywords		検索キーワード
	 * @param string	$langId				言語
	 * @param int		$order				取得順(0=昇順,1=降順)
	 * @param int       $userId				参照制限用ユーザID
	 * @param function	$callback			コールバック関数
	 * @param int		$categoryId			カテゴリーID(nullのとき指定なし)
	 * @return 			なし
	 */
	function getPublicContentItems($limit, $page, $contentId, $now, $startDt, $endDt, $keywords, $langId, $order, $userId, $callback, $categoryId = null)
	{
		$initDt = $this->gEnv->getInitValueOfTimestamp();
		$offset = $limit * ($page -1);
		if ($offset < 0) $offset = 0;
		
		$params = array();
		$queryStr = 'SELECT * FROM content LEFT JOIN _login_user ON cn_create_user_id = lu_id AND lu_deleted = false ';
		$queryStr .=  'WHERE cn_visible = true ';
		$queryStr .=    'AND cn_deleted = false ';		// 削除されていない
		$queryStr .=    'AND cn_search_target = true ';		// 検索対象
		$queryStr .=    'AND cn_type = ? ';$params[] = '';				// 汎用コンテンツ
		$queryStr .=    'AND cn_history_index >= 0 ';		// 正規(Regular)記事を対象
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
		list($condQueryStr, $condParams) = $this->_createPublicSearchCondition($now, $startDt, $endDt, $keywords, $userId, $categoryId);
		$queryStr .= $condQueryStr;
		$params = array_merge($params, $condParams);
		
		if (empty($contentId)){
			$ord = '';
			if (!empty($order)) $ord = 'DESC ';
			$queryStr .=  'ORDER BY cn_create_dt ' . $ord . 'LIMIT ' . $limit . ' offset ' . $offset;// 作成順
		}
		$this->selectLoop($queryStr, $params, $callback);
	}
	/**
	 * 公開中のコンテンツ項目数を取得
	 *
	 * @param timestamp $now				現在日時(現在日時より未来の投稿日時の記事は取得しない)
	 * @param timestamp	$startDt			期間(開始日)
	 * @param timestamp	$endDt				期間(終了日)
	 * @param string,array	$keywords		検索キーワード
	 * @param string	$langId				言語
	 * @param int       $userId				参照制限用ユーザID
	 * @param int		$categoryId			カテゴリーID(nullのとき指定なし)
	 * @return int							項目数
	 */
	function getPublicContentItemsCount($now, $startDt, $endDt, $keywords, $langId, $userId, $categoryId = null)
	{
		$initDt = $this->gEnv->getInitValueOfTimestamp();		// 日時初期化値
		$params = array();
		
		$queryStr = 'SELECT * FROM content LEFT JOIN _login_user ON cn_create_user_id = lu_id AND lu_deleted = false ';
		$queryStr .=  'WHERE cn_visible = true ';
		$queryStr .=    'AND cn_deleted = false ';		// 削除されていない
		$queryStr .=    'AND cn_search_target = true ';		// 検索対象
		$queryStr .=    'AND cn_type = ? ';$params[] = '';				// 汎用コンテンツ
		$queryStr .=    'AND cn_history_index >= 0 ';		// 正規(Regular)記事を対象
		$queryStr .=    'AND cn_language_id = ? ';$params[] = $langId;
		
		// ##### 任意設定の検索条件 #####
		list($condQueryStr, $condParams) = $this->_createPublicSearchCondition($now, $startDt, $endDt, $keywords, $userId, $categoryId);
		$queryStr .= $condQueryStr;
		$params = array_merge($params, $condParams);

		return $this->selectRecordCount($queryStr, $params);
	}
	/**
	 * 公開中のコンテンツ項目の検索条件を作成
	 *
	 * @param timestamp $now				現在日時
	 * @param timestamp	$startDt			期間(開始日)
	 * @param timestamp	$endDt				期間(終了日)
	 * @param string,array	$keywords		検索キーワード
	 * @param int       $userId				ユーザID
	 * @param int		$categoryId			カテゴリーID(nullのとき指定なし)
	 * @return array						クエリー文字列と配列パラメータの連想配列
	 */
	function _createPublicSearchCondition($now, $startDt, $endDt, $keywords, $userId, $categoryId = null)
	{
		$initDt = $this->gEnv->getInitValueOfTimestamp();		// 日時初期化値
		$queryStr = '';
		$params = array();
	
		// ##### 検索条件 #####
		// 期間で指定
		if (!empty($startDt)){
			$queryStr .=    'AND ? <= cn_create_dt ';
			$params[] = $startDt;
		}
		if (!empty($endDt)){
			$queryStr .=    'AND cn_create_dt < ? ';
			$params[] = $endDt;
		}
		
		// タイトル、本文、ユーザ定義フィールドを検索
		if (!empty($keywords)){
			if (is_string($keywords)) $keywords = array($keywords);
			
			for ($i = 0; $i < count($keywords); $i++){
				$keyword = addslashes($keywords[$i]);// 「'"\」文字をエスケープ
				$queryStr .=    'AND (cn_name LIKE \'%' . $keyword . '%\' ';
				$queryStr .=    'OR cn_html LIKE \'%' . $keyword . '%\' ';
				$queryStr .=    'OR cn_description LIKE \'%' . $keyword . '%\' ';
				$queryStr .=    'OR cn_option_fields LIKE \'%' . $keyword . '%\') ';	// ユーザ定義フィールド
			}
		}

		// カテゴリー
		if (isset($categoryId)){

		}

		// ##### コンテンツ参照制限 #####
		// 公開期間を指定
		$queryStr .=    'AND (cn_active_start_dt = ? OR (cn_active_start_dt != ? AND cn_active_start_dt <= ?)) ';
		$queryStr .=    'AND (cn_active_end_dt = ? OR (cn_active_end_dt != ? AND cn_active_end_dt > ?)) ';
		$params[] = $initDt;
		$params[] = $initDt;
		$params[] = $now;
		$params[] = $initDt;
		$params[] = $initDt;
		$params[] = $now;
		
		// ##### ユーザによる参照制限 #####
		// ゲストユーザはユーザ制限のない記事のみ参照可能
		if (empty($userId)){
			$queryStr .= 'AND cn_user_limited = false ';		// ユーザ制限のないデータ
		}
		
		return array($queryStr, $params);
	}
	/**
	 * コンテンツ項目の新規追加
	 *
	 * @param string  $contentType	コンテンツタイプ
	 * @param string  $lang			言語ID
	 * @param string  $name			コンテンツ名
	 * @param string  $desc			説明
	 * @param string  $html			HTML
	 * @param bool    $visible		表示状態
	 * @param bool    $default		デフォルトで使用するかどうか(未使用)
	 * @param bool    $limited		ユーザ制限するかどうか
	 * @param string  $key			外部参照用キー
	 * @param string  $password		パスワード
	 * @param string  $metaTitle	METAタグ、タイトル
	 * @param string  $metaDesc		METAタグ、ページ要約
	 * @param string  $metaKeyword	METAタグ、検索用キーワード
	 * @param string  $headOthers	ヘッダ部その他
	 * @param timestamp	$startDt	期間(開始日)
	 * @param timestamp	$endDt		期間(終了日)
	 * @param int     $newSerial	新規シリアル番号
	 * @param array   $otherParams	その他のフィールド値
	 * @return bool					true = 成功、false = 失敗
	 */
	function addContentItem($contentType, $lang, $name, $desc, $html, $visible, $default, $limited, $key, $password, $metaTitle, $metaDesc, $metaKeyword, $headOthers, $startDt, $endDt, &$newSerial,
								$otherParams = null)
	{
		$now = date("Y/m/d H:i:s");	// 現在日時
		$user = $this->gEnv->getCurrentUserId();	// 現在のユーザ
		
		// トランザクション開始
		$this->startTransaction();
		
		// コンテンツIDを決定する
		$queryStr  = 'SELECT MAX(cn_id) AS mid FROM content ';
		$queryStr .=   'WHERE cn_type = ? ';
		$ret = $this->selectRecord($queryStr, array($contentType), $row);
		if ($ret){
			$contId = $row['mid'] + 1;
		} else {
			$contId = 1;
		}
		
		// 前レコードの削除状態チェック
		$historyIndex = 0;
		$queryStr = 'SELECT * FROM content ';
		$queryStr .=  'WHERE cn_type = ? ';
		$queryStr .=    'AND cn_id = ? ';
		$queryStr .=    'AND cn_language_id = ? ';
		$queryStr .=  'ORDER BY cn_history_index DESC ';
		$ret = $this->selectRecord($queryStr, array($contentType, $contId, $lang), $row);
		if ($ret){
			if (!$row['cn_deleted']){		// レコード存在していれば終了
				$this->endTransaction();
				return false;
			}
			$historyIndex = $row['cn_history_index'] + 1;
		}
		
		// データを追加
		$params = array($contentType, $contId, $lang, $historyIndex, $name, $desc, $html,
								intval($visible), intval($limited), $key, $password, $metaTitle, $metaDesc, $metaKeyword, $headOthers, $startDt, $endDt, $user, $now);
								
		$queryStr  = 'INSERT INTO content ';
		$queryStr .=   '(';
		$queryStr .=   'cn_type, ';
		$queryStr .=   'cn_id, ';
		$queryStr .=   'cn_language_id, ';
		$queryStr .=   'cn_history_index, ';
		$queryStr .=   'cn_name, ';
		$queryStr .=   'cn_description, ';
		$queryStr .=   'cn_html, ';
		$queryStr .=   'cn_visible, ';
		$queryStr .=   'cn_user_limited, ';
		$queryStr .=   'cn_key, ';
		$queryStr .=   'cn_password, ';
		$queryStr .=   'cn_meta_title, ';
		$queryStr .=   'cn_meta_description, ';
		$queryStr .=   'cn_meta_keywords, ';
		$queryStr .=   'cn_head_others, ';
		$queryStr .=   'cn_active_start_dt, ';
		$queryStr .=   'cn_active_end_dt, ';
		$queryStr .=   'cn_create_user_id, ';
		$queryStr .=   'cn_create_dt ';
		
		// その他のフィールド値を追加
		$otherValueStr = '';
		if (!empty($otherParams)){
			$keys = array_keys($otherParams);// キーを取得
			for ($i = 0; $i < count($keys); $i++){
				$fieldName = $keys[$i];
				$fieldValue = $otherParams[$fieldName];
				if (!isset($fieldValue)) continue;
				$params[] = $fieldValue;
				$queryStr .= ', ' . $fieldName;
				$otherValueStr .= ', ?';
			}
		}
		$queryStr .=  ') VALUES ';
		$queryStr .=  '(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?' . $otherValueStr . ')';
		$this->execStatement($queryStr, $params);
		
		// 新規のシリアル番号取得
		$queryStr = 'select max(cn_serial) as ns from content ';
		$ret = $this->selectRecord($queryStr, array(), $row);
		if ($ret) $newSerial = $row['ns'];
			
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
}
?>
