<?php
/**
 * counterプラグイン
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2017 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
// Copyright (C)
//   2002-2005 PukiWiki Developers Team
//   2002 Y.MASUI GPL2 http://masui.net/pukiwiki/ masui@masui.net

//define('PLUGIN_COUNTER_SUFFIX', '.count');// Counter file's suffix

// Report one
function plugin_counter_inline()
{
//	global $vars;

	// BugTrack2/106: Only variables can be passed by reference from PHP 5.0.5
	$args = func_get_args(); // with array_shift()

	$arg = strtolower(array_shift($args));
	switch ($arg) {
	case ''     : $arg = 'total'; /*FALLTHROUGH*/
	case 'total': /*FALLTHROUGH*/
	case 'today': /*FALLTHROUGH*/
	case 'yesterday':
		//$counter = plugin_counter_get_count($vars['page']);
		$counter = plugin_counter_get_count(WikiParam::getPage());
		return $counter[$arg];
	default:
		return '&counter([total|today|yesterday]);';
	}
}

// Report all
function plugin_counter_convert()
{
//	global $vars;

//	$counter = plugin_counter_get_count($vars['page']);
	$counter = plugin_counter_get_count(WikiParam::getPage());
	return <<<EOD
<div class="counter">
Counter:   {$counter['total']},
today:     {$counter['today']},
yesterday: {$counter['yesterday']}
</div>
EOD;
}

// Return a summary
function plugin_counter_get_count($page)
{
//	global $vars;
	static $counters = array();
	static $default;

	if (! isset($default))
		$default = array(
			'total'     => 0,
			'date'      => get_date('Y/m/d'),
			'today'     => 0,
			'yesterday' => 0,
			'ip'        => '');

	if (! WikiPage::isPage($page)) return $default;
	if (isset($counters[$page])) return $counters[$page];

	// Set default
	$counters[$page] = $default;
	$modify = FALSE;

/*	$file = COUNTER_DIR . encode($page) . PLUGIN_COUNTER_SUFFIX;
	$fp = fopen($file, file_exists($file) ? 'r+' : 'w+')
		or die('counter.inc.php: Cannot open COUTER_DIR/' . basename($file));
	set_file_buffer($fp, 0);
	flock($fp, LOCK_EX);
	rewind($fp);
	foreach ($default as $key=>$val) {
		// Update
		$counters[$page][$key] = rtrim(fgets($fp, 256));
		if (feof($fp)) break;
	}*/
	// アクセスカウントデータ読み込み
	$lines = WikiPage::getPageCount($page);
	$i = 0;
	foreach ($default as $key => $val){
		if ($i >= count($lines)) break;
		$counters[$page][$key] = rtrim($lines[$i]);
		$i++;
	}
	
	if ($counters[$page]['date'] != $default['date']) {
		// New day
		$modify = TRUE;
		$is_yesterday = ($counters[$page]['date'] == get_date('Y/m/d', strtotime('yesterday', UTIME)));
		$counters[$page]['ip']        = $_SERVER['REMOTE_ADDR'];
		$counters[$page]['date']      = $default['date'];
		$counters[$page]['yesterday'] = $is_yesterday ? $counters[$page]['today'] : 0;
		$counters[$page]['today']     = 1;
		$counters[$page]['total']++;

	} else if ($counters[$page]['ip'] != $_SERVER['REMOTE_ADDR']) {
		// Not the same host
		$modify = TRUE;
		$counters[$page]['ip']        = $_SERVER['REMOTE_ADDR'];
		$counters[$page]['today']++;
		$counters[$page]['total']++;
	}
	// Modify
	/*if ($modify && $vars['cmd'] == 'read') {
		rewind($fp);
		ftruncate($fp, 0);
		foreach (array_keys($default) as $key)
			fputs($fp, $counters[$page][$key] . "\n");
	}
	flock($fp, LOCK_UN);
	fclose($fp);*/

	// アクセスカウントデータを更新
	if ($modify && WikiParam::getCmd() == 'read'){
		$updateData = '';
		foreach (array_keys($default) as $key){
			$updateData .= $counters[$page][$key] . "\n";
		}
		WikiPage::updatePageCount($page, $updateData);
	}
	return $counters[$page];
}
?>
