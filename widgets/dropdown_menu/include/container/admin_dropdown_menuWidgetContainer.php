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
 * @version    SVN: $Id: admin_dropdown_menuWidgetContainer.php 4945 2012-06-08 01:35:42Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseAdminWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/dropdown_menuDb.php');

class admin_dropdown_menuWidgetContainer extends BaseAdminWidgetContainer
{
	private $db;	// DB接続オブジェクト
	private $serialNo;		// 選択中の項目のシリアル番号
	private $serialArray = array();			// 表示中のシリアル番号
	private $langId;
	private $configId;		// 定義ID
	private $paramObj;		// パラメータ保存用オブジェクト
	private $menuId;		// メニューID
	private $menuType;		// メニュータイプ
	private $css;			// メニュー用CSS
	private $cssId;			// CSS用ID
	const DEFAULT_NAME_HEAD = '名称未設定';			// デフォルトの設定名
	const DEFAULT_MENU_ID = 'main_menu';			// デフォルトメニューID
	const MAX_MENU_TREE_LEVEL = 5;			// メニュー階層最大数
	const DEFAULT_MENU_CLASS = 'sf-menu';	// メニューのクラス名
	const VERTICAL_MENU_CLASS = 'sf-vertical';		// 縦型用メニュークラス名
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->db = new dropdown_menuDb();
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
		$name	= $request->trimValueOf('item_name');			// ヘッダタイトル
		$showTitle = ($request->trimValueOf('item_showtitle') == 'on') ? 1 : 0;		// タイトルを表示するかどうか
		$this->menuType = $request->trimValueOf('item_menu_type');		// メニュータイプ
		$this->menuId = $request->trimValueOf('item_menuid');
		if ($this->menuId == '') $this->menuId = self::DEFAULT_MENU_ID;
		$this->css	= $request->trimValueOf('item_css');		// メニュー用CSS
		$this->cssId	= $request->trimValueOf('item_css_id');		// CSS用ID
		
		$replaceNew = false;		// データを再取得するかどうか
		if (empty($act)){// 初期起動時
			// デフォルト値設定
			$this->configId = $defConfigId;		// 呼び出しウィンドウから引き継いだ定義ID
			$replaceNew = true;			// データ再取得
		} else if ($act == 'add'){// 新規追加
			// 入力チェック
			$this->checkInput($name, '名前');
			
			// エラーなしの場合は、データを登録
			if ($this->getMsgCount() == 0){
				// 追加オブジェクト作成
				$newObj = new stdClass;
				$newObj->menuId	= $this->menuId;		// メニューID
				$newObj->name	= $name;// 表示名
				$newObj->showTitle = $showTitle;		// タイトルを表示するかどうか
				$newObj->menuType = $this->menuType;			// メニュータイプ
				$newObj->cssId	= $this->cssId;					// CSS用ID
				$newObj->css	= $this->css;					// メニューCSS
				
				$ret = $this->addPageDefParam($defSerial, $defConfigId, $this->paramObj, $newObj, $this->menuId);
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
					$targetObj->showTitle = $showTitle;					// タイトルを表示するかどうか
					//$targetObj->menuType = $this->menuType;			// メニュータイプは更新しない
					$targetObj->cssId	= $this->cssId;					// CSS用ID
					$targetObj->css		= $this->css;					// メニューCSS
					
					// 取得値で更新
					$this->menuId = $targetObj->menuId;		// メニューID
				}
				
				// 設定値を更新
				if ($ret) $ret = $this->updatePageDefParam($defSerial, $defConfigId, $this->paramObj, $this->configId, $targetObj, $this->menuId);
				if ($ret){
					$this->setMsg(self::MSG_GUIDANCE, 'データを更新しました');
					$replaceNew = true;			// データ再取得
				} else {
					$this->setMsg(self::MSG_APP_ERR, 'データ更新に失敗しました');
				}
			}
		} else if ($act == 'select' || $act == 'reload'){	// 定義IDを変更、データ再取得
			$replaceNew = true;			// データ再取得
		} else if ($act == 'select_menutype'){		// メニュータイプ変更
		}
		// 設定項目選択メニュー作成
		$this->createItemMenu();
		
		// メニューID選択メニュー作成
		$this->db->getMenuIdList(array($this, 'menuIdListLoop'));
		
		// プレビューメニュー作成
		$previewHtml = $this->createMenu($this->menuId, 0);
				
		// 表示用データを取得
		if (empty($this->configId)){		// 新規登録の場合
			$this->tmpl->setAttribute('item_name_visible', 'visibility', 'visible');// 名前入力フィールド表示
			$name = $this->createDefaultName();			// デフォルト登録項目名
			$this->cssId = $this->createDefaultCssId();	// CSS用ID
			$showTitle = 1;					// タイトルを表示するかどうか
			
			// デフォルト用のCSSを取得
			$this->css = $this->getParsedTemplateData('default.tmpl.css', array($this, 'makeCss'));
			$this->serialNo = 0;
		} else {
			if ($replaceNew){
				$ret = $this->getPageDefParam($defSerial, $defConfigId, $this->paramObj, $this->configId, $targetObj);
				if ($ret){
					$this->menuId	= $targetObj->menuId;		// メニューID
					$name			= $targetObj->name;// 名前
					$showTitle 		= $targetObj->showTitle;					// タイトルを表示するかどうか
					$this->menuType = $targetObj->menuType;			// メニュータイプ
					$this->cssId	= $targetObj->cssId;					// CSS用ID
					$this->css		= $targetObj->css;					// メニューCSS
				}
			}
			$this->serialNo = $this->configId;
			
			// メニュータイプの変更を不可にする
			$this->tmpl->addVar("_widget", "horiz_disabled", 'disabled');
			$this->tmpl->addVar("_widget", "vert_disabled", 'disabled');
				
			// 新規作成でないときは、メニューを変更不可にする(画面作成から呼ばれている場合のみ)
			if (!empty($defConfigId) && !empty($defSerial)) $this->tmpl->addVar("_widget", "id_disabled", 'disabled');
		}
		// プレビュー用のCSSを作成
		$this->headCss = str_replace(M3_TAG_START . M3_TAG_MACRO_WIDGET_URL . M3_TAG_END, $this->gEnv->getCurrentWidgetRootUrl(), $this->css);
		
		// 画面にデータを埋め込む
		$this->tmpl->addVar("item_name_visible", "name", $name);		// 名前
		if (!empty($this->configId)) $this->tmpl->addVar("_widget", "id", $this->configId);		// 定義ID
		$checked = '';
		if ($showTitle) $checked = 'checked';
		$this->tmpl->addVar("_widget", "show_title", $checked);	// タイトルを表示するかどうか
		
		// メニュークラスの設定
		if (empty($this->menuType)){			// メニュータイプ(横型)
			$this->tmpl->addVar("_widget", "horiz_checked", 'checked');
			$this->tmpl->addVar("_widget", "css_class",	self::DEFAULT_MENU_CLASS);
		} else {
			$this->tmpl->addVar("_widget", "vert_checked", 'checked');
			$this->tmpl->addVar("_widget", "css_class",	self::DEFAULT_MENU_CLASS . ' ' . self::VERTICAL_MENU_CLASS);
		}
		$this->tmpl->addVar("_widget", "css_id",	$this->cssId);	// CSS用ID
		$this->tmpl->addVar("_widget", "css",	$this->css);
		$this->tmpl->addVar("_widget", "preview",	$previewHtml);
		
		$this->tmpl->addVar("_widget", "serial", $this->serialNo);// 選択中のシリアル番号、IDを設定
		
		// ボタンの表示制御
		if (empty($this->serialNo)){		// 新規追加項目を選択しているとき
			$this->tmpl->setAttribute('add_button', 'visibility', 'visible');// 「新規追加」ボタン
		} else {
			$this->tmpl->setAttribute('update_button', 'visibility', 'visible');// 「更新」ボタン
			
			// ヘルプの追加
			$this->convertHelp('update_button');
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
		return $this->headCss;
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
	 * CSS用のデフォルトのIDを取得
	 *
	 * @return string	ID						
	 */
	function createDefaultCssId()
	{
		return $this->gEnv->getCurrentWidgetId() . '_' . $this->getTempConfigId($this->paramObj);
	}
	/**
	 * 取得したデータをテンプレートに設定する
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function menuIdListLoop($index, $fetchedRow, $param)
	{
		$value = $fetchedRow['mn_id'];
		$name = $fetchedRow['mn_name'];
			
		$selected = '';
		if ($value == $this->menuId) $selected = 'selected';
		
		$row = array(
			'value'    => $value,			// ページID
			'name'     => $name,			// ページ名
			'selected' => $selected														// 選択中かどうか
		);
		$this->tmpl->addVars('menu_id_list', $row);
		$this->tmpl->parseTemplate('menu_id_list', 'a');
		return true;
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
			
			// メニュー定義名を取得
			$menuName = '';
			if ($this->db->getMenu($targetObj->menuId, $row)){
				$menuName = $row['mn_name'];
			}
			
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
				'menu_name' => $this->convertToDispString($menuName),		// メニュー定義名
				'def_count' => $defCount							// 使用数
			);
			$this->tmpl->addVars('itemlist', $row);
			$this->tmpl->parseTemplate('itemlist', 'a');
			
			// シリアル番号を保存
			$this->serialArray[] = $id;
		}
	}
	/**
	 * メニューツリー作成
	 *
	 * @param string	$menuId		メニューID
	 * @param int		$parantId	親メニュー項目ID
	 * @param int		$level		階層数
	 * @return string		ツリーメニュータグ
	 */
	function createMenu($menuId, $parantId, $level = 0)
	{
		// メニューの階層を制限
		if ($level >= self::MAX_MENU_TREE_LEVEL) return '';
		
		$treeHtml = '';
		if ($this->db->getChildMenuItems($menuId, $parantId, $rows)){
			$itemCount = count($rows);
			for ($i = 0; $i < $itemCount; $i++){
				$row = $rows[$i];
				
				// 非表示のときは処理を飛ばす
				if (!$row['md_visible']) continue;

				// リンク先の作成
				$linkUrl = $row['md_link_url'];
				$linkUrl = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $this->gEnv->getRootUrl(), $linkUrl);
				if (empty($linkUrl)) $linkUrl = '#';
				$linkUrl = $this->convertUrlToHtmlEntity($linkUrl);
				
				// リンクタイプに合わせてタグを生成
				$option = '';
				switch ($row['md_link_type']){
					case 0:			// 同ウィンドウで開くリンク
						break;
					case 1:			// 別ウィンドウで開くリンク
						$option = 'target="_blank"';
						break;
				}
				// メニュー項目を作成
				$name = $this->getCurrentLangString($row['md_name']);
				if (empty($name)) continue;
				
				// ##### ツリーメニュー作成 #####
				if ($row['md_type'] == 0){	// リンク項目のとき
					$treeHtml .= '<li><a href="' . $linkUrl . '" ' . $option . '>' . $this->convertToDispString($name) . '</a></li>' . M3_NL;
				} else if ($row['md_type'] == 1){			// フォルダのとき
					// サブメニュー作成
					$treeHtml .= '<li><a class="head" href="#">' . $this->convertToDispString($name) . '</a>' . M3_NL;
					$treeHtml .= '<ul>' . M3_NL;
					$treeHtml .= $this->createMenu($menuId, $row['md_id'], $level + 1);
					$treeHtml .= '</ul>' . M3_NL;
					$treeHtml .= '</li>' . M3_NL;
				} else if ($row['md_type'] == 2){			// テキストのとき
					//$treeHtml .= '<li><span>' . $this->convertToDispString($name) . '</span></li>' . M3_NL;
				} else if ($row['md_type'] == 3){			// セパレータのとき
					//$treeHtml .= '<li><span>' . '-----' . '</span></li>' . M3_NL;
				}
			}
		}
		return $treeHtml;
	}
	/**
	 * CSSデータ作成処理コールバック
	 *
	 * @param object         $tmpl			テンプレートオブジェクト
	 * @param								なし
	 */
	function makeCss($tmpl)
	{
		// メニュータイプが縦型のときは縦型用のCSSを追加
		if (!empty($this->menuType)){
			$tmpl->setAttribute('add_vertical', 'visibility', 'visible');
			$tmpl->addVar('add_vertical', 'id', '#' . $this->cssId);
		}
		$tmpl->addVar('_tmpl', 'id', '#' . $this->cssId);
	}
}
?>
