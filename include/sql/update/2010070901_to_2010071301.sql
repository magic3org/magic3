-- *
-- * バージョンアップ用スクリプト
-- *
-- * PHP versions 5
-- *
-- * LICENSE: This source file is licensed under the terms of the GNU General Public License.
-- *
-- * @package    Magic3 Framework
-- * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
-- * @copyright  Copyright 2006-2010 Magic3 Project.
-- * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
-- * @version    SVN: $Id: 2010070901_to_2010071301.sql 3410 2010-07-20 06:56:41Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
-- メニュー定義マスター
ALTER TABLE _menu_def ADD md_content_type      VARCHAR(10)    DEFAULT ''             NOT NULL;      -- リンク先のコンテンツの種別
ALTER TABLE _menu_def ADD md_content_id        VARCHAR(10)    DEFAULT ''             NOT NULL;      -- リンク先のコンテンツのID

-- ウィジェット情報
-- 廃止ウィジェット
DELETE FROM _widgets WHERE wd_id = 'default_mainmenu';
INSERT INTO _widgets (wd_id, wd_name, wd_type, wd_version, wd_joomla_class, wd_author, wd_copyright, wd_license, wd_official_level, wd_description, wd_read_scripts, wd_read_css, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt, wd_status) VALUES
('default_mainmenu',   'デフォルトメインメニュー(廃止予定)',   'menu', '1.0.0',  '_menu',        'Naoki Hirata', 'Magic3.org', 'GPL', 10, '常に表示し共通で使用するメインメニュー。',         false,           false,       false,         true,        true,         false,               false,true,           0, 2, -1, now(), now(), -1);
DELETE FROM _widgets WHERE wd_id = 'nav_menu';
INSERT INTO _widgets (wd_id, wd_name, wd_type, wd_version, wd_params, wd_author, wd_copyright, wd_license, wd_official_level, wd_description, wd_read_scripts, wd_read_css, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt, wd_status) VALUES
('nav_menu',   'ナビゲーションメニュー(廃止予定)',   'menu', '1.0.0',  '',        'Naoki Hirata', 'Magic3.org', 'GPL', 10, 'ページのヘッダ下や、フッタ部に表示するメニュー。',           false,           false,       false,         true,        true,         false,               false,true,           0, 1, -1, now(), now(), -1);
DELETE FROM _widgets WHERE wd_id = 'nav_menu_css';
INSERT INTO _widgets (wd_id, wd_name, wd_type, wd_version, wd_params, wd_author, wd_copyright, wd_license, wd_official_level, wd_description, wd_read_scripts, wd_read_css, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt, wd_status) VALUES
('nav_menu_css',   'ナビゲーションメニュー(CSS)(廃止予定)',   'menu', '1.0.0',  '',        '', 'Magic3.org', 'GPL', 10, 'ページのヘッダ下や、フッタ部に表示するメニュー。SPANタグにて表示。',           false,           false,       false,         true,        true,         false,               false,true,           0, 1, -1, now(), now(), -1);
-- 管理機能ウィジェット
DELETE FROM _widgets WHERE wd_id = 'admin_main';
INSERT INTO _widgets
(wd_id,        wd_name,      wd_author,      wd_copyright, wd_license, wd_official_level, wd_add_script_lib, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_install_dt, wd_create_dt) VALUES
('admin_main', '管理用画面', 'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'pagedef=jquery-ui.tabs;pagedef_mobile=jquery-ui.tabs;pagedef_smartphone=jquery-ui.tabs;menudef=jquery.simpletree;', false,        false,       false,        true,          false, true, now(),now());
-- 管理機能以外のウィジェット
DELETE FROM _widgets WHERE wd_id = 'accordion_menu';
INSERT INTO _widgets
(wd_id,            wd_name, wd_type,                  wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_add_script_lib, wd_add_script_lib_a,                         wd_has_admin, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('accordion_menu', 'アコーディオンメニュー','menu','1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                '複数のメニューが作成できる2階層のアコーディオンメニューです。サブメニューも管理できる共通のメニュー定義を使用します。', 'jquery-ui.accordion', 'jquery-ui.accordion', true,  true,              true, 3,          1, now(),         now());
DELETE FROM _widgets WHERE wd_id = 'blog_main';
INSERT INTO _widgets (wd_id, wd_name, wd_type, wd_version, wd_params, wd_author, wd_copyright, wd_license, wd_official_level, wd_description, wd_add_script_lib_a, wd_read_scripts, wd_read_css, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('blog_main',          'ブログ - メイン',            'BGMA', '1.0.0',  '',        'Naoki Hirata', 'Magic3.org', 'GPL', 10, 'ブログの記事の内容を表示する。', 'entry_detail=jquery-ui.tabs',          false,           false,       true,         true,        true,        false,               false,true,               0, 1, 2, now(),    now());
DELETE FROM _widgets WHERE wd_id = 'user_content';
INSERT INTO _widgets
(wd_id,          wd_name,                wd_type, wd_version, wd_author,                       wd_copyright,                    wd_license, wd_official_level, wd_description,         wd_add_script_lib, wd_add_script_lib_a, wd_has_admin, wd_enable_operation, wd_initialized, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('user_content', 'ユーザ作成コンテンツ', 'user',  '1.1.0',    '株式会社 毎日メディアサービス', '株式会社 毎日メディアサービス', 'GPL',      10,                'ユーザが管理可能なコンテンツを表示', 'jquery-ui.tabs', 'jquery.tablednd', true, true, true,           2, 2, now(), now());
