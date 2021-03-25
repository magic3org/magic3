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
 * @copyright  Copyright 2006-2021 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
// 直接アクセスの防止
defined('M3_SYSTEM') or die('Access error: Direct access denied.');

global $HELP;

// ########## ブログ記事 ##########
$HELP['entry_list']['title'] = 'ブログ記事一覧';
$HELP['entry_list']['body'] = '登録されているブログ記事の一覧です。';
$HELP['entry_detail']['title'] = 'ブログ記事詳細';
$HELP['entry_detail']['body'] = 'ブログ記事についての設定を行います。';
$HELP['entry_search']['title'] = 'ブログ記事検索';
$HELP['entry_search']['body'] = 'ブログ記事を検索します。';
$HELP['entry_check']['title'] = '選択用チェックボックス';
$HELP['entry_check']['body'] = '編集、削除を行う項目を選択します。';
$HELP['entry_name']['title'] = 'タイトル';
$HELP['entry_name']['body'] = 'ブログ記事のタイトルです。';
$HELP['entry_id']['title'] = 'ID';
$HELP['entry_id']['body'] = 'ブログ記事に自動的に振られるIDです。';
$HELP['entry_status']['title'] = '閲覧状態';
$HELP['entry_status']['body'] = 'ブログ記事の現在の閲覧状態を示します。';
$HELP['entry_visible_status']['title'] = '公開状態';
$HELP['entry_visible_status']['body'] = 'ブログ記事の状態を示します。「公開する」はユーザから閲覧できる状態です。「公開しない」はユーザから閲覧できない状態です。「編集中」は記事が編集中でユーザから閲覧できない状態です。';
$HELP['entry_category']['title'] = 'カテゴリー';
$HELP['entry_category']['body'] = 'ブログ記事の分類カテゴリーです。';
$HELP['entry_user']['title'] = '投稿者';
$HELP['entry_user']['body'] = 'ブログ記事の投稿者です。';
$HELP['entry_blogid']['title'] = '所属ブログ';
$HELP['entry_blogid']['body'] = 'マルチブログの場合の所属ブログです。';
$HELP['entry_dt']['title'] = '投稿日時';
$HELP['entry_dt']['body'] = 'ブログ記事の投稿日時です。<br />投稿日時を未来に設定した場合は、その日時までユーザからは閲覧できません。';
$HELP['entry_active_term']['title'] = '公開期間';
$HELP['entry_active_term']['body'] = 'ブログ記事をユーザに公開する期間を設定します。空の場合は制限なしを示します。';
$HELP['entry_view_count']['title'] = '閲覧数';
$HELP['entry_view_count']['body'] = 'ブログ記事の閲覧数です。()内は新規作成からの閲覧数で、()なしは更新後からの閲覧数です。管理権限ユーザの閲覧はカウントされません。';
$HELP['entry_content']['title'] = '投稿内容';
$HELP['entry_content']['body'] = 'ブログ記事の内容です。「本文1」にブログ記事を記述します。ブログ記事が長い場合は、省略記事を「本文1」全文を「本文2」に記述して、「本文1」から「続きを読む」で「本文2」を表示させることができます。';
$HELP['entry_desc']['title'] = '簡易説明';
$HELP['entry_desc']['body'] = 'ブログ記事の概要を設定します。';
$HELP['entry_meta_description']['title'] = 'ページ要約';
$HELP['entry_meta_description']['body'] = 'ヘッダ部のdescriptionタグに設定される文字列です。120文字程度で記述します。<br />Googleでは検索結果に表示されます。';
$HELP['entry_meta_keywords']['title'] = '検索キーワード';
$HELP['entry_meta_keywords']['body'] = 'ヘッダ部のkeywordsタグに設定される文字列です。検索エンジン用のキーワードを「,」区切りで10個以下で記述します。';
$HELP['entry_related_content']['title'] = '関連コンテンツ';
$HELP['entry_related_content']['body'] = '関連するブログ記事のIDを「,」区切りで設定します。';
$HELP['entry_search_keyword']['title'] = 'キーワード';
$HELP['entry_search_keyword']['body'] = 'ブログ記事を検索するキーワードを設定します。検索対象は記事タイトルと本文です。';
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
$HELP['entry_ret_btn']['body'] = 'ブログ記事一覧へ戻ります。';
$HELP['entry_preview_btn']['title'] = 'プレビューボタン';
$HELP['entry_preview_btn']['body'] = 'コンテンツを表示した実際の画面です。';

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
$HELP['comment_entry_name']['body'] = 'ブログ記事のタイトルです。';
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

// ########## ブログカテゴリー ##########
$HELP['category_list']['title'] = 'カテゴリー一覧';
$HELP['category_list']['body'] = 'カテゴリー一覧です。ブログ記事のカテゴリー分けに使用します。';
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
$HELP['category_id']['body'] = 'カテゴリーに自動的に振られるIDです。';
$HELP['category_name']['title'] = 'カテゴリー名';
$HELP['category_name']['body'] = 'カテゴリーの名前です。';
$HELP['category_index']['title'] = '表示順';
$HELP['category_index']['body'] = 'カテゴリーを一覧表示する際の表示順です。';
$HELP['category_visible']['title'] = '公開';
$HELP['category_visible']['body'] = 'カテゴリーをユーザに公開するかどうかを制御します。';
$HELP['category_buttons']['title'] = '操作ボタン';
$HELP['category_buttons']['body'] = '新規 - 新規にカテゴリーを追加します。<br />編集 - 選択されているカテゴリーを編集します。カテゴリーを選択するには、一覧の左端のチェックボックスにチェックを入れます。<br />削除ボタン - 選択されているカテゴリーを削除します。カテゴリーを選択するには、一覧の左端のチェックボックスにチェックを入れます。';

// ########## マルチブログ管理 ##########
$HELP['blogid_list']['title'] = 'ブログ一覧';
$HELP['blogid_list']['body'] = '使用可能なブログの一覧です。';
$HELP['blogid_detail']['title'] = 'ブログ詳細';
$HELP['blogid_detail']['body'] = 'ブログについての設定を行います。';
$HELP['blogid_check']['title'] = '選択用チェックボックス';
$HELP['blogid_check']['body'] = '編集や削除を行う項目を選択します。';
$HELP['blogid_name']['title'] = 'ブログ名';
$HELP['blogid_name']['body'] = 'ブログの名前です。';
$HELP['blogid_id']['title'] = 'ブログ識別ID';
$HELP['blogid_id']['body'] = 'ブログを識別するためにユニークにIDを付けます。';
$HELP['blogid_visible']['title'] = '公開';
$HELP['blogid_visible']['body'] = 'ブログを一般ユーザに公開するかどうかを制御します。非公開に設定の場合は一般ユーザから参照することはできません。';
$HELP['blogid_owner']['title'] = 'ブログ所有者';
$HELP['blogid_owner']['body'] = 'ブログの所有者です。「投稿ユーザ」以上のレベルのユーザを指定します。ブログの投稿が可能です。';
$HELP['blogid_template']['title'] = 'テンプレート';
$HELP['blogid_template']['body'] = 'ブログで使用するテンプレートです。';
$HELP['blogid_limit_user']['title'] = '閲覧制限';
$HELP['blogid_limit_user']['body'] = '閲覧を制限する場合は「ユーザを制限する」にチェックを入れます。<br />特定のユーザに閲覧制限する場合はメニューからユーザを選択します。「指定なし」の場合はログインしたユーザすべてが閲覧できます。';
$HELP['blogid_meta_title']['title'] = 'タイトル名';
$HELP['blogid_meta_title']['body'] = 'ヘッダ部のtitleタグに設定される文字列です。Webブラウザの画面タイトルとして表示されます。';
$HELP['blogid_meta_description']['title'] = 'ページ要約';
$HELP['blogid_meta_description']['body'] = 'ヘッダ部のdescriptionタグに設定される文字列です。120文字程度で記述します。<br />Googleでは検索結果に表示されます。';
$HELP['blogid_meta_keywords']['title'] = '検索キーワード';
$HELP['blogid_meta_keywords']['body'] = 'ヘッダ部のkeywordsタグに設定される文字列です。検索エンジン用のキーワードを「,」区切りで10個以下で記述します。';
$HELP['blogid_new_btn']['title'] = '新規ボタン';
$HELP['blogid_new_btn']['body'] = '新規にブログを追加します。';
$HELP['blogid_edit_btn']['title'] = '編集ボタン';
$HELP['blogid_edit_btn']['body'] = '選択されているブログを編集します。<br />項目を選択するには、一覧の左端のチェックボックスにチェックを入れます。';
$HELP['blogid_del_btn']['title'] = '削除ボタン';
$HELP['blogid_del_btn']['body'] = '選択されているブログを削除します。<br />項目を選択するには、一覧の左端のチェックボックスにチェックを入れます。';
$HELP['blogid_ret_btn']['title'] = '戻るボタン';
$HELP['blogid_ret_btn']['body'] = 'ブログ一覧へ戻ります。';

// ########## ブログ設定 ##########
$HELP['config_title']['title'] = 'ブログ設定';
$HELP['config_title']['body'] = 'ブログ全体の設定を行います。';
$HELP['config_view_count']['title'] = '記事表示数';
$HELP['config_view_count']['body'] = 'ブログ記事を一覧表示する場合の記事の表示数を設定します。';
$HELP['config_view_order']['title'] = '記事表示順';
$HELP['config_view_order']['body'] = 'ブログ記事を一覧表示する場合の記事の表示順を設定します。';
$HELP['config_category_count']['title'] = 'カテゴリ数';
$HELP['config_category_count']['body'] = '記事に設定可能なカテゴリ数です。';
$HELP['config_receive_comment']['title'] = 'コメント';
$HELP['config_receive_comment']['body'] = 'ブログ記事に対して、ユーザからのコメントの受付を許可するかどうかを設定します。';
$HELP['config_max_comment_length']['title'] = '最大文字数';
$HELP['config_max_comment_length']['body'] = 'コメントに入力可能な文字数を設定します。0を指定した場合は無制限です。';
$HELP['config_multi_blog_top_content']['title'] = 'マルチブログトップコンテンツ';
$HELP['config_multi_blog_top_content']['body'] = 'マルチブログを使用した場合のトップ画面のコンテンツです。各ブログへのリンク等を設定します。空文字列を設定した場合は全ブログのブログ記事が一覧表示されます。';
$HELP['config_layout']['title'] = 'レイアウト';
$HELP['config_layout']['body'] = 'コンテンツのレイアウトを設定します。<br />「[#～#]」は自動変換されるMagic3マクロです。「記事詳細」レイアウトでは、「[#USER_SSS#]」(Sは任意の半角英大文字)の形式で、「ユーザ定義フィールド」として任意にフィールドを追加することができます。';
$HELP['config_title_def']['title'] = 'タイトル';
$HELP['config_title_def']['body'] = 'タイトル表示について設定します。このウィジェットでタイトルの表示制御を行うには、ウィジェット共通設定の「タイトル」項目を「表示」で空文字列に設定します。タイトルを非表示にするには「[#NOTITLE#]」を設定します。';
$HELP['config_message_def']['title'] = 'メッセージ';
$HELP['config_message_def']['body'] = '表示されるメッセージについて設定します。';
?>
