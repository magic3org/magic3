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
 * @version    SVN: $Id: newsDb.php 416 2008-03-19 10:40:09Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getDbPath() . '/baseDb.php');

class newsDb extends BaseDb
{
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
		
		// トランザクションスタート
		$this->startTransaction();
				
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
		
		// トランザクション終了
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * コンテンツ項目の削除
	 *
	 * @param string  $contentType	コンテンツタイプ
	 * @param string  $contentId	コンテンツID(0のとき新規)
	 * @param string  $lang			言語ID
	 * @param int $userId			ユーザID(データ更新者)
	 * @return						true=成功、false=失敗
	 */
	function delContentItem($contentType, $contentId, $lang, $userId)
	{
		$now = date("Y/m/d H:i:s");	// 現在日時
		
		// トランザクションスタート
		$this->startTransaction();
		
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
		
		// トランザクション終了
		$ret = $this->endTransaction();
		return $ret;
	}
}
?>
