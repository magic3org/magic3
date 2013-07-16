<?php
/**
 * lsプラグイン
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2008 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: ls.inc.php 1099 2008-10-23 02:02:39Z fishbone $
 * @link       http://www.magic3.org
 */
// CopyRight 2002 Y.MASUI GPL2
// http://masui.net/pukiwiki/ masui@masui.net

function plugin_ls_convert()
{
//	global $vars;
	$with_title = FALSE;

	if (func_num_args())
	{
		$args = func_get_args();
		$with_title = in_array('title',$args);
	}

//	$prefix = $vars['page'].'/';
	$prefix = WikiParam::getPage() . '/';
	
	$pages = array();
	foreach (get_existpages() as $page)
	{
		if (strpos($page,$prefix) === 0)
		{
			$pages[] = $page;
		}
	}
	natcasesort($pages);

	$ls = array();
	foreach ($pages as $page)
	{
		$comment = '';
		if ($with_title)
		{
			list($comment) = get_source($page);
			// 見出しの固有ID部を削除
			$comment = preg_replace('/^(\*{1,3}.*)\[#[A-Za-z][\w-]+\](.*)$/','$1$2',$comment);

			$comment = '- ' . ereg_replace('^[-*]+','',$comment);
		}
		$ls[] = "-[[$page]] $comment";
	}
	return convert_html($ls);
}
?>
