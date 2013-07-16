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
 * @copyright  Copyright 2006-2009 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: admin_m_quizkBaseWidgetContainer.php 2458 2009-10-24 07:26:44Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseAdminWidgetContainer.php');

class admin_m_quizkBaseWidgetContainer extends BaseAdminWidgetContainer
{
	const DEFAULT_TASK = 'operation';		// デフォルトの画面

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
		$createList = true;		// パンくずリストを作成するかどうか
		switch ($task){
			case 'operation':			// 運用管理
				$linkList = ' &gt;&gt; 運用管理';// パンくずリスト
				break;
			case 'csv':		// アンケートデータアップロード
				$linkList = ' &gt;&gt; CSVデータ';// パンくずリスト
				break;
			case 'total':			// 集計画面
				$linkList = ' &gt;&gt; 集計';// パンくずリスト
				break;
			default:
				break;
		}

		if ($createList){				// パンくずリストを作成するとき
			// ####### 上段メニューの作成 #######
			$menuText = '<div id="configmenu-upper">' . M3_NL;
			$menuText .= '<ul>' . M3_NL;
		
			$current = '';
			$baseUrl = $this->getAdminUrlWithOptionParam(true);// 画面定義ID付き
		
			// 運用管理
			$current = '';
			$link = $baseUrl . '&task=operation';
			if ($task == 'operation'){
				$current = 'id="current"';
			}
			$menuText .= '<li ' . $current . '><a href="'. $this->convertUrlToHtmlEntity($link) .'"><span>運用管理</span></a></li>' . M3_NL;
			
			// CSVデータ管理
			$current = '';
			$link = $baseUrl . '&task=csv';
			if ($task == 'csv'){
				$current = 'id="current"';
			}
			$menuText .= '<li ' . $current . '><a href="'. $this->convertUrlToHtmlEntity($link) .'"><span>CSVデータ</span></a></li>' . M3_NL;
			
			// 集計
			/*$current = '';
			$link = $baseUrl . '&task=total';
			if ($task == 'total'){
				$current = 'id="current"';
			}
			$menuText .= '<li ' . $current . '><a href="'. $this->convertUrlToHtmlEntity($link) .'"><span>集計</span></a></li>' . M3_NL;
			*/
			
			// 上段メニュー終了
			$menuText .= '</ul>' . M3_NL;
			$menuText .= '</div>' . M3_NL;
		
			// 作成データの埋め込み
			$linkList = '<div id="configmenu-top"><label>' . '携帯クイズ' . $linkList . '</div>';
			$outputText .= '<table width="90%"><tr><td>' . $linkList . $menuText . '</td></tr></table>' . M3_NL;
			$this->tmpl->addVar("_widget", "menu_items", $outputText);
		} else {
			$this->tmpl->addVar("_widget", "menu_items", '');
		}
	}
}
?>
