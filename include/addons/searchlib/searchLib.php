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
require_once(dirname(__FILE__) . '/searchLibDb.php');

class searchLib
{
	private $db;				// DB接続オブジェクト
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// DBオブジェクト作成
		$this->db = new searchLibDb();
	}
	/**
	 * コンテンツを検索
	 *
	 * @param string    $contentType		コンテンツタイプ
	 * @param string	$langId				言語
	 * @param timestamp	$startDt			期間(開始日)
	 * @param timestamp	$endDt				期間(終了日)
	 * @param array		$category			カテゴリーID
	 * @param array     $keywords			検索キーワード
	 * @param int       $order				検索ソート順(0=降順、1=昇順)
	 * @param int		$limit				取得する項目数
	 * @param int		$page				取得するページ(1～)
	 * @param function	$callback			コールバック関数
	 * @return 								なし
	 */
	private function getContent($contentType, $langId, $startDt, $endDt, $category, $keywords, $order, $limit, $page, $callback)
	{
		switch ($contentType){
			case M3_VIEW_TYPE_CONTENT:				// 汎用コンテンツ
				break;
			case M3_VIEW_TYPE_PRODUCT:				// 商品情報(Eコマース)
				break;
			case M3_VIEW_TYPE_BBS:					// BBS
				break;
			case M3_VIEW_TYPE_BLOG:				// ブログ
				break;
			case M3_VIEW_TYPE_WIKI:				// wiki
				break;
			case M3_VIEW_TYPE_USER:				// ユーザ作成コンテンツ
				break;
			case M3_VIEW_TYPE_EVENT:				// イベント情報
				$this->db->getEvent($langId, $startDt, $endDt, $category, $keywords, $order, $limit, $page, $callback);
				break;
			case M3_VIEW_TYPE_PHOTO:				// フォトギャラリー
				break;
		}
	}
	/**
	 * 検索したコンテンツ数を取得
	 *
	 * @param string    $contentType		コンテンツタイプ
	 * @param string	$langId				言語
	 * @param timestamp	$startDt			期間(開始日)
	 * @param timestamp	$endDt				期間(終了日)
	 * @param array		$category			カテゴリーID
	 * @param array     $keywords			検索キーワード
	 * @return int							項目数
	 */
	private function getContentCount($contentType, $langId, $startDt, $endDt, $category, $keywords)
	{
		$rowCount = 0;
		
		switch ($contentType){
			case M3_VIEW_TYPE_CONTENT:				// 汎用コンテンツ
				break;
			case M3_VIEW_TYPE_PRODUCT:				// 商品情報(Eコマース)
				break;
			case M3_VIEW_TYPE_BBS:					// BBS
				break;
			case M3_VIEW_TYPE_BLOG:				// ブログ
				break;
			case M3_VIEW_TYPE_WIKI:				// wiki
				break;
			case M3_VIEW_TYPE_USER:				// ユーザ作成コンテンツ
				break;
			case M3_VIEW_TYPE_EVENT:				// イベント情報
				$rowCount = $this->db->getEventCount($langId, $startDt, $endDt, $category, $keywords);
				break;
			case M3_VIEW_TYPE_PHOTO:				// フォトギャラリー
				break;
		}
		return $rowCount;
	}
}
?>
