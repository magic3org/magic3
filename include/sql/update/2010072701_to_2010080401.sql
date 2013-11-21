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
-- * @version    SVN: $Id: 2010072701_to_2010080401.sql 3466 2010-08-09 03:01:58Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
-- ウィジェット情報マスター(管理用)
DELETE FROM _widgets WHERE wd_id = 'admin_menu3';
INSERT INTO _widgets
(wd_id,         wd_name,           wd_type, wd_author,      wd_copyright, wd_license, wd_official_level, wd_available, wd_editable, wd_initialized, wd_launch_index, wd_install_dt, wd_create_dt) VALUES
('admin_menu3', '管理用メニュー3', 'menu',  'Naoki Hirata', 'Magic3.org', 'GPL',      10,                false,        false,       true,           100,             now(),         now());

-- ウィジェット情報マスター(携帯用)
DELETE FROM _widgets WHERE wd_id = 'm/blog';
INSERT INTO _widgets
(wd_id,      wd_name,          wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_device_type, wd_mobile, wd_has_admin, wd_initialized, wd_install_dt, wd_create_dt) VALUES
('m/blog', 'ブログ', '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10, 'ブログ(携帯用)', 1,              true,      true,         true,              now(),         now());

-- *** システム標準テーブル ***
-- ブログ設定マスター
ALTER TABLE blog_config DROP PRIMARY KEY;                                                                 -- プライマリーキー削除
ALTER TABLE blog_config ADD bg_blog_id          VARCHAR(30)    DEFAULT ''                    NOT NULL;    -- ブログID(空文字列=デフォルト)
ALTER TABLE blog_config ADD PRIMARY KEY (bg_blog_id, bg_id);                                              -- プライマリーキー再設定

INSERT INTO blog_config
(bg_id,                bg_value,    bg_name,                     bg_index) VALUES
('m:entry_view_count', '3',         '記事表示数(携帯)',          1),
('m:entry_view_order', '1',         '記事表示順(携帯)',          2);
