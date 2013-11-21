-- *
-- * バージョンアップ用スクリプト
-- *
-- * PHP versions 5
-- *
-- * LICENSE: This source file is licensed under the terms of the GNU General Public License.
-- *
-- * @package    Magic3 Framework
-- * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
-- * @copyright  Copyright 2006-2010 Magic3 Project.
-- * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
-- * @version    SVN: $Id: 2010071301_to_2010072301.sql 3427 2010-07-26 03:47:18Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
-- ウィジェット情報マスター(管理用)
INSERT INTO _widgets
(wd_id,         wd_name,           wd_author,      wd_copyright, wd_license, wd_official_level, wd_available, wd_editable, wd_initialized, wd_launch_index, wd_install_dt, wd_create_dt) VALUES
('admin_menu3', '管理用メニュー3', 'Naoki Hirata', 'Magic3.org', 'GPL',      10,                false,        false,       true,           100,             now(),         now());
-- ウィジェット情報マスター(PC用)
DELETE FROM _widgets WHERE wd_id = 'pretty_photo';
INSERT INTO _widgets
(wd_id,          wd_name,          wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_add_script_lib, wd_add_script_lib_a, wd_read_scripts, wd_read_css, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('pretty_photo', 'プリティフォト', '2.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10, 'サムネール表示した画像を拡大します。', 'jquery.prettyphoto', 'jquery.tablednd,jquery.prettyphoto',               false,           false,       true,         true,        true,         true,                                true,                true,              0,  3,             1, now(),         now());
