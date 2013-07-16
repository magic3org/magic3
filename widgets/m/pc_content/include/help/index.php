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
 * @version    SVN: $Id: index.php 1253 2008-11-19 05:43:26Z fishbone $
 * @link       http://www.magic3.org
 */
// 直接アクセスの防止
defined('M3_SYSTEM') or die('Access error: Direct access denied.');

global $HELP;

// ########## コンテンツ一覧 ##########
$HELP['content']['title'] = 'コンテンツ一覧';
$HELP['content']['body'] = 'コンテンツの一覧です。';
$HELP['content_check']['title'] = '選択用チェックボックス';
$HELP['content_check']['body'] = '編集や削除を行う項目を選択します。';
$HELP['content_id']['title'] = 'コンテンツID';
$HELP['content_id']['body'] = 'コンテンツを識別するためのIDです。新規追加時に自動的に設定されます。';
$HELP['content_name']['title'] = '名前';
$HELP['content_name']['body'] = 'コンテンツの名前です。コンテンツのタイトルとして表示されます。';
$HELP['content_visible']['title'] = '公開';
$HELP['content_visible']['body'] = 'コンテンツをユーザに公開するかどうかを制御します。非公開に設定の場合はユーザから参照することはできません。';
$HELP['content_limited']['title'] = 'ユーザ制限';
$HELP['content_limited']['body'] = 'コンテンツの参照をログインしたユーザに限定するかどうかを設定します。チェックが入っているコンテンツはログインユーザだけが参照可能です。';
$HELP['content_default']['title'] = 'デフォルト項目';
$HELP['content_default']['body'] = 'URLのパラメータでコンテンツIDが指定されていない場合に表示されるコンテンツを指定します。1つだけ設定可能です。';
$HELP['content_update_user']['title'] = '更新者';
$HELP['content_update_user']['body'] = 'コンテンツを更新したユーザです。';
$HELP['content_update_dt']['title'] = '更新日時';
$HELP['content_update_dt']['body'] = 'コンテンツを更新した日時です。';
$HELP['content_view_count']['title'] = '参照数';
$HELP['content_view_count']['body'] = 'コンテンツがユーザに参照された回数です。管理者の参照はカウントされません。';
$HELP['content_act']['title'] = '操作';
$HELP['content_act']['body'] = '各種操作を行います。<br />●メニューに追加<br />「メインメニュー」ウィジェットにコンテンツを表示するメニュー項目を追加します。';
$HELP['content_html']['title'] = 'HTML';
$HELP['content_html']['body'] = 'コンテンツの内容となるHTMLです。';
$HELP['content_ref_custom']['title'] = '置換文字列を参照';
$HELP['content_ref_custom']['body'] = 'コンテンツに埋め込んだ置換文字列はコンテンツ表示時に設定文字列に変換します。置換文字列の設定値を参照します。';
$HELP['content_key']['title'] = '外部参照用キー';
$HELP['content_key']['body'] = '外部ウィジェットからの取得用キーです。';

$HELP['content_new_btn']['title'] = '新規ボタン';
$HELP['content_new_btn']['body'] = '新規コンテンツを追加します。';
$HELP['content_edit_btn']['title'] = '編集ボタン';
$HELP['content_edit_btn']['body'] = '選択されているコンテンツを編集します。<br />コンテンツを選択するには、一覧の左端のチェックボックスにチェックを入れます。';
$HELP['content_del_btn']['title'] = '削除ボタン';
$HELP['content_del_btn']['body'] = '選択されているコンテンツを削除します。<br />コンテンツを選択するには、一覧の左端のチェックボックスにチェックを入れます。';
$HELP['content_ret_btn']['title'] = '戻るボタン';
$HELP['content_ret_btn']['body'] = 'コンテンツ一覧へ戻ります。';
?>
