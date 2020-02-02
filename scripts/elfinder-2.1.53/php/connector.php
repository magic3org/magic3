<?php
/**
 * index.php
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     ���c���B(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2018 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
// ########## Magic3�A�N�Z�X����(�J�n) ##########
require_once('../../../include/global.php');

if (!$gAccessManager->loginedByUser()){		// ���O�C�����̃��[�U�̓A�N�Z�X������
	echo 'Access error: access denied.';

	$gOpeLogManager->writeUserAccess(__METHOD__, '�t�@�C���u���E�U�ւ̕s���ȃA�N�Z�X�����o���܂����B���O�C���Ȃ�', 3001, '�A�N�Z�X���u���b�N���܂����B');
	exit(0);
}
// ########## Magic3�A�N�Z�X����(�I��) ##########
//error_reporting(0); // Set E_ALL for debuging

// // To Enable(true) handling of PostScript files by ImageMagick
// // It is disabled by default as a countermeasure 
// // of Ghostscript multiple -dSAFER sandbox bypass vulnerabilities
// // see https://www.kb.cert.org/vuls/id/332928
// define('ELFINDER_IMAGEMAGICK_PS', true);
// ===============================================

// load composer autoload before load elFinder autoload If you need composer
//require './vendor/autoload.php';

// elFinder autoload
require './autoload.php';
// ===============================================

// Enable FTP connector netmount
//elFinder::$netDrivers['ftp'] = 'FTP';
// ===============================================

// // Required for Dropbox network mount
// // Installation by composer
// // `composer require kunalvarma05/dropbox-php-sdk`
// // Enable network mount
// elFinder::$netDrivers['dropbox2'] = 'Dropbox2';
// // Dropbox2 Netmount driver need next two settings. You can get at https://www.dropbox.com/developers/apps
// // AND reuire regist redirect url to "YOUR_CONNECTOR_URL?cmd=netmount&protocol=dropbox2&host=1"
// define('ELFINDER_DROPBOX_APPKEY',    '');
// define('ELFINDER_DROPBOX_APPSECRET', '');
// ===============================================

// // Required for Google Drive network mount
// // Installation by composer
// // `composer require google/apiclient:^2.0`
// // Enable network mount
// elFinder::$netDrivers['googledrive'] = 'GoogleDrive';
// // GoogleDrive Netmount driver need next two settings. You can get at https://console.developers.google.com
// // AND reuire regist redirect url to "YOUR_CONNECTOR_URL?cmd=netmount&protocol=googledrive&host=1"
// define('ELFINDER_GOOGLEDRIVE_CLIENTID',     '');
// define('ELFINDER_GOOGLEDRIVE_CLIENTSECRET', '');
// // Required case of without composer
// define('ELFINDER_GOOGLEDRIVE_GOOGLEAPICLIENT', '/path/to/google-api-php-client/vendor/autoload.php');
// ===============================================

// // Required for Google Drive network mount with Flysystem
// // Installation by composer
// // `composer require nao-pon/flysystem-google-drive:~1.1 nao-pon/elfinder-flysystem-driver-ext`
// // Enable network mount
// elFinder::$netDrivers['googledrive'] = 'FlysystemGoogleDriveNetmount';
// // GoogleDrive Netmount driver need next two settings. You can get at https://console.developers.google.com
// // AND reuire regist redirect url to "YOUR_CONNECTOR_URL?cmd=netmount&protocol=googledrive&host=1"
// define('ELFINDER_GOOGLEDRIVE_CLIENTID',     '');
// define('ELFINDER_GOOGLEDRIVE_CLIENTSECRET', '');
// ===============================================

// // Required for One Drive network mount
// //  * cURL PHP extension required
// //  * HTTP server PATH_INFO supports required
// // Enable network mount
// elFinder::$netDrivers['onedrive'] = 'OneDrive';
// // GoogleDrive Netmount driver need next two settings. You can get at https://dev.onedrive.com
// // AND reuire regist redirect url to "YOUR_CONNECTOR_URL/netmount/onedrive/1"
// define('ELFINDER_ONEDRIVE_CLIENTID',     '');
// define('ELFINDER_ONEDRIVE_CLIENTSECRET', '');
// ===============================================

// // Required for Box network mount
// //  * cURL PHP extension required
// // Enable network mount
// elFinder::$netDrivers['box'] = 'Box';
// // Box Netmount driver need next two settings. You can get at https://developer.box.com
// // AND reuire regist redirect url to "YOUR_CONNECTOR_URL"
// define('ELFINDER_BOX_CLIENTID',     '');
// define('ELFINDER_BOX_CLIENTSECRET', '');
// ===============================================


// // Zoho Office Editor APIKey
// // https://www.zoho.com/docs/help/office-apis.html
// define('ELFINDER_ZOHO_OFFICE_APIKEY', '');
// ===============================================

// // Online converter (online-convert.com) APIKey
// // https://apiv2.online-convert.com/docs/getting_started/api_key.html
// define('ELFINDER_ONLINE_CONVERT_APIKEY', '');
// ===============================================

// // Zip Archive editor
// // Installation by composer
// // `composer require nao-pon/elfinder-flysystem-ziparchive-netmount`
// define('ELFINDER_DISABLE_ZIPEDITOR', false); // set `true` to disable zip editor
// ===============================================

/**
 * Simple function to demonstrate how to control file access using "accessControl" callback.
 * This method will disable accessing files/folders starting from '.' (dot)
 *
 * @param  string    $attr    attribute name (read|write|locked|hidden)
 * @param  string    $path    absolute file path
 * @param  string    $data    value of volume option `accessControlData`
 * @param  object    $volume  elFinder volume driver object
 * @param  bool|null $isDir   path is directory (true: directory, false: file, null: unknown)
 * @param  string    $relpath file path relative to volume root directory started with directory separator
 * @return bool|null
 **/
function access($attr, $path, $data, $volume, $isDir, $relpath) {
	$basename = basename($path);
	return $basename[0] === '.'                  // if file/folder begins with '.' (dot)
			 && strlen($relpath) !== 1           // but with out volume root
		? !($attr == 'read' || $attr == 'write') // set read+write to false, other (locked+hidden) set to true
		:  null;                                 // else elFinder decide it itself
}

// ########## Magic3�A�N�Z�X����(�J�n) ##########
// �f�B���N�g���Q�Ɣ͈͂𐧌�
$dirType = $gRequestManager->trimValueOf('dirtype');
if (!empty($dirType) && !in_array($dirType, array('image', 'file'))){
	$gOpeLogManager->writeUserAccess(__METHOD__, '�t�@�C���u���E�U�ւ̕s���ȃp�����[�^�����o���܂����Bdirtype=' . $dirType , 3001, '�A�N�Z�X���u���b�N���܂����B');
	exit(0);
}
$path = $gEnvManager->getResourcePathForUser();
$url = $gEnvManager->getRelativeResourcePathToDocumentRootForUser();
if (!empty($dirType)){
	$path .= '/' . $dirType;
	$url .= '/' . $dirType;
}

// �f�B���N�g�����Ȃ��ꍇ�͍쐬
if (!file_exists($path)) mkdir($path, M3_SYSTEM_DIR_PERMISSION, true/*�ċA�I*/);

// �A�b�v���[�h�\�ȃt�@�C���𐧌�
if ($dirType == 'image'){
	$uploadAllow = array('image');
} else {
	$uploadAllow = array('image', 'application/pdf');		// �摜�APDF������
}
// ########## Magic3�A�N�Z�X����(�I��) ##########

// �摜�̎��������̐ݒ�
$autoResizeEnable	= (bool)$gSystemManager->getSystemConfig(SystemManager::CF_UPLOAD_IMAGE_AUTORESIZE);		// �摜���T�C�Y�@�\���g�p���邩�ǂ���
$maxWidth			= $gSystemManager->getSystemConfig(SystemManager::CF_UPLOAD_IMAGE_AUTORESIZE_MAX_WIDTH);		// �摜���T�C�Y�@�\�ő�摜��
$maxHeight			= $gSystemManager->getSystemConfig(SystemManager::CF_UPLOAD_IMAGE_AUTORESIZE_MAX_HEIGHT);		// �摜���T�C�Y�@�\�ő�摜����

// Documentation for connector options:
// https://github.com/Studio-42/elFinder/wiki/Connector-configuration-options
$opts = array(
	// 'debug' => true,
	'roots' => array(
		// Items volume
		array(
			'driver'        => 'LocalFileSystem',           // driver for accessing file system (REQUIRED)
//			'path'          => '../files/',                 // path to files (REQUIRED)
//			'URL'           => dirname($_SERVER['PHP_SELF']) . '/../files/', // URL to files (REQUIRED)
			'path'          => $path,		// path to files (REQUIRED)
			'URL'           => $url,		// URL to files (REQUIRED)
//			'trashHash'     => 't1_Lw',                     // elFinder's hash of trash folder
//			'winHashFix'    => DIRECTORY_SEPARATOR !== '/', // to make hash same to Linux one on windows too
			'uploadDeny'    => array('all'),                // All Mimetypes not allowed to upload
//			'uploadAllow'   => array('image/x-ms-bmp', 'image/gif', 'image/jpeg', 'image/png', 'image/x-icon', 'text/plain'), // Mimetype `image` and `text/plain` allowed to upload
			'uploadAllow'   => $uploadAllow,// Mimetype allowed to upload
			'uploadOrder'   => array('deny', 'allow'),      // allowed Mimetype `image` and `text/plain` only
			'accessControl' => 'access'                     // disable and hide dot starting files (OPTIONAL)
		)
		// Trash volume
		/*
		array(
			'id'            => '1',
			'driver'        => 'Trash',
			'path'          => '../files/.trash/',
			'tmbURL'        => dirname($_SERVER['PHP_SELF']) . '/../files/.trash/.tmb/',
			'winHashFix'    => DIRECTORY_SEPARATOR !== '/', // to make hash same to Linux one on windows too
			'uploadDeny'    => array('all'),                // Recomend the same settings as the original volume that uses the trash
			'uploadAllow'   => array('image/x-ms-bmp', 'image/gif', 'image/jpeg', 'image/png', 'image/x-icon', 'text/plain'), // Same as above
			'uploadOrder'   => array('deny', 'allow'),      // Same as above
			'accessControl' => 'access',                    // Same as above
		)*/
	),
	'bind' =>	array(
		'upload.presave' => array(
			'Plugin.AutoResize.onUpLoadPreSave'
		)
	),
	'plugin' => array(
		'AutoResize' => array(
			'enable'		=> $autoResizeEnable,		// �摜�T�C�Y�𐧌�
			'maxWidth'		=> $maxWidth,		// �ő�摜��
			'maxHeight'		=> $maxHeight,		// �ő�摜����
			'quality'		=> 100			// JPEG image save quality
		)
	)
);

// run elFinder
$connector = new elFinderConnector(new elFinder($opts));
$connector->run();

