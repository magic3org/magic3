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
	const GITHUB_USER = 'magic3org';				// GitHubユーザ
	const GITHUB_REPO_OFFICIAL_SAMPLE = 'magic3_sample_data';				// GitHubレポジトリ
	const OFFICIAL_SAMPLE_DIR = 'release';			// サンプルデータリリースディレクトリ
	
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
		$tmpDir = $gEnvManager->getTempDirBySession();		// セッション単位の作業ディレクトリを取得
		
		// ファイルダウンロード
		$repo = new GitRepo(self::GITHUB_USER, self::GITHUB_REPO_OFFICIAL_SAMPLE);
		$archivePath = self::OFFICIAL_SAMPLE_DIR . '/' . $infoLists[$i]['filename'];
		$repo->downloadZipFile($archivePath, $tmpDir, $destPath);
		
		// パッケージ情報ファイルを取得
		$data = json_decode(file_get_contents($destPath . '/index.json'));
		if ($data === false) return false;
		
		// 作業ディレクトリ削除
		rmDirectory($tmpDir);
		return $status;
	}
}
?>
