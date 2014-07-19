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
 * @copyright  Copyright 2006-2014 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: html.php 3474 2010-08-13 10:36:48Z fishbone $
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

// Show page-content
// modified for Magic3 by naoki on 2008/9/29
//function catbody($title, $page, $body)
/*function catbody($body)
{
	global $trackback, $trackback_javascript, $referer, $javascript;

	// Add JavaScript header when ...
	if ($trackback && $trackback_javascript) $javascript = 1; // Set something If you want
	if (! PKWK_ALLOW_JAVASCRIPT) unset($javascript);
}*/

// Show 'edit' form
//function edit_form($page, $postdata, $digest = FALSE, $b_template = TRUE)
function edit_form($page, $postdata, $digest = FALSE, $b_template = TRUE, $cmd='')
{
	//global $script, $vars, $rows, $cols, $hr, $function_freeze;
	global $script, $rows, $cols, $hr, $function_freeze;
	global $_btn_preview, $_btn_repreview, $_btn_update, $_btn_cancel, $_msg_help;
	global $whatsnew, $_btn_template, $_btn_load, $load_template_func;
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

	if($load_template_func && $b_template) {
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
		
		if ($templateType == M3_TEMPLATE_BOOTSTRAP_30){		// Bootstrap型テンプレートの場合
			$template = <<<EOD
  <select class="form-control" name="template_page">
   <option value="">-- $_btn_template --</option>
$s_pages
  </select>
  <input type="submit" name="template" class="button btn" value="$_btn_load" accesskey="r" />
EOD;
		} else {
			$template = <<<EOD
  <select name="template_page">
   <option value="">-- $_btn_template --</option>
$s_pages
  </select>
  <input type="submit" name="template" class="button" value="$_btn_load" accesskey="r" />
  <br />
EOD;
		}

		/*if (isset($vars['refer']) && $vars['refer'] != '')
			$refer = '[[' . strip_bracket($vars['refer']) . ']]' . "\n\n";*/
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
		
		if ($templateType == M3_TEMPLATE_BOOTSTRAP_30){		// Bootstrap型テンプレートの場合
			// Only for administrator
			if ($notimeupdate == 2) {
				$add_notimestamp = '<input type="password" class="form-control" name="pass" size="12" />';
			}
			$add_notimestamp = '<div class="checkbox-inline"><input type="checkbox" name="notimestamp" id="_edit_form_notimestamp" value="true"' . $checked_time . ' />' .
								'<label for="_edit_form_notimestamp">' . $_btn_notchangetimestamp . '</label></div>' . $add_notimestamp . '&nbsp;';
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
	
	if ($templateType == M3_TEMPLATE_BOOTSTRAP_30){		// Bootstrap型テンプレートの場合
		$cols = EDIT_COLS_BOOTSTRAP;
		$body = <<<EOD
<div class="edit_form">
 <form action="$postScript" method="post" class="form form-inline" role="form">
$template
  $addtag
  <input type="hidden" name="wcmd"    value="edit" />
  <input type="hidden" name="page"   value="$s_page" />
  <input type="hidden" name="digest" value="$s_digest" />
  <div><textarea class="wiki_edit form-control" name="msg" rows="$rows" cols="$cols">$s_postdata</textarea></div>
   <input type="submit" name="preview" class="button btn" value="$btn_preview" accesskey="p" />
   <input type="submit" name="write"   class="button btn" value="$_btn_update" accesskey="s" />
   $add_top
   $add_notimestamp
  <textarea name="original" style="display:none">$s_original</textarea>
 </form>
 <form action="$postScript" method="post" class="form form-inline" role="form">
  <input type="hidden" name="wcmd"    value="edit" />
  <input type="hidden" name="page"   value="$s_page" />
  <input type="submit" name="cancel" class="button btn" value="$_btn_cancel" accesskey="c" />
 </form>
</div>
EOD;
	} else {
		$body = <<<EOD
<div class="edit_form">
 <form action="$postScript" method="post" style="margin-bottom:0px;" class="form">
$template
  $addtag
  <input type="hidden" name="wcmd"    value="edit" />
  <input type="hidden" name="page"   value="$s_page" />
  <input type="hidden" name="digest" value="$s_digest" />
  <textarea name="msg" class="wiki_edit" rows="$rows" cols="$cols">$s_postdata</textarea>
  <br />
  <div style="float:left;">
   <input type="submit" name="preview" class="button" value="$btn_preview" accesskey="p" />
   <input type="submit" name="write"   class="button" value="$_btn_update" accesskey="s" />
   $add_top
   $add_notimestamp
  </div>
  <textarea name="original" rows="1" cols="1" style="display:none">$s_original</textarea>
 </form>
 <form action="$postScript" method="post" style="margin-top:0px;" class="form">
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
		$body .= '<ul><li><a href="' .
			$script . WikiParam::convQuery("?cmd=edit&amp;help=true&amp;page=$r_page") .
			'">' . $_msg_help . '</a></li></ul>';
	}

	return $body;
}

// Related pages
function make_related($page, $tag = '')
{
	//global $script, $vars, $rule_related_str, $related_str;
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
		if (check_non_list($page)) continue;

		$r_page   = rawurlencode($page);
		$s_page   = htmlspecialchars($page);
		$passage  = get_passage($lastmod);
		// modified for Magic3 by naoki on 2008/10/6
		/*$_links[] = $tag ?
			'<a href="' . $script . '?' . $r_page . '" title="' .
			$s_page . ' ' . $passage . '">' . $s_page . '</a>' :
			'<a href="' . $script . '?' . $r_page . '">' .
			$s_page . '</a>' . $passage;*/
		$_links[] = $tag ?
			'<a href="' . $script . WikiParam::convQuery("?$r_page") . '" title="' .
			$s_page . ' ' . $passage . '">' . $s_page . '</a>' :
			'<a href="' . $script . WikiParam::convQuery("?$r_page") . '">' .
			$s_page . '</a>' . $passage;
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
		$pattern = array_map(create_function('$a',
			'return \'/\' . $a . \'/\';'), array_keys($line_rules));
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

	$s_page = htmlspecialchars($page);
	$r_page = rawurlencode($page);

	// modified for Magic3 by naoki on 2008/10/6
/*	return '<a href="' . $script . '?plugin=related&amp;page=' . $r_page .
		'">' . $s_page . '</a> ';*/
	return '<a href="' . $script . WikiParam::convQuery("?plugin=related&amp;page=$r_page") . '">' . $s_page . '</a> ';
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
	if ($strip === TRUE) $str = strip_htmltag(make_link(preg_replace_callback($NotePattern, create_function('$matches','return;'), $str)));		// PHP5.5用修正
	
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

// DTD definitions
/*define('PKWK_DTD_XHTML_1_1',              17); // Strict only
define('PKWK_DTD_XHTML_1_0',              16); // Strict
define('PKWK_DTD_XHTML_1_0_STRICT',       16);
define('PKWK_DTD_XHTML_1_0_TRANSITIONAL', 15);
define('PKWK_DTD_XHTML_1_0_FRAMESET',     14);
define('PKWK_DTD_HTML_4_01',               3); // Strict
define('PKWK_DTD_HTML_4_01_STRICT',        3);
define('PKWK_DTD_HTML_4_01_TRANSITIONAL',  2);
define('PKWK_DTD_HTML_4_01_FRAMESET',      1);
define('PKWK_DTD_TYPE_XHTML',  1);
define('PKWK_DTD_TYPE_HTML',   0);*/

// Output HTML DTD, <html> start tag. Return content-type.
/*function pkwk_output_dtd($pkwk_dtd = PKWK_DTD_XHTML_1_1, $charset = CONTENT_CHARSET)
{
	static $called;
	if (isset($called)) die('pkwk_output_dtd() already called. Why?');
	$called = TRUE;

	$type = PKWK_DTD_TYPE_XHTML;
	$option = '';
	switch($pkwk_dtd){
	case PKWK_DTD_XHTML_1_1             :
		$version = '1.1' ;
		$dtd     = 'http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd';
		break;
	case PKWK_DTD_XHTML_1_0_STRICT      :
		$version = '1.0' ;
		$option  = 'Strict';
		$dtd     = 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd';
		break;
	case PKWK_DTD_XHTML_1_0_TRANSITIONAL:
		$version = '1.0' ;
		$option  = 'Transitional';
		$dtd     = 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd';
		break;

	case PKWK_DTD_HTML_4_01_STRICT      :
		$type    = PKWK_DTD_TYPE_HTML;
		$version = '4.01';
		$dtd     = 'http://www.w3.org/TR/html4/strict.dtd';
		break;
	case PKWK_DTD_HTML_4_01_TRANSITIONAL:
		$type    = PKWK_DTD_TYPE_HTML;
		$version = '4.01';
		$option  = 'Transitional';
		$dtd     = 'http://www.w3.org/TR/html4/loose.dtd';
		break;

	default: die('DTD not specified or invalid DTD');
		break;
	}

	$charset = htmlspecialchars($charset);

	// Output XML or not
	if ($type == PKWK_DTD_TYPE_XHTML) echo '<?xml version="1.0" encoding="' . $charset . '" ?>' . "\n";

	// Output doctype
	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD ' .
		($type == PKWK_DTD_TYPE_XHTML ? 'XHTML' : 'HTML') . ' ' .
		$version .
		($option != '' ? ' ' . $option : '') .
		'//EN" "' .
		$dtd .
		'">' . "\n";

	// Output <html> start tag
	echo '<html';
	if ($type == PKWK_DTD_TYPE_XHTML) {
		echo ' xmlns="http://www.w3.org/1999/xhtml"'; // dir="ltr"
		echo ' xml:lang="' . LANG . '"';
		if ($version == '1.0') echo ' lang="' . LANG . '"'; // Only XHTML 1.0
	} else {
		echo ' lang="' . LANG . '"'; // HTML
	}
	echo '>' . "\n"; // <html>

	// Return content-type (with MIME type)
	if ($type == PKWK_DTD_TYPE_XHTML) {
		// NOTE: XHTML 1.1 browser will ignore http-equiv
		return '<meta http-equiv="content-type" content="application/xhtml+xml; charset=' . $charset . '" />' . "\n";
	} else {
		return '<meta http-equiv="content-type" content="text/html; charset=' . $charset . '" />' . "\n";
	}
}*/
?>
