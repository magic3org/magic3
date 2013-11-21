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
-- * @version    SVN: $Id: 2009012201_to_2009012301.sql 1501 2009-02-04 06:26:45Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
-- ウィジェット情報マスター
DELETE FROM _widgets WHERE wd_id = 'banner2';
INSERT INTO _widgets
(wd_id,     wd_name,              wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,                                                                     wd_read_scripts, wd_read_css, wd_add_script_lib, wd_add_script_lib_a, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_install_dt, wd_create_dt) VALUES
('banner2', 'バナー表示2', '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'バナー画像をグループ化して、グループごとに表示できるバナー管理ウィジェットです。', false,           false,       '',                '',                  true, true,         true,                true,           now(),         now());

-- バナー表示定義
ALTER TABLE bn_def  ADD bd_item_html TEXT                                         NOT NULL;      -- バナー項目表示テンプレート
ALTER TABLE bn_def  ADD bd_css_id    VARCHAR(20)    DEFAULT ''                    NOT NULL;      -- CSS用ID
ALTER TABLE bn_def  ADD bd_css       TEXT                                         NOT NULL;      -- CSS
-- バナー項目
ALTER TABLE bn_item ADD bi_type      INT            DEFAULT 0                     NOT NULL;      -- 項目タイプ(0=画像、1=Flash)

-- *** システム標準テーブル ***
