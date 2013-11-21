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
-- * @version    SVN: $Id: 2009012301_to_2009021001.sql 1513 2009-02-16 03:20:50Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
-- ウィジェット情報マスター
DELETE FROM _widgets WHERE wd_id = 'image2';
INSERT INTO _widgets
(wd_id,   wd_name, wd_version, wd_params, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_read_scripts, wd_read_css, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_install_dt, wd_create_dt) VALUES
('image2', '画像2',  '1.0.0',    '',        'Naoki Hirata', 'Magic3.org', 'GPL',      10, '様々な画像を単一で表示。',               false,           false,       true,         true,        true,         false,                                true,                true,              0,               now(),         now());

UPDATE _widgets SET wd_name = '画像(廃止予定)', wd_status = -1 WHERE wd_id = 'image' AND wd_deleted = false;
UPDATE _widgets SET wd_name = 'バナー表示(廃止予定)', wd_status = -1 WHERE wd_id = 'banner' AND wd_deleted = false;

-- *** システム標準テーブル ***
