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
 * @copyright  Copyright 2006-2014 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseAdminWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/slide_imageDb.php');

class admin_slide_imageWidgetContainer extends BaseAdminWidgetContainer
{
	private $db;	// DB接続オブジェクト
	private $serialNo;		// 選択中の項目のシリアル番号
	private $serialArray = array();			// 表示中のシリアル番号
	private $langId;		// 言語ID
	private $configId;		// 定義ID
	private $paramObj;		// パラメータ保存用オブジェクト
	private $imageInfoArray = array();			// 画像情報
	private $css;			// メニュー用CSS
	private $cssId;			// CSS用ID
	const DEFAULT_NAME_HEAD = '名称未設定';			// デフォルトの設定名
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->db = new slide_imageDb();
		
		// 初期値を取得
		$this->langId	= $this->gEnv->getCurrentLanguage();		// 表示言語を取得
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
		$act = $request->trimValueOf('act');
		$this->serialNo = $request->trimValueOf('serial');		// 選択項目のシリアル番号
		$this->configId = $request->trimValueOf('item_id');		// 定義ID
		if (empty($this->configId)) $this->configId = $defConfigId;		// 呼び出しウィンドウから引き継いだ定義ID
		
		// 入力値を取得
		$name	= $request->trimValueOf('item_name');			// 定義名
		$imageCount = $request->trimValueOf('imagecount');		// 画像情報数
		$urls = $request->trimValueOf('item_url');		// 画像URL
		$titles = $request->trimValueOf('item_title');		// 画像タイトル
		$this->css	= $request->valueOf('item_css');		// メニュー用CSS
		$this->cssId	= $request->trimValueOf('item_css_id');		// CSS用ID
		$showTitle		= $request->trimCheckedValueOf('item_show_title');		// タイトルを表示するかどうか
		$showPager		= $request->trimCheckedValueOf('item_show_pager');			// ページ移動ボタンを表示するかどうか
		$showControl	= $request->trimCheckedValueOf('item_show_control');		// 前後移動ボタンを表示するかどうか
		$auto			= $request->trimCheckedValueOf('item_auto');		// 自動切り替えするかどうか
		 
		$replaceNew = false;		// データを再取得するかどうか
		if ($act == 'add'){// 新規追加
			// 入力値のエラーチェック
			
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
				$newObj->imageInfo	= array();
				$newObj->cssId	= $this->cssId;					// CSS用ID
				$newObj->css	= $this->css;					// メニューCSS
				$newObj->showTitle		= $showTitle;		// タイトルを表示するかどうか
				$newObj->showPager		= $showPager;			// ページ移動ボタンを表示するかどうか
				$newObj->showControl	= $showControl;		// 前後移動ボタンを表示するかどうか
				$newObj->auto			= $auto;		// 自動切り替えするかどうか
				
				for ($i = 0; $i < $imageCount; $i++){
					// パスをマクロ形式に変換
					$url = $urls[$i];
					if (!empty($url)) $url = $this->gEnv->getMacroPath($url);

					$newInfoObj = new stdClass;
					$newInfoObj->name	= $titles[$i];
					$newInfoObj->url	= $url;
					$newObj->imageInfo[] = $newInfoObj;
				}
				
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
			
			if ($this->getMsgCount() == 0){			// エラーのないとき
				// 現在の設定値を取得
				$ret = $this->getPageDefParam($defSerial, $defConfigId, $this->paramObj, $this->configId, $targetObj);
				if ($ret){
					// ウィジェットオブジェクト更新
					$targetObj->imageInfo	= array();
					$targetObj->cssId	= $this->cssId;					// CSS用ID
					$targetObj->css		= $this->css;					// メニューCSS
					$targetObj->showTitle	= $showTitle;		// タイトルを表示するかどうか
					$targetObj->showPager	= $showPager;			// ページ移動ボタンを表示するかどうか
					$targetObj->showControl	= $showControl;		// 前後移動ボタンを表示するかどうか
					$targetObj->auto		= $auto;		// 自動切り替えするかどうか
					
					for ($i = 0; $i < $imageCount; $i++){
						// パスをマクロ形式に変換
						$url = $urls[$i];
						if (!empty($url)) $url = $this->gEnv->getMacroPath($url);

						$newInfoObj = new stdClass;
						$newInfoObj->name	= $titles[$i];
						$newInfoObj->url	= $url;
						$targetObj->imageInfo[] = $newInfoObj;
					}
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
				$this->imageInfoArray = array();			// 画像情報
				$this->cssId = $this->createDefaultCssId();	// CSS用ID
				$this->css = $this->getParsedTemplateData('default.tmpl.css', array($this, 'makeCss'));// デフォルト用のCSSを取得
				$showTitle = '0';		// タイトルを表示するかどうか
				$showPager		= '0';		// ページ移動ボタンを表示するかどうか
				$showControl	= '1';		// 前後移動ボタンを表示するかどうか
				$auto			= '0';		// 自動切り替えするかどうか
			}
			$this->serialNo = 0;
		} else {
			if ($replaceNew){// データ再取得時
				$ret = $this->getPageDefParam($defSerial, $defConfigId, $this->paramObj, $this->configId, $targetObj);
				if ($ret){
					$name	= $targetObj->name;// 名前
					if (!empty($targetObj->imageInfo)) $this->imageInfoArray = $targetObj->imageInfo;			// 画像情報
					$this->cssId	= $targetObj->cssId;					// CSS用ID
					$this->css		= $targetObj->css;					// メニューCSS
					$showTitle 		= $targetObj->showTitle;		// タイトルを表示するかどうか
					$showPager		= $targetObj->showPager;			// ページ移動ボタンを表示するかどうか
					$showControl	= $targetObj->showControl;		// 前後移動ボタンを表示するかどうか
					$auto			= $targetObj->auto;		// 自動切り替えするかどうか
				}
			}
			$this->serialNo = $this->configId;
				
			// 新規作成でないときは、メニューを変更不可にする(画面作成から呼ばれている場合のみ)
			if (!empty($defConfigId) && !empty($defSerial)) $this->tmpl->addVar("_widget", "id_disabled", 'disabled');
		}
		
		// 画像情報一覧作成
		$this->createImageList();
		if (empty($this->imageInfoArray)) $this->tmpl->setAttribute('image_list', 'visibility', 'hidden');// 画像情報一覧
		
		// 画面にデータを埋め込む
		if (!empty($this->configId)) $this->tmpl->addVar("_widget", "id", $this->configId);		// 定義ID
		$this->tmpl->addVar("item_name_visible", "name",	$this->convertToDispString($name));
		$this->tmpl->addVar("_widget", "css_id",	$this->cssId);	// CSS用ID
		$this->tmpl->addVar("_widget", "css",	$this->css);
		$this->tmpl->addVar("_widget", "show_title_checked",	$this->convertToCheckedString($showTitle));		// タイトルを表示するかどうか
		$this->tmpl->addVar("_widget", "show_pager_checked",	$this->convertToCheckedString($showPager));			// ページ移動ボタンを表示するかどうか
		$this->tmpl->addVar("_widget", "show_control_checked",	$this->convertToCheckedString($showControl));		// 前後移動ボタンを表示するかどうか
		$this->tmpl->addVar("_widget", "auto_checked",	$this->convertToCheckedString($auto));		// 自動切り替えするかどうか
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
	 * 画像情報一覧を作成
	 *
	 * @return なし						
	 */
	function createImageList()
	{
		$imageCount = count($this->imageInfoArray);
		for ($i = 0; $i < $imageCount; $i++){
			$infoObj = $this->imageInfoArray[$i];
			$name = $infoObj->name;// タイトル名
			
			// ファイル名取得
			$filename = '';
			$partArray = explode('/', $infoObj->url);
			if (count($partArray) > 0) $filename = $partArray[count($partArray)-1];
			
			// 画像URL
			$url = $infoObj->url;
			if (!empty($url)) $url = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $this->gEnv->getRootUrl(), $url);
			
			$rootUrl = $this->getUrl($this->gEnv->getRootUrl());
			$row = array(
				'filename' => $this->convertToDispString($filename),	// ファイル名
				'url' => $this->convertToDispString($this->getUrl($url)),				// URL
				'title' => $this->convertToDispString($name),			// 名前
				'root_url' => $this->convertToDispString($rootUrl)
			);
			$this->tmpl->addVars('image_list', $row);
			$this->tmpl->parseTemplate('image_list', 'a');
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
			$width		= $targetObj->width;		// 画像の幅
			$height		= $targetObj->height;		// 画像の高さ
			$widthType	= $targetObj->widthType;		// 画像の幅単位
			$heightType	= $targetObj->heightType;		// 画像の高さ単位
		
			// ファイル名取得
			$filename = '';
			$partArray = explode('/', $targetObj->imageUrl);
			if (count($partArray) > 0) $filename = $partArray[count($partArray)-1];
		
			// 使用数
			$defCount = 0;
			if (!empty($id)){
				$defCount = $this->_db->getPageDefCount($this->gEnv->getCurrentWidgetId(), $id);
			}
			$operationDisagled = '';
			if ($defCount > 0) $operationDisagled = 'disabled';
			
			// 画像サイズ
			$imgWidth = '';
			$imgHeight = '';
			if (!empty($width) && $width > 0){
				$imgWidth = $width;
				if (!empty($widthType)) $imgWidth .= '%';
			}
			if (!empty($height) && $height > 0){
				$imgHeight = $height;
				if (!empty($heightType)) $imgHeight .= '%';
			}
			
			// 画像URL
			$url = $targetObj->imageUrl;
			if (!empty($url)) $url = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $this->gEnv->getRootUrl(), $url);
			
			$row = array(
				'index' => $i,
				'id' => $id,
				'ope_disabled' => $operationDisagled,			// 選択可能かどうか
				'name' => $this->convertToDispString($name),		// 名前
				'filename' => $this->convertToDispString($filename),	// ファイル名
				'def_count' => $defCount,							// 使用数
				'width' => $imgWidth,					// 画像幅
				'height' => $imgHeight,					// 画像高さ
				'url' => $this->getUrl($url)					// URL
			);
			$this->tmpl->addVars('itemlist', $row);
			$this->tmpl->parseTemplate('itemlist', 'a');
			
			// シリアル番号を保存
			$this->serialArray[] = $id;
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
		$tmpl->addVar("_tmpl", "css_id",	'#' . $this->cssId);		// メニュー用CSS
	}
}
?>
