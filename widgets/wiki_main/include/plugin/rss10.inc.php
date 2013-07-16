<?php
/**
 * rss10プラグイン
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2008 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: rss10.inc.php 1140 2008-10-27 04:49:29Z fishbone $
 * @link       http://www.magic3.org
 */
function plugin_rss10_action()
{
	global $gPageManager;
	
	// ページ作成処理中断
	$gPageManager->abortPage();
	
	pkwk_headers_sent();
	header('Status: 301 Moved Permanently');
	//header('Location: ' . get_script_uri() . '?cmd=rss&ver=1.0'); // HTTP
	header('Location: ' . get_script_uri() . WikiParam::convQuery('?cmd=rss&ver=1.0', false)); // HTTP
	//exit;
	
	// システム強制終了
	$gPageManager->exitSystem();
}
?>
