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
-- * @version    SVN: $Id: 2011101101_to_2011112601.sql 4472 2011-11-26 11:00:36Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
-- ユーザグループマスター
DROP TABLE IF EXISTS _user_group;
CREATE TABLE _user_group (
    ug_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    ug_id                VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- グループID
    ug_language_id       VARCHAR(2)     DEFAULT ''                    NOT NULL,      -- 言語ID
    ug_history_index     INT            DEFAULT 0                     NOT NULL,      -- 履歴管理用インデックスNo(0～)

    ug_name              VARCHAR(30)    DEFAULT ''                    NOT NULL,      -- グループ名称
    ug_sort_order        INT            DEFAULT 0                     NOT NULL,      -- ソート用

    ug_create_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード作成者
    ug_create_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード作成日時
    ug_update_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード更新者
    ug_update_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード更新日時
    ug_deleted           BOOLEAN        DEFAULT false                 NOT NULL,      -- レコード削除状態
    PRIMARY KEY          (ug_serial),
    UNIQUE               (ug_id,        ug_language_id,               ug_history_index)
) TYPE=innodb;

-- ユーザとユーザグループの対応付けマスター
DROP TABLE IF EXISTS _user_with_group;
CREATE TABLE _user_with_group (
    uw_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    uw_user_serial       INT            DEFAULT 0                     NOT NULL,      -- ログインユーザシリアル番号
    uw_index             INT            DEFAULT 0                     NOT NULL,      -- インデックス番号(0～)
    uw_group_id          VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- ユーザグループID
    PRIMARY KEY          (uw_serial),
    UNIQUE               (uw_user_serial,    uw_index)
) TYPE=innodb;

-- *** システム標準テーブル ***
-- 個人情報追加フィールド
DROP TABLE IF EXISTS person_info_opt_field;
CREATE TABLE person_info_opt_field (
    pf_id                VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- フィールドID
    pf_language_id       VARCHAR(2)     DEFAULT ''                    NOT NULL,      -- 言語ID
    pf_name              VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- 名称
    pf_field_type        VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- フィールドタイプ
    pf_index             INT            DEFAULT 0                     NOT NULL,      -- ソート用
    PRIMARY KEY          (pf_id,        pf_language_id)
) TYPE=innodb;

-- 個人情報追加フィールド値
DROP TABLE IF EXISTS person_info_opt_value;
CREATE TABLE person_info_opt_value (
    pl_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    pl_person_serial     INT            DEFAULT 0                     NOT NULL,      -- 個人情報シリアル番号
    pl_field_id          VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- 個人情報追加フィールドID
    pl_value             TEXT                                         NOT NULL,      -- 値
    PRIMARY KEY          (pl_serial),
    UNIQUE               (pl_person_serial, pl_field_id)
) TYPE=innodb;
