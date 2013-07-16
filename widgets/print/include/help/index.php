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
 * @copyright  Copyright 2006-2009 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: index.php 1984 2009-06-15 09:07:38Z fishbone $
 * @link       http://www.magic3.org
 */
// 直接アクセスの防止
defined('M3_SYSTEM') or die('Access error: Direct access denied.');

global $HELP;

// ########## 印刷項目一覧 ##########
$HELP['print_list']['title'] = '印刷設定一覧';
$HELP['print_list']['body'] = '登録されている印刷設定の一覧です。';
$HELP['print_detail']['title'] = '印刷設定';
$HELP['print_detail']['body'] = '印刷についての設定を行います。';
$HELP['print_check']['title'] = '選択用チェックボックス';
$HELP['print_check']['body'] = '削除を行う項目を選択します。';
$HELP['print_name']['title'] = '名前';
$HELP['print_name']['body'] = '印刷設定名です。';
$HELP['print_name_input']['title'] = '名前';
$HELP['print_name_input']['body'] = '印刷設定名です。新規に登録するか、登録済みの設定を選択します。';
$HELP['print_tag_id']['title'] = '印刷範囲のタグID';
$HELP['print_tag_id']['body'] = '印刷範囲を示すタグ(例えばdiv)のIDを設定します。';
$HELP['print_ref']['title'] = '使用';
$HELP['print_ref']['body'] = '印刷設定を使用しているウィジェット数を示します。使用が0の印刷設定のみ削除可能です。';
$HELP['print_list_btn']['title'] = '一覧ボタン';
$HELP['print_list_btn']['body'] = '印刷設定一覧を表示します。';
$HELP['print_del_btn']['title'] = '削除ボタン';
$HELP['print_del_btn']['body'] = '選択されている印刷設定を削除します。<br />項目を選択するには、一覧の左端のチェックボックスにチェックを入れます。';
$HELP['print_ret_btn']['title'] = '戻るボタン';
$HELP['print_ret_btn']['body'] = '印刷詳細へ戻ります。';
?>
