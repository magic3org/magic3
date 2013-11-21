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
-- * @version    SVN: $Id: 2011051401_to_2011060601.sql 4172 2011-06-08 09:14:26Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
-- キャッシュトラン
ALTER TABLE _cache MODIFY ca_url               VARCHAR(191)   DEFAULT ''                    NOT NULL;      -- アクセスURL

-- サイト解析ページビュートラン
ALTER TABLE _analyze_page_view MODIFY ap_url               VARCHAR(191)   DEFAULT ''                    NOT NULL;      -- URL

-- サイト解析日時カウントトラン
ALTER TABLE _analyze_daily_count MODIFY aa_url             VARCHAR(191)   DEFAULT ''                    NOT NULL;      -- URL

-- *** システム標準テーブル ***
-- Wikiコンテンツマスター
ALTER TABLE wiki_content MODIFY wc_id                VARCHAR(191)   DEFAULT ''                    NOT NULL;      -- コンテンツID
