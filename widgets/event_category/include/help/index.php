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
 * @copyright  Copyright 2006-2013 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
// 直接アクセスの防止
defined('M3_SYSTEM') or die('Access error: Direct access denied.');

global $HELP;

// ########## イベントカテゴリー ##########
$HELP['list']['title'] = 'イベントカテゴリー設定一覧';
$HELP['list']['body'] = '登録されているイベントカテゴリー設定の一覧です。';
$HELP['detail']['title'] = 'イベントカテゴリー設定';
$HELP['detail']['body'] = 'イベントカテゴリーについての設定を行います。';
$HELP['check']['title'] = '選択用チェックボックス';
$HELP['check']['body'] = '削除を行う項目を選択します。';
$HELP['name']['title'] = '名前';
$HELP['name']['body'] = 'イベントカテゴリー設定名です。';
$HELP['name_input']['title'] = '名前';
$HELP['name_input']['body'] = 'イベントカテゴリー設定名です。新規に登録するか、登録済みの設定を選択します。';
$HELP['id']['title'] = 'イベントカテゴリー設定ID';
$HELP['id']['body'] = '自動的に振られるイベントカテゴリー設定IDです。';
$HELP['category']['title'] = 'カテゴリー';
$HELP['category']['body'] = 'ブログのカテゴリーを選択します。';
$HELP['view_count']['title'] = '表示項目数';
$HELP['view_count']['body'] = 'リストの最大表示項目数を設定します。0を設定した場合はすべての記事を取得します。';
$HELP['use_rss']['title'] = 'RSS配信';
$HELP['use_rss']['body'] = 'RSS配信を行うかどうかを設定します。';
$HELP['ref']['title'] = '使用';
$HELP['ref']['body'] = 'イベントカテゴリー設定を使用しているウィジェット数を示します。使用が0のイベントカテゴリー設定のみ削除可能です。';
$HELP['list_btn']['title'] = '一覧ボタン';
$HELP['list_btn']['body'] = 'イベントカテゴリー設定一覧を表示します。';
$HELP['del_btn']['title'] = '削除ボタン';
$HELP['del_btn']['body'] = '選択されているイベントカテゴリー設定を削除します。<br />項目を選択するには、一覧の左端のチェックボックスにチェックを入れます。';
$HELP['ret_btn']['title'] = '戻るボタン';
$HELP['ret_btn']['body'] = 'イベントカテゴリー詳細へ戻ります。';
?>
