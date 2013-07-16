-- *
-- * 基本テーブルデータ登録スクリプト
-- *
-- * PHP versions 5
-- *
-- * LICENSE: This source file is licensed under the terms of the GNU General Public License.
-- *
-- * @package    Magic3 Framework
-- * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
-- * @copyright  Copyright 2006-2013 Magic3 Project.
-- * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
-- * @version    SVN: $Id: insert_base.sql 6174 2013-07-16 02:23:16Z fishbone $
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
('db_version',                  '2012090701',               'DBバージョン'),
('server_id',                   '',                         'サーバ識別用ID'),
('server_url',                  '',                         'サーバURL'),
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
('site_mobile_in_public',       '1',                        '携帯用サイト公開'),
('site_smartphone_in_public',   '1',                        'スマートフォン用サイト公開'),
('site_access_exception_ip',    '',                         'サイトアクセス制御なしIP'),
('toppage_image_path',          '',                         '管理画面トップページ画像パス'),
('mobile_encoding',             'SJIS-win',                 '携帯用出力変換エンコード'),
('mobile_charset',              'Shift_JIS',                '携帯HTML上でのエンコーディング表記'),
('install_dt',                  '',                         'インストール日時'),
('log_dir',                     '',                         'ログ出力ディレクトリ'),
('work_dir',                    '',                         '作業用ディレクトリ'),
('default_template',            'moyoo_blue_dog',           'PC一般画面用デフォルトテンプレート'),
('admin_default_template',      '_admin2',                  '管理画面用デフォルトテンプレート'),
('mobile_default_template',     'm/default',                '携帯画面用デフォルトテンプレート'),
('smartphone_default_template', 's/default_jquery',         'スマートフォン画面用デフォルトテンプレート'),
('msg_template',                '_system',                  'メッセージ表示用テンプレート'),
('use_template_id_in_session',  '1',                        'セッションにテンプレートIDを保存'),
('use_content_maintenance',     '0',                        'メンテナンス画面用コンテンツの取得'),
('use_content_access_deny',     '0',                        'アクセス不可画面用コンテンツの取得'),
('use_jquery',                  '1',                        '一般画面にjQueryを使用'),
('admin_default_theme',         'black-tie',                '管理画面用jQueryUIテーマ'),
('head_title_format',           '$1;$1 - $2;$1 - $2 - $3;', 'HTMLヘッダタイトルフォーマット'),
('mobile_auto_redirect',        '0',                        '携帯アクセスの自動遷移'),
('mobile_use_session',           '1',                       '携帯セッション管理'),
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
('config_window_open_type',      '1',                       '設定画面のウィンドウ表示タイプ'),
('config_window_style',          'toolbar=no,menubar=no,location=no,status=no,scrollbars=yes,resizable=yes,width=1050,height=900',               '設定画面の表示属性'),
('dev_use_latest_script_lib',    '0',                       '最新JavaScriptライブラリの使用(開発用)'),
('google_maps_key',              '',                        'Googleマップ利用キー'),
('fix_ie6_transparent_png',      '0',                       'IE6の透過PNG対応'),
('site_logo_filename', 'logo_72c.jpg;logo_200c.jpg',   'サイトロゴファイル名'),
('thumb_format', '72c.jpg;200c.jpg',   'サムネール仕様'),
('avatar_format',      '72c.jpg',   'アバター仕様');

-- バージョン管理マスター
INSERT INTO _version (vs_id,         vs_value,     vs_name)
VALUES               ('basic_table', '2008013001', '基本テーブルのバージョン');

-- ログインユーザマスター
INSERT INTO _login_user
(lu_id, lu_account, lu_password,  lu_name,  lu_user_type, lu_assign, lu_create_dt) VALUES
(1,     'admin',    md5('admin'), '管理者', 100,          'sy,',     now());

-- 追加クラスマスター
INSERT INTO _addons
(ao_id,     ao_class_name, ao_name,            ao_description) VALUES
('bloglib', 'blogLib',     'ブログライブラリ', '');

-- 管理画面メニューデータ
INSERT INTO _nav_item
(ni_id, ni_parent_id, ni_index, ni_nav_id,    ni_task_id,        ni_view_control, ni_param, ni_name,                ni_help_title,          ni_help_body) VALUES
(100,   0,            0,        'admin_menu', '_page',           0,               '',       '画面管理',             '画面管理',             'Webサイトのデザインや機能を管理します。'),
(101,   100,          0,        'admin_menu', 'pagedef',         0,               '',       'PC用画面',         'PC用画面編集',         'PC用Webサイトの画面を作成します。'),
(102,   100,          1,        'admin_menu', 'pagedef_mobile',  0,               '',       '携帯用画面',       '携帯用画面編集',       '携帯用Webサイトの画面を作成します。'),
(103,   100,          2,        'admin_menu', 'pagedef_smartphone',  0,           '',       'スマートフォン用画面', 'スマートフォン用画面編集',       'スマートフォン用Webサイトの画面を作成します。'),
(104,   100,          3,        'admin_menu', 'widgetlist',      0,               '',       'ウィジェット管理',     'ウィジェット管理',     'ウィジェットの管理を行います。'),
(105,   100,          4,        'admin_menu', 'templist',        0,               '',       'テンプレート管理',     'テンプレート管理',     'テンプレートの管理を行います。'),
(106,   100,          5,        'admin_menu', 'smenudef',        0,               '',       'メニュー管理', 'メニュー管理', 'メニュー定義を管理します。'),
(200,   0,            1,        'admin_menu', '_login',          0,               '',       'システム運用',         '',                     ''),
(201,   200,          0,        'admin_menu', 'userlist',        0,               '',       'ユーザ一覧',           'ユーザ一覧',           'ログイン可能なユーザを管理します。'),
(202,   200,          1,        'admin_menu', 'accesslog',     0,               '',       '運用状況', '運用状況', 'サイトの運用状況を表示します。'),
(300,   0,            2,        'admin_menu', '_config',         0,               '',       'システム管理',         '',                     ''),
(301,   300,          0,        'admin_menu', 'configsite',      0,               '',       '基本情報',             '基本情報',             'サイト運営に必要な情報を設定します。'),
(302,   300,          1,        'admin_menu', 'configsys',       0,               '',       'システム情報',         'システム情報',         'システム全体の設定、運用状況を管理します。'),
(303,   300,          2,        'admin_menu', 'resbrowse',       0,               '',       'リソースブラウズ',     'リソースブラウズ',     'リソースファイルを管理します。'),
(304,   300,          3,        'admin_menu', 'master',          0,               '',       'システムマスター管理', 'システムマスター管理', 'システムに関するマスターテーブルの管理を行います。'),
(305,   300,          4,        'admin_menu', 'initsystem',      0,               '',       'DBメンテナンス',       'DBメンテナンス',       'データの初期化などDBのメンテナンスを行います。'),
(399,   0,            3,        'admin_menu', '_399',            1,               '',       '改行',                 '',                     ''),
(1100,  0,            11,       'admin_menu', '_others',         0,               '',       'その他',               '',                     ''),
(1101,  1100,         0,        'admin_menu', 'logout',          0,               '',       'ログアウト',           'ログアウト',           '管理機能からログアウトします。'),
(10100,   0,            0,        'admin_menu.en', '_page',           0,               '',       'Edit Page',             'Edit Page',             'Edit page for design and function.'),
(10101,   10100,          0,        'admin_menu.en', 'pagedef',         0,               '',       'PC Page',         'PC Page',         'Edit page for PC.'),
(10102,   10100,          1,        'admin_menu.en', 'pagedef_mobile',  0,               '',       'Mobile Page',       'Mobile Page',       'Edit page for Mobile.'),
(10103,   10100,          2,        'admin_menu.en', 'pagedef_smartphone',  0,           '',       'Smartphone Page', 'Smartphone Page',       'Edit page for Smartphone.'),
(10104,   10100,          3,        'admin_menu.en', 'widgetlist',      0,               '',       'Widget Administration',     'Widget Administration',     'Administrate widgets with widget config window.'),
(10105,   10100,          4,        'admin_menu.en', 'templist',        0,               '',       'Template Administration',     'Template Administration',     'Administrate templates.'),
(10106,   10100,          5,        'admin_menu.en', 'smenudef',        0,               '',       'Menu Administration', 'Menu Administration', 'Administrate menu definition.'),
(10200,   0,            1,        'admin_menu.en', '_login',          0,               '',       'System Operation',         '',                     ''),
(10201,   10200,          0,        'admin_menu.en', 'userlist',        0,               '',       'User List',           'User List',           'Administrate user to login.'),
(10202,   10200,          1,        'admin_menu.en', 'accesslog',     0,               '',       'Site Conditions', 'Site Conditions', 'Operation log and access analytics on site.'),
(10300,   0,            2,        'admin_menu.en', '_config',         0,               '',       'System Administration',         '',                     ''),
(10301,   10300,          0,        'admin_menu.en', 'configsite',      0,               '',       'Site Information',             'Site Information',             'Configure site information.'),
(10302,   10300,          1,        'admin_menu.en', 'configsys',       0,               '',       'System Information',         'System Information',         'Configure sytem information.'),
(10303,   10300,          2,        'admin_menu.en', 'resbrowse',       0,               '',       'Resource Browse',     'Resource Browse',     'Administrate resource files.'),
(10304,   10300,          3,        'admin_menu.en', 'master',          0,               '',       'System Master', 'System Master', 'Administrate system master data.'),
(10305,   10300,          4,        'admin_menu.en', 'initsystem',      0,               '',       'Database Maintenance',       'Database Maintenance',       'Database maintenance such as data initializing.'),
(10399,   0,            3,        'admin_menu.en', '_399',            1,               '',       'Return',                 '',                     ''),
(11100,  0,            11,       'admin_menu.en', '_others',         0,               '',       'Others',               '',                     ''),
(11101,  11100,         0,        'admin_menu.en', 'logout',          0,               '',       'Logout',           'Logout',           'Logout from system.');

-- ページIDマスター
INSERT INTO _page_id 
(pg_id,          pg_type, pg_default_sub_id, pg_path,       pg_name,                            pg_description,                       pg_priority, pg_device_type, pg_active, pg_visible, pg_mobile, pg_editable, pg_admin_menu, pg_analytics) VALUES
('index',        0,       'content',         'index',       'PC用アクセスポイント',             'PC用アクセスポイント',               0,           0,              true,      true,      false,     true, true, true),
('s_index',      0,       'front',         's/index',     'スマートフォン用アクセスポイント', 'スマートフォン用アクセスポイント',   1,           2,              true,      true,      false,     true, false, true),
('m_index',      0,       'front',         'm/index',     '携帯用アクセスポイント',           '携帯用アクセスポイント',             2,           1,              true,      true,      true,      true, false, true),
('admin_index',  0,       'content',         'admin/index', '管理用アクセスポイント',           '管理用アクセスポイント',             3,           0,              true,      true,      false,     false, false, false),
('connector',    0,       'content',         'connector',   'サーバ接続用アクセスポイント',     'サーバ接続用アクセスポイント',       4,           0,              true,      true,      false,     true, false, false),
('front',        1,       '',                '',            'トップ画面',                       'トップ画面用',                   0,           0,              true,      true,      false,     true, false, false),
('content',      1,       '',                '',            'コンテンツ',                       'コンテンツ画面用',                   1,           0,              true,      true,      false,     false, false, false),
('shop',         1,       '',                '',            'ECショップ',                       'ECショップ画面用',                   2,           0,              true,      true,      false,     true, false, false),
('shop_safe',    1,       '',                '',            'ECショップ(セキュリティ保護)',     'ECショップ(セキュリティ保護)画面用', 3,           0,              true,      true,      false,     true, false, false),
('bbs',          1,       '',                '',            '掲示板',                           '掲示板画面用',                       4,           0,              true,      true,      false,     true, false, false),
('blog',         1,       '',                '',            'ブログ',                           'ブログ画面用',                       5,           0,              true,      true,      false,     true, false, false),
('wiki',         1,       '',                '',            'Wiki',                             'Wiki画面用',                         6,           0,              true,      true,      false,     true, false, false),
('event',        1,       '',                '',            'イベント',                         'イベント画面用',                     7,           0,              true,      true,      false,     true, false, false),
('photo',        1,       '',                '',            'フォトギャラリー',                   'フォトギャラリー画面用',               8,           0,              true,      true,      false,     true, false, false),
('contact',      1,       '',                '',            'お問い合わせ',                     'お問い合わせ画面用',                 9,           0,              true,      true,      false,     true, false, false),
('contact2',     1,       '',                '',            'お問い合わせ2',                    'お問い合わせ画面用',                 10,          0,              true,      true,      false,     true, false, false),
('reserve',      1,       '',                '',            '予約',                             '予約画面用',                         20,          0,              true,      true,      false,     true, false, false),
('search',       1,       '',                '',            '検索',                             '検索画面用',                         21,          0,              true,      true,      false,     true, false, false),
('user',         1,       '',                '',            'ユーザコンテンツ',                 'ユーザ作成コンテンツ用',             50,          0,              true,      true,      false,     true, false, false),
('deploy',       1,       '',                '',            'ウィジェット有効化用',             'ウィジェット有効化用',               100,         0,              true,      false,     false,     true, false, false),
('test',         1,       '',                '',            'ウィジェットテスト用',             'ウィジェットテスト用非公開画面',     101,         0,              false,     true,      false,     true, false, false);

-- ページ情報マスター
INSERT INTO _page_info
(pn_id,       pn_sub_id,   pn_content_type, pn_use_ssl) VALUES
('index',     'content',   'content',       false),
('index',     'shop',      'product',       false),
('index',     'shop_safe', '',              true),
('index',     'bbs',       'bbs',           false),
('index',     'blog',      'blog',          false),
('index',     'wiki',      'wiki',          false),
('index',     'user',      'user',          false),
('index',     'event',     'event',         false),
('index',     'photo',     'photo',         false),
('index',     'search',    'search',        false),
('index',     'contact',   '',              true),
('index',     'contact2',  '',              true),
('index',     'safe',      '',              true),
('m_index',   'content',   'content',       false),
('m_index',   'shop',      'product',       false),
('m_index',   'bbs',       'bbs',           false),
('m_index',   'blog',      'blog',          false),
('m_index',   'wiki',      'wiki',          false),
('m_index',   'user',      'user',          false),
('m_index',   'event',     'event',         false),
('m_index',   'photo',     'photo',         false),
('m_index',   'search',    'search',        false),
('s_index',   'content',   'content',       false),
('s_index',   'shop',      'product',       false),
('s_index',   'shop_safe', '',              true),
('s_index',   'bbs',       'bbs',           false),
('s_index',   'blog',      'blog',          false),
('s_index',   'wiki',      'wiki',          false),
('s_index',   'user',      'user',          false),
('s_index',   'event',     'event',         false),
('s_index',   'photo',     'photo',         false),
('s_index',   'search',    'search',        false),
('s_index',   'contact',   '',              true),
('s_index',   'contact2',  '',              true),
('s_index',   'safe',      '',              true),
('admin_index', 'front',   'dboard',        false),
('connector', 'content',   'content',       false);

-- ページ定義マスター
INSERT INTO _page_def
(pd_id,         pd_sub_id,      pd_position_id, pd_index, pd_widget_id,   pd_config_id, pd_visible, pd_editable, pd_title_visible) VALUES
('admin_index', '',             'top',          1,        'admin_menu3',  0,            true,       false, false),
('admin_index', 'front',        'main',         1,        'admin_main',   0,            true,       false, false),
('admin_index', 'front',        'main',         2,        'admin/analytics',   0,            true,       true, false),
('admin_index', 'front',        'main',         3,        'admin/opelog',   0,            true,       true, false),
('admin_index', 'front',        'left',         1,        'admin/loginuser',   0,            true,       true, true),
('admin_index', 'content',      'main',         1,        'admin_main',   0,            true,       false, false),
('connector',   'content',      'main',         1,        'c/updateinfo', 0,            true,       false,       true);

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
(ls_id,                     ls_language_id, ls_value,                             ls_name) VALUES
('msg_site_in_maintenance', 'ja',           'ただいまサイトのメンテナンス中です', 'メンテナンス中メッセージ'),
('msg_access_deny',         'ja',           'アクセスできません',                 'アクセス不可メッセージ');

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
('user_data',    'ユーザ不正データ',     'ユーザ操作の不正なデータ送信を示します',     10,       10);