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
-- * @version    SVN: $Id: 2010021601_to_2010030301.sql 2904 2010-03-08 12:29:58Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システム標準テーブル ***
-- システム設定マスター
INSERT INTO _system_config 
(sc_id,                       sc_value,         sc_name) VALUES
('dev_use_latest_script_lib', '0',              '最新JavaScriptライブラリの使用(開発用)'),
('google_maps_key',           '',               'Googleマップ利用キー');

-- ウィジェット情報
DELETE FROM _widgets WHERE wd_id = 'pretty_photo';
INSERT INTO _widgets
(wd_id,   wd_name, wd_version, wd_params, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_add_script_lib, wd_add_script_lib_a, wd_read_scripts, wd_read_css, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('pretty_photo', 'プリティフォト',  '1.1.0',    '',        'Naoki Hirata', 'Magic3.org', 'GPL',      10, 'サムネール表示した画像を拡大します。', 'jquery', 'jquery.tablednd',               true,           true,       true,         true,        true,         true,                                true,                true,              0,  3,             1, now(),         now());
DELETE FROM _widgets WHERE wd_id = 'breadcrumb';
INSERT INTO _widgets
(wd_id,        wd_name,          wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,                         wd_has_admin, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('breadcrumb', 'パンくずリスト', '1.0.0',    '株式会社 毎日メディアサービス', '株式会社 毎日メディアサービス', 'GPL',      10,                'メニュー定義からパンくずリストを作成', false,        true,           100, 0, 0, now(),    now());
