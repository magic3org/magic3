<?php
/**
 * nofollowプラグイン
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
// Copyright (C) 2005 PukiWiki Developers Team
// License: The same as PukiWiki
// Output contents with "nofollow,noindex" option
function plugin_nofollow_convert()
{
	global $nofollow;

	$nofollow = 1;
	
	return '';
}
?>
