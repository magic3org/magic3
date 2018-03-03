-- *
-- * バージョンアップ用スクリプト
-- *
-- * PHP versions 5
-- *
-- * LICENSE: This source file is licensed under the terms of the GNU General Public License.
-- *
-- * @package    Magic3 Framework
-- * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
-- * @copyright  Copyright 2006-2018 Magic3 Project.
-- * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
-- * @version    SVN: $Id$
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
-- 管理画面メニューデータ
DELETE FROM _nav_item WHERE ni_nav_id = 'admin_menu';
DELETE FROM _nav_item WHERE ni_nav_id = 'admin_menu.en';
INSERT INTO _nav_item
(ni_id, ni_parent_id, ni_index, ni_nav_id,       ni_task_id,           ni_view_control, ni_visible, ni_param, ni_hide_option,   ni_name,                ni_help_title,          ni_help_body) VALUES
(100,   0,            0,        'admin_menu',    '_page',              0,               true,       '',       'site_operation', '画面管理',             '画面管理',             'Webサイトのデザインや機能を管理します。'),
(101,   100,          0,        'admin_menu',    'pagedef',            0,               true,       '',       '',               'PC画面',         'PC画面編集',         'PC用Webサイトの画面を作成します。'),
(102,   100,          1,        'admin_menu',    'pagedef_smartphone', 0,               false,      '',       '',               'スマートフォン画面', 'スマートフォン画面編集',       'スマートフォン用Webサイトの画面を作成します。'),
(103,   100,          2,        'admin_menu',    'pagedef_mobile',     0,               false,      '',       '',               '携帯画面',       '携帯画面編集',       '携帯用Webサイトの画面を作成します。'),
(104,   100,          3,        'admin_menu',    '_104',               3,               true,       '',       '',               'セパレータ',                 '',                     ''),
(105,   100,          4,        'admin_menu',    'widgetlist',         0,               true,       '',       '',               'ウィジェット管理',     'ウィジェット管理',     'ウィジェットの管理を行います。'),
(106,   100,          5,        'admin_menu',    'templist',           0,               true,       '',       '',               'テンプレート管理',     'テンプレート管理',     'テンプレートの管理を行います。'),
(107,   100,          6,        'admin_menu',    'smenudef',           0,               true,       '',       '',               'メニュー管理', 'メニュー管理', 'メニュー定義を管理します。'),
(199,   0,            1,        'admin_menu',    '_199',               1,               true,       '',       '',               '改行',                 '',                     ''),
(200,   0,            2,        'admin_menu',    '_login',             0,               true,       '',       '',               'システム運用',         '',                     ''),
(201,   200,          0,        'admin_menu',    'userlist',           0,               true,       '',       '',               'ユーザ管理',           'ユーザ管理',           'ログイン可能なユーザを管理します。'),
(202,   200,          1,        'admin_menu',    'accesslog',          0,               true,       '',       '',               '運用状況', '運用状況', 'サイトの運用状況を表示します。'),
(299,   0,            3,        'admin_menu',    '_299',               1,               true,       '',       '',               '改行',                 '',                     ''),
(300,   0,            4,        'admin_menu',    '_config',            0,               true,       '',       'site_operation', 'システム管理',         '',                     ''),
(301,   300,          0,        'admin_menu',    'configsite',         0,               true,       '',       '',               '基本情報',             '基本情報',             'サイト運営に必要な情報を設定します。'),
(302,   300,          1,        'admin_menu',    'configsys',          0,               true,       '',       '',               'システム情報',         'システム情報',         'システム全体の設定、運用状況を管理します。'),
(303,   300,          2,        'admin_menu',    'mainte',             0,               true,       '',       '',               'メンテナンス', 'メンテナンス', 'ファイルやDBなどのメンテナンスを行います。'),
(10100, 0,            0,        'admin_menu.en', '_page',              0,               true,       '',       'site_operation', 'Edit Page',             'Edit Page',             'Edit page for design and function.'),
(10101, 10100,        0,        'admin_menu.en', 'pagedef',            0,               true,       '',       '',               'PC Page',         'PC Page',         'Edit page for PC.'),
(10102, 10100,        1,        'admin_menu.en', 'pagedef_smartphone', 0,               false,      '',       '',               'Smartphone Page', 'Smartphone Page',       'Edit page for Smartphone.'),
(10103, 10100,        2,        'admin_menu.en', 'pagedef_mobile',     0,               false,      '',       '',               'Mobile Page',       'Mobile Page',       'Edit page for Mobile.'),
(10104, 10100,        3,        'admin_menu.en', '_10104',             3,               true,       '',       '',               'Separator',                 '',                     ''),
(10105, 10100,        4,        'admin_menu.en', 'widgetlist',         0,               true,       '',       '',               'Widget Administration',     'Widget Administration',     'Administrate widgets with widget config window.'),
(10106, 10100,        5,        'admin_menu.en', 'templist',           0,               true,       '',       '',               'Template Administration',     'Template Administration',     'Administrate templates.'),
(10107, 10100,        6,        'admin_menu.en', 'smenudef',           0,               true,       '',       '',               'Menu Administration', 'Menu Administration', 'Administrate menu definition.'),
(10199, 0,            1,        'admin_menu.en', '_10199',             1,               true,       '',       '',               'Return',                 '',                     ''),
(10200, 0,            2,        'admin_menu.en', '_login',             0,               true,       '',       '',               'System Operation',         '',                     ''),
(10201, 10200,        0,        'admin_menu.en', 'userlist',           0,               true,       '',       '',               'User List',           'User List',           'Administrate user to login.'),
(10202, 10200,        1,        'admin_menu.en', 'accesslog',          0,               true,       '',       '',               'Site Conditions', 'Site Conditions', 'Operation log and access analytics on site.'),
(10299, 0,            3,        'admin_menu.en', '_10299',             1,               true,       '',       '',               'Return',                 '',                     ''),
(10300, 0,            4,        'admin_menu.en', '_config',            0,               true,       '',       'site_operation', 'System Administration',         '',                     ''),
(10301, 10300,        0,        'admin_menu.en', 'configsite',         0,               true,       '',       '',               'Site Information',             'Site Information',             'Configure site information.'),
(10302, 10300,        1,        'admin_menu.en', 'configsys',          0,               true,       '',       '',               'System Information',         'System Information',         'Configure sytem information.'),
(10303, 10300,        2,        'admin_menu.en', 'mainte',             0,               true,       '',       '',               'System Maintenance', 'System Maintenance', 'Maintenance about file system and database.');

-- *** システム標準テーブル ***
