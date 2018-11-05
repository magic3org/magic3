-- *
-- * バージョンアップ用スクリプト
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
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
-- ウィジェットカテゴリマスター
DELETE FROM _widget_category;
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
('meta',        '',            'メタ機能',             27,            true),
('analytics',   '',            'サイト解析',           28,            true),
('admin',       'admin',       '管理画面用',           50,            true),
('test',        'test',        'テスト用',             200,           true);


-- *** システム標準テーブル ***
