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
 * @copyright  Copyright 2006-2014 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: admin_photo_mainBaseWidgetContainer.php 5599 2013-02-06 11:47:30Z fishbone $
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
	protected $_openBy;				// ウィンドウオープンタイプ
	protected $_baseUrl;			// 管理画面のベースURL
	protected $_langId;			// 現在の言語
	protected $_userId;			// 現在のユーザ
	protected $_errMessage;		// エラーメッセージ
/*	const CF_RECEIVE_COMMENT		= 'receive_comment';		// コメントを受け付けるかどうか
	const CF_RECEIVE_TRACKBACK		= 'receive_trackback';		// トラックバックを受け付けるかどうか
	const CF_ENTRY_VIEW_COUNT		= 'entry_view_count';			// 記事表示数
	const CF_ENTRY_VIEW_ORDER		= 'entry_view_order';			// 記事表示方向
	const CF_MAX_COMMENT_LENGTH		= 'comment_max_length';		// コメント最大文字数
	const CF_USE_MULTI_BLOG		= 'use_multi_blog';		// マルチブログ機能を使用するかどうか
	const CF_MULTI_BLOG_TOP_CONTENT	= 'multi_blog_top_content';		// マルチブログ時のトップコンテンツ
	const CF_CATEGORY_COUNT			= 'category_count';		// カテゴリ数
	const DEFAULT_COMMENT_LENGTH	= 300;				// デフォルトのコメント最大文字数
	const DEFAULT_CATEGORY_COUNT	= 2;				// デフォルトのカテゴリ数
	*/
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
		
		// 画像取得の場合は終了
		if ($task == 'imagebrowse_direct') return;
		
		// パンくずリストを作成
		switch ($task){
			case 'imagebrowse':		// 画像管理
			case 'imagebrowse_detail':	// 画像管理詳細
				$linkList = ' &gt;&gt; 画像管理 &gt;&gt; 画像一覧';// パンくずリスト
				break;
			case 'comment':		// 画像コメント
			case 'comment_detail':	// 画像コメント
				$linkList = ' &gt;&gt; 画像管理 &gt;&gt; コメント一覧';// パンくずリスト
				break;
			case 'author':		// 画像管理者一覧
			case 'author_detail':	// 画像管理者詳細
				$linkList = ' &gt;&gt; 基本設定 &gt;&gt; 画像管理者一覧';// パンくずリスト
				break;
			case 'category':		// 画像カテゴリー一覧
			case 'category_detail':	// 画像カテゴリー詳細
				$linkList = ' &gt;&gt; 基本設定 &gt;&gt; 画像カテゴリー一覧';// パンくずリスト
				break;
			case 'search':		// 検索条件
				$linkList = ' &gt;&gt; 基本設定 &gt;&gt; 検索条件';// パンくずリスト
				break;
			case 'config':		// フォトギャラリー設定
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
		if ($task == 'imagebrowse' ||
			$task == 'imagebrowse_detail' ||
			$task == 'comment' ||		// 画像コメント一覧
			$task == 'comment_detail'){		// 画像コメント詳細
			$current = 'id="current"';
		}
		$menuText .= '<li ' . $current . '><a href="'. $this->getUrl($link) .'"><span>画像管理</span></a></li>' . M3_NL;
		
		// 基本設定
		$current = '';
		$link = $this->_baseUrl . '&task=author';
		if ($task == 'author' ||		// 画像管理者一覧
			$task == 'author_detail' ||		// 画像管理者詳細
			$task == 'category' ||		// 画像カテゴリー一覧
			$task == 'category_detail' ||		// 画像カテゴリー詳細
			$task == 'search' ||		// 検索条件
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

		if ($task == 'imagebrowse' ||
			$task == 'imagebrowse_detail' ||
			$task == 'comment' ||		// 画像コメント一覧
			$task == 'comment_detail'){		// 画像コメント詳細
			
			// 画像一覧
			$current = '';
			$link = $this->_baseUrl . '&task=imagebrowse';
			if ($task == 'imagebrowse' || $task == 'imagebrowse_detail') $current = 'id="current"';
			$menuText .= '<li ' . $current . '><a href="'. $this->getUrl($link) .'"><span>画像一覧</span></a></li>' . M3_NL;
			
			// 画像コメント一覧
			$current = '';
			$link = $this->_baseUrl . '&task=comment';
			if ($task == 'comment' || $task == 'comment_detail') $current = 'id="current"';
			$menuText .= '<li ' . $current . '><a href="'. $this->getUrl($link) .'"><span>コメント一覧</span></a></li>' . M3_NL;
		} else if ($task == 'author' ||		// 画像管理者一覧
			$task == 'author_detail' ||		// 画像管理者詳細
			$task == 'category' ||		// 画像カテゴリー一覧
			$task == 'category_detail' ||		// 画像カテゴリー詳細
			$task == 'search' ||		// 検索条件
			$task == 'config'){		// ブログ設定

			// 画像管理者
			$current = '';
			$link = $this->_baseUrl . '&task=author';
			if ($task == 'author' || $task == 'author_detail') $current = 'id="current"';
			$menuText .= '<li ' . $current . '><a href="'. $this->getUrl($link) .'"><span>画像管理者</span></a></li>' . M3_NL;
			
			// 画像カテゴリー
			$current = '';
			$link = $this->_baseUrl . '&task=category';
			if ($task == 'category' || $task == 'category_detail') $current = 'id="current"';
			$menuText .= '<li ' . $current . '><a href="'. $this->getUrl($link) .'"><span>画像カテゴリー</span></a></li>' . M3_NL;
			
			// 検索条件
			$current = '';
			$link = $this->_baseUrl . '&task=search';
			if ($task == 'search') $current = 'id="current"';
			$menuText .= '<li ' . $current . '><a href="'. $this->getUrl($link) .'"><span>検索条件</span></a></li>' . M3_NL;
			
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
