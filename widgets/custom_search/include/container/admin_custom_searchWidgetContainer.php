<?php
/**
 * コンテナクラス
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    カスタム検索
 * @author     株式会社 毎日メディアサービス
 * @copyright  Copyright 2010-2021 株式会社 毎日メディアサービス.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.m-media.co.jp
 */
require_once($gEnvManager->getContainerPath() . '/baseAdminWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/custom_searchDb.php');

class admin_custom_searchWidgetContainer extends BaseAdminWidgetContainer
{
	private $db;	// DB接続オブジェクト
	private $serialNo;		// 選択中の項目のシリアル番号
	private $serialArray = array();			// 表示中のシリアル番号
	private $langId;
	private $configId;		// 定義ID
	private $paramObj;		// パラメータ保存用オブジェクト
	private $searchTextId;		// 検索用テキストフィールドのタグID
	private $searchButtonId;		// 検索用ボタンのタグID
	private $searchResetId;		// 検索エリアリセットボタンのタグID
	private $imageType;		// 選択中の画像タイプ
	private $targetRenderType;		// 実際に表示する画面の描画出力タイプ
	private $tmpDir;		// 作業ディレクトリ
	const DEFAULT_NAME_HEAD = '名称未設定';			// デフォルトの設定名
	const DEFAULT_SEARCH_COUNT	= 20;				// デフォルトの検索結果表示数
	const DEFAULT_RESULT_LENGTH = 200;			// 検索結果コンテンツの文字列最大長
	const DEFAULT_IMAGE_TYPE = '80c.jpg';		// デフォルトの画像タイプ
	const DEFAULT_IMAGE_FILENAME_HEAD = 'default';		// デフォルトの画像ファイル名ヘッダ
	const IMAGE_TYPE_ENTRY_IMAGE = 'entryimage';			// 画像タイプ(記事デフォルト画像)
	const ACT_UPLOAD_IMAGE	= 'uploadimage';			// 画像アップロード
	const ACT_GET_IMAGE		= 'getimage';		// 画像取得
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->db = new custom_searchDb();
		
		// 実際に表示する画面の描画出力タイプを取得
		$templateType = $this->_getTemplateType($this->gSystem->defaultTemplateId());// デフォルトのテンプレートIDからテンプレートタイプを取得
		$this->targetRenderType = $this->_getRenderType($templateType);
		
		// 作業ディレクトリを取得
		$this->tmpDir = $this->gEnv->getTempDirBySession();		// セッション単位の作業ディレクトリパスを取得
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
		$task = $request->trimValueOf('task');
		if ($task == 'list'){		// 一覧画面
			// 通常のテンプレート処理を組み込みのテンプレート処理に変更。_setTemplate()、_assign()はキャンセル。
			$this->replaceAssignTemplate(self::ASSIGN_TEMPLATE_BASIC_CONFIG_LIST);		// 設定一覧(基本)
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
		return 'admin.tmpl.html';
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
		return $this->createDetail($request);
	}
	/**
	 * テンプレートにデータ埋め込む
	 *
	 * _setTemplate()で指定したテンプレートファイルにデータを埋め込む。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。_setTemplate()と共有。
	 * @return								なし
	 */
	function _postAssign($request, &$param)
	{
		// メニューバー、パンくずリスト作成(簡易版)
		$this->createBasicConfigMenubar($request);
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
		
		// パラメータ初期化
		$userId		= $this->gEnv->getCurrentUserId();
		$this->langId	= $this->gEnv->getCurrentLanguage();		// 表示言語を取得
		
		// 入力値を取得(共通)
		$act = $request->trimValueOf('act');
		$this->serialNo = $request->trimValueOf('serial');		// 選択項目のシリアル番号
		$this->configId = $request->trimValueOf('item_id');		// 定義ID
		if (empty($this->configId)) $this->configId = $defConfigId;		// 呼び出しウィンドウから引き継いだ定義ID
		
		// 入力値を取得(その他)
		$name	= $request->trimValueOf('item_name');			// ヘッダタイトル
		$searchTemplate = $request->valueOf('item_html');		// 検索用テンプレート
		$resultCount	= $request->valueOf('item_result_count');			// 表示項目数
		$resultLength	= $request->valueOf('item_result_length');			// テキストサイズ
		$this->searchTextId = $request->trimValueOf('item_search_text');		// 検索用テキストフィールドのタグID
		$this->searchButtonId = $request->trimValueOf('item_search_button');		// 検索用ボタンのタグID
		$this->searchResetId = $request->trimValueOf('item_search_reset');		// 検索エリアリセットボタンのタグID
		$showImage		= $request->trimCheckedValueOf('item_show_image');		// 画像を表示するかどうか
		$this->imageType	= $request->trimValueOf('item_image_type');				// 画像タイプ
		$imageWidth		= $request->trimIntValueOf('item_image_width', '0');			// 画像幅(空文字列をOKとする)
		$imageHeight	= $request->trimIntValueOf('item_image_height', '0');			// 画像高さ(空文字列をOKとする)
		$updatedEntryImage	= $request->trimValueOf('updated_entryimage');		// 記事デフォルト画像更新フラグ

		// 検索対象
		$isTargetContent = ($request->trimValueOf('item_target_content') == 'on') ? 1 : 0;		// 汎用コンテンツを検索対象とするかどうか
		$isTargetBlog = ($request->trimValueOf('item_target_blog') == 'on') ? 1 : 0;			// ブログ記事を検索対象とするかどうか
		$isTargetProduct = ($request->trimValueOf('item_target_product') == 'on') ? 1 : 0;			// 商品情報を検索対象とするかどうか
		$isTargetEvent = ($request->trimValueOf('item_target_event') == 'on') ? 1 : 0;			// イベント情報を検索対象とするかどうか
		$isTargetBbs = ($request->trimValueOf('item_target_bbs') == 'on') ? 1 : 0;			// BBSを検索対象とするかどうか
		$isTargetPhoto = ($request->trimValueOf('item_target_photo') == 'on') ? 1 : 0;			// フォトギャラリーを検索対象とするかどうか
		$isTargetWiki = ($request->trimValueOf('item_target_wiki') == 'on') ? 1 : 0;			// Wikiを検索対象とするかどうか
		
		// Pタグを除去
		$searchTemplate = $this->gInstance->getTextConvManager()->deleteTag($searchTemplate, 'p');

		$replaceNew = false;		// データを再取得するかどうか
		if (empty($act)){// 初期起動時
			// デフォルト値設定
			$this->configId = $defConfigId;		// 呼び出しウィンドウから引き継いだ定義ID
			$replaceNew = true;			// データ再取得
			
			// 作業ディレクトリを削除
			rmDirectory($this->tmpDir);
		} else if ($act == 'add'){// 新規追加
			// 入力チェック
			$this->checkInput($name, '名前');
			if (empty($isTargetContent) && empty($isTargetBlog) && empty($isTargetProduct) && empty($isTargetEvent) && 
					empty($isTargetBbs) && empty($isTargetPhoto) && empty($isTargetWiki)) $this->setUserErrorMsg('検索対象が選択されていません');
			$this->checkNumeric($resultCount, '表示件数');
			$this->checkNumeric($resultLength, 'テキストサイズ');
			
			// 設定名の重複チェック
			if (is_array($this->paramObj)){
				for ($i = 0; $i < count($this->paramObj); $i++){
					$targetObj = $this->paramObj[$i]->object;
					if ($name == $targetObj->name){		// 定義名
						$this->setUserErrorMsg('名前が重複しています');
						break;
					}
				}
			}
			
			// 記事デフォルト画像のエラーチェック
			if (!empty($updatedEntryImage)){
				list($entryImageFilenameArray, $tmpArray) = $this->gInstance->getImageManager()->getSystemThumbFilename(self::DEFAULT_IMAGE_FILENAME_HEAD/*デフォルト画像*/);
				for ($i = 0; $i < count($entryImageFilenameArray); $i++){
					$path = $this->tmpDir . DIRECTORY_SEPARATOR . $entryImageFilenameArray[$i];
					if (!file_exists($path)){
						$this->setAppErrorMsg('コンテンツデフォルト画像が正常にアップロードされていません');
						break;
					}
				}
			}
			
			// エラーなしの場合は、データを登録
			if ($this->getMsgCount() == 0){
				// 空の場合デフォルト値を設定
				if (empty($searchTemplate)){
					if ($this->targetRenderType == M3_RENDER_BOOTSTRAP){
						$searchTemplate = $this->getParsedTemplateData('default_bootstrap.tmpl.html', array($this, 'makeSearcheTemplate'));// デフォルト用の検索テンプレート
					} else {
						$searchTemplate = $this->getParsedTemplateData('default.tmpl.html', array($this, 'makeSearcheTemplate'));// デフォルト用の検索テンプレート
					}
				}
				
				// 画像の移動
				if (!empty($updatedEntryImage)){		// 画像更新の場合
					$ret = mvFileToDir($this->tmpDir, $entryImageFilenameArray, $this->gInstance->getImageManager()->getSystemThumbPath(M3_VIEW_TYPE_SEARCH, 0/*PC用*/, ''/*ディレクトリ取得*/));
				}
				
				// 追加オブジェクト作成
				$newObj = new stdClass;
				$newObj->name	= $name;// 表示名
				$newObj->resultCount	= $resultCount;// 表示件数
				$newObj->resultLength	= $resultLength;			// テキストサイズ
				$newObj->showImage		= $showImage;		// 画像を表示するかどうか
				$newObj->imageType		= $this->imageType;				// 画像タイプ
				$newObj->imageWidth		= intval($imageWidth);			// 画像幅
				$newObj->imageHeight		= intval($imageHeight);			// 画像高さ
				$newObj->searchTemplate = $searchTemplate;		// 検索用テンプレート
				$newObj->searchTextId = $this->searchTextId;		// 検索用テキストフィールドのタグID
				$newObj->searchButtonId = $this->searchButtonId;		// 検索用ボタンのタグID
				$newObj->searchResetId = $this->searchResetId;		// 検索エリアリセットボタンのタグID
				$newObj->isTargetContent = $isTargetContent;		// 汎用コンテンツを検索対象とするかどうか
				$newObj->isTargetBlog = $isTargetBlog;			// ブログ記事を検索対象とするかどうか
				$newObj->isTargetProduct = $isTargetProduct;			// 商品情報を検索対象とするかどうか
				$newObj->isTargetEvent = $isTargetEvent;			// イベント情報を検索対象とするかどうか
				$newObj->isTargetBbs = $isTargetBbs;			// BBSを検索対象とするかどうか
				$newObj->isTargetPhoto = $isTargetPhoto;			// フォトギャラリーを検索対象とするかどうか
				$newObj->isTargetWiki = $isTargetWiki;			// Wikiを検索対象とするかどうか
				
				$ret = $this->addPageDefParam($defSerial, $defConfigId, $this->paramObj, $newObj);
				if ($ret){
					$this->setGuidanceMsg('データを追加しました');
					
					$this->configId = $defConfigId;		// 定義定義IDを更新
					$replaceNew = true;			// データ再取得
					
					// 作業ディレクトリを削除
					rmDirectory($this->tmpDir);
				} else {
					$this->setAppErrorMsg('データ追加に失敗しました');
				}
			}
		} else if ($act == 'update'){		// 設定更新のとき
			// 入力値のエラーチェック
			if (empty($isTargetContent) && empty($isTargetBlog) && empty($isTargetProduct) && empty($isTargetEvent) && 
					empty($isTargetBbs) && empty($isTargetPhoto) && empty($isTargetWiki)) $this->setUserErrorMsg('検索対象が選択されていません');
			$this->checkNumeric($resultCount, '表示件数');
			$this->checkNumeric($resultLength, 'テキストサイズ');
			
			// 記事デフォルト画像のエラーチェック
			if (!empty($updatedEntryImage)){
				list($entryImageFilenameArray, $tmpArray) = $this->gInstance->getImageManager()->getSystemThumbFilename(self::DEFAULT_IMAGE_FILENAME_HEAD/*デフォルト画像*/);
				for ($i = 0; $i < count($entryImageFilenameArray); $i++){
					$path = $this->tmpDir . DIRECTORY_SEPARATOR . $entryImageFilenameArray[$i];
					if (!file_exists($path)){
						$this->setAppErrorMsg('コンテンツデフォルト画像が正常にアップロードされていません');
						break;
					}
				}
			}
			
			if ($this->getMsgCount() == 0){			// エラーのないとき
				// 空の場合デフォルト値を設定
				if (empty($searchTemplate)){
					if ($this->targetRenderType == M3_RENDER_BOOTSTRAP){
						$searchTemplate = $this->getParsedTemplateData('default_bootstrap.tmpl.html', array($this, 'makeSearcheTemplate'));// デフォルト用の検索テンプレート
					} else {
						$searchTemplate = $this->getParsedTemplateData('default.tmpl.html', array($this, 'makeSearcheTemplate'));// デフォルト用の検索テンプレート
					}
				}
				
				// 画像の移動
				if (!empty($updatedEntryImage)){		// 画像更新の場合
					$ret = mvFileToDir($this->tmpDir, $entryImageFilenameArray, $this->gInstance->getImageManager()->getSystemThumbPath(M3_VIEW_TYPE_SEARCH, 0/*PC用*/, ''/*ディレクトリ取得*/));
				}
				
				// 現在の設定値を取得
				$ret = $this->getPageDefParam($defSerial, $defConfigId, $this->paramObj, $this->configId, $targetObj);
				if ($ret){
					// ウィジェットオブジェクト更新
					$targetObj->resultCount	= $resultCount;// 表示件数
					$targetObj->resultLength	= $resultLength;			// テキストサイズ
					$targetObj->showImage		= $showImage;		// 画像を表示するかどうか
					$targetObj->imageType		= $this->imageType;				// 画像タイプ
					$targetObj->imageWidth		= intval($imageWidth);			// 画像幅
					$targetObj->imageHeight		= intval($imageHeight);			// 画像高さ
					$targetObj->searchTemplate	= $searchTemplate;		// 検索用テンプレート
					$targetObj->searchTextId = $this->searchTextId;		// 検索用テキストフィールドのタグID
					$targetObj->searchButtonId = $this->searchButtonId;		// 検索用ボタンのタグID
					$targetObj->searchResetId = $this->searchResetId;		// 検索エリアリセットボタンのタグID
					$targetObj->isTargetContent = $isTargetContent;		// 汎用コンテンツを検索対象とするかどうか
					$targetObj->isTargetBlog = $isTargetBlog;			// ブログ記事を検索対象とするかどうか
					$targetObj->isTargetProduct = $isTargetProduct;			// 商品情報を検索対象とするかどうか
					$targetObj->isTargetEvent = $isTargetEvent;			// イベント情報を検索対象とするかどうか
					$targetObj->isTargetBbs = $isTargetBbs;			// BBSを検索対象とするかどうか
					$targetObj->isTargetPhoto = $isTargetPhoto;			// フォトギャラリーを検索対象とするかどうか
					$targetObj->isTargetWiki = $isTargetWiki;			// Wikiを検索対象とするかどうか
				}
				
				// 設定値を更新
				if ($ret) $ret = $this->updatePageDefParam($defSerial, $defConfigId, $this->paramObj, $this->configId, $targetObj);
				if ($ret){
					$this->setMsg(self::MSG_GUIDANCE, 'データを更新しました');
					
					$replaceNew = true;			// データ再取得
					
					// 作業ディレクトリを削除
					rmDirectory($this->tmpDir);
				} else {
					$this->setMsg(self::MSG_APP_ERR, 'データ更新に失敗しました');
				}
			}
		} else if ($act == self::ACT_UPLOAD_IMAGE){		// 画像アップロード
			// 作業ディレクトリを作成
			$this->tmpDir = $this->gEnv->getTempDirBySession(true/*ディレクトリ作成*/);		// セッション単位の作業ディレクトリを取得
			
			// Ajaxでのファイルアップロード処理
			$this->ajaxUploadFile($request, array($this, 'uploadFile'), $this->tmpDir);
		} else if ($act == self::ACT_GET_IMAGE){			// 画像取得
			$imageType = $request->trimValueOf('type');		// 画像タイプ
			
			// Ajaxでの画像取得
			$this->getImageByType($imageType);
		} else if ($act == 'select'){	// 定義IDを変更
			$replaceNew = true;			// データ再取得
		}
		
		// 表示用データを取得
		if (empty($this->configId)){		// 新規登録の場合
			$this->tmpl->setAttribute('item_name_visible', 'visibility', 'visible');// 名前入力フィールド表示
			if ($replaceNew){		// データ再取得時
				//$name = $this->createDefaultName();			// デフォルト登録項目名
				$name = $this->createConfigDefaultName();			// デフォルト登録項目名
				$resultCount	= self::DEFAULT_SEARCH_COUNT;			// 表示項目数
				$resultLength	= self::DEFAULT_RESULT_LENGTH;			// テキストサイズ
				$showImage		= 0;		// 画像を表示するかどうか
				$this->imageType	= self::DEFAULT_IMAGE_TYPE;				// 画像タイプ
				$imageWidth		= 0;			// 画像幅
				$imageHeight	= 0;			// 画像高さ
				
				// デフォルトの検索テンプレート作成
				$tagHead = $this->createTagIdHead();
				$this->searchTextId = $tagHead . '_text';		// 検索用テキストフィールドのタグID
				$this->searchButtonId = $tagHead . '_button';		// 検索用ボタンのタグID
				$this->searchResetId = $tagHead . '_reset';		// 検索エリアリセットボタンのタグID
				if ($this->targetRenderType == M3_RENDER_BOOTSTRAP){
					$searchTemplate = $this->getParsedTemplateData('default_bootstrap.tmpl.html', array($this, 'makeSearcheTemplate'));// デフォルト用の検索テンプレート
				} else {
					$searchTemplate = $this->getParsedTemplateData('default.tmpl.html', array($this, 'makeSearcheTemplate'));// デフォルト用の検索テンプレート
				}
				
				$isTargetContent = 1;		// 汎用コンテンツを検索対象とするかどうか
				$isTargetBlog = 1;			// ブログ記事を検索対象とするかどうか
				$isTargetProduct = 1;			// 商品情報を検索対象とするかどうか
				$isTargetEvent = 1;			// イベント情報を検索対象とするかどうか
				$isTargetBbs = 1;			// BBSを検索対象とするかどうか
				$isTargetPhoto = 1;			// フォトギャラリーを検索対象とするかどうか
				$isTargetWiki = 1;			// Wikiを検索対象とするかどうか
			}
			$this->serialNo = 0;
		} else {		// 更新の場合
			if ($replaceNew){
				$ret = $this->getPageDefParam($defSerial, $defConfigId, $this->paramObj, $this->configId, $targetObj);
				if ($ret){
					$name = $targetObj->name;// 名前
					$resultCount	= $targetObj->resultCount;			// 表示項目数
					$resultLength	= intval($targetObj->resultLength);			// テキストサイズ
					if ($resultLength <= 0) $resultLength = self::DEFAULT_RESULT_LENGTH;			// テキストサイズ
					$showImage			= $targetObj->showImage;		// 画像を表示するかどうか
					$this->imageType	= $targetObj->imageType;				// 画像タイプ
					$imageWidth			= intval($targetObj->imageWidth);			// 画像幅
					$imageHeight		= intval($targetObj->imageHeight);			// 画像高さ
					$this->searchTextId = $targetObj->searchTextId;		// 検索用テキストフィールドのタグID
					$this->searchButtonId = $targetObj->searchButtonId;		// 検索用ボタンのタグID
					$this->searchResetId = $targetObj->searchResetId;		// 検索エリアリセットボタンのタグID
					$isTargetContent = $targetObj->isTargetContent;		// 汎用コンテンツを検索対象とするかどうか
					$isTargetBlog = $targetObj->isTargetBlog;			// ブログ記事を検索対象とするかどうか
					$isTargetProduct = $targetObj->isTargetProduct;			// 商品情報を検索対象とするかどうか
					$isTargetEvent = $targetObj->isTargetEvent;			// イベント情報を検索対象とするかどうか
					$isTargetBbs = $targetObj->isTargetBbs;			// BBSを検索対象とするかどうか
					$isTargetPhoto = $targetObj->isTargetPhoto;			// フォトギャラリーを検索対象とするかどうか
					$isTargetWiki = $targetObj->isTargetWiki;			// Wikiを検索対象とするかどうか
					$searchTemplate = $targetObj->searchTemplate;		// 検索用テンプレート
				}
			}
			$this->serialNo = $this->configId;
				
			// 新規作成でないときは、メニューを変更不可にする(画面作成から呼ばれている場合のみ)
			if (!empty($defConfigId) && !empty($defSerial)) $this->tmpl->addVar("_widget", "id_disabled", 'disabled');
		}
		
		// 設定項目選択メニュー作成
		$this->createItemMenu();
		
		// 画像タイプ選択メニュー作成
		$this->createImageTypeList();
		
		// アップロード実行用URL
		$uploadUrl = $this->gEnv->getDefaultAdminUrl() . '?' . M3_REQUEST_PARAM_OPERATION_COMMAND . '=' . M3_REQUEST_CMD_CONFIG_WIDGET;	// ウィジェット設定画面
		$uploadUrl .= '&' . M3_REQUEST_PARAM_WIDGET_ID . '=' . $this->gEnv->getCurrentWidgetId();	// ウィジェットID
//		$uploadUrl .= '&' . M3_REQUEST_PARAM_OPERATION_TASK . '=' . self::TASK_CONFIG;
		$uploadUrl .= '&' . M3_REQUEST_PARAM_OPERATION_ACT . '=' . self::ACT_UPLOAD_IMAGE;
		$this->tmpl->addVar("_widget", "upload_url_entryimage", $this->getUrl($uploadUrl . '&type=' . self::IMAGE_TYPE_ENTRY_IMAGE));		// 記事デフォルト画像
		
		// ##### 画像の表示 #####
		// アップロードされているファイルがある場合は、アップロード画像を表示
		$updateStatus = '0';			// 画像更新状態
		$imageUrl = $this->getDefaultImageUrl(self::DEFAULT_IMAGE_TYPE, $filename) . '?' . date('YmdHis');
		$this->tmpl->addVar("_widget", "entryimage_url", $this->convertUrlToHtmlEntity($this->getUrl($imageUrl)));			// 記事デフォルト画像
		$this->tmpl->addVar("_widget", "updated_entryimage", $updateStatus);
		
		// 画面にデータを埋め込む
		$this->tmpl->addVar("item_name_visible", "name", $name);		// 名前
		$this->tmpl->addVar("_widget", "result_count",	$resultCount);			// 表示項目数
		$this->tmpl->addVar("_widget", "result_length",	$resultLength);			// テキストサイズ
		$this->tmpl->addVar("_widget", "show_image_checked",	$this->convertToCheckedString($showImage));// 画像を表示するかどうか
		$imageWidth = empty($imageWidth) ? '' : $imageWidth;
		$imageHeight = empty($imageHeight) ? '' : $imageHeight;
		$this->tmpl->addVar("_widget", "image_width",	$this->convertToDispString($imageWidth));// 画像幅
		$this->tmpl->addVar("_widget", "image_height",	$this->convertToDispString($imageHeight));// 画像高さ
		$this->tmpl->addVar("_widget", "upload_area", $this->gDesign->createDragDropFileUploadHtml());		// 画像アップロードエリア
		$this->tmpl->addVar("_widget", "html",	$searchTemplate);
		$this->tmpl->addVar("_widget", "search_text",	$this->searchTextId);		// 検索用テキストフィールドのタグID
		$this->tmpl->addVar("_widget", "search_button",	$this->searchButtonId);		// 検索用ボタンのタグID
		$this->tmpl->addVar("_widget", "search_reset",	$this->searchResetId);		// 検索エリアリセットボタンのタグID
		$tagStr = $this->searchTextId . '(入力フィールドのID), ' . $this->searchButtonId . '(検索実行ボタンのID), ' . $this->searchResetId . '(検索リセットボタンのID)';
		$this->tmpl->addVar("_widget", "tag_id_str", $tagStr);// タグIDの表示
		if (!empty($isTargetContent)) $this->tmpl->addVar('_widget', 'target_content_checked', 'checked');		// 汎用コンテンツを検索対象とするかどうか
		if (!empty($isTargetBlog)) $this->tmpl->addVar('_widget', 'target_blog_checked', 'checked');			// ブログ記事を検索対象とするかどうか
		if (!empty($isTargetProduct)) $this->tmpl->addVar('_widget', 'target_product_checked', 'checked');			// 商品情報を検索対象とするかどうか
		if (!empty($isTargetEvent)) $this->tmpl->addVar('_widget', 'target_event_checked', 'checked');			// イベント情報を検索対象とするかどうか
		if (!empty($isTargetBbs)) $this->tmpl->addVar('_widget', 'target_bbs_checked', 'checked');			// BBSを検索対象とするかどうか
		if (!empty($isTargetPhoto)) $this->tmpl->addVar('_widget', 'target_photo_checked', 'checked');			// フォトギャラリーを検索対象とするかどうか
		if (!empty($isTargetWiki)) $this->tmpl->addVar('_widget', 'target_wiki_checked', 'checked');			// Wikiを検索対象とするかどうか
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
	 * 検索テンプレートデータ作成処理コールバック
	 *
	 * @param object         $tmpl			テンプレートオブジェクト
	 * @param								なし
	 */
	function makeSearcheTemplate($tmpl)
	{
		$tmpl->addVar("_tmpl", "widget_url",	$this->gEnv->getCurrentWidgetRootUrl());		// ウィジェットのURL
		$tmpl->addVar("_tmpl", "search_text_id",	$this->searchTextId);		// 検索用テキストフィールドのタグID
		$tmpl->addVar("_tmpl", "search_button_id",	$this->searchButtonId);		// 検索用ボタンのタグID
		$tmpl->addVar("_tmpl", "search_reset_id",	$this->searchResetId);		// 検索エリアリセットボタンのタグID
	}
	/**
	 * inputタグID用のヘッダ文字列を作成
	 *
	 * @return string	ID						
	 */
	function createTagIdHead()
	{
		return $this->gEnv->getCurrentWidgetId() . '_' . $this->getTempConfigId($this->paramObj);
	}
	/**
	 * 画像タイプ選択メニュー作成
	 *
	 * @return なし
	 */
	function createImageTypeList()
	{
		$formats = $this->gInstance->getImageManager()->getSystemThumbFormat(1/*クロップ画像のみ*/);
		
		for ($i = 0; $i < count($formats); $i++){
			$id = $formats[$i];
			$name = $id;
			
			$selected = '';
			if ($id == $this->imageType) $selected = 'selected';

			$row = array(
				'value'			=> $this->convertToDispString($id),				// 値
				'name'			=> $this->convertToDispString($name),			// 名前
				'selected'		=> $selected			// 選択中かどうか
			);
			$this->tmpl->addVars('image_type_list', $row);
			$this->tmpl->parseTemplate('image_type_list', 'a');
		}
	}
	/**
	 * 最大画像を取得
	 *
	 * @param string $type		画像タイプ
	 * @return					なし
	 */
	function getImageByType($type)
	{
		// 画像パス作成
		switch ($type){
		case self::IMAGE_TYPE_ENTRY_IMAGE:			// 記事デフォルト画像
//			$filename = $this->getDefaultEntryImageFilename();		// 記事デフォルト画像名取得
			$this->getDefaultImageUrl(self::DEFAULT_IMAGE_TYPE, $filename);
			break;
		}
		$imagePath = '';
		if (!empty($filename)) $imagePath = $this->gEnv->getTempDirBySession() . '/' . $filename;
			
		// ページ作成処理中断
		$this->gPage->abortPage();

		if (is_readable($imagePath)){
			// 画像情報を取得
			$imageMimeType = '';
			$imageSize = @getimagesize($imagePath);
			if ($imageSize) $imageMimeType = $imageSize['mime'];	// ファイルタイプを取得
			
			// 画像MIMEタイプ設定
			if (!empty($imageMimeType)) header('Content-type: ' . $imageMimeType);
			
			// キャッシュの設定
			header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');// 過去の日付
			header('Cache-Control: no-store, no-cache, must-revalidate');// HTTP/1.1
			header('Cache-Control: post-check=0, pre-check=0');
			header('Pragma: no-cache');
		
			// 画像ファイル読み込み
			readfile($imagePath);
		} else {
			$this->gPage->showError(404);
		}
	
		// システム強制終了
		$this->gPage->exitSystem();
	}
	/**
	 * アップロードファイルから各種画像を作成
	 *
	 * @param bool           $isSuccess		アップロード成功かどうか
	 * @param object         $resultObj		アップロード処理結果オブジェクト
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param string         $filePath		アップロードされたファイル
	 * @param string         $destDir		アップロード先ディレクトリ
	 * @return								なし
	 */
	function uploadFile($isSuccess, &$resultObj, $request, $filePath, $destDir)
	{
		$type = $request->trimValueOf('type');		// 画像タイプ
		
		if ($isSuccess){		// ファイルアップロード成功のとき
			// 各種画像を作成
			switch ($type){
			case self::IMAGE_TYPE_ENTRY_IMAGE:			// 記事デフォルト画像
				$formats = $this->gInstance->getImageManager()->getSystemThumbFormat();
				$filenameBase = self::DEFAULT_IMAGE_FILENAME_HEAD;
				break;
			}

			$ret = $this->gInstance->getImageManager()->createImageByFormat($filePath, $formats, $destDir, $filenameBase, $destFilename);
			if ($ret){			// 画像作成成功の場合
				// 画像参照用URL
				$imageUrl = $this->gEnv->getDefaultAdminUrl() . '?' . M3_REQUEST_PARAM_OPERATION_COMMAND . '=' . M3_REQUEST_CMD_CONFIG_WIDGET;	// ウィジェット設定画面
				$imageUrl .= '&' . M3_REQUEST_PARAM_WIDGET_ID . '=' . $this->gEnv->getCurrentWidgetId();	// ウィジェットID
//				$imageUrl .= '&' . M3_REQUEST_PARAM_OPERATION_TASK . '=' . self::TASK_CONFIG;
				$imageUrl .= '&' . M3_REQUEST_PARAM_OPERATION_ACT . '=' . self::ACT_GET_IMAGE;
				$imageUrl .= '&type=' . $type . '&' . date('YmdHis');
				$resultObj['url'] = $imageUrl;
			} else {// エラーの場合
				$resultObj = array('error' => 'Could not create resized images.');
			}
		}
	}
	/**
	 * デフォルトの画像のURLを取得
	 *
	 * @param string $format		画像フォーマット
	 * @param string $filename		ファイル名
	 * @return string				URL
	 */
	function getDefaultImageUrl($format, &$filename)
	{
		$filename = $this->gInstance->getImageManager()->getThumbFilename(self::DEFAULT_IMAGE_FILENAME_HEAD, $format);
		$url = $this->gInstance->getImageManager()->getSystemThumbUrl(M3_VIEW_TYPE_SEARCH, 0/*PC用*/, $filename);
		return $url;
	}
}
?>
