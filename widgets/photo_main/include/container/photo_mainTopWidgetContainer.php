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
 * @copyright  Copyright 2006-2013 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: photo_mainTopWidgetContainer.php 5631 2013-02-10 11:34:25Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/photo_mainBaseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/photo_mainDb.php');
require_once($gEnvManager->getCurrentWidgetDbPath() .	'/photo_categoryDb.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/photo_commentDb.php');
require_once($gEnvManager->getCommonPath() . '/valueCheck.php');

class photo_mainTopWidgetContainer extends photo_mainBaseWidgetContainer
{
	private $db;			// DB接続オブジェクト
	private $categoryDb;	// DB接続オブジェクト
	private $serialArray = array();		// 表示されている項目シリアル番号
	private $photoArray;	// 取得した写真
	private $thumbnailSize;		// デフォルトサムネールサイズ
	private $thumbailSizeArray;	// サムネールサイズ
	private $notfoundThumbnailUrl;		// サムネール画像が見つからない場合の画像
	private $viewCount;		// 表示項目数
	private $viewOrder;	// 表示順
	private $shortTitleLength;		// 略式写真タイトルの長さ
	private $photoId;		// 写真ID
	private $startNo;		// 先頭項目番号
	private $currentDay;		// 現在日
	private $currentHour;		// 現在時間
	private $fieldInfoArray;	// 検索条件項目
	private $categoryInfoArray;		// カテゴリー表示情報
	private $categoryArray;		// 選択中のカテゴリー
	private $authorArray;		// 選択中の著作者
	private $sortKey;		// ソートキー
	private $sortDirection;		// ソート方向
	private $menuAuthorRows;	// メニュー用の著作者情報
	private $authCategory;		// 参照可能なカテゴリー
	private $authCategoryStr;	// 参照可能なカテゴリー(表示用)
	private $ecObj;					// EC共通ライブラリオブジェクト
	private $isExistsProduct;		// 商品が存在するかどうか
	private $prePrice;		// 価格表示用
	private $postPrice;		// 価格表示用
	private $categoryWithPwdArray;	// パスワードが必要なカテゴリー
	const CSS_FILE = '/style.css';		// CSSファイルのパス
	const NOT_FOUND_ICON_FILE = 		'/images/system/notfound128.gif';		// 写真が見つからないアイコン
	const BACK_ICON_FILE = '/images/system/back.png';		// 「戻る」アイコン
	const PREV_ICON_FILE = '/images/system/previous.png';		// 「前へ」アイコン
	const NEXT_ICON_FILE = '/images/system/next.png';		// 「次へ」アイコン
	const PERMALINK_ICON_FILE = '/images/system/permalink.png';		// 「パーマリンク」アイコン
	const LOAD_ICON_FILE = '/loader.gif';			// ロード中アイコン
	const RATY_IMAGE_DIR = '/jquery/raty/img';		// jquery.raty画像パス
	const LINK_PAGE_COUNT		= 10;			// リンクページ数
	const BACK_BUTTON_TITLE = '戻る';		// 「戻る」ボタンタイトル
	const PREV_BUTTON_TITLE = '前へ';		// 「前へ」ボタンタイトル
	const NEXT_BUTTON_TITLE = '次へ';		// 「次へ」ボタンタイトル
	const SEARCH_FIELD_HEAD = 'photo_main_item';			// フィールド名の先頭文字列
	const SEARCH_FIELD_CLASS_HEAD = 'photo_main_';			// フィールドクラスの先頭文字列
	const TARGET_WIDGET = 'photo_shop';			// 画像購入ウィジェット
	const PRODUCT_CLASS = 'photo';			// 商品クラス
	const TAX_TYPE = 'sales';						// 課税タイプ(外税)
	const PRODUCT_TYPE_DOWNLOAD = 'download';		// 商品タイプ
	const STANDARD_PRICE = 'selling';				// 通常価格
	const PRICE_OBJ_ID = "eclib";		// 価格計算オブジェクトID
	const DEFAULT_CURRENCY 		= 'JPY';			// デフォルト通貨
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->notfoundThumbnailUrl = $this->getUrl($this->gEnv->getRootUrl() . self::NOT_FOUND_ICON_FILE);
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
		$this->photoId = $request->trimValueOf(M3_REQUEST_PARAM_PHOTO_ID);
		if (empty($this->photoId)) $this->photoId = $request->trimValueOf(M3_REQUEST_PARAM_PHOTO_ID_SHORT);		// 略式ID
		
		$act = $request->trimValueOf('act');
		if ($act == 'search'){
			return 'main_list.tmpl.html';
		} else if ($act == 'inputcart'){			// カートに入れる
			return '';
		} else {
			if (empty($this->photoId)){
				return 'main_list.tmpl.html';
			} else {
				return 'photo_detail.tmpl.html';
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
		$now = date("Y/m/d H:i:s");	// 現在日時
		$this->currentDay = date("Y/m/d");		// 日
		$this->currentHour = (int)date("H");		// 時間
		$this->currentPageUrl = $this->gEnv->createCurrentPageUrl();// 現在のページURL
		
		$this->viewCount = self::$_configArray[photo_mainCommonDef::CF_PHOTO_LIST_ITEM_COUNT];	// 表示項目数
		$this->viewOrder = self::$_configArray[photo_mainCommonDef::CF_PHOTO_LIST_ORDER];	// 表示順
		$this->shortTitleLength = self::$_configArray[photo_mainCommonDef::CF_PHOTO_TITLE_SHORT_LENGTH];		// 略式写真タイトルの長さ
		
		// POST,GET値取得
		$act = $request->trimValueOf('act');

		if ($act == 'getlist'){		// 画像一覧Ajax取得
			$this->getList($request);
		} else if ($act == 'getdetail'){// 画像詳細Ajax取得
			$this->getDetail($request);
		} else if ($act == 'rate'){// 評価コメント投票
			$this->rate($request);
		} else if ($act == 'inputcart'){			// カートに入れる
			$this->inputCart($request);
		} else if ($act == 'downloadimage'){			// 画像ダウンロード
			$this->downloadImage($request);
		} else {		// 詳細、一覧画面
			if (empty($this->photoId)){		
				$this->showList($request);
			} else {// 詳細画面
				$this->showDetail($request);
			}
		}
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
		// 画像カテゴリーのパスワード制御を行う場合は暗号化ライブラリを追加
		if (empty(self::$_configArray[photo_mainCommonDef::CF_PHOTO_CATEGORY_PASSWORD])){
			return '';
		} else {
			return 'md5';
		}
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
	 * 一覧画面表示
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param								なし
	 */
	function showList($request)
	{
		// DBオブジェクト
		$this->categoryDb = new photo_categoryDb();
		
		// 初期設定
		$paramObj = $this->getWidgetParamObj();
		if (empty($paramObj)){		// 保存値がないとき
			$this->fieldInfoArray = array();			// 項目定義
		
			// デフォルトの検索テンプレート作成
			$searchTemplate = $this->getParsedTemplateData(photo_mainCommonDef::DEFAULT_SEARCH_AREA_TMPL, array($this, '_makeSearcheTemplate'));// デフォルト用の検索テンプレート
		} else {
			$searchTemplate = $paramObj->searchTemplate;		// 検索用テンプレート
			if (!empty($paramObj->fieldInfo)) $this->fieldInfoArray = $paramObj->fieldInfo;			// 項目定義
		}
		// カテゴリーメニュー情報取得
		$menuCategoryArray = array();			// メニュー項目用カテゴリー
		$fieldCount = count($this->fieldInfoArray);		// 検索項目数
		for ($i = 0; $i < $fieldCount; $i++){
			// 表示するカテゴリIDを取得
			$itemType = $this->fieldInfoArray[$i]->itemType;
			$itemCategory = $this->fieldInfoArray[$i]->category;
			if ($itemType == 'category'){
				if (!empty($itemCategory) && !in_array($itemCategory, $menuCategoryArray)) $menuCategoryArray[] = $itemCategory;	// 親カテゴリー
				//if (!in_array($itemCategory, $menuCategoryArray)) $menuCategoryArray[] = $itemCategory;	// 親カテゴリー
			}
		}
		
		// POST,GET値取得
		$keyword = $request->trimValueOf(M3_REQUEST_PARAM_KEYWORD);		// 検索キーワード
		$paramPageNo = $request->trimValueOf(M3_REQUEST_PARAM_PAGE_NO);		// ページ番号パラメータ
		$pageNo = $request->trimIntValueOf(M3_REQUEST_PARAM_PAGE_NO, '1');				// ページ番号
		$category = $request->trimValueOf('category');		// カテゴリー
		$author = $request->trimValueOf('author');			// 著作者
		$sort = $request->trimValueOf('sort');		// ソート順
		$act = $request->trimValueOf('act');
		
		// 画像カテゴリーにパスワード制限を掛けている場合の処理
		if (!empty(self::$_configArray[photo_mainCommonDef::CF_PHOTO_CATEGORY_PASSWORD])){
			$this->authCategory = array();		// 参照可能なカテゴリー
				
			// ブラウザのクライアントIDを取得
			$clientId = $this->gAccess->getClientId();
			if (!empty($clientId)){
				// クライアントパラメータオブジェクトを取得
				$clientObj = $this->getClientParamObj($clientId, $this->gEnv->getCurrentWidgetId());
				if (!empty($clientObj) && !empty($clientObj->authCategory)){
					$this->authCategory = explode(',', $clientObj->authCategory);
				}
			
				if ($act == 'addcategory'){			// 参照カテゴリー追加のとき
					$accessCategoryId = $request->trimValueOf('access_category');			// 参照するカテゴリー
					$password = $request->trimValueOf('password');

					// 画像カテゴリーのパスワードをチェック
					$ret = $this->categoryDb->getCategoryByCategoryId($accessCategoryId, $this->_langId, $row);
					if ($ret && !empty($row['hc_password']) && $row['hc_password'] == $password){
						// 画像カテゴリーを追加
						if (!in_array($accessCategoryId, $this->authCategory)) $this->authCategory[] = $accessCategoryId;
					
						// クライアントパラメータを更新
						$clientObj->authCategory = implode(',', $this->authCategory);
						$ret = $this->updateClientParamObj($clientId, $this->gEnv->getCurrentWidgetId(), $clientObj);
						$this->setGuidanceMsg('認証に成功しました');
					} else {
						// 認証に失敗した画像カテゴリーは削除する
						$destAuthCategory = array();
						for ($i = 0; $i < count($this->authCategory); $i++){
							if ($this->authCategory[$i] != $accessCategoryId) $destAuthCategory[] = $this->authCategory[$i];
						}
						$this->authCategory = $destAuthCategory;
					
						// クライアントパラメータを更新
						$clientObj->authCategory = implode(',', $this->authCategory);
						$ret = $this->updateClientParamObj($clientId, $this->gEnv->getCurrentWidgetId(), $clientObj);
						$this->setAppErrorMsg('認証に失敗しました');
					}
				}
			}
			$this->tmpl->setAttribute('category_script', 'visibility', 'visible');		// 画像カテゴリースクリプト表示

			// 画像カテゴリー選択メニュー作成
			$this->categoryWithPwdArray = array();	// パスワードが必要なカテゴリー
			$this->authCategoryStr = '';
			$this->categoryDb->getAllCategory($this->_langId, array($this, 'categoryListLoop'), true);
			
			$categoryWithPwdCount = count($this->categoryWithPwdArray);
			if ($categoryWithPwdCount > 1){		// パスワードが必要なカテゴリーが複数の場合
				// 認証されていないカテゴリーがある場合のみ表示
				for ($i = 0; $i < $categoryWithPwdCount; $i++){
					if (!in_array($this->categoryWithPwdArray[$i], $this->authCategory)) break;		// 認証されていないとき
				}
				if ($i < $categoryWithPwdCount){
					$this->tmpl->setAttribute('category_area', 'visibility', 'visible');		// 画像カテゴリーエリア表示
			
					// 参照可能カテゴリー表示
					$this->authCategoryStr = rtrim($this->authCategoryStr, ', ');
					$this->tmpl->addVar("category_area", "auth_category_list", $this->convertToDispString($this->authCategoryStr));
				}
			} else if ($categoryWithPwdCount == 1){		// 参照可能なカテゴリーが単数のとき
				if (!in_array($this->categoryWithPwdArray[0], $this->authCategory)){		// 認証されていないとき
					$this->tmpl->addVar("single_category_area", "value", $this->convertToDispString($this->categoryWithPwdArray[0]));
					$this->tmpl->setAttribute('single_category_area', 'visibility', 'visible');		// 画像カテゴリーエリア表示
				}
			}
		}
		
		// データエラーチェック
		$this->checkInputValues($category, $author, $sort);
		
		// URLパラメータ作成
		$urlParams = $this->createUrlParams($keyword, $category, $author, $sort);
		
		// キーワード分割
		$parsedKeywords = $this->gInstance->getTextConvManager()->parseSearchKeyword($keyword);

		// ##### 検索キーワードを記録 #####
		if (empty($paramPageNo)){			// 検索を実行した時点のみ記録
			for ($i = 0; $i < count($parsedKeywords); $i++){
				$this->gInstance->getAnalyzeManager()->logSearchWord($this->gEnv->getCurrentWidgetId(), $parsedKeywords[$i]);
			}
		}

		// 写真数を取得
		$limitedCategory = $this->authCategory;
		if (!is_null($limitedCategory) && count($limitedCategory) == 0) $limitedCategory = array('0');		// 空の場合はダミーのカテゴリーIDを追加
		$totalCount = self::$_mainDb->searchPhotoItemCount($this->_langId, null/*開始日時*/, null/*終了日時*/, $parsedKeywords/*キーワード*/,
															$this->categoryArray/*カテゴリー*/, $this->authorArray/*撮影者*/, $limitedCategory);

		// リンク文字列作成、ページ番号調整
		$this->calcPage($pageNo, $totalCount, $this->viewCount, $pageCount, $startNo, $endNo);
		
		// 検索条件を設定した場合で画像数が0のときはメッセージ表示
		if ($startNo > $endNo){
			if (!empty($parsedKeywords) || !empty($this->categoryArray) || !empty($this->authorArray)) $this->setUserErrorMsg('画像が見つかりません');
		}
		
		// ページリンク作成
		$pageLink = $this->createPageLink($pageNo, $pageCount, self::LINK_PAGE_COUNT, $this->gEnv->createCurrentPageUrl(), $urlParams);
		if (!empty($pageLink)){
			$this->tmpl->setAttribute('page_link_top', 'visibility', 'visible');		// リンク表示
			$this->tmpl->setAttribute('page_link_bottom', 'visibility', 'visible');		// リンク表示
			$this->tmpl->addVar("page_link_top", "page_link", $pageLink);
			$this->tmpl->addVar("page_link_bottom", "page_link", $pageLink);
		}

		// AJAX用URL、詳細画面用URL
		$ajaxUrl = 'act=getlist&' . M3_REQUEST_PARAM_PAGE_NO . '=' . $pageNo . $urlParams;
		
		// 検索カテゴリ情報を取得
		$ret = $this->categoryDb->getAllCategoryForMenu($this->_langId, $menuCategoryArray, $rows);
		if ($ret){
			$category = array();
			$parentCategory = '';
			$rowCount = count($rows);
			for ($i = 0; $i < $rowCount; $i++){
				$line = $rows[$i];
				if ($parentCategory == $line['hc_parent_id']){
					$category[] = array('name' => $line['hc_name'],	'value' => $line['hc_id']);
				} else {
					if (!empty($category)) $this->categoryInfoArray[$parentCategory] = $category;
					$parentCategory = $line['hc_parent_id'];
					$category = array();
					$category[] = array('name' => $line['hc_name'],	'value' => $line['hc_id']);
				}
			}
			if (!empty($category)) $this->categoryInfoArray[$parentCategory] = $category;
		}
		
		// ##### 検索画面作成 #####
		if (!empty(self::$_configArray[photo_mainCommonDef::CF_PHOTO_CATEGORY_PASSWORD]) && empty($this->authCategory)){// 画像カテゴリーのパスワード制限ありで参照可能カテゴリーがないとき
			$fieldOutput = '';		// 検索画面なし
		} else {
			$fieldOutput = $this->createFieldOutput($searchTemplate);
		}
		if (!empty($fieldOutput)){
			$this->tmpl->setAttribute('search_area', 'visibility', 'visible');		// 検索エリア表示
			$this->tmpl->addVar("search_area", "html",	$fieldOutput);
			$this->tmpl->addVar("search_area", "search_form_id",	photo_mainCommonDef::SEARCH_FORM_ID);		// 検索フォームのタグID
			/*
			$this->tmpl->addVar("_widget", "search_text_id",	photo_mainCommonDef::SEARCH_TEXT_ID);		// 検索用テキストフィールドのタグID
			$this->tmpl->addVar("_widget", "search_button_id",	photo_mainCommonDef::SEARCH_BUTTON_ID);		// 検索用ボタンのタグID
			$this->tmpl->addVar("_widget", "search_reset_id",	photo_mainCommonDef::SEARCH_RESET_ID);		// 検索エリアリセットボタンのタグID
			$this->tmpl->addVar("_widget", "search_sort_id",	photo_mainCommonDef::SEARCH_SORT_ID);		// 検索エリアソート順のタグID
			$this->tmpl->addVar("_widget", "search_form_id",	photo_mainCommonDef::SEARCH_FORM_ID);		// 検索フォームのタグID
			$this->tmpl->addVar("_widget", "keyword",	addslashes($keyword));		// 検索キーワード(Javascript部)
			$this->tmpl->addVar("_widget", "sort",		$this->sortKey . '-' . $this->sortDirection);		// デフォルトのソート順
			*/
			$this->tmpl->setAttribute('search_script', 'visibility', 'visible');		// 検索エリア表示
			$this->tmpl->addVar("search_script", "search_url", $this->getUrl($this->currentPageUrl));
			$this->tmpl->addVar("search_script", "search_text_id",	photo_mainCommonDef::SEARCH_TEXT_ID);		// 検索用テキストフィールドのタグID
			$this->tmpl->addVar("search_script", "search_button_id",	photo_mainCommonDef::SEARCH_BUTTON_ID);		// 検索用ボタンのタグID
			$this->tmpl->addVar("search_script", "search_reset_id",	photo_mainCommonDef::SEARCH_RESET_ID);		// 検索エリアリセットボタンのタグID
			$this->tmpl->addVar("search_script", "search_sort_id",	photo_mainCommonDef::SEARCH_SORT_ID);		// 検索エリアソート順のタグID
			$this->tmpl->addVar("search_script", "search_form_id",	photo_mainCommonDef::SEARCH_FORM_ID);		// 検索フォームのタグID
			$this->tmpl->addVar("search_script", "keyword",	addslashes($keyword));		// 検索キーワード(Javascript部)
			$this->tmpl->addVar("search_script", "sort",		$this->sortKey . '-' . $this->sortDirection);		// デフォルトのソート順
		}
		
		// 画面埋め込みデータ
		$this->tmpl->addVar("_widget", "photo_count", $this->convertToDispString($this->viewCount));
		$this->tmpl->addVar("_widget", "load_icon_url", $this->getUrl($this->gEnv->getCurrentWidgetImagesUrl() . self::LOAD_ICON_FILE));			// ロード中アイコン
		$this->tmpl->addVar("_widget", "photo_detail_url", $this->getUrl($this->gEnv->getDefaultUrl(), true/*リンク用*/));		// 詳細画面へのリンク
		$this->tmpl->addVar("_widget", "photo_detail_url_others", $urlParams);		// 詳細画面へのリンクの付加URLパラメータ
		$this->tmpl->addVar("_widget", "ajax_url", $ajaxUrl);
		$this->tmpl->addVar('_widget', 'raty_image_url', $this->getUrl($this->gEnv->getScriptsUrl() . self::RATY_IMAGE_DIR));	// jquery.raty画像パス
	}
	/**
	 * 一覧画面データ取得
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param								なし
	 */
	function getList($request)
	{
		$keyword = $request->trimValueOf(M3_REQUEST_PARAM_KEYWORD);		// 検索キーワード
		$pageNo = $request->trimIntValueOf(M3_REQUEST_PARAM_PAGE_NO, '1');				// ページ番号
		$category = $request->trimValueOf('category');		// カテゴリー
		$author = $request->trimValueOf('author');			// 著作者
		$sort = $request->trimValueOf('sort');		// ソート順

		// 画像カテゴリーにパスワード制限を掛けている場合の処理
		if (!empty(self::$_configArray[photo_mainCommonDef::CF_PHOTO_CATEGORY_PASSWORD])){
			// 参照可能なカテゴリーを取得
			$this->authCategory = array();
			$clientId = $this->gAccess->getClientId();		// ブラウザのクライアントIDを取得
			if (!empty($clientId)){
				// クライアントパラメータオブジェクトを取得
				$clientObj = $this->getClientParamObj($clientId, $this->gEnv->getCurrentWidgetId());
				if (!empty($clientObj) && !empty($clientObj->authCategory)){
					$this->authCategory = explode(',', $clientObj->authCategory);
				}
			}
		}
		
		// データエラーチェック
		$this->checkInputValues($category, $author, $sort);
		
		// URLパラメータ作成
		$urlParams = $this->createUrlParams($keyword, $category, $author, $sort);
		
		// キーワード分割
		$parsedKeywords = $this->gInstance->getTextConvManager()->parseSearchKeyword($keyword);
		
		// 表示用パラメータ取得
		$this->thumbnailSize = intval(self::$_configArray[photo_mainCommonDef::CF_DEFAULT_THUMBNAIL_SIZE]);		// デフォルトサムネールサイズ
		if ($this->thumbnailSize <= 0) $this->thumbnailSize = photo_mainCommonDef::DEFAULT_THUMBNAIL_SIZE;
		$this->thumbailSizeArray = trimExplode(',', self::$_configArray[photo_mainCommonDef::CF_THUMBNAIL_SIZE]);
		if (empty($this->thumbailSizeArray)) $this->thumbailSizeArray = array(photo_mainCommonDef::DEFAULT_THUMBNAIL_SIZE);
		
		// デフォルトでは最新の登録画像を取得
		$this->photoArray = array();	// 取得した写真
		$this->startNo = $this->viewCount * ($pageNo -1) + 1;
		$limitedCategory = $this->authCategory;
		if (!is_null($limitedCategory) && count($limitedCategory) == 0) $limitedCategory = array('0');		// 空の場合はダミーのカテゴリーIDを追加
		self::$_mainDb->searchPhotoItem($this->viewCount, $pageNo, $this->_langId, null/*開始日時*/, null/*終了日時*/, $parsedKeywords/*キーワード*/,
						$this->categoryArray/*カテゴリー*/, $this->authorArray/*撮影者*/, $this->sortKey/*ソートキー*/, $this->sortDirection/*ソート方向*/,
						array($this, 'viewListLoop'), $urlParams, $limitedCategory);

		// Ajax戻りデータ
		$this->gInstance->getAjaxManager()->addData('items', $this->photoArray);
		$this->gInstance->getAjaxManager()->addData('pagecount', $pageCount);		// 総ページ数
		$this->gInstance->getAjaxManager()->addData('pageno', $pageNo);			// 現在のページ番号
	}
	/**
	 * 詳細画面表示
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @return								なし
	 */
	function showDetail($request)
	{
		$commentDb = new photo_commentDb();
		
		// 画像カテゴリーにパスワード制限を掛けている場合の処理
		if (!empty(self::$_configArray[photo_mainCommonDef::CF_PHOTO_CATEGORY_PASSWORD])){
			// 参照可能なカテゴリーを取得
			$this->authCategory = array();
			$clientId = $this->gAccess->getClientId();		// ブラウザのクライアントIDを取得
			if (!empty($clientId)){
				// クライアントパラメータオブジェクトを取得
				$clientObj = $this->getClientParamObj($clientId, $this->gEnv->getCurrentWidgetId());
				if (!empty($clientObj) && !empty($clientObj->authCategory)){
					$this->authCategory = explode(',', $clientObj->authCategory);
				}
			}
		}
		
		$ret = self::$_mainDb->getSearchPhotoInfo($this->photoId, $this->_langId, $row, $categoryRows);
		if (!$ret){
			$this->setUserErrorMsg('画像が見つかりません');
			return;
		}
		// カテゴリーのアクセスチェック
		if (!is_null($this->authCategory)){
			for ($i = 0; $i < count($categoryRows); $i++){
				if (in_array($categoryRows[$i]['hc_id'], $this->authCategory)) break;
			}
			if ($i == count($categoryRows)){
				$this->setUserErrorMsg('画像が見つかりません');
				$this->writeUserError(__METHOD__, '不正な画像アクセスを検出しました。画像タイトル=' . $row['ht_name'], 2200, '公開画像ID=' . $this->photoId);
				return;
			}
		}
		
		// URLパラメータ
		$itemNo = $request->trimValueOf(M3_REQUEST_PARAM_ITEM_NO);		// 画像No
		if (!ValueCheck::isNumeric($itemNo)) $itemNo = 1;
		$keyword = $request->trimValueOf(M3_REQUEST_PARAM_KEYWORD);		// 検索キーワード
		$pageNo = $request->trimIntValueOf(M3_REQUEST_PARAM_PAGE_NO, '1');				// ページ番号
		$category = $request->trimValueOf('category');		// カテゴリー
		$author = $request->trimValueOf('author');			// 著作者
		$sort = $request->trimValueOf('sort');		// ソート順

		// データエラーチェック
		$this->checkInputValues($category, $author, $sort);
		
		// URLパラメータ作成
		$urlParams = $this->createUrlParams($keyword, $category, $author, $sort);
		
		// キーワード分割
		$parsedKeywords = $this->gInstance->getTextConvManager()->parseSearchKeyword($keyword);
	
		// ページ番号修正
		$pageNo = intval(($itemNo -1) / $this->viewCount) + 1;
	
		$title = $alt = $row['ht_name'];
		$author = $row['lu_name'];
		$date = $row['ht_date'];		// 撮影日
		
		// 画像サイズ
		$imageMaxSize = intval(self::$_configArray[photo_mainCommonDef::CF_DEFAULT_IMAGE_SIZE]);
		if ($imageMaxSize <= 0) $imageMaxSize = photo_mainCommonDef::DEFAULT_IMAGE_SIZE;
		list($width, $height) = explode('x', $row['ht_image_size']);
		if ($width > $height){
			$height = round(($height / $width) * $imageMaxSize);
			$width = $imageMaxSize;
		} else {
			$width = round(($width / $height) * $imageMaxSize);
			$height = $imageMaxSize;
		}
		if (empty($width) || empty($height)){
			$width = $height = $imageMaxSize;
		}
		
		// 詳細画像
		$imagePath = photo_mainCommonDef::getPublicImagePath($this->photoId);
		if (file_exists($imagePath)){
			$imageUrl = photo_mainCommonDef::getPublicImageUrl($this->photoId);
		} else {
			$imageUrl = $this->notfoundThumbnailUrl;
		}
		
		// パーマリンク
		$permaUrl = $this->getUrl($this->gEnv->getDefaultUrl() . '?'. M3_REQUEST_PARAM_PHOTO_ID . '=' . $this->photoId);
		$permaLink = '<a href="' . $this->convertUrlToHtmlEntity($permaUrl) . '"><img src="' . $this->getUrl($this->gEnv->getRootUrl() . self::PERMALINK_ICON_FILE) . 
						'" width="' . photo_mainCommonDef::BUTTON_ICON_SIZE . '" height="' . photo_mainCommonDef::BUTTON_ICON_SIZE . '" title="パーマリンク" alt="パーマリンク" style="border:none;margin:0;padding:0;" /></a>';
		
		// 前後のリンクを取得
		$this->startNo = $request->trimIntValueOf(M3_REQUEST_PARAM_ITEM_NO, '1');
		$limitedCategory = $this->authCategory;
		if (!is_null($limitedCategory) && count($limitedCategory) == 0) $limitedCategory = array('0');		// 空の場合はダミーのカテゴリーIDを追加
		self::$_mainDb->searchPhotoItemByNo(3, $this->startNo -1, $this->_langId, null/*開始日時*/, null/*終了日時*/, $parsedKeywords/*キーワード*/, 
				$this->categoryArray/*カテゴリー*/, $this->authorArray/*撮影者*/, $this->sortKey/*ソートキー*/, $this->sortDirection/*ソート方向*/,
				array($this, 'prevNextListLoop'), $urlParams, $limitedCategory);
	
		// 戻り先一覧画面のURLを取得
		$backUrl = $this->currentPageUrl;
		if ($pageNo > 1) $backUrl .= '&' . M3_REQUEST_PARAM_PAGE_NO . '=' . $pageNo;
		$backUrl .= $urlParams;
		
		// 画像カテゴリーを取得
		$categoryStr = '';
		for ($i = 0; $i < count($categoryRows); $i++){
			if ($i > 0) $categoryStr .= ', ';
			$categoryStr .= $categoryRows[$i]['hc_name'];
		}
		
		// 投票状況
		$rateDisabled = '';
		$clientId = $this->gAccess->getClientId();			// ブラウザのクライアントIDを取得
		if (empty($clientId)){
			$rateDisabled = 'disabled';
		} else {
			$isRegistered = $commentDb->isExistsComment($this->photoId, $clientId, $this->_langId);
			if ($isRegistered) $rateDisabled = 'disabled';
		}
		
		// コメントを取得
		$commentDb->getCommentByPublicPhotoId(photo_mainCommonDef::DEFAULT_COMMENT_COUNT, 1/**/, $this->photoId, $this->_langId, array($this, 'commentListLoop'));
		if (count($this->serialArray) == 0) $this->tmpl->setAttribute('itemlist', 'visibility', 'hidden');
		
		// 画像情報を表示
		$this->tmpl->setAttribute('photo_info_script', 'visibility', 'visible');
		$this->tmpl->setAttribute('photo_info_area', 'visibility', 'visible');
		
		// オンラインショップ機能
		if (!empty(self::$_configArray[photo_mainCommonDef::CF_ONLINE_SHOP])){
			$this->tmpl->setAttribute('product_script', 'visibility', 'visible');
			$this->tmpl->setAttribute('product_info', 'visibility', 'visible');
			
			// フォト商品の表示
			if (!empty(self::$_configArray[photo_mainCommonDef::CF_SELL_PRODUCT_PHOTO])){		// フォト関連商品販売をする場合
				$this->ecObj = $this->gInstance->getObject(self::PRICE_OBJ_ID);
				
				// 通貨情報を取得
				$ret = $this->ecObj->db->getCurrency(self::DEFAULT_CURRENCY, $this->_langId, $currencyRow);
				if ($ret){
					$this->prePrice = $this->convertToDispString($currencyRow['cu_symbol']);
					$this->postPrice = $this->convertToDispString($currencyRow['cu_post_symbol']);
				}
				
				self::$_mainDb->getAllProduct($this->_langId, array($this, 'productListLoop'));
				if (empty($this->isExistsProduct)) $this->tmpl->setAttribute('product_list', 'visibility', 'hidden'); 		// 商品が存在しないとき
			} else {
				$this->tmpl->setAttribute('product_list', 'visibility', 'hidden');
			}
			
			// ダウンロード販売の表示
			if (!empty(self::$_configArray[photo_mainCommonDef::CF_SELL_PRODUCT_DOWNLOAD])){		// ダウンロード販売をする場合
				// ダウンロード権のチェック
				$canDownloadImage = false;
				$ret = self::$_mainDb->getContentAccess($this->_userId, M3_VIEW_TYPE_PHOTO, $row['ht_id'], $accessRow);
				if ($ret){
					if ($accessRow['cs_download']) $canDownloadImage = true;
				}
			
				if ($canDownloadImage){
					$this->tmpl->setAttribute('product_download', 'visibility', 'visible');
				} else {
					$this->tmpl->setAttribute('product_cart', 'visibility', 'visible');
				}
			} else {
				$this->tmpl->setAttribute('download_area', 'visibility', 'hidden');
			}
		}
		// ##### HTMLタグで生成される文字列を埋め込む #####
		// 「投票する」ボタン使用可否を設定
		$contentParam = array();
		$rateTag = '';
		if (self::$_configArray[photo_mainCommonDef::CF_USE_PHOTO_RATE]){	// 画像情報(評価)を使用のとき
			if (empty($rateDisabled)){
				$buttonLabel = '投票する';
			} else {
				$buttonLabel = '投票済み';
			}
			$rateTag = '<span id="photo_rating"></span><input id="photo_rate_send" type="button" class="button" value="' . $buttonLabel . '" ' . $rateDisabled . '/><span id="photo_rate_message"></span>';
		}
		$contentParam[M3_TAG_MACRO_RATE] = $rateTag;
		if (self::$_configArray[photo_mainCommonDef::CF_USE_PHOTO_DESCRIPTION] && // HTMLで画像説明を表示のとき
			self::$_configArray[photo_mainCommonDef::CF_HTML_PHOTO_DESCRIPTION]) $contentParam[M3_TAG_MACRO_CONTENT_DESCRIPTION] = $row['ht_description'];
		$contentParam[M3_TAG_MACRO_PERMALINK] = $permaLink;		// 画像情報(パーマリンク)
		$contentText = $this->createDetailContent($contentParam);	// コンテンツレイアウトに埋め込む
		
		// Magic3マクロ変換。あらかじめ「CT_」タグをすべて取得する?
		$contentInfo = array();
		$contentInfo[M3_TAG_MACRO_CONTENT_ID] = $this->photoId;			// 画像情報(ID)
		$contentInfo[M3_TAG_MACRO_CONTENT_URL] = $permaUrl;			// 画像情報(画像情報URL)
		$contentInfo[M3_TAG_MACRO_CONTENT_TITLE] = $title;			// 画像情報(タイトル)
		$contentInfo[M3_TAG_MACRO_CONTENT_AUTHOR] = $author;		// 画像情報(撮影者)
		if (self::$_configArray[photo_mainCommonDef::CF_USE_PHOTO_DATE]) $contentInfo[M3_TAG_MACRO_CONTENT_DATE] = ($date == $this->gEnv->getInitValueOfDate() ? '' : $date);		// 画像情報(撮影日)
		if (self::$_configArray[photo_mainCommonDef::CF_USE_PHOTO_LOCATION]) $contentInfo[M3_TAG_MACRO_CONTENT_LOCATION] = $row['ht_location'];		// 画像情報(撮影場所)
		if (self::$_configArray[photo_mainCommonDef::CF_USE_PHOTO_CAMERA]) $contentInfo[M3_TAG_MACRO_CONTENT_CAMERA] = $row['ht_camera'];		// 画像情報(カメラ)
		if (self::$_configArray[photo_mainCommonDef::CF_USE_PHOTO_DESCRIPTION] && 
			!self::$_configArray[photo_mainCommonDef::CF_HTML_PHOTO_DESCRIPTION]) $contentInfo[M3_TAG_MACRO_CONTENT_DESCRIPTION] = $row['ht_description'];			// 画像情報(説明)
		if (self::$_configArray[photo_mainCommonDef::CF_USE_PHOTO_CATEGORY]) $contentInfo[M3_TAG_MACRO_CONTENT_CATEGORY] = $categoryStr;		// 画像情報(カテゴリー)
		if (self::$_configArray[photo_mainCommonDef::CF_USE_PHOTO_KEYWORD]) $contentInfo[M3_TAG_MACRO_CONTENT_KEYWORD] = $row['ht_keyword'];		// 画像情報(検索キーワード)
		$contentText = $this->convertM3ToHtml($contentText, true/*改行コードをbrタグに変換*/, $contentInfo);
		$this->tmpl->addVar("photo_info_area", "content", $contentText);
		
		// ##### HTMLヘッダ処理 #####
		// HTMLヘッダにタグ出力
		$outputHead = self::$_configArray[photo_mainCommonDef::CF_OUTPUT_HEAD];			// ヘッダ出力するかどうか
		if ($outputHead){			// ヘッダ出力するかどうか
			// システム用サムネールを取得
			$thumbUrl = '';
			$thumbFilename = $row['ht_thumb_filename'];
			if (!empty($thumbFilename)){
				$thumbFilenameArray = explode(';', $thumbFilename);
				$thumbFilename = $thumbFilenameArray[count($thumbFilenameArray) -1];
			}
			$thumbPath = $this->gInstance->getImageManager()->getSystemThumbPath(M3_VIEW_TYPE_PHOTO, 0/*PC用*/, $thumbFilename, 'ogp');
			$thumbUrl = $this->gInstance->getImageManager()->getSystemThumbUrl(M3_VIEW_TYPE_PHOTO, 0/*PC用*/, $thumbFilename, 'ogp');
				
			// ヘッダ追加項目
			$contentInfo[M3_TAG_MACRO_CONTENT_SUMMARY] = $row['ht_summary'];		// 画像情報(概要)
			$contentInfo[M3_TAG_MACRO_CONTENT_IMAGE] = $this->getUrl($thumbUrl);		// 画像情報(サムネール画像)
			
			$headText = self::$_configArray[photo_mainCommonDef::CF_HEAD_VIEW_DETAIL];
			$headText = $this->convertM3ToHead($headText, $contentInfo);
			$this->gPage->setHeadOthers($headText);
		}
		
		// 画像情報
		$this->tmpl->addVar("photo_info_area", "photo_id", $this->photoId);
		$this->tmpl->addVar("photo_info_area", "img_url", $this->getUrl($imageUrl));
		$this->tmpl->addVar("photo_info_area", "width", $width);
		$this->tmpl->addVar("photo_info_area", "height", $height);
		$this->tmpl->addVar("photo_info_area", "title", $this->convertToDispString($title));
		$this->tmpl->addVar("photo_info_area", "alt", $this->convertToDispString($alt));
		$this->tmpl->addVar("photo_info_area", "back_url", $this->convertUrlToHtmlEntity($this->getUrl($backUrl, true/*リンク用*/)));// 「戻る」リンク
		$this->tmpl->addVar("photo_info_area", "back_img_url", $this->getUrl($this->gEnv->getRootUrl() . self::BACK_ICON_FILE));// 「戻る」アイコン
		$this->tmpl->addVar("photo_info_area", "icon_size", photo_mainCommonDef::BUTTON_ICON_SIZE);
		$this->tmpl->addVar("photo_info_area", "back_title", self::BACK_BUTTON_TITLE);
		$this->tmpl->addVar("photo_info_area", "back_alt", self::BACK_BUTTON_TITLE);
		// スクリプト部
		$this->tmpl->addVar('photo_info_script', 'raty_image_url', $this->getUrl($this->gEnv->getScriptsUrl() . self::RATY_IMAGE_DIR));	// jquery.raty画像パス
		$this->tmpl->addVar("photo_info_script", "init_rate", $this->convertToDispString($row['ht_rate_average']));		// 評価値
		$this->tmpl->addVar("photo_info_area", "comment_count", count($this->serialArray));		// コメント数
		
		// ハッシュキー作成
		$postTicket = md5(time() . $this->gAccess->getAccessLogSerialNo());
		$request->setSessionValue(M3_SESSION_POST_TICKET, $postTicket);		// セッションに保存
		$this->tmpl->addVar("photo_info_area", "ticket", $postTicket);				// 画面に書き出し
			
		// ビューカウントを更新
		if (!$this->gEnv->isSystemManageUser()){		// システム運用者以上の場合はカウントしない
			// 詳細な参照情報
			$this->gInstance->getAnalyzeManager()->updateContentViewCount(photo_mainCommonDef::REF_TYPE_CONTENT, $row['ht_id'], $this->currentDay, $this->currentHour);
			
			// 画像情報の参照数更新
			self::$_mainDb->updatePhotoInfoViewCount($row['ht_serial']);
		}
	}
	/**
	 * 詳細画面データ取得
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param								なし
	 */
	function getDetail($request)
	{
	}
	/**
	 * 評価投票
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param								なし
	 */
	function rate($request)
	{
		$commentDb = new photo_commentDb();
		$postTicket = $request->trimValueOf('ticket');		// POST確認用
		$comment = $request->trimValueOf('comment');
		$rateValue = $request->trimIntValueOf('value', '0');
		$clientId = $this->gAccess->getClientId();			// ブラウザのクライアントIDを取得

		$retValue = 0;		// 登録状況
		if (!empty($postTicket) && $postTicket == $request->getSessionValue(M3_SESSION_POST_TICKET) && !empty($clientId)){		// 正常なPOST値のとき
			// 送信データのチェック
			if (0 < $rateValue && $rateValue <= 5){
				// 既に登録済みかどうか確認
				$isRegistered = $commentDb->isExistsComment($this->photoId, $clientId, $this->_langId);
				if (!$isRegistered){
					$ret = $commentDb->addCommentItem($this->photoId, $this->_langId, $clientId, $rateValue, $comment, $rateAverage, $newSerial);
					if ($ret) $ret = $commentDb->getCommentBySerial($newSerial, $row);
					if ($ret){
						$rateValue = $row['hr_rate_value'];		// 評価値
						$comment = $row['hr_message'];		// コメント
						$regDate = $this->convertToDispDateTime($row['hr_regist_dt'], 0, 10);		// 登録日付
						$retValue = 1;		// 登録成功
					}
				}
			}
		}
		$request->unsetSessionValue(M3_SESSION_POST_TICKET);		// セッション値をクリア
					
		// Ajax戻りデータ
		$this->gInstance->getAjaxManager()->addData('result', $retValue);
		$this->gInstance->getAjaxManager()->addData('rate_average', $rateAverage);		// 評価集計値
		$this->gInstance->getAjaxManager()->addData('rate_value', $rateValue);		// 評価値
		$this->gInstance->getAjaxManager()->addData('comment', $comment);		// コメント
		$this->gInstance->getAjaxManager()->addData('reg_date', $regDate);				// 登録日付
	}
	/**
	 * 商品をカートに入れる処理
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param								なし
	 */
	function inputCart($request)
	{
		// Eコマースライブラリオブジェクト取得
		$ecLibObj = $this->gInstance->getObject(photo_mainCommonDef::EC_LIB_OBJ_ID);
		if (is_null($ecLibObj)) return;

		// 入力値取得
		$quantity = $request->trimIntValueOf('quantity', '1');		// 数量

		// パラメータエラーチェック
		if (!$this->isPublicPhotoId($this->photoId, $this->_langId)) return;		// 画像が非公開の場合は終了
		
		// 商品が存在するかチェック
		$productType = $request->trimValueOf('product');
		if ($productType != self::PRODUCT_TYPE_DOWNLOAD){
			$ret = self::$_mainDb->getProductByProductId($productType, $this->_langId, $row);
			if (!$ret) return;
		}
		
		// クッキー読み込み、カートIDを取得
		$cartId = $request->getCookieValue(M3_COOKIE_CART_ID);
		if (empty($cartId)){	// カートIDが設定されていないとき
			// カートIDを生成
			$cartId = $ecLibObj->createCartId();
		}
		$request->setCookieValue(M3_COOKIE_CART_ID, $cartId);

		// ####### カートに商品を追加 ########
		// 商品名と価格を取得
		// カートの情報を取得
		$cartSerial = 0;		// カートシリアル番号
		$cartItemSerial = 0;	// カート商品シリアル番号
		$ret = $ecLibObj->db->getCartHead($cartId, $this->_langId, $row);
		if ($ret){
			$cartSerial = $row['sh_serial'];
		}
		// 商品情報を取得
		$isValidItem = true;		// 現在のカートのデータが有効かどうか
		$ret = self::$_mainDb->getPhotoInfoWithPrice($this->photoId, self::PRODUCT_CLASS, $productType, self::STANDARD_PRICE, $this->_langId, $row);
		if ($ret){
			// 価格を取得
			$productId = $row['ht_id'];	// 画像商品ID
			$price = $row['pp_price'];	// 価格
			$currency = $row['pp_currency_id'];	// 通貨
			$taxType = self::TAX_TYPE;					// 税種別

			// 価格作成
			$ecLibObj->setCurrencyType($currency, $this->_langId);		// 通貨設定
			$ecLibObj->setTaxType($taxType, $this->_langId);		// 税種別設定
			$totalPrice = $ecLibObj->getPriceWithTax($price, $dispPrice);	// 税込み価格取得
		} else {		// 商品情報が取得できないときは、カートの商品をキャンセル
			$this->writeError(__METHOD__, '商品情報が取得できません。', 1100,
													'商品クラス=' . self::PRODUCT_CLASS . ', 画像ID=' . $this->photoId . ', 商品タイプ=' . $productType);// 運用ログに記録
			$isValidItem = false;
		}

		// カートにある商品を取得
		if ($isValidItem && $cartSerial > 0){
			$ret = $ecLibObj->db->getCartItem($cartSerial, self::PRODUCT_CLASS, $productId, $productType, $cartItemRow);
			if ($ret){		// 取得できたときは価格をチェック
				if ($cartItemRow['si_available']){		// データが有効のとき
					// 価格が変更されているときはカートの商品を無効にする
					$cartItemCurrency = $cartItemRow['si_currency_id'];
					$cartItemQuantity = $cartItemRow['si_quantity'];
					$cartItemPrice = $cartItemRow['si_subtotal'];
					$cartItemSerial = $cartItemRow['si_serial'];
					
					if ($cartItemCurrency != $currency) $isValidItem = false;		// 通貨が変更のときはレコードを無効化
					if ($totalPrice * $cartItemQuantity != $cartItemPrice) $isValidItem = false;		// 価格が変更のときはレコードを無効化
				} else {
					$isValidItem = false;
				}
			}
		}
		// カート商品情報を更新
		if ($isValidItem){
			// ダウンロード購入は複数購入不可
			if ($productType != self::PRODUCT_TYPE_DOWNLOAD || $cartItemQuantity == 0){
				// カートに商品を追加
				$ret = $ecLibObj->db->addCartItem($cartSerial, $cartItemSerial, $cartId, self::PRODUCT_CLASS, $productId, $productType, $this->_langId, 
														$currency, $totalPrice, $quantity);
			}
		} else {
			// カート商品の無効化
			$ret = $ecLibObj->db->voidCartItem($cartSerial, $cartItemSerial);
		}
		// カート画面を表示
		$cartPage = $this->createCmdUrlToWidget(self::TARGET_WIDGET, 'task=cart');
		$this->gPage->redirect($cartPage);
	}
	/**
	 * 画像ダウンロード処理
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param								なし
	 */
	function downloadImage($request)
	{
		$ret = self::$_mainDb->getSearchPhotoInfo($this->photoId, $this->_langId, $row, $categoryRows);
		if (!$ret){
			$this->gOpeLog->writeUserAccess(__METHOD__, '不正なダウンロードを検出しました。画像情報が取得できません。', 2200, 'ダウンロードをブロックしました。公開画像ID=' . $this->photoId);
			return;
		}
		
		// アクセス権をチェック
		$canDownloadImage = false;
		$ret = self::$_mainDb->getContentAccess($this->_userId, M3_VIEW_TYPE_PHOTO, $row['ht_id'], $accessRow);
		if ($ret){
			if ($accessRow['cs_download']) $canDownloadImage = true;
		}
		if (!$canDownloadImage){
			$this->gOpeLog->writeUserAccess(__METHOD__, '不正なダウンロードを検出しました。ダウンロード権限がありません。', 2200, 'ダウンロードをブロックしました。画像コード=' . $row['ht_code']);
			return;
		}
				
		// ページ作成処理中断
		$this->gPage->abortPage();
		
		// ダウンロード画像ファイル名作成
		// 画像フォーマット
		$mimeType = $row['ht_mime_type'];
		if ($mimeType == image_type_to_mime_type(IMAGETYPE_GIF)){
			$ext = '.gif';
		} else if ($mimeType == image_type_to_mime_type(IMAGETYPE_JPEG)){
			$ext = '.jpg';
		} else if ($mimeType == image_type_to_mime_type(IMAGETYPE_PNG)){
			$ext = '.png';
		} else if ($mimeType == image_type_to_mime_type(IMAGETYPE_BMP)){
			$ext = '.bmp';
		}
		$downloadFilename = strtr($row['ht_name'], ' ', '_') . $ext;		// 半角スペースを「_」に変換

		// ダウンロード処理
		$imagePath = $this->gEnv->getIncludePath() . photo_mainCommonDef::PHOTO_DIR . $row['ht_dir'] . DIRECTORY_SEPARATOR . $row['ht_public_id'];
		if (file_exists($imagePath)){
			$ret = $this->gPage->downloadFile($imagePath, $downloadFilename);
			
			// ダウンロード数更新
			if (!$this->gEnv->isSystemManageUser()){		// システム運用者以上の場合はカウントしない
				$this->gInstance->getAnalyzeManager()->updateContentViewCount(photo_mainCommonDef::REF_TYPE_DOWNLOAD, $row['ht_id'], $this->currentDay, $this->currentHour);
				
				// ダウンロードを記録
				$this->gInstance->getAnalyzeManager()->logContentDownload(M3_VIEW_TYPE_PHOTO, $row['ht_id']);
			}
		} else {			// 画像がみつからない場合
			$this->writeError(__METHOD__, '画像が見つからないため、ダウンロードできません。', 1100, '画像コード=' . $row['ht_code'] . ',ファイル名=' . $imagePath);
		}
		
		// システム強制終了
		$this->gPage->exitSystem();
	}
	/**
	 * 取得したコンテンツ項目をテンプレートに設定する
	 *
	 * @param int		$index			行番号
	 * @param array		$fetchedRow		取得行
	 * @param object	$param			任意使用パラメータ
	 * @return bool						trueを返すとループ続行。falseを返すとその時点で終了。
	 */
	function viewListLoop($index, $fetchedRow, $param)
	{
		$thumbnailSize = $this->thumbnailSize;
		
		// サムネール画像が存在するかどうかチェック
		$thumbnailPath = photo_mainCommonDef::getThumbnailPath($fetchedRow['ht_public_id'], $thumbnailSize);
		if (file_exists($thumbnailPath)){
			$thumbnailUrl = $this->getUrl(photo_mainCommonDef::getThumbnailUrl($fetchedRow['ht_public_id'], $thumbnailSize));
		} else {
			// 他のサイズのサムネールを探す
			for ($i = 0; $i < count($this->thumbailSizeArray); $i++){
				$thumbnailSize = $this->thumbailSizeArray[$i];
				$thumbnailPath = photo_mainCommonDef::getThumbnailPath($fetchedRow['ht_public_id'], $thumbnailSize);
				if (file_exists($thumbnailPath)){
					$thumbnailUrl = $this->getUrl(photo_mainCommonDef::getThumbnailUrl($fetchedRow['ht_public_id'], $thumbnailSize));
					break;
				}
			}
			if ($i == count($this->thumbailSizeArray)){			// サムネールが見つからないとき
				$thumbnailUrl = $this->notfoundThumbnailUrl;
				$thumbnailSize = $this->thumbnailSize;
			}
		}
		$shortTitle = makeTruncStr($fetchedRow['ht_name'], $this->shortTitleLength);
		
		$row = array(
			'id' =>	$fetchedRow['ht_public_id'],		// 公開画像ID
			'no' =>	$this->startNo + $index,		// 画像番号
			'title' => $fetchedRow['ht_name'],		// 写真タイトル
			'title_short' => $shortTitle,		// 略式写真タイトル
			'score' => $fetchedRow['ht_rate_average'],		// 評価値
			'image_url' => $thumbnailUrl,	// サムネール画像のURL
			'image_size' => $thumbnailSize	// サムネール画像サイズ
		);
		$this->photoArray[] = $row;
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
	function commentListLoop($index, $fetchedRow)
	{
		$row = array(
			'no' =>	$index + 1,		// コメント番号
			'rate_value' => $this->convertToDispString($fetchedRow['hr_rate_value']),	// 評価値
			'comment' => $this->convertToDispString($fetchedRow['hr_message']),	// コメント
			'date' => $this->convertToDispDateTime($fetchedRow['hr_regist_dt'], 0, 10)	// 日付
		);
		$this->tmpl->addVars('itemlist', $row);
		$this->tmpl->parseTemplate('itemlist', 'a');
		$this->serialArray[] = $fetchedRow['hr_serial'];
		return true;
	}
	/**
	 * 前後のリンク項目をテンプレートに設定する
	 *
	 * @param int		$index			行番号
	 * @param array		$fetchedRow		取得行
	 * @param object	$urlParams		URL付加パラメータ(任意使用パラメータ)
	 * @return bool						trueを返すとループ続行。falseを返すとその時点で終了。
	 */
	function prevNextListLoop($index, $fetchedRow, $urlParams)
	{
		if ($index == 0 && $fetchedRow['ht_public_id'] != $this->photoId){		// 「前へ」リンクのとき
			$this->tmpl->setAttribute('prev_link', 'visibility', 'visible');		// リンク表示
			$this->tmpl->addVar("prev_link", "prev_img_url", $this->getUrl($this->gEnv->getRootUrl() . self::PREV_ICON_FILE));// 「前へ」アイコン
			$this->tmpl->addVar("prev_link", "prev_title", self::PREV_BUTTON_TITLE);
			$this->tmpl->addVar("prev_link", "prev_alt", self::PREV_BUTTON_TITLE);
			$this->tmpl->addVar("prev_link", "icon_size", photo_mainCommonDef::BUTTON_ICON_SIZE);
			
			// リンク作成
			$linkUrl = $this->getUrl($this->gEnv->getDefaultUrl() . '?'. M3_REQUEST_PARAM_PHOTO_ID . '=' . $fetchedRow['ht_public_id'] .
												'&' . M3_REQUEST_PARAM_ITEM_NO . '=' . ($this->startNo -1) . $urlParams, true/*リンク用*/);
			$this->tmpl->addVar("prev_link", "prev_url", $this->convertUrlToHtmlEntity($linkUrl));
		} else if ($index >= 1 && $fetchedRow['ht_public_id'] != $this->photoId){// 「次へ」リンクのとき
			$this->tmpl->setAttribute('next_link', 'visibility', 'visible');		// リンク表示
			$this->tmpl->addVar("next_link", "next_img_url", $this->getUrl($this->gEnv->getRootUrl() . self::NEXT_ICON_FILE));// 「次へ」アイコン
			$this->tmpl->addVar("next_link", "next_title", self::NEXT_BUTTON_TITLE);
			$this->tmpl->addVar("next_link", "next_alt", self::NEXT_BUTTON_TITLE);
			$this->tmpl->addVar("next_link", "icon_size", photo_mainCommonDef::BUTTON_ICON_SIZE);
			
			// リンク作成
			$linkUrl = $this->getUrl($this->gEnv->getDefaultUrl() . '?'. M3_REQUEST_PARAM_PHOTO_ID . '=' . $fetchedRow['ht_public_id'] .
												'&' . M3_REQUEST_PARAM_ITEM_NO . '=' . ($this->startNo +1) . $urlParams, true/*リンク用*/);
			$this->tmpl->addVar("next_link", "next_url", $this->convertUrlToHtmlEntity($linkUrl));
			return false;
		}
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
	function categoryListLoop($index, $fetchedRow, $param)
	{
		// パスワードが設定されていない場合は表示しない
		if (empty($fetchedRow['hc_password'])) return true;
		
		$id = $fetchedRow['hc_id'];// カテゴリ項目ID
		$name = $fetchedRow['hc_name'];		// カテゴリ項目名
		$selected = '';
		$this->categoryWithPwdArray[] = $id;	// パスワードが必要なカテゴリー
		
		// 既にアクセス可能になっているカテゴリーは表示しない
		if (in_array($id, $this->authCategory)){
			$this->authCategoryStr .= $name . ',';	// 参照可能なカテゴリー(表示用)
			return true;
		}
		
		$row = array(
			'value' => $this->convertToDispString($id),		// カテゴリ項目ID
			'name' => $this->convertToDispString($name),	// カテゴリ項目名
			'selected'	=> $selected						// 選択中かどうか
		);
		$this->tmpl->addVars('access_category_list', $row);
		$this->tmpl->parseTemplate('access_category_list', 'a');
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
	function productListLoop($index, $fetchedRow, $param)
	{
		$ret = self::$_mainDb->getProductBySerial($fetchedRow['hp_serial'], $row, $row2, $row3);
		if (!$ret) return true;

		// 価格を取得
		$priceArray = $this->getPrice($row2, self::STANDARD_PRICE);
		$price		= $priceArray['pp_price'];			// 価格
		$currency	= $priceArray['pp_currency_id'];	// 通貨
		$taxType	= $fetchedRow['hp_tax_type_id'];	// 税種別

		// 表示額作成
		$this->ecObj->setCurrencyType($currency, $this->_langId);		// 通貨設定
		$this->ecObj->setTaxType($taxType, $this->_langId);		// 税種別設定
		$totalPrice = $this->ecObj->getPriceWithTax($price, $dispPrice);	// 税込み価格取得
		
		$row = array(
			'index'				=> $index,
			'product_name'		=> $this->convertToDispString($fetchedRow['hp_name']),	// 商品名
			'product_detail'	=> $this->convertToDispString($fetchedRow['hp_description_short']),	// 簡易商品説明
			'disp_total_price'	=> $this->prePrice . $dispPrice . $this->postPrice,		// 税込み価格
			'product'			=> $this->convertToDispString($fetchedRow['hp_id'])			// フォト商品ID
		);
		$this->tmpl->addVars('product_list', $row);
		$this->tmpl->parseTemplate('product_list', 'a');
		
		$row2 = array(
			'index'			=> $index
		);
		$this->tmpl->addVars('product_script_list', $row2);
		$this->tmpl->parseTemplate('product_script_list', 'a');
		$this->isExistsProduct = true;		// 商品が存在するかどうか
		return true;
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
	 * 検索テンプレートデータ作成処理コールバック
	 *
	 * @param object         $tmpl			テンプレートオブジェクト
	 * @param								なし
	 */
	function _makeSearcheTemplate($tmpl)
	{
		$tmpl->addVar("_tmpl", "search_text_id",	photo_mainCommonDef::SEARCH_TEXT_ID);		// 検索用テキストフィールドのタグID
		$tmpl->addVar("_tmpl", "search_button_id",	photo_mainCommonDef::SEARCH_BUTTON_ID);		// 検索用ボタンのタグID
		$tmpl->addVar("_tmpl", "search_reset_id",	photo_mainCommonDef::SEARCH_RESET_ID);		// 検索エリアリセットボタンのタグID
		$tmpl->addVar("_tmpl", "search_sort_id",	photo_mainCommonDef::SEARCH_SORT_ID);		// 検索エリアソートメニューのタグID
	}
	/**
	 * ページ番号計算処理
	 *
	 * @param int $pageNo			ページ番号(1～)。ページ番号が範囲外にある場合は自動的に調整
	 * @param int $totalCount		総項目数
	 * @param int $viewItemCount	1ページあたりの項目数
	 * @param int $pageCount		戻り値、ページ総数(1～)。
	 * @param int $startNo			戻り値、先頭項目番号(1～)。
	 * @param int $endNo			戻り値、最後項目番号(1～)。
	 * @return 						なし
	 */
	function calcPage(&$pageNo, $totalCount, $viewItemCount, &$pageCount, &$startNo, &$endNo)
	{
		// 表示するページ番号の修正
		$pageCount = (int)(($totalCount -1) / $viewItemCount) + 1;		// 総ページ数
		if ($pageNo < 1) $pageNo = 1;
		if ($pageNo > $pageCount) $pageNo = $pageCount;
		$startNo = ($pageNo -1) * $viewItemCount +1;		// 先頭の行番号
		$endNo = $pageNo * $viewItemCount > $totalCount ? $totalCount : $pageNo * $viewItemCount;// 最後の行番号
	}
	/**
	 * ページリンク作成
	 *
	 * @param int $pageNo			ページ番号(1～)。
	 * @param int $pageCount		総項目数
	 * @param int $linkCount		最大リンク数
	 * @param string $baseUrl		リンク用のベースURL
	 * @param string $urlParams		オプションのURLパラメータ
	 * @return string				リンクHTML
	 */
	function createPageLink($pageNo, $pageCount, $linkCount, $baseUrl, $urlParams)
	{
		// ページング用リンク作成
		$pageLink = '';
		if ($pageCount > 1){	// ページが2ページ以上のときリンクを作成
			// ページ数1から「LINK_PAGE_COUNT」までのリンクを作成
			$maxPageCount = $pageCount < $linkCount ? $pageCount : $linkCount;
			for ($i = 1; $i <= $maxPageCount; $i++){
				if ($i == $pageNo){
					$link = '&nbsp;[' . $i . ']';
				} else {
					$linkUrl = $this->getUrl($baseUrl . '&' . M3_REQUEST_PARAM_PAGE_NO . '=' . $i . $urlParams, true/*リンク用*/);
					$link = '&nbsp;<a href="' . $this->convertUrlToHtmlEntity($linkUrl) . '" >[' . $i . ']</a>';
				}
				$pageLink .= $link;
			}
			// 残りは「...」表示
			if ($pageCount > $linkCount) $pageLink .= '&nbsp;...';
		}
		if ($pageNo > 1){		// 前ページがあるとき
			$linkUrl = $this->getUrl($baseUrl . '&' . M3_REQUEST_PARAM_PAGE_NO . '=' . ($pageNo -1) . $urlParams, true/*リンク用*/);
			$link = '<a href="' . $this->convertUrlToHtmlEntity($linkUrl) . '" >[前へ]</a>';
			$pageLink = $link . $pageLink;
		}
		if ($pageNo < $pageCount){		// 次ページがあるとき
			$linkUrl = $this->getUrl($baseUrl . '&' . M3_REQUEST_PARAM_PAGE_NO . '=' . ($pageNo +1) . $urlParams, true/*リンク用*/);
			$link = '&nbsp;<a href="' . $this->convertUrlToHtmlEntity($linkUrl) . '" >[次へ]</a>';
			$pageLink .= $link;
		}
		return $pageLink;
	}
	/**
	 * 検索条件フィールド作成
	 *
	 * @param string $templateData	テンプレートデータ
	 * @return string				フィールドデータ
	 */
	function createFieldOutput($templateData)
	{
		$fieldOutput = $templateData;
		
		$fieldCount = count($this->fieldInfoArray);
		for ($i = 0; $i < $fieldCount; $i++){
			$infoObj = $this->fieldInfoArray[$i];
			$itemType = $infoObj->itemType;			// カテゴリ種別
			$selectType = $infoObj->selectType;				// 選択タイプ
			$category = $infoObj->category;			// カテゴリ
			
			// 選択項目取得
			if ($itemType == 'category'){		// カテゴリーの場合
				$selItems = $this->categoryInfoArray[$category];		// カテゴリ項目
				$selItemCount = count($selItems);
				$inputValues = $this->categoryArray;
			} else if ($itemType == 'author'){	// 撮影者の場合
				if (empty($this->menuAuthorRows)){
					$ret = self::$_mainDb->getAllUserForMenu(photo_mainCommonDef::USER_OPTION, $this->menuAuthorRows);
				}
				$selItems = array();
				for ($j = 0; $j < count($this->menuAuthorRows); $j++){
					$selItems[] = array('name' => $this->menuAuthorRows[$j]['lu_name'],	'value' => $this->menuAuthorRows[$j]['lu_account']);
				}
				$selItemCount = count($selItems);
				$inputValues = $this->authorArray;
			}
			// 入力フィールドの作成
			$inputTag = '';
			$fieldName = self::SEARCH_FIELD_HEAD . ($i + 1);
			$inputTag = '';
			switch ($selectType){
				case 'single':	// 単一選択
					$inputTag .= '<select id="' . $fieldName . '" name="' . $fieldName . '" class="' . self::SEARCH_FIELD_CLASS_HEAD . $itemType . '" >' . M3_NL;
					$inputTag .= '<option value="">-- 選択なし --</option>' . M3_NL;
					for ($j = 0; $j < $selItemCount; $j++){
						$param = array();
						$paramStr = '';
						$value = $selItems[$j]['value'];
						$name = $selItems[$j]['name'];
						if ($itemType == 'category'){		// カテゴリーの場合
							for ($k = 0; $k < count($inputValues); $k++){
								$inputValuesLine = $inputValues[$k];
								if (in_array($value, $inputValuesLine)){
									$param[] = 'selected';
									break;
								}
							}
						} else {
							if (in_array($value, $inputValues)) $param[] = 'selected';
						}
						if (count($param) > 0) $paramStr = ' ' . implode($param, ' ');
						$inputTag .= '<option value="' . $this->convertToDispString($value) . '"' . $paramStr . '>' . $this->convertToDispString($name) . '</option>' . M3_NL;
					}
					$inputTag .= '</select>' . M3_NL;
					break;
				case 'multi':	// 複数選択
					$fieldName .= '[]';
					for ($j = 0; $j < $selItemCount; $j++){
						$param = array();
						$paramStr = '';
						$value = $selItems[$j]['value'];
						$name = $selItems[$j]['name'];
						if ($itemType == 'category'){		// カテゴリーの場合
							for ($k = 0; $k < count($inputValues); $k++){
								$inputValuesLine = $inputValues[$k];
								if (in_array($value, $inputValues)){
									$param[] = 'checked';
									break;
								}
							}
						} else {
							if (in_array($value, $inputValues)) $param[] = 'checked';
						}
						$param[] = 'class="' . self::SEARCH_FIELD_CLASS_HEAD . $itemType . '"';		// クラス名追加
						if (count($param) > 0) $paramStr = ' ' . implode($param, ' ');
						$inputTag .= '<label><input type="checkbox" name="' . $fieldName . '" value="' . $this->convertToDispString($value) . '"'
										. $paramStr . ' />' . $this->convertToDispString($name) . '</label>';
					}
					$inputTag .= M3_NL;
					break;
			}

			// テンプレートに埋め込む
			$keyTag = M3_TAG_START . M3_TAG_MACRO_ITEM_KEY . ($i + 1) . M3_TAG_END;
			$fieldOutput = str_replace($keyTag, $inputTag, $fieldOutput);
		}
		return $fieldOutput;
	}
	/**
	 * POST,GETパラメータエラーチェック
	 *
	 * @param string $category		画像カテゴリー
	 * @param string $author		著作者
	 * @param string $sort			ソート順
	 * @return bool					true=正常、false=異常
	 */
	function checkInputValues($category, $author, $sort)
	{
		$retStatus = true;		// 処理状態
		
		// カテゴリー
		// 検索条件フィールド内はOR、フィールド間はAND検索
		// フィールド内の結果は「,」区切りで、フィールド間の結果は「+」または「 」で区切る
		$this->categoryArray = array();
		$pos = strpos($category, '+');		// カテゴリは「+」または「 」で区切る
		if ($pos === false){
			$categorysArray = explode(' ', $category);
		} else {
			$categorysArray = explode('+', $category);
		}
		for ($i = 0; $i < count($categorysArray); $i++){
			$categoryArray = array();
			$categorySrcArray = explode(',', $categorysArray[$i]);
			for ($j = 0; $j < count($categorySrcArray); $j++){
				if (!empty($categorySrcArray[$j])) $categoryArray[] = $categorySrcArray[$j];
			}
			if (!ValueCheck::isNumeric($categoryArray)){		// すべて数値であるかチェック
				$this->categoryArray = array();		// エラーの場合は初期化
				$retStatus = false;
				break;
			}
			if (!empty($categoryArray)) $this->categoryArray[] = $categoryArray;
		}
		// 著作者
		$this->authorArray = explode(',', $author);
		if (!empty($this->authorArray)){
			$isErr = false;
			$ret = self::$_mainDb->getAllUserForMenu(photo_mainCommonDef::USER_OPTION, $this->menuAuthorRows);
			if ($ret){
				$userArray = array();
				for ($i = 0; $i < count($this->menuAuthorRows); $i++){
					$userArray[] = $this->menuAuthorRows[$i]['lu_account'];
				}
				for ($i = 0; $i < count($this->authorArray); $i++){
					if (!in_array($this->authorArray[$i], $userArray)){
						$isErr = true;
						break;
					}
				}
			} else {
				$isErr = true;
			}
			if ($isErr){
				$this->authorArray = array();		// 選択中の著作者
				$retStatus = false;
			}
		}
		// ソート順
		list($this->sortKey, $this->sortDirection) = explode('-', $sort);
		if (!in_array($this->sortKey, array('index', 'date', 'rate', 'ref')) || !in_array($this->sortDirection, array('0', '1'))){
			//$this->sortKey = 'date';		// デフォルト値
			//$this->sortDirection = '0';
			$this->sortKey = self::$_configArray[photo_mainCommonDef::CF_PHOTO_LIST_SORT_KEY];
			if ($this->sortKey == '') $this->sortKey = photo_mainCommonDef::DEFAULT_PHOTO_LIST_SORT_KEY;
			$this->sortDirection = self::$_configArray[photo_mainCommonDef::CF_PHOTO_LIST_ORDER];		
			if ($this->sortDirection == '') $this->sortDirection = photo_mainCommonDef::DEFAULT_PHOTO_LIST_ORDER;// デフォルトの画像一覧並び順(降順)
			$retStatus = false;
		}
		return $retStatus;
	}
	/**
	 * 画像ID公開状況チェック
	 *
	 * @param string $photoId		画像ID
	 * @param string $lang			言語ID
	 * @return bool					true=公開中、false=非公開
	 */
	function isPublicPhotoId($photoId, $lang)
	{
		$ret = self::$_mainDb->getSearchPhotoInfo($photoId, $lang, $row, $categoryRows);
		return $ret;
	}
	/**
	 * URLパラメータ作成
	 *
	 * @param string $keyword		検索キーワード
	 * @param string $category		画像カテゴリー
	 * @param string $author		著作者
	 * @param string $sort			ソート順
	 * @return string				URLパラメータ
	 */
	function createUrlParams($keyword, $category, $author, $sort)
	{
		$params = '';
		if (!empty($keyword)) $params .= '&' . M3_REQUEST_PARAM_KEYWORD . '=' . urlencode($keyword);// 検索キーワード
		if (!empty($category)){
			// カテゴリーの半角スペースは「+」に変換
			$category = str_replace(' ', '+', $category);
			$params .= '&category' . '=' . $category;// カテゴリー
		}
		if (!empty($author)) $params .= '&author' . '=' . $author;// 撮影者
		if (!empty($sort)) $params .= '&sort' . '=' . $sort;// ソート順
		return $params;
	}
	/**
	 * クライアントパラメータオブジェクトを取得
	 *
	 * @param string $clientId	クライアントID
	 * @param string $widgetId	ウィジェットID
	 * @return object			パラメータオブジェクト。取得できないときはnull。
	 */
	function getClientParamObj($clientId, $widgetId)
	{
		$serializedParam = self::$_mainDb->getClientParam($clientId, $widgetId);
		if (empty($serializedParam)){
			$clientObj = new stdClass;
			return $clientObj;
		} else {
			return unserialize($serializedParam);
		}
	}
	/**
	 * クライアントパラメータオブジェクトを更新
	 *
	 * @param string $clientId	クライアントID
	 * @param string $widgetId	ウィジェットID
	 * @param object $obj		格納するウィジェットパラメータオブジェクト
	 * @return bool				true=更新成功、false=更新失敗
	 */
	function updateClientParamObj($clientId, $widgetId, $obj)
	{
		if (empty($obj)){
			$updateObj = null;
		} else {
			$updateObj = serialize($obj);
		}
		$ret = self::$_mainDb->updateClientParam($clientId, $widgetId, $updateObj);
		return $ret;
	}
	/**
	 * 価格取得
	 *
	 * @param array  	$srcRows			価格リスト
	 * @param string	$priceType			価格のタイプ
	 * @return array						取得した価格行
	 */
	function getPrice($srcRows, $priceType)
	{
		for ($i = 0; $i < count($srcRows); $i++){
			if ($srcRows[$i]['pp_price_type_id'] == $priceType){
				return $srcRows[$i];
			}
		}
		return array();
	}
	/**
	 * 詳細コンテンツを作成
	 *
	 * @param array	$contentParam		コンテンツ作成用パラメータ
	 * @return string					作成コンテンツ
	 */
	function createDetailContent($contentParam)
	{
		$contentText = self::$_configArray[photo_mainCommonDef::CF_LAYOUT_VIEW_DETAIL];// コンテンツレイアウト(詳細表示)
		
		// コンテンツを作成
		$keys = array_keys($contentParam);
		for ($i = 0; $i < count($keys); $i++){
			$key = $keys[$i];
			$value = str_replace('\\', '\\\\', $contentParam[$key]);		// ##### (注意)preg_replaceで変換値のバックスラッシュが解釈されるので、あらかじめバックスラッシュを2重化しておく必要がある
			
			$pattern = '/' . preg_quote(M3_TAG_START . $key) . ':?(.*?)' . preg_quote(M3_TAG_END) . '/u';
			$contentText = preg_replace($pattern, $value, $contentText);
		}
		return $contentText;
	}
}
?>
