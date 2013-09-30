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

class admin_calendarDateWidgetContainer extends admin_calendarBaseWidgetContainer
{
	private $serialArray = array();		// 表示されている項目シリアル番号
	private $dateFieldArray;	// 基本日入力値
	private $repeatTypeArray;		// 繰り返しタイプ
	private $repeatType;		// 繰り返しタイプ
	private $weekArray;			// 曜日データ
	private $dateCount;			// 基本日数
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// 初期値設定
		$this->repeatTypeArray	= array(	array(	'name' => '繰り返しなし',	'value' => '0'),
										array(	'name' => '曜日基準',		'value' => '1'));
		$this->weekArray		= array('日', '月', '火', '水', '木', '金', '土');			// 曜日データ
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
		if ($task == 'date_detail'){		// 詳細画面
			return 'admin_date_detail.tmpl.html';
		} else {			// 一覧画面
			return 'admin_date.tmpl.html';
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
		if ($task == 'date_detail'){	// 詳細画面
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
		// パラメータの取得
		$task = $request->trimValueOf('task');		// 処理区分
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
			if ($this->getMsgCount() == 0 && count($delItems) > 0){
				$ret = self::$_mainDb->deleteCalendarDef($delItems);
				if ($ret){		// データ削除成功のとき
					$this->setGuidanceMsg('データを削除しました');
				} else {
					$this->setAppErrorMsg('データ削除に失敗しました');
				}
			}
		}
		// 一覧作成
		self::$_mainDb->getCalendarDefList(array($this, 'calendarDefLoop'));
		if (empty($this->serialArray)) $this->tmpl->setAttribute('calendar_def_list', 'visibility', 'hidden');// 一覧非表示
		
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
		$defId = $request->trimValueOf('serial');		// カレンダー定義ID

		$name = $request->trimValueOf('item_name');		// 日付タイプ名
		$this->repeatType = $request->trimIntValueOf('item_repeat_type', '0');		// 繰り返しタイプ
		
		$this->dateCount = intval($request->trimValueOf('datecount'));		// 基本日数		
		$dateNames		= $request->trimValueOf('item_date_name');		// 基本日名
		$dateTypes = $request->trimValueOf('item_date_type');		// 基本日日付タイプ
		
		// 基本日入力取得
		$this->dateFieldArray = array();
		for ($i = 0; $i < $this->dateCount; $i++){
			$newObj				= new stdClass;
			$newObj->dateName	= $dateNames[$i];			// 基本日名
			$newObj->dateType	= $dateTypes[$i];		// 基本日日付タイプ
			$this->dateFieldArray[]	= $newObj;
		}

		$replaceNew = false;		// データを再取得するかどうか
		if ($act == 'add'){		// 新規追加のとき
			// 入力チェック
			$this->checkInput($name, '名前');
			
			// エラーなしの場合は、データを追加
			if ($this->getMsgCount() == 0){	
				// カレンダー定義を追加
				$ret = self::$_mainDb->updateCalendarDef(0/*新規*/, $name, $this->repeatType, $this->dateCount, $newId);
				
				// 基本日を追加
				if ($ret) $ret = self::$_mainDb->updateDate($newId, 0/*インデックス番号*/, $this->dateFieldArray);

				// 例外日を追加
				
				if ($ret){		// データ追加成功のとき
					$this->setMsg(self::MSG_GUIDANCE, 'データを追加しました');
					
					$defId = $newId;	// カレンダー定義ID
					$replaceNew = true;			// データを再取得
				} else {
					$this->setMsg(self::MSG_APP_ERR, 'データの追加に失敗しました');
				}
			}
		} else if ($act == 'update'){		// 更新のとき
			// 入力チェック
			$this->checkInput($name, '名前');
			
			// エラーなしの場合は、データを更新
			if ($this->getMsgCount() == 0){
				// カレンダー定義を更新
				$ret = self::$_mainDb->updateCalendarDef($defId, $name, $this->repeatType, $this->dateCount, $newId);
				
				// 基本日を追加
				if ($ret) $ret = self::$_mainDb->updateDate($defId, 0/*インデックス番号*/, $this->dateFieldArray);
				
				if ($ret){		// データ追加成功のとき
					$this->setMsg(self::MSG_GUIDANCE, 'データを更新しました');
					$replaceNew = true;			// データを再取得
				} else {
					$this->setMsg(self::MSG_APP_ERR, 'データ更新に失敗しました');
				}
			}
		} else if ($act == 'delete'){		// 削除のとき		
			// エラーなしの場合は、データを削除
			if ($this->getMsgCount() == 0){
				$ret = self::$_mainDb->deleteCalendarDef(array($defId));
				if ($ret){		// データ削除成功のとき
					$this->setMsg(self::MSG_GUIDANCE, 'データを削除しました');
				} else {
					$this->setMsg(self::MSG_APP_ERR, 'データ削除に失敗しました');
				}
			}
		} else if ($act == 'select'){
		} else {		// 初期状態
			$replaceNew = true;			// データを再取得
		}
		
		// 表示データ再取得
		if ($replaceNew){
			$ret = self::$_mainDb->getCalendarDef($defId, $row);
			if ($ret){
				$name = $row['cd_name'];
				$this->repeatType = $row['cd_repeat_type'];

				// 基本日を取得
				$this->dateFieldArray = array();
				self::$_mainDb->getDateList($defId, 0/*基本データ*/, array($this, 'dateLoop'));			
			} else {		// 新規の場合
				$name = '';
				$this->repeatType = '0';
			}
		}
		
		// 繰り返しタイプメニュー作成
		$this->createRepeatTypeMenu();
		
		// 日付タイプ選択メニュー作成
		self::$_mainDb->getDateTypeList(array($this, 'dateTypeLoop'));
		if (self::$_mainDb->getEffectedRowCount() <= 0) $this->tmpl->setAttribute('date_type_list', 'visibility', 'hidden');// 一覧非表示		

		// 基本日一覧を作成
		$this->createDateList();

		$this->tmpl->addVar("_widget", "date_count", $this->convertToDispString($this->dateCount));	// 基本日数
	
		// 時間割定義を作成
		$this->createTimeList();
//		if (empty($this->timeDefArray)) $this->tmpl->setAttribute('except_date_list', 'visibility', 'hidden');
		
		// 入力フィールドの設定、共通項目のデータ設定
		if (empty($defId)){		// 新規追加のとき
			$this->tmpl->addVar('_widget', 'id', '新規');
			
			$this->tmpl->setAttribute('add_button', 'visibility', 'visible');	// 追加ボタン表示
		} else {
			$this->tmpl->addVar('_widget', 'id', $defId);
			
			// データ更新、削除ボタン表示
			$this->tmpl->setAttribute('delete_button', 'visibility', 'visible');
			$this->tmpl->setAttribute('update_button', 'visibility', 'visible');	// 更新ボタン表示
		}
		$this->tmpl->addVar("_widget", "serial", $this->convertToDispString($defId));	// カレンダー定義ID
		$this->tmpl->addVar("_widget", "name", $this->convertToDispString($name));		// 日付タイプ名
	}
	/**
	 * 日付定義一覧をテンプレートに設定する
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function calendarDefLoop($index, $fetchedRow, $param)
	{
		$id = $fetchedRow['cd_id'];
		$repeatType = $fetchedRow['cd_repeat_type'];
		$repeatTypeName = '';
		for ($i = 0; $i < count($this->repeatTypeArray); $i++){
			if ($this->repeatTypeArray[$i]['value'] == $repeatType) $repeatTypeName = $this->repeatTypeArray[$i]['name'];
		}
		$row = array(
			'name'		=> $this->convertToDispString($fetchedRow['cd_name']),			// カレンダー定義名
			'type'	=> $this->convertToDispString($repeatTypeName),			// 繰り返しタイプ
			'index'			=> $this->convertToDispString($index),			// 行編集用
			'serial'		=> $this->convertToDispString($id)			// 行編集用
		);
		$this->tmpl->addVars('calendar_def_list', $row);
		$this->tmpl->parseTemplate('calendar_def_list', 'a');
		
		// シリアル番号を保存
		$this->serialArray[] = $id;
		return true;
	}
	/**
	 * 日付一覧をテンプレートに設定する
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function dateTypeLoop($index, $fetchedRow, $param)
	{
		$id = $this->convertToDispString($fetchedRow['dt_id']);
		$row = array(
			'name'		=> $this->convertToDispString($fetchedRow['dt_name']),			// 日付タイプ名
			'value'	=> $this->convertToDispString($id)			// 日付タイプID
		);
		$this->tmpl->addVars('date_type_list', $row);
		$this->tmpl->parseTemplate('date_type_list', 'a');		
		return true;
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
		$newObj				= new stdClass;
		$newObj->dateName	= $fetchedRow['ce_name'];			// 基本日名
		$newObj->dateType	= $fetchedRow['ce_date_type_id'];		// 基本日日付タイプ
		$this->dateFieldArray[]	= $newObj;		
/*		$serialNo = $this->convertToDispString($fetchedRow['ce_serial']);
		$row = array(
			'name'		=> $this->convertToDispString($fetchedRow['ce_name']),			// 名前
			'type_id'	=> $this->convertToProperTime($fetchedRow['ce_date_type_id']),			// 日付タイプID
			'index'			=> $this->convertToDispString($index),			// 行編集用
			'serial'		=> $this->convertToDispString($serialNo)			// 行編集用
		);
		$this->tmpl->addVars('date_list', $row);
		$this->tmpl->parseTemplate('date_list', 'a');*/
		
		// シリアル番号を保存
//		$this->serialArray[] = $serialNo;
		return true;
	}
	/**
	 * 時間割定義を作成
	 *
	 * @return なし						
	 */
	function createTimeList()
	{
		$timeCount = count($this->timeDefArray);
		for ($i = 0; $i < $timeCount; $i++){
			$defObj = $this->timeDefArray[$i];
			$title		= $defObj->title;			// 時間枠タイトル
			$startTime	= $defObj->startTime;		// 開始時間
			$minute		= $defObj->minute;			// 時間枠(分)
			$row = array(
				'title'			=> $this->convertToDispString($title),	// 時間枠タイトル
				'start_time'	=> $this->convertToDispString($startTime),	// 開始時間
				'minute'		=> $this->convertToDispString($minute),		// 時間枠(分)
				'root_url'		=> $this->convertToDispString($this->getUrl($this->gEnv->getRootUrl()))
			);
			$this->tmpl->addVars('except_date_list', $row);
			$this->tmpl->parseTemplate('except_date_list', 'a');
		}
	}
	/**
	 * 繰り返しタイプ選択メニュー作成
	 *
	 * @return なし
	 */
	function createRepeatTypeMenu()
	{
		for ($i = 0; $i < count($this->repeatTypeArray); $i++){
			$value = $this->repeatTypeArray[$i]['value'];
			$name = $this->repeatTypeArray[$i]['name'];
			
			$row = array(
				'value'    => $value,			// タイプ値
				'name'     => $this->convertToDispString($name),			// タイプ名
				'selected' => $this->convertToSelectedString($value, $this->repeatType)			// 選択中かどうか
			);
			$this->tmpl->addVars('repeat_type_list', $row);
			$this->tmpl->parseTemplate('repeat_type_list', 'a');
		}
	}
	/**
	 * 基本日リストを作成
	 *
	 * @return なし
	 */
	function createDateList()
	{	
		switch ($this->repeatType){
			case '0':		// 繰り返しなし
			default:
				$this->dateCount = 0;			// 基本日数
				break;
			case '1':		// 曜日基準
				$this->dateCount = count($this->weekArray);		// 基本日数
				
				for ($i = 0; $i < $this->dateCount; $i++){
					$defObj = $this->dateFieldArray[$i];
					$dateName	= $defObj->dateName;			// 基本日名
					$dateType	= $defObj->dateType;		// 基本日日付タイプ

					$row = array(
						'index'		=> $i,
						'key'		=> $this->weekArray[$i],
						'name'		=> $this->convertToDispString($dateName),			// 名前
						'type_id'	=> $this->convertToDispString($dateType),			// 日付タイプID
					);
					$this->tmpl->addVars('date_list', $row);
					$this->tmpl->parseTemplate('date_list', 'a');
				}
				break;
		}
		if ($this->dateCount <= 0) $this->tmpl->setAttribute('date_list', 'visibility', 'hidden');// 一覧非表示
	}
}
?>
