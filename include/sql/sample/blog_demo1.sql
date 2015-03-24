-- *
-- * データ登録スクリプト「ブログサイトデモ1」
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
-- [ブログサイトデモ1]
-- ##### 初心者の初回インストール時に最適 #####
-- ブログ主軸型サイト。
-- 機能は、ブログ、汎用コンテンツ、バナー管理。

-- システム設定
UPDATE _system_config SET sc_value = 'art41_sample2' WHERE sc_id = 'default_template';

-- 管理画面ページデータ(デフォルトを変更)
UPDATE _page_id SET pg_default_sub_id = 'blog' WHERE pg_id = 'index' AND pg_type = 0;
-- スマートフォン,携帯のアクセスポイントを隠す
UPDATE _page_id SET pg_active = true WHERE pg_id = 's_index' AND pg_type = 0;
UPDATE _page_id SET pg_active = true WHERE pg_id = 'm_index' AND pg_type = 0;

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
(201,   200,          0,        'admin_menu', 'userlist',        0,               '',       'ユーザ管理',           'ユーザ管理',           'ログイン可能なユーザを管理します。'),
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
(pd_id,   pd_sub_id, pd_position_id, pd_index, pd_widget_id,         pd_config_id, pd_config_name,       pd_title,   pd_menu_id,  pd_title_visible, pd_update_dt) VALUES
('index', '',        'user3',        2,        'default_menu',       1,            'メインメニュー設定', '',         'main_menu', true,             now()),
('index', '',        'left',         4,        'templateChanger',    0,            '',                   '',         '',          true,             now()),
('index', '',        'right',        5,        'blog_category_menu', 0,            '',                   '',         '',          true,             now()),
('index', '',        'right',        7,        'blog_archive_menu',  0,            '',                   '',         '',          true,             now()),
('index', '',        'right',        9,        'default_login_box',  0,            '',                   '',         '',          true,             now()),
('index', '',        'main',         3,        'banner3',            3,            '',                   '',         '',          false,            now()),
('index', 'content', 'main',         6,        'default_content',    0,            '',                   '',         '',          false,            now()),
('index', 'blog',    'main',         3,        'whatsnew',           1,            '',                   '新着情報', '',          true,            now()),
('index', 'blog',    'main',         5,        'blog_main',          0,            '',                   'ブログ',   '',          true,            now()),
('index', 'blog',    'left',         7,        'blog_new_box',       0,            '',                   '',         '',          true,             now()),
('index', 'blog',    'left',         9,        'blog_calendar_box',  0,            '',                   '',         '',          true,             now()),
('index', 'blog',    'left',         11,       'blog_search_box',    0,            '',                   '',         '',          true,             now()),
('index', 'search',  'main',         5,        'custom_search',      1,            '',                   '',         '',          false,            now()),
('index', 'contact', 'main',         5,        'contactus',          0,            '',                   '',         '',          false,            now());

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
DELETE FROM _widget_param WHERE wp_id = 'whatsnew';
INSERT INTO _widget_param
(wp_id,          wp_config_id, wp_param,                                                                                                wp_create_dt) VALUES 
('whatsnew', 1,            'O:8:"stdClass":3:{s:4:"name";s:16:"名称未設定1";s:9:"itemCount";s:2:"10";s:6:"useRss";i:1;}', now());

-- ブログ
TRUNCATE TABLE blog_entry;
INSERT INTO blog_entry (be_id, be_language_id, be_history_index, be_name, be_html, be_status, be_regist_user_id, be_regist_dt) VALUES 
(1, 'ja', 0, '富士山登山', '<p>富士山に初めて登りました。 <br />\r\n天候がよく、実にすばらしい登山日和でした。</p>\r\n<p><br />\r\n高地の空気になれるため、少しずつ上っていきます。<br />\r\n下界がどんどん遠ざかっていきます。</p>\r\n<p><img width="420" height="280" src="[#M3_ROOT_URL#]/resource/image/sample/blog/fuji1.jpg" alt="" /></p>\r\n<p>山頂には神社があります。</p>\r\n<p><img width="267" height="400" src="[#M3_ROOT_URL#]/resource/image/sample/blog/fuji2.jpg" alt="" /></p>\r\n<p>&nbsp;</p>\r\n<p>下山はひたすら砂地を下っていきます。</p>\r\n<p><img width="267" height="400" src="[#M3_ROOT_URL#]/resource/image/sample/blog/fuji3.jpg" alt="" /></p>', 2, 1, '2007-08-28 19:56:04'),
(2, 'ja', 0, '多摩川の夕日', '<p>8月も終わりになりました。まだまだ暑いですが、夜は少し涼しく、ピークは過ぎた感じです。<br />\r\n部屋から夕日が沈んでゆくのが見えます。</p>\r\n<p><img width="410" height="272" src="[#M3_ROOT_URL#]/resource/image/sample/blog/tama1.jpg" alt="" /></p>', 2, 1, '2007-08-29 13:39:20');

TRUNCATE TABLE blog_category;
INSERT INTO blog_category (bc_id, bc_language_id, bc_name, bc_sort_order) VALUES 
(1, 'ja', '登山', 1),
(2, 'ja', '風景', 2);

TRUNCATE TABLE blog_entry_with_category;
INSERT INTO blog_entry_with_category (bw_entry_serial, bw_index, bw_category_id) VALUES 
(1,  0, 1),
(2,  0, 2);

-- 新着情報
TRUNCATE TABLE news;
INSERT INTO news 
(nw_id, nw_history_index, nw_type, nw_server_id, nw_device_type, nw_regist_dt, nw_name, nw_content_type, nw_content_id, nw_url, nw_link, nw_content_dt, nw_message, nw_site_name, nw_site_link, nw_site_url, nw_summary, nw_mark, nw_visible, nw_user_limited, nw_create_user_id, nw_create_dt, nw_update_user_id, nw_update_dt, nw_deleted) VALUES
(1, 0, '', '', 0, '2007-08-29 13:39:20', '', 'blog', '2', '[#M3_ROOT_URL#]/index.php?entryid=2', '', '0000-00-00 00:00:00', '「[#TITLE#]」を追加しました', '', '', '', '', 0, 1, 0, 1, '2015-03-24 05:46:24', 0, '0000-00-00 00:00:00', 0);

-- バナー定義
TRUNCATE TABLE bn_def;
INSERT INTO bn_def
(bd_id, bd_item_id,                      bd_name,           bd_disp_type, bd_disp_direction, bd_disp_item_count, bd_disp_align) VALUES 
(1,     '1,2,3,4,5,6',                   'サンプルバナー1', 0,            0,                 1,                  3),
(2,     '11,12,13,14,15,16,17,18,19',    'サンプルバナー2', 0,            0,                 2,                  0),
(3,     '7,8,9,10,11,12,13,14',          'サンプルバナー3', 1,            1,                 2,                  0);

TRUNCATE TABLE bn_item;
INSERT INTO bn_item (bi_id, bi_name,    bi_image_url, bi_html) VALUES 
(1,     'DVD',     '[#M3_ROOT_URL#]/resource/image/sample/banner/sample1.gif', '[#ITEM#]'),
(2,     'レンタル', '[#M3_ROOT_URL#]/resource/image/sample/banner/sample2.gif', '[#ITEM#]'),
(3,     '美容',     '[#M3_ROOT_URL#]/resource/image/sample/banner/sample3.gif', '[#ITEM#]'),
(4,     '夏物',       '[#M3_ROOT_URL#]/resource/image/sample/banner/sample4.gif', '[#ITEM#]'),
(5,     '視力',     '[#M3_ROOT_URL#]/resource/image/sample/banner/sample5.gif', '[#ITEM#]'),
(6,     '朝顔',     '[#M3_ROOT_URL#]/resource/image/sample/banner/sample6.gif', '[#ITEM#]'),
(7,     '夏祭り',     '[#M3_ROOT_URL#]/resource/image/sample/banner/sample7.gif', '[#ITEM#]'),
(8,     'ＰＣ',     '[#M3_ROOT_URL#]/resource/image/sample/banner/sample8.gif', '[#ITEM#]'),
(9,     'ジンギスカン',     '[#M3_ROOT_URL#]/resource/image/sample/banner/sample9.gif', '[#ITEM#]'),
(10,    'クッキー',     '[#M3_ROOT_URL#]/resource/image/sample/banner/sample10.gif', '[#ITEM#]'),
(11,    '飲み会',     '[#M3_ROOT_URL#]/resource/image/sample/banner/sample11.gif', '[#ITEM#]'),
(12,    'コスメ',     '[#M3_ROOT_URL#]/resource/image/sample/banner/sample12.gif', '[#ITEM#]'),
(13,    'タブレット',     '[#M3_ROOT_URL#]/resource/image/sample/banner/sample13.gif', '[#ITEM#]'),
(14,    'ジュエリー',     '[#M3_ROOT_URL#]/resource/image/sample/banner/sample14.gif', '[#ITEM#]'),
(15,    'パン',     '[#M3_ROOT_URL#]/resource/image/sample/banner/sample15.gif', '[#ITEM#]'),
(16,    'ハロウィーン',     '[#M3_ROOT_URL#]/resource/image/sample/banner/sample16.gif', '[#ITEM#]'),
(17,    'ラケット',     '[#M3_ROOT_URL#]/resource/image/sample/banner/sample17.gif', '[#ITEM#]'),
(18,    'きのこ',     '[#M3_ROOT_URL#]/resource/image/sample/banner/sample18.gif', '[#ITEM#]'),
(19,    'すいか',     '[#M3_ROOT_URL#]/resource/image/sample/banner/sample19.gif', '[#ITEM#]');

-- コンテンツ
TRUNCATE TABLE content;
INSERT INTO content (cn_type, cn_id, cn_language_id, cn_name,              cn_description,         cn_html,                        cn_default, cn_key, cn_create_user_id, cn_create_dt) VALUES 
('', 1,     'ja',           '会社情報',   '会社情報', '<div class="ec_common">\r\n<table>\r\n    <tbody>\r\n        <tr>\r\n            <th>社　名</th>\r\n            <td>\r\n            <p>&nbsp;</p>\r\n            <p>&nbsp;</p>\r\n            <p>&nbsp;</p>\r\n            </td>\r\n        </tr>\r\n        <tr>\r\n            <th>所在地</th>\r\n            <td>\r\n            <p>&nbsp;</p>\r\n            <p>&nbsp;</p>\r\n            <p>&nbsp;</p>\r\n            </td>\r\n        </tr>\r\n        <tr>\r\n            <th>設　立</th>\r\n            <td>\r\n            <p>&nbsp;</p>\r\n            <p>&nbsp;</p>\r\n            <p>&nbsp;</p>\r\n            </td>\r\n        </tr>\r\n        <tr>\r\n            <th>代表者</th>\r\n            <td>\r\n            <p>&nbsp;</p>\r\n            <p>&nbsp;</p>\r\n            <p>&nbsp;</p>\r\n            </td>\r\n        </tr>\r\n        <tr>\r\n            <th>事業内容</th>\r\n            <td>\r\n            <p>&nbsp;</p>\r\n            <p>●商品Ａの製造<br />\r\n            ●商品Ｂの卸売<br />\r\n            ●商品Ｃの販売</p>\r\n            <p>&nbsp;</p>\r\n            </td>\r\n        </tr>\r\n        <tr>\r\n            <th>主要取引銀行</th>\r\n            <td>\r\n            <p>&nbsp;</p>\r\n            <p>&nbsp;</p>\r\n            <p>&nbsp;</p>\r\n            </td>\r\n        </tr>\r\n        <tr>\r\n            <th>主要取引先</th>\r\n            <td>\r\n            <p>&nbsp;</p>\r\n            <p>■ＸＸＸ株式会社<br />\r\n            ■ＹＹＹ株式会社<br />\r\n            ■株式会社　ＺＺＺ</p>\r\n            <p>&nbsp;</p>\r\n            <p>&nbsp;</p>\r\n            </td>\r\n        </tr>\r\n    </tbody>\r\n</table>\r\n</div>\r\n',              false, '',                0, now());
