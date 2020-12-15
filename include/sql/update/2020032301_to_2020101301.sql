-- *
-- * バージョンアップ用スクリプト
-- *
-- * PHP versions 5
-- *
-- * LICENSE: This source file is licensed under the terms of the GNU General Public License.
-- *
-- * @package    Magic3 Framework
-- * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
-- * @copyright  Copyright 2006-2020 Magic3 Project.
-- * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
-- * @version    SVN: $Id$
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
-- 不要テーブル削除(セッション管理トラン)
DROP TABLE IF EXISTS _session;

-- 自動ログインマスター
ALTER TABLE _auto_login MODIFY ag_id     CHAR(128)       DEFAULT ''                    NOT NULL;      -- 自動ログインキー
ALTER TABLE _auto_login DROP INDEX ag_user_id

-- テンプレート情報
DELETE FROM _templates WHERE tm_id = 'themler_old';

-- *** システム標準テーブル ***

