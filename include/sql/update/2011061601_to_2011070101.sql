-- *
-- * バージョンアップ用スクリプト
-- *
-- * PHP versions 5
-- *
-- * LICENSE: This source file is licensed under the terms of the GNU General Public License.
-- *
-- * @package    Magic3 Framework
-- * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
-- * @copyright  Copyright 2006-2011 Magic3 Project.
-- * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
-- * @version    SVN: $Id: 2011061601_to_2011070101.sql 4230 2011-07-19 03:35:44Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
-- システム設定マスター
DELETE FROM _system_config WHERE sc_id = 'portal_server_version';
DELETE FROM _system_config WHERE sc_id = 'portal_server_url';
DELETE FROM _system_config WHERE sc_id = 'site_registered_in_portal';
INSERT INTO _system_config 
(sc_id,                       sc_value,           sc_name) VALUES
('portal_server_version',     '',                 'ポータルサーババージョン'),
('portal_server_url',         'http://magic3.me', 'ポータルサーバURL'),
('site_registered_in_portal', '0', 'ポータルサーバへのサイトの登録状況');
