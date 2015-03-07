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
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/event_mainBaseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/event_mainDb.php');
require_once($gEnvManager->getCurrentWidgetDbPath() .	'/event_mainCategoryDb.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/event_mainCommentDb.php');

class event_mainTopWidgetContainer extends event_mainBaseWidgetContainer
{
	private $categoryDb;	// DB接続オブジェクト
	private $commentDb;			// DB接続オブジェクト
	private $entryId;	// 記事ID
	private $startDt;
	private $endDt;
	private $pageNo;				// ページ番号
	private $now;	// 現在日時
	private $currentDay;		// 現在日
	private $currentHour;		// 現在時間
	// 表示制御
	private $isSystemManageUser;	// システム運用可能ユーザかどうか
	private $preview;				// プレビューモードかどうか
	private $outputHead;			// ヘッダ出力するかどうか
	private $useWidgetTitle;		// ウィジェットタイトルを使用するかどうか
	private $isExistsViewData;		// 表示データがあるかどうか
//	private $viewExtEntry;			// 結果を表示するかどうか
	// 表示項目
	private $entryViewCount;// 記事表示数
	private $entryViewOrder;// 記事表示順
	private $title;		// 表示タイトル
	private $widgetTitle;	// ウィジェットタイトル
	private $message;			// ユーザ向けメッセージ
	private $pageTitle;				// 画面タイトル、パンくずリスト用タイトル
	private $headTitle;		// METAタグタイトル
	private $headDesc;		// METAタグ要約
	private $headKeyword;	// METAタグキーワード
	private $addLib = array();		// 追加スクリプト
	private $viewMode;					// 表示モード
	private $editIconPos;			// 編集アイコンの位置
	private $avatarSize;		// アバター画像サイズ
	private $titleList;		// 一覧タイトル
	private $titleNoEntry;		// 記事なし時タイトル
	private $messageNoEntry;		// イベント記事が登録されていないメッセージ
	private $messageFindNoEntry;	// イベント記事が見つからないメッセージ
	private $startTitleTagLevel;	// 最初のタイトルタグレベル
	private $itemTagLevel;			// 記事のタイトルタグレベル
	// イベント情報追加分
	private $useCalendar;		// カレンダーを使用するかどうか
			
//	const CONTENT_TYPE = 'ev';
	const LINK_PAGE_COUNT		= 5;			// リンクページ数
	const ICON_SIZE = 32;		// アイコンのサイズ
	const EDIT_ICON_MIN_POS = 30;			// 編集アイコンの位置
	const EDIT_ICON_NEXT_POS = 35;			// 編集アイコンの位置
	const MESSAGE_EXT_ENTRY		= '結果を見る';					// イベント記事に結果がある場合の表示
	const MESSAGE_EXT_ENTRY_PRE	= '…&nbsp;';							// イベント記事に結果がある場合の表示
	const DEFAULT_TITLE_SEARCH = '検索';		// 検索時のデフォルトタイトル
	const TITLE_RELATED_CONTENT_BLOCK = '関連記事';		// 関連コンテンツブロック
	const EDIT_ICON_FILE = '/images/system/page_edit32.png';		// 編集アイコン
	const NEW_ICON_FILE = '/images/system/page_add32.png';		// 新規アイコン
	const COOKIE_LIB = 'jquery.cookie';		// 名前保存用クッキーライブラリ
	const ENTRY_BODY_BLOCK_CLASS = 'event_entry_body';		// 記事本文ブロックのCSSクラス
	const CATEGORY_BLOCK_CLASS = 'event_category_list';		// カテゴリーブロックのCSSクラス
	const CATEGORY_BLOCK_LABEL = 'カテゴリー：';		// カテゴリーブロックのラベル
	const CATEGORY_BLOCK_SEPARATOR = ', ';			// カテゴリーブロック内の区切り
	// イベント情報追加分
	const TASK_ADMIN_ENTRY_DETAIL = 'entry_detail';			// 記事編集画面詳細
	const ENTRY_RESULT_BLOCK_CLASS = 'event_entry_result';		// 記事結果ブロックのCSSクラス
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->categoryDb = new event_mainCategoryDb();
		$this->commentDb = new event_mainCommentDb();
		
		// 初期値設定
		$this->editIconPos = self::EDIT_ICON_MIN_POS;			// 編集アイコンの位置
		$this->outputHead = self::$_configArray[event_mainCommonDef::CF_OUTPUT_HEAD];			// ヘッダ出力するかどうか
		$this->useWidgetTitle = self::$_configArray[event_mainCommonDef::CF_USE_WIDGET_TITLE];		// ウィジェットタイトルを使用するかどうか

		$this->entryViewCount	= self::$_configArray[event_mainCommonDef::CF_ENTRY_VIEW_COUNT];// 記事表示数
		if (empty($this->entryViewCount)) $this->entryViewCount = event_mainCommonDef::DEFAULT_VIEW_COUNT;
		$this->entryViewOrder	= self::$_configArray[event_mainCommonDef::CF_ENTRY_VIEW_ORDER];// 記事表示順
		$this->title = self::$_configArray[event_mainCommonDef::CF_TITLE_DEFAULT];		// デフォルトタイトル
		$this->titleList = self::$_configArray[event_mainCommonDef::CF_TITLE_LIST];		// 一覧タイトル
		if (empty($this->titleList)) $this->titleList = event_mainCommonDef::DEFAULT_TITLE_LIST;
		$this->titleSearchList = self::$_configArray[event_mainCommonDef::CF_TITLE_SEARCH_LIST];		// 検索結果タイトル
		if (empty($this->titleSearchList)) $this->titleSearchList = event_mainCommonDef::DEFAULT_TITLE_SEARCH_LIST;
		$this->titleNoEntry = self::$_configArray[event_mainCommonDef::CF_TITLE_NO_ENTRY];		// 記事なし時タイトル
		if (empty($this->titleNoEntry)) $this->titleNoEntry = event_mainCommonDef::DEFAULT_TITLE_NO_ENTRY;
		$this->messageNoEntry = self::$_configArray[event_mainCommonDef::CF_MESSAGE_NO_ENTRY];		// イベント記事が登録されていないメッセージ
		if (empty($this->messageNoEntry)) $this->messageNoEntry = event_mainCommonDef::DEFAULT_MESSAGE_NO_ENTRY;
		$this->messageFindNoEntry = self::$_configArray[event_mainCommonDef::CF_MESSAGE_FIND_NO_ENTRY];		// イベント記事が見つからないメッセージ
		if (empty($this->messageFindNoEntry)) $this->messageFindNoEntry = event_mainCommonDef::DEFAULT_MESSAGE_FIND_NO_ENTRY;
		$this->startTitleTagLevel = self::$_configArray[event_mainCommonDef::CF_TITLE_TAG_LEVEL];	// 最初のタイトルタグレベル
		if (empty($this->startTitleTagLevel)) $this->startTitleTagLevel = event_mainCommonDef::DEFAULT_TITLE_TAG_LEVEL;
		// イベント情報追加分
		$this->useCalendar	= self::$_configArray[event_mainCommonDef::CF_USE_CALENDAR];		// カレンダーを使用するかどうか
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
		// 入力値取得
		$act = $request->trimValueOf('act');
		$this->entryId = $request->trimValueOf(M3_REQUEST_PARAM_EVENT_ID);
		if (empty($this->entryId)) $this->entryId = $request->trimValueOf(M3_REQUEST_PARAM_EVENT_ID_SHORT);		// 略式イベント記事ID
		
		if ($act == 'search'){
			$this->viewMode = 2;					// 表示モード(検索一覧表示)
			return 'list.tmpl.html';		// 検索結果一覧
		} else {
			$year = $request->trimValueOf('year');		// 年指定
			$month = $request->trimValueOf('month');		// 月指定
			$category = $request->trimValueOf(M3_REQUEST_PARAM_CATEGORY_ID);		// カテゴリID
			if (!empty($category) || !empty($year) || !empty($month)){
				$this->viewMode = 1;					// 表示モード(記事一覧表示)
				return 'list.tmpl.html';	// 記事一覧
			} else if (empty($this->entryId)){
				$this->viewMode = 0;					// 表示モード(トップ一覧表示)
				return 'list.tmpl.html';		// トップ画面記事一覧
			} else {
				$this->viewMode = 10;					// 表示モード(記事単体表示)
				if ($this->_renderType == M3_RENDER_BOOTSTRAP){
					return 'single_bootstrap.tmpl.html';		// 記事詳細
				} else {
					return 'single.tmpl.html';		// 記事詳細
				}
			}
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
		// 初期設定値
		$this->now = date("Y/m/d H:i:s");	// 現在日時
		$this->currentDay = date("Y/m/d");		// 日
		$this->currentHour = (int)date("H");		// 時間
//		$this->currentPageUrl = $this->gEnv->createCurrentPageUrl();// 現在のページURL
		$this->isSystemManageUser = $this->gEnv->isSystemManageUser();	// システム運用可能ユーザかどうか
		$this->pageTitle = '';		// 画面タイトル、パンくずリスト用タイトル
		
		// 入力値取得
		$act = $request->trimValueOf('act');
		$cmd = $request->trimValueOf(M3_REQUEST_PARAM_OPERATION_COMMAND);
		$this->pageNo = $request->trimIntValueOf('page', '1');				// ページ番号
		$this->startDt = $request->trimValueOf('start');
		$this->endDt = $request->trimValueOf('end');
		
		// 管理者でプレビューモードのときは表示制限しない
		$this->preview = false;
		if ($this->isSystemManageUser && $cmd == M3_REQUEST_CMD_PREVIEW){		// システム運用者以上
			$this->preview = true;
		}
		
		// 共通ボタン作成
		if (self::$_canEditEntry){		// 記事編集権限ありのとき
			$buttonList = $this->createButtonTag(0);// 新規作成ボタン
		} else {
			$buttonList = '';
		}
		
		// タイトルのタグレベル
		$this->itemTagLevel = $this->startTitleTagLevel;			// 記事のタイトルタグレベル
		switch ($this->viewMode){					// 表示モード
			case 0:			// トップ一覧表示
			default:
				if (!$this->useWidgetTitle && !empty($this->title)) $this->itemTagLevel++;
				break;
			case 1:			// 記事一覧表示
			case 2:			// 検索一覧表示
				if (!$this->useWidgetTitle) $this->itemTagLevel++;
				break;
			case 10:			// 記事単体表示
				break;
		}
		
		switch ($this->viewMode){					// 表示モード
			case 0:			// トップ一覧表示
			default:
				if (self::$_canEditEntry) $this->tmpl->setAttribute('show_script', 'visibility', 'visible');		// 編集機能表示
				$this->showTopList($request);
				break;
			case 1:			// 記事一覧表示
				if (self::$_canEditEntry) $this->tmpl->setAttribute('show_script', 'visibility', 'visible');		// 編集機能表示
				$this->showList($request);
				break;
			case 2:			// 検索一覧表示
				if (self::$_canEditEntry) $this->tmpl->setAttribute('show_script', 'visibility', 'visible');		// 編集機能表示
				$this->showSearchList($request);
				break;
			case 10:			// 記事単体表示
				$avatarFormat = $this->gInstance->getImageManager()->getDefaultAvatarFormat();		// 画像フォーマット取得
				$this->gInstance->getImageManager()->parseImageFormat($avatarFormat, $imageType, $imageAttr, $this->avatarSize);		// 画像情報取得
		
				$this->showSingle($request);		// 記事単体表示のとき
				break;
		}

		// HTMLサブタイトルを設定
		$this->gPage->setHeadSubTitle($this->pageTitle);
		
		// タイトルの設定
		if ($this->useWidgetTitle){			// ウィジェットタイトルを使用するとき
			if (!empty($this->title)) $this->widgetTitle = $this->title;	// ウィジェットタイトル
		} else {
			if (!empty($this->title)){
				$this->tmpl->setAttribute('show_title', 'visibility', 'visible');		// 年月表示
				$this->tmpl->addVar("show_title", "title", '<h' . $this->startTitleTagLevel . '>' . $this->convertToDispString($this->title) . '</h' . $this->startTitleTagLevel . '>');
			}
		}

		// メッセージを表示
		if (!empty($this->message)){
			$this->tmpl->setAttribute('message', 'visibility', 'visible');
			$this->tmpl->addVar("message", "message", $this->convertToDispString($this->message, true/*タグを残す*/));
		}
		
		// 運用可能ユーザの場合は編集用ボタンを表示
		if (self::$_canEditEntry){		// 記事編集権限ありのとき
			// 共通ボタン埋め込み
			$this->tmpl->setAttribute('button_list', 'visibility', 'visible');
			$this->tmpl->addVar("button_list", "button_list", $buttonList);
			
			if ($this->isSystemManageUser){		// 管理者権限ありのとき
				$editUrl = $this->getConfigAdminUrl('openby=simple&task=' . self::TASK_ADMIN_ENTRY_DETAIL);
				$configUrl = $this->getConfigAdminUrl('openby=other');
				$this->tmpl->setAttribute('admin_script', 'visibility', 'visible');
				$this->tmpl->addVar("admin_script", "edit_url", $editUrl);
				$this->tmpl->addVar("admin_script", "config_url", $configUrl);
			}
		}
		// 他画面へのリンク
		if ($this->useCalendar){
			$this->tmpl->setAttribute('top_link_area', 'visibility', 'visible');
			$topLink = $this->convertUrlToHtmlEntity($this->getUrl($this->_baseUrl . '&task=' . self::TASK_CALENDAR, true));
			$topName = 'カレンダー';
			$this->tmpl->addVar("top_link_area", "calendar_url", $topLink);
			$this->tmpl->addVar("top_link_area", "calendar_name", $topName);
		}
	}
	/**
	 * 記事単体表示
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param								なし
	 */
	function showSingle($request)
	{
		// 記事ID指定のときは記事を取得
		$entryRow = array();
		if (!empty($this->entryId)){
			$ret = self::$_mainDb->getEntryItem($this->entryId, $this->_langId, $entryRow);
			if ($ret){
				// ページのタイトル設定
				$this->title = $entryRow['ee_name'];
				$this->pageTitle = $this->title;
			}
		}
		
		// 記事表示
		self::$_mainDb->getEntryItems($this->entryViewCount, $this->pageNo, $this->now, $this->entryId, $this->startDt/*期間開始*/, $this->endDt/*期間終了*/,
										''/*検索キーワード*/, $this->_langId, $this->entryViewOrder, array($this, 'itemsLoop'), $this->preview);

		
		// イベント記事データがないときはデータなしメッセージ追加
		if (!$this->isExistsViewData){
			$this->title = $this->titleNoEntry;
			$this->message = $this->messageNoEntry;
		}
	}
	/**
	 * トップ一覧画面表示
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param								なし
	 */
	function showTopList($request)
	{
		// 総数を取得
		$totalCount = self::$_mainDb->getEntryItemsCount($this->now, $this->startDt, $this->endDt, ''/*検索キーワード*/, $this->_langId, $this->preview);
		$this->calcPageLink($this->pageNo, $totalCount, $this->entryViewCount);
		
		// リンク文字列作成、ページ番号調整
		$pageLink = $this->createPageLink($this->pageNo, self::LINK_PAGE_COUNT, $this->currentPageUrl);

		// 記事一覧作成

		self::$_mainDb->getEntryItems($this->entryViewCount, $this->pageNo, $this->now, 0/*期間で指定*/, $this->startDt/*期間開始*/, $this->endDt/*期間終了*/,
							''/*検索キーワード*/, $this->_langId, $this->entryViewOrder, array($this, 'itemsLoop'), $this->preview);
		
		if ($this->isExistsViewData){
			// ページリンクを埋め込む
			if (!empty($pageLink)){
				$this->tmpl->setAttribute('page_link', 'visibility', 'visible');		// リンク表示
				$this->tmpl->addVar("page_link", "page_link", $pageLink);
			}
		} else {	// イベント記事データがないときはデータなしメッセージ追加
			$this->title = $this->titleNoEntry;
			$this->message = $this->messageNoEntry;
		}
	}
	/**
	 * 検索結果画面表示
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param								なし
	 */
	function showSearchList($request)
	{
		// 入力値取得
		$keyword = $request->trimValueOf('keyword');// 検索キーワード
		
		// キーワード検索のとき
		if (empty($keyword)){
			$this->message = '検索キーワードが入力されていません';
		} else {
			// キーワード分割
			$parsedKeywords = $this->gInstance->getTextConvManager()->parseSearchKeyword($keyword);
			
			// 検索キーワードを記録
			for ($i = 0; $i < count($parsedKeywords); $i++){
				$this->gInstance->getAnalyzeManager()->logSearchWord($this->gEnv->getCurrentWidgetId(), $parsedKeywords[$i]);
			}
			
			// 総数を取得
			$totalCount = self::$_mainDb->getEntryItemsCount($this->now, ''/*期間開始*/, ''/*期間終了*/, $parsedKeywords, $this->_langId, $this->preview);
			$this->calcPageLink($this->pageNo, $totalCount, $this->entryViewCount);

			// リンク文字列作成、ページ番号調整
			$pageLink = $this->createPageLink($this->pageNo, self::LINK_PAGE_COUNT, $this->currentPageUrl . '&act=search&keyword=' . urlencode($keyword));

			// 記事一覧を表示
			self::$_mainDb->getEntryItems($this->entryViewCount, $this->pageNo, $this->now, 0, ''/*期間開始*/, ''/*期間終了*/,
													$parsedKeywords, $this->_langId, $this->entryViewOrder, array($this, 'itemsLoop'), $this->preview);
			
			if ($this->isExistsViewData){
				// ページリンクを埋め込む
				if (!empty($pageLink)){
					$this->tmpl->setAttribute('page_link', 'visibility', 'visible');		// リンク表示
					$this->tmpl->addVar("page_link", "page_link", $pageLink);
				}
				$this->message = '検索キーワード：' . $keyword;
			} else {	// 検索結果なしの場合
				$this->tmpl->setAttribute('entrylist', 'visibility', 'hidden');
				$this->message = $this->messageFindNoEntry . '<br />検索キーワード：' . $keyword;
			}
		}
		$this->title = $this->titleSearchList;				// 検索一覧タイトル
		$this->pageTitle = self::DEFAULT_TITLE_SEARCH;		// 画面タイトル、パンくずリスト用タイトル
	}
	/**
	 * カテゴリー、アーカイブからの一覧画面表示
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param								なし
	 */
	function showList($request)
	{
		// 入力値取得
		$category = $request->trimValueOf(M3_REQUEST_PARAM_CATEGORY_ID);		// カテゴリID
		$year = $request->trimValueOf('year');		// 年指定
		$month = $request->trimValueOf('month');		// 月指定
		$day = $request->trimValueOf('day');		// 日指定
		
		if (!empty($category)){				// カテゴリー指定のとき
			// 総数を取得
			$totalCount = self::$_mainDb->getEntryItemsCountByCategory($this->now, $category, $this->_langId);
			$this->calcPageLink($this->pageNo, $totalCount, $this->entryViewCount);

			// リンク文字列作成、ページ番号調整
			$pageLink = $this->createPageLink($this->pageNo, self::LINK_PAGE_COUNT, $this->currentPageUrl . '&act=view&' . M3_REQUEST_PARAM_CATEGORY_ID . '=' . $category);
			
			// 記事一覧を表示
			self::$_mainDb->getEntryItemsByCategory($this->entryViewCount, $this->pageNo, $this->now, $category, $this->_langId, $this->entryViewOrder, array($this, 'itemsLoop'));

			// タイトルの設定
			$ret = $this->categoryDb->getCategoryByCategoryId($category, $this->gEnv->getDefaultLanguage(), $row);
			if ($ret) $this->title = str_replace('$1', $row['bc_name'], $this->titleList);
			
			// イベント記事データがないときはデータなしメッセージ追加
			if ($this->isExistsViewData){
				// ページリンクを埋め込む
				if (!empty($pageLink)){
					$this->tmpl->setAttribute('page_link', 'visibility', 'visible');		// リンク表示
					$this->tmpl->addVar("page_link", "page_link", $pageLink);
				}
			} else {
				$this->title = $this->titleNoEntry;
				$this->message = $this->messageNoEntry;
			}
		} else if (!empty($year)){			// 年月日指定のとき
			if (!empty($month) && !empty($day)){		// 日指定のとき
				$startDt = $year . '/' . $month . '/' . $day;
				$endDt = $this->getNextDay($year . '/' . $month . '/' . $day);
				
				// 総数を取得
				$totalCount = self::$_mainDb->getEntryItemsCount($this->now, $startDt, $endDt, ''/*検索キーワード*/, $this->_langId, $this->preview);
				$this->calcPageLink($this->pageNo, $totalCount, $this->entryViewCount);
				
				// リンク文字列作成、ページ番号調整
				$pageLink = $this->createPageLink($this->pageNo, self::LINK_PAGE_COUNT, $this->currentPageUrl . '&act=view&year=' . $year . '&month=' . $month . '&day=' . $day);

				// 記事一覧作成
				self::$_mainDb->getEntryItems($this->entryViewCount, $this->pageNo, $this->now, 0/*期間で指定*/, $startDt/*期間開始*/, $endDt/*期間終了*/,
													''/*検索キーワード*/, $this->_langId, $this->entryViewOrder, array($this, 'itemsLoop'), $this->preview);
				
				if ($this->isExistsViewData){
					// ページリンクを埋め込む
					if (!empty($pageLink)){
						$this->tmpl->setAttribute('page_link', 'visibility', 'visible');		// リンク表示
						$this->tmpl->addVar("page_link", "page_link", $pageLink);
					}
				}
				
				// 年月日の表示
				$this->title = str_replace('$1', $year . '年 ' . $month . '月 ' . $day . '日', $this->titleList);
				
				// イベント記事データがないときはデータなしメッセージ追加
				if (!$this->isExistsViewData){
					$this->message = $this->messageNoEntry;
				}
			} else if (!empty($month)){		// 月指定のとき
				$startDt = $year . '/' . $month . '/1';
				$endDt = $this->getNextMonth($year . '/' . $month) . '/1';
				
				// 総数を取得
				$totalCount = self::$_mainDb->getEntryItemsCount($this->now, $startDt, $endDt, ''/*検索キーワード*/, $this->_langId, $this->preview);
				$this->calcPageLink($this->pageNo, $totalCount, $this->entryViewCount);
				
				// リンク文字列作成、ページ番号調整
				$pageLink = $this->createPageLink($this->pageNo, self::LINK_PAGE_COUNT, $this->currentPageUrl . '&act=view&year=' . $year . '&month=' . $month);
				
				// 記事一覧作成
				self::$_mainDb->getEntryItems($this->entryViewCount, $this->pageNo, $this->now, 0/*期間で指定*/, $startDt/*期間開始*/, $endDt/*期間終了*/,
													''/*検索キーワード*/, $this->_langId, $this->entryViewOrder, array($this, 'itemsLoop'), $this->preview);
				
				if ($this->isExistsViewData){
					// ページリンクを埋め込む
					if (!empty($pageLink)){
						$this->tmpl->setAttribute('page_link', 'visibility', 'visible');		// リンク表示
						$this->tmpl->addVar("page_link", "page_link", $pageLink);
					}
				}
				// 年月の表示
				$this->title = str_replace('$1', $year . '年 ' . $month . '月', $this->titleList);
				
				// イベント記事データがないときはデータなしメッセージ追加
				if (!$this->isExistsViewData){
					$this->message = $this->messageNoEntry;
				}
			} else {		// 年指定のとき
				$startDt = $year . '/1/1';
				$endDt = ($year + 1) . '/1/1';
				
				// 総数を取得
				$totalCount = self::$_mainDb->getEntryItemsCount($this->now, $startDt, $endDt, ''/*検索キーワード*/, $this->_langId, $this->preview);
				$this->calcPageLink($this->pageNo, $totalCount, $this->entryViewCount);
				
				// リンク文字列作成、ページ番号調整
				$pageLink = $this->createPageLink($this->pageNo, self::LINK_PAGE_COUNT, $this->currentPageUrl . '&act=view&year=' . $year);
				
				// 記事一覧作成
				self::$_mainDb->getEntryItems($this->entryViewCount, $this->pageNo, $this->now, 0/*期間で指定*/, $startDt/*期間開始*/, $endDt/*期間終了*/,
													''/*検索キーワード*/, $this->_langId, $this->entryViewOrder, array($this, 'itemsLoop'), $this->preview);
				
				if ($this->isExistsViewData){
					// ページリンクを埋め込む
					if (!empty($pageLink)){
						$this->tmpl->setAttribute('page_link', 'visibility', 'visible');		// リンク表示
						$this->tmpl->addVar("page_link", "page_link", $pageLink);
					}
				}
				// 年の表示
				$this->title = str_replace('$1', $year . '年', $this->titleList);
				
				// イベント記事データがないときはデータなしメッセージ追加
				if (!$this->isExistsViewData){
					$this->message = $this->messageNoEntry;
				}
			}
		}
		$this->pageTitle = $this->title;		// カテゴリー名を画面タイトルにする
	}
	/**
	 * ヘッダ部メタタグの設定
	 *
	 * HTMLのheadタグ内に出力する。
	 * _assign()よりも後に実行される。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ
	 * @return array 						設定データ。連想配列で「title」「description」「keywords」を設定。
	 */
	function _setHeadMeta($request, &$param)
	{
		$headData = array(	'title' => $this->headTitle,
							'description' => $this->headDesc,
							'keywords' => $this->headKeyword);
		return $headData;
	}
	/**
	 * JavascriptライブラリをHTMLヘッダ部に設定
	 *
	 * JavascriptライブラリをHTMLのheadタグ内に追加出力する。
	 * _assign()よりも後に実行される。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。
	 * @return string,array 				Javascriptライブラリ。出力しない場合は空文字列を設定。
	 */
	function _addScriptLibToHead($request, &$param)
	{
		return $this->addLib;
	}
	/**
	 * 取得したコンテンツ項目をテンプレートに設定する
	 *
	 * @param int		$index			行番号
	 * @param array		$fetchedRow		取得行
	 * @param object	$param			任意使用パラメータ
	 * @return bool						trueを返すとループ続行。falseを返すとその時点で終了。
	 */
	function itemsLoop($index, $fetchedRow)
	{
		// 参照ビューカウントを更新
		if (!$this->isSystemManageUser){		// システム運用者以上の場合はカウントしない
			$this->gInstance->getAnalyzeManager()->updateContentViewCount(self::CONTENT_TYPE, $fetchedRow['ee_serial'], $this->currentDay, $this->currentHour);
		}

		$entryId = $fetchedRow['ee_id'];// 記事ID
		$title = $fetchedRow['ee_name'];// タイトル
		$date = $fetchedRow['ee_regist_dt'];// 日付
		$accessPointUrl = $this->gEnv->getDefaultUrl();
		// イベント情報追加分
		$summary = $row['ee_summary'];		// 要約
		$place = $fetchedRow['ee_place'];// 開催場所
		$contact = $fetchedRow['ee_contact'];		// 連絡先
		$url = $fetchedRow['ee_url'];		// URL
		$isAllDay = $fetchedRow['ee_is_all_day'];			// 終日イベントかどうか
		
		// 記事へのリンクを生成
		$linkUrl = $this->getUrl($this->gEnv->getDefaultUrl() . '?'. M3_REQUEST_PARAM_EVENT_ID . '=' . $entryId, true/*リンク用*/);
		
		// タイトル作成
		$titleTag = '<h' . $this->itemTagLevel . '><a href="' . $this->convertUrlToHtmlEntity($linkUrl) . '">' . $this->convertToDispString($title) . '</a></h' . $this->itemTagLevel . '>';
		
		// ユーザ定義フィールド値取得
		// 埋め込む文字列はHTMLエスケープする
		$fieldInfoArray = event_mainCommonDef::parseUserMacro(array(self::$_configArray[event_mainCommonDef::CF_LAYOUT_ENTRY_SINGLE], self::$_configArray[event_mainCommonDef::CF_LAYOUT_ENTRY_LIST]));
		$fieldValueArray = $this->unserializeArray($fetchedRow['ee_option_fields']);
		$userFields = array();
		$fieldKeys = array_keys($fieldInfoArray);
		for ($i = 0; $i < count($fieldKeys); $i++){
			$key = $fieldKeys[$i];
			$value = $fieldValueArray[$key];
			$userFields[$key] = isset($value) ? $this->convertToDispString($value) : '';
		}
			
		// カレント言語がデフォルト言語と異なる場合はデフォルト言語の添付ファイルを取得
		$isDefaltContent = false;	// デフォルト言語のコンテンツを取得したかどうか
		if ($this->_isMultiLang && $this->_langId != $this->gEnv->getDefaultLanguage()){
			$ret = self::$_mainDb->getEntryItem($entryId, $this->_langId, $defaltContentRow);
			if ($ret) $isDefaltContent = true;
		}
			
		// コンテンツのサムネールを取得
		$thumbFilename = $fetchedRow['ee_thumb_filename'];
		if ($isDefaltContent) $thumbFilename = $defaltContentRow['ee_thumb_filename'];
		$thumbUrl = event_mainCommonDef::getEyecatchImageUrl($thumbFilename, self::$_configArray[event_mainCommonDef::CF_ENTRY_DEFAULT_IMAGE]);

		// カテゴリーリンクブロック
		$categoryTag = '';
		$defaultSerial = $fetchedRow['ee_serial'];
		if ($isDefaltContent) $defaultSerial = $defaltContentRow['ee_serial'];		// デフォルト言語のカテゴリーを取得
		$ret = self::$_mainDb->getEntryBySerial($defaultSerial, $row, $categoryRows);
		if ($ret){
			// テキストスタイルのとき
			$categoryItems = array();
			$categoryTag .= '<div class="' . self::CATEGORY_BLOCK_CLASS . '"><strong>' . self::CATEGORY_BLOCK_LABEL . '</strong>';		// カテゴリー項目のラベル
			for ($i = 0; $i < count($categoryRows); $i++){
				$categoryUrl = $this->currentPageUrl . '&' . M3_REQUEST_PARAM_CATEGORY_ID . '=' . $categoryRows[$i]['bc_id'];	// カテゴリーリンク先
				$categoryItems[] = '<a href="' . $this->convertUrlToHtmlEntity($this->getUrl($categoryUrl)) . '">' . $this->convertToDispString($categoryRows[$i]['bc_name']) . '</a>';
			}
			$categoryTag .= implode(self::CATEGORY_BLOCK_SEPARATOR, $categoryItems);	// リンク項目を連結
			$categoryTag .= '</div>';
		}
				
		// 関連コンテンツリンク
		$relatedContentTag = '';	// 関連コンテンツリンク
		if ($this->viewMode == 10){	// 記事単体表示のとき
			$relatedContent = $fetchedRow['ee_related_content'];
			if ($isDefaltContent) $relatedContent = $defaltContentRow['ee_related_content'];
			if (!empty($relatedContent)){
				$contentIdArray = array_map('trim', explode(',', $relatedContent));
				$ret = self::$_mainDb->getEntryItem($contentIdArray, $this->_langId, $rows);
				if ($ret){
					$relatedContentTag .= '<h' . ($this->itemTagLevel + 1) . '>' . self::TITLE_RELATED_CONTENT_BLOCK . '</h' . ($this->itemTagLevel + 1) . '>';
					$relatedContentTag .= '<ul>';
					for ($i = 0; $i < count($rows); $i++){
						$relatedUrl = $accessPointUrl . '?' . M3_REQUEST_PARAM_EVENT_ID . '=' . $rows[$i]['ee_id'];	// 関連コンテンツリンク先
						$relatedContentTag .= '<li><a href="' . $this->convertUrlToHtmlEntity($this->getUrl($relatedUrl)) . '">' . $this->convertToDispString($rows[$i]['ee_name']);
						$relatedContentTag .= '</a></li>';
					}
					$relatedContentTag .= '</ul>';
				}
			}
		}

		// Magic3マクロ変換
		// あらかじめ「CT_」タグをすべて取得する?
		$contentInfo = array();
		$contentInfo[M3_TAG_MACRO_CONTENT_ID] = $fetchedRow['ee_id'];			// コンテンツ置換キー(エントリーID)
		$contentInfo[M3_TAG_MACRO_CONTENT_URL] = $this->getUrl($this->gEnv->getDefaultUrl() . '?' . M3_REQUEST_PARAM_EVENT_ID . '=' . $fetchedRow['ee_id']);// コンテンツ置換キー(エントリーURL)
		$contentInfo[M3_TAG_MACRO_CONTENT_AUTHOR] = $fetchedRow['lu_name'];			// コンテンツ置換キー(著者)
		$contentInfo[M3_TAG_MACRO_CONTENT_TITLE] = $fetchedRow['ee_name'];			// コンテンツ置換キー(タイトル)
		$contentInfo[M3_TAG_MACRO_CONTENT_DESCRIPTION] = $fetchedRow['ee_description'];			// コンテンツ置換キー(簡易説明)
		$contentInfo[M3_TAG_MACRO_CONTENT_IMAGE] = $this->getUrl($thumbUrl);		// コンテンツ置換キー(画像)
		$contentInfo[M3_TAG_MACRO_CONTENT_UPDATE_DT] = $fetchedRow['ee_create_dt'];		// コンテンツ置換キー(更新日時)
		$contentInfo[M3_TAG_MACRO_CONTENT_REGIST_DT] = $fetchedRow['ee_regist_dt'];		// コンテンツ置換キー(登録日時)
		$contentInfo[M3_TAG_MACRO_CONTENT_DATE] = $this->timestampToDate($fetchedRow['ee_regist_dt']);		// コンテンツ置換キー(登録日)
		$contentInfo[M3_TAG_MACRO_CONTENT_TIME] = $this->timestampToTime($fetchedRow['ee_regist_dt']);		// コンテンツ置換キー(登録時)
		$contentInfo[M3_TAG_MACRO_CONTENT_START_DT] = $fetchedRow['ee_active_start_dt'];		// コンテンツ置換キー(公開開始日時)
		$contentInfo[M3_TAG_MACRO_CONTENT_END_DT] = $fetchedRow['ee_active_end_dt'];		// コンテンツ置換キー(公開終了日時)
		// イベント情報追加分
		$contentInfo[M3_TAG_MACRO_CONTENT_LOCATION]	= $fetchedRow['ee_place'];// 開催場所
		$contentInfo[M3_TAG_MACRO_CONTENT_CONTACT]	= $fetchedRow['ee_contact'];		// 連絡先
		$contentInfo[M3_TAG_MACRO_CONTENT_INFO_URL]		= $fetchedRow['ee_url'];		// その他の情報のURL
		
		// HTMLを出力(出力内容は特にエラーチェックしない)
		$entryHtml = $fetchedRow['ee_html'];
		$resultHtml = '';		// 結果
		if ($this->viewMode == 10){	// 記事単体表示のとき
			if (!empty($fetchedRow['ee_html_ext'])){
			//	$entryHtml = $fetchedRow['ee_html_ext'];// 続きがある場合は続きを出力
				$resultHtml = $fetchedRow['ee_html_ext'];		// 結果
			}
			
			// HTMLヘッダにタグ出力
			if ($this->outputHead){			// ヘッダ出力するかどうか
				$headText = self::$_configArray[event_mainCommonDef::CF_HEAD_VIEW_DETAIL];
				$headText = $this->convertM3ToHead($headText, $contentInfo);
				$this->gPage->setHeadOthers($headText);
			}
		} else {
			// 続きがある場合はリンクを付加
			if (!empty($fetchedRow['ee_html_ext'])){
				$entryHtml .= self::MESSAGE_EXT_ENTRY_PRE . '<a href="' . $this->convertUrlToHtmlEntity($linkUrl) . '" >' . self::MESSAGE_EXT_ENTRY . '</a>';
			}
		}
		if (!empty($entryHtml)) $entryHtml = '<div class="' . self::ENTRY_BODY_BLOCK_CLASS . '">' . $entryHtml . '</div>';// DIVで括る
		if (!empty($resultHtml)) $resultHtml = '<div class="' . self::ENTRY_RESULT_BLOCK_CLASS . '">' . $resultHtml . '</div>';// DIVで括る
		
		// イベント開催期間
		$dateHtml = '';
		if ($fetchedRow['ee_end_dt'] == $this->gEnv->getInitValueOfTimestamp()){		// 開催開始日時のみ表示のとき
			if ($isAllDay){		// 終日イベントのとき
				$dateHtml = $this->convertToDispDate($fetchedRow['ee_start_dt']);
			} else {
				$dateHtml = $this->convertToDispDateTime($fetchedRow['ee_start_dt'], 0/*ロングフォーマット*/, 10/*時分*/);
			}
		} else {
			if ($isAllDay){		// 終日イベントのとき
				$dateHtml = $this->convertToDispDate($fetchedRow['ee_start_dt']) . self::DATE_RANGE_DELIMITER;
				$dateHtml .= $this->convertToDispDate($fetchedRow['ee_end_dt']);
			} else {
				$dateHtml = $this->convertToDispDateTime($fetchedRow['ee_start_dt'], 0/*ロングフォーマット*/, 10/*時分*/) . self::DATE_RANGE_DELIMITER;
				$dateHtml .= $this->convertToDispDateTime($fetchedRow['ee_end_dt'], 0/*ロングフォーマット*/, 10/*時分*/);
			}
		}
		
		// コンテンツレイアウトに埋め込む
		$contentParam = array_merge($userFields, array(M3_TAG_MACRO_TITLE => $titleTag, M3_TAG_MACRO_BODY => $entryHtml, M3_TAG_MACRO_RESULT => $resultHtml, M3_TAG_MACRO_DATE => $dateHtml,
														/*M3_TAG_MACRO_FILES => '', M3_TAG_MACRO_PAGES => '',*/
														'LINKS' => $relatedContentTag, M3_TAG_MACRO_CATEGORY => $categoryTag));
		$entryHtml = $this->createDetailContent($this->viewMode, $contentParam);
		$entryHtml = $this->convertM3ToHtml($entryHtml, true/*改行コーをbrタグに変換*/, $contentInfo);		// コンテンツマクロ変換
		
		// コンテンツ編集権限がある場合はボタンを表示
		$buttonList = '';
		if (self::$_canEditEntry) $buttonList = $this->createButtonTag($fetchedRow['ee_serial']);// 編集権限があるとき
		
		$row = array(
			'entry' => $entryHtml,	// 投稿記事
			'button_list' => $buttonList	// 記事編集ボタン
		);
		$this->tmpl->addVars('entrylist', $row);
		$this->tmpl->parseTemplate('entrylist', 'a');
		$this->isExistsViewData = true;				// 表示データがあるかどうか
		return true;
	}
	/**
	 * 詳細コンテンツを作成
	 *
	 * @param int $viewMode				表示モード
	 * @param array	$contentParam		コンテンツ作成用パラメータ
	 * @return string			作成コンテンツ
	 */
	function createDetailContent($viewMode, $contentParam)
	{
		static $initContentText;
		
		if (!isset($initContentText)){
			if ($viewMode == 10){		// 記事単体表示の場合
				$initContentText = self::$_configArray[event_mainCommonDef::CF_LAYOUT_ENTRY_SINGLE];			// コンテンツレイアウト(記事詳細)
			} else {
				$initContentText = self::$_configArray[event_mainCommonDef::CF_LAYOUT_ENTRY_LIST];			// コンテンツレイアウト(記事一覧)
			}
		}
		
		// コンテンツを作成
		$contentText = $initContentText;
		$keys = array_keys($contentParam);
		for ($i = 0; $i < count($keys); $i++){
			$key = $keys[$i];
			$value = str_replace('\\', '\\\\', $contentParam[$key]);	// ##### (注意)preg_replaceで変換値のバックスラッシュが解釈されるので、あらかじめバックスラッシュを2重化しておく必要がある
			
			$pattern = '/' . preg_quote(M3_TAG_START . $key) . ':?(.*?)' . preg_quote(M3_TAG_END) . '/u';
			$contentText = preg_replace($pattern, $value, $contentText);
		}
		return $contentText;
	}
	/**
	 * 編集用ボタンタグ作成
	 *
	 * @param int $serial		イベント記事のシリアル番号(0のときは新規ボタン)
	 * @return string			タグ
	 */
	function createButtonTag($serial)
	{
		$buttonList = '';
		
		if (empty($serial)){
			$iconUrl = $this->gEnv->getRootUrl() . self::NEW_ICON_FILE;		// 新規アイコン
			$iconTitle = '記事作成';
			$editImg = '<img class="m3icon" src="' . $this->getUrl($iconUrl) . '" width="' . self::ICON_SIZE . '" height="' . self::ICON_SIZE . '" alt="' . $iconTitle . '" title="' . $iconTitle . '" rel="m3help" />';
			$buttonLink = '<a href="javascript:void(0);" onclick="editEntry(0);">' . $editImg . '</a>';
			$buttonList .= '<div class="m3edittool" style="top:' . $this->editIconPos . 'px;position:relative;">' . $buttonLink . '</div>';		// *** スタイルは直接設定する必要あり ***
			
			$this->editIconPos += self::EDIT_ICON_NEXT_POS;			// アイコンの位置を更新
		} else {
			$iconUrl = $this->gEnv->getRootUrl() . self::EDIT_ICON_FILE;		// 編集アイコン
			$iconTitle = '記事編集';
			$editImg = '<img class="m3icon" src="' . $this->getUrl($iconUrl) . '" width="' . self::ICON_SIZE . '" height="' . self::ICON_SIZE . '" alt="' . $iconTitle . '" title="' . $iconTitle . '" rel="m3help" />';
			$buttonLink = '<a href="javascript:void(0);" onclick="editEntry(' . $serial . ');">' . $editImg . '</a>';
			$buttonList .= '<div class="m3edittool" style="top:' . $this->editIconPos . 'px;position:relative;">' . $buttonLink . '</div>';		// *** スタイルは直接設定する必要あり ***
			
			$this->editIconPos = self::EDIT_ICON_MIN_POS;			// アイコンの位置を初期位置に戻す
		}
		return $buttonList;
	}
}
?>
