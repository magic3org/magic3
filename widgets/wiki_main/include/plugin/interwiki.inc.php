<?php
/**
 * interwikiプラグイン
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2008 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: interwiki.inc.php 1095 2008-10-21 08:51:41Z fishbone $
 * @link       http://www.magic3.org
 */
function plugin_interwiki_action()
{
	//global $vars, $InterWikiName;
	global $InterWikiName;

	if (PKWK_SAFE_MODE) die_message('InterWiki plugin is not allowed');

	$match = array();
	//if (! preg_match("/^$InterWikiName$/", $vars['page'], $match)) return plugin_interwiki_invalid();
	if (!preg_match("/^$InterWikiName$/", WikiParam::getPage(), $match)) return plugin_interwiki_invalid();

	$url = get_interwiki_url($match[2], $match[3]);
	if ($url === FALSE) return plugin_interwiki_invalid();

	pkwk_headers_sent();
	header('Location: ' . $url);
	exit;
}

function plugin_interwiki_invalid()
{
	global $_title_invalidiwn, $_msg_invalidiwn;
	return array(
		'msg'  => $_title_invalidiwn,
		'body' => str_replace(array('$1', '$2'),
			array(htmlspecialchars(''),
			make_pagelink('InterWikiName')),
			$_msg_invalidiwn));
}
?>
