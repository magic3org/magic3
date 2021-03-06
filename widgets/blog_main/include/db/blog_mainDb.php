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
 * @copyright  Copyright 2006-2019 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getDbPath() . '/baseDb.php');

class blog_mainDb extends BaseDb
{
	const INIT_HISTORY_INDEX_FOR_SCHEDULE = -1000;			// 予約記事用の履歴番号初期値
	
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
	 * ブログ定義値を取得
	 *
	 * @param string $key		キーとなる項目値
	 * @return string $value	値
	 */
	function getConfig($key)
	{
		$retValue = '';
		$queryStr = 'SELECT bg_value FROM blog_config ';
		$queryStr .=  'WHERE bg_id  = ?';
		$ret = $this->selectRecord($queryStr, array($key), $row);
		if ($ret) $retValue = $row['bg_value'];
		return $retValue;
	}
	/**
	 * ブログ定義値を更新
	 *
	 * @param string $key		キーとなる項目値
	 * @param string $value		値
	 * @return					true = 正常、false=異常
	 */
	function updateConfig($key, $value)
	{
		// データの確認
		$queryStr = 'SELECT bg_value FROM blog_config ';
		$queryStr .=  'WHERE bg_id  = ?';
		$ret = $this->isRecordExists($queryStr, array($key));
		if ($ret){
			$queryStr = "UPDATE blog_config SET bg_value = ? WHERE bg_id = ?";
			return $this->execStatement($queryStr, array($value, $key));
		} else {
			$queryStr = "INSERT INTO blog_config (bg_id, bg_value) VALUES (?, ?)";
			return $this->execStatement($queryStr, array($key, $value));
		}
	}
	/**
	 * エントリー項目一覧を取得(管理用)
	 *
	 * @param int		$limit				取得する項目数
	 * @param int		$page				取得するページ(1～)
	 * @param timestamp	$startDt			期間(開始日)
	 * @param timestamp	$endDt				期間(終了日)
	 * @param array		$category			カテゴリーID
	 * @param string	$keyword			検索キーワード
	 * @param string	$langId				言語
	 * @param string	$blogId				ブログID
	 * @param  array    $rows				取得した行
	 * @return bool							true=データあり, false=データなし
	 */
	function searchEntryItems($limit, $page, $startDt, $endDt, $category, $keyword, $langId, $blogId, &$rows)
	{
		$offset = $limit * ($page -1);
		if ($offset < 0) $offset = 0;
		
		$params = array();
		if (count($category) == 0){		// カテゴリー指定なしのとき
			$queryStr = 'SELECT * FROM blog_entry LEFT JOIN _login_user ON be_regist_user_id = lu_id AND lu_deleted = false ';
			$queryStr .=  'WHERE be_language_id = ? '; $params[] = $langId;
			$queryStr .=    'AND be_deleted = false ';		// 削除されていない
			$queryStr .=    'AND be_history_index >= 0 ';		// 正規(Regular)記事を対象
		} else {
			$queryStr = 'SELECT distinct(be_serial) FROM blog_entry RIGHT JOIN blog_entry_with_category ON be_serial = bw_entry_serial ';
			$queryStr .=  'WHERE be_language_id = ? '; $params[] = $langId;
			$queryStr .=    'AND be_deleted = false ';		// 削除されていない
			$queryStr .=    'AND be_history_index >= 0 ';		// 正規(Regular)記事を対象
			
			// 記事カテゴリー
			$queryStr .=    'AND bw_category_id in (' . implode(",", $category) . ') ';
		}
		// タイトル、本文、説明、ユーザ定義フィールドを検索
		if (!empty($keyword)){
			// 「'"\」文字をエスケープ
			$keyword = addslashes($keyword);
			
			$queryStr .=    'AND (be_name LIKE \'%' . $keyword . '%\' ';
			$queryStr .=    'OR be_html LIKE \'%' . $keyword . '%\' ';
			$queryStr .=    'OR be_html_ext LIKE \'%' . $keyword . '%\' ';
			$queryStr .=    'OR be_description LIKE \'%' . $keyword . '%\' ';
			$queryStr .=    'OR be_option_fields LIKE \'%' . $keyword . '%\') ';	// ユーザ定義フィールド
		}
		
		// 日付範囲
		if (!empty($startDt)){
			$queryStr .=    'AND ? <= be_regist_dt ';
			$params[] = $startDt;
		}
		if (!empty($endDt)){
			$queryStr .=    'AND be_regist_dt < ? ';
			$params[] = $endDt;
		}
		// ブログID
		if (isset($blogId)){
			$queryStr .=    'AND be_blog_id = ? ';
			$params[] = $blogId;
		}
		
		if (count($category) == 0){
			$queryStr .=  'ORDER BY be_regist_dt desc limit ' . $limit . ' offset ' . $offset;
			//$this->selectLoop($queryStr, $params, $callback, null);
			$ret = $this->selectRecords($queryStr, $params, $rows);
		} else {
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
		
			$queryStr = 'SELECT * FROM blog_entry LEFT JOIN _login_user ON be_regist_user_id = lu_id AND lu_deleted = false ';
			$queryStr .=  'WHERE be_serial in (' . $serialStr . ') ';
			$queryStr .=  'ORDER BY be_regist_dt desc limit ' . $limit . ' offset ' . $offset;
			//$this->selectLoop($queryStr, array(), $callback, null);
			$ret = $this->selectRecords($queryStr, array(), $rows);
		}
		return $ret;
	}
	/**
	 * エントリー項目数を取得(管理用)
	 *
	 * @param timestamp	$startDt			期間(開始日)
	 * @param timestamp	$endDt				期間(終了日)
	 * @param array		$category			カテゴリーID
	 * @param string	$keyword			検索キーワード
	 * @param string	$langId				言語
	 * @param string	$blogId				ブログID(未指定の場合はnull)
	 * @return int							項目数
	 */
	function getEntryItemCount($startDt, $endDt, $category, $keyword, $langId, $blogId = null)
	{
		$params = array();
		if (count($category) == 0){		// カテゴリー指定なしのとき
			$queryStr = 'SELECT * FROM blog_entry LEFT JOIN _login_user ON be_regist_user_id = lu_id AND lu_deleted = false ';
			$queryStr .=  'WHERE be_language_id = ? '; $params[] = $langId;
			$queryStr .=    'AND be_deleted = false ';		// 削除されていない
			$queryStr .=    'AND be_history_index >= 0 ';		// 正規(Regular)記事を対象
		} else {
			$queryStr = 'SELECT distinct(be_serial) FROM blog_entry RIGHT JOIN blog_entry_with_category ON be_serial = bw_entry_serial ';
			$queryStr .=  'WHERE be_language_id = ? '; $params[] = $langId;
			$queryStr .=    'AND be_deleted = false ';		// 削除されていない
			$queryStr .=    'AND be_history_index >= 0 ';		// 正規(Regular)記事を対象
			
			// 記事カテゴリー
			$queryStr .=    'AND bw_category_id in (' . implode(",", $category) . ') ';
		}
		// タイトル、本文、説明、ユーザ定義フィールドを検索
		if (!empty($keyword)){
			// 「'"\」文字をエスケープ
			$keyword = addslashes($keyword);
			
			$queryStr .=    'AND (be_name LIKE \'%' . $keyword . '%\' ';
			$queryStr .=    'OR be_html LIKE \'%' . $keyword . '%\' ';
			$queryStr .=    'OR be_html_ext LIKE \'%' . $keyword . '%\' ';
			$queryStr .=    'OR be_description LIKE \'%' . $keyword . '%\' ';
			$queryStr .=    'OR be_option_fields LIKE \'%' . $keyword . '%\') ';	// ユーザ定義フィールド
		}
		
		// 日付範囲
		if (!empty($startDt)){
			$queryStr .=    'AND ? <= be_regist_dt ';
			$params[] = $startDt;
		}
		if (!empty($endDt)){
			$queryStr .=    'AND be_regist_dt < ? ';
			$params[] = $endDt;
		}
		// ブログID
		if (isset($blogId)){
			$queryStr .=    'AND be_blog_id = ? ';
			$params[] = $blogId;
		}
		
		if (count($category) == 0){
			return $this->selectRecordCount($queryStr, $params);
		} else {
			// シリアル番号を取得
			$serialArray = array();
			$ret = $this->selectRecords($queryStr, $params, $serialRows);
			if ($ret){
				for ($i = 0; $i < count($serialRows); $i++){
					$serialArray[] = $serialRows[$i]['be_serial'];
				}
			}
			$serialStr = implode(',', $serialArray);
			if (empty($serialStr)) $serialStr = '0';		// 0レコードのときはダミー値を設定

			$queryStr = 'SELECT * FROM blog_entry LEFT JOIN _login_user ON be_regist_user_id = lu_id AND lu_deleted = false ';
			$queryStr .=  'WHERE be_serial in (' . $serialStr . ') ';
			return $this->selectRecordCount($queryStr, array());
		}
	}
	/**
	 * 次のエントリーIDを取得
	 *
	 * @return int							エントリーID
	 */
	function getNextEntryId()
	{
		// エントリーIDを決定する
		$queryStr = 'SELECT MAX(be_id) AS mid FROM blog_entry ';
		$ret = $this->selectRecord($queryStr, array(), $row);
		if ($ret){
			$entryId = $row['mid'] + 1;
		} else {
			$entryId = 1;
		}
		return $entryId;
	}
	/**
	 * エントリー項目の新規追加
	 *
	 * @param string  $id			エントリーID(0以下のときはエントリーIDを新規取得)
	 * @param string  $langId		言語ID
	 * @param string  $name			コンテンツ名
	 * @param string  $html			HTML
	 * @param string  $html2		HTML(続き)
	 * @param int     $status		エントリー状態(0=未設定、1=編集中、2=公開、3=非公開)
	 * @param array   $category		カテゴリーID
	 * @param array   $images		記事画像
	 * @param string  $blogId		ブログID
	 * @param int     $regUserId	投稿者ユーザID
	 * @param timestamp $regDt		投稿日時
	 * @param timestamp	$startDt	期間(開始日)
	 * @param timestamp	$endDt		期間(終了日)
	 * @param bool    $showComment	コメントを表示するかどうか
	 * @param bool $receiveComment	コメントを受け付けるかどうか
	 * @param int     $newSerial	新規シリアル番号
	 * @param array   $otherParams	その他のフィールド値
	 * @return bool					true = 成功、false = 失敗
	 */
	function addEntryItem($id, $langId, $name, $html, $html2, $status, $category, $images, $blogId, $regUserId, $regDt, $startDt, $endDt, $showComment, $receiveComment, &$newSerial, $otherParams = null)
	{
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $this->gEnv->getCurrentUserId();	// 現在のユーザ

		// トランザクション開始
		$this->startTransaction();
		
		if (intval($id) <= 0){		// エントリーIDが0以下のときは、エントリーIDを新規取得
			// エントリーIDを決定する
			$queryStr = 'SELECT MAX(be_id) AS mid FROM blog_entry ';
			$ret = $this->selectRecord($queryStr, array(), $row);
			if ($ret){
				$entryId = $row['mid'] + 1;
			} else {
				$entryId = 1;
			}
			
			// 新規記事追加のときは記事IDが変更されていないかチェック
			if (intval($id) * (-1) != $entryId){
				$this->endTransaction();
				return false;
			}
		} else {
			$entryId = $id;
		}
		
		// 前レコードの削除状態チェック
		$historyIndex = 0;
		$queryStr  = 'SELECT * FROM blog_entry ';
		$queryStr .=   'WHERE be_id = ? ';
		$queryStr .=     'AND be_language_id = ? ';
		$queryStr .=     'AND be_history_index >= 0 ';		// 正規(Regular)記事を対象
		$queryStr .=   'ORDER BY be_history_index DESC ';
		$ret = $this->selectRecord($queryStr, array($entryId, $langId), $row);
		if ($ret){
			if (!$row['be_deleted']){		// レコード存在していれば終了
				$this->endTransaction();
				return false;
			}
			$historyIndex = $row['be_history_index'] + 1;
		}
		
		// データを追加
		$params = array($entryId, $langId, $historyIndex, $name, $html, $html2, $status, 
												intval($showComment), intval($receiveComment), $blogId, $now, $regUserId, $regDt, $startDt, $endDt, $userId, $now);
												
		$queryStr  = 'INSERT INTO blog_entry ';
		$queryStr .=   '(be_id, ';
		$queryStr .=   'be_language_id, ';
		$queryStr .=   'be_history_index, ';
		$queryStr .=   'be_name, ';
		$queryStr .=   'be_html, ';
		$queryStr .=   'be_html_ext, ';
		$queryStr .=   'be_status, ';
		$queryStr .=   'be_show_comment, ';
		$queryStr .=   'be_receive_comment, ';
		$queryStr .=   'be_blog_id, ';
		$queryStr .=   'be_dt, ';
		$queryStr .=   'be_regist_user_id, ';
		$queryStr .=   'be_regist_dt, ';
		$queryStr .=   'be_active_start_dt, ';
		$queryStr .=   'be_active_end_dt, ';
		$queryStr .=   'be_create_user_id, ';
		$queryStr .=   'be_create_dt ';
		
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
		$queryStr .=   '(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?' . $otherValueStr . ')';
		$this->execStatement($queryStr, $params);
		
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

		// 記事画像の更新
		for ($i = 0; $i < count($images); $i++){
			$ret = $this->updateEntryImage($newSerial, $i, $images[$i]);
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
	 * エントリー項目の更新
	 *
	 * @param int     $serial		シリアル番号
	 * @param string  $name			コンテンツ名
	 * @param string  $html			HTML
	 * @param string  $html2		HTML(続き)
	 * @param int     $status		エントリー状態(0=未設定、1=編集中、2=公開、3=非公開)
	 * @param array   $category		カテゴリーID
	 * @param array   $images		記事画像
	 * @param string  $blogId		ブログID
	 * @param int     $regUserId	投稿者ユーザID(0のときは更新しない)
	 * @param timestamp $regDt		投稿日時(空のときは更新しない)
	 * @param timestamp	$startDt	期間(開始日)
	 * @param timestamp	$endDt		期間(終了日)
	 * @param bool    $showComment	コメントを表示するかどうか
	 * @param bool $receiveComment	コメントを受け付けるかどうか
	 * @param int     $newSerial	新規シリアル番号
	 * @param array   $oldRecord	更新前の旧データ
	 * @param array   $otherParams	その他のフィールド値
	 * @return bool					true = 成功、false = 失敗
	 */
	function updateEntryItem($serial, $name, $html, $html2, $status, $category, $images, $blogId, $regUserId, $regDt, $startDt, $endDt, $showComment, $receiveComment, &$newSerial, 
								&$oldRecord, $otherParams = null)
	{
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $this->gEnv->getCurrentUserId();	// 現在のユーザ
						
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
			$historyIndex = $row['be_history_index'] + 1;
			
			$oldRecord = $row;			// 旧データ
		} else {		// 存在しない場合は終了
			$this->endTransaction();
			return false;
		}
		// 古いレコードを削除
		$queryStr  = 'UPDATE blog_entry ';
		$queryStr .=   'SET be_deleted = true, ';	// 削除
		$queryStr .=     'be_update_user_id = ?, ';
		$queryStr .=     'be_update_dt = ? ';
		$queryStr .=   'WHERE be_serial = ?';
		$this->execStatement($queryStr, array($userId, $now, intval($serial)));
		
		// データを追加
		if (empty($regUserId)){
			$rUserId = $row['be_regist_user_id'];
		} else {
			$rUserId = $regUserId;
		}
		if (empty($regDt)){
			$rDt = $row['be_regist_dt'];
		} else {
			$rDt = $regDt;
		}
		$entryId = $row['be_id'];
		$langId = $row['be_language_id'];
		
		// 新規レコード追加
		$params = array($entryId, $langId, $historyIndex, $name, $html, $html2, $status, 
												intval($showComment), intval($receiveComment), $blogId, $now, $rUserId, $rDt, $startDt, $endDt, $userId, $now);
		$queryStr  = 'INSERT INTO blog_entry ';
		$queryStr .=   '(be_id, ';
		$queryStr .=   'be_language_id, ';
		$queryStr .=   'be_history_index, ';
		$queryStr .=   'be_name, ';
		$queryStr .=   'be_html, ';
		$queryStr .=   'be_html_ext, ';
		$queryStr .=   'be_status, ';
		$queryStr .=   'be_show_comment, ';
		$queryStr .=   'be_receive_comment, ';
		$queryStr .=   'be_blog_id, ';
		$queryStr .=   'be_dt, ';
		$queryStr .=   'be_regist_user_id, ';
		$queryStr .=   'be_regist_dt, ';
		$queryStr .=   'be_active_start_dt, ';
		$queryStr .=   'be_active_end_dt, ';
		$queryStr .=   'be_create_user_id, ';
		$queryStr .=   'be_create_dt ';

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
		$queryStr .=   ') VALUES ';
		$queryStr .=   '(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?' . $otherValueStr . ')';
		$this->execStatement($queryStr, $params);

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

		// 記事画像の更新
		for ($i = 0; $i < count($images); $i++){
			$ret = $this->updateEntryImage($newSerial, $i, $images[$i]);
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
	 * 記事更新日の更新
	 *
	 * @param string  $id			エントリーID
	 * @param string  $langId		言語ID
	 * @return bool					true = 成功、false = 失敗
	 */
	function updateEntryDt($id, $langId)
	{
		$serial = $this->getEntrySerialNoByContentId($id, $langId);
		if (empty($serial)) return false;
		
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
		$queryStr .=   'SET be_dt = ?, ';	// 更新日
		$queryStr .=     'be_update_user_id = ?, ';
		$queryStr .=     'be_update_dt = ? ';
		$queryStr .=   'WHERE be_serial = ?';
		$this->execStatement($queryStr, array($now, $userId, $now, intval($serial)));
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * サムネールファイル名の更新
	 *
	 * @param string $id			エントリーID
	 * @param string $langId		言語ID
	 * @param string $thumbFilename	サムネールファイル名
	 * @param string $thumbSrc		サムネール作成元画像ファイル(resourceディレクトリからの相対パス)
	 * @return bool					true = 成功、false = 失敗
	 */
	function updateThumbFilename($id, $langId, $thumbFilename, $thumbSrc)
	{
		$serial = $this->getEntrySerialNoByContentId($id, $langId);
		if (empty($serial)) return false;
		
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
		$queryStr .=   'SET be_thumb_filename = ?, ';	// サムネールファイル名
		$queryStr .=     'be_thumb_src = ?, ';			// サムネール作成元画像ファイル
		$queryStr .=     'be_update_user_id = ?, ';
		$queryStr .=     'be_update_dt = ? ';
		$queryStr .=   'WHERE be_serial = ?';
		$this->execStatement($queryStr, array($thumbFilename, $thumbSrc, $userId, $now, intval($serial)));
		
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
	 * 記事画像の更新
	 *
	 * @param int        $serial		記事シリアル番号
	 * @param int        $index			インデックス番号
	 * @param string     $path			画像パス
	 * @return bool		 true = 成功、false = 失敗
	 */
	function updateEntryImage($serial, $index, $path)
	{
		// 新規レコード追加
		$queryStr = 'INSERT INTO blog_image ';
		$queryStr .=  '(';
		$queryStr .=  'bm_entry_serial, ';
		$queryStr .=  'bm_index, ';
		$queryStr .=  'bm_image_src) ';
		$queryStr .=  'VALUES ';
		$queryStr .=  '(?, ?, ?)';
		$ret =$this->execStatement($queryStr, array(intval($serial), $index, $path));
		return $ret;
	}
	/**
	 * エントリー項目をシリアル番号で取得
	 *
	 * @param string	$serial				シリアル番号
	 * @param array     $row				レコード
	 * @param array     $categoryRow		記事カテゴリー
	 * @return bool							取得 = true, 取得なし= false
	 */
	function getEntryBySerial($serial, &$row, &$categoryRow)
	{
		$queryStr  = 'SELECT *, reg.lu_name AS reg_user_name, updt.lu_name AS update_user_name FROM blog_entry LEFT JOIN _login_user ON be_create_user_id = lu_id AND lu_deleted = false ';
		$queryStr .=   'LEFT JOIN _login_user as reg ON be_regist_user_id = reg.lu_id AND reg.lu_deleted = false ';
		$queryStr .=   'LEFT JOIN _login_user as updt ON be_create_user_id = updt.lu_id AND updt.lu_deleted = false ';
		$queryStr .=   'WHERE be_serial = ? ';
		$ret = $this->selectRecord($queryStr, array(intval($serial)), $row);
		
		if ($ret){
			// ブログカテゴリー
			$queryStr  = 'SELECT * FROM blog_entry_with_category LEFT JOIN blog_category ON bw_category_id = bc_id AND bc_deleted = false ';
			$queryStr .=   'WHERE bw_entry_serial = ? ';
		//	$queryStr .=  'ORDER BY bw_index ';
			$queryStr .=  'ORDER BY bc_sort_order, bc_id ';		// カテゴリー並び順
			$this->selectRecords($queryStr, array(intval($serial)), $categoryRow);
		}
		return $ret;
	}
	/**
	 * エントリー項目のシリアル番号をエントリーIDで取得
	 *
	 * @param string	$id					エントリーID
	 * @param string	$langId				言語ID
	 * @return int							シリアル番号、取得できないときは0を返す
	 */
	function getEntrySerialNoByContentId($id, $langId)
	{
		$serial = 0;
		$queryStr  = 'SELECT * FROM blog_entry ';
		$queryStr .=   'WHERE be_deleted = false ';	// 削除されていない
		$queryStr .=   'AND be_history_index >= 0 ';		// 正規(Regular)記事を対象
		$queryStr .=   'AND be_id = ? ';
		$queryStr .=   'AND be_language_id = ? ';
		$ret = $this->selectRecord($queryStr, array($id, $langId), $row);
		if ($ret) $serial = $row['be_serial'];
		return $serial;
	}
	/**
	 * エントリー項目の削除
	 *
	 * @param array   $serial		シリアルNo
	 * @return						true=成功、false=失敗
	 */
	function delEntryItem($serial)
	{
		// 引数のエラーチェック
		if (!is_array($serial)) return false;
		if (count($serial) <= 0) return true;
		
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $this->gEnv->getCurrentUserId();	// 現在のユーザ
		
		// トランザクション開始
		$this->startTransaction();
		
		for ($i = 0; $i < count($serial); $i++){
			$queryStr  = 'SELECT * FROM blog_entry ';
			$queryStr .=   'WHERE be_deleted = false ';		// 未削除
			$queryStr .=     'AND be_serial = ? ';
			$ret = $this->selectRecord($queryStr, array($serial[$i]), $row);
			if ($ret){		// 既に登録レコードがあるとき			
				// レコードを削除
				$queryStr  = 'UPDATE blog_entry ';
				$queryStr .=   'SET be_deleted = true, ';	// 削除
				$queryStr .=     'be_update_user_id = ?, ';
				$queryStr .=     'be_update_dt = ? ';
				$queryStr .=   'WHERE be_serial = ?';
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
	 * エントリーIDでエントリー項目を削除
	 *
	 * @param int   $serial			シリアルNo
	 * @return						true=成功、false=失敗
	 */
	function delEntryItemById($serial)
	{
		// 引数のエラーチェック
//		if (!is_array($serial)) return false;
//		if (count($serial) <= 0) return true;
		
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $this->gEnv->getCurrentUserId();	// 現在のユーザ
		
		// トランザクション開始
		$this->startTransaction();
		
		// コンテンツIDを取得
		$queryStr  = 'select * from blog_entry ';
		$queryStr .=   'where be_deleted = false ';		// 未削除
		$queryStr .=     'and be_serial = ? ';
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
		$entryId = $row['be_id'];
		
		// レコードを削除
		$queryStr  = 'UPDATE blog_entry ';
		$queryStr .=   'SET be_deleted = true, ';	// 削除
		$queryStr .=     'be_update_user_id = ?, ';
		$queryStr .=     'be_update_dt = now() ';
		$queryStr .=   'WHERE be_id = ?';
		$this->execStatement($queryStr, array($userId, $entryId));
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * エントリー項目を取得
	 *
	 * @param int,array		$id				エントリーID
	 * @param string	$langId				言語
	 * @param array     $row				レコード
	 * @return bool							取得 = true, 取得なし= false
	 */
	function getEntryItem($id, $langId, &$row)
	{
		if (is_array($id)){
			$params = array();
			$contentId = implode(',', $id);
		
			// CASE文作成
			$caseStr = 'CASE be_id ';
			for ($i = 0; $i < count($id); $i++){
				$caseStr .= 'WHEN ' . $id[$i] . ' THEN ' . $i . ' ';
			}
			$caseStr .= 'END AS no';

			$queryStr = 'SELECT *, ' . $caseStr . ' FROM blog_entry LEFT JOIN blog_id ON be_blog_id = bl_id AND bl_deleted = false ';
			$queryStr .=  'WHERE be_deleted = false ';		// 削除されていない
			$queryStr .=    'AND be_history_index >= 0 ';		// 正規(Regular)記事を対象
			$queryStr .=    'AND be_id in (' . $contentId . ') ';
			$queryStr .=    'AND be_language_id = ? '; $params[] = $langId;
			$queryStr .=  'ORDER BY no';
			$ret = $this->selectRecords($queryStr, $params, $row);
		} else {
			$queryStr  = 'SELECT * FROM blog_entry LEFT JOIN blog_id ON be_blog_id = bl_id AND bl_deleted = false ';
			$queryStr .=   'WHERE be_deleted = false ';	// 削除されていない
			$queryStr .=   'AND be_history_index >= 0 ';		// 正規(Regular)記事を対象
			$queryStr .=   'AND be_id = ? ';
			$queryStr .=   'AND be_language_id = ? ';
			$ret = $this->selectRecord($queryStr, array($id, $langId), $row);
		}
		return $ret;
	}
	/**
	 * 前後のエントリー項目を取得(管理用)
	 *
	 * @param timestamp $regDate			登録日時
	 * @param array     $prevRow			前のレコード
	 * @param array     $nextRow			次のレコード
	 * @param string	$blogId				ブログID(未指定の場合はnull)
	 * @return bool							取得 = true, 取得なし= false
	 */
	function getPrevNextEntryByDate($regDate, &$prevRow, &$nextRow, $blogId = null)
	{
		if ($regDate == $this->gEnv->getInitValueOfTimestamp()){
			return false;
		} else {
			$retStatus = false;
			$params = array();
			$queryStr  = 'SELECT * FROM blog_entry LEFT JOIN blog_id ON be_blog_id = bl_id AND bl_deleted = false ';
			$queryStr .=   'WHERE be_deleted = false ';	// 削除されていない
			$queryStr .=     'AND be_history_index >= 0 ';		// 正規(Regular)記事を対象
			$queryStr .=     'AND be_regist_dt < ? '; $params[] = $regDate;
			
			// ブログID
			if (isset($blogId)){
				$queryStr .=    'AND be_blog_id = ? ';
				$params[] = $blogId;
			}
			$queryStr .=   'ORDER BY be_regist_dt DESC LIMIT 1';// 投稿順
			$ret = $this->selectRecord($queryStr, $params, $row);
			if ($ret){
				$prevRow = $row;
				$retStatus = true;
			}
			
			$params = array();
			$queryStr  = 'SELECT * FROM blog_entry LEFT JOIN blog_id ON be_blog_id = bl_id AND bl_deleted = false ';
			$queryStr .=   'WHERE be_deleted = false ';	// 削除されていない
			$queryStr .=     'AND be_history_index >= 0 ';		// 正規(Regular)記事を対象
			$queryStr .=     'AND ? < be_regist_dt '; $params[] = $regDate;
			
			// ブログID
			if (isset($blogId)){
				$queryStr .=    'AND be_blog_id = ? ';
				$params[] = $blogId;
			}
			$queryStr .=   'ORDER BY be_regist_dt LIMIT 1';// 投稿順
			$ret = $this->selectRecord($queryStr, $params, $row);
			if ($ret){
				$nextRow = $row;
				$retStatus = true;
			}
		}
		return $retStatus;
	}
	/**
	 * 前後のエントリー項目を取得(一般用)
	 *
	 * @param timestamp $regDate			登録日時
	 * @param string	$langId				言語
	 * @param int		$order				取得順(0=昇順,1=降順)
	 * @param timestamp	$startDt			期間(開始日)
	 * @param timestamp	$endDt				期間(終了日)
	 * @param array		$keywords			検索キーワード
	 * @param string	$blogId				ブログID(nullのとき指定なし)
	 * @param int       $userId				参照制限する場合のユーザID
	 * @param array     $prevRow			前のレコード
	 * @param array     $nextRow			次のレコード
	 * @return bool							取得 = true, 取得なし= false
	 */
	function getPrevNextEntryByKeyword($regDate, $langId, $order, $startDt, $endDt, $keywords, $blogId, $userId, &$prevRow, &$nextRow)
	{
		if ($regDate == $this->gEnv->getInitValueOfTimestamp()){
			return false;
		} else {
			$retStatus = false;
			
			// ### 前の日時の記事を取得 ###
			$params = array();
			$queryStr  = 'SELECT * FROM blog_entry LEFT JOIN blog_id ON be_blog_id = bl_id AND bl_deleted = false ';
			$queryStr .=   'WHERE be_deleted = false ';	// 削除されていない
			$queryStr .=     'AND be_history_index >= 0 ';		// 正規(Regular)記事を対象
			$queryStr .=     'AND be_language_id = ? ';	$params[] = $langId;
			$queryStr .=     'AND be_regist_dt < ? '; $params[] = $regDate;
			
			// 検索条件を付加
			list($condQueryStr, $condParams) = $this->_createSearchCondition($startDt, $endDt, $keywords, $blogId, $userId);
			$queryStr .= $condQueryStr;
			$params = array_merge($params, $condParams);
		
			$queryStr .=   'ORDER BY be_regist_dt DESC LIMIT 1';// 投稿順
			$ret = $this->selectRecord($queryStr, $params, $row);
			if ($ret){
				$prevRow = $row;
				$retStatus = true;
			}
			
			// ### 後の日時の記事を取得 ###
			$params = array();
			$queryStr  = 'SELECT * FROM blog_entry LEFT JOIN blog_id ON be_blog_id = bl_id AND bl_deleted = false ';
			$queryStr .=   'WHERE be_deleted = false ';	// 削除されていない
			$queryStr .=     'AND be_history_index >= 0 ';		// 正規(Regular)記事を対象
			$queryStr .=     'AND be_language_id = ? ';	$params[] = $langId;
			$queryStr .=     'AND ? < be_regist_dt '; $params[] = $regDate;
			
			// 検索条件を付加
			list($condQueryStr, $condParams) = $this->_createSearchCondition($startDt, $endDt, $keywords, $blogId, $userId);
			$queryStr .= $condQueryStr;
			$params = array_merge($params, $condParams);
			
			$queryStr .=   'ORDER BY be_regist_dt LIMIT 1';// 投稿順
			$ret = $this->selectRecord($queryStr, $params, $row);
			if ($ret){
				$nextRow = $row;
				$retStatus = true;
			}
			
			// ### 降順の場合は前後入れ替え ###
			if (!empty($order)){
				$tmp = $prevRow;
				$prevRow = $nextRow;
				$nextRow = $tmp;
			}
		}
		return $retStatus;
	}
	/**
	 * カテゴリー選択で前後のエントリー項目を取得(一般用)
	 *
	 * @param timestamp $regDate			登録日時
	 * @param string	$langId				言語
	 * @param int		$order				取得順(0=昇順,1=降順)
	 * @param int		$categoryId			カテゴリーID
	 * @param string	$blogId				ブログID(nullのとき指定なし)
	 * @param int       $userId				参照制限する場合のユーザID
	 * @param array     $prevRow			前のレコード
	 * @param array     $nextRow			次のレコード
	 * @return bool							取得 = true, 取得なし= false
	 */
	function getPrevNextEntryByCategory($regDate, $langId, $order, $categoryId, $blogId, $userId, &$prevRow, &$nextRow)
	{
		if ($regDate == $this->gEnv->getInitValueOfTimestamp()){
			return false;
		} else {
			$retStatus = false;
			
			// ### 前の日時の記事を取得 ###
			$params = array();
//			$queryStr  = 'SELECT * FROM blog_entry LEFT JOIN blog_id ON be_blog_id = bl_id AND bl_deleted = false ';
			$queryStr  = 'SELECT distinct(be_serial) FROM blog_entry LEFT JOIN blog_id ON be_blog_id = bl_id AND bl_deleted = false ';
			$queryStr .=   'RIGHT JOIN blog_entry_with_category ON be_serial = bw_entry_serial ';
			$queryStr .=   'WHERE be_deleted = false ';	// 削除されていない
			$queryStr .=     'AND be_history_index >= 0 ';		// 正規(Regular)記事を対象
			$queryStr .=     'AND be_language_id = ? ';	$params[] = $langId;
			$queryStr .=     'AND be_regist_dt < ? '; $params[] = $regDate;
			$queryStr .=     'AND bw_category_id = ? ';	$params[] = $categoryId;// 記事カテゴリー
		
			// 検索条件を付加
			list($condQueryStr, $condParams) = $this->_createSearchCondition(null, null, null, $blogId, $userId);
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
		
			$queryStr .=   'ORDER BY be_regist_dt DESC LIMIT 1';// 投稿順
			$ret = $this->selectRecord($queryStr, $params, $row);
			if ($ret){
				$prevRow = $row;
				$retStatus = true;
			}
		
			// ### 後の日時の記事を取得 ###
			$params = array();
//			$queryStr  = 'SELECT * FROM blog_entry LEFT JOIN blog_id ON be_blog_id = bl_id AND bl_deleted = false ';
			$queryStr  = 'SELECT distinct(be_serial) FROM blog_entry LEFT JOIN blog_id ON be_blog_id = bl_id AND bl_deleted = false ';
			$queryStr .=   'RIGHT JOIN blog_entry_with_category ON be_serial = bw_entry_serial ';
			$queryStr .=   'WHERE be_deleted = false ';	// 削除されていない
			$queryStr .=     'AND be_history_index >= 0 ';		// 正規(Regular)記事を対象
			$queryStr .=     'AND be_language_id = ? ';	$params[] = $langId;
			$queryStr .=     'AND ? < be_regist_dt '; $params[] = $regDate;
			$queryStr .=     'AND bw_category_id = ? ';	$params[] = $categoryId;// 記事カテゴリー
			
			// 検索条件を付加
			list($condQueryStr, $condParams) = $this->_createSearchCondition(null, null, null, $blogId, $userId);
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
			
			$queryStr .=   'ORDER BY be_regist_dt LIMIT 1';// 投稿順
			$ret = $this->selectRecord($queryStr, $params, $row);
			if ($ret){
				$nextRow = $row;
				$retStatus = true;
			}
			
			// ### 降順の場合は前後入れ替え ###
			if (!empty($order)){
				$tmp = $prevRow;
				$prevRow = $nextRow;
				$nextRow = $tmp;
			}
		}
		return $retStatus;
	}
	/**
	 * 検索条件を作成
	 *
	 * @param timestamp	$startDt			期間(開始日)
	 * @param timestamp	$endDt				期間(終了日)
	 * @param array		$keywords			検索キーワード
	 * @param string	$blogId				ブログID(nullのとき指定なし)
	 * @param int       $userId				参照制限する場合のユーザID
	 * @return array						クエリー文字列と配列パラメータの連想配列
	 */
	function _createSearchCondition($startDt, $endDt, $keywords, $blogId, $userId)
	{
		$now = date("Y/m/d H:i:s");	// 現在日時
		$initDt = $this->gEnv->getInitValueOfTimestamp();		// 日時初期化値
		$queryStr = '';
		$params = array();
		
		// 取得期間
		if (!empty($startDt)){
			$queryStr .=    'AND ? <= be_regist_dt ';
			$params[] = $startDt;
		}
		if (!empty($endDt)){
			$queryStr .=    'AND be_regist_dt < ? ';
			$params[] = $endDt;
		}
		
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
		
		// タイトルと記事、ユーザ定義フィールドを検索
		if (!empty($keywords)){
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
			
		// ユーザ参照制限
		if (isset($userId)){
			$queryStr .=     'AND (be_blog_id = \'\' ';
			$queryStr .=     'OR (be_blog_id != \'\' ';
			$queryStr .=     'AND ((bl_owner_id = ? AND bl_owner_id != 0) ';	$params[] = $userId;
			$queryStr .=     'OR bl_user_limited = false ';
			$queryStr .=     'OR (bl_user_limited = true AND bl_limited_user_id = \'\' AND 0 != ' . $userId . ') ';
			$queryStr .=     'OR (bl_user_limited = true AND bl_limited_user_id != \'\' AND bl_limited_user_id LIKE \'%' . blog_mainCommonDef::USER_ID_SEPARATOR . $userId . blog_mainCommonDef::USER_ID_SEPARATOR . '%\')))) ';
		}
		
		return array($queryStr, $params);
	}
	/**
	 * エントリー項目を取得(表示用)
	 *
	 * @param int		$limit				取得する項目数
	 * @param int		$page				取得するページ(1～)
	 * @param timestamp $now				現在日時(現在日時より未来の投稿日時の記事は取得しない)
	 * @param int		$entryId			エントリーID(0のときは期間で取得)
	 * @param timestamp	$startDt			期間(開始日)
	 * @param timestamp	$endDt				期間(終了日)
	 * @param array		$keywords			検索キーワード
	 * @param string	$langId				言語
	 * @param int		$order				取得順(0=昇順,1=降順)
	 * @param function	$callback			コールバック関数
	 * @param string	$blogId				ブログID(nullのとき指定なし)
	 * @param int       $userId				参照制限する場合のユーザID
	 * @param bool		$preview			プレビューモードかどうか
	 * @return 			なし
	 */
	function getEntryItems($limit, $page, $now, $entryId, $startDt, $endDt, $keywords, $langId, $order, $callback, $blogId = null, $userId = null, $preview = false)
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
		if (!empty($entryId)){
			$queryStr .=     'AND be_id = ? ';		$params[] = $entryId;
		}
		
		// タイトルと記事、ユーザ定義フィールドを検索
		if (!empty($keywords)){
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
			$queryStr .=    'AND be_blog_id = ? ';		$params[] = $blogId;
		}
	
		// ユーザ参照制限
		if (isset($userId)){
			$queryStr .=     'AND (be_blog_id = \'\' ';
			$queryStr .=     'OR (be_blog_id != \'\' ';
			$queryStr .=     'AND ((bl_owner_id = ? AND bl_owner_id != 0) ';	$params[] = $userId;
			$queryStr .=     'OR bl_user_limited = false ';
			$queryStr .=     'OR (bl_user_limited = true AND bl_limited_user_id = \'\' AND 0 != ' . $userId . ') ';
			$queryStr .=     'OR (bl_user_limited = true AND bl_limited_user_id != \'\' AND bl_limited_user_id LIKE \'%' . blog_mainCommonDef::USER_ID_SEPARATOR . $userId . blog_mainCommonDef::USER_ID_SEPARATOR . '%\')))) ';
		}
	
		// 検索条件
		if (!empty($startDt)){
			$queryStr .=    'AND ? <= be_regist_dt ';
			$params[] = $startDt;
		}
		if (!empty($endDt)){
			$queryStr .=    'AND be_regist_dt < ? ';
			$params[] = $endDt;
		}
		
		if (!$preview){		// プレビューモードでないときは取得制限
			$queryStr .=     'AND be_status = ? ';		$params[] = 2;	// 「公開」(2)データを表示
			$queryStr .=     'AND be_regist_dt <= ? ';	$params[] = $now;		// 投稿日時が現在日時よりも過去のものを取得
		
			// 公開期間を指定
			$queryStr .=    'AND (be_active_start_dt = ? OR (be_active_start_dt != ? AND be_active_start_dt <= ?)) ';
			$queryStr .=    'AND (be_active_end_dt = ? OR (be_active_end_dt != ? AND be_active_end_dt > ?)) ';
			$params[] = $initDt;
			$params[] = $initDt;
			$params[] = $now;
			$params[] = $initDt;
			$params[] = $initDt;
			$params[] = $now;
		}

		if (empty($entryId)){
			$ord = '';
			if (!empty($order)) $ord = 'DESC ';
			$queryStr .=  'ORDER BY be_regist_dt ' . $ord . 'LIMIT ' . $limit . ' offset ' . $offset;// 投稿順
		}
		$this->selectLoop($queryStr, $params, $callback);
	}
	/**
	 * エントリー項目数を取得(表示用)
	 *
	 * @param timestamp $now				現在日時(現在日時より未来の投稿日時の記事は取得しない)
	 * @param timestamp	$startDt			期間(開始日)
	 * @param timestamp	$endDt				期間(終了日)
	 * @param array		$keywords			検索キーワード
	 * @param string	$langId				言語
	 * @param string	$blogId				ブログID(nullのとき指定なし)
	 * @param int       $userId				参照制限する場合のユーザID
	 * @param bool		$preview			プレビューモードかどうか
	 * @return int							項目数
	 */
	function getEntryItemsCount($now, $startDt, $endDt, $keywords, $langId, $blogId = null, $userId = null, $preview = false)
	{
		$initDt = $this->gEnv->getInitValueOfTimestamp();		// 日時初期化値
		$params = array();
		
		$queryStr = 'SELECT * FROM blog_entry LEFT JOIN blog_id ON be_blog_id = bl_id AND bl_deleted = false ';
		$queryStr .=  'WHERE be_deleted = false ';		// 削除されていない
		$queryStr .=    'AND be_history_index >= 0 ';		// 正規(Regular)記事を対象
		$queryStr .=    'AND be_language_id = ? ';	$params[] = $langId;
		
		// タイトルと記事、ユーザ定義フィールドを検索
		if (!empty($keywords)){
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
			$queryStr .=    'AND be_blog_id = ? ';		$params[] = $blogId;
		}
		
		// ユーザ参照制限
		if (isset($userId)){
			$queryStr .=     'AND (be_blog_id = \'\' ';
			$queryStr .=     'OR (be_blog_id != \'\' ';
			$queryStr .=     'AND ((bl_owner_id = ? AND bl_owner_id != 0) ';	$params[] = $userId;
			$queryStr .=     'OR bl_user_limited = false ';
			$queryStr .=     'OR (bl_user_limited = true AND bl_limited_user_id = \'\' AND 0 != ' . $userId . ') ';
			$queryStr .=     'OR (bl_user_limited = true AND bl_limited_user_id != \'\' AND bl_limited_user_id LIKE \'%' . blog_mainCommonDef::USER_ID_SEPARATOR . $userId . blog_mainCommonDef::USER_ID_SEPARATOR . '%\')))) ';
		}
		
		// 検索条件
		if (!empty($startDt)){
			$queryStr .=    'AND ? <= be_regist_dt ';
			$params[] = $startDt;
		}
		if (!empty($endDt)){
			$queryStr .=    'AND be_regist_dt < ? ';
			$params[] = $endDt;
		}
		
		if (!$preview){		// プレビューモードでないときは取得制限
			$queryStr .=    'AND be_status = ? ';		$params[] = 2;	// 「公開」(2)データを表示
			$queryStr .=    'AND be_regist_dt <= ? ';	$params[] = $now;		// 投稿日時が現在日時よりも過去のものを取得
		
			// 公開期間を指定
			$queryStr .=    'AND (be_active_start_dt = ? OR (be_active_start_dt != ? AND be_active_start_dt <= ?)) ';
			$queryStr .=    'AND (be_active_end_dt = ? OR (be_active_end_dt != ? AND be_active_end_dt > ?)) ';
			$params[] = $initDt;
			$params[] = $initDt;
			$params[] = $now;
			$params[] = $initDt;
			$params[] = $initDt;
			$params[] = $now;
		}
		return $this->selectRecordCount($queryStr, $params);
	}
	/**
	 * エントリー項目をカテゴリー指定で取得(表示用)
	 *
	 * @param int		$limit				取得する項目数
	 * @param int		$page				取得するページ(1～)
	 * @param timestamp $now				現在日時(現在日時より未来の投稿日時の記事は取得しない)
	 * @param int		$categoryId			カテゴリーID
	 * @param string	$langId				言語
	 * @param int		$order				取得順(0=昇順,1=降順)
	 * @param function	$callback			コールバック関数
	 * @param int       $userId				参照制限する場合のユーザID
	 * @return 			なし
	 */
	function getEntryItemsByCategory($limit, $page, $now, $categoryId, $langId, $order, $callback, $userId = null)
	{
		$offset = $limit * ($page -1);
		if ($offset < 0) $offset = 0;
		$initDt = $this->gEnv->getInitValueOfTimestamp();		// 日時初期化値
		$params = array();
		
		$queryStr = 'SELECT distinct(be_serial) FROM blog_entry LEFT JOIN blog_id ON be_blog_id = bl_id AND bl_deleted = false ';
		$queryStr .=  'RIGHT JOIN blog_entry_with_category ON be_serial = bw_entry_serial ';
		$queryStr .=  'WHERE be_language_id = ? '; $params[] = $langId;
		$queryStr .=    'AND be_deleted = false ';		// 削除されていない
		$queryStr .=    'AND be_history_index >= 0 ';		// 正規(Regular)記事を対象
		$queryStr .=    'AND be_status = ? '; $params[] = 2;	// 「公開」(2)データ
		$queryStr .=    'AND bw_category_id = ? ';	$params[] = $categoryId;// 記事カテゴリー
		$queryStr .=    'AND be_regist_dt <= ? ';	$params[] = $now;			// 投稿日時が現在日時よりも過去のものを取得
		
		// ユーザ参照制限
		if (isset($userId)){
			$queryStr .=     'AND (be_blog_id = \'\' ';
			$queryStr .=     'OR (be_blog_id != \'\' ';
			$queryStr .=     'AND ((bl_owner_id = ? AND bl_owner_id != 0) ';	$params[] = $userId;
			$queryStr .=     'OR bl_user_limited = false ';
			$queryStr .=     'OR (bl_user_limited = true AND bl_limited_user_id = \'\' AND 0 != ' . $userId . ') ';
			$queryStr .=     'OR (bl_user_limited = true AND bl_limited_user_id != \'\' AND bl_limited_user_id LIKE \'%' . blog_mainCommonDef::USER_ID_SEPARATOR . $userId . blog_mainCommonDef::USER_ID_SEPARATOR . '%\')))) ';
		}
		
		// 公開期間を指定
		$queryStr .=    'AND (be_active_start_dt = ? OR (be_active_start_dt != ? AND be_active_start_dt <= ?)) ';
		$queryStr .=    'AND (be_active_end_dt = ? OR (be_active_end_dt != ? AND be_active_end_dt > ?)) ';
		$params[] = $initDt;
		$params[] = $initDt;
		$params[] = $now;
		$params[] = $initDt;
		$params[] = $initDt;
		$params[] = $now;
		
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
		if (!empty($order)) $ord = 'DESC ';
		$queryStr .=  'ORDER BY be_regist_dt ' . $ord . 'LIMIT ' . $limit . ' offset ' . $offset;// 投稿順
		$this->selectLoop($queryStr, array(), $callback, null);
	}
	/**
	 * エントリー項目数をカテゴリー指定で取得(表示用)
	 *
	 * @param timestamp $now				現在日時(現在日時より未来の投稿日時の記事は取得しない)
	 * @param int		$categoryId			カテゴリーID
	 * @param string	$langId				言語
	 * @param int       $userId				参照制限する場合のユーザID
	 * @return int							エントリー項目数
	 */
	function getEntryItemsCountByCategory($now, $categoryId, $langId, $userId = null)
	{
		$initDt = $this->gEnv->getInitValueOfTimestamp();		// 日時初期化値
		$params = array();
		
		$queryStr = 'SELECT distinct(be_serial) FROM blog_entry LEFT JOIN blog_id ON be_blog_id = bl_id AND bl_deleted = false ';
		$queryStr .=  'RIGHT JOIN blog_entry_with_category ON be_serial = bw_entry_serial ';
		$queryStr .=  'WHERE be_language_id = ? '; $params[] = $langId;
		$queryStr .=    'AND be_deleted = false ';		// 削除されていない
		$queryStr .=    'AND be_history_index >= 0 ';		// 正規(Regular)記事を対象
		$queryStr .=    'AND be_status = ? '; $params[] = 2;	// 「公開」(2)データ
		$queryStr .=    'AND bw_category_id = ? ';	$params[] = $categoryId;// 記事カテゴリー
		$queryStr .=    'AND be_regist_dt <= ? ';	$params[] = $now;			// 投稿日時が現在日時よりも過去のものを取得
		
		// ユーザ参照制限
		if (isset($userId)){
			$queryStr .=     'AND (be_blog_id = \'\' ';
			$queryStr .=     'OR (be_blog_id != \'\' ';
			$queryStr .=     'AND ((bl_owner_id = ? AND bl_owner_id != 0) ';	$params[] = $userId;
			$queryStr .=     'OR bl_user_limited = false ';
			$queryStr .=     'OR (bl_user_limited = true AND bl_limited_user_id = \'\' AND 0 != ' . $userId . ') ';
			$queryStr .=     'OR (bl_user_limited = true AND bl_limited_user_id != \'\' AND bl_limited_user_id LIKE \'%' . blog_mainCommonDef::USER_ID_SEPARATOR . $userId . blog_mainCommonDef::USER_ID_SEPARATOR . '%\')))) ';
		}
		
		// 公開期間を指定
		$queryStr .=    'AND (be_active_start_dt = ? OR (be_active_start_dt != ? AND be_active_start_dt <= ?)) ';
		$queryStr .=    'AND (be_active_end_dt = ? OR (be_active_end_dt != ? AND be_active_end_dt > ?)) ';
		$params[] = $initDt;
		$params[] = $initDt;
		$params[] = $now;
		$params[] = $initDt;
		$params[] = $initDt;
		$params[] = $now;

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
	
		$queryStr = 'SELECT * FROM blog_entry LEFT JOIN blog_id ON be_blog_id = bl_id AND bl_deleted = false ';
		$queryStr .=  'WHERE be_serial in (' . $serialStr . ') ';
		return $this->selectRecordCount($queryStr, array());
	}
	/**
	 * すべての言語を取得
	 *
	 * @param function	$callback			コールバック関数
	 * @return			true=取得、false=取得せず
	 */
	function getAllLang($callback)
	{
		$queryStr = 'SELECT * FROM _language ORDER BY ln_priority';
		$this->selectLoop($queryStr, array(), $callback, null);
	}
	/**
	 * 記事カテゴリー一覧を取得
	 *
	 * @param string	$langId				言語
	 * @param array		$rows				取得データ
	 * @return bool							true=取得、false=取得せず
	 */
	function getAllCategory($langId, &$rows)
	{
		$queryStr = 'SELECT * FROM blog_category LEFT JOIN _login_user ON bc_create_user_id = lu_id AND lu_deleted = false ';
		$queryStr .=  'WHERE bc_language_id = ? ';
		$queryStr .=    'AND bc_deleted = false ';		// 削除されていない
		$queryStr .=  'ORDER BY bc_sort_order, bc_id';			// 表示順
		$retValue = $this->selectRecords($queryStr, array($langId), $rows);
		return $retValue;
	}
	/**
	 * 記事が指定ブログ属するかチェック
	 *
	 * @param int    $serial		記事のシリアルNo
	 * @param string $blogId		ブログID
	 * @param bool $isHistoryRef	履歴を参照するかどうか
	 * @return bool					true=存在する、false=存在しない
	 */
	function isExistsEntryInBlogId($serial, $blogId, $isHistoryRef = false)
	{
		$queryStr  = 'SELECT * FROM blog_entry ';
		$queryStr .=   'WHERE be_serial = ? ';
		$queryStr .=     'AND be_blog_id = ? ';
		if (!$isHistoryRef) $queryStr .=     'AND be_deleted = false ';		// 削除されていない
		return $this->isRecordExists($queryStr, array(intval($serial), $blogId));
	}
	/**
	 * コメントが指定ブログ属するかチェック
	 *
	 * @param int    $serial		コメントのシリアルNo
	 * @param string $blogId		ブログID
	 * @return bool					true=存在する、false=存在しない
	 */
	function isExistsCommentInBlogId($serial, $blogId)
	{
		$queryStr  = 'SELECT * FROM blog_comment ';
		$queryStr .=   'LEFT JOIN blog_entry ON bo_entry_id = be_id AND be_deleted = false ';
		$queryStr .=   'WHERE bo_deleted = false ';		// 削除されていない
		$queryStr .=     'AND bo_serial = ? ';
		$queryStr .=     'AND be_blog_id = ? ';
		return $this->isRecordExists($queryStr, array(intval($serial), $blogId));
	}
	/**
	 * 利用可能なブログのブログIDを取得
	 *
	 * @param array   $rows			取得レコード
	 * @return bool					取得 = true, 取得なし= false
	 */
	function getAvailableBlogId(&$rows)
	{
		$params = array();
		$queryStr  = 'SELECT * FROM blog_id ';
		$queryStr .=   'WHERE bl_deleted = false ';		// 削除されていない
		if (!$this->gEnv->isSystemManageUser()){		// コンテンツ編集可能ユーザの場合
			$queryStr .=     'AND bl_owner_id = ? '; $params[] = $this->gEnv->getCurrentUserId();	// 現在のユーザ
		}
		$queryStr .=  'ORDER BY bl_index, bl_id';
		$retValue = $this->selectRecords($queryStr, $params, $rows);
		return $retValue;
	}
	/**
	 * ブログ一覧を取得(管理用)
	 *
	 * @param function	$callback			コールバック関数
	 * @return 			なし
	 */
	function getAllBlogInfo($callback)
	{
		$queryStr = 'SELECT * FROM blog_id LEFT JOIN _login_user ON bl_owner_id = lu_id AND lu_deleted = false ';
		$queryStr .=  'WHERE bl_deleted = false ';		// 削除されていない
		$queryStr .=  'ORDER BY bl_index, bl_id';
		$this->selectLoop($queryStr, array(), $callback);
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
	 * ブログ情報をシリアル番号で取得
	 *
	 * @param string	$serial				シリアル番号
	 * @param array     $row				レコード
	 * @return bool							取得 = true, 取得なし= false
	 */
	function getBlogInfoBySerial($serial, &$row)
	{
		$queryStr  = 'SELECT * FROM blog_id LEFT JOIN _login_user ON bl_create_user_id = lu_id AND lu_deleted = false ';
		$queryStr .=   'WHERE bl_serial = ? ';
		$ret = $this->selectRecord($queryStr, array(intval($serial)), $row);
		return $ret;
	}
	/**
	 * 公開可能なブログ情報かどうか
	 *
	 * @param string	$id			識別ID
	 * @return bool					true=存在する、false=存在しない
	 */
	function isActiveBlogInfo($id)
	{
		$queryStr  = 'SELECT * FROM blog_id ';
		$queryStr .=   'WHERE bl_deleted = false ';
		$queryStr .=     'AND bl_visible = true ';
		$queryStr .=     'AND bl_id = ? ';
		return $this->isRecordExists($queryStr, array($id));
	}
	/**
	 * 参照可能なブログ情報かどうか
	 *
	 * @param string	$id			識別ID
	 * @param int       $userId		ユーザID
	 * @return bool					true=存在する、false=存在しない
	 */
	function isReadableBlogInfo($id, $userId)
	{
		$queryStr  = 'SELECT * FROM blog_id ';
		$queryStr .=   'WHERE bl_deleted = false ';
		$queryStr .=     'AND bl_visible = true ';
		$queryStr .=     'AND bl_id = ? ';
		$queryStr .=     'AND (bl_user_limited = false ';
		$queryStr .=     'OR (bl_user_limited = true AND bl_limited_user_id = \'\' AND 0 != ' . $userId . ') ';
		$queryStr .=     'OR (bl_user_limited = true AND bl_limited_user_id != \'\' AND bl_limited_user_id LIKE \'%' . blog_mainCommonDef::USER_ID_SEPARATOR . $userId . blog_mainCommonDef::USER_ID_SEPARATOR . '%\')) ';
		return $this->isRecordExists($queryStr, array($id));
	}
	/**
	 * ブログ情報を更新
	 *
	 * @param string $serial		シリアル番号(0のときは新規登録)
	 * @param string $id			ブログID
	 * @param string $name			名前
	 * @param int    $index			表示順
	 * @param string $templateId	テンプレートID
	 * @param string $subTemplateId	サブテンプレートID
	 * @param bool $visible			表示制御
	 * @param bool $userLimited		ユーザ制限
	 * @param string  $metaTitle	METAタグ、タイトル
	 * @param string  $metaDesc		METAタグ、ページ要約
	 * @param string  $metaKeyword	METAタグ、検索用キーワード
	 * @param int    $ownerId		所有者ID
	 * @param string  $limitedUserId	制限ユーザID
	 * @param int $newSerial	新規シリアル番号
	 * @return					true = 正常、false=異常
	 */
	function updateBlogInfo($serial, $id, $name, $index, $templateId, $subTemplateId, $visible, $userLimited, $metaTitle, $metaDesc, $metaKeyword, $ownerId, $limitedUserId, &$newSerial)
	{
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $this->gEnv->getCurrentUserId();	// 現在のユーザ
		$contentUpdateDt = $this->gEnv->getInitValueOfTimestamp();
		
		// トランザクション開始
		$this->startTransaction();
		
		// 前レコードの削除状態チェック
		$historyIndex = 0;
		$desc = '';
		if (empty($serial)){		// 新規登録のとき
			$queryStr = 'SELECT * FROM blog_id ';
			$queryStr .=  'WHERE bl_id = ? ';
			$queryStr .=  'ORDER BY bl_history_index DESC ';
			$ret = $this->selectRecord($queryStr, array($id), $row);
			if ($ret){
				if (!$row['bl_deleted']){		// レコード存在していれば終了
					$this->endTransaction();
					return false;
				}
				$historyIndex = $row['bl_history_index'] + 1;
			}
		} else {		// 更新のとき
			// 指定のシリアルNoのレコードが削除状態でないかチェック
			$queryStr  = 'SELECT * FROM blog_id ';
			$queryStr .=   'WHERE bl_serial = ? ';
			$ret = $this->selectRecord($queryStr, array(intval($serial)), $row);
			if ($ret){		// 既に登録レコードがあるとき
				if ($row['bl_deleted']){		// レコードが削除されていれば終了
					$this->endTransaction();
					return false;
				}
				$historyIndex = $row['bl_history_index'] + 1;
				
				// 識別IDとコンテンツ更新日時の変更は不可
				$id = $row['bl_id'];
				$contentUpdateDt = $row['bl_content_update_dt'];
			} else {		// 存在しない場合は終了
				$this->endTransaction();
				return false;
			}
			
			// 古いレコードを削除
			$queryStr  = 'UPDATE blog_id ';
			$queryStr .=   'SET bl_deleted = true, ';	// 削除
			$queryStr .=     'bl_update_user_id = ?, ';
			$queryStr .=     'bl_update_dt = ? ';
			$queryStr .=   'WHERE bl_serial = ?';
			$ret = $this->execStatement($queryStr, array($userId, $now, intval($serial)));
			if (!$ret){
				$this->endTransaction();
				return false;
			}
		}
		
		// データを追加
		$queryStr = 'INSERT INTO blog_id ';
		$queryStr .=  '(bl_id, bl_history_index, bl_name, bl_index, bl_template_id, bl_sub_template_id, bl_visible, bl_user_limited, bl_meta_title, bl_meta_description, bl_meta_keywords, bl_owner_id, bl_limited_user_id, bl_content_update_dt, bl_create_user_id, bl_create_dt) ';
		$queryStr .=  'VALUES ';
		$queryStr .=  '(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
		$this->execStatement($queryStr, array($id, $historyIndex, $name, $index, $templateId, $subTemplateId, intval($visible), intval($userLimited), $metaTitle, $metaDesc, $metaKeyword, intval($ownerId), $limitedUserId, $contentUpdateDt, $userId, $now));
		
		// 新規のシリアル番号取得
		$queryStr = 'SELECT MAX(bl_serial) AS ns FROM blog_id ';
		$ret = $this->selectRecord($queryStr, array(), $row);
		if ($ret) $newSerial = $row['ns'];
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * ブログ情報の削除
	 *
	 * @param array $serial			シリアルNo
	 * @return						true=成功、false=失敗
	 */
	function delBlogInfo($serial)
	{
		$now = date("Y/m/d H:i:s");	// 現在日時
		$user = $this->gEnv->getCurrentUserId();	// 現在のユーザ
		
		if (!is_array($serial) || count($serial) <= 0) return true;
		
		// トランザクション開始
		$this->startTransaction();
		
		// 指定のシリアルNoのレコードが削除状態でないかチェック
		for ($i = 0; $i < count($serial); $i++){
			$queryStr  = 'SELECT * FROM blog_id ';
			$queryStr .=   'WHERE bl_deleted = false ';		// 未削除
			$queryStr .=     'AND bl_serial = ? ';
			$ret = $this->isRecordExists($queryStr, array($serial[$i]));
			// 存在しない場合は、既に削除されたとして終了
			if (!$ret){
				$this->endTransaction();
				return false;
			}
		}
		
		// レコードを削除
		$queryStr  = 'UPDATE blog_id ';
		$queryStr .=   'SET bl_deleted = true, ';	// 削除
		$queryStr .=     'bl_update_user_id = ?, ';
		$queryStr .=     'bl_update_dt = ? ';
		$queryStr .=   'WHERE bl_serial in (' . implode(',', $serial) . ') ';
		$this->execStatement($queryStr, array($user, $now));
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * 最大表示順を取得
	 *
	 * @return int					最大表示順
	 */
	function getBlogInfoMaxIndex()
	{
		$queryStr = 'SELECT max(bl_index) as mi FROM blog_id ';
		$queryStr .=  'WHERE bl_deleted = false ';
		$ret = $this->selectRecord($queryStr, array(), $row);
		if ($ret){
			$index = $row['mi'];
		} else {
			$index = 0;
		}
		return $index;
	}
	/**
	 * テンプレートリスト取得
	 *
	 * @param int      $type		テンプレートのタイプ(0=PC用、1=携帯用、2=スマートフォン)
	 * @param function $callback	コールバック関数
	 * @return						なし
	 */
	function getAllTemplateList($type, $callback)
	{
		// tm_device_typeは後で追加したため、tm_mobileを残しておく
		$queryStr = 'SELECT * FROM _templates ';
		$queryStr .=  'WHERE tm_deleted = false ';// 削除されていない
		$queryStr .=     'AND tm_available = true ';
		$params = array();
		switch ($type){
			case 0:		// PC用テンプレート
			case 2:		// スマートフォン用テンプレート
			default:
				$queryStr .=    'AND tm_mobile = false ';		// 携帯用以外
				$queryStr .=    'AND tm_device_type = ? '; $params[] = $type;
				break;
			case 1:		// 携帯用のとき
				$queryStr .=    'AND tm_mobile = true ';		// 携帯用
				break;
		}
		$queryStr .=  'ORDER BY tm_id';
		$this->selectLoop($queryStr, $params, $callback);
	}
	/**
	 * テンプレート情報の取得
	 *
	 * @param string  $id			テンプレートID
	 * @return						true=正常、false=異常
	 */
	function getTemplate($id, &$row)
	{
		$queryStr  = 'SELECT * FROM _templates ';
		$queryStr .=   'WHERE tm_id = ? ';
		$queryStr .=   'AND tm_deleted = false';
		$ret = $this->selectRecord($queryStr, array($id), $row);
		return $ret;
	}
	/**
	 * ユーザリスト取得
	 *
	 * @param int      $minLevel	最小のユーザレベル
	 * @param function $callback	コールバック関数
	 * @return						なし
	 */
	function getUserList($minLevel, $callback)
	{
		$queryStr  = 'SELECT * FROM _login_user ';
		$queryStr .=   'WHERE lu_deleted = false ';// 削除されていない
		$queryStr .=     'AND lu_user_type >= ? ';		// ユーザレベル
		$queryStr .=   'ORDER BY lu_user_type, lu_account';
		$this->selectLoop($queryStr, array($minLevel), $callback);
	}
	/**
	 * ブログ記事履歴を取得
	 *
	 * @param string	$entryId			記事ID
	 * @param string	$langId				言語
	 * @param int		$limit				取得する項目数
	 * @param int		$page				取得するページ(1～)
	 * @param function	$callback			コールバック関数
	 * @return 			なし
	 */
	function getEntryHistory($entryId, $langId, $limit, $page, $callback)
	{
		$offset = $limit * ($page -1);
		if ($offset < 0) $offset = 0;
		
		$params = array();
		$queryStr = 'SELECT * FROM blog_entry LEFT JOIN _login_user ON be_create_user_id = lu_id AND lu_deleted = false ';
		$queryStr .=  'WHERE be_id = ? ';$params[] = $entryId;
		$queryStr .=    'AND be_language_id = ? ';$params[] = $langId;
		$queryStr .=    'AND be_history_index >= 0 ';		// 正規(Regular)記事を対象
		$queryStr .=  'ORDER BY be_history_index ';
		$queryStr .=    'DESC ';
		$queryStr .=  'LIMIT ' . $limit . ' OFFSET ' . $offset;
		$this->selectLoop($queryStr, $params, $callback);
	}
	/**
	 * ブログ記事履歴数を取得
	 *
	 * @param string	$entryId			記事ID
	 * @param string	$langId				言語
	 * @return int							項目数
	 */
	function getEntryHistoryCount($entryId, $langId)
	{
		$params = array();
		$queryStr = 'SELECT * FROM blog_entry LEFT JOIN _login_user ON be_create_user_id = lu_id AND lu_deleted = false ';
		$queryStr .=  'WHERE be_id = ? ';$params[] = $entryId;
		$queryStr .=    'AND be_language_id = ? ';$params[] = $langId;
		$queryStr .=    'AND be_history_index >= 0 ';		// 正規(Regular)記事を対象
		return $this->selectRecordCount($queryStr, $params);
	}
	/**
	 * ブログ予約記事を取得
	 *
	 * @param string	$entryId			記事ID
	 * @param string	$langId				言語
	 * @param int		$limit				取得する項目数
	 * @param int		$page				取得するページ(1～)
	 * @param function	$callback			コールバック関数
	 * @return 			なし
	 */
	function getEntrySchedule($entryId, $langId, $limit, $page, $callback)
	{
		$offset = $limit * ($page -1);
		if ($offset < 0) $offset = 0;
		
		$params = array();
		$queryStr = 'SELECT * FROM blog_entry LEFT JOIN _login_user ON be_create_user_id = lu_id AND lu_deleted = false ';
		$queryStr .=  'WHERE be_deleted = false ';
		$queryStr .=    'AND be_id = ? ';$params[] = $entryId;
		$queryStr .=    'AND be_language_id = ? ';$params[] = $langId;
		$queryStr .=    'AND be_history_index <= -1000 ';		// 予約(Scheduled)記事を対象
		$queryStr .=  'ORDER BY be_active_start_dt, be_history_index ';
		$queryStr .=  'LIMIT ' . $limit . ' OFFSET ' . $offset;
		$this->selectLoop($queryStr, $params, $callback);
	}
	/**
	 * ブログ予約記事数を取得
	 *
	 * @param string	$entryId			記事ID
	 * @param string	$langId				言語
	 * @return int							項目数
	 */
	function getEntryScheduleCount($entryId, $langId)
	{
		$params = array();
		$queryStr = 'SELECT * FROM blog_entry LEFT JOIN _login_user ON be_create_user_id = lu_id AND lu_deleted = false ';
		$queryStr .=  'WHERE be_deleted = false ';
		$queryStr .=    'AND be_id = ? ';$params[] = $entryId;
		$queryStr .=    'AND be_language_id = ? ';$params[] = $langId;
		$queryStr .=    'AND be_history_index <= -1000 ';		// 予約(Scheduled)記事を対象
		return $this->selectRecordCount($queryStr, $params);
	}
	/**
	 * 予約記事の新規追加
	 *
	 * @param string  $id			記事ID
	 * @param string  $langId		言語ID
	 * @param string  $html			HTML
	 * @param string  $html2		HTML(続き)
	 * @param timestamp	$startDt	期間(開始日)
	 * @param timestamp	$endDt		期間(終了日)
	 * @param int     $newSerial	新規シリアル番号
	 * @param array   $otherParams	その他のフィールド値
	 * @return bool					true = 成功、false = 失敗
	 */
	function addEntryScheduleItem($id, $langId, $html, $html2, $startDt, $endDt, &$newSerial, $otherParams = null)
	{
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $this->gEnv->getCurrentUserId();	// 現在のユーザ
			
		// トランザクション開始
		$this->startTransaction();
		
		// 次の履歴IDを取得
		$historyIndex = self::INIT_HISTORY_INDEX_FOR_SCHEDULE;				// 予約記事履歴番号を初期化
		$queryStr  = 'SELECT MIN(be_history_index) AS minh FROM blog_entry ';
		$queryStr .=   'WHERE be_id = ? ';
		$queryStr .=     'AND be_language_id = ? ';
		$queryStr .=     'AND be_history_index <= ? ';		// 正規(Regular)記事を対象
		$ret = $this->selectRecord($queryStr, array($id, $langId, self::INIT_HISTORY_INDEX_FOR_SCHEDULE), $row);
		if ($ret){
			if (!is_null($row['minh'])) $historyIndex = intval($row['minh']) - 1;
		}

		// 期間の値を修正
		if (is_null($startDt)) $startDt = $this->gEnv->getInitValueOfTimestamp();
		if (is_null($endDt)) $endDt = $this->gEnv->getInitValueOfTimestamp();
		
		// データを追加
		$params = array($id, $langId, $historyIndex, $html, $html2, $startDt, $endDt, $userId, $now);
												
		$queryStr  = 'INSERT INTO blog_entry ';
		$queryStr .=   '(be_id, ';
		$queryStr .=   'be_language_id, ';
		$queryStr .=   'be_history_index, ';
		$queryStr .=   'be_html, ';
		$queryStr .=   'be_html_ext, ';
		$queryStr .=   'be_active_start_dt, ';
		$queryStr .=   'be_active_end_dt, ';
		$queryStr .=   'be_update_user_id, ';			// 更新履歴は管理しないのでupdateを使用する
		$queryStr .=   'be_update_dt ';
		
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
		$queryStr .=   '(?, ?, ?, ?, ?, ?, ?, ?, ?' . $otherValueStr . ')';
		$this->execStatement($queryStr, $params);
		
		// 新規のシリアル番号取得
		$newSerial = 0;
		$queryStr = 'SELECT MAX(be_serial) AS ns FROM blog_entry ';
		$ret = $this->selectRecord($queryStr, array(), $row);
		if ($ret) $newSerial = $row['ns'];

		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * 予約記事の更新
	 *
	 * @param int     $serial		シリアル番号
	 * @param string  $html			HTML
	 * @param string  $html2		HTML(続き)
	 * @param timestamp	$startDt	期間(開始日)
	 * @param timestamp	$endDt		期間(終了日)
	 * @param array   $otherParams	その他のフィールド値
	 * @return bool					true = 成功、false = 失敗
	 */
	function updateEntryScheduleItem($serial, $html, $html2, $startDt, $endDt, $otherParams = null)
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
		
		// 期間の値を修正
		if (is_null($startDt)) $startDt = $this->gEnv->getInitValueOfTimestamp();
		if (is_null($endDt)) $endDt = $this->gEnv->getInitValueOfTimestamp();
		
		// 既存項目を更新
		$params = array();
		$queryStr  = 'UPDATE blog_entry ';
		$queryStr .=   'SET ';
		if (!empty($otherParams)){
			$keys = array_keys($otherParams);// キーを取得
			for ($i = 0; $i < count($keys); $i++){
				$fieldName = $keys[$i];
				$fieldValue = $otherParams[$fieldName];
				if (!isset($fieldValue)) continue;
				$queryStr .= $fieldName . ' = ?, ';
				$params[] = $fieldValue;
			}
		}
		$queryStr .=     'be_html = ?, ';
		$queryStr .=     'be_html_ext = ?, ';
		$queryStr .=     'be_active_start_dt = ?, ';
		$queryStr .=     'be_active_end_dt = ?, ';
		$queryStr .=     'be_update_user_id = ?, ';
		$queryStr .=     'be_update_dt = ? ';
		$queryStr .=   'WHERE be_serial = ? ';
		$ret = $this->execStatement($queryStr, array_merge($params, array($html, $html2, $startDt, $endDt, $userId, $now, intval($serial))));
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * プレビュー用記事の更新
	 *
	 * 機能：記事IDは負の値に変換。
	 *
	 * @param string  $id			記事ID
	 * @param string  $langId		言語ID
	 * @param string  $html			HTML
	 * @param string  $html2		HTML(続き)
	 * @param array   $category		カテゴリーID
	 * @param array   $otherParams	その他のフィールド値
	 * @param int     $serial		更新対象レコードのシリアル番号
	 * @return bool					true = 成功、false = 失敗
	 */
	function updateEntryPreview($id, $langId, $html, $html2, $category, $otherParams, &$serial)
	{
		// パラメータエラーチェック
		if ($id < 0) return false;
		
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $this->gEnv->getCurrentUserId();	// 現在のユーザ
		$entryId = intval($id) * (-1);						// 記事IDを負の値に変換
		$historyIndex = $userId * (-1);				// ユーザIDを負の値に変換
		
		// トランザクション開始
		$this->startTransaction();
		
		// レコードの状態チェック
		$queryStr  = 'SELECT * FROM blog_entry ';
		$queryStr .=   'WHERE be_id = ? ';
		$queryStr .=     'AND be_language_id = ? ';
		$queryStr .=     'AND be_history_index = ? ';
		$ret = $this->selectRecord($queryStr, array($entryId, $langId, $historyIndex), $row);
		if ($ret){
			// データ存在している場合は一旦削除
			$serial = $row['be_serial'];
			$queryStr  = 'DELETE FROM blog_entry WHERE be_serial = ?';
			$this->execStatement($queryStr, array($serial));
			
			// カテゴリー削除
			$queryStr  = 'DELETE FROM blog_entry_with_category WHERE bw_entry_serial = ?';
			$this->execStatement($queryStr, array($serial));
		}

		// 新規レコードを追加
		$queryStr  = 'INSERT INTO blog_entry ';
		$queryStr .=   '(be_id, ';
		$queryStr .=   'be_language_id, ';
		$queryStr .=   'be_history_index, ';
		$queryStr .=   'be_html, ';
		$queryStr .=   'be_html_ext, ';
		$queryStr .=   'be_create_user_id, ';
		$queryStr .=   'be_create_dt ';

		// その他のフィールド値を追加
		$params = array();
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
		$queryStr .=   '(?, ?, ?, ?, ?, ?, ?' . $otherValueStr . ')';
		$this->execStatement($queryStr, array_merge(array($entryId, $langId, $historyIndex, $html, $html2, $userId, $now), $params));
	
		// 新規のシリアル番号取得
		$serial = 0;
		$queryStr = 'SELECT MAX(be_serial) AS ns FROM blog_entry ';
		$ret = $this->selectRecord($queryStr, array(), $row);
		if ($ret) $serial = $row['ns'];
		
		// 記事カテゴリーの更新
		for ($i = 0; $i < count($category); $i++){
			$ret = $this->updateEntryCategory($serial, $i, $category[$i]);
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
	 * プレビュー用記事を取得
	 *
	 * @param int,array		$id				エントリーID
	 * @param string	$langId				言語
	 * @param array     $row				レコード
	 * @return bool							取得 = true, 取得なし= false
	 */
	function getEntryPreview($id, $langId, &$row)
	{
		// パラメータエラーチェック
		if ($id < 0) return false;
		
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $this->gEnv->getCurrentUserId();	// 現在のユーザ
		$entryId = intval($id) * (-1);						// 記事IDを負の値に変換
		$historyIndex = $userId * (-1);				// ユーザIDを負の値に変換
		
//		$queryStr  = 'SELECT * FROM blog_entry LEFT JOIN blog_id ON be_blog_id = bl_id AND bl_deleted = false ';
		$queryStr  = 'SELECT * FROM blog_entry LEFT JOIN blog_id ON be_blog_id = bl_id AND bl_deleted = false ';
		$queryStr .=   'LEFT JOIN _login_user ON be_regist_user_id = lu_id AND lu_deleted = false ';
		$queryStr .=   'WHERE be_id = ? ';
		$queryStr .=     'AND be_language_id = ? ';
		$queryStr .=     'AND be_history_index = ? ';
		$ret = $this->selectRecord($queryStr, array($entryId, $langId, $historyIndex), $row);

		return $ret;
	}
	/**
	 * すべてのプレビュー用記事を削除
	 *
	 * @return bool							true=成功, false=失敗
	 */
	function delAllEntryPreview()
	{
		$userId = $this->gEnv->getCurrentUserId();	// 現在のユーザ
		$historyIndex = $userId * (-1);				// ユーザIDを負の値に変換

		// トランザクション開始
		$this->startTransaction();
		
		// 削除対象の記事のシリアル番号を取得
		$queryStr  = 'SELECT * FROM blog_entry ';
		$queryStr .=   'WHERE be_id <= 0 ';
		$queryStr .=     'AND be_history_index = ? ';
		$ret = $this->selectRecords($queryStr, array($historyIndex), $rows);
		if ($ret){
			for ($i = 0; $i < count($rows); $i++){
				$serial = $rows[$i]['be_serial'];
				
				// カテゴリーを削除
				$queryStr  = 'DELETE FROM blog_entry_with_category WHERE bw_entry_serial = ?';
				$this->execStatement($queryStr, array($serial));
			}
		}

		// 記事を削除
		$queryStr  = 'DELETE FROM blog_entry ';
		$queryStr .=   'WHERE be_id <= 0 ';
		$queryStr .=     'AND be_history_index = ? ';
		$this->execStatement($queryStr, array($historyIndex));
				
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
}
?>
