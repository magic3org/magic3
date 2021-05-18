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
require_once($gEnvManager->getCurrentWidgetDbPath() . '/featured_contentDb.php');

class admin_featured_contentWidgetContainer extends BaseAdminWidgetContainer
{
	private $db;	// DB接続オブジェクト
	private $serialNo;		// 選択中の項目のシリアル番号
	private $serialArray = array();			// 表示中のシリアル番号
	private $langId;
	private $configId;		// 定義ID
	private $paramObj;		// パラメータ保存用オブジェクト
	private $fieldInfoArray = array();			// フィールド情報
	const DEFAULT_NAME_HEAD = '名称未設定';			// デフォルトの設定名
	const CONTENT_WIDGET_ID_PC = 'default_content';			// コンテンツ編集ウィジェット(PC用)
	const CONTENT_TYPE = '';			// コンテンツタイプ
	const DEFAULT_COLUMN_COUNT = 2;		// カラム数
	const DEFAULT_LEAD_CONTENT_COUNT = 1;		// 先頭のコンテンツ数
	const DEFAULT_COLUMN_CONTENT_COUNT = 2;		// カラム部のコンテンツ数
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->db = new featured_contentDb();
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
		$this->langId	= $this->gEnv->getCurrentLanguage();		// 表示言語を取得
		$act = $request->trimValueOf('act');
		$this->serialNo = $request->trimValueOf('serial');		// 選択項目のシリアル番号
		$this->configId = $request->trimValueOf('item_id');		// 定義ID
		if (empty($this->configId)) $this->configId = $defConfigId;		// 呼び出しウィンドウから引き継いだ定義ID
		
		// 入力値を取得
		//$defName	= $request->trimValueOf('item_def_name');			// 定義名
		$name	= $request->trimValueOf('item_name');			// 定義名
		$fieldCount = $request->trimValueOf('fieldcount');		// 表示項目数
		$contentIds = $request->trimValueOf('item_contentid');		// 表示項目、コンテンツID
		$showReadMore = ($request->trimValueOf('item_show_read_more') == 'on') ? 1 : 0;		// 「続きを読む」ボタンを表示
		$readMoreTitle = $request->trimValueOf('item_read_more_title');						// 「続きを読む」ボタンタイトル
		$leadContentCount = $request->trimValueOf('item_lead_content_count');						// 先頭のコンテンツ数
		$columnContentCount = $request->trimValueOf('item_column_content_count');						// カラム部のコンテンツ数
		$columnCount	= $request->trimValueOf('item_column_count');						// カラム数
		$showCreateDate = ($request->trimValueOf('item_show_created_date') == 'on') ? 1 : 0;		// 表示項目(作成日)
		$showModifiedDate = ($request->trimValueOf('item_show_modified_date') == 'on') ? 1 : 0;		// 表示項目(更新日)
		$showPublishedDate = ($request->trimValueOf('item_show_published_date') == 'on') ? 1 : 0;		// 表示項目(公開日)
		
		$replaceNew = false;		// データを再取得するかどうか
		if ($act == 'add'){// 新規追加
			// 入力値のエラーチェック
			$this->checkNumeric($leadContentCount, '先頭部のコンテンツ数');
			$this->checkNumeric($columnContentCount, 'カラム部のコンテンツ数');
			$this->checkNumeric($columnCount, 'カラム数');
			
			// 設定名の重複チェック
			if (is_array($this->paramObj)){
				for ($i = 0; $i < count($this->paramObj); $i++){
					$targetObj = $this->paramObj[$i]->object;
					//if ($defName == $targetObj->name){		// 定義名
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
				//$newObj->name		= $defName;// 表示名
				$newObj->name	= $name;// 表示名
				$newObj->showReadMore	= $showReadMore;		// 「続きを読む」ボタンを表示
				$newObj->readMoreTitle	= $readMoreTitle;		// 「続きを読む」ボタンタイトル
				$newObj->leadContentCount	= $leadContentCount;						// 先頭のコンテンツ数
				$newObj->columnContentCount	= $columnContentCount;						// カラム部のコンテンツ数
				$newObj->columnCount	= $columnCount;						// カラム数
				$newObj->showCreateDate		= $showCreateDate;		// 表示項目(作成日)
				$newObj->showModifiedDate	= $showModifiedDate;		// 表示項目(更新日)
				$newObj->showPublishedDate	= $showPublishedDate;		// 表示項目(公開日)
		
				$newObj->fieldInfo	= array();
				
				for ($i = 0; $i < $fieldCount; $i++){
					$newInfoObj = new stdClass;
					$newInfoObj->contentId	= $contentIds[$i];
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
			$this->checkNumeric($leadContentCount, '先頭部のコンテンツ数');
			$this->checkNumeric($columnContentCount, 'カラム部のコンテンツ数');
			$this->checkNumeric($columnCount, 'カラム数');
			
			if ($this->getMsgCount() == 0){			// エラーのないとき
				// 現在の設定値を取得
				$ret = $this->getPageDefParam($defSerial, $defConfigId, $this->paramObj, $this->configId, $targetObj);
				if ($ret){
					// ウィジェットオブジェクト更新
					$targetObj->showReadMore	= $showReadMore;		// 「続きを読む」ボタンを表示
					$targetObj->readMoreTitle	= $readMoreTitle;		// 「続きを読む」ボタンタイトル
					$targetObj->leadContentCount	= $leadContentCount;						// 先頭のコンテンツ数
					$targetObj->columnContentCount	= $columnContentCount;						// カラム部のコンテンツ数
					$targetObj->columnCount	= $columnCount;						// カラム数
					$targetObj->showCreateDate		= $showCreateDate;		// 表示項目(作成日)
					$targetObj->showModifiedDate	= $showModifiedDate;		// 表示項目(更新日)
					$targetObj->showPublishedDate	= $showPublishedDate;		// 表示項目(公開日)
					$targetObj->fieldInfo	= array();
				
					for ($i = 0; $i < $fieldCount; $i++){
						$newInfoObj = new stdClass;
						$newInfoObj->contentId	= $contentIds[$i];
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
			//$this->tmpl->setAttribute('item_def_name_visible', 'visibility', 'visible');// 名前入力フィールド表示
			$this->tmpl->setAttribute('item_name_visible', 'visibility', 'visible');// 名前入力フィールド表示
			if ($replaceNew){		// データ再取得時
				//$defName = $this->createDefaultName();			// デフォルト登録項目名
				$name = $this->createConfigDefaultName();			// デフォルト登録項目名
				$showReadMore = 0;		// 「続きを読む」ボタンを表示
				$readMoreTitle	= '';		// 「続きを読む」ボタンタイトル
				$leadContentCount	= self::DEFAULT_LEAD_CONTENT_COUNT;						// 先頭のコンテンツ数
				$columnContentCount	= self::DEFAULT_COLUMN_CONTENT_COUNT;						// カラム部のコンテンツ数
				$columnCount 		= self::DEFAULT_COLUMN_COUNT;						// カラム数
				$showCreateDate		= 0;		// 表示項目(作成日)
				$showModifiedDate	= 0;		// 表示項目(更新日)
				$showPublishedDate	= 0;		// 表示項目(公開日)
				$this->fieldInfoArray = array();			// フィールド情報
			}
			$this->serialNo = 0;
		} else {
			if ($replaceNew){// データ再取得時
				$ret = $this->getPageDefParam($defSerial, $defConfigId, $this->paramObj, $this->configId, $targetObj);
				if ($ret){
					//$defName	= $targetObj->name;// 名前
					$name		= $targetObj->name;	// 名前
					$showReadMore = $targetObj->showReadMore;		// 「続きを読む」ボタンを表示
					$readMoreTitle	= $targetObj->readMoreTitle;		// 「続きを読む」ボタンタイトル
					$leadContentCount	= $targetObj->leadContentCount;						// 先頭のコンテンツ数
					$columnContentCount	= $targetObj->columnContentCount;						// カラム部のコンテンツ数
					$columnCount		= $targetObj->columnCount;						// カラム数
					$showCreateDate		= $targetObj->showCreateDate;		// 表示項目(作成日)
					$showModifiedDate	= $targetObj->showModifiedDate;		// 表示項目(更新日)
					$showPublishedDate	= $targetObj->showPublishedDate;		// 表示項目(公開日)
					if (!empty($targetObj->fieldInfo)) $this->fieldInfoArray = $targetObj->fieldInfo;			// フィールド情報
				}
			}
			$this->serialNo = $this->configId;
				
			// 新規作成でないときは、メニューを変更不可にする(画面作成から呼ばれている場合のみ)
			if (!empty($defConfigId) && !empty($defSerial)) $this->tmpl->addVar("_widget", "id_disabled", 'disabled');
		}
		
		// 表示項目一覧作成
		$this->createFieldList();
		if (empty($this->fieldInfoArray)) $this->tmpl->setAttribute('field_list', 'visibility', 'hidden');// 表示項目一覧を表示
			
		// 画面にデータを埋め込む(ウィジェット共通部)
		if (!empty($this->configId)) $this->tmpl->addVar("_widget", "id", $this->configId);		// 定義ID
		//$this->tmpl->addVar("item_def_name_visible", "def_name",	$defName);
		$this->tmpl->addVar("item_name_visible", "name",	$name);
		$this->tmpl->addVar("_widget", "serial", $this->serialNo);// 選択中のシリアル番号、IDを設定
		
		// 画面にデータを埋め込む
		$checked = '';
		if ($showReadMore) $checked = 'checked';
		$this->tmpl->addVar("_widget", "show_read_more", $checked);	// 「続きを読む」ボタンを表示
		$this->tmpl->addVar("_widget", "read_more_title", $readMoreTitle);		// 「続きを読む」ボタンタイトル
		$this->tmpl->addVar("_widget", "lead_content_count", $leadContentCount);		// 先頭のコンテンツ数
		$this->tmpl->addVar("_widget", "column_content_count", $columnContentCount);						// カラム部のコンテンツ数
		$this->tmpl->addVar("_widget", "column_count", $columnCount);						// カラム数
		$checked = '';
		if ($showCreateDate) $checked = 'checked';
		$this->tmpl->addVar("_widget", "show_created_date", $checked);	// 表示項目(作成日)
		$checked = '';
		if ($showModifiedDate) $checked = 'checked';
		$this->tmpl->addVar("_widget", "show_modified_date", $checked);	// 表示項目(更新日)
		$checked = '';
		if ($showPublishedDate) $checked = 'checked';
		$this->tmpl->addVar("_widget", "show_published_date", $checked);	// 表示項目(公開日)
					
		$this->tmpl->addVar('_widget', 'content_widget_id', self::CONTENT_WIDGET_ID_PC);// コンテンツ表示ウィジェット
		$this->tmpl->addVar('_widget', 'admin_url', $this->getUrl($this->gEnv->getDefaultAdminUrl()));// 管理者URL
		
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
			$defName = $targetObj->name;// 定義名
			$selected = '';
			if ($this->configId == $id) $selected = 'selected';

			$row = array(
				'name' => $defName,		// 名前
				'value' => $id,		// 定義ID
				'selected' => $selected	// 選択中の項目かどうか
			);
			$this->tmpl->addVars('title_list', $row);
			$this->tmpl->parseTemplate('title_list', 'a');
		}
	}
	/**
	 * 表示項目一覧を作成
	 *
	 * @return なし						
	 */
	function createFieldList()
	{
		$fieldCount = count($this->fieldInfoArray);
		for ($i = 0; $i < $fieldCount; $i++){
			$infoObj = $this->fieldInfoArray[$i];
//			$name = $infoObj->name;// 名前
			$contentId = $infoObj->contentId;		// コンテンツID
			
			// コンテンツを取得
			$ret = $this->db->getContentByContentId(self::CONTENT_TYPE, $contentId, $this->langId, $row);
			if ($ret){
				$contentName = $row['cn_name'];
			}
			
			$row = array(
//				'name' => $this->convertToDispString($name),	// 名前
				'name' => $this->convertToDispString($contentName),	// コンテンツ名
				'content_id' => $this->convertToDispString($contentId),	// コンテンツID
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
/*	function createDefaultName()
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
	}*/
}
?>
