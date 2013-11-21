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
-- * @version    SVN: $Id: 2010090101_to_2010091401.sql 3593 2010-09-15 09:19:20Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
DELETE FROM _widgets WHERE wd_id = 'custom_search_box';
INSERT INTO _widgets
(wd_id,               wd_name,            wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,                                     wd_add_script_lib, wd_add_script_lib_a, wd_has_admin, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('custom_search_box', 'カスタム検索連携', '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'カスタム検索に検索結果を表示する検索ウィジェット', 'jquery', '',     true,         true,true,               1, -1, now(),    now());

-- ページ情報マスター
ALTER TABLE _page_info ADD pn_user_limited      BOOLEAN        DEFAULT false                 NOT NULL;      -- アクセス可能ユーザを制限

-- *** システム標準テーブル ***
