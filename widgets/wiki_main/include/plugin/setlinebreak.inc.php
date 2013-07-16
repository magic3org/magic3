<?php
/**
 * linebreakプラグイン
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2008 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: setlinebreak.inc.php 1121 2008-10-25 03:21:07Z fishbone $
 * @link       http://www.magic3.org
 */
// Set linebreak plugin - on/of linebreak-to-'<br />' conversion
//
// Usage:
//	#setlinebreak          : Invert on/off
//	#setlinebreak(on)      : ON  (from this line)
//	#setlinebreak(off)     : OFF (from this line)
//	#setlinebreak(default) : Reset

function plugin_setlinebreak_convert()
{
	global $line_break;
	static $default;

	if (! isset($default)) $default = $line_break;

	if (func_num_args() == 0) {
		// Invert
		$line_break = ! $line_break;
	} else {
		$args = func_get_args();
		switch (strtolower($args[0])) {
		case 'on':	/*FALLTHROUGH*/
		case 'true':	/*FALLTHROUGH*/
		case '1':
			$line_break = 1;
			break;

		case 'off':	/*FALLTHROUGH*/
		case 'false':	/*FALLTHROUGH*/
		case '0':
			$line_break = 0;
			break;

		case 'default':
			$line_break = $default;
			break;

		default:
			return '#setlinebreak: Invalid argument: ' .
				htmlspecialchars($args[0]) . '<br />';
		}
	}
	return '';
}
?>
