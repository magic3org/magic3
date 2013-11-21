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
-- * @version    SVN: $Id: 2009061501_to_2009062601.sql 2026 2009-07-01 10:36:54Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
-- システム設定マスター
INSERT INTO _system_config 
(sc_id,                          sc_value,         sc_name) VALUES
('mobile_use_session',           '1',              '携帯セッション管理');

-- ウィジェット情報マスター
DELETE FROM _widgets WHERE wd_id = 'reg_user';
INSERT INTO _widgets
(wd_id,      wd_name,          wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,           wd_add_script_lib, wd_has_admin, wd_initialized, wd_launch_index, wd_install_dt, wd_create_dt) VALUES
('reg_user', '汎用ユーザ登録', '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                '汎用的なユーザ登録機能', 'md5', true,         true,           0,               now(), now());
DELETE FROM _widgets WHERE wd_id = 'm/default_login';
INSERT INTO _widgets
(wd_id,             wd_name,              wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,             wd_mobile, wd_has_admin, wd_initialized, wd_launch_index, wd_install_dt, wd_create_dt) VALUES
('m/default_login', 'デフォルトログイン', '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'デフォルトのログイン機能', true,      false,        true,           100,             now(),         now());

-- テンプレート情報
DELETE FROM _templates WHERE tm_id = 'm/smallfont';
INSERT INTO _templates
(tm_id,                     tm_name,                   tm_type, tm_mobile, tm_clean_type, tm_create_dt) VALUES
('m/smallfont',             'm/smallfont',             0,       true,      0,             now());

-- メール内容
DELETE FROM _mail_form WHERE mf_id = 'regist_user_auto';
INSERT INTO _mail_form (mf_id,              mf_language_id, mf_subject,         mf_content,                                                                 mf_create_dt) 
VALUES                 ('regist_user_auto', 'ja',           'ユーザ登録',       'ご登録ありがとうございました。\nパスワードを送信します。\nこのパスワードでログインするとユーザとして承認されます。\n\n[#URL#]\n\nパスワード:　[#PASSWORD#]', now());
DELETE FROM _mail_form WHERE mf_id = 'regist_user_auth';
INSERT INTO _mail_form (mf_id,              mf_language_id, mf_subject,         mf_content,                                                                 mf_create_dt) 
VALUES                 ('regist_user_auth', 'ja',           'ユーザ登録',       'ご登録ありがとうございました。\nパスワードを送信します。\n管理者からの承認後、このパスワードでログイン可能になります。\n\nパスワード:　[#PASSWORD#]', now());
DELETE FROM _mail_form WHERE mf_id = 'regist_user_completed';
INSERT INTO _mail_form (mf_id,                   mf_language_id, mf_subject,         mf_content,                                                                 mf_create_dt) 
VALUES                 ('regist_user_completed', 'ja',           'ユーザ自動登録完了',   'ユーザの登録を承認しました。\n\nアカウント:　[#ACCOUNT#]', now());
-- *** システム標準テーブル ***
