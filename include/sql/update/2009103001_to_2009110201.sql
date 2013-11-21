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
-- * @version    SVN: $Id: 2009103001_to_2009110201.sql 2511 2009-11-03 05:14:14Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
-- ウィジェット情報マスター
ALTER TABLE _widgets ADD wd_active            BOOLEAN        DEFAULT true                  NOT NULL;      -- 一般ユーザが実行可能かどうか

-- ウィジェット情報(新規追加)
DELETE FROM _widgets WHERE wd_id = 'gotop';
INSERT INTO _widgets
(wd_id,   wd_name,        wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,     wd_add_script_lib, wd_add_script_lib_a, wd_read_scripts, wd_read_css, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('gotop', '上へ参ります', '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                '画面トップへ移動', 'jquery',          '',                  false,           false,       true,         true,        false,        false,               false,               true,           1,             -1, now(), now());

-- *** システム標準テーブル ***
