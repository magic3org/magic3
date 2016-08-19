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
-- [イベントサイト初期化]
-- イベント情報主軸型サイト。
-- 主な機能は、イベント情報、カレンダー。
-- 初期インストールデータは必要最小限のみ

-- システム設定
UPDATE _system_config SET sc_value = 'art42_sample3' WHERE sc_id = 'default_template';

-- 管理画面ページデータ(デフォルトを変更)
UPDATE _page_id SET pg_default_sub_id = 'front' WHERE pg_id = 'index' AND pg_type = 0;
-- スマートフォン,携帯のアクセスポイントを隠す
UPDATE _page_id SET pg_active = false WHERE pg_id = 's_index' AND pg_type = 0;
UPDATE _page_id SET pg_active = false WHERE pg_id = 'm_index' AND pg_type = 0;
-- 必要なページのみ表示
DELETE FROM _page_id WHERE pg_type = 1;
INSERT INTO _page_id 
(pg_id,          pg_type,      pg_name,                            pg_description,                       pg_priority, pg_active, pg_visible, pg_editable, pg_available) VALUES
('front',        1,            'トップ画面',                       'トップ画面用',                       0,           true,      true,       true,        true),
('content',      1,            'コンテンツ',                       'コンテンツ画面用',                   1,           true,      true,       false,       true),
('shop',         1,            'ECショップ',                       'ECショップ画面用',                   2,           true,      true,       true,        false),
('shop_safe',    1,            'ECショップ(セキュリティ保護)',     'ECショップ(セキュリティ保護)画面用', 3,           true,      true,       true,        false),
('bbs',          1,            '掲示板',                           '掲示板画面用',                       4,           true,      true,       true,        false),
('blog',         1,            'ブログ',                           'ブログ画面用',                       5,           true,      true,       true,        true),
('wiki',         1,            'Wiki',                             'Wiki画面用',                         6,           true,      true,       true,        false),
('calendar',     1,            'カレンダー',                       'カレンダー画面用',                   7,           true,      true,       true,        true),
('event',        1,            'イベント情報',                     'イベント情報画面用',                 8,           true,      true,       true,        true),
('photo',        1,            'フォトギャラリー',                 'フォトギャラリー画面用',             9,           true,      true,       true,        false),
('contact',      1,            'お問い合わせ',                     'お問い合わせ画面用',                 10,          true,      true,       true,        true),
('contact2',     1,            'お問い合わせ2',                    'お問い合わせ画面用',                 11,          true,      true,       true,        false),
('reguser',      1,            'ユーザ登録',                       'ユーザ登録画面用',                   12,          true,      true,       true,        false),
('reserve',      1,            '予約',                             '予約画面用',                         19,          true,      true,       true,        false),
('member',       1,            '会員',                             '会員画面用',                         20,          true,      true,       true,        true),
('evententry',   1,            'イベント予約',                     'イベント予約画面用',                 21,          true,      true,       true,        true),
('search',       1,            '検索',                             '検索画面用',                         22,          true,      true,       true,        true),
('user',         1,            'ユーザコンテンツ',                 'ユーザ作成コンテンツ用',             50,          true,      true,       true,        false),
('deploy',       1,            '[ウィジェット有効化用]',             'ウィジェット有効化用',               100,         true,      false,      true,        false),
('test',         1,            '[ウィジェットテスト用]',             'ウィジェットテスト用非公開画面',     101,         false,     true,       true,        false);

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

-- イベント
TRUNCATE TABLE event_entry;

-- イベントカテゴリー
TRUNCATE TABLE event_category;
INSERT INTO event_category
(ec_id, ec_language_id, ec_name) VALUES
(1,     'ja',           '●●●大会');

TRUNCATE TABLE event_entry_with_category;

-- コンテンツ
TRUNCATE TABLE content;
INSERT INTO content
(cn_type, cn_id, cn_language_id, cn_name,              cn_html,                        cn_default, cn_key, cn_create_user_id, cn_create_dt) VALUES 
('', 1,     'ja',           '運営会社', '<table class="table">\r\n	<tbody>\r\n		<tr>\r\n			<th>社　名</th>\r\n			<td>\r\n			<p>株式会社イベント</p>\r\n			</td>\r\n		</tr>\r\n		<tr>\r\n			<th>所在地</th>\r\n			<td>\r\n			<p></p>\r\n			</td>\r\n		</tr>\r\n		<tr>\r\n			<th>設　立</th>\r\n			<td>\r\n			<p></p>\r\n			</td>\r\n		</tr>\r\n		<tr>\r\n			<th>代表者</th>\r\n			<td>\r\n			<p></p>\r\n			</td>\r\n		</tr>\r\n		<tr>\r\n			<th>事業内容</th>\r\n			<td>\r\n			<p>●イベントサイトの運営</p>\r\n			</td>\r\n		</tr>\r\n				</tbody>\r\n</table><br /><div class="googlemaps" id="gmap201472916954" style="width:100%;height:300px;display:none;margin:0 auto;">\r\n<script type="text/javascript">\r\n//<![CDATA[\r\n// Magic3 googlemaps v1.00 mapid:201472916954\r\n$(function(){\r\n	var mapStyle = [{"featureType":"water","stylers":[{"visibility":"on"},{"color":"#acbcc9"}]},{"featureType":"landscape","stylers":[{"color":"#f2e5d4"}]},{"featureType":"road.highway","elementType":"geometry","stylers":[{"color":"#c5c6c6"}]},{"featureType":"road.arterial","elementType":"geometry","stylers":[{"color":"#e4d7c6"}]},{"featureType":"road.local","elementType":"geometry","stylers":[{"color":"#fbfaf7"}]},{"featureType":"poi.park","elementType":"geometry","stylers":[{"color":"#c5dac6"}]},{"featureType":"administrative","stylers":[{"visibility":"on"},{"lightness":33}]},{"featureType":"road"},{"featureType":"poi.park","elementType":"labels","stylers":[{"visibility":"on"},{"lightness":20}]},{},{"featureType":"road","stylers":[{"lightness":20}]}];\r\n	var allMapTypes = [	"original",\r\n						google.maps.MapTypeId.SATELLITE,\r\n						google.maps.MapTypeId.HYBRID,\r\n						google.maps.MapTypeId.TERRAIN	];\r\n	var opts = {	mapTypeControlOptions: {	mapTypeIds: allMapTypes } };\r\n	var mapDiv = document.getElementById("gmap201472916954");\r\n	var map = new google.maps.Map(mapDiv, opts);\r\n	var originalMapType = new google.maps.StyledMapType(mapStyle, { name: "地図" });\r\n	map.mapTypes.set("original", originalMapType);\r\n	map.setMapTypeId("original");\r\n	map.setMapTypeId(allMapTypes[0]);\r\n	map.setCenter(new google.maps.LatLng(34.69116, 135.52506));\r\n	map.setZoom(11);\r\n	mapDiv.style.display = "";\r\n	m3GooglemapsAddMarkers(map, [{lat:34.68732, lon:135.5262, text:"場所はここ"}]);\r\n});\r\n//]]></script>\r\n</div>\r\n',              false, '',                0, now()),
('', 2,     'ja',           'サイト説明',   '<p>このサイトはイベントを紹介するサンプルサイトです。<br />\r\n&nbsp;</p>\r\n',              false, '',                0, now());

-- 新着情報
TRUNCATE TABLE news;
