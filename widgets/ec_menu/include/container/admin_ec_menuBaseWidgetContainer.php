<?php
/**
 * index.php用コンテナクラス
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2016 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseAdminWidgetContainer.php');

class admin_ec_menuBaseWidgetContainer extends BaseAdminWidgetContainer
{
	// 画面
	const DEFAULT_TASK 			= 'menudef';
	const TASK_MENUDEF			= 'menudef';			// メニュー定義
	const TASK_MENUDEF_DETAIL	= 'menudef_detail';		// メニュー定義(詳細)
	const TASK_OTHER			= 'other';					// その他設定
	
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
		
		// パンくずリストの定義データ作成
		$titles = array();
		switch ($task){
			case self::TASK_MENUDEF:		// メニュー定義
				$titles[] = 'メニュー定義';
				break;
			case self::TASK_MENUDEF_DETAIL:		// メニュー定義(詳細)
				$titles[] = 'メニュー定義';
				$titles[] = '詳細';
				break;
			case self::TASK_OTHER:		// その他設定
				$titles[] = '基本';
				break;
		}
		
		// メニューバーの定義データ作成
		$menu =	array(
					(Object)array(
						'name'		=> 'メニュー定義',	// メニュー定義
						'task'		=> self::TASK_MENUDEF,
						'url'		=> '',
						'tagid'		=> '',
						'active'	=> (
											$task == self::TASK_MENUDEF ||			// メニュー定義
											$task == self::TASK_MENUDEF_DETAIL		// メニュー定義(詳細)
										),
						'submenu'	=> array()
					),
					(Object)array(
						'name'		=> '基本',		// 基本
						'task'		=> self::TASK_OTHER,
						'url'		=> '',
						'tagid'		=> '',
						'active'	=> (
											$task == self::TASK_OTHER			// その他設定
										),
						'submenu'	=> array()
					)
				);

		// サブメニューバーを作成
		$this->setConfigMenubarDef($titles, $menu);

/*
		// ウィンドウオープンタイプ取得
		$openBy = $request->trimValueOf(M3_REQUEST_PARAM_OPEN_BY);		// ウィンドウオープンタイプ
		if (!empty($openBy)) $this->addOptionUrlParam(M3_REQUEST_PARAM_OPEN_BY, $openBy);
				
		// 表示画面を決定
		$task = $request->trimValueOf(M3_REQUEST_PARAM_OPERATION_TASK);
		if (empty($task)) $task = self::DEFAULT_TASK;		// デフォルト画面を設定
		
		// パンくずリストを作成
		switch ($task){
			case self::TASK_MENUDEF:		// メニュー定義
			case self::TASK_MENUDEF_DETAIL:		// メニュー定義詳細
				$linkList = ' &gt;&gt; メニュー定義';// パンくずリスト
				break;
			case self::TASK_OTHER:		// その他設定
				$linkList = ' &gt;&gt; その他';// パンくずリスト
				break;
		}

		// ####### 上段メニューの作成 #######
		$menuText = '<div id="configmenu-upper">' . M3_NL;
		$menuText .= '<ul>' . M3_NL;
	
		$current = '';
		$baseUrl = $this->getAdminUrlWithOptionParam(true);// 画面定義ID付き
	
		// メニュー定義
		$current = '';
		$link = $this->getUrl($baseUrl . '&task=menudef');
		if ($task == self::TASK_MENUDEF ||
			$task == self::TASK_MENUDEF_DETAIL){
			$current = 'id="current"';
		}
		$menuText .= '<li ' . $current . '><a href="'. $this->convertUrlToHtmlEntity($link) .'"><span>メニュー定義</span></a></li>' . M3_NL;
	
		// その他設定
		$current = '';
		$link = $this->getUrl($baseUrl . '&task=other');
		if ($task == self::TASK_OTHER){		// その他設定
			$current = 'id="current"';
		}
		$menuText .= '<li ' . $current . '><a href="'. $this->convertUrlToHtmlEntity($link) .'"><span>その他</span></a></li>' . M3_NL;
	
		// 上段メニュー終了
		$menuText .= '</ul>' . M3_NL;
		$menuText .= '</div>' . M3_NL;
	
		// 作成データの埋め込み
		$linkList = '<div id="configmenu-top"><label>' . '商品メニュー' . $linkList . '</label></div>';
		$outputText .= '<table width="90%"><tr><td>' . $linkList . $menuText . '</td></tr></table>' . M3_NL;
		$this->tmpl->addVar("_widget", "menu_items", $outputText);
*/
	}
}
?>
