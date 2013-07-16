<?php
/**
 * コンテナクラス
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    ユーザ作成コンテンツ
 * @author     株式会社 毎日メディアサービス
 * @copyright  Copyright 2010 株式会社 毎日メディアサービス.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: admin_user_contentRoomWidgetContainer.php 3026 2010-04-13 05:14:43Z fishbone $
 * @link       http://www.m-media.co.jp
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() . '/admin_user_contentBaseWidgetContainer.php');

class admin_user_contentRoomWidgetContainer extends admin_user_contentBaseWidgetContainer
{
	private $langId;		// 言語ID
	private $serialNo;		// 選択中の項目のシリアル番号
	private $serialArray = array();		// 表示されている項目シリアル番号
	private $categoryArray = array();	// 表示されている所属カテゴリのID
	private $categoryValues = array();	// 選択されているカテゴリの項目値
	const CONTENT_TYPE = 'uc';		// 参照数カウント用
	
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
		if ($task == 'room_detail'){		// 詳細画面
			return 'admin_room_detail.tmpl.html';
		} else {			// 一覧画面
			return 'admin_room.tmpl.html';
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
		if ($task == 'room_detail'){	// 詳細画面
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
		$this->langId = $this->gEnv->getDefaultLanguage();		// デフォルト言語
		$act = $request->trimValueOf('act');
		
		if ($act == 'delete'){		// 項目削除の場合
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
				// ルームIDを取得
				$roomArray = array();
				for ($i = 0; $i < count($delItems); $i++){
					// シリアル番号からデータを取得
					$ret = $this->_localDb->getRoomBySerial($delItems[$i], $row);
					if ($ret) $roomArray[] = $row['ur_id'];			// ルーム識別ID
				}
				
				// ルームを削除
				$ret = $this->_localDb->delRoom($delItems);
				
				// 削除するルームに対応したカテゴリを削除
				if ($ret){
					for ($i = 0; $i < count($roomArray); $i++){
						$ret = $this->_localDb->delRoomCategory($roomArray[$i]);
					}
				}
				
				if ($ret){		// データ削除成功のとき
					$this->setGuidanceMsg('データを削除しました');
				} else {
					$this->setAppErrorMsg('データ削除に失敗しました');
				}
			}
		}
		
		// 一覧作成
		$this->_localDb->getAllRooms(array($this, 'itemLoop'));
		
		if (count($this->serialArray) > 0){
			$this->tmpl->addVar("_widget", "serial_list", implode($this->serialArray, ','));// 表示項目のシリアル番号を設定
		} else {
			// 項目がないときは、一覧を表示しない
			$this->tmpl->setAttribute('itemlist', 'visibility', 'hidden');
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
		$this->langId = $this->gEnv->getDefaultLanguage();		// デフォルト言語
		
		$act = $request->trimValueOf('act');
		$this->serialNo = $request->trimValueOf('serial');		// 選択項目のシリアル番号
		$name	= $request->trimValueOf('item_name');	// 名前
		$id		= $request->trimValueOf('item_id');	// ルーム識別ID
		$groupId	= $request->trimValueOf('item_group_id');	// 所属グループID
		$visible = ($request->trimValueOf('item_visible') == 'on') ? 1 : 0;		// 表示するかどうか
		$categoryList = $request->trimValueOf('category_list');	// カテゴリIDのリスト
		if (!empty($categoryList)) $this->categoryArray = explode(',', $categoryList);
		
		// カテゴリーの選択項目値を取得
		for ($i = 0; $i < count($this->categoryArray); $i++){
			$itemName = 'item_category' . $i;
			$itemValue = $request->trimValueOf($itemName);
			$categoryId = $this->categoryArray[$i];
			
			// 空以外の値を取得
			if (!empty($itemValue)) $this->categoryValues[$categoryId] = $itemValue;
		}
		
		$replaceNew = false;		// データを再取得するかどうか
		if ($act == 'add'){		// 新規追加のとき
			// 入力チェック
			//$this->checkInput($name, '表示名');
			$this->checkSingleByte($id, '識別ID');
			$this->checkNumeric($groupId, '所属グループID');		// 所属グループID
			
			// 同じIDがある場合はエラー
			if ($this->_localDb->getRoomById($id, $row)) $this->setMsg(self::MSG_USER_ERR, '識別IDが重複しています');
			
			// エラーなしの場合は、データを更新
			if ($this->getMsgCount() == 0){
				$ret = $this->_localDb->updateRoom(0/*新規*/, $id, $name, $groupId, $visible, $newSerial);
				
				// 選択カテゴリを更新
				if ($ret) $ret = $this->_localDb->updateRoomCategory($id, $this->categoryValues);
				
				if ($ret){		// データ追加成功のとき
					$this->setMsg(self::MSG_GUIDANCE, 'データを追加しました');
					
					$this->serialNo = $newSerial;		// シリアル番号を更新
					$replaceNew = true;			// データを再取得
				} else {
					$this->setMsg(self::MSG_APP_ERR, 'データ追加に失敗しました');
				}
			}
		} else if ($act == 'update'){		// 行更新のとき
			// 入力チェック
			//$this->checkInput($name, '表示名');
			$this->checkNumeric($groupId, '所属グループID');		// 所属グループID
			
			// エラーなしの場合は、データを更新
			if ($this->getMsgCount() == 0){
				$ret = $this->_localDb->updateRoom($this->serialNo, $id, $name, $groupId, $visible, $newSerial);
				
				// 選択カテゴリを更新
				if ($ret) $ret = $this->_localDb->updateRoomCategory($id, $this->categoryValues);
				
				if ($ret){		// データ追加成功のとき
					$this->setMsg(self::MSG_GUIDANCE, 'データを更新しました');
				
					$this->serialNo = $newSerial;		// シリアル番号を更新
					$replaceNew = true;			// データを再取得
				} else {
					$this->setMsg(self::MSG_APP_ERR, 'データ更新に失敗しました');
				}
			}
		} else {		// 初期状態
			// シリアル番号からデータを取得
			$ret = $this->_localDb->getRoomBySerial($this->serialNo, $row);
			if ($ret) $id = $row['ur_id'];			// ルーム識別ID
			
			if (empty($id)){		// タブ識別IDが空のときは新規とする
				$this->serialNo = 0;
				$id		= '';		// 識別ID
				$name	= '';	// 名前
				$groupId = 0;	// 所属グループID
				$visible	= 1;		// 公開
			} else {
				$replaceNew = true;			// データを再取得
			}
		}
		// 表示データ再取得
		if ($replaceNew){
			// タブ識別IDからデータを取得
			$ret = $this->_localDb->getRoomById($id, $row);
			if ($ret){
				$this->serialNo = $row['ur_serial'];
				$name		= $row['ur_name'];
				$groupId	= $row['ur_group_id'];		// 所属グループID
				$visible	= $row['ur_visible'];		// 公開
				
				// 選択カテゴリを取得
				$ret = $this->_localDb->getRoomCategory($id, $rows);
				if ($ret){
					$this->categoryValues = array();
					for ($i = 0; $i < count($rows); $i++){
						$key = $rows[$i][um_category_id];
						$value = $rows[$i][um_category_item_id];
						$this->categoryValues[$key] = $value;
					}
				}
			}
		}
		
		// カテゴリメニュー作成
		$this->categoryArray = array();		// 表示カテゴリを一旦初期化
		$this->_localDb->getAllCategoryForMenu($this->langId, array($this, 'menuLoop'));
		
		// 項目がないときは、カテゴリメニューを表示しない
		if (empty($this->categoryArray)){
			$this->tmpl->setAttribute('category', 'visibility', 'hidden');
		} else {	// カテゴリが設定されているとき
			$this->tmpl->addVar("_widget", "category_list", implode($this->categoryArray, ','));		// 表示中のカテゴリIDのリスト
		}
		
		if (empty($this->serialNo)){		// シリアル番号が空のときは新規とする
			$this->tmpl->setAttribute('add_button', 'visibility', 'visible');// 新規登録ボタン表示
			$this->tmpl->setAttribute('new_id_field', 'visibility', 'visible');// 新規ID入力フィールド表示
			
			$this->tmpl->addVar("new_id_field", "id", $id);		// 識別キー
		} else {
			$this->tmpl->setAttribute('update_button', 'visibility', 'visible');// 更新ボタン表示
			$this->tmpl->setAttribute('id_field', 'visibility', 'visible');// 固定IDフィールド表示
			
			$this->tmpl->addVar("id_field", "id", $id);		// 識別キー
		}
		
		// 画面にデータを埋め込む
		$this->tmpl->addVar("_widget", "name", $name);		// 名前
		$this->tmpl->addVar("_widget", "group_id", $groupId);		// 所属グループID
		
		// 項目表示、項目利用可否チェックボックス
		$checked = '';
		if ($visible) $checked = 'checked';
		$this->tmpl->addVar("_widget", "visible", $checked);
		
		// 選択中のシリアル番号を設定
		$this->tmpl->addVar("_widget", "serial", $this->serialNo);
	}
	/**
	 * 取得したルーム情報をテンプレートに設定する
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function itemLoop($index, $fetchedRow, $param)
	{
		$id = $fetchedRow['ur_id'];		// ルームID
		$visible = '';
		if ($fetchedRow['ur_visible']){	// 項目の表示
			$visible = 'checked';
		}
		
		// 総参照数
		$contentId = $id;		// コンテンツID
		$totalViewCount = $this->gInstance->getAnalyzeManager()->getTotalContentViewCount(self::CONTENT_TYPE, 0/*コンテンツID指定*/, $contentId);
		
		$row = array(
			'index' => $index,
			'serial' => $fetchedRow['ur_serial'],
			'id' =>	$this->convertToDispString($id),		// 識別ID
			'name'     => $this->convertToDispString($fetchedRow['ur_name']),			// 表示名
			'group'     => $this->convertToDispString($fetchedRow['ur_group_id']),			// 所属グループID
			'visible'	=> $visible,					// 公開状況
			'view_count' => $totalViewCount									// 総参照数
		);
		$this->tmpl->addVars('itemlist', $row);
		$this->tmpl->parseTemplate('itemlist', 'a');
		
		// 表示中項目のシリアル番号を保存
		$this->serialArray[] = $fetchedRow['ur_serial'];
		return true;
	}
	/**
	 * 取得したタブ定義をテンプレートに設定する
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function menuLoop($index, $fetchedRow, $param)
	{
		static $categoryIndex = 0;	// カテゴリ表示順
		static $isFirst = true;		// 最初の項目かどうか
		
		$id = $fetchedRow['ua_id'];
		$itemId = $fetchedRow['ua_item_id'];
		if (empty($itemId)){		// カテゴリタイトルのとき
			$itemRow = array(		
				'index'		=> $categoryIndex,			// 項目番号
				'title'		=> $this->convertToDispString($fetchedRow['ua_name'])			// 表示名										
			);
			$this->tmpl->addVars('category', $itemRow);
			$this->tmpl->parseTemplate('category', 'a');
			
			$categoryIndex++;		// カテゴリ表示順を更新
			$isFirst = true;		// 最初の項目かどうか
			$this->categoryArray[] = $fetchedRow['ua_id'];// カテゴリIDを保存
		} else {
			if ($isFirst){		// 最初の項目のとき
				$this->tmpl->clearTemplate('category_list');
				$isFirst = false;
			}
			// カテゴリの選択状況を取得
			$selected = '';
			if ($this->categoryValues[$id] == $itemId) $selected = 'selected';
			
			$menurow = array(
				'value'		=> $this->convertToDispString($itemId),			// カテゴリー項目ID
				'name'		=> $this->convertToDispString($fetchedRow['ua_name']),			// カテゴリー項目名
				'selected'	=> $selected													// 選択中かどうか
			);
			$this->tmpl->addVars('category_list', $menurow);
			$this->tmpl->parseTemplate('category_list', 'a');
		}
		return true;
	}
}
?>
