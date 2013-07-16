<?php
/**
 * lastmodプラグイン
 *
 * 機能：Wikiページの最終更新日時を表示する。
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2008 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: lastmod.inc.php 1098 2008-10-22 11:43:09Z fishbone $
 * @link       http://www.magic3.org
 */
// Originally written by Reimy, 2003

function plugin_lastmod_inline()
{
	//global $vars, $WikiName, $BracketName;
	global $WikiName, $BracketName;

	$args = func_get_args();
	$page = $args[0];

	if ($page == ''){
		//$page = $vars['page']; // Default: page itself
		$page = WikiParam::getPage();
	} else {
		if (preg_match("/^($WikiName|$BracketName)$/", strip_bracket($page))) {
			//$page = get_fullname(strip_bracket($page), $vars['page']);
			$page = get_fullname(strip_bracket($page), WikiParam::getPage());
		} else {
			return FALSE;
		}
	}
	if (! is_page($page)) return FALSE;

	return format_date(get_filetime($page));
}
?>
