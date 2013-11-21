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
-- * @version    SVN: $Id: 2010122801_to_2011011101.sql 3943 2011-01-19 10:30:15Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
-- ページ定義マスター
ALTER TABLE _page_def ADD pd_except_sub_id  TEXT                                         NOT NULL;      -- 共通時例外ページサブID(「,」区切りで複数指定可)

-- ウィジェット情報マスター
ALTER TABLE _widgets ADD wd_admin           BOOLEAN        DEFAULT false                 NOT NULL;      -- 管理用ウィジェットかどうか

-- *** システム標準テーブル ***

