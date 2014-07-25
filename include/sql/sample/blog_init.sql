-- *
-- * データ登録スクリプト「ブログサイト初期化」
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
-- [ブログサイト初期化]
-- ブログ主軸型サイト。
-- 機能は、ブログ、汎用コンテンツ、バナー管理。
-- 初期インストールデータは必要最小限のみ

-- システム設定
UPDATE _system_config SET sc_value = 'art41_sample2' WHERE sc_id = 'default_template';

-- 変換文字列
DELETE FROM _key_value;
INSERT INTO _key_value
(kv_id,               kv_name,       kv_value, kv_group_id) VALUES
('CUSTOM_KEY_001',      '会社名',      '', 'user'),
('CUSTOM_KEY_002',      '所在地',      '', 'user'),
('CUSTOM_KEY_003',      '設立',      '', 'user'),
('CUSTOM_KEY_004',      '代表者',      '', 'user'),
('CUSTOM_KEY_005',      '事業内容',      '', 'user'),
('CUSTOM_KEY_006',      '主要取引銀行',      '', 'user'),
('CUSTOM_KEY_007',      '主要取引先',      '', 'user'),
('CUSTOM_KEY_008',      'ショップ名',      '', 'user'),
('CUSTOM_KEY_009',      'ショップオーナー名',      '', 'user'),
('CUSTOM_KEY_010',      'ショップ住所',      '', 'user'),
('CUSTOM_KEY_011',      'ショップ電話番号',      '', 'user'),
('CUSTOM_KEY_012',      'ショップメールアドレス',      '', 'user');

-- 管理画面ページデータ(デフォルトを変更)
UPDATE _page_id SET pg_default_sub_id = 'blog' WHERE pg_id = 'index' AND pg_type = 0;

-- 管理画面メニューデータ
DELETE FROM _nav_item;
INSERT INTO _nav_item
(ni_id, ni_parent_id, ni_index, ni_nav_id,    ni_task_id,        ni_view_control, ni_param, ni_name,                ni_help_title,          ni_help_body) VALUES
(100,   0,            0,        'admin_menu', '_page',           0,               '',       '画面管理',             '画面管理',             'Webサイトのデザインや機能を管理します。'),
(101,   100,          0,        'admin_menu', 'pagedef',         0,               '',       'PC画面',         'PC画面編集',         'PC用Webサイトの画面を作成します。'),
(102,   100,          1,        'admin_menu', 'pagedef_smartphone',  0,           '',       'スマートフォン画面', 'スマートフォン画面編集',       'スマートフォン用Webサイトの画面を作成します。'),
(103,   100,          2,        'admin_menu', 'pagedef_mobile',  0,               '',       '携帯画面',       '携帯画面編集',       '携帯用Webサイトの画面を作成します。'),
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
(10100,   0,            0,        'admin_menu.en', '_page',           0,               '',       'Edit Page',             'Edit Page',             'Edit page for design and function.'),
(10101,   10100,          0,        'admin_menu.en', 'pagedef',         0,               '',       'PC Page',         'PC Page',         'Edit page for PC.'),
(10102,   10100,          1,        'admin_menu.en', 'pagedef_smartphone',  0,           '',       'Smartphone Page', 'Smartphone Page',       'Edit page for Smartphone.'),
(10103,   10100,          2,        'admin_menu.en', 'pagedef_mobile',  0,               '',       'Mobile Page',       'Mobile Page',       'Edit page for Mobile.'),
(10104,   10100,          3,        'admin_menu.en', '_10104',            3,               '',       'Separator',                 '',                     ''),
(10105,   10100,          4,        'admin_menu.en', 'widgetlist',      0,               '',       'Widget Administration',     'Widget Administration',     'Administrate widgets with widget config window.'),
(10106,   10100,          5,        'admin_menu.en', 'templist',        0,               '',       'Template Administration',     'Template Administration',     'Administrate templates.'),
(10107,   10100,          6,        'admin_menu.en', 'smenudef',        0,               '',       'Menu Administration', 'Menu Administration', 'Administrate menu definition.'),
(10199,   0,            1,        'admin_menu.en', '_10199',            1,               '',       'Return',                 '',                     ''),
(10200,   0,            2,        'admin_menu.en', '_login',          0,               '',       'System Operation',         '',                     ''),
(10201,   10200,          0,        'admin_menu.en', 'userlist',        0,               '',       'User List',           'User List',           'Administrate user to login.'),
(10202,   10200,          1,        'admin_menu.en', 'accesslog',     0,               '',       'Site Conditions', 'Site Conditions', 'Operation log and access analytics on site.'),
(10299,   0,            3,        'admin_menu.en', '_10299',            1,               '',       'Return',                 '',                     ''),
(10300,   0,            4,        'admin_menu.en', '_config',         0,               '',       'System Administration',         '',                     ''),
(10301,   10300,          0,        'admin_menu.en', 'configsite',      0,               '',       'Site Information',             'Site Information',             'Configure site information.'),
(10302,   10300,          1,        'admin_menu.en', 'configsys',       0,               '',       'System Information',         'System Information',         'Configure sytem information.'),
(10303,   10300,          2,        'admin_menu.en', 'mainte',          0,               '',       'System Maintenance', 'System Maintenance', 'Maintenance about file system and database.');

-- 画面定義
DELETE FROM _page_def WHERE pd_id = 'index';
INSERT INTO _page_def
(pd_id,   pd_sub_id, pd_position_id, pd_index, pd_widget_id,         pd_config_id, pd_config_name,       pd_menu_id,  pd_title_visible, pd_update_dt) VALUES
('index', '',        'user3',         2,        'default_menu',       1,            'メインメニュー設定', 'main_menu', true,             now()),
('index', '',        'left',         4,        'templateChanger',    0,            '',                   '',          true,             now()),
('index', '',        'right',        5,        'blog_category_menu', 0,            '',                   '',          true,             now()),
('index', '',        'right',        7,        'blog_archive_menu',  0,            '',                   '',          true,             now()),
('index', '',        'right',        9,        'default_login_box',  0,            '',                   '',          true,             now()),
('index', '',        'main',         3,        'banner3',            3,            '',                   '',          false,            now()),
('index', 'content', 'main',         6,        'default_content',    0,            '',                   '',          false,            now()),
('index', 'blog',    'main',         5,        'blog_main',          0,            '',                   '',          false,            now()),
('index', 'blog',    'left',         7,        'blog_new_box',       0,            '',                   '',          true,             now()),
('index', 'blog',    'left',         9,        'blog_calendar_box',  0,            '',                   '',          true,             now()),
('index', 'blog',    'left',         11,       'blog_search_box',    0,            '',                   '',          true,             now()),
('index', 'search',  'main',         5,        'custom_search',      1,            '',                   '',          false,            now()),
('index', 'contact', 'main',         5,        'contactus',          0,            '',                   '',          false,            now());

-- 新メニュー対応
TRUNCATE TABLE _menu_def;
INSERT INTO _menu_def
(md_id, md_index, md_menu_id,  md_name,        md_link_url,                                         md_update_dt) VALUES
(1,     1,        'main_menu', 'ホーム',       '[#M3_ROOT_URL#]/',                                   now()),
(2,     2,        'main_menu', '会社情報',     '[#M3_ROOT_URL#]/index.php?contentid=1', now()),
(3,     3,        'main_menu', 'お問い合わせ', '[#M3_ROOT_URL#]/index.php?sub=contact',             now());

-- ウィジェットパラメータ
DELETE FROM _widget_param WHERE wp_id = 'default_menu';
INSERT INTO _widget_param
(wp_id,          wp_config_id, wp_param,                                                                                                wp_create_dt) VALUES 
('default_menu', 1,            'O:\8:"stdClass":3:{s:6:"menuId";s:9:"main_menu";s:4:"name";s:27:"メインメニュー設定";s:9:"limitUser";i:0;}', now());
DELETE FROM _widget_param WHERE wp_id = 'custom_search';
INSERT INTO _widget_param
(wp_id,          wp_config_id, wp_param,                                                                                                wp_create_dt) VALUES 
('custom_search', 1,            'O:8:"stdClass":15:{s:4:"name";s:16:"名称未設定1";s:11:"resultCount";s:2:"20";s:14:"searchTemplate";s:239:"<input id="custom_search_1_text" maxlength="40" size="10" type="text" /><input class="button" id="custom_search_1_button" type="button" value="検索" /><input class="button" id="custom_search_1_reset" type="button" value="リセット" />";s:12:"searchTextId";s:20:"custom_search_1_text";s:14:"searchButtonId";s:22:"custom_search_1_button";s:13:"searchResetId";s:21:"custom_search_1_reset";s:15:"isTargetContent";i:1;s:12:"isTargetUser";i:1;s:12:"isTargetBlog";i:1;s:9:"fieldInfo";a:0:{}s:15:"isTargetProduct";i:0;s:13:"isTargetEvent";i:0;s:11:"isTargetBbs";i:0;s:13:"isTargetPhoto";i:0;s:12:"isTargetWiki";i:0;}', now());

-- ブログ
TRUNCATE TABLE blog_entry;
TRUNCATE TABLE blog_category;
TRUNCATE TABLE blog_entry_with_category;

-- バナー定義
TRUNCATE TABLE bn_def;
TRUNCATE TABLE bn_item;

-- コンテンツ
TRUNCATE TABLE content;
INSERT INTO content (cn_type, cn_id, cn_language_id, cn_name,              cn_description,         cn_html,                        cn_default, cn_key, cn_create_user_id, cn_create_dt) VALUES 
('', 1,     'ja',           '会社情報',   '会社情報', '<div class="ec_common">\r\n<table>\r\n    <tbody>\r\n        <tr>\r\n            <th>社　名</th>\r\n            <td>\r\n            <p>&nbsp;</p>\r\n            <p>[#CUSTOM_KEY_001#]</p>\r\n            <p>&nbsp;</p>\r\n            </td>\r\n        </tr>\r\n        <tr>\r\n            <th>所在地</th>\r\n            <td>\r\n            <p>&nbsp;</p>\r\n            <p>[#CUSTOM_KEY_002#]</p>\r\n            <p>&nbsp;</p>\r\n            </td>\r\n        </tr>\r\n        <tr>\r\n            <th>設　立</th>\r\n            <td>\r\n            <p>&nbsp;</p>\r\n            <p>[#CUSTOM_KEY_003#]</p>\r\n            <p>&nbsp;</p>\r\n            </td>\r\n        </tr>\r\n        <tr>\r\n            <th>代表者</th>\r\n            <td>\r\n            <p>&nbsp;</p>\r\n            <p>[#CUSTOM_KEY_004#]</p>\r\n            <p>&nbsp;</p>\r\n            </td>\r\n        </tr>\r\n        <tr>\r\n            <th>事業内容</th>\r\n            <td>\r\n            <p>&nbsp;</p>\r\n            <p>[#CUSTOM_KEY_005#]●商品Ａの製造<br />\r\n            ●商品Ｂの卸売<br />\r\n            ●商品Ｃの販売</p>\r\n            <p>&nbsp;</p>\r\n            </td>\r\n        </tr>\r\n        <tr>\r\n            <th>主要取引銀行</th>\r\n            <td>\r\n            <p>&nbsp;</p>\r\n            <p>[#CUSTOM_KEY_006#]</p>\r\n            <p>&nbsp;</p>\r\n            </td>\r\n        </tr>\r\n        <tr>\r\n            <th>主要取引先</th>\r\n            <td>\r\n            <p>&nbsp;</p>\r\n            <p>[#CUSTOM_KEY_007#]■ＸＸＸ株式会社<br />\r\n            ■ＹＹＹ株式会社<br />\r\n            ■株式会社　ＺＺＺ</p>\r\n            <p>&nbsp;</p>\r\n            <p>&nbsp;</p>\r\n            </td>\r\n        </tr>\r\n    </tbody>\r\n</table>\r\n</div>\r\n',              false, '',                0, now());
