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
-- * @version    SVN: $Id: 2010081801_to_2010082001.sql 3541 2010-08-28 03:20:00Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
-- ページIDマスター
ALTER TABLE _page_id ADD pg_url                TEXT                                         NOT NULL;      -- アクセスURL(ページID種別がページメインIDとき使用)

-- 検索キーワードトラン
ALTER TABLE _search_word ADD sw_path              VARCHAR(40)    DEFAULT ''                    NOT NULL;      -- アクセスポイントパス
