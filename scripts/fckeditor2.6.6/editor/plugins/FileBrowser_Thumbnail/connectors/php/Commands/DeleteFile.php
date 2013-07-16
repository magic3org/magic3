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
*/

class DeleteFile extends command {
	var $filename;
	
	function init() {
		$this->filename = $_GET['FileName'];
	}
	
	function run() {
		$err_no = $this->_run();
		
		header ("content-type: text/xml");
		echo "<?xml version=\"1.0\" encoding=\"utf-8\" ?>\n";
?>
<Connector command="DeleteFile" resourceType="<?php echo $this->XMLEncode($this->type); ?>">
	<CurrentFolder path="<?php echo $this->XMLEncode($this->raw_cwd); ?>" url="<?php echo $this->XMLEncode($this->url($this->actual_cwd)); ?>" />
	<Error number="<?php echo $err_no; ?>" />
</Connector>
<?php
	}
	
	function _run() {
		//Check if we can create the directory here
		if (! is_writeable($this->path($this->actual_cwd))) {
			return 103;	//No permissions to rename
		}
		
		$thumb = "{$this->actual_cwd}.thumb_{$this->filename}";
		
		if (file_exists($this->path($thumb))) {
			if (! @unlink($this->path($thumb))) {
				return 110;
			}
		}
		
		return (@unlink($this->path("{$this->actual_cwd}{$this->filename}"))) ? 0 : 110;
	}
}
?>