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
 * @copyright  Copyright 2006-2015 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseAdminWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/admin_menuDb.php');

class admin_menu4WidgetContainer extends BaseAdminWidgetContainer
{
	protected $db;	// DB接続オブジェクト
	protected $cssFilePath;			// CSSファイル
	protected $contentMenu;			// コンテンツ編集メニュー
	protected $subContentMenu;			// サブコンテンツ編集メニュー
	protected $useMenu;				// メニューを使用するかどうか
	protected $useCloseButton;				// 「閉じる」を使用するかどうか
	const DEFAULT_CSS_FILE = '/default.css';		// CSSファイル
	const WIDGET_CSS_FILE = '/widget.css';			// ウィジェット単体表示用CSS
	const DEFAULT_NAV_ID = 'admin_menu';			// ナビゲーションメニューID
//	const DEFAULT_THEME_DIR = '/ui/themes/';				// jQueryUIテーマ格納ディレクトリ
//	const THEME_CSS_FILE = 'jquery-ui.custom.css';		// テーマファイル
//	const CF_ADMIN_DEFAULT_THEME = 'admin_default_theme';		// 管理画面用jQueryUIテーマ
	const HELP_ICON_FILE = '/images/system/help24.gif';		// ヘルプアイコン
	const TOP_ICON_FILE = '/images/system/home32.png';		// トップ遷移アイコン
	const TOP_SERVER_ADMIN_ICON_FILE = '/images/system/globe32.png';		// トップ遷移アイコン(サーバ管理運用の場合)
	const CLOSE_ICON_FILE = '/images/system/close32.png';		// ウィンドウ閉じるアイコン
	const PREV_ICON_FILE = '/images/system/prev48.png';		// ウィンドウ「前へ」アイコン
	const NEXT_ICON_FILE = '/images/system/next48.png';		// ウィンドウ「次へ」アイコン
	const PC_ICON_FILE = '/images/system/device/pc.png';		// PCアイコン
	const SMARTPHONE_ICON_FILE = '/images/system/device/smartphone.png';		// スマートフォンアイコン
	const MOBILE_ICON_FILE = '/images/system/device/mobile.png';		// 携帯アイコン
	const PC_CLOSED_ICON_FILE = '/images/system/device/pc_closed.png';		// PCアイコン(非公開)
	const SMARTPHONE_CLOSED_ICON_FILE = '/images/system/device/smartphone_closed.png';		// スマートフォンアイコン(非公開)
	const MOBILE_CLOSED_ICON_FILE = '/images/system/device/mobile_closed.png';		// 携帯アイコン(非公開)
	const SITE_OPEN_ICON_FILE = '/images/system/site_open24.png';			// アクセスポイント公開
	const SITE_CLOSE_ICON_FILE = '/images/system/site_close24.png';			// アクセスポイント非公開
	const LOGOUT_ICON_FILE = '/images/system/logout24.png';		// ログアウトアイコン
	const MAX_SITENAME_LENGTH = 20;		// サイト名の最大文字数
	const ICON_SIZE = 24;			// アイコンサイズ
	const SITE_ICON_SIZE = 32;			// サイトメニューアイコンサイズ
	const AVATAR_ICON_SIZE = 32;		// ユーザアバターアイコンサイズ
	const HELP_TITLE = 'ヘルプ';
	const MENU_TITLE_PREVIEW = 'プレビュー';
	const MENU_TITLE_CONTENT = 'コンテンツ管理';		// コンテンツ編集メニューのタイトル
	const MENU_TITLE_SUB_CONTENT = '補助コンテンツ管理';		// サブコンテンツ編集メニューのタイトル
	const UNTITLED_USER_NAME = '名称なしユーザ';		// ユーザ名が設定されていなかった場合の表示名
	const MAINMENU_INDENT_LEBEL = 4;		// メインメニューのインデントレベル
	const SITEMENU_INDENT_LEBEL = 2;		// サイトメニューのインデントレベル
	const MAINMENU_COL_STYLE = 'col-md-';	// Bootstrapのカラムクラス
	const MENUBAR_HEIGHT = 60;			// メインメニューバーの高さ
	const SUB_MENUBAR_HEIGHT = 50;			// サブメニューバーの高さ

	// DB定義値
	const CF_SITE_IN_PUBLIC			= 'site_in_public';			// サイト公開状況
	const CF_SITE_PC_IN_PUBLIC		= 'site_pc_in_public';				// PC用サイトの公開状況
	const CF_SITE_MOBILE_IN_PUBLIC	= 'site_mobile_in_public';		// 携帯用サイトの公開状況
	const CF_SITE_SMARTPHONE_IN_PUBLIC = 'site_smartphone_in_public';		// スマートフォン用サイトの公開状況
	const CF_SYSTEM_TYPE			= 'system_type';		// システム運用タイプ
	const SYSTEM_TYPE_SERVER_ADMIN	= 'serveradmin';		// システム運用タイプ(サーバ管理)
	
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
		$pageSubId = $request->trimValueOf(M3_REQUEST_PARAM_PAGE_SUB_ID);		// ページIDを取得
		$act = $request->trimValueOf('act');
		
		if ($act == 'opensite'){		// サイト公開制御
			$deviceType = $request->trimIntValueOf('device', '0');
			$isOpen = $request->trimIntValueOf('isopen', '0');		// サイトの公開状況

			$siteInPublic			= $this->gSystem->siteInPublic();			// サイト全体の公開状況
			$sitePcInPublic			= $this->gSystem->sitePcInPublic();			// PC用サイトの公開状況
			$siteMobileInPublic		= $this->gSystem->siteMobileInPublic();		// 携帯用サイトの公開状況
			$siteSmartphoneInPublic = $this->gSystem->siteSmartphoneInPublic();	// スマートフォン用サイトの公開状況
			
			switch ($deviceType){
				case 0:			// PC用画面のとき
					if ($isOpen){
						if ($siteInPublic){		// 全サイト公開のとき
							$this->_db->updateSystemConfig(self::CF_SITE_PC_IN_PUBLIC, 1);	// PCサイト公開
						} else {
							$this->_db->updateSystemConfig(self::CF_SITE_IN_PUBLIC, 1);		// サイト運用開始
							
							$this->_db->updateSystemConfig(self::CF_SITE_PC_IN_PUBLIC, 1);	// PCサイト公開
							$this->_db->updateSystemConfig(self::CF_SITE_MOBILE_IN_PUBLIC, 0);	// 携帯サイト公開
							$this->_db->updateSystemConfig(self::CF_SITE_SMARTPHONE_IN_PUBLIC, 0);	// スマートフォンサイト公開
						}
					} else {
						if ($siteInPublic){		// 全サイト公開のとき
							$this->_db->updateSystemConfig(self::CF_SITE_PC_IN_PUBLIC, 0);	// PCサイト非公開
						}
					}
					break;
				case 1:			// 携帯用画面のとき
					if ($isOpen){
						if ($siteInPublic){		// 全サイト公開のとき
							$this->_db->updateSystemConfig(self::CF_SITE_MOBILE_IN_PUBLIC, 1);	// 携帯サイト公開
						} else {
							$this->_db->updateSystemConfig(self::CF_SITE_IN_PUBLIC, 1);		// サイト運用開始
							
							$this->_db->updateSystemConfig(self::CF_SITE_PC_IN_PUBLIC, 0);	// PCサイト公開
							$this->_db->updateSystemConfig(self::CF_SITE_MOBILE_IN_PUBLIC, 1);	// 携帯サイト公開
							$this->_db->updateSystemConfig(self::CF_SITE_SMARTPHONE_IN_PUBLIC, 0);	// スマートフォンサイト公開
						}
					} else {
						if ($siteInPublic){		// 全サイト公開のとき
							$this->_db->updateSystemConfig(self::CF_SITE_MOBILE_IN_PUBLIC, 0);	// 携帯サイト非公開
						}
					}
					break;
				case 2:			// スマートフォン用画面のとき
					if ($isOpen){
						if ($siteInPublic){		// 全サイト公開のとき
							$this->_db->updateSystemConfig(self::CF_SITE_SMARTPHONE_IN_PUBLIC, 1);	// スマートフォンサイト公開
						} else {
							$this->_db->updateSystemConfig(self::CF_SITE_IN_PUBLIC, 1);		// サイト運用開始
							
							$this->_db->updateSystemConfig(self::CF_SITE_PC_IN_PUBLIC, 0);	// PCサイト公開
							$this->_db->updateSystemConfig(self::CF_SITE_MOBILE_IN_PUBLIC, 0);	// 携帯サイト公開
							$this->_db->updateSystemConfig(self::CF_SITE_SMARTPHONE_IN_PUBLIC, 1);	// スマートフォンサイト公開
						}
					} else {
						if ($siteInPublic){		// 全サイト公開のとき
							$this->_db->updateSystemConfig(self::CF_SITE_SMARTPHONE_IN_PUBLIC, 0);	// スマートフォンサイト非公開
						}
					}
					break;
			}

			// 画面を全体を再表示する
			$this->gPage->redirect();
		}
		
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
			if ($openBy != 'tabs' && $openBy != 'iframe' && $openBy != 'dialog'){		// タブ、インナーフレーム、ダイアログ表示以外
				$this->useCloseButton = true;				// 「閉じる」を使用するかどうか
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
			$this->useMenu = true;				// メニューを使用するかどうか
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
			
			// カラム数を求める
			$topMenuCount = count($rows);
			$columnCount = 0;
			for ($i = 0; $i < $topMenuCount; $i++){
				if ($rows[$i]['ni_view_control'] != 0) $columnCount++;		// 改行のとき
			}
			if ($topMenuCount > 0 && $rows[$topMenuCount -1]['ni_view_control'] == 0) $columnCount++;
			$columnWidth = 12 / $columnCount;		// Bootstrapでの幅
			$menuInner = str_repeat(M3_INDENT_SPACE, self::MAINMENU_INDENT_LEBEL) . '<li class="' . self::MAINMENU_COL_STYLE . $columnWidth . '"><ul>' . M3_NL;
						
			for ($i = 0; $i < $topMenuCount; $i++){
				if ($rows[$i]['ni_view_control'] == 1){		// 改行のとき
					$menuInner .= str_repeat(M3_INDENT_SPACE, self::MAINMENU_INDENT_LEBEL) . '</ul></li><li class="' . self::MAINMENU_COL_STYLE . $columnWidth . '"><ul>' . M3_NL;
				} else {		// 改行以外のとき
					$topId = $rows[$i]['ni_id'];
			
					// サブレベル取得
					$this->db->getNavItems($navId, $topId, $subRows);
			
					// ヘルプの作成
					$helpText = '';
					$title = $rows[$i]['ni_help_title'];
					if (!empty($title)){
						$helpText = $this->gInstance->getHelpManager()->createHelpText($title, $rows[$i]['ni_help_body']);
					}
								
					// メニュー大項目
					$menuInner .= str_repeat(M3_INDENT_SPACE, self::MAINMENU_INDENT_LEBEL + 1);
					$menuInner .= '<li class="dropdown-header"><span ' . $helpText . '>' . $this->convertToDispString($rows[$i]['ni_name']) . '</span></li>' . M3_NL;
					
					// メニュー小項目
					if (count($subRows) > 0){
						for ($l = 0; $l < count($subRows); $l++){
							// 項目の種別
							$itemType = $subRows[$l]['ni_view_control'];
							
							// ヘルプの作成
							$helpText = '';
							$title = $subRows[$l]['ni_help_title'];
							if (!empty($title)){
								$helpText = $this->gInstance->getHelpManager()->createHelpText($title, $subRows[$l]['ni_help_body']);
							}
						
							$menuInner .= str_repeat(M3_INDENT_SPACE, self::MAINMENU_INDENT_LEBEL + 2);
							
							switch ($itemType){
								case 0:		// リンク項目
								default:
									$menuInner .= '<li><a href="';
									$menuInner .= $this->getUrl($this->gEnv->getDefaultAdminUrl() . '?task=' . $subRows[$l]['ni_task_id']);	// 起動タスクパラメータを設定
									if (!empty($subRows[$l]['ni_param'])){		// パラメータが存在するときはパラメータを追加
										$menuInner .= '&' . M3_REQUEST_PARAM_OPERATION_TODO . '=' . urlencode($subRows[$l]['ni_param']);
									}
									$menuInner .= '" ><span ' . $helpText . '>' . $this->convertToDispString($subRows[$l]['ni_name']) . '</span></a></li>' . M3_NL;
									break;
								case 2:		// 使用不可
									break;
								case 3:		// セパレータ
									$menuInner .= '<li class="divider"></li>' . M3_NL;
									break;
							}
						}
					}
					$menuInner .= str_repeat(' ', 4);
				}
			}
			
			$menuInner .= str_repeat(M3_INDENT_SPACE, self::MAINMENU_INDENT_LEBEL) . '</ul></li>' . M3_NL;
			$this->tmpl->addVar("menu", "menu_inner", $menuInner);
			$this->tmpl->addVar("menu", "widget_url", $this->getUrl($this->gEnv->getCurrentWidgetRootUrl()));	// ウィジェットのルートディレクトリ
			
			$this->tmpl->addVar("menu", "top_url", $this->getUrl($this->gEnv->getDefaultAdminUrl()));		// トップメニュー画面URL
			
			// サイト表示
			$siteName = $this->gEnv->getSiteName();
			$siteName = makeTruncStr($siteName, self::MAX_SITENAME_LENGTH);
			$siteUrl = $this->gEnv->getRootUrl();
			$this->tmpl->addVar("menu", "site_name", $siteName);
			$this->tmpl->addVar("menu", "pc_url", $siteUrl);
			//$this->tmpl->addVar("menu", "site", '<label><a href="#" onclick="previewSite(\'' . $siteUrl . '\');">' . $siteUrl . '</a></label>');
			
			// トップアイコンを設定
			$value = $this->gSystem->getSystemConfig(self::CF_SYSTEM_TYPE);		// システム運用タイプ
			if ($value == self::SYSTEM_TYPE_SERVER_ADMIN){		// サーバ管理の場合
				$iconUrl = $this->gEnv->getRootUrl() . self::TOP_SERVER_ADMIN_ICON_FILE;
			} else {
				$iconUrl = $this->gEnv->getRootUrl() . self::TOP_ICON_FILE;
			}
			$iconTitle = $this->_('Top Page');		// トップ画面
			$imageSize = self::SITE_ICON_SIZE;
			$iconTag = '<img class="home" src="' . $this->getUrl($iconUrl) . '" width="' . $imageSize . '" height="' . $imageSize . '" border="0" alt="' . $iconTitle . '" />';
			$this->tmpl->addVar("menu", "top_image", $iconTag);
				
			// システムバージョン
			$this->tmpl->addVar("menu", "system", 'Magic3 v' . M3_SYSTEM_VERSION);
			$this->tmpl->addVar("menu", "official_url", 'http://www.magic3.org');
			
			// ユーザ名
			$userId = $this->gEnv->getCurrentUserId();
			$ret = $this->_db->getLoginUserRecordById($userId, $row);// ユーザ情報取得
			if ($ret){
				$userName	= $row['lu_name'];	// ユーザ名
				$avatar		= $row['lu_avatar'];		// アバター
			}
			if (empty($userName)) $userName = self::UNTITLED_USER_NAME;
			$this->tmpl->addVar("menu", "user", $this->convertToDispString($userName));
			
			// アバター
			$avatarFormat = $this->gInstance->getImageManager()->getDefaultAvatarFormat();		// 画像フォーマット取得
			// アバター画像取得
			$this->gInstance->getImageManager()->parseImageFormat($avatarFormat, $imageType, $imageAttr, $imageSize);		// 画像情報取得
			$avatarUrl = $this->gInstance->getImageManager()->getAvatarUrl($avatar);
			$iconTitle = 'アバター画像';
			$imageSize = self::AVATAR_ICON_SIZE;
			$iconTag = '<img class="avatar" src="' . $this->getUrl($avatarUrl) . '" width="' . $imageSize . '" height="' . $imageSize . '" border="0" alt="' . $iconTitle . '" />';
			$this->tmpl->addVar("menu", "avatar_img", $iconTag);
		
			// ユーザメニュー
			$iconTitle = 'ログアウト';
			$iconUrl = $this->gEnv->getRootUrl() . self::LOGOUT_ICON_FILE;		// ログアウト
			$iconTag = '<img src="' . $this->getUrl($iconUrl) . '" width="' . self::ICON_SIZE . '" height="' . self::ICON_SIZE . '" border="0" alt="' . $iconTitle . '" />';
			$this->tmpl->addVar("menu", "logout_img", $iconTag);
			
			// 運用中のコンテンツを取得
			$this->contentMenu = $this->getContentMenu();			// コンテンツ編集メニュー項目取得
			$this->subContentMenu = $this->getSubContentMenu();			// サブコンテンツ編集メニュー
			
			// サイトメニュー
			$siteMenuTag = $this->createSiteMenuTag();
			$this->tmpl->addVar("menu", "site_menu", $siteMenuTag);
		}
		// ##### サブメニューバーとパンくずリストを作成 #####
		$topPos = 0;		// コンテンツの開始位置
		if ($this->useMenu) $topPos = self::MENUBAR_HEIGHT;		// コンテンツの開始位置
		
		// サブメニューバーを表示
		$subNavbarDef = $this->gPage->getAdminSubNavbarDef();
		if (!empty($subNavbarDef)){
			$topPos += self::SUB_MENUBAR_HEIGHT;		// サブメニューバーの高さ追加
					
			$this->tmpl->setAttribute('subnavbar', 'visibility', 'visible');
			if ($this->useMenu) $this->tmpl->setAttribute('usesubmenubar', 'visibility', 'visible');
			
			// サブメニューバー作成
			list($title, $menu) = $this->createSubMenubar($subNavbarDef);
			$this->tmpl->addVar('subnavbar', 'title', $title);
			$this->tmpl->addVar('subnavbar', 'menu', $menu);
		}
		// パンくずリストを表示
		$breadcrumbDef = $this->gPage->getAdminBreadcrumbDef();
		if (!empty($breadcrumbDef)){
			$this->tmpl->setAttribute('breadcrumb', 'visibility', 'visible');
			for ($i = 0; $i < count($breadcrumbDef); $i++){
				$row = array(
					'name' => $this->convertToDispString($breadcrumbDef[$i])
				);
				$this->tmpl->addVars('breadcrumb_list', $row);
				$this->tmpl->parseTemplate('breadcrumb_list', 'a');
			}
		}
		// メニューバーの高さ位置を修正
		if (!empty($subNavbarDef) || !empty($breadcrumbDef)){
			$this->tmpl->setAttribute('fixtoppos', 'visibility', 'visible');
			$this->tmpl->addVar('fixtoppos', 'second_top', $this->convertToDispString($topPos - self::SUB_MENUBAR_HEIGHT));
			$this->tmpl->addVar('fixtoppos', 'content_top', $this->convertToDispString($topPos));		// コンテンツのトップ位置
		}
			
		// 「前へ」「次へ」アイコンを設定
		$this->tmpl->setAttribute('prevnextbutton', 'visibility', 'visible');
		$iconUrl = $this->gEnv->getRootUrl() . self::PREV_ICON_FILE;
		$this->tmpl->addVar("prevnextbutton", "prev_image", $this->getUrl($iconUrl));
		$iconUrl = $this->gEnv->getRootUrl() . self::NEXT_ICON_FILE;
		$this->tmpl->addVar("prevnextbutton", "next_image", $this->getUrl($iconUrl));
				
		// テキストをローカライズ
		$localeText = array();
		$localeText['msg_logout'] = $this->_('Logout from system?');// ログアウトしますか?
//		$localeText['label_top'] = $this->_('Top');// トップ
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
		if ($this->useMenu){					// メニューを使用するかどうか
			return $this->cssFilePath;
		} else if ($this->useCloseButton){				// 「閉じる」を使用するかどうか
			$this->cssFilePath = $this->getUrl($this->gEnv->getCurrentWidgetCssUrl() . self::WIDGET_CSS_FILE);		// ウィジェット単体表示用CSSファイル
			return $this->cssFilePath;
		} else {
			return '';
		}
	}
	/**
	 * サイトメニュータグを作成
	 *
	 * @return string			サイトメニュータグ
	 */
	function createSiteMenuTag()
	{
		$menuTag = '';
		$isOpen					= $this->gSystem->siteInPublic();
		
		// アクセスポイントごとの公開状況
		$sitePcInPublic			= $this->gSystem->sitePcInPublic();			// PC用サイトの公開状況
		$siteSmartphoneInPublic = $this->gSystem->siteSmartphoneInPublic();	// スマートフォン用サイトの公開状況
		$siteMobileInPublic		= $this->gSystem->siteMobileInPublic();		// 携帯用サイトの公開状況
		
		// PC用サイトアイコン作成
		$isActiveSite = $this->gSystem->getSiteActiveStatus(0);		// PC用サイト
		if ($isActiveSite){
			$isVisibleSite = false;		// 公開中かどうか
			$iconTitle = 'PC用アクセスポイント';
			if ($isOpen && $sitePcInPublic){
				$iconUrl = $this->gEnv->getRootUrl() . self::PC_ICON_FILE;
				$isVisibleSite = true;		// 公開中かどうか
			} else {
				$iconUrl = $this->gEnv->getRootUrl() . self::PC_CLOSED_ICON_FILE;		// サイト非公開
			}
			$iconTag  = str_repeat(M3_INDENT_SPACE, self::SITEMENU_INDENT_LEBEL) . '<li class="dropdown" >' . M3_NL;
        	$iconTag .= str_repeat(M3_INDENT_SPACE, self::SITEMENU_INDENT_LEBEL + 1) .
						'<a href="#" class="dropdown-toggle device_icon" data-toggle="dropdown" data-placement="bottom" data-container="body" title="' . $iconTitle . '" rel="m3help">';
			$iconTag .= '<img src="' . $this->getUrl($iconUrl) . '" width="' . self::SITE_ICON_SIZE . '" height="' . self::SITE_ICON_SIZE . '" border="0" alt="' . $iconTitle . '" /><b class="caret"></b></a>' . M3_NL;
        	$iconTag .= str_repeat(M3_INDENT_SPACE, self::SITEMENU_INDENT_LEBEL + 1) . '<ul class="dropdown-menu">' . M3_NL;
			$iconTag .= $this->createContentMenu(0, $isVisibleSite);				// コンテンツ編集メニュー付加
			$iconTag .= str_repeat(M3_INDENT_SPACE, self::SITEMENU_INDENT_LEBEL + 1) . '</ul>'. M3_NL;
			$iconTag .= str_repeat(M3_INDENT_SPACE, self::SITEMENU_INDENT_LEBEL) . '</li>' . M3_NL;
			$menuTag .= $iconTag;
		}

		// スマートフォン用サイトアイコン作成
		$isActiveSite = $this->gSystem->getSiteActiveStatus(2);		// スマートフォン用サイト
		if ($isActiveSite){
			$iconTitle = 'スマートフォン用アクセスポイント';
			$isVisibleSite = false;		// 公開中かどうか
			if ($isOpen && $siteSmartphoneInPublic){
				$iconUrl = $this->gEnv->getRootUrl() . self::SMARTPHONE_ICON_FILE;
				$isVisibleSite = true;		// 公開中かどうか
			} else {
				$iconUrl = $this->gEnv->getRootUrl() . self::SMARTPHONE_CLOSED_ICON_FILE;// サイト非公開
			}
			$iconTag  = str_repeat(M3_INDENT_SPACE, self::SITEMENU_INDENT_LEBEL) . '<li class="dropdown" >' . M3_NL;
        	$iconTag .= str_repeat(M3_INDENT_SPACE, self::SITEMENU_INDENT_LEBEL + 1) .
						'<a href="#" class="dropdown-toggle device_icon" data-toggle="dropdown" data-placement="bottom" data-container="body" title="' . $iconTitle . '" rel="m3help">';
			$iconTag .= '<img src="' . $this->getUrl($iconUrl) . '" width="' . self::SITE_ICON_SIZE . '" height="' . self::SITE_ICON_SIZE . '" border="0" alt="' . $iconTitle . '" /><b class="caret"></b></a>' . M3_NL;
        	$iconTag .= str_repeat(M3_INDENT_SPACE, self::SITEMENU_INDENT_LEBEL + 1) . '<ul class="dropdown-menu">' . M3_NL;
			$iconTag .= $this->createContentMenu(2, $isVisibleSite);// コンテンツ編集メニュー付加
			$iconTag .= str_repeat(M3_INDENT_SPACE, self::SITEMENU_INDENT_LEBEL + 1) . '</ul>'. M3_NL;
			$iconTag .= str_repeat(M3_INDENT_SPACE, self::SITEMENU_INDENT_LEBEL) . '</li>' . M3_NL;
			$menuTag .= $iconTag;
		}

		// 携帯用サイトアイコン作成
		$isActiveSite = $this->gSystem->getSiteActiveStatus(1);		// 携帯用サイト
		if ($isActiveSite){
			$iconTitle = '携帯用アクセスポイント';
			$isVisibleSite = false;		// 公開中かどうか
			if ($isOpen && $siteMobileInPublic){
				$iconUrl = $this->gEnv->getRootUrl() . self::MOBILE_ICON_FILE;
				$isVisibleSite = true;		// 公開中かどうか
			} else {
				$iconUrl = $this->gEnv->getRootUrl() . self::MOBILE_CLOSED_ICON_FILE;// サイト非公開
			}
			$iconTag  = str_repeat(M3_INDENT_SPACE, self::SITEMENU_INDENT_LEBEL) . '<li class="dropdown" >' . M3_NL;
        	$iconTag .= str_repeat(M3_INDENT_SPACE, self::SITEMENU_INDENT_LEBEL + 1) .
						'<a href="#" class="dropdown-toggle device_icon" data-toggle="dropdown" data-placement="bottom" data-container="body" title="' . $iconTitle . '" rel="m3help">';
			$iconTag .= '<img src="' . $this->getUrl($iconUrl) . '" width="' . self::SITE_ICON_SIZE . '" height="' . self::SITE_ICON_SIZE . '" border="0" alt="' . $iconTitle . '" /><b class="caret"></b></a>' . M3_NL;
        	$iconTag .= str_repeat(M3_INDENT_SPACE, self::SITEMENU_INDENT_LEBEL + 1) . '<ul class="dropdown-menu">' . M3_NL;
			$iconTag .= $this->createContentMenu(1, $isVisibleSite);			// コンテンツ編集メニュー付加
			$iconTag .= str_repeat(M3_INDENT_SPACE, self::SITEMENU_INDENT_LEBEL + 1) . '</ul>'. M3_NL;
			$iconTag .= str_repeat(M3_INDENT_SPACE, self::SITEMENU_INDENT_LEBEL) . '</li>' . M3_NL;
			$menuTag .= $iconTag;
		}
		return $menuTag;
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
/*		$contentType = array(	M3_VIEW_TYPE_CONTENT,				// 汎用コンテンツ
								M3_VIEW_TYPE_PRODUCT,				// 製品
								M3_VIEW_TYPE_BBS,					// BBS
								M3_VIEW_TYPE_BLOG,				// ブログ
								M3_VIEW_TYPE_WIKI,				// Wiki
								M3_VIEW_TYPE_USER,				// ユーザ作成コンテンツ
								M3_VIEW_TYPE_EVENT,				// イベント
								M3_VIEW_TYPE_PHOTO);				// フォトギャラリー*/
		$contentType = $this->gPage->getMainContentTypes();
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
		$contentType = $this->gPage->getSubContentTypes();
		$ret = $this->db->getEditSubWidgetOnPage($pageIdArray, $contentType, $rows);
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
	 * @param bool $isVisibleSite		アクセスポイント公開中かどうか
	 * @return string					メニュータグ
	 */
	function createContentMenu($deviceType, $isVisibleSite)
	{
		static $mainContentTypeArray;
		static $subContentTypeArray;
		static $mainFeatureTypeArray;
		
		if (!isset($mainContentTypeArray)){
			$mainContentTypeArray = array();
			$mainContentTypeInfo = $this->gPage->getMainContentTypeInfo();		// 主要コンテンツタイプ情報
			for ($i = 0; $i < count($mainContentTypeInfo); $i++){
				$value = $mainContentTypeInfo[$i]['value'];
				$name = $mainContentTypeInfo[$i]['name'];
				$mainContentTypeArray[$value] = $name;
			}
		}
		if (!isset($subContentTypeArray)){
			$subContentTypeArray = array();
			$subContentTypeInfo = $this->gPage->getSubContentTypeInfo();		// 補助コンテンツタイプ情報
			for ($i = 0; $i < count($subContentTypeInfo); $i++){
				$value = $subContentTypeInfo[$i]['value'];
				$name = $subContentTypeInfo[$i]['name'];
				$subContentTypeArray[$value] = $name;
			}
		}
		if (!isset($mainFeatureTypeArray)){
			$mainFeatureTypeArray = array();
			$mainFeatureTypeInfo = $this->gPage->getMainFeatureTypeInfo();		// 主要機能タイプ情報
			for ($i = 0; $i < count($mainFeatureTypeInfo); $i++){
				$value = $mainFeatureTypeInfo[$i]['value'];
				$name = $mainFeatureTypeInfo[$i]['name'];
				$mainFeatureTypeArray[$value] = $name;
			}
		}

		$menuTag = '';
		$menu = $this->contentMenu[$deviceType];		// コンテンツ編集メニュー
		$subMenu = $this->subContentMenu[$deviceType];	// サブコンテンツ編集メニュー
//		if (empty($menu) && empty($subMenu)) return '';
		
		// プレビュー用リンク
		$menuTag .= str_repeat(M3_INDENT_SPACE, self::SITEMENU_INDENT_LEBEL + 2);
		
//		$title = '<i class="glyphicon glyphicon-eye-open btn-lg"></i> ' . self::MENU_TITLE_PREVIEW;		// アイコン付加
		$title = self::MENU_TITLE_PREVIEW;		// アイコン付加
		switch ($deviceType){
			case 0:			// PC用画面のとき
			default:
				$menuTag .= '<li><a href="#" onclick="m3ShowPreviewWindow(0, \'' . $this->gEnv->getDefaultUrl() . '\');return false;">' . $title . '</a></li>' . M3_NL;
				break;
			case 1:			// 携帯用画面のとき
				$menuTag .= '<li><a href="#" onclick="m3ShowPreviewWindow(1, \'' . $this->gEnv->getDefaultMobileUrl() . '\');return false;">' . $title . '</a></li>' . M3_NL;
				break;
			case 2:			// スマートフォン用画面のとき
				$menuTag .= '<li><a href="#" onclick="m3ShowPreviewWindow(2, \'' . $this->gEnv->getDefaultSmartphoneUrl() . '\');return false;">' . $title . '</a></li>' . M3_NL;
				break;
		}
		
		// コンテンツ編集メニュー
		if (!empty($menu)){
			// セパレータ
			$menuTag .= str_repeat(M3_INDENT_SPACE, self::SITEMENU_INDENT_LEBEL + 2);
			$menuTag .= '<li class="divider"></li>' . M3_NL;
		
			// タイトル
			$menuTag .= str_repeat(M3_INDENT_SPACE, self::SITEMENU_INDENT_LEBEL + 2);
			$menuTag .= '<li class="dropdown-header">' . self::MENU_TITLE_CONTENT . '</li>' . M3_NL;
		
			for ($i = 0; $i < count($menu); $i++){
				$widgetId = $menu[$i]['wd_id'];
				$title = $this->getCurrentLangString($menu[$i]['wd_content_name']);		// ウィジェットのコンテンツ名を取得
				
				if (empty($title)){
					// コンテンツ単位でタイトルを取得
					$contentType = $menu[$i]['wd_type'];
					$title = $mainContentTypeArray[$contentType];
				}
				if (empty($title)) $title = $menu[$i]['wd_name'];		// コンテンツ名が取得できないときはウィジェット名を設定
				if (empty($title)) continue;
			
				$menuTag .= str_repeat(M3_INDENT_SPACE, self::SITEMENU_INDENT_LEBEL + 2);
				$menuTag .= '<li ><a href="#" onclick="m3ShowConfigWindow(\'' . $widgetId . '\', 0, 0);return false;"><span >' . $this->convertToDispString($title) . '</span></a></li>' . M3_NL;
			}
		}
		
		// サブコンテンツ編集メニュー
		$subMenu = $this->arrangeSubMenu($subMenu);
		if (!empty($subMenu)){
			// セパレータ
			$menuTag .= str_repeat(M3_INDENT_SPACE, self::SITEMENU_INDENT_LEBEL + 2);
			$menuTag .= '<li class="divider"></li>' . M3_NL;
			
			// タイトル
			$menuTag .= str_repeat(M3_INDENT_SPACE, self::SITEMENU_INDENT_LEBEL + 2);
			$menuTag .= '<li class="dropdown-header">' . self::MENU_TITLE_SUB_CONTENT . '</li>' . M3_NL;
		
			for ($i = 0; $i < count($subMenu); $i++){
				$widgetId = $subMenu[$i]['wd_id'];
				if ($subMenu[$i]['wd_content_widget_id']) $widgetId = $subMenu[$i]['wd_content_widget_id'];
				$title = $this->getCurrentLangString($subMenu[$i]['wd_content_name']);		// ウィジェットのコンテンツ名を取得
				
				if (empty($title)){
					// コンテンツ単位でタイトルを取得(主要コンテンツ、補助コンテンツ、主要機能タイプの順に探す)
					$contentType = $subMenu[$i]['wd_content_type'];
					$title = $mainContentTypeArray[$contentType];
					if (empty($title)) $title = $subContentTypeArray[$contentType];
					if (empty($title)) $title = $mainFeatureTypeArray[$contentType];
				}
				if (empty($title)) $title = $subMenu[$i]['wd_name'];		// サブコンテンツ名が取得できないときはウィジェット名を設定
				if (empty($title)) continue;

				$menuTag .= str_repeat(M3_INDENT_SPACE, self::SITEMENU_INDENT_LEBEL + 2);
				$menuTag .= '<li ><a href="#" onclick="m3ShowConfigWindow(\'' . $widgetId . '\', 0, 0);return false;"><span >' . $this->convertToDispString($title) . '</span></a></li>' . M3_NL;
			}
		}
		// セパレータ
		$menuTag .= str_repeat(M3_INDENT_SPACE, self::SITEMENU_INDENT_LEBEL + 2);
		$menuTag .= '<li class="divider"></li>' . M3_NL;
		
		// アクセスポイントの公開制御
		if ($isVisibleSite){
			$openSiteMessage = 'アクセスポイントを非公開';
			$iconTitle = 'アクセスポイントを非公開';
			$iconUrl = $this->gEnv->getRootUrl() . self::SITE_CLOSE_ICON_FILE;// アクセスポイント非公開
		} else {
			$openSiteMessage = 'アクセスポイントを公開';
			$iconTitle = 'アクセスポイントを公開';
			$iconUrl = $this->gEnv->getRootUrl() . self::SITE_OPEN_ICON_FILE;		// アクセスポイント公開
		}
		$menuTag .= str_repeat(M3_INDENT_SPACE, self::SITEMENU_INDENT_LEBEL + 2);
		$menuTag .= '<li><a href="#" onclick="siteOpen(' . $deviceType . ',' . intval(!$isVisibleSite) . ');return false;">';
		$menuTag .= '<img src="' . $this->getUrl($iconUrl) . '" width="' . self::ICON_SIZE . '" height="' . self::ICON_SIZE . '" border="0" alt="' . $iconTitle . '" />' . $openSiteMessage . '</a></li>' . M3_NL;

		return $menuTag;
	}
	/**
	 * サブコンテンツ編集メニュー項目の重複を調整
	 *
	 * @param array $menu		サブメニュー
	 * @return array			修正済みサブメニュー
	 */
	function arrangeSubMenu($menu)
	{
		$destMenu = array();
		$widgets = array();
		for ($i = 0; $i < count($menu); $i++){
			$menuRow = $menu[$i];
			$widgetId = $menu[$i]['wd_id'];
			if (!empty($menu[$i]['wd_content_widget_id'])) $widgetId = $menu[$i]['wd_content_widget_id'];
			if (!in_array($widgetId, $widgets)){
				$destMenu[] = $menuRow;
				$widgets[] = $widgetId;
			}
		}
		return $destMenu;
	}
	/**
	 * サブメニューバー作成
	 *
	 * @param object $navbarDef	メニューバー定義
	 * @return 					なし
	 */
	function createSubMenubar($navbarDef)
	{
/*		// タイトル作成
		$titleTag = '';
		if (!empty($navbarDef->title)){
			$title = $this->convertToDispString($navbarDef->title);
			if (!empty($navbarDef->help)) $title = '<span ' . $navbarDef->help . '>' . $title . '</span>';
			$titleTag = '<div class="navbar-text title">' . $title . '</div>';
		}
		
		// メニュー作成
		$menuTag = '';
		$baseUrl = $navbarDef->baseurl;
		$menu = $navbarDef->menu;
		$menuItemCount = count($menu);
		for ($i = 0; $i < $menuItemCount; $i++){
			$menuItem = $menu[$i];
			$name	= $menuItem->name;
			$tagId	= $menuItem->tagid;
			$active = $menuItem->active;
			$disabled	= $menuItem->disabled;
			$task	= $menuItem->task;
			$url	= $menuItem->url;
			$help	= $menuItem->help;
			$subMenu = $menuItem->submenu;
			
			if (empty($subMenu)){		// サブメニューを持たない場合
				if ($active){
					$buttonType = 'btn-primary';
				} else {
					$buttonType = 'btn-success';
				}
				if ($disabled) $buttonType .= ' disabled';		// 使用可否
				$tagIdAttr = '';		// タグID
				if (!empty($tagId)) $tagIdAttr = ' id="' . $tagId . '"';
				
				// タスクまたはURLが設定されている場合はリンクを設定
				$event = '';
				$linkUrl = '';			// リンク先 
				if (!empty($task)) $linkUrl = createUrl($baseUrl, 'task=' . $task);
				if (empty($linkUrl)) $linkUrl = $url;
				if (!empty($linkUrl)) $event = ' onclick="window.location=\'' . $linkUrl . '\';"';
				$button = '<button type="button"' . $tagIdAttr . ' class="btn navbar-btn ' . $buttonType . '"' . $event . '>' . $this->convertToDispString($name) . '</button>';
				if (!empty($help)) $button = '<span ' . $help . '>' . $button . '</span>';
				$menuTag .= '<li>' . $button . '</li>';
			} else {		// サブメニューがある場合
				// アクティブな項目があるかチェック
				$subMenuTag = '';
				for ($j = 0; $j < count($subMenu); $j++){
					$subMenuItem = $subMenu[$j];
					$subName	= $subMenuItem->name;
					$subTagId	= $subMenuItem->tagid;
					$subActive	= $subMenuItem->active;
					$subDisabled	= $subMenuItem->disabled;
					$task		= $subMenuItem->task;
					$url		= $subMenuItem->url;
					
					$linkUrl = '';			// リンク先 
					if (!empty($task)) $linkUrl = createUrl($baseUrl, 'task=' . $task);
					if (empty($linkUrl)) $linkUrl = $url;
					if (empty($linkUrl)) $linkUrl = '#';
					$classActive = '';
					if ($subDisabled){		// 使用可否
						$classActive = ' class="disabled"';
						$linkUrl = '#';
					} else if ($subActive){
						$classActive = ' class="active"';
						$active = true;			// 親の階層もアクティブにする
					}
					$tagIdAttr = '';		// タグID
					if (!empty($subTagId)) $tagIdAttr = ' id="' . $subTagId . '"';
					$subMenuTag .= '<li' . $tagIdAttr . $classActive . '><a href="' . $this->getUrl($linkUrl) . '">' . $this->convertToDispString($subName) . '</a></li>';
				}
				$subMenuTag = '<ul class="dropdown-menu" role="menu">' . $subMenuTag . '</ul>';

 				if ($active){
					$buttonType = 'btn-primary';
				} else {
					$buttonType = 'btn-success';
				}
				$menuTag .= '<li><a class="btn navbar-btn ' . $buttonType . '" data-toggle="dropdown" href="#" >' . $this->convertToDispString($name) . ' <span class="caret"></span></a>' . $subMenuTag . '</li>';
			}
		}
		if (!empty($menuTag)) $menuTag = '<ul class="nav navbar-nav">' . $menuTag . '</ul>';
		*/
		// タイトル作成
		$titleTag = $this->gDesign->createSubMenubarTitleTag($navbarDef, 2/*システム画面(共通設定画面等)*/);
		
		// メニュー作成
		$menuTag = $this->gDesign->createSubMenubarMenuTag($navbarDef);
		
		return array($titleTag, $menuTag);
	}
}
?>
