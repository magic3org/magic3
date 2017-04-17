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
 * @copyright  Copyright 2006-2017 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/admin_mainBaseWidgetContainer.php');

class admin_mainTempBaseWidgetContainer extends admin_mainBaseWidgetContainer
{
	const BREADCRUMB_TITLE	= 'テンプレート管理';		// パンくずリストトップタイトル
	// 画面
	const DEFAULT_TASK = 'templist';			// デフォルト画面(テンプレート一覧)
				
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
		// 表示画面を決定
		$task = $request->trimValueOf(M3_REQUEST_PARAM_OPERATION_TASK);
		if (empty($task)) $task = self::DEFAULT_TASK;
		
		// パンくずリストの作成
		$titles = array(self::BREADCRUMB_TITLE);
		switch ($task){
			case self::TASK_TEMPLIST:			// テンプレート一覧
				$titles[] = 'テンプレート一覧';
				break;
			case self::TASK_TEMPIMAGE:			// テンプレート画像一覧
			case self::TASK_TEMPIMAGE_DETAIL:	// テンプレート画像一覧(詳細)
				$titles[] = '画像一覧';
				break;
			case self::TASK_TEMPGENERATECSS:			// テンプレートCSS生成
			case self::TASK_TEMPGENERATECSS_DETAIL:	// テンプレートCSS生成(詳細)
				$titles[] = 'CSS生成';
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
									'name'		=> 'テンプレート一覧',
									'task'		=> self::TASK_TEMPLIST,
									'url'		=> '',
									'tagid'		=> '',
									'active'	=> ($task == self::TASK_TEMPLIST),
									'submenu'	=> array()
								)
/*								(Object)array(
									'name'		=> '画像一覧',
									'task'		=> self::TASK_TEMPIMAGE,
									'url'		=> '',
									'tagid'		=> '',
									'active'	=> ($task == self::TASK_TEMPIMAGE || $task == self::TASK_TEMPIMAGE_DETAIL),
									'submenu'	=> array()
								)*/
							);
		$this->gPage->setAdminSubNavbarDef($navbarDef);
	}
}
?>
