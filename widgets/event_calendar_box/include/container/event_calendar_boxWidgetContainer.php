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
require_once($gEnvManager->getContainerPath()		. '/baseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath()	. '/event_calendar_boxDb.php');
require_once(CALENDAR_ROOT			. 'Month/Weekdays.php');

class event_calendar_boxWidgetContainer extends BaseWidgetContainer
{
	private $db;	// DB接続オブジェクト
	private $entryDays = array();		// イベントのある日付
	private $css;	// カレンダー用CSS
	private $langId;		// 言語
	const TARGET_WIDGET = 'event_main';		// 呼び出しウィジェットID
	const DEFAULT_TITLE = 'イベントカレンダー';			// デフォルトのウィジェットタイトル
		
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DB接続オブジェクト作成
		$this->db = new event_calendar_boxDb();
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
		return 'index.tmpl.html';
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
		$calendar = new Calendar_Month_Weekdays($year, $month, 0);		// 日曜日を先頭にする
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
		$this->db->getEntryItems($now, $startDt, $endDt, $this->gEnv->getCurrentLanguage(), array($this, 'itemLoop'));
		
		// データの存在範囲を取得
		$rangeStartYearMonth = $rangeEndYearMonth = 0;
		$ret = $this->db->getTermWithEntryItems($this->langId, $rangeStartDt, $rangeEndDt);
		if ($ret){
			$this->timestampToYearMonthDay($rangeStartDt, $rangeYear, $rangeMonth, $rangeDay);
			$rangeStartYearMonth = intval(sprintf('%04s%02s', $rangeYear, $rangeMonth));
			$this->timestampToYearMonthDay($rangeEndDt, $rangeYear, $rangeMonth, $rangeDay);
			$rangeEndYearMonth = intval(sprintf('%04s%02s', $rangeYear, $rangeMonth));
		}

		$prevUrl = $this->gPage->createWidgetCmdUrl(self::TARGET_WIDGET, $this->gEnv->getCurrentWidgetId(), 'act=view&year=' . $prevYear . '&month=' . $prevMonth);
		$nextUrl = $this->gPage->createWidgetCmdUrl(self::TARGET_WIDGET, $this->gEnv->getCurrentWidgetId(), 'act=view&year=' . $nextYear . '&month=' . $nextMonth);
		$calendarData = '';
		$calendarData .= '<div align="center">' . M3_NL;
		// 前月へのリンク
		if (!empty($rangeStartYearMonth) && $rangeStartYearMonth <= intval(sprintf('%04s%02s', $prevYear, $prevMonth))){
			$calendarData .= '<a href="' . $this->convertUrlToHtmlEntity($this->getUrl($prevUrl, true/*リンク用*/)) . '">' . $prevMonth . '</a>' . M3_NL;
		} else {
			$calendarData .= $prevMonth . M3_NL;
		}
		$calendarData .= ' | ' . $year . '/' . $month . ' | ' . M3_NL;
		// 翌月へのリンク
		if (intval(sprintf('%04s%02s', $nextYear, $nextMonth)) <= $rangeEndYearMonth){
			$calendarData .= '<a href="' . $this->convertUrlToHtmlEntity($this->getUrl($nextUrl, true/*リンク用*/)) . '">' . $nextMonth . '</a>' . M3_NL;
		} else {
			$calendarData .= $nextMonth . M3_NL;
		}
		$calendarData .= '</div>' . M3_NL;
		$calendarData .= '<table id="event_calendar">' . M3_NL;
		$calendarData .= '<tr>' . M3_NL;
		$calendarData .= '<th class="sun" style="background:none;">日</th>' . M3_NL;
		$calendarData .= '<th style="background:none;">月</th>' . M3_NL;
		$calendarData .= '<th style="background:none;">火</th>' . M3_NL;
		$calendarData .= '<th style="background:none;">水</th>' . M3_NL;
		$calendarData .= '<th style="background:none;">木</th>' . M3_NL;
		$calendarData .= '<th style="background:none;">金</th>' . M3_NL;
		$calendarData .= '<th class="sat" style="background:none;">土</th>' . M3_NL;
		$calendarData .= '</tr>' . M3_NL;
		while ($Day = $calendar->fetch()) {
		    if ($Day->isFirst()) {
		        $calendarData .= "<tr>" . M3_NL;
		    }

		    if ($Day->isEmpty()) {
		        $calendarData .= "<td>&nbsp;</td>" . M3_NL;
		    } else {
				if (in_array($Day->thisDay(), $this->entryDays)){			// イベント記事あり
					$dayUrl = $this->gPage->createWidgetCmdUrl(self::TARGET_WIDGET, $this->gEnv->getCurrentWidgetId(), 'act=view&year=' . $year . '&month=' . $month . '&day=' . $Day->thisDay());
					$dayLink = '<a href="' . $this->convertUrlToHtmlEntity($this->getUrl($dayUrl, true/*リンク用*/)) . '">' . $Day->thisDay(). '</a>';
					$calendarData .= '<td>'. $dayLink ."</td>" . M3_NL;
				} else {
		        	$calendarData .= '<td>'.$Day->thisDay()."</td>" . M3_NL;
				}
		    }

		    if ($Day->isLast()) {
		        $calendarData .= "</tr>" . M3_NL;
		    }
		}
		$calendarData .= "</table>" . M3_NL;
		$this->tmpl->addVar("_widget", "calendar", $calendarData);
		
		// CSSを作成
		$this->css = $this->getParsedTemplateData('default.tmpl.css', array($this, 'makeCss'));
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
	 * 取得したデータをテンプレートに設定する
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function itemLoop($index, $fetchedRow, $param)
	{
		// 日を取得
		$this->timestampToYearMonthDay($fetchedRow['ee_start_dt'], $year, $month, $day);
		
		if (!in_array($day, $this->entryDays)) $this->entryDays[] = $day;
		return true;
	}
	/**
	 * CSSデータ作成処理コールバック
	 *
	 * @param object         $tmpl			テンプレートオブジェクト
	 * @param								なし
	 */
	function makeCss($tmpl)
	{
		// 画像URL
		$tmpl->addVar("_tmpl", "img_url", $this->getUrl($this->gEnv->getCurrentWidgetRootUrl() . '/images'));
	}
}
?>
