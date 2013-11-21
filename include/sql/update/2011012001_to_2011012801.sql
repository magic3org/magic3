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
-- * @version    SVN: $Id: 2011012001_to_2011012801.sql 3984 2011-02-07 03:01:55Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
-- テンプレート情報マスター
ALTER TABLE _templates ADD tm_joomla_params               TEXT                                         NOT NULL;      -- joomla!用パラメータ

-- *** システム標準テーブル ***
-- イベント設定マスター
DROP TABLE IF EXISTS event_config;
CREATE TABLE event_config (
    eg_id                VARCHAR(30)    DEFAULT ''                    NOT NULL,      -- ID(Key)
    eg_value             TEXT                                         NOT NULL,      -- 値
    eg_name              VARCHAR(50)    DEFAULT ''                    NOT NULL,      -- 名称
    eg_description       VARCHAR(80)    DEFAULT ''                    NOT NULL,      -- 説明
    eg_index             INT            DEFAULT 0                     NOT NULL,      -- ソート用
    PRIMARY KEY          (eg_id)
) TYPE=innodb;

-- イベント記事マスター
DROP TABLE IF EXISTS event_entry;
CREATE TABLE event_entry (
    ee_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    ee_id                INT            DEFAULT 0                     NOT NULL,      -- エントリーID
    ee_language_id       VARCHAR(2)     DEFAULT ''                    NOT NULL,      -- 言語ID
    ee_history_index     INT            DEFAULT 0                     NOT NULL,      -- 履歴管理用インデックスNo(0～)
    
    ee_name              VARCHAR(60)    DEFAULT ''                    NOT NULL,      -- エントリータイトル
    ee_html              TEXT                                         NOT NULL,      -- エントリー本文HTML
    ee_html_ext          TEXT                                         NOT NULL,      -- エントリー本文HTML(結果)
    ee_summary           VARCHAR(300)   DEFAULT ''                    NOT NULL,      -- 概要
    ee_admin_note        VARCHAR(100)   DEFAULT ''                    NOT NULL,      -- 管理者用備考
    ee_place             VARCHAR(100)   DEFAULT ''                    NOT NULL,      -- 場所
    ee_contact           VARCHAR(60)    DEFAULT ''                    NOT NULL,      -- 連絡先(Eメール,電話番号)
    ee_url               TEXT                                         NOT NULL,      -- URL
    ee_status            SMALLINT       DEFAULT 0                     NOT NULL,      -- エントリー状態(0=未設定、1=編集中、2=公開、3=非公開)
    ee_show_comment      BOOLEAN        DEFAULT true                  NOT NULL,      -- コメントを表示するかどうか
    ee_receive_comment   BOOLEAN        DEFAULT false                 NOT NULL,      -- コメントの受け付け可否
    ee_user_limited      BOOLEAN        DEFAULT false                 NOT NULL,      -- 参照ユーザを制限
    ee_start_dt          TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- イベント期間(開始)
    ee_end_dt            TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- イベント期間(終了)

    ee_check_out_user_id INT            DEFAULT 0                     NOT NULL,      -- チェックアウトユーザID(0のときはチェックイン状態)
    ee_check_out_dt      TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- チェックアウト日時
    ee_create_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード作成者
    ee_create_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード作成日時
    ee_update_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード更新者
    ee_update_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード更新日時
    ee_deleted           BOOLEAN        DEFAULT false                 NOT NULL,      -- レコード削除状態
    PRIMARY KEY          (ee_serial),
    UNIQUE               (ee_id,        ee_language_id,               ee_history_index)
) TYPE=innodb;

-- イベントコメントトラン
DROP TABLE IF EXISTS event_comment;
CREATE TABLE event_comment (
    eo_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    eo_entry_id          INT            DEFAULT 0                     NOT NULL,      -- エントリーID
    eo_language_id       VARCHAR(2)     DEFAULT ''                    NOT NULL,      -- 言語ID
    eo_parent_serial     INT            DEFAULT 0                     NOT NULL,      -- 親コメントのシリアル番号
    eo_user_id           INT            DEFAULT 0                     NOT NULL,      -- ユーザID
    eo_regist_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- 投稿日時
    
    eo_name              VARCHAR(50)    DEFAULT ''                    NOT NULL,      -- タイトル
    eo_html              TEXT                                         NOT NULL,      -- 本文HTML
    eo_url               TEXT                                         NOT NULL,      -- 参照用URL
    eo_user_name         VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- ユーザ名
    eo_email             VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- Eメールアドレス
    eo_status            SMALLINT       DEFAULT 0                     NOT NULL,      -- 状態(0=未設定、1=非公開、2=公開)
    
    eo_update_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード更新者
    eo_update_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード更新日時
    eo_deleted           BOOLEAN        DEFAULT false                 NOT NULL,      -- レコード削除状態
    PRIMARY KEY          (eo_serial)
) TYPE=innodb;

-- イベントカテゴリマスター
DROP TABLE IF EXISTS event_category;
CREATE TABLE event_category (
    ec_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    ec_id                VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- カテゴリID
    ec_item_id           VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- カテゴリ項目ID(空=カテゴリ種別、空以外=カテゴリ項目)
    ec_language_id       VARCHAR(2)     DEFAULT ''                    NOT NULL,      -- 言語ID
    ec_history_index     INT            DEFAULT 0                     NOT NULL,      -- 履歴管理用インデックスNo(0～)

    ec_name              VARCHAR(30)    DEFAULT ''                    NOT NULL,      -- カテゴリ名称
    ec_index             INT            DEFAULT 0                     NOT NULL,      -- カテゴリ項目の表示順(カテゴリ項目IDが空のときはカテゴリの表示順)

    ec_create_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード作成者
    ec_create_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード作成日時
    ec_update_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード更新者
    ec_update_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード更新日時
    ec_deleted           BOOLEAN        DEFAULT false                 NOT NULL,      -- レコード削除状態
    PRIMARY KEY          (ec_serial),
    UNIQUE               (ec_id,        ec_item_id,  ec_language_id,  ec_history_index)
) TYPE=innodb;

-- イベント記事とイベント記事カテゴリーの対応付けマスター
DROP TABLE IF EXISTS event_entry_with_category;
CREATE TABLE event_entry_with_category (
    ew_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    ew_entry_id          INT            DEFAULT 0                     NOT NULL,      -- エントリーID
    ew_category_id       VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- カテゴリID
    ew_category_item_id  VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- カテゴリ項目ID
    PRIMARY KEY          (ew_serial),
    UNIQUE               (ew_entry_id,  ew_category_id, ew_category_item_id)
) TYPE=innodb;

-- イベント設定マスター
INSERT INTO event_config
(eg_id,                     eg_value,    eg_name,                              eg_index) VALUES
('receive_comment',         '0',         'コメントの受け付け',                 1),
('entry_view_count',        '10',        '記事表示数',                         2),
('entry_view_order',        '0',         '記事表示順',                         3),
('comment_count',           '100',       '1投稿記事のコメント最大数',          4),
('comment_open_time',       '30',        'コメント投稿可能期間(日)',           5),
('top_contents',            '',          'トップ画面コンテンツ',               6),
('m:entry_view_count',      '3',         '記事表示数(携帯)',                   7),
('m:entry_view_order',      '1',         '記事表示順(携帯)',                   8),
('m:top_contents',          '',          'トップ画面コンテンツ(携帯)',         9);
