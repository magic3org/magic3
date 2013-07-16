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
 * @copyright  Copyright 2006-2011 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: admin_s_photoBaseWidgetContainer.php 4716 2012-02-26 02:19:15Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() . '/photoCommonDef.php');
require_once($gEnvManager->getContainerPath() . '/baseAdminWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/s_photoDb.php');

class admin_s_photoBaseWidgetContainer extends BaseAdminWidgetContainer
{
	protected static $_mainDb;			// DB接続オブジェクト
	protected static $_configArray;		// BBS定義値
	protected static $_isLimitedUser;		// 使用制限ユーザ(画像投稿者)かどうか
	protected $_openBy;				// ウィンドウオープンタイプ
	protected $_baseUrl;			// 管理画面のベースURL
	protected $_langId;			// 現在の言語
	protected $_userId;			// 現在のユーザ
	const DEFAULT_TASK = 'config';			// デフォルトの画面
	
	// カレンダー用スクリプト
	const CALENDAR_SCRIPT_FILE = '/jscalendar-1.0/calendar.js';		// カレンダースクリプトファイル
	const CALENDAR_LANG_FILE = '/jscalendar-1.0/lang/calendar-ja.js';	// カレンダー言語ファイル
	const CALENDAR_SETUP_FILE = '/jscalendar-1.0/calendar-setup.js';	// カレンダーセットアップファイル
	const CALENDAR_CSS_FILE = '/jscalendar-1.0/calendar-win2k-1.css';		// カレンダー用CSSファイル
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		if (!isset(self::$_mainDb)) self::$_mainDb = new s_photoDb();
			
		// ブログ定義を読み込む
		if (!isset(self::$_configArray)) self::$_configArray = photoCommonDef::loadConfig(self::$_mainDb);
		
		// システム運用者の場合は、ユーザオプションがあればユーザ専用ディレクトリに制限
		if (!isset(self::$_isLimitedUser)){
			$ret = $this->gEnv->hasUserTypeOption(photoCommonDef::USER_OPTION);
			if ($ret){
				self::$_isLimitedUser = true;		// 使用制限ユーザ(画像投稿者)かどうか
			} else {
				self::$_isLimitedUser = false;
			}
		}
		
		// 変数初期化
		$this->_langId	= $this->gEnv->getCurrentLanguage();		// 表示言語を取得
		$this->_userId = $this->gEnv->getCurrentUserId();
	}
	/**
	 * テンプレートに前処理
	 *
	 * _setTemplate()で指定したテンプレートファイルにデータを埋め込む。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。_setTemplate()と共有。
	 * @return								なし
	 */
	function _preAssign($request, &$param)
	{
		$this->openBy = $request->trimValueOf(M3_REQUEST_PARAM_OPEN_BY);		// ウィンドウオープンタイプ
		if (!empty($this->openBy)) $this->addOptionUrlParam(M3_REQUEST_PARAM_OPEN_BY, $this->openBy);
		
		// 管理画面ペースURL取得
		$this->_baseUrl = $this->getAdminUrlWithOptionParam();
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
		if ($this->openBy == 'simple') return;			// シンプルウィンドウのときはメニューを表示しない
		
		// 使用限定ユーザの場合はメニュー表示しない
		if (self::$_isLimitedUser) return;
		
		// 表示画面を決定
		$task = $request->trimValueOf(M3_REQUEST_PARAM_OPERATION_TASK);
		if (empty($task)) $task = self::DEFAULT_TASK;
		
		// パンくずリストを作成
		switch ($task){
/*			case 'search':		// 検索条件
				$linkList = ' &gt;&gt; 基本設定 &gt;&gt; 検索条件';// パンくずリスト
				break;
				*/
			case 'config':		// フォトギャラリー設定
				$linkList = ' &gt;&gt; 基本設定 &gt;&gt; フォトギャラリー設定';// パンくずリスト
				break;
		}

		// ####### 上段メニューの作成 #######
		$menuText = '<div id="configmenu-upper">' . M3_NL;
		$menuText .= '<ul>' . M3_NL;
		
		$current = '';

		// 基本設定
		$current = '';
		$link = $this->_baseUrl . '&task=config';
		if ($task == 'search' ||		// 検索条件
			$task == 'config'){		// ブログ設定
			$current = 'id="current"';
		}
		$menuText .= '<li ' . $current . '><a href="'. $this->getUrl($link) .'"><span>基本設定</span></a></li>' . M3_NL;
		
		// 上段メニュー終了
		$menuText .= '</ul>' . M3_NL;
		$menuText .= '</div>' . M3_NL;
		
		// ####### 下段メニューの作成 #######		
		$menuText .= '<div id="configmenu-lower">' . M3_NL;
		$menuText .= '<ul>' . M3_NL;

		if ($task == 'search' ||		// 検索条件
			$task == 'config'){		// ブログ設定
			
			// 検索条件
/*			$current = '';
			$link = $this->_baseUrl . '&task=search';
			if ($task == 'search') $current = 'id="current"';
			$menuText .= '<li ' . $current . '><a href="'. $this->getUrl($link) .'"><span>検索条件</span></a></li>' . M3_NL;
			*/
			// その他設定
			$current = '';
			$link = $this->_baseUrl . '&task=config';
			if ($task == 'config') $current = 'id="current"';
			$menuText .= '<li ' . $current . '><a href="'. $this->getUrl($link) .'"><span>フォトギャラリー設定</span></a></li>' . M3_NL;
		}
		
		// 下段メニュー終了
		$menuText .= '</ul>' . M3_NL;
		$menuText .= '</div>' . M3_NL;

		// 作成データの埋め込み
		$linkList = '<div id="configmenu-top"><label>' . 'フォトギャラリー' . $linkList . '</div>';
		$outputText .= '<table width="90%"><tr><td>' . $linkList . $menuText . '</td></tr></table>' . M3_NL;
		$this->tmpl->addVar("_widget", "menu_items", $outputText);
	}
}
?>
