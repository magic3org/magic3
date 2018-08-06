<?php
/**
 * コンテナクラス
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
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/admin_mainBaseWidgetContainer.php');

class admin_mainMainteBaseWidgetContainer extends admin_mainBaseWidgetContainer
{
	const BREADCRUMB_TITLE	= 'メンテナンス';		// パンくずリストトップタイトル
	// 画面
//	const TASK_RESBROWSE 		= 'resbrowse';		// ファイルブラウザ
	const TASK_FILEBROWSE 		= 'filebrowse';		// ファイルブラウザ
	const TASK_PAGEINFO			= 'pageinfo';	// ページ情報
	const TASK_PAGEINFO_DETAIL	= 'pageinfo_detail';	// ページ情報
	const TASK_ACCESSPOINT			= 'accesspoint';		// アクセスポイント
	const TASK_ACCESSPOINT_DETAIL	= 'accesspoint_detail';		// アクセスポイント
	const TASK_PAGEID			= 'pageid';		// ページID
	const TASK_PAGEID_DETAIL	= 'pageid_detail';		// ページID
	const TASK_MENUID			= 'menuid';		// メニューID
	const TASK_MENUID_DETAIL	= 'menuid_detail';		// メニューID
	const TASK_INITSYSTEM		= 'initsystem';		// DBメンテナンス
	const TASK_INSTALLDATA		= 'installdata';	// データインストール
	const TASK_DBBACKUP			= 'dbbackup';		// DBバックアップ
//	const TASK_DBCONDITION		= 'dbcondition';	// DB状況
	const TASK_INITWIZARD		= 'initwizard';		// 管理画面カスタムウィザード
	const TASK_EDITMENU			= 'editmenu';		// 管理メニュー編集
	const TASK_MAIN				= 'mainte';			// 全体(メンテナンス)
//	const DEFAULT_TASK			= 'resbrowse';		// デフォルト(ファイルブラウザ)
	const DEFAULT_TASK			= 'filebrowse';		// デフォルト(ファイルブラウザ)
	
	const TASK_NAME_MAIN = 'メンテナンス';
//	const HELP_KEY_RESBROWSE	= 'resbrowse';		// ファイルブラウザ
	const HELP_KEY_PAGEINFO		= 'pageinfo';
	const HELP_KEY_PAGEID		= 'pageid';
	const HELP_KEY_MENUID		= 'menuid';
	const HELP_KEY_INITSYSTEM	= 'initsystem';		// DBデータ初期化
	const HELP_KEY_DBBACKUP		= 'dbbackup';		// DBバックアップ
	const HELP_KEY_DBCONDITION	= 'dbcondition';	// DB状況
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
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
	function _postAssign($request, &$param)
	{
		$task = $request->trimValueOf(M3_REQUEST_PARAM_OPERATION_TASK);
		if ($task == self::TASK_MAIN){		// トップページ指定の場合はデフォルトページへリダイレクト
			//$task = self::DEFAULT_TASK;
		
			$mainteTopPage = $this->gEnv->getDefaultAdminUrl() . '?task=' . self::DEFAULT_TASK;
			$this->gPage->redirect($this->getUrl($mainteTopPage));
			return;
		}
							
		// パンくずリストの作成
		$titles = array();
		$titles[] = self::BREADCRUMB_TITLE;
		switch ($task){
			case self::TASK_FILEBROWSE:		// ファイルブラウザ
				$titles[] = 'ファイル管理';
				$titles[] = 'ファイルブラウザ';
				break;
			case self::TASK_PAGEINFO:	// ページ情報
				$titles[] = 'マスター管理';
				$titles[] = 'ページ情報';
				break;
			case self::TASK_PAGEINFO_DETAIL:	// ページ情報
				$titles[] = 'マスター管理';
				$titles[] = 'ページ情報';
				$titles[] = '詳細';
				break;
			case self::TASK_ACCESSPOINT:		// アクセスポイント
				$titles[] = 'マスター管理';
				$titles[] = 'アクセスポイント';
				break;
			case self::TASK_ACCESSPOINT_DETAIL:		// アクセスポイント
				$titles[] = 'マスター管理';
				$titles[] = 'アクセスポイント';
				$titles[] = '詳細';
				break;
			case self::TASK_PAGEID:	// ページID
				$titles[] = 'マスター管理';
				$titles[] = 'ページID';
				break;
			case self::TASK_PAGEID_DETAIL:		// ページID
				$titles[] = 'マスター管理';
				$titles[] = 'ページID';
				$titles[] = '詳細';
				break;
			case self::TASK_MENUID:		// メニューID
				$titles[] = 'マスター管理';
				$titles[] = 'メニューID';
				break;
			case self::TASK_MENUID_DETAIL:		// メニューID
				$titles[] = 'マスター管理';
				$titles[] = 'メニューID';
				$titles[] = '詳細';
				break;
			case self::TASK_TASKACCESS:						// 管理画面アクセス制御
				$titles[] = 'マスター管理';
				$titles[] = 'アクセス制御';
				break;
			case self::TASK_INITSYSTEM:		// DBメンテナンス
				$titles[] = 'DB管理';
				$titles[] = 'システム再インストール';
				break;
			case self::TASK_INSTALLDATA:	// データインストール
				$titles[] = 'DB管理';
				$titles[] = 'データインストール';
				break;
			case self::TASK_DBBACKUP:		// DBバックアップ
				$titles[] = 'DB管理';
				$titles[] = 'バックアップ';
				break;
			case self::TASK_DB_ACCESSLOG:	// DB管理アクセスログ
				$titles[] = 'DB管理';
				$titles[] = 'アクセスログ';
				break;
			case self::TASK_INITWIZARD:		// 管理画面カスタムウィザード
				$titles[] = '管理画面設定';
				$titles[] = '管理画面カスタムウィザード';
				break;
			case self::TASK_EDITMENU:		// 管理メニュー編集
				$titles[] = '管理画面設定';
				$titles[] = '管理メニュー編集';
				break;
		}
		$this->gPage->setAdminBreadcrumbDef($titles);

		// メニューバーの作成
		$navbarDef = new stdClass;
//		$navbarDef->title = $this->gEnv->getCurrentWidgetTitle();		// ウィジェット名
		$navbarDef->baseurl = $this->getAdminUrlWithOptionParam();
		$navbarDef->help	= '';// ヘルプ文字列
		$navbarDef->menu =	array(
								(Object)array(
									'name'		=> 'ファイル管理',
									'task'		=> '',
									'url'		=> '',
									'tagid'		=> '',
									'active'	=> (
														$task == self::TASK_FILEBROWSE		// ファイルブラウザ
													),
									'help'		=> $this->gInstance->getHelpManager()->getHelpText(self::TASK_FILEBROWSE),// ヘルプ文字列
									'submenu'	=> array(
										(Object)array(
											'name'		=> 'ファイルブラウザ',
											'task'		=> self::TASK_FILEBROWSE,
											'url'		=> '',
											'tagid'		=> '',
											'active'	=> (
																$task == self::TASK_FILEBROWSE		// ファイルブラウザ
															),
											'help'		=> $this->gInstance->getHelpManager()->getHelpText(self::TASK_FILEBROWSE),// ヘルプ文字列
										)
									)
								),
								(Object)array(
									'name'		=> 'マスター管理',
									'task'		=> '',
									'url'		=> '',
									'tagid'		=> '',
									'active'	=> (
														$task == self::TASK_PAGEINFO ||			// ページ情報
														$task == self::TASK_PAGEINFO_DETAIL ||	// ページ情報詳細
														$task == self::TASK_ACCESSPOINT ||			// アクセスポイント
														$task == self::TASK_ACCESSPOINT_DETAIL ||	// アクセスポイント
														$task == self::TASK_PAGEID ||			// ページID
														$task == self::TASK_PAGEID_DETAIL ||	// ページID詳細
														$task == self::TASK_MENUID ||			// メニューID
														$task == self::TASK_MENUID_DETAIL ||		// メニューID詳細
														$task == self::TASK_TASKACCESS				// 管理画面アクセス制御
													),
									'submenu'	=> array(
										(Object)array(
											'name'		=> 'ページ情報',
											'task'		=> self::TASK_PAGEINFO,
											'url'		=> '',
											'tagid'		=> '',
											'active'	=> (
																$task == self::TASK_PAGEINFO ||			// ページ情報
																$task == self::TASK_PAGEINFO_DETAIL	// ページ情報詳細
															)
										),
										(Object)array(
											'name'		=> 'アクセスポイント',
											'task'		=> self::TASK_ACCESSPOINT,
											'url'		=> '',
											'tagid'		=> '',
											'active'	=> (
																$task == self::TASK_ACCESSPOINT ||			// アクセスポイント
																$task == self::TASK_ACCESSPOINT_DETAIL		// アクセスポイント詳細
															)
										),
										(Object)array(
											'name'		=> 'ページID',
											'task'		=> self::TASK_PAGEID,
											'url'		=> '',
											'tagid'		=> '',
											'active'	=> (
																$task == self::TASK_PAGEID ||			// ページID
																$task == self::TASK_PAGEID_DETAIL	// ページID詳細
															)
										),
										(Object)array(
											'name'		=> 'メニューID',
											'task'		=> self::TASK_MENUID,
											'url'		=> '',
											'tagid'		=> '',
											'active'	=> (
																$task == self::TASK_MENUID ||			// メニューID
																$task == self::TASK_MENUID_DETAIL		// メニューID詳細
															)
										),
										(Object)array(
											'name'		=> 'アクセス制御',
											'task'		=> self::TASK_TASKACCESS,
											'url'		=> '',
											'tagid'		=> '',
											'active'	=> (
																$task == self::TASK_TASKACCESS			// 管理画面アクセス制御
															)
										)
									)
								),
								(Object)array(
									'name'		=> 'DB管理',
									'task'		=> '',
									'url'		=> '',
									'tagid'		=> '',
									'active'	=> (
														$task == self::TASK_INSTALLDATA ||	// データインストール
														$task == self::TASK_DBBACKUP ||			// DBバックアップ
														$task == self::TASK_DB_ACCESSLOG			// DB管理アクセスログ
													),
									'submenu'	=> array(
										(Object)array(
											'name'		=> 'データインストール',
											'task'		=> self::TASK_INSTALLDATA,
											'url'		=> '',
											'tagid'		=> '',
											'active'	=> (
																$task == self::TASK_INSTALLDATA		// データインストール
															)
										),
										(Object)array(
											'name'		=> 'バックアップ',
											'task'		=> self::TASK_DBBACKUP,
											'url'		=> '',
											'tagid'		=> '',
											'active'	=> (
																$task == self::TASK_DBBACKUP		// DBバックアップ
															)
										),
										(Object)array(
											'name'		=> 'アクセスログ',
											'task'		=> self::TASK_DB_ACCESSLOG,
											'url'		=> '',
											'tagid'		=> '',
											'active'	=> (
																$task == self::TASK_DB_ACCESSLOG		// DB管理アクセスログ
															)
										)
									)
								)
							);
							
		// 設定によって「管理画面設定」は変更
		if (M3_PERMIT_REINSTALL){			// 再インストール可能な場合は「システム再インストール」項目を表示
			$navbarDef->menu[] =	(Object)array(
										'name'		=> '管理画面設定',
										'task'		=> '',
										'url'		=> '',
										'tagid'		=> '',
										'active'	=> (
															$task == self::TASK_INITWIZARD ||		// 管理画面カスタムウィザード
															$task == self::TASK_EDITMENU ||			// 管理メニュー編集
															$task == self::TASK_INITSYSTEM			// システム再インストール
														),
										'submenu'	=> array(
											(Object)array(
												'name'		=> '管理画面カスタムウィザード',
												'task'		=> self::TASK_INITWIZARD,
												'url'		=> '',
												'tagid'		=> '',
												'active'	=> (
																	$task == self::TASK_INITWIZARD		// 管理画面カスタムウィザード
																)
											),
											(Object)array(
												'name'		=> '管理メニュー編集',
												'task'		=> self::TASK_EDITMENU,
												'url'		=> '',
												'tagid'		=> '',
												'active'	=> (
																	$task == self::TASK_EDITMENU		// 管理メニュー編集
																)
											),
											(Object)array(
												'name'		=> 'システム再インストール',
												'task'		=> self::TASK_INITSYSTEM,
												'url'		=> '',
												'tagid'		=> '',
												'active'	=> (
																	$task == self::TASK_INITSYSTEM		// システム再インストール
																)
											),
										)
									);
		} else {
			$navbarDef->menu[] =	(Object)array(
										'name'		=> '管理画面設定',
										'task'		=> '',
										'url'		=> '',
										'tagid'		=> '',
										'active'	=> (
															$task == self::TASK_INITWIZARD ||		// 管理画面カスタムウィザード
															$task == self::TASK_EDITMENU			// 管理メニュー編集
														),
										'submenu'	=> array(
											(Object)array(
												'name'		=> '管理画面カスタムウィザード',
												'task'		=> self::TASK_INITWIZARD,
												'url'		=> '',
												'tagid'		=> '',
												'active'	=> (
																	$task == self::TASK_INITWIZARD		// 管理画面カスタムウィザード
																)
											),
											(Object)array(
												'name'		=> '管理メニュー編集',
												'task'		=> self::TASK_EDITMENU,
												'url'		=> '',
												'tagid'		=> '',
												'active'	=> (
																	$task == self::TASK_EDITMENU		// 管理メニュー編集
																)
											)
										)
									);
		}
		$this->gPage->setAdminSubNavbarDef($navbarDef);
		
/*
		// パンくずリストを作成
		switch ($task){
			case self::TASK_FILEBROWSE:		// ファイルブラウザ
				$linkList = ' ファイル管理 &gt;&gt; ファイルブラウザ';
				break;	
			case self::TASK_PAGEINFO:	// ページ情報一覧
			case self::TASK_PAGEINFO_DETAIL:	// ページ情報詳細
				$linkList = ' マスター管理 &gt;&gt; ページ情報';
				break;
			case self::TASK_PAGEID:	// ページID一覧
			case self::TASK_PAGEID_DETAIL:	// ページID詳細
				$linkList = ' マスター管理 &gt;&gt; ページID';
				break;
			case self::TASK_MENUID:		// メニューID
			case self::TASK_MENUID_DETAIL:		// メニューID
				$linkList = ' マスター管理 &gt;&gt; メニューID';
				break;
			case self::TASK_INITSYSTEM:		// DBデータ初期化
				$linkList = ' DB管理 &gt;&gt; データ初期化';
				break;
			case self::TASK_DBBACKUP:		// DBバックアップ
				$linkList = ' DB管理 &gt;&gt; バックアップ';
				break;
			case self::TASK_DBCONDITION:		// DB状況
				$linkList = ' DB管理 &gt;&gt; 状況';
				break;
		}
		// ####### 上段メニューの作成 #######
		$menuText = '<div id="configmenu-upper">' . M3_NL;
		$menuText .= '<ul>' . M3_NL;
		
		$current = '';
		$baseUrl = $this->gEnv->getDefaultAdminUrl();
		
		// ファイル管理
		$current = '';
		$link = $baseUrl . '?task=' . self::TASK_FILEBROWSE;
		if ($task == self::TASK_FILEBROWSE){		// ファイルブラウザ
			$current = 'id="current"';
		}
		$menuText .= '<li ' . $current . '><a href="'. $this->getUrl($link) .'"><span>ファイル管理</span></a></li>' . M3_NL;
		
		// マスター管理
		$current = '';
		$link = $baseUrl . '?task=' . self::TASK_PAGEINFO;		// ページ情報一覧
		if ($task == self::TASK_PAGEINFO ||						// ページ情報一覧
			$task == self::TASK_PAGEINFO_DETAIL ||				// ページ情報詳細
			$task == self::TASK_PAGEID ||						// ページID一覧
			$task == self::TASK_PAGEID_DETAIL ||				// ページID詳細
			$task == self::TASK_MENUID ||						// メニューID一覧
			$task == self::TASK_MENUID_DETAIL){					// メニューID詳細
			$current = 'id="current"';
		}
		$menuText .= '<li ' . $current . '><a href="'. $this->getUrl($link) .'"><span>マスター管理</span></a></li>' . M3_NL;
		
		// DB管理
		$current = '';
		$link = $baseUrl . '?task=' . self::TASK_INITSYSTEM;		// DBデータ初期化
		if ($task == self::TASK_INITSYSTEM ||		// DBデータ初期化
			$task == self::TASK_DBBACKUP ||		// DBバックアップ
			$task == self::TASK_DBCONDITION){		// DB状況
			$current = 'id="current"';
		}
		$menuText .= '<li ' . $current . '><a href="'. $this->getUrl($link) .'"><span>DB管理</span></a></li>' . M3_NL;
		
		// 上段メニュー終了
		$menuText .= '</ul>' . M3_NL;
		$menuText .= '</div>' . M3_NL;
		
		// ####### 下段メニューの作成 #######
		$menuText .= '<div id="configmenu-lower">' . M3_NL;
		$menuText .= '<ul>' . M3_NL;
				
		if ($task == self::TASK_FILEBROWSE){		// ファイルブラウザ
			// ### ファイルブラウザ ###
			$current = '';
			$link = $baseUrl . '?task=' . self::TASK_FILEBROWSE;
			if ($task == self::TASK_FILEBROWSE) $current = 'id="current"';

			// ヘルプを作成
			$helpText = $this->gInstance->getHelpManager()->getHelpText(self::HELP_KEY_RESBROWSE);
			$menuText .= '<li ' . $current . '><a href="'. $this->getUrl($link) .'"><span ' . $helpText . '>ファイルブラウザ</span></a></li>' . M3_NL;
		} else if ($task == self::TASK_PAGEINFO ||						// ページ情報一覧
			$task == self::TASK_PAGEINFO_DETAIL ||				// ページ情報詳細
			$task == self::TASK_PAGEID ||						// ページID一覧
			$task == self::TASK_PAGEID_DETAIL ||				// ページID詳細
			$task == self::TASK_MENUID ||						// メニューID一覧
			$task == self::TASK_MENUID_DETAIL){					// メニューID詳細
			
			// ### ページ情報 ###
			$current = '';
			$link = $baseUrl . '?task=pageinfo';
			if ($task == self::TASK_PAGEINFO || $task == self::TASK_PAGEINFO_DETAIL){
				$current = 'id="current"';
			}
			// ヘルプを作成
			$helpText = $this->gInstance->getHelpManager()->getHelpText(self::HELP_KEY_PAGEINFO);
			$menuText .= '<li ' . $current . '><a href="'. $this->getUrl($link) .'"><span ' . $helpText . '>ページ情報</span></a></li>' . M3_NL;
		
			// ### ページID ###
			$current = '';
			$link = $baseUrl . '?task=pageid';
			if ($task == self::TASK_PAGEID || $task == self::TASK_PAGEID_DETAIL){
				$current = 'id="current"';
			}
			// ヘルプを作成
			$helpText = $this->gInstance->getHelpManager()->getHelpText(self::HELP_KEY_PAGEID);
			$menuText .= '<li ' . $current . '><a href="'. $this->getUrl($link) .'"><span ' . $helpText . '>ページID</span></a></li>' . M3_NL;
		
			// ### メニューID ###
			$current = '';
			$link = $baseUrl . '?task=menuid';
			if ($task == self::TASK_MENUID || $task == self::TASK_MENUID_DETAIL){
				$current = 'id="current"';
			}
			// ヘルプを作成
			$helpText = $this->gInstance->getHelpManager()->getHelpText(self::HELP_KEY_MENUID);
			$menuText .= '<li ' . $current . '><a href="'. $this->getUrl($link) .'"><span ' . $helpText . '>メニューID</span></a></li>' . M3_NL;
		} else if ($task == self::TASK_INITSYSTEM || 	// DBデータ初期化
					$task == self::TASK_DBBACKUP ||		// DBバックアップ
					$task == self::TASK_DBCONDITION){		// DB状況
			// ### DBデータ初期化 ###
			$current = '';
			$link = $baseUrl . '?task=' . self::TASK_INITSYSTEM;
			if ($task == self::TASK_INITSYSTEM) $current = 'id="current"';

			// ヘルプを作成
			$helpText = $this->gInstance->getHelpManager()->getHelpText(self::HELP_KEY_INITSYSTEM);
			$menuText .= '<li ' . $current . '><a href="'. $this->getUrl($link) .'"><span ' . $helpText . '>データ初期化</span></a></li>' . M3_NL;
			
			// ### DBバックアップ ###
			$current = '';
			$link = $baseUrl . '?task=' . self::TASK_DBBACKUP;
			if ($task == self::TASK_DBBACKUP) $current = 'id="current"';

			// ヘルプを作成
			$helpText = $this->gInstance->getHelpManager()->getHelpText(self::HELP_KEY_DBBACKUP);
			$menuText .= '<li ' . $current . '><a href="'. $this->getUrl($link) .'"><span ' . $helpText . '>バックアップ</span></a></li>' . M3_NL;
			
			// ### DB状況 ###
			$current = '';
			$link = $baseUrl . '?task=' . self::TASK_DBCONDITION;
			if ($task == self::TASK_DBCONDITION) $current = 'id="current"';

			// ヘルプを作成
			$helpText = $this->gInstance->getHelpManager()->getHelpText(self::HELP_KEY_DBCONDITION);
			$menuText .= '<li ' . $current . '><a href="'. $this->getUrl($link) .'"><span ' . $helpText . '>状況</span></a></li>' . M3_NL;
		}
		
		// 上段メニュー終了
		$menuText .= '</ul>' . M3_NL;
		$menuText .= '</div>' . M3_NL;
		
		// 作成データの埋め込み
		$linkList = '<div id="configmenu-top"><label>' . self::TASK_NAME_MAIN . ' &gt;&gt;' . $linkList . '</label></div>';
		$outputText .= '<table width="90%"><tr><td>' . $linkList . $menuText . '</td></tr></table>' . M3_NL;
		$this->tmpl->addVar("_widget", "menu_items", $outputText);
*/
	}
}
?>
