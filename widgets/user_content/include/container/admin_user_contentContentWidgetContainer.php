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
 * @version    SVN: $Id: admin_user_contentContentWidgetContainer.php 3072 2010-04-26 08:09:18Z fishbone $
 * @link       http://www.m-media.co.jp
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() . '/admin_user_contentBaseWidgetContainer.php');

class admin_user_contentContentWidgetContainer extends admin_user_contentBaseWidgetContainer
{
	private $langId;		// 言語ID
	private $serialNo;		// 選択中の項目のシリアル番号
	private $serialArray = array();		// 表示されている項目シリアル番号
	private $isExistsRoom;		// ルームが存在するかどうか
	private $roomId;		// ルームID
	
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
		if ($task == 'content_detail'){		// 詳細画面
			return 'admin_content_detail.tmpl.html';
		} else {			// 一覧画面
			return 'admin_content.tmpl.html';
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
		if ($task == 'content_detail'){	// 詳細画面
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
		
		// 前画面からの引継ぎデータを取得
		$this->roomId = $request->trimValueOf('roomid');// 選択中のルームIDを取得
		
		// ルーム選択メニューを作成
		// ユーザが選択可能なルームのみ表示
		if ($this->gEnv->isSystemManageUser()){			// システム運用可ユーザのとき
			$this->tmpl->setAttribute('sel_room_area', 'visibility', 'visible');// ルーム選択メニュー表示
			
			$this->_localDb->getAllRooms(array($this, 'roomListLoop'));
			
			if (!$this->isExistsRoom){
				$this->tmpl->setAttribute('sel_room_area', 'visibility', 'hidden');// ルーム選択メニューを非表示
				
				$this->tmpl->setAttribute('room_area', 'visibility', 'visible');	// ルームID表示
				$this->tmpl->addVar('room_area', 'room_id', 'ルームが作成されていません');	// ルームID表示
			}
		} else {
			$this->tmpl->setAttribute('room_area', 'visibility', 'visible');	// ルームID表示
			$this->tmpl->addVar('room_area', 'room_id', $this->roomId);	// ルームID表示
		}
		// 一覧作成
		$this->_localDb->getAllContents($this->roomId, $this->langId, array($this, 'itemLoop'));
		
		if (count($this->serialArray) > 0){		// コンテンツ一覧にデータがある場合
			$this->tmpl->addVar("_widget", "serial_list", implode($this->serialArray, ','));// 表示項目のシリアル番号を設定
			
			// プレビュー用URL作成
			$previewUrl = $this->gEnv->getDefaultUrl() . '?' . M3_REQUEST_PARAM_ROOM_ID . '=' . $this->roomId . '&' . M3_REQUEST_PARAM_OPERATION_COMMAND . '=' . M3_REQUEST_CMD_PREVIEW;
			$this->tmpl->addVar('_widget', 'preview_url', $previewUrl);// プレビュー用URL(フロント画面)
		} else {
			// 項目がないときは、一覧を表示しない
			$this->tmpl->setAttribute('itemlist', 'visibility', 'hidden');
			
			// ボタンの制御
			$this->tmpl->addVar('_widget', 'preview_btn_disabled', 'disabled');// プレビュー用ボタン
			$this->tmpl->addVar('_widget', 'edit_btn_disabled', 'disabled');// 編集ボタン
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
		$defaultTimestamp = $this->gEnv->getInitValueOfTimestamp();
		$this->langId = $this->gEnv->getDefaultLanguage();		// デフォルト言語
		
		$act = $request->trimValueOf('act');
		$this->serialNo = $request->trimValueOf('serial');		// 選択項目のシリアル番号(シリアル番号はコンテンツIDとして使用)
	
		// 前画面からの引継ぎデータを取得
		$this->roomId = $request->trimValueOf('roomid');// 選択中のルームIDを取得
				
		// コンテンツIDからコンテンツ項目情報を取得
		$ret = $this->_localDb->getItemById($this->serialNo, $row);
		if ($ret){
			$id		= $row['ui_id'];			// コンテンツ項目識別ID
			$name	= $row['ui_name'];		// コンテンツ項目名
			$type	= $row['ui_type'];		// 項目タイプ
			
			// 編集可能なコンテンツ項目かチェック
			if (!$this->canEditContent($id)){
				$this->cancelParse();
				return;
			}
		} else {			// コンテンツIDが異常のときは終了
			$this->cancelParse();
			return;
		}
		
		// 入力データ取得
		switch ($type){
			case 0:		// HTMLのとき
			default:
				$html = $request->valueOf('item_html');		// タグを許可する
				$number = 0;
				break;
			case 1:		// テキストのとき
				$html = $request->trimValueOf('item_html');
				$number = 0;
				break;
			case 2:		// 数値のとき
				$html = $request->trimValueOf('item_html');
				$number = $request->trimValueOf('item_number');			// 数値
				break;
		}
		$replaceNew = false;		// データを再取得するかどうか
		if ($act == 'add'){		// 新規追加のとき
			// 入力チェック
			if ($type == 2){			// データタイプが数値のとき
				$this->checkNumericF($number, '数値');
			}
			
			// エラーなしの場合は、データを更新
			if ($this->getMsgCount() == 0){
				$ret = $this->_localDb->updateContent($id, $this->roomId, $this->langId, $html, $number, 1/*表示*/, $defaultTimestamp/*公開開始*/, $defaultTimestamp/*公開終了*/, 0/*ユーザ制限なし*/);
				
				// コンテンツ更新日時の更新
				if ($ret){
					// タブ識別IDからデータを取得
					$ret = $this->_localDb->getContent($id, $this->roomId, $this->langId, $row);
					if ($ret) $ret = $this->_localDb->updateRoomContentUpdateDt($this->roomId, $row['ur_content_update_dt']);
				}
				
				if ($ret){		// データ追加成功のとき
					$this->setMsg(self::MSG_GUIDANCE, 'データを追加しました');
					
					$replaceNew = true;			// データを再取得
				} else {
					$this->setMsg(self::MSG_APP_ERR, 'データ追加に失敗しました');
				}
			}
		} else if ($act == 'update'){		// 行更新のとき
			// 入力チェック
			if ($type == 2){			// データタイプが数値のとき
				$this->checkNumericF($number, '数値');
			}
			
			// エラーなしの場合は、データを更新
			if ($this->getMsgCount() == 0){
				$ret = $this->_localDb->updateContent($id, $this->roomId, $this->langId, $html, $number, 1/*表示*/, $defaultTimestamp/*公開開始*/, $defaultTimestamp/*公開終了*/, 0/*ユーザ制限なし*/);
				
				// コンテンツ更新日時の更新
				if ($ret){
					// タブ識別IDからデータを取得
					$ret = $this->_localDb->getContent($id, $this->roomId, $this->langId, $row);
					if ($ret) $ret = $this->_localDb->updateRoomContentUpdateDt($this->roomId, $row['ur_content_update_dt']);
				}
				
				if ($ret){		// データ追加成功のとき
					$this->setMsg(self::MSG_GUIDANCE, 'データを更新しました');
				
					$replaceNew = true;			// データを再取得
				} else {
					$this->setMsg(self::MSG_APP_ERR, 'データ更新に失敗しました');
				}
			}
		} else {		// 初期状態
			$replaceNew = true;			// データを再取得
		}
		// 表示データ再取得
		if ($replaceNew){
			// タブ識別IDからデータを取得
			$ret = $this->_localDb->getContent($id, $this->roomId, $this->langId, $row);
			if ($ret){
				$html		= $row['uc_data'];		// 設定データ
				$number		= $row['uc_data_search_num'];		// 数値データ
			} else {// データがないときはデフォルトの設定
				$html		= '';		// 設定データ
				$number		= 0;		// 数値データ
			}
		}
		
		// 登録データの入力フィールドを作成
		switch ($type){
			case 0:		// HTMLのとき
			default:
				$this->tmpl->setAttribute('edit_html', 'visibility', 'visible');// FCKEditor表示
				
				$this->tmpl->addVar("input_field", "html", $html);		// HTML
				break;
			case 1:		// テキストのとき
				$this->tmpl->addVar('input_field', 'datatype', 'text');
				
				$this->tmpl->addVar("input_field", "html", $html);		// HTML
				break;
			case 2:		// 数値のとき
				$this->tmpl->addVar('input_field', 'datatype', 'number');
				
				$this->tmpl->addVar("input_field", "html", $html);		// HTML
				$this->tmpl->addVar("input_field", "number", $number);		// 数値
				break;
		}

		// 埋め込みタグ
		$keyTag = M3_TAG_START . M3_TAG_MACRO_ITEM_KEY . $id . M3_TAG_END;
		
		// 画面にデータを埋め込む
		$this->tmpl->addVar("_widget", "name", $this->convertToDispString($name));		// 名前
		$this->tmpl->addVar("_widget", "tag", $keyTag);		// タグ
		
		// 前画面からの引継ぎデータを再設定
		$this->tmpl->addVar("_widget", "room_id", $this->roomId);	// ルームID
		
		// 選択中のシリアル番号を設定
		$this->tmpl->addVar("_widget", "serial", $this->serialNo);
		
		if (empty($this->serialNo)){		// シリアル番号が空のときは新規とする
			$this->tmpl->setAttribute('add_button', 'visibility', 'visible');// 新規登録ボタン表示
		} else {
			$this->tmpl->setAttribute('update_button', 'visibility', 'visible');// 更新ボタン表示
		}
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
		// 編集可能なコンテンツ項目かチェック
		if (!$this->canEditContent($fetchedRow['ui_id'])) return true;
		
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
		
		// 設定データ
		$data = '';
		switch ($fetchedRow['ui_type']){
			case 0:		// HTML
			default:
				// $fetchedRow['uc_data']はNULL値が来ることがあるので注意
				if (!empty($fetchedRow['uc_data'])) $data = $fetchedRow['uc_data'];			// HTMLタグはそのままにする
				break;
			case 1:		// テキスト
				$data = $this->convertToDispString($fetchedRow['uc_data']);
				break;
			case 2:
				$data = $this->convertToDispString($fetchedRow['uc_data']);
				break;	// 数値
		}
		
		$row = array(
			'index' => $index,
			'serial' => $fetchedRow['ui_serial'],
			'id' =>	$this->convertToDispString($fetchedRow['ui_id']),		// 識別ID
			'name'     => $this->convertToDispString($fetchedRow['ui_name']),			// 表示名
			'type'	=> $this->convertToDispString($typeStr),				// データタイプ
			'tag'	=> $keyTag,					// 埋め込みタグ
			'data'	=> $data					// 設定データ
		);
		$this->tmpl->addVars('itemlist', $row);
		$this->tmpl->parseTemplate('itemlist', 'a');
		
		// 表示中項目のシリアル番号を保存
		$this->serialArray[] = $fetchedRow['ui_id'];			// コンテンツ項目ID
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
	function roomListLoop($index, $fetchedRow, $param)
	{
		$id = $fetchedRow['ur_id'];
		$selectStr = '';
		if ($id == $this->roomId) $selectStr .= 'selected ';			// 選択中のルームIDのとき
		
		$row = array(
			'value' => $this->convertToDispString($id),			// ID
			'name' => $this->convertToDispString($id) . ' - ' . $this->convertToDispString($fetchedRow['ur_name']),		// 名前
			'selected' => $selectStr		// 選択状態
		);
		$this->tmpl->addVars('room_list', $row);
		$this->tmpl->parseTemplate('room_list', 'a');
		
		$this->isExistsRoom = true;		// ルームが存在するかどうか
		return true;
	}
	/**
	 * 編集可能なコンテンツ項目かどうかチェック
	 *
	 * @param string $id	コンテンツ項目ID
	 * @return bool			true=編集可能、false=編集不可
	 */
	function canEditContent($id)
	{
		static $visibleContent;
		
		if ($this->gEnv->isSystemManageUser()){			// システム運用可ユーザのとき
			return true;
		} else {		// システム運用可ユーザ以下は、表示している項目のみ編集可能
			return true;
		}
	}
}
?>
