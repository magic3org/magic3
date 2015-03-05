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
 * @copyright  Copyright 2010-2015 株式会社 毎日メディアサービス.
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
	private $fieldInfoArray = array();			// 項目定義
	private $categoryArray;		// カテゴリ種別メニュー
	private $selTypeArray;	// 項目選択タイプメニュー
	private $imageType;		// 選択中の画像タイプ
	const DEFAULT_NAME_HEAD = '名称未設定';			// デフォルトの設定名
	const MESSAGE_NO_USER_CATEGORY = 'カテゴリが登録されていません';			// ユーザ作成コンテンツ用のカテゴリが登録されていないときのメッセージ
	const DEFAULT_SEARCH_COUNT	= 20;				// デフォルトの検索結果表示数
	const DEFAULT_RESULT_LENGTH = 200;			// 検索結果コンテンツの文字列最大長
	const DEFAULT_IMAGE_TYPE = '80c.jpg';		// デフォルトの画像タイプ
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->db = new custom_searchDb();
		
		// 項目選択タイプ
		$this->selTypeArray = array(	array(	'name' => '単一選択',	'value' => 'single'),
										array(	'name' => '複数選択',	'value' => 'multi'));
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
		$task = $request->trimValueOf('task');
//		if ($task == 'list'){		// 一覧画面
//			return 'admin_list.tmpl.html';
//		} else {			// 一覧画面
			return 'admin.tmpl.html';
//		}
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
//		$task = $request->trimValueOf('task');
//		if ($task == 'list'){		// 一覧画面
//			return $this->createList($request);
//		} else {			// 詳細設定画面
			return $this->createDetail($request);
//		}
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

		// 検索対象
		$isTargetContent = ($request->trimValueOf('item_target_content') == 'on') ? 1 : 0;		// 汎用コンテンツを検索対象とするかどうか
		$isTargetUser = ($request->trimValueOf('item_target_user') == 'on') ? 1 : 0;			// ユーザ作成コンテンツを検索対象とするかどうか
		$isTargetBlog = ($request->trimValueOf('item_target_blog') == 'on') ? 1 : 0;			// ブログ記事を検索対象とするかどうか
		$isTargetProduct = ($request->trimValueOf('item_target_product') == 'on') ? 1 : 0;			// 商品情報を検索対象とするかどうか
		$isTargetEvent = ($request->trimValueOf('item_target_event') == 'on') ? 1 : 0;			// イベント情報を検索対象とするかどうか
		$isTargetBbs = ($request->trimValueOf('item_target_bbs') == 'on') ? 1 : 0;			// BBSを検索対象とするかどうか
		$isTargetPhoto = ($request->trimValueOf('item_target_photo') == 'on') ? 1 : 0;			// フォトギャラリーを検索対象とするかどうか
		$isTargetWiki = ($request->trimValueOf('item_target_wiki') == 'on') ? 1 : 0;			// Wikiを検索対象とするかどうか
		
		// カテゴリ項目定義
		$fieldCount = intval($request->trimValueOf('fieldcount'));		// カテゴリ定義項目数
		$categoryTypes = $request->trimValueOf('item_type');					// カテゴリ種別
		$selectTypes = $request->trimValueOf('item_sel_type');			// カテゴリ選択方法
		$values = $request->trimValueOf('titlevisible');			// タイトルの表示制御
		$titleVisibles = array();
		if (strlen($values) > 0) $titleVisibles = explode(',', $values);
		$initValues = $request->trimValueOf('item_init_value');			// 初期値
		
		// カテゴリ設定を取得
		$this->fieldInfoArray = array();
		for ($i = 0; $i < $fieldCount; $i++){
			$newInfoObj = new stdClass;
			$newInfoObj->categoryType = $categoryTypes[$i];
			$newInfoObj->selectType = $selectTypes[$i];
			$newInfoObj->titleVisible = $titleVisibles[$i];
			$newInfoObj->initValue = $initValues[$i];
			$this->fieldInfoArray[] = $newInfoObj;
		}
		
		// Pタグを除去
		$searchTemplate = $this->gInstance->getTextConvManager()->deleteTag($searchTemplate, 'p');

		$replaceNew = false;		// データを再取得するかどうか
		if (empty($act)){// 初期起動時
			// デフォルト値設定
			$this->configId = $defConfigId;		// 呼び出しウィンドウから引き継いだ定義ID
			$replaceNew = true;			// データ再取得
		} else if ($act == 'add'){// 新規追加
			// 入力チェック
			$this->checkInput($name, '名前');
			if (empty($isTargetContent) && empty($isTargetUser) && empty($isTargetBlog) && empty($isTargetProduct) && empty($isTargetEvent) && 
					empty($isTargetBbs) && empty($isTargetPhoto) && empty($isTargetWiki)) $this->setUserErrorMsg('検索対象が選択されていません');
			$this->checkNumeric($resultCount, '表示件数');
			$this->checkNumeric($resultLength, 'テキストサイズ');
			
			// 設定名の重複チェック
			for ($i = 0; $i < count($this->paramObj); $i++){
				$targetObj = $this->paramObj[$i]->object;
				if ($name == $targetObj->name){		// 定義名
					$this->setUserErrorMsg('名前が重複しています');
					break;
				}
			}
			
			// エラーなしの場合は、データを登録
			if ($this->getMsgCount() == 0){
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
				$newObj->isTargetUser = $isTargetUser;			// ユーザ作成コンテンツを検索対象とするかどうか
				$newObj->isTargetBlog = $isTargetBlog;			// ブログ記事を検索対象とするかどうか
				$newObj->isTargetProduct = $isTargetProduct;			// 商品情報を検索対象とするかどうか
				$newObj->isTargetEvent = $isTargetEvent;			// イベント情報を検索対象とするかどうか
				$newObj->isTargetBbs = $isTargetBbs;			// BBSを検索対象とするかどうか
				$newObj->isTargetPhoto = $isTargetPhoto;			// フォトギャラリーを検索対象とするかどうか
				$newObj->isTargetWiki = $isTargetWiki;			// Wikiを検索対象とするかどうか
				$newObj->fieldInfo = $this->fieldInfoArray;			// カテゴリ定義
				
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
			if (empty($isTargetContent) && empty($isTargetUser) && empty($isTargetBlog) && empty($isTargetProduct) && empty($isTargetEvent) && 
					empty($isTargetBbs) && empty($isTargetPhoto) && empty($isTargetWiki)) $this->setUserErrorMsg('検索対象が選択されていません');
			$this->checkNumeric($resultCount, '表示件数');
			$this->checkNumeric($resultLength, 'テキストサイズ');
			
			if ($this->getMsgCount() == 0){			// エラーのないとき
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
					$targetObj->isTargetUser = $isTargetUser;			// ユーザ作成コンテンツを検索対象とするかどうか
					$targetObj->isTargetBlog = $isTargetBlog;			// ブログ記事を検索対象とするかどうか
					$targetObj->isTargetProduct = $isTargetProduct;			// 商品情報を検索対象とするかどうか
					$targetObj->isTargetEvent = $isTargetEvent;			// イベント情報を検索対象とするかどうか
					$targetObj->isTargetBbs = $isTargetBbs;			// BBSを検索対象とするかどうか
					$targetObj->isTargetPhoto = $isTargetPhoto;			// フォトギャラリーを検索対象とするかどうか
					$targetObj->isTargetWiki = $isTargetWiki;			// Wikiを検索対象とするかどうか
					$targetObj->fieldInfo = $this->fieldInfoArray;			// カテゴリ定義
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
		}
		
		// 表示用データを取得
		if (empty($this->configId)){		// 新規登録の場合
			$this->tmpl->setAttribute('item_name_visible', 'visibility', 'visible');// 名前入力フィールド表示
			if ($replaceNew){		// データ再取得時
				$name = $this->createDefaultName();			// デフォルト登録項目名
				$resultCount	= self::DEFAULT_SEARCH_COUNT;			// 表示項目数
				$resultLength	= self::DEFAULT_RESULT_LENGTH;			// テキストサイズ
				$showImage		= 0;		// 画像を表示するかどうか
				$this->imageType	= self::DEFAULT_IMAGE_TYPE;				// 画像タイプ
				$imageWidth		= 0;			// 画像幅
				$imageHeight	= 0;			// 画像高さ
				$this->fieldInfoArray = array();			// 項目定義
				
				// デフォルトの検索テンプレート作成
				$tagHead = $this->createTagIdHead();
				$this->searchTextId = $tagHead . '_text';		// 検索用テキストフィールドのタグID
				$this->searchButtonId = $tagHead . '_button';		// 検索用ボタンのタグID
				$this->searchResetId = $tagHead . '_reset';		// 検索エリアリセットボタンのタグID
				$searchTemplate = $this->getParsedTemplateData('default.tmpl.html', array($this, 'makeSearcheTemplate'));// デフォルト用の検索テンプレート
				
				$isTargetContent = 1;		// 汎用コンテンツを検索対象とするかどうか
				$isTargetUser = 1;			// ユーザ作成コンテンツを検索対象とするかどうか
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
					$isTargetUser = $targetObj->isTargetUser;			// ユーザ作成コンテンツを検索対象とするかどうか
					$isTargetBlog = $targetObj->isTargetBlog;			// ブログ記事を検索対象とするかどうか
					$isTargetProduct = $targetObj->isTargetProduct;			// 商品情報を検索対象とするかどうか
					$isTargetEvent = $targetObj->isTargetEvent;			// イベント情報を検索対象とするかどうか
					$isTargetBbs = $targetObj->isTargetBbs;			// BBSを検索対象とするかどうか
					$isTargetPhoto = $targetObj->isTargetPhoto;			// フォトギャラリーを検索対象とするかどうか
					$isTargetWiki = $targetObj->isTargetWiki;			// Wikiを検索対象とするかどうか
					$searchTemplate = $targetObj->searchTemplate;		// 検索用テンプレート
					if (!empty($targetObj->fieldInfo)) $this->fieldInfoArray = $targetObj->fieldInfo;			// 項目定義
				}
			}
			$this->serialNo = $this->configId;
				
			// 新規作成でないときは、メニューを変更不可にする(画面作成から呼ばれている場合のみ)
			if (!empty($defConfigId) && !empty($defSerial)) $this->tmpl->addVar("_widget", "id_disabled", 'disabled');
		}
		
		// 設定項目選択メニュー作成
		$this->createItemMenu();
		
		// 画像タイプ選択メニュー作成
		$this->createpImageTypeList();
		
		// カテゴリ情報取得
		$this->categoryArray = array();		// カテゴリ種別メニュー
		$ret = $this->db->getAllCategory($this->langId, $rows);
		if ($ret){
			for ($i = 0; $i < count($rows); $i++){
				$line = array();
				$line['name'] = $rows[$i]['ua_name'];
				$line['value'] = $rows[$i]['ua_id'];
				$this->categoryArray[] = $line;
			}
		}
		
		// メニュー作成
		$this->createCategoryMenu();
		$this->createSelTypeMenu();
		
		// 項目定義一覧を作成
		$this->createFieldList();
		if (empty($this->fieldInfoArray)) $this->tmpl->setAttribute('field_list', 'visibility', 'hidden');// 項目定義一覧を隠す
		
		// メッセージ設定
		if (empty($this->categoryArray)){			// 絞り込みカテゴリが登録されていないとき
			$messageStr = '<b><font color="red">' . self::MESSAGE_NO_USER_CATEGORY . '</font></b>';
			$this->tmpl->addVar("_widget", "user_content_message",	$messageStr);		// ユーザ作成コンテンツ用メッセージ
		}
		
		// 画面にデータを埋め込む
		$this->tmpl->addVar("item_name_visible", "name", $name);		// 名前
		$this->tmpl->addVar("_widget", "result_count",	$resultCount);			// 表示項目数
		$this->tmpl->addVar("_widget", "result_length",	$resultLength);			// テキストサイズ
		$this->tmpl->addVar("_widget", "show_image_checked",	$this->convertToCheckedString($showImage));// 画像を表示するかどうか
		$imageWidth = empty($imageWidth) ? '' : $imageWidth;
		$imageHeight = empty($imageHeight) ? '' : $imageHeight;
		$this->tmpl->addVar("_widget", "image_width",	$this->convertToDispString($imageWidth));// 画像幅
		$this->tmpl->addVar("_widget", "image_height",	$this->convertToDispString($imageHeight));// 画像高さ
		$this->tmpl->addVar("_widget", "html",	$searchTemplate);
		$this->tmpl->addVar("_widget", "search_text",	$this->searchTextId);		// 検索用テキストフィールドのタグID
		$this->tmpl->addVar("_widget", "search_button",	$this->searchButtonId);		// 検索用ボタンのタグID
		$this->tmpl->addVar("_widget", "search_reset",	$this->searchResetId);		// 検索エリアリセットボタンのタグID
		$tagStr = $this->searchTextId . '(入力フィールドのID), ' . $this->searchButtonId . '(検索実行ボタンのID), ' . $this->searchResetId . '(検索リセットボタンのID)';
		$this->tmpl->addVar("_widget", "tag_id_str", $tagStr);// タグIDの表示
		$this->tmpl->addVar('_widget', 'tag_start', M3_TAG_START . M3_TAG_MACRO_ITEM_KEY);		// 置換タグ(前)
		$this->tmpl->addVar('_widget', 'tag_end', M3_TAG_END);		// 置換タグ(後)
		if (!empty($isTargetContent)) $this->tmpl->addVar('_widget', 'target_content_checked', 'checked');		// 汎用コンテンツを検索対象とするかどうか
		if (!empty($isTargetUser)) $this->tmpl->addVar('_widget', 'target_user_checked', 'checked');			// ユーザ作成コンテンツを検索対象とするかどうか
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
	function createDefaultName()
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
	 * 項目定義一覧を作成
	 *
	 * @return なし						
	 */
	function createFieldList()
	{
		$fieldCount = count($this->fieldInfoArray);
		for ($i = 0; $i < $fieldCount; $i++){
			$infoObj = $this->fieldInfoArray[$i];
			$categoryType = $infoObj->categoryType;// カテゴリ種別
			$selectType = $infoObj->selectType;		// 選択方法
			$titleVisible = $infoObj->titleVisible;		// タイトル表示制御
			$initValue = $infoObj->initValue;		// 初期値

			// カテゴリ種別メニュー作成
			$this->tmpl->clearTemplate('type_list2');
			
			for ($j = 0; $j < count($this->categoryArray); $j++){
				$value = $this->categoryArray[$j]['value'];
				$name = $this->categoryArray[$j]['name'];

				$selected = '';
				if ($value == $categoryType) $selected = 'selected';

				$tableLine = array(
					'value'    => $value,			// タイプ値
					'name'     => $this->convertToDispString($name),			// タイプ名
					'selected' => $selected			// 選択中かどうか
				);
				$this->tmpl->addVars('type_list2', $tableLine);
				$this->tmpl->parseTemplate('type_list2', 'a');
			}
			
			// 選択方法メニュー作成
			$this->tmpl->clearTemplate('sel_type_list2');
			
			for ($j = 0; $j < count($this->selTypeArray); $j++){
				$value = $this->selTypeArray[$j]['value'];
				$name = $this->selTypeArray[$j]['name'];

				$selected = '';
				if ($value == $selectType) $selected = 'selected';

				$tableLine = array(
					'value'    => $value,			// タイプ値
					'name'     => $this->convertToDispString($name),			// タイプ名
					'selected' => $selected			// 選択中かどうか
				);
				$this->tmpl->addVars('sel_type_list2', $tableLine);
				$this->tmpl->parseTemplate('sel_type_list2', 'a');
			}
			
			// タイトルの表示制御
			$checked = '';
			if (!empty($titleVisible)) $checked = 'checked';
			
			$row = array(
				'root_url' => $this->convertToDispString($this->getUrl($this->gEnv->getRootUrl())),
				'title_visible_checked' => $checked,
				'init_value' => $this->convertToDispString($initValue)
			);
			$this->tmpl->addVars('field_list', $row);
			$this->tmpl->parseTemplate('field_list', 'a');
		}
	}
	/**
	 * カテゴリメニュー作成
	 *
	 * @return なし
	 */
	function createCategoryMenu()
	{
		for ($i = 0; $i < count($this->categoryArray); $i++){
			$value = $this->categoryArray[$i]['value'];
			$name = $this->categoryArray[$i]['name'];
			
			$row = array(
				'value'    => $value,			// タイプ値
				'name'     => $this->convertToDispString($name),			// タイプ名
				'selected' => $selected			// 選択中かどうか
			);
			$this->tmpl->addVars('type_list', $row);
			$this->tmpl->parseTemplate('type_list', 'a');
		}
	}
	/**
	 * 項目選択タイプメニュー作成
	 *
	 * @return なし
	 */
	function createSelTypeMenu()
	{
		for ($i = 0; $i < count($this->selTypeArray); $i++){
			$value = $this->selTypeArray[$i]['value'];
			$name = $this->selTypeArray[$i]['name'];
			
			$row = array(
				'value'    => $value,			// タイプ値
				'name'     => $this->convertToDispString($name),			// タイプ名
				'selected' => $selected			// 選択中かどうか
			);
			$this->tmpl->addVars('sel_type_list', $row);
			$this->tmpl->parseTemplate('sel_type_list', 'a');
		}
	}
	/**
	 * 画像タイプ選択メニュー作成
	 *
	 * @return なし
	 */
	function createpImageTypeList()
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
}
?>
