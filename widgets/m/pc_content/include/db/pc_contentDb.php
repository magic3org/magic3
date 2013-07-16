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
 * @copyright  Copyright 2006-2008 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: pc_contentDb.php 1253 2008-11-19 05:43:26Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getDbPath() . '/baseDb.php');

class pc_contentDb extends BaseDb
{
	/**
	 * コンテンツ項目を取得
	 *
	 * @param function	$callback			コールバック関数
	 * @param array		$contentIdArray		コンテンツID
	 * @param string	$lang				言語
	 * @param bool		$all				すべてのデータを取得するか、ユーザ制限のないデータを取得するかを指定
	 * @return 			なし
	 */
	function getContentItems($callback, $contentIdArray, $lang, $all=true)
	{
		$contentType = '';
		
		// コンテンツIDの指定がない場合は、デフォルト値を取得
		if ($contentIdArray == null){
			$queryStr = 'SELECT * FROM content ';
			$queryStr .=  'WHERE cn_visible = true ';
			$queryStr .=    'AND cn_default = true ';
			$queryStr .=    'AND cn_deleted = false ';		// 削除されていない
			$queryStr .=    'AND cn_type = ? ';
			$queryStr .=    'AND cn_language_id = ? ';
			if (!$all) $queryStr .=    'AND cn_user_limited = false ';		// ユーザ制限のないデータ
			$queryStr .=  'ORDER BY cn_serial';
			$this->selectLoop($queryStr, array($contentType, $lang), $callback, null);
		} else {
			$contentId = implode(',', $contentIdArray);
			
			// CASE文作成
			$caseStr = 'CASE cn_id ';
			for ($i = 0; $i < count($contentIdArray); $i++){
				$caseStr .= 'WHEN ' . $contentIdArray[$i] . ' THEN ' . $i . ' ';
			}
			$caseStr .= 'END AS no';

			$queryStr = 'SELECT *, ' . $caseStr . ' FROM content ';
			$queryStr .=  'WHERE cn_visible = true ';
			$queryStr .=    'AND cn_deleted = false ';		// 削除されていない
			$queryStr .=    'AND cn_type = ? ';
			$queryStr .=    'AND cn_id in (' . $contentId . ') ';
			$queryStr .=    'AND cn_language_id = ? ';
			if (!$all) $queryStr .=    'AND cn_user_limited = false ';		// ユーザ制限のないデータ
			$queryStr .=  'ORDER BY no';
			$this->selectLoop($queryStr, array($contentType, $lang), $callback, null);
		}
	}
	/**
	 * コンテンツ項目一覧を取得(管理用)
	 *
	 * @param function	$callback			コールバック関数
	 * @param string	$lang				言語
	 * @return 			なし
	 */
	function getAllContentItems($callback, $lang)
	{
		$contentType = '';
		$queryStr = 'SELECT * FROM content LEFT JOIN _login_user ON cn_create_user_id = lu_id AND lu_deleted = false ';
		$queryStr .=  'WHERE cn_type = ? ';
		$queryStr .=    'AND cn_language_id = ? ';
		$queryStr .=    'AND cn_deleted = false ';		// 削除されていない
		$queryStr .=  'ORDER BY cn_id';
		$this->selectLoop($queryStr, array($contentType, $lang), $callback, null);
	}
	/**
	 * コンテンツの対応言語を取得(管理用)
	 *
	 * @param string	$contentId			コンテンツID
	 * @return			true=取得、false=取得せず
	 */
	function getLangByContentId($contentId, &$rows)
	{
		$contentType = '';
		$queryStr = 'SELECT ln_id, ln_name, ln_name_en FROM content LEFT JOIN _language ON cn_language_id = ln_id ';
		$queryStr .=  'WHERE cn_deleted = false ';	// 削除されていない
		$queryStr .=    'AND cn_type = ? ';
		$queryStr .=    'AND cn_id = ? ';
		$queryStr .=  'ORDER BY cn_id, ln_priority';
		$retValue = $this->selectRecords($queryStr, array($contentType, $contentId), $rows);
		return $retValue;
	}
	/**
	 * コンテンツの対応言語を取得(管理用)
	 *
	 * @param function	$callback			コールバック関数
	 * @param string	$contentId			コンテンツID
	 * @return			なし
	 */
	function getLangLoopByContentId($callback, $contentId)
	{
		// コンテンツIDがないときは終了
		if (empty($contentId)) return;
		
		$contentType = '';
		$queryStr = 'SELECT ln_id, ln_name, ln_name_en FROM content LEFT JOIN _language ON cn_language_id = ln_id ';
		$queryStr .=  'WHERE cn_deleted = false ';	// 削除されていない
		$queryStr .=    'AND cn_type = ? ';
		$queryStr .=    'AND cn_id = ? ';
		$queryStr .=  'ORDER BY cn_id, ln_priority';
		$this->selectLoop($queryStr, array($contentType, $contentId), $callback, null);
	}
	/**
	 * コンテンツ項目をコンテンツIDで取得
	 *
	 * @param string	$contentId			コンテンツID
	 * @param string	$langId				言語ID
	 * @param array     $row				レコード
	 * @return bool							取得 = true, 取得なし= false
	 */
	function getContentByContentId($contentId, $langId, &$row)
	{
		$contentType = '';
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
	 * コンテンツ項目の新規追加
	 *
	 * @param string  $contentid	コンテンツID
	 * @param string  $lang			言語ID
	 * @param string  $name			コンテンツ名
	 * @param string  $desc			説明
	 * @param string  $html			HTML
	 * @param bool    $visible		表示状態
	 * @param bool    $default		デフォルトで使用するかどうか
	 * @param bool    $limited		ユーザ制限するかどうか
	 * @param string  $key			外部参照用キー
	 * @param int     $newSerial	新規シリアル番号
	 * @return bool					true = 成功、false = 失敗
	 */
	function addContentItem($contentid, $lang, $name, $desc, $html, $visible, $default, $limited, $key, &$newSerial)
	{
		global $gEnvManager;
		$now = date("Y/m/d H:i:s");	// 現在日時
		$user = $gEnvManager->getCurrentUserId();	// 現在のユーザ
		$contentType = '';
				
		// トランザクション開始
		$this->startTransaction();
		
		if ($contentid == 0){		// コンテンツIDが0のときは、コンテンツIDを新規取得
			// コンテンツIDを決定する
			$queryStr = 'select max(cn_id) as mid from content ';
			$queryStr .=  'WHERE cn_type = ? ';
			$ret = $this->selectRecord($queryStr, array($contentType), $row);
			if ($ret){
				$contId = $row['mid'] + 1;
			} else {
				$contId = 1;
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
		if ($default){
			$queryStr  = 'UPDATE content ';
			$queryStr .=   'SET cn_default = false, ';		// デフォルトをクリア
			$queryStr .=     'cn_update_user_id = ?, ';
			$queryStr .=     'cn_update_dt = ? ';
			$queryStr .=   'WHERE cn_deleted = false ';
			$queryStr .=     'AND cn_type = ? ';
			$queryStr .=     'AND cn_language_id = ? ';
			$this->execStatement($queryStr, array($user, $now, $contentType, $lang));
		}
		// データを追加
		$queryStr = 'INSERT INTO content ';
		$queryStr .=  '(cn_type, cn_id, cn_language_id, cn_history_index, cn_name, cn_description, cn_html, cn_visible, cn_default, cn_user_limited, cn_key, cn_create_user_id, cn_create_dt) ';
		$queryStr .=  'VALUES ';
		$queryStr .=  '(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
		$this->execStatement($queryStr, array($contentType, $contId, $lang, $historyIndex, $name, $desc, $html, intval($visible), intval($default), intval($limited), $key, $user, $now));
		
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
	 * @param string  $html			HTML
	 * @param bool    $visible		表示状態
	 * @param bool    $default		デフォルトで使用するかどうか
	 * @param bool    $limited		ユーザ制限するかどうか
	 * @param string  $key			外部参照用キー
	 * @param int     $newSerial	新規シリアル番号
	 * @return bool					true = 成功、false = 失敗
	 */
	function updateContentItem($serial, $name, $html, $visible, $default, $limited, $key, &$newSerial)
	{
		global $gEnvManager;
		$now = date("Y/m/d H:i:s");	// 現在日時
		$user = $gEnvManager->getCurrentUserId();	// 現在のユーザ
		$contentType = '';
				
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
		} else {		// 存在しない場合は終了
			$this->endTransaction();
			return false;
		}
		// デフォルトを設定のときは他のデフォルトを解除
		if ($default){
			$queryStr  = 'UPDATE content ';
			$queryStr .=   'SET cn_default = false, ';		// デフォルトをクリア
			$queryStr .=     'cn_update_user_id = ?, ';
			$queryStr .=     'cn_update_dt = ? ';
			$queryStr .=   'WHERE cn_deleted = false ';
			$queryStr .=     'AND cn_type = ? ';
			$queryStr .=     'AND cn_language_id = ? ';
			$this->execStatement($queryStr, array($user, $now, $contentType, $row['cn_language_id']));
		}
		
		// 古いレコードを削除
		$queryStr  = 'UPDATE content ';
		$queryStr .=   'SET cn_deleted = true, ';	// 削除
		$queryStr .=     'cn_update_user_id = ?, ';
		$queryStr .=     'cn_update_dt = ? ';
		$queryStr .=   'WHERE cn_serial = ?';
		$this->execStatement($queryStr, array($user, $now, $serial));
		
		// 新規レコード追加
		$queryStr = 'INSERT INTO content ';
		$queryStr .=  '(cn_type, cn_id, cn_language_id, cn_history_index, cn_name, cn_description, cn_html, cn_visible, cn_default, cn_user_limited, cn_key, cn_create_user_id, cn_create_dt) ';
		$queryStr .=  'VALUES ';
		$queryStr .=  '(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
		$this->execStatement($queryStr, array($row['cn_type'], $row['cn_id'], $row['cn_language_id'], $historyIndex, $name, $row['cn_description'], $html, intval($visible), intval($default), intval($limited), $key, $user, $now));

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
		global $gEnvManager;
		$now = date("Y/m/d H:i:s");	// 現在日時
		$user = $gEnvManager->getCurrentUserId();	// 現在のユーザ
		
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
		$queryStr .=     'cn_update_dt = now() ';
		$queryStr .=   'WHERE cn_serial in (' . implode($serial, ',') . ') ';
		$this->execStatement($queryStr, array($user));
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * コンテンツIDでコンテンツ項目を削除
	 *
	 * @param int $serial			シリアルNo
	 * @param int $userId			ユーザID(データ更新者)
	 * @return						true=成功、false=失敗
	 */
	function delContentItemById($serial, $userId)
	{
		$contentType = '';
				
		// トランザクション開始
		$this->startTransaction();
		
		// コンテンツIDを取得
		$queryStr  = 'select * from content ';
		$queryStr .=   'where cn_deleted = false ';		// 未削除
		$queryStr .=     'and cn_serial = ? ';
		$ret = $this->selectRecord($queryStr, array($serial), $row);
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
		$queryStr .=     'cn_update_dt = now() ';
		$queryStr .=   'WHERE cn_type = ? ';
		$queryStr .=     'AND cn_id = ? ';
		$this->execStatement($queryStr, array($userId, $contentType, $contId));
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * コンテンツ項目をすべて削除
	 *
	 * @param int $userId			ユーザID(データ更新者)
	 * @return						true=成功、false=失敗
	 */
	function delAllContentItems($userId)
	{
		$contentType = '';
			
		// トランザクション開始
		$this->startTransaction();
		
		// レコードを削除
		$queryStr  = 'UPDATE content ';
		$queryStr .=   'SET cn_deleted = true, ';	// 削除
		$queryStr .=     'cn_update_user_id = ?, ';
		$queryStr .=     'cn_update_dt = now() ';
		$queryStr .=   'WHERE cn_type = ? AND cn_deleted = false';
		$this->execStatement($queryStr, array($userId, $contentType));
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
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
	 * メニュー項目の追加
	 *
	 * @param string  $menuId		メニューID
	 * @param string  $lang			言語ID
	 * @param string  $name			メニュー名
	 * @param string  $url			URL
	 * @param int     $incIndex		表示順増加分
	 * @param int     $userId		更新者ユーザID
	 * @return bool					true = 成功、false = 失敗
	 */
	function addMenuItem($menuId, $lang, $name, $url, $incIndex, $userId)
	{
		// トランザクション開始
		$this->startTransaction();
		
		// メニュー項目IDを作成
		$queryStr = 'select max(mi_id) as mid from menu_item ';
		$ret = $this->selectRecord($queryStr, array(), $row);
		if ($ret){
			$menuItemId = $row['mid'] + 1;
		} else {
			$menuItemId = 1;
		}
		// インデックス番号を作成
		$queryStr = 'SELECT max(mi_index) as m FROM menu_item ';
		$queryStr .=  'WHERE mi_menu_id = ? ';
		$queryStr .=    'AND mi_language_id = ? ';
		$ret = $this->selectRecord($queryStr, array($menuId, $lang), $row);
		if ($ret){
			$index = $row['m'] + $incIndex;
		} else {
			$index = 1;
		}
		
		$queryStr = 'INSERT INTO menu_item ';
		$queryStr .=  '(mi_menu_id, mi_id, mi_language_id, mi_name, mi_index, mi_link_type, mi_link_url, mi_visible, mi_enable, mi_update_user_id, mi_update_dt) ';
		$queryStr .=  'VALUES ';
		$queryStr .=  '(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, now())';
		$this->execStatement($queryStr, array($menuId, $menuItemId, $lang, $name, $index, 0, $url, true, true, $userId));
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
}
?>
