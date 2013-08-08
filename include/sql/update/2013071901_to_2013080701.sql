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
-- * @version    SVN: $Id: 2013062901_to_2013071401.sql 6167 2013-07-14 05:41:34Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
-- システム設定マスター
INSERT INTO _system_config 
(sc_id,               sc_value,                  sc_name) VALUES
('smartphone_use_jquery_mobile',    '0',                        'スマートフォン画面でjQuery Mobileを使用');

-- ページIDマスター
ALTER TABLE _page_id ADD pg_available         BOOLEAN        DEFAULT true                  NOT NULL;      -- メニューから選択可能かどうか

-- *** システム標準テーブル ***
