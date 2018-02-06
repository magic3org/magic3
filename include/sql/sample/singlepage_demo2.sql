-- *
-- * データ登録スクリプト「シングルページレイアウトデモ2」
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
-- [シングルページレイアウトデモ2]
-- コンテンツがすべて1ページに収まるシングルページレイアウトを使ったサンプル。レスポンシブウェブ対応。
-- Bootstrapベースのシングルページレイアウト用のテンプレートを使用。

-- システム設定
UPDATE _system_config SET sc_value = 'bs_single_orange' WHERE sc_id = 'default_template';

-- サイト定義マスター
DELETE FROM _site_def WHERE sd_id = 'site_name';
INSERT INTO _site_def
(sd_id,                  sd_language_id, sd_value,         sd_name) VALUES
('site_name',            'ja',           'シングルページサンプル',               'サイト名');

-- 管理画面ページデータ(デフォルトを変更)
UPDATE _page_id SET pg_default_sub_id = 'front' WHERE pg_id = 'index' AND pg_type = 0;
-- スマートフォン,携帯のアクセスポイントを隠す
UPDATE _page_id SET pg_active = false WHERE pg_id = 's_index' AND pg_type = 0;
UPDATE _page_id SET pg_active = false WHERE pg_id = 'm_index' AND pg_type = 0;
-- 必要なページのみ表示
DELETE FROM _page_id WHERE pg_type = 1 AND pg_priority < 100;
INSERT INTO _page_id 
(pg_id,          pg_type,      pg_name,                            pg_description,                       pg_priority, pg_active, pg_visible, pg_editable) VALUES
('front',        1,            'トップ画面',                       'トップ画面用',                       0,           true,      true,       true),
('content',      1,            'コンテンツ',                       'コンテンツ画面用',                   1,           true,      true,       false),
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
('search',       1,            '検索',                             '検索画面用',                         21,          false,      true,       true),
('user',         1,            'ユーザコンテンツ',                 'ユーザ作成コンテンツ用',             50,          false,      true,       true);

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
(202,   200,          1,        'admin_menu',    'opelog',             0,               true,       '',       '',               '運用状況', '運用状況', 'サイトの運用状況を表示します。'),
(299,   0,            3,        'admin_menu',    '_299',               1,               true,       '',       '',               '改行',                 '',                     ''),
(300,   0,            4,        'admin_menu',    '_config',            0,               true,       '',       '',               'システム管理',         '',                     ''),
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
(10202, 10200,        1,        'admin_menu.en', 'opelog',             0,               true,       '',       '',               'Site Conditions', 'Site Conditions', 'Operation log and access analytics on site.'),
(10299, 0,            3,        'admin_menu.en', '_10299',             1,               true,       '',       '',               'Return',                 '',                     ''),
(10300, 0,            4,        'admin_menu.en', '_config',            0,               true,       '',       '',               'System Administration',         '',                     ''),
(10301, 10300,        0,        'admin_menu.en', 'configsite',         0,               true,       '',       '',               'Site Information',             'Site Information',             'Configure site information.'),
(10302, 10300,        1,        'admin_menu.en', 'configsys',          0,               true,       '',       '',               'System Information',         'System Information',         'Configure sytem information.'),
(10303, 10300,        2,        'admin_menu.en', 'mainte',             0,               true,       '',       '',               'System Maintenance', 'System Maintenance', 'Maintenance about file system and database.');

-- 画面定義
DELETE FROM _page_def WHERE pd_id = 'index';
INSERT INTO _page_def
(pd_id,   pd_sub_id, pd_position_id,  pd_index, pd_widget_id,         pd_config_id, pd_config_name,       pd_title,             pd_menu_id,  pd_title_visible, pd_view_page_state, pd_update_dt) VALUES
('index', '',        'hmenu',         2,        'default_menu',       1,            'メインメニュー設定', '',                   'main_menu', true,             0,                  now()),
('index', '',        'footer',        20,       'default_footer',     0,            '',                   '',                   '',          false,            0,                  now()),
('index', 'front',   'brand',         2,        'simple_html',        1,            '名称未設定1',                   '',                   '',          false,            0,                  now()),
('index', 'front',   'header',        2,        'simple_html',        2,            '名称未設定2',                   'Your Favorite Source of Free Bootstrap Themes',          '',          true,             0,                  now()),
('index', 'front',   'header-button', 2,        'simple_html',        3,            '名称未設定3',                   '',                   '',          false,            0,                  now()),
('index', 'front',   'about',         2,        'simple_html',        4,            '名称未設定4',                   'We\'ve got what you need!',    '',          true,             0,                  now()),
('index', 'front',   'about-button',  2,        'simple_html',        5,            '名称未設定5',                   '',    '',          false,             0,                  now()),
('index', 'front',   'services',      2,        'simple_html',        6,            '名称未設定6',                   'At Your Service', '',          true,             0,                  now()),
('index', 'front',   'services-body', 2,        'simple_html',        7,            '名称未設定7',                   '', '',          false,             0,                  now()),
('index', 'front',   'contact',       2,        'simple_html',        8,            '名称未設定8',                   'Let\'s Get In Touch!', '',          true,             0,                  now()),
('index', 'front',   'contact-body',  2,        'simple_html',        9,            '名称未設定9',                   '', '',          false,             0,                  now()),
('index', 'front',   'portfolio',     2,        'simple_html',        10,           '名称未設定10',                   '', '',          false,             0,                  now()),
('index', 'front',   'others',        2,        'simple_html',        11,           '名称未設定11',                   'Free Download at Start Bootstrap!', '',          true,             0,                  now());

-- メニュー定義
TRUNCATE TABLE _menu_def;
INSERT INTO _menu_def
(md_id, md_index, md_menu_id,  md_name,        md_link_url,  md_update_dt) VALUES
(1,     1,        'main_menu', 'About',        '#about',     now()),
(2,     2,        'main_menu', 'Services',     '#services',  now()),
(3,     3,        'main_menu', 'Portfolio',    '#portfolio', now()),
(4,     4,        'main_menu', 'Contact',      '#contact',   now());

-- ウィジェットパラメータ
DELETE FROM _widget_param WHERE wp_id = 'default_menu';
INSERT INTO _widget_param
(wp_id,          wp_config_id, wp_param,                                                                                                wp_create_dt) VALUES 
('default_menu', 1,            'O:\8:"stdClass":3:{s:6:"menuId";s:9:"main_menu";s:4:"name";s:27:"メインメニュー設定";s:9:"limitUser";i:0;}', now());
DELETE FROM _widget_param WHERE wp_id = 'simple_html';
INSERT INTO _widget_param
(wp_id,         wp_config_id, wp_param,                                                                                                wp_create_dt) VALUES 
('simple_html', 1,            'O:8:\"stdClass\":3:{s:2:\"id\";N;s:4:\"name\";s:16:\"名称未設定1\";s:4:\"html\";s:15:\"Start Bootstrap\";}', now()),
('simple_html', 2,            'O:8:\"stdClass\":3:{s:2:\"id\";N;s:4:\"name\";s:16:\"名称未設定2\";s:4:\"html\";s:160:\"<p>Start Bootstrap can help you build better websites using the Bootstrap CSS framework! Just download your template and start going, no strings attached!</p>\r\n\";}', now()),
('simple_html', 3,            'O:8:\"stdClass\":3:{s:2:\"id\";N;s:4:\"name\";s:16:\"名称未設定3\";s:4:\"html\";s:13:\"Find Out More\";}', now()),
('simple_html', 4,            'O:8:\"stdClass\":3:{s:2:\"id\";N;s:4:\"name\";s:16:\"名称未設定4\";s:4:\"html\";s:240:\"<p class=\"text-faded\">Start Bootstrap has everything you need to get your new website up and running in no time! All of the templates and themes on Start Bootstrap are open source, free to download, and easy to use. No strings attached!</p>\";}', now()),
('simple_html', 5,            'O:8:\"stdClass\":3:{s:2:\"id\";N;s:4:\"name\";s:16:\"名称未設定5\";s:4:\"html\";s:12:\"Get Started!\";}', now()),
('simple_html', 6,            'O:8:\"stdClass\":1:{s:4:\"html\";s:33:\"<div style=\"display:none;\"></div>\";}', now()),
('simple_html', 7,            'O:8:\"stdClass\":3:{s:2:\"id\";N;s:4:\"name\";s:16:\"名称未設定7\";s:4:\"html\";s:983:\"<div class=\"col-lg-3 col-md-6 text-center\"><div class=\"service-box\"><i class=\"fa fa-4x fa-diamond text-primary sr-icons\">&nbsp;</i><h3>Sturdy Templates</h3><p class=\"text-muted\">Our templates are updated regularly so they don&#39;t break.</p></div></div><div class=\"col-lg-3 col-md-6 text-center\"><div class=\"service-box\"><i class=\"fa fa-4x fa-paper-plane text-primary sr-icons\">&nbsp;</i><h3>Ready to Ship</h3><p class=\"text-muted\">You can use this theme as is, or you can make changes!</p></div></div><div class=\"col-lg-3 col-md-6 text-center\"><div class=\"service-box\"><i class=\"fa fa-4x fa-newspaper-o text-primary sr-icons\">&nbsp;</i><h3>Up to Date</h3><p class=\"text-muted\">We update dependencies to keep things fresh.</p></div></div><div class=\"col-lg-3 col-md-6 text-center\"><div class=\"service-box\"><i class=\"fa fa-4x fa-heart text-primary sr-icons\">&nbsp;</i><h3>Made with Love</h3><p class=\"text-muted\">You have to make your websites with love these days!</p></div></div>\r\n\";}', now()),
('simple_html', 8,            'O:8:\"stdClass\":3:{s:2:\"id\";N;s:4:\"name\";s:16:\"名称未設定8\";s:4:\"html\";s:146:\"<p>Ready to start your next project with us? That\'s great! Give us a call or send us an email and we will get back to you as soon as possible!</p>\";}', now()),
('simple_html', 9,            'O:8:\"stdClass\":3:{s:2:\"id\";N;s:4:\"name\";s:16:\"名称未設定9\";s:4:\"html\";s:304:\"<div class=\"col-lg-4 col-lg-offset-2 text-center\"><i class=\"fa fa-phone fa-3x sr-contact\">&nbsp;</i><p>123-456-6789</p></div><div class=\"col-lg-4 text-center\"><i class=\"fa fa-envelope-o fa-3x sr-contact\">&nbsp;</i><p><a href=\"mailto:your-email@your-domain.com\">feedback@startbootstrap.com</a></p></div>\r\n\";}', now()),
('simple_html', 10,            'O:8:\"stdClass\":3:{s:2:\"id\";N;s:4:\"name\";s:17:\"名称未設定10\";s:4:\"html\";s:2742:\"<div class=\"col-lg-4 col-sm-6\"><a class=\"portfolio-box\" href=\"[#M3_ROOT_URL#]/templates/bs_single_orange/images/portfolio/fullsize/1.jpg\"><img alt=\"\" class=\"img-responsive\" src=\"[#M3_ROOT_URL#]/templates/bs_single_orange/images/portfolio/thumbnails/1.jpg\" /><div class=\"portfolio-box-caption\"><div class=\"portfolio-box-caption-content\"><div class=\"project-category text-faded\">Category</div><div class=\"project-name\">Project Name</div></div></div></a></div><div class=\"col-lg-4 col-sm-6\"><a class=\"portfolio-box\" href=\"[#M3_ROOT_URL#]/templates/bs_single_orange/images/portfolio/fullsize/2.jpg\"><img alt=\"\" class=\"img-responsive\" src=\"[#M3_ROOT_URL#]/templates/bs_single_orange/images/portfolio/thumbnails/2.jpg\" /><div class=\"portfolio-box-caption\"><div class=\"portfolio-box-caption-content\"><div class=\"project-category text-faded\">Category</div><div class=\"project-name\">Project Name</div></div></div></a></div><div class=\"col-lg-4 col-sm-6\"><a class=\"portfolio-box\" href=\"[#M3_ROOT_URL#]/templates/bs_single_orange/images/portfolio/fullsize/3.jpg\"><img alt=\"\" class=\"img-responsive\" src=\"[#M3_ROOT_URL#]/templates/bs_single_orange/images/portfolio/thumbnails/3.jpg\" /><div class=\"portfolio-box-caption\"><div class=\"portfolio-box-caption-content\"><div class=\"project-category text-faded\">Category</div><div class=\"project-name\">Project Name</div></div></div></a></div><div class=\"col-lg-4 col-sm-6\"><a class=\"portfolio-box\" href=\"[#M3_ROOT_URL#]/templates/bs_single_orange/images/portfolio/fullsize/4.jpg\"><img alt=\"\" class=\"img-responsive\" src=\"[#M3_ROOT_URL#]/templates/bs_single_orange/images/portfolio/thumbnails/4.jpg\" /><div class=\"portfolio-box-caption\"><div class=\"portfolio-box-caption-content\"><div class=\"project-category text-faded\">Category</div><div class=\"project-name\">Project Name</div></div></div></a></div><div class=\"col-lg-4 col-sm-6\"><a class=\"portfolio-box\" href=\"[#M3_ROOT_URL#]/templates/bs_single_orange/images/portfolio/fullsize/5.jpg\"><img alt=\"\" class=\"img-responsive\" src=\"[#M3_ROOT_URL#]/templates/bs_single_orange/images/portfolio/thumbnails/5.jpg\" /><div class=\"portfolio-box-caption\"><div class=\"portfolio-box-caption-content\"><div class=\"project-category text-faded\">Category</div><div class=\"project-name\">Project Name</div></div></div></a></div><div class=\"col-lg-4 col-sm-6\"><a class=\"portfolio-box\" href=\"[#M3_ROOT_URL#]/templates/bs_single_orange/images/portfolio/fullsize/6.jpg\"><img alt=\"\" class=\"img-responsive\" src=\"[#M3_ROOT_URL#]/templates/bs_single_orange/images/portfolio/thumbnails/6.jpg\" /><div class=\"portfolio-box-caption\"><div class=\"portfolio-box-caption-content\"><div class=\"project-category text-faded\">Category</div><div class=\"project-name\">Project Name</div></div></div></a></div>\";}', now()),
('simple_html', 11,            'O:8:\"stdClass\":3:{s:2:\"id\";N;s:4:\"name\";s:17:\"名称未設定11\";s:4:\"html\";s:123:\"<a href=\"http://startbootstrap.com/template-overviews/creative/\" class=\"btn btn-default btn-xl sr-button\">Download Now!</a>\";}', now());