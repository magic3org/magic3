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
 * @version    SVN: $Id: ec_mainDelivMethodDb.php 5434 2012-12-06 12:32:21Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getDbPath() . '/baseDb.php');

class ec_mainDelivMethodDb extends BaseDb
{
	/**
	 * シリアル番号から配送方法IDを取得
	 *
	 * @param int		$serial				シリアル番号
	 * @return string						配送方法ID。該当なしのときは空文字列
	 */
	function getDelivMethodDefIdBySerial($serial)
	{
		$id = '';
		$queryStr = 'SELECT * FROM delivery_method_def ';
		$queryStr .=  'WHERE do_deleted = false ';
		$queryStr .=  'AND do_serial = ? ';
		$ret = $this->selectRecord($queryStr, array($serial), $row);
		if ($ret) $id = $row['do_id'];
		return $id;
	}
	/**
	 * 配送方法をシリアル番号で削除
	 *
	 * @param array   $serial		シリアルNo
	 * @return						true=成功、false=失敗
	 */
	function deleteDelivMethodDefBySerial($serial)
	{
		global $gEnvManager;
		
		// 引数のエラーチェック
		if (!is_array($serial)) return false;
		if (count($serial) <= 0) return true;
		
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $gEnvManager->getCurrentUserId();	// 現在のユーザ
		
		// トランザクション開始
		$this->startTransaction();
		
		// 指定のシリアルNoのレコードが削除状態でないかチェック
		for ($i = 0; $i < count($serial); $i++){
			$queryStr  = 'SELECT * FROM delivery_method_def ';
			$queryStr .=   'WHERE do_deleted = false ';		// 未削除
			$queryStr .=     'AND do_serial = ? ';
			$ret = $this->isRecordExists($queryStr, array($serial[$i]));
			// 存在しない場合は、既に削除されたとして終了
			if (!$ret){
				$this->endTransaction();
				return false;
			}
		}
		
		// レコードを削除
		$queryStr  = 'UPDATE delivery_method_def ';
		$queryStr .=   'SET do_deleted = true, ';	// 削除
		$queryStr .=     'do_update_user_id = ?, ';
		$queryStr .=     'do_update_dt = ? ';
		$queryStr .=   'WHERE do_serial in (' . implode($serial, ',') . ') ';
		$this->execStatement($queryStr, array($userId, $now));
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * 配送方法を取得
	 *
	 * @param string	$id					配送方法ID
	 * @param string	$lang				言語
	 * @param int     	$setId				セットID
	 * @param array     $row				レコード
	 * @return bool							取得 = true, 取得なし= false
	 */
	function getDelivMethodDef($id, $lang, $setId, &$row)
	{
		$queryStr = 'SELECT * FROM delivery_method_def ';
		$queryStr .=  'WHERE do_deleted = false ';
		$queryStr .=  'AND do_id = ? ';
		$queryStr .=  'AND do_language_id = ? ';
		$queryStr .=  'AND do_set_id = ? ';
		$ret = $this->selectRecord($queryStr, array($id, $lang, $setId), $row);
		return $ret;
	}
	/**
	 * 配送方法定義を取得
	 *
	 * @param string	$lang				言語
	 * @param int     	$setId			セットID
	 * @param function	$callback		コールバック関数
	 * @return 			なし
	 */
	function getAllDelivMethodDef($lang, $setId, $callback)
	{
		$queryStr = 'SELECT * FROM delivery_method_def ';
		$queryStr .=  'WHERE do_deleted = false ';
		$queryStr .=  'AND do_language_id = ? ';
		$queryStr .=  'AND do_set_id = ? ';
		$queryStr .=  'ORDER BY do_index ';
		$this->selectLoop($queryStr, array($lang, $setId), $callback, null);
	}
	/**
	 * 配送方法更新
	 *
	 * @param int  	  $id			配送方法ID
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
	function updateDelivMethodDef($id, $lang, $setId, $name, $desc, $index, $visible, $iwidgetId = '', $param = '')
	{
		global $gEnvManager;
		
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $gEnvManager->getCurrentUserId();	// 現在のユーザ
		
		// トランザクション開始
		$this->startTransaction();
		
		// 指定のレコードの履歴インデックス取得
		$queryStr  = 'SELECT * FROM delivery_method_def ';
		$queryStr .=   'WHERE do_id = ? ';
		$queryStr .=   'AND do_language_id = ? ';
		$queryStr .=   'AND do_set_id = ? ';
		$queryStr .=  'ORDER BY do_history_index desc ';
		$ret = $this->selectRecord($queryStr, array($id, $lang, $setId), $row);
		if ($ret){
			if (!$row['do_deleted']){		// 削除されていない場合
				// レコードを削除
				$queryStr  = 'UPDATE delivery_method_def ';
				$queryStr .=   'SET do_deleted = true, ';
				$queryStr .=   'do_update_user_id = ?, ';
				$queryStr .=   'do_update_dt = ? ';			
				$queryStr .=   'WHERE do_serial = ? ';
				$this->execStatement($queryStr, array($userId, $now, $row['do_serial']));
			}
			$historyIndex = $row['do_history_index'] + 1;
		} else {		// 新規の場合
			$historyIndex = 0;
		}

		// 新規レコードを追加
		$queryStr  = 'INSERT INTO delivery_method_def (';
		$queryStr .=   'do_id, ';
		$queryStr .=   'do_language_id, ';
		$queryStr .=   'do_set_id, ';
		$queryStr .=   'do_history_index, ';
		$queryStr .=   'do_name, ';
		$queryStr .=   'do_description, ';
		$queryStr .=   'do_iwidget_id, ';
		$queryStr .=   'do_param, ';
		$queryStr .=   'do_index, ';
		$queryStr .=   'do_visible, ';
		$queryStr .=   'do_create_user_id, ';
		$queryStr .=   'do_create_dt ';
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
	 * 配送方法定義の削除
	 *
	 * @param int  	  $id			配送方法ID
	 * @param int     $setId		セットID
	 * @param string  $lang			言語
	 * @return						true=成功、false=失敗
	 */
	function deleteDelivMethodDef($id, $lang, $setId)
	{
		global $gEnvManager;
		
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $gEnvManager->getCurrentUserId();	// 現在のユーザ
		
		// トランザクション開始
		$this->startTransaction();
		
		// 指定のレコードの履歴インデックス取得
		$queryStr  = 'SELECT * FROM delivery_method_def ';
		$queryStr .=   'WHERE do_id = ? ';
		$queryStr .=   'AND do_language_id = ? ';
		$queryStr .=   'AND do_set_id = ? ';
		$queryStr .=  'ORDER BY do_history_index desc ';
		$ret = $this->selectRecord($queryStr, array($id, $lang, $setId), $row);
		if (!$ret){
			$this->endTransaction();		// トランザクション終了
			return false;
		}
		if ($row['do_deleted']){
			$this->endTransaction();		// トランザクション終了
			return false;		// 削除されていれば終了
		}
		
		// レコードを削除
		$queryStr  = 'UPDATE delivery_method_def ';
		$queryStr .=   'SET do_deleted = true, ';
		$queryStr .=   'do_update_user_id = ?, ';
		$queryStr .=   'do_update_dt = ? ';			
		$queryStr .=   'WHERE do_serial = ? ';
		$this->execStatement($queryStr, array($userId, $now, $row['do_serial']));
			
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * 配送方法IDの存在チェック
	 *
	 * @param string	$lang		言語
	 * @param int     	$setId		セットID
	 * @param string  	$id			ID
	 * @return bool					true=存在する、false=存在しない
	 */
	function isExistsDelivMethodId($lang, $setId, $id)
	{
		$queryStr = 'SELECT * FROM delivery_method_def ';
		$queryStr .=  'WHERE do_deleted = false ';
		$queryStr .=  'AND do_id = ? ';
		$queryStr .=  'AND do_language_id = ? ';
		$queryStr .=  'AND do_set_id = ? ';
		return $this->isRecordExists($queryStr, array($id, $lang, $setId));
	}
	/**
	 * 最大表示順を取得
	 *
	 * @param string	$lang		言語
	 * @param int     	$setId		セットID
	 * @return int					最大表示順
	 */
	function getMaxDelivMethodIndex($lang, $setId)
	{
		$queryStr = 'SELECT max(do_index) as mindex FROM delivery_method_def ';
		$queryStr .=  'WHERE do_deleted = false ';
		$queryStr .=  'AND do_language_id = ? ';
		$queryStr .=  'AND do_set_id = ? ';
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
