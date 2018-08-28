<?php
/**
 * index.php
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2016 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
// ########## Magic3アクセス制御(開始) ##########
require_once('../../../include/global.php');

if (!$gAccessManager->loginedByUser()){		// ログイン中のユーザはアクセスを許可
	echo 'Access error: access denied.';

	$gOpeLogManager->writeUserAccess(__METHOD__, 'ファイルブラウザへの不正なアクセスを検出しました。ログインなし', 3001, 'アクセスをブロックしました。');
	exit(0);
}
// ########## Magic3アクセス制御(終了) ##########
//error_reporting(0); // Set E_ALL for debuging

// load composer autoload before load elFinder autoload If you need composer
//require './vendor/autoload.php';

// elFinder autoload
require './autoload.php';
// ===============================================

// Enable FTP connector netmount
//elFinder::$netDrivers['ftp'] = 'FTP';
// ===============================================

/**
 * # Dropbox volume driver need `composer require dropbox-php/dropbox-php:dev-master@dev`
 *  OR "dropbox-php's Dropbox" and "PHP OAuth extension" or "PEAR's HTTP_OAUTH package"
 * * dropbox-php: http://www.dropbox-php.com/
 * * PHP OAuth extension: http://pecl.php.net/package/oauth
 * * PEAR's HTTP_OAUTH package: http://pear.php.net/package/http_oauth
 *  * HTTP_OAUTH package require HTTP_Request2 and Net_URL2
 */
// // Required for Dropbox.com connector support
// // On composer
// elFinder::$netDrivers['dropbox'] = 'Dropbox';
// // OR on pear
// include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'elFinderVolumeDropbox.class.php';

// // Dropbox driver need next two settings. You can get at https://www.dropbox.com/developers
// define('ELFINDER_DROPBOX_CONSUMERKEY',    '');
// define('ELFINDER_DROPBOX_CONSUMERSECRET', '');
// define('ELFINDER_DROPBOX_META_CACHE_PATH',''); // optional for `options['metaCachePath']`
// ===============================================

// // Required for Google Drive network mount
// // Installation by composer
// // `composer require nao-pon/flysystem-google-drive:~1.1 google/apiclient:~2.0@rc nao-pon/elfinder-flysystem-driver-ext`
// // Enable network mount
// elFinder::$netDrivers['googledrive'] = 'FlysystemGoogleDriveNetmount';
// // GoogleDrive Netmount driver need next two settings. You can get at https://console.developers.google.com
// // AND reuire regist redirect url to "YOUR_CONNECTOR_URL?cmd=netmount&protocol=googledrive&host=1"
// define('ELFINDER_GOOGLEDRIVE_CLIENTID',     '');
// define('ELFINDER_GOOGLEDRIVE_CLIENTSECRET', '');
// ===============================================

/**
 * Simple function to demonstrate how to control file access using "accessControl" callback.
 * This method will disable accessing files/folders starting from '.' (dot)
 *
 * @param  string  $attr  attribute name (read|write|locked|hidden)
 * @param  string  $path  file path relative to volume root directory started with directory separator
 * @return bool|null
 **/
function access($attr, $path, $data, $volume) {
	return strpos(basename($path), '.') === 0       // if file/folder begins with '.' (dot)
		? !($attr == 'read' || $attr == 'write')    // set read+write to false, other (locked+hidden) set to true
		:  null;                                    // else elFinder decide it itself
}

// ########## Magic3アクセス制御(開始) ##########
// ディレクトリ参照範囲を制限
$dirType = $gRequestManager->trimValueOf('dirtype');
if (!empty($dirType) && !in_array($dirType, array('image', 'file'))){
	$gOpeLogManager->writeUserAccess(__METHOD__, 'ファイルブラウザへの不正なパラメータを検出しました。dirtype=' . $dirType , 3001, 'アクセスをブロックしました。');
	exit(0);
}
$path = $gEnvManager->getResourcePathForUser();
$url = $gEnvManager->getRelativeResourcePathToDocumentRootForUser();
if (!empty($dirType)){
	$path .= '/' . $dirType;
	$url .= '/' . $dirType;
}

// ディレクトリがない場合は作成
if (!file_exists($path)) mkdir($path, M3_SYSTEM_DIR_PERMISSION, true/*再帰的*/);

// アップロード可能なファイルを制限
if ($dirType == 'image'){
	$uploadAllow = array('image');
} else {
	$uploadAllow = array('image', 'application/pdf');		// 画像、PDFを許可
}
// ########## Magic3アクセス制御(終了) ##########

// 画像の自動生成の設定
$autoResizeEnable	= (bool)$gSystemManager->getSystemConfig(SystemManager::CF_UPLOAD_IMAGE_AUTORESIZE);		// 画像リサイズ機能を使用するかどうか
$maxWidth			= $gSystemManager->getSystemConfig(SystemManager::CF_UPLOAD_IMAGE_AUTORESIZE_MAX_WIDTH);		// 画像リサイズ機能最大画像幅
$maxHeight			= $gSystemManager->getSystemConfig(SystemManager::CF_UPLOAD_IMAGE_AUTORESIZE_MAX_HEIGHT);		// 画像リサイズ機能最大画像高さ

// Documentation for connector options:
// https://github.com/Studio-42/elFinder/wiki/Connector-configuration-options
$opts = array(
	// 'debug' => true,
	'roots' => array(
		array(
			'driver'        => 'LocalFileSystem',   // driver for accessing file system (REQUIRED)
			'path'          => $path,		// path to files (REQUIRED)
			'URL'           => $url,		// URL to files (REQUIRED)
			'uploadDeny'    => array('all'),                // All Mimetypes not allowed to upload
			'uploadAllow'   => $uploadAllow,// Mimetype allowed to upload
			'uploadOrder'   => array('deny', 'allow'),      // allowed Mimetype `image` and `text/plain` only
			'accessControl' => 'access'             // disable and hide dot starting files (OPTIONAL)
		)
	),
	'bind' =>	array(
		'upload.presave' => array(
			'Plugin.AutoResize.onUpLoadPreSave'
		)
	),
	'plugin' => array(
		'AutoResize' => array(
			'enable'		=> $autoResizeEnable,		// 画像サイズを制限
			'maxWidth'		=> $maxWidth,		// 最大画像幅
			'maxHeight'		=> $maxHeight,		// 最大画像高さ
			'quality'		=> 100			// JPEG image save quality
		)
	)
);

// run elFinder
$connector = new elFinderConnector(new elFinder($opts));
$connector->run();

