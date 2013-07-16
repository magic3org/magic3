-- *
-- * バージョンアップ用スクリプト
-- *
-- * PHP versions 5
-- *
-- * LICENSE: This source file is licensed under the terms of the GNU General Public License.
-- *
-- * @package    Magic3 Framework
-- * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
-- * @copyright  Copyright 2006-2012 Magic3 Project.
-- * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
-- * @version    SVN: $Id: 2012082201_to_2012090701.sql 5212 2012-09-16 03:53:02Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
-- システム設定マスター
INSERT INTO _system_config 
(sc_id,                          sc_value,                  sc_name) VALUES
('avatar_format',      '72c.jpg',   'アバター仕様');

-- *** システム標準テーブル ***

-- ブログ設定マスター
INSERT INTO blog_config
(bg_id,                     bg_value,    bg_name,                              bg_index) VALUES
('entry_default_image',      '0_72c.jpg;0_200c.jpg', '記事デフォルト画像', 0),
('comment_user_limited',      '0',       'コメントのユーザ制限',                 0);

-- 運用メッセージタイプマスター
DELETE FROM _operation_type;
INSERT INTO _operation_type
(ot_id,          ot_name,                ot_description,                               ot_level, ot_sort_order) VALUES
('info',         'システム情報',         'システム運用の正常な動作を示します',         0,        1),
('request',      'システム操作要求',     'システムからの操作要求を示します',           1,        2),
('warn',         'システム警告',         'システム運用の注意が必要な動作を示します',   2,        3),
('error',        'システム通常エラー',   'システム運用の異常な動作を示します',         10,       4),
('fatal',        'システム致命的エラー', 'システム運用の致命的に異常な動作を示します', 10,       5),
('user_info',    'ユーザ操作',           'ユーザ操作の正常な動作を示します',           0,        6),
('user_request', 'ユーザ操作要求',       'ユーザ操作からの操作要求を示します',         1,        7),
('user_err',     'ユーザ操作エラー',     'ユーザ操作の異常な動作を示します',           10,       8),
('user_access',  'ユーザ不正アクセス',   'ユーザ操作の不正なアクセスを示します',       10,       9),
('user_data',    'ユーザ不正データ',     'ユーザ操作の不正なデータ送信を示します',     10,       10);

-- 運用ログトラン
ALTER TABLE _operation_log ADD ol_link TEXT                                   NOT NULL;      -- リンク先

-- ログインユーザ情報マスター(共通的に任意で使用するユーザ情報テーブル)
ALTER TABLE _login_user_info ADD li_profile TEXT                                   NOT NULL;      -- 自己紹介
