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
 * @copyright  Copyright 2006-2013 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: admin_mainUserBaseWidgetContainer.php 5814 2013-03-11 10:22:45Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/admin_mainBaseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/admin_mainDb.php');

class admin_mainUserBaseWidgetContainer extends admin_mainBaseWidgetContainer
{
	protected $_mainDb;			// DB接続オブジェクト
	const TASK_USERLIST		= 'userlist';		// ユーザ一覧
	const TASK_USERLIST_DETAIL = 'userlist_detail';	// ユーザ詳細
	const TASK_USERGROUP	= 'usergroup';		// ユーザグループ
	const DEFAULT_TOP_PAGE = 'userlist';		// デフォルトのトップ画面
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
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
		
		// パンくずリストを作成
		switch ($task){
			case self::TASK_USERLIST:	// ユーザ一覧
			case self::TASK_USERLIST_DETAIL:	// ユーザ詳細
				$linkList = ' &gt;&gt; ユーザ一覧';
				break;
			case self::TASK_USERGROUP:	// ユーザグループ
				$linkList = ' &gt;&gt; ユーザグループ';
				break;
		}
				
		// ####### 上段メニューの作成 #######
		$menuText = '<div id="configmenu-upper">' . M3_NL;
		$menuText .= '<ul>' . M3_NL;
		
		$current = '';
		$link = $this->gEnv->getDefaultAdminUrl() . '?' . M3_REQUEST_PARAM_OPERATION_COMMAND . '=' . M3_REQUEST_CMD_CONFIG_WIDGET . 
				'&' . M3_REQUEST_PARAM_WIDGET_ID . '=' . $this->gEnv->getCurrentWidgetId();
				
		// ### ユーザ一覧 ###
		$current = '';
		$link = $this->gEnv->getDefaultAdminUrl() . '?' . 'task=' . self::TASK_USERLIST;
		if ($task == self::TASK_USERLIST ||
			$task == self::TASK_USERLIST_DETAIL){	// ユーザ詳細
			$current = 'id="current"';
		}
		// ヘルプを作成
		$helpText = $this->gInstance->getHelpManager()->getHelpText(self::TASK_USERLIST);
		$menuText .= '<li ' . $current . '><a href="'. $this->getUrl($link) .'"><span ' . $helpText . '>ユーザ一覧</span></a></li>' . M3_NL;
		
		// ### ユーザグループ ###
		$current = '';
		$link = $this->gEnv->getDefaultAdminUrl() . '?' . 'task=' . self::TASK_USERGROUP;
		if ($task == self::TASK_USERGROUP){
			$current = 'id="current"';
		}
		// ヘルプを作成
		$helpText = $this->gInstance->getHelpManager()->getHelpText(self::TASK_USERGROUP);
		$menuText .= '<li ' . $current . '><a href="'. $this->getUrl($link) .'"><span ' . $helpText . '>ユーザグループ</span></a></li>' . M3_NL;
		
		// 上段メニュー終了
		$menuText .= '</ul>' . M3_NL;
		$menuText .= '</div>' . M3_NL;
		
		// 作成データの埋め込み
		$linkList = '<div id="configmenu-top"><label>' . 'ユーザ管理' . $linkList . '</label></div>';
		$outputText .= '<table width="90%"><tr><td>' . $linkList . $menuText . '</td></tr></table>' . M3_NL;
		$this->tmpl->addVar("_widget", "menu_items", $outputText);
	}
}
?>
