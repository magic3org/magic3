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
 * @copyright  Copyright 2006-2011 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: admin_mainConfigbasicBaseWidgetContainer.php 4223 2011-07-08 03:56:33Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/admin_mainBaseWidgetContainer.php');

class admin_mainConfigbasicBaseWidgetContainer extends admin_mainBaseWidgetContainer
{
	const HELP_KEY_CONFIGSITE = 'configsite';		// サイト情報
	const HELP_KEY_PAGEHEAD = 'pagehead';		// ページヘッダ情報
	const HELP_KEY_PORTAL = 'portal';			// Magic3ポータル
	const DEFAULT_TOP_PAGE = 'configsite';		// デフォルトのトップ画面
	
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
			case 'configsite':	// サイト情報
				$linkList = ' &gt;&gt; サイト情報';
				break;
			case 'pagehead':	// ページヘッダ情報
			case 'pagehead_detail':	// ページヘッダ情報詳細
				$linkList = ' &gt;&gt; ページヘッダ情報';
				break;
			case 'portal':	// Magic3ポータル
				$linkList = ' &gt;&gt; Magic3ポータル';
				break;
		}
				
		// ####### 上段メニューの作成 #######
		$menuText = '<div id="configmenu-upper">' . M3_NL;
		$menuText .= '<ul>' . M3_NL;
		
		$current = '';
		$link = $this->gEnv->getDefaultAdminUrl() . '?' . M3_REQUEST_PARAM_OPERATION_COMMAND . '=' . M3_REQUEST_CMD_CONFIG_WIDGET . 
				'&' . M3_REQUEST_PARAM_WIDGET_ID . '=' . $this->gEnv->getCurrentWidgetId();
				
		// ### サイト情報 ###
		$current = '';
		$link = $this->gEnv->getDefaultAdminUrl() . '?' . 'task=configsite';
		if ($task == 'configsite'){
			$current = 'id="current"';
		}
		// ヘルプを作成
		$helpText = $this->gInstance->getHelpManager()->getHelpText(self::HELP_KEY_CONFIGSITE);
		$menuText .= '<li ' . $current . '><a href="'. $this->getUrl($link) .'"><span ' . $helpText . '>サイト情報</span></a></li>' . M3_NL;
		
		// ### ページヘッダ情報 ###
		$current = '';
		$link = $this->gEnv->getDefaultAdminUrl() . '?' . 'task=pagehead';
		if ($task == 'pagehead'){
			$current = 'id="current"';
		}
		// ヘルプを作成
		$helpText = $this->gInstance->getHelpManager()->getHelpText(self::HELP_KEY_PAGEHEAD);
		$menuText .= '<li ' . $current . '><a href="'. $this->getUrl($link) .'"><span ' . $helpText . '>ページヘッダ情報</span></a></li>' . M3_NL;
		
		// ### Magic3ポータル ###
		$current = '';
		$link = $this->gEnv->getDefaultAdminUrl() . '?' . 'task=portal';
		if ($task == 'portal'){
			$current = 'id="current"';
		}
		// ヘルプを作成
		$helpText = $this->gInstance->getHelpManager()->getHelpText(self::HELP_KEY_PORTAL);
		$menuText .= '<li ' . $current . '><a href="'. $this->getUrl($link) .'"><span ' . $helpText . '>Magic3ポータル</span></a></li>' . M3_NL;
		
		// 上段メニュー終了
		$menuText .= '</ul>' . M3_NL;
		$menuText .= '</div>' . M3_NL;
		
		// 作成データの埋め込み
		$linkList = '<div id="configmenu-top"><label>' . '基本情報' . $linkList . '</div>';
		$outputText .= '<table width="90%"><tr><td>' . $linkList . $menuText . '</td></tr></table>' . M3_NL;
		$this->tmpl->addVar("_widget", "menu_items", $outputText);
	}
}
?>
