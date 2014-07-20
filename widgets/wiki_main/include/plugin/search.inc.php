<?php
/**
 * searchプラグイン
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2014 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */

// Allow search via GET method 'index.php?plugin=search&word=keyword'
// NOTE: Also allows DoS to your site more easily by SPAMbot or worm or ...
define('PLUGIN_SEARCH_DISABLE_GET_ACCESS', 1); // 1, 0

define('PLUGIN_SEARCH_MAX_LENGTH', 80);
define('PLUGIN_SEARCH_MAX_BASE',   16); // #search(1,2,3,...,15,16)

// Show a search box on a page
function plugin_search_convert()
{
	static $done;

	if (isset($done)) {
		return '#search(): You already view a search box<br />' . "\n";
	} else {
		$done = TRUE;
		$args = func_get_args();
		return plugin_search_search_form('', '', $args);
	}
}

function plugin_search_action()
{
	//global $post, $vars, $_title_result, $_title_search, $_msg_searching;
	global $_title_result, $_title_search, $_msg_searching;

	if (PLUGIN_SEARCH_DISABLE_GET_ACCESS) {
		//$s_word = isset($post['word']) ? htmlspecialchars($post['word']) : '';
		$word = WikiParam::getPostVar('word');
	} else {
		//$s_word = isset($vars['word']) ? htmlspecialchars($vars['word']) : '';
		$word = WikiParam::getVar('word');
	}
	$s_word = htmlspecialchars($word);
	
	if (strlen($s_word) > PLUGIN_SEARCH_MAX_LENGTH) {
		//unset($vars['word']); // Stop using $_msg_word at lib/html.php
		die_message('Search words too long');
	}

	/*$type = isset($vars['type']) ? $vars['type'] : '';
	$base = isset($vars['base']) ? $vars['base'] : '';*/
	$type = WikiParam::getVar('type');
	$base = WikiParam::getVar('base');

	if ($s_word != '') {
		// Search
		$msg  = str_replace('$1', $s_word, $_title_result);
		//$body = do_search($vars['word'], $type, FALSE, $base);
		$body = do_search($word, $type, false, $base);
	} else {
		// Init
		//unset($vars['word']); // Stop using $_msg_word at lib/html.php
		$msg  = $_title_search;
		$body = '<br />' . "\n" . $_msg_searching . "\n";
	}

	// Show search form
	$bases = ($base == '') ? array() : array($base);
	$body .= plugin_search_search_form($s_word, $type, $bases);

	return array('msg'=>$msg, 'body'=>$body);
}

function plugin_search_search_form($s_word = '', $type = '', $bases = array())
{
	global $script, $_btn_and, $_btn_or, $_btn_search;
	global $_search_pages, $_search_all;
	global $gEnvManager;
	
	// テンプレートタイプに合わせて出力を変更
	$templateType = $gEnvManager->getCurrentTemplateType();
	
	$and_check = $or_check = '';
	if ($type == 'OR') {
		$or_check  = ' checked="checked"';
	} else {
		$and_check = ' checked="checked"';
	}

	$base_option = '';
	if (!empty($bases)) {
		$base_msg = '';
		$_num = 0;
		$check = ' checked="checked"';
		foreach($bases as $base) {
			++$_num;
			if (PLUGIN_SEARCH_MAX_BASE < $_num) break;
			$label_id = '_p_search_base_id_' . $_num;
			$s_base   = htmlspecialchars($base);
			$base_str = '<strong>' . $s_base . '</strong>';
			$base_label = str_replace('$1', $base_str, $_search_pages);
			
			// テンプレートタイプに合わせて出力を変更
			if ($templateType == M3_TEMPLATE_BOOTSTRAP_30){		// Bootstrap型テンプレートの場合
				$base_msg  .=<<<EOD
<div class="radio-inline"><input type="radio" name="base" id="$label_id" value="$s_base" $check />
  <label for="$label_id">$base_label</label></div>
EOD;
			} else {
				$base_msg  .=<<<EOD
 <div>
  <input type="radio" name="base" id="$label_id" value="$s_base" $check />
  <label for="$label_id">$base_label</label>
 </div>
EOD;
			}
			$check = '';
		}
		
		// テンプレートタイプに合わせて出力を変更
		if ($templateType == M3_TEMPLATE_BOOTSTRAP_30){		// Bootstrap型テンプレートの場合
			$base_msg .=<<<EOD
<div class="radio-inline"><input type="radio" name="base" id="_p_search_base_id_all" value="" />
<label for="_p_search_base_id_all">$_search_all</label></div>
EOD;
			$base_option = '<div>' . $base_msg . '</div>';
		} else {
			$base_msg .=<<<EOD
  <input type="radio" name="base" id="_p_search_base_id_all" value="" />
  <label for="_p_search_base_id_all">$_search_all</label>
EOD;
			$base_option = '<div class="small">' . $base_msg . '</div>';
		}
	}
	$postScript = $script . WikiParam::convQuery("?cmd=search");
	
	// テンプレートタイプに合わせて出力を変更
	if ($templateType == M3_TEMPLATE_BOOTSTRAP_30){		// Bootstrap型テンプレートの場合
		$retValue = <<<EOD
<form action="$postScript" method="post" class="form form-inline" role="form">
  <div class="form-group"><input type="text" class="form-control" name="word" value="$s_word" size="20" /></div>
  <div class="radio-inline"><label for="_p_search_AND"><input type="radio" name="type" id="_p_search_AND" value="AND" $and_check />
  $_btn_and</label></div>
  <div class="radio-inline"><label for="_p_search_OR"><input type="radio" name="type" id="_p_search_OR"  value="OR"  $or_check  />
  $_btn_or</label></div>
  <input type="submit" class="button btn" value="$_btn_search" />
$base_option
</form>
EOD;
	} else {
		$retValue = <<<EOD
<form action="$postScript" method="post" class="form">
 <div>
  <input type="text"  name="word" value="$s_word" size="20" />
  <input type="radio" name="type" id="_p_search_AND" value="AND" $and_check />
  <label for="_p_search_AND">$_btn_and</label>
  <input type="radio" name="type" id="_p_search_OR"  value="OR"  $or_check  />
  <label for="_p_search_OR">$_btn_or</label>
  &nbsp;<input type="submit" class="button" value="$_btn_search" />
 </div>
$base_option
</form>
EOD;
	}
	return $retValue;
}
?>
