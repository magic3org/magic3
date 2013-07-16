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
 * @version    SVN: $Id: index.php 4629 2012-01-29 06:59:06Z fishbone $
 * @link       http://www.magic3.org
 */
// 直接アクセスの防止
defined('M3_SYSTEM') or die('Access error: Direct access denied.');

global $HELP;

$HELP['send_message']['title'] = 'メール機能の使用';
$HELP['send_message']['body'] = 'サーバからのメール送信を使用可能にします。使用不可の場合は、ユーザ画面の送信ボタンが使用不可(非アクティブ)になります。';
$HELP['email_receiver']['title'] = '受信用メールアドレス';
$HELP['email_receiver']['body'] = 'メール受付用のメールアドレスを指定します。空の場合は基本情報で設定したE-mailアドレスに送信されます。';
$HELP['title_visible']['title'] = 'タイトル';
$HELP['title_visible']['body'] = 'トップ位置に表示するタイトル名です。';
$HELP['name_visible']['title'] = '入力フィールド - 名前';
$HELP['name_visible']['body'] = 'ユーザ画面に名前の入力フィールドを表示します。';
$HELP['name_kana_visible']['title'] = '入力フィールド - フリガナ';
$HELP['name_kana_visible']['body'] = 'ユーザ画面にフリガナの入力フィールドを表示します。';
$HELP['email_visible']['title'] = '入力フィールド - Eメール';
$HELP['email_visible']['body'] = 'ユーザ画面にEメールの入力フィールドを表示します。';
$HELP['company_visible']['title'] = '入力フィールド - 会社名';
$HELP['company_visible']['body'] = 'ユーザ画面に会社名の入力フィールドを表示します。';
$HELP['zipcode_visible']['title'] = '入力フィールド - 郵便番号';
$HELP['zipcode_visible']['body'] = 'ユーザ画面に郵便番号の入力フィールドを表示します。';
$HELP['state_visible']['title'] = '入力フィールド - 都道府県';
$HELP['state_visible']['body'] = 'ユーザ画面に都道府県の入力フィールドを表示します。';
$HELP['address_visible']['title'] = '入力フィールド - 住所';
$HELP['address_visible']['body'] = 'ユーザ画面に住所の入力フィールドを表示します。';
$HELP['tel_visible']['title'] = '入力フィールド - 電話番号';
$HELP['tel_visible']['body'] = 'ユーザ画面に電話番号の入力フィールドを表示します。';
$HELP['body_visible']['title'] = '入力フィールド - 内容';
$HELP['body_visible']['body'] = 'ユーザ画面に内容の入力フィールドを表示します。';
$HELP['explanation']['title'] = '説明';
$HELP['explanation']['body'] = 'ユーザ画面に表示する説明やメッセージを設定します。タイトルの下に表示されます。';
?>
