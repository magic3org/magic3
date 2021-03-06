-- *
-- * バージョンアップ用スクリプト
-- *
-- * PHP versions 5
-- *
-- * LICENSE: This source file is licensed under the terms of the GNU General Public License.
-- *
-- * @package    Magic3 Framework
-- * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
-- * @copyright  Copyright 2006-2018 Magic3 Project.
-- * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
-- * @version    SVN: $Id$
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
-- システム設定マスター
INSERT INTO _system_config 
(sc_id,                     sc_value,         sc_name) VALUES
('site_menu_hier', '1',              'サイトのメニューを階層化するかどうか');

-- テンプレート情報
DELETE FROM _templates WHERE tm_id = 'bootstrap4_custom';
INSERT INTO _templates
(tm_id,                           tm_name,                         tm_type, tm_use_bootstrap, tm_create_dt) VALUES
('bootstrap4_custom',             'bootstrap4_custom',             11,      true,             now());

-- ページ定義マスター
DELETE FROM _page_def WHERE pd_id = 'admin_index';
INSERT INTO _page_def
(pd_id,         pd_sub_id,      pd_position_id, pd_index, pd_widget_id,          pd_config_id, pd_visible, pd_editable, pd_title_visible, pd_visible_condition) VALUES
('admin_index', '',             'top',          1,        'admin_menu',          0,            true,       false,       false,            ''),
('admin_index', 'front',        'top',          2,        'admin/message',       0,            true,       false,       false,            ''),
('admin_index', 'front',        'main',         1,        'admin_main',          0,            true,       false,       false,            ''),
('admin_index', 'front',        'main',         2,        'admin/analytics',     0,            true,       true,        false,            ''),
('admin_index', 'front',        'main',         3,        'admin/opelog',        0,            true,       true,        false,            ''),
('admin_index', 'front',        'left',         1,        'admin/loginuser',     0,            true,       true,        true,             ''),
('admin_index', 'content',      'main',         1,        'admin_main',          0,            true,       false,       false,            ''),
('admin_index', 'content',      'left',         1,        'admin/remotecontent', 0,            true,       true,        true,             'task=dummy'),
('admin_index', 'content',      'right',        1,        'admin/remotecontent', 0,            true,       true,        true,             'task=help');

-- *** システム標準テーブル ***
