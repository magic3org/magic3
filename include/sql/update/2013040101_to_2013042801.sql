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
-- * @version    SVN: $Id: 2013040101_to_2013042801.sql 6022 2013-05-20 04:28:13Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
DELETE FROM _system_config WHERE sc_id = 'wysiwyg_editor';
INSERT INTO _system_config 
(sc_id,                          sc_value,                  sc_name) VALUES
('wysiwyg_editor', 'ckeditor',   'WYSIWYGエディター');

-- *** システム標準テーブル ***
-- *** システム標準テーブル ***
-- 汎用コンテンツマスター
ALTER TABLE content ADD cn_search_content     TEXT NOT NULL;       -- 検索用コンテンツ

