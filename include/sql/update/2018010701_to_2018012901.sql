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
DELETE FROM _templates WHERE tm_id = 'art41_sample1';
DELETE FROM _templates WHERE tm_id = 'art41_sample2';
DELETE FROM _templates WHERE tm_id = 'art42_sample4';

DELETE FROM _templates WHERE tm_id = 'themler_old';
INSERT INTO _templates
(tm_id,                tm_name,             tm_type, tm_generator, tm_version) VALUES
('themler_old',        'themler_old',       2,       'themler',    '1.0.68');

DELETE FROM _templates WHERE tm_id = '_layout';
INSERT INTO _templates
(tm_id,                           tm_name,                         tm_type, tm_device_type, tm_mobile, tm_use_bootstrap, tm_available, tm_generator, tm_version, tm_info_url) VALUES
('_layout',                       '_layout',                       99,       0,              false,     false,            false,        '',           '',         '');

-- *** システム標準テーブル ***
