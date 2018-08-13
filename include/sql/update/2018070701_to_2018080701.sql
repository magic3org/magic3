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
(sc_id,                        sc_value,              sc_name) VALUES
('use_landing_page',           '0',                   'ランディングページ機能を使用するかどうか'),
('system_manager_enable_task', 'top,userlist_detail,loginhistory', 'システム運用者が実行可能な管理画面タスク');

-- ランディングページ情報マスター
DROP TABLE IF EXISTS _landing_page;
CREATE TABLE _landing_page (
    lp_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    lp_id                VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- ランディングページID
    lp_history_index     INT            DEFAULT 0                     NOT NULL,      -- 履歴管理用インデックスNo(0～)
    
    lp_name              VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- ページ名
    lp_visible           BOOLEAN        DEFAULT true                  NOT NULL,      -- 公開可否
	lp_owner_id          INT            DEFAULT 0                     NOT NULL,      -- ページの所有者ID
	lp_regist_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- ページ作成日時
	
    lp_create_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード作成者
    lp_create_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード作成日時
    lp_update_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード更新者
    lp_update_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード更新日時
    lp_deleted           BOOLEAN        DEFAULT false                 NOT NULL,      -- レコード削除状態
    PRIMARY KEY          (lp_serial),
    UNIQUE               (lp_id,        lp_history_index)
) ENGINE=innodb;

-- ウィジェット情報マスター
ALTER TABLE _widgets ADD wd_personal_mode           BOOLEAN        DEFAULT false                 NOT NULL;      -- パーソナルモード対応かどうか

-- *** システム標準テーブル ***
