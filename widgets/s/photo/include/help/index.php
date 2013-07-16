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
 * @copyright  Copyright 2006-2011 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: index.php 4711 2012-02-22 13:20:46Z fishbone $
 * @link       http://www.magic3.org
 */
// 直接アクセスの防止
defined('M3_SYSTEM') or die('Access error: Direct access denied.');

global $HELP;

// ########## 画像ブラウザ ##########
$HELP['imagebrowse_keyword']['title'] = '検索キーワード';
$HELP['imagebrowse_keyword']['body'] = '画像のキーワード検索用の文字列です。語句を「,」区切りで設定します。';

// ########## 画像カテゴリー ##########
$HELP['category_list']['title'] = '画像カテゴリー一覧';
$HELP['category_list']['body'] = '画像のカテゴリー一覧です。画像のカテゴリー分けに使用します。';
$HELP['category_detail']['title'] = '画像カテゴリー詳細';
$HELP['category_detail']['body'] = '画像カテゴリーの情報を編集します。';
$HELP['category_new_btn']['title'] = '新規ボタン';
$HELP['category_new_btn']['body'] = '新規にカテゴリーを追加します。';
$HELP['category_edit_btn']['title'] = '編集ボタン';
$HELP['category_edit_btn']['body'] = '選択されているカテゴリーを編集します。<br>カテゴリーを選択するには、一覧の左端のチェックボックスにチェックを入れます。';
$HELP['category_del_btn']['title'] = '削除ボタン';
$HELP['category_del_btn']['body'] = '選択されているカテゴリーを削除します。<br>カテゴリーを選択するには、一覧の左端のチェックボックスにチェックを入れます。';
$HELP['category_ret_btn']['title'] = '戻るボタン';
$HELP['category_ret_btn']['body'] = 'カテゴリー一覧へ戻ります。';
$HELP['category_check']['title'] = '選択用チェックボックス';
$HELP['category_check']['body'] = '編集や削除を行う項目を選択します。';
$HELP['category_id']['title'] = 'カテゴリーID';
$HELP['category_id']['body'] = 'カテゴリーに自動的に振られるIDです。';
$HELP['category_name']['title'] = 'カテゴリー名';
$HELP['category_name']['body'] = 'カテゴリーの名前です。';
$HELP['category_index']['title'] = '表示順';
$HELP['category_index']['body'] = 'カテゴリーを一覧表示する際の表示順です。';
$HELP['category_visible']['title'] = '公開';
$HELP['category_visible']['body'] = 'カテゴリーをユーザに公開するかどうかを制御します。';

// ########## フォトギャラリー設定 ##########
$HELP['config_title']['title'] = 'フォトギャラリー設定';
$HELP['config_title']['body'] = 'フォトギャラリー全体の設定を行います。';
$HELP['config_view_count']['title'] = '画像表示数';
$HELP['config_view_count']['body'] = 'フォトギャラリー画像を一覧表示する場合の画像の表示数を設定します。';
$HELP['config_view_order']['title'] = '画像表示順';
$HELP['config_view_order']['body'] = 'フォトギャラリー画像を一覧表示する場合の画像の表示順を設定します。';
$HELP['config_category_count']['title'] = 'カテゴリ数';
$HELP['config_category_count']['body'] = '画像に設定可能なカテゴリ数です。';
$HELP['config_receive_comment']['title'] = 'コメント';
$HELP['config_receive_comment']['body'] = 'フォトギャラリー画像に対して、ユーザからのコメントの受付を許可するかどうかを設定します。';
$HELP['config_max_comment_length']['title'] = '最大文字数';
$HELP['config_max_comment_length']['body'] = 'コメントに入力可能な文字数を設定します。0を指定した場合は無制限です。';
$HELP['config_multi_blog_top_content']['title'] = 'マルチフォトギャラリートップコンテンツ';
$HELP['config_multi_blog_top_content']['body'] = 'マルチフォトギャラリーを使用した場合のトップ画面のコンテンツです。';
?>
