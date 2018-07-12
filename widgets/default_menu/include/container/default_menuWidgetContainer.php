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
 * @copyright  Copyright 2006-2018 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/default_menuDb.php');

class default_menuWidgetContainer extends BaseWidgetContainer
{
	private $db;			// DB接続オブジェクト
	private $targetObj;		// 設定オブジェクト
	private $langId;		// 現在の言語
	private $paramObj;		// 定義取得用
	private $cssFilePath = array();			// CSSファイル
	private $templateType;		// テンプレートのタイプ
	private $isHierMenu;		// 階層化メニューを使用するかどうか
	private $currentUserLogined;	// 現在のユーザはログイン中かどうか
	private $menuData = array();			// Joomla用のメニューデータ
	private $menuTree = array();			// Joomla用のメニュー階層データ
	private $renderType;		// 描画出力タイプ
	private $showLogin;			// ログインフィールドを表示するかどうか
	private $loginFailed;		// ログイン失敗かどうか
	const DEFAULT_CONFIG_ID = 0;
	const MAX_MENU_TREE_LEVEL = 5;			// メニュー階層最大数
	const DEFAULT_BOOTSTRAP_CSS_FILE = '/bootstrap.css';		// CSSファイル
	const LOGIN_FAIL_MARK = '<i class="glyphicon glyphicon-remove-circle error" title="ログイン失敗" rel="tooltip" data-placement="auto"></i> ';		// ログイン失敗表示
	const LOGIN_FAIL_MESSAGE = '<p class="message error">ログインに失敗しました</p>';
	const TASK_REGIST = 'regist';		// 会員登録画面へのリンク用タスク
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->db = new default_menuDb();
		
		// 定義ID取得
		$configId = $this->gEnv->getCurrentWidgetConfigId();
		if (empty($configId)) $configId = self::DEFAULT_CONFIG_ID;
		
		// パラメータオブジェクトを取得
		$this->targetObj = $this->getWidgetParamObjByConfigId($configId);
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
		$this->renderType = 'JOOMLA_NEW';		// 描画出力タイプ
		$this->templateType = $this->gEnv->getCurrentTemplateType();
		$isNav = $this->isNavigationMenuStyle();		// ナビゲーションメニュータイプかどうか
		$useVerticalMenu 	= 0;// 縦型メニューデザインを使用するかどうか
		if (!empty($this->targetObj) && !empty($this->targetObj->useVerticalMenu)) $useVerticalMenu = 1;
		
		// 描画タイプを決める
		switch ($this->templateType){
			case 0:
				$this->renderType = 'JOOMLA_OLD';
				break;
			case 10:	// Bootstrap v3.0
			case 11:	// Bootstrap v4.0
				if ($isNav){
					$this->renderType = 'BOOTSTRAP_NAV';		// Boostrapナビゲーションメニュー
				} else {
					if ($useVerticalMenu){		// 縦型メニューの場合
						$this->renderType = 'BOOTSTRAP';
					} else {
						$this->renderType = 'JOOMLA_NEW';		// 描画出力タイプ
					}
				}
				break;
			default:
				$this->renderType = 'JOOMLA_NEW';		// 描画出力タイプ
				break;
		}
		
		$templateFile = '';
		switch ($this->renderType){
			case 'JOOMLA_NEW':
			default:
				$templateFile = 'index.tmpl.html';
				break;
			case 'JOOMLA_OLD':
				$templateFile = 'index_old.tmpl.html';
				break;
			case 'BOOTSTRAP_NAV':		// Bootstrapナビゲーションメニュー
				if ($this->templateType == 10){	// Bootstrap v3.0のとき
					$templateFile = 'index_bootstrap_nav.tmpl.html';
				} else {	// Bootstrap v4.0のとき
					$templateFile = 'index_bootstrap_nav4.tmpl.html';
				}
				
				// CSSファイルの追加
				$this->cssFilePath[] = $this->getUrl($this->gEnv->getCurrentWidgetCssUrl() . self::DEFAULT_BOOTSTRAP_CSS_FILE);		// CSSファイル
				break;
			case 'BOOTSTRAP':		// Bootstrapメニュー
				$templateFile = 'index_bootstrap.tmpl.html';
				break;
		}
		return $templateFile;
	}
	/**
	 * テンプレートにデータ埋め込む
	 *
	 * _setTemplate()で指定したテンプレートファイルにデータを埋め込む。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。_setTemplate()と共有。
	 * @param								なし
	 */
	function _assign($request, &$param)
	{
		$this->langId = $this->gEnv->getCurrentLanguage();
		$this->currentUserLogined = $this->gEnv->isCurrentUserLogined();	// 現在のユーザはログイン中かどうか
		
//		// 定義ID取得
//		$configId = $this->gEnv->getCurrentWidgetConfigId();
//		if (empty($configId)) $configId = self::DEFAULT_CONFIG_ID;
//		
//		// パラメータオブジェクトを取得
//		$targetObj = $this->getWidgetParamObjByConfigId($configId);
		$targetObj = $this->targetObj;
		if (empty($targetObj)){		// 定義データが取得できないとき
			// 出力抑止
			$this->cancelParse();
			return;
		}
		
		$menuId		= $targetObj->menuId;	// メニューID
		$name		= $targetObj->name;// 定義名
//		$this->isHierMenu	= $targetObj->isHierMenu;		// 階層化メニューを使用するかどうか
		$limitUser			= $targetObj->limitUser;// ユーザを制限するかどうか
		$useVerticalMenu 	= $targetObj->useVerticalMenu;		// 縦型メニューデザインを使用するかどうか
		$showSitename	= isset($targetObj->showSitename) ? $targetObj->showSitename : 1;		// サイト名を表示するかどうか
		$showSearch		= isset($targetObj->showSearch) ? $targetObj->showSearch : 0;			// 検索フィールドを表示するかどうか
		$this->showLogin	= isset($targetObj->showLogin) ? $targetObj->showLogin : 0;			// ログインを表示するかどうか
		$showRegist		= isset($targetObj->showRegist) ? $targetObj->showRegist : 0;			// 会員登録を表示するかどうか
		$anotherColor	= isset($targetObj->anotherColor) ? $targetObj->anotherColor : 0;		// 色を変更するかどうか
		
		// 階層化メニューを使用するかどうか取得
		//$this->getMenuInfo($this->isHierMenu, $itemId, $row);
		$this->isHierMenu = $this->gSystem->isSiteMenuHier();
		
		$act = $request->trimValueOf('act');
		if ($act == 'nav_login'){			// ログインのとき
			// アカウント、パスワード取得
			$account = $request->trimValueOf('account');
			$password = $request->trimValueOf('password');
			$autoLogin = ($request->trimValueOf('autologin') == 'on') ? 1 : 0;		// 自動ログインを使用するかどうか
			
			// ユーザ認証
			if ($this->gAccess->userLoginByAccount($account, $password)){
				$userId = $this->gEnv->getCurrentUserId();
				
				// ### 自動ログインの処理 ###
				// 自動ログインしないに設定した場合は自動ログイン情報を削除
				$this->gAccess->userAutoLogin($userId, $autoLogin);
				
				// 画面を全体を再表示する
				$this->gPage->redirect($this->gEnv->getCurrentRequestUri());
				return;
			} else {		// ログイン失敗の場合
				// ログイン状態を削除
				$this->gAccess->userLogout();
				
				$this->loginFailed = true;		// ログイン失敗かどうか
//				$this->tmpl->setAttribute('login_status', 'visibility', 'visible');		// ログイン状況
//				$this->tmpl->addVar("login_status", "message", 'ログインに失敗しました');
			}
		} else if ($act == 'nav_logout'){			// ログアウトのとき
			$this->gAccess->userLogout();
			
			// 画面を全体を再表示する
			$this->gPage->redirect($this->gEnv->getCurrentRequestUri());
			return;
		}
		
		// 縦型メニューデザイン使用の場合はJoomla用パラメータを設定
		if (!empty($useVerticalMenu)) $this->gEnv->setCurrentWidgetJoomlaParam(array('moduleclass_sfx' => 'art-vmenu'));

		// ユーザ制限があるときはログイン時のみ表示
		if (!$limitUser || $this->currentUserLogined){
			// メニュー作成
			$this->menuData['path'] = array();
			$this->menuData['active_id'] = 0;
			$parentTree = array();			// 選択されている項目までの階層パス
			$menuHtml = $this->createMenu($menuId, 0, 0, $tmp, $parentTree);
			
			if (!empty($menuHtml)) $this->tmpl->addVar("_widget", "menu_html", $menuHtml);
			
			// Joomla用のメニュー階層データを設定
			$this->menuData['tree'] = $this->menuTree;
			$this->gEnv->setJoomlaMenuData($this->menuData);
			
			// Bootstrap用のデータを埋め込む
			if ($this->renderType == 'BOOTSTRAP_NAV'){
				$navbarOptionClass = array();// 追加クラス
				$menuAttr = $this->gEnv->getMenuAttr();

				// ログイン状態を取得
				$userName = $this->gEnv->getCurrentUserName();
					
				// サイト名の表示制御
				if (!$showSitename){
					if ($this->templateType == 10){	// Bootstrap v3.0のとき
						$sitenameOptionClass = ' visible-xs';			// モニタが最小サイズの場合のみ表示
					} else {		// Bootstrap v4.0のとき
						$sitenameOptionClass = ' d-lg-none';			// モニタが最小サイズの場合のみ表示
					}
					$this->tmpl->addVar("_widget", "sitename_option_class", $sitenameOptionClass);
				}
				// 検索フィールド表示制御
				if ($showSearch) $this->tmpl->setAttribute('show_search', 'visibility', 'visible');

				// 会員登録表示制御
				if ($showRegist){			// 会員登録を表示するかどうか
					if (empty($userName)){		// ログインしていない場合のみ表示
						$this->tmpl->setAttribute('show_regist', 'visibility', 'visible');
						
						// コンテンツタイプが「会員」のページを取得
						$linkUrl = $this->gPage->createContentPageUrl(M3_VIEW_TYPE_MEMBER, M3_REQUEST_PARAM_OPERATION_TASK . '=' . self::TASK_REGIST);
						$linkUrl = $this->getUrl($linkUrl, true/*リンク用*/);
						$this->tmpl->addVar("show_regist", "url", $this->convertUrlToHtmlEntity($linkUrl));
						
						// 選択状況のチェック
						if ($this->checkMenuItemUrl($linkUrl)) $this->tmpl->addVar("show_regist", "CLASS", ' class="active"');
					}
				}
				// ログインフィールド表示制御
				if ($this->showLogin){
					if (empty($userName)){		// ユーザがログインしていないとき
						$this->tmpl->setAttribute('show_login', 'visibility', 'visible');
						
						// ログイン失敗の表示
						if ($this->loginFailed){
							$this->tmpl->addVar("show_login", "login_status", self::LOGIN_FAIL_MARK);		// アイコン
							$this->tmpl->addVar("show_login", "message", self::LOGIN_FAIL_MESSAGE);		// メッセージ
						}
					} else {
						$this->tmpl->setAttribute('show_logout', 'visibility', 'visible');
						$this->tmpl->addVar("show_logout", "account", $this->convertToDispString($userName));
					}
					// ログイン用スクリプト追加
					$this->tmpl->setAttribute('show_login_script', 'visibility', 'visible');
				}
				
				// 追加クラス
				// メニュー属性を取得
				if (!empty($menuAttr['bootstyle'])) $navbarOptionClass[] = $menuAttr['bootstyle'];
				
				// メニューバーの色を設定
				if ($this->templateType == 10){	// Bootstrap v3.0のとき
					if ($anotherColor) $navbarOptionClass[] = 'navbar-inverse';
				} else {		// Bootstrap v4.0のとき
					// 折り返しポイントを設定。サイト名の表示制御「d-lg-none」と連携。
					$navbarOptionClass[] = 'navbar-expand-lg';
					
					if ($anotherColor){		// 別の色を指定の場合
						$navbarOptionClass[] = 'navbar-dark';
						$navbarOptionClass[] = 'bg-primary';
					} else {			// デフォルトカラー
					//	$navbarOptionClass[] = 'navbar-light';
					//	$navbarOptionClass[] = 'bg-light';

						
						$navbarOptionClass[] = 'navbar-dark';
						$navbarOptionClass[] = 'bg-dark';
					}
				}

				// データを画面に埋め込む
				if (!empty($navbarOptionClass)) $this->tmpl->addVar("_widget", "navbar_option_class", ' ' . implode(' ', $navbarOptionClass));
				$this->tmpl->addVar("_widget", "site_url", $this->convertUrlToHtmlEntity($this->gEnv->getRootUrl() . '/'));
				$this->tmpl->addVar("_widget", "sitename", $this->convertToDispString($this->gEnv->getSiteName()));
			}
		} else {
			// 出力抑止
			$this->cancelParse();
		}
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
		return $this->cssFilePath;
	}
	/**
	 * JavascriptライブラリをHTMLヘッダ部に設定
	 *
	 * JavascriptライブラリをHTMLのheadタグ内に追加出力する。
	 * _assign()よりも後に実行される。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。
	 * @return string,array 				Javascriptライブラリ。出力しない場合は空文字列を設定。
	 */
	function _addScriptLibToHead($request, &$param)
	{
		if ($this->showLogin){			// ログインフィールドを表示する場合
			return array(ScriptLibInfo::LIB_MD5, ScriptLibInfo::LIB_JQUERY_COOKIE);
		} else {
			return '';
		}
	}
	/**
	 * メニューツリー作成
	 *
	 * @param string	$menuId		メニューID
	 * @param int		$parentId	親メニュー項目ID
	 * @param int		$level		階層数
	 * @param bool		$hasSelectedChild	現在選択状態の子項目があるかどうか
	 * @param array     $parentTree	現在の階層パス
	 * @return string				ツリーメニュータグ
	 */
	function createMenu($menuId, $parentId, $level, &$hasSelectedChild, &$parentTree)
	{
		static $index = 0;		// インデックス番号
		$hasSelectedChild = false;

		// メニューの階層を制限
		if ($level >= self::MAX_MENU_TREE_LEVEL) return '';
		
		$treeHtml = '';
		if ($this->db->getChildMenuItems($menuId, $parentId, $this->langId, $rows)){
			$itemCount = count($rows);
			for ($i = 0; $i < $itemCount; $i++){
				$row = $rows[$i];
				$classArray = array();		// 項目のクラス
				$linkClassArray = array();	// リンクタグのクラス
				$attr = '';
				// Joomla用メニューデータ(デフォルト値)
				$menuItem = new stdClass;		// Joomla用メニューデータ
				$menuItem->type = 'alias';		// 内部リンク。外部リンク(url)
				$menuItem->id = $index + 1;
				$menuItem->level = $level + 1;
				$menuItem->active = false;
				$menuItem->parent = false;
				// 階層作成用
				$menuItem->deeper = false;
				$menuItem->shallower = false;
				$menuItem->level_diff = 0;
				$menuTreeCount = count($this->menuTree);
				if ($menuTreeCount > 0){		// 前データがあるときは取得
					$menuLastItem = $this->menuTree[$menuTreeCount -1];
					$menuLastItem->deeper = ($menuItem->level > $menuLastItem->level);
					$menuLastItem->shallower = ($menuItem->level < $menuLastItem->level);
					$menuLastItem->level_diff = $menuLastItem->level - $menuItem->level;
				}
									
				// 非表示のときは処理を飛ばす
				if (!$row['md_visible']) continue;
				
				// ユーザ制限がある場合はログイン状態をチェック
				if ($row['md_user_limited'] && !$this->currentUserLogined) continue;
		
				// リンク先のコンテンツの表示状況に合わせる
				if ($row['md_content_type'] == M3_VIEW_TYPE_CONTENT){		// 汎用コンテンツの場合
					// ログインユーザに表示制限されている場合はメニューを追加しない
					if (!empty($row['cn_user_limited']) && !$this->currentUserLogined) continue;
				}
						
				// Joomla1.0対応
				if ($this->renderType == 'JOOMLA_OLD') $linkClassArray[] = 'mainlevel';
				
				// リンク先の作成
				$linkUrl = $row['md_link_url'];
				$linkUrl = $this->getUrl($linkUrl, true/*リンク用*/);
				if (empty($linkUrl)) $linkUrl = '#';
				
				// 選択状態の設定
				if ($this->checkMenuItemUrl($linkUrl)){
					$attr = ' id="current"';		// メニュー項目を選択状態にする
					$classArray[] = 'active';
					$hasSelectedChild = true;
					
					// Joomla用メニュー階層データ
					$pathTree = $parentTree;			// パスを取得
					$pathTree[] = $index + 1;
					$this->menuData['path'] = $pathTree;
					$this->menuData['active_id'] = $index + 1;
					$menuItem->active = true;
				}
				
				// リンクタイプに合わせてタグを生成
				// ### メニューがナビゲーションタイプの場合はテンプレートタイプに合わせたクラスを出力する。ナビゲーションタイプでない場合はプレーンなUL,LIタグを出力する。###
				if ($this->renderType == 'BOOTSTRAP_NAV'){// Bootstrapナビゲーションメニューのとき
					if ($this->templateType == 11){// Bootstrap v4.0のとき
						if ($level == 0){		// 1階層目の場合
							array_unshift($linkClassArray, 'nav-link');
						} else {			// 2階層目以下の場合
							array_unshift($linkClassArray, 'dropdown-item');
						}
					}
				}
				$linkOption = '';
				if (count($linkClassArray) > 0) $linkOption .= 'class="' . implode(' ', $linkClassArray) . '"';
				switch ($row['md_link_type']){
					case 0:			// 同ウィンドウで開くリンク
					default:
						$menuItem->browserNav = 0;		// ウィンドウオープン方法(0=同じウィンドウ、1=別タブ、2=別ウィンドウ)
						break;
					case 1:			// 別ウィンドウで開くリンク
						$linkOption .= ' target="_blank"';
						$menuItem->browserNav = 1;		// ウィンドウオープン方法(0=同じウィンドウ、1=別タブ、2=別ウィンドウ)
						break;
				}
				
				// メニュー項目を作成
				//$name = $row['md_name'];
				$name = $this->getCurrentLangString($row['md_name']);
				//if (empty($name)) continue;
				$title = $this->getCurrentLangString($row['md_title']);		// タイトル(HTML可)
				if (empty($title)) $title = $name;
				if (empty($title)) continue;
				
				// メニュータイトルの処理。タグが含まれていない場合は文字をエスケープする。
				$stripName = strip_tags($title);
				if (strlen($stripName) == strlen($title)) $title = $this->convertToDispString($title);		// 文字列長が同じとき
				
				$index++;		// インデックス番号更新
								
				switch ($row['md_type']){
					case 0:			// リンク項目のとき
						// Joomla用メニューデータ作成
						//$menuItem->title = $name;
						$menuItem->title = $title;
						$menuItem->flink = $linkUrl;
						
						// ##### Joomla用メニュー階層更新 #####
						$this->menuTree[] = $menuItem;
						
						// ##### タグ作成 #####
						// ### メニューがナビゲーションタイプの場合はテンプレートタイプに合わせたクラスを出力する。ナビゲーションタイプでない場合はプレーンなUL,LIタグを出力する。###
						if ($this->renderType == 'BOOTSTRAP_NAV'){// Bootstrapナビゲーションメニューのとき
							if ($this->templateType == 11){				// Bootstrap v4.0のとき
								if ($level == 0) array_unshift($classArray, 'nav-item');		// 1階層目の場合
							}
						}
				
						if (count($classArray) > 0) $attr .= ' class="' . implode(' ', $classArray) . '"';
						$treeHtml .= '<li' . $attr . '><a href="' . $this->convertUrlToHtmlEntity($linkUrl) . '" ' . $linkOption . '><span>' . $title . '</span></a></li>' . M3_NL;
						break;
					case 1:			// フォルダのとき
						if (!empty($this->isHierMenu)){	// 階層化メニューを使用する場合
							// Joomla用メニューデータ作成
							//$menuItem->title = $name;
							$menuItem->title = $title;
							$menuItem->flink = $linkUrl;
							$menuItem->parent = true;
							// 階層作成用
							//$menuItem->deeper = true;
							//$menuItem->level_diff = 1;

							// ##### Joomla用メニュー階層更新 #####
							$this->menuTree[] = $menuItem;
						
							// 階層を更新
							//array_push($parentTree, $index + 1);
							array_push($parentTree, $index);
							
							// サブメニュー作成
							$menuText = $this->createMenu($menuId, $row['md_id'], $level + 1, $hasSelectedChildSub, $parentTree);
							if ($hasSelectedChildSub) $hasSelectedChild = true;			// さらに子孫が選択状態にあるときは現在の位置の状態を更新
							
							// 階層を戻す
							array_pop($parentTree);
							
							// 子項目が選択中のときは「active」に設定
							if ($hasSelectedChildSub) $classArray[] = 'active';

							// 先頭に「parent」クラスを追加
							array_unshift($classArray, 'parent');
							
							// ##### タグ作成 #####
							// ### メニューがナビゲーションタイプの場合はテンプレートタイプに合わせたクラスを出力する。ナビゲーションタイプでない場合はプレーンなUL,LIタグを出力する。###
							if ($this->renderType == 'BOOTSTRAP_NAV'){// Bootstrapナビゲーションメニューのとき
								$dropDownCaret = '';
								
								if ($this->templateType == 10){	// Bootstrap v3.0のとき
									if ($level == 0){
										$dropDownCaret = ' <b class="caret"></b>';
									} else {
										$classArray[] = 'dropdown-submenu';
									}
								} else {		// Bootstrap v4.0のとき
									if ($level == 0){		// 1階層目の場合
										array_unshift($classArray, 'nav-item dropdown');
									} else {			// 2階層目以下の場合
										array_unshift($classArray, 'dropdown-submenu');
									}
								}
								
								$linkClassArray[] = 'dropdown-toggle';
								
								// 子項目が選択中のときは「active」に設定
								if ($hasSelectedChildSub) $linkClassArray[] = 'active';

								$linkOption = '';
								if (count($linkClassArray) > 0) $linkOption .= 'class="' . implode(' ', $linkClassArray) . '"';
									
								if (count($classArray) > 0) $attr .= ' class="' . implode(' ', $classArray) . '"';
								$treeHtml .= '<li' . $attr . '><a href="' . $this->convertUrlToHtmlEntity($linkUrl) . '" ' . $linkOption . ' data-toggle="dropdown"><span>' . $title . $dropDownCaret . '</span></a>' . M3_NL;
								if (!empty($menuText)){
									$treeHtml .= '<ul class="dropdown-menu">' . M3_NL;
									$treeHtml .= $menuText;
									$treeHtml .= '</ul>' . M3_NL;
								}
								$treeHtml .= '</li>' . M3_NL;
							} else {
								if (count($classArray) > 0) $attr .= ' class="' . implode(' ', $classArray) . '"';
								$treeHtml .= '<li' . $attr . '><a href="' . $this->convertUrlToHtmlEntity($linkUrl) . '"><span>' . $title . '</span></a>' . M3_NL;
								if (!empty($menuText)){
									$treeHtml .= '<ul>' . M3_NL;
									$treeHtml .= $menuText;
									$treeHtml .= '</ul>' . M3_NL;
								}
								$treeHtml .= '</li>' . M3_NL;
							}
						}
						break;
					case 2:			// テキストのとき
						$treeHtml .= '<li><span>' . $title . '</span></li>' . M3_NL;
						break;
					case 3:			// セパレータのとき
						// Joomla用メニューデータ作成
						$menuItem->type = 'separator';
						$menuItem->title = $title;
						$menuItem->flink = '';
						
						// ##### Joomla用メニュー階層更新 #####
						$this->menuTree[] = $menuItem;
						
						// ##### タグ作成 #####
						if ($this->renderType == 'BOOTSTRAP_NAV' || $this->renderType == 'BOOTSTRAP'){// Bootstrapメニューのとき
							$treeHtml .= '<li class="divider"></li>' . M3_NL;
						} else {
							$treeHtml .= '<li><span class="separator">' . $title . '</span></li>' . M3_NL;
						}
						break;
				}
				
				if ($this->renderType == 'JOOMLA_OLD'){			// Joomla!v1.0のとき
					$itemRow = array(
						'link_url' => $this->convertUrlToHtmlEntity($linkUrl),		// リンク
						//'name' => $this->convertToDispString($name),			// タイトル
						'name' => $title,			// タイトル
						'attr' => $attr,			// liタグ追加属性
						'option' => $linkOption			// Aタグ追加属性
					);
					$this->tmpl->addVars('itemlist', $itemRow);
					$this->tmpl->parseTemplate('itemlist', 'a');
				}
				$menuTreeCount = count($this->menuTree);
				if ($menuTreeCount > 0){		// 前データがあるときは取得
					$menuLastItem = $this->menuTree[$menuTreeCount -1];
					$menuLastItem->deeper = (1 > $menuLastItem->level);
					$menuLastItem->shallower = (1 < $menuLastItem->level);
					$menuLastItem->level_diff = $menuLastItem->level - 1;
				}
			}
		}
		return $treeHtml;
	}
	/**
	 * メニュー項目の選択条件をチェック
	 *
	 * @param string $url	チェック対象のURL
	 * @return bool			true=アクティブ、false=非アクティブ
	 */
	function checkMenuItemUrl($url)
	{
		$currentUrl = $this->gEnv->getCurrentRequestUri();
		
		// 同じURLのとき
		if ($url == $currentUrl) return true;
		
		// URLを解析
		$queryArray = array();
		$parsedUrl = parse_url($url);
		if (!empty($parsedUrl['query'])) parse_str($parsedUrl['query'], $queryArray);		// クエリーの解析

		// ルートかどうかチェック(クエリー文字列なし)
		if ($this->isRootUrl($url)){
			$parsedUrl = parse_url($currentUrl);
			if (empty($parsedUrl['query'])){		// クエリ文字列がないことが条件。「#」はあっても良い。
				// ページサブIDで比較
				if ($this->gEnv->getCurrentPageSubId() == $this->gEnv->getDefaultPageSubId()) return true;
			}
		}
		
		// パラメータがサブページIDだけの場合はページサブIDで比較
		if (count($queryArray) == 1 && isset($queryArray[M3_REQUEST_PARAM_PAGE_SUB_ID])){
			if ($this->gEnv->getCurrentPageSubId() == $queryArray[M3_REQUEST_PARAM_PAGE_SUB_ID]) return true;
		}
		return false;
	}
	/**
	 * URLがルートを指しているかどうか取得
	 *
	 * @param string $url	チェック対象のURL
	 * @return bool			true=ルート、false=ルート以外
	 */
	function isRootUrl($url)
	{
		$url = str_replace('https://', 'http://', $url);		// 一旦httpに統一
		$systemUrl = str_replace('https://', 'http://', $this->gEnv->getRootUrl());		// 一旦httpに統一
		$systemSslUrl = str_replace('https://', 'http://', $this->gEnv->getSslRootUrl());		// 一旦httpに統一

		$parsedUrl = parse_url($url);
		if (empty($parsedUrl['query'])){		// クエリ文字列がないことが条件。「#」はあっても良い。
			// パスを解析
			$relativePath = str_replace($systemUrl, '', $url);		// ルートURLからの相対パスを取得
			if (empty($relativePath)){			// Magic3のルートURLの場合
				return true;
			} else if (strStartsWith($relativePath, '/') || strStartsWith($relativePath, '/' . M3_FILENAME_INDEX)){		// ルートURL配下のとき
				return true;
			} else {		// ルートURL以外のURLのとき(SSL用のURL以下かどうかチェック)
				$relativePath = str_replace($systemSslUrl, '', $url);		// ルートURLからの相対パスを取得
				if (empty($relativePath)){			// Magic3のルートURLの場合
					return true;
				} else if (strStartsWith($relativePath, '/') || strStartsWith($relativePath, '/' . M3_FILENAME_INDEX)){		// ルートURL配下のとき
					return true;
				}
			}
		}
		return false;
	}
}
?>
