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
 * @copyright  Copyright 2006-2012 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: s_blogCommonDef.php 4794 2012-03-26 15:52:34Z fishbone $
 * @link       http://www.magic3.org
 */
 
class s_blogCommonDef
{
	// デフォルト値
	const DEFAULT_TITLE_LIST_IMAGE	= 'noimage.png';		// デフォルトのタイトルリスト画像
	const DEFAULT_THUMB_IMAGE_EXT 	= 'png';				// サムネールファイルのデフォルト拡張子
	const TITLE_LIST_IMAGE_SIZE		= 80;					// タイトルリスト画像のサイズ

	// DB定義値
	// 共通
	const CF_RECEIVE_COMMENT		= 'receive_comment';		// コメントを受け付けるかどうか
	const CF_RECEIVE_TRACKBACK		= 'receive_trackback';		// トラックバックを受け付けるかどうか
	const CF_USE_MULTI_BLOG			= 'use_multi_blog';		// マルチブログ機能を使用するかどうか
	const CF_MAX_COMMENT_LENGTH		= 'comment_max_length';		// コメント最大文字数
	// スマートフォン独自
	const CF_ENTRY_VIEW_COUNT			= 's:entry_view_count';			// 記事表示数
	const CF_ENTRY_VIEW_ORDER			= 's:entry_view_order';			// 記事表示方向
	const CF_TOP_CONTENT				= 's:top_content';				// トップコンテンツ
	const CF_AUTO_RESIZE_IMAGE_MAX_SIZE = 's:auto_resize_image_max_size';		// 画像の自動変換最大サイズ
	const CF_JQUERY_VIEW_STYLE			= 's:jquery_view_style';		// jQueryMobile表示スタイル
	const CF_USE_TITLE_LIST_IMAGE		= 's:use_title_list_image';			// タイトルリスト画像を使用するかどうか
	const CF_TITLE_LIST_IMAGE			= 's:title_list_image';			// タイトルリスト画像
}
?>
