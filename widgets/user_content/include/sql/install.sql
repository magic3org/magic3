-- ユーザコンテンツ表示タブマスター
DROP TABLE IF EXISTS user_content_tab;
CREATE TABLE user_content_tab (
    ub_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    ub_id                VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- タブID
    ub_language_id       VARCHAR(2)     DEFAULT ''                    NOT NULL,      -- 言語ID
    ub_history_index     INT            DEFAULT 0                     NOT NULL,      -- 履歴管理用インデックスNo(0～)
    
    ub_name              VARCHAR(60)    DEFAULT ''                    NOT NULL,      -- 名称
    ub_description       VARCHAR(80)    DEFAULT ''                    NOT NULL,      -- 説明
    ub_template_html     TEXT                                         NOT NULL,      -- テンプレートHTML
    ub_use_item_id       TEXT                                         NOT NULL,      -- 使用しているコンテンツ項目ID(カンマ区切り)
    ub_index             INT            DEFAULT 0                     NOT NULL,      -- ソート用
    ub_visible           BOOLEAN        DEFAULT true                  NOT NULL,      -- 表示可否
    ub_user_limited      BOOLEAN        DEFAULT false                 NOT NULL,      -- 参照ユーザを制限
    
    ub_create_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード作成者
    ub_create_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード作成日時
    ub_update_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード更新者
    ub_update_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード更新日時
    ub_deleted           BOOLEAN        DEFAULT false                 NOT NULL,      -- レコード削除状態
    PRIMARY KEY          (ub_serial),
    UNIQUE               (ub_id,        ub_language_id,               ub_history_index)
) TYPE=innodb;

-- ユーザ作成コンテンツ項目マスター
DROP TABLE IF EXISTS user_content_item;
CREATE TABLE user_content_item (
    ui_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    ui_id                VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- 項目ID
    ui_history_index     INT            DEFAULT 0                     NOT NULL,      -- 履歴管理用インデックスNo(0～)
    
    ui_name              VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- 名前
    ui_type              INT            DEFAULT 0                     NOT NULL,      -- コンテンツタイプ(0=HTML,1=文字列,2=数値)
    
    ui_create_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード作成者
    ui_create_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード作成日時
    ui_update_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード更新者
    ui_update_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード更新日時
    ui_deleted           BOOLEAN        DEFAULT false                 NOT NULL,      -- レコード削除状態
    PRIMARY KEY          (ui_serial),
    UNIQUE               (ui_id,        ui_history_index)
) TYPE=innodb;

-- ユーザ作成コンテンツマスター
DROP TABLE IF EXISTS user_content;
CREATE TABLE user_content (
    uc_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    uc_id                VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- コンテンツID
    uc_room_id           VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- コンテンツ所属ID
    uc_language_id       VARCHAR(2)     DEFAULT ''                    NOT NULL,      -- 言語ID
    uc_history_index     INT            DEFAULT 0                     NOT NULL,      -- 履歴管理用インデックスNo(0～)
    
    uc_name              VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- コンテンツ名
    uc_description       VARCHAR(100)   DEFAULT ''                    NOT NULL,      -- 説明
    uc_data              TEXT                                         NOT NULL,      -- コンテンツデータ
    uc_data_search_num   DECIMAL(15,4)  DEFAULT 0                     NOT NULL,      -- コンテンツ検索用データ(数値)
    uc_visible           BOOLEAN        DEFAULT true                  NOT NULL,      -- 表示可否
    uc_active_start_dt   TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- 表示可能期間(開始)
    uc_active_end_dt     TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- 表示可能期間(終了)
    uc_user_limited      BOOLEAN        DEFAULT false                 NOT NULL,      -- 参照ユーザを制限
    
    uc_check_out_user_id INT            DEFAULT 0                     NOT NULL,      -- チェックアウトユーザID(0のときはチェックイン状態)
    uc_check_out_dt      TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- チェックアウト日時
    uc_create_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード作成者
    uc_create_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード作成日時
    uc_update_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード更新者
    uc_update_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード更新日時
    uc_locked            BOOLEAN        DEFAULT false                 NOT NULL,      -- レコードロック状態
    uc_deleted           BOOLEAN        DEFAULT false                 NOT NULL,      -- レコード削除状態
    PRIMARY KEY          (uc_serial),
    UNIQUE               (uc_id,        uc_room_id,  uc_language_id,  uc_history_index)
) TYPE=innodb;

-- ウィジェット情報
DELETE FROM _widgets WHERE wd_id = 'user_content';
INSERT INTO _widgets
(wd_id,          wd_name,                wd_type, wd_version, wd_author,                       wd_copyright,                    wd_license, wd_official_level, wd_description,         wd_add_script_lib, wd_add_script_lib_a, wd_has_admin, wd_initialized, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('user_content', 'ユーザ作成コンテンツ', 'user',  '0.5.0',    '株式会社 毎日メディアサービス', '株式会社 毎日メディアサービス', 'GPL',      10,                'ユーザが管理可能なコンテンツを表示', 'jquery-ui,jquery-ui-plus', '', true, true,           2, 2, now(), now());
