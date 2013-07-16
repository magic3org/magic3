<?php
/**
 * brプラグイン
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2008 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: br.inc.php 1098 2008-10-22 11:43:09Z fishbone $
 * @link       http://www.magic3.org
 */
// Escape using <br> in <blockquote> (BugTrack/583)
define('PLUGIN_BR_ESCAPE_BLOCKQUOTE', 1);

//define('PLUGIN_BR_TAG', '<br class="spacer" />');
define('PLUGIN_BR_TAG', '<br class="wiki_spacer" />');

function plugin_br_convert()
{
	if (PLUGIN_BR_ESCAPE_BLOCKQUOTE) {
		//return '<div class="spacer">&nbsp;</div>';
		return '<div class="wiki_spacer">&nbsp;</div>';
	} else {
		return PLUGIN_BR_TAG;
	}
}
function plugin_br_inline()
{
	return PLUGIN_BR_TAG;
}
?>
