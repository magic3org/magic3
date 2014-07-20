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
 * @copyright  Copyright 2006-2014 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: freeze.inc.php 1601 2009-03-21 05:51:06Z fishbone $
 * @link       http://www.magic3.org
 */
function plugin_freeze_convert() { return ''; }

function plugin_freeze_action()
{
	global $script, $function_freeze;
	global $_title_isfreezed, $_title_freezed, $_title_freeze;
	global $_msg_invalidpass, $_msg_freezing, $_btn_freeze;
	global $gEnvManager;
	
	$page = WikiParam::getPage();
	if (! $function_freeze || ! is_page($page)) return array('msg' => '', 'body' => '');

	$pass = WikiParam::getVar('pass');
	$msg = $body = '';
	if (is_freeze($page)) {
		// Freezed already
		$msg  = $_title_isfreezed;
		$body = str_replace('$1', htmlspecialchars(strip_bracket($page)), $_title_isfreezed);
	} else if ($pass != '' && pkwk_login($pass)) {
		// ページをロックする
		WikiPage::lockPage($page, true);

		// Update
		is_freeze($page, TRUE);
		WikiParam::setCmd('read');
		$msg  = $_title_freezed;
		$body = '';
	} else {
		// Show a freeze form
		$msg    = $_title_freeze;
		$s_page = htmlspecialchars($page);
		$body   = ($pass == '') ? '' : "<p><strong>$_msg_invalidpass</strong></p>\n";
		$postScript = $script . WikiParam::convQuery("?");
		
		// テンプレートタイプに合わせて出力を変更
		$templateType = $gEnvManager->getCurrentTemplateType();
		if ($templateType == M3_TEMPLATE_BOOTSTRAP_30){		// Bootstrap型テンプレートの場合
			$body  .= <<<EOD
<p>$_msg_freezing</p>
<form action="$postScript" method="post" class="form form-inline" role="form">
  <input type="hidden"   name="wcmd"  value="freeze" />
  <input type="hidden"   name="page" value="$s_page" />
  <input type="hidden"   name="pass" />
  <div class="form-group"><input type="password" class="form-control" name="password" size="12" /></div>
  <input type="submit"   name="ok"   class="button btn btn-default" value="$_btn_freeze" onclick="this.form.pass.value = hex_md5(this.form.password.value);" />
</form>
EOD;
		} else {
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
	}

	return array('msg'=>$msg, 'body'=>$body);
}
?>
