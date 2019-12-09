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

class s_blogDb extends BaseDb
{
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
	 * @param function	$callback			コールバック関数
	 * @param string	$blogId				ブログID(未指定の場合はnull)
	 * @return 			なし
	 */
	function searchEntryItems($limit, $page, $startDt, $endDt, $category, $keyword, $langId, $callback, $blogId = null)
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
		// 商品名、商品コード、説明を検索
		if (!empty($keyword)){
			// 「'"\」文字をエスケープ
			$keyword = addslashes($keyword);
			
			$queryStr .=    'AND (be_name LIKE \'%' . $keyword . '%\' ';
			$queryStr .=    'OR be_html LIKE \'%' . $keyword . '%\' ';
			$queryStr .=    'OR be_html_ext LIKE \'%' . $keyword . '%\' ';
			$queryStr .=    'OR be_description LIKE \'%' . $keyword . '%\') ';
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
			$this->selectLoop($queryStr, $params, $callback, null);
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
			$this->selectLoop($queryStr, array(), $callback, null);
		}
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
		// 商品名、商品コード、説明を検索
		if (!empty($keyword)){
			// 「'"\」文字をエスケープ
			$keyword = addslashes($keyword);
			
			$queryStr .=    'AND (be_name LIKE \'%' . $keyword . '%\' ';
			$queryStr .=    'OR be_html LIKE \'%' . $keyword . '%\' ';
			$queryStr .=    'OR be_html_ext LIKE \'%' . $keyword . '%\' ';
			$queryStr .=    'OR be_description LIKE \'%' . $keyword . '%\') ';
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
	 * エントリー項目を検索(表示用)
	 *
	 * @param int		$limit				取得する項目数
	 * @param int		$page				取得するページ(1～)
	 * @param timestamp $now				現在日時(現在日時より未来の投稿日時の記事は取得しない)
	 * @param array		$keywords			検索キーワード
	 * @param string	$langId				言語
	 * @param function	$callback			コールバック関数
	 * @return 			なし
	 */
	function searchEntryItemsByKeyword($limit, $page, $now, $keywords, $langId, $callback)
	{
		$offset = $limit * ($page -1);
		if ($offset < 0) $offset = 0;
		$initDt = $this->gEnv->getInitValueOfTimestamp();		// 日時初期化値
		$params = array();
		
		//$queryStr = 'SELECT * FROM blog_entry ';
		$queryStr  = 'SELECT * FROM blog_entry LEFT JOIN blog_id ON be_blog_id = bl_id AND bl_deleted = false ';
		$queryStr .=   'WHERE be_language_id = ? ';	$params[] = $langId;
		$queryStr .=     'AND be_deleted = false ';		// 削除されていない
		$queryStr .=     'AND be_history_index >= 0 ';		// 正規(Regular)記事を対象
		$queryStr .=     'AND be_regist_dt <= ? ';	$params[] = $now;	// 投稿日時が現在日時よりも過去のものを取得

		// タイトルと記事を検索
		if (!empty($keywords)){
			for ($i = 0; $i < count($keywords); $i++){
				$keyword = addslashes($keywords[$i]);// 「'"\」文字をエスケープ
				$queryStr .=    'AND (be_name LIKE \'%' . $keyword . '%\' ';
				$queryStr .=    'OR be_html LIKE \'%' . $keyword . '%\' ';
				$queryStr .=    'OR be_html_ext LIKE \'%' . $keyword . '%\' ';
				$queryStr .=    'OR be_description LIKE \'%' . $keyword . '%\') ';
			}
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
		
		$queryStr .=  'ORDER BY be_regist_dt desc limit ' . $limit . ' offset ' . $offset;
		$this->selectLoop($queryStr, $params, $callback);
	}
	/**
	 * 検索条件のエントリー項目数を取得(表示用)
	 *
	 * @param timestamp $now				現在日時(現在日時より未来の投稿日時の記事は取得しない)
	 * @param array		$keywords			検索キーワード
	 * @param string	$langId				言語
	 * @return int							項目数
	 */
	function searchEntryItemsCountByKeyword($now, $keywords, $langId)
	{
		$initDt = $this->gEnv->getInitValueOfTimestamp();		// 日時初期化値
		$params = array();
		
		$queryStr = 'SELECT * FROM blog_entry ';
		$queryStr .=  'WHERE be_language_id = ? ';	$params[] = $langId;
		$queryStr .=    'AND be_deleted = false ';		// 削除されていない
		$queryStr .=    'AND be_history_index >= 0 ';		// 正規(Regular)記事を対象
		$queryStr .=    'AND be_regist_dt <= ? ';	$params[] = $now;	// 投稿日時が現在日時よりも過去のものを取得

		// タイトルと記事を検索
		if (!empty($keywords)){
			for ($i = 0; $i < count($keywords); $i++){
				$keyword = addslashes($keywords[$i]);// 「'"\」文字をエスケープ
				$queryStr .=    'AND (be_name LIKE \'%' . $keyword . '%\' ';
				$queryStr .=    'OR be_html LIKE \'%' . $keyword . '%\' ';
				$queryStr .=    'OR be_html_ext LIKE \'%' . $keyword . '%\' ';
				$queryStr .=    'OR be_description LIKE \'%' . $keyword . '%\') ';
			}
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
		return $this->selectRecordCount($queryStr, $params);
	}
	/**
	 * エントリー項目の新規追加
	 *
	 * @param string  $id			エントリーID
	 * @param string  $langId		言語ID
	 * @param string  $name			コンテンツ名
	 * @param string  $html			HTML
	 * @param string  $html2		HTML(続き)
	 * @param int     $status		エントリー状態(0=未設定、1=編集中、2=公開、3=非公開)
	 * @param array   $category		カテゴリーID
	 * @param string  $blogId		ブログID
	 * @param int     $regUserId	投稿者ユーザID
	 * @param timestamp $regDt		投稿日時
	 * @param timestamp	$startDt	期間(開始日)
	 * @param timestamp	$endDt		期間(終了日)
	 * @param int     $newSerial	新規シリアル番号
	 * @param bool    $showComment	コメントを表示するかどうか
	 * @param bool $receiveComment	コメントを受け付けるかどうか
	 * @return bool					true = 成功、false = 失敗
	 */
	function addEntryItem($id, $langId, $name, $html, $html2, $status, $category, $blogId, $regUserId, $regDt, $startDt, $endDt, $showComment, $receiveComment, &$newSerial)
	{
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $this->gEnv->getCurrentUserId();	// 現在のユーザ
			
		// トランザクション開始
		$this->startTransaction();
		
		if ($id == 0){		// エントリーIDが0のときは、エントリーIDを新規取得
			// エントリーIDを決定する
			$queryStr = 'select max(be_id) as mid from blog_entry ';
			$ret = $this->selectRecord($queryStr, array(), $row);
			if ($ret){
				$entryId = $row['mid'] + 1;
			} else {
				$entryId = 1;
			}
		} else {
			$entryId = $id;
		}
		
		// 前レコードの削除状態チェック
		$historyIndex = 0;
		$queryStr = 'SELECT * FROM blog_entry ';
		$queryStr .=  'WHERE be_id = ? ';
		$queryStr .=    'AND be_language_id = ? ';
		$queryStr .=    'AND be_history_index >= 0 ';		// 正規(Regular)記事を対象
		$queryStr .=  'ORDER BY be_history_index DESC ';
		$ret = $this->selectRecord($queryStr, array($entryId, $langId), $row);
		if ($ret){
			if (!$row['be_deleted']){		// レコード存在していれば終了
				$this->endTransaction();
				return false;
			}
			$historyIndex = $row['be_history_index'] + 1;
		}
		
		// データを追加
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
		$queryStr .=   'be_create_dt) ';
		$queryStr .= 'VALUES ';
		$queryStr .=   '(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
		$this->execStatement($queryStr, array($entryId, $langId, $historyIndex, $name, $html, $html2, $status, 
												intval($showComment), intval($receiveComment), $blogId, $now, $regUserId, $regDt, $startDt, $endDt, $userId, $now));
		
		// 新規のシリアル番号取得
		$newSerial = 0;
		$queryStr = 'select max(be_serial) as ns from blog_entry ';
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
	 * エントリー項目の更新
	 *
	 * @param int     $serial		シリアル番号
	 * @param string  $name			コンテンツ名
	 * @param string  $html			HTML
	 * @param string  $html2		HTML(続き)
	 * @param int     $status		エントリー状態(0=未設定、1=編集中、2=公開、3=非公開)
	 * @param array   $category		カテゴリーID
	 * @param string  $blogId		ブログID
	 * @param int     $regUserId	投稿者ユーザID(0のときは更新しない)
	 * @param timestamp $regDt		投稿日時(空のときは更新しない)
	 * @param timestamp	$startDt	期間(開始日)
	 * @param timestamp	$endDt		期間(終了日)
	 * @param bool    $showComment	コメントを表示するかどうか
	 * @param bool $receiveComment	コメントを受け付けるかどうか
	 * @param int     $newSerial	新規シリアル番号
	 * @return bool					true = 成功、false = 失敗
	 */
	function updateEntryItem($serial, $name, $html, $html2, $status, $category, $blogId, $regUserId, $regDt, $startDt, $endDt, $showComment, $receiveComment, &$newSerial)
	{
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $this->gEnv->getCurrentUserId();	// 現在のユーザ
						
		// トランザクション開始
		$this->startTransaction();
		
		// 指定のシリアルNoのレコードが削除状態でないかチェック
		$historyIndex = 0;		// 履歴番号
		$queryStr  = 'select * from blog_entry ';
		$queryStr .=   'where be_serial = ? ';
		$ret = $this->selectRecord($queryStr, array($serial), $row);
		if ($ret){		// 既に登録レコードがあるとき
			if ($row['be_deleted']){		// レコードが削除されていれば終了
				$this->endTransaction();
				return false;
			}
			$historyIndex = $row['be_history_index'] + 1;
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
		$this->execStatement($queryStr, array($userId, $now, $serial));
		
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
		$queryStr .=   'be_create_dt) ';
		$queryStr .= 'VALUES ';
		$queryStr .=   '(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
		$this->execStatement($queryStr, array($entryId, $langId, $historyIndex, $name, $html, $html2, $status, 
												intval($showComment), intval($receiveComment), $blogId, $now, $rUserId, $rDt, $startDt, $endDt, $userId, $now));

		// 新規のシリアル番号取得
		$newSerial = 0;
		$queryStr = 'select max(be_serial) as ns from blog_entry ';
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
		$ret = $this->selectRecord($queryStr, array($serial), $row);
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
		$this->execStatement($queryStr, array($now, $userId, $now, $serial));
		
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
		$ret =$this->execStatement($queryStr, array($serial, $index, $categoryId));
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
		$queryStr  = 'select *,reg.lu_name as reg_user_name from blog_entry LEFT JOIN _login_user ON be_create_user_id = lu_id AND lu_deleted = false ';
		$queryStr .=   'LEFT JOIN _login_user as reg ON be_regist_user_id = reg.lu_id AND reg.lu_deleted = false ';
		$queryStr .=   'WHERE be_serial = ? ';
		$ret = $this->selectRecord($queryStr, array($serial), $row);
		
		if ($ret){
			// ブログカテゴリー
			$queryStr  = 'SELECT * FROM blog_entry_with_category LEFT JOIN blog_category ON bw_category_id = bc_id AND bc_deleted = false ';
			$queryStr .=   'WHERE bw_entry_serial = ? ';
			$queryStr .=  'ORDER BY bw_index ';
			$this->selectRecords($queryStr, array($serial), $categoryRow);
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
	 * @param array   $serial		シリアルNo
	 * @return						true=成功、false=失敗
	 */
	function delEntryItemById($serial)
	{
		// 引数のエラーチェック
		if (!is_array($serial)) return false;
		if (count($serial) <= 0) return true;
		
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $this->gEnv->getCurrentUserId();	// 現在のユーザ
		
		// トランザクション開始
		$this->startTransaction();
		
		// コンテンツIDを取得
		$queryStr  = 'select * from blog_entry ';
		$queryStr .=   'where be_deleted = false ';		// 未削除
		$queryStr .=     'and be_serial = ? ';
		$ret = $this->selectRecord($queryStr, array($serial), $row);
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
	 * @param int		$id					エントリーID
	 * @param string	$langId				言語
	 * @param array     $row				レコード
	 * @return bool							取得 = true, 取得なし= false
	 */
	function getEntryItem($id, $langId, &$row)
	{
		$queryStr  = 'SELECT * FROM blog_entry LEFT JOIN blog_id ON be_blog_id = bl_id AND bl_deleted = false ';
		$queryStr .=   'WHERE be_deleted = false ';	// 削除されていない
		$queryStr .=   'AND be_history_index >= 0 ';		// 正規(Regular)記事を対象
		$queryStr .=   'AND be_id = ? ';
		$queryStr .=   'AND be_language_id = ? ';
		$ret = $this->selectRecord($queryStr, array($id, $langId), $row);
		return $ret;
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
	 * @param string	$langId				言語
	 * @param int		$order				取得順(0=昇順,1=降順)
	 * @param function	$callback			コールバック関数
	 * @param string	$blogId				ブログID(nullのとき指定なし)
	 * @return 			なし
	 */
	function getEntryItems($limit, $page, $now, $entryId, $startDt, $endDt, $langId, $order, $callback, $blogId = null)
	{
		$offset = $limit * ($page -1);
		if ($offset < 0) $offset = 0;
		$initDt = $this->gEnv->getInitValueOfTimestamp();		// 日時初期化値
		$params = array();
		
		// エントリーIDの指定がない場合は、期間で取得
		if (empty($entryId)){
			$queryStr  = 'SELECT * FROM blog_entry LEFT JOIN blog_id ON be_blog_id = bl_id AND bl_deleted = false ';
			$queryStr .=   'WHERE be_deleted = false ';		// 削除されていない
			$queryStr .=     'AND be_history_index >= 0 ';		// 正規(Regular)記事を対象
			$queryStr .=     'AND be_status = ? ';		$params[] = 2;	// 「公開」(2)データを表示
			$queryStr .=     'AND be_language_id = ? ';	$params[] = $langId;
			$queryStr .=     'AND be_regist_dt <= ? ';	$params[] = $now;		// 投稿日時が現在日時よりも過去のものを取得
			
			// ブログID
			if (isset($blogId)){
				$queryStr .=    'AND be_blog_id = ? ';		$params[] = $blogId;
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
			
			// 公開期間を指定
			$queryStr .=    'AND (be_active_start_dt = ? OR (be_active_start_dt != ? AND be_active_start_dt <= ?)) ';
			$queryStr .=    'AND (be_active_end_dt = ? OR (be_active_end_dt != ? AND be_active_end_dt > ?)) ';
			$params[] = $initDt;
			$params[] = $initDt;
			$params[] = $now;
			$params[] = $initDt;
			$params[] = $initDt;
			$params[] = $now;
		
			$ord = '';
			if (!empty($order)) $ord = 'DESC ';
			$queryStr .=  'ORDER BY be_regist_dt ' . $ord . 'LIMIT ' . $limit . ' offset ' . $offset;// 投稿順
			$this->selectLoop($queryStr, $params, $callback, null);
		} else {
			//$queryStr = 'SELECT * FROM blog_entry ';
			$queryStr  = 'SELECT * FROM blog_entry LEFT JOIN blog_id ON be_blog_id = bl_id AND bl_deleted = false ';
			$queryStr .=   'WHERE be_deleted = false ';		// 削除されていない
			$queryStr .=     'AND be_history_index >= 0 ';		// 正規(Regular)記事を対象
			$queryStr .=     'AND be_status = ? ';	$params[] = 2;	// 「公開」(2)データを表示
			$queryStr .=     'AND be_id = ? ';		$params[] = $entryId;
			$queryStr .=     'AND be_language_id = ? ';	$params[] = $langId;
			$queryStr .=     'AND be_regist_dt <= ? ';	$params[] = $now;		// 投稿日時が現在日時よりも過去のものを取得
			
			// ブログID
			if (isset($blogId)){
				$queryStr .=    'AND be_blog_id = ? ';		$params[] = $blogId;
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
			$this->selectLoop($queryStr, $params, $callback, null);		// 「公開」(2)データを表示
		}
	}
	
	/**
	 * エントリー項目数を取得(表示用)
	 *
	 * @param timestamp $now				現在日時(現在日時より未来の投稿日時の記事は取得しない)
	 * @param timestamp	$startDt			期間(開始日)
	 * @param timestamp	$endDt				期間(終了日)
	 * @param string	$langId				言語
	 * @param string	$blogId				ブログID(nullのとき指定なし)
	 * @return int							項目数
	 */
	function getEntryItemsCount($now, $startDt, $endDt, $langId, $blogId = null)
	{
		$initDt = $this->gEnv->getInitValueOfTimestamp();		// 日時初期化値
		$params = array();
		
		$queryStr = 'SELECT * FROM blog_entry ';
		$queryStr .=  'WHERE be_deleted = false ';		// 削除されていない
		$queryStr .=    'AND be_history_index >= 0 ';		// 正規(Regular)記事を対象
		$queryStr .=    'AND be_status = ? ';		$params[] = 2;	// 「公開」(2)データを表示
		$queryStr .=    'AND be_language_id = ? ';	$params[] = $langId;
		$queryStr .=    'AND be_regist_dt <= ? ';	$params[] = $now;		// 投稿日時が現在日時よりも過去のものを取得
		
		// ブログID
		if (isset($blogId)){
			$queryStr .=    'AND be_blog_id = ? ';		$params[] = $blogId;
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
		
		// 公開期間を指定
		$queryStr .=    'AND (be_active_start_dt = ? OR (be_active_start_dt != ? AND be_active_start_dt <= ?)) ';
		$queryStr .=    'AND (be_active_end_dt = ? OR (be_active_end_dt != ? AND be_active_end_dt > ?)) ';
		$params[] = $initDt;
		$params[] = $initDt;
		$params[] = $now;
		$params[] = $initDt;
		$params[] = $initDt;
		$params[] = $now;
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
	 * @return 			なし
	 */
	function getEntryItemsByCategory($limit, $page, $now, $categoryId, $langId, $order, $callback)
	{
		$offset = $limit * ($page -1);
		if ($offset < 0) $offset = 0;
		$initDt = $this->gEnv->getInitValueOfTimestamp();		// 日時初期化値
		$params = array();
		
		$queryStr = 'SELECT distinct(be_serial) FROM blog_entry RIGHT JOIN blog_entry_with_category ON be_serial = bw_entry_serial ';
		$queryStr .=  'WHERE be_language_id = ? '; $params[] = $langId;
		$queryStr .=    'AND be_deleted = false ';		// 削除されていない
		$queryStr .=    'AND be_history_index >= 0 ';		// 正規(Regular)記事を対象
		$queryStr .=    'AND be_status = ? '; $params[] = 2;	// 「公開」(2)データ
		$queryStr .=    'AND bw_category_id = ? ';	$params[] = $categoryId;// 記事カテゴリー
		$queryStr .=    'AND be_regist_dt <= ? ';	$params[] = $now;			// 投稿日時が現在日時よりも過去のものを取得
		
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
	
		//$queryStr = 'SELECT * FROM blog_entry ';
		$queryStr  = 'SELECT * FROM blog_entry LEFT JOIN blog_id ON be_blog_id = bl_id AND bl_deleted = false ';
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
	 * @return int							エントリー項目数
	 */
	function getEntryItemsCountByCategory($now, $categoryId, $langId)
	{
		$initDt = $this->gEnv->getInitValueOfTimestamp();		// 日時初期化値
		$params = array();
		
		$queryStr = 'SELECT distinct(be_serial) FROM blog_entry RIGHT JOIN blog_entry_with_category ON be_serial = bw_entry_serial ';
		$queryStr .=  'WHERE be_language_id = ? '; $params[] = $langId;
		$queryStr .=    'AND be_deleted = false ';		// 削除されていない
		$queryStr .=    'AND be_history_index >= 0 ';		// 正規(Regular)記事を対象
		$queryStr .=    'AND be_status = ? '; $params[] = 2;	// 「公開」(2)データ
		$queryStr .=    'AND bw_category_id = ? ';	$params[] = $categoryId;// 記事カテゴリー
		$queryStr .=    'AND be_regist_dt <= ? ';	$params[] = $now;			// 投稿日時が現在日時よりも過去のものを取得
		
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
	
		$queryStr = 'SELECT * FROM blog_entry ';
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
		$queryStr .=  'ORDER BY bc_id';
		$retValue = $this->selectRecords($queryStr, array($langId), $rows);
		return $retValue;
	}
	/**
	 * 記事が指定ブログ属するかチェック
	 *
	 * @param int    $serial		記事のシリアルNo
	 * @param string $blogId		ブログID
	 * @return bool					true=存在する、false=存在しない
	 */
	function isExistsEntryInBlogId($serial, $blogId)
	{
		$queryStr  = 'SELECT * FROM blog_entry ';
		$queryStr .=   'WHERE be_deleted = false ';		// 削除されていない
		$queryStr .=     'AND be_serial = ? ';
		$queryStr .=     'AND be_blog_id = ? ';
		return $this->isRecordExists($queryStr, array($serial, $blogId));
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
		return $this->isRecordExists($queryStr, array($serial, $blogId));
	}
	/**
	 * すべてのブログIDを取得
	 *
	 * @param array   $rows			取得レコード
	 * @return bool					取得 = true, 取得なし= false
	 */
	function getAllBlogId(&$rows)
	{
		$queryStr  = 'SELECT * FROM blog_id ';
		$queryStr .=  'WHERE bl_deleted = false ';		// 削除されていない
		$queryStr .=  'ORDER BY bl_index, bl_id';
		$retValue = $this->selectRecords($queryStr, array(), $rows);
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
		$ret = $this->selectRecord($queryStr, array($serial), $row);
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
		$queryStr = 'SELECT * FROM blog_id ';
		$queryStr .=  'WHERE bl_deleted = false ';
		$queryStr .=  'AND bl_visible = true ';
		$queryStr .=  'AND bl_id = ? ';
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
	 * @param bool $visible			表示制御
	 * @param string  $metaTitle	METAタグ、タイトル
	 * @param string  $metaDesc		METAタグ、ページ要約
	 * @param string  $metaKeyword	METAタグ、検索用キーワード
	 * @param int    $ownerId		所有者ID
	 * @param int $newSerial	新規シリアル番号
	 * @return					true = 正常、false=異常
	 */
	function updateBlogInfo($serial, $id, $name, $index, $templateId, $visible, $metaTitle, $metaDesc, $metaKeyword, $ownerId, &$newSerial)
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
			$ret = $this->selectRecord($queryStr, array($serial), $row);
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
			$ret = $this->execStatement($queryStr, array($userId, $now, $serial));
			if (!$ret){
				$this->endTransaction();
				return false;
			}
		}
		
		// データを追加
		$queryStr = 'INSERT INTO blog_id ';
		$queryStr .=  '(bl_id, bl_history_index, bl_name, bl_index, bl_template_id, bl_visible, bl_meta_title, bl_meta_description, bl_meta_keywords, bl_owner_id, bl_content_update_dt, bl_create_user_id, bl_create_dt) ';
		$queryStr .=  'VALUES ';
		$queryStr .=  '(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
		$this->execStatement($queryStr, array($id, $historyIndex, $name, $index, $templateId, intval($visible), $metaTitle, $metaDesc, $metaKeyword, intval($ownerId), $contentUpdateDt, $userId, $now));
		
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
}
?>
