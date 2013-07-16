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
 * @version    SVN: $Id: admin_static_contentWidgetContainer.php 5489 2012-12-28 13:00:45Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseAdminWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/static_contentDb.php');

class admin_static_contentWidgetContainer extends BaseAdminWidgetContainer
{
	private $db;	// DB接続オブジェクト
	private $serialNo;		// 選択中の項目のシリアル番号
	private $serialArray = array();			// 表示中のシリアル番号
	private $langId;
	private $paramObj;		// パラメータ保存用オブジェクト
	private $contentId;		// コンテンツID
	private $menuHtml;	// コンテンツメニュー
	const DEFAULT_NAME_HEAD = '名称未設定';			// デフォルトの設定名
	const CONTENT_WIDGET_ID = 'default_content';			// コンテンツ編集ウィジェット
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->db = new static_contentDb();
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
			return 'admin_main_list.tmpl.html';
		} else {			// 一覧画面
			return 'admin_main_detail.tmpl.html';
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
		$name = $request->trimValueOf('item_name');
		$showReadMore = ($request->trimValueOf('item_show_read_more') == 'on') ? 1 : 0;		// 「続きを読む」ボタンを表示
		$readMoreTitle = $request->trimValueOf('item_read_more_title');						// 「続きを読む」ボタンタイトル
		$this->contentId = $request->trimValueOf('contentid');	// コンテンツID
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
			if (empty($this->contentId)) $this->setUserErrorMsg('表示コンテンツが選択されていません');

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
				$newObj->contentId = $this->contentId;		// コンテンツID
				$newObj->showReadMore	= $showReadMore;		// 「続きを読む」ボタンを表示
				$newObj->readMoreTitle	= $readMoreTitle;		// 「続きを読む」ボタンタイトル
				
				$ret = $this->addPageDefParam($defSerial, $defConfigId, $this->paramObj, $newObj);
				if ($ret){
					$this->setGuidanceMsg('データを追加しました');
					
					$this->configId = $defConfigId;		// 定義定義IDを更新
					$replaceNew = true;			// データ再取得
				} else {
					$this->setAppErrorMsg('データ追加に失敗しました');
				}
			}
		} else if ($act == 'update'){		// 項目更新の場合
			// エラーなしの場合は、データを更新
			if ($this->getMsgCount() == 0){
				// 現在の設定値を取得
				$ret = $this->getPageDefParam($defSerial, $defConfigId, $this->paramObj, $this->configId, $targetObj);
				if ($ret){
					// ウィジェットオブジェクト更新
					$targetObj->contentId = $this->contentId;
					$targetObj->showReadMore	= $showReadMore;		// 「続きを読む」ボタンを表示
					$targetObj->readMoreTitle	= $readMoreTitle;		// 「続きを読む」ボタンタイトル
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
		} else if ($act == 'getmenu'){		// コンテンツ選択メニュー取得
			// コンテンツIDを取得
			$contentSerial = $request->trimValueOf('content_serial');
			$ret = $this->db->getContentBySerial($contentSerial, $row);
			if ($ret) $this->contentId = $row['cn_id'];
			
			// コンテンツ選択メニューを作成
			$this->menuHtml  = '<select name="contentid">';
	        $this->menuHtml .= '<option value="0">-- 未選択 --</option>';
			$this->db->getAllContentItems(array($this, 'itemListLoop'), $this->langId);
			$this->menuHtml .= '</select>';
			
			$this->gInstance->getAjaxManager()->addData('menu_html', $this->menuHtml);
		} else if ($act == 'select'){	// 定義IDを変更
			$replaceNew = true;			// データ再取得
		}

		// 定義選択メニュー作成
		$this->createDefListMenu();

		// 表示用データを取得
		if (empty($this->configId)){		// 新規登録の場合
			$this->tmpl->setAttribute('item_name_visible', 'visibility', 'visible');// 名前入力フィールド表示
			if ($replaceNew){		// データ再取得時
				$name = $this->createDefaultName();			// デフォルト登録項目名
				$showReadMore = 0;		// 「続きを読む」ボタンを表示
				$readMoreTitle	= '';		// 「続きを読む」ボタンタイトル
			}
			$this->serialNo = 0;
		} else {
			if ($replaceNew){
				$ret = $this->getPageDefParam($defSerial, $defConfigId, $this->paramObj, $this->configId, $targetObj);
				if ($ret){
					$name = $targetObj->name;// 名前
					$this->contentId = $targetObj->contentId;			// コンテンツID
					$showReadMore = $targetObj->showReadMore;		// 「続きを読む」ボタンを表示
					$readMoreTitle	= $targetObj->readMoreTitle;		// 「続きを読む」ボタンタイトル
				}
			}
			$this->serialNo = $this->configId;
				
			// 新規作成でないときは、メニューを変更不可にする(画面作成から呼ばれている場合のみ)
			if (!empty($defConfigId) && !empty($defSerial)) $this->tmpl->addVar("_widget", "id_disabled", 'disabled');
		}
		
		// コンテンツ項目リストをデフォルト言語で取得
		$this->db->getAllContentItems(array($this, 'itemListLoop'), $this->langId);
		
		// ### 入力値を再設定 ###
		$this->tmpl->addVar("item_name_visible", "name", $name);		// 名前
		$checked = '';
		if ($showReadMore) $checked = 'checked';
		$this->tmpl->addVar("_widget", "show_read_more", $checked);	// 「続きを読む」ボタンを表示
		$this->tmpl->addVar("_widget", "read_more_title", $readMoreTitle);		// 「続きを読む」ボタンタイトル
		$this->tmpl->addVar("_widget", "serial", $this->serialNo);// 選択中のシリアル番号、IDを設定
		$this->tmpl->addVar('_widget', 'content_widget_id', self::CONTENT_WIDGET_ID);// コンテンツ表示ウィジェット
		
		// ボタンの表示制御
		if (empty($this->serialNo)){		// 新規追加項目を選択しているとき
			$this->tmpl->setAttribute('add_button', 'visibility', 'visible');// 「新規追加」ボタン
		} else {
			$this->tmpl->setAttribute('update_button', 'visibility', 'visible');// 「更新」ボタン
		}
		// タブの選択状態を設定
		// 一度設定を保存している場合は、メニュー定義を前面にする(初期起動時のみ)
		//if (empty($act) && !empty($this->configId)) $this->tmpl->setAttribute('select_edit_content', 'visibility', 'visible');
		
		// ページ定義IDとページ定義のレコードシリアル番号を更新
		$this->endPageDefParam($defSerial, $defConfigId, $this->paramObj);
	}
	/**
	 * 定義選択用メニュー
	 *
	 * @return なし						
	 */
	function createDefListMenu()
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
	 * 取得したデータをテンプレートに設定する
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function itemListLoop($index, $fetchedRow, $param)
	{
		$id = $fetchedRow['cn_id'];
		$selected = '';
		if ($this->contentId == $id) $selected = 'selected';
			
		$row = array(
			'value' => $id,			// ID
			'name' => $this->convertToDispString($fetchedRow['cn_name']),		// 名前
			'selected' => $selected	// 選択中の項目かどうか
		);
		$this->tmpl->addVars('content_list', $row);
		$this->tmpl->parseTemplate('content_list', 'a');
		
		// コンテンツ選択メニューHTML
		$this->menuHtml .= '<option value="' . $id . '" ' . $selected . '>' . $this->convertToDispString($fetchedRow['cn_name']) . '</option>';
		return true;
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
		$this->langId	= $this->gEnv->getCurrentLanguage();		// 表示言語を取得
		$act = $request->trimValueOf('act');
		
		// 詳細画面からの引継ぎデータ
		$contentId = $request->trimValueOf('contentid');
		
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
		// 選択リスト作成
		$this->createDefList();
		
		// メニュー定義画面のURLを作成
		$editUrl = $this->gEnv->getDefaultAdminUrl() . '?cmd=configwidget&openby=tabs&widget=' . self::CONTENT_WIDGET_ID . '&task=content_detail&contentid=' . $contentId;
		$this->tmpl->addVar("_widget", "url", $this->getUrl($editUrl));
		$this->tmpl->addVar("_widget", "content_id", $contentId);
		
		$this->tmpl->addVar("_widget", "serial_list", implode($this->serialArray, ','));// 表示項目のシリアル番号を設定
		
		// ページ定義IDとページ定義のレコードシリアル番号を更新
		$this->endPageDefParam($defSerial, $defConfigId, $this->paramObj);
	}
	/**
	 * 選択リスト作成
	 *
	 * @return なし						
	 */
	function createDefList()
	{
		for ($i = 0; $i < count($this->paramObj); $i++){
			$id = $this->paramObj[$i]->id;// 定義ID
			$targetObj = $this->paramObj[$i]->object;
			$name = $targetObj->name;// 定義名
			
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
				'def_count' => $defCount							// 使用数
			);
			$this->tmpl->addVars('itemlist', $row);
			$this->tmpl->parseTemplate('itemlist', 'a');
			
			// シリアル番号を保存
			$this->serialArray[] = $id;
		}
	}
}
?>
