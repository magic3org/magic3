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
 * @copyright  Copyright 2006-2016 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() . '/admin_mainMainteBaseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/admin_mainDb.php');

class admin_mainAccesspointWidgetContainer extends admin_mainMainteBaseWidgetContainer
{
	private $db;	// DB接続オブジェクト
	private $pageId;	// ページID
	private $serialArray = array();		// 表示されている項目シリアル番号
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->db = new admin_mainDb();
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
		if ($task == 'accesspoint_detail'){		// 詳細画面
			return 'accesspoint_detail.tmpl.html';
		} else {			// 一覧画面
			return 'accesspoint.tmpl.html';
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
		if ($task == 'accesspoint_detail'){	// 詳細画面
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
		// パラメータの取得
		$task = $request->trimValueOf('task');		// 処理区分
		$act = $request->trimValueOf('act');
		
		if ($act == 'delete'){		// 項目の削除
			$listedItem = explode(',', $request->trimValueOf('seriallist'));
			$delItems = array();
			for ($i = 0; $i < count($listedItem); $i++){
				// 項目がチェックされているかを取得
				$itemName = 'item' . $i . '_selected';
				$itemValue = ($request->trimValueOf($itemName) == 'on') ? 1 : 0;
				
				if ($itemValue){		// チェック項目
					$delItems[] = $listedItem[$i];
					
					// 削除可能かチェック
					$ret = $this->db->getPageIdRecord(0/*アクセスポイント*/, $listedItem[$i], $row);
					if ($ret){
						if (!$row['pg_editable']) $this->setMsg(self::MSG_APP_ERR, 'このデータは削除不可データです。ID=' . $listedItem[$i]);
					} else {
						$this->setMsg(self::MSG_APP_ERR, '該当データが見つかりません');
					}
				}
			}
			if ($this->getMsgCount() == 0 && count($delItems) > 0){		// 削除項目ありのとき
				for ($i = 0; $i < count($delItems); $i++){
					$ret = $this->db->deletePageId(0/*アクセスポイント*/, $delItems[$i]);
					if (!$ret) break;
				}
				if ($ret){		// データ削除成功のとき
					$this->setGuidanceMsg('データを削除しました');
				} else {
					$this->setAppErrorMsg('データ削除に失敗しました');
				}
			}
		}
		
//		$this->tmpl->setAttribute('pageid_list', 'visibility', 'visible');		// ページID一覧表示
		$this->db->getPageIdList(array($this, 'pageIdLoop'), 0);
		
		$this->tmpl->addVar("_widget", "serial_list", implode($this->serialArray, ','));// 表示項目のシリアル番号を設定
	}
	/**
	 * 詳細画面作成
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param								なし
	 */
	function createDetail($request)
	{
		$act = $request->trimValueOf('act');
		$this->pageId = $request->trimValueOf('pageid');		// ページID

		$newPageId = $request->trimValueOf('item_newpageid');		// 新規ページID
		$name = $request->trimValueOf('item_name');		// 名前
		$priority = $request->trimValueOf('item_priority');		// 優先度
		$active = ($request->trimValueOf('item_active') == 'on') ? 1 : 0;		// 値が有効かどうか

		$replaceNew = false;		// データを再取得するかどうか
		if ($act == 'add'){		// 新規追加のとき
			// 入力チェック
			//$this->checkSingleByte($newPageId, 'ページID');
			$this->checkPath($newPageId, 'ページID');
			$this->checkInput($name, '名前');
			$this->checkNumeric($priority, '優先順');
			
			// 登録済みのページIDかどうかチェック
			if ($this->getMsgCount() == 0){
				if ($this->db->isExistsPageId(0/*アクセスポイント*/, $newPageId)) $this->setMsg(self::MSG_USER_ERR, 'すでに登録済みのページIDです');
			}
			// エラーなしの場合は、データを更新
			if ($this->getMsgCount() == 0){
				// ページIDの追加
				$ret = $this->db->updatePageId(0/*アクセスポイント*/, $newPageId, $name, ''/*説明*/, $priority, $active, null/*未使用*/);
				if ($ret){		// データ追加成功のとき
					$this->setMsg(self::MSG_GUIDANCE, 'データを追加しました');
					
					$this->pageId = $newPageId;		// ページID設定
					$replaceNew = true;			// データを再取得
				} else {
					$this->setMsg(self::MSG_APP_ERR, 'データの追加に失敗しました');
				}
			}
		} else if ($act == 'update'){		// 更新のとき
			// 入力チェック
			//$this->checkSingleByte($this->pageId, 'ページID');
			$this->checkPath($this->pageId, 'ページID');
			$this->checkInput($name, '名前');
			$this->checkNumeric($priority, '優先順');

			// 更新可能かチェック
			if ($this->getMsgCount() == 0){
				$ret = $this->db->getPageIdRecord(0/*アクセスポイント*/, $this->pageId, $row);
				if ($ret){
					if (!$row['pg_editable']) $this->setMsg(self::MSG_APP_ERR, 'このデータは編集不可データです');
				} else {
					$this->setMsg(self::MSG_APP_ERR, '該当データが見つかりません');
				}
			}
			
			// エラーなしの場合は、データを更新
			if ($this->getMsgCount() == 0){
				// ページIDの更新
				$ret = $this->db->updatePageId(0/*アクセスポイント*/, $this->pageId, $name, ''/*説明*/, $priority, $active, null/*未使用*/);
				if ($ret){		// データ追加成功のとき
					$this->setMsg(self::MSG_GUIDANCE, 'データを更新しました');
					$replaceNew = true;			// データを再取得
				} else {
					$this->setMsg(self::MSG_APP_ERR, 'データ更新に失敗しました');
				}
			}
		} else if ($act == 'delete'){		// 削除のとき
			// 削除可能かチェック
			$ret = $this->db->getPageIdRecord(0/*アクセスポイント*/, $this->pageId, $row);
			if ($ret){
				if (!$row['pg_editable']) $this->setMsg(self::MSG_APP_ERR, 'このデータは編集不可データです');
			} else {
				$this->setMsg(self::MSG_APP_ERR, '該当データが見つかりません');
			}
			
			// エラーなしの場合は、データを削除
			if ($this->getMsgCount() == 0){
				$ret = $this->db->deletePageId(0/*アクセスポイント*/, $this->pageId);
				if ($ret){		// データ削除成功のとき
					$this->setMsg(self::MSG_GUIDANCE, 'データを削除しました');
				} else {
					$this->setMsg(self::MSG_APP_ERR, 'データ削除に失敗しました');
				}
			}
		} else {		// 初期状態
			$replaceNew = true;			// データを再取得
		}
		// 表示データ再取得
		$editable = true;			// データの編集が可能かどうか
		if ($replaceNew){
			$ret = $this->db->getPageIdRecord(0/*アクセスポイント*/, $this->pageId, $row);
			if ($ret){
				$name = $row['pg_name'];
				$priority = $row['pg_priority'];
				$active = $row['pg_active'];
				if (!$row['pg_editable']) $editable = false;
			} else {
				$active = '1';		// デフォルトはページ公開
			}
		}
		
		if (empty($this->pageId)){		// 新規追加のとき
			$this->tmpl->setAttribute('new_pageid', 'visibility', 'visible');// ページID入力領域表示
			$this->tmpl->addVar("new_pageid", "new_pageid", $newPageId);			// ページID
			$this->tmpl->setAttribute('add_button', 'visibility', 'visible');// 追加ボタン表示
		} else if ($editable){		// 編集可能のとき
			$this->tmpl->setAttribute('update_button', 'visibility', 'visible');// 更新ボタン表示
		} else {		// 編集不可のとき
			$this->tmpl->setAttribute('update_button', 'visibility', 'visible');// 更新ボタン表示
			$this->tmpl->addVar("update_button", "update_disabled", 'disabled');		// 更新ボタン無効
			$this->tmpl->addVar("update_button", "del_disabled", 'disabled');		// 削除ボタン無効
			
			// その他の項目無効化
			$this->tmpl->addVar("_widget", "name_disabled", 'disabled');		// ページ名
			$this->tmpl->addVar("_widget", "priority_disabled", 'disabled');		// 優先度
			$this->tmpl->addVar("_widget", "active_disabled", 'disabled');		// 有効な値かどうか
		}

		$this->tmpl->addVar("_widget", "page_id", $this->pageId);			// ページID
		
		$this->tmpl->addVar("_widget", "name", $name);		// ページ名
		$this->tmpl->addVar("_widget", "priority", $priority);		// 優先度
		$checked = '';
		if ($active) $checked = 'checked';
		$this->tmpl->addVar("_widget", "active", $checked);		// 有効な値かどうか
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
		$value = $this->convertToDispString($fetchedRow['pg_id']);
		
		// 有効かどうか
		$active = '';
		if ($fetchedRow['pg_active']) $active = 'checked';
		
		$row = array(
			'index'		=> $index,			// インデックス番号
			'value'		=> $value,			// ページID
			'name'		=> $this->convertToDispString($fetchedRow['pg_name']),			// ページ名
			'path'		=> $this->convertToDispString($fetchedRow['pg_path']),			// パス
			'priority'	=> $this->convertToDispString($fetchedRow['pg_priority']),			// 優先度
			'active'	=> $active	// 有効無効
		);
		$this->tmpl->addVars('id_list', $row);
		$this->tmpl->parseTemplate('id_list', 'a');
		
		// 表示中項目のページサブIDを保存
		$this->serialArray[] = $value;
		return true;
	}
}
?>
