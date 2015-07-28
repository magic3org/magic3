<?php
/**
 * naviプラグイン
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
/*
 * Usage:
 *   #navi(contents-page-name)   <for ALL child pages>
 *   #navi([contents-page-name][,reverse]) <for contents page>
 *
 * Parameter:
 *   contents-page-name - Page name of home of the navigation (default:itself)
 *   reverse            - Show contents revese
 *
 * Behaviour at contents page:
 *   Always show child-page list like 'ls' plugin
 *
 * Behaviour at child pages:
 *
 *   The first plugin call - Show a navigation bar like a DocBook header
 *
 *     Prev  <contents-page-name>  Next
 *     --------------------------------
 *
 *   The second call - Show a navigation bar like a DocBook footer
 *
 *     --------------------------------
 *     Prev          Home          Next
 *     <pagename>     Up     <pagename>
 *
 * Page-construction example:
 *   foobar    - Contents page, includes '#navi' or '#navi(foobar)'
 *   foobar/1  - One of child pages, includes one or two '#navi(foobar)'
 *   foobar/2  - One of child pages, includes one or two '#navi(foobar)'
 */

// Exclusive regex pattern of child pages
define('PLUGIN_NAVI_EXCLUSIVE_REGEX', '');
//define('PLUGIN_NAVI_EXCLUSIVE_REGEX', '#/_#'); // Ignore 'foobar/_memo' etc.

// Insert <link rel=... /> tags into XHTML <head></head>
define('PLUGIN_NAVI_LINK_TAGS', FALSE);	// FALSE, TRUE

// ----

function plugin_navi_convert()
{
	global $script, $head_tags;
	global $_navi_prev, $_navi_next, $_navi_up, $_navi_home;
	global $gEnvManager;
	static $navi = array();

	$current = WikiParam::getPage();
	$reverse = FALSE;
	if (func_num_args()) {
		list($home, $reverse) = array_pad(func_get_args(), 2, '');
		// strip_bracket() is not necessary but compatible
		$home    = get_fullname(strip_bracket($home), $current);
		$is_home = ($home == $current);
		if (! is_page($home)) {
			return '#navi(contents-page-name): No such page: ' .
				htmlspecialchars($home) . '<br />';
		} else if (! $is_home &&
		    ! preg_match('/^' . preg_quote($home, '/') . '/', $current)) {
			return '#navi(' . htmlspecialchars($home) .
				'): Not a child page like: ' .
				htmlspecialchars($home . '/' . basename($current)) .
				'<br />';
		}
		$reverse = (strtolower($reverse) == 'reverse');
	} else {
		$home    = WikiParam::getPage();
		$is_home = TRUE; // $home == $current
	}

	$pages  = array();
	$footer = isset($navi[$home]); // The first time: FALSE, the second: TRUE
	if (! $footer) {
		$navi[$home] = array(
			'up'   =>'',
			'prev' =>'',
			'prev1'=>'',
			'next' =>'',
			'next1'=>'',
			'home' =>'',
			'home1'=>'',
		);

		$pages = preg_grep('/^' . preg_quote($home, '/') . '($|\/)/', get_existpages());
		if (PLUGIN_NAVI_EXCLUSIVE_REGEX != '') {
			// If old PHP could use preg_grep(,,PREG_GREP_INVERT)...
			$pages = array_diff($pages, preg_grep(PLUGIN_NAVI_EXCLUSIVE_REGEX, $pages));
		}
		$pages[] = $current; // Sentinel :)
		$pages   = array_unique($pages);
		natcasesort($pages);
		if ($reverse) $pages = array_reverse($pages);

		$prev = $home;
		foreach ($pages as $page) {
			if ($page == $current) break;
			$prev = $page;
		}
		$next = current($pages);

		$pos = strrpos($current, '/');
		$up = '';
		if ($pos > 0) {
			$up = substr($current, 0, $pos);
			$navi[$home]['up']    = make_pagelink($up, $_navi_up);
		}
		if (! $is_home) {
			$navi[$home]['prev']  = make_pagelink($prev);
			$navi[$home]['prev1'] = make_pagelink($prev, $_navi_prev);
		}
		if ($next != '') {
			$navi[$home]['next']  = make_pagelink($next);
			$navi[$home]['next1'] = make_pagelink($next, $_navi_next);
		}
		$navi[$home]['home']  = make_pagelink($home);
		$navi[$home]['home1'] = make_pagelink($home, $_navi_home);

		// Generate <link> tag: start next prev(previous) parent(up)
		// Not implemented: contents(toc) search first(begin) last(end)
		if (PLUGIN_NAVI_LINK_TAGS) {
			foreach (array('start'=>$home, 'next'=>$next,
			    'prev'=>$prev, 'up'=>$up) as $rel=>$_page) {
				if ($_page != '') {
					$s_page = htmlspecialchars($_page);
					$r_page = rawurlencode($_page);
					$head_tags[] = ' <link rel="' .
						$rel . '" href="' . $script .
						'?' . $r_page . '" title="' .
						$s_page . '" />';
				}
			}
		}
	}

	$body = '';
	
	// テンプレートタイプに合わせて出力を変更
	$templateType = $gEnvManager->getCurrentTemplateType();

	if ($is_home) {
		// Show contents
		$count = count($pages);
		if ($count == 0) {
			return '#navi(contents-page-name): You already view the result<br />';
		} else if ($count == 1) {
			// Sentinel only: Show usage and warning
			$home = htmlspecialchars($home);
			$body .= '#navi(' . $home . '): No child page like: ' . $home . '/Foo';
		} else {
			$body .= '<ul>';
			foreach ($pages as $page)
				if ($page != $home)
					$body .= ' <li>' . make_pagelink($page) . '</li>';
			$body .= '</ul>';
		}
	} else if (! $footer){
		// Header
		$body = '<ul class="navi">' . M3_NL;
		$body .= '<li class="navi_left"><strong>' . $navi[$home]['prev1'] . '</strong></li>' . M3_NL;
		$body .= '<li class="navi_right"><strong>' . $navi[$home]['next1'] . '</strong></li>' . M3_NL;
		$body .= '<li class="navi_none"><strong>' . $navi[$home]['home'] . '</strong></li>' . M3_NL;
		$body .= '</ul>' . M3_NL;
		
		if ($templateType == M3_TEMPLATE_BOOTSTRAP_30){		// Bootstrap型テンプレートの場合
			$body = '<div class="well">' . $body . '</div>';
		} else {
			$body .= '<hr class="full_hr" />' . M3_NL;
		}
	} else {
		// Footer
		$body = '<ul class="navi">' . M3_NL;
		$body .= '<li class="navi_left"><strong>' . $navi[$home]['prev1'] . '<br />' . $navi[$home]['prev'] . '</strong></li>' . M3_NL;
		$body .= '<li class="navi_right"><strong>' . $navi[$home]['next1'] . '<br />' . $navi[$home]['next'] . '</strong></li>' . M3_NL;
		$body .= '<li class="navi_none"><strong>' . $navi[$home]['home1'] . '<br />' . $navi[$home]['up'] . '</strong></li>' . M3_NL;
		$body .= '</ul>' . M3_NL;
		
		if ($templateType == M3_TEMPLATE_BOOTSTRAP_30){		// Bootstrap型テンプレートの場合
			$body = '<div class="well">' . $body . '</div>';
		} else {
			$body = '<hr class="full_hr" />' . M3_NL . $body;
		}
	}
	return $body;
}
?>
