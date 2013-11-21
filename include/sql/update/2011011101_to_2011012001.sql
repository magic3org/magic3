-- *
-- * バージョンアップ用スクリプト
-- *
-- * PHP versions 5
-- *
-- * LICENSE: This source file is licensed under the terms of the GNU General Public License.
-- *
-- * @package    Magic3 Framework
-- * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
-- * @copyright  Copyright 2006-2011 Magic3 Project.
-- * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
-- * @version    SVN: $Id: 2011011101_to_2011012001.sql 3957 2011-01-24 05:06:14Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
-- ページIDマスター
ALTER TABLE _page_id ADD pg_visible           BOOLEAN        DEFAULT true                  NOT NULL;      -- ページを表示するかどうか

-- *** システム標準テーブル ***
-- ブログコメントトラン
ALTER TABLE blog_comment MODIFY bo_html     TEXT NOT NULL;       -- 本文HTML

-- ブログ設定マスター
INSERT INTO blog_config
(bg_id,                     bg_value,    bg_name) VALUES
('comment_max_length',      '300',       'コメント最大文字数');
