<?php
/*
PHP Connector for the FCKEditor v2 File Manager
Written By Grant French, UK, Sept 2004
http://www.mcpuk.net

FCKEditor - By Frederico Caldeira Knabben
http://www.fckeditor.net

File Description:
Implements the DeleteFolder command to delete a folder
in the current directory. Output is in XML

2009.2.10	mbstringなしでも実行できるように修正 by naoki hirata
*/

class DeleteFolder extends command {
	var $foldername;
	
	function init() {
		$this->foldername = $_GET[ 'FolderName' ] ;
	}
	
	function run() {
		$err_no = $this->_run( "{$this->actual_cwd}{$this->foldername}/" ) ;
		
		header ( "content-type: text/xml" ) ;
		echo "<?xml version=\"1.0\" encoding=\"utf-8\" ?>\n" ;
?>
<Connector command="DeleteFolder" resourceType="<?php echo $this->XMLEncode( $this->type ) ; ?>">
	<CurrentFolder path="<?php echo $this->XMLEncode( $this->raw_cwd ) ; ?>" url="<?php echo $this->XMLEncode( $this->url( $this->actual_cwd ) ) ; ?>" />
	<Error number="<?php echo $err_no ; ?>" />
</Connector>
<?php
	}
	
	function _run( $dir ) {
		//Check if we can create the directory here
		if ( ! is_writeable( $this->path( $dir ) ) ) {
			return 103 ;	//No permissions to rename
		}
		
		$dh = @opendir( $this->path( $dir ) ) ;
		
		if ( $dh === false ) {
			return 105 ;
		}
		
		$err = 0 ;
		
		while ( ( $entry = readdir( $dh ) ) !== false ) {
			if ( $entry == "." ) continue;
			if ( $entry == ".." ) continue;
			
			if (function_exists('mb_convert_encoding')){
				$entry = mb_convert_encoding( $entry, 'UTF-8', $this->fckphp_config[ 'FileEncoding' ] ) ;
			}
			
			if ( is_dir( $this->path( "{$dir}{$entry}/" ) ) ) {
				$err = $this->_run( "{$dir}{$entry}/" ) ;
				if ( $err != 0 ) break ;
				
				continue ;
			}
			
			if ( ! @unlink( $this->path( "{$dir}{$entry}" ) ) ) {
				$err = 110 ;
				break ;
			}
		}
		
		closedir( $dh ) ;
		
		if ( $err != 0 ) return $err ;
		
		return ( @rmdir( $this->path( $dir ) ) ) ? 0 : 110 ;
	}
}
?>