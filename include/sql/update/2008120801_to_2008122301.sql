-- *
-- * バージョンアップ用スクリプト
-- *
-- * PHP versions 5
-- *
-- * LICENSE: This source file is licensed under the terms of the GNU General Public License.
-- *
-- * @package    Magic3 Framework
-- * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
-- * @copyright  Copyright 2006-2008 Magic3 Project.
-- * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
-- * @version    SVN: $Id: 2008120801_to_2008122301.sql 1396 2008-12-26 07:19:52Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
-- ウィジェット情報マスター
DELETE FROM _widgets WHERE wd_id = 'accordion_menu';
INSERT INTO _widgets
(wd_id,            wd_name,                 wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_add_script_lib, wd_add_script_lib_a,                         wd_has_admin, wd_use_instance_def, wd_initialized, wd_install_dt, wd_create_dt) VALUES
('accordion_menu', 'アコーディオンメニュー','1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                '複数のメニューが作成できる2階層のアコーディオンメニューです。サブメニューも管理できる共通のメニュー定義を使用します。', 'jquery-ui,jquery-ui-plus', 'jquery-ui,jquery-ui-plus', true,  true,              true,           now(),         now());

-- *** システム標準テーブル ***
