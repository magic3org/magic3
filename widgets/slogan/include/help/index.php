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

// ########## CSS項目一覧 ##########
$HELP['item_list']['title'] = '設定一覧';
$HELP['item_list']['body'] = '登録されている設定の一覧です。';
$HELP['item_check']['title'] = '選択用チェックボックス';
$HELP['item_check']['body'] = '削除を行う項目を選択します。';
$HELP['item_name']['title'] = '名前';
$HELP['item_name']['body'] = '設定名です。';
$HELP['item_name_input']['title'] = '名前';
$HELP['item_name_input']['body'] = '設定名です。新規に登録するか、登録済みの設定を選択します。';
$HELP['item_css_id']['title'] = 'CSS用ID';
$HELP['item_css_id']['body'] = '表示項目のHTMLタグに設定されるIDです。';
$HELP['item_css']['title'] = 'CSS';
$HELP['item_css']['body'] = 'CSSの設定内容です。';
$HELP['item_message']['title'] = 'メッセージ';
$HELP['item_message']['body'] = '表示するメッセージです。';
$HELP['item_fontsize']['title'] = 'フォントサイズ';
$HELP['item_fontsize']['body'] = 'HTMLの親要素に合わせてフォントサイズを自動的に変化させるための設定です。「コンプレッサー値」でフォントの圧縮拡大率を指定します。「最小サイズ」「最大サイズ」でフォントのサイズ範囲を指定します。';
$HELP['item_id']['title'] = 'CSS設定ID';
$HELP['item_id']['body'] = '自動的に振られるCSS設定IDです。';
$HELP['item_ref']['title'] = '使用';
$HELP['item_ref']['body'] = 'CSS設定を使用しているウィジェット数を示します。使用が0のCSS設定のみ削除可能です。';
$HELP['item_list_btn']['title'] = '一覧ボタン';
$HELP['item_list_btn']['body'] = 'CSS設定一覧を表示します。';
$HELP['item_del_btn']['title'] = '削除ボタン';
$HELP['item_del_btn']['body'] = '選択されているCSS設定を削除します。<br />項目を選択するには、一覧の左端のチェックボックスにチェックを入れます。';
$HELP['item_ret_btn']['title'] = '戻るボタン';
$HELP['item_ret_btn']['body'] = 'CSS詳細へ戻ります。';
?>
