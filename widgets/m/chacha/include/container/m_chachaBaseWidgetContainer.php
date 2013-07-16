<?php
/**
 * コンテナクラス
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    マイクロブログ
 * @author     株式会社 毎日メディアサービス
 * @copyright  Copyright 2010 株式会社 毎日メディアサービス.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: m_chachaBaseWidgetContainer.php 3509 2010-08-19 03:18:16Z fishbone $
 * @link       http://www.m-media.co.jp
 */
require_once($gEnvManager->getContainerPath() . '/baseMobileWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/chachaDb.php');

class m_chachaBaseWidgetContainer extends BaseMobileWidgetContainer
{
	protected $_db;			// DB接続オブジェクト
	protected $_mobileId;		// 携帯ID
	protected $_boardId;	// 掲示板ID
	protected $_autolink;		// リンクを自動作成
	protected $_spacer;		// スペーサ
	const ERR_MESSAGE_COLOR = '#ff0000';			// エラーメッセージカラー
	const SPACER_FORMAT = '<div style="background-color:#aaaaaa;margin:1px 0;height:1px;"><img src="%s/images/system/spacer.gif" width="1" height="1" /></div>';		// スペーサフォーマット
	const CREATE_CODE_RETRY_COUNT = 10;				// コード生成のリトライ数
	const DEFAULT_BBS_ID_HEAD = 'board';		// デフォルトの掲示板ID
	const AVATAR_DIR = '/widgets/chacha/avatar32';			// アバター格納ディレクトリ
	const DEFAULT_AVATAR_ICON_FILE = '/images/default_avatar32.gif';		// デフォルトのアバターアイコン(携帯)
	const DEFAULT_AVATAR_FILE_EXT = 'gif';			// アバターファイルのデフォルト拡張子
	const AVATAR_SIZE = 32;			// アバター画像のサイズ
	// 値定義
	const CF_AUTOLINK = 'autolink';			// 自動的にリンクを作成
	const CF_MESSAGE_COUNT_MYPAGE = 'm:message_count_mypage';	// マイページのメッセージ表示項目数(携帯)
	const CF_TOP_CONTENTS = 'm:top_contents';		// トップコンテンツ(携帯)
	const CF_MESSAGE_LENGTH = 'message_length';	// 最大メッセージ長
	// 画面
	const DEFAULT_TASK = 'top';		// デフォルトの画面
	const TASK_TOP = 'top';			// トップ画面
	const TASK_THREAD = 'thread';			// スレッド処理
	const TASK_READ = 'read';		// スレッド表示
	const TASK_PROFILE = 'profile';			// プロフィール表示
	const TASK_MYPAGE = 'mypage';			// マイページ表示
	// URL用パラメータ
	const URL_PARAM_MEMBER_ID = 'memberid';		// 会員ID
	const URL_PARAM_MESSAGE_ID = 'messageid';		// メッセージID
	// 共通のCSS
	const CSS_LINK_STYLE_TOP = 'text-align:right;';	// 上のリンク部のスタイル
	const CSS_LINK_STYLE_BOTTOM = 'text-align:center;';					// 下のリンク部のスタイル
//	const CSS_LINK_STYLE_BOTTOM_RIGHT = 'text-align:right;';// 下右のリンク部のスタイル
	const CSS_LINK_STYLE_INNER_BOTTOM = 'text-align:right;';	// 内枠の下のリンク部のスタイル

	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
				
		// 端末IDを取得
		$this->_mobileId = $this->gEnv->getMobileId();
					
		// サブウィジェット起動のときだけ初期処理実行
		if ($this->gEnv->getIsSubWidget()){
			// DBオブジェクト作成
			$this->_db = new chachaDb();
		
			// 定義ID取得
			// 定義IDから掲示板IDを作成
			$configId = $this->gEnv->getCurrentWidgetConfigId();
			$this->_boardId = self::DEFAULT_BBS_ID_HEAD . intval($configId);
		
			// BBS定義を読み込む
			$this->_loadConfig($this->_boardId);
		
			//$this->_currentPageUrl = $this->gEnv->createCurrentPageUrl();	// 現在のページのURL
			$this->_currentPageUrl = $this->gEnv->createCurrentPageUrlForMobile();// 現在のページのURL(携帯用のパラメータ付き)
			$this->_autolink = $this->_configArray[self::CF_AUTOLINK];		// リンクを自動作成
			
			// スペーサ作成
			$this->_spacer = sprintf(self::SPACER_FORMAT, $this->getUrl($this->gEnv->getRootUrl()));
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
		// 共通のリンク設定
		$this->tmpl->addVar("_widget", "link_style_top", self::CSS_LINK_STYLE_TOP);// 上下のリンク部のスタイル
		$this->tmpl->addVar("_widget", "link_style_bottom", self::CSS_LINK_STYLE_BOTTOM);// 下のリンク部のスタイル
		//$this->tmpl->addVar("_widget", "link_style_bottom_right", self::CSS_LINK_STYLE_BOTTOM_RIGHT);// 下右のリンク部のスタイル
		$this->tmpl->addVar("_widget", "link_style_inner_bottom", self::CSS_LINK_STYLE_INNER_BOTTOM);// 内枠下のリンク部のスタイル
		$this->tmpl->addVar('_widget', 'top_url', $this->gEnv->createCurrentPageUrlForMobile(''));
		
		// メッセージカラーを設定
		if ($this->getMsgCount(1) > 0 || $this->getMsgCount(2) > 0){		// エラーメッセージが出力されているとき
			$errMessageColor = self::ERR_MESSAGE_COLOR;		// エラーメッセージ色
			$errMessageStyle = '';
			if (!empty($errMessageColor)) $errMessageStyle .= 'color:' . $errMessageColor . ';';
			$attr = 'style="' . $errMessageStyle . 'text-align:center;"';
			$this->setMessageAttr($attr);
		} else if ($this->getMsgCount(3) > 0){			// ガイダンスメッセージが出力されているとき
			$attr = 'style="text-align:center;"';
			$this->setMessageAttr($attr);
		}
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
	/**
	 * アバターファイルのURLを取得
	 *
	 * @param string $memberId		会員ID
	 * @return string				URL
	 */
	function getAvatarUrl($memberId)
	{
		$avatarImagePath = $this->gEnv->getResourcePath() . self::AVATAR_DIR . '/' . $memberId . '.' . self::DEFAULT_AVATAR_FILE_EXT;
		if (file_exists($avatarImagePath)){		// アバターファイルが見つからないときはデフォルトを使用
			$avatarImageUrl = $this->gEnv->getResourceUrl() . self::AVATAR_DIR . '/' . $memberId . '.' . self::DEFAULT_AVATAR_FILE_EXT;
		} else {
			$avatarImageUrl = $this->gEnv->getCurrentWidgetRootUrl() . self::DEFAULT_AVATAR_ICON_FILE;
		}
		return $avatarImageUrl;
	}
	/**
	 * メッセージを表示用に変換
	 *
	 * @param string $message		変換元メッセージ
	 * @param string $threadId		スレッドID
	 * @return string				変換後メッセージ
	 */
	function convDispMessage($message, $threadId)
	{
		// リンク変換
		if (!empty($this->_autolink)){		// 自動リンク作成のとき
			$message = preg_replace("/(https?):\/\/([\w;\/\?:\@&=\+\$,\-\.!~\*'\(\)%#]+)/", "<a href=\"$1://$2\" target=\"_blank\">$1://$2</a>", $message);
		
			// メッセージへのリンク
		//	$baseUrl = $this->convertUrlToHtmlEntity($this->getUrl($this->_currentPageUrl . '&' . M3_REQUEST_PARAM_BBS_THREAD_ID . '=' . $threadId, true));
			$messageUrl = $baseUrl . $this->convertUrlToHtmlEntity('&' . M3_REQUEST_PARAM_ITEM_NO . '=');
			$messageListUrl = $baseUrl . $this->convertUrlToHtmlEntity('&' . M3_REQUEST_PARAM_LIST_NO . '=');
			$message = preg_replace("/&gt;&gt;([0-9]+)(?![-\d])/", '<a href="' . $messageUrl . '$1" target="_blank">&gt;&gt;$1</a>', $message);
			$message = preg_replace("/&gt;&gt;([0-9]+)\-([0-9]+)/", '<a href="' . $messageListUrl . '$1-$2" target="_blank">&gt;&gt;$1-$2</a>', $message);
		}
		return $message;
	}
	/**
	 * ランダム文字列を作成
	 *
	 * @param string $baseChars		使用する文字
	 * @param int $length			作成する文字列の長さ
	 * @return string				ランダム文字列。作成できなかった場合は空文字列。
	 */
	function _createRandString($baseChars, $length)
	{
    	// 文字列の初期化
    	$destStr = '';

		if (!(is_numeric($length) && $length > 0)) return $destStr;
    
		for ($i = 0; $i < $length; $i++){
			$pos = rand(0, strlen($baseChars) -1);
			$destStr .= $baseChars[$pos];
		}
		return $destStr;
	}
	/**
	 * テキストデータを表示用のテキストに変換
	 *
	 * 変換内容　・改行コードをスペース「&nbsp;」に変換
	 *
	 * @param string $src			変換するデータ
	 * @return string				変換後データ
	 */
	function _convertToPreviewTextWithSpace($src)
	{
		return preg_replace("/(\015\012)|(\015)|(\012)/", "&nbsp;", $src);
	}
}
?>
