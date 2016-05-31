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
/***************************************************************************************************
### 複製元クラス admin_blog_mainScheduleWidgetContainer ###
複製元クラスからadmin_blog_mainScheduleWidgetContainerクラスを生成する
変更行
　・親クラスファイルの読み込み(require_once)
　・クラス名定義
****************************************************************************************************/
require_once($gEnvManager->getCurrentWidgetContainerPath() . '/admin_blog_mainBaseWidgetContainer.php');

class admin_blog_mainScheduleWidgetContainer extends admin_blog_mainBaseWidgetContainer
{
	private $serialNo;		// 記事シリアル番号
	private $serialArray = array();		// 表示されている項目シリアル番号
	private $langId;		// 編集言語
	private $entryId;
	const DEFAULT_LIST_COUNT = 20;			// 最大リスト表示数
	const LINK_PAGE_COUNT		= 20;			// リンクページ数
	const EYECATCH_IMAGE_SIZE = 40;		// アイキャッチ画像サイズ
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
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
		$task = $request->trimValueOf('task');
		
		if ($task == 'schedule_detail'){		// 詳細画面
			return 'admin_schedule_detail.tmpl.html';
		} else {			// 一覧画面
			return 'admin_schedule.tmpl.html';
		}
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
		$task = $request->trimValueOf('task');
		if ($task == 'schedule_detail'){	// 詳細画面
			return $this->createDetail($request);
		} else {			// 一覧画面
			return $this->createList($request);
		}
	}
	/**
	 * JavascriptファイルをHTMLヘッダ部に設定
	 *
	 * JavascriptファイルをHTMLのheadタグ内に追加出力する。
	 * _assign()よりも後に実行される。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。
	 * @return string 						Javascriptファイル。出力しない場合は空文字列を設定。
	 */
	function _addScriptFileToHead($request, &$param)
	{
		$scriptArray = array($this->getUrl($this->gEnv->getScriptsUrl() . self::CALENDAR_SCRIPT_FILE),		// カレンダースクリプトファイル
							$this->getUrl($this->gEnv->getScriptsUrl() . self::CALENDAR_LANG_FILE),	// カレンダー言語ファイル
							$this->getUrl($this->gEnv->getScriptsUrl() . self::CALENDAR_SETUP_FILE));	// カレンダーセットアップファイル
		return $scriptArray;

	}
	/**
	 * CSSファイルをHTMLヘッダ部に設定
	 *
	 * CSSファイルをHTMLのheadタグ内に追加出力する。
	 * _assign()よりも後に実行される。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。
	 * @return string 						CSS文字列。出力しない場合は空文字列を設定。
	 */
	function _addCssFileToHead($request, &$param)
	{
		return $this->getUrl($this->gEnv->getScriptsUrl() . self::CALENDAR_CSS_FILE);
	}
	/**
	 * 一覧画面作成
	 *
	 * _setTemplate()で指定したテンプレートファイルにデータを埋め込む。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param								なし
	 */
	function createList($request)
	{
		$act = $request->trimValueOf('act');
		$this->langId	= $request->trimValueOf(M3_REQUEST_PARAM_OPERATION_LANG);		// 編集言語を取得
		if (empty($this->langId)) $this->langId = $this->_langId;
		$this->entryId = $request->trimValueOf(M3_REQUEST_PARAM_BLOG_ENTRY_ID);
		$pageNo = $request->trimIntValueOf(M3_REQUEST_PARAM_PAGE_NO, '1');				// ページ番号
		
		// 一覧表示数
		$maxListCount = self::DEFAULT_LIST_COUNT;
		
		// 総数を取得
		$totalCount = self::$_mainDb->getEntryScheduleCount($this->entryId, $this->langId);

		// ページング計算
		$this->calcPageLink($pageNo, $totalCount, $maxListCount);
		
		// ページングリンク作成
		$pageLink = $this->createPageLink($pageNo, self::LINK_PAGE_COUNT, ''/*リンク作成用(未使用)*/, 'selpage($1);return false;');
		
		// 予約記事を取得
		self::$_mainDb->getEntrySchedule($this->entryId, $this->langId, $maxListCount, $pageNo, array($this, 'itemListLoop'));
		if (count($this->serialArray) <= 0) $this->tmpl->setAttribute('itemlist', 'visibility', 'hidden');// 投稿記事がないときは、一覧を表示しない
		
		// ブログ記事を取得
		$ret = self::$_mainDb->getEntryItem($this->entryId, $this->langId, $row);
		if ($ret){
			$title = $row['be_name'];				// タイトル
		}
		
		// ページ遷移(Pagination)用
		$this->tmpl->addVar("_widget", "page_link", $pageLink);
		$this->tmpl->addVar("_widget", "total_count", $this->convertToDispString($totalCount));
		
		// その他
		$this->tmpl->addVar("_widget", "title", $this->convertToDispString($title));		// 記事タイトル
		$this->tmpl->addVar("_widget", "page", $this->convertToDispString($pageNo));
		$this->tmpl->addVar("_widget", "entry_id", $this->convertToDispString($this->entryId));
		$this->tmpl->addVar("_widget", "serial_list", implode($this->serialArray, ','));// 表示項目のシリアル番号を設定
	}
	/**
	 * 詳細画面作成
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param								なし
	 */
	function createDetail($request)
	{
		$act = $request->trimValueOf('act');
		$this->serialNo = $request->trimValueOf('serial');		// 記事シリアル番号
		$this->langId	= $request->trimValueOf(M3_REQUEST_PARAM_OPERATION_LANG);		// 編集言語を取得
		if (empty($this->langId)) $this->langId = $this->_langId;
		$this->entryId = $request->trimValueOf(M3_REQUEST_PARAM_BLOG_ENTRY_ID);
		$pageNo = $request->trimIntValueOf(M3_REQUEST_PARAM_PAGE_NO, '1');				// ページ番号
		
//		$name = $request->trimValueOf('item_name');
		$updateDate = $request->trimValueOf('item_date');		// 更新日
		$updateTime = $request->trimValueOf('item_time');		// 更新時間
		$html = $request->valueOf('item_html');
		$html2 = $request->valueOf('item_html2');
		
		if ($act == 'add'){		// 項目追加の場合
		} else if ($act == 'update'){		// 項目更新の場合
		} else if ($act == 'delete'){		// 項目削除の場合
			if (empty($this->serialNo)){
				$this->setUserErrorMsg('削除項目が選択されていません');
			}
			// エラーなしの場合は、データを削除
			if ($this->getMsgCount() == 0){
			}
		} else {		// 初期状態
			if (empty($this->serialNo)){
				$name = '';				// タイトル
				$html = '';				// HTML
				$html2 = '';				// HTML
				$updateDate = date("Y/m/d");		// 更新日
				$updateTime = date("H:i:s");		// 更新時間
			}
		}
		
		// 設定データを再取得
		if ($reloadData){		// データの再ロード
			$ret = self::$_mainDb->getEntryBySerial($this->serialNo, $row, $categoryRow);
			if ($ret){
			} else {
			}
		}
		
		// ブログ記事を取得
		$ret = self::$_mainDb->getEntryItem($this->entryId, $this->langId, $row);
		if ($ret){
			$entrySerialNo = $row['be_serial'];		// シリアル番号
			$reloadData = true;		// データの再読み込み
			
			$name = $row['be_name'];				// タイトル
			$html = $row['be_html'];				// HTML
			$html = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $this->getUrl($this->gEnv->getRootUrl()), $html);// アプリケーションルートを変換
			$html2 = $row['be_html_ext'];				// HTML
			$html2 = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $this->getUrl($this->gEnv->getRootUrl()), $html2);// アプリケーションルートを変換
			
			// アイキャッチ画像
			$iconUrl = blog_mainCommonDef::getEyecatchImageUrl($row['be_thumb_filename'], self::$_configArray[blog_mainCommonDef::CF_ENTRY_DEFAULT_IMAGE], self::$_configArray[blog_mainCommonDef::CF_THUMB_TYPE], 's'/*sサイズ画像*/) . '?' . date('YmdHis');
			if (empty($row['be_thumb_filename'])){
				$iconTitle = 'アイキャッチ画像未設定';
			} else {
				$iconTitle = 'アイキャッチ画像';
			}
			$eyecatchImageTag = '<img src="' . $this->getUrl($iconUrl) . '" width="' . self::EYECATCH_IMAGE_SIZE . '" height="' . self::EYECATCH_IMAGE_SIZE . '" rel="m3help" alt="' . $iconTitle . '" title="' . $iconTitle . '" />';
		} else {
			$entrySerialNo = 0;
			

		}


		
		// プレビュー用URL
		$previewUrl = $this->gEnv->getDefaultUrl() . '?' . M3_REQUEST_PARAM_BLOG_ENTRY_ID . '=' . $this->entryId;
		if ($historyIndex >= 0) $previewUrl .= '&' . M3_REQUEST_PARAM_HISTORY . '=' . $historyIndex;		// 履歴番号(旧データの場合のみ有効)
		$previewUrl .= '&' . M3_REQUEST_PARAM_OPERATION_COMMAND . '=' . M3_REQUEST_CMD_PREVIEW;
		if ($this->isMultiLang) $previewUrl .= '&' . M3_REQUEST_PARAM_OPERATION_LANG . '=' . $this->langId;		// 多言語対応の場合は言語IDを付加
		$this->tmpl->addVar('_widget', 'preview_url', $previewUrl);// プレビュー用URL(フロント画面)
		
		// CKEditor用のCSSファイルを読み込む
		$this->loadCKEditorCssFiles($previewUrl);
		
		// #### 更新、新規登録部をを作成 ####
		if (empty($this->serialNo)){		// シリアル番号のときは新規とする
			$this->tmpl->addVar("_widget", "id", '新規');
			$this->tmpl->setAttribute('add_button', 'visibility', 'visible');// 「新規追加」ボタン
		} else {
			$this->tmpl->addVar("_widget", "id", $id);
			$this->tmpl->setAttribute('update_button', 'visibility', 'visible');
		}
		$this->tmpl->addVar("_widget", "serial", $this->serialNo);
		
		// その他
		$this->tmpl->addVar("_widget", "page", $this->convertToDispString($pageNo));
		$this->tmpl->addVar("_widget", "entry_id", $this->convertToDispString($this->entryId));
		$this->tmpl->addVar("_widget", "id", $this->convertToDispString($this->entryId));
		$this->tmpl->addVar("_widget", "item_name", $this->convertToDispString($name));		// 名前
		$this->tmpl->addVar("_widget", "update_date", $updateDate);	// 更新日
		$this->tmpl->addVar("_widget", "update_time", $updateTime);	// 更新時間
		$this->tmpl->addVar("_widget", "item_html", $html);		// HTML
		$this->tmpl->addVar("_widget", "item_html2", $html2);		// HTML(続き)
		$this->tmpl->addVar("_widget", "eyecatch_image", $eyecatchImageTag);		// アイキャッチ画像
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

		$row = array(
			'no' => $this->convertToDispString($index + 1),								// 項目番号
			'serial' => $this->convertToDispString($fetchedRow['be_serial']),			// シリアル番号
			'user' => $this->convertToDispString($fetchedRow['lu_name']),	// 更新者
			'date' => $this->convertToDispDateTime($fetchedRow['be_create_dt'])	// 更新日時
		);
		$this->tmpl->addVars('itemlist', $row);
		$this->tmpl->parseTemplate('itemlist', 'a');
		
		// 表示中のコンテンツIDを保存
		$this->serialArray[] = $fetchedRow['be_serial'];
		return true;
	}
}
?>
