-- *
-- * 標準テーブルデータ登録スクリプト
-- *
-- * PHP versions 5
-- *
-- * LICENSE: This source file is licensed under the terms of the GNU General Public License.
-- *
-- * @package    Magic3 Framework
-- * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
-- * @copyright  Copyright 2006-2015 Magic3 Project.
-- * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
-- * @version    SVN: $Id$
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- 標準テーブルデータ登録スクリプト
-- システムの標準構成で必要な初期データの登録を行う
-- --------------------------------------------------------------------------------------------------

-- 汎用コンテンツ設定マスター
INSERT INTO content_config
(ng_type,   ng_id,                  ng_value,    ng_name,                              ng_index) VALUES
('',        'use_password',         '0',         'パスワードアクセス制御',                 1),
('',        'password_content',         'このコンテンツはパスワードが必要です。<br />パスワードを入力してください。',         'パスワード画面コンテンツ',                 2),
('',        'layout_view_detail',   '[#BODY#][#FILES#][#PAGES#][#LINKS#]', 'コンテンツレイアウト(詳細表示)',               1),
('smartphone',        'layout_view_detail',   '[#BODY#][#FILES#][#PAGES#][#LINKS#]', 'コンテンツレイアウト(詳細表示)',               1),
('',           'output_head',      '0', 'HTMLヘッダ出力', 2),
('smartphone', 'output_head',      '0', 'HTMLヘッダ出力', 2),
('',           'head_view_detail', '<meta property="og:type" content="article" /><meta property="og:title" content="[#CT_TITLE#]" /><meta property="og:url" content="[#CT_URL#]" /><meta property="og:image" content="[#CT_IMAGE#]" /><meta property="og:description" content="[#CT_DESCRIPTION#]" /><meta property="og:site_name" content="[#SITE_NAME#]" />', 'HTMLヘッダ(詳細表示)',               3),
('smartphone', 'head_view_detail', '<meta property="og:type" content="article" /><meta property="og:title" content="[#CT_TITLE#]" /><meta property="og:url" content="[#CT_URL#]" /><meta property="og:image" content="[#CT_IMAGE#]" /><meta property="og:description" content="[#CT_DESCRIPTION#]" /><meta property="og:site_name" content="[#SITE_NAME#]" />', 'HTMLヘッダ(詳細表示)',               3),
('',           'use_jquery',      '0', 'jQueryスクリプト作成', 0),
('smartphone', 'use_jquery',      '0', 'jQueryスクリプト作成', 0),
('',           'use_content_template',      '0', 'コンテンツ単位のテンプレート設定', 0);

-- 新着情報設定マスター
INSERT INTO news_config
(nc_id,                  nc_value,    nc_name,                              nc_index) VALUES
('default_message',   '「[#TITLE#]」を追加しました', 'デフォルトメッセージ',               1),
('date_format',       'n月j日', '日時フォーマット',               1),
('layout_list_item',  '[#DATE#] [#MESSAGE#][#MARK#]', 'リスト項目レイアウト',               1);

-- Wiki設定マスター
INSERT INTO wiki_config
(wg_id,                     wg_value,        wg_name) VALUES
('password',                '',              '共通パスワード'),
('default_page',            'FrontPage',     'デフォルトページ'),
('whatsnew_page',           'RecentChanges', '最終更新ページ'),
('whatsdeleted_page',       'RecentDeleted', '最終削除ページ'),
('auth_type',               'admin',     '認証タイプ'),
('show_page_title',         '1',         'タイトルを表示するかどうか'),
('show_page_url',         '1',         'URLを表示するかどうか'),
('show_page_related',       '1',         '関連ページを表示するかどうか'),
('show_page_attach_files',  '1',         '添付ファイルを表示するかどうか'),
('show_page_last_modified', '1',         '最終更新を表示するかどうか'),
('show_toolbar_for_all_user', '0',         'すべてのユーザにツールバーを表示するかどうか'),
('user_limited_freeze',       '0',         '凍結・解凍機能のユーザ制限'),
('show_auto_heading_anchor',       '1',         '見出し自動アンカー'),
('layout_main',             '<article><header>[#TITLE#][#URL#]</header>[#TOOLBAR#][#BODY#]</article>[#TOOLBAR#][#FILES|pretag=----#][#UPDATES|pretag=----#][#LINKS#]', 'ページレイアウト(メイン)'),
('date_format', 'Y-m-d',         '日付フォーマット'),
('time_format', 'H:i:s',         '時間フォーマット'),
('show_username', '0',         'ユーザ名を表示するかどうか'),
('auto_link_wikiname', '1',         'Wiki名を自動リンクするかどうか'),
('recent_changes_count', '100',         '最終更新ページ最大項目数'),
('recent_deleted_count', '100',         '最終削除ページ最大項目数'),
('upload_filesize',   '1M',             'アップロードファイルの最大サイズ(バイト数)');

-- ブログ設定マスター
INSERT INTO blog_config
(bg_id,                     bg_value,    bg_name,                              bg_index) VALUES
('receive_comment',         '0',         'コメントの受け付け',                 1),
('receive_trackback',       '0',         'トラックバックの受け付け',           2),
('entry_view_count',        '10',        '記事表示数',                         3),
('entry_view_order',        '1',         '記事表示順',                         4),
('comment_max_length',      '300',       'コメント最大文字数',                 5),
('comment_count',           '100',       '1投稿記事のコメント最大数',          6),
('comment_open_time',       '30',        'コメント投稿可能期間(日)',           7),
('use_multi_blog',          '0',         'マルチブログを使用',                 8),
('multi_blog_top_content',  '',          'マルチブログのトップ画面コンテンツ', 9),
('category_count',          '2',         '記事に設定可能なカテゴリ数',         10),
('thumb_type',              's=80c.jpg;mw=160x120c.jpg;l=200c.jpg', '記事サムネールタイプ定義', 0),
('entry_default_image',     '0_72c.jpg;0_80c.jpg;0_200c.jpg',       '記事デフォルト画像', 0),
('comment_user_limited',      '0',       'コメントのユーザ制限',                 0),
('layout_entry_single',   '<small>[#CT_AUTHOR#] [#CT_DATE#] [#CT_TIME#] [#BLOG_LINK#]</small>[#BODY#][#CATEGORY#][#LINKS#]', 'コンテンツレイアウト(記事詳細)',               0),
('layout_entry_list',   '[#TITLE#]<small>[#CT_AUTHOR#] [#CT_DATE#] [#CT_TIME#] [#BLOG_LINK#]</small>[#BODY#][#CATEGORY#][#COMMENT_LINK#]', 'コンテンツレイアウト(記事一覧)',               0),
('layout_comment_list',   '[#AVATAR#]<dl><dt>[#TITLE#] <small>[#CM_AUTHOR#] [#CM_DATE#] [#CM_TIME#] [#URL#]</small></dt><dd>[#BODY#]</dd></dl>', 'コンテンツレイアウト(コメント一覧)',               0),
('output_head',      '0', 'HTMLヘッダ出力', 0),
('head_view_detail', '<meta property="og:type" content="article" /><meta property="og:title" content="[#CT_TITLE#]" /><meta property="og:url" content="[#CT_URL#]" /><meta property="og:image" content="[#CT_IMAGE#]" /><meta property="og:description" content="[#CT_DESCRIPTION#]" /><meta property="og:site_name" content="[#SITE_NAME#]" />', 'HTMLヘッダ(詳細表示)',               0),
('m:entry_view_count',      '3',         '記事表示数(携帯)',                   11),
('m:entry_view_order',      '1',         '記事表示順(携帯)',                   12),
('m:title_color',           '',         'タイトルの背景色',                    13),
('s:entry_view_count',        '10',        '記事表示数',                         100),
('s:entry_view_order',        '1',         '記事表示順',                         101),
('s:top_content',  '',          'トップ画面コンテンツ', 102),
('s:auto_resize_image_max_size',  '280',      '画像の自動変換最大サイズ',           103),
('s:jquery_view_style',       '1',      'jQueryMobile表示スタイル',           104),
('s:use_title_list_image',       '1',      'タイトルリスト画像を使用',           105),
('s:title_list_image',       '',      'タイトルリスト画像',           106),
('s:layout_entry_single',   '<small>[#CT_AUTHOR#] [#CT_DATE#] [#CT_TIME#] [#BLOG_LINK#]</small>[#BODY#][#CATEGORY#][#LINKS#]', 'コンテンツレイアウト(記事詳細)',               100),
('s:layout_entry_list',   '[#TITLE#]<small>[#CT_AUTHOR#] [#CT_DATE#] [#CT_TIME#] [#BLOG_LINK#]</small>[#BODY#][#CATEGORY#][#COMMENT_LINK#]', 'コンテンツレイアウト(記事一覧)',               100),
('s:layout_comment_list', '[#AVATAR#]<dl><dt>[#TITLE#] <small>[#CM_AUTHOR#] [#CM_DATE#] [#CM_TIME#] [#URL#]</small></dt><dd>[#BODY#]</dd></dl>', 'コンテンツレイアウト(コメント一覧)',               0),
('s:output_head',      '0', 'HTMLヘッダ出力', 100),
('s:head_view_detail', '<meta property="og:type" content="article" /><meta property="og:title" content="[#CT_TITLE#]" /><meta property="og:url" content="[#CT_URL#]" /><meta property="og:image" content="[#CT_IMAGE#]" /><meta property="og:description" content="[#CT_DESCRIPTION#]" /><meta property="og:site_name" content="[#SITE_NAME#]" />', 'HTMLヘッダ(詳細表示)',               100);

-- BBS設定マスター
INSERT INTO bbs_config (sf_id,                     sf_value,    sf_name,                        sf_index)
VALUES                 ('use_email',               '1',         'Eメール送信機能',              1);
INSERT INTO bbs_config (sf_id,                     sf_value,    sf_name,                        sf_index)
VALUES                 ('send_password_on_regist', '0',         '会員登録時のEメール自動送信',  2);
INSERT INTO bbs_config (sf_id,                     sf_value,    sf_name,                        sf_index)
VALUES                 ('can_edit_thread',         '0',         '投稿記事編集許可',             3);
INSERT INTO bbs_config (sf_id,                     sf_value,    sf_name,                        sf_index)
VALUES                 ('admin_name',              'BBS管理者', '管理者名',                     4);
INSERT INTO bbs_config (sf_id,                     sf_value,    sf_name,                        sf_index)
VALUES                 ('auto_email_sender',       '',          '自動送信メール送信元アドレス', 5);

-- BBSグループマスター
INSERT INTO `bbs_group` (`sg_id`, `sg_language_id`, `sg_name`, `sg_sort_order`, `sg_editable`, `sg_create_dt`) VALUES
(1,    'ja',           'ゲスト',        1,   false,   now());

-- ユーザコンテンツ項目マスター
INSERT INTO user_content_item
(ui_id,          ui_name,      ui_description,                   ui_type,  ui_key,        ui_create_dt) VALUES
('DEFAULT_LIST', '検索一覧用', '検索結果の一覧に表示するデータ', 0,        'SEARCH_LIST', now());

-- 予約リソースマスター
INSERT INTO reserve_resource (rr_id, rr_type, rr_config_id, rr_name, rr_sort_order)
VALUES                       (1,     0,       0,            'デフォルト', 1);

-- 予約設定マスター
INSERT INTO reserve_config (rc_id,   rc_key,                  rc_value, rc_name,        rc_index)
VALUES                     (0, 'unit_interval_minute', '15',     '単位時間(分)', 0);
INSERT INTO reserve_config (rc_id,   rc_key,                  rc_value, rc_name,                 rc_index)
VALUES                     (0, 'max_count_per_unit',      '3',      '1単位あたりの最大登録数', 1);
INSERT INTO reserve_config (rc_id,   rc_key,                  rc_value, rc_name,                 rc_index)
VALUES                     (0, 'view_day_start',      '0',      '先頭に表示する日付', 2);
INSERT INTO reserve_config (rc_id,   rc_key,                  rc_value, rc_name,                 rc_index)
VALUES                     (0, 'view_day_range',      '10',      '一覧表示日数', 3);
INSERT INTO reserve_config (rc_id,   rc_key,                  rc_value, rc_name,                 rc_index)
VALUES                     (0, 'max_user_reserve_count',      '1',      'ユーザの最大予約可能数', 4);
INSERT INTO reserve_config (rc_id,   rc_key,                  rc_value, rc_name,                 rc_index)
VALUES                     (0, 'default_resource_id',      '1',      'デフォルトのリソースID', 5);
INSERT INTO reserve_config (rc_id,   rc_key,                  rc_value, rc_name,                 rc_index)
VALUES                     (0, 'cancel_available_day',      '1',    '予約キャンセル可能な日数', 6);
INSERT INTO reserve_config (rc_id,   rc_key,                  rc_value, rc_name,                 rc_index)
VALUES                     (0, 'show_new_reserve_field',      '1',    '新規予約フィールドを表示', 7);

-- 予約カレンダーマスター
INSERT INTO reserve_calendar (ra_config_id, ra_usual, ra_specify_type, ra_day_attribute, ra_start_time, ra_end_time, ra_available)
VALUES                       (0,            true,     1,               2,                900,           1200,        true);
INSERT INTO reserve_calendar (ra_config_id, ra_usual, ra_specify_type, ra_day_attribute, ra_start_time, ra_end_time, ra_available)
VALUES                       (0,            true,     1,               2,                1300,          1730,        true);
INSERT INTO reserve_calendar (ra_config_id, ra_usual, ra_specify_type, ra_day_attribute, ra_start_time, ra_end_time, ra_available)
VALUES                       (0,            true,     1,               3,                900,           1200,        true);
INSERT INTO reserve_calendar (ra_config_id, ra_usual, ra_specify_type, ra_day_attribute, ra_start_time, ra_end_time, ra_available)
VALUES                       (0,            true,     1,               3,                1300,          1730,        true);
INSERT INTO reserve_calendar (ra_config_id, ra_usual, ra_specify_type, ra_day_attribute, ra_start_time, ra_end_time, ra_available)
VALUES                       (0,            true,     1,               4,                900,           1200,        true);
INSERT INTO reserve_calendar (ra_config_id, ra_usual, ra_specify_type, ra_day_attribute, ra_start_time, ra_end_time, ra_available)
VALUES                       (0,            true,     1,               4,                1300,          1730,        true);
INSERT INTO reserve_calendar (ra_config_id, ra_usual, ra_specify_type, ra_day_attribute, ra_start_time, ra_end_time, ra_available)
VALUES                       (0,            true,     1,               5,                900,           1200,        true);
INSERT INTO reserve_calendar (ra_config_id, ra_usual, ra_specify_type, ra_day_attribute, ra_start_time, ra_end_time, ra_available)
VALUES                       (0,            true,     1,               5,                1300,          1730,        true);
INSERT INTO reserve_calendar (ra_config_id, ra_usual, ra_specify_type, ra_day_attribute, ra_start_time, ra_end_time, ra_available)
VALUES                       (0,            true,     1,               6,                900,           1200,        true);
INSERT INTO reserve_calendar (ra_config_id, ra_usual, ra_specify_type, ra_day_attribute, ra_start_time, ra_end_time, ra_available)
VALUES                       (0,            true,     1,               6,                1300,          1730,        true);
INSERT INTO reserve_calendar (ra_config_id, ra_usual, ra_specify_type, ra_day_attribute, ra_start_time, ra_end_time, ra_available)
VALUES                       (0,            true,     1,               7,                900,           1200,        true);

-- カレンダー初期データ
INSERT INTO time_period
(to_date_type_id, to_index, to_name, to_start_time, to_minute) VALUES
(1, 0, '午前', '09:00:00', 180),
(1, 1, '午後', '13:00:00', 240),
(2, 0, '午前', '09:00:00', 180);
INSERT INTO date_type
(dt_id, dt_name, dt_sort_order) VALUES
(1, '営業日(終日)', 1),
(2, '営業日(午前のみ)', 2);

-- テーブルのバージョン
DELETE FROM _version WHERE vs_id = 'standard_table';
INSERT INTO _version (vs_id,                         vs_value, vs_name)
VALUES               ('standard_table',     '2008032401',     '標準テーブルのバージョン');

-- メール内容
DELETE FROM _mail_form WHERE mf_id = 'regist_member';
INSERT INTO _mail_form (mf_id,           mf_language_id, mf_subject,         mf_content,                                                                 mf_create_dt) 
VALUES                 ('regist_member', 'ja',           '会員登録',         'ご登録ありがとうございました。\nパスワードを送信します。\nこのパスワードでログインし、パスワードを再設定してください。\n\n[#URL#]\n\nパスワード　[#PASSWORD#]', now());
DELETE FROM _mail_form WHERE mf_id = 'send_password';
INSERT INTO _mail_form (mf_id,           mf_language_id, mf_subject,         mf_content,                                                                 mf_create_dt) 
VALUES                 ('send_password', 'ja',           'パスワード再送信', 'パスワードを再送信します。\nこのパスワードでログインし、パスワードを再設定してください。\n\n[#URL#]\n\nパスワード　[#PASSWORD#]',                               now());
DELETE FROM _mail_form WHERE mf_id = 'send_password_simple';
INSERT INTO _mail_form (mf_id,           mf_language_id, mf_subject,         mf_content,                                                                 mf_create_dt) 
VALUES                 ('send_password_simple', 'ja',    'パスワード再送信', 'パスワードを再送信します。\nこのパスワードでログインし、パスワードを再設定してください。\n\nパスワード　[#PASSWORD#]',                               now());
DELETE FROM _mail_form WHERE mf_id = 'contact_us';
INSERT INTO _mail_form (mf_id,           mf_language_id, mf_subject,         mf_content,                                                                 mf_create_dt) 
VALUES                 ('contact_us', 'ja',              'お問い合わせ',     '以下のお問い合わせがありました。\n\n[#BODY#]',                             now());
DELETE FROM _mail_form WHERE mf_id = 'test';
INSERT INTO _mail_form (mf_id,  mf_language_id, mf_subject,     mf_content,                                       mf_create_dt) 
VALUES                 ('test', 'ja',           'テストメール', 'このメールはテスト用のメールです。\n\n[#BODY#]', now());
DELETE FROM _mail_form WHERE mf_id = 'regist_user_auto';
INSERT INTO _mail_form (mf_id,              mf_language_id, mf_name, mf_subject,         mf_content,                                                                 mf_create_dt) 
VALUES                 ('regist_user_auto', 'ja',           '会員自動登録', '[[#SITE_NAME#]] 会員登録 ([#ACCOUNT#])',       'ご登録ありがとうございます。\nパスワードを送信します。\nこのパスワードでログインすると会員として承認されます。\n\n[#URL#]\n\nパスワード:　[#PASSWORD#]', now());
DELETE FROM _mail_form WHERE mf_id = 'regist_user_auth';
INSERT INTO _mail_form (mf_id,              mf_language_id, mf_name, mf_subject,         mf_content,                                                                 mf_create_dt) 
VALUES                 ('regist_user_auth', 'ja',           '会員承認登録', '[[#SITE_NAME#]] 会員登録 ([#ACCOUNT#])',       'ご登録ありがとうございます。\nパスワードを送信します。\n管理者からの承認後、このパスワードでログイン可能になります。\n\nパスワード:　[#PASSWORD#]', now());
DELETE FROM _mail_form WHERE mf_id = 'regist_user_auth_a';
INSERT INTO _mail_form (mf_id,              mf_language_id, mf_name, mf_subject,         mf_content,                  mf_admin,                                               mf_create_dt) 
VALUES                 ('regist_user_auth_a', 'ja',           '会員承認登録(管理者用)', '=> [[#SITE_NAME#]] 会員登録 ([#ACCOUNT#])',       '承認が必要な会員の登録がありました。\n会員管理画面からユーザを承認して下さい。\n\n[#URL#]', true, now());
DELETE FROM _mail_form WHERE mf_id = 'regist_user_auto_completed';
INSERT INTO _mail_form (mf_id,                   mf_language_id, mf_name, mf_subject,         mf_content,                                                                 mf_create_dt) 
VALUES                 ('regist_user_auto_completed', 'ja',           '会員自動登録完了', '[[#SITE_NAME#]] 会員自動登録完了 ([#ACCOUNT#])',   '会員の登録を承認しました。\n\nアカウント:　[#ACCOUNT#]', now());
DELETE FROM _mail_form WHERE mf_id = 'regist_user_auth_completed';
INSERT INTO _mail_form (mf_id,                   mf_language_id, mf_name, mf_subject,         mf_content,                                                                 mf_create_dt) 
VALUES                 ('regist_user_auth_completed', 'ja',           '会員承認登録完了', '[[#SITE_NAME#]] 会員登録完了 ([#ACCOUNT#])',   '会員の登録を承認しました。\n\nアカウント:　[#ACCOUNT#]', now());
DELETE FROM _mail_form WHERE mf_id = 'regist_user_completed';
INSERT INTO _mail_form (mf_id,                   mf_language_id, mf_subject,         mf_content,                                                                 mf_create_dt) 
VALUES                 ('regist_user_completed', 'ja',           'ユーザ自動登録完了',   'ユーザの登録を承認しました。\n\nアカウント:　[#ACCOUNT#]', now());
DELETE FROM _mail_form WHERE mf_id = 'send_tmp_password';
INSERT INTO _mail_form (mf_id,           mf_language_id, mf_subject,         mf_content,                                                                 mf_create_dt) 
VALUES                 ('send_tmp_password', 'ja',           '仮パスワード送信', '仮パスワードを送信します。\nこのパスワードでログインし、パスワードを再設定してください。\n\nパスワード　[#PASSWORD#]',                               now());

-- テンプレート情報
TRUNCATE TABLE _templates;
INSERT INTO _templates
(tm_id,                           tm_name,                         tm_type, tm_device_type, tm_mobile, tm_use_bootstrap, tm_available, tm_generator, tm_version) VALUES
('_admin4',                       '_admin4',                       2,       0,              false,     true,             false,        '',           ''),
('_system',                       '_system',                       1,       0,              false,     false,            false,        '',           ''),
('_layout',                       '_layout',                       1,       0,              false,     false,            false,        '',           ''),
('art41_sample1',                 'art41_sample1',                 2,       0,              false,     false,            true,         '',           ''),
('art41_sample2',                 'art41_sample2',                 2,       0,              false,     false,            true,         '',           ''),
('art42_sample3',                 'art42_sample3',                 2,       0,              false,     false,            true,         '',           ''),
('art42_sample4',                 'art42_sample4',                 2,       0,              false,     false,            true,         '',           ''),
('art42_sample5',                 'art42_sample5',                 2,       0,              false,     false,            true,         '',           ''),
('themler_sample1',               'themler_sample1',               2,       0,              false,     false,            true,         'themler',    '1.0.33'),
('bootstrap_yeti',                'bootstrap_yeti',                10,      0,              false,     true,             true,         '',           ''),
('bootstrap_cerulean',            'bootstrap_cerulean',            10,      0,              false,     true,             true,         '',           ''),
('bootstrap_united',              'bootstrap_united',              10,      0,              false,     true,             true,         '',           ''),
('bootstrap_cerulean_head',       'bootstrap_cerulean_head',       10,      0,              false,     true,             true,         '',           ''),
('m/default',                     'm/default',                     0,       1,              true,      false,            true,         '',           ''),
('m/smallfont',                   'm/smallfont',                   0,       1,              true,      false,            true,         '',           ''),
('s/default_simple',              's/default_simple',              1,       2,              false,     false,            true,         '',           ''),
('s/default_jquery',              's/default_jquery',              1,       2,              false,     false,            true,         '',           ''),
('s/default_jquery13',           's/default_jquery13',             1,       2,              false,     false,            true,         '',           ''),
('s/art42_sample2',               's/art42_sample2',               1,       2,              false,     false,            true,         '',           '');

-- メニューIDマスター
INSERT INTO _menu_id
(mn_id,         mn_name,          mn_description, mn_device_type, mn_widget_id, mn_sort_order) VALUES
('main_menu',   'メインメニュー(PC用)', '',             0,        '',      0),
('sub_menu1',   'サブメニュー1(PC用)',  '',             0,        '',      1),
('sub_menu2',   'サブメニュー2(PC用)',  '',             0,        '',      2),
('sub_menu3',   'サブメニュー3(PC用)',  '',             0,        '',      3),
('m_main_menu', 'メインメニュー(携帯用)', '',             1,      '',        10),
('s_main_menu', 'メインメニュー(スマートフォン用)', '',             2,     '',         0);

-- 基本テーブルの不要なデータを削除
-- 画面定義
DELETE FROM _page_def WHERE pd_id = 'index';

-- 画面定義(携帯用)
DELETE FROM _page_def WHERE pd_id = 'm_index';

-- 画面定義(スマートフォン用)
DELETE FROM _page_def WHERE pd_id = 's_index';

-- --------------------------------------------------------------------------------------------------
-- 2ちゃんねる風BBSウィジェット用
-- --------------------------------------------------------------------------------------------------
-- BBS(2ch)設定マスター
INSERT INTO bbs_2ch_config 
(tg_id,                   tg_value,                     tg_name) VALUES
('title',                 '掲示板',                     '掲示板タイトル'),
('top_image',             'tubo.gif',                   'トップ表示画像'),
('title_color',           '#000000',                    'タイトル背景色'),
('top_link',              '',                           'トップ画像のリンク先'),
('bg_color',              '#EFEFEF',                    '背景色'),
('bg_image',            'ba.gif',                     '背景画像'),
('noname_name',           '名無し太郎', '名前未設定時の表示名'),
('admin_name',           'サイト運営者', 'サイト運営者名'),
('makethread_color',      '#CCFFCC',                    ''),
('menu_color',            '#CCFFCC',                    ''),
('thread_color',          '#EFEFEF',                    ''),
('text_color',            '#000000',                    ''),
('name_color',            'green',                      '投稿者名文字色'),
('link_color',            '#0000FF',                    ''),
('alink_color',           '#FF0000',                    ''),
('vlink_color',           '#660099',                    ''),
('err_message_color',     '#FF0000',                    'エラーメッセージ文字色'),
('subject_color',         '#FF0000',                    '件名文字色'),
('thread_count',         '10',                         'トップ画面に表示するスレッド最大数'),
('menu_thread_count',       '40',                         'メニューに表示するスレッド最大数'),
('res_count',             '10',                         'トップ画面に表示するレス最大数'),
('link_number',           '15',                         ''),
('unicode',               'pass',                       ''),
('delete_name',           'あぼーん',                   ''),
('subject_length',         '40',                   '件名最大長'),
('name_length',            '20',                   '投稿者名最大長'),
('email_length',           '20',                   'emailアドレス最大長'),
('message_length',         '2000',                   '投稿文最大長'),
('line_length',            '80',                   '投稿文行長'),
('line_count',             '50',                   '投稿文行数'),
('res_anchor_link_count',  '10',                   'レスアンカーリンク数'),
('thread_tatesugi',       '',                   ''),
('nanashi_check',         '',                   ''),
('timecount',             '',                   ''),
('timeclose',             '',                   ''),
('proxy_check',           '',                   ''),
('oversea_thread',        '',                   ''),
('oversea_proxy',         '',                   ''),
('disp_id',               '',                   ''),
('force_id',              '',                   ''),
('no_id',                 '',                   ''),
('keeplogcount',          '4096',                   'ログファイル保持数'),
('thread_res',            '500',                   '1スレッドに投稿できるレス数の上限'),
('thread_max_msg',        'あれ、<NUM>超えちゃったみたい…書き込めないや…<br />　　　 ∧∧ 　　　　　　　　　　 ∧,,∧<br />　　　（；ﾟДﾟ） 　　　　　　　　　ミﾟДﾟ,,彡 　おｋｋ<br />　　　ﾉ つ▼〔|￣￣］ 　　　　 ▽⊂　ﾐ 　　　新スレいこうぜ<br />　～（,,⊃〔￣||====]～～［］⊂,⊂,,,;;ﾐ@',                   'レスオーバー時のメッセージ'),
('thread_bytes',          '524288',                   '1スレッドの上限(バイト)'),
('file_upload',                '0',                   'ファイルアップ許可'),
('max_bytes',             '300000',                   'アップロード上限(バイト)'),
('max_w',                 '120',                   'サムネイル画像の幅'),
('max_h',                 '160',                   'サムネイル画像の高さ'),
('teletype',              '1',                   '等幅フォント機能'),
('name_774',              '1',                   'スレッド内名無し名変更機能'),
('force_774',             '1',                   '名無しへ強制変更機能'),
('force_no_id',           '1',                   'IDなし機能'),
('force_sage',            '1',                   'sage強制機能'),
('force_stars',           '1',                   'レス要キャップ機能'),
('force_normal',          '1',                   'スレッド内VIP機能解除'),
('force_name',            '1',                   '名前入力強制機能'),
('force_up',              '0',                   'アップロード機能'),
('gz_flag',               '0',                   'gzip圧縮をする'),
('jikan_kisei',           '0',                   '時間規制'),
('jikan_start',           '22',                   '規制開始時間(0-23)'),
('jikan_end',             '2',                   '規制終了時間(0-23)'),
('bbs_guide',             '掲示板の規則等を書いてください。',                    '掲示板規則'),
('bottom_message',        '<center><b>投稿者へのメッセージを書いてください。</b></center>',                     'トップ画面下部メッセージ'),
('bbs_style',             '1',                   '掲示板のスタイル(0=テンプレート、1=2ch)'),
('show_email',            '0',                   'Eメールアドレスを表示'),
('autolink',              '1',                   '自動的にリンクを作成'),
('msg_thread_end',        'このスレッドは${maxnum}を超えました。 <br /> もう書けないので、新しいスレッドを立ててくださいです。。。 ', 'スレッドの終了メッセージ'),
('thread_end_message',    'このスレッドは[#RES_MAX_NO#]を超えました。\r\nもう書けないので、新しいスレッドを立ててくださいです。。。', 'スレッドの終了メッセージ');

-- --------------------------------------------------------------------------------------------------
-- マイクロブログウィジェット用
-- --------------------------------------------------------------------------------------------------
INSERT INTO mblog_config 
(mc_id,                   mc_value,                mc_name) VALUES
('post_with_no_login',    '0',                     'ログインなしの投稿'),
('use_subject',           '0',                     '件名の使用'),
('message_id_length',     '5',                     'メッセージIDのバイト数'),
('message_count_top',     '3',                     'トップページのメッセージ表示項目数'),
('message_count_mypage',  '30',                    'マイページのメッセージ表示項目数'),
('m:message_count_mypage','10',                    'マイページのメッセージ表示項目数(携帯)'),
('text_color',            '#000000',               '文字色'),
('bg_color',              '#EAF4F5',               '背景色'),
('inner_bg_color',        '#FFFFCC',               '内枠のデフォルト背景色'),
('profile_color',         '',                      'プロフィール背景色'),
('err_message_color',     '#FF0000',               'エラーメッセージ文字色'),
('bg_image',              '',                      '背景画像'),
('message_length',         '200',                   '投稿文最大長'),
('subject_length',         '30',                   '件名最大長'),
('name_length',            '30',                   '投稿者名最大長'),
('email_length',           '30',                   'emailアドレス最大長'),
('top_contents',           '',                     'トップ画面のコンテンツ'),
('m:top_contents',           '',                   'トップ画面のコンテンツ(携帯)');

-- --------------------------------------------------------------------------------------------------
-- イベント情報用
-- --------------------------------------------------------------------------------------------------
-- イベント設定マスター
INSERT INTO event_config
(eg_id,                     eg_value,    eg_name,                              eg_index) VALUES
('entry_view_count',        '10',        '記事表示数',                         3),
('entry_view_order',        '0',         '記事表示順',                         4),
('top_contents',            'これからのイベントを表示します。',          'トップ画面コンテンツ',               6),
('category_count',          '2',         '記事に設定可能なカテゴリ数',         10),
('thumb_type',              's=80c.jpg;mw=160x120c.jpg;l=200c.jpg', '記事サムネールタイプ定義', 0),
('entry_default_image',     '0_72c.jpg;0_80c.jpg;0_200c.jpg',       '記事デフォルト画像', 0),
('msg_no_entry',            'イベント記事は登録されていません',     'イベント記事が登録されていないメッセージ',                 0),
('msg_find_no_entry',       'イベント記事が見つかりません',         'イベント記事が見つからないメッセージ',                 0),
('msg_no_entry_in_future',  '今後のイベントはありません',           '予定イベントなし時メッセージ',                 0),
('layout_entry_single',     '<div class="entry_head"><span class="event_date">日時：[#DATE#]</span><span class="event_location">場所：[#CT_PLACE#]</span><div><span class="event_contact">連絡先：[#CT_CONTACT#]</span></div><div><span class="event_url">URL：[#CT_INFO_URL:autolink=true;#]</span></div></div><div class="entry_content">[#BODY#][#RESULT#]</div>[#CATEGORY#][#LINKS#]', 'コンテンツレイアウト(記事詳細)',               0),
('layout_entry_list',       '[#TITLE#]<div class="entry_head"><span class="event_date">日時：[#DATE#]</span><span class="event_location">場所：[#CT_PLACE#]</span><div>[#DETAIL_LINK#]</div></div><div class="entry_content">[#BODY#]</div>[#CATEGORY#]', 'コンテンツレイアウト(記事一覧)',               0),
('output_head',      '0', 'HTMLヘッダ出力', 0),
('head_view_detail', '<meta property="og:type" content="article" /><meta property="og:title" content="[#CT_TITLE#]" /><meta property="og:url" content="[#CT_URL#]" /><meta property="og:image" content="[#CT_IMAGE#]" /><meta property="og:description" content="[#CT_DESCRIPTION#]" /><meta property="og:site_name" content="[#SITE_NAME#]" />', 'HTMLヘッダ(詳細表示)',               0);

-- イベント予約設定マスター
INSERT INTO evententry_config
(ef_id,                 ef_value,    ef_name) VALUES
('show_entry_count',     '0',         '参加者数を表示するかどうか'),
('show_entry_member',    '0',         '参加者を表示するかどうか(会員対象)'),
('enable_cancel',        '0',         'キャンセル機能を使用可能にするかどうか'),
('layout_entry_single', '<div class="entry_info"><div style="float:left;">[#IMAGE#]</div><div class="clearfix"><div>[#CT_SUMMARY#]</div></div><div><span class="event_date">日時：[#DATE#]</span> <span class="event_location">場所：[#CT_PLACE#]</span></div><div><span class="event_contact">連絡先：[#CT_CONTACT#]</span></div></div><div class="evententry_content">[#BODY#]</div><div class="evententry_info"><div>定員: [#CT_QUOTA#]</div><div>参加: [#CT_ENTRY_COUNT#]</div></div><div><strong>会員名: [#CT_MEMBER_NAME#]</strong></div>[#BUTTON|type=ok;title=予約する|予約済み#]',         'レイアウト(記事詳細)'),
('msg_entry_exceed_max',     '予約が定員に達しました',     '予約定員オーバーメッセージ'),
('msg_entry_out_of_term',   '受付期間外です',     '受付期間外メッセージ'),
('msg_entry_term_expired',   '受付期間を終了しました',     '受付期間終了メッセージ'),
('msg_entry_stopped',        '受付は一時中断しています',   '受付中断メッセージ'),
('msg_entry_closed',         '受付を終了しました',         '受付終了メッセージ'),
('msg_event_closed',         'イベントは終了しました',         'イベント終了メッセージ'),
('msg_entry_user_registered',       'このイベントを予約しました', '予約済みメッセージ');

-- --------------------------------------------------------------------------------------------------
-- フォトギャラリー用
-- --------------------------------------------------------------------------------------------------
-- フォトギャラリー設定マスター
INSERT INTO photo_config
(hg_id,                     hg_value,           hg_name,                                  hg_index) VALUES
('image_protect_copyright',       '1',                '画像著作権保護',                             1),
('upload_image_max_size',   '500K',             'アップロード画像の最大サイズ(バイト数)', 2),
('watermark_filename',      'default_mark.jpg', 'セキュリティ保護画像ファイル名',         3),
('default_image_size',      '450',              '公開画像デフォルトサイズ',               4),
('default_thumbnail_size',  '128',              'サムネール画像デフォルトサイズ',         5),
('thumbnail_bg_color',      '#FFFFFF',              'サムネール画像背景色',         6),
('thumbnail_type',          '0',              'サムネールタイプ',         7),
('image_category_count',  '2',                '画像カテゴリー数',         8),
('photo_list_item_count',  '24',                '画像一覧表示項目数',         9),
('photo_list_order',        '0',         '画像一覧表示順',                         10),
('photo_title_short_length',  '10',                '画像タイトル(略式)文字数',         11),
('photo_category_password', '0',                '画像カテゴリーのパスワード制限',             12),
('thumbnail_crop',      '1',                'サムネール画像切り取り',         13),
('image_size',          '450',              '公開画像サイズ',               14),
('thumbnail_size',      '128',              'サムネール画像サイズ',         15),
('image_quality',       '100',              '画像の品質',                   16),
('photo_list_sort_key', 'index',            '画像一覧のソートキー',         17),
('s:photo_list_item_count',  '24',                '画像一覧表示項目数',         200),
('s:photo_list_order',        '1',         '画像一覧表示順',                         201),
('s:photo_title_short_length',  '7',                '画像タイトル(略式)文字数',         202),
('s:photo_list_sort_key', 'index',            '画像一覧のソートキー',         203),
('s:default_image_size',      '320',              '公開画像デフォルトサイズ',               204),
('s:default_thumbnail_size',  '128',              'サムネール画像デフォルトサイズ',         205),
('html_photo_description',  '0',                'HTML形式の画像情報(説明)',         0),
('use_photo_date',        '1',                '画像情報(撮影日)を使用',         0),
('use_photo_location',       '1',                '画像情報(撮影場所)を使用',         0),
('use_photo_camera',      '1',                '画像情報(カメラ)を使用',         0),
('use_photo_description', '1',                '画像情報(説明)を使用',         0),
('use_photo_keyword',     '1',                '画像情報(検索キーワード)を使用',         0),
('use_photo_category',    '1',                '画像情報(カテゴリー)を使用',         0),
('use_photo_rate',    '1',                '画像情報(評価)を使用',         0),
('layout_view_detail',   '<table class="photo_info table"><caption>画像情報</caption><tbody><tr><th>ID</th><td>[#CT_ID#]</td></tr><tr><th>タイトル</th><td>[#CT_TITLE#]&nbsp;[#PERMALINK#]</td></tr><tr><th>撮影者</th><td>[#CT_AUTHOR#]</td></tr><tr><th>撮影日</th><td>[#CT_DATE#]</td></tr><tr><th>場所</th><td>[#CT_LOCATION#]</td></tr><tr><th>カメラ</th><td>[#CT_CAMERA#]</td></tr><tr><th>説明</th><td>[#CT_DESCRIPTION#]</td></tr><tr><th>カテゴリー</th><td>[#CT_CATEGORY#]</td></tr><tr><th>キーワード</th><td>[#CT_KEYWORD#]</td></tr><tr><th>評価</th><td>[#RATE#]</td></tr></tbody></table>', 'レイアウト(詳細表示)',               0),
('output_head',      '0', 'HTMLヘッダ出力', 0),
('head_view_detail',   '<meta property="og:type" content="article" /><meta property="og:title" content="[#CT_TITLE#]" /><meta property="og:url" content="[#CT_URL#]" /><meta property="og:image" content="[#CT_IMAGE#]" /><meta property="og:description" content="[#CT_SUMMARY#]" /><meta property="og:site_name" content="[#SITE_NAME#]" />', 'ヘッダ出力(詳細表示)',               0);

-- --------------------------------------------------------------------------------------------------
-- 携帯クイズ王ウィジェット用
-- --------------------------------------------------------------------------------------------------
-- クイズ設定マスター
DROP TABLE IF EXISTS quiz_config;
CREATE TABLE quiz_config (
    qc_id                VARCHAR(30)    DEFAULT ''                    NOT NULL,      -- ID(Key)
    qc_value             TEXT                                         NOT NULL,      -- 値
    qc_name              VARCHAR(50)    DEFAULT ''                    NOT NULL,      -- 名称
    qc_description       VARCHAR(80)    DEFAULT ''                    NOT NULL,      -- 説明
    qc_index             INT            DEFAULT 0                     NOT NULL,      -- ソート用
    PRIMARY KEY          (qc_id)
) ENGINE=innodb;

-- クイズパターンセットIDマスター
DROP TABLE IF EXISTS quiz_set_id;
CREATE TABLE quiz_set_id (
    qs_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    qs_id                VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- 定義項目ID
    qs_history_index     INT            DEFAULT 0                     NOT NULL,      -- 履歴管理用インデックスNo(0～)

    qs_name              VARCHAR(50)    DEFAULT ''                    NOT NULL,      -- 名称
    qs_description       VARCHAR(80)    DEFAULT ''                    NOT NULL,      -- 説明
    qs_index             INT            DEFAULT 0                     NOT NULL,      -- ソート用
    qs_visible           BOOLEAN        DEFAULT true                  NOT NULL,      -- 表示制御
    
    qs_create_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード作成者
    qs_create_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード作成日時
    qs_update_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード更新者
    qs_update_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード更新日時
    qs_deleted           BOOLEAN        DEFAULT false                 NOT NULL,      -- レコード削除状態
    PRIMARY KEY          (qs_serial),
    UNIQUE               (qs_id,  qs_history_index)
) ENGINE=innodb;

-- クイズ問題定義マスター
DROP TABLE IF EXISTS quiz_item_def;
CREATE TABLE quiz_item_def (
    qd_serial            INT            AUTO_INCREMENT,                              -- シリアル番号
    qd_set_id            VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- 定義セットID
    qd_id                VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- クイズ項目(問題、回答)ID
    qd_history_index     INT            DEFAULT 0                     NOT NULL,      -- 履歴管理用インデックスNo(0～)

    qd_type              INT            DEFAULT 0                     NOT NULL,      -- 項目タイプ(0=問題、1=回答)
    qd_select_answer_id  TEXT                                         NOT NULL,      -- 選択用回答ID
    qd_answer_id         VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- 正解回答ID
    qd_title             VARCHAR(30)    DEFAULT ''                    NOT NULL,      -- タイトル
    qd_content           VARCHAR(300)   DEFAULT ''                    NOT NULL,      -- 内容
    qd_index             INT            DEFAULT 0                     NOT NULL,      -- ソート用
    qd_visible           BOOLEAN        DEFAULT true                  NOT NULL,      -- 表示制御
    
    qd_create_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード作成者
    qd_create_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード作成日時
    qd_update_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード更新者
    qd_update_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード更新日時
    qd_deleted           BOOLEAN        DEFAULT false                 NOT NULL,      -- レコード削除状態
    PRIMARY KEY          (qd_serial),
    UNIQUE               (qd_set_id,   qd_id,  qd_history_index)
) ENGINE=innodb;

-- クイズユーザ回答トラン
DROP TABLE IF EXISTS quiz_user_post;
CREATE TABLE quiz_user_post (
    qp_serial            INT            AUTO_INCREMENT,                              -- シリアル番号
    qp_mobile_id         VARCHAR(30)    DEFAULT ''                    NOT NULL,      -- 携帯端末ID
    qp_set_id            VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- 定義セットID
    qp_question_id       VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- クイズ問題ID
    qp_answer_id         VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- 回答ID
    qp_result            BOOLEAN        DEFAULT false                 NOT NULL,      -- 回答結果
    qp_dt                TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- 記録日時
    qp_access_log_serial INT            DEFAULT 0                     NOT NULL,      -- アクセスログ番号
    PRIMARY KEY          (qp_serial),
    UNIQUE               (qp_mobile_id,   qp_set_id,  qp_question_id)
) ENGINE=innodb;

INSERT INTO quiz_set_id (qs_id,         qs_name,              qs_index)
VALUES                  ('default_set', 'デフォルトパターン', 0);
INSERT INTO quiz_config (qc_id,            qc_value,      qc_name,                        qc_index)
VALUES                  ('current_set_id', 'default_set', '現在運用中のパターンセットID', 0);
-- サンプルデータ
INSERT INTO quiz_item_def
(qd_set_id,      qd_id, qd_type, qd_select_answer_id, qd_answer_id, qd_title, qd_content,            qd_index) VALUES
('default_set', 'A001', 1,       '',                  '',           '回答1',  '回答1の説明です。',   0),
('default_set', 'A002', 1,       '',                  '',           '回答2',  '回答2の説明です。',   0),
('default_set', 'A003', 1,       '',                  '',           '回答3',  '回答3の説明です。',   0),
('default_set', 'A004', 1,       '',                  '',           '回答4',  '回答4の説明です。',   0),
('default_set', 'A005', 1,       '',                  '',           '回答5',  '回答5の説明です。',   0),
('default_set', 'A006', 1,       '',                  '',           '回答6',  '回答6の説明です。',   0),
('default_set', 'Q001', 0,       'A001;A002;A003',    'A002',       '問題1',  'サンプル問題1です。', 1),
('default_set', 'Q002', 0,       'A006;A004;A005',    'A006',       '問題2',  'サンプル問題2です。', 2);

-- --------------------------------------------------------------------------------------------------
-- 以下、変更の少ないデータ
-- --------------------------------------------------------------------------------------------------
-- 国マスター
INSERT INTO country
(ct_id, ct_language_id, ct_name,          ct_name_short, ct_iso_code_2, ct_index) VALUES
('JPN', 'ja',           '日本',           '日本',        'JP',          0),
('USA', 'ja',           'アメリカ合衆国', 'アメリカ',    'US',          1),
('GBR', 'ja',           'イギリス',       'イギリス',    'GB',          2),
('DEU', 'ja',           'ドイツ',         'ドイツ',      'DE',          3),
('FRA', 'ja',           'フランス',       'フランス',    'FR',          4),
('CHN', 'ja',           '中華人民共和国', '中国',        'CN',          5),
('KOR', 'ja',           '大韓民国',       '韓国',        'KR',          6);

-- 通貨マスター
INSERT INTO currency
(cu_id, cu_language_id, cu_name,      cu_description,         cu_symbol, cu_post_symbol, cu_decimal_place, cu_index) VALUES
('JPY', 'ja',           '円',         '日本円',               '￥',      '円',           0,                1),
('USD', 'ja',           'ドル',       'アメリカドル',         '$',       '',             2,                2),
('EUR', 'ja',           'ユーロ',     '欧州ユーロ',           '',        '',             2,                3),
('GBP', 'ja',           'ポンド',     'イギリスポンド',       '￡',      '',             2,                4),
('CNY', 'ja',           '元',         '中国人民元',           '',        '元',           0,                5),
('KRW', 'ja',           'ウォン',     '韓国ウォン',           '',        'ウォン',       0,                6),
('TWD', 'ja',           'ドル',       '台湾ドル',             '',        'ドル',         2,                7),
('THB', 'ja',           'バーツ',     'タイバーツ',           '',        'バーツ',       0,                8),
('CAD', 'ja',           'ドル',       'カナダドル',           '',        'ドル',         2,                9),
('SGD', 'ja',           'ドル',       'シンガポールドル',     '',        'ドル',         2,                10),
('MYR', 'ja',           'リンギット', 'マレーシアリンギット', '',        'リンギット',   0,                11),
('CHF', 'ja',           'フラン',     'スイスフラン',         '',        'フラン',       0,                12),
('IDR', 'ja',           'ルピア',     'インドネシアルピア',   '',        'ルピア',       0,                13),
('INR', 'ja',           'ルピー',     'インドルピー',         '',        'ルピー',       0,                14),
('PHP', 'ja',           'ペソ',       'フィリピンペソ',       '',        'ペソ',         0,                15),
('NZD', 'ja',           'ドル',       'ニュージーランドドル', '',        'ドル',         2,                16),
('HKD', 'ja',           'ドル',       '香港ドル',             '',        'ドル',         2,                17),
('TRL', 'ja',           'リラ',       'トルコリラ',           '',        'リラ',         0,                18);
