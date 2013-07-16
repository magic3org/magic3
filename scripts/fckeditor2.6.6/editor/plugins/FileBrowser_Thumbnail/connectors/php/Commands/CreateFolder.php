<?php
/*
PHP Connector for the FCKEditor v2 File Manager
Written By Grant French, UK, Sept 2004
http://www.mcpuk.net

FCKEditor - By Frederico Caldeira Knabben
http://www.fckeditor.net

File Description:
Implements the CreateFolder command to make a new folder
in the current directory. Output is in XML

2009.2.10	mbstringなしでも実行できるように修正 by naoki hirata
*/

class CreateFolder extends command {
	var $newfolder;
	
	function init() {
		$this->newfolder = $_GET['NewFolderName'];
	}
	
	function run() {
		$err_no = $this->_run();
		
		header ("content-type: text/xml");
		echo "<?xml version=\"1.0\" encoding=\"utf-8\" ?>\n";
?>
<Connector command="CreateFolder" resourceType="<?php echo $this->XMLEncode($this->type); ?>">
	<CurrentFolder path="<?php echo $this->XMLEncode($this->raw_cwd); ?>" url="<?php echo $this->XMLEncode($this->url($this->actual_cwd)); ?>" />
	<Error number="<?php echo $err_no; ?>" />
</Connector>
<?php
	}
	
	function _run() {
		if ($this->newfolder == '') {
			return 101;
		}
		
		if (function_exists('mb_ereg')){
			if (mb_ereg($this->fckphp_config['DisableName'], $this->newfolder)) {
				return 101;
			}
		} else {
			if (ereg($this->fckphp_config['DisableName'], $this->newfolder)) {
				return 101;
			}
		}
		
		if (function_exists('mb_ereg')){
			if (mb_ereg($this->fckphp_config['DisableChars'], $this->newfolder)) {
				return 102;
			}
		} else {
			if (ereg($this->fckphp_config['DisableChars'], $this->newfolder)) {
				return 102;
			}
		}
		
		$newdir = "{$this->actual_cwd}{$this->newfolder}/";
		
		//Check if it already exists
		if (file_exists($this->path($newdir))) {
			return 104; //Folder or file already exists
		}
		
		if (! $this->mapFolder($newdir)) {
			return 110;	//Unknown error
		}
		
		return 0;	//Success
	}
}
?>