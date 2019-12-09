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
 * @version    SVN: $Id: admin_reserve_mainReserveWidgetContainer.php 2363 2009-09-26 14:45:44Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() . '/admin_reserve_mainBaseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() .	'/reserve_mainDb.php');

class admin_reserve_mainReserveWidgetContainer extends admin_reserve_mainBaseWidgetContainer
{
	private $db;	// DB接続オブジェクト
	private $mainDb;	// DB接続オブジェクト
	private $sysDb;		// システムDBオブジェクト
	private $serialNo;			// シリアル番号
	private $firstNo;			// 項目番号
	private $hour;		// 予約時間
	private $minute;		// 予約時間(分)
	private $date;		// 選択中の年月日
	private $viewStatus;	// 表示ステータス
	private $selectUser;		// 新規追加ユーザ
	private $serialArray = array();		// 表示されている項目シリアル番号
	private $viewDayStart;		// 先頭に表示する日付
	private $viewDayRange;		// 表示範囲
	private $viewMinHour;			// 最小時間
	private $viewMaxHour;			// 最大時間
	private $availableTime = array();	// 選択可能時間
	private $unitIntervalMinute;		// 単位時間(分)
	private $defaultResouceId;				// デフォルトのリソースID
	const DEFAULT_RES_TYPE = 0;	// デフォルトの設定タイプ(常設)
	const DEFAULT_CONFIG_ID = 0;		// デフォルト定義ID
	const DEFAULT_VIEW_STATUS = 1;		// デフォルトの表示ステータス
	const VIEW_DAY_RANGE = 'view_day_range';	// 表示範囲
	const VIEW_DAY_START = 'view_day_start';	// 先頭に表示する日付
	const DEFAULT_LIST_COUNT = 20;			// 最大リスト表示数
	const UNIT_ACTIVE_STYLE = 'style="background-color:white;border: 1px solid;white-space: nowrap;"';	// カレンダー表の選択可能領域のカラー
	const UNIT_INACTIVE_STYLE = 'style="background-color:lightgray;border: 1px solid;white-space: nowrap;"';	// カレンダー表の選択可能領域のカラー
	const UNIT_SIDE_STYLE = 'style="border:1px solid;white-space: nowrap;"';
	const ACCESS_TYPE = 'rv';			// ログインユーザのアクセス可能な機能タイプ
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
		$task = $request->trimValueOf('task');
		if ($task == 'reserve_detail'){		// 詳細画面
			return 'admin_reserve_detail.tmpl.html';
		} else {
			return 'admin_reserve.tmpl.html';
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
		if ($task == 'reserve_detail'){	// 詳細画面
			return $this->createDetail($request);
		} else {			// 一覧画面
			return $this->createList($request);
		}
	}
	/**
	 * 一覧画面作成
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param								なし
	 */
	function createList($request)
	{
		global $gEnvManager;
		
		$userId = $gEnvManager->getCurrentUserId();
		
		$act = $request->trimValueOf('act');
				
		// デフォルト値を取得
		$maxListCount = self::DEFAULT_LIST_COUNT;
		$serializedParam = $this->sysDb->getWidgetParam($gEnvManager->getCurrentWidgetId());
		if (!empty($serializedParam)){
			$dispInfo = unserialize($serializedParam);
///			$maxListCount = $dispInfo->maxMemberListCountByAdmin;		// 会員リスト最大表示数
		}
		
		// 予約可能範囲を取得
		$this->getAvailableTime();			

		$this->viewDayStart = $request->trimValueOf('day_start');		// 先頭に表示する日付
		$this->viewDayRange = $request->trimValueOf('day_range');		// 表示範囲
	
		// 入力値を取得
		$reserveDate			= $request->trimValueOf('reserve_date');
		if (!empty($reserveDate)) $reserveDate = $this->convertToProperDate($reserveDate);			// 予約日時
		$this->hour = $request->trimValueOf('time_hour');
		if ($this->hour == '') $this->hour = -1;
		$this->minute = $request->trimValueOf('time_minute');
		if ($this->minute == '') $this->minute = -1;
		$this->selectUser				= $request->trimValueOf('user');
		
		if ($act == 'new'){		// 予約追加の場合
			// 入力チェック
			$this->checkDate($reserveDate, '予約日付');
			if ($this->hour == -1) $this->setUserErrorMsg('予約時間(時)が選択されていません');
			if ($this->minute == -1) $this->setUserErrorMsg('予約時間(分)が選択されていません');
			if (empty($this->selectUser)) $this->setUserErrorMsg('ユーザが選択されていません');
			
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
				$reserveCount = $this->db->getReserveStatusCountByDateTime($this->defaultResouceId, $this->selectUser, $reserveDateTime, 1/*予約状態*/);
				if ($reserveCount >= 1) $this->setUserErrorMsg('すでにこのユーザの予約があります');
			}
				
			// エラーなしの場合
			if ($this->getMsgCount() == 0){
				$date = $reserveDate . ' ' . $this->hour . ':' . $this->minute . ':0';
				$note = '';		// 備考
				$ret = $this->db->addReserveStatus($this->defaultResouceId, $this->selectUser, $date, 1/*予約*/, $note, $newSerial);
				if ($ret){
					$this->setGuidanceMsg('予約が完了しました');
					
					// 入力値初期化
					$reserveDate = '';
					$this->hour = -1;
					$this->minute = -1;
					$this->selectUser = 0;
				} else {
					$this->setAppErrorMsg('予約に失敗しました');
				}
			}
		} else if ($act == 'selviewmenu'){		// 表示状態の変更のとき
			$this->db->updateReserveConfig(self::DEFAULT_CONFIG_ID, self::VIEW_DAY_START, $this->viewDayStart);// 先頭に表示する日付
			$this->db->updateReserveConfig(self::DEFAULT_CONFIG_ID, self::VIEW_DAY_RANGE, $this->viewDayRange);// 表示範囲
		} else {
			$this->viewDayStart = $this->db->getReserveConfig(self::DEFAULT_CONFIG_ID, self::VIEW_DAY_START);		// 先頭に表示する日付
			$this->viewDayRange = $this->db->getReserveConfig(self::DEFAULT_CONFIG_ID, self::VIEW_DAY_RANGE);		// 表示範囲
		}
		// リソース一覧を表示
		$this->db->getAllResource(self::DEFAULT_RES_TYPE, self::DEFAULT_CONFIG_ID, array($this, 'resourceListLoop'));
		
		// 先頭日付メニュー作成
		$this->createDayStartMenu();
		
		// 表示日数メニュー作成
		$this->createDayRangeMenu();
		
		// 予約表を作成
		$this->createDayList();
		
		// 予約登録部分を作成
		// 時間メニューを作成
		$this->createTimeMenu();
		
		// ユーザリストを取得
		$this->db->getAllUserListForMenu(self::ACCESS_TYPE, array($this, 'userListLoop'));
		
		$this->tmpl->addVar("_widget", "reserve_date", $reserveDate);			// 予約月日登録用
		$this->tmpl->addVar("_widget", "serial_list", implode(',', $this->serialArray));// 表示項目のシリアル番号を設定
		$this->tmpl->addVar("_widget", "script_url", $gEnvManager->getScriptsUrl());
	}
	/**
	 * 詳細画面作成
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param								なし
	 */
	function createDetail($request)
	{
		global $gEnvManager;
		global $gSystemManager;
		global $gInstanceManager;
		global $gPageManager;

		// ユーザ情報、表示言語
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId		= $gEnvManager->getCurrentUserId();
		$langId	= $gEnvManager->getCurrentLanguage();		// 表示言語を取得
				
		$act = $request->trimValueOf('act');
		$this->serialNo = $request->trimValueOf('serial');		// 選択項目のシリアル番号

		// 予約可能範囲を取得
		$this->getAvailableTime();
		
		$this->viewDayStart = $this->db->getReserveConfig(self::DEFAULT_CONFIG_ID, self::VIEW_DAY_START);		// 先頭に表示する日付
		$this->viewDayRange = $this->db->getReserveConfig(self::DEFAULT_CONFIG_ID, self::VIEW_DAY_RANGE);		// 表示範囲
	
		// 入力値を取得
		$this->date	= $request->trimValueOf('item_date');		// 選択日時
		$this->viewStatus = $request->trimValueOf('view_status');		// 表示ステータス
		if ($this->viewStatus == '') $this->viewStatus = self::DEFAULT_VIEW_STATUS;
		
		$this->hour = $request->trimValueOf('time_hour');
		if ($this->hour == '') $this->hour = -1;
		$this->minute = $request->trimValueOf('time_minute');
		if ($this->minute == '') $this->minute = -1;
		$this->selectUser				= $request->trimValueOf('user');
		$note	= $request->trimValueOf('note');
		
		if ($act == 'new'){		// 項目追加の場合
			// 入力チェック
			if (empty($this->date)) $this->setUserErrorMsg('予約日付が選択されていません');
			if ($this->hour == -1) $this->setUserErrorMsg('予約時間(時)が選択されていません');
			if ($this->minute == -1) $this->setUserErrorMsg('予約時間(分)が選択されていません');
			if (empty($this->selectUser)) $this->setUserErrorMsg('ユーザが選択されていません');
			
			// 予約日時
			$reserveDateTime = $this->date . ' ' . $this->hour . ':' . $this->minute;
			$reserveHourMinute = $this->hour * 100 + $this->minute;
			
			// 予約可能かチェック
			// 本日以降
			if ($this->getMsgCount() == 0){
				if (strtotime(date("Y/m/d")) >= strtotime($this->date)) $this->setUserErrorMsg('明日以降を選択してください');
			}
			
			// 営業時間範囲
			if ($this->getMsgCount() == 0){
				// 曜日を取得
				$week = date("w", strtotime($this->date));
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
				$reserveCount = $this->db->getReserveStatusCountByDateTime($this->defaultResouceId, $this->selectUser, $reserveDateTime, 1/*予約状態*/);
				if ($reserveCount >= 1) $this->setUserErrorMsg('すでにこのユーザの予約があります');
			}
				
			// エラーなしの場合
			if ($this->getMsgCount() == 0){
				$ret = $this->db->addReserveStatus($this->defaultResouceId, $this->selectUser, $reserveDateTime, 1/*予約*/, $note, $newSerial);
				if ($ret){
					$this->setGuidanceMsg('予約が完了しました');
					
					// 入力値初期化
					$this->hour = -1;
					$this->minute = -1;
					$this->selectUser = 0;
				} else {
					$this->setAppErrorMsg('予約に失敗しました');
				}
			}
		} else if ($act == 'update'){		// 項目更新の場合
			// 登録済みデータを取得
			$ret = $this->db->getReserveStatusBySerial($this->serialNo, $row);

			// データを更新
			$status = $request->trimValueOf('item' . $this->serialNo . '_status');		// 予約ステータス
			$note = $request->trimValueOf('item' . $this->serialNo . '_note');		// 予約備考
			if ($ret) $ret = $this->db->updateReserveStatus($this->serialNo, $this->defaultResouceId, $row['rs_user_id'], $status, $note);
			if ($ret){
				$this->setGuidanceMsg('予約を更新しました');
			} else {
				$this->setAppErrorMsg('予約更新に失敗しました');
			}
		} else if ($act == 'selviewmenu'){		// 表示日付変更のとき
		} else {	// 初期表示
			$year = $request->trimValueOf('year');
			$month = $request->trimValueOf('month');
			$day = $request->trimValueOf('day');
			$this->date = $year . '/' . $month . '/' . $day;		// 選択中の年月日
		}

		// 日付メニューを作成
		$this->createDayMenu();
		
		// 表示ステータスメニューを作成
		$this->createViewStatusMenu();
		
		// 予約状況の表を作成
		$this->createDetailDayList();
		
		// 予約登録部分を作成
		// 時間メニューを作成
		$this->createTimeMenu();
		
		// ユーザリストを取得
		$this->db->getAllUserListForMenu(self::ACCESS_TYPE, array($this, 'userListLoop'));
		
		$this->tmpl->addVar("_widget", "current_date", $this->date);			// 予約登録日
		
		// 値を埋め込む
		$this->tmpl->addVar("_widget", "serial", $this->serialNo);
		$this->tmpl->addVar("_widget", "script_url", $gEnvManager->getScriptsUrl());	// スクリプトパスの設定
	}
	/**
	 * 先頭の日付のメニュー作成
	 *
	 * @return なし
	 */
	function createDayStartMenu()
	{
		$days = array(array(0, '今日'), array(1, '今週'), array(2, '先週'), array(3, '先々週'), array(11, '前日'), array(12, '3日前'));
		for ($i = 0; $i < count($days); $i++){
			$value = $days[$i][0];
			$name = $days[$i][1];
			$selected = '';
			if ($value == $this->viewDayStart) $selected = 'selected';
			$row = array(
				'value'    => $value,			// 値
				'name'     => $name,			// 表示名
				'selected' => $selected		// 選択中かどうか
			);
			$this->tmpl->addVars('day_start', $row);
			$this->tmpl->parseTemplate('day_start', 'a');
		}
	}
	/**
	 * 表示日数のメニュー作成
	 *
	 * @return なし
	 */
	function createDayRangeMenu()
	{
		$days = array(10, 15, 30, 60);		// 表示日数
		for ($i = 0; $i < count($days); $i++){
			$value = $days[$i];
			$name = $value;
			$selected = '';
			if ($value == $this->viewDayRange) $selected = 'selected';
			$row = array(
				'value'    => $value,			// 値
				'name'     => $name,			// 表示名
				'selected' => $selected		// 選択中かどうか
			);
			$this->tmpl->addVars('day_range', $row);
			$this->tmpl->parseTemplate('day_range', 'a');
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
	/**
	 * 予約表を作成
	 *
	 * @return なし
	 */
	function createDayList()
	{
		// 1時間あたりの分割数
		$unitCount = 60 / $this->unitIntervalMinute;
		
		// 本日を取得
		list($yyyy, $mm, $dd) = preg_split('/[\/\.\-]/', date("Y/m/d"));
		$todayYear = intval($yyyy);
		$todayMonth = intval($mm);
		$todayDay = intval($dd);

		// 先頭日を求める
		$date = '';		// 先頭日
		switch ($this->viewDayStart){
			case 0:			// 本日から表示
				$date = date("Y/m/d");
				break;
			case 1:			// 今週から表示
				// 日曜日までの日数を求める
				$startDay = $dayOfWeek * (-1);
				$date = date("Y/m/d", strtotime("$startDay day"));
				break;
			case 2:			// 先週から表示
				// 日曜日までの日数を求める
				$startDay = $dayOfWeek * (-1) - 7;
				$date = date("Y/m/d", strtotime("$startDay day"));
				break;
			case 3:			// 先々週から表示
				// 日曜日までの日数を求める
				$startDay = $dayOfWeek * (-1) - 14;
				$date = date("Y/m/d", strtotime("$startDay day"));
				break;
			case 11:		// 前日から表示
				$date = date("Y/m/d", strtotime("-1 day"));
				break;
			case 12:		// 3日前から表示
				$date = date("Y/m/d", strtotime("-3 day"));
				break;
		}
		// 年月日を分割
		list($yyyy, $mm, $dd) = preg_split('/[\/\.\-]/', $date);
		$year = intval($yyyy);
		$month = intval($mm);
		$day = intval($dd);
			
		// 先頭の曜日
		$dayOfWeek = date("w", mktime(0, 0, 0, $month, $day, $year));
		
		// 予約状況を取得
		$endDate = date("Y/m/d", strtotime("$date $this->viewDayRange day"));			// 表示終了日の翌日
		$this->db->getReserveStatusByDate($this->defaultResouceId, $date, $endDate, 1/*予約*/, $reserveRows);
		$reserveRowsCount = count($reserveRows);

		if ($reserveRowsCount > 0){
			$reserveReadPos = 0;		// 予約状況読み込み位置
		} else {
			$reserveReadPos = -1;		// 予約状況読み込み位置
		}
		$head = '';		// ヘッダ部データ
		$lineData = array();
		for ($i = 0; $i < $this->viewDayRange; $i++){
			// ***** ヘッダ部作成 *****
			// 日付作成
			if ($i == 0 || $day == 1){
				$viewDate = $month . '/' . $day;
			} else {
				$viewDate = $day;
			}
			// 曜日
			switch ($dayOfWeek){
				case 0: $weekName = '日'; break;
				case 1: $weekName = '月'; break;
				case 2: $weekName = '火'; break;
				case 3: $weekName = '水'; break;
				case 4: $weekName = '木'; break;
				case 5: $weekName = '金'; break;
				case 6: $weekName = '土'; break;
			}
			$viewDate = $viewDate . '(' . $weekName . ')';
			
			// 日付のカラー設定
			if ($year == $todayYear && $month == $todayMonth && $day == $todayDay){	// 本日のとき
				$viewDate = '<b><font color="green">' . $viewDate . '</font></b>';
			} else if ($dayOfWeek == 0){		// 日曜日のとき
				$viewDate = '<b><font color="red">' . $viewDate . '</font></b>';
			} else {
				$viewDate = '<font color="white">' . $viewDate . '</font>';
			}
			$viewDate = '&nbsp;&nbsp;<a href="#" onClick="editItemByDate(' . $year . ', ' . $month . ', ' . $day . ');" style="text-decoration: underline;">' . $viewDate . '</a>&nbsp;&nbsp;';
			$head .= '<th style="border-left: 1px solid;border-right: 1px solid;white-space: nowrap;" align="center">' . $viewDate . '</th>';
			
			// 有効範囲の取得
			$startHourMinute = -1;
			$endHourMinute = -1;
			$startHourMinute2 = -1;
			$endHourMinute2 = -1;
			// 同じ曜日の値で上書き
			$hourMinute = $this->availableTime[$dayOfWeek];			// 設定時分を取得
			// 前半
			$startHour = $hourMinute[0][0];
			$startMinute = $hourMinute[0][1];
			$endHour = $hourMinute[0][2];
			$endMinute = $hourMinute[0][3];
			if ($startHour != -1){
				$startHourMinute = $startHour * 100 + $startMinute;
				$endHourMinute = $endHour * 100 + $endMinute;
			}
			// 後半
			$startHour = $hourMinute[1][0];
			$startMinute = $hourMinute[1][1];
			$endHour = $hourMinute[1][2];
			$endMinute = $hourMinute[1][3];
			if ($startHour != -1){
				$startHourMinute2 = $startHour * 100 + $startMinute;
				$endHourMinute2 = $endHour * 100 + $endMinute;
			}			
			
			$lineIndex = 0;
			for ($j = $this->viewMinHour ;	$j < $this->viewMaxHour; $j++){
				// 時間ヘッダ(左)
				if ($i == 0) $lineData[$lineIndex] = '<td rowspan="' . $unitCount . '" align="center" ' . self::UNIT_SIDE_STYLE . ' >' . $j . ':00' . '</td>';
				
				for ($k = 0; $k < $unitCount; $k++){
					// 選択単位の先頭の時間
					$unitHourMinute = $j * 100 + $this->unitIntervalMinute * $k;

					// カラーの設定
					$unitStyle = self::UNIT_INACTIVE_STYLE;		// 無効領域のカラー
					if (($startHourMinute != -1 && ($startHourMinute <= $unitHourMinute && $unitHourMinute < $endHourMinute)) ||
						($startHourMinute2 != -1 && ($startHourMinute2 <= $unitHourMinute && $unitHourMinute < $endHourMinute2))){		// 有効時間のとき
						$unitStyle = self::UNIT_ACTIVE_STYLE;
					}
					
					// 予約の登録状況を取得
					$regData = '';		// 登録されているデータ
					if ($reserveReadPos != -1){		// 読み込み終了でないとき
						for ($l = $reserveReadPos; $l < $reserveRowsCount; $l++){
							// タイムスタンプを取得
							$unitTimestamp = mktime($j, $this->unitIntervalMinute * $k, 0, $month, $day, $year);
							$this->timestampToYearMonthDay($reserveRows[$l]['rs_start_dt'], $resYear, $resMonth, $resDay);
							$this->timestampToHourMinuteSecond($reserveRows[$l]['rs_start_dt'], $resHour, $resMinute, $resSecond);
							$resTimestamp = mktime($resHour, $resMinute, $resSecond, $resMonth, $resDay, $resYear);
							
							if ($resTimestamp == $unitTimestamp){	// 該当する日時のとき
								$name = $reserveRows[$l]['li_family_name'] . $reserveRows[$l]['li_first_name'];
								if (empty($name)) $name = '名称未設定';
								$regData .= '<div>' . $name . '</div>';
							} else if ($resTimestamp > $unitTimestamp){
								$reserveReadPos = $l;		// 読み込み位置を更新
								break;
							}
						}
						if ($l == $reserveRowsCount) $reserveReadPos = -1;		// 読み込み終了
					}
					if (empty($regData)) $regData = '&nbsp;';
					$lineData[$lineIndex] .= '<td ' . $unitStyle . '>' . $regData . '</td>';
					
					// 時間ヘッダ(右)
					if ($i == $this->viewDayRange -1 && $k == 0) $lineData[$lineIndex] .= '<td rowspan="' . $unitCount . '" align="center" ' . self::UNIT_SIDE_STYLE . ' >' . $j . ':00' . '</td>';
					$lineIndex++;
				}
			}
			// 最後の日付を保存
			if ($i == $this->viewDayRange -1) $lastData = $year . '/' . $month . '/' . $day;
			
			// 翌日を求める
			list($yyyy, $mm, $dd) = preg_split('/[\/\.\-]/', date("Y/m/d", mktime(0, 0, 0, $month, $day + 1, $year)));
			
			$year = intval($yyyy);
			$month = intval($mm);
			$day = intval($dd);
			
			// 曜日を更新
			$dayOfWeek++;
			if ($dayOfWeek == 7) $dayOfWeek = 0;
		}
		
		for ($i = 0; $i < $lineIndex; $i++){
			$line = $lineData[$i];
			
			// データを埋め込む
			$row = array(
				'index'    => $i,			// インデックス番号
				'sel_date'    => $year . '/' . $month . '/' . $day,			// 選択用日付
				'date'    => $viewDate,			// 日付
				'line'     => $line,			// 行データ
				'selected' => $selected		// 選択中かどうか
			);
			$this->tmpl->addVars('itemlist', $row);
			$this->tmpl->parseTemplate('itemlist', 'a');
		}
		
		$this->tmpl->addVar("_widget", "head", $head);// ヘッダ部タイトル
		$this->tmpl->addVar("_widget", "view_range", '(' . $date . '～' . $lastData . ')');
	}
	/**
	 * 予約表(詳細)を作成
	 *
	 * @return なし
	 */
	function createDetailDayList()
	{
		// 1時間あたりの分割数
		$unitCount = 60 / $this->unitIntervalMinute;
		
		// 年月日を分割
		list($yyyy, $mm, $dd) = preg_split('/[\/\.\-]/', $this->date);
		$year = intval($yyyy);
		$month = intval($mm);
		$day = intval($dd);
		
		// 曜日
		$dayOfWeek = date("w", mktime(0, 0, 0, $month, $day, $year));
		
		// 予約状況を取得
		$endDate = date("Y/m/d", strtotime("$this->date 1 day"));			// 表示終了日の翌日
		$this->db->getReserveStatusByDate($this->defaultResouceId, $this->date, $endDate, $this->viewStatus/*表示の設定によって制御*/, $reserveRows);
		$reserveRowsCount = count($reserveRows);
		
		if ($reserveRowsCount > 0){
			$reserveReadPos = 0;		// 予約状況読み込み位置
		} else {
			$reserveReadPos = -1;		// 予約状況読み込み位置
		}
		// 有効範囲の取得
		$startHourMinute = -1;
		$endHourMinute = -1;
		$startHourMinute2 = -1;
		$endHourMinute2 = -1;
		// 同じ曜日の値で上書き
		$hourMinute = $this->availableTime[$dayOfWeek];			// 設定時分を取得
		// 前半
		$startHour = $hourMinute[0][0];
		$startMinute = $hourMinute[0][1];
		$endHour = $hourMinute[0][2];
		$endMinute = $hourMinute[0][3];
		if ($startHour != -1){
			$startHourMinute = $startHour * 100 + $startMinute;
			$endHourMinute = $endHour * 100 + $endMinute;
		}
		// 後半
		$startHour = $hourMinute[1][0];
		$startMinute = $hourMinute[1][1];
		$endHour = $hourMinute[1][2];
		$endMinute = $hourMinute[1][3];
		if ($startHour != -1){
			$startHourMinute2 = $startHour * 100 + $startMinute;
			$endHourMinute2 = $endHour * 100 + $endMinute;
		}			
		
		$lineIndex = 0;
		for ($j = $this->viewMinHour ;	$j < $this->viewMaxHour; $j++){
			for ($k = 0; $k < $unitCount; $k++){
				// 選択単位の先頭の時間
				$unitHourMinute = $j * 100 + $this->unitIntervalMinute * $k;

				// カラーの設定
				$unitStyle = self::UNIT_INACTIVE_STYLE;		// 無効領域のカラー
				if (($startHourMinute != -1 && ($startHourMinute <= $unitHourMinute && $unitHourMinute < $endHourMinute)) ||
					($startHourMinute2 != -1 && ($startHourMinute2 <= $unitHourMinute && $unitHourMinute < $endHourMinute2))){		// 有効時間のとき
					$unitStyle = self::UNIT_ACTIVE_STYLE;
				}
				
				// 予約の登録状況を取得
				$regData = array();		// 登録されているデータ
				if ($reserveReadPos != -1){		// 読み込み終了でないとき
					for ($l = $reserveReadPos; $l < $reserveRowsCount; $l++){
						// タイムスタンプを取得
						$unitTimestamp = mktime($j, $this->unitIntervalMinute * $k, 0, $month, $day, $year);
						$this->timestampToYearMonthDay($reserveRows[$l]['rs_start_dt'], $resYear, $resMonth, $resDay);
						$this->timestampToHourMinuteSecond($reserveRows[$l]['rs_start_dt'], $resHour, $resMinute, $resSecond);
						$resTimestamp = mktime($resHour, $resMinute, $resSecond, $resMonth, $resDay, $resYear);
						
						if ($resTimestamp == $unitTimestamp){	// 該当する日時のとき
							$name = $reserveRows[$l]['li_family_name'] . $reserveRows[$l]['li_first_name'];
							if (empty($name)) $name = '名称未設定';

							$regRec = array('name' => $name, 'note' => $reserveRows[$l]['rs_note'], 'status' => $reserveRows[$l]['rs_status'], 'serial' => $reserveRows[$l]['rs_serial']);
							$regData[] = $regRec;
						} else if ($resTimestamp > $unitTimestamp){
							$reserveReadPos = $l;		// 読み込み位置を更新
							break;
						}
					}
					if ($l == $reserveRowsCount) $reserveReadPos = -1;		// 読み込み終了
				}
				
				$regCount = count($regData);
				if ($regCount > 0){			// 予約があるとき
					for ($i = 0; $i < $regCount; $i++){
						// 時間カラム作成
						$timeCol = '';
						if ($i == 0) $timeCol = '<td rowspan="' . $regCount . '" align="center" style="border: 1px solid;">' . $j . ':' . sprintf("%02d", $this->unitIntervalMinute * $k) . '</td>';

						// 予約ステータスを作成
						$res1 = '';
						$res2 = '';
						if ($regData[$i]['status'] == 1){		// 予約状態
							$res1 = 'selected';
						} else if ($regData[$i]['status'] == 2){	// キャンセル状態
							$res2 = 'selected';
						}
						$statusCol = '<select name="item' . $regData[$i]['serial'] . '_status">' . M3_NL;
						$statusCol .= '<option value="1"' . $res1 . '>予約中</option>' . M3_NL;
						$statusCol .= '<option value="2"' . $res2 . '>キャンセル</option>' . M3_NL;
						$statusCol .= '</select>' . M3_NL;

						// データを埋め込む
						$row = array(
							'visible_style'	=> '',				// 表示状態
							'line_color'	=> $unitStyle,		// 行のカラー
							'time_col'    => $timeCol,			// 時間表示
							'status_col'    => $statusCol,			// 予約スタータス
							'name'    => $regData[$i]['name'],					// ユーザ名
							'note'    => $regData[$i]['note'],					// 備考
							'serial' => $regData[$i]['serial']		// シリアル番号
						);
						$this->tmpl->addVars('itemlist', $row);
						$this->tmpl->parseTemplate('itemlist', 'a');
					}
				} else {		// 予約がないとき
					// 時間カラム作成
					$timeCol = '<td align="center" style="border: 1px solid;">' . $j . ':' . sprintf("%02d", $this->unitIntervalMinute * $k) . '</td>';

					// データを埋め込む
					$row = array(
						'visible_style'	=> 'style="visibility:hidden;"',				// 表示状態
						'line_color'	=> $unitStyle,		// 行のカラー
						'time_col'    => $timeCol,			// 時間表示
						'status_col'    => '',			// 予約スタータス
						'name'    => '',					// ユーザ名
						'note'    => '',					// 備考
						'serial' => 0		// シリアル番号
					);
					$this->tmpl->addVars('itemlist', $row);
					$this->tmpl->parseTemplate('itemlist', 'a');
				}
			}
		}
	}
	/**
	 * 日付メニューを作成
	 *
	 * @return なし
	 */
	function createDayMenu()
	{
		// 本日を取得
		list($yyyy, $mm, $dd) = preg_split('/[\/\.\-]/', date("Y/m/d"));
		$todayYear = intval($yyyy);
		$todayMonth = intval($mm);
		$todayDay = intval($dd);

		// 先頭日を求める
		$date = '';		// 先頭日
		switch ($this->viewDayStart){
			case 0:			// 本日から表示
				$date = date("Y/m/d");
				break;
			case 1:			// 今週から表示
				// 日曜日までの日数を求める
				$startDay = $dayOfWeek * (-1);
				$date = date("Y/m/d", strtotime("$startDay day"));
				break;
			case 2:			// 先週から表示
				// 日曜日までの日数を求める
				$startDay = $dayOfWeek * (-1) - 7;
				$date = date("Y/m/d", strtotime("$startDay day"));
				break;
			case 3:			// 先々週から表示
				// 日曜日までの日数を求める
				$startDay = $dayOfWeek * (-1) - 14;
				$date = date("Y/m/d", strtotime("$startDay day"));
				break;
			case 11:		// 前日から表示
				$date = date("Y/m/d", strtotime("-1 day"));
				break;
			case 12:		// 3日前から表示
				$date = date("Y/m/d", strtotime("-3 day"));
				break;
		}
		// 年月日を分割
		list($yyyy, $mm, $dd) = preg_split('/[\/\.\-]/', $date);
		$year = intval($yyyy);
		$month = intval($mm);
		$day = intval($dd);
			
		// 先頭の曜日
		$dayOfWeek = date("w", mktime(0, 0, 0, $month, $day, $year));
		
		for ($i = 0; $i < $this->viewDayRange; $i++){
			// 日付作成
			$viewDate = $year . '年' . $month . '月' . $day . '日';

			// 曜日
			switch ($dayOfWeek){
				case 0: $weekName = '日'; break;
				case 1: $weekName = '月'; break;
				case 2: $weekName = '火'; break;
				case 3: $weekName = '水'; break;
				case 4: $weekName = '木'; break;
				case 5: $weekName = '金'; break;
				case 6: $weekName = '土'; break;
			}
			$viewDate = $viewDate . '(' . $weekName . ')';
			
			$selected = '';
			$valueDate = $year . '/' . $month . '/' . $day;
			if ($this->date == $valueDate) $selected = 'selected';
			$row = array(
				'value'    => $valueDate,			// 値
				'name'     => $viewDate,			// 表示名
				'selected' => $selected		// 選択中かどうか
			);
			$this->tmpl->addVars('date_list', $row);
			$this->tmpl->parseTemplate('date_list', 'a');
			
			// 翌日を求める
			$date = date("Y/m/d", mktime(0, 0, 0, $month, $day + 1, $year));
			list($yyyy, $mm, $dd) = preg_split('/[\/\.\-]/', $date);
			
			$year = intval($yyyy);
			$month = intval($mm);
			$day = intval($dd);
			
			// 曜日を更新
			$dayOfWeek++;
			if ($dayOfWeek == 7) $dayOfWeek = 0;
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
	function resourceListLoop($index, $fetchedRow, $param)
	{
		global $gEnvManager;

		$row = array(
			'value'    => $fetchedRow['rr_index'],			// 値
			'name'     => $this->convertToDispString($fetchedRow['rr_name']),			// 表示名
			'selected' => $selected		// 選択中かどうか
		);
		$this->tmpl->addVars('resource', $row);
		$this->tmpl->parseTemplate('resource', 'a');
		return true;
	}
	/**
	 * 取得したデータをテンプレートに設定する
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function userListLoop($index, $fetchedRow, $param)
	{
		global $gEnvManager;

		$name = $this->convertToDispString($fetchedRow['li_family_name']) . '&nbsp;' . $this->convertToDispString($fetchedRow['li_first_name']);
		$no = $this->convertToDispString($fetchedRow['li_no']);			// ユーザ番号
		if (!empty($no)) $no = '&nbsp;-&nbsp;' . $no;
		
		$selected = '';
		if ($fetchedRow['li_id'] == $this->selectUser) $selected = 'selected';
		$row = array(
			'value'    => $this->convertToDispString($fetchedRow['li_id']),			// 値
			'name'     => $name,			// 表示名
			'no'     => $no,			// ユーザ番号
			'selected' => $selected		// 選択中かどうか
		);
		$this->tmpl->addVars('user_list', $row);
		$this->tmpl->parseTemplate('user_list', 'a');
		return true;
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
	 * 表示ステータスメニューを作成
	 *
	 * @return なし
	 */
	function createViewStatusMenu()
	{
		$viewStatus = array(array('value' => 0, 'name'	=> 'すべて'), array('value' => 1, 'name'	=> '予約中'), array('value' => 2, 'name'	=> 'キャンセル'));
		
		// 表示ステータスメニュー
		for ($i = 0; $i < count($viewStatus); $i++){
			$selected = '';
			if ($viewStatus[$i]['value'] == $this->viewStatus) $selected = 'selected';
			$row = array(
				'value'    => $viewStatus[$i]['value'],			// 値
				'name'     => $viewStatus[$i]['name'],			// 表示名
				'selected' => $selected		// 選択中かどうか
			);
			$this->tmpl->addVars('view_status', $row);
			$this->tmpl->parseTemplate('view_status', 'a');
		}
	}
}
?>
