<?php
/*
PHP Connector for the FCKEditor v2 File Manager
Written By Grant French, UK, Sept 2004
http://www.mcpuk.net

FCKEditor - By Frederico Caldeira Knabben
http://www.fckeditor.net

File Description:
Implements the FileUpload command,
Checks the file uploaded is allowed, 
then moves it to the user data area. 

2008.6.8	modified by naoki hirata
2009.2.10	mbstringなしでも実行できるように修正 by naoki hirata
*/

class FileUpload extends command {
	function run() {
		$disp = $this->_run();
		
		header ("content-type: text/html; charset=UTF-8");
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>Upload Complete</title>
    </head>
    <body>
        <script type="text/javascript">
window.parent.frames['frmUpload'].OnUploadCompleted(<?php echo $disp; ?>) ;
        </script>
    </body>
</html>
<?php
	}
	
	function _run() {
		global $gOpeLogManager;
		
		if (sizeof($_FILES) == 0){		// アップロードファイルがないとき
			return "202";// fixed by naoki on 2008/12/9.
		}
		// エラーコード取得
		$errCode = $_FILES['NewFile']['error'];
		if($errCode !== UPLOAD_ERR_OK){
			$gOpeLogManager->writeError(__METHOD__, 'ファイルブラウザからのアップロードに失敗しました。エラーコード: ' . $errCode, 3001);
		}
		
		$typeconfig = $this->fckphp_config['ResourceAreas'][$this->type];
		
		if (! array_key_exists("NewFile", $_FILES)) {
			return "203";	//No parametor
		}
		
		if ($_FILES['NewFile']['size'] > ($typeconfig['MaxSize'] * 1024)) {
			return "204,'{$typeconfig['MaxSize']}'";	//Too big
		}
		
		$filename = $_FILES['NewFile']['name'];
		
		if ($filename == '') {
			return 101;
		}
		
		if (function_exists('mb_ereg')){
			if (mb_ereg($this->fckphp_config['DisableName'], $filename)) {
				return 101;
			}
		} else {
			if (ereg($this->fckphp_config['DisableName'], $filename)) {
				return 101;
			}
		}
		
		if (function_exists('mb_ereg')){
			if (mb_ereg($this->fckphp_config['DisableChars'], $filename)) {
				return 102;
			}
		} else {
			if (ereg($this->fckphp_config['DisableChars'], $filename)) {
				return 102;
			}
		}
		
		if (function_exists('mb_strrpos')){
			$lastdot = mb_strrpos($filename, ".");
			if ($lastdot === false) {
				return "205";	//No ext
			}
		
			$ext = mb_substr($filename, ($lastdot + 1));
			$filename = mb_substr($filename, 0, $lastdot);
		} else {
			$lastdot = strrpos($filename, ".");
			if ($lastdot === false) {
				return "205";	//No ext
			}
		
			$ext = substr($filename, ($lastdot + 1));
			$filename = substr($filename, 0, $lastdot);
		}
		
		if (! in_array(strtolower($ext), $typeconfig['AllowedExtensions'])) {
			return "206,'{$ext}'";	//Disallowed file extension
		}
		
		if (! $this->mapFolder($this->actual_cwd)) {
			return 110;	//Unknown error
		}
		
		$test = 0;
		$dirSizes = array();
		$globalSize = 0;
		
		if ($this->fckphp_config['DiskQuota']['Global'] != -1) {
			foreach ($this->fckphp_config['ResourceTypes'] as $resType) {
				//$path = "{$this->fckphp_config['UserFilesPath']}/{$resType}";
				$path = $this->fckphp_config['UserFilesPath'] . '/' . strtolower($resType);		// ディレクトリ名を英小文字に設定 by naoki
				
				if (is_dir($this->path($path))) {
					$dirSizes[$resType] = $this->getDirSize($path);
					
					if ($dirSizes[$resType] === false) {
						return "207,'{$resType}'";	//Unable to determine the size of a folder
					}
				} else {
					$dirSizes[$resType] = 0;
				}
				
				$globalSize += $dirSizes[$resType];
			}
			
			$globalSize += $_FILES['NewFile']['size'];
			
			if ($globalSize > ($this->fckphp_config['DiskQuota']['Global'] * 1024 * 1024)) {
				return "208,'{$this->fckphp_config['DiskQuota']['Global']}'";	//You are over the global disk quota
			}
		}
		
		if ($typeconfig['DiskQuota'] != -1) {
			if ($this->fckphp_config['DiskQuota']['Global'] == -1) {
				//$path = "{$this->fckphp_config['UserFilesPath']}/{$this->type}";
				$path = $this->fckphp_config['UserFilesPath'] . '/' . strtolower($this->type);		// ディレクトリ名を英小文字に設定 by naoki

				if (is_dir($this->path($path))) {
					$dirSizes[$this->type] = $this->getDirSize($path);
					
					if ($dirSizes[$this->type] === false) {
						return "207,'{$this->type}'";	//Unable to determine the size of a folder
					}
				} else {
					$dirSizes[$this->type] = 0;
				}
			}
			
			if (($dirSizes[$this->type] + $_FILES['NewFile']['size']) > ($typeconfig['DiskQuota'] * 1024 * 1024)) {
				return "209,'{$typeconfig['DiskQuota']}'";	//You are over the disk quota for this resource type
			}
		}
		
		$unique = $filename;
		$done = false;
		
		for ($i = 1; $i < $this->fckphp_config['AvailableMax']; $i++) {
			if (! file_exists($this->path("{$this->actual_cwd}{$unique}.{$ext}"))) {
				$done = true;
				break;
			}
			
			$unique = "{$filename}({$i})";
		}
		
		if (! $done) {
			return "210";
		}
		
		//Upload file
		if (! is_uploaded_file($_FILES['NewFile']['tmp_name'])) {
			return "211";
		}
		
		if (! move_uploaded_file($_FILES['NewFile']['tmp_name'], $this->path("{$this->actual_cwd}{$unique}.{$ext}"))) {
			$path = $this->path("{$this->actual_cwd}");
			//$gOpeLogManager->writeError(__METHOD__, 'ファイルのアップロードに失敗しました。ディレクトリに書き込み権限がない可能性があります。ディレクトリ: ' . $path, 3001);
			return "212,'{$path}'";
		}
		
		chmod($this->path("{$this->actual_cwd}{$unique}.{$ext}"), 0777);

		return ($unique == $filename) ? "0" : "1,'". "{$unique}.{$ext}'";
	}
	
	function getDirSize($dir) {
		if (($dh = @opendir($this->path($dir))) === false) {
			return false;
		}
		
		$dirSize=0;
		
		while ($file = @readdir($dh)) {
			if ($file == ".") continue;
			if ($file == "..") continue;
			
			if (function_exists('mb_convert_encoding')){
				$file = mb_convert_encoding($file, $this->fckphp_config['FileEncoding'], 'UTF-8');
			}
			
			if (is_dir($this->path("{$dir}/{$file}"))) {
				$tmp_dirSize = $this->getDirSize("{$dir}/{$file}");
				
				if ($tmp_dirSize === false) {
					@closedir($dh);
					return false;
				}
				
				$dirSize += $tmp_dirSize;
				
				continue;
			}
			
			$dirSize += filesize($this->path("{$dir}/{$file}"));
		}
		
		@closedir($dh);
		
		return $dirSize;
	}
}
?>