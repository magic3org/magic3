-- *
-- * データ登録スクリプト「Eコマースサイト初期化」
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
-- [Eコマースサイト初期化]
-- Eコマース主軸型サイト。
-- Eコマース機能にアクセスしやすいようにカスタマイズした管理画面
-- 初期インストールデータは必要最小限のみ

-- システム設定
UPDATE _system_config SET sc_value = 'art42_sample5' WHERE sc_id = 'default_template';
UPDATE _system_config SET sc_value = '0' WHERE sc_id = 'site_menu_hier';

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
('search',       1,            '検索',                             '検索画面用',                         21,          true,      true,       true);

-- ページの固定テンプレートをリセット
UPDATE _page_info SET pn_template_id = '' WHERE pn_id = 'index' AND pn_deleted = false;

-- 管理画面メニューデータ
DELETE FROM _nav_item WHERE ni_nav_id = 'admin_menu';
DELETE FROM _nav_item WHERE ni_nav_id = 'admin_menu.en';
INSERT INTO _nav_item
(ni_id, ni_parent_id, ni_index, ni_nav_id,    ni_task_id,             ni_view_control, ni_param,       ni_name,                ni_help_title,            ni_help_body) VALUES
(100,   0,            0,        'admin_menu', '_page',                0,               '',             '画面管理',             '画面管理',               'Webサイトのデザインや機能を管理します。'),
(101,   100,          0,        'admin_menu', 'pagedef',              0,               '',             'PC画面',               'PC画面編集',             'PC用Webサイトの画面を作成します。'),
(102,   100,          1,        'admin_menu', 'pagedef_smartphone',   0,               '',             'スマートフォン画面',   'スマートフォン画面編集', 'スマートフォン用Webサイトの画面を作成します。'),
(103,   100,          2,        'admin_menu', '_103',                 3,               '',             'セパレータ',           '',                       ''),
(104,   100,          3,        'admin_menu', 'widgetlist',           0,               '',             'ウィジェット管理',     'ウィジェット管理',       'ウィジェットの管理を行います。'),
(105,   100,          4,        'admin_menu', 'templist',             0,               '',             'テンプレート管理',     'テンプレート管理',       'テンプレートの管理を行います。'),
(106,   100,          5,        'admin_menu', 'smenudef',             0,               '',             'メニュー管理',         'メニュー管理',           'メニュー定義を管理します。'),
(199,   0,            1,        'admin_menu', '_199',                 1,               '',             '改行',                 '',                       ''),
(200,   0,            2,        'admin_menu', '_login',               0,               '',             'システム運用',         '',                       ''),
(201,   200,          0,        'admin_menu', 'userlist',             0,               '',             'ユーザ管理',           'ユーザ管理',             'ログイン可能なユーザを管理します。'),
(202,   200,          1,        'admin_menu', 'accesslog',            0,               '',             '運用状況',             '運用状況',               'サイトの運用状況を表示します。'),
(299,   0,            3,        'admin_menu', '_299',                 1,               '',             '改行',                 '',                       ''),
(300,   0,            4,        'admin_menu', '_config',              0,               '',             'システム管理',         '',                       ''),
(301,   300,          0,        'admin_menu', 'configsite',           0,               '',             '基本情報',             '基本情報',               'サイト運営に必要な情報を設定します。'),
(302,   300,          1,        'admin_menu', 'configsys',            0,               '',             'システム情報',         'システム情報',           'システム全体の設定、運用状況を管理します。'),
(303,   300,          2,        'admin_menu', 'mainte',               0,               '',             'メンテナンス',         'メンテナンス',           'ファイルやDBなどのメンテナンスを行います。'),
(399,   0,            5,        'admin_menu', '_399',                 1,               '',             '改行',                 '',                       ''),
(500,   0,            6,        'admin_menu', '_daily',               0,               '',             '日常処理',             '',                       ''),
(501,   500,          0,        'admin_menu', 'configwidget_ec_main', 0,               'task=order',   '受注管理',             '受注管理',               '受注管理を行います。'),
(502,   500,          1,        'admin_menu', 'configwidget_ec_main', 0,               'task=product', '商品管理',             '商品管理',               '商品管理を行います。'),
(503,   500,          2,        'admin_menu', 'configwidget_ec_main', 0,               'task=member',  '会員管理',             '会員管理',               '会員情報を管理します。');

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
(md_id, md_index, md_menu_id,  md_name,                  md_link_url,                                         md_update_dt) VALUES
(1,     1,        'main_menu', 'ホーム',                 '[#M3_ROOT_URL#]/',                                   now()),
(2,     2,        'main_menu', '通信販売法に基づく表記', '[#M3_ROOT_URL#]/index.php?contentid=3', now()),
(3,     3,        'main_menu', '個人情報保護方針',       '[#M3_ROOT_URL#]/index.php?contentid=2',             now()),
(4,     4,        'main_menu', '会社情報',               '[#M3_ROOT_URL#]/index.php?contentid=1',             now()),
(5,     5,        'main_menu', 'お問い合わせ',           '[#M3_ROOT_URL#]/index.php?sub=contact',             now());

-- ウィジェットパラメータ
DELETE FROM _widget_param WHERE wp_id = 'ec_product_display2';
DELETE FROM _widget_param WHERE wp_id = 'static_content';
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
('ec_menu', 1, 'ja', 'docomo',              '',         '<p align="center"><img width="300" height="50" alt="" src="[#M3_ROOT_URL#]/resource/image/sample/product_head/dc1.jpg" /></p>',                        false, '', 0, now()),
('ec_menu', 2, 'ja', 'au',              '',         '<p align="center"><img width="300" height="50" alt="" src="[#M3_ROOT_URL#]/resource/image/sample/product_head/au1.jpg" /></p>',                        false, '', 0, now());

-- バナー
TRUNCATE TABLE bn_def;
TRUNCATE TABLE bn_item;

-- 商品情報
TRUNCATE TABLE product_price;
TRUNCATE TABLE product_status;
TRUNCATE TABLE product_image;
TRUNCATE TABLE product_category;
TRUNCATE TABLE product_with_category;
TRUNCATE TABLE product;

-- 多言語対応文字列マスター
DELETE FROM _language_string WHERE ls_type = 1 AND ls_id = 'word_account';
INSERT INTO _language_string
(ls_type, ls_id,                     ls_language_id, ls_value,                             ls_name) VALUES
(1,       'word_account',         'ja',           'ID',                 'アカウント');