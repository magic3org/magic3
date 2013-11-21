-- *
-- * バージョンアップ用スクリプト
-- *
-- * PHP versions 5
-- *
-- * LICENSE: This source file is licensed under the terms of the GNU General Public License.
-- *
-- * @package    Magic3 Framework
-- * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
-- * @copyright  Copyright 2006-2010 Magic3 Project.
-- * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
-- * @version    SVN: $Id: 2010100901_to_2010101101.sql 3715 2010-10-19 04:27:12Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***

-- サイト解析状況マスター
DROP TABLE IF EXISTS _analyze_status;
CREATE TABLE _analyze_status (
    as_id                VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- ID(Key)
    as_value             TEXT                                         NOT NULL,      -- 値
    as_update_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- 更新日時
    PRIMARY KEY  (as_id)
) TYPE=innodb;

-- サイト解析ページビュートラン
DROP TABLE IF EXISTS _analyze_page_view;
CREATE TABLE _analyze_page_view (
    ap_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    ap_type              INT            DEFAULT 0                     NOT NULL,      -- データタイプ(0=全データ、1=ブラウザアクセスに限定)
    ap_url               VARCHAR(200)   DEFAULT ''                    NOT NULL,      -- URL
    ap_date              DATE           DEFAULT '0000-00-00'          NOT NULL,      -- 日付
    ap_hour              SMALLINT       DEFAULT 0                     NOT NULL,      -- 時間
    ap_count             INT            DEFAULT 0                     NOT NULL,      -- 参照数
    ap_path              VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- アクセスポイントパス
    PRIMARY KEY          (ap_serial),
    UNIQUE               (ap_type,      ap_url,       ap_date,       ap_hour)
) TYPE=innodb;

-- サイト解析日時カウントトラン
DROP TABLE IF EXISTS _analyze_daily_count;
CREATE TABLE _analyze_daily_count (
    aa_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    aa_type              INT            DEFAULT 0                     NOT NULL,      -- データタイプ(0=訪問数、1=訪問者数)
    aa_url               VARCHAR(200)   DEFAULT ''                    NOT NULL,      -- URL
    aa_date              DATE           DEFAULT '0000-00-00'          NOT NULL,      -- 日付
    aa_count             INT            DEFAULT 0                     NOT NULL,      -- 参照数
    aa_path              VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- アクセスポイントパス
    PRIMARY KEY          (aa_serial),
    UNIQUE               (aa_type,      aa_url,        aa_date)
) TYPE=innodb;

-- *** システム標準テーブル ***

