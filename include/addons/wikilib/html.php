<?php
/**
 * HTML作成ライブラリ
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2014 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */

// User-defined rules (convert without replacing source)
function make_line_rules($str)
{
	global $line_rules;
	static $pattern, $replace;

	if (! isset($pattern)) {
		$pattern = array_map(create_function('$a',
			'return \'/\' . $a . \'/\';'), array_keys($line_rules));
		$replace = array_values($line_rules);
		unset($line_rules);
	}
	return preg_replace($pattern, $replace, $str);
}

// Remove all HTML tags(or just anchor tags), and WikiName-speific decorations
function strip_htmltag($str, $all = TRUE)
{
	global $_symbol_noexists;
	static $noexists_pattern;

	if (! isset($noexists_pattern))
		$noexists_pattern = '#<span class="noexists">([^<]*)<a[^>]+>' .
			preg_quote($_symbol_noexists, '#') . '</a></span>#';

	// Strip Dagnling-Link decoration (Tags and "$_symbol_noexists")
	$str = preg_replace($noexists_pattern, '$1', $str);

	if ($all) {
		// All other HTML tags
		return preg_replace('#<[^>]+>#',        '', $str);
	} else {
		// All other anchor-tags only
		return preg_replace('#<a[^>]+>|</a>#i', '', $str);
	}
}

// Make heading string (remove heading-related decorations from Wiki text)
function make_heading(& $str, $strip = TRUE)
{
	global $NotePattern;

	// Cut fixed-heading anchors
	$id = '';
	$matches = array();
	if (preg_match('/^(\*{0,3})(.*?)\[#([A-Za-z][\w-]+)\](.*?)$/m', $str, $matches)) {
		$str = $matches[2] . $matches[4];
		$id  = $matches[3];
	} else {
		$str = preg_replace('/^\*{0,3}/', '', $str);
	}

	// Cut footnotes and tags
//	if ($strip === TRUE) $str = strip_htmltag(make_link(preg_replace($NotePattern, '', $str)));
	if ($strip === TRUE) $str = strip_htmltag(make_link(preg_replace_callback($NotePattern, create_function('$matches','return;'), $str)));		// PHP5.5用修正
	
	return $id;
}
?>
