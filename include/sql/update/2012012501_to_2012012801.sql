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
-- * @version    SVN: $Id: 2012012501_to_2012012801.sql 4626 2012-01-28 12:39:25Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***

-- *** システム標準テーブル ***
-- ブログ設定マスター
INSERT INTO blog_config
(bg_id,                     bg_value,    bg_name,                              bg_index) VALUES
('s:entry_view_count',        '10',        '記事表示数',                         100),
('s:entry_view_order',        '1',         '記事表示順',                         101),
('s:top_content',  '',          'トップ画面コンテンツ', 102),
('s:auto_resize_image_max_size',  '280',      '画像の自動変換最大サイズ',           103),
('s:jquery_view_style',       '1',      'jQueryMobile表示スタイル',           104),
('s:use_title_list_image',       '1',      'タイトルリスト画像を使用',           105),
('s:title_list_image',       '',      'タイトルリスト画像',           106);
