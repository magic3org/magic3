-- *
-- * 基本テーブルデータ登録スクリプト
-- *
-- * PHP versions 5
-- *
-- * LICENSE: This source file is licensed under the terms of the GNU General Public License.
-- *
-- * @package    Magic3 Framework
-- * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
-- * @copyright  Copyright 2006-2018 Magic3 Project.
-- * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
-- * @version    SVN: $Id$
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- 基本テーブルデータ登録スクリプト
-- ベースシステム(フレームワーク)で最小限必要な初期データの登録を行う
-- --------------------------------------------------------------------------------------------------

-- システム設定マスター
-- システムの動作に影響する設定を管理する
INSERT INTO _system_config 
(sc_id,                          sc_value,                  sc_name) VALUES
('system_name',                 'Magic3',                   'システム名称'),
('db_version',                  '2018110101',               'DBバージョン'),
('server_id',                   '',                         'サーバ識別用ID'),
('server_no',                   '-1',                       'サーバ管理No'),
('server_admin_max_server_no',  '0',                        '最大サーバ管理番号(サイト管理用)'),
('realtime_server_port',        '',                         'リアルタイムサーバポート番号'),
('server_url',                  '',                         'サーバURL'),
('server_dir',                  '',                         'サーバディレクトリ'),
('system_type',                 '',                         'システム運用タイプ'),  -- serveradmin=サーバ運用
('default_lang',                'ja',                       'デフォルト言語'),
('multi_language',               '0',                       '多言語対応'),
('accept_language',             '',                         'アクセス可能言語'),
('csv_upload_encoding',         'SJIS-win',                 'CSVアップロードエンコード'),
('csv_download_encoding',       'SJIS-win',                 'CSVダウンロードエンコード'),
('csv_delim_code',              ',',                        'CSV区切りコード'),
('csv_nl_code',                 '\r\n',                     'CSV改行コード'),
('csv_file_suffix',             '.csv',                     'CSVファイル拡張子'),
('permit_init_system',          '0',                        'システム初期化許可'),
('permit_change_lang',          '1',                        '処理言語変更許可'),
('permit_detail_config',        '0',                        'システム詳細設定許可'),
('regenerate_session_id',       '0',                        'セッションID毎回更新'),
('script_cache_in_browser',     '1',                        'ブラウザのスクリプトキャッシュ'),
('hierarchical_page',           '0',                        'ページの階層化'),
('site_in_public',              '1',                        'サイト公開'),
('site_pc_in_public',           '1',                        'PC用サイト公開'),
('site_smartphone_in_public',   '1',                        'スマートフォン用サイト公開'),
('system_manager_enable_task', 'top,userlist_detail,loginhistory', 'システム運用者が実行可能な管理画面タスク'),
('site_access_exception_ip',    '',                         'サイトアクセス制御なしIP'),
('toppage_image_path',          '',                         '管理画面トップページ画像パス'),
('install_dt',                  '',                         'インストール日時'),
('log_dir',                     '',                         'ログ出力ディレクトリ'),
('work_dir',                    '',                         '作業用ディレクトリ'),
('access_in_intranet',          '0',                        'イントラネット運用'),
('site_operation_mode',         '0',                        'サイト運用モード'),
('multi_device_admin',          '0',                        'マルチデバイス最適化管理画面'),
('default_menu_def_type',       '1',                        'デフォルトのメニュー定義画面タイプ'),  -- 0=多階層,1=単階層(廃止予定)
('site_menu_hier',              '1',                        'サイトのメニューを階層化するかどうか'),
('default_template',            'art42_sample5',            'PCフロント画面用デフォルトテンプレート'),
('admin_default_template',      '_admin',                   '管理画面用デフォルトテンプレート'),
('smartphone_default_template', 's/default_jquery',         'スマートフォン画面用デフォルトテンプレート'),
('default_sub_template',        '',                         'PCフロント画面用デフォルトサブテンプレート'),
('msg_template',                '_system',                  'メッセージ表示用テンプレート'),
('use_content_maintenance',     '0',                        'メンテナンス画面用コンテンツの取得'),
('use_content_access_deny',     '0',                        'アクセス不可画面用コンテンツの取得'),
('external_jquery',             '0',                        'システム外部のjQueryを使用'),
('default_theme',               'smoothness',               'フロント画面用jQueryUIテーマ'),
('admin_default_theme',         'smoothness',               '管理画面用jQueryUIテーマ'),
('jquery_version',               '1.9',                     'jQueryバージョン(PC用)'),
('admin_jquery_version',         '1.9',                     '管理画面用jQueryバージョン'),
('head_title_format',           '$1;$1 - $2;$1 - $2 - $3;', 'HTMLヘッダタイトルフォーマット'),
('default_h_tag_level',         '2',                        'ウィジェットのHタグレベル'),
('smartphone_auto_redirect',    '0',                        'スマートフォンアクセスの自動遷移'),
('distribution_name',            'magic3.org',              'ディストリビューション名'),
('distribution_version',         '',                        'ディストリビューションバージョン'),
('use_ssl',                      '0',                       'SSL通信'),
('ssl_root_url',                 '',                        'SSL用のルートURL'),
('use_ssl_admin',                '0',                       '管理画面のSSL通信'),
('use_page_cache',               '0',                       '画面キャッシュ'),
('page_cache_lifetime',          '1440',                    '画面キャッシュの保持時間(分)'),
('use_connect_server',           '1',                       'ポータルサーバ接続'),
('default_connect_server_url',   '',                        'デフォルトの連携サーバURL'),
('portal_server_version',        '',                        'ポータルサーババージョン'),
('portal_server_url',            'http://magic3.me',        'ポータルサーバURL'),
('site_registered_in_portal',    '0',                       'ポータルサーバへのサイトの登録状況'),
('config_window_style',          'toolbar=no,menubar=no,location=no,status=no,scrollbars=yes,resizable=yes,width=1050,height=900',               '設定画面の表示属性'),
('dev_use_latest_script_lib',    '0',                       '最新JavaScriptライブラリの使用(開発用)'),
('google_api_key',               '',                        'GoogleAPIキー'),
('google_maps_key',              '',                        'Googleマップ利用キー'),
('upload_image_autoresize',      '1',                       'アップロード画像の自動リサイズ'),
('upload_image_autoresize_max_width',  '5000',              'アップロード画像自動リサイズの最大幅'),
('upload_image_autoresize_max_height', '5000',              'アップロード画像自動リサイズの最大高さ'),
('fix_ie6_transparent_png',      '0',                       'IE6の透過PNG対応'),
('site_logo_filename', 'sm=logo_80c.png;lg=logo_200c.png',  'サイトロゴファイル名(廃止)'),
('site_logo_format',   'sm=80c.png;lg=200c.png',            'サイトロゴ仕様'),
('thumb_format', '72c.jpg;80c.jpg;80x60c.jpg;160x120c.jpg;200x150c.jpg;200c.jpg',   'コンテンツ用サムネール仕様'),
('avatar_format',      'sm=32c.png;md=80c.png;lg=128c.png', 'アバター仕様'),
('ogp_thumb_format',             '200c.jpg',   'OGP用サムネール仕様'),
('wysiwyg_editor',               'ckeditor',   'WYSIWYGエディター'),
('site_smartphone_url',          '',                        'スマートフォン用サイトURL'),
('multi_domain',                 '0',                       'マルチドメイン運用'),
('use_landing_page',             '0',                        'ランディングページ機能を使用するかどうか'),
('auto_login',                   '1',                        'フロント画面自動ログイン機能'),
('auto_login_admin',             '0',                        '管理画面自動ログイン機能'),
('server_tools_user',            '',                         '管理ツールアカウント'),
('server_tools_password',        '',                         '管理ツールパスワード'),
('awstats_data_path',            '../tools/awstats',         'Awstatsデータのデータパス'),
('smtp_use_server',              '0',                        'SMTP外部サーバを使用するかどうか'),
('smtp_host',                    '',                         'SMTPホスト名'),
('smtp_port',                    '587',                      'SMTPポート番号'),
('smtp_encrypt_type',            'tls',                      'SMTP暗号化タイプ'),
('smtp_authentication',          '1',                        'SMTP認証'),
('smtp_account',                 '',                         'SMTP接続アカウント'),
('smtp_password',                '',                         'SMTPパスワード'),
('default_content_type',       'blog',         'デフォルトコンテンツタイプ'),                -- WordPressテンプレートで使用(現在未使用)
('default_menu_id',            'main_menu',    'フロント画面用デフォルトメニューID'),        -- WordPressテンプレートで使用(現在未使用)
('smartphone_default_menu_id', 's_main_menu',  'スマートフォン画面用デフォルトメニューID');  -- WordPressテンプレートで使用(現在未使用)

-- バージョン管理マスター
INSERT INTO _version (vs_id,         vs_value,     vs_name)
VALUES               ('basic_table', '2008013001', '基本テーブルのバージョン');

-- ログインユーザマスター
INSERT INTO _login_user
(lu_id, lu_account, lu_password,  lu_name,  lu_user_type, lu_assign, lu_create_dt) VALUES
(1,     'admin',    md5('admin'), '管理者', 100,          'sy,',     now());

-- 追加クラスマスター
INSERT INTO _addons
(ao_id,        ao_class_name, ao_name,                    ao_description, ao_opelog_hook) VALUES
('bloglib',    'blogLib',     'ブログライブラリ',         '',             false),
('contentlib', 'contentLib',  '汎用コンテンツライブラリ', '',             false),
('newslib',    'newsLib',     '新着情報ライブラリ',       '',             true),
('wikilib',    'wikiLib',     'Wikiライブラリ',           '',             false),
('linkinfo',   'linkInfo',    'リンク情報',               '',             false),
('eventlib',   'eventLib',    'イベント情報ライブラリ',   '',             false);

-- 管理画面メニューデータ
INSERT INTO _nav_item
(ni_id, ni_parent_id, ni_index, ni_nav_id,       ni_task_id,           ni_view_control, ni_visible, ni_param, ni_hide_option,   ni_name,                ni_help_title,          ni_help_body) VALUES
(100,   0,            0,        'admin_menu',    '_page',              0,               true,       '',       'site_operation', '画面管理',             '画面管理',             'Webサイトのデザインや機能を管理します。'),
(101,   100,          0,        'admin_menu',    'pagedef',            0,               true,       '',       '',               'PC画面',         'PC画面編集',         'PC用Webサイトの画面を作成します。'),
(102,   100,          1,        'admin_menu',    'pagedef_smartphone', 0,               false,      '',       '',               'スマートフォン画面', 'スマートフォン画面編集',       'スマートフォン用Webサイトの画面を作成します。'),
(103,   100,          2,        'admin_menu',    '_103',               3,               true,       '',       '',               'セパレータ',                 '',                     ''),
(104,   100,          3,        'admin_menu',    'widgetlist',         0,               true,       '',       '',               'ウィジェット管理',     'ウィジェット管理',     'ウィジェットの管理を行います。'),
(105,   100,          4,        'admin_menu',    'templist',           0,               true,       '',       '',               'テンプレート管理',     'テンプレート管理',     'テンプレートの管理を行います。'),
(106,   100,          5,        'admin_menu',    'smenudef',           0,               true,       '',       '',               'メニュー管理', 'メニュー管理', 'メニュー定義を管理します。'),
(199,   0,            1,        'admin_menu',    '_199',               1,               true,       '',       '',               '改行',                 '',                     ''),
(200,   0,            2,        'admin_menu',    '_login',             0,               true,       '',       '',               'システム運用',         '',                     ''),
(201,   200,          0,        'admin_menu',    'userlist',           0,               true,       '',       '',               'ユーザ管理',           'ユーザ管理',           'ログイン可能なユーザを管理します。'),
(202,   200,          1,        'admin_menu',    'accesslog',          0,               true,       '',       '',               '運用状況', '運用状況', 'サイトの運用状況を表示します。'),
(299,   0,            3,        'admin_menu',    '_299',               1,               true,       '',       '',               '改行',                 '',                     ''),
(300,   0,            4,        'admin_menu',    '_config',            0,               true,       '',       'site_operation', 'システム管理',         '',                     ''),
(301,   300,          0,        'admin_menu',    'configsite',         0,               true,       '',       '',               '基本情報',             '基本情報',             'サイト運営に必要な情報を設定します。'),
(302,   300,          1,        'admin_menu',    'configsys',          0,               true,       '',       '',               'システム情報',         'システム情報',         'システム全体の設定、運用状況を管理します。'),
(303,   300,          2,        'admin_menu',    'mainte',             0,               true,       '',       '',               'メンテナンス', 'メンテナンス', 'ファイルやDBなどのメンテナンスを行います。'),
(10100, 0,            0,        'admin_menu.en', '_page',              0,               true,       '',       'site_operation', 'Edit Page',             'Edit Page',             'Edit page for design and function.'),
(10101, 10100,        0,        'admin_menu.en', 'pagedef',            0,               true,       '',       '',               'PC Page',         'PC Page',         'Edit page for PC.'),
(10102, 10100,        1,        'admin_menu.en', 'pagedef_smartphone', 0,               false,      '',       '',               'Smartphone Page', 'Smartphone Page',       'Edit page for Smartphone.'),
(10103, 10100,        2,        'admin_menu.en', '_10103',             3,               true,       '',       '',               'Separator',                 '',                     ''),
(10104, 10100,        3,        'admin_menu.en', 'widgetlist',         0,               true,       '',       '',               'Widget Administration',     'Widget Administration',     'Administrate widgets with widget config window.'),
(10105, 10100,        4,        'admin_menu.en', 'templist',           0,               true,       '',       '',               'Template Administration',     'Template Administration',     'Administrate templates.'),
(10106, 10100,        5,        'admin_menu.en', 'smenudef',           0,               true,       '',       '',               'Menu Administration', 'Menu Administration', 'Administrate menu definition.'),
(10199, 0,            1,        'admin_menu.en', '_10199',             1,               true,       '',       '',               'Return',                 '',                     ''),
(10200, 0,            2,        'admin_menu.en', '_login',             0,               true,       '',       '',               'System Operation',         '',                     ''),
(10201, 10200,        0,        'admin_menu.en', 'userlist',           0,               true,       '',       '',               'User List',           'User List',           'Administrate user to login.'),
(10202, 10200,        1,        'admin_menu.en', 'accesslog',          0,               true,       '',       '',               'Site Conditions', 'Site Conditions', 'Operation log and access analytics on site.'),
(10299, 0,            3,        'admin_menu.en', '_10299',             1,               true,       '',       '',               'Return',                 '',                     ''),
(10300, 0,            4,        'admin_menu.en', '_config',            0,               true,       '',       'site_operation', 'System Administration',         '',                     ''),
(10301, 10300,        0,        'admin_menu.en', 'configsite',         0,               true,       '',       '',               'Site Information',             'Site Information',             'Configure site information.'),
(10302, 10300,        1,        'admin_menu.en', 'configsys',          0,               true,       '',       '',               'System Information',         'System Information',         'Configure sytem information.'),
(10303, 10300,        2,        'admin_menu.en', 'mainte',             0,               true,       '',       '',               'System Maintenance', 'System Maintenance', 'Maintenance about file system and database.');

-- ページIDマスター
INSERT INTO _page_id 
(pg_id,          pg_type, pg_default_sub_id, pg_path,       pg_name,                            pg_description,                       pg_priority, pg_device_type, pg_active, pg_visible, pg_mobile, pg_editable, pg_admin_menu, pg_frontend) VALUES
('index',        0,       'content',         'index',       'PC用アクセスポイント',             'PC用アクセスポイント',               0,           0,              true,      true,       false,     true,        true,          true),
('s_index',      0,       'content',           's/index',     'スマートフォン用アクセスポイント', 'スマートフォン用アクセスポイント',   1,           2,              false,     true,       false,     true,        false,         true),
('admin_index',  0,       'content',         'admin/index', '管理用アクセスポイント',           '管理用アクセスポイント',             3,           0,              true,      true,       false,     false,       false,         false),
('connector',    0,       'content',         'connector',   'サーバ接続用アクセスポイント',     'サーバ接続用アクセスポイント',       4,           0,              true,      true,       false,     false,       false,         false);
INSERT INTO _page_id 
(pg_id,          pg_type,      pg_name,                            pg_description,                       pg_priority, pg_active, pg_visible, pg_editable, pg_function_type) VALUES
('front',        1,            'トップ画面',                       'トップ画面用',                       0,           true,      true,      true,        ''),
('content',      1,            'コンテンツ',                       'コンテンツ画面用',                   1,           true,      true,       false,       ''),
('shop',         1,            'ECショップ',                       'ECショップ画面用',                   2,           false,     true,      true,        ''),
('shop_safe',    1,            'ECショップ(セキュリティ保護)',     'ECショップ(セキュリティ保護)画面用', 3,           false,     true,      true,        ''),
('bbs',          1,            '掲示板',                           '掲示板画面用',                       4,           false,     true,      true,        ''),
('blog',         1,            'ブログ',                           'ブログ画面用',                       5,           true,      true,      true,        ''),
('wiki',         1,            'Wiki',                             'Wiki画面用',                         6,           false,     true,      true,        ''),
('calendar',     1,            'カレンダー',                       'カレンダー画面用',                   7,           false,     true,      true,        ''),
('event',        1,            'イベント情報',                     'イベント情報画面用',                 8,           false,     true,      true,        ''),
('photo',        1,            'フォトギャラリー',                 'フォトギャラリー画面用',             9,           false,     true,      true,        ''),
('contact',      1,            'お問い合わせ',                     'お問い合わせ画面用',                 10,          true,      true,      true,        ''),
('contact2',     1,            'お問い合わせ2',                    'お問い合わせ画面用',                 11,          false,     true,      true,        ''),
('reguser',      1,            'ユーザ登録',                       'ユーザ登録画面用',                   12,          false,     true,      true,        ''),
('reserve',      1,            '予約',                             '予約画面用',                         19,          false,     true,      true,        ''),
('member',       1,            '会員',                             '会員画面用',                         20,          false,     true,      true,        ''),
('evententry',   1,            'イベント予約',                     'イベント予約画面用',                 21,          false,     true,      true,        ''),
('search',       1,            '検索',                             '検索画面用',                         22,          true,      true,      true,        ''),
('deploy',       1,            '[ウィジェット有効化用]',           'ウィジェット有効化用',               100,         false,     false,     true,        'activate');

-- ページ情報マスター
INSERT INTO _page_info
(pn_id,       pn_sub_id,   pn_content_type, pn_use_ssl) VALUES
('index',     'content',   'content',       false),
('index',     'shop',      'product',       false),
('index',     'shop_safe', 'commerce',      true),
('index',     'bbs',       'bbs',           false),
('index',     'blog',      'blog',          false),
('index',     'wiki',      'wiki',          false),
('index',     'calendar',  'calendar',      false),
('index',     'event',     'event',         false),
('index',     'photo',     'photo',         false),
('index',     'member',    'member',        true),
('index',     'evententry','evententry',    false),
('index',     'search',    'search',        false),
('index',     'contact',   '',              true),
('index',     'contact2',  '',              true),
('index',     'safe',      '',              true),
('s_index',   'content',   'content',       false),
('s_index',   'shop',      'product',       false),
('s_index',   'shop_safe', 'commerce',      true),
('s_index',   'bbs',       'bbs',           false),
('s_index',   'blog',      'blog',          false),
('s_index',   'wiki',      'wiki',          false),
('s_index',   'calendar',  'calendar',      false),
('s_index',   'event',     'event',         false),
('s_index',   'photo',     'photo',         false),
('s_index',   'member',    'member',        true),
('s_index',   'evententry','evententry',    false),
('s_index',   'search',    'search',        false),
('s_index',   'contact',   '',              true),
('s_index',   'contact2',  '',              true),
('s_index',   'safe',      '',              true),
('admin_index', 'front',   'dboard',        false),
('connector', 'content',   'content',       false);

-- ページ定義マスター
INSERT INTO _page_def
(pd_id,         pd_sub_id,      pd_position_id, pd_index, pd_widget_id,          pd_config_id, pd_visible, pd_editable, pd_title_visible, pd_visible_condition) VALUES
('admin_index', '',             'top',          1,        'admin_menu',          0,            true,       false,       false,            ''),
('admin_index', 'front',        'top',          2,        'admin/message',       0,            true,       false,       false,            ''),
('admin_index', 'front',        'main',         1,        'admin_main',          0,            true,       false,       false,            ''),
('admin_index', 'front',        'main',         2,        'admin/analytics',     0,            true,       true,        false,            ''),
('admin_index', 'front',        'main',         3,        'admin/opelog',        0,            true,       true,        false,            ''),
('admin_index', 'front',        'left',         1,        'admin/loginuser',     0,            true,       true,        true,             ''),
('admin_index', 'content',      'main',         1,        'admin_main',          0,            true,       false,       false,            ''),
('admin_index', 'content',      'left',         1,        'admin/remotecontent', 0,            true,       true,        true,             'task=dummy'),
('admin_index', 'content',      'right',        1,        'admin/remotecontent', 0,            true,       true,        true,             'task=help');

-- サイト定義マスター
INSERT INTO _site_def
(sd_id,                  sd_language_id, sd_value,         sd_name) VALUES
('head_description',     'ja',           '',               'HTMLヘッダdescription'),
('head_keywords',        'ja',           '',               'HTMLヘッダkeywords'),
('head_robots',          'ja',           '',               'HTMLヘッダrobots'),
('head_title',           'ja',           '',               'HTMLヘッダtitle'),
('head_others',          'ja',           '<meta property="og:type" content="website" /><meta property="og:url" content="[#SITE_URL#]" /><meta property="og:image" content="[#SITE_IMAGE#]" /><meta property="og:title" content="[#SITE_NAME#]" /><meta property="og:description" content="[#SITE_DESCRIPTION#]" />',               'HTMLヘッダその他'),
('site_name',            'ja',           '',               'サイト名'),
('site_owner',           'ja',           '',               'サイト所有者'),
('site_copyright',       'ja',           '',               'サイト著作権'),
('site_email',           'ja',           '',               'サイトeメール'),
('license_key',          'ja',           '',               'ライセンスキー'),
('license_email',        'ja',           '',               'ライセンスeメール'),
('license_name',         'ja',           '',               'ライセンス登録者名'),
('license_zipcode',      'ja',           '',               'ライセンス登録住所(郵便番号)'),
('license_address',      'ja',           '',               'ライセンス登録住所(住所)'),
('license_fax',          'ja',           '',               'ライセンス登録住所(FAX)'),
('msg_site_maintenance', 'ja',           'ただいまメンテナンス中です', 'サイトメンテナンスメッセージ');

-- 多言語対応文字列マスター
INSERT INTO _language_string
(ls_type, ls_id,                           ls_language_id, ls_value,                             ls_name) VALUES
(0,       'msg_site_in_maintenance',       'ja',           'ただいまサイトのメンテナンス中です。<br />もうしばらくお待ちください。', 'メンテナンス中メッセージ'),
(0,       'msg_access_deny',               'ja',           'アクセスできません',                 'アクセス不可メッセージ'),
(0,       'msg_page_not_found',            'ja',           'ページが見つかりません',             '存在しないページメッセージ'),
(0,       'msg_admin_popup_login',         'ja',           '',                                   'ログイン時管理者向けポップアップメッセージ'),
(1,       'word_account',                  'ja',           'ID(Eメール)',                        'アカウント'),
(2,       'dboard',      'ja',           'ダッシュボード',       'ダッシュボード'),
(2,       'search',      'ja',           '検索結果',             '検索結果'),
(2,       'news',        'ja',           '新着情報',             '新着情報'),
(2,       'commerce',    'ja',           'Eコマース',            'Eコマース'),
(2,       'content',     'ja',           '汎用コンテンツ',       '汎用コンテンツ'),
(2,       'product',     'ja',           '商品情報',             '商品情報'),
(2,       'bbs',         'ja',           'BBS',                  'BBS'),
(2,       'blog',        'ja',           'ブログ',               'ブログ'),
(2,       'wiki',        'ja',           'Wiki',                 'Wiki'),
(2,       'event',       'ja',           'イベント情報',         'イベント情報'),
(2,       'photo',       'ja',           'フォトギャラリー',     'フォトギャラリー'),
(10,      'COM_CONTENT_CREATED_DATE_ON',   'ja',           '作成日：%s',         ''),
(10,      'COM_CONTENT_LAST_UPDATED',      'ja',           '更新日：%s',         ''),
(10,      'COM_CONTENT_PUBLISHED_DATE_ON', 'ja',           '公開日：%s',         ''),
(10,      'COM_CONTENT_WRITTEN_BY',        'ja',           '作成者：%s',           ''),
(10,      'COM_CONTENT_CATEGORY',          'ja',           'カテゴリー：%s',     ''),
(10,      'COM_CONTENT_ARTICLE_HITS',      'ja',           'ヒット数：%s',     ''),
(10,      'COM_CONTENT_READ_MORE',         'ja',           'もっと読む: ',       ''),
(10,      'COM_CONTENT_READ_MORE_TITLE',   'ja',           'もっと読む',         ''),
(10,      'COM_CONTENT_PREV',              'ja',           '前',                 ''),
(10,      'COM_CONTENT_NEXT',              'ja',           '次',                 ''),
(10,      'COM_CONTENT_START',             'ja',           '最初',               ''),
(10,      'COM_CONTENT_END',               'ja',           '最後',               ''),
(10,      'COM_CONTENT_PAGE_CURRENT_OF_TOTAL', 'ja',       'ページ番号 %s 総数 %s',  ''),
(10,      'DATE_FORMAT_LC',                'ja',           'Y年Fd日（l）',       ''),
(10,      'DATE_FORMAT_LC1',               'ja',           'Y年Fd日（l）',       ''),
(10,      'DATE_FORMAT_LC2',               'ja',           'Y年Fd日（l）H:i',    ''),
(10,      'DATE_FORMAT_LC3',               'ja',           'Y年Fd日',            ''),
(10,      'DATE_FORMAT_LC4',               'ja',           'Y-m-d',              ''),
(10,      'DATE_FORMAT_JS1',               'ja',           'y-m-d',              ''),
(10,      'JANUARY_SHORT',                 'ja',           '1月',                ''),
(10,      'JANUARY',                       'ja',           '1月',                ''),
(10,      'FEBRUARY_SHORT',                'ja',           '2月',                ''),
(10,      'FEBRUARY',                      'ja',           '2月',                ''),
(10,      'MARCH_SHORT',                   'ja',           '3月',                ''),
(10,      'MARCH',                         'ja',           '3月',                ''),
(10,      'APRIL_SHORT',                   'ja',           '4月',                ''),
(10,      'APRIL',                         'ja',           '4月',                ''),
(10,      'MAY_SHORT',                     'ja',           '5月',                ''),
(10,      'MAY',                           'ja',           '5月',                ''),
(10,      'JUNE_SHORT',                    'ja',           '6月',                ''),
(10,      'JUNE',                          'ja',           '6月',                ''),
(10,      'JULY_SHORT',                    'ja',           '7月',                ''),
(10,      'JULY',                          'ja',           '7月',                ''),
(10,      'AUGUST_SHORT',                  'ja',           '8月',                ''),
(10,      'AUGUST',                        'ja',           '8月',                ''),
(10,      'SEPTEMBER_SHORT',               'ja',           '9月',                ''),
(10,      'SEPTEMBER',                     'ja',           '9月',                ''),
(10,      'OCTOBER_SHORT',                 'ja',           '10月',               ''),
(10,      'OCTOBER',                       'ja',           '10月',               ''),
(10,      'NOVEMBER_SHORT',                'ja',           '11月',               ''),
(10,      'NOVEMBER',                      'ja',           '11月',               ''),
(10,      'DECEMBER_SHORT',                'ja',           '12月',               ''),
(10,      'DECEMBER',                      'ja',           '12月',               ''),
(10,      'SUN',                           'ja',           '日',                 ''),
(10,      'SUNDAY',                        'ja',           '日曜',               ''),
(10,      'MON',                           'ja',           '月',                 ''),
(10,      'MONDAY',                        'ja',           '月曜',               ''),
(10,      'TUE',                           'ja',           '火',                 ''),
(10,      'TUESDAY',                       'ja',           '火曜',               ''),
(10,      'WED',                           'ja',           '水',                 ''),
(10,      'WEDNESDAY',                     'ja',           '水曜',               ''),
(10,      'THU',                           'ja',           '木',                 ''),
(10,      'THURSDAY',                      'ja',           '木曜',               ''),
(10,      'FRI',                           'ja',           '金',                 ''),
(10,      'FRIDAY',                        'ja',           '金曜',               ''),
(10,      'SAT',                           'ja',           '土',                 ''),
(10,      'SATURDAY',                      'ja',           '土曜',               ''),
(10,      'LAST_UPDATED2',                 'ja',           '更新日 %s',          '旧バージョン互換用'),
(10,      'COM_CONTENT_CREATED_DATE_ON',   'en',           'Created: %s',        ''),
(10,      'COM_CONTENT_LAST_UPDATED',      'en',           'Last Updated: %s',   ''),
(10,      'COM_CONTENT_PUBLISHED_DATE_ON', 'en',           'Published: %s',      ''),
(10,      'COM_CONTENT_WRITTEN_BY',        'en',           'Written by %s',      ''),
(10,      'COM_CONTENT_CATEGORY',          'en',           'Category: %s',       ''),
(10,      'COM_CONTENT_ARTICLE_HITS',      'en',           'Hits: %s',           ''),
(10,      'COM_CONTENT_READ_MORE',         'en',           'Read more: ',        ''),
(10,      'COM_CONTENT_READ_MORE_TITLE',   'en',           'Read more...',       ''),
(10,      'COM_CONTENT_PREV',              'en',           'Prev',               ''),
(10,      'COM_CONTENT_NEXT',              'en',           'Next',               ''),
(10,      'COM_CONTENT_START',             'en',           'Start',              ''),
(10,      'COM_CONTENT_END',               'en',           'End',                ''),
(10,      'COM_CONTENT_PAGE_CURRENT_OF_TOTAL', 'en',       'Page %s of %s',      ''),
(10,      'DATE_FORMAT_LC',                'en',           'l, d F Y',           ''),
(10,      'DATE_FORMAT_LC1',               'en',           'l, d F Y',           ''),
(10,      'DATE_FORMAT_LC2',               'en',           'l, d F Y H:i',       ''),
(10,      'DATE_FORMAT_LC3',               'en',           'd F Y',              ''),
(10,      'DATE_FORMAT_LC4',               'en',           'Y-m-d',              ''),
(10,      'DATE_FORMAT_JS1',               'en',           'y-m-d',              ''),
(10,      'JANUARY_SHORT',                 'en',           'Jan',                ''),
(10,      'JANUARY',                       'en',           'January',            ''),
(10,      'FEBRUARY_SHORT',                'en',           'Feb',                ''),
(10,      'FEBRUARY',                      'en',           'February',           ''),
(10,      'MARCH_SHORT',                   'en',           'Mar',                ''),
(10,      'MARCH',                         'en',           'March',              ''),
(10,      'APRIL_SHORT',                   'en',           'Apr',                ''),
(10,      'APRIL',                         'en',           'April',              ''),
(10,      'MAY_SHORT',                     'en',           'May',                ''),
(10,      'MAY',                           'en',           'May',                ''),
(10,      'JUNE_SHORT',                    'en',           'Jun',                ''),
(10,      'JUNE',                          'en',           'June',               ''),
(10,      'JULY_SHORT',                    'en',           'Jul',                ''),
(10,      'JULY',                          'en',           'July',               ''),
(10,      'AUGUST_SHORT',                  'en',           'Aug',                ''),
(10,      'AUGUST',                        'en',           'August',             ''),
(10,      'SEPTEMBER_SHORT',               'en',           'Sep',                ''),
(10,      'SEPTEMBER',                     'en',           'September',          ''),
(10,      'OCTOBER_SHORT',                 'en',           'Oct',                ''),
(10,      'OCTOBER',                       'en',           'October',            ''),
(10,      'NOVEMBER_SHORT',                'en',           'Nov',                ''),
(10,      'NOVEMBER',                      'en',           'November',           ''),
(10,      'DECEMBER_SHORT',                'en',           'Dec',                ''),
(10,      'DECEMBER',                      'en',           'December',           ''),
(10,      'SUN',                           'en',           'Sun',                ''),
(10,      'SUNDAY',                        'en',           'Sunday',             ''),
(10,      'MON',                           'en',           'Mon',                ''),
(10,      'MONDAY',                        'en',           'Monday',             ''),
(10,      'TUE',                           'en',           'Tue',                ''),
(10,      'TUESDAY',                       'en',           'Tuesday',            ''),
(10,      'WED',                           'en',           'Wed',                ''),
(10,      'WEDNESDAY',                     'en',           'Wednesday',          ''),
(10,      'THU',                           'en',           'Thu',                ''),
(10,      'THURSDAY',                      'en',           'Thursday',           ''),
(10,      'FRI',                           'en',           'Fri',                ''),
(10,      'FRIDAY',                        'en',           'Friday',             ''),
(10,      'SAT',                           'en',           'Sat',                ''),
(10,      'SATURDAY',                      'en',           'Saturday',           ''),
(10,      'LAST_UPDATED2',                 'en',           'Last Updated on %s', '旧バージョン互換用');

-- --------------------------------------------------------------------------------------------------
-- 以下、変更の少ないデータ
-- --------------------------------------------------------------------------------------------------

-- 言語マスター
INSERT INTO _language
(ln_id, ln_name,          ln_name_en,   ln_priority, ln_image_filename) VALUES
('ja',  '日本語',         'Japanese',   0,           'jp.png'),
('en',  '英語',           'English',    1,           'gb.png'),
('de',  'ドイツ語',       'German',     2,           'de.png'),
('fr',  'フランス語',     'French',     3,           'fr.png'),
('es',  'スペイン語',     'Spanish',    4,           'es.png'),
('it',  'イタリア語',     'Italian',    5,           'it.png'),
('pt',  'ポルトガル語',   'Portuguese', 6,           'pt.png'),
('zh',  '中国語',         'Chinese',    7,           'cn.png'),
('ko',  '韓国語',         'Korean',     8,           'kr.png'),
('th',  'タイ語',         'Thai',       9,           'th.png'),
('id',  'インドネシア語', 'Indonesian', 10,          'id.png'),
('ru',  'ロシア語',       'Russian',    11,          'ru.png'),
('ar',  'アラビア語',     'Arabic',     12,          'ae.png'),
('zh-cn',  '中国語(簡体)',         'Chinese(Simplified)',    20,           'cn.png'),
('zh-tw',  '中国語(繁体)',         'Chinese(Traditional)',    21,           'tw.png');

-- テンプレート表示位置マスター
INSERT INTO _template_position
(tp_id,       tp_name,     tp_description, tp_sort_order) VALUES
('main',      'main',      '',             0),
('left',      'left',      '',             1),
('right',     'right',     '',             2),
('center',    'center',    '',             3),
('top',       'top',       '',             4),
('bottom',    'bottom',    '',             5),
('header',    'header',    '',             6),
('footer',    'footer',    '',             7),
('navi',      'navi',      '',             8),
('inset',     'inset',     '',             9),
('banner',    'banner',    '',             10),
('newsflash', 'newsflash', '',             11),
('legals',    'legals',    '',             12),
('pathway',   'pathway',   '',             13),
('toolbar',   'toolbar',   '',             14),
('cpanel',    'cpanel',    '',             15),
('user1',     'user1',     '',             16),
('user2',     'user2',     '',             17),
('user3',     'user3',     '',             18),
('user4',     'user4',     '',             19),
('user5',     'user5',     '',             20),
('advert1',   'advert1',   '',             21),
('advert2',   'advert2',   '',             22),
('advert3',   'advert3',   '',             23),
('advert4',   'advert4',   '',             24),
('advert5',   'advert5',   '',             25),
('icon',      'icon',      '',             26),
('debug',     'debug',     'デバッグ用',   27);

-- 運用メッセージタイプマスター
INSERT INTO _operation_type
(ot_id,          ot_name,                ot_description,                               ot_level, ot_sort_order) VALUES
('info',         'システム情報',         'システム運用の正常な動作を示します',         0,        1),
('request',      'システム操作要求',     'システムからの操作要求を示します',           1,        2),
('warn',         'システム警告',         'システム運用の注意が必要な動作を示します',   2,        3),
('error',        'システム通常エラー',   'システム運用の異常な動作を示します',         10,       4),
('fatal',        'システム致命的エラー', 'システム運用の致命的に異常な動作を示します', 10,       5),
('user_info',    'ユーザ操作',           'ユーザ操作の正常な動作を示します',           0,        6),
('user_request', 'ユーザ操作要求',       'ユーザ操作からの操作要求を示します',         1,        7),
('user_err',     'ユーザ操作エラー',     'ユーザ操作の異常な動作を示します',           10,       8),
('user_access',  'ユーザ不正アクセス',   'ユーザ操作の不正なアクセスを示します',       10,       9),
('user_data',    'ユーザ不正データ',     'ユーザ操作の不正なデータ送信を示します',     10,       10),
('guide',        'ガイダンス',           'システム運用に関連しないメッセージを示します',         -1,        11);

-- ウィジェットカテゴリマスター
INSERT INTO _widget_category
(wt_id,         wt_parent_id,  wt_name,                wt_sort_order, wt_visible) VALUES
('',            '',            'その他',               100,           true),
('content',     'content',     '汎用コンテンツ',       1,             true),
('blog',        'blog',        'ブログ',               2,             true),
('bbs',         'bbs',         'BBS',                  3,             false),
('commerce',    'commerce',    'Eコマース',            4,             false),
('photo',       'photo',       'フォトギャラリー',     5,             false),
('event',       'event',       'イベント情報',         6,             false),
('wiki',        'wiki',        'Wiki',                 7,             false),
('member',      'member',      '会員',                 9,             false),
('subcontent',  'subcontent',  '補助コンテンツ',       20,            true),
('searchform/', 'searchform/', '検索・お問い合わせ',   21,            true),
('search',      'searchform/', '検索',                 22,            true),
('form',        'searchform/', 'お問い合わせ',         23,            true),
('menu',        'menu',        'メニュー',             24,            true),
('image',       'image',       '画像',                 25,            true),
('design',      'design',      'デザイン',             26,            true),
('admin',       'admin',       '管理画面用',           50,            true);
