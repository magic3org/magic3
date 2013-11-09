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
-- テンプレート情報マスター
ALTER TABLE _templates ADD tm_use_bootstrap         BOOLEAN        DEFAULT false                  NOT NULL;      -- Bootstrapを使用するかどうか

-- *** システム標準テーブル ***
-- カレンダー時間枠
DROP TABLE IF EXISTS time_period;
CREATE TABLE time_period (
    to_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    to_date_type_id      INT            DEFAULT 0                     NOT NULL,      -- 日付タイプID(1～,-1以下=カレンダー日付のシリアル番号)
    to_index             INT            DEFAULT 0                     NOT NULL,      -- インデックス番号(0～)
    
    to_name              VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- 名前
    to_start_time        TIME           DEFAULT '00:00:00'            NOT NULL,      -- 開始時刻
    to_minute            INT            DEFAULT 0                     NOT NULL,      -- 時間(分)
    PRIMARY KEY          (to_serial),
    UNIQUE               (to_date_type_id,        to_index)
) TYPE=innodb;

-- 日付タイプ
DROP TABLE IF EXISTS date_type;
CREATE TABLE date_type (
    dt_id                INT            DEFAULT 0                     NOT NULL,      -- 日付タイプID
    
    dt_name              VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- 名称
    dt_sort_order        INT            DEFAULT 0                     NOT NULL,      -- ソート順

    dt_update_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード更新者
    dt_update_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード更新日時
    dt_deleted           BOOLEAN        DEFAULT false                 NOT NULL,      -- レコード削除状態
    PRIMARY KEY          (dt_id)
) TYPE=innodb;

-- カレンダー日付
DROP TABLE IF EXISTS calendar_date;
CREATE TABLE calendar_date (
    ce_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    ce_def_id            INT            DEFAULT 0                     NOT NULL,      -- カレンダー定義ID
    ce_type              INT            DEFAULT 0                     NOT NULL,      -- データタイプ(0=インデックス番号,1=日付,10=基本日オプション(インデックス番号))
    ce_index             INT            DEFAULT 0                     NOT NULL,      -- インデックス番号(0～)
    ce_date              DATE           DEFAULT '0000-00-00'          NOT NULL,      -- 日付
    
    ce_name              VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- 名前
    ce_date_type_id      INT            DEFAULT 0                     NOT NULL,      -- 日付タイプID(1～,-1=個別時間定義)
    ce_style             TEXT                                         NOT NULL,      -- HTMLスタイル属性
    PRIMARY KEY          (ce_serial),
    UNIQUE               (ce_def_id,    ce_type,     ce_index,        ce_date)
) TYPE=innodb;

-- カレンダー定義
DROP TABLE IF EXISTS calendar_def;
CREATE TABLE calendar_def (
    cd_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    cd_id                INT            DEFAULT 0                     NOT NULL,      -- カレンダー定義ID
    cd_history_index     INT            DEFAULT 0                     NOT NULL,      -- 履歴管理用インデックスNo(0～)
    
    cd_name              VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- 名前
    cd_repeat_type       INT            DEFAULT 0                     NOT NULL,      -- 繰り返しタイプ(0=繰り返しなし,1=曜日基準,2=日付基準)
    cd_date_count        INT            DEFAULT 0                     NOT NULL,      -- 所要日数
    cd_style             TEXT                                         NOT NULL,      -- HTMLスタイル属性
    cd_active_start_dt   TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- 有効期間(開始)
    cd_active_end_dt     TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- 有効期間(終了)
    
    cd_create_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード作成者
    cd_create_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード作成日時
    cd_update_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード更新者
    cd_update_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード更新日時
    cd_deleted           BOOLEAN        DEFAULT false                 NOT NULL,      -- レコード削除状態
    PRIMARY KEY          (cd_serial),
    UNIQUE               (cd_id,        cd_history_index)
) TYPE=innodb;

-- カレンダー初期データ
INSERT INTO time_period
(to_date_type_id, to_index, to_name, to_start_time, to_minute) VALUES
(1, 0, '午前', '09:00:00', 180),
(1, 1, '午後', '13:00:00', 240),
(2, 0, '午前', '09:00:00', 180);
INSERT INTO date_type
(dt_id, dt_name, dt_sort_order) VALUES
(1, '営業日(終日)', 1),
(2, '営業日(午前のみ)', 2);
