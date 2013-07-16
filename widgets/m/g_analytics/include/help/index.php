<?php
/**
 * ヘルプリソースファイル
 * index.php
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2008 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: index.php 3717 2010-10-19 07:28:14Z fishbone $
 * @link       http://www.magic3.org
 */
// 直接アクセスの防止
defined('M3_SYSTEM') or die('Access error: Direct access denied.');

global $HELP;

// ########## ユーザ一覧 ##########
$HELP['account_no']['title'] = 'アカウント番号';
$HELP['account_no']['body'] = 'Google Analyticsから取得した「xxxx-x」形式のアカウント番号を設定します。アカウント番号はトラッキングコードに埋め込まれます。この値が空の場合はトラッキングコードを出力しません。';
?>
