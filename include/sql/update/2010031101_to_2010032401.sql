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
-- * @version    SVN: $Id: 2010031101_to_2010032401.sql 3000 2010-03-31 07:33:10Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- ユーザ作成コンテンツ項目マスター
ALTER TABLE user_content_item ADD ui_search_target     BOOLEAN        DEFAULT true                  NOT NULL;      -- 検索対象かどうか
ALTER TABLE user_content_item ADD ui_key               VARCHAR(20)    DEFAULT ''                    NOT NULL;      -- 外部からの参照用キー

-- ウィジェット情報
DELETE FROM _widgets WHERE wd_id = 'user_content';
INSERT INTO _widgets
(wd_id,          wd_name,                wd_type, wd_version, wd_author,                       wd_copyright,                    wd_license, wd_official_level, wd_description,         wd_add_script_lib, wd_add_script_lib_a, wd_has_admin, wd_enable_operation, wd_initialized, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('user_content', 'ユーザ作成コンテンツ', 'user',  '1.0.0',    '株式会社 毎日メディアサービス', '株式会社 毎日メディアサービス', 'GPL',      10,                'ユーザが管理可能なコンテンツを表示', 'jquery-ui,jquery-ui-plus', '', true, true, true,           2, 2, now(), now());
