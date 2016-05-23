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
-- * @version    SVN: $Id: 2013022601_to_2013031701.sql 5855 2013-03-24 10:26:56Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
-- 自動ログインマスター
DROP TABLE IF EXISTS _auto_login;
CREATE TABLE _auto_login (
    ag_id                CHAR(32)       DEFAULT ''                    NOT NULL,      -- 自動ログインキー
    ag_user_id           INT            DEFAULT 0                     NOT NULL,      -- ログインユーザID
    ag_client_id         VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- PCの場合はアクセス管理用クッキー値。携帯の場合は端末ID「XX-xxxxxx」(XX=キャリアDC,AU,SB、xxxxxx=端末ID)。
    ag_index             INT            DEFAULT 0                     NOT NULL,      -- インデックス番号(0～)

    ag_path              VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- アクセスポイントパス
    ag_expire_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- 有効期限
    ag_access_log_serial INT            DEFAULT 0                     NOT NULL,      -- 登録時アクセスログシリアル番号

    ag_create_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード作成者
    ag_create_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード作成日時
    ag_update_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード更新者
    ag_update_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード更新日時
    ag_deleted           BOOLEAN        DEFAULT false                 NOT NULL,      -- レコード削除状態
    PRIMARY KEY          (ag_id),
    UNIQUE               (ag_user_id,   ag_client_id, ag_index)
) TYPE=innodb;

-- オプションコンテンツパラメータマスター
DROP TABLE IF EXISTS _option_content_param;
CREATE TABLE _option_content_param (
    oc_page_id           VARCHAR(50)    DEFAULT ''                    NOT NULL,      -- ページID
    oc_id                VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- URLパラメータ

    oc_name              VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- コンテンツ名称
    oc_widget_id         VARCHAR(50)    DEFAULT ''                    NOT NULL,      -- 実行ウィジェットID(ファイル名)
    oc_sort_order        INT            DEFAULT 0                     NOT NULL,      -- ソート順
    PRIMARY KEY          (oc_page_id,   oc_id)
) TYPE=innodb;

-- ログインユーザマスター
ALTER TABLE _login_user ADD lu_default_admin_url            TEXT                                         NOT NULL;      -- デフォルトの管理画面のURL(「?」以降)

-- ウィジェット情報マスター
ALTER TABLE _widgets ADD wd_content_name         TEXT                                         NOT NULL;      -- コンテンツ名称(メニュー表示用)

-- システム設定マスター
-- システムの動作に影響する設定を管理する
INSERT INTO _system_config 
(sc_id,               sc_value,                  sc_name) VALUES
('auto_login',        '1',                        'フロント画面自動ログイン機能'),
('auto_login_admin',        '0',                        '管理画面自動ログイン機能');

-- *** システム標準テーブル ***

