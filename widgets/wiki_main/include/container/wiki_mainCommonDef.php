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
	// ##### 共通定義値 #####
	const AUTH_TYPE_ADMIN		= 'admin';		// 認証タイプ(管理権限ユーザ)
	const AUTH_TYPE_LOGIN_USER	= 'loginuser';		// 認証タイプ(ログインユーザ)
	const AUTH_TYPE_PASSWORD	= 'password';		// 認証タイプ(共通パスワード)
	
	// ##### DB定義値 #####
	const CF_PASSWORD			= 'password';			// 共通パスワード
	const CF_DEFAULT_PAGE		= 'default_page';		// デフォルトページ
	const CF_WHATSNEW_PAGE		= 'whatsnew_page';		// 最終更新ページ
	const CF_WHATSDELETED_PAGE	= 'whatsdeleted_page';	// 最終削除ページ
	const CF_AUTH_TYPE		= 'auth_type';			// 認証タイプ(admin=管理権限ユーザ、loginuser=ログインユーザ、password=共通パスワード)
	const CF_SHOW_PAGE_TITLE			= 'show_page_title';			// タイトル表示
	const CF_SHOW_PAGE_RELATED			= 'show_page_related';			// 関連ページ
	const CF_SHOW_PAGE_ATTACH_FILES		= 'show_page_attach_files';		// 添付ファイル
	const CF_SHOW_PAGE_LAST_MODIFIED	= 'show_page_last_modified';	// 最終更新
	const CF_SHOW_TOOLBAR_FOR_ALL_USER	= 'show_toolbar_for_all_user';				// ツールバーを表示するかどうか
	const CF_USER_LIMITED_FREEZE		= 'user_limited_freeze';				// 凍結・解凍機能のユーザ制限

	// ##### デフォルト値 #####
	const DEFAULT_DEFAULT_PAGE = 'FrontPage';		// デフォルトページ
	const DEFAULT_WHATSNEW_PAGE		= 'RecentChanges';		// 最終更新ページ
	const DEFAULT_WHATSDELETED_PAGE	= 'RecentDeleted';		// 最終削除ページ
	
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
}
?>
