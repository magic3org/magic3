<?php
/**
 * onlineプラグイン
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2010 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: online.inc.php 3474 2010-08-13 10:36:48Z fishbone $
 * @link       http://www.magic3.org
 */
// Copyright (C)
//   2002-2005 PukiWiki Developers Team
//   2001-2002 Originally written by yu-ji
// License: GPL v2 or (at your option) any later version
//
// Online plugin -- Just show the number 'users-on-line'

define('PLUGIN_ONLINE_TIMEOUT', 60 * 5); // Count users in N seconds

// ----

// List of 'IP-address|last-access-time(seconds)'
//define('PLUGIN_ONLINE_USER_LIST', COUNTER_DIR . 'user.dat');

// Regex of 'IP-address|last-access-time(seconds)'
define('PLUGIN_ONLINE_LIST_REGEX', '/^([^\|]+)\|([0-9]+)$/');

function plugin_online_convert()
{
	return plugin_online_itself(0);
}

function plugin_online_inline()
{
	return plugin_online_itself(1);
}

function plugin_online_itself($type = 0)
{
	static $count, $result, $base;

	if (! isset($count)) {
		if (isset($_SERVER['REMOTE_ADDR'])) {
			$host  = $_SERVER['REMOTE_ADDR'];
		} else {
			$host  = '';
		}

		// Try read
		if (plugin_online_check_online($count, $host)) {
			$result = TRUE;
		} else {
			// Write
			$result = plugin_online_sweep_records($host);
		}
	}

	if ($result) {
		return $count; // Integer
	} else {
		/*if (! isset($base)) $base = basename(PLUGIN_ONLINE_USER_LIST);
		$error = '"COUNTER_DIR/' . $base . '" not writable';
		if ($type == 0) {
			$error = '#online: ' . $error . '<br />' . "\n";
		} else {
			$error = '&online: ' . $error . ';';
		}
		*/
		if ($type == 0) {
			$error = '#online: error<br />' . "\n";
		} else {
			$error = '&online: error;';
		}
		return $error; // String
	}
}

// Check I am already online (recorded and not time-out)
// $count == Number of online users
function plugin_online_check_online(&$count, $host = '')
{
/*	if (! file_exists(PLUGIN_ONLINE_USER_LIST) &&
	    ! @touch(PLUGIN_ONLINE_USER_LIST))
		return FALSE;

	// Open
	$fp = @fopen(PLUGIN_ONLINE_USER_LIST, 'r');
	if ($fp == FALSE) return FALSE;
	set_file_buffer($fp, 0);
*/

	// Init
	$count   = 0;
	$found   = FALSE;
	$matches = array();
/*
	flock($fp, LOCK_SH);

	// Read
	while (! feof($fp)) {
		$line = fgets($fp, 512);
		if ($line === FALSE) continue;

		// Ignore invalid-or-outdated lines
		if (! preg_match(PLUGIN_ONLINE_LIST_REGEX, $line, $matches) ||
		    ($matches[2] + PLUGIN_ONLINE_TIMEOUT) <= UTIME ||
		    $matches[2] > UTIME) continue;

		++$count;
		if (! $found && $matches[1] == $host) $found = TRUE;
	}
*/
	// 参照中のユーザデータを取得
	$lines = WikiPage::getCacheUserOnline();
	foreach ($lines as $line){
		// Ignore invalid-or-outdated lines
		if (! preg_match(PLUGIN_ONLINE_LIST_REGEX, $line, $matches) ||
		    ($matches[2] + PLUGIN_ONLINE_TIMEOUT) <= UTIME ||
		    $matches[2] > UTIME) continue;

		++$count;
		if (! $found && $matches[1] == $host) $found = TRUE;
	}
	
//	flock($fp, LOCK_UN);

//	if(! fclose($fp)) return FALSE;

	if (! $found && $host != '') ++$count; // About you

	return $found;
}

// Cleanup outdated records, Add/Replace new record, Return the number of 'users in N seconds'
// NOTE: Call this when plugin_online_check_online() returnes FALSE
function plugin_online_sweep_records($host = '')
{
/*
	// Open
	$fp = @fopen(PLUGIN_ONLINE_USER_LIST, 'r+');
	if ($fp == FALSE) return FALSE;
	set_file_buffer($fp, 0);

	flock($fp, LOCK_EX);

	// Read to check
	$lines = @file(PLUGIN_ONLINE_USER_LIST);
	if ($lines === FALSE) $lines = array();
	*/
	// 参照中のユーザデータを取得
	$lines = WikiPage::getCacheUserOnline();

	// Need modify?
	$line_count = $count = count($lines);
	$matches = array();
	$dirty   = FALSE;
	for ($i = 0; $i < $line_count; $i++) {
		if (! preg_match(PLUGIN_ONLINE_LIST_REGEX, $lines[$i], $matches) ||
		    ($matches[2] + PLUGIN_ONLINE_TIMEOUT) <= UTIME ||
		    $matches[2] > UTIME ||
		    $matches[1] == $host) {
			unset($lines[$i]); // Invalid or outdated or invalid date
			--$count;
			$dirty = TRUE;
		}
	}
	if ($host != '' ) {
		// Add new, at the top of the record
		array_unshift($lines, strtr($host, "\n", '') . '|' . UTIME . "\n");
		++$count;
		$dirty = TRUE;
	}

	if ($dirty) {
		/*
		// Write
		if (! ftruncate($fp, 0)) return FALSE;
		rewind($fp);
		fputs($fp, join('', $lines));
		*/
		WikiPage::updateCacheUserOnline(join('', $lines));
	}

//	flock($fp, LOCK_UN);

//	if(! fclose($fp)) return FALSE;

	return $count; // Number of lines == Number of users online
}
?>
