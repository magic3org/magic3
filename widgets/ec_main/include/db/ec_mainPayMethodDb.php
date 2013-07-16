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
 * @version    SVN: $Id: ec_mainPayMethodDb.php 5434 2012-12-06 12:32:21Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getDbPath() . '/baseDb.php');

class ec_mainPayMethodDb extends BaseDb
{
	/**
	 * シリアル番号から支払方法IDを取得
	 *
	 * @param int		$serial				シリアル番号
	 * @return string						支払方法ID。該当なしのときは空文字列
	 */
	function getPayMethodDefIdBySerial($serial)
	{
		$id = '';
		$queryStr = 'SELECT * FROM pay_method_def ';
		$queryStr .=  'WHERE po_deleted = false ';
		$queryStr .=  'AND po_serial = ? ';
		$ret = $this->selectRecord($queryStr, array($serial), $row);
		if ($ret) $id = $row['po_id'];
		return $id;
	}
	/**
	 * 支払方法をシリアル番号で削除
	 *
	 * @param array   $serial		シリアルNo
	 * @return						true=成功、false=失敗
	 */
	function deletePayMethodDefBySerial($serial)
	{
		// 引数のエラーチェック
		if (!is_array($serial)) return false;
		if (count($serial) <= 0) return true;
		
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $this->gEnv->getCurrentUserId();	// 現在のユーザ
		
		// トランザクション開始
		$this->startTransaction();
		
		// 指定のシリアルNoのレコードが削除状態でないかチェック
		for ($i = 0; $i < count($serial); $i++){
			$queryStr  = 'SELECT * FROM pay_method_def ';
			$queryStr .=   'WHERE po_deleted = false ';		// 未削除
			$queryStr .=     'AND po_serial = ? ';
			$ret = $this->isRecordExists($queryStr, array($serial[$i]));
			// 存在しない場合は、既に削除されたとして終了
			if (!$ret){
				$this->endTransaction();
				return false;
			}
		}
		
		// レコードを削除
		$queryStr  = 'UPDATE pay_method_def ';
		$queryStr .=   'SET po_deleted = true, ';	// 削除
		$queryStr .=     'po_update_user_id = ?, ';
		$queryStr .=     'po_update_dt = ? ';
		$queryStr .=   'WHERE po_serial in (' . implode($serial, ',') . ') ';
		$this->execStatement($queryStr, array($userId, $now));
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * 支払い方法を取得
	 *
	 * @param string	$id					支払い方法ID
	 * @param string	$lang				言語
	 * @param int     	$setId				セットID
	 * @param array     $row				レコード
	 * @return bool							取得 = true, 取得なし= false
	 */
	function getPayMethodDef($id, $lang, $setId, &$row)
	{
		$queryStr = 'SELECT * FROM pay_method_def ';
		$queryStr .=  'WHERE po_deleted = false ';
		$queryStr .=  'AND po_id = ? ';
		$queryStr .=  'AND po_language_id = ? ';
		$queryStr .=  'AND po_set_id = ? ';
		$ret = $this->selectRecord($queryStr, array($id, $lang, $setId), $row);
		return $ret;
	}
	/**
	 * 支払い方法定義を取得
	 *
	 * @param string	$lang				言語
	 * @param int     	$setId			セットID
	 * @param function	$callback		コールバック関数
	 * @return 			なし
	 */
	function getAllPayMethodDef($lang, $setId, $callback)
	{
		$queryStr = 'SELECT * FROM pay_method_def ';
		$queryStr .=  'WHERE po_deleted = false ';
		$queryStr .=  'AND po_language_id = ? ';
		$queryStr .=  'AND po_set_id = ? ';
		$queryStr .=  'ORDER BY po_index ';
		$this->selectLoop($queryStr, array($lang, $setId), $callback, null);
	}
	/**
	 * 支払い方法更新
	 *
	 * @param int  	  $id			支払い方法ID
	 * @param int     $setId		セットID
	 * @param string  $lang			言語
	 * @param string  $name			名前
	 * @param string  $desc			説明
	 * @param int  $index			表示順
	 * @param bool  $visible		表示状況
	 * @param string  $iwidgetId	インナーウィジェットID
	 * @param string  $param		パラメータ
	 * @return bool					true = 成功、false = 失敗
	 */
	function updatePayMethodDef($id, $lang, $setId, $name, $desc, $index, $visible, $iwidgetId = '', $param = '')
	{
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $this->gEnv->getCurrentUserId();	// 現在のユーザ
		
		// トランザクション開始
		$this->startTransaction();
		
		// 指定のレコードの履歴インデックス取得
		$queryStr  = 'SELECT * FROM pay_method_def ';
		$queryStr .=   'WHERE po_id = ? ';
		$queryStr .=   'AND po_language_id = ? ';
		$queryStr .=   'AND po_set_id = ? ';
		$queryStr .=  'ORDER BY po_history_index desc ';
		$ret = $this->selectRecord($queryStr, array($id, $lang, $setId), $row);
		if ($ret){
			if (!$row['po_deleted']){		// 削除されていない場合
				// レコードを削除
				$queryStr  = 'UPDATE pay_method_def ';
				$queryStr .=   'SET po_deleted = true, ';
				$queryStr .=   'po_update_user_id = ?, ';
				$queryStr .=   'po_update_dt = ? ';			
				$queryStr .=   'WHERE po_serial = ? ';
				$this->execStatement($queryStr, array($userId, $now, $row['po_serial']));
			}
			$historyIndex = $row['po_history_index'] + 1;
		} else {		// 新規の場合
			$historyIndex = 0;
		}

		// 新規レコードを追加
		$queryStr  = 'INSERT INTO pay_method_def (';
		$queryStr .=   'po_id, ';
		$queryStr .=   'po_language_id, ';
		$queryStr .=   'po_set_id, ';
		$queryStr .=   'po_history_index, ';
		$queryStr .=   'po_name, ';
		$queryStr .=   'po_description, ';
		$queryStr .=   'po_iwidget_id, ';
		$queryStr .=   'po_param, ';
		$queryStr .=   'po_index, ';
		$queryStr .=   'po_visible, ';
		$queryStr .=   'po_create_user_id, ';
		$queryStr .=   'po_create_dt ';
		$queryStr .= ') VALUES (';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?) ';
		$ret = $this->execStatement($queryStr, array($id, $lang, $setId, $historyIndex, $name, $desc, $iwidgetId, $param, $index, $visible, $userId, $now));
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * 支払い方法定義の削除
	 *
	 * @param int  	  $id			支払い方法ID
	 * @param int     $setId		セットID
	 * @param string  $lang			言語
	 * @return						true=成功、false=失敗
	 */
	function deletePayMethodDef($id, $lang, $setId)
	{
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $this->gEnv->getCurrentUserId();	// 現在のユーザ
		
		// トランザクション開始
		$this->startTransaction();
		
		// 指定のレコードの履歴インデックス取得
		$queryStr  = 'SELECT * FROM pay_method_def ';
		$queryStr .=   'WHERE po_id = ? ';
		$queryStr .=   'AND po_language_id = ? ';
		$queryStr .=   'AND po_set_id = ? ';
		$queryStr .=  'ORDER BY po_history_index desc ';
		$ret = $this->selectRecord($queryStr, array($id, $lang, $setId), $row);
		if (!$ret){
			$this->endTransaction();		// トランザクション終了
			return false;
		}
		if ($row['po_deleted']){
			$this->endTransaction();		// トランザクション終了
			return false;		// 削除されていれば終了
		}
		
		// レコードを削除
		$queryStr  = 'UPDATE pay_method_def ';
		$queryStr .=   'SET po_deleted = true, ';
		$queryStr .=   'po_update_user_id = ?, ';
		$queryStr .=   'po_update_dt = ? ';			
		$queryStr .=   'WHERE po_serial = ? ';
		$this->execStatement($queryStr, array($userId, $now, $row['po_serial']));
			
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * 支払い方法IDの存在チェック
	 *
	 * @param string	$lang		言語
	 * @param int     	$setId		セットID
	 * @param string  	$id			ID
	 * @return bool					true=存在する、false=存在しない
	 */
	function isExistsPayMethodId($lang, $setId, $id)
	{
		$queryStr = 'SELECT * FROM pay_method_def ';
		$queryStr .=  'WHERE po_deleted = false ';
		$queryStr .=  'AND po_id = ? ';
		$queryStr .=  'AND po_language_id = ? ';
		$queryStr .=  'AND po_set_id = ? ';
		return $this->isRecordExists($queryStr, array($id, $lang, $setId));
	}
	/**
	 * 最大表示順を取得
	 *
	 * @param string	$lang		言語
	 * @param int     	$setId		セットID
	 * @return int					最大表示順
	 */
	function getMaxPayMethodIndex($lang, $setId)
	{
		$queryStr = 'SELECT max(po_index) as mindex FROM pay_method_def ';
		$queryStr .=  'WHERE po_deleted = false ';
		$queryStr .=  'AND po_language_id = ? ';
		$queryStr .=  'AND po_set_id = ? ';
		$ret = $this->selectRecord($queryStr, array($lang, $setId), $row);
		if ($ret){
			$index = $row['mindex'];
		} else {
			$index = 0;
		}
		return $index;
	}
}
?>
