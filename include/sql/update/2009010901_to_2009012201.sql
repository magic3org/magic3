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
-- * @version    SVN: $Id: 2009010901_to_2009012201.sql 1464 2009-01-23 08:42:04Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
INSERT INTO _system_config 
(sc_id,                          sc_value,                  sc_name) VALUES
('mobile_auto_redirect',         '0',                       '携帯アクセスの自動遷移');

-- *** システム標準テーブル ***
ALTER TABLE _mail_send_log MODIFY ms_body         TEXT NOT NULL;
ALTER TABLE _mail_form     MODIFY mf_content      TEXT NOT NULL;
ALTER TABLE bbs_thread     MODIFY se_html         TEXT NOT NULL;
ALTER TABLE product        MODIFY pt_description  TEXT NOT NULL;
