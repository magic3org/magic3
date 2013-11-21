-- *
-- * バージョンアップ用スクリプト
-- *
-- * PHP versions 5
-- *
-- * LICENSE: This source file is licensed under the terms of the GNU General Public License.
-- *
-- * @package    Magic3 Framework
-- * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
-- * @copyright  Copyright 2006-2009 Magic3 Project.
-- * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
-- * @version    SVN: $Id: 2009101801_to_2009102701.sql 2483 2009-10-29 04:03:07Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
-- ウィジェット情報
DELETE FROM _widgets WHERE wd_id = 'default_mainmenu';
INSERT INTO _widgets (wd_id, wd_name, wd_type, wd_version, wd_joomla_class, wd_author, wd_copyright, wd_license, wd_official_level, wd_description, wd_read_scripts, wd_read_css, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('default_mainmenu',   'デフォルトメインメニュー(廃止予定)',   'menu', '1.0.0',  '_menu',        'Naoki Hirata', 'Magic3.org', 'GPL', 10, '常に表示し共通で使用するメインメニュー。',         false,           false,       true,         true,        true,         false,               false,true,           0, 2, -1, now(), now());
DELETE FROM _widgets WHERE wd_id = 'default_menu';
INSERT INTO _widgets
(wd_id,                wd_name, wd_type,                      wd_version, wd_joomla_class, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_add_script_lib, wd_add_script_lib_a,                         wd_has_admin, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('default_menu', 'デフォルトメニュー', 'menu',         '1.0.0',    '_menu', 'Naoki Hirata', 'Magic3.org', 'GPL',      10,                '複数のメニューが作成できる1階層の新型デフォルトメニューです。サブメニューも管理できる共通のメニュー定義を使用します。ナビゲーションメニューの位置(画面上部)にある「user3」ポジションに配置するとナビゲーションメニューが表示できます。', '', '', true,  true,              true, 2,          1, now(),         now());
DELETE FROM _widgets WHERE wd_id = 'nav_menu';
INSERT INTO _widgets (wd_id, wd_name, wd_type, wd_version, wd_params, wd_author, wd_copyright, wd_license, wd_official_level, wd_description, wd_read_scripts, wd_read_css, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('nav_menu',   'ナビゲーションメニュー',   'menu', '1.0.0',  '',        'Naoki Hirata', 'Magic3.org', 'GPL', 10, 'ページのヘッダ下や、フッタ部に表示するメニュー。',           false,           false,       true,         true,        true,         false,               false,true,           0, 1, -1, now(), now());
DELETE FROM _widgets WHERE wd_id = 'nav_menu_css';
INSERT INTO _widgets (wd_id, wd_name, wd_type, wd_version, wd_params, wd_author, wd_copyright, wd_license, wd_official_level, wd_description, wd_read_scripts, wd_read_css, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('nav_menu_css',   'ナビゲーションメニュー(CSS)',   'menu', '1.0.0',  '',        '', 'Magic3.org', 'GPL', 10, 'ページのヘッダ下や、フッタ部に表示するメニュー。SPANタグにて表示。',           false,           false,       true,         true,        true,         false,               false,true,           0, 1, -1, now(), now());
DELETE FROM _widgets WHERE wd_id = 'accordion_menu';
INSERT INTO _widgets
(wd_id,            wd_name, wd_type,                  wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_add_script_lib, wd_add_script_lib_a,                         wd_has_admin, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('accordion_menu', 'アコーディオンメニュー','menu','1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                '複数のメニューが作成できる2階層のアコーディオンメニューです。サブメニューも管理できる共通のメニュー定義を使用します。', 'jquery-ui,jquery-ui-plus', 'jquery-ui,jquery-ui-plus', true,  true,              true, 3,          1, now(),         now());
DELETE FROM _widgets WHERE wd_id = 'dropdown_menu';
INSERT INTO _widgets
(wd_id,            wd_name, wd_type,                 wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_read_scripts, wd_read_css, wd_add_script_lib, wd_add_script_lib_a,                         wd_has_admin, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('dropdown_menu', 'ドロップダウンメニュー','menu','1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                '複数のメニューが作成できる多階層のドロップダウンメニューです。サブメニューも管理できる共通のメニュー定義を使用します。', true, true, 'jquery.bgiframe,jquery.hoverintent', 'jquery.bgiframe,jquery.hoverintent', true,  true,              true, 3,          1, now(),         now());
-- ウィジェット情報(携帯用)
DELETE FROM _widgets WHERE wd_id = 'm/mainmenu';
INSERT INTO _widgets
(wd_id,      wd_name,          wd_type, wd_version, wd_params, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_mobile, wd_has_admin, wd_initialized, wd_install_dt, wd_create_dt) VALUES
('m/mainmenu', '携帯メインメニュー', 'menu',  '1.0.0',    '',        'Naoki Hirata', 'Magic3.org', 'GPL',      10, '常に表示するメインメニュー。(携帯用)',               true,      true,         true,              now(),         now());
-- ウィジェット情報(新規追加)
DELETE FROM _widgets WHERE wd_id = 'contactus_freelayout';
INSERT INTO _widgets
(wd_id,              wd_name,                          wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,                             wd_read_scripts, wd_read_css,wd_add_script_lib, wd_add_script_lib_a, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('contactus_freelayout', 'フリーレイアウトお問い合わせ', '1.0.0',    '株式会社 毎日メディアサービス', '株式会社 毎日メディアサービス', 'GPL',      10,                'フリーレイアウトでカスタマイズ可能なお問い合わせメール送信', false,           false,     '', 'jquery.tablednd', true,         false,               true,                true, 0,          0, now(),         now());

-- *** システム標準テーブル ***
