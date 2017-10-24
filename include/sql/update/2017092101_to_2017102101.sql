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

-- 価格種別マスター
DELETE FROM price_type;
INSERT INTO price_type (pr_id, pr_language_id, pr_kind, pr_name, pr_sort_order) VALUES ('regular', 'ja', 10, '通常価格',      1);
INSERT INTO price_type (pr_id, pr_language_id, pr_kind, pr_name, pr_sort_order) VALUES ('sale',    'ja', 11, 'セール価格',    2);
INSERT INTO price_type (pr_id, pr_language_id, pr_kind, pr_name, pr_sort_order) VALUES ('member',  'ja', 12, '会員価格',      3);
INSERT INTO price_type (pr_id, pr_language_id, pr_kind, pr_name, pr_sort_order) VALUES ('disposal','ja', 13, '処分価格',      4);
INSERT INTO price_type (pr_id, pr_language_id, pr_kind, pr_name, pr_sort_order) VALUES ('buying',  'ja', 20, '仕入価格',      5);

-- 税率マスター
UPDATE tax_rate SET tr_rate = '8.00' WHERE tr_id = 'rate_sales';

-- Eコマース設定マスター
INSERT INTO commerce_config
(cg_id,                    cg_value, cg_name,                        cg_index) VALUES
('use_sale_price',        '0',                'セール価格使用',                           100),
('price_suffix',        '(税込)',                '価格表示接尾辞',                           100);
