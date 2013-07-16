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
 * @version    SVN: $Id: admin_user_contentCategoryWidgetContainer.php 3016 2010-04-09 12:42:26Z fishbone $
 * @link       http://www.m-media.co.jp
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() . '/admin_user_contentBaseWidgetContainer.php');

class admin_user_contentCategoryWidgetContainer extends admin_user_contentBaseWidgetContainer
{
	private $langId;		// 言語ID
	private $serialNo;		// 選択中の項目のシリアル番号
	private $serialArray = array();		// 表示されている項目シリアル番号
	private $categoryItemArray = array();			// カテゴリ項目情報
	
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
		if ($task == 'category_detail'){		// 詳細画面
			return 'admin_category_detail.tmpl.html';
		} else {			// 一覧画面
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
				$ret = $this->_localDb->delCategory($delItems);
				if ($ret){		// データ削除成功のとき
					$this->setGuidanceMsg('データを削除しました');
				} else {
					$this->setAppErrorMsg('データ削除に失敗しました');
				}
			}
		}
		
		// 一覧作成
		$this->_localDb->getAllCategory($this->langId, array($this, 'itemLoop'));
		
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
		$index	= $request->trimValueOf('item_index');	// 表示順
		$itemCount = intval($request->trimValueOf('itemcount'));		// カテゴリ項目数
		$lineIds = $request->trimValueOf('item_lineid');		// 行カテゴリID
		$lineNames = $request->trimValueOf('item_linename');		// 行カテゴリ名前
		
		// 行データを取得
		for ($i = 0; $i < $itemCount; $i++){
			$line = array();
			$line['ua_item_id'] = $lineIds[$i];
			$line['ua_name'] = $lineNames[$i];
			$line['ua_index'] = $i + 1;
			$this->categoryItemArray[] = $line;
		}

		$replaceNew = false;		// データを再取得するかどうか
		if ($act == 'add'){		// 新規追加のとき
			// 入力チェック
			$this->checkInput($name, '表示名');
			$this->checkSingleByte($id, 'カテゴリ識別ID');
			$this->checkNumeric($index, '表示順');		// 表示順
			for ($i = 0; $i < $itemCount; $i++){
				$no = $i + 1;
				$this->checkInput($this->categoryItemArray[$i]['ua_name'], 'カテゴリ項目名(No.' . $no . ')');
				$this->checkSingleByte($this->categoryItemArray[$i]['ua_item_id'], 'カテゴリ項目ID(No.' . $no . ')');
			}
			
			// 同じIDがある場合はエラー
			if ($this->_localDb->getCategoryById($id, $this->langId, $row)) $this->setMsg(self::MSG_USER_ERR, 'カテゴリ識別IDが重複しています');
			
			// エラーなしの場合は、データを更新
			if ($this->getMsgCount() == 0){
				$ret = $this->_localDb->updateCategory(0/*新規*/, $id, $this->langId, $name, $index, $this->categoryItemArray, $newSerial);
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
			$this->checkSingleByte($id, 'カテゴリ識別ID');
			$this->checkNumeric($index, '表示順');		// 表示順
			for ($i = 0; $i < $itemCount; $i++){
				$no = $i + 1;
				$this->checkInput($this->categoryItemArray[$i]['ua_name'], 'カテゴリ項目名(No.' . $no . ')');
				$this->checkSingleByte($this->categoryItemArray[$i]['ua_item_id'], 'カテゴリ項目ID(No.' . $no . ')');
			}
			
			// エラーなしの場合は、データを更新
			if ($this->getMsgCount() == 0){
				$ret = $this->_localDb->updateCategory($this->serialNo, $id, $this->langId, $name, $index, $this->categoryItemArray, $newSerial);
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
			$ret = $this->_localDb->getCategoryBySerial($this->serialNo, $row);
			if ($ret) $id = $row['ua_id'];			// カテゴリ識別ID
			
			if (empty($id)){		// カテゴリIDが空のときは新規とする
				$this->serialNo = 0;
				$id		= '';		// 識別ID
				$name	= '';	// 名前
				$index = 0;	// 表示順
			} else {
				$replaceNew = true;			// データを再取得
			}
		}
		// 表示データ再取得
		if ($replaceNew){
			// タブ識別IDからデータを取得
			$ret = $this->_localDb->getCategoryById($id, $this->langId, $row);
			if ($ret){
				$this->serialNo = $row['ua_serial'];
				$name		= $row['ua_name'];
				$index	= $row['ua_index'];		// 表示順
				
				// カテゴリ項目取得
				$ret = $this->_localDb->getAllCategoryItemsById($id, $this->langId, $this->categoryItemArray);
			}
		}
		
		// カテゴリ項目一覧作成
		$this->createCategoryItemList();
		if (empty($this->categoryItemArray)) $this->tmpl->setAttribute('item_list', 'visibility', 'hidden');// カテゴリ項目情報一覧
		
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
		$this->tmpl->addVar("_widget", "index", $index);		// 表示順
		
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
		$row = array(
			'index' => $index,
			'serial' => $fetchedRow['ua_serial'],
			'id' =>	$this->convertToDispString($fetchedRow['ua_id']),		// 識別ID
			'name'     => $this->convertToDispString($fetchedRow['ua_name']),			// 表示名
			'item_index' => $fetchedRow['ua_index']				// 表示順
		);
		$this->tmpl->addVars('itemlist', $row);
		$this->tmpl->parseTemplate('itemlist', 'a');
		
		// 表示中項目のシリアル番号を保存
		$this->serialArray[] = $fetchedRow['ua_serial'];
		return true;
	}
	/**
	 * カテゴリ項目一覧を作成
	 *
	 * @return なし						
	 */
	function createCategoryItemList()
	{
		$itemCount = count($this->categoryItemArray);
		for ($i = 0; $i < $itemCount; $i++){
			$id = $this->categoryItemArray[$i]['ua_item_id'];// カテゴリ項目ID
			$name = $this->categoryItemArray[$i]['ua_name'];		// カテゴリ項目名
			
			$row = array(
				'id' => $this->convertToDispString($id),	// カテゴリ項目ID
				'name' => $this->convertToDispString($name),	// カテゴリ項目名
				'root_url' => $this->convertToDispString($this->getUrl($this->gEnv->getRootUrl()))
			);
			$this->tmpl->addVars('item_list', $row);
			$this->tmpl->parseTemplate('item_list', 'a');
		}
	}
}
?>
