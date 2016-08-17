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

class admin_mainMenuidWidgetContainer extends admin_mainMainteBaseWidgetContainer
{
	private $db;	// DB接続オブジェクト
	private $deviceType;	// 端末タイプ
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
		if ($task == 'menuid_detail'){		// 詳細画面
			return 'menuid_detail.tmpl.html';
		} else {			// 一覧画面
			return 'menuid.tmpl.html';
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
		if ($task == 'menuid_detail'){	// 詳細画面
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
		
		if ($act == 'delete'){		// メニュー項目の削除
			$listedItem = explode(',', $request->trimValueOf('seriallist'));
			$delItems = array();
			for ($i = 0; $i < count($listedItem); $i++){
				// 項目がチェックされているかを取得
				$itemName = 'item' . $i . '_selected';
				$itemValue = ($request->trimValueOf($itemName) == 'on') ? 1 : 0;
				
				if ($itemValue){		// チェック項目
					$delItems[] = $listedItem[$i];
					
					// 削除可能かチェック
					$refCount = $this->_db->getMenuIdRefCount($listedItem[$i]);		// メニューID使用数
					if ($refCount > 0){		// 参照ありのときは削除できない
						$this->setMsg(self::MSG_USER_ERR, '使用中のメニューIDは削除できません。メニューID=' . $listedItem[$i]);
						break;
					}
				}
			}
			if ($this->getMsgCount() == 0 && count($delItems) > 0){
				$ret = $this->db->delMenuId($delItems);
				if ($ret){		// データ削除成功のとき
					$this->setGuidanceMsg('データを削除しました');
				} else {
					$this->setAppErrorMsg('データ削除に失敗しました');
				}
			}
		}
		
		$this->db->getMenuIdList(-1/*すべてのデバイス*/, array($this, 'itemLoop'), true/*すべてのメニューIDを取得*/);
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
		$menuId = $request->trimValueOf('serial');		// メニューID

		$newMenuId = $request->trimValueOf('item_menuid');		// 新規メニューID
		$name = $request->trimValueOf('item_name');		// 名前
		$sortOrder = $request->trimValueOf('item_sort_order');		// ソート順
		$this->deviceType = $request->trimValueOf('item_device_type');		// 端末タイプ
		$targetWidget = $request->trimValueOf('item_target_widget');		// 対象ウィジェット

		$replaceNew = false;		// データを再取得するかどうか
		if ($act == 'add'){		// 新規追加のとき
			// 入力チェック
			$this->checkSingleByte($newMenuId, 'メニューID');
			$this->checkInput($name, '名前');
			$this->checkNumeric($sortOrder, 'ソート順');
			
			// 登録済みのページIDかどうかチェック
			if ($this->getMsgCount() == 0){
				if ($this->db->isExistsMenuId($newMenuId)) $this->setMsg(self::MSG_USER_ERR, 'すでに登録済みのメニューIDです');
			}
			// エラーなしの場合は、データを更新
			if ($this->getMsgCount() == 0){
				// ページIDの追加
				$ret = $this->db->updateMenuId($newMenuId, $name, $sortOrder, $this->deviceType, $targetWidget);
				if ($ret){		// データ追加成功のとき
					$this->setMsg(self::MSG_GUIDANCE, 'データを追加しました');
					
					$menuId = $newMenuId;		// メニューID再設定
					$replaceNew = true;			// データを再取得
				} else {
					$this->setMsg(self::MSG_APP_ERR, 'データの追加に失敗しました');
				}
			}
		} else if ($act == 'update'){		// 更新のとき
			// 入力チェック
			$this->checkSingleByte($menuId, 'メニューID');
			$this->checkInput($name, '名前');
			$this->checkNumeric($sortOrder, 'ソート順');
			
			// エラーなしの場合は、データを更新
			if ($this->getMsgCount() == 0){
				// ページIDの更新
				$ret = $this->db->updateMenuId($menuId, $name, $sortOrder, $this->deviceType, $targetWidget);
				if ($ret){		// データ追加成功のとき
					$this->setMsg(self::MSG_GUIDANCE, 'データを更新しました');
					$replaceNew = true;			// データを再取得
				} else {
					$this->setMsg(self::MSG_APP_ERR, 'データ更新に失敗しました');
				}
			}
		} else if ($act == 'delete'){		// 削除のとき
			// 参照ありのときは削除できない
			$refCount = $this->_db->getMenuIdRefCount($menuId);		// メニューID使用数
			if ($refCount > 0) $this->setMsg(self::MSG_USER_ERR, '使用中のメニューIDは削除できません');
			
			// エラーなしの場合は、データを削除
			if ($this->getMsgCount() == 0){
				$ret = $this->db->delMenuId(array($menuId));
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
		if ($replaceNew){
			$ret = $this->db->getMenuId($menuId, $row);
			if ($ret){
				$name = $row['mn_name'];
				$sortOrder = $row['mn_sort_order'];
				$this->deviceType = $row['mn_device_type'];		// 端末タイプ
				$targetWidget = $row['mn_widget_id'];			// 対象ウィジェット
			}
		}
		
		// アクセスポイント選択メニュー作成
		$this->db->getAccessPointList(array($this, 'pageIdLoop'));
		
		if (empty($menuId)){		// 新規追加のとき
			$this->tmpl->setAttribute('show_menuid', 'visibility', 'visible');// メニューID入力領域表示
			$this->tmpl->setAttribute('add_button', 'visibility', 'visible');// 追加ボタン表示
			
			$this->tmpl->addVar("show_menuid", "menu_id", $newMenuId);			// メニューID
		} else {
			$this->tmpl->setAttribute('update_button', 'visibility', 'visible');// 更新ボタン表示
			$this->tmpl->addVar("_widget", "menu_id", $menuId);			// メニューID
			
			// 使用中のメニューIDは削除できない
			$refCount = $this->_db->getMenuIdRefCount($menuId);		// メニューID使用数
			if ($refCount > 0) $this->tmpl->addVar("update_button", "del_disabled", "disabled");		// 削除ボタン使用不可
		}
		
		$this->tmpl->addVar("_widget", "name", $this->convertToDispString($name));		// ページ名
		$this->tmpl->addVar("_widget", "sort_order", $sortOrder);		// ソート順
		$this->tmpl->addVar("_widget", "target_widget", $targetWidget);		// 対象ウィジェット
	}
	/**
	 * メニューIDをテンプレートに設定する
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function itemLoop($index, $fetchedRow, $param)
	{
		$value = $this->convertToDispString($fetchedRow['mn_id']);
		
		$accessPointName = str_replace('用アクセスポイント', '', $fetchedRow['pg_name']);		// アクセスポイント名
		$row = array(
			'index'		=> $index,			// インデックス番号
			'value'		=> $value,			// メニューID
			'name'		=> $this->convertToDispString($fetchedRow['mn_name']),			// メニューID名
			'access_point_name'	=> $this->convertToDispString($accessPointName),
			'sort_order'	=> $this->convertToDispString($fetchedRow['mn_sort_order']),	// ソート順
			'ref_count' => $this->_db->getMenuIdRefCount($value)		// メニューID使用数
		);
		$this->tmpl->addVars('id_list', $row);
		$this->tmpl->parseTemplate('id_list', 'a');
		
		// 表示中項目のページサブIDを保存
		$this->serialArray[] = $value;
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
		$name	= $fetchedRow['pg_name'];			// ページ名
		$deviceType = $fetchedRow['pg_device_type'];	// 端末タイプ(0=PC、1=携帯、2=スマートフォン)
		
		$selected = '';
		if ($deviceType == $this->deviceType) $selected = 'selected';

		$row = array(
			'value'    => $this->convertToDispString($deviceType),			// 端末タイプ
			'name'     => $this->convertToDispString($name),					// ページ名
			'selected' => $selected												// 選択中かどうか
		);
		$this->tmpl->addVars('main_id_list', $row);
		$this->tmpl->parseTemplate('main_id_list', 'a');
		return true;
	}
}
?>
