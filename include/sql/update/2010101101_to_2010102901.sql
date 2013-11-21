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
-- * @version    SVN: $Id: 2010101101_to_2010102901.sql 3758 2010-10-29 12:56:40Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
-- サイト解析ページビュートラン
ALTER TABLE _analyze_page_view DROP INDEX ap_type;                                                       -- ユニーク制約削除
ALTER TABLE _analyze_page_view ADD UNIQUE (ap_type,      ap_url,   ap_path, ap_date,       ap_hour);     -- ユニーク制約再設定

-- サイト解析日時カウントトラン
ALTER TABLE _analyze_daily_count DROP INDEX aa_type;                                                  -- ユニーク制約削除
ALTER TABLE _analyze_daily_count ADD UNIQUE (aa_type,  aa_url,        aa_path,   aa_date);            -- ユニーク制約再設定

-- *** システム標準テーブル ***

