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
 * @version    SVN: $Id: s_photoslideDb.php 4700 2012-02-19 14:28:34Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getDbPath() . '/baseDb.php');

class s_photoslideDb extends BaseDb
{
	/**
	 * フォトギャラリー画像を取得(表示用)
	 *
	 * @param int 		$itemCount			取得項目数
	 * @param string	$lang				言語
	 * @param string	$sortKey			ソートキー(index=表示順,date=日付,rate=評価,ref=参照数)
	 * @param int		$sortDirection		取得順(0=降順,1=昇順)
	 * @param function	$callback			コールバック関数
	 * @return 			なし
	 */
	function getPhotoItems($itemCount, $lang, $sortKey, $sortDirection, $callback)
	{
		$queryStr  = 'SELECT DISTINCT ht_public_id,ht_name,ht_rate_average,ht_view_count,ht_regist_dt FROM photo ';
		$queryStr .=   'WHERE ht_deleted = false ';		// 未削除
		$queryStr .=     'AND ht_visible = true ';		// 公開中
		
		// ソート順
		switch ($sortKey){
			case 'index':	// 表示順
			default:
				$orderKey = 'ht_sort_order ';
				if (empty($sortDirection)) $orderKey .= 'DESC ';
				$orderKey .= ',ht_regist_dt ';
				break;
			case 'date':		// 日付
				$orderKey = 'ht_regist_dt ';
				break;
			case 'rate':		// 評価
				$orderKey = 'ht_rate_average ';
				break;
			case 'ref':			// 参照数
				$orderKey = 'ht_view_count ';
				break;
		}
		$ord = '';
		if (empty($sortDirection)) $ord = 'DESC ';
		$queryStr .=  'ORDER BY ' . $orderKey . $ord . 'LIMIT ' . $itemCount;	// 画像アップロード日時順
		$this->selectLoop($queryStr, array(), $callback);
	}
	/**
	 * フォトギャラリー定義値を取得
	 *
	 * @param string $key		キーとなる項目値
	 * @return string $value	値
	 */
	function getConfig($key)
	{
		$retValue = '';
		$queryStr = 'SELECT hg_value FROM photo_config ';
		$queryStr .=  'WHERE hg_id  = ?';
		$ret = $this->selectRecord($queryStr, array($key), $row);
		if ($ret) $retValue = $row['hg_value'];
		return $retValue;
	}
}
?>
