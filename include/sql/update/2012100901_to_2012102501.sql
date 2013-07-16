-- *
-- * バージョンアップ用スクリプト
-- *
-- * PHP versions 5
-- *
-- * LICENSE: This source file is licensed under the terms of the GNU General Public License.
-- *
-- * @package    Magic3 Framework
-- * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
-- * @copyright  Copyright 2006-2012 Magic3 Project.
-- * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
-- * @version    SVN: $Id: 2012100901_to_2012102501.sql 5803 2013-03-07 11:09:50Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
-- メニューIDマスター
ALTER TABLE _menu_id ADD mn_widget_id         VARCHAR(50)    DEFAULT ''                    NOT NULL;      -- ウィジェットID(ファイル名)

-- メニュー定義マスター
ALTER TABLE _menu_def ADD md_param             TEXT                                         NOT NULL;      -- その他パラメータ

-- メニューIDマスター
DELETE FROM _menu_id WHERE mn_id = 'ec_menu';
INSERT INTO _menu_id
(mn_id,         mn_name,          mn_description, mn_device_type, mn_widget_id, mn_sort_order) VALUES
('ec_menu',   'EC用メニュー(PC用)', '「ec_menu」ウィジェット専用のメニュー',             0,   'ec_menu',           10);

-- *** システム標準テーブル ***
