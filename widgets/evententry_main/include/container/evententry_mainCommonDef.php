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
 * @copyright  Copyright 2006-2015 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
 
class evententry_mainCommonDef
{
	static $_deviceType = 0;				// デバイスタイプ(PC)
	static $_viewContentType = M3_VIEW_TYPE_EVENTENTRY;		// コンテンツタイプ
	
	// ##### 定義値 #####
	const DATE_RANGE_DELIMITER		= '～';				// 日時範囲用デリミター
		
	// ##### DB定義値 #####
	const CF_LAYOUT_ENTRY_SINGLE	= 'layout_entry_single';			// コンテンツレイアウト(記事詳細)
	
	// ##### デフォルト値 #####
	const DEFAULT_LAYOUT_ENTRY_SINGLE = '<div class="entry_head"><span class="event_date">日時：[#DATE#]</span><span class="event_location">場所：[#CT_PLACE#]</span><div><span class="event_contact">連絡先：[#CT_CONTACT#]</span></div></div><div class="entry_content">[#BODY#]</div><div>[#BODY#]<div class="evententry_info">定員: [#CT_QUOTA#]</div><div>参加: [#CT_ENTRY_COUNT#]</div></div>[#BUTTON|type=ok;title=参加する#]';	// デフォルトのコンテンツレイアウト(記事詳細)

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
				$key = $rows[$i]['ef_id'];
				$value = $rows[$i]['ef_value'];
				$retVal[$key] = $value;
			}
		}
		return $retVal;
	}
}
?>
