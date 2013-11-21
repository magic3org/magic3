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
-- * @version    SVN: $Id: 2008112601_to_2008120601.sql 1325 2008-12-07 05:43:54Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
-- ウィジェット情報マスター
DELETE FROM _widgets WHERE wd_id = 'content_search_box';
DELETE FROM _widgets WHERE wd_id = 'nav_menu_css';
INSERT INTO _widgets
(wd_id,                wd_name,                       wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,                                             wd_read_scripts, wd_read_css, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_install_dt, wd_create_dt) VALUES
('content_search_box', 'コンテンツ - 検索',           '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'コンテンツを検索するためのボックス。',                              false,           false,       true,         true,        false,        false,               false,               true,           now(),    now()),
('nav_menu_css',       'ナビゲーションメニュー(CSS)', '1.0.0',    '',             'Magic3.org', 'GPL',      10,                'ページのヘッダ下や、フッタ部に表示するメニュー。SPANタグにて表示。',    false,           false,       true,         true,        true,         false,               false,               true,           now(), now());

-- メール内容
DELETE FROM _mail_form WHERE mf_id = 'test';
INSERT INTO _mail_form (mf_id,  mf_language_id, mf_subject,     mf_content,                                       mf_create_dt) 
VALUES                 ('test', 'ja',           'テストメール', 'このメールはテスト用のメールです。\n\n[#BODY#]', now());

-- ページ情報マスター
DELETE FROM _page_info;
INSERT INTO _page_info
(pn_id,     pn_sub_id, pn_content_type) VALUES
('index',   'content', 'content'),
('index',   'wiki',    'wiki'),
('m_index', 'content', 'content');

-- *** システム標準テーブル ***
