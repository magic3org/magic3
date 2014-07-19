-- *
-- * データ登録スクリプト「汎用管理画面デモ1」
-- *
-- * PHP versions 5
-- *
-- * LICENSE: This source file is licensed under the terms of the GNU General Public License.
-- *
-- * @package    Magic3 Framework
-- * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
-- * @copyright  Copyright 2006-2013 Magic3 Project.
-- * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
-- * @version    SVN: $Id: simple_demo1.sql 5912 2013-04-07 07:42:11Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- [汎用管理画面デモ1]
-- 特定の機能に特化しない汎用型サイト。
-- 主要機能は、コンテンツ管理、ブログ。

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
UPDATE _page_id SET pg_default_sub_id = 'content' WHERE pg_id = 'index' AND pg_type = 0;

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
(pd_id,   pd_sub_id, pd_position_id, pd_index, pd_widget_id,       pd_config_id, pd_config_name,       pd_menu_id,  pd_title_visible, pd_update_dt) VALUES
('index', '',        'user3',        2,        'default_menu',     1,            'メインメニュー設定', 'main_menu', true,             now()),
('index', '',        'left',         8,        'templateChanger',  0,            '',                   '',          true,             now()),
('index', 'content', 'left',         10,       'access_count',     0,            '',                   '',          true,        now()),
('index', 'content', 'main',         6,       'default_content',   0,            '',                   '',          false,        now()),
('index', 'blog',    'main',         1,       'banner3',           3,            '',                   '',          false,        now()),
('index', 'blog',    'main',         3,        'blog_main',        0,                  '',             '',          false,        now()),
('index', 'blog',    'left',         5,        'blog_new_box',        0,              '',              '',          true,        now()),
('index', 'blog',    'left',         7,        'blog_calendar_box',        0,          '',              '',          true,        now()),
('index', 'blog',    'left',         9,        'blog_search_box',        0,            '',              '',          true,        now()),
('index', 'blog',    'left',         12,       'qrcode',          0,                   '',              '',          true,        now()),
('index', 'blog',    'left',         15,       'blog_category_menu',          0,       '',              '',          true,        now()),
('index', 'blog',    'left',         16,       'blog_archive_menu',          0,        '',              '',          true,        now()),
('index', 'bbs',    'main',         3,        'bbs_2ch_main',        0,                 '',              '',          false,        now()),
('index', 'photo',    'main',         3,        'photo_main',        0,                 '',              '',          false,        now()),
('index', 'wiki',    'main',         3,        'wiki_main',        0,                 '',              '',          false,        now()),
('index', 'search',  'main',         5,        'custom_search',      1,            '',                   '',          false,            now()),
('index', 'contact', 'left',         6,        'joomla_clock',        0,              '',              '',          true,        now()),
('index', 'contact', 'main',         3,        'contactus',        0,                '',              '',          false,        now());

-- 新メニュー対応
TRUNCATE TABLE _menu_def;
INSERT INTO _menu_def
(md_id, md_index, md_menu_id,  md_name,            md_link_url,                                         md_update_dt) VALUES
(1,     1,        'main_menu', 'ホーム',           '[#M3_ROOT_URL#]/',                                   now()),
(2,     2,        'main_menu', 'コンテンツの作成', '[#M3_ROOT_URL#]/index.php?contentid=5', now()),
(3,     3,        'main_menu', 'ブログ',           '[#M3_ROOT_URL#]/index.php?sub=blog', now()),
(4,     4,        'main_menu', '掲示板',           '[#M3_ROOT_URL#]/index.php?sub=bbs', now()),
(5,     5,        'main_menu', 'wiki',             '[#M3_ROOT_URL#]/index.php?sub=wiki', now()),
(6,     6,        'main_menu', 'フォトギャラリー', '[#M3_ROOT_URL#]/index.php?sub=photo', now()),
(7,     7,        'main_menu', '会社情報',         '[#M3_ROOT_URL#]/index.php?contentid=6', now()),
(8,     8,        'main_menu', 'お問い合わせ',     '[#M3_ROOT_URL#]/index.php?sub=contact',             now());

-- ウィジェットパラメータ
DELETE FROM _widget_param WHERE wp_id = 'default_menu';
INSERT INTO _widget_param
(wp_id,          wp_config_id, wp_param,                                                                                                wp_create_dt) VALUES 
('default_menu', 1,            'O:\8:"stdClass":3:{s:6:"menuId";s:9:"main_menu";s:4:"name";s:27:"メインメニュー設定";s:9:"limitUser";i:0;}', now());
DELETE FROM _widget_param WHERE wp_id = 'custom_search';
INSERT INTO _widget_param
(wp_id,          wp_config_id, wp_param,                                                                                                wp_create_dt) VALUES 
('custom_search', 1,            'O:8:"stdClass":10:{s:4:"name";s:16:"名称未設定1";s:11:"resultCount";s:2:"20";s:14:"searchTemplate";s:241:"<input type="text" maxlength="40" size="10" id="custom_search_1_text" /> <input type="button" value="検索" class="button" id="custom_search_1_button" /> <input type="button" value="リセット" class="button" id="custom_search_1_reset" />";s:12:"searchTextId";s:20:"custom_search_1_text";s:14:"searchButtonId";s:22:"custom_search_1_button";s:13:"searchResetId";s:21:"custom_search_1_reset";s:15:"isTargetContent";i:1;s:12:"isTargetUser";i:1;s:12:"isTargetBlog";i:1;s:9:"fieldInfo";a:0:{}}', now());

TRUNCATE TABLE `content`;
INSERT INTO `content` (`cn_id`, `cn_language_id`, `cn_name`,              `cn_description`,         `cn_html`,                        `cn_default`, `cn_create_user_id`, `cn_create_dt`) VALUES 
(1,     'ja',           'ようこそ',           'Magic3の説明', '<p>&nbsp;</p><p><font size="3">Magic3は、ユーザが自由な表現でWebサイトを「<strong>創る</strong>」ためのCMS(コンテンツマネージメントシステム)です。<br>Eコマースやブログ、BBS(掲示板)など様々な機能をもったサイトが構築できます。</font></p><p><img src="[#M3_ROOT_URL#]/images/himawari.jpg" alt="" /></p>
<p><font size="3">コンポーネント部品である「Widget」(ウィジェット)を、画面に並べていくだけの簡単な操作でWebサイトが自由自在に作れます。<br />HTMLやプログラミングをまったく知らなくてもWebサイトが作れる、夢のWebアプリケーションです。</font></p>', true,       0, now()),
(2,     'ja',           '春の花々',           '春の花々', '<p><font color="#ff0000">春</font>になりました。水芭蕉が咲いています。</p><p><img src="[#M3_ROOT_URL#]/images/basho.jpg" alt="" /></p><p>桜も咲いています。</p><p><img src="[#M3_ROOT_URL#]/images/sakura.jpg" alt="" /></p>',              false,                 0, now()),
(3,     'ja',           'テンプレート説明',   'デザインテンプレートの説明', '<p>Magic3では、Webサイトの見栄えを決定するデザインテンプレートが完全に独立しています。<br />サイトのコンテンツ(内容)を一切変更することなく、デザインテンプレートを切り替えることによって、<br />一瞬にしてサイトイメージが変更できます。</p><p>変更してみましょう。<br />画像の赤枠のウィジェット(部品)で変更します。<br />メニューからデザインを選択し、「選択」ボタンを押します。</p><p>&nbsp;</p><p><img src="[#M3_ROOT_URL#]/images/doc/tempchange.gif" alt="" /></p>',              false,                 0, now()),
(4,     'ja',           'ウィジェット説明',   'ウィジェットの説明', '<p>Magic3では、ウィジェットと呼ぶ部品を画面に並べることによって、Webサイトを構築します。<br />写真の赤枠が、それぞれ別々の機能を持つウィジェットです。<br />ウィジェットは、単体で独立して動作するプラグインコンポーネントです。<br />さまざまなウィジェットを付けたり、はずしたりすることによって、Webサイトの機能を自由に変更できます。</p><p>&nbsp;</p><p><img src="[#M3_ROOT_URL#]/images/doc/widget.gif" alt="" /></p><p>&nbsp;</p><p>画面にウィジェットを対応させる管理画面です。(デモサイトでは管理者のログインはできません。)<br />管理画面自体もウィジェットで作成されています。</p><p><img alt="" src="[#M3_ROOT_URL#]/images/doc/pagedef.gif" /></p><p>&nbsp;</p><p>ウィジェット一覧です。詳細ボタンからウィジェットごとの個別の設定を行います</p>
<p><img width="521" height="388" alt="" src="[#M3_ROOT_URL#]/images/doc/widgetlist.gif" /></p>',              false,                 0, now()),
(5,     'ja',           'コンテンツ作成',   'コンテンツ作成', '<p>「デフォルトコンテンツビュー」ウィジェットでHTMLコンテンツを作成、管理します。<br />ＨＴＭＬエディタが付属しているので、HTMLタグを直接編集するよりも容易にHTMLが作成できます。</p><p>&nbsp;</p><p><img alt="" src="[#M3_ROOT_URL#]/images/doc/editcontent.gif" /></p><p>&nbsp;</p>',              false,                 0, now()),
(6,     'ja',           '会社情報',   '会社情報', '<div class="ec_common">\r\n<table>\r\n    <tbody>\r\n        <tr>\r\n            <th>社　名</th>\r\n            <td>\r\n            <p>&nbsp;</p>\r\n            <p>[#CUSTOM_KEY_001#]</p>\r\n            <p>&nbsp;</p>\r\n            </td>\r\n        </tr>\r\n        <tr>\r\n            <th>所在地</th>\r\n            <td>\r\n            <p>&nbsp;</p>\r\n            <p>[#CUSTOM_KEY_002#]</p>\r\n            <p>&nbsp;</p>\r\n            </td>\r\n        </tr>\r\n        <tr>\r\n            <th>設　立</th>\r\n            <td>\r\n            <p>&nbsp;</p>\r\n            <p>[#CUSTOM_KEY_003#]</p>\r\n            <p>&nbsp;</p>\r\n            </td>\r\n        </tr>\r\n        <tr>\r\n            <th>代表者</th>\r\n            <td>\r\n            <p>&nbsp;</p>\r\n            <p>[#CUSTOM_KEY_004#]</p>\r\n            <p>&nbsp;</p>\r\n            </td>\r\n        </tr>\r\n        <tr>\r\n            <th>事業内容</th>\r\n            <td>\r\n            <p>&nbsp;</p>\r\n            <p>[#CUSTOM_KEY_005#]●商品Ａの製造<br />\r\n            ●商品Ｂの卸売<br />\r\n            ●商品Ｃの販売</p>\r\n            <p>&nbsp;</p>\r\n            </td>\r\n        </tr>\r\n        <tr>\r\n            <th>主要取引銀行</th>\r\n            <td>\r\n            <p>&nbsp;</p>\r\n            <p>[#CUSTOM_KEY_006#]</p>\r\n            <p>&nbsp;</p>\r\n            </td>\r\n        </tr>\r\n        <tr>\r\n            <th>主要取引先</th>\r\n            <td>\r\n            <p>&nbsp;</p>\r\n            <p>[#CUSTOM_KEY_007#]■ＸＸＸ株式会社<br />\r\n            ■ＹＹＹ株式会社<br />\r\n            ■株式会社　ＺＺＺ</p>\r\n            <p>&nbsp;</p>\r\n            <p>&nbsp;</p>\r\n            </td>\r\n        </tr>\r\n    </tbody>\r\n</table>\r\n</div>\r\n',              false, 0, now());

TRUNCATE TABLE `blog_entry`;
INSERT INTO `blog_entry` (`be_id`, `be_language_id`, `be_history_index`, `be_name`, `be_html`, `be_status`, `be_regist_user_id`, `be_regist_dt`) VALUES 
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

TRUNCATE TABLE `bbs_category`;
INSERT INTO `bbs_category` (`sr_id`, `sr_language_id`, `sr_history_index`, `sr_name`, `sr_sort_order`) VALUES 
(1, 'ja', 0, 'Magic3についての話題', 1),
(2, 'ja', 0, '投稿テスト用', 2);

DELETE FROM bbs_group WHERE sg_id = 2;
INSERT INTO `bbs_group` (`sg_id`, `sg_language_id`, `sg_history_index`, `sg_name`, `sg_sort_order`, `sg_editable`) VALUES 
(2, 'ja', 0, '一般ユーザ', 2, true);

TRUNCATE TABLE `bbs_group_access`;
INSERT INTO `bbs_group_access` (`so_group_id`, `so_category_id`, `so_read`, `so_write`) VALUES 
(1, 1, true, false),
(1, 2, true, true),
(2, 1, true, true),
(2, 2, true, true);

TRUNCATE TABLE `bbs_thread`;
INSERT INTO `bbs_thread` (`se_id`, `se_language_id`, `se_history_index`, `se_name`, `se_html`, `se_status`, `se_level`, `se_max_sort_order`, `se_root_id`, `se_parent_id`, `se_sort_order`, `se_category_id`, `se_regist_user_id`, `se_regist_dt`) VALUES 
(1, 'ja', 0, 'バージョン1.0リリース', 'Magic3バージョン1.0をリリースしました。\r\nEコマース、ブログ、掲示板(BBS)の機能が使用できます。', 2, 0, 1, 1, 0, 0, 1, 1, '2007-09-25 14:13:18'),
(2, 'ja', 0, 'Re: バージョン1.0リリース', 'Magic3はオープンソースです。\r\n無料で使用でき、ソースコードも利用できます。\r\nGPLライセンスです。', 2, 1, 0, 1, 1, 1, 1, 1, '2007-09-25 14:16:48'),
(3, 'ja', 0, 'テスト用カテゴリー', 'このカテゴリーはテスト用です。', 2, 0, 0, 3, 0, 0, 2, 1, '2007-09-25 14:17:34');

-- バナー定義
TRUNCATE TABLE bn_def;
INSERT INTO bn_def
(bd_id, bd_item_id,                      bd_name,           bd_disp_type, bd_disp_direction, bd_disp_item_count, bd_disp_align) VALUES 
(1,     '1,2,3,4,5,6',                   'サンプルバナー1', 0,            0,                 1,                  3),
(2,     '11,12,13,14,15,16,17,18,19',    'サンプルバナー2', 0,            0,                 2,                  0),
(3,     '7,8,9,10,11,12,13,14',          'サンプルバナー3', 1,            1,                 2,                  0);

TRUNCATE TABLE bn_item;
INSERT INTO bn_item (bi_id, bi_name,    bi_image_url) VALUES 
(1,     'DVD',     '[#M3_ROOT_URL#]/resource/image/sample/banner/sample1.gif'),
(2,     'レンタル', '[#M3_ROOT_URL#]/resource/image/sample/banner/sample2.gif'),
(3,     '美容',     '[#M3_ROOT_URL#]/resource/image/sample/banner/sample3.gif'),
(4,     '夏物',       '[#M3_ROOT_URL#]/resource/image/sample/banner/sample4.gif'),
(5,     '視力',     '[#M3_ROOT_URL#]/resource/image/sample/banner/sample5.gif'),
(6,     '朝顔',     '[#M3_ROOT_URL#]/resource/image/sample/banner/sample6.gif'),
(7,     '夏祭り',     '[#M3_ROOT_URL#]/resource/image/sample/banner/sample7.gif'),
(8,     'ＰＣ',     '[#M3_ROOT_URL#]/resource/image/sample/banner/sample8.gif'),
(9,     'ジンギスカン',     '[#M3_ROOT_URL#]/resource/image/sample/banner/sample9.gif'),
(10,    'クッキー',     '[#M3_ROOT_URL#]/resource/image/sample/banner/sample10.gif'),
(11,    '飲み会',     '[#M3_ROOT_URL#]/resource/image/sample/banner/sample11.gif'),
(12,    'コスメ',     '[#M3_ROOT_URL#]/resource/image/sample/banner/sample12.gif'),
(13,    'タブレット',     '[#M3_ROOT_URL#]/resource/image/sample/banner/sample13.gif'),
(14,    'ジュエリー',     '[#M3_ROOT_URL#]/resource/image/sample/banner/sample14.gif'),
(15,    'パン',     '[#M3_ROOT_URL#]/resource/image/sample/banner/sample15.gif'),
(16,    'ハロウィーン',     '[#M3_ROOT_URL#]/resource/image/sample/banner/sample16.gif'),
(17,    'ラケット',     '[#M3_ROOT_URL#]/resource/image/sample/banner/sample17.gif'),
(18,    'きのこ',     '[#M3_ROOT_URL#]/resource/image/sample/banner/sample18.gif'),
(19,    'すいか',     '[#M3_ROOT_URL#]/resource/image/sample/banner/sample19.gif');

TRUNCATE TABLE `product_category`;
INSERT INTO `product_category` (`pc_id`, `pc_language_id`, `pc_name`, `pc_parent_id`, `pc_sort_order`) VALUES 
(1, 'ja', 'ドコモ', 0, 1),
(2, 'ja', 'au', 0, 2);

TRUNCATE TABLE `product_price`;
INSERT INTO `product_price` (`pp_product_id`, `pp_language_id`, `pp_price_type_id`, `pp_currency_id`, `pp_price`, `pp_active_start_dt`, `pp_active_end_dt`) VALUES 
(1, 'ja', 'selling', 'JPY', 12000.0000, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(2, 'ja', 'selling', 'JPY', 12000.0000, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(3, 'ja', 'selling', 'JPY', 11000.0000, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(4, 'ja', 'selling', 'JPY', 13000.0000, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(5, 'ja', 'selling', 'JPY', 12000.0000, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(6, 'ja', 'selling', 'JPY', 10000.0000, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(7, 'ja', 'selling', 'JPY', 10000.0000, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(8, 'ja', 'selling', 'JPY', 12000.0000, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(9, 'ja', 'selling', 'JPY', 10000.0000, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(10, 'ja', 'selling', 'JPY', 10000.0000, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(11, 'ja', 'selling', 'JPY', 10000.0000, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(12, 'ja', 'selling', 'JPY', 10000.0000, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(13, 'ja', 'selling', 'JPY', 13000.0000, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(14, 'ja', 'selling', 'JPY', 12000.0000, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(15, 'ja', 'selling', 'JPY', 13000.0000, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(16, 'ja', 'selling', 'JPY', 12000.0000, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(17, 'ja', 'selling', 'JPY', 11000.0000, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(18, 'ja', 'selling', 'JPY', 11000.0000, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(19, 'ja', 'selling', 'JPY', 11000.0000, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(20, 'ja', 'selling', 'JPY', 10000.0000, '0000-00-00 00:00:00', '0000-00-00 00:00:00');

TRUNCATE TABLE `product_status`;
INSERT INTO `product_status` (`ps_id`, `ps_language_id`, `ps_type`, `ps_value`) VALUES 
(1, 'ja', 'new', '1'),
(1, 'ja', 'suggest', '0'),
(2, 'ja', 'new', '1'),
(2, 'ja', 'suggest', '0'),
(3, 'ja', 'new', '1'),
(3, 'ja', 'suggest', '0'),
(4, 'ja', 'new', '1'),
(4, 'ja', 'suggest', '0'),
(5, 'ja', 'new', '1'),
(5, 'ja', 'suggest', '0'),
(6, 'ja', 'new', '1'),
(6, 'ja', 'suggest', '0'),
(7, 'ja', 'new', '1'),
(7, 'ja', 'suggest', '0'),
(8, 'ja', 'new', '1'),
(8, 'ja', 'suggest', '0'),
(9, 'ja', 'new', '1'),
(9, 'ja', 'suggest', '0'),
(10, 'ja', 'new', '1'),
(10, 'ja', 'suggest', '0'),
(11, 'ja', 'new', '1'),
(11, 'ja', 'suggest', '1'),
(12, 'ja', 'new', '1'),
(12, 'ja', 'suggest', '0'),
(13, 'ja', 'new', '0'),
(13, 'ja', 'suggest', '0'),
(14, 'ja', 'new', '0'),
(14, 'ja', 'suggest', '0'),
(15, 'ja', 'new', '0'),
(15, 'ja', 'suggest', '0'),
(16, 'ja', 'new', '0'),
(16, 'ja', 'suggest', '0'),
(17, 'ja', 'new', '1'),
(17, 'ja', 'suggest', '0'),
(18, 'ja', 'new', '0'),
(18, 'ja', 'suggest', '1'),
(19, 'ja', 'new', '0'),
(19, 'ja', 'suggest', '1'),
(20, 'ja', 'new', '0'),
(20, 'ja', 'suggest', '1');

TRUNCATE TABLE `product_image`;
INSERT INTO `product_image` (`im_type`, `im_id`, `im_language_id`, `im_size_id`, `im_name`, `im_url`) VALUES 
(2, 1, 'ja', 'small-product', '', '[#M3_ROOT_URL#]/resource/image/sample/product/au1.gif'),
(2, 1, 'ja', 'standard-product', '', '[#M3_ROOT_URL#]/resource/image/sample/product/au1.png'),
(2, 1, 'ja', 'large-product', '', '[#M3_ROOT_URL#]/resource/image/sample/product/au1.gif'),
(2, 2, 'ja', 'small-product', '', '[#M3_ROOT_URL#]/resource/image/sample/product/au2.gif'),
(2, 2, 'ja', 'standard-product', '', '[#M3_ROOT_URL#]/resource/image/sample/product/au2.png'),
(2, 2, 'ja', 'large-product', '', '[#M3_ROOT_URL#]/resource/image/sample/product/au2.gif'),
(2, 3, 'ja', 'small-product', '', '[#M3_ROOT_URL#]/resource/image/sample/product/au3.png'),
(2, 3, 'ja', 'standard-product', '', '[#M3_ROOT_URL#]/resource/image/sample/product/au3.png'),
(2, 3, 'ja', 'large-product', '', '[#M3_ROOT_URL#]/resource/image/sample/product/au3_2.png'),
(2, 4, 'ja', 'small-product', '', '[#M3_ROOT_URL#]/resource/image/sample/product/a4.png'),
(2, 4, 'ja', 'standard-product', '', '[#M3_ROOT_URL#]/resource/image/sample/product/au4.png'),
(2, 4, 'ja', 'large-product', '', '[#M3_ROOT_URL#]/resource/image/sample/product/a4.png'),
(2, 5, 'ja', 'small-product', '', '[#M3_ROOT_URL#]/resource/image/sample/product/a5.png'),
(2, 5, 'ja', 'standard-product', '', '[#M3_ROOT_URL#]/resource/image/sample/product/au5.png'),
(2, 5, 'ja', 'large-product', '', '[#M3_ROOT_URL#]/resource/image/sample/product/a5.png'),
(2, 6, 'ja', 'small-product', '', '[#M3_ROOT_URL#]/resource/image/sample/product/a6.png'),
(2, 6, 'ja', 'standard-product', '', '[#M3_ROOT_URL#]/resource/image/sample/product/au6.png'),
(2, 6, 'ja', 'large-product', '', '[#M3_ROOT_URL#]/resource/image/sample/product/a6.png'),
(2, 7, 'ja', 'small-product', '', '[#M3_ROOT_URL#]/resource/image/sample/product/a7.png'),
(2, 7, 'ja', 'standard-product', '', '[#M3_ROOT_URL#]/resource/image/sample/product/au7.png'),
(2, 7, 'ja', 'large-product', '', '[#M3_ROOT_URL#]/resource/image/sample/product/a7.png'),
(2, 8, 'ja', 'small-product', '', '[#M3_ROOT_URL#]/resource/image/sample/product/a8.png'),
(2, 8, 'ja', 'standard-product', '', '[#M3_ROOT_URL#]/resource/image/sample/product/au8.png'),
(2, 8, 'ja', 'large-product', '', '[#M3_ROOT_URL#]/resource/image/sample/product/a8.png'),
(2, 9, 'ja', 'small-product', '', '[#M3_ROOT_URL#]/resource/image/sample/product/a9.png'),
(2, 9, 'ja', 'standard-product', '', '[#M3_ROOT_URL#]/resource/image/sample/product/au9.png'),
(2, 9, 'ja', 'large-product', '', '[#M3_ROOT_URL#]/resource/image/sample/product/a9.png'),
(2, 10, 'ja', 'small-product', '', '[#M3_ROOT_URL#]/resource/image/sample/product/a10.png'),
(2, 10, 'ja', 'standard-product', '', '[#M3_ROOT_URL#]/resource/image/sample/product/au10.png'),
(2, 10, 'ja', 'large-product', '', '[#M3_ROOT_URL#]/resource/image/sample/product/a10.png'),
(2, 11, 'ja', 'small-product', '', '[#M3_ROOT_URL#]/resource/image/sample/product/a11.png'),
(2, 11, 'ja', 'standard-product', '', '[#M3_ROOT_URL#]/resource/image/sample/product/au11.png'),
(2, 11, 'ja', 'large-product', '', '[#M3_ROOT_URL#]/resource/image/sample/product/a11.png'),
(2, 12, 'ja', 'small-product', '', '[#M3_ROOT_URL#]/resource/image/sample/product/a12.png'),
(2, 12, 'ja', 'standard-product', '', '[#M3_ROOT_URL#]/resource/image/sample/product/au12.png'),
(2, 12, 'ja', 'large-product', '', '[#M3_ROOT_URL#]/resource/image/sample/product/a12.png'),
(2, 13, 'ja', 'small-product', '', '[#M3_ROOT_URL#]/resource/image/sample/product/do1.png'),
(2, 13, 'ja', 'standard-product', '', '[#M3_ROOT_URL#]/resource/image/sample/product/d1.png'),
(2, 13, 'ja', 'large-product', '', '[#M3_ROOT_URL#]/resource/image/sample/product/do1.png'),
(2, 14, 'ja', 'small-product', '', '[#M3_ROOT_URL#]/resource/image/sample/product/do2.png'),
(2, 14, 'ja', 'standard-product', '', '[#M3_ROOT_URL#]/resource/image/sample/product/d2.png'),
(2, 14, 'ja', 'large-product', '', '[#M3_ROOT_URL#]/resource/image/sample/product/do2.png'),
(2, 15, 'ja', 'small-product', '', '[#M3_ROOT_URL#]/resource/image/sample/product/do3.png'),
(2, 15, 'ja', 'standard-product', '', '[#M3_ROOT_URL#]/resource/image/sample/product/d3.png'),
(2, 15, 'ja', 'large-product', '', '[#M3_ROOT_URL#]/resource/image/sample/product/do3.png'),
(2, 16, 'ja', 'small-product', '', '[#M3_ROOT_URL#]/resource/image/sample/product/do4.png'),
(2, 16, 'ja', 'standard-product', '', '[#M3_ROOT_URL#]/resource/image/sample/product/d4.png'),
(2, 16, 'ja', 'large-product', '', '[#M3_ROOT_URL#]/resource/image/sample/product/do4.png'),
(2, 17, 'ja', 'small-product', '', '[#M3_ROOT_URL#]/resource/image/sample/product/do5.png'),
(2, 17, 'ja', 'standard-product', '', '[#M3_ROOT_URL#]/resource/image/sample/product/d5.png'),
(2, 17, 'ja', 'large-product', '', '[#M3_ROOT_URL#]/resource/image/sample/product/do5.png'),
(2, 18, 'ja', 'small-product', '', '[#M3_ROOT_URL#]/resource/image/sample/product/do6.png'),
(2, 18, 'ja', 'standard-product', '', '[#M3_ROOT_URL#]/resource/image/sample/product/d6.png'),
(2, 18, 'ja', 'large-product', '', '[#M3_ROOT_URL#]/resource/image/sample/product/do6.png'),
(2, 19, 'ja', 'small-product', '', '[#M3_ROOT_URL#]/resource/image/sample/product/do7.png'),
(2, 19, 'ja', 'standard-product', '', '[#M3_ROOT_URL#]/resource/image/sample/product/d7.png'),
(2, 19, 'ja', 'large-product', '', '[#M3_ROOT_URL#]/resource/image/sample/product/do7.png'),
(2, 20, 'ja', 'small-product', '', '[#M3_ROOT_URL#]/resource/image/sample/product/do8.png'),
(2, 20, 'ja', 'standard-product', '', '[#M3_ROOT_URL#]/resource/image/sample/product/d8.png'),
(2, 20, 'ja', 'large-product', '', '[#M3_ROOT_URL#]/resource/image/sample/product/do8.png');

TRUNCATE TABLE `product`;
INSERT INTO `product` (`pt_id`, `pt_language_id`, `pt_name`, `pt_code`, `pt_product_type`, `pt_description`, `pt_description_short`, `pt_admin_note`, `pt_category_id`, `pt_related_product`, `pt_manufacturer_id`, `pt_sort_order`, `pt_default_price`, `pt_visible`, `pt_search_keyword`, `pt_site_url`, `pt_unit_type_id`, `pt_unit_quantity`, `pt_innner_quantity`, `pt_quantity_decimal`, `pt_price_decimal`, `pt_weight`, `pt_tax_type_id`, `pt_parent_id`, `pt_attr_condition`, `pt_product_set`, `pt_option_price`) VALUES 
(1, 'ja', 'MEDIA SKIN', 'AU001', 1, '<p>情緒に訴える新しい触感！</p>\r\n<p><span class="Text">デザイナー吉岡徳仁氏によるau design project第6弾モデル。<br />\r\n表面処理と塗料により2種類の異なる触感を実現しました。オレンジとホワイトは、ファンデーションに利用されているシリコン粒子でさらっとした心地よさ、ブラックは、特殊ウレタン粒子を含んだソフトフィール塗料による、しっとりとした心地よさに仕上がっています。<br />\r\nまた、キー部分を覆うフリップカバーはMEDIA SKINのシンプルな美しさと心地よい触感に貢献しているだけでなく、開閉動作と連動して着信応答や終話ができる使いやすさを兼ね備えています。</span></p>\r\n<p>&nbsp;</p>\r\n<p>&nbsp;</p>', '新しい触感と美しい映像をまとったエモーションナルケータイ。', '', 2, '', 0, 1, '', true, '', '', 'ko', 1.00, 0, 0, 0, 0.00, 'sales', 0, '', '', ''),
(2, 'ja', 'AQUOS ケータイ', 'AU002', 1, '<p><span class="Text">液晶テレビ「AQUOS」の液晶技術を活かした美しい3.0インチ大画面のモバイルASV液晶を搭載。さらに「SVエンジン」「6色カラーフィルター」「明るさセンサー」を採用し、屋内外で鮮やかに見やすい映像を楽しめます。</span></p>\r\n<p>「サイクロイド」スタイルにより横向き全画面で「ワンセグ」を楽しめる！<span class="Text">画面を90&deg;回すだけで「ワンセグ」<small class="CaptionText"><font size="2">(注2)</font></small> が起動し、テレビを全画面で楽しみながらチャンネル選局もできる、独自の使いやすさを実現しました。<br />\r\n</span></p>', '3インチワイド液晶で「ワンセグ」＆「デジタルラジオ」が楽しめるAQUOSケータイ。', '', 2, '', 0, 2, '', true, '', '', 'ko', 1.00, 0, 0, 0, 0.00, 'sales', 0, '', '', ''),
(3, 'ja', 'W51K', 'AU003', 1, '<p>薄さ約20mmのスリムボディながら、「ワンセグ」を大型2.7インチワイド液晶で楽しめほか、外部メモリへの番組録画も可能。また、LISMO「ビデオクリップ」の視聴も可能な最新LISMOサービスにも対応しています。</p>\r\n<p><span class="Text">「ワンセグ」、音楽、カメラなどをスマートに操作できる「フロントメディアキー」を搭載。液晶を表にして閉じた場合には「ワンセグ」やカメラの操作を、液晶を裏にして閉じた場合には音楽操作がラクラク。また、数字キーには、使いやすさとデザイン性を両立した「パネル型フレームレスキー」を採用しています。</span></p>\r\n<p>&nbsp;</p>', '迫力の大画面&高音質。', '', 2, '', 0, 3, '', true, '', '', 'ko', 1.00, 0, 0, 0, 0.00, 'sales', 0, '', '', ''),
(4, 'ja', 'W51P', 'AU004', 1, '<p><span class="Text">通話や着信をはじめ、FeliCaサイン、カメラ起動などを&quot;ヒカリ&quot;でお知らせ。<br />\r\nアシンメトリー (非対称) なデザインの「ソフトイルミネーションパネル」を採用することで、段差からの&quot;ヒカリ&quot;の射し込みにより、レリーフパターンが浮かび上がります。左右の段差が全く異なる表情を見せ、記号的ではない女性らしさを表現します。<br />\r\nまた、待受画面などの画面デザインも、本体のデザインにあわせた4つのパターンのテーマをプリセット。</span></p>\r\n<p><span class="Text"><span class="Text">「ワンプッシュオープン」機能</span></span></p>\r\n<p><span class="Text"><span class="Text">「ワンプッシュオープン」なら、ヒンジ横のボタンをプッシュするだけで、片手で素早くケータイをオープン。開く時のスマートさだけでなく、着信時には開けばそのまま通話も可能。不在着信・新着メールの表示がオープンするだけで確認できるなど、使いやすさも備えています。</span></span></p>', '”ヒカリ”が魅せる女性らしさ。ワンプッシュオープン対応の「おサイフケータイ」。', '', 2, '', 0, 4, '', true, '', '', 'ko', 1.00, 0, 0, 0, 0.00, 'sales', 0, '', '', ''),
(5, 'ja', 'W44', 'AU005', 1, '<p>ケータイの新しいカタチ「モバイルシアタースタイル」</p>\r\n<p><span class="Text">ケータイを横向きに開く新感覚の「モバイルシアタースタイル」を採用。観やすい横スタイルで「ワンセグ」<small class="CaptionText"><font size="2">(注2)</font></small> やLISMO「ビデオクリップ」などを存分に楽しめます。横スタイルに適した待受画面「マイスクリーン」や、横スタイルのためのメニュー「シアターメニュー」などの機能も充実。</span></p>\r\n<p><span class="Text">リアルにこだわった高画質大画面＆高音質</span></p>\r\n<p><span class="Text"><span class="Text">ケータイ最大級の約3インチフルワイド液晶を搭載。ソニー製液晶テレビ「BRAVIA」<small class="CaptionText"><font size="2">(注3)</font></small> の画質向上技術を採用した「RealityMAX&trade;」<small class="CaptionText"><font size="2">(注4)</font></small> により、映像も鮮明です。またCD並の高音質な音声と、動画や写真・文字によるデータ放送が楽しめる「デジタルラジオ」<small class="CaptionText"><font size="2">(注5)</font></small> にも対応。「DBEX&trade;」により、臨場感あふれるハイクオリティサウンドも実現しています</span></span></p>\r\n<p><span class="Text"><br />\r\n<img height="10" alt="" src="http://www.au.kddi.com/common/image/_.gif" width="1" /></span></p>', '3.0インチ画面で、「ワンセグ」＆「デジタルラジオ」を楽しむDuel Styleケータイ。', '', 2, '', 0, 5, '', true, '', '', 'ko', 1.00, 0, 0, 0, 0.00, 'sales', 0, '', '', ''),
(6, 'ja', 'G\'ｚ　One', 'AU006', 1, '<p>&nbsp;</p>\r\n<p><span class="Text">ダイナミックなフォルムと洗練されたデザイン</span></p>\r\n<p><span class="Text"><span class="Text">ダイナミックなフォルムと緻密なディテールで、未来感を感じさせる新世代の&quot;タフネス&quot;デザインを表現。操作キーの照明に、本体色にマッチするカラーをそれぞれ採用。またサブ液晶は白黒反転表示にも対応し、オリジナルサイトからのダウンロードでカスタマイズも可能です。</span></span></p>\r\n<p><span class="Text">耐水性・耐衝撃性のタフネス性能をWINで実現</span></p>\r\n<p><span class="Text">IPX7相当 <small class="CaptionText"><font size="2">(注2)</font></small> の耐水性と、耐衝撃性をWINで実現。WIN＋タフネス性能により、WINの高機能をさまざまな場面で利用できます。</span></p>\r\n<p>&nbsp;</p>', '耐水・耐衝撃ボディと、大型液晶＆2.1メガカメラ。WIN初のタフネスケータイ。   ', '', 2, '', 0, 6, '', true, '', '', 'ko', 1.00, 0, 0, 0, 0.00, 'sales', 0, '', '', ''),
(7, 'ja', 'ジュニアケータイ', 'AU007', 1, '<p>いざというとき安心な「移動経路通知」</p>\r\n<p><span class="Text">お子さまが「防犯ブザー」を鳴らしたときや、ケータイの電源が切られたときには、その場所を家族のケータイに写真付きで緊急通知。その後は、約5分おきに更新される地図で、ケータイやパソコンからお子さまの足どりを確認できます。Cメールで強制的に起動/中止させることも可能です。</span></p>\r\n<p><span class="Text"><span class="Text">「防犯ブザー」は、いざというときに使いやすいひも引き型。ブザーが鳴らされると、カメラ撮影、家族への電話、現在位置と写真の緊急送信&amp;移動経路通知を自動で行います。また、電池の抜き取りを防ぐ「電池フタロック」 で、強制的な電源オフも防止。</span><br />\r\n</span></p>', '移動経路通知＆防犯ブザーストラップ、生活防水対応で安心のジュニアケータイ。', '', 2, '', 0, 7, '', true, '', '', 'ko', 1.00, 0, 0, 0, 0.00, 'sales', 0, '', '', ''),
(8, 'ja', 'Sweets cute', 'AU008', 1, '<p>お勉強などに役立つ便利機能もたっぷり。</p>\r\n<p><span class="Text">英和4万6千語&amp;和英5万6千語のGモバイル辞典と、国語4万7千語の明鏡モバイル国語辞典を搭載 <small class="CaptionText"><font size="2">(注1)</font></small>。また「カメラde辞書」機能では、漢字にカメラをかざすと漢字をよみがなに変換でき、漢字の意味も表示されます。時間割・おこづかい帳・日記帳などもプリセット。<br />\r\n</span></p>\r\n<p><span class="Text">やわらかフォルム＆ハートフルなデザイン</span></p>\r\n<p><span class="Text"><span class="Text">プロダクトデザイナー柴田文江氏による&quot;Sweets&quot;第3弾が登場。今度のテーマは「やさしい思いやりがいっぱいの、ハートフルなケータイ」です。初代Sweetsのかわいらしさを受け継ぎながら、コロンとしたスタイルと、ビスケットをディップしたようなやわらかなデザイン&amp;カラーも個性的。<br />\r\n</span></span></p>', '', '', 2, '', 0, 8, '', true, '', '', 'ko', 1.00, 0, 0, 0, 0.00, 'sales', 0, '', '', ''),
(9, 'ja', 'A5518SA', 'AU009', 1, '<p>コンパクトボディーでシンプル操作</p>\r\n<p><span class="Text">カメラ機能をなくすことで、ビジネス面でのセキュリティにも配慮。ボディは薄さ21mm・重さ103gのコンパクトさと、シンプルで使いやすい操作性を大切にしました。また大きく押しやすいキーと、見やすい「でか文字」で文字入力もラクラク。さらに、使いやすさを大切にした「フレンドリーデザイン」に対応しています。</span></p>\r\n<p><span class="Text"><span class="Text">アドレス帳には1,000件、スケジュール帳には500件まで、たっぷり保存可能。また「赤外線通信」を利用すると、アドレス帳登録やプロフィールの交換などもスムーズ。</span></span></p>', 'コンパクトで使いやすい、「フレンドリーデザイン」対応のカメラなしモデル。', '', 2, '', 0, 9, '', true, '', '', 'ko', 1.00, 0, 0, 0, 0.00, 'sales', 0, '', '', ''),
(10, 'ja', 'A5514SA', 'AU010', 1, '<p>&nbsp;</p>\r\n<p><span class="Text">海外でも話せる、メール＆EZwebも使える</span></p>\r\n<p><span class="Text">お申し込み不要で、そのまま海外に持ち出しても通話&amp;パケット通信が可能なグローバルパスポートに対応。渡航先でもいつもの電話番号のままでご利用いただけます。通話だけでなく、メールやEZwebも可能だから、旅先で撮った写真やムービーをその場で送るなど旅行にビジネスにさまざまなシーンで活躍します。海外のパケット通信対応エリアも順次拡大中。<br />\r\n<span class="Text">業界初の開いても閉じても突起のないフラットなスタイル「Smooth Style」を実現。従来のヒンジ部分がカットされた新機構のフォルムは、なめらかなラインで顔にフィットし、今までにない使いやすさを追求しました。またグローバルパスポート対応モデル初の内蔵アンテナで、海外でもコンパクトに持ち歩けます。<br />\r\n</span></span></p>', '海外でも話せる、コンパクト&フラットなグローバルパスポート対応モデル。', '', 2, '', 0, 10, '', true, '', '', 'ko', 1.00, 0, 0, 0, 0.00, 'sales', 0, '', '', ''),
(11, 'ja', '簡単ケータイ　A1406', 'AU011', 1, '<p>見やすさ、押しやすさ、聞きやすさを大切に</p>\r\n<p>・大きく押しやすい「でかキー」で、電話番号も文字もラクラク入力できます。</p>\r\n<p>・2.4インチの大画面液晶と、最大40ドットの大きな「でか文字」で、見やすい文字表示に。</p>\r\n<p>・混雑した場所でも相手の声が聞き取りやすい「でか受話音」。</p>\r\n<p>・<span class="Text">押すだけで決まった相手に電話をかけられる3つの「ワンタッチキー」を搭載。</span></p>\r\n<p>&nbsp;</p>', '大きなキーと大きな文字表示。ワンタッチキーで使いやすい「簡単ケータイ」。', '', 2, '', 0, 11, '', true, '', '', 'ko', 1.00, 0, 0, 0, 0.00, 'sales', 0, '', '', ''),
(12, 'ja', '簡単ケータイS　A101K', 'AU012', 1, '<p>電話のかけ方が、とにかく簡単</p>\r\n<p>ご自宅のコードレス電話と同じような使い方で話せます。</p>\r\n<p>よく電話する相手を、「ワンタッチボタン」に登録すると、もっと簡単に話せます。設定も簡単です。</p>\r\n<p><span class="LargeText">より便利にお使いいただけるよう、登録した相手の名前を書き込める専用シールが付属しています。</span></p>\r\n<p><span class="LargeText"><span class="LargeText">自分の電話番号を書き込むことができ、落下防止にも配慮した「クリップ付きストラップ」。自分の電話番</span></span></p>\r\n<p><span class="LargeText"><span class="LargeText">号の確認や持ち歩きにも安心です。</span></span></p>', '', '', 2, '', 0, 12, '', true, '', '', 'ko', 1.00, 0, 0, 0, 0.00, 'sales', 0, '', '', ''),
(13, 'ja', 'FOMA D904i', 'DO001', 1, '<p>9シリーズ最薄16.8ｍｍのスリム・スライド</p>\r\n<p>2.8インチワイドQVGA液晶搭載で、凹凸の無いスリムデザインを採用。新機構「アシストスライド」により、開けても閉じても心地よいなめらかなスライド開閉を実現しています。</p>\r\n<p>ケータイを振るだけで機能が連動する「モーショナルコントロール」対応</p>\r\n<p>モーションコントロール（加速度センサー）搭載でケータイを振ったり、傾けたりすることに機能が連動します。</p>\r\n<ul class="normal txt">\r\n    <li>ケータイを左ヨコに倒すことで、自動的にヨコ向きワイド画面表示に切り替えが可能。</li>\r\n    <li>「直感ゲーム」対応で、ケータイを動かす操作で遊べるiアプリ｢タマラン｣をプリインストール。</li>\r\n    <li>ケータイを逆さまにしたり振ったりすることに連動してドコモダケなどの「マチキャラ」も動作。</li>\r\n</ul>', '携帯を振って動かす直感操作が新しい、スリム・スライドケータイ', '', 1, '', 0, 1, '', true, '', '', 'ko', 1.00, 0, 0, 0, 0.00, 'sales', 0, '', '', ''),
(14, 'ja', 'FOMA　F901i', 'DO002', 1, '<p>3.1インチフルワイド大画面で楽しめるワンセグ対応</p>\r\n<ul class="normal txt">\r\n    <li>画面をヨコにして、テレビとリモコンが1つになったようなスタイルでワンセグ視聴が可能。</li>\r\n    <li>照光センサーによる「明るさ自動調整機能」や、メールを作成しながらワンセグを視聴する「マルチウィンドウ」、字幕を大きく表示する「アドバンストモード」対応などワンセグ視聴に配慮した機能も充実。</li>\r\n    <li>IPS液晶搭載で、早い動きの表示に強く、約170度の広い視野角で視聴が可能</li>\r\n</ul>', '3.1インチ・フルワイド大画面でワンセグを楽しめるヨコモーションケータイ', '', 1, '', 0, 2, '', true, '', '', 'ko', 1.00, 0, 0, 0, 0.00, 'sales', 0, '', '', ''),
(15, 'ja', 'FOMA　P904i', 'DO003', 1, '<p>シンプルで新しいコンパクトデザイン＆Newカスタムジャケット</p>\r\n<p class="txt">シンプルでコンパクトなミラーパネルが美しいアシンメトリーデザインと、カスタムジャケット無しでも完成するデザインです。カスタムジャケットは今までのデザイン的な変化だけでなく、プライベートウィンドウ（背面ディスプレイ）の表情も変化する新しいコンセプトを採用しました。また、ケータイの背面にヒカリで不在着信など各種情報が浮かび上がる「ヒカリアイコン<span class="sup">TM</span>」も搭載しています。</p>\r\n<p class="txt">ケータイで1つの音楽を定額で楽しむスタイル「うた・ホーダイ」に対応</p>\r\n<p>&nbsp;</p>\r\n<p>\r\n<li>「うた・ホーダイ」に対応。</li>\r\n<li>Windows Media&reg; Audio（WMA）にも対応し、月額1980円（税込）で250万曲以上を聴き放題の音楽配信サービス「Napster&reg;」も楽しめる。</li>\r\n<li>SDオーディオなら最長約65時間の長時間再生が可能。</li>\r\n<li>Bluetooth&reg;対応だからワイヤレスで音楽を楽しめる。ケータイとワイヤレスイヤホンの接続も従来の13タッチから3タッチに短縮。</li>\r\n</p>\r\n<p>&nbsp;</p>', 'Newデザイン＆Newカスタムジャケット対応のワイヤレスミュージックケータイ', '', 1, '', 0, 3, '', true, '', '', 'ko', 1.00, 0, 0, 0, 0.00, 'sales', 0, '', '', ''),
(16, 'ja', 'FOMA N703iμ', 'DO004', 1, '<p>11.4mmｍｐ超薄型に掲載される高機能</p>\r\n<p class="txt">薄さが際立つスポーティなデザイン、その中に先進機能を搭載しています。超薄型ボディの表面には、LEDやスピーカー機能を兼ね備えたディンプルをデザイン。薄さが際立つツートーンのカラーリングでいっそう美しく、さらに、メガピクセルカメラや2.3インチQVGA+<span class="sup">TM</span> 液晶、microSD<span class="sup">TM</span>メモリーカードスロットを搭載しました。</p>\r\n<p class="txt">内蔵コンテンツを、スタイリングモードで一括設定</p>\r\n<p class="txt">待受画面をはじめ、メニュー画面、状態表示、アイコン、ミュージックプレーヤー画面などをお好みで設定できます。</p>', '厚さ11.4mmの世界最薄', '', 1, '', 0, 4, '', true, '', '', 'ko', 1.00, 0, 0, 0, 0.00, 'sales', 0, '', '', ''),
(17, 'ja', 'FOMA　P703iμ', 'DO005', 1, '<p>超薄型11.4ｍｍの、新素材感ステンレスボディ</p>\r\n<p class="txt">スリムサイズと軽量化を実現しました。カードサイズに近いサイズ感で、小さくて扱いやすいデザインです。基板を樹脂で固めて強度を上げる新工法を採用しました。ゆがみやねじれなど外からの力に強く、薄さと強さを両立したタフなボディです。アウトカメラ側ボディに使われたステンレスのクールな質感は、デザイン性と共に強度を強めます。</p>\r\n<p class="txt">SDオーディオを搭載</p>\r\n<p class="txt">最大2GBのmicroSD<span class="sup">TM</span>メモリーカードに、ネットストア「MOOCS」やCD、コンポから入手した曲を転送することができます。メールやiモードの操作も同時にできます。</p>\r\n<p class="txt">&nbsp;</p>', '厚さ11.4mmの高級感を醸し出す、Super Slimステンレスボディ', '', 1, '', 0, 5, '', true, '', '', 'ko', 1.00, 0, 0, 0, 0.00, 'sales', 0, '', '', ''),
(18, 'ja', 'FOMA　F703i', 'DO006', 1, '<p>水に濡れても安心のIPX5,IPX7の性能</p>\r\n<p>取扱いやすい防水キャップを採用</p>\r\n<p class="txt">キャップの半挿しによる浸水を未然に防ぐため、閉めやすく、且つ完全に閉まったときの感触が指に伝わる構造にしました。</p>\r\n<ul class="txt normal">\r\n    <li>雨の中で傘をささずに通話できます。（1時間の雨量が20mm程度）</li>\r\n    <li>お風呂場で使用できます。</li>\r\n    <li>洗面器などに張った静水につけて、ゆすりながら汚れを洗い落とすことができます。</li>\r\n</ul>', '日常生活にフィットするウォータープルーフ・スリムケータイ', '', 1, '', 0, 6, '', true, '', '', 'ko', 1.00, 0, 0, 0, 0.00, 'sales', 0, '', '', ''),
(19, 'ja', 'FOMA　703i', 'DO007', 1, '<p>メールの打ちやすさを追及した「Wave Tile Key」</p>\r\n<p>操作性を重視した立体形状の「Wave Tile Key<span class="sup">TM</span>」。フレームレスだからキーが大きく、ネイルアートを施した女性の長い爪でもメールの文字入力がスムーズです。また白色のキーバックライトが高級感を演出します。待受画面には世界中で活躍中の「はやさきちーこ」の繊細な線とエレガントな色彩が調和したイラスト3タイプをプリインストールしています。フランスで出版されると同時に大人気となった、オトナの絵本ブームの火付け役、「リサとガスパール」をプリインストールしています</p>', 'メール機能にこだわったHappyデコメケータイ', '', 1, '', 0, 7, '', true, '', '', 'ko', 1.00, 0, 0, 0, 0.00, 'sales', 0, '', '', ''),
(20, 'ja', 'FOMA　SO703i', 'DO008', 1, '<p>デザイン</p>\r\n<p>本体フロント面は、着せかえのできる「Style-Up&reg;」パネルを採用しています。それぞれのパネルのテーマに合わせた「アロマシート<span class="sup">TM</span>」も付属しました。「アロマシート<span class="sup">TM</span>」を本体部分に貼り付けることで、パネルのデザインと香りを組み合わせてお楽しみいただけます。</p>\r\n<p>\r\n<li>アロマシート<span class="sup">TM</span>は香りのマイクロカプセルをシート状にしたもので、FOMA端末に取り付けて香りをお楽しみいただけます。</li>\r\n<li>香りが弱くなってきた場合は、アロマシート<span class="sup">TM</span>の表面を指で軽くこすると、マイクロカプセルがはじけ香りがします。</li>\r\n<li>アロマシート<span class="sup">TM</span>は消耗品です。マイクロカプセルがすべてはじけると、香りは出なくなります。香りの持続期間は約3ヶ月間ですが、温度、湿度などの環境やアロマシート<span class="sup">TM</span>をこする回数により変わります。</li>\r\n<li>香りの感じ方には個人差があります。</li>\r\n</p>', '香りもデザインも着せ替えられる、アロマケータイ', '', 1, '', 0, 8, '', true, '', '', 'ko', 1.00, 0, 0, 0, 0.00, 'sales', 0, '', '', '');
