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
 * @version    SVN: $Id: index.php 3124 2010-05-13 05:41:33Z fishbone $
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
$HELP['menu_limit_user']['title'] = 'ユーザ制限';
$HELP['menu_limit_user']['body'] = 'メニューの表示をログインしたユーザに限定するかどうかを設定します。';
$HELP['menu_type']['title'] = 'メニュータイプ';
$HELP['menu_type']['body'] = 'メニュータイプを設定します。<br />●スライド<br />メニュー項目ごとにメニューの開閉を制御します。<br />●アコーディオン<br />１つだけメニュー項目が開いた状態になります。<br />●オープン<br />全メニュー項目を表示します。';
$HELP['menu_default_no']['title'] = 'デフォルトの選択項目';
$HELP['menu_default_no']['body'] = '初期表示時に開くメニュー項目を1つ選択できます。選択する項目は並び番号で指定します。0を設定した場合はメニュー項目がすべて閉じた状態でメニューが表示されます。';
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
?>
