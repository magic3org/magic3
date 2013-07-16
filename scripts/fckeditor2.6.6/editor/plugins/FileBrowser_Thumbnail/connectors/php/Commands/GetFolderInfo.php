<?php
/*
 * GetFolderInfo Class: Getting folder infomation.
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2010 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: GetFolderInfo.php 3480 2010-08-16 09:27:01Z fishbone $
 * @link       http://www.magic3.org
 */
class GetFolderInfo extends command
{
	/**
	 * コマンド実行
	 *
	 * @return 				なし
	 */
	function run()
	{
		$path = $this->path($this->actual_cwd);
		$trimPath = trim($path, '/');
		$pathArray = explode('/', $trimPath);
		$folderName = $pathArray[count($pathArray) -1];
		if (function_exists('mb_convert_encoding')) $folderName = mb_convert_encoding($folderName, 'UTF-8', $this->fckphp_config['FileEncoding']);

		// ディレクトリの使用サイズを求める
		$size = $this->calcSize($path);
		$size = ceil($size / 1024);
		
		// エラー番号設定
		$errNo = 0;
		
		header("content-type: text/xml");
		echo "<?xml version=\"1.0\" encoding=\"utf-8\" ?>\n";
?>
<Connector command="GetFolderInfo" resourceType="<?php echo $this->XMLEncode($this->type); ?>">
	<CurrentFolder path="<?php echo $this->XMLEncode($this->raw_cwd); ?>" url="<?php echo $this->XMLEncode($this->url($this->actual_cwd)); ?>" />
	<Error number="<?php echo $errNo; ?>" />
	<Folders>
		<Folder name="<?php echo $this->XMLEncode($folderName); ?>" size="<?php echo $size; ?>" />
	</Folders>
</Connector>
<?php
	}
	/**
	 * ディレクトリのディスク使用サイズを求める
	 *
	 * @param string $path		ディレクトリのパス
	 * @return 					ディスク使用サイズ
	 */
	function calcSize($path)
	{
		$size = 0;		// ディスク使用サイズ
		
		if ($dirHandle = @opendir($path)){
			while ($file = @readdir($dirHandle)) {
				if ($file == '.' || $file == '..') continue;
			
				// ディレクトリのときはサブディレクトリを計算
				$filePath = $path . '/' . $file;
				if (is_dir($filePath)){
					$size += $this->calcSize($filePath);
				} else {
					$size += @filesize($filePath);
				}
			}
			closedir($dirHandle);
		}
		return $size;
	}
}
?>