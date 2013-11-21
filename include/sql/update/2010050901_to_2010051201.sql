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
-- * @version    SVN: $Id: 2010050901_to_2010051201.sql 3130 2010-05-14 07:06:16Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
-- メニューIDマスター
ALTER TABLE _menu_id ADD mn_device_type                INT            DEFAULT 0                     NOT NULL;      -- 端末タイプ(0=PC、1=携帯、2=スマートフォン)
INSERT INTO _menu_id
(mn_id,         mn_name,                          mn_description, mn_device_type, mn_sort_order) VALUES
('s_main_menu', 'スマートフォン用メインメニュー', '',             2,              0);

-- *** システム標準テーブル ***
-- バナー項目
ALTER TABLE bn_item MODIFY bi_html TEXT NOT NULL;      -- テンプレートHTML

-- ウィジェット情報
DELETE FROM _widgets WHERE wd_id = 's/slide_menu';
INSERT INTO _widgets
(wd_id,        wd_name,           wd_type, wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_device_type, wd_add_script_lib, wd_add_script_lib_a,                         wd_has_admin, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('s/slide_menu', 'スライドメニュー', 'menu', '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'スライドオープンできる2階層のメニューです。共通のメニュー定義を使用します。', 2, 'jquery', 'jquery', true,  true,              true, 3,          1, now(),         now());
