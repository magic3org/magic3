<?php
/**
 * serverプラグイン
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2008 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: server.inc.php 1173 2008-11-05 01:01:47Z fishbone $
 * @link       http://www.magic3.org
 */
// Server information plugin
// by Reimy http://pukiwiki.reimy.com/

function plugin_server_convert()
{
	if (PKWK_SAFE_MODE) return ''; // Show nothing

	return '<dl class="wiki_list">' . "\n" .
		'<dt>Server Name</dt>'     . '<dd>' . SERVER_NAME . '</dd>' . "\n" .
		'<dt>Server Software</dt>' . '<dd>' . SERVER_SOFTWARE . '</dd>' . "\n" .
		'<dt>Server Admin</dt>'    . '<dd>' .
			'<a href="mailto:' . SERVER_ADMIN . '">' .
			SERVER_ADMIN . '</a></dd>' . "\n" .
		'</dl>' . "\n";
}
?>
