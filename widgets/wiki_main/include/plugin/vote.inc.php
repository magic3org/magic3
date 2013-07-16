<?php
/**
 * voteプラグイン
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2009 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: vote.inc.php 1601 2009-03-21 05:51:06Z fishbone $
 * @link       http://www.magic3.org
 */
function plugin_vote_action()
{
	//global $vars, $script, $cols,$rows;
	global $script, $cols,$rows;
	global $_title_collided, $_msg_collided, $_title_updated;
	global $_vote_plugin_votes;

	if (PKWK_READONLY) die_message('PKWK_READONLY prohibits editing');

	//$postdata_old  = get_source($vars['refer']);
	$postdata_old  = get_source(WikiParam::getRefer());

	$vote_no = 0;
	$title = $body = $postdata = $postdata_input = $vote_str = '';
	$matches = array();
	foreach($postdata_old as $line) {
		//if (! preg_match('/^#vote(?:\((.*)\)(.*))?$/i', $line, $matches) || $vote_no++ != $vars['vote_no']) {
		if (!preg_match('/^#vote(?:\((.*)\)(.*))?$/i', $line, $matches) || $vote_no++ != WikiParam::getVar('vote_no')){
			$postdata .= $line;
			continue;
		}
		$args  = explode(',', $matches[1]);
		$lefts = isset($matches[2]) ? $matches[2] : '';

		foreach($args as $arg) {
			$cnt = 0;
			if (preg_match('/^(.+)\[(\d+)\]$/', $arg, $matches)) {
				$arg = $matches[1];
				$cnt = $matches[2];
			}
			$e_arg = encode($arg);
			//if (! empty($vars['vote_' . $e_arg]) && $vars['vote_' . $e_arg] == $_vote_plugin_votes)
			if (WikiParam::getVar('vote_' . $e_arg) == $_vote_plugin_votes)
				++$cnt;

			$votes[] = $arg . '[' . $cnt . ']';
		}

		$vote_str       = '#vote(' . @join(',', $votes) . ')' . $lefts . "\n";
		$postdata_input = $vote_str;
		$postdata      .= $vote_str;
	}

	//if (md5(@join('', get_source($vars['refer']))) != $vars['digest']) {
	if (md5(get_source(WikiParam::getRefer(), true)) != WikiParam::getVar('digest')) {
		$title = $_title_collided;

	/*	$s_refer          = htmlspecialchars($vars['refer']);
		$s_digest         = htmlspecialchars($vars['digest']);*/
		$s_refer          = htmlspecialchars(WikiParam::getRefer());
		$s_digest         = htmlspecialchars(WikiParam::getVar('digest'));
		
		$postScript = $script . WikiParam::convQuery("?cmd=preview");
		$s_postdata_input = htmlspecialchars($postdata_input);
		$body = <<<EOD
$_msg_collided
<form action="$postScript" method="post" class="form">
 <div>
  <input type="hidden" name="refer"  value="$s_refer" />
  <input type="hidden" name="digest" value="$s_digest" />
  <textarea name="msg" class="wiki_edit" rows="$rows" cols="$cols">$s_postdata_input</textarea><br />
 </div>
</form>
EOD;
	} else {
		//page_write($vars['refer'], $postdata);
		page_write(WikiParam::getRefer(), $postdata);
		$title = $_title_updated;
	}

	//$vars['page'] = $vars['refer'];
	WikiParam::setPage(WikiParam::getRefer());

	return array('msg'=>$title, 'body'=>$body);
}

function plugin_vote_convert()
{
	//global $script, $vars,  $digest;
	global $script;
	global $_vote_plugin_choice, $_vote_plugin_votes;
	static $number = array();

	//$page = isset($vars['page']) ? $vars['page'] : '';
	$page = WikiParam::getPage();
	
	// Vote-box-id in the page
	if (! isset($number[$page])) $number[$page] = 0; // Init
	$vote_no = $number[$page]++;

	if (! func_num_args()) return '#vote(): No arguments<br />' . "\n";

	if (PKWK_READONLY) {
		$_script = '';
		$_submit = 'hidden';
	} else {
		//$_script = $script;
		$_script = $script . WikiParam::convQuery("?");
		$_submit = 'submit';
	}

	$args     = func_get_args();
	$s_page   = htmlspecialchars($page);
	//$s_digest = htmlspecialchars($digest);
	$s_digest = htmlspecialchars(WikiParam::getDigest());

	$body = <<<EOD
<form action="$_script" method="post" class="form">
 <table cellspacing="0" cellpadding="2" class="style_table" summary="vote">
  <tr>
   <td align="left" class="vote_label" style="padding-left:1em;padding-right:1em"><strong>$_vote_plugin_choice</strong>
    <input type="hidden" name="plugin"  value="vote" />
    <input type="hidden" name="refer"   value="$s_page" />
    <input type="hidden" name="vote_no" value="$vote_no" />
    <input type="hidden" name="digest"  value="$s_digest" />
   </td>
   <td align="center" class="vote_label"><strong>$_vote_plugin_votes</strong></td>
  </tr>
EOD;

	$tdcnt = 0;
	$matches = array();
	foreach($args as $arg) {
		$cnt = 0;

		if (preg_match('/^(.+)\[(\d+)\]$/', $arg, $matches)) {
			$arg = $matches[1];
			$cnt = $matches[2];
		}
		$e_arg = encode($arg);

		$link = make_link($arg);

		$cls = ($tdcnt++ % 2)  ? 'vote_td1' : 'vote_td2';

		$body .= <<<EOD
  <tr>
   <td align="left"  class="$cls" style="padding-left:1em;padding-right:1em;">$link</td>
   <td align="right" class="$cls">$cnt&nbsp;&nbsp;
    <input type="$_submit" name="vote_$e_arg" class="button" value="$_vote_plugin_votes" />
   </td>
  </tr>
EOD;
	}

	$body .= <<<EOD
 </table>
</form>
EOD;

	return $body;
}
?>
