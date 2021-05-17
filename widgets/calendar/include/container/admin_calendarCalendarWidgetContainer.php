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
 * @copyright  Copyright 2006-2021 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getWidgetContainerPath('calendar') . '/admin_calendarBaseWidgetContainer.php');

class admin_calendarCalendarWidgetContainer extends admin_calendarBaseWidgetContainer
{
	private $serialNo;		// 選択中の項目のシリアル番号
	private $serialArray = array();			// 表示中のシリアル番号
	private $configId;		// 定義ID
	private $paramObj;		// パラメータ保存用オブジェクト
	private $viewOption;	// FullCalendar表示オプション
	private $css;		// デザインCSS
	private $dateDefId;		// カレンダー定義ID
	const DEFAULT_NAME_HEAD = '名称未設定';			// デフォルトの設定名
	const DEFAULT_EVENT_TOOLTIP_TITLE_STYLE	= "color: '#fff', background: 'red'";		// ツールチップ(タイトル)のスタイル
	const DEFAULT_EVENT_TOOLTIP_BORDER_STYLE	= "width: 2, radius: 5, color: '#444'";		// ツールチップ(ボーダー)のスタイル
	// DB定義
	const CF_GOOGLE_API_KEY	= 'google_api_key';		// GoogleAPIキー
		
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
		if ($task == self::TASK_CALENDAR_LIST){		// 設定一覧
			return 'admin_list.tmpl.html';
		} else {			// 一覧画面
			return 'admin.tmpl.html';
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
		if ($task == self::TASK_CALENDAR_LIST){		// 設定一覧
			return $this->createList($request);
		} else {			// 詳細設定画面
			return $this->createDetail($request);
		}
	}
	/**
	 * 詳細画面作成
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param								なし
	 */
	function createDetail($request)
	{
		// ページ定義IDとページ定義のレコードシリアル番号を取得
		$this->startPageDefParam($defSerial, $defConfigId, $this->paramObj);
		
		$userId		= $this->gEnv->getCurrentUserId();
		$this->langId	= $this->gEnv->getCurrentLanguage();		// 表示言語を取得
		$act = $request->trimValueOf('act');
		$this->serialNo = $request->trimValueOf('serial');		// 選択項目のシリアル番号
		$this->configId = $request->trimValueOf('item_id');		// 定義ID
		if (empty($this->configId)) $this->configId = $defConfigId;		// 呼び出しウィンドウから引き継いだ定義ID
		
		// 入力値を取得
		$name	= $request->trimValueOf('item_name');			// 定義名
		$this->dateDefId = $request->trimValueOf('item_date_def_id');		// カレンダー定義ID
		$this->viewOption = $request->valueOf('item_view_option');	// FullCalendar表示オプション
		$showSimpleEvent = $request->trimCheckedValueOf('item_show_simple_event');		// 簡易イベント記事を表示するかどうか
		$showEvent = $request->trimCheckedValueOf('item_show_event');		// イベント記事を表示するかどうか
		$showEventTooltip = $request->trimCheckedValueOf('item_show_event_tooltip');		// イベント記事用のツールチップを表示するかどうか
		$showHoliday = $request->trimCheckedValueOf('item_show_holiday');		// 祝日を表示するかどうか
		$simpleEventTooltipTitleStyle = $request->trimValueOf('item_simple_event_tooltip_title_style');		// ツールチップ(タイトル)のスタイル
		$simpleEventTooltipBorderStyle = $request->trimValueOf('item_simple_event_tooltip_border_style');		// ツールチップ(ボーダー)のスタイル		
		$eventTooltipTitleStyle = $request->trimValueOf('item_event_tooltip_title_style');		// ツールチップ(タイトル)のスタイル
		$eventTooltipBorderStyle = $request->trimValueOf('item_event_tooltip_border_style');		// ツールチップ(ボーダー)のスタイル
		$holidayColor = $request->trimValueOf('item_holiday_color');		// 背景色(祝日)
		$layoutTooltip = $request->valueOf('item_layout_tooltip');		// ツールチップのレイアウト
		$this->css	= $request->valueOf('item_css');		// デザインCSS
		
		// 空の場合はデフォルト値に戻す
		if (empty($simpleEventTooltipTitleStyle)) $simpleEventTooltipTitleStyle = self::DEFAULT_EVENT_TOOLTIP_TITLE_STYLE;		// ツールチップ(タイトル)のスタイル
		if (empty($simpleEventTooltipBorderStyle)) $simpleEventTooltipBorderStyle = self::DEFAULT_EVENT_TOOLTIP_BORDER_STYLE;		// ツールチップ(ボーダー)のスタイル
		if (empty($eventTooltipTitleStyle)) $eventTooltipTitleStyle = self::DEFAULT_EVENT_TOOLTIP_TITLE_STYLE;		// ツールチップ(タイトル)のスタイル
		if (empty($eventTooltipBorderStyle)) $eventTooltipBorderStyle = self::DEFAULT_EVENT_TOOLTIP_BORDER_STYLE;		// ツールチップ(ボーダー)のスタイル
		if (empty($layoutTooltip)) $layoutTooltip = $this->getParsedTemplateData('default_tooltip.tmpl.html');		// ツールチップのレイアウト	
				
		$replaceNew = false;		// データを再取得するかどうか
		if ($act == 'add'){// 新規追加
			// 入力チェック
			$this->checkInput($name, '名前');
			
			// 設定名の重複チェック
			for ($i = 0; $i < count($this->paramObj); $i++){
				$targetObj = $this->paramObj[$i]->object;
				if ($name == $targetObj->name){		// 定義名
					$this->setUserErrorMsg('名前が重複しています');
					break;
				}
			}
			// GoogleAPIを利用の場合はキーの設定をチェック
			if ($showHoliday){
				$retValue = $this->gSystem->getSystemConfig(self::CF_GOOGLE_API_KEY);
				if (empty($retValue)) $this->setUserErrorMsg('GoogleAPIキーの設定が必要です');
			}
			
			// エラーなしの場合は、データを登録
			if ($this->getMsgCount() == 0){
				// 追加オブジェクト作成
				$newObj = new stdClass;
				$newObj->name	= $name;// 表示名
				$newObj->dateDefId = $this->dateDefId;		// カレンダー定義ID
				$newObj->viewOption = $this->viewOption;	// FullCalendar表示オプション
				$newObj->showSimpleEvent = $showSimpleEvent;		// 簡易イベント記事を表示するかどうか
				$newObj->showEvent = $showEvent;		// イベント記事を表示するかどうか
				$newObj->showEventTooltip = $showEventTooltip;		// イベント記事用のツールチップを表示するかどうか
				$newObj->showHoliday = $showHoliday;		// 祝日を表示するかどうか
				$newObj->simpleEventTooltipTitleStyle = $simpleEventTooltipTitleStyle;		// ツールチップ(タイトル)のスタイル
				$newObj->simpleEventTooltipBorderStyle = $simpleEventTooltipBorderStyle;		// ツールチップ(ボーダー)のスタイル
				$newObj->eventTooltipTitleStyle = $eventTooltipTitleStyle;		// ツールチップ(タイトル)のスタイル
				$newObj->eventTooltipBorderStyle = $eventTooltipBorderStyle;		// ツールチップ(ボーダー)のスタイル
				$newObj->layoutTooltip = $layoutTooltip;		// ツールチップのレイアウト
				$newObj->holidayColor = $holidayColor;		// 背景色(祝日)
				$newObj->css = $this->css;		// デザインCSS
				
				$ret = $this->addPageDefParam($defSerial, $defConfigId, $this->paramObj, $newObj);
				if ($ret){
					$this->setGuidanceMsg('データを追加しました');
					
					$this->configId = $defConfigId;		// 定義定義IDを更新
					$replaceNew = true;			// データ再取得
				} else {
					$this->setAppErrorMsg('データ追加に失敗しました');
				}
			}
		} else if ($act == 'update'){		// 設定更新のとき
			// 入力値のエラーチェック
			// GoogleAPIを利用の場合はキーの設定をチェック
			if ($showHoliday){
				$retValue = $this->gSystem->getSystemConfig(self::CF_GOOGLE_API_KEY);
				if (empty($retValue)) $this->setUserErrorMsg('GoogleAPIキーの設定が必要です');
			}
			
			if ($this->getMsgCount() == 0){			// エラーのないとき
				// 現在の設定値を取得
				$ret = $this->getPageDefParam($defSerial, $defConfigId, $this->paramObj, $this->configId, $targetObj);
				if ($ret){
					// ウィジェットオブジェクト更新
					$targetObj->dateDefId = $this->dateDefId;		// カレンダー定義ID
					$targetObj->viewOption = $this->viewOption;	// FullCalendar表示オプション
					$targetObj->showSimpleEvent = $showSimpleEvent;		// 簡易イベント記事を表示するかどうか
					$targetObj->showEvent = $showEvent;		// イベント記事を表示するかどうか
					$targetObj->showEventTooltip = $showEventTooltip;		// イベント記事用のツールチップを表示するかどうか
					$targetObj->showHoliday = $showHoliday;		// 祝日を表示するかどうか
					$targetObj->simpleEventTooltipTitleStyle = $simpleEventTooltipTitleStyle;		// ツールチップ(タイトル)のスタイル
					$targetObj->simpleEventTooltipBorderStyle = $simpleEventTooltipBorderStyle;		// ツールチップ(ボーダー)のスタイル
					$targetObj->eventTooltipTitleStyle = $eventTooltipTitleStyle;		// ツールチップ(タイトル)のスタイル
					$targetObj->eventTooltipBorderStyle = $eventTooltipBorderStyle;		// ツールチップ(ボーダー)のスタイル
					$targetObj->layoutTooltip = $layoutTooltip;		// ツールチップのレイアウト
					$targetObj->holidayColor = $holidayColor;		// 背景色(祝日)
					$targetObj->css			= $this->css;		// デザインCSS
				}
				
				// 設定値を更新
				if ($ret) $ret = $this->updatePageDefParam($defSerial, $defConfigId, $this->paramObj, $this->configId, $targetObj);
				if ($ret){
					$this->setMsg(self::MSG_GUIDANCE, 'データを更新しました');
					$replaceNew = true;			// データ再取得
				} else {
					$this->setMsg(self::MSG_APP_ERR, 'データ更新に失敗しました');
				}
			}
		} else if ($act == 'select'){	// 定義IDを変更
			$replaceNew = true;			// データ再取得
		} else {	// 初期起動時、または上記以外の場合
			// デフォルト値設定
			$this->configId = $defConfigId;		// 呼び出しウィンドウから引き継いだ定義ID
			$replaceNew = true;			// データ再取得
		}
		// 表示用データを取得
		if (empty($this->configId)){		// 新規登録の場合
			$this->tmpl->setAttribute('item_name_visible', 'visibility', 'visible');// 名前入力フィールド表示
			if ($replaceNew){		// データ再取得時
//				$name = $this->createDefaultName();			// デフォルト登録項目名
				$name = $this->createConfigDefaultName();			// デフォルト登録項目名
				$this->dateDefId = '0';		// カレンダー定義ID
				$this->viewOption = $this->getParsedTemplateData('option.tmpl.js');	// FullCalendar表示オプション
				$showSimpleEvent = '0';		// 簡易イベント記事を表示するかどうか
				$showEvent = '0';		// イベント記事を表示するかどうか
				$showEventTooltip	= '0';		// イベント記事用のツールチップを表示するかどうか
				$showHoliday = '0';		// 祝日を表示するかどうか
				$simpleEventTooltipTitleStyle = self::DEFAULT_EVENT_TOOLTIP_TITLE_STYLE;		// ツールチップ(タイトル)のスタイル
				$simpleEventTooltipBorderStyle = self::DEFAULT_EVENT_TOOLTIP_BORDER_STYLE;		// ツールチップ(ボーダー)のスタイル
				$eventTooltipTitleStyle = self::DEFAULT_EVENT_TOOLTIP_TITLE_STYLE;		// ツールチップ(タイトル)のスタイル
				$eventTooltipBorderStyle = self::DEFAULT_EVENT_TOOLTIP_BORDER_STYLE;		// ツールチップ(ボーダー)のスタイル
				$layoutTooltip = $this->getParsedTemplateData('default_tooltip.tmpl.html');		// ツールチップのレイアウト	
				$holidayColor = '';		// 背景色(祝日)	
				$this->css = $this->getParsedTemplateData('default.tmpl.css', array($this, 'makeCss'));		// デザインCSS
			}
			$this->serialNo = 0;
		} else {
			if ($replaceNew){// データ再取得時
				$ret = $this->getPageDefParam($defSerial, $defConfigId, $this->paramObj, $this->configId, $targetObj);
				if ($ret){
					$name		= $targetObj->name;	// 名前
					$this->dateDefId	= $targetObj->dateDefId;		// カレンダー定義ID
					$this->viewOption = $targetObj->viewOption;	// FullCalendar表示オプション
					if (isset($targetObj->showSimpleEvent)) $showSimpleEvent = $targetObj->showSimpleEvent;		// 簡易イベント記事を表示するかどうか
					if (isset($targetObj->showEvent)) $showEvent = $targetObj->showEvent;		// イベント記事を表示するかどうか
					if (isset($targetObj->showEventTooltip)) $showEventTooltip	= $targetObj->showEventTooltip;		// イベント記事用のツールチップを表示するかどうか
					if (isset($targetObj->showHoliday)) $showHoliday = $targetObj->showHoliday;		// 祝日を表示するかどうか
					if (isset($targetObj->simpleEventTooltipTitleStyle)) $simpleEventTooltipTitleStyle = $targetObj->simpleEventTooltipTitleStyle;		// ツールチップ(タイトル)のスタイル
					if (isset($targetObj->simpleEventTooltipBorderStyle)) $simpleEventTooltipBorderStyle = $targetObj->simpleEventTooltipBorderStyle;		// ツールチップ(ボーダー)のスタイル
					if (isset($targetObj->eventTooltipTitleStyle)) $eventTooltipTitleStyle = $targetObj->eventTooltipTitleStyle;		// ツールチップ(タイトル)のスタイル
					if (isset($targetObj->eventTooltipBorderStyle)) $eventTooltipBorderStyle = $targetObj->eventTooltipBorderStyle;		// ツールチップ(ボーダー)のスタイル
					if (isset($targetObj->layoutTooltip)) $layoutTooltip = $targetObj->layoutTooltip;		// ツールチップのレイアウト
					if (isset($targetObj->holidayColor)) $holidayColor = $targetObj->holidayColor;		// 背景色(祝日)
					if (isset($targetObj->css)) $this->css = $targetObj->css;					// デザインCSS
				}
			}
			$this->serialNo = $this->configId;
				
			// 新規作成でないときは、メニューを変更不可にする(画面作成から呼ばれている場合のみ)
			if (!empty($defConfigId) && !empty($defSerial)) $this->tmpl->addVar("_widget", "id_disabled", 'disabled');
		}
		// 設定項目選択メニュー作成
		$this->createItemMenu();
		
		// 日付定義選択メニュー作成
		self::$_mainDb->getCalendarDefList(array($this, 'dateDefLoop'));
		if (self::$_mainDb->getEffectedRowCount() <= 0) $this->tmpl->setAttribute('date_def_list', 'visibility', 'hidden');// 一覧非表示
		
		// 画面にデータを埋め込む
		if (!empty($this->configId)) $this->tmpl->addVar("_widget", "id", $this->configId);		// 定義ID
		$this->tmpl->addVar("item_name_visible", "name",	$name);
		$this->tmpl->addVar("_widget", "view_option",	$this->convertToDispString($this->viewOption));		// FullCalendar表示オプション
		$this->tmpl->addVar("_widget", "show_simple_event",	$this->convertToCheckedString($showSimpleEvent));		// 簡易イベント記事を表示するかどうか
		$this->tmpl->addVar("_widget", "show_event",	$this->convertToCheckedString($showEvent));		// イベント記事を表示するかどうか
		$this->tmpl->addVar("_widget", "show_event_tooltip",	$this->convertToCheckedString($showEventTooltip));	// イベント記事用のツールチップを表示するかどうか
		$this->tmpl->addVar("_widget", "show_holiday",	$this->convertToCheckedString($showHoliday));		// 祝日を表示するかどうか
		$this->tmpl->addVar("_widget", "holiday_color",	$this->convertToDispString($holidayColor));		// 背景色(祝日)
		$this->tmpl->addVar("_widget", "css",	$this->convertToDispString($this->css));		// デザインCSS
		$this->tmpl->addVar("_widget", "simple_event_tooltip_title_style",	$this->convertToDispString($simpleEventTooltipTitleStyle));		// ツールチップ(タイトル)のスタイル
		$this->tmpl->addVar("_widget", "simple_event_tooltip_border_style",	$this->convertToDispString($simpleEventTooltipBorderStyle));		// ツールチップ(ボーダー)のスタイル
		$this->tmpl->addVar("_widget", "event_tooltip_title_style",	$this->convertToDispString($eventTooltipTitleStyle));		// ツールチップ(タイトル)のスタイル
		$this->tmpl->addVar("_widget", "event_tooltip_border_style",	$this->convertToDispString($eventTooltipBorderStyle));		// ツールチップ(ボーダー)のスタイル
		$this->tmpl->addVar("_widget", "layout_tooltip",	$this->convertToDispString($layoutTooltip));		// ツールチップのレイアウト
		
		$this->tmpl->addVar("_widget", "serial", $this->serialNo);// 選択中のシリアル番号、IDを設定
		
		// ボタンの表示制御
		if (empty($this->serialNo)){		// 新規追加項目を選択しているとき
			$this->tmpl->setAttribute('add_button', 'visibility', 'visible');// 「新規追加」ボタン
		} else {
			$this->tmpl->setAttribute('update_button', 'visibility', 'visible');// 「更新」ボタン
		}
		
		// ページ定義IDとページ定義のレコードシリアル番号を更新
		$this->endPageDefParam($defSerial, $defConfigId, $this->paramObj);
	}
	/**
	 * 選択用メニューを作成
	 *
	 * @return なし						
	 */
	function createItemMenu()
	{
		if (!is_array($this->paramObj)) return;
		
		for ($i = 0; $i < count($this->paramObj); $i++){
			$id = $this->paramObj[$i]->id;// 定義ID
			$targetObj = $this->paramObj[$i]->object;
			$name = $targetObj->name;// 定義名
			$selected = '';
			if ($this->configId == $id) $selected = 'selected';

			$row = array(
				'name' => $name,		// 名前
				'value' => $id,		// 定義ID
				'selected' => $selected	// 選択中の項目かどうか
			);
			$this->tmpl->addVars('title_list', $row);
			$this->tmpl->parseTemplate('title_list', 'a');
		}
	}
	/**
	 * デフォルトの名前を取得
	 *
	 * @return string	デフォルト名						
	 */
/*	function createDefaultName()
	{
		$name = self::DEFAULT_NAME_HEAD;
		for ($j = 1; $j < 100; $j++){
			$name = self::DEFAULT_NAME_HEAD . $j;
			// 設定名の重複チェック
			for ($i = 0; $i < count($this->paramObj); $i++){
				$targetObj = $this->paramObj[$i]->object;
				if ($name == $targetObj->name){		// 定義名
					break;
				}
			}
			// 重複なしのときは終了
			if ($i == count($this->paramObj)) break;
		}
		return $name;
	}*/
	/**
	 * 一覧画面作成
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param								なし
	 */
	function createList($request)
	{
		// ページ定義IDとページ定義のレコードシリアル番号を取得
		$this->startPageDefParam($defSerial, $defConfigId, $this->paramObj);
		
		$userId		= $this->gEnv->getCurrentUserId();
		$langId	= $this->gEnv->getCurrentLanguage();		// 表示言語を取得
		$act = $request->trimValueOf('act');
		
		if ($act == 'delete'){		// メニュー項目の削除
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
				$ret = $this->delPageDefParam($defSerial, $defConfigId, $this->paramObj, $delItems);
				if ($ret){		// データ削除成功のとき
					$this->setGuidanceMsg('データを削除しました');
				} else {
					$this->setAppErrorMsg('データ削除に失敗しました');
				}
			}
		}
		// 定義一覧作成
		$this->createItemList();
		if (count($this->serialArray) <= 0) $this->tmpl->setAttribute('itemlist', 'visibility', 'hidden');		// データがないときは一覧非表示
		
		$this->tmpl->addVar("_widget", "serial_list", implode(',', $this->serialArray));// 表示項目のシリアル番号を設定
		
		// ページ定義IDとページ定義のレコードシリアル番号を更新
		$this->endPageDefParam($defSerial, $defConfigId, $this->paramObj);
	}
	/**
	 * 定義一覧作成
	 *
	 * @return なし						
	 */
	function createItemList()
	{
		for ($i = 0; $i < count($this->paramObj); $i++){
			$id			= $this->paramObj[$i]->id;// 定義ID
			$targetObj	= $this->paramObj[$i]->object;
			$name = $targetObj->name;// 定義名
		
			// 使用数
			$defCount = 0;
			if (!empty($id)){
				$defCount = $this->_db->getPageDefCount($this->gEnv->getCurrentWidgetId(), $id);
			}
			$operationDisagled = '';
			if ($defCount > 0) $operationDisagled = 'disabled';
			
			$row = array(
				'index' => $i,
				'id' => $id,
				'ope_disabled' => $operationDisagled,			// 選択可能かどうか
				'name' => $this->convertToDispString($name),		// 名前
				'def_count' => $defCount							// 使用数
			);
			$this->tmpl->addVars('itemlist', $row);
			$this->tmpl->parseTemplate('itemlist', 'a');
			
			// シリアル番号を保存
			$this->serialArray[] = $id;
		}
	}
	/**
	 * CSSデータ作成処理コールバック
	 *
	 * @param object         $tmpl			テンプレートオブジェクト
	 * @param								なし
	 */
	function makeCss($tmpl)
	{
	}
	/**
	 * 日付定義をテンプレートに設定する
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function dateDefLoop($index, $fetchedRow, $param)
	{
		$value = $fetchedRow['cd_id'];
		$row = array(
			'value'    => $this->convertToDispString($value),			// 定義ID
			'name'     => $this->convertToDispString($fetchedRow['cd_name']),			// 定義名
			'selected' => $this->convertToSelectedString($value, $this->dateDefId)			// 選択中かどうか
		);
		$this->tmpl->addVars('date_def_list', $row);
		$this->tmpl->parseTemplate('date_def_list', 'a');	
		return true;
	}
}
?>
