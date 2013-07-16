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
 * @version    SVN: $Id: index.php 3836 2010-11-17 06:05:07Z fishbone $
 * @link       http://www.magic3.org
 */
// 直接アクセスの防止
defined('M3_SYSTEM') or die('Access error: Direct access denied.');

global $HELP;

// ########## ブログ設定 ##########
$HELP['config_title']['title'] = 'ブログ設定';
$HELP['config_title']['body'] = 'ブログ機能の携帯用の設定を行います。';
$HELP['config_view_count']['title'] = '記事表示数';
$HELP['config_view_count']['body'] = 'ブログ記事を一覧表示する場合の記事の表示数を設定します。';
$HELP['config_view_order']['title'] = '記事表示順';
$HELP['config_view_order']['body'] = 'ブログ記事を一覧表示する場合の記事の表示順を設定します。';
$HELP['config_title_color']['title'] = 'タイトル背景色';
$HELP['config_title_color']['body'] = 'タイトルの背景色を設定します。';
?>
