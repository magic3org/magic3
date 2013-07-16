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
 * @copyright  Copyright 2006-2012 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: index.php 4550 2012-01-02 02:49:16Z fishbone $
 * @link       http://www.magic3.org
 */
// 直接アクセスの防止
defined('M3_SYSTEM') or die('Access error: Direct access denied.');

global $HELP;

// ########## CSS項目一覧 ##########
$HELP['css_list']['title'] = 'CSS設定一覧';
$HELP['css_list']['body'] = '登録されているCSS設定の一覧です。';
$HELP['css_detail']['title'] = 'CSS設定';
$HELP['css_detail']['body'] = 'CSSについての設定を行います。';
$HELP['css_check']['title'] = '選択用チェックボックス';
$HELP['css_check']['body'] = '削除を行う項目を選択します。';
$HELP['css_name']['title'] = '名前';
$HELP['css_name']['body'] = 'CSS設定名です。';
$HELP['css_name_input']['title'] = '名前';
$HELP['css_name_input']['body'] = 'CSS設定名です。新規に登録するか、登録済みの設定を選択します。';
$HELP['css_css']['title'] = 'CSS';
$HELP['css_css']['body'] = 'CSSの設定内容です。HTMLのヘッダ部に出力されます。';
$HELP['css_file']['title'] = 'CSSファイル';
$HELP['css_file']['body'] = 'CSSファイルを読み込む場合にファイルを選択します。CSSディレクトリにファイルをアップロードするとファイル一覧が表示されます。';
$HELP['css_id']['title'] = 'CSS設定ID';
$HELP['css_id']['body'] = '自動的に振られるCSS設定IDです。';
$HELP['css_ref']['title'] = '使用';
$HELP['css_ref']['body'] = 'CSS設定を使用しているウィジェット数を示します。使用が0のCSS設定のみ削除可能です。';
$HELP['css_list_btn']['title'] = '一覧ボタン';
$HELP['css_list_btn']['body'] = 'CSS設定一覧を表示します。';
$HELP['css_del_btn']['title'] = '削除ボタン';
$HELP['css_del_btn']['body'] = '選択されているCSS設定を削除します。<br />項目を選択するには、一覧の左端のチェックボックスにチェックを入れます。';
$HELP['css_ret_btn']['title'] = '戻るボタン';
$HELP['css_ret_btn']['body'] = 'CSS詳細へ戻ります。';
?>
