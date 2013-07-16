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
 * @copyright  Copyright 2006-2009 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: admin_m_quizkCsvWidgetContainer.php 1931 2009-05-28 08:43:03Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/admin_m_quizkBaseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() .	'/quizkDb.php');

class admin_m_quizkCsvWidgetContainer extends admin_m_quizkBaseWidgetContainer
{
	private $db;	// DB接続オブジェクト
	private $csvData;		// CSV作成用
	private $setId;			// 現在選択中のセットID
	private $defaultSetId;		// 現在運用中のセットID
	const CFG_DEFAULT_SET_ID_KEY = 'current_set_id';		// 現在の選択中のセットID取得用キー
	const CSV_QUIZ_DEF_HEAD = 'quiz_def_';				// CSVファイル名
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->db = new quizkDb();
		
		// デフォルト値取得
		$this->defaultSetId = $this->db->getConfig(self::CFG_DEFAULT_SET_ID_KEY);		// 定義セットID
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
		return 'admin_csv.tmpl.html';
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
		$dataReplace = ($request->trimValueOf('item_replace') == 'on') ? 1 : 0;		// データの入れ替えを行うかどうか
		$this->setId = $request->trimValueOf('setid');		// 定義セットID
		$act = $request->trimValueOf('act');
		if ($act == 'upload'){		// CSVアップロード
			// ファイル名からアップロードするデータの種別を判断
			$uploadFilename = $_FILES['upfile']['name'];		// アップロードされたファイルのファイル名取得

			$skipField	= '項目ID';	// ヘッダ部認識用文字列
			$badField	= '';				// 不正なヘッダ文字列

			if (is_uploaded_file($_FILES['upfile']['tmp_name'])) {
				// ファイルを保存するサーバディレクトリを指定
				$tmpFile = tempnam($this->gEnv->getWorkDirPath(), M3_SYSTEM_WORK_UPLOAD_FILENAME_HEAD);

				// アップされたテンポラリファイルを保存ディレクトリにコピー
				$ret = move_uploaded_file($_FILES['upfile']['tmp_name'], $tmpFile);
				if ($ret){
					$addCount = 0;		// 追加項目数
					$updateCount = 0;	// 更新項目数
					$colCount = 0;		// カラム数
					$lineCount = 0;		// 行数
					$message = '';		// 追加メッセージ

					// トランザクションスタート
					$this->db->startTransaction();
				
					if ($dataReplace){		// データ入れ替えの場合は既存データを削除
						$this->db->deleteAllItems($this->setId);
					}
					// ファイルオープン
					$fp = fopen($tmpFile, "r");
				
					// データ読み込み
					$delimType = 0;		// カンマ区切り
					if ($this->gEnv->getDefaultCsvDelimCode() == "\t") $delimType = 1;		// タブ区切り
					while (($data = fgetByCsv($fp, $delimType)) !== false){
						if ($colCount == 0) $colCount = count($data);		// カラム数取得
						$lineCount++;		// 行番号更新
					
						// ヘッダ読み飛ばし
						if (trim($data[0]) == $skipField){
							continue;
						} else if (trim($data[0]) == $badField){
							$this->setAppErrorMsg('不正なヘッダを検出しました');
						}
						$newColCount = count($data);
						if ($newColCount == 0 || ($newColCount == 1 && $data[0] == "")) continue;		// 空行は読み飛ばす
						if ($colCount != $newColCount){
							$message .= $lineCount . '行目のカラム数が異常です。この行は読み飛ばしました。<br />';
							continue;		// カラム数が合わない行も読み飛ばす
						}
					
						// データをDBに格納する
						$id			= trim($data[0]);				// 項目ID
						$type		= trim($data[1]);			// 項目タイプ
						$index		= trim($data[2]);			// 項目順
						$selAnswer	= trim($data[3]);		// 選択用回答
						$answer		= trim($data[4]);			// 回答値
						$title		= trim($data[5]);			// タイトル
						$content	= trim($data[6]);			// 内容
						$visible	= trim($data[7]);			// 表示制御
					
						// データのエラーチェック
						// エラーなしの場合は、データを登録
						if ($this->getMsgCount() == 0){
							// フィールドIDを見て、新規登録か更新かを判断
							$updateRecord = false;
							if ($this->db->isExistsItem($this->setId, $id)) $updateRecord = true;
						
							// データを更新
							$ret = $this->db->updateItem($this->setId, $id, $type, $index, $selAnswer, $answer, $title, $content, $visible, $serial);
							if ($updateRecord){		// 既存項目の更新のとき
								if ($ret) $updateCount++;	// 更新項目数
							} else {			// 新規項目の追加のとき
								if ($ret) $addCount++;
							}
						}
					}
					// ファイルを閉じる
					fclose($fp);
				
					// トランザクション終了
					$ret = $this->db->endTransaction();
					if ($ret && $this->getMsgCount() == 0){
						$this->setGuidanceMsg('データを' . $addCount . '件追加しました');
						$this->setGuidanceMsg('データを' . $updateCount . '件更新しました');
						$this->setGuidanceMsg($message);
					} else {
						$this->setAppErrorMsg('データ追加に失敗しました');
					}
				}
				// テンポラリファイル削除
				unlink($tmpFile);
			} else {
				$msg = 'アップロードファイルが見つかりません(要因：アップロード可能なファイルのMaxサイズを超えている可能性があります - ' . $gSystemManager->getMaxFileSizeForUpload() . 'バイト)';
				$this->setAppErrorMsg($msg);
			}
		} else if ($act == 'download'){		// CSVダウンロード
			// ダウンロード時のデフォルトファイル名
			$down_file = self::CSV_QUIZ_DEF_HEAD . date("YmdHi") . $this->gEnv->getDefaultCsvFileSuffix();
		
			// ヘッダ部を作成
			$buf = array();
			$buf[] = '項目ID';
			$buf[] = '項目タイプ';
			$buf[] = '項目順';
			$buf[] = '選択用回答ID';
			$buf[] = '正解回答ID';
			$buf[] = 'タイトル';
			$buf[] = '内容';
			$buf[] = '表示制御';

			$delim = $this->gEnv->getDefaultCsvDelimCode();		// CSV区切りコードを取得
			$this->csvData[] = implode($delim, $buf) . $this->gEnv->getDefaultCsvNLCode();
		
			// クイズ定義データを取得
			$this->db->getAllItems($this->setId, array($this, 'fieldCsvLoop'));
			
			// CSVの出力
			ob_end_clean();
			header("Content-Type: application/force-download");
			header("Content-Disposition: attachment; filename=" . $down_file);
			header("Content-Description: File Transfer");
			header("Content-Length: " . strlen(join("", $this->csvData)));
			
			$encoding = $this->gEnv->getCsvDownloadEncoding();		// デフォルトのダウンロードエンコーディング取得
			foreach ($this->csvData as $mval){
			    echo mb_convert_encoding($mval, $encoding);
			    flush();
			    ob_flush();
			    usleep(10000);
			}
			ob_end_flush();
			exit();		// スクリプト終了
		}
		// 定義セットIDの選択メニュー作成
		$this->db->getAllSetId(array($this, 'setIdListLoop'));
	}
	/**
	 * 取得したデータをCSV形式で出力する
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function fieldCsvLoop($index, $fetchedRow, $param)
	{
		$buf = array();
		$delim = $this->gEnv->getDefaultCsvDelimCode();		// CSV区切りコードを取得
		if ($delim == "\t"){	// タブ区切りのCSVフォーマットのとき
			$buf[] = $fetchedRow['qd_id'];				// 項目ID
			$buf[] = $fetchedRow['qd_type'];			// 項目タイプ
			$buf[] = $fetchedRow['qd_index'];			// 項目順
			$buf[] = $fetchedRow['qd_select_answer_id'];	// 選択用回答
			$buf[] = $fetchedRow['qd_answer_id'];			// 回答ID
			$buf[] = $fetchedRow['qd_title'];			// タイトル
			$buf[] = $fetchedRow['qd_content'];			// 内容
			$buf[] = $fetchedRow['qd_visible'];			// 表示制御
		} else {
			$buf[] = $this->convertToEscapedCsv($fetchedRow['qd_id']);				// 項目ID
			$buf[] = $this->convertToEscapedCsv($fetchedRow['qd_type']);			// 項目タイプ
			$buf[] = $this->convertToEscapedCsv($fetchedRow['qd_index']);			// 項目順
			$buf[] = $this->convertToEscapedCsv($fetchedRow['qd_select_answer_id']);	// 選択用回答
			$buf[] = $this->convertToEscapedCsv($fetchedRow['qd_answer_id']);			// 回答ID
			$buf[] = $this->convertToEscapedCsv($fetchedRow['qd_title']);			// タイトル
			$buf[] = $this->convertToEscapedCsv($fetchedRow['qd_content']);			// 内容
			$buf[] = $this->convertToEscapedCsv($fetchedRow['qd_visible']);			// 表示制御
		}
		$this->csvData[] = implode($delim, $buf) . $this->gEnv->getDefaultCsvNLCode();
		return true;
	}
	/**
	 * セットIDリスト、取得したデータをテンプレートに設定する
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function setIdListLoop($index, $fetchedRow, $param)
	{
		$id = $fetchedRow['qs_id'];
		$name = $fetchedRow['qs_name'];
		
		$selected = '';
		if ($id == $this->setId) $selected = 'selected';		// 現在操作対象のセットID
		if ($id == $this->defaultSetId) $name .= '(現在運用中)';
		$row = array(
			'value'    => $this->convertToDispString($id),			// セットID
			'name'     => $this->convertToDispString($name),			// セットID名
			'selected' => $selected														// 選択中かどうか
		);
		$this->tmpl->addVars('set_id_list', $row);
		$this->tmpl->parseTemplate('set_id_list', 'a');
		return true;
	}
}
?>
