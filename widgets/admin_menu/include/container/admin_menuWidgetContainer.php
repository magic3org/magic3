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
 * @copyright  Copyright 2006-2021 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseAdminWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/admin_menuDb.php');

class admin_menuWidgetContainer extends BaseAdminWidgetContainer
{
	protected $db;	// DB接続オブジェクト
	protected $cssFilePath;			// CSSファイル
	protected $contentMenu;			// コンテンツ編集メニュー
	protected $subContentMenu;			// サブコンテンツ編集メニュー
	protected $useMenu;				// メニューを使用するかどうか
	protected $useCloseButton;				// 「閉じる」を使用するかどうか
	protected $systemType;			// システム運用タイプ
	const DEFAULT_CSS_FILE = '/default2.1.css';		// CSSファイル
	const WIDGET_CSS_FILE = '/widget.css';			// ウィジェット単体表示用CSS
	const DEFAULT_NAV_ID = 'admin_menu';			// ナビゲーションメニューID
	const HELP_ICON_FILE = '/images/system/help24.gif';		// ヘルプアイコン
	const SITE_TEST_ICON_FILE = '/images/system/site_test32.png';		// サイトテスト中アイコン
	const TOP_MENU_ICON_FILE = '/images/system/home32_menu.png';		// トップ遷移アイコン(サイト運用モード)
	const DEVELOP_ICON_FILE = '/images/system/develop32.png';		// 開発モードアイコン
	const TOP_SERVER_ADMIN_ICON_FILE = '/images/system/globe32.png';		// トップ遷移アイコン(サーバ管理運用の場合)
	const SMALL_DEVICE_ICON_FILE = '/images/system/smalldevice32.png';		// 小画面デバイスアイコン(小画面最適化実行時)
//	const CLOSE_ICON_FILE = '/images/system/close32.png';		// ウィンドウ閉じるアイコン
	const PREV_ICON_FILE = '/images/system/prev48.png';		// ウィンドウ「前へ」アイコン
	const NEXT_ICON_FILE = '/images/system/next48.png';		// ウィンドウ「次へ」アイコン
	const PC_ICON_FILE = '/images/system/device/pc.png';		// PCアイコン
	const SMARTPHONE_ICON_FILE = '/images/system/device/smartphone.png';		// スマートフォンアイコン
	const PC_CLOSED_ICON_FILE = '/images/system/device/pc_closed.png';		// PCアイコン(非公開)
	const SMARTPHONE_CLOSED_ICON_FILE = '/images/system/device/smartphone_closed.png';		// スマートフォンアイコン(非公開)
	const MENU_ICON_FILE = '/images/system/menu24.png';		// メニュー定義画面アイコン
	const MAX_SITENAME_LENGTH = 20;		// サイト名の最大文字数
	const MAX_SITENAME_LENGTH_S = 9;		// サイト名の最大文字数
	const ICON_SIZE = 24;			// アイコンサイズ
	const SITE_ICON_SIZE = 32;			// サイトメニューアイコンサイズ
	const AVATAR_ICON_SIZE = 32;		// ユーザアバターアイコンサイズ
	const HELP_TITLE = 'ヘルプ';
	const MENU_TITLE_PREVIEW = 'プレビュー';
	const MENU_TITLE_CONTENT = 'コンテンツ管理';		// コンテンツ編集メニューのタイトル
	const MENU_TITLE_SUB_CONTENT = 'サブコンテンツ管理';		// サブコンテンツ編集メニューのタイトル
	const MENU_TITLE_MENUDEF = 'メニュー定義';		// メニュー定義編集メニューのタイトル
	const UNTITLED_USER_NAME = '名称なしユーザ';		// ユーザ名が設定されていなかった場合の表示名
	const MAINMENU_INDENT_LEBEL = 4;		// メインメニューのインデントレベル
	const SITEMENU_INDENT_LEBEL = 2;		// サイトメニューのインデントレベル
	const MAINMENU_COL_STYLE = 'col-md-';	// Bootstrapのカラムクラス
	const MENUBAR_HEIGHT = 60;			// メインメニューバーの高さ
	const SUB_MENUBAR_HEIGHT = 50;			// サブメニューバーの高さ
//	const SEL_MENU_ID = 'admin_menu';		// メニュー変換対象メニューバーID
//	const TREE_MENU_TASK	= 'menudef';	// メニュー管理画面(多階層)
	const TASK_MENUDEF			= 'menudef';				// メニュー定義画面の使用可否のチェック用
	const TASK_SITEOPEN			= 'siteopen';			// アクセスポイントの公開,非公開
	
	// DB定義値
	const CF_SITE_IN_PUBLIC			= 'site_in_public';			// サイト公開状況
	const CF_SITE_PC_IN_PUBLIC		= 'site_pc_in_public';				// PC用サイトの公開状況
	const CF_SITE_SMARTPHONE_IN_PUBLIC = 'site_smartphone_in_public';		// スマートフォン用サイトの公開状況
	const CF_SITE_OPERATION_MODE = 'site_operation_mode';			// サイト運用モード
	const CF_TEST_MODE				= 'test_mode';				// テストモードかどうか
	const CF_PERMIT_DETAIL_CONFIG	= 'permit_detail_config';				// 詳細設定が可能かどうか
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
		
//		if (!$this->gEnv->isSystemAdmin()) return;	// システム管理者以外の場合は終了
		if (!$this->gEnv->isSystemManageUser()) return;	// システム運用権限がない場合は終了(2018/8/5変更)
		
		$menu = $request->trimValueOf('menu');
		$openBy = $request->trimValueOf(M3_REQUEST_PARAM_OPEN_BY);		// ウィンドウオープンタイプ
		$pageSubId = $request->trimValueOf(M3_REQUEST_PARAM_PAGE_SUB_ID);		// ページIDを取得
		$act = $request->trimValueOf('act');
		
		if ($act == 'opensite' && !$this->gPage->isPersonalMode()){		// サイト公開制御。パーソナルモードの場合は変更不可。
			$deviceType = $request->trimIntValueOf('device', '0');
			$isOpen = $request->trimIntValueOf('isopen', '0');		// サイトの公開状況

			$siteInPublic			= $this->gSystem->siteInPublic();			// サイト全体の公開状況
			$sitePcInPublic			= $this->gSystem->sitePcInPublic();			// PC用サイトの公開状況
			$siteSmartphoneInPublic = $this->gSystem->siteSmartphoneInPublic();	// スマートフォン用サイトの公開状況
			
			switch ($deviceType){
				case 0:			// PC用画面のとき
					if ($isOpen){
						if ($siteInPublic){		// 全サイト公開のとき
							$this->_db->updateSystemConfig(self::CF_SITE_PC_IN_PUBLIC, 1);	// PCサイト公開
						} else {
							$this->_db->updateSystemConfig(self::CF_SITE_IN_PUBLIC, 1);		// サイト運用開始
							
							$this->_db->updateSystemConfig(self::CF_SITE_PC_IN_PUBLIC, 1);	// PCサイト公開
							$this->_db->updateSystemConfig(self::CF_SITE_SMARTPHONE_IN_PUBLIC, 0);	// スマートフォンサイト公開
						}
					} else {
						if ($siteInPublic){		// 全サイト公開のとき
							$this->_db->updateSystemConfig(self::CF_SITE_PC_IN_PUBLIC, 0);	// PCサイト非公開
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
				//$iconUrl = $this->gEnv->getRootUrl() . self::CLOSE_ICON_FILE;
				//$this->tmpl->addVar("closebutton", "close_image", $this->getUrl($iconUrl));
				
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
			// メインメニューの作成。パーソナルモードの場合はメインメニューは作成しない。
			if ($this->gPage->isPersonalMode()){
				$this->tmpl->setAttribute('mainmenu', 'visibility', 'hidden');
			} else {		// メインメニュー表示の場合
				// システムの表示モードを取得
				$isSiteOperationModeOn = $this->gSystem->getSystemConfig(self::CF_SITE_OPERATION_MODE);		// サイト運用モード
		
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
				$escapeColumnEnd = true;		// 改行読み飛ばしをリセット
				for ($i = 0; $i < $topMenuCount; $i++){
					// 非表示オプション取得
					$hideOptions = array();
					if (!empty($rows[$i]['ni_hide_option'])) $hideOptions = explode(',', $rows[$i]['ni_hide_option']);
				
					// サイト運用モードがオンの場合は非表示項目を非表示にする
					if ($isSiteOperationModeOn && in_array('site_operation', $hideOptions)) continue;
				
					// ### 読み飛ばさない行が一行でもある場合は改行を出力する ###
					if ($rows[$i]['ni_view_control'] == 1){		// 改行のとき
						if (!$escapeColumnEnd) $columnCount++;
					
						$escapeColumnEnd = true;		// 改行読み飛ばしをリセット
					} else {		// 改行以外のとき
						$escapeColumnEnd = false;		// 改行読み飛ばしなしにセット
					}
				}

				// 最後が改行でない場合を修正
				if ($topMenuCount > 0 && $rows[$topMenuCount -1]['ni_view_control'] == 0 && !$escapeColumnEnd) $columnCount++;
				$columnWidth = 12 / $columnCount;		// Bootstrapでの幅
				$menuInner = str_repeat(M3_INDENT_SPACE, self::MAINMENU_INDENT_LEBEL) . '<li class="' . self::MAINMENU_COL_STYLE . $columnWidth . '"><ul>' . M3_NL;
			
				$escapeColumnEnd = true;		// 改行読み飛ばしをリセット
				for ($i = 0; $i < $topMenuCount; $i++){
					// 非表示オプション取得
					$hideOptions = array();
					if (!empty($rows[$i]['ni_hide_option'])) $hideOptions = explode(',', $rows[$i]['ni_hide_option']);
				
					// サイト運用モードがオンの場合は非表示項目を非表示にする
					if ($isSiteOperationModeOn && in_array('site_operation', $hideOptions)) continue;
				
					// ### 読み飛ばさない行が一行でもある場合は改行を出力する ###
					if ($rows[$i]['ni_view_control'] == 1){		// 改行のとき
						// 改行読み飛ばしのときは終了
						if ($escapeColumnEnd) continue;
					
						$menuInner .= str_repeat(M3_INDENT_SPACE, self::MAINMENU_INDENT_LEBEL) . '</ul></li><li class="' . self::MAINMENU_COL_STYLE . $columnWidth . '"><ul>' . M3_NL;
					
						$escapeColumnEnd = true;		// 改行読み飛ばしをリセット
					} else {		// 改行以外のとき
						$escapeColumnEnd = false;		// 改行読み飛ばしなしにセット
					
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
										$menuInner .= '<li class="divider hidden-xs"></li>' . M3_NL;
										break;
								}
							}
						}
						$menuInner .= str_repeat(' ', 4);
					}
				}
			
				$menuInner .= str_repeat(M3_INDENT_SPACE, self::MAINMENU_INDENT_LEBEL) . '</ul></li>' . M3_NL;
				$this->tmpl->addVar("mainmenu", "menu_inner", $menuInner);
				
				// メインメニューのカラム数が少ない場合はメインメニューの左位置を「メニュー」ボタンの左位置に合わせる
				if ($columnCount <= 2) $this->tmpl->setAttribute('smallmainmenu', 'visibility', 'visible');
			}
			
			//$this->tmpl->addVar("menu", "widget_url", $this->getUrl($this->gEnv->getCurrentWidgetRootUrl()));	// ウィジェットのルートディレクトリ
			$this->tmpl->addVar("menu", "top_url", $this->getUrl($this->gEnv->getDefaultAdminUrl()));		// トップメニュー画面URL
			
			// サイト表示
			$siteName = $this->gEnv->getSiteName();
			if ($this->_isSmallDeviceOptimize){			// 小画面デバイス最適化の場合
				$siteName = makeTruncStr($siteName, self::MAX_SITENAME_LENGTH_S);
			} else {
				$siteName = makeTruncStr($siteName, self::MAX_SITENAME_LENGTH);
			}
			$siteUrl = $this->gEnv->getRootUrl();
			$this->tmpl->addVar("menu", "site_name", $siteName);
			$this->tmpl->addVar("menu", "pc_url", $siteUrl);
			//$this->tmpl->addVar("menu", "site", '<label><a href="#" onclick="previewSite(\'' . $siteUrl . '\');">' . $siteUrl . '</a></label>');
			
			// 小画面最適化アイコン
			if ($this->_isSmallDeviceOptimize){				// 管理画面の小画面デバイス最適化を行う場合
				$iconUrl = $this->gEnv->getRootUrl() . self::SMALL_DEVICE_ICON_FILE;
				$iconTitle = $this->_('Small Screen Device');		// 小画面デバイス
				$imageSize = self::SITE_ICON_SIZE;
				$iconTag = '<img class="smalldevice" src="' . $this->getUrl($iconUrl) . '" width="' . $imageSize . '" height="' . $imageSize . '" border="0" alt="' . $iconTitle . '" />';
				$this->tmpl->addVar("menu", "small_device_image", $iconTag);
			}
			
			// トップアイコンを設定
			$iconUrl = '';
			$this->systemType = $this->gSystem->getSystemConfig(self::CF_SYSTEM_TYPE);		// システム運用タイプ
			$testMode = $this->gSystem->getSystemConfig(self::CF_TEST_MODE);	// テストモードかどうか
			$developMode = $this->gSystem->getSystemConfig(self::CF_PERMIT_DETAIL_CONFIG);	// 開発モード
			$preTitle = '';
			if ($testMode){		// テストモードかどうか
				$iconUrl = $this->gEnv->getRootUrl() . self::SITE_TEST_ICON_FILE;		// サイトテスト中アイコン
				$preTitle = '[テスト中]';			// 開発モード
			} else if ($developMode){		// 開発モードの場合
				$iconUrl = $this->gEnv->getRootUrl() . self::DEVELOP_ICON_FILE;		// 開発モードアイコン
				$preTitle = '[' . $this->_('Develop Mode') . ']';			// 開発モード
			} else {
				if ($this->systemType == self::SYSTEM_TYPE_SERVER_ADMIN){		// サーバ管理の場合
					$iconUrl = $this->gEnv->getRootUrl() . self::TOP_SERVER_ADMIN_ICON_FILE;
				} else {
					if ($isSiteOperationModeOn){			// サイト運用モードのとき
						$iconUrl = $this->gEnv->getRootUrl() . self::TOP_MENU_ICON_FILE;
					} else {
						// デフォルトのダッシュボードアイコンを表示
					}
				}
			}
			//$iconTitle = $this->_('Top Page');		// トップ画面
			$iconTitle = $this->_('Go to Dashboard');		// ダッシュボードへ
			$imageSize = self::SITE_ICON_SIZE;
			if (empty($iconUrl)){
				$iconTag = '<i class="dashboard fas fa-tachometer-alt text-warning"></i>';	// ダッシュボードアイコン
			} else {
				$iconTag = '<img class="home" src="' . $this->getUrl($iconUrl) . '" width="' . $imageSize . '" height="' . $imageSize . '" border="0" alt="' . $iconTitle . '" />';
			}
			
			//$topTitle = $this->_('Go Top');		// トップ画面へ
			$topTitle = $this->_('Go to Dashboard');		// ダッシュボードへ
			$this->tmpl->addVar("menu", "top_image", $iconTag);
			$this->tmpl->addVar("menu", "top_title", $preTitle . $topTitle);
				
			// システムバージョン。パーソナルモードの場合は表示しない。
			if ($this->gPage->isPersonalMode()){
				$this->tmpl->setAttribute('system_version', 'visibility', 'hidden');
			} else {
				$this->tmpl->addVar("system_version", "system", 'Magic3 v' . M3_SYSTEM_VERSION);
				$this->tmpl->addVar("system_version", "official_url", M3_SYSTEM_OFFICIAL_SITE);
			}
			
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
			$iconTag = '<i class="fas fa-user fa-2x" style="color: black;"></i>';		// デフォルトカラーは黒。黒以外の場合はtext-danger等クラスを使用する。
			$this->tmpl->addVar("menu", "user_info_img", $iconTag);
			$loginStatusUrl = '?task=userlist_detail&userid=' . $userId;// ユーザ情報画面URL
			$this->tmpl->addVar("menu", "user_info_url", $this->convertUrlToHtmlEntity($loginStatusUrl));
			//$iconTitle = 'ログアウト';
			//$iconUrl = $this->gEnv->getRootUrl() . self::LOGOUT_ICON_FILE;		// ログアウト
			//$iconTag = '<img src="' . $this->getUrl($iconUrl) . '" width="' . self::ICON_SIZE . '" height="' . self::ICON_SIZE . '" border="0" alt="' . $iconTitle . '" />';
			$iconTag = '<i class="fas fa-power-off fa-2x text-danger"></i>';
			$this->tmpl->addVar("menu", "logout_img", $iconTag);
			
			// 運用中のコンテンツを取得
			$this->contentMenu = $this->getContentMenu();			// コンテンツ編集メニュー項目取得
			$this->subContentMenu = $this->getSubContentMenu();			// サブコンテンツ編集メニュー
			
			// サイトメニュー
			$siteMenuTag = $this->createSiteMenuTag();
			$this->tmpl->addVar("menu", "site_menu", $siteMenuTag);
			
			// システムバージョンアップ情報
			// ダッシュボード画面の場合のみアップデート情報を取得
			if ($this->gPage->getContentType() == M3_VIEW_TYPE_DASHBOARD){
				$this->tmpl->setAttribute('system_update', 'visibility', 'visible');	// アップデート表示タグ
				$this->tmpl->setAttribute('checkupdate', 'visibility', 'visible');		// アップデート取得スクリプト
			}
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
			if (empty($title)) $title = '&nbsp;';			// タイトルが空の場合は左位置調整のためにスペースを設定
			$this->tmpl->addVar('subnavbar', 'title', $title);
			$this->tmpl->addVar('subnavbar', 'menu', $menu);
		}
		// パンくずリストを表示
		$breadcrumbDef = $this->gPage->getAdminBreadcrumbDef($helpDef);
		if (!empty($breadcrumbDef)){
			$this->tmpl->setAttribute('breadcrumb', 'visibility', 'visible');
			$breadcrumbHtml = $this->gDesign->createAdminBreadcrumb($breadcrumbDef, $helpDef);
			$this->tmpl->addVar('breadcrumb', 'html', $breadcrumbHtml);
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
		$localeText['label_user_info'] = $this->_('User Information');// ユーザ情報
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
						'<a href="#" class="dropdown-toggle device_icon" data-toggle="dropdown" data-placement="left" data-container="body" title="' . $iconTitle . '" rel="m3help">';
			$iconTag .= '<img src="' . $this->getUrl($iconUrl) . '" width="' . self::SITE_ICON_SIZE . '" height="' . self::SITE_ICON_SIZE . '" border="0" alt="' . $iconTitle . '" /><b class="caret"></b></a>' . M3_NL;
        	$iconTag .= str_repeat(M3_INDENT_SPACE, self::SITEMENU_INDENT_LEBEL + 1) . '<ul class="dropdown-menu">' . M3_NL;
			$iconTag .= $this->createContentMenu(0, $isVisibleSite);				// コンテンツ編集メニュー付加
			$iconTag .= $this->createMenuDefMenu(0, $isVisibleSite);				// メニュー定義編集メニュー付加
			$iconTag .= $this->createAccessPointControlMenu(0, $isVisibleSite);				// アクセスポイント公開制御メニュー付加
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
						'<a href="#" class="dropdown-toggle device_icon" data-toggle="dropdown" data-placement="left" data-container="body" title="' . $iconTitle . '" rel="m3help">';
			$iconTag .= '<img src="' . $this->getUrl($iconUrl) . '" width="' . self::SITE_ICON_SIZE . '" height="' . self::SITE_ICON_SIZE . '" border="0" alt="' . $iconTitle . '" /><b class="caret"></b></a>' . M3_NL;
        	$iconTag .= str_repeat(M3_INDENT_SPACE, self::SITEMENU_INDENT_LEBEL + 1) . '<ul class="dropdown-menu">' . M3_NL;
			$iconTag .= $this->createContentMenu(2, $isVisibleSite);// コンテンツ編集メニュー付加
			$iconTag .= $this->createMenuDefMenu(2, $isVisibleSite);				// メニュー定義編集メニュー付加
			$iconTag .= $this->createAccessPointControlMenu(2, $isVisibleSite);				// アクセスポイント公開制御メニュー付加
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
			$subContentTypeInfo = $this->gPage->getSubContentTypeInfo();		// サブコンテンツタイプ情報
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
		// アイコン
		if ($this->_isSmallDeviceOptimize){				// 管理画面の小画面デバイス最適化を行う場合
			$iconTag = '<i class="glyphicon glyphicon-picture"></i> ';
		} else {
			//$iconTitle = 'プレビュー';
			//$iconUrl = $this->gEnv->getRootUrl() . self::PREVIEW_ICON_FILE;		// プレビューアイコン
			//$iconTag = '<img src="' . $this->getUrl($iconUrl) . '" width="' . self::ICON_SIZE . '" height="' . self::ICON_SIZE . '" border="0" alt="' . $iconTitle . '" />';
			$iconTag = '<i class="fas fa-image fa-2x" style="color: black;"></i>';
		}
		$title = self::MENU_TITLE_PREVIEW;		// アイコン付加
		switch ($deviceType){
			case 0:			// PC用画面のとき
			default:
				$menuTag .= '<li><a href="#" onclick="m3ShowPreviewWindow(0, \'' . $this->gEnv->getDefaultUrl() . '\');return false;">' . $iconTag . $title . '</a></li>' . M3_NL;
				break;
			case 2:			// スマートフォン用画面のとき
				$menuTag .= '<li><a href="#" onclick="m3ShowPreviewWindow(2, \'' . $this->gEnv->getDefaultSmartphoneUrl() . '\');return false;">' . $iconTag . $title . '</a></li>' . M3_NL;
				break;
		}
		
		// コンテンツ編集メニュー
		if (!empty($menu)){
			// セパレータ
			$menuTag .= str_repeat(M3_INDENT_SPACE, self::SITEMENU_INDENT_LEBEL + 2);
			$menuTag .= '<li class="divider hidden-xs"></li>' . M3_NL;
		
			// タイトル
			$menuTag .= str_repeat(M3_INDENT_SPACE, self::SITEMENU_INDENT_LEBEL + 2);
			$menuTag .= '<li class="dropdown-header">' . self::MENU_TITLE_CONTENT . '</li>' . M3_NL;
		
			// アイコン
			if ($this->_isSmallDeviceOptimize){				// 管理画面の小画面デバイス最適化を行う場合
				$iconTag = '<i class="glyphicon glyphicon-cog"></i> ';
			} else {
				//$iconTitle = 'ウィジェット設定';
				//$iconUrl = $this->gEnv->getRootUrl() . self::CONFIG_ICON_FILE;		// ウィジェット設定アイコン
				//$iconTag = '<img src="' . $this->getUrl($iconUrl) . '" width="' . self::ICON_SIZE . '" height="' . self::ICON_SIZE . '" border="0" alt="' . $iconTitle . '" />';
				$iconTag = '<i class="fas fa-cog fa-2x" style="color: black;"></i>';
			}
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
				$menuTag .= '<li ><a href="#" onclick="m3ShowConfigWindow(\'' . $widgetId . '\', 0, 0);return false;">' . $iconTag . $this->convertToDispString($title) . '</a></li>' . M3_NL;
			}
		}
		
		// サブコンテンツ編集メニュー
		$subMenu = $this->arrangeSubMenu($subMenu);
		if (!empty($subMenu)){
			// セパレータ
			$menuTag .= str_repeat(M3_INDENT_SPACE, self::SITEMENU_INDENT_LEBEL + 2);
			$menuTag .= '<li class="divider hidden-xs"></li>' . M3_NL;
			
			// タイトル
			$menuTag .= str_repeat(M3_INDENT_SPACE, self::SITEMENU_INDENT_LEBEL + 2);
			$menuTag .= '<li class="dropdown-header">' . self::MENU_TITLE_SUB_CONTENT . '</li>' . M3_NL;
		
			// アイコン
			if ($this->_isSmallDeviceOptimize){				// 管理画面の小画面デバイス最適化を行う場合
				$iconTag = '<i class="glyphicon glyphicon-cog"></i> ';
			} else {
				//$iconTitle = 'ウィジェット設定';
				//$iconUrl = $this->gEnv->getRootUrl() . self::CONFIG_ICON_FILE;		// ウィジェット設定アイコン
				//$iconTag = '<img src="' . $this->getUrl($iconUrl) . '" width="' . self::ICON_SIZE . '" height="' . self::ICON_SIZE . '" border="0" alt="' . $iconTitle . '" />';
				$iconTag = '<i class="fas fa-cog fa-2x" style="color: black;"></i>';
			}
			
			for ($i = 0; $i < count($subMenu); $i++){
				$widgetId = $subMenu[$i]['wd_id'];
				if ($subMenu[$i]['wd_content_widget_id']) $widgetId = $subMenu[$i]['wd_content_widget_id'];
				$title = $this->getCurrentLangString($subMenu[$i]['wd_content_name']);		// ウィジェットのコンテンツ名を取得
				
				if (empty($title)){
					// コンテンツ単位でタイトルを取得(主要コンテンツ、サブコンテンツ、主要機能タイプの順に探す)
					$contentType = $subMenu[$i]['wd_content_type'];
					$title = $mainContentTypeArray[$contentType];
					if (empty($title)) $title = $subContentTypeArray[$contentType];
					if (empty($title)) $title = $mainFeatureTypeArray[$contentType];
				}
				if (empty($title)) $title = $subMenu[$i]['wd_name'];		// サブコンテンツ名が取得できないときはウィジェット名を設定
				if (empty($title)) continue;

				$menuTag .= str_repeat(M3_INDENT_SPACE, self::SITEMENU_INDENT_LEBEL + 2);
				$menuTag .= '<li ><a href="#" onclick="m3ShowConfigWindow(\'' . $widgetId . '\', 0, 0);return false;">' . $iconTag . $this->convertToDispString($title) . '</a></li>' . M3_NL;
			}
		}
		return $menuTag;
	}
	/**
	 * アクセスポイント公開制御メニュー作成
	 *
	 * @param int $deviceType			デバイスタイプ
	 * @param bool $isVisibleSite		アクセスポイント公開中かどうか
	 * @return string					メニュータグ
	 */
	function createAccessPointControlMenu($deviceType, $isVisibleSite)
	{
		// パーソナルモードの場合はアクセスポイントの公開制御は使用不可
		if ($this->gPage->isPersonalMode()) return '';
		
		// システム運用者の場合はアクセス許可がなければ表示しない
		if ($this->gEnv->isSystemManager() && !in_array(self::TASK_SITEOPEN, $this->gSystem->getSystemManagerEnableTask())) return '';
		
		// サーバ管理でのシステム運用の場合はアクセスポイントの制御項目を表示しない
		if ($this->systemType == self::SYSTEM_TYPE_SERVER_ADMIN) return '';		// サーバ管理の場合

		// セパレータ
		$menuTag = str_repeat(M3_INDENT_SPACE, self::SITEMENU_INDENT_LEBEL + 2);
		$menuTag .= '<li class="divider hidden-xs"></li>' . M3_NL;
		
		// アクセスポイントの公開制御
		if ($isVisibleSite){
			$openSiteMessage = 'アクセスポイントを非公開';
			//$iconTitle = 'アクセスポイントを非公開';
			$iconTag = '<i class="fas fa-minus-circle fa-2x text-danger"></i>';
		} else {
			$openSiteMessage = 'アクセスポイントを公開';
			//$iconTitle = 'アクセスポイントを公開';
			$iconTag = '<i class="fas fa-caret-square-right fa-2x text-success"></i>';
		}
		$menuTag .= str_repeat(M3_INDENT_SPACE, self::SITEMENU_INDENT_LEBEL + 2);
		$menuTag .= '<li><a href="#" onclick="siteOpen(' . $deviceType . ',' . intval(!$isVisibleSite) . ');return false;">';
		//$menuTag .= '<img src="' . $this->getUrl($iconUrl) . '" width="' . self::ICON_SIZE . '" height="' . self::ICON_SIZE . '" border="0" alt="' . $iconTitle . '" />' . $openSiteMessage . '</a></li>' . M3_NL;
		$menuTag .= $iconTag . $openSiteMessage . '</a></li>' . M3_NL;
		
		return $menuTag;
	}
	
	/**
	 * メニュー定義編集メニュー作成
	 *
	 * @param int $deviceType			デバイスタイプ
	 * @param bool $isVisibleSite		アクセスポイント公開中かどうか
	 * @return string					メニュータグ
	 */
	function createMenuDefMenu($deviceType, $isVisibleSite)
	{
		// システム運用者の場合はアクセス許可がなければ表示しない
		if ($this->gEnv->isSystemManager() && !in_array(self::TASK_MENUDEF, $this->gSystem->getSystemManagerEnableTask())) return '';
		
		// セパレータ
		$menuTag = str_repeat(M3_INDENT_SPACE, self::SITEMENU_INDENT_LEBEL + 2);
		$menuTag .= '<li class="divider hidden-xs"></li>' . M3_NL;
	
		// タイトル
		$menuTag .= str_repeat(M3_INDENT_SPACE, self::SITEMENU_INDENT_LEBEL + 2);
		$menuTag .= '<li class="dropdown-header">' . self::MENU_TITLE_MENUDEF . '</li>' . M3_NL;

		// ページID取得
		$pageId = '';
		switch ($deviceType){
			case 0:			// PC用画面のとき
			default:
				$pageId = $this->gEnv->getDefaultPageId();
				break;
			case 2:			// スマートフォン用画面のとき
				$pageId = $this->gEnv->getDefaultSmartphonePageId();
				break;
		}
		$ret = $this->db->getMenuId($pageId, $rows);
		if ($ret){
			// 重複しないメニュー情報を作成
			$menuIdArray = array();
			$destMenu = array();
			for ($i = 0; $i < count($rows); $i++){
				$menuId = $rows[$i]['pd_menu_id'];
				if (!in_array($menuId, $menuIdArray)){
					$menuIdArray[] = $menuId;
					$destMenu[] = $rows[$i];
				}
			}

			//$iconTitle = 'メニュー定義';
			//$iconUrl = $this->gEnv->getRootUrl() . self::MENU_ICON_FILE;		// メニュー定義画面アイコン
			
			// メニュー定義の編集画面のタイプを取得
//			$isHierMenu = $this->getMenuIsHider();			// メニュー定義編集画面が多階層タイプであるかどうか
			$isHierMenu = $this->gSystem->isSiteMenuHier();
		
			// メニュー定義画面のURLを作成
			$taskValue = 'menudef';
			if (empty($isHierMenu)) $taskValue = 'smenudef';
		
			for ($i = 0; $i < count($destMenu); $i++){
				$title = $destMenu[$i]['mn_name'];
				$menuDefUrl = $this->gEnv->getDefaultAdminUrl() . '?' . 'task=' . $taskValue . '&menuid=' . $destMenu[$i]['pd_menu_id'];
			
				$menuTag .= str_repeat(M3_INDENT_SPACE, self::SITEMENU_INDENT_LEBEL + 2);
				$menuTag .= '<li><a href="' . $this->getUrl($menuDefUrl) . '">';
//				$menuTag .= '<img src="' . $this->getUrl($iconUrl) . '" width="' . self::ICON_SIZE . '" height="' . self::ICON_SIZE . '" border="0" alt="' . $iconTitle . '" />' . $this->convertToDispString($title) . '</a></li>' . M3_NL;
				$menuTag .= '<i class="fas fa-stream fa-2x" style="color: black;"></i>' . $this->convertToDispString($title) . '</a></li>' . M3_NL;
			}
		}
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
		// タイトル作成
		$titleTag = $this->gDesign->createSubMenubarTitleTag($navbarDef, 2/*システム画面(共通設定画面等)*/);
		
		// メニュー作成
		$menuTag = $this->gDesign->createSubMenubarMenuTag($navbarDef);
		
		return array($titleTag, $menuTag);
	}
	/**
	 * メニュー管理画面が多階層メニューかどうかを取得
	 *
	 * @return bool				true=多階層、false=単階層
	 */
/*	function getMenuIsHider()
	{
		$isHier = false;	// 多階層メニューかどうか
		$ret = $this->db->getNavItemsByTask(self::SEL_MENU_ID, self::TREE_MENU_TASK, $row);
		if ($ret) $isHier = true;

		return $isHier;
	}*/
}
?>
