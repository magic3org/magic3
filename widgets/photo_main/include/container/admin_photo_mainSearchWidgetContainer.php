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
 * @version    SVN: $Id: admin_photo_mainSearchWidgetContainer.php 4586 2012-01-13 01:32:22Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() . '/admin_photo_mainBaseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() .	'/photo_categoryDb.php');

class admin_photo_mainSearchWidgetContainer extends admin_photo_mainBaseWidgetContainer
{
	private $categoryDb;	// DB接続オブジェクト
	private $langId;
	private $fieldInfoArray = array();			// 項目定義
	private $itemTypeArray;		// 項目タイプメニュー
	private $categoryArray;		// カテゴリ種別メニュー
	private $selTypeArray;	// 項目選択タイプメニュー
	const MESSAGE_NO_USER_CATEGORY = 'カテゴリが登録されていません';			// ユーザ作成コンテンツ用のカテゴリが登録されていないときのメッセージ
//	const DEFAULT_SEARCH_AREA = 'default_search.tmpl.html';		// デフォルトの検索エリア
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->categoryDb = new photo_categoryDb();
		
		// 項目タイプ
		$this->itemTypeArray = array(	array(	'name' => '絞り込み-カテゴリー',	'value' => 'category'),
										array(	'name' => '絞り込み-撮影者',		'value' => 'author'));
		// 項目選択タイプ
		$this->selTypeArray = array(	array(	'name' => '単一選択',	'value' => 'single'),
										array(	'name' => '複数選択',	'value' => 'multi'));
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
		return 'admin_search.tmpl.html';
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
	 * 詳細画面作成
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param								なし
	 */
	function createDetail($request)
	{
		$this->langId	= $this->gEnv->getCurrentLanguage();		// 表示言語を取得
		$act = $request->trimValueOf('act');

		// 入力値を取得
		$searchTemplate = $request->valueOf('item_html');		// 検索用テンプレート
		
		// カテゴリ項目定義
		$fieldCount = intval($request->trimValueOf('fieldcount'));		// 検索定義項目数
		$itemTypes = $request->trimValueOf('item_type');			// 項目種別
		$selectTypes = $request->trimValueOf('item_sel_type');			// 項目選択方法
		$categoryes = $request->trimValueOf('item_category');			// カテゴリー

		// カテゴリ設定を取得
		$this->fieldInfoArray = array();
		for ($i = 0; $i < $fieldCount; $i++){
			$newInfoObj = new stdClass;
			$newInfoObj->itemType = $itemTypes[$i];
			$newInfoObj->selectType = $selectTypes[$i];
			if ($newInfoObj->itemType == 'category') $newInfoObj->category = $categoryes[$i];		// カテゴリーを取得
			$this->fieldInfoArray[] = $newInfoObj;
		}
		
		// Pタグを除去
		$searchTemplate = $this->gInstance->getTextConvManager()->deleteTag($searchTemplate, 'p');

		$replaceNew = false;		// データを再取得するかどうか
		if ($act == 'update'){		// 設定更新のとき
			
			if ($this->getMsgCount() == 0){			// エラーのないとき
				// 設定値を更新
				$newObj = new stdClass;
				$newObj->searchTemplate = $searchTemplate;		// 検索用テンプレート
				$newObj->fieldInfo = $this->fieldInfoArray;			// カテゴリ定義
				$ret = $this->updateWidgetParamObj($newObj);
				if ($ret){
					$this->setMsg(self::MSG_GUIDANCE, 'データを更新しました');
					
					$replaceNew = true;			// データ再取得
				} else {
					$this->setMsg(self::MSG_APP_ERR, 'データ更新に失敗しました');
				}
			}
		} else {
			$replaceNew = true;			// データ再取得
		}
		
		// 表示用データを取得
		if ($replaceNew){
			$paramObj = $this->getWidgetParamObj();
			if (empty($paramObj)){		// 保存値がないとき
				$this->fieldInfoArray = array();			// 項目定義
			
				// デフォルトの検索テンプレート作成
				$searchTemplate = $this->getParsedTemplateData(photo_mainCommonDef::DEFAULT_SEARCH_AREA_TMPL, array($this, '_makeSearcheTemplate'));// デフォルト用の検索テンプレート
			} else {
				$searchTemplate = $paramObj->searchTemplate;		// 検索用テンプレート
				if (!empty($paramObj->fieldInfo)) $this->fieldInfoArray = $paramObj->fieldInfo;			// 項目定義
			}
		}

		// 親カテゴリ情報取得
		$this->categoryArray = array();		// カテゴリ種別メニュー
		$ret = $this->categoryDb->getAllPCategory($this->langId, $rows);
		if ($ret){
			for ($i = 0; $i < count($rows); $i++){
				$line = array();
				$line['name'] = $rows[$i]['name'];
				$line['value'] = $rows[$i]['parent'];
				$this->categoryArray[] = $line;
			}
		}

		// メニュー作成
		$this->createItemTypeMenu();
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
		$this->tmpl->addVar("_widget", "html",	$searchTemplate);
		$tagStr = photo_mainCommonDef::SEARCH_TEXT_ID . '(入力フィールドのID), ' . photo_mainCommonDef::SEARCH_BUTTON_ID . '(検索実行ボタンのID), ' . photo_mainCommonDef::SEARCH_RESET_ID . '(検索リセットボタンのID), ' . photo_mainCommonDef::SEARCH_SORT_ID . '(ソートメニューのID)';
		$this->tmpl->addVar("_widget", "tag_id_str", $tagStr);// タグIDの表示
		$this->tmpl->addVar('_widget', 'tag_start', M3_TAG_START . M3_TAG_MACRO_ITEM_KEY);		// 置換タグ(前)
		$this->tmpl->addVar('_widget', 'tag_end', M3_TAG_END);		// 置換タグ(後)
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
	 * 項目定義一覧を作成
	 *
	 * @return なし						
	 */
	function createFieldList()
	{
		$fieldCount = count($this->fieldInfoArray);
		for ($i = 0; $i < $fieldCount; $i++){
			$infoObj = $this->fieldInfoArray[$i];
			$itemType = $infoObj->itemType;// 項目種別
			$selectType = $infoObj->selectType;		// 選択方法
			$category = $infoObj->category;// カテゴリ

			// 項目タイプメニュー作成
			$this->tmpl->clearTemplate('item_type_list2');
			
			for ($j = 0; $j < count($this->itemTypeArray); $j++){
				$value = $this->itemTypeArray[$j]['value'];
				$name = $this->itemTypeArray[$j]['name'];

				$selected = '';
				if ($value == $itemType) $selected = 'selected';

				$tableLine = array(
					'value'    => $value,			// タイプ値
					'name'     => $this->convertToDispString($name),			// タイプ名
					'selected' => $selected			// 選択中かどうか
				);
				$this->tmpl->addVars('item_type_list2', $tableLine);
				$this->tmpl->parseTemplate('item_type_list2', 'a');
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
			
			// カテゴリーメニュー作成
			$this->tmpl->clearTemplate('category_list2');
			
			for ($j = 0; $j < count($this->categoryArray); $j++){
				$value = $this->categoryArray[$j]['value'];
				$name = $this->categoryArray[$j]['name'];

				$selected = '';
				if ($value == $category) $selected = 'selected';

				$tableLine = array(
					'value'    => $value,			// タイプ値
					'name'     => $this->convertToDispString($name),			// タイプ名
					'selected' => $selected			// 選択中かどうか
				);
				$this->tmpl->addVars('category_list2', $tableLine);
				$this->tmpl->parseTemplate('category_list2', 'a');
			}
			
			$row = array(
				'root_url' => $this->convertToDispString($this->getUrl($this->gEnv->getRootUrl())),
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
			$this->tmpl->addVars('category_list', $row);
			$this->tmpl->parseTemplate('category_list', 'a');
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
	 * 項目タイプメニュー作成
	 *
	 * @return なし
	 */
	function createItemTypeMenu()
	{
		for ($i = 0; $i < count($this->itemTypeArray); $i++){
			$value = $this->itemTypeArray[$i]['value'];
			$name = $this->itemTypeArray[$i]['name'];
			
			$row = array(
				'value'    => $value,			// タイプ値
				'name'     => $this->convertToDispString($name),			// タイプ名
				'selected' => $selected			// 選択中かどうか
			);
			$this->tmpl->addVars('item_type_list', $row);
			$this->tmpl->parseTemplate('item_type_list', 'a');
		}
	}
}
?>
