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
 * @version    SVN: $Id: admin_s_css_addWidgetContainer.php 4549 2012-01-02 02:10:58Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseAdminWidgetContainer.php');

class admin_s_css_addWidgetContainer extends BaseAdminWidgetContainer
{
	private $serialNo;		// 選択中の項目のシリアル番号
	private $serialArray = array();			// 表示中のシリアル番号
	private $langId;
	private $configId;		// 定義ID
	private $paramObj;		// パラメータ保存用オブジェクト
	private $css;			// CSS
	private $cssFiles;		// CSSファイル
	private $cssDir;		// CSSファイル読み込みディレクトリ
	const DEFAULT_NAME_HEAD = '名称未設定';			// デフォルトの設定名
	const DEFAULT_CSS = "input.button {\n    border:2px outset #FF3366;\n    background-color:#FF3366;\n}\n";
	const CSS_DIR = '/resource/css';			// CSSファイル格納ディレクトリ
	const FIELD_HEAD = 'item_file_';					// CSSファイル選択項目名ヘッダ
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
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
		
		$this->langId	= $this->gEnv->getCurrentLanguage();		// 表示言語を取得
		$this->cssDir	=  $this->gEnv->getSystemRootPath() . self::CSS_DIR;		// CSSファイル読み込みディレクトリ
		
		$act = $request->trimValueOf('act');
		$this->serialNo = $request->trimValueOf('serial');		// 選択項目のシリアル番号
		$this->configId = $request->trimValueOf('item_id');		// 定義ID
		if (empty($this->configId)) $this->configId = $defConfigId;		// 呼び出しウィンドウから引き継いだ定義ID
		
		// 入力値を取得
		$name	= $request->trimValueOf('item_name');			// 定義名
		$this->css	= $request->valueOf('item_css');		// CSS
		
		// CSSファイル
		$this->cssFiles = array();
		$files = $this->getFiles($this->cssDir);
		for ($i = 0; $i < count($files); $i++){
			$itemName = self::FIELD_HEAD . ($i + 1);
			$itemValue = $request->trimValueOf($itemName);
			if (!empty($itemValue))	$this->cssFiles[] = $itemValue;
		}

		$replaceNew = false;		// データを再取得するかどうか
		if ($act == 'add'){// 新規追加
			// 入力チェック
			$this->checkInput($name, '名前');
			$this->checkInput($this->css, 'CSS');
			
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
				$newObj->name	= $name;// 表示名
				$newObj->css	= $this->css;					// CSS
				$newObj->cssFiles = $this->cssFiles;			// CSSファイル
				
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
			$this->checkInput($this->css, 'CSS');
			
			if ($this->getMsgCount() == 0){			// エラーのないとき
				// 現在の設定値を取得
				$ret = $this->getPageDefParam($defSerial, $defConfigId, $this->paramObj, $this->configId, $targetObj);
				if ($ret){
					// ウィジェットオブジェクト更新
					$targetObj->css		= $this->css;					// CSS
					$targetObj->cssFiles = $this->cssFiles;			// CSSファイル
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
		// 表示用データを取得
		if (empty($this->configId)){		// 新規登録の場合
			$this->tmpl->setAttribute('item_name_visible', 'visibility', 'visible');// 名前入力フィールド表示
			if ($replaceNew){		// データ再取得時
				//$name = $this->createDefaultName();			// デフォルト登録項目名
				$name = $this->createConfigDefaultName();			// デフォルト登録項目名
				$this->css		= self::DEFAULT_CSS;					// CSS
				$this->cssFiles = array();			// CSSファイル
			}
			$this->serialNo = 0;
		} else {
			if ($replaceNew){// データ再取得時
				$ret = $this->getPageDefParam($defSerial, $defConfigId, $this->paramObj, $this->configId, $targetObj);
				if ($ret){
					$name		= $targetObj->name;	// 名前
					$this->css	= $targetObj->css;	// CSS
					$this->cssFiles = $targetObj->cssFiles;			// CSSファイル
				}
			}
			$this->serialNo = $this->configId;
				
			// 新規作成でないときは、メニューを変更不可にする(画面作成から呼ばれている場合のみ)
			if (!empty($defConfigId) && !empty($defSerial)) $this->tmpl->addVar("_widget", "id_disabled", 'disabled');
		}

		// 設定項目選択メニュー作成
		$this->createItemMenu();
		
		// CSSファイル一覧作成
		$addFiles = array();		// すでに登録されているので追加が必要なファイル
		for ($i = 0; $i < count($this->cssFiles); $i++){
			$file = $this->cssDir . $this->cssFiles[$i];
			if (!in_array($file, $files)) $addFiles[] = $file;
		}
		$files = array_merge($files, $addFiles);
		if (empty($files)){
			$this->tmpl->setAttribute('itemlist', 'visibility', 'hidden');
		} else {
			$this->createCssFileList($files);
		}
		
		// 画面にデータを埋め込む
		if (!empty($this->configId)) $this->tmpl->addVar("_widget", "id", $this->configId);		// 定義ID
		$this->tmpl->addVar("item_name_visible", "name",	$name);
		$this->tmpl->addVar("_widget", "css",	$this->css);
		$this->tmpl->addVar("_widget", "css_dir", $this->cssDir);	// CSSファイル読み込みディレクトリ
		$this->tmpl->addVar("_widget", "file_count", count($files));	// CSSファイル数
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
	 * 一覧画面作成
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param								なし
	 */
	function createList($request)
	{
		// ページ定義IDとページ定義のレコードシリアル番号を取得
		$this->startPageDefParam($defSerial, $defConfigId, $this->paramObj);
		
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
		
		$this->tmpl->addVar("_widget", "serial_list", implode(',', $this->serialArray));// 表示項目のシリアル番号を設定
		
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
		
			// 使用数
			$defCount = 0;
			if (!empty($id)){
				$defCount = $this->_db->getPageDefCount($this->gEnv->getCurrentWidgetId(), $id);
			}
			$operationDisagled = '';
			if ($defCount > 0) $operationDisagled = 'disabled';
			
			$row = array(
				'index' => $i,
				'id' => $id,
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
	 * CSSファイル一覧作成
	 *
	 * @param array $files		CSSファイルフルパス
	 * @return なし
	 */
	function createCssFileList($files)
	{
		for ($i = 0; $i < count($files); $i++){
			$path = str_replace($this->cssDir, '', $files[$i]);	// 相対パス
			$name = trim($path, DIRECTORY_SEPARATOR);
			
			$checked = '';
			if (in_array($path, $this->cssFiles)) $checked = 'checked';

			$row = array(
				'index'		=> $i + 1,
				'value'		=> $this->convertToDispString($path),			// 値
				'name'		=> $this->convertToDispString($name),			// 名前
				'file_checked'	=> $checked			// 選択中かどうか
			);
			$this->tmpl->addVars('itemlist', $row);
			$this->tmpl->parseTemplate('itemlist', 'a');
		}
	}
	/**
	 * 指定ディレクトリのファイル一覧を取得
	 *
	 * @param string $path		読み込みディレクトリ
	 * @return array			ファイル名一覧
	 */
	function getFiles($path)
	{
		$files = array();
		if (is_dir($path)){
			$dir = dir($path);
			while (($file = $dir->read()) !== false){
				if ($file == '.' || $file == '..') continue;
				
				$filePath = $path . '/' . $file;
				if (strncmp($file, '.', 1) != 0 &&		// 「.」で始まる名前のディレクトリ、ファイルは読み込まない
					strncmp($file, '_', 1) != 0){		// 「_」で始まる名前のディレクトリ、ファイルは読み込まない	
					if (is_dir($filePath)){	// ディレクトリのとき
						$addFiles = $this->getFiles($filePath);
						$files = array_merge($files, $addFiles);
					} else if (is_file($filePath)){// ファイルのとき
						$files[] = $filePath;
					}
				}
			}
			$dir->close();
		}
		return $files;
	}
}
?>
