<?php
/**
 * calendar_editプラグイン
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2008 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: calendar_edit.inc.php 1148 2008-10-28 07:16:55Z fishbone $
 * @link       http://www.magic3.org
 */
// Copyright (C)
//   2003,2005 PukiWiki Developers Team
//   2001-2002 Originally written by yu-ji
// License: GPL v2 or (at your option) any later version
//
// Calendar_ edit plugin (needs calendar plugin)

function plugin_calendar_edit_convert()
{
	global $command;

	if (! file_exists(PLUGIN_DIR . 'calendar.inc.php')) return FALSE;

	require_once PLUGIN_DIR . 'calendar.inc.php';
	if (! function_exists('plugin_calendar_convert')) return FALSE;

	$command = 'edit';
	$args = func_num_args() ? func_get_args() : array();
	return call_user_func_array('plugin_calendar_convert', $args);
}
?>
