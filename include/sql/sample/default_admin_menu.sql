-- *
-- * データ登録スクリプト「デフォルト管理画面」
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
-- [デフォルト管理画面]
-- 管理画面をデフォルトに戻す。

-- ページ定義マスター
DELETE FROM _page_def WHERE pd_id = 'admin_index';
INSERT INTO _page_def
(pd_id,         pd_sub_id,      pd_position_id, pd_index, pd_widget_id,   pd_config_id, pd_visible, pd_editable, pd_title_visible) VALUES
('admin_index', '',             'top',          1,        'admin_menu4',  0,            true,       false, false),
('admin_index', 'front',        'top',          2,        'admin/message',  0,            true,       false, false),
('admin_index', 'front',        'main',         1,        'admin_main',   0,            true,       false, false),
('admin_index', 'front',        'main',         2,        'admin/analytics',   0,            true,       true, false),
('admin_index', 'front',        'main',         3,        'admin/opelog',   0,            true,       true, false),
('admin_index', 'front',        'left',         1,        'admin/loginuser',   0,            true,       true, true),
('admin_index', 'content',      'main',         1,        'admin_main',   0,            true,       false, false);

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
(10100,   0,            0,        'admin_menu.en', '_page',           0,               '',       'Edit Page',             'Edit Page',             'Edit page for design and function.'),
(10101,   10100,          0,        'admin_menu.en', 'pagedef',         0,               '',       'PC Page',         'PC Page',         'Edit page for PC.'),
(10102,   10100,          1,        'admin_menu.en', 'pagedef_mobile',  0,               '',       'Mobile Page',       'Mobile Page',       'Edit page for Mobile.'),
(10103,   10100,          2,        'admin_menu.en', 'pagedef_smartphone',  0,           '',       'Smartphone Page', 'Smartphone Page',       'Edit page for Smartphone.'),
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

-- テンプレート情報
DELETE FROM _templates WHERE tm_id = '_admin4';
INSERT INTO _templates
(tm_id,                           tm_name,                         tm_type, tm_device_type, tm_mobile, tm_use_bootstrap, tm_available, tm_clean_type, tm_create_dt) VALUES
('_admin4',                       '_admin4',                       2,       0,              false,     true,             false,        0,             now());

-- システム設定マスター(管理画面用デフォルトテンプレートを変更)
UPDATE _system_config SET sc_value = '_admin4' WHERE sc_id = 'admin_default_template';
