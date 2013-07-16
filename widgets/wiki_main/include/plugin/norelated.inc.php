<?php
/**
 * norelatedプラグイン
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2008 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: norelated.inc.php 1100 2008-10-23 02:36:14Z fishbone $
 * @link       http://www.magic3.org
 */
// - Stop showing related link automatically if $related_link = 1

function plugin_norelated_convert()
{
	global $related_link;
	
	$related_link = 0;
	return '';
}
?>
