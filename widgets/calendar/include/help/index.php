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
 * @copyright  Copyright 2006-2015 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
// 直接アクセスの防止
defined('M3_SYSTEM') or die('Access error: Direct access denied.');

global $HELP;

// ########## 設定管理 ##########
$HELP['calendar_list']['title'] = 'カレンダー設定一覧';
$HELP['calendar_list']['body'] = '登録されているカレンダー設定の一覧です。';
$HELP['calendar_detail']['title'] = 'カレンダー設定';
$HELP['calendar_detail']['body'] = 'カレンダーについての設定を行います。';
$HELP['calendar_check']['title'] = '選択用チェックボックス';
$HELP['calendar_check']['body'] = '削除を行う項目を選択します。';
$HELP['calendar_name']['title'] = '名前';
$HELP['calendar_name']['body'] = 'カレンダー設定名です。';
$HELP['calendar_name_input']['title'] = '名前';
$HELP['calendar_name_input']['body'] = 'カレンダー設定名です。新規に登録するか、登録済みの設定を選択します。';
$HELP['calendar_id']['title'] = 'カレンダー設定ID';
$HELP['calendar_id']['body'] = '自動的に振られるカレンダー設定IDです。';
$HELP['calendar_ref']['title'] = '使用';
$HELP['calendar_ref']['body'] = 'カレンダー設定を使用しているウィジェット数を示します。使用が0のカレンダー設定のみ削除可能です。';
$HELP['calendar_list_btn']['title'] = '一覧ボタン';
$HELP['calendar_list_btn']['body'] = 'カレンダー設定一覧を表示します。';
$HELP['calendar_del_btn']['title'] = '削除ボタン';
$HELP['calendar_del_btn']['body'] = '選択されているカレンダー設定を削除します。<br />項目を選択するには、一覧の左端のチェックボックスにチェックを入れます。';
$HELP['calendar_ret_btn']['title'] = '戻るボタン';
$HELP['calendar_ret_btn']['body'] = 'カレンダー設定へ戻ります。';

?>
