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
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
// 直接アクセスの防止
defined('M3_SYSTEM') or die('Access error: Direct access denied.');

global $HELP;

// ########## バナー管理 ##########
$HELP['banner_list']['title'] = 'バナー設定一覧';
$HELP['banner_list']['body'] = '登録されているバナー設定の一覧です。';
$HELP['banner_detail']['title'] = 'バナー設定';
$HELP['banner_detail']['body'] = 'バナーについての設定を行います。';
$HELP['banner_check']['title'] = '選択用チェックボックス';
$HELP['banner_check']['body'] = '削除を行う項目を選択します。';
$HELP['banner_name']['title'] = '名前';
$HELP['banner_name']['body'] = 'バナー設定名です。';
$HELP['banner_name_input']['title'] = '名前';
$HELP['banner_name_input']['body'] = 'バナー設定名です。新規に登録するか、登録済みの設定を選択します。';
$HELP['banner_id']['title'] = 'バナー設定ID';
$HELP['banner_id']['body'] = '自動的に振られるバナー設定IDです。';
$HELP['banner_image']['title'] = '画像';
$HELP['banner_image']['body'] = '設定で使用する画像IDを「,」区切りで指定します。';
$HELP['banner_disp_type']['title'] = '表示方法';
$HELP['banner_disp_type']['body'] = '画像の表示方法を指定します。<br />「表示順」で画像の取得方法を指定します。「順次」または「ランダム」での表示が可能です。<br />「表示方向」で画像の並び方を指定します。<br />「表示項目数」で一度に表示する画像の数を指定します。';
$HELP['banner_preview']['title'] = '画像一覧';
$HELP['banner_preview']['body'] = 'この設定で表示する画像を一覧表示します。';
$HELP['banner_ref']['title'] = '使用';
$HELP['banner_ref']['body'] = 'バナー設定を使用しているウィジェット数を示します。使用が0のバナー設定のみ削除可能です。';
$HELP['banner_list_btn']['title'] = '一覧ボタン';
$HELP['banner_list_btn']['body'] = 'バナー設定一覧を表示します。';
$HELP['banner_del_btn']['title'] = '削除ボタン';
$HELP['banner_del_btn']['body'] = '選択されているバナー設定を削除します。<br />項目を選択するには、一覧の左端のチェックボックスにチェックを入れます。';
$HELP['banner_ret_btn']['title'] = '戻るボタン';
$HELP['banner_ret_btn']['body'] = 'バナー設定へ戻ります。';
$HELP['banner_image_btn']['title'] = '画像変更';
$HELP['banner_image_btn']['body'] = '画像選択用のダイアログを表示します。選択用のチェックボックスにチェックを入れて画像を選択します。項目選択後「確定」ボタンを押すと選択が反映されます。';

// ########## 画像管理 ##########
$HELP['image_list']['title'] = '画像一覧';
$HELP['image_list']['body'] = '登録されている画像の一覧です。';
$HELP['image_detail']['title'] = '画像';
$HELP['image_detail']['body'] = '画像とリンク先の組み合わせを管理します。';
$HELP['image_check']['title'] = '選択用チェックボックス';
$HELP['image_check']['body'] = '編集や削除を行う項目を選択します。';
$HELP['image_name']['title'] = '名前';
$HELP['image_name']['body'] = '画像名です。';
$HELP['image_name_input']['title'] = '名前';
$HELP['image_name_input']['body'] = '画像名です。新規に登録するか、登録済みの設定を選択します。';
$HELP['image_id']['title'] = '画像ID';
$HELP['image_id']['body'] = '自動的に振られる画像IDです。';
$HELP['image_file']['title'] = '画像';
$HELP['image_file']['body'] = '表示する画像を指定します。「変更」ボタンを押すと画像が選択できます。';
$HELP['image_url']['title'] = '画像パス';
$HELP['image_url']['body'] = '表示する画像ファイルのパスです。';
$HELP['image_type']['title'] = '種別';
$HELP['image_type']['body'] = '画像の種別を選択します。画像ファイルまたはFlashファイルが指定できます。';
$HELP['image_visible']['title'] = '公開';
$HELP['image_visible']['body'] = '画像ををユーザに公開するかどうかを制御します。非公開に設定の場合はユーザから参照することはできません。';
$HELP['image_filename']['title'] = 'ファイル名';
$HELP['image_filename']['body'] = '画像ファイル名です。';
$HELP['image_link_url']['title'] = 'リンク先URL';
$HELP['image_link_url']['body'] = '画像をクリックしたときに移動する遷移先のURLです。';
$HELP['image_size']['title'] = '画像サイズ';
$HELP['image_size']['body'] = '画像の表示サイズを指定します。空の場合は実際の画像のサイズで表示されます。';
$HELP['image_image']['title'] = '表示画像';
$HELP['image_image']['body'] = '実際に表示される画像です。';
$HELP['image_layout']['title'] = 'レイアウト';
$HELP['image_layout']['body'] = '画像表示用のレイアウトです。「埋め込みタグ」が画像に変換されます。';
$HELP['image_view_count']['title'] = '閲覧数';
$HELP['image_view_count']['body'] = '画像がユーザに閲覧された回数です。管理者の閲覧はカウントされません。';
$HELP['image_note']['title'] = '管理者用備考';
$HELP['image_note']['body'] = '管理者が自由に利用できる備考欄です。ユーザ向けには表示されません。';
$HELP['image_preview']['title'] = '画像プレビュー';
$HELP['image_preview']['body'] = '画像一覧の項目をプレビューできます。選択するには項目行をマウスクリックします。';
$HELP['image_update_dt']['title'] = '更新日時';
$HELP['image_update_dt']['body'] = '画像情報の更新日時を示します。';
$HELP['image_update_user']['title'] = '更新者';
$HELP['image_update_user']['body'] = '画像情報の更新者を示します。';
$HELP['image_new_btn']['title'] = '新規ボタン';
$HELP['image_new_btn']['body'] = '画像を追加します。';
$HELP['image_edit_btn']['title'] = '編集ボタン';
$HELP['image_edit_btn']['body'] = '選択されている画像項目を編集します。<br>選択するには、一覧の左端のチェックボックスにチェックを入れます。';
$HELP['image_del_btn']['title'] = '削除ボタン';
$HELP['image_del_btn']['body'] = '選択されている画像を削除します。<br />項目を選択するには、一覧の左端のチェックボックスにチェックを入れます。';
$HELP['image_ret_btn']['title'] = '戻るボタン';
$HELP['image_ret_btn']['body'] = '画像一覧へ戻ります。';
?>
