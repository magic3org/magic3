<?php
/**
 * Wiki定義クラス
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
//require_once($gEnvManager->getCurrentWidgetDbPath() .	'/wiki_mainDb.php');

class WikiConfig
{
	private static $db;		// DBオブジェクト
	private static $sessionObj;		// セッションオブジェクト
	private static $_configArray;	// Wiki設定(DB定義値)
	private static $defaultPage;	// デフォルトページ名
	private static $whatsnewPage;		// 最終更新ページ
	private static $whatsdeletedPage;	// 最終削除ページ
	private static $authType;	// ユーザの認証方法
	private static $isShowToolbarForAllUser;	// 全ユーザ向けにツールバーを表示するかどうか
	private static $isShowPageTitle;				// ページタイトルを表示するかどうか
	private static $isShowPageUrl;					// ページURLを表示するかどうか
	private static $isShowPageRelated;				// 関連ページを表示するかどうか
	private static $isShowPageAttachFiles;				// 添付ファイルを表示するかどうか
	private static $isShowPageLastModified;				// 最終更新を表示するかどうか
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
	}
	/**
	 * オブジェクトを初期化
	 *
	 * @param object $db	DBオブジェクト
	 * @return				なし
	 */
	public static function init($db)
	{
		global $defaultpage;
		global $whatsnew;
		global $whatsdeleted;
		static $init = false;		// 初期化完了かどうか
		
		if ($init) return;
		
		// 設定値を取得
		self::$_configArray = wiki_mainCommonDef::loadConfig($db);
		
		self::$isShowPageTitle = self::$_configArray[wiki_mainCommonDef::CF_SHOW_PAGE_TITLE];			// ページタイトルを表示するかどうか
		if (!isset(self::$isShowPageTitle)) self::$isShowPageTitle = '1';
		self::$isShowPageUrl = self::$_configArray[wiki_mainCommonDef::CF_SHOW_PAGE_URL];			// ページURLを表示するかどうか
		if (!isset(self::$isShowPageUrl)) self::$isShowPageUrl = '1';
		self::$isShowPageRelated = self::$_configArray[wiki_mainCommonDef::CF_SHOW_PAGE_RELATED];			// 関連ページを表示するかどうか
		if (!isset(self::$isShowPageRelated)) self::$isShowPageRelated = '1';
		self::$isShowPageAttachFiles = self::$_configArray[wiki_mainCommonDef::CF_SHOW_PAGE_ATTACH_FILES];			// 添付ファイルを表示するかどうか
		if (!isset(self::$isShowPageAttachFiles)) self::$isShowPageAttachFiles = '1';
		self::$isShowPageLastModified = self::$_configArray[wiki_mainCommonDef::CF_SHOW_PAGE_LAST_MODIFIED];			// 最終更新を表示するかどうか
		if (!isset(self::$isShowPageLastModified)) self::$isShowPageLastModified = '1';
		self::$isShowToolbarForAllUser = self::$_configArray[wiki_mainCommonDef::CF_SHOW_TOOLBAR_FOR_ALL_USER];// 全ユーザ向けにツールバーを表示するかどうか
		if (!isset(self::$isShowToolbarForAllUser)) self::$isShowToolbarForAllUser = '0';
		
		// デフォルトページを取得
		self::$defaultPage = self::$_configArray[wiki_mainCommonDef::CF_DEFAULT_PAGE];// デフォルトページ
		if (empty(self::$defaultPage)) self::$defaultPage = wiki_mainCommonDef::DEFAULT_DEFAULT_PAGE;
		$defaultpage = self::$defaultPage;	// グローバル値にも設定
		self::$whatsnewPage = self::$_configArray[wiki_mainCommonDef::CF_WHATSNEW_PAGE];		// 最終更新ページ名
		if (empty(self::$whatsnewPage)) self::$whatsnewPage = wiki_mainCommonDef::DEFAULT_WHATSNEW_PAGE;
		$whatsnew = self::$whatsnewPage;
		self::$whatsdeletedPage = self::$_configArray[wiki_mainCommonDef::CF_WHATSDELETED_PAGE];		// 最終削除ページ名
		if (empty(self::$whatsdeletedPage)) self::$whatsdeletedPage = wiki_mainCommonDef::DEFAULT_WHATSDELETED_PAGE;
		$whatsdeleted = self::$whatsdeletedPage;

		// ユーザ認証方法
		self::$authType = self::$_configArray[wiki_mainCommonDef::CF_AUTH_TYPE];
		if (empty(self::$authType)) self::$authType = wiki_mainCommonDef::AUTH_TYPE_ADMIN;		// デフォルトの認証タイプは管理権限
		
		// 初期化完了
		$init = true;
	}
	/**
	 * ID指定で設定値を取得
	 *
	 * @param string $id	定義ID
	 * @return string		共通パスワード
	 */
	public static function getConfig($id)
	{
		return self::$_configArray[$id];
	}
	/**
	 * デフォルトのページ名を取得
	 *
	 * @return string				デフォルトページ名
	 */
	public static function getDefaultPage()
	{
		return self::$defaultPage;
	}
	/**
	 * 「編集されたページ」のページ名を取得
	 *
	 * @return string		ページ名
	 */
	public static function getWhatsnewPage()
	{
//		global $whatsnew;
//		return $whatsnew;
		return self::$whatsnewPage;
	}
	/**
	 * 「削除されたページ」のページ名を取得
	 *
	 * @return string		ページ名
	 */
	public static function getWhatsdeletedPage()
	{
//		global $whatsdeleted;
//		return $whatsdeleted;
		return self::$whatsdeletedPage;
	}
	/**
	 * インターWikiページ名を取得
	 *
	 * @return string				ページ名
	 */
	public static function getInterWikiPage()
	{
		global $interwiki;
		return $interwiki;
	}
	/**
	 * ヘルプページのページ名を取得
	 *
	 * @return string		ページ名
	 */
	public static function getHelpPage()
	{
		global $help_page;
		return $help_page;
	}
	/**
	 * メニューバーページのページ名を取得
	 *
	 * @return string		ページ名
	 */
	public static function getMenuBarPage()
	{
		global $menubar;
		return $menubar;
	}
	/**
	 * 現在日時を取得
	 *
	 * @return string		現在日時
	 */
	public static function getNow()
	{
		global $now;
		return $now;
	}
	/**
	 * プラグイン格納ディレクトリを取得
	 *
	 * @return string		プラグインディレクトリ
	 */
	public static function getPluginDir()
	{
		return PLUGIN_DIR;
	}
	/**
	 * 添付ファイルアップロードディレクトリを取得
	 *
	 * @return string		ディレクトリ
	 */
	public static function getAttachFileUploadDir()
	{
		return UPLOAD_DIR;
	}
	/**
	 * ライブラリディレクトリを取得
	 *
	 * @return string		ライブラリディレクトリ
	 */
	public static function getLibDir()
	{
		global $gEnvManager;
//		return $gEnvManager->getCurrentWidgetLibPath() . '/';
		return dirname(__FILE__) . '/';
	}
	/**
	 * DBディレクトリを取得
	 *
	 * @return string		DBディレクトリ
	 */
	public static function getDbDir()
	{
		global $gEnvManager;
//		return $gEnvManager->getCurrentWidgetDbPath() . '/';
		return dirname(dirname(__FILE__)) . '/db/';
	}
	/**
	 * ページが編集可能かどうかを取得
	 *
	 * @return bool				true=編集可能、false=読み込みのみ
	 */
	public static function isPageEditable()
	{
		if (PKWK_READONLY){
			return false;
		} else {
			return true;
		}
	}
	/**
	 * ページがバックアップ可能かどうかを取得
	 *
	 * @return bool				true=可能、false=不可
	 */
	public static function isPageBackup()
	{
		global $do_backup;
		if ($do_backup){
			return true;
		} else {
			return false;
		}
	}
	/**
	 * ページが凍結可能かどうかを取得
	 *
	 * @return bool				true=可能、false=不可
	 */
	public static function isPageFreeze()
	{
		global $function_freeze;
		if ($function_freeze){
			return true;
		} else {
			return false;
		}
	}
	/**
	 * すべてのユーザ向けにツールバーを表示するかどうかを取得
	 *
	 * @return bool		true=すべてのユーザに表示、false=管理者のみ表示
	 */
	public static function isShowToolbarForAllUser()
	{
		return self::$isShowToolbarForAllUser;
	}
	/**
	 * タイトルを表示するかどうか
	 *
	 * @return bool		true=表示、false=非表示
	 */
	public static function isShowPageTitle()
	{
		return self::$isShowPageTitle;
	}
	/**
	 * URLを表示するかどうか
	 *
	 * @return bool		true=表示、false=非表示
	 */
	public static function isShowPageUrl()
	{
		return self::$isShowPageUrl;
	}
	/**
	 * 関連ファイル表示するかどうか
	 *
	 * @return bool		true=表示、false=非表示
	 */
	public static function isShowPageRelated()
	{
		return self::$isShowPageRelated;
	}
	/**
	 * 添付ファイルを表示するかどうか
	 *
	 * @return bool		true=表示、false=非表示
	 */
	public static function isShowPageAttachFiles()
	{
		return self::$isShowPageAttachFiles;
	}
	/**
	 * 最終更新を表示するかどうか
	 *
	 * @return bool		true=表示、false=非表示
	 */
	public static function isShowPageLastModified()
	{
		return self::$isShowPageLastModified;
	}
	/**
	 * 共通パスワードを取得
	 *
	 * @return string		共通パスワード
	 */
	public static function getPassword()
	{
		return self::$_configArray[wiki_mainCommonDef::CF_PASSWORD];
	}
	/**
	 * 見出し自動アンカーを表示するかどうかを取得
	 *
	 * @return bool			true=表示、false=非表示
	 */
	public static function getAutoHeadingAnchorVisibility()
	{
		return self::$_configArray[wiki_mainCommonDef::CF_SHOW_AUTO_HEADING_ANCHOR];
	}
	/**
	 * アクセス中のユーザにデータ編集権限があるかを判断
	 *
	 * @return bool		true=権限あり、false=権限なし
	 */
	public static function isUserWithEditAuth()
	{
		global $gEnvManager;

		$ret = false;
		switch (self::$authType){
			case wiki_mainCommonDef::AUTH_TYPE_ADMIN:		// 認証タイプ(システム運用権限ユーザのみ)
				break;
			case wiki_mainCommonDef::AUTH_TYPE_LOGIN_USER:		// 認証タイプ(ログインユーザ)
				if ($gEnvManager->isCurrentUserLogined()) $ret = true;
				break;
			case wiki_mainCommonDef::AUTH_TYPE_PASSWORD:		// 認証タイプ(共通パスワード)
				if (self::$sessionObj->editAuth) $ret = true;		// パスワード認証が通っている場合
				break;
			default:
				break;
		}
		
		// システム運用権限ありの場合はどの認証タイプの場合でも編集可能
		if ($gEnvManager->isSystemManageUser()) $ret = true;
		return $ret;
	}
	/**
	 * アクセス中のユーザにページ凍結・解凍権限があるかを判断
	 *
	 * @return bool		true=権限あり、false=権限なし
	 */
	public static function isUserWithFreezeAuth()
	{
		global $gEnvManager;
		
		// 凍結解凍権限を制限する場合は、システム運用権限ユーザのみが凍結解凍権限あり
		$ret = false;
		if (self::$_configArray[wiki_mainCommonDef::CF_USER_LIMITED_FREEZE]){
			if ($gEnvManager->isSystemManageUser()) $ret = true;
		} else {
			if (self::isUserWithEditAuth()) $ret = true;			
		}
		return $ret;
	}
	/**
	 * アクセス中のユーザにデータ参照権限があるかを判断
	 *
	 * @return bool		true=権限あり、false=権限なし
	 */
	public static function isUserWithReadAuth()
	{
		return true;
	}
	/**
	 * 認証方法がログイン認証であるかどうか
	 *
	 * @return bool		true=ログイン認証、false=ログイン認証以外
	 */
	public static function isPasswordAuth()
	{
		if (self::$authType == wiki_mainCommonDef::AUTH_TYPE_PASSWORD){		// 認証タイプ(共通パスワード)
			return true;
		} else {
			return false;
		}
	}
	/**
	 * パスワード認証を許可する
	 *
	 * @return 				なし
	 */
	public static function permitPasswordAuth()
	{
		if (!empty(self::$sessionObj)){
			self::$sessionObj->editAuth = true;			// 編集権限あり
		}
	}
	/**
	 * セッションオブジェクトを設定
	 *
	 * @param object $obj	セッションオブジェクト
	 * @return 				なし
	 */
	public static function setSessionObj($obj)
	{
		if (empty($obj)){
			$obj = new stdClass;		// 空の場合は作成
			$obj->editAuth = false;
			self::$sessionObj = $obj;
		} else {
			self::$sessionObj = $obj;		// セッションオブジェクト
		}
	}
	/**
	 * セッションオブジェクトを取得
	 *
	 * @return object		セッションオブジェクト
	 */
	public static function getSessionObj()
	{
		return self::$sessionObj;
	}
}
?>
