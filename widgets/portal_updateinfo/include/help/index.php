<?php
/**
 * ヘルプリソースファイル
 * index.php
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    ポータル用コンテンツ更新情報
 * @author     株式会社 毎日メディアサービス
 * @copyright  Copyright 2009 株式会社 毎日メディアサービス.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: index.php 2725 2009-12-21 08:55:42Z fishbone $
 * @link       http://www.m-media.co.jp
 */
// 直接アクセスの防止
defined('M3_SYSTEM') or die('Access error: Direct access denied.');

global $HELP;

// ########## コンテンツ更新情報 ##########
$HELP['news_list']['title'] = '更新情報一覧';
$HELP['news_list']['body'] = '現在表示状態にある更新情報の一覧です。';
$HELP['news_check']['title'] = '選択用チェックボックス';
$HELP['news_check']['body'] = '削除を行う項目を選択します。';
$HELP['news_message']['title'] = 'メッセージ';
$HELP['news_message']['body'] = '表示メッセージです。';
$HELP['news_title']['title'] = 'コンテンツタイトル';
$HELP['news_title']['body'] = '更新されたコンテンツのタイトルです。';
$HELP['news_site']['title'] = 'サイト名';
$HELP['news_site']['body'] = 'コンテンツを更新したサイト名です。';
$HELP['news_dt']['title'] = '更新日時';
$HELP['news_dt']['body'] = 'コンテンツを更新した日時です。';
$HELP['news_del_btn']['title'] = '削除ボタン';
$HELP['news_del_btn']['body'] = '選択されている更新情報を削除します。<br />項目を選択するには、一覧の左端のチェックボックスにチェックを入れます。';
$HELP['news_ret_btn']['title'] = '戻るボタン';
$HELP['news_ret_btn']['body'] = '更新情報一覧へ戻ります。';

// ########## その他の設定 ##########
$HELP['other_view_count']['title'] = '表示項目数';
$HELP['other_view_count']['body'] = 'リストの最大表示項目数を設定します。';
?>
