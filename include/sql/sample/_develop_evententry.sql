-- *
-- * データ登録スクリプト「開発ウィジェット登録」
-- *
-- * PHP versions 5
-- *
-- * LICENSE: This source file is licensed under the terms of the GNU General Public License.
-- *
-- * @package    Magic3 Framework
-- * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
-- * @copyright  Copyright 2006-2014 Magic3 Project.
-- * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
-- * @version    SVN: $Id$
-- * @link       http://www.magic3.org
-- *
-- [開発ウィジェット登録]   *****仕様変更あり注意*****
-- 開発中のウィジェットの登録を行う。
-- ・イベント参加機能

-- イベント参加情報マスター
DROP TABLE IF EXISTS evententry_info;
CREATE TABLE evententry_info (
    ei_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    ei_id                INT            DEFAULT 0                     NOT NULL,      -- イベント参加情報ID
    ei_content_type      VARCHAR(10)    DEFAULT ''                    NOT NULL,      -- コンテンツ種別
    ei_contents_id       VARCHAR(32)    DEFAULT ''                    NOT NULL,      -- 共通コンテンツID
    ei_type              VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- 受付タイプ
    ei_history_index     INT            DEFAULT 0                     NOT NULL,      -- 履歴管理用インデックスNo(0～)
    
    ei_code              VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- イベント参加コード
    ei_html              TEXT                                         NOT NULL,      -- 説明
    ei_max_entry         INT            DEFAULT 0                     NOT NULL,      -- 最大受付数(0は定員なし)
    ei_expire_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- 有効期限
    
    ei_create_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード作成者
    ei_create_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード作成日時
    ei_update_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード更新者
    ei_update_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード更新日時
    ei_deleted           BOOLEAN        DEFAULT false                 NOT NULL,      -- レコード削除状態
    PRIMARY KEY          (ei_serial),
    UNIQUE               (ei_id,        ei_content_type,      ei_contents_id,       ei_type,       ei_history_index)
) ENGINE=innodb;

-- イベント参加要求トラン
DROP TABLE IF EXISTS evententry_request;
CREATE TABLE evententry_request (
    er_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    er_info_id           INT            DEFAULT 0                     NOT NULL,      -- イベント参加情報ID
    er_index             INT            DEFAULT 0                     NOT NULL,      -- インデックス番号(0～)
    
    er_code              VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- 受付コード
    er_user_id           INT            DEFAULT 0                     NOT NULL,      -- 参加者
    er_status            SMALLINT       DEFAULT 0                     NOT NULL,      -- 状態(0=未設定、1=参加、2=キャンセル)
    
    er_create_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード作成者
    er_create_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード作成日時
    er_update_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード更新者
    er_update_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード更新日時
    er_deleted           BOOLEAN        DEFAULT false                 NOT NULL,      -- レコード削除状態
    PRIMARY KEY          (er_serial),
    UNIQUE               (er_info_id,   er_index)
) ENGINE=innodb;
