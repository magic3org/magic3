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
-- * @version    SVN: $Id: 2013042801_to_2013061301.sql 6102 2013-06-13 05:48:19Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
-- システム設定マスター
UPDATE _system_config SET sc_value = '80c.jpg' WHERE sc_id = 'avatar_format';

-- *** システム標準テーブル ***
-- 汎用コメント設定マスター
DROP TABLE IF EXISTS comment_config;
CREATE TABLE comment_config (
    cf_content_type      VARCHAR(10)    DEFAULT ''                    NOT NULL,      -- コンテンツ種別
    cf_contents_id       VARCHAR(32)    DEFAULT ''                    NOT NULL,      -- コンテンツID(空の場合は共通)
    
    cf_view_type         INT            DEFAULT 0                     NOT NULL,      -- コメントタイプ(0=フラット,1=ツリー)
    cf_view_direction    INT            DEFAULT 0                     NOT NULL,      -- 表示方向(0=昇順、1=降順)
    cf_max_count         INT            DEFAULT 0                     NOT NULL,      -- コメント最大数
    cf_max_length        INT            DEFAULT 0                     NOT NULL,      -- コメント文字数
    cf_image_max_size    INT            DEFAULT 0                     NOT NULL,      -- 画像の最大サイズ(縦横)
    cf_visible           BOOLEAN        DEFAULT true                  NOT NULL,      -- 表示可否(個別設定可)
    cf_visible_d         BOOLEAN        DEFAULT true                  NOT NULL,      -- 表示可否デフォルト値
    cf_accept_post       BOOLEAN        DEFAULT true                  NOT NULL,      -- コメントの受付(個別設定可)
    cf_accept_post_d     BOOLEAN        DEFAULT true                  NOT NULL,      -- コメントの受付デフォルト値
    cf_start_dt          TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- 使用期間(開始)(個別設定可)
    cf_end_dt            TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- 使用期間(終了)(個別設定可)
    cf_user_limited      BOOLEAN        DEFAULT false                 NOT NULL,      -- 投稿ユーザを制限
    cf_need_authorize    BOOLEAN        DEFAULT false                 NOT NULL,      -- 認証が必要かどうか
    cf_permit_html       BOOLEAN        DEFAULT false                 NOT NULL,      -- HTMLメッセージ
    cf_permit_image      BOOLEAN        DEFAULT false                 NOT NULL,      -- 画像あり
    cf_autolink          BOOLEAN        DEFAULT false                 NOT NULL,      -- 自動リンク
    cf_use_title         BOOLEAN        DEFAULT true                  NOT NULL,      -- タイトルあり
    cf_use_author        BOOLEAN        DEFAULT true                  NOT NULL,      -- 投稿者名あり
    cf_use_email         BOOLEAN        DEFAULT true                  NOT NULL,      -- Eメールあり
    cf_use_url           BOOLEAN        DEFAULT true                  NOT NULL,      -- URLあり
    cf_use_avatar        BOOLEAN        DEFAULT true                  NOT NULL,      -- アバターあり
    cf_use_date          BOOLEAN        DEFAULT true                  NOT NULL,      -- 日付あり
    PRIMARY KEY          (cf_content_type,   cf_contents_id)
) TYPE=innodb;

-- 汎用コメントトラン
DROP TABLE IF EXISTS comment;
CREATE TABLE comment (
    cm_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    cm_content_type      VARCHAR(10)    DEFAULT ''                    NOT NULL,      -- コンテンツ種別
    cm_contents_id       VARCHAR(32)    DEFAULT ''                    NOT NULL,      -- 共通コンテンツID
    cm_device_type       INT            DEFAULT 0                     NOT NULL,      -- 端末タイプ(0=PC、1=携帯、2=スマートフォン)
    cm_language_id       VARCHAR(2)     DEFAULT ''                    NOT NULL,      -- 言語ID
    cm_parent_serial     INT            DEFAULT 0                     NOT NULL,      -- 親コメントのシリアル番号
    
    cm_no                INT            DEFAULT 0                     NOT NULL,      -- コメント番号(投稿順)
    cm_sort_order        INT            DEFAULT 0                     NOT NULL,      -- 表示順
    cm_nest_level        INT            DEFAULT 0                     NOT NULL,      -- ツリータイプの場合のネスト段階
    cm_title             VARCHAR(50)    DEFAULT ''                    NOT NULL,      -- タイトル
    cm_message           TEXT                                         NOT NULL,      -- メッセージ
    cm_url               TEXT                                         NOT NULL,      -- 参照用URL
    cm_author            VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- 投稿者名
    cm_email             VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- Eメールアドレス
    cm_status            SMALLINT       DEFAULT 0                     NOT NULL,      -- 状態(0=未設定、1=非公開、2=公開)
    
    cm_create_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード作成者
    cm_create_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード作成日時
    cm_update_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード更新者
    cm_update_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード更新日時
    cm_deleted           BOOLEAN        DEFAULT false                 NOT NULL,      -- レコード削除状態
    PRIMARY KEY          (cm_serial)
) TYPE=innodb;

