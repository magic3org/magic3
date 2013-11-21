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
-- * @version    SVN: $Id: 2010040801_to_2010041201.sql 3046 2010-04-20 08:37:56Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
-- コンテンツ参照トラン
ALTER TABLE _view_count DROP INDEX vc_type_id;                                                                    -- ユニーク制約削除
ALTER TABLE _view_count ADD vc_content_id            VARCHAR(50)        DEFAULT ''                    NOT NULL;      -- コンテンツ識別用のID
ALTER TABLE _view_count ADD UNIQUE (vc_type_id,      vc_content_serial, vc_content_id,           vc_date,       vc_hour);-- ユニーク制約再設定

-- 検索キーワードトラン
DROP TABLE IF EXISTS _search_word;
CREATE TABLE _search_word (
    sw_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    sw_word              TEXT                                         NOT NULL,      -- 検索キーワード
    sw_basic_word        TEXT                                         NOT NULL,      -- 比較用基本ワード
    sw_widget_id         VARCHAR(50)    DEFAULT ''                    NOT NULL,      -- ウィジェットID(ファイル名)
    sw_type              INT            DEFAULT 0                     NOT NULL,      -- 端末タイプ(0=PC、1=携帯、2=スマートフォン)
    sw_client_id         VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- PCの場合はアクセス管理用クッキー値。携帯の場合は端末ID「XX-xxxxxx」(XX=キャリアDC,AU,SB、xxxxxx=端末ID)。
    sw_access_log_serial INT            DEFAULT 0                     NOT NULL,      -- アクセスログシリアル番号
    sw_dt                TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- 記録日時
    PRIMARY KEY          (sw_serial)
) TYPE=innodb;

-- ウィジェット情報
DELETE FROM _widgets WHERE wd_id = 'custom_search';
INSERT INTO _widgets
(wd_id,           wd_name,        wd_type, wd_version, wd_author,                       wd_copyright,                    wd_license, wd_official_level, wd_description,         wd_add_script_lib, wd_add_script_lib_a, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('custom_search', 'カスタム検索', '',      '1.0.0',    '株式会社 毎日メディアサービス', '株式会社 毎日メディアサービス', 'GPL',      10,                '各種コンテンツが検索可能で表示レイアウトもカスタマイズ可能な検索ウィジェット', 'jquery', 'jquery.tablednd', true, false, true, true,          1, -1, now(), now());

-- *** システム標準テーブル ***
-- ユーザ作成コンテンツルームマスター
ALTER TABLE user_content_room ADD ur_content_update_dt     TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL;      -- コンテンツ更新日時
