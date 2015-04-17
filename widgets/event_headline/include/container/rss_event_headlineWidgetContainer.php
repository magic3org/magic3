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
require_once($gEnvManager->getContainerPath() . '/baseRssContainer.php');
require_once($gEnvManager->getCurrentWidgetContainerPath() . '/event_headlineCommonDef.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/event_headlineDb.php');

class rss_event_headlineWidgetContainer extends BaseRssContainer
{
	private $db;
	private $isExistsList;				// リスト項目が存在するかどうか
	private $rssChannel;				// RSSチャンネル部出力データ
	private $rssSeqUrl = array();					// 項目の並び
	private $defaultUrl;	// システムのデフォルトURL
	const DEFAULT_CONFIG_ID = 0;
	const DEFAULT_TITLE = 'ブログ最新記事';			// デフォルトのウィジェットタイトル
	const DEFAULT_DESC = '最新のブログ記事が取得できます。';
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
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
		return 'rss_index.tmpl.html';
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
		//$configId = $this->gEnv->getCurrentWidgetConfigId();
		$configId = $request->trimValueOf(M3_REQUEST_PARAM_CONFIG_ID);
		if (empty($configId)) $configId = self::DEFAULT_CONFIG_ID;
		
		// 初期値設定
		$itemCount			= event_headlineCommonDef::DEFAULT_ITEM_COUNT;	// 表示項目数
		$sortOrder			= '0';		// ソート順
		$useBaseDay			= '0';		// 基準日を使用するかどうか
		$dayCount			= 0;			// 基準日までの日数
//		$this->showImage	= 0;				// 画像を表示するかどうか
//		$this->imageType	= event_headlineCommonDef::DEFAULT_IMAGE_TYPE;				// 画像タイプ
//		$this->imageWidth	= 0;				// 画像幅
//		$this->imageHeight	= 0;				// 画像高さ
		$useRss				= 1;							// RSS配信を行うかどうか

		// 設定値を取得
		$paramObj = $this->getWidgetParamObjByConfigId($configId);
		if (!empty($paramObj)){		// 定義データが取得できたとき
			if (isset($paramObj->itemCount))	$itemCount			= $paramObj->itemCount;
			if (isset($paramObj->sortOrder))	$sortOrder			= $paramObj->sortOrder;		// ソート順
			if (isset($paramObj->useBaseDay))	$useBaseDay			= $paramObj->useBaseDay;		// 基準日を使用するかどうか
			if (isset($paramObj->dayCount))		$dayCount			= $paramObj->dayCount;			// 基準日までの日数
//			if (isset($paramObj->showImage))	$this->showImage	= $paramObj->showImage;				// 画像を表示するかどうか
//			if (isset($paramObj->imageType))	$this->imageType	= $paramObj->imageType;				// 画像タイプ
//			if (isset($paramObj->imageWidth))	$this->imageWidth	= $paramObj->imageWidth;				// 画像幅
//			if (isset($paramObj->imageHeight))	$this->imageHeight	= $paramObj->imageHeight;				// 画像高さ
			if (isset($paramObj->useRss))		$useRss				= $paramObj->useRss;// RSS配信を行うかどうか
		}
		
		// RSS配信を行わないときは終了
		if (empty($useRss)){
			// ページ作成処理中断
			$this->gPage->abortPage();
			
			// システム強制終了
			$this->gPage->exitSystem();
		}

		// イベント記事取得
		$this->defaultUrl = $this->gEnv->getDefaultUrl();
		$this->db->getEntryItems($itemCount, $this->_langId, $sortOrder, $useBaseDay, $dayCount, array($this, 'itemLoop'));
		
		if (!$this->isExistsList) $this->tmpl->setAttribute('itemlist', 'visibility', 'hidden');// 一覧非表示
		
		// RSSチャンネル部出力データ作成
		$linkUrl = $this->getUrl($this->gPage->createRssCmdUrl($this->gEnv->getCurrentWidgetId()));
		$this->rssChannel = array(	'title' => self::DEFAULT_TITLE,		// タイトル
									'link' => $linkUrl,					// RSS配信用URL
									'description' => self::DEFAULT_DESC,// 説明
									'seq' => $this->rssSeqUrl);			// 項目の並び
	}
	/**
	 * RSSのチャンネル部出力
	 *
	 * _assign()よりも後に実行される。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ
	 * @return array 						設定データ
	 */
	function _setRssChannel($request, &$param)
	{
		return $this->rssChannel;
	}
	/**
	 * 取得したメニュー項目をテンプレートに設定する
	 *
	 * @param int		$index			行番号
	 * @param array		$fetchedRow		取得行
	 * @param object	$param			任意使用パラメータ
	 * @return bool						trueを返すとループ続行。falseを返すとその時点で終了。
	 */
	function itemLoop($index, $fetchedRow)
	{
		$entryId = $fetchedRow['ee_id'];	// イベント記事ID
		$title = $fetchedRow['ee_name'];	// タイトル

		// イベント記事へのリンク
		$url = $this->defaultUrl . '?'. M3_REQUEST_PARAM_EVENT_ID . '=' . $entryId;
		$linkUrl = $this->getUrl($url, true/*リンク用*/);
		$escapedLinkUrl = $this->convertUrlToHtmlEntity($linkUrl);
		
		if (!empty($title)){
			$row = array(
				'total' => $totalViewCount,		// 閲覧数
				'link_url' => $this->convertUrlToHtmlEntity($linkUrl),		// リンク
				'name' => $this->convertToDispString($title),			// タイトル
				'date' => getW3CDate($fetchedRow['ee_start_dt'])		// イベント開催日時
			);
			$this->tmpl->addVars('itemlist', $row);
			$this->tmpl->parseTemplate('itemlist', 'a');
		
			// RSS用
			$this->rssSeqUrl[] = $linkUrl;					// 項目の並び
			
			$this->isExistsList = true;		// リスト項目が存在するかどうか
		}
		return true;
	}
}
?>
