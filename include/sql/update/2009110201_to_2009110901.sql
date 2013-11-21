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
-- * @version    SVN: $Id: 2009110201_to_2009110901.sql 2562 2009-11-18 03:58:30Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
-- システム設定マスター
INSERT INTO _system_config 
(sc_id,                        sc_value,         sc_name) VALUES
('default_connect_server_url', '',               'デフォルトの連携サーバURL'),
('config_window_style',        'toolbar=no,menubar=no,location=no,status=no,scrollbars=yes,resizable=yes,width=1050,height=900',               '設定画面の表示属性');

-- ページ定義マスター
ALTER TABLE _page_def ADD pd_menu_id           VARCHAR(20)    DEFAULT ''                    NOT NULL;      -- メニューID

-- メニューIDマスター
INSERT INTO _menu_id
(mn_id,       mn_name,         mn_description, mn_sort_order) VALUES
('sub_menu1', 'サブメニュー1', '',             1),
('sub_menu2', 'サブメニュー2', '',             2),
('sub_menu3', 'サブメニュー3', '',             3);

-- ページIDマスター
INSERT INTO _page_id 
(pg_id,          pg_type, pg_default_sub_id, pg_path,       pg_name,                        pg_description,                       pg_priority, pg_active, pg_mobile, pg_editable) VALUES
('user1',        1,       '',                '',            'ユーザ定義1',                  'ユーザ任意定義用',                   20,          true,      false,     true),
('user2',        1,       '',                '',            'ユーザ定義2',                  'ユーザ任意定義用',                   21,          true,      false,     true),
('user3',        1,       '',                '',            'ユーザ定義3',                  'ユーザ任意定義用',                   22,          true,      false,     true);

-- *** システム標準テーブル ***
