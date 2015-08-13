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

-- Wiki設定マスター
DELETE FROM wiki_config;
INSERT INTO wiki_config
(wg_id,                     wg_value,        wg_name) VALUES
('password',                '',              '共通パスワード'),
('default_page',            'FrontPage',     'デフォルトページ'),
('whatsnew_page',           'RecentChanges', '最終更新ページ'),
('whatsdeleted_page',       'RecentDeleted', '最終削除ページ'),
('auth_type',               'admin',     '認証タイプ'),
('show_page_title',         '1',         'タイトルを表示するかどうか'),
('show_page_url',         '1',         'URLを表示するかどうか'),
('show_page_related',       '1',         '関連ページを表示するかどうか'),
('show_page_attach_files',  '1',         '添付ファイルを表示するかどうか'),
('show_page_last_modified', '1',         '最終更新を表示するかどうか'),
('show_toolbar_for_all_user', '0',         'すべてのユーザにツールバーを表示するかどうか'),
('user_limited_freeze',       '0',         '凍結・解凍機能のユーザ制限'),
('show_auto_heading_anchor',       '1',         '見出し自動アンカー'),
('layout_main',             '<article><header>[#TITLE#][#URL#]</header>[#TOOLBAR#][#BODY#]</article>[#TOOLBAR#][#FILES|pretag=----#][#UPDATES|pretag=----#][#LINKS#]', 'ページレイアウト(メイン)'),
('date_format', 'Y-m-d',         '日付フォーマット'),
('time_format', 'H:i:s',         '時間フォーマット'),
('show_username', '0',         'ユーザ名を表示するかどうか'),
('auto_link_wikiname', '1',         'Wiki名を自動リンクするかどうか'),
('recent_changes_count', '100',         '最終更新ページ最大項目数'),
('recent_deleted_count', '100',         '最終削除ページ最大項目数'),
('upload_filesize',   '1M',             'アップロードファイルの最大サイズ(バイト数)');
