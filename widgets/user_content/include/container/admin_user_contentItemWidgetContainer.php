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
 * @version    SVN: $Id: admin_user_contentItemWidgetContainer.php 4605 2012-01-20 01:12:33Z fishbone $
 * @link       http://www.m-media.co.jp
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() . '/admin_user_contentBaseWidgetContainer.php');

class admin_user_contentItemWidgetContainer extends admin_user_contentBaseWidgetContainer
{
	private $langId;		// 言語ID
	private $serialNo;		// 選択中の項目のシリアル番号
	private $serialArray = array();		// 表示されている項目シリアル番号
	
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
		if ($task == 'item_detail'){		// 詳細画面
			return 'admin_item_detail.tmpl.html';
		} else {			// 一覧画面
			return 'admin_item.tmpl.html';
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
		if ($task == 'item_detail'){	// 詳細画面
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
				// 識別IDが組み込みのIDでないかチェック
				for ($i = 0; $i < count($delItems); $i++){
					$ret = $this->_localDb->getItemBySerial($delItems[$i], $row);
					if (!ret){
						$this->setAppErrorMsg('データ削除に失敗しました');
						break;
					}
					if (strtoupper($row['ui_id']) == $row['ui_id']){			// コンテンツ項目識別ID
						$this->setMsg(self::MSG_USER_ERR, '削除できない項目が選択されています(' . $this->convertToDispString($row['ui_name']) . ')');
						break;
					}
				}
				if ($i == count($delItems)){		// エラーなしの場合
					$ret = $this->_localDb->delItem($delItems);
					if ($ret){		// データ削除成功のとき
						$this->setGuidanceMsg('データを削除しました');
					} else {
						$this->setAppErrorMsg('データ削除に失敗しました');
					}
				}
			}
		}
		
		// 一覧作成
		$this->_localDb->getAllItems(array($this, 'itemLoop'));
		
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
		$desc	= $request->trimValueOf('item_desc');	// 説明
		$id		= $request->trimValueOf('item_id');	// 識別ID
		$this->type	= $request->trimValueOf('item_type');	// 項目タイプ
		$key		= $request->trimValueOf('item_key');	// 外部参照キー
		
		$replaceNew = false;		// データを再取得するかどうか
		if ($act == 'add'){		// 新規追加のとき
			// 入力チェック
			$this->checkInput($name, '表示名');
			//$this->checkSingleByte($id, '識別ID');
			$this->checkSingleByte($id, '識別ID', false, 1/*英小文字に制限*/);
			$this->checkNumeric($this->type, '項目タイプ');
			
			// 同じIDがある場合はエラー
			if ($this->_localDb->getItemById($id, $row)) $this->setMsg(self::MSG_USER_ERR, '識別IDが重複しています');
			
			// エラーなしの場合は、データを更新
			if ($this->getMsgCount() == 0){
				$ret = $this->_localDb->updateItem(0/*新規*/, $id, $name, $desc, $this->type, $key, $newSerial);
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
			$this->checkInput($name, '表示名');
			$this->checkNumeric($this->type, '項目タイプ');
			
			// エラーなしの場合は、データを更新
			if ($this->getMsgCount() == 0){
				$ret = $this->_localDb->updateItem($this->serialNo, $id, $name, $desc, $this->type, $key, $newSerial);
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
			$ret = $this->_localDb->getItemBySerial($this->serialNo, $row);
			if ($ret) $id = $row['ui_id'];			// コンテンツ項目識別ID
			
			if (empty($id)){		// タブ識別IDが空のときは新規とする
				$this->serialNo = 0;
				$id		= '';		// 識別ID
				$name	= '';	// 名前
				$desc	= '';	// 説明
				$this->type	= 0;	// 項目タイプ
				$key		= '';	// 外部参照キー
			} else {
				$replaceNew = true;			// データを再取得
			}
		}
		// 表示データ再取得
		if ($replaceNew){
			// タブ識別IDからデータを取得
			$ret = $this->_localDb->getItemById($id, $row);
			if ($ret){
				$this->serialNo = $row['ui_serial'];
				$name			= $row['ui_name'];
				$desc			= $row['ui_description'];	// 説明
				$this->type		= $row['ui_type'];		// 項目タイプ
				$key			= $row['ui_key'];	// 外部参照キー
			}
		}

		// コンテンツ項目タイプメニュー
		$this->createItemTypeMenu();
		
		if (empty($this->serialNo)){		// シリアル番号が空のときは新規とする
			$this->tmpl->setAttribute('add_button', 'visibility', 'visible');// 新規登録ボタン表示
			$this->tmpl->setAttribute('new_id_field', 'visibility', 'visible');// 新規ID入力フィールド表示
			
			$this->tmpl->addVar("new_id_field", "id", $id);		// 識別キー
		} else {
			if (strtoupper($id) != $id) $this->tmpl->setAttribute('update_button', 'visibility', 'visible');			// 更新ボタン表示。組み込み識別IDのときは更新不可。
			$this->tmpl->setAttribute('id_field', 'visibility', 'visible');// 固定IDフィールド表示
			
			$this->tmpl->addVar("id_field", "id", $id);		// 識別キー
		}
		
		// 画面にデータを埋め込む
		$this->tmpl->addVar("_widget", "name", $name);		// 名前
		$this->tmpl->addVar("_widget", "desc", $desc);		// 説明
		$this->tmpl->addVar("_widget", "key", $key);		// 外部参照キー
		
		// 選択中のシリアル番号を設定
		$this->tmpl->addVar("_widget", "serial", $this->serialNo);
	}
	/**
	 * 取得したタブ定義をテンプレートに設定する
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function itemLoop($index, $fetchedRow, $param)
	{
		// コンテンツ項目のデータタイプ
		$typeStr = '';
		for ($i = 0; $i < count($this->_itemTypeArray); $i++){
			if ($this->_itemTypeArray[$i]['value'] == $fetchedRow['ui_type']){
				$typeStr = $this->_itemTypeArray[$i]['name'];
				break;
			}
		}
		// 埋め込みタグ
		$keyTag = M3_TAG_START . M3_TAG_MACRO_ITEM_KEY . $fetchedRow['ui_id'] . M3_TAG_END;
		
		$row = array(
			'index' => $index,
			'serial' => $fetchedRow['ui_serial'],
			'id' =>	$this->convertToDispString($fetchedRow['ui_id']),		// 識別ID
			'name'     => $this->convertToDispString($fetchedRow['ui_name']),			// 表示名
			'type'	=> $this->convertToDispString($typeStr),				// データタイプ
			'tag'	=> $keyTag,					// 埋め込みタグ
			'desc'	=> $this->convertToDispString($fetchedRow['ui_description'])				// 説明
		);
		$this->tmpl->addVars('itemlist', $row);
		$this->tmpl->parseTemplate('itemlist', 'a');
		
		// 表示中項目のシリアル番号を保存
		$this->serialArray[] = $fetchedRow['ui_serial'];
		return true;
	}
	/**
	 * コンテンツ項目タイプ選択メニュー作成
	 *
	 * @return なし
	 */
	function createItemTypeMenu()
	{
		for ($i = 0; $i < count($this->_itemTypeArray); $i++){
			$value = $this->_itemTypeArray[$i]['value'];
			$name = $this->_itemTypeArray[$i]['name'];
			
			$selected = '';
			if ($value == $this->type) $selected = 'selected';
			
			$row = array(
				'value'    => $value,			// ページID
				'name'     => $name,			// ページ名
				'selected' => $selected														// 選択中かどうか
			);
			$this->tmpl->addVars('item_type_list', $row);
			$this->tmpl->parseTemplate('item_type_list', 'a');
		}
	}
}
?>
