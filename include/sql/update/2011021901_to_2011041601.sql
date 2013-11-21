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
-- * @version    SVN: $Id: 2011021901_to_2011041601.sql 4091 2011-05-01 11:58:45Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
-- 追加クラスマスター
ALTER TABLE _addons MODIFY ao_id     VARCHAR(20)    DEFAULT ''                    NOT NULL;      -- クラスID

-- 運用ログトラン
ALTER TABLE _operation_log ADD ol_link_url TEXT                                   NOT NULL;      -- リンク用URL

-- ログインユーザマスター
ALTER TABLE _login_user ADD lu_avatar            VARCHAR(40)    DEFAULT ''        NOT NULL;      -- アバターファイル名
