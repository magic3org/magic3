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
 * @version    SVN: $Id: index.php 1428 2009-01-10 11:10:54Z fishbone $
 * @link       http://www.magic3.org
 */
// 直接アクセスの防止
defined('M3_SYSTEM') or die('Access error: Direct access denied.');

global $HELP;

// ########## トップアクセスコンテンツ一覧 ##########
$HELP['title']['title'] = 'タイトル';
$HELP['title']['body'] = '一覧のタイトル名です。';
$HELP['view_count']['title'] = '表示項目数';
$HELP['view_count']['body'] = 'リストの最大表示項目数を設定します。';
?>
