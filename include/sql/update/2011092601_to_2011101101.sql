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
-- * @version    SVN: $Id: 2011092601_to_2011101101.sql 4417 2011-10-27 22:53:44Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
-- システム設定マスター
DELETE FROM _system_config WHERE sc_id = 'fix_ie6_transparent_png';
INSERT INTO _system_config 
(sc_id,                       sc_value,           sc_name) VALUES
('fix_ie6_transparent_png', '0', 'IE6の透過PNG対応');

-- *** システム標準テーブル ***
-- 写真情報マスター
ALTER TABLE photo ADD ht_rate_average       DECIMAL(4,2)  DEFAULT 0                     NOT NULL;      -- 評価平均値
ALTER TABLE photo ADD ht_view_count         INT           DEFAULT 0                     NOT NULL;      -- 参照数

-- 画像評価トラン
DROP TABLE IF EXISTS photo_rate;
CREATE TABLE photo_rate (
    hr_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    hr_photo_id          INT            DEFAULT 0                     NOT NULL,      -- 画像ID
    hr_language_id       VARCHAR(2)     DEFAULT ''                    NOT NULL,      -- 言語ID
    hr_ip                VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- アクセス元IP(IPv6対応)
    hr_parent_serial     INT            DEFAULT 0                     NOT NULL,      -- 親コメントのシリアル番号
    hr_user_id           INT            DEFAULT 0                     NOT NULL,      -- ユーザID
    hr_regist_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- 投稿日時
    
    hr_rate_value        SMALLINT       DEFAULT 0                     NOT NULL,      -- 評価値
    hr_message           TEXT                                         NOT NULL,      -- メッセージ
    hr_status            SMALLINT       DEFAULT 0                     NOT NULL,      -- 状態(0=未設定、1=非公開、2=公開)
    
    hr_update_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード更新者
    hr_update_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード更新日時
    hr_deleted           BOOLEAN        DEFAULT false                 NOT NULL,      -- レコード削除状態
    PRIMARY KEY          (hr_serial)
) TYPE=innodb;
