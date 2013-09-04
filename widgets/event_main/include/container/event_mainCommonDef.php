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
 * @copyright  Copyright 2006-2013 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
 
class event_mainCommonDef
{
	const VIEW_CONTENT_TYPE = 'ev';		// 記事参照数取得用
		
	// DB定義値
	const CF_RECEIVE_COMMENT		= 'receive_comment';		// コメントを受け付けるかどうか
	const CF_ENTRY_VIEW_COUNT		= 'entry_view_count';			// 記事表示数
	const CF_ENTRY_VIEW_ORDER		= 'entry_view_order';			// 記事表示方向
	const CF_CATEGORY_COUNT			= 'catagory_count';			// カテゴリー数
	const CF_MAX_COMMENT_LENGTH		= 'comment_max_length';		// コメント最大文字数
	const CF_TOP_CONTENTS			= 'top_contents';		// トップコンテンツ
	const CF_MSG_NO_ENTRY_IN_FUTURE	= 'msg_no_entry_in_future';		// 予定イベントなし時メッセージ
	// デフォルト値
	const DEFAULT_VIEW_COUNT	= 10;				// デフォルトの表示記事数
	const DEFAULT_COMMENT_LENGTH	= 300;				// デフォルトのコメント最大文字数
	const DEFAULT_CATEGORY_COUNT	= 2;				// デフォルトのカテゴリー数
	const DEFAULT_MSG_NO_ENTRY_IN_FUTURE		= '今後のイベントはありません';	// 予定イベントなし時メッセージ
	
	/**
	 * イベント定義値をDBから取得
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
				$key = $rows[$i]['eg_id'];
				$value = $rows[$i]['eg_value'];
				$retVal[$key] = $value;
			}
		}
		return $retVal;
	}
}
?>
