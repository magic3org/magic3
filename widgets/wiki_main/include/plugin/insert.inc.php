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
 * @copyright  Copyright 2006-2014 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: insert.inc.php 1601 2009-03-21 05:51:06Z fishbone $
 * @link       http://www.magic3.org
 */
define('INSERT_COLS', 70); // Columns of textarea
define('INSERT_ROWS',  5); // Rows of textarea
define('INSERT_INS',   1); // Order of insertion (1:before the textarea, 0:after)
// Bootstrap用
define('INSERT_COLS_BOOTSTRAP', 40); // Columns of textarea

function plugin_insert_action()
{
	global $script, $cols, $rows;
	global $_title_collided, $_msg_collided, $_title_updated;
	global $gEnvManager;
	
	if (PKWK_READONLY) die_message('PKWK_READONLY prohibits editing');

	$msg = WikiParam::getMsg();
	if ($msg == '') return;

	$msg = preg_replace('/' . "\r" . '/', '', $msg);
	$insert = ($msg != '') ? "\n" . $msg . "\n" : '';

	$postdata = '';
	$refer = WikiParam::getRefer();
	$postdata_old  = get_source($refer);
	$insert_no = 0;

	foreach($postdata_old as $line) {
		if (! INSERT_INS) $postdata .= $line;
		if (preg_match('/^#insert$/i', $line)) {
			if ($insert_no == WikiParam::getVar('insert_no'))
				$postdata .= $insert;
			$insert_no++;
		}
		if (INSERT_INS) $postdata .= $line;
	}

	$postdata_input = $insert . "\n";

	$body = '';
	$digest = WikiParam::getVar('digest');
	if (md5(get_source($refer, true)) != $digest) {
		$title = $_title_collided;
		$body = $_msg_collided . "\n";

		$s_refer  = htmlspecialchars($refer);
		$s_digest = htmlspecialchars($digest);
		$s_postdata_input = htmlspecialchars($postdata_input);
		$postScript = $script . WikiParam::convQuery("?cmd=preview");
		
		// テンプレートタイプに合わせて出力を変更
		$templateType = $gEnvManager->getCurrentTemplateType();
		if ($templateType == M3_TEMPLATE_BOOTSTRAP_30){		// Bootstrap型テンプレートの場合
			$body .= <<<EOD
<pre class="wiki_pre">$s_postdata_input</pre>
EOD;
		} else {
			$body .= <<<EOD
<form action="$postScript" method="post" class="form">
 <div>
  <input type="hidden" name="refer"  value="$s_refer" />
  <input type="hidden" name="digest" value="$s_digest" />
  <textarea name="msg" class="wiki_edit" rows="$rows" cols="$cols">$s_postdata_input</textarea><br />
 </div>
</form>
EOD;
		}
	} else {
		page_write($refer, $postdata);

		$title = $_title_updated;
	}
	$retvars['msg']  = $title;
	$retvars['body'] = $body;

	WikiParam::setPage($refer);

	return $retvars;
}

function plugin_insert_convert()
{
	global $script;
	global $_btn_insert;
	global $gEnvManager;
	static $numbers = array();

	if (PKWK_READONLY) return ''; // Show nothing

	$page = WikiParam::getPage();
	if (! isset($numbers[$page])) $numbers[$page] = 0;
	$insert_no = $numbers[$page]++;

	$s_page   = htmlspecialchars($page);
	$s_digest = htmlspecialchars(WikiParam::getDigest());
	$postScript = $script . WikiParam::convQuery("?");
	$s_cols = INSERT_COLS;
	$s_rows = INSERT_ROWS;
	
	// テンプレートタイプに合わせて出力を変更
	$templateType = $gEnvManager->getCurrentTemplateType();
	if ($templateType == M3_TEMPLATE_BOOTSTRAP_30){		// Bootstrap型テンプレートの場合
		$s_cols = INSERT_COLS_BOOTSTRAP;
		$string = <<<EOD
<form action="$postScript" method="post" class="form form-inline" role="form">
  <input type="hidden" name="insert_no" value="$insert_no" />
  <input type="hidden" name="refer"  value="$s_page" />
  <input type="hidden" name="plugin" value="insert" />
  <input type="hidden" name="digest" value="$s_digest" />
  <div><textarea name="msg" class="wiki_edit form-control" rows="$s_rows" cols="$s_cols"></textarea></div>
  <input type="submit" name="insert" class="button btn" value="$_btn_insert" />
</form>
EOD;
	} else {
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
	}
	return $string;
}
?>
