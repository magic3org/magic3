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
	private $defaultEntryName;		// 対象記事のタイトル
	const DEFAULT_LIST_COUNT = 20;			// 最大リスト表示数
	const LINK_PAGE_COUNT		= 20;			// リンクページ数
	const EYECATCH_IMAGE_SIZE = 40;		// アイキャッチ画像サイズ
	const ICON_SIZE = 32;		// アイコンのサイズ

	const ACTIVE_ICON_FILE = '/images/system/active32.png';			// 公開中アイコン
	const INACTIVE_ICON_FILE = '/images/system/inactive32.png';		// 非公開アイコン
	// 予約状態
	const SCHEDULE_STATUS_DRAFT = 1;		// 予約状態(編集中)
	const SCHEDULE_STATUS_EXEC = 2;			// 予約状態(実行)
	const SCHEDULE_STATUS_CLOSE = 3;			// 予約状態(終了)
	
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
	 * @return								なし
	 */
	function createList($request)
	{
		$act = $request->trimValueOf('act');
		$this->langId	= $request->trimValueOf(M3_REQUEST_PARAM_OPERATION_LANG);		// 編集言語を取得
		if (empty($this->langId)) $this->langId = $this->_langId;
		$this->entryId = $request->trimValueOf(M3_REQUEST_PARAM_BLOG_ENTRY_ID);
		$pageNo = $request->trimIntValueOf(M3_REQUEST_PARAM_PAGE_NO, '1');				// ページ番号
		
		// ### 現在のブログ記事の情報を取得 ###
		$this->loadEntryInfo($this->entryId, $this->langId);
		
		if ($act == 'delete'){		// 項目削除の場合
			$listedItem = explode(',', $request->trimValueOf('seriallist'));
			$delItems = array();
			for ($i = 0; $i < count($listedItem); $i++){
				// 項目がチェックされているかを取得
				$itemName = 'item' . $i . '_selected';
				$itemValue = ($request->trimValueOf($itemName) == 'on') ? 1 : 0;
				
				if ($itemValue){		// チェック項目
					$delItems[] = $listedItem[$i];
				}
			}
			if (count($delItems) > 0){
				$ret = self::$_mainDb->delEntryItem($delItems);
				if ($ret){		// データ削除成功のとき
					$this->setGuidanceMsg('データを削除しました');
				} else {
					$this->setAppErrorMsg('データ削除に失敗しました');
				}
			}
		}
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
	 * @return								なし
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
		$status = $request->trimValueOf('item_status');		// エントリー状態(0=未設定、1=編集中、2=公開、3=非公開)
		$scheduleDate = $request->trimValueOf('item_update_date');		// 更新日
		$scheduleTime = $request->trimValueOf('item_update_time');		// 更新時間
		$html = $request->valueOf('item_html');
		$html2 = $request->valueOf('item_html2');
		
		// ### 現在のブログ記事の情報を取得 ###
		$this->loadEntryInfo($this->entryId, $this->langId);
		
		if ($act == 'add'){		// 項目追加の場合
			// 入力チェック
			switch ($status){
			case self::SCHEDULE_STATUS_DRAFT:		// 予約状態(編集中)
			case self::SCHEDULE_STATUS_CLOSE:			// 予約状態(終了)
			default:
				$this->checkDate($scheduleDate, '更新日付', true/*入力なしOK*/);
				$this->checkTime($scheduleTime, '更新時間', true/*入力なしOK*/);
				break;
			case self::SCHEDULE_STATUS_EXEC:		// 予約状態(実行)
				$this->checkDate($scheduleDate, '更新日付');
				$this->checkTime($scheduleTime, '更新時間');
				
				// 記事公開の場合は更新日時をチェック
				if (strtotime($scheduleDate . ' ' . $scheduleTime) < strtotime($entryRow['be_regist_dt']) ||		// 投稿日時よりも前の場合
					strtotime($scheduleDate . ' ' . $scheduleTime) < strtotime('now')) $this->setUserErrorMsg('更新日時が不正です');
				break;
			}
			$this->checkInput($html, '投稿内容');
			
			// エラーなしの場合は、データを登録
			if ($this->getMsgCount() == 0){
				// 更新日時は、公開開始日時に格納
				$startDt = $this->convertToProperDate($scheduleDate) . ' ' . $this->convertToProperTime($scheduleTime);
				
				// 予約記事を登録
				$otherParams = array('be_status' => $status);
				$ret = self::$_mainDb->addEntryScheduleItem($this->entryId, $this->langId, $html, $html2, $startDt, $endDt, $newSerial, $otherParams);
				if ($ret){
					$this->setGuidanceMsg('データを追加しました');
					
					// シリアル番号更新
					$this->serialNo = $newSerial;
					$reloadData = true;		// データの再ロード
				} else {
					$this->setAppErrorMsg('データ追加に失敗しました');
				}
			}
		} else if ($act == 'update'){		// 項目更新の場合
			// 入力チェック
			switch ($status){
			case self::SCHEDULE_STATUS_DRAFT:		// 予約状態(編集中)
			case self::SCHEDULE_STATUS_CLOSE:			// 予約状態(終了)
			default:
				$this->checkDate($scheduleDate, '更新日付', true/*入力なしOK*/);
				$this->checkTime($scheduleTime, '更新時間', true/*入力なしOK*/);
				break;
			case self::SCHEDULE_STATUS_EXEC:		// 予約状態(実行)
				$this->checkDate($scheduleDate, '更新日付');
				$this->checkTime($scheduleTime, '更新時間');
				
				// 記事公開の場合は更新日時をチェック
				if (strtotime($scheduleDate . ' ' . $scheduleTime) < strtotime($entryRow['be_regist_dt']) ||		// 投稿日時よりも前の場合
					strtotime($scheduleDate . ' ' . $scheduleTime) < strtotime('now')) $this->setUserErrorMsg('更新日時が不正です');
				break;
			}
			$this->checkInput($html, '投稿内容');
			
			// エラーなしの場合は、データを登録
			if ($this->getMsgCount() == 0){
				// 更新日時は、公開開始日時に格納
				$startDt = $this->convertToProperDate($scheduleDate) . ' ' . $this->convertToProperTime($scheduleTime);
				
				// 予約記事を更新
				$otherParams = array('be_status' => $status);
				$ret = self::$_mainDb->updateEntryScheduleItem($this->serialNo, $html, $html2, $startDt, $endDt, $otherParams);
				if ($ret){
					$this->setGuidanceMsg('データを更新しました');
					
					$reloadData = true;		// データの再ロード
				} else {
					$this->setAppErrorMsg('データ更新に失敗しました');
				}
			}
		} else if ($act == 'delete'){		// 項目削除の場合
			if (empty($this->serialNo)){
				$this->setUserErrorMsg('削除項目が選択されていません');
			}
			// エラーなしの場合は、データを削除
			if ($this->getMsgCount() == 0){
				$ret = self::$_mainDb->delEntryItem(array($this->serialNo));
				if ($ret){		// データ削除成功のとき
					$this->setGuidanceMsg('データを削除しました');
				} else {
					$this->setAppErrorMsg('データ削除に失敗しました');
				}
			}
		} else {		// 初期状態
			$reloadData = true;			// 設定データ再読み込み
		}

		// 予約対象のブログ記事を取得(初期設定、表示用)
		$ret = self::$_mainDb->getEntryItem($this->entryId, $this->langId, $entryRow);
		if ($ret){
//			$this->defaultEntryName = $entryRow['be_name'];				// タイトル
			$entryHtml = $entryRow['be_html'];				// HTML
			$entryHtml = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $this->getUrl($this->gEnv->getRootUrl()), $entryHtml);// アプリケーションルートを変換
			$entryHtml2 = $entryRow['be_html_ext'];				// HTML
			$entryHtml2 = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $this->getUrl($this->gEnv->getRootUrl()), $entryHtml2);// アプリケーションルートを変換

			// アイキャッチ画像
			$iconUrl = blog_mainCommonDef::getEyecatchImageUrl($entryRow['be_thumb_filename'], self::$_configArray[blog_mainCommonDef::CF_ENTRY_DEFAULT_IMAGE], self::$_configArray[blog_mainCommonDef::CF_THUMB_TYPE], 's'/*sサイズ画像*/) . '?' . date('YmdHis');
			if (empty($entryRow['be_thumb_filename'])){
				$iconTitle = 'アイキャッチ画像未設定';
			} else {
				$iconTitle = 'アイキャッチ画像';
			}
			$eyecatchImageTag = '<img src="' . $this->getUrl($iconUrl) . '" width="' . self::EYECATCH_IMAGE_SIZE . '" height="' . self::EYECATCH_IMAGE_SIZE . '" rel="m3help" alt="' . $iconTitle . '" title="' . $iconTitle . '" />';
		}
		
		// 設定データを再取得
		if ($reloadData){		// データの再ロード
			$ret = self::$_mainDb->getEntryBySerial($this->serialNo, $row, $categoryRow);
			if ($ret){
				// 予約記事で更新
				$status = $row['be_status'];				// 記事状態
				$html = $row['be_html'];				// 記事内容1
				$html = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $this->getUrl($this->gEnv->getRootUrl()), $html);// アプリケーションルートを変換
				$html2 = $row['be_html_ext'];				// 記事内容2
				$html2 = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $this->getUrl($this->gEnv->getRootUrl()), $html2);// アプリケーションルートを変換
				
				// 公開期間から更新日時を取得
				if ($row['be_active_start_dt'] == $this->gEnv->getInitValueOfTimestamp()){
					$scheduleDate = '';	// 公開期間開始日
					$scheduleTime = '';	// 公開期間開始時間
				} else {
					$scheduleDate = $this->timestampToDate($row['be_active_start_dt']);	// 公開期間開始日
					$scheduleTime = $this->timestampToTime($row['be_active_start_dt']);	// 公開期間開始時間
				}
			
				$updateUser = $row['update_user_name'];			// 予約記事更新者
				$updateDt = $row['be_update_dt'];		// 予約記事更新日時
			} else {
				$this->serialNo = 0;
				$status = self::SCHEDULE_STATUS_DRAFT;		// 予約状態(編集中)
				$html = $entryHtml;				// 記事内容1
				$html2 = $entryHtml2;				// 記事内容2
				$scheduleDate = date("Y/m/d");		// 更新日
				$scheduleTime = date("H:i:s");		// 更新時間
				$updateUser = '';			// 予約記事更新者
				$updateDt = '';		// 予約記事更新日時
			}
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
			$this->tmpl->setAttribute('delete_button', 'visibility', 'visible');
		}
		$this->tmpl->addVar("_widget", "serial", $this->serialNo);
		
		// その他
		$this->tmpl->addVar("_widget", "page", $this->convertToDispString($pageNo));
		$this->tmpl->addVar("_widget", "entry_id", $this->convertToDispString($this->entryId));
		$this->tmpl->addVar("_widget", "id", $this->convertToDispString($this->entryId));
		switch ($status){		// 記事状態
			case self::SCHEDULE_STATUS_DRAFT:	$this->tmpl->addVar("_widget", "selected_draft", 'selected');	break;		// 予約状態(編集中)
			case self::SCHEDULE_STATUS_EXEC:	$this->tmpl->addVar("_widget", "selected_exec", 'selected');	break;		// 予約状態(実行)
			case self::SCHEDULE_STATUS_CLOSE:	$this->tmpl->addVar("_widget", "selected_close", 'selected');	break;		// 予約状態(終了)
		}
		$this->tmpl->addVar("_widget", "item_name", $this->convertToDispString($this->defaultEntryName));		// 名前
		$this->tmpl->addVar("_widget", "update_date", $this->convertToDispString($scheduleDate));	// 更新日
		$this->tmpl->addVar("_widget", "update_time", $this->convertToDispString($scheduleTime));	// 更新時間
		$this->tmpl->addVar("_widget", "item_html", $html);		// HTML
		$this->tmpl->addVar("_widget", "item_html2", $html2);		// HTML(続き)
		$this->tmpl->addVar("_widget", "eyecatch_image", $eyecatchImageTag);		// アイキャッチ画像
		$this->tmpl->addVar("_widget", "update_user", $this->convertToDispString($updateUser));	// 更新者
		$this->tmpl->addVar("_widget", "update_dt", $this->convertToDispDateTime($updateDt));	// 更新日時
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
		$name = $fetchedRow['be_name'];			// 記事タイトル
		if (empty($name)) $name = $this->defaultEntryName;
		
		switch ($fetchedRow['be_status']){
		case self::SCHEDULE_STATUS_DRAFT:		// 予約状態(編集中)
			$iconUrl = $this->gEnv->getRootUrl() . self::INACTIVE_ICON_FILE;		// 非公開アイコン
			$iconTitle = '編集中';
			break;
		case self::SCHEDULE_STATUS_EXEC:		// 予約状態(実行)
			$iconUrl = $this->gEnv->getRootUrl() . self::ACTIVE_ICON_FILE;			// 公開中アイコン
			$iconTitle = '実行';
			break;
		case self::SCHEDULE_STATUS_CLOSE:			// 予約状態(終了)
			$iconUrl = $this->gEnv->getRootUrl() . self::INACTIVE_ICON_FILE;		// 非公開アイコン
			$iconTitle = '終了';
			break;
		}
		$statusImg = '<img src="' . $this->getUrl($iconUrl) . '" width="' . self::ICON_SIZE . '" height="' . self::ICON_SIZE . '" rel="m3help" alt="' . $iconTitle . '" title="' . $iconTitle . '" />';
		
		$scheduleDt = $fetchedRow['be_active_start_dt'];		// 更新日時
		$row = array(
			'index' => $this->convertToDispString($index),		// 項目番号
			'no' => $this->convertToDispString($index + 1),								// 項目番号
			'serial' => $this->convertToDispString($fetchedRow['be_serial']),			// シリアル番号
			'name' => $this->convertToDispString($name),	// 記事タイトル
			'date' => $this->convertToDispDateTime($scheduleDt),	// 予約更新日時
			'status_img' => $statusImg												// 予約状態
		);
		$this->tmpl->addVars('itemlist', $row);
		$this->tmpl->parseTemplate('itemlist', 'a');
		
		// 表示中のコンテンツIDを保存
		$this->serialArray[] = $fetchedRow['be_serial'];
		return true;
	}
	/**
	 * ブログ記事情報を取得
	 *
	 * @param string $entryId			記事ID
	 * @param string $langId			言語ID
	 * @return							なし
	 */
	function loadEntryInfo($entryId, $langId)
	{
		// 予約対象のブログ記事を取得(初期設定、表示用)
		$ret = self::$_mainDb->getEntryItem($entryId, $langId, $row);
		if ($ret){
			$this->defaultEntryName = $row['be_name'];				// タイトル
		}
	}
}
?>
