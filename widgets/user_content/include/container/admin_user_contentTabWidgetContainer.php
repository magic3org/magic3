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
 * @version    SVN: $Id: admin_user_contentTabWidgetContainer.php 3057 2010-04-22 10:14:03Z fishbone $
 * @link       http://www.m-media.co.jp
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() . '/admin_user_contentBaseWidgetContainer.php');

class admin_user_contentTabWidgetContainer extends admin_user_contentBaseWidgetContainer
{
	private $langId;		// 言語ID
	private $contentItems = array();	// コンテンツ項目定義
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
		if ($task == 'tab_detail'){		// 詳細画面
			return 'admin_tab_detail.tmpl.html';
		} else {			// 一覧画面
			return 'admin_tab.tmpl.html';
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
		if ($task == 'tab_detail'){	// 詳細画面
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
				$ret = $this->_localDb->delTab($delItems);
				if ($ret){		// データ削除成功のとき
					$this->setGuidanceMsg('データを削除しました');
				} else {
					$this->setAppErrorMsg('データ削除に失敗しました');
				}
			}
		}
		// コンテンツ項目定義を取得
		$this->contentItems = $this->getAllContentItems();
		
		// 一覧作成
		$this->_localDb->getAllTabs($this->langId, array($this, 'itemLoop'));
		
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
		$id		= $request->trimValueOf('item_id');	// 識別ID
		$groupId	= $request->trimValueOf('item_group_id');	// 所属グループID
		$index	= $request->trimValueOf('item_index');	// 表示順
		$html = $request->valueOf('item_html');		// テンプレート用HTML
		$visible = ($request->trimValueOf('item_visible') == 'on') ? 1 : 0;		// 表示するかどうか
		
		$replaceNew = false;		// データを再取得するかどうか
		if ($act == 'add'){		// 新規追加のとき
			// 入力チェック
			$this->checkInput($name, '表示名');
			$this->checkSingleByte($id, '識別ID');
			$this->checkNumeric($groupId, '所属グループID');		// 所属グループID
			$this->checkNumeric($index, '表示順');
			
			// 同じIDがある場合はエラー
			if ($this->_localDb->getTabById($this->langId, $id, $row)) $this->setMsg(self::MSG_USER_ERR, '識別IDが重複しています');
			
			// エラーなしの場合は、データを更新
			if ($this->getMsgCount() == 0){
				// テンプレートから埋め込まれているコンテンツ項目のIDを取得
				$useItemId = '';
				$idArray = $this->getItemId($html);
				if (!empty($idArray)) $useItemId = implode(',', $idArray);
				
				$ret = $this->_localDb->updateTab(0/*新規*/, $this->langId, $id, $name, $html, $index, $visible, $useItemId, $groupId, $newSerial);
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
			$this->checkNumeric($groupId, '所属グループID');		// 所属グループID
			$this->checkNumeric($index, '表示順');

			// エラーなしの場合は、データを更新
			if ($this->getMsgCount() == 0){
				// テンプレートから埋め込まれているコンテンツ項目のIDを取得
				$useItemId = '';
				$idArray = $this->getItemId($html);
				if (!empty($idArray)) $useItemId = implode(',', $idArray);
				
				$ret = $this->_localDb->updateTab($this->serialNo, $this->langId, $id, $name, $html, $index, $visible, $useItemId, $groupId, $newSerial);
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
			$ret = $this->_localDb->getTabBySerial($this->serialNo, $row);
			if ($ret) $id = $row['ub_id'];			// タブ識別ID
			
			if (empty($id)){		// タブ識別IDが空のときは新規とする
				$this->serialNo = 0;
				$id		= '';		// 識別ID
				$name	= '';	// 名前
				$groupId = 0;	// 所属グループID
				$html	= '';		// テンプレート
				$index	= $this->_localDb->getMaxTabIndex() + 1;	// 表示順
				$visible	= 1;		// 公開
			} else {
				$replaceNew = true;			// データを再取得
			}
		}
		// コンテンツ項目定義を取得
		$this->contentItems = $this->getAllContentItems();
		
		// 表示データ再取得
		if ($replaceNew){
			// タブ識別IDからデータを取得
			$ret = $this->_localDb->getTabById($this->langId, $id, $row);
			if ($ret){
				$this->serialNo = $row['ub_serial'];
				$name		= $row['ub_name'];
				$groupId	= $row['ub_group_id'];		// 所属グループID
				$html		= $row['ub_template_html'];
				$index		= $row['ub_index'];
				$visible	= $row['ub_visible'];		// 公開
				$useItem	= $row[ub_use_item_id];		// 使用中のコンテンツ項目
			}
		}
		// 使用しているコンテンツ項目IDを取得
		$useItemStr = '';
		if (!empty($useItem)){
			$useItemArray = explode(',', $useItem);
			for ($i = 0; $i < count($useItemArray); $i++){
				$key = $useItemArray[$i];
				$contentName = $this->contentItems[$key]['ui_name'];
				$useItemStr .= $contentName . '(' . $key . '),';
			}
			$useItemStr = rtrim($useItemStr, ',');
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
		$this->tmpl->addVar("_widget", "index", $index);		// 表示順
		$this->tmpl->addVar("_widget", "html", $html);		// テンプレート
		$this->tmpl->addVar("_widget", "use_item", $this->convertToDispString($useItemStr));		// コンテンツ項目
		
		// 項目表示、項目利用可否チェックボックス
		$checked = '';
		if ($visible) $checked = 'checked';
		$this->tmpl->addVar("_widget", "visible", $checked);
		
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
		$visible = '';
		if ($fetchedRow['ub_visible']){	// 項目の表示
			$visible = 'checked';
		}
		
		// 使用しているコンテンツ項目IDを取得
		$useItem = $fetchedRow['ub_use_item_id'];
		$useItemStr = '';
		if (!empty($useItem)){
			$useItemArray = explode(',', $useItem);
			for ($i = 0; $i < count($useItemArray); $i++){
				$key = $useItemArray[$i];
				$name = $this->contentItems[$key]['ui_name'];
				$useItemStr .= $name . '(' . $key . '),';
			}
			$useItemStr = rtrim($useItemStr, ',');
		}
		
		$row = array(
			'index' => $index,
			'serial' => $fetchedRow['ub_serial'],
			'id' =>	$this->convertToDispString($fetchedRow['ub_id']),		// 識別ID
			'name'     => $this->convertToDispString($fetchedRow['ub_name']),			// 表示名
			'group'     => $this->convertToDispString($fetchedRow['ub_group_id']),			// 所属グループID
			'visible'	=> $visible,					// 公開
			'use_item'	=> $useItemStr,					// 使用しているコンテンツ項目ID
		);
		$this->tmpl->addVars('itemlist', $row);
		$this->tmpl->parseTemplate('itemlist', 'a');
		
		// 表示中項目のシリアル番号を保存
		$this->serialArray[] = $fetchedRow['ub_serial'];
		return true;
	}
	/**
	 * テンプレートから埋め込まれているコンテンツ項目IDを取得
	 *
	 * @param string $src		検索対象データ
	 * @return array			コンテンツ項目ID
	 */
	function getItemId($src)
	{
		$idArray = array();
		$matches = array();
		$pattern = '/(' . preg_quote(M3_TAG_START) . '([A-Za-z0-9_]+)' . preg_quote(M3_TAG_END) . ')/u';
		preg_match_all($pattern, $src, $matches, PREG_SET_ORDER);
		for ($i = 0; $i < count($matches); $i++){
			$value = $matches[$i][2];
			if (strStartsWith($value, M3_TAG_MACRO_ITEM_KEY)){
				$value = substr($value, strlen(M3_TAG_MACRO_ITEM_KEY));
				if (!in_array($value, $idArray)) $idArray[] = $value;
			}
		}
		return $idArray;
	}
	/**
	 * すべてのコンテンツ項目IDを取得
	 *
	 * @return array			コンテンツ項目IDをキーにしたコンテンツ項目レコードの連想配列
	 */
	function getAllContentItems()
	{
		$destArray = array();
		
		$ret = $this->_localDb->getAllContentItems($rows);
		if ($ret){
			$count = count($rows);
			for ($i = 0; $i < $count; $i++){
				$key = $rows[$i]['ui_id'];
				$destArray[$key] = $rows[$i];
			}
		}
		return $destArray;
	}
}
?>
