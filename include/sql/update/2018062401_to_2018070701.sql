-- *
-- * バージョンアップ用スクリプト
-- *
-- * PHP versions 5
-- *
-- * LICENSE: This source file is licensed under the terms of the GNU General Public License.
-- *
-- * @package    Magic3 Framework
-- * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
-- * @copyright  Copyright 2006-2018 Magic3 Project.
-- * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
-- * @version    SVN: $Id$
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***

-- テンプレート情報
ALTER TABLE _templates ADD tm_has_admin         BOOLEAN        DEFAULT false                 NOT NULL;      -- 管理画面があるかどうか

DELETE FROM _templates WHERE tm_id = 'bootstrap4_custom';
INSERT INTO _templates
(tm_id,                           tm_name,                         tm_type, tm_use_bootstrap, tm_has_admin, tm_create_dt) VALUES
('bootstrap4_custom',             'bootstrap4_custom',             11,      true,             true,         now());

-- *** システム標準テーブル ***
