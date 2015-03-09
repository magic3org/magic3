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

-- *** システム標準テーブル ***
-- イベント設定マスター
DELETE FROM event_config;
INSERT INTO event_config
(bg_id,                     bg_value,    bg_name,                              bg_index) VALUES
('entry_view_count',        '10',        '記事表示数',                         3),
('entry_view_order',        '0',         '記事表示順',                         4),
('top_contents',            '',          'トップ画面コンテンツ',               6),
('category_count',          '2',         '記事に設定可能なカテゴリ数',         10),
('thumb_type',              's=80c.jpg;mw=160x120c.jpg;l=200c.jpg', '記事サムネールタイプ定義', 0),
('entry_default_image',     '0_72c.jpg;0_80c.jpg;0_200c.jpg',       '記事デフォルト画像', 0),
('msg_no_entry',            'イベント記事は登録されていません',     'イベント記事が登録されていないメッセージ',                 0),
('msg_find_no_entry',       'イベント記事が見つかりません',         'イベント記事が見つからないメッセージ',                 0),
('msg_no_entry_in_future',  '今後のイベントはありません',           '予定イベントなし時メッセージ',                 0),
('layout_entry_single',     '<div class="entry_head"><span class="event_date">日時：[#DATE#]</span><span class="event_location">場所：[#CT_LOCATION#]</span><div><span class="event_contact">連絡先：[#CT_CONTACT#]</span></div><div><span class="event_url">URL：[#CT_INFO_URL:autolink=true;#]</span></div></div><div class="entry_content">[#BODY#][#RESULT#]</div>[#CATEGORY#][#LINKS#]', 'コンテンツレイアウト(記事詳細)',               0),
('layout_entry_list',       '[#TITLE#]<div class="entry_head"><span class="event_date">日時：[#DATE#]</span><span class="event_location">場所：[#CT_LOCATION#]</span><div>[#DETAIL_LINK#]</div></div><div class="entry_content">[#BODY#]</div>[#CATEGORY#]', 'コンテンツレイアウト(記事一覧)',               0),
('output_head',      '0', 'HTMLヘッダ出力', 0),
('head_view_detail', '<meta property="og:type" content="article" /><meta property="og:title" content="[#CT_TITLE#]" /><meta property="og:url" content="[#CT_URL#]" /><meta property="og:image" content="[#CT_IMAGE#]" /><meta property="og:description" content="[#CT_DESCRIPTION#]" /><meta property="og:site_name" content="[#SITE_NAME#]" />', 'HTMLヘッダ(詳細表示)',               0);

-- イベント記事マスター
ALTER TABLE event_entry ADD ee_thumb_filename    TEXT                                         NOT NULL;      -- サムネールファイル名(「;」区切り)
ALTER TABLE event_entry ADD ee_related_content   TEXT                                         NOT NULL;      -- 関連コンテンツID(「,」区切り)