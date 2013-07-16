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
 * @version    SVN: $Id: index.php 3823 2010-11-15 02:17:31Z fishbone $
 * @link       http://www.magic3.org
 */
// 直接アクセスの防止
defined('M3_SYSTEM') or die('Access error: Direct access denied.');

global $HELP;

// ########## ブログ選択カテゴリ ##########
$HELP['list']['title'] = 'ブログ選択カテゴリ設定一覧';
$HELP['list']['body'] = '登録されているブログ選択カテゴリ設定の一覧です。';
$HELP['detail']['title'] = 'ブログ選択カテゴリ設定';
$HELP['detail']['body'] = 'ブログ選択カテゴリについての設定を行います。';
$HELP['check']['title'] = '選択用チェックボックス';
$HELP['check']['body'] = '削除を行う項目を選択します。';
$HELP['name']['title'] = '名前';
$HELP['name']['body'] = 'ブログ選択カテゴリ設定名です。';
$HELP['name_input']['title'] = '名前';
$HELP['name_input']['body'] = 'ブログ選択カテゴリ設定名です。新規に登録するか、登録済みの設定を選択します。';
$HELP['id']['title'] = 'ブログ選択カテゴリ設定ID';
$HELP['id']['body'] = '自動的に振られるブログ選択カテゴリ設定IDです。';
$HELP['category']['title'] = 'カテゴリ';
$HELP['category']['body'] = 'ブログのカテゴリを選択します。';
$HELP['view_count']['title'] = '表示項目数';
$HELP['view_count']['body'] = 'リストの最大表示項目数を設定します。';
$HELP['use_rss']['title'] = 'RSS配信';
$HELP['use_rss']['body'] = 'RSS配信を行うかどうかを設定します。';
$HELP['ref']['title'] = '使用';
$HELP['ref']['body'] = 'ブログ選択カテゴリ設定を使用しているウィジェット数を示します。使用が0のブログ選択カテゴリ設定のみ削除可能です。';
$HELP['list_btn']['title'] = '一覧ボタン';
$HELP['list_btn']['body'] = 'ブログ選択カテゴリ設定一覧を表示します。';
$HELP['del_btn']['title'] = '削除ボタン';
$HELP['del_btn']['body'] = '選択されているブログ選択カテゴリ設定を削除します。<br />項目を選択するには、一覧の左端のチェックボックスにチェックを入れます。';
$HELP['ret_btn']['title'] = '戻るボタン';
$HELP['ret_btn']['body'] = 'ブログ選択カテゴリ詳細へ戻ります。';
?>
