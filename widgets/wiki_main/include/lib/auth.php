<?php
/**
 * ユーザ認証ライブラリ
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
// Copyright (C) 2003-2005 PukiWiki Developers Team
// License: GPL v2 or (at your option) any later version
//
// Authentication related functions

define('PKWK_PASSPHRASE_LIMIT_LENGTH', 512);

// Passwd-auth related ----

function pkwk_login($pass = '')
{
	// modified for Magic3 by naoki on 2008/10/10
	//global $adminpass;

	/*if (! PKWK_READONLY && isset($adminpass) &&
		pkwk_hash_compute($pass, $adminpass) === $adminpass) {*/
	//if (!PKWK_READONLY && !empty($pass)){		// パスワードチェック
	if (!PKWK_READONLY){		// パスワードチェック
		if (WikiConfig::isUserWithEditAuth()){		// 編集権限ありのとき
			return true;
		} else if (WikiConfig::isPasswordAuth()){		// パスワード認証のとき
			$password = WikiConfig::getPassword();
			if (empty($pass) || empty($password)){
				return false;
			} else if ($pass != $password){
				return false;
			} else {
				WikiConfig::permitPasswordAuth();			// パスワード認証を許可
				return true;
			}
		} else {
			return false;
		}
	} else {
		sleep(2);       // Blocking brute force attack
		return false;
	}
}

// Compute RFC2307 'userPassword' value, like slappasswd (OpenLDAP)
// $phrase : Pass-phrase
// $scheme : Specify '{scheme}' or '{scheme}salt'
// $prefix : Output with a scheme-prefix or not
// $canonical : Correct or Preserve $scheme prefix
function pkwk_hash_compute($phrase = '', $scheme = '{x-php-md5}', $prefix = TRUE, $canonical = FALSE)
{
	if (! is_string($phrase) || ! is_string($scheme)) return FALSE;

	if (strlen($phrase) > PKWK_PASSPHRASE_LIMIT_LENGTH)
		die('pkwk_hash_compute(): malicious message length');

	// With a {scheme}salt or not
	$matches = array();
	if (preg_match('/^(\{.+\})(.*)$/', $scheme, $matches)) {
		$scheme = $matches[1];
		$salt   = $matches[2];
	} else if ($scheme != '') {
		$scheme  = ''; // Cleartext
		$salt    = '';
	}

	// Compute and add a scheme-prefix
	switch (strtolower($scheme)) {

	// PHP crypt()
	case '{x-php-crypt}' :
		$hash = ($prefix ? ($canonical ? '{x-php-crypt}' : $scheme) : '') .
			($salt != '' ? crypt($phrase, $salt) : crypt($phrase));
		break;

	// PHP md5()
	case '{x-php-md5}'   :
		$hash = ($prefix ? ($canonical ? '{x-php-md5}' : $scheme) : '') .
			md5($phrase);
		break;

	// PHP sha1()
	case '{x-php-sha1}'  :
		$hash = ($prefix ? ($canonical ? '{x-php-sha1}' : $scheme) : '') .
			sha1($phrase);
		break;

	// LDAP CRYPT
	case '{crypt}'       :
		$hash = ($prefix ? ($canonical ? '{CRYPT}' : $scheme) : '') .
			($salt != '' ? crypt($phrase, $salt) : crypt($phrase));
		break;

	// LDAP MD5
	case '{md5}'         :
		$hash = ($prefix ? ($canonical ? '{MD5}' : $scheme) : '') .
			base64_encode(hex2bin(md5($phrase)));
		break;

	// LDAP SMD5
	case '{smd5}'        :
		// MD5 Key length = 128bits = 16bytes
		$salt = ($salt != '' ? substr(base64_decode($salt), 16) : substr(crypt(''), -8));
		$hash = ($prefix ? ($canonical ? '{SMD5}' : $scheme) : '') .
			base64_encode(hex2bin(md5($phrase . $salt)) . $salt);
		break;

	// LDAP SHA
	case '{sha}'         :
		$hash = ($prefix ? ($canonical ? '{SHA}' : $scheme) : '') .
			base64_encode(hex2bin(sha1($phrase)));
		break;

	// LDAP SSHA
	case '{ssha}'        :
		// SHA-1 Key length = 160bits = 20bytes
		$salt = ($salt != '' ? substr(base64_decode($salt), 20) : substr(crypt(''), -8));
		$hash = ($prefix ? ($canonical ? '{SSHA}' : $scheme) : '') .
			base64_encode(hex2bin(sha1($phrase . $salt)) . $salt);
		break;

	// LDAP CLEARTEXT and just cleartext
	case '{cleartext}'   : /* FALLTHROUGH */
	case ''              :
		$hash = ($prefix ? ($canonical ? '{CLEARTEXT}' : $scheme) : '') .
			$phrase;
		break;

	// Invalid scheme
	default:
		$hash = FALSE;
		break;
	}

	return $hash;
}


// Basic-auth related ----

// Check edit-permission
function check_editable($page, $auth_flag = TRUE, $exit_flag = TRUE)
{
	global $script, $_title_cannotedit, $_msg_unfreeze;

	if (edit_auth($page, $auth_flag, $exit_flag) && is_editable($page)) {
		// Editable
		return TRUE;
	} else {
		return false;
		// Not editable
		if ($exit_flag === FALSE) {
			return FALSE; // Without exit
		} else {
			// With exit
			$body = $title = str_replace('$1',
				htmlspecialchars(strip_bracket($page)), $_title_cannotedit);
			// modified for Magic3 by naoki on 2008/10/10
			/*if (is_freeze($page))
				$body .= '(<a href="' . $script . '?cmd=unfreeze&amp;page=' .
					rawurlencode($page) . '">' . $_msg_unfreeze . '</a>)';*/
			if (is_freeze($page)){
				$body .= '(<a href="' . $script . WikiParam::convQuery('?cmd=unfreeze&amp;page=' . rawurlencode($page)) . '">' . $_msg_unfreeze . '</a>)';
			}
			$page = str_replace('$1', make_search($page), $_title_cannotedit);
			// modified for Magic3 by naoki on 2008/9/29
			//catbody($title, $page, $body);
			//catbody($body);
			//exit;
		}
	}
}

// Check read-permission
function check_readable($page, $auth_flag = TRUE, $exit_flag = TRUE)
{
	return read_auth($page, $auth_flag, $exit_flag);
}

function edit_auth($page, $auth_flag = TRUE, $exit_flag = TRUE)
{
	global $edit_auth, $edit_auth_pages, $_title_cannotedit;
	return $edit_auth ?  basic_auth($page, $auth_flag, $exit_flag,
		$edit_auth_pages, $_title_cannotedit) : TRUE;
}

function read_auth($page, $auth_flag = TRUE, $exit_flag = TRUE)
{
	global $read_auth, $read_auth_pages, $_title_cannotread;
	return $read_auth ?  basic_auth($page, $auth_flag, $exit_flag,
		$read_auth_pages, $_title_cannotread) : TRUE;
}

// Basic authentication
function basic_auth($page, $auth_flag, $exit_flag, $auth_pages, $title_cannot)
{
	global $auth_method_type, $auth_users, $_msg_auth;

	// Checked by:
	$target_str = '';
	if ($auth_method_type == 'pagename') {
		$target_str = $page; // Page name
	} else if ($auth_method_type == 'contents') {
		$target_str = join('', get_source($page)); // Its contents
	}

	$user_list = array();
	foreach($auth_pages as $key=>$val)
		if (preg_match($key, $target_str))
			$user_list = array_merge($user_list, explode(',', $val));

	if (empty($user_list)) return TRUE; // No limit

	$matches = array();
	if (! isset($_SERVER['PHP_AUTH_USER']) &&
		! isset($_SERVER ['PHP_AUTH_PW']) &&
		isset($_SERVER['HTTP_AUTHORIZATION']) &&
		preg_match('/^Basic (.*)$/', $_SERVER['HTTP_AUTHORIZATION'], $matches))
	{

		// Basic-auth with $_SERVER['HTTP_AUTHORIZATION']
		list($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']) =
			explode(':', base64_decode($matches[1]));
	}

	if (PKWK_READONLY ||
		! isset($_SERVER['PHP_AUTH_USER']) ||
		! in_array($_SERVER['PHP_AUTH_USER'], $user_list) ||
		! isset($auth_users[$_SERVER['PHP_AUTH_USER']]) ||
		pkwk_hash_compute(
			$_SERVER['PHP_AUTH_PW'],
			$auth_users[$_SERVER['PHP_AUTH_USER']]
			) !== $auth_users[$_SERVER['PHP_AUTH_USER']])
	{
		// Auth failed
		pkwk_common_headers();
		if ($auth_flag) {
			header('WWW-Authenticate: Basic realm="' . $_msg_auth . '"');
			header('HTTP/1.0 401 Unauthorized');
		}
		if ($exit_flag) {
			$body = $title = str_replace('$1',
				htmlspecialchars(strip_bracket($page)), $title_cannot);
			$page = str_replace('$1', make_search($page), $title_cannot);
			// modified for Magic3 by naoki on 2008/9/29
			//catbody($title, $page, $body);
			//catbody($body);
			exit;
		}
		return FALSE;
	} else {
		return TRUE;
	}
}
?>
