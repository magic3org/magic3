-- *
-- * バージョンアップ用スクリプト
-- *
-- * PHP versions 5
-- *
-- * LICENSE: This source file is licensed under the terms of the GNU General Public License.
-- *
-- * @package    Magic3 Framework
-- * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
-- * @copyright  Copyright 2006-2010 Magic3 Project.
-- * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
-- * @version    SVN: $Id: 2010061801_to_2010070901.sql 3360 2010-07-09 02:17:59Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- ウィジェット情報
DELETE FROM _widgets WHERE wd_id = 'chacha_main';
INSERT INTO _widgets
(wd_id,         wd_name,                wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_read_css,               wd_add_script_lib, wd_add_script_lib_a, wd_use_ajax, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('chacha_main', 'マイクロブログメイン', '1.0.0',    '株式会社 毎日メディアサービス', '株式会社 毎日メディアサービス', 'GPL',      10,                'マイクロブログのメイン', true, 'jquery.jcarousel,jquery.cookie',                       '', true,        true,         true,               false,true,           0, 2, 2, now(), now());
-- ウィジェット情報(携帯用)
DELETE FROM _widgets WHERE wd_id = 'm/chacha';
INSERT INTO _widgets
(wd_id,      wd_name,          wd_version, wd_author,                       wd_copyright, wd_license, wd_official_level, wd_description, wd_mobile, wd_has_admin, wd_initialized, wd_install_dt, wd_create_dt) VALUES
('m/chacha', 'マイクロブログ', '1.0.0',    '株式会社 毎日メディアサービス', '株式会社 毎日メディアサービス', 'GPL',      10, 'マイクロブログ(携帯用)',               true,      true,         true,              now(),         now());

-- マイクロブログ設定マスター
DROP TABLE IF EXISTS mblog_config;
CREATE TABLE mblog_config (
    mc_board_id          VARCHAR(30)    DEFAULT ''                    NOT NULL,      -- 掲示板ID(空文字列=デフォルト)
    mc_id                VARCHAR(30)    DEFAULT ''                    NOT NULL,      -- ID(key)
    mc_value             TEXT                                         NOT NULL,      -- 値
    mc_name              VARCHAR(50)    DEFAULT ''                    NOT NULL,      -- 名称
    mc_description       VARCHAR(80)    DEFAULT ''                    NOT NULL,      -- 説明
    mc_index             INT            DEFAULT 0                     NOT NULL,      -- ソート用
    PRIMARY KEY          (mc_board_id,  mc_id)
) TYPE=innodb;

-- マイクロブログスレッドマスター
DROP TABLE IF EXISTS mblog_thread;
CREATE TABLE mblog_thread (
    mt_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    mt_board_id          VARCHAR(30)    DEFAULT ''                    NOT NULL,      -- 掲示板ID(空文字列=デフォルト)
    mt_id                VARCHAR(10)    DEFAULT ''                    NOT NULL,      -- スレッドID
    mt_history_index     INT            DEFAULT 0                     NOT NULL,      -- 履歴管理用インデックスNo(0～)
    
    mt_no                INT            DEFAULT 0                     NOT NULL,      -- スレッド番号
    mt_update_no         INT            DEFAULT 0                     NOT NULL,      -- スレッド更新番号
    mt_subject           TEXT                                         NOT NULL,      -- 件名
    mt_message_count     INT            DEFAULT 0                     NOT NULL,      -- 投稿数
    mt_access_count      INT            DEFAULT 0                     NOT NULL,      -- 参照数
    mt_dt                TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- スレッド更新日時
    mt_log_serial        INT            DEFAULT 0                     NOT NULL,      -- アクセスログシリアル番号
    
    mt_create_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード作成者
    mt_create_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード作成日時
    mt_update_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード更新者
    mt_update_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード更新日時
    mt_deleted           BOOLEAN        DEFAULT false                 NOT NULL,      -- レコード削除状態
    PRIMARY KEY          (mt_serial),
    UNIQUE               (mt_board_id,  mt_id,  mt_history_index)
) TYPE=innodb;

-- マイクロブログスレッドメッセージトラン
DROP TABLE IF EXISTS mblog_thread_message;
CREATE TABLE mblog_thread_message (
    mm_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    mm_board_id          VARCHAR(30)    DEFAULT ''                    NOT NULL,      -- 掲示板ID(空文字列=デフォルト)
    mm_thread_id         VARCHAR(10)    DEFAULT ''                    NOT NULL,      -- スレッドID
    mm_index             INT            DEFAULT 0                     NOT NULL,      -- 投稿番号(1以上)
    mm_history_index     INT            DEFAULT 0                     NOT NULL,      -- 履歴管理用インデックスNo(0～)
    
    mm_message           TEXT                                         NOT NULL,      -- 投稿文
    mm_status_param      TEXT                                         NOT NULL,      -- 投稿文状態
    mm_regist_member_id  VARCHAR(10)    DEFAULT ''                    NOT NULL,      -- 投稿会員ID
    mm_regist_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- 投稿日時
    mm_log_serial        INT            DEFAULT 0                     NOT NULL,      -- アクセスログシリアル番号

    mm_create_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード作成者
    mm_create_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード作成日時
    mm_update_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード更新者
    mm_update_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード更新日時
    mm_deleted           BOOLEAN        DEFAULT false                 NOT NULL,      -- レコード削除状態
    PRIMARY KEY          (mm_serial),
    UNIQUE               (mm_board_id,  mm_thread_id,  mm_index, mm_history_index)
) TYPE=innodb;

-- マイクロブログ会員情報マスター
DROP TABLE IF EXISTS mblog_member;
CREATE TABLE mblog_member (
    mb_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    mb_id                VARCHAR(10)    DEFAULT ''                    NOT NULL,      -- 会員ID
    mb_history_index     INT            DEFAULT 0                     NOT NULL,      -- 履歴管理用インデックスNo(0～)
    
    mb_device_id         VARCHAR(32)    DEFAULT ''                    NOT NULL,      -- 端末ID(携帯のときは端末ID、PCのときクッキー値)
    mb_user_id           INT            DEFAULT 0                     NOT NULL,      -- ログインユーザID
    mb_password          CHAR(32)       DEFAULT ''                    NOT NULL,      -- パスワード(MD5)
    mb_name              VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- 会員名
    mb_email             VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- Eメールアドレス
    mb_avatar            VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- アバターファイル名
    mb_url               TEXT                                         NOT NULL,      -- ホームーページ
    mb_show_email        BOOLEAN        DEFAULT false                 NOT NULL,      -- Eメールアドレスを公開するかどうか
    mb_regist_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- 登録日時
    mb_last_access_dt    TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- 最終アクセス日時
    
    mb_create_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード作成者
    mb_create_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード作成日時
    mb_update_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード更新者
    mb_update_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード更新日時
    mb_deleted           BOOLEAN        DEFAULT false                 NOT NULL,      -- レコード削除状態
    PRIMARY KEY          (mb_serial),
    UNIQUE               (mb_id,        mb_history_index)
) TYPE=innodb;

INSERT INTO mblog_config 
(mc_id,                   mc_value,                mc_name) VALUES
('post_with_no_login',    '0',                     'ログインなしの投稿'),
('use_subject',           '0',                     '件名の使用'),
('message_id_length',     '5',                     'メッセージIDのバイト数'),
('message_count_top',     '3',                     'トップページのメッセージ表示項目数'),
('message_count_mypage',  '30',                    'マイページのメッセージ表示項目数'),
('m:message_count_mypage','10',                    'マイページのメッセージ表示項目数(携帯)'),
('text_color',            '#000000',               '文字色'),
('bg_color',              '#EAF4F5',               '背景色'),
('inner_bg_color',        '#FFFFCC',               '内枠のデフォルト背景色'),
('profile_color',         '',                      'プロフィール背景色'),
('err_message_color',     '#FF0000',               'エラーメッセージ文字色'),
('bg_image',              '',                      '背景画像'),
('message_length',         '200',                   '投稿文最大長'),
('subject_length',         '30',                   '件名最大長'),
('name_length',            '30',                   '投稿者名最大長'),
('email_length',           '30',                   'emailアドレス最大長'),
('top_contents',           '',                     'トップ画面のコンテンツ'),
('m:top_contents',           '',                   'トップ画面のコンテンツ(携帯)');
