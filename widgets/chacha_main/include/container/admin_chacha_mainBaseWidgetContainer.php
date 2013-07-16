<?php
/**
 * index.php用コンテナクラス
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    マイクロブログ
 * @author     株式会社 毎日メディアサービス
 * @copyright  Copyright 2010 株式会社 毎日メディアサービス.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: admin_chacha_mainBaseWidgetContainer.php 3261 2010-06-19 07:33:36Z fishbone $
 * @link       http://www.m-media.co.jp
 */
require_once($gEnvManager->getContainerPath() . '/baseAdminWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/chacha_mainDb.php');

class admin_chacha_mainBaseWidgetContainer extends BaseAdminWidgetContainer
{
	protected $_db;			// DB接続オブジェクト
	protected $_boardId;	// 掲示板ID
	const DEFAULT_BBS_ID_HEAD = 'board';		// デフォルトの掲示板ID
	const DEFAULT_TOP_PAGE = 'message';		// デフォルトのトップページ
	const WIDGET_TITLE_NAME = 'マイクロブログ';				// ウィジェットタイトル名
	const CF_TEXT_COLOR = 'text_color';	// 文字色
	const CF_BG_COLOR = 'bg_color';		// 背景色
	const CF_INNER_BG_COLOR = 'inner_bg_color';	// 内枠背景色
	const CF_PROFILE_COLOR = 'profile_color';		// プロフィール背景色
	const CF_ERR_MESSAGE_COLOR = 'err_message_color';		// エラーメッセージ文字色
	const CF_MESSAGE_LENGTH = 'message_length';	// 最大メッセージ長
	const CF_TOP_CONTENTS = 'top_contents';		// トップコンテンツ
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// サブウィジェット起動のときだけ初期処理実行
		if ($this->gEnv->getIsSubWidget()){
			// DBオブジェクト作成
			$this->_db = new chacha_mainDb();
		
			$this->_boardId = self::DEFAULT_BBS_ID_HEAD . '0';
		
			// BBS定義を読み込む
			$this->_loadConfig($this->_boardId);
		}
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
				$linkList = ' &gt;&gt; メッセージ管理';// パンくずリスト
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
			$task == 'messaget_detail'){
			$current = 'id="message"';
		}
		$menuText .= '<li ' . $current . '><a href="'. $this->getUrl($link, true) .'"><span>メッセージ管理</span></a></li>' . M3_NL;

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
	 * ブログ定義値をDBから取得
	 *
	 * @param string $boardId	掲示板ID
	 * @return bool				true=取得成功、false=取得失敗
	 */
	function _loadConfig($boardId)
	{
		$this->_configArray = array();

		// BBS定義を読み込み
		$ret = $this->_db->getAllConfig($rows, $boardId);
		if ($ret){
			// 取得データを連想配列にする
			$configCount = count($rows);
			for ($i = 0; $i < $configCount; $i++){
				$key = $rows[$i]['mc_id'];
				$value = $rows[$i]['mc_value'];
				$this->_configArray[$key] = $value;
			}
		}
		return $ret;
	}
}
?>
