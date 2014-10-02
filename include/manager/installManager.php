<?php
/**
 * インストールマネージャー
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2014 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once(M3_SYSTEM_INCLUDE_PATH . '/common/core.php');
require_once(M3_SYSTEM_INCLUDE_PATH . '/common/gitRepo.php');

class InstallManager extends Core
{
	private $db;						// DBオブジェクト
	private $errorMessage = array();			// エラーメッセージ
	const GITHUB_USER = 'magic3org';				// GitHubユーザ
	const GITHUB_REPO_OFFICIAL_SAMPLE = 'magic3_sample_data';				// GitHubレポジトリ
	const OFFICIAL_SAMPLE_DIR = 'release';			// サンプルデータリリースディレクトリ
	const PACKAGE_DIR_COPYFILE = 'copyfile';		// パッケージ内のファイルコピー用のディレクトリ
	const MSG_FILE_NOT_FOUND = 'ファイルが見つかりません。ファイル：';
	const MSG_FILE_NOT_TO_IDENTIFY = '不正なファイルです。ファイル：';
	const MSG_CANNOT_CREATE_DIRECTORY = 'ディレクトリが作成出来ません。ディレクトリ：';
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		global $gInstanceManager;
		
		// システムDBオブジェクト取得
		$this->db = $gInstanceManager->getSytemDbObject();
	}
	/**
	 * 公式サイトのサンプルデータリストを取得
	 *
	 * @return array		サンプルデータリスト
	 */
	function getOfficialSampleList()
	{
		$infoLists = array();
		$repo = new GitRepo(self::GITHUB_USER, self::GITHUB_REPO_OFFICIAL_SAMPLE);
		$options  = array('http' => array('user_agent'=> $_SERVER['HTTP_USER_AGENT']));
		$context  = stream_context_create($options);
		$url = $repo->getFileUrl('release/info.json');
		$data = json_decode(file_get_contents($url, 0, $context));
		if ($data === false) return $infoLists;

		$fileCount = count($data);
		for ($i = 0; $i < $fileCount; $i++){
			$info = array();
			$info['id']				= $data[$i]->{'id'};
			$info['title']			= $data[$i]->{'title'};
			$info['description'] 	= $data[$i]->{'description'};
			$info['category']		= $data[$i]->{'category'};
			$info['filename']		= $data[$i]->{'filename'};
			$info['version']		= $data[$i]->{'version'};
			$info['status']			= $data[$i]->{'status'};
			$info['thumbnail']		= $data[$i]->{'thumbnail'};
			$info['format']			= $data[$i]->{'format'};
			$infoLists[] = $info;
		}
		return $infoLists;
	}
	/**
	 * サンプルデータをインストール
	 *
	 * @param string $id	サンプルデータID
	 * @return bool			true=成功、false=失敗
	 */
	function installOffcialSample($id)
	{
		global $gEnvManager;
		
		// サンプルデータリストを所得
		$infoLists = $this->getOfficialSampleList();
		for ($i = 0; $i < count($infoLists); $i++){
			if ($infoLists[$i]['id'] == $id) break;
		}
		if ($i == count($infoLists)) return false;
		
		// 作業ディレクトリを作成
		$tmpDir = $gEnvManager->getTempDirBySession(true/*ディレクトリ作成*/);		// セッション単位の作業ディレクトリを取得
		
		// ファイルダウンロード
		$repo = new GitRepo(self::GITHUB_USER, self::GITHUB_REPO_OFFICIAL_SAMPLE);
		$archivePath = self::OFFICIAL_SAMPLE_DIR . '/' . $infoLists[$i]['filename'];
		$repo->downloadZipFile($archivePath, $tmpDir, $destPath);
		
		// パッケージコマンドファイルを取得
		$cmdList = json_decode(file_get_contents($destPath . '/index.json'));
		if ($cmdList === false) return false;
		
		// インストールコマンドを実行
		$status = $this->_execInstallCmd($destPath, $cmdList);

		// 作業ディレクトリ削除
		rmDirectory($tmpDir);
		return $status;
	}
	/**
	 * インストールコマンド実行
	 *
	 * @param string $basePath	パッケージのベースディレクトリ
	 * @param array $cmdList	実行コマンド
	 * @return bool				true=成功、false=失敗
	 */
	function _execInstallCmd($basePath, $cmdList)
	{
		global $gInstanceManager;
		global $gEnvManager;
		
		$status = true;
		$cmdCount = count($cmdList);
		for ($i = 0; $i < $cmdCount; $i++){
			$info = array();
			$no				= $cmdList[$i]->{'no'};
			$description	= $cmdList[$i]->{'description'};
			$operation 		= $cmdList[$i]->{'operation'};
			$scriptFile		= $basePath . '/' . $cmdList[$i]->{'script'};

			switch ($operation){
				case 'copyfile':			// ファイルコピーのとき
					// 定義ファイルの存在チェック
					if (!is_readable($scriptFile)){
						return false;
					}
					
					// ファイルコピー定義ファイルを解析
					$copyList = json_decode(file_get_contents($scriptFile));
					if ($copyList === false){
						return false;
					}
					
					$copyCount = count($copyList);
					for ($j = 0; $j < $copyCount; $j++){
						$fileNo = $copyList[$j]->{'no'};
						$fileSrc = $copyList[$j]->{'src'};
						$fileDest = $copyList[$j]->{'dest'};
						$fileMd5 = $copyList[$j]->{'md5'};
						
						// ファイルパス作成
						$srcFilePath = $basePath . '/' . self::PACKAGE_DIR_COPYFILE . '/' . $fileSrc;		// パッケージ内のファイル(「copyfile」ディレクトリ以下)
						if (strEndsWith($fileDest, '/')){		// ディレクトリの場合
							$destFilePath = $gEnvManager->getSystemRootPath() . '/' . $fileDest . basename($fileSrc);
						} else {
							$destFilePath = $gEnvManager->getSystemRootPath() . '/' . $fileDest;
						}
						
						// ファイルの存在チェック
						if (!file_exists($srcFilePath)){
							$this->errorMessage[] = self::MSG_FILE_NOT_FOUND . $srcFilePath;
							return false;
						}

						// ディレクトリ作成
						$destDir = dirname($destFilePath);
						if (!file_exists($destDir)){
							$ret = mkdir($destDir, M3_SYSTEM_DIR_PERMISSION, true/*再帰的*/);
							if (!$ret){
								$this->errorMessage[] = self::MSG_CANNOT_CREATE_DIRECTORY . $srcFilePath;
								return false;
							}
						}
						// ファイル移動
						if (file_exists($destFilePath)) unlink($destFilePath);
						mvFile($srcFilePath, $destFilePath);
						
						// ファイル確認
						$checkSum = md5_file($destFilePath);
						if ($fileMd5 != $checkSum){
							$this->errorMessage[] = self::MSG_FILE_NOT_TO_IDENTIFY . $srcFilePath;
							return false;
						}
					}
					break;
				case 'execsql':				// SQLスクリプト実行のとき
					// 定義ファイルの存在チェック
					if (!is_readable($scriptFile)){
						return false;
					}
					
					// SQLスクリプトの実行
					$status = $gInstanceManager->getDbManager()->execScriptWithConvert($scriptFile, $this->errorMessage);
					break;
			}
		}
		return $status;
	}
	/**
	 * エラーメッセージを取得
	 *
	 * @return array			エラーメッセージ
	 */
	function getErrorMessage()
	{
		return $this->errorMessage;
	}
}
?>
