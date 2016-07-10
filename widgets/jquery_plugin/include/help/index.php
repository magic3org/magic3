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
 * @copyright  Copyright 2006-2016 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
// 直接アクセスの防止
defined('M3_SYSTEM') or die('Access error: Direct access denied.');

global $HELP;

// ########## jQueryプラグイン項目一覧 ##########
$HELP['jquery_list']['title'] = 'jQueryプラグイン設定一覧';
$HELP['jquery_list']['body'] = '登録されているjQueryプラグイン設定の一覧です。';
$HELP['jquery_detail']['title'] = 'jQueryプラグイン設定';
$HELP['jquery_detail']['body'] = 'jQueryプラグインについての設定を行います。';
$HELP['jquery_check']['title'] = '選択用チェックボックス';
$HELP['jquery_check']['body'] = '削除を行う項目を選択します。';
$HELP['jquery_name']['title'] = '名前';
$HELP['jquery_name']['body'] = 'jQueryプラグインの設定名です。';
$HELP['jquery_name_input']['title'] = '名前';
$HELP['jquery_name_input']['body'] = 'jQueryプラグインの設定名です。新規に登録するか、登録済みの設定を選択します。';
$HELP['jquery_plugin']['title'] = '追加プラグイン';
$HELP['jquery_plugin']['body'] = '追加するjQueryプラグインを選択します。';
$HELP['jquery_script']['title'] = 'Javascript';
$HELP['jquery_script']['body'] = 'HTMLのHEADタグ内に設定されるJavascriptです。';
$HELP['jquery_id']['title'] = 'jQueryプラグインの設定ID';
$HELP['jquery_id']['body'] = '自動的に振られるjQueryプラグインの設定IDです。';
$HELP['jquery_ref']['title'] = '使用';
$HELP['jquery_ref']['body'] = 'jQueryプラグイン設定を使用しているウィジェット数を示します。使用が0のjQueryプラグイン設定のみ削除可能です。';
$HELP['jquery_list_btn']['title'] = '一覧ボタン';
$HELP['jquery_list_btn']['body'] = 'jQueryプラグイン設定一覧を表示します。';
$HELP['jquery_del_btn']['title'] = '削除ボタン';
$HELP['jquery_del_btn']['body'] = '選択されているjQueryプラグイン設定を削除します。<br />項目を選択するには、一覧の左端のチェックボックスにチェックを入れます。';
$HELP['jquery_ret_btn']['title'] = '戻るボタン';
$HELP['jquery_ret_btn']['body'] = 'jQueryプラグイン設定へ戻ります。';
?>
