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
 * @copyright  Copyright 2006-2010 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: admin_mainConfigsystemBaseWidgetContainer.php 3822 2010-11-15 01:58:17Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/admin_mainBaseWidgetContainer.php');

class admin_mainConfigsystemBaseWidgetContainer extends admin_mainBaseWidgetContainer
{
	const HELP_KEY_CONFIGSYS = 'configsys';		// システム基本設定
	const HELP_KEY_CONFIGLANG = 'configlang';		// 言語設定
	const HELP_KEY_CONFIGMESSAGE = 'configmessage';		// メッセージ設定
	const DEFAULT_TOP_PAGE = 'configsys';		// デフォルトのトップ画面
	
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
		if (empty($task)) $task = self::DEFAULT_TOP_PAGE;
		
		// パンくずリストを作成
		switch ($task){
			case 'configsys':	// システム基本設定
				$linkList = ' &gt;&gt; ' . $this->_('System Basic Cofiguration');			// システム基本設定
				break;
			case 'configlang':	// 言語設定
				$linkList = ' &gt;&gt; ' . $this->_('Language Cofiguration');		// 言語設定
				break;
			case 'configmessage':	// メッセージ設定
				$linkList = ' &gt;&gt; ' . $this->_('Message Cofiguration');		// メッセージ設定
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
		$link = $this->gEnv->getDefaultAdminUrl() . '?' . 'task=configsys';
		if ($task == 'configsys'){
			$current = 'id="current"';
		}
		// ヘルプを作成
		$helpText = $this->gInstance->getHelpManager()->getHelpText(self::HELP_KEY_CONFIGSYS);
		$menuText .= '<li ' . $current . '><a href="'. $this->getUrl($link) .'"><span ' . $helpText . '>システム基本設定</span></a></li>' . M3_NL;
		
		// ### 言語設定 ###
		$current = '';
		$link = $this->gEnv->getDefaultAdminUrl() . '?' . 'task=configlang';
		if ($task == 'configlang'){
			$current = 'id="current"';
		}
		// ヘルプを作成
		$helpText = $this->gInstance->getHelpManager()->getHelpText(self::HELP_KEY_CONFIGLANG);
		$menuText .= '<li ' . $current . '><a href="'. $this->getUrl($link) .'"><span ' . $helpText . '>言語設定</span></a></li>' . M3_NL;
		
		// ### メッセージ設定 ###
		$current = '';
		$link = $this->gEnv->getDefaultAdminUrl() . '?' . 'task=configmessage';
		if ($task == 'configmessage'){
			$current = 'id="current"';
		}
		// ヘルプを作成
		$helpText = $this->gInstance->getHelpManager()->getHelpText(self::HELP_KEY_CONFIGMESSAGE);
		$menuText .= '<li ' . $current . '><a href="'. $this->getUrl($link) .'"><span ' . $helpText . '>メッセージ設定</span></a></li>' . M3_NL;
		
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
