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

-- *** システム標準テーブル ***
-- イベント記事マスター
ALTER TABLE event_entry ADD ee_regist_user_id    INT            DEFAULT 0                     NOT NULL;      -- エントリー作者
ALTER TABLE event_entry ADD ee_is_all_day        BOOLEAN        DEFAULT false                 NOT NULL;      -- 終日イベントかどうか

-- イベントカテゴリーマスター
DROP TABLE IF EXISTS event_category;
CREATE TABLE event_category (
    ec_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    ec_id                INT            DEFAULT 0                     NOT NULL,      -- カテゴリーID
    ec_language_id       VARCHAR(2)     DEFAULT ''                    NOT NULL,      -- 言語ID
    ec_history_index     INT            DEFAULT 0                     NOT NULL,      -- 履歴管理用インデックスNo(0～)

    ec_name              VARCHAR(30)    DEFAULT ''                    NOT NULL,      -- カテゴリー名称
    ec_parent_id         INT            DEFAULT 0                     NOT NULL,      -- 親カテゴリーID
    ec_sort_order        INT            DEFAULT 0                     NOT NULL,      -- ソート用
    ec_visible           BOOLEAN        DEFAULT true                  NOT NULL,      -- 表示するかどうか

    ec_create_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード作成者
    ec_create_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード作成日時
    ec_update_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード更新者
    ec_update_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード更新日時
    ec_deleted           BOOLEAN        DEFAULT false                 NOT NULL,      -- レコード削除状態
    PRIMARY KEY          (ec_serial),
    UNIQUE               (ec_id,        ec_language_id,               ec_history_index)
) TYPE=innodb;

-- イベント記事とイベント記事カテゴリーの対応付けマスター
DROP TABLE IF EXISTS event_entry_with_category;
CREATE TABLE event_entry_with_category (
    ew_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    ew_entry_serial      INT            DEFAULT 0                     NOT NULL,      -- ブログ記事シリアル番号
    ew_index             INT            DEFAULT 0                     NOT NULL,      -- インデックス番号
    ew_category_id       INT            DEFAULT 0                     NOT NULL,      -- ブログ記事カテゴリーID
    PRIMARY KEY          (ew_serial),
    UNIQUE               (ew_entry_serial,      ew_index)
) TYPE=innodb;

-- イベント設定マスター
INSERT INTO event_config
(eg_id,                     eg_value,    eg_name,                              eg_index) VALUES
('msg_no_entry_in_future',  '今後のイベントはありません',         '予定イベントなし時メッセージ',                 0),
('catagory_count',          '2', 'カテゴリー最大数', 0);