<?php
/**
 * 運用ログDBクラス
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2014 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getDbPath() . '/baseDb.php');

class admin_messageDb extends BaseDb
{
	/**
	 * 運用ログ取得
	 *
	 * @param int		$level		取得ログのレベル
	 * @param int		$status		取得するデータの状況(0=すべて、1=未参照のみ、2=参照済みのみ)
	 * @param int		$limit		取得する項目数
	 * @param int		$page		取得するページ(1～)
	 * @param function	$callback	コールバック関数
	 * @return						なし
	 */
	function getOpeLogList($level, $status, $limit, $page, $callback)
	{
		$offset = $limit * ($page -1);
		if ($offset < 0) $offset = 0;
		
		$params = array();
		$queryStr  = 'SELECT * FROM _operation_log LEFT JOIN _operation_type ON ol_type = ot_id ';
		
		// 取得ログレベルを指定
		$queryStr .= 'WHERE ol_show_top = true ';		// トップ表示メッセージ
		$queryStr .=   'AND ot_level >= ? '; $params[] = $level;

		// 参照状況を制限
		if ($status == 1){		// 未参照
			$queryStr .= 'AND ol_checked = false ';
		} else if ($status == 2){	// 参照済み
			$queryStr .= 'AND ol_checked = true ';
		}
		$queryStr .=  'ORDER BY ol_serial DESC limit ' . $limit . ' offset ' . $offset;
		$this->selectLoop($queryStr, $params, $callback);
	}
	/**
	 * 運用ログの確認状況を更新
	 *
	 * @param string  $serial		シリアル番号
	 * @param bool    $checked		確認状況
	 * @return						true=正常、false=異常
	 */
	function updateOpeLogChecked($serial, $checked)
	{
		// トランザクションスタート
		$this->startTransaction();
		
		$queryStr = 'UPDATE _operation_log SET ol_checked = ? WHERE ol_serial = ?';
		$params = array(intval($checked), $serial);
		$ret = $this->execStatement($queryStr, $params);
		
		// トランザクション終了
		$ret = $this->endTransaction();
		return $ret;
	}
}
?>
