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
 * @copyright  Copyright 2006-2014 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/admin_mainBaseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/admin_mainDb.php');

class admin_mainTest_navbarWidgetContainer extends admin_mainBaseWidgetContainer
{
	private $db;	// DB接続オブジェクト
		
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DB接続オブジェクト作成
		$this->db = new admin_mainDb();
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
		return 'test_navbar.tmpl.html';
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
		$this->gPage->setAdminBreadcrumbDef(array('テスト画面', '詳細画面'));
		
		$navbarDef = new stdClass;
		$navbarDef->title = 'ウィジェット名';
		$navbarDef->baseurl = $this->getAdminUrlWithOptionParam();
		
/*		$navbarMenu = array();
		$menuItem = new stdClass;
		$menuItem->name	= 'トップ項目';
		$menuItem->task	= 'item_detail';
		$menuItem->url	= '';
		$menuItem->active	= false;
		$navbarMenu[] = $menuItem;
		$menuItem = new stdClass;
		$menuItem->name	= 'トップ項目2';
		$menuItem->task	= '';
		$menuItem->url	= '#widget_other';
		$menuItem->active	= true;
		$navbarMenu[] = $menuItem;
		$menuItem = new stdClass;
		$menuItem->name	= 'トップ項目3';
		$menuItem->task	= '';
		$menuItem->url	= '';
		$menuItem->active	= false;
		$menuItem->submenu = array(
								(Object)array(	'name'		=> 'トップ項目3サブ項目1',
												'task'		=> 'a',
												'url'		=> '',
												'active'	=> false	),
								(Object)array(	'name'		=> 'トップ項目3サブ項目1',
												'task'		=> 'a',
												'url'		=> '',
												'active'	=> false	),
								(Object)array(	'name'		=> 'トップ項目3サブ項目1',
												'task'		=> 'a',
												'url'		=> '',
												'active'	=> false	)
								);*/
		$navbarDef->menu =	array(
								(Object)array(
									'name'		=> 'トップ項目',
									'task'		=> 'item_detail',
									'url'		=> '',
									'tagid'		=> '',
									'active'	=> false,
									'submenu'	=> array()
								),
								(Object)array(
									'name'		=> 'トップ項目2',
									'task'		=> '',
									'url'		=> '#widget_other',
									'tagid'		=> '',
									'active'	=> true,
									'submenu'	=> array()
								),
								(Object)array(
									'name'		=> 'トップ項目3',
									'task'		=> '',
									'url'		=> '',
									'tagid'		=> '',
									'active'	=> false,
									'submenu'	=> array(
										(Object)array(
											'name'		=> 'トップ項目3サブ項目1',
											'task'		=> 'a',
											'url'		=> '',
											'tagid'		=> '',
											'active'	=> false
										),
										(Object)array(
											'name'		=> 'トップ項目3サブ項目1',
											'task'		=> 'a',
											'url'		=> '',
											'tagid'		=> '',
											'active'	=> false
										),
										(Object)array(
											'name'		=> 'トップ項目3サブ項目1',
											'task'		=> 'a',
											'url'		=> '',
											'tagid'		=> '',
											'active'	=> false
										)
									)
								),
								(Object)array(
									'name'		=> 'トップ項目4',
									'task'		=> '',
									'url'		=> '',
									'tagid'		=> '',
									'active'	=> false,
									'submenu'	=> array(
										(Object)array(
											'name'		=> 'トップ項目4サブ項目1',
											'task'		=> 'a',
											'url'		=> '',
											'tagid'		=> '',
											'active'	=> false
										),
										(Object)array(
											'name'		=> 'トップ項目4サブ項目1',
											'task'		=> 'a',
											'url'		=> '',
											'tagid'		=> '',
											'active'	=> true
										),
										(Object)array(
											'name'		=> 'トップ項目4サブ項目1',
											'task'		=> 'a',
											'url'		=> '',
											'tagid'		=> '',
											'active'	=> false
										)
									)
								)
							);
//		$navbarMenu[] = $menuItem;
//		$navbarDef->menu = $navbarMenu;
/*		$menuItem = new stdClass;
		$menuItem->name	= 'トップ項目3サブ項目1';
		$menuItem->task	= 'a';
		$menuItem->url	= '';
		$menuItem->active	= false;
		$menuItems[] = $menuItem;
		$menuItem = new stdClass;
		$menuItem->name	= 'トップ項目3サブ項目2';
		$menuItem->task	= b'';
		$menuItem->url	= '';
		$menuItem->active	= true;
		$menuItems[] = $menuItem;
		$menuItem = new stdClass;
		$menuItem->name	= 'トップ項目3サブ項目3';
		$menuItem->task	= 'c';
		$menuItem->url	= '';
		$menuItem->active	= false;
		$menuItems[] = $menuItem;*/
		
//var_dump($navbarDef->menu);
		$this->gPage->setAdminSubNavbarDef($navbarDef);
	}
}
?>
