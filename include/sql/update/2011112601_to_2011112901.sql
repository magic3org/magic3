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
-- * @version    SVN: $Id: 2011112601_to_2011112901.sql 4498 2011-12-09 00:41:29Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------
-- *** Eコマーステーブル ***
-- 受注明細トラン
ALTER TABLE order_detail MODIFY od_product_name     TEXT NOT NULL;       -- 商品名
ALTER TABLE order_detail ADD od_product_class     VARCHAR(10)    DEFAULT ''                    NOT NULL;      -- 商品クラス
ALTER TABLE order_detail ADD od_product_type_id   VARCHAR(10)    DEFAULT ''                    NOT NULL;      -- 商品タイプ
ALTER TABLE order_detail ADD od_product_code     TEXT NOT NULL;       -- 商品コード

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
) TYPE=innodb;

-- 商品価格マスター
ALTER TABLE product_price DROP INDEX pp_product_id;-- ALTER TABLE product_price DROP CONSTRAINT product_price_pp_product_id_pp_language_id_pp_price_type_id_key; -- ユニーク制約削除
ALTER TABLE product_price ADD pp_product_class     VARCHAR(10)    DEFAULT ''                    NOT NULL;      -- 商品クラス
ALTER TABLE product_price ADD pp_product_type_id   VARCHAR(10)    DEFAULT ''                    NOT NULL;      -- 商品タイプ
ALTER TABLE product_price ADD UNIQUE (pp_product_class,     pp_product_id,    pp_product_type_id,    pp_language_id,      pp_price_type_id,      pp_history_index);                -- ユニーク制約再設定

-- ショッピングカート商品項目
ALTER TABLE shop_cart_item DROP INDEX si_head_serial;-- ALTER TABLE shop_cart_item DROP CONSTRAINT shop_cart_item_si_head_serial_si_product_id_key; -- ユニーク制約削除
ALTER TABLE shop_cart_item ADD si_product_class     VARCHAR(10)    DEFAULT ''                    NOT NULL;      -- 商品クラス
ALTER TABLE shop_cart_item ADD si_product_type_id   VARCHAR(10)    DEFAULT ''                    NOT NULL;      -- 商品タイプ
ALTER TABLE shop_cart_item ADD UNIQUE (si_head_serial, si_product_class,     si_product_id,    si_product_type_id);                -- ユニーク制約再設定

