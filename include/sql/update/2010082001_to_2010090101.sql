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
-- * @version    SVN: $Id: 2010082001_to_2010090101.sql 3580 2010-09-10 08:56:41Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
-- システム設定マスター
INSERT INTO _system_config 
(sc_id,                 sc_value,         sc_name) VALUES
('admin_default_theme', 'start',          '管理画面用jQueryUIテーマ');

-- *** システム標準テーブル ***
-- ブログIDマスター
DROP TABLE IF EXISTS blog_id;
CREATE TABLE blog_id (
    bl_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    bl_id                VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- ブログID
    bl_history_index     INT            DEFAULT 0                     NOT NULL,      -- 履歴管理用インデックスNo(0～)
    
    bl_name              VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- 名前
    bl_template_id       VARCHAR(50)    DEFAULT ''                    NOT NULL,      -- テンプレートID
    bl_meta_title        TEXT                                         NOT NULL,      -- METAタグ、タイトル
    bl_meta_description  TEXT                                         NOT NULL,      -- METAタグ、ページ要約
    bl_meta_keywords     TEXT                                         NOT NULL,      -- METAタグ、検索用キーワード
    bl_owner_id          INT            DEFAULT 0                     NOT NULL,      -- ブログの所有者ID
    bl_group_id          INT            DEFAULT 0                     NOT NULL,      -- 所属グループID
    bl_visible           BOOLEAN        DEFAULT true                  NOT NULL,      -- 公開可否
    bl_active_start_dt   TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- 公開期間(開始)
    bl_active_end_dt     TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- 公開期間(終了)
    bl_content_update_dt TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- コンテンツ更新日時
    
    bl_create_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード作成者
    bl_create_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード作成日時
    bl_update_user_id    INT            DEFAULT 0                     NOT NULL,      -- レコード更新者
    bl_update_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- レコード更新日時
    bl_deleted           BOOLEAN        DEFAULT false                 NOT NULL,      -- レコード削除状態
    PRIMARY KEY          (bl_serial),
    UNIQUE               (bl_id,        bl_history_index)
) TYPE=innodb;

-- ブログエントリー(記事)マスター
ALTER TABLE blog_entry ADD be_blog_id              VARCHAR(20)    DEFAULT ''                    NOT NULL;      -- ブログID
ALTER TABLE blog_entry ADD be_show_comment         BOOLEAN        DEFAULT true                  NOT NULL;      -- コメントを表示するかどうか
ALTER TABLE blog_entry ADD be_receive_comment      BOOLEAN        DEFAULT false                 NOT NULL;      -- コメントの受け付け可否

-- ブログ設定マスター
INSERT INTO blog_config
(bg_id,                    bg_value,    bg_name) VALUES
('comment_count',          '100',       '1投稿記事のコメント最大数'),
('comment_open_time',      '30',        'コメント投稿可能期間(日)');
