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
 * @version    SVN: $Id: mainmenuDb.php 459 2008-04-01 04:03:20Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getDbPath() . '/baseDb.php');

class mainmenuDb extends BaseDb
{
	/**
	 * メニュー情報を取得
	 *
	 * @param string	$menuId				メニューID
	 * @param string	$lang				言語
	 * @param array     $row				レコード
	 * @return bool							取得 = true, 取得なし= false
	 */
	function getMenu($menuId, $lang, &$row)
	{
		$retValue = 0;
		$queryStr  = 'select * from menu ';
		$queryStr .=   'WHERE me_id = ? ';
		$queryStr .=     'AND me_language_id = ? ';
		$ret = $this->selectRecord($queryStr, array($menuId, $lang), $row);
		return $ret;
	}
	/**
	 * メニューの項目を取得
	 *
	 * @param function	$callback			コールバック関数
	 * @param string	$menuId				メニューID
	 * @param string	$lang				言語
	 * @return 			なし
	 */
	function getMenuItems($callback, $menuId, $lang)
	{
		$queryStr = 'SELECT * FROM menu_item ';
		$queryStr .=  'WHERE mi_visible = true ';
		$queryStr .=    'AND mi_menu_id = ? ';
		$queryStr .=    'AND mi_language_id = ? ';
		$queryStr .=  'ORDER BY mi_index';
		$this->selectLoop($queryStr, array($menuId, $lang), $callback, null);
	}
	/**
	 * メニュー項目をシリアル番号で取得
	 *
	 * @param string	$serial				シリアル番号
	 * @param array     $row				レコード
	 * @return bool							取得 = true, 取得なし= false
	 */
	function getMenuBySerial($serial, &$row)
	{
		$retValue = 0;
		$queryStr  = 'select * from menu_item ';
		$queryStr .=   'WHERE mi_serial = ? ';
		$ret = $this->selectRecord($queryStr, array($serial), $row);
		return $ret;
	}
	/**
	 * メニュー項目のデフォルトインデックス番号を取得
	 *
	 * @return int					デフォルトインデックス番号
	 */
	function getDefaultMenuIndex()
	{
		$default = 0;
		$queryStr = 'select max(mi_index) as ms from menu_item ';
		$ret = $this->selectRecord($queryStr, array(), $maxRow);
		if ($ret) $default = $maxRow['ms'] + 1;
		return $default;
	}
	/**
	 * 新規のメニュー項目IDを取得
	 *
	 * @return int					新規メニュー項目ID番号
	 */
	function getNewMenuId()
	{
		$id = 0;
		$queryStr = 'select max(mi_id) as ms from menu_item ';
		$ret = $this->selectRecord($queryStr, array(), $maxRow);
		if ($ret) $id = $maxRow['ms'] + 1;
		return $id;
	}
	/**
	 * メニューの項目を取得(管理用)
	 *
	 * @param function	$callback			コールバック関数
	 * @param string	$menuId				メニューID
	 * @param string	$lang				言語
	 * @return 			なし
	 */
	function getAllMenuItems($callback, $menuId, $lang)
	{
		$queryStr = 'SELECT * FROM menu_item ';
		$queryStr .=  'WHERE mi_menu_id = ? ';
		$queryStr .=    'AND mi_language_id = ? ';
		$queryStr .=  'ORDER BY mi_index';
		$this->selectLoop($queryStr, array($menuId, $lang), $callback, null);
	}
	/**
	 * メニュー項目の追加
	 *
	 * @param string  $menuId		メニューID
	 * @param string  $id			メニュー項目ID
	 * @param string  $lang			言語ID
	 * @param string  $name			メニュー名
	 * @param int     $index		インデックス番号
	 * @param int     $linkType		リンクタイプ
	 * @param string  $url			URL
	 * @param bool    $visible		表示状態
	 * @param bool    $enable		使用可否
	 * @param int     $userId		更新者ユーザID
	 * @param int     $newSerial	新規シリアル番号
	 * @return bool					true = 成功、false = 失敗
	 */
	function addMenuItem($menuId, $id, $lang, $name, $index, $linkType, $url, $visible, $enable, $userId, &$newSerial)
	{
		// トランザクション開始
		$this->startTransaction();
		
		// インデックスが0のときは、最大値を格納
		if (empty($index)){
			$default = 1;
			$queryStr = 'SELECT max(mi_index) as ms FROM menu_item ';
			$queryStr .=  'WHERE mi_menu_id = ? ';
			$queryStr .=    'AND mi_language_id = ? ';
			$ret = $this->selectRecord($queryStr, array($menuId, $lang), $maxRow);
			if ($ret) $default = $maxRow['ms'] + 1;
			$index = $default;
		}
		$queryStr = 'INSERT INTO menu_item ';
		$queryStr .=  '(mi_menu_id, mi_id, mi_language_id, mi_name, mi_index, mi_link_type, mi_link_url, mi_visible, mi_enable, mi_update_user_id, mi_update_dt) ';
		$queryStr .=  'VALUES ';
		$queryStr .=  '(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, now())';
		$this->execStatement($queryStr, array($menuId, $id, $lang, $name, $index, $linkType, $url, $visible, $enable, $userId));
		
		// 新規のシリアル番号取得
		$queryStr = 'select max(mi_serial) as ns from menu_item ';
		$ret = $this->selectRecord($queryStr, array(), $row);
		if ($ret) $newSerial = $row['ns'];
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * メニュー項目の更新
	 *
	 * @param string  $menuId		メニューID
	 * @param string  $id			メニュー項目ID
	 * @param string  $lang			言語ID
	 * @param string  $name			メニュー名
	 * @param int     $index		インデックス番号
	 * @param int     $linkType		リンクタイプ
	 * @param string  $url			URL
	 * @param bool    $visible		表示状態
	 * @param bool    $enable		使用可否
	 * @param int     $userId		更新者ユーザID
	 * @return bool					true = 成功、false = 失敗
	 */
	function updateMenuItem($menuId, $id, $lang, $name, $index, $linkType, $url, $visible, $enable, $userId)
	{
		// トランザクション開始
		$this->startTransaction();

		$params = array();
		$queryStr = 'UPDATE menu_item ';
		$queryStr .=  'SET mi_menu_id = ?, ';		$params[] = $menuId;
		$queryStr .=    'mi_name = ?, ';			$params[] = $name;
		if (!empty($index)){
			$queryStr .=    'mi_index = ?, ';
			$params[] = $index;
		}
		$queryStr .=    'mi_link_type = ?, ';		$params[] = $linkType;
		$queryStr .=    'mi_link_url = ?, ';		$params[] = $url;
		$queryStr .=    'mi_visible = ?, ';			$params[] = $visible;
		$queryStr .=    'mi_enable = ?, ';			$params[] = $enable;
		$queryStr .=    'mi_update_user_id = ?, ';	$params[] = $userId;
		$queryStr .=    'mi_update_dt = now() ';
		$queryStr .=  'WHERE mi_id = ? AND ';		$params[] = $id;
		$queryStr .=    'mi_language_id = ?';		$params[] = $lang;
		$this->execStatement($queryStr, $params);

		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * メニュー項目の更新
	 *
	 * @param int     $serial		メニュー項目シリアル番号
	 * @param string  $menuId		メニューID
	 * @param string  $name			メニュー名
	 * @param int     $index		インデックス番号
	 * @param int     $linkType		リンクタイプ
	 * @param string  $url			URL
	 * @param bool    $visible		表示状態
	 * @param bool    $enable		使用可否
	 * @param int     $userId		更新者ユーザID
	 * @return bool					true = 成功、false = 失敗
	 */
	function updateMenuItemBySerial($serial, $menuId, $name, $index, $linkType, $url, $visible, $enable, $userId)
	{
		// トランザクション開始
		$this->startTransaction();

		$params = array();
		$queryStr = 'UPDATE menu_item ';
		$queryStr .=  'SET mi_menu_id = ?, ';		$params[] = $menuId;
		$queryStr .=    'mi_name = ?, ';			$params[] = $name;
		if (!empty($index)){
			$queryStr .=    'mi_index = ?, ';
			$params[] = $index;
		}
		$queryStr .=    'mi_link_type = ?, ';		$params[] = $linkType;
		$queryStr .=    'mi_link_url = ?, ';		$params[] = $url;
		$queryStr .=    'mi_visible = ?, ';			$params[] = $visible;
		$queryStr .=    'mi_enable = ?, ';			$params[] = $enable;
		$queryStr .=    'mi_update_user_id = ?, ';	$params[] = $userId;
		$queryStr .=    'mi_update_dt = now() ';
		$queryStr .=  'WHERE mi_serial = ? ';		$params[] = $serial;
		$this->execStatement($queryStr, $params);

		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * メニュー項目の削除
	 *
	 * @param int $serialNo			シリアルNo
	 * @return						true=成功、false=失敗
	 */
	function delMenuItem($serialNo)
	{
		// トランザクション開始
		$this->startTransaction();
		
		// レコードを削除
		$queryStr  = 'DELETE FROM menu_item ';
		$queryStr .=   'WHERE mi_serial = ?';
		$this->execStatement($queryStr, array($serialNo));
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * メニュー項目の削除
	 *
	 * @param string $serial			複数シリアルNoをカンマ区切り
	 * @return						true=成功、false=失敗
	 */
	function delMenuItems($serial)
	{
		// トランザクション開始
		$this->startTransaction();
		
		// レコードを削除
		$queryStr  = 'DELETE FROM menu_item ';
		$queryStr .=   'WHERE mi_serial in (' . $serial . ') ';
		$this->execStatement($queryStr, array());
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * メニュー項目のすべて削除
	 *
	 * @return						true=成功、false=失敗
	 */
	function delMenuAllItem()
	{
		// トランザクション開始
		$this->startTransaction();
		
		// レコードを削除
		$queryStr  = 'DELETE FROM menu_item ';
		$this->execStatement($queryStr, array());
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * メニュー項目IDの重複チェック
	 *
	 * @param string  $id			メニュー項目ID
	 * @param string  $lang			言語ID
	 * @return bool					true = 存在する、false = 存在しない
	 */
	function isExistsMenuId($id, $lang)
	{
		$queryStr = 'SELECT * from menu_item ';
		$queryStr .=  'WHERE mi_id = ? ';
		$queryStr .=    'AND mi_language_id = ?';
		return $this->isRecordExists($queryStr, array($id, $lang));
	}
	/**
	 * ページIDのリストを取得
	 *
	 * @param function $callback	コールバック関数
	 * @param int $type				リストの種別
	 * @return						なし
	 */
	function getPageIdList($callback, $type)
	{
		$queryStr = 'select * from _page_id ';
		$queryStr .=  'where pg_type = ? ';
		$queryStr .=  'order by pg_priority';
		$this->selectLoop($queryStr, array($type), $callback);
	}
	/**
	 * メニュー項目の表示順を変更する
	 *
	 * @param string  $menuId			メニューID
	 * @param string  $lang				言語ID
	 * @param array $menuItemNoArray	並び順
	 * @return bool					true = 成功、false = 失敗
	 */
	function orderMenuItems($menuId, $lang, $menuItemNoArray)
	{
		global $gEnvManager;

		// メニュー項目をすべて取得
		$queryStr = 'SELECT * FROM menu_item ';
		$queryStr .=  'WHERE mi_visible = true ';		// 表示中の項目
		$queryStr .=    'AND mi_menu_id = ? ';
		$queryStr .=    'AND mi_language_id = ? ';
		$queryStr .=  'ORDER BY mi_index';
		$ret = $this->selectRecords($queryStr, array($menuId, $lang), $rows);
		if (!$ret) return false;
	
		// メニュー数をチェックし、異なっている場合はエラー
		$menuItemCount = count($rows);
		if ($menuItemCount != count($menuItemNoArray)) return false;
		
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $gEnvManager->getCurrentUserId();	// 現在のユーザ
		
		// トランザクション開始
		$this->startTransaction();

		for ($i = 0; $i < $menuItemCount; $i++){
			$serialNo = $rows[$menuItemNoArray[$i]]['mi_serial'];
			$index = $rows[$i]['mi_index'];

			// 既存項目を更新
			$queryStr  = 'UPDATE menu_item ';
			$queryStr .=   'SET ';
			$queryStr .=     'mi_index = ?, ';
			$queryStr .=     'mi_update_user_id = ?, ';
			$queryStr .=     'mi_update_dt = ? ';
			$queryStr .=   'WHERE mi_serial = ? ';
			$this->execStatement($queryStr, array($index, $userId, $now, $serialNo));
		}
										
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * コンテンツ項目一覧を取得
	 *
	 * @param string	$lang				言語
	 * @param string	$contentType		コンテンツタイプ
	 * @param function	$callback			コールバック関数

	 * @return 			なし
	 */
	function getVisibleAllContents($lang, $contentType, $callback)
	{
		$queryStr = 'SELECT * FROM content ';
		$queryStr .=  'WHERE cn_type = ? ';
		$queryStr .=    'AND cn_language_id = ? ';
		$queryStr .=    'AND cn_visible = true ';		// 画面に表示可能
		$queryStr .=    'AND cn_deleted = false ';		// 削除されていない
		$queryStr .=  'ORDER BY cn_id';
		$this->selectLoop($queryStr, array($contentType, $lang), $callback, null);
	}
}
?>
