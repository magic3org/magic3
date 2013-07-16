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
-- * @version    SVN: $Id: 2012111401_to_2012112601.sql 5532 2013-01-09 13:13:02Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
-- 多言語対応文字列マスター
ALTER TABLE _language_string DROP PRIMARY KEY;-- ALTER TABLE _language_string DROP CONSTRAINT _language_string_pkey; -- ユニーク制約削除
ALTER TABLE _language_string ADD ls_type         SMALLINT       DEFAULT 0                     NOT NULL;      -- 文字列(0=メッセージ,1=共通用語,10=Joomla!用)
ALTER TABLE _language_string ADD PRIMARY KEY (ls_type,    ls_id,        ls_language_id); -- ユニーク制約再設定

INSERT INTO _language_string
(ls_type, ls_id,                     ls_language_id, ls_value,                             ls_name) VALUES
(1,       'word_account',         'ja',           'ID(Eメール)',                 'アカウント');

-- *** システム標準テーブル ***

