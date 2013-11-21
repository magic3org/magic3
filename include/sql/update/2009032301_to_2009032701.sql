-- *
-- * バージョンアップ用スクリプト
-- *
-- * PHP versions 5
-- *
-- * LICENSE: This source file is licensed under the terms of the GNU General Public License.
-- *
-- * @package    Magic3 Framework
-- * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
-- * @copyright  Copyright 2006-2009 Magic3 Project.
-- * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
-- * @version    SVN: $Id: 2009032301_to_2009032701.sql 1683 2009-04-01 04:38:28Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------
-- *** システムベーステーブル ***
-- インナーウィジェット情報マスター
DELETE FROM _iwidgets WHERE iw_widget_id = 'ec_main' AND iw_id = 'staterate';
INSERT INTO _iwidgets
(iw_widget_id, iw_id,       iw_name,    iw_type,    iw_author,      iw_copyright, iw_license, iw_official_level, iw_install_dt, iw_create_dt) VALUES
('ec_main', 'staterate', '送付先基準', 'DELIVERY', 'Naoki Hirata', 'Magic3.org', 'GPL',      10,                now(),         now());
DELETE FROM _iwidgets WHERE iw_widget_id = 'ec_main' AND iw_id = 'quantityrate';
INSERT INTO _iwidgets
(iw_widget_id, iw_id,       iw_name,    iw_type,    iw_author,      iw_copyright, iw_license, iw_official_level, iw_install_dt, iw_create_dt) VALUES
('ec_main', 'quantityrate', '商品数基準', 'DELIVERY', 'Naoki Hirata', 'Magic3.org', 'GPL',      10,                now(),         now());

-- *** EC用テーブル ***
-- 商品情報マスター
ALTER TABLE product ADD pt_deliv_type VARCHAR(10)    DEFAULT ''                    NOT NULL;      -- 配送料金の計算方法
ALTER TABLE product ADD pt_deliv_size VARCHAR(10)    DEFAULT ''                    NOT NULL;      -- 商品サイズ-送料計算に使用
ALTER TABLE product ADD pt_deliv_fee  DECIMAL(15,4)  DEFAULT 0                     NOT NULL;      -- 商品単位送料-送料計算に使用
-- ショッピングカート商品項目
ALTER TABLE shop_cart_item ADD si_active BOOLEAN        DEFAULT true                  NOT NULL;      -- 購入対象かどうか
