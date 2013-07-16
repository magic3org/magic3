<?php
/**
 * コンテナクラス
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2010 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: m_blogBaseWidgetContainer.php 3836 2010-11-17 06:05:07Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseMobileWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/blogDb.php');

class m_blogBaseWidgetContainer extends BaseMobileWidgetContainer
{
	protected $_db;			// DB接続オブジェクト
	protected $_mobileId;		// 携帯ID
	protected $_blogId;	// ブログID
	protected $_spacer;		// スペーサ
	protected $_currentPageUrl;// 現在のページのURL(携帯用のパラメータ付き)
	// 表示設定
	const ERR_MESSAGE_COLOR = '#ff0000';			// エラーメッセージカラー
	const SPACER_FORMAT = '<div style="background-color:#aaaaaa;margin:1px 0;height:1px;"><img src="%s/images/system/spacer.gif" width="1" height="1" /></div>';		// スペーサフォーマット
	const LINK_PAGE_COUNT		= 5;			// リンクページ数
	const SEARCH_BODY_SIZE = 200;			// 検索結果の記事本文の文字列最大長
	
	// 値定義
	const CF_ENTRY_VIEW_COUNT		= 'm:entry_view_count';			// 記事表示数
	const CF_ENTRY_VIEW_ORDER		= 'm:entry_view_order';			// 記事表示方向
	const CF_SEARCH_COUNT		= 'm:search_count';		// 検索記事数
	const CF_SEARCH_ORDER		= 'm:search_order';		// 検索記事表示方向
	const CF_TITLE_COLOR			= 'm:title_color';			// タイトルの背景色
	
	// 画面
	const DEFAULT_TASK = 'read';		// デフォルトの画面
	const TASK_READ = 'read';		// スレッド表示
	// URL用パラメータ
//	const URL_PARAM_MEMBER_ID = 'memberid';		// 会員ID
//	const URL_PARAM_MESSAGE_ID = 'messageid';		// メッセージID
	// 共通のCSS
	const CSS_LINK_STYLE_BOTTOM = 'text-align:center;';					// 下のリンク部のスタイル
	const CSS_LINK_STYLE_INNER_BOTTOM = 'text-align:right;';	// 内枠の下のリンク部のスタイル
	// 画面タイトル
	const DEFAULT_TITLE_SEARCH = '検索';		// 検索時のデフォルトタイトル
	// 表示メッセージ
	const MESSAGE_NO_ENTRY_TITLE = 'ブログ記事未登録';
	const MESSAGE_NO_ENTRY		= 'ブログ記事は登録されていません';				// ブログ記事が登録されていないメッセージ
	const MESSAGE_FIND_NO_ENTRY	= 'ブログ記事が見つかりません';
	const MESSAGE_EXT_ENTRY		= '続きを読む';					// 投稿記事に続きがある場合の表示
	const MESSAGE_EXT_ENTRY_PRE	= '…&nbsp;';							// 投稿記事に続きがある場合の表示
	const MESSAGE_SEARCH_KEYWORD = '検索ｷｰﾜｰﾄﾞ:&nbsp;';				// 検索キーワード用ラベル
	// アクセス分析用
	const CONTENT_TYPE = 'bg';		// 参照数カウント用
		
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
			$this->_db = new blogDb();
		
			// 定義ID取得
			// 定義IDから掲示板IDを作成
			$configId = $this->gEnv->getCurrentWidgetConfigId();
			$this->_blogId = '';
		
			// BBS定義を読み込む
			$this->_loadConfig($this->_blogId);
		
			//$this->_currentPageUrl = $this->gEnv->createCurrentPageUrl();	// 現在のページのURL
			$this->_currentPageUrl = $this->gEnv->createCurrentPageUrlForMobile();// 現在のページのURL(携帯用のパラメータ付き)
			
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
		$this->tmpl->addVar("_widget", "link_style_bottom", self::CSS_LINK_STYLE_BOTTOM);// 下のリンク部のスタイル
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
	 * @param string $blogId	ブログID
	 * @return bool				true=取得成功、false=取得失敗
	 */
	function _loadConfig($blogId)
	{
		$this->_configArray = array();

		// BBS定義を読み込み
		$ret = $this->_db->getAllConfig($rows, $blogId);
		if ($ret){
			// 取得データを連想配列にする
			$configCount = count($rows);
			for ($i = 0; $i < $configCount; $i++){
				$key = $rows[$i]['bg_id'];
				$value = $rows[$i]['bg_value'];
				$this->_configArray[$key] = $value;
			}
		}
		return $ret;
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
		/*if (!empty($this->_autolink)){		// 自動リンク作成のとき
			$message = preg_replace("/(https?):\/\/([\w;\/\?:\@&=\+\$,\-\.!~\*'\(\)%#]+)/", "<a href=\"$1://$2\" target=\"_blank\">$1://$2</a>", $message);
		
			// メッセージへのリンク
		//	$baseUrl = $this->convertUrlToHtmlEntity($this->getUrl($this->_currentPageUrl . '&' . M3_REQUEST_PARAM_BBS_THREAD_ID . '=' . $threadId, true));
			$messageUrl = $baseUrl . $this->convertUrlToHtmlEntity('&' . M3_REQUEST_PARAM_ITEM_NO . '=');
			$messageListUrl = $baseUrl . $this->convertUrlToHtmlEntity('&' . M3_REQUEST_PARAM_LIST_NO . '=');
			$message = preg_replace("/&gt;&gt;([0-9]+)(?![-\d])/", '<a href="' . $messageUrl . '$1" target="_blank">&gt;&gt;$1</a>', $message);
			$message = preg_replace("/&gt;&gt;([0-9]+)\-([0-9]+)/", '<a href="' . $messageListUrl . '$1-$2" target="_blank">&gt;&gt;$1-$2</a>', $message);
		}*/
		return $message;
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
