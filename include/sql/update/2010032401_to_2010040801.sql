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
-- * @version    SVN: $Id: 2010032401_to_2010040801.sql 3022 2010-04-12 01:31:55Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- ユーザコンテンツ表示タブマスター
ALTER TABLE user_content_tab ADD ub_group_id     INT            DEFAULT 0                     NOT NULL;      -- 所属グループID

-- ユーザ作成コンテンツルームマスター
ALTER TABLE user_content_room ADD ur_group_id     INT            DEFAULT 0                     NOT NULL;      -- 所属グループID

-- ユーザ作成コンテンツカテゴリマスター
DROP TABLE IF EXISTS user_content_category;
CREATE TABLE user_content_category (
    ua_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    ua_id                VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- カテゴリID
    ua_item_id           VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- カテゴリ項目ID(空=カテゴリ種別、空以外=カテゴリ項目)
    ua_language_id       VARCHAR(2)     DEFAULT ''                    NOT NULL,      -- 言語ID
    ua_history_index     INT            DEFAULT 0                     NOT NULL,      -- 履歴管理用インデックスNo(0～)

    ua_name              VARCHAR(30)    DEFAULT ''                    NOT NULL,      -- カテゴリ名称
    ua_index             INT            DEFAULT 0                     NOT NULL,      -- カテゴリ項目の表示順(カテゴリ項目IDが空のときはカテゴリの表示順)

    ua_create_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード作成者
    ua_create_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード作成日時
    ua_update_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード更新者
    ua_update_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード更新日時
    ua_deleted           BOOLEAN        DEFAULT false                 NOT NULL,      -- レコード削除状態
    PRIMARY KEY          (ua_serial),
    UNIQUE               (ua_id,        ua_item_id,  ua_language_id,  ua_history_index)
) TYPE=innodb;

-- ユーザ作成コンテンツカテゴリとルームの対応付けマスター
DROP TABLE IF EXISTS user_content_room_category;
CREATE TABLE user_content_room_category (
    um_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    um_room_id           VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- ルームID
    um_category_id       VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- カテゴリID
    um_category_item_id  VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- カテゴリ項目ID
    PRIMARY KEY          (um_serial),
    UNIQUE               (um_room_id,   um_category_id, um_category_item_id)
) TYPE=innodb;

-- ユーザ作成コンテンツ項目マスター
INSERT INTO user_content_item
(ui_id,          ui_name,      ui_description,                   ui_type,  ui_key,        ui_create_dt) VALUES
('DEFAULT_LIST', '検索一覧用', '検索結果の一覧に表示するデータ', 0,        'SEARCH_LIST', now());

-- ウィジェット情報
DELETE FROM _widgets WHERE wd_id = 'user_content';
INSERT INTO _widgets
(wd_id,          wd_name,                wd_type, wd_version, wd_author,                       wd_copyright,                    wd_license, wd_official_level, wd_description,         wd_add_script_lib, wd_add_script_lib_a, wd_has_admin, wd_enable_operation, wd_initialized, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('user_content', 'ユーザ作成コンテンツ', 'user',  '1.1.0',    '株式会社 毎日メディアサービス', '株式会社 毎日メディアサービス', 'GPL',      10,                'ユーザが管理可能なコンテンツを表示', 'jquery-ui,jquery-ui-plus', 'jquery.tablednd', true, true, true,           2, 2, now(), now());
