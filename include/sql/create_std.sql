-- *
-- * 標準テーブル作成スクリプト
-- *
-- * PHP versions 5
-- *
-- * LICENSE: This source file is licensed under the terms of the GNU General Public License.
-- *
-- * @package    Magic3 Framework
-- * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
-- * @copyright  Copyright 2006-2020 Magic3 Project.
-- * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
-- * @version    SVN: $Id$
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- 標準テーブル作成スクリプト
-- システムの標準構成で必要なテーブルの作成を行う
-- --------------------------------------------------------------------------------------------------

-- 国マスター
DROP TABLE IF EXISTS country;
CREATE TABLE country (
    ct_id                VARCHAR(3)     DEFAULT ''                    NOT NULL,      -- 国ID(ISO 3文字コード)
    ct_language_id       VARCHAR(2)     DEFAULT ''                    NOT NULL,      -- 言語ID
    ct_name              VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- 国名称
    ct_name_short        VARCHAR(15)    DEFAULT ''                    NOT NULL,      -- 国名称略称
    ct_iso_code_2        VARCHAR(2)     DEFAULT ''                    NOT NULL,      -- ISO 2文字コード
    ct_index             INT            DEFAULT 0                     NOT NULL,      -- ソート用
    PRIMARY KEY          (ct_id,        ct_language_id)
) ENGINE=innodb;

-- 通貨マスター
DROP TABLE IF EXISTS currency;
CREATE TABLE currency (
    cu_id                VARCHAR(3)     DEFAULT ''                    NOT NULL,      -- 通貨ID
    cu_language_id       VARCHAR(2)     DEFAULT ''                    NOT NULL,      -- 言語ID
    cu_name              VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- 名称
    cu_description       VARCHAR(80)    DEFAULT ''                    NOT NULL,      -- 説明
    cu_symbol            VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- 表示記号
    cu_post_symbol       VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- 後表示記号
    cu_decimal_place     INT            DEFAULT 0                     NOT NULL,      -- 小数以下桁数
    cu_index             INT            DEFAULT 0                     NOT NULL,      -- ソート用
    PRIMARY KEY          (cu_id,        cu_language_id)
) ENGINE=innodb;

-- 汎用コンテンツ設定マスター
DROP TABLE IF EXISTS content_config;
CREATE TABLE content_config (
    ng_type              VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- コンテンツタイプ
    ng_id                VARCHAR(30)    DEFAULT ''                    NOT NULL,      -- ID(key)
    ng_value             TEXT                                         NOT NULL,      -- 値
    ng_name              VARCHAR(50)    DEFAULT ''                    NOT NULL,      -- 名称
    ng_description       VARCHAR(80)    DEFAULT ''                    NOT NULL,      -- 説明
    ng_index             INT            DEFAULT 0                     NOT NULL,      -- ソート用
    PRIMARY KEY          (ng_type,      ng_id)
) ENGINE=innodb;

-- 汎用コンテンツマスター
DROP TABLE IF EXISTS content;
CREATE TABLE content (
    cn_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    cn_type              VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- コンテンツタイプ
    cn_id                INT            DEFAULT 0                     NOT NULL,      -- コンテンツID
    cn_language_id       VARCHAR(5)     DEFAULT ''                    NOT NULL,      -- 言語ID
    cn_history_index     INT            DEFAULT 0                     NOT NULL,      -- 履歴管理用インデックスNo(0～)
    
    cn_name              VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- コンテンツ名
    cn_description       VARCHAR(100)   DEFAULT ''                    NOT NULL,      -- 説明
    cn_html              TEXT                                         NOT NULL,      -- コンテンツHTML
    cn_meta_title        TEXT                                         NOT NULL,      -- METAタグ、タイトル
    cn_meta_description  TEXT                                         NOT NULL,      -- METAタグ、ページ要約
    cn_meta_keywords     TEXT                                         NOT NULL,      -- METAタグ、検索用キーワード
    cn_head_others       TEXT                                         NOT NULL,      -- HEADタグその他
    cn_disp_type         SMALLINT       DEFAULT 0                     NOT NULL,      -- 表示タイプ(0=プレーン、1=インナーフレーム)
    cn_visible           BOOLEAN        DEFAULT true                  NOT NULL,      -- 表示可否
    cn_regist_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- 登録日時
    cn_active_start_dt   TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- 表示可能期間(開始)
    cn_active_end_dt     TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- 表示可能期間(終了)
    cn_default           BOOLEAN        DEFAULT false                 NOT NULL,      -- デフォルトフラグ(廃止予定)
    cn_key               VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- 外部からの参照用キー
    cn_user_limited      BOOLEAN        DEFAULT false                 NOT NULL,      -- 参照ユーザを制限
    cn_search_target     BOOLEAN        DEFAULT true                  NOT NULL,      -- 検索対象かどうか
    cn_password          CHAR(32)       DEFAULT ''                    NOT NULL,      -- アクセス制限パスワード(MD5)
    cn_search_content    TEXT                                         NOT NULL,      -- 検索用コンテンツ
    cn_thumb_filename    TEXT                                         NOT NULL,      -- サムネールファイル名(「;」区切り)
    cn_thumb_src         TEXT                                         NOT NULL,      -- サムネールの元のファイル(リソースディレクトリからの相対パス)
    cn_template_id       VARCHAR(50)    DEFAULT ''                    NOT NULL,      -- テンプレートID
    cn_sub_template_id   VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- サブテンプレートID
    cn_option_fields     TEXT                                         NOT NULL,      -- 追加フィールド
    cn_related_content   TEXT                                         NOT NULL,      -- 関連コンテンツID(「,」区切り)
    cn_related_url       TEXT                                         NOT NULL,      -- 関連URL(「;」区切り)
    cn_attach_access_key VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- 添付ファイルアクセスキー
    cn_attach_access_url TEXT                                         NOT NULL,      -- 添付ファイルアクセスキー取得用URL
    cn_script_lib        TEXT                                         NOT NULL,      -- 共通スクリプトライブラリ(ライブラリ名で指定、「,」区切りで複数指定可)
    cn_script            TEXT                                         NOT NULL,      -- Javascriptスクリプト
    
    cn_check_out_user_id INT            DEFAULT 0                     NOT NULL,      -- チェックアウトユーザID(0のときはチェックイン状態)
    cn_check_out_dt      TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- チェックアウト日時
    cn_create_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード作成者
    cn_create_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード作成日時
    cn_update_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード更新者
    cn_update_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード更新日時
    cn_locked            BOOLEAN        DEFAULT false                 NOT NULL,      -- レコードロック状態
    cn_deleted           BOOLEAN        DEFAULT false                 NOT NULL,      -- レコード削除状態
    PRIMARY KEY          (cn_serial),
    UNIQUE               (cn_type,      cn_id,        cn_language_id,               cn_history_index)
) ENGINE=innodb;

-- 新着情報設定マスター
DROP TABLE IF EXISTS news_config;
CREATE TABLE news_config (
    nc_id                VARCHAR(30)    DEFAULT ''                    NOT NULL,      -- ID(Key)
    nc_value             TEXT                                         NOT NULL,      -- 値
    nc_name              VARCHAR(50)    DEFAULT ''                    NOT NULL,      -- 名称
    nc_description       VARCHAR(80)    DEFAULT ''                    NOT NULL,      -- 説明
    nc_index             INT            DEFAULT 0                     NOT NULL,      -- ソート用
    PRIMARY KEY          (nc_id)
) ENGINE=innodb;

-- 新着情報トラン
DROP TABLE IF EXISTS news;
CREATE TABLE news (
    nw_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    nw_id                INT            DEFAULT 0                     NOT NULL,      -- ID
    nw_history_index     INT            DEFAULT 0                     NOT NULL,      -- 履歴管理用インデックスNo(0～)

    nw_type              VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- メッセージタイプ
    nw_server_id         CHAR(32)       DEFAULT ''                    NOT NULL,      -- サーバ識別ID
    nw_device_type       INT            DEFAULT 0                     NOT NULL,      -- 端末タイプ(0=PC、1=携帯、2=スマートフォン)
    nw_regist_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- 登録日時
    nw_name              TEXT                                         NOT NULL,      -- コンテンツ名
    nw_content_type      VARCHAR(10)    DEFAULT ''                    NOT NULL,      -- コンテンツの種別
    nw_content_id        TEXT                                         NOT NULL,      -- コンテンツID
    nw_url               TEXT                                         NOT NULL,      -- リンク先
    nw_link              TEXT                                         NOT NULL,      -- コンテンツリンク先(廃止予定)
    nw_content_dt        TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- コンテンツ更新日時
    nw_message           TEXT                                         NOT NULL,      -- メッセージ
    nw_site_name         TEXT                                         NOT NULL,      -- サイト名
    nw_site_link         TEXT                                         NOT NULL,      -- サイトリンク(廃止予定)
    nw_site_url          TEXT                                         NOT NULL,      -- サイトリンク
    nw_summary           VARCHAR(100)   DEFAULT ''                    NOT NULL,      -- 概要
    nw_mark              INT            DEFAULT 0                     NOT NULL,      -- 付加マーク(0=なし、1=新規)
    nw_visible           BOOLEAN        DEFAULT false                 NOT NULL,      -- 表示するかどうか
    nw_user_limited      BOOLEAN        DEFAULT false                 NOT NULL,      -- アクセス可能ユーザを制限

    nw_create_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード作成者
    nw_create_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード作成日時
    nw_update_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード更新者
    nw_update_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード更新日時
    nw_deleted           BOOLEAN        DEFAULT false                 NOT NULL,      -- レコード削除状態
    PRIMARY KEY          (nw_serial),
    UNIQUE               (nw_id,        nw_history_index)
) ENGINE=innodb;

-- Wiki設定マスター
DROP TABLE IF EXISTS wiki_config;
CREATE TABLE wiki_config (
    wg_id                VARCHAR(30)    DEFAULT ''                    NOT NULL,      -- ID(Key)
    wg_value             TEXT                                         NOT NULL,      -- 値
    wg_name              VARCHAR(50)    DEFAULT ''                    NOT NULL,      -- 名称
    wg_description       VARCHAR(80)    DEFAULT ''                    NOT NULL,      -- 説明
    wg_index             INT            DEFAULT 0                     NOT NULL,      -- ソート用
    PRIMARY KEY          (wg_id)
) ENGINE=innodb;

-- Wikiコンテンツマスター
DROP TABLE IF EXISTS wiki_content;
CREATE TABLE wiki_content (
    wc_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    wc_type              VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- コンテンツタイプ
    wc_id                VARCHAR(191)   DEFAULT ''                    NOT NULL,      -- コンテンツID
    wc_history_index     INT            DEFAULT 0                     NOT NULL,      -- 履歴管理用インデックスNo(0～)
    
    wc_data              TEXT                                         NOT NULL,      -- コンテンツ内容
    wc_content_dt        TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- コンテンツ更新日時
    wc_visible           BOOLEAN        DEFAULT true                  NOT NULL,      -- 表示可否
    wc_fore_serial       INT            DEFAULT 0                     NOT NULL,      -- 前レコードシリアル番号
    wc_next_serial       INT            DEFAULT 0                     NOT NULL,      -- 次レコードシリアル番号
    
    wc_check_out_user_id INT            DEFAULT 0                     NOT NULL,      -- チェックアウトユーザID(0のときはチェックイン状態)
    wc_check_out_dt      TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- チェックアウト日時
    wc_create_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード作成者
    wc_create_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード作成日時
    wc_update_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード更新者
    wc_update_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード更新日時
    wc_locked            BOOLEAN        DEFAULT false                 NOT NULL,      -- レコードロック状態
    wc_deleted           BOOLEAN        DEFAULT false                 NOT NULL,      -- レコード削除状態
    PRIMARY KEY          (wc_serial),
    UNIQUE               (wc_type,      wc_id,  wc_history_index)
) ENGINE=innodb;

-- ブログ設定マスター
DROP TABLE IF EXISTS blog_config;
CREATE TABLE blog_config (
    bg_blog_id           VARCHAR(30)    DEFAULT ''                    NOT NULL,      -- ブログID(空文字列=デフォルト)
    bg_id                VARCHAR(30)    DEFAULT ''                    NOT NULL,      -- ID(Key)
    bg_value             TEXT                                         NOT NULL,      -- 値
    bg_name              VARCHAR(50)    DEFAULT ''                    NOT NULL,      -- 名称
    bg_description       VARCHAR(80)    DEFAULT ''                    NOT NULL,      -- 説明
    bg_index             INT            DEFAULT 0                     NOT NULL,      -- ソート用
    PRIMARY KEY          (bg_blog_id,   bg_id)
) ENGINE=innodb;

-- ブログIDマスター
DROP TABLE IF EXISTS blog_id;
CREATE TABLE blog_id (
    bl_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    bl_id                VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- ブログID
    bl_history_index     INT            DEFAULT 0                     NOT NULL,      -- 履歴管理用インデックスNo(0～)
    
    bl_name              VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- 名前
    bl_template_id       VARCHAR(50)    DEFAULT ''                    NOT NULL,      -- テンプレートID
    bl_sub_template_id   VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- サブテンプレートID
    bl_meta_title        TEXT                                         NOT NULL,      -- METAタグ、タイトル
    bl_meta_description  TEXT                                         NOT NULL,      -- METAタグ、ページ要約
    bl_meta_keywords     TEXT                                         NOT NULL,      -- METAタグ、検索用キーワード
    bl_index             INT            DEFAULT 0                     NOT NULL,      -- ソート用
    bl_owner_id          INT            DEFAULT 0                     NOT NULL,      -- ブログの所有者ID
    bl_group_id          INT            DEFAULT 0                     NOT NULL,      -- 所属グループID
    bl_user_limited      BOOLEAN        DEFAULT false                 NOT NULL,      -- 参照ユーザを制限
    bl_limited_user_id   TEXT                                         NOT NULL,      -- 参照可能ユーザ(,区切り)
    bl_visible           BOOLEAN        DEFAULT true                  NOT NULL,      -- 公開可否
    bl_active_start_dt   TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- 公開期間(開始)
    bl_active_end_dt     TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- 公開期間(終了)
    bl_content_update_dt TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- コンテンツ更新日時
    
    bl_create_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード作成者
    bl_create_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード作成日時
    bl_update_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード更新者
    bl_update_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード更新日時
    bl_deleted           BOOLEAN        DEFAULT false                 NOT NULL,      -- レコード削除状態
    PRIMARY KEY          (bl_serial),
    UNIQUE               (bl_id,        bl_history_index)
) ENGINE=innodb;

-- ブログカテゴリマスター
DROP TABLE IF EXISTS blog_category;
CREATE TABLE blog_category (
    bc_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    bc_id                INT            DEFAULT 0                     NOT NULL,      -- カテゴリID
    bc_language_id       VARCHAR(2)     DEFAULT ''                    NOT NULL,      -- 言語ID
    bc_history_index     INT            DEFAULT 0                     NOT NULL,      -- 履歴管理用インデックスNo(0～)

    bc_name              VARCHAR(30)    DEFAULT ''                    NOT NULL,      -- カテゴリ名称
    bc_html              TEXT                                         NOT NULL,      -- 説明
    bc_parent_id         INT            DEFAULT 0                     NOT NULL,      -- 親カテゴリID
    bc_sort_order        INT            DEFAULT 0                     NOT NULL,      -- ソート用
    bc_visible           BOOLEAN        DEFAULT true                  NOT NULL,      -- 表示するかどうか

    bc_create_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード作成者
    bc_create_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード作成日時
    bc_update_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード更新者
    bc_update_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード更新日時
    bc_deleted           BOOLEAN        DEFAULT false                 NOT NULL,      -- レコード削除状態
    PRIMARY KEY          (bc_serial),
    UNIQUE               (bc_id,        bc_language_id,               bc_history_index)
) ENGINE=innodb;

-- ブログエントリー(記事)マスター
DROP TABLE IF EXISTS blog_entry;
CREATE TABLE blog_entry (
    be_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    be_id                INT            DEFAULT 0                     NOT NULL,      -- エントリーID
    be_language_id       VARCHAR(2)     DEFAULT ''                    NOT NULL,      -- 言語ID
    be_history_index     INT            DEFAULT 0                     NOT NULL,      -- 履歴管理用インデックスNo(0～)
    
    be_name              VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- エントリータイトル
    be_html              TEXT                                         NOT NULL,      -- エントリー本文HTML
    be_html_ext          TEXT                                         NOT NULL,      -- エントリー本文HTML(続き)
    be_description       VARCHAR(100)   DEFAULT ''                    NOT NULL,      -- 概要
    be_status            SMALLINT       DEFAULT 0                     NOT NULL,      -- エントリー状態(0=未設定、1=編集中、2=公開、3=非公開)
    be_search_tag        VARCHAR(100)   DEFAULT ''                    NOT NULL,      -- 検索用タグ(「,」区切り)
    be_theme_id          VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- ブログテーマID(廃止予定)
    be_thumb_filename    TEXT                                         NOT NULL,      -- サムネールファイル名(「;」区切り)
    be_thumb_src         TEXT                                         NOT NULL,      -- サムネールの元のファイル(リソースディレクトリからの相対パス)
    be_option_fields     TEXT                                         NOT NULL,      -- 追加フィールド
    be_related_content   TEXT                                         NOT NULL,      -- 関連コンテンツID(「,」区切り)
    be_meta_description  TEXT                                         NOT NULL,      -- METAタグ、ページ要約
    be_meta_keywords     TEXT                                         NOT NULL,      -- METAタグ、検索用キーワード
    be_show_comment      BOOLEAN        DEFAULT true                  NOT NULL,      -- コメントを表示するかどうか
    be_receive_comment   BOOLEAN        DEFAULT false                 NOT NULL,      -- コメントの受け付け可否
    be_user_limited      BOOLEAN        DEFAULT false                 NOT NULL,      -- 参照ユーザを制限
    be_blog_id           VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- ブログID
    be_master_serial     INT            DEFAULT 0                     NOT NULL,      -- 作成元レコードのシリアル番号
    be_regist_user_id    INT            DEFAULT 0                     NOT NULL,      -- エントリー作者
    be_regist_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- 投稿日時
    be_dt                TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- ブログ記事更新日時
    be_active_start_dt   TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- 公開期間(開始)
    be_active_end_dt     TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- 公開期間(終了)

    be_check_out_user_id INT            DEFAULT 0                     NOT NULL,      -- チェックアウトユーザID(0のときはチェックイン状態)
    be_check_out_dt      TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- チェックアウト日時
    be_create_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード作成者
    be_create_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード作成日時
    be_update_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード更新者
    be_update_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード更新日時
    be_deleted           BOOLEAN        DEFAULT false                 NOT NULL,      -- レコード削除状態
    PRIMARY KEY          (be_serial),
    UNIQUE               (be_id,        be_language_id,               be_history_index)
) ENGINE=innodb;

-- ブログ記事とブログ記事カテゴリーの対応付けマスター
DROP TABLE IF EXISTS blog_entry_with_category;
CREATE TABLE blog_entry_with_category (
    bw_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    bw_entry_serial      INT            DEFAULT 0                     NOT NULL,      -- ブログ記事シリアル番号
    bw_index             INT            DEFAULT 0                     NOT NULL,      -- インデックス番号
    bw_category_id       INT            DEFAULT 0                     NOT NULL,      -- ブログ記事カテゴリーID
    PRIMARY KEY          (bw_serial),
    UNIQUE               (bw_entry_serial,      bw_index)
) ENGINE=innodb;

-- ブログコメントトラン
DROP TABLE IF EXISTS blog_comment;
CREATE TABLE blog_comment (
    bo_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    bo_entry_id          INT            DEFAULT 0                     NOT NULL,      -- エントリーID
    bo_language_id       VARCHAR(2)     DEFAULT ''                    NOT NULL,      -- 言語ID
    bo_parent_serial     INT            DEFAULT 0                     NOT NULL,      -- 親コメントのシリアル番号
    bo_user_id           INT            DEFAULT 0                     NOT NULL,      -- ユーザID
    bo_regist_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- 投稿日時
    
    bo_no                INT            DEFAULT 0                     NOT NULL,      -- コメント番号
    bo_name              VARCHAR(50)    DEFAULT ''                    NOT NULL,      -- タイトル
    bo_html              TEXT                                         NOT NULL,      -- 本文HTML
    bo_url               TEXT                                         NOT NULL,      -- 参照用URL
    bo_user_name         VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- ユーザ名
    bo_email             VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- Eメールアドレス
    bo_status            SMALLINT       DEFAULT 0                     NOT NULL,      -- 状態(0=未設定、1=非公開、2=公開)
    
    bo_update_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード更新者
    bo_update_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード更新日時
    bo_deleted           BOOLEAN        DEFAULT false                 NOT NULL,      -- レコード削除状態
    PRIMARY KEY          (bo_serial)
) ENGINE=innodb;

-- BBS設定マスター
DROP TABLE IF EXISTS bbs_config;
CREATE TABLE bbs_config (
    sf_id                VARCHAR(30)    DEFAULT ''                    NOT NULL,      -- ID(Key)
    sf_value             TEXT                                         NOT NULL,      -- 値
    sf_name              VARCHAR(50)    DEFAULT ''                    NOT NULL,      -- 名称
    sf_description       VARCHAR(80)    DEFAULT ''                    NOT NULL,      -- 説明
    sf_index             INT            DEFAULT 0                     NOT NULL,      -- ソート用
    PRIMARY KEY          (sf_id)
) ENGINE=innodb;

-- BBSカテゴリマスター
DROP TABLE IF EXISTS bbs_category;
CREATE TABLE bbs_category (
    sr_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    sr_id                INT            DEFAULT 0                     NOT NULL,      -- カテゴリID
    sr_language_id       VARCHAR(2)     DEFAULT ''                    NOT NULL,      -- 言語ID
    sr_history_index     INT            DEFAULT 0                     NOT NULL,      -- 履歴管理用インデックスNo(0～)

    sr_name              VARCHAR(30)    DEFAULT ''                    NOT NULL,      -- カテゴリ名称
    sr_sort_order        INT            DEFAULT 0                     NOT NULL,      -- ソート用
    sr_visible           BOOLEAN        DEFAULT true                  NOT NULL,      -- 表示するかどうか

    sr_create_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード作成者
    sr_create_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード作成日時
    sr_update_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード更新者
    sr_update_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード更新日時
    sr_deleted           BOOLEAN        DEFAULT false                 NOT NULL,      -- レコード削除状態
    PRIMARY KEY          (sr_serial),
    UNIQUE               (sr_id,        sr_language_id,               sr_history_index)
) ENGINE=innodb;

-- BBSグループマスター
DROP TABLE IF EXISTS bbs_group;
CREATE TABLE bbs_group (
    sg_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    sg_id                INT            DEFAULT 0                     NOT NULL,      -- グループID
    sg_language_id       VARCHAR(2)     DEFAULT ''                    NOT NULL,      -- 言語ID
    sg_history_index     INT            DEFAULT 0                     NOT NULL,      -- 履歴管理用インデックスNo(0～)

    sg_name              VARCHAR(30)    DEFAULT ''                    NOT NULL,      -- グループ名称
    sg_sort_order        INT            DEFAULT 0                     NOT NULL,      -- ソート用
    sg_editable          BOOLEAN        DEFAULT true                  NOT NULL,      -- データの編集許可

    sg_create_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード作成者
    sg_create_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード作成日時
    sg_update_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード更新者
    sg_update_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード更新日時
    sg_deleted           BOOLEAN        DEFAULT false                 NOT NULL,      -- レコード削除状態
    PRIMARY KEY          (sg_serial),
    UNIQUE               (sg_id,        sg_language_id,               sg_history_index)
) ENGINE=innodb;

-- BBS記事マスター
DROP TABLE IF EXISTS bbs_thread;
CREATE TABLE bbs_thread (
    se_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    se_id                INT            DEFAULT 0                     NOT NULL,      -- エントリーID
    se_language_id       VARCHAR(2)     DEFAULT ''                    NOT NULL,      -- 言語ID
    se_history_index     INT            DEFAULT 0                     NOT NULL,      -- 履歴管理用インデックスNo(0～)
    
    se_name              VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- タイトル
    se_html              TEXT                                         NOT NULL,      -- 本文HTML
    se_status            SMALLINT       DEFAULT 0                     NOT NULL,      -- 記事状態(0=未設定、1=保留、2=表示、3=非表示)
    se_closed            BOOLEAN        DEFAULT false                 NOT NULL,      -- 投稿終了状態
    se_level             INT            DEFAULT 0                     NOT NULL,      -- 階層レベル
    se_max_sort_order    INT            DEFAULT 0                     NOT NULL,      -- 同スレッド内のソート順最大値
    se_root_id           INT            DEFAULT 0                     NOT NULL,      -- ルートスレッドID(ルートの場合は自ID)
    se_parent_id         INT            DEFAULT 0                     NOT NULL,      -- 親スレッドID
    se_sort_order        INT            DEFAULT 0                     NOT NULL,      -- 同スレッド内のソート順
    se_category_id       INT            DEFAULT 0                     NOT NULL,      -- 所属カテゴリー
    se_regist_user_id    INT            DEFAULT 0                     NOT NULL,      -- 投稿者
    se_regist_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- 投稿日時
    se_log_serial        INT            DEFAULT 0                     NOT NULL,      -- アクセスログシリアル番号

    se_check_out_user_id INT            DEFAULT 0                     NOT NULL,      -- チェックアウトユーザID(0のときはチェックイン状態)
    se_check_out_dt      TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- チェックアウト日時
    se_create_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード作成者
    se_create_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード作成日時
    se_update_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード更新者
    se_update_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード更新日時
    se_deleted           BOOLEAN        DEFAULT false                 NOT NULL,      -- レコード削除状態
    PRIMARY KEY          (se_serial),
    UNIQUE               (se_id,        se_language_id,               se_history_index)
) ENGINE=innodb;

-- BBSグループアクセス権マスター
DROP TABLE IF EXISTS bbs_group_access;
CREATE TABLE bbs_group_access (
    so_group_id          INT            DEFAULT 0                     NOT NULL,      -- グループID(0はゲスト(ログインなし)グループ)
    so_category_id       INT            DEFAULT 0                     NOT NULL,      -- カテゴリーID
    
    so_read              BOOLEAN        DEFAULT false                 NOT NULL,      -- 読み込み権限
    so_write             BOOLEAN        DEFAULT false                 NOT NULL,      -- 書き込み権限

    so_update_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード更新者
    so_update_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード更新日時
    PRIMARY KEY          (so_group_id,  so_category_id)
) ENGINE=innodb;

-- BBS会員情報マスター
DROP TABLE IF EXISTS bbs_member;
CREATE TABLE bbs_member (
    sv_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    sv_id                INT            DEFAULT 0                     NOT NULL,      -- 会員ID
    sv_history_index     INT            DEFAULT 0                     NOT NULL,      -- 履歴管理用インデックスNo(0～)
    
    sv_language_id       VARCHAR(2)     DEFAULT ''                    NOT NULL,      -- 対応言語ID
    sv_type              SMALLINT       DEFAULT 0                     NOT NULL,      -- 会員種別(0=仮会員、1=正会員)
    sv_name              VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- 会員名
    sv_login_user_id     INT            DEFAULT 0                     NOT NULL,      -- ログインユーザID
    sv_group             TEXT                                         NOT NULL,      -- 所属グループ
    sv_regist_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- 登録日時
    sv_avatar            VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- アバターファイル名
    sv_signature         VARCHAR(200)   DEFAULT ''                    NOT NULL,      -- 署名
    sv_url               TEXT                                         NOT NULL,      -- ホームーページ
    sv_recv_mailnews     BOOLEAN        DEFAULT false                 NOT NULL,      -- 新着情報メールを受信するかどうか

    sv_create_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード作成者
    sv_create_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード作成日時
    sv_update_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード更新者
    sv_update_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード更新日時
    sv_deleted           BOOLEAN        DEFAULT false                 NOT NULL,      -- レコード削除状態
    PRIMARY KEY          (sv_serial),
    UNIQUE               (sv_id,        sv_history_index)
) ENGINE=innodb;

-- BBS投稿ログトラン
DROP TABLE IF EXISTS bbs_post_log;
CREATE TABLE bbs_post_log (
    sl_user_id           INT            DEFAULT 0                     NOT NULL,      -- 投稿ユーザID
    sl_count             INT            DEFAULT 0                     NOT NULL,      -- 投稿回数
    sl_update_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- 最終投稿日時
    PRIMARY KEY  (sl_user_id)
) ENGINE=innodb;

-- BBS投稿参照トラン
DROP TABLE IF EXISTS bbs_view_count;
CREATE TABLE bbs_view_count (
    su_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    su_thread_id         INT            DEFAULT 0                     NOT NULL,      -- スレッドID
    su_language_id       VARCHAR(2)     DEFAULT ''                    NOT NULL,      -- 言語ID
    su_date              DATE           DEFAULT '0000-00-00'          NOT NULL,      -- 日付
    su_hour              SMALLINT       DEFAULT 0                     NOT NULL,      -- 時間
    su_count             INT            DEFAULT 0                     NOT NULL,      -- 参照数
    PRIMARY KEY          (su_serial),
    UNIQUE               (su_thread_id,   su_language_id,         su_date,       su_hour)
) ENGINE=innodb;

-- アクセスカウンターウィジェット用
-- アクセス時間管理テーブル
DROP TABLE IF EXISTS ac_access;
CREATE TABLE ac_access (
    ac_ssid              VARCHAR(32)    DEFAULT ''                    NOT NULL,      -- セッションID
    ac_time              INT            DEFAULT 0                     NOT NULL,      -- 最終アクセス時間
    PRIMARY KEY  (ac_ssid)
) ENGINE=innodb;

-- アクセス数管理テーブル
DROP TABLE IF EXISTS ac_count;
CREATE TABLE ac_count (
    co_date              DATE           DEFAULT '0000-00-00'          NOT NULL,      -- 日付
    co_count             INT            DEFAULT 0                     NOT NULL,      -- アクセス回数
    PRIMARY KEY  (co_date)
) ENGINE=innodb;

-- バナーウィジェット用
-- バナー表示定義
DROP TABLE IF EXISTS bn_def;
CREATE TABLE bn_def (
    bd_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    bd_id                INT            DEFAULT 0                     NOT NULL,      -- バナーID
    
    bd_item_id           TEXT                                         NOT NULL,      -- 対応バナー項目(「,」区切りで複数指定可)
    bd_name              VARCHAR(50)    DEFAULT ''                    NOT NULL,      -- バナー名
    bd_disp_type         SMALLINT       DEFAULT 0                     NOT NULL,      -- 表示形式(0=サイクリック,1=ランダム)
    bd_first_item_index  INT            DEFAULT 0                     NOT NULL,      -- バナー項目の読み込み位置インデックス(サイクリック時に使用)
    bd_disp_item_count   SMALLINT       DEFAULT 0                     NOT NULL,      -- 同時に表示する項目数
    bd_disp_direction    SMALLINT       DEFAULT 0                     NOT NULL,      -- 表示方向(0=縦,1=横)
    bd_disp_align        SMALLINT       DEFAULT 0                     NOT NULL,      -- 表示位置アラインメント(0=指定なし,1=left,2=center,3=right)
    bd_css_id            VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- CSS用ID
    bd_css               TEXT                                         NOT NULL,      -- CSS
    bd_item_html         TEXT                                         NOT NULL,      -- バナー項目表示テンプレート
    
    bd_update_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード更新者
    bd_update_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード更新日時
    PRIMARY KEY          (bd_serial),
    UNIQUE               (bd_id)
) ENGINE=innodb;

-- バナー項目
DROP TABLE IF EXISTS bn_item;
CREATE TABLE bn_item (
    bi_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    bi_id                INT            DEFAULT 0                     NOT NULL,      -- バナー項目ID
    bi_history_index     INT            DEFAULT 0                     NOT NULL,      -- 履歴管理用インデックスNo(0～)
    
    bi_group             INT            DEFAULT 0                     NOT NULL,      -- グルーピング用
    bi_name              VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- バナー名
    bi_type              INT            DEFAULT 0                     NOT NULL,      -- 項目タイプ(0=画像、1=Flash)
    bi_description       VARCHAR(100)   DEFAULT ''                    NOT NULL,      -- 説明
    bi_admin_note        VARCHAR(100)   DEFAULT ''                    NOT NULL,      -- 管理者用備考
    bi_image_url         TEXT                                         NOT NULL,      -- 表示画像
    bi_link_url          TEXT                                         NOT NULL,      -- リンク先
    bi_image_width       VARCHAR(10)    DEFAULT ''                    NOT NULL,      -- 画像幅
    bi_image_height      VARCHAR(10)    DEFAULT ''                    NOT NULL,      -- 画像高さ
    bi_image_alt         VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- 画像代替テキスト
    bi_image_title       VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- 画像ツールチップ
    bi_html              TEXT                                         NOT NULL,      -- テンプレートHTML
    bi_attr              TEXT                                         NOT NULL,      -- その他属性(「;」区切り)
    bi_visible           BOOLEAN        DEFAULT true                  NOT NULL,      -- 表示可否
    bi_active_start_dt   TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- 表示可能期間(開始)
    bi_active_end_dt     TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- 表示可能期間(終了)

    bi_check_out_user_id INT            DEFAULT 0                     NOT NULL,      -- チェックアウトユーザID(0のときはチェックイン状態)
    bi_check_out_dt      TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- チェックアウト日時
    bi_create_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード作成者
    bi_create_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード作成日時
    bi_update_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード更新者
    bi_update_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード更新日時
    bi_deleted           BOOLEAN        DEFAULT false                 NOT NULL,      -- レコード削除状態
    PRIMARY KEY          (bi_serial),
    UNIQUE               (bi_id,        bi_history_index)
) ENGINE=innodb;

-- バナー項目参照ログ
DROP TABLE IF EXISTS bn_item_view;
CREATE TABLE bn_item_view (
    bv_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    bv_public_key        CHAR(32)       DEFAULT ''                    NOT NULL,      -- 公開発行キー
    bv_item_serial       INT            DEFAULT 0                     NOT NULL,      -- バナー項目シリアル番号
    bv_log_serial        INT            DEFAULT 0                     NOT NULL,      -- アクセスログシリアル番号
    bv_dt                TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- 参照日時
    PRIMARY KEY          (bv_serial)
) ENGINE=innodb;

-- バナー項目クリックログ
DROP TABLE IF EXISTS bn_item_access;
CREATE TABLE bn_item_access (
    ba_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    ba_public_key        CHAR(32)       DEFAULT ''                    NOT NULL,      -- 公開発行キー
    ba_redirect_url      TEXT                                         NOT NULL,      -- 遷移先URL
    ba_log_serial        INT            DEFAULT 0                     NOT NULL,      -- アクセスログシリアル番号
    ba_dt                TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- 参照日時
    PRIMARY KEY          (ba_serial)
) ENGINE=innodb;

-- 予約リソースマスター
DROP TABLE IF EXISTS reserve_resource;
CREATE TABLE reserve_resource (
    rr_id                INT            DEFAULT 0                     NOT NULL,      -- リソースID
    rr_type              INT            DEFAULT 0                     NOT NULL,      -- リソースタイプ(0=常設、1=週間、2=スポット)
    rr_config_id         INT            DEFAULT 0                     NOT NULL,      -- 設定ID
    rr_name              VARCHAR(50)    DEFAULT ''                    NOT NULL,      -- 名称
    rr_description       VARCHAR(80)    DEFAULT ''                    NOT NULL,      -- 説明
    rr_visible           BOOLEAN        DEFAULT true                  NOT NULL,      -- 表示可否
    rr_active_start_dt   TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- 有効期間(開始)
    rr_active_end_dt     TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- 有効期間(終了)
    rr_sort_order        INT            DEFAULT 0                     NOT NULL,      -- 表示ソート用
    
    rr_update_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード更新者
    rr_update_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード更新日時
    rr_deleted           BOOLEAN        DEFAULT false                 NOT NULL,      -- レコード削除状態
    PRIMARY KEY          (rr_id)
) ENGINE=innodb;

-- 予約設定マスター
DROP TABLE IF EXISTS reserve_config;
CREATE TABLE reserve_config (
    rc_id                INT            DEFAULT 0                     NOT NULL,      -- 定義ID
    rc_key               VARCHAR(30)    DEFAULT ''                    NOT NULL,      -- キー
    rc_value             TEXT                                         NOT NULL,      -- 値
    rc_name              VARCHAR(50)    DEFAULT ''                    NOT NULL,      -- 名称
    rc_description       VARCHAR(80)    DEFAULT ''                    NOT NULL,      -- 説明
    rc_index             INT            DEFAULT 0                     NOT NULL,      -- ソート用
    PRIMARY KEY          (rc_id,        rc_key)
) ENGINE=innodb;

-- 予約カレンダーマスター
DROP TABLE IF EXISTS reserve_calendar;
CREATE TABLE reserve_calendar (
    ra_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    ra_config_id         INT            DEFAULT 0                     NOT NULL,      -- 設定ID
    ra_usual             BOOLEAN        DEFAULT false                 NOT NULL,      -- 通常あるいは特定日の区別
    ra_specify_type      INT            DEFAULT 0                     NOT NULL,      -- 属性指定方法(0=デフォルト値、1=曜日指定、2=毎月、3=毎年)
    ra_day_attribute     INT            DEFAULT 0                     NOT NULL,      -- 日にち属性(0=未設定、1～7=日曜～土曜、8=祝日)
    ra_date              DATE           DEFAULT '0000-00-00'          NOT NULL,      -- 日にち指定の場合の日にち
    ra_start_time        INT            DEFAULT 0                     NOT NULL,      -- 日にち時間範囲指定の場合の開始時間(hhmm)
    ra_end_time          INT            DEFAULT 0                     NOT NULL,      -- 日にち時間範囲指定の場合の終了時間(hhmm)
    ra_available         BOOLEAN        DEFAULT false                 NOT NULL,      -- 利用可能かどうか
    PRIMARY KEY          (ra_serial)
) ENGINE=innodb;

-- 予約状況トラン
DROP TABLE IF EXISTS reserve_status;
CREATE TABLE reserve_status (
    rs_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    rs_resource_id       INT            DEFAULT 0                     NOT NULL,      -- リソースID
    rs_user_id           INT            DEFAULT 0                     NOT NULL,      -- ユーザID
    rs_status            INT            DEFAULT 0                     NOT NULL,      -- 状態(1=予約、2=キャンセル)
    rs_start_dt          TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- 開始日時
    rs_note              VARCHAR(100)   DEFAULT ''                    NOT NULL,      -- 備考
    
    rs_create_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード作成者
    rs_create_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード作成日時
    rs_update_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード更新者
    rs_update_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード更新日時
    rs_deleted           BOOLEAN        DEFAULT false                 NOT NULL,      -- レコード削除状態
    PRIMARY KEY          (rs_serial)
) ENGINE=innodb;

-- BBS(2ch)設定マスター
DROP TABLE IF EXISTS bbs_2ch_config;
CREATE TABLE bbs_2ch_config (
    tg_board_id          VARCHAR(30)    DEFAULT ''                    NOT NULL,      -- 掲示板ID(空文字列=デフォルト)
    tg_id                VARCHAR(30)    DEFAULT ''                    NOT NULL,      -- ID(key)
    tg_value             TEXT                                         NOT NULL,      -- 値
    tg_name              VARCHAR(50)    DEFAULT ''                    NOT NULL,      -- 名称
    tg_description       VARCHAR(80)    DEFAULT ''                    NOT NULL,      -- 説明
    tg_index             INT            DEFAULT 0                     NOT NULL,      -- ソート用
    PRIMARY KEY          (tg_board_id,  tg_id)
) ENGINE=innodb;

-- BBS(2ch)スレッドマスター
DROP TABLE IF EXISTS bbs_2ch_thread;
CREATE TABLE bbs_2ch_thread (
    th_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    th_board_id          VARCHAR(30)    DEFAULT ''                    NOT NULL,      -- 掲示板ID(空文字列=デフォルト)
    th_id                CHAR(32)       DEFAULT ''                    NOT NULL,      -- スレッドID(掲示板IDに関わりなく全体でユニークに設定)
    
    th_subject           TEXT                                         NOT NULL,      -- 件名
    th_message_count     INT            DEFAULT 0                     NOT NULL,      -- 投稿数
    th_access_count      INT            DEFAULT 0                     NOT NULL,      -- 参照数
    th_dt                TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- スレッド更新日時
    th_log_serial        INT            DEFAULT 0                     NOT NULL,      -- BBSアクセスログシリアル番号
    
    th_create_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード作成者
    th_create_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード作成日時
    th_update_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード更新者
    th_update_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード更新日時
    th_deleted           BOOLEAN        DEFAULT false                 NOT NULL,      -- レコード削除状態
    PRIMARY KEY          (th_serial),
    UNIQUE               (th_board_id,  th_id)
) ENGINE=innodb;

-- BBS(2ch)スレッドメッセージトラン
DROP TABLE IF EXISTS bbs_2ch_thread_message;
CREATE TABLE bbs_2ch_thread_message (
    te_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    te_board_id          VARCHAR(30)    DEFAULT ''                    NOT NULL,      -- 掲示板ID(空文字列=デフォルト)
    te_thread_id         CHAR(32)       DEFAULT ''                    NOT NULL,      -- スレッドID
    te_index             INT            DEFAULT 0                     NOT NULL,      -- 投稿番号(1以上)
    
    te_user_name         VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- 投稿者名
    te_email             VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- Eメールアドレス
    te_message           TEXT                                         NOT NULL,      -- 投稿文
    te_status_param      TEXT                                         NOT NULL,      -- 投稿文状態
    te_regist_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- 投稿日時
    te_log_serial        INT            DEFAULT 0                     NOT NULL,      -- BBSアクセスログシリアル番号

    te_update_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード更新者
    te_update_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード更新日時
    te_deleted           BOOLEAN        DEFAULT false                 NOT NULL,      -- レコード削除状態
    PRIMARY KEY          (te_serial),
    UNIQUE               (te_board_id,  te_thread_id,  te_index)
) ENGINE=innodb;

-- --------------------------------------------------------------------------------------------------
-- イベント情報用
-- --------------------------------------------------------------------------------------------------
-- イベント設定マスター
DROP TABLE IF EXISTS event_config;
CREATE TABLE event_config (
    eg_id                VARCHAR(30)    DEFAULT ''                    NOT NULL,      -- ID(Key)
    eg_value             TEXT                                         NOT NULL,      -- 値
    eg_name              VARCHAR(50)    DEFAULT ''                    NOT NULL,      -- 名称
    eg_description       VARCHAR(80)    DEFAULT ''                    NOT NULL,      -- 説明
    eg_index             INT            DEFAULT 0                     NOT NULL,      -- ソート用
    PRIMARY KEY          (eg_id)
) ENGINE=innodb;

-- イベント記事マスター
DROP TABLE IF EXISTS event_entry;
CREATE TABLE event_entry (
    ee_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    ee_id                INT            DEFAULT 0                     NOT NULL,      -- エントリーID
    ee_language_id       VARCHAR(2)     DEFAULT ''                    NOT NULL,      -- 言語ID
    ee_history_index     INT            DEFAULT 0                     NOT NULL,      -- 履歴管理用インデックスNo(0～)
    
    ee_name              VARCHAR(60)    DEFAULT ''                    NOT NULL,      -- エントリータイトル
    ee_html              TEXT                                         NOT NULL,      -- エントリー本文HTML
    ee_html_ext          TEXT                                         NOT NULL,      -- エントリー本文HTML(結果)
    ee_summary           TEXT                                         NOT NULL,      -- 概要
    ee_admin_note        VARCHAR(100)   DEFAULT ''                    NOT NULL,      -- 管理者用備考
    ee_place             TEXT                                         NOT NULL,      -- 場所
    ee_contact           TEXT                                         NOT NULL,      -- 連絡先(Eメール,電話番号)
    ee_url               TEXT                                         NOT NULL,      -- URL
    ee_regist_user_id    INT            DEFAULT 0                     NOT NULL,      -- エントリー作者
    ee_status            SMALLINT       DEFAULT 0                     NOT NULL,      -- エントリー状態(0=未設定、1=編集中、2=公開、3=非公開)
    ee_show_comment      BOOLEAN        DEFAULT true                  NOT NULL,      -- コメントを表示するかどうか
    ee_receive_comment   BOOLEAN        DEFAULT false                 NOT NULL,      -- コメントの受け付け可否
    ee_is_all_day        BOOLEAN        DEFAULT false                 NOT NULL,      -- 終日イベントかどうか
    ee_user_limited      BOOLEAN        DEFAULT false                 NOT NULL,      -- 参照ユーザを制限
    ee_start_dt          TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- イベント期間(開始)
    ee_end_dt            TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- イベント期間(終了)
    ee_thumb_filename    TEXT                                         NOT NULL,      -- サムネールファイル名(「;」区切り)
    ee_option_fields     TEXT                                         NOT NULL,      -- 追加フィールド
    ee_related_content   TEXT                                         NOT NULL,      -- 関連コンテンツID(「,」区切り)

    ee_check_out_user_id INT            DEFAULT 0                     NOT NULL,      -- チェックアウトユーザID(0のときはチェックイン状態)
    ee_check_out_dt      TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- チェックアウト日時
    ee_create_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード作成者
    ee_create_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード作成日時
    ee_update_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード更新者
    ee_update_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード更新日時
    ee_deleted           BOOLEAN        DEFAULT false                 NOT NULL,      -- レコード削除状態
    PRIMARY KEY          (ee_serial),
    UNIQUE               (ee_id,        ee_language_id,               ee_history_index)
) ENGINE=innodb;

-- イベントカテゴリーマスター
DROP TABLE IF EXISTS event_category;
CREATE TABLE event_category (
    ec_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    ec_id                INT            DEFAULT 0                     NOT NULL,      -- カテゴリーID
    ec_language_id       VARCHAR(2)     DEFAULT ''                    NOT NULL,      -- 言語ID
    ec_history_index     INT            DEFAULT 0                     NOT NULL,      -- 履歴管理用インデックスNo(0～)

    ec_name              VARCHAR(30)    DEFAULT ''                    NOT NULL,      -- カテゴリー名称
    ec_parent_id         INT            DEFAULT 0                     NOT NULL,      -- 親カテゴリーID
    ec_sort_order        INT            DEFAULT 0                     NOT NULL,      -- ソート用
    ec_visible           BOOLEAN        DEFAULT true                  NOT NULL,      -- 表示するかどうか

    ec_create_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード作成者
    ec_create_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード作成日時
    ec_update_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード更新者
    ec_update_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード更新日時
    ec_deleted           BOOLEAN        DEFAULT false                 NOT NULL,      -- レコード削除状態
    PRIMARY KEY          (ec_serial),
    UNIQUE               (ec_id,        ec_language_id,               ec_history_index)
) ENGINE=innodb;

-- イベント記事とイベント記事カテゴリーの対応付けマスター
DROP TABLE IF EXISTS event_entry_with_category;
CREATE TABLE event_entry_with_category (
    ew_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    ew_entry_serial      INT            DEFAULT 0                     NOT NULL,      -- ブログ記事シリアル番号
    ew_index             INT            DEFAULT 0                     NOT NULL,      -- インデックス番号
    ew_category_id       INT            DEFAULT 0                     NOT NULL,      -- ブログ記事カテゴリーID
    PRIMARY KEY          (ew_serial),
    UNIQUE               (ew_entry_serial,      ew_index)
) ENGINE=innodb;

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

-- イベント予約マスター
DROP TABLE IF EXISTS evententry;
CREATE TABLE evententry (
    et_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    et_id                INT            DEFAULT 0                     NOT NULL,      -- イベント予約ID
    et_history_index     INT            DEFAULT 0                     NOT NULL,      -- 履歴管理用インデックスNo(0～)
    
    et_event_id          VARCHAR(32)    DEFAULT ''                    NOT NULL,      -- イベントID
    et_type              VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- 受付タイプ
    et_code              VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- イベント予約受付コード
    et_html              TEXT                                         NOT NULL,      -- 説明
    et_status            SMALLINT       DEFAULT 0                     NOT NULL,      -- 状態(0=未設定、1=非公開、2=受付中、3=受付停止、4=受付終了)
    et_show_entry_count  BOOLEAN        DEFAULT true                  NOT NULL,      -- 参加者数を表示するかどうか
    et_show_entry_member BOOLEAN        DEFAULT true                  NOT NULL,      -- 参加者を表示するかどうか(会員対象)
    et_enable_cancel     BOOLEAN        DEFAULT true                  NOT NULL,      -- キャンセル機能を使用可能にするかどうか
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
    er_index             INT            DEFAULT 0                     NOT NULL,      -- インデックス番号(1～)
    
    er_code              VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- 受付コード
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

-- --------------------------------------------------------------------------------------------------
-- フォトギャラリー用
-- --------------------------------------------------------------------------------------------------
-- フォトギャラリー設定マスター
DROP TABLE IF EXISTS photo_config;
CREATE TABLE photo_config (
    hg_id                VARCHAR(30)    DEFAULT ''                    NOT NULL,      -- ID(key)
    hg_value             TEXT                                         NOT NULL,      -- 値
    hg_name              VARCHAR(80)    DEFAULT ''                    NOT NULL,      -- 名称
    hg_description       VARCHAR(160)   DEFAULT ''                    NOT NULL,      -- 説明
    hg_index             INT            DEFAULT 0                     NOT NULL,      -- ソート用
    PRIMARY KEY          (hg_id)
) ENGINE=innodb;

-- 写真情報マスター
DROP TABLE IF EXISTS photo;
CREATE TABLE photo (
    ht_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    ht_id                INT            DEFAULT 0                     NOT NULL,      -- 画像ID
    ht_language_id       VARCHAR(2)     DEFAULT ''                    NOT NULL,      -- 言語ID
    ht_history_index     INT            DEFAULT 0                     NOT NULL,      -- 履歴管理用インデックスNo(0～)

    ht_public_id         CHAR(32)       DEFAULT ''                    NOT NULL,      -- 公開用画像ID
    ht_dir               TEXT                                         NOT NULL,      -- 画像格納ディレクトリ
    ht_code              VARCHAR(50)    DEFAULT ''                    NOT NULL,      -- 画像コード
    ht_sort_order        INT            DEFAULT 0                     NOT NULL,      -- ソート順
    ht_mime_type         VARCHAR(30)    DEFAULT ''                    NOT NULL,      -- 画像MIMEタイプ
    ht_image_size        VARCHAR(10)    DEFAULT ''                    NOT NULL,      -- 画像縦横サイズ
    ht_original_filename VARCHAR(256)   DEFAULT ''                    NOT NULL,      -- 元の画像ファイル名
    ht_thumb_filename    TEXT                                         NOT NULL,      -- サムネールファイル名(「;」区切り)
    ht_file_size         INT            DEFAULT 0                     NOT NULL,      -- ファイルサイズ(バイト)
    ht_name              VARCHAR(160)   DEFAULT ''                    NOT NULL,      -- 画像名称
    ht_camera            VARCHAR(80)    DEFAULT ''                    NOT NULL,      -- カメラ
    ht_location          TEXT                                         NOT NULL,      -- 撮影場所
    ht_date              DATE           DEFAULT '0000-00-00'          NOT NULL,      -- 撮影日
    ht_time              INT            DEFAULT 0                     NOT NULL,      -- 撮影時間(hhmm)
    ht_summary           VARCHAR(100)   DEFAULT ''                    NOT NULL,      -- 画像概要
    ht_description       TEXT                                         NOT NULL,      -- 画像説明
    ht_note              TEXT                                         NOT NULL,      -- 補足情報(廃止予定)
    ht_keyword           TEXT                                         NOT NULL,      -- 検索用キーワード(「,」区切りで複数指定可)
    ht_visible           BOOLEAN        DEFAULT true                  NOT NULL,      -- 表示するかどうか
    ht_user_limited      BOOLEAN        DEFAULT false                 NOT NULL,      -- 参照ユーザを制限
    ht_license           VARCHAR(2)     DEFAULT ''                    NOT NULL,      -- ライセンス(ロイヤリティフリー(RF),ライツマネージド(RM))
    ht_owner_id          INT            DEFAULT 0                     NOT NULL,      -- 所有者ID
    ht_regist_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- アップロード日時
    ht_active_start_dt   TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- 公開期間(開始)
    ht_active_end_dt     TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- 公開期間(終了)
    ht_rate_average      DECIMAL(4,2)   DEFAULT 0                     NOT NULL,      -- 評価平均値
    ht_view_count        INT            DEFAULT 0                     NOT NULL,      -- 参照数
    
    ht_create_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード作成者
    ht_create_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード作成日時
    ht_update_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード更新者
    ht_update_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード更新日時
    ht_deleted           BOOLEAN        DEFAULT false                 NOT NULL,      -- レコード削除状態
    PRIMARY KEY          (ht_serial),
    UNIQUE               (ht_id,        ht_language_id,               ht_history_index)
) ENGINE=innodb;

-- 写真カテゴリマスター
DROP TABLE IF EXISTS photo_category;
CREATE TABLE photo_category (
    hc_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    hc_id                INT            DEFAULT 0                     NOT NULL,      -- カテゴリID
    hc_language_id       VARCHAR(2)     DEFAULT ''                    NOT NULL,      -- 言語ID
    hc_history_index     INT            DEFAULT 0                     NOT NULL,      -- 履歴管理用インデックスNo(0～)

    hc_name              VARCHAR(30)    DEFAULT ''                    NOT NULL,      -- カテゴリ名称
    hc_parent_id         INT            DEFAULT 0                     NOT NULL,      -- 親カテゴリID
    hc_password          CHAR(32)       DEFAULT ''                    NOT NULL,      -- アクセス制限パスワード(MD5)
    hc_sort_order        INT            DEFAULT 0                     NOT NULL,      -- ソート用
    hc_visible           BOOLEAN        DEFAULT true                  NOT NULL,      -- 表示するかどうか

    hc_create_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード作成者
    hc_create_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード作成日時
    hc_update_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード更新者
    hc_update_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード更新日時
    hc_deleted           BOOLEAN        DEFAULT false                 NOT NULL,      -- レコード削除状態
    PRIMARY KEY          (hc_serial),
    UNIQUE               (hc_id,        hc_language_id,  hc_history_index)
) ENGINE=innodb;

-- 写真と写真カテゴリーの対応付けマスター
DROP TABLE IF EXISTS photo_with_category;
CREATE TABLE photo_with_category (
    hw_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    hw_photo_serial      INT            DEFAULT 0                     NOT NULL,      -- 写真情報シリアル番号
    hw_index             INT            DEFAULT 0                     NOT NULL,      -- インデックス番号(0～)
    hw_category_id       INT            DEFAULT 0                     NOT NULL,      -- カテゴリID
    PRIMARY KEY          (hw_serial),
    UNIQUE               (hw_photo_serial,  hw_index)
) ENGINE=innodb;

-- 画像評価トラン
DROP TABLE IF EXISTS photo_rate;
CREATE TABLE photo_rate (
    hr_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    hr_photo_id          INT            DEFAULT 0                     NOT NULL,      -- 画像ID
    hr_language_id       VARCHAR(2)     DEFAULT ''                    NOT NULL,      -- 言語ID
    hr_client_id         CHAR(32)       DEFAULT ''                    NOT NULL,      -- クライアントID
    hr_ip                VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- アクセス元IP(IPv6対応)
    hr_parent_serial     INT            DEFAULT 0                     NOT NULL,      -- 親コメントのシリアル番号
    hr_user_id           INT            DEFAULT 0                     NOT NULL,      -- ユーザID
    hr_regist_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- 投稿日時
    
    hr_rate_value        SMALLINT       DEFAULT 0                     NOT NULL,      -- 評価値
    hr_message           TEXT                                         NOT NULL,      -- メッセージ
    hr_status            SMALLINT       DEFAULT 0                     NOT NULL,      -- 状態(0=未設定、1=非公開、2=公開)
    
    hr_update_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード更新者
    hr_update_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード更新日時
    hr_deleted           BOOLEAN        DEFAULT false                 NOT NULL,      -- レコード削除状態
    PRIMARY KEY          (hr_serial)
) ENGINE=innodb;
-- --------------------------------------------------------------------------------------------------
-- 汎用コメント用
-- --------------------------------------------------------------------------------------------------
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
    cf_image_max_upload  INT            DEFAULT 0                     NOT NULL,      -- 画像の最大アップロード数
    cf_upload_max_bytes  INT            DEFAULT 0                     NOT NULL,      -- アップロード画像の最大バイトサイズ
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
) ENGINE=innodb;

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
) ENGINE=innodb;

-- --------------------------------------------------------------------------------------------------
-- カレンダー用
-- --------------------------------------------------------------------------------------------------
-- カレンダー時間枠
DROP TABLE IF EXISTS time_period;
CREATE TABLE time_period (
    to_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    to_date_type_id      INT            DEFAULT 0                     NOT NULL,      -- 日付タイプID(1～,-1以下=カレンダー日付のシリアル番号)
    to_index             INT            DEFAULT 0                     NOT NULL,      -- インデックス番号(0～)
    
    to_name              VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- 名前
    to_start_time        TIME           DEFAULT '00:00:00'            NOT NULL,      -- 開始時刻
    to_minute            INT            DEFAULT 0                     NOT NULL,      -- 時間(分)
    PRIMARY KEY          (to_serial),
    UNIQUE               (to_date_type_id,        to_index)
) ENGINE=innodb;

-- 日付タイプ
DROP TABLE IF EXISTS date_type;
CREATE TABLE date_type (
    dt_id                INT            DEFAULT 0                     NOT NULL,      -- 日付タイプID
    
    dt_name              VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- 名称
    dt_sort_order        INT            DEFAULT 0                     NOT NULL,      -- ソート順

    dt_update_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード更新者
    dt_update_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード更新日時
    dt_deleted           BOOLEAN        DEFAULT false                 NOT NULL,      -- レコード削除状態
    PRIMARY KEY          (dt_id)
) ENGINE=innodb;

-- カレンダー日付
DROP TABLE IF EXISTS calendar_date;
CREATE TABLE calendar_date (
    ce_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    ce_def_id            INT            DEFAULT 0                     NOT NULL,      -- カレンダー定義ID
    ce_type              INT            DEFAULT 0                     NOT NULL,      -- データタイプ(0=インデックス番号,1=日付,10=基本日オプション(インデックス番号))
    ce_index             INT            DEFAULT 0                     NOT NULL,      -- インデックス番号(0～)
    ce_date              DATE           DEFAULT '0000-00-00'          NOT NULL,      -- 日付
    
    ce_name              VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- 名前
    ce_date_type_id      INT            DEFAULT 0                     NOT NULL,      -- 日付タイプID(1～,-1=個別時間定義)
    ce_style             TEXT                                         NOT NULL,      -- HTMLスタイル属性
    ce_param             TEXT                                         NOT NULL,      -- オプションパラメータ(シリアライズデータ)
    PRIMARY KEY          (ce_serial),
    UNIQUE               (ce_def_id,    ce_type,     ce_index,        ce_date)
) ENGINE=innodb;

-- カレンダー定義
DROP TABLE IF EXISTS calendar_def;
CREATE TABLE calendar_def (
    cd_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    cd_id                INT            DEFAULT 0                     NOT NULL,      -- カレンダー定義ID
    cd_history_index     INT            DEFAULT 0                     NOT NULL,      -- 履歴管理用インデックスNo(0～)
    
    cd_name              VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- 名前
    cd_repeat_type       INT            DEFAULT 0                     NOT NULL,      -- 繰り返しタイプ(0=繰り返しなし,1=曜日基準,2=日付基準)
    cd_date_count        INT            DEFAULT 0                     NOT NULL,      -- 所要日数
    cd_style             TEXT                                         NOT NULL,      -- HTMLスタイル属性
    cd_open_date_style   TEXT                                         NOT NULL,      -- 開業日HTMLスタイル属性
    cd_closed_date_style TEXT                                         NOT NULL,      -- 休業日HTMLスタイル属性
    cd_active_start_dt   TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- 有効期間(開始)
    cd_active_end_dt     TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- 有効期間(終了)
    
    cd_create_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード作成者
    cd_create_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード作成日時
    cd_update_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード更新者
    cd_update_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード更新日時
    cd_deleted           BOOLEAN        DEFAULT false                 NOT NULL,      -- レコード削除状態
    PRIMARY KEY          (cd_serial),
    UNIQUE               (cd_id,        cd_history_index)
) ENGINE=innodb;

-- カレンダーイベントマスター
DROP TABLE IF EXISTS calendar_event;
CREATE TABLE calendar_event (
    cv_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    cv_id                INT            DEFAULT 0                     NOT NULL,      -- エントリーID
    cv_history_index     INT            DEFAULT 0                     NOT NULL,      -- 履歴管理用インデックスNo(0～)
    
    cv_name              VARCHAR(60)    DEFAULT ''                    NOT NULL,      -- エントリータイトル
    cv_html              TEXT                                         NOT NULL,      -- エントリー本文HTML
    cv_start_dt          TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- イベント期間(開始)
    cv_end_dt            TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- イベント期間(終了)
    cv_visible           BOOLEAN        DEFAULT true                  NOT NULL,      -- 表示可否
    cv_is_all_day        BOOLEAN        DEFAULT false                 NOT NULL,      -- 終日イベントかどうか
    
    cv_create_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード作成者
    cv_create_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード作成日時
    cv_update_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード更新者
    cv_update_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード更新日時
    cv_deleted           BOOLEAN        DEFAULT false                 NOT NULL,      -- レコード削除状態
    PRIMARY KEY          (cv_serial),
    UNIQUE               (cv_id,        cv_history_index)
) ENGINE=innodb;
