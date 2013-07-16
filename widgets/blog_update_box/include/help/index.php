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
 * @copyright  Copyright 2006-2010 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: index.php 3909 2010-12-28 04:17:52Z fishbone $
 * @link       http://www.magic3.org
 */
// 直接アクセスの防止
defined('M3_SYSTEM') or die('Access error: Direct access denied.');

global $HELP;

// ########## ブログ更新一覧 ##########
$HELP['view_count']['title'] = '表示項目数';
$HELP['view_count']['body'] = 'リストの最大表示項目数を設定します。';
$HELP['use_rss']['title'] = 'RSS配信';
$HELP['use_rss']['body'] = 'RSS配信を行うかどうかを設定します。';
?>
