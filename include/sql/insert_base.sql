-- *
-- * 基本テーブルデータ登録スクリプト
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
-- --------------------------------------------------------------------------------------------------
-- 基本テーブルデータ登録スクリプト
-- ベースシステム(フレームワーク)で最小限必要な初期データの登録を行う
-- --------------------------------------------------------------------------------------------------

-- システム設定マスター
-- システムの動作に影響する設定を管理する
INSERT INTO _system_config 
(sc_id,                          sc_value,                  sc_name) VALUES
('system_name',                 'Magic3',                   'システム名称'),
('db_version',                  '2014072101',               'DBバージョン'),
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
('admin_default_template',      '_admin4',                  '管理画面用デフォルトテンプレート'),
('mobile_default_template',     'm/default',                '携帯画面用デフォルトテンプレート'),
('smartphone_default_template', 's/default_jquery',         'スマートフォン画面用デフォルトテンプレート'),
('msg_template',                '_system',                  'メッセージ表示用テンプレート'),
('use_template_id_in_session',  '1',                        'セッションにテンプレートIDを保存'),
('use_content_maintenance',     '0',                        'メンテナンス画面用コンテンツの取得'),
('use_content_access_deny',     '0',                        'アクセス不可画面用コンテンツの取得'),
('use_jquery',                  '1',                        '一般画面にjQueryを使用'),
('default_theme',               'black-tie',                '一般画面用jQueryUIテーマ'),
('admin_default_theme',         'black-tie',                '管理画面用jQueryUIテーマ'),
('jquery_version',               '1.8',                     'jQueryバージョン(PC用)'),
('admin_jquery_version',         '1.8',                     '管理画面用jQueryバージョン'),
('s:jquery_version',             '1.8',                     'jQueryバージョン(スマートフォン用)'),
('head_title_format',           '$1;$1 - $2;$1 - $2 - $3;', 'HTMLヘッダタイトルフォーマット'),
('mobile_auto_redirect',        '0',                        '携帯アクセスの自動遷移'),
('mobile_use_session',           '1',                       '携帯セッション管理'),
('smartphone_auto_redirect',    '0',                        'スマートフォンアクセスの自動遷移'),
('smartphone_use_jquery_mobile', '0',                       'スマートフォン画面でjQuery Mobileを使用'),
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
('site_logo_filename',           'logo_72c.jpg;logo_200c.jpg',   'サイトロゴファイル名'),
('thumb_format',                 '72c.jpg;200c.jpg',   'サムネール仕様'),
('avatar_format',                '80c.jpg',   'アバター仕様'),
('ogp_thumb_format',             '200c.jpg',   'OGP用サムネール仕様'),
('wysiwyg_editor',               'ckeditor',   'WYSIWYGエディター'),
('site_mobile_url',              '',                        '携帯用サイトURL'),
('site_smartphone_url',          '',                        'スマートフォン用サイトURL'),
('multi_domain',                 '0',                       'マルチドメイン運用'),
('auto_login',        '1',                        '一般画面自動ログイン機能'),
('auto_login_admin',        '0',                        '管理画面自動ログイン機能'),
('access_in_intranet',               '0',                       'イントラネット運用'),
('awstats_data_path', '../tools/awstats', 'Awstatsデータのデータパス');

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
('newslib',    'newsLib',     '新着情報ライブラリ',       '',             true);

-- 管理画面メニューデータ
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

-- ページIDマスター
INSERT INTO _page_id 
(pg_id,          pg_type, pg_default_sub_id, pg_path,       pg_name,                            pg_description,                       pg_priority, pg_device_type, pg_active, pg_visible, pg_mobile, pg_editable, pg_admin_menu, pg_analytics) VALUES
('index',        0,       'content',         'index',       'PC用アクセスポイント',             'PC用アクセスポイント',               0,           0,              true,      true,      false,     true, true, true),
('s_index',      0,       'front',         's/index',     'スマートフォン用アクセスポイント', 'スマートフォン用アクセスポイント',   1,           2,              true,      true,      false,     true, false, true),
('m_index',      0,       'front',         'm/index',     '携帯用アクセスポイント',           '携帯用アクセスポイント',             2,           1,              true,      true,      true,      true, false, true),
('admin_index',  0,       'content',         'admin/index', '管理用アクセスポイント',           '管理用アクセスポイント',             3,           0,              true,      true,      false,     false, false, false),
('connector',    0,       'content',         'connector',   'サーバ接続用アクセスポイント',     'サーバ接続用アクセスポイント',       4,           0,              true,      true,      false,     true, false, false);
INSERT INTO _page_id 
(pg_id,          pg_type,      pg_name,                            pg_description,                       pg_priority, pg_active, pg_visible, pg_editable, pg_available) VALUES
('front',        1,            'トップ画面',                       'トップ画面用',                       0,           true,      true,       true,        true),
('content',      1,            'コンテンツ',                       'コンテンツ画面用',                   1,           true,      true,       false,       true),
('shop',         1,            'ECショップ',                       'ECショップ画面用',                   2,           true,      true,       true,        true),
('shop_safe',    1,            'ECショップ(セキュリティ保護)',     'ECショップ(セキュリティ保護)画面用', 3,           true,      true,       true,        true),
('bbs',          1,            '掲示板',                           '掲示板画面用',                       4,           true,      true,       true,        true),
('blog',         1,            'ブログ',                           'ブログ画面用',                       5,           true,      true,       true,        true),
('wiki',         1,            'Wiki',                             'Wiki画面用',                         6,           true,      true,       true,        true),
('event',        1,            'イベント',                         'イベント画面用',                     7,           true,      true,       true,        true),
('photo',        1,            'フォトギャラリー',                   'フォトギャラリー画面用',           8,           true,      true,       true,        true),
('contact',      1,            'お問い合わせ',                     'お問い合わせ画面用',                 9,           true,      true,       true,        true),
('contact2',     1,            'お問い合わせ2',                    'お問い合わせ画面用',                 10,          true,      true,       true,        false),
('reguser',      1,            'ユーザ登録',                       'ユーザ登録画面用',                   12,          true,      true,       true,        false),
('reserve',      1,            '予約',                             '予約画面用',                         20,          true,      true,       true,        false),
('search',       1,            '検索',                             '検索画面用',                         21,          true,      true,       true,        true),
('user',         1,            'ユーザコンテンツ',                 'ユーザ作成コンテンツ用',             50,          true,      true,       true,        true),
('deploy',       1,            'ウィジェット有効化用',             'ウィジェット有効化用',               100,         true,      false,      true,        false),
('test',         1,            'ウィジェットテスト用',             'ウィジェットテスト用非公開画面',     101,         false,     true,       true,        false);

-- ページ情報マスター
INSERT INTO _page_info
(pn_id,       pn_sub_id,   pn_content_type, pn_use_ssl) VALUES
('index',     'content',   'content',       false),
('index',     'shop',      'product',       false),
('index',     'shop_safe', 'commerce',      true),
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
('s_index',   'shop_safe', 'commerce',      true),
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
('admin_index', '',             'top',          1,        'admin_menu4',  0,            true,       false, false),
('admin_index', 'front',        'top',          2,        'admin/message',  0,            true,       false, false),
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
(ls_type, ls_id,                           ls_language_id, ls_value,                             ls_name) VALUES
(0,       'msg_site_in_maintenance',       'ja',           'ただいまサイトのメンテナンス中です', 'メンテナンス中メッセージ'),
(0,       'msg_access_deny',               'ja',           'アクセスできません',                 'アクセス不可メッセージ'),
(0,       'msg_page_not_found',            'ja',           'ページが見つかりません',                 '存在しないページメッセージ'),
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
(2,       'user',        'ja',           'ユーザ作成コンテンツ', 'ユーザ作成コンテンツ'),
(2,       'event',       'ja',           'イベント情報',         'イベント情報'),
(2,       'photo',       'ja',           'フォトギャラリー',     'フォトギャラリー'),
(10,      'COM_CONTENT_CREATED_DATE_ON',   'ja',           '作成日：%s',         ''),
(10,      'COM_CONTENT_LAST_UPDATED',      'ja',           '更新日：%s',         ''),
(10,      'COM_CONTENT_PUBLISHED_DATE_ON', 'ja',           '公開日：%s',         ''),
(10,      'COM_CONTENT_WRITTEN_BY',        'ja',           '作者：%s',           ''),
(10,      'COM_CONTENT_CATEGORY',          'ja',           'カテゴリー：%s',     ''),
(10,      'COM_CONTENT_ARTICLE_HITS',      'ja',           'アクセス数：%s',     ''),
(10,      'COM_CONTENT_READ_MORE_TITLE',   'ja',           'もっと読む',         ''),
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
(10,      'COM_CONTENT_READ_MORE_TITLE',   'en',           'Read more...',       ''),
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
(wt_id, wt_name,            wt_sort_order) VALUES
('',     'その他',   100),
('content',   '汎用コンテンツ',   1),
('blog',      'ブログ',           2),
('bbs',       'BBS',           3),
('commerce',  'Eコマース',        4),
('photo',     'フォトギャラリー', 5),
('event',     'イベント情報',     6),
('wiki',     'Wiki',     7),
('user',     'ユーザ作成コンテンツ',     8),
('subcontent',     '補助コンテンツ',     9),
('search',     '検索',             10),
('reguser',     'ユーザ登録',      11),
('menu',     'メニュー',         12),
('image',     '画像',         13),
('design',    'デザイン',         14),
('admin',     '管理画面用',      20);
