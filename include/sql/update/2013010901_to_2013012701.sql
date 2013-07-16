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
-- * @version    SVN: $Id: 2013010901_to_2013012701.sql 5584 2013-01-28 09:34:59Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
-- システム設定マスター
INSERT INTO _system_config 
(sc_id,                          sc_value,            sc_name) VALUES
('jquery_version',               '1.8',               'jQueryバージョン(PC用)'),
('admin_jquery_version',         '1.6',               '管理画面用jQueryバージョン'),
('s:jquery_version',             '1.8',               'jQueryバージョン(スマートフォン用)');

-- *** システム標準テーブル ***

