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
-- * @version    SVN: $Id: 2012081301_to_2012082201.sql 5149 2012-09-03 01:42:12Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***

-- *** システム標準テーブル ***

-- ブログエントリー(記事)マスター
ALTER TABLE blog_entry ADD be_related_content     TEXT NOT NULL;       -- 関連コンテンツID(「,」区切り)
ALTER TABLE blog_entry ADD be_option_fields     TEXT                                         NOT NULL;      -- 追加フィールド

-- ブログ設定マスター
INSERT INTO blog_config
(bg_id,                     bg_value,    bg_name,                              bg_index) VALUES
('layout_entry_single',   '[#TITLE#]<small>[#CT_AUTHOR#] [#CT_DATE#] [#CT_TIME#] [#BLOG_LINK#]</small>[#BODY#][#CATEGORY#][#LINKS#]', 'コンテンツレイアウト(記事詳細)',               0),
('layout_entry_list',   '[#TITLE#]<small>[#CT_AUTHOR#] [#CT_DATE#] [#CT_TIME#] [#BLOG_LINK#]</small>[#BODY#][#CATEGORY#][#COMMENT_LINK#]', 'コンテンツレイアウト(記事一覧)',               0),
('output_head',      '0', 'HTMLヘッダ出力', 0),
('head_view_detail', '<meta property="og:type" content="article" /><meta property="og:title" content="[#CT_TITLE#]" /><meta property="og:url" content="[#CT_URL#]" /><meta property="og:image" content="[#CT_IMAGE#]" /><meta property="og:description" content="[#CT_DESCRIPTION#]" /><meta property="og:site_name" content="[#SITE_NAME#]" />', 'HTMLヘッダ(詳細表示)',               0),
('s:layout_entry_single',   '[#TITLE#]<small>[#CT_AUTHOR#] [#CT_DATE#] [#CT_TIME#] [#BLOG_LINK#]</small>[#BODY#][#CATEGORY#][#LINKS#]', 'コンテンツレイアウト(記事詳細)',               0),
('s:layout_entry_list',   '[#TITLE#]<small>[#CT_AUTHOR#] [#CT_DATE#] [#CT_TIME#] [#BLOG_LINK#]</small>[#BODY#][#CATEGORY#][#COMMENT_LINK#]', 'コンテンツレイアウト(記事一覧)',               0),
('s:output_head',      '0', 'HTMLヘッダ出力', 0),
('s:head_view_detail', '<meta property="og:type" content="article" /><meta property="og:title" content="[#CT_TITLE#]" /><meta property="og:url" content="[#CT_URL#]" /><meta property="og:image" content="[#CT_IMAGE#]" /><meta property="og:description" content="[#CT_DESCRIPTION#]" /><meta property="og:site_name" content="[#SITE_NAME#]" />', 'HTMLヘッダ(詳細表示)',               0);


