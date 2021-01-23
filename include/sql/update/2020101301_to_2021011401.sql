-- *
-- * バージョンアップ用スクリプト
-- *
-- * PHP versions 5
-- *
-- * LICENSE: This source file is licensed under the terms of the GNU General Public License.
-- *
-- * @package    Magic3 Framework
-- * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
-- * @copyright  Copyright 2006-2021 Magic3 Project.
-- * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
-- * @version    SVN: $Id$
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
UPDATE _system_config SET sc_value = '1.12' WHERE sc_id = 'jquery_version'; -- jQueryバージョン(PC用)
UPDATE _system_config SET sc_value = '1.12' WHERE sc_id = 'admin_jquery_version'; -- 管理画面用jQueryバージョン

-- *** システム標準テーブル ***

