<?php
/**
 * Zipアーカイブ処理クラス
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
//require_once($gEnvManager->getLibPath()				. '/pcl/pclzip.lib.php' );
require_once($gEnvManager->getLibPath()				. '/pclzip-2-8-2/pclzip.lib.php');

class ZipArchiver
{
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
	}
	/**
	 * アーカイブ
	 *
	 * @param string $srcPath				アーカイブファイルパス
	 * @param string $destDir				解凍先ディレクトリ
	 * @return bool							true=成功、false=失敗
	 */
	public function extract($srcPath, $destDir)
	{
		// zipファイルを解凍
		/*$zipFile = new PclZip($tmpFile);
		if (($zipList = $zipFile->listContent()) == 0){
			$msg = 'zipファイルの内容のリスト取得に失敗しました(要因: ' . $zipFile->errorName(true) . ')';
			$this->setAppErrorMsg($msg);							
		}*
			/*for ($i = 0; $i < sizeof($zipList); $i++) {
				for(reset($zipList[$i]); $key = key($zipList[$i]); next($zipList[$i])) {
					echo "File $i / [$key] = ".$zipList[$i][$key]."<br>";
				}
				echo "<br>";
			}*/
			
		// zipファイルを解凍
		$zipFile = new PclZip($srcPath);
		$ret = $zipFile->extract(PCLZIP_OPT_PATH, $destDir);
		/*if (($zipList = $zipFile->listContent()) == 0){
			$msg = 'zipファイルの内容のリスト取得に失敗しました(要因: ' . $zipFile->errorName(true) . ')';
			$this->setAppErrorMsg($msg);							
		}*/
		//$msg = 'ファイルのアップロードに失敗しました(要因: ' . $zipFile->errorName(true) . ')';
		//$this->setAppErrorMsg($msg);
		return $ret;
	}
}
?>
