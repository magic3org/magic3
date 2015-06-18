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
require_once($gEnvManager->getCurrentWidgetDbPath() .	'/wiki_mainDb.php');

class WikiConfig
{
	private static $db;		// DBオブジェクト
	private static $sessionObj;		// セッションオブジェクト
	private static $_configArray;	// Wiki設定(DB定義値)
	private static $defaultPage;	// デフォルトページ名
	private static $authType;	// ユーザの認証方法
	private static $isShowToolbarForAllUser;	// 全ユーザ向けにツールバーを表示するかどうか
	private static $isShowPageTitle;				// ページタイトルを表示するかどうか
	private static $isShowPageRelated;				// 関連ページを表示するかどうか
	private static $isShowPageAttachFiles;				// 添付ファイルを表示するかどうか
	private static $isShowPageLastModified;				// 最終更新を表示するかどうか
/*	const SHOW_TOOLBAR_FOR_ALL_USER = 'show_toolbar_for_all_user';
	const AUTH_TYPE_ADMIN		= 'admin';		// 認証タイプ(管理権限ユーザ)
	const AUTH_TYPE_LOGIN_USER	= 'loginuser';		// 認証タイプ(ログインユーザ)
	const AUTH_TYPE_PASSWORD	= 'password';		// 認証タイプ(共通パスワード)
	const DEFAULT_PAGE = 'FrontPage';		// デフォルトのページ
	const CONFIG_KEY_AUTH_TYPE = 'auth_type';			// 認証タイプ(admin=管理権限ユーザ、loginuser=ログインユーザ、password=共通パスワード)
	const CONFIG_KEY_SHOW_PAGE_TITLE		= 'show_page_title';		// タイトル表示
	const CONFIG_KEY_SHOW_PAGE_RELATED		= 'show_page_related';// 関連ページ
	const CONFIG_KEY_SHOW_PAGE_ATTACH_FILES	= 'show_page_attach_files';// 添付ファイル
	const CONFIG_KEY_SHOW_PAGE_LAST_MODIFIED	= 'show_page_last_modified';// 最終更新
	const CONFIG_KEY_PASSWORD = 'password';		// 共通パスワード
	const CONFIG_KEY_DEFAULT_PAGE = 'default_page';		// デフォルトページ
	*/
	
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
//		self::$db = $db;
		
		// 設定値を取得
		self::$_configArray = wiki_mainCommonDef::loadConfig($db);
		
		self::$isShowPageTitle = self::$_configArray[wiki_mainCommonDef::CF_SHOW_PAGE_TITLE];			// ページタイトルを表示するかどうか
		if (!isset(self::$isShowPageTitle)) self::$isShowPageTitle = '1';
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
		
		// ユーザ認証方法
		self::$authType = self::$_configArray[wiki_mainCommonDef::CF_AUTH_TYPE];
		if (empty(self::$authType)) self::$authType = wiki_mainCommonDef::AUTH_TYPE_ADMIN;		// デフォルトの認証タイプは管理権限
		
/*		$value = self::$db->getConfig(wiki_mainCommonDef::CF_SHOW_TOOLBAR_FOR_ALL_USER);		// 全ユーザ向けにツールバーを表示するかどうか
		if ($value == ''){
			self::$isShowToolbarForAllUser = true;		// デフォルトは表示
		} else {
			if (!empty($value)) self::$isShowToolbarForAllUser = true;
		}
		$value = self::$db->getConfig(wiki_mainCommonDef::CF_SHOW_PAGE_TITLE);		// ページタイトルを表示するかどうか
		if ($value == ''){
			self::$isShowPageTitle = true;				// デフォルトは表示
		} else {
			if (!empty($value)) self::$isShowPageTitle = true;
		}
		$value = self::$db->getConfig(wiki_mainCommonDef::CF_SHOW_PAGE_RELATED);		// 関連ページを表示するかどうか
		if ($value == ''){
			self::$isShowPageRelated = true;				// デフォルトは表示
		} else {
			if (!empty($value)) self::$isShowPageRelated = true;
		}
		$value = self::$db->getConfig(wiki_mainCommonDef::CF_SHOW_PAGE_ATTACH_FILES);		// 添付ファイルを表示するかどうか
		if ($value == ''){
			self::$isShowPageAttachFiles = true;				// デフォルトは表示
		} else {
			if (!empty($value)) self::$isShowPageAttachFiles = true;
		}
		$value = self::$db->getConfig(wiki_mainCommonDef::CF_SHOW_PAGE_LAST_MODIFIED);		// 最終更新を表示するかどうか
		if ($value == ''){
			self::$isShowPageLastModified = true;				// デフォルトは表示
		} else {
			if (!empty($value)) self::$isShowPageLastModified = true;
		}
		$value = self::$db->getConfig(wiki_mainCommonDef::CF_DEFAULT_PAGE);// デフォルトページ
		if (empty($value)){
			self::$defaultPage = wiki_mainCommonDef::DEFAULT_DEFAULT_PAGE;
		} else {
			self::$defaultPage = $value;
		}
		$defaultpage = self::$defaultPage;	// グローバル値にも設定
		
		// ユーザ認証方法
		$value = self::$db->getConfig(wiki_mainCommonDef::CF_AUTH_TYPE);// ユーザの認証方法
		if (empty($value)){
			self::$authType = wiki_mainCommonDef::AUTH_TYPE_ADMIN;		// デフォルトの認証タイプは管理権限
			//self::$authType = self::AUTH_TYPE_PASSWORD;		// 認証タイプ(共通パスワード)
		} else {
			self::$authType = $value;
		}*/
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
		global $whatsnew;
		return $whatsnew;
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
		return $gEnvManager->getCurrentWidgetLibPath() . '/';
	}
	/**
	 * DBディレクトリを取得
	 *
	 * @return string		DBディレクトリ
	 */
	public static function getDbDir()
	{
		global $gEnvManager;
		return $gEnvManager->getCurrentWidgetDbPath() . '/';
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
