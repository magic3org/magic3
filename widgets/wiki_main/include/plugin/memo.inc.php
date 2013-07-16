<?php
/**
 * memoプラグイン
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2010 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: memo.inc.php 3474 2010-08-13 10:36:48Z fishbone $
 * @link       http://www.magic3.org
 */
define('MEMO_COLS', 60); // Columns of textarea
define('MEMO_ROWS',  5); // Rows of textarea

function plugin_memo_action()
{
	//global $script, $vars, $cols, $rows;
	global $script, $cols, $rows;
	global $_title_collided, $_msg_collided, $_title_updated;

	if (PKWK_READONLY) die_message('PKWK_READONLY prohibits editing');
	//if (! isset($vars['msg']) || $vars['msg'] == '') return;
	$msg = WikiParam::getMsg();
	if ($msg == '') return;

	//$memo_body = preg_replace('/' . "\r" . '/', '', $vars['msg']);
	$memo_body = preg_replace('/' . "\r" . '/', '', $msg);
	$memo_body = str_replace("\n", '\n', $memo_body);
	$memo_body = str_replace('"', '&#x22;', $memo_body); // Escape double quotes
	$memo_body = str_replace(',', '&#x2c;', $memo_body); // Escape commas

	//$postdata_old  = get_source($vars['refer']);
	$refer = WikiParam::getRefer();
	$postdata_old  = get_source($refer);
	$postdata = '';
	$memo_no = 0;
	foreach($postdata_old as $line) {
		if (preg_match("/^#memo\(?.*\)?$/i", $line)) {
			//if ($memo_no == $vars['memo_no']) {
			if ($memo_no == WikiParam::getVar('memo_no')) {
				$postdata .= '#memo(' . $memo_body . ')' . "\n";
				$line = '';
			}
			++$memo_no;
		}
		$postdata .= $line;
	}

	$postdata_input = $memo_body . "\n";

	$body = '';
	//if (md5(@join('', get_source($vars['refer']))) != $vars['digest']) {
	if (md5(get_source($refer, true)) != WikiParam::getVar('digest')) {
		$title = $_title_collided;
		$body  = $_msg_collided . "\n";

		/*$s_refer  = htmlspecialchars($vars['refer']);
		$s_digest = htmlspecialchars($vars['digest']);*/
		$s_refer  = htmlspecialchars($refer);
		$s_digest = htmlspecialchars(WikiParam::getVar('digest'));
		$s_postdata_input = htmlspecialchars($postdata_input);

		$postScript = $script . WikiParam::convQuery("?cmd=preview");
		$body .= <<<EOD
<form action="$postScript" method="post" class="form">
 <div>
  <input type="hidden" name="refer"  value="$s_refer" />
  <input type="hidden" name="digest" value="$s_digest" />
  <textarea name="msg" class="wiki_edit" rows="$rows" cols="$cols" id="textarea">$s_postdata_input</textarea><br />
 </div>
</form>
EOD;
/*
		$body .= <<<EOD
<form action="$script?cmd=preview" method="post">
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

function plugin_memo_convert()
{
	//global $script, $vars, $digest;
	global $script, $digest;
	global $_btn_memo_update;
	static $numbers = array();

/*	if (! isset($numbers[$vars['page']])) $numbers[$vars['page']] = 0;
	$memo_no = $numbers[$vars['page']]++;*/
	$page = WikiParam::getPage();
	if (! isset($numbers[$page])) $numbers[$page] = 0;
	$memo_no = $numbers[$page]++;

	$data = func_get_args();
	$data = implode(',', $data);	// Care all arguments
	$data = str_replace('&#x2c;', ',', $data); // Unescape commas
	$data = str_replace('&#x22;', '"', $data); // Unescape double quotes
	$data = htmlspecialchars(str_replace('\n', "\n", $data));

	if (PKWK_READONLY) {
		$_script = '';
		$_submit = '';	
	} else {
		$_script = $script . WikiParam::convQuery("?");
		$_submit = '<input type="submit" name="memo" class="button"   value="' . $_btn_memo_update . '" />';
	}

	/*$s_page   = htmlspecialchars($vars['page']);
	$s_digest = htmlspecialchars($digest);*/
	$s_page   = htmlspecialchars($page);
	$s_digest = htmlspecialchars(WikiParam::getDigest());
	$s_cols   = MEMO_COLS;
	$s_rows   = MEMO_ROWS;
	$string   = <<<EOD
<form action="$_script" method="post" class="memo" class="form">
 <div>
  <input type="hidden" name="memo_no" value="$memo_no" />
  <input type="hidden" name="refer"   value="$s_page" />
  <input type="hidden" name="plugin"  value="memo" />
  <input type="hidden" name="digest"  value="$s_digest" />
  <textarea name="msg" class="wiki_edit" rows="$s_rows" cols="$s_cols">$data</textarea><br />
  $_submit
 </div>
</form>
EOD;

	return $string;
}
?>
