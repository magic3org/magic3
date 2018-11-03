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
	const BREADCRUMB_TITLE	= 'メンテナンス';		// パンくずリスト用トップタイトル
	
	// 画面
	const TASK_MAIN				= 'mainte';			// 全体(メンテナンス)
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
		
		// ##### メニュー項目の表示制御 #####
		$visible_LandingPage = $this->gSystem->getSystemConfig(self::CF_USE_LANDING_PAGE);			// 「ランディングページ機能」の表示

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
				$titles[] = '管理機能設定';
				$titles[] = '管理画面カスタムウィザード';
				break;
			case self::TASK_EDITMENU:		// 管理メニュー編集
				$titles[] = '管理機能設定';
				$titles[] = '管理メニュー編集';
				break;
			case self::TASK_LANDINGPAGE:		// ランディングページ管理
				$titles[] = '管理機能設定';
				$titles[] = 'ランディングページ管理';
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
														$task == self::TASK_MENUID_DETAIL		// メニューID詳細
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
							
		// 設定によって「管理機能設定」は変更
		if (M3_PERMIT_REINSTALL){			// 再インストール可能な場合は「システム再インストール」項目を表示
			$navbarDef->menu[] =	(Object)array(
										'name'		=> '管理機能設定',
										'task'		=> '',
										'url'		=> '',
										'tagid'		=> '',
										'active'	=> (
															$task == self::TASK_INITWIZARD ||		// 管理画面カスタムウィザード
															$task == self::TASK_EDITMENU ||			// 管理メニュー編集
															$task == self::TASK_LANDINGPAGE	||		// ランディングページ管理
															$task == self::TASK_INITSYSTEM			// システム再インストール
														),
										'submenu'	=> array(
											(Object)array(
												'name'		=> 'ランディングページ管理',
												'task'		=> self::TASK_LANDINGPAGE,
												'url'		=> '',
												'tagid'		=> '',
												'visible'	=> $visible_LandingPage,					// 表示制御
												'active'	=> (
																	$task == self::TASK_LANDINGPAGE		// ランディングページ管理
																)
											),
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
										'name'		=> '管理機能設定',
										'task'		=> '',
										'url'		=> '',
										'tagid'		=> '',
										'active'	=> (
															$task == self::TASK_INITWIZARD ||		// 管理画面カスタムウィザード
															$task == self::TASK_EDITMENU ||			// 管理メニュー編集
															$task == self::TASK_LANDINGPAGE		// ランディングページ管理
														),
										'submenu'	=> array(
											(Object)array(
												'name'		=> 'ランディングページ管理',
												'task'		=> self::TASK_LANDINGPAGE,
												'url'		=> '',
												'tagid'		=> '',
												'visible'	=> $visible_LandingPage,					// 表示制御
												'active'	=> (
																	$task == self::TASK_LANDINGPAGE		// ランディングページ管理
																)
											),
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
												'visible'	=> false,					// 暫定非表示
												'active'	=> (
																	$task == self::TASK_EDITMENU		// 管理メニュー編集
																)
											),
										)
									);
		}
		$this->gPage->setAdminSubNavbarDef($navbarDef);
	}
}
?>
