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
-- * @version    SVN: $Id: 2012102501_to_2012110501.sql 5446 2012-12-08 16:08:51Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
-- 定型メールフォーム
ALTER TABLE _mail_form DROP INDEX mf_id;-- ALTER TABLE _mail_form DROP CONSTRAINT _mail_form_mf_id_mf_history_index_key; -- ユニーク制約削除
ALTER TABLE _mail_form ADD UNIQUE (mf_id,       mf_language_id,               mf_history_index); -- ユニーク制約再設定

-- *** システム標準テーブル ***
