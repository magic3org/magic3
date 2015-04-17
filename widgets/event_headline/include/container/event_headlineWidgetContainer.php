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
 * @copyright  Copyright 2006-2015 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath()		. '/baseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetContainerPath() . '/event_headlineCommonDef.php');
require_once($gEnvManager->getCurrentWidgetDbPath()	. '/event_headlineDb.php');

class event_headlineWidgetContainer extends BaseWidgetContainer
{
	private $db;	// DB接続オブジェクト
	private $isEntry;	// 記事の投稿があるかどうか
	private $defaultUrl;	// システムのデフォルトURL
	private $headRssFile;				// RSS情報
	private $showImage;		// 画像を表示するかどうか
	private $imageType;				// 画像タイプ
	private $imageWidth;			// 画像幅
	private $imageHeight;			// 画像高さ
	const DEFAULT_CONFIG_ID = 0;
	const DEFAULT_TITLE = 'イベントヘッドライン';		// デフォルトのウィジェットタイトル名
	const RSS_ICON_FILE = '/images/system/rss14.png';		// RSSリンク用アイコン
		
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DB接続オブジェクト作成
		$this->db = new event_headlineDb();
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
		// 定義ID取得
		$configId = $this->gEnv->getCurrentWidgetConfigId();
		if (empty($configId)) $configId = self::DEFAULT_CONFIG_ID;
		
		// 初期値設定
		$itemCount			= event_headlineCommonDef::DEFAULT_ITEM_COUNT;	// 表示項目数
		$sortOrder			= '0';		// ソート順
		$useBaseDay			= '0';		// 基準日を使用するかどうか
		$dayCount			= 0;			// 基準日までの日数
		$this->showImage	= 0;				// 画像を表示するかどうか
		$this->imageType	= event_headlineCommonDef::DEFAULT_IMAGE_TYPE;				// 画像タイプ
		$this->imageWidth	= 0;				// 画像幅
		$this->imageHeight	= 0;				// 画像高さ
		$useRss				= 1;							// RSS配信を行うかどうか

		// 設定値を取得
		$paramObj = $this->getWidgetParamObjByConfigId($configId);
		if (!empty($paramObj)){		// 定義データが取得できたとき
			if (isset($paramObj->itemCount))	$itemCount			= $paramObj->itemCount;
			if (isset($paramObj->sortOrder))	$sortOrder			= $paramObj->sortOrder;		// ソート順
			if (isset($paramObj->useBaseDay))	$useBaseDay			= $paramObj->useBaseDay;		// 基準日を使用するかどうか
			if (isset($paramObj->dayCount))		$dayCount			= $paramObj->dayCount;			// 基準日までの日数
			if (isset($paramObj->showImage))	$this->showImage	= $paramObj->showImage;				// 画像を表示するかどうか
			if (isset($paramObj->imageType))	$this->imageType	= $paramObj->imageType;				// 画像タイプ
			if (isset($paramObj->imageWidth))	$this->imageWidth	= $paramObj->imageWidth;				// 画像幅
			if (isset($paramObj->imageHeight))	$this->imageHeight	= $paramObj->imageHeight;				// 画像高さ
			if (isset($paramObj->useRss))		$useRss				= $paramObj->useRss;// RSS配信を行うかどうか
		}

		// イベント記事取得
		$this->defaultUrl = $this->gEnv->getDefaultUrl();
		$this->db->getEntryItems($itemCount, $this->_langId, $sortOrder, $useBaseDay, $dayCount, array($this, 'itemLoop'));
			
		if (!$this->isEntry){	// 記事の投稿がないときはメッセージを出力
			$this->tmpl->addVar("_widget", "message", 'イベント記事はありません');
			
			$this->tmpl->setAttribute('itemlist', 'visibility', 'hidden');// 一覧非表示
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
		$entryId = $fetchedRow['ee_id'];
		
		// タイトルを設定
		$title = $fetchedRow['ee_name'];
		
		// イベント記事へのリンク
		$url = $this->defaultUrl . '?'. M3_REQUEST_PARAM_EVENT_ID . '=' . $fetchedRow['ee_id'];
		$escapedLinkUrl = $this->convertUrlToHtmlEntity($this->getUrl($url, true/*リンク用*/));

		// オプション項目
		$optionStr = '';
		
		// 画像
		$imageTag = '';
		if ($this->showImage){
			$titleStr = $fetchedRow['ee_name'];
			$imageUrl = $this->getImageUrl($entryId, $this->imageType);
			$style = '';
			if ($this->imageWidth > 0) $style .= 'width:' . $this->imageWidth . 'px;';
			if ($this->imageHeight > 0) $style .= 'height:' . $this->imageHeight . 'px;';
			if (!empty($style)) $style = 'style="' . $style . '" ';
			$imageTag = '<img src="' . $this->getUrl($imageUrl) . '" alt="' . $titleStr . '" title="' . $titleStr . '" ' . $style . '/>';
			$imageTag = '<div style="float:left;"><a href="' . $escapedLinkUrl . '">' . $imageTag . '</a></div>';
		}
		// 記事名
		$nameTag = '<a href="' . $escapedLinkUrl . '"><span>' . $this->convertToDispString($title) . '</span></a>';
		$nameTag .= $optionStr;
		if ($this->showImage) $nameTag = '<div class="clearfix">' . $nameTag . '</div>';
		$nameTag = event_headlineCommonDef::DEFAULT_EVENT_ITEM_LAYOUT;
		$row = array(
			'name' 		=> $nameTag,			// タイトル
			'image'		=> $imageTag								// 画像
		);
		$this->tmpl->addVars('itemlist', $row);
		$this->tmpl->parseTemplate('itemlist', 'a');
		
		$this->isEntry = true;	// 記事の投稿があるかどうか
		return true;
	}
	/**
	 * 画像のURLを取得
	 *
	 * @param string $entryId		記事ID
	 * @param string $format		画像フォーマット
	 * @return string				URL
	 */
	function getImageUrl($entryId, $format)
	{
		$filename = $this->gInstance->getImageManager()->getThumbFilename($entryId, $format);
		$path = $this->gInstance->getImageManager()->getSystemThumbPath(M3_VIEW_TYPE_EVENT, 0/*PC用*/, $filename);
		if (!file_exists($path)){
			$filename = $this->gInstance->getImageManager()->getThumbFilename(0, $format);		// デフォルト画像ファイル名
			$path = $this->gInstance->getImageManager()->getSystemThumbPath(M3_VIEW_TYPE_EVENT, 0/*PC用*/, $filename);
		}
		$url = $this->gInstance->getImageManager()->getSystemThumbUrl(M3_VIEW_TYPE_EVENT, 0/*PC用*/, $filename);
		return $url;
	}
}
?>
