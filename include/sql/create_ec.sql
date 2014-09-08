-- *
-- * Eコマーステーブル作成スクリプト
-- *
-- * PHP versions 5
-- *
-- * LICENSE: This source file is licensed under the terms of the GNU General Public License.
-- *
-- * @package    Magic3 Framework
-- * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
-- * @copyright  Copyright 2006-2012 Magic3 Project.
-- * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
-- * @version    SVN: $Id: create_ec.sql 5430 2012-12-06 00:19:30Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- Eコマーステーブル作成スクリプト
-- Eコマース機能で必要なテーブルの作成、初期データの登録を行う
-- --------------------------------------------------------------------------------------------------

-- Eコマース設定マスター
DROP TABLE IF EXISTS commerce_config;
CREATE TABLE commerce_config (
    cg_id                VARCHAR(30)    DEFAULT ''                    NOT NULL,      -- ID(Key)
    cg_value             TEXT                                         NOT NULL,      -- 値
    cg_name              VARCHAR(50)    DEFAULT ''                    NOT NULL,      -- 名称
    cg_description       VARCHAR(80)    DEFAULT ''                    NOT NULL,      -- 説明
    cg_index             INT            DEFAULT 0                     NOT NULL,      -- ソート用
    PRIMARY KEY          (cg_id)
) ENGINE=innodb;
INSERT INTO commerce_config
(cg_id,                    cg_value, cg_name,                        cg_index) VALUES
('default_currency',       'JPY',    'デフォルト通貨',               0),
('default_tax_type',       'sales',  'デフォルト課税タイプ',         1),
('tax_in_price',           '0',      '税処理区分',                   2),      -- 0=外税、1=内税
('price_calc_type',        '0',      '金額端数処理',                 3),      -- 0=切り捨て、1=切り上げ、2=四捨五入
('tax_calc_type',          '0',      '税端数処理',                   4),      -- 0=切り捨て、1=切り上げ、2=四捨五入
('use_email',              '1',      'メール送信機能',               5),
('shop_email',             '',       'ショップ宛てメールアドレス',   6),
('auto_email_sender',      '',       '自動送信メール送信元アドレス', 7),
('shop_name',              '',       'ショップ名',                   8),
('shop_owner',             '',       'ショップオーナー名',           9),
('shop_address',           '',       'ショップ住所',                 10),
('shop_phone',             '',       'ショップ電話番号',             11),
('category_select_count',  '2',      '商品カテゴリー選択可能数',     12),
('order_cancel_hour',      '24',     '注文のキャンセル可能時間',     13),
('disp_product_count',     '10',     '商品一覧表示項目数',           14),
('decrement_view_stock_count',              '1',      '注文時の表示在庫数デクリメント',               15),
('permit_non_member_order', '0',      '非会員からの注文受付',         16),
('hierarchical_category',  '1',      '階層化商品カテゴリー',         17);

-- 単位マスター
DROP TABLE IF EXISTS unit_type;
CREATE TABLE unit_type (
    ut_id                VARCHAR(4)     DEFAULT ''                    NOT NULL,      -- 単位ID
    ut_language_id       VARCHAR(2)     DEFAULT ''                    NOT NULL,      -- 言語ID
    ut_name              VARCHAR(30)    DEFAULT ''                    NOT NULL,      -- 名称
    ut_description       VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- 説明
    ut_symbol            VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- 記号
    ut_decimal_place     INT            DEFAULT 0                     NOT NULL,      -- 小数以下桁数
    ut_index             INT            DEFAULT 0                     NOT NULL,      -- ソート用
    PRIMARY KEY          (ut_id,        ut_language_id)
) ENGINE=innodb;
INSERT INTO unit_type (ut_id, ut_language_id, ut_name, ut_description, ut_symbol, ut_decimal_place, ut_index) VALUES ('ko',   'ja', '個',             '', '個',       0, 0);
INSERT INTO unit_type (ut_id, ut_language_id, ut_name, ut_description, ut_symbol, ut_decimal_place, ut_index) VALUES ('hako', 'ja', '箱',             '', '箱',       0, 1);
INSERT INTO unit_type (ut_id, ut_language_id, ut_name, ut_description, ut_symbol, ut_decimal_place, ut_index) VALUES ('mai',  'ja', '枚',             '', '枚',       0, 2);
INSERT INTO unit_type (ut_id, ut_language_id, ut_name, ut_description, ut_symbol, ut_decimal_place, ut_index) VALUES ('ken',  'ja', '件',             '', '件',       0, 3);
INSERT INTO unit_type (ut_id, ut_language_id, ut_name, ut_description, ut_symbol, ut_decimal_place, ut_index) VALUES ('satu', 'ja', '冊',             '', '冊',       0, 4);
INSERT INTO unit_type (ut_id, ut_language_id, ut_name, ut_description, ut_symbol, ut_decimal_place, ut_index) VALUES ('cart', 'ja', 'カートン',       '', 'カートン', 0, 5);
INSERT INTO unit_type (ut_id, ut_language_id, ut_name, ut_description, ut_symbol, ut_decimal_place, ut_index) VALUES ('case', 'ja', 'ケース',         '', 'ケース',   0, 6);
INSERT INTO unit_type (ut_id, ut_language_id, ut_name, ut_description, ut_symbol, ut_decimal_place, ut_index) VALUES ('g',    'ja', 'グラム',         '', 'g',        2, 7);
INSERT INTO unit_type (ut_id, ut_language_id, ut_name, ut_description, ut_symbol, ut_decimal_place, ut_index) VALUES ('kg',   'ja', 'キログラム',     '', 'kg',       2, 8);
INSERT INTO unit_type (ut_id, ut_language_id, ut_name, ut_description, ut_symbol, ut_decimal_place, ut_index) VALUES ('m',    'ja', 'メートル',       '', 'm',        2, 9);
INSERT INTO unit_type (ut_id, ut_language_id, ut_name, ut_description, ut_symbol, ut_decimal_place, ut_index) VALUES ('cm',   'ja', 'センチメートル', '', 'cm',       2, 10);
INSERT INTO unit_type (ut_id, ut_language_id, ut_name, ut_description, ut_symbol, ut_decimal_place, ut_index) VALUES ('in',   'ja', 'インチ',         '', 'in',       2, 11);
INSERT INTO unit_type (ut_id, ut_language_id, ut_name, ut_description, ut_symbol, ut_decimal_place, ut_index) VALUES ('l',    'ja', 'リットル',       '', 'l',        2, 12);
INSERT INTO unit_type (ut_id, ut_language_id, ut_name, ut_description, ut_symbol, ut_decimal_place, ut_index) VALUES ('ml',   'ja', 'ミリリットル',   '', 'ml',       2, 13);
INSERT INTO unit_type (ut_id, ut_language_id, ut_name, ut_description, ut_symbol, ut_decimal_place, ut_index) VALUES ('gal',  'ja', 'ガロン',         '', 'gal',      2, 14);

-- 税率マスター
DROP TABLE IF EXISTS tax_rate;
CREATE TABLE tax_rate (
    tr_id                VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- 税率ID
    tr_priority          INT            DEFAULT 0                     NOT NULL,      -- 優先度
    tr_name              VARCHAR(30)    DEFAULT ''                    NOT NULL,      -- 名称
    tr_rate              DECIMAL(7,4)   DEFAULT 0                     NOT NULL,      -- 税率(%)
    tr_active_start_dt   TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- 有効期限開始日時
    tr_active_end_dt     TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- 有効期限終了日時
    PRIMARY KEY          (tr_id,        tr_priority)
) ENGINE=innodb;
INSERT INTO tax_rate (tr_id, tr_priority, tr_name, tr_rate) VALUES ('rate_sales', 0, '消費税率', '5.00');

-- 税種別マスター
DROP TABLE IF EXISTS tax_type;
CREATE TABLE tax_type (
    tt_id                VARCHAR(10)    DEFAULT ''                    NOT NULL,      -- 種別ID
    tt_language_id       VARCHAR(2)     DEFAULT ''                    NOT NULL,      -- 言語ID
    tt_tax_rate_type     INT            DEFAULT 0                     NOT NULL,      -- 税率種別(0=税率なし、1=固定(tax_rateテーブル))
    tt_tax_inout         SMALLINT       DEFAULT 0                     NOT NULL,      -- 内税外税区分(0=外税、1=内税)
    tt_tax_rate_id       VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- 税率ID
    tt_name              VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- 名称
    tt_description       VARCHAR(100)   DEFAULT ''                    NOT NULL,      -- 説明
    tt_geo_zone_id       TEXT                                         NOT NULL,      -- 対象区域(,区切り)
    tt_index             INT            DEFAULT 0                     NOT NULL,      -- ソート用
    PRIMARY KEY          (tt_id,        tt_language_id)
) ENGINE=innodb;
INSERT INTO tax_type (tt_id, tt_language_id, tt_tax_rate_type, tt_tax_rate_id, tt_name, tt_geo_zone_id, tt_index) VALUES ('sales', 'ja', 1, 'rate_sales', '課税(外税)',   '1', 0);
INSERT INTO tax_type (tt_id, tt_language_id, tt_tax_rate_type, tt_tax_rate_id, tt_name, tt_geo_zone_id, tt_index) VALUES ('notax', 'ja', 0, '',           '非課税', '1', 1);

-- 地理的地域マスター
DROP TABLE IF EXISTS geo_zone;
CREATE TABLE geo_zone (
    gz_id                INT            DEFAULT 0                     NOT NULL,      -- 地域ID
    gz_country_id        VARCHAR(3)     DEFAULT ''                    NOT NULL,      -- 国ID
    gz_region_id         VARCHAR(10)    DEFAULT ''                    NOT NULL,      -- 地域ID
    gz_type              INT            DEFAULT 0                     NOT NULL,      -- 地域タイプ(0=国全域、1=都道府県や州、2=地方)
    gz_language_id       VARCHAR(2)     DEFAULT ''                    NOT NULL,      -- 言語ID
    gz_name              VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- 名称
    gz_description       VARCHAR(100)   DEFAULT ''                    NOT NULL,      -- 説明
    gz_index             INT            DEFAULT 0                     NOT NULL,      -- ソート用
    PRIMARY KEY          (gz_id),
    UNIQUE               (gz_country_id,    gz_region_id,             gz_type,       gz_language_id)
) ENGINE=innodb;

INSERT INTO geo_zone (gz_id, gz_country_id, gz_region_id, gz_type, gz_language_id, gz_name, gz_description, gz_index) VALUES (1, 'JPN', '00', 0, 'ja', '日本全域', '', 1);
INSERT INTO geo_zone (gz_id, gz_country_id, gz_region_id, gz_type, gz_language_id, gz_name, gz_description, gz_index) VALUES (2, 'JPN', '01', 1, 'ja', '北海道',   '', 2);
INSERT INTO geo_zone (gz_id, gz_country_id, gz_region_id, gz_type, gz_language_id, gz_name, gz_description, gz_index) VALUES (3, 'JPN', '02', 1, 'ja', '青森県',   '', 3);
INSERT INTO geo_zone (gz_id, gz_country_id, gz_region_id, gz_type, gz_language_id, gz_name, gz_description, gz_index) VALUES (4, 'JPN', '03', 1, 'ja', '岩手県',   '', 4);
INSERT INTO geo_zone (gz_id, gz_country_id, gz_region_id, gz_type, gz_language_id, gz_name, gz_description, gz_index) VALUES (5, 'JPN', '04', 1, 'ja', '宮城県',   '', 5);
INSERT INTO geo_zone (gz_id, gz_country_id, gz_region_id, gz_type, gz_language_id, gz_name, gz_description, gz_index) VALUES (6, 'JPN', '05', 1, 'ja', '秋田県',   '', 6);
INSERT INTO geo_zone (gz_id, gz_country_id, gz_region_id, gz_type, gz_language_id, gz_name, gz_description, gz_index) VALUES (7, 'JPN', '06', 1, 'ja', '山形県',   '', 7);
INSERT INTO geo_zone (gz_id, gz_country_id, gz_region_id, gz_type, gz_language_id, gz_name, gz_description, gz_index) VALUES (8, 'JPN', '07', 1, 'ja', '福島県',   '', 8);
INSERT INTO geo_zone (gz_id, gz_country_id, gz_region_id, gz_type, gz_language_id, gz_name, gz_description, gz_index) VALUES (9, 'JPN', '08', 1, 'ja', '茨城県',   '', 9);
INSERT INTO geo_zone (gz_id, gz_country_id, gz_region_id, gz_type, gz_language_id, gz_name, gz_description, gz_index) VALUES (10, 'JPN', '09', 1, 'ja', '栃木県',   '', 10);
INSERT INTO geo_zone (gz_id, gz_country_id, gz_region_id, gz_type, gz_language_id, gz_name, gz_description, gz_index) VALUES (11, 'JPN', '10', 1, 'ja', '群馬県',   '', 11);
INSERT INTO geo_zone (gz_id, gz_country_id, gz_region_id, gz_type, gz_language_id, gz_name, gz_description, gz_index) VALUES (12, 'JPN', '11', 1, 'ja', '埼玉県',   '', 12);
INSERT INTO geo_zone (gz_id, gz_country_id, gz_region_id, gz_type, gz_language_id, gz_name, gz_description, gz_index) VALUES (13, 'JPN', '12', 1, 'ja', '千葉県',   '', 13);
INSERT INTO geo_zone (gz_id, gz_country_id, gz_region_id, gz_type, gz_language_id, gz_name, gz_description, gz_index) VALUES (14, 'JPN', '13', 1, 'ja', '東京都',   '', 14);
INSERT INTO geo_zone (gz_id, gz_country_id, gz_region_id, gz_type, gz_language_id, gz_name, gz_description, gz_index) VALUES (15, 'JPN', '14', 1, 'ja', '神奈川県', '', 15);
INSERT INTO geo_zone (gz_id, gz_country_id, gz_region_id, gz_type, gz_language_id, gz_name, gz_description, gz_index) VALUES (16, 'JPN', '15', 1, 'ja', '新潟県',   '', 16);
INSERT INTO geo_zone (gz_id, gz_country_id, gz_region_id, gz_type, gz_language_id, gz_name, gz_description, gz_index) VALUES (17, 'JPN', '16', 1, 'ja', '富山県',   '', 17);
INSERT INTO geo_zone (gz_id, gz_country_id, gz_region_id, gz_type, gz_language_id, gz_name, gz_description, gz_index) VALUES (18, 'JPN', '17', 1, 'ja', '石川県',   '', 18);
INSERT INTO geo_zone (gz_id, gz_country_id, gz_region_id, gz_type, gz_language_id, gz_name, gz_description, gz_index) VALUES (19, 'JPN', '18', 1, 'ja', '福井県',   '', 19);
INSERT INTO geo_zone (gz_id, gz_country_id, gz_region_id, gz_type, gz_language_id, gz_name, gz_description, gz_index) VALUES (20, 'JPN', '19', 1, 'ja', '山梨県',   '', 20);
INSERT INTO geo_zone (gz_id, gz_country_id, gz_region_id, gz_type, gz_language_id, gz_name, gz_description, gz_index) VALUES (21, 'JPN', '20', 1, 'ja', '長野県',   '', 21);
INSERT INTO geo_zone (gz_id, gz_country_id, gz_region_id, gz_type, gz_language_id, gz_name, gz_description, gz_index) VALUES (22, 'JPN', '21', 1, 'ja', '岐阜県',   '', 22);
INSERT INTO geo_zone (gz_id, gz_country_id, gz_region_id, gz_type, gz_language_id, gz_name, gz_description, gz_index) VALUES (23, 'JPN', '22', 1, 'ja', '静岡県',   '', 23);
INSERT INTO geo_zone (gz_id, gz_country_id, gz_region_id, gz_type, gz_language_id, gz_name, gz_description, gz_index) VALUES (24, 'JPN', '23', 1, 'ja', '愛知県',   '', 24);
INSERT INTO geo_zone (gz_id, gz_country_id, gz_region_id, gz_type, gz_language_id, gz_name, gz_description, gz_index) VALUES (25, 'JPN', '24', 1, 'ja', '三重県',   '', 25);
INSERT INTO geo_zone (gz_id, gz_country_id, gz_region_id, gz_type, gz_language_id, gz_name, gz_description, gz_index) VALUES (26, 'JPN', '25', 1, 'ja', '滋賀県',   '', 26);
INSERT INTO geo_zone (gz_id, gz_country_id, gz_region_id, gz_type, gz_language_id, gz_name, gz_description, gz_index) VALUES (27, 'JPN', '26', 1, 'ja', '京都府',   '', 27);
INSERT INTO geo_zone (gz_id, gz_country_id, gz_region_id, gz_type, gz_language_id, gz_name, gz_description, gz_index) VALUES (28, 'JPN', '27', 1, 'ja', '大阪府',   '', 28);
INSERT INTO geo_zone (gz_id, gz_country_id, gz_region_id, gz_type, gz_language_id, gz_name, gz_description, gz_index) VALUES (29, 'JPN', '28', 1, 'ja', '兵庫県',   '', 29);
INSERT INTO geo_zone (gz_id, gz_country_id, gz_region_id, gz_type, gz_language_id, gz_name, gz_description, gz_index) VALUES (30, 'JPN', '29', 1, 'ja', '奈良県',   '', 30);
INSERT INTO geo_zone (gz_id, gz_country_id, gz_region_id, gz_type, gz_language_id, gz_name, gz_description, gz_index) VALUES (31, 'JPN', '30', 1, 'ja', '和歌山県', '', 31);
INSERT INTO geo_zone (gz_id, gz_country_id, gz_region_id, gz_type, gz_language_id, gz_name, gz_description, gz_index) VALUES (32, 'JPN', '31', 1, 'ja', '鳥取県',   '', 32);
INSERT INTO geo_zone (gz_id, gz_country_id, gz_region_id, gz_type, gz_language_id, gz_name, gz_description, gz_index) VALUES (33, 'JPN', '32', 1, 'ja', '島根県',   '', 33);
INSERT INTO geo_zone (gz_id, gz_country_id, gz_region_id, gz_type, gz_language_id, gz_name, gz_description, gz_index) VALUES (34, 'JPN', '33', 1, 'ja', '岡山県',   '', 34);
INSERT INTO geo_zone (gz_id, gz_country_id, gz_region_id, gz_type, gz_language_id, gz_name, gz_description, gz_index) VALUES (35, 'JPN', '34', 1, 'ja', '広島県',   '', 35);
INSERT INTO geo_zone (gz_id, gz_country_id, gz_region_id, gz_type, gz_language_id, gz_name, gz_description, gz_index) VALUES (36, 'JPN', '35', 1, 'ja', '山口県',   '', 36);
INSERT INTO geo_zone (gz_id, gz_country_id, gz_region_id, gz_type, gz_language_id, gz_name, gz_description, gz_index) VALUES (37, 'JPN', '36', 1, 'ja', '徳島県',   '', 37);
INSERT INTO geo_zone (gz_id, gz_country_id, gz_region_id, gz_type, gz_language_id, gz_name, gz_description, gz_index) VALUES (38, 'JPN', '37', 1, 'ja', '香川県',   '', 38);
INSERT INTO geo_zone (gz_id, gz_country_id, gz_region_id, gz_type, gz_language_id, gz_name, gz_description, gz_index) VALUES (39, 'JPN', '38', 1, 'ja', '愛媛県',   '', 39);
INSERT INTO geo_zone (gz_id, gz_country_id, gz_region_id, gz_type, gz_language_id, gz_name, gz_description, gz_index) VALUES (40, 'JPN', '39', 1, 'ja', '高知県',   '', 40);
INSERT INTO geo_zone (gz_id, gz_country_id, gz_region_id, gz_type, gz_language_id, gz_name, gz_description, gz_index) VALUES (41, 'JPN', '40', 1, 'ja', '福岡県',   '', 41);
INSERT INTO geo_zone (gz_id, gz_country_id, gz_region_id, gz_type, gz_language_id, gz_name, gz_description, gz_index) VALUES (42, 'JPN', '41', 1, 'ja', '佐賀県',   '', 42);
INSERT INTO geo_zone (gz_id, gz_country_id, gz_region_id, gz_type, gz_language_id, gz_name, gz_description, gz_index) VALUES (43, 'JPN', '42', 1, 'ja', '長崎県',   '', 43);
INSERT INTO geo_zone (gz_id, gz_country_id, gz_region_id, gz_type, gz_language_id, gz_name, gz_description, gz_index) VALUES (44, 'JPN', '43', 1, 'ja', '熊本県',   '', 44);
INSERT INTO geo_zone (gz_id, gz_country_id, gz_region_id, gz_type, gz_language_id, gz_name, gz_description, gz_index) VALUES (45, 'JPN', '44', 1, 'ja', '大分県',   '', 45);
INSERT INTO geo_zone (gz_id, gz_country_id, gz_region_id, gz_type, gz_language_id, gz_name, gz_description, gz_index) VALUES (46, 'JPN', '45', 1, 'ja', '宮崎県',   '', 46);
INSERT INTO geo_zone (gz_id, gz_country_id, gz_region_id, gz_type, gz_language_id, gz_name, gz_description, gz_index) VALUES (47, 'JPN', '46', 1, 'ja', '鹿児島県', '', 47);
INSERT INTO geo_zone (gz_id, gz_country_id, gz_region_id, gz_type, gz_language_id, gz_name, gz_description, gz_index) VALUES (48, 'JPN', '47', 1, 'ja', '沖縄県',   '', 48);

-- 画像サイズマスター
DROP TABLE IF EXISTS image_size;
CREATE TABLE image_size (
    is_id                VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- 画像サイズID
    is_type              INT            DEFAULT 0                     NOT NULL,      -- 画像用途タイプ(1=アイコン、2=バナー、3=商品、0=一般用途)
    is_name              VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- 画像サイズ名称
    is_width             INT            DEFAULT 0                     NOT NULL,      -- 画像サイズ幅
    is_height            INT            DEFAULT 0                     NOT NULL,      -- 画像サイズ高さ
    is_sort_order        INT            DEFAULT 0                     NOT NULL,      -- ソート順
    PRIMARY KEY  (is_id)
) ENGINE=innodb;
INSERT INTO image_size (is_id, is_type, is_name, is_width, is_height, is_sort_order) VALUES ('full-banner',      2, 'フルサイズバナー',     468, 60,  1);
INSERT INTO image_size (is_id, is_type, is_name, is_width, is_height, is_sort_order) VALUES ('half-banner',      2, 'ハーフサイズバナー',   234, 60,  2);
INSERT INTO image_size (is_id, is_type, is_name, is_width, is_height, is_sort_order) VALUES ('small-banner',     2, 'スモールサイズバナー', 200, 40,  3);
INSERT INTO image_size (is_id, is_type, is_name, is_width, is_height, is_sort_order) VALUES ('micro-banner',     2, 'マイクロバナー',       88,  31,  4);
INSERT INTO image_size (is_id, is_type, is_name, is_width, is_height, is_sort_order) VALUES ('standard-product', 3, '商品用標準サイズ',     100, 80,  5);
INSERT INTO image_size (is_id, is_type, is_name, is_width, is_height, is_sort_order) VALUES ('small-product',    3, '商品用小サイズ',       50,  40,  6);
INSERT INTO image_size (is_id, is_type, is_name, is_width, is_height, is_sort_order) VALUES ('large-product',    3, '商品用大サイズ',       200, 160, 7);
INSERT INTO image_size (is_id, is_type, is_name, is_width, is_height, is_sort_order) VALUES ('exlarge-product',  3, '商品用特大サイズ',     400, 320, 8);

-- 商品画像マスター
DROP TABLE IF EXISTS product_image;
CREATE TABLE product_image (
    im_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    im_product_class     VARCHAR(10)    DEFAULT ''                    NOT NULL,      -- 商品クラス
    im_type              INT            DEFAULT 0                     NOT NULL,      -- 画像タイプ(1=商品カテゴリ、2=商品)
    im_id                INT            DEFAULT 0                     NOT NULL,      -- 画像とリンクさせる対象のID
    im_language_id       VARCHAR(2)     DEFAULT ''                    NOT NULL,      -- 言語ID
    im_size_id           VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- 画像サイズID
    im_history_index     INT            DEFAULT 0                     NOT NULL,      -- 履歴管理用インデックスNo(0～)
    
    im_name              VARCHAR(30)    DEFAULT ''                    NOT NULL,      -- 画像名
    im_url               TEXT                                         NOT NULL,      -- URL
    
    im_create_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード作成者
    im_create_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード作成日時
    im_update_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード更新者
    im_update_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード更新日時
    im_deleted           BOOLEAN        DEFAULT false                 NOT NULL,      -- レコード削除状態
    PRIMARY KEY          (im_serial),
    UNIQUE               (im_product_class,     im_type,      im_id,              im_language_id,          im_size_id,       im_history_index)
) ENGINE=innodb;

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
) ENGINE=innodb;
INSERT INTO product_class
(pu_id,   pu_language_id, pu_name,                pu_index) VALUES 
('',      'ja',           '一般商品',             1),
('photo', 'ja',           'フォトギャラリー商品', 2);

-- 商品カテゴリマスター
DROP TABLE IF EXISTS product_category;
CREATE TABLE product_category (
    pc_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    pc_id                INT            DEFAULT 0                     NOT NULL,      -- 商品カテゴリID
    pc_language_id       VARCHAR(2)     DEFAULT ''                    NOT NULL,      -- 言語ID
    pc_history_index     INT            DEFAULT 0                     NOT NULL,      -- 履歴管理用インデックスNo(0～)

    pc_name              VARCHAR(60)    DEFAULT ''                    NOT NULL,      -- 商品カテゴリ名称
    pc_parent_id         INT            DEFAULT 0                     NOT NULL,      -- 親カテゴリID
    pc_sort_order        INT            DEFAULT 0                     NOT NULL,      -- ソート用
    pc_visible           BOOLEAN        DEFAULT true                  NOT NULL,      -- 表示するかどうか

    pc_create_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード作成者
    pc_create_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード作成日時
    pc_update_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード更新者
    pc_update_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード更新日時
    pc_deleted           BOOLEAN        DEFAULT false                 NOT NULL,      -- レコード削除状態
    PRIMARY KEY          (pc_serial),
    UNIQUE               (pc_id,        pc_language_id,               pc_history_index)
) ENGINE=innodb;

-- 商品と商品カテゴリーの対応付けマスター
DROP TABLE IF EXISTS product_with_category;
CREATE TABLE product_with_category (
    pw_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    pw_product_serial    INT            DEFAULT 0                     NOT NULL,      -- 商品シリアル番号
    pw_index             INT            DEFAULT 0                     NOT NULL,      -- インデックス番号(0～)
    pw_category_id       INT            DEFAULT 0                     NOT NULL,      -- 商品カテゴリーID
    PRIMARY KEY          (pw_serial),
    UNIQUE               (pw_product_serial,    pw_index)
) ENGINE=innodb;

-- 価格種別マスター
DROP TABLE IF EXISTS price_type;
CREATE TABLE price_type (
    pr_id                VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- 識別子
    pr_language_id       VARCHAR(2)     DEFAULT ''                    NOT NULL,      -- 言語ID
    pr_kind              INT            DEFAULT 0                     NOT NULL,      -- 種別(10～19=販売価格)
    pr_name              VARCHAR(30)    DEFAULT ''                    NOT NULL,      -- 正式名称
    pr_name_short        VARCHAR(10)    DEFAULT ''                    NOT NULL,      -- 略称
    pr_description       VARCHAR(50)    DEFAULT ''                    NOT NULL,      -- 説明
    pr_sort_order        INT            DEFAULT 0                     NOT NULL,      -- ソート用
    PRIMARY KEY          (pr_id,        pr_language_id)
) ENGINE=innodb;
-- INSERT INTO price_type (pr_id, pr_language_id, pr_kind, pr_name, pr_sort_order) VALUES ('suggest', 'ja', 10, '希望小売価格',  1);
INSERT INTO price_type (pr_id, pr_language_id, pr_kind, pr_name, pr_sort_order) VALUES ('selling', 'ja', 10, '通常価格',      2);
INSERT INTO price_type (pr_id, pr_language_id, pr_kind, pr_name, pr_sort_order) VALUES ('bargain', 'ja', 10, '特価',          3);
INSERT INTO price_type (pr_id, pr_language_id, pr_kind, pr_name, pr_sort_order) VALUES ('member',  'ja', 10, '会員価格',      3);
INSERT INTO price_type (pr_id, pr_language_id, pr_kind, pr_name, pr_sort_order) VALUES ('sale1',   'ja', 11, '売上価格1',     4);
INSERT INTO price_type (pr_id, pr_language_id, pr_kind, pr_name, pr_sort_order) VALUES ('sale2',   'ja', 11, '売上価格2',     5);
INSERT INTO price_type (pr_id, pr_language_id, pr_kind, pr_name, pr_sort_order) VALUES ('buying',  'ja', 12, '仕入価格',      6);

-- 商品受注状況マスター
DROP TABLE IF EXISTS order_status;
CREATE TABLE order_status (
    os_id                INT            DEFAULT 0                     NOT NULL,      -- 受注状況ID
    os_language_id       VARCHAR(2)     DEFAULT ''                    NOT NULL,      -- 言語ID
    os_name              VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- 受注状況名
    os_description       VARCHAR(50)    DEFAULT ''                    NOT NULL,      -- 説明
    PRIMARY KEY          (os_id,        os_language_id)
) ENGINE=innodb;
INSERT INTO order_status (os_id, os_language_id, os_name) VALUES (100, 'ja', '見積依頼');
INSERT INTO order_status (os_id, os_language_id, os_name) VALUES (101, 'ja', '見積送付済');
INSERT INTO order_status (os_id, os_language_id, os_name) VALUES (200, 'ja', '注文受付');
INSERT INTO order_status (os_id, os_language_id, os_name) VALUES (300, 'ja', '入金待ち');
INSERT INTO order_status (os_id, os_language_id, os_name) VALUES (301, 'ja', '入金済み');
INSERT INTO order_status (os_id, os_language_id, os_name) VALUES (302, 'ja', '入庫待ち');
INSERT INTO order_status (os_id, os_language_id, os_name) VALUES (303, 'ja', '保留');
INSERT INTO order_status (os_id, os_language_id, os_name) VALUES (400, 'ja', '配送待ち');
INSERT INTO order_status (os_id, os_language_id, os_name) VALUES (401, 'ja', '配送済み');
INSERT INTO order_status (os_id, os_language_id, os_name) VALUES (900, 'ja', '終了');
INSERT INTO order_status (os_id, os_language_id, os_name) VALUES (901, 'ja', 'キャンセル');

-- 支払い方法マスター
DROP TABLE IF EXISTS pay_method_def;
CREATE TABLE pay_method_def (
    po_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    po_id                VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- 支払い方法ID
    po_language_id       VARCHAR(2)     DEFAULT ''                    NOT NULL,      -- 言語ID
    po_set_id            INT            DEFAULT 0                     NOT NULL,      -- セットID(0=デフォルトセット)
    po_history_index     INT            DEFAULT 0                     NOT NULL,      -- 履歴管理用インデックスNo(0～)
    
    po_name              VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- 名称
    po_description       TEXT                                         NOT NULL,      -- 説明
    po_iwidget_id        VARCHAR(50)    DEFAULT ''                    NOT NULL,      -- インナーウィジェットID(ファイル名)
    po_param             TEXT                                         NOT NULL,      -- 設定インナーウィジェット用パラメータ
    po_index             INT            DEFAULT 0                     NOT NULL,      -- 表示順(1～)
    po_visible           BOOLEAN        DEFAULT true                  NOT NULL,      -- 項目を表示するかどうか
    
    po_create_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード作成者
    po_create_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード作成日時
    po_update_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード更新者
    po_update_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード更新日時
    po_deleted           BOOLEAN        DEFAULT false                 NOT NULL,      -- レコード削除状態
    PRIMARY KEY          (po_serial),
    UNIQUE               (po_id,        po_language_id, po_set_id,    po_history_index)
) ENGINE=innodb;
INSERT INTO pay_method_def 
(po_id,     po_language_id, po_name, po_index) VALUES
('payment_service',  'ja', '決済サービス', 1);

-- 配送方法マスター
DROP TABLE IF EXISTS delivery_method_def;
CREATE TABLE delivery_method_def (
    do_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    do_id                VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- 配送方法ID
    do_language_id       VARCHAR(2)     DEFAULT ''                    NOT NULL,      -- 言語ID
    do_set_id            INT            DEFAULT 0                     NOT NULL,      -- セットID(0=デフォルトセット)
    do_history_index     INT            DEFAULT 0                     NOT NULL,      -- 履歴管理用インデックスNo(0～)
    
    do_name              VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- 名称
    do_description       TEXT                                         NOT NULL,      -- 説明
    do_iwidget_id        VARCHAR(50)    DEFAULT ''                    NOT NULL,      -- インナーウィジェットID(ファイル名)
    do_param             TEXT                                         NOT NULL,      -- 設定インナーウィジェット用パラメータ
    do_index             INT            DEFAULT 0                     NOT NULL,      -- 表示順(1～)
    do_visible           BOOLEAN        DEFAULT true                  NOT NULL,      -- 項目を表示するかどうか
    
    do_create_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード作成者
    do_create_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード作成日時
    do_update_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード更新者
    do_update_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード更新日時
    do_deleted           BOOLEAN        DEFAULT false                 NOT NULL,      -- レコード削除状態
    PRIMARY KEY          (do_serial),
    UNIQUE               (do_id,        do_language_id, do_set_id,    do_history_index)
) ENGINE=innodb;
INSERT INTO delivery_method_def (do_id, do_language_id, do_name, do_index) VALUES ('yubin',    'ja', '一般小包郵便物', 1);
INSERT INTO delivery_method_def (do_id, do_language_id, do_name, do_index) VALUES ('takuhai',  'ja', '宅配',           2);

-- 会員情報マスター
DROP TABLE IF EXISTS shop_member;
CREATE TABLE shop_member (
    sm_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    sm_id                INT            DEFAULT 0                     NOT NULL,      -- 会員情報ID
    sm_history_index     INT            DEFAULT 0                     NOT NULL,      -- 履歴管理用インデックスNo(0～)
    
    sm_language_id       VARCHAR(2)     DEFAULT ''                    NOT NULL,      -- 対応言語ID
    sm_type              SMALLINT       DEFAULT 0                     NOT NULL,      -- 会員種別(0=未設定、1=個人、2=法人)
    sm_company_info_id   INT            DEFAULT 0                     NOT NULL,      -- 法人情報ID
    sm_person_info_id    INT            DEFAULT 0                     NOT NULL,      -- 個人情報ID
    sm_customer_id       INT            DEFAULT 0                     NOT NULL,      -- 所属取引先
    sm_member_no         VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- 会員No(任意)
    sm_login_user_id     INT            DEFAULT 0                     NOT NULL,      -- ログインユーザID
    sm_point             INT            DEFAULT 0                     NOT NULL,      -- 取得ポイント
    sm_trade             BOOLEAN        DEFAULT false                 NOT NULL,      -- 業者向け画面
    sm_newsletter        BOOLEAN        DEFAULT false                 NOT NULL,      -- メールニュース配信
    sm_direct_mail       BOOLEAN        DEFAULT false                 NOT NULL,      -- ダイレクトメール配信
        
    sm_create_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード作成者
    sm_create_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード作成日時
    sm_update_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード更新者
    sm_update_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード更新日時
    sm_deleted           BOOLEAN        DEFAULT false                 NOT NULL,      -- レコード削除状態
    PRIMARY KEY          (sm_serial),
    UNIQUE               (sm_id,        sm_history_index)
) ENGINE=innodb;

-- 仮会員情報マスター
DROP TABLE IF EXISTS shop_tmp_member;
CREATE TABLE shop_tmp_member (
    sb_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    
    sb_language_id       VARCHAR(2)     DEFAULT ''                    NOT NULL,      -- 対応言語ID
    sb_type              SMALLINT       DEFAULT 0                     NOT NULL,      -- 会員種別(0=未設定、1=個人、2=法人)
    sb_company_info_id   INT            DEFAULT 0                     NOT NULL,      -- 法人情報ID
    sb_person_info_id    INT            DEFAULT 0                     NOT NULL,      -- 個人情報ID
    sb_login_user_id     INT            DEFAULT 0                     NOT NULL,      -- ログインユーザID
    
    sb_create_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード作成者
    sb_create_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード作成日時
    sb_update_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード更新者
    sb_update_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード更新日時
    sb_deleted           BOOLEAN        DEFAULT false                 NOT NULL,      -- レコード削除状態
    PRIMARY KEY          (sb_serial)
) ENGINE=innodb;

-- 個人情報マスター
DROP TABLE IF EXISTS person_info;
CREATE TABLE person_info (
    pi_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    pi_id                INT            DEFAULT 0                     NOT NULL,      -- 個人情報ID
    pi_language_id       VARCHAR(2)     DEFAULT ''                    NOT NULL,      -- 言語ID
    pi_history_index     INT            DEFAULT 0                     NOT NULL,      -- 履歴管理用インデックスNo(0～)

    pi_family_name       VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- ユーザ名(姓)漢字
    pi_first_name        VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- ユーザ名(名)漢字
    pi_family_name_kana  VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- ユーザ名(姓)カナ
    pi_first_name_kana   VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- ユーザ名(名)カナ
    pi_gender            SMALLINT       DEFAULT 0                     NOT NULL,      -- 性別(0=未設定、1=男、2=女)
    pi_birthday          DATE           DEFAULT '0000-00-00'          NOT NULL,      -- 誕生日(西暦)
    pi_email             VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- Eメールアドレス
    pi_mobile            VARCHAR(30)    DEFAULT ''                    NOT NULL,      -- 携帯電話
    pi_address_id        INT            DEFAULT 0                     NOT NULL,      -- 住所ID

    pi_create_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード作成者
    pi_create_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード作成日時
    pi_update_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード更新者
    pi_update_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード更新日時
    pi_deleted           BOOLEAN        DEFAULT false                 NOT NULL,      -- レコード削除状態
    PRIMARY KEY          (pi_serial),
    UNIQUE               (pi_id,        pi_language_id,               pi_history_index)
) ENGINE=innodb;

-- 個人情報追加フィールド
DROP TABLE IF EXISTS person_info_opt_field;
CREATE TABLE person_info_opt_field (
    pf_id                VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- フィールドID
    pf_language_id       VARCHAR(2)     DEFAULT ''                    NOT NULL,      -- 言語ID
    pf_name              VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- 名称
    pf_field_type        VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- フィールドタイプ
    pf_index             INT            DEFAULT 0                     NOT NULL,      -- ソート用
    PRIMARY KEY          (pf_id,        pf_language_id)
) ENGINE=innodb;

-- 個人情報追加フィールド値
DROP TABLE IF EXISTS person_info_opt_value;
CREATE TABLE person_info_opt_value (
    pl_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    pl_person_serial     INT            DEFAULT 0                     NOT NULL,      -- 個人情報シリアル番号
    pl_field_id          VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- 個人情報追加フィールドID
    pl_value             TEXT                                         NOT NULL,      -- 値
    PRIMARY KEY          (pl_serial),
    UNIQUE               (pl_person_serial, pl_field_id)
) ENGINE=innodb;

-- 法人情報マスター
DROP TABLE IF EXISTS company_info;
CREATE TABLE company_info (
    ci_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    ci_id                INT            DEFAULT 0                     NOT NULL,      -- 法人情報ID
    ci_language_id       VARCHAR(2)     DEFAULT ''                    NOT NULL,      -- 言語ID
    ci_history_index     INT            DEFAULT 0                     NOT NULL,      -- 履歴管理用インデックスNo(0～)
    
    ci_type              INT            DEFAULT 0                     NOT NULL,      -- レコードタイプ(0=会社全体、1=部署)
    ci_parent_id         INT            DEFAULT 0                     NOT NULL,      -- 親法人情報ID
    ci_name              VARCHAR(100)   DEFAULT ''                    NOT NULL,      -- 法人名または部署名漢字
    ci_name_kana         VARCHAR(100)   DEFAULT ''                    NOT NULL,      -- 法人名または部署名カナ
    ci_bussiness_type_id INT            DEFAULT 0                     NOT NULL,      -- 業種ID
    ci_found_dt          DATE           DEFAULT '0000-00-00'          NOT NULL,      -- 設立日(西暦)
    ci_address_id        INT            DEFAULT 0                     NOT NULL,      -- 住所ID

    ci_create_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード作成者
    ci_create_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード作成日時
    ci_update_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード更新者
    ci_update_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード更新日時
    ci_deleted           BOOLEAN        DEFAULT false                 NOT NULL,      -- レコード削除状態
    PRIMARY KEY          (ci_serial),
    UNIQUE               (ci_id,        ci_language_id,               ci_history_index)
) ENGINE=innodb;

-- 住所マスター
DROP TABLE IF EXISTS address;
CREATE TABLE address (
    ad_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    ad_id                INT            DEFAULT 0                     NOT NULL,      -- 住所ID
    ad_language_id       VARCHAR(2)     DEFAULT ''                    NOT NULL,      -- 言語ID
    ad_history_index     INT            DEFAULT 0                     NOT NULL,      -- 履歴管理用インデックスNo(0～)
    
    ad_title             VARCHAR(30)    DEFAULT ''                    NOT NULL,      -- 表示タイトル
    ad_zipcode           VARCHAR(10)    DEFAULT ''                    NOT NULL,      -- 郵便番号(7桁)
    ad_state_id          INT            DEFAULT 0                     NOT NULL,      -- 都道府県、州(geo_zoneテーブル)
    ad_address1          VARCHAR(100)   DEFAULT ''                    NOT NULL,      -- 市区町村
    ad_address2          VARCHAR(60)    DEFAULT ''                    NOT NULL,      -- ビル・マンション名等
    ad_phone             VARCHAR(30)    DEFAULT ''                    NOT NULL,      -- 電話番号
    ad_fax               VARCHAR(30)    DEFAULT ''                    NOT NULL,      -- FAX
    ad_country_id        VARCHAR(3)     DEFAULT ''                    NOT NULL,      -- 国ID
    
    ad_create_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード作成者
    ad_create_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード作成日時
    ad_update_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード更新者
    ad_update_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード更新日時
    ad_deleted           BOOLEAN        DEFAULT false                 NOT NULL,      -- レコード削除状態
    PRIMARY KEY          (ad_serial),
    UNIQUE               (ad_id,        ad_language_id,               ad_history_index)
) ENGINE=innodb;

-- 取引先マスター
DROP TABLE IF EXISTS customer;
CREATE TABLE customer (
    cc_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    cc_id                INT            DEFAULT 0                     NOT NULL,      -- 取引先ID
    cc_history_index     INT            DEFAULT 0                     NOT NULL,      -- 履歴管理用インデックスNo(0～)

    cc_data_type         SMALLINT       DEFAULT 0                     NOT NULL,      -- 取引先のタイプ(0=法人、1=個人)
    cc_detail_id         INT            DEFAULT 0                     NOT NULL,      -- 詳細情報(取引先のタイプに応じて参照するテーブルが異なる)
    cc_name              VARCHAR(50)    DEFAULT ''                    NOT NULL,      -- 取引先名
    cc_short_name        VARCHAR(10)    DEFAULT ''                    NOT NULL,      -- 取引先名略称

    cc_is_custmer        BOOLEAN        DEFAULT false                 NOT NULL,      -- 「得意先(顧客)」属性の有無
    cc_is_supplier       BOOLEAN        DEFAULT false                 NOT NULL,      -- 「仕入先」属性の有無
    cc_is_delivery       BOOLEAN        DEFAULT false                 NOT NULL,      -- 「出荷先」属性の有無
    cc_is_payment        BOOLEAN        DEFAULT false                 NOT NULL,      -- 「支払先」属性の有無
    cc_is_billing        BOOLEAN        DEFAULT false                 NOT NULL,      -- 「請求先」属性の有無
    cc_sort_order        SMALLINT       DEFAULT 0                     NOT NULL,      -- ソート順

    cc_create_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード作成者
    cc_create_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード作成日時
    cc_update_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード更新者
    cc_update_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード更新日時
    cc_deleted           BOOLEAN        DEFAULT false                 NOT NULL,      -- レコード削除状態
    PRIMARY KEY          (cc_serial),
    UNIQUE               (cc_id,        cc_history_index)
) ENGINE=innodb;

-- 商品価格マスター
DROP TABLE IF EXISTS product_price;
CREATE TABLE product_price (
    pp_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    pp_product_class     VARCHAR(10)    DEFAULT ''                    NOT NULL,      -- 商品クラス
    pp_product_id        INT            DEFAULT 0                     NOT NULL,      -- 商品ID
    pp_product_type_id   VARCHAR(10)    DEFAULT ''                    NOT NULL,      -- 商品タイプ
    pp_language_id       VARCHAR(2)     DEFAULT ''                    NOT NULL,      -- 言語ID
    pp_price_type_id     VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- 価格の種別ID(price_typeテーブル)
    pp_history_index     INT            DEFAULT 0                     NOT NULL,      -- 履歴管理用インデックスNo(0～)
        
    pp_currency_id       VARCHAR(3)     DEFAULT ''                    NOT NULL,      -- 通貨種別
    pp_price             DECIMAL(15,4)  DEFAULT 0                     NOT NULL,      -- 単価(税抜)
    pp_active_start_dt   TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- 有効期限開始日時
    pp_active_end_dt     TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- 有効期限終了日時

    pp_create_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード作成者
    pp_create_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード作成日時
    pp_update_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード更新者
    pp_update_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード更新日時
    pp_deleted           BOOLEAN        DEFAULT false                 NOT NULL,      -- レコード削除状態
    PRIMARY KEY          (pp_serial),
    UNIQUE               (pp_product_class,     pp_product_id,    pp_product_type_id,    pp_language_id,      pp_price_type_id,      pp_history_index)
) ENGINE=innodb;

-- 商品情報マスター
DROP TABLE IF EXISTS product;
CREATE TABLE product (
    pt_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    pt_id                INT            DEFAULT 0                     NOT NULL,      -- 商品ID
    pt_language_id       VARCHAR(2)     DEFAULT ''                    NOT NULL,      -- 言語ID
    pt_history_index     INT            DEFAULT 0                     NOT NULL,      -- 履歴管理用インデックスNo(0～)

    pt_name              VARCHAR(80)    DEFAULT ''                    NOT NULL,      -- 商品名称
    pt_code              VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- 商品コード
    pt_product_type      SMALLINT       DEFAULT 0                     NOT NULL,      -- 商品タイプ(1=単品商品(親子なし)、2=単品商品(親子)、10=セット商品、20=オプション商品)
    pt_description       TEXT                                         NOT NULL,      -- 商品説明
    pt_description_short VARCHAR(100)   DEFAULT ''                    NOT NULL,      -- 商品説明(簡易)
    pt_admin_note        VARCHAR(100)   DEFAULT ''                    NOT NULL,      -- 管理者用備考
    pt_category_id       INT            DEFAULT 0                     NOT NULL,      -- 商品カテゴリーID(廃止)
    pt_related_product   TEXT                                         NOT NULL,      -- 関連商品ID(,区切り)
    pt_manufacturer_id   INT            DEFAULT 0                     NOT NULL,      -- メーカーID
    pt_sort_order        INT            DEFAULT 0                     NOT NULL,      -- ソート用
    pt_default_price     VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- 優先する価格タイプ
    pt_visible           BOOLEAN        DEFAULT true                  NOT NULL,      -- 表示するかどうか
    pt_meta_title        TEXT                                         NOT NULL,      -- METAタグ、タイトル
    pt_meta_description  TEXT                                         NOT NULL,      -- METAタグ、ページ要約
    pt_meta_keywords     TEXT                                         NOT NULL,      -- METAタグ、検索用キーワード
    pt_search_keyword    VARCHAR(50)    DEFAULT ''                    NOT NULL,      -- 検索キーワード(「,」区切り)
    pt_site_url          TEXT                                         NOT NULL,      -- 詳細情報のサイト
    pt_unit_type_id      VARCHAR(4)     DEFAULT ''                    NOT NULL,      -- 販売の基準となる単位種別
    pt_unit_quantity     DECIMAL(5,2)   DEFAULT 0                     NOT NULL,      -- 1販売単位となる数量
    pt_innner_quantity   INT            DEFAULT 0                     NOT NULL,      -- 1販売単位に含まれる製品数量(入数)
    pt_quantity_decimal  INT            DEFAULT 0                     NOT NULL,      -- 数量小数桁
    pt_price_decimal     INT            DEFAULT 0                     NOT NULL,      -- 単価小数桁
    pt_weight            DECIMAL(5,2)   DEFAULT 0                     NOT NULL,      -- 重量(kg)-配送時の送料算出に使用
    pt_deliv_type        VARCHAR(10)    DEFAULT ''                    NOT NULL,      -- 配送料金の計算方法
    pt_deliv_size        VARCHAR(10)    DEFAULT ''                    NOT NULL,      -- 商品サイズ-送料計算に使用
    pt_deliv_fee         DECIMAL(15,4)  DEFAULT 0                     NOT NULL,      -- 商品単位送料-送料計算に使用
    pt_tax_type_id       VARCHAR(10)    DEFAULT ''                    NOT NULL,      -- 税種別ID
    
    pt_attr_menu         TEXT                                         NOT NULL,      -- 商品タイプが単品商品(親子なし)の場合の商品属性選択メニュー(メニュー間区切り「;」、メニュー内区切り「,」)
    pt_parent_id         INT            DEFAULT 0                     NOT NULL,      -- 商品タイプが単品商品(親子)の場合の商品親ID
    pt_attr_condition    TEXT                                         NOT NULL,      -- 商品タイプが単品商品(親子)の場合の商品属性の条件(,区切り)
    pt_product_set       TEXT                                         NOT NULL,      -- 商品タイプがセット商品の場合の組み合わせ商品ID(,区切り)
    pt_option_price      VARCHAR(10)    DEFAULT ''                    NOT NULL,      -- 商品タイプがオプション商品の場合の価格設定
    
    pt_create_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード作成者
    pt_create_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード作成日時
    pt_update_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード更新者
    pt_update_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード更新日時
    pt_deleted           BOOLEAN        DEFAULT false                 NOT NULL,      -- レコード削除状態
    PRIMARY KEY          (pt_serial),
    UNIQUE               (pt_id,        pt_language_id,               pt_history_index)
) ENGINE=innodb;

-- フォト商品情報マスター
DROP TABLE IF EXISTS photo_product;
CREATE TABLE photo_product (
    hp_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    hp_id                INT            DEFAULT 0                     NOT NULL,      -- 商品ID
    hp_language_id       VARCHAR(2)     DEFAULT ''                    NOT NULL,      -- 言語ID
    hp_history_index     INT            DEFAULT 0                     NOT NULL,      -- 履歴管理用インデックスNo(0～)

    hp_name              VARCHAR(80)    DEFAULT ''                    NOT NULL,      -- 商品名称
    hp_code              VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- 商品コード
    hp_product_type      SMALLINT       DEFAULT 0                     NOT NULL,      -- 商品タイプ(1=単品商品(親子なし)、2=単品商品(親子)、10=セット商品、20=オプション商品)
    hp_description       TEXT                                         NOT NULL,      -- 商品説明
    hp_description_short VARCHAR(100)   DEFAULT ''                    NOT NULL,      -- 商品説明(簡易)
    hp_admin_note        VARCHAR(100)   DEFAULT ''                    NOT NULL,      -- 管理者用備考
    hp_related_product   TEXT                                         NOT NULL,      -- 関連商品ID(,区切り)
    hp_manufacturer_id   INT            DEFAULT 0                     NOT NULL,      -- メーカーID
    hp_sort_order        INT            DEFAULT 0                     NOT NULL,      -- ソート用
    hp_default_price     VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- 優先する価格タイプ
    hp_visible           BOOLEAN        DEFAULT true                  NOT NULL,      -- 表示するかどうか
    hp_meta_title        TEXT                                         NOT NULL,      -- METAタグ、タイトル
    hp_meta_description  TEXT                                         NOT NULL,      -- METAタグ、ページ要約
    hp_meta_keywords     TEXT                                         NOT NULL,      -- METAタグ、検索用キーワード
    hp_search_keyword    VARCHAR(50)    DEFAULT ''                    NOT NULL,      -- 検索キーワード(「,」区切り)
    hp_site_url          TEXT                                         NOT NULL,      -- 詳細情報のサイト
    hp_unit_type_id      VARCHAR(4)     DEFAULT ''                    NOT NULL,      -- 販売の基準となる単位種別
    hp_unit_quantity     DECIMAL(5,2)   DEFAULT 0                     NOT NULL,      -- 1販売単位となる数量
    hp_innner_quantity   INT            DEFAULT 0                     NOT NULL,      -- 1販売単位に含まれる製品数量(入数)
    hp_quantity_decimal  INT            DEFAULT 0                     NOT NULL,      -- 数量小数桁
    hp_price_decimal     INT            DEFAULT 0                     NOT NULL,      -- 単価小数桁
    hp_weight            DECIMAL(5,2)   DEFAULT 0                     NOT NULL,      -- 重量(kg)-配送時の送料算出に使用
    hp_deliv_type        VARCHAR(10)    DEFAULT ''                    NOT NULL,      -- 配送料金の計算方法
    hp_deliv_size        VARCHAR(10)    DEFAULT ''                    NOT NULL,      -- 商品サイズ-送料計算に使用
    hp_deliv_fee         DECIMAL(15,4)  DEFAULT 0                     NOT NULL,      -- 商品単位送料-送料計算に使用
    hp_tax_type_id       VARCHAR(10)    DEFAULT ''                    NOT NULL,      -- 税種別ID
    
    hp_attr_menu         TEXT                                         NOT NULL,      -- 商品タイプが単品商品(親子なし)の場合の商品属性選択メニュー(メニュー間区切り「;」、メニュー内区切り「,」)
    hp_parent_id         INT            DEFAULT 0                     NOT NULL,      -- 商品タイプが単品商品(親子)の場合の商品親ID
    hp_attr_condition    TEXT                                         NOT NULL,      -- 商品タイプが単品商品(親子)の場合の商品属性の条件(,区切り)
    hp_product_set       TEXT                                         NOT NULL,      -- 商品タイプがセット商品の場合の組み合わせ商品ID(,区切り)
    hp_option_price      VARCHAR(10)    DEFAULT ''                    NOT NULL,      -- 商品タイプがオプション商品の場合の価格設定
    
    hp_create_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード作成者
    hp_create_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード作成日時
    hp_update_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード更新者
    hp_update_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード更新日時
    hp_deleted           BOOLEAN        DEFAULT false                 NOT NULL,      -- レコード削除状態
    PRIMARY KEY          (hp_serial),
    UNIQUE               (hp_id,        hp_language_id,               hp_history_index)
) ENGINE=innodb;

-- 商品タイプマスター
DROP TABLE IF EXISTS product_type;
CREATE TABLE product_type (
    py_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    py_product_class     VARCHAR(10)    DEFAULT ''                    NOT NULL,      -- 商品クラス
    py_id                VARCHAR(10)    DEFAULT ''                    NOT NULL,      -- 商品タイプID
    py_language_id       VARCHAR(2)     DEFAULT ''                    NOT NULL,      -- 言語ID
    py_history_index     INT            DEFAULT 0                     NOT NULL,      -- 履歴管理用インデックスNo(0～)
    
    py_name              VARCHAR(30)    DEFAULT ''                    NOT NULL,      -- 名称
    py_code              VARCHAR(10)    DEFAULT ''                    NOT NULL,      -- 商品タイプコード
    py_description       VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- 説明
    py_index             INT            DEFAULT 0                     NOT NULL,      -- 項目順(1～)
    py_single_select     BOOLEAN        DEFAULT false                 NOT NULL,      -- 単数選択のみ

    py_create_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード作成者
    py_create_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード作成日時
    py_update_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード更新者
    py_update_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード更新日時
    py_deleted           BOOLEAN        DEFAULT false                 NOT NULL,      -- レコード削除状態
    PRIMARY KEY          (py_serial),
    UNIQUE               (py_product_class,     py_id,        py_language_id,               py_history_index)
) ENGINE=innodb;
INSERT INTO product_type
(py_product_class, py_id,      py_language_id, py_name,            py_code, py_description, py_index, py_single_select) VALUES 
('',               '',         'ja',           '標準商品',         'ST',    '',             1,             false),
('photo',          '',         'ja',           '標準商品',         'ST',    '',             1,             false),
('photo',          'download', 'ja',           'ダウンロード画像', 'DL',    '',             2,             true);

-- 商品ステータス種別マスター
DROP TABLE IF EXISTS product_status_type;
CREATE TABLE product_status_type (
    pa_id                VARCHAR(10)    DEFAULT ''                    NOT NULL,      -- 識別子
    pa_language_id       VARCHAR(2)     DEFAULT ''                    NOT NULL,      -- 言語ID
    pa_name              VARCHAR(30)    DEFAULT ''                    NOT NULL,      -- 正式名称
    pa_description       VARCHAR(50)    DEFAULT ''                    NOT NULL,      -- 説明
    pa_data_type         INT            DEFAULT 0                     NOT NULL,      -- データ型(0=bool,1=int,2=string)
    pa_priority          INT            DEFAULT 0                     NOT NULL,      -- 優先度(0～)
    PRIMARY KEY          (pa_id,        pa_language_id)
) ENGINE=innodb;
INSERT INTO product_status_type (pa_id, pa_language_id, pa_name, pa_priority) VALUES ('new',     'ja', '新着',       0);
INSERT INTO product_status_type (pa_id, pa_language_id, pa_name, pa_priority) VALUES ('suggest', 'ja', 'おすすめ',   1);
INSERT INTO product_status_type (pa_id, pa_language_id, pa_name, pa_priority) VALUES ('few',     'ja', '残りわずか', 2);
INSERT INTO product_status_type (pa_id, pa_language_id, pa_name, pa_priority) VALUES ('limited', 'ja', '限定品',     3);

-- ショッピングカートトラン
DROP TABLE IF EXISTS shop_cart;
CREATE TABLE shop_cart (
    sh_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    sh_id                CHAR(32)       DEFAULT ''                    NOT NULL,      -- 買い物かごID
    sh_language_id       VARCHAR(2)     DEFAULT ''                    NOT NULL,      -- 言語ID
    sh_user_id           INT            DEFAULT 0                     NOT NULL,      -- ユーザが特定できた場合のユーザID
    sh_dt                TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- カートの更新日時
    PRIMARY KEY          (sh_serial),
    UNIQUE               (sh_id,        sh_language_id)
) ENGINE=innodb;

-- ショッピングカート商品項目
DROP TABLE IF EXISTS shop_cart_item;
CREATE TABLE shop_cart_item (
    si_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    si_head_serial       INT            DEFAULT 0                     NOT NULL,      -- ショッピングカートトランシリアル番号
    si_product_class     VARCHAR(10)    DEFAULT ''                    NOT NULL,      -- 商品クラス
    si_product_id        INT            DEFAULT 0                     NOT NULL,      -- 商品ID
    si_product_type_id   VARCHAR(10)    DEFAULT ''                    NOT NULL,      -- 商品タイプ
    si_currency_id       VARCHAR(3)     DEFAULT ''                    NOT NULL,      -- 通貨種別
    si_quantity          INT            DEFAULT 0                     NOT NULL,      -- 数量
    si_subtotal          DECIMAL(15,4)  DEFAULT 0                     NOT NULL,      -- 税込み小計
    si_active            BOOLEAN        DEFAULT true                  NOT NULL,      -- 購入対象かどうか
    si_available         BOOLEAN        DEFAULT true                  NOT NULL,      -- データ有効性
    PRIMARY KEY          (si_serial),
    UNIQUE               (si_head_serial, si_product_class,     si_product_id,    si_product_type_id)
) ENGINE=innodb;

-- 商品ステータスマスター
DROP TABLE IF EXISTS product_status;
CREATE TABLE product_status (
    ps_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    ps_id                INT            DEFAULT 0                     NOT NULL,      -- 商品ID
    ps_language_id       VARCHAR(2)     DEFAULT ''                    NOT NULL,      -- 言語ID
    ps_type              VARCHAR(10)    DEFAULT ''                    NOT NULL,      -- 商品ステータス種別ID
    ps_history_index     INT            DEFAULT 0                     NOT NULL,      -- 履歴管理用インデックスNo(0～)

    ps_value             VARCHAR(10)    DEFAULT ''                    NOT NULL,      -- 値

    ps_create_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード作成者
    ps_create_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード作成日時
    ps_update_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード更新者
    ps_update_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード更新日時
    ps_deleted           BOOLEAN        DEFAULT false                 NOT NULL,      -- レコード削除状態
    PRIMARY KEY          (ps_serial),
    UNIQUE               (ps_id,        ps_language_id,               ps_type,          ps_history_index)
) ENGINE=innodb;

-- 商品販売ステータスマスター
DROP TABLE IF EXISTS sale_status;
CREATE TABLE sale_status (
    sa_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    sa_id                INT            DEFAULT 0                     NOT NULL,      -- 履歴管理用レコードID
    sa_history_index     INT            DEFAULT 0                     NOT NULL,      -- 履歴管理用インデックスNo(0～)
    sa_status            INT            DEFAULT 0                     NOT NULL,      -- 販売ステータス
    sa_create_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード作成者
    sa_create_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード作成日時
    sa_update_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード更新者
    sa_update_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード更新日時
    sa_deleted           BOOLEAN        DEFAULT false                 NOT NULL,      -- レコード削除状態
    PRIMARY KEY          (sa_serial),
    UNIQUE               (sa_id,        sa_history_index)
) ENGINE=innodb;

-- 商品注文書トラン
DROP TABLE IF EXISTS order_sheet;
CREATE TABLE order_sheet (
    oe_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    oe_user_id           INT            DEFAULT 0                     NOT NULL,      -- 対象ユーザ
    oe_language_id       VARCHAR(2)     DEFAULT ''                    NOT NULL,      -- 受注言語
    
    oe_client_id         CHAR(32)       DEFAULT ''                    NOT NULL,      -- クライアントID
    oe_custm_id          INT            DEFAULT 0                     NOT NULL,      -- 得意先(顧客)ID(参照用)、会員情報のときは-を付ける
    oe_custm_name        VARCHAR(50)    DEFAULT ''                    NOT NULL,      -- 得意先(顧客)名
    oe_custm_name_kana   VARCHAR(50)    DEFAULT ''                    NOT NULL,      -- 得意先(顧客)名(カナ)
    oe_custm_person      VARCHAR(50)    DEFAULT ''                    NOT NULL,      -- 得意先(顧客)担当者名
    oe_custm_person_kana VARCHAR(50)    DEFAULT ''                    NOT NULL,      -- 得意先(顧客)担当者名(カナ)
    oe_custm_zipcode     VARCHAR(10)    DEFAULT ''                    NOT NULL,      -- 郵便番号(7桁)
    oe_custm_state_id    INT            DEFAULT 0                     NOT NULL,      -- 都道府県、州(geo_zoneテーブル)
    oe_custm_address1    VARCHAR(100)   DEFAULT ''                    NOT NULL,      -- 市区町村
    oe_custm_address2    VARCHAR(60)    DEFAULT ''                    NOT NULL,      -- ビル・マンション名等
    oe_custm_phone       VARCHAR(30)    DEFAULT ''                    NOT NULL,      -- 電話番号
    oe_custm_fax         VARCHAR(30)    DEFAULT ''                    NOT NULL,      -- FAX
    oe_custm_email       VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- Eメールアドレス
    oe_custm_country_id  VARCHAR(3)     DEFAULT ''                    NOT NULL,      -- 国ID
    
    oe_deliv_id          INT            DEFAULT 0                     NOT NULL,      -- 出荷先ID(参照用)
    oe_deliv_name        VARCHAR(50)    DEFAULT ''                    NOT NULL,      -- 出荷先名
    oe_deliv_name_kana   VARCHAR(50)    DEFAULT ''                    NOT NULL,      -- 出荷先名(カナ)
    oe_deliv_person      VARCHAR(50)    DEFAULT ''                    NOT NULL,      -- 出荷先担当者名
    oe_deliv_person_kana VARCHAR(50)    DEFAULT ''                    NOT NULL,      -- 出荷先担当者名(カナ)
    oe_deliv_zipcode     VARCHAR(10)    DEFAULT ''                    NOT NULL,      -- 郵便番号(7桁)
    oe_deliv_state_id    INT            DEFAULT 0                     NOT NULL,      -- 都道府県、州(geo_zoneテーブル)
    oe_deliv_address1    VARCHAR(100)   DEFAULT ''                    NOT NULL,      -- 市区町村
    oe_deliv_address2    VARCHAR(60)    DEFAULT ''                    NOT NULL,      -- ビル・マンション名等
    oe_deliv_phone       VARCHAR(30)    DEFAULT ''                    NOT NULL,      -- 電話番号
    oe_deliv_fax         VARCHAR(30)    DEFAULT ''                    NOT NULL,      -- FAX
    oe_deliv_email       VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- Eメールアドレス
    oe_deliv_country_id  VARCHAR(3)     DEFAULT ''                    NOT NULL,      -- 国ID

    oe_bill_id           INT            DEFAULT 0                     NOT NULL,      -- 請求先ID(参照用)
    oe_bill_name         VARCHAR(50)    DEFAULT ''                    NOT NULL,      -- 請求先名
    oe_bill_name_kana    VARCHAR(50)    DEFAULT ''                    NOT NULL,      -- 請求先名(カナ)
    oe_bill_person       VARCHAR(50)    DEFAULT ''                    NOT NULL,      -- 請求先担当者名
    oe_bill_person_kana  VARCHAR(50)    DEFAULT ''                    NOT NULL,      -- 請求先担当者名(カナ)
    oe_bill_zipcode      VARCHAR(10)    DEFAULT ''                    NOT NULL,      -- 郵便番号(7桁)
    oe_bill_state_id     INT            DEFAULT 0                     NOT NULL,      -- 都道府県、州(geo_zoneテーブル)
    oe_bill_address1     VARCHAR(100)   DEFAULT ''                    NOT NULL,      -- 市区町村
    oe_bill_address2     VARCHAR(60)    DEFAULT ''                    NOT NULL,      -- ビル・マンション名等
    oe_bill_phone        VARCHAR(30)    DEFAULT ''                    NOT NULL,      -- 電話番号
    oe_bill_fax          VARCHAR(30)    DEFAULT ''                    NOT NULL,      -- FAX
    oe_bill_email        VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- Eメールアドレス
    oe_bill_country_id   VARCHAR(3)     DEFAULT ''                    NOT NULL,      -- 国ID

    oe_deliv_method_id   VARCHAR(50)    DEFAULT ''                    NOT NULL,      -- 配送方法
    oe_pay_method_id     VARCHAR(50)    DEFAULT ''                    NOT NULL,      -- 支払い方法
    oe_card_type         VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- クレジットカードタイプ
    oe_card_owner        VARCHAR(64)    DEFAULT ''                    NOT NULL,      -- クレジットカード所有者
    oe_card_number       VARCHAR(32)    DEFAULT ''                    NOT NULL,      -- クレジットカード番号
    oe_card_expires      VARCHAR(4)     DEFAULT ''                    NOT NULL,      -- クレジットカード期限

    oe_message           TEXT                                         NOT NULL,      -- 取引先からのメッセージ
    oe_note              TEXT                                         NOT NULL,      -- 補足情報
    oe_option_fields     TEXT                                         NOT NULL,      -- 追加フィールド(「,」区切り)
    oe_demand_dt         DATE           DEFAULT '0000-00-00'          NOT NULL,      -- 希望納期(日付)
    oe_demand_time       TEXT                                         NOT NULL,      -- 希望納期(時間帯)
    oe_appoint_dt        DATE           DEFAULT '0000-00-00'          NOT NULL,      -- 予定納期
    oe_discount_desc     TEXT                                         NOT NULL,      -- 値引き説明
    oe_status            INT            DEFAULT 0                     NOT NULL,      -- 注文書状態(0=通常、1=オンライン処理中)
    oe_session           CHAR(32)       DEFAULT ''                    NOT NULL,      -- セッションID
    
    oe_currency_id       VARCHAR(3)     DEFAULT ''                    NOT NULL,      -- 通貨ID
    oe_subtotal          DECIMAL(15,4)  DEFAULT 0                     NOT NULL,      -- 商品総額
    oe_discount          DECIMAL(15,4)  DEFAULT 0                     NOT NULL,      -- 値引き額
    oe_deliv_fee         DECIMAL(15,4)  DEFAULT 0                     NOT NULL,      -- 配送料
    oe_charge            DECIMAL(15,4)  DEFAULT 0                     NOT NULL,      -- 手数料
    oe_total             DECIMAL(15,4)  DEFAULT 0                     NOT NULL,      -- 支払い総額

    oe_update_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード更新者
    oe_update_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード更新日時
    PRIMARY KEY          (oe_serial),
    UNIQUE               (oe_user_id,   oe_language_id)
) ENGINE=innodb;

-- 商品受注トラン
DROP TABLE IF EXISTS order_header;
CREATE TABLE order_header (
    or_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    or_id                INT            DEFAULT 0                     NOT NULL,      -- 履歴管理用レコードID
    or_history_index     INT            DEFAULT 0                     NOT NULL,      -- 履歴管理用インデックスNo(0～)
    or_user_id           INT            DEFAULT 0                     NOT NULL,      -- 対象ユーザ
    or_language_id       VARCHAR(2)     DEFAULT ''                    NOT NULL,      -- 受注言語
    or_order_no          VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- 注文番号(任意)
    or_order_type        INT            DEFAULT 0                     NOT NULL,      -- 注文種別(0=一般会員)
    
    or_custm_id          INT            DEFAULT 0                     NOT NULL,      -- 得意先(顧客)ID(参照用)
    or_custm_name        VARCHAR(50)    DEFAULT ''                    NOT NULL,      -- 得意先(顧客)名
    or_custm_name_kana   VARCHAR(50)    DEFAULT ''                    NOT NULL,      -- 得意先(顧客)名(カナ)
    or_custm_person      VARCHAR(50)    DEFAULT ''                    NOT NULL,      -- 得意先(顧客)担当者名
    or_custm_person_kana VARCHAR(50)    DEFAULT ''                    NOT NULL,      -- 得意先(顧客)担当者名(カナ)
    or_custm_zipcode     VARCHAR(10)    DEFAULT ''                    NOT NULL,      -- 郵便番号(7桁)
    or_custm_state_id    INT            DEFAULT 0                     NOT NULL,      -- 都道府県、州(geo_zoneテーブル)
    or_custm_address1    VARCHAR(100)   DEFAULT ''                    NOT NULL,      -- 市区町村
    or_custm_address2    VARCHAR(60)    DEFAULT ''                    NOT NULL,      -- ビル・マンション名等
    or_custm_phone       VARCHAR(30)    DEFAULT ''                    NOT NULL,      -- 電話番号
    or_custm_fax         VARCHAR(30)    DEFAULT ''                    NOT NULL,      -- FAX
    or_custm_email       VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- Eメールアドレス
    or_custm_country_id  VARCHAR(3)     DEFAULT ''                    NOT NULL,      -- 国ID
    
    or_deliv_id          INT            DEFAULT 0                     NOT NULL,      -- 出荷先ID(参照用)
    or_deliv_name        VARCHAR(50)    DEFAULT ''                    NOT NULL,      -- 出荷先名
    or_deliv_name_kana   VARCHAR(50)    DEFAULT ''                    NOT NULL,      -- 出荷先名(カナ)
    or_deliv_person      VARCHAR(50)    DEFAULT ''                    NOT NULL,      -- 出荷先担当者名
    or_deliv_person_kana VARCHAR(50)    DEFAULT ''                    NOT NULL,      -- 出荷先担当者名(カナ)
    or_deliv_zipcode     VARCHAR(10)    DEFAULT ''                    NOT NULL,      -- 郵便番号(7桁)
    or_deliv_state_id    INT            DEFAULT 0                     NOT NULL,      -- 都道府県、州(geo_zoneテーブル)
    or_deliv_address1    VARCHAR(100)   DEFAULT ''                    NOT NULL,      -- 市区町村
    or_deliv_address2    VARCHAR(60)    DEFAULT ''                    NOT NULL,      -- ビル・マンション名等
    or_deliv_phone       VARCHAR(30)    DEFAULT ''                    NOT NULL,      -- 電話番号
    or_deliv_fax         VARCHAR(30)    DEFAULT ''                    NOT NULL,      -- FAX
    or_deliv_email       VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- Eメールアドレス
    or_deliv_country_id  VARCHAR(3)     DEFAULT ''                    NOT NULL,      -- 国ID

    or_bill_id           INT            DEFAULT 0                     NOT NULL,      -- 請求先ID(参照用)
    or_bill_name         VARCHAR(50)    DEFAULT ''                    NOT NULL,      -- 請求先名
    or_bill_name_kana    VARCHAR(50)    DEFAULT ''                    NOT NULL,      -- 請求先名(カナ)
    or_bill_person       VARCHAR(50)    DEFAULT ''                    NOT NULL,      -- 請求先担当者名
    or_bill_person_kana  VARCHAR(50)    DEFAULT ''                    NOT NULL,      -- 請求先担当者名(カナ)
    or_bill_zipcode      VARCHAR(10)    DEFAULT ''                    NOT NULL,      -- 郵便番号(7桁)
    or_bill_state_id     INT            DEFAULT 0                     NOT NULL,      -- 都道府県、州(geo_zoneテーブル)
    or_bill_address1     VARCHAR(100)   DEFAULT ''                    NOT NULL,      -- 市区町村
    or_bill_address2     VARCHAR(60)    DEFAULT ''                    NOT NULL,      -- ビル・マンション名等
    or_bill_phone        VARCHAR(30)    DEFAULT ''                    NOT NULL,      -- 電話番号
    or_bill_fax          VARCHAR(30)    DEFAULT ''                    NOT NULL,      -- FAX
    or_bill_email        VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- Eメールアドレス
    or_bill_country_id   VARCHAR(3)     DEFAULT ''                    NOT NULL,      -- 国ID

    or_deliv_method_id   VARCHAR(50)    DEFAULT ''                    NOT NULL,      -- 配送方法
    or_pay_method_id     VARCHAR(50)    DEFAULT ''                    NOT NULL,      -- 支払い方法
    or_card_type         VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- クレジットカードタイプ
    or_card_owner        VARCHAR(64)    DEFAULT ''                    NOT NULL,      -- クレジットカード所有者
    or_card_number       VARCHAR(32)    DEFAULT ''                    NOT NULL,      -- クレジットカード番号
    or_card_expires      VARCHAR(4)     DEFAULT ''                    NOT NULL,      -- クレジットカード期限

    or_message           TEXT                                         NOT NULL,      -- 取引先からのメッセージ
    or_note              TEXT                                         NOT NULL,      -- 補足情報
    or_option_fields     TEXT                                         NOT NULL,      -- 追加フィールド(「,」区切り)
    or_demand_dt         DATE           DEFAULT '0000-00-00'          NOT NULL,      -- 希望納期(日付)
    or_demand_time       TEXT                                         NOT NULL,      -- 希望納期(時間帯)
    or_appoint_dt        DATE           DEFAULT '0000-00-00'          NOT NULL,      -- 予定納期
    or_discount_desc     TEXT                                         NOT NULL,      -- 値引き説明
    
    or_currency_id       VARCHAR(3)     DEFAULT ''                    NOT NULL,      -- 通貨ID
    or_subtotal          DECIMAL(15,4)  DEFAULT 0                     NOT NULL,      -- 商品総額
    or_discount          DECIMAL(15,4)  DEFAULT 0                     NOT NULL,      -- 値引き額
    or_deliv_fee         DECIMAL(15,4)  DEFAULT 0                     NOT NULL,      -- 配送料
    or_charge            DECIMAL(15,4)  DEFAULT 0                     NOT NULL,      -- 手数料
    or_total             DECIMAL(15,4)  DEFAULT 0                     NOT NULL,      -- 支払い総額
    
    or_order_status      INT            DEFAULT 0                     NOT NULL,      -- 受注状況
    or_estimate_dt       TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- 見積日時
    or_regist_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- 受注受付日時
    or_order_dt          TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- 受注処理開始日時
    or_pay_dt            TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- 支払い日時
    or_deliv_dt          TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- 配送日時
    or_close_dt          TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- 取引終了日時

    or_create_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード作成者
    or_create_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード作成日時
    or_update_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード更新者
    or_update_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード更新日時
    or_deleted           BOOLEAN        DEFAULT false                 NOT NULL,      -- レコード削除状態
    PRIMARY KEY          (or_serial),
    UNIQUE               (or_id,        or_history_index)
) ENGINE=innodb;

-- 商品受注トラン追加フィールド
DROP TABLE IF EXISTS order_opt_field;
CREATE TABLE order_opt_field (
    of_id                VARCHAR(10)    DEFAULT ''                    NOT NULL,      -- フィールドID
    of_language_id       VARCHAR(2)     DEFAULT ''                    NOT NULL,      -- 言語ID
    of_name              VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- 名称
    of_field_type        VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- フィールドタイプ
    of_index             INT            DEFAULT 0                     NOT NULL,      -- ソート用
    PRIMARY KEY          (of_id,        of_language_id)
) ENGINE=innodb;

-- 受注トラン追加フィールド値
DROP TABLE IF EXISTS order_opt_field_value;
CREATE TABLE order_opt_field_value (
    ov_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    ov_field_id          INT            DEFAULT 0                     NOT NULL,      -- 商品トラン追加フィールドID
    ov_history_index     INT            DEFAULT 0                     NOT NULL,      -- 履歴管理用インデックスNo(0～)
    ov_value             VARCHAR(10)    DEFAULT ''                    NOT NULL,      -- 値
    ov_create_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード作成者
    ov_create_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード作成日時
    ov_update_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード更新者
    ov_update_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード更新日時
    ov_deleted           BOOLEAN        DEFAULT false                 NOT NULL,      -- レコード削除状態
    PRIMARY KEY          (ov_serial),
    UNIQUE               (ov_field_id,  ov_history_index)
) ENGINE=innodb;

-- 受注明細トラン
DROP TABLE IF EXISTS order_detail;
CREATE TABLE order_detail (
    od_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    od_order_id          INT            DEFAULT 0                     NOT NULL,      -- 受注ID
    od_index             INT            DEFAULT 0                     NOT NULL,      -- 明細番号(0～)
    od_history_index     INT            DEFAULT 0                     NOT NULL,      -- 履歴管理用インデックスNo(0～)

    od_product_class     VARCHAR(10)    DEFAULT ''                    NOT NULL,      -- 商品クラス
    od_product_id        INT            DEFAULT 0                     NOT NULL,      -- 商品ID
    od_product_type_id   VARCHAR(10)    DEFAULT ''                    NOT NULL,      -- 商品タイプ
    od_product_name      TEXT                                         NOT NULL,      -- 商品名
    od_product_code      TEXT                                         NOT NULL,      -- 商品コード
    od_attribute         TEXT                                         NOT NULL,      -- 商品属性(属性メニューの選択値)
    od_note              TEXT                                         NOT NULL,      -- 補足情報

    od_unit_price        DECIMAL(15,4)  DEFAULT 0                     NOT NULL,      -- 受注単価(税抜)
    od_price_with_tax    DECIMAL(15,4)  DEFAULT 0                     NOT NULL,      -- 受注単価(税込み)
    od_quantity          INT            DEFAULT 0                     NOT NULL,      -- 数量
    od_tax               DECIMAL(15,4)  DEFAULT 0                     NOT NULL,      -- 税
    od_total             DECIMAL(15,4)  DEFAULT 0                     NOT NULL,      -- 受注価格(税込)

    od_delivery_dt       DATE           DEFAULT '0000-00-00'          NOT NULL,      -- 明細単位の納期

    od_create_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード作成者
    od_create_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード作成日時
    od_update_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード更新者
    od_update_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード更新日時
    od_deleted           BOOLEAN        DEFAULT false                 NOT NULL,      -- レコード削除状態
    PRIMARY KEY          (od_serial),
    UNIQUE               (od_order_id,  od_index,      od_history_index)
) ENGINE=innodb;

-- 商品記録トラン
DROP TABLE IF EXISTS product_record;
CREATE TABLE product_record (
    pe_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    pe_product_id        INT            DEFAULT 0                     NOT NULL,      -- 商品ID
    pe_language_id       VARCHAR(2)     DEFAULT ''                    NOT NULL,      -- 言語ID
    
    pe_order_count       INT            DEFAULT 0                     NOT NULL,      -- 累積注文数
    pe_sales_count       INT            DEFAULT 0                     NOT NULL,      -- 累積販売数
    pe_stock_count       INT            DEFAULT 0                     NOT NULL,      -- 表示在庫数
    pe_view_count        INT            DEFAULT 0                     NOT NULL,      -- 参照回数
    pe_promote_count     INT            DEFAULT 0                     NOT NULL,      -- 販売促進回数
    
    pe_update_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード更新者
    pe_update_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード更新日時
    PRIMARY KEY          (pe_serial),
    UNIQUE               (pe_product_id,        pe_language_id)
) ENGINE=innodb;

-- 商品参照ログ
DROP TABLE IF EXISTS product_view;
CREATE TABLE product_view (
    pv_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    pv_type              INT            DEFAULT 0                     NOT NULL,      -- ログタイプ(0=表示)
    pv_product_serial    INT            DEFAULT 0                     NOT NULL,      -- 商品項目シリアル番号
    pv_log_serial        INT            DEFAULT 0                     NOT NULL,      -- アクセスログシリアル番号
    pv_dt                TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- 参照日時
    PRIMARY KEY          (pv_serial)
) ENGINE=innodb;

-- 入出庫予定トラン
DROP TABLE IF EXISTS stock_plan;
CREATE TABLE stock_plan (
    sp_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    sp_product_id        INT            DEFAULT 0                     NOT NULL,      -- 商品ID
    sp_scheduled_dt      DATE           DEFAULT '0000-00-00'          NOT NULL,      -- 入出庫予定日
    sp_index             INT            DEFAULT 0                     NOT NULL,      -- 入出庫インデックス(0～)
    sp_history_index     INT            DEFAULT 0                     NOT NULL,      -- 履歴管理用インデックスNo(0～)

    sp_plan_type         INT            DEFAULT 0                     NOT NULL,      -- 入出庫区分(1=入庫、2=出庫、3=棚卸)
    sp_customer_id       INT            DEFAULT 0                     NOT NULL,      -- 予定取引先
    sp_quantity          INT            DEFAULT 0                     NOT NULL,      -- 個数
    sp_note              TEXT                                         NOT NULL,      -- 補足情報

    sp_create_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード作成者
    sp_create_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード作成日時
    sp_update_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード更新者
    sp_update_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード更新日時
    sp_deleted           BOOLEAN        DEFAULT false                 NOT NULL,      -- レコード削除状態
    PRIMARY KEY          (sp_serial),
    UNIQUE               (sp_product_id,        sp_scheduled_dt,      sp_index,         sp_history_index)
) ENGINE=innodb;

-- 更新データ
-- メニューIDマスター
DELETE FROM _menu_id WHERE mn_id = 'ec_menu';
INSERT INTO _menu_id
(mn_id,         mn_name,          mn_description, mn_device_type, mn_widget_id, mn_sort_order) VALUES
('ec_menu',   'EC用メニュー(PC用)', '「ec_menu」ウィジェット専用のメニュー',             0,   'ec_menu',           10);

-- 追加クラスマスター
DELETE FROM _addons WHERE ao_id = 'eclib';
INSERT INTO _addons (ao_id,     ao_class_name, ao_name,               ao_description, ao_index)
VALUES              ('eclib',   'ecLib',       'Eコマースライブラリ', '',             1);
DELETE FROM _addons WHERE ao_id = 'ecmail';
INSERT INTO _addons (ao_id,     ao_class_name, ao_name,               ao_description, ao_index)
VALUES              ('ecmail',   'ecMail',       'Eコマースメール連携', '',             2);

-- インナーウィジェット
-- インナーウィジェット(配送方法)
DELETE FROM _iwidgets WHERE iw_widget_id = 'ec_main' AND iw_id = 'flatrate';
INSERT INTO _iwidgets
(iw_widget_id, iw_id,      iw_name,    iw_type,    iw_author,      iw_copyright, iw_license, iw_license_type, iw_official_level, iw_install_dt, iw_create_dt) VALUES
('ec_main', 'flatrate', '定額', 'DELIVERY', 'Naoki Hirata', 'Magic3.org', 'GPL', 0,      10,                now(),         now());
DELETE FROM _iwidgets WHERE iw_widget_id = 'ec_main' AND iw_id = 'classrate';
INSERT INTO _iwidgets
(iw_widget_id, iw_id,       iw_name,    iw_type,    iw_author,      iw_copyright, iw_license, iw_license_type, iw_official_level, iw_install_dt, iw_create_dt) VALUES
('ec_main', 'classrate', '購入額基準', 'DELIVERY', 'Naoki Hirata', 'Magic3.org', 'GPL', 0,      10,                now(),         now());
DELETE FROM _iwidgets WHERE iw_widget_id = 'ec_main' AND iw_id = 'staterate';
INSERT INTO _iwidgets
(iw_widget_id, iw_id,       iw_name,    iw_type,    iw_author,      iw_copyright, iw_license, iw_license_type, iw_official_level, iw_install_dt, iw_create_dt) VALUES
('ec_main', 'staterate', '送付先基準', 'DELIVERY', 'Naoki Hirata', 'Magic3.org', 'GPL', 0,      10,                now(),         now());
DELETE FROM _iwidgets WHERE iw_widget_id = 'ec_main' AND iw_id = 'quantityrate';
INSERT INTO _iwidgets
(iw_widget_id, iw_id,       iw_name,    iw_type,    iw_author,      iw_copyright, iw_license, iw_license_type, iw_official_level, iw_install_dt, iw_create_dt) VALUES
('ec_main', 'quantityrate', '商品数基準', 'DELIVERY', 'Naoki Hirata', 'Magic3.org', 'GPL', 0,      10,                now(),         now());
DELETE FROM _iwidgets WHERE iw_widget_id = 'ec_main' AND iw_id = 'productrate';
INSERT INTO _iwidgets
(iw_widget_id, iw_id,       iw_name,    iw_type,    iw_author,      iw_copyright, iw_license, iw_license_type, iw_official_level, iw_install_dt, iw_create_dt) VALUES
('ec_main', 'productrate', '商品別規定', 'DELIVERY', 'Naoki Hirata', 'Magic3.org', 'GPL', 0,      10,                now(),         now());
DELETE FROM _iwidgets WHERE iw_widget_id = 'ec_main' AND iw_id = 'weightrate';
INSERT INTO _iwidgets
(iw_widget_id, iw_id,       iw_name,    iw_type,    iw_author,      iw_copyright, iw_license, iw_license_type, iw_official_level, iw_install_dt, iw_create_dt) VALUES
('ec_main', 'weightrate', '送付先+重量基準', 'DELIVERY', 'Naoki Hirata', 'Magic3.org', 'GPL', 0,      10,                now(),         now());
-- インナーウィジェット(支払方法)
DELETE FROM _iwidgets WHERE iw_widget_id = 'ec_main' AND iw_id = 'epsilon';
INSERT INTO _iwidgets
(iw_widget_id, iw_id,     iw_name,          iw_type,    iw_author,      iw_copyright, iw_license,               iw_license_type, iw_official_level, iw_online, iw_install_dt, iw_create_dt) VALUES
('ec_main', 'epsilon', 'イプシロン決済', 'PAYMENT', 'Naoki Hirata', 'Magic3.org', 'GPL', 0,               10,                true,      now(),         now());
DELETE FROM _iwidgets WHERE iw_widget_id = 'ec_main' AND iw_id = 'exchange_classrate';
INSERT INTO _iwidgets
(iw_widget_id, iw_id,     iw_name,          iw_type,    iw_author,      iw_copyright, iw_license,               iw_license_type, iw_official_level, iw_online, iw_install_dt, iw_create_dt) VALUES
('ec_main', 'exchange_classrate', '代金引換(購入額基準)', 'PAYMENT', 'Naoki Hirata', 'Magic3.org', 'GPL', 0,               10,                false,      now(),         now());
-- インナーウィジェット(注文計算)
DELETE FROM _iwidgets WHERE iw_widget_id = 'ec_main' AND iw_id = 'lotbuying';
INSERT INTO _iwidgets
(iw_widget_id, iw_id,       iw_name,          iw_type,     iw_author,      iw_copyright, iw_license,               iw_license_type, iw_official_level, iw_install_dt, iw_create_dt) VALUES
('ec_main', 'lotbuying', 'まとめ買い割引', 'CALCORDER', 'Naoki Hirata', 'Magic3.org', 'GPL', 0,               10,                now(),         now());
DELETE FROM _iwidgets WHERE iw_widget_id = 'ec_main' AND iw_id = 'product_lotbuying';
INSERT INTO _iwidgets
(iw_widget_id, iw_id,               iw_name,                iw_type,     iw_author,      iw_copyright, iw_license,                iw_license_type, iw_official_level, iw_install_dt, iw_create_dt) VALUES
('ec_main', 'product_lotbuying', '商品別まとめ買い割引', 'CALCORDER', 'Naoki Hirata', 'Magic3.org', 'GPL', 0,               10,                now(),         now());

-- メール内容
DELETE FROM _mail_form WHERE mf_id = 'regist_member_to_backoffice';
INSERT INTO _mail_form (mf_id,           mf_language_id, mf_subject,         mf_content,                                                                 mf_create_dt) 
VALUES                 ('regist_member_to_backoffice', 'ja',           '会員登録',         '■顧客コード：[#MEMBER_NO#]■\n■メールアカウント：[#EMAIL#]■\n■顧客名：[#NAME#]■\n■カナヨミ：[#NAME_KANA#]■\n■郵便番号：[#ZIPCODE#]■\n■住所１：[#ADDRESS1#]■\n■住所２：[#ADDRESS2#]■\n■電話番号：[#PHONE#]■\n', now());
DELETE FROM _mail_form WHERE mf_id = 'order_product_to_backoffice';
INSERT INTO _mail_form (mf_id,           mf_language_id, mf_subject,         mf_content,                                                                 mf_create_dt) 
VALUES                 ('order_product_to_backoffice', 'ja',           '商品受注',         '■受注コード：[#ORDER_NO#]■\n■受注日付：[#DATE#]■\n■顧客コード：[#MEMBER_NO#]■\n■顧客名：[#NAME#]■\n■届先名：[#DELIV_NAME#]■\n■届先郵便番号：[#ZIPCODE#]■\n■届先住所１：[#ADDRESS1#]■\n■届先住所２：[#ADDRESS2#]■\n■届先電話番号：[#PHONE#]■\n■配達希望日：[#DEMAND_DATE#]■\n■配達時間帯：[#DEMAND_TIME#]■\n[#BODY#]■配送方法：[#DELIV_METHOD#]■\n■決済方法：[#PAY_METHOD#]■\n■備考：[#NOTE#]■', now());
DELETE FROM _mail_form WHERE mf_id = 'order_product_to_shop_manager';
INSERT INTO _mail_form (mf_id,           mf_language_id, mf_subject,         mf_content,                                                                 mf_create_dt) 
VALUES                 ('order_product_to_shop_manager', 'ja',           '商品受注',         '■受注コード：[#ORDER_NO#]■\n■受注日付：[#DATE#]■\n■会員コード：[#MEMBER_NO#]■\n■会員名：[#NAME#]■\n■会員Eメール：[#EMAIL#]■\n■管理画面URL：[#ADMIN_URL#]■\n■届先名：[#DELIV_NAME#]■\n■届先郵便番号：[#ZIPCODE#]■\n■届先都道府県：[#STATE#]■\n■届先住所１：[#ADDRESS1#]■\n■届先住所２：[#ADDRESS2#]■\n■届先電話番号：[#PHONE#]■\n■配達希望日：[#DEMAND_DATE#]■\n■配達時間帯：[#DEMAND_TIME#]■\n[#BODY#]■配送方法：[#DELIV_METHOD#]■\n■決済方法：[#PAY_METHOD#]■\n■備考：[#NOTE#]■\n\n**********\nお届け先\n**********\n[#DELIV_TEXT#]\n**********\n注文内容\n**********\n[#ORDER_TEXT#]\n', now());
DELETE FROM _mail_form WHERE mf_id = 'order_product_to_customer';
INSERT INTO _mail_form (mf_id,           mf_language_id, mf_subject,         mf_content,                                                                 mf_create_dt) 
VALUES                 ('order_product_to_customer', 'ja',           'ご注文の確認(自動送信)',         '[#NAME#] 様\n\nこの度は[#SHOP_NAME#]をご利用頂きまして誠にありがとうございます。\n下記の通りご注文を承りましたのでご確認ください。\n\n**********\nお届け先\n**********\n[#DELIV_TEXT#]\n**********\n注文内容\n**********\n[#ORDER_TEXT#]\n\n[#SIGNATURE#]', now());

-- フォトギャラリー設定マスター(Eコマース追加分)
INSERT INTO photo_config
(hg_id,               hg_value,           hg_name,                                  hg_index) VALUES
('online_shop',       '0',                'オンラインショップ機能',                 13),
('auto_stock',        '1',                '在庫自動処理',                           14),
('accept_order',      '1',                '注文の受付',                             15),
('use_email',         '1',                'メール送信機能',                         16),
('shop_email',        '',                 'ショップ宛てメールアドレス',             17),
('auto_email_sender', '',                 '自動送信メール送信元アドレス',           18),
('use_member_address', '1',               '会員登録の住所使用',                     19),
('auto_regist_member', '1',               '自動会員登録',                           20),
('sell_product_photo', '0',               'フォト商品販売',                         21),
('sell_product_download', '0',            'ダウンロード商品販売',                   22),
('member_notice', '',                     '会員向けお知らせ',                       23),
('email_to_order_product', '',            '商品受注時メール送信先',                 24);
