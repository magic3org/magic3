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
-- システム設定マスター
INSERT INTO _system_config 
(sc_id,                          sc_value,             sc_name) VALUES
('server_tools_user',                 '',                   '管理ツールアカウント'),
('server_tools_password',                '',                   '管理ツールパスワード');

-- システム設定マスター(管理画面用デフォルトテンプレートを変更)
UPDATE _system_config SET sc_value = '1.9' WHERE sc_id = 'admin_jquery_version';

-- テンプレート情報
DELETE FROM _templates WHERE tm_id = 'themler_sample2';
INSERT INTO _templates
(tm_id,                    tm_name,                 tm_type, tm_generator, tm_version) VALUES
('themler_sample2',        'themler_sample2',       2,       'themler',    '1.0.53');
