<?php
/**
 * 新着情報クラス
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2015 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once(dirname(__FILE__) . '/eventLibDb.php');

class eventLib
{
	private $db;				// DB接続オブジェクト
	private $configArray;		// イベント情報定義値
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// DBオブジェクト作成
		$this->db = new eventLibDb();
		
		// 定義値読み込み
		$this->configArray = $this->_loadConfig($this->db);
	}
	/**
	 * コンテンツを検索
	 *
	 * @param string	$langId				言語
	 * @param timestamp	$startDt			期間(開始日)
	 * @param timestamp	$endDt				期間(終了日)
	 * @param array		$category			カテゴリーID
	 * @param array     $keywords			検索キーワード
	 * @param int       $order				検索ソート順(0=降順、1=昇順)
	 * @param int		$limit				取得する項目数
	 * @param int		$page				取得するページ(1～)
	 * @param function	$callback			コールバック関数
	 * @param object    $tmpl				出力テンプレート
	 * @return 								なし
	 */
	function getContent($langId, $startDt, $endDt, $category, $keywords, $order, $limit, $page, $callback, $tmpl = null)
	{
		$this->db->getEvent($langId, $startDt, $endDt, $category, $keywords, $order, $limit, $page, $callback, $tmpl);
	}
	/**
	 * 検索したコンテンツ数を取得
	 *
	 * @param string	$langId				言語
	 * @param timestamp	$startDt			期間(開始日)
	 * @param timestamp	$endDt				期間(終了日)
	 * @param array		$category			カテゴリーID
	 * @param array     $keywords			検索キーワード
	 * @return int							項目数
	 */
	function getContentCount($langId, $startDt, $endDt, $category, $keywords)
	{
		$rowCount = $this->db->getEventCount($langId, $startDt, $endDt, $category, $keywords);
		return $rowCount;
	}
	/**
	 * アイキャッチ用画像のURLを取得
	 *
	 * @param string $filenames				作成済みファイル名(「;」区切り)
	 * @param string $defaultFilenames		作成済みデフォルトファイル名(「;」区切り)
	 * @param string $thumbTypeDef			サムネール画像タイプ定義(タイプ指定の場合)
	 * @param string $thumbType				サムネール画像タイプ(s,m,l)(タイプ指定の場合)
	 * @param int    $deviceType			デバイスタイプ(0=PC、1=携帯、2=スマートフォン)
	 * @return string						画像URL
	 */
	function getEyecatchImageUrl($filenames, $defaultFilenames, $thumbTypeDef = '', $thumbType = '', $deviceType = 0)
	{
		global $gInstanceManager;
		static $thumbTypeArray;
		
		$thumbUrl = '';
		if (empty($filenames)) $filenames = $defaultFilenames;		// 記事デフォルト画像
		if (!empty($filenames)){
			$thumbFilename = $gInstanceManager->getImageManager()->getSystemThumbFilenameByType($filenames, $thumbTypeDef, $thumbType);
			if (!empty($thumbFilename)) $thumbUrl = $gInstanceManager->getImageManager()->getSystemThumbUrl(M3_VIEW_TYPE_EVENT, $deviceType, $thumbFilename);
		}
		return $thumbUrl;
	}
	/**
	 * イベント定義値をDBから取得
	 *
	 * @param object $db	DBオブジェクト
	 * @return array		取得データ
	 */
	private function _loadConfig($db)
	{
		$retVal = array();

		// イベント情報定義を読み込み
		$ret = $db->getAllConfig($rows);
		if ($ret){
			// 取得データを連想配列にする
			$configCount = count($rows);
			for ($i = 0; $i < $configCount; $i++){
				$key = $rows[$i]['eg_id'];
				$value = $rows[$i]['eg_value'];
				$retVal[$key] = $value;
			}
		}
		return $retVal;
	}
}
?>
