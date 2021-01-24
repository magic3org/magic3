<?php
/**
 * templateプラグイン
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2021 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */

define('MAX_LEN', 60);

function plugin_template_action()
{
	global $script;
	global $_title_edit;
	global $_msg_template_start, $_msg_template_end, $_msg_template_page, $_msg_template_refer;
	global $_btn_template_create, $_title_template;
	global $_err_template_already, $_err_template_invalid, $_msg_template_force;
	global $gEnvManager;

	// ### パスワード認証フォーム表示 ###
	// 認証されている場合はスルーして関数以降を実行
	$retStatus = password_form();
	if (!empty($retStatus)) return $retStatus;
	
	// テンプレートタイプに合わせて出力を変更
	$templateType = $gEnvManager->getCurrentTemplateType();
	
	$refer = WikiParam::getRefer();
	if ($refer == '' || !WikiPage::isPage($refer)) return FALSE;
	$lines = get_source($refer);

	// Remove '#freeze'
	//if (! empty($lines) && strtolower(rtrim($lines[0])) == '#freeze')		// removed for magic3
	//	array_shift($lines);
	$begin = is_numeric(WikiParam::getVar('begin')) ? WikiParam::getVar('begin') : 0;
	$end   = is_numeric(WikiParam::getVar('end')) ? WikiParam::getVar('end') : count($lines) - 1;
	if ($begin > $end) {
		$temp  = $begin;
		$begin = $end;
		$end   = $temp;
	}
	$page    = WikiParam::getPage();
	$is_page = WikiPage::isPage($page);

	// edit
	if ($is_pagename = is_pagename($page) && (!$is_page || WikiParam::getVar('force') != '')){
		$postdata	= join('', array_splice($lines, $begin, $end - $begin + 1));
		$msg		= $_title_edit;
		$body		= edit_form($page, $postdata);
		WikiParam::setRefer($page);
		return array('msg' => $msg, 'body' => $body);
	}
	$begin_select = $end_select = '';
	for ($i = 0; $i < count($lines); $i++) {
		$line = htmlspecialchars(mb_strimwidth($lines[$i], 0, MAX_LEN, '...'));

		$tag = ($i == $begin) ? ' selected="selected"' : '';
		$begin_select .= "<option value=\"$i\"$tag>$line</option>\n";

		$tag = ($i == $end) ? ' selected="selected"' : '';
		$end_select .= "<option value=\"$i\"$tag>$line</option>\n";
	}

	$_page = htmlspecialchars($page);
	$msg = $body = $tag = '';
	if ($is_page) {
		$msg = $_err_template_already;
		
		// テンプレートタイプに合わせて出力を変更
		if ($templateType == M3_TEMPLATE_BOOTSTRAP_30){		// Bootstrap v3.0型テンプレートの場合
			$tag = '<div class="checkbox-inline"><input type="checkbox" name="force" value="1" />'.$_msg_template_force . '</div>';
		} else if ($templateType == M3_TEMPLATE_BOOTSTRAP_40){		// Bootstrap v4.0型テンプレートの場合
			$tag = '<div class="form-group form-check mt-1"><input type="checkbox" id="_p_template_force" name="force" class="form-check-input" value="1" /><label class="form-check-label" for="_p_template_force">'.$_msg_template_force . '</label></div>';
		} else {
			$tag = '<input type="checkbox" name="force" value="1" />'.$_msg_template_force;
		}
	} else if ($page != '' && ! $is_pagename) {
		$msg = str_replace('$1', $_page, $_err_template_invalid);
	}

	$s_refer = htmlspecialchars($refer);
	$s_page  = ($page == '') ? str_replace('$1', $s_refer, $_msg_template_page) : $_page;
	$postScript = $script . WikiParam::convQuery('?');
	
	// テンプレートタイプに合わせて出力を変更
	if ($templateType == M3_TEMPLATE_BOOTSTRAP_30){		// Bootstrap v3.0型テンプレートの場合
		$body .= '<form action="' . $postScript . '" method="post" class="form" role="form">' . M3_NL;
		$body .= '<input type="hidden" name="plugin" value="template" />' . M3_NL;
		$body .= '<input type="hidden" name="refer"  value="' . $s_refer . '" />' . M3_NL;
		$body .= '<div class="form-group"><label for="_p_template_begin">' . $_msg_template_start . '</label><select class="form-control" name="begin" id="_p_template_begin" size="10">' . $begin_select . '</select></div>' . M3_NL;
		$body .= '<div class="form-group"><label for="_p_template_end">' . $_msg_template_end . '</label><select class="form-control" name="end" id="_p_template_end" size="10">' . $end_select . '</select></div>' . M3_NL;
		$body .= '<div class="form-group"><label for="_p_template_refer">' . $_msg_template_refer . ' <input type="text" class="form-control" name="page" id="_p_template_refer" value="' . $s_page . '" size="15" /></label></div>' . M3_NL;
		$body .= '<input type="submit" name="submit" class="button btn" value="' . $_btn_template_create . '" /> ' . $tag . M3_NL;
		$body .= '</form>' . M3_NL;
	} else if ($templateType == M3_TEMPLATE_BOOTSTRAP_40){		// Bootstrap v4.0型テンプレートの場合
		$body .= '<form action="' . $postScript . '" method="post" class="form">' . M3_NL;
		$body .= '<input type="hidden" name="plugin" value="template" />' . M3_NL;
		$body .= '<input type="hidden" name="refer"  value="' . $s_refer . '" />' . M3_NL;
		$body .= '<div class="form-group"><label for="_p_template_begin">' . $_msg_template_start . '</label><select class="form-control" name="begin" id="_p_template_begin" size="10">' . $begin_select . '</select></div>' . M3_NL;
		$body .= '<div class="form-group"><label for="_p_template_end">' . $_msg_template_end . '</label><select class="form-control" name="end" id="_p_template_end" size="10">' . $end_select . '</select></div>' . M3_NL;
		$body .= '<div class="form-inline"><div class="form-group mr-2"><label for="_p_template_refer" class="mr-2">' . $_msg_template_refer . '</label><input type="text" class="form-control" name="page" id="_p_template_refer" value="' . $s_page . '" size="30" /></div>' . M3_NL;
		$body .= '<input type="submit" name="submit" class="button btn" value="' . $_btn_template_create . '" /></div>' . $tag . M3_NL;
		$body .= '</form>' . M3_NL;
	} else {
		$body .= '<form action="' . $postScript . '" method="post" class="form">' . M3_NL;
		$body .= '<div>' . M3_NL;
		$body .= '<input type="hidden" name="plugin" value="template" />' . M3_NL;
		$body .= '<input type="hidden" name="refer"  value="' . $s_refer . '" />' . M3_NL;
		$body .= $_msg_template_start . '<br /><select name="begin" size="10">' . $begin_select . '</select><br /><br />' . M3_NL;
		$body .= $_msg_template_end . '<br /><select name="end"   size="10">' . $end_select . '</select><br /><br />' . M3_NL;
		$body .= '<label for="_p_template_refer">' . $_msg_template_refer . '</label>' . M3_NL;
		$body .= '<input type="text" name="page" id="_p_template_refer" value="' . $s_page . '" />' . M3_NL;
		$body .= '<input type="submit" name="submit" class="button" value="' . $_btn_template_create . '" /> ' . $tag . M3_NL;
		$body .= '</div>' . M3_NL;
		$body .= '</form>' . M3_NL;
	}
	
	$msg = ($msg == '') ? $_title_template : $msg;
	return array('msg' => $msg, 'body' => $body);
}
?>
