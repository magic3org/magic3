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
 * @version    SVN: $Id: index.php 2854 2010-02-14 16:14:17Z fishbone $
 * @link       http://www.magic3.org
 */
// 直接アクセスの防止
defined('M3_SYSTEM') or die('Access error: Direct access denied.');

global $HELP;

// ########## 画像項目一覧 ##########
$HELP['image_list']['title'] = '画像設定一覧';
$HELP['image_list']['body'] = '登録されている画像設定の一覧です。';
$HELP['image_detail']['title'] = '画像設定';
$HELP['image_detail']['body'] = '画像についての設定を行います。';
$HELP['image_preview']['title'] = '画像プレビュー';
$HELP['image_preview']['body'] = '画像のプレビューを表示します。項目の行をマウスクリックして選択します。';
$HELP['image_check']['title'] = '選択用チェックボックス';
$HELP['image_check']['body'] = '削除を行う項目を選択します。';
$HELP['image_name']['title'] = '名前';
$HELP['image_name']['body'] = '画像設定名です。';
$HELP['image_name_input']['title'] = '名前';
$HELP['image_name_input']['body'] = '画像設定名です。新規に登録するか、登録済みの設定を選択します。';
$HELP['image_id']['title'] = '画像設定ID';
$HELP['image_id']['body'] = '自動的に振られる画像設定IDです。';
$HELP['image_file']['title'] = '画像';
$HELP['image_file']['body'] = '表示する画像を指定します。「変更」ボタンを押すと画像が選択できます。';
$HELP['image_url']['title'] = '画像パス';
$HELP['image_url']['body'] = '表示する画像ファイルのパスです。';
$HELP['image_size']['title'] = '画像サイズ';
$HELP['image_size']['body'] = '画像の表示サイズを「px」または「%」で指定します。0を入力すると実際の画像のサイズで表示します。';
$HELP['image_margin']['title'] = '画像マージン';
$HELP['image_margin']['body'] = '画像の周囲のマージンを「px」で指定します。';
$HELP['image_align']['title'] = '表示位置';
$HELP['image_align']['body'] = '画像の表示位置を選択します。';
$HELP['image_pos']['title'] = '座標指定';
$HELP['image_pos']['body'] = '画像の表示位置をより詳細に指定する場合は「有効」にチェックを入れて、相対位置または絶対位置で座標を指定します。';
$HELP['image_bgcolor']['title'] = '背景色';
$HELP['image_bgcolor']['body'] = '画像の背景色を指定します。';
$HELP['image_link']['title'] = 'リンク';
$HELP['image_link']['body'] = '画像をクリックした時、画面を遷移する場合は、「あり」にチェックを入れて「リンク先」を指定します。リンク先にはURLを設定します。また「mailto:」を先頭に付けてEメールアドレスを設定するとメーラが起動できるリンクになります。<br />例) mailto:user@example.com?subject=件名';
$HELP['image_filename']['title'] = 'ファイル名';
$HELP['image_filename']['body'] = '画像のファイル名です。';
$HELP['image_ref']['title'] = '使用';
$HELP['image_ref']['body'] = '画像設定を使用しているウィジェット数を示します。使用が0の画像設定のみ削除可能です。';
$HELP['image_list_btn']['title'] = '一覧ボタン';
$HELP['image_list_btn']['body'] = '画像設定一覧を表示します。';
$HELP['image_del_btn']['title'] = '削除ボタン';
$HELP['image_del_btn']['body'] = '選択されている画像設定を削除します。<br />項目を選択するには、一覧の左端のチェックボックスにチェックを入れます。';
$HELP['image_ret_btn']['title'] = '戻るボタン';
$HELP['image_ret_btn']['body'] = '画像詳細へ戻ります。';
?>
