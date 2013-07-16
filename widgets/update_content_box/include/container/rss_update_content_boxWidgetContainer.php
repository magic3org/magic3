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
 * @copyright  Copyright 2006-2009 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: rss_update_content_boxWidgetContainer.php 2632 2009-12-06 15:24:34Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseRssContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/update_content_boxDb.php');

class rss_update_content_boxWidgetContainer extends BaseRssContainer
{
	private $db;
	private $itemCount;					// リスト項目数
	private $isExistsList;				// リスト項目が存在するかどうか
	private $rssChannel;				// RSSチャンネル部出力データ
	private $rssSeqUrl = array();					// 項目の並び
	const DEFAULT_ITEM_COUNT = 10;		// デフォルトの表示項目数
	const CONTENT_TYPE = '';		// コンテンツタイプ
	const TARGET_WIDGET = 'default_content';		// 呼び出しウィジェットID
	const DEFAULT_TITLE = '更新コンテンツ';			// デフォルトのウィジェットタイトル
	const DEFAULT_DESC = '最新の更新コンテンツが取得できます。';
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->db = new update_content_boxDb();
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
		$langId = $this->gEnv->getDefaultLanguage();
		
		$this->itemCount = self::DEFAULT_ITEM_COUNT;	// 表示項目数
		$paramObj = $this->getWidgetParamObj();
		if (!empty($paramObj)){
			$this->itemCount	= $paramObj->itemCount;
		}
			
		// ログインユーザでないときは、ユーザ制限のない項目だけ表示
		$all = false;
		if ($this->gEnv->isCurrentUserLogined()) $all = true;
		
		// 一覧を作成
		$this->db->getContentList($langId, self::CONTENT_TYPE, $all, $now, array($this, 'itemsLoop'));
				
		// 画面にデータを埋め込む
		if ($this->isExistsList) $this->tmpl->setAttribute('itemlist', 'visibility', 'visible');
		
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
	function itemsLoop($index, $fetchedRow)
	{
		// 表示項目数に達したときは終了
		if ($index >= $this->itemCount) return false;
		
		$serial = $fetchedRow['vc_serial'];
		$totalViewCount = $fetchedRow['total'];
		$name = $fetchedRow['cn_name'];

		// リンク先の作成
		$linkUrl = $this->getUrl($this->gEnv->getDefaultUrl() . '?' . M3_REQUEST_PARAM_CONTENT_ID . '=' . $fetchedRow['cn_id'], true);

		if (!empty($name)){
			$row = array(
				'total' => $totalViewCount,		// 閲覧数
				'link_url' => $this->convertUrlToHtmlEntity($linkUrl),		// リンク
				'name' => $this->convertToDispString($name),			// タイトル
				'date' => getW3CDate($fetchedRow['cn_create_dt'])		// 更新日時
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
