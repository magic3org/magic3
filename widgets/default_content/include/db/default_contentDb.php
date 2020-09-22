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

class default_contentDb extends BaseDb
{
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
	 * 汎用コンテンツ定義値を取得
	 *
	 * @param string $contentType	コンテンツタイプ
	 * @param string $key			キーとなる項目値
	 * @return string $value		値
	 */
	function getConfig($contentType, $key)
	{
		$retValue = '';
		$queryStr  = 'SELECT ng_value FROM content_config ';
		$queryStr .=   'WHERE ng_type = ? ';
		$queryStr .=     'AND ng_id  = ? ';
		$ret = $this->selectRecord($queryStr, array($contentType, $key), $row);
		if ($ret) $retValue = $row['ng_value'];
		return $retValue;
	}
	/**
	 * 汎用コンテンツ定義値を更新
	 *
	 * @param string $contentType	コンテンツタイプ
	 * @param string $key			キーとなる項目値
	 * @param string $value			値
	 * @return						true = 正常、false=異常
	 */
	function updateConfig($contentType, $key, $value)
	{
		// データの確認
		$queryStr  = 'SELECT ng_value FROM content_config ';
		$queryStr .=   'WHERE ng_type = ? ';
		$queryStr .=     'AND ng_id  = ? ';
		$ret = $this->isRecordExists($queryStr, array($contentType, $key));
		if ($ret){
			$queryStr = "UPDATE content_config SET ng_value = ? WHERE ng_type = ? AND ng_id = ?";
			return $this->execStatement($queryStr, array($value, $contentType, $key));
		} else {
			$queryStr = "INSERT INTO content_config (ng_type, ng_id, ng_value) VALUES (?, ?, ?)";
			return $this->execStatement($queryStr, array($contentType, $key, $value));
		}
	}
	/**
	 * コンテンツ項目を取得(表示用)
	 *
	 * @param string	$contentType		コンテンツタイプ
	 * @param function	$callback			コールバック関数
	 * @param array		$contentIdArray		コンテンツID
	 * @param string	$lang				言語
	 * @param bool		$all				すべてのデータを取得するか、ユーザ制限のないデータを取得するかを指定
	 * @param string	$now				現在日時
	 * @param bool		$preview			プレビューモードかどうか
	 * @return 			なし
	 */
	function getContentItems($contentType, $callback, $contentIdArray, $lang, $all, $now, $preview)
	{
		$params = array();
		$initDt = $this->gEnv->getInitValueOfTimestamp();
		
		// コンテンツIDの指定がない場合は一覧を取得
		if (empty($contentIdArray)){
			$queryStr = 'SELECT * FROM content ';
			$queryStr .=  'WHERE cn_deleted = false ';		// 削除されていない
			$queryStr .=    'AND cn_type = ? ';
			$queryStr .=    'AND cn_language_id = ? ';
			$params[] = $contentType;
			$params[] = $lang;
			
			if (!$preview){		// プレビューモードでないときは取得制限
				$queryStr .=    'AND cn_visible = true ';
				if (!$all) $queryStr .=    'AND cn_user_limited = false ';		// ユーザ制限のないデータ
			
				// 公開期間を指定
				$queryStr .=    'AND (cn_active_start_dt = ? OR (cn_active_start_dt != ? AND cn_active_start_dt <= ?)) ';
				$queryStr .=    'AND (cn_active_end_dt = ? OR (cn_active_end_dt != ? AND cn_active_end_dt > ?))';
				$params[] = $initDt;
				$params[] = $initDt;
				$params[] = $now;
				$params[] = $initDt;
				$params[] = $initDt;
				$params[] = $now;
			}
//			$queryStr .=  'ORDER BY cn_serial';
			$queryStr .=  'ORDER BY cn_id DESC';		// 降順
			$this->selectLoop($queryStr, $params, $callback);
		} else {
			$contentId = implode(',', $contentIdArray);
			
			// CASE文作成
			$caseStr = 'CASE cn_id ';
			for ($i = 0; $i < count($contentIdArray); $i++){
				$caseStr .= 'WHEN ' . $contentIdArray[$i] . ' THEN ' . $i . ' ';
			}
			$caseStr .= 'END AS no';

			$queryStr = 'SELECT *, ' . $caseStr . ' FROM content ';
			$queryStr .=  'WHERE cn_deleted = false ';		// 削除されていない
			$queryStr .=    'AND cn_type = ? ';
			$queryStr .=    'AND cn_id in (' . $contentId . ') ';
			$queryStr .=    'AND cn_language_id = ? ';
			$params[] = $contentType;
			$params[] = $lang;
			
			if (!$preview){		// プレビューモードでないときは取得制限
				$queryStr .=    'AND cn_visible = true ';
				if (!$all) $queryStr .=    'AND cn_user_limited = false ';		// ユーザ制限のないデータ
			
				// 公開期間を指定
				$queryStr .=    'AND (cn_active_start_dt = ? OR (cn_active_start_dt != ? AND cn_active_start_dt <= ?)) ';
				$queryStr .=    'AND (cn_active_end_dt = ? OR (cn_active_end_dt != ? AND cn_active_end_dt > ?))';
				$params[] = $initDt;
				$params[] = $initDt;
				$params[] = $now;
				$params[] = $initDt;
				$params[] = $initDt;
				$params[] = $now;
			}
			$queryStr .=  'ORDER BY no';
			$this->selectLoop($queryStr, $params, $callback);
		}
	}
	/**
	 * コンテンツ項目を取得
	 *
	 * @param string	$contentType		コンテンツタイプ
	 * @param array		$contentIdArray		コンテンツID
	 * @param string	$lang				言語
	 * @param array     $rows				取得レコード
	 * @return 			なし
	 */
	function getContentItemsById($contentType, $contentIdArray, $lang, &$rows)
	{
		$params = array();
		$contentId = implode(',', $contentIdArray);
		
		// CASE文作成
		$caseStr = 'CASE cn_id ';
		for ($i = 0; $i < count($contentIdArray); $i++){
			$caseStr .= 'WHEN ' . $contentIdArray[$i] . ' THEN ' . $i . ' ';
		}
		$caseStr .= 'END AS no';

		$queryStr = 'SELECT *, ' . $caseStr . ' FROM content ';
		$queryStr .=  'WHERE cn_deleted = false ';		// 削除されていない
		$queryStr .=    'AND cn_type = ? ';
		$queryStr .=    'AND cn_id in (' . $contentId . ') ';
		$queryStr .=    'AND cn_language_id = ? ';
		$params[] = $contentType;
		$params[] = $lang;
		
		$queryStr .=  'ORDER BY no';
		$retValue = $this->selectRecords($queryStr, $params, $rows);
		return $retValue;
	}
	/**
	 * コンテンツ項目をコンテンツIDと履歴番号で取得
	 *
	 * @param string	$contentType		コンテンツタイプ
	 * @param function	$callback			コールバック関数
	 * @param string	$contentId			コンテンツID
	 * @param string	$langId				言語ID
	 * @return 			なし
	 */
	function getContentItemsByHistory($contentType, $callback, $contentId, $langId, $historyIndex)
	{
		$queryStr  = 'SELECT * FROM content LEFT JOIN _login_user ON cn_create_user_id = lu_id AND lu_deleted = false ';
		$queryStr .=   'WHERE cn_type = ? ';
		$queryStr .=   'AND cn_id = ? ';
		$queryStr .=   'AND cn_language_id = ? ';
		$queryStr .=   'AND cn_history_index = ? ';
		$this->selectLoop($queryStr, array($contentType, $contentId, $langId, $historyIndex), $callback);
	}
	/**
	 * コンテンツの対応言語を取得(管理用)
	 *
	 * @param string	$contentType		コンテンツタイプ
	 * @param string	$contentId			コンテンツID
	 * @param array     $rows				取得レコード
	 * @return			true=取得、false=取得せず
	 */
	function getLangByContentId($contentType, $contentId, &$rows)
	{
		$queryStr = 'SELECT * FROM content LEFT JOIN _language ON cn_language_id = ln_id ';
		$queryStr .=  'WHERE cn_deleted = false ';	// 削除されていない
		$queryStr .=    'AND cn_type = ? ';
		$queryStr .=    'AND cn_id = ? ';
		$queryStr .=  'ORDER BY cn_id, ln_priority';
		$retValue = $this->selectRecords($queryStr, array($contentType, $contentId), $rows);
		return $retValue;
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
	 * コンテンツ項目をシリアル番号で取得
	 *
	 * @param string	$serial				シリアル番号
	 * @param array     $row				レコード
	 * @return bool							取得 = true, 取得なし= false
	 */
	function getContentBySerial($serial, &$row)
	{
		$queryStr  = 'select * from content LEFT JOIN _login_user ON cn_create_user_id = lu_id AND lu_deleted = false ';
		$queryStr .=   'WHERE cn_serial = ? ';
		$ret = $this->selectRecord($queryStr, array($serial), $row);
		return $ret;
	}
	/**
	 * 次のコンテンツIDを取得
	 *
	 * @param string	$contentType		コンテンツタイプ
	 * @return int							コンテンツID
	 */
	function getNextContentId($contentType)
	{
		// コンテンツIDを決定する
		$queryStr  = 'SELECT MAX(cn_id) AS mid FROM content ';
		$queryStr .=   'WHERE cn_type = ? ';
		$ret = $this->selectRecord($queryStr, array($contentType), $row);
		if ($ret){
			$contId = $row['mid'] + 1;
		} else {
			$contId = 1;
		}
		return $contId;
	}
	/**
	 * コンテンツ項目の新規追加
	 *
	 * @param string  $contentType	コンテンツタイプ
	 * @param string  $contentid	コンテンツID(0以下のときはコンテンツIDを新規取得)
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
	function addContentItem($contentType, $contentid, $lang, $name, $desc, $html, $visible, $default, $limited, $key, $password, $metaTitle, $metaDesc, $metaKeyword, $headOthers, $startDt, $endDt, &$newSerial,
								$otherParams = null)
	{
		$now = date("Y/m/d H:i:s");	// 現在日時
		$user = $this->gEnv->getCurrentUserId();	// 現在のユーザ
		
		// トランザクション開始
		$this->startTransaction();
		
		if (intval($contentid) <= 0){		// コンテンツIDが0以下のときは、コンテンツIDを新規取得
			// コンテンツIDを決定する
			$queryStr  = 'SELECT MAX(cn_id) AS mid FROM content ';
			$queryStr .=   'WHERE cn_type = ? ';
			$ret = $this->selectRecord($queryStr, array($contentType), $row);
			if ($ret){
				$contId = $row['mid'] + 1;
			} else {
				$contId = 1;
			}
			
			// 新規コンテンツ追加のときはコンテンツIDが変更されていないかチェック
			if (intval($contentid) * (-1) != $contId){
				$this->endTransaction();
				return false;
			}
		} else {
			$contId = $contentid;
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
		// デフォルトを設定のときは他のデフォルトを解除
/*		if ($default){
			$queryStr  = 'UPDATE content ';
			$queryStr .=   'SET cn_update_user_id = ?, ';
			$queryStr .=     'cn_update_dt = ? ';
			$queryStr .=   'WHERE cn_deleted = false ';
			$queryStr .=     'AND cn_type = ? ';
			$queryStr .=     'AND cn_language_id = ? ';
			$this->execStatement($queryStr, array($user, $now, $contentType, $lang));
		}*/
		
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
	
	/**
	 * コンテンツ項目の更新
	 *
	 * @param int     $serial		シリアル番号
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
	 * @param array   $oldRecord	更新前の旧データ
	 * @param array   $otherParams	その他のフィールド値
	 * @return bool					true = 成功、false = 失敗
	 */
	function updateContentItem($serial, $name, $desc, $html, $visible, $default, $limited, $key, $password, $metaTitle, $metaDesc, $metaKeyword, $headOthers, $startDt, $endDt, &$newSerial,
								&$oldRecord, $otherParams = null)
	{
		$now = date("Y/m/d H:i:s");	// 現在日時
		$user = $this->gEnv->getCurrentUserId();	// 現在のユーザ
				
		// トランザクション開始
		$this->startTransaction();
		
		// 指定のシリアルNoのレコードが削除状態でないかチェック
		$historyIndex = 0;		// 履歴番号
		$queryStr  = 'select * from content ';
		$queryStr .=   'where cn_serial = ? ';
		$ret = $this->selectRecord($queryStr, array($serial), $row);
		if ($ret){		// 既に登録レコードがあるとき
			if ($row['cn_deleted']){		// レコードが削除されていれば終了
				$this->endTransaction();
				return false;
			}
			$historyIndex = $row['cn_history_index'] + 1;
			
			$oldRecord = $row;			// 旧データ
		} else {		// 存在しない場合は終了
			$this->endTransaction();
			return false;
		}
		// デフォルトを設定のときは他のデフォルトを解除
/*		if ($default){
			$queryStr  = 'UPDATE content ';
			$queryStr .=   'SET cn_update_user_id = ?, ';
			$queryStr .=     'cn_update_dt = ? ';
			$queryStr .=   'WHERE cn_deleted = false ';
			$queryStr .=     'AND cn_type = ? ';
			$queryStr .=     'AND cn_language_id = ? ';
			$this->execStatement($queryStr, array($user, $now, $row['cn_type'], $row['cn_language_id']));
		}*/
		
		// 古いレコードを削除
		$queryStr  = 'UPDATE content ';
		$queryStr .=   'SET cn_deleted = true, ';	// 削除
		$queryStr .=     'cn_update_user_id = ?, ';
		$queryStr .=     'cn_update_dt = ? ';
		$queryStr .=   'WHERE cn_serial = ?';
		$this->execStatement($queryStr, array($user, $now, $serial));
		
		// 新規レコード追加
		$params = array($row['cn_type'], $row['cn_id'], $row['cn_language_id'], $historyIndex, $name, $desc, $html,
							intval($visible), intval($limited), $key, $password, $metaTitle, $metaDesc, $metaKeyword, $headOthers, $row['cn_generator'], $startDt, $endDt, $user, $now);
							
		$queryStr  = 'INSERT INTO content ';
		$queryStr .=   '(cn_type, ';
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
		$queryStr .=   'cn_generator, ';
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
		$queryStr .=   ') VALUES ';
		$queryStr .=   '(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?' . $otherValueStr . ')';
		$this->execStatement($queryStr, $params);

		// 新規のシリアル番号取得
		$queryStr = 'select max(cn_serial) as ns from content ';
		$ret = $this->selectRecord($queryStr, array(), $row);
		if ($ret) $newSerial = $row['ns'];
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	
	/**
	 * コンテンツ項目の削除
	 *
	 * @param array $serial			シリアルNo
	 * @return						true=成功、false=失敗
	 */
	function delContentItem($serial)
	{
		$now = date("Y/m/d H:i:s");	// 現在日時
		$user = $this->gEnv->getCurrentUserId();	// 現在のユーザ
		
		if (!is_array($serial) || count($serial) <= 0) return true;
		
		// トランザクション開始
		$this->startTransaction();
		
		// 指定のシリアルNoのレコードが削除状態でないかチェック
		for ($i = 0; $i < count($serial); $i++){
			$queryStr  = 'SELECT * FROM content ';
			$queryStr .=   'WHERE cn_deleted = false ';		// 未削除
			$queryStr .=     'AND cn_serial = ? ';
			$ret = $this->isRecordExists($queryStr, array($serial[$i]));
			// 存在しない場合は、既に削除されたとして終了
			if (!$ret){
				$this->endTransaction();
				return false;
			}
		}
		
		// レコードを削除
		$queryStr  = 'UPDATE content ';
		$queryStr .=   'SET cn_deleted = true, ';	// 削除
		$queryStr .=     'cn_update_user_id = ?, ';
		$queryStr .=     'cn_update_dt = ? ';
		$queryStr .=   'WHERE cn_serial in (' . implode(',', $serial) . ') ';
		$this->execStatement($queryStr, array($user, $now));
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * コンテンツIDでコンテンツ項目を削除
	 *
	 * @param array $serial			シリアルNo
	 * @return						true=成功、false=失敗
	 */
	function delContentItemById($serial)
	{
		$now = date("Y/m/d H:i:s");	// 現在日時
		$user = $this->gEnv->getCurrentUserId();	// 現在のユーザ

		if (!is_array($serial) || count($serial) <= 0) return true;
		
		// トランザクション開始
		$this->startTransaction();
		
		for ($i = 0; $i < count($serial); $i++){
			// コンテンツIDを取得
			$queryStr  = 'select * from content ';
			$queryStr .=   'where cn_deleted = false ';		// 未削除
			$queryStr .=     'and cn_serial = ? ';
			$ret = $this->selectRecord($queryStr, array($serial[$i]), $row);
			if ($ret){		// 既に登録レコードがあるとき
				if ($row['cn_deleted']){		// レコードが削除されていれば終了
					$this->endTransaction();
					return false;
				}
			} else {		// 存在しない場合は終了
				$this->endTransaction();
				return false;
			}
			$contId = $row['cn_id'];
		
			// レコードを削除
			$queryStr  = 'UPDATE content ';
			$queryStr .=   'SET cn_deleted = true, ';	// 削除
			$queryStr .=     'cn_update_user_id = ?, ';
			$queryStr .=     'cn_update_dt = ? ';
			$queryStr .=   'WHERE cn_deleted = false ';		// 未削除
			$queryStr .=     'AND cn_type = ? ';
			$queryStr .=     'AND cn_id = ? ';
			$this->execStatement($queryStr, array($userId, $now, $row['cn_type'], $contId));
		}
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * コンテンツ一覧を取得(管理用)
	 *
	 * @param string	$contentType		コンテンツタイプ
	 * @param string	$langId				言語
	 * @param int		$limit				取得する項目数
	 * @param int		$page				取得するページ(1～)
	 * @param string	$keyword			検索キーワード
	 * @param int		$searchKey			検索キー(0=コンテンツID、1=更新日時)
	 * @param int		$searchOrder		検索ソート順(0=昇順、1=降順)
	 * @param function	$callback			コールバック関数
	 * @return 			なし
	 */
	function searchContent($contentType, $langId, $limit, $page, $keyword, $searchKey, $searchOrder, $callback)
	{
		$offset = $limit * ($page -1);
		if ($offset < 0) $offset = 0;
		
		$params = array();
		$queryStr = 'SELECT * FROM content LEFT JOIN _login_user ON cn_create_user_id = lu_id AND lu_deleted = false ';
		$queryStr .=  'WHERE cn_deleted = false ';		// 削除されていない
		$queryStr .=    'AND cn_type = ? ';$params[] = $contentType;
		$queryStr .=    'AND cn_language_id = ? ';$params[] = $langId;

		// タイトルと記事を検索
		if (!empty($keyword)){
			// 「'"\」文字をエスケープ
			$keyword = addslashes($keyword);
			$queryStr .=    'AND (cn_name LIKE \'%' . $keyword . '%\' ';
			$queryStr .=    'OR cn_html LIKE \'%' . $keyword . '%\' ';
			$queryStr .=    'OR cn_description LIKE \'%' . $keyword . '%\') ';
		}
		if (empty($searchKey)){			// コンテンツID
			$queryStr .=  'ORDER BY cn_id ';
		} else {			// 更新日時
			$queryStr .=  'ORDER BY cn_create_dt ';
		}
		if (!empty($searchOrder)){		// 降順のとき
			$queryStr .=  'desc ';
		}
		$queryStr .=  'limit ' . $limit . ' offset ' . $offset;
		$this->selectLoop($queryStr, $params, $callback);
	}
	/**
	 * コンテンツ数を取得(管理用)
	 *
	 * @param string	$contentType		コンテンツタイプ
	 * @param string	$langId				言語
	 * @param string	$keyword			検索キーワード
	 * @param int		$searchKey			検索キー(0=コンテンツID、1=更新日時)
	 * @param int		$searchOrder		検索ソート順(0=昇順、1=降順)
	 * @return int							項目数
	 */
	function getContentCount($contentType, $langId, $keyword, $searchKey, $searchOrder)
	{
		$params = array();
		$queryStr = 'SELECT * FROM content ';
		$queryStr .=  'WHERE cn_deleted = false ';		// 削除されていない
		$queryStr .=    'AND cn_type = ? ';$params[] = $contentType;
		$queryStr .=    'AND cn_language_id = ? ';$params[] = $langId;

		// タイトルと記事を検索
		if (!empty($keyword)){
			// 「'"\」文字をエスケープ
			$keyword = addslashes($keyword);
			$queryStr .=    'AND (cn_name LIKE \'%' . $keyword . '%\' ';
			$queryStr .=    'OR cn_html LIKE \'%' . $keyword . '%\' ';
			$queryStr .=    'OR cn_description LIKE \'%' . $keyword . '%\') ';
		}
		return $this->selectRecordCount($queryStr, $params);
	}
	/**
	 * コンテンツ項目を検索(一般表示用)
	 *
	 * @param string	$contentType		コンテンツタイプ
	 * @param int		$limit				取得する項目数
	 * @param int		$page				取得するページ(1～)
	 * @param array		$keywords			検索キーワード
	 * @param string	$langId				言語
	 * @param bool		$all				すべてのデータを取得するか、ユーザ制限のないデータを取得するかを指定
	 * @param string	$now				現在日時
	 * @param function	$callback			コールバック関数
	 * @return 			なし
	 */
	function searchContentByKeyword($contentType, $limit, $page, $keywords, $langId, $all, $now, $callback)
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
		if (!$all) $queryStr .=    'AND cn_user_limited = false ';		// ユーザ制限のないデータ

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
		
		// 公開期間を指定
		$queryStr .=    'AND (cn_active_start_dt = ? OR (cn_active_start_dt != ? AND cn_active_start_dt <= ?)) ';
		$queryStr .=    'AND (cn_active_end_dt = ? OR (cn_active_end_dt != ? AND cn_active_end_dt > ?)) ';
		$params[] = $initDt;
		$params[] = $initDt;
		$params[] = $now;
		$params[] = $initDt;
		$params[] = $initDt;
		$params[] = $now;
		
		$queryStr .=  'ORDER BY cn_create_dt desc limit ' . $limit . ' offset ' . $offset;
		$this->selectLoop($queryStr, $params, $callback);
	}
	/**
	 * 利用可能な言語を取得
	 *
	 * @param function	$callback			コールバック関数
	 * @return			true=取得、false=取得せず
	 */
	function getAvailableLang($callback)
	{
		$queryStr  = 'SELECT * FROM _language ';
		$queryStr .=   'WHERE ln_available = true ';
		$queryStr .=   'ORDER BY ln_priority';
		$this->selectLoop($queryStr, array(), $callback);
	}
	/**
	 * 言語情報を取得
	 *
	 * @param string $langId		言語ID
	 * @param array  $row			取得レコード
	 * @return						true=取得、false=取得せず
	 */
	function getLang($langId, &$row)
	{
		$queryStr  = 'SELECT * FROM _language ';
		$queryStr .=   'WHERE ln_id = ? ';
		$retValue = $this->selectRecord($queryStr, array($langId), $row);
		return $retValue;
	}
	/**
	 * メニュー項目の追加
	 *
	 * @param string  $menuId		メニューID
	 * @param string  $name			メニュー名
	 * @param string  $url			URL
	 * @return bool					true = 成功、false = 失敗
	 */
	function addMenuItem($menuId, $name, $url)
	{
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $this->gEnv->getCurrentUserId();	// 現在のユーザ
		
		// トランザクション開始
		$this->startTransaction();
		
		// IDを求める
		$id = 1;
		$queryStr = 'SELECT max(md_id) as ms FROM _menu_def ';
		$ret = $this->selectRecord($queryStr, array(), $maxRow);
		if ($ret) $id = $maxRow['ms'] + 1;
			
		// 最大値インデックスを取得
		$index = 1;
		$queryStr = 'SELECT max(md_index) as ms FROM _menu_def ';
		$queryStr .=  'WHERE md_menu_id = ? ';
		$ret = $this->selectRecord($queryStr, array($menuId), $maxRow);
		if ($ret) $index = $maxRow['ms'] + 1;
			
		$queryStr = 'INSERT INTO _menu_def ';
		$queryStr .=  '(md_id, md_index, md_menu_id, md_name, md_link_url, md_update_user_id, md_update_dt) ';
		$queryStr .=  'VALUES ';
		$queryStr .=  '(?, ?, ?, ?, ?, ?, ?)';
		$this->execStatement($queryStr, array($id, $index, $menuId, $name, $url, $userId, $now));
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * メニューIDのリストを取得
	 *
	 * @param int	$deviceType		デバイスタイプ
	 * @param function $callback	コールバック関数
	 * @param object $tmpl			出力テンプレート
	 * @return						なし
	 */
	function getMenuIdList($deviceType, $callback, $tmpl)
	{
		$queryStr = 'SELECT * FROM _menu_id ';
		$queryStr .=  'WHERE mn_device_type = ? ';
		$queryStr .=  'ORDER BY mn_sort_order';
		$this->selectLoop($queryStr, array($deviceType), $callback, $tmpl);
	}
	/**
	 * コンテンツ履歴を取得
	 *
	 * @param string	$contentType		コンテンツタイプ
	 * @param string	$contentId			コンテンツID
	 * @param string	$langId				言語
	 * @param int		$limit				取得する項目数
	 * @param int		$page				取得するページ(1～)
	 * @param function	$callback			コールバック関数
	 * @return 			なし
	 */
	function getContentHistory($contentType, $contentId, $langId, $limit, $page, $callback)
	{
		$offset = $limit * ($page -1);
		if ($offset < 0) $offset = 0;
		
		$params = array();
		$queryStr = 'SELECT * FROM content LEFT JOIN _login_user ON cn_create_user_id = lu_id AND lu_deleted = false ';
		$queryStr .=  'WHERE cn_type = ? ';$params[] = $contentType;
		$queryStr .=    'AND cn_id = ? ';$params[] = $contentId;
		$queryStr .=    'AND cn_language_id = ? ';$params[] = $langId;
		$queryStr .=  'ORDER BY cn_history_index ';
		$queryStr .=    'DESC ';
		$queryStr .=  'LIMIT ' . $limit . ' OFFSET ' . $offset;
		$this->selectLoop($queryStr, $params, $callback);
	}
	/**
	 * コンテンツ履歴数を取得
	 *
	 * @param string	$contentType		コンテンツタイプ
	 * @param string	$contentId			コンテンツID
	 * @param string	$langId				言語
	 * @return int							項目数
	 */
	function getContentHistoryCount($contentType, $contentId, $langId)
	{
		$params = array();
		$queryStr = 'SELECT * FROM content LEFT JOIN _login_user ON cn_create_user_id = lu_id AND lu_deleted = false ';
		$queryStr .=  'WHERE cn_type = ? ';$params[] = $contentType;
		$queryStr .=    'AND cn_id = ? ';$params[] = $contentId;
		$queryStr .=    'AND cn_language_id = ? ';$params[] = $langId;
		return $this->selectRecordCount($queryStr, $params);
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
	 * テンプレートリスト取得
	 *
	 * @param int      $type		テンプレートのタイプ(0=PC用、1=携帯用、2=スマートフォン)
	 * @param function $callback	コールバック関数
	 * @return						なし
	 */
	function getAllTemplateList($type, $callback)
	{
		// tm_device_typeは後で追加したため、tm_mobileを残しておく
		$queryStr  = 'SELECT * FROM _templates ';
		$queryStr .=   'WHERE tm_deleted = false ';// 削除されていない
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
}
?>
