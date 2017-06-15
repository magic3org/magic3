-- *
-- * バージョンアップ用スクリプト
-- *
-- * PHP versions 5
-- *
-- * LICENSE: This source file is licensed under the terms of the GNU General Public License.
-- *
-- * @package    Magic3 Framework
-- * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
-- * @copyright  Copyright 2006-2017 Magic3 Project.
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
(sc_id,                 sc_value,         sc_name) VALUES
('default_content_type', 'blog',     'デフォルトコンテンツタイプ');

-- *** システム標準テーブル ***
-- 汎用コンテンツマスター
ALTER TABLE content ADD cn_thumb_src     TEXT                                         NOT NULL;      -- サムネールの元のファイル(リソースディレクトリからの相対パス)

-- ブログエントリー(記事)マスター
ALTER TABLE blog_entry ADD be_thumb_src     TEXT                                         NOT NULL;      -- サムネールの元のファイル(リソースディレクトリからの相対パス)

