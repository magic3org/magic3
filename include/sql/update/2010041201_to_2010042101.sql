-- *
-- * バージョンアップ用スクリプト
-- *
-- * PHP versions 5
-- *
-- * LICENSE: This source file is licensed under the terms of the GNU General Public License.
-- *
-- * @package    Magic3 Framework
-- * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
-- * @copyright  Copyright 2006-2010 Magic3 Project.
-- * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
-- * @version    SVN: $Id: 2010041201_to_2010042101.sql 3088 2010-05-03 02:18:49Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
-- ウィジェット情報
DELETE FROM _widgets WHERE wd_id = 'contactus_freelayout2';
INSERT INTO _widgets
(wd_id,              wd_name,                          wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,                             wd_read_scripts, wd_read_css,wd_add_script_lib, wd_add_script_lib_a, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('contactus_freelayout2', 'フリーレイアウトお問い合わせ2', '1.0.0',    '株式会社 毎日メディアサービス', '株式会社 毎日メディアサービス', 'GPL',      10,                'フリーレイアウトでカスタマイズ可能なお問い合わせメール送信機能。「フリーレイアウトお問い合わせ」のバージョンアップ版。', false,           false,     'jquery.formtips,jquery.format', 'jquery.tablednd', true,         false,               true,                true, 0,          0, now(),         now());
