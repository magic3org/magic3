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
 * @version    SVN: $Id: admin_blog_mainCategoryWidgetContainer.php 5145 2012-08-29 13:21:42Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() . '/admin_blog_mainBaseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() .	'/blog_categoryDb.php');

class admin_blog_mainCategoryWidgetContainer extends admin_blog_mainBaseWidgetContainer
{
	private $db;	// DB接続オブジェクト
	private $serialNo;			// シリアル番号
	private $firstNo;			// 項目番号
	private $configType;		// 設定タイプ
	private $serialArray = array();		// 表示されている項目シリアル番号
	const DEFAULT_RES_TYPE = 0;	// デフォルトの設定タイプ(常設)
	const DEFAULT_CONFIG_ID = 0;	// デフォルトの設定ID
	const DEFAULT_LIST_COUNT = 20;			// 最大リスト表示数
		
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->db = new blog_categoryDb();
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
		if ($task == 'category_detail'){		// 詳細画面
			return 'admin_category_detail.tmpl.html';
		} else {
			return 'admin_category.tmpl.html';
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
		if ($task == 'category_detail'){	// 詳細画面
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
		$this->langId	= $this->gEnv->getCurrentLanguage();		// 表示言語を取得
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
				$ret = $this->db->delCategoryBySerial($delItems);
				if ($ret){		// データ削除成功のとき
					$this->setGuidanceMsg('データを削除しました');
				} else {
					$this->setAppErrorMsg('データ削除に失敗しました');
				}
			}
		}
		// #### カテゴリーリストを作成 ####
		$this->db->getAllCategory(array($this, 'categoryListLoop'), $this->langId);// デフォルト言語で取得
		
		if (count($this->serialArray) > 0){
			$this->tmpl->addVar("_widget", "serial_list", implode($this->serialArray, ','));// 表示項目のシリアル番号を設定
		} else {
			$this->tmpl->setAttribute('itemlist', 'visibility', 'hidden');// 項目がないときは、一覧を表示しない
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
		$this->langId	= $this->gEnv->getCurrentLanguage();		// 表示言語を取得
		$userId = $this->gEnv->getCurrentUserId();
		
		$act = $request->trimValueOf('act');
		$this->serialNo = $request->trimValueOf('serial');		// 選択項目のシリアル番号

		$name	= $request->trimValueOf('item_name');		// カテゴリー名称
		$index	= $request->trimValueOf('item_index');		// 表示順
		$visible = ($request->trimValueOf('item_visible') == 'on') ? 1 : 0;			// 表示するかどうか
		
		$replaceNew = false;		// データを再取得するかどうか
		if ($act == 'add'){		// 項目追加の場合
			// 入力チェック
			$this->checkInput($name, '名前');
			$this->checkNumeric($index, '表示順');
			
			// エラーなしの場合は、データを登録
			if ($this->getMsgCount() == 0){
				$ret = $this->db->addCategory(0, $this->langId, $name, 0, $index, $visible, $userId, $newSerial);
				if ($ret){
					$this->setGuidanceMsg('データを追加しました');
					
					// シリアル番号更新
					$this->serialNo = $newSerial;
					$replaceNew = true;			// データを再取得
				} else {
					$this->setAppErrorMsg('データ追加に失敗しました');
				}
			}
		} else if ($act == 'update'){		// 項目更新の場合
			// 入力チェック
			$this->checkInput($name, '名前');
			$this->checkNumeric($index, '表示順');		
			
			// エラーなしの場合は、データを登録
			if ($this->getMsgCount() == 0){
				$ret = $this->db->updateCategory($this->serialNo, $name, 0, $index, $visible, $userId, $newSerial);
				if ($ret){
					$this->setGuidanceMsg('データを更新しました');
					
					// 登録済みのカテゴリーを取得
					$this->serialNo = $newSerial;
					$replaceNew = true;			// データを再取得
				} else {
					$this->setAppErrorMsg('データ更新に失敗しました');
				}
			}
		} else if ($act == 'delete'){		// 項目削除の場合
			$ret = $this->db->delCategoryBySerial(array($this->serialNo));
			if ($ret){		// データ削除成功のとき
				$this->setGuidanceMsg('データを削除しました');
			} else {
				$this->setAppErrorMsg('データ削除に失敗しました');
			}
		} else {	// 初期表示
			// 入力値初期化
			if (empty($this->serialNo)){		// シリアル番号
				$name = '';		// 名前
				$index = $this->db->getMaxIndex($this->langId) + 1;	// 表示順
				$visible = 1;	// 表示状態
			} else {
				$replaceNew = true;			// データを再取得
			}
		}
		// データを再取得のとき
		if ($replaceNew){
			$ret = $this->db->getCategoryBySerial($this->serialNo, $row);
			if ($ret){
				// 取得値を設定
				$id = $row['bc_id'];		// ID
				$this->langId = $row['bc_language_id'];		// 言語ID
				$name = $row['bc_name'];		// 名前
				$index = $row['bc_sort_order'];	// 表示順
				$visible = $row['bc_visible'];	// 表示状態
				$updateUser = $this->convertToDispString($row['lu_name']);	// 更新者
				$updateDt = $this->convertToDispDateTime($row['bc_create_dt']);	// 更新日時
			}
		}
		// #### 更新、新規登録部をを作成 ####
		if (empty($this->serialNo)){		// シリアル番号のときは新規とする
			$this->tmpl->addVar("_widget", "id", '新規');
			$this->tmpl->setAttribute('add_button', 'visibility', 'visible');// 「新規追加」ボタン
		} else {
			$this->tmpl->addVar("_widget", "id", $id);
			$this->tmpl->setAttribute('update_button', 'visibility', 'visible');
		}
		$this->tmpl->addVar("_widget", "serial", $this->serialNo);
		$this->tmpl->addVar("_widget", "name", $name);		// 名前
		$this->tmpl->addVar("_widget", "index", $index);		// 表示順
		
		$visibleStr = '';
		if ($visible){	// 項目の表示
			$visibleStr = 'checked';
		}
		$this->tmpl->addVar("_widget", "visible", $visibleStr);		// 表示状態
		if (!empty($updateUser)) $this->tmpl->addVar("_widget", "update_user", $updateUser);	// 更新者
		if (!empty($updateDt)) $this->tmpl->addVar("_widget", "update_dt", $updateDt);	// 更新日時
	}
	/**
	 * 取得したデータをテンプレートに設定する
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function categoryListLoop($index, $fetchedRow, $param)
	{
		$serial = $fetchedRow['bc_serial'];
		$id = $this->convertToDispString($fetchedRow['bc_id']);

		
		// 対応言語を取得
		$lang = '';
		$ret = $this->db->getLangByCategoryId($fetchedRow['bc_id'], $rows);
		if ($ret){
			$count = count($rows);
			for ($i = 0; $i < $count; $i++){
				if ($this->gEnv->getCurrentLanguage() == 'ja'){	// 日本語の場合
					$lang .= $rows[$i]['ln_name'];
					if ($i != $count -1) $lang .= ',';
				} else {
					$lang .= $rows[$i]['ln_name_en'];
					if ($i != $count -1) $lang .= ',';
				}
			}
		}
		// 親カテゴリー名を取得
		$pcatId = $fetchedRow['bc_parent_id'];
		$pcategoryName = '';
		if ($pcatId != 0){
			$ret = $this->db->getCategoryByCategoryId($pcatId, $this->gEnv->getDefaultLanguage(), $row);
			if ($ret) $pcategoryName = $this->convertToDispString($row['bc_name']);
		}
		$visible = '';
		if ($fetchedRow['bc_visible']){	// 項目の表示
			$visible = 'checked';
		}		
		$row = array(
			'index' => $index,													// 行番号
			'serial' => $serial,	// シリアル番号
			'id' => $id,			// ID
			'name' => $this->convertToDispString($fetchedRow['bc_name']),		// 名前
			'pcategory_name' => $pcategoryName,		// 親カテゴリー名前
			'view_index' => $this->convertToDispString($fetchedRow['bc_sort_order']),		// 表示順
			'lang' => $lang,													// 対応言語
			'update_user' => $this->convertToDispString($fetchedRow['lu_name']),	// 更新者
			'update_dt' => $this->convertToDispDateTime($fetchedRow['bc_create_dt']),	// 更新日時
			'visible' => $visible,											// メニュー項目表示制御
			'default' => $default											// デフォルト項目
		);
		$this->tmpl->addVars('itemlist', $row);
		$this->tmpl->parseTemplate('itemlist', 'a');

		// 表示中項目のシリアル番号を保存
		$this->serialArray[] = $serial;
		return true;
	}
	/**
	 * 取得した言語をテンプレートに設定する
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function langLoop($index, $fetchedRow, $param)
	{
		$selected = '';
		if ($fetchedRow['ln_id'] == $this->langId){
			$selected = 'selected';
		}
		if ($this->gEnv->getCurrentLanguage() == 'ja'){		// 日本語表示の場合
			$name = $this->convertToDispString($fetchedRow['ln_name']);
		} else {
			$name = $this->convertToDispString($fetchedRow['ln_name_en']);
		}

		$row = array(
			'value'    => $this->convertToDispString($fetchedRow['ln_id']),			// 言語ID
			'name'     => $name,			// 言語名
			'selected' => $selected														// 選択中かどうか
		);
		$this->tmpl->addVars('lang_list', $row);
		$this->tmpl->parseTemplate('lang_list', 'a');
		return true;
	}
}
?>
