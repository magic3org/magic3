<?php
/**
 * stationaryプラグイン
 *
 * 機能：プラグイン作成用の雛形
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2008 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: stationary.inc.php 1128 2008-10-25 10:59:47Z fishbone $
 * @link       http://www.magic3.org
 */

// Define someting like this
define('PLUGIN_STATIONARY_MAX', 10);

// Init someting
function plugin_stationary_init()
{
	if (PKWK_SAFE_MODE || PKWK_READONLY) return; // Do nothing

	$messages = array(
		'_plugin_stationary_A' => 'a',
		'_plugin_stationary_B' => array('C' => 'c', 'D'=>'d'),
		);
	set_plugin_messages($messages);
}

// Convert-type plugin: #stationary or #stationary(foo)
function plugin_stationary_convert()
{
	// If you don't want this work at secure/productive site,
	if (PKWK_SAFE_MODE) return ''; // Show nothing

	// If this plugin will write someting,
	if (PKWK_READONLY) return ''; // Show nothing

	// Init
	$args = array();
	$result = '';

	// Get arguments
	if (func_num_args()) {
		$args = func_get_args();
		foreach	(array_keys($args) as $key)
			$args[$key] = trim($args[$key]);
		$result = join(',', $args);
	}

	return '#stationary(' . htmlspecialchars($result) . ')<br />';
}

// In-line type plugin: &stationary; or &stationary(foo); , or &stationary(foo){bar};
function plugin_stationary_inline()
{
	if (PKWK_SAFE_MODE || PKWK_READONLY) return ''; // See above

	// {bar} is always exists, and already sanitized
	$args = func_get_args();
	$body = strip_autolink(array_pop($args)); // {bar}

	foreach	(array_keys($args) as $key)
		$args[$key] = trim($args[$key]);
	$result = join(',', $args);

	return '&amp;stationary(' . htmlspecialchars($result) . '){' . $body . '};';
}

// Action-type plugin: ?plugin=stationary&foo=bar
function plugin_stationary_action()
{
	// See above
	if (PKWK_SAFE_MODE || PKWK_READONLY)
		die_message('PKWK_SAFE_MODE or PKWK_READONLY prohibits this');

	$msg  = 'Message';
	$body = 'Message body';

	return array('msg'=>htmlspecialchars($msg), 'body'=>htmlspecialchars($body));
}
?>
