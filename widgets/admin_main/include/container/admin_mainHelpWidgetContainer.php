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
 * @copyright  Copyright 2006-2016 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/admin_mainBaseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/admin_mainDb.php');

class admin_mainHelpWidgetContainer extends admin_mainBaseWidgetContainer
{
	private $db;	// DB接続オブジェクト
	const BREADCRUMB_TITLE = 'ヘルプ';		// 画面タイトル名(パンくずリスト)
	const MAGIC3_DOC_UPDATE_TITLE = 'ドキュメント更新情報';		// リモート表示コンテンツのタイトル
	const NAV_ID = 'helplink';				// ヘルプ項目取得用ナビゲーションID
	const MAGIC3_DOC_UPDATE_RSS = 'http://doc.magic3.org/index.php?cmd=rss&widget=wiki_update';		// Magic3ドキュメントサイト更新情報RSS
	const POS_RIGHT = 'right';			// リモート表示コンテンツキー(rightポジション用)
	const DATE_FORMAT = 'Y年 n月 j日';		// 日付フォーマット
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->db = new admin_mainDb();
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
		return 'help.tmpl.html';
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
		$act = $request->trimValueOf('act');

		// ヘルプ項目を取得
		$this->db->getNavItemsByLoop(self::NAV_ID, 0/*第1階層*/, array($this, 'itemListLoop'));
		
		// リモート表示コンテンツ(rightポジション用)作成
		$content = $this->getParsedTemplateData('help_remote_right.tmpl.html', array($this, 'makeRemoteContentRight'), $request);
		
		// リモート表示コンテンツ設定
//		$content = '<h2>てすと。。。</h2>';
		$this->gEnv->setRemoteContent(self::POS_RIGHT, $content);
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
		// パンくずリストの作成
		$this->gPage->setAdminBreadcrumbDef(array(self::BREADCRUMB_TITLE));
	}
	/**
	 * 取得したデータをテンプレートに設定する
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function itemListLoop($index, $fetchedRow, $param)
	{
		// 現在の言語に対応したテキストを取得
		$name = $this->getCurrentLangString($fetchedRow['ni_name']);
		$detail = $this->getCurrentLangString($fetchedRow['ni_help_body']);		// 説明
		
		// URLをリンクに変換
		$destMsg = '';
		$offset = 0;
		$exp = '/https?:\/\/[\w\/\@\$()!?&%#:;.,~\'=*+-]+[\w\/]+/';		// URLを検出
		while (preg_match($exp, $detail, $matches, PREG_OFFSET_CAPTURE, $offset)){
			$matchStart = $matches[0][1];
			$matchLength = strlen($matches[0][0]);
			$url = $matches[0][0];
			
			// 検出位置までの文字列を連結(HTMLエスケープ処理)
			$destMsg .= $this->convertToDispString(substr($detail, $offset, $matchStart - $offset));
			
			// リンクを作成
			$destMsg .= '<a href="' . $this->convertUrlToHtmlEntity($url) . '" class="external" target="_blank">' . $this->convertToDispString($url) . '</a>';
			
			// 読み込み位置を更新
			$offset = $matchStart + $matchLength;
		}
		// 残りの部分を連結
		$destMsg .= substr($detail, $offset);
		
		// リンクを付加
		$itemTag  = '<a href="#">' . $this->convertToDispString($name) . '</a>';
		$itemTag .= '<div>';
		//$itemTag .= $this->convertToDispString($detail) . ' ';
		$itemTag .= $destMsg . ' ';
		if (!empty($fetchedRow['ni_url'])) $itemTag .= $this->gDesign->createAdminPageLink('<i class="glyphicon glyphicon-new-window"></i>', $fetchedRow['ni_url']);			// リンクを付加
		$itemTag .= '</div>';
		
		$row = array(
			'item' => $itemTag
		);
		$this->tmpl->addVars('itemlist', $row);
		$this->tmpl->parseTemplate('itemlist', 'a');
		return true;
	}
	/**
	 * リモート表示コンテンツ(rightポジション用)作成処理コールバック
	 *
	 * @param object	$tmpl			テンプレートオブジェクト
	 * @param object	$request		任意パラメータ(HTTPリクエストオブジェクト)
	 * @param							なし
	 */
	function makeRemoteContentRight($tmpl, $request)
	{
		// タイトル設定
		$tmpl->addVar("_tmpl", "title", $this->convertToDispString(self::MAGIC3_DOC_UPDATE_TITLE));
		
		// Magic3ドキュメントサイトの更新情報RSSを取得
		$rss = simplexml_load_file(self::MAGIC3_DOC_UPDATE_RSS);
		foreach ($rss->item as $item){
			$title = $item->title;
			$date = date("Y年 n月 j日", strtotime($item->pubDate));
			$link = $item->link;
			$description = mb_strimwidth (strip_tags($item->description), 0 , 110, "…Read More", "utf-8");
//			echo $title.'+'.$link;
			
//			$name = $fetchedRow['wc_id'];
//			$date = date(self::DATE_FORMAT, strtotime($fetchedRow['wc_content_dt']));
			$name = $item->title;
			$date = date(self::DATE_FORMAT, strtotime($item->pubDate));
		
			// リンク先の作成
			//$linkUrl = $this->getUrl($this->gEnv->getDefaultUrl() . '?' . $fetchedRow['wc_id'], true);
			$linkUrl = $item->link;

			if (!isset($this->currentDate)){
				// 日付を更新
				$this->currentDate = $date;
			
				// バッファ更新
				$tmpl->clearTemplate('item_list');
			} else if ($date != $this->currentDate){
				// 前の日付を表示
				$dateRow = array(
					'date'		=> $this->convertToDispString($this->currentDate)			// 日付
				);
				$tmpl->addVars('date_list', $dateRow);
				$tmpl->parseTemplate('date_list', 'a');
			
				// 日付を更新
				$this->currentDate = $date;
			
				// バッファ更新
				$tmpl->clearTemplate('item_list');
			}
			$row = array(
				'link_url'	=> $this->convertUrlToHtmlEntity($linkUrl),		// リンク
				'name'		=> $this->convertToDispString($name)			// タイトル
			);
			$tmpl->addVars('item_list', $row);
			$tmpl->parseTemplate('item_list', 'a');
			
			$this->isExistsList = true;		// リスト項目が存在するかどうか
		}
	}
}
?>
