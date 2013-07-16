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
 * @copyright  Copyright 2006-2011 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: admin_picasaWidgetContainer.php 4233 2011-07-25 09:39:04Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseAdminWidgetContainer.php');

class admin_picasaWidgetContainer extends BaseAdminWidgetContainer
{
	private $sysDb;	// DB接続オブジェクト
	private $serialNo;		// 選択中の項目のシリアル番号
	private $serialArray = array();			// 表示中のシリアル番号
	private $langId;
	private $configId;		// 定義ID
	private $paramObj;		// パラメータ保存用オブジェクト
	private $displayTypeArray;		// Picasa表示タイプ選択用
	private $displayType;			// Picasa表示タイプ
	const DEFAULT_NAME_HEAD = '名称未設定';			// デフォルトの設定名
	const CONTENT_WIDGET_ID = 's/content';			// コンテンツ編集ウィジェット
	const DEFAULT_DISPLAY_TYPE = 'title_image';		// デフォルトのPicasa表示タイプ
	const DEFAULT_IMAGE_WIDTH = 64;			// 画像幅
	const DEFAULT_IMAGE_HEIGHT = 64;			// 画像幅
	const DEFAULT_IMAGE_STYLE = 'margin:5px;';	// 画像スタイル
	const DEFAULT_ALBUM_COUNT = 10;			// アルバム数
	const DEFAULT_IMAGE_COUNT = 10;			// 画像数
	const DEFAULT_COL_COUNT = 2;			// カラム数
	const MAX_RESULT_COUNT = 300;			// 問い合わせ結果の取得数
	const DEFAULT_GOOGLE_LANG = 'ja';			// Googleの表示言語 en_US
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->sysDb = $this->gInstance->getSytemDbObject();
		
		// Picasa表示タイプ選択用
		$this->displayTypeArray = array(	array(	'name' => 'アルバムタイトル',				'value' => 'title'),
											array(	'name' => 'アルバムタイトルと画像',			'value' => 'title_image'),
											array(	'name' => 'ランダムアルバム',				'value' => 'random_album'),
											array(	'name' => '選択アルバム',					'value' => 'select_album'));
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
		if ($task == 'list'){		// 一覧画面
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
		if ($task == 'list'){		// 一覧画面
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

		// 入力値を取得
		$name		= $request->trimValueOf('item_name');			// 設定タイトル
		$picasaId	= $request->trimValueOf('item_picasa_id');		// PicasaユーザID
		$this->displayType = $request->trimValueOf('item_display_type');			// Picasa表示タイプ
		$imageWidth	= $request->trimValueOf('item_image_width');			// 画像幅
		$imageHeight	= $request->trimValueOf('item_image_height');			// 画像幅
		$imageStyle	= $request->trimValueOf('item_image_style');			// 画像スタイル
		$showTitle	= ($request->trimValueOf('item_show_title') == 'on') ? 1 : 0;			// アルバムタイトルを表示するかどうか
		$albumCount	= $request->trimValueOf('item_album_count');			// アルバム数
		$imageCount	= $request->trimValueOf('item_image_count');			// 画像数
		$colCount	= $request->trimValueOf('item_col_count');			// カラム数
		$this->albumId = $request->trimValueOf('item_select_album');			// アルバムID
		
		$this->configId = $request->trimValueOf('item_id');		// 定義ID
		if (empty($this->configId)) $this->configId = $defConfigId;		// 呼び出しウィンドウから引き継いだ定義ID
		
		$replaceNew = false;		// データを再取得するかどうか
		if (empty($act)){// 初期起動時
			// デフォルト値設定
			$this->configId = $defConfigId;		// 呼び出しウィンドウから引き継いだ定義ID
			$replaceNew = true;			// データ再取得
		} else if ($act == 'add'){// 新規追加
			// 入力チェック
			$this->checkInput($name, '名前');
			$this->checkInput($picasaId, 'PicasaユーザID');
			
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
				$newObj->name		= $name;// 表示名
				$newObj->picasaId	= $picasaId;		// PicasaユーザID
				$newObj->displayType = $this->displayType;			// Picasa表示タイプ
				$newObj->imageWidth		= $imageWidth;			// 画像幅
				$newObj->imageHeight	= $imageHeight;			// 画像幅
				$newObj->imageStyle		= $imageStyle;			// 画像スタイル
				$newObj->showTitle	= $showTitle;			// アルバムタイトルを表示するかどうか
				$newObj->albumCount	= $albumCount;			// アルバム数
				$newObj->imageCount	= $imageCount;			// 画像数
				$newObj->colCount	= $colCount;			// カラム数
				$newObj->albumId	= $this->albumId;			// アルバムID
		
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
			$this->checkInput($picasaId, 'PicasaユーザID');
			
			if ($this->getMsgCount() == 0){			// エラーのないとき
				// 現在の設定値を取得
				$ret = $this->getPageDefParam($defSerial, $defConfigId, $this->paramObj, $this->configId, $targetObj);
				if ($ret){
					// ウィジェットオブジェクト更新
					$targetObj->picasaId	= $picasaId;		// PicasaユーザID
					$targetObj->displayType = $this->displayType;			// Picasa表示タイプ
					$targetObj->imageWidth	= $imageWidth;			// 画像幅
					$targetObj->imageHeight	= $imageHeight;			// 画像幅
					$targetObj->imageStyle	= $imageStyle;			// 画像スタイル
					$targetObj->showTitle	= $showTitle;			// アルバムタイトルを表示するかどうか
					$targetObj->albumCount	= $albumCount;			// アルバム数
					$targetObj->imageCount	= $imageCount;			// 画像数
					$targetObj->colCount	= $colCount;			// カラム数
					$targetObj->albumId	= $this->albumId;			// アルバムID
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
				$picasaId = '';		// PicasaユーザID
				$this->displayType = self::DEFAULT_DISPLAY_TYPE;			// Picasa表示タイプ
				$imageWidth = self::DEFAULT_IMAGE_WIDTH;			// 画像幅
				$imageHeight = self::DEFAULT_IMAGE_HEIGHT;			// 画像幅
				$imageStyle = self::DEFAULT_IMAGE_STYLE;			// 画像スタイル
				$showTitle = 1;			// アルバムタイトルを表示するかどうか
				$albumCount = self::DEFAULT_ALBUM_COUNT;			// アルバム数
				$imageCount = self::DEFAULT_IMAGE_COUNT;			// 画像数
				$colCount = self::DEFAULT_COL_COUNT;			// カラム数
				$this->albumId = '';			// アルバムID
			}
			$this->serialNo = 0;
		} else {
			if ($replaceNew){
				$ret = $this->getPageDefParam($defSerial, $defConfigId, $this->paramObj, $this->configId, $targetObj);
				if ($ret){
					$name = $targetObj->name;// 名前
					$picasaId = $targetObj->picasaId;		// PicasaユーザID
					$this->displayType = $targetObj->displayType;			// Picasa表示タイプ
					$imageWidth = $targetObj->imageWidth;			// 画像幅
					$imageHeight = $targetObj->imageHeight;			// 画像幅
					$imageStyle	= $targetObj->imageStyle;			// 画像スタイル
					$showTitle	= $targetObj->showTitle;			// アルバムタイトルを表示するかどうか
					$albumCount	= $targetObj->albumCount;			// アルバム数
					$imageCount	= $targetObj->imageCount;			// 画像数
					$colCount	= $targetObj->colCount;			// カラム数
					$this->albumId = $targetObj->albumId;			// アルバムID
				}
			}
			$this->serialNo = $this->configId;
				
			// 新規作成でないときは、メニューを変更不可にする(画面作成から呼ばれている場合のみ)
			if (!empty($defConfigId) && !empty($defSerial)) $this->tmpl->addVar("_widget", "id_disabled", 'disabled');
		}
		
		// 設定項目選択メニュー作成
		$this->createItemMenu();
		
		// Picasa表示タイプメニュー作成
		$this->createDisplayTypeMenu();
		
		// アルバム選択メニュー作成
		if ($this->displayType == 'select_album' && !empty($picasaId)){
			$this->createAlbumMenu($picasaId);
		}
		
		// 画面にデータを埋め込む
		$this->tmpl->addVar("item_name_visible", "name", $this->convertToDispString($name));		// 名前
		$this->tmpl->addVar("_widget", "picasa_id",	$this->convertToDispString($picasaId));	// PicasaユーザID
		$this->tmpl->addVar("_widget", "image_width",	$this->convertToDispString($imageWidth));	// 画像幅
		$this->tmpl->addVar("_widget", "image_height",	$this->convertToDispString($imageHeight));	// 画像幅
		$this->tmpl->addVar("_widget", "image_style",	$this->convertToDispString($imageStyle));	// 画像スタイル
		$this->tmpl->addVar("_widget", "album_count",	$this->convertToDispString($albumCount));			// アルバム数
		$this->tmpl->addVar("_widget", "image_count",	$this->convertToDispString($imageCount));			// 画像数
		$this->tmpl->addVar("_widget", "col_count",		$this->convertToDispString($colCount));			// カラム数
		$checked = '';
		if (!empty($showTitle)) $checked = 'checked';
		$this->tmpl->addVar("_widget", "checked_show_title", $checked);		// アルバムタイトルを表示するかどうか
		
		// パスの設定
		$this->tmpl->addVar('_widget', 'admin_url', $this->getUrl($this->gEnv->getDefaultAdminUrl()));// 管理者URL
		$this->tmpl->addVar("_widget", "serial", $this->serialNo);// 選択中のシリアル番号、IDを設定
		$this->tmpl->addVar('_widget', 'content_widget_id', self::CONTENT_WIDGET_ID);// コンテンツ表示ウィジェット
				
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
		
		// 項目がないときは、一覧を表示しない
		if (count($this->serialArray) == 0) $this->tmpl->setAttribute('itemlist', 'visibility', 'hidden');		
		
		$this->tmpl->addVar("_widget", "serial_list", implode($this->serialArray, ','));// 表示項目のシリアル番号を設定
		
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
			$id = $this->paramObj[$i]->id;// 定義ID
			$targetObj = $this->paramObj[$i]->object;
			$name = $targetObj->name;// 定義名
			
			$defCount = 0;
			if (!empty($id)){
				$defCount = $this->sysDb->getPageDefCount($this->gEnv->getCurrentWidgetId(), $id);
			}
			$operationDisagled = '';
			if ($defCount > 0) $operationDisagled = 'disabled';
			$row = array(
				'index' => $i,
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
	 * Picasa表示タイプ選択メニュー作成
	 *
	 * @return なし
	 */
	function createDisplayTypeMenu()
	{
		for ($i = 0; $i < count($this->displayTypeArray); $i++){
			$value = $this->displayTypeArray[$i]['value'];
			$name = $this->displayTypeArray[$i]['name'];
			
			$selected = '';
			if ($value == $this->displayType) $selected = 'selected';
			
			$row = array(
				'value'    => $value,			// ページID
				'name'     => $name,			// ページ名
				'selected' => $selected														// 選択中かどうか
			);
			$this->tmpl->addVars('display_list', $row);
			$this->tmpl->parseTemplate('display_list', 'a');
		}
	}
	/**
	 * アルバム選択メニュー作成
	 *
	 * @param string $picasaId		ユーザID
	 * @return なし
	 */
	function createAlbumMenu($picasaId)
	{
		// RSSを取得
		$commonParam = '&hl=' . self::DEFAULT_GOOGLE_LANG . '&access=public';
		$rssUrl = 'https://picasaweb.google.com/data/feed/api/user/' . $picasaId . '?alt=rss&kind=album' . $commonParam . '&max-results=' . self::MAX_RESULT_COUNT;	// RSS 2.0でアルバムを取得
		
		$xml = simplexml_load_file($rssUrl);
		if ($xml !== false){	// 正常終了のとき
			$albumCount = count($xml->channel->item);		// アルバム数
			
			for ($i = 0; $i < $albumCount; $i++){
				$album = $xml->channel->item[$i];
				$albumTitle = $album->title;
				$albumUrl = str_replace('https://', 'http://', $album->link);		// HTTPに統一
				$photoCount = (int)$album->children('http://schemas.google.com/photos/2007')->numphotos;// 画像数
				$albumDir = basename($albumUrl);		// アルバムディレクトリ名
				$albumPath = parse_url($album->guid);
				$albumId = basename($albumPath['path']);		// アルバムID
				
				$selected = '';
				if ($albumId == $this->albumId) $selected = 'selected';
			
				$row = array(
					'value'    => $this->convertToDispString($albumId),			// アルバムID
					'name'     => $this->convertToDispString($albumTitle),			// アルバムタイトル
					'selected' => $selected														// 選択中かどうか
				);
				$this->tmpl->addVars('album_list', $row);
				$this->tmpl->parseTemplate('album_list', 'a');
			}
		}
	}
}
?>
