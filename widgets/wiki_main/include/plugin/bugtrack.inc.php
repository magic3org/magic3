<?php
/**
 * bugtrackプラグイン
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
// Copyright:
// 2002-2005 PukiWiki Developers Team
// 2002 Y.MASUI GPL2  http://masui.net/pukiwiki/ masui@masui.net

// Numbering format
define('PLUGIN_BUGTRACK_NUMBER_FORMAT', '%d'); // Like 'page/1'
//define('PLUGIN_BUGTRACK_NUMBER_FORMAT', '%03d'); // Like 'page/001'

function plugin_bugtrack_init()
{
	global $_plugin_bugtrack;
	static $init;

	if (isset($init)) return; // Already init
	if (isset($_plugin_bugtrack)) die('Global $_plugin_bugtrack had been init. Why?');
	$init = TRUE;

	$_plugin_bugtrack = array(
		'priority_list'  => array('緊急', '重要', '普通', '低'),
		'state_list'     => array('提案', '着手', 'CVS待ち', '完了', '保留', '却下'),
		'state_sort'     => array('着手', 'CVS待ち', '保留', '完了', '提案', '却下'),
		'state_bgcolor'  => array('#ccccff', '#ffcc99', '#ccddcc', '#ccffcc', '#ffccff', '#cccccc', '#ff3333'),
		'header_bgcolor' => '#ffffcc',
		'base'     => 'ページ',
		'summary'  => 'サマリ',
		'nosummary'=> 'ここにサマリを記入して下さい',
		'priority' => '優先順位',
		'state'    => '状態',
		'name'     => '投稿者',
		'noname'   => '名無しさん',
		'date'     => '投稿日',
		'body'     => 'メッセージ',
		'category' => 'カテゴリー',
		'pagename' => 'ページ名',
		'pagename_comment' => '空欄のままだと自動的にページ名が振られます。',
		'version_comment'  => '空欄でも構いません',
		'version'  => 'バージョン',
		'submit'   => '追加'
		);
}

// #bugtrack: Show bugtrack form
function plugin_bugtrack_convert()
{
	if (PKWK_READONLY) return ''; // Show nothing

	$base = WikiParam::getPage();
	$category = array();
	if (func_num_args()) {
		$category = func_get_args();
		$_base    = get_fullname(strip_bracket(array_shift($category)), $base);
		if (is_pagename($_base)) $base = $_base;
	}

	return plugin_bugtrack_print_form($base, $category);
}

function plugin_bugtrack_print_form($base, $category)
{
	global $_plugin_bugtrack;
	global $gEnvManager;
	static $id = 0;
	
	++$id;
	$select_priority = "\n";
	$count = count($_plugin_bugtrack['priority_list']);
	$selected = '';
	for ($i = 0; $i < $count; ++$i) {
		if ($i == ($count - 1)) $selected = ' selected="selected"'; // The last one
		$priority_list = htmlspecialchars($_plugin_bugtrack['priority_list'][$i]);
		$select_priority .= '    <option value="' . $priority_list . '"' .
			$selected . '>' . $priority_list . '</option>' . "\n";
	}

	$select_state = "\n";
	for ($i = 0; $i < count($_plugin_bugtrack['state_list']); ++$i) {
		$state_list = htmlspecialchars($_plugin_bugtrack['state_list'][$i]);
		$select_state .= '    <option value="' . $state_list . '">' .
			$state_list . '</option>' . "\n";
	}

	$script     = get_script_uri() . WikiParam::convQuery('?');
	$s_base     = htmlspecialchars($base);
	$s_name     = htmlspecialchars($_plugin_bugtrack['name']);
	$s_category = htmlspecialchars($_plugin_bugtrack['category']);
	$s_priority = htmlspecialchars($_plugin_bugtrack['priority']);
	$s_state    = htmlspecialchars($_plugin_bugtrack['state']);
	$s_pname    = htmlspecialchars($_plugin_bugtrack['pagename']);
	$s_pnamec   = htmlspecialchars($_plugin_bugtrack['pagename_comment']);
	$s_version  = htmlspecialchars($_plugin_bugtrack['version']);
	$s_versionc = htmlspecialchars($_plugin_bugtrack['version_comment']);
	$s_summary  = htmlspecialchars($_plugin_bugtrack['summary']);
	$s_body     = htmlspecialchars($_plugin_bugtrack['body']);
	$s_submit   = htmlspecialchars($_plugin_bugtrack['submit']);
	
	// テンプレートタイプに合わせて出力を変更
	$templateType = $gEnvManager->getCurrentTemplateType();
	if ($templateType == M3_TEMPLATE_BOOTSTRAP_30){		// Bootstrap型テンプレートの場合
		if (empty($category)) {
			$encoded_category = '<input type="text" class="form-control" name="category" id="_p_bugtrack_category_' . $id . '" />';
		} else {
			$encoded_category = '<select class="form-control" name="category" id="_p_bugtrack_category_' . $id . '">';
			foreach ($category as $_category) {
				$s_category = htmlspecialchars($_category);
				$encoded_category .= '<option value="' . $s_category . '">' .
					$s_category . '</option>' . "\n";
			}
			$encoded_category .= '</select>';
		}
	
		$body = <<<EOD
<form action="$script" method="post" class="form form-horizontal" role="form">
<input type="hidden" name="plugin" value="bugtrack" />
<input type="hidden" name="mode"   value="submit" />
<input type="hidden" name="base"   value="$s_base" />
<div class="form-group">
  <label class="col-sm-2 control-label" for="_p_bugtrack_name_$id">$s_name</label>
  <div class="col-sm-4"><input type="text" id="_p_bugtrack_name_$id" class="form-control" name="name" size="20" /></div>
</div>
<div class="form-group">
  <label class="col-sm-2 control-label" for="_p_bugtrack_category_$id">$s_category</label>
  <div class="col-sm-4">$encoded_category</div>
</div>
<div class="form-group">
  <label class="col-sm-2 control-label" for="_p_bugtrack_priority_$id">$s_priority</label>
  <div class="col-sm-2"><select class="form-control" id="_p_bugtrack_priority_$id" name="priority">$select_priority   </select></div>
</div>
<div class="form-group">
  <label class="col-sm-2 control-label" for="_p_bugtrack_state_$id">$s_state</label>
  <div class="col-sm-2"><select class="form-control" id="_p_bugtrack_state_$id" name="state">$select_state   </select></div>
</div>
<div class="form-group">
  <label class="col-sm-2 control-label" for="_p_bugtrack_pagename_$id">$s_pname</label>
  <div class="col-sm-4"><input id="_p_bugtrack_pagename_$id" class="form-control" name="pagename" size="20" type="text" /></div>
  <div class="col-sm-6"><span class="help-block">$s_pnamec</span></div>
</div>
<div class="form-group">
  <label class="col-sm-2 control-label" for="_p_bugtrack_version_$id">$s_version</label>
  <div class="col-sm-2"><input id="_p_bugtrack_version_$id" class="form-control" name="version" size="10" type="text" /></div>
  <div class="col-sm-offset-2 col-sm-6"><span class="help-block">$s_versionc</span></div>
</div>
<div class="form-group">
  <label class="col-sm-2 control-label" for="_p_bugtrack_summary_$id">$s_summary</label>
  <div class="col-sm-10"><input id="_p_bugtrack_summary_$id" class="form-control" name="summary" size="60" type="text" /></div>
</div>
<div class="form-group">
  <label class="col-sm-2 control-label" for="_p_bugtrack_body_$id">$s_body</label>
  <div class="col-sm-10"><textarea id="_p_bugtrack_body_$id" class="form-control wiki_edit" name="body" rows="6"></textarea></div>
</div>
<div class="form-group">
  <div class="col-sm-offset-2 col-sm-10"><input type="submit" class="button btn" value="$s_submit" /></div>
</div>
</form>
EOD;
	} else {
		if (empty($category)) {
			$encoded_category = '<input name="category" id="_p_bugtrack_category_' . $id .
				'" type="text" />';
		} else {
			$encoded_category = '<select name="category" id="_p_bugtrack_category_' . $id . '">';
			foreach ($category as $_category) {
				$s_category = htmlspecialchars($_category);
				$encoded_category .= '<option value="' . $s_category . '">' .
					$s_category . '</option>' . "\n";
			}
			$encoded_category .= '</select>';
		}
		
		$body = <<<EOD
<form action="$script" method="post" class="form">
 <table border="0">
  <tr>
   <th><label for="_p_bugtrack_name_$id">$s_name</label></th>
   <td><input  id="_p_bugtrack_name_$id" name="name" size="20" type="text" /></td>
  </tr>
  <tr>
   <th><label for="_p_bugtrack_category_$id">$s_category</label></th>
   <td>$encoded_category</td>
  </tr>
  <tr>
   <th><label for="_p_bugtrack_priority_$id">$s_priority</label></th>
   <td><select id="_p_bugtrack_priority_$id" name="priority">$select_priority   </select></td>
  </tr>
  <tr>
   <th><label for="_p_bugtrack_state_$id">$s_state</label></th>
   <td><select id="_p_bugtrack_state_$id" name="state">$select_state   </select></td>
  </tr>
  <tr>
   <th><label for="_p_bugtrack_pagename_$id">$s_pname</label></th>
   <td><input  id="_p_bugtrack_pagename_$id" name="pagename" size="20" type="text" />
    <small>$s_pnamec</small></td>
  </tr>
  <tr>
   <th><label for="_p_bugtrack_version_$id">$s_version</label></th>
   <td><input  id="_p_bugtrack_version_$id" name="version" size="10" type="text" />
    <small>$s_versionc</small></td>
  </tr>
  <tr>
   <th><label for="_p_bugtrack_summary_$id">$s_summary</label></th>
   <td><input  id="_p_bugtrack_summary_$id" name="summary" size="60" type="text" /></td>
  </tr>
  <tr>
   <th><label   for="_p_bugtrack_body_$id">$s_body</label></th>
   <td><textarea id="_p_bugtrack_body_$id" name="body" class="wiki_edit" cols="60" rows="6"></textarea></td>
  </tr>
  <tr>
   <td colspan="2" align="right">
    <input type="submit" class="button" class="button" value="$s_submit" />
    <input type="hidden" name="plugin" value="bugtrack" />
    <input type="hidden" name="mode"   value="submit" />
    <input type="hidden" name="base"   value="$s_base" />
   </td>
  </tr>
 </table>
</form>
EOD;
	}
	return $body;
}

// Add new issue
function plugin_bugtrack_action()
{
	//global $post;
	global $gPageManager;
	
	if (PKWK_READONLY) die_message('PKWK_READONLY prohibits editing');
	//if ($post['mode'] != 'submit') return FALSE;
	if (WikiParam::getPostVar('mode') != 'submit') return FALSE;

/*	$page = plugin_bugtrack_write($post['base'], $post['pagename'], $post['summary'],
		$post['name'], $post['priority'], $post['state'], $post['category'],
		$post['version'], $post['body']);*/
		
	$page = plugin_bugtrack_write(WikiParam::getPostVar('base'), WikiParam::getPostVar('pagename'), WikiParam::getPostVar('summary'),
		WikiParam::getPostVar('name'), WikiParam::getPostVar('priority'), WikiParam::getPostVar('state'), WikiParam::getPostVar('category'),
		WikiParam::getPostVar('version'), WikiParam::getPostVar('body'));

	// ページ作成処理中断
	$gPageManager->abortPage();
	
	pkwk_headers_sent();
	//header('Location: ' . get_script_uri() . '?' . rawurlencode($page));
	header('Location: ' . get_script_uri() . WikiParam::convQuery('?' . rawurlencode($page), false));
	//exit;
	
	// システム強制終了
	$gPageManager->exitSystem();
}

function plugin_bugtrack_write($base, $pagename, $summary, $name, $priority, $state, $category, $version, $body)
{
	//global $post;

	$base     = strip_bracket($base);
	$pagename = strip_bracket($pagename);

	$postdata = plugin_bugtrack_template($base, $summary, $name, $priority,
		$state, $category, $version, $body);

	$id = $jump = 1;
	$page = $base . '/' . sprintf(PLUGIN_BUGTRACK_NUMBER_FORMAT, $id);
	while (WikiPage::isPage($page)) {
		$id   = $jump;
		$jump += 50;
		$page = $base . '/' . sprintf(PLUGIN_BUGTRACK_NUMBER_FORMAT, $jump);
	}
	$page = $base . '/' . sprintf(PLUGIN_BUGTRACK_NUMBER_FORMAT, $id);
	while (WikiPage::isPage($page))
		$page = $base . '/' . sprintf(PLUGIN_BUGTRACK_NUMBER_FORMAT, ++$id);

	if ($pagename == '') {
		page_write($page, $postdata);
	} else {
		$pagename = get_fullname($pagename, $base);
		if (WikiPage::isPage($pagename) || ! is_pagename($pagename)) {
			$pagename = $page; // Set default
		} else {
			page_write($page, 'move to [[' . $pagename . ']]');
		}
		page_write($pagename, $postdata);
	}

	return $page;
}

// Generate new page contents
function plugin_bugtrack_template($base, $summary, $name, $priority, $state, $category, $version, $body)
{
	global $_plugin_bugtrack, $WikiName;

	if (! preg_match("/^$WikiName$$/",$base)) $base = '[[' . $base . ']]';
	if ($name != '' && ! preg_match("/^$WikiName$$/",$name)) $name = '[[' . $name . ']]';

	if ($name    == '') $name    = $_plugin_bugtrack['noname'];
	if ($summary == '') $summary = $_plugin_bugtrack['nosummary'];
/*
	 return <<<EOD
* $summary

- ${_plugin_bugtrack['base'    ]}: $base
- ${_plugin_bugtrack['name'    ]}: $name
- ${_plugin_bugtrack['priority']}: $priority
- ${_plugin_bugtrack['state'   ]}: $state
- ${_plugin_bugtrack['category']}: $category
- ${_plugin_bugtrack['date'    ]}: now?
- ${_plugin_bugtrack['version' ]}: $version

** ${_plugin_bugtrack['body']}
$body
--------

#comment
EOD;*/
	$now = format_date(UTIME);
	return <<<EOD
* $summary

- ${_plugin_bugtrack['base'    ]}: $base
- ${_plugin_bugtrack['name'    ]}: $name
- ${_plugin_bugtrack['priority']}: $priority
- ${_plugin_bugtrack['state'   ]}: $state
- ${_plugin_bugtrack['category']}: $category
- ${_plugin_bugtrack['date'    ]}: $now
- ${_plugin_bugtrack['version' ]}: $version

** ${_plugin_bugtrack['body']}
$body
--------

#comment
EOD;
}

// ----------------------------------------
// BugTrack-List plugin

// #bugtrack_list plugin itself
function plugin_bugtrack_list_convert()
{
	global $_plugin_bugtrack;

	$page = WikiParam::getPage();
	if (func_num_args()) {
		list($_page) = func_get_args();
		$_page = get_fullname(strip_bracket($_page), $page);
		if (is_pagename($_page)) $page = $_page;
	}

	$data = array();
	$pattern = $page . '/';
	$pattern_len = strlen($pattern);
	foreach (get_existpages() as $page)
		if (strpos($page, $pattern) === 0 && is_numeric(substr($page, $pattern_len)))
			array_push($data, plugin_bugtrack_list_pageinfo($page));

	$count_list = count($_plugin_bugtrack['state_list']);

	$table = array();
	for ($i = 0; $i <= $count_list + 1; ++$i) $table[$i] = array();

	foreach ($data as $line) {
		list($page, $no, $summary, $name, $priority, $state, $category) = $line;
		foreach (array('summary', 'name', 'priority', 'state', 'category') as $item)
			$$item = htmlspecialchars($$item);
		$page_link = make_pagelink($page);

		$state_no = array_search($state, $_plugin_bugtrack['state_sort']);
		if ($state_no === NULL || $state_no === FALSE) $state_no = $count_list;
		$bgcolor = htmlspecialchars($_plugin_bugtrack['state_bgcolor'][$state_no]);

		$row = <<<EOD
 <tr>
  <td style="background-color:$bgcolor">$page_link</td>
  <td style="background-color:$bgcolor">$state</td>
  <td style="background-color:$bgcolor">$priority</td>
  <td style="background-color:$bgcolor">$category</td>
  <td style="background-color:$bgcolor">$name</td>
  <td style="background-color:$bgcolor">$summary</td>
 </tr>
EOD;
		$table[$state_no][$no] = $row;
	}

	$table_html = ' <tr>' . "\n";
	$bgcolor = htmlspecialchars($_plugin_bugtrack['header_bgcolor']);
	foreach (array('pagename', 'state', 'priority', 'category', 'name', 'summary') as $item)
		$table_html .= '  <th style="background-color:' . $bgcolor . '">' .
			htmlspecialchars($_plugin_bugtrack[$item]) . '</th>' . "\n";
	$table_html .= ' </tr>' . "\n";

	for ($i = 0; $i <= $count_list; ++$i) {
		ksort($table[$i], SORT_NUMERIC);
		$table_html .= join("\n", $table[$i]);
	}

	return '<table border="1" width="100%">' . "\n" .
		$table_html . "\n" .
		'</table>';
}

// Get one set of data from a page (or a page moved to $page)
function plugin_bugtrack_list_pageinfo($page, $no = NULL, $recurse = TRUE)
{
	global $WikiName, $InterWikiName, $BracketName, $_plugin_bugtrack;

	if ($no === NULL)
		$no = preg_match('/\/([0-9]+)$/', $page, $matches) ? $matches[1] : 0;

	$source = get_source($page);

	// Check 'moved' page _just once_
	$regex  = "/move\s*to\s*($WikiName|$InterWikiName|\[\[$BracketName\]\])/";
	$match  = array();
	if ($recurse && preg_match($regex, $source[0], $match))
		return plugin_bugtrack_list_pageinfo(strip_bracket($match[1]), $no, FALSE);

	$body = join("\n", $source);
	foreach(array('summary', 'name', 'priority', 'state', 'category') as $item) {
		$regex = '/-\s*' . preg_quote($_plugin_bugtrack[$item], '/') . '\s*:(.*)/';
		if (preg_match($regex, $body, $matches)) {
			if ($item == 'name') {
				$$item = strip_bracket(trim($matches[1]));
			} else {
				$$item = trim($matches[1]);
			}
		} else {
				$$item = ''; // Data not found
		}
	}

	if (preg_match("/\*([^\n]*)/", $body, $matches)) {
		$summary = $matches[1];
		make_heading($summary);
	}

	return array($page, $no, $summary, $name, $priority, $state, $category);
}
?>
