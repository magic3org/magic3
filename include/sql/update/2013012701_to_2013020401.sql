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
-- * @version    SVN: $Id: 2013012701_to_2013020401.sql 5619 2013-02-07 23:49:39Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***


-- *** システム標準テーブル ***
-- フォトギャラリー設定マスター
INSERT INTO photo_config
(hg_id,               hg_value,           hg_name,                                  hg_index) VALUES
('html_photo_description',  '0',                'HTML形式の画像情報(説明)',         0),
('use_photo_date',        '1',                '画像情報(撮影日)を使用',         0),
('use_photo_location',       '1',                '画像情報(撮影場所)を使用',         0),
('use_photo_camera',      '1',                '画像情報(カメラ)を使用',         0),
('use_photo_description', '1',                '画像情報(説明)を使用',         0),
('use_photo_keyword',     '1',                '画像情報(検索キーワード)を使用',         0),
('use_photo_category',    '1',                '画像情報(カテゴリー)を使用',         0),
('use_photo_rate',    '1',                '画像情報(評価)を使用',         0),
('layout_view_detail',   '<table class="photo_info"><caption>画像情報</caption><tbody><tr><th>ID</th><td>[#CT_ID#]</td></tr><tr><th>タイトル</th><td>[#CT_TITLE#]&nbsp;[#PERMALINK#]</td></tr><tr><th>撮影者</th><td>[#CT_AUTHOR#]</td></tr><tr><th>撮影日</th><td>[#CT_DATE#]</td></tr><tr><th>場所</th><td>[#CT_LOCATION#]</td></tr><tr><th>カメラ</th><td>[#CT_CAMERA#]</td></tr><tr><th>説明</th><td>[#CT_DESCRIPTION#]</td></tr><tr><th>カテゴリー</th><td>[#CT_CATEGORY#]</td></tr><tr><th>キーワード</th><td>[#CT_KEYWORD#]</td></tr><tr><th>評価</th><td>[#RATE#]</td></tr></tbody></table>', 'レイアウト(詳細表示)',               0);

-- 写真情報マスター
ALTER TABLE photo ADD ht_description              TEXT                                         NOT NULL;      -- 画像説明

-- 画像評価トラン
ALTER TABLE photo_rate ADD hr_client_id         CHAR(32)       DEFAULT ''                    NOT NULL;      -- クライアントID