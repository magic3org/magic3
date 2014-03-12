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
('user',     'ユーザ作成コンテンツ',     7),
('subcontent',     '補助コンテンツ',     8),
('search',     '検索',             9),
('reguser',     'ユーザ登録',      10),
('menu',     'メニュー',         11),
('image',     '画像',         12),
('design',    'デザイン',         13),
('admin',     '管理画面用',      20);

-- ウィジェット情報マスター
ALTER TABLE _widgets ADD wd_type_option   VARCHAR(10)    DEFAULT ''                    NOT NULL;      -- ウィジェット種別オプション(nav=ナビゲーションメニュー)

-- *** システム標準テーブル ***

