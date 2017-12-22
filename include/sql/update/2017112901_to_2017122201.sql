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

-- インナーウィジェット情報マスター
ALTER TABLE _iwidgets ADD iw_params     TEXT                                         NOT NULL;      -- 追加パラメータ(「;」区切り)

-- ID仕様変更のため一時的に二重登録
-- インナーウィジェット(配送方法)
DELETE FROM _iwidgets WHERE iw_widget_id = '' AND iw_id = 'ec_main,flatrate';
INSERT INTO _iwidgets
(iw_widget_id, iw_id,      iw_name,    iw_type,    iw_author,      iw_copyright, iw_license, iw_license_type, iw_official_level, iw_install_dt, iw_create_dt) VALUES
('', 'ec_main,flatrate', '定額', 'DELIVERY', 'Naoki Hirata', 'Magic3.org', 'GPL', 0,      10,                now(),         now());
DELETE FROM _iwidgets WHERE iw_widget_id = '' AND iw_id = 'ec_main,classrate';
INSERT INTO _iwidgets
(iw_widget_id, iw_id,       iw_name,    iw_type,    iw_author,      iw_copyright, iw_license, iw_license_type, iw_official_level, iw_install_dt, iw_create_dt) VALUES
('', 'ec_main,classrate', '購入額基準', 'DELIVERY', 'Naoki Hirata', 'Magic3.org', 'GPL', 0,      10,                now(),         now());
DELETE FROM _iwidgets WHERE iw_widget_id = '' AND iw_id = 'ec_main,staterate';
INSERT INTO _iwidgets
(iw_widget_id, iw_id,       iw_name,       iw_type,   iw_params,                   iw_author,      iw_copyright, iw_license,   iw_license_type, iw_official_level, iw_install_dt, iw_create_dt) VALUES
('',    'ec_main,staterate', '送付先基準', 'DELIVERY', 'wc_support=shipping-zones', 'Naoki Hirata', 'Magic3.org', 'GPL',         0,      10,                now(),         now());
DELETE FROM _iwidgets WHERE iw_widget_id = '' AND iw_id = 'ec_main,quantityrate';
INSERT INTO _iwidgets
(iw_widget_id, iw_id,       iw_name,    iw_type,    iw_author,      iw_copyright, iw_license, iw_license_type, iw_official_level, iw_install_dt, iw_create_dt) VALUES
('', 'ec_main,quantityrate', '商品数基準', 'DELIVERY', 'Naoki Hirata', 'Magic3.org', 'GPL', 0,      10,                now(),         now());
DELETE FROM _iwidgets WHERE iw_widget_id = '' AND iw_id = 'ec_main,productrate';
INSERT INTO _iwidgets
(iw_widget_id, iw_id,       iw_name,    iw_type,    iw_author,      iw_copyright, iw_license, iw_license_type, iw_official_level, iw_install_dt, iw_create_dt) VALUES
('', 'ec_main,productrate', '商品別規定', 'DELIVERY', 'Naoki Hirata', 'Magic3.org', 'GPL', 0,      10,                now(),         now());
DELETE FROM _iwidgets WHERE iw_widget_id = '' AND iw_id = 'ec_main,weightrate';
INSERT INTO _iwidgets
(iw_widget_id, iw_id,        iw_name,           iw_type,    iw_params,                   iw_author,      iw_copyright, iw_license, iw_license_type, iw_official_level, iw_install_dt, iw_create_dt) VALUES
('',    'ec_main,weightrate', '送付先+重量基準', 'DELIVERY', 'wc_support=shipping-zones', 'Naoki Hirata', 'Magic3.org', 'GPL', 0,      10,                now(),         now());
-- インナーウィジェット(支払方法)
DELETE FROM _iwidgets WHERE iw_widget_id = '' AND iw_id = 'ec_main,epsilon';
INSERT INTO _iwidgets
(iw_widget_id, iw_id,     iw_name,          iw_type,    iw_author,      iw_copyright, iw_license,               iw_license_type, iw_official_level, iw_online, iw_install_dt, iw_create_dt) VALUES
('', 'ec_main,epsilon', 'イプシロン決済', 'PAYMENT', 'Naoki Hirata', 'Magic3.org', 'GPL', 0,               10,                true,      now(),         now());
DELETE FROM _iwidgets WHERE iw_widget_id = '' AND iw_id = 'ec_main,exchange_classrate';
INSERT INTO _iwidgets
(iw_widget_id, iw_id,     iw_name,          iw_type,    iw_author,      iw_copyright, iw_license,               iw_license_type, iw_official_level, iw_online, iw_install_dt, iw_create_dt) VALUES
('', 'ec_main,exchange_classrate', '代金引換(購入額基準)', 'PAYMENT', 'Naoki Hirata', 'Magic3.org', 'GPL', 0,               10,                false,      now(),         now());
-- インナーウィジェット(注文計算)
DELETE FROM _iwidgets WHERE iw_widget_id = '' AND iw_id = 'ec_main,lotbuying';
INSERT INTO _iwidgets
(iw_widget_id, iw_id,       iw_name,          iw_type,     iw_author,      iw_copyright, iw_license,               iw_license_type, iw_official_level, iw_install_dt, iw_create_dt) VALUES
('', 'ec_main,lotbuying', 'まとめ買い割引', 'CALCORDER', 'Naoki Hirata', 'Magic3.org', 'GPL', 0,               10,                now(),         now());
DELETE FROM _iwidgets WHERE iw_widget_id = '' AND iw_id = 'ec_main,product_lotbuying';
INSERT INTO _iwidgets
(iw_widget_id, iw_id,               iw_name,                iw_type,     iw_author,      iw_copyright, iw_license,                iw_license_type, iw_official_level, iw_install_dt, iw_create_dt) VALUES
('', 'ec_main,product_lotbuying', '商品別まとめ買い割引', 'CALCORDER', 'Naoki Hirata', 'Magic3.org', 'GPL', 0,               10,                now(),         now());

-- *** システム標準テーブル ***
-- -- 商品情報マスター
ALTER TABLE product ADD pt_download      BOOLEAN        DEFAULT false                 NOT NULL;      -- ダウンロード商品かどうか
ALTER TABLE product ADD pt_delivery      BOOLEAN        DEFAULT true                 NOT NULL;      -- 配送が必要な商品かどうか

