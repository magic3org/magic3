<?php
/**
 * recentプラグイン
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
// Copyright (C)
//   2002-2006 PukiWiki Developers Team
//   2002      Y.MASUI http://masui.net/pukiwiki/ masui@masui.net
// License: GPL version 2
//
// Recent plugin -- Show RecentChanges list
//   * Usually used at 'MenuBar' page
//   * Also used at special-page, without no #recnet at 'MenuBar'

// Default number of 'Show latest N changes'
define('PLUGIN_RECENT_DEFAULT_LINES', 10);

define('PLUGIN_RECENT_USAGE', '#recent(number-to-show)');

function plugin_recent_convert()
{
	global $date_format, $_recent_plugin_frame, $show_passage;

	$recent_lines = PLUGIN_RECENT_DEFAULT_LINES;
	if (func_num_args()) {
		$args = func_get_args();
		if (! is_numeric($args[0]) || isset($args[1])) {
			return PLUGIN_RECENT_USAGE . '<br />';
		} else {
			$recent_lines = $args[0];
		}
	}

	// 最終更新データを取得
	$lines = WikiPage::getCacheRecentChanges();

	$script = get_script_uri();
	$date = $items = '';
	$lineCount = 0;			// 行数カウント用
	foreach ($lines as $line) {
		// 最終更新データの行を解析
		list($time, $page) = explode("\t", rtrim($line));
		if (empty($page)) continue;				// フォーマットに合わないデータは読み飛ばす

		$_date = get_date($date_format, $time);
		if ($date != $_date) {
			// End of the day
			if ($date != '') $items .= '</ul>' . "\n";

			// New day
			$date = $_date;
			$items .= '<strong>' . $date . '</strong>' . "\n" . '<ul class="recent_list">' . "\n";
		}

		$s_page = htmlspecialchars($page);
		if ($page == WikiParam::getPage()){
			// No need to link to the page you just read, or notify where you just read
			$items .= ' <li>' . $s_page . '</li>' . "\n";
		} else {
			$r_page = rawurlencode($page);
			$passage = $show_passage ? ' ' . get_passage($time) : '';
			//$items .= ' <li><a href="' . $script . '?' . $r_page . '"' . ' title="' . $s_page . $passage . '">' . $s_page . '</a></li>' . "\n";
			$items .= ' <li><a href="' . $script . WikiParam::convQuery('?' . $r_page) . '"' . ' title="' . $s_page . $passage . '">' . $s_page . '</a></li>' . "\n";
		}
		$lineCount++;
		if ($lineCount >= $recent_lines) break;
	}
	// End of the day
	if ($date != '') $items .= '</ul>' . "\n";

	//return sprintf($_recent_plugin_frame, count($lines), $items);
	return sprintf($_recent_plugin_frame, $lineCount, $items);
}
?>
