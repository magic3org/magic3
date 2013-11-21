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
-- * @version    SVN: $Id: 2009082701_to_2009091901.sql 2402 2009-10-08 07:42:03Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
-- システム設定マスター
INSERT INTO _system_config 
(sc_id,                 sc_value,         sc_name) VALUES
('use_page_cache',      '0',              '画面キャッシュ'),
('page_cache_lifetime', '1440',           '画面キャッシュの保持時間(分)');	-- 1日ごと

-- ウィジェット情報マスター
ALTER TABLE _widgets ADD wd_cache_type    INT            DEFAULT 0                     NOT NULL;      -- キャッシュタイプ(0=不可、1=可、2=非ログイン時可, 3=ページキャッシュのみ可)
ALTER TABLE _widgets ADD wd_cache_lifetime    INT        DEFAULT 0                     NOT NULL;      -- キャッシュの保持時間(分)
ALTER TABLE _widgets ADD wd_view_control_type INT        DEFAULT 0                     NOT NULL;      -- 表示出力の制御タイプ(-1=固定、0=可変、1=ウィジェットパラメータ可変、2=URLパラメータ可変)

-- ウィジェットパラメータマスター
ALTER TABLE _widget_param ADD wp_cache_html        TEXT                                         NOT NULL;      -- キャッシュデータ
ALTER TABLE _widget_param ADD wp_cache_title       TEXT                                         NOT NULL;      -- キャッシュヘッダタイトル
ALTER TABLE _widget_param ADD wp_cache_user_id     INT            DEFAULT 0                     NOT NULL;      -- キャッシュ更新者
ALTER TABLE _widget_param ADD wp_cache_update_dt   TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL;      -- キャッシュ更新日時

-- キャッシュトラン
DROP TABLE IF EXISTS _cache;
CREATE TABLE _cache (
    ca_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    ca_widget_id         VARCHAR(50)    DEFAULT ''                    NOT NULL,      -- ウィジェットID(ファイル名)
    ca_url               VARCHAR(200)   DEFAULT ''                    NOT NULL,      -- アクセスURL
    
    ca_page_id           VARCHAR(50)    DEFAULT ''                    NOT NULL,      -- ページID
    ca_page_sub_id       VARCHAR(50)    DEFAULT ''                    NOT NULL,      -- ページ補助ID
    ca_html              TEXT                                         NOT NULL,      -- キャッシュデータ
    
    ca_update_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード更新者
    ca_update_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード更新日時
    PRIMARY KEY          (ca_serial),
    UNIQUE               (ca_widget_id, ca_url)
) TYPE=innodb;

-- ページ情報マスター
DELETE FROM _page_info;
INSERT INTO _page_info
(pn_id,     pn_sub_id,   pn_content_type, pn_use_ssl) VALUES
('index',   'content',   'content',       false),
('index',   'shop',      'product',       false),
('index',   'shop_safe', '',              true),
('index',   'bbs',       'bbs',           false),
('index',   'blog',      'blog',          false),
('index',   'wiki',      'wiki',          false),
('index',   'contact',   '',              true),
('index',   'safe',      '',              true),
('m_index', 'content',   'content',       false),
('m_index', 'shop',      'product',       false),
('m_index', 'bbs',       'bbs',           false),
('m_index', 'blog',      'blog',          false),
('m_index', 'wiki',      'wiki',          false);

-- ウィジェット情報
DELETE FROM _widgets WHERE wd_id = 'default_mainmenu';
INSERT INTO _widgets (wd_id, wd_name, wd_type, wd_version, wd_joomla_class, wd_author, wd_copyright, wd_license, wd_official_level, wd_description, wd_read_scripts, wd_read_css, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('default_mainmenu',   'デフォルトメインメニュー(廃止予定)',   'DMEN', '1.0.0',  '_menu',        'Naoki Hirata', 'Magic3.org', 'GPL', 10, '常に表示し共通で使用するメインメニュー。',         false,           false,       true,         true,        true,         false,               false,true,           0, 2, -1, now(), now());
DELETE FROM _widgets WHERE wd_id = 'default_menu';
INSERT INTO _widgets
(wd_id,                wd_name,                       wd_version, wd_joomla_class, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_add_script_lib, wd_add_script_lib_a,                         wd_has_admin, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('default_menu', 'デフォルトメニュー',         '1.0.0',    '_menu', 'Naoki Hirata', 'Magic3.org', 'GPL',      10,                '複数のメニューが作成できる1階層の新型デフォルトメニューです。サブメニューも管理できる共通のメニュー定義を使用します。ナビゲーションメニューの位置(画面上部)にある「user3」ポジションに配置するとナビゲーションメニューが表示できます。', '', '', true,  true,              true, 2,          1, now(),         now());
DELETE FROM _widgets WHERE wd_id = 'nav_menu';
INSERT INTO _widgets (wd_id, wd_name, wd_type, wd_version, wd_params, wd_author, wd_copyright, wd_license, wd_official_level, wd_description, wd_read_scripts, wd_read_css, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('nav_menu',   'ナビゲーションメニュー',   'NAVM', '1.0.0',  '',        'Naoki Hirata', 'Magic3.org', 'GPL', 10, 'ページのヘッダ下や、フッタ部に表示するメニュー。',           false,           false,       true,         true,        true,         false,               false,true,           0, 1, -1, now(), now());
DELETE FROM _widgets WHERE wd_id = 'nav_menu_css';
INSERT INTO _widgets (wd_id, wd_name, wd_type, wd_version, wd_params, wd_author, wd_copyright, wd_license, wd_official_level, wd_description, wd_read_scripts, wd_read_css, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('nav_menu_css',   'ナビゲーションメニュー(CSS)',   'NAV2', '1.0.0',  '',        '', 'Magic3.org', 'GPL', 10, 'ページのヘッダ下や、フッタ部に表示するメニュー。SPANタグにて表示。',           false,           false,       true,         true,        true,         false,               false,true,           0, 1, -1, now(), now());
DELETE FROM _widgets WHERE wd_id = 'default_footer';
INSERT INTO _widgets (wd_id, wd_name, wd_type, wd_version, wd_params, wd_author, wd_copyright, wd_license, wd_official_level, wd_description, wd_read_scripts, wd_read_css, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('default_footer',     'デフォルトフッタ',           'DFOT', '1.0.0',  '',        'Naoki Hirata', 'Magic3.org', 'GPL', 10, 'フッタ部分に表示し、著作権の表示を行う。',          false,           false,       true,         true,        false,        false,               false,true,           0, 1, -1, now(), now());
DELETE FROM _widgets WHERE wd_id = 'custom_header';
INSERT INTO _widgets (wd_id, wd_name, wd_type, wd_version, wd_params, wd_author, wd_copyright, wd_license, wd_official_level, wd_description, wd_read_scripts, wd_read_css, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('custom_header',     'カスタムヘッダ',           'DFHT', '1.0.0',  '',        'Naoki Hirata', 'Magic3.org', 'GPL', 10, 'ヘッダ部分で画像やサイトのタイトル文字列をカスタマイズ。',          false,           false,       true,         true,        true,        false,               false,true,           0, 3, -1, now(), now());
DELETE FROM _widgets WHERE wd_id = 'ec_login_box';
INSERT INTO _widgets (wd_id, wd_name, wd_type, wd_version, wd_params, wd_author, wd_copyright, wd_license, wd_official_level, wd_description, wd_add_script_lib, wd_read_scripts, wd_read_css, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('ec_login_box',       'Eコマース - ログイン',       'ELOG', '1.0.0',  '',        'Naoki Hirata', 'Magic3.org', 'GPL', 10, 'Eコマースの会員ログイン用ボックス。', 'md5',         false,           false,       true,         true,        false,        false,               false,true,           0, 2, -1, now(), now());
DELETE FROM _widgets WHERE wd_id = 'custom_footer';
INSERT INTO _widgets (wd_id, wd_name, wd_type, wd_version, wd_params, wd_author, wd_copyright, wd_license, wd_official_level, wd_description, wd_read_scripts, wd_read_css, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('custom_footer',     'カスタムフッタ',           'CSFT', '1.0.0',  '',        'Naoki Hirata', 'Magic3.org', 'GPL', 10, 'フッタ部に表示し、著作権等の表示を行う。',          false,           false,       true,         true,        true,        false,               false,true,           0, 1, -1, now(), now());
DELETE FROM _widgets WHERE wd_id = 'separator';
INSERT INTO _widgets (wd_id, wd_name, wd_type, wd_version, wd_params, wd_author, wd_copyright, wd_license, wd_official_level, wd_description, wd_read_scripts, wd_read_css, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('separator',          'セパレータ',                 'SEPT', '1.0.0',  '',        'Naoki Hirata', 'Magic3.org', 'GPL', 10, 'ウィジェット間の区切り線。',          false,           false,       true,         true,        false,         false,               false,true,          0, 1, -1, now(), now());
DELETE FROM _widgets WHERE wd_id = 'templateChanger';
INSERT INTO _widgets (wd_id, wd_name, wd_version, wd_params, wd_author, wd_copyright, wd_license, wd_official_level, wd_description, wd_add_script_lib, wd_add_script_lib_a, wd_read_scripts, wd_read_css, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('templateChanger',    'テンプレートチェンジャー',   '1.0.0',  '',        'Naoki Hirata', 'Magic3.org', 'GPL', 10, 'エンドユーザからテンプレートの変更を可能にする。', 'jquery', '',         false,           false,       true,         true,        false,        false,               false,true,           0, 1, -1, now(), now());
DELETE FROM _widgets WHERE wd_id = 'access_count';
INSERT INTO _widgets (wd_id, wd_name, wd_type, wd_version, wd_params, wd_author, wd_copyright, wd_license, wd_official_level, wd_description, wd_read_scripts, wd_read_css, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('access_count',       'アクセスカウンター',         'ACCT', '1.0.0',  '',        'Naoki Hirata', 'Magic3.org', 'GPL', 10, 'サイトのアクセス数を表示。',          false,           false,       true,         true,        false,        false,               false,true,           0, 0, 0, now(), now());
DELETE FROM _widgets WHERE wd_id = 'dg_clock';
INSERT INTO _widgets (wd_id, wd_name, wd_type, wd_version, wd_params, wd_author, wd_copyright, wd_license, wd_official_level, wd_description, wd_read_scripts, wd_read_css, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('dg_clock',           'デジタル時計',               'DGCK', '1.0.0',  '',        'Naoki Hirata', 'Magic3.org', 'GPL', 10, 'デジタル時計。',          false,           false,       true,         true,        false,        false,               false,true,           0, 0, -1, now(), now());
DELETE FROM _widgets WHERE wd_id = 'default_content';
INSERT INTO _widgets (wd_id, wd_name, wd_type, wd_version, wd_params, wd_author, wd_copyright, wd_license, wd_official_level, wd_description, wd_read_scripts, wd_read_css, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('default_content',    'デフォルトコンテンツビュー', 'content', '1.0.0',  '',        'Naoki Hirata', 'Magic3.org', 'GPL', 10, '画像やテキストからできた様々なコンテンツを表示。表示コンテンツはURLパラメータで指定。コンテンツ(content)ページ専用。',          false,           false,       true,         true,        true,         false,               false,true,           0, 2, 2, now(), now());
DELETE FROM _widgets WHERE wd_id = 'static_content';
INSERT INTO _widgets (wd_id, wd_name, wd_type, wd_version, wd_params, wd_author, wd_copyright, wd_license, wd_official_level, wd_description, wd_read_scripts, wd_read_css, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('static_content',    '固定コンテンツビュー', '', '1.0.0',  '',        'Naoki Hirata', 'Magic3.org', 'GPL', 10, '固定でコンテンツを表示。コンテンツはデフォルトコンテンツビューと共有。',          false,           false,       true,         true,        true,         false,               true,true,           0, 2, 1, now(), now());
DELETE FROM _widgets WHERE wd_id = 'content_search_box';
INSERT INTO _widgets (wd_id, wd_name, wd_version, wd_params, wd_author, wd_copyright, wd_license, wd_official_level, wd_description, wd_read_scripts, wd_read_css, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('content_search_box',   'コンテンツ - 検索', '1.0.0',  '',        'Naoki Hirata', 'Magic3.org', 'GPL', 10, 'コンテンツを検索するためのボックス。',        false,           false,       true,         true,        false,         false,               false,true,               0, 1, -1, now(),    now());
DELETE FROM _widgets WHERE wd_id = 'news';
INSERT INTO _widgets (wd_id, wd_name, wd_type, wd_version, wd_params, wd_author, wd_copyright, wd_license, wd_official_level, wd_description, wd_read_scripts, wd_read_css, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('news',    'お知らせ', 'NEWS', '1.0.0',  '',        'Naoki Hirata', 'Magic3.org', 'GPL', 10, 'お知らせを表示。',          false,           false,       true,         true,        true,         false,               true,true,           0, 1, 1, now(), now());
DELETE FROM _widgets WHERE wd_id = 'blogparts_box';
INSERT INTO _widgets (wd_id, wd_name, wd_type, wd_version, wd_params, wd_author, wd_copyright, wd_license, wd_official_level, wd_description, wd_read_scripts, wd_read_css, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('blogparts_box',    'ブログパーツ', 'BPAR', '1.0.0',  '',        'Naoki Hirata', 'Magic3.org', 'GPL', 10, 'ブログパーツを表示',          false,           false,       true,         true,        true,         false,               true,true,           0, 1, 1, now(), now());
DELETE FROM _widgets WHERE wd_id = 'ec_menu';
INSERT INTO _widgets (wd_id, wd_name, wd_type, wd_version, wd_joomla_class, wd_author, wd_copyright, wd_license, wd_official_level, wd_description, wd_read_scripts, wd_read_css, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('ec_menu',   'Eコマース - 商品メニュー',   'ECMN', '1.0.0',  '_menu',        'Naoki Hirata', 'Magic3.org', 'GPL', 10, 'Eコマースの商品メニュー。カテゴリで取得した商品一覧をEコマースメインウィジェットに表示させる。',          false,           false,       true,         true,        true,         false,               false,true,           0, 1, -1, now(), now());
DELETE FROM _widgets WHERE wd_id = 'ec_category_menu';
INSERT INTO _widgets (wd_id, wd_name, wd_type, wd_version, wd_params, wd_author, wd_copyright, wd_license, wd_official_level, wd_description, wd_read_scripts, wd_read_css, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('ec_category_menu',   'Eコマース - カテゴリーメニュー', 'ECCM', '1.0.0',  '',        'Naoki Hirata', 'Magic3.org', 'GPL', 10, 'Eコマースカテゴリメニュー',          false,           false,       true,         true,        true,         false,               false,true,           0, 1, -1, now(), now());
DELETE FROM _widgets WHERE wd_id = 'ec_cart_box';
INSERT INTO _widgets (wd_id, wd_name, wd_type, wd_version, wd_params, wd_author, wd_copyright, wd_license, wd_official_level, wd_description, wd_read_scripts, wd_read_css, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('ec_cart_box',        'Eコマース - カート',     'ECCT', '1.0.0',  '',        'Naoki Hirata', 'Magic3.org', 'GPL', 10, 'Eコマースでカート内の商品を表示するボックス。',         false,           false,       true,         true,        false,         false,               false,true,           100, 0, 0, now(),      now());
DELETE FROM _widgets WHERE wd_id = 'ec_product_header';
INSERT INTO _widgets (wd_id, wd_name, wd_type, wd_version, wd_params, wd_author, wd_copyright, wd_license, wd_official_level, wd_description, wd_read_scripts, wd_read_css, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('ec_product_header',               'Eコマース - 商品ヘッダ',                   'ECHD', '1.0.0',  '',  'Naoki Hirata', 'Magic3.org', 'GPL', 10, 'Eコマース商品メニューと連携するウィジェットです。商品メニュー選択時に商品ヘッダコンテンツを表示します。',     false,           false,       true,         true,        false,        false,               false,true,           0, 1, 2, now(), now());
DELETE FROM _widgets WHERE wd_id = 'ec_product_display';
INSERT INTO _widgets
(wd_id, wd_name, wd_type, wd_version, wd_params, wd_author, wd_copyright, wd_license, wd_official_level, wd_description, wd_read_scripts, wd_read_css, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('ec_product_display',            'Eコマース - 新着おすすめ',         'ECPD', '1.0.0',  '',        'Naoki Hirata', 'Magic3.org', 'GPL', 10, '商品のおすすめや新着などの一覧を表示する。',          false,           false,       true,         true,        true,         false,               true,true,           0, 1, 2, now(), now());
DELETE FROM _widgets WHERE wd_id = 'ec_search_box';
INSERT INTO _widgets (wd_id, wd_name, wd_type, wd_version, wd_params, wd_author, wd_copyright, wd_license, wd_official_level, wd_description, wd_read_scripts, wd_read_css, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('ec_search_box',   'Eコマース - 検索',            'ECSR', '1.0.0',  '',        'Naoki Hirata', 'Magic3.org', 'GPL', 10, 'Eコマース商品検索ボックス。',         false,           false,       true,         true,        false,         false,               false,true,               0, 1, -1, now(),    now());
DELETE FROM _widgets WHERE wd_id = 'blog_main';
INSERT INTO _widgets (wd_id, wd_name, wd_type, wd_version, wd_params, wd_author, wd_copyright, wd_license, wd_official_level, wd_description, wd_add_script_lib_a, wd_read_scripts, wd_read_css, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('blog_main',          'ブログ - メイン',            'BGMA', '1.0.0',  '',        'Naoki Hirata', 'Magic3.org', 'GPL', 10, 'ブログの記事の内容を表示する。', 'entry_detail=jquery-ui-plus',          false,           false,       true,         true,        true,        false,               false,true,               0, 1, 2, now(),    now());
DELETE FROM _widgets WHERE wd_id = 'blog_search_box';
INSERT INTO _widgets (wd_id, wd_name, wd_type, wd_version, wd_params, wd_author, wd_copyright, wd_license, wd_official_level, wd_description, wd_read_scripts, wd_read_css, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('blog_search_box',   'ブログ - 検索',            'BGSR', '1.0.0',  '',        'Naoki Hirata', 'Magic3.org', 'GPL', 10, 'ブログ記事を検索するためのボックス。',        false,           false,       true,         true,        false,         false,               false,true,               0, 1, -1, now(),    now());
DELETE FROM _widgets WHERE wd_id = 'blog_new_box';
INSERT INTO _widgets (wd_id, wd_name, wd_type, wd_version, wd_params, wd_author, wd_copyright, wd_license, wd_official_level, wd_description, wd_read_scripts, wd_read_css, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('blog_new_box',   'ブログ - 新規',            'BGNW', '1.0.0',  '',        'Naoki Hirata', 'Magic3.org', 'GPL', 10, 'ブログの新規一覧を表示。',         false,           false,       true,         true,        false,         false,               false,true,               0, 1, -1, now(),    now());
DELETE FROM _widgets WHERE wd_id = 'blog_calendar_box';
INSERT INTO _widgets (wd_id, wd_name, wd_type, wd_version, wd_params, wd_author, wd_copyright, wd_license, wd_official_level, wd_description, wd_read_scripts, wd_read_css, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('blog_calendar_box',   'ブログ - カレンダー',            'BGCL', '1.0.0',  '',        'Naoki Hirata', 'Magic3.org', 'GPL', 10, 'カレンダーからブログ記事にアクセスするためのボックス。',        false,           false,       true,         true,        false,         false,               false,true,               0, 3, 2, now(),    now());
DELETE FROM _widgets WHERE wd_id = 'bbs_main';
INSERT INTO _widgets (wd_id, wd_name, wd_type, wd_version, wd_params, wd_author, wd_copyright, wd_license, wd_official_level, wd_description, wd_read_scripts, wd_read_css, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('bbs_main',          '掲示板 - メイン',            'BBMA', '1.0.0',  '',        'Naoki Hirata', 'Magic3.org', 'GPL', 10, '掲示板の投稿記事を表示。',         false,           false,       true,         true,        true,        false,               false,true,               0, 0, 2, now(),    now());
DELETE FROM _widgets WHERE wd_id = 'bbs_login_box';
INSERT INTO _widgets (wd_id, wd_name, wd_type, wd_version, wd_params, wd_author, wd_copyright, wd_license, wd_official_level, wd_description, wd_add_script_lib, wd_read_scripts, wd_read_css, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('bbs_login_box',         '掲示板 - ログイン',            'BBML', '1.0.0',  '',        'Naoki Hirata', 'Magic3.org', 'GPL', 10, '掲示板の会員ログイン用ボックス。', 'md5',        false,           false,       true,         true,        false,        false,               false,true,               0, 2, -1, now(),    now());
DELETE FROM _widgets WHERE wd_id = 'contactus';
INSERT INTO _widgets (wd_id, wd_name, wd_type, wd_version, wd_params, wd_author, wd_copyright, wd_license, wd_official_level, wd_description, wd_read_scripts, wd_read_css, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('contactus',          'お問い合わせメール送信',            'CONU', '1.0.0',  '',        'Naoki Hirata', 'Magic3.org', 'GPL', 10, 'お問い合わせの入力データをメールで送る。',          false,           false,       true,         true,        true,        false,               false,true,               0, 0, 0, now(),    now());
DELETE FROM _widgets WHERE wd_id = 'joomla_clock';
INSERT INTO _widgets (wd_id, wd_name, wd_type, wd_version, wd_params, wd_author, wd_copyright, wd_license, wd_official_level, wd_description, wd_read_scripts, wd_read_css, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('joomla_clock',           'Joomla時計',               'JMCK', '1.0.0',  '',        'Naoki Hirata', 'Magic3.org', 'GPL', 10, 'Joomlaロゴの時計。',          false,           false,       true,         true,        false,        false,               false,true,           0, 1, -1, now(), now());
DELETE FROM _widgets WHERE wd_id = 'qrcode';
INSERT INTO _widgets (wd_id, wd_name, wd_type, wd_version, wd_params, wd_author, wd_copyright, wd_license, wd_official_level, wd_description, wd_read_scripts, wd_read_css, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('qrcode',           'QRコード',               'QRVW', '1.0.0',  '',        'Naoki Hirata', 'Magic3.org', 'GPL', 10, 'QRコードを作成表示。',         false,           false,       true,         true,        true,        true,               false,true,           0, 1, -1, now(), now());
DELETE FROM _widgets WHERE wd_id = 'youtube2';
INSERT INTO _widgets (wd_id, wd_name, wd_type, wd_version, wd_params, wd_author, wd_copyright, wd_license, wd_official_level, wd_description, wd_read_scripts, wd_read_css, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('youtube2',     'YouTube2',           'YOUT', '1.0.0',  '',        'Naoki Hirata', 'Magic3.org', 'GPL', 10, 'YouTubeの投稿動画を表示。',         false,           false,       true,         true,        true,        false,               true,true,           0, 1, 1, now(), now());
DELETE FROM _widgets WHERE wd_id = 'flash';
INSERT INTO _widgets (wd_id, wd_name, wd_type, wd_version, wd_params, wd_author, wd_copyright, wd_license, wd_official_level, wd_description, wd_read_scripts, wd_read_css, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('flash',     'Flash',           '', '1.0.0',  '',        'Naoki Hirata', 'Magic3.org', 'GPL', 10, 'Flashファイルを表示。',         false,           false,       true,         true,        true,        false,               true,true,           0, 1, 1, now(), now());
DELETE FROM _widgets WHERE wd_id = 'release_info';
INSERT INTO _widgets (wd_id, wd_name, wd_type, wd_version, wd_params, wd_author, wd_copyright, wd_license, wd_official_level, wd_description, wd_read_scripts, wd_read_css, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('release_info',               'Magic3リリース情報',                   'RINF', '1.0.0',  '',  'Naoki Hirata', 'Magic3.org', 'GPL', 10, 'Magic3の最新リリース情報を表示。',      false,           false,       true,         true,        false,        false,               false,true,           0, 1, -1, now(), now());
DELETE FROM _widgets WHERE wd_id = 'reserve_main';
INSERT INTO _widgets (wd_id, wd_name, wd_type, wd_version, wd_params, wd_author, wd_copyright, wd_license, wd_official_level, wd_description, wd_read_scripts, wd_read_css, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('reserve_main',            '予約 - メイン',         'RESM', '1.0.0',  '',        'Naoki Hirata', 'Magic3.org', 'GPL', 10, '予約管理のメインプログラム。',          false,           false,       true,         true,        true,         true,               false,true,           0, 0, 2, now(), now());
DELETE FROM _widgets WHERE wd_id = 'ec_product_random_box';
INSERT INTO _widgets
(wd_id, wd_name, wd_type, wd_version, wd_params, wd_author, wd_copyright, wd_license, wd_official_level, wd_description, wd_add_script_lib, wd_read_scripts, wd_read_css, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('ec_product_random_box',               'Eコマース - 商品ランダム表示',                   'ECPR', '1.0.0',  '',  'Naoki Hirata', 'Magic3.org', 'GPL', 10, '商品をランダムに表示する。', 'jquery.easing,jquery.jcarousel',    false,           true,       true,         true,        true,        false,               false,true,           0, 0, 0, now(), now());
DELETE FROM _widgets WHERE wd_id = 'blog_category_menu';
INSERT INTO _widgets (wd_id, wd_name, wd_type, wd_version, wd_params, wd_author, wd_copyright, wd_license, wd_official_level, wd_description, wd_read_scripts, wd_read_css, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('blog_category_menu',   'ブログ - カテゴリーメニュー', 'BGCM', '1.0.0',  '',        'Naoki Hirata', 'Magic3.org', 'GPL', 10, 'ブログカテゴリメニュー',          false,           false,       true,         true,        false,         false,               false,true,           0, 1, -1, now(), now());
DELETE FROM _widgets WHERE wd_id = 'blog_archive_menu';
INSERT INTO _widgets (wd_id, wd_name, wd_type, wd_version, wd_params, wd_author, wd_copyright, wd_license, wd_official_level, wd_description, wd_read_scripts, wd_read_css, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('blog_archive_menu',   'ブログ - アーカイブメニュー', 'BGAM', '1.0.0',  '',        'Naoki Hirata', 'Magic3.org', 'GPL', 10, 'ブログアーカイブメニュー',          false,           false,       true,         true,        false,         false,               false,true,           0, 1, -1, now(), now());
DELETE FROM _widgets WHERE wd_id = 'photoslide';
INSERT INTO _widgets
(wd_id, wd_name, wd_type, wd_version, wd_params, wd_author, wd_copyright, wd_license, wd_official_level, wd_description, wd_add_script_lib, wd_add_script_lib_a, wd_read_scripts, wd_read_css, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('photoslide',               '画像スライドショー',                   '', '1.0.0',  '',  'Naoki Hirata', 'Magic3.org', 'GPL', 10, '画像をスライドショー表示する。', 'jquery.cycle', 'jquery.cycle',   false,           false,       true,         true,        true,        false,               true,true,           0, 3, 1, now(), now());
DELETE FROM _widgets WHERE wd_id = 'phpcode';
INSERT INTO _widgets (wd_id, wd_name, wd_type, wd_version, wd_params, wd_author, wd_copyright, wd_license, wd_official_level, wd_description, wd_add_script_lib_a, wd_read_scripts, wd_read_css, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('phpcode',     'PHPコード実行',           '', '1.0.0',  '',        'Naoki Hirata', 'Magic3.org', 'GPL', 10, 'PHPのプログラムコードを実行。', 'jquery.codepress',        false,           false,       true,         true,        true,        false,               true,true,           0, 0, 0, now(), now());
DELETE FROM _widgets WHERE wd_id = 'g_analytics';
INSERT INTO _widgets (wd_id, wd_name, wd_type, wd_version, wd_params, wd_author, wd_copyright, wd_license, wd_official_level, wd_description, wd_read_scripts, wd_read_css, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('g_analytics',     'Google Analytics',           '', '1.0.0',  '',        'Naoki Hirata', 'Magic3.org', 'GPL', 10, 'Google Analytics トラッキングコードを出力する。',          false,           false,       true,         true,        true,        false,               false,true,           0, 1, -1, now(), now());
DELETE FROM _widgets WHERE wd_id = 'g_qrcode';
INSERT INTO _widgets (wd_id, wd_name, wd_type, wd_version, wd_params, wd_author, wd_copyright, wd_license, wd_official_level, wd_description, wd_read_scripts, wd_read_css, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('g_qrcode',     'Google QRコード',           '', '1.0.0',  '',        'Naoki Hirata', 'Magic3.org', 'GPL', 10, 'Google APIを使ったQRコード表示。',         false,           false,       true,         true,        true,        false,               true,true,           0, 1, 1, now(), now());
DELETE FROM _widgets WHERE wd_id = 'wiki_main';
INSERT INTO _widgets (wd_id, wd_name, wd_type, wd_version, wd_params, wd_author, wd_copyright, wd_license, wd_official_level, wd_description, wd_add_script_lib, wd_add_script_lib_a, wd_read_scripts, wd_read_css, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('wiki_main',          'Wiki - メイン',            '', '1.0.0',  '',        'Naoki Hirata', 'Magic3.org', 'GPL', 10, 'Wikiを表示する。', 'md5', 'md5',         false,           true,       true,         true,        true,        false,               false,true,               0, 1, 2, now(),    now());
DELETE FROM _widgets WHERE wd_id = 'default_login_box';
INSERT INTO _widgets (wd_id, wd_name, wd_version, wd_params, wd_author, wd_copyright, wd_license, wd_official_level, wd_description, wd_add_script_lib, wd_read_scripts, wd_read_css, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('default_login_box',         'デフォルトログイン',   '1.0.0',  '',        'Naoki Hirata', 'Magic3.org', 'GPL', 10, 'デフォルトのログイン用ボックス。', 'md5',        false,           false,       true,         true,        false,        false,               false,true,               0, 2, -1, now(),    now());
DELETE FROM _widgets WHERE wd_id = 'accordion_menu';
INSERT INTO _widgets
(wd_id,            wd_name,                 wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_add_script_lib, wd_add_script_lib_a,                         wd_has_admin, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('accordion_menu', 'アコーディオンメニュー','1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                '複数のメニューが作成できる2階層のアコーディオンメニューです。サブメニューも管理できる共通のメニュー定義を使用します。', 'jquery-ui,jquery-ui-plus', 'jquery-ui,jquery-ui-plus', true,  true,              true, 3,          1, now(),         now());
DELETE FROM _widgets WHERE wd_id = 'dropdown_menu';
INSERT INTO _widgets
(wd_id,            wd_name,                 wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_read_scripts, wd_read_css, wd_add_script_lib, wd_add_script_lib_a,                         wd_has_admin, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('dropdown_menu', 'ドロップダウンメニュー','1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                '複数のメニューが作成できる多階層のドロップダウンメニューです。サブメニューも管理できる共通のメニュー定義を使用します。', true, true, 'jquery.bgiframe,jquery.hoverintent', 'jquery.bgiframe,jquery.hoverintent', true,  true,              true, 3,          1, now(),         now());
DELETE FROM _widgets WHERE wd_id = 'top_content_box';
INSERT INTO _widgets
(wd_id,             wd_name,                    wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_read_scripts, wd_read_css, wd_add_script_lib, wd_add_script_lib_a,                         wd_has_admin, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('top_content_box', 'トップアクセスコンテンツリスト', '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                '閲覧数の多い順からコンテンツリストを表示します。', false, false, '', '', true,  false,              true, 1,          -1, now(),         now());
DELETE FROM _widgets WHERE wd_id = 'update_content_box';
INSERT INTO _widgets
(wd_id,             wd_name,                    wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_read_scripts, wd_read_css, wd_add_script_lib, wd_add_script_lib_a,                         wd_has_admin, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('update_content_box', '更新コンテンツリスト', '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                '最新の更新からコンテンツリストを表示します。', false, false, '', '', true,  false,              true, 1,          -1, now(),         now());
DELETE FROM _widgets WHERE wd_id = 'banner2';
INSERT INTO _widgets
(wd_id,     wd_name,              wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,                                                                     wd_read_scripts, wd_read_css, wd_add_script_lib, wd_add_script_lib_a, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('banner2', 'バナー表示2', '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'バナー画像をグループ化して、グループごとに表示できるバナー管理ウィジェットです。', false,           false,       '',                '',                  true, true,         true,                true,  0,         1, now(),         now());
DELETE FROM _widgets WHERE wd_id = 'image2';
INSERT INTO _widgets
(wd_id,   wd_name, wd_version, wd_params, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_read_scripts, wd_read_css, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('image2', '画像2',  '1.0.0',    '',        'Naoki Hirata', 'Magic3.org', 'GPL',      10, '様々な画像を単一で表示。',               false,           false,       true,         true,        true,         false,                                true,                true,              0, 1,              1, now(),         now());
DELETE FROM _widgets WHERE wd_id = 'pretty_photo';
INSERT INTO _widgets
(wd_id,   wd_name, wd_version, wd_params, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_add_script_lib, wd_add_script_lib_a, wd_read_scripts, wd_read_css, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('pretty_photo', 'プリティフォト',  '1.0.0',    '',        'Naoki Hirata', 'Magic3.org', 'GPL',      10, 'サムネール表示した画像を拡大します。', '', 'jquery.tablednd',               true,           true,       true,         true,        true,         true,                                true,                true,              0,  3,             1, now(),         now());
DELETE FROM _widgets WHERE wd_id = 'css_add';
INSERT INTO _widgets
(wd_id,   wd_name, wd_version, wd_params, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_read_scripts, wd_read_css, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('css_add', 'CSS追加',  '1.0.0',    '',        'Naoki Hirata', 'Magic3.org', 'GPL',      10, 'テンプレートのCSSに加えてCSS定義を追加するためのウィジェットです。画面上には何も表示されません。',               false,           false,       true,         true,        true,         false,                                true,                true,              0,  3,             1, now(),         now());
DELETE FROM _widgets WHERE wd_id = 'effect_nicejforms';
INSERT INTO _widgets
(wd_id,   wd_name, wd_version, wd_params, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_add_script_lib, wd_add_script_lib_a, wd_read_scripts, wd_read_css, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('effect_nicejforms', 'effect_nicejforms',  '1.0.0',    '',        'Naoki Hirata', 'Magic3.org', 'GPL',      10, 'フォーム部品を装飾するウィジェット。', 'jquery', 'jquery',              true,           false,       true,         true,        true,         false,                                true,                true,              0, 1,              1, now(),         now());
DELETE FROM _widgets WHERE wd_id = 'contactus_modal';
INSERT INTO _widgets
(wd_id,   wd_name, wd_version, wd_params, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_add_script_lib, wd_add_script_lib_a, wd_read_scripts, wd_read_css, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('contactus_modal', 'モーダル型お問い合わせ',  '1.0.0',    '',        'Naoki Hirata', 'Magic3.org', 'GPL',      10, 'モーダルで起動するお問い合わせウィジェット。', 'jquery.simplemodal', 'jquery.simplemodal',              true,           true,       true,         true,        true,         true,                                true,                true, 0,             0,               0, now(),         now());
DELETE FROM _widgets WHERE wd_id = 'contactus_custom';
INSERT INTO _widgets
(wd_id,              wd_name,                          wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,                             wd_read_scripts, wd_read_css,wd_add_script_lib, wd_add_script_lib_a, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('contactus_custom', 'カスタムお問い合わせメール送信', '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'カスタマイズ可能なお問い合わせメール送信', false,           false,     '', 'jquery.tablednd', true,         false,               true,                true, 0,          0, now(),         now());
DELETE FROM _widgets WHERE wd_id = 'print';
INSERT INTO _widgets
(wd_id,   wd_name, wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,                                     wd_read_scripts, wd_read_css, wd_add_script_lib, wd_add_script_lib_a, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('print', '印刷',  '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'あらかじめ印刷部分を設定した部分印刷を行います。', true,            false,       'jquery',                       '',                  true,         false,               true,                true, 1,          1, now(),         now());
DELETE FROM _widgets WHERE wd_id = 'reg_user';
INSERT INTO _widgets
(wd_id,      wd_name,          wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,           wd_add_script_lib, wd_has_admin, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('reg_user', '汎用ユーザ登録', '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                '汎用的なユーザ登録機能', 'md5', true,         true,           0, 0,              0, now(), now());
DELETE FROM _widgets WHERE wd_id = 'ec_disp';
INSERT INTO _widgets
(wd_id,     wd_name,                wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,                                      wd_has_admin, wd_initialized, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('ec_disp', 'Eコマース - 商品表示', '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'Eコマースの商品表示機能。商品の説明や一覧を表示。', true,         true, 1,  2, now(), now());
DELETE FROM _widgets WHERE wd_id = 'ec_main';
INSERT INTO _widgets
(wd_id,     wd_name,              wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,                                                        wd_has_admin, wd_initialized, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('ec_main', 'Eコマース - メイン', '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'Eコマースのメインプログラム。会員登録処理や商品購入処理などを行う。', true,         true,   0, 2, now(), now());
DELETE FROM _widgets WHERE wd_id = 'fontsize';
INSERT INTO _widgets
(wd_id,   wd_name, wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,                                     wd_read_scripts, wd_read_css, wd_add_script_lib, wd_add_script_lib_a, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('fontsize', 'フォントサイズ',  '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'フォントサイズを拡大縮小して保持します', true,            true,       'jquery.cookie',                       '',                  true,         false,               false,                true, 1,          1, now(),         now());

-- *** システム標準テーブル ***
