<?php
/**
 * popularプラグイン
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
// Popular pages plugin: Show an access ranking of this wiki
// -- like recent plugin, using counter plugin's count --
/*
 * (C) 2003-2005 PukiWiki Developers Team
 * (C) 2002 Kazunori Mizushima <kazunori@uc.netyou.jp>
 *
 * 通算および今日に別けて一覧を作ることができます。
 *
 * [Usage]
 *   #popular
 *   #popular(20)
 *   #popular(20,FrontPage|MenuBar)
 *   #popular(20,FrontPage|MenuBar,true)
 *
 * [Arguments]
 *   1 - 表示する件数                             default 10
 *   2 - 表示させないページの正規表現             default なし
 *   3 - 通算(true)か今日(false)の一覧かのフラグ  default false
 */

define('PLUGIN_POPULAR_DEFAULT', 10);

function plugin_popular_convert()
{
	//global $vars, $whatsnew;
	global $_popular_plugin_frame, $_popular_plugin_today_frame;

	$max    = PLUGIN_POPULAR_DEFAULT;
	$except = '';

	$array = func_get_args();
	$today = FALSE;
	switch (func_num_args()) {
	case 3: if ($array[2]) $today = get_date('Y/m/d');
	case 2: $except = $array[1];
	case 1: $max    = $array[0];
	}

	$counters = array();
	$pages = WikiPage::getCountPages();
	foreach ($pages as $page){
	//foreach (get_existpages(COUNTER_DIR, '.count') as $file=>$page) {
		//if (($except != '' && ereg($except, $page)) || $page == $whatsnew || check_non_list($page) || ! WikiPage::isPage($page)) continue;
		if (($except != '' && ereg($except, $page)) || $page == WikiConfig::getWhatsnewPage() || check_non_list($page) || ! WikiPage::isPage($page)) continue;

		//$array = file(COUNTER_DIR . $file);
		$array = WikiPage::getPageCount($page);
		$count = rtrim($array[0]);
		$date  = rtrim($array[1]);
		$today_count = rtrim($array[2]);

		if ($today) {
			// $pageが数値に見える(たとえばencode('BBS')=424253)とき、
			// array_splice()によってキー値が変更されてしまうのを防ぐ
			// ため、キーに '_' を連結する
			if ($today == $date) $counters['_' . $page] = $today_count;
		} else {
			$counters['_' . $page] = $count;
		}
	}

	asort($counters, SORT_NUMERIC);

	// BugTrack2/106: Only variables can be passed by reference from PHP 5.0.5
	$counters = array_reverse($counters, TRUE); // with array_splice()
	$counters = array_splice($counters, 0, $max);

	$items = '';
	if (! empty($counters)) {
		$items = '<ul class="popular_list">' . "\n";

		foreach ($counters as $page=>$count) {
			$page = substr($page, 1);

			$s_page = htmlspecialchars($page);
			//if ($page == $vars['page']) {
			if ($page == WikiParam::getPage()){
				// No need to link itself, notifies where you just read
				$pg_passage = get_pg_passage($page,FALSE);
				$items .= ' <li><span title="' . $s_page . ' ' . $pg_passage . '">' .
					$s_page . '<span class="counter">(' . $count .
					')</span></span></li>' . "\n";
			} else {
				$items .= ' <li>' . make_pagelink($page,
					$s_page . '<span class="counter">(' . $count . ')</span>') .
					'</li>' . "\n";
			}
		}
		$items .= '</ul>' . "\n";
	}

	return sprintf($today ? $_popular_plugin_today_frame : $_popular_plugin_frame, count($counters), $items);
}
?>
