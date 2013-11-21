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
-- * @version    SVN: $Id: 2008111901_to_2008112601.sql 1300 2008-11-30 11:45:58Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
-- 運用ログトラン
ALTER TABLE _operation_log ADD ol_message_ext TEXT                                         NOT NULL;      -- メッセージ詳細
ALTER TABLE _operation_log ADD ol_checked     BOOLEAN        DEFAULT false                 NOT NULL;      -- メッセージ確認状況

-- 運用メッセージタイプマスター
DROP TABLE IF EXISTS _operation_type;
CREATE TABLE _operation_type (
    ot_id                VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- 運用メッセージタイプID
    ot_name              VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- 運用メッセージ名称
    ot_description       VARCHAR(100)   DEFAULT ''                    NOT NULL,      -- 説明
    ot_level             INT            DEFAULT 0                     NOT NULL,      -- メッセージレベル(0=通常、1=注意、10=要確認)
    ot_sort_order        INT            DEFAULT 0                     NOT NULL,      -- ソート順
    PRIMARY KEY  (ot_id)
) TYPE=innodb;
INSERT INTO _operation_type
(ot_id,         ot_name,                ot_description,                               ot_level, ot_sort_order) VALUES
('info',        'システム情報',         'システム運用の正常な動作を示します',         0,        1),
('warn',        'システム警告',         'システム運用の注意が必要な動作を示します',   1,        2),
('error',       'システム通常エラー',   'システム運用の異常な動作を示します',         10,       3),
('fatal',       'システム致命的エラー', 'システム運用の致命的に異常な動作を示します', 10,       4),
('user_info',   'ユーザ操作',           'ユーザ操作の正常な動作を示します',           0,        5),
('user_err',    'ユーザ操作エラー',     'ユーザ操作の異常な動作を示します',           10,       6),
('user_access', 'ユーザ不正アクセス',   'ユーザ操作の不正なアクセスを示します',       10,       7),
('user_data',   'ユーザ不正データ',     'ユーザ操作の不正なデータ送信を示します',     10,       8);

-- *** システム標準テーブル ***
