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
-- * @version    SVN: $Id: 2010051701_to_2010052601.sql 3178 2010-06-02 05:23:47Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
-- ウィジェット情報
DELETE FROM _widgets WHERE wd_id = 'static_content';
INSERT INTO _widgets (wd_id, wd_name, wd_type, wd_version, wd_params, wd_author, wd_copyright, wd_license, wd_official_level, wd_description, wd_read_scripts, wd_read_css, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('static_content',    '固定コンテンツビュー', '', '1.1.0',  '',        'Naoki Hirata', 'Magic3.org', 'GPL', 10, '固定でコンテンツを表示。コンテンツはデフォルトコンテンツビューと共有。',          false,           false,       true,         true,        true,         false,               true,true,           0, 2, 1, now(), now());
DELETE FROM _widgets WHERE wd_id = 'jquery_plugin';
INSERT INTO _widgets
(wd_id,        wd_name,                wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_has_admin, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type,    wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('jquery_plugin', 'jQueryプラグイン', '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'jQueryプラグインを追加するためのウィジェットです。画面上には何も表示されません。',               true,         true,                true,              0,  3,             1, now(),         now());
DELETE FROM _widgets WHERE wd_id = 'access_count';
INSERT INTO _widgets
(wd_id,          wd_name,              wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,               wd_has_admin, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('access_count', 'アクセスカウンター', '2.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'サイトのアクセス数を表示。', true,         false,true,           0, 0, now(), now());
