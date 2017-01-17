-- *
-- * バージョンアップ用スクリプト
-- *
-- * PHP versions 5
-- *
-- * LICENSE: This source file is licensed under the terms of the GNU General Public License.
-- *
-- * @package    Magic3 Framework
-- * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
-- * @copyright  Copyright 2006-2016 Magic3 Project.
-- * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
-- * @version    SVN: $Id$
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
-- 多言語対応文字列マスター
DELETE FROM _language_string WHERE ls_type = 0 AND ls_id = 'msg_admin_popup_login' AND ls_language_id = 'ja';
INSERT INTO _language_string
(ls_type, ls_id,                           ls_language_id, ls_value,                             ls_name) VALUES
(0,       'msg_admin_popup_login',               'ja',           '', 'ログイン時管理者向けポップアップメッセージ');

-- システム設定マスター
DELETE FROM _system_config WHERE sc_id = 'admin_default_theme';
DELETE FROM _system_config WHERE sc_id = 'default_theme';
INSERT INTO _system_config 
(sc_id,                 sc_value,         sc_name) VALUES
('admin_default_theme', 'smoothness',     '管理画面用jQueryUIテーマ'),
('default_theme',       'smoothness',     'フロント画面用jQueryUIテーマ');

INSERT INTO _system_config 
(sc_id,                          sc_value, sc_name) VALUES
('multi_device_admin', '0',      'マルチデバイス最適化管理画面');

-- *** システム標準テーブル ***

