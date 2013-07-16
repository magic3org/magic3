<?php
/*
PHP Connector for the FCKEditor v2 File Manager
Written By Grant French, UK, Sept 2004
http://www.mcpuk.net

FCKEditor - By Frederico Caldeira Knabben
http://www.fckeditor.net

File Description:
Main connector file, implements the State Pattern to 
redirect requests to the appropriate class based on 
the command name passed.

2008.6.8	modified by naoki hirata
2009.2.10	mbstringなしでも実行できるように修正 by naoki hirata
2010.3.30	ログインしたユーザは実行許可するように修正 by naoki hirata
2012.9.24	PHP5.4でStrictメッセージが出るコードを修正 by naoki hirata
*/
// ########## アクセス制御 ##########
require_once('../../../../../../../include/global.php');

if (!$gAccessManager->loginedByUser()){		// ログイン中のユーザはアクセスを許可
	echo 'Access error: access denied.';

	$gOpeLogManager->writeUserAccess(__METHOD__, 'ファイルブラウザへの不正なアクセスを検出しました。ログインなし', 3001, 'アクセスをブロックしました。');
	exit(0);
}
// ##################################

if (function_exists('mb_internal_encoding')) {
	mb_internal_encoding('UTF-8');
	mb_regex_encoding('UTF-8');
}

require_once "config.php";

outputHeaders();

//Get the passed data
$command = getParam('Command', "");
$type = getParam('Type', "File");
$cwd = getParam('CurrentFolder', "/");

$fckphp_config['UserFilesPath'] = getParam('ServerPath', $fckphp_config['UserFilesPath']);
if (function_exists('mb_ereg_replace')){
	$fckphp_config['UserFilesPath'] = mb_ereg_replace('/$', '', $fckphp_config['UserFilesPath']);
} else {
	$fckphp_config['UserFilesPath'] = ereg_replace('/$', '', $fckphp_config['UserFilesPath']);
}

if (! in_array($command, $fckphp_config['Commands'])) {
	//No reason for me to be here.
	echo "Invalid command '{$command}'.";
	
	$gOpeLogManager->writeUserAccess(__METHOD__, 'ファイルブラウザへの不正なアクセスを検出しました。不正コマンド: ' . $command, 3001, 'アクセスをブロックしました。');
	exit(0);
}

//bit of validation
if (! in_array($type, $fckphp_config['ResourceTypes'])) {
	echo "Invalid resource type.";
	
	$gOpeLogManager->writeUserAccess(__METHOD__, 'ファイルブラウザへの不正なアクセスを検出しました。不正リソース: ' . $type, 3001, 'アクセスをブロックしました。');
	exit(0);
}

require_once "Commands/{$command}.php";

$action = new $command($fckphp_config, $type, $cwd);

$action->run();

exit(0);



function outputHeaders() {

	//Anti browser caching headers
	//Borrowed from fatboy's implementation  (fatFCK@code247.com)
	
	// ensure file is never cached
	// Date in the past
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
	
	// always modified
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	
	// HTTP/1.1
	header("Cache-Control: no-store, no-cache, must-revalidate");
	header("Cache-Control: post-check=0, pre-check=0", false);
	
	// HTTP/1.0
	header("Pragma: no-cache");
}


function getParam($name, $default) {
	if (! isset($_GET[$name])) return $default;
	if ($_GET[$name] == "") return $default;
	return $_GET[$name];
}


class command {
	var $fckphp_config;
	var $type;
	var $cwd;
	var $actual_cwd;
	
	
	function command($fckphp_config, $type, $cwd) {
		// $this->__construct($fckphp_config, $type, $cwd);		// Strictメッセージが出る fixed by naoki 2012/9/24
		$this->initialize($fckphp_config, $type, $cwd);
	}
	
	
	// function __construct($fckphp_config, $type, $cwd) {	// Strictメッセージが出る fixed by naoki 2012/9/24
	function initialize($fckphp_config, $type, $cwd) {
		$this->fckphp_config = $fckphp_config;
		$this->type = $type;
		$this->raw_cwd = $cwd;
		//$this->actual_cwd = "{$this->fckphp_config['UserFilesPath']}/{$type}{$this->raw_cwd}";
		$this->actual_cwd = $this->fckphp_config['UserFilesPath'] . '/' . strtolower($type) . $this->raw_cwd;// ディレクトリ名を英小文字に設定 by naoki
		
		if (method_exists($this, 'init')) {
			$this->init() ;
		}
	}
	
	
	function path($path) {
		if (function_exists('mb_ereg_replace')){
			$path = mb_ereg_replace('/', DIRECTORY_SEPARATOR, $path);
			$path = mb_convert_encoding($path, $this->fckphp_config['FileEncoding'], 'UTF-8');
		} else {
			$path = ereg_replace('/', DIRECTORY_SEPARATOR, $path);
		}
		return "{$this->fckphp_config['basedir']}{$path}";
	}
	
	
	function mapFolder($dir) {
		if (function_exists('mb_ereg_replace')){
			$parent = mb_ereg_replace( '[^/]+/$', '', $dir ) ;
		} else {
			$parent = ereg_replace( '[^/]+/$', '', $dir ) ;
		}
		if (! file_exists( $this->path($parent) ) ) {
			if ( ! $this->mapFolder( $parent ) ) return false;
		}
		
		if ( file_exists( $this->path($dir) ) ) return true;
		
		$oldumask = umask(0) ;
		$result = @mkdir( $this->path($dir), 0777 ) ;
		umask( $oldumask ) ;
		
		return $result ;
	}
	
	
	function url($url) {
		if (function_exists('mb_split')){
			$url_part = mb_split('/', $url);
		
			for ($n = 0; $n < count($url_part); $n++) {
				$url_part[$n] = rawurlencode(mb_convert_encoding($url_part[$n], $this->fckphp_config['FileEncoding'], 'UTF-8'));
			}
		} else {
			//$url_part = split('/', $url);
			$url_part = explode('/', $url);
			for ($n = 0; $n < count($url_part); $n++) {
				$url_part[$n] = rawurlencode($url_part[$n]);
			}
		}
		return implode('/', $url_part);
	}
	
	
	function XMLEncode($str) {
		if (function_exists('mb_ereg_replace')){
			$str = mb_ereg_replace( '&' , '&amp;', $str );
			$str = mb_ereg_replace( '<' , '&lt;', $str );
			$str = mb_ereg_replace( '>' , '&gt;', $str );
			$str = mb_ereg_replace( '"' , '&quot;', $str );
			$str = mb_ereg_replace( '\'' , '&apos;', $str );
		} else {
			$str = ereg_replace( '&' , '&amp;', $str );
			$str = ereg_replace( '<' , '&lt;', $str );
			$str = ereg_replace( '>' , '&gt;', $str );
			$str = ereg_replace( '"' , '&quot;', $str );
			$str = ereg_replace( '\'' , '&apos;', $str );
		}
		return $str;
	}
}
?> 
