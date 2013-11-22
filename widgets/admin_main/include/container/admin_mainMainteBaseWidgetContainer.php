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
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/admin_mainBaseWidgetContainer.php');

class admin_mainMainteBaseWidgetContainer extends admin_mainBaseWidgetContainer
{
	const TASK_MAIN = 'mainte';				// メンテナンス
	const TASK_RESBROWSE = 'resbrowse';		// ファイルブラウザ
	const TASK_INITSYSTEM = 'initsystem';		// DBメンテナンス
	const TASK_DBBACKUP = 'dbbackup';		// DBバックアップ
	const TASK_PAGEINFO = 'pageinfo';	// ページ情報
	const TASK_PAGEINFO_DETAIL = 'pageinfo_detail';	// ページ情報
	const TASK_PAGEID = 'pageid';		// ページID
	const TASK_PAGEID_DETAIL = 'pageid_detail';		// ページID
	const TASK_MENUID = 'menuid';		// メニューID
	const TASK_MENUID_DETAIL = 'menuid_detail';		// メニューID
	const DEFAULT_TASK = 'resbrowse';		// ファイルブラウザ
	
	const TASK_NAME_MAIN = 'メンテナンス';
	const HELP_KEY_RESBROWSE	= 'resbrowse';		// ファイルブラウザ
	const HELP_KEY_PAGEINFO		= 'pageinfo';
	const HELP_KEY_PAGEID		= 'pageid';
	const HELP_KEY_MENUID		= 'menuid';
	const HELP_KEY_INITSYSTEM	= 'initsystem';		// DBデータ初期化
	const HELP_KEY_DBBACKUP		= 'dbbackup';		// DBバックアップ
	
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
		if ($task == self::TASK_MAIN) $task = self::DEFAULT_TASK;
		
		// パンくずリストを作成
		switch ($task){
			case self::TASK_RESBROWSE:		// ファイルブラウザ
				$linkList = ' ファイル管理 &gt;&gt; ファイルブラウザ';
				break;	
			case self::TASK_PAGEINFO:	// ページ情報一覧
			case self::TASK_PAGEINFO_DETAIL:	// ページ情報詳細
				$linkList = ' マスター管理 &gt;&gt; ページ情報';
				break;
			case self::TASK_PAGEID:	// ページID一覧
			case self::TASK_PAGEID_DETAIL:	// ページID詳細
				$linkList = ' マスター管理 &gt;&gt; ページID';
				break;
			case self::TASK_MENUID:		// メニューID
			case self::TASK_MENUID_DETAIL:		// メニューID
				$linkList = ' マスター管理 &gt;&gt; メニューID';
				break;
			case self::TASK_INITSYSTEM:		// DBデータ初期化
				$linkList = ' DB管理 &gt;&gt; データ初期化';
				break;
			case self::TASK_DBBACKUP:		// DBバックアップ
				$linkList = ' DB管理 &gt;&gt; バックアップ';
				break;
		}
		// ####### 上段メニューの作成 #######
		$menuText = '<div id="configmenu-upper">' . M3_NL;
		$menuText .= '<ul>' . M3_NL;
		
		$current = '';
		$baseUrl = $this->gEnv->getDefaultAdminUrl();
		
		// ファイル管理
		$current = '';
		$link = $baseUrl . '?task=' . self::TASK_RESBROWSE;
		if ($task == self::TASK_RESBROWSE){		// ファイルブラウザ
			$current = 'id="current"';
		}
		$menuText .= '<li ' . $current . '><a href="'. $this->getUrl($link) .'"><span>ファイル管理</span></a></li>' . M3_NL;
		
		// マスター管理
		$current = '';
		$link = $baseUrl . '?task=' . self::TASK_PAGEINFO;		// ページ情報一覧
		if ($task == self::TASK_PAGEINFO ||						// ページ情報一覧
			$task == self::TASK_PAGEID ||						// ページID一覧
			$task == self::TASK_MENUID){						// メニューID
			$current = 'id="current"';
		}
		$menuText .= '<li ' . $current . '><a href="'. $this->getUrl($link) .'"><span>マスター管理</span></a></li>' . M3_NL;
		
		// DB管理
		$current = '';
		$link = $baseUrl . '?task=' . self::TASK_INITSYSTEM;		// DBデータ初期化
		if ($task == self::TASK_INITSYSTEM ||		// DBデータ初期化
			$task == self::TASK_DBBACKUP){		// DBバックアップ
			$current = 'id="current"';
		}
		$menuText .= '<li ' . $current . '><a href="'. $this->getUrl($link) .'"><span>DB管理</span></a></li>' . M3_NL;
		
		// 上段メニュー終了
		$menuText .= '</ul>' . M3_NL;
		$menuText .= '</div>' . M3_NL;
		
		// ####### 下段メニューの作成 #######
		$menuText .= '<div id="configmenu-lower">' . M3_NL;
		$menuText .= '<ul>' . M3_NL;
				
		if ($task == self::TASK_RESBROWSE){		// ファイルブラウザ
			// ### ファイルブラウザ ###
			$current = '';
			$link = $baseUrl . '?task=' . self::TASK_RESBROWSE;
			if ($task == self::TASK_RESBROWSE) $current = 'id="current"';

			// ヘルプを作成
			$helpText = $this->gInstance->getHelpManager()->getHelpText(self::HELP_KEY_RESBROWSE);
			$menuText .= '<li ' . $current . '><a href="'. $this->getUrl($link) .'"><span ' . $helpText . '>ファイルブラウザ</span></a></li>' . M3_NL;
		} else if ($task == self::TASK_PAGEINFO ||						// ページ情報一覧
			$task == self::TASK_PAGEID ||						// ページID一覧
			$task == self::TASK_MENUID){						// メニューID
			
			// ### ページ情報 ###
			$current = '';
			$link = $baseUrl . '?task=pageinfo';
			if ($task == 'pageinfo'){
				$current = 'id="current"';
			}
			// ヘルプを作成
			$helpText = $this->gInstance->getHelpManager()->getHelpText(self::HELP_KEY_PAGEINFO);
			$menuText .= '<li ' . $current . '><a href="'. $this->getUrl($link) .'"><span ' . $helpText . '>ページ情報</span></a></li>' . M3_NL;
		
			// ### ページID ###
			$current = '';
			$link = $baseUrl . '?task=pageid';
			if ($task == 'pageid'){
				$current = 'id="current"';
			}
			// ヘルプを作成
			$helpText = $this->gInstance->getHelpManager()->getHelpText(self::HELP_KEY_PAGEID);
			$menuText .= '<li ' . $current . '><a href="'. $this->getUrl($link) .'"><span ' . $helpText . '>ページID</span></a></li>' . M3_NL;
		
			// ### メニューID ###
			$current = '';
			$link = $baseUrl . '?task=menuid';
			if ($task == 'menuid'){
				$current = 'id="current"';
			}
			// ヘルプを作成
			$helpText = $this->gInstance->getHelpManager()->getHelpText(self::HELP_KEY_MENUID);
			$menuText .= '<li ' . $current . '><a href="'. $this->getUrl($link) .'"><span ' . $helpText . '>メニューID</span></a></li>' . M3_NL;
		} else if ($task == self::TASK_INITSYSTEM || 	// DBデータ初期化
					$task == self::TASK_DBBACKUP){		// DBバックアップ
			// ### DBデータ初期化 ###
			$current = '';
			$link = $baseUrl . '?task=' . self::TASK_INITSYSTEM;
			if ($task == self::TASK_INITSYSTEM) $current = 'id="current"';

			// ヘルプを作成
			$helpText = $this->gInstance->getHelpManager()->getHelpText(self::HELP_KEY_INITSYSTEM);
			$menuText .= '<li ' . $current . '><a href="'. $this->getUrl($link) .'"><span ' . $helpText . '>データ初期化</span></a></li>' . M3_NL;
			
			// ### DBバックアップ ###
			$current = '';
			$link = $baseUrl . '?task=' . self::TASK_DBBACKUP;
			if ($task == self::TASK_DBBACKUP) $current = 'id="current"';

			// ヘルプを作成
			$helpText = $this->gInstance->getHelpManager()->getHelpText(self::HELP_KEY_DBBACKUP);
			$menuText .= '<li ' . $current . '><a href="'. $this->getUrl($link) .'"><span ' . $helpText . '>バックアップ</span></a></li>' . M3_NL;
		}
		
		// 上段メニュー終了
		$menuText .= '</ul>' . M3_NL;
		$menuText .= '</div>' . M3_NL;
		
		// 作成データの埋め込み
		$linkList = '<div id="configmenu-top"><label>' . self::TASK_NAME_MAIN . ' &gt;&gt;' . $linkList . '</label></div>';
		$outputText .= '<table width="90%"><tr><td>' . $linkList . $menuText . '</td></tr></table>' . M3_NL;
		$this->tmpl->addVar("_widget", "menu_items", $outputText);
	}
}
?>
