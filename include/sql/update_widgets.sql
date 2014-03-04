-- *
-- * _widgetsテーブル更新スクリプト
-- *
-- * PHP versions 5
-- *
-- * LICENSE: This source file is licensed under the terms of the GNU General Public License.
-- *
-- * @package    Magic3 Framework
-- * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
-- * @copyright  Copyright 2006-2014 Magic3 Project.
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
DELETE FROM _widgets WHERE wd_id = 'admin_menu4';
INSERT INTO _widgets
(wd_id,         wd_name,           wd_admin, wd_type, wd_category_id, wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_available, wd_editable, wd_has_admin, wd_initialized, wd_launch_index, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('admin_menu4', '管理用メニュー4', true,     'menu',  'admin', '1.0.0', 'Naoki Hirata', 'Magic3.org', 'GPL',      10,                false,        false,       true, true,           100,     '2014-01-06',        now(),         now());
DELETE FROM _widgets WHERE wd_id = 'admin_main';
INSERT INTO _widgets
(wd_id,        wd_name,      wd_admin, wd_category_id, wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_add_script_lib, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('admin_main', '管理用画面', true,     'admin',            '1.1.0', 'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'pagedef=jquery-ui.tabs;pagedef_mobile=jquery-ui.tabs;pagedef_smartphone=jquery-ui.tabs;menudef=jquery.jstree;menudef_detail=wysiwyg_editor;smenudef_detail=wysiwyg_editor;analyzegraph=jquery.jqplot;adjustwidget=jquery-ui.tabs,wysiwyg_editor;filebrowser=elfinder;editmenu_others=elfinder;initwizard_=bootstrap', false,        false,       false,        true,          false, true, '2013-03-04', now(),now());
DELETE FROM _widgets WHERE wd_id = 'admin/analytics';
INSERT INTO _widgets
(wd_id,             wd_name,            wd_admin, wd_category_id, wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_add_script_lib, wd_available, wd_editable, wd_has_admin, wd_initialized,  wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('admin/analytics', '管理用サイト解析', true,     'admin', '1.1.0', 'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'jquery.jqplot', false,        false,       true, true,           '2013-03-04', now(),         now());
DELETE FROM _widgets WHERE wd_id = 'admin/opelog';
INSERT INTO _widgets
(wd_id,             wd_name,            wd_admin, wd_category_id, wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_add_script_lib, wd_available, wd_editable, wd_has_admin, wd_initialized,  wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('admin/opelog', '管理用運用ログ表示', true,     'admin', '1.2.0', 'Naoki Hirata', 'Magic3.org', 'GPL',      10,                '', false,        false,       true, true,           '2014-01-13', now(),         now());
DELETE FROM _widgets WHERE wd_id = 'admin/loginuser';
INSERT INTO _widgets
(wd_id,             wd_name,                    wd_admin, wd_category_id, wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_add_script_lib, wd_available, wd_editable, wd_has_admin, wd_initialized,  wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('admin/loginuser', '管理用ログインユーザ情報', true,     'admin',            '2.0.0',   'Naoki Hirata', 'Magic3.org', 'GPL',      10,                '', false,        false,       false, true, '2014-01-13',           now(),         now());
DELETE FROM _widgets WHERE wd_id = 'admin/message';
INSERT INTO _widgets
(wd_id,           wd_name,            wd_admin, wd_category_id, wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_add_script_lib, wd_available, wd_editable, wd_has_admin, wd_initialized,  wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('admin/message', '管理用メッセージ出力', true, 'admin',            '1.0.0', 'Naoki Hirata', 'Magic3.org', 'GPL',      10,                '', false,        false,       false, true,           '2014-01-02', now(),         now());

-- ウィジェット情報(PC用)
DELETE FROM _widgets WHERE wd_id = 'default_menu';
INSERT INTO _widgets
(wd_id,          wd_name,              wd_type, wd_category_id, wd_version, wd_joomla_class, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_add_script_lib, wd_add_script_lib_a,                         wd_has_admin, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('default_menu', 'デフォルトメニュー', 'menu',  'menu',         '2.0.0',    '', 'Naoki Hirata', 'Magic3.org', 'GPL',      10,                '複数のメニューが作成できる多階層のデフォルトメニューです。「user3」ポジションに配置するとナビゲーションメニューが表示できます。', '', 'jquery-ui.tabs', true,  true,              true, 2,          1, '2012-06-05', now(),         now());
DELETE FROM _widgets WHERE wd_id = 'accordion_menu';
INSERT INTO _widgets
(wd_id,            wd_name,                  wd_type, wd_category_id, wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_add_script_lib, wd_add_script_lib_a,                         wd_has_admin, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('accordion_menu', 'アコーディオンメニュー', 'menu',  'menu',         '2.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                '複数のメニューが作成できる2階層のアコーディオンメニューです。', 'jquery-ui.accordion', 'jquery-ui.accordion', true,  true,              true, 3,          1, '2012-06-07', now(),         now());
DELETE FROM _widgets WHERE wd_id = 'dropdown_menu';
INSERT INTO _widgets
(wd_id,            wd_name,                 wd_type, wd_category_id, wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_read_scripts, wd_read_css, wd_add_script_lib, wd_add_script_lib_a,                         wd_has_admin, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('dropdown_menu', 'ドロップダウンメニュー', 'menu',  'menu',         '2.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                '複数のメニューが作成できる多階層のドロップダウンメニューです。', true, true, 'jquery.bgiframe,jquery.hoverintent', 'jquery.bgiframe,jquery.hoverintent', true,  true,              true, 3,          1, '2012-06-07', now(),         now());
DELETE FROM _widgets WHERE wd_id = 'breadcrumb';
INSERT INTO _widgets
(wd_id,        wd_name,          wd_category_id, wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,                         wd_has_admin, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('breadcrumb', 'パンくずリスト', 'menu',         '2.1.0',    '株式会社 毎日メディアサービス', '株式会社 毎日メディアサービス', 'GPL',      10,                'メニュー定義からパンくずリストを作成', true,        true,           100, 0, 0, '2013-03-31', now(),    now());
DELETE FROM _widgets WHERE wd_id = 'default_footer';
INSERT INTO _widgets (wd_id, wd_name, wd_type, wd_version, wd_params, wd_author, wd_copyright, wd_license, wd_official_level, wd_description, wd_read_scripts, wd_read_css, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('default_footer',     'デフォルトフッタ',           'DFOT', '1.0.0',  '',        'Naoki Hirata', 'Magic3.org', 'GPL', 10, 'フッタ部分に表示し、著作権の表示を行う。',          false,           false,       true,         true,        false,        false,               false,true,           0, 1, -1, now(), now());
DELETE FROM _widgets WHERE wd_id = 'custom_header';
INSERT INTO _widgets
(wd_id, wd_name, wd_version, wd_params, wd_author, wd_copyright, wd_license, wd_official_level, wd_description, wd_add_script_lib_a, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('custom_header',     'カスタムヘッダ',         '1.0.1',  '',        'Naoki Hirata', 'Magic3.org', 'GPL', 10, 'ヘッダ部分で画像やサイトのタイトル文字列をカスタマイズ。',  'elfinder',        true,         true,        true,        false,               false,true,           0, 3, -1, now(), now());
DELETE FROM _widgets WHERE wd_id = 'ec_login';
INSERT INTO _widgets
(wd_id,         wd_name,                       wd_version, wd_author,      wd_copyright, wd_license,                wd_official_level, wd_description, wd_add_script_lib, wd_has_admin, wd_initialized, wd_cache_type, wd_view_control_type, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('ec_login', 'Eコマース - ログイン', '2.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL', 10, 'Eコマースの会員ログイン用ボックス', 'md5',     false,     true,           2, -1, '2012-11-30',now(), now());
DELETE FROM _widgets WHERE wd_id = 'custom_footer';
INSERT INTO _widgets (wd_id, wd_name, wd_type, wd_version, wd_params, wd_author, wd_copyright, wd_license, wd_official_level, wd_description, wd_read_scripts, wd_read_css, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('custom_footer',     'カスタムフッタ',           'CSFT', '1.0.0',  '',        'Naoki Hirata', 'Magic3.org', 'GPL', 10, 'フッタ部に表示し、著作権等の表示を行う。',          false,           false,       true,         true,        true,        false,               false,true,           0, 1, -1, now(), now());
DELETE FROM _widgets WHERE wd_id = 'separator';
INSERT INTO _widgets (wd_id, wd_name, wd_type, wd_version, wd_params, wd_author, wd_copyright, wd_license, wd_official_level, wd_description, wd_read_scripts, wd_read_css, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('separator',          'セパレータ',                 'SEPT', '1.0.0',  '',        'Naoki Hirata', 'Magic3.org', 'GPL', 10, 'ウィジェット間の区切り線。',          false,           false,       true,         true,        false,         false,               false,true,          0, 1, -1, now(), now());
DELETE FROM _widgets WHERE wd_id = 'templateChanger';
INSERT INTO _widgets (wd_id, wd_name, wd_version, wd_params, wd_author, wd_copyright, wd_license, wd_official_level, wd_description, wd_add_script_lib, wd_add_script_lib_a, wd_read_scripts, wd_read_css, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('templateChanger',    'テンプレートチェンジャー',   '1.0.0',  '',        'Naoki Hirata', 'Magic3.org', 'GPL', 10, 'エンドユーザからテンプレートの変更を可能にする。', 'jquery', '',         false,           false,       true,         true,        false,        false,               false,true,           0, 1, -1, now(), now());
DELETE FROM _widgets WHERE wd_id = 'access_count';
INSERT INTO _widgets
(wd_id,          wd_name,              wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,               wd_has_admin, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('access_count', 'アクセスカウンター', '2.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'サイトのアクセス数を表示。', true,         false,true,           0, 0, now(), now());
DELETE FROM _widgets WHERE wd_id = 'dg_clock';
INSERT INTO _widgets (wd_id, wd_name, wd_type, wd_version, wd_params, wd_author, wd_copyright, wd_license, wd_official_level, wd_description, wd_read_scripts, wd_read_css, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('dg_clock',           'デジタル時計',               'DGCK', '1.0.0',  '',        'Naoki Hirata', 'Magic3.org', 'GPL', 10, 'デジタル時計。',          false,           false,       true,         true,        false,        false,               false,true,           0, 0, -1, now(), now());
DELETE FROM _widgets WHERE wd_id = 'default_content';
INSERT INTO _widgets
(wd_id,             wd_name,                wd_type,   wd_content_type, wd_edit_content, wd_version, wd_author, wd_copyright, wd_license, wd_official_level, wd_description, wd_add_script_lib, wd_add_script_lib_a, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('default_content', '汎用コンテンツ - デフォルトコンテンツ', 'content', 'content', true, '2.3.0',  'Naoki Hirata', 'Magic3.org', 'GPL', 10, '画像やテキストからできた様々なコンテンツを表示。表示コンテンツはURLパラメータで指定。',          '',           'content_detail=jquery.tablednd',       true,         true,        true,         true,               false,true,           0, 2, 2, '2012-08-18', now(), now());
DELETE FROM _widgets WHERE wd_id = 'static_content';
INSERT INTO _widgets (wd_id, wd_name, wd_type, wd_version, wd_params, wd_author, wd_copyright, wd_license, wd_official_level, wd_description, wd_add_script_lib, wd_add_script_lib_a, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('static_content',    '汎用コンテンツ - 固定コンテンツ', '', '2.0.0',  '',        'Naoki Hirata', 'Magic3.org', 'GPL', 10, '固定的にコンテンツを表示。', '', 'jquery-ui.tabs', true,         true,        true,         false,               true,true,           0, 2, 1, '2012-12-28',now(), now());
DELETE FROM _widgets WHERE wd_id = 'content_search_box';
INSERT INTO _widgets (wd_id, wd_name, wd_version, wd_params, wd_author, wd_copyright, wd_license, wd_official_level, wd_description, wd_read_scripts, wd_read_css, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('content_search_box',   '汎用コンテンツ - 検索', '1.0.0',  '',        'Naoki Hirata', 'Magic3.org', 'GPL', 10, 'コンテンツを検索するためのボックス。',        false,           false,       true,         true,        false,         false,               false,true,               0, 1, -1, now(),    now());
DELETE FROM _widgets WHERE wd_id = 'news';
INSERT INTO _widgets (wd_id, wd_name, wd_type, wd_version, wd_params, wd_author, wd_copyright, wd_license, wd_official_level, wd_description, wd_read_scripts, wd_read_css, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('news',    'お知らせ', 'NEWS', '1.0.0',  '',        'Naoki Hirata', 'Magic3.org', 'GPL', 10, 'お知らせを表示。',          false,           false,       true,         true,        true,         false,               true,true,           0, 1, 1, now(), now());
DELETE FROM _widgets WHERE wd_id = 'blogparts_box';
INSERT INTO _widgets (wd_id, wd_name, wd_type, wd_version, wd_params, wd_author, wd_copyright, wd_license, wd_official_level, wd_description, wd_read_scripts, wd_read_css, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('blogparts_box',    'ブログパーツ', 'BPAR', '1.0.0',  '',        'Naoki Hirata', 'Magic3.org', 'GPL', 10, 'ブログパーツを表示。JavaScriptの場合はスクリプトを「script」タグで囲みます。',          false,           false,       true,         true,        true,         false,               true,true,           0, 1, 1, now(), now());
DELETE FROM _widgets WHERE wd_id = 'ec_menu';
INSERT INTO _widgets
(wd_id,     wd_name,  wd_type,                  wd_version, wd_joomla_class, wd_author, wd_copyright, wd_license, wd_official_level, wd_description, wd_add_script_lib, wd_add_script_lib_a, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('ec_menu', 'Eコマース - 商品メニュー', 'menu', '2.1.0',    '',        'Naoki Hirata', 'Magic3.org', 'GPL', 10, 'Eコマースの商品メニュー。カテゴリで取得した商品一覧をEコマースメインウィジェットに表示させる。',  '', 'jquery.jstree',        true,         true,        true,         false,               false,true,           0, 1, -1, '2013-1-18', now(), now());
DELETE FROM _widgets WHERE wd_id = 'ec_category_menu';
INSERT INTO _widgets (wd_id, wd_name, wd_type, wd_version, wd_params, wd_author, wd_copyright, wd_license, wd_official_level, wd_description, wd_read_scripts, wd_read_css, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('ec_category_menu',   'Eコマース - カテゴリーメニュー', 'ECCM', '1.0.0',  '',        'Naoki Hirata', 'Magic3.org', 'GPL', 10, 'Eコマースカテゴリメニュー',          false,           false,       true,         true,        true,         false,               false,true,           0, 1, -1, now(), now());
DELETE FROM _widgets WHERE wd_id = 'ec_cart';
INSERT INTO _widgets 
(wd_id,        wd_name,                     wd_version, wd_author,      wd_copyright, wd_license,                wd_official_level, wd_description, wd_has_admin, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('ec_cart', 'Eコマース - カート', '2.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',              10,                'Eコマースでカート内の商品を表示する',   true,    true,           100, 0, 0, '2012-11-24', now(),      now());
DELETE FROM _widgets WHERE wd_id = 'ec_product_header';
INSERT INTO _widgets (wd_id, wd_name, wd_type, wd_version, wd_params, wd_author, wd_copyright, wd_license, wd_official_level, wd_description, wd_read_scripts, wd_read_css, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('ec_product_header',               'Eコマース - 商品ヘッダ',                   'ECHD', '1.0.0',  '',  'Naoki Hirata', 'Magic3.org', 'GPL', 10, 'Eコマース商品メニューと連携するウィジェットです。商品メニュー選択時に商品ヘッダコンテンツを表示します。',     false,           false,       true,         true,        false,        false,               false,true,           0, 1, 2, now(), now());
DELETE FROM _widgets WHERE wd_id = 'ec_product_display2';
INSERT INTO _widgets
(wd_id,                 wd_name,                     wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,                               wd_has_admin, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('ec_product_display2', 'Eコマース - 新着おすすめ2', '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                '商品のおすすめや新着などの一覧を表示する。', true,                       true,true,  0,         1, 1, '2013-1-15', now(), now());
DELETE FROM _widgets WHERE wd_id = 'ec_search_box';
INSERT INTO _widgets (wd_id, wd_name, wd_type, wd_version, wd_params, wd_author, wd_copyright, wd_license, wd_official_level, wd_description, wd_read_scripts, wd_read_css, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('ec_search_box',   'Eコマース - 検索',            'ECSR', '1.0.0',  '',        'Naoki Hirata', 'Magic3.org', 'GPL', 10, 'Eコマース商品検索ボックス。',         false,           false,       true,         true,        false,         false,               false,true,               0, 1, -1, now(),    now());
DELETE FROM _widgets WHERE wd_id = 'blog_main';
INSERT INTO _widgets
(wd_id,       wd_name,           wd_type, wd_content_type, wd_edit_content, wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_add_script_lib, wd_add_script_lib_a, wd_read_scripts, wd_read_css, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('blog_main', 'ブログ - メイン', 'blog', 'blog', true,         '2.2.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10, 'ブログ記事を表示する。', 'entry_detail=jquery-ui.tabs', 'entry_detail=jquery-ui.tabs',          false,           false,       true,         true,        true,        true,               false,true,               0, 1, 2, '2013-12-18', now(),    now());
DELETE FROM _widgets WHERE wd_id = 'blog_search_box';
INSERT INTO _widgets (wd_id, wd_name, wd_type, wd_version, wd_params, wd_author, wd_copyright, wd_license, wd_official_level, wd_description, wd_read_scripts, wd_read_css, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('blog_search_box',   'ブログ - 検索',            'BGSR', '1.0.0',  '',        'Naoki Hirata', 'Magic3.org', 'GPL', 10, 'ブログ記事を検索するためのボックス。',        false,           false,       true,         true,        false,         false,               false,true,               0, 1, -1, now(),    now());
DELETE FROM _widgets WHERE wd_id = 'blog_new_box';
INSERT INTO _widgets
(wd_id,          wd_name,         wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,             wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_has_rss, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('blog_new_box', 'ブログ - 新規', '2.1.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'ブログの新規一覧を表示。', true,        false,               false,true,               1, -1, true, '2012-10-04', now(),    now());
DELETE FROM _widgets WHERE wd_id = 'blog_update_box';
INSERT INTO _widgets
(wd_id,          wd_name,         wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,             wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_has_rss, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('blog_update_box', 'ブログ - 更新', '1.1.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'コメントを含むブログの更新状況を一覧表示。', true,        false,               false,true,               1, -1, true, '2012-10-04', now(),    now());
DELETE FROM _widgets WHERE wd_id = 'blog_calendar_box';
INSERT INTO _widgets 
(wd_id,               wd_name,               wd_version, wd_author, wd_copyright, wd_license, wd_official_level, wd_description, wd_read_scripts, wd_read_css, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('blog_calendar_box', 'ブログ - カレンダー', '1.1.0',    'Naoki Hirata', 'Magic3.org', 'GPL', 10, 'カレンダーからブログ記事にアクセスするためのボックス。',        false,           false,       true,         true,        false,         false,               false,true,               0, 3, 2,  '2012-10-04', now(),    now());
DELETE FROM _widgets WHERE wd_id = 'bbs_login_box';
INSERT INTO _widgets (wd_id, wd_name, wd_type, wd_version, wd_params, wd_author, wd_copyright, wd_license, wd_official_level, wd_description, wd_add_script_lib, wd_read_scripts, wd_read_css, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('bbs_login_box',         '掲示板 - ログイン',            'BBML', '1.0.0',  '',        'Naoki Hirata', 'Magic3.org', 'GPL', 10, '掲示板の会員ログイン用ボックス。', 'md5',        false,           false,       true,         true,        false,        false,               false,true,               0, 2, -1, now(),    now());
DELETE FROM _widgets WHERE wd_id = 'contactus';
INSERT INTO _widgets (wd_id, wd_name, wd_type, wd_version, wd_params, wd_author, wd_copyright, wd_license, wd_official_level, wd_description, wd_read_scripts, wd_read_css, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('contactus',          '簡易お問い合わせ',            'CONU', '1.1.0',  '',        'Naoki Hirata', 'Magic3.org', 'GPL', 10, 'お問い合わせの入力データをメールで送る。',          false,           false,       true,         true,        true,        false,               false,true,               0, 0, 0, '2012-06-09', now(),    now());
DELETE FROM _widgets WHERE wd_id = 'joomla_clock';
INSERT INTO _widgets (wd_id, wd_name, wd_type, wd_version, wd_params, wd_author, wd_copyright, wd_license, wd_official_level, wd_description, wd_read_scripts, wd_read_css, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('joomla_clock',           'Joomla時計',               'JMCK', '1.0.0',  '',        'Naoki Hirata', 'Magic3.org', 'GPL', 10, 'Joomlaロゴの時計。',          false,           false,       true,         true,        false,        false,               false,true,           0, 1, -1, now(), now());
DELETE FROM _widgets WHERE wd_id = 'qrcode';
INSERT INTO _widgets (wd_id, wd_name, wd_type, wd_version, wd_params, wd_author, wd_copyright, wd_license, wd_official_level, wd_description, wd_read_scripts, wd_read_css, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('qrcode',           'QRコード',               'QRVW', '1.0.0',  '',        'Naoki Hirata', 'Magic3.org', 'GPL', 10, 'QRコードを作成表示。',         false,           false,       true,         true,        true,        true,               false,true,           0, 1, -1, now(), now());
DELETE FROM _widgets WHERE wd_id = 'youtube2';
INSERT INTO _widgets (wd_id, wd_name, wd_type, wd_version, wd_params, wd_author, wd_copyright, wd_license, wd_official_level, wd_description, wd_read_scripts, wd_read_css, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('youtube2',     'YouTube2',           'YOUT', '1.0.0',  '',        'Naoki Hirata', 'Magic3.org', 'GPL', 10, 'YouTubeの投稿動画を表示。',         false,           false,       true,         true,        true,        false,               true,true,           0, 1, 1, now(), now());
DELETE FROM _widgets WHERE wd_id = 'flash';
INSERT INTO _widgets (wd_id, wd_name, wd_type, wd_version, wd_params, wd_author, wd_copyright, wd_license, wd_official_level, wd_description, wd_add_script_lib_a, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('flash',     'Flash',           '', '1.1.0',  '',        'Naoki Hirata', 'Magic3.org', 'GPL', 10, 'Flashファイルを表示。',         'elfinder', true,         true,        true,        false,               true,true,           0, 1, 1, now(), now());
DELETE FROM _widgets WHERE wd_id = 'release_info';
INSERT INTO _widgets (wd_id, wd_name, wd_type, wd_version, wd_params, wd_author, wd_copyright, wd_license, wd_official_level, wd_description, wd_read_scripts, wd_read_css, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('release_info',               'Magic3リリース情報',                   'RINF', '1.0.0',  '',  'Naoki Hirata', 'Magic3.org', 'GPL', 10, 'Magic3の最新リリース情報を表示。',      false,           false,       true,         true,        false,        false,               false,true,           0, 1, -1, now(), now());
DELETE FROM _widgets WHERE wd_id = 'reserve_main';
INSERT INTO _widgets (wd_id, wd_name, wd_type, wd_version, wd_params, wd_author, wd_copyright, wd_license, wd_official_level, wd_description, wd_read_scripts, wd_read_css, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('reserve_main',            '予約 - メイン',         'RESM', '1.0.0',  '',        'Naoki Hirata', 'Magic3.org', 'GPL', 10, '予約管理のメインプログラム。',          false,           false,       true,         true,        true,         true,               false,true,           0, 0, 2, now(), now());
DELETE FROM _widgets WHERE wd_id = 'ec_product_random';
INSERT INTO _widgets
(wd_id, wd_name, wd_version, wd_params, wd_author, wd_copyright, wd_license, wd_official_level, wd_description, wd_add_script_lib, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('ec_product_random',               'Eコマース - 商品ランダム表示', '2.0.0',  '',  'Naoki Hirata', 'Magic3.org', 'GPL', 10, '商品をランダムに表示する。', 'jquery.easing,jquery.jcarousel',    true,         true,        true,        false,               false,true,           0, 0, 0, '2012-12-13', now(), now());
DELETE FROM _widgets WHERE wd_id = 'blog_category_menu';
INSERT INTO _widgets (wd_id, wd_name, wd_type, wd_version, wd_params, wd_author, wd_copyright, wd_license, wd_official_level, wd_description, wd_read_scripts, wd_read_css, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('blog_category_menu',   'ブログ - カテゴリーメニュー', 'BGCM', '1.0.0',  '',        'Naoki Hirata', 'Magic3.org', 'GPL', 10, 'ブログカテゴリメニュー',          false,           false,       true,         true,        false,         false,               false,true,           0, 1, -1, now(), now());
DELETE FROM _widgets WHERE wd_id = 'blog_archive_menu';
INSERT INTO _widgets
(wd_id, wd_name, wd_version, wd_author, wd_copyright, wd_license, wd_official_level, wd_description, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('blog_archive_menu',   'ブログ - アーカイブメニュー', '2.0.0',  'Naoki Hirata', 'Magic3.org', 'GPL', 10, 'ブログアーカイブメニュー',        true,         false,               true,true,           0, 1, -1, '2013-09-22', now(), now());
DELETE FROM _widgets WHERE wd_id = 'phpcode';
INSERT INTO _widgets (wd_id, wd_name, wd_type, wd_version, wd_params, wd_author, wd_copyright, wd_license, wd_official_level, wd_description, wd_add_script_lib_a, wd_read_scripts, wd_read_css, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('phpcode',     'PHPコード実行',           '', '1.0.0',  '',        'Naoki Hirata', 'Magic3.org', 'GPL', 10, 'PHPのプログラムコードを実行。', 'jquery.codepress',        false,           false,       true,         true,        true,        false,               true,true,           0, 0, 0, now(), now());
DELETE FROM _widgets WHERE wd_id = 'g_analytics';
INSERT INTO _widgets
(wd_id,         wd_name,            wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_has_admin, wd_initialized, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('g_analytics', 'Google Analytics', '2.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'Google Analytics トラッキングコードを出力する。', true,        true,           1, -1, now(), now());
DELETE FROM _widgets WHERE wd_id = 'g_qrcode';
INSERT INTO _widgets (wd_id, wd_name, wd_type, wd_version, wd_params, wd_author, wd_copyright, wd_license, wd_official_level, wd_description, wd_read_scripts, wd_read_css, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('g_qrcode',     'Google QRコード',           '', '1.0.0',  '',        'Naoki Hirata', 'Magic3.org', 'GPL', 10, 'Google APIを使ったQRコード表示。',         false,           false,       true,         true,        true,        false,               true,true,           0, 1, 1, now(), now());
DELETE FROM _widgets WHERE wd_id = 'wiki_main';
INSERT INTO _widgets
(wd_id,       wd_name,         wd_type, wd_content_type, wd_edit_content, wd_version, wd_author, wd_copyright, wd_license, wd_official_level, wd_description, wd_add_script_lib, wd_add_script_lib_a, wd_read_scripts, wd_read_css, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('wiki_main', 'Wiki - メイン', 'wiki', 'wiki', true,         '1.6.0',    'Naoki Hirata', 'Magic3.org', 'GPL', 10, 'Wikiを表示する。', 'md5', 'md5',         false,           true,       true,         true,        true,        false,               false,true,               0, 1, 2, '2012-06-09', now(),    now());
DELETE FROM _widgets WHERE wd_id = 'default_login_box';
INSERT INTO _widgets
(wd_id, wd_name, wd_version, wd_author, wd_copyright, wd_license, wd_official_level, wd_description, wd_add_script_lib, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('default_login_box',         'デフォルトログイン',   '2.1.0',  'Naoki Hirata', 'Magic3.org', 'GPL', 10, '汎用のユーザログイン用ウィジェットです。「汎用ユーザ登録」ウィジェットと連携できます。', 'md5',        true,         true,        true,        false,               false,true,               0, 2, -1, '2013-04-21', now(),    now());
DELETE FROM _widgets WHERE wd_id = 'top_content_box';
INSERT INTO _widgets
(wd_id,             wd_name,                    wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_read_scripts, wd_read_css, wd_add_script_lib, wd_add_script_lib_a,                         wd_has_admin, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('top_content_box', '汎用コンテンツ - トップアクセスリスト', '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                '閲覧数の多い順からコンテンツをリスト表示。', false, false, '', '', true,  false,              true, 1,          -1, now(),         now());
DELETE FROM _widgets WHERE wd_id = 'update_content_box';
INSERT INTO _widgets
(wd_id,             wd_name,                    wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_read_scripts, wd_read_css, wd_add_script_lib, wd_add_script_lib_a,                         wd_has_admin, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_has_rss, wd_install_dt, wd_create_dt) VALUES
('update_content_box', '汎用コンテンツ - 更新リスト', '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                '最新の更新からコンテンツをリスト表示。', false, false, '', '', true,  false,              true, 1,          -1, true, now(),         now());
DELETE FROM _widgets WHERE wd_id = 'image2';
INSERT INTO _widgets
(wd_id,   wd_name, wd_version, wd_params, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_add_script_lib_a, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('image2', '画像2',  '1.0.0',    '',        'Naoki Hirata', 'Magic3.org', 'GPL',      10, '様々な画像を単一で表示。', 'elfinder',             true,         true,        true,         false,                                true,                true,              0, 1,              1, now(),         now());
DELETE FROM _widgets WHERE wd_id = 'pretty_photo';
INSERT INTO _widgets
(wd_id,          wd_name,          wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_add_script_lib, wd_add_script_lib_a,  wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('pretty_photo', 'プリティフォト', '2.1.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10, 'サムネール表示した画像を拡大します。', 'jquery.prettyphoto', 'jquery.tablednd,jquery.prettyphoto,elfinder',               true,         true,        true,         true,                                true,                true,              0,  3,             1, '2013-09-15', now(),         now());
DELETE FROM _widgets WHERE wd_id = 'css_add';
INSERT INTO _widgets
(wd_id,   wd_name, wd_version, wd_params, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_read_scripts, wd_read_css, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('css_add', 'CSS追加',  '2.0.0',    '',        'Naoki Hirata', 'Magic3.org', 'GPL',      10, 'テンプレートのCSSに加えてCSS定義を追加するためのウィジェットです。',               false,           false,       true,         true,        true,         false,                                true,                true,              0,  3,             1, now(),         now());
DELETE FROM _widgets WHERE wd_id = 'effect_nicejforms';
INSERT INTO _widgets
(wd_id,   wd_name, wd_version, wd_params, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_add_script_lib, wd_add_script_lib_a, wd_read_scripts, wd_read_css, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('effect_nicejforms', 'effect_nicejforms',  '1.0.0',    '',        'Naoki Hirata', 'Magic3.org', 'GPL',      10, 'フォーム部品を装飾するウィジェット。', 'jquery', 'jquery',              true,           false,       true,         true,        true,         false,                                true,                true,              0, 1,              1, now(),         now());
DELETE FROM _widgets WHERE wd_id = 'contactus_modal';
INSERT INTO _widgets
(wd_id,   wd_name, wd_version, wd_params, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_add_script_lib, wd_add_script_lib_a, wd_read_scripts, wd_read_css, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('contactus_modal', 'モーダル型お問い合わせ',  '1.0.0',    '',        'Naoki Hirata', 'Magic3.org', 'GPL',      10, 'モーダルで起動するお問い合わせウィジェット。', 'jquery.simplemodal', 'jquery.simplemodal',              true,           true,       true,         true,        true,         true,                                true,                true, 0,             0,               0, now(),         now());
DELETE FROM _widgets WHERE wd_id = 'contactus_custom';
INSERT INTO _widgets
(wd_id,              wd_name,                          wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,                             wd_read_scripts, wd_read_css,wd_add_script_lib, wd_add_script_lib_a, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('contactus_custom', 'カスタムお問い合わせ', '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'カスタマイズ可能なお問い合わせメール送信', false,           false,     '', 'jquery.tablednd', true,         false,               true,                true, 0,          0, now(),         now());
DELETE FROM _widgets WHERE wd_id = 'print';
INSERT INTO _widgets
(wd_id,   wd_name, wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,                                     wd_read_scripts, wd_read_css, wd_add_script_lib, wd_add_script_lib_a, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('print', '印刷',  '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'あらかじめ印刷部分を設定した部分印刷を行います。', true,            false,       'jquery',                       '',                  true,         false,               true,                true, 1,          1, now(),         now());
DELETE FROM _widgets WHERE wd_id = 'reg_user';
INSERT INTO _widgets
(wd_id,      wd_name,          wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,           wd_add_script_lib, wd_has_admin, wd_enable_operation, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('reg_user', '汎用ユーザ登録', '2.0.1',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                '汎用的なユーザ登録機能です。「デフォルトログイン」ウィジェットと連携できます。', 'md5', true, true,        true,           0, 0,              0, '2012-09-19', now(), now());
DELETE FROM _widgets WHERE wd_id = 'ec_disp';
INSERT INTO _widgets
(wd_id,     wd_name,                wd_content_type, wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,                                      wd_has_admin, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('ec_disp', 'Eコマース - 商品表示', 'product',       '1.4.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'Eコマースの商品表示機能。商品の説明や一覧を表示。', true,         true, 10, 1,  2, '2013-7-27', now(), now());
DELETE FROM _widgets WHERE wd_id = 'ec_main';
INSERT INTO _widgets
(wd_id,     wd_name,              wd_type, wd_content_type, wd_edit_content, wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,                                                        wd_has_admin, wd_initialized, wd_cache_type, wd_view_control_type, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('ec_main', 'Eコマース - メイン', 'product', 'commerce', true, '2.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'Eコマースのメインプログラム。会員登録処理や商品購入処理などを行う。', true,         true,   0, 2, '2012-12-07', now(), now());
DELETE FROM _widgets WHERE wd_id = 'fontsize';
INSERT INTO _widgets
(wd_id,   wd_name, wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,                                     wd_read_scripts, wd_read_css, wd_add_script_lib, wd_add_script_lib_a, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('fontsize', 'フォントサイズ',  '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'フォントサイズを拡大縮小して保持します', true,            true,       'jquery.cookie',                       '',                  true,         false,               false,                true, 1,          1, now(),         now());
DELETE FROM _widgets WHERE wd_id = 'googlemaps';
INSERT INTO _widgets
(wd_id,        wd_name,        wd_version, wd_author,                       wd_copyright,                    wd_license, wd_official_level, wd_description, wd_add_script_lib, wd_add_script_lib_a, wd_read_scripts, wd_read_css, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('googlemaps', 'Googleマップ', '3.0.0',    '株式会社 毎日メディアサービス', '株式会社 毎日メディアサービス', 'GPL', 10, 'Goolgeマップを表示。', 'jquery', 'jquery',        false,           false,       true,        false,               true,true,           3, 1, '2012-03-19', now(), now());
DELETE FROM _widgets WHERE wd_id = 'gotop';
INSERT INTO _widgets
(wd_id,   wd_name,        wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,     wd_add_script_lib, wd_add_script_lib_a, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('gotop', '上へ参ります', '1.1.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                '画面トップへ移動', 'jquery',          'elfinder',                  true,         true,        true,        false,               false,               true,           1,             -1, '2013-11-18', now(), now());
DELETE FROM _widgets WHERE wd_id = 'slide_menu';
INSERT INTO _widgets
(wd_id,        wd_name,           wd_type, wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_add_script_lib, wd_add_script_lib_a,                         wd_has_admin, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('slide_menu', 'スライドメニュー', 'menu', '2.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'スライドオープンできる2階層のメニューです。', 'jquery', 'jquery', true,  true,              true, 3,          1, '2012-06-07', now(),         now());
DELETE FROM _widgets WHERE wd_id = 'portal_updateinfo';
INSERT INTO _widgets
(wd_id,              wd_name,                          wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_has_rss, wd_install_dt, wd_create_dt) VALUES
('portal_updateinfo', 'コンテンツ更新情報(ポータル用)', '1.0.0',    '株式会社 毎日メディアサービス', '株式会社 毎日メディアサービス', 'GPL',      10,                'テナントサーバのコンテンツ更新情報を表示', true,         false,               false,                true, 0,          0, true, now(),         now());
DELETE FROM _widgets WHERE wd_id = 'bbs_2ch_main';
INSERT INTO _widgets
(wd_id,          wd_name,                  wd_type, wd_content_type, wd_edit_content, wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,                wd_read_scripts, wd_read_css, wd_add_script_lib, wd_add_script_lib_a, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('bbs_2ch_main', '2ちゃんねる風BBSメイン', 'bbs', 'bbs', true,          '1.2.2',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                '2ちゃんねる風掲示板のメイン', false,           false,       'jquery.cookie',                       'elfinder', true,         true,        true,         false,               false,true,           0, 2, 2, now(), now());
DELETE FROM _widgets WHERE wd_id = 'pdf_list';
INSERT INTO _widgets
(wd_id,      wd_name,   wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,                  wd_add_script_lib, wd_add_script_lib_a, wd_has_admin, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('pdf_list', 'PDF名簿', '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'PDF出力も可能な簡易名簿です。', '',                'jquery.tablednd',   true,  true,              true, 3,          1, now(),         now());
DELETE FROM _widgets WHERE wd_id = 'user_content';
INSERT INTO _widgets
(wd_id,          wd_name,            wd_type, wd_content_type, wd_edit_content, wd_version, wd_author,                       wd_copyright,                    wd_license, wd_official_level, wd_description,         wd_add_script_lib, wd_add_script_lib_a, wd_has_admin, wd_enable_operation, wd_initialized, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('user_content', 'ユーザコンテンツ', 'user',  'user', true,         '1.2.1',    '株式会社 毎日メディアサービス', '株式会社 毎日メディアサービス', 'GPL',      10,                'ユーザが定義可能なコンテンツを表示', 'jquery-ui.tabs', 'jquery.tablednd', true, true, true,           2, 2, now(), now());
DELETE FROM _widgets WHERE wd_id = 'custom_search';
INSERT INTO _widgets
(wd_id,           wd_name,        wd_content_type, wd_version, wd_author,                       wd_copyright,                    wd_license, wd_official_level, wd_description,         wd_add_script_lib, wd_add_script_lib_a, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('custom_search', 'カスタム検索', 'search',      '2.0.0',    '株式会社 毎日メディアサービス', '株式会社 毎日メディアサービス', 'GPL',      10,                '各種コンテンツが検索可能で表示レイアウトもカスタマイズ可能な検索ウィジェット', 'jquery', 'jquery.tablednd', true, false, true, true,          1, -1, '2013-04-29', now(), now());
DELETE FROM _widgets WHERE wd_id = 'contactus_freelayout2';
INSERT INTO _widgets
(wd_id,              wd_name,                          wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,                             wd_read_scripts, wd_read_css,wd_add_script_lib, wd_add_script_lib_a, wd_available, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('contactus_freelayout2', 'フリーレイアウトお問い合わせ2(廃止予定)', '1.0.0',    '株式会社 毎日メディアサービス', '株式会社 毎日メディアサービス', 'GPL',      10,                'フリーレイアウトでカスタマイズ可能なお問い合わせメール送信機能。「フリーレイアウトお問い合わせ」のバージョンアップ版。', false,           false,     'jquery.formtips,jquery.format', 'jquery.tablednd', false, true,         false,               true,                true, 0,          0, now(),         now());
DELETE FROM _widgets WHERE wd_id = 'banner3';
INSERT INTO _widgets
(wd_id,     wd_name,       wd_content_type, wd_edit_content, wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_add_script_lib, wd_add_script_lib_a, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('banner3', 'バナー表示3', 'banner', true, '3.0.1',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'バナー画像をグループ化して、グループごとに表示できるバナー管理ウィジェットです。', '',                'elfinder',                  true, true,         true,                true,  0,         1, '2013-12-28', now(),         now());
DELETE FROM _widgets WHERE wd_id = 'jquery_plugin';
INSERT INTO _widgets
(wd_id,        wd_name,                wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_add_script_lib, wd_add_script_lib_a, wd_has_admin, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type,    wd_view_control_type, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('jquery_plugin', 'jQueryプラグイン追加', '1.1.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'jQueryプラグインを追加するためのウィジェットです。画面上には何も表示されません。',  '', 'codemirror.javascript',             true,         true,                true,              0,  3,             1, '2013-03-02', now(),         now());
DELETE FROM _widgets WHERE wd_id = 'head_add';
INSERT INTO _widgets
(wd_id,      wd_name,    wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_has_admin, wd_initialized, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('head_add', 'HEAD追加', '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'HTMLのHEADタグ内に文字列を追加する', true,        true,           3, 1, now(), now());
DELETE FROM _widgets WHERE wd_id = 'chacha_main';
INSERT INTO _widgets
(wd_id,         wd_name,                wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_read_css,               wd_add_script_lib, wd_add_script_lib_a, wd_use_ajax, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('chacha_main', 'マイクロブログメイン', '1.0.0',    '株式会社 毎日メディアサービス', '株式会社 毎日メディアサービス', 'GPL',      10,                'マイクロブログのメイン', true, 'jquery.jcarousel,jquery.cookie',                       '', true,        true,         true,               false,true,           0, 2, 2, now(), now());
DELETE FROM _widgets WHERE wd_id = 'custom_search_box';
INSERT INTO _widgets
(wd_id,               wd_name,            wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,                                     wd_add_script_lib, wd_add_script_lib_a, wd_has_admin, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('custom_search_box', 'カスタム検索連携', '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'カスタム検索に検索結果を表示する検索ボックス', 'jquery', '',     true,         true,true,               1, -1, now(),    now());
DELETE FROM _widgets WHERE wd_id = 'contactus_freelayout3';
INSERT INTO _widgets
(wd_id,              wd_name,                          wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,                             wd_read_scripts, wd_read_css,wd_add_script_lib, wd_add_script_lib_a, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('contactus_freelayout3', 'フリーレイアウトお問い合わせ3', '1.4.0',    '株式会社 毎日メディアサービス', '株式会社 毎日メディアサービス', 'GPL',      10,                'フリーレイアウトでカスタマイズ可能なお問い合わせメール送信機能。「フリーレイアウトお問い合わせ」のバージョンアップ版。', false,           false,     'jquery.formtips,jquery.format,jquery.calculation', 'jquery.tablednd', true,         false,               true,                true, 0,          0, '2013-09-25', now(),         now());
DELETE FROM _widgets WHERE wd_id = 'blog_list';
INSERT INTO _widgets
(wd_id,         wd_name,                 wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,            wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('blog_list',   'ブログ - ブログリスト', '1.1.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'マルチブログリストを表示', true,         true,        false,         false,               false,true,           0, 1, -1, '2012-10-04', now(), now());
DELETE FROM _widgets WHERE wd_id = 'blog_category_box';
INSERT INTO _widgets
(wd_id,          wd_name,         wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,             wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_has_rss, wd_install_dt, wd_create_dt) VALUES
('blog_category_box', 'ブログ - 選択カテゴリ', '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'ブログの1カテゴリに属する記事の一覧を表示。', true,        false,               true,true,               1, -1, true, now(),    now());
DELETE FROM _widgets WHERE wd_id = 'blog_related_category';
INSERT INTO _widgets
(wd_id,                   wd_name,                 wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,                                 wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('blog_related_category', 'ブログ - 関連カテゴリ', '1.1.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                '表示中のブログ記事に関連したカテゴリを表示。', false,         false,               false,true,           1, -1, '2012-10-04', now(), now());
DELETE FROM _widgets WHERE wd_id = 'youtube_player';
INSERT INTO _widgets
(wd_id,      wd_name,   wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,                  wd_add_script_lib, wd_add_script_lib_a, wd_has_admin, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('youtube_player', 'YouTubeプレーヤー', '1.1.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'YouTube動画が複数管理できます。', 'swfobject,jquery.youtubeplayer',                'jquery.tablednd',   true,  true,              true, 3,          1, now(),         now());
DELETE FROM _widgets WHERE wd_id = 'event_main';
INSERT INTO _widgets
(wd_id,        wd_name,                 wd_type, wd_content_type, wd_edit_content, wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,         wd_add_script_lib, wd_add_script_lib_a, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('event_main', 'イベント情報 - メイン', 'event', 'event', true,          '1.1.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'イベント情報を表示する。', '',                'entry_detail=jquery-ui.tabs;category_detail=jquery.tablednd', true,         true,        true,        true,               false,true,               0, 1, 2, '2013-09-01', now(),    now());
DELETE FROM _widgets WHERE wd_id = 'event_search_box';
INSERT INTO _widgets
(wd_id,              wd_name,               wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('event_search_box', 'イベント情報 - 検索', '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL', 10,  'イベント情報を検索するためのボックス。',    true,         true,        false,         false,               false,true,               0, 1, -1, now(),    now());
DELETE FROM _widgets WHERE wd_id = 'event_calendar_box';
INSERT INTO _widgets
(wd_id,                wd_name,                     wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('event_calendar_box', 'イベント情報 - カレンダー', '1.1.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'イベントを表示するカレンダーボックス。',   true,         true,        false,         false,               false,true,               0, 3, 2, '2013-08-31', now(),    now());
DELETE FROM _widgets WHERE wd_id = 'bbs_2ch_search_box';
INSERT INTO _widgets
(wd_id,                wd_name,                   wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('bbs_2ch_search_box', '2ちゃんねる風BBS - 検索', '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                '2ちゃんねる風BBSの記事を検索するためのボックス。', true,         true,        false,         false,               false,true,               0, 1, -1, now(),    now());
DELETE FROM _widgets WHERE wd_id = 'picasa';
INSERT INTO _widgets
(wd_id,    wd_name,  wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,                 wd_has_admin, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('picasa', 'Picasaアルバム', '1.1.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'Picasaウェブアルバムを表示。', true,         true,                true,           1, 1, now(), now());
DELETE FROM _widgets WHERE wd_id = 'photo_main';
INSERT INTO _widgets
(wd_id,        wd_name,                   wd_type, wd_content_type, wd_edit_content, wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_add_script_lib, wd_add_script_lib_a, wd_use_ajax, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('photo_main', 'フォトギャラリー - メイン', 'photo', 'photo', true,         '1.5.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10, 'フォトギャラリーを表示する。', 'jquery.raty', 'search=jquery.tablednd;',  true,     true,        true,               false,true,       1, 2, '2013-07-28', now(),    now());
DELETE FROM _widgets WHERE wd_id = 'ie6_nomore';
INSERT INTO _widgets 
(wd_id,        wd_name,       wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,                      wd_initialized, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('ie6_nomore', 'IE6 No More', '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'IE6ブラウザ向けにメッセージを表示', true,           1,             -1, now(), now());
DELETE FROM _widgets WHERE wd_id = 'simple_html';
INSERT INTO _widgets (
wd_id,         wd_name,    wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,                                   wd_has_admin, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES (
'simple_html', '汎用HTML', '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'コンテンツとして管理しない部分表示用の汎用HTML', true,         true,                true,          1, -1, now(), now());
DELETE FROM _widgets WHERE wd_id = 'photo_new';
INSERT INTO _widgets
(wd_id,       wd_name,                   wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,                       wd_has_admin, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_has_rss, wd_install_dt, wd_create_dt) VALUES
('photo_new', 'フォトギャラリー - 新規', '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'フォトギャラリーの新規一覧を表示。', true,         false,true,               1, -1, true, now(),    now());
DELETE FROM _widgets WHERE wd_id = 'photoslide2';
INSERT INTO _widgets
(wd_id,         wd_name,               wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_add_script_lib, wd_add_script_lib_a,   wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('photoslide2', '画像スライドショー2', '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10, '画像をスライドショー表示する。フォトギャラリー連動可。', 'jquery.cycle', 'jquery.cycle',   true,        false,               true,true,           3, 1, '2012-02-19', now(), now());
DELETE FROM _widgets WHERE wd_id = 'lang_changer';
INSERT INTO _widgets
(wd_id,          wd_name,    wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_add_script_lib, wd_add_script_lib_a, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('lang_changer', '言語変更', '2.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                '表示言語変更', '',          '',         false,        false,               false,true,           1, -1, '2013-09-19', now(), now());
DELETE FROM _widgets WHERE wd_id = 'ec_product_slide';
INSERT INTO _widgets
(wd_id,         wd_name,               wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_add_script_lib, wd_add_script_lib_a,   wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('ec_product_slide', 'Eコマース - 商品スライド表示', '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10, '商品画像をカテゴリー単位でスライド表示する。', 'jquery', 'jquery',   true,        false,               false,true,           0, 0, '2012-10-28', now(), now());
DELETE FROM _widgets WHERE wd_id = 'ticker';
INSERT INTO _widgets
(wd_id,      wd_name,   wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,                  wd_add_script_lib, wd_add_script_lib_a, wd_has_admin, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('ticker', 'ティッカー', '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'テキストをティッカー表示します。', 'jquery',                'jquery.tablednd',   true,  true,              true, 3,          1, '2012-11-12', now(),         now());
DELETE FROM _widgets WHERE wd_id = 'featured_content';
INSERT INTO _widgets 
(wd_id, wd_name, wd_version, wd_author, wd_copyright, wd_license, wd_official_level, wd_description, wd_add_script_lib, wd_add_script_lib_a, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('featured_content',    '汎用コンテンツ - 特集表示', '1.0.0',         'Naoki Hirata', 'Magic3.org', 'GPL', 10, '特集形式でコンテンツを表示。', '', 'jquery.tablednd', true,         false,               true,true,           0, 2, 1, '2013-1-14',now(), now());
DELETE FROM _widgets WHERE wd_id = 'comment';
INSERT INTO _widgets
(wd_id,     wd_name,       wd_type, wd_content_type, wd_edit_content, wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_add_script_lib, wd_add_script_lib_a, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('comment', '汎用コメント', '', 'comment', true, '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'メインコンテンツウィジェットに付加する汎用のコメント機能', 'jquery.scrollto',                '',                  true, true,         false,                true,  '2013-07-19', now(),         now());
DELETE FROM _widgets WHERE wd_id = 'calendar';
INSERT INTO _widgets
(wd_id,     wd_name,       wd_content_type, wd_edit_content, wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_add_script_lib, wd_add_script_lib_a, wd_use_ajax, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('calendar', '汎用カレンダー', 'calendar', true, '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                '様々な情報をカレンダー表示する汎用カレンダー機能', 'jquery.fullcalendar,jquery.qtip',                'datetype_detail=jquery.tablednd,jquery.timepicker;date_detail=jquery-ui.datepicker,jquery-ui.dialog,jquery.timepicker,jquery.tablednd,jquery.json;',   true,                true, true,         true,                true,  0,         1, '2013-10-17', now(),         now());
DELETE FROM _widgets WHERE wd_id = 'event_category_menu';
INSERT INTO _widgets
(wd_id,                 wd_name,                         wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('event_category_menu', 'イベント情報 - カテゴリーメニュー', '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL', 10, 'イベントカテゴリ一覧を表示。',     false,         false,               false,true,           0, 1, -1, '2013-09-02', now(), now());
DELETE FROM _widgets WHERE wd_id = 'event_category';
INSERT INTO _widgets
(wd_id,            wd_name,                     wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,             wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_has_rss, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('event_category', 'イベント情報 - 選択カテゴリー', '1.1.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'イベント記事の1カテゴリに属する記事の一覧を表示。', true,        false,               true,true,               1, -1, true, '2013-10-06', now(),    now());
DELETE FROM _widgets WHERE wd_id = 'slogan';
INSERT INTO _widgets
(wd_id,   wd_name, wd_version, wd_params, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_add_script_lib, wd_add_script_lib_a,   wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('slogan', 'スローガン',  '1.0.0',    '',        'Naoki Hirata', 'Magic3.org', 'GPL',      10, 'ヘッダ等の前面にメッセージテキストを表示。レスポンシブウェブ対応。',                      'jquery.fittext',         '',        true,         false,                                true,                true,              '2012-11-01', now(),         now());

-- ウィジェット情報(携帯用)
DELETE FROM _widgets WHERE wd_id = 'm/mainmenu';
INSERT INTO _widgets
(wd_id,      wd_name,          wd_type, wd_version, wd_params, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_device_type, wd_mobile, wd_has_admin, wd_initialized, wd_install_dt, wd_create_dt) VALUES
('m/mainmenu', '携帯メインメニュー(廃止予定)', 'menu',  '1.0.0',    '',        'Naoki Hirata', 'Magic3.org', 'GPL',      10, '常に表示するメインメニュー。(携帯用)', 1,              true,      true,         true,              now(),         now());
DELETE FROM _widgets WHERE wd_id = 'm/contactus';
INSERT INTO _widgets
(wd_id,      wd_name,          wd_type, wd_version, wd_params, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_device_type, wd_mobile, wd_has_admin, wd_initialized, wd_install_dt, wd_create_dt) VALUES
('m/contactus', '簡易お問い合わせ', 'MCON',  '1.0.0',    '',        'Naoki Hirata', 'Magic3.org', 'GPL',      10, 'お問い合わせの入力データをメールで送る。', 1,               true,      true,         true,              now(),         now());
DELETE FROM _widgets WHERE wd_id = 'm/custom_header';
INSERT INTO _widgets
(wd_id,      wd_name,          wd_version, wd_params, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_device_type, wd_mobile, wd_has_admin, wd_initialized, wd_install_dt, wd_create_dt) VALUES
('m/custom_header', 'カスタムヘッダ', '1.0.0',    '',        'Naoki Hirata', 'Magic3.org', 'GPL',      10, 'ヘッダ部を表示する。', 1,               true,      true,         true,              now(),         now());
DELETE FROM _widgets WHERE wd_id = 'm/custom_footer';
INSERT INTO _widgets
(wd_id,      wd_name,          wd_type, wd_version, wd_params, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_device_type, wd_mobile, wd_has_admin, wd_initialized, wd_install_dt, wd_create_dt) VALUES
('m/custom_footer', 'カスタムフッタ', 'MCSF',  '1.0.0',    '',        'Naoki Hirata', 'Magic3.org', 'GPL',      10, 'フッタ部に表示し、著作権等の表示を行う。', 1,              true,      true,         true,              now(),         now());
DELETE FROM _widgets WHERE wd_id = 'm/content';
INSERT INTO _widgets
(wd_id,      wd_name,       wd_type,   wd_content_type, wd_content_info, wd_content_name, wd_edit_content, wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_device_type, wd_mobile, wd_has_admin, wd_initialized, wd_install_dt, wd_create_dt) VALUES
('m/content', '汎用コンテンツ - メイン', 'content', 'content', 'mobile', '', true,       '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10, '汎用コンテンツを表示。', 1,              true,      true,         true,              now(),         now());
DELETE FROM _widgets WHERE wd_id = 'm/pc_content';
INSERT INTO _widgets
(wd_id,      wd_name,          wd_type, wd_content_type, wd_content_info, wd_content_name, wd_version, wd_params, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_device_type, wd_mobile, wd_has_admin, wd_initialized, wd_install_dt, wd_create_dt) VALUES
('m/pc_content', '汎用コンテンツ(PC共通) - メイン', 'content', 'content', '', '汎用コンテンツ(PC共通)',   '2.0.0',    '',        'Naoki Hirata', 'Magic3.org', 'GPL',      10, 'PCの汎用コンテンツを自動的に携帯用に変換して表示。', 1,              true,      true,         true,              now(),         now());
DELETE FROM _widgets WHERE wd_id = 'm/content_search_box';
INSERT INTO _widgets
(wd_id,                  wd_name,              wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_device_type,         wd_mobile, wd_has_admin, wd_initialized, wd_install_dt, wd_create_dt) VALUES
('m/content_search_box', '汎用コンテンツ - 検索',  '1.1.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                '汎用コンテンツを検索するためのボックス。', 1, true,      false,               true,           now(),         now());
DELETE FROM _widgets WHERE wd_id = 'm/custom_header';
INSERT INTO _widgets
(wd_id,      wd_name,          wd_version, wd_params, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_device_type, wd_mobile, wd_has_admin, wd_initialized, wd_install_dt, wd_create_dt) VALUES
('m/custom_header', 'カスタムヘッダ', '1.0.0',    '',        'Naoki Hirata', 'Magic3.org', 'GPL',      10, 'ヘッダ部を表示する。', 1,              true,      true,         true,              now(),         now());
DELETE FROM _widgets WHERE wd_id = 'm/quizk';
INSERT INTO _widgets
(wd_id,     wd_name,        wd_description,                         wd_device_type, wd_mobile, wd_has_admin, wd_initialized, wd_install_dt, wd_create_dt) VALUES
('m/quizk', '携帯クイズ王', '携帯用のクイズサイト構築ウィジェット', 1,              true,      true,         true,           now(),         now());
DELETE FROM _widgets WHERE wd_id = 'm/default_login';
INSERT INTO _widgets
(wd_id,             wd_name,              wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_device_type,              wd_mobile, wd_has_admin, wd_initialized, wd_launch_index, wd_install_dt, wd_create_dt) VALUES
('m/default_login', 'デフォルトログイン', '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'デフォルトのログイン機能', 1, true,      false,        true,           100,             now(),         now());
DELETE FROM _widgets WHERE wd_id = 'm/adtag';
INSERT INTO _widgets
(wd_id,      wd_name,   wd_version, wd_params, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_device_type, wd_mobile, wd_has_admin, wd_use_instance_def, wd_initialized, wd_install_dt, wd_create_dt) VALUES
('m/adtag', '広告タグ', '1.0.0',    '',        'Naoki Hirata', 'Magic3.org', 'GPL',      10, '広告タグを埋め込む。', 1,               true,      true,         true, true,              now(),         now());
DELETE FROM _widgets WHERE wd_id = 'm/news';
INSERT INTO _widgets
(wd_id,      wd_name,          wd_version, wd_params, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_device_type, wd_mobile, wd_has_admin, wd_use_instance_def, wd_initialized, wd_install_dt, wd_create_dt) VALUES
('m/news', 'お知らせ', '1.0.0',    '',        'Naoki Hirata', 'Magic3.org', 'GPL',      10, 'お知らせを表示する。', 1,               true,      true,         true,   true,           now(),         now());
DELETE FROM _widgets WHERE wd_id = 'm/contactus_custom';
INSERT INTO _widgets
(wd_id,      wd_name,          wd_version, wd_params, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_device_type, wd_add_script_lib, wd_add_script_lib_a, wd_mobile, wd_has_admin, wd_use_instance_def, wd_initialized, wd_install_dt, wd_create_dt) VALUES
('m/contactus_custom', 'カスタムお問い合わせ', '1.0.0',    '',        'Naoki Hirata', 'Magic3.org', 'GPL',      10, 'カスタマイズ可能なお問い合わせメール送信', 1, '', 'jquery.tablednd',              true,      true,         true, true,              now(),         now());
DELETE FROM _widgets WHERE wd_id = 'm/chacha';
INSERT INTO _widgets
(wd_id,      wd_name,          wd_version, wd_author,                       wd_copyright, wd_license, wd_official_level, wd_description, wd_device_type, wd_mobile, wd_has_admin, wd_initialized, wd_install_dt, wd_create_dt) VALUES
('m/chacha', 'マイクロブログ', '1.0.0',    '株式会社 毎日メディアサービス', '株式会社 毎日メディアサービス', 'GPL',      10, 'マイクロブログ(携帯用)', 1,              true,      true,         true,              now(),         now());
DELETE FROM _widgets WHERE wd_id = 'm/blog';
INSERT INTO _widgets
(wd_id,    wd_name,  wd_type, wd_content_type, wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_device_type, wd_mobile, wd_has_admin, wd_initialized, wd_install_dt, wd_create_dt) VALUES
('m/blog', 'ブログ - メイン', 'blog', 'blog',          '2.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10, 'ブログ(携帯用)', 1,              true,      true,         true,              now(),         now());
DELETE FROM _widgets WHERE wd_id = 'm/blog_search';
INSERT INTO _widgets
(wd_id,           wd_name,      wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_device_type,        wd_mobile, wd_has_admin, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('m/blog_search', 'ブログ - 検索', '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'ブログ記事を検索する', 1, true,      false,        true,           0, 1, -1, now(),    now());
DELETE FROM _widgets WHERE wd_id = 'm/separator';
INSERT INTO _widgets
(wd_id,         wd_name,      wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,  wd_device_type,             wd_mobile, wd_has_admin, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('m/separator', 'セパレータ', '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'ウィジェット間の区切り線。', 1, true,      false,        true,          0, 1, -1, now(), now());
DELETE FROM _widgets WHERE wd_id = 'm/g_analytics';
INSERT INTO _widgets
(wd_id,         wd_name,            wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_device_type, wd_mobile, wd_has_admin, wd_initialized, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('m/g_analytics', 'Google Analytics', '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'Google Analytics トラッキングコードを出力する。', 1, true, true,        true,           1, -1, now(), now());
DELETE FROM _widgets WHERE wd_id = 'm/googlemaps';
INSERT INTO _widgets
(wd_id,          wd_name,        wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,       wd_device_type, wd_mobile, wd_has_admin, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('m/googlemaps', 'Googleマップ', '3.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'Goolgeマップを表示', 1,              true,      true,         true,true,           1, 1, '2012-03-19', now(), now());
DELETE FROM _widgets WHERE wd_id = 'm/blog_category';
INSERT INTO _widgets
(wd_id,         wd_name,            wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_device_type, wd_mobile, wd_has_admin, wd_initialized, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('m/blog_category', 'ブログ - カテゴリ', '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'ブログの全カテゴリを一覧表示する。', 1, true, false,        true,           1, -1, now(), now());
DELETE FROM _widgets WHERE wd_id = 'm/bbs_2ch';
INSERT INTO _widgets
(wd_id,       wd_name,            wd_type, wd_content_type, wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_device_type, wd_mobile, wd_add_script_lib, wd_add_script_lib_a, wd_has_admin, wd_initialized, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('m/bbs_2ch', '2ちゃんねる風BBS', 'bbs', 'bbs',           '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                '2ちゃんねる風掲示板(携帯用)', 1, true, '',                'elfinder', true,        true,     2, 2, now(), now());
DELETE FROM _widgets WHERE wd_id = 'm/bbs_2ch_search_box';
INSERT INTO _widgets
(wd_id,                  wd_name,                   wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_device_type,         wd_mobile, wd_has_admin, wd_initialized, wd_install_dt, wd_create_dt) VALUES
('m/bbs_2ch_search_box', '2ちゃんねる風BBS - 検索', '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                '2ちゃんねる風BBS記事を検索するためのボックス。', 1, true,      false,               true,           now(),         now());
DELETE FROM _widgets WHERE wd_id = 'm/menu';
INSERT INTO _widgets
(wd_id,    wd_name,          wd_type, wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_device_type, wd_mobile, wd_add_script_lib, wd_add_script_lib_a,wd_has_admin, wd_use_instance_def, wd_initialized, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('m/menu', 'デフォルトメニュー', 'menu',  '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10, '複数のメニューが作成できるデフォルトメニュー', 1,              true,     '', 'jquery-ui.tabs',  true,   true,      true,    '2013-11-03',           now(),         now());

-- ウィジェット情報(スマートフォン用)
DELETE FROM _widgets WHERE wd_id = 's/content';
INSERT INTO _widgets
(wd_id,       wd_parent_id, wd_name,      wd_type, wd_content_type,   wd_edit_content, wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_device_type, wd_add_script_lib, wd_add_script_lib_a, wd_has_admin, wd_enable_operation, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('s/content', 'default_content', '汎用コンテンツ - メイン', 'content',       'content', true, '2.3.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                '画像やテキストからできた汎用コンテンツを表示。', 2,         '', 'content_detail=jquery.tablednd', true, true,         true,           0, 2, 2, '2012-08-18', now(), now());
DELETE FROM _widgets WHERE wd_id = 's/slide_menu';
INSERT INTO _widgets
(wd_id,        wd_name,           wd_type, wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_device_type, wd_add_script_lib, wd_add_script_lib_a,                         wd_has_admin, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('s/slide_menu', 'スライドメニュー', 'menu', '2.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'スライドオープンできる2階層のメニューです。', 2, 'jquery', 'jquery', true,  true,              true, 3,          1, '2012-06-07', now(),         now());
DELETE FROM _widgets WHERE wd_id = 's/custom_footer';
INSERT INTO _widgets
(wd_id,             wd_name,          wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,                             wd_device_type, wd_has_admin, wd_initialized, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('s/custom_footer', 'カスタムフッタ', '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'フッタ部に表示し、著作権等の表示を行う。', 2,              true,         true,             1, -1,  now(),         now());
DELETE FROM _widgets WHERE wd_id = 's/g_analytics';
INSERT INTO _widgets
(wd_id,         wd_name,            wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_device_type, wd_has_admin, wd_initialized, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('s/g_analytics', 'Google Analytics', '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'Google Analytics トラッキングコードを出力する。', 2, true,        true,           1, -1, now(), now());
DELETE FROM _widgets WHERE wd_id = 's/googlemaps';
INSERT INTO _widgets
(wd_id,          wd_name,        wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,       wd_device_type, wd_add_script_lib, wd_add_script_lib_a, wd_has_admin, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('s/googlemaps', 'Googleマップ', '3.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'Goolgeマップを表示', 2,              'jquery',          'jquery',            true,                       true,true,           3, 1, '2012-03-19', now(), now());
DELETE FROM _widgets WHERE wd_id = 's/blogparts';
INSERT INTO _widgets
(wd_id,         wd_name,        wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_device_type, wd_has_admin, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('s/blogparts', 'ブログパーツ', '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'ブログパーツを表示', 2, true,         true,true,       1, 1, now(), now());
DELETE FROM _widgets WHERE wd_id = 's/jquery_header';
INSERT INTO _widgets
(wd_id,             wd_name,                wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,                      wd_device_type, wd_add_script_lib, wd_add_script_lib_a, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('s/jquery_header', 'jQueryページ - ヘッダ', '1.1.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'jQuery Mobile型ページ専用のヘッダ', 2,              'jquery.mobile',          '',            true, true,                      true,true, 10,           1, 1, now(), now());
DELETE FROM _widgets WHERE wd_id = 's/jquery_content_menu';
INSERT INTO _widgets
(wd_id,                     wd_name,   wd_type,                    wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,                      wd_device_type, wd_add_script_lib, wd_add_script_lib_a, wd_has_admin, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('s/jquery_content_menu', 'jQueryページ - コンテンツメニュー', 'menu', '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'jQuery Mobile型ページ専用のコンテンツリンクメニュー', 2,              'jquery.mobile',          '',            true,                       true,true,           1, 1, now(), now());
DELETE FROM _widgets WHERE wd_id = 's/jquery_localize';
INSERT INTO _widgets
(wd_id,             wd_name,                wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,                      wd_device_type, wd_add_script_lib, wd_add_script_lib_a, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('s/jquery_localize', 'jQueryページ - 日本語化', '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'jQuery Mobile型ページ専用のメッセージ日本語化ウィジェット', 2,              'jquery.mobile',          '',            false, false,                      false,true, 0,           3, -1, now(), now());
DELETE FROM _widgets WHERE wd_id = 's/simple_html';
INSERT INTO _widgets
(wd_id,             wd_name,          wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,                             wd_device_type, wd_has_admin, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('s/simple_html', '汎用HTML', '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'コンテンツとして管理しない部分表示用の汎用HTML', 2,              true,         true, true,             1, -1,  now(),         now());
DELETE FROM _widgets WHERE wd_id = 's/css_add';
INSERT INTO _widgets
(wd_id,             wd_name,          wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,                             wd_device_type, wd_has_admin, wd_initialized, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('s/css_add', 'CSS追加', '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'テンプレートのCSSに加えてCSS定義を追加するためのウィジェットです。', 2,              true,         true,             3, 1,  now(),         now());
DELETE FROM _widgets WHERE wd_id = 's/jquery_menu';
INSERT INTO _widgets
(wd_id,                     wd_name,  wd_type,                     wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,                      wd_device_type, wd_add_script_lib, wd_add_script_lib_a, wd_has_admin, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('s/jquery_menu', 'jQueryページ - メニュー', 'menu', '2.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'jQuery Mobile型ページ専用のメニュー', 2,              'jquery.mobile',       'jquery-ui.tabs',            true,                       true,true,           1, 1, '2012-06-07', now(), now());
DELETE FROM _widgets WHERE wd_id = 's/blog';
INSERT INTO _widgets
(wd_id,    wd_name,  wd_type, wd_content_type, wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,                      wd_device_type, wd_add_script_lib, wd_add_script_lib_a, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('s/blog', 'ブログ - メイン', 'blog', 'blog',  '1.0.3',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'ブログの記事を表示する。jQueryページ対応。', 2,              'jquery.cookie,jquery.mobile',       'elfinder',            true, true,                      false,true,           1, 2, '2012-03-26', now(), now());
DELETE FROM _widgets WHERE wd_id = 's/contactus';
INSERT INTO _widgets
(wd_id,         wd_name,            wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,                      wd_device_type, wd_add_script_lib, wd_add_script_lib_a, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('s/contactus', '簡易お問い合わせ', '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'お問い合わせの入力データをメールで送る。', 2,              '',       '',            true, false,                      false,true,           0, 0, now(), now());
DELETE FROM _widgets WHERE wd_id = 's/jquery_footer';
INSERT INTO _widgets
(wd_id,             wd_name,                wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,                      wd_device_type, wd_add_script_lib, wd_add_script_lib_a, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('s/jquery_footer', 'jQueryページ - フッタ', '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'jQuery Mobile型ページ専用のフッタ', 2,              'jquery.mobile',          '',            true, false,                      true,true, 1, 1, now(), now());
DELETE FROM _widgets WHERE wd_id = 's/jquery_init';
INSERT INTO _widgets
(wd_id,             wd_name,                wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,                      wd_device_type, wd_add_script_lib, wd_add_script_lib_a, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('s/jquery_init', 'jQueryページ - 初期化', '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'jQuery Mobile型ページ専用の初期化(mobileinit)スクリプト', 2,              'jquery.mobile',          '',            true, true,                      true,true, 3, 1, now(), now());
DELETE FROM _widgets WHERE wd_id = 's/photoslide';
INSERT INTO _widgets
(wd_id,         wd_name,               wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_device_type, wd_add_script_lib, wd_add_script_lib_a,   wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('s/photoslide', '画像スライドショー', '1.0.1',    'Naoki Hirata', 'Magic3.org', 'GPL',      10, '画像をスライドショー表示する。フォトギャラリー連動可。', 2, 'jquery.cycle', 'jquery.cycle',   true,        false,               true,true,           3, 1, '2012-02-27', now(), now());
DELETE FROM _widgets WHERE wd_id = 's/photo';
INSERT INTO _widgets
(wd_id,        wd_name,                   wd_type, wd_content_type, wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_device_type, wd_add_script_lib, wd_add_script_lib_a, wd_use_ajax, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('s/photo', 'フォトギャラリー - メイン', 'photo', 'photo',         '0.8.1',    'Naoki Hirata', 'Magic3.org', 'GPL',      10, 'フォトギャラリーを表示する。', 2, 'jquery.raty', 'search=jquery.tablednd;',  true,     true,        true,               false,true,       1, 2, '2012-07-12', now(),    now());
DELETE FROM _widgets WHERE wd_id = 's/blog_category';
INSERT INTO _widgets
(wd_id,             wd_name,             wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_device_type, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('s/blog_category', 'ブログ - カテゴリ', '1.0.2',    'Naoki Hirata', 'Magic3.org', 'GPL', 10, 'ブログカテゴリメニュー',          2,       true,         false,               false,true,          1, -1, '2012-03-26', now(), now());
DELETE FROM _widgets WHERE wd_id = 's/blog_archive';
INSERT INTO _widgets
(wd_id,             wd_name,             wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_device_type, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('s/blog_archive', 'ブログ - アーカイブ', '1.0.1',    'Naoki Hirata', 'Magic3.org', 'GPL', 10, 'ブログアーカイブメニュー',          2,       true,         false,               false,true,          1, -1, '2012-03-26', now(), now());
DELETE FROM _widgets WHERE wd_id = 's/login';
INSERT INTO _widgets
(wd_id,     wd_name,    wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,                     wd_device_type, wd_add_script_lib, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('s/login', 'ログイン', '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'デフォルトのログイン用ボックス。', 2,              'md5',             false,        false, false,true,               2, -1, '2012-04-03', now(),    now());
DELETE FROM _widgets WHERE wd_id = 's/banner';
INSERT INTO _widgets
(wd_id,     wd_parent_id, wd_name,       wd_content_type, wd_edit_content, wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_device_type, wd_add_script_lib, wd_add_script_lib_a, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('s/banner', 'banner3', 'バナー表示', 'banner', true, '3.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'バナー画像をグループ化して、グループごとに表示できるバナー管理ウィジェットです。', 2, '',                'jquery-ui.tabs',                  true, true,         true,                true,  0,         1, '2013-12-08', now(),         now());
DELETE FROM _widgets WHERE wd_id = 's/bbs_2ch';
INSERT INTO _widgets
(wd_id,       wd_name,                     wd_type, wd_content_type, wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,                wd_device_type, wd_add_script_lib, wd_add_script_lib_a, wd_read_css, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('s/bbs_2ch', '2ちゃんねる風BBS - メイン', 'bbs', 'bbs',           '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                '2ちゃんねる風掲示板のメイン', 2,              'jquery.cookie',                       'elfinder', true, false,         false,               false,true,           2, 2, '2012-04-13', now(), now());
DELETE FROM _widgets WHERE wd_id = 's/lang_changer';
INSERT INTO _widgets
(wd_id,          wd_name,    wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_device_type, wd_add_script_lib, wd_add_script_lib_a, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('s/lang_changer', '言語変更', '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                '表示言語変更', 2, '',          '',         false,        false,               false,true,           1, -1, '2012-05-21', now(), now());

-- ウィジェット情報(サーバ連携用)
DELETE FROM _widgets WHERE wd_id = 'c/updateinfo';
INSERT INTO _widgets
(wd_id,          wd_name,    wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,         wd_available, wd_editable, wd_has_admin, wd_initialized, wd_install_dt, wd_create_dt) VALUES
('c/updateinfo', '新着登録', '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                '新着情報を登録する。', false,        false,       false,        true,           now(),         now());
