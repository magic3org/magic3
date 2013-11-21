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
-- * @version    SVN: $Id: 2008122301_to_2009010101.sql 1414 2009-01-04 13:27:44Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
-- ウィジェット情報マスター
DELETE FROM _widgets WHERE wd_id = 'dropdown_menu';
INSERT INTO _widgets
(wd_id,            wd_name,                 wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_read_scripts, wd_read_css, wd_add_script_lib, wd_add_script_lib_a,                         wd_has_admin, wd_use_instance_def, wd_initialized, wd_install_dt, wd_create_dt) VALUES
('dropdown_menu', 'ドロップダウンメニュー','1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                '複数のメニューが作成できる多階層のドロップダウンメニューです。サブメニューも管理できる共通のメニュー定義を使用します。', true, true, 'jquery.bgiframe,jquery.hoverintent', 'jquery.bgiframe,jquery.hoverintent', true,  true,              true,           now(),         now());

-- *** システム標準テーブル ***
