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
-- * @version    SVN: $Id: 2010042901_to_2010050901.sql 3120 2010-05-12 00:47:05Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
-- テンプレート情報
INSERT INTO _templates
(tm_id,                         tm_name,                       tm_type, tm_device_type, tm_mobile, tm_clean_type, tm_create_dt) VALUES
('s/default',                   's/default',                   1,       2,              false,     0,             now());

-- バナー項目
ALTER TABLE bn_item ADD bi_attr  TEXT                                         NOT NULL;      -- その他属性(「;」区切り)

-- ウィジェット情報
DELETE FROM _widgets WHERE wd_id = 'banner3';
INSERT INTO _widgets
(wd_id,     wd_name,       wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_add_script_lib, wd_add_script_lib_a, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('banner3', 'バナー表示3', '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'バナー画像をグループ化して、グループごとに表示できるバナー管理ウィジェットです。「バナー表示2」のバージョンアップ版。', '',                '',                  true, true,         true,                true,  0,         1, now(),         now());
DELETE FROM _widgets WHERE wd_id = 's/content';
INSERT INTO _widgets
(wd_id,       wd_name,                wd_type,   wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_device_type, wd_has_admin, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('s/content', 'コンテンツ', 'content', '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                '画像やテキストからできた汎用コンテンツを表示。コンテンツ属性(content)ページ専用。', 2,         true,         true,           0, 2, 2, now(), now());
