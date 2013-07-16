<?php
/**
 * DBクラス
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    ポータル用コンテンツ更新情報
 * @author     株式会社 毎日メディアサービス
 * @copyright  Copyright 2009 株式会社 毎日メディアサービス.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: portal_updateinfoDb.php 2724 2009-12-21 07:41:16Z fishbone $
 * @link       http://www.m-media.co.jp
 */
require_once($gEnvManager->getDbPath() . '/baseDb.php');

class portal_updateinfoDb extends BaseDb
{
	/**
	 * 最近の登録順にコンテンツ更新情報リストを取得
	 *
	 * @param string   $typeId		コンテンツタイプ
	 * @param function $callback	コールバック関数
	 * @return						なし
	 */
	function getUpdateInfoList($typeId, $limit, $callback)
	{
		$queryStr  = 'SELECT * FROM news ';
		$queryStr .=   'WHERE nw_deleted = false ';
		$queryStr .=     'AND nw_type = ? ';
		$queryStr .=   'ORDER BY nw_regist_dt DESC ';
		$queryStr .=    'LIMIT ' . intval($limit);
		$this->selectLoop($queryStr, array($typeId), $callback);
	}
	/**
	 * コンテンツ更新情報項目の削除
	 *
	 * @param array $serial			シリアルNo
	 * @return						true=成功、false=失敗
	 */
	function delUpdateInfoItem($serial)
	{
		$now = date("Y/m/d H:i:s");	// 現在日時
		$user = $this->gEnv->getCurrentUserId();	// 現在のユーザ
		
		if (!is_array($serial) || count($serial) <= 0) return true;
		
		// トランザクション開始
		$this->startTransaction();
		
		// 指定のシリアルNoのレコードが削除状態でないかチェック
		for ($i = 0; $i < count($serial); $i++){
			$queryStr  = 'SELECT * FROM news ';
			$queryStr .=   'WHERE nw_deleted = false ';		// 未削除
			$queryStr .=     'AND nw_serial = ? ';
			$ret = $this->isRecordExists($queryStr, array($serial[$i]));
			// 存在しない場合は、既に削除されたとして終了
			if (!$ret){
				$this->endTransaction();
				return false;
			}
		}
		
		// レコードを削除
		$queryStr  = 'UPDATE news ';
		$queryStr .=   'SET nw_deleted = true, ';	// 削除
		$queryStr .=     'nw_update_user_id = ?, ';
		$queryStr .=     'nw_update_dt = ? ';
		$queryStr .=   'WHERE nw_serial in (' . implode($serial, ',') . ') ';
		$this->execStatement($queryStr, array($user, $now));
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
}
?>
