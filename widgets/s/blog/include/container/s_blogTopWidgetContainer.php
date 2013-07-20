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
 * @copyright  Copyright 2006-2012 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: s_blogTopWidgetContainer.php 4799 2012-03-29 10:04:22Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/s_blogBaseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() .	'/s_blog_categoryDb.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/s_blog_commentDb.php');
require_once($gEnvManager->getCommonPath() . '/valueCheck.php');

class s_blogTopWidgetContainer extends s_blogBaseWidgetContainer
{
	private $categoryDb;	// DB接続オブジェクト
	private $commentDb;			// DB接続オブジェクト
	private $viewType;			// 表示タイプ
	private $blogId;			// ブログID
	private $categoryId;			// カテゴリーID
	private $year;				// 表示する年
	private $month;				// 表示する月
	private $day;				// 表示する日
	private $now;				// 現在日時
	private $currentDay;		// 現在日
	private $currentHour;		// 現在時間
	private $currentPageUrl;			// 現在のページURL
	private $currentRootUrl;			// 現在のページのルートURL
	private $imageWidth;				// 画像幅
	private $imageHeight;				// 画像高さ
	private $langId;		// 言語
	private $pageNo;				// ページ番号
	private $entryViewCount;// 記事表示数
	private $entryViewOrder;// 記事表示順
	private $receiveComment;		// コメントを受け付けるかどうか
	private $isOutputComment;		// コメントを出力するかどうか
	private $isExistsViewData;				// 表示データがあるかどうか
	private $viewExtEntry;			// 続きを表示するかどうか
	private $isSystemManageUser;	// システム運用可能ユーザかどうか
	private $pageTitle;				// 画面タイトル、パンくずリスト用タイトル
	private $useMultiBlog;			// マルチブログを使用するかどうか
	private $jQueryViewStyle;		// jQuery Mobileの表示スタイルを使用するかどうか
	private $title;					// 表示タイトル
	private $message;				// 表示メッセージ
	private $headTitle;		// METAタグタイトル
	private $headDesc;		// METAタグ要約
	private $headKeyword;	// METAタグキーワード
	private $addLib = array();		// 追加スクリプト
	private $useTitleListImage;		// タイトルリスト画像を使用するかどうか
	private $defaultTitleListImageUrl;			// デフォルトのタイトルリスト画像
	const CONTENT_TYPE = 'bg';
	const LINK_PAGE_COUNT		= 3;			// リンクページ数
	const MESSAGE_NO_ENTRY_TITLE = 'ブログ記事未登録';
	const MESSAGE_NO_ENTRY		= 'ブログ記事は登録されていません';				// ブログ記事が登録されていないメッセージ
	const MESSAGE_FIND_NO_ENTRY	= 'ブログ記事が見つかりません';
	const MESSAGE_EXT_ENTRY		= '続きを読む';					// 投稿記事に続きがある場合の表示
	const MESSAGE_EXT_ENTRY_PRE	= '…&nbsp;';							// 投稿記事に続きがある場合の表示
	const COMMENT_PERMA_HEAD	= 'comment-';		// コメントパーマリンク
	const COMMENT_TITLE		= ' についてのコメント';	// コメント用タイトル
	const NO_COMMENT_TITLE = 'タイトルなし';				// 未設定時のコメントタイトル
	const DEFAULT_VIEW_COUNT	= 10;				// デフォルトの表示記事数
	const ICON_SIZE = 32;		// アイコンのサイズ
	const EDIT_ICON_FILE = '/images/system/page_edit32.png';		// 編集アイコン
	const NEW_ICON_FILE = '/images/system/page_add32.png';		// 新規アイコン
	const DEFAULT_TITLE_SEARCH = '検索';		// 検索時のデフォルトタイトル
	const COOKIE_LIB = 'jquery.cookie';		// 名前保存用クッキーライブラリ
	const CSS_FILE = '/style.css';		// CSSファイルのパス
		
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->categoryDb = new s_blog_categoryDb();
		$this->commentDb = new s_blog_commentDb();
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
		$this->now = date("Y/m/d H:i:s");	// 現在日時
		$this->currentDay = date("Y/m/d");		// 日
		$this->currentHour = (int)date("H");		// 時間
		$this->currentPageUrl = $this->gEnv->createCurrentPageUrl();// 現在のページURL
		$this->currentRootUrl = $this->gEnv->getRootUrlByCurrentPage();			// 現在のページのルートURL
		$this->langId = $this->gEnv->getCurrentLanguage();
		$this->isSystemManageUser = $this->gEnv->isSystemManageUser();	// システム運用可能ユーザかどうか
		
		// ブログ定義値取得
		$this->entryViewCount	= $this->_getConfig(s_blogCommonDef::CF_ENTRY_VIEW_COUNT);// 記事表示数
		if (empty($this->entryViewCount)) $this->entryViewCount = self::DEFAULT_VIEW_COUNT;
		$this->entryViewOrder	= $this->_getConfig(s_blogCommonDef::CF_ENTRY_VIEW_ORDER);// 記事表示順
		$this->receiveComment = $this->_getConfig(s_blogCommonDef::CF_RECEIVE_COMMENT);		// コメントを受け付けるかどうか
		$this->useTitleListImage = $this->_getConfig(s_blogCommonDef::CF_USE_TITLE_LIST_IMAGE);		// タイトルリスト画像を使用するかどうか
		$this->defaultTitleListImageUrl = $this->_getConfig(s_blogCommonDef::CF_TITLE_LIST_IMAGE);			// タイトルリスト画像
		if (empty($this->defaultTitleListImageUrl)){		// 設定されていない場合はデフォルト画像
			$this->defaultTitleListImageUrl = $this->gEnv->getCurrentWidgetImagesUrl() . '/' . s_blogCommonDef::DEFAULT_TITLE_LIST_IMAGE;
		} else {
			$this->defaultTitleListImageUrl = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $this->gEnv->getRootUrl(), $this->defaultTitleListImageUrl);
		}
		$this->useMultiBlog = $this->_getConfig(s_blogCommonDef::CF_USE_MULTI_BLOG);// マルチブログを使用するかどうか
		$this->jQueryViewStyle = $this->_getConfig(s_blogCommonDef::CF_JQUERY_VIEW_STYLE);		// jQuery Mobileの表示スタイルを使用するかどうか
		list($width, $height) = explode('x', $this->_getConfig(s_blogCommonDef::CF_AUTO_RESIZE_IMAGE_MAX_SIZE));	// 画像の自動変換最大サイズ
		if (intval($width) <= 0) $width = self::DEFAULT_AUTO_RESIZE_IMAGE_MAX_SIZE;
		$this->imageWidth = $width;				// 画像幅
		$this->imageHeight = $height;				// 画像高さ
		if (intval($this->imageHeight) <= 0) $this->imageHeight = $width;
		
		// 入力値取得
		$act = $request->trimValueOf('act');
		$blogId = $request->trimValueOf(M3_REQUEST_PARAM_BLOG_ID);
		if (empty($blogId)) $blogId = $request->trimValueOf(M3_REQUEST_PARAM_BLOG_ID_SHORT);		// 略式ブログID
		$entryId = $request->trimValueOf(M3_REQUEST_PARAM_BLOG_ENTRY_ID);
		if (empty($entryId)) $entryId = $request->trimValueOf(M3_REQUEST_PARAM_BLOG_ENTRY_ID_SHORT);		// 略式ブログ記事ID
		$year = $request->trimValueOf('year');		// 年指定
		$month = $request->trimValueOf('month');		// 月指定
		$day = $request->trimValueOf('day');		// 日指定
		$keyword = $request->trimValueOf('keyword');// 検索キーワード
		$categoryId = $request->trimValueOf(M3_REQUEST_PARAM_CATEGORY_ID);		// カテゴリID
		$this->pageNo = $request->trimIntValueOf(M3_REQUEST_PARAM_PAGE_NO, '1');				// ページ番号
		
		// 表示タイプ取得
		$this->viewType = 'title';			// 表示タイプ(ブログタイトル)
		
		if (!empty($keyword)){
			$this->viewType = 'search';		// 表示タイプ(検索結果)
			$this->blogId	= $blogId;		// ブログIDでの絞り込み
		} else if (!empty($categoryId)){
			$this->viewType = 'category';		// 表示タイプ(カテゴリ選択)
			$this->blogId	= $blogId;		// ブログIDでの絞り込み
			$this->categoryId = $categoryId;			// カテゴリーID
		} else if (!empty($year) && !empty($month)){
			$this->viewType = 'term';		// 表示タイプ(期間選択)
			$this->blogId	= $blogId;		// ブログIDでの絞り込み
			$this->year = $year;		// 年指定
			$this->month = $month;		// 月指定
			$this->day = $day;		// 日指定
		} else if (!empty($entryId)){
			$this->viewType = 'entry';		// 表示タイプ(記事選択)
		} else if (!empty($blogId)){
			$this->viewType = 'multiblog';		// 表示タイプ(マルチブログトップ)
			$this->blogId	= $blogId;		// ブログIDでの絞り込み
		}

		// テンプレート設定
		$template = 'top_jquery.tmpl.html';
				
		switch ($this->viewType){
			case 'search':		// 検索結果
				$template = 'search.tmpl.html';
				break;
			case 'title':		// タイトル一覧
				if (empty($this->jQueryViewStyle)){
					$template = 'main.tmpl.html';
//				} else {
//					$template = 'top_jquery.tmpl.html';
				}
				break;
			case 'category':	// カテゴリー表示
				break;
			case 'term':	// 期間表示
				break;
			case 'entry':		// 記事表示
				$template = 'entry_jquery.tmpl.html';
				break;
		}
		return $template;
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
		$this->pageTitle = '';		// 画面タイトル、パンくずリスト用タイトル
		$this->title = '';		// 表示タイトル
		$this->message = '';	// 表示メッセージ
		
		switch ($this->viewType){
			case 'search':		// 検索結果
				$this->createSearchList($request);
				break;
			case 'title':		// タイトル一覧
				$this->createTitleList($request);
				break;
			case 'category':		// カテゴリー選択
				$this->createCategoryList($request);
				break;
			case 'term':		// 期間選択
				$this->createTermList($request);
				break;
			case 'entry':		// 記事表示
				$this->createEntry($request);
				break;
		}
			// HTMLサブタイトルを設定
		$this->gPage->setHeadSubTitle($this->pageTitle);
		
		// タイトルの設定
		if (!empty($this->title)){
			$this->tmpl->setAttribute('show_title', 'visibility', 'visible');		// 年月表示
			$this->tmpl->addVar("show_title", "title", $this->convertToDispString($this->title));
		}
		
		// メッセージを表示
		if (!empty($this->message)){
			$this->tmpl->setAttribute('message', 'visibility', 'visible');
			$this->tmpl->addVar("message", "message", $this->convertToDispString($this->message));
		}
	}
	/**
	 * タイトル一覧画面表示
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param								なし
	 */
	function createTitleList($request)
	{
		$startDt = '';
		$endDt = '';
//			$showTopContent = false;		// トップコンテンツを表示するかどうか
				$targetBlogId = null;		// 表示対象のブログID
				if ($this->useMultiBlog){		// マルチブログのとき
					// ブログIDが存在しないときはトップコンテンツを表示
					if (empty($blogId)){
					//	$showTopContent = true;
					} else {
						$ret = self::$_mainDb->isActiveBlogInfo($blogId);
						if ($ret){			// ブログ情報が存在するとき
							$targetBlogId = $blogId;
							
							// マルチブログ時のみの処理
							$ret = self::$_mainDb->getBlogInfoById($blogId, $row);
							if ($ret){
								// HTMLメタタグの設定
								$this->headTitle .= $row['bl_meta_title'];
								if (empty($this->headTitle)) $this->headTitle = $row['bl_name'];
								$this->headDesc .= $row['bl_meta_description'];
								$this->headKeyword .= $row['bl_meta_keywords'];
							}
						} else {
							//$showTopContent = true;
						}
					}
				}

		// ##### タイトルリスト作成 #####
		// 総数を取得
		$totalCount = self::$_mainDb->getEntryItemsCount($this->now, $startDt, $endDt, $this->langId, $targetBlogId);

		// リンク文字列作成、ページ番号調整
		// マルチブログのときはブログIDを付加する
		$multiBlogParam = '';		// マルチブログ時の追加パラメータ
		if ($this->useMultiBlog) $multiBlogParam = '&' . M3_REQUEST_PARAM_BLOG_ID . '=' . $targetBlogId;
		$pageLink = $this->createPageLink($this->pageNo, $totalCount, $this->entryViewCount, $this->currentPageUrl . $multiBlogParam);

		// 記事一覧作成
		self::$_mainDb->getEntryItems($this->entryViewCount, $this->pageNo, $this->now, 0/* 期間で指定 */, $startDt/*期間開始*/, $endDt/*期間終了*/,
						$this->langId, $this->entryViewOrder, array($this, 'itemsLoop'), $targetBlogId);
		
		if ($this->isExistsViewData){
			// ページリンクを埋め込む
			if (!empty($pageLink)){
				$this->tmpl->setAttribute('page_link', 'visibility', 'visible');		// リンク表示
				$this->tmpl->addVar("page_link", "page_link", $pageLink);
			}
		} else {
			// ブログ記事データがないときはデータなしメッセージ追加
			$this->title = self::MESSAGE_NO_ENTRY_TITLE;
			$this->message = self::MESSAGE_NO_ENTRY;			// ユーザ向けメッセージ
		}

		// ##### トップコンテンツを表示 #####
		if ($this->pageNo <= 1){		// 最初のページのみ表示
			$topContent = $this->_getConfig(s_blogCommonDef::CF_TOP_CONTENT);// トップコンテンツ
			if (!empty($topContent)){
				$this->tmpl->setAttribute('show_top_content', 'visibility', 'visible');
				$this->tmpl->addVar("show_top_content", "content", $topContent);
			}
		}
	}
	/**
	 * 検索一覧画面表示
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param								なし
	 */
	function createSearchList($request)
	{
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
				$totalCount = self::$_mainDb->searchEntryItemsCountByKeyword($this->now, $parsedKeywords, $this->langId);

				// リンク文字列作成、ページ番号調整
				$pageLink = $this->createPageLink($this->pageNo, $totalCount, $this->entryViewCount, $this->currentPageUrl . '&act=search&keyword=' . urlencode($keyword));
				
				// 記事一覧を表示
				self::$_mainDb->searchEntryItemsByKeyword($this->entryViewCount, $this->pageNo, $this->now, $parsedKeywords, $this->langId, array($this, 'searchItemsLoop'));
				
				if ($this->isExistsViewData){
					// ページリンクを埋め込む
					if (!empty($pageLink)){
						$this->tmpl->setAttribute('page_link', 'visibility', 'visible');		// リンク表示
						$this->tmpl->addVar("page_link", "page_link", $pageLink);
					}
					$this->message = '検索キーワード：' . $keyword;
				} else {	// 検索結果なしの場合
					$this->tmpl->setAttribute('entrylist', 'visibility', 'hidden');
					$this->message = self::MESSAGE_FIND_NO_ENTRY;
				}
			}
			$this->pageTitle = self::DEFAULT_TITLE_SEARCH;		// 画面タイトル、パンくずリスト用タイトル
	}
	/**
	 * カテゴリー選択での一覧画面表示
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param								なし
	 */
	function createCategoryList($request)
	{
		// 総数を取得
		$totalCount = self::$_mainDb->getEntryItemsCountByCategory($this->now, $this->categoryId, $this->langId);

		// リンク文字列作成、ページ番号調整
		$pageLink = $this->createPageLink($this->pageNo, $totalCount, $this->entryViewCount, $this->currentPageUrl . '&' . M3_REQUEST_PARAM_CATEGORY_ID . '=' . $this->categoryId);
		
		// 記事一覧を表示
		self::$_mainDb->getEntryItemsByCategory($this->entryViewCount, $this->pageNo, $this->now, $this->categoryId, $this->langId, $this->entryViewOrder, array($this, 'itemsLoop'));

		// タイトルの設定
		$ret = $this->categoryDb->getCategoryByCategoryId($this->categoryId, $this->gEnv->getDefaultLanguage(), $row);
		if ($ret) $this->title = $row['bc_name'];
		
		// ブログ記事データがないときはデータなしメッセージ追加
		if ($this->isExistsViewData){
			// ページリンクを埋め込む
			if (!empty($pageLink)){
				$this->tmpl->setAttribute('page_link', 'visibility', 'visible');		// リンク表示
				$this->tmpl->addVar("page_link", "page_link", $pageLink);
			}
		} else {
			$this->title = self::MESSAGE_NO_ENTRY_TITLE;
			$this->message = self::MESSAGE_NO_ENTRY;			// ユーザ向けメッセージ
		}
		// 画面タイトル
		$this->pageTitle = $this->title;
	}
	/**
	 * 期間選択での一覧画面表示
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param								なし
	 */
	function createTermList($request)
	{
		if (empty($this->day)){		// 月指定のとき
			$startDt = $this->year . '/' . $this->month . '/1';
			$endDt = $this->getNextMonth($this->year . '/' . $this->month) . '/1';
			
			// 総数を取得
			$totalCount = self::$_mainDb->getEntryItemsCount($this->now, $startDt, $endDt, $this->langId);

			// リンク文字列作成、ページ番号調整
			$pageLink = $this->createPageLink($this->pageNo, $totalCount, $this->entryViewCount, $this->currentPageUrl . '&year=' . $this->year . '&month=' . $this->month);
		
			// 記事一覧作成
			self::$_mainDb->getEntryItems($this->entryViewCount, $this->pageNo, $this->now, $entryId, $startDt/*期間開始*/, $endDt/*期間終了*/, $this->langId, $this->entryViewOrder, array($this, 'itemsLoop'));

			if ($this->isExistsViewData){
				// ページリンクを埋め込む
				if (!empty($pageLink)){
					$this->tmpl->setAttribute('page_link', 'visibility', 'visible');		// リンク表示
					$this->tmpl->addVar("page_link", "page_link", $pageLink);
				}
			}
			// 年月の表示
			$this->title = $this->year . '年 ' . $this->month . '月';
			
			// ブログ記事データがないときはデータなしメッセージ追加
			if (!$this->isExistsViewData){
				$this->message = self::MESSAGE_NO_ENTRY;			// ユーザ向けメッセージ
			}
		} else {
			$startDt = $this->year . '/' . $this->month . '/' . $this->day;
			$endDt = $this->getNextDay($this->year . '/' . $this->month . '/' . $this->day);
			
			// 総数を取得
			$totalCount = self::$_mainDb->getEntryItemsCount($this->now, $startDt, $endDt, $this->langId);

			// リンク文字列作成、ページ番号調整
			$pageLink = $this->createPageLink($this->pageNo, $totalCount, $this->entryViewCount, $this->currentPageUrl . '&year=' . $this->year . '&month=' . $this->month . '&day=' . $this->day);
			
			// 記事一覧作成
			self::$_mainDb->getEntryItems($this->entryViewCount, $this->pageNo, $this->now, $entryId, $startDt/*期間開始*/, $endDt/*期間終了*/, $this->langId, $this->entryViewOrder, array($this, 'itemsLoop'));
			
			if ($this->isExistsViewData){
				// ページリンクを埋め込む
				if (!empty($pageLink)){
					$this->tmpl->setAttribute('page_link', 'visibility', 'visible');		// リンク表示
					$this->tmpl->addVar("page_link", "page_link", $pageLink);
				}
			}
			
			// 年月日の表示
			$this->title = $this->year . '年 ' . $this->month . '月 ' . $this->day . '日';
			
			// ブログ記事データがないときはデータなしメッセージ追加
			if (!$this->isExistsViewData){
				$this->message = self::MESSAGE_NO_ENTRY;			// ユーザ向けメッセージ
			}
		}
		// 画面タイトル
		$this->pageTitle = $this->title;
	}
	/**
	 * 記事画面表示
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param								なし
	 */
	function createEntry($request)
	{
		// 初期設定値
		$sendButtonLabel = 'コメントを投稿';		// 送信ボタンラベル
		$sendStatus = 0;		// 送信状況
		$regUserId		= $this->gEnv->getCurrentUserId();			// 記事投稿ユーザ
		$regDt			= date("Y/m/d H:i:s");						// 投稿日時
		$this->isOutputComment = false;// コメントを出力するかどうか
		$startDt = '';
		$endDt = '';
		
		$act = $request->trimValueOf('act');
		$entryId = $request->trimValueOf(M3_REQUEST_PARAM_BLOG_ENTRY_ID);
		if (empty($entryId)) $entryId = $request->trimValueOf(M3_REQUEST_PARAM_BLOG_ENTRY_ID_SHORT);		// 略式ブログ記事ID
		$blogId = $request->trimValueOf(M3_REQUEST_PARAM_BLOG_ID);
		if (empty($blogId)) $blogId = $request->trimValueOf(M3_REQUEST_PARAM_BLOG_ID_SHORT);		// 略式ブログID
/*		
		$year = $request->trimValueOf('year');		// 年指定
		$month = $request->trimValueOf('month');		// 月指定
		$day = $request->trimValueOf('day');		// 日指定
		$keyword = $request->trimValueOf('keyword');// 検索キーワード
		$category = $request->trimValueOf(M3_REQUEST_PARAM_CATEGORY_ID);		// カテゴリID
*/
		// コメントの入力
		$commentTitle = $request->trimValueOf('title');
		$name = $request->trimValueOf('name');
		$email = $request->trimValueOf('email');
		$url = $request->trimValueOf('url');
		$body = $request->trimValueOf('body');
		
		$showDefault = false;			// デフォルト状態での表示

		if ($act == 'view'){			// 記事を表示のとき
			// コメントを受け付けるときは、コメント入力欄を表示
			// ***** 記事を表示する前に呼び出す必要あり *****
			if (!empty($this->receiveComment)){
				$this->tmpl->setAttribute('entry_footer', 'visibility', 'visible');		// コメントへのリンク
			}
/*			if (!empty($category)){				// カテゴリー指定のとき

			} else if (!empty($year) && !empty($month)){

			}*/
			$this->pageTitle = $this->title;		// カテゴリー名を画面タイトルにする
		} else if ($act == 'checkcomment'){		// コメント確認のとき
			// 入力チェック
			$maxCommentLength = $this->_getConfig(s_blogCommonDef::CF_MAX_COMMENT_LENGTH);// コメント最大長
			if ($maxCommentLength == '') $maxCommentLength = self::DEFAULT_COMMENT_LENGTH;
			if (empty($maxCommentLength)){		// 空のときは長さのチェックなし
				$this->checkInput($body, 'コメント内容');
			} else {
				$this->checkLength($body, 'コメント内容', $maxCommentLength);
			}
			$this->checkMailAddress($email, 'Eメール', true);

			// エラーなしの場合は確認画面表示
			if ($this->getMsgCount() == 0){
				// タイトル作成
				$ret = self::$_mainDb->getEntryItem($entryId, $this->langId, $row);
				if ($ret) $this->title = $row['be_name'] . self::COMMENT_TITLE;
				$this->pageTitle = $this->title;		// 画面タイトル
				
				// ハッシュキー作成
				$postTicket = md5(time() . $this->gAccess->getAccessLogSerialNo());
				$request->setSessionValue(M3_SESSION_POST_TICKET, $postTicket);		// セッションに保存
				$this->tmpl->addVar("_widget", "ticket", $postTicket);				// 画面に書き出し
				
				$this->setGuidanceMsg('この内容でコメントを投稿しますか?');
				
				// 入力の変更不可
				$sendButtonLabel = 'コメントを投稿';		// 送信ボタンラベル
				$sendStatus = 1;// 送信状況を「確定」に変更
				$this->tmpl->addVar("add_comment", "title_disabled", 'readonly');
				$this->tmpl->addVar("add_comment", "name_disabled", 'readonly');
				$this->tmpl->addVar("add_comment", "email_disabled", 'readonly');
				$this->tmpl->addVar("add_comment", "url_disabled", 'readonly');
				$this->tmpl->addVar("add_comment", "body_disabled", 'readonly');
				$this->tmpl->setAttribute('cancel_button', 'visibility', 'visible');		// キャンセルボタン表示
				
				// ### コメント入力欄の表示 ###
				$this->tmpl->setAttribute('add_comment', 'visibility', 'visible');// コメント入力欄表示
				$this->tmpl->addVar("add_comment", "send_button_label", $sendButtonLabel);// 送信ボタンラベル
				$this->tmpl->addVar("add_comment", "send_status", $sendStatus);// 送信状況
				$this->tmpl->setAttribute('entrylist', 'visibility', 'hidden');// 記事表示停止
			} else {
				$showDefault = true;			// デフォルト状態での表示
			}
			
			// 入力値を戻す
			$this->tmpl->addVar("add_comment", "title", $this->convertToDispString($commentTitle));
			$this->tmpl->addVar("add_comment", "name", $this->convertToDispString($name));
			$this->tmpl->addVar("add_comment", "email", $this->convertToDispString($email));
			$this->tmpl->addVar("add_comment", "url", $this->convertToDispString($url));
			$this->tmpl->addVar("add_comment", "body", $this->convertToDispString($body));
			$this->tmpl->addVar("_widget", "entry_id", $this->convertToDispString($entryId));		// 記事ID
		} else if ($act == 'sendcomment'){	// コメント受信のとき
			$postTicket = $request->trimValueOf('ticket');		// POST確認用
			$ret = false;
			if (!empty($entryId) && !empty($body) &&
				!empty($postTicket) && $postTicket == $request->getSessionValue(M3_SESSION_POST_TICKET)){		// 正常なPOST値のとき
				// コメントを保存
				$ret = $this->commentDb->addCommentItem($entryId, $this->langId, $commentTitle, $body, $url, $name, $email, $regUserId, $regDt, $newSerial);
				
				// 記事更新日を更新
				if ($ret) $ret = self::$_mainDb->updateEntryDt($entryId, $this->langId);
			}
			if ($ret){
				$this->setGuidanceMsg('コメントを投稿しました');
			} else {
				$this->setUserErrorMsg('コメントの投稿に失敗しました');
			}
			$showDefault = true;			// デフォルト状態での表示
			$request->unsetSessionValue(M3_SESSION_POST_TICKET);		// セッション値をクリア
		} else if ($act == 'sendcancel'){	// コメントキャンセルのとき
			$showDefault = true;			// デフォルト状態での表示
		} else {
			$showDefault = true;			// デフォルト状態での表示
		}
		// ##### デフォルトの表示では、最新のn件の記事を表示または、記事ID指定で1つの記事を表示
		if ($showDefault){
			// 記事ID指定のときは記事を取得
			$ret = self::$_mainDb->getEntryItem($entryId, $this->langId, $row);
			if ($ret){
				// コメントを受け付けるときは、コメント入力欄を表示
				if (!empty($this->receiveComment)) $this->isOutputComment = true;// コメントを出力するかどうか
				
				// コメントの表示制御
				if ($this->isOutputComment){		// コメントを出力のとき
					$this->tmpl->setAttribute('show_comment', 'visibility', 'visible');		// 既存コメントを表示
					$this->tmpl->addVar("_widget", "entry_id", $entryId);		// 記事を指定
				
					// ### コメント入力欄の表示 ###
					//$ret = self::$_mainDb->getEntryItem($entryId, $this->langId, $row);
					//if ($ret && !empty($row['be_receive_comment'])){		// コメントを受け付ける場合のみ表示
					//if (!empty($entryRow) && !empty($entryRow['be_receive_comment'])){		// コメントを受け付ける場合のみ表示
					if (!empty($row['be_receive_comment'])){		// コメントを受け付ける場合のみ表示
						$this->tmpl->setAttribute('add_comment', 'visibility', 'visible');
						$this->tmpl->addVar("add_comment", "send_button_label", $sendButtonLabel);// 送信ボタンラベル
						$this->tmpl->addVar("add_comment", "send_status", $sendStatus);// 送信状況
					
						// 名前保存用のスクリプトライブラリ追加
						$this->tmpl->setAttribute('init_cookie', 'visibility', 'visible');
						$this->tmpl->setAttribute('update_cookie', 'visibility', 'visible');
						$this->addLib[] = self::COOKIE_LIB;
					}
				}
				// 記事、コメントの表示
				$this->outputEntry($row);
				
				// ページのタイトル設定
				$this->pageTitle = $row['be_name'];		// 記事レコードがあるとき

				// マルチブログのときはパンくずリストにブログ名を追加
				if ($this->useMultiBlog){
					$blogUrl = $this->getUrl($this->gEnv->getDefaultSmartphoneUrl() . '?' . M3_REQUEST_PARAM_BLOG_ID . '=' . $row['bl_id']);
					$this->gPage->setHeadSubTitle($row['bl_name'], $blogUrl);
				}
			} else {
				// 記事がないときはコメントを隠す
				$this->tmpl->setAttribute('entrylist', 'visibility', 'hidden');
				$this->tmpl->setAttribute('add_comment', 'visibility', 'hidden');
			
				// ブログ記事データがないときはデータなしメッセージ追加
				$this->title = self::MESSAGE_NO_ENTRY_TITLE;
				$this->message = self::MESSAGE_NO_ENTRY;			// ユーザ向けメッセージ
			}
		}

		// 運用可能ユーザの場合は編集用ボタンを表示
		if (self::$_canEditEntry){		// 検索画面以外で記事編集権限ありのとき
			// 共通ボタン作成
			$buttonList = '';
			
			// 新規作成ボタン
			$iconUrl = $this->gEnv->getRootUrl() . self::NEW_ICON_FILE;		// 新規アイコン
			$iconTitle = '新規';
			$editImg = '<img src="' . $this->getUrl($iconUrl) . '" width="' . self::ICON_SIZE . '" height="' . self::ICON_SIZE . '" alt="' . $iconTitle . '" title="' . $iconTitle . '" style="border:none;" />';
			$buttonList .= '<div style="text-align:right;"><span style="line-height:0;"><a href="javascript:void(0);" onclick="editEntry(0);">' . $editImg . '</a></span></div>';
			
			$this->tmpl->setAttribute('button_list', 'visibility', 'visible');
			$this->tmpl->addVar("button_list", "button_list", $buttonList);
			
			// 設定画面表示用のスクリプトを埋め込む
			$multiBlogParam = '';		// マルチブログ時の追加パラメータ
			if ($this->useMultiBlog){
				// ブログIDが空のときは取得
				if (empty($blogId)){
					$bId = '';
					$blogLibObj = $this->gInstance->getObject(self::BLOG_OBJ_ID);
					if (isset($blogLibObj)) $bId = $blogLibObj->getBlogId();
					if (!empty($bId)) $multiBlogParam = '&' . M3_REQUEST_PARAM_BLOG_ID . '=' . $bId;
				} else {
					$multiBlogParam = '&' . M3_REQUEST_PARAM_BLOG_ID . '=' . $blogId;
				}
			}
			
			if ($this->isSystemManageUser){		// 管理者権限ありのとき
				$editUrl = $this->getConfigAdminUrl('openby=simple&task=' . self::TASK_ENTRY_DETAIL . $multiBlogParam);
				$configUrl = $this->getConfigAdminUrl('openby=other');
				$this->tmpl->setAttribute('admin_script', 'visibility', 'visible');
				$this->tmpl->addVar("admin_script", "edit_url", $editUrl);
				$this->tmpl->addVar("admin_script", "config_url", $configUrl);
			} else {			// 投稿ユーザのとき
				// 編集用画面へのURL作成
				$urlparam  = M3_REQUEST_PARAM_OPERATION_COMMAND . '=' . M3_REQUEST_CMD_DO_WIDGET . '&';
				$urlparam .= M3_REQUEST_PARAM_WIDGET_ID . '=' . $this->gEnv->getCurrentWidgetId() .'&';
				//$urlparam .= 'openby=simple&task=' . self::TASK_ENTRY_DETAIL . $multiBlogParam;
				$urlparam .= 'openby=other&task=' . self::TASK_ENTRY_DETAIL . $multiBlogParam;
				$editUrl = $this->gEnv->getDefaultSmartphoneUrl() . '?' . $urlparam;

				// 設定画面表示用のスクリプトを埋め込む
				$this->tmpl->setAttribute('edit_script', 'visibility', 'visible');
				$this->tmpl->addVar("edit_script", "edit_url", $this->getUrl($editUrl));
			}
		}
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
		return $this->getUrl($this->gEnv->getCurrentWidgetCssUrl() . self::CSS_FILE);
	}
	/**
	 * 記事を出力
	 *
	 * @param array $row		表示レコード
	 * @return					なし
	 */
	function outputEntry($row)
	{
		$entryId = $row['be_id'];// 記事ID
		$title = $row['be_name'];// タイトル
		$date = $row['be_regist_dt'];// 日付
		$showComment = $row['be_show_comment'];				// コメントを表示するかどうか
		$blogId = $row['be_blog_id'];						// ブログID

		// コメントを取得
		$commentCount = $this->commentDb->getCommentCountByEntryId($entryId, $this->langId);	// コメント総数
		if ($this->isOutputComment){// コメントを出力のとき
			// コメントの内容を取得
			$ret = $this->commentDb->getCommentByEntryId($entryId, $this->langId, $commentRow);
			if ($ret){
				//$this->tmpl->clearTemplate('commentlist');
				for ($i = 0; $i < count($commentRow); $i++){
					$permalink = '#' . self::COMMENT_PERMA_HEAD . $commentRow[$i]['bo_no'];		// コメントパーマリンク
					$commentTitle = $commentRow[$i]['bo_name'];			// コメントタイトル
					if (empty($commentTitle)) $commentTitle = self::NO_COMMENT_TITLE;
					$userName = $this->convertToDispString($commentRow[$i]['bo_user_name']);	// 投稿ユーザは入力値を使用
					$url = $this->convertToDispString($commentRow[$i]['bo_url']);
					$commentInfo = $this->convertToDispString($commentRow[$i]['bo_regist_dt']) . '&nbsp;&nbsp;' . $userName;
					if (!empty($url)) $commentInfo .= '<br />' . $url;
					$comment = $this->convertToPreviewText($this->convertToDispString($commentRow[$i]['bo_html']));		// 改行コードをbrタグに変換
					$commentVars = array(
						'permalink'		=> $permalink,		// コメントパーマリンク
						'comment_title'		=> $this->convertToDispString($commentTitle),			// コメントタイトル
						'comment'		=> $comment,			// コメント内容
						'user_name'		=> $userName,			// 投稿ユーザ名
						'comment_info'	=> $commentInfo						// コメント情報
					);
					$this->tmpl->addVars('commentlist', $commentVars);
					$this->tmpl->parseTemplate('commentlist', 'a');
				}
			} else {	// コメントなしのとき
				//$this->tmpl->clearTemplate('commentlist');
				$commentVars = array(
					'comment'		=> 'コメントはありません',			// コメント内容
					'comment_info'	=> ''						// コメント情報
				);
				$this->tmpl->addVars('commentlist', $commentVars);
				$this->tmpl->parseTemplate('commentlist', 'a');
			}
		}

		// 記事へのリンクを生成
		$linkUrl = $this->getUrl($this->gEnv->getDefaultSmartphoneUrl() . '?'. M3_REQUEST_PARAM_BLOG_ENTRY_ID . '=' . $entryId, true/*リンク用*/);
		$link = '<div><a href="' . $this->convertUrlToHtmlEntity($linkUrl . '#comment') . '" >コメント(' . $commentCount . ')</a></div>';
		
		// HTMLを出力(出力内容は特にエラーチェックしない)
		$entryText = $row['be_html'];
		if (!empty($row['be_html_ext'])) $entryText = $row['be_html_ext'];// 続きがある場合は続きを出力

		// マクロ変換
		$contentInfo = array();
		$contentInfo[M3_TAG_MACRO_CONTENT_TITLE] = $row['be_name'];			// コンテンツ置換キー(タイトル)
		$contentInfo[M3_TAG_MACRO_CONTENT_UPDATE_DT] = $row['be_create_dt'];		// コンテンツ置換キー(更新日時)
		$contentInfo[M3_TAG_MACRO_CONTENT_REGIST_DT] = $row['be_regist_dt'];		// コンテンツ置換キー(登録日時)
		$entryText = $this->convertM3ToHtml($entryText, true/*改行コーをbrタグに変換*/, $contentInfo);

		// 画像サイズを調整
		$entryText = $this->gInstance->getTextConvManager()->autoConvPcContentToSmartphone($entryText, $this->currentRootUrl/*現在のページのルートURL*/, 
																				M3_VIEW_TYPE_BLOG/*ブログコンテンツ*/, $row['be_create_dt']/*コンテンツ作成日時*/,
																				$this->imageWidth/*最大画像幅*/, $this->imageHeight/*最大画像高さ*/);
																		
		// ##### 記事のフッター部 #####
		// コメントを表示しないときはリンクを表示しない
/*		if (empty($showComment)) $link = '';
		$this->tmpl->clearTemplate('entry_footer');
		$row = array(
//			'permalink' => $this->convertUrlToHtmlEntity($linkUrl),	// パーマリンク
			'link' => $link		// コメントへのリンク
		);
		$this->tmpl->addVars('entry_footer', $row);
		$this->tmpl->parseTemplate('entry_footer', 'a');*/

		// コンテンツ編集権限がある場合はボタンを表示
		$buttonList = '';
		if (self::$_canEditEntry){		// 編集権限があるとき
			$iconUrl = $this->gEnv->getRootUrl() . self::EDIT_ICON_FILE;		// 編集アイコン
			$iconTitle = '編集';
			$editImg = '<img src="' . $this->getUrl($iconUrl) . '" width="' . self::ICON_SIZE . '" height="' . self::ICON_SIZE . '" alt="' . $iconTitle . '" title="' . $iconTitle . '" style="border:none;" />';
			$buttonList = '<div style="text-align:right;"><span style="line-height:0;"><a href="javascript:void(0);" onclick="editEntry(' . $row['be_serial'] . ');">' . $editImg . '</a></span></div>';
		}

		// ブログへのリンクを作成
		$blogLink = '';
		if ($this->useMultiBlog && !empty($blogId)){
			$blogName = $row['bl_name'];// ブログ名
			$blogUrl = $this->getUrl($this->gEnv->getDefaultSmartphoneUrl() . '?' . M3_REQUEST_PARAM_BLOG_ID . '=' . $blogId);
			$blogLink = '<span style="font-size:smaller;"><a href="' . $this->convertUrlToHtmlEntity($blogUrl) . '" >' . $this->convertToDispString($blogName) . '</a></span>&nbsp;&nbsp;&nbsp;&nbsp;';
		}

		$row = array(
			'permalink' => $this->convertUrlToHtmlEntity($linkUrl),	// パーマリンク
			'title' => $title,
			'date' => $date,			// 日付
			'entry' => $entryText,	// 投稿記事
			'button_list' => $buttonList,	// 記事編集ボタン
			'blog_link' => $blogLink			// マルチブログへのリンク
		);
		$this->tmpl->addVars('entrylist', $row);
		$this->tmpl->parseTemplate('entrylist', 'a');
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
		$entryId = $fetchedRow['be_id'];// 記事ID
		$title = $fetchedRow['be_name'];// タイトル
		$date = $fetchedRow['be_regist_dt'];// 日付
		$showComment = $fetchedRow['be_show_comment'];				// コメントを表示するかどうか
		$blogId = $fetchedRow['be_blog_id'];						// ブログID
		
		// 記事へのリンクを生成
		$linkUrl = $this->getUrl($this->gEnv->getDefaultSmartphoneUrl() . '?'. M3_REQUEST_PARAM_BLOG_ENTRY_ID . '=' . $entryId, true/*リンク用*/);
		$link = '<div><a href="' . $this->convertUrlToHtmlEntity($linkUrl . '#comment') . '" >コメント(' . $commentCount . ')</a></div>';
		
		// HTMLを出力(出力内容は特にエラーチェックしない)
		$entryText = $fetchedRow['be_html'];
		if ($this->viewExtEntry){			// 続きを表示するかどうか
			if (!empty($fetchedRow['be_html_ext'])) $entryText = $fetchedRow['be_html_ext'];// 続きがある場合は続きを出力
//			$entryText = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $this->getUrl($this->gEnv->getRootUrl()), $entryText);// アプリケーションルートを変換
		} else {
			// 続きがある場合はリンクを付加
//			$entryText = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $this->getUrl($this->gEnv->getRootUrl()), $entryText);// アプリケーションルートを変換
			if (!empty($fetchedRow['be_html_ext'])){
				$entryText .= self::MESSAGE_EXT_ENTRY_PRE . '<a href="' . $this->convertUrlToHtmlEntity($linkUrl) . '" >' . self::MESSAGE_EXT_ENTRY . '</a>';
			}
		}
		// マクロ変換
		$contentInfo = array();
		$contentInfo[M3_TAG_MACRO_CONTENT_TITLE] = $fetchedRow['be_name'];			// コンテンツ置換キー(タイトル)
		$contentInfo[M3_TAG_MACRO_CONTENT_UPDATE_DT] = $fetchedRow['be_create_dt'];		// コンテンツ置換キー(更新日時)
		$contentInfo[M3_TAG_MACRO_CONTENT_REGIST_DT] = $fetchedRow['be_regist_dt'];		// コンテンツ置換キー(登録日時)
		$entryText = $this->convertM3ToHtml($entryText, true/*改行コーをbrタグに変換*/, $contentInfo);

		// 画像サイズを調整
		$entryText = $this->gInstance->getTextConvManager()->autoConvPcContentToSmartphone($entryText, $this->currentRootUrl/*現在のページのルートURL*/, 
																				M3_VIEW_TYPE_BLOG/*ブログコンテンツ*/, $fetchedRow['be_create_dt']/*コンテンツ作成日時*/,
																				$this->imageWidth/*最大画像幅*/, $this->imageHeight/*最大画像高さ*/);

		// ブログへのリンクを作成
		$blogLink = '';
		if ($this->useMultiBlog && !empty($blogId)){
			$blogName = $fetchedRow['bl_name'];// ブログ名
			$blogUrl = $this->getUrl($this->gEnv->getDefaultSmartphoneUrl() . '?' . M3_REQUEST_PARAM_BLOG_ID . '=' . $blogId);
			$blogLink = '<span style="font-size:smaller;"><a href="' . $this->convertUrlToHtmlEntity($blogUrl) . '" >' . $this->convertToDispString($blogName) . '</a></span>&nbsp;&nbsp;&nbsp;&nbsp;';
		}
		
		// タイトルリスト作成
		$optionTag = '';
		if ($this->useTitleListImage){		// タイトルリスト画像を使用するかどうか
			// サムネール画像作成
			$thumbUrl = $this->createThumbnail($fetchedRow['be_html'], $fetchedRow['be_id'], $fetchedRow['be_create_dt']);
			if (empty($thumbUrl)) $thumbUrl = $this->defaultTitleListImageUrl;
			$optionTag .= '<img src="' . $this->getUrl($thumbUrl) . '" />';		// デフォルトのタイトルリスト画像
		}
		$row = array(
			'permalink' => $this->convertUrlToHtmlEntity($linkUrl),	// パーマリンク
			'title' => $title,
			'date' => $date,			// 日付
			'entry' => $entryText,	// 投稿記事
			'blog_link' => $blogLink,			// マルチブログへのリンク
			'option'	=> $optionTag			// オプションのタグ
		);
		$this->tmpl->addVars('entrylist', $row);
		$this->tmpl->parseTemplate('entrylist', 'a');
		$this->isExistsViewData = true;				// 表示データがあるかどうか
		return true;
	}
	/**
	 * 取得したコンテンツ項目をテンプレートに設定する
	 *
	 * @param int		$index			行番号
	 * @param array		$fetchedRow		取得行
	 * @param object	$param			任意使用パラメータ
	 * @return bool						trueを返すとループ続行。falseを返すとその時点で終了。
	 */
	function searchItemsLoop($index, $fetchedRow)
	{
		$title = $fetchedRow['be_name'];			// タイトル
		$blogId = $fetchedRow['be_blog_id'];						// ブログID
		
		// 記事へのリンクを生成
		//$linkUrl = $this->currentPageUrl . '&entryid=' . $fetchedRow['be_id'];
		$linkUrl = $this->getUrl($this->gEnv->getDefaultSmartphoneUrl() . '?'. M3_REQUEST_PARAM_BLOG_ENTRY_ID . '=' . $fetchedRow['be_id'], true/*リンク用*/);
		$link = '<a href="' . $this->convertUrlToHtmlEntity($linkUrl) . '" >' . $title . '</a>';
		
		// 日付
		$date = $fetchedRow['be_regist_dt'];

		// HTMLを出力(出力内容は特にエラーチェックしない)
		// 続きがある場合はリンクを付加
		$entryText = $fetchedRow['be_html'];
//		$entryText = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $this->getUrl($this->gEnv->getRootUrl()), $entryText);// アプリケーションルートを変換
		if (!empty($fetchedRow['be_html_ext'])){
			$entryText .= self::MESSAGE_EXT_ENTRY_PRE . '<a href="' . $this->convertUrlToHtmlEntity($linkUrl) . '" >' . self::MESSAGE_EXT_ENTRY . '</a>';
		}
		// マクロ変換
		$contentInfo = array();
		$contentInfo[M3_TAG_MACRO_CONTENT_TITLE] = $fetchedRow['be_name'];			// コンテンツ置換キー(タイトル)
		$contentInfo[M3_TAG_MACRO_CONTENT_UPDATE_DT] = $fetchedRow['be_create_dt'];		// コンテンツ置換キー(更新日時)
		$contentInfo[M3_TAG_MACRO_CONTENT_REGIST_DT] = $fetchedRow['be_regist_dt'];		// コンテンツ置換キー(登録日時)
		$entryText = $this->convertM3ToHtml($entryText, true/*改行コーをbrタグに変換*/, $contentInfo);
		
		// ブログへのリンクを作成
		$blogLink = '';
		if ($this->useMultiBlog && !empty($blogId)){
			$blogName = $fetchedRow['bl_name'];// ブログ名
			$blogUrl = $this->getUrl($this->gEnv->getDefaultSmartphoneUrl() . '?' . M3_REQUEST_PARAM_BLOG_ID . '=' . $blogId);
			$blogLink = '<span style="font-size:smaller;"><a href="' . $this->convertUrlToHtmlEntity($blogUrl) . '" >' . $this->convertToDispString($blogName) . '</a></span>&nbsp;&nbsp;&nbsp;&nbsp;';
		}
		
		$row = array(
			'title' => $link,			// リンク付きタイトル
			'date' => $date,			// 日付
			'entry' => $entryText,	// 投稿記事
			'blog_link' => $blogLink			// マルチブログへのリンク
		);
		$this->tmpl->addVars('entrylist', $row);
		$this->tmpl->parseTemplate('entrylist', 'a');
		$this->isExistsViewData = true;				// 表示データがあるかどうか
		return true;
	}
	/**
	 * ページリンク作成
	 *
	 * @param int $pageNo			ページ番号(1～)。ページ番号が範囲外にある場合は自動的に調整
	 * @param int $totalCount		総項目数
	 * @param int $viewItemCount	1ページあたりの項目数
	 * @param string $baseUrl		リンク用のベースURL
	 * @return string				リンクHTML
	 */
	function createPageLink(&$pageNo, $totalCount, $viewItemCount, $baseUrl)
	{
		// 表示するページ番号の修正
		$pageCount = (int)(($totalCount -1) / $viewItemCount) + 1;		// 総ページ数
		if ($pageNo < 1) $pageNo = 1;
		if ($pageNo > $pageCount) $pageNo = $pageCount;

		// jQueryタイプのときは属性を追加
		$linkOption = '';
		if (!empty($this->jQueryViewStyle)) $linkOption = 'data-role="button" ';
		
		// ページング用リンク作成
		$pageLink = '';
		if ($pageCount > 1){	// ページが2ページ以上のときリンクを作成
			// ページ数1から「LINK_PAGE_COUNT」までのリンクを作成
			$maxPageCount = $pageCount < self::LINK_PAGE_COUNT ? $pageCount : self::LINK_PAGE_COUNT;
			for ($i = 1; $i <= $maxPageCount; $i++){
				if ($i == $pageNo){
					if (empty($this->jQueryViewStyle)){			// デフォルトのスタイルの場合
						$link = '<span class="page_no">' . $i . '</span>';
					} else {
						$link = '<span class="page_no"><a href="#" data-role="button" class="ui-disabled">' . $i . '</a></span>';
					}
				} else {
					$linkUrl = $this->getUrl($baseUrl . '&page=' . $i, true/*リンク用*/);
					$link = '<span class="page_no"><a href="' . $this->convertUrlToHtmlEntity($linkUrl) . '" ' . $linkOption . '>' . $i . '</a></span>';
				}
				$pageLink .= $link;
			}
			// 残りは「...」表示
			if ($pageCount > self::LINK_PAGE_COUNT){
				if (empty($this->jQueryViewStyle)){			// デフォルトのスタイルの場合
					$pageLink .= '<span class="page_no">...</span>';
				} else {
					$pageLink .= '<span class="page_no"><a href="#" data-role="button" class="ui-disabled">...</a></span>';
				}
			}
		}
		if ($pageNo > 1){		// 前ページがあるとき
			$linkUrl = $this->getUrl($baseUrl . '&page=' . ($pageNo -1), true/*リンク用*/);
			$link = '<span class="page_prev"><a href="' . $this->convertUrlToHtmlEntity($linkUrl) . '" ' . $linkOption . '>前へ</a></span>';
			$pageLink = $link . $pageLink;
		}
		if ($pageNo < $pageCount){		// 次ページがあるとき
			$linkUrl = $this->getUrl($baseUrl . '&page=' . ($pageNo +1), true/*リンク用*/);
			$link = '<span class="page_next"><a href="' . $this->convertUrlToHtmlEntity($linkUrl) . '" ' . $linkOption . '>次へ</a></span>';
			$pageLink .= $link;
		}
		return $pageLink;
	}
	/**
	 * サムネール画像を作成
	 *
	 * @param string $srcHtml		画像を検索するHTML
	 * @param int    $entryId		ブログ記事ID
	 * @param timestamp $updateDt	記事の更新日付
	 * @return string				画像URL
	 */
	function createThumbnail($srcHtml, $entryId, $updateDt)
	{
		// サムネール
		$thumbFile = '/widgets/blog/' . M3_DIR_NAME_SMARTPHONE . '/thumb/' . $entryId . '_' . s_blogCommonDef::TITLE_LIST_IMAGE_SIZE . '.' . s_blogCommonDef::DEFAULT_THUMB_IMAGE_EXT;
		$thumbUrl = $this->gEnv->getResourceUrl() . $thumbFile;
		$thumbPath = $this->gEnv->getResourcePath() . $thumbFile;

		// ファイルと日時をチェック
		$createImage = true;
		if (file_exists($thumbPath) && strtotime($updateDt) < filemtime($thumbPath)){
			$createImage = false;
		}
		
		// サムネールを作成
		if ($createImage){
			// ブログ記事から最初の画像を取得
			// 読み飛ばしが指定されている場合は飛ばす
			$regex = '/<img[^<]*?src\s*=\s*[\'"]+(.+?)[\'"]+[^>]*?>/si';
			if (preg_match($regex, $srcHtml, $matches)){		// 画像が取得できたとき
				// 相対パスを取得
				$imageUrl = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $this->gEnv->getRootUrl(), $matches[1]);
				if (strStartsWith($imageUrl, '/')){
					$relativePath = $this->gEnv->getRelativePathToSystemRootUrl($this->gEnv->getDocumentRootUrl() . $imageUrl);
				} else {
					if ($this->gEnv->isSystemUrlAccess($imageUrl)){		// システム内のファイルのとき
						$relativePath = $this->gEnv->getRelativePathToSystemRootUrl($imageUrl);
					}
				}
				
				if (strStartsWith($relativePath, '/' . M3_DIR_NAME_RESOURCE . '/')){		// リソースディレクトリ以下のリソースのみ変換
					$imagePath = $this->gEnv->getSystemRootPath() . $relativePath;		// 元画像のファイルパス
					
					// 画像格納用のディレクトリ作成
					$destDir = dirname($thumbPath);
					if (!file_exists($destDir)) mkdir($destDir, M3_SYSTEM_DIR_PERMISSION, true/*再帰的*/);

					//$ret = $this->createThumb($imagePath, $thumbPath, s_blogCommonDef::TITLE_LIST_IMAGE_SIZE, IMAGETYPE_PNG);
					$ret = $this->gInstance->getImageManager()->createThumb($imagePath, $thumbPath, s_blogCommonDef::TITLE_LIST_IMAGE_SIZE, IMAGETYPE_PNG);
					if (!$ret) $thumbUrl = '';
				} else {
					$thumbUrl = '';
				}
			} else {		// 画像が取得できないとき
				// サムネール画像を削除
				if (file_exists($thumbPath)) @unlink($thumbPath);
				$thumbUrl = '';
			}
		}
		return $thumbUrl;
	}
}
?>
