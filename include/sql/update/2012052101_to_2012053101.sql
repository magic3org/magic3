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
-- * @version    SVN: $Id: 2012052101_to_2012053101.sql 4932 2012-06-05 09:04:28Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
-- メニュー定義マスター
ALTER TABLE _menu_def MODIFY md_name     TEXT NOT NULL;       -- 名前

-- 汎用コンテンツマスター
ALTER TABLE content MODIFY cn_language_id       VARCHAR(5)     DEFAULT ''                    NOT NULL;      -- 言語ID
