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
 * @copyright  Copyright 2006-2013 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
// 直接アクセスの防止
defined('M3_SYSTEM') or die('Access error: Direct access denied.');

global $HELP;

// ########## 設定管理 ##########
$HELP['config_list']['title'] = 'カレンダー設定一覧';
$HELP['config_list']['body'] = '登録されているカレンダー設定の一覧です。';
$HELP['config_detail']['title'] = 'カレンダー設定';
$HELP['config_detail']['body'] = 'カレンダーについての設定を行います。';
$HELP['config_check']['title'] = '選択用チェックボックス';
$HELP['config_check']['body'] = '削除を行う項目を選択します。';
$HELP['config_name']['title'] = '名前';
$HELP['config_name']['body'] = 'カレンダー設定名です。';
$HELP['config_name_input']['title'] = '名前';
$HELP['config_name_input']['body'] = 'カレンダー設定名です。新規に登録するか、登録済みの設定を選択します。';
$HELP['config_id']['title'] = 'カレンダー設定ID';
$HELP['config_id']['body'] = '自動的に振られるカレンダー設定IDです。';
$HELP['config_ref']['title'] = '使用';
$HELP['config_ref']['body'] = 'カレンダー設定を使用しているウィジェット数を示します。使用が0のカレンダー設定のみ削除可能です。';
$HELP['config_list_btn']['title'] = '一覧ボタン';
$HELP['config_list_btn']['body'] = 'カレンダー設定一覧を表示します。';
$HELP['config_del_btn']['title'] = '削除ボタン';
$HELP['config_del_btn']['body'] = '選択されているカレンダー設定を削除します。<br />項目を選択するには、一覧の左端のチェックボックスにチェックを入れます。';
$HELP['config_ret_btn']['title'] = '戻るボタン';
$HELP['config_ret_btn']['body'] = 'カレンダー設定へ戻ります。';

?>
