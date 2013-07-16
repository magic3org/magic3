<?php
/*
PHP Connector for the FCKEditor v2 File Manager
Written By Grant French, UK, Sept 2004
http://www.mcpuk.net

FCKEditor - By Frederico Caldeira Knabben
http://www.fckeditor.net

File Description:
Implements the Thumbnail command, to return
a thumbnail to the browser for the sent file,
if the file is an image an attempt is made to
generate a thumbnail, otherwise an appropriate
icon is returned.
Output is image data

2009.2.10	mbstringなしでも実行できるように修正 by naoki hirata
*/

class Thumbnail extends command {
	var $filename;
	
	function init() {
		$this->filename = $_GET[ 'FileName' ] ;
	}
	
	function run() {
		$fullfile = $this->path( $this->actual_cwd . $this->filename ) ;
		
		$mime = $this->getMime( $fullfile ) ;
		$ext = $this->getExtension( $fullfile ) ;
		
		$type = $this->getType( $mime, $ext ) ;
		
		if ( $type != '' ) {
			if ( $this->fckphp_config[ 'UseCache' ] == 1 ) {
				$thumb = $this->path( $this->actual_cwd . '.thumb_' . $this->filename ) ;
				
				if ( ! file_exists( $thumb ) ) {
					$image = $this->createThumb( $type, $fullfile ) ;
					$this->outputThumb( $type, $image, $thumb ) ;
				}
				
				header( "Content-type: image/{$type}", true ) ;
				readfile( $thumb ) ;
			} else {
				$image = $this->createThumb( $type, $fullfile ) ;
				header( "Content-type: image/{$type}", true ) ;
				$this->outputThumb( $type, $image ) ;
			}
			
			return ;
		}
		
		header( "Content-type: image/jpeg", true ) ;
		readfile( $this->path( $this->iconLookup( $mime, $ext ) ) ) ;
	}
	
	function getMIME( $file ) {
		if ( function_exists( "mime_content_type" ) ) {
			$mime = mime_content_type( $file ) ;
		} else {
			$mime = $this->image2MIME( $file ) ;
		}
		
		return ( $mime === false ) ? '' : strtolower( $mime ) ;
	}
	
	function image2MIME( $file ) {
		$fh = fopen($file, "r");
		
		if ( $fh === false ) return false ;
		
		$start4 = fread( $fh, 4 ) ;
		$start3 = substr( $start4, 0, 3 ) ;
		
		fclose($fh);
		
		if ( $start4 == "\x89PNG" )			return "image/png" ;
		if ( $start3 == "GIF" )				return "image/gif" ;
		if ( $start3 == "\xFF\xD8\xFF" )	return "image/jpeg" ;
		if ( $start4 == "hsi1" )			return "image/jpeg" ;
		
		return '' ;
	}
	
	function getExtension( $filename ) {
		if (function_exists('mb_ereg')){
			if (mb_ereg('^.*\.([^\.]*)$', $filename, $part)){
				return strtolower( $part[ 1 ] ) ;
			}
		} else {
			if (ereg('^.*\.([^\.]*)$', $filename, $part)){
				return strtolower( $part[ 1 ] ) ;
			}
		}
		return '' ;
	}
	
	function getType( $mime, $ext ) {
		if ( $mime != '' ) {
			if ( $mime == "image/gif" )		return "gif" ;
			if ( $mime == "image/jpeg" )	return "jpeg" ;
			if ( $mime == "image/jpg" )		return "jpeg" ;
			if ( $mime == "image/pjpeg" )	return "jpeg" ;
			if ( $mime == "image/png" )		return "png" ;
		}
		
		if ( $ext == "gif" )	return "gif" ;
		if ( $ext == "jpg" )	return "jpeg" ;
		if ( $ext == "jpeg" )	return "jpeg" ;
		if ( $ext == "png" )	return "png" ;
		
		return '' ;
	}
	
	function iconLookup( $mime, $ext ) {
		if ( $mime == "text/plain" ) {
			$extensions = array_keys( $this->fckphp_config[ 'extIcons' ] ) ;
			
			$icon = $this->fckphp_config[ 'iconLookupDir' ] ;
			$icon .= ( ( in_array( $ext, $extensions ) ) ? $this->fckphp_config[ 'extIcons' ][ $ext ] : "/empty.jpg" ) ;
			
			return $icon ;
		}
		
		//Check specific cases
		$mimes = array_keys( $this->fckphp_config[ 'mimeIcons' ] ) ;
		
		if ( in_array( $mime, $mimes ) ) {
			$icon = $this->fckphp_config[ 'iconLookupDir' ] ;
			$icon .= $this->fckphp_config[ 'mimeIcons' ][ $mime ] ;
			
			return $icon ;
		}
		
		//Check for the generic mime type
		$mimePrefix = "text" ;
		$firstSlash = strpos( $mime, "/" ) ;
		
		if ( $firstSlash !== false ) $mimePrefix = substr( $mime, 0, $firstSlash ) ;
		
		$icon = $this->fckphp_config[ 'iconLookupDir' ] ;
		$icon .= ( ( in_array( $mimePrefix, $mimes ) ) ? $this->fckphp_config[ 'mimeIcons' ][ $mimePrefix ] : "/empty.jpg" ) ;
		
		return $icon ;
	}
	
	function createThumb( $type, $path ) {
		$size = 96 ;
		
		// imagecreatefrom...
		
		switch ( $type ) {
			case "jpeg" :
				$img = @imagecreatefromjpeg( $path ) ;
				break ;
			case "gif" :
				$img = @imagecreatefromgif( $path ) ;
				break ;
			case "png" :
				$img = @imagecreatefrompng( $path ) ;
				break ;
			default:
				return false ;
		}
		
		// size for thumbnail
		
		$width = imagesx( $img ) ;
		$height = imagesy( $img ) ;
		
		if ( $width > $height ) {
			$n_height = $height * ( $size / $width ) ;
			$n_width = $size ;
		} else {
			$n_width = $width * ( $size / $height ) ;
			$n_height = $size ;
		}
		
		$x = 0 ;
		$y = 0 ;
		
		if ( $n_width < $size ) $x = round( ( $size - $n_width ) / 2 ) ;
		if ( $n_height < $size ) $y = round( ( $size - $n_height ) / 2 ) ;
		
		// imagecreatetruecolor
		
		$thumb = imagecreatetruecolor( $size, $size ) ;
		
		$bgcolor = imagecolorallocate( $thumb, 255, 255, 255 ) ;
		imagefill( $thumb, 0, 0, $bgcolor ) ;
		
		// imagecopyresized (imagecopyresampled)
		
		if ( function_exists( "imagecopyresampled" ) ) {
			if ( ! imagecopyresampled( $thumb, $img, $x, $y, 0, 0, $n_width, $n_height, $width, $height ) ) {
				if (! imagecopyresized( $thumb, $img, $x, $y, 0, 0, $n_width, $n_height, $width, $height ) ) return false ;
			}
		} else {
			if ( ! imagecopyresized($thumb, $img, $x, $y, 0, 0, $n_width, $n_height, $width, $height)) return false ;
		}
		
		return $thumb ;
	}
	
	function outputThumb( $type, &$image, $path = null ) {
		if ( is_null( $path ) ) {
			switch ( $type ) {
				case "jpeg" :
					imagejpeg( $image ) ;
					break ;
				case "gif" :
					imagegif( $image ) ;
					break ;
				case "png" :
					imagepng( $image ) ;
					break ;
			}
		} else {
			switch ( $type ) {
				case "jpeg" :
					imagejpeg( $image, $path ) ;
					break ;
				case "gif" :
					imagegif( $image, $path ) ;
					break ;
				case "png" :
					imagepng( $image, $path ) ;
					break ;
			}
		}
	}
}
?>