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
-- * @version    SVN: $Id: 2012012801_to_2012022401.sql 4728 2012-03-01 14:52:09Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
-- 運用ログトラン
ALTER TABLE _operation_log ADD ol_widget_id         VARCHAR(50)    DEFAULT ''                    NOT NULL;      -- 実行ウィジェットID(ファイル名)

-- ページ定義マスター
ALTER TABLE _page_def ADD pd_password          CHAR(32)       DEFAULT ''                    NOT NULL;      -- アクセス制限パスワード(MD5)

-- *** システム標準テーブル ***
-- 汎用コンテンツマスター
ALTER TABLE content ADD cn_password          CHAR(32)       DEFAULT ''                    NOT NULL;      -- アクセス制限パスワード(MD5)

-- フォトギャラリー設定マスター(スマートフォン用)
INSERT INTO photo_config
(hg_id,                     hg_value,           hg_name,                                  hg_index) VALUES
('s:photo_list_item_count',  '24',                '画像一覧表示項目数',         9),
('s:photo_list_order',        '1',         '画像一覧表示順',                         10),
('s:photo_title_short_length',  '7',                '画像タイトル(略式)文字数',         11),
('s:photo_list_sort_key', 'index',            '画像一覧のソートキー',         16),
('s:default_image_size',      '320',              '公開画像デフォルトサイズ',               4),
('s:default_thumbnail_size',  '128',              'サムネール画像デフォルトサイズ',         5);
