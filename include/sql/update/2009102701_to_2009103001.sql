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
-- * @version    SVN: $Id: 2009102701_to_2009103001.sql 2494 2009-10-31 08:18:25Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
-- ウィジェット情報(新規追加)
DELETE FROM _widgets WHERE wd_id = 'googlemaps';
INSERT INTO _widgets
(wd_id,        wd_name,        wd_version, wd_author,                       wd_copyright,                    wd_license, wd_official_level, wd_description, wd_add_script_lib, wd_add_script_lib_a, wd_read_scripts, wd_read_css, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('googlemaps', 'Googleマップ', '1.0.0',    '株式会社 毎日メディアサービス', '株式会社 毎日メディアサービス', 'GPL', 10, 'Goolgeマップを表示。', 'jquery', 'jquery',        false,           false,       true,        false,               true,true,           3, 1, now(), now());

-- *** システム標準テーブル ***
