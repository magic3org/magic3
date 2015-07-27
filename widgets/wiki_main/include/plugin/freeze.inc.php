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
 * @copyright  Copyright 2006-2015 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
function plugin_freeze_convert() { return ''; }

function plugin_freeze_action()
{
	global $script, $function_freeze;
//	global $dummy_password;
	global $_title_isfreezed, $_title_freezed, $_title_freeze;
	global $_msg_invalidpass, $_msg_freezing, $_btn_freeze, $_msg_no_operation_allowed;
	global $gEnvManager;
	
	// ### パスワード認証フォーム表示 ###
	// 認証されている場合はスルーして関数以降を実行
	$retStatus = password_form();
	if (!empty($retStatus)) return $retStatus;
	
	$page = WikiParam::getPage();
	if (! $function_freeze || ! is_page($page)) return array('msg' => '', 'body' => '');

	$pass = WikiParam::getVar('pass');
	$action = WikiParam::getVar('action');			// 次画面遷移用
	$msg = $body = '';
		
	if (is_freeze($page)) {
		// Freezed already
		$msg  = $_title_isfreezed;
		$body = str_replace('$1', htmlspecialchars(strip_bracket($page)), $_title_isfreezed);
	//} else if ($pass != '' && pkwk_login($pass)) {
	} else if (!empty($action)){			// 「凍結」ボタン実行の場合
		// 解凍・凍結権限ありの場合のみ凍結可能
		if (WikiConfig::isUserWithFreezeAuth()){			// 解凍・凍結権限ありの場合
			// ページをロックする
			WikiPage::lockPage($page, true);

			// Update
			is_freeze($page, TRUE);
			WikiParam::setCmd('read');
			$msg  = $_title_freezed;
			$body = '';
		}
//	} else if (!WikiConfig::isPasswordAuth() && !$editAuth){			// パスワード認証以外(管理権限ユーザまたはログインユーザ)の場合で、編集権限がない場合
//		$body = "<p><strong>$_msg_no_operation_allowed</strong></p>\n";
	} else {
		// Show a freeze form
		$msg    = $_title_freeze;
		$s_page = htmlspecialchars($page);
//		$body   = ($pass == '') ? '' : "<p><strong>$_msg_invalidpass</strong></p>\n";
		$postScript = $script . WikiParam::convQuery("?");
		
		// テンプレートタイプに合わせて出力を変更
		// 編集権限ありの場合はパスワードを入力しない
		$templateType = $gEnvManager->getCurrentTemplateType();
		if ($templateType == M3_TEMPLATE_BOOTSTRAP_30){		// Bootstrap型テンプレートの場合
//			if (!$editAuth) $body .= '<p>' . $_msg_freezing . '</p>' . M3_NL;
			$body .= '<form action="' . $postScript . '" method="post" class="form form-inline" role="form">' . M3_NL;
			$body .= '<input type="hidden"   name="wcmd"  value="freeze" />' . M3_NL;
			$body .= '<input type="hidden"   name="page" value="' . $s_page . '" />' . M3_NL;
			$body .= '<input type="hidden" name="action" value="done" />' . M3_NL;		// 次画面遷移用
			
//			$body .= '<input type="hidden"   name="pass" />' . M3_NL;
//			if ($editAuth){
//				$body .= '<input type="hidden" name="password" value="' . $dummy_password . '" />' . M3_NL;
//			} else {
//				$body .= '<div class="form-group"><input type="password" class="form-control" name="password" size="12" /></div>' . M3_NL;
//			}
//			$body .= '<input type="submit"   name="ok"   class="button btn" value="' . $_btn_freeze . '" onclick="this.form.pass.value = hex_md5(this.form.password.value);" />' . M3_NL;
			$body .= '<input type="submit"   name="ok"   class="button btn" value="' . $_btn_freeze . '" />' . M3_NL;
			$body .= '</form>' . M3_NL;
		} else {
//			if (!$editAuth) $body .= '<p>' . $_msg_freezing . '</p>' . M3_NL;
			$body .= '<form action="' . $postScript . '" method="post" class="form">' . M3_NL;
			$body .= '<div>' . M3_NL;
			$body .= '<input type="hidden"   name="wcmd"  value="freeze" />' . M3_NL;
			$body .= '<input type="hidden"   name="page" value="' . $s_page . '" />' . M3_NL;
			$body .= '<input type="hidden" name="action" value="done" />' . M3_NL;		// 次画面遷移用
			
//			$body .= '<input type="hidden"   name="pass" />' . M3_NL;
//			if ($editAuth){
//				$body .= '<input type="hidden" name="password" value="' . $dummy_password . '" />' . M3_NL;
//			} else {
//				$body .= '<input type="password" name="password" size="12" />' . M3_NL;
//			}
//			$body .= '<input type="submit"   name="ok"   class="button" value="' . $_btn_freeze . '" onclick="this.form.pass.value = hex_md5(this.form.password.value);" />' . M3_NL;
			$body .= '<input type="submit"   name="ok"   class="button" value="' . $_btn_freeze . '" />' . M3_NL;
			$body .= '</div>' . M3_NL;
			$body .= '</form>' . M3_NL;
		}
	}

	return array('msg'=>$msg, 'body'=>$body);
}
?>
