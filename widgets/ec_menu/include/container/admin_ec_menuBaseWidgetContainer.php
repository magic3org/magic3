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
 * @copyright  Copyright 2006-2012 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: admin_ec_menuBaseWidgetContainer.php 5562 2013-01-18 03:49:58Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseAdminWidgetContainer.php');

class admin_ec_menuBaseWidgetContainer extends BaseAdminWidgetContainer
{
	const DEFAULT_TASK = 'menudef';
	
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
		// ウィンドウオープンタイプ取得
		$openBy = $request->trimValueOf(M3_REQUEST_PARAM_OPEN_BY);		// ウィンドウオープンタイプ
		if (!empty($openBy)) $this->addOptionUrlParam(M3_REQUEST_PARAM_OPEN_BY, $openBy);
				
		// 表示画面を決定
		$task = $request->trimValueOf(M3_REQUEST_PARAM_OPERATION_TASK);
		if (empty($task)) $task = self::DEFAULT_TASK;		// デフォルト画面を設定
		
		// パンくずリストを作成
		switch ($task){
			case 'menudef':		// メニュー定義
			case 'menudef_detail':		// メニュー定義詳細
				$linkList = ' &gt;&gt; メニュー定義';// パンくずリスト
				break;
			case 'other':		// その他
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
		if ($task == 'menudef' ||
			$task == 'menudef_detail'){
			$current = 'id="current"';
		}
		$menuText .= '<li ' . $current . '><a href="'. $this->convertUrlToHtmlEntity($link) .'"><span>メニュー定義</span></a></li>' . M3_NL;
	
		// その他設定
		$current = '';
		$link = $this->getUrl($baseUrl . '&task=other');
		if ($task == 'other'){		// その他設定
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
	}
}
?>
