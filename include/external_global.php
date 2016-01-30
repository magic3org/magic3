<?php
/**
 * 外部起動用グローバル定義ファイル
 *
 * 機能：ユーザが編集不可のグローバル定義。共通クラスの取り込み用。
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2016 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    Release 2.15.x SVN: $Id$
 * @link       http://www.magic3.org
 */
//echo "############" .posix_getpwuid(posix_geteuid())['name'];
//echo "############" .trim(shell_exec('whoami'));

// ########## Magic3アクセス制御(開始) ##########
require_once(dirname(__FILE__) . '/global.php');

if (!$gAccessManager->isExternalPermittedUser()){		// rootのみアクセスを許可
	echo 'Access error: access denied.';

	$gOpeLogManager->writeUserAccess(__METHOD__, '外部起動インターフェイスへの不正なアクセスを検出しました。root以外のユーザ。', 3001, 'アクセスをブロックしました。');
	exit(0);
}
// ########## Magic3アクセス制御(終了) ##########
?>
