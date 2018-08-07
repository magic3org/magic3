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

class admin_mainConfigbasicBaseWidgetContainer extends admin_mainBaseWidgetContainer
{
	const BREADCRUMB_TITLE	= '基本情報';		// パンくずリストトップタイトル
	
	// 画面
	const DEFAULT_TASK			= 'configsite';			// デフォルトの画面
				
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
			case self::TASK_CONFIGSITE:			// サイト情報
				$titles[] = 'サイト情報';
				break;
			case self::TASK_PAGEHEAD:			// ページヘッダ情報
			case self::TASK_PAGEHEAD_DETAIL:	// ページヘッダ情報詳細
				$titles[] = 'ページヘッダ情報';
				break;
			case self::TASK_PORTAL:				// Magic3ポータル
				$titles[] = 'Magic3ポータル';
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
									'name'		=> 'サイト情報',
									'task'		=> self::TASK_CONFIGSITE,
									'url'		=> '',
									'tagid'		=> '',
									'active'	=> ($task == self::TASK_CONFIGSITE),
									'submenu'	=> array()
								),
								(Object)array(
									'name'		=> 'ページヘッダ情報',
									'task'		=> self::TASK_PAGEHEAD,
									'url'		=> '',
									'tagid'		=> '',
									'active'	=> ($task == self::TASK_PAGEHEAD || $task == self::TASK_PAGEHEAD_DETAIL),
									'submenu'	=> array()
								)
							);
		$this->gPage->setAdminSubNavbarDef($navbarDef);
	}
}
?>
