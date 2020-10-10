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
 * @copyright  Copyright 2006-2020 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/admin_mainBaseWidgetContainer.php');
require_once($gEnvManager->getCommonPath() .	'/gitRepo.php');

class admin_mainUpdatesystemWidgetContainer extends admin_mainBaseWidgetContainer
{
	private $step;		// 現在のアップデート段階
	private $version;			// アップデート中のバージョン
	private $isCompleted;		// 実行処理の完了状態
	private $updateId;			// アップデートID
	private $packageDir;		// ソースパッケージディレクトリ名
	private $backupDir;			// バックアップディレクトリ名
	const UPDATE_INFO_URL = 'https://raw.githubusercontent.com/magic3org/magic3/master/include/version_info/update_system.json';		// バージョンアップ可能なバージョン情報取得用
	const UPDATE_STATUS_FILE = 'update_status.json';	// バージョンアップ状態ファイル
	const BACKUP_DIR = 'backup-';		// バックアップディレクトリ名
	const PACKAGE_DIR = 'magic3org-magic3-';			// パッケージディレクトリ名
	const UPDATE_LOG_FILE = 'update.log';		// アップデートログファイル
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
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
		if ($act == 'getinfo'){		// 最新情報取得
			// アップデート可能なバージョンを取得
			$findUpdate = false;
			$infoSrc = file_get_contents(self::UPDATE_INFO_URL);
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
			if ($step < 1 || 3 < $step){
				// システムバージョンアップ用ワークディレクトリを削除
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
		
			// システムバージョンアップワークディレクトリ作成
			$updateWorkDir = $this->gEnv->getSystemUpdateWorkPath(true/*ディレクトリ作成*/);
			
			// アップデートのステップ状態を取得
			$savedStatus = array();
			$updateStatusFile = $updateWorkDir . DIRECTORY_SEPARATOR . self::UPDATE_STATUS_FILE;
			$updateStatusStr = file_get_contents($updateStatusFile);
			if ($updateStatusStr !== false){
				$savedStatus = json_decode($updateStatusStr, true);
			}
			
			// バージョンが古い場合はソースパッケージディレクトリ、バックアップディレクトリを削除
			if (!empty($savedStatus) && version_compare($this->version, $savedStatus['version']) > 0){
				if (!empty($savedStatus['package_dir'])) rmDirectory($updateWorkDir . DIRECTORY_SEPARATOR . $savedStatus['package_dir']);
				if (!empty($savedStatus['backup_dir'])) rmDirectory($updateWorkDir . DIRECTORY_SEPARATOR . $savedStatus['backup_dir']);
				
				unlink($updateStatusFile);
				$savedStatus = array();
			}
			
			// ### 段階ごとの処理 ###
			// 処理が途中で中断している場合は再度実行して上書きするようにする
			if ($this->step == 1){
				$this->_saveUpdateStep($this->step, false/*開始*/);
				
				// タグでZip圧縮ファイルを取得し、指定ディレクトリに解凍。ディレクトリが存在する場合は上書きされる。
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
					$this->gInstance->getAjaxManager()->addData('message', 'パッケージ取得失敗');
					$this->gInstance->getAjaxManager()->addData('code', '0');	// 異常終了
					return;
				}
				$this->_saveUpdateStep($this->step, true/*終了*/);
				
				$this->gInstance->getAjaxManager()->addData('message', 'ソースパッケージダウンロード - 終了');
				$this->gInstance->getAjaxManager()->addData('code', '1');
			} else if ($this->step == 2){
				$this->gInstance->getAjaxManager()->addData('message', 'ソースファイル更新 - 終了');
				$this->gInstance->getAjaxManager()->addData('code', '1');
			}

		
		} else {
			$versionStr = '<span class="error">取得不可</span>';
			$disabled = 'disabled';
			
			// アップデート可能なバージョンを取得
			$infoSrc = file_get_contents(self::UPDATE_INFO_URL);
			if ($infoSrc !== false){
				$versionInfo = json_decode($infoSrc, true);
			
				// バージョン番号を表示
				$versionStr = $versionInfo['version_tag'];
				if (version_compare($versionInfo['version'], M3_SYSTEM_VERSION) > 0){	// バージョンアップ可能な場合
					$versionStr = '<span class="available">' . $versionStr . '</span>';
					$disabled = '';
				}
			}
			$this->tmpl->addVar('_widget', 'ver_str', $versionStr);
			$this->tmpl->addVar('_widget', 'button_disabled', $disabled);
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
		$infoSrc = file_get_contents(self::UPDATE_INFO_URL);
		if ($infoSrc !== false){
			$versionInfo = json_decode($infoSrc, true);
		
			// バージョンアップ可能な場合のみバージョン番号取得
			if (version_compare($versionInfo['version'], M3_SYSTEM_VERSION) > 0){	// バージョンアップ可能な場合
				$info['version'] = $versionInfo['version'];
				$info['version_tag'] = $versionInfo['version_tag'];
			}
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
}
?>
