<?php
/**
 * unfreezeプラグイン
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2015 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */

// Show edit form when unfreezed
define('PLUGIN_UNFREEZE_EDIT', TRUE);

function plugin_unfreeze_action()
{
	global $script, $function_freeze;
	global $dummy_password;
	global $_title_isunfreezed, $_title_unfreezed, $_title_unfreeze;
	global $_msg_invalidpass, $_msg_unfreezing, $_btn_unfreeze;
	global $gEnvManager;

	$page = WikiParam::getPage();
	if (!$function_freeze || !is_page($page)) return array('msg' => '', 'body' => '');

	$pass = WikiParam::getVar('pass');
	$msg = $body = '';
	if (! is_freeze($page)) {
		// Unfreezed already
		$msg  = $_title_isunfreezed;
		$body = str_replace('$1', htmlspecialchars(strip_bracket($page)), $_title_isunfreezed);
	} else if ($pass != '' && pkwk_login($pass)) {
		// Unfreeze
		//$postdata = get_source($page);
		//array_shift($postdata);
		//$postdata = join('', $postdata);
		//file_write(DATA_DIR, $page, $postdata, TRUE);
		// ページをロックを解除
		WikiPage::lockPage($page, false);

		// Update 
		is_freeze($page, TRUE);
		if (PLUGIN_UNFREEZE_EDIT) {
			//$vars['cmd'] = 'read'; // To show 'Freeze' link
			WikiParam::setCmd('read');
			$msg  = $_title_unfreezed;
			//$body = edit_form($page, $postdata);
			$body = edit_form($page, get_source($page, true));
		} else {
			//$vars['cmd'] = 'read';
			WikiParam::setCmd('read');
			$msg  = $_title_unfreezed;
			$body = '';
		}
	} else {
		// Show unfreeze form
		$msg    = $_title_unfreeze;
		$s_page = htmlspecialchars($page);
		$body   = ($pass == '') ? '' : "<p><strong>$_msg_invalidpass</strong></p>\n";
		
		// modified for Magic3 by naoki on 2008/10/10
		$postScript = $script . WikiParam::convQuery("?");

		$editAuth = WikiConfig::isUserWithEditAuth();		// 編集権限があるかどうか
		
		// テンプレートタイプに合わせて出力を変更
		$templateType = $gEnvManager->getCurrentTemplateType();
		if ($templateType == M3_TEMPLATE_BOOTSTRAP_30){		// Bootstrap型テンプレートの場合
			if (!$editAuth) $body .= '<p>' . $_msg_unfreezing . '</p>' . M3_NL;
			$body .= '<form action="' . $postScript . '" method="post" class="form form-inline" role="form">' . M3_NL;
			$body .= '<input type="hidden"   name="wcmd"  value="unfreeze" />' . M3_NL;
			$body .= '<input type="hidden"   name="page" value="' . $s_page . '" />' . M3_NL;
			$body .= '<input type="hidden"   name="pass" />' . M3_NL;
			if ($editAuth){
				$body .= '<input type="hidden" name="password" value="' . $dummy_password . '" />' . M3_NL;
			} else {
				$body .= '<div class="form-group"><input type="password" class="form-control" name="password" size="12" /></div>' . M3_NL;
			}
			$body .= '<input type="submit"   name="ok"   class="button btn" value="' . $_btn_unfreeze . '" onclick="this.form.pass.value = hex_md5(this.form.password.value);" />' . M3_NL;
			$body .= '</form>' . M3_NL;
		} else {
			if (!$editAuth) $body .= '<p>'. $_msg_unfreezing . '</p>' . M3_NL;
			$body .= '<form action="' . $postScript . '" method="post" class="form">' . M3_NL;
			$body .= '<div>' . M3_NL;
			$body .= '<input type="hidden"   name="wcmd"  value="unfreeze" />' . M3_NL;
			$body .= '<input type="hidden"   name="page" value="' . $s_page . '" />' . M3_NL;
			$body .= '<input type="hidden"   name="pass" />' . M3_NL;
			if ($editAuth){
				$body .= '<input type="hidden" name="password" value="' . $dummy_password . '" />' . M3_NL;
			} else {
				$body .= '<input type="password" name="password" size="12" />' . M3_NL;
			}
			$body .= '<input type="submit"   name="ok"   class="button" value="' . $_btn_unfreeze . '" onclick="this.form.pass.value = hex_md5(this.form.password.value);" />' . M3_NL;
			$body .= '</div>' . M3_NL;
			$body .= '</form>' . M3_NL;
		}
	}

	return array('msg'=>$msg, 'body'=>$body);
}
?>
