-- *
-- * データ登録スクリプト「開発ウィジェット登録」
-- *
-- * PHP versions 5
-- *
-- * LICENSE: This source file is licensed under the terms of the GNU General Public License.
-- *
-- * @package    Magic3 Framework
-- * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
-- * @copyright  Copyright 2006-2014 Magic3 Project.
-- * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
-- * @version    SVN: $Id$
-- * @link       http://www.magic3.org
-- *
-- [開発ウィジェット登録]   *****仕様変更あり注意*****
-- 開発中のウィジェットの登録を行う。
-- ・フォトギャラリー関係
-- ・スマートフォンブログ関係

-- フォトギャラリー設定マスター

-- ウィジェット情報
DELETE FROM _widgets WHERE wd_id = 'photo_cart';
INSERT INTO _widgets 
(wd_id,        wd_name,                     wd_version, wd_author,      wd_copyright, wd_license,                wd_license_type, wd_official_level, wd_description, wd_has_admin, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('photo_cart', 'フォトギャラリー - カート', '1.1.0',    'Naoki Hirata', 'Magic3.org', 'Magic3 Commerce License', 1,               10,                'フォトギャラリーのカート内の商品を表示するオプションウィジェット。',   true,    true,           100, 0, 0, '2012-11-24',now(),      now());
DELETE FROM _widgets WHERE wd_id = 'photo_shop';
INSERT INTO _widgets
(wd_id,     wd_name,              wd_type, wd_edit_content, wd_version, wd_author,      wd_copyright, wd_license, wd_license_type, wd_official_level, wd_description,                                                        wd_has_admin, wd_initialized, wd_cache_type, wd_view_control_type, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('photo_shop', 'フォトギャラリー - ショップ', 'product', true, '1.0.0',    'Naoki Hirata', 'Magic3.org', 'Magic3 Commerce License',   1,   10,                'フォトギャラリーの商品購入オプションウィジェット。', true,         true,   0, 2, '2012-11-24',now(), now());
DELETE FROM _widgets WHERE wd_id = 'photo_login';
INSERT INTO _widgets
(wd_id,         wd_name,                       wd_version, wd_author,      wd_copyright, wd_license,                wd_license_type, wd_official_level, wd_description, wd_add_script_lib, wd_has_admin, wd_initialized, wd_cache_type, wd_view_control_type, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('photo_login', 'フォトギャラリー - ログイン', '1.0.0',    'Naoki Hirata', 'Magic3.org', 'Magic3 Commerce License', 1,               10, 'フォトギャラリーの会員ログイン用ボックス。', 'md5',     false,     true,           2, -1, '2012-11-24',now(), now());
DELETE FROM _widgets WHERE wd_id = 'ec_product_carousel';
INSERT INTO _widgets
(wd_id,         wd_name,               wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_add_script_lib, wd_add_script_lib_a,   wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_release_dt, wd_install_dt, wd_create_dt) VALUES
('ec_product_carousel', 'Eコマース - 商品カルーセル表示', '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10, '商品画像をランダムでカルーセル表示する。', 'jquery.cloudcarousel,jquery.mousewheel', '',   true,        false,               false,true,           0, 0, '2012-11-01', now(), now());

-- インナーウィジェット(配送方法)
DELETE FROM _iwidgets WHERE iw_widget_id = 'photo_shop' AND iw_id = 'flatrate';
INSERT INTO _iwidgets
(iw_widget_id, iw_id,      iw_name,    iw_type,    iw_author,      iw_copyright, iw_license, iw_license_type, iw_official_level, iw_install_dt, iw_create_dt) VALUES
('photo_shop', 'flatrate', '定額', 'DELIVERY', 'Naoki Hirata', 'Magic3.org', 'Magic3 Commerce License', 1,      10,                now(),         now());
DELETE FROM _iwidgets WHERE iw_widget_id = 'photo_shop' AND iw_id = 'classrate';
INSERT INTO _iwidgets
(iw_widget_id, iw_id,       iw_name,    iw_type,    iw_author,      iw_copyright, iw_license, iw_license_type, iw_official_level, iw_install_dt, iw_create_dt) VALUES
('photo_shop', 'classrate', '購入額基準', 'DELIVERY', 'Naoki Hirata', 'Magic3.org', 'Magic3 Commerce License', 1,      10,                now(),         now());
DELETE FROM _iwidgets WHERE iw_widget_id = 'photo_shop' AND iw_id = 'staterate';
INSERT INTO _iwidgets
(iw_widget_id, iw_id,       iw_name,    iw_type,    iw_author,      iw_copyright, iw_license, iw_license_type, iw_official_level, iw_install_dt, iw_create_dt) VALUES
('photo_shop', 'staterate', '送付先基準', 'DELIVERY', 'Naoki Hirata', 'Magic3.org', 'Magic3 Commerce License', 1,      10,                now(),         now());
DELETE FROM _iwidgets WHERE iw_widget_id = 'photo_shop' AND iw_id = 'quantityrate';
INSERT INTO _iwidgets
(iw_widget_id, iw_id,       iw_name,    iw_type,    iw_author,      iw_copyright, iw_license, iw_license_type, iw_official_level, iw_install_dt, iw_create_dt) VALUES
('photo_shop', 'quantityrate', '商品数基準', 'DELIVERY', 'Naoki Hirata', 'Magic3.org', 'Magic3 Commerce License', 1,      10,                now(),         now());
DELETE FROM _iwidgets WHERE iw_widget_id = 'photo_shop' AND iw_id = 'productrate';
INSERT INTO _iwidgets
(iw_widget_id, iw_id,       iw_name,    iw_type,    iw_author,      iw_copyright, iw_license, iw_license_type, iw_official_level, iw_install_dt, iw_create_dt) VALUES
('photo_shop', 'productrate', '商品別規定', 'DELIVERY', 'Naoki Hirata', 'Magic3.org', 'Magic3 Commerce License', 1,      10,                now(),         now());
DELETE FROM _iwidgets WHERE iw_widget_id = 'photo_shop' AND iw_id = 'weightrate';
INSERT INTO _iwidgets
(iw_widget_id, iw_id,       iw_name,    iw_type,    iw_author,      iw_copyright, iw_license, iw_license_type, iw_official_level, iw_install_dt, iw_create_dt) VALUES
('photo_shop', 'weightrate', '送付先+重量基準', 'DELIVERY', 'Naoki Hirata', 'Magic3.org', 'Magic3 Commerce License', 1,      10,                now(),         now());
-- インナーウィジェット(支払方法)
DELETE FROM _iwidgets WHERE iw_widget_id = 'photo_shop' AND iw_id = 'epsilon';
INSERT INTO _iwidgets
(iw_widget_id, iw_id,     iw_name,          iw_type,    iw_author,      iw_copyright, iw_license,               iw_license_type, iw_official_level, iw_online, iw_install_dt, iw_create_dt) VALUES
('photo_shop', 'epsilon', 'イプシロン決済', 'PAYMENT', 'Naoki Hirata', 'Magic3.org', 'Magic3 Commerce License', 1,               10,                true,      now(),         now());
DELETE FROM _iwidgets WHERE iw_widget_id = 'photo_shop' AND iw_id = 'exchange_classrate';
INSERT INTO _iwidgets
(iw_widget_id, iw_id,     iw_name,          iw_type,    iw_author,      iw_copyright, iw_license,               iw_license_type, iw_official_level, iw_online, iw_install_dt, iw_create_dt) VALUES
('photo_shop', 'exchange_classrate', '代金引換(購入額基準)', 'PAYMENT', 'Naoki Hirata', 'Magic3.org', 'Magic3 Commerce License', 1,               10,                false,      now(),         now());
-- インナーウィジェット(注文計算)
DELETE FROM _iwidgets WHERE iw_widget_id = 'photo_shop' AND iw_id = 'lotbuying';
INSERT INTO _iwidgets
(iw_widget_id, iw_id,       iw_name,          iw_type,     iw_author,      iw_copyright, iw_license,               iw_license_type, iw_official_level, iw_install_dt, iw_create_dt) VALUES
('photo_shop', 'lotbuying', 'まとめ買い割引', 'CALCORDER', 'Naoki Hirata', 'Magic3.org', 'Magic3 Commerce License', 1,               10,                now(),         now());
DELETE FROM _iwidgets WHERE iw_widget_id = 'photo_shop' AND iw_id = 'product_lotbuying';
INSERT INTO _iwidgets
(iw_widget_id, iw_id,               iw_name,                iw_type,     iw_author,      iw_copyright, iw_license,                iw_license_type, iw_official_level, iw_install_dt, iw_create_dt) VALUES
('photo_shop', 'product_lotbuying', '商品別まとめ買い割引', 'CALCORDER', 'Naoki Hirata', 'Magic3.org', 'Magic3 Commerce License', 1,               10,                now(),         now());

-- インナーウィジェットメソッド定義マスター
TRUNCATE TABLE _iwidget_method;
INSERT INTO _iwidget_method 
(id_type,     id_id, id_language_id, id_name,              id_iwidget_id,          id_index) VALUES 
('CALCORDER', 1,     'ja',           '画像まとめ買い割引', 'photo_shop,lotbuying', 1),
('CALCORDER', 2,     'ja',           'フォト商品まとめ買い割引', 'photo_shop,product_lotbuying', 2);

-- 商品価格マスター
INSERT INTO product_price
(pp_product_class, pp_product_id, pp_product_type_id, pp_language_id, pp_price_type_id, pp_currency_id, pp_price) VALUES 
('photo',          0,             'download',         'ja',           'selling',        'JPY',          100);
-- ('photo',          1,             'download',         'ja',           'selling',        'JPY',          200);

-- 個人情報追加フィールド
TRUNCATE TABLE person_info_opt_field;
INSERT INTO person_info_opt_field
(pf_id,    pf_language_id, pf_name,   pf_field_type, pf_index) VALUES
('sports', 'ja',           'スポーツ', 'text',        1);

-- 管理画面メニューデータ
DELETE FROM _nav_item;
INSERT INTO _nav_item
(ni_id, ni_parent_id, ni_index, ni_nav_id,    ni_task_id,        ni_view_control, ni_param, ni_name,                ni_help_title,          ni_help_body) VALUES
(100,   0,            0,        'admin_menu', '_page',           0,               '',       '画面管理',             '画面管理',             'Webサイトのデザインや機能を管理します。'),
(101,   100,          0,        'admin_menu', 'pagedef',         0,               '',       'PC用画面',         'PC用画面編集',         'PC用Webサイトの画面を作成します。'),
(102,   100,          1,        'admin_menu', 'pagedef_mobile',  0,               '',       '携帯用画面',       '携帯用画面編集',       '携帯用Webサイトの画面を作成します。'),
(103,   100,          2,        'admin_menu', 'pagedef_smartphone',  0,           '',       'スマートフォン用画面', 'スマートフォン用画面編集',       'スマートフォン用Webサイトの画面を作成します。'),
(104,   100,          3,        'admin_menu', '_104',            3,               '',       'セパレータ',                 '',                     ''),
(105,   100,          4,        'admin_menu', 'widgetlist',      0,               '',       'ウィジェット管理',     'ウィジェット管理',     'ウィジェットの管理を行います。'),
(106,   100,          5,        'admin_menu', 'templist',        0,               '',       'テンプレート管理',     'テンプレート管理',     'テンプレートの管理を行います。'),
(107,   100,          6,        'admin_menu', 'smenudef',        0,               '',       'メニュー管理', 'メニュー管理', 'メニュー定義を管理します。'),
(199,   0,            1,        'admin_menu', '_199',            1,               '',       '改行',                 '',                     ''),
(200,   0,            2,        'admin_menu', '_login',          0,               '',       'システム運用',         '',                     ''),
(201,   200,          0,        'admin_menu', 'userlist',        0,               '',       'ユーザ一覧',           'ユーザ一覧',           'ログイン可能なユーザを管理します。'),
(202,   200,          1,        'admin_menu', 'accesslog',     0,               '',       '運用状況', '運用状況', 'サイトの運用状況を表示します。'),
(299,   0,            3,        'admin_menu', '_299',            1,               '',       '改行',                 '',                     ''),
(300,   0,            4,        'admin_menu', '_config',         0,               '',       'システム管理',         '',                     ''),
(301,   300,          0,        'admin_menu', 'configsite',      0,               '',       '基本情報',             '基本情報',             'サイト運営に必要な情報を設定します。'),
(302,   300,          1,        'admin_menu', 'configsys',       0,               '',       'システム情報',         'システム情報',         'システム全体の設定、運用状況を管理します。'),
(303,   300,          2,        'admin_menu', 'mainte',          0,               '',       'メンテナンス', 'メンテナンス', 'ファイルやDBなどのメンテナンスを行います。'),
(399,   0,            5,        'admin_menu', '_399',            1,               '',       '改行',                 '',                     ''),
(500,   0,            6,        'admin_menu', '_daily',          0,               '',           '日常処理', '', ''),
(501,   500,          0,        'admin_menu', 'configwidget_ec_main',       0,               'task=order', '受注管理', '受注管理', '受注管理を行います。'),
(502,   500,          1,        'admin_menu', 'configwidget_ec_main',       0,               'task=product', '商品管理', '商品管理', '商品管理を行います。'),
(503,   500,          2,        'admin_menu', 'configwidget_ec_main',       0,               'task=member',   '会員管理', '会員管理', '会員情報を管理します。');
