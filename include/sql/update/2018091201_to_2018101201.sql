-- *
-- * バージョンアップ用スクリプト
-- *
-- * PHP versions 5
-- *
-- * LICENSE: This source file is licensed under the terms of the GNU General Public License.
-- *
-- * @package    Magic3 Framework
-- * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
-- * @copyright  Copyright 2006-2018 Magic3 Project.
-- * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
-- * @version    SVN: $Id$
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
-- ユーザアクセスログトラン
ALTER TABLE _access_log ADD al_landing_page_id                VARCHAR(40)    DEFAULT ''                    NOT NULL;      -- ランディングページID

-- 個人最適化パラメータトラン
DROP TABLE IF EXISTS _personalize_param;
CREATE TABLE _personalize_param (
    pz_id                CHAR(32)       DEFAULT ''                    NOT NULL,      -- クライアントID
    
    pz_param             TEXT                                         NOT NULL,      -- パラメータオブジェクトをシリアライズしたもの
    pz_update_ip         VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- レコード更新アクセス元IP(IPv6対応)
    pz_update_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード更新日時
    PRIMARY KEY          (pz_id)
) ENGINE=innodb;

-- *** システム標準テーブル ***
