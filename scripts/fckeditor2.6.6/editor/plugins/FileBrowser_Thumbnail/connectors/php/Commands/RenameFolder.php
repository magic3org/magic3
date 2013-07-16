<?php
/*
PHP Connector for the FCKEditor v2 File Manager
Written By Grant French, UK, Sept 2004
http://www.mcpuk.net

FCKEditor - By Frederico Caldeira Knabben
http://www.fckeditor.net

File Description:
Implements the DeleteFile command to delete a file
in the current directory. Output is in XML

2009.2.10	mbstringなしでも実行できるように修正 by naoki hirata
*/

class RenameFolder extends command {
	var $foldername;
	var $newname;
	
	function init() {
		$this->foldername = $_GET['FolderName'];
		$this->newname = $_GET['NewName'];
	}
	
	function run() {
		$err_no = $this->_run();
		
		header ("content-type: text/xml");
		echo "<?xml version=\"1.0\" encoding=\"utf-8\" ?>\n";
?>
<Connector command="RenameFolder" resourceType="<?php echo $this->XMLEncode($this->type); ?>">
	<CurrentFolder path="<?php echo $this->XMLEncode($this->raw_cwd); ?>" url="<?php echo $this->XMLEncode($this->url($this->actual_cwd)); ?>" />
	<Error number="<?php echo $err_no; ?>" />
</Connector>
<?php
	}
	
	function _run() {
		if ($this->newname == '') {
			return 101;
		}
		
		if (function_exists('mb_ereg')){
			if (mb_ereg($this->fckphp_config['DisableName'], $this->newname)) {
				return 101;
			}
		} else {
			if (ereg($this->fckphp_config['DisableName'], $this->newname)) {
				return 101;
			}
		}
		if (function_exists('mb_ereg')){
			if (mb_ereg($this->fckphp_config['DisableChars'], $this->newname)) {
				return 102;
			}
		} else {
			if (ereg($this->fckphp_config['DisableChars'], $this->newname)) {
				return 102;
			}
		}
		
		//Check if we can create the directory here
		if (! is_writeable($this->path($this->actual_cwd))) {
			return 103;	//No permissions to rename
		}
		
		//Check if it already exists
		if (file_exists($this->path("{$this->actual_cwd}{$this->newname}"))) {
			return 104; //Folder or file already exists
		}
		
		if (! @rename($this->path("{$this->actual_cwd}{$this->foldername}"), $this->path("{$this->actual_cwd}/{$this->newname}"))) {
			return 110;
		}
		
		return 0;
	}
}
?>