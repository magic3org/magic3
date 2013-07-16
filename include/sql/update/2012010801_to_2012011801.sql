-- *
-- * バージョンアップ用スクリプト
-- *
-- * PHP versions 5
-- *
-- * LICENSE: This source file is licensed under the terms of the GNU General Public License.
-- *
-- * @package    Magic3 Framework
-- * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
-- * @copyright  Copyright 2006-2012 Magic3 Project.
-- * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
-- * @version    SVN: $Id: 2012010801_to_2012011801.sql 4637 2012-01-31 04:29:30Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***

-- *** Eコマーステーブル ***
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
) TYPE=innodb;

-- 商品画像マスター
ALTER TABLE product_image DROP INDEX im_type;-- ALTER TABLE product_image DROP CONSTRAINT product_image_im_type_im_id_im_language_id_im_size_id_im_hi_key; -- ユニーク制約削除
ALTER TABLE product_image ADD im_product_class     VARCHAR(10)    DEFAULT ''                    NOT NULL;      -- 商品クラス
ALTER TABLE product_image ADD UNIQUE (im_product_class,      im_type,      im_id,              im_language_id,          im_size_id,       im_history_index);-- ユニーク制約再設定



