-- *
-- * データ登録スクリプト「Eコマースショップデモ2」
-- *
-- * PHP versions 5
-- *
-- * LICENSE: This source file is licensed under the terms of the GNU General Public License.
-- *
-- * @package    Magic3 Framework
-- * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
-- * @copyright  Copyright 2006-2017 Magic3 Project.
-- * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
-- * @version    SVN: $Id$
-- * @link       http://www.magic3.org
-- *
-- [Eコマースショップデモ2]
-- Eコマース主軸型サイト。
-- Eコマース機能にアクセスしやすいようにカスタマイズした管理画面
-- WordPressテンプレートテスト用

-- システム設定
UPDATE _system_config SET sc_value = 'shop-isle' WHERE sc_id = 'default_template';

-- サイト定義マスター
DELETE FROM _site_def WHERE sd_id = 'site_name';
DELETE FROM _site_def WHERE sd_id = 'site_slogan';
DELETE FROM _site_def WHERE sd_id = 'head_title';
INSERT INTO _site_def
(sd_id,                  sd_language_id, sd_value,         sd_name) VALUES
('site_name',            'ja',           'Magic3デモ',               'サイト名'),
('site_slogan',          'ja',           'WordPressテンプレートテスト中',               'スローガン'),
('head_title',           'ja',           'Magic3デモ',               'HTMLヘッダtitle');

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
('shop',         1,            'ECショップ',                       'ECショップ画面用',                   2,           true,      true,       true),
('shop_safe',    1,            'ECショップ(セキュリティ保護)',     'ECショップ(セキュリティ保護)画面用', 3,           true,      true,       true),
('bbs',          1,            '掲示板',                           '掲示板画面用',                       4,           false,      true,       true),
('blog',         1,            'ブログ',                           'ブログ画面用',                       5,           false,      true,       true),
('wiki',         1,            'Wiki',                             'Wiki画面用',                         6,           false,      true,       true),
('calendar',     1,            'カレンダー',                       'カレンダー画面用',                   7,           false,      true,       true),
('event',        1,            'イベント情報',                     'イベント情報画面用',                 8,           false,      true,       true),
('photo',        1,            'フォトギャラリー',                 'フォトギャラリー画面用',             9,           false,      true,       true),
('contact',      1,            'お問い合わせ',                     'お問い合わせ画面用',                 10,          true,      true,       true),
('contact2',     1,            'お問い合わせ2',                    'お問い合わせ画面用',                 11,          false,      true,       true),
('reguser',      1,            'ユーザ登録',                       'ユーザ登録画面用',                   12,          false,      true,       true),
('reserve',      1,            '予約',                             '予約画面用',                         19,          false,      true,       true),
('member',       1,            '会員',                             '会員画面用',                         20,          true,      true,       true),
('search',       1,            '検索',                             '検索画面用',                         21,          true,      true,       true),
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
(202,   200,          1,        'admin_menu', 'opelog',          0,               true, '',       '運用状況', '運用状況', 'サイトの運用状況を表示します。'),
(299,   0,            3,        'admin_menu', '_299',            1,               true, '',       '改行',                 '',                     ''),
(300,   0,            4,        'admin_menu', '_config',         0,               true, '',       'システム管理',         '',                     ''),
(301,   300,          0,        'admin_menu', 'configsite',      0,               true, '',       '基本情報',             '基本情報',             'サイト運営に必要な情報を設定します。'),
(302,   300,          1,        'admin_menu', 'configsys',       0,               true, '',       'システム情報',         'システム情報',         'システム全体の設定、運用状況を管理します。'),
(303,   300,          2,        'admin_menu', 'mainte',          0,               true, '',       'メンテナンス', 'メンテナンス', 'ファイルやDBなどのメンテナンスを行います。'),
(399,   0,            5,        'admin_menu', '_399',            1,               true, '',       '改行',                 '',                     ''),
(500,   0,            6,        'admin_menu', '_daily',          0,               true, '',           '日常処理', '', ''),
(501,   500,          0,        'admin_menu', 'configwidget_ec_main',       0,    true, 'task=order', '受注管理', '受注管理', '受注管理を行います。'),
(502,   500,          1,        'admin_menu', 'configwidget_ec_main',       0,    true, 'task=product', '商品管理', '商品管理', '商品管理を行います。'),
(503,   500,          2,        'admin_menu', 'configwidget_ec_main',       0,    true, 'task=member',   '会員管理', '会員管理', '会員情報を管理します。');

-- 画面定義
DELETE FROM _page_def WHERE pd_id = 'index';
INSERT INTO _page_def
(pd_id,   pd_sub_id,   pd_position_id, pd_index, pd_widget_id,            pd_config_id, pd_config_name,       pd_title,    pd_menu_id,  pd_title_visible, pd_update_dt) VALUES
('index', '',          'user3',        2,        'default_menu',          1,            'メインメニュー設定', '',          'main_menu', true,             now()),
('index', '',          'left',         6,        'ec_search_box',         0,            '',                   '',          '',          true,             now()),
('index', '',          'left',         9,        'ec_menu',               0,            '',                   '',          '',          true,             now()),
('index', '',          'right',        3,        'ec_login',          0,            '',                   '',          '',          true,             now()),
('index', '',          'right',        5,       'ec_cart',           0,            '',                   '',          '',          true,             now()),
('index', '',     'right',        7,        'ec_product_random', 0,            '',                   '',          '',          true,             now()),
('index', 'front',     'main',         3,        'static_content',        1,            'ようこそ',           '',          '',          true,             now()),
('index', 'front',     'main',         6,        'ec_product_display2',   1,            '新着',               '新着',      '',          true,             now()),
('index', 'front',     'main',         9,        'ec_product_display2',   2,            'おすすめ',           'おすすめ',  '',          true,             now()),
('index', 'content',   'main',         6,        'default_content',       0,            '',                   '',          '',          false,             now()),
('index', 'shop',      'main',         1,        'ec_product_slide',      0,            '',                   '',          '',          false,            now()),
('index', 'shop',      'main',         3,        'ec_product_header',     0,            '',                   '',          '',          false,            now()),
('index', 'shop',      'main',         12,       'ec_disp',               0,            '',                   '',          '',          false,             now()),
('index', 'shop_safe', 'main',         12,       'ec_main',               0,            '',                   '',          '',          false,             now()),
('index', 'search',    'main',         3,        'custom_search',         1,            '',                   '',          '',          false,             now()),
('index', 'contact',   'main',         3,        'contactus',             0,            '',                   '',          '',          false,             now());

-- 新メニュー対応
TRUNCATE TABLE _menu_def;
INSERT INTO _menu_def
(md_id, md_index, md_menu_id,  md_name,                  md_type, md_link_url,                             md_param,     md_update_dt) VALUES
(1,     1,        'main_menu', 'ホーム',                 0,       '[#M3_ROOT_URL#]/',                      '',           now()),
(2,     2,        'main_menu', '通信販売法に基づく表記', 0,       '[#M3_ROOT_URL#]/index.php?contentid=3', '',       now()),
(3,     3,        'main_menu', '個人情報保護方針',       0,       '[#M3_ROOT_URL#]/index.php?contentid=2', '',            now()),
(4,     4,        'main_menu', '会社情報',               0,       '[#M3_ROOT_URL#]/index.php?contentid=1', '',            now()),
(5,     5,        'main_menu', 'お問い合わせ',           0,       '[#M3_ROOT_URL#]/index.php?sub=contact', '',            now()),
(6,     6,        'ec_menu',   'ドコモ',                 1,       '',                                      'category=1&pcontent=1', now()),
(7,     7,        'ec_menu',   'au',                     1,       '',                                      'category=2&pcontent=2', now());

-- ウィジェットパラメータ
DELETE FROM _widget_param WHERE wp_id = 'ec_product_display2';
INSERT INTO _widget_param
(wp_id,                 wp_config_id, wp_param,                                                                                                wp_create_dt) VALUES 
('ec_product_display2', 1,            'O:\8:"stdClass":12:{s:4:"name";s:16:"名称未設定1";s:11:"detailLabel";s:21:"もっと詳しく...";s:8:"rowCount";s:1:"2";s:11:"columnCount";s:1:"3";s:7:"imgSize";s:16:"standard-product";s:11:"nameVisible";i:1;s:11:"codeVisible";i:0;s:12:"priceVisible";i:1;s:11:"descVisible";i:0;s:10:"imgVisible";i:1;s:13:"detailVisible";i:0;s:12:"productItems";s:5:"2,3,4";}', now()),
('ec_product_display2', 2,            'O:\8:"stdClass":12:{s:4:"name";s:16:"名称未設定2";s:11:"detailLabel";s:21:"もっと詳しく...";s:8:"rowCount";s:1:"2";s:11:"columnCount";s:1:"3";s:7:"imgSize";s:16:"standard-product";s:11:"nameVisible";i:1;s:11:"codeVisible";i:0;s:12:"priceVisible";i:1;s:11:"descVisible";i:0;s:10:"imgVisible";i:1;s:13:"detailVisible";i:0;s:12:"productItems";s:8:"13,14,15";}', now());
DELETE FROM _widget_param WHERE wp_id = 'static_content';
INSERT INTO _widget_param
(wp_id,                wp_config_id, wp_param,                                                                                                wp_create_dt) VALUES 
#('static_content', 0,            'a:\1:{i:0;O:\8:"stdClass":3:{s:4:"name";s:33:"ようこそ携帯ショップへ";s:2:"id";i:1;s:9:"contentId";s:1:"5";}}', now());
('static_content', 1,            'O:\8:"stdClass":2:{s:4:"name";s:33:"ようこそ携帯ショップへ";s:9:"contentId";s:1:"5";}', now());
DELETE FROM _widget_param WHERE wp_id = 'default_menu';
INSERT INTO _widget_param
(wp_id,          wp_config_id, wp_param,                                                                                                wp_create_dt) VALUES 
('default_menu', 1,            'O:\8:"stdClass":3:{s:6:"menuId";s:9:"main_menu";s:4:"name";s:27:"メインメニュー設定";s:9:"limitUser";i:0;}', now());
DELETE FROM _widget_param WHERE wp_id = 'custom_search';
INSERT INTO _widget_param
(wp_id,          wp_config_id, wp_param,                                                                                                wp_create_dt) VALUES 
('custom_search', 1,            'O:8:"stdClass":15:{s:4:"name";s:16:"名称未設定1";s:11:"resultCount";s:2:"20";s:14:"searchTemplate";s:239:"<input id="custom_search_1_text" maxlength="40" size="10" type="text" /><input class="button" id="custom_search_1_button" type="button" value="検索" /><input class="button" id="custom_search_1_reset" type="button" value="リセット" />";s:12:"searchTextId";s:20:"custom_search_1_text";s:14:"searchButtonId";s:22:"custom_search_1_button";s:13:"searchResetId";s:21:"custom_search_1_reset";s:15:"isTargetContent";i:1;s:12:"isTargetUser";i:1;s:12:"isTargetBlog";i:1;s:9:"fieldInfo";a:0:{}s:15:"isTargetProduct";i:0;s:13:"isTargetEvent";i:0;s:11:"isTargetBbs";i:0;s:13:"isTargetPhoto";i:0;s:12:"isTargetWiki";i:0;}', now());

TRUNCATE TABLE content;
INSERT INTO content (cn_type, cn_id, cn_language_id, cn_name,              cn_description,         cn_html,                        cn_default, cn_key, cn_create_user_id, cn_create_dt) VALUES 
('', 1,     'ja',           '会社情報',   '会社情報', '<div class="ec_common">\r\n<table>\r\n    <tbody>\r\n        <tr>\r\n            <th>社　名</th>\r\n            <td>\r\n            <p>&nbsp;</p>\r\n            <p>[#CUSTOM_KEY_001#]</p>\r\n            <p>&nbsp;</p>\r\n            </td>\r\n        </tr>\r\n        <tr>\r\n            <th>所在地</th>\r\n            <td>\r\n            <p>&nbsp;</p>\r\n            <p>[#CUSTOM_KEY_002#]</p>\r\n            <p>&nbsp;</p>\r\n            </td>\r\n        </tr>\r\n        <tr>\r\n            <th>設　立</th>\r\n            <td>\r\n            <p>&nbsp;</p>\r\n            <p>[#CUSTOM_KEY_003#]</p>\r\n            <p>&nbsp;</p>\r\n            </td>\r\n        </tr>\r\n        <tr>\r\n            <th>代表者</th>\r\n            <td>\r\n            <p>&nbsp;</p>\r\n            <p>[#CUSTOM_KEY_004#]</p>\r\n            <p>&nbsp;</p>\r\n            </td>\r\n        </tr>\r\n        <tr>\r\n            <th>事業内容</th>\r\n            <td>\r\n            <p>&nbsp;</p>\r\n            <p>[#CUSTOM_KEY_005#]●商品Ａの製造<br />\r\n            ●商品Ｂの卸売<br />\r\n            ●商品Ｃの販売</p>\r\n            <p>&nbsp;</p>\r\n            </td>\r\n        </tr>\r\n        <tr>\r\n            <th>主要取引銀行</th>\r\n            <td>\r\n            <p>&nbsp;</p>\r\n            <p>[#CUSTOM_KEY_006#]</p>\r\n            <p>&nbsp;</p>\r\n            </td>\r\n        </tr>\r\n        <tr>\r\n            <th>主要取引先</th>\r\n            <td>\r\n            <p>&nbsp;</p>\r\n            <p>[#CUSTOM_KEY_007#]■ＸＸＸ株式会社<br />\r\n            ■ＹＹＹ株式会社<br />\r\n            ■株式会社　ＺＺＺ</p>\r\n            <p>&nbsp;</p>\r\n            <p>&nbsp;</p>\r\n            </td>\r\n        </tr>\r\n    </tbody>\r\n</table>\r\n</div>\r\n',              false, '',                0, now()),
('', 2,     'ja',           '個人情報保護方針',   '個人情報保護方針',        '<div class="ec_commno">\r\n<table>\r\n    <tbody>\r\n        <tr>\r\n            <td>「○○○○○」では利用者の皆様が安心してご利用頂けるよう最低限の個人情報を提供頂いております。<br />\r\n            「○○○○○」ではご提供頂いた個人情報の保護について最大限の注意を払っています。 <br />\r\n            「○○○○○」の個人情報保護についての考え方は以下の通りです。<br />\r\n            <br />\r\n            ■   弊社では会員により登録された個人及び団体や法人の情報については、<br />\r\n            「○○○○○」 において最先端の機能やサービスを開発・提供するためにのみ利用し、<br />\r\n            会員個人情報の保護に細心の注意を払うものとします。 <br />\r\n            ■   このプライバシーポリシーの適用範囲は、「○○○○○」 で提供されるサービスのみであります。<br />\r\n            (範囲は下記、第1項に規定)<br />\r\n            ■   本規約に明記された場合を除き、目的以外の利用は致しません。(目的は下記、第2項に規定)<br />\r\n            ■   本規約に明記された場合を除き、第三者への開示は致しません。(管理は下記、第2項に規定)<br />\r\n            ■   その他本規約に規定された方法での適切な管理を定期的に行います。<br />\r\n            ■   「○○○○○」は利用者の許可なくして、プライバシーポリシーの変更をすることができます。<br />\r\n            「○○○○○」が、個人情報取得内容の変更・利用方法の変更・開示内容の変更等をした際には、<br />\r\n            利用者がその内容を知ることができるよう、弊社ホームページのお知らせに公開し、<br />\r\n            このプライバシーポリシーに反映することにより通知致します。<br />\r\n            <br />\r\n            1．「○○○○○」のプライバシーポリシーについての考え方が適用される範囲 <br />\r\n            ■   「○○○○○」のプライバシーポリシーについての考え方は、<br />\r\n            会員が「○○○○○」のサービスを利用される場合に適用されます。 <br />\r\n            ■   会員が「○○○○○」のサービスを利用される際に収集される個人情報は、 <br />\r\n            「○○○○○」の個人情報保護についての考え方に従って管理されます。 <br />\r\n            ■   「○○○○○」の個人情報保護考え方は、 「○○○○○」が直接提供される<br />\r\n            サービスのみであり、リンク等でつながった他の組織・会社等のサービスは適用範囲外となります。<br />\r\n            ■  「○○○○○」のサービスのご利用は、利用者の責任において行われるものとします。<br />\r\n            ■   弊社のホームページ及び当ホームページにリンクが設定されている他のホームページから<br />\r\n            取得された各種情報の利用によって生じたあらゆる損害に関して、「○○○○○」は<br />\r\n            一切の責任を負いません。<br />\r\n            <br />\r\n            2．「○○○○○」の個人情報の収集と利用  <br />\r\n            「○○○○○」では会員の皆様に最先端の機能やサービスを開発・提供するために、<br />\r\n            会員について幾つかの個人情報が必要となります。 <br />\r\n            ■   ショップのID・パスワードは利用者ご自身の責任において管理をお願い致します。<br />\r\n            - パスワードは定期的に変更し、他人が類推しやすいような名前や生年月日、<br />\r\n            電話番号など は避けることをお勧め致します。<br />\r\n            - また、知人・友人などであっても開示・貸与・譲渡しないで下さい。<br />\r\n            - お問合せのメールや弊社のホームページ上の Q&amp;Aにはパスワードを書き込まないようお願い致します。<br />\r\n            ■   収集された個人情報は「○○○○○」のサービスを提供するために必要な限度においてのみ利用し、<br />\r\n            次の場合を除き、いかなる第三者にも提供致しません。 <br />\r\n            ■   会員の同意がある場合 <br />\r\n            - 会員から個人情報の利用に関する同意を求めるための電子メールを送付する場合 <br />\r\n            - あらかじめ弊社と機密保持契約を締結している企業（例えば、業務委託先）<br />\r\n            等に必要な限度において開示する場合 <br />\r\n            - 会員に対し、弊社、または、弊社の業務提携先等の広告宣伝のための電子メール、<br />\r\n            ダイレクトメールを送付する場合 <br />\r\n            - 「○○○○○」における会員の行為が、「○○○○○」利用規約や方針・告知、<br />\r\n            「○○○○○」の十戒等に違反している場合に、他の会員、第三者または弊社の権利、<br />\r\n            財産を保護するために必要と認められる場合 <br />\r\n            - 裁判所、行政機関の命令等、その他法律の定めに従って個人情報の開示を求められた場合、<br />\r\n            または犯罪の捜査、第三　者に対する権利侵害の排除若しくはその予防、<br />\r\n            その他これに準ずる必要性ある場合 <br />\r\n            ■   会員は、弊社に対し、個人情報を上に定める方法で利用することにつきあらかじめ同意するものとし、<br />\r\n            異議を述べないものとします。<br />\r\n            ■   また、「○○○○○」では次の様な場合、弊社のビジネスパートナーと会員の個人情報を<br />\r\n            共有する事があります。 <br />\r\n            - 会員向け特別サービスなど、事業的な理由がある場合。<br />\r\n            この場合、情報を提供する前に会員の同意を求め、同意無しでは提供致しません。 <br />\r\n            - 統計資料作成、市場調査、データ分析などを行う場合。<br />\r\n            この場合、特定個人を判別することができない様に加工された情報だけを提供致します。 <br />\r\n            <br />\r\n            3. 問い合わせ先  <br />\r\n            ここに示した個人情報についての考え方についてご不明な点などございましたら次の<br />\r\n            アドレスまで電子メールでお問い合わせください。<br />\r\n            <br />\r\n            <br />\r\n            個人情報管理担当 : ●●●● (<a href="mailto:●●●@●●●.com">●●@●●.com</a>)</td>\r\n        </tr>\r\n    </tbody>\r\n</table>\r\n</div>', true,  '',       0, now()),
('', 3,     'ja',           '特定商取引に関する法律に基づく表示',           '特定商取引に関する法律に基づく表示', '<div class="ec_common">\r\n<table>\r\n    <tbody>\r\n        <tr>\r\n            <th colspan="2">特定商取引に関する法律に基づく表示</th>\r\n        </tr>\r\n        <tr>\r\n            <th>運営統括責任者</th>\r\n            <td>[#CUSTOM_KEY_001#]  ○○○　○○○</td>\r\n        </tr>\r\n        <tr>\r\n            <th>所在地</th>\r\n            <td>[#CUSTOM_KEY_002#]</td>\r\n        </tr>\r\n        <tr>\r\n            <th>電話番号</th>\r\n            <td>\r\n            <p>11-11-1111</p>\r\n            </td>\r\n        </tr>\r\n        <tr>\r\n            <th>メールアドレス</th>\r\n            <td>\r\n            <p><a href="mailto:info@example.com">xxxx@xxxx.com<br />\r\n            </a></p>\r\n            </td>\r\n        </tr>\r\n        <tr>\r\n            <th>屋号</th>\r\n            <td>\r\n            <p>○○○○○</p>\r\n            </td>\r\n        </tr>\r\n        <tr>\r\n            <th>商品代金以外の<br>必要料金</th>\r\n            <td>\r\n            <p>-　送料「一律<font color="#ff0000">840円</font>（但し、北海道・沖縄は<font color="#ff0000">1,260円</font>）」</p>\r\n            <p>-　コンビニ振込でご購入の際の振込手数料。</p>\r\n            <p>-　代金引換でご購入の際の代引手数料。</p>\r\n            </td>\r\n        </tr>\r\n        <tr>\r\n            <th>申し込みの有効期限</th>\r\n            <td>\r\n            <p>ご注文日を含め5日間</p>\r\n            </td>\r\n        </tr>\r\n        <tr>\r\n            <th>販売数量</th>\r\n            <td>\r\n            <p>指定はありません。在庫切れ、配送遅れの際はご連絡差し上げます。</p>\r\n            </td>\r\n        </tr>\r\n        <tr>\r\n            <th>お届けについて</th>\r\n            <td>\r\n            <p>ご注文日から、４日～１週間程度のお届けになります。休業日を挟む場合は、少しお時間をいただくことがございます。ご了承下さいませ。</p>\r\n            </td>\r\n        </tr>\r\n        <tr>\r\n            <th>キャンセルについて</th>\r\n            <td>\r\n            <p>商品を発送してからのキャンセルは対応いたしかねます。<br />\r\n            返品につきましては別途お問い合わせください。</p>\r\n            </td>\r\n        </tr>\r\n        <tr>\r\n            <th colspan="2">お支払い方法について</th>\r\n        </tr>\r\n        <tr>\r\n            <th>お支払方法</th>\r\n            <td>\r\n            <p><strong>●郵便振込●（前払い）</strong><br />\r\n            ※振込手数料はお客様負担です。<br />\r\n            ※入金確認後、翌発送日に発送いたします。<br />\r\n            <br />\r\n            【口座番号】XXXX-XXX-XXXX-XXX<br />\r\n            【口座名義】○○○○○</p>\r\n            <p><strong>●代金引換●（後払い）</strong><br />\r\n            荷物と引き換えに代金を配達員へお支払いいただくシステムです。<br />\r\n            【代引手数料】全国一律<font color="#ff0000">420円</font><br />\r\n            （お買い上げ金額が21,000円以上の場合は無料です。）</p>\r\n            <p><strong>●クレジットカード●</strong><br />\r\n            ※下記のカードがご利用いただけます。<br />\r\n            （1回払いのみとさせていただきます、ご了承下さいませ）</p>\r\n            <p><img src="[#M3_ROOT_URL#]/resource/image/sample/shop/credit_card.gif" alt="クレジットカード画像" /><br />\r\n            VISA、MASTER、DC</p>\r\n            <p>ご注文の際に「カードの種類」「カードNo.」「カードの有効期限」をご明記下さい。</p>\r\n            <p><strong>●コンビニ払い●（後払い）</strong><br />\r\n            ※振込手数料は無料です。<br />\r\n            ※購入金額が、30,000円を越える場合は、コンビニ決済をご利用頂けません。<br />\r\n            他お支払い方法をご利用下さい。</p>\r\n            <p><img src="[#M3_ROOT_URL#]/resource/image/sample/shop/convenience.gif" alt="コンビニ画像" /><br />\r\n            ローソン・ファミリーマート・セブンイレブン<br />\r\n            セイコーマート</p>\r\n            <p>【コンビニ・クレジット払いに関する注意事項】</p>\r\n            <p><a href="http://www.example.com/" target="_blank">http://www.xxxx.com</a></p>\r\n            </td>\r\n        </tr>\r\n        <tr>\r\n            <th>お支払い期限</th>\r\n            <td>\r\n            <p>【前払い】ご注文後<font color="#ff0000">5日以内</font>にお願いします。<br />\r\n            ※5日経っても入金が確認できない場合はキャンセル扱いとさせていただきます。</p>\r\n            <p>【後払い】商品到着後<font color="#ff0000">8日以内</font>にお願いします。</p>\r\n            <p>【代引】商品お届け時に、配送員に商品確認後、代金をお支払い下さい。</p>\r\n            </td>\r\n        </tr>\r\n        <tr>\r\n            <th colspan="2">送料について</th>\r\n        </tr>\r\n        <tr>\r\n            <th>送料</th>\r\n            <td>\r\n            <p>全国一律<font color="#ff0000">840円</font></p>\r\n            <p>但し、北海道・沖縄は<font color="#ff0000">1,260円</font></p>\r\n            </td>\r\n        </tr>\r\n        <tr>\r\n            <th colspan="2">返品について</th>\r\n        </tr>\r\n        <tr>\r\n            <th>不良品</th>\r\n            <td>\r\n            <p>商品には万全を期しておりますが、万一不都合がございましたら、商品到着後8日以内に弊社宛にご返送ください。早急にお取り替えいたします。<br />\r\n            <font color="#ff0000">※返品前に必ずメール、もしくはお問い合わせフォームにてご連絡ください。</font></p>\r\n            <p>【返送先】<br />\r\n            [#CUSTOM_KEY_002#]<br />\r\n            XXXX　係</p>\r\n            </td>\r\n        </tr>\r\n        <tr>\r\n            <th>返品期限</th>\r\n            <td>\r\n            <p>商品到着後8日以内<br />\r\n            <font color="#ff0000">※返品前に必ずメール、もしくはお問い合わせフォームにてご連絡ください。</font></p>\r\n            </td>\r\n        </tr>\r\n        <tr>\r\n            <th>返品送料</th>\r\n            <td>\r\n            <p>不良品交換、誤品配送交換の場合、弊社着払いにて対応いたします。<br />\r\n            お客様都合での返品につきましては送料をご負担願います。</p>\r\n            </td>\r\n        </tr>\r\n    </tbody>\r\n</table>\r\n</div>',              false,   '',               0, now()),
('', 4,     'ja',           '会員規約',   '会員規約', '<p>xxxx年x月x日<br />\r\n<br />\r\n<br />\r\n[#CUSTOM_KEY_001#](以下「当社」という)は、当社が運営する「○○○○○」の利用について、以下のとおり本規約を定めます。<br />\r\n<br />\r\n<br />\r\n<br />\r\n第1条（定義）<br />\r\n<br />\r\n本規約においては、次の各号記載の用語はそれぞれ次の意味で使用します。<br />\r\n<br />\r\n「○○○○○」とは、商品又はサービスの提供情報掲載、オンラインによる商品又はサービスの提供機能を持ったシステムで、当社が本規約に基づいてインターネット上で運営するサイトをいいます。<br />\r\n<br />\r\n「利用者」とは、(http://www.example.com/)にアクセスする者をいいます。<br />\r\n<br />\r\n「本サービス」とは、当社が本規約に基づき(http://www.○○○.com/)を利用する者に対し、提供するサービスをいい、サービスの内容、種類については、当社の独自の判断により随時変更、増減が行なわれるものとし、その通知は随時、(http://www.○○○.com/)上での表示、又は電子メールその他の通信手段を通じて行なわれるものとします。 <br />\r\n<br />\r\n<br />\r\n<br />\r\n第２条（規約の範囲及び変更） <br />\r\n<br />\r\n<br />\r\n<br />\r\n1 本規約は、本サービスの利用に関し、当社及び利用者に適用するものとし、利用者は(http://www.○○○.com/)を利用するにあたり、本規約を誠実に遵守するものとします。<br />\r\n<br />\r\n2 当社が別途(http://www.○○○.com/)上における掲示またはその他の方法により規定する個別規定及び当社が随時利用者に対し通知する追加規定は、本規約の一部を構成します。本規約と個別規定及び追加規定が異なる場合には、個別規定及び追加規定が優先するものとします。<br />\r\n<br />\r\n3 当社は利用者の承諾なく、当社の独自の判断により、本規約を変更する事があります。この場合、(http://www.○○○.com/)が提供するサービスの利用条件は変更後の利用規約に基づくものとします。当該変更は、予め当社に通知したアドレス宛の電子メール、(http: //www.○○○.com/)上の一般掲示又はその他当社が適当と認めるその他の方法により通知した時点より効力を発するものとします。<br />\r\n<br />\r\n4 規約の変更に伴い、利用者に不利益、損害が発生した場合、当社はその責任を一切負わないものとします。<br />\r\n<br />\r\n&nbsp;<br />\r\n<br />\r\n<br />\r\n<br />\r\n第３条（利用者の地位及び制限事項） <br />\r\n<br />\r\n<br />\r\n<br />\r\n1 利用者の地位<br />\r\n<br />\r\n(http://www.○○○.com/)において利用者は、提供される本サービスのいずれかを享受する時点において（ここにいう享受には、情報の閲覧も含みます）、本規約に合意したものとみなされ、同時に(http://www.○○○.com/)における利用者としての地位を得るものとします。 <br />\r\n<br />\r\n2 利用者に対する制限事項<br />\r\n<br />\r\n利用者は、以下に掲げる行為は行ってはならないものとします。<br />\r\n<br />\r\n(1) (http://www.○○○.com/)が指定した方法以外の方法によって、(http://www.○○○.com/)を利用する行為。<br />\r\n<br />\r\n(2) 他者になりすまして本サービスを利用する行為。 <br />\r\n<br />\r\n(3) (http://www.○○○.com/)認める以外の方法で、本サービスに関連するデータのリンクを、他のデータ等へ指定する行為。 <br />\r\n<br />\r\n(4) (http://www.○○○.com/)を利用するコンピュータに保存されているデータへ不正アクセスする、又はこれを破壊もしくは破壊するおそれのある行為。<br />\r\n<br />\r\n(5) 本サービスの運営を妨害する行為。 <br />\r\n<br />\r\n(6) 本サービスを使用した営業活動並びに営利を目的とした利用及びその準備を目的とした利用。但し、当社が別 途承認した場合には、この限りではありません。 <br />\r\n<br />\r\n(7) 他の利用者の個人情報を収集したり、蓄積すること、又はこれらの行為をしようとする事。<br />\r\n<br />\r\n(8) 公序良俗に反する行為及びその他国内外の法令に反する行為。<br />\r\n<br />\r\n&nbsp;<br />\r\n<br />\r\n<br />\r\n<br />\r\n&nbsp;<br />\r\n<br />\r\n<br />\r\n<br />\r\n第4条（本サービスの中断、停止） <br />\r\n<br />\r\n<br />\r\n<br />\r\n1 当社は以下の何れかの事由に該当する場合、当社の独自の判断により、利用者に事前に通 知することなく本サービスの一部もしくは全部を一時中断、又は停止することがあります。<br />\r\n<br />\r\n(1) 本サービスのための装置、システムの保守点検、更新を定期的にまたは緊急に行う場合。<br />\r\n<br />\r\n(2) 火災、停電、天災などの不可抗力により、本サービスの提供が困難な場合。<br />\r\n<br />\r\n(3) 第一種電気通信事業者の任務が提供されない場合。 <br />\r\n<br />\r\n(4) その他、運用上あるいは技術上当社が本サービスの一時中断、もしくは停止が必要であるか、又は不測の事態により、当社が本サービスの提供が困難と判断した場合。 <br />\r\n<br />\r\n<br />\r\n2 当社は、本サービスの提供の一時中断、停止等の発生により、利用者または第三者が被ったいかなる不利益、損害について、理由を問わず一切の責任を負わないものとします。 <br />\r\n<br />\r\n<br />\r\n<br />\r\n第５条（リンクの扱いについて） <br />\r\n<br />\r\n<br />\r\n<br />\r\n「○○○○○」が提供する各種サービスの中から他のサイトへリンクをしたり、第三者が他のサイトへのリンクを提供している場合、当社は(http: //www.○○○.com/)外のサイトについては、何ら責任は負いません。この場合、当該サイトに包括され、また当該サイト上で利用が可能となっているコンテンツ、広告、商品、サービスなどについても同様に一切責任を負いません。 当社は、それらのコンテンツ、広告、商品、サービスなどに起因または関連して生じた一切の損害についても賠償する責任は負いません。 <br />\r\n<br />\r\n<br />\r\n<br />\r\n第６条（著作権) <br />\r\n<br />\r\n<br />\r\n<br />\r\n1 利用者は、権利者の承諾を得ないで、いかなる方法においても(http://www.○○○.com/)を通じて提供されるいかなる情報も、著作権法で定める利用者個人の私的使用の範囲を超える複製、販売、出版、その他の用途に使用することはできないものとします。<br />\r\n<br />\r\n2 利用者は、権利者の承諾を得ないで、いかなる方法においても、第三者をして、(http://www.○○○.com/)を通じて提供されるいかなる情報も使用させたり、公開させたりすることはできないものとします。 <br />\r\n<br />\r\n3 本条の規約に違反して問題が発生した場合、利用者は、自己の責任と費用において係る問題を解決するとともに、当社に何らの迷惑又は損害を与えないものとします。<br />\r\n<br />\r\n<br />\r\n<br />\r\n<br />\r\n<br />\r\n第７条(賠償責任) <br />\r\n<br />\r\n<br />\r\n<br />\r\n1 本サービスの提供、遅滞、変更、中断、中止、停止、もしくは廃止、提供される情報等の流出もしくは焼失等、又はその他本サービスに関連して発生した利用者又は第三者の損害について、当社は一切の責任を負わないものとします。但し、本サービスを通じて登録した個人情報については別途定める「個人情報の取扱について」に準じます。 <br />\r\n<br />\r\n2 利用者が本サービス利用によって第三者に対して損害を与えた場合、利用者は自己の責任と費用をもって解決し、当社に損害を与えることのないものとします。利用者が本規約に反した行為、又は不正もしくは違法な行為によって当社に損害を与えた場合、当社は当該利用者に対して相応の損害賠償の請求ができるものとします。<br />\r\n<br />\r\n<br />\r\n<br />\r\n<br />\r\n<br />\r\n第８条（プライバシー・ポリシー） <br />\r\n<br />\r\n<br />\r\n<br />\r\n利用者による本サービスの利用に関連して当社が知り得る利用者の情報の管理および取扱いについては、当社が別途定めるプライバシー・ポリシーによるものとします。 <br />\r\n<br />\r\n<br />\r\n<br />\r\n第９条（準拠法） <br />\r\n<br />\r\n本規約の成立、効力、履行及び解釈に関しては日本国法が適用されるものとします。 <br />\r\n<br />\r\n<br />\r\n<br />\r\n第１０条（合意管轄） <br />\r\n<br />\r\n<br />\r\n本規約に関して紛争が生じた場合、当社本店所在地を管轄する地方裁判所を第一審の専属的合意管轄裁判所とします。</p>',              false,  'agreement',                0, now()),
('', 5,     'ja',           'ようこそ',   'ようこそ', '<p>ようこそ、Magic3ショップデモサイトへ。</p><p>このサイトはMagic3を使って、Eコマースシステムを作ったデモサイトです。</p><p>&nbsp;</p>',              false, '',                0, now()),
('ec_menu', 1, 'ja', 'docomo',              '',         '<p align="center"><img width="300" height="50" alt="" src="[#M3_ROOT_URL#]/resource/image/sample/product_head/dc1.jpg" /></p>',                        false, '', 0, now()),
('ec_menu', 2, 'ja', 'au',              '',         '<p align="center"><img width="300" height="50" alt="" src="[#M3_ROOT_URL#]/resource/image/sample/product_head/au1.jpg" /></p>',                        false, '', 0, now());

TRUNCATE TABLE `bn_def`;
INSERT INTO `bn_def` (`bd_id`, `bd_item_id`, `bd_name`, `bd_disp_type`, `bd_disp_item_count`, `bd_disp_align`) VALUES 
(1,     '1,2,3,4,5,6',                   'サンプルバナー1', 0,            1,                  3),
(2,     '11,12,13,14,15,16,17,18,19',    'サンプルバナー2', 0,            2,                  0),
(3,     '7,8,9,10',                      'サンプルバナー3', 1,            1,                  0);

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

TRUNCATE TABLE product_price;
INSERT INTO product_price (pp_product_id, pp_price_type_id, pp_currency_id, pp_price) VALUES 
(1, 'regular', 'JPY', 12000.0000),
(2, 'regular', 'JPY', 12000.0000),
(3, 'regular', 'JPY', 11000.0000),
(4, 'regular', 'JPY', 13000.0000),
(5, 'regular', 'JPY', 12000.0000),
(6, 'regular', 'JPY', 10000.0000),
(7, 'regular', 'JPY', 10000.0000),
(8, 'regular', 'JPY', 12000.0000),
(9, 'regular', 'JPY', 10000.0000),
(10, 'regular', 'JPY', 10000.0000),
(11, 'regular', 'JPY', 10000.0000),
(12, 'regular', 'JPY', 10000.0000),
(13, 'regular', 'JPY', 13000.0000),
(14, 'regular', 'JPY', 12000.0000),
(15, 'regular', 'JPY', 13000.0000),
(16, 'regular', 'JPY', 12000.0000),
(17, 'regular', 'JPY', 11000.0000),
(18, 'regular', 'JPY', 11000.0000),
(19, 'regular', 'JPY', 11000.0000),
(20, 'regular', 'JPY', 10000.0000),
(1, 'sale', 'JPY', 10000.0000),
(2, 'sale', 'JPY', 10000.0000),
(3, 'sale', 'JPY', 9000.0000),
(4, 'sale', 'JPY', 10000.0000),
(5, 'sale', 'JPY', 10000.0000),
(8, 'sale', 'JPY', 10000.0000),
(12, 'sale', 'JPY', 8000.0000),
(13, 'sale', 'JPY', 10000.0000),
(14, 'sale', 'JPY', 10000.0000),
(15, 'sale', 'JPY', 10000.0000);

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

TRUNCATE TABLE `product_category`;
INSERT INTO `product_category` (`pc_id`, `pc_language_id`, `pc_name`, `pc_parent_id`, `pc_sort_order`) VALUES 
(1, 'ja', 'ドコモ', 0, 1),
(2, 'ja', 'au', 0, 2);

TRUNCATE TABLE product_with_category;
INSERT INTO product_with_category (pw_product_serial, pw_index, pw_category_id) VALUES 
(1,  0, 2),
(2,  0, 2),
(3,  0, 2),
(4,  0, 2),
(5,  0, 2),
(6,  0, 2),
(7,  0, 2),
(8,  0, 2),
(9,  0, 2),
(10, 0, 2),
(11, 0, 2),
(12, 0, 2),
(13, 0, 1),
(14, 0, 1),
(15, 0, 1),
(16, 0, 1),
(17, 0, 1),
(18, 0, 1),
(19, 0, 1),
(20, 0, 1);

TRUNCATE TABLE product_record;
INSERT INTO product_record
(pe_product_id, pe_language_id, pe_stock_count) VALUES
(1,  'ja', 3),
(2,  'ja', 2),
(3,  'ja', 1),
(4,  'ja', 3),
(5,  'ja', 2),
(6,  'ja', 1),
(7,  'ja', 5),
(8,  'ja', 4),
(9,  'ja', 3),
(10, 'ja', 2),
(11, 'ja', 1),
(12, 'ja', 5),
(13, 'ja', 4),
(14, 'ja', 3),
(15, 'ja', 2),
(16, 'ja', 1),
(17, 'ja', 3),
(18, 'ja', 2),
(19, 'ja', 1),
(20, 'ja', 3);

TRUNCATE TABLE product;
INSERT INTO product (pt_id, pt_language_id, pt_name, pt_code, pt_product_type, pt_description, pt_description_short, pt_admin_note, pt_category_id, pt_related_product, pt_manufacturer_id, pt_sort_order, pt_default_price, pt_visible, pt_search_keyword, pt_site_url, pt_unit_type_id, pt_unit_quantity, pt_innner_quantity, pt_quantity_decimal, pt_price_decimal, pt_weight, pt_tax_type_id, pt_parent_id, pt_attr_condition, pt_product_set, pt_option_price, pt_thumb_src) VALUES 
(1, 'ja', 'MEDIA SKIN', 'AU001', 1, '<p>情緒に訴える新しい触感！</p>\r\n<p><span class="Text">デザイナー吉岡徳仁氏によるau design project第6弾モデル。<br />\r\n表面処理と塗料により2種類の異なる触感を実現しました。オレンジとホワイトは、ファンデーションに利用されているシリコン粒子でさらっとした心地よさ、ブラックは、特殊ウレタン粒子を含んだソフトフィール塗料による、しっとりとした心地よさに仕上がっています。<br />\r\nまた、キー部分を覆うフリップカバーはMEDIA SKINのシンプルな美しさと心地よい触感に貢献しているだけでなく、開閉動作と連動して着信応答や終話ができる使いやすさを兼ね備えています。</span></p>\r\n<p>&nbsp;</p>\r\n<p>&nbsp;</p>', '新しい触感と美しい映像をまとったエモーションナルケータイ。', '', 2, '', 0, 1, '', true, '', '', 'ko', 1.00, 0, 0, 0, 0.00, 'sales', 0, '', '', '', '/image/sample/product/au1.gif'),
(2, 'ja', 'AQUOS ケータイ', 'AU002', 1, '<p><span class="Text">液晶テレビ「AQUOS」の液晶技術を活かした美しい3.0インチ大画面のモバイルASV液晶を搭載。さらに「SVエンジン」「6色カラーフィルター」「明るさセンサー」を採用し、屋内外で鮮やかに見やすい映像を楽しめます。</span></p>\r\n<p>「サイクロイド」スタイルにより横向き全画面で「ワンセグ」を楽しめる！<span class="Text">画面を90&deg;回すだけで「ワンセグ」<small class="CaptionText"><font size="2">(注2)</font></small> が起動し、テレビを全画面で楽しみながらチャンネル選局もできる、独自の使いやすさを実現しました。<br />\r\n</span></p>', '3インチワイド液晶で「ワンセグ」＆「デジタルラジオ」が楽しめるAQUOSケータイ。', '', 2, '', 0, 2, '', true, '', '', 'ko', 1.00, 0, 0, 0, 0.00, 'sales', 0, '', '', '', '/image/sample/product/au2.gif'),
(3, 'ja', 'W51K', 'AU003', 1, '<p>薄さ約20mmのスリムボディながら、「ワンセグ」を大型2.7インチワイド液晶で楽しめほか、外部メモリへの番組録画も可能。また、LISMO「ビデオクリップ」の視聴も可能な最新LISMOサービスにも対応しています。</p>\r\n<p><span class="Text">「ワンセグ」、音楽、カメラなどをスマートに操作できる「フロントメディアキー」を搭載。液晶を表にして閉じた場合には「ワンセグ」やカメラの操作を、液晶を裏にして閉じた場合には音楽操作がラクラク。また、数字キーには、使いやすさとデザイン性を両立した「パネル型フレームレスキー」を採用しています。</span></p>\r\n<p>&nbsp;</p>', '迫力の大画面&高音質。', '', 2, '', 0, 3, '', true, '', '', 'ko', 1.00, 0, 0, 0, 0.00, 'sales', 0, '', '', '', '/image/sample/product/au3_2.png'),
(4, 'ja', 'W51P', 'AU004', 1, '<p><span class="Text">通話や着信をはじめ、FeliCaサイン、カメラ起動などを&quot;ヒカリ&quot;でお知らせ。<br />\r\nアシンメトリー (非対称) なデザインの「ソフトイルミネーションパネル」を採用することで、段差からの&quot;ヒカリ&quot;の射し込みにより、レリーフパターンが浮かび上がります。左右の段差が全く異なる表情を見せ、記号的ではない女性らしさを表現します。<br />\r\nまた、待受画面などの画面デザインも、本体のデザインにあわせた4つのパターンのテーマをプリセット。</span></p>\r\n<p><span class="Text"><span class="Text">「ワンプッシュオープン」機能</span></span></p>\r\n<p><span class="Text"><span class="Text">「ワンプッシュオープン」なら、ヒンジ横のボタンをプッシュするだけで、片手で素早くケータイをオープン。開く時のスマートさだけでなく、着信時には開けばそのまま通話も可能。不在着信・新着メールの表示がオープンするだけで確認できるなど、使いやすさも備えています。</span></span></p>', '”ヒカリ”が魅せる女性らしさ。ワンプッシュオープン対応の「おサイフケータイ」。', '', 2, '', 0, 4, '', true, '', '', 'ko', 1.00, 0, 0, 0, 0.00, 'sales', 0, '', '', '', '/image/sample/product/a4.png'),
(5, 'ja', 'W44', 'AU005', 1, '<p>ケータイの新しいカタチ「モバイルシアタースタイル」</p>\r\n<p><span class="Text">ケータイを横向きに開く新感覚の「モバイルシアタースタイル」を採用。観やすい横スタイルで「ワンセグ」<small class="CaptionText"><font size="2">(注2)</font></small> やLISMO「ビデオクリップ」などを存分に楽しめます。横スタイルに適した待受画面「マイスクリーン」や、横スタイルのためのメニュー「シアターメニュー」などの機能も充実。</span></p>\r\n<p><span class="Text">リアルにこだわった高画質大画面＆高音質</span></p>\r\n<p><span class="Text"><span class="Text">ケータイ最大級の約3インチフルワイド液晶を搭載。ソニー製液晶テレビ「BRAVIA」<small class="CaptionText"><font size="2">(注3)</font></small> の画質向上技術を採用した「RealityMAX&trade;」<small class="CaptionText"><font size="2">(注4)</font></small> により、映像も鮮明です。またCD並の高音質な音声と、動画や写真・文字によるデータ放送が楽しめる「デジタルラジオ」<small class="CaptionText"><font size="2">(注5)</font></small> にも対応。「DBEX&trade;」により、臨場感あふれるハイクオリティサウンドも実現しています</span></span></p>\r\n', '3.0インチ画面で、「ワンセグ」＆「デジタルラジオ」を楽しむDuel Styleケータイ。', '', 2, '', 0, 5, '', true, '', '', 'ko', 1.00, 0, 0, 0, 0.00, 'sales', 0, '', '', '', '/image/sample/product/a5.png'),
(6, 'ja', 'Gｚ　One', 'AU006', 1, '<p>&nbsp;</p>\r\n<p><span class="Text">ダイナミックなフォルムと洗練されたデザイン</span></p>\r\n<p><span class="Text"><span class="Text">ダイナミックなフォルムと緻密なディテールで、未来感を感じさせる新世代の&quot;タフネス&quot;デザインを表現。操作キーの照明に、本体色にマッチするカラーをそれぞれ採用。またサブ液晶は白黒反転表示にも対応し、オリジナルサイトからのダウンロードでカスタマイズも可能です。</span></span></p>\r\n<p><span class="Text">耐水性・耐衝撃性のタフネス性能をWINで実現</span></p>\r\n<p><span class="Text">IPX7相当 <small class="CaptionText"><font size="2">(注2)</font></small> の耐水性と、耐衝撃性をWINで実現。WIN＋タフネス性能により、WINの高機能をさまざまな場面で利用できます。</span></p>\r\n<p>&nbsp;</p>', '耐水・耐衝撃ボディと、大型液晶＆2.1メガカメラ。WIN初のタフネスケータイ。   ', '', 2, '', 0, 6, '', true, '', '', 'ko', 1.00, 0, 0, 0, 0.00, 'sales', 0, '', '', '', '/image/sample/product/a6.png'),
(7, 'ja', 'ジュニアケータイ', 'AU007', 1, '<p>いざというとき安心な「移動経路通知」</p>\r\n<p><span class="Text">お子さまが「防犯ブザー」を鳴らしたときや、ケータイの電源が切られたときには、その場所を家族のケータイに写真付きで緊急通知。その後は、約5分おきに更新される地図で、ケータイやパソコンからお子さまの足どりを確認できます。Cメールで強制的に起動/中止させることも可能です。</span></p>\r\n<p><span class="Text"><span class="Text">「防犯ブザー」は、いざというときに使いやすいひも引き型。ブザーが鳴らされると、カメラ撮影、家族への電話、現在位置と写真の緊急送信&amp;移動経路通知を自動で行います。また、電池の抜き取りを防ぐ「電池フタロック」 で、強制的な電源オフも防止。</span><br />\r\n</span></p>', '移動経路通知＆防犯ブザーストラップ、生活防水対応で安心のジュニアケータイ。', '', 2, '', 0, 7, '', true, '', '', 'ko', 1.00, 0, 0, 0, 0.00, 'sales', 0, '', '', '', '/image/sample/product/a7.png'),
(8, 'ja', 'Sweets cute', 'AU008', 1, '<p>お勉強などに役立つ便利機能もたっぷり。</p>\r\n<p><span class="Text">英和4万6千語&amp;和英5万6千語のGモバイル辞典と、国語4万7千語の明鏡モバイル国語辞典を搭載 <small class="CaptionText"><font size="2">(注1)</font></small>。また「カメラde辞書」機能では、漢字にカメラをかざすと漢字をよみがなに変換でき、漢字の意味も表示されます。時間割・おこづかい帳・日記帳などもプリセット。<br />\r\n</span></p>\r\n<p><span class="Text">やわらかフォルム＆ハートフルなデザイン</span></p>\r\n<p><span class="Text"><span class="Text">プロダクトデザイナー柴田文江氏による&quot;Sweets&quot;第3弾が登場。今度のテーマは「やさしい思いやりがいっぱいの、ハートフルなケータイ」です。初代Sweetsのかわいらしさを受け継ぎながら、コロンとしたスタイルと、ビスケットをディップしたようなやわらかなデザイン&amp;カラーも個性的。<br />\r\n</span></span></p>', '', '', 2, '', 0, 8, '', true, '', '', 'ko', 1.00, 0, 0, 0, 0.00, 'sales', 0, '', '', '', '/image/sample/product/a8.png'),
(9, 'ja', 'A5518SA', 'AU009', 1, '<p>コンパクトボディーでシンプル操作</p>\r\n<p><span class="Text">カメラ機能をなくすことで、ビジネス面でのセキュリティにも配慮。ボディは薄さ21mm・重さ103gのコンパクトさと、シンプルで使いやすい操作性を大切にしました。また大きく押しやすいキーと、見やすい「でか文字」で文字入力もラクラク。さらに、使いやすさを大切にした「フレンドリーデザイン」に対応しています。</span></p>\r\n<p><span class="Text"><span class="Text">アドレス帳には1,000件、スケジュール帳には500件まで、たっぷり保存可能。また「赤外線通信」を利用すると、アドレス帳登録やプロフィールの交換などもスムーズ。</span></span></p>', 'コンパクトで使いやすい、「フレンドリーデザイン」対応のカメラなしモデル。', '', 2, '', 0, 9, '', true, '', '', 'ko', 1.00, 0, 0, 0, 0.00, 'sales', 0, '', '', '', '/image/sample/product/a9.png'),
(10, 'ja', 'A5514SA', 'AU010', 1, '<p>&nbsp;</p>\r\n<p><span class="Text">海外でも話せる、メール＆EZwebも使える</span></p>\r\n<p><span class="Text">お申し込み不要で、そのまま海外に持ち出しても通話&amp;パケット通信が可能なグローバルパスポートに対応。渡航先でもいつもの電話番号のままでご利用いただけます。通話だけでなく、メールやEZwebも可能だから、旅先で撮った写真やムービーをその場で送るなど旅行にビジネスにさまざまなシーンで活躍します。海外のパケット通信対応エリアも順次拡大中。<br />\r\n<span class="Text">業界初の開いても閉じても突起のないフラットなスタイル「Smooth Style」を実現。従来のヒンジ部分がカットされた新機構のフォルムは、なめらかなラインで顔にフィットし、今までにない使いやすさを追求しました。またグローバルパスポート対応モデル初の内蔵アンテナで、海外でもコンパクトに持ち歩けます。<br />\r\n</span></span></p>', '海外でも話せる、コンパクト&フラットなグローバルパスポート対応モデル。', '', 2, '', 0, 10, '', true, '', '', 'ko', 1.00, 0, 0, 0, 0.00, 'sales', 0, '', '', '', '/image/sample/product/a10.png'),
(11, 'ja', '簡単ケータイ　A1406', 'AU011', 1, '<p>見やすさ、押しやすさ、聞きやすさを大切に</p>\r\n<p>・大きく押しやすい「でかキー」で、電話番号も文字もラクラク入力できます。</p>\r\n<p>・2.4インチの大画面液晶と、最大40ドットの大きな「でか文字」で、見やすい文字表示に。</p>\r\n<p>・混雑した場所でも相手の声が聞き取りやすい「でか受話音」。</p>\r\n<p>・<span class="Text">押すだけで決まった相手に電話をかけられる3つの「ワンタッチキー」を搭載。</span></p>\r\n<p>&nbsp;</p>', '大きなキーと大きな文字表示。ワンタッチキーで使いやすい「簡単ケータイ」。', '', 2, '', 0, 11, '', true, '', '', 'ko', 1.00, 0, 0, 0, 0.00, 'sales', 0, '', '', '', '/image/sample/product/a11.png'),
(12, 'ja', '簡単ケータイS　A101K', 'AU012', 1, '<p>電話のかけ方が、とにかく簡単</p>\r\n<p>ご自宅のコードレス電話と同じような使い方で話せます。</p>\r\n<p>よく電話する相手を、「ワンタッチボタン」に登録すると、もっと簡単に話せます。設定も簡単です。</p>\r\n<p><span class="LargeText">より便利にお使いいただけるよう、登録した相手の名前を書き込める専用シールが付属しています。</span></p>\r\n<p><span class="LargeText"><span class="LargeText">自分の電話番号を書き込むことができ、落下防止にも配慮した「クリップ付きストラップ」。自分の電話番</span></span></p>\r\n<p><span class="LargeText"><span class="LargeText">号の確認や持ち歩きにも安心です。</span></span></p>', '', '', 2, '', 0, 12, '', true, '', '', 'ko', 1.00, 0, 0, 0, 0.00, 'sales', 0, '', '', '', '/image/sample/product/a12.png'),
(13, 'ja', 'FOMA D904i', 'DO001', 1, '<p>9シリーズ最薄16.8ｍｍのスリム・スライド</p>\r\n<p>2.8インチワイドQVGA液晶搭載で、凹凸の無いスリムデザインを採用。新機構「アシストスライド」により、開けても閉じても心地よいなめらかなスライド開閉を実現しています。</p>\r\n<p>ケータイを振るだけで機能が連動する「モーショナルコントロール」対応</p>\r\n<p>モーションコントロール（加速度センサー）搭載でケータイを振ったり、傾けたりすることに機能が連動します。</p>\r\n<ul class="normal txt">\r\n    <li>ケータイを左ヨコに倒すことで、自動的にヨコ向きワイド画面表示に切り替えが可能。</li>\r\n    <li>「直感ゲーム」対応で、ケータイを動かす操作で遊べるiアプリ｢タマラン｣をプリインストール。</li>\r\n    <li>ケータイを逆さまにしたり振ったりすることに連動してドコモダケなどの「マチキャラ」も動作。</li>\r\n</ul>', '携帯を振って動かす直感操作が新しい、スリム・スライドケータイ', '', 1, '', 0, 1, '', true, '', '', 'ko', 1.00, 0, 0, 0, 0.00, 'sales', 0, '', '', '', '/image/sample/product/do1.png'),
(14, 'ja', 'FOMA　F901i', 'DO002', 1, '<p>3.1インチフルワイド大画面で楽しめるワンセグ対応</p>\r\n<ul class="normal txt">\r\n    <li>画面をヨコにして、テレビとリモコンが1つになったようなスタイルでワンセグ視聴が可能。</li>\r\n    <li>照光センサーによる「明るさ自動調整機能」や、メールを作成しながらワンセグを視聴する「マルチウィンドウ」、字幕を大きく表示する「アドバンストモード」対応などワンセグ視聴に配慮した機能も充実。</li>\r\n    <li>IPS液晶搭載で、早い動きの表示に強く、約170度の広い視野角で視聴が可能</li>\r\n</ul>', '3.1インチ・フルワイド大画面でワンセグを楽しめるヨコモーションケータイ', '', 1, '', 0, 2, '', true, '', '', 'ko', 1.00, 0, 0, 0, 0.00, 'sales', 0, '', '', '', '/image/sample/product/do2.png'),
(15, 'ja', 'FOMA　P904i', 'DO003', 1, '<p>シンプルで新しいコンパクトデザイン＆Newカスタムジャケット</p>\r\n<p class="txt">シンプルでコンパクトなミラーパネルが美しいアシンメトリーデザインと、カスタムジャケット無しでも完成するデザインです。カスタムジャケットは今までのデザイン的な変化だけでなく、プライベートウィンドウ（背面ディスプレイ）の表情も変化する新しいコンセプトを採用しました。また、ケータイの背面にヒカリで不在着信など各種情報が浮かび上がる「ヒカリアイコン<span class="sup">TM</span>」も搭載しています。</p>\r\n<p class="txt">ケータイで1つの音楽を定額で楽しむスタイル「うた・ホーダイ」に対応</p>\r\n<p>&nbsp;</p>\r\n<p>\r\n<li>「うた・ホーダイ」に対応。</li>\r\n<li>Windows Media&reg; Audio（WMA）にも対応し、月額1980円（税込）で250万曲以上を聴き放題の音楽配信サービス「Napster&reg;」も楽しめる。</li>\r\n<li>SDオーディオなら最長約65時間の長時間再生が可能。</li>\r\n<li>Bluetooth&reg;対応だからワイヤレスで音楽を楽しめる。ケータイとワイヤレスイヤホンの接続も従来の13タッチから3タッチに短縮。</li>\r\n</p>\r\n<p>&nbsp;</p>', 'Newデザイン＆Newカスタムジャケット対応のワイヤレスミュージックケータイ', '', 1, '', 0, 3, '', true, '', '', 'ko', 1.00, 0, 0, 0, 0.00, 'sales', 0, '', '', '', '/image/sample/product/do3.png'),
(16, 'ja', 'FOMA N703iμ', 'DO004', 1, '<p>11.4mmｍｐ超薄型に掲載される高機能</p>\r\n<p class="txt">薄さが際立つスポーティなデザイン、その中に先進機能を搭載しています。超薄型ボディの表面には、LEDやスピーカー機能を兼ね備えたディンプルをデザイン。薄さが際立つツートーンのカラーリングでいっそう美しく、さらに、メガピクセルカメラや2.3インチQVGA+<span class="sup">TM</span> 液晶、microSD<span class="sup">TM</span>メモリーカードスロットを搭載しました。</p>\r\n<p class="txt">内蔵コンテンツを、スタイリングモードで一括設定</p>\r\n<p class="txt">待受画面をはじめ、メニュー画面、状態表示、アイコン、ミュージックプレーヤー画面などをお好みで設定できます。</p>', '厚さ11.4mmの世界最薄', '', 1, '', 0, 4, '', true, '', '', 'ko', 1.00, 0, 0, 0, 0.00, 'sales', 0, '', '', '', '/image/sample/product/do4.png'),
(17, 'ja', 'FOMA　P703iμ', 'DO005', 1, '<p>超薄型11.4ｍｍの、新素材感ステンレスボディ</p>\r\n<p class="txt">スリムサイズと軽量化を実現しました。カードサイズに近いサイズ感で、小さくて扱いやすいデザインです。基板を樹脂で固めて強度を上げる新工法を採用しました。ゆがみやねじれなど外からの力に強く、薄さと強さを両立したタフなボディです。アウトカメラ側ボディに使われたステンレスのクールな質感は、デザイン性と共に強度を強めます。</p>\r\n<p class="txt">SDオーディオを搭載</p>\r\n<p class="txt">最大2GBのmicroSD<span class="sup">TM</span>メモリーカードに、ネットストア「MOOCS」やCD、コンポから入手した曲を転送することができます。メールやiモードの操作も同時にできます。</p>\r\n<p class="txt">&nbsp;</p>', '厚さ11.4mmの高級感を醸し出す、Super Slimステンレスボディ', '', 1, '', 0, 5, '', true, '', '', 'ko', 1.00, 0, 0, 0, 0.00, 'sales', 0, '', '', '', '/image/sample/product/do5.png'),
(18, 'ja', 'FOMA　F703i', 'DO006', 1, '<p>水に濡れても安心のIPX5,IPX7の性能</p>\r\n<p>取扱いやすい防水キャップを採用</p>\r\n<p class="txt">キャップの半挿しによる浸水を未然に防ぐため、閉めやすく、且つ完全に閉まったときの感触が指に伝わる構造にしました。</p>\r\n<ul class="txt normal">\r\n    <li>雨の中で傘をささずに通話できます。（1時間の雨量が20mm程度）</li>\r\n    <li>お風呂場で使用できます。</li>\r\n    <li>洗面器などに張った静水につけて、ゆすりながら汚れを洗い落とすことができます。</li>\r\n</ul>', '日常生活にフィットするウォータープルーフ・スリムケータイ', '', 1, '', 0, 6, '', true, '', '', 'ko', 1.00, 0, 0, 0, 0.00, 'sales', 0, '', '', '', '/image/sample/product/do6.png'),
(19, 'ja', 'FOMA　703i', 'DO007', 1, '<p>メールの打ちやすさを追及した「Wave Tile Key」</p>\r\n<p>操作性を重視した立体形状の「Wave Tile Key<span class="sup">TM</span>」。フレームレスだからキーが大きく、ネイルアートを施した女性の長い爪でもメールの文字入力がスムーズです。また白色のキーバックライトが高級感を演出します。待受画面には世界中で活躍中の「はやさきちーこ」の繊細な線とエレガントな色彩が調和したイラスト3タイプをプリインストールしています。フランスで出版されると同時に大人気となった、オトナの絵本ブームの火付け役、「リサとガスパール」をプリインストールしています</p>', 'メール機能にこだわったHappyデコメケータイ', '', 1, '', 0, 7, '', true, '', '', 'ko', 1.00, 0, 0, 0, 0.00, 'sales', 0, '', '', '', '/image/sample/product/do7.png'),
(20, 'ja', 'FOMA　SO703i', 'DO008', 1, '<p>デザイン</p>\r\n<p>本体フロント面は、着せかえのできる「Style-Up&reg;」パネルを採用しています。それぞれのパネルのテーマに合わせた「アロマシート<span class="sup">TM</span>」も付属しました。「アロマシート<span class="sup">TM</span>」を本体部分に貼り付けることで、パネルのデザインと香りを組み合わせてお楽しみいただけます。</p>\r\n<p>\r\n<li>アロマシート<span class="sup">TM</span>は香りのマイクロカプセルをシート状にしたもので、FOMA端末に取り付けて香りをお楽しみいただけます。</li>\r\n<li>香りが弱くなってきた場合は、アロマシート<span class="sup">TM</span>の表面を指で軽くこすると、マイクロカプセルがはじけ香りがします。</li>\r\n<li>アロマシート<span class="sup">TM</span>は消耗品です。マイクロカプセルがすべてはじけると、香りは出なくなります。香りの持続期間は約3ヶ月間ですが、温度、湿度などの環境やアロマシート<span class="sup">TM</span>をこする回数により変わります。</li>\r\n<li>香りの感じ方には個人差があります。</li>\r\n</p>', '香りもデザインも着せ替えられる、アロマケータイ', '', 1, '', 0, 8, '', true, '', '', 'ko', 1.00, 0, 0, 0, 0.00, 'sales', 0, '', '', '', '/image/sample/product/do8.png');

-- Eコマース設定マスター
UPDATE commerce_config SET cg_value = '1' WHERE cg_id = 'use_sale_price';

-- 多言語対応文字列マスター
DELETE FROM _language_string WHERE ls_type = 1 AND ls_id = 'word_account';
INSERT INTO _language_string
(ls_type, ls_id,                     ls_language_id, ls_value,                             ls_name) VALUES
(1,       'word_account',         'ja',           'ID',                 'アカウント');

-- 画像サイズマスター(新サイズ)
DELETE FROM image_size;
INSERT INTO image_size (is_id, is_type, is_name, is_width, is_height, is_sort_order) VALUES ('full-banner',      2, 'フルサイズバナー',     640, 128,  1);
INSERT INTO image_size (is_id, is_type, is_name, is_width, is_height, is_sort_order) VALUES ('half-banner',      2, 'ハーフサイズバナー',   320, 128,  2);
INSERT INTO image_size (is_id, is_type, is_name, is_width, is_height, is_sort_order) VALUES ('small-banner',     2, 'スモールサイズバナー', 320, 64,  3);
INSERT INTO image_size (is_id, is_type, is_name, is_width, is_height, is_sort_order) VALUES ('micro-banner',     2, 'マイクロバナー',       160,  64,  4);
INSERT INTO image_size (is_id, is_type, is_name, is_width, is_height, is_sort_order) VALUES ('standard-product', 3, '商品用標準サイズ',     640, 640,  5);
INSERT INTO image_size (is_id, is_type, is_name, is_width, is_height, is_sort_order) VALUES ('small-product',    3, '商品用小サイズ',       320,  320,  6);
INSERT INTO image_size (is_id, is_type, is_name, is_width, is_height, is_sort_order) VALUES ('large-product',    3, '商品用大サイズ',       1280, 1280, 7);
INSERT INTO image_size (is_id, is_type, is_name, is_width, is_height, is_sort_order) VALUES ('exlarge-product',  3, '商品用特大サイズ',     2000, 2000, 8);
