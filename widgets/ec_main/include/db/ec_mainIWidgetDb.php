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
 * @version    SVN: $Id: ec_mainIWidgetDb.php 5434 2012-12-06 12:32:21Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getDbPath() . '/baseDb.php');

class ec_mainIWidgetDb extends BaseDb
{
	/**
	 * シリアル番号からインナーウィジェットメソッドIDを取得
	 *
	 * @param int		$serial				シリアル番号
	 * @return string						配送方法ID。該当なしのときは空文字列
	 */
	function getMethodIdBySerial($serial)
	{
		$id = '';
		$queryStr = 'SELECT * FROM _iwidget_method ';
		$queryStr .=  'WHERE id_deleted = false ';
		$queryStr .=  'AND id_serial = ? ';
		$ret = $this->selectRecord($queryStr, array($serial), $row);
		if ($ret) $id = $row['id_id'];
		return $id;
	}
	/**
	 * 配送方法をシリアル番号で削除
	 *
	 * @param array   $serial		シリアルNo
	 * @return						true=成功、false=失敗
	 */
	function deleteMethodBySerial($serial)
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
			$queryStr  = 'SELECT * FROM _iwidget_method ';
			$queryStr .=   'WHERE id_deleted = false ';		// 未削除
			$queryStr .=     'AND id_serial = ? ';
			$ret = $this->isRecordExists($queryStr, array($serial[$i]));
			// 存在しない場合は、既に削除されたとして終了
			if (!$ret){
				$this->endTransaction();
				return false;
			}
		}
		
		// レコードを削除
		$queryStr  = 'UPDATE _iwidget_method ';
		$queryStr .=   'SET id_deleted = true, ';	// 削除
		$queryStr .=     'id_update_user_id = ?, ';
		$queryStr .=     'id_update_dt = ? ';
		$queryStr .=   'WHERE id_serial in (' . implode($serial, ',') . ') ';
		$this->execStatement($queryStr, array($userId, $now));
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * インナーウィジェットメソッドを取得
	 *
	 * @param string    $type			メソッド種別
	 * @param string	$id				メソッドID
	 * @param string	$lang			言語
	 * @param int     	$setId			セットID
	 * @param array     $row			レコード
	 * @return bool						取得 = true, 取得なし= false
	 */
	function getMethod($type, $id, $lang, $setId, &$row)
	{
		$queryStr = 'SELECT * FROM _iwidget_method ';
		$queryStr .=  'WHERE id_deleted = false ';
		$queryStr .=  'AND id_type = ? ';
		$queryStr .=  'AND id_id = ? ';
		$queryStr .=  'AND id_language_id = ? ';
		$queryStr .=  'AND id_set_id = ? ';
		$ret = $this->selectRecord($queryStr, array($type, $id, $lang, $setId), $row);
		return $ret;
	}
	/**
	 * インナーウィジェットメソッド定義を取得
	 *
	 * @param string    $type			メソッド種別
	 * @param string	$lang			言語
	 * @param int     	$setId			セットID
	 * @param function	$callback		コールバック関数
	 * @return 			なし
	 */
	function getAllMethod($type, $lang, $setId, $callback)
	{
		$queryStr = 'SELECT * FROM _iwidget_method ';
		$queryStr .=  'WHERE id_deleted = false ';
		$queryStr .=  'AND id_type = ? ';
		$queryStr .=  'AND id_language_id = ? ';
		$queryStr .=  'AND id_set_id = ? ';
		$queryStr .=  'ORDER BY id_index ';
		$this->selectLoop($queryStr, array($type, $lang, $setId), $callback);
	}
	/**
	 * インナーウィジェットメソッド更新
	 *
	 * @param int  	  $id			メソッドID(空のときは新規追加)
	 * @param string  $type			メソッドタイプ
	 * @param string  $lang			言語
	 * @param int     $setId		セットID
	 * @param string  $name			名前
	 * @param string  $descShort			簡易説明
	 * @param string  $desc			説明
	 * @param int  $index			表示順
	 * @param bool  $visible		表示状況
	 * @param string  $iwidgetId	インナーウィジェットID
	 * @param string  $param		パラメータ
	 * @return bool					true = 成功、false = 失敗
	 */
	function updateMethod(&$id, $type, $lang, $setId, $name, $descShort, $desc, $index, $visible, $iwidgetId = '', $param = '')
	{
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $this->gEnv->getCurrentUserId();	// 現在のユーザ
		
		// トランザクション開始
		$this->startTransaction();

		$historyIndex = 0;
		if (empty($id)){
			// 新規のIDを求める
			$id = 1;
			$queryStr  = 'SELECT MAX(id_id) AS ms FROM _iwidget_method ';
			$queryStr .=   'WHERE id_type = ? ';
			$ret = $this->selectRecord($queryStr, array($type), $maxRow);
			if ($ret) $id = $maxRow['ms'] + 1;
		} else {
			// 指定のレコードの履歴インデックス取得
			$queryStr  = 'SELECT * FROM _iwidget_method ';
			$queryStr .=   'WHERE id_type = ? ';
			$queryStr .=   'AND id_id = ? ';
			$queryStr .=   'AND id_language_id = ? ';
			$queryStr .=   'AND id_set_id = ? ';
			$queryStr .=  'ORDER BY id_history_index DESC ';
			$ret = $this->selectRecord($queryStr, array($type, $id, $lang, $setId), $row);
			if ($ret){
				if ($row['id_deleted']){		// 削除されている場合は終了
					$this->endTransaction();
					return false;
				} else {		// 削除されていない場合
					// レコードを削除
					$queryStr  = 'UPDATE _iwidget_method ';
					$queryStr .=   'SET id_deleted = true, ';
					$queryStr .=     'id_update_user_id = ?, ';
					$queryStr .=     'id_update_dt = ? ';			
					$queryStr .=   'WHERE id_serial = ? ';
					$this->execStatement($queryStr, array($userId, $now, $row['id_serial']));
				}
				$historyIndex = $row['id_history_index'] + 1;
			} else {		// データが存在しない場合は終了
				$this->endTransaction();
				return false;
			}
		}

		// 新規レコードを追加
		$queryStr  = 'INSERT INTO _iwidget_method (';
		$queryStr .=   'id_type, ';
		$queryStr .=   'id_id, ';
		$queryStr .=   'id_language_id, ';
		$queryStr .=   'id_set_id, ';
		$queryStr .=   'id_history_index, ';
		$queryStr .=   'id_name, ';
		$queryStr .=   'id_desc_short, ';
		$queryStr .=   'id_desc, ';
		$queryStr .=   'id_iwidget_id, ';
		$queryStr .=   'id_param, ';
		$queryStr .=   'id_index, ';
		$queryStr .=   'id_visible, ';
		$queryStr .=   'id_create_user_id, ';
		$queryStr .=   'id_create_dt ';
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
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?) ';
		$ret = $this->execStatement($queryStr, array($type, $id, $lang, $setId, $historyIndex, $name, $descShort, $desc, $iwidgetId, $param, $index, $visible, $userId, $now));
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * 最大表示順を取得
	 *
	 * @param string    $type			メソッド種別
	 * @param string	$lang		言語
	 * @param int     	$setId		セットID
	 * @return int					最大表示順
	 */
	function getMaxMethodIndex($type, $lang, $setId)
	{
		$queryStr  = 'SELECT max(id_index) AS mindex FROM _iwidget_method ';
		$queryStr .=   'WHERE id_deleted = false ';
		$queryStr .=   'AND id_type = ? ';
		$queryStr .=   'AND id_language_id = ? ';
		$queryStr .=   'AND id_set_id = ? ';
		$ret = $this->selectRecord($queryStr, array($type, $lang, $setId), $row);
		if ($ret){
			$index = $row['mindex'];
		} else {
			$index = 0;
		}
		return $index;
	}
}
?>
