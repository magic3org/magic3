-- *
-- * データ登録スクリプト「Eコマース標準管理メニュー」
-- *
-- * PHP versions 5
-- *
-- * LICENSE: This source file is licensed under the terms of the GNU General Public License.
-- *
-- * @package    Magic3 Framework
-- * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
-- * @copyright  Copyright 2006-2012 Magic3 Project.
-- * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
-- * @version    SVN: $Id: ec.sql 5857 2013-03-24 23:24:31Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- [Eコマース標準管理メニュー]
-- Eコマース向けに構成した標準管理メニュー
-- 階層型メニュー管理

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
(106,   100,          5,        'admin_menu', 'menudef',                      '',          0,               '',       'メニュー管理', 'メニュー管理', 'メニュー定義を管理します。', true),
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
(500,   0,            3,        'admin_menu', '_daily',                        '',          0,               '',           '日常処理', '', '', true),
(501,   500,          0,        'admin_menu', 'configwidget_ec_main',       '',             0,               'task=order', '受注管理', '受注管理', '受注管理を行います。', true),
(502,   500,          1,        'admin_menu', 'configwidget_ec_main',       '',             0,               'task=product', '商品管理', '商品管理', '商品管理を行います。', true),
(503,   500,          2,        'admin_menu', 'configwidget_ec_main',       '',             0,               'task=member',   '会員管理', '会員管理', '会員情報を管理します。', true);

