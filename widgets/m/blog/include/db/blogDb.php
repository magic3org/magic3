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
 * @copyright  Copyright 2006-2010 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: blogDb.php 3835 2010-11-17 04:26:15Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getDbPath() . '/baseDb.php');

class blogDb extends BaseDb
{
	/**
	 * ブログ定義値をすべて取得
	 *
	 * 掲示板IDが空のデフォルト値は常に読み込む
	 *
	 * @param array  $rows			レコード
	 * @param string $blogId		ブログID(空の場合はデフォルト値を取得)
	 * @return bool					1行以上取得 = true, 取得なし= false
	 */
	function getAllConfig(&$rows, $blogId = '')
	{
		$queryStr  = 'SELECT * FROM blog_config ';
		$queryStr .=  'WHERE bg_blog_id = \'\' ';
		$queryStr .=   'OR bg_blog_id = ? ';
		$queryStr .=   'ORDER BY bg_blog_id, bg_index';
		$retValue = $this->selectRecords($queryStr, array($blogId), $rows);
		return $retValue;
	}
	/**
	 * ブログ定義値を更新
	 *
	 * @param string $key		キーとなる項目値
	 * @param string $value		値
	 * @param string $blogId		掲示板ID
	 * @return					true = 正常、false=異常
	 */
	function updateConfig($key, $value, $blogId = '')
	{
		// トランザクションスタート
		$this->startTransaction();
		
		$queryStr  = 'SELECT bg_value FROM blog_config ';
		$queryStr .=   'WHERE bg_blog_id = ? ';
		$queryStr .=     'AND bg_id = ? ';
		$ret = $this->selectRecord($queryStr, array($blogId, $key), $row);
		if ($ret){
			$queryStr  = 'UPDATE blog_config ';
			$queryStr .=   'SET bg_value = ? ';
			$queryStr .=   'WHERE bg_blog_id = ? ';
			$queryStr .=     'AND bg_id = ? ';
			$ret = $this->execStatement($queryStr, array($value, $blogId, $key));			
		} else {
			$queryStr  = 'INSERT INTO blog_config (';
			$queryStr .=   'bg_blog_id, ';
			$queryStr .=   'bg_id, ';
			$queryStr .=   'bg_value ';
			$queryStr .= ') VALUES (';
			$queryStr .=   '?, ?, ?';
			$queryStr .= ')';
			$ret = $this->execStatement($queryStr, array($blogId, $key, $value));	
		}
		// トランザクション終了
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * エントリー項目を検索(表示用)
	 *
	 * @param int		$limit				取得する項目数
	 * @param int		$page				取得するページ(1～)
	 * @param timestamp $now				現在日時(現在日時より未来の投稿日時の記事は取得しない)
	 * @param string	$keyword			検索キーワード
	 * @param string	$langId				言語
	 * @param function	$callback			コールバック関数
	 * @return 			なし
	 */
	function searchEntryItemsByKeyword($limit, $page, $now, $keyword, $langId, $callback)
	{
		$offset = $limit * ($page -1);
		if ($offset < 0) $offset = 0;
		$initDt = $this->gEnv->getInitValueOfTimestamp();		// 日時初期化値
		$params = array();
		
		$queryStr = 'SELECT * FROM blog_entry ';
		$queryStr .=  'WHERE be_language_id = ? ';	$params[] = $langId;
		$queryStr .=    'AND be_deleted = false ';		// 削除されていない
		$queryStr .=    'AND be_regist_dt <= ? ';	$params[] = $now;	// 投稿日時が現在日時よりも過去のものを取得

		// タイトルと記事を検索
		if (!empty($keyword)){
			// 「'"\」文字をエスケープ
			$keyword = addslashes($keyword);
			
			$queryStr .=    'AND (be_name LIKE \'%' . $keyword . '%\' ';
			$queryStr .=    'OR be_html LIKE \'%' . $keyword . '%\' ';
			$queryStr .=    'OR be_html_ext LIKE \'%' . $keyword . '%\' ';
			$queryStr .=    'OR be_description LIKE \'%' . $keyword . '%\') ';
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
		$this->selectLoop($queryStr, $params, $callback, null);
	}
	/**
	 * 検索条件のエントリー項目数を取得(表示用)
	 *
	 * @param timestamp $now				現在日時(現在日時より未来の投稿日時の記事は取得しない)
	 * @param string	$keyword			検索キーワード
	 * @param string	$langId				言語
	 * @return int							項目数
	 */
	function searchEntryItemsCountByKeyword($now, $keyword, $langId)
	{
		$initDt = $this->gEnv->getInitValueOfTimestamp();		// 日時初期化値
		$params = array();
		
		$queryStr = 'SELECT * FROM blog_entry ';
		$queryStr .=  'WHERE be_language_id = ? ';	$params[] = $langId;
		$queryStr .=    'AND be_deleted = false ';		// 削除されていない
		$queryStr .=    'AND be_regist_dt <= ? ';	$params[] = $now;	// 投稿日時が現在日時よりも過去のものを取得

		// タイトルと記事を検索
		if (!empty($keyword)){
			// 「'"\」文字をエスケープ
			$keyword = addslashes($keyword);
			
			$queryStr .=    'AND (be_name LIKE \'%' . $keyword . '%\' ';
			$queryStr .=    'OR be_html LIKE \'%' . $keyword . '%\' ';
			$queryStr .=    'OR be_html_ext LIKE \'%' . $keyword . '%\' ';
			$queryStr .=    'OR be_description LIKE \'%' . $keyword . '%\') ';
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
	 * @return 			なし
	 */
	function getEntryItems($limit, $page, $now, $entryId, $startDt, $endDt, $langId, $order, $callback)
	{
		$offset = $limit * ($page -1);
		if ($offset < 0) $offset = 0;
		$initDt = $this->gEnv->getInitValueOfTimestamp();		// 日時初期化値
		$params = array();
		
		// エントリーIDの指定がない場合は、期間で取得
		if (empty($entryId)){
			$queryStr = 'SELECT * FROM blog_entry ';
			$queryStr .=  'WHERE be_deleted = false ';		// 削除されていない
			$queryStr .=    'AND be_status = ? ';		$params[] = 2;	// 「公開」(2)データを表示
			$queryStr .=    'AND be_language_id = ? ';	$params[] = $langId;
			$queryStr .=    'AND be_regist_dt <= ? ';	$params[] = $now;		// 投稿日時が現在日時よりも過去のものを取得
			
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
			$queryStr = 'SELECT * FROM blog_entry ';
			$queryStr .=  'WHERE be_deleted = false ';		// 削除されていない
			$queryStr .=    'AND be_status = ? ';	$params[] = 2;	// 「公開」(2)データを表示
			$queryStr .=    'AND be_id = ? ';		$params[] = $entryId;
			$queryStr .=    'AND be_language_id = ? ';	$params[] = $langId;
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
	 * @return int							項目数
	 */
	function getEntryItemsCount($now, $startDt, $endDt, $langId)
	{
		$initDt = $this->gEnv->getInitValueOfTimestamp();		// 日時初期化値
		$params = array();
		
		$queryStr = 'SELECT * FROM blog_entry ';
		$queryStr .=  'WHERE be_deleted = false ';		// 削除されていない
		$queryStr .=    'AND be_status = ? ';		$params[] = 2;	// 「公開」(2)データを表示
		$queryStr .=    'AND be_language_id = ? ';	$params[] = $langId;
		$queryStr .=    'AND be_regist_dt <= ? ';	$params[] = $now;		// 投稿日時が現在日時よりも過去のものを取得
		
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
	 * ブログカテゴリーをカテゴリーIDで取得
	 *
	 * @param int		$id					カテゴリーID
	 * @param string	$langId				言語ID
	 * @param array     $row				レコード
	 * @return bool							取得 = true, 取得なし= false
	 */
	function getCategoryByCategoryId($id, $langId, &$row)
	{
		$queryStr  = 'SELECT * FROM blog_category LEFT JOIN _login_user ON bc_create_user_id = lu_id AND lu_deleted = false ';
		$queryStr .=   'WHERE bc_deleted = false ';	// 削除されていない
		$queryStr .=   'AND bc_id = ? ';
		$queryStr .=   'AND bc_language_id = ? ';
		$ret = $this->selectRecord($queryStr, array($id, $langId), $row);
		return $ret;
	}
}
?>
