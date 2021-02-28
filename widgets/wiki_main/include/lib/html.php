<?php
/**
 * HTML作成ライブラリ
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
// Copyright (C)
//   2002-2006 PukiWiki Developers Team
//   2001-2002 Originally written by yu-ji
// License: GPL v2 or (at your option) any later version
//
// HTML-publishing related functions
// Bootstrap用
define('EDIT_COLS_BOOTSTRAP', 40); // Columns of textarea

// Show 'edit' form
//function edit_form($page, $postdata, $digest = FALSE, $b_template = TRUE)
function edit_form($page, $postdata, $digest = FALSE, $b_template = TRUE, $cmd='')
{
	global $script, $rows, $cols, $hr;
	global $_btn_preview, $_btn_repreview, $_btn_update, $_btn_cancel, $_msg_help;
	global $whatsnew, $_btn_template, $_btn_load;
	global $notimeupdate;
	global $_btn_addtop;
	global $gEnvManager;
	
	// テンプレートタイプに合わせて出力を変更
	$templateType = $gEnvManager->getCurrentTemplateType();
		
	// Newly generate $digest or not
	if ($digest === FALSE) $digest = md5(get_source($page, true));

	$refer = $template = '';

 	// Add plugin
	$addtag = $add_top = '';
	if ($cmd == 'add'){
		$addtag  = '<input type="hidden" name="add"    value="true" />';
		$add_top = (WikiParam::getVar('add_top') != '') ? ' checked="checked"' : '';
		
		if ($templateType == M3_TEMPLATE_BOOTSTRAP_30){		// Bootstrap型テンプレートの場合
			$add_top = '<div class="checkbox-inline"><input type="checkbox" name="add_top" id="_edit_form_add_top" value="true"' . $add_top . ' />' . "\n" .
					'<label for="_edit_form_add_top">' . $_btn_addtop . '</label></div>';
		} else {
			$add_top = '<input type="checkbox" name="add_top" id="_edit_form_add_top" value="true"' . $add_top . ' />' . "\n" .
					'<label for="_edit_form_add_top">' . $_btn_addtop . '</label>';
		}
	}

	if ($b_template){
		$pages  = array();
		foreach(get_existpages() as $_page) {
			if ($_page == $whatsnew || check_non_list($_page))
				continue;
			$s_page = htmlspecialchars($_page);
			$pages[$_page] = '   <option value="' . $s_page . '">' .
				$s_page . '</option>';
		}
		ksort($pages);
		$s_pages  = join("\n", $pages);
		
		if ($templateType == M3_TEMPLATE_BOOTSTRAP_30){		// Bootstrap v3.0型テンプレートの場合
			$template  = '<select class="form-control" name="template_page">' . M3_NL;
			$template .= '<option value="">-- ' . $_btn_template . ' --</option>' . M3_NL;
			$template .= $s_pages;
			$template .= '</select>' . M3_NL;
			$template .= '<input type="submit" name="template" class="button btn" value="' . $_btn_load . '" accesskey="r" />' . M3_NL;
			
			// 編集用ツールバー追加。システム運用権限がある場合のみ有効。
			if ($gEnvManager->isSystemManageUser()){
				$template .= '<div class="pull-right">';
				$template .= '<div class="btn-group btn-group-sm" role="group" aria-label="edit toolbar">';
				$template .= '<a id="selfile" href="javascript:void(0);" class="btn btn-warning" role="button" data-container="body" rel="m3help" title="ファイルを挿入" ><i class="glyphicon glyphicon-file"></i></a>';
		  		$template .= '<a id="selimage" href="javascript:void(0);" class="btn btn-warning" role="button" data-container="body" rel="m3help" title="画像を挿入" ><i class="glyphicon glyphicon-picture"></i></a>';
				$template .= '</div>';
				$template .= '</div>';
			}
		} else if ($templateType == M3_TEMPLATE_BOOTSTRAP_40){		// Bootstrap v4.0型テンプレートの場合
			$template  = '<div class="form-group"><div class="input-group">';
			$template .= '<select class="form-control" name="template_page" style="width: auto;">' . M3_NL;
			$template .= '<option value="">-- ' . $_btn_template . ' --</option>' . M3_NL;
			$template .= $s_pages;
			$template .= '</select>' . M3_NL;
			$template .= '<input type="submit" name="template" class="button btn form-control" value="' . $_btn_load . '" accesskey="r" />' . M3_NL;
			$template .= '</div></div>';
			
			// 編集用ツールバー追加。システム運用権限がある場合のみ有効。
			if ($gEnvManager->isSystemManageUser()){
				$template .= '<div class="btn-group btn-group-sm" role="group" aria-label="edit toolbar">';
				$template .= '<a id="selfile" href="javascript:void(0);" class="button btn" role="button" data-container="body" rel="m3help" title="ファイルを挿入" ><i class="glyphicon glyphicon-file"></i></a>';
		  		$template .= '<a id="selimage" href="javascript:void(0);" class="button btn" role="button" data-container="body" rel="m3help" title="画像を挿入" ><i class="glyphicon glyphicon-picture"></i></a>';
				$template .= '</div>';
			}
		} else {
			$template  = '<select name="template_page">' . M3_NL;
			$template .= '<option value="">-- ' . $_btn_template . ' --</option>' . M3_NL;
			$template .= $s_pages;
			$template .= '</select>' . M3_NL;
			$template .= '<input type="submit" name="template" class="button" value="' . $_btn_load . '" accesskey="r" />' . M3_NL;
			
			// 編集用ツールバー追加。システム運用権限がある場合のみ有効。
			if ($gEnvManager->isSystemManageUser()){
				$template .= '<div class="pull-right">';
				$template .= '<div class="btn-group" role="group" aria-label="edit toolbar">';
				$template .= '<a id="selfile" href="javascript:void(0);" class="btn btn-warning" role="button" data-container="body" rel="m3help" title="ファイルを挿入" ><i class="glyphicon glyphicon-file"></i></a>';
		  		$template .= '<a id="selimage" href="javascript:void(0);" class="btn btn-warning" role="button" data-container="body" rel="m3help" title="画像を挿入" ><i class="glyphicon glyphicon-picture"></i></a>';
				$template .= '</div>';
				$template .= '</div>';
			}
		}
  
  		// 画像選択ダイアログ(elFinder)追加。システム運用権限がある場合のみ有効。
		if ($gEnvManager->isSystemManageUser()) WikiScript::addScript(WikiScript::SCRIPT_TYPE_EDIT_TOOLBAR/*Wikiページ編集用ツールバー*/, array( 'button_image' => 'selimage', 'button_file' => 'selfile', 'textarea' => 'wiki_edit' ));
		
		$referValue = WikiParam::getVar('refer');
		if ($referValue != '') $refer = '[[' . strip_bracket($referValue) . ']]' . "\n\n";
	}

	$r_page      = rawurlencode($page);
	$s_page      = htmlspecialchars($page);
	$s_digest    = htmlspecialchars($digest);
	$s_postdata  = htmlspecialchars($refer . $postdata);
	/*$s_original  = isset($vars['original']) ? htmlspecialchars($vars['original']) : $s_postdata;
	$b_preview   = isset($vars['preview']); // TRUE when preview*/
	$s_original  = (WikiParam::getVar('original') != '') ? htmlspecialchars(WikiParam::getVar('original')) : $s_postdata;
	$b_preview   = (WikiParam::getVar('preview') != ''); // TRUE when preview
	$btn_preview = $b_preview ? $_btn_repreview : $_btn_preview;

	// Checkbox 'do not change timestamp'
	$add_notimestamp = '';
	if ($notimeupdate != 0) {
		global $_btn_notchangetimestamp;

		$checked_time = (WikiParam::getVar('notimestamp') != '') ? ' checked="checked"' : '';
		
		if ($templateType == M3_TEMPLATE_BOOTSTRAP_30){		// Bootstrap v3.0型テンプレートの場合
			// Only for administrator
			if ($notimeupdate == 2) {
				$add_notimestamp = '<input type="password" class="form-control" name="pass" size="12" />';
			}
			$add_notimestamp = '<div class="checkbox-inline"><input type="checkbox" name="notimestamp" id="_edit_form_notimestamp" value="true"' . $checked_time . ' />' .
								'<label for="_edit_form_notimestamp">' . $_btn_notchangetimestamp . '</label></div>' . $add_notimestamp . '&nbsp;';
		} else if ($templateType == M3_TEMPLATE_BOOTSTRAP_40){		// Bootstrap v4.0型テンプレートの場合
			// Only for administrator
			if ($notimeupdate == 2) {
				$add_notimestamp = '<input type="password" class="form-control" name="pass" size="12" style="width:auto; float:right;" />';
			}
			$add_notimestamp = '<div class="form-check form-check-inline"><input type="checkbox" name="notimestamp" id="_edit_form_notimestamp" class="form-check-input" value="true"' . $checked_time . ' />' .
								'<label for="_edit_form_notimestamp" class="form-check-label">' . $_btn_notchangetimestamp . '</label></div>' . $add_notimestamp . '&nbsp;';
		} else {
			// Only for administrator
			if ($notimeupdate == 2) {
				$add_notimestamp = '   ' .
					'<input type="password" name="pass" size="12" />' . "\n";
			}
			$add_notimestamp = '<input type="checkbox" name="notimestamp" ' .
				'id="_edit_form_notimestamp" value="true"' . $checked_time . ' />' . "\n" .
				'   ' . '<label for="_edit_form_notimestamp"><span class="small">' .
				$_btn_notchangetimestamp . '</span></label>' . "\n" .
				$add_notimestamp .
				'&nbsp;';
		}
	}

	$postScript = $script . WikiParam::convQuery("?");
	
	if ($templateType == M3_TEMPLATE_BOOTSTRAP_30){		// Bootstrap v3.0型テンプレートの場合
		$cols = EDIT_COLS_BOOTSTRAP;
		$body = <<<EOD
<div class="edit_form">
 <form method="post" class="form form-inline" role="form">
$template
  $addtag
  <input type="hidden" name="wcmd"    value="edit" />
  <input type="hidden" name="page"   value="$s_page" />
  <input type="hidden" name="digest" value="$s_digest" />
  <div><textarea id="wiki_edit" name="msg" class="wiki_edit form-control" rows="$rows" cols="$cols">$s_postdata</textarea></div>
   <input type="submit" name="preview" class="button btn" value="$btn_preview" accesskey="p" />
   <input type="submit" name="write"   class="button btn" value="$_btn_update" accesskey="s" />
   $add_top
   $add_notimestamp
  <textarea name="original" style="display:none">$s_original</textarea>
 </form>
 <form method="post" class="form form-inline" role="form">
  <input type="hidden" name="wcmd"    value="edit" />
  <input type="hidden" name="page"   value="$s_page" />
  <input type="submit" name="cancel" class="button btn" value="$_btn_cancel" accesskey="c" />
 </form>
</div>
EOD;
	} else if ($templateType == M3_TEMPLATE_BOOTSTRAP_40){		// Bootstrap v4.0型テンプレートの場合
		$body = <<<EOD
<div class="edit_form form-group clearfix">
 <form method="post" class="form">
$template
  $addtag
  <input type="hidden" name="wcmd"    value="edit" />
  <input type="hidden" name="page"   value="$s_page" />
  <input type="hidden" name="digest" value="$s_digest" />
  <textarea id="wiki_edit" name="msg" class="wiki_edit form-control mb-2" rows="$rows" cols="$cols">$s_postdata</textarea>
  <div class="float-left">
   <input type="submit" name="preview" class="button btn" value="$btn_preview" accesskey="p" />
   <input type="submit" name="write"   class="button btn btn-success" value="$_btn_update" accesskey="s" />
   $add_top
   $add_notimestamp
  </div>
  <textarea name="original" style="display:none">$s_original</textarea>
 </form>
 <form method="post" class="form">
  <input type="hidden" name="wcmd"    value="edit" />
  <input type="hidden" name="page"   value="$s_page" />
  <input type="submit" name="cancel" class="button btn float-right" value="$_btn_cancel" accesskey="c" />
 </form>
</div>
EOD;
	} else {
		$body = <<<EOD
<div class="edit_form">
 <form method="post" style="margin-bottom:0px;" class="form">
$template
  $addtag
  <input type="hidden" name="wcmd"    value="edit" />
  <input type="hidden" name="page"   value="$s_page" />
  <input type="hidden" name="digest" value="$s_digest" />
  <textarea id="wiki_edit" name="msg" class="wiki_edit" rows="$rows" cols="$cols">$s_postdata</textarea>
  <br />
  <div style="float:left;">
   <input type="submit" name="preview" class="button" value="$btn_preview" accesskey="p" />
   <input type="submit" name="write"   class="button" value="$_btn_update" accesskey="s" />
   $add_top
   $add_notimestamp
  </div>
  <textarea name="original" rows="1" cols="1" style="display:none">$s_original</textarea>
 </form>
 <form method="post" style="margin-top:0px;" class="form">
  <input type="hidden" name="wcmd"    value="edit" />
  <input type="hidden" name="page"   value="$s_page" />
  <input type="submit" name="cancel" class="button" value="$_btn_cancel" accesskey="c" />
 </form>
</div>
EOD;
	}

	//if (isset($vars['help'])) {
	if (WikiParam::getVar('help') != ''){
		$body .= $hr . catrule();
	} else {
		// modified for Magic3 by naoki on 2008/10/6
		/*$body .= '<ul><li><a href="' .
			$script . '?cmd=edit&amp;help=true&amp;page=' . $r_page .
			'">' . $_msg_help . '</a></li></ul>';*/
		$body .= '<ul><li><a href="' . $script . WikiParam::convQuery("?cmd=edit&amp;help=true&amp;page=$r_page") . '">' . $_msg_help . '</a></li></ul>';
	}

	return $body;
}

// Related pages
function make_related($page, $tag = '')
{
	global $script, $rule_related_str, $related_str;
	global $_ul_left_margin, $_ul_margin, $_list_pad_str;

	$links = links_get_related($page);

	if ($tag) {
		ksort($links);
	} else {
		arsort($links);
	}

	$_links = array();
	foreach ($links as $page=>$lastmod) {
		// システム用ページは除外する
		//if (check_non_list($page)) continue;
		if (in_array($page, WikiConfig::getNoLinkPages()) || check_non_list($page)) continue;

		$r_page   = rawurlencode($page);
		$s_page   = htmlspecialchars($page);
		$passage  = get_passage($lastmod);
		// modified for Magic3 by naoki on 2008/10/6
		/*$_links[] = $tag ?
			'<a href="' . $script . '?' . $r_page . '" title="' .
			$s_page . ' ' . $passage . '">' . $s_page . '</a>' :
			'<a href="' . $script . '?' . $r_page . '">' .
			$s_page . '</a>' . $passage;*/
		$_links[] = $tag ? '<a href="' . $script . WikiParam::convQuery("?$r_page") . '" title="' . $s_page . ' ' . $passage . '">' . $s_page . '</a>' :
					'<a href="' . $script . WikiParam::convQuery("?$r_page") . '">' . $s_page . '</a>' . $passage;
	}
	if (empty($_links)) return ''; // Nothing

	if ($tag == 'p') { // From the line-head
		$margin = $_ul_left_margin + $_ul_margin;
		$style  = sprintf($_list_pad_str, 1, $margin, $margin);
		$retval =  "\n" . '<ul' . $style . '>' . "\n" .
			'<li>' . join($rule_related_str, $_links) . '</li>' . "\n" .
			'</ul>' . "\n";
	} else if ($tag) {
		$retval = join($rule_related_str, $_links);
	} else {
		$retval = join($related_str, $_links);
	}

	return $retval;
}

// User-defined rules (convert without replacing source)
function make_line_rules($str)
{
	global $line_rules;
	static $pattern, $replace;

	if (! isset($pattern)) {
//		$pattern = array_map(create_function('$a', 'return \'/\' . $a . \'/\';'), array_keys($line_rules));
		$pattern = array_map(function($a){ return '/' . $a . '/'; }, array_keys($line_rules));
		$replace = array_values($line_rules);
		unset($line_rules);
	}

	return preg_replace($pattern, $replace, $str);
}

// Remove all HTML tags(or just anchor tags), and WikiName-speific decorations
function strip_htmltag($str, $all = TRUE)
{
	global $_symbol_noexists;
	static $noexists_pattern;

	if (! isset($noexists_pattern))
		$noexists_pattern = '#<span class="noexists">([^<]*)<a[^>]+>' .
			preg_quote($_symbol_noexists, '#') . '</a></span>#';

	// Strip Dagnling-Link decoration (Tags and "$_symbol_noexists")
	$str = preg_replace($noexists_pattern, '$1', $str);

	if ($all) {
		// All other HTML tags
		return preg_replace('#<[^>]+>#',        '', $str);
	} else {
		// All other anchor-tags only
		return preg_replace('#<a[^>]+>|</a>#i', '', $str);
	}
}

// Remove AutoLink marker with AutLink itself
function strip_autolink($str)
{
	return preg_replace('#<!--autolink--><a [^>]+>|</a><!--/autolink-->#', '', $str);
}

// Make a backlink. searching-link of the page name, by the page name, for the page name
function make_search($page)
{
	global $script;

	// タイトルにバックリンクを付加するかどうかを取得
	$titleRelated = WikiConfig::getUsePageTitleRelated();
	if ($titleRelated){
		$s_page = htmlspecialchars($page);
		$r_page = rawurlencode($page);
		return '<a href="' . $script . WikiParam::convQuery("?plugin=related&amp;page=$r_page") . '">' . $s_page . '</a> ';
	} else {			// バックリンクを付加しない場合
		$s_page = htmlspecialchars($page);
		return $s_page;
	}
}

// Make heading string (remove heading-related decorations from Wiki text)
function make_heading(& $str, $strip = TRUE)
{
	global $NotePattern;

	// Cut fixed-heading anchors
	$id = '';
	$matches = array();
	if (preg_match('/^(\*{0,3})(.*?)\[#([A-Za-z][\w-]+)\](.*?)$/m', $str, $matches)) {
		$str = $matches[2] . $matches[4];
		$id  = $matches[3];
	} else {
		$str = preg_replace('/^\*{0,3}/', '', $str);
	}

	// Cut footnotes and tags
//	if ($strip === TRUE) $str = strip_htmltag(make_link(preg_replace($NotePattern, '', $str)));
//	if ($strip === TRUE) $str = strip_htmltag(make_link(preg_replace_callback($NotePattern, create_function('$matches','return;'), $str)));		// PHP5.5用修正
	if ($strip === TRUE) $str = strip_htmltag(make_link(preg_replace_callback($NotePattern, function($matches){ return ''; }, $str)));

	return $id;
}

// Separate a page-name(or URL or null string) and an anchor
// (last one standing) without sharp
function anchor_explode($page, $strict_editable = FALSE)
{
	$pos = strrpos($page, '#');
	if ($pos === FALSE) return array($page, '', FALSE);

	// Ignore the last sharp letter
	if ($pos + 1 == strlen($page)) {
		$pos = strpos(substr($page, $pos + 1), '#');
		if ($pos === FALSE) return array($page, '', FALSE);
	}

	$s_page = substr($page, 0, $pos);
	$anchor = substr($page, $pos + 1);

	if($strict_editable === TRUE &&  preg_match('/^[a-z][a-f0-9]{7}$/', $anchor)) {
		return array ($s_page, $anchor, TRUE); // Seems fixed-anchor
	} else {
		return array ($s_page, $anchor, FALSE);
	}
}

// Check HTTP header()s were sent already, or
// there're blank lines or something out of php blocks
function pkwk_headers_sent()
{
	if (PKWK_OPTIMISE) return;

	$file = $line = '';
	if (version_compare(PHP_VERSION, '4.3.0', '>=')) {
		if (headers_sent($file, $line))
		    die('Headers already sent at ' .
		    	htmlspecialchars($file) .
			' line ' . $line . '.');
	} else {
		if (headers_sent())
			die('Headers already sent.');
	}
}

// Output common HTTP headers
function pkwk_common_headers()
{
	if (! PKWK_OPTIMISE) pkwk_headers_sent();

	if(defined('PKWK_ZLIB_LOADABLE_MODULE')) {
		$matches = array();
		if(ini_get('zlib.output_compression') &&
		    preg_match('/\b(gzip|deflate)\b/i', $_SERVER['HTTP_ACCEPT_ENCODING'], $matches)) {
		    	// Bug #29350 output_compression compresses everything _without header_ as loadable module
		    	// http://bugs.php.net/bug.php?id=29350
			header('Content-Encoding: ' . $matches[1]);
			header('Vary: Accept-Encoding');
		}
	}
}
/**
 * パスワード認証画面を作成
 *
 * 機能：パスワード認証が必要な場合は認証画面を作成、認証が通った場合やすでに認証されている場合は空のデータを返す
 *
 * @return array		認証画面の連想配列データ(title=タイトル、body=フォーム内容)。認証の必要がない場合は空を返す。
 */
function password_form()
{
	// ##### readコマンドでコンテンツを読み出す場合でも一旦editを通してこの関数が呼ばれる(read→edit) #####
	global $_msg_password, $_btn_submit, $_title_authorization_required, $_msg_authorization_required;		// パスワード認証用
	global $gEnvManager;
	global $gPageManager;
	
	// ###### パスワード認証処理 #####
	$pass = WikiParam::getVar('pass');
	$editAuth = WikiConfig::isUserWithEditAuth();		// 編集権限があるかどうか

	// 編集権限をチェック
	if (!$editAuth){			// 編集権限がない場合
		if ($pass != '' && pkwk_login($pass)){		// パスワードが送信されている場合はログイン処理
		} else {
			// パスワード入力画面を作成
			$msg = $_title_authorization_required;
			$body = "<p>$_msg_authorization_required</p>\n";

			// パスワード認証の場合は入力フィールドを表示
			if (WikiConfig::isPasswordAuth()){
				$templateType = $gEnvManager->getCurrentTemplateType();
				if ($templateType == M3_TEMPLATE_BOOTSTRAP_30){		// Bootstrap v3.0型テンプレートの場合
					$body .= '<form method="post" class="form form-inline" role="form">' . M3_NL;
					$body .= '<input type="hidden"   name="pass" />' . M3_NL;
					$body .= '<div class="form-group"><label>' . $_msg_password . ': <input type="password" class="form-control" name="password" size="12" /></label>' . M3_NL;
					$body .= '<input type="submit" class="button btn" value="' . $_btn_submit . '" onclick="this.form.pass.value = hex_md5(this.form.password.value); this.form.password.value = \'\';" /></div>' . M3_NL;
					$body .= '</form>' . M3_NL;
				} else if ($templateType == M3_TEMPLATE_BOOTSTRAP_40){		// Bootstrap v4.0型テンプレートの場合
					$body .= '<form method="post" class="form form-inline">' . M3_NL;
					$body .= '<input type="hidden"   name="pass" />' . M3_NL;
					$body .= '<div class="form-group mr-2"><label class="mr-2" for="_p_password">' . $_msg_password . ': </label><input type="password" id="_p_password" class="form-control" name="password" size="12" /></div>' . M3_NL;
					$body .= '<input type="submit" class="button btn" value="' . $_btn_submit . '" onclick="this.form.pass.value = hex_md5(this.form.password.value); this.form.password.value = \'\';" />' . M3_NL;
					$body .= '</form>' . M3_NL;
				} else {
					$body .= '<form method="post" class="form">' . M3_NL;
					$body .= '<input type="hidden"   name="pass" />' . M3_NL;
					$body .= '<label>' . $_msg_password . ': <input type="password" name="password" size="12" /></label>' . M3_NL;
					$body .= '<input type="submit" class="button" value="' . $_btn_submit . '" onclick="this.form.pass.value = hex_md5(this.form.password.value); this.form.password.value = \'\';" />' . M3_NL;
					$body .= '</form>' . M3_NL;
				}
			} else {		// パスワード認証でないとき
/*				// パスワード認証ではない場合、編集権限がないページへのアクセスは不正アクセスとする
				$widgetObj = $gEnvManager->getCurrentWidgetObj();
				if (!empty($widgetObj)){
					$page = WikiParam::getPage();
					$msgDetail = 'アクセスをブロックしました。URL=' . $gEnvManager->getCurrentRequestUri();
					$widgetObj->writeUserError(__METHOD__, 'Wikiページへの不正なアクセスを検出しました。ページ名=' . $page, 2200, $msgDetail);
				}*/
				$cmd = WikiParam::getCmd();
				$plugin = WikiParam::getPlugin();

				// コマンドかプラグインが指定されている場合は実行権限エラーを返す
				// 存在しないページを表示しようとした場合は、$cmdが「edit」で来る
				if ((!empty($cmd) && $cmd != 'edit') || !empty($plugin)){
					$msg = wiki_mainCommonDef::DEFAULT_FORBIDDEN_TITLE;
					$body = '<p>' . wiki_mainCommonDef::DEFAULT_FORBIDDEN_MSG . '</p>';
				
					// HTTPステータスコードを403に設定
					$gPageManager->setResponse(403/*禁止されている*/);
				} else {		// Wikiページの表示の場合
					// ##### アクセス権限がないページへのアクセスは「未検出」とする #####
					$msg = wiki_mainCommonDef::DEFAULT_NOT_FOUND_TITLE;
					$body = '<p>' . wiki_mainCommonDef::DEFAULT_NOT_FOUND_MSG . '</p>';
				
					// HTTPステータスコードを404に設定
					$gPageManager->setResponse(404/*存在しないページ*/);
				}
			}
			return array(
				'msg'	=> $msg,
				'body'	=> $body
			);
		}
	}
	// 認証を通った場合
	return array();
}
?>
