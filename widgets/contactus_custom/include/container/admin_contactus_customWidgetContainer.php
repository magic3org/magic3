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
 * @copyright  Copyright 2006-2009 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: admin_contactus_customWidgetContainer.php 2319 2009-09-16 01:10:12Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseAdminWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/contactus_customDb.php');

class admin_contactus_customWidgetContainer extends BaseAdminWidgetContainer
{
	private $db;	// DB接続オブジェクト
	private $sysDb;	// DB接続オブジェクト
	private $serialNo;		// 選択中の項目のシリアル番号
	private $serialArray = array();			// 表示中のシリアル番号
	private $langId;
	private $configId;		// 定義ID
	private $paramObj;		// パラメータ保存用オブジェクト
	private $typeArray;		// 項目タイプ
	private $fieldInfoArray = array();			// お問い合わせ項目情報
	const DEFAULT_NAME_HEAD = '名称未設定';			// デフォルトの設定名
	const DEFAULT_TITLE_NAME = 'お問い合わせ';	// デフォルトのタイトル名
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->db = new contactus_customDb();
		$this->sysDb = $this->gInstance->getSytemDbObject();
		
		// お問い合わせ項目タイプ
		$this->typeArray = array(	array(	'name' => 'テキストボックス',	'value' => 'text'),
									array(	'name' => 'テキストエリア',		'value' => 'textarea'),
									array(	'name' => 'セレクトメニュー',	'value' => 'select'),
									array(	'name' => 'チェックボックス',	'value' => 'checkbox'),
									array(	'name' => 'ラジオボタン',		'value' => 'radio'));
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
		$this->configId = $request->trimValueOf('item_id');		// 定義ID
		if (empty($this->configId)) $this->configId = $defConfigId;		// 呼び出しウィンドウから引き継いだ定義ID
		
		// 入力値を取得
		$name	= $request->trimValueOf('item_name');			// 定義名
		$showTitle = ($request->trimValueOf('show_title') == 'on') ? 1 : 0;		// タイトルの表示
		$titleName = $request->trimValueOf('title_name');				// タイトル名
		$explanation = trim($request->valueOf('explanation'));				// 説明
		$fieldCount = $request->trimValueOf('fieldcount');		// お問い合わせ項目数
		$titles = $request->trimValueOf('item_title');		// お問い合わせ項目タイトル
		$descs = $request->trimValueOf('item_desc');		// お問い合わせ項目説明
		$types	= $request->trimValueOf('item_type');		// お問い合わせ項目タイプ
		$defs = $request->trimValueOf('item_def');		// お問い合わせ項目定義
		$values = $request->trimValueOf('required');		// お問い合わせ項目必須入力
		$requireds = array();
		if (!empty($values)) $requireds = explode(',', $values);
		$emailSubject = $request->trimValueOf('email_subject');		// メールタイトル
		$emailReceiver = trim($request->valueOf('email_receiver'));	// メール受信者(aaaa<xxx@xxx.xxx>形式が可能)
		
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
				$newObj->showTitle		= $showTitle;		// タイトルの表示
				$newObj->titleName		= $titleName;				// タイトル名
				$newObj->explanation	= $explanation;				// 説明
				$newObj->emailSubject = $emailSubject;		// メールタイトル
				$newObj->emailReceiver = $emailReceiver;	// メール受信者(aaaa<xxx@xxx.xxx>形式が可能)
				$newObj->fieldInfo	= array();
				
				for ($i = 0; $i < $fieldCount; $i++){
					$newInfoObj = new stdClass;
					$newInfoObj->title	= $titles[$i];
					$newInfoObj->desc	= $descs[$i];
					$newInfoObj->type	= $types[$i];
					$newInfoObj->def	= $defs[$i];
					$newInfoObj->required	= $requireds[$i];
					$newObj->fieldInfo[] = $newInfoObj;
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
					$targetObj->showTitle		= $showTitle;		// タイトルの表示
					$targetObj->titleName		= $titleName;				// タイトル名
					$targetObj->explanation	= $explanation;				// 説明
					$targetObj->emailSubject = $emailSubject;		// メールタイトル
					$targetObj->emailReceiver = $emailReceiver;	// メール受信者(aaaa<xxx@xxx.xxx>形式が可能)
					$targetObj->fieldInfo	= array();
				
					for ($i = 0; $i < $fieldCount; $i++){
						$newInfoObj = new stdClass;
						$newInfoObj->title	= $titles[$i];
						$newInfoObj->desc	= $descs[$i];
						$newInfoObj->type	= $types[$i];
						$newInfoObj->def	= $defs[$i];
						$newInfoObj->required	= $requireds[$i];
						$targetObj->fieldInfo[] = $newInfoObj;
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
				$showTitle = 0;		// タイトルの表示
				$titleName = self::DEFAULT_TITLE_NAME;				// タイトル名
				$explanation = '';				// 説明
				$emailSubject = '';		// メールタイトル
				$emailReceiver = '';	// メール受信者(aaaa<xxx@xxx.xxx>形式が可能)
				$this->fieldInfoArray = array();			// お問い合わせ項目情報
			}
			$this->serialNo = 0;
		} else {
			if ($replaceNew){// データ再取得時
				$ret = $this->getPageDefParam($defSerial, $defConfigId, $this->paramObj, $this->configId, $targetObj);
				if ($ret){
					$name	= $targetObj->name;// 名前
					$showTitle = $targetObj->showTitle;		// タイトルの表示
					$titleName = $targetObj->titleName;				// タイトル名
					$explanation = $targetObj->explanation;				// 説明
					$emailSubject = $targetObj->emailSubject;		// メールタイトル
					$emailReceiver = $targetObj->emailReceiver;	// メール受信者(aaaa<xxx@xxx.xxx>形式が可能)
					if (!empty($targetObj->fieldInfo)) $this->fieldInfoArray = $targetObj->fieldInfo;			// お問い合わせ項目情報
				}
			}
			$this->serialNo = $this->configId;
				
			// 新規作成でないときは、メニューを変更不可にする(画面作成から呼ばれている場合のみ)
			if (!empty($defConfigId) && !empty($defSerial)) $this->tmpl->addVar("_widget", "id_disabled", 'disabled');
		}
		
		// 追加用タイプメニュー作成
		$this->createTypeMenu1();
		
		// お問い合わせ項目一覧作成
		$this->createFieldList();
		if (empty($this->fieldInfoArray)) $this->tmpl->setAttribute('field_list', 'visibility', 'hidden');// お問い合わせ項目情報一覧
		
		// 画面にデータを埋め込む
		if (!empty($this->configId)) $this->tmpl->addVar("_widget", "id", $this->configId);		// 定義ID
		$this->tmpl->addVar("item_name_visible", "name",	$name);
		if (!empty($showTitle)) $this->tmpl->addVar("_widget", "show_title",	'checked');		// タイトルの表示
		$this->tmpl->addVar("_widget", "title_name",	$this->convertToDispString($titleName));				// タイトル名
		$this->tmpl->addVar("_widget", "explanation",	$explanation);				// 説明
		$this->tmpl->addVar("_widget", "email_subject",	$emailSubject);		// メールタイトル
		$this->tmpl->addVar("_widget", "email_receiver",	$emailReceiver);	// メール受信者(aaaa<xxx@xxx.xxx>形式が可能)
		$this->tmpl->addVar("_widget", "serial", $this->serialNo);// 選択中のシリアル番号、IDを設定
		
		// ボタンの表示制御
		if (empty($this->serialNo)){		// 新規追加項目を選択しているとき
			$this->tmpl->setAttribute('add_button', 'visibility', 'visible');// 「新規追加」ボタン
			
			// プレビューボタン作成
			$this->tmpl->addVar("_widget", "preview_disabled", 'disabled ');// 「プレビュー」ボタン
		} else {
			$this->tmpl->setAttribute('update_button', 'visibility', 'visible');// 「更新」ボタン
			
			// このウィジェットがマップされているページサブIDを取得
			$subPageId = $this->gPage->getPageSubIdByWidget($this->gEnv->getDefaultPageId(), $this->gEnv->getCurrentWidgetId(), $defConfigId);
			$previewUrl = $this->gEnv->getDefaultUrl();
			if (!empty($subPageId)) $previewUrl .= '?sub=' . $subPageId;
			$this->tmpl->addVar("_widget", "preview_url", $this->getUrl($previewUrl));
			
			// ヘルプの追加
			$this->convertHelp('update_button');
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
	 * お問い合わせ項目一覧を作成
	 *
	 * @return なし						
	 */
	function createFieldList()
	{
		$fieldCount = count($this->fieldInfoArray);
		for ($i = 0; $i < $fieldCount; $i++){
			$infoObj = $this->fieldInfoArray[$i];
			$title = $infoObj->title;// タイトル名
			$desc = $infoObj->desc;		// 説明
			$type = $infoObj->type;		// 項目タイプ
			$def = $infoObj->def;		// 項目定義
			$requiredCheck = '';
			if (!empty($infoObj->required)) $requiredCheck = 'checked';
			
			// 行を作成
			$this->tmpl->clearTemplate('type_list2');
			
			for ($j = 0; $j < count($this->typeArray); $j++){
				$value = $this->typeArray[$j]['value'];
				$name = $this->typeArray[$j]['name'];

				$selected = '';
				if ($value == $type) $selected = 'selected';

				$tableLine = array(
					'value'    => $value,			// タイプ値
					'name'     => $this->convertToDispString($name),			// タイプ名
					'selected' => $selected			// 選択中かどうか
				);
				$this->tmpl->addVars('type_list2', $tableLine);
				$this->tmpl->parseTemplate('type_list2', 'a');
			}
			
			$row = array(
				'title' => $this->convertToDispString($title),	// タイトル名
				'desc' => $this->convertToDispString($desc),	// 説明
				'def' => $this->convertToDispString($def),		// 定義情報
				'required' => $requiredCheck,							// 必須入力
				'root_url' => $this->convertToDispString($this->getUrl($this->gEnv->getRootUrl()))
			);
			$this->tmpl->addVars('field_list', $row);
			$this->tmpl->parseTemplate('field_list', 'a');
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
			$emailReceiver	= $targetObj->emailReceiver;		// 受信メールアドレス
		
			// 使用数
			$defCount = 0;
			if (!empty($id)){
				$defCount = $this->sysDb->getPageDefCount($this->gEnv->getCurrentWidgetId(), $id);
			}
			$operationDisagled = '';
			if ($defCount > 0) $operationDisagled = 'disabled';
			
			$row = array(
				'index' => $i,
				'id' => $id,
				'ope_disabled' => $operationDisagled,			// 選択可能かどうか
				'name' => $this->convertToDispString($name),		// 名前
				'email_receiver' => $this->convertToDispString($emailReceiver),	// 受信メールアドレス
				'def_count' => $defCount							// 使用数
			);
			$this->tmpl->addVars('itemlist', $row);
			$this->tmpl->parseTemplate('itemlist', 'a');
			
			// シリアル番号を保存
			$this->serialArray[] = $id;
		}
	}
	/**
	 * タイプ選択メニュー作成
	 *
	 * @return なし
	 */
	function createTypeMenu1()
	{
		for ($i = 0; $i < count($this->typeArray); $i++){
			$value = $this->typeArray[$i]['value'];
			$name = $this->typeArray[$i]['name'];
			
			$row = array(
				'value'    => $value,			// タイプ値
				'name'     => $this->convertToDispString($name),			// タイプ名
				'selected' => $selected			// 選択中かどうか
			);
			$this->tmpl->addVars('type_list1', $row);
			$this->tmpl->parseTemplate('type_list1', 'a');
		}
	}
}
?>
