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
 * @copyright  Copyright 2006-2010 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: admin_bbs_2ch_mainBaseWidgetContainer.php 4019 2011-03-06 11:37:15Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseAdminWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/bbs_2ch_mainDb.php');

class admin_bbs_2ch_mainBaseWidgetContainer extends BaseAdminWidgetContainer
{
	protected $_db;			// DB接続オブジェクト
	protected $_boardId;	// 掲示板ID
	const DEFAULT_BBS_ID = 'board1';		// デフォルトの掲示板ID
	const DEFAULT_TOP_PAGE = 'message';		// デフォルトのトップページ
	const DEFAULT_BOTTOM_MESSAGE = '<center><b>どのような形の削除依頼であれ公開させていただきます。</b></center>';		// デフォルトのトップ画面下部メッセージ
	const DEFAULT_THREAD_END_MESSAGE = "このスレッドは[#RES_MAX_NO#]を超えました。\r\nもう書けないので、新しいスレッドを立ててくださいです。。。";		// デフォルトのレス上限メッセージ
	const DEFAULT_ADMIN_NAME = 'サイト運営者';				// サイト運営者名
	const WIDGET_TITLE_NAME = '2ちゃんねる風BBSメイン';				// ウィジェットタイトル名
	const CF_BBS_TITLE = 'title';			// 掲示板タイトル
	const CF_TITLE_COLOR = 'title_color';	// タイトルカラー
	const CF_TOP_LINK = 'top_link';		// トップ画像のリンク先
	const CF_TOP_IMAGE = 'top_image';		// トップ画像
	const CF_BBS_GUIDE = 'bbs_guide';		// 掲示板規則
	const CF_BOTTOM_MESSAGE = 'bottom_message';		// トップ画面下部メッセージ
	const CF_THREAD_END_MESSAGE = 'thread_end_message';		// レス上限メッセージ
	const CF_BG_IMAGE = 'bg_image';		// 背景画像
	const CF_BG_COLOR = 'bg_color';		// 背景色
	const CF_TEXT_COLOR = 'text_color';		// 文字色
	const CF_MENU_COLOR = 'menu_color';		// メニュー背景色
	const CF_MAKE_THREAD_COLOR = 'makethread_color';		// スレッド作成部背景色
	const CF_THREAD_COLOR = 'thread_color';		// スレッド表示部背景色
	const CF_LINK_COLOR = 'link_color';		// リンク色
	const CF_ALINK_COLOR = 'alink_color';		// リンク色
	const CF_VLINK_COLOR = 'vlink_color';		// リンク色
	const CF_NAME_COLOR = 'name_color';			// 投稿者名文字色
	const CF_FILE_UPLOAD = 'file_upload';		// ファイルアップロード許可
	const CF_SUBJECT_LENGTH = 'subject_length';		// 件名最大長
	const CF_NAME_LENGTH = 'name_length';		// 投稿者名最大長
	const CF_EMAIL_LENGTH = 'email_length';		// emailアドレス最大長
	const CF_MESSAGE_LENGTH = 'message_length';	// 最大メッセージ長
	const CF_ERR_MESSAGE_COLOR = 'err_message_color';		// エラーメッセージ文字色
	const CF_SUBJECT_COLOR = 'subject_color';		// 件名文字色
	const CF_LINE_LENGTH = 'line_length';		// 投稿文行長
	const CF_LINE_COUNT = 'line_count';			// 投稿文行数
	const CF_RES_ANCHOR_LINK_COUNT = 'res_anchor_link_count';		// レスアンカーリンク数
	const CF_THREAD_COUNT = 'thread_count';		// トップ画面に表示するスレッド最大数
	const CF_RES_COUNT = 'res_count';		// トップ画面に表示するレス最大数
	const CF_THREAD_RES = 'thread_res';		// 1スレッドに投稿できるレス数の上限
	const CF_MENU_THREAD_COUNT = 'menu_thread_count';	// メニューに表示するスレッド最大数
	const CF_SHOW_EMAIL = 'show_email';		// Eメールアドレスを表示
	const CF_AUTOLINK = 'autolink';			// 自動的にリンクを作成
	const CF_NONAME_NAME = 'noname_name';				// 名前未設定時の表示名
	const CF_ADMIN_NAME = 'admin_name';				// サイト運営者名
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->_db = new bbs_2ch_mainDb();
		
		// BBS定義を読み込む
		$this->_loadConfig();
		
		$this->_boardId = self::DEFAULT_BBS_ID;
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
			case 'message':		// メッセージ管理
			case 'message_detail':		// メッセージ管理(詳細)
				$linkList = ' &gt;&gt; 投稿管理';// パンくずリスト
				break;
			case 'other':		// その他設定
				$linkList = ' &gt;&gt; 基本設定';// パンくずリスト
				break;
		}
		// ####### 上段メニューの作成 #######
		$menuText = '<div id="configmenu-upper">' . M3_NL;
		$menuText .= '<ul>' . M3_NL;
		$baseUrl = $this->getAdminUrlWithOptionParam(true);// 画面定義ID付き
		
		// メッセージ管理
		$current = '';
		$link = $baseUrl . '&task=message';
		if ($task == 'message' ||
			$task == 'message_detail'){
			$current = 'id="current"';
		}
		$menuText .= '<li ' . $current . '><a href="'. $this->getUrl($link, true) .'"><span>投稿管理</span></a></li>' . M3_NL;

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
	/**
	 * BBS定義値をDBから取得
	 *
	 * @return bool			true=取得成功、false=取得失敗
	 */
	function _loadConfig()
	{
		$this->_configArray = array();

		// BBS定義を読み込み
		$ret = $this->_db->getAllConfig($rows);
		if ($ret){
			// 取得データを連想配列にする
			$configCount = count($rows);
			for ($i = 0; $i < $configCount; $i++){
				$key = $rows[$i]['tg_id'];
				$value = $rows[$i]['tg_value'];
				$this->_configArray[$key] = $value;
			}
		}
		return $ret;
	}
}
?>
