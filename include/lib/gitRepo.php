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
class GitRepo
{
	protected $user;		// ユーザID
	protected $repo;		// レポジトリID
	protected $responseCode;	// 取得結果コード
	protected $responseText;	// 取得データ
	const URL_GET_REPO_INFO = 'https://api.github.com/repos/%s/%s';		// レポジトリ情報取得用URL
	const URL_GET_DIR_INFO = 'https://api.github.com/repos/%s/%s/contents%s';		// ディレクトリ情報取得用URL

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
		$options  = array('http' => array('user_agent'=> $_SERVER['HTTP_USER_AGENT']));
		$context  = stream_context_create($options);
		$response = file_get_contents($url, false, $context);

		// レスポンスコードを取得
		preg_match('/HTTP\/1\.[0|1|x] ([0-9]{3})/', $http_response_header[0], $matches);
		$code = $matches[1];
		$this->responseCode = (false === $response) ? 400 : $code;
		$this->responseText = $response;
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
}
?>
