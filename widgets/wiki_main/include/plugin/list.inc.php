<?php
/**
 * listプラグイン
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2008 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: list.inc.php 1084 2008-10-18 06:10:55Z fishbone $
 * @link       http://www.magic3.org
 */
function plugin_list_action()
{
	//global $vars, $_title_list, $_title_filelist, $whatsnew;
	global $_title_list, $_title_filelist;

	// Redirected from filelist plugin?
	//$filelist = (isset($vars['cmd']) && $vars['cmd'] == 'filelist');
	$filelist = (WikiParam::getCmd() == 'filelist');

	return array(
		'msg'=>$filelist ? $_title_filelist : $_title_list,
		'body'=>plugin_list_getlist($filelist));
}

// Get a list
function plugin_list_getlist($withfilename = FALSE)
{
	//global $non_list, $whatsnew;
	global $non_list;

	//$pages = array_diff(get_existpages(), array($whatsnew));
	$pages = array_diff(get_existpages(), array(WikiConfig::getWhatsnewPage()));
	if (! $withfilename)
		$pages = array_diff($pages, preg_grep('/' . $non_list . '/S', $pages));
	if (empty($pages)) return '';

	return page_list($pages, 'read', $withfilename);
}
?>
