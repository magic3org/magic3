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
	// 受付イベントコード自動生成用
	const EVENT_CODE_HEAD = 'eve';			// 自動生成する受付イベントコードのヘッダ部
	const EVENT_CODE_LENGTH = 5;			// 自動生成する受付イベントコードの数値桁数
	
	// ##### DB定義値 #####
	const CF_LAYOUT_ENTRY_SINGLE	= 'layout_entry_single';			// コンテンツレイアウト(記事詳細)
	
	// ##### デフォルト値 #####
	const DEFAULT_LAYOUT_ENTRY_SINGLE = '<div class="entry_info"><div style="float:left;">[#IMAGE#]</div><div class="clearfix"><div>[#CT_SUMMARY#]</div></div><div><span class="event_date">日時：[#DATE#]</span> <span class="event_location">場所：[#CT_PLACE#]</span></div><div><span class="event_contact">連絡先：[#CT_CONTACT#]</span></div></div><div class="evententry_content">[#BODY#]</div><div class="evententry_info"><div>定員: [#CT_QUOTA#]</div><div>参加: [#CT_ENTRY_COUNT#]</div></div><div><strong>会員名: [#CT_MEMBER_NAME#]</strong></div>[#BUTTON|type=ok;title=予約する|予約済み#]';	// デフォルトのコンテンツレイアウト(記事詳細)

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
	/**
	 * イベント予約コードを生成
	 *
	 * @param string $id	イベントID
	 * @return string		生成した受付イベントコード
	 */
	static function generateEventCode($id)
	{
		$code = self::EVENT_CODE_HEAD . sprintf("%0" . self::EVENT_CODE_LENGTH . "d", $id);
		return $code;
	}
	/**
	 * イベント予約受付コードを生成
	 *
	 * @param string $id	イベントID
	 * @param string $type	予約タイプ
	 * @return string		生成した受付イベントコード
	 */
	static function generateEntryCode($id, $type)
	{
		$code = self::generateEventCode($eventId) . "-%04d";
		return $code;
	}
}
?>
