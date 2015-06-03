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
 
class evententry_attachmentCommonDef
{
	// ##### 定義値 #####
	const CF_SHOW_ENTRY_COUNT		= 'show_entry_count';			// 参加者数を表示するかどうか
	const CF_SHOW_ENTRY_MEMBER		= 'show_entry_member';			// 参加者を表示するかどうか(会員対象)
	const CF_ENABLE_CANCEL			= 'enable_cancel';				// キャンセル機能を使用可能にするかどうか
	const CF_LAYOUT_ENTRY_SINGLE	= 'layout_entry_single';			// (未使用)コンテンツレイアウト(記事詳細)
	
	// ##### デフォルト値 #####
	const DEFAULT_LAYOUT = '[#BODY#]<div>定員: [#CT_QUOTA#]</div><div>参加: [#CT_ENTRY_COUNT#]</div>[#BUTTON|title=予約画面へ|予約済み#]';	// イベント予約レイアウト
	
	/**
	 * 定義値をDBから取得
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
