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
 * @copyright  Copyright 2006-2014 Magic3 Project.
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

include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'elFinderConnector.class.php';
include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'elFinder.class.php';
include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'elFinderVolumeDriver.class.php';
include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'elFinderVolumeLocalFileSystem.class.php';
// Required for MySQL storage connector
// include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'elFinderVolumeMySQL.class.php';
// Required for FTP connector support
// include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'elFinderVolumeFTP.class.php';

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

// ディレクトリ参照範囲を制限
$dirType = $gRequestManager->trimValueOf('dirtype');
if (!empty($dirType) && !in_array($dirType, array('image', 'flash', 'file'))){
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
	
// Documentation for connector options:
// https://github.com/Studio-42/elFinder/wiki/Connector-configuration-options
$opts = array(
	// 'debug' => true,
	'roots' => array(
		array(
			'driver'        => 'LocalFileSystem',   // driver for accessing file system (REQUIRED)
//			'path'          => '../files/',         // path to files (REQUIRED)
//			'URL'           => dirname($_SERVER['PHP_SELF']) . '/../files/', // URL to files (REQUIRED)
			'path'          => $path,		// path to files (REQUIRED)
			'URL'           => $url,		// URL to files (REQUIRED)
			'accessControl' => 'access'             // disable and hide dot starting files (OPTIONAL)
		)
	)
);

// run elFinder
$connector = new elFinderConnector(new elFinder($opts));
$connector->run();

