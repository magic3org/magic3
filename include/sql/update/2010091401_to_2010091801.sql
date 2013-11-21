-- *
-- * バージョンアップ用スクリプト
-- *
-- * PHP versions 5
-- *
-- * LICENSE: This source file is licensed under the terms of the GNU General Public License.
-- *
-- * @package    Magic3 Framework
-- * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
-- * @copyright  Copyright 2006-2010 Magic3 Project.
-- * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
-- * @version    SVN: $Id: 2010091401_to_2010091801.sql 3628 2010-09-24 15:46:05Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
-- 追加クラスマスター
DELETE FROM _addons WHERE ao_id = 'bloglib';
INSERT INTO _addons
(ao_id,     ao_class_name, ao_name,            ao_description) VALUES
('bloglib', 'blogLib',     'ブログライブラリ', '');

-- *** システム標準テーブル ***
-- ブログ設定マスター
INSERT INTO blog_config
(bg_id,                    bg_value,    bg_name) VALUES
('use_multi_blog',         '0',         'マルチブログを使用'),
('multi_blog_top_content', '',          'マルチブログのトップ画面コンテンツ'),
('category_count',         '2',         '記事に設定可能なカテゴリ数');

-- ブログIDマスター
ALTER TABLE blog_id ADD bl_user_limited      BOOLEAN        DEFAULT false                 NOT NULL;      -- 参照ユーザを制限
ALTER TABLE blog_id ADD bl_index             INT            DEFAULT 0                     NOT NULL;      -- ソート用

-- ブログエントリー(記事)マスター
ALTER TABLE blog_entry ADD be_user_limited      BOOLEAN        DEFAULT false                 NOT NULL;      -- 参照ユーザを制限

