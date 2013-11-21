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
-- * @version    SVN: $Id: 2011051201_to_2011051401.sql 4135 2011-05-14 15:01:14Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
-- ウィジェット情報マスター
ALTER TABLE _widgets ADD wd_content_type              VARCHAR(10)    DEFAULT ''                    NOT NULL;      -- ページのコンテンツ種別

-- ログインユーザマスター
ALTER TABLE _login_user ADD lu_regist_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL;      -- 登録日時

-- 運用ログトラン
ALTER TABLE _operation_log ADD ol_search_option          TEXT                                         NOT NULL;      -- 検索用補助データ
