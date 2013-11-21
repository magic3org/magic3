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
-- * @version    SVN: $Id: 2009071401_to_2009071601.sql 2144 2009-07-19 06:59:15Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
-- ユーザアクセスログトラン
ALTER TABLE _access_log ADD al_path VARCHAR(40)    DEFAULT ''                    NOT NULL;      -- アクセスポイントパス

-- クライアントIPアクセス制御マスター
DROP TABLE IF EXISTS _access_ip;
CREATE TABLE _access_ip (
    ai_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    ai_type              INT            DEFAULT 0                     NOT NULL,      -- アクセス制御タイプ(0=未設定、1=管理機能アクセス許可、2=アクセス拒否、3=登録許可)
    ai_ip                VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- アクセス元IP(IPv6対応)
    ai_ip_mask           VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- IPマスク値
    ai_server_id         CHAR(32)       DEFAULT ''                    NOT NULL,      -- サーバ識別ID
    
    ai_param             TEXT                                         NOT NULL,      -- その他パラメータ
    PRIMARY KEY          (ai_serial),
    UNIQUE               (ai_type, ai_ip, ai_ip_mask, ai_server_id)
) TYPE=innodb;

-- ページIDマスター
INSERT INTO _page_id 
(pg_id,          pg_type, pg_default_sub_id, pg_path,       pg_name,                        pg_description,                       pg_priority, pg_active, pg_mobile, pg_editable) VALUES
('regist',       0,       'content',         'regist',      'データ登録用アクセスポイント', 'データ登録用アクセスポイント',       3,           true,      false,     true);

-- ページ情報マスター
DELETE FROM _page_info;
INSERT INTO _page_info
(pn_id,     pn_sub_id,   pn_content_type, pn_use_ssl) VALUES
('index',   'content',   'content',       false),
('index',   'wiki',      'wiki',          false),
('index',   'contact',   '',              true),
('index',   'shop_safe', '',              true),
('index',   'safe',      '',              true),
('m_index', 'content',   'content',       false),
('m_index', 'wiki',      'wiki',          false);

-- ウィジェット情報マスター
DELETE FROM _widgets WHERE wd_id = 'fontsize';
INSERT INTO _widgets
(wd_id,   wd_name, wd_version, wd_author,      wd_copyright, wd_license, wd_official_level, wd_description,                                     wd_read_scripts, wd_read_css, wd_add_script_lib, wd_add_script_lib_a, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_install_dt, wd_create_dt) VALUES
('fontsize', 'フォントサイズ',  '1.0.0',    'Naoki Hirata', 'Magic3.org', 'GPL',      10,                'フォントサイズを拡大縮小して保持します', true,            true,       'jquery.cookie',                       '',                  true,         false,               false,                true,           now(),         now());

-- *** システム標準テーブル ***
