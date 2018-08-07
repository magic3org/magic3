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

class admin_mainConfigsystemBaseWidgetContainer extends admin_mainBaseWidgetContainer
{
	protected $_mainDb;			// DB接続オブジェクト
	const BREADCRUMB_TITLE	= 'システム情報';		// パンくずリストトップタイトル
	
	// 画面
	const DEFAULT_TASK			= 'configsys';		// デフォルト画面
	
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
		if (empty($task)) $task = self::DEFAULT_TASK;
		
		// パンくずリストの作成
		$titles = array(self::BREADCRUMB_TITLE);
		switch ($task){
			case self::TASK_CONFIGSYS:	// システム基本設定
				$titles[] = $this->_('System Basic Cofiguration');
				break;
			case self::TASK_SERVER_ENV:	// サーバ環境
				$titles[] = $this->_('Server Environment');
				break;
			case self::TASK_CONFIGLANG:	// 言語設定
				$titles[] = $this->_('Language Cofiguration');
				break;
			case self::TASK_CONFIGMESSAGE:	// メッセージ設定
				$titles[] = $this->_('Message Cofiguration');
				break;
			case self::TASK_CONFIGIMAGE:		// 画像設定
				$titles[] = $this->_('Image Cofiguration');
				break;
			case self::TASK_CONFIGMAIL:		// メールサーバ
				$titles[] = $this->_('Mail Server Cofiguration');
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
									'name'		=> $this->_('System Basic Cofiguration'),			// システム基本設定
									'task'		=> self::TASK_CONFIGSYS,
									'url'		=> '',
									'tagid'		=> '',
									'active'	=> ($task == self::TASK_CONFIGSYS),
									'help'		=> $this->gInstance->getHelpManager()->getHelpText(self::TASK_CONFIGSYS),// ヘルプ文字列
									'submenu'	=> array()
								),
								(Object)array(
									'name'		=> $this->_('Language Cofiguration'),		// 言語設定
									'task'		=> self::TASK_CONFIGLANG,
									'url'		=> '',
									'tagid'		=> '',
									'active'	=> ($task == self::TASK_CONFIGLANG),
									'help'		=> $this->gInstance->getHelpManager()->getHelpText(self::TASK_CONFIGLANG),// ヘルプ文字列
									'submenu'	=> array()
								),
								(Object)array(
									'name'		=> $this->_('Message Cofiguration'),		// メッセージ設定
									'task'		=> self::TASK_CONFIGMESSAGE,
									'url'		=> '',
									'tagid'		=> '',
									'active'	=> ($task == self::TASK_CONFIGMESSAGE),
									'help'		=> $this->gInstance->getHelpManager()->getHelpText(self::TASK_CONFIGMESSAGE),// ヘルプ文字列
									'submenu'	=> array()
								),
								(Object)array(
									'name'		=> $this->_('Image Cofiguration'),		// 画像設定
									'task'		=> self::TASK_CONFIGIMAGE,
									'url'		=> '',
									'tagid'		=> '',
									'active'	=> ($task == self::TASK_CONFIGIMAGE),
									'help'		=> $this->gInstance->getHelpManager()->getHelpText(self::TASK_CONFIGIMAGE),// ヘルプ文字列
									'submenu'	=> array()
								),
								(Object)array(
									'name'		=> $this->_('Mail Server Cofiguration'),		// メールサーバ
									'task'		=> self::TASK_CONFIGMAIL,
									'url'		=> '',
									'tagid'		=> '',
									'active'	=> ($task == self::TASK_CONFIGMAIL),
									'help'		=> $this->gInstance->getHelpManager()->getHelpText(self::TASK_CONFIGMAIL),// ヘルプ文字列
									'submenu'	=> array()
								),
								(Object)array(
									'name'		=> $this->_('Server Environment'),			// サーバ環境
									'task'		=> self::TASK_SERVER_ENV,
									'url'		=> '',
									'tagid'		=> '',
									'active'	=> ($task == self::TASK_SERVER_ENV),
									'help'		=> $this->gInstance->getHelpManager()->getHelpText(self::TASK_SERVER_ENV),// ヘルプ文字列
									'submenu'	=> array()
								)
							);
		$this->gPage->setAdminSubNavbarDef($navbarDef);
	}
}
?>
