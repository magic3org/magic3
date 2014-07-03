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
-- テンプレート情報マスター
ALTER TABLE _templates ADD tm_version      VARCHAR(10)    DEFAULT ''                    NOT NULL;      -- テンプレートバージョン文字列

-- 新着情報トラン
ALTER TABLE news ADD nw_device_type       INT            DEFAULT 0                     NOT NULL;      -- 端末タイプ(0=PC、1=携帯、2=スマートフォン)
ALTER TABLE news ADD nw_content_type      VARCHAR(10)    DEFAULT ''                    NOT NULL;      -- コンテンツの種別
ALTER TABLE news ADD nw_content_id        TEXT                                         NOT NULL;      -- コンテンツID
ALTER TABLE news ADD nw_summary           VARCHAR(100)   DEFAULT ''                    NOT NULL;      -- 概要
ALTER TABLE news ADD nw_mark              INT            DEFAULT 0                     NOT NULL;      -- 付加マーク(0=なし、1=新規)
ALTER TABLE news ADD nw_visible           BOOLEAN        DEFAULT false                 NOT NULL;      -- 表示するかどうか
ALTER TABLE news ADD nw_user_limited      BOOLEAN        DEFAULT false                 NOT NULL;      -- アクセス可能ユーザを制限
ALTER TABLE news ADD nw_create_user_id    INT            DEFAULT 0                     NOT NULL;      -- レコード作成者
ALTER TABLE news ADD nw_create_dt         TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL;      -- レコード作成日時
ALTER TABLE news ADD nw_id                INT            DEFAULT 0                     NOT NULL;      -- ID
ALTER TABLE news ADD nw_history_index     INT            DEFAULT 0                     NOT NULL;      -- 履歴管理用インデックスNo(0～)
ALTER TABLE news ADD UNIQUE (nw_id,  nw_history_index);            -- ユニーク制約再設定

-- ウィジェット情報マスター
ALTER TABLE _widgets ADD wd_content_widget_id         VARCHAR(50)    DEFAULT ''                    NOT NULL;      -- コンテンツ編集用のウィジェット

-- *** システム標準テーブル ***
-- 新着情報設定マスター
DROP TABLE IF EXISTS news_config;
CREATE TABLE news_config (
    nc_id                VARCHAR(30)    DEFAULT ''                    NOT NULL,      -- ID(Key)
    nc_value             TEXT                                         NOT NULL,      -- 値
    nc_name              VARCHAR(50)    DEFAULT ''                    NOT NULL,      -- 名称
    nc_description       VARCHAR(80)    DEFAULT ''                    NOT NULL,      -- 説明
    nc_index             INT            DEFAULT 0                     NOT NULL,      -- ソート用
    PRIMARY KEY          (nc_id)
) TYPE=innodb;

INSERT INTO news_config
(nc_id,                  nc_value,    nc_name,                              nc_index) VALUES
('default_message',   '「[#TITLE#]」を追加しました', 'デフォルトメッセージ',               1),
('date_format',       'n月j日', '日時フォーマット',               1),
('layout_list_item',  '[#DATE#] [#MESSAGE#][#MARK#]', 'リスト項目レイアウト',               1);
