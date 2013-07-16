<?php
/**
 * versionプラグイン
 *
 * 機能：PukiWikiのバージョンを表示する。
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2008 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: version.inc.php 1098 2008-10-22 11:43:09Z fishbone $
 * @link       http://www.magic3.org
 */
function plugin_version_convert()
{
	if (PKWK_SAFE_MODE) return ''; // Show nothing

	return '<p>' . S_VERSION . '</p>';
}

function plugin_version_inline()
{
	if (PKWK_SAFE_MODE) return ''; // Show nothing

	return S_VERSION;
}
?>
