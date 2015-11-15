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

-- ブログ設定マスター
DELETE FROM blog_config;
INSERT INTO blog_config
(bg_id,                     bg_value,    bg_name) VALUES
('receive_comment',         '0',         'コメントの受け付け'),
('receive_trackback',       '0',         'トラックバックの受け付け'),
('entry_view_count',        '10',        '記事表示数'),
('entry_view_order',        '1',         '記事表示順'),
('comment_max_length',      '300',       'コメント最大文字数'),
('comment_count',           '100',       '1投稿記事のコメント最大数'),
('comment_open_time',       '30',        'コメント投稿可能期間(日)'),
('use_multi_blog',          '0',         'マルチブログを使用'),
('multi_blog_top_content',  '',          'マルチブログのトップ画面コンテンツ'),
('category_count',          '2',         '記事に設定可能なカテゴリ数'),
('readmore_label',          'もっと読む',         '「もっと読む」ボタンラベル'),
('entry_list_disp_type',         '0',         '記事一覧の表示タイプ'),
('show_entry_list_image',         '1',         '記事一覧に画像を表示するかどうか'),
('entry_list_image_type',         '80c.jpg',         '一覧の画像タイプ'),
('show_prev_next_entry_link',    '1', '前後記事リンクを表示するかどうか'),
('prev_next_entry_link_pos',     '1', '前後記事リンク表示位置'),
('show_entry_author',            '1', '投稿者を表示するかどうか'),
('show_entry_regist_dt',         '1', '投稿日時を表示するかどうか'),
('show_entry_view_count',        '0', '閲覧数を表示するかどうか'),
('thumb_type',              's=80c.jpg;mw=160x120c.jpg;l=200c.jpg', '記事サムネールタイプ定義'),
('entry_default_image',     '0_72c.jpg;0_80c.jpg;0_200c.jpg',       '記事デフォルト画像'),
('comment_user_limited',      '0',       'コメントのユーザ制限'),
('layout_entry_single',   '<small>[#CT_AUTHOR#] [#CT_DATE#] [#CT_TIME#] [#BLOG_LINK#]</small>[#BODY#][#CATEGORY#][#LINKS#]', 'コンテンツレイアウト(記事詳細)'),
('layout_entry_list',   '<small>[#CT_AUTHOR#] [#CT_DATE#] [#CT_TIME#] [#BLOG_LINK#]</small>[#BODY#][#CATEGORY#][#COMMENT_LINK#]', 'コンテンツレイアウト(記事一覧)'),
('layout_comment_list',   '[#AVATAR#]<dl><dt>[#TITLE#] <small>[#CM_AUTHOR#] [#CM_DATE#] [#CM_TIME#] [#URL#]</small></dt><dd>[#BODY#]</dd></dl>', 'コンテンツレイアウト(コメント一覧)'),
('output_head',      '0', 'HTMLヘッダ出力'),
('head_view_detail', '<meta property="og:type" content="article" /><meta property="og:title" content="[#CT_TITLE#]" /><meta property="og:url" content="[#CT_URL#]" /><meta property="og:image" content="[#CT_IMAGE#]" /><meta property="og:description" content="[#CT_DESCRIPTION#]" /><meta property="og:site_name" content="[#SITE_NAME#]" />', 'HTMLヘッダ(詳細表示)'),
('m:entry_view_count',      '3',         '記事表示数(携帯)'),
('m:entry_view_order',      '1',         '記事表示順(携帯)'),
('m:title_color',           '',         'タイトルの背景色'),
('s:entry_view_count',        '10',        '記事表示数'),
('s:entry_view_order',        '1',         '記事表示順'),
('s:top_content',  '',          'トップ画面コンテンツ'),
('s:auto_resize_image_max_size',  '280',      '画像の自動変換最大サイズ'),
('s:jquery_view_style',       '1',      'jQueryMobile表示スタイル'),
('s:use_title_list_image',       '1',      'タイトルリスト画像を使用'),
('s:title_list_image',       '',      'タイトルリスト画像'),
('s:layout_entry_single',   '<small>[#CT_AUTHOR#] [#CT_DATE#] [#CT_TIME#] [#BLOG_LINK#]</small>[#BODY#][#CATEGORY#][#LINKS#]', 'コンテンツレイアウト(記事詳細)'),
('s:layout_entry_list',   '<small>[#CT_AUTHOR#] [#CT_DATE#] [#CT_TIME#] [#BLOG_LINK#]</small>[#BODY#][#CATEGORY#][#COMMENT_LINK#]', 'コンテンツレイアウト(記事一覧)'),
('s:layout_comment_list', '[#AVATAR#]<dl><dt>[#TITLE#] <small>[#CM_AUTHOR#] [#CM_DATE#] [#CM_TIME#] [#URL#]</small></dt><dd>[#BODY#]</dd></dl>', 'コンテンツレイアウト(コメント一覧)'),
('s:output_head',      '0', 'HTMLヘッダ出力'),
('s:head_view_detail', '<meta property="og:type" content="article" /><meta property="og:title" content="[#CT_TITLE#]" /><meta property="og:url" content="[#CT_URL#]" /><meta property="og:image" content="[#CT_IMAGE#]" /><meta property="og:description" content="[#CT_DESCRIPTION#]" /><meta property="og:site_name" content="[#SITE_NAME#]" />', 'HTMLヘッダ(詳細表示)');

