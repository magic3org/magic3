<?php
/**
 * sourceプラグイン
 *
 * 機能：Wikiページのソースコードを表示する。
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2017 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
function plugin_source_action()
{
	//global $vars, $_source_messages;
	global $_source_messages;

	if (PKWK_SAFE_MODE) die_message('PKWK_SAFE_MODE prohibits this');

/*	$page = isset($vars['page']) ? $vars['page'] : '';
	$vars['refer'] = $page;*/
	
	$page = WikiParam::getPage();
	WikiParam::setRefer($page);

	if (! WikiPage::isPage($page) || ! check_readable($page, false, false))
		return array('msg' => $_source_messages['msg_notfound'],
			'body' => $_source_messages['err_notfound']);

	/*return array('msg' => $_source_messages['msg_title'],
		'body' => '<pre id="source">' .
		htmlspecialchars(join('', get_source($page))) . '</pre>');*/
	return array('msg' => $_source_messages['msg_title'],
		'body' => '<pre class="wiki_pre">' .
		htmlspecialchars(join('', get_source($page))) . '</pre>');
}
?>
