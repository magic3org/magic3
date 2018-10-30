-- *
-- * _widgetsテーブル更新スクリプト
-- *
-- * PHP versions 5
-- *
-- * LICENSE: This source file is licensed under the terms of the GNU General Public License.
-- *
-- * @package    Magic3 Framework
-- * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
-- * @copyright  Copyright 2006-2018 Magic3 Project.
-- * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
-- * @version    SVN: $Id$
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- _widgetsテーブル更新スクリプト
-- ウィジェットの登録データを更新する
-- 一番最後に実行されるスクリプトファイル
-- --------------------------------------------------------------------------------------------------

-- ウィジェット情報(管理機能)
DELETE FROM _widgets WHERE wd_id = 'admin_menu';
INSERT INTO _widgets
(wd_id,         wd_name,           wd_admin, wd_type, wd_category_id, wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_add_script_lib,  wd_available, wd_editable, wd_has_admin, wd_initialized, wd_launch_index, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('admin_menu', '管理用メニュー', true,     'menu',  'admin',        '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'bootstrap.toggle', false,        false,       true, true,           100,     '2018-06-24',        now(),         now());
DELETE FROM _widgets WHERE wd_id = 'admin_main';
INSERT INTO _widgets
(wd_id,        wd_name,      wd_admin, wd_category_id, wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_add_script_lib, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('admin_main', '管理用画面', true,     'admin',            '1.1.0', 'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'menudef=jquery.jstree;menudef_detail=wysiwyg_editor;smenudef_detail=wysiwyg_editor;analyzegraph=jquery.jqplot;adjustwidget=wysiwyg_editor,ckeditor_m3toolbar;filebrowse=elfinder;editmenu_others=elfinder;initwizard_=bootstrap;configsys=bootstrap.toggle;pageinfo=bootstrap.toggle;pagedef=bootstrap.toggle;test_ckeditor=ckeditor_m3toolbar;test_config=wysiwyg_editor,ckeditor_m3toolbar;test_input=bootstrap.datetimepicker;test_chat=socketio;test_realtime=socketio;test_voice=webrtc;configimage=jquery.uploadfile;tempimage=jquery.uploadfile;tempimage_detail=jquery.uploadfile;userlist_detail=md5;landingpage_detail=md5', false,        false,       false,        true,          false, true, '2013-03-04', now(),now());
DELETE FROM _widgets WHERE wd_id = 'admin/analytics';
INSERT INTO _widgets
(wd_id,             wd_name,            wd_admin, wd_category_id, wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_add_script_lib, wd_available, wd_editable, wd_has_admin, wd_initialized,  wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('admin/analytics', '管理用サイト解析', true,     'admin', '1.1.0', 'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'jquery.jqplot', false,        false,       true, true,           '2013-03-04', now(),         now());
DELETE FROM _widgets WHERE wd_id = 'admin/opelog';
INSERT INTO _widgets
(wd_id,             wd_name,            wd_admin, wd_category_id, wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_add_script_lib, wd_available, wd_editable, wd_has_admin, wd_initialized,  wd_launch_index, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('admin/opelog', '管理用運用ログ表示', true,     'admin', '1.2.0', 'Naoki Hirata', 'Magic3.org', 'GPL',      10,                '', false,        false,       true, true,    1,       '2014-01-13', now(),         now());
DELETE FROM _widgets WHERE wd_id = 'admin/loginuser';
INSERT INTO _widgets
(wd_id,             wd_name,                    wd_admin, wd_category_id, wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_add_script_lib, wd_available, wd_editable, wd_has_admin, wd_initialized,  wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('admin/loginuser', '管理用ログインユーザ情報', true,     'admin',            '2.0.0',   'Naoki Hirata', 'Magic3.org', 'GPL',      10,                '', false,        false,       false, true, '2014-01-13',           now(),         now());
DELETE FROM _widgets WHERE wd_id = 'admin/message';
INSERT INTO _widgets
(wd_id,           wd_name,            wd_admin, wd_category_id, wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_add_script_lib, wd_available, wd_editable, wd_has_admin, wd_initialized,  wd_launch_index, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('admin/message', '管理用メッセージ出力', true, 'admin',            '1.0.0', 'Naoki Hirata', 'Magic3.org', 'GPL',      10,                '', false,        false,       false, true,   1,        '2014-01-02', now(),         now());
DELETE FROM _widgets WHERE wd_id = 'admin/remotecontent';
INSERT INTO _widgets
(wd_id,                 wd_name,                  wd_admin, wd_category_id, wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_available, wd_editable, wd_has_admin, wd_initialized, wd_launch_index, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('admin/remotecontent', 'リモート表示コンテンツ', true,     'admin',        '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                false,        false,       false,        true,           1,               '2016-02-11',  now(),         now());
-- ウィジェット情報(サーバ連携用)
DELETE FROM _widgets WHERE wd_id = 'c/updateinfo';
INSERT INTO _widgets
(wd_id,          wd_name,    wd_admin, wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,         wd_available, wd_editable, wd_has_admin, wd_initialized, wd_install_dt, wd_create_dt) VALUES
('c/updateinfo', '新着登録(サーバ連携)', true,     '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'サーバ連携で新着情報を登録する。', false,        false,       false,        true,           now(),         now());

-- ウィジェット情報(PC用/メニュー)
DELETE FROM _widgets WHERE wd_id = 'default_menu';
INSERT INTO _widgets
(wd_id,          wd_name,              wd_type, wd_type_option, wd_category_id, wd_template_type, wd_version, wd_required_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_add_script_lib, wd_add_script_lib_a,                         wd_has_admin, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('default_menu', 'デフォルトメニュー', 'menu',  'nav',          'menu',         'bootstrap',      '4.0.0',   '2.9.4',        'Naoki Hirata', 'Magic3.org', 'GPL',      10,                '画面に複数配置が可能な標準のメニューです。単階層または多階層でメニューを定義します。テンプレートに合わせて多様な表示ができます。「user3」ポジションに配置するとナビゲーションタイプのメニューが表示できます。', '', '', true,  true,              true, 2,          1, '2014-12-10', now(),         now());
DELETE FROM _widgets WHERE wd_id = 'breadcrumb';
INSERT INTO _widgets
(wd_id,        wd_name,          wd_type,      wd_category_id, wd_template_type, wd_version, wd_author,                       wd_copyright,                    wd_license, wd_official_level, wd_description,                         wd_add_script_lib, wd_add_script_lib_a, wd_has_admin, wd_initialized, wd_launch_index, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('breadcrumb', 'パンくずリスト', 'breadcrumb', 'menu',         'bootstrap',      '4.2.0',    '株式会社 毎日メディアサービス', '株式会社 毎日メディアサービス', 'GPL',      10,                'メニュー定義からパンくずリストを作成', '',                'jquery.uploadfile', true,        true,           10, '2018-07-27', now(),    now());
-- ウィジェット情報(PC用/汎用コンテンツ)
DELETE FROM _widgets WHERE wd_id = 'default_content';
INSERT INTO _widgets
(wd_id,             wd_name,                                 wd_type,   wd_content_type, wd_category_id, wd_edit_content, wd_version, wd_author, wd_copyright, wd_license, wd_official_level, wd_description, wd_add_script_lib, wd_add_script_lib_a, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('default_content', '汎用コンテンツ-デフォルトコンテンツ', 'content', 'content',       'content',      true,            '3.3.1',  'Naoki Hirata', 'Magic3.org', 'GPL', 10, '汎用コンテンツを管理し、画面に表示します。',          '',           'content_detail=md5,ckeditor_m3toolbar,jquery.tablednd;other=ckeditor_m3toolbar',       true,         true,        true,         true,               false,true,           0, '2018-05-17', now(), now());
DELETE FROM _widgets WHERE wd_id = 'static_content';
INSERT INTO _widgets
(wd_id,            wd_name,                           wd_type, wd_category_id, wd_version, wd_author, wd_copyright, wd_license, wd_official_level, wd_description, wd_add_script_lib, wd_add_script_lib_a, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('static_content', '汎用コンテンツ-固定コンテンツ', '',      'content',      '2.1.0',    'Naoki Hirata', 'Magic3.org', 'GPL', 10, '固定的にコンテンツを表示。', '', '', true,         true,        true,         false,               true,true,           0, 2, 1, '2014-08-13',now(), now());
DELETE FROM _widgets WHERE wd_id = 'content_search_box';
INSERT INTO _widgets
(wd_id,                wd_name,                 wd_category_id, wd_template_type, wd_version, wd_author, wd_copyright, wd_license, wd_official_level, wd_description, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('content_search_box', '汎用コンテンツ-検索', 'content',      'bootstrap',      '2.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL', 10, 'コンテンツを検索するためのボックス。',       true,         true,        false,         false,               false,true,               0, 1, -1, '2014-04-14', now(),    now());
DELETE FROM _widgets WHERE wd_id = 'top_content_box';
INSERT INTO _widgets
(wd_id,             wd_name,                                 wd_category_id, wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_add_script_lib, wd_add_script_lib_a,                         wd_has_admin, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('top_content_box', '汎用コンテンツ-トップアクセスリスト', 'content',      '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                '閲覧数の多い順からコンテンツをリスト表示。', '', '', true,  false,              true, 1,          -1, now(),         now());
DELETE FROM _widgets WHERE wd_id = 'update_content_box';
INSERT INTO _widgets
(wd_id,                wd_name,                       wd_category_id, wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_add_script_lib, wd_add_script_lib_a,                         wd_has_admin, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_has_rss, wd_install_dt, wd_create_dt) VALUES
('update_content_box', '汎用コンテンツ-更新リスト', 'content',      '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                '汎用コンテンツの最新更新リストを表示。', '', '', true,  false,              true, 1,          -1, true, now(),         now());
DELETE FROM _widgets WHERE wd_id = 'featured_content';
INSERT INTO _widgets
(wd_id,              wd_name,                     wd_category_id, wd_version, wd_author, wd_copyright, wd_license, wd_official_level, wd_description, wd_add_script_lib, wd_add_script_lib_a, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('featured_content', '汎用コンテンツ-特集表示', 'content',      '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL', 10, '特集形式でコンテンツを表示。', '', 'jquery.tablednd', true,         false,               true,true,           0, 2, 1, '2013-1-14',now(), now());
-- ウィジェット情報(PC用/ブログ)
DELETE FROM _widgets WHERE wd_id = 'blog_main';
INSERT INTO _widgets
(wd_id,       wd_name,         wd_type, wd_content_type, wd_category_id, wd_template_type, wd_edit_content, wd_version, wd_required_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_add_script_lib, wd_add_script_lib_a, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('blog_main', 'ブログ-メイン', 'blog',  'blog',          'blog',         'bootstrap',      true,            '3.8.0',    '2.15.15',           'Naoki Hirata', 'Magic3.org', 'GPL',      10, 'ブログ記事を管理し、画面に表示します。', 'entry_detail=jquery-ui.tabs,ckeditor_m3toolbar;image=jquery.jcrop,elfinder;schedule_detail=jquery-ui.tabs,ckeditor_m3toolbar;', 'entry_detail=jquery-ui.tabs,ckeditor_m3toolbar;config=ckeditor_m3toolbar,jquery.uploadfile;image=jquery.jcrop,elfinder;analytics=jquery.m3stickyheader,jquery.jqplot;schedule_detail=jquery-ui.tabs,ckeditor_m3toolbar;',       true,         true,        true,        true,               false,true,               '2017-10-18', now(),    now());
DELETE FROM _widgets WHERE wd_id = 'blog_search_box';
INSERT INTO _widgets
(wd_id,             wd_name,         wd_category_id, wd_template_type, wd_version, wd_author, wd_copyright, wd_license, wd_official_level, wd_description, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('blog_search_box', 'ブログ-検索', 'blog',         'bootstrap,wordpress',      '3.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL', 10, 'ブログ記事を検索するためのボックス。',          true,         true,        false,         false,               false,true,               0, 1, -1, '2017-07-29', now(),    now());
DELETE FROM _widgets WHERE wd_id = 'blog_new_box';
INSERT INTO _widgets
(wd_id,          wd_name,         wd_category_id, wd_template_type, wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,             wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_has_rss, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('blog_new_box', 'ブログ-最新', 'blog',        'wordpress', '3.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'ブログの最新記事一覧を表示。', true,        false,               false,true,               1, -1, true, '2017-07-12', now(),    now());
DELETE FROM _widgets WHERE wd_id = 'blog_update_box';
INSERT INTO _widgets
(wd_id,          wd_name,            wd_category_id, wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,             wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_has_rss, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('blog_update_box', 'ブログ-更新', 'blog',         '1.1.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'コメントを含むブログの更新状況を一覧表示。', true,        false,               false,true,               1, -1, true, '2012-10-04', now(),    now());
DELETE FROM _widgets WHERE wd_id = 'blog_calendar_box';
INSERT INTO _widgets
(wd_id,               wd_name,             wd_category_id, wd_template_type, wd_version, wd_author, wd_copyright, wd_license, wd_official_level, wd_description, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('blog_calendar_box', 'ブログ-カレンダー', 'blog',         'wordpress',      '2.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL', 10, 'カレンダーからブログ記事にアクセスするためのボックス。',       true,         true,        false,         false,               false,true,               '2017-07-20', now(),    now());
DELETE FROM _widgets WHERE wd_id = 'blog_category_menu';
INSERT INTO _widgets
(wd_id,                wd_name,                     wd_category_id, wd_template_type, wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('blog_category_menu', 'ブログ-カテゴリーメニュー', 'blog', 'wordpress',        '2.0.1',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'ブログカテゴリメニュー',  true,         true,        false,         false,               false,true,           '2018-01-15',now(), now());
DELETE FROM _widgets WHERE wd_id = 'blog_archive_menu';
INSERT INTO _widgets
(wd_id,               wd_name,                     wd_category_id, wd_template_type, wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('blog_archive_menu', 'ブログ-アーカイブメニュー', 'blog',         'wordpress',      '3.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'ブログアーカイブメニュー',        true,         false,               true,true,           '2017-07-13', now(), now());
DELETE FROM _widgets WHERE wd_id = 'blog_list';
INSERT INTO _widgets
(wd_id,         wd_name,                 wd_category_id, wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,            wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('blog_list',   'ブログ-ブログリスト', 'blog',         '1.1.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'マルチブログリストを表示', true,         true,        false,         false,               false,true,           0, 1, -1, '2012-10-04', now(), now());
DELETE FROM _widgets WHERE wd_id = 'blog_category_box';
INSERT INTO _widgets
(wd_id,               wd_name,                 wd_category_id, wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,             wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_has_rss, wd_install_dt, wd_create_dt) VALUES
('blog_category_box', 'ブログ-選択カテゴリ', 'blog',         '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'ブログの1カテゴリに属する記事の一覧を表示。', true,        false,               true,true,               1, -1, true, now(),    now());
DELETE FROM _widgets WHERE wd_id = 'blog_related_category';
INSERT INTO _widgets
(wd_id,                   wd_name,                 wd_category_id, wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,                                 wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('blog_related_category', 'ブログ-関連カテゴリ', 'blog',         '1.1.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                '表示中のブログ記事に関連したカテゴリを表示。', false,         false,               false,true,           1, -1, '2012-10-04', now(), now());
-- ウィジェット情報(PC用/Eコマース)
DELETE FROM _widgets WHERE wd_id = 'ec_login';
INSERT INTO _widgets
(wd_id,         wd_name,             wd_category_id, wd_version, wd_author,      wd_copyright, wd_license,                wd_official_level, wd_description, wd_add_script_lib, wd_has_admin, wd_initialized, wd_cache_type, wd_view_control_type, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('ec_login', 'Eコマース-ログイン', 'commerce',     '2.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL', 10, 'Eコマースの会員ログイン用ボックス', 'md5',     false,     true,           2, -1, '2012-11-30',now(), now());
DELETE FROM _widgets WHERE wd_id = 'ec_menu';
INSERT INTO _widgets
(wd_id,     wd_name,                    wd_type, wd_category_id, wd_version, wd_joomla_class, wd_author, wd_copyright, wd_license, wd_official_level, wd_description, wd_add_script_lib, wd_add_script_lib_a, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('ec_menu', 'Eコマース-商品メニュー', 'menu',  'commerce',     '2.1.0',    '',        'Naoki Hirata', 'Magic3.org', 'GPL', 10, 'Eコマースの商品メニュー。カテゴリで取得した商品一覧をEコマースメインウィジェットに表示させる。',  '', 'jquery.jstree;menudef_detail=ckeditor_m3toolbar;',        true,         true,        true,         false,               false,true,           0, 1, -1, '2013-1-18', now(), now());
DELETE FROM _widgets WHERE wd_id = 'ec_cart';
INSERT INTO _widgets
(wd_id,     wd_name,              wd_category_id, wd_version, wd_author,      wd_copyright, wd_license,                wd_official_level, wd_description, wd_has_admin, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('ec_cart', 'Eコマース-カート', 'commerce',     '2.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',              10,                'Eコマースでカート内の商品を表示する',   true,    true,           100, 0, 0, '2012-11-24', now(),      now());
DELETE FROM _widgets WHERE wd_id = 'ec_product_header';
INSERT INTO _widgets
(wd_id,               wd_name,                wd_category_id, wd_version, wd_params, wd_author, wd_copyright, wd_license, wd_official_level, wd_description, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('ec_product_header', 'Eコマース-商品ヘッダ', 'commerce',     '1.0.0',  '',  'Naoki Hirata', 'Magic3.org', 'GPL', 10, 'Eコマース商品メニューと連携するウィジェットです。商品メニュー選択時に商品ヘッダコンテンツを表示します。',    true,         true,        false,        false,               false,true,           0, 1, 2, now(), now());
DELETE FROM _widgets WHERE wd_id = 'ec_product_display2';
INSERT INTO _widgets
(wd_id,                 wd_name,                     wd_category_id, wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,                               wd_has_admin, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('ec_product_display2', 'Eコマース-新着おすすめ2', 'commerce',     '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                '商品のおすすめや新着などの一覧を表示する。', true,                       true,true,  0,         1, 1, '2013-1-15', now(), now());
DELETE FROM _widgets WHERE wd_id = 'ec_search_box';
INSERT INTO _widgets
(wd_id,           wd_name,          wd_category_id, wd_version, wd_params, wd_author, wd_copyright, wd_license, wd_official_level, wd_description, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('ec_search_box', 'Eコマース-検索', 'commerce',     '1.0.0',  '',        'Naoki Hirata', 'Magic3.org', 'GPL', 10, 'Eコマース商品検索ボックス。',     true,         true,        false,         false,               false,true,               0, 1, -1, now(),    now());
DELETE FROM _widgets WHERE wd_id = 'ec_product_random';
INSERT INTO _widgets
(wd_id,               wd_name,                        wd_category_id, wd_version, wd_author, wd_copyright, wd_license, wd_official_level, wd_description, wd_add_script_lib, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('ec_product_random', 'Eコマース-商品ランダム表示', 'commerce',     '2.0.0',  'Naoki Hirata', 'Magic3.org', 'GPL', 10, '商品をランダムに表示する。', 'jquery.easing,jquery.jcarousel',    true,         true,        true,        false,               false,true,           0, 0, 0, '2012-12-13', now(), now());
DELETE FROM _widgets WHERE wd_id = 'ec_disp';
INSERT INTO _widgets
(wd_id,     wd_name,                wd_content_type, wd_category_id, wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_add_script_lib_a,                                     wd_has_admin, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('ec_disp', 'Eコマース-商品表示', 'product',       'commerce',     '1.4.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'Eコマースの商品表示機能。商品の説明や一覧を表示。', 'ckeditor_m3toolbar', true,         true, 10, 1,  2, '2013-7-27', now(), now());
DELETE FROM _widgets WHERE wd_id = 'ec_main';
INSERT INTO _widgets
(wd_id,     wd_name,              wd_type,   wd_content_type, wd_category_id, wd_edit_content, wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_add_script_lib_a,  wd_has_admin, wd_initialized, wd_cache_type, wd_view_control_type, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('ec_main', 'Eコマース-メイン', 'product', 'commerce',      'commerce',     true, '2.6.1',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'Eコマースのメインプログラム。会員登録処理や商品購入処理などを行う。', 'product_detail=ckeditor_m3toolbar;image=jquery.jcrop,elfinder;calcorder_detail=ckeditor_m3toolbar;delivmethod_detail=ckeditor_m3toolbar;paymethod_detail=ckeditor_m3toolbar;other=ckeditor_m3toolbar;', true,         true,   0, 2, '2017-12-18', now(), now());
DELETE FROM _widgets WHERE wd_id = 'ec_product_slide';
INSERT INTO _widgets
(wd_id,              wd_name,                        wd_category_id, wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_add_script_lib, wd_add_script_lib_a,   wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('ec_product_slide', 'Eコマース-商品スライド表示', 'commerce',     '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10, '商品画像をカテゴリー単位でスライド表示する。', 'jquery', 'jquery',   true,        false,               false,true,           0, 0, '2012-10-28', now(), now());
-- ウィジェット情報(PC用/フォトギャラリー)
DELETE FROM _widgets WHERE wd_id = 'photo_main';
INSERT INTO _widgets
(wd_id,        wd_name,                     wd_type, wd_content_type, wd_category_id, wd_edit_content, wd_template_type, wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_add_script_lib, wd_add_script_lib_a, wd_use_ajax, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('photo_main', 'フォトギャラリー-メイン', 'photo', 'photo',         'photo',        true,         'bootstrap', '2.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10, 'フォトギャラリーを表示する。', 'jquery.raty', 'search=jquery.tablednd;imagebrowse=jquery.uploadfile;imagebrowse_detail=ckeditor_m3toolbar;',  true,     true,        true,               false,true,       1, 2, '2017-01-08', now(),    now());
DELETE FROM _widgets WHERE wd_id = 'photo_new';
INSERT INTO _widgets
(wd_id,       wd_name,                   wd_category_id, wd_template_type, wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,                       wd_has_admin, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_has_rss, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('photo_new', 'フォトギャラリー-新規', 'photo',      'bootstrap',  '1.1.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'フォトギャラリーの新規一覧を表示。', true,         false,true,               1, -1, true, '2014-09-07', now(),    now());
-- ウィジェット情報(PC用/イベント情報)
DELETE FROM _widgets WHERE wd_id = 'event_main';
INSERT INTO _widgets
(wd_id,        wd_name,               wd_type, wd_content_type, wd_category_id, wd_edit_content, wd_template_type, wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,         wd_add_script_lib, wd_add_script_lib_a, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('event_main', 'イベント情報-メイン', 'event', 'event',         'event',        true,            'bootstrap',      '2.1.1',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'イベント情報を表示する。', '',                'entry_detail=jquery-ui.tabs,ckeditor_m3toolbar;category_detail=jquery.tablednd;config=ckeditor_m3toolbar,jquery.uploadfile;image=jquery.jcrop,elfinder;', true,         true,        true,        true,               false,true,               0, 1, 2, '2015-04-20', now(),    now());
DELETE FROM _widgets WHERE wd_id = 'event_search_box';
INSERT INTO _widgets
(wd_id,              wd_name,               wd_category_id, wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('event_search_box', 'イベント情報-検索', 'event',        '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL', 10,  'イベント情報を検索するためのボックス。',    true,         true,        false,         false,               false,true,               0, 1, -1, now(),    now());
DELETE FROM _widgets WHERE wd_id = 'event_calendar_box';
INSERT INTO _widgets
(wd_id,                wd_name,                     wd_category_id, wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('event_calendar_box', 'イベント情報-カレンダー', 'event',        '1.1.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'イベントを表示するカレンダーボックス。',   true,         true,        false,         false,               false,true,               0, 3, 2, '2013-08-31', now(),    now());
DELETE FROM _widgets WHERE wd_id = 'event_category_menu';
INSERT INTO _widgets
(wd_id,                 wd_name,                             wd_category_id, wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('event_category_menu', 'イベント情報-カテゴリーメニュー', 'event',        '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL', 10, 'イベントカテゴリ一覧を表示。',     false,         false,               false,true,           0, 1, -1, '2013-09-02', now(), now());
DELETE FROM _widgets WHERE wd_id = 'event_category';
INSERT INTO _widgets
(wd_id,            wd_name,                         wd_category_id, wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,             wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_has_rss, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('event_category', 'イベント情報-選択カテゴリー', 'event',        '1.1.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'イベント記事の1カテゴリに属する記事の一覧を表示。', true,        false,               true,true,               1, -1, true, '2013-10-06', now(),    now());
DELETE FROM _widgets WHERE wd_id = 'event_headline';
INSERT INTO _widgets
(wd_id,            wd_name,                 wd_category_id, wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,                 wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_has_rss, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('event_headline', 'イベント情報-ヘッドライン', 'event',        '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'イベントのヘッドライン表示。', true,         false,               false,               true,              true,       '2015-04-13',  now(),         now());
DELETE FROM _widgets WHERE wd_id = 'evententry_main';
INSERT INTO _widgets
(wd_id,             wd_name,               wd_type, wd_content_type, wd_category_id, wd_edit_content, wd_content_widget_id, wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_add_script_lib, wd_add_script_lib_a, wd_available, wd_has_admin, wd_use_instance_def, wd_initialized, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('evententry_main', 'イベント予約-メイン', '', 'evententry',    'event',   true,            '',                   '0.9.0',   'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'イベントの予約管理を行う。', 'login=md5',                '',                true,  true,         false,                true,  '2015-04-29', now(),         now());
DELETE FROM _widgets WHERE wd_id = 'evententry_attachment';
INSERT INTO _widgets
(wd_id,                   wd_name,                       wd_content_type, wd_category_id, wd_edit_content, wd_content_widget_id, wd_template_type, wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,                   wd_add_script_lib, wd_add_script_lib_a,                         wd_has_admin, wd_use_instance_def, wd_initialized, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('evententry_attachment', 'イベント予約-アタッチメント', 'evententry',    'event',        true,            'evententry_main',                   'bootstrap',      '1.1.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'イベント情報に予約機能を付加。', '',                '',                  true,  false,              true, '2015-06-05', now(),         now());
-- ウィジェット情報(PC用/補助コンテンツ)
DELETE FROM _widgets WHERE wd_id = 'banner3';
INSERT INTO _widgets
(wd_id,     wd_name,       wd_content_type, wd_category_id, wd_edit_content, wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_add_script_lib, wd_add_script_lib_a, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('banner3', 'バナー表示3', 'banner',        'subcontent',   true, '3.1.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'バナー画像をグループ化して、グループごとに表示できるバナー管理ウィジェットです。', '',                'elfinder',                  true, true,         true,                true,  0,         1, '2015-01-02', now(),         now());
DELETE FROM _widgets WHERE wd_id = 'calendar';
INSERT INTO _widgets
(wd_id,      wd_name,          wd_content_type, wd_category_id, wd_edit_content, wd_version, wd_required_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_add_script_lib, wd_add_script_lib_a, wd_use_ajax, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('calendar', '汎用カレンダー', 'calendar',      'subcontent',   true,            '2.2.0',   '2.10.7',            'Naoki Hirata', 'Magic3.org', 'GPL',      10,                '様々な情報をカレンダー表示する汎用カレンダー', 'jquery.fullcalendar,jquery.qtip',                'datetype_detail=jquery.tablednd,jquery.timepicker;date_detail=jquery-ui.datepicker,jquery-ui.dialog,jquery.timepicker,jquery.tablednd,jquery.json;event_detail=ckeditor_m3toolbar;',   true,                true, true,         true,                true,  '2015-05-24', now(),         now());
DELETE FROM _widgets WHERE wd_id = 'news_headline';
INSERT INTO _widgets
(wd_id,      wd_name,                      wd_content_type, wd_category_id, wd_edit_content, wd_content_widget_id, wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_add_script_lib, wd_add_script_lib_a, wd_has_admin, wd_enable_operation, wd_has_rss, wd_use_instance_def, wd_initialized, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('news_headline', '新着情報-ヘッドライン', 'news',          'subcontent',   true,            'news_main',          '1.1.1',   'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'サイトの最新情報をリスト表示する。', '',                '',                 true,  false, true,      true,                true,  '2018-01-15', now(),         now());
DELETE FROM _widgets WHERE wd_id = 'news_main';
INSERT INTO _widgets
(wd_id,       wd_name,           wd_content_type, wd_category_id, wd_edit_content, wd_content_widget_id, wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_add_script_lib, wd_add_script_lib_a, wd_available, wd_has_admin, wd_use_instance_def, wd_initialized, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('news_main', '新着情報-メイン', 'news',          'subcontent',   true,            '',                   '1.1.0',   'Naoki Hirata', 'Magic3.org', 'GPL',      10,                '最新情報管理ウィジェット', '',                '',                false,  true,         false,                true,  '2017-02-06', now(),         now());
-- ウィジェット情報(PC用/検索)
DELETE FROM _widgets WHERE wd_id = 'custom_search';
INSERT INTO _widgets
(wd_id,           wd_name,        wd_content_type, wd_category_id, wd_version, wd_author,                       wd_copyright,                    wd_license, wd_official_level, wd_description,         wd_add_script_lib, wd_add_script_lib_a, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('custom_search', 'カスタム検索', 'search',        'search',       '3.1.0',    '株式会社 毎日メディアサービス', '株式会社 毎日メディアサービス', 'GPL',      10,                '各種コンテンツが検索可能で表示レイアウトもカスタマイズ可能な検索ウィジェット', 'jquery', 'jquery.tablednd,ckeditor_m3toolbar,jquery.uploadfile', true, false, true, true,          1, -1, '2018-05-28', now(), now());
DELETE FROM _widgets WHERE wd_id = 'custom_search_box';
INSERT INTO _widgets
(wd_id,               wd_name,            wd_category_id, wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,                                     wd_add_script_lib, wd_add_script_lib_a, wd_has_admin, wd_use_instance_def, wd_initialized, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('custom_search_box', 'カスタム検索連携', 'search',       '2.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'カスタム検索ウィジェットに検索結果を表示する検索ボックス', 'jquery', 'ckeditor_m3toolbar',     true,         true,true,               '2015-10-23', now(),    now());
-- ウィジェット情報(PC用/会員)
DELETE FROM _widgets WHERE wd_id = 'member_main';
INSERT INTO _widgets
(wd_id,          wd_name,          wd_content_type, wd_category_id, wd_edit_content, wd_content_widget_id, wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_add_script_lib, wd_add_script_lib_a, wd_available, wd_has_admin, wd_use_instance_def, wd_initialized, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('member_main', '会員情報-メイン', 'member',          'subcontent',   true,            '',                   '1.0.0',   'Naoki Hirata', 'Magic3.org', 'GPL',      10,                '会員管理ウィジェット', '',                '',                false,  true,         false,                true,  '2015-05-27', now(),         now());
DELETE FROM _widgets WHERE wd_id = 'default_login_box';
INSERT INTO _widgets
(wd_id,               wd_name,              wd_category_id, wd_template_type, wd_version, wd_author, wd_copyright, wd_license, wd_official_level, wd_description, wd_add_script_lib, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('default_login_box', 'デフォルトログイン', 'member',       'bootstrap,wordpress',      '3.0.0',  'Naoki Hirata', 'Magic3.org', 'GPL', 10, '汎用の会員ログイン用ウィジェットです。「汎用会員登録」ウィジェットと連携できます。', 'md5',        true,         true,        true,        false,               false,true,               10, '2017-07-31', now(),    now());
DELETE FROM _widgets WHERE wd_id = 'reg_user';
INSERT INTO _widgets
(wd_id,      wd_name,        wd_content_type, wd_category_id, wd_edit_content, wd_content_widget_id, wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,           wd_add_script_lib, wd_has_admin, wd_enable_operation, wd_initialized, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('reg_user', '汎用会員登録', 'member',        'member',       true,            'member_main',        '2.1.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                '汎用的な会員登録機能です。「デフォルトログイン」ウィジェットと連携できます。', 'md5', true, true,        true,           '2015-05-31', now(), now());
-- ウィジェット情報(PC用/画像)
DELETE FROM _widgets WHERE wd_id = 'pretty_photo';
INSERT INTO _widgets
(wd_id,          wd_name,          wd_category_id, wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_add_script_lib, wd_add_script_lib_a,  wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('pretty_photo', 'プリティフォト', 'image', '2.3.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10, 'サムネール表示した画像を拡大します。', 'jquery.prettyphoto', 'jquery.tablednd,jquery.prettyphoto,elfinder',               true,         true,        true,         true,                                true,                true,              0,  3,             1, '2017-02-20', now(),         now());
DELETE FROM _widgets WHERE wd_id = 'photoslide2';
INSERT INTO _widgets
(wd_id,         wd_name,               wd_category_id, wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_add_script_lib, wd_add_script_lib_a,   wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('photoslide2', '画像スライドショー2', 'image',        '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10, '画像をスライドショー表示する。フォトギャラリー連動可。', 'jquery.cycle', 'jquery.cycle',   true,        false,               true,true,           3, 1, '2012-02-19', now(), now());
DELETE FROM _widgets WHERE wd_id = 'slide_image';
INSERT INTO _widgets
(wd_id,         wd_name,            wd_category_id, wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_add_script_lib, wd_add_script_lib_a,  wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('slide_image', 'スライドイメージ', 'image',        '1.0.1',   'Naoki Hirata', 'Magic3.org', 'GPL',      10, '画像をスライド表示します', 'jquery.bxslider', 'jquery.tablednd,jquery.bxslider,elfinder',              true,         true,                                true,                true,           '2014-08-21', now(),         now());
-- ウィジェット情報(PC用/Wiki)
DELETE FROM _widgets WHERE wd_id = 'wiki_main';
INSERT INTO _widgets
(wd_id,       wd_name,       wd_category_id,    wd_type, wd_content_type, wd_edit_content, wd_template_type, wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,     wd_add_script_lib, wd_add_script_lib_a, wd_has_admin, wd_initialized, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('wiki_main', 'Wiki-メイン', 'wiki',            'wiki',  'wiki',          true,           'bootstrap',      '3.6.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'Wikiを表示する。', 'md5',             'md5',               true,                       true,               '2018-10-07', now(),    now());
DELETE FROM _widgets WHERE wd_id = 'wiki_simple';
INSERT INTO _widgets (
wd_id,          wd_name,                   wd_category_id, wd_template_type, wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,                                   wd_add_script_lib_a, wd_has_admin, wd_use_instance_def, wd_initialized, wd_install_dt, wd_create_dt) VALUES
('wiki_simple', 'Wiki-簡易Wikiコンテンツ', 'wiki',         'bootstrap',      '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'Wiki記法のコンテンツを表示', '', true,         true,                true,          now(), now());
DELETE FROM _widgets WHERE wd_id = 'wiki_update';
INSERT INTO _widgets
(wd_id,                wd_name,                       wd_category_id, wd_template_type, wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,  wd_add_script_lib, wd_add_script_lib_a,                         wd_has_admin, wd_use_instance_def, wd_initialized, wd_has_rss, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('wiki_update', 'Wiki-更新リスト', 'wiki',     'bootstrap',   '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'Wikiコンテンツの最新更新リストを表示。', '', '', true,  false,              true, true, '2014-07-20', now(),         now());
-- ウィジェット情報(PC用/その他)
DELETE FROM _widgets WHERE wd_id = 'custom_header';
INSERT INTO _widgets
(wd_id,           wd_name,          wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_add_script_lib_a, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('custom_header', 'カスタムヘッダ', '1.0.1',    'Naoki Hirata', 'Magic3.org', 'GPL', 10, 'ヘッダ部分で画像やサイトのタイトル文字列をカスタマイズ。',  'elfinder,ckeditor_m3toolbar',        true,         true,        true,        false,               false,true,           0, 3, -1, now(), now());
DELETE FROM _widgets WHERE wd_id = 'custom_footer';
INSERT INTO _widgets
(wd_id,           wd_name,          wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,                             wd_add_script_lib_a,  wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('custom_footer', 'カスタムフッタ', '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'フッタ部に表示し、著作権等の表示を行う。', 'ckeditor_m3toolbar', true,         true,        true,        false,               false,true,           0, 1, -1, now(), now());
DELETE FROM _widgets WHERE wd_id = 'access_count';
INSERT INTO _widgets
(wd_id,          wd_name,              wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,               wd_available, wd_has_admin, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('access_count', 'アクセスカウンター', '2.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'サイトのアクセス数を表示。', false,        true,         false,true,           0, 0, now(), now());
DELETE FROM _widgets WHERE wd_id = 'bbs_login_box';
INSERT INTO _widgets
(wd_id,           wd_name,           wd_category_id, wd_version, wd_params, wd_author, wd_copyright, wd_license, wd_official_level, wd_description, wd_add_script_lib, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('bbs_login_box', '掲示板-ログイン', 'bbs',          '1.0.0',  '',        'Naoki Hirata', 'Magic3.org', 'GPL', 10, '掲示板の会員ログイン用ボックス。', 'md5',      true,         true,        false,        false,               false,true,               0, 2, -1, now(),    now());
DELETE FROM _widgets WHERE wd_id = 'contactus';
INSERT INTO _widgets
(wd_id,       wd_name,            wd_template_type, wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_add_script_lib, wd_add_script_lib_a, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('contactus', '簡易お問い合わせ', 'bootstrap',      '3.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10, 'お問い合わせの入力データをメールで送る。', '', 'ckeditor_m3toolbar',         true,         true,        true,        false,               false,true,               0, 0, 0, '2017-11-20', now(),    now());
DELETE FROM _widgets WHERE wd_id = 'qrcode';
INSERT INTO _widgets
(wd_id,    wd_name,    wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,         wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('qrcode', 'QRコード', '1.1.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'QRコードを作成表示。', true,         true,        true,         true,                false,               true,           '2015-07-03',  now(),         now());
DELETE FROM _widgets WHERE wd_id = 'reserve_main';
INSERT INTO _widgets
(wd_id,          wd_name,       wd_version, wd_params, wd_author, wd_copyright, wd_license, wd_official_level, wd_description, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('reserve_main', '予約-メイン', '1.0.0',  '',        'Naoki Hirata', 'Magic3.org', 'GPL', 10, '予約管理のメインプログラム。',  false,         true,        true,         true,               false,true,           0, 0, 2, now(), now());
DELETE FROM _widgets WHERE wd_id = 'phpcode';
INSERT INTO _widgets
(wd_id,     wd_name,         wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,                  wd_add_script_lib_a,  wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('phpcode', 'PHPコード実行', '2.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'PHPのプログラムコードを実行。', 'jquery.codepress',   false,        true,        true,        false,               true,true,           0, '2016-07-10', now(), now());
DELETE FROM _widgets WHERE wd_id = 'g_analytics';
INSERT INTO _widgets
(wd_id,         wd_name,            wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,                                   wd_available, wd_has_admin, wd_initialized, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('g_analytics', 'Google Analytics', '2.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'Google Analyticsトラッキングコードを出力する。', false,        true,         true,           '2015-04-20',  now(),         now());
DELETE FROM _widgets WHERE wd_id = 'g_analytics2';
INSERT INTO _widgets
(wd_id,          wd_name,             wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,                                    wd_has_admin, wd_initialized, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('g_analytics2', 'Google Analytics2', '0.9.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'グローバルサイトタグ(gtag.js)タイプのGoogle Analyticsトラッキングコードを出力する。', true,         true,           '2018-10-19', now(),         now());
DELETE FROM _widgets WHERE wd_id = 'image2';
INSERT INTO _widgets
(wd_id,    wd_name, wd_version, wd_params, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,             wd_add_script_lib_a, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('image2', '画像2', '2.0.0',    '',        'Naoki Hirata', 'Magic3.org', 'GPL',      10,                '様々な画像を単一で表示。', 'elfinder',          true,         true,        true,         false,               true,                true,           '2018-04-19', now(),         now());
DELETE FROM _widgets WHERE wd_id = 'css_add';
INSERT INTO _widgets
(wd_id,   wd_name, wd_template_type, wd_version, wd_params, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('css_add', 'CSS追加',  'bootstrap,wordpress', '3.0.0',    '',        'Naoki Hirata', 'Magic3.org', 'GPL',      10, 'テンプレートのCSSに加えてCSS定義やCSSファイルを追加するウィジェットです。',    true,         true,        true,         false,                                true,                true,              0,  3,             1, '2018-09-13', now(),         now());
DELETE FROM _widgets WHERE wd_id = 'print';
INSERT INTO _widgets
(wd_id,   wd_name, wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,                                     wd_read_scripts, wd_read_css, wd_add_script_lib, wd_add_script_lib_a, wd_available, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_install_dt, wd_create_dt) VALUES
('print', '印刷',  '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'あらかじめ印刷部分を設定した部分印刷を行います。', true,            false,       'jquery',                       '',     false,              true,         false,               true,                true, now(),         now());
DELETE FROM _widgets WHERE wd_id = 'fontsize';
INSERT INTO _widgets
(wd_id,      wd_name,          wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,                           wd_add_script_lib, wd_add_script_lib_a, wd_available, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('fontsize', 'フォントサイズ', '2.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'フォントサイズを拡大縮小して保持します', 'jquery.cookie',   '',                  false, true,         false,               false,                true, 1,          1, '2014-03-27', now(),         now());
DELETE FROM _widgets WHERE wd_id = 'googlemaps';
INSERT INTO _widgets
(wd_id,         wd_name,       wd_version, wd_author,                       wd_copyright,                    wd_license, wd_official_level, wd_description, wd_add_script_lib, wd_add_script_lib_a, wd_available, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('googlemaps', 'Googleマップ', '3.1.0',    '株式会社 毎日メディアサービス', '株式会社 毎日メディアサービス', 'GPL', 10, 'Goolgeマップを表示。', 'jquery', 'jquery,ckeditor_m3toolbar',              false, true,        false,               true,true,           3, 1, '2017-11-23', now(), now());
DELETE FROM _widgets WHERE wd_id = 'gotop';
INSERT INTO _widgets
(wd_id,   wd_name,            wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,               wd_add_script_lib, wd_add_script_lib_a, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('gotop', 'スクロールトップ', '2.1.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                '画面トップへスクロール移動', 'jquery',          'elfinder',          true,         true,        true,         false,               false,               true,    '2016-08-02', now(), now());
DELETE FROM _widgets WHERE wd_id = 'portal_updateinfo';
INSERT INTO _widgets
(wd_id,               wd_name,                          wd_version, wd_author,                       wd_copyright,                    wd_license, wd_official_level, wd_description, wd_available, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_has_rss, wd_install_dt, wd_create_dt) VALUES
('portal_updateinfo', 'コンテンツ更新情報(ポータル用)', '1.0.0',    '株式会社 毎日メディアサービス', '株式会社 毎日メディアサービス', 'GPL',      10,                'テナントサーバのコンテンツ更新情報を表示', false, true,         false,               false,                true, 0,          0, true, now(),         now());
DELETE FROM _widgets WHERE wd_id = 'bbs_2ch_main';
INSERT INTO _widgets
(wd_id,          wd_name,                  wd_type, wd_content_type, wd_edit_content, wd_category_id, wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,  wd_add_script_lib, wd_add_script_lib_a, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('bbs_2ch_main', '2ちゃんねる風BBSメイン', 'bbs', 'bbs', true,          'bbs',     '1.2.2',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                '2ちゃんねる風掲示板のメイン', 'jquery.cookie',                       'other=elfinder,ckeditor_m3toolbar', true,         true,        true,         false,               false,true,           0, 2, 2, now(), now());
DELETE FROM _widgets WHERE wd_id = 'pdf_list';
INSERT INTO _widgets
(wd_id,      wd_name,   wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,                  wd_add_script_lib, wd_add_script_lib_a, wd_available, wd_has_admin, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('pdf_list', 'PDF名簿', '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'PDF出力も可能な簡易名簿です。', '',                'jquery.tablednd',   false,        true,  true,              true, 3,          1, now(),         now());
DELETE FROM _widgets WHERE wd_id = 'user_content';
INSERT INTO _widgets
(wd_id,          wd_name,            wd_type, wd_content_type, wd_edit_content, wd_version, wd_author,                       wd_copyright,                    wd_license, wd_official_level, wd_description,         wd_add_script_lib, wd_add_script_lib_a, wd_has_admin, wd_enable_operation, wd_initialized, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('user_content', 'ユーザコンテンツ', 'user',  'user', true,         '1.2.1',    '株式会社 毎日メディアサービス', '株式会社 毎日メディアサービス', 'GPL',      10,                'ユーザが定義可能なコンテンツを表示', 'jquery-ui.tabs;content_detail=ckeditor_m3toolbar;', 'category_detail=jquery.tablednd;content_detail=ckeditor_m3toolbar;other=ckeditor_m3toolbar;tab_detail=ckeditor_m3toolbar;', true, true, true,           2, 2, now(), now());
DELETE FROM _widgets WHERE wd_id = 'jquery_plugin';
INSERT INTO _widgets
(wd_id,           wd_name,            wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_add_script_lib, wd_add_script_lib_a, wd_available, wd_has_admin, wd_use_instance_def, wd_initialized, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('jquery_plugin', 'jQueryプラグイン', '1.2.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'jQueryプラグインを追加するためのウィジェットです。画面上には何も表示されません。',  '', 'codemirror.javascript', false,             true,         true,                true,              '2016-07-10', now(),         now());
DELETE FROM _widgets WHERE wd_id = 'head_add';
INSERT INTO _widgets
(wd_id,      wd_name,    wd_template_type, wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_has_admin, wd_initialized, wd_cache_type, wd_view_control_type, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('head_add', 'HEAD追加', 'bootstrap,wordpress', '2.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'HTMLのHEADタグ内に文字列を追加する', true,        true,           3, 1, '2017-07-25',  now(), now());
DELETE FROM _widgets WHERE wd_id = 'contactus_freelayout3';
INSERT INTO _widgets
(wd_id,                   wd_name,                                  wd_status, wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,       wd_add_script_lib, wd_add_script_lib_a, wd_available, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('contactus_freelayout3', 'フリーレイアウトお問い合わせ3(廃止予定)', -1,       '2.3.0',    '株式会社 毎日メディアサービス', '株式会社 毎日メディアサービス', 'GPL',      10,                'フリーレイアウトで作成可能なお問い合わせフォーム機能。「フリーレイアウトお問い合わせ」のバージョンアップ版。', 'jquery.formtips,jquery.format,jquery.calculation,jquery.uploadfile', 'jquery.tablednd,ckeditor_m3toolbar', false, true,         true,               true,                true, 0,          0, '2018-05-31', now(),         now());
DELETE FROM _widgets WHERE wd_id = 'freeform';
INSERT INTO _widgets
(wd_id,      wd_name,          wd_version, wd_author,                       wd_copyright,                    wd_license, wd_official_level, wd_description,    wd_add_script_lib, wd_add_script_lib_a, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('freeform', 'フリーフォーム', '1.0.0',    '株式会社 毎日メディアサービス', '株式会社 毎日メディアサービス', 'GPL',      10,                'フリーレイアウトで作成可能なお問い合わせフォーム機能。「フリーレイアウトお問い合わせ」から名称変更。', 'jquery.formtips,jquery.format,jquery.calculation,jquery.uploadfile', 'jquery.tablednd,ckeditor_m3toolbar', true,         true,               true,                true, 0,          0, '2018-10-25', now(),         now());
DELETE FROM _widgets WHERE wd_id = 'bbs_2ch_search_box';
INSERT INTO _widgets
(wd_id,                wd_name,                 wd_category_id, wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('bbs_2ch_search_box', '2ちゃんねる風BBS-検索', 'bbs',          '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                '2ちゃんねる風BBSの記事を検索するためのボックス。', true,         true,        false,         false,               false,true,               0, 1, -1, now(),    now());
DELETE FROM _widgets WHERE wd_id = 'simple_html';
INSERT INTO _widgets (
wd_id,         wd_name,    wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,                                   wd_add_script_lib_a, wd_has_admin, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('simple_html', '汎用HTML', '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'コンテンツとして管理しない部分表示用の汎用HTML', 'ckeditor_m3toolbar', true,         true,                true,          1, -1, now(), now());
DELETE FROM _widgets WHERE wd_id = 'lang_changer';
INSERT INTO _widgets
(wd_id,          wd_name,    wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_available, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('lang_changer', '言語変更', '2.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                '表示言語変更', false,        false,        false,               false,               true,          '2013-09-19', now(), now());
DELETE FROM _widgets WHERE wd_id = 'ticker';
INSERT INTO _widgets
(wd_id,      wd_name,   wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,                  wd_add_script_lib, wd_add_script_lib_a, wd_has_admin, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('ticker', 'ティッカー', '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'テキストをティッカー表示します。', 'jquery',                'jquery.tablednd',   true,  true,              true, 3,          1, '2012-11-12', now(),         now());
DELETE FROM _widgets WHERE wd_id = 'comment';
INSERT INTO _widgets
(wd_id,     wd_name,        wd_type, wd_content_type, wd_edit_content, wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,                                             wd_add_script_lib, wd_add_script_lib_a, wd_available, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('comment', '汎用コメント', '',      'comment',       true,            '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'メインコンテンツウィジェットに付加する汎用のコメント機能', 'jquery.scrollto', '',                  false,        true, true,         false,                true,  '2013-07-19', now(),         now());
DELETE FROM _widgets WHERE wd_id = 'slogan';
INSERT INTO _widgets
(wd_id,   wd_name, wd_version, wd_params, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_add_script_lib, wd_add_script_lib_a,   wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('slogan', 'スローガン',  '1.1.0',    '',        'Naoki Hirata', 'Magic3.org', 'GPL',      10, 'ヘッダ等の前面にメッセージテキストを表示。レスポンシブウェブ対応。',                      'jquery.fittext',         '',        true,         false,                                true,                true,              '2014-08-09', now(),         now());
DELETE FROM _widgets WHERE wd_id = 'skyway';
INSERT INTO _widgets
(wd_id,    wd_name,  wd_status, wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,             wd_available, wd_use_ajax, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('skyway', 'SkyWay', 1, '0.1.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'SkyWayサンプルプログラム', false, true,        true,         true,                false,               true,           '2018-04-26',  now(),         now());
DELETE FROM _widgets WHERE wd_id = 'repl_chat';
INSERT INTO _widgets
(wd_id,       wd_name,                 wd_status, wd_template_type, wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,                            wd_available, wd_use_ajax, wd_has_admin, wd_enable_operation, wd_hide_title, wd_use_instance_def, wd_initialized, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('repl_chat', 'Repl-AIチャットボット', 1,         'bootstrap',      '0.9.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'Repl-AIサービスと連携したチャットボット', false,        true,        true,         true,                true,          true,                true,           '2018-09-08',  now(),         now());
DELETE FROM _widgets WHERE wd_id = 'luis_chat';
INSERT INTO _widgets
(wd_id,       wd_name,                 wd_status, wd_template_type, wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,                            wd_available, wd_use_ajax, wd_has_admin, wd_enable_operation, wd_hide_title, wd_use_instance_def, wd_initialized, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('luis_chat', 'LUISチャットボット', 1,         '',      '0.9.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'LUISサービスと連携したチャットボット', false,        true,        true,         true,                true,          true,                true,           '2018-10-27',  now(),         now());

-- ウィジェット情報(スマートフォン用/汎用コンテンツ)
DELETE FROM _widgets WHERE wd_id = 's/content';
INSERT INTO _widgets
(wd_id,       wd_parent_id,      wd_name,                   wd_type,   wd_content_type, wd_category_id, wd_edit_content, wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_device_type, wd_add_script_lib, wd_add_script_lib_a, wd_has_admin, wd_enable_operation, wd_initialized, wd_launch_index, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('s/content', 'default_content', '汎用コンテンツ-メイン', 'content', 'content',       'content',      true, '3.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                '汎用コンテンツを管理し、画面に表示します。', 2,         '', 'content_detail=ckeditor_m3toolbar,jquery.tablednd;other=ckeditor_m3toolbar', true, true,         true,           0, '2014-11-22', now(), now());
-- ウィジェット情報(スマートフォン用/ブログ)
DELETE FROM _widgets WHERE wd_id = 's/blog';
INSERT INTO _widgets
(wd_id,    wd_name,           wd_type, wd_content_type, wd_category_id, wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,                      wd_device_type, wd_add_script_lib, wd_add_script_lib_a, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('s/blog', 'ブログ-メイン', 'blog',  'blog',          'blog',         '1.0.3',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'ブログの記事を表示する。jQueryページ対応。', 2,              'jquery.cookie',       'elfinder,ckeditor_m3toolbar',            true, true,                      false,true,           1, 2, '2012-03-26', now(), now());
DELETE FROM _widgets WHERE wd_id = 's/blog_category';
INSERT INTO _widgets
(wd_id,             wd_name,             wd_category_id, wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_device_type, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('s/blog_category', 'ブログ-カテゴリ', 'blog',         '1.0.2',    'Naoki Hirata', 'Magic3.org', 'GPL', 10, 'ブログカテゴリメニュー',          2,       true,         false,               false,true,          1, -1, '2012-03-26', now(), now());
DELETE FROM _widgets WHERE wd_id = 's/blog_archive';
INSERT INTO _widgets
(wd_id,             wd_name,              wd_category_id, wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_device_type, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('s/blog_archive', 'ブログ-アーカイブ', 'blog',         '1.0.1',    'Naoki Hirata', 'Magic3.org', 'GPL', 10, 'ブログアーカイブメニュー',          2,       true,         false,               false,true,          1, -1, '2012-03-26', now(), now());
-- ウィジェット情報(スマートフォン用/フォトギャラリー)
DELETE FROM _widgets WHERE wd_id = 's/photo';
INSERT INTO _widgets
(wd_id,     wd_name,                     wd_type, wd_content_type, wd_category_id, wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_device_type, wd_add_script_lib, wd_add_script_lib_a, wd_use_ajax, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('s/photo', 'フォトギャラリー-メイン', 'photo', 'photo',         'photo',        '0.8.1',    'Naoki Hirata', 'Magic3.org', 'GPL',      10, 'フォトギャラリーを表示する。', 2, 'jquery.raty', 'search=jquery.tablednd;',  true,     true,        true,               false,true,       1, 2, '2012-07-12', now(),    now());
-- ウィジェット情報(スマートフォン用/補助コンテンツ)
DELETE FROM _widgets WHERE wd_id = 's/banner';
INSERT INTO _widgets
(wd_id,      wd_parent_id, wd_name,      wd_content_type, wd_category_id, wd_edit_content, wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_device_type, wd_add_script_lib, wd_add_script_lib_a, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('s/banner', 'banner3',    'バナー表示', 'banner',        'subcontent',   true,            '3.1.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'バナー画像をグループ化して、グループごとに表示できるバナー管理ウィジェットです。', 2, '',                'elfinder',                  true, true,         true,                true,  0,         1, '2015-01-02', now(),         now());
-- ウィジェット情報(スマートフォン用/会員)
DELETE FROM _widgets WHERE wd_id = 's/login';
INSERT INTO _widgets
(wd_id,     wd_name,    wd_category_id, wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,                     wd_device_type, wd_add_script_lib, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('s/login', 'ログイン', 'member',      '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'デフォルトのログイン用ボックス。', 2,              'md5',             false,        false, false,true,               2, -1, '2012-04-03', now(),    now());
-- ウィジェット情報(スマートフォン用/画像)
DELETE FROM _widgets WHERE wd_id = 's/photoslide';
INSERT INTO _widgets
(wd_id,          wd_name,              wd_category_id,  wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_device_type, wd_add_script_lib, wd_add_script_lib_a,   wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('s/photoslide', '画像スライドショー', 'image',         '1.0.1',    'Naoki Hirata', 'Magic3.org', 'GPL',      10, '画像をスライドショー表示する。フォトギャラリー連動可。', 2, 'jquery.cycle', 'jquery.cycle',   true,        false,               true,true,           3, 1, '2012-02-27', now(), now());
-- ウィジェット情報(スマートフォン用/その他)
DELETE FROM _widgets WHERE wd_id = 's/custom_footer';
INSERT INTO _widgets
(wd_id,             wd_name,          wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,                             wd_device_type, wd_add_script_lib_a, wd_has_admin, wd_initialized, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('s/custom_footer', 'カスタムフッタ', '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'フッタ部に表示し、著作権等の表示を行う。', 2, 'ckeditor_m3toolbar',             true,         true,             1, -1,  now(),         now());
DELETE FROM _widgets WHERE wd_id = 's/g_analytics';
INSERT INTO _widgets
(wd_id,         wd_name,            wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_device_type, wd_has_admin, wd_initialized, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('s/g_analytics', 'Google Analytics', '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'Google Analytics トラッキングコードを出力する。', 2, true,        true,           1, -1, now(), now());
DELETE FROM _widgets WHERE wd_id = 's/googlemaps';
INSERT INTO _widgets
(wd_id,          wd_name,        wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,       wd_device_type, wd_add_script_lib, wd_add_script_lib_a, wd_has_admin, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('s/googlemaps', 'Googleマップ', '3.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'Goolgeマップを表示', 2,              'jquery',          'jquery,ckeditor_m3toolbar',            true,                       true,true,           3, 1, '2012-03-19', now(), now());
DELETE FROM _widgets WHERE wd_id = 's/blogparts';
INSERT INTO _widgets
(wd_id,         wd_name,        wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_device_type, wd_has_admin, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('s/blogparts', 'ブログパーツ', '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'ブログパーツを表示', 2, true,         true,true,       1, 1, now(), now());
DELETE FROM _widgets WHERE wd_id = 's/simple_html';
INSERT INTO _widgets
(wd_id,             wd_name,          wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,                             wd_device_type, wd_add_script_lib_a, wd_has_admin, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('s/simple_html', '汎用HTML', '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'コンテンツとして管理しない部分表示用の汎用HTML', 2, 'ckeditor_m3toolbar',             true,         true, true,             1, -1,  now(),         now());
DELETE FROM _widgets WHERE wd_id = 's/css_add';
INSERT INTO _widgets
(wd_id,             wd_name,          wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,                             wd_device_type, wd_has_admin, wd_initialized, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('s/css_add', 'CSS追加', '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'テンプレートのCSSに加えてCSS定義を追加するためのウィジェットです。', 2,              true,         true,             3, 1,  now(),         now());
DELETE FROM _widgets WHERE wd_id = 's/contactus';
INSERT INTO _widgets
(wd_id,         wd_name,            wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,                      wd_device_type, wd_add_script_lib, wd_add_script_lib_a, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('s/contactus', '簡易お問い合わせ', '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'お問い合わせの入力データをメールで送る。', 2,              '',       'ckeditor_m3toolbar',            true, false,                      false,true,           0, 0, now(), now());
DELETE FROM _widgets WHERE wd_id = 's/bbs_2ch';
INSERT INTO _widgets
(wd_id,       wd_name,                   wd_type, wd_content_type, wd_category_id, wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,                wd_device_type, wd_add_script_lib, wd_add_script_lib_a, wd_read_css, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('s/bbs_2ch', '2ちゃんねる風BBS-メイン', 'bbs',   'bbs',           'bbs',          '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                '2ちゃんねる風掲示板のメイン', 2,              'jquery.cookie',                       'elfinder,ckeditor_m3toolbar', true, false,         false,               false,true,           2, 2, '2012-04-13', now(), now());
DELETE FROM _widgets WHERE wd_id = 's/lang_changer';
INSERT INTO _widgets
(wd_id,          wd_name,    wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_device_type, wd_add_script_lib, wd_add_script_lib_a, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('s/lang_changer', '言語変更', '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                '表示言語変更', 2, '',          '',         false,        false,               false,true,           1, -1, '2012-05-21', now(), now());
