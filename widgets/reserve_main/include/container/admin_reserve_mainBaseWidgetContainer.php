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
 * @copyright  Copyright 2006-2009 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: admin_reserve_mainBaseWidgetContainer.php 2458 2009-10-24 07:26:44Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseAdminWidgetContainer.php');

class admin_reserve_mainBaseWidgetContainer extends BaseAdminWidgetContainer
{
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
		global $gEnvManager;

		$openBy = $request->trimValueOf(M3_REQUEST_PARAM_OPEN_BY);		// ウィンドウオープンタイプ
		if (!empty($openBy)) $this->addOptionUrlParam(M3_REQUEST_PARAM_OPEN_BY, $openBy);
				
		// 表示画面を決定
		$task = $request->trimValueOf(M3_REQUEST_PARAM_OPERATION_TASK);
		if (empty($task)) $task = 'reserve';
		
		// パンくずリストを作成
		switch ($task){
/*			case 'top';			// トップ画面
				$linkList = '';// パンくずリスト
				break;*/
			case 'reserve':		// 予約管理
			case 'reserve_detail':		// 予約管理(詳細)
				$linkList = ' &gt;&gt; 予約管理 &gt;&gt; 予約一覧';// パンくずリスト
				break;
			case 'user':		// ユーザ管理
			case 'user_detail':		// ユーザ管理(詳細)
				$linkList = ' &gt;&gt; ユーザ管理 &gt;&gt; ユーザ一覧';// パンくずリスト
				break;
			case 'calendar':		// カレンダー設定
				$linkList = ' &gt;&gt; 基本設定 &gt;&gt; カレンダー設定';// パンくずリスト
				break;
			case 'resource':		// リソース設定
			case 'resource_detail':// リソース設定
				$linkList = ' &gt;&gt; 基本設定 &gt;&gt; 予約対象設定';// パンくずリスト
				break;
			case 'other':		// その他設定
				$linkList = ' &gt;&gt; 基本設定 &gt;&gt; その他';// パンくずリスト
				break;
		}

		// ####### 上段メニューの作成 #######
		$menuText = '<div id="configmenu-upper">' . M3_NL;
		$menuText .= '<ul>' . M3_NL;
		
		$current = '';
		$baseUrl = $this->getAdminUrlWithOptionParam();
		
		// 予約管理
		$current = '';
		$link = $baseUrl . '&task=reserve';
		if ($task == 'reserve' ||
			$task == 'reserve_detail'){
			$current = 'id="current"';
		}
		$menuText .= '<li ' . $current . '><a href="'. $link .'"><span>予約管理</span></a></li>' . M3_NL;
		
		// ユーザ管理
		$current = '';
		$link = $baseUrl . '&task=user';
		if ($task == 'user' ||
			$task == 'user_detail'){
			$current = 'id="current"';
		}
		$menuText .= '<li ' . $current . '><a href="'. $link .'"><span>ユーザ管理</span></a></li>' . M3_NL;
		
		// 基本設定
		$current = '';
		$link = $baseUrl . '&task=calendar';
		if ($task == 'calendar' ||		// カレンダー設定
			$task == 'resource' ||		// リソース設定
			$task == 'resource_detail' ||	// リソース設定詳細
			$task == 'other'){		// その他設定
			$current = 'id="current"';
		}
		$menuText .= '<li ' . $current . '><a href="'. $link .'"><span>基本設定</span></a></li>' . M3_NL;
		
		// 上段メニュー終了
		$menuText .= '</ul>' . M3_NL;
		$menuText .= '</div>' . M3_NL;
		
		// ####### 下段メニューの作成 #######		
		$menuText .= '<div id="configmenu-lower">' . M3_NL;
		$menuText .= '<ul>' . M3_NL;

		if ($task == 'reserve' ||
			$task == 'reserve_detail'){	// 予約管理
			// 予約一覧
			$current = '';
			$link = $baseUrl . '&task=reserve';
			if ($task == 'reserve') $current = 'id="current"';
			$menuText .= '<li ' . $current . '><a href="'. $link .'"><span>予約一覧</span></a></li>' . M3_NL;
		} else if ($task == 'user' ||
			$task == 'user_detail'){	// ユーザ管理
			// ユーザ一覧
			$current = '';
			$link = $baseUrl . '&task=user';
			if ($task == 'user') $current = 'id="current"';
			$menuText .= '<li ' . $current . '><a href="'. $link .'"><span>ユーザ一覧</span></a></li>' . M3_NL;
		} else if ($task == 'calendar' ||		// カレンダー設定
			$task == 'resource' ||		// リソース設定
			$task == 'resource_detail' ||	// リソース設定詳細
			$task == 'other'){		// その他設定
			
			// カレンダー設定
			$current = '';
			$link = $baseUrl . '&task=calendar';
			if ($task == 'calendar' || $task == 'calendar_detail') $current = 'id="current"';
			$menuText .= '<li ' . $current . '><a href="'. $link .'"><span>カレンダー設定</span></a></li>' . M3_NL;
		
			// リソース設定
			$current = '';
			$link = $baseUrl . '&task=resource';
			if ($task == 'resource' || $task == 'resource_detail') $current = 'id="current"';
			$menuText .= '<li ' . $current . '><a href="'. $link .'"><span>予約対象設定</span></a></li>' . M3_NL;
			
			// その他設定
			$current = '';
			$link = $baseUrl . '&task=other';
			if ($task == 'other') $current = 'id="current"';
			$menuText .= '<li ' . $current . '><a href="'. $link .'"><span>その他</span></a></li>' . M3_NL;
		}
		
		// 下段メニュー終了
		$menuText .= '</ul>' . M3_NL;
		$menuText .= '</div>' . M3_NL;

		// 作成データの埋め込み
		$linkList = '<div id="configmenu-top"><label>' . '予約' . $linkList . '</div>';
		$outputText .= '<table width="90%"><tr><td>' . $linkList . $menuText . '</td></tr></table>' . M3_NL;
		$this->tmpl->addVar("_widget", "menu_items", $outputText);
	}
}
?>
