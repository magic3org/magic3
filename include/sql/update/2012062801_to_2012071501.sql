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
-- * @version    SVN: $Id: 2012062801_to_2012071501.sql 5049 2012-07-22 10:50:57Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
-- 添付ファイルマスター
ALTER TABLE _attach_file ADD af_delete_log_serial INT            DEFAULT 0                     NOT NULL;      -- ファイル削除時のアクセスログシリアル番号

-- 汎用コンテンツ設定マスター
INSERT INTO content_config
(ng_type,   ng_id,                  ng_value,                              ng_name,                              ng_index) VALUES
('',        'layout_view_detail',   '[#BODY#][#FILES#][#PAGES#][#LINKS#]', 'コンテンツレイアウト(詳細表示)',               1),
('smartphone',        'layout_view_detail',   '[#BODY#][#FILES#][#PAGES#][#LINKS#]', 'コンテンツレイアウト(詳細表示)',               1);

-- *** システム標準テーブル ***

