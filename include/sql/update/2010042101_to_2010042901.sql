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
-- * @version    SVN: $Id: 2010042101_to_2010042901.sql 3102 2010-05-07 06:05:36Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
-- システム設定マスター
INSERT INTO _system_config 
(sc_id,                         sc_value,         sc_name) VALUES
('smartphone_default_template', 's/default',      'スマートフォン画面用デフォルトテンプレート'),
('site_smartphone_in_public',   '1',              'スマートフォン用サイト公開');

-- 多言語対応文字列マスター
INSERT INTO _language_string
(ls_id,                     ls_language_id, ls_value,                             ls_name) VALUES
('msg_site_in_maintenance', 'ja',           'ただいまサイトのメンテナンス中です', 'メンテナンス中メッセージ');

-- ページIDマスター
ALTER TABLE _page_id ADD pg_device_type                INT            DEFAULT 0                     NOT NULL;      -- 端末タイプ(0=PC、1=携帯、2=スマートフォン)
INSERT INTO _page_id 
(pg_id,      pg_type, pg_default_sub_id, pg_path,       pg_name,                            pg_description,                       pg_priority, pg_active, pg_device_type, pg_mobile, pg_editable) VALUES
('s_index',  0,       'content',         's/index',     'スマートフォン用アクセスポイント', 'スマートフォン用アクセスポイント',   1,           true,      2,              false,     true);

-- ウィジェット情報マスター
ALTER TABLE _widgets ADD wd_device_type                INT            DEFAULT 0                     NOT NULL;      -- 端末タイプ(0=PC、1=携帯、2=スマートフォン)

-- テンプレート情報マスター
ALTER TABLE _templates ADD tm_device_type              INT            DEFAULT 0                     NOT NULL;      -- 端末タイプ(0=PC、1=携帯、2=スマートフォン)

-- 検索キーワードトラン
ALTER TABLE _search_word ADD sw_device_type            INT            DEFAULT 0                     NOT NULL;      -- 端末タイプ(0=PC、1=携帯、2=スマートフォン)

-- ページ情報マスター
INSERT INTO _page_info
(pn_id,       pn_sub_id,   pn_content_type, pn_use_ssl) VALUES
('s_index',   'content',   'content',       false);

-- ウィジェット情報マスター
DELETE FROM _widgets WHERE wd_id = 'admin_main';
INSERT INTO _widgets
(wd_id,         wd_name, wd_type, wd_params, wd_author, wd_copyright, wd_license, wd_official_level, wd_add_script_lib, wd_read_scripts, wd_read_css, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_install_dt, wd_create_dt) VALUES
('admin_main',  '管理用画面',      'ADBD',  '',     'Naoki Hirata', 'Magic3.org', 'GPL', 10,     'pagedef=jquery-ui-plus;pagedef_mobile=jquery-ui-plus;pagedef_smartphone=jquery-ui-plus;menudef=jquery.simpletree;', true, true,        false,        false,       false,        true,          false, true, 0,   now(),now());
