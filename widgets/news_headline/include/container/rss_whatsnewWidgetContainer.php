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
require_once($gEnvManager->getCurrentWidgetContainerPath() . '/whatsnewCommonDef.php');

class rss_whatsnewWidgetContainer extends BaseRssContainer
{
	private $db;
	private $langId;
	private $isExistsList;				// リスト項目が存在するかどうか
	private $rssChannel;				// RSSチャンネル部出力データ
	private $rssSeqUrl = array();					// 項目の並び
	private $configArray;		// 新着情報定義値
	const DEFAULT_CONFIG_ID = 0;
	const DEFAULT_ITEM_COUNT = 10;		// デフォルトの表示項目数
	const DEFAULT_TITLE = '新着情報';		// デフォルトのウィジェットタイトル名
	const DEFAULT_DESC = 'サイトの最新情報が取得できます。';
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// 初期値
		$this->langId = $this->gEnv->getCurrentLanguage();
				
		// DBオブジェクト作成
		$this->db = new whatsnewDb();
		
		// 共通定義値取得
		$this->configArray = whatsnewCommonDef::loadConfig($this->db);
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
		
		// RSS配信を行わないときは終了
		if (empty($useRss)){
			// ページ作成処理中断
			$this->gPage->abortPage();
			
			// システム強制終了
			$this->gPage->exitSystem();
		}
		
		// 一覧を作成
		$this->db->getNewsList('', $itemCount, 1, array($this, 'itemLoop'));
				
		// 画面にデータを埋め込む
		if ($this->isExistsList) $this->tmpl->setAttribute('itemlist', 'visibility', 'visible');
		
		// RSSチャンネル部出力データ作成
		$linkUrl = $this->getUrl($this->gPage->createRssCmdUrl($this->gEnv->getCurrentWidgetId()));
		$this->rssChannel = array(	'title'			=> self::DEFAULT_TITLE,		// タイトル
									'link'			=> $linkUrl,					// RSS配信用URL
									'description'	=> self::DEFAULT_DESC,// 説明
									'seq'			=> $this->rssSeqUrl);			// 項目の並び
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
		// コンテンツタイトル取得
		$contentType = $fetchedRow['nw_content_type'];	// コンテンツタイプ
		$contentId = $fetchedRow['nw_content_id'];	// コンテンツID
		if (!empty($contentType) && !empty($contentId)){
			$contentTitle = whatsnewCommonDef::getContentTitle($this->db, $this->langId, $contentType, $contentId);
		} else {
			$contentTitle = $fetchedRow['nw_name'];	// コンテンツタイトル
		}
		
		// リンク先URL
		$linkUrl = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $this->gEnv->getRootUrl(), $fetchedRow['nw_url']);		// URLを修正
		
		// メッセージ
		$message = $fetchedRow['nw_message'];
		$keyTag = M3_TAG_START . M3_TAG_MACRO_TITLE . M3_TAG_END;
		$message = str_replace($keyTag, $contentTitle, $message);// タイトルを変換
		
		if (!empty($message)){
			$row = array(
				'link_url' => $this->convertUrlToHtmlEntity($linkUrl),		// リンク
				'name' => $this->convertToDispString($message),			// メッセージ
				'date' => getW3CDate($fetchedRow['nw_regist_dt'])		// 登録日時
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
