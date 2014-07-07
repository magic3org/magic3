<?php
/**
 * index.php用共通定義クラス
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
 
class whatsnewCommonDef
{
	const UNKNOWN_CONTENT = 'タイトル不明';
	
	// DBフィールド名
	const FD_DEFAULT_MESSAGE	= 'default_message';		// デフォルトメッセージ
	const FD_DATE_FORMAT		= 'date_format';			// 日時フォーマット
	const FD_LAYOUT_LIST_ITEM	= 'layout_list_item';		// リスト項目レイアウト
	
	/**
	 * 新着情報定義値をDBから取得
	 *
	 * @param object $db	DBオブジェクト
	 * @return array		取得データ
	 */
	static function loadConfig($db)
	{
		$retVal = array();

		// 汎用コンテンツ定義を読み込み
		$ret = $db->getAllConfig($rows);
		if ($ret){
			// 取得データを連想配列にする
			$configCount = count($rows);
			for ($i = 0; $i < $configCount; $i++){
				$key = $rows[$i]['nc_id'];
				$value = $rows[$i]['nc_value'];
				$retVal[$key] = $value;
			}
		}
		return $retVal;
	}
	
	/**
	 * コンテンツタイトル取得
	 *
	 * @param object $db				DBオブジェクト
	 * @param string $langId			言語ID
	 * @param string $contentType		コンテンツタイプ
	 * @param string $contentId			コンテンツID
	 * @param string					コンテンツタイトル
	 */
	static function getContentTitle($db, $langId, $contentType, $contentId)
	{
		$contentName = self::UNKNOWN_CONTENT;
		
		switch ($contentType){
			case M3_VIEW_TYPE_CONTENT:				// 汎用コンテンツ
				$ret = $db->getContentById(''/*PC用コンテンツ*/, $langId, $contentId, $row);
				if ($ret) $contentName = $row['cn_name'];
				break;
			case M3_VIEW_TYPE_PRODUCT:				// 商品情報(Eコマース)
				$ret = $db->getProductById($contentId, $langId, $row);
				if ($ret) $contentName = $row['pt_name'];
				break;
			case M3_VIEW_TYPE_BBS:					// BBS
				// 未使用
				break;
			case M3_VIEW_TYPE_BLOG:				// ブログ
				$ret = $db->getEntryById($contentId, $langId, $row);
				if ($ret) $contentName = $row['be_name'];
				break;
			case M3_VIEW_TYPE_WIKI:				// wiki
				$contentName = $contentId;
				break;
			case M3_VIEW_TYPE_USER:				// ユーザ作成コンテンツ
				$ret = $db->getRoomById($contentId, $langId, $row);
				if ($ret) $contentName = $row['ur_name'];
				break;
			case M3_VIEW_TYPE_EVENT:				// イベント情報
				$ret = $db->getEventById($contentId, $langId, $row);
				if ($ret) $contentName = $row['ee_name'];
				break;
			case M3_VIEW_TYPE_PHOTO:				// フォトギャラリー
				$ret = $db->getPhotoById($contentId, $langId, $row);
				if ($ret) $contentName = $row['ht_name'];
				break;
		}
		return $contentName;
	}
}
?>
