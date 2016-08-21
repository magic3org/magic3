-- *
-- * バージョンアップ用スクリプト
-- *
-- * PHP versions 5
-- *
-- * LICENSE: This source file is licensed under the terms of the GNU General Public License.
-- *
-- * @package    Magic3 Framework
-- * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
-- * @copyright  Copyright 2006-2016 Magic3 Project.
-- * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
-- * @version    SVN: $Id$
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
-- ウィジェット情報マスター
ALTER TABLE _widgets ADD wd_enable_content  BOOLEAN        DEFAULT false                 NOT NULL;      -- コンテンツ組み込み可能かどうか

-- ページIDマスター
ALTER TABLE _page_id ADD pg_frontend           BOOLEAN        DEFAULT false                  NOT NULL;      -- フロント画面用かどうか(ページID種別がアクセスポイント時)、pg_analyticsは廃止
ALTER TABLE _page_id ADD pg_system_type        VARCHAR(20)    DEFAULT ''                    NOT NULL;      -- システム任意目的用タイプ
DELETE FROM _page_id WHERE pg_type = 0;
INSERT INTO _page_id 
(pg_id,          pg_type, pg_default_sub_id, pg_path,       pg_name,                            pg_description,                       pg_priority, pg_device_type, pg_active, pg_visible, pg_mobile, pg_editable, pg_admin_menu, pg_frontend) VALUES
('index',        0,       'content',         'index',       'PC用アクセスポイント',             'PC用アクセスポイント',               0,           0,              true,      true,       false,     true,        true,          true),
('s_index',      0,       'content',           's/index',     'スマートフォン用アクセスポイント', 'スマートフォン用アクセスポイント',   1,           2,              false,     true,       false,     true,        false,         true),
('m_index',      0,       'content',           'm/index',     '携帯用アクセスポイント',           '携帯用アクセスポイント',             2,           1,              false,     true,       true,      true,        false,         true),
('admin_index',  0,       'content',         'admin/index', '管理用アクセスポイント',           '管理用アクセスポイント',             3,           0,              true,      true,       false,     false,       false,         false),
('connector',    0,       'content',         'connector',   'サーバ接続用アクセスポイント',     'サーバ接続用アクセスポイント',       4,           0,              true,      true,       false,     false,       false,         false);
UPDATE _page_id SET pg_system_type = 'activate' WHERE pg_id = 'deploy' AND pg_type = 1;

-- *** システム標準テーブル ***

