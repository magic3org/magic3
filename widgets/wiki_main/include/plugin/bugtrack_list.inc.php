<?php
/**
 * bugtrack_listプラグイン
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2008 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: bugtrack_list.inc.php 1146 2008-10-28 04:00:49Z fishbone $
 * @link       http://www.magic3.org
 */
// Copyright
// 2002-2005 PukiWiki Developers Team
// 2002 Y.MASUI GPL2 http://masui.net/pukiwiki/ masui@masui.net

require_once(PLUGIN_DIR . 'bugtrack.inc.php');

function plugin_bugtrack_list_init()
{
	plugin_bugtrack_init();
}
?>
