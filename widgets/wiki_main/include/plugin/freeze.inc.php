<?php
/**
 * freezeプラグイン
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2009 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: freeze.inc.php 1601 2009-03-21 05:51:06Z fishbone $
 * @link       http://www.magic3.org
 */

function plugin_freeze_convert() { return ''; }

function plugin_freeze_action()
{
	// modified for Magic3 by naoki on 2008/10/10
	//global $script, $vars, $function_freeze;
	global $script, $function_freeze;
	global $_title_isfreezed, $_title_freezed, $_title_freeze;
	global $_msg_invalidpass, $_msg_freezing, $_btn_freeze;

	//$page = isset($vars['page']) ? $vars['page'] : '';
	$page = WikiParam::getPage();
	if (! $function_freeze || ! is_page($page))
		return array('msg' => '', 'body' => '');

	//$pass = isset($vars['pass']) ? $vars['pass'] : NULL;
	$pass = WikiParam::getVar('pass');
	$msg = $body = '';
	if (is_freeze($page)) {
		// Freezed already
		$msg  = $_title_isfreezed;
		$body = str_replace('$1', htmlspecialchars(strip_bracket($page)),
			$_title_isfreezed);

	//} else if ($pass !== NULL && pkwk_login($pass)) {
	} else if ($pass != '' && pkwk_login($pass)) {
		// Freeze
		//$postdata = get_source($page);
		//array_unshift($postdata, "#freeze\n");	// removed for magic3
		//file_write(DATA_DIR, $page, join('', $postdata), TRUE);
		// ページをロックする
		WikiPage::lockPage($page, true);

		// Update
		is_freeze($page, TRUE);
		//$vars['cmd'] = 'read';
		WikiParam::setCmd('read');
		$msg  = $_title_freezed;
		$body = '';

	} else {
		// Show a freeze form
		$msg    = $_title_freeze;
		$s_page = htmlspecialchars($page);
		//$body   = ($pass === NULL) ? '' : "<p><strong>$_msg_invalidpass</strong></p>\n";
		$body   = ($pass == '') ? '' : "<p><strong>$_msg_invalidpass</strong></p>\n";
		// modified for Magic3 by naoki on 2008/10/6
		$postScript = $script . WikiParam::convQuery("?");
		$body  .= <<<EOD
<p>$_msg_freezing</p>
<form action="$postScript" method="post" class="form">
 <div>
  <input type="hidden"   name="wcmd"  value="freeze" />
  <input type="hidden"   name="page" value="$s_page" />
  <input type="hidden"   name="pass" />
  <input type="password" name="password" size="12" />
  <input type="submit"   name="ok"   class="button" value="$_btn_freeze" onclick="this.form.pass.value = hex_md5(this.form.password.value);" />
 </div>
</form>
EOD;
	}

	return array('msg'=>$msg, 'body'=>$body);
}
?>
