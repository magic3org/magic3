-- *
-- * データ登録スクリプト「フォトギャラリーサイトデモ」
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
-- [フォトギャラリーサイトデモ(作成中)]
-- フォトギャラリー主軸型サイト。用途は会員制の画像閲覧サイトなど。
-- 機能はフォトギャラリー、汎用コンテンツ、会員登録等。
-- 初期インストールデータは必要最小限のみ。

-- システム設定
UPDATE _system_config SET sc_value = 'bootstrap_cerulean_head' WHERE sc_id = 'default_template';

-- サイト定義マスター
DELETE FROM _site_def WHERE sd_id = 'site_name';
INSERT INTO _site_def
(sd_id,                  sd_language_id, sd_value,         sd_name) VALUES
('site_name',            'ja',           '世界の動物',               'サイト名');

-- 管理画面ページデータ(デフォルトを変更)
UPDATE _page_id SET pg_default_sub_id = 'photo' WHERE pg_id = 'index' AND pg_type = 0;
-- スマートフォン,携帯のアクセスポイントを隠す
UPDATE _page_id SET pg_active = false WHERE pg_id = 's_index' AND pg_type = 0;
UPDATE _page_id SET pg_active = false WHERE pg_id = 'm_index' AND pg_type = 0;

-- 管理画面メニューデータ
DELETE FROM _nav_item;
INSERT INTO _nav_item
(ni_id, ni_parent_id, ni_index, ni_nav_id,    ni_task_id,        ni_view_control, ni_visible, ni_param, ni_name,                ni_help_title,          ni_help_body) VALUES
(100,   0,            0,        'admin_menu', '_page',           0,               true, '',       '画面管理',             '画面管理',             'Webサイトのデザインや機能を管理します。'),
(101,   100,          0,        'admin_menu', 'pagedef',         0,               true, '',       'PC画面',         'PC画面編集',         'PC用Webサイトの画面を作成します。'),
(102,   100,          1,        'admin_menu', 'pagedef_smartphone',  0,           false, '',       'スマートフォン画面', 'スマートフォン画面編集',       'スマートフォン用Webサイトの画面を作成します。'),
(103,   100,          2,        'admin_menu', 'pagedef_mobile',  0,               false, '',       '携帯画面',       '携帯画面編集',       '携帯用Webサイトの画面を作成します。'),
(104,   100,          3,        'admin_menu', '_104',            3,               true, '',       'セパレータ',                 '',                     ''),
(105,   100,          4,        'admin_menu', 'widgetlist',      0,               true, '',       'ウィジェット管理',     'ウィジェット管理',     'ウィジェットの管理を行います。'),
(106,   100,          5,        'admin_menu', 'templist',        0,               true, '',       'テンプレート管理',     'テンプレート管理',     'テンプレートの管理を行います。'),
(107,   100,          6,        'admin_menu', 'smenudef',        0,               true, '',       'メニュー管理', 'メニュー管理', 'メニュー定義を管理します。'),
(199,   0,            1,        'admin_menu', '_199',            1,               true, '',       '改行',                 '',                     ''),
(200,   0,            2,        'admin_menu', '_login',          0,               true, '',       'システム運用',         '',                     ''),
(201,   200,          0,        'admin_menu', 'userlist',        0,               true, '',       'ユーザ一覧',           'ユーザ一覧',           'ログイン可能なユーザを管理します。'),
(202,   200,          1,        'admin_menu', 'accesslog',       0,               true, '',       '運用状況', '運用状況', 'サイトの運用状況を表示します。'),
(299,   0,            3,        'admin_menu', '_299',            1,               true, '',       '改行',                 '',                     ''),
(300,   0,            4,        'admin_menu', '_config',         0,               true, '',       'システム管理',         '',                     ''),
(301,   300,          0,        'admin_menu', 'configsite',      0,               true, '',       '基本情報',             '基本情報',             'サイト運営に必要な情報を設定します。'),
(302,   300,          1,        'admin_menu', 'configsys',       0,               true, '',       'システム情報',         'システム情報',         'システム全体の設定、運用状況を管理します。'),
(303,   300,          2,        'admin_menu', 'mainte',          0,               true, '',       'メンテナンス', 'メンテナンス', 'ファイルやDBなどのメンテナンスを行います。'),
(10100,   0,            0,        'admin_menu.en', '_page',           0,               true, '',       'Edit Page',             'Edit Page',             'Edit page for design and function.'),
(10101,   10100,          0,        'admin_menu.en', 'pagedef',         0,               true, '',       'PC Page',         'PC Page',         'Edit page for PC.'),
(10102,   10100,          1,        'admin_menu.en', 'pagedef_smartphone',  0,           false, '',       'Smartphone Page', 'Smartphone Page',       'Edit page for Smartphone.'),
(10103,   10100,          2,        'admin_menu.en', 'pagedef_mobile',  0,               false, '',       'Mobile Page',       'Mobile Page',       'Edit page for Mobile.'),
(10104,   10100,          3,        'admin_menu.en', '_10104',            3,               true, '',       'Separator',                 '',                     ''),
(10105,   10100,          4,        'admin_menu.en', 'widgetlist',      0,               true, '',       'Widget Administration',     'Widget Administration',     'Administrate widgets with widget config window.'),
(10106,   10100,          5,        'admin_menu.en', 'templist',        0,               true, '',       'Template Administration',     'Template Administration',     'Administrate templates.'),
(10107,   10100,          6,        'admin_menu.en', 'smenudef',        0,               true, '',       'Menu Administration', 'Menu Administration', 'Administrate menu definition.'),
(10199,   0,            1,        'admin_menu.en', '_10199',            1,               true, '',       'Return',                 '',                     ''),
(10200,   0,            2,        'admin_menu.en', '_login',          0,               true, '',       'System Operation',         '',                     ''),
(10201,   10200,          0,        'admin_menu.en', 'userlist',        0,               true, '',       'User List',           'User List',           'Administrate user to login.'),
(10202,   10200,          1,        'admin_menu.en', 'accesslog',     0,               true, '',       'Site Conditions', 'Site Conditions', 'Operation log and access analytics on site.'),
(10299,   0,            3,        'admin_menu.en', '_10299',            1,               true, '',       'Return',                 '',                     ''),
(10300,   0,            4,        'admin_menu.en', '_config',         0,               true, '',       'System Administration',         '',                     ''),
(10301,   10300,          0,        'admin_menu.en', 'configsite',      0,               true, '',       'Site Information',             'Site Information',             'Configure site information.'),
(10302,   10300,          1,        'admin_menu.en', 'configsys',       0,               true, '',       'System Information',         'System Information',         'Configure sytem information.'),
(10303,   10300,          2,        'admin_menu.en', 'mainte',          0,               true, '',       'System Maintenance', 'System Maintenance', 'Maintenance about file system and database.');

-- 画面定義
DELETE FROM _page_def WHERE pd_id = 'index';
INSERT INTO _page_def
(pd_id,   pd_sub_id, pd_position_id,   pd_index, pd_widget_id,         pd_config_id, pd_config_name,       pd_menu_id,  pd_title,  pd_title_visible, pd_view_control_type, pd_update_dt) VALUES
('index', '',        'user3',           2,       'default_menu',       1,            'メインメニュー設定', 'main_menu', '',        false,            0,                    now()),
('index', '',        'footer',          2,       'default_footer',     0,            '',                   '',          '',        false,            0,                    now()),
('index', '',        'footer',          10,      'gotop',              0,            '',                   '',          '',        false,            0,                    now()),
('index', 'photo',   'header-pre-hide', 2,       'slogan',             1,            '',                   '',          '',        false,            0,                    now()),
('index', 'photo',   'header',          2,       'slide_image',        1,            '',                   '',          '',        false,            0,                    now()),
('index', 'photo',   'main',            2,       'static_content',     1,            '',                   '',          '',        true,             2,                    now()),
('index', 'photo',   'main',            3,       'pretty_photo',       1,            '',                   '',          '',        false,            2,                    now()),
('index', 'photo',    'main',            5,       'photo_main',          0,            '',                   '',        '',        false,            1,                    now()),
('index', 'photo',    'left',            5,       'photo_new',          0,            '',                   '',         '最新画像', true,            1,                    now()),
('index', 'content', 'main',            6,       'default_content',    0,            '',                   '',          '',        true,            0,                    now()),
('index', 'member',  'main',            6,       'reg_user',           0,            '',                   '',          '',        true,             0,                    now()),
('index', 'search',  'main',            5,       'custom_search',      1,            '',                   '',          '',        false,            0,                    now()),
('index', 'contact', 'main',            5,       'contactus',          0,            '',                   '',          '',        true,            0,                    now());

-- 新メニュー対応
TRUNCATE TABLE _menu_def;
INSERT INTO _menu_def
(md_id, md_index, md_menu_id,  md_name,        md_link_url,                                         md_update_dt) VALUES
(1,     1,        'main_menu', 'ホーム',       '[#M3_ROOT_URL#]/',                                   now()),
(2,     2,        'main_menu', '運営',     '[#M3_ROOT_URL#]/index.php?contentid=1', now()),
(3,     3,        'main_menu', 'お問い合わせ', '[#M3_ROOT_URL#]/index.php?sub=contact',             now());

-- ウィジェットパラメータ
DELETE FROM _widget_param WHERE wp_id = 'default_menu';
INSERT INTO _widget_param
(wp_id,          wp_config_id, wp_param,                                                                                                wp_create_dt) VALUES 
('default_menu', 1,            'O:8:"stdClass":10:{s:6:"menuId";s:9:"main_menu";s:4:"name";s:27:"メインメニュー設定";s:9:"limitUser";s:1:"0";s:10:"isHierMenu";s:1:"0";s:15:"useVerticalMenu";s:1:"0";s:12:"showSitename";s:1:"0";s:10:"showSearch";s:1:"1";s:12:"anotherColor";s:1:"1";s:9:"showLogin";s:1:"1";s:10:"showRegist";s:1:"1";}', now());
DELETE FROM _widget_param WHERE wp_id = 'custom_search';
INSERT INTO _widget_param
(wp_id,          wp_config_id, wp_param,                                                                                                wp_create_dt) VALUES 
('custom_search', 1,            'O:8:"stdClass":16:{s:4:"name";s:16:"名称未設定1";s:11:"resultCount";s:2:"20";s:14:"searchTemplate";s:241:"<input id="custom_search_1_text" maxlength="40" size="10" type="text" /> <input class="button" id="custom_search_1_button" type="button" value="検索" /> <input class="button" id="custom_search_1_reset" type="button" value="リセット" />";s:12:"searchTextId";s:20:"custom_search_1_text";s:14:"searchButtonId";s:22:"custom_search_1_button";s:13:"searchResetId";s:21:"custom_search_1_reset";s:15:"isTargetContent";i:1;s:12:"isTargetUser";i:0;s:12:"isTargetBlog";i:0;s:9:"fieldInfo";a:0:{}s:15:"isTargetProduct";i:0;s:13:"isTargetEvent";i:0;s:11:"isTargetBbs";i:0;s:13:"isTargetPhoto";i:1;s:12:"isTargetWiki";i:0;s:12:"resultLength";s:3:"200";}', now());
DELETE FROM _widget_param WHERE wp_id = 'slogan';
INSERT INTO _widget_param
(wp_id,          wp_config_id, wp_param,                                                                                                wp_create_dt) VALUES 
('slogan', 1,            'O:8:"stdClass":7:{s:4:"name";s:16:"名称未設定1";s:7:"message";s:15:"世界の動物";s:4:"size";s:3:"2.5";s:5:"cssId";s:8:"slogan_1";s:3:"css";s:129:"#slogan_1 {\r\n    position:relative;\r\n    color:#FFF;\r\n    z-index:200;\r\n    top:1.0em;\r\n    padding-left:3em;\r\n    height:0;\r\n}\r\n";s:7:"minSize";s:2:"20";s:7:"maxSize";s:2:"50";}', now());
DELETE FROM _widget_param WHERE wp_id = 'slide_image';
INSERT INTO _widget_param
(wp_id,          wp_config_id, wp_param,                                                                                                wp_create_dt) VALUES 
('slide_image', 1,            'O:8:"stdClass":8:{s:4:"name";s:16:"名称未設定1";s:9:"imageInfo";a:4:{i:0;O:8:"stdClass":2:{s:4:"name";s:0:"";s:3:"url";s:59:"[#M3_ROOT_URL#]/resource/image/sample/header/rhinoceros.jpg";}i:1;O:8:"stdClass":2:{s:4:"name";s:0:"";s:3:"url";s:56:"[#M3_ROOT_URL#]/resource/image/sample/header/buffalo.jpg";}i:2;O:8:"stdClass":2:{s:4:"name";s:0:"";s:3:"url";s:57:"[#M3_ROOT_URL#]/resource/image/sample/header/elephant.jpg";}i:3;O:8:"stdClass":2:{s:4:"name";s:0:"";s:3:"url";s:53:"[#M3_ROOT_URL#]/resource/image/sample/header/lion.jpg";}}s:5:"cssId";s:13:"slide_image_1";s:3:"css";s:282:"#slide_image_1 .bx-wrapper img {\r\n	margin: 0 auto;\r\n	width:100%;\r\n}\r\n#slide_image_1 .bx-wrapper .bx-viewport {\r\n	-moz-box-shadow: none;\r\n	-webkit-box-shadow: none;\r\n	box-shadow: none;\r\n	border:none;\r\n	background-color:transparent;\r\n	left:0;\r\n}\r\n#slide_image_1\r\n	overflow: hidden;\r\n}";s:9:"showTitle";s:1:"0";s:9:"showPager";s:1:"0";s:11:"showControl";s:1:"1";s:4:"auto";s:1:"0";}', now());
DELETE FROM _widget_param WHERE wp_id = 'static_content';
INSERT INTO _widget_param
(wp_id,          wp_config_id, wp_param,                                                                                                wp_create_dt) VALUES 
('static_content', 1,            'O:8:"stdClass":4:{s:4:"name";s:16:"名称未設定1";s:9:"contentId";s:1:"2";s:12:"showReadMore";i:0;s:13:"readMoreTitle";s:0:"";}', now());
DELETE FROM _widget_param WHERE wp_id = 'pretty_photo';
INSERT INTO _widget_param
(wp_id,          wp_config_id, wp_param,                                                                                                wp_create_dt) VALUES 
('pretty_photo', 1,            'O:8:"stdClass":9:{s:4:"name";s:16:"名称未設定1";s:4:"size";s:3:"120";s:7:"opacity";s:4:"0.80";s:9:"imageInfo";a:8:{i:0;O:8:"stdClass":3:{s:4:"name";s:0:"";s:4:"desc";s:0:"";s:3:"url";s:54:"[#M3_ROOT_URL#]/resource/image/sample/animal/top01.jpg";}i:1;O:8:"stdClass":3:{s:4:"name";s:0:"";s:4:"desc";s:0:"";s:3:"url";s:54:"[#M3_ROOT_URL#]/resource/image/sample/animal/top02.jpg";}i:2;O:8:"stdClass":3:{s:4:"name";s:0:"";s:4:"desc";s:0:"";s:3:"url";s:54:"[#M3_ROOT_URL#]/resource/image/sample/animal/top03.jpg";}i:3;O:8:"stdClass":3:{s:4:"name";s:0:"";s:4:"desc";s:0:"";s:3:"url";s:54:"[#M3_ROOT_URL#]/resource/image/sample/animal/top04.jpg";}i:4;O:8:"stdClass":3:{s:4:"name";s:0:"";s:4:"desc";s:0:"";s:3:"url";s:54:"[#M3_ROOT_URL#]/resource/image/sample/animal/top05.jpg";}i:5;O:8:"stdClass":3:{s:4:"name";s:0:"";s:4:"desc";s:0:"";s:3:"url";s:54:"[#M3_ROOT_URL#]/resource/image/sample/animal/top06.jpg";}i:6;O:8:"stdClass":3:{s:4:"name";s:0:"";s:4:"desc";s:0:"";s:3:"url";s:54:"[#M3_ROOT_URL#]/resource/image/sample/animal/top07.jpg";}i:7;O:8:"stdClass":3:{s:4:"name";s:0:"";s:4:"desc";s:0:"";s:3:"url";s:54:"[#M3_ROOT_URL#]/resource/image/sample/animal/top08.jpg";}}s:5:"cssId";s:14:"pretty_photo_1";s:3:"css";s:870:"#pretty_photo_1.gallery {\r\n	list-style: none;\r\n	/*margin: 0 0 10px 0;*/\r\n	margin: 0;\r\n	padding: 0;\r\n	border: 0;\r\n}\r\n#pretty_photo_1.gallery li {\r\n	display: block;\r\n	float: left;\r\n	margin: 0 5px 5px 0;\r\n}\r\n#pretty_photo_1.gallery li:before {	/* remove list mark */\r\n	display:none;\r\n}\r\n#pretty_photo_1.gallery li a {\r\n	padding: 2px;\r\n	display: block;\r\n	border: 2px #9db2b9 solid;\r\n	-moz-border-radius: 5px;\r\n	-webkit-border-radius: 5px;\r\n	line-height: 0;\r\n	text-decoration: none;\r\n}\r\n#pretty_photo_1.gallery li a:hover {\r\n	border: 2px #313739 solid;\r\n}\r\n#pretty_photo_1.gallery li a:focus {\r\n	outline: none;\r\n}\r\n#pretty_photo_1.gallery li a img {\r\n	margin: 0;\r\n	padding: 0;\r\n	border: 0;\r\n}\r\n#pretty_photo_1.clearfix:after {\r\n	content: "."; \r\n	display: block; \r\n	height: 0; \r\n	clear: both; \r\n	visibility: hidden;\r\n}\r\n#pretty_photo_1.clearfix {\r\n	display: inline-block;\r\n}\r\n";s:5:"theme";s:13:"light_rounded";s:9:"showTitle";s:1:"0";s:16:"showSocialButton";s:1:"0";}', now());
DELETE FROM _widget_param WHERE wp_id = 'reg_user';
INSERT INTO _widget_param
(wp_id,      wp_config_id, wp_param,                                                                                                wp_create_dt) VALUES 
('reg_user', 0,            'O:8:"stdClass":1:{s:8:"authType";s:4:"auto";}', now());

-- コンテンツ
TRUNCATE TABLE content;
INSERT INTO content
(cn_type, cn_id, cn_language_id, cn_name,              cn_html,                        cn_default, cn_key, cn_create_user_id, cn_create_dt) VALUES 
('', 1,     'ja',           '会社情報', '<table class="table">\r\n	<tbody>\r\n		<tr>\r\n			<th>社　名</th>\r\n			<td>\r\n			<p>株式会社ドキュメント</p>\r\n			</td>\r\n		</tr>\r\n		<tr>\r\n			<th>所在地</th>\r\n			<td>\r\n			<p></p>\r\n			</td>\r\n		</tr>\r\n		<tr>\r\n			<th>設　立</th>\r\n			<td>\r\n			<p></p>\r\n			</td>\r\n		</tr>\r\n		<tr>\r\n			<th>代表者</th>\r\n			<td>\r\n			<p></p>\r\n			</td>\r\n		</tr>\r\n		<tr>\r\n			<th>事業内容</th>\r\n			<td>\r\n			<p>●ドキュメントの作成</p>\r\n			</td>\r\n		</tr>\r\n		<tr>\r\n			<th>主要取引銀行</th>\r\n			<td>\r\n			<p></p>\r\n			</td>\r\n		</tr>\r\n		<tr>\r\n			<th>主要取引先</th>\r\n			<td>\r\n			<p></p>\r\n			</td>\r\n		</tr>\r\n		<tr>\r\n			<th></th>\r\n			<td></td>\r\n		</tr>\r\n	</tbody>\r\n</table>',              false, '',                0, now()),
('', 2,     'ja',           'ようこそ「世界の動物」へ',   '<p>このサイトはＰＣ、スマートフォン、タブレットなどマルチデバイスに対応した会員制の画像ライブラリサイトです。<br />\r\n会員登録を行ってから、ログインすると画像が閲覧できます。</p>\r\n\r\n<p><a class="button" href="http://192.168.216.131/magic3/index.php?sub=member">会員登録へ</a></p>\r\n',              false, '',                0, now());
