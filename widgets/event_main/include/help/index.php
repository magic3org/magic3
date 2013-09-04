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
 * @version    SVN: $Id: index.php 3978 2011-02-04 05:34:23Z fishbone $
 * @link       http://www.magic3.org
 */
// 直接アクセスの防止
defined('M3_SYSTEM') or die('Access error: Direct access denied.');

global $HELP;

// ########## イベント記事 ##########
$HELP['entry_list']['title'] = 'イベント記事一覧';
$HELP['entry_list']['body'] = '登録されているイベント記事の一覧です。';
$HELP['entry_detail']['title'] = 'イベント記事詳細';
$HELP['entry_detail']['body'] = 'イベント記事についての設定を行います。';
$HELP['entry_search']['title'] = 'イベント記事検索';
$HELP['entry_search']['body'] = 'イベント記事を検索します。';
$HELP['entry_check']['title'] = '選択用チェックボックス';
$HELP['entry_check']['body'] = '編集、削除を行う項目を選択します。';
$HELP['entry_name']['title'] = 'タイトル';
$HELP['entry_name']['body'] = 'イベント記事のタイトルです。';
$HELP['entry_id']['title'] = 'ID';
$HELP['entry_id']['body'] = 'イベント記事に自動的に振られるIDです。';
$HELP['entry_status']['title'] = '公開';
$HELP['entry_status']['body'] = 'イベント記事の状態を示します。「公開」はユーザから閲覧できる状態です。「非公開」はユーザから閲覧できない状態です。「編集中」は記事が編集中でユーザから閲覧できない状態です。';
$HELP['entry_category']['title'] = 'カテゴリー';
$HELP['entry_category']['body'] = 'イベント記事の分類カテゴリーです。';
$HELP['entry_user']['title'] = '投稿者';
$HELP['entry_user']['body'] = 'イベント記事の投稿者です。';
$HELP['entry_blogid']['title'] = '所属イベント';
$HELP['entry_blogid']['body'] = 'マルチイベントの場合の所属イベントです。';
$HELP['entry_dt']['title'] = '投稿日時';
$HELP['entry_dt']['body'] = 'イベントの開催日時です。<br />時間を設定しない場合は「終日」にチェックを入れます。';
$HELP['entry_active_term']['title'] = '公開期間';
$HELP['entry_active_term']['body'] = 'イベント記事をユーザに公開する期間を設定します。空の場合は制限なしを示します。';
$HELP['entry_view_count']['title'] = '閲覧数';
$HELP['entry_view_count']['body'] = 'イベント記事の閲覧数です。管理権限ユーザの閲覧はカウントされません。';
$HELP['entry_content']['title'] = '投稿内容';
$HELP['entry_content']['body'] = 'イベントの内容です。「予定」にイベントの内容を記述します。「結果」にイベントの結果を記述します。';
$HELP['entry_search_keyword']['title'] = 'キーワード';
$HELP['entry_search_keyword']['body'] = 'イベント記事を検索するキーワードを設定します。検索対象は記事タイトルと本文です。';
$HELP['entry_search_category']['title'] = 'カテゴリー';
$HELP['entry_search_category']['body'] = '記事をカテゴリーで絞り込みます。';
$HELP['entry_search_dt']['title'] = '期間';
$HELP['entry_search_dt']['body'] = '記事を投稿日時の期間で絞り込みます。';
$HELP['entry_new_btn']['title'] = '新規ボタン';
$HELP['entry_new_btn']['body'] = '新規記事を追加します。';
$HELP['entry_edit_btn']['title'] = '編集ボタン';
$HELP['entry_edit_btn']['body'] = '選択されている記事を編集します。<br>項目を選択するには、一覧の左端のチェックボックスにチェックを入れます。';
$HELP['entry_del_btn']['title'] = '削除ボタン';
$HELP['entry_del_btn']['body'] = '選択されている記事を削除します。<br />項目を選択するには、一覧の左端のチェックボックスにチェックを入れます。';
$HELP['entry_ret_btn']['title'] = '戻るボタン';
$HELP['entry_ret_btn']['body'] = 'イベント記事一覧へ戻ります。';

// ########## コメント ##########
$HELP['comment_list']['title'] = 'コメント一覧';
$HELP['comment_list']['body'] = '投稿されたコメントの一覧です。';
$HELP['comment_detail']['title'] = 'コメント詳細';
$HELP['comment_detail']['body'] = 'コメントについての設定を行います。';
$HELP['comment_search']['title'] = 'コメント検索';
$HELP['comment_search']['body'] = 'コメントを検索します。';
$HELP['comment_check']['title'] = '選択用チェックボックス';
$HELP['comment_check']['body'] = '編集、削除を行う項目を選択します。';
$HELP['comment_entry_name']['title'] = '記事タイトル';
$HELP['comment_entry_name']['body'] = 'イベント記事のタイトルです。';
$HELP['comment_name']['title'] = 'コメントタイトル';
$HELP['comment_name']['body'] = 'コメントのタイトルです。';
$HELP['comment_user']['title'] = '投稿者';
$HELP['comment_user']['body'] = 'コメントの投稿者です。';
$HELP['comment_dt']['title'] = '投稿日時';
$HELP['comment_dt']['body'] = 'コメントの投稿日時です。';
$HELP['comment_content']['title'] = 'コメント内容';
$HELP['comment_content']['body'] = 'コメントの内容です。';
$HELP['comment_email']['title'] = 'Eメール';
$HELP['comment_email']['body'] = 'コメントに付加したEメールです。';
$HELP['comment_url']['title'] = 'URL';
$HELP['comment_url']['body'] = 'コメントに付加した参照用URLです。';
$HELP['comment_search_keyword']['title'] = 'キーワード';
$HELP['comment_search_keyword']['body'] = 'コメントを検索するキーワードを設定します。検索対象はコメントタイトルと本文です。';
$HELP['comment_search_dt']['title'] = '期間';
$HELP['comment_search_dt']['body'] = 'コメントを投稿日時の期間で絞り込みます。';
$HELP['comment_edit_btn']['title'] = '編集ボタン';
$HELP['comment_edit_btn']['body'] = '選択されているコメントを編集します。<br>項目を選択するには、一覧の左端のチェックボックスにチェックを入れます。';
$HELP['comment_del_btn']['title'] = '削除ボタン';
$HELP['comment_del_btn']['body'] = '選択されているコメントを削除します。<br />項目を選択するには、一覧の左端のチェックボックスにチェックを入れます。';
$HELP['comment_ret_btn']['title'] = '戻るボタン';
$HELP['comment_ret_btn']['body'] = 'コメント一覧へ戻ります。';

// ########## イベントカテゴリー ##########
$HELP['category_list']['title'] = 'カテゴリー一覧';
$HELP['category_list']['body'] = 'カテゴリー一覧です。イベント記事のカテゴリー分けに使用します。';
$HELP['category_detail']['title'] = 'カテゴリー詳細';
$HELP['category_detail']['body'] = 'カテゴリーの情報を編集します。';
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
$HELP['category_id']['body'] = 'カテゴリーの識別に使用するIDです。半角英数で設定します。';
$HELP['category_name']['title'] = 'カテゴリー名';
$HELP['category_name']['body'] = 'カテゴリーの名前です。';
$HELP['category_index']['title'] = '表示順';
$HELP['category_index']['body'] = 'カテゴリーを一覧表示する際の表示順です。';
$HELP['category_visible']['title'] = '公開';
$HELP['category_visible']['body'] = 'カテゴリーをユーザに公開するかどうかを制御します。';

// ########## イベント設定 ##########
$HELP['config_title']['title'] = 'イベント設定';
$HELP['config_title']['body'] = 'イベント全体の設定を行います。';
$HELP['config_view_count']['title'] = '記事表示数';
$HELP['config_view_count']['body'] = 'イベント記事を一覧表示する場合の記事の表示数を設定します。';
$HELP['config_view_order']['title'] = '記事表示順';
$HELP['config_view_order']['body'] = 'イベント記事を一覧表示する場合の記事の表示順を設定します。';
$HELP['config_category_count']['title'] = 'カテゴリ数';
$HELP['config_category_count']['body'] = '記事に設定可能なカテゴリ数です。';
$HELP['config_receive_comment']['title'] = 'コメント';
$HELP['config_receive_comment']['body'] = 'イベント記事に対して、ユーザからのコメントの受付を許可するかどうかを設定します。';
$HELP['config_max_comment_length']['title'] = '最大文字数';
$HELP['config_max_comment_length']['body'] = 'コメントに入力可能な文字数を設定します。0を指定した場合は無制限です。';
$HELP['config_top_contents']['title'] = 'トップコンテンツ';
$HELP['config_top_contents']['body'] = 'トップ画面のコンテンツです。';
?>
