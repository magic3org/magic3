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
-- * @version    SVN: $Id: 2012022401_to_2012031501.sql 4764 2012-03-17 13:28:15Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
-- システム設定マスター
INSERT INTO _system_config 
(sc_id,                sc_value,         sc_name) VALUES
('site_logo_filename', 'logo_72c.jpg',   'サイトロゴファイル名');

-- ユーザアクセスログトラン
ALTER TABLE _access_log ADD al_cookie      BOOLEAN        DEFAULT false                 NOT NULL;      -- クッキーがあるかどうか
ALTER TABLE _access_log ADD al_crawler     BOOLEAN        DEFAULT false                 NOT NULL;      -- クローラかどうか
