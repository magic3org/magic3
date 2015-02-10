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
-- * @version    SVN: $Id: 2012080601_to_2012081301.sql 5116 2012-08-18 08:58:12Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
-- システム設定マスター
DELETE FROM _system_config WHERE sc_id = 'site_logo_filename';
INSERT INTO _system_config 
(sc_id,                          sc_value,                  sc_name) VALUES
('site_logo_filename', 'logo_72c.jpg;logo_200c.jpg',   'サイトロゴファイル名'),
('thumb_format', '72c.jpg;200c.jpg',   'コンテンツ用サムネール仕様');

-- サイト定義マスター
DELETE FROM _site_def WHERE sd_id = 'head_others';
INSERT INTO _site_def
(sd_id,                  sd_language_id, sd_value,         sd_name) VALUES
('head_others',          'ja',           '<meta property="og:type" content="website" /><meta property="og:url" content="[#SITE_URL#]" /><meta property="og:image" content="[#SITE_IMAGE#]" /><meta property="og:title" content="[#SITE_NAME#]" /><meta property="og:description" content="[#SITE_DESCRIPTION#]" />',               'HTMLヘッダその他');

-- *** システム標準テーブル ***
-- 汎用コンテンツマスター
ALTER TABLE content ADD cn_thumb_filename     TEXT                                         NOT NULL;      -- サムネールファイル名(「;」区切り)

-- ブログエントリー(記事)マスター
ALTER TABLE blog_entry ADD be_thumb_filename     TEXT                                         NOT NULL;      -- サムネールファイル名(「;」区切り)

-- 汎用コンテンツ設定マスター
DELETE FROM content_config WHERE ng_id = 'head_view_detail';
INSERT INTO content_config
(ng_type,      ng_id,              ng_value,                              ng_name,                              ng_index) VALUES
('',           'head_view_detail', '<meta property="og:type" content="article" /><meta property="og:title" content="[#CT_TITLE#]" /><meta property="og:url" content="[#CT_URL#]" /><meta property="og:image" content="[#CT_IMAGE#]" /><meta property="og:description" content="[#CT_DESCRIPTION#]" /><meta property="og:site_name" content="[#SITE_NAME#]" />', 'HTMLヘッダ(詳細表示)',               3),
('smartphone', 'head_view_detail', '<meta property="og:type" content="article" /><meta property="og:title" content="[#CT_TITLE#]" /><meta property="og:url" content="[#CT_URL#]" /><meta property="og:image" content="[#CT_IMAGE#]" /><meta property="og:description" content="[#CT_DESCRIPTION#]" /><meta property="og:site_name" content="[#SITE_NAME#]" />', 'HTMLヘッダ(詳細表示)',               3);
