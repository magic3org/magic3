<?php
/**
 * randomプラグイン
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2008 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: random.inc.php 1128 2008-10-25 10:59:47Z fishbone $
 * @link       http://www.magic3.org
 */
/*
 *プラグイン random
  配下のページをランダムに表示する

 *Usage
  #random(メッセージ)

 *パラメータ
 -メッセージ~
 リンクに表示する文字列

 */

function plugin_random_convert()
{
	//global $script, $vars;
	global $script;

	$title = '[Random Link]'; // default
	if (func_num_args()) {
		$args  = func_get_args();
		$title = $args[0];
	}

	return "<p><a href=\"$script" . WikiParam::convQuery('?plugin=random&amp;refer=' . rawurlencode(WikiParam::getPage())) . '">' . htmlspecialchars($title) . '</a></p>';
	//return "<p><a href=\"$script?plugin=random&amp;refer=" . rawurlencode($vars['page']) . '">' . htmlspecialchars($title) . '</a></p>';
}

function plugin_random_action()
{
	//global $vars;

	//$pattern = strip_bracket($vars['refer']) . '/';
	$refer = WikiParam::getRefer();
	$pattern = strip_bracket($refer) . '/';
	$pages = array();
	foreach (get_existpages() as $_page) {
		if (strpos($_page, $pattern) === 0)
			$pages[$_page] = strip_bracket($_page);
	}

	srand((double)microtime() * 1000000);
	$page = array_rand($pages);

	//if ($page != '') $vars['refer'] = $page;
	if ($page != '') WikiParam::setRefer($page);

	return array('body'=>'','msg'=>'');
}
?>
