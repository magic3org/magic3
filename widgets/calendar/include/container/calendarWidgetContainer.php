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
require_once($gEnvManager->getContainerPath() . '/baseWidgetContainer.php');
require_once($gEnvManager->getWidgetContainerPath('calendar') . '/default_calendarCommonDef.php');
require_once($gEnvManager->getWidgetDbPath('calendar') . '/calendarDb.php');

class calendarWidgetContainer extends BaseWidgetContainer
{
	private $db;			// DB接続オブジェクト
	private $events;		// 表示イベント
	private $dates;			// 有効日
	private $langId;		// 言語
	private $css;		// デザインCSS
	private $addScript = array();		// 追加スクリプト
	private $showEventTooltip;		// イベント記事用のツールチップを表示するかどうか
	private $dateTypeInfo;		// 日付データタイプ
	private $optionDateTypeInfo;		// 日付データタイプ(基本日オプション)
	private $dateInfo;		// 日付データ
	const DEFAULT_CONFIG_ID = 0;
	const DEFAULT_TITLE = 'カレンダー';		// デフォルトのウィジェットタイトル名
	const MAX_ITEM_COUNT = 100;				// カレンダーに表示する項目の最大数
	const GOOGLE_SCRIPT_FILE	= '/jquery/fullcalendar-2.2.6/gcal.js';				// Googleカレンダー用スクリプト
	const DEFAULT_EVENT_TOOLTIP_TITLE_STYLE		= "color: '#fff', background: 'red'";		// ツールチップ(タイトル)のスタイル
	const DEFAULT_EVENT_TOOLTIP_BORDER_STYLE	= "width: 2, radius: 5, color: '#444'";		// ツールチップ(ボーダー)のスタイル
	const DEFAULT_SIMPLE_EVENT_CLASS_NAME = 'simple_event_default';			// デフォルトのクラス名(簡易イベント)
	const DEFAULT_EVENT_CLASS_NAME = 'event_default';			// デフォルトのクラス名(イベント記事)
	const OVERWRITE_CSS_FILE = '/overwrite.css';		// fullcalendarCSS上書き用ファイル
	const EVENT_ID_HEAD = 'e-';							// イベント記事の識別用IDヘッダ
	const SIMPLE_EVENT_ID_HEAD = 's-';					// 簡易イベント記事の識別用IDヘッダ
	
	// DB定義
	const CF_GOOGLE_API_KEY	= 'google_api_key';		// GoogleAPIキー
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->db = new calendarDb();
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
		return 'main.tmpl.html';
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
		// 初期値設定
		$this->langId = $this->gEnv->getCurrentLanguage();

		$act = $request->trimValueOf('act');
		if ($act == 'getdata'){
			$this->getData($request);
		} else {		// カレンダー表示
			$this->showCalendar($request);
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
	 * CSSデータをHTMLヘッダ部に設定
	 *
	 * CSSデータをHTMLのheadタグ内に追加出力する。
	 * _assign()よりも後に実行される。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。
	 * @return string 						CSS文字列。出力しない場合は空文字列を設定。
	 */
	function _addCssToHead($request, &$param)
	{
		return $this->css;
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
		return $this->addScript;
	}
	/**
	 * カレンダー表示
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param								なし
	 */
	function showCalendar($request)
	{
		// 定義ID取得
		$configId = $this->gEnv->getCurrentWidgetConfigId();
		if (empty($configId)) $configId = self::DEFAULT_CONFIG_ID;
	
		// パラメータオブジェクトを取得
		$targetObj = $this->getWidgetParamObjByConfigId($configId);
		if (empty($targetObj)){// 定義データが取得できないときは終了
			$this->cancelParse();		// テンプレート変換処理中断
			return;
		}
		// 設定値を取得
		$dateDefId	= $targetObj->dateDefId;		// カレンダー定義ID
		
		// デフォルト値設定
		$simpleEventTooltipTitleStyle = self::DEFAULT_EVENT_TOOLTIP_TITLE_STYLE;		// ツールチップ(タイトル)のスタイル
		$simpleEventTooltipBorderStyle = self::DEFAULT_EVENT_TOOLTIP_BORDER_STYLE;		// ツールチップ(ボーダー)のスタイル
		$eventTooltipTitleStyle = self::DEFAULT_EVENT_TOOLTIP_TITLE_STYLE;		// ツールチップ(タイトル)のスタイル
		$eventTooltipBorderStyle = self::DEFAULT_EVENT_TOOLTIP_BORDER_STYLE;		// ツールチップ(ボーダー)のスタイル
		$layoutTooltip = $this->getParsedTemplateData('default_tooltip.tmpl.html');		// ツールチップのレイアウト	
		$closedDateStyle	= default_calendarCommonDef::DEFAULT_CLOSED_DATE_STYLE;		// 休業日スタイル	
				
		$viewOption = $targetObj->viewOption;	// FullCalendar表示オプション
		if (isset($targetObj->showSimpleEvent)) $showSimpleEvent = $targetObj->showSimpleEvent;		// 簡易イベント記事を表示するかどうか
		if (isset($targetObj->showEvent)) $showEvent = $targetObj->showEvent;		// イベント記事を表示するかどうか
		if (isset($targetObj->showEventTooltip)) $this->showEventTooltip	= $targetObj->showEventTooltip;		// イベント記事用のツールチップを表示するかどうか
		if (isset($targetObj->showHoliday)) $showHoliday = $targetObj->showHoliday;		// 祝日を表示するかどうか
		if (isset($targetObj->simpleEventTooltipTitleStyle)) $simpleEventTooltipTitleStyle = $targetObj->simpleEventTooltipTitleStyle;		// ツールチップ(タイトル)のスタイル
		if (isset($targetObj->simpleEventTooltipBorderStyle)) $simpleEventTooltipBorderStyle = $targetObj->simpleEventTooltipBorderStyle;		// ツールチップ(ボーダー)のスタイル
		if (isset($targetObj->eventTooltipTitleStyle)) $eventTooltipTitleStyle = $targetObj->eventTooltipTitleStyle;		// ツールチップ(タイトル)のスタイル
		if (isset($targetObj->eventTooltipBorderStyle)) $eventTooltipBorderStyle = $targetObj->eventTooltipBorderStyle;		// ツールチップ(ボーダー)のスタイル
		if (isset($targetObj->layoutTooltip)) $layoutTooltip = $targetObj->layoutTooltip;		// ツールチップのレイアウト
		if (isset($targetObj->holidayColor)) $holidayColor = $targetObj->holidayColor;		// 背景色(祝日)
		if (isset($targetObj->css)) $this->css = $targetObj->css;			// デザインCSS
		
		$ret = $this->db->getCalendarDef($dateDefId, $row);
		if ($ret){
			$openDateStyle		= $row['cd_open_date_style'];		// 開業日スタイル
			$closedDateStyle	= $row['cd_closed_date_style'];		// 休業日スタイル
			
			// 開業日、休業日スタイルを設定
			if (!empty($openDateStyle)){
				$this->tmpl->setAttribute('show_dates', 'visibility', 'visible');
				$this->tmpl->addVar("show_dates", "css", $openDateStyle);
			}
			if (!empty($closedDateStyle)){
				$this->tmpl->setAttribute('show_closeddates', 'visibility', 'visible');
				$this->tmpl->addVar("show_closeddates", "css", $closedDateStyle);
			}
		}
		
		// 追加スクリプト
		$this->addScript = array($this->getUrl($this->gEnv->getScriptsUrl() . self::GOOGLE_SCRIPT_FILE));
		
		// 取得コンテンツタイプ
		$typeArray = array();
		if ($showSimpleEvent) $typeArray[] = 'simpleevent';
		if ($showEvent) $typeArray[] = 'event';
		$type = implode(',', $typeArray);
		
		list($year, $month, $day) = explode('/', date('Y/m/d'));	// 現在日時
		$month = intval($month) -1;
		$day = intval($day) - 1;
		
		// 祝日表示
		if ($showHoliday){
			$googleApiKey = $this->gSystem->getSystemConfig(self::CF_GOOGLE_API_KEY);			// GoogleAPIキー取得
			
			$this->tmpl->setAttribute('show_holiday', 'visibility', 'visible');
			if (empty($holidayColor)) $holidayColor = 'red';
			$this->tmpl->addVar("show_holiday", "color", $this->convertToDispString($holidayColor));
			$this->tmpl->addVar("show_holiday", "google_api_key", $googleApiKey);
		}
		// ツールチップ用のデータを追加
		if ($this->showEventTooltip || $showSimpleEvent){
			$this->tmpl->setAttribute('show_tooltip', 'visibility', 'visible');
			
			// ツールチップスタイル
			$this->tmpl->addVar("show_tooltip", "simple_title_style", $simpleEventTooltipTitleStyle);
			$this->tmpl->addVar("show_tooltip", "simple_border_style", $simpleEventTooltipBorderStyle);
			$this->tmpl->addVar("show_tooltip", "title_style", $eventTooltipTitleStyle);
			$this->tmpl->addVar("show_tooltip", "border_style", $eventTooltipBorderStyle);

			// ### ツールチップコンテンツ ###
			// 簡易イベント
			$tooltipOn = 'false';
			if ($showSimpleEvent){
				$contentText = 'event.content';
				$tooltipOn = 'true';			// ツールチップ表示
			} else {
				$contentText = '\'\'';
			}
			$this->tmpl->addVar("show_tooltip", "simple_event_content", $contentText);
			$this->tmpl->addVar("show_tooltip", "simple_event_tooltip_on", $tooltipOn);
			
			// イベント記事
			$tooltipOn = 'false';
			if ($this->showEventTooltip){
				$contentInfo = array();
				$contentInfo[M3_TAG_MACRO_CONTENT_START_TIME]	= "' + (event.start ? (moment(event.start).format('H:mm')) : '') + '";		// コンテンツ置換キー(開始時間)
				$contentInfo[M3_TAG_MACRO_CONTENT_END_TIME]		= "' + (event.end ? (moment(event.end).format('H:mm')) : '') + '";		// コンテンツ置換キー(終了時間)
				$contentInfo[M3_TAG_MACRO_CONTENT_LOCATION]		= "' + event.location + '";			// コンテンツ置換キー(場所)
				$contentInfo[M3_TAG_MACRO_CONTENT_DESCRIPTION]	= "' + event.description + '";			// コンテンツ置換キー(概要)
				$contentText = $this->convertM3ToText($layoutTooltip, $contentInfo, true/*改行コード削除*/);
				$contentText = "'" . $contentText . "'";		// 「'」で括る
				$tooltipOn = 'true';			// ツールチップ表示
			} else {
				$contentText = '\'\'';
			}
			$this->tmpl->addVar("show_tooltip", "content", $contentText);
			$this->tmpl->addVar("show_tooltip", "event_tooltip_on", $tooltipOn);
		}
		
		// データを埋め込む
		$this->tmpl->addVar("_widget", "type", $type);		// 取得コンテンツタイプ
		$this->tmpl->addVar("_widget", "year", $year);
		$this->tmpl->addVar("_widget", "month", $month);
		$this->tmpl->addVar("_widget", "day", $day);
		$this->tmpl->addVar("_widget", "option", $this->convertToDispString($viewOption));
		$this->tmpl->addVar("_widget", "sub_id", $this->gEnv->getCurrentPageSubId());			// カレンダー定義IDを取得するにはページサブIDが必要
		$this->tmpl->addVar("_widget", "overwrite_css", $this->getUrl($this->gEnv->getCurrentWidgetCssUrl() . self::OVERWRITE_CSS_FILE));
	}
	/**
	 * カレンダー情報データ取得
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param								なし
	 */
	function getData($request)
	{
		// 定義ID取得
		$configId = $this->gEnv->getCurrentWidgetConfigId();
		if (empty($configId)) $configId = self::DEFAULT_CONFIG_ID;
	
		// パラメータオブジェクトを取得
		$targetObj = $this->getWidgetParamObjByConfigId($configId);
		if (empty($targetObj)){// 定義データが取得できないときは終了
			$this->cancelParse();		// テンプレート変換処理中断
			return;
		}
		// 設定値を取得
		$dateDefId	= $targetObj->dateDefId;		// カレンダー定義ID
		
		// 画面出力キャンセル
		$this->cancelParse();
		
		$eventType = $request->trimValueOf('type');			// 取得コンテンツタイプ
		$typeArray = array();
		if (!empty($eventType)) $typeArray = explode(',', $eventType);
		// カレンダーの表示期間を取得。終了日は、表示される日の翌日が設定されている
		$startDt = $request->trimValueOf('start');
		if (!empty($startDt)) $startDt = $this->convertToProperDate($startDt);
		$endDt = $request->trimValueOf('end');
		if (!empty($endDt)) $endDt = $this->convertToProperDate($endDt);
			
		// 表示データを取得
		$this->events = array();
		if (in_array('simpleevent', $typeArray)){			// 簡易イベント取得の場合
			// イベント取得
			$this->db->getEvent(self::MAX_ITEM_COUNT, 1, $startDt, $endDt, $this->langId, array($this, 'simpleEventLoop'));
		}
		if (in_array('event', $typeArray)){			// イベント記事取得の場合
			// イベント取得
			$this->db->getEventItems(self::MAX_ITEM_COUNT, 1, $startDt, $endDt, $this->langId, array($this, 'eventLoop'));
		}
		// Ajax戻りデータ
		$this->gInstance->getAjaxManager()->addData('events', $this->events);
		
		// 日付定義取得
		list($this->dates, $this->closeddates) = $this->getOpenDate($dateDefId, $startDt, $endDt);
		
		// Ajax戻りデータ
		$this->gInstance->getAjaxManager()->addData('dates', $this->dates);
		$this->gInstance->getAjaxManager()->addData('closeddates', $this->closeddates);
	}
	/**
	 * 取得したコンテンツ項目をテンプレートに設定する
	 *
	 * @param int		$index			行番号
	 * @param array		$fetchedRow		取得行
	 * @param object	$param			任意使用パラメータ
	 * @return bool						trueを返すとループ続行。falseを返すとその時点で終了。
	 */
	function simpleEventLoop($index, $fetchedRow)
	{
		$entryId = $fetchedRow['cv_id'];// 記事ID
		$title = $fetchedRow['cv_name'];// タイトル
		$isAllDay = $fetchedRow['cv_is_all_day'];			// 終日イベントかどうか
		if ($fetchedRow['cv_end_dt'] == $this->gEnv->getInitValueOfTimestamp()){		// 開催開始日時のみ表示のとき
			if ($isAllDay){		// 終日イベントのとき
				$startDate = $this->convertToDispDate($fetchedRow['cv_start_dt']);// 開催日時(開始)
			} else {
				$startDate = $fetchedRow['cv_start_dt'];// 開催日時(開始)
			}
			$endDate = '';// 開催日時(終了)
		} else {
			if ($isAllDay){		// 終日イベントのとき
				$startDate = $this->convertToDispDate($fetchedRow['cv_start_dt']);// 開催日時(開始)
				$endDate = $this->convertToDispDate($fetchedRow['cv_end_dt']);// 開催日時(終了)
			} else {
				$startDate = $fetchedRow['cv_start_dt'];// 開催日時(開始)
				$endDate = $fetchedRow['cv_end_dt'];// 開催日時(終了)
			}
		}

		// イベント記事へのリンクを生成
//		$linkUrl = $this->getUrl($this->gEnv->getDefaultUrl() . '?'. M3_REQUEST_PARAM_EVENT_ID . '=' . $entryId, true/*リンク用*/);
		
		$event = array(
						'id'	=> self::SIMPLE_EVENT_ID_HEAD . $entryId,
						'title'	=> $title,
						'start'	=> $startDate,		// 開始
						'end'	=> $endDate,		// 終了
						'url'	=> $linkUrl,		// リンク先
						'className'	=> self::DEFAULT_SIMPLE_EVENT_CLASS_NAME,				// イベントクラス名
//						'allDay'	=> 'true',		// 

						// ツールチップ用データ
						'content'	=> $fetchedRow['cv_html']			// ツールチップコンテンツ
//						'location' => $fetchedRow['cv_place'],			// 場所
//						'description' => $fetchedRow['cv_summary']		// 概要
						);	
		
		$this->events[] = $event;
		return true;
	}
	/**
	 * 取得したコンテンツ項目をテンプレートに設定する
	 *
	 * @param int		$index			行番号
	 * @param array		$fetchedRow		取得行
	 * @param object	$param			任意使用パラメータ
	 * @return bool						trueを返すとループ続行。falseを返すとその時点で終了。
	 */
	function eventLoop($index, $fetchedRow)
	{
		$entryId = $fetchedRow['ee_id'];// 記事ID
		$title = $fetchedRow['ee_name'];// タイトル
		$isAllDay = $fetchedRow['ee_is_all_day'];			// 終日イベントかどうか
		if ($fetchedRow['ee_end_dt'] == $this->gEnv->getInitValueOfTimestamp()){		// 開催開始日時のみ表示のとき
			if ($isAllDay){		// 終日イベントのとき
				$startDate = $this->convertToDispDate($fetchedRow['ee_start_dt']);// 開催日時(開始)
			} else {
				$startDate = $fetchedRow['ee_start_dt'];// 開催日時(開始)
			}
			$endDate = '';// 開催日時(終了)
		} else {
			if ($isAllDay){		// 終日イベントのとき
				$startDate = $this->convertToDispDate($fetchedRow['ee_start_dt']);// 開催日時(開始)
				$endDate = $this->convertToDispDate($fetchedRow['ee_end_dt']);// 開催日時(終了)
			} else {
				$startDate = $fetchedRow['ee_start_dt'];// 開催日時(開始)
				$endDate = $fetchedRow['ee_end_dt'];// 開催日時(終了)
			}
		}

		// イベント記事へのリンクを生成
		$linkUrl = $this->getUrl($this->gEnv->getDefaultUrl() . '?'. M3_REQUEST_PARAM_EVENT_ID . '=' . $entryId, true/*リンク用*/);
		
		$event = array(
						'id'	=> self::EVENT_ID_HEAD . $entryId,
						'title'	=> $title,
						'start'	=> $startDate,		// 開始
						'end'	=> $endDate,		// 終了
						'url'	=> $linkUrl,		// リンク先
						'className'	=> self::DEFAULT_EVENT_CLASS_NAME,				// イベントクラス名

						// ツールチップ用データ
						'location' => $fetchedRow['ee_place'],			// 場所
						'description' => $fetchedRow['ee_summary']		// 概要
						);	
		
		$this->events[] = $event;
		return true;
	}
	/**
	 * 開業日取得
	 *
	 * @param string	$defId				カレンダー定義ID
	 * @param timestamp	$startDt			期間(開始日)
	 * @param timestamp	$endDt				期間(終了日)
	 * @param array							日付(yyyy-mm-dd)の配列
	 */
	function getOpenDate($defId, $startDt, $endDt)
	{
		// 日付情報初期化
		$openDateInfo = array();
		$closedDateInfo = array();
		
		$this->dateInfo = array();
		$ret = $this->db->getCalendarDef($defId, $row);
		if ($ret){
			$name = $row['cd_name'];
			$repeatType 		= $row['cd_repeat_type'];
			$openDateStyle		= $row['cd_open_date_style'];		// 開業日スタイル
			$closedDateStyle	= $row['cd_closed_date_style'];		// 休業日スタイル
	
			// 基本日を取得
			$this->dateTypeInfo = array();		// 日付データタイプ
			$this->db->getDateList($defId, 0/*基本日データ*/, array($this, 'dateLoop'));
			
			// 基本日オプションを取得
			$this->optionDateTypeInfo = array();		// 日付データタイプ(基本日オプション)
			$this->db->getDateList($defId, 10/*基本日データ(オプション)*/, array($this, 'optionDateLoop'));
		} else {
			return $this->dateInfo;
		}
		
		// カレンダーを作成
		list($year, $month, $day) = explode('/', $startDt);
		$year = intval($year);
		$month = intval($month);
		$day = intval($day);
		$week = date('w', mktime(0, 0, 0, $month, $day, $year));		// 曜日(0=日曜日,1=月曜日...)
		
		// 基本日データ作成
		list($endYear, $endMonth, $endDay) = explode('/', $endDt);
		$endYear = intval($endYear);
		$endMonth = intval($endMonth);
		$endDay = intval($endDay);		
		$endTime = mktime(0, 0, 0, $endMonth, $endDay, $endYear);
		// 先頭月の情報を取得
		$startWeek = date('w', mktime(0, 0, 0, $month, 1, $year));		// 曜日(0=日曜日,1=月曜日...)	
		$weekCount = intval(($day -1) / 7) + 1;				// 何番目の週か
		while (true){
			// 月の先頭かどうか
			if (intval($day) == 1){
				$weekCount = 1;				// 何番目の週か
				$startWeek = $week;			// 先頭の曜日		
			}
			
			$date = sprintf('%04s-%02s-%02s', $year, $month, $day);
			switch ($repeatType){
				case '0':		// 繰り返しなし
				default:
					$this->dateInfo[$date] = 0;
					break;
				case '1':		// 曜日基準
					$this->dateInfo[$date] = $this->dateTypeInfo[$week];
					break;
			}
			// 基本日オプションで上書き
			if ($weekCount > 0){
				$dateTypeInfo = $this->optionDateTypeInfo[$weekCount][$week];
				if (isset($dateTypeInfo)) $this->dateInfo[$date] = $dateTypeInfo;
			}	
			
			// 翌日を求める
			$nextTime = mktime(0, 0, 0, $month, $day + 1, $year);
			if ($nextTime >= $endTime) break;
			
			list($year, $month, $day) = explode('/', date("Y/m/d", $nextTime));		
			$year = intval($year);
			$month = intval($month);
			$day = intval($day);
			
			// 曜日を更新
			$week++;
			if ($week == 7) $week = 0;
			
			// 週番号を更新
			if ($weekCount > 0 && $week == $startWeek) $weekCount++;
		}
		// 前月の基本日オプションで上書き
		

		// 例外日データで更新
		$this->db->getDateList($defId, 1/*例外日データ*/, array($this, 'exceptDateLoop'));
		
		$keys = array_keys($this->dateInfo);
		for ($i = 0; $i < count($keys); $i++){
			$key = $keys[$i];
			$value = $this->dateInfo[$key];
			if (empty($value)){
				$closedDateInfo[] = $key;		// 時間定義がある場合は取得
			} else {
				$openDateInfo[] = $key;		// 時間定義がある場合は取得
			}
		}
		return array($openDateInfo, $closedDateInfo);
	}
	/**
	 * 基本日一覧を取得
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function dateLoop($index, $fetchedRow, $param)
	{	
		$this->dateTypeInfo[]	= intval($fetchedRow['ce_date_type_id']);	// 基本日日付タイプ	
		return true;
	}
	/**
	 * 基本日オプション一覧を取得
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function optionDateLoop($index, $fetchedRow, $param)
	{
		$param = $fetchedRow['ce_param'];			// オプションパラメータ
		if (!empty($param)){
			$optionArray = unserialize($param);
			$no 	= $optionArray['no'];		// 基本日オプション番号
			$week	= $optionArray['week'];		// 基本日オプション曜日
			$this->optionDateTypeInfo[$no][$week] = intval($fetchedRow['ce_date_type_id']);	// 基本日日付タイプ
		}
		return true;
	}
	/**
	 * 例外日一覧を取得
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function exceptDateLoop($index, $fetchedRow, $param)
	{
		$this->timestampToYearMonthDay($fetchedRow['ce_date'], $year, $month, $day);
		$dateStr = sprintf('%04s-%02s-%02s', $year, $month, $day);
		$dateType	= intval($fetchedRow['ce_date_type_id']);		// 基本日日付タイプ
		
		$this->dateInfo[$dateStr] = $dateType;	
		return true;
	}
}
?>
