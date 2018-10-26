-- *
-- * データ登録スクリプト「テストウィジェット登録」
-- *
-- * PHP versions 5
-- *
-- * LICENSE: This source file is licensed under the terms of the GNU General Public License.
-- *
-- * @package    Magic3 Framework
-- * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
-- * @copyright  Copyright 2006-2010 Magic3 Project.
-- * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
-- * @version    SVN: $Id: _test.sql 5753 2013-02-28 13:23:08Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- [テストウィジェット登録]
-- テスト用のウィジェットの登録を行う。

-- ウィジェット情報
DELETE FROM _widgets WHERE wd_id = 'date';
INSERT INTO _widgets (wd_id, wd_name, wd_type, wd_version, wd_params, wd_author, wd_copyright, wd_license, wd_official_level, wd_description, wd_read_scripts, wd_read_css, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_install_dt, wd_create_dt) VALUES
('date',               '現在日時',                   'DTST', '1.0.0',  '',  'Naoki Hirata', 'Magic3.org', 'GPL', 10, '現在の日付時刻を表示。',      false,           false,       true,         true,        false,        false,               false,true,           0, now(), now());
DELETE FROM _widgets WHERE wd_id = 'ajax_sample1';
INSERT INTO _widgets 
(wd_id,          wd_name,                  wd_type, wd_version, wd_params, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_read_scripts, wd_read_css, wd_use_ajax, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_install_dt, wd_create_dt) VALUES
('ajax_sample1', 'Ajaxサンプルプログラム', 'AJSP',  '1.0.0',    '',        'Naoki Hirata', 'Magic3.org', 'GPL',      10, 'Ajaxサンプルプログラム。サーバと通信し、サーバの時刻を取得。',                true,            true,        true,        true,         true,               false,        true,                false,               true,              0, now(), now());
DELETE FROM _widgets WHERE wd_id = 'ajax_sample2';
INSERT INTO _widgets 
(wd_id,          wd_name,                   wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_add_script_lib, wd_read_css, wd_use_ajax, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_install_dt, wd_create_dt) VALUES
('ajax_sample2', 'Ajaxサンプルプログラム2', '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'Ajaxサンプルプログラム。',  'jquery.jcarousel', true, true,        false,        true,                false,               true,             now(), now());
DELETE FROM _widgets WHERE wd_id = 'test_ckeditor';
INSERT INTO _widgets
(wd_id,           wd_name,            wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_install_dt, wd_create_dt) VALUES
('test_ckeditor', 'CKEditorテスト用', '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10, 'CKEditorテスト用のプログラム',  true,         true,        true,        false,               false,true,           now(), now());
DELETE FROM _widgets WHERE wd_id = 'test_message';
INSERT INTO _widgets
(wd_id,           wd_name,            wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_install_dt, wd_create_dt) VALUES
('test_message', 'メッセージテスト用', '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10, 'メッセージ出力テスト用のプログラム',  true,         true,        true,        false,               false,true,           now(), now());
DELETE FROM _widgets WHERE wd_id = 'test_login';
INSERT INTO _widgets
(wd_id,           wd_name,            wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_add_script_lib, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_install_dt, wd_create_dt) VALUES
('test_login', 'ログインテスト用', '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10, 'ログインテスト用のプログラム',  'md5', true,         true,        false,        false,               false,true,           now(), now());
DELETE FROM _widgets WHERE wd_id = 'test_uploadfile';
INSERT INTO _widgets
(wd_id,             wd_name,                        wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_add_script_lib, wd_add_script_lib_a, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_install_dt, wd_create_dt) VALUES
('test_uploadfile', 'ファイルアップロードテスト用', '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10, 'ドラッグ&ドロップファイルアップロードテスト用のプログラム',  'jquery.uploadfile4', 'jquery.uploadfile4', true,         true,        true,        false,               false,true,           now(), now());
