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
-- * @version    SVN: $Id: 2009062601_to_2009070601.sql 2053 2009-07-07 07:36:05Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
-- システム設定マスター
INSERT INTO _system_config 
(sc_id,               sc_value,         sc_name) VALUES
('use_ssl',           '0',              'SSL通信');

-- ページ情報マスター
INSERT INTO _page_info
(pn_id,     pn_sub_id, pn_content_type) VALUES
('index',   'shop',    'product'),
('m_index', 'shop',    'product');

-- ウィジェット情報マスター
DELETE FROM _widgets WHERE wd_id = 'ec_disp';
INSERT INTO _widgets
(wd_id,     wd_name,                wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,                                      wd_has_admin, wd_initialized, wd_install_dt, wd_create_dt) VALUES
('ec_disp', 'Eコマース - 商品表示', '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'Eコマースの商品表示機能。商品の説明や一覧を表示。', true,         true,   now(), now());
DELETE FROM _widgets WHERE wd_id = 'ec_main';
INSERT INTO _widgets
(wd_id,     wd_name,              wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,                                                        wd_has_admin, wd_initialized, wd_install_dt, wd_create_dt) VALUES
('ec_main', 'Eコマース - メイン', '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'Eコマースのメインプログラム。会員登録処理や商品購入処理などを行う。', true,         true,   now(), now());

-- *** システム標準テーブル ***
