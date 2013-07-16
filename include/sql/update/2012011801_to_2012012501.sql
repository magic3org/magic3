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
-- * @version    SVN: $Id: 2012011801_to_2012012501.sql 4615 2012-01-25 00:46:36Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***

-- *** システム標準テーブル ***
-- 写真情報マスター
ALTER TABLE photo ADD ht_sort_order        INT            DEFAULT 0                     NOT NULL;      -- ソート順

-- フォトギャラリー設定マスター
INSERT INTO photo_config
(hg_id,                 hg_value,           hg_name,                                  hg_index) VALUES
('image_size',          '450',              '公開画像サイズ',               13),
('thumbnail_size',      '128',              'サムネール画像サイズ',         14),
('image_quality',       '100',              '画像の品質',                   15),
('photo_list_sort_key', 'index',            '画像一覧のソートキー',         16);
