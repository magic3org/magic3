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
 * @copyright  Copyright 2006-2008 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: reserve_mainReserveWidgetContainer.php 5056 2012-07-23 02:50:32Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() .			'/baseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() .	'/reserve_mainDb.php');
require_once(CALENDAR_ROOT			. 'Month/Weekdays.php');

class reserve_mainReserveWidgetContainer extends BaseWidgetContainer
{
	private $db;	// DB接続オブジェクト
	private $sysDb;		// システムDBオブジェクト
	private $langId;	// 表示言語
	private $serialNo;			// シリアル番号
	private $hour;		// 予約時間
	private $minute;		// 予約時間(分)
	private $reserveDateArray = array();		// 予約日
	private $viewMinHour;			// 最小時間
	private $viewMaxHour;			// 最大時間
	private $availableTime = array();	// 選択可能時間
	private $unitIntervalMinute;		// 単位時間(分)
	private $maxUserReserveCount;			// 1ユーザの予約可能数
	private $maxCountPerUnit;				// 1単位あたりの予約可能数
	private $defaultResouceId;				// デフォルトのリソースID
	private $cancelAvailableDay;			// 予約キャンセル可能な日数
	const DEFAULT_CONFIG_ID = 0;		// デフォルト定義ID
	const UNIT_INTERVAL_MINUTE = 'unit_interval_minute';	// 単位時間(分)
	const MAX_USER_RESERVE_COUNT = 'max_user_reserve_count';	// 最大ユーザ予約可能数
	const MAX_COUNT_PER_UNIT = 'max_count_per_unit';			// 1単位あたりの登録可能数
	const DEFAULT_RESOURCE_ID = 'default_resource_id';			// デフォルトのリソースID
	const CANCEL_AVAILABLE_DAY = 'cancel_available_day';		// 予約キャンセル可能な日数
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		global $gInstanceManager;
			
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->db = new reserve_mainDb();
		$this->sysDb = $gInstanceManager->getSytemDbObject();
		
		$this->unitIntervalMinute	= $this->db->getReserveConfig(self::DEFAULT_CONFIG_ID, self::UNIT_INTERVAL_MINUTE);		// 1単位あたりの時間
		$this->maxUserReserveCount	= $this->db->getReserveConfig(self::DEFAULT_CONFIG_ID, self::MAX_USER_RESERVE_COUNT);		// 1ユーザ登録可能な予約数
		$this->maxCountPerUnit		= $this->db->getReserveConfig(self::DEFAULT_CONFIG_ID, self::MAX_COUNT_PER_UNIT);		// 1単位あたりの予約可能数
		$this->defaultResouceId	= $this->db->getReserveConfig(self::DEFAULT_CONFIG_ID, self::DEFAULT_RESOURCE_ID);		// デフォルトのリソースID
		$this->cancelAvailableDay	= $this->db->getReserveConfig(self::DEFAULT_CONFIG_ID, self::CANCEL_AVAILABLE_DAY);		// 予約キャンセル可能な日数
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
		return 'reserve.tmpl.html';
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
		global $gEnvManager;
		global $gAccessManager;
		global $gPageManager;
		
		// デフォルト値取得
		$now = date("Y/m/d H:i:s");	// 現在日時
		$today = date("Y/m/d");	// 現在日付
		$this->langId	= $gEnvManager->getCurrentLanguage();		// 表示言語を取得
		$userId = $gEnvManager->getCurrentUserId();
		
		// 予約可能時間範囲を取得
		$this->getAvailableTime();
		
		$act = $request->trimValueOf('act');
		$this->serialNo = $request->trimValueOf('serial');		// 選択項目のシリアル番号
				
		// 年月を取得
		// 空の場合は、予約日があれば予約日の月のカレンダーを表示
		$showReservedDate = false;		// 予約日のあるカレンダーを表示するかどうか
		$year = $request->trimValueOf('year');		// 年指定
		if (!(is_numeric($year) && 1 <= $year)){			// エラー値のとき
			$year = date('Y');
			$showReservedDate = true;		// 予約日のあるカレンダーを表示するかどうか
		}
		$month = $request->trimValueOf('month');	// 月指定
		if (!(is_numeric($month) && 1 <= $month && $month <= 12)){			// エラー値のとき
			$month = date('n');
			$showReservedDate = true;		// 予約日のあるカレンダーを表示するかどうか
		}
		
		// 入力値を取得
		$reserveDate			= $request->trimValueOf('reserve_date');
		if (!empty($reserveDate)) $reserveDate = $this->convertToProperDate($reserveDate);			// 予約日時
		$this->hour = $request->trimValueOf('time_hour');
		if ($this->hour == '') $this->hour = -1;
		$this->minute = $request->trimValueOf('time_minute');
		if ($this->minute == '') $this->minute = -1;
		
		if ($act == 'new'){		// 新規予約
			// 入力チェック
			$this->checkDate($reserveDate, '予約日付');
			if ($this->hour == -1) $this->setUserErrorMsg('予約時間(時)が選択されていません');
			if ($this->minute == -1) $this->setUserErrorMsg('予約時間(分)が選択されていません');
			
			// 予約日時
			$reserveDateTime = $reserveDate . ' ' . $this->hour . ':' . $this->minute;
			$reserveHourMinute = $this->hour * 100 + $this->minute;
			
			// 予約可能かチェック
			// 本日以降
			if ($this->getMsgCount() == 0){
				if (strtotime(date("Y/m/d")) >= strtotime($reserveDate)) $this->setUserErrorMsg('明日以降を選択してください');
			}
			
			// 営業時間範囲
			if ($this->getMsgCount() == 0){
				// 曜日を取得
				$week = date("w", strtotime($reserveDate));
				$hourMinute		= $this->availableTime[$week];			// 設定時分を取得
				$startHour		= $hourMinute[0][0];
				$startMinute	= $hourMinute[0][1];
				$endHour		= $hourMinute[0][2];
				$endMinute		= $hourMinute[0][3];
				$startHour2		= $hourMinute[1][0];
				$startMinute2	= $hourMinute[1][1];
				$endHour2		= $hourMinute[1][2];
				$endMinute2		= $hourMinute[1][3];

				switch ($week){
					case 0: $weekName = '日曜日'; break;
					case 1: $weekName = '月曜日'; break;
					case 2: $weekName = '火曜日'; break;
					case 3: $weekName = '水曜日'; break;
					case 4: $weekName = '木曜日'; break;
					case 5: $weekName = '金曜日'; break;
					case 6: $weekName = '土曜日'; break;
				}
				$msg = $weekName;
				if ($startHour == -1 && $startHour2 == -1){
					// 休日をチェック
					$msg .= 'はお休みです';
					$this->setUserErrorMsg($msg);
				} else if (!($startHour != -1 && ($startHour * 100 + $startMinute <= $reserveHourMinute && $reserveHourMinute < $endHour * 100 + $endMinute)) &&
					!($startHour2 != -1 && ($startHour2 * 100 + $startMinute2 <= $reserveHourMinute && $reserveHourMinute < $endHour2 * 100 + $endMinute2))){
					// 営業時間をチェック
					$msg .= 'の時間の指定可能範囲は&nbsp;';
					if ($startHour != -1) $msg .= $startHour . '時' . $startMinute . '分～' . $endHour . '時' . $endMinute . '分';
					if ($startHour2 != -1) $msg .= '<br>または&nbsp;' . $startHour2 . '時' . $startMinute2 . '分～' . $endHour2 . '時' . $endMinute2 . '分';
					$msg .= 'です';
					$this->setUserErrorMsg($msg);
				}
			}
			
			// 同じ日時の予約可能最大数
			if ($this->getMsgCount() == 0){
				$reserveCount = $this->db->getReserveStatusCountByDateTime($this->defaultResouceId, 0, $reserveDateTime, 1/*予約状態*/);
				if ($reserveCount >= $this->maxCountPerUnit) $this->setUserErrorMsg('この時間は予約がいっぱいです');
			}
			// 同ユーザがすでに登録済みかどうかチェック
			if ($this->getMsgCount() == 0){
				$reserveCount = $this->db->getReserveStatusCountByDateTime($this->defaultResouceId, $userId, $reserveDateTime, 1/*予約状態*/);
				if ($reserveCount >= 1) $this->setUserErrorMsg('この時間にすでに予約があります');
			}
			// 1ユーザの最大登録可能数
			//if ($this->getMsgCount() == 0){
			//	$reserveCount = $this->db->getReserveStatusCountByDateTime($this->defaultResouceId, $userId, $reserveDateTime, 1/*予約状態*/);
			//	if ($reserveCount >= $this->maxUserReserveCount) $this->setUserErrorMsg('予約可能最大数を超えています');
			//}
				
			// エラーなしの場合
			if ($this->getMsgCount() == 0){
				$date = $reserveDate . ' ' . $this->hour . ':' . $this->minute . ':0';
				$note = '';		// 備考
				$ret = $this->db->addReserveStatus($this->defaultResouceId, $userId, $date, 1/*予約*/, $note, $newSerial);
				if ($ret){
					$this->setGuidanceMsg('予約が完了しました');
					
					// 入力値初期化
					$reserveDate = '';
					$this->hour = -1;
					$this->minute = -1;
				} else {
					$this->setAppErrorMsg('予約に失敗しました');
				}
			}
		} else if ($act == 'cancel'){			// 予約キャンセルのとき
			$note = '';
			$ret = $this->db->updateReserveStatus($this->serialNo, $this->defaultResouceId, $userId, 2/*キャンセル*/, $note);
			if ($ret){
				$this->setGuidanceMsg('予約をキャンセルしました');
			} else {
				$this->setAppErrorMsg('予約キャンセルに失敗しました');
			}
		} else if ($act == 'logout'){			// ログアウトのとき
			$gAccessManager->userLogout();
			
			// 画面を全体を再表示する
			$gPageManager->redirect($gEnvManager->createCurrentPageUrl());
			return;
		} else {
		}
		// 予約状況を取得
		$canCancelReserve = false;		// 予約キャンセルできるかどうか
		$canNewReserve = false;				// 新規予約できるかどうか
		$reservedDate = '予約はありません';
		$ret = $this->db->getReserveStatus($this->defaultResouceId, $userId, $today, 1/*予約中*/, $rows);
		if ($ret){	// データが取得できたとき
			// シリアル番号
			$this->serialNo = $rows[0]['rs_serial'];
			
			// 最近の予約を取得
			$this->timestampToYearMonthDay($rows[0]['rs_start_dt'], $reserveYear, $reserveMonth, $reserveDay);
			$this->timestampToHourMinuteSecond($rows[0]['rs_start_dt'], $reserveHour, $reserveMinute, $reserveSecond);
			$reservedDate = $reserveYear . '年 ' . $reserveMonth . '月 ' . $reserveDay . '日 ' . $reserveHour. '時 ' . $reserveMinute . '分';
			
			// 予約日を保存
			$this->reserveDateArray[] = $reserveYear . '/' . $reserveMonth . '/' . $reserveDay;
			
			// 明日以降の予約はキャンセル可能
			if (strtotime(date("Y/m/d")) < strtotime($reserveYear . '/' . $reserveMonth . '/' . $reserveDay)) $canCancelReserve = true;
		} else {
			// 予約がない場合は新規予約可能
			$canNewReserve = true;				// 新規予約できるかどうか
			$showReservedDate = false;		// 予約日のあるカレンダーを表示するかどうか
		}
		
		// カレンダーの表示を修正
		if ($showReservedDate){		// 予約日のあるカレンダーを表示するかどうか
			if (count($this->reserveDateArray) > 0){
				$year = $reserveYear;
				$month = $reserveMonth;
			}
		}

		// カレンダーを作成
		$calendarData = $this->createCalendar($year, $month);
		$this->tmpl->addVar("_widget", "calendar", $calendarData);
		
		// ログインユーザの表示
		$userName = $gEnvManager->getCurrentUserName();
		$this->tmpl->addVar("_widget", "login_status", $userName . ' 様');
		
		// 新規登録用
		$this->createTimeMenu();		// 時間メニュー

		// ボタンの設定
		// 新規予約できないとき
		if (!$canNewReserve) $this->tmpl->addVar("_widget", "new_button_disabled", 'disabled');		// ボタン使用不可
		// 予約キャンセル可能なとき
		if ($canCancelReserve) $this->tmpl->setAttribute('cancel_button', 'visibility', 'visible');
		
		// データを埋め込む
		$todayDate = intval(date("Y")) . '年 ' . intval(date("m")) . '月 ' . intval(date("d")) . '日';
		$this->tmpl->addVar("_widget", "reserved_date", $reservedDate);			// 予約月日登録済み
		$this->tmpl->addVar("_widget", "reserve_date", $reserveDate);			// 予約月日登録用
		$this->tmpl->addVar("_widget", "today_date", $todayDate);			// 本日
		$this->tmpl->addVar("_widget", "serial", $this->serialNo);
		
		// ディレクトリを設定
		$this->tmpl->addVar("_widget", "script_url", $gEnvManager->getScriptsUrl());
	}
	/**
	 * カレンダーを作成
	 *
	 * @param int         $year			年
	 * @param int         $month		月
	 * @param string					生成したカレンダー
	 */
	function createCalendar($year, $month)
	{	
		global $gPageManager;
		global $gEnvManager;
		
		// 今日を取得
		$nowYear = date("Y");
		$nowMonth = date("m");
		$nowDay = date("d");
				
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

		// 前月、次月リンク作成
		$prevUrl = $gEnvManager->createCurrentPageUrl() . '&task=reserve&year=' . $prevYear . '&month=' . $prevMonth;
		$nextUrl = $gEnvManager->createCurrentPageUrl() . '&task=reserve&year=' . $nextYear . '&month=' . $nextMonth;

		$calendarData = '';
		$calendarData .= '<table class="reserve_calendar">' . M3_NL;
		$calendarData .= '<div align="center">' . M3_NL;
		$calendarData .= '<a href="' . $prevUrl . '">' . $prevMonth. '</a>' . M3_NL;
		$calendarData .= ' | ' . $year . '/' . $month . ' | ' . M3_NL;
		$calendarData .= '<a href="' . $nextUrl . '">' . $nextMonth . '</a>' . M3_NL;
		$calendarData .= '</div>' . M3_NL;
		$calendarData .= '<tr>' . M3_NL;
		$calendarData .= '<th class="sun">日</th>' . M3_NL;
		$calendarData .= '<th class="*">月</th>' . M3_NL;
		$calendarData .= '<th>火</th>' . M3_NL;
		$calendarData .= '<th>水</th>' . M3_NL;
		$calendarData .= '<th>木</th>' . M3_NL;
		$calendarData .= '<th>金</th>' . M3_NL;
		$calendarData .= '<th class="sat">土</th>' . M3_NL;
		$calendarData .= '</tr>' . M3_NL;
		
		$week = 0;		// 曜日
		while ($Day = $calendar->fetch()) {
		    if ($Day->isFirst()) {
		        $calendarData .= '<tr>' . M3_NL;
				$week = 0;		// 曜日を初期化
		    }

		    if ($Day->isEmpty()) {
		        $calendarData .= '<td>&nbsp;</td>' . M3_NL;
		    } else {
				$date = intval($year) . '/' . intval($month) . '/' . $Day->thisDay();
				if (in_array($date, $this->reserveDateArray)){			// 予約ありのとき
					$weekStr = ' class="select"';
		        	$calendarData .= '<td' . $weekStr . '>' . $Day->thisDay() . '</td>' . M3_NL;
				} else if ($nowYear == $year && $nowMonth == $month && $nowDay == $Day->thisDay()){
					$weekStr = ' class="today"';
		        	$calendarData .= '<td' . $weekStr . '>' . $Day->thisDay() . '</td>' . M3_NL;
				} else {
					$weekStr = '';
					if ($week == 0){		// 日曜日のとき
						$weekStr = ' class="sun"';
					} else if ($week == 6){		// 土曜日のとき
						$weekStr = ' class="sat"';
					}
		        	$calendarData .= '<td' . $weekStr . '>' . $Day->thisDay() . '</td>' . M3_NL;
				}
		    }
			$week++;		// 曜日を更新
			
		    if ($Day->isLast()) {
		        $calendarData .= '</tr>' . M3_NL;
		    }
		}
		$calendarData .= '</table></div>' . M3_NL;
		return $calendarData;
	}
	/**
	 * 時間メニューを作成
	 *
	 * @return なし
	 */
	function createTimeMenu()
	{
		// 1時間あたりの分割数
		$unitCount = 60 / $this->unitIntervalMinute;
		
		// 時メニュー作成
		for ($j = $this->viewMinHour; $j < $this->viewMaxHour; $j++){
			$selected = '';
			if ($j == $this->hour) $selected = 'selected';
			$row = array(
				'value'    => $j,			// 値
				'name'     => $j,			// 表示名
				'selected' => $selected		// 選択中かどうか
			);
			$this->tmpl->addVars('time_hour', $row);
			$this->tmpl->parseTemplate('time_hour', 'a');
		}
		// 分メニュー作成
		for ($j = 0; $j < $unitCount; $j++){
			$minute = $this->unitIntervalMinute * $j;
			$selected = '';
			if ($minute == $this->minute) $selected = 'selected';
			$row = array(
				'value'    => $minute,			// 値
				'name'     => $minute,			// 表示名
				'selected' => $selected		// 選択中かどうか
			);
			$this->tmpl->addVars('time_minute', $row);
			$this->tmpl->parseTemplate('time_minute', 'a');
		}
	}
	/**
	 * 予約可能範囲を取得
	 *
	 * @return なし
	 */
	function getAvailableTime()
	{
		// 設定値を取得
		$ret = $this->db->getCalendarByWeek(self::DEFAULT_CONFIG_ID, $rows);
		if ($ret){
			// データ初期化
			$newAvailableTime = array();
			for ($i = 0; $i < 7; $i++){
				$newAvailableTime[] = array(array(-1, -1, -1, -1), array(-1, -1, -1, -1));
			}
			// 最小、最大表示時間
			$this->viewMinHour = 24;			// 最小時間
			$viewMaxHourMinute = 0;			// 最大時間
			for ($i = 0; $i < count($rows); $i++){
				// 時間を解析
				$startHour		= intval($rows[$i]['ra_start_time'] / 100);
				$startMinute	= $rows[$i]['ra_start_time'] - $startHour * 100;
				$endHour		= intval($rows[$i]['ra_end_time'] / 100);
				$endMinute		= $rows[$i]['ra_end_time'] - $endHour * 100;

				// 最小、最大表示時間更新
				if ($this->viewMinHour > $startHour) $this->viewMinHour = $startHour;
				if ($viewMaxHourMinute < $endHour * 100 + $endMinute) $viewMaxHourMinute = $endHour * 100 + $endMinute;
				
				if ($rows[$i]['ra_specify_type'] == 1){		// 曜日指定のとき
					$attr = $rows[$i]['ra_day_attribute'] -1;		// 曜日取得
					if ($newAvailableTime[$attr][0][0] == -1){	// 前半データ
						$newAvailableTime[$attr][0][0] = $startHour;
						$newAvailableTime[$attr][0][1] = $startMinute;
						$newAvailableTime[$attr][0][2] = $endHour;
						$newAvailableTime[$attr][0][3] = $endMinute;
					} else {		// 後半データ
						$newAvailableTime[$attr][1][0] = $startHour;
						$newAvailableTime[$attr][1][1] = $startMinute;
						$newAvailableTime[$attr][1][2] = $endHour;
						$newAvailableTime[$attr][1][3] = $endMinute;
					}
				}
			}
			// 最小、最大表示時間更新
			if ($viewMaxHourMinute > (int)($viewMaxHourMinute / 100) * 100){
				$this->viewMaxHour = (int)($viewMaxHourMinute / 100) + 1;
			} else {
				$this->viewMaxHour = (int)($viewMaxHourMinute / 100);
			}
			if ($this->viewMaxHour > 24) $this->viewMaxHour = 24;
			
			// 取得値を更新
			$this->availableTime = $newAvailableTime;
		}
	}
}
?>
