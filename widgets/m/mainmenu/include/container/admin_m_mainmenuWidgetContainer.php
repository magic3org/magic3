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
 * @copyright  Copyright 2006-2008 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: admin_m_mainmenuWidgetContainer.php 880 2008-08-06 07:40:32Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/mainmenuDb.php');

class admin_m_mainmenuWidgetContainer extends BaseWidgetContainer
{
	private $db;	// DB接続オブジェクト
	private $serialNo;		// 選択中の項目のシリアル番号
	private $serialArray = array();			// 表示中のシリアル番号
	private $currentUrl;	// 現在のURL
	private $currentPageSubId;	// 現在のページサブID
	private $currentContentId;	// 現在のコンテンツID
	const MAIN_MENU_ID = 'mobile_menu';			// メニューID
	const CONTENT_WIDGET_ID = 'm/content';			// コンテンツ編集ウィジェット
	const CONTENT_TYPE = 'mobile';					// コンテンツタイプ
	const CONTENT_WIDGET_TYPE = 'content';			// コンテンツを表示するウィジェットのタイプ
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->db = new mainmenuDb();
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
		if ($task == 'detail'){		// 詳細画面
			return 'admin_menu_detail.tmpl.html';
		} else {			// 一覧画面
			return 'admin_menu.tmpl.html';
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
		if ($task == 'detail'){	// 詳細画面
			return $this->createDetail($request);
		} else {			// 一覧画面
			return $this->createList($request);
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
		global $gEnvManager;
		global $gPageManager;
		
		$userId		= $gEnvManager->getCurrentUserId();
		$langId	= $gEnvManager->getCurrentLanguage();		// 表示言語を取得
		$act = $request->trimValueOf('act');
		
		if ($act == 'updatemenu'){		// メニュー項目順の更新のとき
			$menuitems = $request->trimValueOf('menuitems');
			if (!empty($menuitems)){
				$menuItemNoArray = explode(',', $menuitems);
			
				// メニューの並び順を変更
				$this->db->orderMenuItems(self::MAIN_MENU_ID, $langId, $menuItemNoArray);
			}
			$gPageManager->updateParentWindow();// 親ウィンドウを更新
		} else if ($act == 'delete'){		// メニュー項目の削除
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
				$ret = $this->db->delMenuItems(implode($delItems, ','));
				if ($ret){		// データ削除成功のとき
					$this->setGuidanceMsg('データを削除しました');
				} else {
					$this->setAppErrorMsg('データ削除に失敗しました');
				}
			}
			$gPageManager->updateParentWindow();// 親ウィンドウを更新
		}
		// メニュー項目を取得
		$menuId = self::MAIN_MENU_ID;
		$this->db->getAllMenuItems(array($this, 'itemListLoop'), $menuId, $langId);
		
		$this->tmpl->addVar("_widget", "serial_list", implode($this->serialArray, ','));// 表示項目のシリアル番号を設定
		$this->tmpl->addVar('_widget', 'content_widget_id', self::CONTENT_WIDGET_ID);// コンテンツ表示ウィジェット
		$this->tmpl->addVar('_widget', 'admin_url', $gEnvManager->getDefaultAdminUrl());// 管理者URL
	}
	/**
	 * 詳細画面作成
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param								なし
	 */
	function createDetail($request)
	{
		global $gEnvManager;
		
		$userId		= $gEnvManager->getCurrentUserId();
		$langId	= $gEnvManager->getCurrentLanguage();		// 表示言語を取得
		$act = $request->trimValueOf('act');
		$this->serialNo = $request->trimValueOf('serial');		// 選択項目のシリアル番号
		
		$name = $request->trimValueOf('item_name');
		$linkType = 0;			// リンク先は同ウィンドウのみ
		$inputUrl = $request->trimValueOf('item_url');		// 入力URL
		$visible = ($request->trimValueOf('item_visible') == 'on') ? 1 : 0;		// チェックボックス
		$enable = ($request->trimValueOf('item_enable') == 'on') ? 1 : 0;		// チェックボックス
		$url = $request->trimValueOf('save_url');		// 決定したURL
		$url = str_replace($gEnvManager->getRootUrl(), M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $url);// マクロ変換
		
		if ($act == 'add'){// 新規追加
			// 入力チェック
			$this->checkInput($name, '名前');

			// エラーなしの場合は、データを登録
			if ($this->getMsgCount() == 0){
				// IDを生成
				$id = $this->db->getNewMenuId();
				$ret = $this->db->addMenuItem(self::MAIN_MENU_ID, $id, $langId, $name, 0, $linkType, $url, $visible, $enable, $userId, $newSerial);
				if ($ret){
					$this->setGuidanceMsg('データを追加しました');
					$this->serialNo = $newSerial;		// シリアル番号を更新
				} else {
					$this->setAppErrorMsg('データ追加に失敗しました');
				}
			}
		} else if ($act == 'update'){		// 項目更新の場合
			// 入力チェック
			$this->checkInput($name, '名前');
			
			// エラーなしの場合は、データを更新
			if ($this->getMsgCount() == 0){
				$ret = $this->db->updateMenuItemBySerial($this->serialNo, self::MAIN_MENU_ID, $name, 0, $linkType, $url, $visible, $enable, $userId);
				if ($ret){
					$this->setGuidanceMsg('データを更新しました');
				} else {
					$this->setAppErrorMsg('データ更新に失敗しました');
				}
			}
		} else if ($act == 'delete'){		// 項目削除の場合
			$ret = $this->db->delMenuItems($this->serialNo);
			if ($ret){		// データ削除成功のとき
				$this->setGuidanceMsg('データを削除しました');
			} else {
				$this->setAppErrorMsg('データ削除に失敗しました');
			}
		} else {
			if (empty($this->serialNo)){		// 新規項目追加のとき
				$id = 0;			// ID
				$name = '';		// 名前
				$linkType = 0;	// リンクタイプ
				$visible = 1;
				$enable = 1;
				$url = '';	// リンク先			
			} else {
				$ret = $this->db->getMenuBySerial($this->serialNo, $row);
				if ($ret){
					// 取得値を設定
					$id = $row['mi_id'];			// ID
					$name = $row['mi_name'];		// 名前
					$linkType = $row['mi_link_type'];	// リンクタイプ
					$visible = $row['mi_visible'];
					$enable = $row['mi_enable'];
					$this->serialNo = $row['mi_serial'];
					$url = $row['mi_link_url'];	// リンク先
				}
			}
		}
		// システム配下のパスであるかチェック
		$pos = strpos($url, M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END);
		if ($pos === false){		// 見つからない場合
			$inSystemDir = false;
		} else {
			$inSystemDir = true;
		}
		// リンク先を実URLに変換
		$url = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $gEnvManager->getRootUrl(), $url);		// マクロ展開
		
		// URLからページサブIDとコンテンツIDを取得
		$this->currentUrl = $url;
		$this->currentPageSubId = '';	// 現在のページサブID
		$this->currentContentId = '';	// 現在のコンテンツID
		if ($inSystemDir){		// システム配下のとき
			list($tmp, $urlParam) = explode("?", $url);
			$params = explode("&", $urlParam);
			$count = count($params);
			for ($i = 0; $i < $count; $i++){
				list($key, $value) = explode('=', $params[$i]);
				if ($key == 'sub'){
					$this->currentPageSubId = $value;	// 現在のページサブID
				} else if ($key == 'contentid'){
					$this->currentContentId = $value;
				}
			}
			// ページサブIDが設定されていない場合は、ルートを選択
			if (empty($this->currentPageSubId)){
				$this->tmpl->addVar("_widget", "root_selected", 'selected');	// 任意設定のURL
			}
		} else {
			if (!empty($url)){
				$this->tmpl->addVar("_widget", "other_selected", 'selected');	// 任意設定のURL
				$this->tmpl->addVar("_widget", "input_url", $url);		// 任意設定のURL
			}
		}
		
		// ### 入力値を再設定 ###
		$this->tmpl->addVar("_widget", "sel_item_name", $name);		// 名前
		$this->tmpl->addVar("_widget", "sel_item_url", $url);		// 表示するURL
		$this->tmpl->addVar("_widget", "save_url", $url);		// URL
		$this->tmpl->addVar("_widget", "root_page", $gEnvManager->getDefaultMobileUrl());		// 携帯トップページ
		
		// 項目表示、項目利用可否チェックボックス
		$visibleStr = '';
		if ($visible){
			$visibleStr = 'checked';
		}
		$enableStr = '';
		if ($enable){
			$enableStr = 'checked';
		}
		$this->tmpl->addVar("_widget", "sel_item_visible", $visibleStr);
		$this->tmpl->addVar("_widget", "sel_item_enable", $enableStr);
		
		// 選択中のシリアル番号、IDを設定
		$this->tmpl->addVar("_widget", "serial", $this->serialNo);
		
		// リンク先設定用メニュー
		$this->db->getPageIdList(array($this, 'pageSubIdLoop'), 1);// ウィジェットサブIDメニュー作成
		$this->db->getVisibleAllContents($langId, self::CONTENT_TYPE, array($this, 'contentListLoop'));

		// ボタンの表示制御
		if (empty($this->serialNo)){		// 新規追加項目を選択しているとき
			//$this->tmpl->addVar("_widget", "title", 'メニュー項目新規');// タイトル
			$this->tmpl->setAttribute('add_button', 'visibility', 'visible');// 「新規追加」ボタン
		} else {
			//$this->tmpl->addVar("_widget", "title", 'メニュー項目更新');// タイトル
			$this->tmpl->setAttribute('del_button', 'visibility', 'visible');// 「削除」ボタン
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
		global $gEnvManager;
		global $gPageManager;
		static $rowno = 0;
		
		$visible = '';
		if ($fetchedRow['mi_visible']){
			$visible = 'checked';
		}
		$enable = '';
		if ($fetchedRow['mi_enable']){
			$enable = 'checked';
		}
		// 行カラーの設定
		$lineColor = '';
		if ($index % 2 != 0){
			$lineColor = 'class="even"';		// 偶数行
		}
		// リンクタイプ
		$linkString = '';
		switch ($fetchedRow['mi_link_type']){
			case 0:			// 同ウィンドウで開くリンク
				$linkString = '同ウィンドウ';
				break;
			case 1:			// 別ウィンドウで開くリンク
				$linkString = '別ウィンドウ';
				break;
		}
		// 項目選択のラジオボタンの状態
		$serial = $this->convertToDispString($fetchedRow['mi_serial']);
		$selected = '';
		if ($serial == $this->serialNo){
			$selected = 'checked';
		}
		
		// リンクURLからコンテンツIDを取得
		$linkUrl = $fetchedRow['mi_link_url'];
		// システム配下のパスであるかチェック
		$contentId = '';
		$pos = strpos($linkUrl, M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END);
		if ($pos === false){		// 見つからない場合
		} else {
			$params = explode("&", $linkUrl);
			$count = count($params);
			for ($i = 0; $i < $count; $i++){
				list($key, $value) = explode('=', $params[$i]);
				if ($key == 'contentid'){
					$contentId = $value;
					break;
				}
			}
		}
		// リンク先を実URLに変換
		$linkUrlStr = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $gEnvManager->getRootUrl(), $linkUrl);
		$linkUrl = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $gEnvManager->getRootUrl(), $linkUrl);		// マクロ展開
		//$linkUrlStr = '<div style="overflow:auto;width:300px"><a href="#" title="'. $linkUrl . '" onClick="showUrl(\'' . $linkUrl . '\');">' . $linkUrlStr . '</a></div>';
		$linkUrlStr = '<div style="overflow:auto;width:500px"><a href="#" onClick="showUrl(\'' . $linkUrl . '\');">' . $linkUrlStr . '</a></div>';
		
		// コンテンツを編集するためのウィジェットを取得
		$widgetId = $gPageManager->getWidgetIdByWidgetType($linkUrl, self::CONTENT_WIDGET_TYPE);
		
		// コンテンツの編集ボタンの有効状態
		$enableContentLink = '';
		if (empty($contentId)) $enableContentLink = 'disabled';
		
		$row = array(
			'line_color' => $lineColor,											// 行のカラー
			'index' => $index,													// 行番号
			'rowno' => $rowno,													// 行番号
			'serial' => $serial,			// シリアル番号
			'id' => $this->convertToDispString($fetchedRow['mi_id']),			// ID
			'name' => $this->convertToDispString($fetchedRow['mi_name']),		// 名前
			'link_type' => $linkString,											// リンクタイプ
			'link_str' => $linkUrlStr,		// リンクURL
			'edit_widget_id' => $widgetId,											// コンテンツ編集ウィジェットID
			'content_id' => $contentId,											// コンテンツID
			'enable_content' => $enableContentLink,											// コンテンツの編集ボタンの有効状態
			'update_dt' => $this->convertToDispDateTime($fetchedRow['mi_create_dt']),	// 更新日時
			'visible' => $visible,											// メニュー項目表示制御
			'enable' => $enable,												// メニュー項目利用制御
			'selected' => $selected												// 項目選択用ラジオボタン
		);
		$this->tmpl->addVars('itemlist', $row);
		$this->tmpl->parseTemplate('itemlist', 'a');
		
		// メニューのプレビュー
		if ($fetchedRow['mi_visible']){		// 表示項目のみ追加
			$this->tmpl->addVars('menuitemlist', $row);
			$this->tmpl->parseTemplate('menuitemlist', 'a');
			$rowno++;
		}
		
		// シリアル番号を保存
		$this->serialArray[] = $serial;
		return true;
	}
	/**
	 * ページID、取得したデータをテンプレートに設定する
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function pageIdLoop($index, $fetchedRow, $param)
	{
		$selected = '';
		$row = array(
			'value'    => $this->convertToDispString($fetchedRow['pg_id']),			// ページID
			'name'     => $this->convertToDispString($fetchedRow['pg_name']),			// ページ名
			'selected' => $selected														// 選択中かどうか
		);
		$this->tmpl->addVars('main_id_list', $row);
		$this->tmpl->parseTemplate('main_id_list', 'a');
		return true;
	}
	/**
	 * ページサブID、取得したデータをテンプレートに設定する
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function pageSubIdLoop($index, $fetchedRow, $param)
	{
		$selected = '';
		if ($fetchedRow['pg_id'] == $this->currentPageSubId) $selected = 'selected';	// 現在のページサブID
		$row = array(
			'value'    => $this->convertToDispString($fetchedRow['pg_id']),			// ページID
			'name'     => $this->convertToDispString($fetchedRow['pg_name']),			// ページ名
			'selected' => $selected														// 選択中かどうか
		);
		$this->tmpl->addVars('sub_id_list', $row);
		$this->tmpl->parseTemplate('sub_id_list', 'a');
		return true;
	}
	/**
	 * 取得したデータをテンプレートに設定する
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function contentListLoop($index, $fetchedRow, $param)
	{
		global $gEnvManager;

		$selected = '';
		if ($fetchedRow['cn_id'] == $this->currentContentId) $selected = 'selected';	// 現在のコンテンツID
		$row = array(
			'value'    => $this->convertToDispString($fetchedRow['cn_id']),			// ページID
			'name'     => $this->convertToDispString($fetchedRow['cn_name']),			// ページ名
			'selected' => $selected														// 選択中かどうか
		);
		$this->tmpl->addVars('content_list', $row);
		$this->tmpl->parseTemplate('content_list', 'a');
		return true;
	}
}
?>
