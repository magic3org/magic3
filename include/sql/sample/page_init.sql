-- *
-- * データ登録スクリプト「ページ初期化」
-- *
-- * PHP versions 5
-- *
-- * LICENSE: This source file is licensed under the terms of the GNU General Public License.
-- *
-- * @package    Magic3 Framework
-- * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
-- * @copyright  Copyright 2006-2021 Magic3 Project.
-- * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
-- * @version    SVN: $Id$
-- * @link       http://www.magic3.org
-- *
-- [ページ初期化]
-- ページを初期状態に戻す

-- システム設定
UPDATE _system_config SET sc_value = 'bootstrap4_custom' WHERE sc_id = 'default_template';
UPDATE _system_config SET sc_value = '0' WHERE sc_id = 'site_menu_hier';

-- 管理画面ページデータ(デフォルトを変更)
UPDATE _page_id SET pg_default_sub_id = 'content' WHERE pg_id = 'index' AND pg_type = 0;
-- スマートフォン,携帯のアクセスポイントを隠す
UPDATE _page_id SET pg_active = false WHERE pg_id = 's_index' AND pg_type = 0;
UPDATE _page_id SET pg_active = false WHERE pg_id = 'm_index' AND pg_type = 0;

-- ページIDリセット
DELETE FROM _page_id WHERE pg_type = 1 AND pg_priority < 100;
INSERT INTO _page_id 
(pg_id,          pg_type,      pg_name,                            pg_description,                       pg_priority, pg_active, pg_visible, pg_editable) VALUES
('front',        1,            'トップ画面',                       'トップ画面用',                       0,           false,      true,       true),
('content',      1,            'コンテンツ',                       'コンテンツ画面用',                   1,           true,       true,       false),
('shop',         1,            'ECショップ',                       'ECショップ画面用',                   2,           false,      true,       true),
('shop_safe',    1,            'ECショップ(セキュリティ保護)',     'ECショップ(セキュリティ保護)画面用', 3,           false,      true,       true),
('bbs',          1,            '掲示板',                           '掲示板画面用',                       4,           false,      true,       true),
('blog',         1,            'ブログ',                           'ブログ画面用',                       5,           false,      true,       true),
('wiki',         1,            'Wiki',                             'Wiki画面用',                         6,           false,      true,       true),
('calendar',     1,            'カレンダー',                       'カレンダー画面用',                   7,           false,      true,       true),
('event',        1,            'イベント情報',                     'イベント情報画面用',                 8,           false,      true,       true),
('photo',        1,            'フォトギャラリー',                 'フォトギャラリー画面用',             9,           false,      true,       true),
('contact',      1,            'お問い合わせ',                     'お問い合わせ画面用',                 10,          false,      true,       true),
('contact2',     1,            'お問い合わせ2',                    'お問い合わせ画面用',                 11,          false,      true,       true),
('reguser',      1,            'ユーザ登録',                       'ユーザ登録画面用',                   12,          false,      true,       true),
('reserve',      1,            '予約',                             '予約画面用',                         19,          false,      true,       true),
('member',       1,            '会員',                             '会員画面用',                         20,          false,      true,       true),
('search',       1,            '検索',                             '検索画面用',                         21,          false,      true,       true);

-- ページの固定テンプレートをリセット
UPDATE _page_info SET pn_template_id = '' WHERE pn_id = 'index' AND pn_deleted = false;

-- 管理画面メニューデータ
DELETE FROM _nav_item WHERE ni_nav_id = 'admin_menu';
DELETE FROM _nav_item WHERE ni_nav_id = 'admin_menu.en';
INSERT INTO _nav_item
(ni_id, ni_parent_id, ni_index, ni_nav_id,       ni_task_id,           ni_view_control, ni_visible, ni_param, ni_hide_option,   ni_name,                ni_help_title,          ni_help_body) VALUES
(100,   0,            0,        'admin_menu',    '_page',              0,               true,       '',       'site_operation', '画面管理',             '画面管理',             'Webサイトのデザインや機能を管理します。'),
(101,   100,          0,        'admin_menu',    'pagedef',            0,               true,       '',       '',               'ページ編集',         'ページ編集',         'ウィジェットを配置してページを作成します。'),
(102,   100,          1,        'admin_menu',    'pagedef_smartphone', 0,               false,      '',       '',               'ページ編集(スマートフォン)', 'ページ編集(スマートフォン)',       'ウィジェットを配置してスマートフォン用のページを作成します。'),
(103,   100,          2,        'admin_menu',    '_103',               3,               true,       '',       '',               'セパレータ',                 '',                     ''),
(104,   100,          3,        'admin_menu',    'widgetlist',         0,               true,       '',       '',               'ウィジェット管理',     'ウィジェット管理',     'ウィジェットの管理を行います。'),
(105,   100,          4,        'admin_menu',    'templist',           0,               true,       '',       '',               'テンプレート管理',     'テンプレート管理',     'テンプレートの管理を行います。'),
(106,   100,          5,        'admin_menu',    'smenudef',           0,               true,       '',       '',               'メニュー管理', 'メニュー管理', 'メニュー定義を管理します。'),
(199,   0,            1,        'admin_menu',    '_199',               1,               true,       '',       '',               '改行',                 '',                     ''),
(200,   0,            2,        'admin_menu',    '_login',             0,               true,       '',       '',               'システム運用',         '',                     ''),
(201,   200,          0,        'admin_menu',    'userlist',           0,               true,       '',       '',               'ユーザ管理',           'ユーザ管理',           'ログイン可能なユーザを管理します。'),
(202,   200,          1,        'admin_menu',    'accesslog',          0,               true,       '',       '',               '運用状況', '運用状況', 'サイトの運用状況を表示します。'),
(299,   0,            3,        'admin_menu',    '_299',               1,               true,       '',       '',               '改行',                 '',                     ''),
(300,   0,            4,        'admin_menu',    '_config',            0,               true,       '',       'site_operation', 'システム管理',         '',                     ''),
(301,   300,          0,        'admin_menu',    'configsite',         0,               true,       '',       '',               '基本情報',             '基本情報',             'サイト運営に必要な情報を設定します。'),
(302,   300,          1,        'admin_menu',    'configsys',          0,               true,       '',       '',               'システム情報',         'システム情報',         'システム全体の設定、運用状況を管理します。'),
(303,   300,          2,        'admin_menu',    'mainte',             0,               true,       '',       '',               'メンテナンス', 'メンテナンス', 'ファイルやDBなどのメンテナンスを行います。'),
(10100, 0,            0,        'admin_menu.en', '_page',              0,               true,       '',       'site_operation', 'Page Administration',             'Page Administration',             'Administrate web site pages with design and functions.'),
(10101, 10100,        0,        'admin_menu.en', 'pagedef',            0,               true,       '',       '',               'Edit Page',         'Edit Page',         'Edit page by posisioning widgets.'),
(10102, 10100,        1,        'admin_menu.en', 'pagedef_smartphone', 0,               false,      '',       '',               'Edit Page(Smartphone)', 'Edit Page(Smartphone)',       'Edit page for Smartphone by posisioning widgets.'),
(10103, 10100,        2,        'admin_menu.en', '_10103',             3,               true,       '',       '',               'Separator',                 '',                     ''),
(10104, 10100,        3,        'admin_menu.en', 'widgetlist',         0,               true,       '',       '',               'Widget Administration',     'Widget Administration',     'Administrate widgets with widget config window.'),
(10105, 10100,        4,        'admin_menu.en', 'templist',           0,               true,       '',       '',               'Template Administration',     'Template Administration',     'Administrate templates.'),
(10106, 10100,        5,        'admin_menu.en', 'smenudef',           0,               true,       '',       '',               'Menu Administration', 'Menu Administration', 'Administrate menu definition.'),
(10199, 0,            1,        'admin_menu.en', '_10199',             1,               true,       '',       '',               'Return',                 '',                     ''),
(10200, 0,            2,        'admin_menu.en', '_login',             0,               true,       '',       '',               'System Operation',         '',                     ''),
(10201, 10200,        0,        'admin_menu.en', 'userlist',           0,               true,       '',       '',               'User List',           'User List',           'Administrate user to login.'),
(10202, 10200,        1,        'admin_menu.en', 'accesslog',          0,               true,       '',       '',               'Site Conditions', 'Site Conditions', 'Operation log and access analytics on site.'),
(10299, 0,            3,        'admin_menu.en', '_10299',             1,               true,       '',       '',               'Return',                 '',                     ''),
(10300, 0,            4,        'admin_menu.en', '_config',            0,               true,       '',       'site_operation', 'System Administration',         '',                     ''),
(10301, 10300,        0,        'admin_menu.en', 'configsite',         0,               true,       '',       '',               'Site Information',             'Site Information',             'Configure site information.'),
(10302, 10300,        1,        'admin_menu.en', 'configsys',          0,               true,       '',       '',               'System Information',         'System Information',         'Configure sytem information.'),
(10303, 10300,        2,        'admin_menu.en', 'mainte',             0,               true,       '',       '',               'System Maintenance', 'System Maintenance', 'Maintenance about file system and database.');

-- ウィジェットカテゴリマスターリセット
DELETE FROM _widget_category;
INSERT INTO _widget_category
(wt_id,         wt_parent_id,  wt_name,                wt_sort_order, wt_visible) VALUES
('',            '',            'その他',               100,           true),
('content',     'content',     '汎用コンテンツ',       1,             true),
('blog',        'blog',        'ブログ',               2,             true),
('bbs',         'bbs',         'BBS',                  3,             false),
('commerce',    'commerce',    'Eコマース',            4,             false),
('photo',       'photo',       'フォトギャラリー',     5,             false),
('event',       'event',       'イベント情報',         6,             false),
('wiki',        'wiki',        'Wiki',                 7,             false),
('member',      'member',      '会員',                 9,             false),
('subcontent',  'subcontent',  '補助コンテンツ',       20,            true),
('searchform/', 'searchform/', '検索・お問い合わせ',   21,            true),
('search',      'searchform/', '検索',                 22,            true),
('form',        'searchform/', 'お問い合わせ',         23,            true),
('menu',        'menu',        'メニュー',             24,            true),
('image',       'image',       '画像',                 25,            true),
('design',      'design',      'デザイン',             26,            true),
('meta',        '',            'メタ機能',             27,            true),
('analytics',   '',            'サイト解析',           28,            true),
('admin',       'admin',       '管理画面用',           50,            true),
('test',        'test',        'テスト用',             200,           true);

-- 画面定義削除
DELETE FROM _page_def WHERE pd_id = 'index';

-- メニュー定義削除
DELETE FROM _menu_def;