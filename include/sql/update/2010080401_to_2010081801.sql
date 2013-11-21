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
-- * @version    SVN: $Id: 2010080401_to_2010081801.sql 3513 2010-08-19 05:56:07Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
-- ウィジェット情報(携帯用)
DELETE FROM _widgets WHERE wd_id = 'm/blog_search';
INSERT INTO _widgets
(wd_id,           wd_name,      wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,         wd_mobile, wd_has_admin, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('m/blog_search', 'ブログ検索', '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'ブログ記事を検索する', true,      false,        true,           0, 1, -1, now(),    now());
DELETE FROM _widgets WHERE wd_id = 'm/separator';
INSERT INTO _widgets
(wd_id,         wd_name,      wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,               wd_mobile, wd_has_admin, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('m/separator', 'セパレータ', '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'ウィジェット間の区切り線。', true,      false,        true,          0, 1, -1, now(), now());
