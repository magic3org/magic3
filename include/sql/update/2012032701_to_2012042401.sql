-- *
-- * バージョンアップ用スクリプト
-- *
-- * PHP versions 5
-- *
-- * LICENSE: This source file is licensed under the terms of the GNU General Public License.
-- *
-- * @package    Magic3 Framework
-- * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
-- * @copyright  Copyright 2006-2012 Magic3 Project.
-- * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
-- * @version    SVN: $Id: 2012032701_to_2012042401.sql 4890 2012-04-26 22:30:39Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
-- ページIDマスター
ALTER TABLE _page_id ADD pg_admin_menu           BOOLEAN        DEFAULT false                  NOT NULL;      -- 管理メニューを表示するかどうか(ページID種別がアクセスポイント時。初期値。)
ALTER TABLE _page_id ADD pg_analytics           BOOLEAN        DEFAULT false                  NOT NULL;      -- アクセス解析対象かどうか(ページID種別がアクセスポイント時)
UPDATE _page_id SET pg_admin_menu = true WHERE pg_id = 'index' AND pg_type = 0;
UPDATE _page_id SET pg_analytics = true WHERE pg_id = 'index' AND pg_type = 0;
UPDATE _page_id SET pg_analytics = true WHERE pg_id = 'm_index' AND pg_type = 0;
UPDATE _page_id SET pg_analytics = true WHERE pg_id = 's_index' AND pg_type = 0;

-- *** システム標準テーブル ***

