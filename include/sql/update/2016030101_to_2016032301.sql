-- *
-- * バージョンアップ用スクリプト
-- *
-- * PHP versions 5
-- *
-- * LICENSE: This source file is licensed under the terms of the GNU General Public License.
-- *
-- * @package    Magic3 Framework
-- * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
-- * @copyright  Copyright 2006-2016 Magic3 Project.
-- * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
-- * @version    SVN: $Id$
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
-- システム設定マスター
INSERT INTO _system_config 
(sc_id,                          sc_value,             sc_name) VALUES
('server_no',                 '-1',                   'サーバ管理No'),
('server_admin_max_server_no',                 '0',                   '最大サーバ管理番号(サイト管理用)'),
('realtime_server_port',                 '',                   'リアルタイムサーバポート番号');

-- *** システム標準テーブル ***
-- ブログ記事マスター
ALTER TABLE blog_entry ADD be_meta_description  TEXT                                         NOT NULL;      -- METAタグ、ページ要約
ALTER TABLE blog_entry ADD be_meta_keywords     TEXT                                         NOT NULL;      -- METAタグ、検索用キーワード
