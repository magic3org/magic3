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
 * @copyright  Copyright 2006-2012 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: admin_photoslide2WidgetContainer.php 4699 2012-02-19 14:14:58Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseAdminWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath()	. '/photoslide2Db.php');

class admin_photoslide2WidgetContainer extends BaseAdminWidgetContainer
{
	private $db;	// DB接続オブジェクト
	private $serialNo;		// 選択中の項目のシリアル番号
	private $serialArray = array();			// 表示中のシリアル番号
	private $langId;
	private $configId;			// 定義ID
	private $paramObj;			// パラメータ保存用オブジェクト
	private $dispType;			// 画像表示方法
	private $dispTypeDef;		// 画像表示方法定義
	private $sortKeyTypeArray;		// ソートキータイプ
	private $sortKey;				// ソートキー
	private $effectItems = array('blindX', 'blindY', 'blindZ', 'cover', 'curtainX', 'curtainY', 'fade', 'fadeZoom', 'growX', 'growY', 'scrollUp', 'scrollDown',
								'scrollLeft', 'scrollRight', 'scrollHorz', 'scrollVert', 'shuffle', 'slideX', 'slideY', 'toss', 'turnUp', 'turnDown',
								'turnLeft', 'turnRight', 'uncover', 'wipe', 'zoom');
	private $speedItems = array('slow', 'normal', 'fast');
	private $effect;		// 選択中のエフェクト
	private $speed;			// エフェクトのspeedパラメータ
	private $cssId;			// CSS用ID
	private $css;			// 追加CSS
	const DEFAULT_NAME_HEAD = '名称未設定';			// デフォルトの設定名
	const DEFAULT_DIR = '/resource/image/sample/photo';			// デフォルト読み込みディレクトリ
//	const DEFAULT_CSS = ".photoslide {\n    height:  160px;\n    width:   180px;\n    padding: 0;\n    margin:  0;\n}\n.photoslide img {\n    padding: 10px;\n    border:  1px solid #ccc;\n    background-color: #eee;\n    width:  150px;\n    height: 113px;\n    top:  0;\n    left: 0;\n}";
	const DEFAULT_EFFECT = 'fade';		// デフォルトのエフェクト
	const DEFAULT_IMAGE_TYPE 	= 'directory';		// デフォルトの画像タイプ
	const DEFAULT_IMAGE_COUNT	= 10;		// デフォルトの画像取得数
	const DEFAULT_SORT_ORDER	= '0';		// デフォルトの画像取得順(降順)
	const DEFAULT_SORT_KEY		= 'index';	// デフォルトのソートキー
	const PHOTO_IMAGE_DIR		= '/widgets/photo/image';		// フォトギャラリー公開画像ディレクトリ
	const DEFAULT_PHOTO_IMAGE_EXT	= 'jpg';	// フォトギャラリー公開画像ファイル拡張子
	const CF_PHOTO_CATEGORY_PASSWORD	= 'photo_category_password';		// 画像カテゴリーのパスワード制限
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->db = new photoslide2Db();
		
		// 表示順
		$this->dispTypeDef = array(	array(	'name' => '順次',		'value' => '0'),
									array(	'name' => 'ランダム',	'value' => '1'));
		// ソートキー選択用
		$this->sortKeyTypeArray = array(	array(	'name' => '画像表示順',		'value' => 'index'),
											array(	'name' => '日付',			'value' => 'date'),
											array(	'name' => '評価',			'value' => 'rate'),
											array(	'name' => '参照数',			'value' => 'ref'));
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
		$langId	= $this->gEnv->getCurrentLanguage();		// 表示言語を取得
		$act = $request->trimValueOf('act');
		$this->serialNo = $request->trimValueOf('serial');		// 選択項目のシリアル番号
		$this->configId = $request->trimValueOf('item_id');		// 定義ID
		if (empty($this->configId)) $this->configId = $defConfigId;		// 呼び出しウィンドウから引き継いだ定義ID
		
		$name	= $request->trimValueOf('item_name');			// ヘッダタイトル
		$imageType = $request->trimValueOf('item_image_type');		// 表示画像タイプ
		$dir	= $request->trimValueOf('item_dir');		// 画像読み込みディレクトリ
		$this->cssId	= $request->trimValueOf('item_css_id');		// CSS用ID
		$this->css	= $request->trimValueOf('item_css');		// 追加CSS
		$this->dispType = $request->trimValueOf('item_disptype');	// 表示方法
		$this->effect	= $request->trimValueOf('item_effect');		// エフェクト
		$this->speed	= $request->trimValueOf('item_speed');
		$imageCount = $request->trimValueOf('item_image_count');		// 画像一覧表示数
		$sortOrder = $request->trimValueOf('item_sort_order');		// 画像一覧表示順
		$this->sortKey = $request->trimValueOf('item_sort_key');				// ソートキー
		
		$replaceNew = false;		// データを再取得するかどうか
		if ($act == 'add'){// 新規追加
			// 入力チェック
			$this->checkInput($name, '名前');
			$this->checkInput($imageType, '表示画像タイプ');
			$this->checkInput($dir, '画像読み込みディレクトリ');
			$this->checkInput($this->effect, 'エフェクト');
			$this->checkNumeric($imageCount, '画像取得数');
			
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
				$newObj->imageType = $imageType;		// 表示画像タイプ
				$newObj->dir = $dir;		// 画像読み込みディレクトリ
				$newObj->cssId	= $this->cssId;					// CSS用ID
				$newObj->css = $this->css;		// 追加CSS
				$newObj->dispType	= $this->dispType;	// 表示方法
				$newObj->effect		= $this->effect;				// エフェクト
				$newObj->speed		= $this->speed;
				$newObj->imageCount	= $imageCount;	// 画像取得数
				$newObj->sortOrder	= $sortOrder;		// 画像並び順
				$newObj->sortKey	= $this->sortKey;		// 画像ソートキー
					
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
			$this->checkInput($imageType, '表示画像タイプ');
			$this->checkInput($dir, '画像読み込みディレクトリ');
			$this->checkInput($this->effect, 'エフェクト');
			$this->checkNumeric($imageCount, '画像取得数');
			
			if ($this->getMsgCount() == 0){			// エラーのないとき
				// 現在の設定値を取得
				$ret = $this->getPageDefParam($defSerial, $defConfigId, $this->paramObj, $this->configId, $targetObj);
				if ($ret){
					// ウィジェットオブジェクト更新
					$targetObj->imageType = $imageType;		// 表示画像タイプ
					$targetObj->dir	= $dir;		// 画像読み込みディレクトリ
					$targetObj->cssId	= $this->cssId;					// CSS用ID
					$targetObj->css = $this->css;		// 追加CSS
					$targetObj->dispType = $this->dispType;	// 表示方法
					$targetObj->effect		= $this->effect;				// エフェクト
					$targetObj->speed		= $this->speed;
					$targetObj->imageCount	= $imageCount;	// 画像取得数
					$targetObj->sortOrder	= $sortOrder;		// 画像並び順
					$targetObj->sortKey		= $this->sortKey;		// 画像ソートキー
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
		} else {	// 初期起動時、または上記以外の場合
			// デフォルト値設定
			$this->configId = $defConfigId;		// 呼び出しウィンドウから引き継いだ定義ID
			$replaceNew = true;			// データ再取得
		}
		
		// 設定項目選択メニュー作成
		$this->createItemMenu();
		
		// 表示用データを取得
		if (empty($this->configId)){		// 新規登録の場合
			$this->tmpl->setAttribute('item_name_visible', 'visibility', 'visible');// 名前入力フィールド表示
			if ($replaceNew){		// データ再取得時
				$name = $this->createDefaultName();			// デフォルト登録項目名
				$imageType = self::DEFAULT_IMAGE_TYPE;		// 表示画像タイプ
				$dir = self::DEFAULT_DIR;		// 画像読み込みディレクトリ
				$this->cssId = $this->createDefaultCssId();	// CSS用ID
				//$this->css = self::DEFAULT_CSS;		// 追加CSS
				$this->css = $this->getParsedTemplateData('default.tmpl.css', array($this, 'makeCss'));
				$this->dispType = 0;	// 表示方法
				$this->effect	= self::DEFAULT_EFFECT;				// エフェクト
				$this->speed	= '';
				$imageCount		= self::DEFAULT_IMAGE_COUNT;	// 画像取得数
				$sortOrder		= self::DEFAULT_SORT_ORDER;		// 画像並び順
				$this->sortKey	= self::DEFAULT_SORT_KEY;		// 画像ソートキー
			}
			$this->serialNo = 0;
		} else {
			if ($replaceNew){// データ再取得時
				$ret = $this->getPageDefParam($defSerial, $defConfigId, $this->paramObj, $this->configId, $targetObj);
				if ($ret){
					$name	= $targetObj->name;// 名前
					$imageType = $targetObj->imageType;		// 表示画像タイプ
					$dir			= $targetObj->dir;		// 画像読み込みディレクトリ
					$this->cssId	= $targetObj->cssId;					// CSS用ID
					$this->css		= $targetObj->css;		// 追加CSS
					$this->dispType = $targetObj->dispType;	// 表示方法
					$this->effect	= $targetObj->effect;			// エフェクト
					$this->speed	= $targetObj->speed;
					$imageCount		= $targetObj->imageCount;	// 画像取得数
					$sortOrder		= $targetObj->sortOrder;		// 画像並び順
					$this->sortKey	= $targetObj->sortKey;		// 画像ソートキー
				}
			}
			$this->serialNo = $this->configId;
			
			// 新規作成でないときは、メニューを変更不可にする
			if (!empty($defConfigId) && !empty($defSerial)) $this->tmpl->addVar("_widget", "id_disabled", 'disabled');
		}
		// 表示順選択メニュー作成
		$this->createDispTypeMenu();
		
		// ソートキー選択メニュー作成
		$this->createSortKeyMenu();
		
		// エフェクトメニュー作成
		$this->createEffectMenu();
		
		// プレビュースライドショー用画像一覧作成
		switch ($imageType){
			case 'directory':
				$this->createImageList($dir);
				break;
			case 'photo':
				if (!$this->db->getConfig(self::CF_PHOTO_CATEGORY_PASSWORD)){			// カテゴリーパスワード制限がかかっているときは画像の表示不可
					$this->db->getPhotoItems($imageCount, $langId, $this->sortKey, $sortOrder, array($this, 'itemLoop'));
				}
				break;
		}
		
		// エフェクト設定を作成
		$effectStr = $this->createEffect($this->effect, $this->speed);
		$this->tmpl->addVar('_widget', 'effect', $effectStr);
		
		// 画面にデータを埋め込む
		if (!empty($this->configId)) $this->tmpl->addVar("_widget", "id", $this->configId);		// 定義ID
		$this->tmpl->addVar("item_name_visible", "name", $name);		// 名前
		if ($imageType == self::DEFAULT_IMAGE_TYPE){	// 画像タイプ
			$this->tmpl->addVar("_widget", "image_type_directory_checked", 'checked');// ディレクトリ画像
		} else {
			$this->tmpl->addVar("_widget", "image_type_photo_checked", 'checked');// フォトギャラリー画像
		}
		$this->tmpl->addVar("_widget", "dir",	$dir);
		$this->tmpl->addVar("_widget", "css_id",	$this->cssId);	// CSS用ID
		$this->tmpl->addVar("_widget", "css",	$this->css);
		$this->tmpl->addVar("_widget", "image_count", $imageCount);// 画像取得数
		if (empty($sortOrder)){	// 順方向
			$this->tmpl->addVar("_widget", "order_dec_selected", 'selected');// 降順
		} else {
			$this->tmpl->addVar("_widget", "order_inc_selected", 'selected');// 昇順
		}
		
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
		
		$this->tmpl->addVar("_widget", "serial_list", implode($this->serialArray, ','));// 表示項目のシリアル番号を設定
		
		// ページ定義IDとページ定義のレコードシリアル番号を更新
		$this->endPageDefParam($defSerial, $defConfigId, $this->paramObj);
	}
	/**
	 * CSSデータをHTMLヘッダ部に設定
	 *
	 * CSSデータをHTMLのheadタグ内に追加出力する。
	 * _assign()よりも後に実行される。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。
	 * @return string 						CSS文字列。出力しない場合は空文字列を設定。
	 */
	function _addCssToHead($request, &$param)
	{
		return $this->css;
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
	 * エフェクト選択用メニューを作成
	 *
	 * @return なし						
	 */
	function createEffectMenu()
	{
		for ($i = 0; $i < count($this->effectItems); $i++){
			$name = $this->effectItems[$i];
			$selected = '';
			if ($this->effect == $name) $selected = 'selected';

			$row = array(
				'name' => $name,		// 名前
				'value' => $name,		// 定義ID
				'selected' => $selected	// 選択中の項目かどうか
			);
			$this->tmpl->addVars('effect_list', $row);
			$this->tmpl->parseTemplate('effect_list', 'a');
		}
		for ($i = 0; $i < count($this->speedItems); $i++){
			$name = $this->speedItems[$i];
			$selected = '';
			if ($this->speed == $name) $selected = 'selected';

			$row = array(
				'name' => $name,		// 名前
				'value' => $name,		// 定義ID
				'selected' => $selected	// 選択中の項目かどうか
			);
			$this->tmpl->addVars('speed_list', $row);
			$this->tmpl->parseTemplate('speed_list', 'a');
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
	 * スライドショー用画像一覧を作成
	 *
	 * @param string $dir		画像のあるディレクトリ
	 * @return なし							
	 */
	function createImageList($dir)
	{
		// 画像ディレクトリを読み込み
		$searchPath	= $this->gEnv->getSystemRootPath() . $dir;		// 画像検索パス
		$urlPath	= $this->gEnv->getRootUrl() . $dir;
		
		// ファイル一覧取得
		$files = $this->getFiles($searchPath);
		
		// 表示方法によって並べ替え
		switch ($this->dispType){
			case 0:
				sort($files);
				break;
			case 1:
				shuffle($files);
				break;
		}
		
		for ($i = 0; $i < count($files); $i++){
			$imageUrl = $urlPath . '/' . $files[$i];
			$row = array(
				'url'    => $this->getUrl($imageUrl)			// ファイル名
			);
			$this->tmpl->addVars('image_list', $row);
			$this->tmpl->parseTemplate('image_list', 'a');
			
			// 画像一覧用
			$this->tmpl->addVars('image_list2', $row);
			$this->tmpl->parseTemplate('image_list2', 'a');
		}
	}
	/**
	 * 取得したデータをテンプレートに設定する
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function itemLoop($index, $fetchedRow, $param)
	{
		$photoId = $fetchedRow['ht_public_id'];		// フォトID
		$title = $fetchedRow['ht_name'];		// サムネール画像タイトル
		
		// 画像詳細へのリンク
		$url = $this->gEnv->getDefaultUrl() . '?' . M3_REQUEST_PARAM_PHOTO_ID . '=' . $photoId;
		$urlLink = $this->convertUrlToHtmlEntity($this->getUrl($url, true));

		// 画像URL
		$imageUrl = $this->getPhotoImageUrl($photoId);
						
		$row = array(
			'url'    => $this->getUrl($imageUrl)			// 画像URL
		);
		$this->tmpl->addVars('image_list', $row);
		$this->tmpl->parseTemplate('image_list', 'a');
		
		// 画像一覧用
		$this->tmpl->addVars('image_list2', $row);
		$this->tmpl->parseTemplate('image_list2', 'a');
		return true;
	}
	/**
	 * 指定ディレクトリのファイル一覧を取得
	 *
	 * @param string $path		読み込みディレクトリ
	 * @return array			ファイル名一覧
	 */
	function getFiles($path)
	{
		$filenames = array();
		if (is_dir($path)){
			$dir = dir($path);
			while (($file = $dir->read()) !== false){
				$filePath = $path . '/' . $file;
				// ファイルかどうかチェック
				if (strncmp($file, '.', 1) != 0 && $file != '..' && is_file($filePath)
					&& strncmp($file, '_', 1) != 0){		// 「_」で始まる名前のファイルは読み込まない
					$filenames[] = $file;
				}
			}
			$dir->close();
		}
		return $filenames;
	}
	/**
	 * フォトギャラリー画像のURLを取得
	 *
	 * @param string $photoId		画像ID
	 * @return string				画像URL
	 */
	function getPhotoImageUrl($photoId)
	{
		return $this->gEnv->getResourceUrl() . self::PHOTO_IMAGE_DIR . '/' . $photoId . '.' . self::DEFAULT_PHOTO_IMAGE_EXT;
	}
	/**
	 * エフェクトの設定を作成
	 *
	 * @param string $effect		エフェクト
	 * @param string $speed			speedパラメータ
	 * @return string				エフェクト文字列
	 */
	function createEffect($effect, $speed)
	{
		$effectStr = '';
		if (!empty($effect)) $effectStr .= 'fx: \'' . $effect . '\'';
		if (!empty($speed)){
			if (!empty($effectStr)) $effectStr .= ',';
			$effectStr .= 'speed: \'' . $speed . '\'';
		}
		return $effectStr;
	}
	/**
	 * 定義一覧作成
	 *
	 * @return なし						
	 */
	function createItemList()
	{
		for ($i = 0; $i < count($this->paramObj); $i++){
			$id			= $this->paramObj[$i]->id;// 定義ID
			$targetObj	= $this->paramObj[$i]->object;
			$name = $targetObj->name;// 定義名
			
			// 読み込みディレクトリ
			$filename = rtrim($targetObj->dir, '/');
			
			// 使用数
			$defCount = 0;
			if (!empty($id)){
				$defCount = $this->_db->getPageDefCount($this->gEnv->getCurrentWidgetId(), $id);
			}
			$operationDisagled = '';
			if ($defCount > 0) $operationDisagled = 'disabled';
			$row = array(
				'index' => $i,
				'ope_disabled' => $operationDisagled,			// 選択可能かどうか
				'name' => $this->convertToDispString($name),		// 名前
				'filename' => $filename,		// ファイル名
				'url' => $this->getUrl($url),					// URL
				'width' => $targetObj->width,					// Flashファイル幅
				'height' => $targetObj->height,					// Flashファイル高さ
				'def_count' => $defCount							// 使用数
			);
			$this->tmpl->addVars('itemlist', $row);
			$this->tmpl->parseTemplate('itemlist', 'a');
			
			// シリアル番号を保存
			$this->serialArray[] = $id;
		}
	}
	/**
	 * 表示順選択メニュー作成
	 *
	 * @return なし
	 */
	function createDispTypeMenu()
	{
		for ($i = 0; $i < count($this->dispTypeDef); $i++){
			$value = $this->dispTypeDef[$i]['value'];
			$name = $this->dispTypeDef[$i]['name'];
			
			$selected = '';
			if ($value == $this->dispType) $selected = 'selected';
			
			$row = array(
				'value'    => $value,			// 値
				'name'     => $name,			// 名前
				'selected' => $selected														// 選択中かどうか
			);
			$this->tmpl->addVars('disp_type_list', $row);
			$this->tmpl->parseTemplate('disp_type_list', 'a');
		}
	}
	/**
	 * ソートキー選択メニュー作成
	 *
	 * @return なし
	 */
	function createSortKeyMenu()
	{
		for ($i = 0; $i < count($this->sortKeyTypeArray); $i++){
			$value = $this->sortKeyTypeArray[$i]['value'];
			$name = $this->sortKeyTypeArray[$i]['name'];
			
			$selected = '';
			if ($value == $this->sortKey) $selected = 'selected';
			
			$row = array(
				'value'    => $value,			// ソートキーID
				'name'     => $name,			// 名前
				'selected' => $selected			// 選択中かどうか
			);
			$this->tmpl->addVars('item_sort_key_type_list', $row);
			$this->tmpl->parseTemplate('item_sort_key_type_list', 'a');
		}
	}
	/**
	 * CSS用のデフォルトのIDを取得
	 *
	 * @return string	ID						
	 */
	function createDefaultCssId()
	{
		return $this->gEnv->getCurrentWidgetId() . '_' . $this->getTempConfigId($this->paramObj);
	}
	/**
	 * CSSデータ作成処理コールバック
	 *
	 * @param object         $tmpl			テンプレートオブジェクト
	 * @param								なし
	 */
	function makeCss($tmpl)
	{
		$tmpl->addVar('_tmpl', 'id', '#' . $this->cssId);
	}
}
?>
