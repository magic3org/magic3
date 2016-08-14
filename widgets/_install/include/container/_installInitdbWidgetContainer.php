<?php
/**
 * index.php用コンテナクラス
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
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/_installBaseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/_installDb.php');

class _installInitdbWidgetContainer extends _installBaseWidgetContainer
{
	private $db;	// DB接続オブジェクト
	private $createTableScripts;			// テーブル作成スクリプト
	private $insertTableScripts;			// データインストールスクリプト
	private $updateTableScripts;			// テーブル更新スクリプト
	const CF_SERVER_ID = 'server_id';
	const CF_SERVER_URL = 'server_url';		// サーバURL
	const CF_SERVER_TOOL_USER = 'server_tool_user';			// 管理ツールアカウント
	const CF_SERVER_TOOL_PASSWORD = 'server_tool_password';		// 管理ツールパスワード
	const INSTALL_DT = 'install_dt';		// システムインストール日時
	const WORK_DIR = 'work_dir';			// 一時ディレクトリ
	const UPDATE_DIR = 'update';			// 追加スクリプトディレクトリ名
	const INSTALL_INFO_CLASS = 'InstallInfo';			// インストール情報クラス
	const DEFAULT_LANG		= 'default_lang';					// デフォルト言語
	const PROCESSING_ICON_FILE = '/images/system/processing.gif';		// 処理中
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->db = new _installDB();
		
		// 実行SQLスクリプトファイルの定義
/*		$this->createTableScripts = array(	array(	'filename' 		=> 'create_base.sql',					// ファイル名
													'name'			=> 'システム基本テーブル作成',				// 表示名
													'description'	=> 'システムで最小限必要なテーブルの作成'),	// 説明
											array(	'filename' 		=> 'create_std.sql',					// ファイル名
													'name'			=> 'システム標準テーブル作成',				// 表示名
													'description'	=> 'システムを通常使用するのに必要なテーブルの作成'),	// 説明
											array(	'filename' 		=> 'create_ec.sql',					// ファイル名
													'name'			=> 'Eコマース用テーブル',				// 表示名
													'description'	=> 'Eコマース用テーブルの作成'));	// 説明

		$this->insertTableScripts = array(	array(	'filename' 		=> 'insert_base.sql',					// ファイル名
													'name'			=> 'システム基本データ登録',				// 表示名
													'description'	=> 'システムで最小限必要なデータの登録'),	// 説明
											array(	'filename' 		=> 'insert_std.sql',					// ファイル名
													'name'			=> 'システム標準データ登録',				// 表示名
													'description'	=> 'システムを通常使用するのに必要なデータの登録'));	// 説明
													*/
													
		// デバッグモードで起動している場合はテスト用スクリプト追加
		if (M3_SYSTEM_DEBUG){
			$this->createTableScripts[] = array(	'filename' 		=> 'create_test.sql',					// ファイル名
													'name'			=> 'テスト用テーブル作成',				// 表示名
													'description'	=> 'テスト用に必要なテーブルの作成');	// 説明
		}
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
		return 'initdb.tmpl.html';
	}
	/**
	 * テンプレートにデータ埋め込む
	 *
	 * _setTemplate()で指定したテンプレートファイルにデータを埋め込む。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。_setTemplate()と共有。
	 * @return								なし
	 */
	function _assign($request, &$param)
	{
		$localeText = array();
		$task = $request->trimValueOf('task');
		if ($task == 'initdb'){	// DB構築
			$this->createInit($request);
			
			$localeText['title_exec_db'] = $this->_('Create Database');		// 画面タイトル(ＤＢ構築)
		} else if ($task == 'updatedb'){			// DBバージョンアップ
			$this->createUpdate($request);
			
			$localeText['title_exec_db'] = $this->_('Update Database');		// 画面タイトル(ＤＢバージョンアップ)
		}
		// テキストをローカライズ
		$localeText['msg_exec_db'] = $this->_('Wait for complete of creating database after the do action.');// 実行後は処理が完了するまでしばらくお待ちください
		$localeText['label_do'] = $this->_('Do');
		$localeText['label_target'] = $this->_('Target');
		$localeText['label_table'] = $this->_('All Tables');
//		$localeText['label_processing'] = $this->_('Processing');
		$this->setLocaleText($localeText);
	}
	/**
	 * DB初期化画面作成
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @return								なし
	 */
	function createInit($request)
	{
		$now = date("Y/m/d H:i:s");	// 現在日時
		$task = $request->trimValueOf('task');
		$act = $request->trimValueOf('act');
		$type = $request->trimValueOf('install_type');
		$from = $request->trimValueOf('from');
		
		if ($act == 'done'){
			// インストール情報オブジェクト取得
			$installInfo = $this->getInstallInfo();
			if (isset($installInfo)){
				// テーブル作成、初期化スクリプト情報取得
				$this->createTableScripts = $installInfo->getCreateTableScripts();
				$this->insertTableScripts = $installInfo->getInsertTableScripts();
				$this->updateTableScripts = $installInfo->getUpdateTableScripts();			// テーブル更新スクリプト
			}
			
			// ##### SQLスクリプトを実行 #####
			$filename = '';
			$ret = true;
			
			// タイムアウトを停止
			$this->gPage->setNoTimeout();
			
			// トランザクション開始
			//$this->gInstance->getDbManager()->startTransaction();		// PostgreSQLでは途中で落ちるのではずす
			
			// テーブル作成
			if ($ret){
				for ($i = 0; $i < count($this->createTableScripts); $i++){
					$ret = $this->gInstance->getDbManager()->execInitScriptFile($this->createTableScripts[$i]['filename'], $errors);
					if (!$ret){
						$filename = $this->createTableScripts[$i]['filename'];
						break;// 異常終了の場合
					}
				}
			}
			// 初期データインストール
			if ($ret){
				for ($i = 0; $i < count($this->insertTableScripts); $i++){
					$ret = $this->gInstance->getDbManager()->execInitScriptFile($this->insertTableScripts[$i]['filename'], $errors);
					if (!$ret){
						$filename = $this->insertTableScripts[$i]['filename'];
						break;// 異常終了の場合
					}
				}
			}
			
			// ##### 初期データ登録 #####
			if ($ret){
				// 初期値設定
				$langId = $request->trimValueOf(M3_REQUEST_PARAM_OPERATION_LANG);
				$serverId = md5($this->gEnv->getRootUrl() . time());		// サーバID
				if ($ret) $ret = $this->_db->updateSystemConfig(self::CF_SERVER_ID, $serverId);
				if ($ret) $ret = $this->_db->updateSystemConfig(self::CF_SERVER_URL, $this->gEnv->getRootUrl());
				if ($ret) $ret = $this->_db->updateSystemConfig(self::INSTALL_DT, $now);
				if ($ret) $ret = $this->_db->updateSystemConfig(M3_TB_FIELD_DB_UPDATE_DT, $now);
				//if ($ret) $ret = $this->_db->updateSystemConfig(self::WORK_DIR, M3_SYSTEM_WORK_DIR_PATH);// 一時ディレクトリ
				if ($ret) $ret = $this->_db->updateSystemConfig(self::WORK_DIR, $this->gEnv->getWorkDirPath());// 一時ディレクトリ
				if ($ret) $ret = $this->_db->updateSystemConfig(self::DEFAULT_LANG, $langId);// デフォルト言語
				
				// サーバ管理用の情報を登録
				if (defined('M3_INSTALL_ADMIN_SERVER') && M3_INSTALL_ADMIN_SERVER){			// サーバ管理システムの場合
					if ($ret) $ret = $this->_db->updateSystemConfig(self::CF_SERVER_TOOL_USER, M3_INSTALL_SERVER_TOOL_USER);// 管理ツールアカウント
					if ($ret) $ret = $this->_db->updateSystemConfig(self::CF_SERVER_TOOL_PASSWORD, M3_INSTALL_SERVER_TOOL_PASSWORD);// 管理ツールパスワード
				}
			}
			// ##### これ以降、DBへのログ出力可能 #####
			if ($ret){
				$currentVer = $this->_db->getSystemConfig(M3_TB_FIELD_DB_VERSION);
				$msg = $this->_('Database created. Database Version: %s');			// DBを作成しました。 DBバージョン: %s
				//$this->gOpeLog->writeInfo(__METHOD__, 'DBを作成しました。 DBバージョン: ' . $currentVer, 1001);
				$this->gOpeLog->writeInfo(__METHOD__, sprintf($msg, $currentVer), 1001);
			}
			
			// テーブルのパッチを当てる
			if ($ret){
				$ret = $this->updateDb($filename, $updateErrors);
				
				// エラーメッセージ追加
				if (!$ret){
					if (!isset($errors)) $errors = array();
					array_splice($errors, count($errors), 0, $updateErrors);
				}
			}
			
			// カスタムインストール用のデータをロード
			$isCustomInstall = false;		// カスタマイズしたかどうか
			if ($ret){
				if (isset($installInfo) && method_exists($installInfo, 'getCustomScripts')){
					// 追加スクリプト取得
					$scripts = $installInfo->getCustomScripts();
					
					// 順番にスクリプトを実行
					for ($i = 0; $i < count($scripts); $i++){
						// スクリプト実行
						$ret = $this->gInstance->getDbManager()->execScriptWithConvert($scripts[$i]['filename'], $customErrors);		// 正常終了の場合
				
						// エラーメッセージ追加
						if (!$ret){			// エラーのときは終了
							$filename = $scripts[$i]['filename'];
							
							if (!isset($errors)) $errors = array();
							array_splice($errors, count($errors), 0, $customErrors);
							break;
						}
					}
					if ($i == count($scripts)) $isCustomInstall = true;
				}
			}
			
			// トランザクション終了
			//$this->gInstance->getDbManager()->endTransaction();// PostgreSQLでは途中で落ちるのではずす
			
			// ウィジェット情報を更新
			if ($ret){
				for ($i = 0; $i < count($this->updateTableScripts); $i++){
					$ret = $this->gInstance->getDbManager()->execInitScriptFile($this->updateTableScripts[$i]['filename'], $errors);
					if (!$ret){
						$filename = $this->updateTableScripts[$i]['filename'];
						break;// 異常終了の場合
					}
				}
			}
			
			if ($ret){// 正常終了の場合
				// ##### サイト定義ファイルのオプション定義をクリアする #####
				$ret = $this->gConfig->removeOptionParam($msg);
				
				// DBのバージョン取得
				$currentVer = $this->_db->getSystemConfig(M3_TB_FIELD_DB_VERSION);
								
				if ($isCustomInstall){		// カスタムインストールのとき
					$type = 'custom';		// カスタムインストールのとき
					//$installMsg = 'カスタムインストールによるDB構築処理が正常に終了しました。現在のDBバージョン: ' . $currentVer;
					$msg = $this->_('Succeeded in creating database by custom install. Current Database Version: %s');	// カスタムインストールによるDB構築処理が正常に終了しました。現在のDBバージョン: %s
					$installMsg = sprintf($msg, $currentVer);
				} else {
					$type = 'all';
					//$installMsg = 'DB構築処理が正常に終了しました。現在のDBバージョン: ' . $currentVer;
					$msg = $this->_('Succeeded in creating database. Current Database Version: %s');// DB構築処理が正常に終了しました。現在のDBバージョン: %s
					$installMsg = sprintf($msg, $currentVer);
				}
				
				// ログ出力
				$this->gOpeLog->writeInfo(__METHOD__, $installMsg, 1000);
				
				// 初期設定用画面への遷移を通知
//				$guideMsg = $this->_('If you want initializing the system easily, use \'Admin Page Custom Wizard\'. Select \'Maintenance / Core Control / Admin Page Custom Wizard\' on the main menu.');	// システムの初期化を簡単に行うには「管理画面カスタムウィザード」を使用します。メインメニューから「メンテナンス / コア管理 / 管理画面カスタムウィザード」を選択します。
//				$this->gOpeLog->writeGuide(__METHOD__, $guideMsg, 3000, '', '', 'task=initwizard', true/*トップ表示*/);
				
				// 次の画面へ遷移
//				$this->gPage->redirect('?task=initother&install_type=' . $type . '&from=initdb' . '&' . M3_REQUEST_PARAM_OPERATION_LANG . '=' . $this->gEnv->getCurrentLanguage());
				$this->gPage->redirectInInstall('?task=initother&install_type=' . $type . '&from=initdb' . '&' . M3_REQUEST_PARAM_OPERATION_LANG . '=' . $this->gEnv->getCurrentLanguage());
			} else {
				//$msg = 'ＤＢ初期化に失敗しました';
				//if (!empty($filename)) $msg .= '(スクリプト名=' . $filename . ')';
				$msg = $this->_('Failed in initializing database.');			// ＤＢ初期化に失敗しました
				if (!empty($filename)) $msg .= '(' . $this->_('Script filename') . '=' . $filename . ')';// スクリプト名
				$this->setMsg(self::MSG_APP_ERR, $msg);
				
				// ログ出力
				$this->gOpeLog->writeError(__METHOD__, $msg, 1100);
			}
			// エラーメッセージを画面に表示
			if (!empty($errors)){
				foreach ($errors as $error) {
					$this->setMsg(self::MSG_APP_ERR, $error);
				}
			}
		}
		if ($from == 'inputparam'){		// 接続情報の入力画面からの遷移のとき
			//$msg = 'DBを構築します';
			$msg = $this->_('Create database.');		// DBを構築します
			$this->tmpl->addVar("_widget", "from", $from);		// 戻り画面を設定
		} else {		// DBのバージョンアップ方法の選択画面からの遷移のとき
			//$msg = 'DBを構築します<br />既存のデータはすべて削除されます';
			$msg = $this->_('Create database.<br />Clear all the existing data.');			// DBを構築します<br />既存のデータはすべて削除されます
		}
		// 画面の設定
		$this->tmpl->addVar("_widget", "task", $task);		// 実行処理を設定
		$this->tmpl->addVar("_widget", "message", $msg);		// ＤＢ構築
//		$this->tmpl->addVar('_widget', 'process_image', $this->getUrl($this->gEnv->getRootUrl() . self::PROCESSING_ICON_FILE));	// 処理中アイコン
	}
	/**
	 * DBバージョンアップ画面作成
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @return								なし
	 */
	function createUpdate($request)
	{
		$now = date("Y/m/d H:i:s");	// 現在日時
		$task = $request->trimValueOf('task');
		$act = $request->trimValueOf('act');
		$type = $request->trimValueOf('install_type');
		
		if ($act == 'done'){
			// インストール情報オブジェクト取得
			$installInfo = $this->getInstallInfo();
			if (isset($installInfo)){
				// テーブル作成、初期化スクリプト情報取得
				$this->createTableScripts = $installInfo->getCreateTableScripts();
				$this->insertTableScripts = $installInfo->getInsertTableScripts();
				$this->updateTableScripts = $installInfo->getUpdateTableScripts();			// テーブル更新スクリプト
			}
			// 更新スクリプトがあるかどうか
			$updateDb = false;
			if ($this->getUpdateScriptCount() > 0) $updateDb = true;
			
			// タイムアウトを停止
			$this->gPage->setNoTimeout();
		
			// テーブルのパッチを当てる
			if ($updateDb){
				$ret = $this->updateDb($filename, $updateErrors);
			} else {
				$ret = true;
			}
		
			// ウィジェット情報を更新(共通処理)
			if ($ret){
				for ($i = 0; $i < count($this->updateTableScripts); $i++){
					$ret = $this->gInstance->getDbManager()->execInitScriptFile($this->updateTableScripts[$i]['filename'], $errors);
					if (!$ret){
						$filename = $this->updateTableScripts[$i]['filename'];
						break;// 異常終了の場合
					}
				}
			}
			if ($ret){// 正常終了の場合
				// デフォルト値設定
				
				// 更新日時を設定
				if ($updateDb){
					$now = date("Y/m/d H:i:s");	// 現在日時
					$this->_db->updateSystemConfig(M3_TB_FIELD_DB_UPDATE_DT, $now);
				}
			
				// システム初期化を不可に設定(インストール終了)
				$this->gSystem->disableInitSystem();
			
				// ログ出力
				$currentVer = $this->_db->getSystemConfig(M3_TB_FIELD_DB_VERSION);
				if ($updateDb){		// DB更新処理ありの場合
					$msg = $this->_('Database updated. Database Version: %s');		// DB更新処理が正常に終了しました。現在のDBバージョン: %s
					//$this->gOpeLog->writeInfo(__METHOD__, 'DB更新処理が正常に終了しました。現在のDBバージョン: ' . $currentVer, 1000);
					$this->gOpeLog->writeInfo(__METHOD__, sprintf($msg, $currentVer), 1000);
				} else {
					$msg = $this->_('Widget information updated. Database Version: %s');			// ウィジェット情報を更新しました。DBバージョン: %s
					//$this->gOpeLog->writeInfo(__METHOD__, 'ウィジェット情報を更新しました。DBバージョン: ' . $currentVer, 1000);
					$this->gOpeLog->writeInfo(__METHOD__, sprintf($msg, $currentVer), 1000);
				}
			
				$type = 'all';
//				$this->gPage->redirect('?task=copyfile&install_type=' . $type . '&from=updatedb' . '&' . M3_REQUEST_PARAM_OPERATION_LANG . '=' . $this->gEnv->getCurrentLanguage());
				$this->gPage->redirectInInstall('?task=copyfile&install_type=' . $type . '&from=updatedb' . '&' . M3_REQUEST_PARAM_OPERATION_LANG . '=' . $this->gEnv->getCurrentLanguage());
			} else {
				// エラーメッセージ追加
				if (!$ret){
					if (!isset($errors)) $errors = array();
					array_splice($errors, count($errors), 0, $updateErrors);
				}
				
				//$msg = 'DB更新に失敗しました';
				//if (!empty($filename)) $msg .= '(スクリプト名=' . $filename . ')';
				$msg = $this->_('Failed in updating database');			// DB更新に失敗しました
				if (!empty($filename)) $msg .= '(' . $this->_('Script filename') . '=' . $filename . ')';// スクリプト名
				$this->setMsg(self::MSG_APP_ERR, $msg);
			
				// ログ出力
				$this->gOpeLog->writeError(__METHOD__, $msg, 1100);
			}
			// エラーメッセージを画面に表示
			if (!empty($errors)){
				foreach ($errors as $error) {
					$this->setMsg(self::MSG_APP_ERR, $error);
				}
			}
		}
			
		// 画面の設定
		$this->tmpl->addVar("_widget", "task", $task);		// 実行処理を設定
		$this->tmpl->addVar("_widget", "message", $this->_('Keep existing data, and update system and database.'));		// 既存データを残して、DBをバージョンアップします
//		$this->tmpl->addVar('_widget', 'process_image', $this->getUrl($this->gEnv->getRootUrl() . self::PROCESSING_ICON_FILE));	// 処理中アイコン
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
			
				//$ret = $this->gInstance->getDbManager()->execInitScriptFile(self::UPDATE_DIR . '/' . $files[$i], $errors);
				$ret = $this->gInstance->getDbManager()->execInitScriptFile(self::UPDATE_DIR . '/' . $files[$i], $errors);
				if ($ret){
					// 成功の場合はDBのバージョンを更新
					$this->_db->updateSystemConfig(M3_TB_FIELD_DB_VERSION, $nextVer);
					
					// 更新情報をログに残す
					$msg = $this->_('Database updated. Database Version: from %s to %s');// DBをバージョンアップしました。 DBバージョン: %sから%s
					//$this->gOpeLog->writeInfo(__METHOD__, 'DBをバージョンアップしました。 DBバージョン: ' . $foreVer . 'から'. $nextVer, 1002);
					$this->gOpeLog->writeInfo(__METHOD__, sprintf($msg, $foreVer, $nextVer), 1002);
				} else {
					$filename = $files[$i];
					break;// 異常終了の場合
				}
			} else {
				// ファイル名のエラーメッセージを出力
				$msg = $this->_('Bad script file found in files for update. Filename: %s');// DBバージョンアップ用のスクリプトファイルに不正なファイルを検出しました。 ファイル名: %s
				//$this->gOpeLog->writeWarn(__METHOD__, 'DBバージョンアップ用のスクリプトファイルに不正なファイルを検出しました。 ファイル名: ' . $files[$i], 1101);
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
	/**
	 * インストール情報オブジェクト取得
	 *
	 * @return object		インストール情報オブジェクト
	 */
	function getInstallInfo()
	{
		$infoObj = null;
		
		// 初期化情報読み込み
		$installInfoFile = M3_SYSTEM_INCLUDE_PATH . '/install/installInfo.php';
		if (file_exists($installInfoFile)){
			require_once($installInfoFile);
			$infoClass = self::INSTALL_INFO_CLASS;
			$infoObj = new $infoClass;
		} else {
			// デフォルトを検索
			$installInfoFile = M3_SYSTEM_INCLUDE_PATH . '/install/installInfo_default.php';
			if (file_exists($installInfoFile)){
				require_once($installInfoFile);
				$infoClass = self::INSTALL_INFO_CLASS;
				$infoObj = new $infoClass;
			}
		}
		return $infoObj;
	}
}
?>
