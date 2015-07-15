<?php
/**
 * addプラグイン
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2015 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
// Add plugin - Append new text below/above existing page
// Usage: cmd=add&page=pagename

function plugin_add_action()
{
	global $_title_add, $_msg_add;

	if (PKWK_READONLY) die_message('PKWK_READONLY prohibits editing');

	$page = WikiParam::getPage();
	check_editable($page);

	return array(
		'msg'  => $_title_add,
		'body' =>
			'<ul>' . "\n" .
			' <li>' . $_msg_add . '</li>' . "\n" .
			'</ul>' . "\n" .
			edit_form($page, '', false, true, 'add')
		);
}
?>
