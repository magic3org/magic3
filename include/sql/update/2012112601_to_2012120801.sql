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
-- * @version    SVN: $Id: 2012112601_to_2012120801.sql 5604 2013-02-06 15:22:36Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
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
DELETE FROM _mail_form WHERE mf_id = 'order_product_to_shop_manager';
INSERT INTO _mail_form (mf_id,           mf_language_id, mf_subject,         mf_content,                                                                 mf_create_dt) 
VALUES                 ('order_product_to_shop_manager', 'ja',           '商品受注',         '■受注コード：[#ORDER_NO#]■\n■受注日付：[#DATE#]■\n■会員コード：[#MEMBER_NO#]■\n■会員名：[#NAME#]■\n■会員Eメール：[#EMAIL#]■\n■管理画面URL：[#ADMIN_URL#]■\n■届先名：[#DELIV_NAME#]■\n■届先郵便番号：[#ZIPCODE#]■\n■届先都道府県：[#STATE#]■\n■届先住所１：[#ADDRESS1#]■\n■届先住所２：[#ADDRESS2#]■\n■届先電話番号：[#PHONE#]■\n■配達希望日：[#DEMAND_DATE#]■\n■配達時間帯：[#DEMAND_TIME#]■\n[#BODY#]■配送方法：[#DELIV_METHOD#]■\n■決済方法：[#PAY_METHOD#]■\n■備考：[#NOTE#]■\n\n**********\nお届け先\n**********\n[#DELIV_TEXT#]\n**********\n注文内容\n**********\n[#ORDER_TEXT#]\n', now());
DELETE FROM _mail_form WHERE mf_id = 'order_product_to_customer';
INSERT INTO _mail_form (mf_id,           mf_language_id, mf_subject,         mf_content,                                                                 mf_create_dt) 
VALUES                 ('order_product_to_customer', 'ja',           'ご注文の確認(自動送信)',         '[#NAME#] 様\n\nこの度は[#SHOP_NAME#]をご利用頂きまして誠にありがとうございます。\n下記の通りご注文を承りましたのでご確認ください。\n\n**********\nお届け先\n**********\n[#DELIV_TEXT#]\n**********\n注文内容\n**********\n[#ORDER_TEXT#]\n\n[#SIGNATURE#]', now());

-- *** システム標準テーブル ***
-- 支払い方法マスター
TRUNCATE TABLE pay_method_def;
INSERT INTO pay_method_def (po_id, po_language_id, po_name, po_index) VALUES ('payment_service',  'ja', '決済サービス', 1);

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

-- 商品タイプマスター
INSERT INTO product_type
(py_product_class, py_id,      py_language_id, py_name,            py_code, py_description, py_index, py_single_select) VALUES 
('',               '',         'ja',           '標準商品',         'ST',    '',             1,             false),
('photo',          '',         'ja',           '標準商品',         'ST',    '',             1,             false),
('photo',          'download', 'ja',           'ダウンロード画像', 'DL',    '',             2,             true);

-- 商品クラスマスター
INSERT INTO product_class
(pu_id,   pu_language_id, pu_name,                pu_index) VALUES 
('',      'ja',           '一般商品',             1),
('photo', 'ja',           'フォトギャラリー商品', 2);


