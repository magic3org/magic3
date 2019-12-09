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
 * @version    SVN: $Id: admin_reserve_mainResourceWidgetContainer.php 642 2008-05-22 12:25:05Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() . '/admin_reserve_mainBaseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() .	'/reserve_mainDb.php');

class admin_reserve_mainResourceWidgetContainer extends admin_reserve_mainBaseWidgetContainer
{
	private $db;	// DB接続オブジェクト
	private $mainDb;	// DB接続オブジェクト
	private $sysDb;		// システムDBオブジェクト
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
		global $gInstanceManager;
		
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->db = new reserve_mainDb();
		$this->sysDb = $gInstanceManager->getSytemDbObject();
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
		if ($task == 'resource_detail'){		// 詳細画面
			return 'admin_resource_detail.tmpl.html';
		} else {
			return 'admin_resource.tmpl.html';
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
		if ($task == 'resource_detail'){	// 詳細画面
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
		
		$pageNo = $request->trimIntValueOf(M3_REQUEST_PARAM_PAGE_NO, '1');				// ページ番号
		
		// デフォルト値を取得
		$maxListCount = self::DEFAULT_LIST_COUNT;	// 表示項目数

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
				$ret = $this->db->deleteResourceById($delItems);
				if ($ret){		// データ削除成功のとき
					$this->setGuidanceMsg('データを削除しました');
				} else {
					$this->setAppErrorMsg('データ削除に失敗しました');
				}
			}
		}
		// 総数を取得
		$totalCount = $this->db->getAllResourceListCount(self::DEFAULT_RES_TYPE, self::DEFAULT_CONFIG_ID);
		
		// 表示するページ番号の修正
		$pageCount = (int)(($totalCount -1) / $maxListCount) + 1;		// 総ページ数
		if ($pageNo < 1) $pageNo = 1;
		if ($pageNo > $pageCount) $pageNo = $pageCount;
		$this->firstNo = ($pageNo -1) * $maxListCount + 1;		// 先頭番号
		
		// リソース一覧を表示
		$this->db->getAllResourceList(self::DEFAULT_RES_TYPE, self::DEFAULT_CONFIG_ID, $maxListCount, ($pageNo -1) * $maxListCount, array($this, 'resourceListLoop'));
		
		// ページング用リンク作成
		$pageLink = '';
		if ($pageCount > 1){	// ページが2ページ以上のときリンクを作成
			for ($i = 1; $i <= $pageCount; $i++){
				//$linkUrl = $this->currentPageUrl . '&category=' . $this->categoryId . '&page=' . $i;
				if ($i == $pageNo){
					$link = '&nbsp;' . $i;
				} else {
					//$link = '&nbsp;<a href="' . $linkUrl . '" >' . $i . '</a>';
					$link = '&nbsp;<a href="#" onclick="selpage(\'' . $i . '\');return false;">' . $i . '</a>';
				}
				$pageLink .= $link;
			}
		}
		$this->tmpl->addVar("_widget", "page_link", $pageLink);
		$this->tmpl->addVar("_widget", "page", $pageNo);		// 現在のページ番号
		
		$this->tmpl->addVar("_widget", "serial_list", implode(',', $this->serialArray));// 表示項目のシリアル番号を設定
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

		// ユーザ情報、表示言語
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId		= $gEnvManager->getCurrentUserId();
		$langId	= $gEnvManager->getCurrentLanguage();		// 表示言語を取得
				
		$act = $request->trimValueOf('act');
		$this->serialNo = $request->trimValueOf('serial');		// 選択項目のシリアル番号(シリアル番号はリソースIDを使用する)
		
		$name				= $request->trimValueOf('item_name');		// 名前
		$desc				= $request->trimValueOf('item_desc');		// 説明
		$visible			= ($request->trimValueOf('item_visible') == 'on') ? 1 : 0;	// 表示状態
		$index				= $request->trimValueOf('item_index');		// ソート順
				
		$replaceNew = false;		// データを再取得するかどうか
		if ($act == 'add'){		// 項目追加の場合
			// 入力チェック
			$this->checkInput($name, '名前');
			$this->checkNumeric($index, '表示順');

			// エラーなしの場合は、データを登録
			if ($this->getMsgCount() == 0){
				$ret = $this->db->updateResource(0/*新規追加*/, self::DEFAULT_RES_TYPE, self::DEFAULT_CONFIG_ID, $name, $desc, $visible, $index, $newResourceId);
				if ($ret){
					$this->setGuidanceMsg('データを追加しました');
					
					$this->serialNo = $newResourceId;
				} else {
					$this->setAppErrorMsg('データ追加に失敗しました');
				}
			}
		} else if ($act == 'update'){		// 項目更新の場合
			if (empty($this->serialNo)){
				$this->setUserErrorMsg('更新するリソースが選択されていません');
			}
			// 入力チェック
			$this->checkInput($name, '名前');
			$this->checkNumeric($index, '表示順');
			
			// エラーなしの場合は、データを登録
			if ($this->getMsgCount() == 0){
				$ret = $this->db->updateResource($this->serialNo, self::DEFAULT_RES_TYPE, self::DEFAULT_CONFIG_ID, $name, $desc, $visible, $index, $newResourceId);
				if ($ret){
					$this->setGuidanceMsg('データを更新しました');
					$replaceNew = true;			// 会員情報を再取得
				} else {
					$this->setAppErrorMsg('データ更新に失敗しました');
				}
			}
		} else if ($act == 'delete'){		// 項目削除の場合
			if (empty($this->serialNo)){
				$this->setUserErrorMsg('削除するリソースが選択されていません');
			}
			// エラーなしの場合は、データを削除
			if ($this->getMsgCount() == 0){
				$ret = $this->db->deleteResourceById(array($this->serialNo));
				if ($ret){		// データ削除成功のとき
					$this->setGuidanceMsg('データを削除しました');
				} else {
					$this->setAppErrorMsg('データ削除に失敗しました');
				}
			}
		} else if ($act == 'search'){		// 検索再実行
		} else {	// 初期表示
			if (empty($this->serialNo)){
				// デフォルトの表示順を設定
				$index = $this->db->getMaxResourceIndex(self::DEFAULT_RES_TYPE, self::DEFAULT_CONFIG_ID) + 1;
				$visible = 1;	// 表示状態
			} else {
				$replaceNew = true;			// 会員情報を再取得
			}
		}
		if ($replaceNew){
			// リソース情報を取得
			$ret = $this->db->getResourceById($this->serialNo, $row);
			if ($ret){
				// 取得値を設定
				$name = $this->convertToDispString($row['rr_name']);			// 名前
				$desc = $this->convertToDispString($row['rr_description']);			// 説明
				$index = $this->convertToDispString($row['rr_sort_order']);			// 表示順
				$visible = $row['rr_visible'];	// 表示状態
			}
		}
		
		// #### 更新、新規登録部をを作成 ####
		$this->tmpl->addVar("_widget", "name", $name);		// 名前
		$this->tmpl->addVar("_widget", "desc", $desc);		// 説明
		$this->tmpl->addVar("_widget", "index", $index);		// 表示順
		$visibleStr = '';
		if ($visible){	// 項目の表示
			$visibleStr = 'checked';
		}
		$this->tmpl->addVar("_widget", "visible", $visibleStr);		// 表示状態
		
		// ボタンの設定
		if (empty($this->serialNo)){		// 新規追加項目を選択しているとき
			$this->tmpl->setAttribute('add_button', 'visibility', 'visible');// 「新規追加」ボタン
		} else {
			// データ更新、削除ボタン表示
			$this->tmpl->setAttribute('update_button', 'visibility', 'visible');// 更新、削除ボタン
		}
		// 値を埋め込む
		$this->tmpl->addVar("_widget", "serial", $this->serialNo);
	}
	/**
	 * 取得したデータをテンプレートに設定する
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function resourceListLoop($index, $fetchedRow, $param)
	{
		global $gEnvManager;
		
		// 行カラーの設定
		$lineColor = '';
		if ($index % 2 != 0){
			$lineColor = 'class="even"';		// 偶数行
		}
		$serial = $fetchedRow['rr_id'];// リソースIDをシリアル番号とする
		$visible = '';
		if ($fetchedRow['rr_visible']){	// 項目の表示
			$visible = 'checked';
		}
		$row = array(
			'line_color' => $lineColor,						// 行のカラー
			'no' => $this->firstNo + $index,				// 行番号
			'index' => $index,								// 項目番号
			'serial' => $serial,	// シリアル番号
			'id' => $id,			// ID
			'view_index' => $this->convertToDispString($fetchedRow['rr_sort_order']),			// 表示順
			'name' => $this->convertToDispString($fetchedRow['rr_name']),	// 名前
			'visible' => $visible												// 公開
		);
		$this->tmpl->addVars('itemlist', $row);
		$this->tmpl->parseTemplate('itemlist', 'a');
		
		// 表示中項目のシリアル番号を保存
		$this->serialArray[] = $serial;
		return true;
	}
}
?>
