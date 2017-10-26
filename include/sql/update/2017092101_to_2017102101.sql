-- *
-- * バージョンアップ用スクリプト
-- *
-- * PHP versions 5
-- *
-- * LICENSE: This source file is licensed under the terms of the GNU General Public License.
-- *
-- * @package    Magic3 Framework
-- * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
-- * @copyright  Copyright 2006-2017 Magic3 Project.
-- * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
-- * @version    SVN: $Id$
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***


-- *** システム標準テーブル ***
-- 商品価格マスターデータ変換
UPDATE product_price SET pp_price_type_id = 'regular' WHERE pp_price_type_id = 'selling';

-- 商品価格マスターのキー変更
ALTER TABLE product_price DROP INDEX pp_product_class;-- ALTER TABLE product_price DROP CONSTRAINT product_price_pp_product_class_pp_product_id_pp_product_typ_key; -- ユニーク制約削除
ALTER TABLE product_price DROP COLUMN pp_language_id;  -- カラム削除
ALTER TABLE product_price ADD UNIQUE (pp_product_class,     pp_product_id,    pp_product_type_id,    pp_currency_id,   pp_price_type_id,      pp_history_index);                -- ユニーク制約再設定

-- 価格種別マスター
DELETE FROM price_type;
INSERT INTO price_type (pr_id, pr_language_id, pr_kind, pr_name, pr_sort_order) VALUES ('regular', 'ja', 10, '通常価格',      1);
INSERT INTO price_type (pr_id, pr_language_id, pr_kind, pr_name, pr_sort_order) VALUES ('sale',    'ja', 11, 'セール価格',    2);
INSERT INTO price_type (pr_id, pr_language_id, pr_kind, pr_name, pr_sort_order) VALUES ('member',  'ja', 12, '会員価格',      3);
INSERT INTO price_type (pr_id, pr_language_id, pr_kind, pr_name, pr_sort_order) VALUES ('disposal','ja', 13, '処分価格',      4);
INSERT INTO price_type (pr_id, pr_language_id, pr_kind, pr_name, pr_sort_order) VALUES ('buying',  'ja', 20, '仕入価格',      5);

-- 税種別マスター(仕様変更)
DROP TABLE IF EXISTS tax_type;
CREATE TABLE tax_type (
    tt_id                VARCHAR(10)    DEFAULT ''                    NOT NULL,      -- 種別ID
    tt_tax_rate_type     INT            DEFAULT 0                     NOT NULL,      -- 税率種別(0=税率なし、1=固定(tax_rateテーブル))
    tt_tax_inout         SMALLINT       DEFAULT 0                     NOT NULL,      -- 内税外税区分(0=外税、1=内税)
    tt_tax_rate_id       VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- 税率ID
    tt_name              VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- 名称
    tt_description       VARCHAR(100)   DEFAULT ''                    NOT NULL,      -- 説明
    tt_geo_zone_id       TEXT                                         NOT NULL,      -- 対象区域(,区切り)
    tt_index             INT            DEFAULT 0                     NOT NULL,      -- ソート用
    PRIMARY KEY          (tt_id)
) ENGINE=innodb;
INSERT INTO tax_type (tt_id, tt_tax_rate_type, tt_tax_rate_id, tt_name, tt_geo_zone_id, tt_index) VALUES ('sales', 1, 'rate_sales', '課税(外税)',   '1', 0);
INSERT INTO tax_type (tt_id, tt_tax_rate_type, tt_tax_rate_id, tt_name, tt_geo_zone_id, tt_index) VALUES ('notax', 0, '',           '非課税', '1', 1);

-- 税率マスター(仕様変更)
DROP TABLE IF EXISTS tax_rate;
CREATE TABLE tax_rate (
    tr_id                VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- 税率ID
    tr_index             INT            DEFAULT 0                     NOT NULL,      -- インデックス番号(0～)
    tr_name              VARCHAR(30)    DEFAULT ''                    NOT NULL,      -- 名称
    tr_rate              DECIMAL(7,4)   DEFAULT 0                     NOT NULL,      -- 税率(%)
    tr_active_start_dt   TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- 有効期限開始日時
    tr_active_end_dt     TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- 有効期限終了日時
    PRIMARY KEY          (tr_id,        tr_index)
) ENGINE=innodb;
INSERT INTO tax_rate (tr_id, tr_name, tr_rate) VALUES ('rate_sales', '消費税率', '8.00');

-- Eコマース設定マスター
INSERT INTO commerce_config
(cg_id,                    cg_value, cg_name,                        cg_index) VALUES
('use_sale_price',        '0',                'セール価格使用',                           100),
('price_suffix',        '(税込)',                '価格表示接尾辞',                           100);
