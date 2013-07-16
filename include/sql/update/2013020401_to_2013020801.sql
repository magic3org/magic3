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
-- * @version    SVN: $Id: 2013020401_to_2013020801.sql 5640 2013-02-11 21:52:48Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
INSERT INTO _system_config 
(sc_id,                          sc_value,                  sc_name) VALUES
('ogp_thumb_format', '200c.jpg',   'OGP用サムネール仕様'),
('wysiwyg_editor', 'fckeditor',   'WYSIWYGエディター');

-- *** システム標準テーブル ***
-- フォトギャラリー設定マスター
INSERT INTO photo_config
(hg_id,               hg_value,           hg_name,                                  hg_index) VALUES
('output_head',      '0', 'HTMLヘッダ出力', 0),
('head_view_detail',   '<meta property="og:type" content="article" /><meta property="og:title" content="[#CT_TITLE#]" /><meta property="og:url" content="[#CT_URL#]" /><meta property="og:image" content="[#CT_IMAGE#]" /><meta property="og:description" content="[#CT_SUMMARY#]" /><meta property="og:site_name" content="[#SITE_NAME#]" />', 'ヘッダ出力(詳細表示)',               0);

-- 写真情報マスター
ALTER TABLE photo ADD ht_summary              VARCHAR(100)   DEFAULT ''                    NOT NULL;      -- 画像概要
ALTER TABLE photo ADD ht_thumb_filename     TEXT                                         NOT NULL;      -- サムネールファイル名(「;」区切り)