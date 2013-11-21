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
-- * @version    SVN: $Id: 2009060601_to_2009061501.sql 2000 2009-06-21 06:12:45Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
-- システム設定マスター
INSERT INTO _system_config 
(sc_id,                          sc_value,                  sc_name) VALUES
('distribution_name',            'magic3.org',              'ディストリビューション名'),
('distribution_version',         '',                        'ディストリビューションバージョン');

-- ウィジェット情報マスター
DELETE FROM _widgets WHERE wd_id = 'print';
INSERT INTO _widgets
(wd_id,   wd_name, wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,                                     wd_read_scripts, wd_read_css, wd_add_script_lib, wd_add_script_lib_a, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_install_dt, wd_create_dt) VALUES
('print', '印刷',  '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'あらかじめ印刷部分を設定した部分印刷を行います。', true,            false,       'jquery',                       '',                  true,         false,               true,                true,           now(),         now());
DELETE FROM _widgets WHERE wd_id = 'contactus_custom';
INSERT INTO _widgets
(wd_id,              wd_name,                          wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,                             wd_read_scripts, wd_read_css,wd_add_script_lib, wd_add_script_lib_a, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_install_dt, wd_create_dt) VALUES
('contactus_custom', 'カスタムお問い合わせメール送信', '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'カスタマイズ可能なお問い合わせメール送信', false,           false,     '', 'jquery.tablednd', true,         false,               true,                true,           now(),         now());

-- *** システム標準テーブル ***
