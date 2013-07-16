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
 * @copyright  Copyright 2006-2008 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: index.php 1410 2009-01-02 14:32:20Z fishbone $
 * @link       http://www.magic3.org
 */
// 直接アクセスの防止
defined('M3_SYSTEM') or die('Access error: Direct access denied.');

global $HELP;

// ########## メインメニュー項目一覧 ##########
$HELP['menu_list']['title'] = 'メニュー設定一覧';
$HELP['menu_list']['body'] = '登録されているメニュー設定の一覧です。';
$HELP['menu_detail']['title'] = 'メニュー設定';
$HELP['menu_detail']['body'] = 'メニューについての設定を行います。';
$HELP['menu_check']['title'] = '選択用チェックボックス';
$HELP['menu_check']['body'] = '削除を行う項目を選択します。';
$HELP['menu_name']['title'] = '名前';
$HELP['menu_name']['body'] = 'メニュー設定名です。メニューのタイトルにも使用されます。';
$HELP['menu_name_input']['title'] = '名前';
$HELP['menu_name_input']['body'] = 'メニュー設定名です。新規に登録するか、登録済みの設定を選択します。';
$HELP['menu_id']['title'] = 'メニュー設定ID';
$HELP['menu_id']['body'] = '自動的に振られるメニュー設定IDです。';
$HELP['menu_show_title']['title'] = 'タイトル表示';
$HELP['menu_show_title']['body'] = 'メニューにタイトルを表示するかどうかを設定します。タイトルは「名前」が使用されます。';
$HELP['menu_limit_user']['title'] = 'ユーザ制限';
$HELP['menu_limit_user']['body'] = 'メニューの表示をログインしたユーザに限定するかどうかを設定します。';
$HELP['menu_css']['title'] = 'CSS';
$HELP['menu_css']['body'] = 'メニューデザインカスタマイズ用のCSSです。';
$HELP['menu_css_id']['title'] = 'CSS用ID';
$HELP['menu_css_id']['body'] = 'CSS定義のためのIDです。';
$HELP['menu_def']['title'] = 'メニュー定義';
$HELP['menu_def']['body'] = 'メニュー項目で使用するメニュー定義を選択します。定義データはシステムで共通です。<br />メニュー定義は、メニューのヘッダ部を表示する複数の「フォルダ」とその子要素のリンク項目の「リンク」の２階層で定義します。';
$HELP['menu_def_name']['title'] = 'メニュー定義';
$HELP['menu_def_name']['body'] = 'メニュー項目で使用しているメニュー定義です。定義データはシステムで共通です。';
$HELP['menu_ref']['title'] = '使用';
$HELP['menu_ref']['body'] = 'メニュー設定を使用しているウィジェット数を示します。使用が0のメニュー設定のみ削除可能です。';
$HELP['menu_list_btn']['title'] = '一覧ボタン';
$HELP['menu_list_btn']['body'] = 'メニュー設定一覧を表示します。';
$HELP['menu_del_btn']['title'] = '削除ボタン';
$HELP['menu_del_btn']['body'] = '選択されているメニュー設定を削除します。<br />項目を選択するには、一覧の左端のチェックボックスにチェックを入れます。';
$HELP['menu_ret_btn']['title'] = '戻るボタン';
$HELP['menu_ret_btn']['body'] = 'メニュー設定へ戻ります。';
$HELP['menu_edit_def_btn']['title'] = 'メニュー定義を編集ボタン';
$HELP['menu_edit_def_btn']['body'] = '選択されているメニュー定義を編集します。';
$HELP['menu_reload_btn']['title'] = '再取得ボタン';
$HELP['menu_reload_btn']['body'] = 'データを再取得します。';
?>
