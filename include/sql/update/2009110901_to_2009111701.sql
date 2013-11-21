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
-- * @version    SVN: $Id: 2009110901_to_2009111701.sql 2562 2009-11-18 03:58:30Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
-- ウィジェット情報(新規追加)
DELETE FROM _widgets WHERE wd_id = 'slide_menu';
INSERT INTO _widgets
(wd_id,        wd_name,           wd_type, wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_add_script_lib, wd_add_script_lib_a,                         wd_has_admin, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('slide_menu', 'スライドメニュー', 'menu', '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'スライドオープンできる2階層のメニューです。共通のメニュー定義を使用します。', 'jquery', 'jquery', true,  true,              true, 3,          1, now(),         now());

-- *** システム標準テーブル ***
