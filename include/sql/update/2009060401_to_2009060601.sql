-- *
-- * バージョンアップ用スクリプト
-- *
-- * PHP versions 5
-- *
-- * LICENSE: This source file is licensed under the terms of the GNU General Public License.
-- *
-- * @package    Magic3 Framework
-- * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
-- * @copyright  Copyright 2006-2009 Magic3 Project.
-- * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
-- * @version    SVN: $Id: 2009060401_to_2009060601.sql 1973 2009-06-06 10:45:06Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
-- ウィジェット情報マスター
ALTER TABLE _widgets ADD wd_joomla_class          VARCHAR(50)    DEFAULT ''                    NOT NULL;      -- Joomla!テンプレート用のクラス名

UPDATE _widgets SET wd_joomla_class = '_menu' WHERE wd_id = 'default_mainmenu' AND wd_deleted = false;
UPDATE _widgets SET wd_joomla_class = '_menu' WHERE wd_id = 'default_menu' AND wd_deleted = false;
UPDATE _widgets SET wd_joomla_class = '_menu' WHERE wd_id = 'ec_menu' AND wd_deleted = false;

-- *** システム標準テーブル ***
