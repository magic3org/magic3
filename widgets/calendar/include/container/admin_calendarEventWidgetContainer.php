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
 * @copyright  Copyright 2006-2013 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getWidgetContainerPath('calendar') . '/admin_calendarBaseWidgetContainer.php');

class admin_calendarEventWidgetContainer extends admin_calendarBaseWidgetContainer
{
	private $serialNo;		// 選択中の項目のシリアル番号
	private $entryId;
	private $serialArray = array();		// 表示されている項目シリアル番号
	private $categoryArray = array();	// 取得カテゴリー
	const ICON_SIZE = 16;		// アイコンのサイズ
	const DEFAULT_LIST_COUNT = 20;			// 最大リスト表示数
	const LINK_PAGE_COUNT		= 20;			// リンクページ数
	const CALENDAR_ICON_FILE	= '/images/system/calendar.png';		// カレンダーアイコン
	const SEARCH_ICON_FILE		= '/images/system/search16.png';		// 検索用アイコン
	
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
		
		if ($task == 'event_detail'){		// 詳細画面
			return 'admin_event_detail.tmpl.html';
		} else {			// 一覧画面
			return 'admin_event.tmpl.html';
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
		if ($task == 'event_detail'){	// 詳細画面
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
		
		// ##### 検索条件 #####
		$pageNo = $request->trimIntValueOf(M3_REQUEST_PARAM_PAGE_NO, '1');				// ページ番号
		
		// 一覧表示数
		$maxListCount = self::DEFAULT_LIST_COUNT;

		$search_startDt = $request->trimValueOf('search_start');		// 検索範囲開始日付
		if (!empty($search_startDt)) $search_startDt = $this->convertToProperDate($search_startDt);
		$search_endDt = $request->trimValueOf('search_end');			// 検索範囲終了日付
		if (!empty($search_endDt)) $search_endDt = $this->convertToProperDate($search_endDt);
		$search_keyword = $request->trimValueOf('search_keyword');			// 検索キーワード

		// キーワード分割
		$parsedKeywords = $this->gInstance->getTextConvManager()->parseSearchKeyword($search_keyword);

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
				$ret = self::$_mainDb->delEvent($delItems);
				if ($ret){		// データ削除成功のとき
					$this->setGuidanceMsg('データを削除しました');
					
					// 親ウィンドウを更新
					$this->gPage->updateParentWindow();
				} else {
					$this->setAppErrorMsg('データ削除に失敗しました');
				}
			}
		} else if ($act == 'search'){		// 検索のとき
			if (!empty($search_startDt) && !empty($search_endDt) && $search_startDt > $search_endDt){
				$this->setUserErrorMsg('期間の指定範囲にエラーがあります。');
			}
			$pageNo = 1;		// ページ番号初期化
		} else if ($act == 'selpage'){			// ページ選択
		}
		// ###### 一覧の取得条件を作成 ######
		if (!empty($search_endDt)) $endDt = $this->getNextDay($search_endDt);
		
		// 総数を取得
		$totalCount = self::$_mainDb->getEventCount($search_startDt, $endDt, $this->categoryArray, $parsedKeywords);

		// ページング計算
		$this->calcPageLink($pageNo, $totalCount, $maxListCount);
/*		// 表示するページ番号の修正
		$pageCount = (int)(($totalCount -1) / $maxListCount) + 1;		// 総ページ数
		if ($pageNo < 1) $pageNo = 1;
		if ($pageNo > $pageCount) $pageNo = $pageCount;
		$this->firstNo = ($pageNo -1) * $maxListCount + 1;		// 先頭番号
		
		// ページング用リンク作成
		$pageLink = '';
		if ($pageCount > 1){	// ページが2ページ以上のときリンクを作成
			for ($i = 1; $i <= $pageCount; $i++){
				if ($i == $pageNo){
					$link = '&nbsp;' . $i;
				} else {
					$link = '&nbsp;<a href="#" onclick="selpage(\'' . $i . '\');return false;">' . $i . '</a>';
				}
				$pageLink .= $link;
			}
		}*/
		$currentBaseUrl = '';		// POST用のリンク作成
		$pageLink = $this->createPageLink($pageNo, self::LINK_PAGE_COUNT, $currentBaseUrl, 'selpage($1);return false;');
		
		// 記事項目リストを取得
		self::$_mainDb->searchEvent($maxListCount, $pageNo, $search_startDt, $endDt, $this->categoryArray, $parsedKeywords, array($this, 'itemListLoop'));
		if (count($this->serialArray) <= 0) $this->tmpl->setAttribute('itemlist', 'visibility', 'hidden');// 投稿記事がないときは、一覧を表示しない		
		
		// ボタン作成
		$searchImg = $this->getUrl($this->gEnv->getRootUrl() . self::SEARCH_ICON_FILE);
		$searchStr = '検索';
		$this->tmpl->addVar("_widget", "search_img", $searchImg);
		$this->tmpl->addVar("_widget", "search_str", $searchStr);
		
		// 検索結果
		$this->tmpl->addVar("_widget", "page_link", $pageLink);
		$this->tmpl->addVar("_widget", "total_count", $totalCount);
		
		// 検索条件
		$this->tmpl->addVar("_widget", "search_start", $search_startDt);	// 開始日付
		$this->tmpl->addVar("_widget", "search_end", $search_endDt);	// 終了日付
		$this->tmpl->addVar("_widget", "search_keyword", $search_keyword);	// 検索キーワード

		// 非表示項目を設定
		$this->tmpl->addVar("_widget", "serial_list", implode(',', $this->serialArray));// 表示項目のシリアル番号を設定
		$this->tmpl->addVar("_widget", "page", $pageNo);	// ページ番号
	}
	/**
	 * 詳細画面作成
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param								なし
	 */
	function createDetail($request)
	{
		// 入力値取得
		$act = $request->trimValueOf('act');	
		$this->entryId = $request->trimValueOf('entry');		// 記事エントリーID
		$this->serialNo = $request->trimValueOf('serial');		// 選択項目のシリアル番号
		if (empty($this->serialNo)) $this->serialNo = 0;
		$name = $request->trimValueOf('item_name');
		$html = $request->valueOf('item_html');
		$visible = $request->trimCheckedValueOf('item_visible');		// 表示可否
		$isAllDay = $request->trimCheckedValueOf('item_is_all_day');			// 終日イベントかどうか

		// 公開期間を取得
		$start_date = $request->trimValueOf('item_start_date');		// 公開期間開始日付
		if (!empty($start_date)) $start_date = $this->convertToProperDate($start_date);
		$start_time = $request->trimValueOf('item_start_time');		// 公開期間開始時間
		if (empty($start_date)){
			$start_time = '';					// 日付が空のときは時刻も空に設定する
		} else {
			if (empty($start_time)) $start_time = '00:00';		// 日付が入っているときは時間にデフォルト値を設定
		}
		$end_date = $request->trimValueOf('item_end_date');		// 公開期間終了日付
		if (!empty($end_date)) $end_date = $this->convertToProperDate($end_date);
		$end_time = $request->trimValueOf('item_end_time');		// 公開期間終了時間
		if (empty($end_date)){
			$end_time = '';					// 日付が空のときは時刻も空に設定する
		} else {
			if (empty($end_time)) $end_time = '00:00';		// 日付が入っているときは時間にデフォルト値を設定
		}
		// 終日設定の場合は時間を初期化
		if ($isAllDay){
			if (!empty($start_time)) $start_time = '00:00';
			if (!empty($end_time)) $end_time = '00:00';
			if (!empty($start_date) && $start_date == $end_date){		// 日付が同じ場合は終了日を削除
				$end_date = '';
				$end_time = '';
			}
		}
		// 時間を修正
		if (!empty($start_time)) $start_time = $this->convertToProperTime($start_time, 1/*時分フォーマット*/);
		if (!empty($end_time)) $end_time = $this->convertToProperTime($end_time, 1/*時分フォーマット*/);
		
		$dataReload = false;		// データの再ロード
		if ($act == 'add'){		// 項目追加の場合
			// 入力チェック
			$this->checkInput($name, 'タイトル');
			$this->checkDate($start_date, '開始日付');
			$this->checkTime($start_time, '開始時間');
					
			// 期間範囲のチェック
			if (!empty($start_date) && !empty($end_date)){
				if (strtotime($start_date . ' ' . $start_time) >= strtotime($end_date . ' ' . $end_time)) $this->setUserErrorMsg('開催期間が不正です');
			}
			
			// エラーなしの場合は、データを登録
			if ($this->getMsgCount() == 0){
				// 保存データ作成
				if (empty($start_date)){
					$startDt = $this->gEnv->getInitValueOfTimestamp();
				} else {
					$startDt = $start_date . ' ' . $start_time;
				}
				if (empty($end_date)){
					$endDt = $this->gEnv->getInitValueOfTimestamp();
				} else {
					$endDt = $end_date . ' ' . $end_time;
				}
				
				$ret = self::$_mainDb->addEvent(0, ''/*言語(未使用)*/, $name, $html, $visible, $isAllDay, $startDt, $endDt, $this->categoryArray, $newSerial);
				if ($ret){
					$this->setGuidanceMsg('データを追加しました');
					// シリアル番号更新
					$this->serialNo = $newSerial;
					$dataReload = true;		// データの再ロード
					
					// 親ウィンドウを更新
					$this->gPage->updateParentWindow();
				} else {
					$this->setAppErrorMsg('データ追加に失敗しました');
				}
			}
		} else if ($act == 'update'){		// 項目更新の場合
			// 入力チェック
			$this->checkInput($name, 'タイトル');
			$this->checkDate($start_date, '開始日付');
			$this->checkTime($start_time, '開始時間');
			
			// 期間範囲のチェック
			if (!empty($start_date) && !empty($end_date)){
				if (strtotime($start_date . ' ' . $start_time) >= strtotime($end_date . ' ' . $end_time)) $this->setUserErrorMsg('開催期間が不正です');
			}
			
			// エラーなしの場合は、データを更新
			if ($this->getMsgCount() == 0){
				// 保存データ作成
				if (empty($start_date)){
					$startDt = $this->gEnv->getInitValueOfTimestamp();
				} else {
					$startDt = $start_date . ' ' . $start_time;
				}
				if (empty($end_date)){
					$endDt = $this->gEnv->getInitValueOfTimestamp();
				} else {
					$endDt = $end_date . ' ' . $end_time;
				}
				
				$ret = self::$_mainDb->updateEvent($this->serialNo, $name, $html, $visible, $isAllDay, $startDt, $endDt, $category, $newSerial);
				if ($ret){
					$this->setGuidanceMsg('データを更新しました');
					// シリアル番号更新
					$this->serialNo = $newSerial;
					$dataReload = true;		// データの再ロード
					
					// 親ウィンドウを更新
					$this->gPage->updateParentWindow();
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
				$ret = self::$_mainDb->delEvent(array($this->serialNo));
				if ($ret){		// データ削除成功のとき
					$this->setGuidanceMsg('データを削除しました');
					
					// 親ウィンドウを更新
					$this->gPage->updateParentWindow();
				} else {
					$this->setAppErrorMsg('データ削除に失敗しました');
				}
			}
		} else {	// 初期画面表示のとき
			// 初期値設定
			// 所属ブログIDは親ウィンドウから引き継ぐ
			$start_date = date("Y/m/d");		// 開催日付
			$start_time = date("H:i:s");		// 開催時間
			$visible = '1';		// 表示可否
			$isAllDay = 0;			// 終日イベントかどうか
			$dataReload = true;		// データの再ロード
		}
		
		// 設定データを再取得
		if ($dataReload){		// データの再ロード
			$ret = self::$_mainDb->getEventBySerial($this->serialNo, $row, $categoryRow);
			if ($ret){
				$this->entryId = $row['cv_id'];		// 記事ID
				$name = $row['cv_name'];				// タイトル
				$html = $row['cv_html'];				// HTML
				$html = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $this->getUrl($this->gEnv->getRootUrl()), $html);// アプリケーションルートを変換
				$start_date = $this->convertToDispDate($row['cv_start_dt']);	// 開催期間開始日
				$start_time = $this->convertToDispTime($row['cv_start_dt'], 1/*時分*/);	// 開催期間開始時間
				$end_date = $this->convertToDispDate($row['cv_end_dt']);	// 開催期間終了日
				$end_time = $this->convertToDispTime($row['cv_end_dt'], 1/*時分*/);	// 開催期間終了時間
				$visible = $row['cv_visible'];				// 表示可否
				$isAllDay = $row['cv_is_all_day'];			// 終日イベントかどうか
				$update_user = $row['lu_name'];// 更新者
				$update_dt = $this->convertToDispDateTime($row['cv_create_dt']);
			}
		}
		
		// ### 入力値を再設定 ###
		$this->tmpl->addVar('_widget', 'entry', $this->entryId);
		$this->tmpl->addVar("_widget", "name", $this->convertToDispString($name));		// 名前
		$this->tmpl->addVar("_widget", "html", $html);		// HTML
		$this->tmpl->addVar("_widget", "start_date", $start_date);	// 開催日付
		$this->tmpl->addVar("_widget", "start_time", $start_time);	// 開催時間
		$this->tmpl->addVar("_widget", "start_date", $start_date);	// 公開期間開始日
		$this->tmpl->addVar("_widget", "start_time", $start_time);	// 公開期間開始時間
		$this->tmpl->addVar("_widget", "end_date", $end_date);	// 公開期間終了日
		$this->tmpl->addVar("_widget", "end_time", $end_time);	// 公開期間終了時間
		$this->tmpl->addVar("_widget", "visible", $this->convertToCheckedString($visible));// 表示可否
		$this->tmpl->addVar("_widget", "is_all_day", $this->convertToCheckedString($isAllDay));// 終日イベントかどうか
		$this->tmpl->addVar("_widget", "update_user", $this->convertToDispString($update_user));	// 更新者
		$this->tmpl->addVar("_widget", "update_dt", $update_dt);	// 更新日時
		
		// 非表示項目を設定
		$this->tmpl->addVar("_widget", "serial", $this->serialNo);	// シリアル番号
		$this->tmpl->addVar('_widget', 'calendar_img', $this->getUrl($this->gEnv->getRootUrl() . self::CALENDAR_ICON_FILE));	// カレンダーアイコン		

		// 入力フィールドの設定、共通項目のデータ設定
		if ($this->entryId == 0){		// 記事IDが0のときは、新規追加モードにする
			$this->tmpl->setAttribute('add_button', 'visibility', 'visible');
		} else {
			if ($this->serialNo == 0){		// 未登録データのとき
				// データ追加ボタン表示
				$this->tmpl->setAttribute('add_button', 'visibility', 'visible');
			} else {
				// データ更新、削除ボタン表示
				$this->tmpl->setAttribute('delete_button', 'visibility', 'visible');// デフォルト言語以外はデータ削除
				$this->tmpl->setAttribute('update_button', 'visibility', 'visible');
			}
		}
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
		$serial = $fetchedRow['cv_serial'];// シリアル番号
		$isAllDay = $fetchedRow['cv_is_all_day'];			// 終日イベントかどうか
		$visibleStr = $this->convertToCheckedString($fetchedRow['cv_visible']);
		
		// イベント開催期間
		if ($fetchedRow['cv_end_dt'] == $this->gEnv->getInitValueOfTimestamp()){		// // 期間終了がないとき
			if ($isAllDay){		// 終日イベントのときは時間を表示しない
				$startDtStr = $this->convertToDispDate($fetchedRow['cv_start_dt']);
				$endDtStr = '';
			} else {
				$startDtStr = $this->convertToDispDateTime($fetchedRow['cv_start_dt'], 1/*ショートフォーマット*/, 10/*時分*/);
				$endDtStr = '';
			}
		} else {
			if ($isAllDay){		// 終日イベントのときは時間を表示しない
				$startDtStr = $this->convertToDispDate($fetchedRow['cv_start_dt']);
				$endDtStr = $this->convertToDispDate($fetchedRow['cv_end_dt']);
			} else {
				$startDtStr = $this->convertToDispDateTime($fetchedRow['cv_start_dt'], 1/*ショートフォーマット*/, 10/*時分*/);
				$endDtStr = $this->convertToDispDateTime($fetchedRow['cv_end_dt'], 1/*ショートフォーマット*/, 10/*時分*/);
			}
		}
		
		$row = array(
			'index' => $index,		// 項目番号
			'serial' => $serial,			// シリアル番号
			'id' => $this->convertToDispString($fetchedRow['cv_id']),			// ID
			'name' => $this->convertToDispString($fetchedRow['cv_name']),		// 名前
			'visible' => $visibleStr,											// 表示可否
			'date_start' => $startDtStr,	// 開催日時
			'date_end' => $endDtStr			// 開催日時
		);
		$this->tmpl->addVars('itemlist', $row);
		$this->tmpl->parseTemplate('itemlist', 'a');
		
		// 表示中項目のシリアル番号を保存
		$this->serialArray[] = $serial;
		return true;
	}
}
?>
