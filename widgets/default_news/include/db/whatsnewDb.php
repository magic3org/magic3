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
 * @copyright  Copyright 2006-2014 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getDbPath() . '/baseDb.php');

class whatsnewDb extends BaseDb
{
	/**
	 * 汎用コメント定義値を取得
	 *
	 * @param string $contentType	コンテンツタイプ
	 * @param array  $rows			レコード
	 * @return bool					1行以上取得 = true, 取得なし= false
	 */
	function getAllConfig($contentType, &$rows)
	{
		$queryStr  = 'SELECT * FROM comment_config ';
		$queryStr .=   'WHERE cf_content_type  = ?';
		$retValue = $this->selectRecords($queryStr, array($contentType), $rows);
		return $retValue;
	}
	/**
	 * 汎用コメント定義値を取得
	 *
	 * @param string $contentType	コンテンツタイプ
	 * @param string $contentsId		コンテンツID
	 * @param array  $row			レコード
	 * @return string $value		値
	 */
	function getConfig($contentType, $contentsId, &$row)
	{
		$params = array();
		$queryStr  = 'SELECT * FROM comment_config ';
		$queryStr .=   'WHERE cf_content_type  = ?'; $params[] = $contentType;
		$queryStr .=     'AND cf_contents_id = ? '; $params[] = $contentsId;
		$ret = $this->selectRecord($queryStr, $params, $row);
		return $ret;
	}
	/**
	 * 汎用コメント定義値を更新
	 *
	 * @param string $contentType	コンテンツタイプ
	 * @param string $contentsId		コンテンツID
	 * @param array  $fieldValues	フィールド値
	 * @return						true = 正常、false=異常
	 */
	function updateConfig($contentType, $contentsId, $fieldValues)
	{
		// 引数チェック
		if (count($fieldValues) <= 0) return true;
		
		// データの確認
		$params = array();
		$queryStr  = 'SELECT * FROM comment_config ';
		$queryStr .=   'WHERE cf_content_type  = ?'; $params[] = $contentType;
		$queryStr .=     'AND cf_contents_id = ? '; $params[] = $contentsId;
		$ret = $this->isRecordExists($queryStr, $params);

		$params = array();
		$fieldQueryStr = '';
		$fieldValueStr = '';
		if ($ret){
			$keys = array_keys($fieldValues);		// キーを取得
			for ($i = 0; $i < count($keys); $i++){
				$fieldName = $keys[$i];
				$fieldValue = $fieldValues[$fieldName];
				if (!isset($fieldValue)) continue;
				$params[] = $fieldValue;
				$fieldQueryStr .= $fieldName . ' = ?, ';
			}
			$fieldQueryStr = rtrim($fieldQueryStr, ', ');

			$queryStr  = 'UPDATE comment_config ';
			$queryStr .= 'SET ' . $fieldQueryStr . ' ';
			$queryStr .= 'WHERE cf_content_type  = ? '; $params[] = $contentType;
			$queryStr .=   'AND cf_contents_id = ? '; $params[] = $contentsId;
			$ret = $this->execStatement($queryStr, $params);
			return $ret;
		} else {
			$keys = array_keys($fieldValues);		// キーを取得
			for ($i = 0; $i < count($keys); $i++){
				$fieldName = $keys[$i];
				$fieldValue = $fieldValues[$fieldName];
				if (!isset($fieldValue)) continue;
				$params[] = $fieldValue;
				$fieldQueryStr .= $fieldName . ', ';
				$fieldValueStr .= '?, ';
			}
			$params[] = $contentType;
			$fieldQueryStr .= 'cf_content_type, ';
			$fieldValueStr .= '?, ';
			$params[] = $contentsId;
			$fieldQueryStr .= 'cf_contents_id';
			$fieldValueStr .= '?';
		
			$queryStr  = 'INSERT INTO comment_config ';
			$queryStr .= '(' . $fieldQueryStr . ') VALUES ';
			$queryStr .= '(' . $fieldValueStr . ')';
			$ret = $this->execStatement($queryStr, $params);
			return $ret;
		}
	}
	/**
	 * コメント項目一覧を取得(管理用)
	 *
	 * @param string $contentType			コンテンツタイプ(空の場合はすべて)
	 * @param string	$langId				言語
	 * @param int		$limit				取得する項目数
	 * @param int		$page				取得するページ(1～)
	 * @param timestamp	$startDt			期間(開始日)
	 * @param timestamp	$endDt				期間(終了日)
	 * @param array	    $keywords			検索キーワード
	 * @param function	$callback			コールバック関数
	 * @return 			なし
	 */
	function searchCommentItems($contentType, $langId, $limit, $page, $startDt, $endDt, $keywords, $callback)
	{
		$offset = $limit * ($page -1);
		if ($offset < 0) $offset = 0;
		
		$params = array();
		$queryStr  = 'SELECT * FROM comment LEFT JOIN _login_user ON cm_create_user_id = lu_id AND lu_deleted = false ';
		$queryStr .=   'WHERE cm_language_id = ? '; $params[] = $langId;
		$queryStr .=     'AND cm_deleted = false ';		// 削除されていない
		if (!empty($contentType)){
			$queryStr .=     'AND cm_content_type = ? ';
			$params[] = $contentType;
		}
		
		// コメント内容を検索
		if (!empty($keywords)){
			for ($i = 0; $i < count($keywords); $i++){
				$keyword = addslashes($keywords[$i]);// 「'"\」文字をエスケープ
				$queryStr .=    'AND (cm_title LIKE \'%' . $keyword . '%\' ';
				$queryStr .=    'OR cm_message LIKE \'%' . $keyword . '%\' ';
				$queryStr .=    'OR cm_url LIKE \'%' . $keyword . '%\' ';
				$queryStr .=    'OR cm_author LIKE \'%' . $keyword . '%\' ';
				$queryStr .=    'OR cm_email LIKE \'%' . $keyword . '%\') ';
			}
		}
		
		// 日付範囲
		if (!empty($startDt)){
			$queryStr .=    'AND ? <= cm_regist_dt ';
			$params[] = $startDt;
		}
		if (!empty($endDt)){
			$queryStr .=    'AND cm_regist_dt < ? ';
			$params[] = $endDt;
		}
		$queryStr .=  'ORDER BY cm_serial DESC LIMIT ' . $limit . ' OFFSET ' . $offset;
		$this->selectLoop($queryStr, $params, $callback);
	}
	/**
	 * コメント項目数を取得(管理用)
	 *
	 * @param string $contentType			コンテンツタイプ(空の場合はすべて)
	 * @param string	$langId				言語
	 * @param timestamp	$startDt			期間(開始日)
	 * @param timestamp	$endDt				期間(終了日)
	 * @param array	    $keyword			検索キーワード
	 * @return int							コメント数
	 */
	function getCommentItemCount($contentType, $langId, $startDt, $endDt, $keyword)
	{
		$params = array();
		$queryStr  = 'SELECT * FROM comment LEFT JOIN _login_user ON cm_create_user_id = lu_id AND lu_deleted = false ';
		$queryStr .=   'WHERE cm_language_id = ? '; $params[] = $langId;
		$queryStr .=     'AND cm_deleted = false ';		// 削除されていない
		if (!empty($contentType)){
			$queryStr .=     'AND cm_content_type = ? ';
			$params[] = $contentType;
		}
		
		// コメント内容を検索
		if (!empty($keywords)){
			for ($i = 0; $i < count($keywords); $i++){
				$keyword = addslashes($keywords[$i]);// 「'"\」文字をエスケープ
				$queryStr .=    'AND (cm_title LIKE \'%' . $keyword . '%\' ';
				$queryStr .=    'OR cm_message LIKE \'%' . $keyword . '%\' ';
				$queryStr .=    'OR cm_url LIKE \'%' . $keyword . '%\' ';
				$queryStr .=    'OR cm_author LIKE \'%' . $keyword . '%\' ';
				$queryStr .=    'OR cm_email LIKE \'%' . $keyword . '%\') ';
			}
		}
		
		// 日付範囲
		if (!empty($startDt)){
			$queryStr .=    'AND ? <= cm_regist_dt ';
			$params[] = $startDt;
		}
		if (!empty($endDt)){
			$queryStr .=    'AND cm_regist_dt < ? ';
			$params[] = $endDt;
		}
		return $this->selectRecordCount($queryStr, $params);
	}
	/**
	 * コメント項目の更新
	 *
	 * @param int     $serial		シリアル番号
	 * @param array   $fieldData	更新フィールド値
	 * @return bool					true = 成功、false = 失敗
	 */
	function updateCommentItem($serial, $fieldData)
	{
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $this->gEnv->getCurrentUserId();	// 現在のユーザ
						
		// トランザクション開始
		$this->startTransaction();
		
		// 指定のシリアルNoのレコードが削除状態でないかチェック
		$queryStr  = 'SELECT * FROM comment ';
		$queryStr .=   'WHERE cm_serial = ? ';
		$ret = $this->selectRecord($queryStr, array($serial), $row);
		if ($ret){		// 既に登録レコードがあるとき
			if ($row['cm_deleted']){		// レコードが削除されていれば終了
				$this->endTransaction();
				return false;
			}
		} else {		// 存在しない場合は終了
			$this->endTransaction();
			return false;
		}
		
		// フィールド値を追加
		$params = array();
		$otherValueStr = '';
		if (!empty($fieldData)){
			$keys = array_keys($fieldData);// キーを取得
			for ($i = 0; $i < count($keys); $i++){
				$fieldName = $keys[$i];
				$fieldValue = $fieldData[$fieldName];
				if (!isset($fieldValue)) continue;
				$params[] = $fieldValue;
				$otherValueStr .= $fieldName . ' = ?,';
			}
		}
		
		// データを更新
		$queryStr  = 'UPDATE comment ';
		$queryStr .=   'SET ';
		$queryStr .=     $otherValueStr;
		$queryStr .=     'cm_update_user_id = ?, ';
		$queryStr .=     'cm_update_dt = ? ';
		$queryStr .=   'WHERE cm_serial = ?';
		$this->execStatement($queryStr, array_merge($params, array($userId, $now, $serial)));
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * コメント項目をシリアル番号で取得
	 *
	 * @param string	$serial				シリアル番号
	 * @param array     $row				レコード
	 * @return bool							取得 = true, 取得なし= false
	 */
	function getCommentItem($serial, &$row)
	{
		$queryStr  = 'SELECT *, cdb.lu_name AS author, udb.lu_name AS update_user_name FROM comment LEFT JOIN _login_user AS cdb ON cm_create_user_id = cdb.lu_id AND cdb.lu_deleted = false ';
//		$queryStr  = 'SELECT * FROM comment LEFT JOIN _login_user ON cm_create_user_id = lu_id AND lu_deleted = false ';
		$queryStr .=   'LEFT JOIN _login_user AS udb ON cm_update_user_id = udb.lu_id AND udb.lu_deleted = false ';
		$queryStr .=   'WHERE cm_serial = ? ';
		$ret = $this->selectRecord($queryStr, array($serial), $row);
		return $ret;
	}
	/**
	 * コメント項目の削除
	 *
	 * @param array   $serial		シリアルNo
	 * @return						true=成功、false=失敗
	 */
	function delCommentItem($serial)
	{
		// 引数のエラーチェック
		if (!is_array($serial)) return false;
		if (count($serial) <= 0) return true;
		
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $this->gEnv->getCurrentUserId();	// 現在のユーザ
		
		// トランザクション開始
		$this->startTransaction();
		
		for ($i = 0; $i < count($serial); $i++){
			$queryStr  = 'SELECT * FROM comment ';
			$queryStr .=   'WHERE cm_deleted = false ';		// 未削除
			$queryStr .=     'AND cm_serial = ? ';
			$ret = $this->selectRecord($queryStr, array($serial[$i]), $row);
			if ($ret){		// 既に登録レコードがあるとき			
				// レコードを削除
				$queryStr  = 'UPDATE comment ';
				$queryStr .=   'SET cm_deleted = true, ';	// 削除
				$queryStr .=     'cm_update_user_id = ?, ';
				$queryStr .=     'cm_update_dt = ? ';
				$queryStr .=   'WHERE cm_serial = ?';
				$this->execStatement($queryStr, array($userId, $now, $serial[$i]));
			} else {// 指定のシリアルNoのレコードが削除状態のときはエラー
				$this->endTransaction();
				return false;
			}
		}
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * コメントを取得(一般表示用)
	 *
	 * @param string $contentType			コンテンツタイプ
	 * @param string	$langId				言語
	 * @param string    $contentsId			共通コメントID
	 * @param int		$limit				取得する項目数
	 * @param int		$page				取得するページ(1～)
	 * @param int		$sortDirection		ソート方向(0=昇順、1=降順)
	 * @param bool      $authorizedOnly		承認済みコメントに制限するかどうか
	 * @param function	$callback			コールバック関数
	 * @return 			なし
	 */
	function getComment($contentType, $langId, $contentsId, $limit, $page, $sortDirection, $callback)
	{
		$offset = $limit * ($page -1);
		if ($offset < 0) $offset = 0;
		
		$params = array();
		$queryStr  = 'SELECT * FROM comment LEFT JOIN _login_user ON cm_create_user_id = lu_id AND lu_deleted = false ';
		$queryStr .=   'WHERE cm_language_id = ? '; $params[] = $langId;
		$queryStr .=     'AND cm_deleted = false ';		// 削除されていない
		$queryStr .=     'AND cm_content_type = ? '; $params[] = $contentType;
		$queryStr .=     'AND cm_contents_id = ? '; $params[] = $contentsId;
		
		if ($authorizedOnly){			// 公開可能なコメントのみ表示
			$queryStr .=     'AND cm_status = ? '; $params[] = 2;		// 公開
		} else {
			$queryStr .=     'AND cm_status != ? '; $params[] = 1;		// 非公開以外(未承認と公開)
		}
		$ord = '';
		if (!empty($sortDirection)) $ord = 'DESC ';
		$queryStr .=  'ORDER BY cm_no ' . $ord . 'LIMIT ' . $limit . ' OFFSET ' . $offset;
		$this->selectLoop($queryStr, $params, $callback);
	}
	
	/**
	 * コメント数を取得(一般表示用)
	 *
	 * @param string $contentType			コンテンツタイプ
	 * @param string $langId				言語
	 * @param string    $contentsId			共通コメントID
	 * @param bool   $authorizedOnly		承認済みコメントに制限するかどうか
	 * @return int							コメント数
	 */
	function getCommentCount($contentType, $langId, $contentsId, $authorizedOnly)
	{
		$params = array();
		$queryStr  = 'SELECT * FROM comment LEFT JOIN _login_user ON cm_create_user_id = lu_id AND lu_deleted = false ';
		$queryStr .=   'WHERE cm_language_id = ? '; $params[] = $langId;
		$queryStr .=     'AND cm_deleted = false ';		// 削除されていない
		$queryStr .=     'AND cm_content_type = ? '; $params[] = $contentType;
		$queryStr .=     'AND cm_contents_id = ? '; $params[] = $contentsId;
		
		if ($authorizedOnly){			// 公開可能なコメントのみ表示
			$queryStr .=     'AND cm_status = ? '; $params[] = 2;		// 公開
		} else {
			$queryStr .=     'AND cm_status != ? '; $params[] = 1;		// 非公開以外(未承認と公開)
		}
		return $this->selectRecordCount($queryStr, $params);
	}
	
	/**
	 * コンテンツ一覧を取得(管理用)
	 *
	 * @param string	$contentType		コンテンツタイプ
	 * @param string	$langId				言語
	 * @param int		$limit				取得する項目数
	 * @param int		$page				取得するページ(1～)
	 * @param function	$callback			コールバック関数
	 * @return 			なし
	 */
	function getContent($contentType, $langId, $limit, $page, $callback)
	{
		$offset = $limit * ($page -1);
		if ($offset < 0) $offset = 0;
		
		$params = array();
		$queryStr  = 'SELECT *, cn_id AS contents_id, cn_name AS content_title, cn_create_dt AS update_dt FROM content LEFT JOIN _login_user ON cn_create_user_id = lu_id AND lu_deleted = false ';
		$queryStr .=   'WHERE cn_deleted = false ';		// 削除されていない
		$queryStr .=     'AND cn_type = ? ';$params[] = $contentType;
		$queryStr .=     'AND cn_language_id = ? ';$params[] = $langId;
		$queryStr .=   'ORDER BY cn_create_dt DESC ';
		$queryStr .=   'LIMIT ' . $limit . ' OFFSET ' . $offset;
		$this->selectLoop($queryStr, $params, $callback);
	}
	/**
	 * コンテンツ数を取得(管理用)
	 *
	 * @param string	$contentType		コンテンツタイプ
	 * @param string	$langId				言語
	 * @return int							項目数
	 */
	function getContentCount($contentType, $langId)
	{
		$params = array();
		$queryStr  = 'SELECT * FROM content ';
		$queryStr .=   'WHERE cn_deleted = false ';		// 削除されていない
		$queryStr .=     'AND cn_type = ? ';$params[] = $contentType;
		$queryStr .=     'AND cn_language_id = ? ';$params[] = $langId;

		$itemCount = $this->selectRecordCount($queryStr, $params);
		return $itemCount;
	}
	/**
	 * コンテンツ項目をコンテンツIDで取得
	 *
	 * @param string  $contentType		コンテンツタイプ
	 * @param string	$langId			言語ID
	 * @param string	$contentId		コンテンツID
	 * @param array     $row			レコード
	 * @return bool						取得 = true, 取得なし= false
	 */
	function getContentById($contentType, $langId, $contentId, &$row)
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
	 * ブログ記事一覧を取得(管理用)
	 *
	 * @param string	$langId				言語
	 * @param int		$limit				取得する項目数
	 * @param int		$page				取得するページ(1～)
	 * @param function	$callback			コールバック関数
	 * @return 			なし
	 */
	function getEntry($langId, $limit, $page, $callback)
	{
		$offset = $limit * ($page -1);
		if ($offset < 0) $offset = 0;
		
		$params = array();
		$queryStr  = 'SELECT *, be_id AS contents_id, be_name AS content_title, be_create_dt AS update_dt FROM blog_entry LEFT JOIN _login_user ON be_create_user_id = lu_id AND lu_deleted = false ';
		$queryStr .=   'WHERE be_deleted = false ';		// 削除されていない
		$queryStr .=     'AND be_language_id = ? ';$params[] = $langId;
		$queryStr .=   'ORDER BY be_create_dt DESC ';
		$queryStr .=   'LIMIT ' . $limit . ' OFFSET ' . $offset;
		$this->selectLoop($queryStr, $params, $callback);
	}
	/**
	 * ブログ記事数を取得(管理用)
	 *
	 * @param string	$langId				言語
	 * @return int							項目数
	 */
	function getEntryCount($langId)
	{
		$params = array();
		$queryStr  = 'SELECT * FROM blog_entry LEFT JOIN _login_user ON be_create_user_id = lu_id AND lu_deleted = false ';
		$queryStr .=   'WHERE be_deleted = false ';		// 削除されていない
		$queryStr .=     'AND be_language_id = ? ';$params[] = $langId;
		return $this->selectRecordCount($queryStr, $params);
	}
	/**
	 * ブログ記事をエントリーIDで取得
	 *
	 * @param string	$id					エントリーID
	 * @param string	$langId				言語ID
	 * @param array     $row				レコード
	 * @return bool							取得 = true, 取得なし= false
	 */
	function getEntryById($id, $langId, &$row)
	{
		$queryStr  = 'SELECT * FROM blog_entry ';
		$queryStr .=   'WHERE be_deleted = false ';	// 削除されていない
		$queryStr .=   'AND be_id = ? ';
		$queryStr .=   'AND be_language_id = ? ';
		$ret = $this->selectRecord($queryStr, array($id, $langId), $row);
		return $ret;
	}
	/**
	 * 商品情報一覧を取得(管理用)
	 *
	 * @param string	$langId				言語
	 * @param int		$limit				取得する項目数
	 * @param int		$page				取得するページ(1～)
	 * @param function	$callback			コールバック関数
	 * @return 			なし
	 */
	function getProduct($langId, $limit, $page, $callback)
	{
		$offset = $limit * ($page -1);
		if ($offset < 0) $offset = 0;
		
		$params = array();
		$queryStr  = 'SELECT *, pt_id AS contents_id, pt_name AS content_title, pt_create_dt AS update_dt FROM product LEFT JOIN _login_user ON pt_create_user_id = lu_id AND lu_deleted = false ';
		$queryStr .=   'WHERE pt_deleted = false ';		// 削除されていない
		$queryStr .=     'AND pt_language_id = ? ';$params[] = $langId;
		$queryStr .=   'ORDER BY pt_create_dt DESC ';
		$queryStr .=   'LIMIT ' . $limit . ' OFFSET ' . $offset;
		$this->selectLoop($queryStr, $params, $callback);
	}
	/**
	 * 商品情報数を取得(管理用)
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
	function getProductById($id, $langId, &$row)
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
	 * イベント情報一覧を取得(管理用)
	 *
	 * @param string	$langId				言語
	 * @param int		$limit				取得する項目数
	 * @param int		$page				取得するページ(1～)
	 * @param function	$callback			コールバック関数
	 * @return 			なし
	 */
	function getEvent($langId, $limit, $page, $callback)
	{
		$offset = $limit * ($page -1);
		if ($offset < 0) $offset = 0;
		
		$params = array();
		$queryStr  = 'SELECT *, ee_id AS contents_id, ee_name AS content_title, ee_create_dt AS update_dt FROM event_entry LEFT JOIN _login_user ON ee_create_user_id = lu_id AND lu_deleted = false ';
		$queryStr .=   'WHERE ee_deleted = false ';		// 削除されていない
		$queryStr .=     'AND ee_language_id = ? ';$params[] = $langId;
		$queryStr .=   'ORDER BY ee_create_dt DESC ';
		$queryStr .=   'LIMIT ' . $limit . ' OFFSET ' . $offset;
		$this->selectLoop($queryStr, $params, $callback);
	}
	/**
	 * イベント情報数を取得(管理用)
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
	function getEventById($id, $langId, &$row)
	{
		$queryStr  = 'SELECT * FROM event_entry ';
		$queryStr .=   'WHERE ee_deleted = false ';	// 削除されていない
		$queryStr .=   'AND ee_id = ? ';
		$queryStr .=   'AND ee_language_id = ? ';
		$ret = $this->selectRecord($queryStr, array($id, $langId), $row);
		return $ret;
	}
	/**
	 * フォト情報一覧を取得(管理用)
	 *
	 * @param string	$langId				言語
	 * @param int		$limit				取得する項目数
	 * @param int		$page				取得するページ(1～)
	 * @param function	$callback			コールバック関数
	 * @return 			なし
	 */
	function getPhoto($langId, $limit, $page, $callback)
	{
		$offset = $limit * ($page -1);
		if ($offset < 0) $offset = 0;
		
		$params = array();
		$queryStr  = 'SELECT *, ht_public_id AS contents_id, ht_name AS content_title, ht_create_dt AS update_dt FROM photo LEFT JOIN _login_user ON ht_create_user_id = lu_id AND lu_deleted = false ';
		$queryStr .=   'WHERE ht_deleted = false ';
		$queryStr .=     'AND ht_language_id = ? '; $params[] = $langId;
		$queryStr .=   'ORDER BY ht_create_dt DESC ';
		$queryStr .=   'LIMIT ' . $limit . ' OFFSET ' . $offset;
		$this->selectLoop($queryStr, $params, $callback);
	}
	/**
	 * フォト情報を取得(管理用)
	 *
	 * @param string	$langId				言語
	 * @return int							項目数
	 */
	function getPhotoCount($langId)
	{
		$params = array();
		$queryStr  = 'SELECT * FROM photo LEFT JOIN _login_user ON ht_create_user_id = lu_id AND lu_deleted = false ';
		$queryStr .=   'WHERE ht_deleted = false ';
		$queryStr .=     'AND ht_language_id = ? '; $params[] = $langId;
		return $this->selectRecordCount($queryStr, $params);
	}
	/**
	 * フォト情報を取得
	 *
	 * @param int		$id					公開画像ID
	 * @param string	$langId				言語
	 * @param array     $row				レコード
	 * @return bool							取得 = true, 取得なし= false
	 */
	function getPhotoById($id, $langId, &$row)
	{
		$queryStr  = 'SELECT * FROM photo LEFT JOIN _login_user ON ht_owner_id = lu_id AND lu_deleted = false ';
		$queryStr .=   'WHERE ht_deleted = false ';
		$queryStr .=     'AND ht_public_id = ? ';
		$queryStr .=     'AND ht_language_id = ? ';
		$ret = $this->selectRecord($queryStr, array($id, $langId), $row);
		return $ret;
	}
	/**
	 * ルーム情報一覧を取得(管理用)
	 *
	 * @param string	$langId				言語
	 * @param int		$limit				取得する項目数
	 * @param int		$page				取得するページ(1～)
	 * @param function	$callback			コールバック関数
	 * @return 			なし
	 */
	function getRoom($langId, $limit, $page, $callback)
	{
		$offset = $limit * ($page -1);
		if ($offset < 0) $offset = 0;
		
		$params = array();
		$queryStr  = 'SELECT *, ur_id AS contents_id, ur_name AS content_title, ur_create_dt AS update_dt FROM user_content_room LEFT JOIN _login_user ON ur_create_user_id = lu_id AND lu_deleted = false ';
		$queryStr .=   'WHERE ur_deleted = false ';		// 削除されていない
		$queryStr .=   'ORDER BY ur_create_dt DESC ';
		$queryStr .=   'LIMIT ' . $limit . ' OFFSET ' . $offset;
		$this->selectLoop($queryStr, $params, $callback);
	}
	/**
	 * ルーム情報数を取得(管理用)
	 *
	 * @param string	$langId				言語
	 * @return int							項目数
	 */
	function getRoomCount($langId)
	{
		$params = array();
		$queryStr  = 'SELECT * FROM user_content_room LEFT JOIN _login_user ON ur_create_user_id = lu_id AND lu_deleted = false ';
		$queryStr .=   'WHERE ur_deleted = false ';		// 削除されていない
		return $this->selectRecordCount($queryStr, $params);
	}
	/**
	 * ルーム情報を識別IDで取得
	 *
	 * @param string	$id					識別ID
	 * @param string	$langId				言語
	 * @param array     $row				レコード
	 * @return bool							取得 = true, 取得なし= false
	 */
	function getRoomById($id, $langId, &$row)
	{
		$queryStr  = 'SELECT * FROM user_content_room ';
		$queryStr .=   'WHERE ur_deleted = false ';
		$queryStr .=   'AND ur_id = ? ';
		$ret = $this->selectRecord($queryStr, array($id), $row);
		return $ret;
	}
	/**
	 * Wiki情報一覧を取得(管理用)
	 *
	 * @param string	$langId				言語
	 * @param int		$limit				取得する項目数
	 * @param int		$page				取得するページ(1～)
	 * @param function	$callback			コールバック関数
	 * @return 			なし
	 */
	function getWiki($langId, $limit, $page, $callback)
	{
		$offset = $limit * ($page -1);
		if ($offset < 0) $offset = 0;
		
		$type = '';		// Wikiコンテンツタイプ
		$params = array();
		$queryStr  = 'SELECT *, wc_id AS contents_id, wc_id AS content_title, wc_create_dt AS update_dt FROM wiki_content LEFT JOIN _login_user ON wc_create_user_id = lu_id AND lu_deleted = false ';
		$queryStr .=   'WHERE wc_deleted = false ';		// 削除されていない
		$queryStr .=     'AND wc_type = ? ';$params[] = $type;
		$queryStr .=   'ORDER BY wc_create_dt DESC ';
		$queryStr .=   'LIMIT ' . $limit . ' OFFSET ' . $offset;
		$this->selectLoop($queryStr, $params, $callback);
	}
	/**
	 * Wiki情報数を取得(管理用)
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
	 * コメントの新規追加
	 *
	 * @param int $addType			追加タイプ(0=フラット、1=ツリー)
	 * @param string $contentType	コンテンツタイプ
	 * @param string $langId		言語
	 * @param string  $contentsId	共通コメントID
	 * @param int  $deviceType		デバイスタイプ(0=PC、1=携帯、2=スマートフォン)
	 * @param int  $parentSerial	親コメントシリアル番号
	 * @param string  $title		題名
	 * @param string  $message		コメントメッセージ
	 * @param string  $url			URL
	 * @param string  $author		ユーザ名
	 * @param string  $email		Eメール
	 * @param int $status			状態(0=未設定、1=非公開、2=公開)
	 * @param int     $newSerial	新規シリアル番号
	 * @return bool					true = 成功、false = 失敗
	 */
	function addComment($addType, $contentType, $langId, $contentsId, $deviceType, $parentSerial, $title, $message, $url, $author, $email, $status, &$newSerial)
	{
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $this->gEnv->getCurrentUserId();	// 現在のユーザ
		$nestLevel = 0;
		if (!empty($userId)){		// ログイン中の場合
			$author = '';
		}
		
		// トランザクション開始
		$this->startTransaction();
		
		// コメントNoを決定する
		$params = array();
		$queryStr  = 'SELECT MAX(cm_no) AS mid FROM comment ';
		$queryStr .=   'WHERE cm_content_type = ? '; $params[] = $contentType;
		$queryStr .=     'AND cm_contents_id = ? '; $params[] = $contentsId;
		$ret = $this->selectRecord($queryStr, $params, $row);
		if ($ret){
			$commentNo = $row['mid'] + 1;
		} else {
			$commentNo = 1;
		}
		
		// 親コメントがある場合は情報を取得
		if (!empty($parentSerial)){
			$queryStr  = 'SELECT * FROM comment ';
			$queryStr .=   'WHERE cm_serial = ? ';
			$ret = $this->selectRecord($queryStr, array(intval($parentSerial)), $row);
			if ($ret){
				$nestLevel = $row['cm_nest_level'] + 1;
			}
		}
		// 表示順を作成
		if (empty($addType)){		// 最後に追加
			$sortOrder = $commentNo;
		} else {		// レスポンス先のコメントの最後に追加
		}
		// コメントを追加
		$queryStr  = 'INSERT INTO comment ';
		$queryStr .=   '(cm_content_type, cm_contents_id, cm_device_type, cm_language_id, cm_parent_serial, cm_no, cm_sort_order, cm_nest_level, cm_title, cm_message, cm_url, cm_author, cm_email, cm_status, cm_create_user_id, cm_create_dt) ';
		$queryStr .=   'VALUES ';
		$queryStr .=   '(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
		$this->execStatement($queryStr, array($contentType, $contentsId, $deviceType, $langId, $parentSerial, $commentNo, $sortOrder, $nestLevel, $title, $message, $url, $author, $email, $status, $userId, $now));
		
		// 新規のシリアル番号取得
		$newSerial = 0;
		$queryStr = 'SELECT MAX(cm_serial) AS ns FROM comment ';
		$ret = $this->selectRecord($queryStr, array(), $row);
		if ($ret) $newSerial = $row['ns'];
			
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
}
?>
