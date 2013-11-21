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
-- * @version    SVN: $Id: 2010102901_to_2010111701.sql 3855 2010-11-22 03:10:38Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
-- ページ定義マスター
ALTER TABLE _page_def ADD pd_active_start_dt   TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL;      -- アクセス可能期間(開始)
ALTER TABLE _page_def ADD pd_active_end_dt     TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL;      -- アクセス可能期間(終了)
ALTER TABLE _page_def ADD pd_edit_status       SMALLINT       DEFAULT 0                     NOT NULL;      -- 編集状態(0=編集完了、1=編集中)

-- *** システム標準テーブル ***
-- ブログ設定マスター
INSERT INTO blog_config
(bg_id,                     bg_value,    bg_name) VALUES
('m:title_color',           '',         'タイトルの背景色');
