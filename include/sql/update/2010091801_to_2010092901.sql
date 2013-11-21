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
-- * @version    SVN: $Id: 2010091801_to_2010092901.sql 3681 2010-10-08 23:08:22Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
-- ウィジェット情報マスター
ALTER TABLE _widgets ADD wd_release_dt        TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL;      -- リリース日時

-- システム設定
UPDATE _system_config SET sc_value = '_system' WHERE sc_id = 'msg_template';
INSERT INTO _system_config 
(sc_id,                      sc_value,                  sc_name) VALUES
('use_content_maintenance',  '0',                       'メンテナンス画面用コンテンツの取得');

-- ページ定義マスター
UPDATE _page_def SET pd_title_visible = false WHERE pd_widget_id = 'admin_main';
UPDATE _page_def SET pd_title_visible = false WHERE pd_widget_id = 'admin_menu2';

-- テンプレート情報
DELETE FROM _templates WHERE tm_id = '_system';
INSERT INTO _templates
(tm_id,                      tm_name,                    tm_type, tm_device_type, tm_mobile, tm_available, tm_clean_type, tm_create_dt) VALUES
('_system',                  '_system',                  1,       0,              false,     false,        0,             now());

-- *** システム標準テーブル ***
-- 汎用コンテンツマスター
ALTER TABLE content ADD    cn_search_target           BOOLEAN        DEFAULT true                  NOT NULL;      -- 検索対象かどうか
