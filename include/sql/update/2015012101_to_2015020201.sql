-- *
-- * バージョンアップ用スクリプト
-- *
-- * PHP versions 5
-- *
-- * LICENSE: This source file is licensed under the terms of the GNU General Public License.
-- *
-- * @package    Magic3 Framework
-- * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
-- * @copyright  Copyright 2006-2015 Magic3 Project.
-- * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
-- * @version    SVN: $Id$
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
-- システム設定マスター
DELETE FROM _system_config WHERE sc_id = 'thumb_format';
INSERT INTO _system_config 
(sc_id,                          sc_value,                  sc_name) VALUES
('thumb_format', '72c.jpg;80c.jpg;200c.jpg',   'サムネール仕様');


-- *** システム標準テーブル ***

-- ブログ設定マスター
DELETE FROM blog_config WHERE sc_id = 'entry_default_image';
INSERT INTO blog_config
(bg_id,                     bg_value,                         bg_name,              bg_index) VALUES
('entry_default_image',     '0_72c.jpg;0_80c.jpg;0_200c.jpg', '記事デフォルト画像', 0);
