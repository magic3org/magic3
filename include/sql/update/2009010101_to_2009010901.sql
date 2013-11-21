-- *
-- * バージョンアップ用スクリプト
-- *
-- * PHP versions 5
-- *
-- * LICENSE: This source file is licensed under the terms of the GNU General Public License.
-- *
-- * @package    Magic3 Framework
-- * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
-- * @copyright  Copyright 2006-2008 Magic3 Project.
-- * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
-- * @version    SVN: $Id: 2009010101_to_2009010901.sql 1433 2009-01-13 03:54:53Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
-- ウィジェット情報マスター
DELETE FROM _widgets WHERE wd_id = 'top_content_box';
INSERT INTO _widgets
(wd_id,             wd_name,                    wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_read_scripts, wd_read_css, wd_add_script_lib, wd_add_script_lib_a,                         wd_has_admin, wd_use_instance_def, wd_initialized, wd_install_dt, wd_create_dt) VALUES
('top_content_box', 'トップアクセスコンテンツリスト', '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                '閲覧数の多い順からコンテンツリストを表示します。', false, false, '', '', true,  false,              true,           now(),         now());
DELETE FROM _widgets WHERE wd_id = 'update_content_box';
INSERT INTO _widgets
(wd_id,             wd_name,                    wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_read_scripts, wd_read_css, wd_add_script_lib, wd_add_script_lib_a,                         wd_has_admin, wd_use_instance_def, wd_initialized, wd_install_dt, wd_create_dt) VALUES
('update_content_box', '更新コンテンツリスト', '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                '最新の更新からコンテンツリストを表示します。', false, false, '', '', true,  false,              true,           now(),         now());

-- ページ情報マスター
DELETE FROM _page_info;
INSERT INTO _page_info
(pn_id,     pn_sub_id, pn_content_type) VALUES
('index',   'content', 'content'),
('index',   'wiki',    'wiki'),
('m_index', 'content', 'content'),
('m_index', 'wiki',    'wiki');

-- *** システム標準テーブル ***
