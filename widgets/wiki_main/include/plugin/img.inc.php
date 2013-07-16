<?php
/**
 * imageプラグイン
 *
 * 機能：画像を表示する。
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2008 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: img.inc.php 1098 2008-10-22 11:43:09Z fishbone $
 * @link       http://www.magic3.org
 */
define('PLUGIN_IMG_USAGE', '#img(): Usage: (URI-to-image[,right[,clear]])<br />' . "\n");
define('PLUGIN_IMG_CLEAR', '<div style="clear:both"></div>' . "\n"); // Stop word-wrapping

// Output inline-image tag from a URI
function plugin_img_convert()
{
	if (PKWK_DISABLE_INLINE_IMAGE_FROM_URI)
		return '#img(): PKWK_DISABLE_INLINE_IMAGE_FROM_URI prohibits this' .
			'<br>' . "\n";

	$args = func_get_args();

	// Check the 2nd argument first, for compatibility
	$arg = isset($args[1]) ? strtoupper($args[1]) : '';
	if ($arg == '' || $arg == 'L' || $arg == 'LEFT') {
		$align = 'left';
	} else if ($arg == 'R' || $arg == 'RIGHT') {
		$align = 'right';
	} else {
		// Stop word-wrapping only (Ugly but compatible)
		// Short usage: #img(,clear)
		return PLUGIN_IMG_CLEAR;
	}

	$url = isset($args[0]) ? $args[0] : '';
	if (! is_url($url) || ! preg_match('/\.(jpe?g|gif|png)$/i', $url))
		return PLUGIN_IMG_USAGE;

	$arg = isset($args[2]) ? strtoupper($args[2]) : '';
	$clear = ($arg == 'C' || $arg == 'CLEAR') ? PLUGIN_IMG_CLEAR : '';

	return <<<EOD
<div style="float:$align;padding:.5em 1.5em .5em 1.5em">
 <img src="$url" alt="" />
</div>$clear
EOD;
}
?>
