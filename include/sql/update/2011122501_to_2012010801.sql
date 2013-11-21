-- *
-- * バージョンアップ用スクリプト
-- *
-- * PHP versions 5
-- *
-- * LICENSE: This source file is licensed under the terms of the GNU General Public License.
-- *
-- * @package    Magic3 Framework
-- * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
-- * @copyright  Copyright 2006-2011 Magic3 Project.
-- * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
-- * @version    SVN: $Id: 2011122501_to_2012010801.sql 4590 2012-01-14 00:36:41Z fishbone $
-- * @link       http://www.magic3.org
-- *
-- --------------------------------------------------------------------------------------------------
-- バージョンアップ用スクリプト
-- --------------------------------------------------------------------------------------------------

-- *** システムベーステーブル ***
-- メニュー定義マスター
ALTER TABLE _menu_def ADD md_enable            BOOLEAN        DEFAULT true                  NOT NULL;      -- 使用可能かどうか

-- ページ情報マスター
DELETE FROM _page_info WHERE pn_id = 's_index';
INSERT INTO _page_info
(pn_id,       pn_sub_id,   pn_content_type, pn_use_ssl) VALUES
('s_index',   'content',   'content',       false),
('s_index',   'shop',      'product',       false),
('s_index',   'shop_safe', '',              true),
('s_index',   'bbs',       'bbs',           false),
('s_index',   'blog',      'blog',          false),
('s_index',   'wiki',      'wiki',          false),
('s_index',   'user',      'user',          false),
('s_index',   'event',     'event',         false),
('s_index',   'photo',     'photo',         false),
('s_index',   'contact',   '',              true),
('s_index',   'contact2',  '',              true),
('s_index',   'contact3',  '',              true),
('s_index',   'safe',      '',              true);

-- *** システム標準テーブル ***


