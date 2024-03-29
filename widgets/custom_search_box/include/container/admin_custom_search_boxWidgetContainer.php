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
 * @copyright  Copyright 2006-2021 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseAdminWidgetContainer.php');

class admin_custom_search_boxWidgetContainer extends BaseAdminWidgetContainer
{
	private $serialNo;		// 選択中の項目のシリアル番号
	private $serialArray = array();			// 表示中のシリアル番号
	private $configId;		// 定義ID
	private $paramObj;		// パラメータ保存用オブジェクト
	private $searchTextId;		// 検索用テキストフィールドのタグID
	private $searchButtonId;		// 検索用ボタンのタグID
	private $searchResetId;		// 検索エリアリセットボタンのタグID
	private $targetRenderType;		// 実際に表示する画面の描画出力タイプ
	const DEFAULT_NAME_HEAD = '名称未設定';			// デフォルトの設定名
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// 実際に表示する画面の描画出力タイプを取得
		$templateType = $this->_getTemplateType($this->gSystem->defaultTemplateId());// デフォルトのテンプレートIDからテンプレートタイプを取得
		$this->targetRenderType = $this->_getRenderType($templateType);
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

		// 入力値を取得
		$name	= $request->trimValueOf('item_name');			// ヘッダタイトル
		$searchTemplate = $request->valueOf('item_html');		// 検索用テンプレート
		$this->searchTextId = $request->trimValueOf('item_search_text');		// 検索用テキストフィールドのタグID
		$this->searchButtonId = $request->trimValueOf('item_search_button');		// 検索用ボタンのタグID
		$this->searchResetId = $request->trimValueOf('item_search_reset');		// 検索エリアリセットボタンのタグID
		$this->configId = $request->trimValueOf('item_id');		// 定義ID
		if (empty($this->configId)) $this->configId = $defConfigId;		// 呼び出しウィンドウから引き継いだ定義ID
		
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
				// 空の場合デフォルト値を設定
				if (empty($searchTemplate)){
					if ($this->targetRenderType == M3_RENDER_BOOTSTRAP){
						$searchTemplate = $this->getParsedTemplateData('default_bootstrap.tmpl.html', array($this, 'makeSearcheTemplate'));// デフォルト用の検索テンプレート
					} else {
						$searchTemplate = $this->getParsedTemplateData('default.tmpl.html', array($this, 'makeSearcheTemplate'));// デフォルト用の検索テンプレート
					}
				}
				
				// 追加オブジェクト作成
				$newObj = new stdClass;
				$newObj->name	= $name;// 表示名
				$newObj->searchTemplate = $searchTemplate;		// 検索用テンプレート
				$newObj->searchTextId = $this->searchTextId;		// 検索用テキストフィールドのタグID
				$newObj->searchButtonId = $this->searchButtonId;		// 検索用ボタンのタグID
				$newObj->searchResetId = $this->searchResetId;		// 検索エリアリセットボタンのタグID
				
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
				// 空の場合デフォルト値を設定
				if (empty($searchTemplate)){
					if ($this->targetRenderType == M3_RENDER_BOOTSTRAP){
						$searchTemplate = $this->getParsedTemplateData('default_bootstrap.tmpl.html', array($this, 'makeSearcheTemplate'));// デフォルト用の検索テンプレート
					} else {
						$searchTemplate = $this->getParsedTemplateData('default.tmpl.html', array($this, 'makeSearcheTemplate'));// デフォルト用の検索テンプレート
					}
				}
				
				// 現在の設定値を取得
				$ret = $this->getPageDefParam($defSerial, $defConfigId, $this->paramObj, $this->configId, $targetObj);
				if ($ret){
					// ウィジェットオブジェクト更新
					$targetObj->searchTemplate	= $searchTemplate;		// 検索用テンプレート
					$targetObj->searchTextId = $this->searchTextId;		// 検索用テキストフィールドのタグID
					$targetObj->searchButtonId = $this->searchButtonId;		// 検索用ボタンのタグID
					$targetObj->searchResetId = $this->searchResetId;		// 検索エリアリセットボタンのタグID
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
		
		// 設定項目選択メニュー作成
		$this->createItemMenu();
		
		// 表示用データを取得
		if (empty($this->configId)){		// 新規登録の場合
			$this->tmpl->setAttribute('item_name_visible', 'visibility', 'visible');// 名前入力フィールド表示
			if ($replaceNew){		// データ再取得時
				//$name = $this->createDefaultName();			// デフォルト登録項目名
				$name = $this->createConfigDefaultName();			// デフォルト登録項目名
				
				// デフォルトの検索テンプレート作成
				$tagHead = $this->createTagIdHead();
				$this->searchTextId = $tagHead . '_text';		// 検索用テキストフィールドのタグID
				$this->searchButtonId = $tagHead . '_button';		// 検索用ボタンのタグID
				$this->searchResetId = $tagHead . '_reset';		// 検索エリアリセットボタンのタグID
//				$searchTemplate = $this->getParsedTemplateData('default.tmpl.html', array($this, 'makeSearcheTemplate'));// デフォルト用の検索テンプレート
				if ($this->targetRenderType == M3_RENDER_BOOTSTRAP){
					$searchTemplate = $this->getParsedTemplateData('default_bootstrap.tmpl.html', array($this, 'makeSearcheTemplate'));// デフォルト用の検索テンプレート
				} else {
					$searchTemplate = $this->getParsedTemplateData('default.tmpl.html', array($this, 'makeSearcheTemplate'));// デフォルト用の検索テンプレート
				}
			}
			$this->serialNo = 0;
		} else {		// 更新の場合
			if ($replaceNew){
				$ret = $this->getPageDefParam($defSerial, $defConfigId, $this->paramObj, $this->configId, $targetObj);
				if ($ret){
					$name = $targetObj->name;// 名前
					$this->searchTextId = $targetObj->searchTextId;		// 検索用テキストフィールドのタグID
					$this->searchButtonId = $targetObj->searchButtonId;		// 検索用ボタンのタグID
					$this->searchResetId = $targetObj->searchResetId;		// 検索エリアリセットボタンのタグID
					$searchTemplate = $targetObj->searchTemplate;		// 検索用テンプレート
				}
			}
			$this->serialNo = $this->configId;
				
			// 新規作成でないときは、メニューを変更不可にする(画面作成から呼ばれている場合のみ)
			if (!empty($defConfigId) && !empty($defSerial)) $this->tmpl->addVar("_widget", "id_disabled", 'disabled');
		}
		
		// 画面にデータを埋め込む
		$this->tmpl->addVar("item_name_visible", "name", $name);		// 名前
		$this->tmpl->addVar("_widget", "html",	$searchTemplate);
		$this->tmpl->addVar("_widget", "search_text",	$this->searchTextId);		// 検索用テキストフィールドのタグID
		$this->tmpl->addVar("_widget", "search_button",	$this->searchButtonId);		// 検索用ボタンのタグID
		$this->tmpl->addVar("_widget", "search_reset",	$this->searchResetId);		// 検索エリアリセットボタンのタグID
		$tagStr = $this->searchTextId . '(入力フィールドのID), ' . $this->searchButtonId . '(検索実行ボタンのID), ' . $this->searchResetId . '(検索リセットボタンのID)';
		$this->tmpl->addVar("_widget", "tag_id_str", $tagStr);// タグIDの表示
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
}
?>
