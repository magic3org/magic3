-- *
-- * データ登録スクリプト「Eコマース標準管理メニュー」
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
-- [Eコマース標準管理メニュー]
-- Eコマース向けに構成した標準管理メニュー
-- 階層型メニュー管理

-- 管理画面メニューデータ
DELETE FROM _nav_item WHERE ni_nav_id = 'admin_menu';
DELETE FROM _nav_item WHERE ni_nav_id = 'admin_menu.en';
INSERT INTO _nav_item
(ni_id, ni_parent_id, ni_index, ni_nav_id,    ni_task_id,             ni_view_control, ni_visible,     ni_param,       ni_name,                ni_help_title,            ni_help_body) VALUES
(100,   0,            0,        'admin_menu', '_page',                0,               true,           '',             '画面管理',             '画面管理',               'Webサイトのデザインや機能を管理します。'),
(101,   100,          0,        'admin_menu', 'pagedef',              0,               true,           '',             'ページ編集',         'ページ編集',         'ウィジェットを配置してページを作成します。'),
(102,   100,          1,        'admin_menu', 'pagedef_smartphone',   0,               false,          '',             'ページ編集(スマートフォン)', 'ページ編集(スマートフォン)',       'ウィジェットを配置してスマートフォン用のページを作成します。'),
(103,   100,          2,        'admin_menu', '_103',                 3,               true,           '',             'セパレータ',           '',                       ''),
(104,   100,          3,        'admin_menu', 'widgetlist',           0,               true,           '',             'ウィジェット管理',     'ウィジェット管理',       'ウィジェットの管理を行います。'),
(105,   100,          4,        'admin_menu', 'templist',             0,               true,           '',             'テンプレート管理',     'テンプレート管理',       'テンプレートの管理を行います。'),
(106,   100,          5,        'admin_menu', 'smenudef',             0,               true,           '',             'メニュー管理',         'メニュー管理',           'メニュー定義を管理します。'),
(199,   0,            1,        'admin_menu', '_199',                 1,               true,           '',             '改行',                 '',                       ''),
(200,   0,            2,        'admin_menu', '_login',               0,               true,           '',             'システム運用',         '',                       ''),
(201,   200,          0,        'admin_menu', 'userlist',             0,               true,           '',             'ユーザ管理',           'ユーザ管理',             'ログイン可能なユーザを管理します。'),
(202,   200,          1,        'admin_menu', 'accesslog',            0,               true,           '',             '運用状況',             '運用状況',               'サイトの運用状況を表示します。'),
(299,   0,            3,        'admin_menu', '_299',                 1,               true,           '',             '改行',                 '',                       ''),
(300,   0,            4,        'admin_menu', '_config',              0,               true,           '',             'システム管理',         '',                       ''),
(301,   300,          0,        'admin_menu', 'configsite',           0,               true,           '',             '基本情報',             '基本情報',               'サイト運営に必要な情報を設定します。'),
(302,   300,          1,        'admin_menu', 'configsys',            0,               true,           '',             'システム情報',         'システム情報',           'システム全体の設定、運用状況を管理します。'),
(303,   300,          2,        'admin_menu', 'mainte',               0,               true,           '',             'メンテナンス',         'メンテナンス',           'ファイルやDBなどのメンテナンスを行います。'),
(399,   0,            5,        'admin_menu', '_399',                 1,               true,           '',             '改行',                 '',                       ''),
(500,   0,            6,        'admin_menu', '_daily',               0,               true,           '',             '日常処理',             '',                       ''),
(501,   500,          0,        'admin_menu', 'configwidget_ec_main', 0,               true,           'task=order',   '受注管理',             '受注管理',               '受注管理を行います。'),
(502,   500,          1,        'admin_menu', 'configwidget_ec_main', 0,               true,           'task=product', '商品管理',             '商品管理',               '商品管理を行います。'),
(503,   500,          2,        'admin_menu', 'configwidget_ec_main', 0,               true,           'task=member',  '会員管理',             '会員管理',               '会員情報を管理します。');
