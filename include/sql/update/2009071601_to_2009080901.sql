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
-- * @version    SVN: $Id: 2009071601_to_2009080901.sql 2214 2009-08-11 02:45:03Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
-- システム設定マスター
INSERT INTO _system_config 
(sc_id,               sc_value,         sc_name) VALUES
('use_ssl_admin',     '0',              '管理画面のSSL通信');

-- ウィジェット情報マスター
ALTER TABLE _widgets ADD wd_has_rss         BOOLEAN        DEFAULT false                 NOT NULL;      -- RSS機能があるかどうか

-- *** システム標準テーブル ***
