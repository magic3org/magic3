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
 * @version    SVN: $Id: index.php 1594 2009-03-19 04:43:33Z fishbone $
 * @link       http://www.magic3.org
 */
// 直接アクセスの防止
defined('M3_SYSTEM') or die('Access error: Direct access denied.');

global $HELP;

// ########## 設定項目一覧 ##########
$HELP['item_list']['title'] = '設定一覧';
$HELP['item_list']['body'] = '登録されている設定の一覧です。';
$HELP['item_detail']['title'] = '設定詳細';
$HELP['item_detail']['body'] = '詳細設定を行います。';
$HELP['item_check']['title'] = '選択用チェックボックス';
$HELP['item_check']['body'] = '削除を行う項目を選択します。';
$HELP['item_name']['title'] = '名前';
$HELP['item_name']['body'] = '設定名です。';
$HELP['item_name_input']['title'] = '名前';
$HELP['item_name_input']['body'] = '設定名です。新規に登録するか、登録済みの設定を選択します。';
$HELP['item_color_type']['title'] = 'カラータイプ';
$HELP['item_color_type']['body'] = 'フォーム部品の基本カラーです。';
$HELP['item_id']['title'] = '設定ID';
$HELP['item_id']['body'] = '自動的に振られる設定IDです。';
$HELP['item_ref']['title'] = '使用';
$HELP['item_ref']['body'] = '設定を使用しているウィジェット数を示します。使用が0の設定のみ削除可能です。';
$HELP['item_list_btn']['title'] = '一覧ボタン';
$HELP['item_list_btn']['body'] = '設定一覧を表示します。';
$HELP['item_del_btn']['title'] = '削除ボタン';
$HELP['item_del_btn']['body'] = '選択されている設定を削除します。<br />項目を選択するには、一覧の左端のチェックボックスにチェックを入れます。';
$HELP['item_ret_btn']['title'] = '戻るボタン';
$HELP['item_ret_btn']['body'] = '設定詳細へ戻ります。';
?>
