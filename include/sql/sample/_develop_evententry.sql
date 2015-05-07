-- *
-- * データ登録スクリプト「開発ウィジェット登録」
-- *
-- * PHP versions 5
-- *
-- * LICENSE: This source file is licensed under the terms of the GNU General Public License.
-- *
-- * @package    Magic3 Framework
-- * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
-- * @copyright  Copyright 2006-2015 Magic3 Project.
-- * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
-- * @version    SVN: $Id$
-- * @link       http://www.magic3.org
-- *
-- [開発ウィジェット登録]   *****仕様変更あり注意*****
-- 開発中のウィジェットの登録を行う。
-- ・イベント予約機能

-- ウィジェット情報
DELETE FROM _widgets WHERE wd_id = 'evententry_main';
INSERT INTO _widgets
(wd_id,             wd_name,               wd_type, wd_content_type, wd_category_id, wd_edit_content, wd_content_widget_id, wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_add_script_lib, wd_add_script_lib_a, wd_available, wd_has_admin, wd_use_instance_def, wd_initialized, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('evententry_main', 'イベント予約-メイン', '', 'evententry',    'event',   true,            '',                   '0.0.1',   'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'イベントの予約管理を行う。', '',                '',                false,  true,         false,                true,  '2015-04-29', now(),         now());
DELETE FROM _widgets WHERE wd_id = 'evententry_attachment';
INSERT INTO _widgets
(wd_id,                   wd_name,                       wd_content_type, wd_category_id, wd_edit_content, wd_content_widget_id, wd_template_type, wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,                   wd_add_script_lib, wd_add_script_lib_a,                         wd_has_admin, wd_use_instance_def, wd_initialized, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('evententry_attachment', 'イベント予約-アタッチメント', 'evententry',    'event',        true,            'evententry_main',                   'bootstrap',      '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'イベント情報に予約機能を付加。', '',                '',                  true,  false,              true, '2015-05-07', now(),         now());

-- イベント予約設定マスター
DROP TABLE IF EXISTS evententry_config;
CREATE TABLE evententry_config (
    ef_id                VARCHAR(30)    DEFAULT ''                    NOT NULL,      -- ID(Key)
    ef_value             TEXT                                         NOT NULL,      -- 値
    ef_name              VARCHAR(50)    DEFAULT ''                    NOT NULL,      -- 名称
    ef_description       VARCHAR(80)    DEFAULT ''                    NOT NULL,      -- 説明
    ef_index             INT            DEFAULT 0                     NOT NULL,      -- ソート用
    PRIMARY KEY          (ef_id)
) ENGINE=innodb;

INSERT INTO evententry_config
(ef_id,                  ef_value,    ef_name) VALUES
('show_entry_count',     '0',         '参加者数を表示するかどうか'),
('show_entry_member',    '0',         '参加者を表示するかどうか(会員対象)');


-- イベント予約マスター
DROP TABLE IF EXISTS evententry;
CREATE TABLE evententry (
    et_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    et_id                INT            DEFAULT 0                     NOT NULL,      -- イベント予約ID
    et_history_index     INT            DEFAULT 0                     NOT NULL,      -- 履歴管理用インデックスNo(0～)
    
    et_content_type      VARCHAR(10)    DEFAULT ''                    NOT NULL,      -- コンテンツ種別
    et_contents_id       VARCHAR(32)    DEFAULT ''                    NOT NULL,      -- 共通コンテンツID
    et_type              VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- 受付タイプ
    et_code              VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- イベント予約受付コード
    et_html              TEXT                                         NOT NULL,      -- 説明
    et_status            SMALLINT       DEFAULT 0                     NOT NULL,      -- 状態(0=未設定、1=非公開、2=受付中、3=受付停止、4=受付終了)
    et_show_entry_count  BOOLEAN        DEFAULT true                  NOT NULL,      -- 参加者数を表示するかどうか
    et_show_entry_member BOOLEAN        DEFAULT true                  NOT NULL,      -- 参加者を表示するかどうか(会員対象)
    et_max_entry         INT            DEFAULT 0                     NOT NULL,      -- 定員(0は定員なし)
    et_start_dt          TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- 受付期間(開始)
    et_end_dt            TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- 受付期間(終了)
    
    et_create_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード作成者
    et_create_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード作成日時
    et_update_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード更新者
    et_update_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード更新日時
    et_deleted           BOOLEAN        DEFAULT false                 NOT NULL,      -- レコード削除状態
    PRIMARY KEY          (et_serial),
    UNIQUE               (et_id,        et_history_index)
) ENGINE=innodb;

-- イベント予約要求トラン
DROP TABLE IF EXISTS evententry_request;
CREATE TABLE evententry_request (
    er_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    er_evententry_id     INT            DEFAULT 0                     NOT NULL,      -- イベント予約ID
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
    UNIQUE               (er_evententry_id,   er_index)
) ENGINE=innodb;

