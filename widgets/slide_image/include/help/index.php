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
 * @copyright  Copyright 2006-2014 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: index.php 1545 2009-03-04 08:42:44Z fishbone $
 * @link       http://www.magic3.org
 */
// 直接アクセスの防止
defined('M3_SYSTEM') or die('Access error: Direct access denied.');

global $HELP;

// ########## 設定項目一覧 ##########
$HELP['image_list']['title'] = '設定一覧';
$HELP['image_list']['body'] = '登録されている設定の一覧です。';
$HELP['image_detail']['title'] = '設定';
$HELP['image_detail']['body'] = '画像表示についての設定を行います。';
$HELP['image_preview']['title'] = 'プレビュー';
$HELP['image_preview']['body'] = 'プレビューを表示します。項目の行をマウスクリックして選択します。';
$HELP['image_check']['title'] = '選択用チェックボックス';
$HELP['image_check']['body'] = '削除を行う項目を選択します。';
$HELP['image_name']['title'] = '名前';
$HELP['image_name']['body'] = '設定名です。';
$HELP['image_name_input']['title'] = '名前';
$HELP['image_name_input']['body'] = '設定名です。新規に登録するか、登録済みの設定を選択します。';
$HELP['image_id']['title'] = '設定ID';
$HELP['image_id']['body'] = '自動的に振られる設定IDです。';
$HELP['image_file']['title'] = '画像';
$HELP['image_file']['body'] = '表示する画像を指定します。<br />「追加」ボタンを押すと画像が追加できます。一覧の「移動」を使用すると、画像の順番が変更できます。';
$HELP['image_size']['title'] = '画像サイズ';
$HELP['image_size']['body'] = 'サムネール画像のサイズを「px」で指定します。';
$HELP['image_filename']['title'] = 'ファイル名';
$HELP['image_filename']['body'] = '画像のファイル名です。';
$HELP['image_ref']['title'] = '使用';
$HELP['image_ref']['body'] = '設定を使用しているウィジェット数を示します。使用が0の設定のみ削除可能です。';
$HELP['image_list_btn']['title'] = '一覧ボタン';
$HELP['image_list_btn']['body'] = '設定一覧を表示します。';
$HELP['image_del_btn']['title'] = '削除ボタン';
$HELP['image_del_btn']['body'] = '選択されている設定を削除します。<br />項目を選択するには、一覧の左端のチェックボックスにチェックを入れます。';
$HELP['image_ret_btn']['title'] = '戻るボタン';
$HELP['image_ret_btn']['body'] = '設定詳細へ戻ります。';
?>
