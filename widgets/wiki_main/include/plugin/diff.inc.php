<?php
/**
 * diffプラグイン
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
// Copyright (C)
//   2002-2005 PukiWiki Developers Team
//   2002      Originally written by yu-ji
// License: GPL v2 or (at your option) any later version

function plugin_diff_action()
{
	// ### パスワード認証フォーム表示 ###
	// 認証されている場合はスルーして関数以降を実行
	$retStatus = password_form();
	if (!empty($retStatus)) return $retStatus;
	
	$page = WikiParam::getPage();
	check_readable($page, true, true);

	$action = WikiParam::getVar('action');
	switch ($action) {
		case 'delete': $retval = plugin_diff_delete($page);	break;
		default:       $retval = plugin_diff_view($page);	break;
	}
	return $retval;
}

function plugin_diff_view($page)
{
	global $script;
	global $_msg_notfound, $_msg_goto, $_msg_deleted, $_msg_addline, $_msg_delline, $_title_diff;
	global $_title_diff_delete;
	
	$r_page = rawurlencode($page);
	$s_page = htmlspecialchars($page);
	$editAuth = WikiConfig::isUserWithEditAuth();		// 編集権限があるかどうか

	$menu = array(
		'<li>' . $_msg_addline . '</li>',
		'<li>' . $_msg_delline . '</li>'
	);
	
	$is_page = is_page($page);
	if ($is_page) {
		$menu[] = ' <li>' . str_replace('$1', '<a href="' . $script . WikiParam::convQuery("?$r_page") . '">' . $s_page . '</a>', $_msg_goto) . '</li>';
	} else {	// ページがない場合
		$menu[] = ' <li>' . str_replace('$1', $s_page, $_msg_deleted) . '</li>';
	}

	$diffData = WikiPage::getPageDiff($page, true);
	if (!empty($diffData)){
//		if (! PKWK_READONLY) {
//		if ($editAuth){	// 編集権限がある場合のみ「削除」のリンクを表示
			$menu[] = '<li><a href="' . $script . WikiParam::convQuery("?cmd=diff&amp;action=delete&amp;page=$r_page") . '">' . str_replace('$1', $s_page, $_title_diff_delete) . '</a></li>';
//		}
		$msg = '<pre class="wiki_pre">' . diff_style_to_css(htmlspecialchars($diffData)) . '</pre>' . "\n";
	} else if ($is_page) {
		$diffData = trim(htmlspecialchars(get_source($page, true)));
		$msg = '<pre class="wiki_pre"><span class="diff_added">' . $diffData . '</span></pre>' . "\n";
	} else {
		return array('msg'=>$_title_diff, 'body'=>$_msg_notfound);
	}

	$menu = join("\n", $menu);
	$body = '<ul>' . $menu . '</ul>';

	return array('msg' => $_title_diff, 'body' => $body . $msg);
}

function plugin_diff_delete($page)
{
	global $script;
	global $_title_diff_delete, $_msg_diff_deleted;
	global $_msg_diff_adminpass, $_btn_delete, $_msg_invalidpass;
	global $dummy_password;
	global $gEnvManager;
	
	$body = '';
	$diffData = WikiPage::getPageDiff($page, true);
	if (! is_pagename($page))     $body = 'Invalid page name';

	if (empty($diffData)) $body = make_pagelink($page) . '\'s diff seems not found';
	if ($body) return array('msg'=>$_title_diff_delete, 'body'=>$body);

	$pass = WikiParam::getVar('pass');
	if ($pass != ''){
		if (pkwk_login($pass)){
			WikiPage::clearPageDiff($page);		// diffデータ削除
			return array(
				'msg'  => $_title_diff_delete,
				'body' => str_replace('$1', make_pagelink($page), $_msg_diff_deleted)
			);
		} else {
			$body .= '<p><strong>' . $_msg_invalidpass . '</strong></p>' . "\n";
		}
	}

	$s_page = htmlspecialchars($page);
	$postScript = $script . WikiParam::convQuery("?");
	
	// テンプレートタイプに合わせて出力を変更
	$templateType = $gEnvManager->getCurrentTemplateType();
	if ($templateType == M3_TEMPLATE_BOOTSTRAP_30){		// Bootstrap型テンプレートの場合
//		$body .= '<p>' . $_msg_diff_adminpass . '</p>' . M3_NL;
		$body .= '<form action="' . $postScript . '" method="post" class="form form-inline" role="form">' . M3_NL;
		$body .= '<input type="hidden"   name="wcmd"    value="diff" />' . M3_NL;
		$body .= '<input type="hidden"   name="page"   value="' . $s_page . '" />' . M3_NL;
		$body .= '<input type="hidden"   name="action" value="delete" />' . M3_NL;
		$body .= '<input type="hidden"   name="pass" />' . M3_NL;
		$body .= '<input type="hidden" name="password" value="' . $dummy_password . '" />' . M3_NL;
//		$body .= '<div class="form-group"><input type="password" class="form-control" name="password" size="12" /></div>' . M3_NL;
		$body .= '<input type="submit"   name="ok"     class="button btn" value="' . $_btn_delete . '" onclick="this.form.pass.value = hex_md5(this.form.password.value);" />' . M3_NL;
		$body .= '</form>' . M3_NL;
	} else {
//		$body .= '<p>' . $_msg_diff_adminpass . '</p>' . M3_NL;
		$body .= '<form action="' . $postScript . '" method="post" class="form">' . M3_NL;
		$body .= '<div>' . M3_NL;
		$body .= '<input type="hidden"   name="wcmd"    value="diff" />' . M3_NL;
		$body .= '<input type="hidden"   name="page"   value="' . $s_page . '" />' . M3_NL;
		$body .= '<input type="hidden"   name="action" value="delete" />' . M3_NL;
		$body .= '<input type="hidden"   name="pass" />' . M3_NL;
		$body .= '<input type="hidden" name="password" value="' . $dummy_password . '" />' . M3_NL;
//		$body .= '<input type="password" name="password" size="12" />' . M3_NL;
		$body .= '<input type="submit"   name="ok"     class="button" value="' . $_btn_delete . '" onclick="this.form.pass.value = hex_md5(this.form.password.value);" />' . M3_NL;
		$body .= '</div>' . M3_NL;
		$body .= '</form>' . M3_NL;
	}
	return array('msg' => $_title_diff_delete, 'body' => $body);
}
?>
