<?php
/**
 * newpageプラグイン
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2009 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: newpage.inc.php 1601 2009-03-21 05:51:06Z fishbone $
 * @link       http://www.magic3.org
 */
function plugin_newpage_convert()
{
	//global $script, $vars, $_btn_edit, $_msg_newpage, $BracketName;
	global $script, $_btn_edit, $_msg_newpage, $BracketName;
	static $id = 0;

	if (PKWK_READONLY) return ''; // Show nothing

	$newpage = '';
	if (func_num_args()) list($newpage) = func_get_args();
	if (! preg_match('/^' . $BracketName . '$/', $newpage)) $newpage = '';

	//$s_page    = htmlspecialchars(isset($vars['refer']) ? $vars['refer'] : $vars['page']);
	$refer = WikiParam::getRefer();
	$s_page	= htmlspecialchars(($refer == '') ? WikiParam::getPage() : $refer);
	$s_newpage = htmlspecialchars($newpage);
	++$id;

	$postScript = $script . WikiParam::convQuery("?");
	$ret = <<<EOD
<form action="$postScript" method="post" class="form">
 <div>
  <input type="hidden" name="plugin" value="newpage" />
  <input type="hidden" name="refer"  value="$s_page" />
  <label for="_p_newpage_$id">$_msg_newpage:</label>
  <input type="text"   name="page" id="_p_newpage_$id" value="$s_newpage" size="30" />
  <input type="submit" class="button" value="$_btn_edit" />
 </div>
</form>
EOD;
	return $ret;
}

function plugin_newpage_action()
{
	//global $vars, $_btn_edit, $_msg_newpage;
	global $_btn_edit, $_msg_newpage;

	if (PKWK_READONLY) die_message('PKWK_READONLY prohibits editing');

	$page = WikiParam::getPage();
	//if ($vars['page'] == '') {
	if ($page == '') {
		$retvars['msg']  = $_msg_newpage;
		$retvars['body'] = plugin_newpage_convert();
		return $retvars;
	} else {
		/*$page    = strip_bracket($vars['page']);
		$r_page  = rawurlencode(isset($vars['refer']) ? get_fullname($page, $vars['refer']) : $page);
		$r_refer = rawurlencode($vars['refer']);*/

		$page    = strip_bracket($page);
		$refer = WikiParam::getRefer();
		$r_page  = rawurlencode(($refer == '') ? $page : get_fullname($page, $refer));
		$r_refer = rawurlencode($refer);
		
		pkwk_headers_sent();
		//header('Location: ' . get_script_uri() . '?cmd=read&page=' . $r_page . '&refer=' . $r_refer);
		header('Location: ' . get_script_uri() . WikiParam::convQuery('?cmd=read&page=' . $r_page . '&refer=' . $r_refer, false));
		exit;
	}
}
?>
