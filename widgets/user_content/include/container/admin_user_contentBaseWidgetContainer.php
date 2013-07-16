<?php
/**
 * index.php用コンテナクラス
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    ユーザ作成コンテンツ
 * @author     株式会社 毎日メディアサービス
 * @copyright  Copyright 2010 株式会社 毎日メディアサービス.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: admin_user_contentBaseWidgetContainer.php 3655 2010-10-01 07:16:39Z fishbone $
 * @link       http://www.m-media.co.jp
 */
require_once($gEnvManager->getContainerPath() . '/baseAdminWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/user_contentDb.php');

class admin_user_contentBaseWidgetContainer extends BaseAdminWidgetContainer
{
	protected $_localDb;			// DB接続オブジェクト
	protected $_itemTypeArray;		// コンテンツ項目のデータタイプ
	const DEFAULT_TOP_PAGE = 'content';		// デフォルトのトップページ
	const WIDGET_TITLE_NAME = 'ユーザコンテンツ';				// ウィジェットタイトル名
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->_localDb = new user_contentDb();
		
		// コンテンツ項目タイプ
		$this->_itemTypeArray = array(	array(	'name' => 'HTML',		'value' => '0'),
										array(	'name' => 'テキスト',	'value' => '1'),
										array(	'name' => '数値',		'value' => '2'));
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
		if ($openBy == 'simple') return;			// シンプルウィンドウのときはメニューを表示しない
		
		// 表示画面を決定
		$task = $request->trimValueOf(M3_REQUEST_PARAM_OPERATION_TASK);
		if (empty($task)) $task = self::DEFAULT_TOP_PAGE;
		
		// パンくずリストを作成
		switch ($task){
			case 'content':		// コンテンツ管理
			case 'content_detail':		// コンテンツ管理(詳細)
				$linkList = ' &gt;&gt; コンテンツ管理 &gt;&gt; コンテンツ部品一覧';// パンくずリスト
				break;
			case 'room':		// ルーム管理
			case 'room_detail':		// ルーム管理(詳細)
				$linkList = ' &gt;&gt; ルーム管理 &gt;&gt; ルーム一覧';// パンくずリスト
				break;
			case 'tab':		// タブ定義
			case 'tab_detail':		// タブ定義(詳細)
				$linkList = ' &gt;&gt; 基本設定 &gt;&gt; タブ定義';// パンくずリスト
				break;
			case 'item':		// コンテンツ項目
			case 'item_detail':		// コンテンツ項目(詳細)
				$linkList = ' &gt;&gt; 基本設定 &gt;&gt; コンテンツ部品定義';// パンくずリスト
				break;
			case 'category':		// カテゴリ定義
			case 'category_detail':		// カテゴリ定義(詳細)
				$linkList = ' &gt;&gt; 基本設定 &gt;&gt; カテゴリ定義';// パンくずリスト
				break;
				break;
			case 'other':		// その他設定
				$linkList = ' &gt;&gt; 基本設定 &gt;&gt; その他';// パンくずリスト
				break;
		}
		// ####### 上段メニューの作成 #######
		$menuText = '<div id="configmenu-upper">' . M3_NL;
		$menuText .= '<ul>' . M3_NL;
		$baseUrl = $this->getAdminUrlWithOptionParam(true);// 画面定義ID付き
		
		// コンテンツ管理
		$current = '';
		$link = $baseUrl . '&task=content';
		if ($task == 'content' ||
			$task == 'content_detail'){
			$current = 'id="current"';
		}
		$menuText .= '<li ' . $current . '><a href="'. $this->getUrl($link, true) .'"><span>コンテンツ管理</span></a></li>' . M3_NL;
		
		// ルーム管理
		$current = '';
		$link = $baseUrl . '&task=room';
		if ($task == 'room' ||
			$task == 'room_detail'){
			$current = 'id="current"';
		}
		$menuText .= '<li ' . $current . '><a href="'. $this->getUrl($link, true) .'"><span>ルーム管理</span></a></li>' . M3_NL;
		
		// その他設定
		$current = '';
		$link = $baseUrl . '&task=other';
		if ($task == 'tab' ||		// タブ定義
			$task == 'tab_detail' ||		// タブ定義(詳細)
			$task == 'item' ||		// コンテンツ項目
			$task == 'item_detail' ||		// コンテンツ項目(詳細)
			$task == 'category' ||		// カテゴリ定義
			$task == 'category_detail' ||		// カテゴリ定義(詳細)
			$task == 'other'){		// その他
			$current = 'id="current"';
		}
		$menuText .= '<li ' . $current . '><a href="'. $this->getUrl($link, true) .'"><span>基本設定</span></a></li>' . M3_NL;
		
		// 上段メニュー終了
		$menuText .= '</ul>' . M3_NL;
		$menuText .= '</div>' . M3_NL;
		
		// ####### 下段メニューの作成 #######		
		$menuText .= '<div id="configmenu-lower">' . M3_NL;
		$menuText .= '<ul>' . M3_NL;

		if ($task == 'content' ||
			$task == 'content_detail'){	// コンテンツ管理
			// コンテンツ管理
			$current = '';
			$link = $baseUrl . '&task=content';
			if ($task == 'content' || $task == 'content_detail') $current = 'id="current"';
			$menuText .= '<li ' . $current . '><a href="'. $this->getUrl($link, true) .'"><span>コンテンツ部品一覧</span></a></li>' . M3_NL;
		} else if ($task == 'room' ||
			$task == 'room_detail'){	// ルーム管理
			// ルーム管理
			$current = '';
			$link = $baseUrl . '&task=room';
			if ($task == 'room' || $task == 'room_detail') $current = 'id="current"';
			$menuText .= '<li ' . $current . '><a href="'. $this->getUrl($link, true) .'"><span>ルーム一覧</span></a></li>' . M3_NL;
		} else if ($task == 'tab' ||		// タブ定義
			$task == 'tab_detail' ||		// タブ定義(詳細)
			$task == 'item' ||		// コンテンツ項目
			$task == 'item_detail' ||		// コンテンツ項目(詳細)
			$task == 'category' ||		// カテゴリ定義
			$task == 'category_detail' ||		// カテゴリ定義(詳細)
			$task == 'other'){		// その他
			
			// コンテンツ項目
			$current = '';
			$link = $baseUrl . '&task=item';
			if ($task == 'item' || $task == 'item_detail') $current = 'id="current"';
			$menuText .= '<li ' . $current . '><a href="'. $this->getUrl($link, true) .'"><span>コンテンツ部品定義</span></a></li>' . M3_NL;
			
			// タブ定義
			$current = '';
			$link = $baseUrl . '&task=tab';
			if ($task == 'tab' || $task == 'tab_detail') $current = 'id="current"';
			$menuText .= '<li ' . $current . '><a href="'. $this->getUrl($link, true) .'"><span>タブ定義</span></a></li>' . M3_NL;
			
			// カテゴリ定義
			$current = '';
			$link = $baseUrl . '&task=category';
			if ($task == 'category' || $task == 'category_detail') $current = 'id="current"';
			$menuText .= '<li ' . $current . '><a href="'. $this->getUrl($link, true) .'"><span>カテゴリ定義</span></a></li>' . M3_NL;
			
			// その他設定
			$current = '';
			$link = $baseUrl . '&task=other';
			if ($task == 'other') $current = 'id="current"';
			$menuText .= '<li ' . $current . '><a href="'. $this->getUrl($link, true) .'"><span>その他</span></a></li>' . M3_NL;
		}
		
		// 下段メニュー終了
		$menuText .= '</ul>' . M3_NL;
		$menuText .= '</div>' . M3_NL;
		
		// 作成データの埋め込み
		$linkList = '<div id="configmenu-top"><label>' . self::WIDGET_TITLE_NAME . $linkList . '</div>';
		$outputText .= '<table width="90%"><tr><td>' . $linkList . $menuText . '</td></tr></table>' . M3_NL;
		$this->tmpl->addVar("_widget", "menu_items", $outputText);
	}
}
?>
