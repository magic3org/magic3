<?php
/**
 * GitHubレポジトリアクセスクラス
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
require_once($gEnvManager->getLibPath()				. '/pcl/pclzip.lib.php' );

class GitRepo
{
	protected $user;		// ユーザID
	protected $repo;		// レポジトリID
	protected $responseCode;	// 取得結果コード
	protected $responseText;	// 取得データ
	protected $rateLimit;		// 接続上限
	protected $rateRemaining;	// 残り接続数
	const URL_GET_REPO_INFO = 'https://api.github.com/repos/%s/%s';		// レポジトリ情報取得用URL
	const URL_GET_DIR_INFO	= 'https://api.github.com/repos/%s/%s/contents%s';		// ディレクトリ情報取得用URL
	const URL_DOWNLOAD_FILE	= 'https://raw.githubusercontent.com/%s/%s/master/%s';		// ファイル取得用URL
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		$args = func_get_args();
		$argCount = func_num_args();
		if (method_exists($this, $func = '__construct' . $argCount)){
			call_user_func_array(array($this, $func), $args);
		}
	}
	/**
	 * コンストラクタ
	 *
	 * @param string $user		ユーザID
	 * @return 					なし
	 */
	function __construct1($user)
	{
		$this->user = $user;
	}
	/**
	 * コンストラクタ
	 *
	 * @param string $user		ユーザID
	 * @param string $repo		レポジトリID
	 * @return 					なし
	 */
	function __construct2($user, $repo)
	{
		$this->user = $user;
		$this->repo = $repo;
	}
	/**
	 * コンテンツ取得
	 *
	 * @param string $url		URL
	 * @return 					なし
	 */
	protected function _request($url)
	{
		// 変数初期化
		$this->rateLimit = 0;		// 接続上限
		$this->rateRemaining = 0;	// 残り接続数
		
		$options  = array('http' => array('user_agent'=> $_SERVER['HTTP_USER_AGENT']));
		$context  = stream_context_create($options);
		$response = @file_get_contents($url, false, $context);

		// レスポンスコードを取得
		preg_match('/HTTP\/1\.[0|1|x] ([0-9]{3})/', $http_response_header[0], $matches);
		$code = $matches[1];
		$this->responseCode = (false === $response) ? 400 : $code;
		$this->responseText = $response;
		
		$headLineCount = count($http_response_header);
		for ($i = 0; $i < $headLineCount; $i++){
			$line = $http_response_header[$i];
			if (empty($this->rateLimit)){		// 接続上限
				$ret = preg_match('/X\-RateLimit\-Limit:\s(\d+)/s', $line, $matches);
				if ($ret) $this->rateLimit = $matches[1];		// 接続上限
			}
			if (empty($this->rateRemaining)){	// 残り接続数
				$ret = preg_match('/X\-RateLimit\-Remaining:\s(\d+)/s', $line, $matches);
				if ($ret) $this->rateRemaining = $matches[1];		// 接続上限
			}
		}
	}
	/**
	 * HTTPレスポンスコードを取得
	 *
	 * @return int		HTTPレスポンスコード
	 */
	function getResponseCode()
	{
		return $this->responseCode;
	}
	/**
	 * レポジトリ情報を取得
	 *
	 * @return array		レポジトリ情報(false=失敗の場合)
	 */
	function getRepoInfo()
	{
		$this->_request(sprintf(self::URL_GET_REPO_INFO, $this->user, $this->repo));
		if ($this->responseCode != 200) return false;

		return json_decode($this->responseText);
	}
	/**
	 * ディレクトリ情報を取得
	 *
	 * @param string $path		パス
	 * @return array			ディレクトリ情報(false=失敗の場合)
	 */
	function getDirInfo($path = '/')
	{
		// パスの修正
		if (!strStartsWith($path, '/')) $path = '/' . $path;
		
		// GitHubから情報取得
		$this->_request(sprintf(self::URL_GET_DIR_INFO, $this->user, $this->repo, $path));
		if ($this->responseCode != 200) return false;

		return json_decode($this->responseText);
	}
	/**
	 * ファイル一覧を取得
	 *
	 * @param string $path		パス
	 * @param bool $recursively	再帰的に取得するかどうか
	 * @return array			ディレクトリ情報(false=失敗の場合)
	 */
	function getFileList($path = '/', $recursively = false)
	{
		$fileList = array();
		
		// パスの修正
		if (!strStartsWith($path, '/')) $path = '/' . $path;
		
		// GitHubから情報取得
		$this->_request(sprintf(self::URL_GET_DIR_INFO, $this->user, $this->repo, $path));
		if ($this->responseCode != 200) return false;

		$fileInfo = json_decode($this->responseText);
		$fileInfoCount = count($fileInfo);
		for ($i = 0; $i < $fileInfoCount; $i++){
			$info = $fileInfo[$i];
			if ($info->type == 'dir'){
				$fileList[] = $info->path . '/';
				if ($recursively){
					$ret = $this->_getFileList($info->path, $fileList);
					if (!$ret) return false;
				}
			} else {
				$fileList[] = $info->path;
			}
		}
		return $fileList;
	}
	/**
	 * ファイル一覧を取得
	 *
	 * @param string $path		パス
	 * @return array			ディレクトリ情報(false=失敗の場合)
	 */
	function _getFileList($path, &$fileList)
	{
		// GitHubから情報取得
		$this->_request(sprintf(self::URL_GET_DIR_INFO, $this->user, $this->repo, $path));
		if ($this->responseCode != 200) return false;
		
		$fileInfo = json_decode($this->responseText);
		$fileInfoCount = count($fileInfo);
		for ($i = 0; $i < $fileInfoCount; $i++){
			$info = $fileInfo[$i];
			if ($info->type == 'dir'){
				$fileList[] = $info->path . '/';
				
				// サブディレクトリに移動
				$ret = $this->_getFileList($info->path, $fileList);
				if (!$ret) return false;
			} else {
				$fileList[] = $info->path;
			}
		}
		return true;
	}
	/**
	 * ファイルをダウンロード
	 *
	 * @param string $srcFile	取得ファイル相対パス
	 * @param string $destPath	保存先ファイル
	 * @return bool				true=成功、false=失敗
	 */
	function _downloadFile($srcFile, $destPath)
	{
		$url = sprintf(self::URL_DOWNLOAD_FILE, $this->user, $this->repo, $srcFile);
		
		// 保存先にファイルが存在している場合は削除
		if (file_exists($destPath)) unlink($destPath);
		
		// GitHubからファイル取得
		$status = false;
		$readBufLength = 1024 * 8;		// 読み込みバッファサイズ
		$options  = array('http' => array('user_agent'=> $_SERVER['HTTP_USER_AGENT']));
		$context  = stream_context_create($options);
		$srcFile = fopen($url, 'rb', false, $context);
		if ($srcFile){
			// 保存先ファイルを開く
			$newFile = fopen($destPath, 'wb');
			if ($newFile){
				while (!feof($srcFile)){
					fwrite($newFile, fread($srcFile, $readBufLength), $readBufLength);
				}
				fclose($newFile);
				$status = true;			// 読み込み完了
			}
			fclose($srcFile);
		}
		return $status;
	}
	/**
	 * ディレクトリのZipアーカイブを取得
	 *
	 * @param string $srcDir		アーカイブするディレクトリ
	 * @param string $destPath		作成ファイル(拡張子「zip」)
	 * @return bool					true=成功、false=失敗
	 */
	function createZipArchive($srcDir, $destPath)
	{
		global $gEnvManager;
		
		$status = false;		// 終了状態
		$fileList = $this->getFileList($srcDir, true/*再帰的*/);
		if ($fileList === false) return false;
		
		// 作業ディレクトリを作成
		$tmpDir = $gEnvManager->getTempDirBySession();		// セッション単位の作業ディレクトリを取得
		
		// トップディレクトリ作成
		$topDir = $tmpDir . DIRECTORY_SEPARATOR . basename($srcDir);
		if (file_exists($topDir)) rmDirectory($topDir);		// 存在する場合は一旦削除
		$ret = mkdir($topDir, M3_SYSTEM_DIR_PERMISSION, true/*再帰的*/);
		if (!$ret){
			// 作業ディレクトリ削除
			rmDirectory($tmpDir);
			return false;
		}
		
		// ファイル取得
		$headLength = strlen(ltrim($srcDir, '/'));
		$fileCount = count($fileList);
		for ($i = 0; $i < $fileCount; $i++){
			$filePath = $fileList[$i];		// レポジトリ内での相対パス
			$relativeFilePath = substr($filePath, $headLength);		// アーカイブ対象内での相対パス
			
			if (strEndsWith($relativeFilePath, '/')){		// ディレクトリの場合
				// ディレクトリ作成
				$dir = $topDir . $relativeFilePath;
				$ret = mkdir($dir, M3_SYSTEM_DIR_PERMISSION, true/*再帰的*/);
				if (!$ret){
					// 作業ディレクトリ削除
					rmDirectory($tmpDir);
					return false;
				}
			} else {
				// ファイル取得
				$downloadPath = $topDir . $relativeFilePath;		// 保存先ファイルパス
				$ret = $this->_downloadFile($filePath, $downloadPath);
				if (!$ret){
					// 作業ディレクトリ削除
					rmDirectory($tmpDir);
					return false;
				}
			}
		}
		// Zipファイル格納先がなければ作成
		$zipDir = dirname($destPath);
		if (!file_exists($zipDir)) $ret = mkdir($zipDir, M3_SYSTEM_DIR_PERMISSION, true/*再帰的*/);
		
		// Zipファイル作成
		$zipFile = new PclZip($destPath);
		$ret = $zipFile->create($topDir, PCLZIP_OPT_REMOVE_PATH, dirname($topDir));
		if ($ret) $status = true;

		// 作業ディレクトリ削除
		rmDirectory($tmpDir);
		return $status;
	}
}
?>
