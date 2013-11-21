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
-- * @version    SVN: $Id: 2011081801_to_2011092601.sql 4361 2011-09-26 12:02:34Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
-- ページ情報マスター
DELETE FROM _page_info WHERE pn_id = 'index' AND pn_sub_id = 'photo';
DELETE FROM _page_info WHERE pn_id = 'm_index' AND pn_sub_id = 'photo';
INSERT INTO _page_info
(pn_id,       pn_sub_id, pn_content_type, pn_use_ssl) VALUES
('index',     'photo',     'photo',         false),
('m_index',   'photo',     'photo',         false);

-- *** システム標準テーブル ***
-- フォトギャラリー設定マスター
DROP TABLE IF EXISTS photo_config;
CREATE TABLE photo_config (
    hg_id                VARCHAR(30)    DEFAULT ''                    NOT NULL,      -- ID(key)
    hg_value             TEXT                                         NOT NULL,      -- 値
    hg_name              VARCHAR(80)    DEFAULT ''                    NOT NULL,      -- 名称
    hg_description       VARCHAR(160)   DEFAULT ''                    NOT NULL,      -- 説明
    hg_index             INT            DEFAULT 0                     NOT NULL,      -- ソート用
    PRIMARY KEY          (hg_id)
) TYPE=innodb;

INSERT INTO photo_config
(hg_id,                     hg_value,           hg_name,                                  hg_index) VALUES
('image_protect_copyright',       '1',                '画像著作権保護',                             1),
('upload_image_max_size',   '500K',             'アップロード画像の最大サイズ(バイト数)', 2),
('watermark_filename',      'default_mark.jpg', 'セキュリティ保護画像ファイル名',         3),
('default_image_size',      '160',              '公開画像デフォルトサイズ',               4),
('default_thumbnail_size',  '128',              'サムネール画像デフォルトサイズ',         5),
('thumbnail_bg_color',      '#FFFFFF',              'サムネール画像背景色',         6),
('thumbnail_type',          '0',              'サムネールタイプ',         7),
('image_category_count',  '2',                '画像カテゴリー数',         8),
('photo_list_item_count',  '24',                '画像一覧表示項目数',         9),
('photo_list_order',        '1',         '画像一覧表示順',                         10),
('photo_title_short_length',  '10',                '画像タイトル(略式)文字数',         11);

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
    ht_mime_type         VARCHAR(30)    DEFAULT ''                    NOT NULL,      -- 画像MIMEタイプ
    ht_image_size        VARCHAR(10)    DEFAULT ''                    NOT NULL,      -- 画像縦横サイズ
    ht_original_filename VARCHAR(256)   DEFAULT ''                    NOT NULL,      -- 元の画像ファイル名
    ht_file_size         INT            DEFAULT 0                     NOT NULL,      -- ファイルサイズ(バイト)
    ht_name              VARCHAR(160)   DEFAULT ''                    NOT NULL,      -- 画像名称
    ht_camera            VARCHAR(80)    DEFAULT ''                    NOT NULL,      -- カメラ
    ht_location          TEXT                                         NOT NULL,      -- 撮影場所
    ht_date              DATE           DEFAULT '0000-00-00'          NOT NULL,      -- 撮影日
    ht_time              INT            DEFAULT 0                     NOT NULL,      -- 撮影時間(hhmm)
    ht_note              TEXT                                         NOT NULL,      -- 補足情報
    ht_keyword           TEXT                                         NOT NULL,      -- 検索用キーワード(「,」区切りで複数指定可)
    ht_visible           BOOLEAN        DEFAULT true                  NOT NULL,      -- 表示するかどうか
    ht_user_limited      BOOLEAN        DEFAULT false                 NOT NULL,      -- 参照ユーザを制限
    ht_license           VARCHAR(2)     DEFAULT ''                    NOT NULL,      -- ライセンス(ロイヤリティフリー(RF),ライツマネージド(RM))
    ht_owner_id          INT            DEFAULT 0                     NOT NULL,      -- 所有者ID
    ht_regist_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- アップロード日時
    ht_active_start_dt   TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- 公開期間(開始)
    ht_active_end_dt     TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- 公開期間(終了)
    
    ht_create_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード作成者
    ht_create_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード作成日時
    ht_update_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード更新者
    ht_update_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード更新日時
    ht_deleted           BOOLEAN        DEFAULT false                 NOT NULL,      -- レコード削除状態
    PRIMARY KEY          (ht_serial),
    UNIQUE               (ht_id,        ht_language_id,               ht_history_index)
) TYPE=innodb;

-- 写真カテゴリマスター
DROP TABLE IF EXISTS photo_category;
CREATE TABLE photo_category (
    hc_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    hc_id                INT            DEFAULT 0                     NOT NULL,      -- カテゴリID
    hc_language_id       VARCHAR(2)     DEFAULT ''                    NOT NULL,      -- 言語ID
    hc_history_index     INT            DEFAULT 0                     NOT NULL,      -- 履歴管理用インデックスNo(0～)

    hc_name              VARCHAR(30)    DEFAULT ''                    NOT NULL,      -- カテゴリ名称
    hc_parent_id         INT            DEFAULT 0                     NOT NULL,      -- 親カテゴリID
    hc_sort_order        INT            DEFAULT 0                     NOT NULL,      -- ソート用
    hc_visible           BOOLEAN        DEFAULT true                  NOT NULL,      -- 表示するかどうか

    hc_create_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード作成者
    hc_create_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード作成日時
    hc_update_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード更新者
    hc_update_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード更新日時
    hc_deleted           BOOLEAN        DEFAULT false                 NOT NULL,      -- レコード削除状態
    PRIMARY KEY          (hc_serial),
    UNIQUE               (hc_id,        hc_language_id,  hc_history_index)
) TYPE=innodb;

-- 写真と写真カテゴリーの対応付けマスター
DROP TABLE IF EXISTS photo_with_category;
CREATE TABLE photo_with_category (
    hw_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    hw_photo_serial      INT            DEFAULT 0                     NOT NULL,      -- 写真情報シリアル番号
    hw_index             INT            DEFAULT 0                     NOT NULL,      -- インデックス番号(0～)
    hw_category_id       INT            DEFAULT 0                     NOT NULL,      -- カテゴリID
    PRIMARY KEY          (hw_serial),
    UNIQUE               (hw_photo_serial,  hw_index)
) TYPE=innodb;
