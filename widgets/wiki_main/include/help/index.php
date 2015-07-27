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
 * @version    SVN: $Id: index.php 1176 2008-11-05 04:23:39Z fishbone $
 * @link       http://www.magic3.org
 */
// 直接アクセスの防止
defined('M3_SYSTEM') or die('Access error: Direct access denied.');

global $HELP;

$HELP['auth_label']['title'] = '認証方法';
$HELP['auth_label']['body'] = 'Wikiコンテンツのデータの編集を許可するユーザ認証方法を指定します。システム管理者またはシステム運用者は認証方法に関わらず常に編集可能です。<br>「管理権限ユーザ」はシステム管理者またはシステム運用者のみ編集可能です。<br>「ログインユーザ」は管理権限に関係なくログインしたユーザのみ編集可能です。<br>「共通パスワード」はログインに関係なく共通のパスワードを入力したユーザが編集可能です。';
$HELP['default_page_label']['title'] = 'デフォルト画面';
$HELP['default_page_label']['body'] = 'デフォルトで表示するWikiページを指定します。実際に存在するWikiページ名を指定します。';
$HELP['visible_items_label']['title'] = '表示項目';
$HELP['visible_items_label']['body'] = '画面に表示する項目の表示制御を行います。';
$HELP['attach_files_label']['title'] = '添付ファイルアップロードディレクトリ';
$HELP['attach_files_label']['body'] = 'Wikiページに添付するファイルの格納ディレクトリです。ファイルをアップロードするには、ディレクトリの書き込み権限が必要です。';

?>
