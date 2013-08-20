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
 * @copyright  Copyright 2006-2013 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseAdminWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetContainerPath() . '/blog_mainCommonDef.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/blog_mainDb.php');

class admin_blog_mainBaseWidgetContainer extends BaseAdminWidgetContainer
{
	protected static $_mainDb;			// DB接続オブジェクト
	protected static $_configArray;		// ブログ定義値
	const DEFAULT_COMMENT_LENGTH	= 300;				// デフォルトのコメント最大文字数
	const DEFAULT_CATEGORY_COUNT	= 2;				// デフォルトのカテゴリ数
	
	// カレンダー用スクリプト
	const CALENDAR_SCRIPT_FILE = '/jscalendar-1.0/calendar.js';		// カレンダースクリプトファイル
	const CALENDAR_LANG_FILE = '/jscalendar-1.0/lang/calendar-ja.js';	// カレンダー言語ファイル
	const CALENDAR_SETUP_FILE = '/jscalendar-1.0/calendar-setup.js';	// カレンダーセットアップファイル
	const CALENDAR_CSS_FILE = '/jscalendar-1.0/calendar-win2k-1.css';		// カレンダー用CSSファイル
	// 画面
	const TASK_CONFIG = 'config';								// その他設定
	const TASK_HISTORY = 'history';							// ブログ記事履歴
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		if (!isset(self::$_mainDb)) self::$_mainDb = new blog_mainDb();
		
		// ブログ定義を読み込む
		if (!isset(self::$_configArray)) self::$_configArray = blog_mainCommonDef::loadConfig(self::$_mainDb);
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
		$openBy = $request->trimValueOf(M3_REQUEST_PARAM_OPEN_BY);		// ウィンドウオープンタイプ
		if (!empty($openBy)) $this->addOptionUrlParam(M3_REQUEST_PARAM_OPEN_BY, $openBy);
		if ($openBy == 'simple') return;			// シンプルウィンドウのときはメニューを表示しない
		
		// 表示画面を決定
		$task = $request->trimValueOf(M3_REQUEST_PARAM_OPERATION_TASK);
		if (empty($task)) $task = 'entry';
		
		// パンくずリストを作成
		switch ($task){
			case 'entry':		// ブログ記事
			case 'entry_detail':	// ブログ記事詳細
			case self::TASK_HISTORY:							// ブログ記事履歴
				$linkList = ' &gt;&gt; ブログ記事 &gt;&gt; 記事一覧';// パンくずリスト
				break;
			case 'comment':		// ブログ記事コメント
			case 'comment_detail':	// ブログ記事コメント
				$linkList = ' &gt;&gt; ブログ記事 &gt;&gt; コメント一覧';// パンくずリスト
				break;
			case 'user':		// ユーザ管理
			case 'user_detail':		// ユーザ管理(詳細)
				$linkList = ' &gt;&gt; ユーザ管理 &gt;&gt; ユーザ一覧';// パンくずリスト
				break;
			case self::TASK_CONFIG:		// ブログ設定
				$linkList = ' &gt;&gt; 基本設定 &gt;&gt; ブログ設定';// パンくずリスト
				break;
			case 'category':		// カテゴリ設定
			case 'category_detail':		// カテゴリ設定
				$linkList = ' &gt;&gt; 基本設定 &gt;&gt; カテゴリ';// パンくずリスト
				break;
			case 'blogid':		// マルチブログ設定
			case 'blogid_detail':		// マルチブログ設定
				$linkList = ' &gt;&gt; 基本設定 &gt;&gt; マルチブログ';// パンくずリスト
				break;
		}

		// ####### 上段メニューの作成 #######
		$menuText = '<div id="configmenu-upper">' . M3_NL;
		$menuText .= '<ul>' . M3_NL;
		
		$current = '';
		$baseUrl = $this->getAdminUrlWithOptionParam();
		
		// ブログ記事管理
		$current = '';
		$link = $baseUrl . '&task=entry';
		if ($task == 'entry' ||
			$task == 'entry_detail' ||
			$task == self::TASK_HISTORY ||	// ブログ記事履歴
			$task == 'comment' ||		// ブログ記事コメント管理
			$task == 'comment_detail'){							
			$current = 'id="current"';
		}
		$menuText .= '<li ' . $current . '><a href="'. $this->getUrl($link) .'"><span>ブログ記事</span></a></li>' . M3_NL;
		
		// 基本設定
		$current = '';
		$link = $baseUrl . '&task=category';
		if ($task == 'category' ||		// カテゴリ設定
			$task == 'category_detail' ||		// カテゴリ設定
			$task == 'blogid' ||		// マルチブログ
			$task == 'blogid_detail' ||		// マルチブログ詳細
			$task == self::TASK_CONFIG){		// ブログ設定
			$current = 'id="current"';
		}
		$menuText .= '<li ' . $current . '><a href="'. $this->getUrl($link) .'"><span>基本設定</span></a></li>' . M3_NL;
		
		// 上段メニュー終了
		$menuText .= '</ul>' . M3_NL;
		$menuText .= '</div>' . M3_NL;
		
		// ####### 下段メニューの作成 #######		
		$menuText .= '<div id="configmenu-lower">' . M3_NL;
		$menuText .= '<ul>' . M3_NL;

		if ($task == 'entry' ||		// ブログ記事管理
			$task == 'entry_detail' ||
			$task == self::TASK_HISTORY ||	// ブログ記事履歴
			$task == 'comment' ||		// ブログ記事コメント管理
			$task == 'comment_detail'){
			
			// ブログ記事一覧
			$current = '';
			$link = $baseUrl . '&task=entry';
			if ($task == 'entry' || $task == 'entry_detail' || $task == self::TASK_HISTORY) $current = 'id="current"';
			$menuText .= '<li ' . $current . '><a href="'. $this->getUrl($link) .'"><span>記事一覧</span></a></li>' . M3_NL;
			
			// ブログ記事コメント一覧
			$current = '';
			$link = $baseUrl . '&task=comment';
			if ($task == 'comment' || $task == 'comment_detail') $current = 'id="current"';
			$menuText .= '<li ' . $current . '><a href="'. $this->getUrl($link) .'"><span>コメント一覧</span></a></li>' . M3_NL;
		} else if ($task == 'category' ||		// カテゴリ設定
			$task == 'category_detail' ||		// カテゴリ設定
			$task == 'blogid' ||		// マルチブログ
			$task == 'blogid_detail' ||		// マルチブログ詳細
			$task == self::TASK_CONFIG){		// ブログ設定
			
			// カテゴリ設定
			$current = '';
			$link = $baseUrl . '&task=category';
			if ($task == 'category' || $task == 'category_detail') $current = 'id="current"';
			$menuText .= '<li ' . $current . '><a href="'. $this->getUrl($link) .'"><span>カテゴリ</span></a></li>' . M3_NL;
			
			// マルチブログ
			$current = '';
			$link = $baseUrl . '&task=blogid';
			if ($task == 'blogid' || $task == 'blogid_detail') $current = 'id="current"';
			$menuText .= '<li ' . $current . '><a href="'. $this->getUrl($link) .'"><span>マルチブログ</span></a></li>' . M3_NL;
			
			// その他設定
			$current = '';
			$link = $baseUrl . '&task=config';
			if ($task == self::TASK_CONFIG) $current = 'id="current"';
			$menuText .= '<li ' . $current . '><a href="'. $this->getUrl($link) .'"><span>ブログ設定</span></a></li>' . M3_NL;
		}
		
		// 下段メニュー終了
		$menuText .= '</ul>' . M3_NL;
		$menuText .= '</div>' . M3_NL;

		// 作成データの埋め込み
		$linkList = '<div id="configmenu-top"><label>' . 'ブログ' . $linkList . '</label></div>';
		$outputText .= '<table width="90%"><tr><td>' . $linkList . $menuText . '</td></tr></table>' . M3_NL;
		$this->tmpl->addVar("_widget", "menu_items", $outputText);
	}
}
?>
