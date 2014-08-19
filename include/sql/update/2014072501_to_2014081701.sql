-- *
-- * バージョンアップ用スクリプト
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
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
-- ページIDマスター
DELETE FROM _page_id WHERE pg_type = 1;
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
('reserve',      1,            '予約',                             '予約画面用',                         19,          true,      true,       true,        false),
('member',       1,            '会員',                             '会員画面用',                         20,          true,      true,       true,        true),
('search',       1,            '検索',                             '検索画面用',                         21,          true,      true,       true,        true),
('user',         1,            'ユーザコンテンツ',                 'ユーザ作成コンテンツ用',             50,          true,      true,       true,        true),
('deploy',       1,            'ウィジェット有効化用',             'ウィジェット有効化用',               100,         true,      false,      true,        false),
('test',         1,            'ウィジェットテスト用',             'ウィジェットテスト用非公開画面',     101,         false,     true,       true,        false);

-- ページ情報マスター
DELETE FROM _page_info;
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
('index',     'member',    'member',        true),
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
('m_index',   'member',    'member',        true),
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
('s_index',   'member',    'member',        true),
('s_index',   'search',    'search',        false),
('s_index',   'contact',   '',              true),
('s_index',   'contact2',  '',              true),
('s_index',   'safe',      '',              true),
('admin_index', 'front',   'dboard',        false),
('connector', 'content',   'content',       false);

-- ウィジェットカテゴリマスター
DELETE FROM _widget_category;
INSERT INTO _widget_category
(wt_id,        wt_name,                wt_sort_order) VALUES
('',           'その他',               100),
('content',    '汎用コンテンツ',       1),
('blog',       'ブログ',               2),
('bbs',        'BBS',                  3),
('commerce',   'Eコマース',            4),
('photo',      'フォトギャラリー',     5),
('event',      'イベント情報',         6),
('wiki',       'Wiki',                 7),
('user',       'ユーザ作成コンテンツ', 8),
('member',     '会員',                 9),
('subcontent', '補助コンテンツ',       20),
('search',     '検索',                 21),
('menu',       'メニュー',             22),
('image',      '画像',                 23),
('design',     'デザイン',             24),
('admin',      '管理画面用',           50);

-- *** システム標準テーブル ***
