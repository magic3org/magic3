-- *
-- * データ登録スクリプト「イベントサイトデモ」
-- *
-- * PHP versions 5
-- *
-- * LICENSE: This source file is licensed under the terms of the GNU General Public License.
-- *
-- * @package    Magic3 Framework
-- * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
-- * @copyright  Copyright 2006-2015 Magic3 Project.
-- * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
-- * @version    SVN: $Id$
-- * @link       http://www.magic3.org
-- *
-- [イベントサイトデモ]
-- イベント情報主軸型サイト。
-- 主な機能は、イベント情報、カレンダー。

-- システム設定
UPDATE _system_config SET sc_value = 'art42_sample3' WHERE sc_id = 'default_template';

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
('blog',         1,            'ブログ',                           'ブログ画面用',                       5,           true,      true,       true),
('wiki',         1,            'Wiki',                             'Wiki画面用',                         6,           false,      true,       true),
('calendar',     1,            'カレンダー',                       'カレンダー画面用',                   7,           true,      true,       true),
('event',        1,            'イベント情報',                     'イベント情報画面用',                 8,           true,      true,       true),
('photo',        1,            'フォトギャラリー',                 'フォトギャラリー画面用',             9,           false,      true,       true),
('contact',      1,            'お問い合わせ',                     'お問い合わせ画面用',                 10,          true,      true,       true),
('contact2',     1,            'お問い合わせ2',                    'お問い合わせ画面用',                 11,          false,      true,       true),
('reguser',      1,            'ユーザ登録',                       'ユーザ登録画面用',                   12,          false,      true,       true),
('reserve',      1,            '予約',                             '予約画面用',                         19,          false,      true,       true),
('member',       1,            '会員',                             '会員画面用',                         20,          true,      true,       true),
('evententry',   1,            'イベント予約',                     'イベント予約画面用',                 21,          true,      true,       true),
('search',       1,            '検索',                             '検索画面用',                         22,          true,      true,       true),
('user',         1,            'ユーザコンテンツ',                 'ユーザ作成コンテンツ用',             50,          false,      true,       true);

-- 管理画面メニューデータ
DELETE FROM _nav_item WHERE ni_nav_id = 'admin_menu';
DELETE FROM _nav_item WHERE ni_nav_id = 'admin_menu.en';
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
(201,   200,          0,        'admin_menu', 'userlist',        0,               true, '',       'ユーザ管理',           'ユーザ管理',           'ログイン可能なユーザを管理します。'),
(202,   200,          1,        'admin_menu', 'opelog',       0,               true, '',       '運用状況', '運用状況', 'サイトの運用状況を表示します。'),
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
(10202,   10200,          1,        'admin_menu.en', 'opelog',     0,               true, '',       'Site Conditions', 'Site Conditions', 'Operation log and access analytics on site.'),
(10299,   0,            3,        'admin_menu.en', '_10299',            1,               true, '',       'Return',                 '',                     ''),
(10300,   0,            4,        'admin_menu.en', '_config',         0,               true, '',       'System Administration',         '',                     ''),
(10301,   10300,          0,        'admin_menu.en', 'configsite',      0,               true, '',       'Site Information',             'Site Information',             'Configure site information.'),
(10302,   10300,          1,        'admin_menu.en', 'configsys',       0,               true, '',       'System Information',         'System Information',         'Configure sytem information.'),
(10303,   10300,          2,        'admin_menu.en', 'mainte',          0,               true, '',       'System Maintenance', 'System Maintenance', 'Maintenance about file system and database.');

-- 画面定義
DELETE FROM _page_def WHERE pd_id = 'index';
INSERT INTO _page_def
(pd_id,   pd_sub_id, pd_position_id, pd_index, pd_widget_id,          pd_config_id, pd_config_name,       pd_title,         pd_menu_id,  pd_title_visible,
pd_css, pd_param) VALUES
('index', '',        'user3',        2,        'default_menu',        1,            'メインメニュー設定', '',               'main_menu', true,
'',     ''),
('index', '',        'left',         4,        'event_category_menu', 0,            '',                   'カテゴリー',               '',          true,
'',     ''),
('index', '',        'left',         5,        'event_category',      1,            '名称未設定1',        '',               '',          true,
'',     ''),
('index', '',        'left',         7,        'event_headline',      2,            '名称未設定2',        '過去のイベント', '',          true,
'',     ''),
('index', '',        'left',         9,        'default_login_box',   0,            '',                   '',               '',          true,
'',     ''),
('index', 'front',   'main',         3,        'static_content',      1,            '名称未設定1',        '',               '',          true,
'',     ''),
('index', 'front',   'main',         5,        'news_headline',       1,            '名称未設定1',        '新着情報',       '',          true,
'',     ''),
('index', 'front',   'main',         7,        'event_headline',      1,            '名称未設定1',        '今後のイベント', '',          true,
'#[#M3_WIDGET_CSS_ID#] ul>li:before {\n    content: none !important;\n    margin: 0;\n    padding: 0;\n}\n#[#M3_WIDGET_CSS_ID#] ul>li {\n    padding-left: 0;\n}\n#[#M3_WIDGET_CSS_ID#] ul{\n    padding-left: 0;\n    list-style: none;\n}\n',
'O:8:"stdClass":2:{s:16:"removeListMarker";s:1:"1";s:3:"css";s:0:"";}'),
('index', 'content', 'main',         6,        'default_content',     0,            '',                   '',               '',          true,
'',     ''),
('index', 'event',   'main',         5,        'event_main',          0,            '',                   'イベント',       '',          true,
'',     ''),
('index', 'calendar','main',         5,        'calendar',            1,            '名称未設定1',        '',               '',          true,
'',     ''),
('index', 'search',  'main',         5,        'custom_search',       1,            '名称未設定1',        '検索',               '',          true,
'',     ''),
('index', 'contact', 'main',         5,        'contactus',           0,            '',                   'お問い合わせ',               '',          true,
'',     '');

-- 新メニュー対応
TRUNCATE TABLE _menu_def;
INSERT INTO _menu_def
(md_id, md_index, md_menu_id,  md_name,        md_link_url,                                         md_update_dt) VALUES
(1,     1,        'main_menu', 'ホーム',       '[#M3_ROOT_URL#]/',                                   now()),
(2,     2,        'main_menu', 'カレンダー',     '[#M3_ROOT_URL#]/index.php?sub=calendar', now()),
(3,     3,        'main_menu', '運営',     '[#M3_ROOT_URL#]/index.php?contentid=1', now()),
(4,     4,        'main_menu', 'お問い合わせ', '[#M3_ROOT_URL#]/index.php?sub=contact',             now());

-- ウィジェットパラメータ
DELETE FROM _widget_param WHERE wp_id = 'default_menu';
INSERT INTO _widget_param
(wp_id,          wp_config_id, wp_param,                                                                                                wp_create_dt) VALUES 
('default_menu', 1,            'O:\8:"stdClass":3:{s:6:"menuId";s:9:"main_menu";s:4:"name";s:27:"メインメニュー設定";s:9:"limitUser";i:0;}', now());
DELETE FROM _widget_param WHERE wp_id = 'custom_search';
INSERT INTO _widget_param
(wp_id,          wp_config_id, wp_param,                                                                                                wp_create_dt) VALUES 
('custom_search', 1,            'O:8:"stdClass":20:{s:4:"name";s:16:"名称未設定1";s:11:"resultCount";s:2:"20";s:14:"searchTemplate";s:247:"<br />\r\n<input id="custom_search_1_text" maxlength="40" size="10" type="text" /><input class="button" id="custom_search_1_button" type="button" value="検索" /><input class="button" id="custom_search_1_reset" type="button" value="リセット" />";s:12:"searchTextId";s:20:"custom_search_1_text";s:14:"searchButtonId";s:22:"custom_search_1_button";s:13:"searchResetId";s:21:"custom_search_1_reset";s:15:"isTargetContent";i:1;s:12:"isTargetUser";i:1;s:12:"isTargetBlog";i:1;s:9:"fieldInfo";a:0:{}s:15:"isTargetProduct";i:0;s:13:"isTargetEvent";i:1;s:11:"isTargetBbs";i:0;s:13:"isTargetPhoto";i:0;s:12:"isTargetWiki";i:0;s:12:"resultLength";s:3:"200";s:9:"showImage";s:1:"0";s:9:"imageType";s:7:"72c.jpg";s:10:"imageWidth";i:0;s:11:"imageHeight";i:0;}', now());
DELETE FROM _widget_param WHERE wp_id = 'static_content';
INSERT INTO _widget_param
(wp_id,          wp_config_id, wp_param,                                                                                                wp_create_dt) VALUES 
('static_content', 1,            'O:8:"stdClass":4:{s:4:"name";s:16:"名称未設定1";s:9:"contentId";s:1:"2";s:12:"showReadMore";i:0;s:13:"readMoreTitle";s:0:"";}', now());
DELETE FROM _widget_param WHERE wp_id = 'news_headline';
INSERT INTO _widget_param
(wp_id,          wp_config_id, wp_param,                                                                                                wp_create_dt) VALUES 
('news_headline', 1,            'O:8:"stdClass":3:{s:4:"name";s:16:"名称未設定1";s:9:"itemCount";s:2:"10";s:6:"useRss";i:1;}', now());
DELETE FROM _widget_param WHERE wp_id = 'event_headline';
INSERT INTO _widget_param
(wp_id,          wp_config_id, wp_param,                                                                                                wp_create_dt) VALUES 
('event_headline', 1,            'O:8:"stdClass":12:{s:4:"name";s:16:"名称未設定1";s:9:"itemCount";s:1:"5";s:9:"sortOrder";s:1:"0";s:15:"futureEventOnly";s:1:"0";s:6:"useRss";s:1:"1";s:10:"useBaseDay";s:1:"1";s:8:"dayCount";s:1:"0";s:9:"showImage";s:1:"1";s:9:"imageType";s:7:"72c.jpg";s:10:"imageWidth";i:0;s:11:"imageHeight";i:0;s:6:"layout";s:157:"<div style="float:left;">[#IMAGE#]</div>\r\n\r\n<div class="clearfix">\r\n<div>[#TITLE#] ([#CT_DATE#] [#CT_TIME|H:i#])</div>\r\n\r\n<div>[#CT_SUMMARY#]</div>\r\n</div>\r\n";}', now()),
('event_headline', 2,            'O:8:"stdClass":11:{s:4:"name";s:16:"名称未設定2";s:9:"itemCount";s:2:"10";s:9:"sortOrder";s:1:"1";s:10:"useBaseDay";s:1:"1";s:8:"dayCount";s:2:"-1";s:9:"showImage";s:1:"0";s:9:"imageType";s:7:"80c.jpg";s:10:"imageWidth";i:0;s:11:"imageHeight";i:0;s:6:"useRss";s:1:"0";s:6:"layout";s:39:"[#TITLE#] ([#CT_DATE#] [#CT_TIME|H:i#])";}', now());
DELETE FROM _widget_param WHERE wp_id = 'calendar';
INSERT INTO _widget_param
(wp_id,          wp_config_id, wp_param,                                                                                                wp_create_dt) VALUES 
('calendar', 1,            'O:8:"stdClass":14:{s:4:"name";s:16:"名称未設定1";s:9:"dateDefId";s:1:"0";s:10:"viewOption";s:564:"// ヘッダーのタイトルとボタン\r\nheader: {\r\n	// title, prev, next, prevYear, nextYear, today\r\n	left: "prev,next today",\r\n	center: "title",\r\n	//right: "month,agendaWeek,agendaDay"\r\n	//right: "month,basicWeek,basicDay"\r\n	right: ""\r\n},\r\n// コンテンツの高さ(px)\r\n//contentHeight: 600,\r\n// カレンダーの縦横比(比率が大きくなると高さが縮む)\r\n//aspectRatio: 1.35,\r\n// イベントの表示項目数の制限\r\neventLimit: true,	// カラムの高さで制限\r\n// イベントの時刻表示フォーマット\r\ntimeFormat: "H:mm",\r\n";s:15:"showSimpleEvent";s:1:"0";s:9:"showEvent";s:1:"1";s:16:"showEventTooltip";s:1:"1";s:11:"showHoliday";s:1:"0";s:28:"simpleEventTooltipTitleStyle";s:32:"color: "#fff", background: "red"";s:29:"simpleEventTooltipBorderStyle";s:34:"width: 2, radius: 5, color: "#444"";s:22:"eventTooltipTitleStyle";s:32:"color: "#fff", background: "red"";s:23:"eventTooltipBorderStyle";s:34:"width: 2, radius: 5, color: "#444"";s:13:"layoutTooltip";s:366:"<span class="tooltip_title" style="font-weight:bold;">開始：</span>[#CT_START_TIME#]<br />\r\n<span class="tooltip_title" style="font-weight:bold;">終了：</span>[#CT_END_TIME#]<br />\r\n<span class="tooltip_title" style="font-weight:bold;">場所：</span>[#CT_PLACE#]<br />\r\n<span class="tooltip_title" style="font-weight:bold;">概要：</span>[#CT_DESCRIPTION#]";s:12:"holidayColor";s:0:"";s:3:"css";s:547:"#calendar a {\r\n    color: #fff;\r\n}\r\n/* カレンダーヘッダ部\r\n.fc-widget-header {\r\n    background-color:#0066cc;\r\n}*/\r\n.fc-sun {\r\n    color: red; /* 文字色(日曜) */\r\n}\r\n.fc-sat {\r\n    color: blue; /* 文字色(土曜) */\r\n}\r\n/* 定休日サンプル\r\n.fc-wed {\r\n    background-color: #FFDFDF;\r\n    border-color: #FFDFDF;\r\n}*/\r\n/* 本日サンプル\r\n.fc-today{\r\n    background-color: yellow;\r\n}*/\r\n/* イベント */\r\n.fc-event,\r\n.fc-agenda .fc-event-time,\r\n.fc-event a {\r\n    background-color: #228B22;\r\n    border-color: #228B22;\r\n}\r\n";}', now());
DELETE FROM _widget_param WHERE wp_id = 'event_category';
INSERT INTO _widget_param
(wp_id,          wp_config_id, wp_param,                                                                                                wp_create_dt) VALUES 
('event_category', 1,            'O:8:"stdClass":6:{s:4:"name";s:16:"名称未設定1";s:10:"categoryId";s:1:"1";s:9:"itemCount";s:2:"10";s:9:"sortOrder";s:1:"1";s:15:"futureEventOnly";s:1:"0";s:6:"useRss";s:1:"0";}', now());

-- イベント定義
DELETE FROM event_config WHERE eg_id = 'entry_view_count';
INSERT INTO event_config
(eg_id,                     eg_value,                         eg_name) VALUES
('entry_view_count',     '3', '記事表示数');

-- イベント
TRUNCATE TABLE event_entry;
INSERT INTO event_entry
(ee_id, ee_language_id, ee_name, ee_html, ee_summary, ee_place, ee_status, ee_start_dt, ee_related_content) VALUES
(1, 'ja', 'イベント-A1', 'イベントサンプル　イベントサンプル　イベントサンプル　イベントサンプル<br />\r\nイベントサンプル　イベントサンプル　イベントサンプル　イベントサンプル<br />\r\n<br />\r\n<img alt="" height="216" src="[#M3_ROOT_URL#]/resource/image/sample/photo/samleL1.jpg" width="288" />', 'イベント-A1の説明', 'A会場', 2, '2015-04-10 10:00:00', '2,3'),
(2, 'ja', 'イベント-A2', 'イベントサンプル　イベントサンプル　イベントサンプル　イベントサンプル<br />\r\nイベントサンプル　イベントサンプル　イベントサンプル　イベントサンプル<br />\r\n<br />\r\n<img alt="" height="301" src="[#M3_ROOT_URL#]/resource/image/sample/photo/samleL4.jpg" width="402" />', 'イベント-A2の説明', 'A会場', 2, '2015-04-19 07:00:00', '1,3'),
(3, 'ja', 'イベント-A3', 'イベントサンプル　イベントサンプル　イベントサンプル　イベントサンプル<br />\r\nイベントサンプル　イベントサンプル　イベントサンプル　イベントサンプル<br />\r\n<br />\r\n<img alt="" src="[#M3_ROOT_URL#]/resource/image/sample/photo/sample1.jpg" style="width: 150px; height: 113px;" />', 'イベント-A3の説明', 'A会場', 2, '2015-05-01 01:00:00', '1,2'),
(4, 'ja', 'イベント-A4', 'イベントサンプル　イベントサンプル　イベントサンプル　イベントサンプル<br />\r\nイベントサンプル　イベントサンプル　イベントサンプル　イベントサンプル<br />\r\n<br />\r\n<img alt="" height="224" src="[#M3_ROOT_URL#]/resource/image/sample/photo/samleL5.jpg" width="336" />', 'イベント-A4の説明', 'A会場', 2, '2015-06-02 08:00:00', '5,6'),
(5, 'ja', 'イベント-A5', 'イベントサンプル　イベントサンプル　イベントサンプル　イベントサンプル<br />\r\nイベントサンプル　イベントサンプル　イベントサンプル　イベントサンプル<br />\r\n<br />\r\n<img alt="" height="224" src="[#M3_ROOT_URL#]/resource/image/sample/photo/samleL5.jpg" width="336" />', 'イベント-A5の説明', 'A会場', 2, '2015-06-04 07:00:00', '7,8'),
(6, 'ja', 'イベント-A6', 'イベントサンプル　イベントサンプル　イベントサンプル　イベントサンプル<br />\r\nイベントサンプル　イベントサンプル　イベントサンプル　イベントサンプル<br />\r\n<br />\r\n<img alt="" height="224" src="[#M3_ROOT_URL#]/resource/image/sample/photo/samleL5.jpg" width="336" />', 'イベント-A6の説明', 'A会場', 2, '2015-07-01 12:00:00', ''),
(7, 'ja', 'イベント-A7', 'イベントサンプル　イベントサンプル　イベントサンプル　イベントサンプル<br />\r\nイベントサンプル　イベントサンプル　イベントサンプル　イベントサンプル<br />\r\n<br />\r\n<img alt="" height="224" src="[#M3_ROOT_URL#]/resource/image/sample/photo/samleL5.jpg" width="336" />', 'イベント-A7の説明', 'A会場', 2, '2015-08-02 21:00:00', '5,8'),
(8, 'ja', 'イベント-A8', 'イベントサンプル　イベントサンプル　イベントサンプル　イベントサンプル<br />\r\nイベントサンプル　イベントサンプル　イベントサンプル　イベントサンプル<br />\r\n<br />\r\n<img alt="" height="381" src="[#M3_ROOT_URL#]/resource/image/sample/photo/samleL6.jpg" width="254" />', 'イベント-A8の説明', 'A会場', 2, '2015-9-19 09:00:00', '5,7'),
(9, 'ja', 'イベント-A9', 'イベントサンプル　イベントサンプル　イベントサンプル　イベントサンプル<br />\r\nイベントサンプル　イベントサンプル　イベントサンプル　イベントサンプル<br />\r\n<br />\r\n<img alt="" height="381" src="[#M3_ROOT_URL#]/resource/image/sample/photo/samleL6.jpg" width="254" />', 'イベント-A9の説明', 'A会場', 2, '2015-10-12 11:00:00', ''),
(10, 'ja', 'イベント-A10', 'イベントサンプル　イベントサンプル　イベントサンプル　イベントサンプル<br />\r\nイベントサンプル　イベントサンプル　イベントサンプル　イベントサンプル<br />\r\n<br />\r\n<img alt="" height="381" src="[#M3_ROOT_URL#]/resource/image/sample/photo/samleL6.jpg" width="254" />', 'イベント-A10の説明', 'A会場', 2, '2015-10-21 07:00:00', ''),
(11, 'ja', 'イベント-A11', 'イベントサンプル　イベントサンプル　イベントサンプル　イベントサンプル<br />\r\nイベントサンプル　イベントサンプル　イベントサンプル　イベントサンプル<br />\r\n<br />\r\n<img alt="" src="[#M3_ROOT_URL#]/resource/image/sample/photo/sample2.jpg" style="width: 150px; height: 113px;" />', 'イベント-A11の説明', 'A会場|http://example.com/', 2, '2015-11-01 12:00:00', ''),
(12, 'ja', 'イベント-A12', 'イベントサンプル　イベントサンプル　イベントサンプル　イベントサンプル<br />\r\nイベントサンプル　イベントサンプル　イベントサンプル　イベントサンプル<br />\r\n<br />\r\n<img alt="" src="[#M3_ROOT_URL#]/resource/image/sample/photo/sample2.jpg" style="width: 150px; height: 113px;" />', 'イベント-A12の説明', 'A会場|http://example.com/', 2, '2015-12-25 12:00:00', ''),
(13, 'ja', 'イベント-A13', 'イベントサンプル　イベントサンプル　イベントサンプル　イベントサンプル<br />\r\nイベントサンプル　イベントサンプル　イベントサンプル　イベントサンプル<br />\r\n<br />\r\n<img alt="" src="[#M3_ROOT_URL#]/resource/image/sample/photo/sample2.jpg" style="width: 150px; height: 113px;" />', 'イベント-A13の説明', 'A会場|http://example.com/', 2, '2016-01-01 12:00:00', ''),
(14, 'ja', 'イベント-A14', 'イベントサンプル　イベントサンプル　イベントサンプル　イベントサンプル<br />\r\nイベントサンプル　イベントサンプル　イベントサンプル　イベントサンプル<br />\r\n<br />\r\n<img alt="" src="[#M3_ROOT_URL#]/resource/image/sample/photo/sample4.jpg" style="width: 150px; height: 113px;" />', 'イベント-A14の説明', 'A会場|http://example.com/', 2, '2016-02-19 14:00:00', '2,4'),
(15, 'ja', 'イベント-A15', 'イベントサンプル　イベントサンプル　イベントサンプル　イベントサンプル<br />\r\nイベントサンプル　イベントサンプル　イベントサンプル　イベントサンプル<br />\r\n<br />\r\n<img alt="" src="[#M3_ROOT_URL#]/resource/image/sample/photo/sample4.jpg" style="width: 150px; height: 113px;" />', 'イベント-A15の説明', 'A会場|http://example.com/', 2, '2016-03-19 13:00:00', '2,3'),
(16, 'ja', 'イベント-A16', 'イベントサンプル　イベントサンプル　イベントサンプル　イベントサンプル<br />\r\nイベントサンプル　イベントサンプル　イベントサンプル　イベントサンプル<br />\r\n<br />\r\n<img alt="" src="[#M3_ROOT_URL#]/resource/image/sample/photo/sample4.jpg" style="width: 150px; height: 113px;" />', 'イベント-A16の説明', 'A会場|http://example.com/', 2, '2016-04-19 13:00:00', '2,3,4'),
(17, 'ja', 'イベント-A17', 'イベントサンプル　イベントサンプル　イベントサンプル　イベントサンプル<br />\r\nイベントサンプル　イベントサンプル　イベントサンプル　イベントサンプル<br />\r\n<br />\r\n<img alt="" src="[#M3_ROOT_URL#]/resource/image/sample/photo/sample4.jpg" style="width: 150px; height: 113px;" />', 'イベント-A17の説明', 'A会場|http://example.com/', 2, '2016-05-19 12:00:00', '2,3,4'),
(18, 'ja', 'イベント-A18', 'イベントサンプル　イベントサンプル　イベントサンプル　イベントサンプル<br />\r\nイベントサンプル　イベントサンプル　イベントサンプル　イベントサンプル<br />\r\n<br />\r\n<img alt="" height="301" src="[#M3_ROOT_URL#]/resource/image/sample/photo/samleL4.jpg" width="402" />', 'イベント-A18の説明', 'A会場|http://example.com/', 3, '2016-06-25 23:00:00', '4,5'),
(19, 'ja', 'イベント-A19', 'イベントサンプル　イベントサンプル　イベントサンプル　イベントサンプル<br />\r\nイベントサンプル　イベントサンプル　イベントサンプル　イベントサンプル<br />\r\n<br />\r\n<img alt="" height="254" src="[#M3_ROOT_URL#]/resource/image/sample/photo/samleL3.jpg" width="339" />', 'イベント-A19の説明', 'A会場|http://example.com/', 2, '2017-03-31 16:00:00', '6'),
(20, 'ja', 'イベント-A20', 'イベントサンプル　イベントサンプル　イベントサンプル　イベントサンプル<br />\r\nイベントサンプル　イベントサンプル　イベントサンプル　イベントサンプル<br />\r\n<br />\r\n<img alt="" src="[#M3_ROOT_URL#]/resource/image/sample/photo/sample3.jpg" style="width: 150px; height: 113px;" />', 'イベント-A20の説明', 'A会場|http://example.com/', 2,  '2021-08-07 23:00:00', '');

-- イベントカテゴリー
TRUNCATE TABLE event_category;
INSERT INTO event_category
(ec_id, ec_language_id, ec_name) VALUES
(1,     'ja',           '●●●大会'),
(2,     'ja',           '△△△大会');
TRUNCATE TABLE event_entry_with_category;
INSERT INTO event_entry_with_category (ew_entry_serial, ew_index, ew_category_id) VALUES 
(1,  0, 1),
(2,  0, 2),
(3,  0, 1),
(3,  1, 2),
(5,  0, 1),
(5,  1, 2),
(6,  0, 1),
(7,  0, 1),
(8,  0, 1);

-- コンテンツ
TRUNCATE TABLE content;
INSERT INTO content
(cn_type, cn_id, cn_language_id, cn_name,              cn_html,                        cn_default, cn_key, cn_create_user_id, cn_create_dt) VALUES 
('', 1,     'ja',           '運営会社', '<table class="table">\r\n	<tbody>\r\n		<tr>\r\n			<th>社　名</th>\r\n			<td>\r\n			<p>株式会社イベント</p>\r\n			</td>\r\n		</tr>\r\n		<tr>\r\n			<th>所在地</th>\r\n			<td>\r\n			<p></p>\r\n			</td>\r\n		</tr>\r\n		<tr>\r\n			<th>設　立</th>\r\n			<td>\r\n			<p></p>\r\n			</td>\r\n		</tr>\r\n		<tr>\r\n			<th>代表者</th>\r\n			<td>\r\n			<p></p>\r\n			</td>\r\n		</tr>\r\n		<tr>\r\n			<th>事業内容</th>\r\n			<td>\r\n			<p>●イベントサイトの運営</p>\r\n			</td>\r\n		</tr>\r\n				</tbody>\r\n</table><br /><div class="googlemaps" id="gmap201472916954" style="width:100%;height:300px;display:none;margin:0 auto;">\r\n<script type="text/javascript">\r\n//<![CDATA[\r\n// Magic3 googlemaps v1.00 mapid:201472916954\r\n$(function(){\r\n	var mapStyle = [{"featureType":"water","stylers":[{"visibility":"on"},{"color":"#acbcc9"}]},{"featureType":"landscape","stylers":[{"color":"#f2e5d4"}]},{"featureType":"road.highway","elementType":"geometry","stylers":[{"color":"#c5c6c6"}]},{"featureType":"road.arterial","elementType":"geometry","stylers":[{"color":"#e4d7c6"}]},{"featureType":"road.local","elementType":"geometry","stylers":[{"color":"#fbfaf7"}]},{"featureType":"poi.park","elementType":"geometry","stylers":[{"color":"#c5dac6"}]},{"featureType":"administrative","stylers":[{"visibility":"on"},{"lightness":33}]},{"featureType":"road"},{"featureType":"poi.park","elementType":"labels","stylers":[{"visibility":"on"},{"lightness":20}]},{},{"featureType":"road","stylers":[{"lightness":20}]}];\r\n	var allMapTypes = [	"original",\r\n						google.maps.MapTypeId.SATELLITE,\r\n						google.maps.MapTypeId.HYBRID,\r\n						google.maps.MapTypeId.TERRAIN	];\r\n	var opts = {	mapTypeControlOptions: {	mapTypeIds: allMapTypes } };\r\n	var mapDiv = document.getElementById("gmap201472916954");\r\n	var map = new google.maps.Map(mapDiv, opts);\r\n	var originalMapType = new google.maps.StyledMapType(mapStyle, { name: "地図" });\r\n	map.mapTypes.set("original", originalMapType);\r\n	map.setMapTypeId("original");\r\n	map.setMapTypeId(allMapTypes[0]);\r\n	map.setCenter(new google.maps.LatLng(34.69116, 135.52506));\r\n	map.setZoom(11);\r\n	mapDiv.style.display = "";\r\n	m3GooglemapsAddMarkers(map, [{lat:34.68732, lon:135.5262, text:"場所はここ"}]);\r\n});\r\n//]]></script>\r\n</div>\r\n',              false, '',                0, now()),
('', 2,     'ja',           'サイト説明',   '<p>このサイトはイベントを紹介するサンプルサイトです。<br />\r\n&nbsp;</p>\r\n',              false, '',                0, now());

-- 新着情報
TRUNCATE TABLE news;
INSERT INTO news 
(nw_id, nw_type, nw_server_id, nw_device_type, nw_regist_dt, nw_name, nw_content_type, nw_content_id, nw_url, nw_link, nw_content_dt, nw_message, nw_site_name, nw_site_link, nw_site_url, nw_summary, nw_mark, nw_visible, nw_user_limited, nw_create_user_id, nw_create_dt) VALUES
(1, '', '', 0, '2015-04-19 23:39:20', '', 'event', '2', '[#M3_ROOT_URL#]/index.php?eventid=2', '', '0000-00-00 00:00:00', '「[#TITLE#]」を追加しました', '', '', '', '', 0, true, false, 1, '2015-03-24 05:46:24');
