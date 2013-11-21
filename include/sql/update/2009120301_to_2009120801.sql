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
-- * @version    SVN: $Id: 2009120301_to_2009120801.sql 2701 2009-12-16 03:15:35Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システム標準テーブル ***
-- システム設定マスター
INSERT INTO _system_config 
(sc_id,                        sc_value,         sc_name) VALUES
('use_connect_server',         '1',              'ポータルサーバ接続');
-- ('server_url',                 '',               'サーバURL');

-- ページIDマスター
INSERT INTO _page_id 
(pg_id,          pg_type, pg_default_sub_id, pg_path,       pg_name,                        pg_description,                       pg_priority, pg_active, pg_mobile, pg_editable) VALUES
('connector',    0,       'content',         'connector',   'サーバ接続用アクセスポイント', 'サーバ接続用アクセスポイント',       3,           true,      false,     true);
-- registは削除

-- ページ情報マスター
INSERT INTO _page_info
(pn_id,       pn_sub_id,   pn_content_type, pn_use_ssl) VALUES
('connector', 'content',   'content',       false);

-- ページ定義マスター
INSERT INTO _page_def
(pd_id,         pd_sub_id,      pd_position_id, pd_index, pd_widget_id,   pd_config_id, pd_visible, pd_editable) VALUES
('connector',   'content',      'main',         1,        'c/updateinfo', 0,            true,       false);

-- ウィジェット情報(新規追加)
DELETE FROM _widgets WHERE wd_id = 'c/updateinfo';
INSERT INTO _widgets
(wd_id,          wd_name,    wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,         wd_available, wd_editable, wd_has_admin, wd_initialized, wd_install_dt, wd_create_dt) VALUES
('c/updateinfo', '新着登録', '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                '新着情報を登録する。', false,        false,       false,        true,           now(),         now());
DELETE FROM _widgets WHERE wd_id = 'portal_updateinfo';
INSERT INTO _widgets
(wd_id,              wd_name,                          wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('portal_updateinfo', 'コンテンツ更新情報(ポータル用)', '1.0.0',    '株式会社 毎日メディアサービス', '株式会社 毎日メディアサービス', 'GPL',      10,                'テナントサーバのコンテンツ更新情報を表示', true,         false,               false,                true, 0,          0, now(),         now());

-- ニュースコンテンツトラン
DROP TABLE IF EXISTS news;
CREATE TABLE news (
    nw_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    nw_type              VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- コンテンツタイプ
    nw_server_id         CHAR(32)       DEFAULT ''                    NOT NULL,      -- サーバ識別ID
    nw_regist_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- 登録日時

    nw_name              TEXT                                         NOT NULL,      -- コンテンツ名
    nw_link              TEXT                                         NOT NULL,      -- コンテンツリンク先
    nw_content_dt        TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- コンテンツ更新日時
    nw_message           TEXT                                         NOT NULL,      -- メッセージ
    nw_site_name         TEXT                                         NOT NULL,      -- サイト名
    nw_site_link         TEXT                                         NOT NULL,      -- サイトリンク

    nw_update_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード更新者
    nw_update_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード更新日時
    nw_deleted           BOOLEAN        DEFAULT false                 NOT NULL,      -- レコード削除状態
    PRIMARY KEY          (nw_serial)
) TYPE=innodb;

-- テナントサーバ情報マスター
DROP TABLE IF EXISTS _tenant_server;
CREATE TABLE _tenant_server (
    ts_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    ts_id                INT            DEFAULT 0                     NOT NULL,      -- サーバID
    ts_history_index     INT            DEFAULT 0                     NOT NULL,      -- 履歴管理用インデックスNo(0～)
    
    ts_server_id         CHAR(32)       DEFAULT ''                    NOT NULL,      -- サーバ識別ID
    ts_name              VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- サーバ名
    ts_url               TEXT                                         NOT NULL,      -- サーバURL
    ts_ip                VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- サーバIP(IPv6対応)
    ts_auth_account      VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- 認証用アカウント
    ts_auth_password     CHAR(32)       DEFAULT ''                    NOT NULL,      -- 認証用パスワード(MD5)
    ts_db_connect_dsn    TEXT                                         NOT NULL,      -- DB接続情報
    ts_db_account        VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- DB接続用アカウント
    ts_db_password       CHAR(32)       DEFAULT ''                    NOT NULL,      -- DB接続用パスワード
    ts_status            SMALLINT       DEFAULT 0                     NOT NULL,      -- サーバ状態(-1=未承認、0=承認済み)
    ts_enable_access     BOOLEAN        DEFAULT true                  NOT NULL,      -- アクセス許可
    ts_active_start_dt   TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- アクセス可能期間(開始)
    ts_active_end_dt     TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- アクセス可能期間(終了)

    ts_create_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード作成者
    ts_create_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード作成日時
    ts_update_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード更新者
    ts_update_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード更新日時
    ts_deleted           BOOLEAN        DEFAULT false                 NOT NULL,      -- レコード削除状態
    PRIMARY KEY          (ts_serial),
    UNIQUE               (ts_id,        ts_history_index)
) TYPE=innodb;
