<?php
/**
 * index.php用コンテナクラス
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2013 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: admin_menu3WidgetContainer.php 6126 2013-06-25 01:17:02Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseAdminWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/admin_menuDb.php');

class admin_menu3WidgetContainer extends BaseAdminWidgetContainer
{
	protected $db;	// DB接続オブジェクト
	protected $cssFilePath;			// CSSファイル
	protected $themeFilePath;		// テーマファイル
	protected $contentMenu;			// コンテンツ編集メニュー
	protected $subContentMenu;			// サブコンテンツ編集メニュー
	//const DEFAULT_SITE_NAME = 'サイト名未設定';
	const DEFAULT_CSS_FILE = '/default.css';		// CSSファイル
	const DEFAULT_NAV_ID = 'admin_menu';			// ナビゲーションメニューID
	const DEFAULT_THEME_DIR = '/ui/themes/';				// jQueryUIテーマ格納ディレクトリ
	const THEME_CSS_FILE = 'jquery-ui.custom.css';		// テーマファイル
//	const CF_ADMIN_DEFAULT_THEME = 'admin_default_theme';		// 管理画面用jQueryUIテーマ
	const HELP_ICON_FILE = '/images/system/help24.gif';		// ヘルプアイコン
	const CLOSE_ICON_FILE = '/images/system/close32.png';		// ウィンドウ閉じるアイコン
	const PC_ICON_FILE = '/images/system/device/pc.png';		// PCアイコン
	const SMARTPHONE_ICON_FILE = '/images/system/device/smartphone.png';		// スマートフォンアイコン
	const MOBILE_ICON_FILE = '/images/system/device/mobile.png';		// 携帯アイコン
	const PC_CLOSED_ICON_FILE = '/images/system/device/pc_closed.png';		// PCアイコン(非公開)
	const SMARTPHONE_CLOSED_ICON_FILE = '/images/system/device/smartphone_closed.png';		// スマートフォンアイコン(非公開)
	const MOBILE_CLOSED_ICON_FILE = '/images/system/device/mobile_closed.png';		// 携帯アイコン(非公開)
	const MAX_SITENAME_LENGTH = 20;		// サイト名の最大文字数
	const ICON_SIZE = 24;			// アイコンサイズ
	const PREVIEW_ICON_SIZE = 24;			// プレビューアイコンサイズ
	const HELP_TITLE = 'ヘルプ';
	const MENU_TITLE_CONTENT = 'コンテンツ管理';		// コンテンツ編集メニューのタイトル
	const MENU_TITLE_SUB_CONTENT = 'サブコンテンツ管理';		// サブコンテンツ編集メニューのタイトル
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->db = new admin_menuDB();
	}
	/**
	 * テンプレートファイルを設定
	 *
	 * _assign()でデータを埋め込むテンプレートファイルのファイル名を返す。
	 * 読み込むディレクトリは、「自ウィジェットディレクトリ/include/template」に固定。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。そのまま_assign()に渡る
	 * @return string 						テンプレートファイル名。テンプレートライブラリを使用しない場合は空文字列「''」を返す。
	 */
	function _setTemplate($request, &$param)
	{	
		return 'index.tmpl.html';
	}
	/**
	 * テンプレートにデータ埋め込む
	 *
	 * _setTemplate()で指定したテンプレートファイルにデータを埋め込む。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。_setTemplate()と共有。
	 * @return								なし
	 */
	function _assign($request, &$param)
	{
		// システム制御画面のときはメニューを作成しないで終了
		if ($this->gPage->getSystemHandleMode() > 0){
			return;
		}
		
		if (!$this->gEnv->isSystemAdmin()) return;	// システム管理者以外の場合は終了
		
		$menu = $request->trimValueOf('menu');
		$openBy = $request->trimValueOf(M3_REQUEST_PARAM_OPEN_BY);		// ウィンドウオープンタイプ
		
		// ページIDを取得
		$pageSubId = $request->trimValueOf(M3_REQUEST_PARAM_PAGE_SUB_ID);
		
		// メニューの表示制御
		$menuStatus = $request->trimValueOf('showmenu');
		if (!empty($menuStatus)){
			if ($menuStatus == 'false'){
				$paramObj->showMenu = 0;	// メニューを表示するかどうか
			} else if ($menuStatus == 'true'){
				$paramObj->showMenu = 1;	// メニューを表示するかどうか
			}
			$ret = $this->updateWidgetParamObj($paramObj);
		}
		
		$this->cssFilePath = $this->getUrl($this->gEnv->getCurrentWidgetCssUrl() . self::DEFAULT_CSS_FILE);		// CSSファイル

		// メニューを表示
		if ($menu == 'off'){	// メニュー非表示指定のとき
		} else if (!empty($openBy)){	// 別ウィンドウで表示のときは閉じるボタン表示
			if ($openBy != 'tabs' && $openBy != 'iframe'){		// タブ、インナーフレーム表示以外
				$this->tmpl->setAttribute('closebutton', 'visibility', 'visible');
			
				// ウィンドウ閉じるアイコンを設定
				$iconUrl = $this->gEnv->getRootUrl() . self::CLOSE_ICON_FILE;
				$this->tmpl->addVar("closebutton", "close_image", $this->getUrl($iconUrl));
				
				// サーバ指定されている場合はサーバ名を設定
				$server = $request->trimValueOf(M3_REQUEST_PARAM_SERVER);
				if (!empty($server)){
					// 設定データを取得
					$ret = $this->_db->getServerById($server, $row);
					if ($ret){
						//$serverName = 'サーバ名：' . $row['ts_name'];// サーバ名
						$serverName = $this->_('Server Name:') . ' ' . $row['ts_name'];// サーバ名
						$this->tmpl->addVar("closebutton", "server_name", $this->convertToDispString($serverName));
					}
				}
			}
		} else {	// メニュー表示のとき
			$this->tmpl->setAttribute('menu', 'visibility', 'visible');
			
			// ##### メニューを作成 #####
			// トップレベル項目を取得
			$navId = self::DEFAULT_NAV_ID . '.' . $this->gEnv->getCurrentLanguage();
			if (!$this->db->getNavItems($navId, 0, $rows)){			// 現在の言語で取得できないときはデフォルト言語で取得
				$navId = self::DEFAULT_NAV_ID . '.' . $this->gEnv->getDefaultLanguage();
				if (!$this->db->getNavItems($navId, 0, $rows)){		// デフォルト言語で取得できないときは拡張子なしで取得
					$navId = self::DEFAULT_NAV_ID;
					$this->db->getNavItems($navId, 0, $rows);
				}
			}
			
			$menuInner = '';
			$menuInner .= '<tr valign="top"><td>'. M3_NL;
			$topMenuCount = count($rows);
			for ($i = 0; $i < $topMenuCount; $i++){
				if ($rows[$i]['ni_view_control'] == 0){		// 改行以外のとき
					$topId = $rows[$i]['ni_id'];
			
					// サブレベル取得
					$this->db->getNavItems($navId, $topId, $subRows);
			
					// メニュー外枠
					//$menuInner .= '<div class="ui-widget m3toppage_menu">'. M3_NL;
					$menuInner .= '<div class="m3toppage_menu">'. M3_NL;
			
					// ヘルプの作成
					$helpText = '';
					/*$title = $rows[$i]['ni_help_title'];
					if (!empty($title)){
						$helpText = $this->gInstance->getHelpManager()->createHelpText($title, $rows[$i]['ni_help_body']);
					}*/
				
					// メニューカテゴリのタイトル
					$menuInner .= str_repeat(' ', 4);
					$menuInner .= '<div class="ui-state-default ui-priority-primary ui-corner-tl ui-corner-tr"><span ' . $helpText . '>' . 
								$this->convertToDispString($rows[$i]['ni_name']) . '</span></div>'. M3_NL;
								
					// 「ul」タグ
					$menuInner .= str_repeat(' ', 4);
					$menuInner .= '<ul class="ui-widget-content ui-corner-bl ui-corner-br">' . M3_NL;
				
					// 「li」タグ
					if (count($subRows) > 0){
						for ($l = 0; $l < count($subRows); $l++){
							// ヘルプの作成
							$helpText = '';
							/*$title = $subRows[$l]['ni_help_title'];
							if (!empty($title)){
								$helpText = $this->gInstance->getHelpManager()->createHelpText($title, $subRows[$l]['ni_help_body']);
							}*/
						
							$menuInner .= str_repeat(' ', 8);
							$menuInner .= '<li ';
							$menuInner .= '><a href="';
							$menuInner .= $this->getUrl($this->gEnv->getDefaultAdminUrl() . '?task=' . $subRows[$l]['ni_task_id']);	// 起動タスクパラメータを設定
							if (!empty($subRows[$l]['ni_param'])){		// パラメータが存在するときはパラメータを追加
								$menuInner .= '&' . M3_REQUEST_PARAM_OPERATION_TODO . '=' . urlencode($subRows[$l]['ni_param']);
							}
							$menuInner .= '" ><span ' . $helpText . '>' . $this->convertToDispString($subRows[$l]['ni_name']) . '</span></a></li>' . M3_NL;
						}
					}
					$menuInner .= str_repeat(' ', 4);
					$menuInner .= '</ul>' . M3_NL;
					$menuInner .= '</div>' . M3_NL;		// メニュー外枠
				} else {		// 改行のとき
					$menuInner .= '</td><td>' . M3_NL;
				}
			}
			// ヘルプへのリンク
			$iconTitle = self::HELP_TITLE;
			$iconUrl = $this->gEnv->getRootUrl() . self::HELP_ICON_FILE;
			$iconTag = '<a href="#" onclick="goHelp();return false;">';
			$iconTag .= '<img src="' . $this->getUrl($iconUrl) . '" width="' . self::ICON_SIZE . '" height="' . self::ICON_SIZE . '" border="0" alt="' . $iconTitle . '" title="' . $iconTitle . '" />';
			$iconTag .= '</a>';
			$menuInner .= '<div style="text-align:right;">' . $iconTag . '</div>';
			
			$menuInner .= '</td></tr>';
			$this->tmpl->addVar("menu", "menu_inner", $menuInner);
			$this->tmpl->addVar("menu", "widget_url", $this->getUrl($this->gEnv->getCurrentWidgetRootUrl()));	// ウィジェットのルートディレクトリ
			
			$this->tmpl->addVar("menu", "top_url", $this->getUrl($this->gEnv->getDefaultAdminUrl()));		// トップメニュー画面URL
			//$themeFile = $this->gEnv->getRootUrl() . self::DEFAULT_THEME_DIR . $this->_db->getSystemConfig(self::CF_ADMIN_DEFAULT_THEME) . '/'. self::THEME_CSS_FILE;	// 管理画面用jQueryUIテーマ
			$themeFile = $this->gEnv->getRootUrl() . self::DEFAULT_THEME_DIR . $this->gSystem->adminDefaultTheme() . '/'. self::THEME_CSS_FILE;	// 管理画面用jQueryUIテーマ
			$this->themeFilePath = $this->getUrl($themeFile);			// jQuery UIテーマ
			
			// サイト表示
			$siteName = $this->gEnv->getSiteName();
//			if (empty($siteName)) $siteName = $this->_('Untitled Site');
			$siteName = makeTruncStr($siteName, self::MAX_SITENAME_LENGTH);
			$siteUrl = $this->gEnv->getRootUrl();
			$this->tmpl->addVar("menu", "site_name", $siteName);
			$this->tmpl->addVar("menu", "pc_url", $siteUrl);
			//$this->tmpl->addVar("menu", "site", '<label><a href="#" onclick="previewSite(\'' . $siteUrl . '\');">' . $siteUrl . '</a></label>');
			
			// システムバージョン
			$this->tmpl->addVar("menu", "system", 'Magic3 v' . M3_SYSTEM_VERSION);
			$this->tmpl->addVar("menu", "official_url", 'http://www.magic3.org');
			
			// 運用中のコンテンツを取得
			$this->contentMenu = $this->getContentMenu();			// コンテンツ編集メニュー項目取得
			$this->subContentMenu = $this->getSubContentMenu();			// サブコンテンツ編集メニュー
			
			// サイトプレビュー
			$previewTag = $this->createSitePreviewTag();
			$this->tmpl->addVar("menu", "site_preview", $previewTag);
		}
		// テキストをローカライズ
		$localeText = array();
		$localeText['msg_logout'] = $this->_('Logout from system?');// ログアウトしますか?
		$localeText['label_top'] = $this->_('Top');// トップ
		$localeText['label_menu'] = $this->_('Menu');// メニュー
		$localeText['label_logout'] = $this->_('Logout');// ログアウト
		$localeText['label_close'] = $this->_('Close');// 閉じる
		$this->setLocaleText($localeText);
	}
	/**
	 * CSSファイルをHTMLヘッダ部に設定
	 *
	 * CSSファイルをHTMLのheadタグ内に追加出力する。
	 * _assign()よりも後に実行される。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。
	 * @return string 						CSS文字列。出力しない場合は空文字列を設定。
	 */
	function _addCssFileToHead($request, &$param)
	{
		if (empty($this->cssFilePath)){
			return array();
		} else {
			return array($this->themeFilePath, $this->cssFilePath);		// jQueryUIテーマを先に読み込み
		}
	}
	/**
	 * サイトプレビュータグを作成
	 *
	 * @return string			プレビュータグ
	 */
	function createSitePreviewTag()
	{
		$previewTag = '';
		$isOpen					= $this->gSystem->siteInPublic();
		
		// アクセスポイントごとの公開状況
		$sitePcInPublic			= $this->gSystem->sitePcInPublic();			// PC用サイトの公開状況
		$siteSmartphoneInPublic = $this->gSystem->siteSmartphoneInPublic();	// スマートフォン用サイトの公開状況
		$siteMobileInPublic		= $this->gSystem->siteMobileInPublic();		// 携帯用サイトの公開状況
		
		// PC用サイトアイコン作成
		$isActiveSite = $this->gSystem->getSiteActiveStatus(0);		// PC用サイト
		if ($isActiveSite){
			$iconTitle = 'PC画面プレビュー';
			if ($isOpen && $sitePcInPublic){
				$iconUrl = $this->gEnv->getRootUrl() . self::PC_ICON_FILE;
			} else {
				$iconUrl = $this->gEnv->getRootUrl() . self::PC_CLOSED_ICON_FILE;		// サイト非公開
			}
			$iconTag = '<span class="static"><a href="#" onclick="m3ShowPreviewWindow(0, \'' . $this->gEnv->getDefaultUrl() . '\');return false;">';
			$iconTag .= '<img src="' . $this->getUrl($iconUrl) . '" width="' . self::PREVIEW_ICON_SIZE . '" height="' . self::PREVIEW_ICON_SIZE . '" border="0" alt="' . $iconTitle . '" title="' . $iconTitle . '" /></a>';
			$iconTag .= $this->createContentMenu(0) . '</span>';		// コンテンツ編集メニュー付加
			$previewTag .= $iconTag;
		}

		// スマートフォン用サイトアイコン作成
		$isActiveSite = $this->gSystem->getSiteActiveStatus(2);		// スマートフォン用サイト
		if ($isActiveSite){
			$iconTitle = 'スマートフォン画面プレビュー';
			if ($isOpen && $siteSmartphoneInPublic){
				$iconUrl = $this->gEnv->getRootUrl() . self::SMARTPHONE_ICON_FILE;
			} else {
				$iconUrl = $this->gEnv->getRootUrl() . self::SMARTPHONE_CLOSED_ICON_FILE;// サイト非公開
			}
			$iconTag = '<span class="static"><a href="#" onclick="m3ShowPreviewWindow(2, \'' . $this->gEnv->getDefaultSmartphoneUrl() . '\');return false;">';
			$iconTag .= '<img src="' . $this->getUrl($iconUrl) . '" width="' . self::PREVIEW_ICON_SIZE . '" height="' . self::PREVIEW_ICON_SIZE . '" border="0" alt="' . $iconTitle . '" title="' . $iconTitle . '" /></a>';
			$iconTag .= $this->createContentMenu(2) . '</span>';		// コンテンツ編集メニュー付加
			$previewTag .= $iconTag;
		}

		// 携帯用サイトアイコン作成
		$isActiveSite = $this->gSystem->getSiteActiveStatus(1);		// 携帯用サイト
		if ($isActiveSite){
			$iconTitle = '携帯画面プレビュー';
			if ($isOpen && $siteMobileInPublic){
				$iconUrl = $this->gEnv->getRootUrl() . self::MOBILE_ICON_FILE;
			} else {
				$iconUrl = $this->gEnv->getRootUrl() . self::MOBILE_CLOSED_ICON_FILE;// サイト非公開
			}
			$iconTag = '<span class="static"><a href="#" onclick="m3ShowPreviewWindow(1, \'' . $this->gEnv->getDefaultMobileUrl() . '\');return false;">';
			$iconTag .= '<img src="' . $this->getUrl($iconUrl) . '" width="' . self::PREVIEW_ICON_SIZE . '" height="' . self::PREVIEW_ICON_SIZE . '" border="0" alt="' . $iconTitle . '" title="' . $iconTitle . '" /></a>';
			$iconTag .= $this->createContentMenu(1) . '</span>';		// コンテンツ編集メニュー付加
			$previewTag .= $iconTag;
		}
		return $previewTag;
	}
	/**
	 * コンテンツ編集メニュー項目を取得
	 *
	 * @return string			メニュー項目データ
	 */
	function getContentMenu()
	{
		$menuItems = array(array(), array(), array());
		$pageIdArray = array($this->gEnv->getDefaultPageId(), $this->gEnv->getDefaultMobilePageId(), $this->gEnv->getDefaultSmartphonePageId());
		$contentType = array(	M3_VIEW_TYPE_CONTENT,				// 汎用コンテンツ
								M3_VIEW_TYPE_PRODUCT,				// 製品
								M3_VIEW_TYPE_BBS,					// BBS
								M3_VIEW_TYPE_BLOG,				// ブログ
								M3_VIEW_TYPE_WIKI,				// wiki
								M3_VIEW_TYPE_USER,				// ユーザ作成コンテンツ
								M3_VIEW_TYPE_EVENT,				// イベント
								M3_VIEW_TYPE_PHOTO);				// フォトギャラリー
		$ret = $this->db->getEditWidgetOnPage($pageIdArray, $contentType, $rows);
		if ($ret){
			$rowCount = count($rows);
			for ($i = 0; $i < $rowCount; $i++){
				$row = $rows[$i];
				switch ($row['pd_id']){
					case $pageIdArray[0]:
					default:
						$index = 0;
						break;
					case $pageIdArray[1]:
						$index = 1;
						break;
					case $pageIdArray[2]:
						$index = 2;
						break;
				}
				$menuItems[$index][] = $row;
			}
		}
		return $menuItems;
	}
	/**
	 * サブコンテンツ編集メニュー項目を取得
	 *
	 * @return string			メニュー項目データ
	 */
	function getSubContentMenu()
	{
		$menuItems = array(array(), array(), array());
		$pageIdArray = array($this->gEnv->getDefaultPageId(), $this->gEnv->getDefaultMobilePageId(), $this->gEnv->getDefaultSmartphonePageId());
		$ret = $this->db->getEditSubWidgetOnPage($pageIdArray, $rows);
		if ($ret){
			$rowCount = count($rows);
			for ($i = 0; $i < $rowCount; $i++){
				$row = $rows[$i];
				switch ($row['pd_id']){
					case $pageIdArray[0]:
					default:
						$index = 0;
						break;
					case $pageIdArray[1]:
						$index = 1;
						break;
					case $pageIdArray[2]:
						$index = 2;
						break;
				}
				$menuItems[$index][] = $row;
			}
		}
		return $menuItems;
	}
	/**
	 * コンテンツ編集メニュー作成
	 *
	 * @param int $deviceType			デバイスタイプ
	 * @return string					メニュータグ
	 */
	function createContentMenu($deviceType)
	{
		$menu = $this->contentMenu[$deviceType];		// コンテンツ編集メニュー
		$subMenu = $this->subContentMenu[$deviceType];	// サブコンテンツ編集メニュー
		if (empty($menu) && empty($subMenu)) return '';
		
		$menuTag .= '<div class="ldd_submenu ui-widget-header">';
		$menuTag .= '<table border="0" cellpadding="0" cellspacing="0" align="center"><tr valign="top"><td>';
		
		// コンテンツ編集メニュー
		if (!empty($menu)){
			$menuTag .= '<div class="m3toppage_menu">';
			$menuTag .= '<div class="ui-state-default ui-priority-primary ui-corner-tl ui-corner-tr"><span>' . self::MENU_TITLE_CONTENT . '</span></div>';
			$menuTag .= '<ul class="ui-widget-content ui-corner-bl ui-corner-br">';
		
			for ($i = 0; $i < count($menu); $i++){
				$widgetId = $menu[$i]['wd_id'];
				$title = $this->getCurrentLangString($menu[$i]['wd_content_name']);		// ウィジェットのコンテンツ名を取得
				
				if (empty($title)){
					// コンテンツ単位でタイトルを取得
					$contentType = $menu[$i]['wd_type'];
					switch ($contentType){
						case M3_VIEW_TYPE_CONTENT:				// 汎用コンテンツ
							$title = '汎用コンテンツ';
							break;
						case M3_VIEW_TYPE_PRODUCT:				// 商品情報(Eコマース)
							$title = '商品情報';
							break;
						case M3_VIEW_TYPE_BBS:					// BBS
							$title = 'BBS';
							break;
						case M3_VIEW_TYPE_BLOG:				// ブログ
							$title = 'ブログ';
							break;
						case M3_VIEW_TYPE_WIKI:				// wiki
							$title = 'wiki';
							break;
						case M3_VIEW_TYPE_USER:				// ユーザ作成コンテンツ
							$title = 'ユーザ作成コンテンツ';
							break;
						case M3_VIEW_TYPE_EVENT:				// イベント
							$title = 'イベント';
							break;
						case M3_VIEW_TYPE_PHOTO:				// フォトギャラリー
							$title = 'フォトギャラリー';
							break;
						default:
							$title = '';
							break;
					}
				}
				if (empty($title)) $title = $menu[$i]['wd_name'];		// コンテンツ名が取得できないときはウィジェット名を設定
				if (empty($title)) continue;
			
				$menuTag .= '<li ><a href="#" onclick="m3ShowConfigWindow(\'' . $widgetId . '\', 0, 0);return false;"><span >' . $this->convertToDispString($title) . '</span></a></li>';
			}
			$menuTag .= '</ul>';
			$menuTag .= '</div>';
		}
		
		// サブコンテンツ編集メニュー
		if (!empty($subMenu)){
			$menuTag .= '<div class="m3toppage_menu">';
			$menuTag .= '<div class="ui-state-default ui-priority-primary ui-corner-tl ui-corner-tr"><span>' . self::MENU_TITLE_SUB_CONTENT . '</span></div>';
			$menuTag .= '<ul class="ui-widget-content ui-corner-bl ui-corner-br">';
		
			for ($i = 0; $i < count($subMenu); $i++){
				$widgetId = $subMenu[$i]['wd_id'];
				$title = $this->getCurrentLangString($subMenu[$i]['wd_content_name']);		// ウィジェットのコンテンツ名を取得
				
				if (empty($title)){
					// コンテンツ単位でタイトルを取得
					$contentType = $subMenu[$i]['wd_content_type'];
					switch ($contentType){
						case 'banner':				// バナー
							$title = 'バナー';
							break;
						default:
							$title = '';
							break;
					}
				}
				if (empty($title)) $title = $subMenu[$i]['wd_name'];		// サブコンテンツ名が取得できないときはウィジェット名を設定
				if (empty($title)) continue;
			
				$menuTag .= '<li ><a href="#" onclick="m3ShowConfigWindow(\'' . $widgetId . '\', 0, 0);return false;"><span >' . $this->convertToDispString($title) . '</span></a></li>';
			}
			$menuTag .= '</ul>';
			$menuTag .= '</div>';
		}
		
		$menuTag .= '</td></tr></table>';
		$menuTag .= '</div>';
		return $menuTag;
	}
}
?>
