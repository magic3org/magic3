<?php
/**
 * deletedプラグイン
 *
 * 機能：リンク情報を更新
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2008 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: deleted.inc.php 1101 2008-10-23 03:57:37Z fishbone $
 * @link       http://www.magic3.org
 */
// Show deleted (= Exists in BACKUP_DIR or DIFF_DIR but not in DATA_DIR)
// page list to clean them up
//
// Usage:
//   index.php?plugin=deleted[&file=on]
//   index.php?plugin=deleted&dir=diff[&file=on]

function plugin_deleted_action()
{
//	global $vars;
	global $_deleted_plugin_title, $_deleted_plugin_title_withfilename;

	//$dir = isset($vars['dir']) ? $vars['dir'] : 'backup';
	//$withfilename  = isset($vars['file']);
	$dir = WikiParam::getVar('dir');
	if ($dir == '') $dir = 'backup';
	//$withfilename  = (WikiParam::getVar('file') != '');

/*	$_DIR['diff'  ]['dir'] = DIFF_DIR;
	$_DIR['diff'  ]['ext'] = '.txt';
	$_DIR['backup']['dir'] = BACKUP_DIR;
	$_DIR['backup']['ext'] = BACKUP_EXT; // .gz or .txt
	*/
	//$_DIR['cache' ]['dir'] = CACHE_DIR; // No way to delete them via web browser now
	//$_DIR['cache' ]['ext'] = '.ref';
	//$_DIR['cache' ]['ext'] = '.rel';

/*	if (! isset($_DIR[$dir]))
		return array('msg'=>'Deleted plugin', 'body'=>'No such setting: Choose backup or diff');
		*/
	// 削除済みのページリストを作成
	$deleted_pages = array_diff(WikiPage::getAllPages(), get_existpages());

	/*if ($withfilename) {
		$retval['msg'] = $_deleted_plugin_title_withfilename;
	} else {
		$retval['msg'] = $_deleted_plugin_title;
	}
	$retval['body'] = page_list($deleted_pages, $dir, $withfilename);
	*/
	$retval['msg'] = $_deleted_plugin_title;
	$retval['body'] = page_list($deleted_pages, $dir);

	return $retval;
}
?>
