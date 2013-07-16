<?php
/**
 * insertプラグイン
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2009 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: insert.inc.php 1601 2009-03-21 05:51:06Z fishbone $
 * @link       http://www.magic3.org
 */
define('INSERT_COLS', 70); // Columns of textarea
define('INSERT_ROWS',  5); // Rows of textarea
define('INSERT_INS',   1); // Order of insertion (1:before the textarea, 0:after)

function plugin_insert_action()
{
	//global $script, $vars, $cols, $rows;
	global $script, $cols, $rows;
	global $_title_collided, $_msg_collided, $_title_updated;

	if (PKWK_READONLY) die_message('PKWK_READONLY prohibits editing');
	//if (! isset($vars['msg']) || $vars['msg'] == '') return;
	$msg = WikiParam::getMsg();
	if ($msg == '') return;

/*	$vars['msg'] = preg_replace('/' . "\r" . '/', '', $vars['msg']);
	$insert = ($vars['msg'] != '') ? "\n" . $vars['msg'] . "\n" : '';*/
	$msg = preg_replace('/' . "\r" . '/', '', $msg);
	$insert = ($msg != '') ? "\n" . $msg . "\n" : '';

	$postdata = '';
	//$postdata_old  = get_source($vars['refer']);
	$refer = WikiParam::getRefer();
	$postdata_old  = get_source($refer);
	$insert_no = 0;

	foreach($postdata_old as $line) {
		if (! INSERT_INS) $postdata .= $line;
		if (preg_match('/^#insert$/i', $line)) {
			//if ($insert_no == $vars['insert_no'])
			if ($insert_no == WikiParam::getVar('insert_no'))
				$postdata .= $insert;
			$insert_no++;
		}
		if (INSERT_INS) $postdata .= $line;
	}

	$postdata_input = $insert . "\n";

	$body = '';
	$digest = WikiParam::getVar('digest');
	//if (md5(@join('', get_source($vars['refer']))) != $vars['digest']) {
	if (md5(get_source($refer, true)) != $digest) {
		$title = $_title_collided;
		$body = $_msg_collided . "\n";

/*		$s_refer  = htmlspecialchars($vars['refer']);
		$s_digest = htmlspecialchars($vars['digest']);*/
		$s_refer  = htmlspecialchars($refer);
		$s_digest = htmlspecialchars($digest);
		$s_postdata_input = htmlspecialchars($postdata_input);
		$postScript = $script . WikiParam::convQuery("?cmd=preview");
		$body .= <<<EOD
<form action="$postScript" method="post" class="form">
 <div>
  <input type="hidden" name="refer"  value="$s_refer" />
  <input type="hidden" name="digest" value="$s_digest" />
  <textarea name="msg" class="wiki_edit" rows="$rows" cols="$cols">$s_postdata_input</textarea><br />
 </div>
</form>
EOD;
/*		$body .= <<<EOD
<form action="$script?cmd=preview" method="post" class="form">
 <div>
  <input type="hidden" name="refer"  value="$s_refer" />
  <input type="hidden" name="digest" value="$s_digest" />
  <textarea name="msg" rows="$rows" cols="$cols" id="textarea">$s_postdata_input</textarea><br />
 </div>
</form>
EOD;*/
	} else {
		//page_write($vars['refer'], $postdata);
		page_write($refer, $postdata);

		$title = $_title_updated;
	}
	$retvars['msg']  = $title;
	$retvars['body'] = $body;

	//$vars['page'] = $vars['refer'];
	WikiParam::setPage($refer);

	return $retvars;
}

function plugin_insert_convert()
{
	//global $script, $vars, $digest;
	global $script;
	global $_btn_insert;
	static $numbers = array();

	if (PKWK_READONLY) return ''; // Show nothing

	//if (! isset($numbers[$vars['page']])) $numbers[$vars['page']] = 0;
	$page = WikiParam::getPage();
	if (! isset($numbers[$page])) $numbers[$page] = 0;

	//$insert_no = $numbers[$vars['page']]++;
	$insert_no = $numbers[$page]++;

	//$s_page   = htmlspecialchars($vars['page']);
	//$s_digest = htmlspecialchars($digest);
	$s_page   = htmlspecialchars($page);
	$s_digest = htmlspecialchars(WikiParam::getDigest());
	$postScript = $script . WikiParam::convQuery("?");
	$s_cols = INSERT_COLS;
	$s_rows = INSERT_ROWS;
	$string = <<<EOD
<form action="$postScript" method="post" class="form">
 <div>
  <input type="hidden" name="insert_no" value="$insert_no" />
  <input type="hidden" name="refer"  value="$s_page" />
  <input type="hidden" name="plugin" value="insert" />
  <input type="hidden" name="digest" value="$s_digest" />
  <textarea name="msg" class="wiki_edit" rows="$s_rows" cols="$s_cols"></textarea><br />
  <input type="submit" name="insert" class="button" value="$_btn_insert" />
 </div>
</form>
EOD;
	return $string;
}
?>
