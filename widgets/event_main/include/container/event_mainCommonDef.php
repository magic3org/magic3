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
 
class event_mainCommonDef
{
	// ##### 定義値 #####
	const VIEW_CONTENT_TYPE = 'ev';				// 記事参照数取得用
	const USER_ID_SEPARATOR = ',';				// ユーザID区切り用セパレータ
	const ATTACH_FILE_DIR = '/etc/event';		// 添付ファイル格納ディレクトリ
	const DOWNLOAD_CONTENT_TYPE = '-file';		// ダウンロードするコンテンツのタイプ
	
	// ##### DB定義値 #####
	const CF_ENTRY_VIEW_COUNT		= 'entry_view_count';			// 記事表示数
	const CF_ENTRY_VIEW_ORDER		= 'entry_view_order';			// 記事表示方向
	const CF_ENTRY_DEFAULT_IMAGE	= 'entry_default_image';		// 記事デフォルト画像
	const CF_CATEGORY_COUNT			= 'category_count';				// カテゴリ数
	const CF_OUTPUT_HEAD			= 'output_head';		// ヘッダ出力するかどうか
	const CF_HEAD_VIEW_DETAIL		= 'head_view_detail';		// ヘッダ出力(詳細表示)
	const CF_LAYOUT_ENTRY_SINGLE	= 'layout_entry_single';			// コンテンツレイアウト(記事詳細)
	const CF_LAYOUT_ENTRY_LIST		= 'layout_entry_list';			// コンテンツレイアウト(記事一覧)
	const CF_LAYOUT_COMMENT_LIST	= 'layout_comment_list';			// コンテンツレイアウト(コメント一覧)
	const CF_USE_WIDGET_TITLE		= 'use_widget_title';		// ウィジェットタイトルを使用するかどうか
	const CF_TITLE_DEFAULT			= 'title_default';		// デフォルトタイトル
	const CF_TITLE_LIST				= 'title_list';		// 一覧タイトル
	const CF_TITLE_SEARCH_LIST		= 'title_search_list';		// 検索結果タイトル
	const CF_TITLE_NO_ENTRY			= 'title_no_entry';		// 記事なし時タイトル
	const CF_MESSAGE_NO_ENTRY		= 'message_no_entry';		// ブログ記事が登録されていないメッセージ
	const CF_MESSAGE_FIND_NO_ENTRY	= 'message_find_no_entry';		// ブログ記事が見つからないメッセージ
	const CF_TITLE_TAG_LEVEL		= 'title_tag_level';		// タイトルのタグレベル
	const CF_THUMB_TYPE				= 'thumb_type';				// サムネールタイプ
	// イベント情報専用
	const CF_USE_CALENDAR			= 'use_calendar';		// カレンダーを使用するかどうか
	const CF_MSG_NO_ENTRY_IN_FUTURE	= 'msg_no_entry_in_future';		// 予定イベントなし時メッセージ
	
	// ##### デフォルト値 #####
	const DEFAULT_VIEW_COUNT	= 10;				// デフォルトの表示記事数
	const DEFAULT_COMMENT_LENGTH	= 300;				// デフォルトのコメント最大文字数
	const DEFAULT_CATEGORY_COUNT	= 2;				// デフォルトのカテゴリー数
	const DEFAULT_MSG_NO_ENTRY_IN_FUTURE		= '今後のイベントはありません';	// 予定イベントなし時メッセージ
	const DEFAULT_LAYOUT_ENTRY_SINGLE = '<small>[#CT_AUTHOR#] [#CT_DATE#] [#CT_TIME#] [#BLOG_LINK#]</small>[#BODY#][#CATEGORY#][#LINKS#]';	// デフォルトのコンテンツレイアウト(記事詳細)
	const DEFAULT_LAYOUT_ENTRY_LIST = '[#TITLE#]<small>[#CT_AUTHOR#] [#CT_DATE#] [#CT_TIME#] [#BLOG_LINK#]</small>[#BODY#][#CATEGORY#][#COMMENT_LINK#]';	// デフォルトのコンテンツレイアウト(記事一覧)
	const DEFAULT_LAYOUT_COMMENT_LIST = '[#AVATAR#]<dl><dt>[#TITLE#] <small>[#CM_AUTHOR#] [#CM_DATE#] [#CM_TIME#] [#URL#]</small></dt><dd>[#BODY#]</dd></dl>';	// デフォルトのコンテンツレイアウト(コメント一覧)
	const DEFAULT_HEAD_VIEW_DETAIL = '<meta property="og:type" content="article" /><meta property="og:title" content="[#CT_TITLE#]" /><meta property="og:url" content="[#CT_URL#]" /><meta property="og:image" content="[#CT_IMAGE#]" /><meta property="og:description" content="[#CT_DESCRIPTION#]" /><meta property="og:site_name" content="[#SITE_NAME#]" />';	// デフォルトのヘッダ出力(詳細表示)
//	const DEFAULT_TITLE_DEFAULT 		= 'イベント記事新規';		// イベントタイトルのデフォルト値
	const DEFAULT_TITLE_LIST 			= '「$1」の記事';		// 一覧タイトルのデフォルト値
	const DEFAULT_TITLE_SEARCH_LIST 	= 'イベント情報検索';		// 検索結果タイトルのデフォルト値
	const DEFAULT_TITLE_NO_ENTRY		= 'イベント記事未登録';
	const DEFAULT_MESSAGE_NO_ENTRY		= 'イベント記事は登録されていません';				// イベント記事が登録されていないメッセージ
	const DEFAULT_MESSAGE_FIND_NO_ENTRY	= 'イベント記事が見つかりません';					// イベント記事が見つからないメッセージ
	const DEFAULT_TITLE_TAG_LEVEL		= 2;		// デフォルトのタイトルタグレベル
	
	/**
	 * イベント定義値をDBから取得
	 *
	 * @param object $db	DBオブジェクト
	 * @return array		取得データ
	 */
	static function loadConfig($db)
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
