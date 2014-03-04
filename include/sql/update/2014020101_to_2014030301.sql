-- *
-- * バージョンアップ用スクリプト
-- *
-- * PHP versions 5
-- *
-- * LICENSE: This source file is licensed under the terms of the GNU General Public License.
-- *
-- * @package    Magic3 Framework
-- * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
-- * @copyright  Copyright 2006-2014 Magic3 Project.
-- * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
-- * @version    SVN: $Id$
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
-- ウィジェット情報マスター
ALTER TABLE _widgets ADD wd_category_id       VARCHAR(20)    DEFAULT ''                    NOT NULL;      -- 所属カテゴリー

-- ウィジェットカテゴリマスター
DROP TABLE IF EXISTS _widget_category;
CREATE TABLE _widget_category (
    wt_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    wt_id                VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- カテゴリID
    wt_history_index     INT            DEFAULT 0                     NOT NULL,      -- 履歴管理用インデックスNo(0～)

    wt_name              VARCHAR(30)    DEFAULT ''                    NOT NULL,      -- カテゴリ名称
    wt_sort_order        INT            DEFAULT 0                     NOT NULL,      -- ソート用
    wt_visible           BOOLEAN        DEFAULT true                  NOT NULL,      -- 表示するかどうか

    wt_create_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード作成者
    wt_create_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード作成日時
    wt_update_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード更新者
    wt_update_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード更新日時
    wt_deleted           BOOLEAN        DEFAULT false                 NOT NULL,      -- レコード削除状態
    PRIMARY KEY          (wt_serial),
    UNIQUE               (wt_id,        wt_history_index)
) TYPE=innodb;

INSERT INTO _widget_category
(wt_id, wt_name,            wt_sort_order) VALUES
('',     'その他',   100),
('content',     '汎用コンテンツ',   1),
('blog',     'ブログ',           2),
('commerce',     'Eコマース',        3),
('photo',     'フォトギャラリー', 4),
('event',     'イベント情報',     5),
('search',     '検索',             6),
('menu',     'メニュー',         7),
('admin',     '管理画面用',      8);

-- *** システム標準テーブル ***

