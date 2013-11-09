-- *
-- * バージョンアップ用スクリプト
-- *
-- * PHP versions 5
-- *
-- * LICENSE: This source file is licensed under the terms of the GNU General Public License.
-- *
-- * @package    Magic3 Framework
-- * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
-- * @copyright  Copyright 2006-2013 Magic3 Project.
-- * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
-- * @version    SVN: $Id$
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
-- メニューIDマスター
DELETE FROM _menu_id WHERE mn_id = 'm_main_menu';
INSERT INTO _menu_id
(mn_id,         mn_name,          mn_description, mn_device_type, mn_sort_order) VALUES
('m_main_menu', 'メインメニュー(携帯用)', '',             1,              10);

-- ウィジェット情報マスター
ALTER TABLE _widgets ADD wd_content_info         VARCHAR(20)    DEFAULT ''                    NOT NULL;      -- コンテンツ情報

-- システム設定マスター
INSERT INTO _system_config 
(sc_id,                          sc_value,                  sc_name) VALUES
('access_in_intranet',               '0',                       'イントラネット運用');

-- *** システム標準テーブル ***
-- カレンダー日付
ALTER TABLE calendar_date ADD ce_param             TEXT                                         NOT NULL;      -- オプションパラメータ(シリアライズデータ)