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
-- * @version    SVN: $Id: 2011080401_to_2011081801.sql 4312 2011-09-12 12:19:59Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
-- ウィジェット情報マスター
ALTER TABLE _widgets ADD wd_license_type    INT            DEFAULT 0                     NOT NULL;      -- ライセンスタイプ(0=オープンソース、1=商用)

-- ログインユーザマスター
ALTER TABLE _login_user ADD lu_admin_widget            TEXT                              NOT NULL;      -- システム運営者が管理可能なウィジェット(「,」区切りで複数指定可)
ALTER TABLE _login_user ADD lu_user_type_option        TEXT                              NOT NULL;      -- ユーザタイプオプション(「ウィジェットID=ユーザタイプ」形式の前後「;」区切りで複数指定可)
