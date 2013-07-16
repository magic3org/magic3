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
 * @copyright  Copyright 2006-2010 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: index.php 5526 2013-01-05 15:31:11Z fishbone $
 * @link       http://www.magic3.org
 */
// 直接アクセスの防止
defined('M3_SYSTEM') or die('Access error: Direct access denied.');

global $HELP;

// ########## 設定項目一覧 ##########
$HELP['untitledtask_list']['title'] = '設定一覧';
$HELP['untitledtask_list']['body'] = '登録されている設定の一覧です。';
$HELP['untitledtask_detail']['title'] = '設定';
$HELP['untitledtask_detail']['body'] = 'リスト表示する動画の設定を行います。';
$HELP['untitledtask_preview']['title'] = 'プレビュー';
$HELP['untitledtask_preview']['body'] = 'プレビューを表示します。項目の行をマウスクリックして選択します。';
$HELP['untitledtask_check']['title'] = '選択用チェックボックス';
$HELP['untitledtask_check']['body'] = '削除を行う項目を選択します。';
$HELP['untitledtask_name']['title'] = '名前';
$HELP['untitledtask_name']['body'] = '設定名です。';
$HELP['untitledtask_name_input']['title'] = '名前';
$HELP['untitledtask_name_input']['body'] = '設定名です。新規に登録するか、登録済みの設定を選択します。';
$HELP['untitledtask_id']['title'] = '設定ID';
$HELP['untitledtask_id']['body'] = '自動的に振られる設定IDです。';
$HELP['untitledtask_content_list']['title'] = 'コンテンツリスト';
$HELP['untitledtask_content_list']['body'] = 'コンテンツのリストを作成します。<br />「コンテンツID」に汎用コンテンツのIDを設定します。';
$HELP['untitledtask_option']['title'] = '表示オプション';
$HELP['untitledtask_option']['body'] = 'コンテンツの表示方法を設定します。<br />コンテンツを分割する場合は「「もっと読む」ボタン表示」にチェックを入れます。コンテンツの区切りは、本文の区切り部分に[#CT_BREAK#]を挿入します。';
$HELP['untitledtask_ref']['title'] = '使用';
$HELP['untitledtask_ref']['body'] = '設定を使用しているウィジェット数を示します。使用が0の設定のみ削除可能です。';
$HELP['untitledtask_list_btn']['title'] = '一覧ボタン';
$HELP['untitledtask_list_btn']['body'] = '設定一覧を表示します。';
$HELP['untitledtask_del_btn']['title'] = '削除ボタン';
$HELP['untitledtask_del_btn']['body'] = '選択されている設定を削除します。<br />項目を選択するには、一覧の左端のチェックボックスにチェックを入れます。';
$HELP['untitledtask_ret_btn']['title'] = '戻るボタン';
$HELP['untitledtask_ret_btn']['body'] = '設定詳細へ戻ります。';
$HELP['untitledtask_preview_btn']['title'] = 'プレビューボタン';
$HELP['untitledtask_preview_btn']['body'] = '実際の画面を表示します。';
?>
