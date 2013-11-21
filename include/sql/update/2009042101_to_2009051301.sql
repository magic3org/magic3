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
-- * @version    SVN: $Id: 2009042101_to_2009051301.sql 1898 2009-05-17 03:34:13Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
-- ウィジェット情報
DELETE FROM _widgets WHERE wd_id = 'm/custom_header';
INSERT INTO _widgets
(wd_id,      wd_name,          wd_version, wd_params, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_mobile, wd_has_admin, wd_initialized, wd_install_dt, wd_create_dt) VALUES
('m/custom_header', 'カスタムヘッダ', '1.0.0',    '',        'Naoki Hirata', 'Magic3.org', 'GPL',      10, 'ヘッダ部を表示する。',               true,      true,         true,              now(),         now());
DELETE FROM _widgets WHERE wd_id = 'm/news';
INSERT INTO _widgets
(wd_id,      wd_name,          wd_version, wd_params, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_mobile, wd_has_admin, wd_initialized, wd_install_dt, wd_create_dt) VALUES
('m/news', 'お知らせ', '1.0.0',    '',        'Naoki Hirata', 'Magic3.org', 'GPL',      10, 'お知らせを表示する。',               true,      true,         true,              now(),         now());

-- *** システム標準テーブル ***
