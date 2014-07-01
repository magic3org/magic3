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
require_once($gEnvManager->getContainerPath() . '/baseRssContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/whatsnewDb.php');

class rss_whatsnewWidgetContainer extends BaseRssContainer
{
	private $db;
	private $isExistsList;				// リスト項目が存在するかどうか
	private $rssChannel;				// RSSチャンネル部出力データ
	private $rssSeqUrl = array();					// 項目の並び
	private $defaultUrl;	// システムのデフォルトURL
	const DEFAULT_CONFIG_ID = 0;
	const DEFAULT_ITEM_COUNT = 10;		// デフォルトの表示項目数
	const DEFAULT_TITLE = 'ブログカテゴリ(%s)の最新記事';		// デフォルトのウィジェットタイトル名
	const DEFAULT_CATEGORY_NAME = 'カテゴリ未選択';		// デフォルトのカテゴリ名
	const DEFAULT_DESC = 'ブログカテゴリ(%s)の最新の記事が取得できます。';
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->db = new whatsnewDb();
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
		$now = date("Y/m/d H:i:s");	// 現在日時
		$langId = $this->gEnv->getCurrentLanguage();
		
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
			if (!isset($useRss)) $useRss = 1;
		}
		// カテゴリIDはURLから取得
		$categoryId = $request->trimValueOf(M3_REQUEST_PARAM_CATEGORY_ID);
		if (empty($categoryId)) $categoryId = 0;
		
/*		// 設定値を取得
		$categoryId = 0;// カテゴリID
		$itemCount = self::DEFAULT_ITEM_COUNT;	// 表示項目数
		$useRss = 1;							// RSS配信を行うかどうか
		$paramObj = $this->getWidgetParamObj();
		if (!empty($paramObj)){
			$categoryId = $paramObj->categoryId;// カテゴリID
			$itemCount	= $paramObj->itemCount;
			$useRss		= $paramObj->useRss;// RSS配信を行うかどうか
			if (!isset($useRss)) $useRss = 1;
		}*/
		
		// RSS配信を行わないときは終了
		//if (empty($useRss)) $this->cancelParse();		// 出力しない
		if (empty($useRss)){
			// ページ作成処理中断
			$this->gPage->abortPage();
			
			// システム強制終了
			$this->gPage->exitSystem();
		}
		// カテゴリ名を取得
		$categoryName = self::DEFAULT_CATEGORY_NAME;
		$ret = $this->db->getCategoryByCategoryId($categoryId, $langId, $row);
		if ($ret) $categoryName = $row['bc_name'];
		
		// 一覧を作成
		$this->defaultUrl = $this->gEnv->getDefaultUrl();
		$this->db->getEntryItems($itemCount, $langId, $categoryId, $now, array($this, 'itemLoop'));
				
		// 画面にデータを埋め込む
		if ($this->isExistsList) $this->tmpl->setAttribute('itemlist', 'visibility', 'visible');
		
		// RSSチャンネル部出力データ作成
		$linkUrl = $this->getUrl($this->gPage->createRssCmdUrl($this->gEnv->getCurrentWidgetId()));
		$this->rssChannel = array(	'title' => sprintf(self::DEFAULT_TITLE, $categoryName),		// タイトル
									'link' => $linkUrl,					// RSS配信用URL
									'description' => sprintf(self::DEFAULT_DESC, $categoryName),// 説明
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
		$totalViewCount = $fetchedRow['total'];
		$name = $fetchedRow['be_name'];

		// 記事へのリンク
		$linkUrl = $this->getUrl($this->defaultUrl . '?'. M3_REQUEST_PARAM_BLOG_ENTRY_ID . '=' . $fetchedRow['be_id'], true);
		
		if (!empty($name)){
			$row = array(
				'total' => $totalViewCount,		// 閲覧数
				'link_url' => $this->convertUrlToHtmlEntity($linkUrl),		// リンク
				'name' => $this->convertToDispString($name),			// タイトル
				'date' => getW3CDate($fetchedRow['be_regist_dt'])		// 投稿日時
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
