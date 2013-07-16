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
 * @version    SVN: $Id: index.php 2319 2009-09-16 01:10:12Z fishbone $
 * @link       http://www.magic3.org
 */
// 直接アクセスの防止
defined('M3_SYSTEM') or die('Access error: Direct access denied.');

global $HELP;

// ########## 設定項目一覧 ##########
$HELP['contactus_list']['title'] = '設定一覧';
$HELP['contactus_list']['body'] = '登録されている設定の一覧です。';
$HELP['contactus_detail']['title'] = '設定';
$HELP['contactus_detail']['body'] = 'お問い合わせについての設定を行います。';
$HELP['contactus_preview']['title'] = 'プレビュー';
$HELP['contactus_preview']['body'] = 'プレビューを表示します。項目の行をマウスクリックして選択します。';
$HELP['contactus_check']['title'] = '選択用チェックボックス';
$HELP['contactus_check']['body'] = '削除を行う項目を選択します。';
$HELP['contactus_name']['title'] = '名前';
$HELP['contactus_name']['body'] = '設定名です。';
$HELP['contactus_name_input']['title'] = '名前';
$HELP['contactus_name_input']['body'] = '設定名です。新規に登録するか、登録済みの設定を選択します。';
$HELP['contactus_id']['title'] = '設定ID';
$HELP['contactus_id']['body'] = '自動的に振られる設定IDです。';
$HELP['contactus_title']['title'] = 'トップタイトル';
$HELP['contactus_title']['body'] = '先頭の位置に表示するタイトルを設定します。';
$HELP['contactus_explanation']['title'] = '説明';
$HELP['contactus_explanation']['body'] = 'トップタイトルの下に位置する説明を設定します。';
$HELP['contactus_field']['title'] = 'お問い合わせ項目';
$HELP['contactus_field']['body'] = 'お問い合わせ項目を定義します。「定義」の記述方法は以下の通りです。(m,nは数値、str,valは文字列を示します。)<br />●テキストボックス<br />「size=m」でフィールドサイズを設定します。<br />●テキストエリア<br />「rows=m;cols=n」で行、列数を設定します。<br />●セレクトメニュー,チェックボックス,ラジオボタン<br />「str1;str2;str3;...」<br />表示値、送信値が異なる場合は「str1=val1;str2=val2;str3=val3;...」';
$HELP['contactus_email']['title'] = 'メール送信';
$HELP['contactus_email']['body'] = 'お問い合わせメールの件名と送信先メールアドレスを設定します。メールアドレスが空の場合は基本情報のメールアドレスへ送信されます。';
$HELP['contactus_ref']['title'] = '使用';
$HELP['contactus_ref']['body'] = '設定を使用しているウィジェット数を示します。使用が0の設定のみ削除可能です。';
$HELP['contactus_list_btn']['title'] = '一覧ボタン';
$HELP['contactus_list_btn']['body'] = '設定一覧を表示します。';
$HELP['contactus_del_btn']['title'] = '削除ボタン';
$HELP['contactus_del_btn']['body'] = '選択されている設定を削除します。<br />項目を選択するには、一覧の左端のチェックボックスにチェックを入れます。';
$HELP['contactus_ret_btn']['title'] = '戻るボタン';
$HELP['contactus_ret_btn']['body'] = '設定詳細へ戻ります。';
$HELP['contactus_preview_btn']['title'] = 'プレビューボタン';
$HELP['contactus_preview_btn']['body'] = '実際の画面を表示します。';
?>
