<?php
/**
 * filelistプラグイン
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2008 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: filelist.inc.php 1126 2008-10-25 05:55:41Z fishbone $
 * @link       http://www.magic3.org
 */
//
// Filelist plugin: redirect to list plugin
// cmd=filelist

function plugin_filelist_action()
{
	return do_plugin_action('list');
}
?>
