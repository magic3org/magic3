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
('content',     '汎用コンテンツ',   1),
('blog',     'ブログ',           2),
('commerce',     'Eコマース',        3),
('photo',     'フォトギャラリー', 4),
('event',     'イベント情報',     5),
('subcontent',     '補助コンテンツ',     6),
('search',     '検索',             7),
('reguser',     'ユーザ登録',      8),
('menu',     'メニュー',         9),
('image',     '画像',         10),
('design',    'デザイン',         11),
('admin',     '管理画面用',      20);

-- *** システム標準テーブル ***

