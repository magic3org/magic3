<?php
/*
PHP Connector for the FCKEditor v2 File Manager
Written By Akira Taniguchi, JP, Dec 2006
http://akira.matrix.jp/

FCKEditor - By Frederico Caldeira Knabben
http://www.fckeditor.net/

File Description:
Configuration file

2008.6.8	modified by naoki hirata
2009.2.10	mbstringなしでも実行できるように修正 by naoki hirata
2010.3.30	ユーザ種別に応じたのディレクトリの自動設定を追加 by naoki hirata
2010.7.14	フォルダ情報取得機能追加 by naoki hirata
*/
/*------------------------------------------------------------------------------*/
/* Encoding of file system					*/
/*------------------------------------------------------------------------------*/
$fckphp_config['FileEncoding'] = 'UTF-8';
/*==============================================================================*/

/*------------------------------------------------------------------------------*/
/* Path to user files relative to the document root (no trailing slash)		*/
/*------------------------------------------------------------------------------*/
//$fckphp_config['UserFilesPath'] = "/UserFiles";
// Magic3のリソースディレクトリ(ルート/resource)をユーザ利用可能にする
//$fckphp_config['UserFilesPath'] = $gEnvManager->getRelativeResourcePathToDocumentRoot();
$fckphp_config['UserFilesPath'] = $gEnvManager->getRelativeResourcePathToDocumentRootForUser();		// ユーザ種別に対応(2010.3.30)
/*==============================================================================*/

/*------------------------------------------------------------------------------*/
/* Use Cache																	*/
/*------------------------------------------------------------------------------*/
$fckphp_config['UseCache'] = 0;		// 0/1 = Unuse / Use
/*==============================================================================*/

/*------------------------------------------------------------------------------*/
/* Global Disk Quota - Max size of all resource areas				*/
/*------------------------------------------------------------------------------*/
$fckphp_config['DiskQuota']['Global'] = 50;	//In MBytes (default: 50mb)
/*==============================================================================*/

/*------------------------------------------------------------------------------*/
/* Per resource area settings:-							*/
/* - AllowedExtensions	:: Array, allowed file extensions (in lowercase)	*/
/* - AllowedMIME	:: Array, allowed mime types (in lowercase)		*/
/* - MaxSize		:: Number, Maximum size of file uploads in KBytes	*/
/* - DiskQuota		:: Number, Maximum size allowed for the resource area in MByte	*/
/* - HideFolders	:: Array, RegExp, matching folder names will be hidden	*/
/* - HideFiles		:: Array, RegExp, matching file names will be hidden	*/
/*------------------------------------------------------------------------------*/
//File Area
$fckphp_config['ResourceAreas']['File'] = array();
$fckphp_config['ResourceAreas']['File']['AllowedExtensions']	= array();
$fckphp_config['ResourceAreas']['File']['AllowedExtensions'][]	= "zip";
$fckphp_config['ResourceAreas']['File']['AllowedExtensions'][]	= "doc";
$fckphp_config['ResourceAreas']['File']['AllowedExtensions'][]	= "xls";
$fckphp_config['ResourceAreas']['File']['AllowedExtensions'][]	= "pdf";
$fckphp_config['ResourceAreas']['File']['AllowedExtensions'][]	= "rtf";
$fckphp_config['ResourceAreas']['File']['AllowedExtensions'][]	= "csv";
/*$fckphp_config['ResourceAreas']['File']['AllowedExtensions'][]	= "jpg";
$fckphp_config['ResourceAreas']['File']['AllowedExtensions'][]	= "gif";
$fckphp_config['ResourceAreas']['File']['AllowedExtensions'][]	= "jpeg";
$fckphp_config['ResourceAreas']['File']['AllowedExtensions'][]	= "png";
$fckphp_config['ResourceAreas']['File']['AllowedExtensions'][]	= "avi";
$fckphp_config['ResourceAreas']['File']['AllowedExtensions'][]	= "mpg";
$fckphp_config['ResourceAreas']['File']['AllowedExtensions'][]	= "mpeg";
$fckphp_config['ResourceAreas']['File']['AllowedExtensions'][]	= "swf";
$fckphp_config['ResourceAreas']['File']['AllowedExtensions'][]	= "fla";*/
$fckphp_config['ResourceAreas']['File']['AllowedMIME']			= array();
$fckphp_config['ResourceAreas']['File']['MaxSize']				= 1024 * 20;	// KBytes
$fckphp_config['ResourceAreas']['File']['DiskQuota']			= 50;			// MBytes
$fckphp_config['ResourceAreas']['File']['HideFolders']			= array("^\.");
$fckphp_config['ResourceAreas']['File']['HideFiles']			= array("^\.");

//Image area
$fckphp_config['ResourceAreas']['Image'] = array();
$fckphp_config['ResourceAreas']['Image']['AllowedExtensions']	= array();
$fckphp_config['ResourceAreas']['Image']['AllowedExtensions'][]	= "jpg";
$fckphp_config['ResourceAreas']['Image']['AllowedExtensions'][]	= "gif";
$fckphp_config['ResourceAreas']['Image']['AllowedExtensions'][]	= "jpeg";
$fckphp_config['ResourceAreas']['Image']['AllowedExtensions'][]	= "png";
$fckphp_config['ResourceAreas']['Image']['AllowedMIME']			= array();
$fckphp_config['ResourceAreas']['Image']['MaxSize']				= 1024 * 20;	// KBytes
$fckphp_config['ResourceAreas']['Image']['DiskQuota']			= 50;			// MBytes
$fckphp_config['ResourceAreas']['Image']['HideFolders']			= array("^\.");
$fckphp_config['ResourceAreas']['Image']['HideFiles']			= array("^\.");

//Flash area
$fckphp_config['ResourceAreas']['Flash'] = array();
$fckphp_config['ResourceAreas']['Flash']['AllowedExtensions']	= array();
$fckphp_config['ResourceAreas']['Flash']['AllowedExtensions'][]	= "swf";
$fckphp_config['ResourceAreas']['Flash']['AllowedExtensions'][]	= "fla";
$fckphp_config['ResourceAreas']['Flash']['AllowedMIME']			= array();
$fckphp_config['ResourceAreas']['Flash']['MaxSize']				= 1024 * 20;	// KBytes
$fckphp_config['ResourceAreas']['Flash']['DiskQuota']			= 50;			// MBytes
$fckphp_config['ResourceAreas']['Flash']['HideFolders']			= array("^\.");
$fckphp_config['ResourceAreas']['Flash']['HideFiles']			= array("^\.");

//Media area
$fckphp_config['ResourceAreas']['Media'] = array();
/*$fckphp_config['ResourceAreas']['Media']['AllowedExtensions']	= array();
$fckphp_config['ResourceAreas']['Media']['AllowedExtensions'][]	= "swf";
$fckphp_config['ResourceAreas']['Media']['AllowedExtensions'][]	= "fla";
$fckphp_config['ResourceAreas']['Media']['AllowedExtensions'][]	= "jpg";
$fckphp_config['ResourceAreas']['Media']['AllowedExtensions'][]	= "gif";
$fckphp_config['ResourceAreas']['Media']['AllowedExtensions'][]	= "jpeg";
$fckphp_config['ResourceAreas']['Media']['AllowedExtensions'][]	= "png";*/
$fckphp_config['ResourceAreas']['Media']['AllowedExtensions'][]	= "mov";
$fckphp_config['ResourceAreas']['Media']['AllowedExtensions'][]	= "ra";
$fckphp_config['ResourceAreas']['Media']['AllowedExtensions'][]	= "ram";
$fckphp_config['ResourceAreas']['Media']['AllowedExtensions'][]	= "rm";
$fckphp_config['ResourceAreas']['Media']['AllowedExtensions'][]	= "avi";
$fckphp_config['ResourceAreas']['Media']['AllowedExtensions'][]	= "mpg";
$fckphp_config['ResourceAreas']['Media']['AllowedExtensions'][]	= "mpeg";
$fckphp_config['ResourceAreas']['Media']['AllowedMIME']			= array();
$fckphp_config['ResourceAreas']['Media']['MaxSize']				= 1024 * 20;	// KBytes
$fckphp_config['ResourceAreas']['Media']['DiskQuota']			= 50;			// MBytes
$fckphp_config['ResourceAreas']['Media']['HideFolders']			= array("^\.");
$fckphp_config['ResourceAreas']['Media']['HideFiles']			= array("^\.");

/*==============================================================================*/		


/*------------------------------------------------------------------------------*/
/* Directory and File Naming :-							*/
/*  -AvailableMax		:: Maximum number of available files				*/
/*  -DisableName		:: Disable name	(regexp)			*/
/*------------------------------------------------------------------------------*/
$fckphp_config['AvailableMax'] = 200;
$fckphp_config['DisableName'] = '(^\..*$|^.*\.$)';
$fckphp_config['DisableChars'] = '[\\/:"*?<>|]';	// .*, *., \/:,;"*?<>|
/*==============================================================================*/

/*------------------------------------------------------------------------------*/
/* Internals :-									*/
/*	ResourceTypes :: Array of valid resource areas				*/
/*	Commands :: Array of valid commands accepted by the connector		*/
/*------------------------------------------------------------------------------*/
$fckphp_config['ResourceTypes'] = array();
$fckphp_config['ResourceTypes'][] = 'File';
$fckphp_config['ResourceTypes'][] = 'Image';
$fckphp_config['ResourceTypes'][] = 'Flash';
$fckphp_config['ResourceTypes'][] = 'Media';

$fckphp_config['Commands'] = array();
$fckphp_config['Commands'][] = "CreateFolder";
$fckphp_config['Commands'][] = "GetFolders";
$fckphp_config['Commands'][] = "GetFoldersAndFiles";
$fckphp_config['Commands'][] = "FileUpload";
$fckphp_config['Commands'][] = "Thumbnail";
$fckphp_config['Commands'][] = "DeleteFile";
$fckphp_config['Commands'][] = "DeleteFolder";
$fckphp_config['Commands'][] = "RenameFile";
$fckphp_config['Commands'][] = "RenameFolder";
$fckphp_config['Commands'][] = "GetFolderInfo";			// フォルダ情報取得(2010.7.14)
/*==============================================================================*/


/*------------------------------------------------------------------------------*/
/* Thumb command																*/
/*------------------------------------------------------------------------------*/
$fckphp_config['mimeIcons']										= array();
$fckphp_config['mimeIcons']["image"]							= "image.jpg";
$fckphp_config['mimeIcons']["audio"]							= "sound.jpg";
$fckphp_config['mimeIcons']["video"]							= "video.jpg";
$fckphp_config['mimeIcons']["text"]								= "document2.jpg";
$fckphp_config['mimeIcons']["text/html"]						= "html.jpg";
$fckphp_config['mimeIcons']["application"]						= "binary.jpg";
$fckphp_config['mimeIcons']["application/pdf"]					= "pdf.jpg";
$fckphp_config['mimeIcons']["application/msword"]				= "document2.jpg";
$fckphp_config['mimeIcons']["application/postscript"]			= "postscript.jpg";
$fckphp_config['mimeIcons']["application/rtf"]					= "document2.jpg";
$fckphp_config['mimeIcons']["application/vnd.ms-excel"]			= "document2.jpg";
$fckphp_config['mimeIcons']["application/vnd.ms-powerpoint"]	= "document2.jpg";
$fckphp_config['mimeIcons']["application/x-tar"]				= "tar.jpg";
$fckphp_config['mimeIcons']["application/zip"]					= "tar.jpg";
$fckphp_config['mimeIcons']["message"]							= "email.jpg";
$fckphp_config['mimeIcons']["message/html"]						= "html.jpg";
$fckphp_config['mimeIcons']["model"]							= "kmplot.jpg";
$fckphp_config['mimeIcons']["multipart"]						= "kmultiple.jpg";

$fckphp_config['extIcons']			= array();
$fckphp_config['extIcons']["pdf"]	= "pdf.jpg";
$fckphp_config['extIcons']["ps"]	= "postscript.jpg";
$fckphp_config['extIcons']["eps"]	= "postscript.jpg";
$fckphp_config['extIcons']["ai"]	= "postscript.jpg";
$fckphp_config['extIcons']["ra"]	= "real_doc.jpg";
$fckphp_config['extIcons']["rm"]	= "real_doc.jpg";
$fckphp_config['extIcons']["ram"]	= "real_doc.jpg";
$fckphp_config['extIcons']["wav"]	= "sound.jpg";
$fckphp_config['extIcons']["mp3"]	= "sound.jpg";
$fckphp_config['extIcons']["ogg"]	= "sound.jpg";
$fckphp_config['extIcons']["eml"]	= "email.jpg";
$fckphp_config['extIcons']["tar"]	= "tar.jpg";
$fckphp_config['extIcons']["zip"]	= "tar.jpg";
$fckphp_config['extIcons']["bz2"]	= "tar.jpg";
$fckphp_config['extIcons']["tgz"]	= "tar.jpg";
$fckphp_config['extIcons']["gz"]	= "tar.jpg";
$fckphp_config['extIcons']["rar"]	= "tar.jpg";
$fckphp_config['extIcons']["avi"]	= "video.jpg";
$fckphp_config['extIcons']["mpg"]	= "video.jpg";
$fckphp_config['extIcons']["mpeg"]	= "video.jpg";
$fckphp_config['extIcons']["jpg"]	= "image.jpg";
$fckphp_config['extIcons']["gif"]	= "image.jpg";
$fckphp_config['extIcons']["png"]	= "image.jpg";
$fckphp_config['extIcons']["jpeg"]	= "image.jpg";
$fckphp_config['extIcons']["nfo"]	= "info.jpg";
$fckphp_config['extIcons']["xls"]	= "spreadsheet.jpg";
$fckphp_config['extIcons']["csv"]	= "spreadsheet.jpg";
$fckphp_config['extIcons']["html"]	= "html.jpg";
$fckphp_config['extIcons']["doc"]	= "document2.jpg";
$fckphp_config['extIcons']["rtf"]	= "document2.jpg";
$fckphp_config['extIcons']["txt"]	= "document2.jpg";
$fckphp_config['extIcons']["xla"]	= "document2.jpg";
$fckphp_config['extIcons']["xlc"]	= "document2.jpg";
$fckphp_config['extIcons']["xlt"]	= "document2.jpg";
$fckphp_config['extIcons']["xlw"]	= "document2.jpg";
$fckphp_config['extIcons']["txt"]	= "document2.jpg";

$fckphp_config['iconLookupDir'] = dirname($_SERVER['PHP_SELF']) . "/images/";
/*==============================================================================*/

/*------------------------------------------------------------------------------*/
/* HTTP over SSL Detection (shouldnt require changing				*/
/*------------------------------------------------------------------------------*/
$fckphp_config['prot'] = ((isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on')) ? "https://" : "http://");
/*==============================================================================*/

/*------------------------------------------------------------------------------*/
/* Prefix added to image path before sending back to editor			*/
/*------------------------------------------------------------------------------*/
$fckphp_config['urlprefix'] = "{$fckphp_config['prot']}{$_SERVER['SERVER_NAME']}";
/*==============================================================================*/

/*------------------------------------------------------------------------------*/
/* DIRECTORY_SEPARATOR			*/
/*------------------------------------------------------------------------------*/
if (! defined('DIRECTORY_SEPARATOR')) {
	define('DIRECTORY_SEPARATOR', (isset($_ENV['OS']) ? (ereg('^Windows', $_ENV['OS']) ? '\\' : '/') : '/'));
}

/*------------------------------------------------------------------------------*/
/* The physical path to the document root					*/
/*------------------------------------------------------------------------------*/
/*
if (extension_loaded('mbstring')){	// mbstring使用可能
	$sRealPath = mb_convert_encoding( realpath( './' ), 'UTF-8', $fckphp_config['FileEncoding'] ) ;

	if (function_exists('mb_split')){
		$sSelfPath_part = mb_split('/', dirname($_SERVER['PHP_SELF']));
	} else {
		$sSelfPath_part = explode('/', dirname($_SERVER['PHP_SELF']));
	}

	for ($n = 0; $n < count($sSelfPath_part); $n++)
	    $sSelfPath_part[$n] = mb_convert_encoding(rawurldecode($sSelfPath_part[$n]), 'UTF-8', $fckphp_config['FileEncoding']);

	$sSelfPath = implode( DIRECTORY_SEPARATOR, $sSelfPath_part ) ;

	$fckphp_config['basedir'] = mb_substr( $sRealPath, 0, mb_strlen( $sRealPath ) - mb_strlen( $sSelfPath ) ) ;
} else {
	$sRealPath = realpath('./');

	//$sSelfPath_part = split( '/', dirname($_SERVER['PHP_SELF']) ) ;
	$sSelfPath_part = explode('/', dirname($_SERVER['PHP_SELF']));
	for ($n = 0; $n < count($sSelfPath_part); $n++){
	    $sSelfPath_part[$n] = rawurldecode($sSelfPath_part[$n]);
	}
	$sSelfPath = implode( DIRECTORY_SEPARATOR, $sSelfPath_part ) ;
	
	$fckphp_config['basedir'] = substr($sRealPath, 0, strlen($sRealPath) - strlen($sSelfPath));
}*/
// document rootはMagic3から取得(add by naoki)
$fckphp_config['basedir'] = $gEnvManager->getDocumentRoot();
/*==============================================================================*/
?>