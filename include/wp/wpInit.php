<?php
/**
 * WordPress初期処理
 *
 * 機能：Magic3向けにWordPressのパラメータを初期化する
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2017 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
/**
 * WordPressのパラメータを初期化
 *
 * @return				なし
 */
function m3WpInit()
{
	global $gEnvManager;
	global $gContentApi;
	
	// ##### オプションデフォルト値 #####
	$timezone_string = '';
	$gmt_offset = 0;
	/* translators: default GMT offset or timezone string. Must be either a valid offset (-12 to 14)
	   or a valid timezone string (America/New_York). See https://secure.php.net/manual/en/timezones.php
	   for all timezone strings supported by PHP.
	*/
	$offset_or_tz = _x( '0', 'default GMT offset or timezone string' );
	if ( is_numeric( $offset_or_tz ) )
		$gmt_offset = $offset_or_tz;
	elseif ( $offset_or_tz && in_array( $offset_or_tz, timezone_identifiers_list() ) )
			$timezone_string = $offset_or_tz;
			
//	$options = array(
	$GLOBALS['m3WpOptions'] = array(
									'siteurl' => $gEnvManager->getRootUrl(),
									'home' => $gEnvManager->getRootUrl(),
									'blogname' => __('My Site'),
									/* translators: site tagline */
									'blogdescription' => __('Just another WordPress site'),
									'users_can_register' => 0,
									'admin_email' => 'you@example.com',
									/* translators: default start of the week. 0 = Sunday, 1 = Monday */
									'start_of_week' => _x( '1', 'start of week' ),
									'use_balanceTags' => 0,
									'use_smilies' => 1,
									'require_name_email' => 1,
									'comments_notify' => 1,
									'posts_per_rss' => 10,
									'rss_use_excerpt' => 0,
									'mailserver_url' => 'mail.example.com',
									'mailserver_login' => 'login@example.com',
									'mailserver_pass' => 'password',
									'mailserver_port' => 110,
									'default_category' => 1,
									'default_comment_status' => 'closed',		// 「open」から変更
									'default_ping_status' => 'closed',		// 「open」から変更
									'default_pingback_flag' => 1,
									'posts_per_page' => 10,
									/* translators: default date format, see https://secure.php.net/date */
									'date_format' => __('F j, Y'),
									/* translators: default time format, see https://secure.php.net/date */
									'time_format' => __('g:i a'),
									/* translators: links last updated date format, see https://secure.php.net/date */
									'links_updated_date_format' => __('F j, Y g:i a'),
									'comment_moderation' => 0,
									'moderation_notify' => 1,
									'permalink_structure' => '',
									'rewrite_rules' => '',
									'hack_file' => 0,
									'blog_charset' => 'UTF-8',
									'moderation_keys' => '',
									'active_plugins' => array(),
									'category_base' => '',
									'ping_sites' => 'http://rpc.pingomatic.com/',
									'comment_max_links' => 2,
									'gmt_offset' => $gmt_offset,

									// 1.5
									'default_email_category' => 1,
									'recently_edited' => '',
									// Magic3にはテンプレートの親子関係はないのでtemplate,stylesheetは常に同じもの示す
									'template' => $gEnvManager->getCurrentTemplateId(),
									'stylesheet' => $gEnvManager->getCurrentTemplateId(),
									'comment_whitelist' => 1,
									'blacklist_keys' => '',
									'comment_registration' => 0,
									'html_type' => 'text/html',

									// 1.5.1
									'use_trackback' => 0,

									// 2.0
									'default_role' => 'subscriber',
								//	'db_version' => $wp_db_version,

									// 2.0.1
									'uploads_use_yearmonth_folders' => $uploads_use_yearmonth_folders,
									'upload_path' => '',

									// 2.1
									'blog_public' => '1',
									'default_link_category' => 2,
									'show_on_front' => 'posts',

									// 2.2
									'tag_base' => '',

									// 2.5
									'show_avatars' => '1',
									'avatar_rating' => 'G',
									'upload_url_path' => '',
									'thumbnail_size_w' => 150,
									'thumbnail_size_h' => 150,
									'thumbnail_crop' => 1,
									'medium_size_w' => 300,
									'medium_size_h' => 300,

									// 2.6
									'avatar_default' => 'mystery',

									// 2.7
									'large_size_w' => 1024,
									'large_size_h' => 1024,
									'image_default_link_type' => 'none',
									'image_default_size' => '',
									'image_default_align' => '',
									'close_comments_for_old_posts' => 0,
									'close_comments_days_old' => 14,
									'thread_comments' => 1,
									'thread_comments_depth' => 5,
									'page_comments' => 0,
									'comments_per_page' => 50,
									'default_comments_page' => 'newest',
									'comment_order' => 'asc',
									'sticky_posts' => array(),
									'widget_categories' => array(),
									'widget_text' => array(),
									'widget_rss' => array(),
									'uninstall_plugins' => array(),

									// 2.8
									'timezone_string' => $timezone_string,

									// 3.0
									'page_for_posts' => 0,
									'page_on_front' => 0,

									// 3.1
									'default_post_format' => 0,

									// 3.5
									'link_manager_enabled' => 0,

									// 4.3.0
									'finished_splitting_shared_terms' => 1,
									'site_icon' => 0,

									// 4.4.0
									'medium_large_size_w' => 768,
									'medium_large_size_h' => 0,
								);
								
	// ### Magic3追加分 ###
	$GLOBALS['m3WpOptions']['WPLANG'] = $gEnvManager->getDefaultLanguage();// 管理画面の言語
	
	// ##### テンプレート情報からカスタマイズ値を取得 #####
	$optionParams = $gEnvManager->getCurrentTemplateCustomParam();
	if (empty($optionParams)){
		$GLOBALS['m3WpCustomParams'] = array();
	} else {
		$GLOBALS['m3WpCustomParams'] = unserialize($optionParams);		// 連想配列に変換
	}
	
	// WordPress以外の主コンテンツ用のプラグインをロード
	$gContentApi->loadPlugin();
}
?>
