<?php
/**
 * コンテナクラス
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    ポータル用コンテンツ更新情報
 * @author     株式会社 毎日メディアサービス
 * @copyright  Copyright 2009 株式会社 毎日メディアサービス.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: portal_updateinfoWidgetContainer.php 2724 2009-12-21 07:41:16Z fishbone $
 * @link       http://www.m-media.co.jp
 */
require_once($gEnvManager->getContainerPath() . '/baseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/portal_updateinfoDb.php');

class portal_updateinfoWidgetContainer extends BaseWidgetContainer
{
	private $db;
	private $itemCount;					// リスト項目数
	private $isExistsList;				// リスト項目が存在するかどうか
	private $headRssFile;				// RSS情報
	const DEFAULT_ITEM_COUNT = 10;		// デフォルトの表示項目数
	const CONTENT_TYPE = 'content';		// 取得コンテンツタイプ
	const DEFAULT_TITLE = 'コンテンツ更新情報';			// デフォルトのウィジェットタイトル
	const RSS_ICON_FILE = '/images/system/rss14.png';		// RSSリンク用アイコン
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->db = new portal_updateinfoDb();
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
		$langId = $this->gEnv->getDefaultLanguage();
		
		$this->itemCount = self::DEFAULT_ITEM_COUNT;	// 表示項目数
		$paramObj = $this->getWidgetParamObj();
		if (!empty($paramObj)){
			$this->itemCount	= $paramObj->itemCount;
		}
		
		// 一覧を作成
		$this->db->getUpdateInfoList(self::CONTENT_TYPE, intval($this->itemCount), array($this, 'itemsLoop'));
				
		// 新着情報がないときは、一覧を表示しない
		if (!$this->isExistsList){
			$this->tmpl->setAttribute('itemlist', 'visibility', 'hidden');
			
			// データなしのメッセージを表示
			$this->tmpl->setAttribute('message_area', 'visibility', 'visible');
			$this->tmpl->addVar("message_area", "message", '更新情報がありません');
		}
		
		// RSS用リンク作成
		$iconTitle = self::DEFAULT_TITLE;
		$iconUrl = $this->gEnv->getRootUrl() . self::RSS_ICON_FILE;
		$rssLink = '<img src="' . $this->getUrl($iconUrl) . '" border="0" alt="' . $iconTitle . '" title="' . $iconTitle . '" />';
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
	/*function _addRssFileToHead($request, &$param)
	{
		return $this->headRssFile;
	}*/
	/**
	 * 取得したメニュー項目をテンプレートに設定する
	 *
	 * @param int		$index			行番号
	 * @param array		$fetchedRow		取得行
	 * @param object	$param			任意使用パラメータ
	 * @return bool						trueを返すとループ続行。falseを返すとその時点で終了。
	 */
	function itemsLoop($index, $fetchedRow)
	{
		$name = $fetchedRow['nw_name'];
		$linkUrl = $fetchedRow['nw_link'];		// コンテンツへのリンク
		$message = $fetchedRow['nw_message'];
		$siteLink = $fetchedRow['nw_site_link'];
		$siteName = $fetchedRow['nw_site_name'];
		
		if (!empty($name)){
			$row = array(
				'link_url' => $this->convertUrlToHtmlEntity($linkUrl),		// リンク
				'name' => $this->convertToDispString($name),			// タイトル
				'message' => $this->convertToDispString($message),		// メッセージ
				'site_link' => $this->convertUrlToHtmlEntity($siteLink),	// サイトへのリンク
				'site_name' => $this->convertToDispString($siteName),		// サイト名
				'date' => $this->convertToDispDateTime($fetchedRow['nw_regist_dt'], 10/*年なし*/, 10/*秒なし*/)		// 登録日時
			);
			$this->tmpl->addVars('itemlist', $row);
			$this->tmpl->parseTemplate('itemlist', 'a');
		
			$this->isExistsList = true;		// リスト項目が存在するかどうか
		}
		return true;
	}
}
?>
