-- *
-- * バージョンアップ用スクリプト
-- *
-- * PHP versions 5
-- *
-- * LICENSE: This source file is licensed under the terms of the GNU General Public License.
-- *
-- * @package    Magic3 Framework
-- * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
-- * @copyright  Copyright 2006-2008 Magic3 Project.
-- * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
-- * @version    SVN: $Id: 2008111301_to_2008111901.sql 1271 2008-11-24 03:28:51Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- システムベーステーブル
ALTER TABLE _operation_log ADD ol_message_code INT            DEFAULT 0                     NOT NULL;      -- メッセージコード

-- システム標準テーブル
ALTER TABLE blog_entry MODIFY be_html     TEXT NOT NULL;
ALTER TABLE blog_entry MODIFY be_html_ext TEXT NOT NULL;
