-- *
-- * 基本テーブル作成スクリプト
-- *
-- * PHP versions 5
-- *
-- * LICENSE: This source file is licensed under the terms of the GNU General Public License.
-- *
-- * @package    Magic3 Framework
-- * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
-- * @copyright  Copyright 2006-2011 Magic3 Project.
-- * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
-- * @version    SVN: $Id: create_base.sql 6157 2013-07-02 00:04:47Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- 基本テーブル作成スクリプト
-- ベースシステム(フレームワーク)で最小限必要なテーブルの作成を行う
-- --------------------------------------------------------------------------------------------------

-- システム設定マスター
-- システムの動作に影響する設定を管理する
DROP TABLE IF EXISTS _system_config;
CREATE TABLE _system_config (
    sc_id                VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- ID(Key)
    sc_value             TEXT                                         NOT NULL,      -- 値
    sc_name              VARCHAR(60)    DEFAULT ''                    NOT NULL,      -- 名称
    sc_description       VARCHAR(80)    DEFAULT ''                    NOT NULL,      -- 説明
    sc_index             INT            DEFAULT 0                     NOT NULL,      -- ソート用
    PRIMARY KEY          (sc_id)
) TYPE=innodb;

-- 多言語対応文字列マスター
DROP TABLE IF EXISTS _language_string;
CREATE TABLE _language_string (
    ls_id                VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- ID(Key)
    ls_language_id       VARCHAR(2)     DEFAULT ''                    NOT NULL,      -- 言語ID
    
    ls_value             TEXT                                         NOT NULL,      -- 値
    ls_name              VARCHAR(60)    DEFAULT ''                    NOT NULL,      -- 名称
    ls_description       VARCHAR(80)    DEFAULT ''                    NOT NULL,      -- 説明
    ls_index             INT            DEFAULT 0                     NOT NULL,      -- ソート用
    PRIMARY KEY          (ls_id,        ls_language_id)
) TYPE=innodb;

-- 言語マスター
DROP TABLE IF EXISTS _language;
CREATE TABLE _language (
    ln_id                VARCHAR(5)     DEFAULT ''                    NOT NULL,      -- 言語ID
    ln_name              TEXT                                         NOT NULL,      -- 言語名称
    ln_name_en           VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- 英語名称
    ln_priority          INT            DEFAULT 0                     NOT NULL,      -- 優先順位
    ln_image_filename    VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- 画像ファイル名
    ln_available         BOOLEAN        DEFAULT true                  NOT NULL,      -- メニューから選択可能かどうか
    PRIMARY KEY  (ln_id)
) TYPE=innodb;

-- 番号管理マスター
DROP TABLE IF EXISTS _used_no;
CREATE TABLE _used_no (
    un_id                VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- ID(Key)
    un_value             VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- 値
    PRIMARY KEY  (un_id)
) TYPE=innodb;

-- 運用メッセージタイプマスター
DROP TABLE IF EXISTS _operation_type;
CREATE TABLE _operation_type (
    ot_id                VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- 運用メッセージタイプID
    ot_name              VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- 運用メッセージ名称
    ot_description       VARCHAR(100)   DEFAULT ''                    NOT NULL,      -- 説明
    ot_level             INT            DEFAULT 0                     NOT NULL,      -- メッセージレベル(0=通常、1=注意、10=要確認)
    ot_sort_order        INT            DEFAULT 0                     NOT NULL,      -- ソート順
    PRIMARY KEY  (ot_id)
) TYPE=innodb;

-- 運用ログトラン
DROP TABLE IF EXISTS _operation_log;
CREATE TABLE _operation_log (
    ol_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    ol_type              VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- メッセージタイプ(info=情報,warn=警告,error=通常エラー,fatal=致命的エラー,user_info=ユーザ操作,user_err=ユーザ操作エラー,user_access=不正アクセス,user_data=不正データ)
    ol_message           TEXT                                         NOT NULL,      -- エラーメッセージ
    ol_message_ext       TEXT                                         NOT NULL,      -- メッセージ詳細
    ol_message_code      INT            DEFAULT 0                     NOT NULL,      -- メッセージコード
    ol_link_url          TEXT                                         NOT NULL,      -- リンク用URL(未使用?)
    ol_link              TEXT                                         NOT NULL,      -- リンク先
    ol_search_option     TEXT                                         NOT NULL,      -- 検索用補助データ
    ol_checked           BOOLEAN        DEFAULT false                 NOT NULL,      -- メッセージ確認状況
    ol_widget_id         VARCHAR(50)    DEFAULT ''                    NOT NULL,      -- 実行ウィジェットID(ファイル名)
    ol_method            TEXT                                         NOT NULL,      -- 実行メソッド
    ol_access_log_serial INT            DEFAULT 0                     NOT NULL,      -- アクセスログシリアル番号
    ol_dt                TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- 記録日時
    PRIMARY KEY          (ol_serial)
) TYPE=innodb;

-- デバッグ用メッセージトラン
DROP TABLE IF EXISTS _debug;
CREATE TABLE _debug (
    db_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    db_message           TEXT                                         NOT NULL,      -- メッセージ
    db_method            TEXT                                         NOT NULL,      -- 実行メソッド
    db_access_log_serial INT            DEFAULT 0                     NOT NULL,      -- アクセスログシリアル番号
    db_memory_usage      INT            DEFAULT 0                     NOT NULL,      -- メモリ使用量(バイト)
    db_time              VARCHAR(10)    DEFAULT ''                    NOT NULL,      -- 起動からの経過時間(秒)
    db_dt                TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- 記録日時
    PRIMARY KEY          (db_serial)
) TYPE=innodb;

-- バージョン管理マスター
DROP TABLE IF EXISTS _version;
CREATE TABLE _version (
    vs_id                VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- ID(Key)
    vs_value             VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- 値
    vs_name              VARCHAR(60)    DEFAULT ''                    NOT NULL,      -- 名称
    vs_description       VARCHAR(80)    DEFAULT ''                    NOT NULL,      -- 説明
    PRIMARY KEY          (vs_id)
) TYPE=innodb;

-- 汎用キー値型パラメータマスター
DROP TABLE IF EXISTS _key_value;
CREATE TABLE _key_value (
    kv_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    kv_id                VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- ID(Key)
    kv_history_index     INT            DEFAULT 0                     NOT NULL,      -- 履歴管理用インデックスNo(0～)
    
    kv_value             TEXT                                         NOT NULL,      -- 値
    kv_name              VARCHAR(60)    DEFAULT ''                    NOT NULL,      -- 名称
    kv_description       VARCHAR(80)    DEFAULT ''                    NOT NULL,      -- 説明
    kv_group_id          VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- 項目グループ識別ID(任意)
    
    kv_create_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード作成者
    kv_create_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード作成日時
    kv_update_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード更新者
    kv_update_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード更新日時
    kv_deleted           BOOLEAN        DEFAULT false                 NOT NULL,      -- レコード削除状態
    PRIMARY KEY          (kv_serial),
    UNIQUE               (kv_id,        kv_history_index)
) TYPE=innodb;

-- ウィジェットパラメータ更新マスター
DROP TABLE IF EXISTS _widget_param_update;
CREATE TABLE _widget_param_update (
    wu_widget_id         VARCHAR(50)    DEFAULT ''                    NOT NULL,      -- ウィジェットID(ファイル名)
    wu_member_name       VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- オブジェクトメンバー名
    wu_key_value_id      VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- 対応する汎用キー値型パラメータマスターのID
    wu_group_id          VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- 項目グループ識別ID
    PRIMARY KEY          (wu_widget_id, wu_member_name)
) TYPE=innodb;

-- デザイン設定マスター
DROP TABLE IF EXISTS _design;
CREATE TABLE _design (
    dn_id                VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- ID(Key)
    dn_value             VARCHAR(10)    DEFAULT ''                    NOT NULL,      -- 値
    dn_name              VARCHAR(50)    DEFAULT ''                    NOT NULL,      -- 名称
    dn_description       VARCHAR(80)    DEFAULT ''                    NOT NULL,      -- 説明
    dn_index             INT            DEFAULT 0                     NOT NULL,      -- ソート用
    PRIMARY KEY          (dn_id)
) TYPE=innodb;

-- セッション管理トラン
DROP TABLE IF EXISTS _session;
CREATE TABLE _session (
    ss_id                CHAR(32)       DEFAULT ''                    NOT NULL,      -- ID
    ss_data              TEXT                                         NOT NULL,      -- 値
    ss_update_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード更新日時
    PRIMARY KEY  (ss_id)
) TYPE=innodb;

-- 管理者一時キートラン
DROP TABLE IF EXISTS _admin_key;
CREATE TABLE _admin_key (
    ak_id                CHAR(32)       DEFAULT ''                    NOT NULL,      -- ID
    ak_ip                VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- アクセス元IP(IPv6対応)
    ak_create_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード作成日時
    PRIMARY KEY  (ak_id)
) TYPE=innodb;

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

-- 追加クラスマスター
DROP TABLE IF EXISTS _addons;
CREATE TABLE _addons (
    ao_id                VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- クラスID
    ao_class_name        VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- クラス名
    ao_name              VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- 名称
    ao_description       VARCHAR(60)    DEFAULT ''                    NOT NULL,      -- 説明
    ao_index             INT            DEFAULT 0                     NOT NULL,      -- ソート用
    ao_autoload          BOOLEAN        DEFAULT true                  NOT NULL,      -- システム起動時の自動読み込み
    PRIMARY KEY          (ao_id)
) TYPE=innodb;

-- ログインユーザマスター
DROP TABLE IF EXISTS _login_user;
CREATE TABLE _login_user (
    lu_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    lu_id                INT            DEFAULT 0                     NOT NULL,      -- ログインユーザID
    lu_history_index     INT            DEFAULT 0                     NOT NULL,      -- 履歴管理用インデックスNo(0～)
    
    lu_account           VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- ログインアカウント
    lu_password          CHAR(32)       DEFAULT ''                    NOT NULL,      -- ログインパスワード(MD5)
    lu_name              VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- ユーザ名
    lu_user_type         SMALLINT       DEFAULT 0                     NOT NULL,      -- ユーザタイプ(-1=未承認ユーザ、0=仮ユーザ、10=一般ユーザ、50=システム運営者、100=システム管理者)
    lu_user_type_option  TEXT                                         NOT NULL,      -- ユーザタイプオプション(「ウィジェットID=ユーザタイプ」形式の前後「;」区切りで複数指定可)
    lu_assign            TEXT                                         NOT NULL,      -- ログイン可能な機能(2バイト文字カンマ区切り、sy=システム管理機能、ec=EC、bg=ブログ、bs=BBS、rv=予約, wk=Wiki)
    lu_admin_widget      TEXT                                         NOT NULL,      -- システム運営者が管理可能なウィジェット(「,」区切りで複数指定可)
    lu_user_status       SMALLINT       DEFAULT 0                     NOT NULL,      -- ユーザの状態
    lu_avatar            VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- アバターファイル名
    lu_email             VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- Eメールアドレス
    lu_skype_account     VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- Skypeアカウント
    lu_enable_login      BOOLEAN        DEFAULT true                  NOT NULL,      -- ログイン許可
    lu_active_start_dt   TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- ログイン可能期間(開始)
    lu_active_end_dt     TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- ログイン可能期間(終了)
    lu_tmp_password      CHAR(32)       DEFAULT ''                    NOT NULL,      -- 仮パスワード(MD5)
    lu_tmp_pwd_dt        TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- 仮パスワード発行日時
    lu_widget_id         VARCHAR(50)    DEFAULT ''                    NOT NULL,      -- レコードを登録したウィジェットID
    lu_regist_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- 登録日時

    lu_create_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード作成者
    lu_create_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード作成日時
    lu_update_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード更新者
    lu_update_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード更新日時
    lu_deleted           BOOLEAN        DEFAULT false                 NOT NULL,      -- レコード削除状態
    PRIMARY KEY          (lu_serial),
    UNIQUE               (lu_id,        lu_history_index)
) TYPE=innodb;

-- ログインユーザ情報マスター(共通的に任意で使用するユーザ情報テーブル)
DROP TABLE IF EXISTS _login_user_info;
CREATE TABLE _login_user_info (
    li_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    li_id                INT            DEFAULT 0                     NOT NULL,      -- ログインユーザID
    li_history_index     INT            DEFAULT 0                     NOT NULL,      -- 履歴管理用インデックスNo(0～)

    li_no                VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- 任意利用No
    li_family_name       VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- ユーザ名(姓)漢字
    li_first_name        VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- ユーザ名(名)漢字
    li_family_name_kana  VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- ユーザ名(姓)カナ
    li_first_name_kana   VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- ユーザ名(名)カナ
    li_gender            SMALLINT       DEFAULT 0                     NOT NULL,      -- 性別(0=未設定、1=男、2=女)
    li_birthday          DATE           DEFAULT '0000-00-00'          NOT NULL,      -- 誕生日(西暦)
    li_email             VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- Eメールアドレス
    li_mobile            VARCHAR(30)    DEFAULT ''                    NOT NULL,      -- 携帯電話
    li_zipcode           VARCHAR(10)    DEFAULT ''                    NOT NULL,      -- 郵便番号(7桁)
    li_state_id          INT            DEFAULT 0                     NOT NULL,      -- 都道府県、州(geo_zoneテーブル)
    li_address1          VARCHAR(100)   DEFAULT ''                    NOT NULL,      -- 市区町村
    li_address2          VARCHAR(60)    DEFAULT ''                    NOT NULL,      -- ビル・マンション名等
    li_phone             VARCHAR(30)    DEFAULT ''                    NOT NULL,      -- 電話番号
    li_fax               VARCHAR(30)    DEFAULT ''                    NOT NULL,      -- FAX
    li_country_id        VARCHAR(3)     DEFAULT ''                    NOT NULL,      -- 国ID
    li_profile           TEXT                                         NOT NULL,      -- 自己紹介

    li_create_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード作成者
    li_create_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード作成日時
    li_update_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード更新者
    li_update_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード更新日時
    li_deleted           BOOLEAN        DEFAULT false                 NOT NULL,      -- レコード削除状態
    PRIMARY KEY          (li_serial),
    UNIQUE               (li_id,        li_history_index)
) TYPE=innodb;

-- ユーザグループマスター
DROP TABLE IF EXISTS _user_group;
CREATE TABLE _user_group (
    ug_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    ug_id                VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- グループID
    ug_language_id       VARCHAR(2)     DEFAULT ''                    NOT NULL,      -- 言語ID
    ug_history_index     INT            DEFAULT 0                     NOT NULL,      -- 履歴管理用インデックスNo(0～)

    ug_name              VARCHAR(30)    DEFAULT ''                    NOT NULL,      -- グループ名称
    ug_sort_order        INT            DEFAULT 0                     NOT NULL,      -- ソート用

    ug_create_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード作成者
    ug_create_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード作成日時
    ug_update_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード更新者
    ug_update_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード更新日時
    ug_deleted           BOOLEAN        DEFAULT false                 NOT NULL,      -- レコード削除状態
    PRIMARY KEY          (ug_serial),
    UNIQUE               (ug_id,        ug_language_id,               ug_history_index)
) TYPE=innodb;

-- ユーザとユーザグループの対応付けマスター
DROP TABLE IF EXISTS _user_with_group;
CREATE TABLE _user_with_group (
    uw_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    uw_user_serial       INT            DEFAULT 0                     NOT NULL,      -- ログインユーザシリアル番号
    uw_index             INT            DEFAULT 0                     NOT NULL,      -- インデックス番号(0～)
    uw_group_id          VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- ユーザグループID
    PRIMARY KEY          (uw_serial),
    UNIQUE               (uw_user_serial,    uw_index)
) TYPE=innodb;

-- ユーザログイントラン
DROP TABLE IF EXISTS _login_log;
CREATE TABLE _login_log (
    ll_user_id           INT            DEFAULT 0                     NOT NULL,      -- ユーザID
    ll_login_count       INT            DEFAULT 0                     NOT NULL,      -- ログイン回数
    ll_access_log_serial INT            DEFAULT 0                     NOT NULL,      -- アクセスログシリアル番号
    ll_pre_login_dt      TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- 前回ログイン日時
    ll_last_login_dt     TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- 最終ログイン日時
    PRIMARY KEY  (ll_user_id)
) TYPE=innodb;

-- ユーザログインエラートラン(廃止予定)
DROP TABLE IF EXISTS _login_err_log;
CREATE TABLE _login_err_log (
    le_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    le_account           VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- ログインアカウント
    le_ip                VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- アクセス元IP(IPv6対応)
    le_access_log_serial INT            DEFAULT 0                     NOT NULL,      -- アクセスログシリアル番号
    PRIMARY KEY          (le_serial)
) TYPE=innodb;

-- ユーザアクセスログトラン
DROP TABLE IF EXISTS _access_log;
CREATE TABLE _access_log (
    al_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    al_user_id           INT            DEFAULT 0                     NOT NULL,      -- ログインユーザID(0=不明)
    al_session           CHAR(32)       DEFAULT ''                    NOT NULL,      -- セッションID
    al_cookie_value      CHAR(32)       DEFAULT ''                    NOT NULL,      -- アクセス管理用クッキーの値
    al_device_id         VARCHAR(32)    DEFAULT ''                    NOT NULL,      -- 端末ID(携帯のときの端末ID)
    al_ip                VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- アクセス元IP(IPv6対応)
    al_method            VARCHAR(4)     DEFAULT ''                    NOT NULL,      -- アクセスメソッド
    al_uri               TEXT                                         NOT NULL,      -- アクセスURI
    al_referer           TEXT                                         NOT NULL,      -- リファラー
    al_request           TEXT                                         NOT NULL,      -- リクエストパラメータ
    al_user_agent        TEXT                                         NOT NULL,      -- アクセスプログラム
    al_accept_language   VARCHAR(50)    DEFAULT ''                    NOT NULL,      -- クライアントの認識可能言語
    al_path              VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- アクセスポイントパス
    al_cookie            BOOLEAN        DEFAULT false                 NOT NULL,      -- クッキーがあるかどうか
    al_crawler           BOOLEAN        DEFAULT false                 NOT NULL,      -- クローラかどうか
    al_is_first          BOOLEAN        DEFAULT false                 NOT NULL,      -- 最初のアクセスかどうか(クッキー値でチェック)
    al_analyzed          BOOLEAN        DEFAULT false                 NOT NULL,      -- ログ解析完了かどうか
    al_dt                TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- アクセス日時
    PRIMARY KEY          (al_serial)
) TYPE=innodb;

-- ウィジェット実行ログトラン
DROP TABLE IF EXISTS _widget_log;
CREATE TABLE _widget_log (
    wl_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    wl_widget_id         VARCHAR(50)    DEFAULT ''                    NOT NULL,      -- ウィジェットID(ファイル名)
    wl_type              INT            DEFAULT 0                     NOT NULL,      -- 実行タイプ(0=ページからの実行、1=単体実行)
    wl_cmd               VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- 実行コマンド
    wl_message           TEXT                                         NOT NULL,      -- 実行メッセージ
    wl_access_log_serial INT            DEFAULT 0                     NOT NULL,      -- アクセスログシリアル番号
    wl_dt                TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- 記録日時
    PRIMARY KEY          (wl_serial)
) TYPE=innodb;

-- ダウンロード実行ログトラン
DROP TABLE IF EXISTS _download_log;
CREATE TABLE _download_log (
    dl_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    dl_user_id           INT            DEFAULT 0                     NOT NULL,      -- ユーザID
    dl_content_type      VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- コンテンツ種別
    dl_content_id        VARCHAR(32)    DEFAULT ''                    NOT NULL,      -- コンテンツID
    dl_access_log_serial INT            DEFAULT 0                     NOT NULL,      -- アクセスログシリアル番号
    dl_dt                TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- 記録日時
    PRIMARY KEY          (dl_serial)
) TYPE=innodb;

-- 検索キーワードトラン
DROP TABLE IF EXISTS _search_word;
CREATE TABLE _search_word (
    sw_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    sw_word              TEXT                                         NOT NULL,      -- 検索キーワード
    sw_basic_word        TEXT                                         NOT NULL,      -- 比較用基本ワード
    sw_path              VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- アクセスポイントパス
    sw_widget_id         VARCHAR(50)    DEFAULT ''                    NOT NULL,      -- ウィジェットID(ファイル名)
    sw_device_type       INT            DEFAULT 0                     NOT NULL,      -- 端末タイプ(0=PC、1=携帯、2=スマートフォン)
    sw_client_id         VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- PCの場合はアクセス管理用クッキー値。携帯の場合は端末ID「XX-xxxxxx」(XX=キャリアDC,AU,SB、xxxxxx=端末ID)。
    sw_access_log_serial INT            DEFAULT 0                     NOT NULL,      -- アクセスログシリアル番号
    sw_dt                TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- 記録日時
    PRIMARY KEY          (sw_serial)
) TYPE=innodb;

-- クライアントIPアクセス制御マスター
DROP TABLE IF EXISTS _access_ip;
CREATE TABLE _access_ip (
    ai_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    ai_type              INT            DEFAULT 0                     NOT NULL,      -- アクセス制御タイプ(0=未設定、1=管理機能アクセス許可、2=アクセス拒否、3=登録許可)
    ai_ip                VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- アクセス元IP(IPv6対応)
    ai_ip_mask           VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- IPマスク値
    ai_server_id         CHAR(32)       DEFAULT ''                    NOT NULL,      -- サーバ識別ID
    
    ai_param             TEXT                                         NOT NULL,      -- その他パラメータ
    PRIMARY KEY          (ai_serial),
    UNIQUE               (ai_type, ai_ip, ai_ip_mask, ai_server_id)
) TYPE=innodb;

-- ナビゲーション項目マスター
DROP TABLE IF EXISTS _nav_item;
CREATE TABLE _nav_item (
    ni_id                INT            DEFAULT 0                     NOT NULL,      -- 項目ID
    ni_parent_id         INT            DEFAULT 0                     NOT NULL,      -- 親項目ID(親がないときは0)
    ni_index             INT            DEFAULT 0                     NOT NULL,      -- 表示順(0～)、ni_parent_id=0のときは親間の表示順
    ni_nav_id            VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- ナビゲーション種別識別ID
    ni_task_id           VARCHAR(70)    DEFAULT ''                    NOT NULL,      -- 起動タスクID、「_」で始まるときはリンクなし
    ni_param             VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- 追加パラメータ
    ni_group_id          VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- 項目グループ識別ID
    ni_view_control      INT            DEFAULT 0                     NOT NULL,      -- 表示位置制御、1のとき表示位置変更
    ni_name              VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- 名前
    ni_help_title        TEXT                                         NOT NULL,      -- ヘルプタイトル
    ni_help_body         TEXT                                         NOT NULL,      -- ヘルプ本文
    ni_visible           BOOLEAN        DEFAULT true                  NOT NULL,      -- 表示するかどうか
    PRIMARY KEY          (ni_id),
    UNIQUE               (ni_nav_id,    ni_task_id,                   ni_param)
) TYPE=innodb;

-- 添付ファイルマスター
DROP TABLE IF EXISTS _attach_file;
CREATE TABLE _attach_file (
    af_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    af_content_type      VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- コンテンツ種別
    af_content_id        VARCHAR(32)    DEFAULT ''                    NOT NULL,      -- コンテンツID
    af_content_serial    INT            DEFAULT 0                     NOT NULL,      -- 対応コンテンツシリアル番号
    af_index             INT            DEFAULT 0                     NOT NULL,      -- インデックス番号(0～)
    af_client_id         CHAR(32)       DEFAULT ''                    NOT NULL,      -- クライアントID
    
    af_file_id           CHAR(32)       DEFAULT ''                    NOT NULL,      -- ファイル識別ID(システムでユニークになるように設定)
    af_filename          VARCHAR(256)   DEFAULT ''                    NOT NULL,      -- ダウンロード用ファイル名
    af_title             VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- タイトル
    af_desc              TEXT                                         NOT NULL,      -- 説明
    af_file_type         VARCHAR(30)    DEFAULT ''                    NOT NULL,      -- ファイルタイプ
    af_original_filename VARCHAR(256)   DEFAULT ''                    NOT NULL,      -- 元のファイル名
    af_file_size         INT            DEFAULT 0                     NOT NULL,      -- ファイルサイズ(バイト)
    af_file_dt           TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- ファイル作成日時
    af_file_deleted_dt   TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- ファイル削除日時
    af_file_deleted      BOOLEAN        DEFAULT false                 NOT NULL,      -- アップロードファイルの削除状態
    af_upload_log_serial INT            DEFAULT 0                     NOT NULL,      -- アップロード時のアクセスログシリアル番号
    af_delete_log_serial INT            DEFAULT 0                     NOT NULL,      -- ファイル削除時のアクセスログシリアル番号
    PRIMARY KEY          (af_serial),
    UNIQUE               (af_content_type,      af_content_id,        af_content_serial, af_index, af_client_id)
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

-- コンテンツ参照トラン
DROP TABLE IF EXISTS _view_count;
CREATE TABLE _view_count (
    vc_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    vc_type_id           VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- コンテンツタイプ(「a:c-h」a=アクセスポイント,c=コンテンツタイプ,h=参照方法。「a:」「-h」省略可。)
    vc_content_serial    INT            DEFAULT 0                     NOT NULL,      -- コンテンツシリアル番号
    vc_content_id        VARCHAR(50)    DEFAULT ''                    NOT NULL,      -- コンテンツ識別用のID
    vc_date              DATE           DEFAULT '0000-00-00'          NOT NULL,      -- 日付
    vc_hour              SMALLINT       DEFAULT 0                     NOT NULL,      -- 時間
    vc_count             INT            DEFAULT 0                     NOT NULL,      -- 参照数
    PRIMARY KEY          (vc_serial),
    UNIQUE               (vc_type_id,   vc_content_serial,            vc_content_id,    vc_date,       vc_hour)
) TYPE=innodb;

-- テンプレート表示位置マスター
DROP TABLE IF EXISTS _template_position;
CREATE TABLE _template_position (
    tp_id                VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- テンプレートID
    tp_name              VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- テンプレート名称
    tp_description       VARCHAR(100)   DEFAULT ''                    NOT NULL,      -- 説明
    tp_sort_order        INT            DEFAULT 0                     NOT NULL,      -- ソート順
    tp_available         BOOLEAN        DEFAULT true                  NOT NULL,      -- メニューから選択可能かどうか
    PRIMARY KEY  (tp_id)
) TYPE=innodb;

-- テンプレート情報マスター
DROP TABLE IF EXISTS _templates;
CREATE TABLE _templates (
    tm_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    tm_id                VARCHAR(50)    DEFAULT ''                    NOT NULL,      -- テンプレートID
    tm_history_index     INT            DEFAULT 0                     NOT NULL,      -- 履歴管理用インデックスNo(0～)
    
    tm_type              INT            DEFAULT 0                     NOT NULL,      -- テンプレート種別(0=デフォルトテンプレート(Joomla!v1.0)、1=Joomla!v1.5、2=Joomla!v2.5)
    tm_device_type       INT            DEFAULT 0                     NOT NULL,      -- 端末タイプ(0=PC、1=携帯、2=スマートフォン)
    tm_language          TEXT                                         NOT NULL,      -- 対応言語ID(「,」区切りで複数指定可)
    tm_name              VARCHAR(50)    DEFAULT ''                    NOT NULL,      -- テンプレート名
    tm_description       VARCHAR(100)   DEFAULT ''                    NOT NULL,      -- 説明
    tm_url               TEXT                                         NOT NULL,      -- 取得先URL
    tm_joomla_params     TEXT                                         NOT NULL,      -- joomla!用パラメータ
    tm_mobile            BOOLEAN        DEFAULT false                 NOT NULL,      -- 携帯対応かどうか
    tm_available         BOOLEAN        DEFAULT true                  NOT NULL,      -- メニューから選択可能かどうか
    tm_clean_type        INT            DEFAULT 0                     NOT NULL,      -- 出力のクリーン処理(0=処理なし,0以外=クリーン処理実行)
    
    tm_create_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード作成者
    tm_create_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード作成日時
    tm_update_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード更新者
    tm_update_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード更新日時
    tm_deleted           BOOLEAN        DEFAULT false                 NOT NULL,      -- レコード削除状態
    PRIMARY KEY          (tm_serial),
    UNIQUE               (tm_id,        tm_history_index)
) TYPE=innodb;

-- ウィジェット情報マスター
DROP TABLE IF EXISTS _widgets;
CREATE TABLE _widgets (
    wd_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    wd_id                VARCHAR(50)    DEFAULT ''                    NOT NULL,      -- ウィジェットID(ファイル名)
    wd_history_index     INT            DEFAULT 0                     NOT NULL,      -- 履歴管理用インデックスNo(0～)
    
    wd_language          TEXT                                         NOT NULL,      -- 対応言語ID(「,」区切りで複数指定可)
    wd_name              VARCHAR(50)    DEFAULT ''                    NOT NULL,      -- ウィジェット名称
    wd_type              VARCHAR(10)    DEFAULT ''                    NOT NULL,      -- ウィジェット種別(menu=メニュー,content=コンテンツ編集)
    wd_content_type      VARCHAR(10)    DEFAULT ''                    NOT NULL,      -- 必要とするページのコンテンツ種別
    wd_device_type       INT            DEFAULT 0                     NOT NULL,      -- 端末タイプ(0=PC、1=携帯、2=スマートフォン)
    wd_version           VARCHAR(10)    DEFAULT ''                    NOT NULL,      -- バージョン文字列
    wd_fingerprint       CHAR(32)       DEFAULT ''                    NOT NULL,      -- ソースコードレベルでウィジェットを識別するためのID
    wd_group_id          VARCHAR(10)    DEFAULT ''                    NOT NULL,      -- ウィジェットグループ(管理用)
    wd_compatible_id     VARCHAR(50)    DEFAULT ''                    NOT NULL,      -- 互換ウィジェットID
    wd_parent_id         VARCHAR(50)    DEFAULT ''                    NOT NULL,      -- 親ウィジェットID(ファイル名)
    wd_joomla_class      VARCHAR(50)    DEFAULT ''                    NOT NULL,      -- Joomla!テンプレート用のクラス名
    wd_suffix            VARCHAR(4)     DEFAULT ''                    NOT NULL,      -- HTMLタグのクラス名に付けるサフィックス文字列
    wd_params            VARCHAR(100)   DEFAULT ''                    NOT NULL,      -- 各種パラメータ
    wd_author            VARCHAR(30)    DEFAULT ''                    NOT NULL,      -- 作者名
    wd_copyright         VARCHAR(30)    DEFAULT ''                    NOT NULL,      -- 著作権
    wd_license           VARCHAR(30)    DEFAULT ''                    NOT NULL,      -- ライセンス
    wd_license_type      INT            DEFAULT 0                     NOT NULL,      -- ライセンスタイプ(0=オープンソース、1=商用)
    wd_official_level    INT            DEFAULT 0                     NOT NULL,      -- 公認レベル(0=非公認、1=準公認、10=正規公認)
    wd_status            INT            DEFAULT 0                     NOT NULL,      -- 状態(0=通常,1=テスト中,-1=廃止予定,-10=廃止)
    wd_cache_type        INT            DEFAULT 0                     NOT NULL,      -- キャッシュタイプ(0=不可、1=可、2=非ログイン時可, 3=ページキャッシュのみ可)
    wd_cache_lifetime    INT            DEFAULT 0                     NOT NULL,      -- キャッシュの保持時間(分)
    wd_view_control_type INT            DEFAULT 0                     NOT NULL,      -- 表示出力の制御タイプ(-1=固定、0=可変、1=ウィジェットパラメータ可変、2=URLパラメータ可変)
    wd_description       TEXT                                         NOT NULL,      -- 説明
    wd_url               TEXT                                         NOT NULL,      -- 取得先URL
    wd_add_script_lib    TEXT                                         NOT NULL,      -- 追加する共通スクリプトライブラリ(ライブラリ名で指定、「,」区切りで複数指定可)
    wd_add_scripts       TEXT                                         NOT NULL,      -- 追加スクリプトファイル(相対パス表記、「,」区切りで複数指定可)
    wd_add_css           TEXT                                         NOT NULL,      -- 追加CSSファイル(相対パス表記、「,」区切りで複数指定可)
    wd_add_script_lib_a  TEXT                                         NOT NULL,      -- (管理機能用)追加する共通スクリプトライブラリ(ライブラリ名で指定、「,」区切りで複数指定可)
    wd_add_scripts_a     TEXT                                         NOT NULL,      -- (管理機能用)追加スクリプトファイル(相対パス表記、「,」区切りで複数指定可)
    wd_add_css_a         TEXT                                         NOT NULL,      -- (管理機能用)追加CSSファイル(相対パス表記、「,」区切りで複数指定可)
    wd_admin             BOOLEAN        DEFAULT false                 NOT NULL,      -- 管理用ウィジェットかどうか
    wd_mobile            BOOLEAN        DEFAULT false                 NOT NULL,      -- 携帯対応かどうか
    wd_show_name         BOOLEAN        DEFAULT false                 NOT NULL,      -- ウィジェット名称を表示するかどうか
    wd_read_scripts      BOOLEAN        DEFAULT false                 NOT NULL,      -- スクリプトディレクトリを自動読み込みするかどうか(廃止予定)
    wd_read_css          BOOLEAN        DEFAULT false                 NOT NULL,      -- cssディレクトリを自動読み込みするかどうか(廃止予定)
    wd_use_ajax          BOOLEAN        DEFAULT false                 NOT NULL,      -- Ajax共通ライブラリを読み込むかどうか
    wd_active            BOOLEAN        DEFAULT true                  NOT NULL,      -- 一般ユーザが実行可能かどうか
    wd_available         BOOLEAN        DEFAULT true                  NOT NULL,      -- メニューから選択可能かどうか
    wd_editable          BOOLEAN        DEFAULT true                  NOT NULL,      -- データ編集可能かどうか
    wd_edit_content      BOOLEAN        DEFAULT false                 NOT NULL,      -- 主要コンテンツ編集可能かどうか
    wd_has_admin         BOOLEAN        DEFAULT false                 NOT NULL,      -- 管理画面があるかどうか
    wd_has_log           BOOLEAN        DEFAULT false                 NOT NULL,      -- ログ参照画面があるかどうか
    wd_enable_operation  BOOLEAN        DEFAULT false                 NOT NULL,      -- 単体起動可能かどうか
    wd_use_instance_def  BOOLEAN        DEFAULT false                 NOT NULL,      -- インスタンス定義が必要かどうか
    wd_initialized       BOOLEAN        DEFAULT false                 NOT NULL,      -- 初期化完了かどうか
    wd_use_cache         BOOLEAN        DEFAULT false                 NOT NULL,      -- キャッシュ機能を使用するかどうか
    wd_has_rss           BOOLEAN        DEFAULT false                 NOT NULL,      -- RSS機能があるかどうか
    wd_sort_order        INT            DEFAULT 0                     NOT NULL,      -- ソート順
--    wd_cache_interval    INT            DEFAULT 0                     NOT NULL,      -- キャッシュの更新時間(分)
    wd_launch_index      INT            DEFAULT 0                     NOT NULL,      -- 遅延実行制御が必要な場合の実行順(0=未設定、0以上=実行順)
    wd_release_dt        TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- リリース日時
    wd_install_dt        TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- インストール日時
    
    wd_index_file        VARCHAR(50)    DEFAULT ''                    NOT NULL,      -- 起動クラスのファイル名
    wd_index_class       VARCHAR(200)   DEFAULT ''                    NOT NULL,      -- 起動クラス名
    wd_admin_file        VARCHAR(50)    DEFAULT ''                    NOT NULL,      -- 管理機能起動クラスのファイル名
    wd_admin_class       VARCHAR(200)   DEFAULT ''                    NOT NULL,      -- 管理機能起動クラス名
    wd_db                VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- 対応DB種(mysql,pgsql等を「,」区切りで指定)
    wd_table_access_type INT            DEFAULT 0                     NOT NULL,      -- テーブルのアクセス範囲(0=テーブル未使用、1=共通テーブルのみ、2=独自テーブル)
	
    wd_checked_out       BOOLEAN        DEFAULT false                 NOT NULL,      -- チェックアウト中かどうか
    wd_checked_out_dt    TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- チェックアウト日時
    wd_create_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード作成者
    wd_create_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード作成日時
    wd_update_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード更新者
    wd_update_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード更新日時
    wd_deleted           BOOLEAN        DEFAULT false                 NOT NULL,      -- レコード削除状態
    PRIMARY KEY          (wd_serial),
    UNIQUE               (wd_id,        wd_history_index)
) TYPE=innodb;

-- ウィジェットパラメータマスター
DROP TABLE IF EXISTS _widget_param;
CREATE TABLE _widget_param (
    wp_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    wp_id                VARCHAR(50)    DEFAULT ''                    NOT NULL,      -- ウィジェットID
    wp_config_id         INT            DEFAULT 0                     NOT NULL,      -- ウィジェット定義ID
    wp_history_index     INT            DEFAULT 0                     NOT NULL,      -- 履歴管理用インデックスNo(0～)
    
    wp_param             TEXT                                         NOT NULL,      -- パラメータオブジェクトをシリアライズしたもの
    wp_cache_html        TEXT                                         NOT NULL,      -- キャッシュデータ
    wp_cache_title       TEXT                                         NOT NULL,      -- キャッシュヘッダタイトル(削除予定)
    wp_meta_title        TEXT                                         NOT NULL,      -- METAタグ、タイトル
    wp_meta_description  TEXT                                         NOT NULL,      -- METAタグ、ページ要約
    wp_meta_keywords     TEXT                                         NOT NULL,      -- METAタグ、検索用キーワード
    
    wp_cache_user_id     INT            DEFAULT 0                     NOT NULL,      -- キャッシュ更新者
    wp_cache_update_dt   TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- キャッシュ更新日時
    wp_checked_out       BOOLEAN        DEFAULT false                 NOT NULL,      -- チェックアウト中かどうか
    wp_checked_out_dt    TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- チェックアウト日時
    wp_create_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード作成者
    wp_create_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード作成日時
    wp_update_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード更新者
    wp_update_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード更新日時
    wp_deleted           BOOLEAN        DEFAULT false                 NOT NULL,      -- レコード削除状態
    PRIMARY KEY          (wp_serial),
    UNIQUE               (wp_id,        wp_config_id, wp_history_index)
) TYPE=innodb;

-- インナーウィジェット情報マスター
DROP TABLE IF EXISTS _iwidgets;
CREATE TABLE _iwidgets (
    iw_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    iw_widget_id         VARCHAR(50)    DEFAULT ''                    NOT NULL,      -- ウィジェットID(ファイル名)
    iw_id                VARCHAR(50)    DEFAULT ''                    NOT NULL,      -- インナーウィジェットID(ファイル名)
    iw_history_index     INT            DEFAULT 0                     NOT NULL,      -- 履歴管理用インデックスNo(0～)
    
    iw_language          TEXT                                         NOT NULL,      -- 対応言語ID(「,」区切りで複数指定可)
    iw_name              VARCHAR(50)    DEFAULT ''                    NOT NULL,      -- ウィジェット名称
    iw_type              VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- ウィジェット種別(ウィジェットの種類を示す文字コード)
    iw_version           VARCHAR(10)    DEFAULT ''                    NOT NULL,      -- バージョン文字列
    iw_fingerprint       CHAR(32)       DEFAULT ''                    NOT NULL,      -- ソースコードレベルでウィジェットを識別するためのID
    iw_author            VARCHAR(30)    DEFAULT ''                    NOT NULL,      -- 作者名
    iw_copyright         VARCHAR(30)    DEFAULT ''                    NOT NULL,      -- 著作権
    iw_license           VARCHAR(30)    DEFAULT ''                    NOT NULL,      -- ライセンス
    iw_license_type      INT            DEFAULT 0                     NOT NULL,      -- ライセンスタイプ(0=オープンソース、1=商用)
    iw_official_level    INT            DEFAULT 0                     NOT NULL,      -- 公認レベル(0=非公認、1=準公認、10=正規公認)
    iw_description       VARCHAR(100)   DEFAULT ''                    NOT NULL,      -- 説明
    iw_url               TEXT                                         NOT NULL,      -- 取得先URL
    iw_online            BOOLEAN        DEFAULT false                 NOT NULL,      -- オンライン接続があるかどうか
    iw_install_dt        TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- インストール日時
    
    iw_index_file        VARCHAR(50)    DEFAULT ''                    NOT NULL,      -- 起動クラスのファイル名
    iw_index_class       VARCHAR(200)   DEFAULT ''                    NOT NULL,      -- 起動クラス名
    iw_admin_file        VARCHAR(50)    DEFAULT ''                    NOT NULL,      -- 管理機能起動クラスのファイル名
    iw_admin_class       VARCHAR(200)   DEFAULT ''                    NOT NULL,      -- 管理機能起動クラス名
    iw_db                VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- 対応DB種(mysql,pgsql等を「,」区切りで指定)
	
    iw_checked_out       BOOLEAN        DEFAULT false                 NOT NULL,      -- チェックアウト中かどうか
    iw_checked_out_dt    TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- チェックアウト日時
    iw_create_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード作成者
    iw_create_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード作成日時
    iw_update_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード更新者
    iw_update_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード更新日時
    iw_deleted           BOOLEAN        DEFAULT false                 NOT NULL,      -- レコード削除状態
    PRIMARY KEY          (iw_serial),
    UNIQUE               (iw_widget_id, iw_id,        iw_history_index)
) TYPE=innodb;

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

-- ページIDマスター
DROP TABLE IF EXISTS _page_id;
CREATE TABLE _page_id (
    pg_id                VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- ページメインID、ページサブID
    pg_type              INT            DEFAULT 0                     NOT NULL,      -- ページID種別(0=ページメインID,1=ページサブID)
    
    pg_default_sub_id    VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- デフォルトのサブページID(ページID種別がページメインIDとき使用)
    pg_url               TEXT                                         NOT NULL,      -- アクセスURL(ページID種別がページメインIDとき使用)
    pg_path              VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- アクセスポイントパス(ページID種別がページメインIDとき使用)
    pg_class             VARCHAR(50)    DEFAULT ''                    NOT NULL,      -- 起動クラス名(ページID種別がページメインIDとき使用)
    pg_device_type       INT            DEFAULT 0                     NOT NULL,      -- 端末タイプ(0=PC、1=携帯、2=スマートフォン)
    pg_name              VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- ページ名称
    pg_description       VARCHAR(60)    DEFAULT ''                    NOT NULL,      -- 説明
    pg_priority          INT            DEFAULT 0                     NOT NULL,      -- 優先度
    pg_mobile            BOOLEAN        DEFAULT false                 NOT NULL,      -- 携帯対応かどうか(ページID種別がページメインIDとき使用)
    pg_active            BOOLEAN        DEFAULT true                  NOT NULL,      -- 有効かどうか
    pg_visible           BOOLEAN        DEFAULT true                  NOT NULL,      -- 表示可能かどうか
    pg_editable          BOOLEAN        DEFAULT true                  NOT NULL,      -- データ編集可能かどうか
    pg_admin_menu        BOOLEAN        DEFAULT false                 NOT NULL,      -- 管理メニューを表示するかどうか(ページID種別がアクセスポイント時。初期値。)
    pg_analytics         BOOLEAN        DEFAULT false                 NOT NULL,      -- アクセス解析対象かどうか(ページID種別がアクセスポイント時)
    PRIMARY KEY  (pg_id, pg_type)
) TYPE=innodb;

-- ページ情報マスター
-- 言語IDが空以外の場合は個別項目のみを使用
DROP TABLE IF EXISTS _page_info;
CREATE TABLE _page_info (
    pn_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    pn_id                VARCHAR(50)    DEFAULT ''                    NOT NULL,      -- ページID
    pn_sub_id            VARCHAR(50)    DEFAULT ''                    NOT NULL,      -- ページ補助ID
    pn_language_id       VARCHAR(2)     DEFAULT ''                    NOT NULL,      -- 言語ID(空=デフォルト)
    pn_history_index     INT            DEFAULT 0                     NOT NULL,      -- 履歴管理用インデックスNo(0～)

    pn_name              VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- ページ名
    pn_template_id       VARCHAR(50)    DEFAULT ''                    NOT NULL,      -- テンプレートID(個別)
    pn_layout_id         VARCHAR(50)    DEFAULT ''                    NOT NULL,      -- レイアウトID(個別)
    pn_meta_title        TEXT                                         NOT NULL,      -- METAタグ、タイトル(個別)
    pn_meta_description  TEXT                                         NOT NULL,      -- METAタグ、ページ要約(個別)
    pn_meta_keywords     TEXT                                         NOT NULL,      -- METAタグ、検索用キーワード(個別)
    pn_head_others       TEXT                                         NOT NULL,      -- HEADタグその他
    pn_content_type      VARCHAR(10)    DEFAULT ''                    NOT NULL,      -- メインコンテンツの種別(content=汎用コンテンツ、product=製品、bbs=掲示板、blog=ブログ、wiki=wikiコンテンツ)
    pn_auth_type         INT            DEFAULT 0                     NOT NULL,      -- アクセス制御タイプ(0=管理者のみ、1=制限なし、2=ログインユーザ)
    pn_user_limited      BOOLEAN        DEFAULT false                 NOT NULL,      -- アクセス可能ユーザを制限
    pn_use_ssl           BOOLEAN        DEFAULT false                 NOT NULL,      -- SSLを使用するかどうか
    
    pn_checked_out       BOOLEAN        DEFAULT false                 NOT NULL,      -- チェックアウト中かどうか
    pn_checked_out_dt    TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- チェックアウト日時
    pn_create_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード作成者
    pn_create_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード作成日時
    pn_update_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード更新者
    pn_update_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード更新日時
    pn_deleted           BOOLEAN        DEFAULT false                 NOT NULL,      -- レコード削除状態
    PRIMARY KEY          (pn_serial),
    UNIQUE               (pn_id,        pn_sub_id,   pn_language_id,  pn_history_index)
) TYPE=innodb;

-- ページ定義マスター
DROP TABLE IF EXISTS _page_def;
CREATE TABLE _page_def (
    pd_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    pd_id                VARCHAR(50)    DEFAULT ''                    NOT NULL,      -- ページID(ファイル名)
    pd_sub_id            VARCHAR(50)    DEFAULT ''                    NOT NULL,      -- ページ補助ID
    pd_set_id            INT            DEFAULT 0                     NOT NULL,      -- 定義セットID
    
    pd_position_id       VARCHAR(50)    DEFAULT ''                    NOT NULL,      -- 表示位置ID
    pd_index             INT            DEFAULT 0                     NOT NULL,      -- 表示順(1～)
    pd_widget_id         VARCHAR(50)    DEFAULT ''                    NOT NULL,      -- 表示するウィジェットID
    pd_config_id         INT            DEFAULT 0                     NOT NULL,      -- ウィジェット定義ID
    pd_config_name       VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- ウィジェット定義名
    pd_menu_id           VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- メニューID
    pd_suffix            VARCHAR(5)     DEFAULT ''                    NOT NULL,      -- インスタンスを区別するためのサフィックス文字列
    pd_title             VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- タイトル
    pd_style             TEXT                                         NOT NULL,      -- HTMLスタイル属性
    pd_except_sub_id     TEXT                                         NOT NULL,      -- 共通時例外ページサブID(「,」区切りで複数指定可)
    pd_view_control_type INT            DEFAULT 0                     NOT NULL,      -- 表示出力の制御タイプ(0=常時表示、1=ログイン時のみ表示、2=非ログイン時のみ表示)
    pd_view_option       TEXT                                         NOT NULL,      -- 表示オプション
    pd_edit_status       SMALLINT       DEFAULT 0                     NOT NULL,      -- 編集状態(0=編集完了、1=編集中)
    pd_top_content       TEXT                                         NOT NULL,      -- 上部コンテンツ
    pd_bottom_content    TEXT                                         NOT NULL,      -- 下部コンテンツ
    pd_show_readmore     BOOLEAN        DEFAULT false                 NOT NULL,      -- 「もっと読む」ボタンを表示するかどうか
    pd_readmore_title    VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- 「もっと読む」タイトル
    pd_readmore_url      TEXT                                         NOT NULL,      -- 「もっと読む」リンク先URL
    pd_visible           BOOLEAN        DEFAULT true                  NOT NULL,      -- ウィジェットを表示するかどうか
    pd_editable          BOOLEAN        DEFAULT true                  NOT NULL,      -- データ編集可能かどうか
    pd_title_visible     BOOLEAN        DEFAULT true                  NOT NULL,      -- タイトルを表示するかどうか
    pd_use_render        BOOLEAN        DEFAULT true                  NOT NULL,      -- Joomla!の描画処理を使用するかどうか
    pd_password          CHAR(32)       DEFAULT ''                    NOT NULL,      -- アクセス制限パスワード(MD5)
    pd_cache_dt          TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- キャッシュ更新日時
    pd_active_start_dt   TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- アクセス可能期間(開始)
    pd_active_end_dt     TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- アクセス可能期間(終了)
    
    pd_update_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード更新者
    pd_update_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード更新日時
    PRIMARY KEY          (pd_serial)
) TYPE=innodb;

-- ページ定義セットマスター
DROP TABLE IF EXISTS _page_def_set;
CREATE TABLE _page_def_set (
    ds_id                INT            DEFAULT 0                     NOT NULL,      -- 定義セットID
    
    ds_name              VARCHAR(50)    DEFAULT ''                    NOT NULL,      -- 定義セット名称
    ds_user_type         INT            DEFAULT 0                     NOT NULL,      -- ユーザタイプ
    
    ds_create_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード作成者
    ds_create_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード作成日時
    PRIMARY KEY          (ds_id)
) TYPE=innodb;

-- キャッシュトラン
DROP TABLE IF EXISTS _cache;
CREATE TABLE _cache (
    ca_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    ca_widget_id         VARCHAR(50)    DEFAULT ''                    NOT NULL,      -- ウィジェットID(ファイル名)
    ca_url               VARCHAR(191)   DEFAULT ''                    NOT NULL,      -- アクセスURL
    
    ca_page_id           VARCHAR(50)    DEFAULT ''                    NOT NULL,      -- ページID
    ca_page_sub_id       VARCHAR(50)    DEFAULT ''                    NOT NULL,      -- ページ補助ID
    ca_html              TEXT                                         NOT NULL,      -- キャッシュデータ
    ca_meta_title        TEXT                                         NOT NULL,      -- METAタグ、タイトル
    ca_meta_description  TEXT                                         NOT NULL,      -- METAタグ、ページ要約
    ca_meta_keywords     TEXT                                         NOT NULL,      -- METAタグ、検索用キーワード

    ca_update_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード更新者
    ca_update_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード更新日時
    PRIMARY KEY          (ca_serial),
    UNIQUE               (ca_widget_id, ca_url)
) TYPE=innodb;

-- サイト定義マスター
DROP TABLE IF EXISTS _site_def;
CREATE TABLE _site_def (
    sd_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    sd_id                VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- 定義項目ID
    sd_language_id       VARCHAR(2)     DEFAULT ''                    NOT NULL,      -- 言語ID
    sd_history_index     INT            DEFAULT 0                     NOT NULL,      -- 履歴管理用インデックスNo(0～)
    
    sd_value             TEXT                                         NOT NULL,      -- 値
    sd_name              VARCHAR(60)    DEFAULT ''                    NOT NULL,      -- 名称
    sd_description       VARCHAR(80)    DEFAULT ''                    NOT NULL,      -- 説明
    sd_index             INT            DEFAULT 0                     NOT NULL,      -- ソート用

    sd_create_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード作成者
    sd_create_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード作成日時
    sd_update_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード更新者
    sd_update_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード更新日時
    sd_deleted           BOOLEAN        DEFAULT false                 NOT NULL,      -- レコード削除状態
    PRIMARY KEY          (sd_serial),
    UNIQUE               (sd_id,        sd_language_id,               sd_history_index)
) TYPE=innodb;

-- メール送信ログトラン
DROP TABLE IF EXISTS _mail_send_log;
CREATE TABLE _mail_send_log (
    ms_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    ms_type              INT            DEFAULT 0                     NOT NULL,      -- メール種別(0=未設定、1=自動送信、2=手動送信)
    ms_widget_id         VARCHAR(50)    DEFAULT ''                    NOT NULL,      -- 送信ウィジェットID
    ms_to                VARCHAR(50)    DEFAULT ''                    NOT NULL,      -- メール送信先アドレス
    ms_from              VARCHAR(50)    DEFAULT ''                    NOT NULL,      -- メール送信元アドレス
    ms_subject           VARCHAR(100)   DEFAULT ''                    NOT NULL,      -- メール件名
    ms_body              TEXT                                         NOT NULL,      -- メール本文
    ms_dt                TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- 送信日時
    PRIMARY KEY          (ms_serial)
) TYPE=innodb;

-- 定型メールフォーム
DROP TABLE IF EXISTS _mail_form;
CREATE TABLE _mail_form (
    mf_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    mf_id                VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- 定義項目ID
    mf_history_index     INT            DEFAULT 0                     NOT NULL,      -- 履歴管理用インデックスNo(0～)
    
    mf_language_id       VARCHAR(2)     DEFAULT ''                    NOT NULL,      -- 言語ID
    mf_subject           VARCHAR(100)   DEFAULT ''                    NOT NULL,      -- 件名
    mf_content           TEXT                                         NOT NULL,      -- コンテンツ

    mf_check_out_user_id INT            DEFAULT 0                     NOT NULL,      -- チェックアウトユーザID(0のときはチェックイン状態)
    mf_check_out_dt      TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- チェックアウト日時
    mf_create_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード作成者
    mf_create_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード作成日時
    mf_update_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード更新者
    mf_update_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード更新日時
    mf_deleted           BOOLEAN        DEFAULT false                 NOT NULL,      -- レコード削除状態
    PRIMARY KEY          (mf_serial),
    UNIQUE               (mf_id,        mf_history_index)
) TYPE=innodb;

-- テーブル作成マスター
DROP TABLE IF EXISTS _table_def;
CREATE TABLE _table_def (
    td_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    td_table_id          VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- テーブル名
    td_id                VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- フィールド名(空文字列=テーブル名保持用)
    td_index             INT            DEFAULT 0                     NOT NULL,      -- フィールド番号
    td_type              VARCHAR(10)    DEFAULT ''                    NOT NULL,      -- フィールド型
    td_name              VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- 名称
    td_default_value     VARCHAR(10)    DEFAULT ''                    NOT NULL,      -- 初期値
    PRIMARY KEY          (td_serial),
    UNIQUE               (td_table_id,  td_id)
) TYPE=innodb;

-- メニューIDマスター
DROP TABLE IF EXISTS _menu_id;
CREATE TABLE _menu_id (
    mn_id                VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- メニューID
    mn_name              VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- メニュー名称
    mn_description       VARCHAR(100)   DEFAULT ''                    NOT NULL,      -- 説明
    mn_type              INT            DEFAULT 0                     NOT NULL,      -- メニュータイプ(0=単一階層、1=複数階層)
    mn_device_type       INT            DEFAULT 0                     NOT NULL,      -- 端末タイプ(0=PC、1=携帯、2=スマートフォン)
    mn_sort_order        INT            DEFAULT 0                     NOT NULL,      -- ソート順
    PRIMARY KEY  (mn_id)
) TYPE=innodb;

-- メニュー定義マスター
DROP TABLE IF EXISTS _menu_def;
CREATE TABLE _menu_def (
    md_id                INT            DEFAULT 0                     NOT NULL,      -- 項目ID
    md_parent_id         INT            DEFAULT 0                     NOT NULL,      -- 親項目ID(親がないときは0)
    md_index             INT            DEFAULT 0                     NOT NULL,      -- 表示順(0～)、md_parent_id=0のときは親間の表示順
    md_menu_id           VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- メニューID
    md_name              TEXT                                         NOT NULL,      -- 名前
    md_description       VARCHAR(100)   DEFAULT ''                    NOT NULL,      -- 説明
    md_type              INT            DEFAULT 0                     NOT NULL,      -- メニュー項目タイプ(0=リンク、1=フォルダ、2=テキスト、3=セパレータ)
    md_link_type         INT            DEFAULT 0                     NOT NULL,      -- リンクタイプ(0=同ウィンドウ、1=別ウィンドウ)
    md_link_url          TEXT                                         NOT NULL,      -- リンク先
    md_content_type      VARCHAR(10)    DEFAULT ''                    NOT NULL,      -- リンク先のコンテンツの種別
    md_content_id        VARCHAR(10)    DEFAULT ''                    NOT NULL,      -- リンク先のコンテンツのID
    md_enable            BOOLEAN        DEFAULT true                  NOT NULL,      -- 使用可能かどうか
    md_visible           BOOLEAN        DEFAULT true                  NOT NULL,      -- 表示するかどうか

    md_update_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード更新者
    md_update_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード更新日時
    PRIMARY KEY          (md_id)
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

-- サイト解析状況マスター
DROP TABLE IF EXISTS _analyze_status;
CREATE TABLE _analyze_status (
    as_id                VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- ID(Key)
    as_value             TEXT                                         NOT NULL,      -- 値
    as_update_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- 更新日時
    PRIMARY KEY  (as_id)
) TYPE=innodb;

-- サイト解析ページビュートラン
DROP TABLE IF EXISTS _analyze_page_view;
CREATE TABLE _analyze_page_view (
    ap_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    ap_type              INT            DEFAULT 0                     NOT NULL,      -- データタイプ(0=全データ、1=ブラウザアクセスに限定)
    ap_url               VARCHAR(191)   DEFAULT ''                    NOT NULL,      -- URL
    ap_date              DATE           DEFAULT '0000-00-00'          NOT NULL,      -- 日付
    ap_hour              SMALLINT       DEFAULT 0                     NOT NULL,      -- 時間
    ap_count             INT            DEFAULT 0                     NOT NULL,      -- 参照数
    ap_path              VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- アクセスポイントパス
    PRIMARY KEY          (ap_serial),
    UNIQUE               (ap_type,      ap_url,   ap_path, ap_date,   ap_hour)
) TYPE=innodb;

-- サイト解析日時カウントトラン
DROP TABLE IF EXISTS _analyze_daily_count;
CREATE TABLE _analyze_daily_count (
    aa_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    aa_type              INT            DEFAULT 0                     NOT NULL,      -- データタイプ(0=訪問数、1=訪問者数)
    aa_url               VARCHAR(191)   DEFAULT ''                    NOT NULL,      -- URL
    aa_date              DATE           DEFAULT '0000-00-00'          NOT NULL,      -- 日付
    aa_count             INT            DEFAULT 0                     NOT NULL,      -- 参照数
    aa_path              VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- アクセスポイントパス
    PRIMARY KEY          (aa_serial),
    UNIQUE               (aa_type,      aa_url,        aa_path,       aa_date)
) TYPE=innodb;

