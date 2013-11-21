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
-- * @version    SVN: $Id: 2010051201_to_2010051701.sql 3150 2010-05-19 08:01:03Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***

-- *** システム標準テーブル ***

-- ウィジェット情報
DELETE FROM _widgets WHERE wd_id = 'blog_new_box';
INSERT INTO _widgets
(wd_id,          wd_name,         wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,             wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_has_rss, wd_install_dt, wd_create_dt) VALUES
('blog_new_box', 'ブログ - 新規', '1.1.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'ブログの新規一覧を表示。', false,        false,               false,true,               1, -1, true, now(),    now());
DELETE FROM _widgets WHERE wd_id = 'g_analytics';
INSERT INTO _widgets
(wd_id,         wd_name,            wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_has_admin, wd_initialized, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('g_analytics', 'Google Analytics', '2.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'Google Analytics トラッキングコードを出力する。', true,        true,           1, -1, now(), now());
