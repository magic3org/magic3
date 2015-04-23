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
-- テンプレート情報
DELETE FROM _templates WHERE tm_id = 'art42_sample4';
INSERT INTO _templates
(tm_id,                           tm_name,                         tm_type, tm_device_type, tm_mobile, tm_use_bootstrap, tm_available, tm_clean_type, tm_create_dt) VALUES
('art42_sample4',                 'art42_sample4',                 2,       0,              false,     false,            true,         0,             now());


-- *** システム標準テーブル ***
-- イベント記事マスター
ALTER TABLE event_entry MODIFY ee_summary TEXT NOT NULL;      -- 概要
ALTER TABLE event_entry MODIFY ee_place TEXT NOT NULL;        -- 場所
ALTER TABLE event_entry MODIFY ee_contact TEXT NOT NULL;      -- 連絡先(Eメール,電話番号)

-- イベント設定マスター
DELETE FROM event_config WHERE eg_id = 'layout_entry_single';
DELETE FROM event_config WHERE eg_id = 'layout_entry_list';
INSERT INTO event_config
(eg_id,                     eg_value,    eg_name,                              eg_index) VALUES
('layout_entry_single',     '<div class="entry_head"><span class="event_date">日時：[#DATE#]</span><span class="event_location">場所：[#CT_PLACE#]</span><div><span class="event_contact">連絡先：[#CT_CONTACT#]</span></div><div><span class="event_url">URL：[#CT_INFO_URL:autolink=true;#]</span></div></div><div class="entry_content">[#BODY#][#RESULT#]</div>[#CATEGORY#][#LINKS#]', 'コンテンツレイアウト(記事詳細)',               0),
('layout_entry_list',       '[#TITLE#]<div class="entry_head"><span class="event_date">日時：[#DATE#]</span><span class="event_location">場所：[#CT_PLACE#]</span><div>[#DETAIL_LINK#]</div></div><div class="entry_content">[#BODY#]</div>[#CATEGORY#]', 'コンテンツレイアウト(記事一覧)',               0);
