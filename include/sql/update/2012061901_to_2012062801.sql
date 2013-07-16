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
-- * @version    SVN: $Id: 2012061901_to_2012062801.sql 6134 2013-06-26 00:06:38Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
-- ダウンロード実行ログトラン
ALTER TABLE _download_log MODIFY dl_content_type      VARCHAR(20)    DEFAULT ''                    NOT NULL;      -- コンテンツ種別

-- 添付ファイルマスター
DROP TABLE IF EXISTS _attach_file;
CREATE TABLE _attach_file (
    af_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    af_content_type      VARCHAR(20)    DEFAULT ''                    NOT NULL,      -- コンテンツ種別
    af_content_id        VARCHAR(32)    DEFAULT ''                    NOT NULL,      -- コンテンツID
    af_content_serial    INT            DEFAULT 0                     NOT NULL,      -- 対応コンテンツシリアル番号
    af_index             INT            DEFAULT 0                     NOT NULL,      -- インデックス番号(0～)
    af_client_id         CHAR(32)       DEFAULT ''                    NOT NULL,      -- クライアントID
    
    af_file_id           CHAR(32)       DEFAULT ''                    NOT NULL,      -- ファイル識別ID
    af_filename          VARCHAR(256)   DEFAULT ''                    NOT NULL,      -- ダウンロード用ファイル名
    af_title             VARCHAR(40)    DEFAULT ''                    NOT NULL,      -- タイトル
    af_desc              TEXT                                         NOT NULL,      -- 説明
    af_file_type         VARCHAR(30)    DEFAULT ''                    NOT NULL,      -- ファイルタイプ
    af_original_filename VARCHAR(256)   DEFAULT ''                    NOT NULL,      -- 元のファイル名
    af_file_size         INT            DEFAULT 0                     NOT NULL,      -- ファイルサイズ(バイト)
    af_file_dt           TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- ファイル作成日時
    af_file_deleted_dt   TIMESTAMP      DEFAULT '0000-00-00 00:00:00' NOT NULL,      -- ファイル削除日時
    af_file_deleted      BOOLEAN        DEFAULT false                 NOT NULL,      -- アップロードファイルの削除状態
    af_upload_log_serial INT            DEFAULT 0                     NOT NULL,      -- アップロード時のアクセスログシリアル番号
    PRIMARY KEY          (af_serial),
    UNIQUE               (af_content_type,      af_content_id,        af_content_serial, af_index, af_client_id)
) TYPE=innodb;

-- *** システム標準テーブル ***
-- 汎用コンテンツマスター
ALTER TABLE content ADD cn_related_content     TEXT NOT NULL;       -- 関連コンテンツID(「,」区切り)
ALTER TABLE content ADD cn_related_url     TEXT NOT NULL;       -- 関連URL(「;」区切り)
