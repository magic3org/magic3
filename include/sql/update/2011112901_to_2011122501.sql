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
-- * @version    SVN: $Id: 2011112901_to_2011122501.sql 4523 2011-12-26 08:56:10Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------
-- *** システムベーステーブル ***
-- クライアント設定値
DROP TABLE IF EXISTS _client_param;
CREATE TABLE _client_param (
    cp_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    cp_id                CHAR(32)       DEFAULT ''                    NOT NULL,      -- クライアントID
    cp_widget_id         VARCHAR(50)    DEFAULT ''                    NOT NULL,      -- ウィジェットID
    
    cp_param             TEXT                                         NOT NULL,      -- パラメータオブジェクトをシリアライズしたもの
    cp_ip                VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- アクセス元IP(IPv6対応)
    cp_update_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード更新日時
    PRIMARY KEY          (cp_serial),
    UNIQUE               (cp_id,        cp_widget_id)
) TYPE=innodb;

-- コンテンツ参照トラン
ALTER TABLE _view_count MODIFY vc_type_id           VARCHAR(20)     DEFAULT ''                    NOT NULL;      -- コンテンツタイプ(「a:c-h」a=アクセスポイント,c=コンテンツタイプ,h=参照方法)

-- ダウンロード実行ログトラン
DROP TABLE IF EXISTS _download_log;
CREATE TABLE _download_log (
    dl_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    dl_user_id           INT            DEFAULT 0                     NOT NULL,      -- ユーザID
    dl_content_type      VARCHAR(10)    DEFAULT ''                    NOT NULL,      -- コンテンツ種別
    dl_content_id        VARCHAR(32)    DEFAULT ''                    NOT NULL,      -- コンテンツID
    dl_access_log_serial INT            DEFAULT 0                     NOT NULL,      -- アクセスログシリアル番号
    dl_dt                TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- 記録日時
    PRIMARY KEY          (dl_serial)
) TYPE=innodb;

-- インナーウィジェット
ALTER TABLE _iwidgets MODIFY iw_type         VARCHAR(20)    DEFAULT ''                    NOT NULL;      -- ウィジェット種別
ALTER TABLE _iwidgets ADD iw_license_type    INT            DEFAULT 0                     NOT NULL;      -- ライセンスタイプ(0=オープンソース、1=商用)
ALTER TABLE _iwidgets ADD iw_online          BOOLEAN        DEFAULT false                 NOT NULL;      -- オンライン接続があるかどうか

-- インナーウィジェットメソッド定義マスター
DROP TABLE IF EXISTS _iwidget_method;
CREATE TABLE _iwidget_method (
    id_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    id_type              VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- メソッド種別
    id_id                INT            DEFAULT 0                     NOT NULL,      -- メソッドID
    id_language_id       VARCHAR(2)     DEFAULT ''                    NOT NULL,      -- 言語ID
    id_set_id            INT            DEFAULT 0                     NOT NULL,      -- セットID(0=デフォルトセット)
    id_history_index     INT            DEFAULT 0                     NOT NULL,      -- 履歴管理用インデックスNo(0～)
    
    id_name              VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- 名称
    id_desc_short        TEXT                                         NOT NULL,      -- 簡易説明(テキストのみ)
    id_desc              TEXT                                         NOT NULL,      -- 説明(HTML)
    id_iwidget_id        VARCHAR(50)    DEFAULT ''                    NOT NULL,      -- インナーウィジェットID
    id_param             TEXT                                         NOT NULL,      -- 設定インナーウィジェット用パラメータ
    id_index             INT            DEFAULT 0                     NOT NULL,      -- 表示順(1～)
    id_visible           BOOLEAN        DEFAULT true                  NOT NULL,      -- 項目を表示するかどうか
    id_active_start_dt   TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- 有効期限開始日時
    id_active_end_dt     TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- 有効期限終了日時
    
    id_create_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード作成者
    id_create_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード作成日時
    id_update_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード更新者
    id_update_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード更新日時
    id_deleted           BOOLEAN        DEFAULT false                 NOT NULL,      -- レコード削除状態
    PRIMARY KEY          (id_serial),
    UNIQUE               (id_type,      id_id,        id_language_id, id_set_id,    id_history_index)
) TYPE=innodb;

-- 番号管理マスター
DROP TABLE IF EXISTS _used_no;
CREATE TABLE _used_no (
    un_id                VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- ID(Key)
    un_value             VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- 値
    PRIMARY KEY  (un_id)
) TYPE=innodb;

-- コンテンツアクセス権マスター
DROP TABLE IF EXISTS _content_access;
CREATE TABLE _content_access (
    cs_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    cs_user_id           INT            DEFAULT 0                     NOT NULL,      -- ユーザID(全ユーザ対象のときは0)
    cs_content_type      VARCHAR(10)    DEFAULT ''                    NOT NULL,      -- コンテンツ種別
    cs_content_id        VARCHAR(32)    DEFAULT ''                    NOT NULL,      -- コンテンツID
    
    cs_read              BOOLEAN        DEFAULT false                 NOT NULL,      -- 読み込み権限
    cs_write             BOOLEAN        DEFAULT false                 NOT NULL,      -- 書き込み権限
    cs_download          BOOLEAN        DEFAULT false                 NOT NULL,      -- ダウンロード権限
    cs_active_start_dt   TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- 有効期限開始日時
    cs_active_end_dt     TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- 有効期限終了日時
    
    cs_update_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード更新者
    cs_update_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード更新日時
    PRIMARY KEY          (cs_serial),
    UNIQUE               (cs_user_id,   cs_content_type,    cs_content_id)
) TYPE=innodb;

-- *** システム標準テーブル ***
-- 写真カテゴリマスター
ALTER TABLE photo_category ADD hc_password          CHAR(32)       DEFAULT ''                    NOT NULL;      -- アクセス制限パスワード(MD5)

-- フォトギャラリー設定マスター(フォトギャラリーメイン)
INSERT INTO photo_config
(hg_id,               hg_value,           hg_name,                                  hg_index) VALUES
('photo_category_password', '0',                '画像カテゴリーのパスワード制限',             12);

-- 商品注文書トラン
ALTER TABLE order_sheet ADD oe_discount_desc       TEXT                                         NOT NULL;      -- 値引き説明
ALTER TABLE order_sheet ADD oe_status              INT            DEFAULT 0                     NOT NULL;      -- 注文書状態(0=通常、1=オンライン処理中)
ALTER TABLE order_sheet ADD oe_session             CHAR(32)       DEFAULT ''                    NOT NULL;      -- セッションID

-- 商品受注トラン
ALTER TABLE order_header ADD or_discount_desc       TEXT                                         NOT NULL;      -- 値引き説明
ALTER TABLE order_header ADD or_pay_dt              TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL;      -- 支払い日時

-- 商品クラスマスター
DROP TABLE IF EXISTS product_class;
CREATE TABLE product_class (
    pu_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    pu_id                VARCHAR(10)    DEFAULT ''                    NOT NULL,      -- 商品クラスID
    pu_language_id       VARCHAR(2)     DEFAULT ''                    NOT NULL,      -- 言語ID
    pu_history_index     INT            DEFAULT 0                     NOT NULL,      -- 履歴管理用インデックスNo(0～)
    
    pu_name              VARCHAR(80)    DEFAULT ''                    NOT NULL,      -- 名称
    pu_description       VARCHAR(200)   DEFAULT ''                    NOT NULL,      -- 説明
    pu_index             INT            DEFAULT 0                     NOT NULL,      -- 表示順(1～)

    pu_create_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード作成者
    pu_create_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード作成日時
    pu_update_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード更新者
    pu_update_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード更新日時
    pu_deleted           BOOLEAN        DEFAULT false                 NOT NULL,      -- レコード削除状態
    PRIMARY KEY          (pu_serial),
    UNIQUE               (pu_id,        pu_language_id,               pu_history_index)
) TYPE=innodb;
