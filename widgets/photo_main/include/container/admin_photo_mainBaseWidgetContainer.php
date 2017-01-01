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
 * @copyright  Copyright 2006-2017 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() . '/photo_mainCommonDef.php');
require_once($gEnvManager->getContainerPath() . '/baseAdminWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/photo_mainDb.php');

class admin_photo_mainBaseWidgetContainer extends BaseAdminWidgetContainer
{
	protected static $_mainDb;			// DB接続オブジェクト
	protected static $_configArray;		// BBS定義値
	protected static $_isLimitedUser;		// 使用制限ユーザ(画像投稿者)かどうか
//	protected $_openBy;				// ウィンドウオープンタイプ
	protected $_baseUrl;			// 管理画面のベースURL
//	protected $_langId;			// 現在の言語
//	protected $_userId;			// 現在のユーザ
	protected $_errMessage;		// エラーメッセージ
	// 画面
	const TASK_IMAGEBROWSE			= 'imagebrowse';		// 画像管理
	const TASK_IMAGEBROWSE_DETAIL	= 'imagebrowse_detail';	// 画像管理詳細
	const TASK_IMAGEBROWSE_DIRECT	= 'imagebrowse_direct';	// 画像取得
	const TASK_COMMENT				= 'comment';		// 画像コメント
	const TASK_COMMENT_DETAIL		= 'comment_detail';	// 画像コメント
	const TASK_AUTHOER				= 'author';		// 画像管理者一覧
	const TASK_AUTHER_DETAIL		= 'author_detail';	// 画像管理者詳細
	const TASK_CATEGORY				= 'category';		// 画像カテゴリー一覧
	const TASK_CATEGORY_DETAIL		= 'category_detail';	// 画像カテゴリー詳細
	const TASK_SEARCH				= 'search';		// 検索条件
	const TASK_CONFIG				= 'config';		// フォトギャラリー設定
	const DEFAULT_TASK = 'imagebrowse';			// デフォルトの画面
	
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
		if (!isset(self::$_mainDb)) self::$_mainDb = new photo_mainDb();
			
		// ブログ定義を読み込む
		if (!isset(self::$_configArray)) self::$_configArray = photo_mainCommonDef::loadConfig(self::$_mainDb);
		
		// システム運用者の場合は、ユーザオプションがあればユーザ専用ディレクトリに制限
		if (!isset(self::$_isLimitedUser)){
			$ret = $this->gEnv->hasUserTypeOption(photo_mainCommonDef::USER_OPTION);
			if ($ret){
				self::$_isLimitedUser = true;		// 使用制限ユーザ(画像投稿者)かどうか
			} else {
				self::$_isLimitedUser = false;
			}
		}
		
		// 変数初期化
//		$this->_langId	= $this->gEnv->getCurrentLanguage();		// 表示言語を取得
//		$this->_userId = $this->gEnv->getCurrentUserId();
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
		$openBy = $request->trimValueOf(M3_REQUEST_PARAM_OPEN_BY);		// ウィンドウオープンタイプ
		if (!empty($openBy)) $this->addOptionUrlParam(M3_REQUEST_PARAM_OPEN_BY, $openBy);
		if ($openBy == 'simple') return;			// シンプルウィンドウのときはメニューを表示しない
		
		// 使用限定ユーザの場合はメニュー表示しない
		if (self::$_isLimitedUser) return;
		
		// 表示画面を決定
		$task = $request->trimValueOf(M3_REQUEST_PARAM_OPERATION_TASK);
		if (empty($task)) $task = self::DEFAULT_TASK;
		
		// 画像取得の場合は終了
		if ($task == self::TASK_IMAGEBROWSE_DIRECT) return;
		
		// パンくずリストを作成
		switch ($task){
			case self::TASK_IMAGEBROWSE:		// 画像管理
			case self::TASK_IMAGEBROWSE_DETAIL:	// 画像管理詳細
				$linkList = ' &gt;&gt; 画像管理 &gt;&gt; 画像一覧';// パンくずリスト
				break;
			case self::TASK_COMMENT:		// 画像コメント
			case self::TASK_COMMENT_DETAIL:	// 画像コメント
				$linkList = ' &gt;&gt; 画像管理 &gt;&gt; コメント一覧';// パンくずリスト
				break;
			case self::TASK_AUTHOER:		// 画像管理者一覧
			case self::TASK_AUTHER_DETAIL:	// 画像管理者詳細
				$linkList = ' &gt;&gt; 基本設定 &gt;&gt; 画像管理者一覧';// パンくずリスト
				break;
			case self::TASK_CATEGORY:		// 画像カテゴリー一覧
			case self::TASK_CATEGORY_DETAIL:	// 画像カテゴリー詳細
				$linkList = ' &gt;&gt; 基本設定 &gt;&gt; 画像カテゴリー一覧';// パンくずリスト
				break;
			case self::TASK_SEARCH:		// 検索条件
				$linkList = ' &gt;&gt; 基本設定 &gt;&gt; 検索条件';// パンくずリスト
				break;
			case self::TASK_CONFIG:		// フォトギャラリー設定
				$linkList = ' &gt;&gt; 基本設定 &gt;&gt; フォトギャラリー設定';// パンくずリスト
				break;
		}

		// ####### 上段メニューの作成 #######
		$menuText = '<div id="configmenu-upper">' . M3_NL;
		$menuText .= '<ul>' . M3_NL;
		
		$current = '';
		
		// 画像管理
		$current = '';
		$link = $this->_baseUrl . '&task=imagebrowse';
		if ($task == self::TASK_IMAGEBROWSE ||
			$task == self::TASK_IMAGEBROWSE_DETAIL ||
			$task == self::TASK_COMMENT ||		// 画像コメント一覧
			$task == self::TASK_COMMENT_DETAIL){		// 画像コメント詳細
			$current = 'id="current"';
		}
		$menuText .= '<li ' . $current . '><a href="'. $this->getUrl($link) .'"><span>画像管理</span></a></li>' . M3_NL;
		
		// 基本設定
		$current = '';
		$link = $this->_baseUrl . '&task=author';
		if ($task == self::TASK_AUTHOER ||		// 画像管理者一覧
			$task == self::TASK_AUTHER_DETAIL ||		// 画像管理者詳細
			$task == self::TASK_CATEGORY ||		// 画像カテゴリー一覧
			$task == self::TASK_CATEGORY_DETAIL ||		// 画像カテゴリー詳細
			$task == self::TASK_SEARCH ||		// 検索条件
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

		if ($task == self::TASK_IMAGEBROWSE ||
			$task == self::TASK_IMAGEBROWSE_DETAIL ||
			$task == self::TASK_COMMENT ||		// 画像コメント一覧
			$task == self::TASK_COMMENT_DETAIL){		// 画像コメント詳細
			
			// 画像一覧
			$current = '';
			$link = $this->_baseUrl . '&task=imagebrowse';
			if ($task == self::TASK_IMAGEBROWSE || $task == self::TASK_IMAGEBROWSE_DETAIL) $current = 'id="current"';
			$menuText .= '<li ' . $current . '><a href="'. $this->getUrl($link) .'"><span>画像一覧</span></a></li>' . M3_NL;
			
			// 画像コメント一覧
			$current = '';
			$link = $this->_baseUrl . '&task=comment';
			if ($task == self::TASK_COMMENT || $task == self::TASK_COMMENT_DETAIL) $current = 'id="current"';
			$menuText .= '<li ' . $current . '><a href="'. $this->getUrl($link) .'"><span>コメント一覧</span></a></li>' . M3_NL;
		} else if ($task == self::TASK_AUTHOER ||		// 画像管理者一覧
			$task == self::TASK_AUTHER_DETAIL ||		// 画像管理者詳細
			$task == self::TASK_CATEGORY ||		// 画像カテゴリー一覧
			$task == self::TASK_CATEGORY_DETAIL ||		// 画像カテゴリー詳細
			$task == self::TASK_SEARCH ||		// 検索条件
			$task == self::TASK_CONFIG){		// ブログ設定

			// 画像管理者
			$current = '';
			$link = $this->_baseUrl . '&task=author';
			if ($task == self::TASK_AUTHOER || $task == self::TASK_AUTHER_DETAIL) $current = 'id="current"';
			$menuText .= '<li ' . $current . '><a href="'. $this->getUrl($link) .'"><span>画像管理者</span></a></li>' . M3_NL;
			
			// 画像カテゴリー
			$current = '';
			$link = $this->_baseUrl . '&task=category';
			if ($task == self::TASK_CATEGORY || $task == self::TASK_CATEGORY_DETAIL) $current = 'id="current"';
			$menuText .= '<li ' . $current . '><a href="'. $this->getUrl($link) .'"><span>画像カテゴリー</span></a></li>' . M3_NL;
			
			// 検索条件
			$current = '';
			$link = $this->_baseUrl . '&task=search';
			if ($task == self::TASK_SEARCH) $current = 'id="current"';
			$menuText .= '<li ' . $current . '><a href="'. $this->getUrl($link) .'"><span>検索条件</span></a></li>' . M3_NL;
			
			// その他設定
			$current = '';
			$link = $this->_baseUrl . '&task=config';
			if ($task == self::TASK_CONFIG) $current = 'id="current"';
			$menuText .= '<li ' . $current . '><a href="'. $this->getUrl($link) .'"><span>フォトギャラリー設定</span></a></li>' . M3_NL;
		}
		
		// 下段メニュー終了
		$menuText .= '</ul>' . M3_NL;
		$menuText .= '</div>' . M3_NL;

		// 作成データの埋め込み
		$linkList = '<div id="configmenu-top"><label>' . 'フォトギャラリー' . $linkList . '</div>';
		$outputText .= '<table><tr><td>' . $linkList . $menuText . '</td></tr></table>' . M3_NL;
		$this->tmpl->addVar("_widget", "menu_items", $outputText);
	}
	/**
	 * DB定義値を更新
	 *
	 * @param array $values	キーと値の連想配列
	 * @return bool			true=成功、false=失敗
	 */
	function _updateConfig($values)
	{
		if (empty($values)) return false;
		
		foreach ($values as $key => $value){
			$ret = self::$_mainDb->updateConfig($key, $value);
			if (!$ret){
				$this->_errMessage = $key;
				break;
			}
		}
		return $ret;
	}
	/**
	 * エラーメッセージを取得
	 *
	 * @return string			メッセージ
	 */
	function _getErrMessage()
	{
		return $this->_errMessage;		// エラーメッセージ
	}
}
?>
