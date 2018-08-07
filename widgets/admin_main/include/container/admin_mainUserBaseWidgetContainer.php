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
require_once($gEnvManager->getCurrentWidgetDbPath() . '/admin_mainDb.php');

class admin_mainUserBaseWidgetContainer extends admin_mainBaseWidgetContainer
{
	protected $_mainDb;			// DB接続オブジェクト
	const BREADCRUMB_TITLE	= 'ユーザ管理';		// パンくずリストトップタイトル
	
	// 画面
	const DEFAULT_TOP_PAGE = 'userlist';		// デフォルトのトップ画面
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DB接続オブジェクト作成
		$this->_mainDb = new admin_mainDb();
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
		// 表示画面を決定
		$task = $request->trimValueOf(M3_REQUEST_PARAM_OPERATION_TASK);
		if (empty($task)) $task = self::DEFAULT_TOP_PAGE;
		
		// パンくずリストの作成
		$titles = array(self::BREADCRUMB_TITLE);
		switch ($task){
			case self::TASK_USERLIST:	// ユーザ一覧
				$titles[] = 'ユーザ一覧';
				break;
			case self::TASK_USERLIST_DETAIL:	// ユーザ詳細
				$titles[] = 'ユーザ一覧';
				$titles[] = '詳細';
				break;			
			case self::TASK_LOGINHISTORY:	// ログイン履歴
				$titles[] = 'ユーザ一覧';
				$titles[] = '詳細';
				$titles[] = 'ログイン履歴';
				break;
			case self::TASK_USERGROUP:	// ユーザグループ
				$titles[] = 'ユーザグループ';
				break;
			case self::TASK_USERGROUP_DETAIL:	// ユーザグループ詳細
				$titles[] = 'ユーザグループ';
				$titles[] = '詳細';
				break;
			case self::TASK_TASKACCESS:						// 管理画面アクセス制御
				$titles[] = 'アクセス制御';
				$titles[] = 'システム運用者';
				break;
		}
		$this->gPage->setAdminBreadcrumbDef($titles);
		
		// メニューバーの作成
		$navbarDef = new stdClass;
		$navbarDef->title = '';
		$navbarDef->baseurl = $this->getAdminUrlWithOptionParam();
		$navbarDef->help	= '';// ヘルプ文字列
		$navbarDef->menu =	array(
								(Object)array(
									'name'		=> 'ユーザ一覧',
									'task'		=> self::TASK_USERLIST,
									'url'		=> '',
									'tagid'		=> '',
									'active'	=> ($task == self::TASK_USERLIST || 
													$task == self::TASK_USERLIST_DETAIL || 
													$task == self::TASK_LOGINHISTORY),
									'submenu'	=> array()
								),
								(Object)array(
									'name'		=> 'ユーザグループ',
									'task'		=> self::TASK_USERGROUP,
									'url'		=> '',
									'tagid'		=> '',
									'active'	=> ($task == self::TASK_USERGROUP || $task == self::TASK_USERGROUP_DETAIL),
									'submenu'	=> array()
								),
								(Object)array(
									'name'		=> 'アクセス制御',
									'task'		=> self::TASK_TASKACCESS,
									'url'		=> '',
									'tagid'		=> '',
									'active'	=> ($task == self::TASK_TASKACCESS),
									'submenu'	=> array()
								)
							);
		$this->gPage->setAdminSubNavbarDef($navbarDef);
	}
}
?>
