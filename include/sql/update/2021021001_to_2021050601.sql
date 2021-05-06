-- *
-- * バージョンアップ用スクリプト
-- *
-- * PHP versions 5
-- *
-- * LICENSE: This source file is licensed under the terms of the GNU General Public License.
-- *
-- * @package    Magic3 Framework
-- * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
-- * @copyright  Copyright 2006-2021 Magic3 Project.
-- * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
-- * @version    SVN: $Id$
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
-- テンプレート情報
DELETE FROM _templates WHERE tm_id = '_system';
INSERT INTO _templates
(tm_id,                 tm_name,               tm_type, tm_generator, tm_version, tm_use_bootstrap, tm_has_admin, tm_available, tm_create_dt) VALUES
('_system',             '_system',             11,      '',           '2.0',      true,             false,        false,        now()); -- システム画面用テンプレート

-- *** システム標準テーブル ***

