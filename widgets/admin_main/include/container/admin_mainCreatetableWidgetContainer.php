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
 * @version    SVN: $Id: admin_mainCreatetableWidgetContainer.php 2355 2009-09-25 05:34:08Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() . '/admin_mainTableBaseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/admin_tableDb.php');

class admin_mainCreatetableWidgetContainer extends admin_mainTableBaseWidgetContainer
{
	private $db;	// DB接続オブジェクト
	private $serialNo;	// シリアルNo
	private $serialArray = array();		// 表示されているコンテンツシリアル番号
	private $tableId;		// 現在選択中のテーブル
	private $dataType;	// データ型
	private $createScript;		// テーブル作成スクリプト
	private $fieldScript;		// フィールド名定義
	const DEFAULT_FIELD_NAME = 'serialno';		// デフォルトのフィールド名
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DB接続オブジェクト作成
		$this->db = new admin_tableDb();
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
		return 'createtable.tmpl.html';
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
		global $gEnvManager;
		
		$act = $request->trimValueOf('act');
		$this->serialNo = $request->trimValueOf('serial');		// 選択項目のシリアル番号
		$tableName = $request->trimValueOf('item_tableid');		// テーブル名
		$fieldName = $request->trimValueOf('add_id');		// フィールド名
		$dispName = $request->trimValueOf('add_name');		// 項目表示名
		$this->dataType = $request->trimValueOf('add_type');// データ型
		$defaultValue = $request->trimValueOf('add_default');// デフォルト値
		$this->tableId = $request->trimValueOf('tableid');		// テーブルID
		
		$reloadData = false;		// データの再読み込み
		if ($act == 'update'){		// 行更新のとき
			// 入力データエラーチェック
			$listedItem = explode(',', $request->trimValueOf('seriallist'));
			for ($i = 0; $i < count($listedItem); $i++){
				$line = array();
				
				// シリアル番号
				$line['td_serial'] = $listedItem[$i];
				
				// ID
				$itemName = 'item' . $i . '_id';
				$itemValue = $request->trimValueOf($itemName);
				$line['td_id'] = $itemValue;
				$this->checkSingleByte($itemValue, 'ID');

				// 名前
				$itemName = 'item' . $i . '_name';
				$itemValue = $request->trimValueOf($itemName);
				$line['td_name'] = $itemValue;
				$this->checkInput($itemValue, '名前');
				
				// データタイプ
				$itemName = 'item' . $i . '_type';
				$itemValue = $request->trimValueOf($itemName);
				$line['td_type'] = $itemValue;
				$this->checkSingleByte($itemValue, 'データタイプ');
				
				// デフォルト値
				$itemName = 'item' . $i . '_default';
				$itemValue = $request->trimValueOf($itemName);
				$line['td_default_value'] = $itemValue;
				$this->checkSingleByte($itemValue, 'デフォルト値', true);
				
				// 行を追加
				$listRows[] = $line;
			}
			// エラーなしの場合は、データを更新
			if ($this->getMsgCount() == 0){
				$ret = $this->db->updateTableField($listRows);
				if ($ret){		// 更新成功のとき
					$this->setMsg(self::MSG_GUIDANCE, 'データを更新しました');
					$listRows = array();		// データを初期化
				} else {
					$this->setMsg(self::MSG_APP_ERR, 'データ更新に失敗しました');
				}
			}
		} else if ($act == 'add'){		// フィールド新規追加のとき
			// 入力チェック
			$this->checkSingleByte($this->tableId, 'テーブル名');
			$this->checkSingleByte($fieldName, 'フィールド名');
			$this->checkSingleByte($this->dataType, 'データ型');

			// テーブル名エラーチェック
			if ($this->getMsgCount() == 0){
				// フィールド名形式チェック(「_」で始まる名前は禁止)
				if (strncmp($fieldName, '_', 1) == 0) $this->setMsg(self::MSG_USER_ERR, '「_」で始まるフィールド名は指定できません');
				
				// フィールド名重複チェック
				if ($this->db->isExistsField($this->tableId, $fieldName)) $this->setMsg(self::MSG_USER_ERR, 'フィールド名が重複しています');
			}
			
			// エラーなしの場合は、データを登録
			if ($this->getMsgCount() == 0){
				// データデフォルト値の設定
				if (strncasecmp($this->dataType, 'text', strlen('text')) == 0){
					// text型はデフォルト値が設定できない(MySQL制約)
					$defaultValue = '';
				} else if (strncasecmp($this->dataType, 'boolean', strlen('boolean')) == 0){// bool型のとき
					if (strncasecmp($defaultValue, 'true', strlen('true')) != 0 && 
							strncasecmp($defaultValue, 'false', strlen('false')) != 0){
						$defaultValue = 'false';
					}
				} else if (strncasecmp($this->dataType, 'int', strlen('int')) == 0){// int型のとき
					if (empty($defaultValue)) $defaultValue = 0;
				}
				$ret = $this->db->addTableField($this->tableId, $fieldName, $dispName, $this->dataType, $defaultValue);
				if ($ret){		// データ追加成功のとき
					$this->setMsg(self::MSG_GUIDANCE, 'データを追加しました');
					
					// 追加行クリア
					$fieldName = '';		// フィールド名
					$dispName = '';			// 項目表示名
					$this->dataType = '';// データ型
					$defaultValue = '';// デフォルト値
					$reloadData = true;		// データの再読み込み
				} else {
					$this->setMsg(self::MSG_APP_ERR, 'データ追加に失敗しました');
				}
			}
		} else if ($act == 'delete'){		// 削除のとき
			$ret = $this->db->deleteTableField($this->serialNo);
			if ($ret){		// データ削除成功のとき
				$this->setMsg(self::MSG_GUIDANCE, 'データを削除しました');
			} else {
				$this->setMsg(self::MSG_APP_ERR, 'データ削除に失敗しました');
			}
		} else if ($act == 'addtable'){		// テーブル名追加のとき
			$this->checkSingleByte($tableName, 'テーブル名');
		
			// テーブルの実体があるかどうか確認
			$tableExists = $this->db->isTableExists($tableName);
			if ($tableExists) $this->setMsg(self::MSG_APP_ERR, '同名のテーブルがすでに存在します');
			
			// すでに登録済みのテーブルかどうか確認
			if ($this->getMsgCount() == 0){
				if ($this->db->isExistsField($tableName, '')) $this->setMsg(self::MSG_APP_ERR, '同名のテーブルがすでに追加されています');
			}
			
			// エラーなしの場合は、データを登録
			if ($this->getMsgCount() == 0){
				// テーブル名追加
				$ret = $this->db->addTableField($tableName, '', '', '', '');
				if ($ret){		// データ追加成功のとき
					$this->setMsg(self::MSG_GUIDANCE, 'テーブルを追加しました');
				
				/*
					// 追加行クリア
					$fieldName = '';		// フィールド名
					$dispName = '';			// 項目表示名
					$this->dataType = '';// データ型
					$defaultValue = '';// デフォルト値*/
					$reloadData = true;		// データの再読み込み
				} else {
					$this->setMsg(self::MSG_APP_ERR, 'テーブル追加に失敗しました');
				}
			}
		} else if ($act == 'seltable'){		// テーブル名選択のとき
			$fieldName = '';		// フィールド名
			$dispName = '';			// 項目表示名
			$this->dataType = '';// データ型
			$defaultValue = '';// デフォルト値
		} else if ($act == 'createtable'){		// テーブル作成のとき
			// 隠れキーフィールドとして「_serial」を使う
			$this->fieldScript = '';
			$this->createScript = 'DROP TABLE IF EXISTS ' . $this->tableId . ';' . M3_NL;
			$this->createScript .= 'CREATE TABLE ' . $this->tableId . ' (' . M3_NL;
			$this->createScript .= '    _serial    INT AUTO_INCREMENT,' . M3_NL;
			$this->db->getTableDef($this->tableId, array($this, 'createScriptLoop'));
			$this->createScript .= '    PRIMARY KEY(_serial)' . M3_NL;
			$this->createScript .= ') TYPE=innodb;' . M3_NL;
			
			// テーブル定義をファイルに書き出す
			$scriptPath = $gEnvManager->getTablesPath() . '/' . $this->tableId . '/install.sql';
			$ret = writeFile($scriptPath, $this->createScript);
			
			// フィールド名をphpスクリプトに書き出す
			if ($ret){
				$preFieldScript = '<?php' . M3_NL;
				$preFieldScript .= 'defined(\'M3_SYSTEM\') or die(\'Access error: Direct access denied.\');' . M3_NL;
				$preFieldScript .= 'global $TABLE_FIELDS;' . M3_NL;
				$preFieldScript .= '$TABLE_FIELDS = array();' . M3_NL;
				$this->fieldScript = $preFieldScript . $this->fieldScript . '?>';
				$phpPath = $gEnvManager->getTablesPath() . '/' . $this->tableId . '/index.php';
				$ret = writeFile($phpPath, $this->fieldScript);
			}
			if ($ret){
				// スクリプト実行
				if ($this->gInstance->getDbManager()->execScriptWithConvert($scriptPath, $errors)){// 正常終了の場合
					$this->setMsg(self::MSG_GUIDANCE, 'ファイルに書き出しました');
					$this->setMsg(self::MSG_GUIDANCE, 'テーブル作成完了しました');
				} else {
					$this->setMsg(self::MSG_APP_ERR, "ファイルに書き出しました");
					$this->setMsg(self::MSG_APP_ERR, "テーブル作成に失敗しました");
				}
				if (!empty($errors)){
					foreach ($errors as $error) {
						$this->setMsg(self::MSG_APP_ERR, $error);
					}
				}
			} else {
				$this->setMsg(self::MSG_APP_ERR, 'ファイルに書き出し失敗しました');
			}
		} else {
			$reloadData = true;		// データの再読み込み
		}
		// テーブル名一覧取得
		$this->db->getAllTableIdList(array($this, 'tableIdLoop'));

		// テーブル定義一覧表示
		if (empty($this->tableId)){// テーブル新規追加のとき
			$this->tmpl->setAttribute('new_table_id', 'visibility', 'visible');

			// 値を戻す
			$this->tmpl->addVar("new_table_id", "table_name", $tableName);
		} else {
			// 一覧作成
			$this->db->getTableDef($this->tableId, array($this, 'fieldListLoop'));
			$this->tmpl->setAttribute('table_def', 'visibility', 'visible');
			
			if (count($this->serialArray) > 0) $this->tmpl->setAttribute('fieldlistbody', 'visibility', 'visible');
			
			// テーブル存在チェック
			$tableExists = $this->db->isTableExists($this->tableId);
			if ($tableExists){
				$this->tmpl->addVar('_widget', 'create_table_msg', 'このテーブルを再構築しますか?\n既存データはすべて消去されます。');
				$this->tmpl->addVar('table_def', 'table_status', 'テーブル状態：作成済');
				$this->tmpl->addVar('table_def', 'create_table_btn_label', 'テーブルを再構築');
			} else {
				$this->tmpl->addVar('_widget', 'create_table_msg', 'このテーブルを新規作成しますか?');
				$this->tmpl->addVar('table_def', 'table_status', 'テーブル状態：未作成');
				$this->tmpl->addVar('table_def', 'create_table_btn_label', 'テーブルを新規作成');
			}
		}
		// 値を戻す
//		$this->tmpl->addVar("_widget", "table_id", $this->tableId);
		$this->tmpl->addVar("table_def", "field_id", $fieldName);// フィールド名
		$this->tmpl->addVar("table_def", "field_name", $dispName);	// 項目表示名
		$this->tmpl->addVar("table_def", "field_default", $defaultValue);// デフォルト値
		$this->tmpl->addVar("_widget", "serial_list", implode(',', $this->serialArray));// 表示項目のシリアル番号を設定
	}
	/**
	 * 取得したデータをテンプレートに設定する
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function fieldListLoop($index, $fetchedRow, $param)
	{
		// 行カラーの設定
		$lineColor = '';
		if ($index % 2 != 0){
			$lineColor = 'class="even"';		// 偶数行
		}
		$row = array(
			'line_color' => $lineColor,											// 行のカラー
			'index' => $index,													// 項目インデックス番号
			'no' => $index + 1,													// 行番号
			'serial' => $this->convertToDispString($fetchedRow['td_serial']),			// シリアル番号
			'id' => $this->convertToDispString($fetchedRow['td_id']),			// ID
			'name' => $this->convertToDispString($fetchedRow['td_name']),			// 項目表示名
			'type' => $this->convertToDispString($fetchedRow['td_type']),		// データ型
			'default' => $this->convertToDispString($fetchedRow['td_default_value']),		// デフォルト値
			'selected' => $selected												// 項目選択用ラジオボタン
		);
		$this->tmpl->addVars('fieldlist', $row);
		$this->tmpl->parseTemplate('fieldlist', 'a');
		
		// 表示中のコンテンツIDを保存
		$this->serialArray[] = $fetchedRow['td_serial'];
		return true;
	}
	/**
	 * テーブル作成用スクリプトを作成する
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function createScriptLoop($index, $fetchedRow, $param)
	{
		$dataType = $fetchedRow['td_type'];
		if (strncasecmp($dataType, 'text', strlen('text')) == 0 ||
			strncasecmp($dataType, 'char', strlen('char')) == 0 ||
			strncasecmp($dataType, 'varchar', strlen('varchar')) == 0 ||
			strncasecmp($dataType, 'timestamp', strlen('timestamp')) == 0){
			$defalut = '\'' . $fetchedRow['td_default_value'] . '\'';
		} else {
			$defalut = $fetchedRow['td_default_value'];
		}
		
	    $this->createScript .= '    ' . $fetchedRow['td_id'] . '    ' . $fetchedRow['td_type'] . '    DEFAULT ' . $defalut . '    NOT NULL,' . M3_NL;
		
		// フィールド名
		$this->fieldScript .= '$TABLE_FIELDS[' . $index . ']["id"] = "' . $fetchedRow['td_id'] . '";' . M3_NL;
		$this->fieldScript .= '$TABLE_FIELDS[' . $index . ']["name"] = "' . $fetchedRow['td_name'] . '";' . M3_NL;
		$this->fieldScript .= '$TABLE_FIELDS[' . $index . ']["type"] = "' . $fetchedRow['td_type'] . '";' . M3_NL;
		$this->fieldScript .= '$TABLE_FIELDS[' . $index . ']["default"] = "' . $fetchedRow['td_default_value'] . '";' . M3_NL;
		return true;
	}
	/**
	 * テーブル名一覧を作成
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function tableIdLoop($index, $fetchedRow, $param)
	{
		$selected = '';
		if ($fetchedRow['td_table_id'] == $this->tableId){
			$selected = 'selected';
		}

		$row = array(
			'value'    => $this->convertToDispString($fetchedRow['td_table_id']),
			'name'     => $this->convertToDispString($fetchedRow['td_table_id']),
			'selected' => $selected														// 選択中かどうか
		);
		$this->tmpl->addVars('table_id_list', $row);
		$this->tmpl->parseTemplate('table_id_list', 'a');
		return true;
	}
}
?>
