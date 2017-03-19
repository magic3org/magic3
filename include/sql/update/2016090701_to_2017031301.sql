-- *
-- * バージョンアップ用スクリプト
-- *
-- * PHP versions 5
-- *
-- * LICENSE: This source file is licensed under the terms of the GNU General Public License.
-- *
-- * @package    Magic3 Framework
-- * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
-- * @copyright  Copyright 2006-2017 Magic3 Project.
-- * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
-- * @version    SVN: $Id$
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
-- テンプレート情報
ALTER TABLE _templates ADD tm_info_url               TEXT                                         NOT NULL;      -- テンプレート情報URL

DELETE FROM _templates WHERE tm_id = 'bs_honoka3';
INSERT INTO _templates
(tm_id,                           tm_name,                  tm_type, tm_device_type, tm_mobile, tm_use_bootstrap, tm_available, tm_clean_type, tm_info_url, tm_create_dt) VALUES
('bs_honoka3',                    'bs_honoka3',             10,       0,              false,     true,             true,        0,             'http://honokak.osaka/',          now());
DELETE FROM _templates WHERE tm_id = 'bs_single_gray';
INSERT INTO _templates
(tm_id,                           tm_name,                  tm_type, tm_device_type, tm_mobile, tm_use_bootstrap, tm_available, tm_clean_type, tm_info_url, tm_create_dt) VALUES
('bs_single_gray',                'bs_single_gray',         10,       0,              false,     true,             true,        0,             'https://startbootstrap.com/template-overviews/grayscale/',          now());

-- *** システム標準テーブル ***

