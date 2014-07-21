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
require_once($gEnvManager->getContainerPath() . '/baseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/wiki_updateDb.php');

class wiki_updateWidgetContainer extends BaseWidgetContainer
{
	private $db;
	private $itemCount;					// リスト項目数
	private $isExistsList;				// リスト項目が存在するかどうか
	private $headRssFile;				// RSS情報
	const DEFAULT_ITEM_COUNT = 10;		// デフォルトの表示項目数
	const DEFAULT_TITLE = '更新リスト';			// デフォルトのウィジェットタイトル
	const RSS_ICON_FILE = '/images/system/rss14.png';		// RSSリンク用アイコン
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->db = new wiki_updateDb();
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
		$this->db->getUpdatePages($this->itemCount, 1/*1ページ目*/, array($this, 'itemsLoop'));
			
		// 画面にデータを埋め込む
		if ($this->isExistsList) $this->tmpl->setAttribute('itemlist', 'visibility', 'visible');

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
	function _addRssFileToHead($request, &$param)
	{
		return $this->headRssFile;
	}
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
		$name = $fetchedRow['wc_id'];

		// リンク先の作成
		$linkUrl = $this->getUrl($this->gEnv->getDefaultUrl() . '?' . M3_REQUEST_PARAM_CONTENT_ID . '=' . $fetchedRow['wc_id'], true);

		$row = array(
			'link_url'	=> $this->convertUrlToHtmlEntity($linkUrl),		// リンク
			'name'		=> $this->convertToDispString($name)			// タイトル
		);
		$this->tmpl->addVars('itemlist', $row);
		$this->tmpl->parseTemplate('itemlist', 'a');
	
		$this->isExistsList = true;		// リスト項目が存在するかどうか
		return true;
	}
}
?>
