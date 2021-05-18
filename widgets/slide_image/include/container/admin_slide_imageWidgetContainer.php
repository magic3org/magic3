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
require_once($gEnvManager->getContainerPath() . '/baseAdminWidgetContainer.php');

class admin_slide_imageWidgetContainer extends BaseAdminWidgetContainer
{
	private $serialNo;		// 選択中の項目のシリアル番号
	private $serialArray = array();			// 表示中のシリアル番号
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
			if (is_array($this->paramObj)){
				for ($i = 0; $i < count($this->paramObj); $i++){
					$targetObj = $this->paramObj[$i]->object;
					if ($name == $targetObj->name){		// 定義名
						$this->setUserErrorMsg('名前が重複しています');
						break;
					}
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
		$this->createConfigNameMenu($this->configId);
				
		// 表示用データを取得
		if (empty($this->configId)){		// 新規登録の場合
			$this->tmpl->setAttribute('item_name_visible', 'visibility', 'visible');// 名前入力フィールド表示
			if ($replaceNew){		// データ再取得時
				$name = $this->createConfigDefaultName();			// デフォルト登録項目名
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
			
			// プレビューボタン使用不可
			$this->tmpl->addVar("_widget", "preview_disabled", 'disabled ');// 「プレビュー」ボタン
		} else {
			$this->tmpl->setAttribute('update_button', 'visibility', 'visible');// 「更新」ボタン
			
			// このウィジェットがマップされているページサブIDを取得
			$subPageId = $this->gPage->getPageSubIdByWidget($this->gEnv->getDefaultPageId(), $this->gEnv->getCurrentWidgetId(), $defConfigId);
			$previewUrl = $this->gEnv->getDefaultUrl();
			if (!empty($subPageId)) $previewUrl .= '?sub=' . $subPageId;
			$this->tmpl->addVar("_widget", "preview_url", $this->getUrl($previewUrl));
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
