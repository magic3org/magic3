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

-- テンプレート情報
DELETE FROM _templates WHERE tm_id = 'shop-isle';
INSERT INTO _templates
(tm_id,                     tm_name,          tm_type, tm_device_type, tm_mobile, tm_use_bootstrap, tm_available, tm_clean_type, tm_info_url, tm_create_dt) VALUES
('shop-isle',                'shop-isle',       100,       0,              false,     false,             true,        0,             'https://themeisle.com/',          now());

-- *** システム標準テーブル ***
-- 商品情報マスター
ALTER TABLE product ADD pt_user_limited      BOOLEAN        DEFAULT false                 NOT NULL;      -- 参照ユーザを制限
ALTER TABLE product ADD pt_active_start_dt   TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL;      -- 公開期間(開始)
ALTER TABLE product ADD pt_active_end_dt     TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL;      -- 公開期間(終了)
ALTER TABLE product ADD pt_thumb_filename    TEXT                                         NOT NULL;      -- サムネールファイル名(「;」区切り)
ALTER TABLE product ADD pt_thumb_src         TEXT                                         NOT NULL;      -- サムネールの元のファイル(リソースディレクトリからの相対パス)
ALTER TABLE product ADD pt_option_fields     TEXT                                         NOT NULL;      -- 追加フィールド

-- 商品ステータス種別マスター
DELETE FROM product_status_type;
INSERT INTO product_status_type (pa_id, pa_language_id, pa_name, pa_priority) VALUES ('new',     'ja', '新着',       0);
INSERT INTO product_status_type (pa_id, pa_language_id, pa_name, pa_priority) VALUES ('suggest', 'ja', 'おすすめ',   1);
INSERT INTO product_status_type (pa_id, pa_language_id, pa_name, pa_priority) VALUES ('few',     'ja', '残りわずか', 2);
INSERT INTO product_status_type (pa_id, pa_language_id, pa_name, pa_priority) VALUES ('limited', 'ja', '限定品',     3);
INSERT INTO product_status_type (pa_id, pa_language_id, pa_name, pa_priority) VALUES ('sale',    'ja', 'セール',     4);

-- 商品ステータスマスター
ALTER TABLE product_status ADD ps_active_start_dt   TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL;      -- 有効期間(開始)
ALTER TABLE product_status ADD ps_active_end_dt     TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL;      -- 有期間(終了)

-- 価格種別マスター
DELETE FROM price_type;
INSERT INTO price_type (pr_id, pr_language_id, pr_kind, pr_name, pr_sort_order) VALUES ('selling', 'ja', 10, '通常価格',      0);
INSERT INTO price_type (pr_id, pr_language_id, pr_kind, pr_name, pr_sort_order) VALUES ('bargain', 'ja', 10, '特価',          1);
INSERT INTO price_type (pr_id, pr_language_id, pr_kind, pr_name, pr_sort_order) VALUES ('member',  'ja', 10, '会員価格',      2);
INSERT INTO price_type (pr_id, pr_language_id, pr_kind, pr_name, pr_sort_order) VALUES ('sale',    'ja', 11, 'セール価格',    3);
INSERT INTO price_type (pr_id, pr_language_id, pr_kind, pr_name, pr_sort_order) VALUES ('buying',  'ja', 12, '仕入価格',      4);

-- Eコマース設定マスター
INSERT INTO commerce_config
(cg_id,                    cg_value, cg_name,                        cg_index) VALUES
('auto_stock',        '1',                '在庫自動処理',                           100),
('accept_order',      '1',                '注文の受付',                             101),
('use_member_address', '1',               '会員登録の住所使用',                     105),
('auto_regist_member', '1',               '自動会員登録',                           106),
('sell_product_photo', '0',               'フォト商品販売',                         107),
('sell_product_download', '0',            'ダウンロード商品販売',                   108),
('member_notice', '',                     '会員向けお知らせ',                       109),
('email_to_order_product', '',            '商品受注時メール送信先',                 110),
('thumb_type',              's=80c.jpg;mw=160x120c.jpg;l=200c.jpg', '製品サムネールタイプ定義', 111),
('product_default_image',     '0_72c.jpg;0_80c.jpg;0_200c.jpg',       '製品デフォルト画像', 112);
