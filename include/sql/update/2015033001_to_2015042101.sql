-- *
-- * バージョンアップ用スクリプト
-- *
-- * PHP versions 5
-- *
-- * LICENSE: This source file is licensed under the terms of the GNU General Public License.
-- *
-- * @package    Magic3 Framework
-- * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
-- * @copyright  Copyright 2006-2015 Magic3 Project.
-- * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
-- * @version    SVN: $Id$
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
-- テンプレート情報

-- *** システム標準テーブル ***
-- イベント記事マスター
ALTER TABLE event_entry MODIFY ee_summary TEXT NOT NULL;      -- 概要
ALTER TABLE event_entry MODIFY ee_place TEXT NOT NULL;        -- 場所
ALTER TABLE event_entry MODIFY ee_contact TEXT NOT NULL;      -- 連絡先(Eメール,電話番号)
