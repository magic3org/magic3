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
 * @copyright  Copyright 2006-2017 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
/***************************************************************************************************
### 複製元クラス admin_blog_mainEntryWidgetContainer ###
複製元クラスからblog_mainEntryWidgetContainerクラスを生成する
変更行
　・親クラスファイルの読み込み(require_once)
　・クラス名定義
****************************************************************************************************/
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/blog_mainBaseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() .	'/blog_mainDb.php');
require_once($gEnvManager->getCommonPath() . '/valueCheck.php');

// このファイルはadmin_blog_mainEntryWidgetContainer.phpの内容と同じ。クラス名の定義のみ異なる。
//class admin_blog_mainEntryWidgetContainer extends admin_blog_mainBaseWidgetContainer
class blog_mainEntryWidgetContainer extends blog_mainBaseWidgetContainer
{
	private $currentYear;		// 現在の年号
	private $serialNo;		// 選択中の項目のシリアル番号
	private $entryId;
	private $blogId;		// 所属ブログ
	private $langId;		// 現在の選択言語
	private $serialArray = array();		// 表示されている項目シリアル番号
	private $categoryListData;		// 全記事カテゴリー
	private $categorySortInfo;		// カテゴリーソート情報
	private $categoryArray;			// 選択中の記事カテゴリー
	private $categoryCount;			// カテゴリ数
	private $isMultiLang;			// 多言語対応画面かどうか
	private $useMultiBlog;// マルチブログを使用するかどうか
	private $useComment;// コメント機能を使用するかどうか
	private $fieldValueArray;		// ユーザ定義フィールド入力値
	const ICON_SIZE = 32;		// アイコンのサイズ
	const SMALL_ICON_SIZE = 16;		// アイコンのサイズ
	const EYECATCH_IMAGE_SIZE = 40;		// アイキャッチ画像サイズ
	const DEFAULT_LIST_COUNT = 20;			// 最大リスト表示数
	const LINK_PAGE_COUNT		= 10;			// リンクページ数
	const LINK_PAGE_COUNT_S		= 5;			// リンクページ数(小画面用)
	const CATEGORY_NAME_SIZE = 20;			// カテゴリー名の最大文字列長
	const CALENDAR_ICON_FILE = '/images/system/calendar.png';		// カレンダーアイコン
	const ACTIVE_ICON_FILE = '/images/system/active32.png';			// 公開中アイコン
	const INACTIVE_ICON_FILE = '/images/system/inactive32.png';		// 非公開アイコン
	const SEARCH_ICON_FILE = '/images/system/search16.png';		// 検索用アイコン
	const SMALL_ACTIVE_ICON_FILE = '/images/system/active.png';			// 公開中アイコン
	const SMALL_INACTIVE_ICON_FILE = '/images/system/inactive.png';		// 非公開アイコン
	const FIELD_HEAD = 'item_';			// フィールド名の先頭文字列
	const NO_BLOG_NAME = '所属なし';		// 所属ブログなし
	const TAG_ID_ACTIVE_TERM = 'activeterm_button';		// 公開期間エリア表示用ボタンタグ
	const TOOLTIP_ACTIVE_TERM = '公開期間を設定';		// 公開期間エリア表示用ボタンツールチップ
	const DATETIME_FORMAT = 'Y年n月j日($1) H:i:s';		// 日付時間フォーマット
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		$this->currentYear = intval(date('Y'));
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
		$this->useMultiBlog		= self::$_configArray[blog_mainCommonDef::CF_USE_MULTI_BLOG];// マルチブログを使用するかどうか
		$this->useComment		= self::$_configArray[blog_mainCommonDef::CF_RECEIVE_COMMENT];// コメント機能を使用するかどうか
		$this->categoryCount	= self::$_configArray[blog_mainCommonDef::CF_CATEGORY_COUNT];			// カテゴリ数
		if (empty($this->categoryCount)) $this->categoryCount = blog_mainCommonDef::DEFAULT_CATEGORY_COUNT;
		
		self::$_mainDb->getAllCategory($this->_langId, $this->categoryListData);		// カテゴリー情報
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
			if ($this->_isSmallDeviceOptimize){			// 小画面デバイス最適化の場合
				return 'admin_entry_detail_small.tmpl.html';
			} else {
				return 'admin_entry_detail.tmpl.html';
			}
		} else {			// 一覧画面
			if ($this->_isSmallDeviceOptimize){			// 小画面デバイス最適化の場合
				return 'admin_entry_small.tmpl.html';
			} else {
				return 'admin_entry.tmpl.html';
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
		if ($this->gEnv->isAdminDirAccess()){		// 管理画面へのアクセスの場合
			$this->blogId = null;	// デフォルトブログ(ブログID空)を含むすべてのブログ記事にアクセス可能
		} else {
			$this->blogId = $request->trimValueOf(M3_REQUEST_PARAM_BLOG_ID);		// 所属ブログ
		}
		
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
				// 削除するブログ記事の情報を取得
				$delEntryInfo = array();
				for ($i = 0; $i < count($delItems); $i++){
					$ret = self::$_mainDb->getEntryBySerial($delItems[$i], $row, $categoryRow);
					if ($ret){
						$newInfoObj = new stdClass;
						$newInfoObj->entryId = $row['be_id'];		// 記事ID
						$newInfoObj->name = $row['be_name'];		// 記事タイトル
						$newInfoObj->thumb = $row['be_thumb_filename'];		// サムネール
						$delEntryInfo[] = $newInfoObj;
					}
				}
				
				$ret = self::$_mainDb->delEntryItem($delItems);
				if ($ret){		// データ削除成功のとき
					$this->setGuidanceMsg('データを削除しました');
					
					// ##### サムネールの削除 #####
					for ($i = 0; $i < count($delEntryInfo); $i++){
						$infoObj = $delEntryInfo[$i];
//						$ret = blog_mainCommonDef::removeThumbnail($infoObj->entryId);
						
						if (!empty($infoObj->thumb)){
							//$oldFiles = explode(';', $infoObj->thumb);
							//$this->gInstance->getImageManager()->delSystemDefaultThumb(M3_VIEW_TYPE_BLOG, blog_mainCommonDef::$_deviceType, $oldFiles);
							
							// アイキャッチ画像削除
							blog_mainCommonDef::removeEyecatchImage($infoObj->entryId);
						}
					}

					// キャッシュデータのクリア
					for ($i = 0; $i < count($delItems); $i++){
						$this->clearCacheBySerial($delItems[$i]);
					}
					
					// 親ウィンドウを更新
					$this->gPage->updateParentWindow();
					
					// 運用ログを残す
					for ($i = 0; $i < count($delEntryInfo); $i++){
						$infoObj = $delEntryInfo[$i];
						$eventParam = array(	M3_EVENT_HOOK_PARAM_CONTENT_TYPE	=> M3_VIEW_TYPE_BLOG,
												M3_EVENT_HOOK_PARAM_CONTENT_ID		=> $infoObj->entryId,
												M3_EVENT_HOOK_PARAM_UPDATE_DT		=> date("Y/m/d H:i:s"));
						$this->writeUserInfoEvent(__METHOD__, 'ブログ記事を削除しました。タイトル: ' . $infoObj->name, 2402, 'ID=' . $infoObj->entryId, $eventParam);
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
		$totalCount = self::$_mainDb->getEntryItemCount($search_startDt, $endDt, $this->categoryArray, $search_keyword, $this->langId, $this->blogId);

		// ページング計算
		$this->calcPageLink($pageNo, $totalCount, $maxListCount);
		
		// ページングリンク作成
		if ($this->_isSmallDeviceOptimize){			// 小画面デバイス最適化の場合
			$pageLink = $this->createPageLink($pageNo, self::LINK_PAGE_COUNT_S, ''/*リンク作成用(未使用)*/, 'selpage($1);return false;');
		} else {
			$pageLink = $this->createPageLink($pageNo, self::LINK_PAGE_COUNT, ''/*リンク作成用(未使用)*/, 'selpage($1);return false;');
		}
		
		// 記事項目リストを取得
		self::$_mainDb->searchEntryItems($maxListCount, $pageNo, $search_startDt, $endDt, $this->categoryArray, $search_keyword, $this->langId, array($this, 'itemListLoop'), $this->blogId);
		if (count($this->serialArray) <= 0) $this->tmpl->setAttribute('itemlist', 'visibility', 'hidden');// 投稿記事がないときは、一覧を表示しない
		
		// カテゴリーメニューを作成
		$this->createCategoryMenu(1);		// メニューは１つだけ表示
		
		// プレビュー用URL
		$previewUrl = $this->gEnv->getDefaultUrl() . '?' . M3_REQUEST_PARAM_PAGE_SUB_ID . '=' . $this->gEnv->getPageSubIdByContentType($this->gEnv->getDefaultPageId(), M3_VIEW_TYPE_BLOG);
		$previewUrl .= '&' . M3_REQUEST_PARAM_OPERATION_COMMAND . '=' . M3_REQUEST_CMD_PREVIEW;
//		if ($this->isMultiLang) $previewUrl .= '&' . M3_REQUEST_PARAM_OPERATION_LANG . '=' . $this->langId;		// 多言語対応の場合は言語IDを付加
		$this->tmpl->addVar('_widget', 'preview_url', $previewUrl);// プレビュー用URL(フロント画面)
		
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
		// コンテンツレイアウトを取得
		$contentLayout = array(self::$_configArray[blog_mainCommonDef::CF_LAYOUT_ENTRY_SINGLE], self::$_configArray[blog_mainCommonDef::CF_LAYOUT_ENTRY_LIST]);
		$fieldInfoArray = blog_mainCommonDef::parseUserMacro($contentLayout);
		
		// 入力値を取得
		$openBy = $request->trimValueOf(M3_REQUEST_PARAM_OPEN_BY);		// ウィンドウオープンタイプ
		$act = $request->trimValueOf('act');
		$this->langId = $request->trimValueOf('item_lang');				// 現在メニューで選択中の言語
		if (empty($this->langId)) $this->langId = $this->gEnv->getDefaultLanguage();			// 言語が選択されていないときは、デフォルト言語を設定	
		$this->entryId = $request->trimValueOf('entryid');		// 記事エントリーID
		$this->serialNo = $request->trimValueOf('serial');		// 選択項目のシリアル番号
		$this->blogId = $request->trimValueOf(M3_REQUEST_PARAM_BLOG_ID);		// 所属ブログ
		$name = $request->trimValueOf('item_name');
		$entry_date = $request->trimValueOf('item_entry_date');		// 投稿日
		$entry_time = $request->trimValueOf('item_entry_time');		// 投稿時間
		$html = $request->valueOf('item_html');
		$html2 = $request->valueOf('item_html2');
		if (strlen($html2) <= 10){ // IE6のときFCKEditorのバグの対応(「続き」が空の場合でもpタグが送信される)
			$html2 = '';
		}
		$desc = $request->trimValueOf('item_desc');		// 簡易説明
		$metaDesc = $request->trimValueOf('item_meta_desc');			// ページ要約
		$metaKeyword = $request->trimValueOf('item_meta_keyword');	// ページキーワード
		$status = $request->trimValueOf('item_status');		// エントリー状態(0=未設定、1=編集中、2=公開、3=非公開)
		$category = '';									// カテゴリー
		$showComment = ($request->trimValueOf('show_comment') == 'on') ? 1 : 0;				// コメントを表示するかどうか
		$receiveComment = ($request->trimValueOf('receive_comment') == 'on') ? 1 : 0;		// コメントを受け付けるかどうか
		$relatedContent = $request->trimValueOf('item_related_content');	// 関連コンテンツ
		if (!$this->useComment){		// コメント機能を使用しない場合のデフォルト値
			$showComment = 1;				// コメントを表示するかどうか
			$receiveComment = 1;		// コメントを受け付けるかどうか
		}
		
		// カテゴリーを取得
		$this->categoryArray = array();
		for ($i = 0; $i < $this->categoryCount; $i++){
			$itemName = 'item_category' . $i;
			$itemValue = $request->trimValueOf($itemName);
			if (!empty($itemValue)){		// 0以外の値を取得
				$this->categoryArray[] = $itemValue;
			}
		}
		// カテゴリーをソート
		$this->sortCategory($this->categoryArray);

		// 公開期間を取得
		$start_date = $request->trimValueOf('item_start_date');		// 公開期間開始日付
		if (!empty($start_date)) $start_date = $this->convertToProperDate($start_date);
		$start_time = $request->trimValueOf('item_start_time');		// 公開期間開始時間
		if (empty($start_date)){
			$start_time = '';					// 日付が空のときは時刻も空に設定する
		} else {
			if (empty($start_time)) $start_time = '00:00';		// 日付が入っているときは時間にデフォルト値を設定
		}
		if (!empty($start_time)) $start_time = $this->convertToProperTime($start_time, 1/*時分フォーマット*/);
		
		$end_date = $request->trimValueOf('item_end_date');		// 公開期間終了日付
		if (!empty($end_date)) $end_date = $this->convertToProperDate($end_date);
		$end_time = $request->trimValueOf('item_end_time');		// 公開期間終了時間
		if (empty($end_date)){
			$end_time = '';					// 日付が空のときは時刻も空に設定する
		} else {
			if (empty($end_time)) $end_time = '00:00';		// 日付が入っているときは時間にデフォルト値を設定
		}
		if (!empty($end_time)) $end_time = $this->convertToProperTime($end_time, 1/*時分フォーマット*/);
		
		// ユーザ定義フィールド入力値取得
		$this->fieldValueArray = array();		// ユーザ定義フィールド入力値
		$fieldKeys = array_keys($fieldInfoArray);
		for ($i = 0; $i < count($fieldKeys); $i++){
			$fieldKey = $fieldKeys[$i];
			$itemName = self::FIELD_HEAD . $fieldKey;
			$itemValue = $this->cleanMacroValue($request->trimValueOf($itemName));
			if (!empty($itemValue)) $this->fieldValueArray[$fieldKey] = $itemValue;
		}
		
		$historyIndex = -1;	// 履歴番号初期化(旧データの場合のみ有効)
		$reloadData = false;		// データの再ロード
		if ($act == 'select'){		// 一覧から選択のとき
			$reloadData = true;		// データの再ロード
		} else if ($act == 'new'){
			$this->serialNo = 0;
			$reloadData = true;		// データの再読み込み
		} else if ($act == 'selectlang'){		// 項目選択の場合
			// 登録済みのコンテンツデータを取得
			$this->serialNo = self::$_mainDb->getEntrySerialNoByContentId($this->entryId, $this->langId);
			if (empty($this->serialNo)){
				// 取得できないときは一部初期化
				//$name = '';				// タイトル
				//$html = '';				// HTML
				//$status = 0;				// エントリー状況
				$status = 1;				// エントリー状況(編集中)
				$reg_user = '';				// 投稿者
				$update_user = '';// 更新者
				$update_dt = '';							
			} else {
				$reloadData = true;		// データの再ロード
			}
		} else if ($act == 'add' || $act == 'addlang'){		// 項目追加の場合
			// 入力チェック
			$this->checkInput($name, 'タイトル');
			$this->checkDate($entry_date, '投稿日付');
			$this->checkTime($entry_time, '投稿時間');
					
			// 期間範囲のチェック
			if (!empty($start_date) && !empty($end_date)){
				if (strtotime($start_date . ' ' . $start_time) >= strtotime($end_date . ' ' . $end_time)) $this->setUserErrorMsg('公開期間が不正です');
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
				$regDt = $this->convertToProperDate($entry_date) . ' ' . $this->convertToProperTime($entry_time);		// 投稿日時
				
				// サムネール画像を取得
				$thumbFilename = '';
				$thumbSrcPath = '';			// サムネール画像の元のファイル
				if (($this->isMultiLang && $this->langId == $this->gEnv->getDefaultLanguage()) || !$this->isMultiLang){		// // 多言語対応の場合はデフォルト言語が選択されている場合のみ処理を行う
					// 次の記事IDを取得
					$nextEntryId = self::$_mainDb->getNextEntryId();
				
					if ($status == 2){		// 記事公開の場合のみアイキャッチ画像を作成
						$thumbPath = $this->gInstance->getImageManager()->getFirstImagePath($html);
						if (empty($thumbPath) && !empty($html2)) $thumbPath = $this->gInstance->getImageManager()->getFirstImagePath($html2);		// 本文1に画像がないときは本文2を検索
						if (!empty($thumbPath)){
							$ret = $this->gInstance->getImageManager()->createSystemDefaultThumb(M3_VIEW_TYPE_BLOG, blog_mainCommonDef::$_deviceType, $nextEntryId, $thumbPath, $destFilename);
							if ($ret) $thumbFilename = implode(';', $destFilename);
						}
						
						// サムネールの元の画像を取得
						$thumbSrcPath = str_replace($this->gEnv->getResourcePath(), '', $thumbPath);
					}
				}
				
				// 画像のパスをマクロ表現に変換
				$html = $this->gInstance->getTextConvManager()->convToContentMacro($html);
				$html2 = $this->gInstance->getTextConvManager()->convToContentMacro($html2);
				
				// 追加パラメータ
				$otherParams = array(	'be_description'		=> $desc,		// 簡易説明
										'be_meta_description'	=> $metaDesc,		// ページ要約(METAタグ)
										'be_meta_keywords'		=> $metaKeyword,		// ページキーワード(METAタグ)
										'be_thumb_filename'		=> $thumbFilename,		// サムネールファイル名
										'be_thumb_src'			=> $thumbSrcPath,		// サムネール画像の元のファイル
										'be_related_content'	=> $relatedContent,		// 関連コンテンツ
										'be_option_fields'		=> $this->serializeArray($this->fieldValueArray));				// ユーザ定義フィールド値

				// 記事データを追加
				if (($this->isMultiLang && $this->langId == $this->gEnv->getDefaultLanguage()) || !$this->isMultiLang){		// 多言語でデフォルト言語、または単一言語のとき
					$ret = self::$_mainDb->addEntryItem($nextEntryId * (-1)/*次のコンテンツIDのチェック*/, $this->langId, $name, $html, $html2, $status, $this->categoryArray, $this->blogId, 
													$this->_userId, $regDt, $startDt, $endDt, $showComment, $receiveComment, $newSerial, $otherParams);
				} else {
					$ret = self::$_mainDb->addEntryItem($this->entryId, $this->langId, $name, $html, $html2, $status, $this->categoryArray, $this->blogId, 
													$this->_userId, $regDt, $startDt, $endDt, $showComment, $receiveComment, $newSerial, $otherParams);
				}
				if ($ret){
					$this->setGuidanceMsg('データを追加しました');
					
					// シリアル番号更新
					$this->serialNo = $newSerial;
					$reloadData = true;		// データの再ロード
					
					// ##### サムネールの作成 #####
/*					$ret = self::$_mainDb->getEntryBySerial($this->serialNo, $row, $categoryRow);
					if ($ret){
						$entryId	= $row['be_id'];		// 記事ID
						$html		= $row['be_html'];				// HTML
						$updateDt	= $row['be_create_dt'];
						$status		= $row['be_status'];
				
						if ($status == 2){		// 公開の場合
							$ret = blog_mainCommonDef::createThumbnail($html, $entryId, $updateDt);
						} else {
							$ret = blog_mainCommonDef::removeThumbnail($entryId);
						}
					}*/
					
					// キャッシュデータのクリア
					$this->clearCacheBySerial($this->serialNo);
					
					// 親ウィンドウを更新
					$this->gPage->updateParentWindow();
					
					// 運用ログを残す
					$statusStr = '';
					$ret = self::$_mainDb->getEntryBySerial($this->serialNo, $row, $categoryRow);
					if ($ret){
						$this->entryId = $row['be_id'];		// 記事ID
						$name = $row['be_name'];		// コンテンツ名前
						$updateDt = $row['be_create_dt'];		// 作成日時
						
						// 公開状態
						switch ($row['be_status']){
							case 1:	$statusStr = '編集中';	break;
							case 2:	$statusStr = '公開';	break;
							case 3:	$statusStr = '非公開';	break;
						}
					}
					$eventParam = array(	M3_EVENT_HOOK_PARAM_CONTENT_TYPE	=> M3_VIEW_TYPE_BLOG,
											M3_EVENT_HOOK_PARAM_CONTENT_ID		=> $this->entryId,
											M3_EVENT_HOOK_PARAM_UPDATE_DT		=> $updateDt);
					$this->writeUserInfoEvent(__METHOD__, 'ブログ記事を追加(' . $statusStr . ')しました。タイトル: ' . $name, 2400, 'ID=' . $this->entryId, $eventParam);
				} else {
					$this->setAppErrorMsg('データ追加に失敗しました');
				}
			}
		} else if ($act == 'update'){		// 項目更新の場合
			// 入力チェック
			$this->checkInput($name, 'タイトル');
			$this->checkDate($entry_date, '投稿日付');
			$this->checkTime($entry_time, '投稿時間');
			
			// 期間範囲のチェック
			if (!empty($start_date) && !empty($end_date)){
				if (strtotime($start_date . ' ' . $start_time) >= strtotime($end_date . ' ' . $end_time)) $this->setUserErrorMsg('公開期間が不正です');
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
				$regDt = $this->convertToProperDate($entry_date) . ' ' . $this->convertToProperTime($entry_time);		// 投稿日時
				
				// サムネール画像を取得
				$thumbFilename = '';
				$thumbSrcPath = '';			// サムネール画像の元のファイル
				if (($this->isMultiLang && $this->langId == $this->gEnv->getDefaultLanguage()) || !$this->isMultiLang){		// // 多言語対応の場合はデフォルト言語が選択されている場合のみ処理を行う
					if ($status == 2){		// 記事公開の場合のみアイキャッチ画像を作成
						// コンテンツからアイキャッチ画像を作成
						$thumbPath = $this->gInstance->getImageManager()->getFirstImagePath($html);
						if (empty($thumbPath) && !empty($html2)) $thumbPath = $this->gInstance->getImageManager()->getFirstImagePath($html2);		// 本文1に画像がないときは本文2を検索
						if (!empty($thumbPath)){
							$ret = $this->gInstance->getImageManager()->createSystemDefaultThumb(M3_VIEW_TYPE_BLOG, blog_mainCommonDef::$_deviceType, $this->entryId, $thumbPath, $destFilename);
							if ($ret) $thumbFilename = implode(';', $destFilename);
						}
					
						// 非公開ディレクトリのアイキャッチ画像をコピー
						$ret = blog_mainCommonDef::copyEyecatchImageToPublicDir($this->entryId);
						if ($ret){			// アイキャッチ画像をコピーした場合は、ファイル名を取得
							// 画像ファイル名、フォーマット取得
							list($destFilename, $formats) = $this->gInstance->getImageManager()->getSystemThumbFilename($this->entryId, 1/*クロップ画像のみ*/);
							$thumbFilename = implode(';', $destFilename);
						}
						
						// サムネールの元の画像を取得
						$thumbSrcPath = str_replace($this->gEnv->getResourcePath(), '', $thumbPath);
					} else {		// 記事非公開の場合
						// 公開ディレクトリのアイキャッチ画像を削除
						blog_mainCommonDef::removeEyecatchImageInPublicDir($this->entryId);
					}
				}

				// 画像のパスをマクロ表現に変換
				$html = $this->gInstance->getTextConvManager()->convToContentMacro($html);
				$html2 = $this->gInstance->getTextConvManager()->convToContentMacro($html2);
				
				// 追加パラメータ
				$otherParams = array(	'be_description'		=> $desc,		// 簡易説明
										'be_meta_description'	=> $metaDesc,		// ページ要約(METAタグ)
										'be_meta_keywords'		=> $metaKeyword,		// ページキーワード(METAタグ)
										'be_thumb_filename'		=> $thumbFilename,		// サムネールファイル名
										'be_thumb_src'			=> $thumbSrcPath,		// サムネール画像の元のファイル
										'be_related_content'	=> $relatedContent,		// 関連コンテンツ
										'be_option_fields'		=> $this->serializeArray($this->fieldValueArray));				// ユーザ定義フィールド値

				// 履歴からのデータ取得の場合はシリアル番号を最新に変更
				$mode = $request->trimValueOf('mode');			// データ更新モード
				if ($mode == 'history'){		// 履歴データ表示モード
					$this->serialNo = self::$_mainDb->getEntrySerialNoByContentId($this->entryId, $this->langId);		// 最新のシリアル番号を取得
					
					// ### 履歴データを再取得すべき? ###
				}
			
				// 記事データを更新
				$ret = self::$_mainDb->updateEntryItem($this->serialNo, $name, $html, $html2, $status, $this->categoryArray, $this->blogId, 
													''/*投稿者そのまま*/, $regDt, $startDt, $endDt, $showComment, $receiveComment, $newSerial, $oldRecord, $otherParams);
/*				if ($ret){
					// コンテンツに画像がなくなった場合は、サムネールを削除
					if (empty($thumbFilename) && !empty($oldRecord['be_thumb_filename'])){
						$oldFiles = explode(';', $oldRecord['be_thumb_filename']);
						$this->gInstance->getImageManager()->delSystemDefaultThumb(M3_VIEW_TYPE_BLOG, blog_mainCommonDef::$_deviceType, $oldFiles);
					}
				}*/
				
				if ($ret){
					$this->setGuidanceMsg('データを更新しました');
					
					// シリアル番号更新
					$this->serialNo = $newSerial;
					$reloadData = true;		// データの再ロード
					
/*					// ##### サムネールの作成 #####
					$ret = self::$_mainDb->getEntryBySerial($this->serialNo, $row, $categoryRow);
					if ($ret){
						$entryId	= $row['be_id'];		// 記事ID
						$html		= $row['be_html'];				// HTML
						$updateDt	= $row['be_create_dt'];
						$status		= $row['be_status'];
				
						if ($status == 2){		// 公開の場合
							$ret = blog_mainCommonDef::createThumbnail($html, $entryId, $updateDt);
						} else {
							$ret = blog_mainCommonDef::removeThumbnail($entryId);
						}
					}*/
					
					// キャッシュデータのクリア
					$this->clearCacheBySerial($this->serialNo);
					
					// 親ウィンドウを更新
					$this->gPage->updateParentWindow();
					
					// 運用ログを残す
					$statusStr = '';
					$ret = self::$_mainDb->getEntryBySerial($this->serialNo, $row, $categoryRow);
					if ($ret){
						$this->entryId = $row['be_id'];		// 記事ID
						$name = $row['be_name'];		// コンテンツ名前
						$updateDt = $row['be_create_dt'];		// 作成日時
						
						// 公開状態
						switch ($row['be_status']){
							case 1:	$statusStr = '編集中';	break;
							case 2:	$statusStr = '公開';	break;
							case 3:	$statusStr = '非公開';	break;
						}
					}
					$eventParam = array(	M3_EVENT_HOOK_PARAM_CONTENT_TYPE	=> M3_VIEW_TYPE_BLOG,
											M3_EVENT_HOOK_PARAM_CONTENT_ID		=> $this->entryId,
											M3_EVENT_HOOK_PARAM_UPDATE_DT		=> $updateDt);
					$this->writeUserInfoEvent(__METHOD__, 'ブログ記事を更新(' . $statusStr . ')しました。タイトル: ' . $name, 2401, 'ID=' . $this->entryId, $eventParam);
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
				// 削除するブログ記事の情報を取得
				$ret = self::$_mainDb->getEntryBySerial($this->serialNo, $row, $categoryRow);
				if ($ret){
					$this->entryId = $row['be_id'];		// 記事ID
					$name = $row['be_name'];		// コンテンツ名前
				}
					
				$ret = self::$_mainDb->delEntryItem(array($this->serialNo));
				if ($ret){		// データ削除成功のとき
					$this->setGuidanceMsg('データを削除しました');
					
					// ##### サムネールの削除 #####
//					$ret = blog_mainCommonDef::removeThumbnail($this->entryId);
					
					// サムネールを削除
					if (!empty($row['be_thumb_filename'])){
//						$oldFiles = explode(';', $row['be_thumb_filename']);
//						$this->gInstance->getImageManager()->delSystemDefaultThumb(M3_VIEW_TYPE_BLOG, blog_mainCommonDef::$_deviceType, $oldFiles);
						
						// アイキャッチ画像削除
						blog_mainCommonDef::removeEyecatchImage($this->entryId);
					}
						
					// キャッシュデータのクリア
					$this->clearCacheBySerial($this->serialNo);
					
					// 親ウィンドウを更新
					$this->gPage->updateParentWindow();
					
					// 運用ログを残す
					$eventParam = array(	M3_EVENT_HOOK_PARAM_CONTENT_TYPE	=> M3_VIEW_TYPE_BLOG,
											M3_EVENT_HOOK_PARAM_CONTENT_ID		=> $this->entryId,
											M3_EVENT_HOOK_PARAM_UPDATE_DT		=> date("Y/m/d H:i:s"));
					$this->writeUserInfoEvent(__METHOD__, 'ブログ記事を削除しました。タイトル: ' . $name, 2402, 'ID=' . $this->entryId, $eventParam);
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
				// 削除するブログ記事の情報を取得
				$ret = self::$_mainDb->getEntryBySerial($this->serialNo, $row, $categoryRow);
				if ($ret){
					$this->entryId = $row['be_id'];		// 記事ID
					$name = $row['be_name'];		// コンテンツ名前
				}
				
				$ret = self::$_mainDb->delEntryItemById($this->serialNo);
				if ($ret){		// データ削除成功のとき
					$this->setGuidanceMsg('データを削除しました');
					
					// ##### サムネールの削除 #####
//					$ret = blog_mainCommonDef::removeThumbnail($this->entryId);
					
					// アイキャッチ画像削除
					blog_mainCommonDef::removeEyecatchImage($this->entryId);
						
					// キャッシュデータのクリア
					$this->clearCacheBySerial($this->serialNo);
					
					// 親ウィンドウを更新
					$this->gPage->updateParentWindow();
					
					// 運用ログを残す
					$eventParam = array(	M3_EVENT_HOOK_PARAM_CONTENT_TYPE	=> M3_VIEW_TYPE_BLOG,
											M3_EVENT_HOOK_PARAM_CONTENT_ID		=> $this->entryId,
											M3_EVENT_HOOK_PARAM_UPDATE_DT		=> date("Y/m/d H:i:s"));
					$this->writeUserInfoEvent(__METHOD__, 'ブログ記事を削除しました。タイトル: ' . $name, 2402, 'ID=' . $this->entryId, $eventParam);
				} else {
					$this->setAppErrorMsg('データ削除に失敗しました');
				}
			}
		} else if ($act == 'preview'){		// プレビューデータ追加
			// ##### ウィジェット出力処理中断 ######
			$this->gPage->abortWidget();
			
			// プレビューに必要なデータを準備
			if (empty($name)) $name = blog_mainCommonDef::DEFAULT_TITLE_NO_TITLE;
			if (empty($entry_date) || empty($entry_time)){
				$entry_date = date("Y/m/d");		// 投稿日
				$entry_time = date("H:i:s");		// 投稿時間
			}
			
			// 保存データ作成
			$regDt = $this->convertToProperDate($entry_date) . ' ' . $this->convertToProperTime($entry_time);		// 投稿日時
			
			// 既存データ取得
			$ret = self::$_mainDb->getEntryItem($this->entryId, $this->langId, $row);
			if ($ret){			// データありの場合
				$userId = $row['be_regist_user_id'];	// 最初の投稿者
			} else {
				$userId = $this->_userId;				// 現在のユーザ
			}
			
			// プレビュー用の記事データを登録
			$otherParams = array();
			$otherParams['be_name']				= $name;
			$otherParams['be_blog_id']			= $this->blogId;
			$otherParams['be_status']			= $status;
			$otherParams['be_regist_user_id']	= $userId;				// 投稿者
			$otherParams['be_regist_dt']		= $regDt;
			$otherParams['be_show_comment']		= $showComment;
			$otherParams['be_receive_comment']	= $receiveComment;
			$otherParams['be_description']		= $desc;		// 簡易説明
			$otherParams['be_meta_description']	= $metaDesc;		// ページ要約(METAタグ)
			$otherParams['be_meta_keywords']	= $metaKeyword;		// ページキーワード(METAタグ)
			$otherParams['be_thumb_filename']	= $thumbFilename;		// サムネールファイル名
			$otherParams['be_related_content']	= $relatedContent;		// 関連コンテンツ
			$otherParams['be_option_fields']	= $this->serializeArray($this->fieldValueArray);				// ユーザ定義フィールド値
			$ret = self::$_mainDb->updateEntryPreview($this->entryId, $this->langId, $html, $html2, $this->categoryArray, $otherParams, $serial);
			if ($ret){
				// プレビュー用URL作成
				$previewUrl = $this->gEnv->getDefaultUrl() . '?' . M3_REQUEST_PARAM_BLOG_ENTRY_ID . '=' . $this->entryId . '-' . $this->_userId;
				$previewUrl .= '&' . M3_REQUEST_PARAM_OPERATION_COMMAND . '=' . M3_REQUEST_CMD_PREVIEW;
				if ($this->isMultiLang) $previewUrl .= '&' . M3_REQUEST_PARAM_OPERATION_LANG . '=' . $this->langId;		// 多言語対応の場合は言語IDを付加
				$this->gInstance->getAjaxManager()->addData('url', $previewUrl);
			}
			return;
		} else if ($act == 'get_history'){		// 履歴データの取得のとき
			$reloadData = true;		// データの再読み込み
		} else {	// 初期画面表示のとき
			// ##### ブログ記事IDが設定されているとき(他ウィジェットからの表示)は、データを取得 #####
			if (empty($this->entryId)){
				if (!empty($this->serialNo)){		// シリアル番号で指定の場合
					$reloadData = true;		// データの再読み込み
				}
			} else {
				// 多言語対応の場合は、言語を取得
				if ($this->isMultiLang){		// 多言語対応の場合
					$langId = $request->trimValueOf(M3_REQUEST_PARAM_OPERATION_LANG);		// lang値を取得
					if (!empty($langId)) $this->langId = $langId;
				}
		
				// ブログ記事を取得
				$ret = self::$_mainDb->getEntryItem($this->entryId, $this->langId, $row);
				if ($ret){
					$this->serialNo = $row['be_serial'];		// シリアル番号
					$reloadData = true;		// データの再読み込み
				} else {
					$this->serialNo = 0;
				}
			}
			if (empty($this->serialNo)){
				// 初期値設定
				// 所属ブログIDは親ウィンドウから引き継ぐ
				//$this->blogId = '';		// 所属ブログ
				$entry_date = date("Y/m/d");		// 投稿日
				$entry_time = date("H:i:s");		// 投稿時間
				$showComment = 1;				// コメントを表示するかどうか
				$receiveComment = 1;		// コメントを受け付けるかどうか
			}
		}
		
		// 設定データを再取得
		if ($reloadData){		// データの再ロード
			$ret = self::$_mainDb->getEntryBySerial($this->serialNo, $row, $categoryRow);
			if ($ret){
				$this->entryId = $row['be_id'];		// 記事ID
				$this->blogId = $row['be_blog_id'];		// 所属ブログ
				$name = $row['be_name'];				// タイトル
				$html = $row['be_html'];				// HTML
				$html = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $this->getUrl($this->gEnv->getRootUrl()), $html);// アプリケーションルートを変換
				$html2 = $row['be_html_ext'];				// HTML
				$html2 = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $this->getUrl($this->gEnv->getRootUrl()), $html2);// アプリケーションルートを変換
				$desc = $row['be_description'];		// 簡易説明
				$metaDesc = $row['be_meta_description'];		// ページ要約(METAタグ)
				$metaKeyword = $row['be_meta_keywords'];		// ページキーワード(METAタグ)
				$status = $row['be_status'];				// エントリー状況
				$reg_user = $row['reg_user_name'];				// 投稿者
				$entry_date = $this->timestampToDate($row['be_regist_dt']);		// 投稿日
				$entry_time = $this->timestampToTime($row['be_regist_dt']);		// 投稿時間
				//$update_user = $this->convertToDispString($row['lu_name']);// 更新者
				$update_user = $this->convertToDispString($row['update_user_name']);// 更新者
				$update_dt = $this->convertToDispDateTime($row['be_create_dt']);
				$start_date = $this->convertToDispDate($row['be_active_start_dt']);	// 公開期間開始日
				$start_time = $this->convertToDispTime($row['be_active_start_dt'], 1/*時分*/);	// 公開期間開始時間
				$end_date = $this->convertToDispDate($row['be_active_end_dt']);	// 公開期間終了日
				$end_time = $this->convertToDispTime($row['be_active_end_dt'], 1/*時分*/);	// 公開期間終了時間
				$showComment = $row['be_show_comment'];				// コメントを表示するかどうか
				$receiveComment = $row['be_receive_comment'];		// コメントを受け付けるかどうか
				$relatedContent = $row['be_related_content'];		// 関連コンテンツ
				
				// 記事カテゴリー取得
				$this->categoryArray = $this->getCategory($categoryRow);
				
				// 履歴番号
				if ($row['be_deleted']) $historyIndex = $row['be_history_index'];		// 旧データの場合のみ有効
				
				// ユーザ定義フィールド
				$this->fieldValueArray = $this->unserializeArray($row['be_option_fields']);
				
				// 前後のエントリーのシリアル番号を取得
				if (($this->isMultiLang && $this->langId == $this->gEnv->getDefaultLanguage()) || !$this->isMultiLang){		// // 多言語対応の場合はデフォルト言語が選択されている場合のみ処理を行う
					if ($this->gEnv->isAdminDirAccess()){		// 管理画面へのアクセスの場合
						$blogId = null;	// デフォルトブログ(ブログID空)を含むすべてのブログ記事にアクセス可能
					} else {
						$blogId = $this->blogId;		// 所属ブログ
					}
					$ret = self::$_mainDb->getPrevNextEntryByDate($row['be_regist_dt'], $prevRow, $nextRow, $blogId);
					if ($ret){
						if (!empty($prevRow)) $prevSerial = $prevRow['be_serial'];
						if (!empty($nextRow)) $nextSerial = $nextRow['be_serial'];
					}
				}
				
				// アイキャッチ画像
				$iconUrl = blog_mainCommonDef::getEyecatchImageUrl($row['be_thumb_filename'], self::$_configArray[blog_mainCommonDef::CF_ENTRY_DEFAULT_IMAGE], self::$_configArray[blog_mainCommonDef::CF_THUMB_TYPE], 's'/*sサイズ画像*/) . '?' . date('YmdHis');
				if (empty($row['be_thumb_filename'])){
					$iconTitle = 'アイキャッチ画像未設定';
				} else {
					$iconTitle = 'アイキャッチ画像';
				}
				$eyecatchImageTag = '<img src="' . $this->getUrl($iconUrl) . '" width="' . self::EYECATCH_IMAGE_SIZE . '" height="' . self::EYECATCH_IMAGE_SIZE . '" rel="m3help" alt="' . $iconTitle . '" title="' . $iconTitle . '" />';
			} else {		// データがないとき
				$this->serialNo = 0;
				$this->entryId = '0';		// 記事ID
				$this->blogId = '';		// 所属ブログ
				$name = '';				// タイトル
				$html = '';				// HTML
				$html2 = '';				// HTML
				$desc = '';		// 簡易説明
				$metaDesc = '';		// ページ要約(METAタグ)
				$metaKeyword = '';		// ページキーワード(METAタグ)
				//$status = 0;				// エントリー状況
				$status = 1;				// エントリー状況(編集中)
				$reg_user = '';				// 投稿者
				$entry_date = date("Y/m/d");		// 投稿日
				$entry_time = date("H:i:s");		// 投稿時間
				$update_user = '';// 更新者
				$update_dt = '';
				$start_date = '';	// 公開期間開始日
				$start_time = '';	// 公開期間開始時間
				$end_date = '';	// 公開期間終了日
				$end_time = '';	// 公開期間終了時間
				$showComment = 1;				// コメントを表示するかどうか
				$receiveComment = 1;		// コメントを受け付けるかどうか
				$relatedContent = '';		// 関連コンテンツ
				
				// 記事カテゴリー取得
				$this->categoryArray = array();
				
				// 履歴番号
				$historyIndex = -1;
				
				// ユーザ定義フィールド
				$this->fieldValueArray = array();
			}
		}
		// カテゴリーメニューを作成
		$this->createCategoryMenu($this->categoryCount);
		
		// ユーザ定義フィールドを作成
		$this->createUserFields($fieldInfoArray);
		
		// 所属ブログ
		if (empty($this->useMultiBlog)){
/*			$this->tmpl->setAttribute('show_blogid_area', 'visibility', 'visible');
			
			$blogName = $this->getBlogName($this->blogId);
			$this->tmpl->addVar("show_blogid_area", "blog_id", $this->blogId);	// 所属ブログID
			$this->tmpl->addVar("show_blogid_area", "blog_name", $blogName);	// 所属ブログ名
			*/
		} else {		// マルチブログを使用するとき
			$this->tmpl->setAttribute('select_blogid_area', 'visibility', 'visible');
			
			// ブログ選択メニュー作成
			$this->createBlogIdMenu();
		}
		
		// コメント機能の設定
		if ($this->useComment){
			$this->tmpl->setAttribute('show_comment_area', 'visibility', 'visible');
			
			$this->tmpl->addVar("show_comment_area", "show_comment", $this->convertToCheckedString($showComment));// コメントを表示するかどうか
			$this->tmpl->addVar("show_comment_area", "receive_comment", $this->convertToCheckedString($receiveComment));// コメントを受け付けるかどうか
		}
		
		// プレビュー用URL
		$previewUrl = $this->gEnv->getDefaultUrl() . '?' . M3_REQUEST_PARAM_BLOG_ENTRY_ID . '=' . $this->entryId;
		if ($historyIndex >= 0) $previewUrl .= '&' . M3_REQUEST_PARAM_HISTORY . '=' . $historyIndex;		// 履歴番号(旧データの場合のみ有効)
		$previewUrl .= '&' . M3_REQUEST_PARAM_OPERATION_COMMAND . '=' . M3_REQUEST_CMD_PREVIEW;
		if ($this->isMultiLang) $previewUrl .= '&' . M3_REQUEST_PARAM_OPERATION_LANG . '=' . $this->langId;		// 多言語対応の場合は言語IDを付加
		$this->tmpl->addVar('_widget', 'preview_url', $previewUrl);// プレビュー用URL(フロント画面)
		
		// CKEditor用のCSSファイルを読み込む
		$this->loadCKEditorCssFiles($previewUrl);
		
		// ### 入力値を再設定 ###
		$this->tmpl->addVar('_widget', 'entryid', $this->entryId);
		$this->tmpl->addVar("_widget", "item_name", $this->convertToDispString($name));		// 名前
		$this->tmpl->addVar("_widget", "item_html", $html);		// HTML
		$this->tmpl->addVar("_widget", "item_html2", $html2);		// HTML(続き)
		$this->tmpl->addVar("_widget", "desc", $desc);		// 簡易説明
		$this->tmpl->addVar("_widget", "meta_desc", $this->convertToDispString($metaDesc));		// ページ要約(METAタグ)
		$this->tmpl->addVar("_widget", "meta_keyword", $this->convertToDispString($metaKeyword));		// ページキーワード(METAタグ)
		if ($this->_isSmallDeviceOptimize){			// 小画面デバイス最適化の場合
			switch ($status){
				case 1:	$this->tmpl->addVar("_widget", "checked_edit", 'checked');		break;
				case 2:	$this->tmpl->addVar("_widget", "checked_public", 'checked');	break;
				case 3:	$this->tmpl->addVar("_widget", "checked_closed", 'checked');	break;
			}
		} else {
			switch ($status){
				case 1:	$this->tmpl->addVar("_widget", "selected_edit", 'selected');	break;
				case 2:	$this->tmpl->addVar("_widget", "selected_public", 'selected');	break;
				case 3:	$this->tmpl->addVar("_widget", "selected_closed", 'selected');	break;
			}
		}
		$this->tmpl->addVar("_widget", "entry_user", $this->convertToDispString($reg_user));	// 投稿者
		$this->tmpl->addVar("_widget", "entry_date", $entry_date);	// 投稿日
		$this->tmpl->addVar("_widget", "entry_time", $entry_time);	// 投稿時
		$this->tmpl->addVar("_widget", "update_user", $update_user);	// 更新者
		$this->tmpl->addVar("_widget", "update_dt", $update_dt);	// 更新日時
		$this->tmpl->addVar("_widget", "start_date", $start_date);	// 公開期間開始日
		$this->tmpl->addVar("_widget", "start_time", $start_time);	// 公開期間開始時間
		$this->tmpl->addVar("_widget", "end_date", $end_date);	// 公開期間終了日
		$this->tmpl->addVar("_widget", "end_time", $end_time);	// 公開期間終了時間
		$this->tmpl->addVar("_widget", "related_content", $relatedContent);	// 関連コンテンツ
		$this->tmpl->addVar("_widget", "eyecatch_image", $eyecatchImageTag);		// アイキャッチ画像
		
		// その他
		$this->tmpl->addVar("_widget", "serial", $this->serialNo);	// シリアル番号
		$this->tmpl->addVar('_widget', 'calendar_img', $this->getUrl($this->gEnv->getRootUrl() . self::CALENDAR_ICON_FILE));	// カレンダーアイコン
		$this->tmpl->addVar('_widget', 'current_widget', $this->_widgetId);		// AJAX用ウィジェットID
		
		// 投稿日時
		if ($this->_isSmallDeviceOptimize){			// 小画面デバイス最適化の場合
			$resistDtTimestamp = strtotime($row['be_regist_dt']);
			$weekTypeArray = array('日', '月', '火', '水', '木', '金', '土');// 曜日表示名
			$datetimeStr = $date = date(self::DATETIME_FORMAT, $resistDtTimestamp);
			$datetimeStr = str_replace('$1', $weekTypeArray[intval(date('w', $resistDtTimestamp))], $datetimeStr);
			$this->tmpl->addVar("_widget", "entry_datetime_text", '<div class="form-control-static m3config_item">' . $datetimeStr . '</div>');
		}
				
		// 記事状態ラベル
		if ($this->_isSmallDeviceOptimize){			// 小画面デバイス最適化の場合
			$statusLabelTag = '';
			switch ($status){
			case 1:				// 編集中
				$statusLabelTag = '&nbsp;<span id="status_label" class="label label-warning">編集中</span>';
				$statusPanelTitle = '編集中';		// 記事状態パネルのタイトル
				$statusPanelColor = 'panel-warning';				// 記事状態パネルのカラー
				break;
			case 2:			// 公開
				$statusLabelTag = '&nbsp;<span id="status_label" class="label label-success">公開</span>';
				$statusPanelTitle = '公開';		// 記事状態パネルのタイトル
				$statusPanelColor = 'panel-success';		// 記事状態パネルのカラー
				break;
			case 3:			// 非公開
				$statusLabelTag = '&nbsp;<span id="status_label" class="label label-warning">非公開</span>';
				$statusPanelTitle = '非公開';		// 記事状態パネルのタイトル
				$statusPanelColor = 'panel-warning';		// 記事状態パネルのカラー
				break;
			}
			$this->tmpl->addVar("_widget", "status_label", $statusLabelTag);
			$this->tmpl->addVar("_widget", "status_panel_color", $statusPanelColor);
			$this->tmpl->addVar("_widget", "status_panel_title", $statusPanelTitle);
		}
		 
		// 公開期間エリア表示ボタン
		$activeTermButton = $this->gDesign->createTermButton(''/*同画面*/, self::TOOLTIP_ACTIVE_TERM, self::TAG_ID_ACTIVE_TERM);
		$this->tmpl->addVar("_widget", "active_term_button", $activeTermButton);
		$this->tmpl->addVar("_widget", "tagid_active_term", self::TAG_ID_ACTIVE_TERM);
		if (!empty($start_date) || !empty($start_time) || !empty($end_date) || !empty($end_time)){
			$this->tmpl->addVar('_widget', 'show_active_term_area', 'true');		// 公開期間エリアの初期の表示状態
		} else {
			$this->tmpl->addVar('_widget', 'show_active_term_area', 'false');		// 公開期間エリアの初期の表示状態
		}
		
		// 前後エントリー移動ボタン
		if (!empty($prevSerial)){
			$this->tmpl->setAttribute('show_prev_button', 'visibility', 'visible');
			$this->tmpl->addVar('show_prev_button', 'serial', $prevSerial);
		}
		if (!empty($nextSerial)){
			$this->tmpl->setAttribute('show_next_button', 'visibility', 'visible');
			$this->tmpl->addVar('show_next_button', 'serial', $nextSerial);
		}

		// 入力フィールドの設定、共通項目のデータ設定
		if (empty($this->entryId)){		// 記事IDが0のときは、新規追加モードにする
			// 記事ID
			$this->tmpl->addVar('_widget', 'id', '新規');
			
			$this->tmpl->setAttribute('add_button', 'visibility', 'visible');
//			$this->tmpl->addVar('_widget', 'preview_btn_disabled', 'disabled');// プレビューボタン使用不可
			$this->tmpl->addVar('_widget', 'history_btn_disabled', 'disabled');// 履歴ボタン使用不可
			$this->tmpl->addVar('_widget', 'image_btn_disabled', 'disabled');// 画像ボタン使用不可
			$this->tmpl->addVar('_widget', 'schedule_btn_disabled', 'disabled');// 記事予約ボタン使用不可
			$this->tmpl->addVar('cancel_button', 'new_btn_disabled', 'disabled');	// 「新規」ボタン使用不可
			
			// デフォルト言語を最初に登録
			//$this->tmpl->addVar("default_lang", "default_lang", $defaultLangName);
			//$this->tmpl->setAttribute('default_lang', 'visibility', 'visible');
		} else {
			// 記事ID
			$itemId = $this->entryId;
			if ($historyIndex >= 0) $itemId .= '(' . ($historyIndex +1) . ')';// 履歴番号(旧データの場合のみ有効)
			$this->tmpl->addVar('_widget', 'id', $itemId);
			
			// ボタンの表示制御
			if (empty($this->serialNo)){		// 未登録データのとき
				// データ追加ボタン表示
				$this->tmpl->setAttribute('add_button', 'visibility', 'visible');
			} else {
				if ($historyIndex >= 0){		// 履歴データの場合
					$this->tmpl->setAttribute('update_history_button', 'visibility', 'visible');		// 「履歴データで更新」ボタン
				} else {
					// データ更新、削除ボタン表示
					$this->tmpl->setAttribute('delete_button', 'visibility', 'visible');// デフォルト言語以外はデータ削除
					$this->tmpl->setAttribute('update_button', 'visibility', 'visible');
				}
			}
			// 言語選択メニュー作成
			//if (!empty($this->entryId)){	// コンテンツが選択されているとき
			//	self::$_mainDb->getAllLang(array($this, 'langLoop'));
			//	$this->tmpl->setAttribute('select_lang', 'visibility', 'visible');
			//}
		}

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
		// レコード値取得
		$serial = $fetchedRow['be_serial'];
		$id		= $fetchedRow['be_id'];

		// カテゴリーを取得
		$categoryArray = array();
		$ret = self::$_mainDb->getEntryBySerial($serial, $row, $categoryRow);
		if ($ret){
			for ($i = 0; $i < count($categoryRow); $i++){
				if (function_exists('mb_strimwidth')){
					$categoryArray[] = mb_strimwidth($categoryRow[$i]['bc_name'], 0, self::CATEGORY_NAME_SIZE, '…');
				} else {
					$categoryArray[] = substr($categoryRow[$i]['bc_name'], 0, self::CATEGORY_NAME_SIZE) . '...';
				}
			}
		}
		$category = implode(',', $categoryArray);
		
		// 公開状態
		switch ($fetchedRow['be_status']){
			case 1:	$status = '<font color="orange">編集中</font>';	break;
			case 2:	$status = '<font color="green">公開</font>';	break;
			case 3:	$status = '非公開';	break;
		}
		// 参照数
		$updateViewCount = $this->gInstance->getAnalyzeManager()->getTotalContentViewCount(blog_mainCommonDef::VIEW_CONTENT_TYPE, $serial);		// 更新後からの参照数
		$totalViewCount = $this->gInstance->getAnalyzeManager()->getTotalContentViewCount(blog_mainCommonDef::VIEW_CONTENT_TYPE, 0, $id);		// 新規作成からの参照数
		$viewCountStr = $updateViewCount;
		if ($totalViewCount > $updateViewCount) $viewCountStr .= '(' . $totalViewCount . ')';		// 新規作成からの参照数がない旧仕様に対応
		
		// ユーザからの参照状況
		$now = date("Y/m/d H:i:s");	// 現在日時
		$startDt = $fetchedRow['be_active_start_dt'];
		$endDt = $fetchedRow['be_active_end_dt'];
		
		$isActive = false;		// 公開状態
		if ($fetchedRow['be_status'] == 2) $isActive = $this->_isActive($startDt, $endDt, $now);// 表示可能
		
		if ($this->_isSmallDeviceOptimize){			// 小画面デバイス最適化の場合
			if ($isActive){		// コンテンツが公開状態のとき
				$iconUrl = $this->gEnv->getRootUrl() . self::SMALL_ACTIVE_ICON_FILE;			// 公開中アイコン
				$iconTitle = '公開中';
			} else {
				$iconUrl = $this->gEnv->getRootUrl() . self::SMALL_INACTIVE_ICON_FILE;		// 非公開アイコン
				$iconTitle = '非公開';
			}
			$statusImg = '<img src="' . $this->getUrl($iconUrl) . '" width="' . self::SMALL_ICON_SIZE . '" height="' . self::SMALL_ICON_SIZE . '" rel="m3help" alt="' . $iconTitle . '" title="' . $iconTitle . '" />';
		} else {
			if ($isActive){		// コンテンツが公開状態のとき
				$iconUrl = $this->gEnv->getRootUrl() . self::ACTIVE_ICON_FILE;			// 公開中アイコン
				$iconTitle = '公開中';
			} else {
				$iconUrl = $this->gEnv->getRootUrl() . self::INACTIVE_ICON_FILE;		// 非公開アイコン
				$iconTitle = '非公開';
			}
			$statusImg = '<img src="' . $this->getUrl($iconUrl) . '" width="' . self::ICON_SIZE . '" height="' . self::ICON_SIZE . '" rel="m3help" alt="' . $iconTitle . '" title="' . $iconTitle . '" />';
		}
		
		// アイキャッチ画像
		$iconUrl = blog_mainCommonDef::getEyecatchImageUrl($fetchedRow['be_thumb_filename'], self::$_configArray[blog_mainCommonDef::CF_ENTRY_DEFAULT_IMAGE], self::$_configArray[blog_mainCommonDef::CF_THUMB_TYPE], 's'/*sサイズ画像*/) . '?' . date('YmdHis');
		if (empty($fetchedRow['be_thumb_filename'])){
			$iconTitle = 'アイキャッチ画像未設定';
		} else {
			$iconTitle = 'アイキャッチ画像';
		}
		$eyecatchImageTag = '<img src="' . $this->getUrl($iconUrl) . '" width="' . self::EYECATCH_IMAGE_SIZE . '" height="' . self::EYECATCH_IMAGE_SIZE . '" rel="m3help" alt="' . $iconTitle . '" title="' . $iconTitle . '" />';
		
		// 投稿日時
		$outputDate = $fetchedRow['be_regist_dt'];
		if ($this->_isSmallDeviceOptimize){			// 小画面デバイス最適化の場合
			if (intval(date('Y', strtotime($outputDate))) == $this->currentYear){		// 年号が今日の年号のとき
				$dispDate = $this->convertToDispDate($outputDate, 11/*年省略,0なし年月*/) . '<br />' . $this->convertToDispTime($outputDate, 1/*時分*/);
			} else {
				$dispDate = $this->convertToDispDate($outputDate, 3/*短縮年,0なし年月*/) . '<br />' . $this->convertToDispTime($outputDate, 1/*時分*/);
			}
		} else {
			$dispDate = $this->convertToDispDateTime($outputDate, 0/*ロングフォーマット*/, 10/*時分*/);
		}
		
		$row = array(
			'index' => $index,		// 項目番号
			'no' => $index + 1,													// 行番号
			'serial' => $serial,			// シリアル番号
			'id' => $this->convertToDispString($id),			// 記事ID
			'name' => $this->convertToDispString($fetchedRow['be_name']),		// 名前
			'lang' => $lang,													// 対応言語
			'eyecatch_image' => $eyecatchImageTag,									// アイキャッチ画像
			'status_img' => $statusImg,												// 公開状態
			'status' => $status,													// 公開状況
			'category' => $category,											// 記事カテゴリー
			//'view_count' => $totalViewCount,									// 参照数
			'view_count' => $this->convertToDispString($viewCountStr),			// 参照数
			'reg_user' => $this->convertToDispString($fetchedRow['lu_name']),	// 投稿者
//			'reg_date' => $this->convertToDispDateTime($fetchedRow['be_regist_dt'], 0/*ロングフォーマット*/, 10/*時分*/)		// 投稿日時
			'reg_date' => $dispDate
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
			if (!empty($srcRows[$i]['bw_category_id'])){
				$destArray[] = $srcRows[$i]['bw_category_id'];
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
				$categoryId = $this->categoryListData[$i]['bc_id'];
				$selected = '';
				if ($j < count($this->categoryArray) && $this->categoryArray[$j] == $categoryId){
					$selected = 'selected';
				}
				$menurow = array(
					'value'		=> $categoryId,			// カテゴリーID
					'name'		=> $this->categoryListData[$i]['bc_name'],			// カテゴリー名
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
	 * ブログ名を取得
	 *
	 * @param string $blogId		ブログID
	 * @return string				ブログ名
	 */
	function getBlogName($blogId)
	{
		$ret = self::$_mainDb->getBlogInfoById($blogId, $row);
		if ($ret){
			return $row['bl_name'];
		} else {
			return self::NO_BLOG_NAME;
		}
	}
	/**
	 * ブログ選択メニューを作成
	 *
	 * @return なし						
	 */
	function createBlogIdMenu()
	{
		if ($this->gEnv->isSystemManageUser()){		// システム運用ユーザのみ「ブログ選択なし」が利用可能
			$selected = '';
			if (empty($this->blogId)) $selected ='selected';
			$row = array(
				'value'    => $this->convertToDispString(''),			// ブログID
				'name'     => $this->convertToDispString(self::NO_BLOG_NAME),			// ブログ選択なし
				'selected' => $selected													// 選択中かどうか
			);
			$this->tmpl->addVars('blogid_list', $row);
			$this->tmpl->parseTemplate('blogid_list', 'a');
		}
				
		$ret = self::$_mainDb->getAvailableBlogId($rows);
		if ($ret){
			for ($i = 0; $i < count($rows); $i++){
				$selected = '';
				if ($rows[$i]['bl_id'] == $this->blogId) $selected = 'selected';
				$row = array(
					'value'    => $this->convertToDispString($rows[$i]['bl_id']),			// ブログID
					'name'     => $this->convertToDispString($rows[$i]['bl_name']),			// ブログ名
					'selected' => $selected														// 選択中かどうか
				);
				$this->tmpl->addVars('blogid_list', $row);
				$this->tmpl->parseTemplate('blogid_list', 'a');
			}
		}
	}
	/**
	 * 期間から公開可能かチェック
	 *
	 * @param timestamp	$startDt		公開開始日時
	 * @param timestamp	$endDt			公開終了日時
	 * @param timestamp	$now			基準日時
	 * @return bool						true=公開可能、false=公開不可
	 */
	function _isActive($startDt, $endDt, $now)
	{
		$isActive = false;		// 公開状態

		if ($startDt == $this->gEnv->getInitValueOfTimestamp() && $endDt == $this->gEnv->getInitValueOfTimestamp()){
			$isActive = true;		// 公開状態
		} else if ($startDt == $this->gEnv->getInitValueOfTimestamp()){
			if (strtotime($now) < strtotime($endDt)) $isActive = true;		// 公開状態
		} else if ($endDt == $this->gEnv->getInitValueOfTimestamp()){
			if (strtotime($now) >= strtotime($startDt)) $isActive = true;		// 公開状態
		} else {
			if (strtotime($startDt) <= strtotime($now) && strtotime($now) < strtotime($endDt)) $isActive = true;		// 公開状態
		}
		return $isActive;
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
			$entryId = $row['be_id'];		// 記事ID
			$urlParam = array();
			$urlParam[] = M3_REQUEST_PARAM_BLOG_ENTRY_ID . '=' . $entryId;		// 記事ID
			$urlParam[] = M3_REQUEST_PARAM_BLOG_ENTRY_ID_SHORT . '=' . $entryId;		// 記事ID略式
			$this->clearCache($urlParam);
		}
	}
	/**
	 * ユーザ定義フィールドを作成
	 *
	 * @param array $fields			フィールドID
	 * @return bool					true=成功、false=失敗
	 */
	function createUserFields($fields)
	{
		if (count($fields) == 0) return true;
		
		$this->tmpl->setAttribute('user_fields', 'visibility', 'visible');
		$keys = array_keys($fields);
		$fieldCount = count($keys);
		for ($i = 0; $i < $fieldCount; $i++){
			if ($i == 0) $this->tmpl->addVar('user_fields', 'type', 'first');		// 最初の行の場合
			
			// 入力値を取得
			$key = $keys[$i];
			$value = $this->fieldValueArray[$key];
			if (!isset($value)) $value = '';
			
			$row = array(
				'row_count'	=> $fieldCount,
				'field_id'	=> $this->convertToDispString($key),
				'value'		=> $this->convertToDispString($value)
			);
			$this->tmpl->addVars('user_fields', $row);
			$this->tmpl->parseTemplate('user_fields', 'a');
		}
		return true;
	}
	/**
	 * カテゴリーを並び順でソート
	 *
	 * @param array $categoryArray	ソート対象のカテゴリー
	 * @return bool					true=成功、false=失敗
	 */
	function sortCategory(&$categoryArray)
	{
		// 重複を除く
		$categoryArray = array_unique($categoryArray);
		
		// ソート情報作成
		$this->categorySortInfo = array();
		$categoryCount = count($this->categoryListData);
		for ($i = 0; $i < $categoryCount; $i++){
			$key = $this->categoryListData[$i]['bc_id'];
			$this->categorySortInfo[$key] = $i;
		}
		
		// カテゴリーをソート
		usort($categoryArray, array($this, 'sortCategoryCompare'));
		return true;
	}
	/**
	 * カテゴリーソート用
	 *
	 * @param int $a		比較値
	 * @param int $b		比較値
	 * @return int			比較結果
	 */
	function sortCategoryCompare($a, $b)
	{
		if ($this->categorySortInfo[$a] == $this->categorySortInfo[$b]) return 0;
		return ($this->categorySortInfo[$a] < $this->categorySortInfo[$b]) ? -1 : 1;
	}
}
?>
