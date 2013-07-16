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
-- * @version    SVN: $Id: 2012053101_to_2012061901.sql 4988 2012-06-21 21:56:59Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
-- ログインユーザマスター
ALTER TABLE _login_user ADD lu_email            VARCHAR(40)    DEFAULT ''        NOT NULL;      -- Eメールアドレス
ALTER TABLE _login_user ADD lu_skype_account            VARCHAR(40)    DEFAULT ''        NOT NULL;      -- Skypeアカウント

-- ウィジェット情報マスター
ALTER TABLE _widgets ADD wd_edit_content           BOOLEAN        DEFAULT false                 NOT NULL;      -- 主要コンテンツ編集可能かどうか
ALTER TABLE _widgets ADD wd_parent_id         VARCHAR(50)    DEFAULT ''                    NOT NULL;      -- 親ウィジェットID(ファイル名)
