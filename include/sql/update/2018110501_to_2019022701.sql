-- *
-- * バージョンアップ用スクリプト
-- *
-- * PHP versions 5
-- *
-- * LICENSE: This source file is licensed under the terms of the GNU General Public License.
-- *
-- * @package    Magic3 Framework
-- * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
-- * @copyright  Copyright 2006-2019 Magic3 Project.
-- * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
-- * @version    SVN: $Id$
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***

-- *** システム標準テーブル ***
-- ブログ記事画像マスター
DROP TABLE IF EXISTS blog_image;
CREATE TABLE blog_image (
    bm_serial            INT            AUTO_INCREMENT,                              -- レコードシリアル番号
    bm_entry_serial      INT            DEFAULT 0                     NOT NULL,      -- ブログ記事シリアル番号
    bm_index             INT            DEFAULT 0                     NOT NULL,      -- インデックス番号
    bm_image_src         TEXT                                         NOT NULL,      -- 画像ファイル(リソースディレクトリからの相対パス)
    PRIMARY KEY          (bm_serial),
    UNIQUE               (bm_entry_serial,      bm_index)
) ENGINE=innodb;
