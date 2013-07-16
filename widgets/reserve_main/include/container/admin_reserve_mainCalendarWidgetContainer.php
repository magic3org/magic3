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
 * @version    SVN: $Id: admin_reserve_mainCalendarWidgetContainer.php 491 2008-04-10 03:42:31Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() .			'/admin_reserve_mainBaseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() .	'/reserve_mainDb.php');

class admin_reserve_mainCalendarWidgetContainer extends admin_reserve_mainBaseWidgetContainer
{
	private $db;	// DB接続オブジェクト
	private $unitIntervalMinute;// 単位時間(分)
	private $availableTime = array();	// 選択可能時間
	const CONFIG_ID = 0;		// 設定ID
	const UNIT_INTERVAL_MINUTE = 'unit_interval_minute';	// 単位時間(分)
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->db = new reserve_mainDb();
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
		return 'admin_calendar.tmpl.html';
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
		
		// 時間間隔を取得
		$this->unitIntervalMinute	= $this->db->getReserveConfig(self::CONFIG_ID, self::UNIT_INTERVAL_MINUTE);
		if (empty($this->unitIntervalMinute)) $this->unitIntervalMinute = 60;
			
		$act = $request->trimValueOf('act');
		if ($act == 'update'){		// 設定更新のとき
			$this->availableTime = array();
			// 入力チェック
			for ($i = 0; $i < 7; $i++){
				$weekName = '';		// 曜日名
				switch ($i){
					case 0: $weekName = '日曜日'; break;
					case 1: $weekName = '月曜日'; break;
					case 2: $weekName = '火曜日'; break;
					case 3: $weekName = '水曜日'; break;
					case 4: $weekName = '木曜日'; break;
					case 5: $weekName = '金曜日'; break;
					case 6: $weekName = '土曜日'; break;
				}
				$itemName = 'time' . $i . '1_start_hour';
				$itemValue1 = $request->trimValueOf($itemName);
				$itemName = 'time' . $i . '1_start_minute';
				$itemValue2 = $request->trimValueOf($itemName);
				$itemName = 'time' . $i . '1_end_hour';
				$itemValue3 = $request->trimValueOf($itemName);
				$itemName = 'time' . $i . '1_end_minute';
				$itemValue4 = $request->trimValueOf($itemName);
				$itemName = 'time' . $i . '2_start_hour';
				$itemValue5 = $request->trimValueOf($itemName);
				$itemName = 'time' . $i . '2_start_minute';
				$itemValue6 = $request->trimValueOf($itemName);
				$itemName = 'time' . $i . '2_end_hour';
				$itemValue7 = $request->trimValueOf($itemName);
				$itemName = 'time' . $i . '2_end_minute';
				$itemValue8 = $request->trimValueOf($itemName);

				// 入力データを保存
				$hourMinute = array(array($itemValue1, $itemValue2, $itemValue3, $itemValue4), array($itemValue5, $itemValue6, $itemValue7, $itemValue8));
				$this->availableTime[] = $hourMinute;
				
				$startTime1 = -1;
				$endTime1 = -1;
				$startTime2 = -1;
				$endTime2 = -1;
				if ($this->getMsgCount() == 0){			// エラーのないとき
					// 前半の入力チェック
					if ($itemValue1 != -1 ||
						$itemValue2 != -1 ||
						$itemValue3 != -1 ||
						$itemValue4 != -1){			// 値が入力されているとき
						if ($itemValue1 == -1 ||
							$itemValue2 == -1 ||
							$itemValue3 == -1 ||
							$itemValue4 == -1){			// すべての値が入力されていなければエラー
							$this->setUserErrorMsg('選択されていないフィールドがあります。曜日=' . $weekName);
						} else {
							// 範囲のチェック
							$startTime1 = intval($itemValue1) * 100 + intval($itemValue2);
							$endTime1 = intval($itemValue3) * 100 + intval($itemValue4);
							if ($startTime1 >= $endTime1) $this->setUserErrorMsg('指定範囲にエラーがあります。曜日=' . $weekName);
						}
					}
					// 後半の入力チェック
					if ($itemValue5 != -1 ||
						$itemValue6 != -1 ||
						$itemValue7 != -1 ||
						$itemValue8 != -1){			// 値が入力されているとき
						if ($itemValue5 == -1 ||
							$itemValue6 == -1 ||
							$itemValue7 == -1 ||
							$itemValue8 == -1){			// すべての値が入力されていなければエラー
							$this->setUserErrorMsg('選択されていないフィールドがあります。曜日=' . $weekName);
						} else {
							// 範囲のチェック
							$startTime2 = intval($itemValue5) * 100 + intval($itemValue6);
							$endTime2 = intval($itemValue7) * 100 + intval($itemValue8);
							if ($startTime2 >= $endTime2) $this->setUserErrorMsg('指定範囲にエラーがあります。曜日=' . $weekName);
						}
					}
					// 前半、後半の整合チェック
					if ($this->getMsgCount() == 0){			// エラーのないとき
						if ($startTime1 != -1 && $endTime1 != -1 && $startTime2 != -1 && $endTime2 != -1){
							if ($endTime1 >= $startTime2) $this->setUserErrorMsg('指定範囲にエラーがあります。曜日=' . $weekName);
						}
					}
				}
			}
			if ($this->getMsgCount() == 0){			// エラーのないとき
				// トランザクションスタート
				$this->db->startTransaction();
				
				// 登録項目を一旦削除
				$ret = $this->db->deleteAllCalendar(self::CONFIG_ID);
				
				if ($ret){
					// データを登録
					for ($i = 0; $i < 7; $i++){
						$hourMinute		= $this->availableTime[$i];			// 設定時分を取得
						$startHour		= $hourMinute[0][0];
						$startMinute	= $hourMinute[0][1];
						$endHour		= $hourMinute[0][2];
						$endMinute		= $hourMinute[0][3];
						$startHour2		= $hourMinute[1][0];
						$startMinute2	= $hourMinute[1][1];
						$endHour2		= $hourMinute[1][2];
						$endMinute2		= $hourMinute[1][3];
					
						// 指定方法
						$specifyType = 1;	// 曜日指定
						$attr = $i + 1;		// 曜日
						
						// 前半を追加
						if ($startHour != -1){		// データが存在するとき
							$startTime = intval($startHour) * 100 + intval($startMinute);
							$endTime = intval($endHour) * 100 + intval($endMinute);
							$ret = $this->db->addCalendar(self::CONFIG_ID, 1/*通常*/, $specifyType, $attr, ''/*日付なし*/, $startTime, $endTime, 1/*利用可能*/);
							if (!$ret) break;
						}
						
						// 後半を追加
						if ($startHour2 != -1){		// データが存在するとき
							$startTime = intval($startHour2) * 100 + intval($startMinute2);
							$endTime = intval($endHour2) * 100 + intval($endMinute2);
							$ret = $this->db->addCalendar(self::CONFIG_ID, 1/*通常*/, $specifyType, $attr, ''/*日付なし*/, $startTime, $endTime, 1/*利用可能*/);
							if (!$ret) break;
						}
					}
				}
				
				// トランザクション終了
				$ret = $this->db->endTransaction();
				if ($ret){
					$this->setMsg(self::MSG_GUIDANCE, 'データを更新しました');
				} else {
					$this->setMsg(self::MSG_APP_ERR, 'データ更新に失敗しました');
				}				
			}
		}
		// 設定値を取得
		$ret = $this->db->getCalendarByWeek(self::CONFIG_ID, $rows);
		if ($ret){
			// データ初期化
			$newAvailableTime = array();
			for ($i = 0; $i < 7; $i++){
				$newAvailableTime[] = array(array(-1, -1, -1, -1), array(-1, -1, -1, -1));
			}
			for ($i = 0; $i < count($rows); $i++){
				// 時間を解析
				$startHour		= intval($rows[$i]['ra_start_time'] / 100);
				$startMinute	= $rows[$i]['ra_start_time'] - $startHour * 100;
				$endHour		= intval($rows[$i]['ra_end_time'] / 100);
				$endMinute		= $rows[$i]['ra_end_time'] - $endHour * 100;

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
			// 取得値を更新
			$this->availableTime = $newAvailableTime;
		}
		
		// 時間メニューを作成
		$this->createTimeMenu();
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
		
		// デフォルトメニュー作成
		for ($i = 0; $i < 7; $i++){
			// メニュー名作成
			$menuName1 = 'time' . $i . '1_start_hour';
			$menuName2 = 'time' . $i . '1_start_minute';
			$menuName3 = 'time' . $i . '1_end_hour';
			$menuName4 = 'time' . $i . '1_end_minute';
			$menuName5 = 'time' . $i . '2_start_hour';
			$menuName6 = 'time' . $i . '2_start_minute';
			$menuName7 = 'time' . $i . '2_end_hour';
			$menuName8 = 'time' . $i . '2_end_minute';
			
			if (count($this->availableTime) > 0){
				$hourMinute = $this->availableTime[$i];			// 設定時分を取得
				$selectedStartHour = $hourMinute[0][0];
				$selectedStartMinute = $hourMinute[0][1];
				$selectedEndHour = $hourMinute[0][2];
				$selectedEndMinute = $hourMinute[0][3];
				$selectedStartHour2 = $hourMinute[1][0];
				$selectedStartMinute2 = $hourMinute[1][1];
				$selectedEndHour2 = $hourMinute[1][2];
				$selectedEndMinute2 = $hourMinute[1][3];
			} else {
				$selectedStartHour = -1;
				$selectedStartMinute = -1;
				$selectedEndHour = -1;
				$selectedEndMinute = -1;
				$selectedStartHour2 = -1;
				$selectedStartMinute2 = -1;
				$selectedEndHour2 = -1;
				$selectedEndMinute2 = -1;
			}
						
			// 時メニュー作成
			for ($j = 0; $j < 24; $j++){
				// 前半の時間帯
				$selected = '';
				if ($j == $selectedStartHour) $selected = 'selected';
				$row = array(
					'value'    => $j,			// 値
					'name'     => $j,			// 表示名
					'selected' => $selected		// 選択中かどうか
				);
				$this->tmpl->addVars($menuName1, $row);
				$this->tmpl->parseTemplate($menuName1, 'a');

				$selected = '';
				if ($j == $selectedEndHour) $selected = 'selected';
				$row = array(
					'value'    => $j,			// 値
					'name'     => $j,			// 表示名
					'selected' => $selected		// 選択中かどうか
				);
				$this->tmpl->addVars($menuName3, $row);
				$this->tmpl->parseTemplate($menuName3, 'a');
				
				// 後半の時間帯
				$selected = '';
				if ($j == $selectedStartHour2) $selected = 'selected';
				$row = array(
					'value'    => $j,			// 値
					'name'     => $j,			// 表示名
					'selected' => $selected		// 選択中かどうか
				);
				$this->tmpl->addVars($menuName5, $row);
				$this->tmpl->parseTemplate($menuName5, 'a');

				$selected = '';
				if ($j == $selectedEndHour2) $selected = 'selected';
				$row = array(
					'value'    => $j,			// 値
					'name'     => $j,			// 表示名
					'selected' => $selected		// 選択中かどうか
				);
				$this->tmpl->addVars($menuName7, $row);
				$this->tmpl->parseTemplate($menuName7, 'a');
			}
			// 分メニュー作成
			for ($j = 0; $j < $unitCount; $j++){
				// 前半の時間帯
				$minute = $this->unitIntervalMinute * $j;
				$selected = '';
				if ($minute == $selectedStartMinute) $selected = 'selected';
				$row = array(
					'value'    => $minute,			// 値
					'name'     => $minute,			// 表示名
					'selected' => $selected		// 選択中かどうか
				);
				$this->tmpl->addVars($menuName2, $row);
				$this->tmpl->parseTemplate($menuName2, 'a');
				
				$selected = '';
				if ($minute == $selectedEndMinute) $selected = 'selected';
				$row = array(
					'value'    => $minute,			// 値
					'name'     => $minute,			// 表示名
					'selected' => $selected		// 選択中かどうか
				);
				$this->tmpl->addVars($menuName4, $row);
				$this->tmpl->parseTemplate($menuName4, 'a');
				
				// 後半の時間帯
				$selected = '';
				if ($minute == $selectedStartMinute2) $selected = 'selected';
				$row = array(
					'value'    => $minute,			// 値
					'name'     => $minute,			// 表示名
					'selected' => $selected		// 選択中かどうか
				);
				$this->tmpl->addVars($menuName6, $row);
				$this->tmpl->parseTemplate($menuName6, 'a');
				
				$selected = '';
				if ($minute == $selectedEndMinute2) $selected = 'selected';
				$row = array(
					'value'    => $minute,			// 値
					'name'     => $minute,			// 表示名
					'selected' => $selected		// 選択中かどうか
				);
				$this->tmpl->addVars($menuName8, $row);
				$this->tmpl->parseTemplate($menuName8, 'a');
			}
		}
	}
}
?>
