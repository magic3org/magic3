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
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/admin_event_mainBaseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() .	'/event_mainDb.php');
require_once($gEnvManager->getCommonPath() . '/valueCheck.php');

class admin_event_mainEntryWidgetContainer extends admin_event_mainBaseWidgetContainer
{
	private $serialNo;		// 選択中の項目のシリアル番号
	private $entryId;
	private $langId;		// 現在の選択言語
	private $serialArray = array();		// 表示されている項目シリアル番号
	private $categoryListData;		// 全記事カテゴリー
	private $categoryArray;			// 選択中の記事カテゴリー
	private $categoryCount;			// カテゴリ数
	private $isMultiLang;			// 多言語対応画面かどうか
	private $fieldValueArray;		// ユーザ定義フィールド入力値
	const ICON_SIZE = 32;		// アイコンのサイズ
	const EYECATCH_IMAGE_SIZE = 40;		// アイキャッチ画像サイズ
	const DEFAULT_LIST_COUNT = 20;			// 最大リスト表示数
	const LINK_PAGE_COUNT		= 20;			// リンクページ数
	const CATEGORY_NAME_SIZE = 20;			// カテゴリー名の最大文字列長
	const CALENDAR_ICON_FILE = '/images/system/calendar.png';		// カレンダーアイコン
	const ACTIVE_ICON_FILE = '/images/system/active32.png';			// 公開中アイコン
	const INACTIVE_ICON_FILE = '/images/system/inactive32.png';		// 非公開アイコン
	const SEARCH_ICON_FILE = '/images/system/search16.png';		// 検索用アイコン
	const FIELD_HEAD = 'item_';			// フィールド名の先頭文字列
		
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
	 * @return 								なし
	 */
	function _init($request)
	{
		// 初期値取得
		$this->isMultiLang = $this->gEnv->isMultiLanguageSite();			// 多言語対応画面かどうか
		
		// DB定義値取得
		$this->categoryCount = self::$_configArray[event_mainCommonDef::CF_CATEGORY_COUNT];			// カテゴリ数
		if (empty($this->categoryCount)) $this->categoryCount = event_mainCommonDef::DEFAULT_CATEGORY_COUNT;
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
		
		if ($task == 'entry_detail'){		// 詳細画面
			return 'admin_entry_detail.tmpl.html';
		} else {			// 一覧画面
			return 'admin_entry.tmpl.html';
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
		if ($task == 'entry_detail'){	// 詳細画面
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
		$this->langId = $request->trimValueOf('item_lang');				// 現在メニューで選択中の言語
		if (empty($this->langId)) $this->langId = $this->gEnv->getDefaultLanguage();			// 言語が選択されていないときは、デフォルト言語を設定
		
		// ##### 検索条件 #####
		$pageNo = $request->trimIntValueOf(M3_REQUEST_PARAM_PAGE_NO, '1');				// ページ番号
		
		// DBの保存設定値を取得
		$maxListCount = self::DEFAULT_LIST_COUNT;
		$serializedParam = $this->_db->getWidgetParam($this->_widgetId);
		if (!empty($serializedParam)){
			$dispInfo = unserialize($serializedParam);
			$maxListCount = $dispInfo->maxMemberListCountByAdmin;		// 会員リスト最大表示数
		}

		$search_startDt = $request->trimValueOf('search_start');		// 検索範囲開始日付
		if (!empty($search_startDt)) $search_startDt = $this->convertToProperDate($search_startDt);
		$search_endDt = $request->trimValueOf('search_end');			// 検索範囲終了日付
		if (!empty($search_endDt)) $search_endDt = $this->convertToProperDate($search_endDt);
		$search_categoryId = $request->trimValueOf('search_category0');			// 検索カテゴリー
		$search_keyword = $request->trimValueOf('search_keyword');			// 検索キーワード
		
		// カテゴリーを格納
		$this->categoryArray = array();
		if (!empty($search_categoryId)){		// 0以外の値を取得
			$this->categoryArray[] = $search_categoryId;
		}

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
				// 削除するイベント記事の情報を取得
				$delEntryInfo = array();
				for ($i = 0; $i < count($delItems); $i++){
					$ret = self::$_mainDb->getEntryBySerial($delItems[$i], $row, $categoryRow);
					if ($ret){
						$newInfoObj = new stdClass;
						$newInfoObj->entryId = $row['ee_id'];		// 記事ID
						$newInfoObj->name = $row['ee_name'];		// 記事タイトル
						$delEntryInfo[] = $newInfoObj;
					}
				}
				
				$ret = self::$_mainDb->delEntryItem($delItems);
				if ($ret){		// データ削除成功のとき
					$this->setGuidanceMsg('データを削除しました');
					
					// キャッシュデータのクリア
					for ($i = 0; $i < count($delItems); $i++){
						$this->clearCacheBySerial($delItems[$i]);
					}
					
					// 親ウィンドウを更新
					$this->gPage->updateParentWindow();
					
					// 運用ログを残す
					for ($i = 0; $i < count($delEntryInfo); $i++){
						$infoObj = $delEntryInfo[$i];
						$eventParam = array(	M3_EVENT_HOOK_PARAM_CONTENT_TYPE	=> M3_VIEW_TYPE_EVENT,
												M3_EVENT_HOOK_PARAM_CONTENT_ID		=> $infoObj->entryId,
												M3_EVENT_HOOK_PARAM_UPDATE_DT		=> date("Y/m/d H:i:s"));
						$this->writeUserInfoEvent(__METHOD__, 'イベント記事を削除しました。タイトル: ' . $infoObj->name, 2402, 'ID=' . $infoObj->entryId, $eventParam);
					}
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
		$totalCount = self::$_mainDb->getEntryItemCount($search_startDt, $endDt, $this->categoryArray, $search_keyword, $this->langId);

		// ページング計算
		$this->calcPageLink($pageNo, $totalCount, $maxListCount);
		
		// ページングリンク作成
		$pageLink = $this->createPageLink($pageNo, self::LINK_PAGE_COUNT, ''/*リンク作成用(未使用)*/, 'selpage($1);return false;');
		
		// 記事項目リストを取得
		self::$_mainDb->searchEntryItems($maxListCount, $pageNo, $search_startDt, $endDt, $this->categoryArray, $search_keyword, $this->langId, array($this, 'itemListLoop'));
		if (count($this->serialArray) <= 0) $this->tmpl->setAttribute('itemlist', 'visibility', 'hidden');// 投稿記事がないときは、一覧を表示しない
		
		// カテゴリーメニューを作成
		self::$_mainDb->getAllCategory($this->langId, $this->categoryListData);
		$this->createCategoryMenu(1);		// メニューは１つだけ表示
		
		// プレビュー用URL
		$previewUrl = $this->gEnv->getDefaultUrl() . '?' . M3_REQUEST_PARAM_PAGE_SUB_ID . '=' . $this->gEnv->getPageSubIdByContentType($this->gEnv->getDefaultPageId(), M3_VIEW_TYPE_EVENT);
		$previewUrl .= '&' . M3_REQUEST_PARAM_OPERATION_COMMAND . '=' . M3_REQUEST_CMD_PREVIEW;
		$this->tmpl->addVar('_widget', 'preview_url', $previewUrl);// プレビュー用URL(一般画面)
		
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
		$this->tmpl->addVar("_widget", "serial_list", implode($this->serialArray, ','));// 表示項目のシリアル番号を設定
		$this->tmpl->addVar("_widget", "page", $pageNo);	// ページ番号
		$this->tmpl->addVar("_widget", "list_count", $maxListCount);	// 一覧表示項目数
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
		$openBy = $request->trimValueOf(M3_REQUEST_PARAM_OPEN_BY);		// ウィンドウオープンタイプ
		$act = $request->trimValueOf('act');
		$this->langId = $request->trimValueOf('item_lang');				// 現在メニューで選択中の言語
		if (empty($this->langId)) $this->langId = $this->gEnv->getDefaultLanguage();			// 言語が選択されていないときは、デフォルト言語を設定
		$this->entryId = $request->trimValueOf('entryid');		// 記事エントリーID
		$this->serialNo = $request->trimValueOf('serial');		// 選択項目のシリアル番号
		if (empty($this->serialNo)) $this->serialNo = 0;
		$name = $request->trimValueOf('item_name');
		$html = $request->valueOf('item_html');
		$html2 = $request->valueOf('item_html2');
		if (strlen($html2) <= 10){ // IE6のときFCKEditorのバグの対応(「続き」が空の場合でもpタグが送信される)
			$html2 = '';
		}
		$summary = $request->trimValueOf('item_summary');		// 要約
		$place = $request->trimValueOf('item_place');		// 場所
		$contact = $request->trimValueOf('item_contact');		// 連絡先
		$url = $request->trimValueOf('item_url');		// URL
		$status = $request->trimValueOf('item_status');		// エントリー状態(0=未設定、1=編集中、2=公開、3=非公開)
		$category = '';									// カテゴリー
		$isAllDay = $request->trimCheckedValueOf('item_is_all_day');			// 終日イベントかどうか
		$relatedContent = $request->trimValueOf('item_related_content');	// 関連コンテンツ
		
		// カテゴリーを取得
		$this->categoryArray = array();
		for ($i = 0; $i < $this->categoryCount; $i++){
			$itemName = 'item_category' . $i;
			$itemValue = $request->trimValueOf($itemName);
			if (!empty($itemValue)){		// 0以外の値を取得
				$this->categoryArray[] = $itemValue;
			}
		}

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
		
		$reloadData = false;		// データの再ロード
		if ($act == 'select'){		// 一覧から選択のとき
			$reloadData = true;		// データの再ロード
		} else if ($act == 'selectlang'){		// 項目選択の場合
			// 登録済みのコンテンツデータを取得
			$this->serialNo = self::$_mainDb->getEntrySerialNoByContentId($this->entryId, $this->langId);
			if (empty($this->serialNo)){
				// 取得できないときは一部初期化
				//$name = '';				// タイトル
				//$html = '';				// HTML
				$status = 0;				// エントリー状況
				$update_user = '';// 更新者
				$update_dt = '';							
			} else {
				$reloadData = true;		// データの再ロード
			}
		} else if ($act == 'add' || $act == 'addlang'){		// 項目追加の場合
			// 入力チェック
			$this->checkInput($name, 'タイトル');
			$this->checkDate($start_date, '開始日付');
			$this->checkTime($start_time, '開始時間');
					
			// 期間範囲のチェック
			if (!empty($start_date) && !empty($end_date)){
				if (strtotime($start_date . ' ' . $start_time) >= strtotime($end_date . ' ' . $end_time)) $this->setUserErrorMsg('開催期間が不正です');
			}
			
			// 関連コンテンツのチェック
			if (!empty($relatedContent)){
				$contentIdArray = explode(',', $relatedContent);
				if (!ValueCheck::isNumeric($contentIdArray)) $this->setUserErrorMsg('関連コンテンツにエラー値があります');// すべて数値であるかチェック
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
				
				// サムネール画像を取得
				$thumbFilename = '';
				if (($this->isMultiLang && $this->langId == $this->gEnv->getDefaultLanguage()) || !$this->isMultiLang){		// // 多言語対応の場合はデフォルト言語が選択されている場合のみ処理を行う
					// 次の記事IDを取得
					$nextEntryId = self::$_mainDb->getNextEntryId();
				
					if ($status == 2){		// 記事公開の場合のみアイキャッチ画像を作成
						$thumbPath = $this->gInstance->getImageManager()->getFirstImagePath($html);
						if (empty($thumbPath) && !empty($html2)) $thumbPath = $this->gInstance->getImageManager()->getFirstImagePath($html2);		// 本文1に画像がないときは本文2を検索
						if (!empty($thumbPath)){
							$ret = $this->gInstance->getImageManager()->createSystemDefaultThumb(M3_VIEW_TYPE_EVENT, event_mainCommonDef::$_deviceType, $nextEntryId, $thumbPath, $destFilename);
							if ($ret) $thumbFilename = implode(';', $destFilename);
						}
					}
				}
				
				// 追加パラメータ
				$otherParams = array(
										'ee_thumb_filename'		=> $thumbFilename,		// サムネールファイル名
										'ee_related_content'	=> $relatedContent,		// 関連コンテンツ
										'ee_option_fields'		=> $this->serializeArray($this->fieldValueArray),		// ユーザ定義フィールド値
										'ee_summary'			=> $summary,			// 概要
										'ee_place'				=> $place,
										'ee_contact'			=> $contact,
										'ee_url'				=> $url,
										'ee_is_all_day'			=> $isAllDay,
										'ee_start_dt'			=> $startDt,
										'ee_end_dt'				=> $endDt
									);
				
				// 記事データを追加
				if (($this->isMultiLang && $this->langId == $this->gEnv->getDefaultLanguage()) || !$this->isMultiLang){		// 多言語でデフォルト言語、または単一言語のとき
					$ret = self::$_mainDb->addEntryItem($nextEntryId * (-1)/*次のコンテンツIDのチェック*/, $this->langId, $name, $html, $html2, $status, $this->categoryArray, $otherParams, $newSerial);
				} else {
					$ret = self::$_mainDb->addEntryItem($this->entryId, $this->langId, $name, $html, $html2, $status, $this->categoryArray, $otherParams, $newSerial);
				}
				if ($ret){
					$this->setGuidanceMsg('データを追加しました');
					// シリアル番号更新
					$this->serialNo = $newSerial;
					$reloadData = true;		// データの再ロード
					
					// キャッシュデータのクリア
					$this->clearCacheBySerial($this->serialNo);
					
					// 親ウィンドウを更新
					$this->gPage->updateParentWindow();
					
					// 運用ログを残す
					$statusStr = '';
					$ret = self::$_mainDb->getEntryBySerial($this->serialNo, $row, $categoryRow);
					if ($ret){
						$this->entryId = $row['ee_id'];		// 記事ID
						$name = $row['ee_name'];		// コンテンツ名前
						$updateDt = $row['ee_create_dt'];		// 作成日時
						
						// 公開状態
						switch ($row['ee_status']){
							case 1:	$statusStr = '編集中';	break;
							case 2:	$statusStr = '公開';	break;
							case 3:	$statusStr = '非公開';	break;
						}
					}
					$eventParam = array(	M3_EVENT_HOOK_PARAM_CONTENT_TYPE	=> M3_VIEW_TYPE_EVENT,
											M3_EVENT_HOOK_PARAM_CONTENT_ID		=> $this->entryId,
											M3_EVENT_HOOK_PARAM_UPDATE_DT		=> $updateDt);
					$this->writeUserInfoEvent(__METHOD__, 'イベント記事を追加(' . $statusStr . ')しました。タイトル: ' . $name, 2400, 'ID=' . $this->entryId, $eventParam);
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
			
			// 関連コンテンツのチェック
			if (!empty($relatedContent)){
				$contentIdArray = explode(',', $relatedContent);
				if (!ValueCheck::isNumeric($contentIdArray)) $this->setUserErrorMsg('関連コンテンツにエラー値があります');// すべて数値であるかチェック
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
				
				// サムネール画像を取得
				$thumbFilename = '';
				if (($this->isMultiLang && $this->langId == $this->gEnv->getDefaultLanguage()) || !$this->isMultiLang){		// // 多言語対応の場合はデフォルト言語が選択されている場合のみ処理を行う
					if ($status == 2){		// 記事公開の場合のみアイキャッチ画像を作成
						// コンテンツからアイキャッチ画像を作成
						$thumbPath = $this->gInstance->getImageManager()->getFirstImagePath($html);
						if (empty($thumbPath) && !empty($html2)) $thumbPath = $this->gInstance->getImageManager()->getFirstImagePath($html2);		// 本文1に画像がないときは本文2を検索
						if (!empty($thumbPath)){
							$ret = $this->gInstance->getImageManager()->createSystemDefaultThumb(M3_VIEW_TYPE_EVENT, event_mainCommonDef::$_deviceType, $this->entryId, $thumbPath, $destFilename);
							if ($ret) $thumbFilename = implode(';', $destFilename);
						}
					
						// 非公開ディレクトリのアイキャッチ画像をコピー
						$ret = event_mainCommonDef::copyEyecatchImageToPublicDir($this->entryId);
						if ($ret){			// アイキャッチ画像をコピーした場合は、ファイル名を取得
							// 画像ファイル名、フォーマット取得
							list($destFilename, $formats) = $this->gInstance->getImageManager()->getSystemThumbFilename($this->entryId, 1/*クロップ画像のみ*/);
							$thumbFilename = implode(';', $destFilename);
						}
					} else {		// 記事非公開の場合
						// 公開ディレクトリのアイキャッチ画像を削除
						event_mainCommonDef::removerEyecatchImageInPublicDir($this->entryId);
					}
				}
				
				// 追加パラメータ
				$otherParams = array(
										'ee_thumb_filename'		=> $thumbFilename,		// サムネールファイル名
										'ee_related_content'	=> $relatedContent,		// 関連コンテンツ
										'ee_option_fields'		=> $this->serializeArray($this->fieldValueArray),		// ユーザ定義フィールド値
										'ee_summary'			=> $summary,			// 概要
										'ee_place'				=> $place,
										'ee_contact'			=> $contact,
										'ee_url'				=> $url,
										'ee_is_all_day'			=> $isAllDay,
										'ee_start_dt'			=> $startDt,
										'ee_end_dt'				=> $endDt
									);
									
				// 記事データを更新
				$ret = self::$_mainDb->updateEntryItem($this->serialNo, $name, $html, $html2, $status, $this->categoryArray, $otherParams, $newSerial);
				if ($ret){
					$this->setGuidanceMsg('データを更新しました');
					// シリアル番号更新
					$this->serialNo = $newSerial;
					$reloadData = true;		// データの再ロード
					
					// キャッシュデータのクリア
					$this->clearCacheBySerial($this->serialNo);
					
					// 親ウィンドウを更新
					$this->gPage->updateParentWindow();
					
					// 運用ログを残す
					$statusStr = '';
					$ret = self::$_mainDb->getEntryBySerial($this->serialNo, $row, $categoryRow);
					if ($ret){
						$this->entryId = $row['ee_id'];		// 記事ID
						$name = $row['ee_name'];		// コンテンツ名前
						$updateDt = $row['ee_create_dt'];		// 作成日時
						
						// 公開状態
						switch ($row['ee_status']){
							case 1:	$statusStr = '編集中';	break;
							case 2:	$statusStr = '公開';	break;
							case 3:	$statusStr = '非公開';	break;
						}
					}
					$eventParam = array(	M3_EVENT_HOOK_PARAM_CONTENT_TYPE	=> M3_VIEW_TYPE_EVENT,
											M3_EVENT_HOOK_PARAM_CONTENT_ID		=> $this->entryId,
											M3_EVENT_HOOK_PARAM_UPDATE_DT		=> $updateDt);
					$this->writeUserInfoEvent(__METHOD__, 'イベント記事を更新(' . $statusStr . ')しました。タイトル: ' . $name, 2401, 'ID=' . $this->entryId, $eventParam);
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
				// 削除するイベント記事の情報を取得
				$ret = self::$_mainDb->getEntryBySerial($this->serialNo, $row, $categoryRow);
				if ($ret){
					$this->entryId = $row['ee_id'];		// 記事ID
					$name = $row['ee_name'];		// コンテンツ名前
				}
				
				$ret = self::$_mainDb->delEntryItem(array($this->serialNo));
				if ($ret){		// データ削除成功のとき
					$this->setGuidanceMsg('データを削除しました');
					
					// キャッシュデータのクリア
					$this->clearCacheBySerial($this->serialNo);
					
					// 親ウィンドウを更新
					$this->gPage->updateParentWindow();
					
					// 運用ログを残す
					$eventParam = array(	M3_EVENT_HOOK_PARAM_CONTENT_TYPE	=> M3_VIEW_TYPE_EVENT,
											M3_EVENT_HOOK_PARAM_CONTENT_ID		=> $this->entryId,
											M3_EVENT_HOOK_PARAM_UPDATE_DT		=> date("Y/m/d H:i:s"));
					$this->writeUserInfoEvent(__METHOD__, 'イベント記事を削除しました。タイトル: ' . $name, 2402, 'ID=' . $this->entryId, $eventParam);
				} else {
					$this->setAppErrorMsg('データ削除に失敗しました');
				}
			}
		} else if ($act == 'deleteid'){		// ID項目削除の場合
			if (empty($this->serialNo)){
				$this->setUserErrorMsg('削除項目が選択されていません');
			}
			// エラーなしの場合は、データを削除
			if ($this->getMsgCount() == 0){
				// 削除するイベント記事の情報を取得
				$ret = self::$_mainDb->getEntryBySerial($this->serialNo, $row, $categoryRow);
				if ($ret){
					$this->entryId = $row['ee_id'];		// 記事ID
					$name = $row['ee_name'];		// コンテンツ名前
				}
				
				$ret = self::$_mainDb->delEntryItemById($this->serialNo);
				if ($ret){		// データ削除成功のとき
					$this->setGuidanceMsg('データを削除しました');
					
					// キャッシュデータのクリア
					$this->clearCacheBySerial($this->serialNo);
					
					// 親ウィンドウを更新
					$this->gPage->updateParentWindow();
					
					// 運用ログを残す
					$eventParam = array(	M3_EVENT_HOOK_PARAM_CONTENT_TYPE	=> M3_VIEW_TYPE_EVENT,
											M3_EVENT_HOOK_PARAM_CONTENT_ID		=> $this->entryId,
											M3_EVENT_HOOK_PARAM_UPDATE_DT		=> date("Y/m/d H:i:s"));
					$this->writeUserInfoEvent(__METHOD__, 'イベント記事を削除しました。タイトル: ' . $name, 2402, 'ID=' . $this->entryId, $eventParam);
				} else {
					$this->setAppErrorMsg('データ削除に失敗しました');
				}
			}
		} else {	// 初期画面表示のとき
			// 初期値設定
			// 所属イベントIDは親ウィンドウから引き継ぐ
			$start_date = date("Y/m/d");		// 開催日付
			$start_time = date("H:i:s");		// 開催時間
			$isAllDay = 0;			// 終日イベントかどうか
			$reloadData = true;		// データの再ロード
		}
		
		// 設定データを再取得
		if ($reloadData){		// データの再ロード
			$ret = self::$_mainDb->getEntryBySerial($this->serialNo, $row, $categoryRow);
			if ($ret){
				$this->entryId = $row['ee_id'];		// 記事ID
				$name = $row['ee_name'];				// タイトル
				$html = $row['ee_html'];				// HTML
				$html = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $this->getUrl($this->gEnv->getRootUrl()), $html);// アプリケーションルートを変換
				$html2 = $row['ee_html_ext'];				// HTML
				$html2 = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $this->getUrl($this->gEnv->getRootUrl()), $html2);// アプリケーションルートを変換
				$summary = $row['ee_summary'];		// 要約
				$place = $row['ee_place'];		// 場所
				$contact = $row['ee_contact'];		// 連絡先
				$url = $row['ee_url'];		// URL
				$status = $row['ee_status'];				// エントリー状況
				$update_user = $row['lu_name'];// 更新者
				$update_dt = $this->convertToDispDateTime($row['ee_create_dt']);
				$start_date = $this->convertToDispDate($row['ee_start_dt']);	// 開催期間開始日
				$start_time = $this->convertToDispTime($row['ee_start_dt'], 1/*時分*/);	// 開催期間開始時間
				$end_date = $this->convertToDispDate($row['ee_end_dt']);	// 開催期間終了日
				$end_time = $this->convertToDispTime($row['ee_end_dt'], 1/*時分*/);	// 開催期間終了時間
				$isAllDay = $row['ee_is_all_day'];			// 終日イベントかどうか
				$relatedContent = $row['ee_related_content'];		// 関連コンテンツ
				
				// 記事カテゴリー取得
				$this->categoryArray = $this->getCategory($categoryRow);
			} else {
				$this->entryId = 0;		// 記事ID
				$name = '';				// タイトル
				$html = '';				// HTML
				$html2 = '';				// HTML
				$summary = '';		// 要約
				$place = '';		// 場所
				$contact = '';		// 連絡先
				$url = '';		// URL
				$status = 0;				// エントリー状況
				$update_user = '';// 更新者
				$update_dt = '';
				$start_date = '';	// 開催期間開始日
				$start_time = '';	// 開催期間開始時間
				$end_date = '';	// 開催期間終了日
				$end_time = '';	// 開催期間終了時間
				$isAllDay = 0;			// 終日イベントかどうか
				$relatedContent = '';		// 関連コンテンツ
				
				// 記事カテゴリー取得
				$this->categoryArray = array();
				
				// ユーザ定義フィールド
				$this->fieldValueArray = array();
			}
		}
		// カテゴリーメニューを作成
		self::$_mainDb->getAllCategory($this->langId, $this->categoryListData);
		$this->createCategoryMenu($this->categoryCount);
		
		// プレビュー用URL
		$previewUrl = $this->gEnv->getDefaultUrl() . '?' . M3_REQUEST_PARAM_EVENT_ID . '=' . $this->entryId;
		$previewUrl .= '&' . M3_REQUEST_PARAM_OPERATION_COMMAND . '=' . M3_REQUEST_CMD_PREVIEW;
		$this->tmpl->addVar('_widget', 'preview_url', $previewUrl);// プレビュー用URL(一般画面)
		
		// ### 入力値を再設定 ###
		$this->tmpl->addVar('_widget', 'entry', $this->entryId);
		$this->tmpl->addVar("_widget", "name", $this->convertToDispString($name));		// 名前
		$this->tmpl->addVar("_widget", "item_html", $html);		// HTML
		$this->tmpl->addVar("_widget", "item_html2", $html2);		// HTML(続き)
		$this->tmpl->addVar("_widget", "summary", $this->convertToDispString($summary));		// 要約
		$this->tmpl->addVar("_widget", "place", $this->convertToDispString($place));		// 場所
		$this->tmpl->addVar("_widget", "contact", $this->convertToDispString($contact));		// 連絡先
		$this->tmpl->addVar("_widget", "url", $this->convertToDispString($url));		// URL
		switch ($status){
			case 1:	$this->tmpl->addVar("_widget", "selected_edit", 'selected');	break;
			case 2:	$this->tmpl->addVar("_widget", "selected_public", 'selected');	break;
			case 3:	$this->tmpl->addVar("_widget", "selected_closed", 'selected');	break;
		}	
		$this->tmpl->addVar("_widget", "start_date", $start_date);	// 開催日付
		$this->tmpl->addVar("_widget", "start_time", $start_time);	// 開催時間
		$this->tmpl->addVar("_widget", "update_user", $this->convertToDispString($update_user));	// 更新者
		$this->tmpl->addVar("_widget", "update_dt", $update_dt);	// 更新日時
		$this->tmpl->addVar("_widget", "start_date", $start_date);	// 公開期間開始日
		$this->tmpl->addVar("_widget", "start_time", $start_time);	// 公開期間開始時間
		$this->tmpl->addVar("_widget", "end_date", $end_date);	// 公開期間終了日
		$this->tmpl->addVar("_widget", "end_time", $end_time);	// 公開期間終了時間
		$this->tmpl->addVar("_widget", "is_all_day", $this->convertToCheckedString($isAllDay));// 終日イベントかどうか
		$this->tmpl->addVar("_widget", "related_content", $relatedContent);	// 関連コンテンツ
		$this->tmpl->addVar("_widget", "eyecatch_image", $eyecatchImageTag);		// アイキャッチ画像
		
		// 非表示項目を設定
		$this->tmpl->addVar("_widget", "serial", $this->serialNo);	// シリアル番号

		// 入力フィールドの設定、共通項目のデータ設定
		if ($this->entryId == 0){		// 記事IDが0のときは、新規追加モードにする
			$this->tmpl->addVar('_widget', 'id', '新規');
			
			$this->tmpl->setAttribute('add_button', 'visibility', 'visible');
			
			// デフォルト言語を最初に登録
//			$this->tmpl->addVar("default_lang", "default_lang", $defaultLangName);
//			$this->tmpl->setAttribute('default_lang', 'visibility', 'visible');
			$this->tmpl->addVar('_widget', 'preview_btn_disabled', 'disabled');// プレビューボタン使用不可
		} else {
			$this->tmpl->addVar('_widget', 'id', $this->entryId);
			
			if ($this->serialNo == 0){		// 未登録データのとき
				// データ追加ボタン表示
				$this->tmpl->setAttribute('add_button', 'visibility', 'visible');
			} else {
				// データ更新、削除ボタン表示
				$this->tmpl->setAttribute('delete_button', 'visibility', 'visible');// デフォルト言語以外はデータ削除
				$this->tmpl->setAttribute('update_button', 'visibility', 'visible');
			}
			// 言語選択メニュー作成
			//if (!empty($this->entryId)){	// コンテンツが選択されているとき
			//	self::$_mainDb->getAllLang(array($this, 'langLoop'));
			//	$this->tmpl->setAttribute('select_lang', 'visibility', 'visible');
			//}
		}

		// パス等を設定
		$this->tmpl->addVar('_widget', 'calendar_img', $this->getUrl($this->gEnv->getRootUrl() . self::CALENDAR_ICON_FILE));	// カレンダーアイコン
		
		// 閉じるボタンの表示制御
		if ($openBy == 'simple') $this->tmpl->setAttribute('cancel_button', 'visibility', 'hidden');		// 詳細画面のみの表示のときは戻るボタンを隠す
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
		$serial = $fetchedRow['ee_serial'];// シリアル番号
		$isAllDay = $fetchedRow['ee_is_all_day'];			// 終日イベントかどうか
		
		// カテゴリーを取得
		$categoryArray = array();
		$ret = self::$_mainDb->getEntryBySerial($serial, $row, $categoryRow);
		if ($ret){
			for ($i = 0; $i < count($categoryRow); $i++){
				if (function_exists('mb_strimwidth')){
					$categoryArray[] = mb_strimwidth($categoryRow[$i]['ec_name'], 0, self::CATEGORY_NAME_SIZE, '…');
				} else {
					$categoryArray[] = substr($categoryRow[$i]['ec_name'], 0, self::CATEGORY_NAME_SIZE) . '...';
				}
			}
		}
		$category = implode(',', $categoryArray);
		
		// 公開状態
		switch ($fetchedRow['ee_status']){
			case 1:	$status = '<font color="orange">編集中</font>';	break;
			case 2:	$status = '<font color="green">公開</font>';	break;
			case 3:	$status = '非公開';	break;
		}
		// 総参照数
		$totalViewCount = $this->gInstance->getAnalyzeManager()->getTotalContentViewCount(event_mainCommonDef::VIEW_CONTENT_TYPE, $serial);
		
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
		$iconUrl = event_mainCommonDef::getEyecatchImageUrl($fetchedRow['ee_thumb_filename'], self::$_configArray[event_mainCommonDef::CF_ENTRY_DEFAULT_IMAGE], self::$_configArray[event_mainCommonDef::CF_THUMB_TYPE], 's'/*sサイズ画像*/) . '?' . date('YmdHis');
		if (empty($fetchedRow['ee_thumb_filename'])){
			$iconTitle = 'アイキャッチ画像未設定';
		} else {
			$iconTitle = 'アイキャッチ画像';
		}
		$eyecatchImageTag = '<img src="' . $this->getUrl($iconUrl) . '" width="' . self::EYECATCH_IMAGE_SIZE . '" height="' . self::EYECATCH_IMAGE_SIZE . '" rel="m3help" alt="' . $iconTitle . '" title="' . $iconTitle . '" />';

		$row = array(
			'index' => $index,		// 項目番号
			'no' => $index + 1,													// 行番号
			'serial' => $serial,			// シリアル番号
			'id' => $this->convertToDispString($fetchedRow['ee_id']),			// ID
			'name' => $this->convertToDispString($fetchedRow['ee_name']),		// 名前
			'lang' => $lang,													// 対応言語
			'eyecatch_image' => $eyecatchImageTag,									// アイキャッチ画像
			'status_img' => $statusImg,												// 公開状態
			'status' => $status,													// 公開状況
			'category' => $category,											// 記事カテゴリー
			'date_start' => $startDtStr,	// 開催日時
			'date_end' => $endDtStr,	// 開催日時
			'place' => $this->convertToDispString($fetchedRow['ee_place']),	// 開催場所
			'view_count' => $totalViewCount,									// 総参照数
			'update_user' => $this->convertToDispString($fetchedRow['lu_name']),	// 更新者
			'update_date' => $this->convertToDispDateTime($fetchedRow['ee_create_dt'])	// 更新日時
		);
		$this->tmpl->addVars('itemlist', $row);
		$this->tmpl->parseTemplate('itemlist', 'a');
		
		// 表示中項目のシリアル番号を保存
		$this->serialArray[] = $serial;
		return true;
	}
	/**
	 * 取得した言語をテンプレートに設定する
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function langLoop($index, $fetchedRow, $param)
	{
		$selected = '';
		if ($fetchedRow['ln_id'] == $this->langId){
			$selected = 'selected';
		}
		if ($this->gEnv->getCurrentLanguage() == 'ja'){		// 日本語表示の場合
			$name = $this->convertToDispString($fetchedRow['ln_name']);
		} else {
			$name = $this->convertToDispString($fetchedRow['ln_name_en']);
		}

		$row = array(
			'value'    => $this->convertToDispString($fetchedRow['ln_id']),			// 言語ID
			'name'     => $name,			// 言語名
			'selected' => $selected														// 選択中かどうか
		);
		$this->tmpl->addVars('lang_list', $row);
		$this->tmpl->parseTemplate('lang_list', 'a');
		return true;
	}
	/**
	 * 記事カテゴリー取得
	 *
	 * @param array  	$srcRows			取得行
	 * @return array						取得した行
	 */
	function getCategory($srcRows)
	{
		$destArray = array();
		$itemCount = 0;
		for ($i = 0; $i < count($srcRows); $i++){
			if (!empty($srcRows[$i]['ew_category_id'])){
				$destArray[] = $srcRows[$i]['ew_category_id'];
				$itemCount++;
				if ($itemCount >= $this->categoryCount) break;
			}
		}
		return $destArray;
	}
	/**
	 * 記事カテゴリーメニューを作成
	 *
	 * @param int  	$size			メニューの表示数
	 * @return なし						
	 */
	function createCategoryMenu($size)
	{
		for ($j = 0; $j < $size; $j++){
			// selectメニューの作成
			$this->tmpl->clearTemplate('category_list');
			for ($i = 0; $i < count($this->categoryListData); $i++){
				$categoryId = $this->categoryListData[$i]['ec_id'];
				$selected = '';
				if ($j < count($this->categoryArray) && $this->categoryArray[$j] == $categoryId){
					$selected = 'selected';
				}
				$menurow = array(
					'value'		=> $categoryId,			// カテゴリーID
					'name'		=> $this->categoryListData[$i]['ec_name'],			// カテゴリー名
					'selected'	=> $selected														// 選択中かどうか
				);
				$this->tmpl->addVars('category_list', $menurow);
				$this->tmpl->parseTemplate('category_list', 'a');
			}
			$itemRow = array(		
					'index'		=> $j			// 項目番号											
			);
			$this->tmpl->addVars('category', $itemRow);
			$this->tmpl->parseTemplate('category', 'a');
		}
	}
	/**
	 * キャッシュデータをクリア
	 *
	 * @param int $serial		削除対象のコンテンツシリアル番号
	 * @return					なし
	 */
	function clearCacheBySerial($serial)
	{
		$ret = self::$_mainDb->getEntryBySerial($serial, $row, $categoryRow);// 記事ID取得
		if ($ret){
			$entryId = $row['ee_id'];		// 記事ID
			$urlParam = array();
			$urlParam[] = M3_REQUEST_PARAM_EVENT_ENTRY_ID . '=' . $entryId;		// 記事ID
			$urlParam[] = M3_REQUEST_PARAM_EVENT_ENTRY_ID_SHORT . '=' . $entryId;		// 記事ID略式
			$this->clearCache($urlParam);
		}
	}
}
?>
