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
 * @version    SVN: $Id: index.php 5491 2012-12-29 11:16:45Z fishbone $
 * @link       http://www.magic3.org
 */
// 直接アクセスの防止
defined('M3_SYSTEM') or die('Access error: Direct access denied.');

global $HELP;

// ########## 固定コンテンツ ##########
$HELP['item_list']['title'] = '設定一覧';
$HELP['item_list']['body'] = '登録されている設定の一覧です。';
$HELP['item_detail']['title'] = '固定コンテンツ設定';
$HELP['item_detail']['body'] = '固定コンテンツについての設定を行います。';
$HELP['item_check']['title'] = '選択用チェックボックス';
$HELP['item_check']['body'] = '削除を行う項目を選択します。';
$HELP['item_name']['title'] = '名前';
$HELP['item_name']['body'] = '設定名です。';
$HELP['item_content']['title'] = 'コンテンツ';
$HELP['item_content']['body'] = '表示する汎用コンテンツを選択します。「未選択」で「コンテンツを編集」ボタンを押すとコンテンツを新規追加できます。';
$HELP['item_option']['title'] = '表示オプション';
$HELP['item_option']['body'] = 'コンテンツの表示方法を設定します。<br />コンテンツを分割する場合は「「もっと読む」ボタン表示」にチェックを入れます。コンテンツの区切りは、本文の区切り部分に[#CT_BREAK#]を挿入します。';
$HELP['item_list_btn']['title'] = '一覧ボタン';
$HELP['item_list_btn']['body'] = '設定一覧を表示します。';
$HELP['item_del_btn']['title'] = '削除ボタン';
$HELP['item_del_btn']['body'] = '選択されている設定を削除します。<br />項目を選択するには、一覧の左端のチェックボックスにチェックを入れます。';
$HELP['item_ret_btn']['title'] = '戻るボタン';
$HELP['item_ret_btn']['body'] = '固定コンテンツ詳細へ戻ります。';
?>
