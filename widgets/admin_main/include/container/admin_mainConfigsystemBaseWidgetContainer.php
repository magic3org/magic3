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

class admin_mainConfigsystemBaseWidgetContainer extends admin_mainBaseWidgetContainer
{
	const BREADCRUMB_TITLE	= 'システム情報';		// パンくずリストトップタイトル
	
	// 画面
	const TASK_CONFIGSYS		= 'configsys';	// システム基本設定
	const TASK_CONFIGLANG		= 'configlang';	// 言語設定
	const TASK_CONFIGMESSAGE	= 'configmessage';	// メッセージ設定
	const TASK_CONFIGIMAGE		= 'configimage';		// 画像設定
	const TASK_SERVER_ENV		= 'serverenv';	// サーバ環境
	const DEFAULT_TASK			= 'configsys';		// デフォルト画面
	
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
			case self::TASK_USERLIST:	// ユーザ一覧
			case self::TASK_USERLIST_DETAIL:	// ユーザ詳細
				$titles[] = 'ユーザ一覧';
				break;
			case self::TASK_USERGROUP:	// ユーザグループ
			case self::TASK_USERGROUP_DETAIL:	// ユーザグループ詳細
				$titles[] = 'ユーザグループ';
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
									'active'	=> ($task == self::TASK_USERLIST || $task == self::TASK_USERLIST_DETAIL),
									'submenu'	=> array()
								),
								(Object)array(
									'name'		=> 'ユーザグループ',
									'task'		=> self::TASK_USERGROUP,
									'url'		=> '',
									'tagid'		=> '',
									'active'	=> ($task == self::TASK_USERGROUP || $task == self::TASK_USERGROUP_DETAIL),
									'submenu'	=> array()
								)
							);
		$this->gPage->setAdminSubNavbarDef($navbarDef);
		
		
		
		
		// パンくずリストを作成
		switch ($task){
			case self::TASK_CONFIGSYS:	// システム基本設定
				$linkList = ' &gt;&gt; ' . $this->_('System Basic Cofiguration');			// システム基本設定
				break;
			case self::TASK_SERVER_ENV:	// サーバ環境
				$linkList = ' &gt;&gt; ' . $this->_('Server Environment');			// サーバ環境
				break;
			case self::TASK_CONFIGLANG:	// 言語設定
				$linkList = ' &gt;&gt; ' . $this->_('Language Cofiguration');		// 言語設定
				break;
			case self::TASK_CONFIGMESSAGE:	// メッセージ設定
				$linkList = ' &gt;&gt; ' . $this->_('Message Cofiguration');		// メッセージ設定
				break;
			case self::TASK_CONFIGIMAGE:		// 画像設定
				$linkList = ' &gt;&gt; ' . $this->_('Image Cofiguration');		// 画像設定
				break;
		}
				
		// ####### 上段メニューの作成 #######
		$menuText = '<div id="configmenu-upper">' . M3_NL;
		$menuText .= '<ul>' . M3_NL;
		
		$current = '';
		$link = $this->gEnv->getDefaultAdminUrl() . '?' . M3_REQUEST_PARAM_OPERATION_COMMAND . '=' . M3_REQUEST_CMD_CONFIG_WIDGET . 
				'&' . M3_REQUEST_PARAM_WIDGET_ID . '=' . $this->gEnv->getCurrentWidgetId();
				
		// ### システム基本設定 ###
		$current = '';
		$link = $this->gEnv->getDefaultAdminUrl() . '?task=' . self::TASK_CONFIGSYS;
		if ($task == self::TASK_CONFIGSYS){
			$current = 'id="current"';
		}
		// ヘルプを作成
		$helpText = $this->gInstance->getHelpManager()->getHelpText(self::TASK_CONFIGSYS);
		$menuText .= '<li ' . $current . '><a href="'. $this->getUrl($link) .'"><span ' . $helpText . '>システム基本設定</span></a></li>' . M3_NL;
		
		// ### サーバ環境 ###
		$current = '';
		$link = $this->gEnv->getDefaultAdminUrl() . '?task=' . self::TASK_SERVER_ENV;
		if ($task == self::TASK_SERVER_ENV){
			$current = 'id="current"';
		}
		// ヘルプを作成
		$helpText = $this->gInstance->getHelpManager()->getHelpText(self::TASK_SERVER_ENV);
		$menuText .= '<li ' . $current . '><a href="'. $this->getUrl($link) .'"><span ' . $helpText . '>サーバ環境</span></a></li>' . M3_NL;
				
		// ### 言語設定 ###
		$current = '';
		$link = $this->gEnv->getDefaultAdminUrl() . '?task=' . self::TASK_CONFIGLANG;
		if ($task == self::TASK_CONFIGLANG){
			$current = 'id="current"';
		}
		// ヘルプを作成
		$helpText = $this->gInstance->getHelpManager()->getHelpText(self::TASK_CONFIGLANG);
		$menuText .= '<li ' . $current . '><a href="'. $this->getUrl($link) .'"><span ' . $helpText . '>言語設定</span></a></li>' . M3_NL;
		
		// ### メッセージ設定 ###
		$current = '';
		$link = $this->gEnv->getDefaultAdminUrl() . '?task=' . self::TASK_CONFIGMESSAGE;
		if ($task == self::TASK_CONFIGMESSAGE){
			$current = 'id="current"';
		}
		// ヘルプを作成
		$helpText = $this->gInstance->getHelpManager()->getHelpText(self::TASK_CONFIGMESSAGE);
		$menuText .= '<li ' . $current . '><a href="'. $this->getUrl($link) .'"><span ' . $helpText . '>メッセージ設定</span></a></li>' . M3_NL;
		
		// ### 画像設定 ###
		$current = '';
		$link = $this->gEnv->getDefaultAdminUrl() . '?task=' . self::TASK_CONFIGIMAGE;
		if ($task == self::TASK_CONFIGIMAGE){
			$current = 'id="current"';
		}
		// ヘルプを作成
		$helpText = $this->gInstance->getHelpManager()->getHelpText(self::TASK_CONFIGIMAGE);
		$menuText .= '<li ' . $current . '><a href="'. $this->getUrl($link) .'"><span ' . $helpText . '>画像設定</span></a></li>' . M3_NL;
		
		// 上段メニュー終了
		$menuText .= '</ul>' . M3_NL;
		$menuText .= '</div>' . M3_NL;
		
		// 作成データの埋め込み
		$linkList = '<div id="configmenu-top"><label>' . 'システム情報' . $linkList . '</label></div>';
		$outputText .= '<table width="90%"><tr><td>' . $linkList . $menuText . '</td></tr></table>' . M3_NL;
		$this->tmpl->addVar("_widget", "menu_items", $outputText);
	}
}
?>
