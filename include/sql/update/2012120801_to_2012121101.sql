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
-- * @version    SVN: $Id: 2012120801_to_2012121101.sql 5471 2012-12-13 14:26:27Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
-- メニュー定義マスター
ALTER TABLE _menu_def ADD md_user_limited      BOOLEAN        DEFAULT false                 NOT NULL;      -- アクセス可能ユーザを制限

-- ページ定義マスター
ALTER TABLE _page_def MODIFY pd_suffix            VARCHAR(10)     DEFAULT ''                    NOT NULL;      -- インスタンスを区別するためのサフィックス文字列

-- *** システム標準テーブル ***

