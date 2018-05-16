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
 * @copyright  Copyright 2006-2018 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/admin_mainMainteBaseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/admin_mainDb.php');
require_once($gEnvManager->getCommonPath() .	'/gitRepo.php');

class admin_mainInstalldataWidgetContainer extends admin_mainMainteBaseWidgetContainer
{
	private $db;	// DB接続オブジェクト
	private $showDetail;		// 詳細表示モードかどうか
	private $sampleId;		// サンプルデータID
	private $sampleTitle;	// サンプルデータタイトル
	private $sampleDesc;	// サンプルデータ説明
	const SAMPLE_DIR = 'sample';				// サンプルSQLディレクトリ名
	const UPDATE_DIR = 'update';			// 追加スクリプトディレクトリ名
	const DOWNLOAD_FILE_PREFIX = 'DOWNLOAD:';		// ダウンロードファイルプレフィックス
	const UNTITLED_TITLE = 'タイトル未設定:';		// タイトルが取得できない場合のタイトル
		
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DB接続オブジェクト作成
		$this->db = new admin_mainDb();
		
		$this->showDetail = $this->db->canDetailConfig();		// 詳細表示かどうか
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
		return 'installdata.tmpl.html';
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
		// 入力値を取得
		$develop = $request->trimValueOf('develop');
		if (!empty($develop)) $this->showDetail = '1';
		
		$act = $request->trimValueOf('act');
		$connectOfficial = $request->trimCheckedValueOf('item_connect_official');
		$this->sampleId = $request->trimValueOf('sample_sql');
		
		if ($act == 'installsampledata'){		// サンプルデータインストールのとき
			if (strStartsWith($this->sampleId, self::DOWNLOAD_FILE_PREFIX)){		// 公式サイトからサンプルデータを取得の場合
			 	// サンプルデータインストール用アーカイブを取得しインストール
				$sampleId = str_replace(self::DOWNLOAD_FILE_PREFIX, '', $this->sampleId);
				$ret = $this->gInstance->getInstallManager()->installOffcialSample($sampleId);
				if ($ret){
					$this->setMsg(self::MSG_GUIDANCE, 'サンプルデータインストール完了しました');
				} else {
					$this->setMsg(self::MSG_APP_ERR, "サンプルデータインストールに失敗しました");
				}
			} else {
				$scriptPath = $this->gEnv->getSqlPath() . '/' . self::SAMPLE_DIR . '/' . $this->sampleId;
			
				// スクリプト実行
				if ($this->gInstance->getDbManager()->execScriptWithConvert($scriptPath, $errors)){// 正常終了の場合
					$this->setMsg(self::MSG_GUIDANCE, 'スクリプト実行完了しました');
					
					// サンプルデータのタイトルを取得
					list($title, $fileDescArray) = $this->getScriptInfo($scriptPath);
					if (empty($title)) $title = self::UNTITLED_TITLE;
					
					// ログを残す
					$msg = 'サンプルデータをインストールしました。データ名: %s';
					$this->gOpeLog->writeInfo(__METHOD__, sprintf($msg, $title), 1000);
				} else {
					$this->setMsg(self::MSG_APP_ERR, "スクリプト実行に失敗しました");
				}
				if (!empty($errors)){
					foreach ($errors as $error) {
						$this->setMsg(self::MSG_APP_ERR, $error);
					}
				}
			}
			// 現在の設定しているテンプレートを解除
			$request->unsetSessionValue(M3_SESSION_CURRENT_TEMPLATE);
		} else if ($act == 'selectfile'){		// スクリプトファイルを選択
			//$this->sampleId = $request->trimValueOf('sample_sql');
		} else if ($act == 'updatedb'){		// DBをバージョンアップ
			$this->execUpdate($request);
		} else if ($act == 'develop'){		// 開発用モード
			$this->showDetail = '1';
		}
		
		// DBのタイプ
		$dbType = $this->db->getDbType();
		
		// サンプルSQLスクリプトディレクトリのチェック
		$searchPath = $this->gEnv->getSqlPath() . '/' . self::SAMPLE_DIR;
		$files = $this->getScript($searchPath);
		sort($files);		// ファイル名をソート

		// スクリプト選択メニュー作成
		for ($i = 0; $i < count($files); $i++){
			$file = $files[$i];
			$name = preg_replace("/(.+)(\.[^.]+$)/", "$1", $file);		// 拡張子除く
			
			// デフォルトのファイル名を決定
			if (empty($this->sampleId)) $this->sampleId = $file;
			
			$selected = '';
			if ($file == $this->sampleId) $selected = 'selected';

			$row = array(
				'value'    => $this->convertToDispString($file),			// ファイル名
				'name'     => $this->convertToDispString($name),			// ファイル名
				'selected' => $selected														// 選択中かどうか
			);
			$this->tmpl->addVars('sample__sql_list', $row);
			$this->tmpl->parseTemplate('sample__sql_list', 'a');
		}
		
		// 公式サイト接続の場合は公式サイトからサンプルパッケージリストを取得
		if ($connectOfficial){
			$row = array(
				'value'    => '',			// ファイル名
				'name'     => '-- 公式サイト --',			// ファイル名
				'selected' => ''	
			);
			$this->tmpl->addVars('sample__sql_list', $row);
			$this->tmpl->parseTemplate('sample__sql_list', 'a');
			
			// 公式サイトのサンプルデータリストを取得
			$this->getSampleListFromOfficialSite();
		}
		
		// 実行スクリプトファイルのヘッダを解析
		if (!empty($this->sampleId) && !strStartsWith($this->sampleId, self::DOWNLOAD_FILE_PREFIX)){
			$scriptPath = $searchPath . '/' . $this->sampleId;
			list($this->sampleTitle, $fileDescArray) = $this->getScriptInfo($scriptPath);
			if (count($fileDescArray)) $this->sampleDesc = implode('<br />', $fileDescArray);
		}
		$content = '<h4>' . $this->convertToDispString($this->sampleTitle, true/*タグ変換なし*/) . '</h4>';
		$content .= $this->convertToDispString($this->sampleDesc, true/*タグ変換なし*/);
		$this->tmpl->addVar("_widget", "content", $content);
				
		// その他値を埋め込む
		$this->tmpl->addVar("_widget", "connect_official", $this->convertToCheckedString($connectOfficial));
		$this->tmpl->addVar("_widget", "develop", $this->showDetail);
		if (!empty($this->showDetail)){			// 開発モードの場合はDBバージョンアップ用ボタンを表示
			$panelTitle = ' (開発者用)';
			$this->tmpl->addVar("_widget", "sub_title", $this->convertToDispString($panelTitle));
			
			$this->tmpl->setAttribute('show_dbupdate', 'visibility', 'visible');		// DBバージョンアップボタン
		}
	}
	/**
	 * ディレクトリ内のスクリプトファイルを取得
	 *
	 * @param string $path		ディレクトリのパス
	 * @return array			スクリプトファイル名
	 */
	function getScript($path)
	{
		static $basePath;
		
		if (!isset($basePath)) $basePath = $path . '/';
		$files = array();
		
		if ($dirHandle = @opendir($path)){
			while ($file = @readdir($dirHandle)) {
				if ($file == '..' || strStartsWith($file, '.')) continue;	
		
				if (!$this->showDetail && strStartsWith($file, '_')) continue;		// 詳細表示モードでなければ、「_」で始まる名前のファイルは読み込まない
				
				// ディレクトリのときはサブディレクトリもチェック
				$filePath = $path . '/' . $file;
				if (is_dir($filePath)){
					$files = array_merge($files, $this->getScript($filePath));
				} else {
					$files[] = str_replace($basePath, '', $filePath);
				}
			}
			closedir($dirHandle);
		}
		return $files;
	}
	/**
	 * 公式サイトのサンプルプログラムリストを取得
	 *
	 * @return				なし
	 */
	function getSampleListFromOfficialSite()
	{
		// 公式サイトからサンプルデータリストを取得
		$sampleList = $this->gInstance->getInstallManager()->getOfficialSampleList();

		$files = array();
		$sampleCount = count($sampleList);
		for ($i = 0; $i < $sampleCount; $i++){
			$id = $sampleList[$i]['id'];
			$status = $sampleList[$i]['status'];
			$sampleId = self::DOWNLOAD_FILE_PREFIX . $id;
			$title = $sampleList[$i]['title'];
			$desc = $sampleList[$i]['description'];
			
			if ($this->showDetail){
				$name = $id . '[' . $status . ']';
			} else {
				// 安定版のみメニューに表示
				if ($status != 'stable') continue;
				$name = $id;
			}
			
			$selected = '';
			if ($sampleId == $this->sampleId){
				$selected = 'selected';
				
				$this->sampleTitle = $title;	// サンプルデータタイトル
				$this->sampleDesc = str_replace("\n", '<br />', $desc);	// サンプルデータ説明
			}

			$row = array(
				'value'    => $this->convertToDispString($sampleId),			// サンプルデータID
				'name'     => $this->convertToDispString($name),			// 表示名
				'selected' => $selected														// 選択中かどうか
			);
			$this->tmpl->addVars('sample__sql_list', $row);
			$this->tmpl->parseTemplate('sample__sql_list', 'a');
		}
	}
	/**
	 * スクリプトファイルの情報取得
	 *
	 * @param string $path		スクリプトファイルパス
	 * @return array			タイトル、説明の連想配列
	 */
	function getScriptInfo($path)
	{
		$title = '';		// タイトル
		$fileDescArray = array();		// 説明
		
		// ファイルの読み込み
		$fp = fopen($path, 'r');
		while (!feof($fp)){
		    $line = fgets($fp, 1024);
			$line = trim($line);
			
			// 空行が来たら終了
			if (empty($line)){
				break;
			} else if (strncmp($line, '--', strlen('--')) != 0){		// コメント以外の場合も終了
				break;
			}
			if (strncmp($line, '-- *', strlen('-- *')) != 0){		// ヘッダ部読み飛ばし
				// コメント記号を削除
				$line = trim(substr($line, strlen('--')));
				
				// タイトルを取得
				if (preg_match('/^\[(.*)\]$/', $line, $match)){
					$title = $match[1];	// サンプルデータタイトル
				} else {
					$fileDescArray[] = $line;
				}
			}
		}
		fclose($fp);
		
		return array($title, $fileDescArray);
	}
	/**
	 * DBバージョンアップ実行
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @return								なし
	 */
	function execUpdate($request)
	{
		$now = date("Y/m/d H:i:s");	// 現在日時
		$act = $request->trimValueOf('act');
		
		// 更新スクリプトがあるかどうか
		if ($this->getUpdateScriptCount() <= 0){
			$this->setMsg(self::MSG_GUIDANCE, 'DBは最新です');
			return;
		}

		// タイムアウトを停止
		$this->gPage->setNoTimeout();
	
		// テーブルの更新処理
		$ret = $this->updateDb($filename, $updateErrors);

		if ($ret){// 正常終了の場合
			// デフォルト値設定
			
			// 更新日時を設定
			$now = date("Y/m/d H:i:s");	// 現在日時
			$this->_db->updateSystemConfig(M3_TB_FIELD_DB_UPDATE_DT, $now);
		
			// システム初期化を不可に設定(インストール終了)
			$this->gSystem->disableInitSystem();
		
			// ログ出力
			$currentVer = $this->_db->getSystemConfig(M3_TB_FIELD_DB_VERSION);

			$msg = $this->_('Database updated. Database Version: %s');		// DB更新処理が正常に終了しました。現在のDBバージョン: %s
			$this->gOpeLog->writeInfo(__METHOD__, sprintf($msg, $currentVer), 1000);
		} else {
			// エラーメッセージ追加
			if (!$ret){
				if (!isset($errors)) $errors = array();
				array_splice($errors, count($errors), 0, $updateErrors);
			}
			
			$msg = $this->_('Failed in updating database');			// DB更新に失敗しました
			if (!empty($filename)) $msg .= '(' . $this->_('Script filename') . '=' . $filename . ')';// スクリプト名
			$this->setMsg(self::MSG_APP_ERR, $msg);
		
			// ログ出力
			$this->gOpeLog->writeError(__METHOD__, $msg, 1100);
		}
		// エラーメッセージを画面に表示
		if (empty($errors)){
			$this->setMsg(self::MSG_GUIDANCE, 'DBを更新しました');
		} else {
			foreach ($errors as $error) {
				$this->setMsg(self::MSG_APP_ERR, $error);
			}
		}
	}
	/**
	 * DBをバージョンアップ
	 *
	 * @param string $filename		エラーがあったファイル名
	 * @param array $errors			エラーメッセージ
	 * @return bool					true=成功、false=失敗
	 */
	function updateDb(&$filename, &$errors)
	{
		$ret = true;
		
		// SQLスクリプトディレクトリのチェック
		$dir = $this->gEnv->getSqlPath() . '/' . self::UPDATE_DIR;
		$files = $this->getUpdateScriptFiles($dir);
		for ($i = 0; $i < count($files); $i++){
			// ファイル名のエラーチェック
			$fileCheck = true;
			list($foreVer, $to, $nextVer, $tmp) = explode('_', basename($files[$i], '.sql'));
			
			if (!is_numeric($foreVer)) $fileCheck = false;
			if (!is_numeric($nextVer)) $fileCheck = false;
			if ($fileCheck && intval($foreVer) >= intval($nextVer)) $fileCheck = false;

			// DBのバージョンをチェックして問題なければ実行
			if ($fileCheck){
				// 現在のバージョンを取得
				$currentVer = $this->_db->getSystemConfig(M3_TB_FIELD_DB_VERSION);
				if ($foreVer != $currentVer) continue;	// バージョンが異なるときは読みとばす
			
				$ret = $this->gInstance->getDbManager()->execInitScriptFile(self::UPDATE_DIR . '/' . $files[$i], $errors);
				if ($ret){
					// 成功の場合はDBのバージョンを更新
					$this->_db->updateSystemConfig(M3_TB_FIELD_DB_VERSION, $nextVer);
					
					// 更新情報をログに残す
					$msg = $this->_('Database updated. Database Version: from %s to %s');// DBをバージョンアップしました。 DBバージョン: %sから%s
					$this->gOpeLog->writeInfo(__METHOD__, sprintf($msg, $foreVer, $nextVer), 1002);
				} else {
					$filename = $files[$i];
					break;// 異常終了の場合
				}
			} else {
				// ファイル名のエラーメッセージを出力
				$msg = $this->_('Bad script file found in files for update. Filename: %s');// DBバージョンアップ用のスクリプトファイルに不正なファイルを検出しました。 ファイル名: %s
				$this->gOpeLog->writeWarn(__METHOD__, sprintf($msg, $files[$i]), 1101);
			}
		}
		return $ret;
	}
	/**
	 * DBバージョンアップ用のスクリプトファイルの数を取得
	 *
	 * @return int			スクリプトファイル数
	 */
	function getUpdateScriptCount()
	{
		$count = 0;// ファイル数初期化
		$currentVer = $this->_db->getSystemConfig(M3_TB_FIELD_DB_VERSION);// 現在のバージョンを取得
		
		// SQLスクリプトディレクトリのチェック
		$dir = $this->gEnv->getSqlPath() . '/' . self::UPDATE_DIR;
		$files = $this->getUpdateScriptFiles($dir);
		for ($i = 0; $i < count($files); $i++){
			// ファイル名のエラーチェック
			$fileCheck = true;
			list($foreVer, $to, $nextVer, $tmp) = explode('_', basename($files[$i], '.sql'));
			
			if (!is_numeric($foreVer)) $fileCheck = false;
			if (!is_numeric($nextVer)) $fileCheck = false;
			if ($fileCheck && intval($foreVer) >= intval($nextVer)) $fileCheck = false;

			// バージョンをチェックして問題なければカウント
			if ($fileCheck){
				if (intval($foreVer) >= intval($currentVer)) $count++;
			}
		}
		return $count;
	}
	/**
	 * 追加用スクリプトファイルを取得
	 *
	 * @param string $path		読み込みパス
	 * @return array			スクリプトファイル名
	 */
	function getUpdateScriptFiles($path)
	{
		$files = array();
		if (is_dir($path)){
			$dir = dir($path);
			while (($file = $dir->read()) !== false){
				$filePath = $path . '/' . $file;
				$pathParts = pathinfo($file);
				$ext = $pathParts['extension'];		// 拡張子
					
				// ファイルかどうかチェック
				if (strncmp($file, '.', 1) != 0 && $file != '..' && is_file($filePath)
					&& strncmp($file, '_', 1) != 0 &&	// 「_」で始まる名前のファイルは読み込まない
					$ext == 'sql'){		// 拡張子が「.sql」のファイルだけを読み込む
					$files[] = $file;
				}
			}
			$dir->close();
		}
		// 取得したファイルは番号順にソートする
		sort($files);
		return $files;
	}
}
?>
