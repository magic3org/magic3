-- *
-- * バージョンアップ用スクリプト
-- *
-- * PHP versions 5
-- *
-- * LICENSE: This source file is licensed under the terms of the GNU General Public License.
-- *
-- * @package    Magic3 Framework
-- * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
-- * @copyright  Copyright 2006-2013 Magic3 Project.
-- * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
-- * @version    SVN: $Id: 2013062901_to_2013071401.sql 6167 2013-07-14 05:41:34Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***

-- *** システム標準テーブル ***
-- カレンダー定義
ALTER TABLE calendar_def ADD cd_open_date_style             TEXT                                         NOT NULL;      -- 開業日HTMLスタイル属性
ALTER TABLE calendar_def ADD cd_closed_date_style             TEXT                                         NOT NULL;      -- 休業日HTMLスタイル属性

-- カレンダーイベントマスター
DROP TABLE IF EXISTS calendar_event;
CREATE TABLE calendar_event (
    cv_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    cv_id                INT            DEFAULT 0                     NOT NULL,      -- エントリーID
    cv_history_index     INT            DEFAULT 0                     NOT NULL,      -- 履歴管理用インデックスNo(0～)
    
    cv_name              VARCHAR(60)    DEFAULT ''                    NOT NULL,      -- エントリータイトル
    cv_html              TEXT                                         NOT NULL,      -- エントリー本文HTML
    cv_start_dt          TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- イベント期間(開始)
    cv_end_dt            TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- イベント期間(終了)
    cv_visible           BOOLEAN        DEFAULT true                  NOT NULL,      -- 表示可否
    cv_is_all_day        BOOLEAN        DEFAULT false                 NOT NULL,      -- 終日イベントかどうか
    
    cv_create_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード作成者
    cv_create_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード作成日時
    cv_update_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード更新者
    cv_update_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード更新日時
    cv_deleted           BOOLEAN        DEFAULT false                 NOT NULL,      -- レコード削除状態
    PRIMARY KEY          (cv_serial),
    UNIQUE               (cv_id,        cv_history_index)
) TYPE=innodb;