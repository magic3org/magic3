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
-- システム設定マスター
INSERT INTO _system_config 
(sc_id,                     sc_value,         sc_name) VALUES
('site_menu_hier', '1',              'サイトのメニューを階層化するかどうか');

-- テンプレート情報
DELETE FROM _templates WHERE tm_id = 'bootstrap4_custom';
INSERT INTO _templates
(tm_id,                           tm_name,                         tm_type, tm_use_bootstrap, tm_create_dt) VALUES
('bootstrap4_custom',             'bootstrap4_custom',             11,      true,             now());

-- *** システム標準テーブル ***
