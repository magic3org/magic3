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
 * @copyright  Copyright 2006-2015 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getWidgetContainerPath('evententry_main') . '/admin_evententry_mainBaseWidgetContainer.php');

class admin_evententry_mainEventWidgetContainer extends admin_evententry_mainBaseWidgetContainer
{
	private $serialNo;		// 選択中の項目のシリアル番号
	private $serialArray = array();		// 表示されている項目シリアル番号
	private $idArray = array();			// 表示されているイベントID
	private $status;			// 参加受付状態(1=非公開、2=公開、3=受付停止)
	private $statusTypeArray;	// イベント状態メニュー作成用
	private $contentType;		// コンテンツタイプ
	private $contentObj;			// コンテンツ情報ライブラリ
	const EVENT_OBJ_ID = 'eventlib';		// 検索用オブジェクト
	const DEFAULT_LIST_COUNT = 20;			// 最大リスト表示数
	const LINK_PAGE_COUNT		= 5;			// リンクページ数
	const MESSAGE_SIZE = 40;			// メッセージの最大文字列長
	const ICON_SIZE = 32;		// アイコンのサイズ
	const EYECATCH_IMAGE_SIZE = 40;		// アイキャッチ画像サイズ
	const SEARCH_ICON_FILE = '/images/system/search16.png';		// 検索用アイコン
	const CALENDAR_ICON_FILE = '/images/system/calendar.png';		// カレンダーアイコン
	const ACTIVE_ICON_FILE = '/images/system/active32.png';			// 公開中アイコン
	const INACTIVE_ICON_FILE = '/images/system/inactive32.png';		// 非公開アイコン
	const DEFAULT_CONTENT_TYPE = 'event';			// 予約対象となるコンテンツタイプ
	const DEFAULT_ENTRY_TYPE = '';			// デフォルトのイベント受付タイプ
	// 受付イベントコード自動生成用
	const EVENT_CODE_HEAD = 'eve';			// 自動生成する受付イベントコードのヘッダ部
	const EVENT_CODE_LENGTH = 5;			// 自動生成する受付イベントコードの数値桁数
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
	}
	/**
	 * ウィジェット初期化
	 *
	 * 共通パラメータの初期化や、以下のパターンでウィジェット出力方法の変更を行う。
	 * ・組み込みの_setTemplate(),_assign()を使用
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param string $task					処理タスク
	 * @return 								なし
	 */
	function _init($request, $task)
	{
		// 初期設定
		$this->statusTypeArray = array (
									array(	'name' => '非公開',		'value' => '1'),
									array(	'name' => '受付中',		'value' => '2'),
									array(	'name' => '受付終了',	'value' => '3')
								);			// 参加受付状態
		$this->contentType = self::DEFAULT_CONTENT_TYPE;		// コンテンツタイプ
		
		switch ($this->contentType){
			case M3_VIEW_TYPE_CONTENT:				// 汎用コンテンツ
				break;
			case M3_VIEW_TYPE_PRODUCT:				// 商品情報(Eコマース)
				break;
			case M3_VIEW_TYPE_BBS:					// BBS
				break;
			case M3_VIEW_TYPE_BLOG:				// ブログ
				break;
			case M3_VIEW_TYPE_WIKI:				// wiki
				break;
			case M3_VIEW_TYPE_USER:				// ユーザ作成コンテンツ
				break;
			case M3_VIEW_TYPE_EVENT:				// イベント情報
				$this->contentObj = $this->gInstance->getObject(self::EVENT_OBJ_ID);
				break;
			case M3_VIEW_TYPE_PHOTO:				// フォトギャラリー
				break;
		}
		if (!isset($this->contentObj)) $this->setAppErrorMsg('情報取得オブジェクトが作成できません');
		
		// CKEditor初期化
		if ($task == self::TASK_EVENT_DETAIL){		// 詳細画面
			$this->loadCKEditorCssFiles();
		}
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
		if ($task == self::TASK_EVENT_DETAIL){		// 詳細画面
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
		if ($task == self::TASK_EVENT_DETAIL){	// 詳細画面
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
		// 初期化
		$maxListCount = self::DEFAULT_LIST_COUNT;
		
		// 入力値取得
		$act = $request->trimValueOf('act');

		// ##### 検索条件 #####
		$pageNo = $request->trimIntValueOf(M3_REQUEST_PARAM_PAGE_NO, '1');				// ページ番号
		$search_startDt = $request->trimValueOf('search_start');		// 検索範囲開始日付
		if (!empty($search_startDt)) $search_startDt = $this->convertToProperDate($search_startDt);
		$search_endDt = $request->trimValueOf('search_end');			// 検索範囲終了日付
		if (!empty($search_endDt)) $search_endDt = $this->convertToProperDate($search_endDt);
		$keyword = $request->trimValueOf('search_keyword');			// 検索キーワード

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
				$ret = self::$_mainDb->delEventItem($delItems);
				if ($ret){		// データ削除成功のとき
					$this->setGuidanceMsg('データを削除しました');
				} else {
					$this->setAppErrorMsg('データ削除に失敗しました');
				}
			}
		} else if ($act == 'search'){		// 検索のとき
			if (!empty($search_startDt) && !empty($search_endDt) && $search_startDt > $search_endDt){
				$this->setUserErrorMsg('期間の指定範囲にエラーがあります。');
			}
			$pageNo = 1;		// ページ番号初期化
		} else if ($act == 'geteventlist'){		// イベント一覧取得
			// ##### ウィジェット出力処理中断 ######
			$this->gPage->abortWidget();
		
			$this->act = $act;				// 実行act
			$eventList = $this->getParsedTemplateData('default_eventlist.tmpl.html', array($this, 'makeEventList'), $request);// イベント一覧作成
			$this->gInstance->getAjaxManager()->addData('html', $eventList);
			return;
		}
		
		// ###### 一覧の取得条件を作成 ######
		if (!empty($search_endDt)) $endDt = $this->getNextDay($search_endDt);
		$parsedKeywords = $this->gInstance->getTextConvManager()->parseSearchKeyword($keyword);

		// 総数を取得
		$totalCount = self::$_mainDb->getEntryListCount($this->contentType, $this->_langId, $parsedKeywords);

		// ページング計算
		$this->calcPageLink($pageNo, $totalCount, $maxListCount);
		
		// ページングリンク作成
		$pageLink = $this->createPageLink($pageNo, self::LINK_PAGE_COUNT, ''/*リンク作成用(未使用)*/, 'selpage($1);return false;');
		
		// イベントリストを取得
		self::$_mainDb->getEntryList($this->contentType, $this->_langId, $maxListCount, $pageNo, $parsedKeywords, array($this, 'itemListLoop'));
		if (count($this->serialArray) <= 0) $this->tmpl->setAttribute('itemlist', 'visibility', 'hidden');// イベントがないときは、一覧を表示しない

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
		$this->tmpl->addVar("_widget", "search_keyword", $keyword);	// 検索キーワード

		// その他の項目
		$this->tmpl->addVar("_widget", "serial_list", implode($this->serialArray, ','));// 表示項目のシリアル番号を設定
		$this->tmpl->addVar("_widget", "page", $pageNo);	// ページ番号
		$this->tmpl->addVar("_widget", "target_widget", $this->gEnv->getCurrentWidgetId());// 画像選択ダイアログ用
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
		$this->serialNo = $request->trimValueOf('serial');		// 選択項目のシリアル番号
		if (empty($this->serialNo)) $this->serialNo = 0;
		
		$html			= $request->valueOf('item_html');			// 説明
		$this->status	= $request->trimValueOf('item_status');		// 状態(0=未設定、1=非公開、2=受付中、3=受付終了)
		$eventId		= $request->trimValueOf('eventid');			// イベントID
		$eventCode		= $request->trimValueOf('item_code');		// イベントコード
		
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
		// 時間を修正
		if (!empty($start_time)) $start_time = $this->convertToProperTime($start_time, 1/*時分フォーマット*/);
		if (!empty($end_time)) $end_time = $this->convertToProperTime($end_time, 1/*時分フォーマット*/);
		
		$reloadData = false;		// データの再ロード
		if ($act == 'new'){			// 新規の場合
			// 目的の受付イベントが作成されている場合はエラー
			$ret = self::$_mainDb->getEntryByContentsId($this->contentType, $eventId, self::DEFAULT_ENTRY_TYPE/*受付タイプ*/, $row);
			if ($ret){
				$this->setAppErrorMsg('既に受付イベントが作成されています');
				$eventId = '';			// イベントIDをリセット
			}
			
			$this->serialNo = 0;
			$reloadData = true;		// データの再読み込み
		} else if ($act == 'add'){		// メッセージを追加
			// 入力チェック
			// 期間範囲のチェック
			if (!empty($start_date) && !empty($end_date)){
				if (strtotime($start_date . ' ' . $start_time) >= strtotime($end_date . ' ' . $end_time)) $this->setUserErrorMsg('受付期間が不正です');
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

				// 追加パラメータ
				$otherParams = array(
										'et_code'			=> $eventCode,
										'et_html'			=> $html,
										'et_status'			=> $this->status,
										'et_start_dt'		=> $startDt,
										'et_end_dt'			=> $endDt
									);
				$ret = self::$_mainDb->addEntry($this->contentType, $eventId, self::DEFAULT_ENTRY_TYPE/*受付タイプ*/, $otherParams, $newSerial);
				if ($ret){
					$this->setGuidanceMsg('データを追加しました');
					
					// シリアル番号更新
					$this->serialNo = $newSerial;
					$reloadData = true;		// データの再読み込み
					
					// 親ウィンドウを更新
					$this->gPage->updateParentWindow($this->serialNo);
					
					// 運用ログを残す
					$statusStr = '';
					$ret = self::$_mainDb->getEntryBySerial($this->serialNo, $row);
					if ($ret){
						$entryId	= $row['et_contents_id'];	// コンテンツID
						$updateDt	= $row['et_create_dt'];		// 作成日時
						
						// 公開状態
						$statusStr = $this->_getStatusLabel($row['et_status']);

						// イベント情報を取得
						$ret = $this->contentObj->getEntry($this->_langId, $entryId, $row);
						if ($ret){
							$eventName	= $row['ee_name'];			// イベント名
						}
					}
					$eventParam = array(	M3_EVENT_HOOK_PARAM_CONTENT_TYPE	=> M3_VIEW_TYPE_EVENTENTRY,
											M3_EVENT_HOOK_PARAM_CONTENT_ID		=> $entryId,
											M3_EVENT_HOOK_PARAM_UPDATE_DT		=> $updateDt);
					$this->writeUserInfoEvent(__METHOD__, '受付イベントを追加(' . $statusStr . ')しました。タイトル: ' . $eventName, 2400, 'イベントID=' . $entryId, $eventParam);
				} else {
					$this->setAppErrorMsg('データ追加に失敗しました');
				}
			}
		} else if ($act == 'update'){		// 項目更新の場合
			// 入力チェック
			// 期間範囲のチェック
			if (!empty($start_date) && !empty($end_date)){
				if (strtotime($start_date . ' ' . $start_time) >= strtotime($end_date . ' ' . $end_time)) $this->setUserErrorMsg('受付期間が不正です');
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
				
				// 追加パラメータ
				$otherParams = array(
										'et_code'			=> $eventCode,
										'et_html'			=> $html,
										'et_status'			=> $this->status,
										'et_start_dt'		=> $startDt,
										'et_end_dt'			=> $endDt
									);
				$ret = self::$_mainDb->updateEntry($this->serialNo, $otherParams, $newSerial);
				if ($ret){
					$this->setGuidanceMsg('データを更新しました');
					
					// シリアル番号更新
					$this->serialNo = $newSerial;
					$reloadData = true;		// データの再読み込み
					
					// 親ウィンドウを更新
					$this->gPage->updateParentWindow($this->serialNo);
					
					// 運用ログを残す
					$statusStr = '';
					$ret = self::$_mainDb->getEntryBySerial($this->serialNo, $row);
					if ($ret){
						$entryId	= $row['et_contents_id'];	// コンテンツID
						$updateDt	= $row['et_create_dt'];		// 作成日時
						
						// 公開状態
						$statusStr = $this->_getStatusLabel($row['et_status']);

						// イベント情報を取得
						$ret = $this->contentObj->getEntry($this->_langId, $entryId, $row);
						if ($ret){
							$eventName	= $row['ee_name'];			// イベント名
						}
					}
					$eventParam = array(	M3_EVENT_HOOK_PARAM_CONTENT_TYPE	=> M3_VIEW_TYPE_EVENTENTRY,
											M3_EVENT_HOOK_PARAM_CONTENT_ID		=> $entryId,
											M3_EVENT_HOOK_PARAM_UPDATE_DT		=> $updateDt);
					$this->writeUserInfoEvent(__METHOD__, '受付イベントを更新(' . $statusStr . ')しました。タイトル: ' . $eventName, 2400, 'イベントID=' . $entryId, $eventParam);
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
				$ret = self::$_mainDb->delEventItem(array($this->serialNo));
				if ($ret){		// データ削除成功のとき
					$this->setGuidanceMsg('データを削除しました');
				} else {
					$this->setAppErrorMsg('データ削除に失敗しました');
				}
			}
		} else {	// 初期画面表示のとき
			$reloadData = true;		// データの再ロード
		}
		// 設定データを再取得
		if ($reloadData){		// データの再ロード
			$ret = self::$_mainDb->getEntryBySerial($this->serialNo, $row);
			if ($ret){
				$eventId = $row['et_contents_id'];		// イベントID
				$this->status = intval($row['et_status']);			// 状態(0=未設定、1=非公開、2=受付中、3=受付終了)

				$eventCode	= $row['et_code'];		// 受付イベントコード
				$start_date = $this->convertToDispDate($row['et_start_dt']);			// 受付期間開始日
				$start_time = $this->convertToDispTime($row['et_start_dt'], 1/*時分*/);	// 受付期間開始時間
				$end_date = $this->convertToDispDate($row['et_end_dt']);				// 受付期間終了日
				$end_time = $this->convertToDispTime($row['et_end_dt'], 1/*時分*/);		// 受付期間終了時間
//				$start_date	= $row['et_start_dt'];			// 受付期間開始日
//				$start_time	= $row['et_start_dt'];	// 受付期間開始時間
//				$end_date	= $row['et_end_dt'];				// 受付期間終了日
//				$end_time	= $row['et_end_dt'];		// 受付期間終了時間
				$html		= $row['et_html'];				// 説明
			} else {		// データ初期化
				$this->serialNo = 0;
				$this->status = 0;			// 状態(0=未設定、1=非公開、2=受付中、3=受付終了)

				$eventCode	= $this->_generateEventCode($eventId);		// 受付イベントコードを生成
				$start_date	= '';	// 受付期間開始日
				$start_time	= '';	// 受付期間開始時間
				$end_date	= '';		// 受付期間終了日
				$end_time	= '';		// 受付期間終了時間
				$html		= '';				// 説明
			}
			
			// イベント情報を取得
			$ret = $this->contentObj->getEntry($this->_langId, $eventId, $row);
			if ($ret){
				$eventId	= $row['ee_id'];
				$eventName	= $row['ee_name'];
				
				// イベント開催期間
				if ($row['ee_end_dt'] == $this->gEnv->getInitValueOfTimestamp()){		// // 期間終了がないとき
//					$startDtStr = $this->convertToDispDateTime($row['ee_start_dt'], 0/*ロングフォーマット*/, 10/*時分*/);
//					$endDtStr = '';
					$startDt = $row['ee_start_dt'];
					$endDt = '';
				} else {
//					$startDtStr = $this->convertToDispDateTime($row['ee_start_dt'], 0/*ロングフォーマット*/, 10/*時分*/);
//					$endDtStr = $this->convertToDispDateTime($row['ee_end_dt'], 0/*ロングフォーマット*/, 10/*時分*/);
					$startDt = $row['ee_start_dt'];
					$endDt = $row['ee_end_dt'];
				}
				
				// アイキャッチ画像
				$iconUrl = $this->contentObj->getEyecatchImageUrl($row['ee_thumb_filename'], 's'/*sサイズ画像*/);
				if (empty($fetchedRow['ee_thumb_filename'])){
					$iconTitle = 'アイキャッチ画像未設定';
				} else {
					$iconTitle = 'アイキャッチ画像';
				}
				$eyecatchImageTag = '<img src="' . $this->getUrl($iconUrl) . '" width="' . self::EYECATCH_IMAGE_SIZE . '" height="' . self::EYECATCH_IMAGE_SIZE . '" rel="m3help" alt="' . $iconTitle . '" title="' . $iconTitle . '" />';
			}
		}
		// 状態メニュー作成
		$this->createStatusMenu();

		// 入力フィールドの設定
		if (!empty($eventId)){			// イベントIDがない場合はエラー
			if (empty($this->serialNo)){		// 未登録データのとき
				// データ追加ボタン表示
				$this->tmpl->setAttribute('add_button', 'visibility', 'visible');
			} else {
				// データ更新、削除ボタン表示
				$this->tmpl->setAttribute('delete_button', 'visibility', 'visible');
				$this->tmpl->setAttribute('update_button', 'visibility', 'visible');
			}
		}
		
		// 表示項目を埋め込む
		$this->tmpl->addVar("_widget", "event_name", $this->convertToDispString($eventName));		// イベント名
		$this->tmpl->addVar("_widget", "event_id", $this->convertToDispString($eventId));		// イベントID
		$this->tmpl->addVar("_widget", "event_code", $this->convertToDispString($eventCode));		// 受付イベントコード
		$this->tmpl->addVar("_widget", "start_date", $start_date);	// 受付期間開始日
		$this->tmpl->addVar("_widget", "start_time", $start_time);	// 受付期間開始時間
		$this->tmpl->addVar("_widget", "end_date", $end_date);		// 受付期間終了日
		$this->tmpl->addVar("_widget", "end_time", $end_time);		// 受付期間終了時間
//		$this->tmpl->addVar("_widget", "start_date", $this->convertToDispDate($start_date));			// 受付期間開始日
//		$this->tmpl->addVar("_widget", "start_time", $this->convertToDispTime($start_date, 1/*時分*/));	// 受付期間開始時間
//		$this->tmpl->addVar("_widget", "end_date", $this->convertToDispDate($end_date));				// 受付期間終了日
//		$this->tmpl->addVar("_widget", "end_time", $this->convertToDispTime($end_time, 1/*時分*/));		// 受付期間終了時間
//		$this->tmpl->addVar("_widget", "date_start", $startDtStr);		// イベント開催日時(開始)
//		$this->tmpl->addVar("_widget", "date_end", $endDtStr);		// イベント開催日時(終了)
		$this->tmpl->addVar("_widget", "date_start", $this->convertToDispDateTime($startDt, 0/*ロングフォーマット*/, 10/*時分*/));		// イベント開催日時(開始)
		$this->tmpl->addVar("_widget", "date_end", $this->convertToDispDateTime($endDt, 0/*ロングフォーマット*/, 10/*時分*/));		// イベント開催日時(終了)
		$this->tmpl->addVar("_widget", "html", $html);		// 説明
		$this->tmpl->addVar("_widget", "eyecatch_image", $eyecatchImageTag);		// アイキャッチ画像
		
		// その他の項目
		$this->tmpl->addVar("_widget", "serial", $this->serialNo);	// シリアル番号
		$this->tmpl->addVar('_widget', 'calendar_img', $this->getUrl($this->gEnv->getRootUrl() . self::CALENDAR_ICON_FILE));	// カレンダーアイコン
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
		$serial = $fetchedRow['et_serial'];// シリアル番号

		// 公開状態
		$iconTitle = $this->_getStatusLabel($fetchedRow['et_status']);
		if ($fetchedRow['et_status'] == 2){		// コンテンツが公開状態のとき
			$iconUrl = $this->gEnv->getRootUrl() . self::ACTIVE_ICON_FILE;			// 公開中アイコン
//			$iconTitle = '公開中';
		} else {
			$iconUrl = $this->gEnv->getRootUrl() . self::INACTIVE_ICON_FILE;		// 非公開アイコン
//			$iconTitle = '非公開';
		}
		$statusImg = '<img src="' . $this->getUrl($iconUrl) . '" width="' . self::ICON_SIZE . '" height="' . self::ICON_SIZE . '" rel="m3help" alt="' . $iconTitle . '" title="' . $iconTitle . '" />';
		
		// イベント開催期間
		if ($fetchedRow['ee_end_dt'] == $this->gEnv->getInitValueOfTimestamp()){		// // 期間終了がないとき
			if ($isAllDay){		// 終日イベントのときは時間を表示しない
				$startDtStr = $this->convertToDispDate($fetchedRow['ee_start_dt']);
				$endDtStr = '';
			} else {
				$startDtStr = $this->convertToDispDateTime($fetchedRow['ee_start_dt'], 1/*ショートフォーマット*/, 10/*時分*/);
				$endDtStr = '';
			}
		} else {
			if ($isAllDay){		// 終日イベントのときは時間を表示しない
				$startDtStr = $this->convertToDispDate($fetchedRow['ee_start_dt']);
				$endDtStr = $this->convertToDispDate($fetchedRow['ee_end_dt']);
			} else {
				$startDtStr = $this->convertToDispDateTime($fetchedRow['ee_start_dt'], 1/*ショートフォーマット*/, 10/*時分*/);
				$endDtStr = $this->convertToDispDateTime($fetchedRow['ee_end_dt'], 1/*ショートフォーマット*/, 10/*時分*/);
			}
		}
		
		$row = array(
			'index'		=> $index,		// 項目番号
			'serial'	=> $serial,			// シリアル番号
			'name'		=> $this->convertToDispString($fetchedRow['ee_name']),		// イベント名
			'status_img' => $statusImg,													// 公開状況
			'date'		=> $startDtStr	// 開催日時
		);
		$this->tmpl->addVars('itemlist', $row);
		$this->tmpl->parseTemplate('itemlist', 'a');
		
		// 表示中項目のシリアル番号を保存
		$this->serialArray[] = $serial;
		return true;
	}
	/**
	 * 画像一覧データ作成処理コールバック
	 *
	 * @param object	$tmpl			テンプレートオブジェクト
	 * @param object	$request		任意パラメータ(HTTPリクエストオブジェクト)
	 * @param							なし
	 */
	function makeEventList($tmpl, $request)
	{
		$pageNo = $request->trimIntValueOf(M3_REQUEST_PARAM_PAGE_NO, '1');				// ページ番号
		
		// 検索条件
		$search_startDt = $request->trimValueOf('search_start');		// 検索範囲開始日付
		if (!empty($search_startDt)) $search_startDt = $this->convertToProperDate($search_startDt);
		$search_endDt = $request->trimValueOf('search_end');			// 検索範囲終了日付
		if (!empty($search_endDt)) $search_endDt = $this->convertToProperDate($search_endDt);
		$search_categoryId = $request->trimValueOf('search_category0');			// 検索カテゴリー
		$search_keyword = $request->trimValueOf('search_keyword');			// 検索キーワード
		
		// キーワード分割
		$keywords = $this->gInstance->getTextConvManager()->parseSearchKeyword($search_keyword);
			
		// カテゴリーを格納
		$category = array();
		if (!empty($search_categoryId)){		// 0以外の値を取得
			$category[] = $search_categoryId;
		}
		
		// 画像選択画面で使用
		$this->selectedItems = explode(',', $request->trimValueOf('items'));
		sort($this->selectedItems, SORT_NUMERIC);		// ID順にソート
			
		// 総数を取得
		$totalCount = $this->contentObj->searchEntryCount($this->_langId, $search_startDt, $search_endDt, $category, $keywords);

		// ページング計算
		$this->calcPageLink($pageNo, $totalCount, self::DEFAULT_LIST_COUNT);
		
		// #### 画像リストを作成 ####
		$this->contentObj->searchEntry($this->_langId, $search_startDt, $search_endDt, $category, $keywords, 0/*降順*/, self::DEFAULT_LIST_COUNT, $pageNo, array($this, 'eventListLoop'), $tmpl);
		if (empty($this->serialArray)) $tmpl->setAttribute('itemlist', 'visibility', 'hidden');// 項目がないときは、一覧を表示しない
		
		// ページングリンク作成
		$currentBaseUrl = '';		// POST用のリンク作成
		$pageLink = $this->createPageLink($pageNo, self::LINK_PAGE_COUNT, $currentBaseUrl, 'selEventPage($1);return false;');
		
		// メッセージ設定
		if (empty($this->serialArray)){
			$msg = '参加を受け付けるイベントが登録されていません。先にイベント情報を登録してください。';
		} else {
			$msg = '参加を受け付けるイベントを選択してください';
		}
		
		// 表示項目
		$itemsStr = $this->convertToDispString(implode($this->selectedItems, ','));
		$tmpl->addVar("_tmpl", "items_label", $itemsStr);	// 画像選択項目
		$tmpl->addVar("_tmpl", "msg", $this->convertToDispString($msg));	// 画像選択項目
		
		// 非表示項目
		$tmpl->addVar("_tmpl", "page_link", $pageLink);
		$tmpl->addVar("_tmpl", "page", $this->convertToDispString($pageNo));	// ページ番号
		$tmpl->addVar("_tmpl", "id_list", $this->convertToDispString(implode($this->idArray, ',')));		// 表示イベントのID
//		$tmpl->addVar("_tmpl", "items", $itemsStr);								// 選択中の画像
	}
	/**
	 * イベント状態選択タイプメニュー作成
	 *
	 * @return なし
	 */
	function createStatusMenu()
	{
		for ($i = 0; $i < count($this->statusTypeArray); $i++){
			$value = $this->statusTypeArray[$i]['value'];
			$name = $this->statusTypeArray[$i]['name'];
			$selected = '';
			if ($this->status == $value) $selected = 'selected';
			
			$row = array(
				'value'    => $value,			// タイプ値
				'name'     => $this->convertToDispString($name),			// タイプ名
				'selected' => $selected			// 選択中かどうか
			);
			$this->tmpl->addVars('status_list', $row);
			$this->tmpl->parseTemplate('status_list', 'a');
		}
	}
	/**
	 * 取得したデータをテンプレートに設定する
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $tmpl			テンプレートオブジェクト(画像選択データ用)
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function eventListLoop($index, $fetchedRow, $tmpl)
	{
		$serial = $fetchedRow['ee_serial'];// シリアル番号
		$id = $fetchedRow['ee_id'];// イベントID
		$isAllDay = $fetchedRow['ee_is_all_day'];			// 終日イベントかどうか
		
		// チェックボックス選択可否
		// 目的の受付イベントが作成されている場合はエラー
		$checkDisabled = '';
		$ret = self::$_mainDb->getEntryByContentsId($this->contentType, $id, self::DEFAULT_ENTRY_TYPE/*受付タイプ*/, $row);
		if ($ret) $checkDisabled = 'disabled';
		
		// 公開状態
		switch ($fetchedRow['ee_status']){
			case 1:	$status = '<font color="orange">編集中</font>';	break;
			case 2:	$status = '<font color="green">公開</font>';	break;
			case 3:	$status = '非公開';	break;
		}
		
		// イベント開催期間
		if ($fetchedRow['ee_end_dt'] == $this->gEnv->getInitValueOfTimestamp()){		// // 期間終了がないとき
			if ($isAllDay){		// 終日イベントのときは時間を表示しない
				$startDtStr = $this->convertToDispDate($fetchedRow['ee_start_dt']);
				$endDtStr = '';
			} else {
				$startDtStr = $this->convertToDispDateTime($fetchedRow['ee_start_dt'], 1/*ショートフォーマット*/, 10/*時分*/);
				$endDtStr = '';
			}
		} else {
			if ($isAllDay){		// 終日イベントのときは時間を表示しない
				$startDtStr = $this->convertToDispDate($fetchedRow['ee_start_dt']);
				$endDtStr = $this->convertToDispDate($fetchedRow['ee_end_dt']);
			} else {
				$startDtStr = $this->convertToDispDateTime($fetchedRow['ee_start_dt'], 1/*ショートフォーマット*/, 10/*時分*/);
				$endDtStr = $this->convertToDispDateTime($fetchedRow['ee_end_dt'], 1/*ショートフォーマット*/, 10/*時分*/);
			}
		}
		
		$isActive = false;		// 公開状態
		if ($fetchedRow['ee_status'] == 2) $isActive = true;// 表示可能
		
		if ($isActive){		// コンテンツが公開状態のとき
			$iconUrl = $this->gEnv->getRootUrl() . self::ACTIVE_ICON_FILE;			// 公開中アイコン
			$iconTitle = '公開中';
		} else {
			$iconUrl = $this->gEnv->getRootUrl() . self::INACTIVE_ICON_FILE;		// 非公開アイコン
			$iconTitle = '非公開';
		}
		$statusImg = '<img src="' . $this->getUrl($iconUrl) . '" width="' . self::ICON_SIZE . '" height="' . self::ICON_SIZE . '" rel="m3help" alt="' . $iconTitle . '" title="' . $iconTitle . '" />';
		
		// アイキャッチ画像
		$iconUrl = $this->contentObj->getEyecatchImageUrl($fetchedRow['ee_thumb_filename'], 's'/*sサイズ画像*/);
		if (empty($fetchedRow['ee_thumb_filename'])){
			$iconTitle = 'アイキャッチ画像未設定';
		} else {
			$iconTitle = 'アイキャッチ画像';
		}
		$eyecatchImageTag = '<img src="' . $this->getUrl($iconUrl) . '" width="' . self::EYECATCH_IMAGE_SIZE . '" height="' . self::EYECATCH_IMAGE_SIZE . '" rel="m3help" alt="' . $iconTitle . '" title="' . $iconTitle . '" />';

		// 場所
		$place = $this->getLabelText($fetchedRow['ee_place']);		// ラベル用文字列取得
		
		// 画像プレビュー用ボタンを作成
		$eventAttr = 'onclick="showPreview(\''. $id . '\', \'' . $name . '\', \'' . $type . '\', \'' . $this->getUrl($url) . '\', \'' . $width .'\', \'' . $height . '\');"';
		$previewButtonTag = $this->gDesign->createPreviewImageButton(''/*同画面*/, 'プレビュー', ''/*タグID*/, $eventAttr/*クリックイベント時処理*/);
		
		$row = array(
			'index' => $index,				// 項目インデックス
			'serial' => $serial,			// シリアル番号
			'id' => $this->convertToDispString($id),			// ID
			'name' => $this->convertToDispString($fetchedRow['ee_name']),		// 名前
			'eyecatch_image' => $eyecatchImageTag,									// アイキャッチ画像
			'status_img' => $statusImg,												// 公開状態
			'status' => $status,													// 公開状況
			'date_start' => $startDtStr,	// 開催日時
			'place' => $this->convertToDispString($place),	// 開催場所
			'preview_image_button'	=> $previewButtonTag,					// 画像プレビューボタン
			'check_disabled'	=> $checkDisabled			// チェックボックス選択可否
		);

		$tmpl->addVars('itemlist', $row);
		$tmpl->parseTemplate('itemlist', 'a');
		
		// 表示中項目のシリアル番号を保存
		$this->serialArray[] = $serial;
		$this->idArray[] = $id;
		return true;
	}
	/**
	 * 受付イベントコードを生成
	 *
	 * @param string $id	イベントID
	 * @return string		生成した受付イベントコード
	 */
	function _generateEventCode($id)
	{
		$code = self::EVENT_CODE_HEAD . sprintf("%0" . self::EVENT_CODE_LENGTH . "d", $id);
		return $code;
	}
	/**
	 * 状態表示ラベルテキスト取得
	 *
	 * @param int $status	状態
	 * @return string		$status
	 */
	function _getStatusLabel($status)
	{
		$statusStr = '取得失敗';
		switch ($status){
			case 1:	$statusStr = '未設定';	break;
			case 1:	$statusStr = '非公開';	break;
			case 2:	$statusStr = '受付中';	break;
			case 3:	$statusStr = '受付終了';	break;
		}
		return $statusStr;
	}
}
?>
