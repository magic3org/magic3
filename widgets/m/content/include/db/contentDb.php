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
 * @version    SVN: $Id: contentDb.php 1340 2008-12-11 09:41:24Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getDbPath() . '/baseDb.php');

class contentDb extends BaseDb
{
	/**
	 * コンテンツ項目を取得
	 *
	 * @param string	$contentType		コンテンツタイプ
	 * @param function	$callback			コールバック関数
	 * @param array		$contentIdArray		コンテンツID
	 * @param string	$lang				言語
	 * @return 			なし
	 */
	function getContentItems($contentType, $callback, $contentIdArray, $lang)
	{
		// コンテンツIDの指定がない場合は、デフォルト値を取得
		if ($contentIdArray == null){
			$queryStr = 'SELECT * FROM content ';
			$queryStr .=  'WHERE cn_visible = true ';
			$queryStr .=    'AND cn_default = true ';
			$queryStr .=    'AND cn_deleted = false ';		// 削除されていない
			$queryStr .=    'AND cn_type = ? ';
			$queryStr .=    'AND cn_language_id = ? ';
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
			$queryStr .=  'ORDER BY no';
			$this->selectLoop($queryStr, array($contentType, $lang), $callback, null);
		}
	}
	/**
	 * コンテンツ項目を検索
	 *
	 * @param string	$contentType		コンテンツタイプ
	 * @param int		$limit				取得する項目数
	 * @param int		$page				取得するページ(1～)
	 * @param string	$keyword			検索キーワード
	 * @param string	$langId				言語
	 * @param bool		$all				すべてのデータを取得するか、ユーザ制限のないデータを取得するかを指定
	 * @param function	$callback			コールバック関数
	 * @return 			なし
	 */
	function searchContentByKeyword($contentType, $limit, $page, $keyword, $langId, $all, $callback)
	{
		$offset = $limit * ($page -1);
		if ($offset < 0) $offset = 0;
		
		$params = array();
		$queryStr = 'SELECT * FROM content ';
		$queryStr .=  'WHERE cn_visible = true ';
		$queryStr .=    'AND cn_deleted = false ';		// 削除されていない
		$queryStr .=    'AND cn_type = ? ';$params[] = $contentType;
		$queryStr .=    'AND cn_language_id = ? ';$params[] = $langId;
		if (!$all) $queryStr .=    'AND cn_user_limited = false ';		// ユーザ制限のないデータ

		// タイトルと記事を検索
		if (!empty($keyword)){
			// 「'"\」文字をエスケープ
			$keyword = addslashes($keyword);
			$queryStr .=    'AND (cn_name LIKE \'%' . $keyword . '%\' ';
			$queryStr .=    'OR cn_html LIKE \'%' . $keyword . '%\' ';
			$queryStr .=    'OR cn_description LIKE \'%' . $keyword . '%\') ';
		}
		$queryStr .=  'ORDER BY cn_create_dt desc limit ' . $limit . ' offset ' . $offset;
		$this->selectLoop($queryStr, $params, $callback, null);
	}
	/**
	 * コンテンツ項目一覧を取得(管理用)
	 
	 * @param string	$contentType		コンテンツタイプ
	 * @param function	$callback			コールバック関数
	 * @param string	$lang				言語
	 * @return 			なし
	 */
	function getAllContentItems($contentType, $callback, $lang)
	{
		$queryStr = 'SELECT * FROM content LEFT JOIN _login_user ON cn_create_user_id = lu_id AND lu_deleted = false ';
		$queryStr .=  'WHERE cn_type = ? ';
		$queryStr .=    'AND cn_language_id = ? ';
		$queryStr .=    'AND cn_deleted = false ';		// 削除されていない
		$queryStr .=  'ORDER BY cn_id';
		$this->selectLoop($queryStr, array($contentType, $lang), $callback, null);
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
	 * @param string  $key			外部参照用キー
	 * @param int     $userId		更新者ユーザID
	 * @param int     $newContentId	新規コンテンツID
	 * @param int     $newSerial	新規シリアル番号
	 * @return bool					true = 成功、false = 失敗
	 */
	function updateContentItem($contentType, $contentId, $lang, $name, $html, $visible, $default, $key, $userId, &$newContentId, &$newSerial)
	{
		$historyIndex = 0;		// 履歴番号
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
			$desc = '';
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
			$desc = $row['cn_description'];
				
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
		$queryStr .=  '(cn_type, cn_id, cn_language_id, cn_history_index, cn_name, cn_description, cn_html, cn_visible, cn_default, cn_key, cn_create_user_id, cn_create_dt) ';
		$queryStr .=  'VALUES ';
		$queryStr .=  '(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
		$this->execStatement($queryStr, array($contentType, $contId, $lang, $historyIndex, $name, $desc, $html, $visible, $default, $key, $userId, $now));

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
	 * @param array $serial			シリアルNo
	 * @param int $userId			ユーザID(データ更新者)
	 * @return						true=成功、false=失敗
	 */
	function delContentItem($serial, $userId)
	{
		$now = date("Y/m/d H:i:s");	// 現在日時
		
		// 引数エラーチェック
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
		$queryStr .=   'WHERE cn_serial in (' . implode($serial, ',') . ') ';
		$this->execStatement($queryStr, array($userId, $now));

		// トランザクション確定
		$ret = $this->endTransaction();
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
	 * デフォルトのコンテンツ項目総数取得
	 *
	 * @param string	$contentType		コンテンツタイプ
	 * @param string	$lang				言語
	 * @return int					総数
	 */
	function getDefaultContentCount($contentType, $lang)
	{
		$queryStr = 'SELECT * FROM content ';
		$queryStr .=  'WHERE cn_visible = true ';
		$queryStr .=    'AND cn_default = true ';
		$queryStr .=    'AND cn_deleted = false ';		// 削除されていない
		$queryStr .=    'AND cn_type = ? ';
		$queryStr .=    'AND cn_language_id = ? ';
		return $this->selectRecordCount($queryStr, array($contentType, $lang));
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
