<?php
/**
 * コンテナクラス
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    ポータル用コンテンツ更新情報
 * @author     株式会社 毎日メディアサービス
 * @copyright  Copyright 2009 株式会社 毎日メディアサービス.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: admin_portal_updateinfoBaseWidgetContainer.php 2716 2009-12-19 07:24:50Z fishbone $
 * @link       http://www.m-media.co.jp
 */
require_once($gEnvManager->getContainerPath() . '/baseAdminWidgetContainer.php');

class admin_portal_updateinfoBaseWidgetContainer extends BaseAdminWidgetContainer
{
	const DEFAULT_TOP_PAGE = 'news';		// デフォルトのトップページ
	const WIDGET_TITLE_NAME = 'コンテンツ更新情報';				// ウィジェットタイトル名
	
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
		if (empty($task)) $task = self::DEFAULT_TOP_PAGE;
		
		// パンくずリストを作成
		switch ($task){
			case 'news':		// 更新情報管理
			case 'news_detail':		// 更新情報管理(詳細)
				$linkList = ' &gt;&gt; 更新情報管理';// パンくずリスト
				break;
			case 'other':		// その他設定
				$linkList = ' &gt;&gt; 基本設定';// パンくずリスト
				break;
		}
		// ####### 上段メニューの作成 #######
		$menuText = '<div id="configmenu-upper">' . M3_NL;
		$menuText .= '<ul>' . M3_NL;
		$baseUrl = $this->getAdminUrlWithOptionParam(true);// 画面定義ID付き
		
		// 更新情報管理
		$current = '';
		$link = $baseUrl . '&task=news';
		if ($task == 'news' ||
			$task == 'news_detail'){
			$current = 'id="current"';
		}
		$menuText .= '<li ' . $current . '><a href="'. $this->getUrl($link, true) .'"><span>更新情報管理</span></a></li>' . M3_NL;
		
		// その他設定
		$current = '';
		$link = $baseUrl . '&task=other';
		if ($task == 'other'){		
			$current = 'id="current"';
		}
		$menuText .= '<li ' . $current . '><a href="'. $this->getUrl($link, true) .'"><span>基本設定</span></a></li>' . M3_NL;
		
		// 上段メニュー終了
		$menuText .= '</ul>' . M3_NL;
		$menuText .= '</div>' . M3_NL;
		
		// 作成データの埋め込み
		$linkList = '<div id="configmenu-top"><label>' . self::WIDGET_TITLE_NAME . $linkList . '</div>';
		$outputText .= '<table width="90%"><tr><td>' . $linkList . $menuText . '</td></tr></table>' . M3_NL;
		$this->tmpl->addVar("_widget", "menu_items", $outputText);
	}
}
?>
