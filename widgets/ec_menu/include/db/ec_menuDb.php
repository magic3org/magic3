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
 * @copyright  Copyright 2006-2012 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: ec_menuDb.php 5449 2012-12-09 03:06:09Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getDbPath() . '/baseDb.php');

class ec_menuDb extends BaseDb
{
	/**
	 * メニューIDのリストを取得
	 *
	 * @param string   $widgetId	ウィジェットID
	 * @param function $callback	コールバック関数
	 * @return						なし
	 */
	function getMenuIdList($widgetId, $callback)
	{
		$queryStr = 'SELECT * FROM _menu_id ';
		$queryStr .=  'WHERE mn_widget_id  = ? ';
		$queryStr .=  'ORDER BY mn_sort_order';
		$this->selectLoop($queryStr, array($widgetId), $callback);
	}
	/**
	 * メニューIDのレコードを取得
	 *
	 * @param string  $id			メニューID
	 * @param array   $row			レコード
	 * @return bool					取得 = true, 取得なし= false
	 */
	function getMenuId($id, &$row)
	{
		$retValue = '';
		$queryStr = 'SELECT * FROM _menu_id ';
		$queryStr .=  'WHERE mn_id  = ?';
		$ret = $this->selectRecord($queryStr, array($id), $row);
		return $ret;
	}
	/**
	 * メニュー項目を取得
	 *
	 * @param string $menuId		メニュー識別ID
	 * @param string $parentId		親項目ID
	 * @param array  $rows			取得レコード
	 * @return bool					true=取得、false=取得せず
	 */
	function getChildMenuItems($menuId, $parentId, &$rows)
	{
		$queryStr  = 'SELECT * FROM _menu_def ';
		$queryStr .=   'WHERE md_menu_id = ? ';
		$queryStr .=     'AND md_parent_id = ? ';
		$queryStr .=   'ORDER BY md_index';
		$retValue = $this->selectRecords($queryStr, array($menuId, $parentId), $rows);
		return $retValue;
	}
	/**
	 * メニューの項目を取得(管理用)
	 *
	 * @param string $menuId		メニューID
	 * @param function $callback	コールバック関数
	 * @return 						なし
	 */
	function getAllMenuItems($menuId, $callback)
	{
		$queryStr  = 'SELECT * FROM _menu_def ';
		$queryStr .=   'WHERE md_menu_id = ? ';
		$queryStr .=   'ORDER BY md_parent_id, md_index';
		$this->selectLoop($queryStr, array($menuId), $callback);
	}
	/**
	 * 利用可能な言語を取得
	 *
	 * @param array  $rows			取得レコード
	 * @return bool					true=取得、false=取得せず
	 */
	function getAvailableLang(&$rows)
	{
		$queryStr  = 'SELECT * FROM _language ';
		$queryStr .=   'WHERE ln_available = true ';
		$queryStr .=   'ORDER BY ln_priority';
		$retValue = $this->selectRecords($queryStr, array(), $rows);
		return $retValue;
	}
	/**
	 * メニュー項目の追加
	 *
	 * @param string  $menuId		メニューID
	 * @param string  $parentId		親メニュー項目ID
	 * @param string  $name			メニュー名
	 * @param string  $title		タイトル(HTML可)
	 * @param string  $desc			説明
	 * @param int     $index		インデックス番号(0のときは最大値を設定)
	 * @param int     $type			項目タイプ
	 * @param int     $linkType		リンクタイプ
	 * @param string  $param		その他URLパラメータ
	 * @param bool    $visible		表示状態
	 * @param int     $newId		新規ID
	 * @param string  $contentType	リンク先コンテンツタイプ
	 * @param string  $contentId	リンク先コンテンツID
	 * @return bool					true = 成功、false = 失敗
	 */
	function addMenuItem($menuId, $parentId, $name, $title, $desc, $index, $type, $linkType, $param, $visible, &$newId, $contentType = '', $contentId = '')
	{
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $this->gEnv->getCurrentUserId();	// 現在のユーザ
		$startTran = false;			// この関数でトランザクションを開始したかどうか
		
		// トランザクション開始
		if (!$this->isInTransaction()){
			$this->startTransaction();
			$startTran = true;
		}
		
		// IDを求める
		$id = 1;
		$queryStr = 'SELECT max(md_id) as ms FROM _menu_def ';
		$ret = $this->selectRecord($queryStr, array(), $maxRow);
		if ($ret) $id = $maxRow['ms'] + 1;
			
		// インデックスが0のときは、最大値を格納
		if (empty($index)){
			$index = 1;
			$queryStr = 'SELECT max(md_index) as ms FROM _menu_def ';
			$queryStr .=  'WHERE md_menu_id = ? ';
			$ret = $this->selectRecord($queryStr, array($menuId), $maxRow);
			if ($ret) $index = $maxRow['ms'] + 1;
		}
		$queryStr = 'INSERT INTO _menu_def ';
		$queryStr .=  '(md_id, md_parent_id, md_index, md_menu_id, md_name, md_title, md_description, md_type, md_link_type, md_param, md_visible, md_content_type, md_content_id, md_update_user_id, md_update_dt) ';
		$queryStr .=  'VALUES ';
		$queryStr .=  '(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
		$this->execStatement($queryStr, array($id, $parentId, $index, $menuId, $name, $title, $desc, $type, $linkType, $param, intval($visible), $contentType, $contentId, $userId, $now));
		
		// 新規のシリアル番号取得
		$queryStr = 'select max(md_id) as ns from _menu_def ';
		$ret = $this->selectRecord($queryStr, array(), $row);
		if ($ret) $newId = $row['ns'];
		
		// トランザクション確定
		if ($startTran) $ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * メニュー項目の更新
	 *
	 * @param int     $id			メニュー項目ID
	 * @param string  $name			メニュー名
	 * @param string  $title		タイトル(HTML可)
	 * @param string  $desc			説明
	 * @param int     $type			項目タイプ
	 * @param int     $linkType		リンクタイプ
	 * @param string  $param		その他URLパラメータ
	 * @param bool    $visible		表示状態
	 * @param string  $contentType	リンク先コンテンツタイプ
	 * @param string  $contentId	リンク先コンテンツID
	 * @return bool					true = 成功、false = 失敗
	 */
	function updateMenuItem($id, $name, $title, $desc, $type, $linkType, $param, $visible, $contentType = '', $contentId = '')
	{
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $this->gEnv->getCurrentUserId();	// 現在のユーザ
		$startTran = false;			// この関数でトランザクションを開始したかどうか
		
		// トランザクション開始
		if (!$this->isInTransaction()){
			$this->startTransaction();
			$startTran = true;
		}

		$params = array();
		$queryStr = 'UPDATE _menu_def ';
		$queryStr .=  'SET md_name = ?, ';			$params[] = $name;
		$queryStr .=    'md_title = ?, ';			$params[] = $title;
		$queryStr .=    'md_description = ?, ';		$params[] = $desc;
		$queryStr .=    'md_type = ?, ';			$params[] = $type;
		$queryStr .=    'md_link_type = ?, ';		$params[] = $linkType;
		$queryStr .=    'md_param = ?, ';			$params[] = $param;
		$queryStr .=    'md_visible = ?, ';			$params[] = $visible;
		$queryStr .=    'md_content_type = ?, ';	$params[] = $contentType;
		$queryStr .=    'md_content_id = ?, ';		$params[] = $contentId;
		$queryStr .=    'md_update_user_id = ?, ';	$params[] = $userId;
		$queryStr .=    'md_update_dt = ? ';		$params[] = $now;
		$queryStr .=  'WHERE md_id = ? ';			$params[] = $id;
		$this->execStatement($queryStr, $params);

		// トランザクション確定
		if ($startTran) $ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * メニュー項目をIDで取得
	 *
	 * @param int     $id			メニュー項目ID
	 * @param array   $row			レコード
	 * @return bool					取得 = true, 取得なし= false
	 */
	function getMenuItem($id, &$row)
	{
		$queryStr  = 'SELECT * FROM _menu_def ';
		$queryStr .=   'WHERE md_id = ? ';
		$ret = $this->selectRecord($queryStr, array($id), $row);
		return $ret;
	}
	/**
	 * メニュー項目の削除
	 *
	 * @param string $id			メニュー項目ID
	 * @return bool					true=成功、false=失敗
	 */
	function delMenuItem($id)
	{
		// レコードを削除
		$queryStr  = 'DELETE FROM _menu_def ';
		$queryStr .=   'WHERE md_id = ? ';
		$ret = $this->execStatement($queryStr, array($id));
		return $ret;
	}
	/**
	 * メニュー項目順序を変更
	 *
	 * @param string $menuId		メニュー識別ID
	 * @param int $parentId			親項目ID
	 * @param int $id				項目ID
	 * @param int $pos				新規の位置
	 * @return						true=成功、false=失敗
	 */
	function reorderMenuItem($menuId, $parentId, $id, $pos)
	{
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $this->gEnv->getCurrentUserId();	// 現在のユーザ
		
		// トランザクション開始
		$this->startTransaction();
		
		$queryStr  = 'SELECT * FROM _menu_def ';
		$queryStr .=   'WHERE md_menu_id = ? ';
		$queryStr .=     'AND md_parent_id = ? ';
		$queryStr .=   'ORDER BY md_index';
		$retValue = $this->selectRecords($queryStr, array($menuId, $parentId), $rows);
		
		// 同階層内かどうかチェック
		$insPos = $pos;			// 項目挿入位置
		for ($i = 0; $i < count($rows); $i++){
			if ($id == $rows[$i]['md_id']){
				//if ($i < $pos) $insPos++;		// 2011/8/22 simpleTreeからjsTreeに変更のため仕様変更
				break;
			}
		}
		$index = 0;
		for ($i = 0; $i < $insPos; $i++){
			$itemId = $rows[$i]['md_id'];
			if ($itemId != $id){
				$queryStr  = 'UPDATE _menu_def ';
				$queryStr .=   'SET md_index = ?, ';	// インデックス
				$queryStr .=     'md_update_user_id = ?, ';
				$queryStr .=     'md_update_dt = ? ';
				$queryStr .=   'WHERE md_id = ?';
				$ret = $this->execStatement($queryStr, array($index, $userId, $now, $itemId));
				$index++;
			}
		}
		$queryStr  = 'UPDATE _menu_def ';
		$queryStr .=   'SET md_index = ?, ';	// インデックス
		$queryStr .=     'md_parent_id = ?, ';
		$queryStr .=     'md_update_user_id = ?, ';
		$queryStr .=     'md_update_dt = ? ';
		$queryStr .=   'WHERE md_id = ?';
		$ret = $this->execStatement($queryStr, array($index, $parentId, $userId, $now, $id));
		$index++;
		for ($i = $insPos; $i < count($rows); $i++){
			$itemId = $rows[$i]['md_id'];
			if ($itemId != $id){
				$queryStr  = 'UPDATE _menu_def ';
				$queryStr .=   'SET md_index = ?, ';	// インデックス
				$queryStr .=     'md_update_user_id = ?, ';
				$queryStr .=     'md_update_dt = ? ';
				$queryStr .=   'WHERE md_id = ?';
				$ret = $this->execStatement($queryStr, array($index, $userId, $now, $itemId));
				$index++;
			}
		}
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * カテゴリーを取得(管理用)
	 *
	 * @param string	$lang			言語ID
	 * @param string	$categoryId		カテゴリーID
	 * @return			true=取得、false=取得せず
	 */
	function getCategory($lang, $categoryId, &$rows)
	{
		$params = array();
		$queryStr = 'SELECT * FROM product_category ';
		$queryStr .=  'WHERE pc_deleted = false ';	// 削除されていない
		$queryStr .=    'AND pc_language_id = ? ';
		if (!empty($categoryId)) $queryStr .=    'AND pc_id in (' . $categoryId . ') ';
		$queryStr .=  'ORDER BY pc_sort_order';
		$retValue = $this->selectRecords($queryStr, array($lang), $rows);
		return $retValue;
	}
	/**
	 * 商品カテゴリー一覧を取得(管理用)
	 *
	 * @param string	$lang				言語
	 * @param array		$rows				取得データ
	 * @return bool							true=取得、false=取得せず
	 */
	function getAllCategory($lang, &$rows)
	{
		$queryStr = 'SELECT * FROM product_category LEFT JOIN _login_user ON pc_create_user_id = lu_id AND lu_deleted = false ';
		$queryStr .=  'WHERE pc_language_id = ? ';
		$queryStr .=    'AND pc_deleted = false ';		// 削除されていない
		$queryStr .=  'ORDER BY pc_sort_order';
		$retValue = $this->selectRecords($queryStr, array($lang), $rows);
		return $retValue;
	}
	/**
	 * コンテンツ項目をコンテンツIDで取得
	 *
	 * @param string	$contentType		コンテンツタイプ
	 * @param string	$contentId			コンテンツID
	 * @param string	$langId				言語ID
	 * @param array     $row				レコード
	 * @return bool							取得 = true, 取得なし= false
	 */
	function getContentByContentId($contentType, $contentId, $langId, &$row)
	{
		$queryStr  = 'SELECT * FROM content ';
		$queryStr .=   'WHERE cn_deleted = false ';	// 削除されていない
		$queryStr .=    'AND cn_type = ? ';
		$queryStr .=   'AND cn_id = ? ';
		$queryStr .=   'AND cn_language_id = ? ';
		$ret = $this->selectRecord($queryStr, array($contentType, $contentId, $langId), $row);
		return $ret;
	}
	/**
	 * コンテンツ項目の更新
	 *
	 * @param string  $contentType	コンテンツタイプ
	 * @param string  $contentId	コンテンツID(0のとき新規)
	 * @param string  $lang			言語ID
	 * @param string  $name			コンテンツ名
	 * @param string  $html			HTML
	 * @param bool    $visible		表示状態
	 * @param int     $newContentId	新規コンテンツID
	 * @param int     $newSerial	新規シリアル番号
	 * @return bool					true = 成功、false = 失敗
	 */
	function updateContentItem($contentType, $contentId, $lang, $name, $html, $visible, &$newContentId, &$newSerial)
	{
		$historyIndex = 0;		// 履歴番号
		$userId = $this->gEnv->getCurrentUserId();	// 現在のユーザ
		$now = date("Y/m/d H:i:s");	// 現在日時
		
		if (empty($contentId)){			// 新規コンテンツ追加のとき
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
			// 前レコードの削除状態チェック
			$queryStr = 'SELECT * FROM content ';
			$queryStr .=  'WHERE cn_type = ? ';
			$queryStr .=    'AND cn_id = ? ';
			$queryStr .=    'AND cn_language_id = ? ';
			$queryStr .=  'ORDER BY cn_history_index DESC ';
			$ret = $this->selectRecord($queryStr, array($contentType, $contentId, $lang), $row);
			if ($ret){
				if ($row['cn_deleted']){		// レコードが削除されていれば終了
					return false;
				}
			} else {
				return false;
			}
			$historyIndex = $row['cn_history_index'] + 1;
			$contId = $row['cn_id'];
				
			// 古いレコードを削除
			$queryStr  = 'UPDATE content ';
			$queryStr .=   'SET cn_deleted = true, ';	// 削除
			$queryStr .=     'cn_update_user_id = ?, ';
			$queryStr .=     'cn_update_dt = ? ';
			$queryStr .=   'WHERE cn_serial = ?';
			$this->execStatement($queryStr, array($userId, $now, $row['cn_serial']));
		}
		
		// 新規レコード追加
		$queryStr = 'INSERT INTO content ';
		$queryStr .=  '(cn_type, cn_id, cn_language_id, cn_history_index, cn_name, cn_html, cn_visible, cn_create_user_id, cn_create_dt) ';
		$queryStr .=  'VALUES ';
		$queryStr .=  '(?, ?, ?, ?, ?, ?, ?, ?, ?)';
		$this->execStatement($queryStr, array($contentType, $contId, $lang, $historyIndex, $name, $html, $visible, $userId, $now));

		// 新規のシリアル番号取得
		$queryStr = 'SELECT max(cn_serial) as ns FROM content ';
		$queryStr .=  'WHERE cn_type = ? ';
		$ret = $this->selectRecord($queryStr, array($contentType), $row);
		if ($ret) $newSerial = $row['ns'];
		
		$newContentId = $contId;		// 新規コンテンツID
		return $ret;
	}
	/**
	 * コンテンツ項目の削除
	 *
	 * @param string  $contentType	コンテンツタイプ
	 * @param string  $contentId	コンテンツID(0のとき新規)
	 * @param string  $lang			言語ID
	 * @return bool					true=成功、false=失敗
	 */
	function delContentItem($contentType, $contentId, $lang)
	{
		$userId = $this->gEnv->getCurrentUserId();	// 現在のユーザ
		$now = date("Y/m/d H:i:s");	// 現在日時
		
		// 前レコードの削除状態チェック
		$queryStr = 'SELECT * FROM content ';
		$queryStr .=  'WHERE cn_type = ? ';
		$queryStr .=    'AND cn_id = ? ';
		$queryStr .=    'AND cn_language_id = ? ';
		$queryStr .=  'ORDER BY cn_history_index DESC ';
		$ret = $this->selectRecord($queryStr, array($contentType, $contentId, $lang), $row);
		if ($ret){
			if ($row['cn_deleted']){		// レコードが削除されていれば終了
				return false;
			}
		} else {
			return false;
		}
			
		// 古いレコードを削除
		$queryStr  = 'UPDATE content ';
		$queryStr .=   'SET cn_deleted = true, ';	// 削除
		$queryStr .=     'cn_update_user_id = ?, ';
		$queryStr .=     'cn_update_dt = ? ';
		$queryStr .=   'WHERE cn_serial = ?';
		$ret = $this->execStatement($queryStr, array($userId, $now, $row['cn_serial']));
		return $ret;
	}
	/**
	 * 商品を商品ID、言語IDで取得
	 *
	 * @param int		$id					商品ID
	 * @param string	$langId				言語ID
	 * @param array     $row				レコード
	 * @return bool							取得 = true, 取得なし= false
	 */
	function getProductWithCategory($id, $langId, &$row)
	{
		$queryStr  = 'SELECT * FROM product RIGHT JOIN product_with_category ON pt_serial = pw_product_serial ';
		$queryStr .=   'WHERE pt_deleted = false ';	// 削除されていない
		$queryStr .=   'AND pt_id = ? ';
		$queryStr .=   'AND pt_language_id = ? ';
		$queryStr .=  'ORDER BY pw_index ';
		$ret = $this->selectRecord($queryStr, array(intval($id), $langId), $row);
		return $ret;
	}
}
?>
