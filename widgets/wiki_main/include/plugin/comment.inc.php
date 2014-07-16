<?php
/**
 * commentプラグイン
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2014 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: comment.inc.php 3474 2010-08-13 10:36:48Z fishbone $
 * @link       http://www.magic3.org
 */
// Copyright (C)
//   2002-2005 PukiWiki Developers Team
//   2001-2002 Originally written by yu-ji
// License: GPL v2 or (at your option) any later version

define('PLUGIN_COMMENT_DIRECTION_DEFAULT', '1'); // 1: above 0: below
define('PLUGIN_COMMENT_SIZE_MSG',  70);
define('PLUGIN_COMMENT_SIZE_NAME', 15);
// Bootstrap用
define('PLUGIN_COMMENT_SIZE_MSG_BOOTSTRAP',	40); // テキストエリアのカラム数

define('PLUGIN_COMMENT_FORMAT_MSG',  '$msg');
define('PLUGIN_COMMENT_FORMAT_NAME', '[[$name]]');
define('PLUGIN_COMMENT_FORMAT_NOW',  '&new{$now};');
define('PLUGIN_COMMENT_FORMAT_STRING', "\x08MSG\x08 -- \x08NAME\x08 \x08NOW\x08");

function plugin_comment_action()
{
	//global $script, $vars, $now, $_title_updated, $_no_name;
	global $script, $_title_updated, $_no_name;
	global $_msg_comment_collided, $_title_comment_collided;

	if (PKWK_READONLY) die_message('PKWK_READONLY prohibits editing');

	//if (! isset($vars['msg'])) return array('msg'=>'', 'body'=>''); // Do nothing
	$msg = WikiParam::getMsg();
	if ($msg == '') return array('msg'=>'', 'body'=>''); // Do nothing

	//$vars['msg'] = str_replace("\n", '', $vars['msg']); // Cut LFs
	$msg = str_replace("\n", '', $msg); // Cut LFs
	$head = '';
	$match = array();
/*	if (preg_match('/^(-{1,2})-*\s*(.*)/', $vars['msg'], $match)) {
		$head        = $match[1];
		$vars['msg'] = $match[2];
	}
	if ($vars['msg'] == '') return array('msg'=>'', 'body'=>''); // Do nothing
	*/
	if (preg_match('/^(-{1,2})-*\s*(.*)/', $msg, $match)) {
		$head	= $match[1];
		$msg	= $match[2];
	}
	if ($msg == '') return array('msg'=>'', 'body'=>''); // Do nothing

	//$comment  = str_replace('$msg', $vars['msg'], PLUGIN_COMMENT_FORMAT_MSG);
	$comment  = str_replace('$msg', $msg, PLUGIN_COMMENT_FORMAT_MSG);
	$name = WikiParam::getVar('name');
	$nodate = WikiParam::getVar('nodate');
	//if(isset($vars['name']) || ($vars['nodate'] != '1')) {
	if($name != '' || $nodate != '1'){
		//$_name = (! isset($vars['name']) || $vars['name'] == '') ? $_no_name : $vars['name'];
		$_name = ($name == '') ? $_no_name : $name;
		$_name = ($_name == '') ? '' : str_replace('$name', $_name, PLUGIN_COMMENT_FORMAT_NAME);
		//$_now  = ($vars['nodate'] == '1') ? '' : str_replace('$now', $now, PLUGIN_COMMENT_FORMAT_NOW);
		$_now  = ($nodate == '1') ? '' : str_replace('$now', WikiConfig::getNow(), PLUGIN_COMMENT_FORMAT_NOW);
		$comment = str_replace("\x08MSG\x08",  $comment, PLUGIN_COMMENT_FORMAT_STRING);
		$comment = str_replace("\x08NAME\x08", $_name, $comment);
		$comment = str_replace("\x08NOW\x08",  $_now,  $comment);
	}
	$comment = '-' . $head . ' ' . $comment;

	$postdata	= '';
	$comment_no	= 0;
	//$above       = (isset($vars['above']) && $vars['above'] == '1');
	$above		= (WikiParam::getVar('above') == '1');
	$commentNo	= WikiParam::getVar('comment_no');
	$refer		= WikiParam::getRefer();
	//foreach (get_source($vars['refer']) as $line) {
	foreach (get_source($refer) as $line){
		if (! $above) $postdata .= $line;
		//if (preg_match('/^#comment/i', $line) && $comment_no++ == $vars['comment_no']) {
		if (preg_match('/^#comment/i', $line) && $comment_no++ == $commentNo){
			if ($above) {
				$postdata = rtrim($postdata) . "\n" .
					$comment . "\n" .
					"\n";  // Insert one blank line above #commment, to avoid indentation
			} else {
				$postdata = rtrim($postdata) . "\n" .
					$comment . "\n"; // Insert one blank line below #commment
			}
		}
		if ($above) $postdata .= $line;
	}

	$title = $_title_updated;
	$body = '';
	//if (md5(@join('', get_source($vars['refer']))) != $vars['digest']) {
	if (md5(get_source($refer, true)) != WikiParam::getVar('digest')){
		$title = $_title_comment_collided;
		//$body  = $_msg_comment_collided . make_pagelink($vars['refer']);
		$body  = $_msg_comment_collided . make_pagelink($refer);
	}

	//page_write($vars['refer'], $postdata);
	page_write($refer, $postdata);

	$retvars['msg']  = $title;
	$retvars['body'] = $body;

	//$vars['page'] = $vars['refer'];
	WikiParam::setPage($refer);

	return $retvars;
}

function plugin_comment_convert()
{
	//global $vars, $digest, $_btn_comment, $_btn_name, $_msg_comment;
	global $digest, $_btn_comment, $_btn_name, $_msg_comment;
	global $gEnvManager;
	static $numbers = array();
	static $comment_cols = PLUGIN_COMMENT_SIZE_MSG;

	if (PKWK_READONLY) return ''; // Show nothing

	// テンプレートタイプに合わせて出力を変更
	$templateType = $gEnvManager->getCurrentTemplateType();
	
	$page = WikiParam::getPage();
	if (!isset($numbers[$page])) $numbers[$page] = 0;
	$comment_no = $numbers[$page]++;

	$options = func_num_args() ? func_get_args() : array();
	if (in_array('noname', $options)) {
		$nametags = '<label for="_p_comment_comment_' . $comment_no . '">' .
			$_msg_comment . '</label>';
	} else {
		if ($templateType == M3_TEMPLATE_BOOTSTRAP_30){		// Bootstrap型テンプレートの場合
			$nametags = '<div><div class="form-group"><label for="_p_comment_name_' . $comment_no . '">' . $_btn_name .
						'<input type="text" class="form-control" name="name" id="_p_comment_name_' . $comment_no .  '" size="' . PLUGIN_COMMENT_SIZE_NAME . '" /></label></div></div>' . "\n";
		} else {
			$nametags = '<label for="_p_comment_name_' . $comment_no . '">' . $_btn_name . '</label>' .
						'<input type="text" name="name" id="_p_comment_name_' . $comment_no .  '" size="' . PLUGIN_COMMENT_SIZE_NAME . '" />' . "\n";
		}
	}
	$nodate = in_array('nodate', $options) ? '1' : '0';
	$above  = in_array('above',  $options) ? '1' : (in_array('below', $options) ? '0' : PLUGIN_COMMENT_DIRECTION_DEFAULT);

	$script = get_script_uri();
	$postScript = $script . WikiParam::convQuery("?");
	$s_page = htmlspecialchars($page);
	
	// テンプレートタイプに合わせて出力を変更
	if ($templateType == M3_TEMPLATE_BOOTSTRAP_30){		// Bootstrap型テンプレートの場合
		$size = PLUGIN_COMMENT_SIZE_MSG_BOOTSTRAP;
		$string = <<<EOD
<form action="$postScript" method="post" class="form form-inline" role="form">
  <input type="hidden" name="plugin" value="comment" />
  <input type="hidden" name="refer"  value="$s_page" />
  <input type="hidden" name="comment_no" value="$comment_no" />
  <input type="hidden" name="nodate" value="$nodate" />
  <input type="hidden" name="above"  value="$above" />
  <input type="hidden" name="digest" value="$digest" />
  $nametags
  <div><div class="form-group"><input type="text" class="form-control" name="msg" id="_p_comment_comment_{$comment_no}" size="$size" maxlength="$comment_cols" /></div></div>
  <input type="submit" name="comment" class="button btn btn-default" value="$_btn_comment" />
</form>
EOD;
	} else {
		$string = <<<EOD
<br />
<form action="$postScript" method="post" class="form">
 <div>
  <input type="hidden" name="plugin" value="comment" />
  <input type="hidden" name="refer"  value="$s_page" />
  <input type="hidden" name="comment_no" value="$comment_no" />
  <input type="hidden" name="nodate" value="$nodate" />
  <input type="hidden" name="above"  value="$above" />
  <input type="hidden" name="digest" value="$digest" />
  $nametags
  <input type="text"   name="msg" id="_p_comment_comment_{$comment_no}" size="$comment_cols" />
  <input type="submit" name="comment" class="button" value="$_btn_comment" />
 </div>
</form>
EOD;
	}
	return $string;
}
?>
