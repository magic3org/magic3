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
-- * @version    SVN: $Id: 2012071501_to_2012080601.sql 5097 2012-08-10 23:58:31Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
INSERT INTO _system_config 
(sc_id,                          sc_value,                  sc_name) VALUES
('use_jquery',               '1',                       '一般画面にjQueryを使用');

-- *** システム標準テーブル ***
-- 汎用コンテンツマスター
ALTER TABLE content ADD cn_option_fields     TEXT                                         NOT NULL;      -- 追加フィールド

-- 汎用コンテンツ設定マスター
INSERT INTO content_config
(ng_type,      ng_id,              ng_value,                              ng_name,                              ng_index) VALUES
('',           'output_head',      '0', 'HTMLヘッダ出力', 2),
('',           'head_view_detail', '<meta property="og:type" content="article" /><meta property="og:title" content="[#CT_TITLE#]" /><meta property="og:url" content="[#CT_URL#]" /><meta property="og:description" content="[#CT_DESCRIPTION#]" /><meta property="og:site_name" content="[#SITE_NAME#]" />', 'HTMLヘッダ(詳細表示)',               3),
('smartphone', 'output_head',      '0', 'HTMLヘッダ出力', 2),
('smartphone', 'head_view_detail', '<meta property="og:type" content="article" /><meta property="og:title" content="[#CT_TITLE#]" /><meta property="og:url" content="[#CT_URL#]" /><meta property="og:description" content="[#CT_DESCRIPTION#]" /><meta property="og:site_name" content="[#SITE_NAME#]" />', 'HTMLヘッダ(詳細表示)',               3);
