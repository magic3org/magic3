-- *
-- * バージョンアップ用スクリプト
-- *
-- * PHP versions 5
-- *
-- * LICENSE: This source file is licensed under the terms of the GNU General Public License.
-- *
-- * @package    Magic3 Framework
-- * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
-- * @copyright  Copyright 2006-2017 Magic3 Project.
-- * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
-- * @version    SVN: $Id$
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
-- システム設定マスター
DELETE FROM _system_config WHERE sc_id = 'external_jquery';
INSERT INTO _system_config 
(sc_id,                      sc_value,            sc_name) VALUES
('external_jquery',          '0',                 'システム外部のjQueryを使用');

-- テンプレート情報マスター
ALTER TABLE _templates ADD tm_attr               TEXT                                         NOT NULL;      -- その他属性(「,」区切り)(woocommerce等)

DELETE FROM _templates WHERE tm_id = '_admin';
INSERT INTO _templates
(tm_id,                           tm_name,                         tm_type, tm_device_type, tm_mobile, tm_use_bootstrap, tm_available, tm_generator, tm_version) VALUES
('_admin',                       '_admin',                         2,       0,              false,     true,             false,        '',           '');

-- システム設定マスター(管理画面用デフォルトテンプレートを変更)
UPDATE _system_config SET sc_value = '_admin' WHERE sc_id = 'admin_default_template';

-- *** システム標準テーブル ***
