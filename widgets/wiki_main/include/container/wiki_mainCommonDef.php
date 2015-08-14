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

class wiki_mainCommonDef
{
	static $_viewContentType = 'wiki';		// 参照数カウント用コンテンツタイプ
	
	// ##### 共通定義値 #####
	const AUTH_TYPE_ADMIN		= 'admin';		// 認証タイプ(管理権限ユーザ)
	const AUTH_TYPE_LOGIN_USER	= 'loginuser';		// 認証タイプ(ログインユーザ)
	const AUTH_TYPE_PASSWORD	= 'password';		// 認証タイプ(共通パスワード)
	
	// ##### DB定義値 #####
	const CF_PASSWORD			= 'password';			// 共通パスワード
	const CF_DEFAULT_PAGE		= 'default_page';		// デフォルトページ
	const CF_WHATSNEW_PAGE		= 'whatsnew_page';		// 最終更新ページ
	const CF_WHATSDELETED_PAGE	= 'whatsdeleted_page';	// 最終削除ページ
	const CF_AUTH_TYPE			= 'auth_type';			// 認証タイプ(admin=管理権限ユーザ、loginuser=ログインユーザ、password=共通パスワード)
	const CF_SHOW_PAGE_TITLE			= 'show_page_title';			// タイトル表示
	const CF_SHOW_PAGE_URL				= 'show_page_url';			// URL表示
	const CF_SHOW_PAGE_RELATED			= 'show_page_related';			// 関連ページ
	const CF_SHOW_PAGE_ATTACH_FILES		= 'show_page_attach_files';		// 添付ファイル
	const CF_SHOW_PAGE_LAST_MODIFIED	= 'show_page_last_modified';	// 最終更新
	const CF_SHOW_TOOLBAR_FOR_ALL_USER	= 'show_toolbar_for_all_user';				// ツールバーを表示するかどうか
	const CF_USER_LIMITED_FREEZE		= 'user_limited_freeze';				// 凍結・解凍機能のユーザ制限
	const CF_SHOW_AUTO_HEADING_ANCHOR	= 'show_auto_heading_anchor';			// 見出し自動アンカーを表示するかどうか
	const CF_SHOW_USERNAME				= 'show_username';				// ユーザ名を表示するかどうか
	const CF_AUTO_LINK_WIKINAME			= 'auto_link_wikiname';					// Wiki名を自動リンクするかどうか
	const CF_LAYOUT_MAIN				= 'layout_main';						// ページレイアウト(メイン)
	const CF_DATE_FORMAT				= 'date_format';					// 日付フォーマット
	const CF_TIME_FORMAT				= 'time_format';					// 時間フォーマット
	const CF_RECENT_CHANGES_COUNT		= 'recent_changes_count';			// 最終更新ページ最大項目数
	const CF_RECENT_DELETED_COUNT		= 'recent_deleted_count';			// 最終削除ページ最大項目数
	const CF_UPLOAD_FILESIZE			= 'upload_filesize';				// アップロードファイルの最大サイズ

	// ##### デフォルト値 #####
	const DEFAULT_DEFAULT_PAGE 		= 'FrontPage';		// デフォルトページ
	const DEFAULT_WHATSNEW_PAGE		= 'RecentChanges';		// 最終更新ページ
	const DEFAULT_WHATSDELETED_PAGE	= 'RecentDeleted';		// 最終削除ページ
	const DEFAULT_LAYOUT_MAIN		= '<article><header>[#TITLE#][#URL#]</header>[#TOOLBAR#][#BODY#]</article>[#TOOLBAR#][#FILES|pretag=----#][#UPDATES|pretag=----#][#LINKS#]';	// ページレイアウト(メイン)
	const DEFAULT_DATE_FORMAT		= 'Y-m-d';					// 日付フォーマット
	const DEFAULT_TIME_FORMAT		= 'H:i:s';					// 時間フォーマット
	const DEFAULT_RECENT_CHANGES_COUNT		= 60;			// 最終更新ページ最大項目数
	const DEFAULT_RECENT_DELETED_COUNT		= 60;			// 最終削除ページ最大項目数
	const DEFAULT_UPLOAD_FILESIZE	= '1M';					// アップロードファイルの最大サイズデフォルトサイズ
	
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
				$key = $rows[$i]['wg_id'];
				$value = $rows[$i]['wg_value'];
				$retVal[$key] = $value;
			}
		}
		return $retVal;
	}
	/**
	 * DBオブジェクト取得
	 *
	 * @return object		DBオブジェクト
	 */
	static function getDb()
	{
		static $db;
		
		if (!isset($db)) $db = new wiki_mainDb();
		return $db;
	}
}
?>
