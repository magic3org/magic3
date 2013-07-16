<?php
/*
PHP Connector for the FCKEditor v2 File Manager
Written By Grant French, UK, Sept 2004
http://www.mcpuk.net

FCKEditor - By Frederico Caldeira Knabben
http://www.fckeditor.net

File Description:
Implements the GetFolders command, to list the folders 
in the current directory. Output is in XML

2009.2.10	mbstringなしでも実行できるように修正 by naoki hirata
*/

class GetFolders extends command {
	function run() {
		$folders = array();
		
		if ($dh = @opendir($this->path($this->actual_cwd))) {
			while (($filename = readdir($dh)) !== false) {
				if ($filename == ".") continue;
				if ($filename == "..") continue;
				
				if (function_exists('mb_convert_encoding')){
					$filename = mb_convert_encoding($filename, 'UTF-8', $this->fckphp_config['FileEncoding']);
				}
				
				if (! is_dir($this->path("{$this->actual_cwd}{$filename}"))) continue;
				
				//check if$fckphp_configured not to show this folder
				$hide = false;
				
				if (function_exists('mb_ereg')){
					for($i = 0; $i < sizeof($this->fckphp_config['ResourceAreas'][$this->type]['HideFolders']); $i++) {
						if (mb_ereg($this->fckphp_config['ResourceAreas'][$this->type]['HideFolders'][$i], $filename)) {
							$hide = true;
							break;
						}
					}
				} else {
					for($i = 0; $i < sizeof($this->fckphp_config['ResourceAreas'][$this->type]['HideFolders']); $i++) {
						if (ereg($this->fckphp_config['ResourceAreas'][$this->type]['HideFolders'][$i], $filename)) {
							$hide = true;
							break;
						}
					}
				}
				
				if ($hide) continue;
				
				array_push($folders, $filename);
			}
			closedir($dh);
		}
		
		sort($folders);
		
		header ("content-type: text/xml");
		echo "<?xml version=\"1.0\" encoding=\"utf-8\" ?>\n";
?>
<Connector command="GetFolders" resourceType="<?php echo $this->XMLEncode($this->type); ?>">
	<CurrentFolder path="<?php echo $this->XMLEncode($this->raw_cwd); ?>" url="<?php echo $this->XMLEncode($this->url($this->actual_cwd)); ?>" />
	<Folders>
<?php
		for ($i = 0; $i < sizeof($folders); $i++) {
?>
		<Folder name="<?php echo $this->XMLEncode($folders[$i]); ?>" />
<?php
		}
?>
	</Folders>
</Connector>
<?php
	}
}
?>