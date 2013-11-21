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
-- * @version    SVN: $Id: 2009070601_to_2009071401.sql 2115 2009-07-14 09:49:58Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
-- システム設定マスター
INSERT INTO _system_config 
(sc_id,               sc_value,         sc_name) VALUES
('ssl_root_url',      '',               'SSL用のルートURL');

-- ウィジェット情報マスター
DELETE FROM _widgets WHERE wd_id = 'm/adtag';
INSERT INTO _widgets
(wd_id,      wd_name,   wd_version, wd_params, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_mobile, wd_has_admin, wd_use_instance_def, wd_initialized, wd_install_dt, wd_create_dt) VALUES
('m/adtag', '広告タグ', '1.0.0',    '',        'Naoki Hirata', 'Magic3.org', 'GPL',      10, '広告タグを埋め込む。',               true,      true,         true, true,              now(),         now());
DELETE FROM _widgets WHERE wd_id = 'm/news';
INSERT INTO _widgets
(wd_id,      wd_name,          wd_version, wd_params, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_mobile, wd_has_admin, wd_use_instance_def, wd_initialized, wd_install_dt, wd_create_dt) VALUES
('m/news', 'お知らせ', '1.0.0',    '',        'Naoki Hirata', 'Magic3.org', 'GPL',      10, 'お知らせを表示する。',               true,      true,         true,   true,           now(),         now());
DELETE FROM _widgets WHERE wd_id = 'm/contactus_custom';
INSERT INTO _widgets
(wd_id,      wd_name,          wd_version, wd_params, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_add_script_lib, wd_add_script_lib_a, wd_mobile, wd_has_admin, wd_use_instance_def, wd_initialized, wd_install_dt, wd_create_dt) VALUES
('m/contactus_custom', 'カスタムお問い合わせ', '1.0.0',    '',        'Naoki Hirata', 'Magic3.org', 'GPL',      10, 'カスタマイズ可能なお問い合わせメール送信', '', 'jquery.tablednd',              true,      true,         true, true,              now(),         now());

-- *** システム標準テーブル ***
