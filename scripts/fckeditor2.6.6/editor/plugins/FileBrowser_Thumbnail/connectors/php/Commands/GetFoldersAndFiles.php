<?php
/*
PHP Connector for the FCKEditor v2 File Manager
Written By Grant French, UK, Sept 2004
http://www.mcpuk.net

FCKEditor - By Frederico Caldeira Knabben
http://www.fckeditor.net

File Description:
Implements the GetFoldersAndFiles command, to list
files and folders in the current directory.
Output is in XML

2009.2.10	mbstringなしでも実行できるように修正 by naoki hirata
2010.8.25	画像のサイズを調べるときファイルサイズもチェック by naoki hirata
*/

class GetFoldersAndFiles extends command {
	function run() {
		$folders = array();
		$files = array();
		
		if ($dh = @opendir($this->path($this->actual_cwd))) {
			while (($filename = readdir($dh)) !== false) {
				if ($filename == ".") continue;
				if ($filename == "..") continue;
				
				if (function_exists('mb_convert_encoding')){
					$filename = mb_convert_encoding($filename, 'UTF-8', $this->fckphp_config['FileEncoding']);
				}
				
				if (is_dir($this->path("{$this->actual_cwd}{$filename}"))) {
					//check if$fckphp_configured not to show this folder
					
					$hide = false;
					
					if (function_exists('mb_ereg')){
						for($i = 0; $i < sizeof($this->fckphp_config['ResourceAreas'][$this->type]['HideFolders']); $i++) {
							if ( mb_ereg($this->fckphp_config['ResourceAreas'][$this->type]['HideFolders'][$i], $filename) ) {
								$hide = true;
								break;
							}
						}
					} else {
						for($i = 0; $i < sizeof($this->fckphp_config['ResourceAreas'][$this->type]['HideFolders']); $i++) {
							if ( ereg($this->fckphp_config['ResourceAreas'][$this->type]['HideFolders'][$i], $filename) ) {
								$hide = true;
								break;
							}
						}
					}
					
					if ($hide) continue;
					
					array_push($folders, $filename);
				} else {
					if (function_exists('mb_strrpos')){
						$lastdot = mb_strrpos($filename, ".");
						$ext = ($lastdot !== false) ? (mb_substr($filename, $lastdot + 1)) : "";
					} else {
						$lastdot = strrpos($filename, ".");
						$ext = ($lastdot !== false) ? (substr($filename, $lastdot + 1)) : "";
					}
					
					if (! in_array(strtolower($ext), $this->fckphp_config['ResourceAreas'][$this->type]['AllowedExtensions'])) continue;
					
					//check if $fckphp_configured not to show this file
					
					$hide = false;
					
					if (function_exists('mb_ereg')){
						for($i = 0; $i < sizeof($this->fckphp_config['ResourceAreas'][$this->type]['HideFiles']); $i++) {
							if ( mb_ereg($this->fckphp_config['ResourceAreas'][$this->type]['HideFiles'][$i], $filename) ) {
								$hide = true;
								break;
							}
						}
					} else {
						for($i = 0; $i < sizeof($this->fckphp_config['ResourceAreas'][$this->type]['HideFiles']); $i++) {
							if ( ereg($this->fckphp_config['ResourceAreas'][$this->type]['HideFiles'][$i], $filename) ) {
								$hide = true;
								break;
							}
						}
					}
					
					if ($hide) continue;
					
					array_push($files, $filename);
				}
			}
			
			closedir($dh); 
		}
		
		sort($folders);
		sort($files);
		
		header ("Content-Type: application/xml; charset=utf-8");
		echo "<?xml version=\"1.0\" encoding=\"utf-8\" ?>\n";
?>
<!DOCTYPE Connector [

<?php include "dtd/iso-lat1.ent";?>
	
	<!ELEMENT Connector	(CurrentFolder,Folders,Files)>
		<!ATTLIST Connector command CDATA "noname">
		<!ATTLIST Connector resourceType CDATA "0">
		
	<!ELEMENT CurrentFolder	(#PCDATA)>
		<!ATTLIST CurrentFolder path CDATA "noname">
		<!ATTLIST CurrentFolder url CDATA "0">
		
	<!ELEMENT Folders	(#PCDATA)>
	
	<!ELEMENT Folder	(#PCDATA)>
		<!ATTLIST Folder name CDATA "noname_dir">
		
	<!ELEMENT Files		(#PCDATA)>
		
	<!ELEMENT File		(#PCDATA)>
		<!ATTLIST File name CDATA "noname_file">
		<!ATTLIST File size CDATA "0">
] >

<Connector command="GetFoldersAndFiles" resourceType="<?php echo $this->XMLEncode($this->type); ?>">
	<CurrentFolder path="<?php echo $this->XMLEncode($this->raw_cwd); ?>" url="<?php echo $this->XMLEncode($this->url($this->actual_cwd)); ?>" />
	<Folders>
<?php
		for ($i = 0; $i < sizeof($folders); $i++) {
?>
		<Folder name="<?php echo $this->XMLEncode($folders[$i]); ?>" url="<?php echo $this->XMLEncode($this->url($folders[$i])); ?>" />
<?php
		}
?>
	</Folders>
	<Files>
<?php
		
		for ($i = 0; $i < sizeof($files); $i++) {
			$size = ceil(@filesize($this->path("{$this->actual_cwd}{$files[$i]}")) / 1024);
			
			// PHPの割り当てメモリサイズが小さいとき、ファイルサイズが大きいとgetimagesizeが落ちるのでサイズをチェック(2010/8/25)
			$width = '';
			$height = '';
			if ($size <= 1024 * 3){			// 3Mバイトまで
				$info = getimagesize($this->path("{$this->actual_cwd}{$files[$i]}"));
				if ($info !== false){
					$width = $info[0];
					$height = $info[1];
				}
			}
?>
		<File name="<?php echo $this->XMLEncode($files[$i]); ?>" url="<?php echo $this->XMLEncode($this->url($files[$i])); ?>" size="<?php echo $size; ?>" width="<?php echo $width; ?>" height="<?php echo $height; ?>" />
<?php
		}
?>
	</Files>
	</Connector>
<?php
	}
}
?>