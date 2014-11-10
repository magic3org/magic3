-- *
-- * バージョンアップ用スクリプト
-- *
-- * PHP versions 5
-- *
-- * LICENSE: This source file is licensed under the terms of the GNU General Public License.
-- *
-- * @package    Magic3 Framework
-- * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
-- * @copyright  Copyright 2006-2009 Magic3 Project.
-- * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
-- * @version    SVN: $Id: 2009051301_to_2009052001.sql 1935 2009-05-29 01:27:58Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
-- システム設定マスター
INSERT INTO _system_config 
(sc_id,                          sc_value,                  sc_name) VALUES
('site_pc_in_public',            '1',                       'PC用サイト公開'),
('site_mobile_in_public',        '1',                       '携帯用サイト公開');

-- メニューIDマスター
ALTER TABLE _menu_id ADD mn_type           INT            DEFAULT 0                     NOT NULL;      -- メニュータイプ(0=単階層、1=多階層)

-- ウィジェット情報マスター
DELETE FROM _widgets WHERE wd_id = 'm/quizk';
INSERT INTO _widgets
(wd_id,     wd_name,        wd_description,                         wd_mobile, wd_has_admin, wd_initialized, wd_install_dt, wd_create_dt) VALUES
('m/quizk', '携帯クイズ王', '携帯用のクイズサイト構築ウィジェット', true,      true,         true,           now(),         now());

-- *** システム標準テーブル ***
-- クイズ設定マスター
DROP TABLE IF EXISTS quiz_config;
CREATE TABLE quiz_config (
    qc_id                VARCHAR(30)    DEFAULT ''                    NOT NULL,      -- ID(Key)
    qc_value             TEXT                                         NOT NULL,      -- 値
    qc_name              VARCHAR(50)    DEFAULT ''                    NOT NULL,      -- 名称
    qc_description       VARCHAR(80)    DEFAULT ''                    NOT NULL,      -- 説明
    qc_index             INT            DEFAULT 0                     NOT NULL,      -- ソート用
    PRIMARY KEY          (qc_id)
) TYPE=innodb;

-- クイズパターンセットIDマスター
DROP TABLE IF EXISTS quiz_set_id;
CREATE TABLE quiz_set_id (
    qs_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    qs_id                VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- 定義項目ID
    qs_history_index     INT            DEFAULT 0                     NOT NULL,      -- 履歴管理用インデックスNo(0～)

    qs_name              VARCHAR(50)    DEFAULT ''                    NOT NULL,      -- 名称
    qs_description       VARCHAR(80)    DEFAULT ''                    NOT NULL,      -- 説明
    qs_index             INT            DEFAULT 0                     NOT NULL,      -- ソート用
    qs_visible           BOOLEAN        DEFAULT true                  NOT NULL,      -- 表示制御
    
    qs_create_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード作成者
    qs_create_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード作成日時
    qs_update_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード更新者
    qs_update_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード更新日時
    qs_deleted           BOOLEAN        DEFAULT false                 NOT NULL,      -- レコード削除状態
    PRIMARY KEY          (qs_serial),
    UNIQUE               (qs_id,  qs_history_index)
) TYPE=innodb;

-- クイズ問題定義マスター
DROP TABLE IF EXISTS quiz_item_def;
CREATE TABLE quiz_item_def (
    qd_serial            INT            AUTO_INCREMENT,                              -- シリアル番号
    qd_set_id            VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- 定義セットID
    qd_id                VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- クイズ項目(問題、回答)ID
    qd_history_index     INT            DEFAULT 0                     NOT NULL,      -- 履歴管理用インデックスNo(0～)

    qd_type              INT            DEFAULT 0                     NOT NULL,      -- 項目タイプ(0=問題、1=回答)
    qd_select_answer_id  TEXT                                         NOT NULL,      -- 選択用回答ID
    qd_answer_id         VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- 正解回答ID
    qd_title             VARCHAR(30)    DEFAULT ''                    NOT NULL,      -- タイトル
    qd_content           VARCHAR(300)   DEFAULT ''                    NOT NULL,      -- 内容
    qd_index             INT            DEFAULT 0                     NOT NULL,      -- ソート用
    qd_visible           BOOLEAN        DEFAULT true                  NOT NULL,      -- 表示制御
    
    qd_create_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード作成者
    qd_create_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード作成日時
    qd_update_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード更新者
    qd_update_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード更新日時
    qd_deleted           BOOLEAN        DEFAULT false                 NOT NULL,      -- レコード削除状態
    PRIMARY KEY          (qd_serial),
    UNIQUE               (qd_set_id,   qd_id,  qd_history_index)
) TYPE=innodb;

-- クイズユーザ回答トラン
DROP TABLE IF EXISTS quiz_user_post;
CREATE TABLE quiz_user_post (
    qp_serial            INT            AUTO_INCREMENT,                              -- シリアル番号
    qp_mobile_id         VARCHAR(30)    DEFAULT ''                    NOT NULL,      -- 携帯端末ID
    qp_set_id            VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- 定義セットID
    qp_question_id       VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- クイズ問題ID
    qp_answer_id         VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- 回答ID
    qp_result            BOOLEAN        DEFAULT false                 NOT NULL,      -- 回答結果
    qp_dt                TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- 記録日時
    qp_access_log_serial INT            DEFAULT 0                     NOT NULL,      -- アクセスログ番号
    PRIMARY KEY          (qp_serial),
    UNIQUE               (qp_mobile_id,   qp_set_id,  qp_question_id)
) TYPE=innodb;

INSERT INTO quiz_set_id (qs_id,         qs_name,              qs_index)
VALUES                  ('default_set', 'デフォルトパターン', 0);
INSERT INTO quiz_config (qc_id,            qc_value,      qc_name,                        qc_index)
VALUES                  ('current_set_id', 'default_set', '現在運用中のパターンセットID', 0);
INSERT INTO quiz_item_def
(qd_set_id,      qd_id, qd_type, qd_select_answer_id, qd_answer_id, qd_title, qd_content,            qd_index) VALUES
('default_set', 'A001', 1,       '',                  '',           '回答1',  '回答1の説明です。',   0),
('default_set', 'A002', 1,       '',                  '',           '回答2',  '回答2の説明です。',   0),
('default_set', 'A003', 1,       '',                  '',           '回答3',  '回答3の説明です。',   0),
('default_set', 'A004', 1,       '',                  '',           '回答4',  '回答4の説明です。',   0),
('default_set', 'A005', 1,       '',                  '',           '回答5',  '回答5の説明です。',   0),
('default_set', 'A006', 1,       '',                  '',           '回答6',  '回答6の説明です。',   0),
('default_set', 'Q001', 0,       'A001;A002;A003',    'A002',       '問題1',  'サンプル問題1です。', 1),
('default_set', 'Q002', 0,       'A006;A004;A005',    'A006',       '問題2',  'サンプル問題2です。', 2);
