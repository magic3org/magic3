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
 * @copyright  Copyright 2006-2021 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/admin_mainBaseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/admin_mainDb.php');
require_once($gEnvManager->getCommonPath() .	'/gitRepo.php');

class admin_mainUpdatesystemWidgetContainer extends admin_mainBaseWidgetContainer
{
	private $db;	// DB接続オブジェクト
	private $step;		// 現在のアップデート段階
	private $version;			// アップデート中のバージョン
	private $preVersion;		// アップデート前の旧バージョン
	private $isCompleted;		// 実行処理の完了状態
	private $updateId;			// アップデートID
	private $packageDir;		// ソースパッケージディレクトリ名
	private $backupDir;			// バックアップディレクトリ名
	private $coreDirList = array('include', 'widgets', 'scripts', 'images');
	private $testMode;			// テストモード
	private $errFileList;			// エラーファイルリスト
	const UPDATE_INFO_URL = 'https://raw.githubusercontent.com/magic3org/magic3/master/include/version_info/update_system.json';		// バージョンアップ可能なバージョン情報取得用
	const UPDATE_INFO_URL_FOR_TEST = 'https://raw.githubusercontent.com/magic3org/magic3/master/include/version_info/_test_update_system.json';		// バージョンアップ可能なバージョン情報取得用(テスト用)
	const UPDATE_STATUS_FILE = 'update_status.json';	// バージョンアップ状態ファイル
	const BACKUP_DIR = 'backup-';		// バックアップディレクトリ名
	const PACKAGE_DIR = 'magic3org-magic3-';			// パッケージディレクトリ名
	const UPDATE_LOG_FILE = 'update.log';		// アップデートログファイル
	const SITE_DEF_FILE = 'siteDef.php';		// サイト定義ファイル
	const DB_UPDATE_DIR = 'update';			// 追加スクリプトディレクトリ名
	const INSTALL_INFO_CLASS = 'InstallInfo';			// インストール情報クラス
	const CF_TEST_MODE = 'test_mode';	// テストモード
	const MAX_ERR_FILE_COUNT = 10;		// エラーファイルの最大検出数
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DB接続オブジェクト作成
		$this->db = new admin_mainDb();
		
		// テストモードの状態を取得
		$this->testMode = $this->gSystem->getSystemConfig(self::CF_TEST_MODE);
		
		// 変数初期化
		$this->updateId = '';		// アップデートID
		$this->packageDir = '';		// ソースパッケージディレクトリ名
		$this->backupDir = '';		// バックアップディレクトリ名
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
		return 'updatesystem.tmpl.html';
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
		$act = $request->trimValueOf('act');
		if ($act == 'getinfo'){		// 最新情報取得(管理画面メニューウィジェットから参照)
			// アップデート可能なバージョンを取得
			$findUpdate = false;
			$infoSrc = $this->_readUpdateInfoFile();
			if ($infoSrc !== false){
				$versionInfo = json_decode($infoSrc, true);
			
				// バージョン番号を表示
				$versionStr = $versionInfo['version_tag'];
				if (version_compare($versionInfo['version'], M3_SYSTEM_VERSION) > 0){	// バージョンアップ可能な場合
					$findUpdate = true;
				}
			}
			// ##### ウィジェット出力処理中断 ######
			$this->gPage->abortWidget();
			
			if ($findUpdate){	// バージョンアップが可能な場合
				$info = array();
				$info['version'] = $versionInfo['version'];
				$info['version_tag'] = $versionInfo['version_tag'];
				$this->gInstance->getAjaxManager()->addData('info', $info);
				$this->gInstance->getAjaxManager()->addData('code', '1');
			} else {
				// アップデートが途中で中断している場合をチェック
				$savedStatus = array();
				$updateWorkDir = $this->gEnv->getSystemUpdateWorkPath();
				$updateStatusFile = $updateWorkDir . DIRECTORY_SEPARATOR . self::UPDATE_STATUS_FILE;
				$updateStatusStr = file_get_contents($updateStatusFile);
				if ($updateStatusStr !== false){
					$savedStatus = json_decode($updateStatusStr, true);
				}
				
				if (empty($savedStatus)){
					$this->gInstance->getAjaxManager()->addData('code', '0');
				} else {	// アップデート中のバージョン情報を返す
					$info = array();
					$info['version'] = $savedStatus['version'];
					$info['pre_version'] = $savedStatus['pre_version'];
					$info['step'] = $savedStatus['step'];
					$info['completed'] = $savedStatus['completed'];
					$this->gInstance->getAjaxManager()->addData('info', $info);
					$this->gInstance->getAjaxManager()->addData('code', '2');		// バージョンアップ中
				}
			}
		} else if ($act == 'checkenv'){		// アップデート環境チェック
			// タイムアウトを停止
			$this->gPage->setNoTimeout();
			
			// ウィジェット出力処理中断
			$this->gPage->abortWidget();
			
			// ファイル、ディレクトリのパーミッションチェック
			$this->errFileList = array();			// エラーファイルリスト
			$ret = $this->_checkPermission($this->gEnv->getSystemRootPath());
			if ($ret && count($this->errFileList) === 0){		// エラーファイルがない場合
				$this->gInstance->getAjaxManager()->addData('message', 'アクセス権チェック - OK');
				$this->gInstance->getAjaxManager()->addData('code', '1');
			} else {
				$message = 'ファイル,ディレクトリのアクセス権を確認してください<br />' . implode('<br />', $this->errFileList);
				$this->gInstance->getAjaxManager()->addData('message', $message);
				$this->gInstance->getAjaxManager()->addData('code', '0');
			}
		} else if ($act == 'updatebystep'){
			// タイムアウトを停止
			$this->gPage->setNoTimeout();
			
			// ウィジェット出力処理中断
			$this->gPage->abortWidget();
			
			// アップデート段階取得
			$step = intval($request->trimValueOf('step'));

			// アップデート段階のエラーチェック
			if ($step < 1 || 4 < $step){
				// システムアップデート用ワークディレクトリを削除
				$updateWorkDir = $this->gEnv->getSystemUpdateWorkPath();
				rmDirectory($updateWorkDir);
				
				$this->gInstance->getAjaxManager()->addData('message', 'パラメータ不正');
				$this->gInstance->getAjaxManager()->addData('code', '0');	// 異常終了
				return;
			}

			// ##### アップデート開始 #####
			$this->step = $step;
			
			// バージョンアップ可能なバージョンを取得
			$versionInfo = $this->_getVersionInfo();
			$this->version = $versionInfo['version'];	// バージョン
			$versionTag = $versionInfo['version_tag'];	// バージョンタグ

			// システムアップデート用ワークディレクトリ作成
			$updateWorkDir = $this->gEnv->getSystemUpdateWorkPath(true/*ディレクトリ作成*/);

			// アップデートのステップ状態を取得
			$savedStatus = array();
			$updateStatusFile = $updateWorkDir . DIRECTORY_SEPARATOR . self::UPDATE_STATUS_FILE;
			$updateStatusStr = file_get_contents($updateStatusFile);
			if ($updateStatusStr !== false){
				$savedStatus = json_decode($updateStatusStr, true);
			}

			// ### インストールパッケージのバージョンチェック ###
			// バージョンが異なる場合はソースパッケージディレクトリ、バックアップディレクトリを削除
			if (!empty($savedStatus) && version_compare($this->version, $savedStatus['version']) != 0){
				if (!empty($savedStatus['package_dir'])) rmDirectory($updateWorkDir . DIRECTORY_SEPARATOR . $savedStatus['package_dir']);
				if (!empty($savedStatus['backup_dir'])) rmDirectory($updateWorkDir . DIRECTORY_SEPARATOR . $savedStatus['backup_dir']);
				
				// ログを残す
				$this->_log('古いバージョンを削除しました。削除バージョン=' . $savedStatus['version'] . ', 最新バージョン=' . $this->version);
				
				// バージョン情報を初期化
				unlink($updateStatusFile);
				$savedStatus = array();
			}
			
			// 処理対象ディレクトリを取得
			if (!empty($savedStatus)){
				if (!empty($savedStatus['pre_version'])) $this->preVersion = $savedStatus['pre_version'];	// アップデート前の旧バージョン
				if (!empty($savedStatus['updateid'])) $this->updateId = $savedStatus['updateid'];
				if (!empty($savedStatus['package_dir'])) $this->packageDir = $savedStatus['package_dir'];
				if (!empty($savedStatus['backup_dir'])) $this->backupDir = $savedStatus['backup_dir'];
			}
			
			// ソース更新後の場合はインストール情報を読み込む
			if ($this->step > 2){
				// インストール情報オブジェクト取得
				$installInfo = $this->_getInstallInfo();
				if (isset($installInfo)){
					// テーブル作成、初期化スクリプト情報取得
					$this->createTableScripts = $installInfo->getCreateTableScripts();
					$this->insertTableScripts = $installInfo->getInsertTableScripts();
					$this->updateTableScripts = $installInfo->getUpdateTableScripts();			// テーブル更新スクリプト
				}
			}
			
			// ### 段階ごとの処理 ###
			// 処理が途中で中断している場合は再度実行して上書きするようにする
			if ($this->step == 1){
				// *** ステップ1 *************************************
				// 1.ソースパッケージをダウンロードしてファイルを解凍
				// 2.空のbakaupディレクトリを作成しステップ2の準備
				// ***************************************************
				
				// ディレクトリが作成されている場合は一旦削除
				if (!empty($savedStatus)){
					if (!empty($savedStatus['package_dir'])) rmDirectory($updateWorkDir . DIRECTORY_SEPARATOR . $savedStatus['package_dir']);
					if (!empty($savedStatus['backup_dir'])) rmDirectory($updateWorkDir . DIRECTORY_SEPARATOR . $savedStatus['backup_dir']);
				}

				// バージョンアップ前のバージョンを保存
				$this->preVersion = M3_SYSTEM_VERSION;
				
				$this->_saveUpdateStep($this->step, false/*開始*/);
				
				// タグでZip圧縮ファイルを取得し、指定ディレクトリに解凍。(ディレクトリは上書きされるが不要なファイルは残るので注意)
				$repo = new GitRepo('magic3org', 'magic3');
				$ret = $repo->downloadZipFileByTag($versionTag, $updateWorkDir, $destPath);
				if ($ret){
					// パッケージ名からアップデートIDを作成
					$this->packageDir = basename($destPath);		// ソースパッケージディレクトリ名
					$filenameParts = explode('-', $this->packageDir);
					$this->updateId = $filenameParts[count($filenameParts) -1];

					// パッケージ内の不要なファイル削除
					$this->_cleanupPackage($destPath);
					
					// バックアップディレクトリ作成
					$this->backupDir = self::BACKUP_DIR . $this->updateId;
					$backupDir = $updateWorkDir . DIRECTORY_SEPARATOR . $this->backupDir;		// バックアップディレクトリ名
					if (!file_exists($backupDir)) @mkdir($backupDir, M3_SYSTEM_DIR_PERMISSION, true/*再帰的に作成*/);
					
				} else {	// パッケージダウンロード失敗の場合
					$this->gInstance->getAjaxManager()->addData('message', 'パッケージ取得失敗(タグ=' . $versionTag . ')');
					$this->gInstance->getAjaxManager()->addData('code', '0');	// 異常終了
					return;
				}
				$this->_saveUpdateStep($this->step, true/*終了*/);

				$this->gInstance->getAjaxManager()->addData('message', 'ソースパッケージダウンロード - 終了');
				$this->gInstance->getAjaxManager()->addData('code', '1');
			} else if ($this->step == 2){
				// *** ステップ2 *************************************
				// 1.コアディレクトリからディレクトリを入れ替え
				//
				// この段階以降は新しく配置された新規のソースでプログラムが動くので注意!
				// ***************************************************
				// Step1が完了していることを確認
				if (empty($savedStatus) || !($savedStatus['step'] == 1 && $savedStatus['completed'])){
					$this->gInstance->getAjaxManager()->addData('message', 'Step1が完了していません');
					$this->gInstance->getAjaxManager()->addData('code', '0');	// 異常終了
					return;
				}
				
				// 一般ユーザのアクセスを停止
				$this->_closeSite(false);
				
				$this->_saveUpdateStep($this->step, false/*開始*/);
				
				// 現在のソースをバックアップディレクトリに移動し、ダウンロードしたパッケージのソースを配置する。
				// resource,templatesディレクトリは移動しない。
				for ($i = 0; $i < count($this->coreDirList); $i++){
					$moveDir = $this->coreDirList[$i];
					$oldDir = $this->gEnv->getSystemRootPath() . DIRECTORY_SEPARATOR . $moveDir;
					$newDir = $updateWorkDir . DIRECTORY_SEPARATOR . $this->packageDir . DIRECTORY_SEPARATOR . $moveDir;	// 新しいソースディレクトリ
					$backupDir = $updateWorkDir . DIRECTORY_SEPARATOR . $this->backupDir . DIRECTORY_SEPARATOR . $moveDir;
					
					// 旧ソースをバックアップディレクトリへ移動できた場合のみ新規ソースを配置する
					$ret = mvDirectory($oldDir, $backupDir);
					if ($ret) $ret = mvDirectory($newDir, $oldDir);
					
					// エラー発生の場合は終了
					if (!$ret) break;
				}
				
				// ソースの配置が完了しなかった場合は終了
				if ($i < count($this->coreDirList)){
					// 入れ替えに成功したディレクトリを元に戻す
					for ($j = $i; $j >= 0; $j--){
						$moveDir = $this->coreDirList[$j];
						$oldDir = $this->gEnv->getSystemRootPath() . DIRECTORY_SEPARATOR . $moveDir;
						$backupDir = $updateWorkDir . DIRECTORY_SEPARATOR . $this->backupDir . DIRECTORY_SEPARATOR . $moveDir;
						
						if (file_exists($backupDir)) mvDirectory($backupDir, $oldDir);
					}
					
					// 問題のあったディレクトリ取得
					$moveDir = $this->coreDirList[$i];
					$oldDir = $this->gEnv->getSystemRootPath() . DIRECTORY_SEPARATOR . $moveDir;
					$message = 'ディレクトリ入れ替え失敗(ディレクトリ=' . $oldDir . ')';
					
					// ログを残す
					$this->_log('システムアップデートエラー: ' . $message);
					
					// ソースの入れ替えに失敗した場合は以下のコードは実行されないことがある
					$this->gInstance->getAjaxManager()->addData('message', $message);
					$this->gInstance->getAjaxManager()->addData('code', '0');	// 異常終了
					return;
				}
				
				// ### URLとDB接続情報は旧ファイルを再生 ###
				$backupSiteDefFile = $updateWorkDir . DIRECTORY_SEPARATOR . $this->backupDir . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . self::SITE_DEF_FILE;
				$siteDefFile = $this->gEnv->getIncludePath() . DIRECTORY_SEPARATOR . self::SITE_DEF_FILE;
				copy($backupSiteDefFile, $siteDefFile);

				$this->_saveUpdateStep($this->step, true/*終了*/);
				
				$this->gInstance->getAjaxManager()->addData('message', 'ソースファイル更新 - 終了');
				$this->gInstance->getAjaxManager()->addData('code', '1');
			} else if ($this->step == 3){
				// *** ステップ3 *************************************
				// 1.DBのバージョンアップ
				// ***************************************************
				// Step2が完了していることを確認
				if (empty($savedStatus) || !($savedStatus['step'] == 2 && $savedStatus['completed'])){
					$this->gInstance->getAjaxManager()->addData('message', 'Step2が完了していません');
					$this->gInstance->getAjaxManager()->addData('code', '0');	// 異常終了
					return;
				}
				
				$this->_saveUpdateStep($this->step, false/*開始*/);
				
				// DBバージョンアップ
				$ret = $this->_updateDb($filename, $updateErrors);
				if (!$ret){
					$this->gInstance->getAjaxManager()->addData('message', 'DBバージョンアップ失敗');
					$this->gInstance->getAjaxManager()->addData('code', '0');	// 異常終了
					return;
				}
				
				// ウィジェット情報を更新
				for ($i = 0; $i < count($this->updateTableScripts); $i++){
					$ret = $this->gInstance->getDbManager()->execInitScriptFile($this->updateTableScripts[$i]['filename'], $errors);
					if (!$ret){
						$filename = $this->updateTableScripts[$i]['filename'];
						break;// 異常終了の場合
					}
				}

				$this->_saveUpdateStep($this->step, true/*終了*/);
				
				$this->gInstance->getAjaxManager()->addData('message', 'DB更新 - 終了');
				$this->gInstance->getAjaxManager()->addData('code', '1');
			} else if ($this->step == 4){
				// *** ステップ4 *************************************
				// 1.テンプレートの更新処理
				// ***************************************************
				// Step3が完了していることを確認
				if (empty($savedStatus) || !($savedStatus['step'] == 3 && $savedStatus['completed'])){
					$this->gInstance->getAjaxManager()->addData('message', 'Step3が完了していません');
					$this->gInstance->getAjaxManager()->addData('code', '0');	// 異常終了
					return;
				}
				
				$this->_saveUpdateStep($this->step, false/*開始*/);
				
				// テンプレートの存在チェック
				if ($this->db->getAllTemplateIdList($rows)){
					for ($i = 0; $i < count($rows); $i++){
						$templateId = $rows[$i]['tm_id'];
						$templateDir = $this->gEnv->getTemplatesPath() . DIRECTORY_SEPARATOR . $templateId;			// テンプレートのディレクトリ
						
						// テンプレートの最新ソース
						$newTemplateDir = $updateWorkDir . DIRECTORY_SEPARATOR . $this->packageDir . DIRECTORY_SEPARATOR . M3_DIR_NAME_TEMPLATES . DIRECTORY_SEPARATOR . $templateId;
						
						if (file_exists($templateDir)){	// テンプレートが存在している場合は最新のソースに更新
							if (file_exists($newTemplateDir)){	// テンプレートの最新ソースがある場合
								$backupTemplateDir = $updateWorkDir . DIRECTORY_SEPARATOR . $this->backupDir . DIRECTORY_SEPARATOR . M3_DIR_NAME_TEMPLATES . DIRECTORY_SEPARATOR . $templateId;
								mvDirectory($templateDir, $backupTemplateDir);
								mvDirectory($newTemplateDir, $templateDir);
							}
						} else {	// テンプレートが存在していない場合は最新ソースから取得
							if (file_exists($newTemplateDir)) mvDirectory($newTemplateDir, $templateDir);
						}
					}
				}
							
				$this->_saveUpdateStep($this->step, true/*終了*/);
				
				$this->gInstance->getAjaxManager()->addData('message', 'アップデート完了(新規システムバージョン=' . $versionTag . ')');
				$this->gInstance->getAjaxManager()->addData('code', '1');
				
				$msg = $this->_('System updated. System Version: from %s to %s');// システムをバージョンアップしました。 システムバージョン: %sから%s
				$this->gOpeLog->writeInfo(__METHOD__, sprintf($msg, 'v' . $this->preVersion, 'v' . $this->version), 1002);
				
				// ログを残す
				$this->_log('システムアップデート完了しました。新バージョン=' . $this->version . ', 旧バージョン=' . $this->preVersion);
				
				// システムアップデート用ワークディレクトリ削除
				rmDirectory($updateWorkDir);
				
				// 一般ユーザのアクセスを再開
				$this->_closeSite(true);
			}
		} else {
			// アップデートが途中で中断している場合をチェック
			$savedStatus = array();
			$updateWorkDir = $this->gEnv->getSystemUpdateWorkPath();
			$updateStatusFile = $updateWorkDir . DIRECTORY_SEPARATOR . self::UPDATE_STATUS_FILE;
			$updateStatusStr = file_get_contents($updateStatusFile);
			if ($updateStatusStr !== false){
				$savedStatus = json_decode($updateStatusStr, true);
			}
			
			if (empty($savedStatus)){	// アップデートが正常に終了している場合
				// アップデート情報を表示
				$this->tmpl->setAttribute('start_panel', 'visibility', 'visible');
				
				$versionStr = '<span class="error">取得不可</span>';
				$disabled = 'disabled';
			
				// アップデート可能なバージョンを取得
				$infoSrc = $this->_readUpdateInfoFile();
				if ($infoSrc !== false){
					$versionInfo = json_decode($infoSrc, true);
			
					// バージョン番号を表示
					$versionStr = $versionInfo['version_tag'];
					if (version_compare($versionInfo['version'], M3_SYSTEM_VERSION) > 0){	// バージョンアップ可能な場合
						$versionStr = '<span class="available">' . $versionStr . '</span>';
						$disabled = '';
					}
				}
			
				$this->tmpl->addVar('start_panel', 'ver_str', $versionStr);
				$this->tmpl->addVar('start_panel', 'button_disabled', $disabled);
			} else {	// アップデートが中断している場合
				// アップデート再開メッセージを表示
				$this->tmpl->setAttribute('resume_panel', 'visibility', 'visible');
				
				$resumeStep = '10';	// 再開ステップ
				$disabled = 'disabled';
					
				// 中断を再開できるかチェック
				if ((version_compare($savedStatus['version'], M3_SYSTEM_VERSION) == 0 && $savedStatus['step'] >= 2) ||	// 既にバージョン番号のみ変わった場合(ソースの入れ替え(Step2以降))
					(version_compare($savedStatus['pre_version'], M3_SYSTEM_VERSION) == 0 && $savedStatus['step'] < 2)){
					
					// 再開可能な場合
					$disabled = '';
					$resumeStep = intval($savedStatus['step']);
					if ($savedStatus['completed']) $resumeStep++;
				}
				
				$this->tmpl->addVar('_widget', 'step', $resumeStep);
				$this->tmpl->addVar('resume_panel', 'button_disabled', $disabled);
			}
		}
	}
	
	/**
	 * バージョンアップ情報取得
	 *
	 * @return array		バージョン情報(version,version_tag)の連想配列
	 */
	function _getVersionInfo()
	{
		$info = array();
		$infoSrc = $this->_readUpdateInfoFile();
		if ($infoSrc !== false){
			$versionInfo = json_decode($infoSrc, true);
		
			// バージョン番号取得
			$info['version'] = $versionInfo['version'];
			$info['version_tag'] = $versionInfo['version_tag'];
		}
		return $info;
	}
	/**
	 * バージョンアップ状態をファイル保存
	 *
	 * @param int 	$step			バージョンアップ段階(0=開始,1=ソースダウンロード、解凍,2=ソース更新,3=DB更新,4=終了)
	 * @param bool 	$isCompleted	処理が完了したかどうか
	 * @return		なし
	 */
	function _saveUpdateStep($step, $isCompleted)
	{
		$status = array();
		$status['version'] = $this->version;	// アップデート中のバージョン
		$status['pre_version'] = $this->preVersion;	// アップデート前の旧バージョン
		$status['step'] = $step;
		$status['completed'] = intval($isCompleted);
		$status['package_dir'] = $this->packageDir;						// ソースパッケージディレクトリ名
		$status['backup_dir'] = $this->backupDir;		// バックアップディレクトリ名
		$status['updateid'] = $this->updateId;							// アップデートID
		$status['date'] = date("Y/m/d H:i:s");
		
		$updateWorkDir = $this->gEnv->getSystemUpdateWorkPath();
		$updateStatusFile = $updateWorkDir . DIRECTORY_SEPARATOR . self::UPDATE_STATUS_FILE;

		file_put_contents($updateStatusFile, json_encode($status));
	}
	/**
	 * パッケージ内の不要なファイルを削除
	 *
	 * @param string $dirPath	対象ディレクトリ
	 * @return bool				処理結果(true=成功、false=失敗)
	 */
	function _cleanupPackage($dirPath)
	{
		$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dirPath));
		$files = iterator_to_array($iterator, true);

		// 不要なファイルを削除
		foreach ($files as $path) {
			if (is_file($path) && substr($path, -9) == '/.gitkeep'){ 
				unlink($path);
			}
		}
	}
	/**
	 * 一般ユーザ向けサイトクローズ制御
	 *
	 * @param bool $siteState	サイト状態(true=オープン、false=クローズ)
	 * @return 					なし
	 */
	function _closeSite($siteState)
	{
		$src = $this->gEnv->getSystemRootPath() . DIRECTORY_SEPARATOR . M3_FILENAME_INDEX;
		$saveSrc = $this->gEnv->getSystemRootPath() . DIRECTORY_SEPARATOR . '_' . M3_FILENAME_INDEX;		// 退避用パス
		if ($siteState){	// サイトオープンの場合
			renameFile($saveSrc, $src);
		} else {
			renameFile($src, $saveSrc);
		}
	}
	/**
	 * アップデート状況のログ出力
	 *
	 * @param string $message	ログメッセージ
	 * @return					なし
	 */
	function _log($message)
	{
		$logPath = $this->gEnv->getSystemLogPath(true/*ディレクトリ作成*/);
		error_log(date("Y/m/d H:i:s") . ' ' . $message . "\n", 3, $logPath . DIRECTORY_SEPARATOR . self::UPDATE_LOG_FILE);
	}
	/**
	 * DBをバージョンアップ
	 *
	 * @param string $filename		エラーがあったファイル名
	 * @param array $errors			エラーメッセージ
	 * @return bool					true=成功、false=失敗
	 */
	function _updateDb(&$filename, &$errors)
	{
		$ret = true;
		
		// SQLスクリプトディレクトリのチェック
		$dir = $this->gEnv->getSqlPath() . '/' . self::DB_UPDATE_DIR;
		$files = $this->_getUpdateScriptFiles($dir);
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
			
				$ret = $this->gInstance->getDbManager()->execInitScriptFile(self::DB_UPDATE_DIR . '/' . $files[$i], $errors);
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
	 * 追加用スクリプトファイルを取得
	 *
	 * @param string $path		読み込みパス
	 * @return array			スクリプトファイル名
	 */
	function _getUpdateScriptFiles($path)
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
	function _getInstallInfo()
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
	
	/**
	 * バージョンアップ情報ファイルを読み込む
	 *
	 * @return string		情報ファイル内容
	 */
	function _readUpdateInfoFile()
	{
		if ($this->testMode){	// テストモードの場合
			$infoSrc = file_get_contents(self::UPDATE_INFO_URL_FOR_TEST);
		} else {
			$infoSrc = file_get_contents(self::UPDATE_INFO_URL);
		}
		return $infoSrc;
	}
	
	/**
	 * ファイル、ディレクトリのアクセス権のチェック
	 *
	 * @param string $dir			ディレクトリパス
	 * @return bool					true=処理終了、false=処理失敗
	 */
	function _checkPermission($dir)
	{
		// ディレクトリの書き込み権限ない場合は終了
		if (!is_writable($dir)){
			// 最大数までパスを保存
			if (count($this->errFileList) < self::MAX_ERR_FILE_COUNT) $this->errFileList[] = $dir;
			
			return false;
		}
		
		if ($dirHandle = opendir($dir)){
			$ret = true;	// エラー検出したかどうか
			while ($file = readdir($dirHandle)) {
				if ($file == '.' || $file == '..') continue;
			
				$filePath = $dir . '/' . $file;
				if (is_dir($filePath)){
					$ret = $this->_checkPermission($filePath);
					
					// 最大数まで達していない場合は検索を続行する
					if (count($this->errFileList) < self::MAX_ERR_FILE_COUNT) $ret = true;
				} else {		// ファイルの場合
					// 書き込み権限がない場合はファイル名を保存
					if (!is_writable($filePath)){
						// 最大数までパスを保存
						if (count($this->errFileList) < self::MAX_ERR_FILE_COUNT){
							$this->errFileList[] = $filePath;
						} else {	// エラーファイルが最大数を超えた場合は終了
							$ret = false;
						}
					}
				}
				if (!$ret) break;
			}
			closedir($dirHandle);
			return $ret;
		} else {		// オープン失敗のとき
			return false;
		}
	}
}
?>
