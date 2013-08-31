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
 * @copyright  Copyright 2006-2013 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/event_mainBaseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/event_mainDb.php');
require_once(CALENDAR_ROOT			. 'Month/Weekdays.php');

class event_mainCalendarWidgetContainer extends event_mainBaseWidgetContainer
{
	private $db;	// DB接続オブジェクト
	private $entryDays = array();		// イベントのある日付
	private $entryInfoArray;				// イベント情報
	private $css;	// カレンダー用CSS
	private $langId;		// 言語
	const TARGET_WIDGET = 'event_main';		// 呼び出しウィジェットID
	const EVENT_PAGE_NAME = 'イベント';			// イベント表示画面名
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DB接続オブジェクト作成
		$this->db = new event_mainDb();
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
		return 'calendar.tmpl.html';
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
		$this->langId = $this->gEnv->getCurrentLanguage();
		$now = date("Y/m/d H:i:s");	// 現在日時
		$year = $request->trimValueOf('year');		// 年指定
		if (!(is_numeric($year) && 1 <= $year)){			// エラー値のとき
			$year = date('Y');
		}
		$month = $request->trimValueOf('month');	// 月指定
		if (!(is_numeric($month) && 1 <= $month && $month <= 12)){			// エラー値のとき
			$month = date('n');
		}
		$day = $request->trimValueOf('day');		// 日指定
		
		// カレンダーを作成
		$calendar = new Calendar_Month($year, $month);
		$calendar->build();
		$prevMonth = $calendar->prevMonth();
		$nextMonth = $calendar->nextMonth();
		if ($prevMonth == 12){
			$prevYear = $year -1;
		} else {
			$prevYear = $year;
		}
		if ($nextMonth == 1){
			$nextYear = $year +1;
		} else {
			$nextYear = $year;
		}
		// データの存在する日を取得
		$startDt = $this->convertToProperDate($year . '/' . $month . '/1');
		$endDt = $this->convertToProperDate($nextYear . '/' . $nextMonth . '/1');
		$ret = $this->db->getEntryItemsForCelendar($now, $startDt, $endDt, $this->gEnv->getCurrentLanguage(), $rows);
		if ($ret) $this->createEventList($rows);
		
		// データの存在範囲を取得
		$rangeStartYearMonth = $rangeEndYearMonth = 0;
		$ret = $this->db->getTermWithEntryItems($this->langId, $rangeStartDt, $rangeEndDt);
		if ($ret){
			$this->timestampToYearMonthDay($rangeStartDt, $rangeYear, $rangeMonth, $rangeDay);
			$rangeStartYearMonth = intval(sprintf('%04s%02s', $rangeYear, $rangeMonth));
			$this->timestampToYearMonthDay($rangeEndDt, $rangeYear, $rangeMonth, $rangeDay);
			$rangeEndYearMonth = intval(sprintf('%04s%02s', $rangeYear, $rangeMonth));
		}
/*		
		$prevUrl = $this->gPage->createWidgetCmdUrl(self::TARGET_WIDGET, $this->gEnv->getCurrentWidgetId(), 'act=view&year=' . $prevYear . '&month=' . $prevMonth);
		$nextUrl = $this->gPage->createWidgetCmdUrl(self::TARGET_WIDGET, $this->gEnv->getCurrentWidgetId(), 'act=view&year=' . $nextYear . '&month=' . $nextMonth);*/
		$prevUrl = $this->_currentPageUrl . '&task=' . self::TASK_CALENDAR . '&act=view&year=' . $prevYear . '&month=' . $prevMonth;
		$nextUrl = $this->_currentPageUrl . '&task=' . self::TASK_CALENDAR . '&act=view&year=' . $nextYear . '&month=' . $nextMonth;
		
		$calendarData  = '<table class="event_main_table" style="width:100%;">' . M3_NL;
		$calendarData .= '<caption>' . M3_NL;
		// 前月へのリンク
		if (!empty($rangeStartYearMonth) && $rangeStartYearMonth <= intval(sprintf('%04s%02s', $prevYear, $prevMonth))){
			$calendarData .= '<a href="' . $this->convertUrlToHtmlEntity($this->getUrl($prevUrl, true/*リンク用*/)) . '">' . $prevMonth. '月</a>' . M3_NL;
		} else {
			$calendarData .= $prevMonth. '月' . M3_NL;
		}
		$calendarData .= '&nbsp;&nbsp;|&nbsp;&nbsp;<span style="font-weight:bold;">' . $year . '年' . $month . '月</span>&nbsp;&nbsp;|&nbsp;&nbsp;' . M3_NL;
		// 翌月へのリンク
		if (intval(sprintf('%04s%02s', $nextYear, $nextMonth)) <= $rangeEndYearMonth){
			$calendarData .= '<a href="' . $this->convertUrlToHtmlEntity($this->getUrl($nextUrl, true/*リンク用*/)) . '">' . $nextMonth . '月</a>' . M3_NL;
		} else {
			$calendarData .= $nextMonth . '月' . M3_NL;
		}
		$calendarData .= '</caption>' . M3_NL;
		$calendarData .= '<tr>' . M3_NL;
		$calendarData .= '<th style="background:none;width:10%;">日付</th>' . M3_NL;
		$calendarData .= '<th style="background:none;width:90%;">イベント</th>' . M3_NL;
		$calendarData .= '</tr>' . M3_NL;
		$week = -1;
		while ($fetchedDay = $calendar->fetch()){
			// 曜日を取得
			if ($week == -1){
				$week = date("w", $fetchedDay->getTimeStamp());
			} else {
				$week++;
				if ($week == 7) $week = 0;
			}
			$weekName = '';		// 曜日名
			$weekClass = '';		// 曜日CSSクラス名
			switch ($week){
				case 0: $weekName = '日';
						$weekClass = 'sun'; break;
				case 1: $weekName = '月'; break;
				case 2: $weekName = '火'; break;
				case 3: $weekName = '水'; break;
				case 4: $weekName = '木'; break;
				case 5: $weekName = '金'; break;
				case 6: $weekName = '土'; 
						$weekClass = 'sat'; break;
			}
			$calendarData .= "<tr>" . M3_NL;
			
			$dayStr = $fetchedDay->thisDay() . '(' . $weekName . ')';
			if (!empty($weekClass)) $dayStr = '<span class="' . $weekClass . '">' . $dayStr . '</span>';
			if (in_array($fetchedDay->thisDay(), $this->entryDays)){			// イベント記事あり
				//$dayUrl = $this->gPage->createWidgetCmdUrl(self::TARGET_WIDGET, $this->gEnv->getCurrentWidgetId(), 'act=view&year=' . $year . '&month=' . $month . '&day=' . $fetchedDay->thisDay());
				$dayUrl = $this->_currentPageUrl . '&act=view&year=' . $year . '&month=' . $month . '&day=' . $fetchedDay->thisDay();
				$dayLink = '<a href="' . $this->convertUrlToHtmlEntity($this->getUrl($dayUrl, true/*リンク用*/)) . '">' . $dayStr . '</a>';
				$calendarData .= '<td style="text-align:center;">'. $dayLink . '</td>' . M3_NL;
				
				// イベントの内容を設定
				$calendarData .= '<td><ul>';
				$eventArray = $this->entryInfoArray[$fetchedDay->thisDay()];
				for ($i = 0; $i < count($eventArray); $i++){
					$entryId = $eventArray[$i]['ee_id'];
					$eventUrl = $this->getUrl($this->gEnv->getDefaultUrl() . '?'. M3_REQUEST_PARAM_EVENT_ID . '=' . $entryId, true/*リンク用*/);
					$eventLink = '<a href="' . $this->convertUrlToHtmlEntity($this->getUrl($eventUrl, true/*リンク用*/)) . '">' . $this->convertToDispString($eventArray[$i]['ee_name']) . '</a>';
					
					// 場所
					$placeStr = '';
					if (!empty($eventArray[$i]['ee_place'])) $placeStr = '場所：' . $this->convertToDispString($eventArray[$i]['ee_place']);
					
					// 期間
					$dateStr = '';
					if ($eventArray[$i]['ee_end_dt'] == $this->gEnv->getInitValueOfTimestamp()){		// 期間終了がないとき
						$dateStr = '時間：' . $this->convertToDispTime($eventArray[$i]['ee_start_dt'], 1);
					} else {
						// 同日内のとき
						if ($this->timestampToDate($eventArray[$i]['ee_start_dt']) == $this->timestampToDate($eventArray[$i]['ee_end_dt'])){
							$dateStr = '時間：' . $this->convertToDispTime($eventArray[$i]['ee_start_dt'], 1) . self::DATE_RANGE_DELIMITER . 
													$this->convertToDispTime($eventArray[$i]['ee_end_dt'], 1);
						} else {
							$dateStr = '期間：' . $this->convertToDispDateTime($eventArray[$i]['ee_start_dt'], 10/*年なし*/, 10/*時分*/) . self::DATE_RANGE_DELIMITER . 
													$this->convertToDispDateTime($eventArray[$i]['ee_end_dt'], 10/*年なし*/, 10/*時分*/);
						}
					}
					
					$addStr = $dateStr . '&nbsp;&nbsp;' . $placeStr;
					$calendarData .= '<li>' . $eventLink . '<br />';
					$calendarData .= $addStr . '</li>';
				}
				$calendarData .= '</ul></td>' . M3_NL;
			} else {
				$calendarData .= '<td style="text-align:center;">' . $dayStr . '</td>' . M3_NL;
				
				$calendarData .= "<td>&nbsp;</td>" . M3_NL;
			}

			$calendarData .= "</tr>" . M3_NL;
		}
		$calendarData .= "</table>" . M3_NL;
		$this->tmpl->addVar("_widget", "calendar", $calendarData);
		
		// CSSを作成
		$this->css = $this->getParsedTemplateData('calendar.tmpl.css');
		
		// 他画面へのリンク
		$this->tmpl->setAttribute('top_link_area', 'visibility', 'visible');
		$topLink = $this->convertUrlToHtmlEntity($this->getUrl($this->_currentPageUrl, true));
		$topName = self::EVENT_PAGE_NAME;
		$this->tmpl->addVar("top_link_area", "top_url", $topLink);
		$this->tmpl->addVar("top_link_area", "top_name", $topName);
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
	 * イベント一覧を作成
	 *
	 * @param array	$entryRecords	イベント情報レコード
	 * @return bool					true=正常終了、false=異常終了
	 */
	function createEventList(&$entryRecords)
	{
		$this->entryInfoArray = array();
		$foreDay = 0;
		$entryInDay = array();		// 一日に含まれるイベント情報
		$itemCount = count($entryRecords);
		for ($i = 0; $i < $itemCount; $i++){
			// 日を取得
			$this->timestampToYearMonthDay($entryRecords[$i]['ee_start_dt'], $year, $month, $day);
		
			if ($day != $foreDay){		// 一日の最初のイベント情報のとき
				$this->entryDays[] = $day;		// イベントがある日付を保存
				
				if ($foreDay != 0){
					$this->entryInfoArray[$foreDay] = $entryInDay;
					$entryInDay = array();		// 一日に含まれるイベント情報
				}
			}
			$entryInDay[] = $entryRecords[$i];
			
			// 日付を更新
			$foreDay = $day;
		}
		if (count($entryInDay) > 0) $this->entryInfoArray[$foreDay] = $entryInDay;
		return true;
	}
}
?>
