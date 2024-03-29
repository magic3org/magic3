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
	const OTHER_TASK_NAME = 'コンテンツ';			// 別画面タスク名
	
	// 画面
	const TASK_LIST = 'list';			// 設定一覧
	
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
			$this->replaceAssignTemplate(self::ASSIGN_TEMPLATE_BASIC_CONFIG_LIST_WITH_IFRAME, array('preAct' => 'preActList', 'postAct' => 'postActList'));		// IFRAME画面付き(タブ切り替え)の設定一覧(基本)
			
			// テンプレートに非表示INPUTタグ追加
			$this->_addHiddenTag('contentid', '{CONTENT_ID}');
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
//		$task = $request->trimValueOf('task');
//		if ($task == 'list'){		// 一覧画面
//			return 'admin_list.tmpl.html';
//		} else {			// 一覧画面
			return 'admin.tmpl.html';
//		}
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
//		$task = $request->trimValueOf('task');
//		if ($task == 'list'){		// 一覧画面
//			return $this->createList($request);
//		} else {			// 詳細設定画面
			return $this->createDetail($request);
//		}
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
		// 表示画面を決定
		$task = $request->trimValueOf(M3_REQUEST_PARAM_OPERATION_TASK);

		// パンくずリストの作成
		// ダミーで作成。タイトルはJavascript側で設定。
		$titles = array();
		$titles[] = '設定なし';
		$this->gPage->setAdminBreadcrumbDef($titles);
		
		// メニューバーの作成
		$navbarDef = new stdClass;
		$navbarDef->title = $this->gEnv->getCurrentWidgetTitle();		// ウィジェット名
		$navbarDef->baseurl = $this->getAdminUrlWithOptionParam();
		$navbarDef->help	= $this->_createWidgetInfoHelp();		// ウィジェットの説明用ヘルプ// ヘルプ文字列
		$navbarDef->menu =	array(
								(Object)array(
									'name'		=> 'コンテンツ',	// コンテンツ
									'task'		=> '',
									'url'		=> '',
									'tagid'		=> 'menubar_other',
									'active'	=> false,
									'submenu'	=> array()
								),
								(Object)array(
									'name'		=> '基本',		// 基本
									'task'		=> '',
									'url'		=> '',
									'tagid'		=> 'menubar_basic',
									'active'	=> (
//														$task == '' ||						// 基本設定
														$task == self::TASK_LIST			// 設定一覧
													),
									'submenu'	=> array()
								)
							);
		$this->gPage->setAdminSubNavbarDef($navbarDef);
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
				$newObj->contentId = $this->contentId;		// コンテンツID
				$newObj->showReadMore	= $showReadMore;		// 「続きを読む」ボタンを表示
				$newObj->readMoreTitle	= $readMoreTitle;		// 「続きを読む」ボタンタイトル
				
				// コンテンツ情報を付加して設定値を追加
				$ret = $this->addPageDefParamWithContent($defSerial, $defConfigId, $this->paramObj, $newObj, M3_VIEW_TYPE_CONTENT/*コンテンツタイプ*/, $this->contentId/*コンテンツID*/);
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
				
				// コンテンツ情報を付加して設定値を更新
				if ($ret) $ret = $this->updatePageDefParamWithContent($defSerial, $defConfigId, $this->paramObj, $this->configId, $targetObj, M3_VIEW_TYPE_CONTENT/*コンテンツタイプ*/, $this->contentId/*コンテンツID*/);
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
			$this->menuHtml  = '<select name="contentid" class="form-control">';
	        $this->menuHtml .= '<option value="0">-- 未選択 --</option>';
			$this->db->getAllContentItems(array($this, 'itemListLoop'), $this->langId);
			$this->menuHtml .= '</select>';
			
			$this->gInstance->getAjaxManager()->addData('menu_html', $this->menuHtml);
		} else if ($act == 'select'){	// 定義IDを変更
			$replaceNew = true;			// データ再取得
		}
		
		// 表示用データを取得
		if (empty($this->configId)){		// 新規登録の場合
			$this->tmpl->setAttribute('item_name_visible', 'visibility', 'visible');// 名前入力フィールド表示
			if ($replaceNew){		// データ再取得時
				//$name = $this->createDefaultName();			// デフォルト登録項目名
				$name = $this->createConfigDefaultName();			// デフォルト登録項目名
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

		// 定義選択メニュー作成
		$this->createDefListMenu();
		
		// コンテンツ項目リストをデフォルト言語で取得
		$this->db->getAllContentItems(array($this, 'itemListLoop'), $this->langId);
		
		// 一度設定を保存している場合は、メニュー定義を前面にする(初期起動時のみ)
		$activeIndex = 0;
/*		if (empty($act) && !empty($this->configId)) $activeIndex = 1;
		// 一覧画面からの戻り画面が指定されてる場合は優先する
		if ($anchor == 'widget_config') $activeIndex = 0;
		*/
		if (empty($activeIndex)){		// タブの選択
			$this->tmpl->addVar("_widget", "active_tab", 'widget_config');
		} else {
			$this->tmpl->addVar("_widget", "active_tab", 'edit_content');		// コンテンツ編集画面
		}

		// ### 入力値を再設定 ###
		$this->tmpl->addVar("item_name_visible", "name", $name);		// 名前
		$this->tmpl->addVar("_widget", "show_read_more", $this->convertToCheckedString($showReadMore));	// 「続きを読む」ボタンを表示
		$this->tmpl->addVar("_widget", "read_more_title", $this->convertToDispString($readMoreTitle));		// 「続きを読む」ボタンタイトル
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
	 * 定義選択用メニュー
	 *
	 * @return なし						
	 */
	function createDefListMenu()
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
	 * 組み込みテンプレート処理での一覧画面作成(ACT前処理)
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param								なし
	 */
	function preActList($request)
	{
	}
	/**
	 * 組み込みテンプレート処理での一覧画面作成(ACT後処理)
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param								なし
	 */
	function postActList($request)
	{
		// 詳細画面からの引継ぎデータ
		$contentId = $request->trimValueOf('contentid');
		
		$editUrl = $this->gEnv->getDefaultAdminUrl() . '?cmd=configwidget&openby=tabs&widget=' . self::CONTENT_WIDGET_ID . '&task=content_detail&contentid=' . $contentId;
		$this->tmpl->addVar("_widget", "url", $this->getUrl($editUrl));
		$this->tmpl->addVar("_widget", "content_id", $contentId);
		$this->tmpl->addVar("_widget", "default_tab", 'tab_main');		// デフォルトタブ
		$this->tmpl->addVar("_widget", "other_task_name", self::OTHER_TASK_NAME);
	}
}
?>
