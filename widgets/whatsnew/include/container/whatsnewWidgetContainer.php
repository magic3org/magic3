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
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath()		. '/baseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath()	. '/whatsnewDb.php');

class whatsnewWidgetContainer extends BaseWidgetContainer
{
	private $db;	// DB接続オブジェクト
	private $langId;
	private $isNews;	// 新着情報があるかどうか
//	private $defaultUrl;	// システムのデフォルトURL
	private $headRssFile;				// RSS情報
	private $configArray;		// 新着情報定義値
	private $listItemLayout;		// 一覧項目レイアウト
	const DEFAULT_CONFIG_ID = 0;
	const DEFAULT_ITEM_COUNT = 20;		// デフォルトの表示項目数
	const MAX_TITLE_LENGTH = 20;			// タイトルの最大文字列長
	const DEFAULT_TITLE = 'ブログカテゴリ(%s)の最新記事';		// デフォルトのウィジェットタイトル名
	const RSS_ICON_FILE = '/images/system/rss14.png';		// RSSリンク用アイコン
	const UNKNOWN_CONTENT_TYPE = 'コンテンツタイプ不明';
	const UNKNOWN_CONTENT = 'タイトル不明';
	
	// DBフィールド名
	const FD_DEFAULT_MESSAGE	= 'default_message';		// デフォルトメッセージ
	const FD_DATE_FORMAT		= 'date_format';			// 日時フォーマット
	const FD_LAYOUT_LIST_ITEM	= 'layout_list_item';		// リスト項目レイアウト
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// 初期設定
		$this->langId	= $this->gEnv->getCurrentLanguage();		// 表示言語を取得
				
		// DB接続オブジェクト作成
		$this->db = new whatsnewDb();
		
		// 共通定義値取得
		$this->configArray = $this->_loadConfig($this->db);
	}
	/**
	 * 新着情報定義値をDBから取得
	 *
	 * @param object $db	DBオブジェクト
	 * @return array		取得データ
	 */
	function _loadConfig($db)
	{
		$retVal = array();

		// 汎用コンテンツ定義を読み込み
		$ret = $db->getAllConfig($rows);
		if ($ret){
			// 取得データを連想配列にする
			$configCount = count($rows);
			for ($i = 0; $i < $configCount; $i++){
				$key = $rows[$i]['nc_id'];
				$value = $rows[$i]['nc_value'];
				$retVal[$key] = $value;
			}
		}
		return $retVal;
	}
	/**
	 * テンプレートファイルを設定
	 *
	 * _assign()でデータを埋め込むテンプレートファイルのファイル名を返す。
	 * 読み込むディレクトリは、「自ウィジェットディレクトリ/include/template」に固定。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。そのまま_assign()に渡る
	 * @return string 						テンプレートファイル名。テンプレートライブラリを使用しない場合は空文字列「''」を返す。
	 */
	function _setTemplate($request, &$param)
	{	
		return 'index.tmpl.html';
	}
	/**
	 * テンプレートにデータ埋め込む
	 *
	 * _setTemplate()で指定したテンプレートファイルにデータを埋め込む。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。_setTemplate()と共有。
	 * @param								なし
	 */
	function _assign($request, &$param)
	{
		$now = date("Y/m/d H:i:s");	// 現在日時
		
		// 定義ID取得
		$configId = $this->gEnv->getCurrentWidgetConfigId();
		if (empty($configId)) $configId = self::DEFAULT_CONFIG_ID;
		
		// 初期値設定
		$itemCount = self::DEFAULT_ITEM_COUNT;	// 表示項目数
		$useRss = 1;							// RSS配信を行うかどうか
		
		// パラメータオブジェクトを取得
		$targetObj = $this->getWidgetParamObjByConfigId($configId);
		if (!empty($targetObj)){		// 定義データが取得できたとき
			$itemCount	= $targetObj->itemCount;
			$useRss		= $targetObj->useRss;// RSS配信を行うかどうか
		}
				
		// 新着情報を取得
//		$this->defaultUrl = $this->gEnv->getDefaultUrl();
		$this->listItemLayout = $this->configArray[self::FD_LAYOUT_LIST_ITEM];		// 一覧項目レイアウト
		$this->db->getNewsList('', $itemCount, 1, array($this, 'itemLoop'));

		if (!$this->isNews){	// 新着情報がないときはメッセージを出力
			$this->tmpl->setAttribute('itemlist', 'visibility', 'hidden');		// リストを非表示
			$this->tmpl->addVar("_widget", "message", '新着情報はありません');
		}
		
		// RSSの設定
		if (empty($useRss)){
			$this->tmpl->addVar("_widget", "rss_link", '');
			$this->headRssFile = array();
		} else {
			// RSS用リンク作成
//			$iconTitle = sprintf(self::DEFAULT_TITLE, $this->categoryName);
			$iconUrl = $this->gEnv->getRootUrl() . self::RSS_ICON_FILE;
			$rssLink = '<img src="' . $this->getUrl($iconUrl) . '" alt="' . $iconTitle . '" title="' . $iconTitle . '" style="border:none;" />';
			$linkUrl = $this->gPage->createRssCmdUrl($this->gEnv->getCurrentWidgetId(), M3_REQUEST_PARAM_CATEGORY_ID . '=' . $categoryId);
			$rssLink = '<a href="' . $this->convertUrlToHtmlEntity($this->getUrl($linkUrl, true/*リンク用*/)) . '">' . $rssLink . '</a>';
			$rssLink = '<div align="right">' . $rssLink . '</div>';		// 右寄せ
			$this->tmpl->addVar("_widget", "rss_link", $rssLink);
		
			// RSS情報を設定
			$this->headRssFile = array(
									'title' => $iconTitle,		// タイトル
									'href' => $this->getUrl($linkUrl)		// リンク先URL
								);				// RSS情報
		}
	}
	/**
	 * ウィジェットのタイトルを設定
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。そのまま_assign()に渡る
	 * @return string 						ウィジェットのタイトル名
	 */
	function _setTitle($request, &$param)
	{
		return $this->categoryName;
	}
	/**
	 * RSS情報をHTMLヘッダ部に設定
	 *
	 * RSS情報をHTMLのheadタグ内に追加出力する。
	 * _assign()よりも後に実行される。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。
	 * @return array 						RSS情報データ。連想配列で「title」(タイトル)「href」(RSS配信用URL)を設定。出力しない場合は空配列を設定。
	 */
	function _addRssFileToHead($request, &$param)
	{
		return $this->headRssFile;
	}
	/**
	 * 取得したデータをテンプレートに設定する
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function itemLoop($index, $fetchedRow, $param)
	{
		// コンテンツタイトル取得
		$contentType = $fetchedRow['nw_content_type'];	// コンテンツタイプ
		$contentId = $fetchedRow['nw_content_id'];	// コンテンツID
		if (!empty($contentType) && !empty($contentId)){
			$contentTitle = $this->getContentTitle($contentType, $contentId);
		} else {
			$contentTitle = $fetchedRow['nw_name'];	// コンテンツタイトル
		}
		
		// リンク先URL
		$linkUrl = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $this->gEnv->getRootUrl(), $fetchedRow['nw_url']);		// URLを修正
		
		$message = $fetchedRow['nw_message'];
		$keyTag = M3_TAG_START . M3_TAG_MACRO_TITLE . M3_TAG_END;
		$pos = strpos($message, $keyTag);
		if ($pos === false){		// タイトル埋め込みタグがないときは全体にリンクを掛ける
			$message = $this->convertToDispString($message);
			if (!empty($linkUrl)) $message = '<a href="' . $this->convertUrlToHtmlEntity($this->getUrl($linkUrl, true/*リンク用*/)) . '">' . $message . '</a>';
		} else {
			$contentTitle = $this->convertToDispString($contentTitle);
			if (!empty($linkUrl)) $contentTitle = '<a href="' . $this->convertUrlToHtmlEntity($this->getUrl($linkUrl, true/*リンク用*/)) . '">' . $contentTitle . '</a>';
			$message = str_replace($keyTag, $contentTitle, $this->convertToDispString($message));// タイトルを変換
		}
		
		// 新着項目
		$itemTag = $this->listItemLayout;
		$keyTag = M3_TAG_START . M3_TAG_MACRO_MESSAGE . M3_TAG_END;		// メッセージ
		$itemTag = str_replace($keyTag, $message, $itemTag);
		$keyTag = M3_TAG_START . M3_TAG_MACRO_DATE . M3_TAG_END;		// 日付
		$itemTag = str_replace($keyTag, date($this->configArray[self::FD_DATE_FORMAT], strtotime($fetchedRow['nw_regist_dt'])), $itemTag);
		$keyTag = M3_TAG_START . M3_TAG_MACRO_MARK . M3_TAG_END;		// マーク
		$itemTag = str_replace($keyTag, '', $itemTag);

		$row = array(
			'item' => $itemTag			// 新着項目
		);
		$this->tmpl->addVars('itemlist', $row);
		$this->tmpl->parseTemplate('itemlist', 'a');
		
		$this->isNews = true;	// 新着情報があるかどうか
		return true;
	}
	/**
	 * コンテンツタイトル取得
	 *
	 * @param string $contentType		コンテンツタイプ
	 * @param string $contentId			コンテンツID
	 * @param string					コンテンツタイトル
	 */
	function getContentTitle($contentType, $contentId)
	{
		$contentName = self::UNKNOWN_CONTENT;
		
		switch ($contentType){
			case M3_VIEW_TYPE_CONTENT:				// 汎用コンテンツ
				$ret = $this->db->getContentById(''/*PC用コンテンツ*/, $this->langId, $contentId, $row);
				if ($ret) $contentName = $row['cn_name'];
				break;
			case M3_VIEW_TYPE_PRODUCT:				// 商品情報(Eコマース)
				$ret = $this->db->getProductById($contentId, $this->langId, $row);
				if ($ret) $contentName = $row['pt_name'];
				break;
			case M3_VIEW_TYPE_BBS:					// BBS
				// 未使用
				break;
			case M3_VIEW_TYPE_BLOG:				// ブログ
				$ret = $this->db->getEntryById($contentId, $this->langId, $row);
				if ($ret) $contentName = $row['be_name'];
				break;
			case M3_VIEW_TYPE_WIKI:				// wiki
				$contentName = $contentId;
				break;
			case M3_VIEW_TYPE_USER:				// ユーザ作成コンテンツ
				$ret = $this->db->getRoomById($contentId, $this->langId, $row);
				if ($ret) $contentName = $row['ur_name'];
				break;
			case M3_VIEW_TYPE_EVENT:				// イベント情報
				$ret = $this->db->getEventById($contentId, $this->langId, $row);
				if ($ret) $contentName = $row['ee_name'];
				break;
			case M3_VIEW_TYPE_PHOTO:				// フォトギャラリー
				$ret = $this->db->getPhotoById($contentId, $this->langId, $row);
				if ($ret) $contentName = $row['ht_name'];
				break;
		}
		return $contentName;
	}
}
?>
