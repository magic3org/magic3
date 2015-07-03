<?php
/**
 * editプラグイン
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
// PukiWiki - Yet another WikiWikiWeb clone.
// $Id: edit.inc.php 3474 2010-08-13 10:36:48Z fishbone $
// Copyright (C) 2001-2006 PukiWiki Developers Team
// License: GPL v2 or (at your option) any later version

define('PLUGIN_EDIT_FREEZE_REGEX', '/^(?:#freeze(?!\w)\s*)+/im');

function plugin_edit_action()
{
	global $_title_edit, $load_template_func;
	global $script, $_title_cannotedit, $_msg_unfreeze, $_title_invalid_pagename, $_msg_invalid_pagename;
	global $gEnvManager;

//	if (PKWK_READONLY) die_message('PKWK_READONLY prohibits editing');

	// ### パスワード認証フォーム表示 ###
	// 認証されている場合はスルーして関数以降を実行
	$retStatus = password_form();
	if (!empty($retStatus)) return $retStatus;
	
	$page = WikiParam::getPage();

	// 「:」で始まるシステム用ページは作成不可
	if (strncmp($page, ':', 1) == 0){
		$msg  = $_title_invalid_pagename;
		$body = sprintf($_msg_invalid_pagename, $page);
		return array('msg' => $msg, 'body' => $body);
	}
		
	if (!check_editable($page, true, true)){		// 編集不可のとき
		$body = $title = str_replace('$1', make_pagelink($page), $_title_cannotedit);
		if (is_freeze($page)){
			$body .= '(<a href="' . $script . WikiParam::convQuery('?cmd=unfreeze&amp;page=' . rawurlencode($page)) . '">' . $_msg_unfreeze . '</a>)';
		}
		return array('msg' => $title, 'body' => $body);
	}

	if (WikiParam::getVar('preview') != '' || ($load_template_func && WikiParam::getVar('template') != '')) {
		return plugin_edit_preview();
	} else if (WikiParam::getVar('write') != '') {
		return plugin_edit_write();
	} else if (WikiParam::getVar('cancel') != '') {
		return plugin_edit_cancel();
	}

	$postdata = get_source($page, true);
	if ($postdata == '') $postdata = auto_template($page);
	return array('msg'=>$_title_edit, 'body'=>edit_form($page, $postdata));
}

// Preview
function plugin_edit_preview()
{
	global $_title_preview, $_msg_preview, $_msg_preview_delete;

	$page = WikiParam::getPage();

	// Loading template
	$templatePage = WikiParam::getVar('template_page');
	if ($templatePage != '' && is_page($templatePage)) {
/*		$vars['msg'] = join('', get_source($vars['template_page']));
		// Cut fixed anchors
		$vars['msg'] = preg_replace('/^(\*{1,3}.*)\[#[A-Za-z][\w-]+\](.*)$/m', '$1$2', $vars['msg']);*/
		
		$msg = get_source($templatePage, true);

		// Cut fixed anchors
		$msg = preg_replace('/^(\*{1,3}.*)\[#[A-Za-z][\w-]+\](.*)$/m', '$1$2', $msg);
//		WikiParam::setMsg($msg);		// tmp
	} else {
		$msg = WikiParam::getMsg();
	}

	/*$vars['msg'] = preg_replace(PLUGIN_EDIT_FREEZE_REGEX, '', $vars['msg']);
	$postdata = $vars['msg'];*/
//	$msg = WikiParam::getMsg();			// tmp
	//$msg = preg_replace(PLUGIN_EDIT_FREEZE_REGEX, '', $msg);		// not use freeze expression removed by magic3
//	WikiParam::setMsg($msg);		// tmp
	$postdata = $msg;
	
	//if (isset($vars['add']) && $vars['add']) {
	if (WikiParam::getVar('add')) {
		//if (isset($vars['add_top']) && $vars['add_top']) {
		if (WikiParam::getVar('add_top')) {
			//$postdata  = $postdata . "\n\n" . @join('', get_source($page));
			$postdata  = $postdata . "\n\n" . get_source($page, true);
		} else {
			//$postdata  = @join('', get_source($page)) . "\n\n" . $postdata;
			$postdata  = get_source($page, true) . "\n\n" . $postdata;
		}
	}

	$body = $_msg_preview . '<br />' . "\n";
	if ($postdata == '')
		$body .= '<strong>' . $_msg_preview_delete . '</strong>';
	$body .= '<br />' . "\n";

	if ($postdata) {
		$postdata = make_str_rules($postdata);
		$postdata = explode("\n", $postdata);
		$postdata = drop_submit(convert_html($postdata));
		$body .= '<div id="preview">' . $postdata . '</div>' . "\n";
	}
	//$body .= edit_form($page, $vars['msg'], $vars['digest'], FALSE);
	$body .= edit_form($page, $msg, WikiParam::getVar('digest'), FALSE);

	return array('msg'=>$_title_preview, 'body'=>$body);
}

// Inline: Show edit (or unfreeze text) link
function plugin_edit_inline()
{
	static $usage = '&edit(pagename#anchor[[,noicon],nolabel])[{label}];';
	// modified for Magic3 by naoki on 2008/10/6
	//global $script, $vars, $fixed_heading_anchor_edit;
	global $script, $fixed_heading_anchor_edit;

	if (PKWK_READONLY) return ''; // Show nothing 

	// Arguments
	$args = func_get_args();

	// {label}. Strip anchor tags only
	$s_label = strip_htmltag(array_pop($args), FALSE);

	$page    = array_shift($args);
	if ($page == NULL) $page = '';
	$_noicon = $_nolabel = FALSE;
	foreach($args as $arg){
		switch(strtolower($arg)){
		case ''       :                   break;
		case 'nolabel': $_nolabel = TRUE; break;
		case 'noicon' : $_noicon  = TRUE; break;
		default       : return $usage;
		}
	}

	// Separate a page-name and a fixed anchor
	list($s_page, $id, $editable) = anchor_explode($page, TRUE);

	// Default: This one
	//if ($s_page == '') $s_page = isset($vars['page']) ? $vars['page'] : '';
	if ($s_page == '') $s_page = WikiParam::getPage();

	// $s_page fixed
	$isfreeze = is_freeze($s_page);
	$ispage   = is_page($s_page);

	// Paragraph edit enabled or not
	$short = htmlspecialchars('Edit');
	if ($fixed_heading_anchor_edit && $editable && $ispage && ! $isfreeze) {
		// Paragraph editing
		$id    = rawurlencode($id);
		$title = htmlspecialchars(sprintf('Edit %s', $page));
		$icon = '<img src="' . IMAGE_DIR . 'paraedit.png' .
			'" width="9" height="9" alt="' .
			$short . '" title="' . $title . '" /> ';
		$class = ' class="anchor_super"';
	} else {
		// Normal editing / unfreeze
		$id    = '';
		if ($isfreeze) {
			$title = 'Unfreeze %s';
			$icon  = 'unfreeze.png';
		} else {
			$title = 'Edit %s';
			$icon  = 'edit.png';
		}
		$title = htmlspecialchars(sprintf($title, $s_page));
		$icon = '<img src="' . IMAGE_DIR . $icon .
			'" width="20" height="20" alt="' .
			$short . '" title="' . $title . '" />';
		$class = '';
	}
	if ($_noicon) $icon = ''; // No more icon
	if ($_nolabel) {
		if (!$_noicon) {
			$s_label = '';     // No label with an icon
		} else {
			$s_label = $short; // Short label without an icon
		}
	} else {
		if ($s_label == '') $s_label = $title; // Rich label with an icon
	}

	// URL
	// modified for Magic3 by naoki on 2008/10/6
	if ($isfreeze) {
		//$url   = $script . '?cmd=unfreeze&amp;page=' . rawurlencode($s_page);
		$url   = $script . WikiParam::convQuery('?cmd=unfreeze&amp;page=' . rawurlencode($s_page));
	} else {
		$s_id = ($id == '') ? '' : '&amp;id=' . $id;
		//$url  = $script . '?cmd=edit&amp;page=' . rawurlencode($s_page) . $s_id;
		$url  = $script . WikiParam::convQuery('?cmd=edit&amp;page=' . rawurlencode($s_page) . $s_id);
	}
	$atag  = '<a' . $class . ' href="' . $url . '" title="' . $title . '">';
	static $atags = '</a>';

	if ($ispage) {
		// Normal edit link
		return $atag . $icon . $s_label . $atags;
	} else {
		// Dangling edit link
		return '<span class="noexists">' . $atag . $icon . $atags .
			$s_label . $atag . '?' . $atags . '</span>';
	}
}

// Write, add, or insert new comment
function plugin_edit_write()
{
	// modified for Magic3 by naoki on 2008/10/6
//	global $trackback;
	global $_title_collided, $_msg_collided_auto, $_msg_collided, $_title_deleted;
	global $notimeupdate, $_msg_invalidpass, $do_update_diff_table;
	global $gPageManager;

/*	$page   = isset($vars['page'])   ? $vars['page']   : '';
	$add    = isset($vars['add'])    ? $vars['add']    : '';
	$digest = isset($vars['digest']) ? $vars['digest'] : '';*/
	$page   = WikiParam::getPage();
	$add    = WikiParam::getVar('add');
	$digest = WikiParam::getVar('digest');		// POST値を取得
	
	/*$vars['msg'] = preg_replace(PLUGIN_EDIT_FREEZE_REGEX, '', $vars['msg']);
	$msg = $vars['msg']; // Reference*/
	//$msg = preg_replace(PLUGIN_EDIT_FREEZE_REGEX, '', WikiParam::getMsg());		// removed for magic3
	//WikiParam::setMsg($msg);
	$msg = WikiParam::getMsg();			// 編集データ
	
	$retvars = array();

	// Collision Detection
	//$oldpagesrc = join('', get_source($page));
	// 変更データの元データが保存されているデータと同じかどうかのチェック
	$oldpagesrc = get_source($page, true);
	$oldpagemd5 = md5($oldpagesrc);
	if ($digest != $oldpagemd5){		// 元データが異なるとき
		//$vars['digest'] = $oldpagemd5; // Reset
		WikiParam::setDigest($oldpagemd5); // Reset

		$original = WikiParam::getOriginal();		// 編集前の元データ
		list($postdata_input, $auto) = do_update_diff($oldpagesrc, $msg, $original);

		$retvars['msg' ] = $_title_collided;
		$retvars['body'] = ($auto ? $_msg_collided_auto : $_msg_collided) . "\n";
		$retvars['body'] .= $do_update_diff_table;
		$retvars['body'] .= edit_form($page, $postdata_input, $oldpagemd5, FALSE);
		return $retvars;
	}

	// Action?
	if ($add) {
		// Add
		//if (isset($vars['add_top']) && $vars['add_top']) {
		if (WikiParam::getVar('add_top')) {
			//$postdata  = $msg . "\n\n" . @join('', get_source($page));
			$postdata  = $msg . "\n\n" . get_source($page, true);
		} else {
			//$postdata  = @join('', get_source($page)) . "\n\n" . $msg;
			$postdata  = get_source($page, true) . "\n\n" . $msg;
		}
	} else {
		// Edit or Remove
		//$postdata = $msg; // Reference
		$postdata = $msg;
	}

	// NULL POSTING, OR removing existing page
	//if ($postdata == '') {
	// スペースや改行のみのデータのときは、空データとしてページを削除
	if (trim($postdata) == ''){
		//page_write($page, $postdata);
		page_write($page, '');
		$retvars['msg' ] = $_title_deleted;
		$retvars['body'] = str_replace('$1', htmlspecialchars($page), $_title_deleted);

//		if ($trackback) tb_delete($page);

		return $retvars;
	}

	// $notimeupdate: Checkbox 'Do not change timestamp'
	//$notimestamp = isset($vars['notimestamp']) && $vars['notimestamp'] != '';
	$notimestamp = WikiParam::getVar('notimestamp') != '';
	//if ($notimeupdate > 1 && $notimestamp && ! pkwk_login($vars['pass'])) {
	if ($notimeupdate > 1 && $notimestamp && ! pkwk_login(WikiParam::getVar('pass'))) {
		// Enable only administrator & password error
		$retvars['body']  = '<p><strong>' . $_msg_invalidpass . '</strong></p>' . "\n";
		$retvars['body'] .= edit_form($page, $msg, $digest, FALSE);
		return $retvars;
	}

	page_write($page, $postdata, $notimeupdate != 0 && $notimestamp);
//	pkwk_headers_sent();
	// modified for Magic3 by naoki on 2008/10/6
	//header('Location: ' . get_script_uri() . '?' . rawurlencode($page));
//	header('Location: ' . get_script_uri() . WikiParam::convQuery('?' . rawurlencode($page), false));
//	exit;
	$gPageManager->redirect(get_script_uri() . WikiParam::convQuery('?' . rawurlencode($page), false));
}

// Cancel (Back to the page / Escape edit page)
function plugin_edit_cancel()
{
	global $gPageManager;
	//global $vars;
//	pkwk_headers_sent();
	// modified for Magic3 by naoki on 2008/10/6
	//header('Location: ' . get_script_uri() . '?' . rawurlencode($vars['page']));
//	header('Location: ' . get_script_uri() . WikiParam::convQuery('?' . rawurlencode(WikiParam::getPage()), false));
//	exit;
	$gPageManager->redirect(get_script_uri() . WikiParam::convQuery('?' . rawurlencode(WikiParam::getPage()), false));
}
?>
