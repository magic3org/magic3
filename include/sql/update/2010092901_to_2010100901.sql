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
-- * @version    SVN: $Id: 2010092901_to_2010100901.sql 3689 2010-10-11 06:54:32Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
-- 多言語対応文字列マスター
INSERT INTO _language_string
(ls_id,             ls_language_id, ls_value,             ls_name) VALUES
('msg_access_deny', 'ja',           'アクセスできません', 'アクセス不可メッセージ');

-- システム設定
INSERT INTO _system_config 
(sc_id,                      sc_value,                  sc_name) VALUES
('use_content_access_deny',  '0',                       'アクセス不可画面用コンテンツの取得');

-- ユーザアクセスログトラン
ALTER TABLE _access_log ADD al_session      CHAR(32)       DEFAULT ''                    NOT NULL;      -- セッションID
ALTER TABLE _access_log ADD al_device_id    VARCHAR(32)    DEFAULT ''                    NOT NULL;      -- 端末ID(携帯のときの端末ID)

-- *** システム標準テーブル ***

