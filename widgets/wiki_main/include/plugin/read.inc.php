<?php
/**
 * readプラグイン
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

function plugin_read_action()
{
	global $_title_invalidwn, $_msg_invalidiwn;

	$page = WikiParam::getPage();
	
	if (is_page($page)) {
		// ページを表示
		check_readable($page, true, true);
		header_lastmod($page);
		return array('msg'=>'', 'body'=>'');
	} else if (! PKWK_SAFE_MODE && is_interwiki($page)) {
		return do_plugin_action('interwiki'); // InterWikiNameを処理
	} else if (is_pagename($page)) {
		WikiParam::setCmd('edit');
		return do_plugin_action('edit'); // 存在しないので、編集フォームを表示
	} else {
		// 無効なページ名
		return array(
			'msg'=>$_title_invalidwn,
			'body'=>str_replace('$1', htmlspecialchars($page),
				str_replace('$2', 'WikiName', $_msg_invalidiwn))
		);
	}
}
?>
