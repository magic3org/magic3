-- *
-- * テスト用テーブル作成スクリプト
-- *
-- * PHP versions 5
-- *
-- * LICENSE: This source file is licensed under the terms of the GNU General Public License.
-- *
-- * @package    Magic3 Framework
-- * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
-- * @copyright  Copyright 2006-2008 Magic3 Project.
-- * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
-- * @version    SVN: $Id: create_test.sql 1258 2008-11-20 03:33:46Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- テスト用テーブル作成スクリプト
-- --------------------------------------------------------------------------------------------------

DROP TABLE IF EXISTS _test1;
CREATE TABLE _test1 (
    t1_id                VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- ID(Key)
    t1_value             TEXT                                         NOT NULL,      -- 値
    t1_name              VARCHAR(60)    DEFAULT ''                    NOT NULL,      -- 名称
    t1_description       VARCHAR(80)    DEFAULT ''                    NOT NULL,      -- 説明
    t1_index             INT            DEFAULT 0                     NOT NULL,      -- ソート用
    PRIMARY KEY          (t1_id)
) TYPE=innodb;

DROP TABLE IF EXISTS _test2;
CREATE TABLE _test2 (
    t2_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    t2_id                VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- ID(Key)
    t2_history_index     INT            DEFAULT 0                     NOT NULL,      -- 履歴管理用インデックスNo(0～)
    
    t2_value             TEXT                                         NOT NULL,      -- 値
    t2_name              VARCHAR(60)    DEFAULT ''                    NOT NULL,      -- 名称
    t2_description       VARCHAR(80)    DEFAULT ''                    NOT NULL,      -- 説明
    
    t2_create_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード作成者
    t2_create_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード作成日時
    t2_update_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード更新者
    t2_update_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード更新日時
    t2_deleted           BOOLEAN        DEFAULT false                 NOT NULL,      -- レコード削除状態
    PRIMARY KEY          (t2_serial),
    UNIQUE               (t2_id,        t2_history_index)
) TYPE=innodb;

