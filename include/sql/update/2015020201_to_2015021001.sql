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
INSERT INTO _system_config 
(sc_id,                 sc_value,    sc_name) VALUES
('upload_image_autoresize', '1',         'アップロード画像の自動リサイズ'),
('upload_image_autoresize_max_width', '1024',         'アップロード画像自動リサイズの最大幅'),
('upload_image_autoresize_max_height', '1024',         'アップロード画像自動リサイズの最大高さ');

DELETE FROM _system_config WHERE sc_id = 'thumb_format';
INSERT INTO _system_config 
(sc_id,                          sc_value,                  sc_name) VALUES
('thumb_format', '72c.jpg;80c.jpg;160x120c.jpg;200c.jpg',   'コンテンツ用サムネール仕様');

-- *** システム標準テーブル ***
-- ブログ設定マスター
INSERT INTO blog_config
(bg_id,                     bg_value,                         bg_name,              bg_index) VALUES
('thumb_type',     's=80c.jpg;mw=160x120c.jpg;l=200c.jpg', '記事サムネールタイプ', 0);

