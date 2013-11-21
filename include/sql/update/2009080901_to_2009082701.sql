-- *
-- * バージョンアップ用スクリプト
-- *
-- * PHP versions 5
-- *
-- * LICENSE: This source file is licensed under the terms of the GNU General Public License.
-- *
-- * @package    Magic3 Framework
-- * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
-- * @copyright  Copyright 2006-2009 Magic3 Project.
-- * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
-- * @version    SVN: $Id: 2009080901_to_2009082701.sql 2288 2009-09-05 04:50:12Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
-- システム設定マスター
UPDATE _system_config SET sc_value = 'SJIS-win' WHERE sc_id = 'mobile_encoding';

-- *** システム標準テーブル ***
-- コンテンツマスター
ALTER TABLE content ADD cn_meta_description  TEXT                                         NOT NULL;      -- METAタグ、ページ要約
ALTER TABLE content ADD cn_meta_keywords     TEXT                                         NOT NULL;      -- METAタグ、検索用キーワード

-- ブログエントリー(記事)マスター
ALTER TABLE blog_entry ADD be_theme_id       VARCHAR(20)    DEFAULT ''                    NOT NULL;      -- ブログテーマID
