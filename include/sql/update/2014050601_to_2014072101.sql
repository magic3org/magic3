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
-- ウィジェットカテゴリマスター
DELETE FROM _widget_category;
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

-- テンプレート情報
DELETE FROM _templates WHERE tm_id = 'bootstrap_united';
INSERT INTO _templates
(tm_id,                           tm_name,                         tm_type, tm_device_type, tm_mobile, tm_use_bootstrap, tm_available, tm_clean_type, tm_create_dt) VALUES
('bootstrap_united',             'bootstrap_united',             10,       0,              false,     true,             true,        0,             now());

-- *** システム標準テーブル ***
