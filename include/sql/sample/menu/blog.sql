-- *
-- * データ登録スクリプト「ブログ標準管理メニュー」
-- *
-- * PHP versions 5
-- *
-- * LICENSE: This source file is licensed under the terms of the GNU General Public License.
-- *
-- * @package    Magic3 Framework
-- * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
-- * @copyright  Copyright 2006-2012 Magic3 Project.
-- * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
-- * @version    SVN: $Id: blog.sql 5857 2013-03-24 23:24:31Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- [ブログ標準管理メニュー]
-- ブログ向けに構成した標準管理メニュー

-- 管理画面メニューデータ
DELETE FROM _nav_item;
INSERT INTO _nav_item
(ni_id, ni_parent_id, ni_index, ni_nav_id,    ni_task_id,                     ni_group_id, ni_view_control, ni_param, ni_name,    ni_help_title, ni_help_body, ni_visible) VALUES
(100,   0,            0,        'admin_menu', '_page',                        '',          0,               '',       '画面管理', '画面管理', 'Webサイトのデザインや機能を管理します。', true),
(101,   100,          0,        'admin_menu', 'pagedef',                      '',          0,               '',       'PC用画面', 'PC用画面編集', 'PC用Webサイトの画面を作成します。', true),
(102,   100,          1,        'admin_menu', 'pagedef_smartphone',           '',          0,               '',       'スマートフォン用画面', 'スマートフォン用画面編集',       'スマートフォン用Webサイトの画面を作成します。', true),
(103,   100,          2,        'admin_menu', 'pagedef_mobile',               '',          0,               '',       '携帯用画面', '携帯用画面編集', '携帯用Webサイトの画面を作成します。', true),
(104,   100,          3,        'admin_menu', 'widgetlist',                   '',          0,               '',       'ウィジェット管理', 'ウィジェット管理', 'ウィジェットの管理を行います。', true),
(105,   100,          4,        'admin_menu', 'templist',                     '',          0,               '',       'テンプレート管理', 'テンプレート管理', 'テンプレートの管理を行います。', true),
(106,   100,          5,        'admin_menu', 'smenudef',                      '',          0,               '',       'メニュー管理', 'メニュー管理', 'メニュー定義を管理します。', true),
(200,   0,            1,        'admin_menu', '_login',                       '',          0,               '',       'システム運用', '', '', true),
(201,   200,          0,        'admin_menu', 'userlist',                     '',          0,               '',       'ユーザ一覧',   'ユーザ一覧', 'ログイン可能なユーザを管理します。', true),
(202,   200,          1,        'admin_menu', 'accesslog',                  '',          0,               '',       '運用状況', '運用状況', 'サイトの運用状況を表示します。', true),
(300,   0,            2,        'admin_menu', '_config',                      '',          0,               '',       'システム管理', '', '', true),
(301,   300,          0,        'admin_menu', 'configsite',                   '',          0,               '',       '基本情報', '基本情報', 'サイト運営に必要な情報を設定します。', true),
(302,   300,          1,        'admin_menu', 'configsys',                    '',          0,               '',       'システム情報', 'システム情報', 'システム全体の設定、運用状況を管理します。', true),
(303,   300,          2,        'admin_menu', 'resbrowse',                    '',          0,               '',       'リソースブラウズ', 'リソースブラウズ', 'リソースファイルを管理します。', true),
(304,   300,          3,        'admin_menu', 'master',                       '',          0,               '',       'システムマスター管理', 'システムマスター管理', 'システムに関するマスターテーブルの管理を行います。', true),
(305,   300,          4,        'admin_menu', 'initsystem',                   '',          0,               '',       'DBメンテナンス', 'DBメンテナンス', 'データの初期化などDBのメンテナンスを行います。', true),
(399,   0,            3,        'admin_menu', '_399',                         '',          1,               '',       '改行', '', '', true),
(500,   0,            4,        'admin_menu', '_content',                      '',          0,               '',       'コンテンツ管理', 'コンテンツ管理', '各種コンテンツを管理します。', true),
(501,   500,          0,        'admin_menu', 'configwidget_default_content', '',          0,               '',       '汎用コンテンツ', '汎用コンテンツ', '汎用コンテンツを管理します。(コンテンツメインウィジェット)', true),
(502,   500,          1,        'admin_menu', 'configwidget_blog_main',       '',          0,               '',       'ブログ', 'ブログ', 'ブログコンテンツを管理します。(バナーメインウィジェット)', true),
(503,   500,          2,        'admin_menu', 'configwidget_event_main',       '',          0,               '',       'イベント情報', 'イベント情報', 'イベント情報を管理します。(イベント情報メインウィジェット)', true),
(1100,  0,            11,       'admin_menu', '_others',                      '',          0,               '',       'その他', '', '', true),
(1101,  1100,         0,        'admin_menu', 'logout',                       '',          0,               '',       'ログアウト', 'ログアウト', '管理機能からログアウトします。', true),
(10100,   0,            0,        'admin_menu.en', '_page',                        '',          0,               '',       'Edit Page', 'Edit Page', 'Edit page for design and function.', true),
(10101,   10100,          0,        'admin_menu.en', 'pagedef',                      '',          0,               '',       'PC Page', 'PC Page', 'Edit page for PC.', true),
(10102,   10100,          1,        'admin_menu.en', 'pagedef_mobile',               '',          0,               '',       'Mobile Page', 'Mobile Page', 'Edit page for Mobile.', true),
(10103,   10100,          2,        'admin_menu.en', 'pagedef_smartphone',           '',          0,               '',       'Smartphone Page', 'Smartphone Page',       'Edit page for Smartphone.', true),
(10104,   10100,          3,        'admin_menu.en', 'widgetlist',                   '',          0,               '',       'Widget Administration', 'Widget Administration', 'Administrate widgets with widget config window.', true),
(10105,   10100,          4,        'admin_menu.en', 'templist',                     '',          0,               '',       'Template Administration', 'Template Administration', 'Administrate templates.', true),
(10106,   10100,          5,        'admin_menu.en', 'smenudef',                      '',          0,               '',       'Menu Administration', 'Menu Administration', 'Administrate menu definition.', true),
(10200,   0,            1,        'admin_menu.en', '_login',                       '',          0,               '',       'System Operation', '', '', true),
(10201,   10200,          0,        'admin_menu.en', 'userlist',                     '',          0,               '',       'User List',   'User List', 'Administrate user to login.', true),
(10202,   10200,          1,        'admin_menu.en', 'accesslog',                  '',          0,               '',       'Site Conditions', 'Site Conditions', 'Operation log and access analytics on site.', true),
(10300,   0,            2,        'admin_menu.en', '_config',                      '',          0,               '',       'System Administration', '', '', true),
(10301,   10300,          0,        'admin_menu.en', 'configsite',                   '',          0,               '',       'Site Information', 'Site Information', 'Configure site information.', true),
(10302,   10300,          1,        'admin_menu.en', 'configsys',                    '',          0,               '',       'System Information', 'System Information', 'Configure sytem information.', true),
(10303,   10300,          2,        'admin_menu.en', 'resbrowse',                    '',          0,               '',       'Resource Browse', 'Resource Browse', 'Administrate resource files.', true),
(10304,   10300,          3,        'admin_menu.en', 'master',                       '',          0,               '',       'System Master', 'System Master', 'Administrate system master data.', true),
(10305,   10300,          4,        'admin_menu.en', 'initsystem',                   '',          0,               '',       'Database Maintenance', 'Database Maintenance', 'Database maintenance such as data initializing.', true),
(10399,   0,            3,        'admin_menu.en', '_399',                         '',          1,               '',       'Return', '', '', true),
(10500,   0,            4,        'admin_menu.en', '_content',                      '',          0,               '',       'Configure Contents', 'Configure Contents', 'Administrate various contents.', true),
(10501,   10500,          0,        'admin_menu.en', 'configwidget_default_content', '',          0,               '',       'Standard Contents', 'Standard Contents', 'Administrate standard contents.', true),
(10502,   10500,          1,        'admin_menu.en', 'configwidget_blog_main',       '',          0,               '',       'Blog Contents', 'Blog Contents', 'Administrate blog contents.', true),
(10503,   10500,          2,        'admin_menu.en', 'configwidget_event_main',       '',          0,               '',       'Event Information', 'Event Information', 'Administrate event Information.', true),
(11100,  0,            11,       'admin_menu.en', '_others',                      '',          0,               '',       'Others', '', '', true),
(11101,  11100,         0,        'admin_menu.en', 'logout',                       '',          0,               '',       'Logout', 'Logout', 'Logout from system.', true);
