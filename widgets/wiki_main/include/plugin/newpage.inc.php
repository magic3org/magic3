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
 * @copyright  Copyright 2006-2015 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
function plugin_newpage_convert()
{
	global $script, $_btn_edit, $_msg_newpage, $BracketName, $_msg_password;
	global $dummy_password;
	global $_msg_no_operation_allowed;
	global $gEnvManager;
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
	$editAuth = WikiConfig::isUserWithEditAuth();		// 編集権限があるかどうか

	if (!WikiConfig::isPasswordAuth() && !$editAuth){			// パスワード認証以外(管理権限ユーザまたはログインユーザ)の場合で、編集権限がない場合
		$body = "<p><strong>$_msg_no_operation_allowed</strong></p>\n";
	} else {
		// テンプレートタイプに合わせて出力を変更
		$body = '';
		$templateType = $gEnvManager->getCurrentTemplateType();
		if ($templateType == M3_TEMPLATE_BOOTSTRAP_30){		// Bootstrap型テンプレートの場合
			$body .= '<form action="' . $postScript . '" method="post" class="form form-inline" role="form">' . M3_NL;
			$body .= '<input type="hidden" name="plugin" value="newpage" />' . M3_NL;
			$body .= '<input type="hidden" name="refer"  value="' . $s_page . '" />' . M3_NL;
			$body .= '<div class="form-group"><label for="_p_newpage_' . $id . '">' . $_msg_newpage . ':</label>' . M3_NL;
			$body .= '<input type="text" class="form-control" name="page" id="_p_newpage_' . $id . '" value="' . $s_newpage . '" size="30" /></div>' . M3_NL;
			
			$body .= '<input type="hidden"   name="pass" />' . M3_NL;
			if ($editAuth){
				$body .= '<input type="hidden" name="password" value="' . $dummy_password . '" />' . M3_NL;
			} else {
				$body .= '<div class="form-group"><label>' . $_msg_password . ':<input type="password" class="form-control" name="password" size="12" /></label></div>' . M3_NL;
			}
			//$body .= '<input type="submit" class="button btn" value="' . $_btn_edit . '" />' . M3_NL;
			$body .= '<input type="submit" class="button btn" value="' . $_btn_edit . '" onclick="this.form.pass.value = hex_md5(this.form.password.value);" />' . M3_NL;
			$body .= '</form>' . M3_NL;
		} else {
			$body .= '<form action="' . $postScript . '" method="post" class="form">' . M3_NL;
			$body .= '<div>' . M3_NL;
			$body .= '<input type="hidden" name="plugin" value="newpage" />' . M3_NL;
			$body .= '<input type="hidden" name="refer"  value="' . $s_page . '" />' . M3_NL;
			$body .= '<label for="_p_newpage_' . $id . '">' . $_msg_newpage . ':</label>' . M3_NL;
			$body .= '<input type="text"   name="page" id="_p_newpage_' . $id . '" value="' . $s_newpage . '" size="30" />' . M3_NL;
			
			$body .= '<input type="hidden"   name="pass" />' . M3_NL;
			if ($editAuth){
				$body .= '<input type="hidden" name="password" value="' . $dummy_password . '" />' . M3_NL;
			} else {
				$body .= '<label>' . $_msg_password . ':<input type="password" name="password" size="12" /></label>' . M3_NL;
			}
			//$body .= '<input type="submit" class="button" value="' . $_btn_edit . '" />' . M3_NL;
			$body .= '<input type="submit" class="button" value="' . $_btn_edit . '" onclick="this.form.pass.value = hex_md5(this.form.password.value);" />' . M3_NL;
			$body .= '</div>' . M3_NL;
			$body .= '</form>' . M3_NL;
		}
	}
	return $body;
}

function plugin_newpage_action()
{
	global $_btn_edit, $_msg_newpage;
	global $gPageManager;

	if (PKWK_READONLY) die_message('PKWK_READONLY prohibits editing');

	$page = WikiParam::getPage();
	$pass = WikiParam::getVar('pass');

	if ($page == '') {
		// ページ名入力フィールド表示
		$retvars['msg']  = $_msg_newpage;
		$retvars['body'] = plugin_newpage_convert();
		return $retvars;
	} else if ($pass != '' && pkwk_login($pass)){
		/*$page    = strip_bracket($vars['page']);
		$r_page  = rawurlencode(isset($vars['refer']) ? get_fullname($page, $vars['refer']) : $page);
		$r_refer = rawurlencode($vars['refer']);*/

		$page    = strip_bracket($page);
		$refer = WikiParam::getRefer();
		$r_page  = rawurlencode(($refer == '') ? $page : get_fullname($page, $refer));
		$r_refer = rawurlencode($refer);
		
//		pkwk_headers_sent();
		//header('Location: ' . get_script_uri() . '?cmd=read&page=' . $r_page . '&refer=' . $r_refer);
//		header('Location: ' . get_script_uri() . WikiParam::convQuery('?cmd=read&page=' . $r_page . '&refer=' . $r_refer, false));
//		exit;
		$gPageManager->redirect(get_script_uri() . WikiParam::convQuery('?cmd=read&page=' . $r_page . '&refer=' . $r_refer, false));
	}
}
?>
