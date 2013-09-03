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
require_once($gEnvManager->getContainerPath()		. '/baseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath()	. '/blog_new_boxDb.php');
require_once($gEnvManager->getCommonPath()			. '/htmlEdit.php');

class blog_new_boxWidgetContainer extends BaseWidgetContainer
{
	private $db;	// DB接続オブジェクト
	private $isEntry;	// 記事の投稿があるかどうか
	private $defaultUrl;	// システムのデフォルトURL
	private $headRssFile;				// RSS情報
	private $optionPassage;						// 表示オプション(経過日時)
	const DEFAULT_ITEM_COUNT = 20;		// デフォルトの表示項目数
	const MAX_TITLE_LENGTH = 20;			// タイトルの最大文字列長
	const DEFAULT_TITLE = 'ブログ最新記事';		// デフォルトのウィジェットタイトル名
	const RSS_ICON_FILE = '/images/system/rss14.png';		// RSSリンク用アイコン
		
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DB接続オブジェクト作成
		$this->db = new blog_new_boxDb();
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
		// 設定値を取得
		$itemCount = self::DEFAULT_ITEM_COUNT;	// 表示項目数
		$useRss = 1;							// RSS配信を行うかどうか
		$this->optionPassage = 0;						// 表示オプション(経過日時)
		$paramObj = $this->getWidgetParamObj();
		if (!empty($paramObj)){
			$itemCount	= $paramObj->itemCount;
			$useRss		= $paramObj->useRss;// RSS配信を行うかどうか
			if (!isset($useRss)) $useRss = 1;
			$this->optionPassage	= $paramObj->optionPassage;		// 表示オプション(経過日時)
			if (!isset($this->optionPassage)) $this->optionPassage = 0;
		}
		
		// 新規ブログタイトルを取得
		$this->defaultUrl = $this->gEnv->getDefaultUrl();
		$this->db->getEntryItems($itemCount, $this->gEnv->getCurrentLanguage(), array($this, 'itemLoop'));
			
		if (!$this->isEntry){	// 記事の投稿がないときはメッセージを出力
			$this->tmpl->addVar("_widget", "message", '投稿記事はありません');
		}
		
		// RSSの設定
		if (empty($useRss)){
			$this->tmpl->addVar("_widget", "rss_link", '');
			$this->headRssFile = array();
		} else {
			// RSS用リンク作成
			$iconTitle = self::DEFAULT_TITLE;
			$iconUrl = $this->gEnv->getRootUrl() . self::RSS_ICON_FILE;
			$rssLink = '<img src="' . $this->getUrl($iconUrl) . '" alt="' . $iconTitle . '" title="' . $iconTitle . '" style="border:none;" />';
			$linkUrl = $this->gPage->createRssCmdUrl($this->gEnv->getCurrentWidgetId());
			$rssLink = '<a href="' . convertUrlToHtmlEntity($this->getUrl($linkUrl)) . '">' . $rssLink . '</a>';
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
		return self::DEFAULT_TITLE;
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
		// タイトルを設定
		$title = $fetchedRow['be_name'];
		// タイトルの長さは制限
		if (function_exists('mb_substr')){
			$title = mb_substr($title, 0, self::MAX_TITLE_LENGTH);
		} else {
			$title = substr($title, 0, self::MAX_TITLE_LENGTH);
		}
		
		// 記事へのリンク
		$url = $this->defaultUrl . '?'. M3_REQUEST_PARAM_BLOG_ENTRY_ID . '=' . $fetchedRow['be_id'];

		// オプション項目
		$optionStr = '';
		if ($this->optionPassage){
			$time = strtotime($fetchedRow['be_regist_dt']);
			if ($time != strtotime($this->gEnv->getInitValueOfTimestamp())){
				$time = time() - $time;
				$optionStr = '<div style="text-align:right;font-size:smaller;">' . $this->convertToDispString($this->convertToDispPassageTime($time) . '前') . '</div>';
			}
		}
		
		$row = array(
			'link_url' => $this->convertUrlToHtmlEntity($this->getUrl($url, true/*リンク用*/)),		// リンク
			'name' => $this->convertToDispString($title),			// タイトル
			'option'	=> $optionStr								// オプション項目
		);
		$this->tmpl->addVars('itemlist', $row);
		$this->tmpl->parseTemplate('itemlist', 'a');
		
		$this->isEntry = true;	// 記事の投稿があるかどうか
		return true;
	}
}
?>
