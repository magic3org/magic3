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
-- * @version    SVN: $Id: 2011060601_to_2011061601.sql 4210 2011-06-16 05:41:44Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
-- テンプレート情報
DELETE FROM _templates WHERE tm_id = 's/default_simple';
DELETE FROM _templates WHERE tm_id = 's/default_jquery';
INSERT INTO _templates
(tm_id,                           tm_name,                         tm_type, tm_device_type, tm_mobile, tm_available, tm_clean_type, tm_create_dt) VALUES
('s/default_simple',              's/default_simple',              1,       2,              false,     true,         0,             now()),
('s/default_jquery',              's/default_jquery',              1,       2,              false,     true,         0,             now());
