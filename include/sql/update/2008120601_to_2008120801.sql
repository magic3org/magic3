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
-- * @version    SVN: $Id: 2008120601_to_2008120801.sql 1380 2008-12-23 02:40:35Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
-- ウィジェット情報マスター
DELETE FROM _widgets WHERE wd_id = 'admin_main';
INSERT INTO _widgets
(wd_id,         wd_name, wd_type, wd_params, wd_author, wd_copyright, wd_license, wd_official_level, wd_add_script_lib, wd_read_scripts, wd_read_css, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_install_dt, wd_create_dt) VALUES
('admin_main',  '管理用画面',      'ADBD',  '',     'Naoki Hirata', 'Magic3.org', 'GPL', 10,     'pagedef=jquery-ui-plus;pagedef_mobile=jquery-ui-plus;menudef=jquery.simpletree;', true,            true,        false,        false,       false,        true,          false, true, 0,   now(),now());
DELETE FROM _widgets WHERE wd_id = 'm/content_search_box';
INSERT INTO _widgets
(wd_id,                wd_name,                       wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,                         wd_mobile, wd_has_admin, wd_initialized, wd_install_dt, wd_create_dt) VALUES
('m/content_search_box', 'コンテンツ - 検索',         '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'コンテンツを検索するためのボックス。', true,      false,               true,           now(),         now());
DELETE FROM _widgets WHERE wd_id = 'default_menu';
INSERT INTO _widgets
(wd_id,                wd_name,                       wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description, wd_add_script_lib, wd_add_script_lib_a,                         wd_has_admin, wd_use_instance_def, wd_initialized, wd_install_dt, wd_create_dt) VALUES
('default_menu', 'デフォルトメニュー',         '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                '複数のメニューが作成できる1階層の新型デフォルトメニューです。サブメニューも管理できる共通のメニュー定義を使用します。', '', '', true,  true,              true,           now(),         now());

-- メニューIDマスター
DROP TABLE IF EXISTS _menu_id;
CREATE TABLE _menu_id (
    mn_id                VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- メニューID
    mn_name              VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- メニュー名称
    mn_description       VARCHAR(100)   DEFAULT ''                    NOT NULL,      -- 説明
    mn_sort_order        INT            DEFAULT 0                     NOT NULL,      -- ソート順
    PRIMARY KEY  (mn_id)
) TYPE=innodb;
INSERT INTO _menu_id
(mn_id,       mn_name,          mn_description, mn_sort_order) VALUES
('main_menu', 'メインメニュー', '',             0);

-- メニュー定義マスター
DROP TABLE IF EXISTS _menu_def;
CREATE TABLE _menu_def (
    md_id                INT            DEFAULT 0                     NOT NULL,      -- 項目ID
    md_parent_id         INT            DEFAULT 0                     NOT NULL,      -- 親項目ID(親がないときは0)
    md_index             INT            DEFAULT 0                     NOT NULL,      -- 表示順(0～)、md_parent_id=0のときは親間の表示順
    md_menu_id           VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- メニュー種別ID
    md_name              VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- 名前
    md_description       VARCHAR(100)   DEFAULT ''                    NOT NULL,      -- 説明
    md_type              INT            DEFAULT 0                     NOT NULL,      -- メニュー項目タイプ(0=リンク、1=フォルダ、2=テキスト、3=セパレータ)
    md_link_type         INT            DEFAULT 0                     NOT NULL,      -- リンクタイプ(0=同ウィンドウ、1=別ウィンドウ)
    md_link_url          TEXT                                         NOT NULL,      -- リンク先
    md_visible           BOOLEAN        DEFAULT true                  NOT NULL,      -- 表示するかどうか

    md_update_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード更新者
    md_update_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード更新日時
    PRIMARY KEY          (md_id)
) TYPE=innodb;

-- *** システム標準テーブル ***
