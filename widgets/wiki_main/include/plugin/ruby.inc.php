<?php
/**
 * rubyプラグイン
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2008 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: ruby.inc.php 1103 2008-10-23 05:12:30Z fishbone $
 * @link       http://www.magic3.org
 */
// Ruby annotation plugin: Add a pronounciation into kanji-word or acronym(s)
// See also about ruby: http://www.w3.org/TR/ruby/
//
// NOTE:
//  Ruby tag works with MSIE only now,
//  but readable for other browsers like: 'words(pronunciation)'

define('PLUGIN_RUBY_USAGE', '&ruby(pronunciation){words};');

function plugin_ruby_inline()
{
	if (func_num_args() != 2) return PLUGIN_RUBY_USAGE;

	list($ruby, $body) = func_get_args();

	// strip_htmltag() is just for avoiding AutoLink insertion
	$body = strip_htmltag($body);

	if ($ruby == '' || $body == '') return PLUGIN_RUBY_USAGE;

	return '<ruby><rb>' . $body . '</rb>' . '<rp>(</rp>' .
		'<rt>' .  htmlspecialchars($ruby) . '</rt>' . '<rp>)</rp>' .
		'</ruby>';
}
?>
