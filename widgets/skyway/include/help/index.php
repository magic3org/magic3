<?php
/**
 * ヘルプリソースファイル
 * index.php
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2018 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: index.php 1984 2009-06-15 09:07:38Z fishbone $
 * @link       http://www.magic3.org
 */
// 直接アクセスの防止
defined('M3_SYSTEM') or die('Access error: Direct access denied.');

global $HELP;

// ########## SkyWay項目一覧 ##########
$HELP['untitledtask_apikey']['title'] = 'SkyWay APIキー';
$HELP['untitledtask_apikey']['body'] = 'SkyWayに登録してAPIキー取得してください。(https://webrtc.ecl.ntt.com/signup.html)';
?>
