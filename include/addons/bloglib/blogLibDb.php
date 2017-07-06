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

class blogLibDb extends BaseDb
{
	/**
	 * エントリー項目を取得
	 *
	 * @param int		$id					エントリーID
	 * @param string	$langId				言語
	 * @param array     $row				レコード
	 * @return bool							取得 = true, 取得なし= false
	 */
	function getEntryItem($id, $langId, &$row)
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
	 * ブログ情報を識別IDで取得(管理用)
	 *
	 * @param string	$id					識別ID
	 * @param array     $row				レコード
	 * @return bool							取得 = true, 取得なし= false
	 */
	function getBlogInfoById($id, &$row)
	{
		$queryStr = 'SELECT * FROM blog_id ';
		$queryStr .=  'WHERE bl_deleted = false ';
		$queryStr .=  'AND bl_id = ? ';
		$ret = $this->selectRecord($queryStr, array($id), $row);
		return $ret;
	}
	/**
	 * ブログ定義値を取得をすべて取得
	 *
	 * @param array  $rows			レコード
	 * @return bool					1行以上取得 = true, 取得なし= false
	 */
	function getAllConfig(&$rows)
	{
		$queryStr  = 'SELECT * FROM blog_config ';
		$queryStr .=   'ORDER BY bg_index';
		$retValue = $this->selectRecords($queryStr, array(), $rows);
		return $retValue;
	}
	/**
	 * 予約記事状態の更新
	 *
	 * @param int  $serial		シリアル番号
	 * @param int  $status			記事状態
	 * @return bool					true = 成功、false = 失敗
	 */
	function updateScheduleEntryStatus($serial, $status)
	{
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $this->gEnv->getCurrentUserId();	// 現在のユーザ

		// トランザクション開始
		$this->startTransaction();
		
		// 指定のシリアルNoのレコードが削除状態でないかチェック
		$queryStr  = 'SELECT * FROM blog_entry ';
		$queryStr .=   'WHERE be_serial = ? ';
		$ret = $this->selectRecord($queryStr, array(intval($serial)), $row);
		if ($ret){		// 既に登録レコードがあるとき
			if ($row['be_deleted']){		// レコードが削除されていれば終了
				$this->endTransaction();
				return false;
			}
		} else {		// 存在しない場合は終了
			$this->endTransaction();
			return false;
		}
		// 日付を更新
		$queryStr  = 'UPDATE blog_entry ';
		$queryStr .=   'SET be_status = ?, ';	// 記事状態
		$queryStr .=     'be_update_user_id = ?, ';
		$queryStr .=     'be_update_dt = ? ';
		$queryStr .=   'WHERE be_serial = ?';
		$this->execStatement($queryStr, array($status, $userId, $now, intval($serial)));
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * 予約中のブログ予約記事を取得
	 *
	 * @param function	$callback			コールバック関数
	 * @return 			なし
	 */
	function getEntryScheduleInActive($callback)
	{
		$now = date("Y/m/d H:i:s");	// 現在日時
		
		$params = array();
		$queryStr = 'SELECT * FROM blog_entry ';
		$queryStr .=  'WHERE be_deleted = false ';
		$queryStr .=    'AND be_status = ? '; $params[] = 2;			// 予約状態(実行)
		$queryStr .=    'AND be_history_index <= -1000 ';		// 予約(Scheduled)記事を対象
		$queryStr .=    'AND be_active_start_dt <= ? '; $params[] = $now;
		$queryStr .=  'ORDER BY be_active_start_dt, be_history_index ';
		$this->selectLoop($queryStr, $params, $callback);
	}
	/**
	 * 予約更新でブログ記事の更新
	 *
	 * @param int     $serial		シリアル番号
	 * @param array   $updateParams	更新値フィールド値
	 * @param int     $newSerial	新規シリアル番号
	 * @param array   $oldRecord	更新前の旧データ
	 * @return bool					true = 成功、false = 失敗
	 */
	function updateEntryItemBySchedule($serial, $updateParams, &$newSerial, &$oldRecord)
	{
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $this->gEnv->getCurrentUserId();	// 現在のユーザ
		$updateFields = array(						// 更新対象のDBフィールド名
							'be_name',				// エントリータイトル
							'be_html',				// エントリー本文HTML
							'be_html_ext',				// エントリー本文HTML(続き)
							'be_description',				// 概要
							'be_status',				// エントリー状態(0=未設定、1=編集中、2=公開、3=非公開)
							'be_search_tag',				// 検索用タグ(「,」区切り)
							'be_theme_id',				// ブログテーマID(廃止予定)
							'be_thumb_filename',				// サムネールファイル名(「;」区切り)
							'be_option_fields',				// 追加フィールド
							'be_related_content',				// 関連コンテンツID(「,」区切り)
							'be_show_comment',				// コメントを表示するかどうか
							'be_receive_comment',				// コメントの受け付け可否
							'be_user_limited',				// 参照ユーザを制限
							'be_blog_id',				// ブログID
							'be_regist_user_id',				// エントリー作者
							'be_regist_dt',				// 投稿日時
							'be_dt',				// ブログ記事更新日時
							'be_active_start_dt',				// 公開期間(開始)
							'be_active_end_dt',				// 公開期間(終了)
							'be_meta_description',				// METAタグ、ページ要約
							'be_meta_keywords',				// METAタグ、検索用キーワード
							'be_master_serial'				// 作成元レコードのシリアル番号
						);
						
		// トランザクション開始
		$this->startTransaction();
		
		// 指定のシリアルNoのレコードが削除状態でないかチェック
		$historyIndex = 0;		// 履歴番号
		$queryStr  = 'SELECT * FROM blog_entry ';
		$queryStr .=   'WHERE be_serial = ? ';
		$ret = $this->selectRecord($queryStr, array(intval($serial)), $row);
		if ($ret){		// 既に登録レコードがあるとき
			if ($row['be_deleted']){		// レコードが削除されていれば終了
				$this->endTransaction();
				return false;
			}
			$entryId = $row['be_id'];
			$langId = $row['be_language_id'];
			$historyIndex = $row['be_history_index'] + 1;
			
			$oldRecord = $row;			// 旧データ
		} else {		// 存在しない場合は終了
			$this->endTransaction();
			return false;
		}
		// カテゴリーを取得
		$queryStr  = 'SELECT * FROM blog_entry_with_category LEFT JOIN blog_category ON bw_category_id = bc_id AND bc_deleted = false ';
		$queryStr .=   'WHERE bw_entry_serial = ? ';
		$queryStr .=  'ORDER BY bc_sort_order, bc_id ';		// カテゴリー並び順
		$this->selectRecords($queryStr, array(intval($serial)), $categoryRows);
		$category = array();
		for ($i = 0; $i < count($categoryRows); $i++){
			$category[] = $categoryRows[$i]['bw_category_id'];
		}
			
		// 古いレコードを削除
		$queryStr  = 'UPDATE blog_entry ';
		$queryStr .=   'SET be_deleted = true, ';	// 削除
		$queryStr .=     'be_update_user_id = ?, ';
		$queryStr .=     'be_update_dt = ? ';
		$queryStr .=   'WHERE be_serial = ?';
		$this->execStatement($queryStr, array($userId, $now, intval($serial)));

		// 更新値を作成
		$updateFieldParams = array();
		for ($i = 0; $i < count($updateFields); $i++){
			$fieldName = $updateFields[$i];
			$updateFieldParams[$fieldName] = $row[$fieldName];
		}
		// 上書き値を設定
		$keys = array_keys($updateParams);
		for ($i = 0; $i < count($keys); $i++){
			$fieldName = $keys[$i];
			$fieldValue = $updateParams[$fieldName];
			$updateFieldParams[$fieldName] = $fieldValue;
		}
		
		// 新規レコード追加
		$params = array();
		$queryStr  = 'INSERT INTO blog_entry ';
		$queryStr .=   '(be_id, ';
		$queryStr .=   'be_language_id, ';
		$queryStr .=   'be_history_index, ';
		$queryStr .=   'be_create_user_id, ';
		$queryStr .=   'be_create_dt';
		
		// その他の更新値を設定
		$updateFieldParamsStr = '';
		$keys = array_keys($updateFieldParams);// キーを取得
		for ($i = 0; $i < count($keys); $i++){
			$fieldName = $keys[$i];
			$fieldValue = $updateFieldParams[$fieldName];
			if (!isset($fieldValue)) continue;
			$params[] = $fieldValue;
			$queryStr .= ', ' . $fieldName;
			$updateFieldParamsStr .= ', ?';
		}
		
		$queryStr .=   ') VALUES ';
		$queryStr .=   '(?, ?, ?, ?, ?' . $updateFieldParamsStr . ')';
		$this->execStatement($queryStr, array_merge(array($entryId, $langId, $historyIndex, $userId, $now), $params));

		// 新規のシリアル番号取得
		$newSerial = 0;
		$queryStr = 'SELECT MAX(be_serial) AS ns FROM blog_entry ';
		$ret = $this->selectRecord($queryStr, array(), $row);
		if ($ret) $newSerial = $row['ns'];
		
		// 記事カテゴリーの更新
		for ($i = 0; $i < count($category); $i++){
			$ret = $this->updateEntryCategory($newSerial, $i, $category[$i]);
			if (!$ret){
				$this->endTransaction();
				return false;
			}
		}
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * 記事カテゴリーの更新
	 *
	 * @param int        $serial		記事シリアル番号
	 * @param int        $index			インデックス番号
	 * @param int        $categoryId	カテゴリーID
	 * @return bool		 true = 成功、false = 失敗
	 */
	function updateEntryCategory($serial, $index, $categoryId)
	{
		// 新規レコード追加
		$queryStr = 'INSERT INTO blog_entry_with_category ';
		$queryStr .=  '(';
		$queryStr .=  'bw_entry_serial, ';
		$queryStr .=  'bw_index, ';
		$queryStr .=  'bw_category_id) ';
		$queryStr .=  'VALUES ';
		$queryStr .=  '(?, ?, ?)';
		$ret =$this->execStatement($queryStr, array(intval($serial), $index, $categoryId));
		return $ret;
	}
	/**
	 * 公開中のエントリー項目を取得。アクセス制限も行う。
	 *
	 * @param int		$limit				取得する項目数
	 * @param int		$page				取得するページ(1～)
	 * @param int,array	$entryId			エントリーID(0のときは期間で取得)
	 * @param timestamp $now				現在日時(現在日時より未来の投稿日時の記事は取得しない)
	 * @param timestamp	$startDt			期間(開始日)
	 * @param timestamp	$endDt				期間(終了日)
	 * @param string,array	$keywords		検索キーワード
	 * @param string	$langId				言語
	 * @param int		$order				取得順(0=昇順,1=降順)
	 * @param int       $userId				参照制限用ユーザID
	 * @param function	$callback			コールバック関数
	 * @param int		$categoryId			カテゴリーID(nullのとき指定なし)
	 * @param string	$blogId				ブログID(nullのとき指定なし)
	 * @return 			なし
	 */
	function getPublicEntryItems($limit, $page, $entryId, $now, $startDt, $endDt, $keywords, $langId, $order, $userId, $callback, $categoryId = null, $blogId = null)
	{
		$offset = $limit * ($page -1);
		if ($offset < 0) $offset = 0;
		$initDt = $this->gEnv->getInitValueOfTimestamp();		// 日時初期化値
		$params = array();
		
		$queryStr  = 'SELECT * FROM blog_entry LEFT JOIN blog_id ON be_blog_id = bl_id AND bl_deleted = false ';
		$queryStr .=   'LEFT JOIN _login_user ON be_regist_user_id = lu_id AND lu_deleted = false ';
		$queryStr .=   'WHERE be_deleted = false ';		// 削除されていない
		$queryStr .=     'AND be_history_index >= 0 ';		// 正規(Regular)記事を対象
		$queryStr .=     'AND be_language_id = ? ';	$params[] = $langId;
		
		// ##### IDで取得コンテンツを指定 #####
		if (!empty($entryId)){
			if (is_array($entryId)){		// 配列で複数指定の場合
				$queryStr .=    'AND be_id in (' . implode(",", $entryId) . ') ';
			} else {
				$queryStr .=     'AND be_id = ? ';		$params[] = $entryId;
			}
		}
		
		// ##### 任意設定の検索条件 #####
		list($condQueryStr, $condParams) = $this->_createPublicSearchCondition($now, $startDt, $endDt, $keywords, $userId, $categoryId, $blogId);
		$queryStr .= $condQueryStr;
		$params = array_merge($params, $condParams);

		if (empty($entryId)){
			$ord = '';
			if (!empty($order)) $ord = 'DESC ';
			$queryStr .=  'ORDER BY be_regist_dt ' . $ord . 'LIMIT ' . $limit . ' offset ' . $offset;// 投稿順
		}
		$this->selectLoop($queryStr, $params, $callback);
	}
	/**
	 * 公開中のエントリー項目数を取得
	 *
	 * @param timestamp $now				現在日時(現在日時より未来の投稿日時の記事は取得しない)
	 * @param timestamp	$startDt			期間(開始日)
	 * @param timestamp	$endDt				期間(終了日)
	 * @param string,array	$keywords		検索キーワード
	 * @param string	$langId				言語
	 * @param int       $userId				参照制限用ユーザID
	 * @param int		$categoryId			カテゴリーID(nullのとき指定なし)
	 * @param string	$blogId				ブログID(nullのとき指定なし)
	 * @return int							項目数
	 */
	function getPublicEntryItemsCount($now, $startDt, $endDt, $keywords, $langId, $userId, $categoryId = null, $blogId = null)
	{
		$initDt = $this->gEnv->getInitValueOfTimestamp();		// 日時初期化値
		$params = array();
		
		$queryStr = 'SELECT * FROM blog_entry LEFT JOIN blog_id ON be_blog_id = bl_id AND bl_deleted = false ';
		$queryStr .=  'WHERE be_deleted = false ';		// 削除されていない
		$queryStr .=    'AND be_history_index >= 0 ';		// 正規(Regular)記事を対象
		$queryStr .=    'AND be_language_id = ? ';	$params[] = $langId;
		
		// ##### 任意設定の検索条件 #####
		list($condQueryStr, $condParams) = $this->_createPublicSearchCondition($now, $startDt, $endDt, $keywords, $userId, $categoryId, $blogId);
		$queryStr .= $condQueryStr;
		$params = array_merge($params, $condParams);

		return $this->selectRecordCount($queryStr, $params);
	}
	/**
	 * 公開中のエントリー項目の検索条件を作成
	 *
	 * @param timestamp $now				現在日時
	 * @param timestamp	$startDt			期間(開始日)
	 * @param timestamp	$endDt				期間(終了日)
	 * @param string,array	$keywords		検索キーワード
	 * @param int       $userId				ユーザID
	 * @param int		$categoryId			カテゴリーID(nullのとき指定なし)
	 * @param string	$blogId				ブログID(nullのとき指定なし)
	 * @return array						クエリー文字列と配列パラメータの連想配列
	 */
	function _createPublicSearchCondition($now, $startDt, $endDt, $keywords, $userId, $categoryId = null, $blogId = null)
	{
		$initDt = $this->gEnv->getInitValueOfTimestamp();		// 日時初期化値
		$queryStr = '';
		$params = array();
	
		// ##### 検索条件 #####
		// 取得期間
		if (!empty($startDt)){
			$queryStr .=    'AND ? <= be_regist_dt ';
			$params[] = $startDt;
		}
		if (!empty($endDt)){
			$queryStr .=    'AND be_regist_dt < ? ';
			$params[] = $endDt;
		}
		
		// タイトルと記事、ユーザ定義フィールドを検索
		if (!empty($keywords)){
			if (is_string($keywords)) $keywords = array($keywords);
			
			for ($i = 0; $i < count($keywords); $i++){
				$keyword = addslashes($keywords[$i]);// 「'"\」文字をエスケープ
				$queryStr .=    'AND (be_name LIKE \'%' . $keyword . '%\' ';
				$queryStr .=    'OR be_html LIKE \'%' . $keyword . '%\' ';
				$queryStr .=    'OR be_html_ext LIKE \'%' . $keyword . '%\' ';
				$queryStr .=    'OR be_description LIKE \'%' . $keyword . '%\' ';
				$queryStr .=    'OR be_option_fields LIKE \'%' . $keyword . '%\') ';	// ユーザ定義フィールド
			}
		}
		
		// ブログID
		if (isset($blogId)){
			$queryStr .=    'AND be_blog_id = ? ';
			$params[] = $blogId;
		}
		
		// カテゴリー
		if (isset($categoryId)){
			$queryStr .=     'AND bw_category_id = ? ';
			$params[] = $categoryId;// 記事カテゴリー
		}

		// ##### コンテンツ参照制限 #####
		// 公開状態
		$queryStr .=     'AND be_status = ? ';		$params[] = 2;	// 「公開」(2)データを表示
		$queryStr .=     'AND be_regist_dt <= ? ';	$params[] = $now;		// 投稿日時が現在日時よりも過去のものを取得
	
		// 公開期間
		$queryStr .=    'AND (be_active_start_dt = ? OR (be_active_start_dt != ? AND be_active_start_dt <= ?)) ';
		$queryStr .=    'AND (be_active_end_dt = ? OR (be_active_end_dt != ? AND be_active_end_dt > ?)) ';
		$params[] = $initDt;
		$params[] = $initDt;
		$params[] = $now;
		$params[] = $initDt;
		$params[] = $initDt;
		$params[] = $now;
		
		// ##### ユーザ参照制限 #####
		// ゲストユーザはユーザ制限のない記事のみ参照可能
		if (empty($userId)){
			$queryStr .= 'AND be_user_limited = false ';		// ユーザ制限のないデータ
		}
		// ブログごとのユーザ参照制限
		$queryStr .=     'AND (be_blog_id = \'\' ';
		$queryStr .=     'OR (be_blog_id != \'\' ';
		$queryStr .=     'AND ((bl_owner_id = ? AND bl_owner_id != 0) ';	$params[] = $userId;
		$queryStr .=     'OR bl_user_limited = false ';
		$queryStr .=     'OR (bl_user_limited = true AND bl_limited_user_id = \'\' AND 0 != ' . $userId . ') ';
		$queryStr .=     'OR (bl_user_limited = true AND bl_limited_user_id != \'\' AND bl_limited_user_id LIKE \'%' . M3_USER_ID_SEPARATOR . $userId . M3_USER_ID_SEPARATOR . '%\')))) ';
		
		return array($queryStr, $params);
	}
	/**
	 * 公開中のエントリーの前後のエントリー項目を取得
	 *
	 * @param int       $type				前後記事のタイプ(0=前方,1=後方)
	 * @param timestamp $regDate			基準となる記事の登録日時
	 * @param timestamp $now				現在日時(現在日時より未来の投稿日時の記事は取得しない)
	 * @param timestamp	$startDt			期間(開始日)
	 * @param timestamp	$endDt				期間(終了日)
	 * @param string,array	$keywords		検索キーワード
	 * @param string	$langId				言語
	 * @param int		$order				取得順(0=昇順,1=降順)
	 * @param int       $userId				参照制限用ユーザID
	 * @param int		$categoryId			カテゴリーID(nullのとき指定なし)
	 * @param string	$blogId				ブログID(nullのとき指定なし)
	 * @return array 						記事のレコード。取得なしの場合はfalseを返す。
	 */
	function getPublicPrevNextEntry($type, $regDate, $now, $startDt, $endDt, $keywords, $langId, $order, $userId, $categoryId = null, $blogId = null)
	{
		$retStatus = false;
					
		if ($regDate == $this->gEnv->getInitValueOfTimestamp()){
			return false;
		} else {
			$isPrev = true;		// 前方データを取得するかどうか
			$params = array();
			$queryStr  = 'SELECT distinct(be_serial) FROM blog_entry LEFT JOIN blog_id ON be_blog_id = bl_id AND bl_deleted = false ';
			$queryStr .=   'RIGHT JOIN blog_entry_with_category ON be_serial = bw_entry_serial ';
			$queryStr .=   'WHERE be_deleted = false ';	// 削除されていない
			$queryStr .=     'AND be_history_index >= 0 ';		// 正規(Regular)記事を対象
			$queryStr .=     'AND be_language_id = ? ';	$params[] = $langId;
			
			// ソート順を決める
			if ($type == 1) $isPrev = false;
			if ($order == 1) $isPrev = !$isPrev;
			
			if ($isPrev){			// 前の記事を取得する場合
				$queryStr .=     'AND be_regist_dt < ? '; $params[] = $regDate;		// ***** 登録日時で前後を取得 *****
			} else {
				$queryStr .=     'AND ? < be_regist_dt '; $params[] = $regDate;		// ***** 登録日時で前後を取得 *****
			}
		
			// 検索条件を付加
			list($condQueryStr, $condParams) = $this->_createPublicSearchCondition($now, $startDt, $endDt, $keywords, $userId, $categoryId, $blogId);
			$queryStr .= $condQueryStr;
			$params = array_merge($params, $condParams);
		
			// シリアル番号を取得
			$serialArray = array();
			$ret = $this->selectRecords($queryStr, $params, $serialRows);
			if ($ret){
				for ($i = 0; $i < count($serialRows); $i++){
					$serialArray[] = $serialRows[$i]['be_serial'];
				}
			}
			$serialStr = implode(',', $serialArray);
			if (empty($serialStr)) $serialStr = '0';	// 0レコードのときはダミー値を設定
	
			$queryStr  = 'SELECT * FROM blog_entry LEFT JOIN blog_id ON be_blog_id = bl_id AND bl_deleted = false ';
			$queryStr .=   'LEFT JOIN _login_user ON be_regist_user_id = lu_id AND lu_deleted = false ';
			$queryStr .=   'WHERE be_serial in (' . $serialStr . ') ';
		
			$ord = '';
			if ($isPrev) $ord = 'DESC ';
			$queryStr .=   'ORDER BY be_regist_dt ' . $ord . 'LIMIT 1';// 投稿順
			$ret = $this->selectRecord($queryStr, $params, $row);
			if ($ret) $retStatus = $row;
		}
		return $retStatus;
	}
	/**
	 * 記事に関連したカテゴリーを取得
	 *
	 * @param int,array		$serial			シリアル番号
	 * @return array						取得レコード、false=取得なし
	 */
	function getContentCategoryBySerial($serial)
	{
		if (!is_array($serial)) $serial = array($serial);
		
		// CASE文作成
		$caseStr = 'CASE be_serial ';
		for ($i = 0; $i < count($serial); $i++){
			$caseStr .= 'WHEN ' . $serial[$i] . ' THEN ' . $i . ' ';
		}
		$caseStr .= 'END AS no';
		
		// シリアル番号に対応したカテゴリーをすべて取得
		$queryStr  = 'SELECT *, ' . $caseStr . ' FROM blog_entry RIGHT JOIN blog_entry_with_category ON be_serial = bw_entry_serial LEFT JOIN blog_category ON bw_category_id = bc_id AND bc_deleted = false ';
		$queryStr .=   'WHERE be_serial in (' . implode(",", $serial) . ') ';
		$queryStr .=   'ORDER BY no, bc_sort_order, bc_id ';		// カテゴリー並び順
		$ret = $this->selectRecords($queryStr, array(), $rows);
		if ($ret) $ret = $rows;
		return $ret;
	}
}
?>
