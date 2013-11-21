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
-- * @version    SVN: $Id: 2010021501_to_2010021601.sql 2886 2010-03-02 07:51:53Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システム標準テーブル ***
-- ページ情報マスター
ALTER TABLE _page_info DROP INDEX pn_id;                                                                        -- ユニーク制約削除
ALTER TABLE _page_info ADD pn_language_id       VARCHAR(2)     DEFAULT ''                    NOT NULL;          -- 言語ID(空=デフォルト)
ALTER TABLE _page_info ADD UNIQUE (pn_id,       pn_sub_id,     pn_language_id,               pn_history_index); -- ユニーク制約再設定
ALTER TABLE _page_info ADD pn_name              VARCHAR(40)    DEFAULT ''                    NOT NULL;          -- ページ名

-- ウィジェット情報
DELETE FROM _widgets WHERE wd_id = 'pdf_list';
INSERT INTO _widgets
(wd_id,      wd_name,   wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,                  wd_add_script_lib, wd_add_script_lib_a, wd_has_admin, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('pdf_list', 'PDF名簿', '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'PDF出力も可能な簡易名簿です。', '',                'jquery.tablednd',   true,  true,              true, 3,          1, now(),         now());
