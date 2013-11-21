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
-- * @version    SVN: $Id: 2009032701_to_2009040201.sql 1730 2009-04-10 07:23:09Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
-- テンプレート情報マスター
ALTER TABLE _templates ADD tm_type    INT            DEFAULT 0                    NOT NULL;      -- テンプレート種別(0=デフォルトテンプレート(Joomla!v1.0)、1=Joomla!v1.5)

-- インナーウィジェット情報マスター
DELETE FROM _iwidgets WHERE iw_widget_id = 'ec_main' AND iw_id = 'productrate';
INSERT INTO _iwidgets
(iw_widget_id, iw_id,       iw_name,    iw_type,    iw_author,      iw_copyright, iw_license, iw_official_level, iw_install_dt, iw_create_dt) VALUES
('ec_main', 'productrate', '商品別規定', 'DELIVERY', 'Naoki Hirata', 'Magic3.org', 'GPL',      10,                now(),         now());
DELETE FROM _iwidgets WHERE iw_widget_id = 'ec_main' AND iw_id = 'weightrate';
INSERT INTO _iwidgets
(iw_widget_id, iw_id,       iw_name,    iw_type,    iw_author,      iw_copyright, iw_license, iw_official_level, iw_install_dt, iw_create_dt) VALUES
('ec_main', 'weightrate', '送付先+重量基準', 'DELIVERY', 'Naoki Hirata', 'Magic3.org', 'GPL',      10,                now(),         now());

-- *** システム標準テーブル ***
